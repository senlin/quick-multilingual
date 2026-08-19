<?php
/**
 * Uninstall Quick Multilingual.
 *
 * Removes all plugin options from the wp_options table. Option keys mirror
 * SOWP\QuickMultilingual\Config; keep this list in sync when new options are added.
 *
 * @package SOWP\QuickMultilingual
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$so_qmp_options = [
	'so_qmp_primary_lang',
	'so_qmp_secondary_lang',
	'so_qmp_primary_hreflang',
	'so_qmp_secondary_hreflang',
	'so_qmp_language_folder_page',
	'so_qmp_number_of_pages',
];

foreach ( $so_qmp_options as $so_qmp_option ) {
	delete_option( $so_qmp_option );
}

// Lite tier supports up to 4 page mappings; delete each registered mapping.
for ( $so_qmp_i = 1; $so_qmp_i <= 4; $so_qmp_i++ ) {
	delete_option( 'so_qmp_page_mapping_' . $so_qmp_i );
}
