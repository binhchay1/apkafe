<?php
/**
 * License Functions
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

global $aawp_license_is_valid;

/**
 * Check whether a valid license was entered or not
 */
function aawp_is_license_valid() {

    global $aawp_license_is_valid;

    //aawp_debug_log( __FUNCTION__ . ' >> $aawp_license_is_valid is: ' . $aawp_license_is_valid );

    if ( is_bool( $aawp_license_is_valid ) )
        return $aawp_license_is_valid;

    // Default.
    $aawp_license_is_valid = false;

    // Get license key.
    $license_key = aawp_get_option( 'key', 'licensing' );
    //aawp_debug_log( __FUNCTION__ . ' >> $license_key: ' . $license_key );

    if ( ! empty ( $license_key ) ) {

        $license_server = aawp_get_option( 'server', 'licensing' );
        $license_info = aawp_get_option( 'info', 'licensing' );

        //aawp_debug_log( __FUNCTION__ . ' >> $license_info: ' . $license_info );
        //aawp_debug_log( __FUNCTION__ . ' >> $license_server: ' . $license_server );

        //aawp_debug_log( __FUNCTION__ . ' >> $license_status: ' . $license_status );
        //aawp_debug_log( __FUNCTION__ . ' >> $license_error: ' . $license_error );
        //aawp_debug_log( __FUNCTION__ . ' >> $license_expires: ' . $license_expires );

        // Connection established.
        if ( ! empty ( $license_info['status'] ) && in_array( $license_info['status'], array( 'active', 'expired' ) ) ) {
            $aawp_license_is_valid = true;
        // Connection failed
        } elseif ( strlen( $license_key ) > 16 ) {
            $aawp_license_is_valid = true;
        }
    }

    //aawp_debug_log( __FUNCTION__ . ' >> $aawp_license_is_valid set to: ' . $aawp_license_is_valid );

    return $aawp_license_is_valid;
}

/**
 * Display license status
 *
 * @param $info
 */
function aawp_display_license_status( $info ) {
    $AAWP_License_Handler = new AAWP_License_Handler( 'https://getaawp.com' );
    $AAWP_License_Handler->display_license_info( $info, true );
}

/**
 * Test license server connection
 *
 * @param $server_url
 * @return bool
 */
