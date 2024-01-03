<?php
/**
 * Plugin Name:     AAWP
 * Plugin URI:      https://getaawp.com
 * Description:     The best WordPress plugin for Amazon Affiliates.
 * Version:         3.17.3
 * Author:          AAWP
 * Author URI:      https://getaawp.com
 * Text Domain:     aawp
 *
 * @package         AAWP
 * @author          AAWP
 * @copyright       Copyright (c) AAWP
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'AAWP' ) ) {

    /**
     * Main AAWP class
     *
     * @since       2.0.0
     */
    final class AAWP {
        /** Singleton *************************************************************/

        /**
         * AAWP instance.
         *
         * @access private
         * @since  3.9.0
         * @var    AAWP The one true AAWP
         */
        private static $instance;

        /**
         * The version number of AAWP.
         *
         * @access private
         * @since  3.9.0
         * @var    string
         */
        private $version = '3.17.3';

        /**
         * The settings instance variable.
         *
         * @access public
         * @since  3.9.0
         * @var    AAWP_Settings
         */
        public $settings;

        /**
         * The api instance variable.
         *
         * @access public
         * @since  3.9.0
         * @var    AAWP_API
         */
        public $api;

        /**
         * The products instance variable.
         *
         * @access public
         * @since  3.9.0
         * @var    AAWP_DB_Products
         */
        public $products;

        /**
         * The lists instance variable.
         *
         * @access public
         * @since  3.9.0
         * @var    AAWP_DB_Lists
         */
        public $lists;

        /**
         * Main AAWP Instance
         *
         * Insures that only one instance of AAWP exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @since 1.0
         * @static
         * @staticvar array $instance
         * @uses AAWP::setup_globals() Setup the globals needed
         * @uses AAWP::includes() Include the required files
         * @uses AAWP::setup_actions() Setup the hooks and actions
         * @return AAWP
         */
        public static function instance() {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AAWP ) ) {
                self::$instance = new AAWP;

                if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {

                    add_action( 'admin_notices', array( 'AAWP', 'below_php_version_notice' ) );

                    return self::$instance;
                }

                self::$instance->setup_constants();
                self::$instance->includes();

                add_action( 'plugins_loaded', array( self::$instance, 'setup_objects' ), -1 );
                add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
                add_action( 'plugins_loaded', array( self::$instance, 'updater' ) );
            }
            return self::$instance;
        }

        /**
         * Throw error on object clone
         *
         * The whole idea of the singleton design pattern is that there is a single
         * object therefore, we don't want the object to be cloned.
         *
         * @since 3.9.0
         * @access protected
         * @return void
         */
        public function __clone() {
            // Cloning instances of the class is forbidden
            _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'aawp' ), '1.0' );
        }

        /**
         * Disable unserializing of the class
         *
         * @since 3.9.0
         * @access protected
         * @return void
         */
        public function __wakeup() {
            // Unserializing instances of the class is forbidden
            _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'aawp' ), '1.0' );
        }

        /**
         * Show a warning to sites running PHP < 5.3
         *
         * @static
         * @access private
         * @since 3.9.0
         * @return void
         */
        public static function below_php_version_notice() {
            ?>
            <div class="error">
                <p>
                    <?php sprintf( esc_html__( 'Your version of PHP is below the minimum version of PHP required by our plugin. Please contact your hosting company and request that your version will be upgraded to %1$s or later.', 'aawp' ), '5.6' ); ?>
                </p>
            </div>
            <?php
        }

        /**
         * Setup plugin constants
         *
         * @access private
         * @since 3.9.0
         * @return void
         */
        private function setup_constants() {

            // Plugin version
            if ( ! defined( 'AAWP_VERSION' ) ) {
                define( 'AAWP_VERSION', $this->version );
            }

            // Plugin Folder Path
            if ( ! defined( 'AAWP_PLUGIN_DIR' ) ) {
                define( 'AAWP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            }

            // Plugin Folder URL
            if ( ! defined( 'AAWP_PLUGIN_URL' ) ) {
                define( 'AAWP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
            }

            // Plugin Root File
            if ( ! defined( 'AAWP_PLUGIN_FILE' ) ) {
                define( 'AAWP_PLUGIN_FILE', __FILE__ );
            }

            // Docs URL
            if ( ! defined( 'AAWP_DOCS_URL' ) ) {
                define( 'AAWP_DOCS_URL', 'https://getaawp.com/docs/' );
            }

            // Plugin Settings URL
            if ( ! defined('AAWP_ADMIN_SETTINGS_URL') ) {
                define( 'AAWP_ADMIN_SETTINGS_URL', admin_url( 'admin.php?page=aawp-settings' ) );
            }

            // Store
            if ( ! defined( 'AAWP_STORE' ) ) {
                $api_settings = get_option( 'aawp_api' );
                $aawp_store = ( ! empty ( $api_settings['country'] ) ) ? $api_settings['country'] : '';

                define( 'AAWP_STORE', $aawp_store );
            }

            // Shortcode
            if ( ! defined( 'AAWP_SHORTCODE' ) ) {
                $general_settings = get_option( 'aawp_general' );
                $aawp_shortcode = ( ! empty ( $general_settings['shortcode'] ) ) ? $general_settings['shortcode'] : 'amazon';

                define( 'AAWP_SHORTCODE', $aawp_shortcode );
            }

            // Placeholder Tracking ID
            if ( ! defined( 'AAWP_PLACEHOLDER_TRACKING_ID' ) ) {
                define( 'AAWP_PLACEHOLDER_TRACKING_ID', 'AAWP_PLACEHOLDER_TRACKING_ID' );
            }
        }

        /**
         * Include required files
         *
         * @access private
         * @since 3.9.0
         * @return void
         */
        private function includes() {

            // Core files
            require_once AAWP_PLUGIN_DIR . 'includes/helper.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions.php';
            require_once AAWP_PLUGIN_DIR . 'includes/license-functions.php';
            require_once AAWP_PLUGIN_DIR . 'includes/class.aawp-db.php';
            require_once AAWP_PLUGIN_DIR . 'includes/class.aawp-db-products.php';
            require_once AAWP_PLUGIN_DIR . 'includes/class.aawp-db-lists.php';
            require_once AAWP_PLUGIN_DIR . 'includes/class.aawp-api.php';
            require_once AAWP_PLUGIN_DIR . 'includes/hooks.php';
            require_once AAWP_PLUGIN_DIR . 'includes/class.aawp-wrapper.php';
            require_once AAWP_PLUGIN_DIR . 'includes/class.aawp-core.php';
            require_once AAWP_PLUGIN_DIR . 'includes/class.aawp-functions.php';
            require_once AAWP_PLUGIN_DIR . 'includes/class.aawp-review-crawler.php';
            require_once AAWP_PLUGIN_DIR . 'includes/api-functions.php';
            require_once AAWP_PLUGIN_DIR . 'includes/cache-functions.php';
            require_once AAWP_PLUGIN_DIR . 'includes/class.cache-handler.php';
            require_once AAWP_PLUGIN_DIR . 'includes/class.template-functions.php';
            require_once AAWP_PLUGIN_DIR . 'includes/class.template-handler.php';
            require_once AAWP_PLUGIN_DIR . 'includes/list-functions.php';
            require_once AAWP_PLUGIN_DIR . 'includes/list-helper-functions.php';
            require_once AAWP_PLUGIN_DIR . 'includes/product-functions.php';
            require_once AAWP_PLUGIN_DIR . 'includes/product-helper-functions.php';
            require_once AAWP_PLUGIN_DIR . 'includes/shortcodes.php';
            require_once AAWP_PLUGIN_DIR . 'includes/class.aawp-product.php';
            require_once AAWP_PLUGIN_DIR . 'includes/scripts.php';

            // Functions
            require_once AAWP_PLUGIN_DIR . 'includes/functions/deprecated/functions.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/deprecated/class.widget.php';

            require_once AAWP_PLUGIN_DIR . 'includes/functions/widgets/functions.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/widgets/class.widget-bestseller.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/widgets/class.widget-box.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/widgets/class.widget-new-releases.php';

            require_once AAWP_PLUGIN_DIR . 'includes/functions/components/affiliate-links-extended.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/components/amp.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/components/cronjobs.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/components/customizations.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/components/geotargeting.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/components/items.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/components/stores.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/components/table-builder.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/components/templating.php';

            require_once AAWP_PLUGIN_DIR . 'includes/functions/class.bestseller.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/class.box.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/class.extended.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/class.fields.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/class.link.php';
            require_once AAWP_PLUGIN_DIR . 'includes/functions/class.new-releases.php';

            // License & Updates
            require_once AAWP_PLUGIN_DIR . 'includes/libraries/class.license-handler.php';
            require_once AAWP_PLUGIN_DIR . 'includes/libraries/class.plugin-updater.php';

            // Admin files
            if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

                // Bootstrap.
                require_once AAWP_PLUGIN_DIR . 'includes/admin/plugins.php';

                // Core
                require_once AAWP_PLUGIN_DIR . 'includes/admin/functions.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/actions.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/ajax.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/hooks.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/pages.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/post-search.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/class.settings.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/dashboard-page.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/class.support.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/sysinfo.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/modals.php';

                // Settings
                require_once AAWP_PLUGIN_DIR . 'includes/admin/class.settings-license.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/class.settings-api.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/class.settings-general.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/class.settings-output.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/class.settings-functions.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/infoboxes.php';

                // Pages
                require_once AAWP_PLUGIN_DIR . 'includes/admin/list-edit.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/list-overview.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/product-edit.php';
                require_once AAWP_PLUGIN_DIR . 'includes/admin/product-overview.php';

                // Upgrades
                require_once AAWP_PLUGIN_DIR . 'includes/admin/upgrades.php';
            }
        }

        /**
         * Setup all objects
         *
         * @access public
         * @since 1.6.2
         * @return void
         */
        public function setup_objects() {

            self::$instance->api = new AAWP_API();
            self::$instance->products = new AAWP_DB_Products();
            self::$instance->lists = new AAWP_DB_Lists();

            if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
                self::$instance->settings = new AAWP_Settings();
            }
        }

        /**
         * Plugin Updater
         *
         * @access private
         * @since 1.0
         * @return void
         */
        public function updater() {

            if ( ! is_admin() || ! class_exists( 'AAWP_Plugin_Updater' ) ) {
                return;
            }

            // Get licensing
            $licensing = get_option( 'aawp_licensing', array() );

            if ( ! empty( $licensing['key'] ) ) {

                $license_server = aawp_get_license_server();

                if ( ! empty( $license_server['url'] ) && ! empty( $license_server['item_id'] ) && ! empty( $license_server['item_name'] )  ) {

                    $AAWP_Plugin_Updater = new AAWP_Plugin_Updater( trailingslashit( $license_server['url'] ) . 'edd-sl-api/', __FILE__, array(
                            'version'   => AAWP_VERSION,
                            'license'   => $licensing['key'],
                            'item_id'   => $license_server['item_id'],
                            'item_name' => $license_server['item_name'],
                            'author'    => 'fdmedia',
                            'url'       => home_url(),
                            'beta'      => false,
                        )
                    );
                }
            }
        }

        /**
         * Loads the plugin language files
         *
         * @access public
         * @since 1.0
         * @return void
         */
        public function load_textdomain() {

            // Set filter for plugin's languages directory
            $lang_dir = dirname( plugin_basename( AAWP_PLUGIN_FILE ) ) . '/languages/';

            /**
             * Filters the languages directory path to use for AAWP.
             *
             * @param string $lang_dir The languages directory path.
             */
            $lang_dir = apply_filters( 'aawp_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter

            global $wp_version;

            $get_locale = get_locale();

            if ( $wp_version >= 4.7 ) {
                $get_locale = get_user_locale();
            }

            /**
             * Defines the plugin language locale used in AAWP.
             *
             * @var $get_locale The locale to use. Uses get_user_locale()` in WordPress 4.7 or greater,
             *                  otherwise uses `get_locale()`.
             */
            $locale = apply_filters( 'plugin_locale', $get_locale, 'aawp' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'aawp', $locale );

            // Setup paths to current locale file
            $mofile_local  = $lang_dir . $mofile;
            $mofile_global = WP_LANG_DIR . '/aawp/' . $mofile;

            if ( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/aawp/ folder
                load_textdomain( 'aawp', $mofile_global );
            } elseif ( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/aawp/languages/ folder
                load_textdomain( 'aawp', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'aawp', false, $lang_dir );
            }
        }
    }
} // End if class_exists check

/**
 * The main function responsible for returning the one true AAWP
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $aawp = aawp(); ?>
 *
 * @since 1.0
 * @return AAWP The one true AAWP Instance
 */
function aawp() {
    return AAWP::instance();
}
aawp();

/**
 * The activation hook
 */
function aawp_activation() {

    // Installation
    require_once plugin_dir_path( __FILE__ ) . '/includes/install.php';

    if ( function_exists( 'aawp_run_install' ) )
        aawp_run_install();
}
register_activation_hook( __FILE__, 'aawp_activation' );