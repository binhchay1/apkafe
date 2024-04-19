<?php
/**
 * Declare class Lasso_Process_Remove_Attribute
 *
 * @package Lasso_Process_Remove_Attribute
 */

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Lasso_Process_Remove_Attribute
 */
class Lasso_Process_Remove_Attribute extends Lasso_Process {
	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_remove_attributes_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'link_remove_attributes';

	/**
	 * Check different content or not
	 *
	 * @var string $diff
	 */
	public $diff = false;

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
		$lasso_cron = new Lasso_Cron();
		$lasso_db   = new Lasso_DB();

		Lasso_Helper::write_log( 'Remove attribute at index: ' . $index, $this->log_name );
		$lasso_db->remove_non_remove_attribute_process();

		$post_id = $index;
		$lasso_cron->remove_lasso_attributes_in_links( $post_id );

		Lasso_Helper::write_log( 'Post id: ' . $post_id . ' | Post title: ' . get_the_title( $post_id ), $this->log_name );

		$this->set_processing_runtime();
		$time_end       = microtime( true );
		$execution_time = round( $time_end - $start_time, 2 );
		Lasso_Helper::write_log( 'Remove attribute takes: ' . $execution_time, $this->log_name );
		Lasso_Helper::write_log( 'Remove attribute at index: ' . $index . ' - End', $this->log_name );

		return false;
	}

	/**
	 * Prepare data for process
	 */
	public function scan_post_page() {
		// ? check whether process is age out and make it can work on Lasso UI via ajax requests
		$this->is_process_age_out();

		if ( $this->is_process_running() ) {
			return false;
		}

		$lasso_db = new Lasso_DB();

		$datas = $lasso_db->get_all_post_ids_in_link_locations();
		$count = count( $datas );

		if ( $count <= 0 ) {
			return false;
		}

		update_option( 'lasso_disable_processes', 1, false ); // ? prevent other processes are triggered
		$lasso_db->remove_non_remove_attribute_process();
		$this->remove_all_processes();

		foreach ( $datas as $data ) {
			$this->push_to_queue( $data );
		}

		$this->set_total( $count );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();

		$this->save()->dispatch();

		return true;
	}

	/**
	 * Do something when task completed
	 */
	public function complete() {
		parent::complete();
		$this->remove_all_processes();

		$lasso_db = new Lasso_DB();

		$datas = $lasso_db->get_all_post_ids_in_link_locations();
		if ( count( $datas ) > 0 ) {
			$this->scan_post_page();
		}
	}
}
new Lasso_Process_Remove_Attribute();
