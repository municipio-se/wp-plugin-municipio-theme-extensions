<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Customizer;

use Municipio\Customizer\KirkiField;
use MunicipioThemeExtensions\Customizer\ResponsiveHeaderSettings;
use PHPUnit\Framework\TestCase;

final class ResponsiveHeaderSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        KirkiField::$fields = [];
    }

    public function testItRegistersEachSettingInMunicipiosExistingSemanticSection(): void
    {
        $settings = new ResponsiveHeaderSettings();

        $settings->register($this->section(ResponsiveHeaderSettings::HEADER_LAYOUT_SECTION_ID));
        $settings->register($this->section(ResponsiveHeaderSettings::TAB_MENU_SECTION_ID));
        $settings->register($this->section(ResponsiveHeaderSettings::SEARCH_SECTION_ID));

        static::assertSame(
            [
                ResponsiveHeaderSettings::BREAKPOINT_SETTING,
                ResponsiveHeaderSettings::TAB_MENU_BUTTON_SIZE_SETTING,
                ResponsiveHeaderSettings::HERO_SEARCH_PLACEHOLDER_SETTING,
            ],
            array_column(KirkiField::$fields, 'settings'),
        );
        static::assertSame('standard', KirkiField::$fields[0]['tab']);
        static::assertSame('lg', KirkiField::$fields[0]['default']);
        static::assertSame('sm', KirkiField::$fields[1]['default']);
        static::assertSame('', KirkiField::$fields[2]['default']);

        foreach (KirkiField::$fields as $field) {
            static::assertSame('controller', $field['output'][0]['type']);
            static::assertFalse($field['output'][0]['as_object']);
        }
    }

    public function testItIgnoresUnrelatedOrInvalidSections(): void
    {
        $settings = new ResponsiveHeaderSettings();

        $settings->register($this->section('another_section'));
        $settings->register(new \stdClass());

        static::assertSame([], KirkiField::$fields);
    }

    private function section(string $id): object
    {
        return new class($id) {
            public function __construct(
                private readonly string $id,
            ) {}

            public function getID(): string
            {
                return $this->id;
            }
        };
    }
}
