<?php
/**
 * Attach product ASINs to list args
 *
 * @param $list_args
 * @param $data
 * @return mixed
 */
function aawp_attach_product_asins_to_list_args( $list_args, $data ) {

    $product_asins = aawp_get_product_asins_from_list_data( $data );

    if ( ! empty ( $product_asins ) )
        $list_args['product_asins'] = $product_asins;

    return $list_args;
}

/**
 * Get product ASINs from list data (coming from API)
 *
 * @param $data
 * @return array|null
 */
function aawp_get_product_asins_from_list_data( $data ) {

    if ( empty( $data ) || ! is_array( $data ) || sizeof( $data ) === 0 )
        return null;

    $product_asins = array();

    foreach ( $data as $Item ) {

        /** var Flowdee\AmazonPAAPI5WP\Item $Item */
        if ( is_object( $Item ) && method_exists( $Item, 'getASIN' ) && ! empty( $product_asin = $Item->getASIN() ) )
            $product_asins[] = $product_asin;
    }

    return $product_asins;
}

/**
 * Get list key from args
 *
 * @param array $args
 * @return string
 */
function aawp_get_list_key_from_args( $args = array() ) {

    $key = '';

    return $key;
}

/**
 * Set up list data coming from API, before storing in database
 *
 * @param $data
 * @param bool $is_update
 * @return array|null
 */
function aawp_setup_list_data_for_database( $data, $is_update = false ) {

    if ( empty ( $data ) || empty( $data['product_asins'] ) )
        return null;

    $data_prepared = array(
        'status' => 'active',
        'list_key' => '',
        'type' => ( ! empty( $data['type'] ) ) ? $data['type'] : '',
        'keywords' => ( ! empty( $data['keywords'] ) ) ? $data['keywords'] : '',
        'browse_node_id' => ( ! empty( $data['browse_node_id'] ) && is_numeric( $data['browse_node_id'] ) ) ? floatval( $data['browse_node_id'] ) : '',
        'browse_node_search' => ( ! empty( $data['browse_node_search'] ) && 1 == $data['browse_node_search'] ) ? 1 : '',
        'product_asins' => ( is_array( $data['product_asins'] ) ) ? implode(',', $data['product_asins'] ) : $data['product_asins'],
        'items_count' => ( ! empty( $data['items_count'] ) && is_numeric( $data['items_count'] ) ) ? floatval( $data['items_count'] ) : 0,
    );

    $list_key = aawp_generate_list_key( $data_prepared );

    if ( empty( $list_key ) )
        return null;

    $data_prepared['list_key'] = $list_key;

    //aawp_debug( $data_prepared, 'prepare_list_data() >> $data_prepared' );

    return $data_prepared;
}

/**
 * Set up list data coming from the database, before using in our plugin
 *
 * @param $data
 * @return array
 */
function aawp_setup_list_data_from_database( $data ) {

    if ( is_object( $data ) ) {

        // Convert object to array
        $data = get_object_vars( $data );

        // Convert product asins from comma separated string to array
        if ( isset( $data['product_asins'] ) )
            $data['product_asins'] = explode( ',', $data['product_asins'] );
    }

    return $data;
}

/**
 * Generate list key based on arguments
 *
 * @param array $args
 *
 * @return bool|string
 */
function aawp_generate_list_key( $args = array() ) {

    if ( empty( $args['type'] ) || ( empty( $args['items_count'] ) || ! is_numeric( $args['items_count'] ) ) )
        return null;

    $list_key_string = 'aawp_list_key_';

    $list_key_args = array(
        'type' => $args['type'],
        'keywords' => ( ! empty( $args['keywords'] ) ) ? $args['keywords'] : '',
        'browse_node_id' => ( ! empty( $args['browse_node_id'] ) && is_numeric( $args['browse_node_id'] ) ) ? floatval( $args['browse_node_id'] ) : 0,
        'browse_node_search' => ( ! empty( $args['browse_node_search'] ) && 1 == $args['browse_node_search'] ) ? 1 : 0,
        'items_count' => floatval( $args['items_count'] )
    );

    //aawp_debug( $list_key_args, 'generate_list_key() >> $list_key_args');

    $list_key_args = implode( '_', $list_key_args );
    $list_key_string .= $list_key_args;
    $list_key = md5( $list_key_string );

    //aawp_debug( $list_key_string, 'generate_list_key() >> $list_key_string');
    //aawp_debug( $list_key, 'generate_list_key() >> $list_key');

    return $list_key;
}