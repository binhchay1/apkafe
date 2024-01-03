<?php
/**
 * Installation
 *
 * @since       3.3.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

function aawp_run_install() {

    /**
     * Create database tables
     */
    $AAWP_DB_Products = new AAWP_DB_Products();

    if ( ! $AAWP_DB_Products->installed() )
        $AAWP_DB_Products->create_table();

    $AAWP_DB_Lists = new AAWP_DB_Lists();

    if ( ! $AAWP_DB_Lists->installed() )
        $AAWP_DB_Lists->create_table();

    /**
     * Register scheduled events
     */
    if ( ! wp_next_scheduled ( 'aawp_wp_scheduled_events' ) )
        wp_schedule_event( time(), 'aawp_continuously', 'aawp_wp_scheduled_events' );

    if ( ! wp_next_scheduled ( 'aawp_wp_scheduled_daily_events' ) )
        wp_schedule_event( time(), 'daily', 'aawp_wp_scheduled_daily_events' );

    if ( ! wp_next_scheduled ( 'aawp_wp_scheduled_weekly_events' ) )
        wp_schedule_event( time(), 'aawp_weekly', 'aawp_wp_scheduled_weekly_events' );
}