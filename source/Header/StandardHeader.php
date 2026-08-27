<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Header;

final class StandardHeader
{
    private const BREAKPOINTS = ['xs', 'sm', 'md', 'lg', 'xl'];
    private const DEFAULT_BREAKPOINT = 'lg';
    private const DEFAULT_TAB_MENU_BUTTON_SIZE = 'sm';

    /**
     * @param mixed $classList Municipios current tab-menu class list.
     * @return mixed The unchanged value when another integration supplies a non-array contract.
     */
    public function filterTabMenuClassList(mixed $classList, mixed $customizer = null): mixed
    {
        if (!is_array($classList)) {
            return $classList;
        }

        $breakpoint = $this->controllerValue($customizer, 'headerBreakpoint', self::DEFAULT_BREAKPOINT);
        if (!is_string($breakpoint) || !in_array($breakpoint, self::BREAKPOINTS, true)) {
            $breakpoint = self::DEFAULT_BREAKPOINT;
        }

        $responsiveClasses = array_map(
            static fn(string $value): string => 'u-display--none@' . $value,
            self::BREAKPOINTS,
        );
        $classList = array_values(array_filter(
            $classList,
            static fn(mixed $class): bool => !is_string($class) || !in_array($class, $responsiveClasses, true),
        ));
        $breakpointIndex = array_search($breakpoint, self::BREAKPOINTS, true);

        foreach (array_slice(self::BREAKPOINTS, 0, $breakpointIndex) as $hiddenBreakpoint) {
            $classList[] = 'u-display--none@' . $hiddenBreakpoint;
        }

        return $classList;
    }

    public function filterTabMenuHeight(mixed $height, mixed $customizer = null): mixed
    {
        $size = $this->controllerValue($customizer, 'tabMenuButtonSize', self::DEFAULT_TAB_MENU_BUTTON_SIZE);

        return is_string($size) && in_array($size, ['sm', 'md', 'lg'], true) ? $size : $height;
    }

    public function filterHeroSearchPlaceholder(mixed $placeholder, mixed $customizer = null): mixed
    {
        $configured = $this->controllerValue($customizer, 'heroSearchPlaceholder', '');

        return is_string($configured) && trim($configured) !== '' ? $configured : $placeholder;
    }

    private function controllerValue(mixed $customizer, string $property, mixed $fallback): mixed
    {
        $values = (array) $customizer;

        return $values[$property] ?? $fallback;
    }
}
