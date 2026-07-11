<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Customizer;

use Municipio\Customizer\KirkiField;

final class TypographySettings
{
    public const SECTION_ID = 'municipio_customizer_section_typography';
    public const BUTTON_TYPOGRAPHY_SETTING = 'typography_button';
    public const BUTTON_LETTER_SPACING_SETTING = self::BUTTON_TYPOGRAPHY_SETTING . '[letter-spacing]';
    public const BUTTON_LETTER_SPACING_PROPERTY = '--letter-spacing-button';
    public const BUTTON_LETTER_SPACING_DEFAULT = '.1rem';

    /**
     * Uses Kirki's composite-control metadata so the dimension is rendered and saved as part of
     * Municipios existing button typography control instead of becoming a separate top-level field.
     */
    public function register(object $section): void
    {
        if (!method_exists($section, 'getID') || $section->getID() !== self::SECTION_ID) {
            return;
        }

        KirkiField::addField([
            'type' => 'dimension',
            'settings' => self::BUTTON_LETTER_SPACING_SETTING,
            'parent_setting' => self::BUTTON_TYPOGRAPHY_SETTING,
            'label' => esc_html__('Letter Spacing', 'municipio-theme-extensions'),
            'section' => self::SECTION_ID,
            'default' => self::BUTTON_LETTER_SPACING_DEFAULT,
            'priority' => 10,
            'transport' => 'refresh',
            'wrapper_attrs' => [
                'data-kirki-parent-control-type' => 'kirki-typography',
                'data-kirki-typography-css-prop' => 'letter-spacing',
                'kirki-typography-subcontrol-type' => 'letter-spacing',
                'class' => '{default_class} kirki-group-item kirki-group-start kirki-group-end kirki-w100',
            ],
        ]);
    }
}
