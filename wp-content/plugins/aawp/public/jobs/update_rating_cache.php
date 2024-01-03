<?php
/**
 * Update rating cache job
 *
 * @package     AAWP\jobs
 * @since       3.3.2
 */

require_once( "../../../../../wp-load.php" );

// Validate key
$cronjob_key = get_option( 'aawp_cronjob_key', null );

if ( ! isset( $_GET['key'] ) || $_GET['key'] != $cronjob_key )
    return;

/*
 * Execute update rating cache event
 */
aawp_renew_rating_cache_event();