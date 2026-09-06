<?php
declare( strict_types=1 );

namespace SOWP\QuickMultilingual\Admin;

use SOWP\QuickMultilingual\Config;
use SOWP\QuickMultilingual\Service\MappingService;

/**
 * Settings page: registration and two-tab rendering (General / Page Translations).
 */
final readonly class SettingsPage {

    public function __construct(
        private MappingService $mapping,
    ) {}

    public function add_menu(): void {
        add_options_page(
            esc_html__( 'Quick Multilingual Settings', 'quick-multilingual' ),
            esc_html__( 'Quick Multilingual', 'quick-multilingual' ),
            'manage_options',
            'quick-multilingual',
            [ $this, 'render' ]
        );
    }

    public function render(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        settings_errors( 'so_qmp_messages' );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Quick Multilingual is a WordPress plugin designed to enhance multilingual websites by adjusting the HTML lang attribute and adding hreflang tags.', 'quick-multilingual' ); ?></p>
            <p><?php esc_html_e( 'This plugin allows you to set the HTML language attribute for up to two languages, custom hreflang codes for those languages, redirect the "language folder" (the secondary language placeholder page) to its respective homepage, map up to 4 primary language pages to their secondary page translations and properly handle language attributes for better SEO and user experience.', 'quick-multilingual' ); ?></p>

            <h2 class="nav-tab-wrapper">
                <a href="#general-settings" class="nav-tab nav-tab-active"><?php esc_html_e( 'General Settings', 'quick-multilingual' ); ?></a>
                <a href="#page-translations" class="nav-tab"><?php esc_html_e( 'Page Translations', 'quick-multilingual' ); ?></a>
            </h2>

            <div id="general-settings" class="so_qmp-tab-content">
                <p class="so_qmp-lang-ref-link"><?php
                    printf(
                        wp_kses(
                            /* translators: %s: URL to lang attributes gist */
                            __( 'Find all HTML lang attributes <a href="%s" target="_blank">here</a>.', 'quick-multilingual' ),
                            [ 'a' => [ 'href' => [], 'target' => [] ] ]
                        ),
                        'https://gist.github.com/JamieMason/3748498'
                    );
                ?></p>

                <form method="post" action="options.php">
                    <?php
                    settings_fields( Config::OPTION_GROUP );
                    do_settings_sections( Config::OPTION_GROUP );
                    ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="so_qmp_number_of_languages"><?php esc_html_e( 'Number of Languages', 'quick-multilingual' ); ?></label></th>
                            <td>
                                <span class="so_qmp-premium-field">
                                    <select id="so_qmp_number_of_languages" class="so_qmp-faded" disabled="disabled">
                                        <?php
                                        for ( $n = 3; $n <= Config::PREMIUM_MAX_LANGUAGES; $n++ ) {
                                            printf(
                                                '<option value="%1$d" %2$s>%1$d</option>',
                                                intval( $n ),
                                                wp_kses( selected( 3, $n, false ), [] )
                                            );
                                        }
                                        ?>
                                    </select>
                                    <span class="so_qmp-premium-overlay">
                                        <strong><?php esc_html_e( 'Quick Multilingual Premium', 'quick-multilingual' ); ?></strong>
                                        <span><?php esc_html_e( 'Unlock up to 20 languages and 100 page mappings.', 'quick-multilingual' ); ?></span>
                                        <?php
                                        // TODO: Replace '#' with the Premium purchase URL once the store is live.
                                        // TODO: Replace 'from $X' with the real lowest price before release.
                                        ?>
                                        <a href="#" class="so_qmp-upgrade-link"><?php esc_html_e( 'Get Premium', 'quick-multilingual' ); ?></a>
                                    </span>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Primary Language', 'quick-multilingual' ); ?></th>
                            <td>
                                <label class="so_qmp-field-label" for="<?php echo esc_attr( Config::OPT_PRIMARY_LANG ); ?>"><?php esc_html_e( 'HTML lang attribute', 'quick-multilingual' ); ?></label>
                                <input type="text" id="<?php echo esc_attr( Config::OPT_PRIMARY_LANG ); ?>" name="<?php echo esc_attr( Config::OPT_PRIMARY_LANG ); ?>" value="<?php echo esc_attr( (string) get_option( Config::OPT_PRIMARY_LANG ) ); ?>" class="regular-text" />
                            </td>
                            <td>
                                <label class="so_qmp-field-label" for="<?php echo esc_attr( Config::OPT_PRIMARY_HREFLANG ); ?>"><?php esc_html_e( 'Hreflang', 'quick-multilingual' ); ?></label>
                                <input type="text" id="<?php echo esc_attr( Config::OPT_PRIMARY_HREFLANG ); ?>" name="<?php echo esc_attr( Config::OPT_PRIMARY_HREFLANG ); ?>" value="<?php echo esc_attr( (string) get_option( Config::OPT_PRIMARY_HREFLANG ) ); ?>" class="regular-text" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Secondary Language', 'quick-multilingual' ); ?></th>
                            <td>
                                <label class="so_qmp-field-label" for="<?php echo esc_attr( Config::OPT_SECONDARY_LANG ); ?>"><?php esc_html_e( 'HTML lang attribute', 'quick-multilingual' ); ?></label>
                                <input type="text" id="<?php echo esc_attr( Config::OPT_SECONDARY_LANG ); ?>" name="<?php echo esc_attr( Config::OPT_SECONDARY_LANG ); ?>" value="<?php echo esc_attr( (string) get_option( Config::OPT_SECONDARY_LANG ) ); ?>" class="regular-text" />
                            </td>
                            <td>
                                <label class="so_qmp-field-label" for="<?php echo esc_attr( Config::OPT_SECONDARY_HREFLANG ); ?>"><?php esc_html_e( 'Hreflang', 'quick-multilingual' ); ?></label>
                                <input type="text" id="<?php echo esc_attr( Config::OPT_SECONDARY_HREFLANG ); ?>" name="<?php echo esc_attr( Config::OPT_SECONDARY_HREFLANG ); ?>" value="<?php echo esc_attr( (string) get_option( Config::OPT_SECONDARY_HREFLANG ) ); ?>" class="regular-text" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Language Folder Page', 'quick-multilingual' ); ?></th>
                            <td>
                            <?php
                            wp_dropdown_pages( [
                                'name'              => esc_attr( (string) Config::OPT_FOLDER_PAGE ),
                                'selected'          => absint( get_option( Config::OPT_FOLDER_PAGE ) ),
                                'show_option_none'  => esc_html__( '— Select —', 'quick-multilingual' ),
                                'option_none_value' => '0',
                            ] );
                            ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Number of Pages to Map', 'quick-multilingual' ); ?></th>
                            <td>
                                <select id="<?php echo esc_attr( Config::OPT_NUMBER_OF_PAGES ); ?>" name="<?php echo esc_attr( Config::OPT_NUMBER_OF_PAGES ); ?>">
                                <?php
                                $current = absint( get_option( Config::OPT_NUMBER_OF_PAGES, 1 ) );
                                for ( $n = 1; $n <= Config::max_page_mappings(); $n++ ) {
                                    printf(
                                        '<option value="%1$d" %2$s>%1$d</option>',
                                        intval( $n ),
                                        wp_kses( selected( $current, $n, false ), [] )
                                    );
                                }
                                ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div><!-- #general-settings -->

            <div id="page-translations" class="so_qmp-tab-content" style="display:none;">
                <h2><?php esc_html_e( 'Here you can map the pages of the primary language to the secondary language.', 'quick-multilingual' ); ?></h2>
                <form method="post" action="options.php">
                    <?php
                    settings_fields( Config::OPTION_GROUP_MAP );
                    do_settings_sections( Config::OPTION_GROUP_MAP );
                    $number_of_pages = intval( get_option( Config::OPT_NUMBER_OF_PAGES, 1 ) );
                    ?>
                    <table class="form-table" id="page-translations-table" role="presentation">
                        <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Page', 'quick-multilingual' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Primary Language Page', 'quick-multilingual' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Secondary Language Page', 'quick-multilingual' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        for ( $i = 1; $i <= $number_of_pages; $i++ ) {
                            $row            = get_option( Config::option_key( $i ), [] );
                            $primary_page   = isset( $row['primary'] ) ? absint( $row['primary'] ) : 0;
                            $secondary_page = isset( $row['secondary'] ) ? absint( $row['secondary'] ) : 0;

                            $folder_id     = absint( get_option( Config::OPT_FOLDER_PAGE ) );
                            $exclude_pages = [ $folder_id ];
                            $children      = get_pages( [ 'child_of' => $folder_id ] );
                            if ( is_array( $children ) ) {
                                foreach ( $children as $child ) {
                                    $exclude_pages[] = $child->ID;
                                }
                            }
                            ?>
                            <tr class="page-mapping-row">
                                <td><?php echo esc_html( sprintf( /* translators: %d: page row number */ __( 'Page %d', 'quick-multilingual' ), $i ) ); ?></td>
                                <td>
                                    <?php
                                    wp_dropdown_pages( [
                                        'name'              => esc_attr( Config::option_key( $i ) . '[primary]' ),
                                        'selected'          => absint( $primary_page ),
                                        'exclude'           => implode( ',', array_map( 'absint', $exclude_pages ) ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
                                        'show_option_none'  => esc_html__( '— Select —', 'quick-multilingual' ),
                                        'option_none_value' => '0',
                                    ] );
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    wp_dropdown_pages( [
                                        'name'              => esc_attr( Config::option_key( $i ) . '[secondary]' ),
                                        'selected'          => absint( $secondary_page ),
                                        'child_of'          => absint( $folder_id ),
                                        'show_option_none'  => esc_html__( '— Select —', 'quick-multilingual' ),
                                        'option_none_value' => '0',
                                    ] );
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div><!-- #page-translations -->
        </div><!-- .wrap -->
        <?php
    }
}
