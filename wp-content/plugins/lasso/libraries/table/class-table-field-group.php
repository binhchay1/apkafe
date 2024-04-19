<?php
/**
 * Declare class Table_Field_Group
 *
 * @package Lasso\Libraries\Table
 */

namespace Lasso\Libraries\Table;

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Table_Detail as Lasso_Table_Detail;

use Lasso\Models\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Table_Field_Group
 *
 * @package Lasso\Libraries\Table
 */
class Table_Field_Group {
	const TABLE = 'lasso_table_field_group';

	/**
	 * ID
	 *
	 * @var int $id ID..
	 */
	private $id;

	/**
	 * Table ID
	 *
	 * @var int $table_id Table id.
	 */
	private $table_id;

	/**
	 * Lasso ID
	 *
	 * @var int $lasso_id Lasso id.
	 */
	private $lasso_id;

	/**
	 * Field ID
	 *
	 * @var int $field_id Field ID.
	 */
	private $field_id;

	/**
	 * Field group ID
	 *
	 * @var int $field_group_id Field group ID.
	 */
	private $field_group_id;

	/**
	 * Lasso ID
	 *
	 * @var int $order Lasso id.
	 */
	private $order;

	/**
	 * Table_Field_Group constructor.
	 *
	 * @param array $data Model data.
	 */
	public function __construct( $data = array() ) {
		if ( isset( $data['id'] ) ) {
			$this->set_id( $data['id'] );
		}

		if ( isset( $data['table_id'] ) ) {
			$this->set_table_id( $data['table_id'] );
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
	 * Get table id.
	 *
	 * @return int
	 */
	public function get_table_id() {
		return $this->table_id;
	}

	/**
	 * Set table id.
	 *
	 * @param int $table_id Table id.
	 */
	public function set_table_id( $table_id ) {
		$this->table_id = $table_id;
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
		$this->lasso_id = $lasso_id;
	}

	/**
	 * Get field id.
	 *
	 * @return int
	 */
	public function get_field_id() {
		return (int) $this->field_id;
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
	 * Get field group id.
	 *
	 * @return int
	 */
	public function get_field_group_id() {
		return $this->field_group_id;
	}

	/**
	 * Set field group ip.
	 *
	 * @param int $field_group_id Field group id.
	 */
	public function set_field_group_id( $field_group_id ) {
		$this->field_group_id = $field_group_id;
	}


	/**
	 * Get order.
	 *
	 * @return int
	 */
	public function get_order() {
		return $this->order;
	}

	/**
	 * Set order.
	 *
	 * @param int $order Order.
	 */
	public function set_order( $order = null ) {
		if ( empty( $order ) ) {
			$order = self::get_max_order_by_table_id( $this->get_table_id() );
			$order ++;
		}
		$this->order = $order;
	}

	/**
	 * Generate field group id.
	 *
	 * @return string
	 */
	public static function generate_field_group_id() {
		$field_group = uniqid( 'group_' );
		global $wpdb;
		$sql    = '
			SELECT COUNT(*) as `row` FROM ' . self::get_table_name() . ' WHERE field_group_id = %s
		';
		$sql    = Model::prepare( $sql, $field_group ); // phpcs:ignore
		$result = Model::get_row( $sql, ARRAY_A ); // phpcs:ignore
		if ( empty( $result['row'] ) ) {
			return $field_group;
		}
		return self::generate_field_group_id();
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
			(`table_id`, `lasso_id`, `field_id`, `field_group_id`, `order`) VALUES 
			(%d, %d, %d, %s, %d);
		';
		$sql    = Model::prepare( $sql, $this->get_table_id(), $this->get_lasso_id(), $this->get_field_id(), $this->get_field_group_id(), $this->get_order() ); // phpcs:ignore
		$result = Model::query( $sql ); // phpcs:ignore
		if ( $result ) {
			$this->set_id( $wpdb->insert_id );
		}

		return $this;
	}

	/**
	 * Update function.
	 *
	 * @return $this
	 */
	public function update() {
		$sql = '
			UPDATE ' . self::get_table_name() . ' SET 
				`table_id`       = %d,
				`lasso_id`       = %s,
				`field_group_id` = %s,
				`field_id`       = %d,
				`order`          = %d
			WHERE `id` = %d;
		';
		$sql = Model::prepare( $sql, $this->get_table_id(), $this->get_lasso_id(), $this->get_field_group_id(), $this->get_field_id(), $this->get_order(), $this->get_id() ); // phpcs:ignore
		Model::query( $sql ); // phpcs:ignore

		return $this;
	}

	/**
	 * Get order of field order field or order within cell
	 *
	 * @param int $table_id Table ID.
	 * @return int|mixed
	 */
	public static function get_max_order_by_table_id( $table_id ) {
		global $wpdb;

		$sql    = '
			SELECT max(`order`) as `order` FROM ' . self::get_table_name() . ' WHERE table_id=%d
		';
		$sql    = Model::prepare( $sql, $table_id ); // phpcs:ignore
		$result = Model::get_row( $sql, ARRAY_A ); // phpcs:ignore

		if ( ! empty( $result['order'] ) ) {
			return $result['order'];
		}
		return 0;
	}

	/**
	 * Get a record
	 *
	 * @param int $table_id Table ID.
	 * @param int $lasso_id Lasso ID.
	 * @param int $field_group_id Field group ID.
	 *
	 * @return Table_Field_Group|null
	 */
	public static function get_by_table_id_lasso_id_field_group_id( $table_id, $lasso_id, $field_group_id ) {
		$sql    = '
			SELECT * FROM ' . self::get_table_name() . ' WHERE table_id = %d AND lasso_id = %d AND field_group_id = %s ;
		';
		$sql    = Model::prepare( $sql, $table_id, $lasso_id, $field_group_id ); // phpcs:ignore
		$result = Model::get_row( $sql, ARRAY_A ); // phpcs:ignore

		if ( ! empty( $result ) ) {
			return new self( $result );
		}
		return null;
	}

	/**
	 * Get list by cell ID
	 *
	 * @param int $table_id Table id.
	 * @param int $lasso_id Lasso id.
	 *
	 * @return Table_Field_Group[]
	 */
	public static function get_list_field_by_table_id_lasso_id( $table_id, $lasso_id ) {
		$sql = '
			SELECT * FROM ' . self::get_table_name() . ' WHERE table_id = %d AND lasso_id = %d ORDER BY `order` ASC
		';

		$sql     = Model::prepare( $sql, $table_id, $lasso_id ); // phpcs:ignore
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
	 * Get list fields in the table
	 *
	 * @param int    $table_id Table ID.
	 * @param string $mode     Display mode.
	 *
	 * @return array|object|null
	 */
	public static function get_list_distinct_field_by_table_id( $table_id, $mode = Lasso_Table_Detail::MODE_DISPLAY ) {
		if ( Lasso_Table_Detail::MODE_DISPLAY === $mode ) {
			$sql = '
				SELECT DISTINCT(field_id) FROM ' . self::get_table_name() . ' WHERE table_id=%d AND field_id != 0
			';
		} else {
			$sql = '
				SELECT field_id FROM ' . self::get_table_name() . ' WHERE table_id=%d GROUP BY field_group_id
			';
		}
		$sql .= ' ORDER BY `order` ASC ';

		$sql     = Model::prepare( $sql, $table_id ); // phpcs:ignore
		$results = Model::get_results( $sql, ARRAY_A ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = (int) $result['field_id'];
			}
		}
		return $list;
	}

	/**
	 * Get a record
	 *
	 * @param int $table_id Table ID.
	 * @param int $lasso_id Lasso ID.
	 * @param int $field_id Field ID.
	 *
	 * @return Table_Field_Group|null
	 */
	public static function get_by_table_id_lasso_id_field_id( $table_id, $lasso_id, $field_id ) {
		$cache_key      = Lasso_Cache_Per_Process::determine_cache_key( array( self::get_table_name(), $table_id, $lasso_id, $field_id ) );
		$cache_instance = Lasso_Cache_Per_Process::get_instance();
		$cache_value    = $cache_instance->get_cache( $cache_key );
		if ( $cache_value ) {
			return $cache_value;
		}

		$sql    = '
			SELECT * FROM ' . self::get_table_name() . ' WHERE table_id = %d AND lasso_id = %d AND field_id = %d ORDER BY `order` ASC;
		';
		$sql    = Model::prepare( $sql, $table_id, $lasso_id, $field_id ); // phpcs:ignore
		$result = Model::get_row( $sql, ARRAY_A ); // phpcs:ignore

		if ( ! empty( $result ) ) {
			$row = new self( $result );
			$cache_instance->set_cache( $cache_key, $row );
			return $row;
		}
		return null;
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
	 * Get list by table id field group id.
	 *
	 * @param int $table_id       Table id.
	 * @param int $field_group_id Field group id.
	 *
	 * @return Table_Field_Group[]
	 */
	public static function get_list_by_table_id_field_group_id( $table_id, $field_group_id ) {
		$cache_key      = Lasso_Cache_Per_Process::determine_cache_key( array( self::get_table_name(), $table_id, $field_group_id ) );
		$cache_instance = Lasso_Cache_Per_Process::get_instance();
		$cache_value    = $cache_instance->get_cache( $cache_key );
		if ( $cache_value ) {
			return $cache_value;
		}

		$sql = '
			SELECT * FROM ' . self::get_table_name() . ' WHERE table_id = %d AND field_group_id = %s
		';

		$sql     = Model::prepare( $sql, $table_id, $field_group_id ); // phpcs:ignore
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
	 * Get max quantity of field by table id and lasso id.
	 *
	 * @param int $table_id Table id.
	 * @param int $lasso_id Lasso id.
	 *
	 * @return int
	 */
	public static function get_max_quantity_of_field_by_table_id_lasso_id( $table_id, $lasso_id ) {
		$sql = '
			SELECT max( total_field ) AS `max`
			FROM 
				( SELECT count( * ) AS total_field FROM ' . self::get_table_name() . ' A INNER JOIN ' . Table_Field_Group_Detail::get_table_name() . ' B
				ON A.field_group_id = B.field_group_id AND A.lasso_id = B.lasso_id WHERE A.table_id= %d AND B.lasso_id = %d GROUP BY B.field_group_id )
				AS results
		';

		$sql    = Model::prepare( $sql, $table_id, $lasso_id ); // phpcs:ignore
		$result = Model::get_row( $sql, ARRAY_A ); // phpcs:ignore

		if ( ! empty( $result['max'] ) ) {
			return (int) $result['max'];
		}
		return 0;
	}

	/**
	 * Get list field group id by table id.
	 *
	 * @param int $table_id Table id.
	 *
	 * @return array
	 */
	public static function get_list_field_group_id_by_table_id( $table_id ) {
		$sql = '
			SELECT field_group_id FROM ' . self::get_table_name() . ' WHERE table_id = %d GROUP BY field_group_id ORDER BY `order` ASC
		';

		$sql     = Model::prepare( $sql, $table_id ); // phpcs:ignore
		$results = Model::get_results( $sql, ARRAY_A ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = $result['field_group_id'];
			}
		}
		return $list;
	}

}
