<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/Fixtures/KirkiField.php';

use MunicipioThemeExtensions\Tests\Support\WordPressState;

WordPressState::reset();

if (!function_exists('esc_html__')) {
    function esc_html__(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

if (!function_exists('get_option')) {
    function get_option(string $option, mixed $default = false): mixed
    {
        return WordPressState::$options[$option] ?? $default;
    }
}

if (!function_exists('update_option')) {
    function update_option(string $option, mixed $value): bool
    {
        WordPressState::$options[$option] = $value;
        return true;
    }
}

if (!function_exists('get_theme_mods')) {
    function get_theme_mods(): array
    {
        return WordPressState::$themeMods;
    }
}

if (!function_exists('get_theme_mod')) {
    function get_theme_mod(string $name, mixed $default = false): mixed
    {
        return WordPressState::$themeMods[$name] ?? $default;
    }
}

if (!function_exists('set_theme_mod')) {
    function set_theme_mod(string $name, mixed $value): void
    {
        WordPressState::$themeMods[$name] = $value;
    }
}

if (!function_exists('is_singular')) {
    function is_singular(): bool
    {
        return WordPressState::$runtime['singular'];
    }
}

if (!function_exists('get_queried_object')) {
    function get_queried_object(): ?object
    {
        return WordPressState::$runtime['queriedObject'];
    }
}

if (!function_exists('get_queried_object_id')) {
    function get_queried_object_id(): int
    {
        return (int) (WordPressState::$runtime['queriedObject']->ID ?? 0);
    }
}

if (!function_exists('get_page_template_slug')) {
    function get_page_template_slug(int $postId = 0): string|false
    {
        return WordPressState::$runtime['pageTemplate'];
    }
}

if (!function_exists('get_post_type_object')) {
    function get_post_type_object(string $postType): ?object
    {
        return WordPressState::$runtime['postTypes'][$postType] ?? null;
    }
}

if (!function_exists('get_posts')) {
    function get_posts(array $arguments = []): array
    {
        WordPressState::$runtime['lastGetPostsArguments'] = $arguments;
        return WordPressState::$runtime['posts'];
    }
}

if (!function_exists('get_the_title')) {
    function get_the_title(int $postId = 0): string
    {
        return WordPressState::$runtime['titles'][$postId] ?? '';
    }
}

if (!function_exists('get_permalink')) {
    function get_permalink(int $postId = 0): string
    {
        return WordPressState::$runtime['permalinks'][$postId] ?? '';
    }
}

if (!function_exists('get_field')) {
    function get_field(string $field, int $postId = 0): mixed
    {
        return WordPressState::$runtime['fields'][$postId][$field] ?? false;
    }
}

if (!function_exists('acf_get_field')) {
    function acf_get_field(string $selector): mixed
    {
        return WordPressState::$runtime['acfFields'][$selector] ?? false;
    }
}

if (!function_exists('acf_add_local_field')) {
    function acf_add_local_field(array $field): bool
    {
        WordPressState::$runtime['acfFields'][$field['key']] = $field;
        WordPressState::$runtime['acfFields'][$field['name']] = $field;
        return true;
    }
}

if (!function_exists('acf_get_fields')) {
    function acf_get_fields(string $parent): array
    {
        return array_values(array_filter(
            WordPressState::$runtime['acfFields'],
            static fn(array $field): bool => ($field['parent'] ?? null) === $parent,
        ));
    }
}

if (!function_exists('plugins_url')) {
    function plugins_url(string $path = '', string $plugin = ''): string
    {
        return 'https://example.test/wp-content/plugins/municipio-theme-extensions/' . ltrim($path, '/');
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style(
        string $handle,
        string $src = '',
        array $deps = [],
        mixed $ver = false,
        string $media = 'all',
    ): void {
        WordPressState::$enqueuedStyles[$handle] = compact('src', 'deps', 'ver', 'media');
    }
}

if (!function_exists('wp_add_inline_style')) {
    function wp_add_inline_style(string $handle, string $data): bool
    {
        return true;
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script(
        string $handle,
        string $src = '',
        array $deps = [],
        mixed $ver = false,
        mixed $args = false,
    ): void {
        WordPressState::$enqueuedScripts[$handle] = compact('src', 'deps', 'ver', 'args');
    }
}
