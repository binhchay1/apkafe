<?php
/**
 * Lasso Group Url - Ajax.
 *
 * @package Pages
 */

namespace Lasso\Pages\Group_Urls;

use Lasso_DB;

use Lasso\Models\Model;

/**
 * Lasso Group Url - Ajax.
 */
class Ajax {
	/**
	 * Lasso Groups page
	 *
	 * @var string $groups_page
	 */
	private $groups_page = 'groups';

	/**
	 * Declare "Lasso ajax requests" to WordPress.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_lasso_save_category_positions', array( $this, 'lasso_save_category_positions' ) );
		add_action( 'wp_ajax_lasso_delete_category', array( $this, 'lasso_delete_category' ) );
	}

	/**
	 * Save category position/order
	 */
	public function lasso_save_category_positions() {
		$post = wp_unslash( $_POST ); // phpcs:ignore
		$set  = $post['set'] ?? '';
		$data = $post['data'] ?? array();

		// ? Clear previous order set -> delete from table where set = $set
		$query   = 'DELETE FROM ' . Model::get_wp_table_name( LASSO_CATEGORY_ORDER_DB ) . ' WHERE parent_slug = %s;';
		$prepare = Model::prepare( $query, $set ); // phpcs:ignore
		Model::query( $prepare );

		$position = 0;
		if ( is_array( $data ) && ! empty( $data ) ) {
			foreach ( $data as $item_id ) {
				// phpcs:ignore: insert into table values($item, $set, $position)
				$query   = 'INSERT INTO ' . Model::get_wp_table_name( LASSO_CATEGORY_ORDER_DB ) . ' VALUES(%d, %s, %d);';
				$prepare = Model::prepare( $query, $item_id, $set, $position ); // phpcs:ignore
				Model::query( $prepare );
				$position++;
			}
		}

		wp_send_json_success(
			array(
				'data' => 1,
				'post' => $post,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Delete a category of Lasso
	 */
	public function lasso_delete_category() {
		$post    = wp_unslash( $_POST ); // phpcs:ignore
		$post_id = $post['post_id'];

		wp_delete_term( $post_id, LASSO_CATEGORY );

		$redirect_link = add_query_arg(
			array(
				'post_type' => LASSO_POST_TYPE,
				'page'      => $this->groups_page,
			),
			admin_url( 'edit.php' )
		);

		wp_send_json_success(
			array(
				'data'          => 1,
				'post'          => $post,
				'redirect_link' => $redirect_link,
			)
		);
	} // @codeCoverageIgnore
}
