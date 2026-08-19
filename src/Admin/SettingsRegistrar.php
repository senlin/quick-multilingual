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
        register_setting( $group, Config::OPT_NUMBER_OF_PAGES, 'absint' );

        $map_group = Config::OPTION_GROUP_MAP;
        for ( $i = 1; $i <= Config::MAX_PAGE_MAPPINGS; $i++ ) {
            register_setting( $map_group, Config::option_key( $i ), [ $this, 'sanitize_page_mapping' ] );
        }
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
