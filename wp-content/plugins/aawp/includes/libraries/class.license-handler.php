<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Allows plugins to use their own update API.
 *
 * @author fdmedia
 * @version 1.0.0
 */
class AAWP_License_Handler {

    private $api_url = '';
    private $api_data = '';
    private $item_id = '';
    private $item_name = '';

    /**
     * Class constructor.
     *
     * @param string $api_url The URL pointing to the custom API endpoint.
     * @param array $api_data Optional data to send with API calls.
     */
    public function __construct( $api_url, $api_data = null ) {

        // Setup API.
        $this->api_url = trailingslashit( $api_url ) . 'edd-sl-api/';
        $this->api_data = $api_data;

        // Setup item.
        $this->item_id = ( ! empty ( $api_data['item_id'] ) && is_numeric( $api_data['item_id'] ) ) ? $api_data['item_id'] : '';
        $this->item_name = ( ! empty ( $api_data['item_name'] ) ) ? $api_data['item_name'] : '';
    }

    /**
     * Handle license key changes
     *
     * @param $license_new
     * @param string $license_old
     * @return string
     */
    public function changed( $license_new, $license_old ) {

        //$this->debug_log( 'AAWP_License_Handler >> changed() >> $license_new: ' . $license_new . ' - $license_old: ' . $license_old );

        $license_info = '';

        // Prepare actions.
        $activate = false;
        $deactivate = false;

        // Case 1: Key entered for the first time.
        if ( empty ( $license_old ) && ! empty ( $license_new ) ) {
            $activate = true;
        }

        // Case 2: Key removed.
        if ( empty ( $license_new ) && ! empty ( $license_old ) ) {
            $deactivate = true;
        }

        // Case 3: Key changed.
        if ( ! empty ( $license_old ) && ! empty ( $license_new ) && $license_new !== $license_old ) {
            $deactivate = true;
            $activate = true;
        }

        // Maybe deactivate old license.
        if ( $deactivate && ! empty ( $license_old ) )  {
            $license_info = $this->deactivate( $license_old );
        }

        // Maybe activate new license.
        if ( $activate && ! empty ( $license_new ) )  {
            $license_info = $this->activate( $license_new );
        }

        // Return license info.
        return $license_info;
    }

    /**
     * Check license
     *
     * @param string $license
     * @return bool|string
     */
    public function check( $license ) {

        if ( empty ( $license ) )
            return false;

        return $this->make_request( $license, 'check' );
    }

    /**
     * Activate license
     *
     * @param $license
     * @return array|bool
     */
    public function activate( $license ) {

        if ( empty ( $license ) )
            return false;

        return $this->make_request( $license, 'activate' );
    }

    /**
     * Deactivate license
     *
     * @param $license
     * @return array|bool
     */
    public function deactivate( $license ) {

        if ( empty ( $license ) )
            return false;

        return $this->make_request( $license, 'deactivate' );
    }

