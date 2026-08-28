<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Layout;

use MunicipioThemeExtensions\Customizer\ContentLayoutSettings;
use MunicipioThemeExtensions\Layout\ModuleWidth;
use MunicipioThemeExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;

final class ModuleWidthTest extends TestCase
{
    protected function setUp(): void
    {
        WordPressState::reset();
    }

    public function testItUsesTheHundredPercentDefaultForInheritedWidths(): void
    {
        static::assertSame(
            ['modularity-text', 'modularity-text-1', 'grid-md-12'],
            (new ModuleWidth())->filterClasses(['modularity-text', 'modularity-text-1', '']),
        );
    }

    public function testItUsesTheSavedDefaultForInheritedWidths(): void
    {
        WordPressState::$themeMods[ContentLayoutSettings::DEFAULT_MODULE_WIDTH_SETTING] = 'grid-md-6';

        static::assertSame(
            ['modularity-text', 'modularity-text-1', 'grid-md-6'],
            (new ModuleWidth())->filterClasses(['modularity-text', 'modularity-text-1', '']),
        );
    }

    public function testItPreservesExplicitAndCoreFallbackWidths(): void
    {
        WordPressState::$themeMods[ContentLayoutSettings::DEFAULT_MODULE_WIDTH_SETTING] = 'grid-md-6';
        $width = new ModuleWidth();

        static::assertSame(['module', 'grid-md-4'], $width->filterClasses(['module', 'grid-md-4']));
        static::assertSame(['module', 'o-grid-12'], $width->filterClasses(['module', 'o-grid-12']));
    }
}
