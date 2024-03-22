<?php
/**
 * List overview page
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

    if ( 'aawp_list' === get_post_type() ) {
        unset( $actions['inline hide-if-no-js'] );
        unset( $actions['view'] );
    }

    return $actions;

}, 10, 1 );

/**
 * Add post columns
 */
add_filter('manage_aawp_list_posts_columns', function( $defaults) {

    unset( $defaults['date'] );

    $defaults['aawp_list_type'] = __( 'Type', 'aawp' );
    $defaults['aawp_list_details'] = __( 'Details', 'aawp' );
    $defaults['aawp_list_max'] = __( 'Items', 'aawp' );
    //$defaults['aawp_list_status'] = __( 'Status', 'aawp' );
    //$defaults['aawp_list_store'] = __( 'Store', 'aawp' );
    $defaults['aawp_list_last_update'] = __( 'Last Update', 'aawp' );
    $defaults['aawp_list_actions'] = __( 'Actions', 'aawp' );

    return $defaults;

}, 10);

/**
 * Add post columns content
 */
add_action('manage_aawp_list_posts_custom_column', function( $column_name, $post_id ) {

    if ( $column_name == 'aawp_list_type' ) {
        $type = aawp_get_list_type( $post_id );
        echo ( ! empty( $type ) ) ? aawp_admin_display_list_type( $type, $echo = false ) : '-';

    } elseif ( $column_name == 'aawp_list_details' ) {
        $keys = aawp_get_list_keys( $post_id );
        echo ( ! empty( $keys ) ) ? $keys : '-';

        $browsenode = aawp_get_list_browse_node( $post_id );
        if ( ! $browsenode )
            echo '<span style="display: block; font-size: 0.8em; color: #bbb;">' . __( 'Browse Nodes disabled', 'aawp' ) . '</span>';

    } elseif ( $column_name == 'aawp_list_max' ) {
        $max = aawp_get_list_max( $post_id );
        echo ( ! empty( $max ) ) ? $max : '-';

    } elseif ( $column_name == 'aawp_list_status' ) {
        $status = aawp_get_list_status( $post_id );
        aawp_admin_display_post_type_entry_status( $status );

    } elseif ( $column_name == 'aawp_list_store' ) {
        $store = aawp_get_list_store( $post_id );
        aawp_the_icon_flag( $store );
        echo '&nbsp;Amazon.' . $store;

    } elseif ( $column_name == 'aawp_list_last_update' ) {
        aawp_admin_the_renew_post_last_update( $post_id, $type = 'aawp_list' );

    } elseif ( $column_name == 'aawp_list_actions' ) {
        aawp_admin_the_renew_post_button( $post_id, $type = 'aawp_list' );
    }

}, 10, 2);