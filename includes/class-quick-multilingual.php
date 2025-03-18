<?php
/**
 * Main plugin class file.
 *
 * @package Quick_Multilingual
 * @since 1.0.0
 */

// Don't load the file directly
defined( 'ABSPATH' ) || exit;

/**
 * Main Quick Multilingual Plugin Class
 */
class Quick_Multilingual {
    /**
     * Plugin instance.
     *
     * @var Quick_Multilingual
     */
    private static $instance = null;

    /**
     * Admin class instance
     * 
     * @var Quick_Multilingual_Admin
     */
    public $admin;

    /**
     * Frontend class instance
     * 
     * @var Quick_Multilingual_Frontend
     */
    public $frontend;

    /**
     * Get plugin instance.
     *
     * @return Quick_Multilingual
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->includes();
        $this->init();
    }

    /**
     * Include required files.
     */
    private function includes() {
        // Core functionality
        require_once QML_PLUGIN_DIR . 'includes/class-quick-multilingual-admin.php';
        require_once QML_PLUGIN_DIR . 'includes/class-quick-multilingual-frontend.php';
    }

    /**
     * Initialize the plugin components.
     */
    private function init() {
        // Load plugin text domain
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        // Initialize components
        $this->admin = new Quick_Multilingual_Admin();
        $this->frontend = new Quick_Multilingual_Frontend();
    }

    /**
     * Load plugin text domain for translation.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'quick-multilingual', false, dirname( plugin_basename( QML_PLUGIN_FILE ) ) . '/languages' );
    }

    /**
     * Get the corresponding page ID from the mapping.
     *
     * @param int    $page_id  The page ID to find the mapping for.
     * @param string $language The language to search in ('primary' or 'secondary').
     * @return int|null The mapped page ID or null if not found.
     */
    public function get_mapped_page_id( $page_id, $language = 'primary' ) {
        for ( $i = 1; $i <= 4; $i++ ) {
            $page_mapping = get_option( 'so_qmp_page_mapping_' . $i );
            
            if ( $page_mapping && isset( $page_mapping[$language] ) && $page_mapping[$language] == $page_id ) {
                return $page_mapping[$language === 'primary' ? 'secondary' : 'primary'];
            }
        }
        
        return null;
    }

    /**
     * Get the current language prefix based on the URL.
     *
     * @return string The current language prefix.
     */
    public function get_current_language_prefix() {
        $secondary_hreflang = get_option( 'so_qmp_secondary_hreflang' );
        $secondary_lang_prefix = '/' . $secondary_hreflang . '/';

        $current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        
        return ( strpos( $current_url, $secondary_lang_prefix ) === 0 ) ? $secondary_lang_prefix : '';
    }
}