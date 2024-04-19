<?php
/**
 * Declare class Lasso_Process_Data_Sync_Content
 *
 * @package Lasso_Process_Data_Sync_Content
 */

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Lasso_Process_Data_Sync_Content
 */
class Lasso_Process_Data_Sync_Content extends Lasso_Process {
	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_data_sync_content_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'process_sync_content';

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
		$start_time      = microtime( true );
		$schema          = get_option( $this->log_name . '_schema', array() );
		$submission_type = get_option( $this->log_name . '_submission_type', 'full' );
		Lasso_Helper::write_log( 'Sync content page: ' . $data, $this->log_name );
		Lasso_Helper::write_log( 'Sync content type: ' . $submission_type, $this->log_name );

		$lasso_sync_content = new Lasso_Data_Sync_Content();
		$lasso_sync_content->set_submission_type( $submission_type );
		$lasso_sync_content->send_data( $data, $schema );

		Lasso_Helper::write_log( 'Sync content type: ' . $lasso_sync_content->get_submission_type(), $this->log_name );
		Lasso_Helper::write_log( 'It took: ' . ( microtime( true ) - $start_time ), $this->log_name );

		return false;
	}

	/**
	 * Prepare data for process
	 *
	 * @param string $type Data sync type (diff, full). Default to 'diff'.
	 */
	public function sync_content( $type = 'diff' ) {
		// ? check whether process is age out and make it can work on Lasso UI via ajax requests
		$this->is_process_age_out();

		if ( $this->is_process_running() ) {
			return false;
		}

		$lasso_sync_content    = new Lasso_Data_Sync_Content();
		$lasso_submission_date = get_option( 'lasso_submission_date_' . $lasso_sync_content->get_table(), '' );
		$pages                 = 0;

		if ( '' === $lasso_submission_date ) {
			$type = 'full';
		}
		$lasso_sync_content->set_submission_type( $type );

		$pages = $lasso_sync_content->get_total_pages();
		Lasso_Helper::write_log( 'Sync content total pages: ' . $pages, $this->log_name );

		if ( 0 === $pages ) {
			return false;
		}

		for ( $i = 1; $i <= $pages; $i++ ) {
			$this->push_to_queue( $i );
		}

		update_option( $this->log_name . '_schema', $lasso_sync_content->get_schema(), false );
		update_option( $this->log_name . '_submission_type', $type, false );

		$this->set_total( $pages );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();

		$this->save()->dispatch();

		return true;
	}
}
new Lasso_Process_Data_Sync_Content();
