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
class Fields extends Model {
	const TABLE = 'lasso_fields';

	const RATING_FIELD_ID       = 1;
	const PROS_FIELD_ID         = 2;
	const CONS_FIELD_ID         = 3;
	const DESCRIPTION_FIELD_ID  = 99989;
	const PRODUCT_NAME_FIELD_ID = 99990;
	const IMAGE_FIELD_ID        = 99991;
	const PRIMARY_BTN_ID        = 99992;
	const SECONDARY_BTN_ID      = 99993;
	const PRICE_ID              = 99994;

	// Lasso field type.
	const FIELD_TYPE_TEXT          = 'text';
	const FIELD_TYPE_LABEL         = 'label';
	const FIELD_TYPE_TEXT_AREA     = 'textarea';
	const FIELD_TYPE_NUMBER        = 'number';
	const FIELD_TYPE_RATING        = 'rating';
	const FIELD_TYPE_BUTTON        = 'button';
	const FIELD_TYPE_IMAGE         = 'image';
	const FIELD_TYPE_BULLETED_LIST = 'bulleted_list';
	const FIELD_TYPE_NUMBERED_LIST = 'numbered_list';
	const FIELD_TYPE_EDITOR        = 'editor';

	const FIELD_TYPE_PRODUCT_NAME = 'product_name';

