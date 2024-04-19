<?php
/**
 * Declare class Lasso_Process_Import_All
 *
 * @package Lasso_Process_Import_All
 */

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Import as Lasso_Import;
use Lasso\Classes\Enum;

use Lasso\Models\Model;

/**
 * Lasso_Process_Import_All
 */
class Lasso_Process_Import_All extends Lasso_Process {
	const LIMIT         = 100;
	const OPTION        = 'lasso_import_all_enable';
	const FILTER_PLUGIN = 'lasso_import_all_filter_plugin';

	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_import_process_all';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'bulk_import';

	/**
	 * Lasso_Process_Import_All constructor.
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
		$time_start       = microtime( true );
		$import_id        = $data['import_id'] ?? false;
		$post_type        = $data['post_type'] ?? false;
		$post_title       = $data['post_title'] ?? '';
		$import_permalink = $data['import_permalink'] ?? '';

		Lasso_Helper::write_log( 'START Import process for: ' . strval( $import_id ), $this->log_name );
		Lasso_Helper::write_log( 'Import info: ' . $post_type, $this->log_name );

		if ( $import_id && $post_type ) {
			$lasso_import = new Lasso_Import();
			$lasso_import->process_single_link_data( $import_id, $post_type, $post_title, $import_permalink, true );
		}

		$time_end       = microtime( true );
		$execution_time = round( $time_end - $time_start, 2 );
		Lasso_Helper::write_log( 'Completed Import process for: ' . strval( $import_id ) . ' - End, total process time ' . $execution_time, $this->log_name );

		return false;
	}

	/**
	 * Prepare data for process
	 *
	 * @param string $filter_plugin Plugin name.
	 */
	public function import( $filter_plugin = null ) {
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
		$sql           = $lasso_db->get_importable_urls_query( false, '', '', $filter_plugin );
		$sql           = $lasso_db->paginate( $sql, 1, self::LIMIT );
		$all_imports   = Model::get_results( $sql );
		$count         = count( $all_imports );

		if ( $count <= 0 ) {
			update_option( self::OPTION, '0' );
			delete_option( self::FILTER_PLUGIN );
			return false;
		}
		update_option( self::OPTION, '1' );

		// ? disable/remove revert all process
		$revert_all_process = new Lasso_Process_Revert_All();
		$revert_all_process->remove_process();
		update_option( Lasso_Process_Revert_All::OPTION, '0' );

		if ( $filter_plugin ) {
			update_option( self::FILTER_PLUGIN, $filter_plugin );
		}

		do_action( 'lasso_import_all_process' );

		Lasso_Helper::write_log( 'Total link to import all: ' . $count, $this->log_name );

		foreach ( $all_imports as $import ) {
			$import = Lasso_Helper::format_importable_data( $import );
			if ( empty( $import->id ) || empty( $import->post_type ) || 'checked' === $import->check_status ) {
				continue;
			}

			$this->push_to_queue(
				array(
					'import_id'        => $import->id,
					'post_type'        => $import->post_type,
					'post_title'       => Lasso_Helper::remove_unexpected_characters_from_post_title( $import->post_title ),
					'import_permalink' => $import->import_permalink,
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
	 * Do something when the process completes
	 */
	public function complete() {
		parent::complete();
		parent::remove_duplicated_processes();

		// ? scan all posts/pages for links/shortcodes after bulk import process completes
		update_option( Enum::OPTION_ENABLE_SCAN_NOTICE_AFTER_IMPORT, 1 );
		$lasso_cron = new Lasso_Process_Force_Scan_All_Posts();
		$lasso_cron->force_to_run_new_scan();
	}

	/**
	 * Complete action
	 *
	 * @param string $action Action name.
	 * @return $this
	 */
	public function complete_action( $action ) {
		// ? If there is nothing to import, we disable the import all process at this time instead of waiting for the next cron(5 minutes for Pro)
		if ( $action === $this->action ) {
			$lasso_db      = new Lasso_DB();
			$filter_plugin = get_option( self::FILTER_PLUGIN, null );
			$sql           = $lasso_db->get_importable_urls_query( false, '', '', $filter_plugin );
			$sql           = $lasso_db->paginate( $sql, 1, 1 );
			$all_imports   = Model::get_results( $sql );
			$count         = count( $all_imports );

			if ( $count <= 0 ) {
				update_option( self::OPTION, '0' );
				delete_option( self::FILTER_PLUGIN );
			}
		}

		return $this;
	}
}
new Lasso_Process_Import_All();
