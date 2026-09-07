<?php
declare( strict_types=1 );

namespace SOWP\QuickMultilingual\Assets;

use SOWP\QuickMultilingual\Config;

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
            [ 'jquery', 'wp-api-fetch' ],
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
            [
                'select_label'        => esc_html__( '— Select —', 'quick-multilingual' ),
                'rest_pages_url'      => rest_url( 'wp/v2/pages' ),
                'secondary_pages_url' => rest_url( 'so-qmp/v1/pages' ),
                'nonce'               => wp_create_nonce( 'wp_rest' ),
                'max_mappings'        => Config::max_page_mappings(),
                'search_placeholder'  => esc_html__( 'Search pages…', 'quick-multilingual' ),
                'no_results'          => esc_html__( 'No pages found.', 'quick-multilingual' ),
                'remove_label'        => esc_html__( '−', 'quick-multilingual' ),
                'remove_aria'         => esc_html__( 'Remove mapping row', 'quick-multilingual' ),
                'add_mapping_label'   => esc_html__( '+ Add mapping', 'quick-multilingual' ),
            ]
        );
    }
}
