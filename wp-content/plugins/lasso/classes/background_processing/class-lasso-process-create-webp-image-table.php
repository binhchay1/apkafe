<?php
/**
 * Declare class Lasso_Process_Create_Webp_Image_Table
 * Create webp image for existed Lasso Posts that have not this image.
 *
 * @package Lasso_Process_Create_Webp_Image_Table
 */

use Lasso\Classes\Enum;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Enum as Lasso_Enum;

use Lasso\Models\Fields as Lasso_Fields;
use Lasso\Models\Model;
use Lasso\Models\Table_Field_Group_Detail as Lasso_Table_Field_Group_Detail;


/**
 * Lasso_Process_Create_Webp_Image_Table
 */
class Lasso_Process_Create_Webp_Image_Table extends Lasso_Process {
	const LIMIT       = 50;
	const OPTION_PAGE = 'lasso_create_webp_image_table_page';
	const OPTION_DATE = 'lasso_create_webp_image_table_date';

	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_create_webp_image_table_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'create_webp_image_table';

	/**
	 * Task
	 *
	 * @param mixed $table_field_group_detail_id Queue item to iterate over.
	 * @return mixed
	 */
	public function task( $table_field_group_detail_id ) {
		if ( ! function_exists( 'imagewebp' ) ) {
			return false;
		}

		if ( empty( $table_field_group_detail_id ) ) {
			return false;
		}

		$start_time = microtime( true );
		Lasso_Helper::write_log( 'START create webp image for Table field group detail id: ' . $table_field_group_detail_id, $this->log_name );

		$table_field_group_detail_model = new Lasso_Table_Field_Group_Detail();
		$table_field_group_detail       = $table_field_group_detail_model->get_one( $table_field_group_detail_id );

		if ( $table_field_group_detail && Lasso_Fields::IMAGE_FIELD_ID === intval( $table_field_group_detail->get_field_id() ?? 0 ) && $table_field_group_detail->get_field_value() ) {
			$webp_image_url = Lasso_Helper::create_webp_image_from_url( $table_field_group_detail->get_field_value() );
			if ( $webp_image_url ) {
				$table_field_group_detail->insert_meta_by_id( $table_field_group_detail->get_id(), Enum::LASSO_WEBP_THUMBNAIL, $webp_image_url );
			}

			Lasso_Helper::write_log( 'Webp image: ' . $webp_image_url, $this->log_name );
		}

		$time_end       = microtime( true );
		$execution_time = round( $time_end - $start_time, 2 );
		Lasso_Helper::write_log( 'Create webp image takes: ' . $execution_time, $this->log_name );
		Lasso_Helper::write_log( 'END create webp image for Table field group detail id: ' . $table_field_group_detail_id, $this->log_name );

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

		$sql         = $lasso_db->get_table_image_field_group_detail_id_non_webp_query();
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
new Lasso_Process_Create_Webp_Image_Table();
