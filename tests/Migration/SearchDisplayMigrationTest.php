<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Migration;

use MunicipioThemeExtensions\Migration\SearchDisplayMigration;
use MunicipioThemeExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;

final class SearchDisplayMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        WordPressState::reset();
    }

    public function testItMapsLegacyDesktopSearchPlacementsToCurrentMobilePlacements(): void
    {
        WordPressState::$themeMods = [
            'header_breakpoint' => 'md',
            'search_display' => ['hero', 'header_sub', 'header', 'mainmenu'],
        ];

        SearchDisplayMigration::run(WordPressState::$themeMods);

        static::assertSame(
            ['hero', 'header_sub', 'header', 'mainmenu', 'header_mobile', 'header_mobile_sub'],
            WordPressState::$themeMods['search_display'],
        );
        static::assertSame('md', WordPressState::$themeMods['header_breakpoint']);
    }

    public function testItPreservesExistingMobilePlacementsAndIsIdempotent(): void
    {
        WordPressState::$themeMods = [
            'tab_menu_button_size' => 'md',
            'search_display' => ['header', 'header_mobile', 'header_sub', 'header_mobile_sub'],
        ];

        SearchDisplayMigration::run(WordPressState::$themeMods);
        $afterFirstRun = WordPressState::$themeMods;
        SearchDisplayMigration::run(WordPressState::$themeMods);

        static::assertSame($afterFirstRun, WordPressState::$themeMods);
    }

    public function testItDoesNotChangeModernOrMalformedSearchSelections(): void
    {
        WordPressState::$themeMods = [
            'search_display' => ['header'],
        ];

        SearchDisplayMigration::run(WordPressState::$themeMods);

        static::assertSame(['header'], WordPressState::$themeMods['search_display']);

        WordPressState::reset();
        WordPressState::$themeMods = [
            'header_breakpoint' => 'md',
            'search_display' => ['header', 17],
        ];

        SearchDisplayMigration::run(WordPressState::$themeMods);

        static::assertSame(['header', 17], WordPressState::$themeMods['search_display']);
    }
}
