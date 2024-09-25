<?php
/**
 * Plugin Name: Quick Multilingual
 * Description: Quick Multilingual allows you to create multilingual brochure sites on WordPress with automatic language attributes and hreflang tags.

 * Author: <a href="https://so-wp.com">Pieter Bos</a>
 * Version: 1.5.2

 * Requires at least: 4.9
 * Tested up to: 6.6
 * Requires PHP: 7.0

 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt

 * Text Domain: quick-multilingual

 * GitHub Plugin URI: https://github.com/senlin/quick-multilingual
 * GitHub Branch: master

 * @package WordPress
 * @author Pieter Bos
 * @since 1.0.0
 */

// don't load the plugin file directly
defined( 'ABSPATH' ) || exit;

/**
 * Load plugin text domain for translation.
 */
function so_qmp_load_textdomain() {
	load_plugin_textdomain( 'quick-multilingual', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'so_qmp_load_textdomain' );

/**
 * Add settings page to WordPress admin.
 */
/**
 * Register and define settings.
 */
// Register the new settings and add the options page
function so_qmp_register_settings() {
	register_setting( 'qmp-settings-group', 'so_qmp_primary_lang' );
	register_setting( 'qmp-settings-group', 'so_qmp_secondary_lang' );
	register_setting( 'qmp-settings-group', 'so_qmp_primary_hreflang' );
	register_setting( 'qmp-settings-group', 'so_qmp_secondary_hreflang' );
	register_setting( 'qmp-settings-group', 'so_qmp_language_folder_page' );
	register_setting( 'qmp-settings-group', 'so_qmp_number_of_pages' );

	// Register page mappings settings
	for ( $i = 1; $i <= 4; $i++ ) {
		register_setting( 'qmp-page-translations-group', 'so_qmp_page_mapping_' . $i );
	}
}
add_action( 'admin_init', 'so_qmp_register_settings' );

// Add the options page
function so_qmp_create_options_page() {
	add_options_page(
		__( 'Quick Multilingual Settings', 'quick-multilingual' ),
		__( 'Quick Multilingual', 'quick-multilingual' ),
		'manage_options',
		'quick-multilingual',
		'so_qmp_options_page_html'
	);
}
add_action( 'admin_menu', 'so_qmp_create_options_page' );

// Render the options page
function so_qmp_options_page_html() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Quick Multilingual Settings', 'quick-multilingual' ); ?></h1>
		<p><?php esc_html_e( 'Quick Multilingual is a WordPress plugin designed to enhance multilingual websites by adjusting the HTML lang attribute and adding hreflang tags.', 'quick-multilingual' ); ?></p>
		<p><?php esc_html_e( 'This plugin allows you to set the HTML language attribute for up to two languages, custom hreflang codes for those languages, redirect the "language folder" (the secondary language placeholder page) to its respective homepage, map up to 4 primary language pages to their secondary page translations and properly handle language attributes for better SEO and user experience.', 'quick-multilingual' ); ?></p>

		<h2 class="nav-tab-wrapper">
			<a href="#general-settings" class="nav-tab"><?php esc_html_e( 'General Settings', 'quick-multilingual' ); ?></a>
			<a href="#page-translations" class="nav-tab"><?php esc_html_e( 'Page Translations', 'quick-multilingual' ); ?></a>
		</h2>

		<div id="general-settings" class="qmp-tab-content">
			<h3><?php printf( esc_html(__( 'Find all HTML lang attributes %s.', 'quick-multilingual' ), '<a href="https://gist.github.com/JamieMason/3748498" target="_blank">' . __( 'here', 'quick-multilingual' ) . '</a>' ) ); ?></h3>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'qmp-settings-group' );
				do_settings_sections( 'qmp-settings-group' );
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'HTML lang attribute primary language', 'quick-multilingual' ); ?></th>
						<td><input type="text" name="so_qmp_primary_lang" value="<?php echo esc_attr( get_option('so_qmp_primary_lang') ); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'HTML lang attribute secondary language', 'quick-multilingual' ); ?></th>
						<td><input type="text" name="so_qmp_secondary_lang" value="<?php echo esc_attr( get_option('so_qmp_secondary_lang') ); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Hreflang primary language', 'quick-multilingual' ); ?></th>
						<td><input type="text" name="so_qmp_primary_hreflang" value="<?php echo esc_attr( get_option('so_qmp_primary_hreflang') ); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Hreflang secondary language', 'quick-multilingual' ); ?></th>
						<td><input type="text" name="so_qmp_secondary_hreflang" value="<?php echo esc_attr( get_option('so_qmp_secondary_hreflang') ); ?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Language Folder Page', 'quick-multilingual' ); ?></th>
						<td>
							<?php
							wp_dropdown_pages(array(
								'name' => 'so_qmp_language_folder_page',
								'selected' => esc_attr( get_option('so_qmp_language_folder_page') ),
								'show_option_none' => esc_html(__( '— Select —', 'quick-multilingual' ) ),
								'option_none_value' => '0'
							));
							?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Number of Pages to Map', 'quick-multilingual' ); ?></th>
						<td>
							<input type="number" id="number_of_pages" name="so_qmp_number_of_pages" value="<?php echo esc_attr( get_option('so_qmp_number_of_pages', 1) ); ?>" min="1" max="4" />
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>

		<div id="page-translations" class="qmp-tab-content" style="display:none;">
			<h3><?php esc_html_e( 'Here you can map the pages of the primary language to the secondary language.', 'quick-multilingual' ); ?></h3>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'qmp-page-translations-group' );
				do_settings_sections( 'qmp-page-translations-group' );

				// Display the number of page mappings set by the user
				$number_of_pages = intval( get_option( 'so_qmp_number_of_pages', 1 ) );

				for ( $i = 1; $i <= $number_of_pages; $i++ ) {
					// Ensure the options are always arrays
					$primary_page = get_option( 'so_qmp_page_mapping_' . $i )['primary'] ?? 0;
					$secondary_page = get_option( 'so_qmp_page_mapping_' . $i )['secondary'] ?? 0;
					?>
					<table class="form-table" id="page-translations-table">
						<thead>
							<tr>
								<th><?php esc_html_e('Page', 'quick-multilingual'); ?></th>
								<th><?php esc_html_e('Primary Language Page', 'quick-multilingual'); ?></th>
								<th><?php esc_html_e('Secondary Language Page', 'quick-multilingual'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$number_of_pages = intval(get_option('so_qmp_number_of_pages', 1));
							for ($i = 1; $i <= $number_of_pages; $i++) {
								// Ensure the options are always arrays
								$primary_page = get_option('so_qmp_page_mapping_' . $i)['primary'] ?? 0;
								$secondary_page = get_option('so_qmp_page_mapping_' . $i)['secondary'] ?? 0;
								?>
								<tr valign="top" class="page-mapping-row">
									<td><?php echo sprintf( esc_html(__('Page %d', 'quick-multilingual'), $i) ); ?></td>
									<td>
										<?php
										// Exclude the language folder page and its children
										$language_folder_page_id = get_option('so_qmp_language_folder_page');
										$exclude_pages = [$language_folder_page_id];
										$children_pages = get_pages(['child_of' => $language_folder_page_id]);
										foreach ($children_pages as $child_page) {
											$exclude_pages[] = $child_page->ID;
										}

										wp_dropdown_pages(array(
											'name' => 'so_qmp_page_mapping_' . $i . '[primary]',
											'selected' => esc_attr($primary_page),
											'exclude' => implode(',', $exclude_pages),
											'show_option_none' => esc_html( __('— Select —', 'quick-multilingual') ),
											'option_none_value' => '0'
										));
										?>
									</td>
									<td>
										<?php
										// Only show children of the language folder page
										wp_dropdown_pages(array(
											'name' => 'so_qmp_page_mapping_' . $i . '[secondary]',
											'selected' => esc_attr($secondary_page),
											'child_of' => $language_folder_page_id,
											'show_option_none' => esc_html( __('— Select —', 'quick-multilingual') ),
											'option_none_value' => '0'
										));
										?>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
					<?php
				}
				?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
	</div>
	<script>
		jQuery(document).ready(function($) {
			// Restore the last active tab from localStorage
			var activeTab = localStorage.getItem('so_qmp_active_tab');
			if (activeTab) {
				$('.nav-tab').removeClass('nav-tab-active');
				$('.qmp-tab-content').hide();
				$('a[href="' + activeTab + '"]').addClass('nav-tab-active');
				$(activeTab).show();
			} else {
				$('.nav-tab').first().click(); // Activate the first tab by default if no active tab is found.
			}

			// Handle tab clicks
			$('.nav-tab').click(function(e) {
				e.preventDefault();
				$('.nav-tab').removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');
				$('.qmp-tab-content').hide();
				$($(this).attr('href')).show();
				// Save the active tab in localStorage
				localStorage.setItem('so_qmp_active_tab', $(this).attr('href'));
			});

			// Handle change in number of pages
			$('#number_of_pages').change(function() {
				var numPages = parseInt($(this).val(), 10);
				var $table = $('#page-translations-table');
				var $rows = $table.find('.page-mapping-row');

				// Remove extra rows
				$rows.slice(numPages).remove();

				// Add new rows if necessary
				for (var i = $rows.length; i < numPages; i++) {
					$table.append(`
						<tr valign="top" class="page-mapping-row">
							<td><?php echo sprintf( esc_html( __( 'Page %d', 'quick-multilingual' ), ' + (i + 1) + ' ) ); ?></td>
							<td>
								<?php
								// Exclude the language folder page and its children
								$language_folder_page_id = get_option( 'so_qmp_language_folder_page' );
								$exclude_pages = [$language_folder_page_id];
								$children_pages = get_pages(['child_of' => $language_folder_page_id]);
								foreach ($children_pages as $child_page) {
									$exclude_pages[] = $child_page->ID;
								}
								?>
								<select name="so_qmp_page_mapping_' + (i + 1) + '[primary]">
									<option value="0"><?php esc_html_e( '— Select —', 'quick-multilingual' ); ?></option>
									<?php
									foreach ( get_pages( array( 'exclude' => implode(',', $exclude_pages) ) ) as $page ) {
										echo '<option value="' . esc_attr( $page->ID ) . '">' . esc_html( $page->post_title ) . '</option>';
									}
									?>
								</select>
							</td>
							<td>
								<select name="so_qmp_page_mapping_' + (i + 1) + '[secondary]">
									<option value="0"><?php esc_html_e( '— Select —', 'quick-multilingual' ); ?></option>
									<?php
									$language_folder_page_id = get_option( 'so_qmp_language_folder_page' );
									foreach ( get_pages( array( 'child_of' => $language_folder_page_id ) ) as $page ) {
										echo '<option value="' . esc_attr( $page->ID ) . '">' . esc_html( $page->post_title ) . '</option>';
									}
									?>
								</select>
							</td>
						</tr>
					`);
				}
			});
		});
	</script>
	<?php
}


// Get the corresponding page ID from the mapping
function so_qmp_get_mapped_page_id( $page_id, $language = 'primary' ) {
	for ( $i = 1; $i <= 4; $i++ ) {
		$page_mapping = get_option( 'so_qmp_page_mapping_' . $i );
		if ( $page_mapping && isset($page_mapping[$language]) && $page_mapping[$language] == $page_id ) {
			return $page_mapping[$language === 'primary' ? 'secondary' : 'primary'];
		}
	}
	return null; // Return null if no mapping is found
}

/**
 * Outputs hreflang tags in the <head> section of the website.
 */
function so_qmp_add_hreflang_tags() {
	// Retrieve the settings
	$primary_hreflang = get_option('so_qmp_primary_hreflang'); // e.g., 'no'
	$secondary_hreflang = get_option('so_qmp_secondary_hreflang'); // e.g., 'en'

	// Retrieve the current page ID
	$current_page_id = get_queried_object_id();

	// Get the permalink for the current page
	$current_page_url = get_permalink($current_page_id);

	// Initialize variables for page mapping
	$is_mapped = false;
	$mapped_primary_id = null;
	$mapped_secondary_id = null;

	// Check the page mappings
	for ($i = 1; $i <= 4; $i++) {
		$page_mapping = get_option('so_qmp_page_mapping_' . $i);
		if ($page_mapping) {
			// Check if the current page ID matches the primary or secondary mapped page ID
			if (isset($page_mapping['primary']) && $page_mapping['primary'] == $current_page_id) {
				$mapped_primary_id = $current_page_id;
				$mapped_secondary_id = isset($page_mapping['secondary']) ? $page_mapping['secondary'] : null;
				$is_mapped = true;
				break;
			} elseif (isset($page_mapping['secondary']) && $page_mapping['secondary'] == $current_page_id) {
				$mapped_secondary_id = $current_page_id;
				$mapped_primary_id = isset($page_mapping['primary']) ? $page_mapping['primary'] : null;
				$is_mapped = true;
				break;
			}
		}
	}

	// Output hreflang tags for mapped pages
	if ($is_mapped) {
		// Get URLs for primary and secondary mapped pages
		$primary_url = $mapped_primary_id ? get_permalink($mapped_primary_id) : '';
		$secondary_url = $mapped_secondary_id ? get_permalink($mapped_secondary_id) : '';

		if ($mapped_primary_id && $primary_url) {
			// Output hreflang tag for primary language
			echo '<link rel="alternate" hreflang="' . esc_attr($primary_hreflang) . '" href="' . esc_url($primary_url) . '" />' . PHP_EOL;
		}
		if ($mapped_secondary_id && $secondary_url) {
			// Output hreflang tag for secondary language
			echo '<link rel="alternate" hreflang="' . esc_attr($secondary_hreflang) . '" href="' . esc_url($secondary_url) . '" />' . PHP_EOL;
		}

		// Output x-default hreflang tag pointing to the primary URL (if exists) or secondary URL as fallback
		$x_default_url = $primary_url ? $primary_url : ($secondary_url ? $secondary_url : $current_page_url);
		echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($x_default_url) . '" />' . PHP_EOL;

	} else {
		// Determine if the current page is under the secondary language path
		$current_lang_prefix = so_qmp_get_current_language_prefix();
		$is_secondary_lang_page = ($current_lang_prefix === '/' . $secondary_hreflang . '/');

		// Output hreflang tags for non-mapped pages
		if ($is_secondary_lang_page) {
			// Output hreflang tag for secondary language
			echo '<link rel="alternate" hreflang="' . esc_attr($secondary_hreflang) . '" href="' . esc_url($current_page_url) . '" />' . PHP_EOL;
		} else {
			// Output hreflang tag for primary language
			echo '<link rel="alternate" hreflang="' . esc_attr($primary_hreflang) . '" href="' . esc_url($current_page_url) . '" />' . PHP_EOL;
		}

		// Output x-default hreflang tag pointing to the current page URL
		echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($current_page_url) . '" />' . PHP_EOL;
	}
}
add_action('wp_head', 'so_qmp_add_hreflang_tags', 1);

/**
 * Set the HTML lang attribute based on the current language.
 */
function so_qmp_set_html_lang() {
	$primary_lang = get_option( 'so_qmp_primary_lang' );
	$secondary_lang = get_option( 'so_qmp_secondary_lang' );

	$current_lang_prefix = so_qmp_get_current_language_prefix();
	$html_lang = ( $current_lang_prefix === '/' . get_option( 'so_qmp_secondary_hreflang' ) . '/' ) ? $secondary_lang : $primary_lang;

	echo ' lang="' . esc_attr( $html_lang ) . '"';
}
add_filter( 'language_attributes', 'so_qmp_set_html_lang', 10 );

/**
 * Get the current language prefix based on the URL.
 */
function so_qmp_get_current_language_prefix() {
	$secondary_hreflang = get_option( 'so_qmp_secondary_hreflang' );
	$secondary_lang_prefix = '/' . $secondary_hreflang . '/';

	$current_url = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
	return ( strpos( $current_url, $secondary_lang_prefix ) === 0 ) ? $secondary_lang_prefix : '';
}

/**
 * Redirect the language folder page to the first mapped secondary language page.
 */
function so_qmp_redirect_language_folder_to_secondary_homepage() {
	// Get the language folder page ID from the plugin's settings.
	$language_folder_page_id = get_option('so_qmp_language_folder_page');

	// Ensure the language folder page ID is set and matches the current page.
	if ($language_folder_page_id && is_page($language_folder_page_id)) {
		// Retrieve the first page mapping.
		$page_mapping = get_option('so_qmp_page_mapping_1');

		// Check if the mapping is set and contains a secondary page.
		if ($page_mapping && isset($page_mapping['secondary']) && !empty($page_mapping['secondary'])) {
			$first_mapped_secondary_page_id = $page_mapping['secondary'];

			// Get the permalink of the first mapped secondary page.
			$redirect_url = get_permalink($first_mapped_secondary_page_id);

			// Check if the redirect URL is valid.
			if ($redirect_url) {
				// Perform the redirect to the secondary language page.
				wp_redirect(esc_url($redirect_url), 301);
				exit;
			}
		}
	}
}

// Hook the function to the template_redirect action to ensure it runs before the template is loaded.
add_action('template_redirect', 'so_qmp_redirect_language_folder_to_secondary_homepage');

/**
 * Add settings link to plugin page.
 */
function so_qmp_add_settings_link( $links ) {
	$settings_link = '<a href="options-general.php?page=quick-multilingual">' . esc_html( __( 'Settings', 'quick-multilingual' ) ) . '</a>';
	array_push( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'so_qmp_add_settings_link' );
