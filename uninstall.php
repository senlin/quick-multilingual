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
$options = array(
	'so_qmp_primary_lang',
	'so_qmp_secondary_lang',
	'so_qmp_primary_hreflang',
	'so_qmp_secondary_hreflang',
	'so_qmp_language_folder_page',
	'so_qmp_number_of_pages'
);

// Delete the individual options
foreach ( $options as $option ) {
	delete_option( $option );
}

// Delete the page mapping options
for ( $i = 1; $i <= 4; $i++ ) {
	delete_option( 'so_qmp_page_mapping_' . $i );
}

// If you want to remove all options with the 'so_qmp_' prefix, you can use this code instead:
// However, be cautious as it might remove options that you didn't intend to remove if other plugins use a similar prefix.
/*
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'so_qmp_%'" );
*/
