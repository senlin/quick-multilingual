<?php

namespace QuickMultilingual;

class Admin {

    public function enqueue_admin_scripts($hook) {
        if ('settings_page_quick-multilingual' !== $hook) {
            return;
        }

        wp_enqueue_script('so_qmp_admin_script', plugins_url('js/admin.js', __FILE__), ['jquery'], '1.0', true);
        wp_enqueue_style('so_qmp_admin_style', plugins_url('css/admin.css', __FILE__));

        $translation_array = [
            'select_option' => esc_html__('— Select —', 'quick-multilingual')
        ];
        wp_localize_script('so_qmp_admin_script', 'so_qmp_vars', $translation_array);
    }

    public function register_settings() {
        register_setting('so_qmp_settings_group', 'so_qmp_primary_lang', 'sanitize_text_field');
        register_setting('so_qmp_settings_group', 'so_qmp_secondary_lang', 'sanitize_text_field');
        register_setting('so_qmp_settings_group', 'so_qmp_primary_hreflang', 'sanitize_text_field');
        register_setting('so_qmp_settings_group', 'so_qmp_secondary_hreflang', 'sanitize_text_field');
        register_setting('so_qmp_settings_group', 'so_qmp_language_folder_page', 'absint');
        register_setting('so_qmp_settings_group', 'so_qmp_number_of_pages', 'absint');

        for ($i = 1; $i <= 4; $i++) {
            register_setting('so_qmp_page_translations_group', 'so_qmp_page_mapping_' . $i, [$this, 'sanitize_page_mapping']);
        }
    }

    public function sanitize_page_mapping($input) {
        $sanitized_input = [];
        if (isset($input['primary'])) {
            $sanitized_input['primary'] = absint($input['primary']);
        }
        if (isset($input['secondary'])) {
            $sanitized_input['secondary'] = absint($input['secondary']);
        }
        return $sanitized_input;
    }

    public function create_options_page() {
        add_options_page(
            esc_html__('Quick Multilingual Settings', 'quick-multilingual'),
            esc_html__('Quick Multilingual', 'quick-multilingual'),
            'manage_options',
            'quick-multilingual',
            [$this, 'options_page_html']
        );
    }

    public function options_page_html() {
        if (!current_user_can('manage_options')) {
            return;
        }

        settings_errors('so_qmp_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p><?php esc_html_e('Quick Multilingual is a WordPress plugin designed to enhance multilingual websites by adjusting the HTML lang attribute and adding hreflang tags.', 'quick-multilingual'); ?></p>
            <p><?php esc_html_e('This plugin allows you to set the HTML language attribute for up to two languages, custom hreflang codes for those languages, redirect the "language folder" (the secondary language placeholder page) to its respective homepage, map up to 4 primary language pages to their secondary page translations and properly handle language attributes for better SEO and user experience.', 'quick-multilingual'); ?></p>

            <h2 class="nav-tab-wrapper">
                <a href="#general-settings" class="nav-tab"><?php esc_html_e('General Settings', 'quick-multilingual'); ?></a>
                <a href="#page-translations" class="nav-tab"><?php esc_html_e('Page Translations', 'quick-multilingual'); ?></a>
            </h2>

            <div id="general-settings" class="so_qmp-tab-content">
                <h3><?php
                    printf(
                        wp_kses(
                            __('Find all HTML lang attributes <a href="%s" target="_blank">here</a>.', 'quick-multilingual'),
                            ['a' => ['href' => [], 'target' => []]]
                        ),
                        'https://gist.github.com/JamieMason/3748498'
                    );
                ?></h3>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('so_qmp_settings_group');
                    do_settings_sections('so_qmp_settings_group');
                    ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('HTML lang attribute primary language', 'quick-multilingual'); ?></th>
                            <td><input type="text" name="so_qmp_primary_lang" value="<?php echo esc_attr(get_option('so_qmp_primary_lang')); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('HTML lang attribute secondary language', 'quick-multilingual'); ?></th>
                            <td><input type="text" name="so_qmp_secondary_lang" value="<?php echo esc_attr(get_option('so_qmp_secondary_lang')); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Hreflang primary language', 'quick-multilingual'); ?></th>
                            <td><input type="text" name="so_qmp_primary_hreflang" value="<?php echo esc_attr(get_option('so_qmp_primary_hreflang')); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Hreflang secondary language', 'quick-multilingual'); ?></th>
                            <td><input type="text" name="so_qmp_secondary_hreflang" value="<?php echo esc_attr(get_option('so_qmp_secondary_hreflang')); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Language Folder Page', 'quick-multilingual'); ?></th>
                            <td>
                                <?php
                                wp_dropdown_pages([
                                    'name' => 'so_qmp_language_folder_page',
                                    'selected' => get_option('so_qmp_language_folder_page'),
                                    'show_option_none' => esc_html__('— Select —', 'quick-multilingual'),
                                    'option_none_value' => '0'
                                ]);
                                ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Number of Pages to Map', 'quick-multilingual'); ?></th>
                            <td>
                                <input type="number" id="so_qmp_number_of_pages" name="so_qmp_number_of_pages" value="<?php echo esc_attr(get_option('so_qmp_number_of_pages', 1)); ?>" min="1" max="4" />
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>

            <div id="page-translations" class="so_qmp-tab-content" style="display:none;">
                <h3><?php esc_html_e('Here you can map the pages of the primary language to the secondary language.', 'quick-multilingual'); ?></h3>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('so_qmp_page_translations_group');
                    do_settings_sections('so_qmp_page_translations_group');

                    $number_of_pages = intval(get_option('so_qmp_number_of_pages', 1));
                    ?>
                    <table class="form-table" id="page-translations-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Page', 'quick-multilingual'); ?></th>
                                <th><?php esc_html_e('Primary Language Page', 'quick-multilingual'); ?></th>
                                <th><?php esc_html_e('Secondary Language Page', 'quick-multilingual'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            for ($i = 1; $i <= $number_of_pages; $i++) {
                                $page_mapping = get_option('so_qmp_page_mapping_' . $i, []);
                                $primary_page = isset($page_mapping['primary']) ? $page_mapping['primary'] : 0;
                                $secondary_page = isset($page_mapping['secondary']) ? $page_mapping['secondary'] : 0;
                                ?>
                                <tr valign="top" class="page-mapping-row">
                                    <td><?php echo esc_html(sprintf(__('Page %d', 'quick-multilingual'), $i)); ?></td>
                                    <td>
                                        <?php
                                        $language_folder_page_id = get_option('so_qmp_language_folder_page');
                                        $exclude_pages = [$language_folder_page_id];
                                        $children_pages = get_pages(['child_of' => $language_folder_page_id]);
                                        foreach ($children_pages as $child_page) {
                                            $exclude_pages[] = $child_page->ID;
                                        }

                                        wp_dropdown_pages([
                                            'name' => 'so_qmp_page_mapping_' . $i . '[primary]',
                                            'selected' => $primary_page,
                                            'exclude' => implode(',', $exclude_pages),
                                            'show_option_none' => esc_html__('— Select —', 'quick-multilingual'),
                                            'option_none_value' => '0'
                                        ]);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        wp_dropdown_pages([
                                            'name' => 'so_qmp_page_mapping_' . $i . '[secondary]',
                                            'selected' => $secondary_page,
                                            'child_of' => $language_folder_page_id,
                                            'show_option_none' => esc_html__('— Select —', 'quick-multilingual'),
                                            'option_none_value' => '0'
                                        ]);
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
            </div>
        </div>
        <?php
    }
}