<?php
declare( strict_types=1 );

namespace SOWP\QuickMultilingual\Assets;

/**
 * Enqueue admin scripts/styles only on the plugin settings screen.
 */
final readonly class AdminAssets {

    public function enqueue( string $hook ): void {
        if ( 'settings_page_quick-multilingual' !== $hook ) {
            return;
        }

        wp_enqueue_script(
            'so_qmp_admin_script',
            SO_QMP_URL . 'js/admin.js',
            [ 'jquery' ],
            SO_QMP_VERSION,
            true
        );

        wp_enqueue_style(
            'so_qmp_admin_style',
            SO_QMP_URL . 'css/admin.css',
            [],
            SO_QMP_VERSION
        );

        wp_localize_script(
            'so_qmp_admin_script',
            'so_qmp_vars',
            [ 'select_option' => esc_html__( '— Select —', 'quick-multilingual' ) ]
        );
    }
}
