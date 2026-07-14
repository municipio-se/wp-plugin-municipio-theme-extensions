<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Customizer;

use Municipio\Customizer\KirkiField;
use MunicipioThemeExtensions\Customizer\MenuSettings;
use PHPUnit\Framework\TestCase;

final class MenuSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        KirkiField::$fields = [];
    }

    public function testItIgnoresOtherCustomizerSections(): void
    {
        (new MenuSettings())->register($this->section('another_section'));

        static::assertSame([], KirkiField::$fields);
    }

    public function testItRegistersTheSettingInMunicipiosMenuBehaviourSection(): void
    {
        (new MenuSettings())->register($this->section(MenuSettings::SECTION_ID));

        static::assertCount(1, KirkiField::$fields);

        $field = KirkiField::$fields[0];

        static::assertSame(MenuSettings::SECONDARY_MENU_START_LEVEL_SETTING, $field['settings']);
        static::assertSame(MenuSettings::SECTION_ID, $field['section']);
        static::assertSame('switch', $field['type']);
        static::assertTrue($field['default']);
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
