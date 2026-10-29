<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Customizer;

use Municipio\Customizer\KirkiField;

final class TreeNavigationSettings
{
    public const MOBILE = 'municipio_theme_extensions_mobile_tree_navigation';
    public const SIDEBAR = 'municipio_theme_extensions_sidebar_tree_navigation';

    public function register(object $section): void
    {
        if (!method_exists($section, 'getID') || $section->getID() !== MenuSettings::SECTION_ID) {
            return;
        }

        foreach ([
            self::MOBILE => 'Tree navigation in the mobile menu',
            self::SIDEBAR => 'Tree navigation in the sidebar',
        ] as $setting => $label) {
            KirkiField::addField([
                'type' => 'switch',
                'settings' => $setting,
                'label' => esc_html__($label, 'municipio-theme-extensions'),
                'section' => MenuSettings::SECTION_ID,
                // Existing installations must opt in; activation never changes saved choices.
                'default' => false,
                'priority' => 20,
                'choices' => [
                    true => esc_html__('Enabled', 'municipio-theme-extensions'),
                    false => esc_html__('Disabled', 'municipio-theme-extensions'),
                ],
                'output' => [['type' => 'controller']],
            ]);
        }
    }

    public static function enabled(string $setting): bool
    {
        return filter_var(get_theme_mod($setting, false), FILTER_VALIDATE_BOOL);
    }
}
