<?php

declare(strict_types=1);

/**
 * Plugin Name:       Municipio Theme Extensions
 * Description:       Adds focused theme compatibility options to modern Municipio installations.
 * Version:           0.1.0
 * Author:            Whitespace
 * Requires PHP:      8.2
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       municipio-theme-extensions
 * Domain Path:       /languages
 */

use MunicipioThemeExtensions\Migration\ActivationMigration;
use MunicipioThemeExtensions\Plugin;

if (!defined('ABSPATH')) {
    exit();
}

$autoload = __DIR__ . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
}

add_action('plugins_loaded', static function (): void {
    load_plugin_textdomain('municipio-theme-extensions', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

if (class_exists(Plugin::class)) {
    (new Plugin(__FILE__, '0.1.0'))->register();
}

if (class_exists(ActivationMigration::class)) {
    register_activation_hook(__FILE__, [ActivationMigration::class, 'run']);
    add_action('after_setup_theme', [ActivationMigration::class, 'run']);
}
