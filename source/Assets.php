<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions;

use MunicipioThemeExtensions\Customizer\TypographySettings;

final class Assets
{
    private const STYLE_HANDLE = 'municipio-theme-extensions';
    private const SLIDER_A11Y_SCRIPT_HANDLE = 'municipio-theme-extensions-slider-accessibility';
    private const MODAL_A11Y_SCRIPT_HANDLE = 'municipio-theme-extensions-modal-accessibility';

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

        // Restores accessible semantics for the slider pagination, which modern
        // Municipio renders as a `role="tablist"` with no `tab` children. See
        // assets/js/slider-accessibility.js for the rationale.
        wp_enqueue_script(
            self::SLIDER_A11Y_SCRIPT_HANDLE,
            plugins_url('assets/js/slider-accessibility.js', $this->pluginFile),
            [],
            $this->version,
            true,
        );

        // Restores a sane focus order for the modal component, which modern
        // Municipio renders with positive `tabindex` values. See
        // assets/js/modal-accessibility.js for the rationale.
        wp_enqueue_script(
            self::MODAL_A11Y_SCRIPT_HANDLE,
            plugins_url('assets/js/modal-accessibility.js', $this->pluginFile),
            [],
            $this->version,
            true,
        );
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
