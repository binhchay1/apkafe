<?php
/**
 * Declare Class Table_Field_Group_Detail
 *
 * @package Lasso\Library\Table
 */

namespace Lasso\Libraries\Table;

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Enum;
use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Models\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Table_Field_Group_Detail
 *
 * @package Lasso\Libraries\Table
 */
class Table_Field_Group_Detail {

	const TABLE = 'lasso_table_field_group_detail';

	/**
	 * ID
	 *
	 * @var int $id ID..
	 */
	private $id;

	/**
	 * Field group ID
	 *
	 * @var string $field_group_id Field group ID.
	 */
	private $field_group_id;

	/**
	 * Lasso ID
	 *
	 * @var int $lasso_id Lasso ID.
	 */
	private $lasso_id;

	/**
	 * Field ID
	 *
	 * @var int $field_id Table id.
	 */
	private $field_id;

	/**
	 * Field value
	 *
	 * @var string $field_value Field value in table mapping.
	 */
	private $field_value;

	/**
	 * Order
	 *
	 * @var int $order Order.
	 */
	private $order;

	/**
	 * Table_Field_Group_Detail constructor.
	 *
	 * @param array $data Model data.
	 */
	public function __construct( $data = array() ) {
		if ( isset( $data['id'] ) ) {
			$this->set_id( $data['id'] );
		}

		if ( isset( $data['lasso_id'] ) ) {
			$this->set_lasso_id( $data['lasso_id'] );
		}

		if ( isset( $data['field_id'] ) ) {
			$this->set_field_id( $data['field_id'] );
		}

		if ( isset( $data['field_group_id'] ) ) {
			$this->set_field_group_id( $data['field_group_id'] );
		}

		if ( isset( $data['order'] ) ) {
			$this->set_order( $data['order'] );
		}

		if ( isset( $data['field_value'] ) ) {
			$this->set_field_value( $data['field_value'] );
		}
	}

	/**
	 * Get id.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set id.
	 *
	 * @param int $id Id.
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Get field group id.
	 *
	 * @return string
	 */
	public function get_field_group_id() {
		return $this->field_group_id;
	}

	/**
	 * Set field group id.
	 *
	 * @param string $field_group_id Field group id.
	 */
	public function set_field_group_id( $field_group_id ) {
		$this->field_group_id = $field_group_id;
	}

	/**
	 * Get lasso id.
	 *
	 * @return int
	 */
	public function get_lasso_id() {
		return $this->lasso_id;
	}

	/**
	 * Set lasso id.
	 *
	 * @param int $lasso_id Lasso id.
	 */
	public function set_lasso_id( $lasso_id ) {
		$this->lasso_id = (int) $lasso_id;
	}

	/**
	 * Get field id.
	 *
	 * @return int
	 */
	public function get_field_id() {
		return $this->field_id;
	}

	/**
	 * Set field id.
	 *
	 * @param int $field_id Field id.
	 */
	public function set_field_id( $field_id ) {
		$this->field_id = (int) $field_id;
	}

	/**
	 * Get order.
	 *
	 * @return mixed
	 */
	public function get_order() {
		return (int) $this->order;
	}

	/**
	 * Set order.
	 *
	 * @param mixed $order Order.
	 */
	public function set_order( $order = null ) {
		if ( empty( $order ) ) {
			$order = self::get_max_order_by_field_group_id_lasso_id( $this->get_field_group_id(), $this->get_lasso_id() );
			$order ++;
		}
		$this->order = (int) $order;
	}

	/**
	 * Get field value
	 *
	 * @return string
	 */
	public function get_field_value() {
		return $this->field_value;
	}

	/**
	 * Set field value
	 *
	 * @param string $field_value Field value.
	 */
	public function set_field_value( $field_value ) {
		$this->field_value = $field_value;
	}

	/**
	 * Get table name
	 *
	 * @return string
	 */
	public static function get_table_name() {
		return Model::get_wp_table_name( self::TABLE );
	}

	/**
	 * Insert new record
	 *
	 * @return $this
	 */
	public function insert() {
		global $wpdb;

		$sql    = '
			INSERT INTO ' . self::get_table_name() . '
			(`field_group_id`,`lasso_id`,`field_id`,`order`,`field_value`) VALUES 
			(%s, %d, %d, %d, %s);
		';
		$sql    = Model::prepare( $sql, $this->get_field_group_id(), $this->get_lasso_id(), $this->get_field_id(), $this->get_order(), $this->get_field_value() ); // phpcs:ignore
		$result = Model::query( $sql ); // phpcs:ignore
		if ( $result ) {
			$this->set_id( $wpdb->insert_id );
		}
		return $this;
	}

	/**
	 * Get order of field
	 *
	 * @param string $field_group_id Field group ID.
	 * @param int    $lasso_id       Lasso id.
	 * @return int
	 */
	public static function get_max_order_by_field_group_id_lasso_id( $field_group_id, $lasso_id ) {
		$sql    = '
			SELECT max(`order`) as `order` FROM ' . self::get_table_name() . ' WHERE field_group_id=%s AND lasso_id = %d
		';
		$sql    = Model::prepare( $sql, $field_group_id, $lasso_id ); // phpcs:ignore
		$result = Model::get_row( $sql, ARRAY_A ); // phpcs:ignore

		if ( ! empty( $result['order'] ) ) {
			return $result['order'];
		}
		return 0;
	}

