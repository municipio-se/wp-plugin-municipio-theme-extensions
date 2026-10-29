<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Customizer;

use Municipio\Customizer\KirkiField;
use MunicipioThemeExtensions\Customizer\MenuSettings;
use MunicipioThemeExtensions\Customizer\TreeNavigationSettings;
use MunicipioThemeExtensions\Navigation\TreeNavigation;
use MunicipioThemeExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;

final class TreeNavigationSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        WordPressState::reset();
        KirkiField::$fields = [];
    }

    public function testUnsavedAndExplicitlyDisabledChoicesLeaveViewPathsAndDataUntouched(): void
    {
        $navigation = new TreeNavigation();
        $data = ['mobileMenu' => ['items' => [1]], 'secondaryMenu' => ['items' => [2]], 'breadcrumbMenu' => [3]];
        static::assertSame(['theme'], $navigation->viewPaths(['theme']));
        static::assertSame($data, $navigation->viewData($data));
        WordPressState::$themeMods[TreeNavigationSettings::MOBILE] = false;
        WordPressState::$themeMods[TreeNavigationSettings::SIDEBAR] = false;
        static::assertSame(['theme'], $navigation->viewPaths(['theme']));
    }

    public function testSeparateActivationAndRestorationPreserveOtherSettings(): void
    {
        $navigation = new TreeNavigation();
        WordPressState::$themeMods = ['existing_menu' => 'saved', TreeNavigationSettings::MOBILE => true];
        $paths = $navigation->viewPaths(['theme']);
        static::assertCount(2, $paths);
        static::assertStringEndsWith('/views/tree/mobile', $paths[1]);
        WordPressState::$themeMods[TreeNavigationSettings::MOBILE] = false;
        WordPressState::$themeMods[TreeNavigationSettings::SIDEBAR] = true;
        static::assertStringEndsWith('/views/tree/sidebar', $navigation->viewPaths(['theme'])[1]);
        unset(WordPressState::$themeMods[TreeNavigationSettings::SIDEBAR]);
        static::assertSame(['theme'], $navigation->viewPaths(['theme']));
        static::assertSame('saved', WordPressState::$themeMods['existing_menu']);
    }

    public function testBothFieldsAreOptInInTheExistingMenuSection(): void
    {
        (new TreeNavigationSettings())->register(new class {
            public function getID(): string
            {
                return MenuSettings::SECTION_ID;
            }
        });
        static::assertCount(2, KirkiField::$fields);
        foreach (KirkiField::$fields as $field) {
            static::assertFalse($field['default']);
            static::assertSame([['type' => 'controller']], $field['output']);
        }
    }
}
