<?php
/**
 * Declare class Deactivator
 *
 * @package Deactivator
 */

namespace Lasso\Classes;

use Lasso_Cron;

/**
 * Deactivator
 */
class Deactivator {
	/**
	 * Run when Lasso is deactivated
	 */
	public function init() {
		$this->lasso_clear_scheduled_hook();

		// ? allow other processes are triggered after removing attribute
		update_option( 'lasso_disable_processes', 0, false );
	}

	/**
	 * Remove WP cron job
	 */
	private function lasso_clear_scheduled_hook() {
		$crons = Lasso_Cron::CRONS;
		foreach ( $crons as $cron_name => $interval ) {
			wp_clear_scheduled_hook( $cron_name );
		}
	}
}
