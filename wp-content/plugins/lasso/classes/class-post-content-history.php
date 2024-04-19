<?php
/**
 * Declare class Post_Content_History
 *
 * @package Post_Content_History
 */

namespace Lasso\Classes;

use Lasso_DB;

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;

use Lasso\Models\Model;

/**
 * Post_Content_History
 */
class Post_Content_History {
	/**
	 * ID
	 *
	 * @var int $id ID.
	 */
	private $id;

	/**
	 * Object ID
	 *
	 * @var int $object_id Post ID or Lasso ID etc.
	 */
	private $object_id;

	/**
	 * Old value
	 *
	 * @var string $old_value Old value.
	 */
	private $old_value;

	/**
	 * New value
	 *
	 * @var string $new_value New value.
	 */
	private $new_value;

	/**
	 * Date updated
	 *
	 * @var string $date_updated Date updated the value.
	 */
	private $date_updated;


	/**
	 * Post_Content_History constructor.
	 *
	 * @param array $data An array from DB.
	 */
	public function __construct( $data = array() ) {
		if ( isset( $data['id'] ) ) {
			$this->set_id( $data['id'] );
		}

		if ( isset( $data['object_id'] ) ) {
			$this->set_object_id( $data['object_id'] );
		}

		if ( isset( $data['old_value'] ) ) {
			$this->set_old_value( $data['old_value'] );
		}

		if ( isset( $data['new_value'] ) ) {
			$this->set_new_value( $data['new_value'] );
		}

		if ( isset( $data['date_updated'] ) ) {
			$this->set_date_updated( $data['date_updated'] );
		}

	}

	/**
	 * Set ID
	 *
	 * @param int $id ID.
	 */
	private function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Get ID
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get object ID
	 *
	 * @return int
	 */
	public function get_object_id() {
		return $this->object_id;
	}

	/**
	 * Set object ID
	 *
	 * @param int $object_id Object ID.
	 */
	public function set_object_id( $object_id ) {
		$this->object_id = $object_id;
	}

	/**
	 * Get old value
	 *
	 * @return string
	 */
	public function get_old_value() {
		return $this->old_value;
	}

	/**
	 * Set old value
	 *
	 * @param string $old_value Old value.
	 */
	public function set_old_value( $old_value ) {
		$this->old_value = $old_value;
	}

	/**
	 * Get new value
	 *
	 * @return string
	 */
	public function get_new_value() {
		return $this->new_value;
	}

	/**
	 * Set new value
	 *
	 * @param string $new_value New value.
	 */
	public function set_new_value( $new_value ) {
		$this->new_value = $new_value;
	}

	/**
	 * Get date updated
	 *
	 * @return string
	 */
	public function get_date_updated() {
		return $this->date_updated;
	}

	/**
	 * Set date updated
	 *
	 * @param string $date_updated Date updated.
	 */
	private function set_date_updated( $date_updated ) {
		$this->date_updated = $date_updated;
	}

	/**
	 * Insert new record
	 *
	 * @return Post_Content_History
	 */
	public function insert() {
		global $wpdb;

		$sql = '
			INSERT INTO ' . Model::get_wp_table_name( LASSO_POST_CONTENT_HISTORY ) . '
			(`object_id`, `old_value`, `new_value`, `updated_date`) VALUES 
			(%d, %s, %s, %s);
		';

		$this->set_date_updated( gmdate( 'Y-m-d H:i:s' ) );
		$sql    = Model::prepare( $sql, $this->get_object_id(), $this->get_old_value(), $this->get_new_value(), $this->get_date_updated() ); // phpcs:ignore
		$result = Model::query( $sql ); // phpcs:ignore
		if ( $result ) {
			$this->set_id( $wpdb->insert_id );
		}
		return $this;
	}

	/**
	 * Get post content history by id
	 *
	 * @param int $id Post content history id.
	 */
	public static function get_by_id( $id ) {
		$lasso_db = new Lasso_DB();
		return $lasso_db->get_post_content_history_detail( $id );
	}

	/**
	 * Get post content history by object id
	 *
	 * @param int   $object_id Object ID.
	 * @param array $columns   Column to select.
	 */
	public static function get_by_object_id( $object_id, $columns = array() ) {
		if ( empty( $columns ) ) {
			$select = '*';
		} else {
			$select = implode( ',', $columns );
		}

		$sql     = 'SELECT ' . $select . ' FROM ' . Model::get_wp_table_name( LASSO_POST_CONTENT_HISTORY ) . ' WHERE object_id = %d';
		$prepare = Model::prepare( $sql, $object_id ); // phpcs:ignore

		return Model::get_row( $prepare );
	}

	/**
	 * Determine enable history setting
	 *
	 * @return bool|mixed
	 */
	private static function should_track_change() {
		return Lasso_Setting::lasso_get_setting( Lasso_Setting_Enum::ENABLE_HISTORY );
	}

	/**
	 * Track changes
	 *
	 * @param int    $object_id Object ID.
	 * @param string $old_value Old value.
	 * @param string $new_value New value.
	 */
	public static function track_changes( $object_id, $old_value, $new_value ) {
		if ( ! self::should_track_change() ) {
			return;
		}

		if ( ! self::is_object_existed( $object_id ) && ! Lasso_Helper::compare_string( $old_value, $new_value ) ) {
			Lasso_Helper::write_log( "Track Change: Object ID: $object_id", 'change_post_content_history' );

			$lasso_change = new self();
			$lasso_change->set_object_id( $object_id );
			$lasso_change->set_old_value( $old_value );
			$lasso_change->set_new_value( $new_value );
			$lasso_change->insert();
		}
	}

	/**
	 * Revert post content by id
	 *
	 * @param int $id Post content history id.
	 */
	public static function revert( $id ) {
		$post_content_history = self::get_by_id( $id );

		if ( $post_content_history ) {
			global $wpdb;
			$post_id      = $post_content_history->post_id;
			$table_posts  = Model::get_wp_table_name( 'posts' );
			$post_content = (string) $post_content_history->old_value;
			clean_post_cache( $post_id );
			$wpdb->update( // phpcs:ignore
				$table_posts,
				array( 'post_content' => $post_content ),
				array( 'ID' => (int) $post_id ),
				array( '%s' ),
				array( '%d' )
			);
		}
	}

	/**
	 * Check if object is existed.
	 *
	 * @param  int $object_id Object id.
	 * @return bool           Is object existed in bool.
	 */
	public static function is_object_existed( $object_id ) {
		$sql    = 'SELECT id FROM ' . Model::get_wp_table_name( LASSO_POST_CONTENT_HISTORY ) . " WHERE object_id = $object_id";
		$result = Model::get_row( $sql );

		return (bool) $result;
	}

	/**
	 * Get post ids query
	 *
	 * @return string
	 */
	public static function get_post_ids_query() {
		return 'SELECT object_id FROM ' . Model::get_wp_table_name( LASSO_POST_CONTENT_HISTORY );
	}
}
