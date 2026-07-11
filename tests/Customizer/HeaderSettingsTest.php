<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Customizer;

use Municipio\Customizer\KirkiField;
use MunicipioThemeExtensions\Customizer\HeaderSettings;
use PHPUnit\Framework\TestCase;

final class HeaderSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        KirkiField::$fields = [];
    }

    public function testItIgnoresOtherCustomizerSections(): void
    {
        (new HeaderSettings())->register($this->section('another_section'));

        static::assertSame([], KirkiField::$fields);
    }

    public function testItRegistersHeaderColorAndTypographyInMunicipiosHeaderSection(): void
    {
        (new HeaderSettings())->register($this->section(HeaderSettings::SECTION_ID));

        static::assertCount(2, KirkiField::$fields);
        static::assertSame(HeaderSettings::COLOR_SETTING, KirkiField::$fields[0]['settings']);
        static::assertSame(HeaderSettings::TYPOGRAPHY_SETTING, KirkiField::$fields[1]['settings']);
        static::assertSame(HeaderSettings::SECTION_ID, KirkiField::$fields[0]['section']);
        static::assertSame('--c-button-default-color', KirkiField::$fields[0]['output'][0]['property']);
        static::assertSame('--c-nav-h-color-contrasting', KirkiField::$fields[0]['output'][1]['property']);
        static::assertSame('color', KirkiField::$fields[0]['output'][2]['property']);
        static::assertStringContainsString(':visited:hover', KirkiField::$fields[0]['output'][2]['element']);
        static::assertSame('--icon-color', KirkiField::$fields[0]['output'][3]['property']);
        static::assertSame('font-size', KirkiField::$fields[1]['output'][0]['property']);
        static::assertSame('font-weight', KirkiField::$fields[1]['output'][1]['property']);
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
