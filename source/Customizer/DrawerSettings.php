<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Customizer;

final class DrawerSettings
{
    public const SECTION_ID = 'municipio_customizer_section_drawer';
    public const MAIN_AREA_SETTING = 'drawer_color_scheme';
    public const SECONDARY_AREA_SETTING = 'drawer_color_scheme_secondary_area';
    public const MAIN_AREA_LIGHT_VALUE = 'light';
    public const SECONDARY_AREA_LIGHT_VALUE = 'duotone-light';

    /**
     * Re-registers Municipios existing fields with one additional choice each.
     * The setting names and modifier output stay owned by Municipio.
     */
    public function register(object $section): void
    {
        if (!method_exists($section, 'getID') || $section->getID() !== self::SECTION_ID) {
            return;
        }

        $this->addField([
            'type' => 'select',
            'settings' => self::MAIN_AREA_SETTING,
            'label' => esc_html__('Drawer color scheme', 'municipio'),
            'description' => esc_html__(
                'Sets the color scheme for the items inside the drawers main area',
                'municipio',
            ),
            'section' => self::SECTION_ID,
            'default' => '',
            'priority' => 10,
            'choices' => [
                '' => esc_html__('Basic', 'municipio'),
                'primary' => esc_html__('Primary', 'municipio'),
                'secondary' => esc_html__('Secondary', 'municipio'),
                self::MAIN_AREA_LIGHT_VALUE => esc_html__('Light', 'municipio-theme-extensions'),
            ],
            'output' => [
                [
                    'type' => 'modifier',
                    'context' => ['site.header.drawer'],
                ],
            ],
        ]);

        $this->addField([
            'type' => 'select',
            'settings' => self::SECONDARY_AREA_SETTING,
            'label' => esc_html__('Drawer secondary area', 'municipio'),
            'description' => esc_html__(
                'If using both the areas in the drawer menu this will decide the bottom area',
                'municipio',
            ),
            'section' => self::SECTION_ID,
            'default' => 'duotone-primary',
            'priority' => 10,
            'choices' => [
                '' => esc_html__('Basic', 'municipio'),
                'duotone-primary' => esc_html__('Primary', 'municipio'),
                'duotone-secondary' => esc_html__('Secondary', 'municipio'),
                self::SECONDARY_AREA_LIGHT_VALUE => esc_html__('Light', 'municipio-theme-extensions'),
            ],
            'output' => [
                [
                    'type' => 'modifier',
                    'context' => ['site.header.drawer'],
                ],
            ],
        ]);
    }

    /**
     * Municipio 6 uses KirkiField while current Municipio uses CustomizerField.
     * Both expose the same static field-registration contract.
     *
     * @param array<string, mixed> $field
     */
    private function addField(array $field): void
    {
        $registrar = class_exists(\Municipio\Customizer\CustomizerField::class)
            ? \Municipio\Customizer\CustomizerField::class
            : \Municipio\Customizer\KirkiField::class;

        $registrar::addField($field);
    }
}
