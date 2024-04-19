<?php
/**
 * Declare class Lasso_Object_Field
 *
 * @package Lasso\Library\Field
 */

namespace Lasso\Libraries\Field;

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;

use Lasso\Models\Fields;
use Lasso\Models\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Lasso_Object_Field
 *
 * @package Lasso\Libraries\Field
 */
class Lasso_Object_Field {
	/**
	 * ID
	 *
	 * @var int $id ID.
	 */
	private $id;

	/**
	 * Field name
	 *
	 * @var string $field_name Field name.
	 */
	private $field_name;

	/**
	 * Field type like text, textarea
	 *
	 * @var string $field_type Field type.
	 */
	private $field_type;

	/**
	 * Field description
	 *
	 * @var string $field_description Field description.
	 */
	private $field_description;

	// Lasso field type.
	const FIELD_TYPE_TEXT             = 'text';
	const FIELD_TYPE_LABEL            = 'label';
	const FIELD_TYPE_TEXT_AREA        = 'textarea';
	const FIELD_TYPE_NUMBER           = 'number';
	const FIELD_TYPE_RATING           = 'rating';
	const TABLE                       = 'lasso_fields';
	const FIELD_TYPE_BUTTON           = 'button';
	const FIELD_TYPE_IMAGE            = 'image';
	const FIELD_TYPE_BUTTON_PRIMARY   = 'Primary';
	const FIELD_TYPE_BUTTON_SECONDARY = 'Secondary';
	const FIELD_PRICE_DESCRIPTION     = 'Prices automatically update every 24 hours with integrations like Amazon.';

	const RATING_FIELD_ID  = 1;
	const PROS_FIELD_ID    = 2;
	const CONS_FIELD_ID    = 3;
	const DESCRIPTION_ID   = 99989;
	const IMAGE_FIELD_ID   = 99991;
	const PRIMARY_BTN_ID   = 99992;
	const SECONDARY_BTN_ID = 99993;
	const PRICE_ID         = 99994;

	/**
	 * Construction of Lasso_Object_Field
	 *
	 * @param array $data Model data.
	 */
	public function __construct( $data = array() ) {
		if ( isset( $data['id'] ) ) {
			$this->set_id( $data['id'] );
		}

		if ( isset( $data['field_name'] ) ) {
			$this->set_field_name( $data['field_name'] );
		}

		if ( isset( $data['field_type'] ) ) {
			$this->set_field_type( $data['field_type'] );
		}

		if ( isset( $data['field_description'] ) ) {
			$this->set_field_description( $data['field_description'] );
		}

	}

	/**
	 * Set id
	 *
	 * @param mixed $id ID.
	 */
	public function set_id( $id ) {
		$this->id = (int) $id;
	}

	/**
	 * Get Field id
	 *
	 * @return mixed
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get field ID
	 *
	 * @return mixed
	 */
	public function get_field_id() {
		return $this->get_id();
	}

	/**
	 * Get field name
	 *
	 * @return mixed
	 */
	public function get_field_name() {
		return $this->field_name;
	}

	/**
	 * Set field name
	 *
	 * @param mixed $field_name Field name.
	 */
	public function set_field_name( $field_name ): void {
		$this->field_name = $field_name;
	}

	/**
	 * Get field type
	 *
	 * @return mixed
	 */
	public function get_field_type() {
		return $this->field_type;
	}

	/**
	 * Set field type
	 *
	 * @param mixed $field_type Field type.
	 */
	public function set_field_type( $field_type ): void {
		$this->field_type = $field_type;
	}

	/**
	 * Get field description
	 *
	 * @return mixed
	 */
	public function get_field_description() {
		return $this->field_description;
	}

	/**
	 * Set field description
	 *
	 * @param mixed $field_description Field description.
	 */
	public function set_field_description( $field_description ): void {
		$this->field_description = $field_description;
	}


	/**
	 * Field type is text
	 *
	 * @return bool
	 */
	public function is_type_text() {
		return self::FIELD_TYPE_TEXT === $this->field_type;
	}

	/**
	 * Field type is textarea
	 *
	 * @return bool
	 */
	public function is_type_textarea() {
		return self::FIELD_TYPE_TEXT_AREA === $this->field_type;
	}

	/**
	 * Field type is number
	 *
	 * @return bool
	 */
	public function is_type_number() {
		return self::FIELD_TYPE_NUMBER === $this->field_type;
	}

	/**
	 * Field type is rating
	 *
	 * @return bool
	 */
	public function is_type_rating() {
		return self::FIELD_TYPE_RATING === $this->field_type;
	}

	/**
	 * Field type is image
	 *
	 * @return bool
	 */
	public function is_image() {
		return self::FIELD_TYPE_IMAGE === $this->field_type;
	}

	/**
	 * Field type is number
	 *
	 * @return bool
	 */
	public function is_product_name() {
		return 'product_name' === $this->field_type;
	}

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		return Model::get_wp_table_name( self::TABLE );
	}

	/**
	 * Get by id
	 *
	 * @param int $id ID.
	 *
	 * @return Lasso_Object_Field
	 */
	public static function get_by_id( $id ) {
		$cache_key      = Lasso_Cache_Per_Process::determine_cache_key( array( self::get_table_name(), $id ) );
		$cache_instance = Lasso_Cache_Per_Process::get_instance();
		$cache_value    = $cache_instance->get_cache( $cache_key );
		if ( $cache_value ) {
			return $cache_value;
		}

		$sql    = '
			SELECT
				*
			FROM ' . self::get_table_name() . ' WHERE id = %d';
		$result   = Model::get_row( Model::prepare( $sql, $id ), ARRAY_A ); // phpcs:ignore
		if ( ! empty( $result ) ) {
			$row = new self( $result );
			$cache_instance->set_cache( $cache_key, $row );
			return $row;
		}
		return null;
	}

	/**
	 * Default is Primary button and vice versa Secondary button
	 *
	 * @param bool $is_primary Flag to check primary or secondary button.
	 *
	 * @return bool
	 */
	public function is_button( $is_primary = true ) {
		if ( $is_primary ) {
			return self::PRIMARY_BTN_ID === $this->id;
		} else {
			return self::SECONDARY_BTN_ID === $this->id;
		}
	}

	/**
	 * Field type is button
	 *
	 * @return bool
	 */
	public function is_type_button() {
		return self::FIELD_TYPE_BUTTON === $this->field_type;
	}

	/**
	 * Field type is label
	 *
	 * @return bool
	 */
	public function is_type_label() {
		return self::FIELD_TYPE_LABEL === $this->field_type;
	}

	/**
	 * Get list built in field with ID is not (1, 2, 3 - Rating/Pros/Cons)
	 *
	 * @return array
	 */
	public static function get_built_in_field_page_table_details() {
		return array( self::DESCRIPTION_ID, self::IMAGE_FIELD_ID, self::PRIMARY_BTN_ID, self::SECONDARY_BTN_ID, self::PRICE_ID );
	}

	/**
	 * Check is price field.
	 *
	 * @return bool
	 */
	public function is_price_field() {
		return $this->is_type_label() && ( self::FIELD_PRICE_DESCRIPTION === $this->field_description );
	}

	/**
	 * Check is price field.
	 *
	 * @return bool
	 */
	public function is_description_field() {
		return ( ( Fields::DESCRIPTION_FIELD_ID === $this->id ) && ( $this->get_field_type() === Fields::FIELD_TYPE_EDITOR ) );
	}

}