    /**
     * Make API request
     *
     * @param $license
     * @param string $action
     * @return array|bool
     */
    private function make_request( $license, $action = 'check' ) {

        switch( $action ) {
            case 'activate' :
                $edd_action = 'activate_license';
                break;
            case 'deactivate' :
                $edd_action = 'deactivate_license';
                break;
            default:
                $edd_action = 'check_license';
                break;
        }

        // Default API parameters.
        $api_params = array(
            'edd_action' => $edd_action,
            'license' => trim( $license ),
            'item_id' => ( ! empty ( $this->item_id ) ) ? absint( $this->item_id ) : '',
            'item_name' => ( ! empty ( $this->item_name ) ) ? urlencode( $this->item_name ) : '',
            'url' => home_url()
        );

        //$this->debug_log( 'AAWP_License_Handler >> make_request() >> $api_params:' );
        //$this->debug_log( $api_params );

        // Send POST request to API.
        $response = wp_remote_post( $this->api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
        //aawp_debug_log( __CLASS__ . ' >> ' . __FUNCTION__ . ' >> api_url: ' . $this->api_url );

        if ( is_wp_error( $response ) )
            return $response->get_error_message();

        $license_data = json_decode( wp_remote_retrieve_body( $response ) );

        //$this->debug_log( 'AAWP_License_Handler >> make_request() >> $license_data:' );
        //$this->debug_log( $license_data );

        return $this->prepare_license_info( $license_data );
    }

    /**
     * Prepare license info (based on license data)
     *
     * @param $license_data
     * @return array
     */
    private function prepare_license_info( $license_data ) {

        $result = array(
            'status' => ( ! empty ( $license_data->license ) ) ? $license_data->license : '', // The status like "active", "inactive", "expired" etc.
            'error' => '', // Only filled if an error occurred.
            'data' => $license_data, // The original license data coming from the API.
            'checked_at' => time()
        );

        if ( ! empty ( $license_data->error ) ) {

            switch( $license_data->error ) {

                case 'expired' :
                    $message = sprintf(
                        __( 'Your license key expired on %s.', 'aawp' ),
                        date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                    );
                    break;

                case 'disabled' :
                case 'revoked' :
                    $message = __( 'Your license key has been disabled.', 'aawp' );
                    break;

                case 'missing' :
                    $message = __( 'Invalid license.', 'aawp' );
                    break;

                case 'invalid' :
                case 'site_inactive' :
                    $message = __( 'Your license is not active for this URL.', 'aawp' );
                    break;

                case 'item_name_mismatch' :
                    $message = sprintf( __( 'This appears to be an invalid license key for %s.', 'aawp' ), $this->item_name );
                    break;

                case 'no_activations_left':
                    $message = __( 'Your license key has reached its activation limit.', 'aawp' );
                    break;

                default :
                    $message = __( 'An error occurred, please try again.', 'aawp' );
                    break;
            }

            // Set error details.
            $license_response['error'] = array(
                'code' => $license_data->error,
                'message' => $message
            );
        }

        return $result;
    }

    /**
     * Get license status display text
     *
     * @param $status
     * @return string|void
     */
    private function get_license_status_display_text( $status ) {

        switch( $status ) {

            case 'valid' :
                $text = __( 'Site activated', 'aawp' );
                break;

            case 'expired' :
                $text = __( 'License expired', 'aawp' );
                break;

            case 'invalid' :
                $text = __( 'Invalid license', 'aawp' );
                break;

            default :
                $text = $status;
                break;
        }

        return $text;
    }

    /**
     * Display license info
     *
     * Array (
        [status] => valid
        [error] =>
        [data] => stdClass Object (
            [success] => 1
            [license] => valid
            [item_id] => 123
            [item_name] => Best WordPress Plugin
            [is_local] => 1
            [license_limit] => 0
            [site_count] => 0
            [expires] => 2021-06-03 23:59:59
            [activations_left] => unlimited
            [checksum] => abc123
            [payment_id] => 123
            [customer_name] => John Doe
            [customer_email] => john@doe.com
            [price_id] => 1
        )
    )
     *
     * @param $license_info
     * @param bool $echo
     * @return false|string
     */
    public function display_license_info( $license_info, $echo = true ) {

        $license_status = ( ! empty ( $license_info['status'] ) ) ? $license_info['status'] : 'inactive';
        $license_error = ( ! empty ( $license_info['error'] ) ) ? $license_info['error'] : null;
        $license_limit = ( ! empty ( $license_info['data']->license_limit ) ) ? absint ( $license_info['data']->license_limit ) : 0;

        //$license_error = array( 'code' => 'no_activations_left', 'message' => 'Your license key has reached its activation limit.' );

        // Icon.
        if ( 'valid' === $license_status ) {
            $license_icon = 'yes-alt';
            $license_status_color = 'green';
        } elseif ( 'expired' === $license_status ) {
            $license_icon = 'warning';
            $license_status_color = 'orange';
        } else {
            $license_icon = 'dismiss';
            $license_status_color = 'red';
        }

        /*
        $license_limit = 1;

        if ( ! is_multisite() && ( ! empty ( $license_limit ) && $license_limit < 3 ) ) {
            $license_icon = 'warning';
            $license_status_color = 'orange';
        }
        */

        // Start output.
        ob_start();
        ?>
        <div class="fd-license-info">
            <span class="fd-license-status" style="color: <?php echo esc_attr( $license_status_color ); ?>;"><?php echo $this->get_license_status_display_text( $license_status ); ?></span>
            <?php if ( ! empty ( $license_error['message'] ) ) { ?>
                <span class="fd-license-error"><code><?php echo $license_error['message']; ?></code></span>
            <?php } ?>
        </div>
        <?php
        $output = ob_get_clean();

        // Finally echo or return output.
        if ( $echo ) {
            echo $output;
        } else {
            return $output;
        }
    }

    /**
     * Debug logging
     *
     * @param $message
     */
    private function debug_log( $message ) {

        if ( WP_DEBUG === true ) {
            if (is_array( $message ) || is_object( $message ) ) {
                error_log( print_r( $message, true ) );
            } else {
                error_log( $message );
            }
        }
    }
}