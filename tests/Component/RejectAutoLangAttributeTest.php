<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Component;

use MunicipioThemeExtensions\Component\RejectAutoLangAttribute;
use PHPUnit\Framework\TestCase;

final class RejectAutoLangAttributeTest extends TestCase
{
    public function testItRemovesTheLiteralAutoLangValueOnTheArticleElement(): void
    {
        $filter = new RejectAutoLangAttribute();

        $data = $filter->filterData([
            'id' => 'article',
            'attributeList' => ['lang' => 'auto'],
        ]);

        static::assertArrayNotHasKey('lang', $data['attributeList']);
    }

    public function testItLeavesARealLanguageValueUnchanged(): void
    {
        $filter = new RejectAutoLangAttribute();

        $data = $filter->filterData([
            'id' => 'article',
            'attributeList' => ['lang' => 'en'],
        ]);

        static::assertSame('en', $data['attributeList']['lang']);
    }

    public function testItLeavesOtherElementsUnchanged(): void
    {
        $filter = new RejectAutoLangAttribute();

        $data = $filter->filterData([
            'id' => 'something-else',
            'attributeList' => ['lang' => 'auto'],
        ]);

        static::assertSame('auto', $data['attributeList']['lang']);
    }

    public function testItHandlesMissingAttributeList(): void
    {
        $filter = new RejectAutoLangAttribute();

        $data = $filter->filterData(['id' => 'article']);

        static::assertSame(['id' => 'article'], $data);
    }
}
