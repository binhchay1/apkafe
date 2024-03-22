<?php
/**
 * Cache Functions
 *
 * @package     AAWP
 * @since       3.4.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get cache duration
 */
function aawp_get_cache_duration() {

    $options_general = aawp_get_options('general');

    return ( isset( $options_general['cache_duration'] ) ) ? intval( $options_general['cache_duration'] ) : 720;
}

/**
 * Set cache last update
 */
function aawp_set_cache_last_update() {
    update_option( 'aawp_cache_last_update', time() );
}

/**
 * Get cache last update
 */
function aawp_get_cache_last_update() {
    return get_option( 'aawp_cache_last_update', null );
}

/**
 * Renew cache
 */
function aawp_renew_cache() {
    aawp_add_log( '*** INITIATED CACHE RENEWAL ***' );
    wp_schedule_single_event( time() + 5, 'aawp_wp_scheduled_single_renew_cache_event' );
}

/**
 * Renew cache event
 */
function aawp_renew_cache_event() {
    aawp_execute_renew_cache();
}

/**
 * Execute renew cache
 *
 * @param bool $force_renewals
 */
function aawp_execute_renew_cache( $force_renewals = false ) {

    $Cache_Handler = new AAWP_Cache_Handler();

    if ( $force_renewals )
        $Cache_Handler->force_renewals();

    $Cache_Handler->renew();
}

/**
 * Renew rating cache event
 */
function aawp_renew_rating_cache_event() {

    if ( ! aawp_is_crawling_reviews_activated() )
        return;

    aawp_execute_renew_rating_cache();
}

/**
 * Execute renew rating cache
 *
 * @param bool $force_renewals
 */
function aawp_execute_renew_rating_cache( $force_renewals = false ) {

    $Cache_Handler = new AAWP_Cache_Handler();

    if ( $force_renewals )
        $Cache_Handler->force_renewals();

    $Cache_Handler->renew_ratings();
}

// TODO: SMART CACHING *****

/**
 * Smart Caching: Activated?
 */
function aawp_smart_caching_activated() {
    $general_options = aawp_get_options( 'general' );
    return ( isset ( $general_options['smart_caching'] ) && '1' == $general_options['smart_caching'] ) ? true : false;
}

/**
 * Renew cache: Add to queue
 */
function aawp_add_renew_cache_queue( $id, $type ) {

    if ( ! aawp_smart_caching_activated() )
        return;

    if ( 'products' === $type ) {
        // TODO
    } elseif ( 'lists' === $type ) {
        // TODO
    }
}

/**
 * Delete our transients cache
 */
function aawp_delete_transients() {

    global $wpdb;

    $wpdb->query( "DELETE FROM `" . $wpdb->prefix . "options` WHERE `option_name` LIKE ('_transient_aawp_%')" );
}