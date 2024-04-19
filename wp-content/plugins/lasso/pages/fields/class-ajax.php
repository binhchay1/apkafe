<?php
/**
 * Lasso Fields - Ajax.
 *
 * @package Pages
 */

namespace Lasso\Pages\Fields;

use Lasso_DB;

use Lasso\Models\Fields;
use Lasso\Models\Field_Mapping;
use Lasso\Models\Model;

/**
 * Lasso Fields - Ajax.
 */
class Ajax {
	/**
	 * Declare "Lasso ajax requests" to WordPress.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_lasso_add_field_to_page', array( $this, 'lasso_add_field_to_page' ) );
		add_action( 'wp_ajax_lasso_remove_field_from_page', array( $this, 'lasso_remove_field_from_page' ) );
		add_action( 'wp_ajax_lasso_create_new_field', array( $this, 'lasso_create_new_field' ) );
		add_action( 'wp_ajax_lasso_save_field_positions', array( $this, 'lasso_save_field_positions' ) );
		add_action( 'wp_ajax_lasso_store_field', array( $this, 'lasso_store_field' ) );
		add_action( 'wp_ajax_lasso_delete_field', array( $this, 'lasso_delete_field' ) );
	}

	/**
	 * Add a Field to a Product
	 */
	public function lasso_add_field_to_page() {
		// phpcs:ignore
		$post   = wp_unslash( $_POST );
		$result = Field_Mapping::add_field_to_page( $post['field_id'], $post['post_id'] );

		wp_send_json_success(
			array(
				'status' => (bool) $result,
				'post'   => $post,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Remove a Field from a Product
	 */
	public function lasso_remove_field_from_page() {
		// phpcs:ignore
		$post = wp_unslash( $_POST );

		$lasso_db = new Lasso_DB();
		$result   = $lasso_db->remove_field_from_page( $post['field_id'], $post['post_id'] );

		wp_send_json_success(
			array(
				'status' => (bool) $result,
				'post'   => $post,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Create a new Field
	 */
	public function lasso_create_new_field() {
		// phpcs:ignore
		$post = wp_unslash( $_POST );

		$lasso_db = new Lasso_DB();
		if ( ! empty( $post['title'] ) ) {
			$result = $lasso_db->create_new_field( $post['title'], $post['type'], $post['description'] );
		} else {
			$result = false;
		}

		wp_send_json_success(
			array(
				'status' => (bool) $result,
				'post'   => $post,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Save field position/order
	 */
	public function lasso_save_field_positions() {
		global $wpdb;

		$post     = wp_unslash( $_POST ); // phpcs:ignore
		$data     = $post['data'] ?? array();
		$position = 0;
		if ( is_array( $data ) && ! empty( $data ) ) {
			foreach ( $data as $item ) {
				$item[3] = 'true' === $item[3] ? 1 : 0;
				$query   = '
					INSERT INTO ' . Model::get_wp_table_name( LASSO_FIELD_MAPPING ) . ' 
					VALUES(%d, %d, %s, %d, %d)
					ON DUPLICATE KEY UPDATE
						lasso_id = %d,
						field_id = %d,
						field_value = %s,
						field_order = %d,
						field_visible = %d
				';
				$prepare = Model::prepare( $query, $item[1], $item[0], $item[2], $position, $item[3], $item[1], $item[0], $item[2], $position, $item[3] ); // phpcs:ignore
				Model::query( $prepare );

				if ( intval( $item[0] ) === Fields::RATING_FIELD_ID ) {
					Field_Mapping::set_show_field_name( $item[1], $item[0], $item[4] );
				}

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
	 * Store field
	 */
	public function lasso_store_field() {
		$post     = wp_unslash( $_POST ); // phpcs:ignore
		$lasso_db = new Lasso_DB();

		if ( 0 === (int) $post['field_id'] ) {
			$result = $lasso_db->create_new_field( $post['field_title'], $post['field_type'], $post['field_description'] );

			wp_send_json_success(
				array(
					'status'    => false !== $result,
					'new_field' => true,
					'link'      => $this->get_fields_page(),
					'post'      => $post,
				)
			);
		} else {
			$result = $lasso_db->update_field( $post['field_id'], $post['field_title'], $post['field_type'], $post['field_description'] );

			wp_send_json_success(
				array(
					'status'    => false !== $result,
					'new_field' => false,
					'post'      => $post,
				)
			);
		}
	} // @codeCoverageIgnore

	/**
	 * Get url of group detail page
	 */
	public function get_fields_page() {
		$fields = add_query_arg(
			array(
				'post_type' => LASSO_POST_TYPE,
				'page'      => 'fields',
			),
			admin_url( 'edit.php' )
		);

		return $fields;
	}

	/**
	 * Delete a field of Lasso
	 */
	public function lasso_delete_field() {
		$post     = wp_unslash( $_POST ); // phpcs:ignore
		$post_id  = $post['post_id'];
		$lasso_db = new Lasso_DB();

		$result = $lasso_db->delete_field( $post_id );

		$redirect_link = add_query_arg(
			array(
				'post_type' => LASSO_POST_TYPE,
				'page'      => $this->get_fields_page(),
			),
			admin_url( 'edit.php' )
		);

		wp_send_json_success(
			array(
				'result'        => $result,
				'post'          => $post,
				'redirect_link' => $redirect_link,
			)
		);
	} // @codeCoverageIgnore
}
