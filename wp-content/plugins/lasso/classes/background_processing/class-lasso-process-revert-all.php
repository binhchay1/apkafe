<?php
/**
 * Declare class Lasso_Process_Revert_All
 *
 * @package Lasso_Process_Revert_All
 */

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Import as Lasso_Import;

use Lasso\Models\Model;

/**
 * Lasso_Process_Revert_All
 */
class Lasso_Process_Revert_All extends Lasso_Process {
	const LIMIT         = 100;
	const OPTION        = 'lasso_revert_all_enable';
	const FILTER_PLUGIN = 'lasso_revert_all_filter_plugin';

	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_revert_process_all';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'bulk_revert';

	/**
	 * Lasso_Process_Revert_All constructor.
	 */
	public function __construct() {
		parent::__construct();
		add_action( self::COMPLETE_ACTION_KEY, array( $this, 'complete_action' ), 10, 1 );
	}

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
		Lasso_Helper::write_log( 'START Revert process for: ' . $data['revert_id'], $this->log_name );

		$post_type = isset( $data['old_uri'] ) && false !== strpos( $data['old_uri'], '[amazon table' )
							&& isset( $data['post_data'] ) ? 'aawp_table' : '';
		// ? AAWP table => set $data['revert_id'] is AAWP table id
		$data['revert_id'] = isset( $data['old_uri'] ) && false !== strpos( $data['old_uri'], '[amazon table' )
							&& isset( $data['post_data'] ) ? $data['post_data'] : $data['revert_id'];

		$lasso_import = new Lasso_Import();
		$lasso_import->process_single_link_revert( $data['revert_id'], $data['source'], $post_type );

		$time_end       = microtime( true );
		$execution_time = round( $time_end - $start_time, 2 );
		Lasso_Helper::write_log( 'Revert process for takes: ' . $execution_time, $this->log_name );
		Lasso_Helper::write_log( 'Revert process for at index: ' . $data['revert_id'] . ' - End', $this->log_name );

		return false;
	}

	/**
	 * Prepare data for process
	 *
	 * @param string $filter_plugin Plugin name.
	 */
	public function revert( $filter_plugin = null ) {
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

		$filter_plugin = $filter_plugin ? $filter_plugin : get_option( self::FILTER_PLUGIN, null );
		$sql           = $lasso_db->get_revertable_urls_query( $filter_plugin );
		$sql           = $lasso_db->paginate( $sql, 1, self::LIMIT );
		$all_reverts   = Model::get_results( $sql );
		$count         = count( $all_reverts );

		if ( $count <= 0 ) {
			update_option( self::OPTION, '0' );
			delete_option( self::FILTER_PLUGIN );
			return false;
		}
		update_option( self::OPTION, '1' );
		if ( $filter_plugin ) {
			update_option( self::FILTER_PLUGIN, $filter_plugin );
		}

		// ? disable/remove import all process
		$import_all_process = new Lasso_Process_Import_All();
		$import_all_process->remove_process();
		update_option( Lasso_Process_Import_All::OPTION, '0' );
		delete_option( Lasso_Process_Import_All::FILTER_PLUGIN );

		foreach ( $all_reverts as $revert ) {
			if ( empty( $revert->import_id ) || empty( $revert->import_source ) ) {
				continue;
			}
			$this->push_to_queue(
				array(
					'revert_id' => $revert->import_id,
					'source'    => $revert->import_source,
					'old_uri'   => $revert->old_uri,
					'post_data' => $revert->post_data,
				)
			);
		}

		$this->set_total( $count );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();
		// ? save queue
		$this->save()->dispatch();

		return true;
	}

	/**
	 * Complete action
	 *
	 * @param string $action Action name.
	 * @return $this
	 */
	public function complete_action( $action ) {
		// ? If there is nothing to revert, we disable the revert all process at this time instead of waiting for the next cron(5 minutes for Pro)
		if ( $action === $this->action ) {
			$lasso_db    = new Lasso_DB();
			$sql         = $lasso_db->get_revertable_urls_query();
			$sql         = $lasso_db->paginate( $sql, 1, 1 );
			$all_reverts = Model::get_results( $sql );
			$count       = count( $all_reverts );

			if ( $count <= 0 ) {
				update_option( self::OPTION, '0' );
			}
		}

		return $this;
	}
}
new Lasso_Process_Revert_All();
