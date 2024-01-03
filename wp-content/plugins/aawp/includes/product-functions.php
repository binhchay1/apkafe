<?php
/**
 * Product functions
 *
 * @package     AAWP
 * @since       3.4.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Create product in database
 *
 * @param $data
 *
 * @return bool|int
 */
function aawp_create_product( $data ) {

    if ( empty( $data ) )
        return false;

    $product_id = aawp()->products->add( $data );

    return $product_id;
}

/**
 * Get products from database
 *
 * @param array $args
 *
 * @return array|null|object
 */
function aawp_get_products( $args = array() ) {

    $products = aawp()->products->get_products( $args );

    return $products;
}

/**
 * Get single product from database by id
 *
 * @param int $product_id
 *
 * @return array|null
 */
function aawp_get_product( $product_id ) {

    if ( empty( $product_id ) )
        return null;

    $product = aawp()->products->get_product_by( 'id', $product_id );

    return $product;
}

/**
 * Get single product from database by asin
 *
 * @param int $product_asin
 *
 * @return array|null
 */
function aawp_get_product_by_asin( $product_asin ) {

    if ( empty( $product_asin ) )
        return null;

    $product = aawp()->products->get_product_by( 'asin', $product_asin );

    return $product;
}

/**
 * Update product in database
 *
 * @param $product_id
 * @param $data
 *
 * @return bool
 */
function aawp_update_product( $product_id, $data ) {

    if ( empty( $product_id ) || empty( $data ) )
        return false;

    $updated = aawp()->products->update( $product_id, $data );

    return $updated;
}

/**
 * Update product rating in database
 *
 * @param $product_id
 * @param $data
 *
 * @return bool
 */
function aawp_update_product_reviews( $product_id, $data ) {

    if ( empty( $product_id ) || empty( $data ) )
        return false;

    $updated = aawp()->products->update_reviews( $product_id, $data );

    return $updated;
}

/**
 * Renew a bunch of products
 *
 * @param $products
 * @param array $args
 *
 * @return int
 */
function aawp_renew_products( $products, $args = array() ) {

    if ( empty( $products ) || ! is_array( $products ) )
        return 0;

    $renewed = 0;

    $product_asin_ids = array();
    $product_asins    = array();

    // Storing asin to id relation in order to re-use it later
    foreach ( $products as $product_index => $product ) {

        if ( ! empty( $product['asin'] ) ) {
            $product_asin_ids[ $product['asin'] ] = $product_index;
            $product_asins[]                      = $product['asin'];
        }
    }

    // Using chunks in order not to lose updated products due to php execution timeouts
    $chunks = array_chunk( $product_asins, 10 );

    aawp_debug_display( $product_asin_ids, 'aawp_renew_products: $product_asin_ids' );
    aawp_debug_display( $chunks, 'aawp_renew_products: $chunks' );

    // Preparing API
    $default_product_args = array(
        // Silence
    );

    // Parse args
    $product_args = wp_parse_args( $args, $default_product_args );

    // Fetch products from API
    foreach ( $chunks as $i => $chunk ) {

        $products_api = aawp()->api->get_products( $chunk, $product_args );

        //aawp_debug_display( $products_api, '$products_api' );

        // Chunk could be fetched from API
        if ( is_array( $products_api ) ) {

	        aawp_debug_display( sizeof( $products_api ), 'Products fetched via API' );

            foreach ( $products_api as $product_api ) {

                /** Flowdee\AmazonPAAPI5WP\Item $product_api */
                if ( ! is_object( $product_api ) || ! method_exists( $product_api, 'getASIN' ) )
                    continue;

                $product_api_asin = $product_api->getASIN();

                if ( empty( $product_api_asin ) || ! isset ( $product_asin_ids[ $product_api_asin ] ) || ! isset ( $products[ $product_asin_ids[ $product_api_asin ] ] ) ) {
                    //aawp_debug_display( '', 'ASIN / ID not found<br>' );
                    continue;
                }

                $product_db = $products[ $product_asin_ids[ $product_api_asin ] ];

                aawp_debug_display( $product_api, '$product_api' );

                if ( empty( $product_db['id'] ) )
                    continue;

                aawp_debug_display( $product_db['id'], '$product_db[id]' );

                $updated = aawp_update_product( $product_db['id'], $product_api );

                if ( $updated )
                    $renewed++;
            }

        // Chunk could not be fetched, trying again each asin
        } else {

            sleep( 1 );

            aawp_debug_display( $chunk, 'Chunk could not be fetched' );
            //echo 'reason: '; var_dump( $products_api ); echo '<br>';

            foreach ( $chunk as $chunk_asin ) {

                /** Flowdee\AmazonPAAPI5WP\Item $product_api */
                $product_api = aawp()->api->get_product( $chunk_asin, $product_args );

                if ( ! is_object( $product_api ) || ! method_exists( $product_api, 'getASIN' ) )
                    continue;

                $product_api_asin = $product_api->getASIN();

                if ( empty( $product_api_asin ) || ! isset ( $product_asin_ids[ $product_api_asin ] ) || ! isset ( $products[ $product_asin_ids[ $product_api_asin ] ] ) ) {
                    //aawp_debug_display( '', 'ASIN / ID not found<br>' );
                    continue;
                }

                $product_db = $products[ $product_asin_ids[ $product_api_asin ] ];

                if ( empty( $product_db['id'] ) )
                    continue;

                $updated = aawp_update_product( $product_db['id'], $product_api );

                if ( $updated )
                    $renewed++;
            }
        }
    }

    return $renewed;
}

