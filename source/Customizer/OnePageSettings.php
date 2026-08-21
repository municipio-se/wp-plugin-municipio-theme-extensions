<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Customizer;

use Municipio\Customizer\KirkiField;

final class OnePageSettings
{
    public const SECTION_ID = 'municipio_customizer_section_general';
    public const DISPLAY_CLASSIC_CONTENT_SETTING = 'municipio_customizer_onepage_body_text';

    /**
     * Reuses the released LTS theme-mod key inside Municipios existing general-settings section.
     * Imported values and Customizer previews therefore work without a data migration.
     */
    public function register(object $section): void
    {
        if (!method_exists($section, 'getID') || $section->getID() !== self::SECTION_ID) {
            return;
        }

        KirkiField::addField([
            'type' => 'checkbox_switch',
            'settings' => self::DISPLAY_CLASSIC_CONTENT_SETTING,
            'label' => esc_html__('Display text content for One Page template', 'municipio-theme-extensions'),
            'section' => self::SECTION_ID,
            'default' => false,
            'priority' => 20,
            'output' => [
                ['type' => 'controller'],
            ],
        ]);
    }
}
