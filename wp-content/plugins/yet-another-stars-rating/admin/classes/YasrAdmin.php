<?php

if (!defined('ABSPATH')) {
    exit('You\'re not allowed to see this page');
} // Exit if accessed directly


/**
 * Class YasrAdmin
 *
 * @author Dario Curvino <@dudo>
 * @since  3.1.7
 *
 */
class YasrAdmin {
    public function init () {
        if(!is_admin()) {
            return;
        }

        $this->loadActions();
        $this->loadAjaxActions();
        $this->freemiusHooks();
    }

    /**
     * Load add_action that run in admin
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return void
     */
    private function loadActions() {
        //Load yasr scripts
        add_action('admin_enqueue_scripts', array($this, 'addAdminScripts'));

        //Add yasr settings pages
        add_action('admin_menu',     array($this, 'addYasrMenu'));

        add_action('plugins_loaded', array($this, 'updateVersion'));
        add_action('plugins_loaded', array($this, 'widgetLastRatings'));
        add_action('plugins_loaded', array($this, 'editCategoryForm'));
    }

    /**
     * add ajax endpoint in admin side
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return void
     */
    private function loadAjaxActions() {
        add_action('wp_ajax_yasr-admin_change_log_page', static function () {
            $yasr_log_widget_admin = new YasrLastRatingsWidget();
            $yasr_log_widget_admin->returnAjaxResponse(true);
        });
    }

    /**
     * Freemius hooks, actions and filters
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return void
     */
    private function freemiusHooks() {
        /**
         * Customize Freemius permission list TO NOT ALLOW TRANSLATIONS
         * @see yasr-optin-page.php
         */
        yasr_fs()->add_filter('permission_list', static function ($permissions) {
            $permissions[0]['label'] = 'View Basic Profile Info';
            $permissions[0]['desc']  = 'Your WordPress user\'s: first & last name, and email address';

            $permissions[1]['label'] = 'View Basic Website Info';
            $permissions[1]['desc']  = 'Homepage URL & title, WP & PHP versions, and site language';

            $permissions[2]['label'] = 'View Basic Plugin Info';
            $permissions[2]['desc']  = 'Current plugin & SDK versions, and if active or uninstalled';

            $permissions[3]['label'] = 'View Plugins & Themes List';
            $permissions[3]['desc']  = 'Names, slugs, versions, and if active or not';

            return $permissions;
        });

        /**
         * https://freemius.com/help/documentation/selling-with-freemius/free-trials/
         *
         * With this hook I change the default Freemius behavior to show trial message after 1 week instead of 1 day
         */
        yasr_fs()->add_filter( 'show_first_trial_after_n_sec', static function ($day_in_sec) {
            return WEEK_IN_SECONDS;
        } );

        /**
         * https://freemius.com/help/documentation/selling-with-freemius/free-trials/
         *
         * With this hook I change the default Freemius behavior to show trial every 60 days instead of 30
         */
        yasr_fs()->add_filter( 'reshow_trial_after_every_n_sec', static function ($thirty_days_in_sec) {
            return 2 * MONTH_IN_SECONDS;
        } );

        /**
         * Customize optin image
         *
         * https://freemius.com/help/documentation/wordpress-sdk/opt-in-message/#opt_in_icon_customization
         */
        yasr_fs()->add_filter( 'plugin_icon' , static function () {
            return YASR_ABSOLUTE_PATH . '/includes/img/yet-another-stars-rating.png';
        });

        /*
         * This will disable the feedback form when the plugin is disabled
         *
         * https://freemius.com/help/documentation/wordpress-sdk/gists/#disable_deactivation_feedback_form
         */
        yasr_fs()->add_filter( 'show_deactivation_feedback_form', '__return_false' );
    }

