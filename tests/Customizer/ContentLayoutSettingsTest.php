<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Customizer;

use Municipio\Customizer\KirkiField;
use MunicipioThemeExtensions\Customizer\ContentLayoutSettings;
use PHPUnit\Framework\TestCase;

final class ContentLayoutSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        KirkiField::$fields = [];
    }

    public function testItRegistersTheLegacySettingsInMunicipiosWidthSection(): void
    {
        $settings = new ContentLayoutSettings(static fn(): array => ['grid-md-12' => '100%', 'grid-md-6' => '50%']);

        $settings->register($this->section(ContentLayoutSettings::SECTION_ID));

        static::assertSame(
            [
                'mx_section_content_layout_heading',
                ContentLayoutSettings::DEFAULT_MODULE_WIDTH_SETTING,
                ContentLayoutSettings::CONTENT_AREA_PLACEMENT_SETTING,
                ContentLayoutSettings::ARTICLE_ALIGNMENT_SETTING,
            ],
            array_column(KirkiField::$fields, 'settings'),
        );
        static::assertSame('grid-md-12', KirkiField::$fields[1]['default']);
        static::assertSame(['grid-md-12' => '100%', 'grid-md-6' => '50%'], KirkiField::$fields[1]['choices']);
        static::assertSame([['type' => 'controller']], KirkiField::$fields[1]['output']);
        static::assertSame('outside', KirkiField::$fields[2]['default']);
        static::assertSame([['type' => 'controller']], KirkiField::$fields[2]['output']);
        static::assertSame('left', KirkiField::$fields[3]['default']);
        static::assertSame([['type' => 'controller']], KirkiField::$fields[3]['output']);
    }

    public function testItIgnoresOtherSections(): void
    {
        $settings = new ContentLayoutSettings();

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
