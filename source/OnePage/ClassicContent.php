<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\OnePage;

use MunicipioThemeExtensions\Customizer\OnePageSettings;

final class ClassicContent
{
    private const CONTROLLER_PROPERTY = 'municipioCustomizerOnepageBodyText';
    private const TEMPLATE = 'one-page.blade.php';

    /**
     * Lets Municipios current One Page template render filtered classic content through its
     * existing content branch. Block content already takes that branch and remains untouched.
     *
     * @param array<string, mixed> $viewData
     * @return array<string, mixed>
     */
    public function filterViewData(array $viewData): array
    {
        if (
            ($viewData['pageTemplate'] ?? null) !== self::TEMPLATE
            || !$this->isEnabled($viewData['customizer'] ?? null)
            || ($viewData['hasBlocks'] ?? false) === true
            || !is_object($viewData['post'] ?? null)
        ) {
            return $viewData;
        }

        $viewData['hasBlocks'] = true;

        return $viewData;
    }

    /**
     * The global compatibility option only promotes the title to visible. The static front page
     * keeps Municipios page-level choice so an imported global value does not expose its internal
     * WordPress title above the front-page modules.
     */
    public function filterShowTitle(mixed $showTitle, int $postId = 0): bool
    {
        if ($postId > 0 && $postId === (int) get_option('page_on_front', 0)) {
            return (bool) $showTitle;
        }

        return (bool) $showTitle || $this->isEnabled();
    }

    private function isEnabled(mixed $customizer = null): bool
    {
        $values = (array) $customizer;
        $value =
            $values[self::CONTROLLER_PROPERTY] ?? $values[OnePageSettings::DISPLAY_CLASSIC_CONTENT_SETTING] ?? get_theme_mod(
                OnePageSettings::DISPLAY_CLASSIC_CONTENT_SETTING,
                false,
            );

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
    }
}
