<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Migration;

use MunicipioThemeExtensions\Customizer\HeaderSettings;

final class ActivationMigration
{
    public const VERSION_OPTION = 'municipio_theme_extensions_migration_version';

    /**
     * The schema version for the first unreleased package release. Development iterations are
     * squashed into this version until the release is tagged.
     *
     * @see docs/migrations.md
     */
    public const VERSION = '1';

    /**
     * Migrates only values needed by the first header delivery. Existing target values and all
     * legacy values are deliberately preserved so activation remains repeatable and reversible.
     */
    public static function run(): void
    {
        $installedVersion = (string) get_option(self::VERSION_OPTION, '0');
        if (version_compare($installedVersion, self::VERSION, '>=')) {
            return;
        }

        $themeMods = get_theme_mods();
        $themeMods = is_array($themeMods) ? $themeMods : [];

        self::migrateColor($themeMods);
        self::migrateTypography($themeMods);
        ButtonTypographyMigration::run($themeMods);

        update_option(self::VERSION_OPTION, self::VERSION);
    }

    /**
     * @param array<string, mixed> $themeMods
     */
    private static function migrateColor(array $themeMods): void
    {
        if (array_key_exists(HeaderSettings::COLOR_SETTING, $themeMods)) {
            return;
        }

        $legacyHeaderColor = $themeMods['header_color'] ?? null;
        $color = self::reuseColor($legacyHeaderColor) ?? self::mapLegacyColor($legacyHeaderColor, $themeMods);

        if ($color === null) {
            $primaryNavigationColors = $themeMods['nav_h_color_primary'] ?? null;
            $color = is_array($primaryNavigationColors)
                ? self::reuseColor($primaryNavigationColors['contrasting'] ?? null)
                : null;
        }

        if ($color !== null) {
            set_theme_mod(HeaderSettings::COLOR_SETTING, $color);
        }
    }

    /**
     * @param array<string, mixed> $themeMods
     */
    private static function migrateTypography(array $themeMods): void
    {
        if (array_key_exists(HeaderSettings::TYPOGRAPHY_SETTING, $themeMods)) {
            return;
        }

        $legacyTypography = $themeMods['typography_button'] ?? null;
        if (!is_array($legacyTypography)) {
            return;
        }

        $typography = [];
        foreach (['font-size', 'variant'] as $property) {
            $value = $legacyTypography[$property] ?? null;
            if (!is_string($value)) {
                continue;
            }

            $typography[$property] = $value;
        }

        if ($typography !== []) {
            set_theme_mod(HeaderSettings::TYPOGRAPHY_SETTING, $typography);
        }
    }

    private static function reuseColor(mixed $color): ?string
    {
        if (!is_string($color)) {
            return null;
        }

        $color = trim($color);
        if (preg_match('/^#[0-9a-f]{3,8}$/i', $color) === 1) {
            return $color;
        }

        if (preg_match('/^(?:rgb|hsl)a?\([^;{}]+\)$/i', $color) === 1) {
            return $color;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $themeMods
     */
    private static function mapLegacyColor(mixed $legacyColor, array $themeMods): ?string
    {
        if (!is_string($legacyColor)) {
            return null;
        }

        return match ($legacyColor) {
            'text-white' => '#ffffff',
            'text-black' => '#000000',
            'text-primary' => self::paletteColor($themeMods, 'color_palette_primary'),
            'text-secondary' => self::paletteColor($themeMods, 'color_palette_secondary'),
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $themeMods
     */
    private static function paletteColor(array $themeMods, string $paletteSetting): ?string
    {
        $palette = $themeMods[$paletteSetting] ?? null;
        return is_array($palette) ? self::reuseColor($palette['base'] ?? null) : null;
    }
}
