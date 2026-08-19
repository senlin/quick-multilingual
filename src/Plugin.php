<?php
declare( strict_types=1 );

namespace SOWP\QuickMultilingual;

use SOWP\QuickMultilingual\Admin\SettingsPage;
use SOWP\QuickMultilingual\Admin\SettingsRegistrar;
use SOWP\QuickMultilingual\Assets\AdminAssets;
use SOWP\QuickMultilingual\Frontend\HeadTags;
use SOWP\QuickMultilingual\Frontend\Redirect;
use SOWP\QuickMultilingual\Service\LanguageResolver;
use SOWP\QuickMultilingual\Service\MappingService;

/**
 * Main plugin orchestrator.
 */
final class Plugin {

    private static ?self $instance = null;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    public function run(): void {
        $mapping = new MappingService();

        $registrar = new SettingsRegistrar();
        $page      = new SettingsPage( $mapping );
        add_action( 'admin_init', [ $registrar, 'register' ] );
        add_action( 'admin_menu', [ $page, 'add_menu' ] );

        $head = new HeadTags( $mapping, new LanguageResolver() );
        add_action( 'wp_head', [ $head, 'hreflang' ], 1 );
        add_action( 'wp_head', [ $head, 'canonical' ], 2 );
        add_filter( 'language_attributes', [ $head, 'html_lang' ], 100 );

        $redirect = new Redirect( $mapping );
        add_action( 'template_redirect', [ $redirect, 'folder_to_secondary_home' ] );

        $assets = new AdminAssets();
        add_action( 'admin_enqueue_scripts', [ $assets, 'enqueue' ] );

        add_action( 'admin_notices', [ Activation::class, 'register_notices' ] );
        add_filter( 'plugin_action_links_' . SO_QMP_BASENAME, [ $this, 'settings_link' ] );
    }

    /**
     * @param array<int,string> $links
     * @return array<int,string>
     */
    public function settings_link( array $links ): array {
        $url = admin_url( 'options-general.php?page=quick-multilingual' );
        array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'quick-multilingual' ) . '</a>' );
        return $links;
    }
}
