<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Navigation;

use Closure;
use MunicipioThemeExtensions\Customizer\SecondaryNavigationPosition;

final class BelowTitleNavigation
{
    private const SETTING = 'secondary_navigation_position';

    /** @var null|Closure(string, array<string, mixed>, array<int, string>, bool): string */
    private readonly ?Closure $renderer;

    public function __construct(
        private readonly PageHideSecondaryMenu $pageHideSecondaryMenu = new PageHideSecondaryMenu(),
        ?callable $renderer = null,
    ) {
        $this->renderer = $renderer === null ? null : Closure::fromCallable($renderer);
    }

    public function render(): void
    {
        echo $this->getMarkup();
    }

    public function getMarkup(): string
    {
        if (get_theme_mod(self::SETTING, 'left') !== SecondaryNavigationPosition::BELOW_TITLE || !is_singular()) {
            return '';
        }

        $post = get_queried_object();
        if (!is_object($post) || ($post->ID ?? null) === null || ($post->post_type ?? null) === null) {
            return '';
        }

        $postType = get_post_type_object((string) $post->post_type);
        if (!is_object($postType) || ($postType->hierarchical ?? false) !== true) {
            return '';
        }

        if ($this->pageHideSecondaryMenu->isHidden((int) $post->ID)) {
            return '';
        }

        $items = array_map(
            static fn(object $child): array => [
                'text' => get_the_title((int) $child->ID),
                'href' => get_permalink((int) $child->ID),
            ],
            get_posts([
                'post_parent' => (int) $post->ID,
                'post_type' => (string) $post->post_type,
                'nopaging' => true,
                'post_status' => 'publish',
                'orderby' => ['menu_order' => 'ASC', 'ID' => 'ASC'],
                'meta_query' => [
                    'relation' => 'OR',
                    [
                        'key' => 'hide_in_menu',
                        'value' => '1',
                        'compare' => '!=',
                    ],
                    [
                        'key' => 'hide_in_menu',
                        'compare' => 'NOT EXISTS',
                    ],
                ],
            ]),
        );

        if ($items === []) {
            return '';
        }

        $renderer =
            $this->renderer
            ?? static fn(string $view, array $data, array $paths, bool $formatError): string => render_blade_view(
                $view,
                $data,
                $paths,
                $formatError,
            );

        return $renderer(
            'below-title-navigation',
            [
                'items' => $items,
                'label' => esc_html__('Subpages', 'municipio-theme-extensions'),
            ],
            [dirname(__DIR__, 2) . '/views'],
            false,
        );
    }
}
