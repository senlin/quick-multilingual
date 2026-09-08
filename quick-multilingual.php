<?php
/**
 * Plugin Name:       Quick Multilingual
 * Description:       Quick Multilingual allows you to create multilingual brochure sites on WordPress with automatic language attributes, hreflang tags and canonical URLs.
 * Author:            Pieter Bos
 * Author URI:        https://so-wp.com
 * Version:            2.0.5
 * Requires at least:  6.2
 * Tested up to:       7.1
 * Requires PHP:       8.2
 * License:            GPL-2.0+
 * License URI:        http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:        quick-multilingual
 * GitHub Plugin URI:  https://github.com/senlin/quick-multilingual
 * GitHub Branch:      main
 *
 * @package  SOWP\QuickMultilingual
 * @author   Pieter Bos
 * @since    1.0.0
 */

// Don't load the plugin file directly.
defined( 'ABSPATH' ) || exit;

define( 'SO_QMP_VERSION', '2.0.5' );
define( 'SO_QMP_FILE', __FILE__ );
define( 'SO_QMP_DIR', plugin_dir_path( __FILE__ ) );
define( 'SO_QMP_URL', plugin_dir_url( __FILE__ ) );
define( 'SO_QMP_BASENAME', plugin_basename( __FILE__ ) );

// Hard floor: bail gracefully on PHP < 8.2 (WP's Requires PHP header also blocks activation).
if ( version_compare( PHP_VERSION, '8.2', '<' ) ) {
	add_action(
		'admin_notices',
		static function () {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__( 'Quick Multilingual requires PHP 8.2 or higher and has been disabled.', 'quick-multilingual' )
			);
		}
	);
	return;
}

/**
 * PSR-4 autoloader for the SOWP\QuickMultilingual namespace.
 * Composer can replace this by adding vendor/autoload.php if desired.
 */
spl_autoload_register(
	static function ( string $class ): void {
		$prefix   = 'SOWP\\QuickMultilingual\\';
		if ( ! str_starts_with( $class, $prefix ) ) {
			return;
		}
		$relative = substr( $class, strlen( $prefix ) );
		$path     = SO_QMP_DIR . 'src/' . str_replace( '\\', '/', $relative ) . '.php';
		if ( is_file( $path ) ) {
			require_once $path;
		}
	}
);

register_activation_hook( __FILE__, [ \SOWP\QuickMultilingual\Activation::class, 'activate' ] );

\SOWP\QuickMultilingual\Plugin::instance()->run();
