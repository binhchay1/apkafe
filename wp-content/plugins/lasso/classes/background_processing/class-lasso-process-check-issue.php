<?php
/**
 * Declare class Lasso_Process_Check_Issue
 * Scan lasso link in posts/pages
 *
 * @package Lasso_Process_Check_Issue
 */

use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Models\Model;

/**
 * Lasso_Process_Check_Issue
 */
class Lasso_Process_Check_Issue extends Lasso_Process {
	const LIMIT       = 100;
	const OPTION_PAGE = 'lasso_check_issue_page';
	const OPTION_DATE = 'lasso_check_issue_date';

	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_check_issue_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'url_issue_check';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $index Queue item to iterate over.
	 *
	 * @return mixed
	 */
	public function task( $index ) {
		if ( empty( $index ) ) {
			return false;
		}

		$start_time = microtime( true );
		Lasso_Helper::write_log( 'START status check for: ' . $index, $this->log_name );

		$cron = new Lasso_Cron();
		$cron->check_issues( $index );

		$time_end       = microtime( true );
		$execution_time = round( $time_end - $start_time, 2 );
		Lasso_Helper::write_log( 'Check issue takes: ' . $execution_time, $this->log_name );
		Lasso_Helper::write_log( 'END status check for: ' . $index . ' - End', $this->log_name );

		return false;
	}

	/**
	 * Prepare data for process
	 */
	public function check_issue() {
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

		$today      = gmdate( 'Y-m-d' );
		$check_date = get_option( self::OPTION_DATE, '' );

		$page = get_option( self::OPTION_PAGE, '1' );
		$page = intval( $page );

		// ? check issue daily
		if ( 1 === $page && $today === $check_date ) {
			return false;
		}

		$sql         = $lasso_db->check_issue_queue_query();
		$sql         = $lasso_db->paginate( $sql, $page, self::LIMIT );
		$all_links   = Model::get_results( $sql );
		$total_count = count( $all_links );

		if ( $total_count <= 0 ) {
			update_option( self::OPTION_PAGE, '1' );
			return false;
		}
		update_option( self::OPTION_PAGE, strval( $page + 1 ) ); // ? increase page
		update_option( self::OPTION_DATE, $today ); // ? set check date is today

		foreach ( $all_links as $link ) {
			if ( empty( $link->id ) ) {
				continue; // ? should not push 0/null/empty
			}
			$this->push_to_queue( $link->id );
		}

		$this->set_total( $total_count );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();
		// ? save queue
		$this->save()->dispatch();

		return true;
	}
}
new Lasso_Process_Check_Issue();
