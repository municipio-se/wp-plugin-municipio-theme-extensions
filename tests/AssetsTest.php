<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests;

use MunicipioThemeExtensions\Assets;
use MunicipioThemeExtensions\Customizer\TypographySettings;
use MunicipioThemeExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;

final class AssetsTest extends TestCase
{
    protected function setUp(): void
    {
        WordPressState::reset();
    }

    public function testItBuildsTheButtonVariableFromTheCompositeTypographySetting(): void
    {
        WordPressState::$themeMods[TypographySettings::BUTTON_TYPOGRAPHY_SETTING] = [
            'letter-spacing' => 'normal',
        ];

        static::assertSame(
            ':root{--letter-spacing-button:normal;}',
            (new Assets('/plugin.php', 'test'))->buttonLetterSpacingCss(),
        );
    }

    public function testItFallsBackWhenTheStoredValueIsNotAValidCssDimension(): void
    {
        WordPressState::$themeMods[TypographySettings::BUTTON_TYPOGRAPHY_SETTING] = [
            'letter-spacing' => 'normal; color: red',
        ];

        static::assertSame(
            ':root{--letter-spacing-button:.1rem;}',
            (new Assets('/plugin.php', 'test'))->buttonLetterSpacingCss(),
        );
    }
}
