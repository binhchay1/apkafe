<?php
/**
 * Handling plugin upgrades
 *
 * @since       3.6.1
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Handling plugin upgrades
 */
function aawp_admin_plugin_upgrades() {

    //set_transient( 'aawp_plugin_update_v3_9_completed', true, 24 * HOUR_IN_SECONDS );

    $aawp_version_installed = get_option( 'aawp_version', '' );

    //aawp_debug_log( 'AAWP_VERSION >> ' . AAWP_VERSION . ' - $aawp_version_installed >> ' . $aawp_version_installed );

    if ( $aawp_version_installed === AAWP_VERSION || ( ! empty( $aawp_version_installed ) && version_compare( AAWP_VERSION, $aawp_version_installed, '<' ) ) )
        return;

    /*
     * Loop updates
     ---------------------------------------------------------- */
    if ( ! empty( $aawp_version_installed ) ) {

        // 3.3.0
        if ( version_compare( $aawp_version_installed, '3.3.0', '<' ) )
            aawp_admin_plugin_update_pre_3_3_0_action();

        // 3.3.2
        if ( version_compare( $aawp_version_installed, '3.3.2', '<' ) )
            aawp_admin_plugin_update_pre_3_3_2_action();

        // 3.3.3
        if ( version_compare( $aawp_version_installed, '3.3.3', '<' ) )
            aawp_admin_plugin_update_pre_3_3_3_action();

        // 3.4.3
        if ( version_compare( $aawp_version_installed, '3.4.3', '<' ) )
            aawp_admin_plugin_update_pre_3_4_3_action();

        // 3.6.1
        if ( version_compare( $aawp_version_installed, '3.6.1', '<' ) ) {
            aawp_admin_plugin_update_pre_3_6_1_action();
        }

        // 3.6.4
        if ( version_compare( $aawp_version_installed, '3.6.4', '<' ) ) {
            aawp_admin_plugin_update_pre_3_6_4_action();
        }

        // 3.6.9
        if ( version_compare( $aawp_version_installed, '3.6.9', '<' ) ) {
            aawp_admin_plugin_update_pre_3_6_9_action();
        }

        // 3.9
        if ( version_compare( $aawp_version_installed, '3.9', '<' ) ) {
            aawp_admin_plugin_update_pre_3_9_action();
        }

        // 3.11
        if ( version_compare( $aawp_version_installed, '3.11', '<' ) ) {
            aawp_admin_plugin_update_pre_3_11_action();
        }

        // 3.14.3
        if ( version_compare( $aawp_version_installed, '3.14.3', '<' ) ) {
            aawp_admin_plugin_update_pre_3_14_3_action();
        }
    }
    /* ---------------------------------------------------------- */

    aawp_add_log( 'Plugin v' . AAWP_VERSION . ' upgrade routine completed.' );

    // General tasks
    aawp_delete_transients();
    aawp_check_scheduled_events();

    // Update current version
    update_option( 'aawp_version', AAWP_VERSION );
}
add_action( 'admin_init', 'aawp_admin_plugin_upgrades' );

/**
 * Pre v3.14.3 upgrade handler
 *
 * - Lookup and overwrite deprecated license server url
 */
function aawp_admin_plugin_update_pre_3_14_3_action() {

    $licensing_settings = aawp_get_options( 'licensing' );

    // Lookup old domain.
    if ( ! empty ( $licensing_settings['server'] ) && strpos( $licensing_settings['server'], 'aawp.de/en' ) !== false ) {

        // Overwrite deprecated server.
        $licensing_settings['server'] = 'https://getaawp.com';

        // Update in database
        aawp_update_options( 'licensing', $licensing_settings );
    }
}

/**
 * Pre v3.10.4 upgrade handler
 * - Convert database tables to utf8mb4
 */
function aawp_admin_plugin_update_pre_3_11_action() {

    global $wpdb;

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $db_products_name = $wpdb->prefix . 'aawp_products';
    $db_lists_name = $wpdb->prefix . 'aawp_lists';

    // Update products table.
    $db_products_updated = maybe_convert_table_to_utf8mb4( $db_products_name );

    if ( $db_products_updated )
        update_option( $db_products_name . '_db_version', '3.11' );

    // Update lists table.
    $db_lists_updated = maybe_convert_table_to_utf8mb4( $db_lists_name );

    if ( $db_lists_updated )
        update_option( $db_lists_name . '_db_version', '3.11' );
}

