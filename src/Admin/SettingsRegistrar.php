<?php
declare( strict_types=1 );

namespace SOWP\QuickMultilingual\Admin;

use SOWP\QuickMultilingual\Config;

/**
 * Registers all plugin settings and their sanitize callbacks.
 */
final readonly class SettingsRegistrar {

    public function register(): void {
        $group = Config::OPTION_GROUP;
        register_setting( $group, Config::OPT_PRIMARY_LANG, 'sanitize_text_field' );
        register_setting( $group, Config::OPT_SECONDARY_LANG, 'sanitize_text_field' );
        register_setting( $group, Config::OPT_PRIMARY_HREFLANG, 'sanitize_text_field' );
        register_setting( $group, Config::OPT_SECONDARY_HREFLANG, 'sanitize_text_field' );
        register_setting( $group, Config::OPT_FOLDER_PAGE, 'absint' );
        register_setting( $group, Config::OPT_NUMBER_OF_PAGES, [ $this, 'sanitize_number_of_pages' ] );

        $map_group = Config::OPTION_GROUP_MAP;
        for ( $i = 1; $i <= Config::max_page_mappings(); $i++ ) {
            register_setting( $map_group, Config::option_key( $i ), [ $this, 'sanitize_page_mapping' ] );
        }
    }

    /**
     * Sanitize callback for the "Number of Pages to Map" option.
     *
     * Rejects invalid values, and when the count shrinks, deletes any
     * orphaned mapping rows beyond the new count (including rows left
     * behind by a Premium downgrade). Clamps the stored value to the
     * tier maximum.
     *
     * @param mixed $input Raw value posted from the settings form.
     * @return int Sanitized page count, between 1 and the tier maximum.
     */
    public function sanitize_number_of_pages( mixed $input ): int {
        $new = absint( $input );
        if ( $new < 1 ) {
            return absint( get_option( Config::OPT_NUMBER_OF_PAGES, 1 ) );
        }
        $old = absint( get_option( Config::OPT_NUMBER_OF_PAGES, 1 ) );
        $max = Config::max_page_mappings();
        if ( $new < $old ) {
            for ( $i = $new + 1; $i <= $max; $i++ ) {
                delete_option( Config::option_key( $i ) );
            }
        }
        return min( $new, $max );
    }

    /**
     * @param mixed $input
     * @return array{primary?: int, secondary?: int}
     */
    public function sanitize_page_mapping( mixed $input ): array {
        $out = [];
        $in  = is_array( $input ) ? $input : [];
        if ( isset( $in['primary'] ) ) {
            $out['primary'] = absint( $in['primary'] );
        }
        if ( isset( $in['secondary'] ) ) {
            $out['secondary'] = absint( $in['secondary'] );
        }
        return $out;
    }
}