    /**
     * Load Scripts in admin side
     *
     * @author Dario Curvino <@dudo>
     *
     * @param $hook | current page in the admin side
     *
     * @return void
     */
    public function addAdminScripts($hook) {
        global $yasr_settings_page;

        if ($hook === 'yet-another-stars-rating_page_yasr_pricing_page'
            || $hook === 'yet-another-stars-rating_page_yasr_settings_page-pricing') {


            if(!isset($_GET['trial'])) {

                wp_enqueue_style(
                    'yasrcss-pricing',
                    YASR_CSS_DIR_ADMIN . 'yasr-pricing-page.css',
                    false,
                    YASR_VERSION_NUM
                );

                YasrScriptsLoader::loadPrincingPage();

            }

        }

        if ($hook === 'index.php'
            || $hook === 'edit.php'
            || $hook === 'post.php'
            || $hook === 'post-new.php'
            || $hook === 'edit-comments.php'
            || $hook === 'term.php'
            || $hook === 'widgets.php'
            || $hook === 'site-editor.php'
            || $hook === 'appearance_page_gutenberg-edit-site'
            || $hook === $yasr_settings_page
            || $hook === 'yet-another-stars-rating_page_yasr_stats_page'
            || $hook === 'yet-another-stars-rating_page_yasr_pricing_page'
            || $hook === 'yet-another-stars-rating_page_yasr_settings_page-pricing'
        ) {
            YasrScriptsLoader::loadRequiredJs();

            /**
             * Add custom script in one of the page used by YASR, at the beginning
             *
             * @param string $hook
             */
            do_action('yasr_add_admin_scripts_begin', $hook);

            YasrScriptsLoader::loadTippy();
            YasrScriptsLoader::loadYasrAdmin();

            wp_enqueue_style(
                'yasrcss',
                YASR_CSS_DIR_ADMIN . 'yasr-admin.css',
                false,
                YASR_VERSION_NUM
            );

            /**
             * Add custom script in one of the page used by YASR, at the end
             *
             * @param string $hook
             */
            do_action('yasr_add_admin_scripts_end', $hook);
        }

        if ($hook === 'post.php' || $hook === 'post-new.php') {
            YasrScriptsLoader::loadClassicEditor();
        }

        //add this only in yasr setting page (admin.php?page=yasr_settings_page)
        if ($hook === $yasr_settings_page) {
            YasrScriptsLoader::loadCodeEditor();
            YasrScriptsLoader::loadAdminSettings();
            YasrScriptsLoader::loadTableCss();
        }

        if($hook === 'yet-another-stars-rating_page_yasr_stats_page') {
            YasrScriptsLoader::loadAdminSettings();
        }

    }

    /**
     * Add YASR Menu
     *
     * @author Dario Curvino <@dudo>
     * @return void
     */
    function addYasrMenu() {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $yasr_settings_page;

        $settings_menu_title = esc_html__('Settings', 'yet-another-stars-rating');

        $stats_menu_title    = esc_html__('Manage Ratings', 'yet-another-stars-rating');

        //Add Settings Page
        $yasr_settings_page = add_menu_page(
            'Yet Another Stars Rating: settings',
            'Yet Another Stars Rating', //Menu Title
            'manage_options', //capability
            'yasr_settings_page', //menu slug
            array($this, 'addSettingsPages'), //The function to be called to output the content for this page.
            'dashicons-star-half'
        );

        add_submenu_page(
            'yasr_settings_page',
            'Yet Another Stars Rating: settings',
            $settings_menu_title,
            'manage_options',
            'yasr_settings_page'
        );

        add_submenu_page(
            'yasr_settings_page',
            'Yet Another Stars Rating: All Ratings',
            $stats_menu_title,
            'manage_options',
            'yasr_stats_page',
            array($this, 'addStatsPage')
        );

        //Filter the pricing page only if trial is not set
        if(isset($_GET['page']) && $_GET['page'] === 'yasr_settings_page-pricing' && !isset($_GET['trial'])) {
            yasr_fs()->add_filter( 'templates/pricing.php', array($this, 'pricingPage') );
        }

    }

    /**
     * Add Yasr settings pages
     *
     * @author Dario Curvino <@dudo>
     * @return void
     */
    function addSettingsPages() {
        if (!current_user_can('manage_options')) {
            /** @noinspection ForgottenDebugOutputInspection */
            wp_die(__('You do not have sufficient permissions to access this page.', 'yet-another-stars-rating'));
        }

        include(YASR_ABSOLUTE_PATH_ADMIN . '/settings/yasr-settings.php');
    } //End yasr_settings_page_content

    /**
     * Add Yasr stats pages
     *
     * @author Dario Curvino <@dudo>
     * @return void
     */
    function addStatsPage() {
        if (!current_user_can('manage_options')) {
            /** @noinspection ForgottenDebugOutputInspection */
            wp_die(__('You do not have sufficient permissions to access this page.', 'yet-another-stars-rating'));
        }

        include(YASR_ABSOLUTE_PATH_ADMIN . '/settings/yasr-stats-page.php');
    }

    /**
     * Custom pricing page
     *
     * @author Dario Curvino <@dudo>
     * @return void
     */
    function pricingPage() {
        if (!current_user_can('manage_options')) {
            /** @noinspection ForgottenDebugOutputInspection */
            wp_die(__('You do not have sufficient permissions to access this page.', 'yet-another-stars-rating'));
        }

        include(YASR_ABSOLUTE_PATH_ADMIN . '/settings/yasr-pricing-page.html');
    }

