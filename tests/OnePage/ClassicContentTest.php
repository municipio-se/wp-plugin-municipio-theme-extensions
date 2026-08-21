<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\OnePage;

use MunicipioThemeExtensions\Customizer\OnePageSettings;
use MunicipioThemeExtensions\OnePage\ClassicContent;
use MunicipioThemeExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;

final class ClassicContentTest extends TestCase
{
    protected function setUp(): void
    {
        WordPressState::reset();
    }

    public function testUnsavedSettingKeepsCurrentMunicipioBehaviour(): void
    {
        $extension = new ClassicContent();
        $viewData = $this->viewData(false);

        static::assertSame($viewData, $extension->filterViewData($viewData));
        static::assertFalse($extension->filterShowTitle(false, 42));
        static::assertTrue($extension->filterShowTitle(true, 42));
    }

    public function testExplicitlyDisabledSettingKeepsCurrentMunicipioBehaviour(): void
    {
        WordPressState::$themeMods[OnePageSettings::DISPLAY_CLASSIC_CONTENT_SETTING] = '0';
        $extension = new ClassicContent();
        $viewData = $this->viewData(false);

        static::assertSame($viewData, $extension->filterViewData($viewData));
        static::assertFalse($extension->filterShowTitle(false, 42));
    }

    public function testEnabledSettingUsesTheCurrentContentAndTitlePaths(): void
    {
        WordPressState::$themeMods[OnePageSettings::DISPLAY_CLASSIC_CONTENT_SETTING] = '1';
        $extension = new ClassicContent();
        $viewData = $extension->filterViewData($this->viewData(false));

        static::assertTrue($viewData['hasBlocks']);
        static::assertTrue($extension->filterShowTitle(false, 42));
    }

    public function testCustomizerControllerValueSupportsPreviewWithoutSaving(): void
    {
        $extension = new ClassicContent();
        $viewData = $this->viewData(false);
        $viewData['customizer'] = (object) ['municipioCustomizerOnepageBodyText' => true];

        static::assertTrue($extension->filterViewData($viewData)['hasBlocks']);
    }

    public function testBlockContentIsNotRoutedThroughASecondRenderPath(): void
    {
        WordPressState::$themeMods[OnePageSettings::DISPLAY_CLASSIC_CONTENT_SETTING] = true;
        $extension = new ClassicContent();
        $viewData = $this->viewData(true);

        static::assertSame($viewData, $extension->filterViewData($viewData));
    }

    public function testOtherTemplatesRemainUnchanged(): void
    {
        WordPressState::$themeMods[OnePageSettings::DISPLAY_CLASSIC_CONTENT_SETTING] = true;
        $extension = new ClassicContent();
        $viewData = $this->viewData(false);
        $viewData['pageTemplate'] = 'page.blade.php';

        static::assertSame($viewData, $extension->filterViewData($viewData));
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(bool $hasBlocks): array
    {
        return [
            'pageTemplate' => 'one-page.blade.php',
            'hasBlocks' => $hasBlocks,
            'post' => (object) ['postContentFiltered' => '<p>Classic content</p>'],
            'customizer' => (object) [],
        ];
    }
}
