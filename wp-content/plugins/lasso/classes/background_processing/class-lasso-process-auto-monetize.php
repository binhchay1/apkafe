<?php
/**
 * Declare class Lasso_Process_Auto_Monetize
 *
 * @package Lasso_Process_Auto_Monetize
 */

use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Models\Auto_Monetize;

/**
 * Lasso_Process_Auto_Monetize
 */
class Lasso_Process_Auto_Monetize extends Lasso_Process {
	const LIMIT = 100;

	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_auto_monetize';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'auto_monetize';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $data Queue item to iterate over.
	 *
	 * @return mixed
	 */
	public function task( $data ) {
		$start_time = microtime( true );

		Lasso_Helper::write_log( 'START Import process for: ' . strval( $data ), $this->log_name );

		$auto_monetize = new Auto_Monetize();
		$row           = $auto_monetize->get_one_by_col( 'url', $data );

		if ( ! $row ) {
			return false;
		}

		$lasso    = new Lasso_Affiliate_Link();
		$lasso_id = $lasso->lasso_add_a_new_link( $data, array( 'link_cloaking' => '0' ) ); // ? Does not change the original URL on post content.

		if ( $lasso_id > 0 ) {
			$lasso_url = $lasso->get_lasso_url( $lasso_id );

			if ( ! $auto_monetize->get_deep_link( $data ) ) {
				$auto_monetize->set_deep_link( $lasso_url->target_url );
				$auto_monetize->set_domain( Lasso_Helper::get_base_domain( $lasso_url->target_url ) );
			}

			$auto_monetize->set_lasso_id( $lasso_id );
			$auto_monetize->update();

			Lasso_Helper::write_log( 'Imported id: ' . $lasso_id, $this->log_name );
		}

		$time_end       = microtime( true );
		$execution_time = round( $time_end - $start_time, 2 );
		Lasso_Helper::write_log( 'Completed Import process - End, total process time ' . $execution_time, $this->log_name );

		return false;
	}

	/**
	 * Prepare data for process
	 */
	public function run() {
		// ? check whether process is age out and make it can work on Lasso UI via ajax requests
		$this->is_process_age_out();

		if ( $this->is_process_running() ) {
			return false;
		}

		// ? get all amazon_product that are not matching with lasso url
		$urls  = Auto_Monetize::get_all_except_lasso( self::LIMIT );
		$count = count( $urls );

		Lasso_Helper::write_log( 'Total: ' . $count, $this->log_name );

		if ( $count <= 0 ) {
			return false;
		}

		foreach ( $urls as $url ) {
			if ( ! Lasso_Helper::validate_url( $url ) ) {
				continue;
			}

			$this->push_to_queue( $url );
		}

		$this->set_total( $count );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();
		// ? save queue
		$this->save()->dispatch();

		return true;
	}
}

new Lasso_Process_Auto_Monetize();
