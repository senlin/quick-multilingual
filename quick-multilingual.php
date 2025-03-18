<?php
/**
 * Plugin Name: Quick Multilingual
 * Description: Quick Multilingual allows you to create multilingual brochure sites on WordPress with automatic language attributes and hreflang tags.
 * Author: <a href="https://so-wp.com">Pieter Bos</a>
 * Version: 1.5.5
 * Requires at least: 4.9
 * Tested up to: 6.6
 * Requires PHP: 7.0
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: quick-multilingual
 * GitHub Plugin URI: https://github.com/senlin/quick-multilingual
 * GitHub Branch: master
 *
 * @package WordPress
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'QuickMultilingual\\';
    $base_dir = __DIR__ . '/includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize the plugin
function quick_multilingual_init() {
    $plugin = new QuickMultilingual\Plugin();
    $plugin->run();
}
add_action('plugins_loaded', 'quick_multilingual_init');