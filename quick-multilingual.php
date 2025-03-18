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
 * @author Pieter Bos
 * @since 1.0.0
 */

// Don't load the plugin file directly
defined( 'ABSPATH' ) || exit;

// Define plugin constants
define( 'QML_PLUGIN_FILE', __FILE__ );
define( 'QML_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'QML_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'QML_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'QML_VERSION', '1.5.5' );

// Include required files
require_once QML_PLUGIN_DIR . 'includes/class-quick-multilingual.php';

// Initialize the plugin
function quick_multilingual_init() {
    return Quick_Multilingual::get_instance();
}

// Start the plugin
quick_multilingual_init();