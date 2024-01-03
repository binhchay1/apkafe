<?php
/**
 * Product overview page
 *
 * @package     AAWP\Admin
 * @since       3.4.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle actions
 */
add_filter( 'post_row_actions', function( $actions ) {

    if ( 'aawp_product' === get_post_type() ) {
        unset( $actions['inline hide-if-no-js'] );
        unset( $actions['view'] );
    }

    return $actions;

}, 10, 1 );

/**
 * Add post columns
 */
add_filter('manage_aawp_product_posts_columns', function( $defaults) {

    unset( $defaults['date'] );

    $defaults['aawp_product_details'] = __( 'Details', 'aawp' );
    $defaults['aawp_product_status'] = __( 'Status', 'aawp' );
    $defaults['aawp_product_last_update'] = __( 'Last Update', 'aawp' );
    $defaults['aawp_product_actions'] = __( 'Actions', 'aawp' );

    return $defaults;

}, 10);

/**
 * Add post columns content
 */
add_action('manage_aawp_product_posts_custom_column', function( $column_name, $post_id ) {

    if ( $column_name == 'aawp_product_details' ) {
        $title = aawp_get_product_title( $post_id );
        echo ( ! empty( $title ) ) ? aawp_truncate_string( $title, 65 ) : '-';

    } elseif ( $column_name == 'aawp_product_status' ) {
        $status = aawp_get_product_status( $post_id );
        aawp_admin_display_post_type_entry_status( $status );

    } elseif ( $column_name == 'aawp_product_last_update' ) {
        aawp_admin_the_renew_post_last_update( $post_id, $type = 'aawp_product' );

    } elseif ( $column_name == 'aawp_product_actions' ) {
        aawp_admin_the_renew_post_button( $post_id, $type = 'aawp_product' );
    }

}, 10, 2);