/**
 * Pre v3.9 upgrade handler
 * - Recreate database tables
 */
function aawp_admin_plugin_update_pre_3_9_action() {

    //aawp_debug_log( 'aawp_admin_plugin_update_pre_3_9_action() executed' );

    // Delete existing database tables
    global $wpdb;

    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "aawp_products" );
    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "aawp_lists" );

    delete_option( $wpdb->prefix . "aawp_products_db_version" );
    delete_option( $wpdb->prefix . "aawp_lists_db_version" );

    // Create new database tables
    $AAWP_DB_Products = new AAWP_DB_Products();

    if ( ! $AAWP_DB_Products->installed() )
        $AAWP_DB_Products->create_table();

    $AAWP_DB_Lists = new AAWP_DB_Lists();

    if ( ! $AAWP_DB_Lists->installed() )
        $AAWP_DB_Lists->create_table();

    set_transient( 'aawp_plugin_update_v3_9_completed', true, 24 * HOUR_IN_SECONDS );
}

/**
 * Pre v3.6.9 upgrade handler
 * - Restore old "hide button" setting
 */
function aawp_admin_plugin_update_pre_3_6_9_action() {

    $output_settings = aawp_get_options( 'output' );

    // Button icon was "0"? then reset icon and set new hide icon setting
    if ( empty( $output_settings['button_icon'] ) ) {
        $output_settings['button_icon'] = 'black';
        $output_settings['button_icon_hide'] = '1';

        aawp_update_options( 'output', $output_settings );
    }
}

/**
 * Below v3.6.4 upgrade handler
 * - Cleanup products without image ids from database
 */
function aawp_admin_plugin_update_pre_3_6_4_action() {

    $products = aawp()->products->get_products( array( 'number' => 200, 'images_missing' => true ) );

    if ( ! empty( $products ) && is_array( $products ) ) {

        foreach ( $products as $product ) {

            if ( ! isset( $product['id'] ) )
                continue;

            $deleted = aawp()->products->delete( $product['id'] );
        }
    }
}

/**
 * Below v3.6.1 upgrade handler
 * - Remove old database tables
 * - Create new database tables
 */
function aawp_admin_plugin_update_pre_3_6_1_action() {
    aawp_reset_database();
}

/**
 * Below v3.4.3 upgrade handler
 * - Optimize price reduction settings (#737)
 */
function aawp_admin_plugin_update_pre_3_4_3_action() {

    // Get options
    $output_options = aawp_get_options( 'output' );

    // Handle renamed "pricing_saved_type" key
    $output_options['pricing_reduction'] = ( isset( $output_options['pricing_saved_type'] ) && 'hidden' != $output_options['pricing_saved_type'] ) ? $output_options['pricing_saved_type'] : 'amount';

    // New defaults
    $output_options['pricing_show_old_price'] = true;
    $output_options['pricing_show_price_reduction'] = true;
    $output_options['pricing_sale_ribbon_text'] = __( 'Sale', 'aawp' );

    // Handle removed "hidden" option for previous "pricing_saved_type" setting
    if ( isset( $output_options['pricing_saved_type'] ) && 'hidden' === $output_options['pricing_saved_type'] ) {
        $output_options['pricing_show_price_reduction'] = false;
        $output_options['pricing_sale_ribbon_text'] = '';
    }

    // Handle removed options for previous "pricing_saved_position" setting
    if ( isset( $output_options['pricing_saved_position'] ) && 'ribbon' === $output_options['pricing_saved_position'] ) {
        $output_options['pricing_show_price_reduction'] = false;
        $output_options['pricing_sale_ribbon_text'] = '%PRICE_REDUCTION%';

    } elseif ( isset( $output_options['pricing_saved_position'] ) && 'no_ribbon' === $output_options['pricing_saved_position'] ) {
        $output_options['pricing_sale_ribbon_text'] = '';
    }

    // Remove old keys
    unset( $output_options['pricing_saved_type'] );
    unset( $output_options['pricing_saved_position'] );

    // Update options
    aawp_update_options( 'output', $output_options );
}


