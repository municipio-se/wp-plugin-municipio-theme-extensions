<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Navigation;

use MunicipioThemeExtensions\Navigation\TreeData;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class TreeDataTest extends TestCase
{
    public function testItResolvesDeclaredBranchesAndPreservesOrderAndTargets(): void
    {
        $calls = [];
        $items = [[
            'id' => 4,
            'label' => 'Parent &amp; sibling',
            'href' => '/parent',
            'children' => true,
            'attributeList' => ['data-fetch-url' => '/branch'],
        ]];
        $data = (new TreeData())->expand($items, static function (string $url) use (&$calls): array {
            $calls[] = $url;
            return [['id' => 5, 'label' => 'Child', 'href' => 'https://example.org', 'target' => '_blank']];
        });
        static::assertSame(['/branch'], $calls);
        static::assertSame('Parent & sibling', $data[0]['label']);
        static::assertSame('/0:4/0:5', $data[0]['children'][0]['key']);
        static::assertSame('_blank', $data[0]['children'][0]['target']);
    }

    public function testRepeatedDestinationIdsHaveDistinctInstanceKeys(): void
    {
        $data = (new TreeData())->expand([['id' => 1], ['id' => 1]], static fn(): array => []);
        static::assertNotSame($data[0]['key'], $data[1]['key']);
    }

    public function testCyclesFailInsteadOfPublishingAnIncompleteTree(): void
    {
        $item = ['id' => 1, 'children' => true, 'attributeList' => ['data-fetch-url' => '/cycle']];
        $this->expectException(RuntimeException::class);
        (new TreeData())->expand([$item], static fn(): array => [$item]);
    }

    public function testMissingResolverFailsInsteadOfHidingChildren(): void
    {
        $this->expectException(RuntimeException::class);
        (new TreeData())->expand([['id' => 1, 'children' => true]], static fn(): array => []);
    }

    public function testSeedDoesNotSharePageSpecificActiveState(): void
    {
        $item = ['id' => 1, 'active' => true, 'children' => [['id' => 2, 'ancestor' => true, 'isCached' => true]]];
        static::assertSame([['id' => 1, 'children' => [['id' => 2]]]], TreeData::seed([$item]));
        static::assertTrue($item['active']);
    }
}
