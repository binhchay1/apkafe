<?php
/**
 * Ajax
 *
 * @package     AAWP\Includes\Admin
 * @since       3.4
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax callback: Select product image
 */
function aawp_admin_ajax_select_product_image_action() { // TODO: Deprecated

    // Sanitizing form data
    $post_id = ( isset ( $_POST['post_id'] ) ) ? intval( $_POST['post_id'] ) : 0;
    $store = ( isset ( $_POST['store'] ) ) ? sanitize_text_field( $_POST['store'] ) : null;
    $image = ( isset ( $_POST['image'] ) ) ? intval( $_POST['image'] ) : 0;

    // AJAX Call
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

        $response = false;

        //aawp_debug_log( '*** admin_ajax_select_product_image_action >> Post ID: ' . $post_id . ' - Store: ' . $store . ' - Image: ' . $image );

        if ( $post_id && is_numeric( $image ) && ! empty( $store ) ) {
            //aawp_update_product_default_image( $post_id, $image, $store );
            $response = true;
        }

        // response output
        //header( "Content-Type: application/json" );
        echo $response;
    }

    // IMPORTANT: don't forget to "exit"
    exit;
}
//add_action( 'wp_ajax_nopriv_aawp_admin_ajax_select_product_image_action', 'aawp_admin_ajax_select_product_image_action' );
//add_action( 'wp_ajax_aawp_admin_ajax_select_product_image_action', 'aawp_admin_ajax_select_product_image_action' );

/**
 * Ajax callback: Renew list/product post
 */
function aawp_admin_ajax_renew_post_action() {

    // Sanitizing form data
    $post_id = ( isset ( $_POST['post_id'] ) ) ? intval( $_POST['post_id'] ) : 0;

    // AJAX Call
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

        $response = false;

        if ( $post_id ) {

            $post_type = get_post_type( $post_id );

            // Lists
            if ( 'aawp_list' === $post_type ) {
                $renewed = aawp_renew_list( $post_id );

                if ( $renewed )
                    $response = aawp_get_list_last_update( $post_id );

            // Products
            } elseif ( 'aawp_product' === $post_type ) {

                // Renew data
                $renewed = aawp_renew_product( $post_id );

                if ( true === $renewed ) {
                    $response = aawp_get_product_last_update( $post_id ); // TODO Replace

                    // Renew ratings
                    $rating_renewed = aawp_renew_product_rating( $post_id ); // TODO Replace
                }
            }

            //aawp_debug_log( '*** aawp_admin_ajax_renew_post_action >> Post ID: ' . $post_id . ' - Post Type: ' . $post_type . ' - Response: ' . $response );
        }

        // response output
        //header( "Content-Type: application/json" );
        echo $response;
    }

    // IMPORTANT: don't forget to "exit"
    exit;
}
add_action( 'wp_ajax_nopriv_aawp_admin_ajax_renew_post_action', 'aawp_admin_ajax_renew_post_action' );
add_action( 'wp_ajax_aawp_admin_ajax_renew_post_action', 'aawp_admin_ajax_renew_post_action' );


/**
 * Admin ajax product search
 */
function aawp_admin_ajax_search() {

    // AJAX Call
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

        $response = false;

        // Variables
        $type = ( isset ( $_POST['type'] ) ) ? sanitize_text_field( $_POST['type'] ) : 'search';
        $keywords = ( isset ( $_POST['keywords'] ) ) ? sanitize_text_field( $_POST['keywords'] ) : '';

        if ( ! empty( $type ) && ! empty( $keywords ) ) {

            $search_args = array(
                'keywords' => $keywords,
                'items_count' => 12
            );

            $products = aawp()->api->get_search_items( $search_args );

            //aawp_debug_log( $products );

            // Error
            if ( is_string( $products ) ) {
                $response = $products;

            // No results
            } elseif ( is_null( $products ) || isset( $products['error'] ) ) {
                $response = '<p class="aawp-notice aawp-notice--info">' . __( 'No products found.', 'aawp' ) . '</p>';

            // Array of ASINs
            } elseif ( is_array( $products ) && sizeof( $products ) > 0 ) {

                ob_start();
                include 'templates/ajax-search-results.php';
                $output = ob_get_clean();

                if ( ! empty( $output ) )
                    $response = $output;
            }

        }

        // response output
        echo $response;
    }

    // IMPORTANT: don't forget to "exit"
    exit;
}
add_action( 'wp_ajax_nopriv_aawp_admin_ajax_search_action', 'aawp_admin_ajax_search' );
add_action( 'wp_ajax_aawp_admin_ajax_search_action', 'aawp_admin_ajax_search' );
