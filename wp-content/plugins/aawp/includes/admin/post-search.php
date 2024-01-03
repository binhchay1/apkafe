<?php
/**
 * Admin Post Search
 *
 * @package     AAWP\Admin
 * @since       3.4.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * TODO: Re-adding later. Issues with duplicate products for e.g. asin search appared
 */

add_filter('posts_join', function( $join ) {

    return $join;

    global $pagenow, $wpdb;

    if ( ! is_admin() || ! isset( $_GET['post_type'] ) || ! isset( $_GET['s'] ) )
        return $join;

    // I want the filter only when performing a search on edit page of Custom Post Type named "segnalazioni"
    if ( $pagenow =='edit.php' && in_array( $_GET['post_type'], array( 'aawp_product', 'aawp_list' ) ) && $_GET['s'] != '') {
        $join .='LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }

    return $join;

} );

add_filter( 'posts_where', function( $where ) {

    return $where;

    global $pagenow, $wpdb;

    if ( ! is_admin() || ! isset( $_GET['post_type'] ) || ! isset( $_GET['s'] ) )
        return $where;

    // I want the filter only when performing a search on edit page of Custom Post Type named "segnalazioni"
    if ( $pagenow == 'edit.php' && in_array( $_GET['post_type'], array( 'aawp_product', 'aawp_list' ) ) && $_GET['s'] != '' ) {
        $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
    }

    return $where;
} );