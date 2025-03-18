<?php
/**
 * Admin functionality class file.
 *
 * @package Quick_Multilingual
 * @since 1.0.0
 */

// Don't load the file directly
defined( 'ABSPATH' ) || exit;

/**
 * Admin functionality for Quick Multilingual
 */
class Quick_Multilingual_Admin {
    /**
     * Constructor.
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        add_action( 'admin_menu', array( $this, 'create_options_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_filter( 'plugin_action_links_' . QML_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_admin_scripts( $hook ) {
        // Only enqueue on this plugin's page
        if ( 'settings_page_quick-multilingual' !== $hook ) {
            return;
        }

        wp_enqueue_script( 'so_qmp_admin_script', QML_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), QML_VERSION, true );
        wp_enqueue_style( 'so_qmp_admin_style', QML_PLUGIN_URL . 'assets/css/admin.css', array(), QML_VERSION );

        // Localize the script with new data
        $translation_array = array(
            'select_option' => esc_html__( '— Select —', 'quick-multilingual' )
        );
        wp_localize_script( 'so_qmp_admin_script', 'so_qmp_vars', $translation_array );
    }

    /**
     * Register and define settings.
     */
    public function register_settings() {
        register_setting( 'so_qmp_settings_group', 'so_qmp_primary_lang', 'sanitize_text_field' );
        register_setting( 'so_qmp_settings_group', 'so_qmp_secondary_lang', 'sanitize_text_field' );
        register_setting( 'so_qmp_settings_group', 'so_qmp_primary_hreflang', 'sanitize_text_field' );
        register_setting( 'so_qmp_settings_group', 'so_qmp_secondary_hreflang', 'sanitize_text_field' );
        register_setting( 'so_qmp_settings_group', 'so_qmp_language_folder_page', 'absint' );
        register_setting( 'so_qmp_settings_group', 'so_qmp_number_of_pages', 'absint' );

        // Register page mappings settings
        for ( $i = 1; $i <= 4; $i++ ) {
            register_setting( 'so_qmp_page_translations_group', 'so_qmp_page_mapping_' . $i, array( $this, 'sanitize_page_mapping' ) );
        }
    }

    /**
     * Sanitize page mapping.
     *
     * @param array $input The input array to sanitize.
     * @return array The sanitized input array.
     */
    public function sanitize_page_mapping( $input ) {
        $sanitized_input = array();
        
        if ( isset( $input['primary'] ) ) {
            $sanitized_input['primary'] = absint( $input['primary'] );
        }
        
        if ( isset( $input['secondary'] ) ) {
            $sanitized_input['secondary'] = absint( $input['secondary'] );
        }
        
        return $sanitized_input;
    }

    /**
     * Add settings page to WordPress admin.
     */
    public function create_options_page() {
        add_options_page(
            esc_html__( 'Quick Multilingual Settings', 'quick-multilingual' ),
            esc_html__( 'Quick Multilingual', 'quick-multilingual' ),
            'manage_options',
            'quick-multilingual',
            array( $this, 'render_options_page' )
        );
    }

    /**
     * Render the options page.
     */
    public function render_options_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        require_once QML_PLUGIN_DIR . 'templates/admin-settings.php';
    }

    /**
     * Add settings link to plugin page.
     *
     * @param array $links Array of plugin action links.
     * @return array Modified array of plugin action links.
     */
    public function add_settings_link( $links ) {
        $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=quick-multilingual' ) ) . '">' . esc_html__( 'Settings', 'quick-multilingual' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }
}