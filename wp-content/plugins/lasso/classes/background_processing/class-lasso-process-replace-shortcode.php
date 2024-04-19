<?php
/**
 * Declare class Lasso_Process_Check_Issue
 * Scan lasso link in posts/pages
 *
 * @package Lasso_Process_Check_Issue
 */

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Lasso_Process_Check_Issue
 */
class Lasso_Process_Replace_Shortcode extends Lasso_Process {
	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_replace_shortcode_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'process_replace_shortcode';

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

		global $post;

		$post = get_post( $index ); // phpcs:ignore
		setup_postdata( $post );

		$lasso_cron = new Lasso_Cron();
		$lasso_cron->check_all_posts_pages( array( $post ) );

		$time_end       = microtime( true );
		$execution_time = round( $time_end - $start_time, 2 );
		Lasso_Helper::write_log( 'Replace shortcode issue takes: ' . $execution_time, $this->log_name );
		Lasso_Helper::write_log( 'END status check for: ' . $index . ' - End', $this->log_name );

		return false;
	}

	/**
	 * Prepare data for process
	 *
	 * @param int    $lasso_id Lasso post id.
	 * @param string $type     Shortcode type.
	 */
	public function replace_shortcode( $lasso_id, $type = '' ) {
		// ? check whether process is age out and make it can work on Lasso UI via ajax requests
		$this->is_process_age_out();

		$lasso_db = new Lasso_DB();

		$all_links   = $lasso_db->get_post_to_replace_shortcode( $lasso_id, $type );
		$total_count = count( $all_links );

		if ( $total_count <= 0 ) {
			return false;
		}

		foreach ( $all_links as $id ) {
			$this->push_to_queue( $id );
		}

		$this->set_total( $total_count );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();
		// ? save queue
		$this->save()->dispatch();

		return true;
	}
}
new Lasso_Process_Replace_Shortcode();
