<?php
declare( strict_types=1 );

namespace SOWP\QuickMultilingual\Frontend;

use SOWP\QuickMultilingual\Config;
use SOWP\QuickMultilingual\Service\MappingService;

/**
 * 301 redirect the language folder page to the first mapped secondary page.
 */
final readonly class Redirect {

    public function __construct(
        private MappingService $mapping,
    ) {}

    public function folder_to_secondary_home(): void {
        $folder_id = absint( get_option( Config::OPT_FOLDER_PAGE ) );
        if ( ! $folder_id || ! is_page( $folder_id ) ) {
            return;
        }

        $secondary_id = $this->mapping->first_secondary();
        if ( null === $secondary_id || 0 === $secondary_id ) {
            return;
        }

        $url = get_permalink( $secondary_id );
        if ( $url ) {
            wp_safe_redirect( esc_url( $url ), 301 );
            exit;
        }
    }
}
