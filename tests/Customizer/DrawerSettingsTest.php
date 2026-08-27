<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Customizer;

use Municipio\Customizer\KirkiField;
use MunicipioThemeExtensions\Customizer\DrawerSettings;
use PHPUnit\Framework\TestCase;

final class DrawerSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        KirkiField::$fields = [];
    }

    public function testItIgnoresOtherCustomizerSections(): void
    {
        (new DrawerSettings())->register($this->section('another_section'));

        static::assertSame([], KirkiField::$fields);
    }

    public function testItAddsLightChoicesToMunicipiosExistingDrawerSettings(): void
    {
        (new DrawerSettings())->register($this->section(DrawerSettings::SECTION_ID));

        static::assertCount(2, KirkiField::$fields);

        [$mainArea, $secondaryArea] = KirkiField::$fields;

        static::assertSame(DrawerSettings::MAIN_AREA_SETTING, $mainArea['settings']);
        static::assertSame('', $mainArea['default']);
        static::assertArrayHasKey(DrawerSettings::MAIN_AREA_LIGHT_VALUE, $mainArea['choices']);
        static::assertSame([['type' => 'modifier', 'context' => ['site.header.drawer']]], $mainArea['output']);

        static::assertSame(DrawerSettings::SECONDARY_AREA_SETTING, $secondaryArea['settings']);
        static::assertSame('duotone-primary', $secondaryArea['default']);
        static::assertArrayHasKey(DrawerSettings::SECONDARY_AREA_LIGHT_VALUE, $secondaryArea['choices']);
        static::assertSame([['type' => 'modifier', 'context' => ['site.header.drawer']]], $secondaryArea['output']);
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
