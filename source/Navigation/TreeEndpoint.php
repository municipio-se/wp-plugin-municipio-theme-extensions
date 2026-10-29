<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Navigation;

use MunicipioThemeExtensions\Customizer\TreeNavigationSettings as Settings;
use RuntimeException;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class TreeEndpoint
{
    private const VERSION = 'municipio_theme_extensions_tree_version';
    private const PREFIX = 'mte_tree_';

    public function register(): void
    {
        register_rest_route('municipio-theme-extensions/v1', '/navigation/(?P<key>[a-f0-9]{64})', [
            'methods' => 'GET',
            'permission_callback' => [$this, 'permission'],
            'callback' => [$this, 'respond'],
        ]);
    }

    public function invalidate(): void
    {
        if (!Settings::enabled(Settings::MOBILE) && !Settings::enabled(Settings::SIDEBAR)) {
            return;
        }
        update_option(self::VERSION, wp_generate_uuid4(), false);
        // A page-cache hit must not keep advertising the old navigation version.
        do_action('litespeed_purge_all');
    }

    public function optionChanged(string $option): void
    {
        if (
            str_starts_with($option, 'theme_mods_')
            || str_starts_with($option, 'page_for_')
            || in_array($option, ['page_on_front', 'show_on_front', 'permalink_structure', 'polylang'], true)
        ) {
            $this->invalidate();
        }
    }

    public function metaChanged(mixed $metaId, int $postId, string $key): void
    {
        if (in_array($key, ['hide_in_menu', 'custom_menu_title'], true) || str_starts_with($key, '_menu_item_')) {
            $this->invalidate();
        }
    }

    public function cacheKey(string $key): string
    {
        if (!Settings::enabled(Settings::MOBILE) && !Settings::enabled(Settings::SIDEBAR)) {
            return $key;
        }
        // Municipio otherwise reuses its own menu cache for 15 minutes after our snapshot changes.
        return $key . ':tree:' . get_option(self::VERSION, 'initial') . ':' . get_current_user_id();
    }

    public function snapshot(array $items): string
    {
        $context = [
            'items' => TreeData::seed($items),
            'user' => get_current_user_id(),
            'locale' => get_locale(),
            'language' => function_exists('pll_current_language') ? pll_current_language() : '',
            'permissions' => $this->permissions(),
            'blog' => get_current_blog_id(),
            'version' => get_option(self::VERSION, 'initial'),
        ];
        $key = hash_hmac('sha256', (string) wp_json_encode($context), wp_salt('auth'));
        set_transient(self::PREFIX . $key, $context, DAY_IN_SECONDS);
        return rest_url('municipio-theme-extensions/v1/navigation/' . $key);
    }

    public function permission(WP_REST_Request $request): bool|WP_Error
    {
        $context = get_transient(self::PREFIX . $request['key']);
        if (
            !is_array($context)
            || $context['user'] !== get_current_user_id()
            || $context['blog'] !== get_current_blog_id()
            || $context['locale'] !== get_locale()
            || $context['language'] !== (function_exists('pll_current_language') ? pll_current_language() : '')
            || $context['permissions'] !== $this->permissions()
            || $context['version'] !== get_option(self::VERSION, 'initial')
        ) {
            return new WP_Error('navigation_expired', 'Navigation is no longer available.', ['status' => 404]);
        }
        return true;
    }

    private function permissions(): string
    {
        $caps = wp_get_current_user()->allcaps;
        ksort($caps);
        return hash('sha256', (string) wp_json_encode($caps));
    }

    public function respond(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $allowed = $this->permission($request);
        if ($allowed !== true) {
            return $allowed;
        }
        $context = get_transient(self::PREFIX . $request['key']);
        $cacheKey = self::PREFIX . 'data_' . $request['key'];
        $data = $context['user'] === 0 ? get_transient($cacheKey) : false;
        if ($data === false) {
            try {
                $data = (new TreeData())->expand($context['items'], $this->resolve(...));
            } catch (RuntimeException) {
                return new WP_Error('navigation_unavailable', 'Navigation could not be loaded.', ['status' => 503]);
            }
            if ($context['user'] === 0) {
                set_transient($cacheKey, $data, DAY_IN_SECONDS);
            }
        }
        $response = new WP_REST_Response($data);
        // Authenticated data must never persist in a shared browser/proxy cache.
        $response->header(
            'Cache-Control',
            $context['user'] === 0 ? 'public, max-age=86400, immutable' : 'private, no-store',
        );
        return $response;
    }

    private function resolve(string $fetchUrl): array
    {
        parse_str((string) wp_parse_url($fetchUrl, PHP_URL_QUERY), $query);
        if ((int) ($query['pageId'] ?? 0) <= 0 || !in_array($query['identifier'] ?? '', ['mobile', 'sidebar'], true)) {
            throw new RuntimeException('Unsupported navigation branch.');
        }
        // Internal dispatch keeps Municipio's language, hidden-page and menu filters, without HTTP recursion.
        $request = new WP_REST_Request('GET', '/municipio/v1/navigation/children');
        $request->set_query_params(array_intersect_key($query, array_flip(['pageId', 'identifier', 'lang'])));
        $response = rest_do_request($request);
        if ($response->is_error() || !is_array($response->get_data())) {
            throw new RuntimeException('Navigation branch failed.');
        }
        return array_map(
            static function (array $item): array {
                // The children endpoint maps post_title directly; retain the page's explicit menu title.
                $title = get_post_meta((int) ($item['id'] ?? 0), 'custom_menu_title', true);
                if (is_string($title) && $title !== '') {
                    $item['label'] = $title;
                }
                return $item;
            },
            $response->get_data(),
        );
    }
}
