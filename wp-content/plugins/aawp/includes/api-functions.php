<?php
/**
 * Amazon API functions
 *
 * @package     AAWP
 * @since       3.4.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get list from API
 *
 * @param array $args
 *
 * @return array|bool|null
 */
function aawp_get_list_from_api( $args = array() ) {

    $list = aawp()->api->get_list( $args );

    return $list;
}

/**
 * Get product from API
 *
 * @param $asin
 * @param array $args
 *
 * @return bool
 */
function aawp_get_product_from_api( $asin, $args = array() ) {

    $product = aawp()->api->get_product( $asin, $args );

    return $product;
}

/**
 * Get products from API
 *
 * @param array $args
 *
 * @return bool
 */
function aawp_get_products_from_api( $asins = array(), $args = array() ) {

    $products = aawp()->api->get_products( $asins, $args );

    return $products;
}

/**
 * Handle API product error response
 *
 * @param string $asin
 * @param array $error Expecting keys "code" and "message".
 */
function aawp_handle_api_product_error_response( $asin, $error ) {

    if ( is_array( $asin ) )
        $asin = implode( ',', $asin );

    $log_text = 'API Response Error >> ASIN: ' . $asin . ' >> Code: ' . $error['code'] . ' - Message: ' . $error['message'];

    //aawp_debug_display( $log_text );
    aawp_add_log( $log_text );

    return;
    /*
     * Handling status
     *
     *  "XXX is not a valid value for ItemId. Please change this value and retry your request."
     *  "This item is not accessible through the Product Advertising API."
     */
    $status = 'unknown';

    if ( strpos( $response, 'not a valid value for ItemId' ) !== false )
        aawp_update_product_status_by_asin( $asin, 'invalid' );

    if ( strpos( $response, 'not accessible through the Product Advertising API' ) !== false )
        aawp_update_product_status_by_asin( $asin, 'not_available' );

    //aawp_debug_log( 'aawp_handle_api_product_error_response >> ' . $response );

    /*
     * Fire hook for further actions
     */
    //do_action( 'aawp_api_product_error', $asin, $status );
}