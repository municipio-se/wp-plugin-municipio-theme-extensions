<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Customizer;

use Closure;

final class ContentLayoutSettings
{
    public const SECTION_ID = 'municipio_customizer_section_width';
    public const DEFAULT_MODULE_WIDTH_SETTING = 'mx_default_module_width';
    public const CONTENT_AREA_PLACEMENT_SETTING = 'mx_content_area_placement';
    public const ARTICLE_ALIGNMENT_SETTING = 'mx_article_alignment';

    /** @var null|Closure(): array<string, string> */
    private readonly ?Closure $widthOptions;

    public function __construct(?callable $widthOptions = null)
    {
        $this->widthOptions = $widthOptions === null ? null : Closure::fromCallable($widthOptions);
    }

    public function register(object $section): void
    {
        if (!method_exists($section, 'getID') || $section->getID() !== self::SECTION_ID) {
            return;
        }

        foreach ($this->fields() as $field) {
            $this->addField($field);
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function fields(): array
    {
        return [
            [
                'type' => 'custom',
                'settings' => 'mx_section_content_layout_heading',
                'section' => self::SECTION_ID,
                'default' => '<h2>' . esc_html__('Content layout', 'municipio-theme-extensions') . '</h2>',
                'priority' => 50,
            ],
            [
                'type' => 'select',
                'settings' => self::DEFAULT_MODULE_WIDTH_SETTING,
                'label' => esc_html__('Default module width', 'municipio-theme-extensions'),
                'description' => esc_html__(
                    'Select the width modules should have when set to “inherit”.',
                    'municipio-theme-extensions',
                ),
                'section' => self::SECTION_ID,
                'default' => 'grid-md-12',
                'choices' => $this->getWidthOptions(),
                'output' => [['type' => 'controller']],
                'priority' => 50,
            ],
            [
                'type' => 'radio',
                'settings' => self::CONTENT_AREA_PLACEMENT_SETTING,
                'label' => esc_html__('Content area placement', 'municipio-theme-extensions'),
                'description' => esc_html__(
                    'Select how modules in the content area are placed in relation to the article.',
                    'municipio-theme-extensions',
                ),
                'section' => self::SECTION_ID,
                'default' => 'outside',
                'choices' => [
                    'outside' => esc_html__('Outside', 'municipio-theme-extensions'),
                    'inside' => esc_html__('Inside', 'municipio-theme-extensions'),
                ],
                'output' => [['type' => 'controller']],
                'priority' => 50,
            ],
            [
                'type' => 'radio',
                'settings' => self::ARTICLE_ALIGNMENT_SETTING,
                'label' => esc_html__('Article content alignment', 'municipio-theme-extensions'),
                'description' => esc_html__(
                    'Select how the article content should be aligned.',
                    'municipio-theme-extensions',
                ),
                'section' => self::SECTION_ID,
                'default' => 'left',
                'choices' => [
                    'left' => esc_html__('Left', 'municipio-theme-extensions'),
                    'center' => esc_html__('Center', 'municipio-theme-extensions'),
                ],
                'output' => [['type' => 'controller']],
                'priority' => 50,
            ],
        ];
    }

    /** @return array<string, string> */
    private function getWidthOptions(): array
    {
        if ($this->widthOptions !== null) {
            return ($this->widthOptions)();
        }

        if (class_exists(\Modularity\Editor::class)) {
            return (array) \Modularity\Editor::widthOptions();
        }

        return [
            'grid-md-12' => '100%',
            'grid-md-9' => '75%',
            'grid-md-8' => '66%',
            'grid-md-6' => '50%',
            'grid-md-4' => '33%',
            'grid-md-3' => '25%',
        ];
    }

    /** @param array<string, mixed> $field */
    private function addField(array $field): void
    {
        $registrar = class_exists(\Municipio\Customizer\CustomizerField::class)
            ? \Municipio\Customizer\CustomizerField::class
            : \Municipio\Customizer\KirkiField::class;

        $registrar::addField($field);
    }
}
