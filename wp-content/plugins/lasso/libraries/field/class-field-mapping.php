<?php
/**
 * Declare class Field_Mapping
 *
 * @package Lasso\Library\Field
 */

namespace Lasso\Libraries\Field;

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Models\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Field_Mapping
 *
 * @package Lasso\Libraries\Field
 */
class Field_Mapping {

	/**
	 * Lasso id
	 *
	 * @var int $lasso_id Lasso ID.
	 */
	private $lasso_id;

	/**
	 * Field ID
	 *
	 * @var int $field_id Field ID.
	 */
	private $field_id;

	/**
	 * Field value
	 *
	 * @var string $field_value Field value.
	 */
	private $field_value;

	/**
	 * Field order
	 *
	 * @var int $field_order Field order.
	 */
	private $field_order;

	/**
	 * Field visiable
	 *
	 * @var int $field_visible Field visible.
	 */
	private $field_visible;

	/**
	 * Construction of Field_Mapping
	 *
	 * @param array $data Model data.
	 */
	public function __construct( $data = array() ) {
		if ( isset( $data['field_id'] ) ) {
			$this->set_field_id( $data['field_id'] );
		}

		if ( isset( $data['lasso_id'] ) ) {
			$this->set_lasso_id( $data['lasso_id'] );
		}

		if ( isset( $data['field_value'] ) ) {
			$this->set_field_value( $data['field_value'] );
		}

		if ( isset( $data['field_order'] ) ) {
			$this->set_field_order( $data['field_order'] );
		}

		if ( isset( $data['field_visible'] ) ) {
			$this->set_field_visible( $data['field_visible'] );
		}
	}

	/**
	 * Get lasso id
	 *
	 * @return mixed
	 */
	public function get_lasso_id() {
		return $this->lasso_id;
	}

	/**
	 * Set lasso id
	 *
	 * @param mixed $lasso_id Lasso ID.
	 */
	public function set_lasso_id( $lasso_id ) {
		$this->lasso_id = (int) $lasso_id;
	}

	/**
	 * Get Field id
	 *
	 * @return mixed
	 */
	public function get_field_id() {
		return $this->field_id;
	}

	/**
	 * Field ID
	 *
	 * @param int $field_id Field ID.
	 */
	public function set_field_id( $field_id ) {
		$this->field_id = (int) $field_id;
	}

	/**
	 * Get field value
	 *
	 * @return mixed
	 */
	public function get_field_value() {
		return $this->field_value;
	}

	/**
	 * Set field value
	 *
	 * @param mixed $field_value Field value.
	 */
	public function set_field_value( $field_value ) {
		$this->field_value = $field_value;
	}

	/**
	 * Get field order
	 *
	 * @return mixed
	 */
	public function get_field_order() {
		return $this->field_order;
	}

	/**
	 * Set field order
	 *
	 * @param int|null $field_order Field order.
	 */
	public function set_field_order( $field_order = null ) {
		if ( ! isset( $field_order ) ) {
			$field_order = self::get_max_order_lasso_id( $this->get_lasso_id() );
			$field_order ++;
		}
		$this->field_order = (int) $field_order;
	}

	/**
	 * Get field visible
	 *
	 * @return mixed
	 */
	public function get_field_visible() {
		return $this->field_visible;
	}

	/**
	 * Set field value
	 *
	 * @param mixed $field_visible Field visible.
	 */
	public function set_field_visible( $field_visible ) {
		$value = $field_visible;
		if ( Lasso_Helper::compare_string( $field_visible, 'on' ) ) {
			$value = 1;
		} elseif ( Lasso_Helper::compare_string( $field_visible, 'off' ) ) {
			$value = 0;
		}
		$this->field_visible = $value;
	}

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		return Model::get_wp_table_name( LASSO_FIELD_MAPPING );
	}

	/**
	 * Get a field mapping by Field ID and Lasso ID
	 *
	 * @param int $lasso_id Lasso ID.
	 * @param int $field_id Field ID.
	 *
	 * @return Field_Mapping|null
	 */
	public static function get_by_lasso_id_field_id( $lasso_id, $field_id ) {
		$cache_key      = Lasso_Cache_Per_Process::determine_cache_key( array( self::get_table_name(), $lasso_id, $field_id ) );
		$cache_instance = Lasso_Cache_Per_Process::get_instance();
		$cache_value    = $cache_instance->get_cache( $cache_key );
		if ( $cache_value ) {
			return $cache_value;
		}

		$sql    = '
			SELECT
				*
			FROM ' . self::get_table_name() . '
			
			WHERE 
				lasso_id = %d AND field_id = %d
			ORDER BY
				field_order ASC';
		$result   = Model::get_row( Model::prepare( $sql, $lasso_id, $field_id ), ARRAY_A ); // phpcs:ignore
		if ( ! empty( $result ) ) {
			$row = new self( $result );
			$cache_instance->set_cache( $cache_key, $row );
			return $row;
		}
		return null;
	}

	/**
	 * Insert new record
	 *
	 * @return $this
	 */
	public function insert() {
		$sql = '
			INSERT INTO ' . self::get_table_name() . '
			(`lasso_id`, `field_id`, `field_value`, `field_order`, `field_visible`) VALUES 
			(%d, %s, %s, %d, %d);
		';
		$sql    = Model::prepare( $sql, $this->get_lasso_id(), $this->get_field_id(), $this->get_field_value(), $this->get_field_order(), $this->get_field_visible() ); // phpcs:ignore
		Model::query( $sql ); // phpcs:ignore

		return $this;
	}

	/**
	 * Update a record
	 *
	 * @return $this
	 */
	public function update() {
		$sql = '
			UPDATE ' . self::get_table_name() . ' SET 
				`field_value`   = %s,
				`field_order`   = %d,
				`field_visible` = %d
			WHERE `lasso_id` = %d and field_id = %d;
		';
		$sql = Model::prepare( $sql, $this->get_field_value(), $this->get_field_order(), $this->get_field_visible(), $this->get_lasso_id(), $this->get_field_id() ); // phpcs:ignore
		Model::query( $sql ); // phpcs:ignore

		return $this;
	}

	/**
	 * Get order of field
	 *
	 * @param int $lasso_id Lasso ID.
	 *
	 * @return int|mixed
	 */
	public static function get_max_order_lasso_id( $lasso_id ) {
		$sql    = '
			SELECT max(`field_order`) as `order` FROM ' . self::get_table_name() . ' WHERE lasso_id=%d
		';
		$sql    = Model::prepare( $sql, $lasso_id ); // phpcs:ignore
		$result = Model::get_row( $sql, ARRAY_A ); // phpcs:ignore

		if ( ! empty( $result ) ) {
			return $result['order'];
		}
		return 0;
	}


}
