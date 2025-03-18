<?php

namespace QuickMultilingual;

class Plugin {

    public function run() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path(__FILE__) . 'class-admin.php';
        require_once plugin_dir_path(__FILE__) . 'class-public.php';
    }

    private function define_admin_hooks() {
        $admin = new Admin();
        add_action('admin_enqueue_scripts', [$admin, 'enqueue_admin_scripts']);
        add_action('admin_init', [$admin, 'register_settings']);
        add_action('admin_menu', [$admin, 'create_options_page']);
    }

    private function define_public_hooks() {
        $public = new PublicArea();
        add_action('wp_head', [$public, 'add_hreflang_tags'], 1);
        add_filter('language_attributes', [$public, 'set_html_lang'], 100);
        add_action('template_redirect', [$public, 'redirect_language_folder_to_secondary_homepage']);
    }
}