	/**
	 * Get a record
	 *
	 * @param int $field_id       Field ID.
	 * @param int $field_group_id Field group ID.
	 * @param int $lasso_id       Lasso ID.
	 *
	 * @return Table_Field_Group_Detail|null
	 */
	public static function get_by_field_id_field_group_id_lasso_id( $field_id, $field_group_id, $lasso_id ) {
		$sql    = '
			SELECT * FROM ' . self::get_table_name() . ' WHERE lasso_id= %d AND field_id = %d AND field_group_id = %s ;
		';
		$sql    = Model::prepare( $sql, $lasso_id, $field_id, $field_group_id ); // phpcs:ignore
		$result = Model::get_row( $sql, ARRAY_A ); // phpcs:ignore

		if ( ! empty( $result ) ) {
			return new self( $result );
		}
		return null;
	}


	/**
	 * Get list by lasso id and field group id.
	 *
	 * @param int $lasso_id       Lasso id.
	 * @param int $field_group_id Field group id.
	 *
	 * @return Table_Field_Group_Detail[]
	 */
	public static function get_list_by_lasso_id_field_group_id( $lasso_id, $field_group_id ) {
		$cache_key      = Lasso_Cache_Per_Process::determine_cache_key( array( self::get_table_name(), $lasso_id, $field_group_id ) );
		$cache_instance = Lasso_Cache_Per_Process::get_instance();
		$cache_value    = $cache_instance->get_cache( $cache_key );
		if ( $cache_value ) {
			return $cache_value;
		}

		$sql = '
			SELECT * FROM ' . self::get_table_name() . ' WHERE lasso_id = %d AND field_group_id = %s ORDER BY `order` ASC
		';

		$sql     = Model::prepare( $sql, $lasso_id, $field_group_id ); // phpcs:ignore
		$results = Model::get_results( $sql, ARRAY_A ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = new self( $result );
			}
		}
		if ( ! empty( $list ) ) {
			$cache_instance->set_cache( $cache_key, $list );
		}
		return $list;
	}

	/**
	 * Get list field group id.
	 *
	 * @param int  $field_group_id Field group id.
	 * @param bool $is_group       Is group.
	 * @return Table_Field_Group_Detail[]
	 */
	public static function get_list_field_group_id( $field_group_id, $is_group = true ) {
		$group = '';
		if ( $is_group ) {
			$group = ' GROUP BY lasso_id ';
		}
		$sql = '
			SELECT * FROM ' . self::get_table_name() . ' WHERE field_group_id = %s ' . $group . ' ORDER BY `order` ASC
		';

		$sql     = Model::prepare( $sql, $field_group_id ); // phpcs:ignore
		$results = Model::get_results( $sql, ARRAY_A ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = new self( $result );
			}
		}
		return $list;
	}

	/**
	 * Get list by field group id and field id.
	 *
	 * @param int $field_group_id Field group id.
	 * @param int $field_id       Field id.
	 *
	 * @return Table_Field_Group_Detail[]
	 */
	public static function get_list_by_field_group_id_field_id( $field_group_id, $field_id ) {
		$sql = '
			SELECT * FROM ' . self::get_table_name() . ' WHERE field_group_id = %s AND field_id = %d ORDER BY `order` ASC
		';

		$sql     = Model::prepare( $sql, $field_group_id, $field_id ); // phpcs:ignore
		$results = Model::get_results( $sql, ARRAY_A ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = new self( $result );
			}
		}
		return $list;
	}

	/**
	 * Update a record
	 *
	 * @return $this
	 */
	public function update() {
		$sql = '
			UPDATE ' . self::get_table_name() . ' SET 
				`field_id`   = %d,
				`field_group_id`   = %s,
				`lasso_id`   = %d,
				`order` = %d,
				`field_value` = %s
			WHERE `id` = %d;
		';
		$sql = Model::prepare( $sql, $this->get_field_id(), $this->get_field_group_id(), $this->get_lasso_id(), $this->get_order(), $this->get_field_value(), $this->get_id() ); // phpcs:ignore
		Model::query( $sql ); // phpcs:ignore

		return $this;
	}

	/**
	 * Delete a row
	 */
	public function delete() {
		$sql = '
			DELETE FROM ' . self::get_table_name() . ' WHERE id = %d
		';
		$sql = Model::prepare( $sql, $this->get_id() ); // phpcs:ignore
		Model::query( $sql ); // phpcs:ignore
	}

	/**
	 * Get list by table id and field id.
	 *
	 * @param int $table_id Table id.
	 * @param int $field_id Field id.
	 *
	 * @return array
	 */
	public static function get_list_by_table_id_field_id( $table_id, $field_id ) {
		$sql = '
			SELECT b.* 
			FROM ' . Table_Field_Group::get_table_name() . ' a 
				INNER JOIN ' . self::get_table_name() . ' b 
				ON a.field_group_id = b.field_group_id 
					AND a.table_id = %d 
					AND b.field_id = %d 
			GROUP BY b.lasso_id
		';

		$sql     = Model::prepare( $sql, $table_id, $field_id ); // phpcs:ignore
		$results = Model::get_results( $sql, ARRAY_A ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = new self( $result );
			}
		}
		return $list;
	}


	/**
	 * Get list by table id and lasso id.
	 *
	 * @param int $table_id Table id.
	 * @param int $lasso_id Lasso id.
	 *
	 * @return Table_Field_Group_Detail[]
	 */
	public static function get_list_by_table_id_lasso_id( $table_id, $lasso_id ) {
		$cache_key      = Lasso_Cache_Per_Process::determine_cache_key( array( self::get_table_name(), $table_id, $lasso_id ) );
		$cache_instance = Lasso_Cache_Per_Process::get_instance();
		$cache_value    = $cache_instance->get_cache( $cache_key );

		if ( $cache_value ) {
			return $cache_value;
		}

		$sql = '
			SELECT b.* 
			FROM ' . Table_Field_Group::get_table_name() . ' a 
			INNER JOIN ' . self::get_table_name() . ' b
				ON a.field_group_id = b.field_group_id
			WHERE a.table_id = %d 
				AND b.lasso_id = %d 
			GROUP BY field_id 
			ORDER BY a.`order`, b.`order` ASC
		';

		$sql     = Model::prepare( $sql, $table_id, $lasso_id ); // phpcs:ignore
		$results = Model::get_results( $sql, ARRAY_A ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = new self( $result );
			}
		}
		$cache_instance->set_cache( $cache_key, $list );
		return $list;
	}

	/**
	 * Get list field id by table id.
	 *
	 * @param int  $table_id           Table id.
	 * @param bool $is_return_field_id Is return field id.
	 *
	 * @return array
	 */
	public static function get_list_field_id_by_table_id( $table_id, $is_return_field_id = false ) {
		$sql = '
			SELECT b.* 
			FROM ' . Table_Field_Group::get_table_name() . ' a 
			INNER JOIN ' . self::get_table_name() . ' b
				ON a.field_group_id = b.field_group_id
			WHERE a.table_id = %d 
			GROUP BY field_id
			ORDER BY a.`order`, b.field_group_id, b.`order` ASC
		';

		$sql     = Model::prepare( $sql, $table_id ); // phpcs:ignore
		$results = Model::get_results( $sql, ARRAY_A ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$row = new self( $result );
				if ( $is_return_field_id ) {
					$list[] = $row->get_field_id();
				} else {
					$list[] = $row;
				}
			}
		}
		return $list;
	}

	/**
	 * Get list field id by field group id.
	 *
	 * @param int $field_group_id Field group id.
	 *
	 * @return array
	 */
	public static function get_list_field_id_by_field_group_id( $field_group_id ) {
		$sql = '
			SELECT field_id FROM ' . self::get_table_name() . ' WHERE field_group_id = %s GROUP BY field_id ORDER BY `order` ASC
		';

		$sql     = Model::prepare( $sql, $field_group_id ); // phpcs:ignore
		$results = Model::get_results( $sql, ARRAY_A ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = $result['field_id'];
			}
		}
		return $list;
	}

	/**
	 * Get optimize image.
	 *
	 * @return mixed
	 */
	public function get_image() {
		$image_path = $this->get_field_value();

		// ? Priority webp image
		if ( '.webp' === strtolower( substr( $image_path, -5 ) ) ) {
			if ( Lasso_Helper::is_uploaded_file_existing( $image_path ) ) {
				return $image_path;
			} else { // ? If webp image was removed, we use the Lasso Post's image.
				$custom_thumbnail = get_post_meta( $this->get_lasso_id(), 'lasso_custom_thumbnail', true );
				$webp_thumbnail   = get_post_meta( $this->get_lasso_id(), Enum::LASSO_WEBP_THUMBNAIL, true );
				if ( ! empty( $webp_thumbnail ) && Lasso_Helper::is_uploaded_file_existing( $webp_thumbnail ) ) {
					return $webp_thumbnail;
				} elseif ( ! empty( $custom_thumbnail ) ) {
					return $custom_thumbnail;
				} else {
					return LASSO_DEFAULT_THUMBNAIL;
				}
			}
		}

		// ? Get webp image from Lasso metadata table
		$sql    = '
			SELECT base.meta_value
			FROM ' . Model::get_wp_table_name( 'lasso_metadata' ) . ' AS base
			WHERE base.object_id = %d
				AND base.type = %s
				AND base.meta_key = %s';
		$sql    = Model::get_wpdb()->prepare( $sql, $this->get_id(), 'Table_Field_Group_Detail', Enum::LASSO_WEBP_THUMBNAIL ); // phpcs:ignore
		$result = Model::get_row( $sql );

		if ( ! empty( $result ) && Lasso_Helper::is_uploaded_file_existing( $result->meta_value ) ) {
			return $result->meta_value;
		}

		return $image_path;
	}
}
