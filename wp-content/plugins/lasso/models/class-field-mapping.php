<?php
/**
 * Models
 *
 * @package Models
 */

namespace Lasso\Models;

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;

use Lasso\Models\MetaData;

/**
 * Model
 */
class Field_Mapping extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_field_mapping';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'lasso_id',
		'field_id',
		'field_value',
		'field_order',
		'field_visible',
	);

	/**
	 * Primary key of the table
	 *
	 * @var string
	 */
	protected $primary_key = 'lasso_id';

	/**
	 * Create table
	 */
	public function create_table() {
		$columns_sql = '
			lasso_id bigint(20) NOT NULL,
			field_id bigint(20) NOT NULL,
			field_value varchar(1000) NOT NULL, 
			field_order int(4) NOT NULL DEFAULT 10,
			field_visible bool NOT NULL DEFAULT true,
			PRIMARY KEY  (lasso_id, field_id)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
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
		$table_name     = ( new self() )->get_table_name();
		$cache_key      = Lasso_Cache_Per_Process::determine_cache_key( array( $table_name, $lasso_id, $field_id ) );
		$cache_instance = Lasso_Cache_Per_Process::get_instance();
		$cache_value    = $cache_instance->get_cache( $cache_key );
		if ( $cache_value ) {
			return $cache_value;
		}

		$sql = '
			SELECT * FROM ' . $table_name . '
				WHERE lasso_id = %d AND field_id = %d
				ORDER BY field_order ASC';

		$sql    = self::get_wpdb()->prepare( $sql, $lasso_id, $field_id );
		$result = self::get_row( $sql );
		if ( ! empty( $result ) ) {
			$row = ( new self() )->map_properties( $result );
			$cache_instance->set_cache( $cache_key, $row );
			return $row;
		}
		return null;
	}

	/**
	 * Add a Field to a Product
	 *
	 * @param int $field_id Field id.
	 * @param int $post_id  Lasso post id.
	 */
	public static function add_field_to_page( $field_id, $post_id ) {
		$field_id      = intval( $field_id );
		$table_name    = ( new self() )->get_table_name();
		$default_value = Fields::RATING_FIELD_ID === $field_id ? '3.5' : ''; // ? default rating is 3.5

		$sql     = '
			INSERT INTO ' . $table_name . ' (lasso_id, field_id, field_value)
			VALUES (%d, %d, %s)
			ON DUPLICATE KEY UPDATE
				lasso_id = %d,
				field_id = %d
		';
		$prepare = self::prepare(
			$sql,
			$post_id,
			$field_id,
			$default_value,
			$post_id,
			$field_id
		);
		$result  = self::query( $prepare );

		return $result;
	}

	/**
	 * Insert and update field value on duplicate.
	 *
	 * @return bool|int
	 */
	public function insert_on_duplicate_update_field_value() {
		$table_name  = $this->get_table_name();
		$lasso_id    = $this->get_lasso_id();
		$field_id    = $this->get_field_id();
		$field_value = $this->get_field_value();
		$field_order = $this->get_field_order() ? $this->get_field_order() : 10;

		$sql = '
			INSERT INTO ' . $table_name . '
				(lasso_id, field_id, field_value, field_order, field_visible)
			VALUES
				(%s, %s, %s, %s, 1)
			ON DUPLICATE KEY UPDATE
				field_value = %s
		';

		$prepare = self::prepare(
			$sql,
			$lasso_id,
			$field_id,
			$field_value,
			$field_order,
			$field_value
		);

		return self::query( $prepare );
	}

	/**
	 * Get show field name value
	 *
	 * @param int $lasso_id Lasso ID.
	 * @param int $field_id Field ID.
	 *
	 * @return bool
	 */
	public static function get_show_field_name( $lasso_id, $field_id ) {
		return MetaData::get_metadata( 'Field_Mapping', $lasso_id, 'show_field_name_' . $field_id );
	}

	/**
	 * Set show field name value
	 *
	 * @param int    $lasso_id Lasso ID.
	 * @param int    $field_id Field ID.
	 * @param string $value    Value.
	 *
	 * @return string
	 */
	public static function set_show_field_name( $lasso_id, $field_id, $value ) {
		return MetaData::update_metadata( 'Field_Mapping', $lasso_id, 'show_field_name_' . $field_id, $value );
	}
}
