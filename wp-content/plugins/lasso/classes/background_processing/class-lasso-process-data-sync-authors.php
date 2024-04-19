<?php
/**
 * Declare class Lasso_Process_Data_Sync_Authors
 *
 * @package Lasso_Process_Data_Sync_Authors
 */

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Lasso_Process_Data_Sync_Authors
 */
class Lasso_Process_Data_Sync_Authors extends Lasso_Process {
	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_data_sync_authors';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'process_sync_authors';

	/**
	 * Lasso_Process constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'lasso_all_processes', array( $this, 'lasso_all_processes' ), 20, 1 );
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $page Queue item to iterate over.
	 *
	 * @return mixed
	 */
	public function task( $page ) {
		$start_time      = microtime( true );
		$schema          = get_option( $this->log_name . '_schema', array() );
		$submission_type = get_option( $this->log_name . '_submission_type', 'full' );
		Lasso_Helper::write_log( 'Sync Authors page: ' . $page, $this->log_name );
		Lasso_Helper::write_log( 'Sync Authors type: ' . $submission_type, $this->log_name );

		$lasso_sync_authors = new Lasso_Data_Sync_Authors();
		$lasso_sync_authors->set_submission_type( $submission_type );
		$lasso_sync_authors->send_data( $page, $schema );

		Lasso_Helper::write_log( 'Sync Authors type: ' . $lasso_sync_authors->get_submission_type(), $this->log_name );
		Lasso_Helper::write_log( 'It took: ' . ( microtime( true ) - $start_time ), $this->log_name );

		return false;
	}

	/**
	 * Prepare data for process
	 *
	 * @param string $type Data sync type (diff, full). Default to 'diff'.
	 */
	public function sync_authors( $type = 'diff' ) {
		// ? check whether process is age out and make it can work on Lasso UI via ajax requests
		$this->is_process_age_out();

		if ( $this->is_process_running() ) {
			return false;
		}

		$lasso_sync_authors    = new Lasso_Data_Sync_Authors();
		$lasso_submission_date = get_option( 'lasso_submission_date_' . Lasso_Data_Sync_Authors::DWH_TABLE_NAME, '' );

		if ( '' === $lasso_submission_date ) {
			$type = 'full';
		}
		$lasso_sync_authors->set_submission_type( $type );

		$pages = $lasso_sync_authors->get_total_pages();
		Lasso_Helper::write_log( 'Sync authors total pages: ' . $pages, $this->log_name );

		if ( 0 === $pages ) {
			return false;
		}

		for ( $i = 1; $i <= $pages; $i++ ) {
			$this->push_to_queue( $i );
		}

		update_option( $this->log_name . '_schema', $lasso_sync_authors->get_schema() );
		update_option( $this->log_name . '_submission_type', $type );

		$this->set_total( $pages );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();

		$this->save()->dispatch();

		return true;
	}

	/**
	 * Add this processes to Lasso processes list for running manually
	 *
	 * @param array $lasso_processes Lasso Processes.
	 * @return array
	 */
	public function lasso_all_processes( $lasso_processes ) {
		// ? We only add this process to UI if cron is getting the issue
		if ( Lasso_Helper::is_cron_getting_issues() ) {
			$processes = array(
				'Lasso_Process_Data_Sync_Authors' => 'Syncing authors data',
			);

			return array_merge( $lasso_processes, $processes );
		}

		return $lasso_processes;
	}
}
new Lasso_Process_Data_Sync_Authors();
