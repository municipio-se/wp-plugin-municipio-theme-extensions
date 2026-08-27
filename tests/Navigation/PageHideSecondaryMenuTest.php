<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Navigation;

use MunicipioThemeExtensions\Navigation\PageHideSecondaryMenu;
use MunicipioThemeExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;

final class PageHideSecondaryMenuTest extends TestCase
{
    protected function setUp(): void
    {
        WordPressState::reset();
    }

    public function testItRegistersTheLegacyFieldContract(): void
    {
        (new PageHideSecondaryMenu())->registerField();

        $field = WordPressState::$runtime['acfFields'][PageHideSecondaryMenu::FIELD_KEY];
        static::assertSame('group_56d83cff12bb3', $field['parent']);
        static::assertSame(PageHideSecondaryMenu::FIELD_NAME, $field['name']);
        static::assertSame('true_false', $field['type']);
    }

    public function testItDoesNotReplaceAnExistingField(): void
    {
        WordPressState::$runtime['acfFields'][PageHideSecondaryMenu::FIELD_KEY] = ['owner' => 'core'];

        (new PageHideSecondaryMenu())->registerField();

        static::assertSame(
            ['owner' => 'core'],
            WordPressState::$runtime['acfFields'][PageHideSecondaryMenu::FIELD_KEY],
        );
    }

    public function testItDoesNotReplaceAnEquivalentFieldOwnedUnderAnotherKey(): void
    {
        WordPressState::$runtime['acfFields']['field_owned_by_core'] = [
            'parent' => 'group_56d83cff12bb3',
            'key' => 'field_owned_by_core',
            'name' => PageHideSecondaryMenu::FIELD_NAME,
        ];

        (new PageHideSecondaryMenu())->registerField();

        static::assertArrayNotHasKey(PageHideSecondaryMenu::FIELD_KEY, WordPressState::$runtime['acfFields']);
    }

    public function testItOnlyClearsTheCurrentSecondaryMenu(): void
    {
        WordPressState::$runtime['singular'] = true;
        WordPressState::$runtime['queriedObject'] = (object) ['ID' => 42];
        WordPressState::$runtime['fields'][42][PageHideSecondaryMenu::FIELD_NAME] = true;
        $viewData = [
            'secondaryMenu' => ['items' => [['title' => 'Child']], 'name' => 'secondary'],
            'mobileMenu' => ['items' => [['title' => 'Mobile']]],
            'breadcrumbMenu' => ['items' => [['title' => 'Home']]],
        ];

        $filtered = (new PageHideSecondaryMenu())->filterViewData($viewData);

        static::assertSame([], $filtered['secondaryMenu']['items']);
        static::assertSame('secondary', $filtered['secondaryMenu']['name']);
        static::assertSame($viewData['mobileMenu'], $filtered['mobileMenu']);
        static::assertSame($viewData['breadcrumbMenu'], $filtered['breadcrumbMenu']);
    }

    public function testItDoesNotChangeViewDataOutsideSingularContext(): void
    {
        WordPressState::$runtime['queriedObject'] = (object) ['ID' => 42];
        WordPressState::$runtime['fields'][42][PageHideSecondaryMenu::FIELD_NAME] = true;
        $viewData = ['secondaryMenu' => ['items' => [['title' => 'Child']]]];

        static::assertSame($viewData, (new PageHideSecondaryMenu())->filterViewData($viewData));
    }
}
