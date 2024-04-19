<?php
/**
 * Declare class Lasso_Process_Scan_Link
 *
 * @package Lasso_Process_Scan_Link
 */

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Lasso_Process_Scan_Link
 */
class Lasso_Process_Scan_Link extends Lasso_Process {
	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_scan_link_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'scan_links';

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
		if ( empty( $data ) ) {
			return false;
		}

		$lasso_id          = $data['lasso_id'] ?? 0;
		$detection_id      = $data['detection_id'] ?? 0;
		$link_location_ids = $data['link_location_ids'] ?? '';
		$link_location_ids = explode( ',', $link_location_ids );

		if ( 0 === count( $link_location_ids ) || 0 === $lasso_id || 0 === $detection_id ) {
			return false;
		}

		Lasso_Helper::write_log( 'Link database at lasso id: ' . $lasso_id, $this->log_name );

		Lasso_Helper::write_log( 'Post id: ' . $detection_id . ' | Post title: ' . get_the_title( $detection_id ), $this->log_name );
		Lasso_Helper::write_log( 'Post link location id: ' . wp_json_encode( $link_location_ids ), $this->log_name );

		$cron = new Lasso_Cron();
		$cron->scan_link_in_post( $lasso_id, $detection_id, $link_location_ids );
		Lasso_Helper::write_log( 'Link database at lasso id: ' . $lasso_id . ' - End', $this->log_name );

		return false;
	}

	/**
	 * Prepare data for process
	 *
	 * @param int $lasso_id Lasso post id.
	 */
	public function scan_post_page( $lasso_id ) {
		// ? check whether process is age out and make it can work on Lasso UI via ajax requests
		$this->is_process_age_out();

		if ( $this->is_process_running_reach_the_limit() ) {
			$this->push_to_lasso_processes_queue( __CLASS__, __FUNCTION__, func_get_args() );
			return false;
		}

		if ( $this->is_process_running() ) {
			return false;
		}

		$lasso_db = new Lasso_DB();

		$datas = $lasso_db->get_post_contains_lasso_id( $lasso_id );
		$count = count( $datas ) ?? 0;

		if ( $count <= 0 || Lasso_Process::are_all_processes_disabled() ) {
			return false;
		}

		foreach ( $datas as $data ) {
			$this->push_to_queue( (array) $data );
		}

		$this->set_total( $count );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();

		$this->save()->dispatch();

		return true;
	}
}
new Lasso_Process_Scan_Link();
