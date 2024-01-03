<?php
/**
 * Items
 *
 * @package     AAWP\Functions\Components
 * @since       3.2.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Register cronjobs component
 */
function aawp_settings_register_items_component( $functions ) {

    $functions[] = 'items';

    return $functions;
}
add_filter( 'aawp_settings_functions', 'aawp_settings_register_items_component' );

/**
 * Extending supported shortcode attributes
 */
function aawp_extend_supported_attributes_for_items_manipulation( $supported, $type ) {

    // Ordering & Filtering
    if ( $type == 'box' || $type == 'bestseller' || $type == 'new_releases' ) {
        array_push( $supported, 'orderby', 'order', 'order_items', 'filterby', 'filter', 'filter_type', 'filter_items', 'filter_compare' );
    }

    // Selecting
    if ( $type == 'bestseller' || $type == 'new_releases' ) {
        array_push( $supported, 'start', 'end', 'select' );
    }

    return $supported;
}
add_filter( 'aawp_func_supported_attributes', 'aawp_extend_supported_attributes_for_items_manipulation', 10, 2 );

/**
 * Extend list keys
 */
function aawp_items_extend_list_key( $list_key, $type, $args, $atts ) {

    // Order items
    if ( isset ( $atts['order_items'] ) && ( 'bestseller' === $type || 'new_releases' === $type ) )
        $list_key['order_items'] = intval ( $atts['order_items'] );

    // Filter items
    if ( isset ( $atts['filter_items'] ) && ( 'bestseller' === $type || 'new_releases' === $type ) )
        $list_key['filter_items'] = intval ( $atts['filter_items'] );

    // Return key
    return $list_key;
}
//add_filter( 'aawp_caching_key', 'aawp_items_extend_list_key', 10, 4 ); // TODO: Deprecated

/**
 * Maybe update query items when needing more products for ordering/filtering
 *
 * @param $query_items
 * @param $atts
 *
 * @return int
 */
function aawp_items_update_list_query_items( $query_items, $atts ) {

    // Order
    if ( isset ( $atts['order_items'] ) && is_numeric( $atts['order_items'] ) )
        return intval( $atts['order_items'] );

    // Filter
    if ( isset ( $atts['filter_items'] ) && is_numeric( $atts['filter_items'] ) )
        return intval ( $atts['filter_items'] );

    return $query_items;
}
add_filter( 'aawp_list_query_items', 'aawp_items_update_list_query_items', 10, 2 );

/**
 * Order items
 */
