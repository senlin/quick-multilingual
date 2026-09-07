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

        // __FILE__ is src/Admin/SettingsPage.php; dirname twice reaches the src dir,
        // and plugin_dir_url() takes its dirname (the plugin root) and appends a slash.
        $images_url = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'images/';
        ?>
        <div class="wrap">

            <h1 class="so_qmp-page-title">
                <img src="<?php echo esc_url( $images_url . 'qml-free-64.png' ); ?>"
                     alt="<?php esc_attr_e( 'Quick Multilingual', 'quick-multilingual' ); ?>"
                     class="so_qmp-title-logo" />
                <?php echo esc_html( get_admin_page_title() ); ?>
            </h1>

            <p><?php esc_html_e( 'Quick Multilingual is a WordPress plugin designed to enhance multilingual websites by adjusting the HTML lang attribute and adding hreflang tags.', 'quick-multilingual' ); ?></p>
            <p><?php esc_html_e( 'This plugin allows you to set the HTML language attribute for up to two languages, custom hreflang codes for those languages, redirect the "language folder" (the secondary language placeholder page) to its respective homepage, map up to 4 primary language pages to their secondary page translations and properly handle language attributes for better SEO and user experience.', 'quick-multilingual' ); ?></p>

            <h2 class="nav-tab-wrapper">
                <a href="#general-settings" class="nav-tab nav-tab-active"><?php esc_html_e( 'General Settings', 'quick-multilingual' ); ?></a>
                <a href="#page-translations" class="nav-tab"><?php esc_html_e( 'Page Translations', 'quick-multilingual' ); ?></a>
            </h2>

            <div class="so_qmp-content-with-sidebar">

                <div class="so_qmp-main-content">

                    <div id="general-settings" class="so_qmp-tab-content">
                        <form method="post" action="options.php">
                            <?php
                            settings_fields( Config::OPTION_GROUP );
                            do_settings_sections( Config::OPTION_GROUP );
                            $picker_uid = 0; // Incremented for each picker to ensure unique IDs
                            ?>
                            <table class="so_qmp_table form-table" role="presentation">

                                <tr>
                                    <th scope="row">
                                        <label for="so_qmp_number_of_languages"><?php esc_html_e( 'Number of Languages', 'quick-multilingual' ); ?></label>
                                    </th>
                                    <td colspan="3">
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
                                        </span>
                                        <span class="so_qmp-premium-teaser">
                                            <img src="<?php echo esc_url( $images_url . 'qml-premium-48.png' ); ?>"
                                                 alt="<?php esc_attr_e( 'Quick Multilingual Premium', 'quick-multilingual' ); ?>"
                                                 class="so_qmp-teaser-logo" />
                                            <span class="so_qmp-teaser-text">
                                                <strong><?php esc_html_e( 'More languages with Premium', 'quick-multilingual' ); ?></strong>
                                                <?php esc_html_e( 'Unlock up to 20 languages and up to 100 page mappings.', 'quick-multilingual' ); ?>
                                                <br><small><?php esc_html_e( 'from EUR 14.99/year', 'quick-multilingual' ); ?></small>
                                            </span>
                                            <a href="https://checkout.freemius.com/plugin/38924/plan/64672/" class="so_qmp-upgrade-btn button">
                                                <span class="dashicons dashicons-lock"></span>
                                                <?php esc_html_e( 'Get Premium', 'quick-multilingual' ); ?>
                                            </a>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="col"><?php esc_html_e( 'Languages', 'quick-multilingual' ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'HTML lang attribute', 'quick-multilingual' ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Hreflang', 'quick-multilingual' ); ?></th>
                                    <th scope="col"><?php esc_html_e( 'Language Folder Page', 'quick-multilingual' ); ?></th>
                                </tr>

                                <tr class="so_qmp-lang-ref-row">
                                    <td></td>
                                    <td colspan="3">
                                        <?php
                                        printf(
                                            wp_kses(
                                                /* translators: %s: URL to lang attributes gist */
                                                __( 'Find all HTML lang attributes <a href="%s" target="_blank">here</a>.', 'quick-multilingual' ),
                                                [ 'a' => [ 'href' => [], 'target' => [] ] ]
                                            ),
                                            'https://gist.github.com/JamieMason/3748498'
                                        );
                                        ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Primary', 'quick-multilingual' ); ?></th>
                                    <td>
                                        <label class="so_qmp-field-label screen-reader-text" for="<?php echo esc_attr( Config::OPT_PRIMARY_LANG ); ?>"><?php esc_html_e( 'HTML lang attribute', 'quick-multilingual' ); ?></label>
                                        <input type="text" id="<?php echo esc_attr( Config::OPT_PRIMARY_LANG ); ?>" name="<?php echo esc_attr( Config::OPT_PRIMARY_LANG ); ?>" value="<?php echo esc_attr( (string) get_option( Config::OPT_PRIMARY_LANG ) ); ?>" class="regular-text" />
                                    </td>
                                    <td>
                                        <label class="so_qmp-field-label screen-reader-text" for="<?php echo esc_attr( Config::OPT_PRIMARY_HREFLANG ); ?>"><?php esc_html_e( 'Hreflang', 'quick-multilingual' ); ?></label>
                                        <input type="text" id="<?php echo esc_attr( Config::OPT_PRIMARY_HREFLANG ); ?>" name="<?php echo esc_attr( Config::OPT_PRIMARY_HREFLANG ); ?>" value="<?php echo esc_attr( (string) get_option( Config::OPT_PRIMARY_HREFLANG ) ); ?>" class="regular-text" />
                                    </td>
                                    <td class="so_qmp-na-cell">
                                        <span class="so_qmp-na"><?php esc_html_e( 'N/A', 'quick-multilingual' ); ?></span>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Secondary', 'quick-multilingual' ); ?></th>
                                    <td>
                                        <label class="so_qmp-field-label screen-reader-text" for="<?php echo esc_attr( Config::OPT_SECONDARY_LANG ); ?>"><?php esc_html_e( 'HTML lang attribute', 'quick-multilingual' ); ?></label>
                                        <input type="text" id="<?php echo esc_attr( Config::OPT_SECONDARY_LANG ); ?>" name="<?php echo esc_attr( Config::OPT_SECONDARY_LANG ); ?>" value="<?php echo esc_attr( (string) get_option( Config::OPT_SECONDARY_LANG ) ); ?>" class="regular-text" />
                                    </td>
                                    <td>
                                        <label class="so_qmp-field-label screen-reader-text" for="<?php echo esc_attr( Config::OPT_SECONDARY_HREFLANG ); ?>"><?php esc_html_e( 'Hreflang', 'quick-multilingual' ); ?></label>
                                        <input type="text" id="<?php echo esc_attr( Config::OPT_SECONDARY_HREFLANG ); ?>" name="<?php echo esc_attr( Config::OPT_SECONDARY_HREFLANG ); ?>" value="<?php echo esc_attr( (string) get_option( Config::OPT_SECONDARY_HREFLANG ) ); ?>" class="regular-text" />
                                    </td>
                                    <td>
                                        <?php
                                        $folder_id = absint( get_option( Config::OPT_FOLDER_PAGE ) );
                                        $folder_title = $folder_id ? get_the_title( $folder_id ) : '';
                                        ?>
                                        <div class="so_qmp-page-picker" data-scope="primary">
                                            <input type="text"
                                                   class="so_qmp-page-search regular-text"
                                                   placeholder="<?php esc_attr_e( 'Search pages…', 'quick-multilingual' ); ?>"
                                                   value="<?php echo esc_attr( $folder_title ); ?>"
                                                   autocomplete="off"
                                                   aria-autocomplete="list"
                                                   aria-controls="so_qmp-results-<?php echo esc_attr( (string) ++$picker_uid ); ?>">
                                            <input type="hidden"
                                                   name="<?php echo esc_attr( Config::OPT_FOLDER_PAGE ); ?>"
                                                   value="<?php echo esc_attr( (string) $folder_id ); ?>">
                                            <ul class="so_qmp-page-results"
                                                id="so_qmp-results-<?php echo esc_attr( (string) $picker_uid ); ?>"
                                                role="listbox"
                                                hidden></ul>
                                        </div>
                                    </td>
                                </tr>

                            </table>
                            <?php submit_button(); ?>
                        </form>
                    </div><!-- #general-settings -->

                    <div id="page-translations" class="so_qmp-tab-content" style="display:none;">
                        <h2><?php esc_html_e( 'Map primary language pages to secondary language pages.', 'quick-multilingual' ); ?></h2>

                        <?php
                        // Language switcher — Premium only (conditional).
                        // Premium hooks onto this action to render the switcher.
                        if ( Config::max_languages() > 2 ) :
                            do_action( 'so_qmp/page_translations/language_switcher' );
                        endif;
                        ?>

                        <form method="post" action="options.php">
                            <?php
                            settings_fields( Config::OPTION_GROUP_MAP );
                            do_settings_sections( Config::OPTION_GROUP_MAP );
                            $current_count = absint( get_option( Config::OPT_NUMBER_OF_PAGES, 1 ) );
                            $folder_id     = absint( get_option( Config::OPT_FOLDER_PAGE ) );
                            $picker_uid    = 0; // Incremented for each picker to ensure unique IDs
                            ?>

                            <!-- Row counter: JS keeps this in sync; submitted with the form -->
                            <input type="hidden"
                                   id="so_qmp-row-count"
                                   name="<?php echo esc_attr( Config::OPT_NUMBER_OF_PAGES ); ?>"
                                   value="<?php echo esc_attr( (string) $current_count ); ?>">

                            <table class="form-table so_qmp_table"
                                   id="page-translations-table"
                                   role="presentation">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col"><?php esc_html_e( 'Primary Language Page', 'quick-multilingual' ); ?></th>
                                        <th scope="col" id="so_qmp-secondary-col-header">
                                            <?php esc_html_e( 'Secondary Language Page', 'quick-multilingual' ); ?>
                                        </th>
                                        <th scope="col" aria-label="<?php esc_attr_e( 'Remove', 'quick-multilingual' ); ?>"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    for ( $i = 1; $i <= $current_count; $i++ ) :
                                        $row            = get_option( Config::option_key( $i ), [] );
                                        $primary_id     = isset( $row['primary'] ) ? absint( $row['primary'] ) : 0;
                                        $secondary_id   = isset( $row['secondary'] ) ? absint( $row['secondary'] ) : 0;
                                        $primary_title  = $primary_id ? get_the_title( $primary_id ) : '';
                                        $secondary_title = $secondary_id ? get_the_title( $secondary_id ) : '';
                                    ?>
                                    <tr class="page-mapping-row" data-row="<?php echo esc_attr( (string) $i ); ?>">
                                        <td><?php echo esc_html( (string) $i ); ?></td>
                                        <td>
                                            <div class="so_qmp-page-picker" data-scope="primary">
                                                <input type="text"
                                                       class="so_qmp-page-search regular-text"
                                                       placeholder="<?php esc_attr_e( 'Search pages…', 'quick-multilingual' ); ?>"
                                                       value="<?php echo esc_attr( $primary_title ); ?>"
                                                       autocomplete="off"
                                                       aria-autocomplete="list"
                                                       aria-controls="so_qmp-results-<?php echo esc_attr( (string) ++$picker_uid ); ?>">
                                                <input type="hidden"
                                                       name="<?php echo esc_attr( Config::option_key( $i ) ); ?>[primary]"
                                                       value="<?php echo esc_attr( (string) $primary_id ); ?>">
                                                <ul class="so_qmp-page-results"
                                                    id="so_qmp-results-<?php echo esc_attr( (string) $picker_uid ); ?>"
                                                    role="listbox"
                                                    hidden></ul>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="so_qmp-page-picker"
                                                 data-scope="secondary"
                                                 data-folder-id="<?php echo esc_attr( (string) $folder_id ); ?>">
                                                <input type="text"
                                                       class="so_qmp-page-search regular-text"
                                                       placeholder="<?php esc_attr_e( 'Search pages…', 'quick-multilingual' ); ?>"
                                                       value="<?php echo esc_attr( $secondary_title ); ?>"
                                                       autocomplete="off"
                                                       aria-autocomplete="list"
                                                       aria-controls="so_qmp-results-<?php echo esc_attr( (string) ++$picker_uid ); ?>">
                                                <input type="hidden"
                                                       name="<?php echo esc_attr( Config::option_key( $i ) ); ?>[secondary]"
                                                       value="<?php echo esc_attr( (string) $secondary_id ); ?>">
                                                <ul class="so_qmp-page-results"
                                                    id="so_qmp-results-<?php echo esc_attr( (string) $picker_uid ); ?>"
                                                    role="listbox"
                                                    hidden></ul>
                                            </div>
                                        </td>
                                        <td class="so_qmp-remove-cell">
                                            <button type="button"
                                                    class="so_qmp-remove-row button-link"
                                                    aria-label="<?php esc_attr_e( 'Remove mapping row', 'quick-multilingual' ); ?>">
                                                −
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endfor; ?>

                                    <!-- Template row — hidden; cloned by JS for new rows -->
                                    <tr id="so_qmp-row-template" class="page-mapping-row" data-row="0"
                                        hidden aria-hidden="true">
                                        <td>–</td>
                                        <td>
                                            <div class="so_qmp-page-picker" data-scope="primary">
                                                <input type="text"
                                                       class="so_qmp-page-search regular-text"
                                                       placeholder="<?php esc_attr_e( 'Search pages…', 'quick-multilingual' ); ?>"
                                                       autocomplete="off"
                                                       aria-autocomplete="list">
                                                <input type="hidden"
                                                       name="so_qmp_page_mapping_0[primary]"
                                                       value="0">
                                                <ul class="so_qmp-page-results" role="listbox" hidden></ul>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="so_qmp-page-picker"
                                                 data-scope="secondary"
                                                 data-folder-id="<?php echo esc_attr( (string) $folder_id ); ?>">
                                                <input type="text"
                                                       class="so_qmp-page-search regular-text"
                                                       placeholder="<?php esc_attr_e( 'Search pages…', 'quick-multilingual' ); ?>"
                                                       autocomplete="off"
                                                       aria-autocomplete="list">
                                                <input type="hidden"
                                                       name="so_qmp_page_mapping_0[secondary]"
                                                       value="0">
                                                <ul class="so_qmp-page-results" role="listbox" hidden></ul>
                                            </div>
                                        </td>
                                        <td class="so_qmp-remove-cell">
                                            <button type="button"
                                                    class="so_qmp-remove-row button-link"
                                                    aria-label="<?php esc_attr_e( 'Remove mapping row', 'quick-multilingual' ); ?>">
                                                −
                                            </button>
                                        </td>
                                    </tr>

                                </tbody>
                            </table><!-- #page-translations-table -->

                            <!-- Add mapping button / Premium CTA -->
                            <div class="so_qmp-add-row-wrap">
                                <button type="button"
                                        id="so_qmp-add-mapping"
                                        class="button"
                                        <?php if ( $current_count >= Config::max_page_mappings() ) echo 'disabled'; ?>>
                                    <?php esc_html_e( '+ Add mapping', 'quick-multilingual' ); ?>
                                </button>

                                <span class="so_qmp-premium-teaser"
                                      id="so_qmp-add-cta"
                                      <?php if ( $current_count < Config::max_page_mappings() ) echo 'hidden'; ?>>
                                    <img src="<?php echo esc_url( $images_url . 'qml-premium-48.png' ); ?>"
                                         class="so_qmp-teaser-logo"
                                         alt="<?php esc_attr_e( 'Quick Multilingual Premium', 'quick-multilingual' ); ?>">
                                    <span class="so_qmp-teaser-text">
                                        <strong><?php esc_html_e( 'More page mappings with Premium', 'quick-multilingual' ); ?></strong>
                                        <?php esc_html_e( 'Unlock up to 20 languages and up to 100 page mappings.', 'quick-multilingual' ); ?>
                                        <br><small><?php esc_html_e( 'from EUR 14.99/year', 'quick-multilingual' ); ?></small>
                                    </span>
                                    <a href="https://checkout.freemius.com/plugin/38924/plan/64672/"
                                       class="button so_qmp-upgrade-btn">
                                        <span class="dashicons dashicons-lock" aria-hidden="true"></span>
                                        <?php esc_html_e( 'Upgrade to Premium', 'quick-multilingual' ); ?>
                                    </a>
                                </span>
                            </div>

                            <?php submit_button(); ?>
                        </form>

                    </div><!-- #page-translations -->

                </div><!-- .so_qmp-main-content -->

                <div class="so_qmp-premium-sidebar">
                    <div class="so_qmp-sidebar-inner">
                        <div class="so_qmp-sidebar-header">
                            <img src="<?php echo esc_url( $images_url . 'qml-premium-64.png' ); ?>"
                                 alt="<?php esc_attr_e( 'Quick Multilingual Premium', 'quick-multilingual' ); ?>"
                                 class="so_qmp-sidebar-logo" />
                            <span class="so_qmp-sidebar-title"><?php esc_html_e( 'Quick Multilingual Premium', 'quick-multilingual' ); ?></span>
                        </div>
                        <ul class="so_qmp-sidebar-features">
                            <li><?php esc_html_e( 'Up to 20 languages', 'quick-multilingual' ); ?></li>
                            <li><?php esc_html_e( 'Up to 100 page mappings', 'quick-multilingual' ); ?></li>
                            <li><?php esc_html_e( 'Full hreflang support for all languages', 'quick-multilingual' ); ?></li>
                            <li><?php esc_html_e( 'Priority support', 'quick-multilingual' ); ?></li>
                        </ul>
                        <p style="text-align: center; margin: 10px 0 15px;">
                            <small><?php esc_html_e( 'from EUR 14.99/year', 'quick-multilingual' ); ?></small>
                        </p>
                        <a href="https://checkout.freemius.com/plugin/38924/plan/64672/" class="so_qmp-sidebar-btn button button-primary">
                            <?php esc_html_e( 'Get Premium', 'quick-multilingual' ); ?>
                        </a>
                    </div>
                </div><!-- .so_qmp-premium-sidebar -->

            </div><!-- .so_qmp-content-with-sidebar -->

        </div><!-- .wrap -->
        <?php
    }
}
