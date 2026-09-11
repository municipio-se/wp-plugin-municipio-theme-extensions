<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Layout;

use MunicipioThemeExtensions\Layout\SectionPlacement;
use PHPUnit\Framework\TestCase;

final class SectionPlacementTest extends TestCase
{
    public function testItAllowsEverySectionsModuleInAllThreeContentAreas(): void
    {
        $filter = new SectionPlacement();
        $specification = [
            'labels' => ['name' => 'Section'],
            'sidebar_incompability' => [
                'content-area-top',
                'content-area',
                'content-area-bottom',
                'right-sidebar',
                'left-sidebar',
                'left-sidebar-bottom',
                'footer-area',
                'footer-area-top',
            ],
        ];

        foreach (['mod-section-split', 'mod-section-full', 'mod-section-featured', 'mod-section-card'] as $postType) {
            $result = $filter->filterIncompatibility($specification, $postType);
            static::assertSame(
                ['right-sidebar', 'left-sidebar', 'left-sidebar-bottom', 'footer-area', 'footer-area-top'],
                $result['sidebar_incompability'],
            );
            static::assertSame($specification['labels'], $result['labels']);
            static::assertSame($result, $filter->filterIncompatibility($result, $postType));
        }
    }

    public function testItPreservesOtherModulesAndAbsentRestrictions(): void
    {
        $filter = new SectionPlacement();
        $specification = ['sidebar_incompability' => [
            'content-area',
            'content-area-top',
            'content-area-bottom',
            'footer-area',
        ]];

        foreach (['mod-text', 'mod-slider', 'mod-section-custom'] as $postType) {
            static::assertSame($specification, $filter->filterIncompatibility($specification, $postType));
        }

        foreach ([[], ['sidebar_incompability' => []], ['sidebar_incompability' => null]] as $empty) {
            static::assertSame($empty, $filter->filterIncompatibility($empty, 'mod-section-split'));
        }

        static::assertSame(
            ['sidebar_incompability' => []],
            $filter->filterIncompatibility(['sidebar_incompability' => ['content-area']], 'mod-section-split'),
        );
    }
}
