<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Modularity;

use MunicipioThemeExtensions\Modularity\UniqueModuleId;
use MunicipioThemeExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;

final class UniqueModuleIdTest extends TestCase
{
    protected function setUp(): void
    {
        WordPressState::reset();
    }

    public function testItLeavesTheFirstOccurrenceOfAnIdUnchanged(): void
    {
        $filter = new UniqueModuleId();

        static::assertSame(
            '<div id="mod-spacer-11049-6a97a420802da" class="modularity-spacer">',
            $filter->filterBeforeModule('<div id="mod-spacer-11049-6a97a420802da" class="modularity-spacer">'),
        );
    }

    public function testItRewritesTheIdOnACollision(): void
    {
        $filter = new UniqueModuleId();
        $markup = '<div id="mod-spacer-11049-6a97a420802da" class="modularity-spacer">';

        $first = $filter->filterBeforeModule($markup);
        $second = $filter->filterBeforeModule($markup);

        static::assertSame($markup, $first);
        static::assertNotSame($markup, $second);
        static::assertSame(
            '<div id="mod-spacer-11049-6a97a420802da-1" class="modularity-spacer">',
            $second,
        );
    }

    public function testItKeepsRewrittenIdsUniqueAcrossRepeatedCollisions(): void
    {
        $filter = new UniqueModuleId();
        $markup = '<div id="mod-spacer-11049-6a97a420802da" class="modularity-spacer">';

        $filter->filterBeforeModule($markup);
        $second = $filter->filterBeforeModule($markup);
        $third = $filter->filterBeforeModule($markup);

        static::assertNotSame($second, $third);
    }

    public function testItLeavesMarkupWithoutAnIdUnchanged(): void
    {
        $filter = new UniqueModuleId();
        $markup = '<div class="modularity-spacer">';

        static::assertSame($markup, $filter->filterBeforeModule($markup));
    }
}
