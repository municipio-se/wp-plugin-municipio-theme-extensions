<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Navigation;

use MunicipioThemeExtensions\Customizer\TreeNavigationSettings as Settings;

final class TreeNavigation
{
    public function __construct(
        private readonly TreeEndpoint $endpoint = new TreeEndpoint(),
    ) {}

    public function viewPaths(array $paths): array
    {
        foreach ([Settings::MOBILE => 'mobile', Settings::SIDEBAR => 'sidebar'] as $setting => $directory) {
            if (Settings::enabled($setting)) {
                // Blade makeView prepends each supplied path, so the last path wins.
                $paths[] = dirname(__DIR__, 2) . '/views/tree/' . $directory;
            }
        }
        return $paths;
    }

    public function viewData(array $data): array
    {
        if (Settings::enabled(Settings::MOBILE) || Settings::enabled(Settings::SIDEBAR)) {
            $data['themeExtensionsTree'] = $this->markup(...);
        }
        return $data;
    }

    public function markup(array $items, string $label, array $classes = [], bool $mobile = false): string
    {
        if ($items === []) {
            return '';
        }
        $id = wp_unique_id('mte-tree-');
        $url = $this->endpoint->snapshot($items);
        if (function_exists('pll_current_language')) {
            $url = add_query_arg('lang', pll_current_language(), $url);
        }
        return sprintf(
            '<nav id="%s" class="mte-tree %s" aria-label="%s" data-tree-url="%s" data-tree-nonce="%s" data-tree-mobile="%s" data-tree-expand="%s" data-tree-collapse="%s"><p class="mte-tree__status" role="status" hidden>%s</p><div data-tree-reserve>%s</div></nav>',
            esc_attr($id),
            esc_attr(implode(' ', $classes)),
            esc_attr($label),
            esc_url($url),
            esc_attr(get_current_user_id() ? wp_create_nonce('wp_rest') : ''),
            $mobile ? 'true' : 'false',
            esc_attr__('Expand', 'municipio-theme-extensions'),
            esc_attr__('Collapse', 'municipio-theme-extensions'),
            esc_html__('Loading navigation…', 'municipio-theme-extensions'),
            $this->reserve($items),
        );
    }

    private function reserve(array $items, string $path = ''): string
    {
        $html = '<ul class="c-nav c-nav--vertical mte-tree__list">';
        foreach (array_values($items) as $index => $item) {
            $key = $path . '/' . $index . ':' . (string) ($item['id'] ?? '');
            $html .= '<li class="c-nav__item"><div class="c-nav__item-wrapper">';
            $href = esc_url((string) ($item['href'] ?? ''));
            $target = (string) ($item['target'] ?? $item['attributeList']['target'] ?? '_top');
            $rel = (string) ($item['rel'] ?? $item['attributeList']['rel'] ?? $item['xfn'] ?? '');
            if ($target === '_blank') {
                $rel .= ' noopener';
            }
            $label = esc_html(html_entity_decode(
                strip_tags((string) ($item['label'] ?? '')),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8',
            ));
            $html .= $href === ''
                ? '<span>' . $label . '</span>'
                : sprintf(
                    '<a class="c-nav__link" data-tree-key="%s" href="%s"%s%s>%s</a>',
                    esc_attr($key),
                    $href,
                    $item['active'] ?? false ? ' aria-current="page"' : '',
                    ' target="' . esc_attr($target) . '" rel="' . esc_attr(trim($rel)) . '"',
                    $label,
                );
            $html .= '</div>';
            if (is_array($item['children'] ?? null) && (($item['active'] ?? false) || ($item['ancestor'] ?? false))) {
                $html .= $this->reserve($item['children'], $key);
            }
            $html .= '</li>';
        }
        return $html . '</ul>';
    }
}
