<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Customizer;

use Municipio\Customizer\KirkiField;

final class ResponsiveHeaderSettings
{
    public const HEADER_LAYOUT_SECTION_ID = 'municipio_customizer_section_header_panel_layout';
    public const TAB_MENU_SECTION_ID = 'municipio_customizer_section_header_panel_tab_menu';
    public const SEARCH_SECTION_ID = 'municipio_customizer_section_search';
    public const BREAKPOINT_SETTING = 'header_breakpoint';
    public const TAB_MENU_BUTTON_SIZE_SETTING = 'tab_menu_button_size';
    public const HERO_SEARCH_PLACEHOLDER_SETTING = 'hero_search_placeholder';

    /**
     * Keeps the restored settings inside Municipios existing semantic sections and controller
     * applicator cache instead of adding a parallel extension panel.
     */
    public function register(object $section): void
    {
        if (!method_exists($section, 'getID')) {
            return;
        }

        $field = match ($section->getID()) {
            self::HEADER_LAYOUT_SECTION_ID => $this->breakpointField(),
            self::TAB_MENU_SECTION_ID => $this->tabMenuButtonSizeField(),
            self::SEARCH_SECTION_ID => $this->heroSearchPlaceholderField(),
            default => null,
        };

        if ($field !== null) {
            KirkiField::addField($field);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function breakpointField(): array
    {
        return [
            'type' => 'select',
            'settings' => self::BREAKPOINT_SETTING,
            'label' => esc_html__('Header breakpoint', 'municipio-theme-extensions'),
            'description' => esc_html__(
                'Selects the first screen size that uses the standard desktop header.',
                'municipio-theme-extensions',
            ),
            'section' => self::HEADER_LAYOUT_SECTION_ID,
            'default' => 'lg',
            'priority' => 20,
            'tab' => 'standard',
            'choices' => [
                'xs' => esc_html__('Extra small', 'municipio-theme-extensions'),
                'sm' => esc_html__('Small', 'municipio-theme-extensions'),
                'md' => esc_html__('Medium', 'municipio-theme-extensions'),
                'lg' => esc_html__('Large', 'municipio-theme-extensions'),
                'xl' => esc_html__('Extra large', 'municipio-theme-extensions'),
            ],
            'output' => [
                [
                    'type' => 'controller',
                    'as_object' => false,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function tabMenuButtonSizeField(): array
    {
        return [
            'type' => 'select',
            'settings' => self::TAB_MENU_BUTTON_SIZE_SETTING,
            'label' => esc_html__('Tab menu button size', 'municipio-theme-extensions'),
            'section' => self::TAB_MENU_SECTION_ID,
            'default' => 'sm',
            'priority' => 20,
            'choices' => [
                'sm' => esc_html__('Small', 'municipio-theme-extensions'),
                'md' => esc_html__('Medium', 'municipio-theme-extensions'),
                'lg' => esc_html__('Large', 'municipio-theme-extensions'),
            ],
            'output' => [
                [
                    'type' => 'controller',
                    'as_object' => false,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function heroSearchPlaceholderField(): array
    {
        return [
            'type' => 'text',
            'settings' => self::HERO_SEARCH_PLACEHOLDER_SETTING,
            'label' => esc_html__('Hero search placeholder', 'municipio-theme-extensions'),
            'section' => self::SEARCH_SECTION_ID,
            'default' => '',
            'priority' => 20,
            'output' => [
                [
                    'type' => 'controller',
                    'as_object' => false,
                ],
            ],
        ];
    }
}
