<?php

// wp eval-file prepends code before evaluating this file, so strict_types cannot be declared here.

$source = dirname(__DIR__);
foreach ([
    'Customizer/TreeNavigationSettings',
    'Navigation/TreeData',
    'Navigation/TreeEndpoint',
    'Navigation/TreeNavigation',
] as $class) {
    if (!class_exists('MunicipioThemeExtensions\\' . str_replace('/', '\\', $class))) {
        require_once $source . '/source/' . $class . '.php';
    }
}
// Run through wp eval-file; choices are process filters, never persisted theme mods.
$mode = $args[0] ?? 'off';
if ($mode !== 'off') {
    add_filter(
        'theme_mod_municipio_theme_extensions_mobile_tree_navigation',
        fn() => $mode === 'mobile' || $mode === 'both',
    );
    add_filter(
        'theme_mod_municipio_theme_extensions_sidebar_tree_navigation',
        fn() => $mode === 'sidebar' || $mode === 'both',
    );
}
$endpoint = new MunicipioThemeExtensions\Navigation\TreeEndpoint();
$navigation = new MunicipioThemeExtensions\Navigation\TreeNavigation($endpoint);
add_action('rest_api_init', [$endpoint, 'register']);
rest_get_server();
foreach (['Controller', 'Modifier', 'Component'] as $type) {
    $class = 'Municipio\\Customizer\\Applicators\\Types\\' . $type;
    $applicator = new $class(new WpService\Implementations\NativeWpService());
    $applicator->applyData($applicator->getData());
}
add_filter('Municipio/viewPaths', [$navigation, 'viewPaths']);
add_filter('Municipio/Template/viewData', [$navigation, 'viewData'], 100);
add_filter('Municipio/Navigation/Cache/Key', [$endpoint, 'cacheKey'], 100);
$page = (int) ($args[1] ?? get_option('page_on_front'));
query_posts(['page_id' => $page]);
$GLOBALS['post'] = get_post($page);
setup_postdata($GLOBALS['post']);
$captured = [];
add_filter(
    'Municipio/Template/viewData',
    function ($data) use (&$captured) {
        $captured = $data;
        return $data;
    },
    999,
);
ob_start();
apply_filters('template_include', 'page');
$html = ob_get_clean();
file_put_contents('/tmp/task232-' . $mode . '-' . $page . '.html', $html);
$result = [
    'mode' => $mode,
    'page' => $page,
    'bytes' => strlen($html),
    'reserveCount' => substr_count($html, 'data-tree-reserve'),
    'panelCount' => substr_count($html, 'data-tree-panel'),
    'captured' => array_keys($captured),
];
foreach (['mobileMenu', 'secondaryMenu', 'breadcrumbMenu', 'primaryMenu', 'mobileSecondaryMenu'] as $name) {
    $result[$name] = [
        'hash' => hash('sha256', wp_json_encode($captured[$name] ?? [])),
        'rootCount' => count($captured[$name]['items'] ?? []),
    ];
}
preg_match_all('/data-tree-url="([^"]+)"/', $html, $matches);
$result['endpoints'] = [];
foreach ($matches[1] as $index => $matchedUrl) {
    $url = html_entity_decode($matchedUrl);
    $path = wp_parse_url($url, PHP_URL_PATH);
    $path = substr($path, strpos($path, '/municipio-theme-extensions/'));
    $response = rest_do_request(new WP_REST_Request('GET', $path));
    $count = static function (array $nodes) use (&$count): int {
        return array_sum(array_map(static fn(array $node): int => 1 + $count($node['children']), $nodes));
    };
    $result['endpoints'][] = [
        'status' => $response->get_status(),
        'nodes' => $response->get_status() === 200 ? $count($response->get_data()) : 0,
    ];
    if ($response->get_status() !== 200) {
        throw new RuntimeException('Tree endpoint failed');
    }
    file_put_contents(
        '/tmp/task232-' . get_current_blog_id() . '-' . $mode . '-' . $page . '-' . $index . '.json',
        wp_json_encode($response->get_data()),
    );
}
if ($mode !== 'off' && $matches[1] !== []) {
    $items = $captured['mobileMenu']['items'];
    $same = $endpoint->snapshot($items);
    $result['sameContextShared'] = $same === $endpoint->snapshot($items);
    $request = new WP_REST_Request('GET');
    $request['key'] = basename(wp_parse_url($same, PHP_URL_PATH));
    $user = get_current_user_id();
    wp_set_current_user($user === 0 ? 1 : 0);
    $result['otherUserDenied'] = is_wp_error($endpoint->permission($request));
    wp_set_current_user($user);
    $changeVersion = static fn() => 'integration-test-other-version';
    add_filter('pre_option_municipio_theme_extensions_tree_version', $changeVersion);
    $result['oldVersionDenied'] = is_wp_error($endpoint->permission($request));
    $result['nextViewNewVersion'] = $same !== $endpoint->snapshot($items);
    remove_filter('pre_option_municipio_theme_extensions_tree_version', $changeVersion);
    foreach (['sameContextShared', 'otherUserDenied', 'oldVersionDenied', 'nextViewNewVersion'] as $check) {
        if (!$result[$check]) {
            throw new RuntimeException($check . ' failed');
        }
    }
}
unset($result['captured']);
echo wp_json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
