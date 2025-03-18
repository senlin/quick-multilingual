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

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class QuickMultilingual {

    public function __construct() {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'create_options_page']);
        add_action('wp_head', [$this, 'add_hreflang_tags'], 1);
        add_filter('language_attributes', [$this, 'set_html_lang'], 100);
        add_action('template_redirect', [$this, 'redirect_language_folder_to_secondary_homepage']);
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_settings_link']);
    }

    public function load_textdomain() {
        load_plugin_textdomain('quick-multilingual', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function enqueue_admin_scripts($hook) {
        if ('settings_page_quick-multilingual' !== $hook) {
            return;
        }

        wp_enqueue_script('so_qmp_admin_script', plugins_url('js/admin.js', __FILE__), ['jquery'], '1.0', true);
        wp_enqueue_style('so_qmp_admin_style', plugins_url('css/admin.css', __FILE__));

        $translation_array = [
            'select_option' => esc_html__('— Select —', 'quick-multilingual')
        ];
        wp_localize_script('so_qmp_admin_script', 'so_qmp_vars', $translation_array);
    }

    public function register_settings() {
        register_setting('so_qmp_settings_group', 'so_qmp_primary_lang', 'sanitize_text_field');
        register_setting('so_qmp_settings_group', 'so_qmp_secondary_lang', 'sanitize_text_field');
        register_setting('so_qmp_settings_group', 'so_qmp_primary_hreflang', 'sanitize_text_field');
        register_setting('so_qmp_settings_group', 'so_qmp_secondary_hreflang', 'sanitize_text_field');
        register_setting('so_qmp_settings_group', 'so_qmp_language_folder_page', 'absint');
        register_setting('so_qmp_settings_group', 'so_qmp_number_of_pages', 'absint');

        for ($i = 1; $i <= 4; $i++) {
            register_setting('so_qmp_page_translations_group', 'so_qmp_page_mapping_' . $i, [$this, 'sanitize_page_mapping']);
        }
    }

    public function sanitize_page_mapping($input) {
        $sanitized_input = [];
        if (isset($input['primary'])) {
            $sanitized_input['primary'] = absint($input['primary']);
        }
        if (isset($input['secondary'])) {
            $sanitized_input['secondary'] = absint($input['secondary']);
        }
        return $sanitized_input;
    }

    public function create_options_page() {
        add_options_page(
            esc_html__('Quick Multilingual Settings', 'quick-multilingual'),
            esc_html__('Quick Multilingual', 'quick-multilingual'),
            'manage_options',
            'quick-multilingual',
            [$this, 'options_page_html']
        );
    }

    public function options_page_html() {
        if (!current_user_can('manage_options')) {
            return;
        }

        settings_errors('so_qmp_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p><?php esc_html_e('Quick Multilingual is a WordPress plugin designed to enhance multilingual websites by adjusting the HTML lang attribute and adding hreflang tags.', 'quick-multilingual'); ?></p>
            <p><?php esc_html_e('This plugin allows you to set the HTML language attribute for up to two languages, custom hreflang codes for those languages, redirect the "language folder" (the secondary language placeholder page) to its respective homepage, map up to 4 primary language pages to their secondary page translations and properly handle language attributes for better SEO and user experience.', 'quick-multilingual'); ?></p>

            <h2 class="nav-tab-wrapper">
                <a href="#general-settings" class="nav-tab"><?php esc_html_e('General Settings', 'quick-multilingual'); ?></a>
                <a href="#page-translations" class="nav-tab"><?php esc_html_e('Page Translations', 'quick-multilingual'); ?></a>
            </h2>

            <div id="general-settings" class="so_qmp-tab-content">
                <h3><?php
                    printf(
                        wp_kses(
                            __('Find all HTML lang attributes <a href="%s" target="_blank">here</a>.', 'quick-multilingual'),
                            ['a' => ['href' => [], 'target' => []]]
                        ),
                        'https://gist.github.com/JamieMason/3748498'
                    );
                ?></h3>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('so_qmp_settings_group');
                    do_settings_sections('so_qmp_settings_group');
                    ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('HTML lang attribute primary language', 'quick-multilingual'); ?></th>
                            <td><input type="text" name="so_qmp_primary_lang" value="<?php echo esc_attr(get_option('so_qmp_primary_lang')); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('HTML lang attribute secondary language', 'quick-multilingual'); ?></th>
                            <td><input type="text" name="so_qmp_secondary_lang" value="<?php echo esc_attr(get_option('so_qmp_secondary_lang')); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Hreflang primary language', 'quick-multilingual'); ?></th>
                            <td><input type="text" name="so_qmp_primary_hreflang" value="<?php echo esc_attr(get_option('so_qmp_primary_hreflang')); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Hreflang secondary language', 'quick-multilingual'); ?></th>
                            <td><input type="text" name="so_qmp_secondary_hreflang" value="<?php echo esc_attr(get_option('so_qmp_secondary_hreflang')); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Language Folder Page', 'quick-multilingual'); ?></th>
                            <td>
                                <?php
                                wp_dropdown_pages([
                                    'name' => 'so_qmp_language_folder_page',
                                    'selected' => get_option('so_qmp_language_folder_page'),
                                    'show_option_none' => esc_html__('— Select —', 'quick-multilingual'),
                                    'option_none_value' => '0'
                                ]);
                                ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Number of Pages to Map', 'quick-multilingual'); ?></th>
                            <td>
                                <input type="number" id="so_qmp_number_of_pages" name="so_qmp_number_of_pages" value="<?php echo esc_attr(get_option('so_qmp_number_of_pages', 1)); ?>" min="1" max="4" />
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>

            <div id="page-translations" class="so_qmp-tab-content" style="display:none;">
                <h3><?php esc_html_e('Here you can map the pages of the primary language to the secondary language.', 'quick-multilingual'); ?></h3>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('so_qmp_page_translations_group');
                    do_settings_sections('so_qmp_page_translations_group');

                    $number_of_pages = intval(get_option('so_qmp_number_of_pages', 1));
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
                            for ($i = 1; $i <= $number_of_pages; $i++) {
                                $page_mapping = get_option('so_qmp_page_mapping_' . $i, []);
                                $primary_page = isset($page_mapping['primary']) ? $page_mapping['primary'] : 0;
                                $secondary_page = isset($page_mapping['secondary']) ? $page_mapping['secondary'] : 0;
                                ?>
                                <tr valign="top" class="page-mapping-row">
                                    <td><?php echo esc_html(sprintf(__('Page %d', 'quick-multilingual'), $i)); ?></td>
                                    <td>
                                        <?php
                                        $language_folder_page_id = get_option('so_qmp_language_folder_page');
                                        $exclude_pages = [$language_folder_page_id];
                                        $children_pages = get_pages(['child_of' => $language_folder_page_id]);
                                        foreach ($children_pages as $child_page) {
                                            $exclude_pages[] = $child_page->ID;
                                        }

                                        wp_dropdown_pages([
                                            'name' => 'so_qmp_page_mapping_' . $i . '[primary]',
                                            'selected' => $primary_page,
                                            'exclude' => implode(',', $exclude_pages),
                                            'show_option_none' => esc_html__('— Select —', 'quick-multilingual'),
                                            'option_none_value' => '0'
                                        ]);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        wp_dropdown_pages([
                                            'name' => 'so_qmp_page_mapping_' . $i . '[secondary]',
                                            'selected' => $secondary_page,
                                            'child_of' => $language_folder_page_id,
                                            'show_option_none' => esc_html__('— Select —', 'quick-multilingual'),
                                            'option_none_value' => '0'
                                        ]);
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
        <?php
    }

    public function add_hreflang_tags() {
        $primary_hreflang = get_option('so_qmp_primary_hreflang');
        $secondary_hreflang = get_option('so_qmp_secondary_hreflang');

        $current_page_id = get_queried_object_id();
        $current_page_url = get_permalink($current_page_id);

        $is_mapped = false;
        $mapped_primary_id = null;
        $mapped_secondary_id = null;

        for ($i = 1; $i <= 4; $i++) {
            $page_mapping = get_option('so_qmp_page_mapping_' . $i);
            if ($page_mapping) {
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

        if ($is_mapped) {
            $primary_url = $mapped_primary_id ? get_permalink($mapped_primary_id) : '';
            $secondary_url = $mapped_secondary_id ? get_permalink($mapped_secondary_id) : '';

            if ($mapped_primary_id && $primary_url) {
                echo '<link rel="alternate" hreflang="' . esc_attr($primary_hreflang) . '" href="' . esc_url($primary_url) . '" />' . PHP_EOL;
            }
            if ($mapped_secondary_id && $secondary_url) {
                echo '<link rel="alternate" hreflang="' . esc_attr($secondary_hreflang) . '" href="' . esc_url($secondary_url) . '" />' . PHP_EOL;
            }

            $x_default_url = $primary_url ? $primary_url : ($secondary_url ? $secondary_url : $current_page_url);
            echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($x_default_url) . '" />' . PHP_EOL;
        } else {
            $current_lang_prefix = $this->get_current_language_prefix();
            $is_secondary_lang_page = ($current_lang_prefix === '/' . $secondary_hreflang . '/');

            if ($is_secondary_lang_page) {
                echo '<link rel="alternate" hreflang="' . esc_attr($secondary_hreflang) . '" href="' . esc_url($current_page_url) . '" />' . PHP_EOL;
            } else {
                echo '<link rel="alternate" hreflang="' . esc_attr($primary_hreflang) . '" href="' . esc_url($current_page_url) . '" />' . PHP_EOL;
            }

            echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($current_page_url) . '" />' . PHP_EOL;
        }
    }

    public function set_html_lang($output) {
        $primary_lang = get_option('so_qmp_primary_lang');
        $secondary_lang = get_option('so_qmp_secondary_lang');

        $current_lang_prefix = $this->get_current_language_prefix();
        $html_lang = ($current_lang_prefix === '/' . get_option('so_qmp_secondary_hreflang') . '/') ? $secondary_lang : $primary_lang;

        $new_output = preg_replace('/lang="[^"]*"/', 'lang="' . esc_attr($html_lang) . '"', $output);

        if ($new_output === $output) {
            $new_output = str_replace('<html', '<html lang="' . esc_attr($html_lang) . '"', $output);
        }

        return $new_output;
    }

    public function get_current_language_prefix() {
        $secondary_hreflang = get_option('so_qmp_secondary_hreflang');
        $secondary_lang_prefix = '/' . $secondary_hreflang . '/';

        $current_url = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        return (strpos($current_url, $secondary_lang_prefix) === 0) ? $secondary_lang_prefix : '';
    }

    public function redirect_language_folder_to_secondary_homepage() {
        $language_folder_page_id = get_option('so_qmp_language_folder_page');

        if ($language_folder_page_id && is_page($language_folder_page_id)) {
            $page_mapping = get_option('so_qmp_page_mapping_1');

            if ($page_mapping && isset($page_mapping['secondary']) && !empty($page_mapping['secondary'])) {
                $first_mapped_secondary_page_id = $page_mapping['secondary'];

                $redirect_url = get_permalink($first_mapped_secondary_page_id);

                if ($redirect_url && $this->is_valid_url($redirect_url)) {
                    wp_safe_redirect(esc_url($redirect_url), 301);
                    exit;
                }
            }
        }
    }

    private function is_valid_url($url) {
        // Validate the URL to ensure it is a valid URL within the site
        $home_url = home_url();
        return strpos($url, $home_url) === 0;
    }

    public function add_settings_link($links) {
        $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=quick-multilingual')) . '">' . esc_html__('Settings', 'quick-multilingual') . '</a>';
        array_push($links, $settings_link);
        return $links;
    }
}

new QuickMultilingual();
