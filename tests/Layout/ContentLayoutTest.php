<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Layout;

use MunicipioThemeExtensions\Customizer\ContentLayoutSettings;
use MunicipioThemeExtensions\Layout\ContentLayout;
use MunicipioThemeExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;

final class ContentLayoutTest extends TestCase
{
    protected function setUp(): void
    {
        WordPressState::reset();
        WordPressState::$runtime['singular'] = true;
    }

    public function testUnsavedSettingsKeepOutsideLeftDefaults(): void
    {
        $layout = new ContentLayout();
        $viewData = $layout->filterViewData(['pageTemplate' => false]);

        static::assertFalse($viewData['centerContent']);
        static::assertTrue($layout->filterOuterContentArea(true));
    }

    public function testControllerValuesCenterTheArticleAndRenderInsideOnce(): void
    {
        WordPressState::$themeMods[ContentLayoutSettings::CONTENT_AREA_PLACEMENT_SETTING] = 'inside';
        $rendered = [];
        $layout = new ContentLayout(static function (string $view, array $data) use (&$rendered): string {
            $rendered = compact('view', 'data');
            return '<div>Content area</div>';
        });

        $viewData = $layout->filterViewData([
            'pageTemplate' => false,
            'customizer' => (object) [
                'mxArticleAlignment' => 'center',
                'mxContentAreaPlacement' => 'inside',
            ],
        ]);

        ob_start();
        $layout->renderInsideContentArea();
        $markup = ob_get_clean();

        static::assertTrue($viewData['centerContent']);
        static::assertSame('<div>Content area</div>', $markup);
        static::assertSame('partials.sidebar', $rendered['view']);
        static::assertSame('content-area', $rendered['data']['id']);
        static::assertSame(['o-grid', 'municipio-theme-extensions-content-area--inside'], $rendered['data']['classes']);
    }

    public function testInsidePlacementHidesBothOuterTemplatePaths(): void
    {
        WordPressState::$themeMods[ContentLayoutSettings::CONTENT_AREA_PLACEMENT_SETTING] = 'inside';
        $layout = new ContentLayout();

        static::assertFalse($layout->filterOuterContentArea(true));
        static::assertFalse($layout->filterOuterContentArea(false));
    }

    public function testInsidePlacementRendersInThePageCenteredTemplate(): void
    {
        WordPressState::$themeMods[ContentLayoutSettings::CONTENT_AREA_PLACEMENT_SETTING] = 'inside';
        WordPressState::$runtime['pageTemplate'] = 'page-centered.blade.php';
        $layout = new ContentLayout(static fn(): string => '<div>Content area</div>');

        ob_start();
        $layout->renderInsideContentArea();

        static::assertSame('<div>Content area</div>', ob_get_clean());
    }

    public function testItDoesNotRenderInsideUnsupportedOrNonSingularTemplates(): void
    {
        WordPressState::$themeMods[ContentLayoutSettings::CONTENT_AREA_PLACEMENT_SETTING] = 'inside';
        $layout = new ContentLayout(static fn(): string => '<div>Unexpected</div>');

        WordPressState::$runtime['pageTemplate'] = 'one-page.blade.php';
        ob_start();
        $layout->renderInsideContentArea();
        static::assertSame('', ob_get_clean());

        WordPressState::$runtime['singular'] = false;
        ob_start();
        $layout->renderInsideContentArea();
        static::assertSame('', ob_get_clean());
    }
}
