<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Customizer;

use MunicipioThemeExtensions\Customizer\SecondaryNavigationPosition;
use PHPUnit\Framework\TestCase;

final class SecondaryNavigationPositionTest extends TestCase
{
    public function testItAppendsBelowTitleWithoutChangingCoreChoices(): void
    {
        $arguments = [
            'settings' => 'secondary_navigation_position',
            'choices' => [
                'left' => 'Left',
                'right' => 'Right',
                'hidden' => 'Hidden',
            ],
        ];

        $filtered = (new SecondaryNavigationPosition())->filterFieldArguments($arguments);

        static::assertSame('Left', $filtered['choices']['left']);
        static::assertSame('Right', $filtered['choices']['right']);
        static::assertSame('Hidden', $filtered['choices']['hidden']);
        static::assertSame('Below title', $filtered['choices']['below_title']);
        static::assertSame('secondary_navigation_position', $filtered['settings']);
    }
}
