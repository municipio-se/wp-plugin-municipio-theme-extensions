<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Navigation;

final class PageHideSecondaryMenu
{
    public const FIELD_KEY = 'field_page_hide_secondary_menu';
    public const FIELD_NAME = 'page_hide_secondary_menu';

    public function registerField(): void
    {
        if (!function_exists('acf_add_local_field') || $this->fieldAlreadyExists()) {
            return;
        }

        acf_add_local_field([
            'parent' => 'group_56d83cff12bb3',
            'key' => self::FIELD_KEY,
            'label' => esc_html__('Hide secondary menu', 'municipio-theme-extensions'),
            'name' => self::FIELD_NAME,
            'type' => 'true_false',
            'ui' => 1,
            'conditional_logic' => 0,
        ]);
    }

    /**
     * @param array<string, mixed> $viewData
     * @return array<string, mixed>
     */
    public function filterViewData(array $viewData): array
    {
        if (!function_exists('is_singular') || !is_singular() || !$this->isHidden()) {
            return $viewData;
        }

        if (is_array($viewData['secondaryMenu'] ?? null)) {
            $viewData['secondaryMenu']['items'] = [];
        }

        return $viewData;
    }

    public function isHidden(?int $postId = null): bool
    {
        $postId ??= function_exists('get_queried_object_id') ? (int) get_queried_object_id() : 0;

        if ($postId <= 0) {
            return false;
        }

        if (function_exists('get_field')) {
            return (bool) get_field(self::FIELD_NAME, $postId);
        }

        return function_exists('get_post_meta') && (bool) get_post_meta($postId, self::FIELD_NAME, true);
    }

    private function fieldAlreadyExists(): bool
    {
        if (
            function_exists('acf_get_field')
            && ((bool) acf_get_field(self::FIELD_KEY) || (bool) acf_get_field(self::FIELD_NAME))
        ) {
            return true;
        }

        if (!function_exists('acf_get_fields')) {
            return false;
        }

        foreach ((array) acf_get_fields('group_56d83cff12bb3') as $field) {
            if (($field['name'] ?? null) === self::FIELD_NAME) {
                return true;
            }
        }

        return false;
    }
}
