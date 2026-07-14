<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Navigation;

use MunicipioThemeExtensions\Navigation\SecondaryMenu;
use PHPUnit\Framework\TestCase;

final class SecondaryMenuTest extends TestCase
{
    public function testItStartsAtLevelTwoByDefaultWhenThePrimaryMenuIsEmpty(): void
    {
        $secondaryMenu = new SecondaryMenu();
        $viewData = $secondaryMenu->filterViewData($this->viewData());
        $menu = $viewData['secondaryMenu'];

        static::assertSame('secondary-menu', $menu['name']);
        static::assertSame('sidebar', $menu['identifier']);
        static::assertSame($this->levelTwoItems(), $menu['items']);
        static::assertSame([['title' => 'Mobile root']], $viewData['mobileMenu']['items']);
        static::assertSame([['title' => 'Home']], $viewData['breadcrumbMenu']['items']);
    }

    public function testItPreservesMunicipiosCurrentTreeWhenTheSettingIsDisabled(): void
    {
        $secondaryMenu = new SecondaryMenu();
        $viewData = $this->viewData();
        $viewData['customizer'] = (object) [
            'municipioThemeExtensionsSecondaryMenuStartAtLevelTwo' => false,
        ];

        static::assertSame($viewData, $secondaryMenu->filterViewData($viewData));
    }

    public function testItDoesNotRemoveAnotherLevelWhenMunicipioAlreadyHandledTheTree(): void
    {
        $secondaryMenu = new SecondaryMenu();
        $viewData = $this->viewData();
        $viewData['primaryMenu'] = ['items' => [['title' => 'Primary item']]];
        $viewData['secondaryMenu'] = [
            'name' => 'secondary-menu',
            'identifier' => 'sidebar',
            'items' => $this->levelTwoItems(),
        ];

        static::assertSame($viewData, $secondaryMenu->filterViewData($viewData));
    }

    public function testItMatchesMunicipiosEmptyResultWhenNoActiveBranchCanBeFound(): void
    {
        $secondaryMenu = new SecondaryMenu();
        $viewData = $this->viewData();
        $viewData['secondaryMenu']['items'][0]['ancestor'] = false;

        static::assertSame([], $secondaryMenu->filterViewData($viewData)['secondaryMenu']['items']);
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(): array
    {
        return [
            'customizer' => (object) [],
            'primaryMenu' => ['items' => []],
            'secondaryMenu' => $this->fullMenu(),
            'mobileMenu' => ['items' => [['title' => 'Mobile root']]],
            'breadcrumbMenu' => ['items' => [['title' => 'Home']]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fullMenu(): array
    {
        return [
            'name' => 'secondary-menu',
            'identifier' => 'sidebar',
            'items' => [
                [
                    'title' => 'Level one',
                    'ancestor' => true,
                    'children' => $this->levelTwoItems(),
                ],
                [
                    'title' => 'Other branch',
                    'ancestor' => false,
                    'children' => [],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function levelTwoItems(): array
    {
        return [
            [
                'title' => 'Level two',
                'ancestor' => true,
                'children' => [
                    [
                        'title' => 'Level three',
                        'ancestor' => false,
                        'children' => [],
                    ],
                ],
            ],
        ];
    }
}
