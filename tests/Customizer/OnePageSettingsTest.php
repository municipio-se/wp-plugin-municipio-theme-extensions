<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Customizer;

use Municipio\Customizer\KirkiField;
use MunicipioThemeExtensions\Customizer\OnePageSettings;
use PHPUnit\Framework\TestCase;

final class OnePageSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        KirkiField::$fields = [];
    }

    public function testItIgnoresOtherCustomizerSections(): void
    {
        (new OnePageSettings())->register($this->section('another_section'));

        static::assertSame([], KirkiField::$fields);
    }

    public function testItReusesTheLtsSettingInMunicipiosGeneralSection(): void
    {
        (new OnePageSettings())->register($this->section(OnePageSettings::SECTION_ID));

        static::assertCount(1, KirkiField::$fields);

        $field = KirkiField::$fields[0];

        static::assertSame(OnePageSettings::DISPLAY_CLASSIC_CONTENT_SETTING, $field['settings']);
        static::assertSame(OnePageSettings::SECTION_ID, $field['section']);
        static::assertSame('checkbox_switch', $field['type']);
        static::assertFalse($field['default']);
        static::assertSame([['type' => 'controller']], $field['output']);
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
