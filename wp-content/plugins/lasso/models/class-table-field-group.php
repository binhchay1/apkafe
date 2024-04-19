<?php
/**
 * Models
 *
 * @package Models
 */

namespace Lasso\Models;

/**
 * Model
 */
class Table_Field_Group extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_table_field_group';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'table_id',
		'lasso_id',
		'field_group_id',
		'field_id',

		'order',
	);

	/**
	 * Primary key of the table
	 *
	 * @var string
	 */
	protected $primary_key = 'id';

	/**
	 * Create table
	 */
	public function create_table() {
		$columns_sql = '
			id bigint(10) NOT NULL AUTO_INCREMENT,
			table_id bigint(10) NOT NULL,
			lasso_id bigint(10) NOT NULL,
			field_group_id varchar(30) NOT NULL,
			field_id bigint(11) NOT NULL,
			`order` tinyint(4) NULL DEFAULT NULL,
			PRIMARY KEY  (id),
			INDEX  idxTableIDLassoIDFieldGroupIDFieldID (table_id, lasso_id, field_group_id, field_id)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
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
		$this->data['order'] = (int) $order;
		return $this;
	}

	/**
	 * Get order of field order field or order within cell
	 *
	 * @param int $table_id Table ID.
	 * @return int|mixed
	 */
	public static function get_max_order_by_table_id( $table_id ) {
		$sql    = '
			SELECT max(`order`) as `order` 
			FROM ' . ( new self() )->get_table_name() . ' 
			WHERE table_id=%d
		';
		$sql    = self::prepare( $sql, $table_id );
		$result = self::get_row( $sql, ARRAY_A );

		if ( ! empty( $result['order'] ) ) {
			return $result['order'];
		}
		return 0;
	}

	/**
	 * Get list object by table id
	 *
	 * @param integer $table_id Table ID.
	 *
	 * @return $this[]
	 */
	public static function get_list_by_table_id( $table_id ) {
		$sql     = ' 
			SELECT * 
			FROM ' . ( new self() )->get_table_name() . ' 
			WHERE table_id = %d 
			ORDER BY `order` ASC 
		';
		$sql     = self::prepare( $sql, $table_id );
		$results = self::get_results( $sql );

		$list = array();
		foreach ( $results as $row ) {
			$inst   = new self();
			$list[] = $inst->map_properties( $row );
		}
		return $list;
	}

	/**
	 * Generate field group id.
	 *
	 * @return string
	 */
	public static function generate_field_group_id() {
		$field_group = uniqid( 'group_' );
		$sql         = '
			SELECT COUNT(*) AS `row` 
			FROM ' . ( new self() )->get_table_name() . ' 
			WHERE field_group_id = %s
		';
		$sql         = self::prepare( $sql, $field_group );
		$result      = self::get_row( $sql, ARRAY_A ); // phpcs:ignore
		if ( empty( $result['row'] ) ) {
			return $field_group;
		}
		return self::generate_field_group_id();
	}

	/**
	 * Get list field group id by table id.
	 *
	 * @param int $table_id Table id.
	 *
	 * @return array
	 */
	public static function get_list_field_group_id_by_table_id( $table_id ) {
		$inst = new self();
		$sql  = '
			SELECT field_group_id 
			FROM ' . $inst->get_table_name() . ' 
			WHERE table_id = %d 
			GROUP BY field_group_id 
			ORDER BY `order` ASC
		';

		$sql     = self::prepare( $sql, $table_id );
		$results = self::get_results( $sql, ARRAY_A );

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = $result['field_group_id'];
			}
		}
		return $list;
	}

	/**
	 * Get list by cell ID
	 *
	 * @param int $table_id Table id.
	 * @param int $lasso_id Lasso id.
	 *
	 * @return $this[]
	 */
	public static function get_list_field_by_table_id_lasso_id( $table_id, $lasso_id ) {
		$sql = '
			SELECT * 
			FROM ' . self::get_table_name() . ' 
			WHERE table_id = %d 
				AND lasso_id = %d 
			ORDER BY `order` ASC
		';

		$sql     = self::prepare( $sql, $table_id, $lasso_id ); // phpcs:ignore
		$results = self::get_results( $sql, ARRAY_A ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = new self( $result );
			}
		}
		return $list;
	}

	/**
	 * Get a record
	 *
	 * @param int $table_id Table ID.
	 * @param int $lasso_id Lasso ID.
	 * @param int $field_group_id Field group ID.
	 *
	 * @return $this|null
	 */
	public static function get_by_table_id_lasso_id_field_group_id( $table_id, $lasso_id, $field_group_id ) {
		$sql    = '
			SELECT * 
			FROM ' . ( new self() )->get_table_name() . ' 
			WHERE table_id = %d 
				AND lasso_id = %d 
				AND field_group_id = %s
		';
		$sql    = self::prepare( $sql, $table_id, $lasso_id, $field_group_id ); // phpcs:ignore
		$result = self::get_row( $sql ); // phpcs:ignore

		if ( ! empty( $result ) ) {
			return ( new self() )->map_properties( $result );
		}
		return null;
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
				( 
					SELECT count( * ) AS total_field 
					FROM ' . ( new self() )->get_table_name() . ' A 
					INNER JOIN ' . ( new Table_Field_Group_Detail() )->get_table_name() . ' B
						ON A.field_group_id = B.field_group_id 
							AND A.lasso_id = B.lasso_id 
					WHERE A.table_id= %d 
						AND B.lasso_id = %d 
					GROUP BY B.field_group_id 
				) AS results
		';

		$sql    = self::prepare( $sql, $table_id, $lasso_id ); // phpcs:ignore
		$result = self::get_row( $sql, ARRAY_A ); // phpcs:ignore

		if ( ! empty( $result['max'] ) ) {
			return (int) $result['max'];
		}
		return 0;
	}

	/**
	 * Get "field group" order. Return 0 if did not existed
	 *
	 * @param int    $table_id       Table ID.
	 * @param string $field_group_id Field group ID.
	 * @return int
	 */
	public static function get_field_group_order( $table_id, $field_group_id ) {
		$sql = '
			SELECT `order` 
			FROM ' . ( new self() )->get_table_name() . ' 
			WHERE table_id = %d 
				AND field_group_id = %s
		';

		$sql    = self::prepare( $sql, $table_id, $field_group_id ); // phpcs:ignore
		$result = self::get_var( $sql ); // phpcs:ignore

		return intval( $result );
	}
}
