<?php
/**
 * Scripts
 *
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Load admin scripts
 *
 * @since       3.2.0
 */
function aawp_admin_scripts() {

    // Load dependencies.
    wp_enqueue_style( 'wp-color-picker' );

    // Load scripts.
    wp_enqueue_script( 'aawp-admin', AAWP_PLUGIN_URL . 'assets/dist/js/admin.js', array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ), AAWP_VERSION );
    wp_enqueue_style( 'aawp-admin', AAWP_PLUGIN_URL . 'assets/dist/css/admin.css', false, AAWP_VERSION );

    // Prepare ajax.
    wp_localize_script( 'aawp-admin', 'aawp_post', array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ));
}
add_action( 'aawp_load_admin_scripts', 'aawp_admin_scripts' );

/**
 * Load frontend scripts
 *
 * @since       3.2.0
 */
function aawp_scripts() {

    // Load styles.
    wp_enqueue_style( 'aawp', AAWP_PLUGIN_URL . 'assets/dist/css/main.css', false, AAWP_VERSION );

    // Don't load javascript on AMP endpoints.
    if ( aawp_is_amp_endpoint() )
        return;

    // Load scripts.
    wp_enqueue_script( 'aawp', AAWP_PLUGIN_URL . 'assets/dist/js/main.js', array( 'jquery' ), AAWP_VERSION, true );
}
add_action( 'aawp_load_scripts', 'aawp_scripts' );