function aawp_test_license_server_connection( $server_url ) {

    $AAWP_License_Handler = new AAWP_License_Handler(
        $server_url, array(
            'item_name' => 'Amazon Affiliate for WordPress'
        )
    );

    $license_check = $AAWP_License_Handler->check( 'test' );

    if ( is_string( $license_check ) ) {
        return $license_check;
    } elseif ( ! empty ( $license_check['data']->checksum ) ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Refresh license info
 */
function aawp_refresh_license_info() {

    $license_settings = aawp_get_options( 'licensing' );

    // Bail if no license key available.
    if ( empty ( $license_settings['key'] ) )
        return;

    $license_checked = get_option( 'aawp_license_checked' );

    //aawp_debug_log( __FUNCTION__ . ' >> $license_checked: ' . $license_checked );

    // Bail if last check is still valid.
    if( ! empty ( $license_checked ) && is_numeric( $license_checked ) && ( $license_checked > strtotime('-7 days') ) ) {
        return;
    }

    //aawp_debug_log( __FUNCTION__ . ' >> check needed.' );

    // Prepare server.
    $server = aawp_get_license_server();

    if ( empty ( $server['url'] ) )
        return;

    // Prepare license handler.
    $AAWP_License_Handler = new AAWP_License_Handler(
        $server['url'], array(
            'item_id' => $server['item_id'],
            'item_name' => $server['item_name']
        )
    );

    $license_info = $AAWP_License_Handler->check( $license_settings['key'] );

    //aawp_debug_log( __FUNCTION__ . ' >> $license_info' );
    //aawp_debug_log( $license_info );

    // Success.
    if ( isset ( $license_info['data'] ) ) {

        $license_settings['info'] = $license_info;

        // Update stored license info.
        aawp_update_options( 'licensing', $license_settings );
    }

    // Set last check timestamp.
    update_option( 'aawp_license_checked', time() );
}
add_action( 'admin_init', 'aawp_refresh_license_info' );

/**
 * Get validated license server
 *
 * @param $license
 * @return false|string
 */
function aawp_validate_license_servers( $license ) {

    $servers = aawp_get_license_servers();

    foreach ( $servers as $server_key => $server ) {

        $AAWP_License_Handler = new AAWP_License_Handler(
            $server['url'], array(
                'item_id' => $server['item_id'],
                'item_name' => $server['item_name']
            )
        );

        $license_check = $AAWP_License_Handler->check( $license );

        if ( isset ( $license_check['data'] ) && isset ( $license_check['data']->success ) && '1' == $license_check['data']->success )
            return $server;
    }

    return false;
}

/**
 * Get license server
 *
 * @return array|null
 */
function aawp_get_license_server() {

    $license_servers = aawp_get_license_servers();

    $server = aawp_get_option( 'server', 'licensing' );
    $server_overwrite = aawp_get_option( 'server_overwrite', 'licensing' );

    if ( ! empty ( $server_overwrite ) && isset ( $license_servers[ $server_overwrite ] ) ) {
        return $license_servers[ $server_overwrite ];
    } elseif ( ! empty ( $server ) && isset ( $license_servers[ $server ] ) ) {
        return $license_servers[ $server ];
    } else {
        return null;
    }
}

/**
 * Get license servers
 *
 * @param string $key
 * @return array|array[]
 */
function aawp_get_license_servers( $key = '' ) {

    // Fallback for old domain.
    if ( ! empty ( $key ) && strpos( $key, 'aawp.de/en' ) !== false ) {
        $key = 'https://getaawp.com';
    }

    // Setup available servers.
    $servers = array(
        'https://getaawp.com' => array(
            'url' => 'https://getaawp.com',
            'name' => 'getaawp.com',
            'item_id' => 1367,
            'item_name' => 'Amazon Affiliate for WordPress'
        ),
        'https://aawp.de' => array(
            'url' => 'https://aawp.de',
            'name' => 'aawp.de',
            'item_id' => 738,
            'item_name' => 'Amazon Affiliate for WordPress'
        ),
    );

    return ( ! empty ( $key ) && isset ( $servers[ $key ] ) ) ? $servers[ $key ] : $servers;
}

/**
 * Get default license server URL
 *
 * @return string
 */
function aawp_get_default_license_server_url() {
    return 'https://getaawp.com';
}

/**
 * Maybe show admin license notices
 *
 * @param $notices
 * @return mixed
 */
function aawp_admin_license_notices( $notices ) {

    $license_settings = aawp_get_options( 'licensing' );

    /*
     * License missing/invalid.
     */
    if ( ! aawp_is_license_valid() ) {

        $message = sprintf( wp_kses( __( 'Please <a href="%s">enter a valid license key</a> in order to use the plugin and receive updates.', 'aawp' ), array(  'a' => array( 'href' => array() ) ) ), add_query_arg( 'tab', 'licensing', AAWP_ADMIN_SETTINGS_URL ) );

        $notices[] = array(
            'force' => true,
            'type' => 'error',
            'dismiss' => false,
            'message' => $message
        );
    }

    /*
     * License expired.
     */
    if ( ! empty ( $license_settings['key'] ) && isset ( $license_settings['info']['status'] ) && 'expired' === $license_settings['info']['status'] ) {

        $license_server = aawp_get_license_server();

        if ( ! empty ( $license_server['url'] ) && ! empty ( $license_server['item_id'] ) ) {

            $renewal_link = add_query_arg( array(
                'edd_license_key' => trim( $license_settings['key'] ),
                'download_id' => absint( $license_server['item_id'] ),
            ), trailingslashit( $license_server['url'] ) . 'checkout/' );

            $message = sprintf( wp_kses( __( 'Your license expired. Please <a href="%s" target="_blank">renew your license</a> in order to receive future updates.', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_url( $renewal_link ) );

            $notices[] = array(
                'force' => false,
                'type' => 'warning',
                'dismiss' => false,
                'message' => $message
            );
        }
    }

    // Return.
    return $notices;
}
add_filter( 'aawp_admin_notices', 'aawp_admin_license_notices' );

/**
 * Maybe show admin license notice
 *
 * @param array $links
 * @param $file
 * @return array
 */
function aawp_admin_plugin_row_meta_license_notice( array $links, $file ) {

    if ( strpos( $file, 'aawp' ) === false)
        return $links;

    if ( ! aawp_is_license_valid() ) {
        $links[] = '<strong style="color: red;">' . sprintf( wp_kses( __( '<a href="%s" style="color: red;">Unlicensed copy. Please enter a valid license key.</a>', 'aawp' ), array(  'a' => array( 'href' => array(), 'style' => array() ) ) ), add_query_arg( 'tab', 'licensing', AAWP_ADMIN_SETTINGS_URL ) ) . '</strong>';
    }

    return $links;
}
add_filter( 'plugin_row_meta', 'aawp_admin_plugin_row_meta_license_notice', 10, 2 );