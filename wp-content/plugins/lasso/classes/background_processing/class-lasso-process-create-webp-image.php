<?php
/**
 * Declare class Lasso_Process_Create_Webp_Image
 * Create webp image for existed Lasso Posts that have not this image.
 *
 * @package Lasso_Process_Create_Webp_Image
 */

use Lasso\Classes\Enum as Lasso_Enum;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting as Lasso_Setting;

use Lasso\Models\Model;

/**
 * Lasso_Process_Create_Webp_Image
 */
class Lasso_Process_Create_Webp_Image extends Lasso_Process {
	const LIMIT       = 100;
	const OPTION_PAGE = 'lasso_create_webp_image_page';
	const OPTION_DATE = 'lasso_create_webp_image_date';

	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_create_webp_image_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'create_webp_image';

	/**
	 * Task
	 *
	 * @param mixed $lasso_id Queue item to iterate over.
	 * @return mixed
	 */
	public function task( $lasso_id ) {
		if ( ! function_exists( 'imagewebp' ) ) {
			return false;
		}

		if ( empty( $lasso_id ) || LASSO_POST_TYPE !== get_post_type( $lasso_id ) ) {
			return false;
		}

		$start_time = microtime( true );
		Lasso_Helper::write_log( 'START create webp image for Lasso ID: ' . $lasso_id, $this->log_name );

		$webp_image_path = Lasso_Helper::create_lasso_webp_image( $lasso_id );

		$time_end       = microtime( true );
		$execution_time = round( $time_end - $start_time, 2 );
		Lasso_Helper::write_log( 'Webp image path: ' . $webp_image_path, $this->log_name );
		Lasso_Helper::write_log( 'Create webp image takes: ' . $execution_time, $this->log_name );
		Lasso_Helper::write_log( 'END create webp image for Lasso ID: ' . $lasso_id . ' - End', $this->log_name );

		return false;
	}

	/**
	 * Prepare data for process
	 */
	public function process() {
		$enable_webp = Lasso_Setting::lasso_get_setting( Lasso_Enum::OPTION_ENABLE_WEBP );
		if ( ! $enable_webp ) {
			return false;
		}

		if ( ! function_exists( 'imagewebp' ) ) {
			return false;
		}

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

		$sql         = $lasso_db->get_lasso_ids_non_webp_query();
		$sql         = $lasso_db->paginate( $sql, $page, self::LIMIT );
		$items       = Model::get_results( $sql );
		$total_count = count( $items );

		if ( $total_count <= 0 ) {
			update_option( self::OPTION_PAGE, '1' );
			return false;
		}
		update_option( self::OPTION_PAGE, strval( $page + 1 ) ); // ? increase page
		update_option( self::OPTION_DATE, $today ); // ? set check date is today

		foreach ( $items as $item ) {
			$this->push_to_queue( $item->id );
		}

		$this->set_total( $total_count );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();
		// ? save queue
		$this->save()->dispatch();

		return true;
	}
}
new Lasso_Process_Create_Webp_Image();
