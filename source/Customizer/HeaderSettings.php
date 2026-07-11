<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Customizer;

use Municipio\Customizer\KirkiField;

final class HeaderSettings
{
    public const SECTION_ID = 'municipio_customizer_section_header_panel_appearance';
    public const COLOR_SETTING = 'municipio_theme_extensions_header_link_color';
    public const TYPOGRAPHY_SETTING = 'municipio_theme_extensions_header_link_typography';

    private const HEADER_NAV_SELECTOR = '.c-header__menu--primary > .o-container > .c-nav';
    private const HEADER_LINK_SELECTOR = self::HEADER_NAV_SELECTOR . ' .c-button';
    private const HEADER_LINK_STATE_SELECTOR =
        self::HEADER_LINK_SELECTOR
            . ', '
            . self::HEADER_LINK_SELECTOR
            . ':link, '
            . self::HEADER_LINK_SELECTOR
            . ':visited, '
            . self::HEADER_LINK_SELECTOR
            . ':hover, '
            . self::HEADER_LINK_SELECTOR
            . ':visited:hover, '
            . self::HEADER_LINK_SELECTOR
            . ':focus, '
            . self::HEADER_LINK_SELECTOR
            . ':focus-visible, '
            . self::HEADER_LINK_SELECTOR
            . ':active';

    /**
     * Appends fields only after modern Municipio has registered its header appearance section.
     * This keeps the plugin inside Municipios existing design UI and ensures the fields are
     * included in Municipios customizer applicator cache.
     */
    public function register(object $section): void
    {
        if (!method_exists($section, 'getID') || $section->getID() !== self::SECTION_ID) {
            return;
        }

        foreach ($this->fields() as $field) {
            KirkiField::addField($field);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fields(): array
    {
        return [
            [
                'type' => 'color',
                'settings' => self::COLOR_SETTING,
                'label' => esc_html__('Header link color', 'municipio-theme-extensions'),
                'description' => esc_html__(
                    'Sets the color of links and icons shown directly in the header.',
                    'municipio-theme-extensions',
                ),
                'section' => self::SECTION_ID,
                'default' => '#000000',
                'priority' => 20,
                'transport' => 'auto',
                'output' => [
                    [
                        'element' => self::HEADER_NAV_SELECTOR,
                        'property' => '--c-button-default-color',
                    ],
                    [
                        'element' => self::HEADER_NAV_SELECTOR,
                        'property' => '--c-nav-h-color-contrasting',
                    ],
                    [
                        'element' => self::HEADER_LINK_STATE_SELECTOR,
                        'property' => 'color',
                    ],
                    [
                        'element' => self::HEADER_LINK_STATE_SELECTOR,
                        'property' => '--icon-color',
                    ],
                ],
            ],
            [
                'type' => 'typography',
                'settings' => self::TYPOGRAPHY_SETTING,
                'label' => esc_html__('Header link typography', 'municipio-theme-extensions'),
                'description' => esc_html__(
                    'Sets the size and weight of links shown directly in the header.',
                    'municipio-theme-extensions',
                ),
                'section' => self::SECTION_ID,
                'default' => [
                    'font-size' => '0.775rem',
                    'variant' => '500',
                ],
                'priority' => 20,
                'transport' => 'auto',
                'output' => [
                    [
                        'choice' => 'font-size',
                        'element' => self::HEADER_LINK_SELECTOR,
                        'property' => 'font-size',
                    ],
                    [
                        'choice' => 'variant',
                        'element' => self::HEADER_LINK_SELECTOR,
                        'property' => 'font-weight',
                    ],
                ],
            ],
        ];
    }
}
