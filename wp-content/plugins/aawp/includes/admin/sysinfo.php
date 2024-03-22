<?php
/**
 * Sysinfo
 *
 * @package     AAWP\Admin
 * @since       3.4.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Collect and return sysinfo data
 *
 * @return mixed|string
 */
function aawp_get_sysinfo() {

    $sysinfo = get_transient( 'aawp_sysinfo' );

    if ( ! empty( $sysinfo ) )
        return $sysinfo;

    global $wpdb;

    // Get theme info
    $theme_data = wp_get_theme();
    $theme      = $theme_data->Name . ' ' . $theme_data->Version;

    $return  = '### Begin System Info ###' . "\n\n";

    // Start with the basics...
    $return .= '-- Site Info' . "\n\n";
    $return .= 'Site URL:                 ' . site_url() . "\n";
    $return .= 'Home URL:                 ' . home_url() . "\n";
    $return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

    $return  = apply_filters( 'aawp_sysinfo_after_site_info', $return );

    // The local users' browser information, handled by the Browser class
    $return .= "\n" . '-- User Browser' . "\n\n";
    $return .= $_SERVER['HTTP_USER_AGENT'] . "\n\n";

    $return  = apply_filters( 'aawp_sysinfo_after_user_browser', $return );

    // WordPress configuration
    $return .= "\n" . '-- WordPress Configuration' . "\n\n";
    $return .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
    $return .= 'Language:                 ' . ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ) . "\n";
    $return .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
    $return .= 'Active Theme:             ' . $theme . "\n";
    $return .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

    // Only show page specs if frontpage is set to 'page'
    if( get_option( 'show_on_front' ) == 'page' ) {
        $front_page_id = get_option( 'page_on_front' );
        $blog_page_id = get_option( 'page_for_posts' );

        $return .= 'Page On Front:            ' . ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
        $return .= 'Page For Posts:           ' . ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
    }

    $return .= 'ABSPATH:                  ' . ABSPATH . "\n";

    /*
    // Make sure wp_remote_post() is working
    $request['cmd'] = '_notify-validate';

    $params = array(
        'sslverify'     => false,
        'timeout'       => 60,
        'user-agent'    => 'AAWP/1.0.0',
        'body'          => $request
    );

    $response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

    if( !is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
        $WP_REMOTE_POST = 'wp_remote_post() works';
    } else {
        $WP_REMOTE_POST = 'wp_remote_post() does not work';
    }

    $return .= 'Remote Post:              ' . $WP_REMOTE_POST . "\n";
    */
    $return .= 'Table Prefix:             ' . 'Length: ' . strlen( $wpdb->prefix ) . '   Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'ERROR: Too long' : 'Acceptable' ) . "\n";
    $return .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
    $return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
    //$return .= 'Registered Post Stati:    ' . implode( ', ', get_post_stati() ) . "\n";

    $return  = apply_filters( 'aawp_sysinfo_after_wordpress_config', $return );

    // TODO: Plugin Settings

    $return  = apply_filters( 'aawp_sysinfo_after_plugin_settings', $return );

    // AAWP Posts
    $return .= "\n" . '-- AAWP Generated Content' . "\n\n";
    $return .= 'Products:                 ' . aawp_get_products_count() . "\n";
    $return .= 'Lists:                    ' . aawp_get_lists_count() . "\n";

    $return  = apply_filters( 'aawp_sysinfo_after_plugin_generated_content', $return );

    // AAWP Templates
    $dir = get_stylesheet_directory() . '/aawp/';

    if( is_dir( $dir ) && ( count( glob( "$dir/*" ) ) !== 0 ) ) {

        $return .= "\n" . '-- AAWP Template Overrides' . "\n\n";

        $paths = array( '', 'deprecated/', 'parts/', 'products/' );

        foreach ( $paths as $path ) {
            if ( is_dir( $dir . $path ) && ( count( glob( $dir . $path . "*" ) ) !== 0 ) ) {
                foreach ( glob( $dir . $path . '*.php' ) as $file ) {
                    $return .= 'Filename:                 ' . $path . basename( $file ) . "\n";
                }
            }
        }

        $return  = apply_filters( 'aawp_sysinfo_after_templates', $return );
    }

    // Get plugins that have an update
    $updates = get_plugin_updates();

    // Must-use plugins
    // NOTE: MU plugins can't show updates!
    $muplugins = get_mu_plugins();
    if( count( $muplugins ) > 0 ) {
        $return .= "\n" . '-- Must-Use Plugins' . "\n\n";

        foreach( $muplugins as $plugin => $plugin_data ) {
            $return .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
        }

        $return = apply_filters( 'aawp_sysinfo_after_wordpress_mu_plugins', $return );
    }

    // WordPress active plugins
    $return .= "\n" . '-- WordPress Active Plugins' . "\n\n";

    $plugins = get_plugins();
    $active_plugins = get_option( 'active_plugins', array() );

    foreach( $plugins as $plugin_path => $plugin ) {
        if( !in_array( $plugin_path, $active_plugins ) )
            continue;

        $update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
        $return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
    }

    $return  = apply_filters( 'aawp_sysinfo_after_wordpress_plugins', $return );

    // WordPress inactive plugins
    $return .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";

    foreach( $plugins as $plugin_path => $plugin ) {
        if( in_array( $plugin_path, $active_plugins ) )
            continue;

        $update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
        $return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
    }

    $return  = apply_filters( 'aawp_sysinfo_after_wordpress_plugins_inactive', $return );

    if( is_multisite() ) {
        // WordPress Multisite active plugins
        $return .= "\n" . '-- Network Active Plugins' . "\n\n";

        $plugins = wp_get_active_network_plugins();
        $active_plugins = get_site_option( 'active_sitewide_plugins', array() );

        foreach( $plugins as $plugin_path ) {
            $plugin_base = plugin_basename( $plugin_path );

            if( !array_key_exists( $plugin_base, $active_plugins ) )
                continue;

            $update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
            $plugin  = get_plugin_data( $plugin_path );
            $return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
        }

        $return  = apply_filters( 'aawp_sysinfo_after_wordpress_ms_plugins', $return );
    }

    // Server configuration (really just versioning)
    $return .= "\n" . '-- Webserver Configuration' . "\n\n";
    $return .= 'PHP Version:              ' . PHP_VERSION . "\n";
    $return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
    $return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

    $return  = apply_filters( 'aawp_sysinfo_after_webserver_config', $return );

    // PHP configs... now we're getting to the important stuff
    $return .= "\n" . '-- PHP Configuration' . "\n\n";
    $return .= 'Safe Mode:                ' . ( ini_get( 'safe_mode' ) ? 'Enabled' : 'Disabled' . "\n" );
    $return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
    $return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
    $return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
    $return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
    $return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
    $return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
    $return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";

    $return  = apply_filters( 'aawp_sysinfo_after_php_config', $return );

    // PHP extensions and such
    $return .= "\n" . '-- PHP Extensions' . "\n\n";
    $return .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "\n";
    $return .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "\n";
    $return .= 'SOAP Client:              ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . "\n";
    $return .= 'mbstring:                 ' . ( extension_loaded('mbstring') ? 'Supported' : 'Not Supported' ) . "\n";
    $return .= 'allow_url_fopen:          ' . ( ini_get('allow_url_fopen') ? 'Supported' : 'Not Supported' ) . "\n";
    $return .= 'Suhosin:                  ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . "\n";

    $return  = apply_filters( 'aawp_sysinfo_after_php_ext', $return );

    $return .= "\n" . '### End System Info ###';

    // This template was taken by the awesome "Easy Digital Downloads" and its team. Thanks for saving me a lot of time :-)

    set_transient( 'aawp_sysinfo', $return, 60 * 15 );

    return $return;
}