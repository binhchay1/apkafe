<?php
/**
 * Declare class Lasso_Process_Link_Database
 *
 * @package Lasso_Process_Link_Database
 */

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Keyword as Lasso_Keyword;
use Lasso\Classes\Enum;

/**
 * Lasso_Process_Link_Database
 */
class Lasso_Process_Link_Database extends Lasso_Process {
	const SCAN_ALL_POSTS_SUCCESSFUL_KEY = 'lasso_scan_all_posts';

	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_link_database_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'link_database';

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
		$cron       = new Lasso_Cron();
		Lasso_Helper::write_log( 'Link database at index: ' . $index, $this->log_name );

		$post = get_post( $index );

		if ( ! is_null( $post ) ) {
			$cron->check_all_posts_pages( array( $post ) );
			$cron->populate_lasso_content( array( $post ), $this->log_name );
			Lasso_Keyword::scan_keywords_in_post_page( $post->ID );
			Lasso_Helper::write_log( 'Post id: ' . $post->ID . ' | Post title: ' . $post->post_title, $this->log_name );
		} else {
			Lasso_Helper::write_log( 'Task index ' . $index . ' is null, doing nothing.', $this->log_name );
		}

		$this->set_processing_runtime();

		$time_end       = microtime( true );
		$execution_time = round( $time_end - $start_time, 2 );
		Lasso_Helper::write_log( 'Link database takes: ' . $execution_time, $this->log_name );
		Lasso_Helper::write_log( 'Link database at index: ' . $index . ' - End', $this->log_name );

