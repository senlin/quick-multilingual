<?php
declare( strict_types=1 );

namespace SOWP\QuickMultilingual\Service;

use SOWP\QuickMultilingual\Config;

/**
 * Determines the active language context for the current request.
 */
final readonly class LanguageResolver {

    /**
     * True when the current page is the language folder page or a descendant of it.
     */
    public function is_secondary_context(): bool {
        $folder_id = absint( get_option( Config::OPT_FOLDER_PAGE ) );
        $current   = get_queried_object_id();
        if ( ! $folder_id || ! $current ) {
            return false;
        }
        return is_page( $folder_id )
            || in_array( $folder_id, get_post_ancestors( $current ), true );
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
