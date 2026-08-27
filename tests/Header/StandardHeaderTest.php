<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Header;

use MunicipioThemeExtensions\Header\StandardHeader;
use PHPUnit\Framework\TestCase;

final class StandardHeaderTest extends TestCase
{
    private StandardHeader $header;

    protected function setUp(): void
    {
        $this->header = new StandardHeader();
    }

    public function testItPreservesCurrentDefaultsWhenControllerValuesAreUnsaved(): void
    {
        $classes = ['u-width--auto', 'u-display--none@xs', 'u-display--none@sm', 'u-display--none@md'];

        static::assertSame($classes, $this->header->filterTabMenuClassList($classes, (object) []));
        static::assertSame('sm', $this->header->filterTabMenuHeight('sm', (object) []));
        static::assertSame('Search this site', $this->header->filterHeroSearchPlaceholder('Search this site', (object) []));
    }

    public function testItUsesCamelCaseCustomizerControllerValues(): void
    {
        $customizer = (object) [
            'headerBreakpoint' => 'md',
            'tabMenuButtonSize' => 'md',
            'heroSearchPlaceholder' => 'What are you looking for?',
        ];
        $classes = ['u-width--auto', 'u-display--none@xs', 'u-display--none@sm', 'u-display--none@md'];

        static::assertSame(
            ['u-width--auto', 'u-display--none@xs', 'u-display--none@sm'],
            $this->header->filterTabMenuClassList($classes, $customizer),
        );
        static::assertSame('md', $this->header->filterTabMenuHeight('sm', $customizer));
        static::assertSame('What are you looking for?', $this->header->filterHeroSearchPlaceholder(
            'Search this site',
            $customizer,
        ));
    }

    public function testItDoesNotTreatPersistedSnakeCaseKeysAsControllerData(): void
    {
        $customizer = [
            'header_breakpoint' => 'xs',
            'tab_menu_button_size' => 'lg',
            'hero_search_placeholder' => 'Legacy value',
        ];
        $classes = ['u-width--auto', 'u-display--none@xs', 'u-display--none@sm', 'u-display--none@md'];

        static::assertSame($classes, $this->header->filterTabMenuClassList($classes, $customizer));
        static::assertSame('sm', $this->header->filterTabMenuHeight('sm', $customizer));
        static::assertSame('Search this site', $this->header->filterHeroSearchPlaceholder(
            'Search this site',
            $customizer,
        ));
    }

    public function testItFallsBackSafelyForInvalidControllerValues(): void
    {
        $customizer = (object) [
            'headerBreakpoint' => 'invalid',
            'tabMenuButtonSize' => 'invalid',
            'heroSearchPlaceholder' => '   ',
        ];
        $classes = ['custom', 'u-display--none@xl'];

        static::assertSame(
            ['custom', 'u-display--none@xs', 'u-display--none@sm', 'u-display--none@md'],
            $this->header->filterTabMenuClassList($classes, $customizer),
        );
        static::assertSame('original', $this->header->filterTabMenuHeight('original', $customizer));
        static::assertSame('Search this site', $this->header->filterHeroSearchPlaceholder(
            'Search this site',
            $customizer,
        ));
    }

    public function testItPreservesUnknownFilterContracts(): void
    {
        static::assertSame('class-string', $this->header->filterTabMenuClassList('class-string'));
    }
}
