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

if (!function_exists('plugins_url')) {
    function plugins_url(string $path = '', string $plugin = ''): string
    {
        return 'https://example.test/wp-content/plugins/municipio-theme-extensions/' . ltrim($path, '/');
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style(string $handle, string $src = '', array $deps = [], mixed $ver = false, string $media = 'all'): void
    {
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
    function wp_enqueue_script(string $handle, string $src = '', array $deps = [], mixed $ver = false, mixed $args = false): void
    {
        WordPressState::$enqueuedScripts[$handle] = compact('src', 'deps', 'ver', 'args');
    }
}