/**
 * Merge renewed product data
 *
 * (Prevent losing reviews/ratings etc.)
 *
 * @param $data_new
 * @param $data_old
 *
 * @return mixed
 */
function aawp_merge_renewed_product_data( $data_new, $data_old ) {

    $data = array_merge( $data_old, $data_new );

    if ( empty( $data['rating'] ) && ! empty( $data_old['rating'] ) ) {
        //echo 'ASIN: ' . $data['asin'] . ' >> fallback for missing rating<br>';
        $data['rating'] = $data_old['rating'];
    }

    if ( empty( $data['reviews'] ) && ! empty( $data_old['reviews'] ) ) {
        //echo 'ASIN: ' . $data['asin'] . ' >> fallback for missing reviews<br>';
        $data['reviews'] = $data_old['reviews'];
    }

    return $data;
}

/**
 * Renew ratings for multiple products
 *
 * @param $products
 *
 * @return int|null
 */
function aawp_renew_product_reviews( $products ) {

    if ( empty( $products ) || ! is_array( $products ) )
        return null;

    $renewed = 0;

    $product_asin_ids = array();
    $product_asins = array();

    // Storing asin to id relation in order to re-use it later
    foreach ( $products as $product_index => $product ) {

        if ( ! empty( $product['asin'] ) ) {
            $product_asin_ids[ $product['asin'] ] = $product_index;
            $product_asins[]                      = $product['asin'];
        }
    }

    // Build chunks
    $chunks = array_chunk( $product_asins, 10 );

    aawp_debug_display( $product_asin_ids, '$product_asin_ids' );
    aawp_debug_display( $product_asins, '$product_asins' );
    aawp_debug_display( $chunks, '$chunks' );

    // Prepare crawler
    $AAWP_Review_Crawler = new AAWP_Review_Crawler();

    // Loop chunks
    foreach ( $chunks as $i => $chunk ) {

        // Loop products
        foreach ( $chunk as $asin ) {

            $review_data = $AAWP_Review_Crawler->get_data( $asin );

            /*
            echo '<strong>Crawled ratings for ASIN ' . $asin . ':';
            var_dump( $review_data );
            echo '<br>';
            */

            if ( ! isset( $product_asin_ids[$asin] ) || ! isset( $products[ $product_asin_ids[ $asin ] ] ) )
                continue;

            $product = $products[ $product_asin_ids[ $asin ] ];

            if ( empty( $product['id'] ) )
                continue;

            if ( ! empty( $review_data['rating'] ) && ! empty( $review_data['reviews'] ) ) {

                $updated = aawp_update_product_reviews( $product['id'], $review_data );

                if ( $updated ) {
                    $renewed++;
                }
            }
        }

        sleep(1); // Pause after crawling a chunk of asins
    }

    return $renewed;
}

/**
 * Renew ratings for a single product
 *
 * @param $product_id
 *
 * @return bool|null
 */
function aawp_renew_product_rating( $product_id ) { // TODO: Deprecated

    if ( empty( $product_id ) || ! is_numeric( $product_id ) )
        return null;

    // TODO: Rebuild

    return false;
}

/**
 * Get total amount of stored products
 *
 * @return int
 */
function aawp_get_products_count() {

    $count = aawp()->products->count();

    return ( is_numeric( $count ) ) ? intval( $count ) : 0;
}

/**
 * Update product status by ASIN
 *
 * @param $asin
 * @param $new_status
 */
function aawp_update_product_status_by_asin( $asin, $new_status ) {

    $product = aawp_get_product_by_asin( $asin );

    if ( ! empty( $product['id'] ) ) {

        $product['status'] = $new_status;

        $updated = aawp_update_product( $product['id'], $product );
    }
}