<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Customizer;

use Municipio\Customizer\KirkiField;

final class MenuSettings
{
    public const SECTION_ID = 'municipio_customizer_section_menu';
    public const SECONDARY_MENU_START_LEVEL_SETTING = 'municipio_theme_extensions_secondary_menu_start_at_level_two';

    /**
     * Appends the compatibility option to Municipios existing menu behaviour section so the
     * controller value participates in the same Customizer applicator cache as core menu options.
     */
    public function register(object $section): void
    {
        if (!method_exists($section, 'getID') || $section->getID() !== self::SECTION_ID) {
            return;
        }

        KirkiField::addField([
            'type' => 'switch',
            'settings' => self::SECONDARY_MENU_START_LEVEL_SETTING,
            'label' => esc_html__('Start secondary menu at level two', 'municipio-theme-extensions'),
            'description' => esc_html__(
                'Hides level one from the secondary page navigation.',
                'municipio-theme-extensions',
            ),
            'section' => self::SECTION_ID,
            'default' => true,
            'priority' => 10,
            'choices' => [
                true => esc_html__('Enabled', 'municipio-theme-extensions'),
                false => esc_html__('Disabled', 'municipio-theme-extensions'),
            ],
            'output' => [
                ['type' => 'controller'],
            ],
        ]);
    }
}