function aawp_order_items( $items, $type, $atts ) {

    $debug_ordering = false;

    if ( ! isset( $atts['orderby'] ) )
        return $items;

    // Default options
    $order_values = array( 'ASC', 'DESC' );
    $orderby_values = array( 'title', 'price', 'amount_saved', 'percentage_saved', 'offer', 'rating', 'reviews' );

    $order = ( isset( $atts['order'] ) && in_array( strtoupper( $atts['order'] ), $order_values ) ) ? strtoupper( $atts['order'] ) : 'DESC';
    $orderby = ( in_array( strtolower( $atts['orderby'] ), $orderby_values ) ) ? strtolower( $atts['orderby'] ) : false;

    if ( ! $orderby )
        return $items;

    //aawp_debug( $items );

    // Reorder
    $order_items = array();
    $reordered_items = array();

	// DEV
	/*
    echo 'Original orderby value: ';
    var_dump($atts['orderby']);
    echo '<br>';
    echo 'Original order value: ';
    var_dump($atts['order']);
    echo '<br>';
    echo 'Items available: ' . sizeof( $items ) . '<br>';
    echo 'Items max: ' . $max . '<br>';
    echo 'Order by: ' . $orderby . '<br>';
    echo 'Order: ';
    var_dump( $order );
    echo '<br>';
    echo 'Order items: ';
    var_dump($order_items);
    echo '<hr>';
	*/

    //aawp_debug( $items, '$items: pre' );

    // Case: Title
    if ( 'title' === $orderby ) {

        foreach ( $items as $item_id => $item ) {
            $AAWP_Product = new AAWP_Product( $item );
            $order_items[$item_id] = $AAWP_Product->get_title();
        }

        //aawp_debug( $order_items, '$order_items: pre' );

        uasort( $order_items, function($a, $b) use ( $order, $orderby) {
            return ( 'ASC' === $order ) ? strcmp( $a, $b ) : strcmp( $b, $a );
        });

    // Case: Pricing
    } elseif ( 'price' === $orderby || 'amount_saved' === $orderby || 'percentage_saved' === $orderby || 'offer' === $orderby ) {

        foreach ( $items as $item_id => $item ) {

            $AAWP_Product = new AAWP_Product( $item );

            $value = 0;

            // Display price
            if ( 'price' === $orderby ) {
                $value = $AAWP_Product->get_price( 'display', false );

            // Percentage saved
            } elseif ( 'percentage_saved' === $orderby || 'offer' === $orderby ) {
                $value = $AAWP_Product->get_price_savings_percentage( false );

            // Amount saved
            } elseif ( 'amount_saved' === $orderby ) {
                $value = $AAWP_Product->get_price_savings( false );
            }

            // Normalize numbers
            $value = number_format( $value, 2 );
            $value = str_replace('.', '', $value );

            // Adding value array
            $order_items[$item_id] = $value;
        }

        //aawp_debug( $order_items, '$order_items: pre' );

        uasort( $order_items, function($a, $b) use ( $order, $orderby) {

            // Return according to order
            if ( 'ASC' === $order ) {
                return $a - $b;
            } else {
                return $b - $a;
            }

        });

    // Case: Ratings
    } elseif ( 'rating' === $orderby || 'reviews' === $orderby ) {

        foreach ( $items as $item_id => $item ) {

            $AAWP_Product = new AAWP_Product( $item );

            if ( 'rating' === $orderby ) {
                $value = $AAWP_Product->get_rating();
            } elseif ( 'reviews' === $orderby ) {
                $value = $AAWP_Product->get_reviews();
            }

            $value = ( ! empty( $value ) ) ? preg_replace( '/[^0-9]/', '', $value ) : 0;

            $order_items[$item_id] = $value;
        }

        //aawp_debug( $order_items, '$order_items: pre' );

        uasort( $order_items, function($a, $b) use ( $order, $orderby) {
            return ( 'ASC' === $order ) ? $a - $b : $b - $a;
        });

    }

    /*
     * Completion
     *
     * 1. Check if items were re-ordered and maybe replace origin ones
     * 2. Handle max items
     */
    if ( sizeof( $order_items ) > 0 ) {

        //aawp_debug( $order_items, '$order_items: after' );

        foreach ( $order_items as $item_key => $order_item ) {
            $reordered_items[] = $items[$item_key];
        }

        //$reordered_items = array_keys( $order_items );
        //aawp_debug( $reordered_items, '$reordered_items' );

        if ( $items !== $reordered_items )
            $items = $reordered_items;
    }

    // Finally return maybe re-ordered items
    return $items;
}
add_filter( 'aawp_items', 'aawp_order_items', 20, 3 );

/**
 * Select items from X to Y
 */
function aawp_select_items( $items, $type, $atts ) {

    if ( ! isset( $atts['start'] ) && ! isset( $atts['end'] ) && ! isset( $atts['select'] ) )
        return $items;

    // Defaults
    $amount = sizeof( $items );
    $start = false;
    $end = false;

    // Select defined
    if ( isset( $atts['select'] ) ) {

        $select = aawp_get_items_start_end_from_select( $atts['select'], $amount );

        if ( isset( $select['start'] ) && is_numeric( $select['start'] ) )
            $start = $select['start'];

        if ( isset( $select['end'] ) && is_numeric( $select['end'] ) )
            $end = $select['end'];

    // Start + End defined
    } else {
        $start = ( isset ( $atts['start'] ) && '0' != $atts['start'] ) ? intval( $atts['start'] ) : 1;
        $end = ( isset ( $atts['end'] ) && $atts['end'] <= $amount ) ? intval( $atts['end'] ) : $amount;
    }

    if ( ! $start || ! $end )
        return $items;

    // Build finale item range
    $range = range($start, $end);

    /*
    // Debug
    echo 'Amount: ';
    var_dump($amount);
    echo '<br>';
    echo 'Start: ';
    var_dump($start);
    echo '<br>';
    echo 'End: ';
    var_dump($end);
    echo '<br>';
    echo 'Range: ';
    var_dump($range);
    echo '<br>';
    */

    // Maybe manipulate final items
    if ( $amount != sizeof( $range ) ) {

        // Counter
        $i = 1;

        // Loop items and kick unneeded
        foreach ( $items as $key => $item ) {
            if ( ! in_array( $i, $range ) )
                unset( $items[ $key ] );

            // Increment counter
            $i++;
        }
    }

    // Return
    return $items;
}
add_filter( 'aawp_items', 'aawp_select_items', 30, 3 );