/**
 * Below v3.3.3 upgrade handler
 * - Reset cronjobs
 * - Set global last_update timestamp if empty
 */
function aawp_admin_plugin_update_pre_3_3_3_action() {

    // Reset cronjobs
    wp_clear_scheduled_hook('aawp_wp_scheduled_events');
    wp_clear_scheduled_hook('aawp_wp_scheduled_daily_events');
    wp_clear_scheduled_hook('aawp_wp_scheduled_weekly_events');

    if ( ! wp_next_scheduled ( 'aawp_wp_scheduled_events' ) )
        wp_schedule_event( time(), 'aawp_continuously', 'aawp_wp_scheduled_events' );

    if ( ! wp_next_scheduled ( 'aawp_wp_scheduled_daily_events' ) )
        wp_schedule_event( time(), 'daily', 'aawp_wp_scheduled_daily_events' );

    if ( ! wp_next_scheduled ( 'aawp_wp_scheduled_weekly_events' ) )
        wp_schedule_event( time(), 'aawp_weekly', 'aawp_wp_scheduled_weekly_events' );

    // Set global last_update timestamp if empty
    $last_update = aawp_get_cache_last_update();

    if ( empty( $last_update ) )
        aawp_set_cache_last_update();
}

/**
 * Below v3.3.2 upgrade handler
 * - Adding new daily cron event
 * - Updating (re-)moved settings
 */
function aawp_admin_plugin_update_pre_3_3_2_action() {

    // Checking cron events
    //if ( ! wp_next_scheduled ( 'aawp_wp_scheduled_daily_events' ) ) // Deprecated
    //    wp_schedule_event( time(), 'daily', 'aawp_wp_scheduled_daily_events' );  // Deprecated

    // Update options
    $general_options = aawp_get_options( 'general' );
    $output_options = aawp_get_options( 'output' );

    $output_options['check_prime'] = ( isset( $output_options['show_check_prime'] ) && $output_options['show_check_prime'] == '1' ) ? 'linked' : 'none';
    unset( $output_options['show_check_prime'] );

    $output_options['button_cart_links'] = ( isset( $general_options['affiliate_links_cart'] ) && $general_options['affiliate_links_cart'] == '1' ) ? '1' : null;
    unset( $general_options['affiliate_links_cart'] );

    aawp_update_options( 'general', $general_options );
    aawp_update_options( 'output', $output_options );
}

/**
 * Below v3.3.0 upgrade handler
 * - Database tables
 * - Cron events
 * - Template settings
 */
function aawp_admin_plugin_update_pre_3_3_0_action() {

    // Checking cron events
    if ( ! wp_next_scheduled ( 'aawp_wp_scheduled_events' ) )
        wp_schedule_event( time(), 'hourly', 'aawp_wp_scheduled_events' );

    // Updating selected default templates which are deprecated
    if ( function_exists( 'aawp_get_options' ) && function_exists( 'aawp_update_options' ) ) {

        $functions_options = aawp_get_options( 'functions' );

        $template_checks = array( 'box_template', 'bestseller_template', 'new_releases_template' );

        foreach ( $template_checks as $default_template ) {

            if ( isset( $functions_options[$default_template] ) ) {

                $template_saved = $functions_options[$default_template];

                if ( in_array( $template_saved, array( 'box', 'bestseller', 'new_releases' ) ) )
                    $template_saved = 'horizontal';

                if ( in_array( $template_saved, array( 'box_table', 'bestseller_table', 'new_releases_table' ) ) )
                    $template_saved = 'table';

                $functions_options[$default_template] = $template_saved;
            }
        }

        aawp_update_options( 'functions', $functions_options);
    }

    // Removing old option caches
    delete_option( 'aawp_cache' );
    delete_option( 'aawp_rating_cache' );
}

// Below 3.0.0
function aawp_admin_plugin_update_pre_3_0_0_action() {

    $api_options = get_option( 'aawp_api', array() );

    if (sizeof($api_options) == 0)
        return;

    // Reset default API connection
    $api_options['status'] = 0;
    update_option( 'aawp_api', $api_options );

    // Clear cache
    //aawp_renew_cache(); // Deprecated
}