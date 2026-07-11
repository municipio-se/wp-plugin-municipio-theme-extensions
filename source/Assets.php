<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions;

use MunicipioThemeExtensions\Customizer\TypographySettings;

final class Assets
{
    private const STYLE_HANDLE = 'municipio-theme-extensions';

    public function __construct(
        private readonly string $pluginFile,
        private readonly string $version,
    ) {}

    public function register(): void
    {
        // Load after Municipio so the equally specific small-button rule can consume our variable.
        add_action('wp_enqueue_scripts', [$this, 'enqueue'], 100);
    }

    public function enqueue(): void
    {
        wp_enqueue_style(
            self::STYLE_HANDLE,
            plugins_url('assets/css/municipio-theme-extensions.css', $this->pluginFile),
            [],
            $this->version,
        );

        wp_add_inline_style(self::STYLE_HANDLE, $this->buttonLetterSpacingCss());
    }

    public function buttonLetterSpacingCss(): string
    {
        $typography = get_theme_mod(TypographySettings::BUTTON_TYPOGRAPHY_SETTING, []);
        $letterSpacing = is_array($typography) ? $typography['letter-spacing'] ?? null : null;

        if (!is_string($letterSpacing) || !$this->isValidLetterSpacing($letterSpacing)) {
            $letterSpacing = TypographySettings::BUTTON_LETTER_SPACING_DEFAULT;
        }

        return sprintf(':root{%s:%s;}', TypographySettings::BUTTON_LETTER_SPACING_PROPERTY, $letterSpacing);
    }

    private function isValidLetterSpacing(string $value): bool
    {
        return (
            preg_match(
                '/^(?:normal|0|[-+]?(?:\d+(?:\.\d+)?|\.\d+)(?:%|cap|ch|cm|em|ex|ic|in|lh|mm|pc|pt|px|q|rem|vmax|vmin|vh|vw))$/i',
                $value,
            ) === 1
        );
    }
}
