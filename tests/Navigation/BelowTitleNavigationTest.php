<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Navigation;

use MunicipioThemeExtensions\Customizer\SecondaryNavigationPosition;
use MunicipioThemeExtensions\Navigation\BelowTitleNavigation;
use MunicipioThemeExtensions\Navigation\PageHideSecondaryMenu;
use MunicipioThemeExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BelowTitleNavigationTest extends TestCase
{
    protected function setUp(): void
    {
        WordPressState::reset();
        WordPressState::$themeMods['secondary_navigation_position'] = SecondaryNavigationPosition::BELOW_TITLE;
        WordPressState::$runtime['singular'] = true;
        WordPressState::$runtime['queriedObject'] = (object) ['ID' => 10, 'post_type' => 'page'];
        WordPressState::$runtime['postTypes']['page'] = (object) ['hierarchical' => true];
    }

    public function testItRendersDirectPublishedChildrenInMenuOrderThroughTheButtonView(): void
    {
        WordPressState::$runtime['posts'] = [(object) ['ID' => 12], (object) ['ID' => 14]];
        WordPressState::$runtime['titles'] = [12 => 'First', 14 => 'Second'];
        WordPressState::$runtime['permalinks'] = [12 => '/first/', 14 => '/second/'];
        $renderedData = [];
        $navigation = new BelowTitleNavigation(new PageHideSecondaryMenu(), static function (
            string $view,
            array $data,
        ) use (&$renderedData): string {
            $renderedData = compact('view', 'data');
            return '<nav>Rendered</nav>';
        });

        static::assertSame('<nav>Rendered</nav>', $navigation->getMarkup());
        static::assertSame('below-title-navigation', $renderedData['view']);
        static::assertSame(
            [
                ['text' => 'First', 'href' => '/first/'],
                ['text' => 'Second', 'href' => '/second/'],
            ],
            $renderedData['data']['items'],
        );
        $arguments = WordPressState::$runtime['lastGetPostsArguments'];
        static::assertSame(10, $arguments['post_parent']);
        static::assertSame('page', $arguments['post_type']);
        static::assertSame('publish', $arguments['post_status']);
        static::assertSame(['menu_order' => 'ASC', 'ID' => 'ASC'], $arguments['orderby']);
        static::assertSame('hide_in_menu', $arguments['meta_query'][1]['key']);
    }

    public function testItDoesNotRenderOutsideSingularContext(): void
    {
        WordPressState::$runtime['singular'] = false;
        WordPressState::$runtime['posts'] = [(object) ['ID' => 12]];

        static::assertSame('', $this->navigation()->getMarkup());
        static::assertSame([], WordPressState::$runtime['lastGetPostsArguments']);
    }

    public function testItDoesNotRenderForNonHierarchicalPostTypes(): void
    {
        WordPressState::$runtime['postTypes']['page'] = (object) ['hierarchical' => false];

        static::assertSame('', $this->navigation()->getMarkup());
    }

    public function testItDoesNotRenderWhenThePageSettingHidesSecondaryNavigation(): void
    {
        WordPressState::$runtime['fields'][10][PageHideSecondaryMenu::FIELD_NAME] = true;

        static::assertSame('', $this->navigation()->getMarkup());
    }

    public function testItDoesNotRenderAnEmptyWrapper(): void
    {
        static::assertSame('', $this->navigation()->getMarkup());
    }

    public function testItsViewExposesAStablePresentationClass(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2) . '/views/below-title-navigation.blade.php');

        static::assertIsString($view);
        static::assertStringContainsString('municipio-theme-extensions-below-title-navigation', $view);
    }

    #[DataProvider('corePositions')]
    public function testItPreservesCorePositions(string $position): void
    {
        WordPressState::$themeMods['secondary_navigation_position'] = $position;
        WordPressState::$runtime['posts'] = [(object) ['ID' => 12]];

        static::assertSame('', $this->navigation()->getMarkup());
    }

    /** @return array<string, array{string}> */
    public static function corePositions(): array
    {
        return [
            'left' => ['left'],
            'right' => ['right'],
            'hidden' => ['hidden'],
        ];
    }

    private function navigation(): BelowTitleNavigation
    {
        return new BelowTitleNavigation(new PageHideSecondaryMenu(), static fn(): string => '<nav>Rendered</nav>');
    }
}
