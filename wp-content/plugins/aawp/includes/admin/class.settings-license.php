<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'AAWP_Settings_License' ) ) {

    class AAWP_Settings_License extends AAWP_Functions {

        public $licensing_server_debug;

        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // Call parent constructor first
            parent::__construct();

            $this->options['licensing'] = get_option( 'aawp_licensing', array() );
            $this->licensing_server_debug = ( isset( $this->options['support']['licensing_server_debug'] ) && $this->options['support']['licensing_server_debug'] == '1' ) ? true : false;

            // Settings functions
            add_filter( $this->settings_tabs_filter, array( &$this, 'add_settings_tabs_filter' ) );
            add_filter( $this->settings_functions_filter, array( &$this, 'add_settings_functions_filter' ) );

            add_action( 'admin_init', array( &$this, 'add_settings' ) );
            add_action( 'aawp_admin_support_core_version', array( &$this, 'admin_support_core_version_update_info' ) );
            add_filter( 'aawp_admin_notices', array( &$this, 'admin_notice_update_info' ) );
        }

        /**
         * Add settings functions
         */
        public function add_settings_tabs_filter( $tabs ) {

            $tabs[0] = array(
                'key' => 'licensing',
                'icon' => 'admin-network',
                'title' => __('Licensing', 'aawp')
            );

            return $tabs;
        }

        public function add_settings_functions_filter( $functions ) {

            $functions[] = 'licensing';

            return $functions;
        }

        /**
         * Admin support info plugin version
         */
        public function admin_support_core_version_update_info() {

            $remote_plugin_version = $this->get_remote_plugin_version();

            if ( $this->is_plugin_update_required() ) {
                $info_color = 'darkorange';
                $info_icon = 'warning';
                $info_text = sprintf( esc_html__( 'Update v%1$s available', 'aawp' ), $remote_plugin_version );
            } else {
                $info_color = 'green';
                $info_icon = 'yes';
                $info_text = sprintf( esc_html__( 'Latest version installed', 'aawp' ), $remote_plugin_version );
            }

            ?>
            &nbsp;<span style="color: <?php echo $info_color; ?>; font-weight: bold;" ><span class="dashicons dashicons-<?php echo $info_icon; ?>"></span>&nbsp;<?php echo $info_text; ?></span>
            <?php
        }

        /**
         * Admin notice update info
         */
        public function admin_notice_update_info( $notices ) {

            if ( ! $this->is_plugin_update_required() )
                return $notices;

            if ( empty( $this->options['licensing']['info'] ) )
                return $notices;

            // Manual update info
            $message = sprintf( wp_kses( __( '<a href="%s">Update available</a>.', 'aawp' ), array(  'a' => array( 'href' => array() ) ) ), admin_url( 'update-core.php' ) );
            $message .= '&nbsp;';
            $message .= sprintf( wp_kses( __( 'In case the update does not show up correctly or you prefer updating the plugin by hand, please take a look at our <a href="%s" target="_blank">documentation</a> for a manual plugin update.', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), ( aawp_is_lang_de() ) ? 'https://aawp.de/docs/article/plugin-aktualisieren/' : 'https://getaawp.com/docs/article/updating-the-plugin/' );

            $notices[] = array(
                'force' => false,
                'type' => 'warning',
                'dismiss' => false,
                'message' => $message
            );

            return $notices;
        }


        /**
         * Settings: Register section and fields
         */
        public function add_settings() {

            register_setting(
                'aawp_licensing',
                'aawp_licensing',
                array( &$this, 'settings_licensing_callback')
            );

            add_settings_section(
                'aawp_licensing_section',
                __('Licensing', 'aawp'),
                array( &$this, 'settings_licensing_section_callback' ),
                'aawp_licensing'
            );

            add_settings_field(
                'aawp_licensing_status',
                __( 'Status', 'aawp' ),
                array( &$this, 'settings_licensing_status_render' ),
                'aawp_licensing',
                'aawp_licensing_section'
            );

            add_settings_field(
                'aawp_licensing_key',
                __( 'License key', 'aawp' ),
                array( &$this, 'settings_licensing_key_render' ),
                'aawp_licensing',
                'aawp_licensing_section',
                array('label_for' => 'aawp_licensing_key')
            );

            add_settings_field(
                'aawp_licensing_server_overwrite',
                __( 'License server', 'aawp' ),
                array( &$this, 'settings_licensing_server_render' ),
                'aawp_licensing',
                'aawp_licensing_section',
                array('label_for' => 'aawp_licensing_server_overwrite')
            );

            add_settings_field(
                'aawp_licensing_debug',
                __( 'Technical Information', 'aawp' ),
                array( &$this, 'settings_licensing_debug_render' ),
                'aawp_licensing',
                'aawp_licensing_section'
            );
        }

        /**
         * Settings callbacks
         *
         * @param $input
         * @return mixed
         */
        public function settings_licensing_callback( $input ) {

            // Defaults.
            $input['info'] = ( ! empty( $this->options['licensing']['info'] ) ) ? $this->options['licensing']['info'] : '';

            //aawp_debug_log( $input );

            // Reset server if overwrite changed.
            if ( ! empty( $input['server_overwrite'] ) && ! empty( $this->options['licensing']['server_overwrite'] ) && $input['server_overwrite'] != $this->options['licensing']['server_overwrite'] ) {
                $input['server'] = null;
            }

            /*
             * License server
             */
            // Validation needed.
            if ( empty ( $input['server'] ) && empty ( $input['server_overwrite'] ) && ! empty ( $input['key'] ) ) {

                //aawp_debug_log( __CLASS__ . ' >> ' . __FUNCTION__ . ' >> Action: aawp_validate_license_servers()' );
                $validated_server = aawp_validate_license_servers( $input['key'] );

                // Success.
                if ( ! empty ( $validated_server['url'] ) ) {
                    $input['server'] = $validated_server['url'];
                }
            }

            // Prepare server.
            if ( ! empty ( $input['server_overwrite'] ) ) {
                $server_url = $input['server_overwrite'];
            } elseif ( ! empty ( $input['server'] ) ) {
                $server_url = $input['server'];
            } else {
                $server_url = aawp_get_default_license_server_url();
            }

            $server = aawp_get_license_servers( $server_url );

            // Test connection.
            if ( isset( $input['test_connection'] ) ) {

                if ( ! empty ( $server_url ) ) {
                    $server_connection_test = aawp_test_license_server_connection( $server_url );
                } else {
                    $server_connection_test = aawp_test_license_server_connection( 'https://getaawp.com' );
                }

                if ( false === $server_connection_test ) {
                    $input['connection_error'] = 1;
                } elseif ( is_string( $server_connection_test ) ) {
                    $input['connection_error'] = $server_connection_test;
                } else {
                    $input['connection_error'] = false;
                }
            }

            /*
             * License actions
             */
            if ( ! empty ( $input['key'] ) ) {

                // Prepare actions.
                $do_license_check = ( isset( $input['verify_license'] ) ) ? true : false;
                $do_license_activation = ( isset( $input['verify_license'] ) && empty ( $input['info'] ) ) ? true : false;
                $do_license_deactivation = ( isset( $input['deactivate_license'] ) ) ? true : false;

                //aawp_debug_log( __CLASS__ . ' >> ' . __FUNCTION__ . ' >> $do_license_check: ' . $do_license_check );
                //aawp_debug_log( __CLASS__ . ' >> ' . __FUNCTION__ . ' >> $do_license_activation: ' . $do_license_activation );
                //aawp_debug_log( __CLASS__ . ' >> ' . __FUNCTION__ . ' >> $do_license_deactivation: ' . $do_license_deactivation );

                if ( $do_license_check || $do_license_activation || $do_license_deactivation ) {

                    if ( ! empty ( $server['url'] ) && ! empty ( $server['item_id'] ) && ! empty ( $server['item_name'] ) ) {

                        $AAWP_License_Handler = new AAWP_License_Handler(
                            $server['url'], array(
                                'item_id' => $server['item_id'],
                                'item_name' => $server['item_name']
                            )
                        );

                        if ( $do_license_activation ) {
                            $license_info = $AAWP_License_Handler->activate( $input['key'] );

                            // Do license check afterward.
                            if ( isset ( $license_info['data'] ) ) {
                                $do_license_check = true;
                            }

                        } elseif ( $do_license_deactivation ) {
                            $license_info = $AAWP_License_Handler->deactivate($input['key']);

                            // Delete license data.
                            if ( isset ( $license_info['data'] ) ) {
                                $input['key'] = '';
                                $input['server'] = '';
                                $input['info'] = '';
                            }
                        }

                        if ( $do_license_check ) {

                            // Fetch license info.
                            $license_info = $AAWP_License_Handler->check( $input['key'] );

                            //aawp_debug_log( __CLASS__ . ' >> ' . __FUNCTION__ . ' >> $license_info' );
                            //aawp_debug_log( $license_info );

                            // Store license info.
                            $input['info'] = ( isset ( $license_info['data'] ) ) ? $license_info : '';

                            // Set connection error to false, if we were able to receive a response from the license server.
                            $input['connection_error'] = false;
                        }
                    }
                }
            }

            /*
             * Reset actions.
             */
            if ( isset( $input['verify_license'] ) )
                unset( $input['verify_license'] );

            if ( isset( $input['deactivate_license'] ) )
                unset( $input['deactivate_license'] );

            if ( isset( $input['test_connection'] ) )
                unset( $input['test_connection'] );

            // Finally save input.
            return $input;
        }

        public function settings_licensing_section_callback() {

            ?>
            <p>
                <?php _e( 'Please enter your license credentials in order to receive updates.', 'aawp' ); ?>
            </p>
            <p>
                <strong><?php _e('Documentation:', 'aawp'); ?></strong>&nbsp;
                <a href="<?php echo aawp_get_page_url( 'docs:license_upgrades' ); ?>" target="_blank" rel="nofollow"><?php _e('"How to upgrade your license?"', 'aawp' ); ?></a>,&nbsp;
                <a href="<?php echo aawp_get_page_url( 'docs:license_renewals' ); ?>" target="_blank" rel="nofollow"><?php _e('"How to renew your license?"', 'aawp' ); ?></a>,&nbsp;
                <a href="<?php echo aawp_get_page_url( 'docs:license_server_issues' ); ?>" target="_blank" rel="nofollow"><?php _e('"License Server – Problems and Fixes"', 'aawp' ); ?></a>
            </p>
            <?php
        }

        public function settings_licensing_status_render() {

            //aawp_debug( $this->options['licensing'] );
            //var_dump( date('Y-m-d H:i:s', $this->options['licensing']['info']['checked_at'] ) );
            //$this->check_license();

            $license_info = ( isset ( $this->options['licensing']['info'] ) ) ? $this->options['licensing']['info'] : false;

            if ( ! empty ( $license_info ) ) {
                aawp_display_license_status( $license_info );
            } else {
                echo '<span style="color: red;">' . __( 'Please enter a valid license key.', 'aawp' ) . ' </span>';
            }
        }

        /**
         * License key render
         */
        public function settings_licensing_key_render() {

            $key = ( isset ( $this->options['licensing']['key'] ) ) ? $this->options['licensing']['key'] : '';

            $show_deactivate_license_button = ( ! empty( $this->options['licensing']['key'] ) ) ? true : false;

            if ( aawp_is_user_admin() ) {
                ?>
                <p>
                    <input type="<?php echo ( ! empty( $key ) ) ? 'password' : 'text'; ?>" id="aawp_licensing_key" class="regular-text" name="aawp_licensing[key]" value="<?php echo $key; ?>" />
                    <?php submit_button(__('Verify License', 'aawp'), 'primary', 'aawp_licensing[verify_license]', false); ?>

                    <?php if ( $show_deactivate_license_button ) { ?>
                        &nbsp;<?php submit_button(__('Deactivate License', 'aawp'), 'secondary', 'aawp_licensing[deactivate_license]', false); ?>
                    <?php } ?>
                </p>
                <?php if ( ! empty ( $key ) ) { ?>
                    <p style="margin-top: 1rem;">
                        <small><em><?php _e('If your license has been upgraded and/or the status is incorrect, click "Verify License" to force a refresh.', 'aawp' ); ?></em></small>
                    </p>
                <?php } ?>

            <?php } else { ?>
                <p>
                    <input type="<?php echo ( ! empty( $key ) ) ? 'password' : 'text'; ?>" id="aawp_licensing_key" class="regular-text" name="aawp_licensing[key]" value="<?php echo $key; ?>" readonly="readonly"/>
                </p>
            <?php }
        }

        /**
         * License server render
         */
        public function settings_licensing_server_render() {

            $server_list = aawp_get_license_servers();
            $servers = array(
                '' => __('Automatic', 'aawp')
            );

            foreach ( $server_list as $server ) {
                $servers[ $server['url'] ] = $server['name'];
            }

            $server = ( isset ( $this->options['licensing']['server'] ) ) ? $this->options['licensing']['server'] : '';
            $server_overwrite = ( isset ( $this->options['licensing']['server_overwrite'] ) ) ? $this->options['licensing']['server_overwrite'] : null;

            //aawp_debug( $server, '$server' );
            //aawp_debug( $server_overwrite, '$server_overwrite' );

            $test_connection_button_class = ( empty ( $this->options['licensing']['status'] ) ) ? 'primary' : 'secondary';
            ?>
            <input type="hidden" id="aawp_licensing_server" name="aawp_licensing[server]" value="<?php echo $server; ?>" />
            <p>
                <select id="aawp_licensing_server_overwrite" name="aawp_licensing[server_overwrite]" <?php if ( ! aawp_is_user_admin() ) echo 'readonly'; ?> style="vertical-align: baseline;" disabled>
                    <?php foreach ( $servers as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $server_overwrite, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
                <?php submit_button(__('Test Connection', 'aawp'), $test_connection_button_class, 'aawp_licensing[test_connection]', false); ?>
            </p>
            <p style="margin-top: 1rem;">
                <small><em><?php _e( 'Please only change if you have been asked to do so by our support team, or if you have problems activating your license.', 'aawp' ); ?></em></small>
            </p>
            <?php
        }

        /**
         * Outputs debug information
         */
        public function settings_licensing_debug_render() {

            //aawp_debug( $this->options['licensing'] );

            //$this->options['licensing']['connection_error'] = '1';
            //$connection_error = 'cURL error 28: Connection timed out after 10002 milliseconds';

            $icon_yes = '<span class="dashicons dashicons-yes-alt aawp-color-green"></span>';
            $icon_no = '<span class="dashicons dashicons-dismiss aawp-color-red"></span>';
            $icon_warning = '<span class="dashicons dashicons-warning aawp-color-orange"></span>';

            // cURL
            $curl_installed = ( function_exists('curl_version') ) ? true : false;
            $curl_info = ( function_exists('curl_version') ) ? curl_version() : null;
            $curl_version_installed = ( ! empty( $curl_info['version'] ) ) ? $curl_info['version'] : null;
            $curl_version_required = '7.64.0'; // https://curl.haxx.se/docs//releases.html
            $curl_version_is_outdated = ( version_compare( $curl_version_installed, $curl_version_required, '<' ) ) ? true : false;
            //aawp_debug( $curl_info );
            /*
            'version_number' => int 475393
            'age' => int 4
            'features' => int 2736797
            'ssl_version_number' => int 0
            'version' => string '7.65.1' (length=6)
            'host' => string 'x86_64-apple-darwin13.4.0' (length=25)
            'ssl_version' => string 'OpenSSL/1.0.2o' (length=14)
            'libz_version' => string '1.2.8' (length=5)
            */

            $openssl_installed = ( extension_loaded('openssl') ) ? true : false;
            $oppenssl_version_installed = ( ! empty( $curl_info['ssl_version'] ) ) ? str_replace( 'OpenSSL/', '', $curl_info['ssl_version'] ) : null;
            ?>
            <!-- Connection Result -->
            <input type="hidden" id="aawp_licensing_connection_error" name="aawp_licensing[connection_error]" value="<?php echo ( ! empty( $this->options['licensing']['connection_error'] ) ) ? esc_html( $this->options['licensing']['connection_error'] ) : ''; ?>" />

            <?php if ( isset( $this->options['licensing']['connection_error'] ) ) { ?>

                <?php if ( empty( $this->options['licensing']['connection_error'] ) ) { ?>

                    <p style="margin-bottom: 1.5rem;">
                        <span>
                            <span class="dashicons dashicons-yes-alt" style="color: green;"></span>&nbsp;
                            <strong><?php _e('Test Connection Result:', 'aawp' ); ?></strong>
                        </span>
                        <code><?php _e('The connection was successfully established.', 'aawp' ); ?></code>
                    </p>

                <?php } else { ?>

                    <p style="margin-bottom: 1.5rem;">
                        <span>
                            <span class="dashicons dashicons-warning" style="color: red;"></span>&nbsp;
                            <strong><?php _e('Test Connection Result:', 'aawp' ); ?></strong>
                        </span>
                        <code><?php echo ( is_string( $this->options['licensing']['connection_error'] ) ) ? $this->options['licensing']['connection_error'] : __( 'The connection could not be established.', 'aawp' ); ?></code>
                    </p>

                <?php } ?>

            <?php } ?>

            <table class="widefat aawp-debug-table">
                <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th><?php _e('Status', 'aawp' ); ?></th>
                        <th><?php _e('Information', 'aawp' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>PHP cURL</td>
                        <td>
                            <?php if ( $curl_installed ) { ?>
                                <?php echo ( ! $curl_version_is_outdated ) ? $icon_yes : $icon_warning; ?>&nbsp;<?php printf( esc_html__( 'Version %s installed', 'aawp' ), $curl_version_installed ); ?>
                            <?php } else { ?>
                                <?php echo $icon_no; ?>&nbsp;<?php _e('Not installed.', 'aawp' ); ?>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ( ! $curl_installed ) { ?>
                                <?php _e('Please install as soon as possible to avoid problems.', 'aawp' ); ?>
                            <?php } elseif ( $curl_version_is_outdated ) { ?>
                                <?php _e('The installed version is no longer up-to-date and should be updated. In case you were able to activate the license, everything is fine for now.', 'aawp' ); ?>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>
                    <tr class="alternate">
                        <td>PHP OpenSSL</td>
                        <td>
                            <?php if ( $openssl_installed ) { ?>

                                <?php echo $icon_yes; ?>
                                &nbsp;<?php printf( esc_html__( 'Version %s installed', 'aawp' ), $oppenssl_version_installed ); ?>

                            <?php } else { ?>
                                <?php echo $icon_no; ?>&nbsp;<?php _e('Not installed.', 'aawp' ); ?>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ( ! $openssl_installed ) { ?>
                                <?php _e('Please install as soon as possible to avoid problems.', 'aawp' ); ?>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
        }

        /**
         * Get latest plugin version remotely
         *
         * @return mixed
         */
        private function get_remote_plugin_version() {

            $remote_plugin_version = get_transient( 'aawp_remote_plugin_version' );

            //aawp_debug_log( __CLASS__ . ' >> ' . __FUNCTION__ . ' >> $remote_plugin_version: ' . $remote_plugin_version );

            // Stored plugin version found
            if ( ! empty( $remote_plugin_version ) )
                return $remote_plugin_version;

            // Fetch the latest version from remote API
            $api_params = array(
                'edd_action' => 'get_version',
                'item_id' => 1367
            );

            // Send GET request to API.
            $response = wp_remote_get( 'https://getaawp.com/edd-sl-api/', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

            // make sure the response came back okay
            if ( is_wp_error( $response ) )
                return false;

            // decode the license data
            $remote_plugin_data = json_decode( wp_remote_retrieve_body( $response ) );
            //aawp_debug_log( __CLASS__ . ' >> ' . __FUNCTION__ . ' >> $remote_plugin_data' );
            //aawp_debug_log( $remote_plugin_data );

            if ( ! empty( $remote_plugin_data ) && ! empty ( $remote_plugin_data->new_version ) && is_string( $remote_plugin_data->new_version ) ) {

                $remote_plugin_version = sanitize_text_field( $remote_plugin_data->new_version );

                //aawp_debug_log( __CLASS__ . ' >> ' . __FUNCTION__ . ' >> $remote_plugin_version' );
                //aawp_debug_log( $remote_plugin_version );

                // Cache latest remote plugin version.
                set_transient( 'aawp_remote_plugin_version', $remote_plugin_version, 60 * 60 * 24 * 7 ); // 7 days

                // Return.
                return $remote_plugin_version;
            }

            return null;
        }

        /**
         * Check whether a plugin update is required or not
         *
         * @return bool
         */
        private function is_plugin_update_required() {

            $current_version = AAWP_VERSION;
            $remote_plugin_version = $this->get_remote_plugin_version();

            // Return true if both versions available and remote one is higher than the installed one
            if ( ! empty( $current_version ) && ! empty( $remote_plugin_version ) && version_compare( $current_version, $remote_plugin_version, '<' ) )
                return true;

            return false;
        }
    }

    new AAWP_Settings_License();
}