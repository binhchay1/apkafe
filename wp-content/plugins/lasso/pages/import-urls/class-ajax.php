<?php
/**
 * Lasso Import Url - Ajax.
 *
 * @package Pages
 */

namespace Lasso\Pages\Import_Urls;

use Lasso_Process_Import_All;
use Lasso_Process_Revert_All;
use Lasso_DB;

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;
use Lasso\Classes\Import as Lasso_Import;

use Lasso\Models\Link_Locations as Model_Link_Locations;


/**
 * Lasso Import Url - Ajax.
 */
class Ajax {
	/**
	 * Declare "Lasso ajax requests" to WordPress.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_lasso_import_all_links', array( $this, 'lasso_import_all_links' ) );
		add_action( 'wp_ajax_lasso_revert_all_links', array( $this, 'lasso_revert_all_links' ) );

		add_action( 'wp_ajax_lasso_import_single_link', array( $this, 'lasso_import_single_link' ) );
		add_action( 'wp_ajax_lasso_revert_single_link', array( $this, 'lasso_revert_single_link' ) );

		add_action( 'wp_ajax_lasso_get_import_locations', array( $this, 'lasso_get_import_locations' ) );

		add_action( 'wp_ajax_lasso_is_import_all_processing', array( $this, 'lasso_is_import_all_processing' ) );
	}

	/**
	 * Import all links
	 */
	public function lasso_import_all_links() {
		update_option( Lasso_Process_Import_All::OPTION, '1' );
		// phpcs:ignore
		$post          = wp_unslash( $_POST );
		$filter_plugin = $post['filter_plugin'] ?? '';
		$import_all    = new Lasso_Process_Import_All();
		$import_all->import( $filter_plugin );

		wp_send_json_success(
			array(
				'status' => true,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Revert all links
	 */
	public function lasso_revert_all_links() {
		update_option( Lasso_Process_Revert_All::OPTION, '1' );

		// phpcs:ignore
		$post          = wp_unslash( $_POST );
		$filter_plugin = $post['filter_plugin'] ?? '';
		$revert_all    = new Lasso_Process_Revert_All();
		$revert_all->revert( $filter_plugin );

		wp_send_json_success(
			array(
				'status' => true,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Import a single post from other plugins into Lasso
	 */
	public function lasso_import_single_link() {
		// phpcs:ignore
		$post             = wp_unslash( $_POST );
		$import_id        = $post['import_id'] ?? '';
		$post_type        = $post['post_type'] ?? '';
		$post_title       = $post['post_title'] ?? '';
		$import_permalink = $post['import_permalink'] ?? '';

		if ( empty( $import_id ) || empty( $post_type ) ) {
			wp_send_json_success(
				array(
					'status' => false,
				)
			);
		}

		$lasso_import = new Lasso_Import();

		list($status, $import_data) = $lasso_import->process_single_link_data( $import_id, $post_type, $post_title, $import_permalink );

		wp_send_json_success(
			array(
				'status' => $status,
				'data'   => $import_data,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Revert a single link from Lasso to other plugins
	 */
	public function lasso_revert_single_link() {
		// phpcs:ignore
		$post          = wp_unslash( $_POST );
		$import_id     = $post['import_id'] ?? '';
		$import_source = $post['import_source'] ?? '';
		$post_type     = $post['post_type'] ?? '';
		$lasso_import  = new Lasso_Import();

		$status = $lasso_import->process_single_link_revert( $import_id, $import_source, $post_type );

		wp_send_json_success(
			array(
				'status' => $status,
				'data'   => $post,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Get import locations
	 */
	public function lasso_get_import_locations() {
		$data        = wp_unslash( $_POST ); // phpcs:ignore
		$lasso_id    = $data['lasso_id'] ?? null;
		$page_number = $data['page_number'] ?? 1;
		$limit       = $data['limit'] ?? 10;
		$order_by    = $data['order_by'] ?? 'post_modified';
		$order_type  = $data['order_type'] ?? 'desc';
		$total_count = Model_Link_Locations::total_locations_by_lasso_id( $lasso_id );
		$sql         = Model_Link_Locations::get_url_links_query( $lasso_id );
		$sql         = ( new Lasso_DB() )->set_order( $sql, $order_by, $order_type );
		$sql         = Lasso_Helper::paginate( $sql, $page_number, $limit );
		$results     = Model_Link_Locations::get_results( $sql );
		$datas       = array();

		foreach ( $results as $result ) {
			$data         = new \stdClass();
			$link_type    = $result->link_type ?? '';
			$display_type = $result->display_type ?? '';
			$lasso_id     = intval( $result->lasso_id ?? 0 );
			$anchor_text  = $result->anchor_text ?? null;

			$data->edit_post  = get_edit_post_link( $result->detection_id );
			$data->post_link  = esc_url( get_permalink( $result->detection_id ) );
			$data->post_title = $result->post_title;

			list( $data->icon_class, $data->type_description ) = Lasso_Html_Helper::get_link_location_displays( $link_type, $display_type, $anchor_text, $lasso_id );

			$datas[] = $data;
		}

		wp_send_json_success(
			array(
				'status' => 1,
				'page'   => $page_number,
				'count'  => $total_count,
				'datas'  => $datas,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Check if bulk import is processing.
	 */
	public function lasso_is_import_all_processing() {
		$allow_import_all       = get_option( Lasso_Process_Import_All::OPTION, '0' );
		$enable_import_all      = 1 === intval( $allow_import_all );
		$total_import_remaining = ( new Lasso_Process_Import_All() )->get_total_remaining();

		wp_send_json_success(
			array(
				'is_processing' => $enable_import_all && $total_import_remaining > 0, // ? Check total remaining instead of method "is_process_running()" to cover the case "wp-cron is not working"
			)
		);
	} // @codeCoverageIgnore
}
