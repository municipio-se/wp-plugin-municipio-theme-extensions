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

    public function testItEnqueuesTheSliderAccessibilityScriptInTheFooter(): void
    {
        (new Assets('/plugin.php', 'test-version'))->enqueue();

        static::assertArrayHasKey(
            'municipio-theme-extensions-slider-accessibility',
            WordPressState::$enqueuedScripts,
        );

        $script = WordPressState::$enqueuedScripts['municipio-theme-extensions-slider-accessibility'];

        static::assertStringEndsWith('assets/js/slider-accessibility.js', $script['src']);
        static::assertSame([], $script['deps']);
        static::assertSame('test-version', $script['ver']);
        static::assertTrue($script['args']);
    }

    public function testItEnqueuesTheModalAccessibilityScriptInTheFooter(): void
    {
        (new Assets('/plugin.php', 'test-version'))->enqueue();

        static::assertArrayHasKey(
            'municipio-theme-extensions-modal-accessibility',
            WordPressState::$enqueuedScripts,
        );

        $script = WordPressState::$enqueuedScripts['municipio-theme-extensions-modal-accessibility'];

        static::assertStringEndsWith('assets/js/modal-accessibility.js', $script['src']);
        static::assertSame([], $script['deps']);
        static::assertSame('test-version', $script['ver']);
        static::assertTrue($script['args']);
    }
}