/**
 * Get start/end index from select
 */
function aawp_get_items_start_end_from_select( $select, $amount = 10 ) {

    // Range
    if ( strpos( $select, '-') !== false ) {

        $select = explode( '-', $select );
        $start = ( isset ( $select[0] ) && '0' != $select[0] ) ? intval( $select[0] ) : 1;
        $end = ( isset ( $select[1] ) && $select[1] <= $amount ) ? intval( $select[1] ) : $amount;

        // Single item
    } else {
        $start = $end = intval( $select );
    }

    return array('start' => $start, 'end' => $end );
}

/**
 * Update items start index when using ranges
 */
function aawp_update_items_start_index( $index, $type, $atts ) {

    if ( isset ( $atts['start'] ) && '0' != $atts['start'] )
        $index = intval( $atts['start'] ) - 1; // Reduce 1 by default because setup_item increases every loop

    if ( isset( $atts['select'] ) ) {
        $amount = ( isset( $atts['items'] ) ) ? : 10;
        $select = aawp_get_items_start_end_from_select( $atts['select'], $amount );

        if ( isset( $select['start'] ) && is_numeric( $select['start'] ) )
            $index = intval( $select['start'] ) - 1;
    }

    return $index;
}
add_filter( 'aawp_items_start_index', 'aawp_update_items_start_index', 10, 3 );

/**
 * Filter items by several conditions
 */