    /**
     * Update version number and backward compatibility
     *
     * Since version 3.0.4 there is the class YasrSettingsValues, which return the default correct settings if not exists.
     * So, when a new release has a new option, there is no more need to insert it here
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return void
     */
    public function updateVersion() {
        //do only in admin
        if (is_admin() && current_user_can('activate_plugins')) {
            global $wpdb;

            $yasr_stored_options = get_option('yasr_general_options');

            if (YASR_VERSION_INSTALLED !== false) {
                //In version 2.9.7 the column comment_id is added
                //Remove Dec 2023
                if (version_compare(YASR_VERSION_INSTALLED, '2.9.7') === -1) {
                    $wpdb->query("ALTER TABLE " . YASR_LOG_MULTI_SET . " ADD comment_id bigint(20) NOT NULL AFTER post_id");
                }

                //Since version 3.3.9 IP is enabled by default
                //Remove Gen 2024
                if (version_compare(YASR_VERSION_INSTALLED, '3.3.9') === -1) {
                    $yasr_stored_options['enable_ip'] = 'yes';
                }

                update_option('yasr_general_options', $yasr_stored_options);
            } //Endif yasr_version_installed !== false
            /****** End backward compatibility functions ******/

            //update version num
            if (YASR_VERSION_INSTALLED !== YASR_VERSION_NUM) {
                update_option('yasr-version', YASR_VERSION_NUM);
            }

        }

    }


    /**
     * Adds widget to show last ratings in dashboard
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return void
     */
    public function widgetLastRatings() {
        //This is for the admins (show all votes in the site)
        if (current_user_can('manage_options')) {
            add_action('wp_dashboard_setup', array($this, 'lastRatingsAdmin'));
        }

        //This is for all the users to see where they've voted
        add_action('wp_dashboard_setup', array($this, 'lastRatingsUser'));
    }

    /**
     * Add widget for admin, show all ratings
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return void
     */
    public function lastRatingsAdmin() {
        wp_add_dashboard_widget(
            'yasr_widget_log_dashboard', //slug for widget
            '&#11088; YASR: Recent Ratings', //widget name
            array($this, 'loadDashboardWidgetAdmin') //function callback
        );
    }

    /**
     * Add widget for user, show all ratings that user give
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return void
     */
    public function lastRatingsUser() {
        wp_add_dashboard_widget(
            'yasr_users_dashboard_widget', //slug for widget
            '&#11088; YASR: Your Ratings', //widget name
            array($this, 'loadDashboardWidgetUser') //function callback
        );
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return void
     */
    public function loadDashboardWidgetAdmin() {
        $log_widget = new YasrLastRatingsWidget();
        echo yasr_kses($log_widget->adminWidget());
    } //End callback function

    /**
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return void
     */
    function loadDashboardWidgetUser() {
        $log_widget = new YasrLastRatingsWidget();
        echo yasr_kses($log_widget->userWidget());
    } //End callback function

    /**
     * Hook into category page to show YASR select
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return void
     */
    public function editCategoryForm () {
        if (current_user_can('manage_options')) {
            $edit_category = new YasrEditCategory();
            $edit_category->init();
        }
    }

    /**
     * Check if the current page is the Gutenberg block editor.
     *
     * @since  2.2.3
     * @return bool
     */
    public static function isGutenbergPage() {
        if (function_exists('is_gutenberg_page') && is_gutenberg_page()) {
            // The Gutenberg plugin is on.
            return true;
        }
        $current_screen = get_current_screen();

        if ($current_screen !== null
            && method_exists($current_screen, 'is_block_editor')
            && $current_screen->is_block_editor()
        ) {
            // Gutenberg page on 5+.
            return true;
        }

        return false;
    }

    /**
     * Multisite support, on new site creation
     *
     * @author Dario Curvino <@dudo>
     *
     * @param \WP_Site $new_site
     *
     * @return void
     */
    public static function onCreateBlog(WP_Site $new_site) {
        if (is_plugin_active_for_network('yet-another-stars-rating/yet-another-stars-rating.php')) {
            switch_to_blog($new_site->blog_id);
            YasrOnInstall::createTables();
            restore_current_blog();
        }
    }

    /**
     * @author Dario Curvino <@dudo>
     *
     * @param $tables
     *
     * @return mixed
     */
    public static function onDeleteBlog($tables) {
        global $wpdb;

        $prefix = $wpdb->prefix . 'yasr_';  //Table prefix

        $yasr_multi_set_table  = $prefix . 'multi_set';
        $yasr_multi_set_fields = $prefix . 'multi_set_fields';
        $yasr_log_multi_set    = $prefix . 'log_multi_set';
        $yasr_log_table        = $prefix . 'log';

        $tables[] = $yasr_multi_set_table;
        $tables[] = $yasr_multi_set_fields;
        $tables[] = $yasr_log_multi_set;
        $tables[] = $yasr_log_table;

        return $tables;
    }

}
