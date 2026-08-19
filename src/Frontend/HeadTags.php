<?php
declare( strict_types=1 );

namespace SOWP\QuickMultilingual\Frontend;

use SOWP\QuickMultilingual\Config;
use SOWP\QuickMultilingual\Service\LanguageResolver;
use SOWP\QuickMultilingual\Service\MappingService;

/**
 * Frontend <head> output: hreflang tags, html lang attribute, and canonical URL.
 */
final readonly class HeadTags {

    public function __construct(
        private MappingService $mapping,
        private LanguageResolver $resolver,
    ) {}

    /**
     * Emit hreflang alternates and x-default for mapped pages, with a
     * single-self-referencing fallback for unmapped pages.
     */
    public function hreflang(): void {
        $primary_href   = get_option( Config::OPT_PRIMARY_HREFLANG );
        $secondary_href = get_option( Config::OPT_SECONDARY_HREFLANG );
        $current_id     = (int) get_queried_object_id();
        $current_url    = get_permalink( $current_id );

        $row = $this->mapping->row_for_page( $current_id );

        if ( null !== $row ) {
            $primary_id   = isset( $row['primary'] ) ? absint( $row['primary'] ) : 0;
            $secondary_id = isset( $row['secondary'] ) ? absint( $row['secondary'] ) : 0;
            $primary_url   = $primary_id ? get_permalink( $primary_id ) : '';
            $secondary_url = $secondary_id ? get_permalink( $secondary_id ) : '';

            if ( $primary_id && $primary_url ) {
                printf(
                    '<link rel="alternate" hreflang="%s" href="%s" />' . PHP_EOL,
                    esc_attr( (string) $primary_href ),
                    esc_url( $primary_url )
                );
            }
            if ( $secondary_id && $secondary_url ) {
                printf(
                    '<link rel="alternate" hreflang="%s" href="%s" />' . PHP_EOL,
                    esc_attr( (string) $secondary_href ),
                    esc_url( $secondary_url )
                );
            }
            $x_default = $primary_url ?: ( $secondary_url ?: $current_url );
            printf( '<link rel="alternate" hreflang="x-default" href="%s" />' . PHP_EOL, esc_url( $x_default ) );
            return;
        }

        // Non-mapped page: minimal self-referencing alternate based on URL prefix.
        $is_secondary_prefix = '' !== $this->resolver->current_language_prefix();
        printf(
            '<link rel="alternate" hreflang="%s" href="%s" />' . PHP_EOL,
            esc_attr( (string) ( $is_secondary_prefix ? $secondary_href : $primary_href ) ),
            esc_url( $current_url )
        );
        printf( '<link rel="alternate" hreflang="x-default" href="%s" />' . PHP_EOL, esc_url( $current_url ) );
    }

    /**
     * Filter for `language_attributes`: forces the html lang attribute per current language.
     */
    public function html_lang( string $output ): string {
        $primary   = get_option( Config::OPT_PRIMARY_LANG );
        $secondary = get_option( Config::OPT_SECONDARY_LANG );
        $html_lang = $this->resolver->is_secondary_context() ? $secondary : $primary;

        $new = preg_replace( '/lang="[^"]*"/', 'lang="' . esc_attr( (string) $html_lang ) . '"', $output );
        if ( $new === $output ) {
            $new = $output . ' lang="' . esc_attr( (string) $html_lang ) . '"';
        }
        return $new;
    }

    /**
     * NEW: canonical URL output.
     *
     * Scoped to mapped pages only: we output a self-referencing canonical and
     * remove WordPress core `rel_canonical` on these pages to prevent duplicate tags.
     */
    public function canonical(): void {
        $current_id = (int) get_queried_object_id();
        if ( ! $current_id ) {
            return;
        }
        if ( null === $this->mapping->row_for_page( $current_id ) ) {
            return; // Let core rel_canonical handle unmapped pages.
        }

        remove_action( 'wp_head', 'rel_canonical' );
        $url = get_permalink( $current_id );
        if ( $url ) {
            printf( '<link rel="canonical" href="%s" />' . PHP_EOL, esc_url( $url ) );
        }
    }
}
