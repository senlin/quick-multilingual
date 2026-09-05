<?php
declare( strict_types=1 );

namespace SOWP\QuickMultilingual\Service;

use SOWP\QuickMultilingual\Config;

/**
 * Determines the active language context for the current request.
 */
final readonly class LanguageResolver {

    /**
     * True when the plugin itself designates the current page as secondary:
     * the language folder page, or the secondary side of an active mapping row.
     * Page hierarchy is intentionally ignored; the mapping is the source of truth.
     */
    public function is_secondary_context(): bool {
        $current = (int) get_queried_object_id();
        if ( ! $current ) {
            return false;
        }

        // The language folder page itself stays secondary.
        $folder_id = absint( get_option( Config::OPT_FOLDER_PAGE ) );
        if ( $folder_id && is_page( $folder_id ) ) {
            return true;
        }

        // Secondary only when mapped as the secondary side of an active row.
        $stored = absint( get_option( Config::OPT_NUMBER_OF_PAGES, 1 ) );
        $limit  = min( max( $stored, 1 ), Config::max_page_mappings() );
        for ( $i = 1; $i <= $limit; $i++ ) {
            $row = get_option( Config::option_key( $i ) );
            if (
                is_array( $row )
                && ! empty( $row['secondary'] )
                && absint( $row['secondary'] ) === $current
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Secondary URL prefix ("/<secondary_hreflang>/") when the current URL starts with it, else ''.
     */
    public function current_language_prefix(): string {
        $secondary = get_option( Config::OPT_SECONDARY_HREFLANG );
        if ( ! is_string( $secondary ) || '' === $secondary ) {
            return '';
        }
        $prefix = '/' . $secondary . '/';
        $uri    = isset( $_SERVER['REQUEST_URI'] )
            ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) )
            : '';
        return ( is_string( $uri ) && str_starts_with( $uri, $prefix ) ) ? $prefix : '';
    }
}
