<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'AAWP_Settings_API' ) ) {

    class AAWP_Settings_API extends AAWP_Functions {

        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // Call parent constructor first
            parent::__construct();

            if ( ! aawp_is_license_valid() )
                return;

            // Setup identifier
            $this->func_order = 10;
            $this->func_id = 'api';
            $this->func_name = __('Amazon API', 'aawp');

            // Settings functions
            add_filter( $this->settings_tabs_filter, array( &$this, 'add_settings_tabs_filter' ) );
            add_filter( $this->settings_functions_filter, array( &$this, 'add_settings_functions_filter' ) );

            add_action( 'admin_init', array( &$this, 'add_settings' ) );
        }

        /**
         * Add settings functions
         */
        public function add_settings_tabs_filter( $tabs ) {

            $tabs[$this->func_order] = array(
                'key' => $this->func_id,
                'icon' => 'admin-plugins',
                'title' => $this->func_name
            );

            if ( isset ( $this->options['api']['status'] ) && $this->options['api']['status'] != '1' )
                $tabs[$this->func_order]['alert'] = 'warning';

            return $tabs;
        }

        public function add_settings_functions_filter( $functions ) {

            $functions[] = $this->func_id;

            return $functions;
        }

        /**
         * Settings: Register section and fields
         */
        public function add_settings() {

            register_setting(
                'aawp_api',
                'aawp_api',
                array( &$this, 'settings_api_callback')
            );

            add_settings_section(
                'aawp_api_section',
                _x('Amazon Product Advertising API', 'Settings Section Headline', 'aawp'),
                array( &$this, 'settings_api_section_callback' ),
                'aawp_api'
            );

            add_settings_field(
                'aawp_api_status',
                __( 'Status', 'aawp' ),
                array( &$this, 'settings_api_status_render' ),
                'aawp_api',
                'aawp_api_section'
            );

            add_settings_field(
                'aawp_api_key',
                __( 'API Key', 'aawp' ),
                array( &$this, 'settings_api_key_render' ),
                'aawp_api',
                'aawp_api_section',
                array('label_for' => 'aawp_api_key')
            );

            add_settings_field(
                'aawp_api_secret',
                __( 'API Secret', 'aawp' ),
                array( &$this, 'settings_api_secret_render' ),
                'aawp_api',
                'aawp_api_section',
                array('label_for' => 'aawp_api_secret')
            );

            add_settings_field(
                'aawp_api_country',
                __( 'Country', 'aawp' ),
                array( &$this, 'settings_api_country_render' ),
                'aawp_api',
                'aawp_api_section',
                array('label_for' => 'aawp_api_country')
            );

            add_settings_field(
                'aawp_api_associate_tag',
                __( 'Tracking ID', 'aawp' ),
                array( &$this, 'settings_api_associate_tag_render' ),
                'aawp_api',
                'aawp_api_section',
                array('label_for' => 'aawp_api_associate_tag')
            );

            /*
             * Action to add more settings within this section
             */
            do_action( 'aawp_settings_api_register' );

            /*
             * Debug info
             */
            add_settings_field(
                'aawp_api_debug',
                __( 'Technical Information', 'aawp' ),
                array( &$this, 'settings_api_debug_render' ),
                'aawp_api',
                'aawp_api_section'
            );
        }

        /**
         * Settings callbacks
         */
        public function settings_api_callback( $input ) {

            //$this->debug($input);
            $empty_database_tables = false;
            $disconnect = false;

            // API Key validation
            $verify_credentials = false;

            if ( isset($input['disconnect'] ) ) {
                $input['status'] = 0;
                $disconnect = true;
            }

            if ( isset($input['connect'] ) || isset($input['reconnect'] ) ) {
                $verify_credentials = true;
            }

            // Cleanup inputs
            $input['key'] = trim( $input['key'] );
            $input['secret'] = trim( $input['secret'] );
            $input['associate_tag'] = trim( $input['associate_tag'] );

            // Validate inputs
            if ( isset ( $input['key'] ) && isset ( $input['secret'] ) && isset( $input['associate_tag'] ) ) {

                // Status might be set to false by api check routine
                if ( isset( $input['status'] ) && $input['status'] == '0' ) {
                    $verify_credentials = true;
                }

                // Key changed
                if ( isset( $this->options['api']['key'] ) && $this->options['api']['key'] != $input['key'] ) {
                    $verify_credentials = true;
                }

                // Secret changed
                if ( isset ( $this->options['api']['secret'] ) && $input['secret'] == sha1( $this->options['api']['secret'] ) ) {
                    // Secret key NOT changed = save existing value again
                    $input['secret'] = $this->options['api']['secret'];
                } else {
                    // Secret key changed
                    $verify_credentials = true;
                }

                // Country changed
                if ( isset( $this->options['api']['country'] ) && $this->options['api']['country'] != $input['country'] ) {
                    $verify_credentials = true;
                    $empty_database_tables = true;
                }

                // Associate tag changed
                if ( isset( $this->options['api']['associate_tag'] ) && $this->options['api']['associate_tag'] != $input['associate_tag'] ) {
                    $verify_credentials = true;
                }

                if ( $input['key'] == '' && $input['secret'] == '' ) {
                    $verify_credentials = false;
                    $input['status'] = 0;
                    $input['error'] = '';
                } else {
                    if ( empty( $input['key'] ) || empty( $input['secret'] ) || empty( $input['associate_tag'] ) ) {
                        $verify_credentials = false;
                        $input['status'] = 0;

                        if ( empty( $input['key'] ) )
                            $input['error'] = __('API key is missing.', 'aawp');

                        if ( empty( $input['secret'] ) )
                            $input['error'] = __('API secret is missing.', 'aawp');

                        if ( empty( $input['associate_tag'] ) )
                            $input['error'] = __('Tracking id is missing.', 'aawp');
                    }
                }
            }

            if ( empty( $input['key'] ) || empty ( $input['secret'] ) || empty( $input['country'] ) || empty( $input['associate_tag'] ) ) {
                $input['status'] = 0;
            }

            if ( $empty_database_tables ) {
                aawp_empty_database_tables();
            }

            if ( !$disconnect && $verify_credentials && class_exists( 'AAWP_API' ) ) {

                // New API verification
                $input['status'] = 0;

                if ( !aawp_check_dependencies() ) {
                    $input['error'] = sprintf( wp_kses( __( 'Your server is missing some dependencies. Please take a look at the <a href="%s">support page</a> and upgrade your server.', 'aawp' ), array(  'a' => array( 'href' => array() ) ) ), admin_url( 'admin.php?page=aawp-support' ) );
                    return $input;
                }

                $country = ( !empty( $input['country'] ) ) ? $input['country'] : '';
                $aws_api_key = ( !empty( $input['key'] ) ) ? $input['key'] : '';
                $aws_api_secret_key = ( !empty( $input['secret'] ) ) ? $input['secret'] : '';
                $aws_associate_tag = ( !empty( $input['associate_tag'] ) ) ? $input['associate_tag'] : '';

                // Setup AAWP
                aawp()->api->set_credentials($country, $aws_api_key, $aws_api_secret_key, $aws_associate_tag, true );

                if ( ! empty( $input['key'] ) && ! empty( $input['secret'] ) && ! empty( $input['associate_tag'] ) && ! empty( $input['country'] ) ) {

                    // Verify credentials by making an API call
                    $credentials_verified = aawp()->api->verify_credentials( $input['key'], $input['secret'], $input['associate_tag'], $input['country'] );

                    // Connection established
                    if ( true === $credentials_verified ) {
                        $input['status'] = 1;
                        $input['error'] = '';

                    // Error code returned
                    } elseif ( ! empty( $credentials_verified['code'] ) ) {
                        $input['error'] = $credentials_verified['code'];
                    }
                }
            }

            return $input;
        }

        public function settings_api_section_callback() {

            ?>
            <p>
                <?php _e( 'In order to use this plugin you have to register as an Amazon Associate, signup for using the Amazon Product Advertising API and receiving your personal API credentials. Please take a look into the documentation in case you need any assistance.', 'aawp' ); ?>
            </p>
            <p>
                <strong><?php _e('Related links:', 'aawp'); ?></strong>&nbsp;
                <?php if ( ! empty ( $this->options['api']['country'] ) ) {
                    $amazon_associates_link = aawp_get_amazon_associates_link();
                    $amazon_product_advertising_api_link = aawp_get_amazon_product_advertising_api_link();
                    ?>
                    <a href="<?php echo $amazon_associates_link; ?>" title="<?php _e('Amazon Associates', 'aawp'); ?>" target="_blank"><?php _e('Amazon Associates', 'aawp'); ?></a>
                    &nbsp;|&nbsp;
                    <a href="<?php echo $amazon_product_advertising_api_link; ?>" title="<?php _e('Amazon Product Advertising API', 'aawp'); ?>" target="_blank"><?php _e('Amazon Product Advertising API', 'aawp'); ?></a>
                    &nbsp;|&nbsp;
                <?php } ?>
                <a href="<?php echo aawp_get_page_url( 'api_key_checker' ); ?>" target="_blank" rel="nofollow"><?php _e('API Key Validator', 'aawp' ); ?></a>
            </p>
            <p>
                <strong><?php _e('Documentation:', 'aawp'); ?></strong>&nbsp;
                <a href="<?php echo aawp_get_page_url( 'docs:api_keys' ); ?>" target="_blank" rel="nofollow"><?php _e('Creating API Keys', 'aawp' ); ?></a>,&nbsp;
                <a href="<?php echo aawp_get_page_url( 'docs:api_issues' ); ?>" target="_blank" rel="nofollow"><?php _e('Amazon API “not connected” – Frequent causes', 'aawp' ); ?></a>,&nbsp;
                <a href="<?php echo aawp_get_page_url( 'docs:api_requestthrottled' ); ?>" target="_blank" rel="nofollow"><?php _e('“RequestThrottled” and how to fix it?', 'aawp' ); ?></a>,&nbsp;
                <a href="<?php echo aawp_get_page_url( 'docs:amazon_apiv5' ); ?>" target="_blank" rel="nofollow"><?php _e('Amazon API v5 Migration', 'aawp' ); ?></a>
            </p>

            <?php
        }

        public function settings_api_status_render() {

            $error = ( !empty ( $this->options['api']['error'] ) ) ? $this->options['api']['error'] : '';
            $status = ( isset ( $this->options['api']['status'] ) && $this->options['api']['status'] ) ? 1 : 0;

            echo '<ul class="aawp-grid-list">';

            echo '<li>';

            if ( $status ) {
                echo '<span style="color: green;"><span class="dashicons dashicons-yes aawp-dashicons-inline"></span> ' . __('Connected', 'aawp') . '</span>';
            } else {
                echo '<span style="color: red;"><span class="dashicons dashicons-no aawp-dashicons-inline"></span> ' . __('Disconnected', 'aawp') . '</span>';
            }

            echo '</li>';

            if ( aawp_is_user_admin() ) {
                echo '<li>';

                if ( $status ) {
                    submit_button( __( 'Reconnect', 'aawp' ), 'button-secondary button-small', 'aawp_api[reconnect]', false );
                    submit_button( __( 'Disconnect', 'aawp' ), 'button-primary button-small', 'aawp_api[disconnect]', false );
                } else {
                    submit_button( __( 'Connect', 'aawp' ), 'button-primary button-small', 'aawp_api[connect]', false );
                }

                echo '</li>';
            }

            echo '</ul>';

            // DEV only
            //$error = 'undefined';

            if ( ! empty ( $error ) ) {

                $error_message = aawp_get_api_error_message( $error );

                echo '<div style="margin-top: 15px;">';
                echo '<span class="dashicons dashicons-warning" style="color: red;"></span>&nbsp;';
                echo '<code style="line-height: 25px;">';
                echo '<strong>' . $error . '</strong>: ';
                echo $error_message;
                echo '</code>';

                if ( ( 'undefined' === $error || strpos( $error_message, 'Undefined' ) !== false || strpos( $error_message, 'Unknown' ) !== false ) && version_compare( phpversion(), '7.0', '<' ) ) {
                    echo '<p>';
                    echo '<span class="dashicons dashicons-sos" style="color: orange;"></span>&nbsp;';
                    printf( esc_html__( 'Please make sure that your server is running PHP version %s or higher. In some cases, an older PHP version was the cause of various API problems.', 'aawp' ), '7.0' );
                    echo '</p>';
                }

                echo '<p>';
                echo '<span class="dashicons dashicons-lightbulb" style="color: green;"></span>&nbsp;';
                echo sprintf( wp_kses( __( 'Please take a look at our article "<a href="%s" target="_blank">Amazon API “not connected” – Frequent causes</a>" in order to solve this problem.', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => '_blank' ) ) ), esc_url( aawp_get_page_url( 'docs:api_issues' ) ) );
                echo '</p>';

                echo '</div>';
            }

            ?>
            <input type="hidden" name="aawp_api[status]" value="<?php echo $status; ?>" />
            <input type="hidden" name="aawp_api[error]" value="<?php echo $error; ?>" />
            <?php
        }

        public function settings_api_key_render() {

            $key = ( isset ( $this->options['api']['key'] ) ) ? $this->options['api']['key'] : '';

            ?>
            <input type="<?php echo ( aawp_is_user_admin() ) ? 'text' : 'password'; ?>" id="aawp_api_key" class="regular-text" name="aawp_api[key]" value="<?php echo $key; ?>" <?php if ( ! aawp_is_user_admin() ) echo 'readonly'; ?>/>
            <?php
        }

        public function settings_api_secret_render() {

            $secret = ( !empty ( $this->options['api']['secret'] ) ) ? sha1($this->options['api']['secret']) : '';

            ?>
            <input type="password" id="aawp_api_secret" class="regular-text" name="aawp_api[secret]" value="<?php echo $secret; ?>" <?php if ( ! aawp_is_user_admin() ) echo 'readonly'; ?>/>
            <?php
        }

        public function settings_api_country_render() {

            $country_tags = aawp_get_amazon_stores();

            $country = ( isset ( $this->options['api']['country'] ) ) ? $this->options['api']['country'] : 'de';

            ksort($country_tags);
            ?>
            <select id="aawp_api_country" name="aawp_api[country]" data-aawp-api-country-selector="true" <?php if ( ! aawp_is_user_admin() ) echo 'readonly'; ?>>
                <?php foreach ( $country_tags as $tag => $label ) { ?>
                    <option value="<?php echo $tag; ?>" <?php selected( $country, $tag ); ?>>amazon.<?php echo $tag; ?></option>
                <?php } ?>
            </select>
            <span id="aawp_api_country_icon" data-aawp-api-country-preview="true"><?php aawp_the_icon_flag( $country ); ?></span>
            <?php
        }

        public function settings_api_associate_tag_render() {

            $associate_tag = ( isset ( $this->options['api']['associate_tag'] ) ) ? $this->options['api']['associate_tag'] : '';

            ?>
            <input type="text" id="aawp_api_associate_tag" class="regular-text" name="aawp_api[associate_tag]" value="<?php echo $associate_tag; ?>" <?php if ( ! aawp_is_user_admin() ) echo 'readonly'; ?>/>
            <p>
                <small><?php _e('Without the tracking id, conversions cannot be assigned to your affiliate account.', 'aawp'); ?><br /><?php _e('Your tracking id should look similar to this: <strong>aawp-21</strong> (might differ depending on the country).', 'aawp'); ?></small>
            </p>
            <?php
        }

        /**
         * Outputs debug information
         */
        public function settings_api_debug_render() {

            //aawp_debug( $this->options['api'] );

            $icon_yes = '<span class="dashicons dashicons-yes-alt aawp-color-green"></span>';
            $icon_no = '<span class="dashicons dashicons-dismiss aawp-color-red"></span>';
            $icon_warning = '<span class="dashicons dashicons-warning aawp-color-orange"></span>';

            // PHP
            $php_version_installed = phpversion();
            $php_version_required = '5.6'; // https://www.php.net/releases/index.php
            $php_version_is_outdated = ( version_compare( $php_version_installed, $php_version_required, '<' ) ) ? true : false;

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
                    <td>PHP</td>
                    <td>
                        <?php echo ( ! $php_version_is_outdated ) ? $icon_yes : $icon_no; ?>&nbsp;<?php printf( esc_html__( 'Version %s installed', 'aawp' ), $php_version_installed ); ?>
                    </td>
                    <td>
                        <?php if ( $php_version_is_outdated ) { ?>
                            <?php printf( esc_html__( 'The installed version is no longer up-to-date and should be updated to version %s or higher.', 'aawp' ), $php_version_required ); ?>
                        <?php } else { ?>
                            -
                        <?php } ?>
                    </td>
                </tr>
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
                            <?php _e('The installed version is no longer up-to-date and should be updated. In case you were able to connect to the API, everything is fine for now.', 'aawp' ); ?>
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
    }

    new AAWP_Settings_API();
}