function aawp_filter_items( $items, $type, $atts ) {

    if ( empty( $atts['filterby'] ) || empty( $atts['filter'] ) )
        return $items;

    // Default options
    $filter_drops = array();
    $filterby_values = array( 'title', 'price' );

	$filter = trim( $atts['filter'] );
	$filter = str_replace( array( ' ,', ', ', ' , ' ) , ',', $filter );
    $filter = ( strpos( $filter, ',' ) !== false ) ? explode(',', $filter ) : array( $filter ); // Multiple or single arguments
    $filterby = ( in_array( strtolower( $atts['filterby'] ), $filterby_values ) ) ? strtolower( $atts['filterby'] ) : false;
    $filter_type = ( isset( $atts['filter_type'] ) && $atts['filter_type'] === 'exclude' ) ? 'exclude' : 'include';
    $filter_items = ( isset( $atts['filter_items'] ) && is_numeric( $atts['filter_items'] ) ) ? $atts['filter_items'] : false;
    $filter_compare = ( isset ( $atts['filter_compare'] ) ) ? esc_html ( $atts['filter_compare'] ) : '=';

    // Debug
    /*
    echo 'Original filterby value: ';
    var_dump($atts['filterby']);
    echo '<br>';
    echo 'Original filter value: ';
    var_dump($atts['filter']);
    echo '<br>';
    echo 'Items available: ' . sizeof( $items ) . '<br>';
    echo 'Items max: ' . $atts['items'] . '<br>';
    echo 'Filter by: ' . $filterby . '<br>';
    echo 'Filter: ';
    var_dump( $filter );
    echo '<br>';
    echo 'Filter type: ' . $filter_type . '<br>';
    echo 'Filter compare: ' . $filter_compare . '<br>';
    echo 'Filter items: ';
    var_dump($filter_items);
    echo '<hr>';
    */

    if ( ! empty ( $filter[0] ) ) {

        foreach ( $items as $item_id => $item ) {

            $AAWP_Product = new AAWP_Product( $item );

            $unset = false;

            // Title
            if ( 'title' === $filterby ) {

                $item_title = $AAWP_Product->get_title();

                foreach ( $filter as $filter_arg ) {
                    if ( 'include' === $filter_type && strpos( strtolower( $item_title ), strtolower( $filter_arg ) ) === false ) {
                        $unset = true;
                    } elseif ( 'exclude' === $filter_type && strpos( strtolower( $item_title ), strtolower( $filter_arg ) ) !== false ) {
                        $unset = true;
                    }
                }

            // Price: Specials
            } elseif ( 'price' === $filterby && ( in_array( 'offer', $filter ) || in_array( 'available', $filter ) || in_array( 'prime', $filter ) ) ) {

                // Offers only
                if ( in_array( 'offer', $filter ) ) {

                    if ( 0 == $AAWP_Product->get_price_savings_percentage() ) {
                        $unset = true;
                    }
                }

                // Prime only
	            if ( in_array( 'prime', $filter ) ) {

                	if ( ! $AAWP_Product->is_prime() ) {
		                $unset = true;
	                }
	            }

                // Available only
                if ( in_array( 'available', $filter ) ) {

                    if ( 0 == $AAWP_Product->get_price( 'display', false ) || '1' != $AAWP_Product->get_availability() ) {
                        $unset = true;
                    }
                }

            // Price
            } elseif ( 'price' === $filterby ) {

                $price_item = $AAWP_Product->get_price( 'display', false );

                // Ranges
                if ( 'range' === $filter_compare && isset ( $filter[1] ) ) {

                    $price_min_reference = aawp_get_filterable_price( $filter[0] );
                    $price_max_reference = aawp_get_filterable_price( $filter[1] );

                    if ( $price_item < $price_min_reference || $price_item > $price_max_reference ) {
                        $unset = true;
                    }

                // Single comparison
                } else {

                    $price_reference = aawp_get_filterable_price( $filter[0] );

                    //echo '$price_item: ' . $price_item . ' - $price_reference: ' . $price_reference . '<br>';

                    // Compare: Filter GREATER than item price
                    if ( '&gt;' == $filter_compare || '>' == $filter_compare || 'more' == $filter_compare ) {

                        if ( $price_item <= $price_reference ) {
                            $unset = true;
                        }

                        // Compare: Filter LOWER than item price
                    } elseif ( '&lt;' == $filter_compare || '<' == $filter_compare || 'less' == $filter_compare ) {

                        if ( $price_item >= $price_reference ) {
                            $unset = true;
                        }

                        // Compare: Filter IS EQUAL TO item price
                    } elseif ( '=' == $filter_compare || 'equal' == $filter_compare ) {

                        if ( $price_item != $price_reference ) {
                            $unset = true;
                        }
                    }
                }
            }

            //------

            if ( $unset ) {
                unset( $items[ $item_id ] );
            }
        }
    }

	// Reset numeric keys
	$items = array_values( $items );

	// TODO: Optimize cache handling

    // Return filtered result
    return $items;
}
add_filter( 'aawp_items', 'aawp_filter_items', 10, 3 );

/**
 * Maybe manipulate items amount
 *
 * @param $items
 * @param $type
 * @param $atts
 *
 * @return mixed
 */
function aawp_manipulate_items_amount( $items, $type, $atts ) {

	$max = ( ! empty ( $atts['items'] ) && is_numeric( $atts['items'] ) ) ? intval( $atts['items'] ) : false;

    //echo 'aawp_manipulate_items_amount() >> $max: ' . $max . '<br>';

	// Adjust amount of items
	if ( $max && sizeof( $items ) > $max )
		$items = array_slice( $items, 0, $max );

	return $items;
}
add_filter( 'aawp_items', 'aawp_manipulate_items_amount', 99, 3 );

/*
 * Filter items: Get filterable price helper
 */
function aawp_get_filterable_price( $price, $extend = true ) {

    $store = aawp_get_amazon_store();

    //echo '<p>Pre Price Filter: ' . $price . ' - ';

    $non_decimal_countries = array( 'in', 'co.jp' );

    // Add 00 if price comes from shortcode and has no decimals
    //if ( $extend && ! in_array( $store, $non_decimal_countries ) )
        //$price = ( strpos( $price, '.') !== false ) ? $price : $price . '00';

    // Strip html entities
    //$price = strip_tags( $price ); // TODO: Remove special chars
    $price = floatval( $price );

    //echo 'After Price Filter: ' . $price . '</p>';

    return $price;
}

