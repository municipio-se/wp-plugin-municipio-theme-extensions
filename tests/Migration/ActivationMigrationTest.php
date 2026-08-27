<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Migration;

use MunicipioThemeExtensions\Customizer\HeaderSettings;
use MunicipioThemeExtensions\Customizer\TypographySettings;
use MunicipioThemeExtensions\Migration\ActivationMigration;
use MunicipioThemeExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;

final class ActivationMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        WordPressState::reset();
    }

    public function testItRecordsTheVersionWhenLegacyValuesAreMissing(): void
    {
        ActivationMigration::run();

        static::assertSame([], WordPressState::$themeMods);
        static::assertSame(ActivationMigration::VERSION, WordPressState::$options[ActivationMigration::VERSION_OPTION]);
    }

    public function testItReusesCompatibleColorsAndTypographyValues(): void
    {
        WordPressState::$themeMods = [
            'header_color' => '#fefefe',
            'typography_button' => [
                'font-size' => '16px',
                'variant' => '600',
                'font-family' => 'Open Sans',
            ],
        ];

        ActivationMigration::run();

        static::assertSame('#fefefe', WordPressState::$themeMods[HeaderSettings::COLOR_SETTING]);
        static::assertSame(
            ['font-size' => '16px', 'variant' => '600'],
            WordPressState::$themeMods[HeaderSettings::TYPOGRAPHY_SETTING],
        );
        static::assertSame('#fefefe', WordPressState::$themeMods['header_color']);
        static::assertSame(
            [
                'font-size' => '16px',
                'variant' => '600',
                'font-family' => 'Open Sans',
                'letter-spacing' => 'normal',
            ],
            WordPressState::$themeMods[TypographySettings::BUTTON_TYPOGRAPHY_SETTING],
        );
    }

    public function testItMapsLegacyColorTokensAndKeepsLegacyValues(): void
    {
        WordPressState::$themeMods = [
            'header_color' => 'text-white',
        ];

        ActivationMigration::run();

        static::assertSame('#ffffff', WordPressState::$themeMods[HeaderSettings::COLOR_SETTING]);
        static::assertSame('text-white', WordPressState::$themeMods['header_color']);
    }

    public function testItFallsBackToTheExistingPrimaryNavigationColor(): void
    {
        WordPressState::$themeMods = [
            'nav_h_color_primary' => ['contrasting' => '#ecf2fb'],
        ];

        ActivationMigration::run();

        static::assertSame('#ecf2fb', WordPressState::$themeMods[HeaderSettings::COLOR_SETTING]);
    }

    public function testItDoesNotOverwriteConfiguredTargetsOrRerunTheMigration(): void
    {
        WordPressState::$themeMods = [
            'header_color' => 'text-white',
            'typography_button' => ['font-size' => '16px', 'letter-spacing' => '.05em'],
            HeaderSettings::COLOR_SETTING => '#123456',
            HeaderSettings::TYPOGRAPHY_SETTING => ['font-size' => '18px', 'variant' => '700'],
        ];

        ActivationMigration::run();
        $afterFirstRun = WordPressState::$themeMods;

        WordPressState::$themeMods['header_color'] = 'text-black';
        ActivationMigration::run();

        static::assertSame('#123456', WordPressState::$themeMods[HeaderSettings::COLOR_SETTING]);
        static::assertSame(
            ['font-size' => '18px', 'variant' => '700'],
            WordPressState::$themeMods[HeaderSettings::TYPOGRAPHY_SETTING],
        );
        static::assertSame(
            '.05em',
            WordPressState::$themeMods[TypographySettings::BUTTON_TYPOGRAPHY_SETTING]['letter-spacing'],
        );
        static::assertSame(
            $afterFirstRun[HeaderSettings::COLOR_SETTING],
            WordPressState::$themeMods[HeaderSettings::COLOR_SETTING],
        );
    }

    public function testItDoesNotRerunTheRecordedMigration(): void
    {
        WordPressState::$options[ActivationMigration::VERSION_OPTION] = '2';
        WordPressState::$themeMods = [
            TypographySettings::BUTTON_TYPOGRAPHY_SETTING => [
                'font-size' => '16px',
                'letter-spacing' => '.05em',
            ],
        ];

        ActivationMigration::run();

        static::assertSame(
            ['font-size' => '16px', 'letter-spacing' => '.05em'],
            WordPressState::$themeMods[TypographySettings::BUTTON_TYPOGRAPHY_SETTING],
        );
        static::assertSame(ActivationMigration::VERSION, WordPressState::$options[ActivationMigration::VERSION_OPTION]);
    }

    public function testItRunsTheSecondMigrationForAnInstallationAtVersionOne(): void
    {
        WordPressState::$options[ActivationMigration::VERSION_OPTION] = '1';
        WordPressState::$themeMods = [
            'hero_search_placeholder' => 'Find services',
            'search_display' => ['header_sub'],
        ];

        ActivationMigration::run();

        static::assertSame(['header_sub', 'header_mobile_sub'], WordPressState::$themeMods['search_display']);
        static::assertSame('2', WordPressState::$options[ActivationMigration::VERSION_OPTION]);
    }

    public function testItDoesNotTreatModernButtonTypographyAsLegacy(): void
    {
        WordPressState::$themeMods = [
            TypographySettings::BUTTON_TYPOGRAPHY_SETTING => [
                'font-family' => 'Open Sans',
                'variant' => '500',
                'text-transform' => 'none',
            ],
        ];

        ActivationMigration::run();

        static::assertArrayNotHasKey(
            'letter-spacing',
            WordPressState::$themeMods[TypographySettings::BUTTON_TYPOGRAPHY_SETTING],
        );
    }
}
