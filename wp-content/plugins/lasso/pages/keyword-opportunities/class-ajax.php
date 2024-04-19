<?php
/**
 * Lasso Keyword Opportunities - Ajax.
 *
 * @package Pages
 */

namespace Lasso\Pages\Keyword_Opportunities;

use Lasso_DB;

use Lasso\Classes\Keyword as Lasso_Keyword;

use Lasso\Models\Model;

/**
 * Lasso Keyword Opportunities - Ajax.
 */
class Ajax {
	/**
	 * Declare "Lasso ajax requests" to WordPress.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_lasso_add_keyword', array( $this, 'lasso_add_keyword' ) );
		add_action( 'wp_ajax_lasso_delete_keyword', array( $this, 'lasso_delete_keyword' ) );
		add_action( 'wp_ajax_lasso_unmonetized_keyword', array( $this, 'lasso_unmonetized_keyword' ) );
		add_action( 'wp_ajax_lasso_get_keywords', array( $this, 'lasso_get_keywords' ) );
	}

	/**
	 * Get keywords
	 */
	public function lasso_get_keywords() {
		$post = wp_unslash( $_POST ); // phpcs:ignore

		$search = ( isset( $post['search_key'] ) ) ? str_replace( ' ', '%', $post['search_key'] ) : '';
		$limit  = $post['limit'];
		$page   = $post['page'];

		$lasso_db = new Lasso_DB();

		// ? Process Sorting
		$search_term_string = '' !== $search ? "AND keyword LIKE '%" . $search . "%'" : '';
		$order_by           = 'id';
		$order_type         = 'desc';

		$sql       = $lasso_db->get_keywords_query( $search_term_string );
		$posts_sql = $lasso_db->set_order( $sql, $order_by, $order_type );
		$posts_sql = $lasso_db->paginate( $sql, $page, $limit );

		$data  = Model::get_results( $posts_sql );
		$count = Model::get_count( $sql );

		array_map(
			function( $value ) {
				$value->keyword = wp_unslash( $value->keyword );
				return $value;
			},
			$data
		);

		wp_send_json_success(
			array(
				'post'  => $post,
				'count' => $count,
				'data'  => $data,
				'sql'   => $sql,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Add a keyword
	 */
	public function lasso_add_keyword() {
		// phpcs:ignore
		$post          = wp_unslash( $_POST );
		$lasso_keyword = new Lasso_Keyword();
		$result        = $lasso_keyword->add_keywords( 0, array( trim( $post['keyword'] ) ) );

		if ( 1 === $result ) {
			wp_send_json_success(
				array(
					'post' => $post,
				)
			);
		} elseif ( 2 === $result ) {
			// Duplicate entry.
			wp_send_json_error( 'Keyword already exists.', 409 ); // Conflict.
		} else {
			// error.
			wp_send_json_error( 'Internal Error.', 500 );
		}
	} // @codeCoverageIgnore

	/**
	 * Delete a keyword
	 */
	public function lasso_delete_keyword() {
		// phpcs:ignore
		$post          = wp_unslash( $_POST );
		$lasso_keyword = new Lasso_Keyword();
		$result        = $lasso_keyword->delete_untrack_keywords( array( $post['keyword'] ) );

		wp_send_json_success(
			array(
				'status' => (bool) $result,
				'post'   => $post,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Unmonetized a keyword
	 */
	public function lasso_unmonetized_keyword() {
		// phpcs:ignore
		$post                = wp_unslash( $_POST );
		$keyword             = $post['keyword'] ?? '';
		$post_id             = $post['post_id'] ?? '';
		$keyword_location_id = $post['keyword_location_id'] ?? '';
		$lasso_keyword       = new Lasso_Keyword();
		$result              = $lasso_keyword->unmonetized_keyword( $keyword, $post_id, $keyword_location_id );

		wp_send_json_success(
			array(
				'status' => (bool) $result,
				'post'   => $post,
			)
		);
	} // @codeCoverageIgnore
}