	const FIELD_TYPE_BUTTON_PRIMARY   = 'Primary';
	const FIELD_TYPE_BUTTON_SECONDARY = 'Secondary';
	const FIELD_PRICE_DESCRIPTION     = 'Prices automatically update every 24 hours with integrations like Amazon.';

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_fields';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'field_name',
		'field_type',
		'field_description',
		'order',
	);

	/**
	 * Primary key of the table
	 *
	 * @var string
	 */
	protected $primary_key = 'id';

	/**
	 * Default data
	 *
	 * @var array
	 */
	protected $default_data = array(
		array( self::RATING_FIELD_ID, 'Primary Rating', self::FIELD_TYPE_RATING, 'Editor Rating. Position in display is fixed.', 6 ),
		array( self::PROS_FIELD_ID, 'Pros', self::FIELD_TYPE_TEXT_AREA, 'A list of all of the Pros. One per line.', 7 ),
		array( self::CONS_FIELD_ID, 'Cons', self::FIELD_TYPE_TEXT_AREA, 'A list of all of the Cons. One per line.', 8 ),
		array( self::DESCRIPTION_FIELD_ID, 'Description', self::FIELD_TYPE_EDITOR, 'Add rich text to describe a product.', 9 ),
		array( self::PRODUCT_NAME_FIELD_ID, 'Product name', self::FIELD_TYPE_PRODUCT_NAME, 'Product name.', 1 ),
		array( self::IMAGE_FIELD_ID, 'Product Image', self::FIELD_TYPE_IMAGE, 'Feature image.', 2 ),
		array( self::PRIMARY_BTN_ID, 'Primary Button', self::FIELD_TYPE_BUTTON, 'Button uses the Primary URL.', 3 ),
		array( self::SECONDARY_BTN_ID, 'Secondary Button', self::FIELD_TYPE_BUTTON, 'Button uses the Secondary URL.', 4 ),
		array( self::PRICE_ID, 'Price', self::FIELD_TYPE_LABEL, 'Prices automatically update every 24 hours with integrations like Amazon.', 5 ),
	);

	/**
	 * Get list built in field with ID is not (1, 2, 3 - Rating/Pros/Cons)
	 *
	 * @return array
	 */
	public static function get_built_in_field_page_table_details() {
		return array(
			self::PRODUCT_NAME_FIELD_ID,
			self::IMAGE_FIELD_ID,
			self::PRIMARY_BTN_ID,
			self::SECONDARY_BTN_ID,
			self::PRICE_ID,
			self::DESCRIPTION_FIELD_ID,
		);
	}

	/**
	 * Create table
	 */
	public function create_table() {
		$columns_sql = '
			id bigint(10) NOT NULL AUTO_INCREMENT,
			field_name varchar(80) NOT NULL,
			field_type varchar(50) NOT NULL,
			field_description varchar(1000) NOT NULL,
			`order` INT(11) NULL DEFAULT 99999,
			PRIMARY KEY  (id)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Add default data
	 */
	public function add_default_data() {
		$default_data = array_map(
			function( $v ) {
				return "('" . implode( "', '", $v ) . "')";
			},
			$this->get_default_data()
		);
		$default_data = implode( ', ', $default_data );

		// ? ignore duplicated rows when inserting: INSERT IGNORE INTO
		$query = '
			INSERT INTO  ' . $this->get_table_name() . ' 
				(id, field_name, field_type, field_description, `order`)
			VALUES 
				' . $default_data . '
			ON DUPLICATE KEY UPDATE 
				field_type = values(field_type),
				field_description = values(field_description), 
				`order` = values(`order`)
		';

		return self::query( $query );
	}

	/**
	 * Field type is text
	 *
	 * @return bool
	 */
	public function is_type_text() {
		return self::FIELD_TYPE_TEXT === $this->get_field_type();
	}

	/**
	 * Field type is textarea
	 *
	 * @return bool
	 */
	public function is_type_textarea() {
		return self::FIELD_TYPE_TEXT_AREA === $this->get_field_type();
	}

	/**
	 * Field type is number
	 *
	 * @return bool
	 */
	public function is_type_number() {
		return self::FIELD_TYPE_NUMBER === $this->get_field_type();
	}

	/**
	 * Field type is rating
	 *
	 * @return bool
	 */
	public function is_type_rating() {
		return self::FIELD_TYPE_RATING === $this->get_field_type();
	}

	/**
	 * Field type is image
	 *
	 * @return bool
	 */
	public function is_type_image() {
		return self::FIELD_TYPE_IMAGE === $this->get_field_type();
	}

	/**
	 * Field type is button
	 *
	 * @return bool
	 */
	public function is_type_button() {
		return self::FIELD_TYPE_BUTTON === $this->get_field_type();
	}

	/**
	 * Field type is number
	 *
	 * @return bool
	 */
	public function is_product_name() {
		return 'product_name' === $this->get_field_type();
	}

	/**
	 * Field type is label
	 *
	 * @return bool
	 */
	public function is_type_label() {
		return self::FIELD_TYPE_LABEL === $this->get_field_type();
	}

	/**
	 * Check is price field.
	 *
	 * @return bool
	 */
	public function is_price_field() {
		return $this->is_type_label() && ( self::FIELD_PRICE_DESCRIPTION === $this->get_field_description() );
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
			return self::PRIMARY_BTN_ID === (int) $this->get_id();
		} else {
			return self::SECONDARY_BTN_ID === (int) $this->get_id();
		}
	}

	/**
	 * Restore built-in fields type
	 */
	public function restore_fields_type() {
		$default_data = array_map(
			function( $v ) {
				unset( $v[1] );
				return "('" . implode( "', '", $v ) . "')";
			},
			$this->get_default_data()
		);

		$default_data = implode( ', ', $default_data );

		// ? ignore duplicated rows when inserting: INSERT IGNORE INTO
		$query = '
			INSERT INTO  ' . $this->get_table_name() . ' 
				(id, field_type, field_description, `order`)
			VALUES 
				' . $default_data . '
			ON DUPLICATE KEY UPDATE 
				field_type = values(field_type),
				field_description = values(field_description), 
				`order` = values(`order`)
		';
		return self::query( $query );
	}

	/**
	 * Get by ID
	 *
	 * @param integer $id ID.
	 *
	 * @return $this
	 */
	public static function get_by_id( $id ) {
		return ( new self() )->get_one( $id );
	}

	/**
	 * Check is price field.
	 *
	 * @return bool
	 */
	public function is_description_field() {
		return ( ( self::DESCRIPTION_FIELD_ID === (int) $this->get_id() ) && ( $this->get_field_type() === self::FIELD_TYPE_EDITOR ) );
	}

	/**
	 * Get field mapping for product
	 *
	 * @param int    $lasso_id Lasso id.
	 * @param string $search   Search text. Default to '%%'. Ex: '%search text%'.
	 */
	public static function get_fields_for_product_query( $lasso_id, $search = '%%' ) {
		$field_mapping_tbl = ( new Field_Mapping() )->get_table_name();
		$field_tbl         = ( new self() )->get_table_name();
		$sql               = '
			SELECT 
				lasso_id, field_name, field_id, field_type, field_description, field_value, field_visible
			FROM ' . $field_mapping_tbl . ' AS fm
				INNER JOIN ' . $field_tbl . ' AS f
				ON fm.field_id = f.id
			WHERE
				lasso_id = %d
				AND field_name LIKE %s
			ORDER BY
				field_order ASC
		';
		$prepare = self::prepare( $sql, $lasso_id, $search ); // phpcs:ignore

		return $prepare;
	}
}
