<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Migration;

use MunicipioThemeExtensions\Customizer\ResponsiveHeaderSettings;

final class SearchDisplayMigration
{
    /**
     * Current Municipio separates desktop and mobile search placements. The legacy-only header
     * settings prove that the shared placement came from LTS, which prevents this migration from
     * changing an intentional modern desktop-only selection.
     *
     * @param array<string, mixed> $themeMods
     */
    public static function run(array $themeMods): void
    {
        if (!self::hasLegacyHeaderSettings($themeMods)) {
            return;
        }

        $searchDisplay = $themeMods['search_display'] ?? null;
        if (!is_array($searchDisplay) || !self::containsOnlyStrings($searchDisplay)) {
            return;
        }

        $migrated = $searchDisplay;
        self::appendMobilePlacement($migrated, 'header', 'header_mobile');
        self::appendMobilePlacement($migrated, 'header_sub', 'header_mobile_sub');

        if ($migrated !== $searchDisplay) {
            set_theme_mod('search_display', $migrated);
        }
    }

    /**
     * @param array<string, mixed> $themeMods
     */
    private static function hasLegacyHeaderSettings(array $themeMods): bool
    {
        foreach ([
            ResponsiveHeaderSettings::BREAKPOINT_SETTING,
            ResponsiveHeaderSettings::TAB_MENU_BUTTON_SIZE_SETTING,
            ResponsiveHeaderSettings::HERO_SEARCH_PLACEHOLDER_SETTING,
        ] as $setting) {
            if (array_key_exists($setting, $themeMods)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, string> $placements
     */
    private static function appendMobilePlacement(array &$placements, string $desktop, string $mobile): void
    {
        if (in_array($desktop, $placements, true) && !in_array($mobile, $placements, true)) {
            $placements[] = $mobile;
        }
    }

    /**
     * @param array<mixed> $values
     */
    private static function containsOnlyStrings(array $values): bool
    {
        foreach ($values as $value) {
            if (!is_string($value)) {
                return false;
            }
        }

        return true;
    }
}
