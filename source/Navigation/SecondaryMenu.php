<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Navigation;

use MunicipioThemeExtensions\Customizer\MenuSettings;

final class SecondaryMenu
{
    private const CONTROLLER_PROPERTY = 'municipioThemeExtensionsSecondaryMenuStartAtLevelTwo';

    /**
     * Filters the final controller data through Municipios active template extension point. Modern
     * Municipio reads the protected controller data directly, so the legacy per-property filters in
     * BaseController::getData() do not run in the current render path.
     *
     * @param array<string, mixed> $viewData
     * @return array<string, mixed>
     */
    public function filterViewData(array $viewData): array
    {
        $primaryMenuItems = (array) ($viewData['primaryMenu']['items'] ?? []);
        $secondaryMenu = (array) ($viewData['secondaryMenu'] ?? []);
        $customizer = $viewData['customizer'] ?? [];

        if (!$this->startsAtLevelTwo($customizer) || count($primaryMenuItems) > 0) {
            return $viewData;
        }

        $viewData['secondaryMenu'] = $this->removeTopLevel($secondaryMenu);

        return $viewData;
    }

    /**
     * Mirrors Municipios RemoveTopLevel decorator for the one configuration where core skips it.
     *
     * @param array<string, mixed> $menu
     * @return array<string, mixed>
     */
    private function removeTopLevel(array $menu): array
    {
        $items = (array) ($menu['items'] ?? []);

        $menu['items'] = [];

        foreach ($items as $item) {
            $children = $item['children'] ?? null;
            if (($item['ancestor'] ?? false) !== true || !is_array($children)) {
                continue;
            }

            $menu['items'] = $children;
        }

        return $menu;
    }

    private function startsAtLevelTwo(mixed $customizer): bool
    {
        $values = (array) $customizer;
        $value =
            $values[self::CONTROLLER_PROPERTY] ?? $values[MenuSettings::SECONDARY_MENU_START_LEVEL_SETTING] ?? true;

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true;
    }
}
