<?php
/**
 * Uninstall
 *
 * Deletes all the plugin data i.e.
 * 		1. Plugin options.
 * 		2. Database tables.
 *      3. Events
 *
 * @since       3.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

$aawp_support_settings = get_option( 'aawp_support' );

if ( ! isset ( $aawp_support_settings['uninstall_remove_data'] ) || $aawp_support_settings['uninstall_remove_data'] != '1' )
    return;

/*
 * Delete plugin options
 */
delete_option( 'aawp_licensing' );
delete_option( 'aawp_api' );
delete_option( 'aawp_general' );
delete_option( 'aawp_output' );
delete_option( 'aawp_functions' );
delete_option( 'aawp_support' );

/*
 * Delete old database tables
 */
global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "aawp_products" );
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "aawp_lists" );

delete_option( $wpdb->prefix . "aawp_products_db_version" );
delete_option( $wpdb->prefix . "aawp_lists_db_version" );

/*
 * Delete transients
 */
$wpdb->query( "DELETE FROM `" . $wpdb->prefix . "options` WHERE `option_name` LIKE ('_transient_aawp_%')" );

/*
 * Removed scheduled events
 */
wp_clear_scheduled_hook('aawp_wp_scheduled_events');
wp_clear_scheduled_hook('aawp_wp_scheduled_daily_events');
wp_clear_scheduled_hook('aawp_wp_scheduled_weekly_events');