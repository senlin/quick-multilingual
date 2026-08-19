<?php
declare( strict_types=1 );

namespace SOWP\QuickMultilingual;

/**
 * Guards the minimum environment requirements at activation.
 */
final readonly class Activation {

    public const MIN_PHP = '8.2';
    public const MIN_WP  = '6.2';

    public static function activate(): void {
        if ( self::meets_requirements() ) {
            return;
        }
        deactivate_plugins( [ SO_QMP_BASENAME ] );
        set_transient( 'so_qmp_requirements_failed', 1, 30 );
    }

    public static function meets_requirements(): bool {
        return version_compare( PHP_VERSION, self::MIN_PHP, '>=' )
            && version_compare( get_bloginfo( 'version' ), self::MIN_WP, '>=' );
    }

    public static function register_notices(): void {
        if ( ! get_transient( 'so_qmp_requirements_failed' ) ) {
            return;
        }
        delete_transient( 'so_qmp_requirements_failed' );

        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html(
                sprintf(
                    /* translators: 1: minimum PHP version, 2: minimum WordPress version */
                    __( 'Quick Multilingual requires PHP %1$s or higher and WordPress %2$s or higher. The plugin has been deactivated.', 'quick-multilingual' ),
                    self::MIN_PHP,
                    self::MIN_WP
                )
            )
        );
    }
}