		return false;
	}

	/**
	 * Prepare data for process
	 *
	 * @param array $custom_ids List of ids. Default to empty array.
	 * @param int   $limit      Limit of posts. Default to 10.
	 */
	public function link_database_limit( $custom_ids = array(), $limit = 10 ) {
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

		$option_page = $this->action . '_page';
		$option_date = $this->action . '_date';

		$today      = gmdate( 'Y-m-d' );
		$check_date = get_option( $option_date, '' );

		$page = get_option( $option_page, '1' );
		$page = intval( $page );

		// ? check issue daily
		if ( 1 === $page && $today === $check_date ) {
			return false;
		}

		$post_ids = is_array( $custom_ids ) && count( $custom_ids ) > 0
			? $custom_ids
			: $lasso_db->get_all_post_ids_link_db( $this->diff, array( 'publish', 'draft' ), $page, $limit );
		$count    = count( $post_ids );

		if ( $count <= 0 || Lasso_Process::are_all_processes_disabled() ) {
			update_option( $option_page, '1' ); // ? reset page to 1 when no data from the query

			// ? Set flag scan all posts successful
			if ( $count <= 0 ) {
				update_option( self::SCAN_ALL_POSTS_SUCCESSFUL_KEY, 1 );
			}

			return false;
		}
		update_option( $option_page, strval( $page + 1 ) ); // ? increase page
		update_option( $option_date, $today ); // ? set check date is today

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

	/**
	 * Prepare data for process. No limit
	 *
	 * @param array $custom_ids List of ids. Default to empty array.
	 */
	public function link_database( $custom_ids = array() ) {
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

		$post_ids = is_array( $custom_ids ) && count( $custom_ids ) > 0
			? $custom_ids
			: $lasso_db->get_all_post_ids_link_db( $this->diff, array( 'publish', 'draft' ) );
		$count    = count( $post_ids );

		if ( $count <= 0 || Lasso_Process::are_all_processes_disabled() ) {
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

	/**
	 * Write logs when task completed
	 */
	public function task_end_log() {
		parent::task_end_log();
		update_option( self::SCAN_ALL_POSTS_SUCCESSFUL_KEY, 1 );
	}
}
new Lasso_Process_Link_Database();



/**
 * This file and link_database is the same
 * This file is used for rebuild data in DB without lock the report page
 */
class Lasso_Process_Build_Link extends Lasso_Process_Link_Database { // phpcs:ignore
	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_build_link_database_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'build_link_database';
}
new Lasso_Process_Build_Link();



class Lasso_Process_Scan_Links_Post_Save extends Lasso_Process_Link_Database { // phpcs:ignore
	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_scan_links_post_save_database_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'scan_link_on_post_saving';
}
new Lasso_Process_Scan_Links_Post_Save();

class Lasso_Process_Build_Rewrite_Slug_Links_In_Posts extends Lasso_Process_Link_Database { // phpcs:ignore
	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_build_rewrite_slug_link_in_post_database_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'build_rewrite_slug_link_in_post';

	/**
	 * Prepare data for process
	 *
	 * @param array $custom_ids List of ids. Default to empty array.
	 */
	public function link_database( $custom_ids = array() ) {
		// ? check whether process is age out and make it can work on Lasso UI via ajax requests
		$this->is_process_age_out();

		if ( $this->is_process_running_reach_the_limit() ) {
			$this->push_to_lasso_processes_queue( __CLASS__, __FUNCTION__, func_get_args() );
			return false;
		}

		// ? Run newest rewrite slug setting
		$batch = $this->get_batch();

		if ( ! empty( $batch ) ) {
			$this->delete( $batch->key );
			$this->unlock_process();
			$this->clear_scheduled_event();
			$this->set_completed();
		}

		$lasso_db = new Lasso_DB();

		$post_ids = is_array( $custom_ids ) && count( $custom_ids ) > 0 ? $custom_ids : $lasso_db->get_all_post_ids_link_db( $this->diff, array( 'publish', 'draft' ) );
		$count    = count( $post_ids );

		if ( $count <= 0 || Lasso_Process::are_all_processes_disabled() ) {
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
new Lasso_Process_Build_Rewrite_Slug_Links_In_Posts();

/**
 * This class is used for force scan all post's links
 * Using for first time active Lasso plugin then scan all posts's,
 * when this background process was completed, we marked the flag to enable import feature.
 */
class Lasso_Process_Force_Scan_All_Posts extends Lasso_Process_Link_Database{ // phpcs:ignore
	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_link_database_process_force_scan_all_posts';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'link_database_force_scan_all_posts';

	/**
	 * Reset data
	 */
	public function reset_data() {
		$option_page = $this->action . '_page';

		delete_option( $option_page );
		delete_option( 'lasso_force_to_run_new_scan' );

		$this->remove_all_processes();
	}

	/**
	 * Force to scan link in post/page
	 *
	 * @param int $limit Limit of posts. Default to 200.
	 */
	public function force_to_run_new_scan( $limit = 200 ) {
		if ( $this->is_process_running() ) {
			return false;
		}

		$enable_lasso_force_scan = get_option( 'lasso_force_to_run_new_scan', 'true' );

		$lasso_db = new Lasso_DB();

		$option_page = $this->action . '_page';

		$page = get_option( $option_page, '1' );
		$page = intval( $page );

		// ? check issue daily
		if ( 1 === $page && 'true' !== $enable_lasso_force_scan ) {
			return false;
		}

		$post_ids = $lasso_db->get_all_post_ids_link_db( false, array( 'publish' ), $page, $limit );
		$count    = count( $post_ids );

		if ( $count <= 0 ) {
			update_option( 'lasso_force_to_run_new_scan', 'false' ); // ? disable force scan
			update_option( $option_page, '1' ); // ? reset page to 1 when no data from the query

			// ? Set flag scan all posts successful
			update_option( self::SCAN_ALL_POSTS_SUCCESSFUL_KEY, 1 );

			return false;
		}
		update_option( $option_page, strval( $page + 1 ) ); // ? increase page

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

	/**
	 * Do something when the process completes
	 */
	public function complete() {
		parent::complete();

		// ? scan all posts/pages for links/shortcodes after bulk import process completes
		update_option( Enum::OPTION_ENABLE_SCAN_NOTICE_AFTER_IMPORT, 0 );
	}
}
new Lasso_Process_Force_Scan_All_Posts();
