<?php
/**
 * List functions
 *
 * @package     AAWP
 * @since       3.4.0
 */

// Exit if accessed directly
use Flowdee\AmazonPAAPI5WP\Item;

if( !defined( 'ABSPATH' ) ) exit;

/**
 * Create list
 *
 * @param $data
 *
 * @return bool|int
 */
function aawp_create_list( $data ) {

    if ( empty( $data ) )
        return false;

    $list_id = aawp()->lists->add( $data );

    return $list_id;
}

/**
 * Get single list from database by id
 *
 * @param int $list_id
 *
 * @return array|null
 */
function aawp_get_list( $list_id ) {

    if ( empty( $list_id ) )
        return null;

    $list = aawp()->lists->get_list_by( 'id', $list_id );

    return $list;
}

/**
 * Get single list from database by list key
 *
 * @param $list_key
 *
 * @return array|bool|null|array
 */
function aawp_get_list_by_key( $list_key ) {

    if ( empty( $list_key ) )
        return null;

    $list = aawp()->lists->get_list_by( 'list_key', $list_key );

    return $list;
}

/**
 * Get single list from database by args
 *
 * @param array $args
 *
 * @return array|bool|null|array
 */
function aawp_get_list_by_args( $args = array() ) {

    if ( ! is_array( $args ) )
        return null;

    $list = aawp()->lists->get_list_by_args( $args );

    return $list;
}

/**
 * Get multiple lists from database by args
 *
 * @param array $args
 *
 * @return array
 */
function aawp_get_lists( $args = array() ) {

    $lists = aawp()->lists->get_lists( $args );

    return $lists;
}

/**
 * Update list in database
 *
 * @param $list_id
 * @param $data
 *
 * @return bool
 */
function aawp_update_list( $list_id, $data ) {

    if ( empty( $list_id ) || empty( $data ) )
        return false;

    $updated = aawp()->lists->update( $list_id, $data );

    return $updated;
}

/**
 * Renew list based on list data
 *
 * @param array $list_data
 *
 * @return bool
 */
function aawp_renew_list( $list_data = array() ) {

    if ( ! isset( $list_data['id'] ) )
        return false;

    $list = aawp()->api->get_list( $list_data );

    if ( ! empty( $list ) ) {

        $list_data['product_asins'] = aawp_get_product_asins_from_list_data( $list );

        // Step 1: Update list in database
        $updated = aawp_update_list( $list_data['id'], $list_data );

        if ( $updated ) {
            // Step 2: Update products associated to this list in database
            if ( is_array( $list ) && sizeof( $list ) > 0 ) {
                foreach ( $list as $product ) {
                    $product_added = aawp_create_product( $product );
                }
            }

            return true;
        }
    }

    return false;
}

/**
 * Get total amount of stored lists
 *
 * @return int
 */
function aawp_get_lists_count() {

    $count = aawp()->lists->count();

    return ( is_numeric( $count ) ) ? intval( $count ) : 0;
}