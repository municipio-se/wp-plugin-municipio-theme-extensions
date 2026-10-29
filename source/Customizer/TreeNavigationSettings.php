<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Customizer;

use Municipio\Customizer\KirkiField;

final class TreeNavigationSettings
{
    public const MOBILE = 'municipio_theme_extensions_mobile_tree_navigation';
    public const SIDEBAR = 'municipio_theme_extensions_sidebar_tree_navigation';
    public const COLORS = 'municipio_theme_extensions_mobile_tree_colors';
    private const COLOR_LABELS = [
        'background' => 'Panel background',
        'level_2' => 'Second level background',
        'level_deeper' => 'Deeper levels background',
        'hover_background' => 'Hover background',
        'hover_text' => 'Hover text',
        'open_background' => 'Expanded row background',
        'open_text' => 'Expanded row text',
    ];

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
        KirkiField::addField([
            'type' => 'multicolor',
            'settings' => self::COLORS,
            'label' => esc_html__('Mobile tree navigation colors', 'municipio-theme-extensions'),
            'section' => MenuSettings::SECTION_ID,
            'priority' => 21,
            'default' => [],
            'choices' => array_map(static fn($label) => esc_html__(
                $label,
                'municipio-theme-extensions',
            ), self::COLOR_LABELS),
            'output' => [['type' => 'controller']],
        ]);
    }

    public static function colorCss(): string
    {
        $values = get_theme_mod(self::COLORS, []);
        $declarations = [];
        $panel = '';
        foreach (self::COLOR_LABELS as $key => $label) {
            $value = is_array($values) ? $values[$key] ?? null : null;
            // Empty choices inherit the site's existing navigation palette.
            if (is_string($value) && preg_match('/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i', $value) === 1) {
                if ($key === 'background') {
                    // The existing light drawer variant consumes this token, scoped to this panel.
                    $panel = '.mte-mobile__content{--color-background:' . $value . '}';
                    continue;
                }
                $declarations[] = '--mte-tree-' . str_replace('_', '-', $key) . ':' . $value;
            }
        }
        return (
            $panel
            . ($declarations === [] ? '' : '.mte-tree[data-tree-mobile="true"]{' . implode(';', $declarations) . '}')
        );
    }

    public static function enabled(string $setting): bool
    {
        return filter_var(get_theme_mod($setting, false), FILTER_VALIDATE_BOOL);
    }
}
