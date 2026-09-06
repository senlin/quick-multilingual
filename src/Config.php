<?php
declare( strict_types=1 );

namespace SOWP\QuickMultilingual;

/**
 * Central configuration: version, text domain, option keys and Lite/Premium tier limits.
 *
 * Option keys are kept identical to 1.5.8 so existing installs upgrade without data loss.
 */
final readonly class Config {

    public const VERSION             = SO_QMP_VERSION;
    public const TEXT_DOMAIN         = 'quick-multilingual';
    public const OPTION_GROUP        = 'so_qmp_settings_group';
    public const OPTION_GROUP_MAP    = 'so_qmp_page_translations_group';

    public const OPT_PRIMARY_LANG       = 'so_qmp_primary_lang';
    public const OPT_SECONDARY_LANG     = 'so_qmp_secondary_lang';
    public const OPT_PRIMARY_HREFLANG   = 'so_qmp_primary_hreflang';
    public const OPT_SECONDARY_HREFLANG = 'so_qmp_secondary_hreflang';
    public const OPT_FOLDER_PAGE        = 'so_qmp_language_folder_page';
    public const OPT_NUMBER_OF_PAGES    = 'so_qmp_number_of_pages';
    public const OPT_PAGE_MAPPING_FMT   = 'so_qmp_page_mapping_%d';

    /** Current feature tier. Premium overrides this and the limits below. */
    public const TIER = 'lite';

    /** Maximum number of languages supported by this tier (Premium: >2). */
    public const MAX_LANGUAGES = 2;

    /** Maximum number of page mappings supported by this tier (Premium: >4). */
    public const MAX_PAGE_MAPPINGS = 4;

    /** Hard ceiling of the Premium "Number of Languages" selector. */
    public const PREMIUM_MAX_LANGUAGES = 20;

    public static function option_key( int $index ): string {
        return sprintf( self::OPT_PAGE_MAPPING_FMT, $index );
    }

    /** Current feature tier. Addons override via the so_qmp/tier filter. */
    public static function tier(): string {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- so_qmp is this plugin's unique prefix; the sniffer derives its expected prefix from the plugin name.
        return (string) apply_filters( 'so_qmp/tier', self::TIER );
    }

    /** Max languages for the current tier. Addons override via so_qmp/max_languages. */
    public static function max_languages(): int {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- so_qmp is this plugin's unique prefix; the sniffer derives its expected prefix from the plugin name.
        return (int) apply_filters( 'so_qmp/max_languages', self::MAX_LANGUAGES );
    }

    /** Max page mappings for the current tier. Addons override via so_qmp/max_page_mappings. */
    public static function max_page_mappings(): int {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- so_qmp is this plugin's unique prefix; the sniffer derives its expected prefix from the plugin name.
        return (int) apply_filters( 'so_qmp/max_page_mappings', self::MAX_PAGE_MAPPINGS );
    }
}
