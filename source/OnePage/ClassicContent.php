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
     * The global compatibility option only promotes the title to visible. When disabled, the
     * current Municipio page-level choice remains authoritative.
     */
    public function filterShowTitle(mixed $showTitle, int $postId = 0): bool
    {
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
