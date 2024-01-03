<?php
/**
 * Support Page
 *
 * @package     AAWP
 * @since       3.4.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if (!class_exists('AAWP_Support')) {

    class AAWP_Support {

        // Variables
        public $options = array();

        private $checks = true;

        private $curl;
        private $curl_exec;
        private $rest;
        private $soap;
        private $mbstring;
        private $fopen;
        private $php;
        private $phpversion = '5.6.0';

        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // Prepare variables
            $this->options = aawp_get_options();

            $this->curl = $this->check_curl();
            $this->curl_exec = $this->check_curl_exec();
            $this->soap = $this->check_soap();
            $this->mbstring = $this->check_mbstring();
            $this->fopen = $this->check_fopen();
            $this->php = $this->check_php();

            // Init menu and settings
            add_action( 'aawp_admin_menu', array( &$this, 'add_admin_menu'), 100 );
            add_action( 'admin_init', array( &$this, 'settings_init' ) );

            if ( ! $this->checks )
                add_filter( 'aawp_admin_menu_show_notification', '__return_true' );
        }

        /**
         * Add main menu
         *
         * @access      Public
         * @since       3.4.0
         * @return      void
         */
        public function add_admin_menu( $parent_menu_slug )
        {

            $menu_title = __( 'Support', 'aawp' );

            if ( ! $this->checks )
                $menu_title = '<span style="color: #d9534f;">' . $menu_title . '</span>';

            add_submenu_page(
                $parent_menu_slug,
                __( 'AAWP - Support', 'aawp' ),
                $menu_title,
                'edit_pages',
                'aawp-support',
                array( &$this, 'render_support_page' )
            );
        }

        /**
         * Settings init
         *
         * @access      Public
         * @since       3.4.0
         * @return      void
         */
        public function settings_init() {

            register_setting(
                'aawp_support',
                'aawp_support',
                array( &$this, 'settings_callback')
            );

            add_settings_section(
                'aawp_support_render',
                false,
                array( &$this, 'settings_section_render' ),
                'aawp_support'
            );

            /*
             * Action to add more settings within this section
             */
            do_action( 'aawp_support_register' );
        }

        /**
         * Settings callbacks
         */
        public function settings_callback( $input ) {

            if ( isset( $input['download_sysinfo'] ) && '1' === $input['download_sysinfo'] ) {
                //aawp_debug_log('ACTION >> download_sysinfo');
                $input['download_sysinfo'] = '0';
            }

            // Handle reschedule events
            if ( isset ( $input['reschedule_events'] ) && $input['reschedule_events'] === '1' ) {
                aawp_add_log( '*** INITIATED RESCHEDULE EVENTS ***' );
                aawp_remove_scheduled_events();
                aawp_check_scheduled_events();
                $input['reschedule_events'] = '0';
            }

            // Handle log reset
            if ( isset ( $input['reset_log'] ) && $input['reset_log'] === '1' ) {
                aawp_delete_log();
                $input['aawp_log'] = '0';
            }

            // Handle clear cache
            if ( isset ( $input['renew_cache'] ) && $input['renew_cache'] === '1' ) {
                aawp_renew_cache();
                $input['renew_cache'] = '0';
            }

            // Handle Reset
            if ( isset ( $input['reset'] ) && $input['reset'] === '1' ) {
                aawp_reset();
                unset($input['reset']);
            }

            // Handle reset database
            if ( isset ( $input['create_database_tables'] ) && $input['create_database_tables'] === '1' ) {
                aawp_add_log( '*** INITIATED CREATION OF DATABASE TABLES ***' );
                aawp_reset_database();
                unset( $input['create_database_tables'] );
            }

            if ( isset ( $input['empty_database_tables'] ) && $input['empty_database_tables'] === '1' ) {
                aawp_add_log( '*** INITIATED EMPTYING OF DATABASE TABLES ***' );
                aawp_empty_database_tables();
                unset( $input['empty_database_tables'] );
            }

            return $input;
        }

        public function settings_section_render() {

            global $wp_version;

            $enabled = '<span style="color: green;"><strong><span class="dashicons dashicons-yes"></span> ' . __('Enabled', 'aawp') . '</strong></span>';
            $disabled = '<span style="color: red;"><strong><span class="dashicons dashicons-no"></span> ' . __('Disabled', 'aawp') . '</strong></span>';

            $uninstall_remove_data = ( isset ( $this->options['support']['uninstall_remove_data'] ) && $this->options['support']['uninstall_remove_data'] == '1' ) ? 1 : 0;
            $debug_mode = ( isset ( $this->options['support']['debug'] ) && $this->options['support']['debug'] == '1' ) ? 1 : 0;
            $debug_log = ( isset ( $this->options['support']['debug_log'] ) && $this->options['support']['debug_log'] == '1' ) ? 1 : 0;

            ?>

            <h3><?php _e('General', 'aawp'); ?></h3>
            <table class="widefat">
                <thead>
                <tr>
                    <th width="300">&nbsp;</th>
                    <th><?php _e('Values', 'aawp'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr class="alternate">
                    <th><?php _e('Core version', 'aawp'); ?></th>
                    <td>
                        <strong><?php echo get_option( 'aawp_version', 'N/A' ); ?></strong>
                        <?php do_action('aawp_admin_support_core_version' ); ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Amazon Product Advertising API', 'aawp'); ?></th>
                    <td>
                        <?php
                        if ( isset ( $this->options['api']['status'] ) && $this->options['api']['status'] ) {
                            echo '<span style="color: green;"><strong><span class="dashicons dashicons-yes"></span> ' . __('Connected', 'aawp') . '</strong></span>';
                        } else {
                            echo '<span style="color: red;"><strong><span class="dashicons dashicons-no"></span> ' . __('Disconnected', 'aawp') . '</strong></span>';
                        }

                        if ( !empty ( $this->options['api']['error'] ) ) {
                            echo '<p style="margin-top: 15px;"><code>' . aawp_get_api_error_message($this->options['api']['error']) . '</code></p>';
                        }
                        ?>
                    </td>
                </tr>
                <?php /*
                <tr class="alternate">
                    <th><?php _e('Plugins', 'aawp'); ?></th>
                    <td>
                        <?php
                        $plugins = apply_filters( 'aawp_settings_plugins', array() );
                        $plugins = array_unique( $plugins );
                        asort($plugins);
                        echo implode( ', ', $plugins );
                        ?>
                    </td>
                </tr>
                <tr>
                    <th><?php _e('Components', 'aawp'); ?></th>
                    <td>
                        <?php
                        $functions = apply_filters( 'aawp_settings_functions', array() );
                        $functions = array_unique( $functions );
                        asort($functions);
                        echo implode( ', ', array_unique( $functions ) );
                        ?>
                    </td>
                </tr>
                */?>
                </tbody>
            </table>

            <p>
                <?php _e('In case one of the values above is <span style="color: red;"><strong>red</strong></span>, please take a look into the documentation for the API registration workflow, review your existing API keys and ensure that your Amazon account is not locked.', 'aawp'); ?>
            </p>

            <h3><?php _e('Database, Cache & Cronjobs', 'aawp'); ?></h3>
            <table class="widefat">
                <thead>
                <tr>
                    <th width="300">&nbsp;</th>
                    <th><?php _e('Info', 'aawp'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th><?php _e('Database', 'aawp'); ?></th>
                    <td>
                        <?php printf( _n( '<strong>%d</strong> product', '<strong>%d</strong> products', aawp_get_products_count(), 'aawp'  ), aawp_get_products_count() ); ?> <?php _e('and', 'aawp'); ?> <?php printf( _n( '<strong>%d</strong> list', '<strong>%d</strong> lists', aawp_get_lists_count(), 'aawp'  ), aawp_get_lists_count() ); ?>
                    </td>
                </tr>
                <!-- TODO: Smart Caching -->
                <tr class="alternate">
                    <th><?php _e('Renew Data Queue', 'aawp'); ?></th>
                    <td>
                        <?php _e( 'N/A', 'aawp' ); ?>
                        <?php /*if ( aawp_smart_caching_activated() ) { ?>
                            <?php printf( _n( '<strong>%d</strong> product', '<strong>%d</strong> products', $renew_products_db_count, 'aawp'  ), $renew_products_db_count ); ?> <?php _e('and', 'aawp'); ?> <?php printf( _n( '<strong>%d</strong> list', '<strong>%d</strong> lists', $renew_lists_db_count, 'aawp'  ), $renew_lists_db_count ); ?>
                        <?php } else {  ?>
                            <em><?php _e('Smart Caching only', 'aawp'); ?></em>
                        <?php }*/ ?>
                    </td>
                </tr>
                <?php /*
                    <tr class="alternate">
                        <th><?php _e('Clear Cache', 'aawp'); ?><span style="color: orange;">*</span></th>
                        <td>
                            <input type="checkbox" id="aawp_support_renew_cache" name="aawp_support[renew_cache]" value="1">
                            <label for="aawp_support_renew_cache"><?php _e('Check in order to remove all data from cache', 'aawp'); ?></label>
                        </td>
                    </tr>
                    */ ?>
                <tr>
                    <th><?php _e('Service Events', 'aawp'); ?></th>
                    <td>
                        <p>
                            <code>aawp_wp_scheduled_events</code> <?php echo ( wp_next_scheduled ( 'aawp_wp_scheduled_events' ) ) ? $enabled : $disabled; ?>
                        </p>
                        <p>
                            <code>aawp_wp_scheduled_hourly_events</code> <?php echo ( wp_next_scheduled ( 'aawp_wp_scheduled_hourly_events' ) ) ? $enabled : $disabled; ?>
                        </p>
                        <p>
                            <code>aawp_wp_scheduled_daily_events</code> <?php echo ( wp_next_scheduled ( 'aawp_wp_scheduled_daily_events' ) ) ? $enabled : $disabled; ?>
                        </p>
                        <p>
                            <code>aawp_wp_scheduled_weekly_events</code> <?php echo ( wp_next_scheduled ( 'aawp_wp_scheduled_weekly_events' ) ) ? $enabled : $disabled; ?>
                        </p>
                        <p>
                            <input type="hidden" id="aawp_support_reschedule_events" name="aawp_support[reschedule_events]" value="0" />
                            <?php submit_button( 'Reschedule Events', 'delete button-secondary', 'aawp-reschedule-events-submit', false ); ?>
                        </p>
                    </td>
                </tr>
                <?php $disable_wp_cron = ( isset ( $this->options['support']['disable_wp_cron'] ) && $this->options['support']['disable_wp_cron'] == '1' ) ? 1 : 0; ?>
                <tr class="alternate">
                    <th><?php _e('Next Cronjob Execution', 'aawp'); ?></th>
                    <?php if ( ! $disable_wp_cron ) { ?>
                        <td><?php $cache_next_update = wp_next_scheduled( 'aawp_wp_scheduled_events' ); echo ( $cache_next_update ) ? aawp_datetime( $cache_next_update ) : 'N/A'; ?></td>
                    <?php } else { ?>
                        <td><?php _e('Disabled', 'aawp'); ?></td>
                    <?php } ?>
                </tr>
                <?php $cache_last_update = aawp_get_cache_last_update(); ?>
                <tr>
                    <th><?php _e('Last update', 'aawp'); ?></th>
                    <td><?php echo ( ! empty ( $cache_last_update ) && is_numeric( $cache_last_update ) ) ? aawp_datetime( $cache_last_update ) : 'N/A'; ?></td>
                </tr>

                <?php do_action( 'aawp_support_cache_table_rows' ); ?>

                </tbody>
            </table>

            <p>
                <strong style="color: orange;">* <?php _e('The cronjob settings are meant for experienced users only!', 'aawp'); ?></strong>
            </p>

            <h3><?php _e('Environment', 'aawp'); ?></h3>
            <table class="widefat">
                <thead>
                <tr>
                    <th width="300"><?php _e('Setting', 'aawp'); ?></th>
                    <th><?php _e('Values', 'aawp'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th>WordPress</th>
                    <td>Version <?php echo $wp_version; ?></td>
                </tr>
                <tr class="alternate">
                    <th>PHP</th>
                    <td>Version <strong><?php echo phpversion(); ?></strong>
                        <?php if ( !$this->php ) { ?>
                            <br /><span style="color: red;"><strong><?php printf( esc_html__( 'PHP Version %1$s or newer required!', 'aawp' ), $this->phpversion ); ?></strong></span>
                        <?php } ?>
                </tr>
                <tr>
                    <th><?php printf( esc_html__( 'PHP "%1$s" extension', 'aawp' ), 'cURL' ); ?></th>
                    <td>
                        <?php echo (isset ($this->curl['enabled']) && $this->curl['enabled']) ? $enabled : $disabled; ?>
                        <?php if (isset ($this->curl['version'])) echo ' (Version ' . $this->curl['version'] . ')'; ?>
                    </td>
                </tr>
                <tr class="alternate">
                    <th><?php printf( esc_html__( 'PHP "%1$s" function', 'aawp' ), 'curl_exec' ); ?></th>
                    <td>
                        <?php echo (isset ($this->curl_exec['enabled']) && $this->curl_exec['enabled']) ? $enabled : $disabled; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php printf( esc_html__( 'PHP "%1$s" extension', 'aawp' ), 'SOAP' ); ?></th>
                    <td>
                        <?php echo (isset ($this->soap['enabled']) && $this->soap['enabled']) ? $enabled : $disabled; ?>
                    </td>
                </tr>
                <tr class="alternate">
                    <th><?php printf( esc_html__( 'PHP "%1$s" extension', 'aawp' ), 'mbstring' ); ?></th>
                    <td>
                        <?php echo (isset ($this->mbstring['enabled']) && $this->mbstring['enabled']) ? $enabled : $disabled; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php printf( esc_html__( 'PHP "%1$s" setting', 'aawp' ), 'allow_url_fopen' ); ?></th>
                    <td>
                        <?php echo ( isset ( $this->fopen['enabled'] ) && $this->fopen['enabled'] ) ? $enabled : $disabled; ?>
                    </td>
                </tr>
                <tr class="alternate">
                    <th><?php printf( esc_html__( 'PHP "%1$s" limit', 'aawp' ), 'max_input_vars' ); ?></th>
                    <td>
                        <?php echo ini_get('max_input_vars' ); ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <p>
                <?php _e('In case one of the values above is <span style="color: red;"><strong>red</strong></span>, please get in contact with your webhoster in order to enable the missing PHP extensions.', 'aawp'); ?>
            </p>

            <p>
                <input type="checkbox" id="aawp_support_uninstall_remove_data" name="aawp_support[uninstall_remove_data]" value="1" <?php echo($uninstall_remove_data == 1 ? 'checked' : ''); ?>>
                <label for="aawp_support_uninstall_remove_data"><?php _e('Remove all plugin data when uninstalling the plugin.', 'aawp'); ?> <strong style="color: red;"><?php _e('This action is not reversible!', 'aawp'); ?></strong></label>
            </p>

            <p>
                <input type="checkbox" id="aawp_support_reset" name="aawp_support[reset]" value="1">
                <label for="aawp_support_reset"><?php _e('Reset plugin settings to default.', 'aawp'); ?> <strong style="color: red;"><?php _e('This action is not reversible!', 'aawp'); ?></strong></label>
            </p>

            <p>
                <input type="checkbox" id="aawp_support_create_database_tables" name="aawp_support[create_database_tables]" value="1">
                <label for="aawp_support_create_database_tables"><?php _e('Create the database tables of the plugin', 'aawp'); ?> <strong style="color: red;"><?php _e('This action is not reversible!', 'aawp'); ?></strong></label>
            </p>

            <p>
                <input type="checkbox" id="aawp_support_empty_database_tables" name="aawp_support[empty_database_tables]" value="1">
                <label for="aawp_support_empty_database_tables"><?php _e('Remove all products and lists from the database tables of the plugin', 'aawp'); ?> <strong style="color: red;"><?php _e('This action is not reversible!', 'aawp'); ?></strong></label>
            </p>

            <p>
                <input type="checkbox" id="aawp_support_debug" name="aawp_support[debug]" value="1" <?php echo($debug_mode == 1 ? 'checked' : ''); ?>>
                <label for="aawp_support_debug"><?php _e('Debug mode', 'aawp'); ?></label>
            </p>

            <?php do_action( 'aawp_support_bottom_render' ); ?>

            <p>
                <strong><?php _e('Debug logs', 'aawp'); ?></strong><br />
                <textarea id="aawp-debug-log" readonly="readonly" onclick="this.focus(); this.select()" rows="10" style="width: 100%;"><?php echo ( ! empty ( $log = aawp_get_log() ) ) ? $log : __( 'No entries yet. ', 'aawp' ); ?></textarea>
            </p>

            <p>
                <input type="checkbox" id="aawp_support_debug_log" name="aawp_support[debug_log]" value="1" <?php echo($debug_log == 1 ? 'checked' : ''); ?>>
                <label for="aawp_support_debug_log"><?php _e('Enable debug logging', 'aawp'); ?></label>
            </p>

            <p>
                <span class="dashicons dashicons-warning" style="color: orange;"></span> <?php printf( wp_kses( __( 'Error codes found? Please check this article: <a href="%s" rel="nofollow" target="_blank">Amazon API Error Codes</a>.', 'aawp' ), array(  'a' => array( 'href' => array(), 'rel' => array( 'nofollow' ), 'target' => array( '_blank' ) ) ) ), esc_url( aawp_get_page_url( 'docs:api_issues' ) ) ); ?>
            </p>

            <?php //echo aawp_format_bytes( mb_strlen( aawp_get_log() , '8bit') ); ?>

            <p style="text-align: right;">
                <input type="hidden" id="aawp_support_reset_log" name="aawp_support[reset_log]" value="0" />
                <?php submit_button( 'Reset log', 'delete button-secondary', 'aawp-reset-log-submit', false ); ?>
                <a id="aawp-download-log-submit" class="button secondary" href="#" download="aawp-log.txt"><?php _e( 'Download Log File', 'aawp' ); ?></a>
            </p>

            <!-- Sysinfo -->
            <textarea readonly="readonly" onclick="this.focus(); this.select()" id="aawp-sysinfo" name="aawp_support[sysinfo]" style="display: none;"><?php echo aawp_get_sysinfo(); ?></textarea>

            <?php
        }


        /**
         * Render Support Page
         *
         * @access      public
         * @since       3.4.0
         * @return      void
         */
        public function render_support_page() {

            if ( ! aawp_is_user_admin() ) {
                wp_die( __('You do not have sufficient permissions to access this page.', 'aawp') );
            }
            ?>

            <div class="wrap aawp-wrap">
            <div id="poststuff">
                <h1>
                    <img class="aawp-icon-settings"
                         src="<?php echo AAWP_PLUGIN_URL . 'assets/img/icon-settings.png'; ?>"/>
                    <?php _e( 'Support', 'aawp' ); ?>
                </h1>

                <?php if ( isset( $_REQUEST['settings-updated'] ) ) { ?>
                    <div class="notice notice-success">
                        <p><strong><?php _e( 'Settings updated.', 'aawp' ) ?></strong></p>
                    </div>
                <?php } ?>

                <?php do_action( 'aawp_support_notices' ); ?>

                <?php //collpress_debug( $this->data_fields_options ); ?>

                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2 aawp-clearfix">
                        <div id="post-body-content">
                            <div class="meta-box-sortables ui-sortable">
                                <div class="postbox">
                                    <div class="inside">
                                        <form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
                                            <?php settings_fields( 'aawp_support' ); ?>
                                            <?php do_settings_sections( 'aawp_support' ); ?>

                                            <p>
                                                <?php submit_button( 'Save Changes', 'button-primary', 'submit', false ); ?>
                                                <a id="aawp-download-sysinfo-submit" class="button secondary" href="#" download="aawp-system-info.txt"><?php _e( 'Download System Info File', 'aawp' ); ?></a>
                                            </p>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="postbox-container-1" class="postbox-container">
                            <div class="meta-box-sortables">
                                <?php do_action( 'aawp_support_infoboxes' ); ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <?php
        }

        public function is_valid ( $checks ) {

            if ( isset ( $checks['curl'] ) && !$this->is_curl_enabled() ) {
                return false;
            }

            return true;
        }

        private function check_curl() {

            if ( ( function_exists('curl_version') ) ) {

                $curl_data = curl_version();
                $version = ( isset ( $curl_data['version'] ) ) ? $curl_data['version'] : null;

                return array(
                    'enabled' => true,
                    'version' => $version
                );
            } else {
                $this->checks = false;
                return false;
            }
        }

        private function check_curl_exec() {

            if ( function_exists( 'curl_exec' ) ) {

                return array(
                    'enabled' => true,
                    'version' => null
                );
            } else {
                $this->checks = false;
                return false;
            }
        }

        public function is_curl_enabled() {

            if ( $this->curl ) {
                return true;
            } else {
                return false;
            }
        }

        public function is_soap_enabled() {

            if ( $this->soap ) {
                return true;
            } else {
                return false;
            }
        }

        public function is_fopen_enabled() {

            if ( $this->fopen ) {
                return true;
            } else {
                return false;
            }
        }

        private function check_soap() {

            if ( ( extension_loaded('soap') ) ) {

                return array(
                    'enabled' => true,
                    'version' => null
                );
            } else {
                $this->checks = false;
                return false;
            }
        }

        private function check_mbstring() {

            if ( ( extension_loaded('mbstring') ) ) {

                return array(
                    'enabled' => true,
                    'version' => null
                );
            } else {
                $this->checks = false;
                return false;
            }
        }

        private function check_fopen() {

            if ( ini_get('allow_url_fopen') ) {

                return array(
                    'enabled' => true,
                    'version' => null
                );
            } else {
                $this->checks = false;
                return false;
            }
        }

        private function check_php() {

            if ( version_compare( phpversion(), $this->phpversion, '<') ) {
                $this->checks = false;
                return false;
            } else {
                return true;
            }
        }
    }

    new AAWP_Support();
}