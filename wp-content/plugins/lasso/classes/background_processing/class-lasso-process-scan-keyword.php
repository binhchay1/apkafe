<?php
/**
 * Declare class Lasso_Process_Scan_Keyword
 *
 * @package Lasso_Process_Scan_Keyword
 */

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Keyword as Lasso_Keyword;

/**
 * Lasso_Process_Scan_Keyword
 */
class Lasso_Process_Scan_Keyword extends Lasso_Process {
	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_scan_keyword_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'scan_keywords';

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
		if ( ! $index ) {
			return false;
		}

		$start_time = microtime( true );
		Lasso_Helper::write_log( 'Link database at index: ' . $index, $this->log_name );

		Lasso_Keyword::scan_keywords_in_post_page( $index );

		Lasso_Helper::write_log( 'Post id: ' . $index . ' | Post title: ' . get_the_title( $index ), $this->log_name );

		Lasso_Helper::write_log( 'Link database takes: ' . ( microtime( true ) - $start_time ), $this->log_name );
		Lasso_Helper::write_log( 'Link database at index: ' . $index . ' - End', $this->log_name );

		return false;
	}

	/**
	 * Prepare data for process
	 *
	 * @param array $post_ids List of post ids. Default to empty array.
	 */
	public function scan( $post_ids = array() ) {
		// ? check whether process is age out and make it can work on Lasso UI via ajax requests
		$this->is_process_age_out();

		if ( $this->is_process_running_reach_the_limit() ) {
			$this->push_to_lasso_processes_queue( __CLASS__, __FUNCTION__, func_get_args() );
			return false;
		}

		if ( $this->is_process_running() ) {
			return false;
		}

		$count = count( $post_ids );

		if ( $count <= 0 ) {
			return false;
		}

		foreach ( $post_ids as $post_id ) {
			if ( empty( $post_id ) ) {
				continue;
			}
			$this->push_to_queue( $post_id );
		}

		$this->set_total( $count );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();
		// ? save queue
		$this->save()->dispatch();

		return true;
	}
}
new Lasso_Process_Scan_Keyword();
