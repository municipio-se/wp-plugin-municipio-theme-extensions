<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Customizer;

use Municipio\Customizer\KirkiField;
use MunicipioThemeExtensions\Customizer\TypographySettings;
use PHPUnit\Framework\TestCase;

final class TypographySettingsTest extends TestCase
{
    protected function setUp(): void
    {
        KirkiField::$fields = [];
    }

    public function testItIgnoresOtherCustomizerSections(): void
    {
        (new TypographySettings())->register($this->section('another_section'));

        static::assertSame([], KirkiField::$fields);
    }

    public function testItAddsButtonLetterSpacingToMunicipiosTypographySection(): void
    {
        (new TypographySettings())->register($this->section(TypographySettings::SECTION_ID));

        static::assertCount(1, KirkiField::$fields);
        static::assertSame(TypographySettings::BUTTON_LETTER_SPACING_SETTING, KirkiField::$fields[0]['settings']);
        static::assertSame(TypographySettings::BUTTON_TYPOGRAPHY_SETTING, KirkiField::$fields[0]['parent_setting']);
        static::assertSame('Letter Spacing', KirkiField::$fields[0]['label']);
        static::assertSame(TypographySettings::SECTION_ID, KirkiField::$fields[0]['section']);
        static::assertSame(
            'kirki-typography',
            KirkiField::$fields[0]['wrapper_attrs']['data-kirki-parent-control-type'],
        );
        static::assertSame(
            'letter-spacing',
            KirkiField::$fields[0]['wrapper_attrs']['kirki-typography-subcontrol-type'],
        );
        static::assertSame('refresh', KirkiField::$fields[0]['transport']);
        static::assertArrayNotHasKey('output', KirkiField::$fields[0]);
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
