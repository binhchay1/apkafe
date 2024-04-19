<?php
/**
 * Declare class MetaData
 *
 * @package MetaData
 */

namespace Lasso\Models;

/**
 * MetaData
 */
class MetaData extends Model {

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_metadata';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'object_id',
		'type',
		'meta_key',
		'meta_value',
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
			`object_id` int(11) NOT NULL,
			`type` varchar(30) NOT NULL,
			`meta_key` varchar(50) NOT NULL,
			`meta_value` varchar(255) DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY  idx_object_id_type_meta_key (object_id, `type`, meta_key)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Delete rows in meta data table
	 *
	 * @param array  $object_ids An array object ID.
	 * @param string $type      Type.
	 */
	public static function delete_by_object_id( $object_ids, $type ) {
		$object_ids = implode( ',', $object_ids );
		$sql        = 'DELETE FROM ' . ( new self() )->get_table_name() . ' 
						WHERE object_id IN ( ' . $object_ids . ' ) AND type = %s ';
		$sql        = self::prepare( $sql, $type );
		self::query( $sql );
	}

	/**
	 * Add primary column
	 */
	public function add_primary_column() {
		$table = $this->get_table_name();

		if ( ! $this->are_columns_created( array( 'id' ) ) ) {
			$sql = 'ALTER TABLE ' . $table . ' ADD id INT PRIMARY KEY AUTO_INCREMENT';

			self::query( $sql );
		}

		return $this;
	}

	/**
	 * Update metadata
	 *
	 * @param string $type  Model name.
	 * @param int    $id    Object ID.
	 * @param string $key   Meta key.
	 * @param string $value Meta value.
	 */
	public static function update_metadata( $type, $id, $key, $value ) {
		$sql = '
			INSERT INTO ' . ( new self() )->get_table_name() . ' (type, object_id, meta_key, meta_value)
			VALUES (%s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE 
				meta_value = %s;
		';
		$sql = self::prepare( $sql, $type, $id, $key, $value, $value );

		return self::query( $sql );
	}

	/**
	 * Get metadata
	 *
	 * @param string $type    Model name.
	 * @param int    $id      Object ID.
	 * @param string $key     Meta key.
	 * @param mixed  $default Default value. Default to null.
	 */
	public static function get_metadata( $type, $id, $key, $default = null ) {
		$sql    = '
			SELECT meta_value
			FROM ' . ( new self() )->get_table_name() . '
			WHERE 
				type = %s 
				AND object_id = %s 
				AND meta_key = %s
		';
		$sql    = self::prepare( $sql, $type, $id, $key );
		$result = self::get_var( $sql );

		return $result || '0' === $result ? $result : $default;
	}
}
