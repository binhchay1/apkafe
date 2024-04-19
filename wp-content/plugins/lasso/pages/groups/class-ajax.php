<?php
/**
 * Lasso Group - Ajax.
 *
 * @package Pages
 */

namespace Lasso\Pages\Groups;

use Lasso_DB;
use Lasso_Affiliate_Link;

use Lasso\Classes\Category_Order as Lasso_Category_Order;
use Lasso\Classes\Group as Lasso_Group;
use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Models\Model;

/**
 * Lasso Group - Ajax.
 */
class Ajax {
	/**
	 * Group detail page
	 *
	 * @var string $group_details_page
	 */
	private $group_details_page = 'group-details';

	/**
	 * Declare "Lasso ajax requests" to WordPress.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_lasso_store_category', array( $this, 'lasso_store_category' ) );
		add_action( 'wp_ajax_lasso_get_groups', array( $this, 'lasso_get_groups' ) );
		add_action( 'wp_ajax_lasso_add_lasso_to_group', array( $this, 'lasso_add_lasso_to_group' ) );
	}

	/**
	 * Store category
	 */
	public function lasso_store_category() {
		$data = wp_unslash( $_POST ); // phpcs:ignore

		if ( 0 === (int) $data['cat_id'] ) {
			$result = wp_insert_term(
				$data['cat_name'], // ? the term
				LASSO_CATEGORY, // ? the taxonomy
				array(
					'description' => $data['cat_desc'],
				)
			);
		} else {
			$result = wp_update_term(
				$data['cat_id'], // ? the term
				LASSO_CATEGORY, // ? the taxonomy
				array(
					'name'        => $data['cat_name'],
					'description' => $data['cat_desc'],
				)
			);
		}

		if ( $result && ! is_wp_error( $result ) ) {
			$result = array(
				'status' => 1,
				'link'   => $this->get_groups_details_page() . '&post_id=' . $result['term_id'],
				'data'   => $data,
				'cat_id' => $result['term_id'] ?? 0,
			);
		} else {
			$result = array(
				'status' => 0,
				'link'   => $this->get_groups_details_page(),
				'data'   => $data,
				'cat_id' => 0,
			);
		}

		wp_send_json_success( $result );
	} // @codeCoverageIgnore

	/**
	 * Get url of group detail page
	 */
	public function get_groups_details_page() {
		$categories = add_query_arg(
			array(
				'post_type' => LASSO_POST_TYPE,
				'page'      => $this->group_details_page,
			),
			admin_url( 'edit.php' )
		);

		return $categories;
	}

	/**
	 * Get groups
	 */
	public function lasso_get_groups() {
		$post = wp_unslash( $_POST ); // phpcs:ignore

		$search = str_replace( ' ', '%', $post['search_key'] );
		$limit  = $post['limit'] ?? 5;
		$page   = $post['page'] ?? 1;

		$lasso_db = new Lasso_DB();

		// ? Process Sorting
		$search_term_string = '' !== $search ? "AND t.name LIKE '%" . $search . "%'" : '';
		$order_by           = 'term_id';
		$order_type         = 'desc';

		$sql = $lasso_db->get_groups_query( $search_term_string );

		$posts_sql = $lasso_db->set_order( $sql, $order_by, $order_type );
		$posts_sql = Lasso_Helper::paginate( $posts_sql, $page, $limit );

		$data  = Model::get_results( $posts_sql );
		$count = Model::get_count( $sql );

		wp_send_json_success(
			array(
				'post'  => $post,
				'count' => $count,
				'data'  => $data,
				'page'  => $page,
			)
		);
	} // @codeCoverageIgnore

	/**
	 * Add a lasso url to group
	 */
	public function lasso_add_lasso_to_group() {
		$data_post   = wp_unslash( $_POST ); // phpcs:ignore
		$id          = $data_post['post_id']; // post_id is wp term id.
		$lasso_id    = $data_post['lasso_id'];
		$lasso_group = Lasso_Group::get_by_id( $id );
		if ( ! $lasso_group->has_lasso_url( $lasso_id ) ) {
			$lasso_group->add_lasso_url( $lasso_id );

			// ? Insert Lasso categories with order
			$slug             = $lasso_group->get_slug();
			$order_max        = Lasso_Category_Order::get_max_order_by_slug( $slug );
			$cat_order_insert = array(
				'item_id'     => $lasso_id,
				'parent_slug' => $slug,
				'term_order'  => $order_max,
			);
			$lasso_cat_order  = new Lasso_Category_Order( $cat_order_insert );
			$lasso_cat_order->insert();

			$lasso_url  = Lasso_Affiliate_Link::get_lasso_url( $lasso_id );
			$lasso_post = get_post( $lasso_id );
			$file_path  = LASSO_PLUGIN_PATH . '/admin/views/rows/group-url-row-2.php';
			$html       = Lasso_Helper::include_with_variables(
				$file_path,
				array(
					'lasso_url'  => $lasso_url,
					'lasso_post' => $lasso_post,
				)
			);
			wp_send_json_success(
				array(
					'status'      => true,
					'html'        => $html,
					'total_links' => $lasso_group->get_total_links(),
				)
			);
		} else {
			wp_send_json_error( 'Link already exists.', 409 );
		}
	} // @codeCoverageIgnore
}
