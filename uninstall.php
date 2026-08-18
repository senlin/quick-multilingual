<?php
/**
 * Uninstall Quick Multilingual
 *
 * @package Quick_Multilingual
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Define the option names to be deleted
$so_qmp_options = array(
	'so_qmp_primary_lang',
	'so_qmp_secondary_lang',
	'so_qmp_primary_hreflang',
	'so_qmp_secondary_hreflang',
	'so_qmp_language_folder_page',
	'so_qmp_number_of_pages',
);

// Delete the individual options
foreach ( $so_qmp_options as $so_qmp_option ) {
	delete_option( $so_qmp_option );
}

// Delete the page mapping options
for ( $so_qmp_i = 1; $so_qmp_i <= 4; $so_qmp_i++ ) {
	delete_option( 'so_qmp_page_mapping_' . $so_qmp_i );
}

// If you want to remove all options with the 'so_qmp_' prefix, you can use this code instead:
// However, be cautious as it might remove options that you didn't intend to remove if other plugins use a similar prefix.
/*
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'so_qmp_%'" );
*/
