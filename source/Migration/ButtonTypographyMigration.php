<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Migration;

use MunicipioThemeExtensions\Customizer\TypographySettings;

final class ButtonTypographyMigration
{
    /**
     * Modern Municipio does not store font size or line height for buttons. Their presence therefore
     * identifies the reusable LTS typography value whose overridden Blade template had normal spacing.
     *
     * @param array<string, mixed> $themeMods
     */
    public static function run(array $themeMods): void
    {
        $typography = $themeMods[TypographySettings::BUTTON_TYPOGRAPHY_SETTING] ?? [];
        $typography = is_array($typography) ? $typography : [];

        if (array_key_exists('letter-spacing', $typography)) {
            return;
        }

        if (!array_key_exists('font-size', $typography) && !array_key_exists('line-height', $typography)) {
            return;
        }

        $typography['letter-spacing'] = 'normal';
        set_theme_mod(TypographySettings::BUTTON_TYPOGRAPHY_SETTING, $typography);
    }
}
