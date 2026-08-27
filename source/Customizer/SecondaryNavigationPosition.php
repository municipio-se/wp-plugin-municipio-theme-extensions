<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Customizer;

final class SecondaryNavigationPosition
{
    public const FIELD_ARGUMENTS_FILTER = 'Municipio/Customizer/SecondaryNavigationPosition/FieldArguments';
    public const BELOW_TITLE = 'below_title';

    /**
     * @param array<string, mixed> $fieldArguments
     * @return array<string, mixed>
     */
    public function filterFieldArguments(array $fieldArguments): array
    {
        $choices = (array) ($fieldArguments['choices'] ?? []);
        $choices[self::BELOW_TITLE] = esc_html__('Below title', 'municipio-theme-extensions');
        $fieldArguments['choices'] = $choices;

        return $fieldArguments;
    }
}
