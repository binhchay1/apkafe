<?php
/**
 * Admin Actions
 *
 * @package     AAWP\Helper
 * @since       2.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Handle admin actions
 */
function aawp_handle_admin_action() {

    if ( strpos($_SERVER['REQUEST_URI'], 'aawp_admin_action') !== false) {

        $action = ($_GET['aawp_admin_action']) ? $_GET['aawp_admin_action'] : null;

        if ($action === 'upgrade_rebuild') {
            aawp_admin_action_upgrade_rebuild();
        }
    }
}

add_action('admin_init','aawp_handle_admin_action');

/*
 * Reset plugin settings
 */
function aawp_reset() {

    // Options
    aawp_reset_options();

    // TODO: Remove lists and products

    // Finished
    wp_redirect( add_query_arg( 'aawp_admin_notice', 'reset_success', AAWP_ADMIN_SETTINGS_URL ), 301);
    exit;
}

/**
 * Reset database tables
 */
function aawp_reset_database() {

    global $wpdb;

    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "aawp_products" );
    $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "aawp_lists" );

    // Second: Create new tables
    aawp()->products->create_table();
    aawp()->lists->create_table();
}

/**
 * Empty database tables
 */
function aawp_empty_database_tables() {

    global $wpdb;

    $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . "aawp_products");
    $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . "aawp_lists");
}

/*
 * Delete options
 */
function aawp_reset_options() {
    delete_option( 'aawp_licensing' );
    delete_option( 'aawp_api' );
    delete_option( 'aawp_general' );
    delete_option( 'aawp_output' );
    delete_option( 'aawp_functions' );
    delete_option( 'aawp_support' );
}

