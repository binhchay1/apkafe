<?php
/**
 * Update cache job
 *
 * @package     AAWP\jobs
 * @since       3.2.0
 */

require_once( "../../../../../wp-load.php" );

// Validate key
$cronjob_key = get_option( 'aawp_cronjob_key', null );

if ( ! isset( $_GET['key'] ) || $_GET['key'] != $cronjob_key )
    return;

/*
 * Execute update cache event and passing validation
 */
aawp_execute_renew_cache( $force_renewals = true );