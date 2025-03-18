<?php

namespace QuickMultilingual;

class PublicArea {

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