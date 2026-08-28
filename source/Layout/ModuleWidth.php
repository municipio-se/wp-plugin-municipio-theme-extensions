<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Layout;

use MunicipioThemeExtensions\Customizer\ContentLayoutSettings;

final class ModuleWidth
{
    /**
     * Current Modularity represents an inherited width as an empty class in this list.
     * Explicit grid classes and the fallback for modules without columnWidth stay untouched.
     *
     * @param array<int, string> $classes
     * @return array<int, string>
     */
    public function filterClasses(array $classes): array
    {
        $inheritedIndex = array_search('', $classes, true);
        if ($inheritedIndex === false) {
            return $classes;
        }

        $width = (string) get_theme_mod(ContentLayoutSettings::DEFAULT_MODULE_WIDTH_SETTING, 'grid-md-12');
        if ($width === '') {
            $width = 'grid-md-12';
        }

        $classes[$inheritedIndex] = $width;

        return $classes;
    }
}
