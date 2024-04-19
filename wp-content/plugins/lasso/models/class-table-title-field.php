<?php
/**
 * Models
 *
 * @package Models
 */

namespace Lasso\Models;

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;

/**
 * Model
 *
 * @deprecated
 */
class Table_Title_Field extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_table_title_field';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'lasso_id',
		'field_id',
		'order',
		'table_id',

		'field_value',
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
			`id` bigint(10) NOT NULL AUTO_INCREMENT,
			`lasso_id` bigint(10) NULL DEFAULT NULL,
			`field_id` bigint(10) NULL DEFAULT NULL,
			`order` int(4) NULL DEFAULT NULL,
			`table_id` bigint(10) NULL DEFAULT NULL,
			`field_value` varchar(1000) NULL DEFAULT NULL,
			PRIMARY KEY  (`id`),
			INDEX  idxTableIDLassoIDFieldID (lasso_id, field_id, table_id)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Get list object by Table ID
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
		$list    = array();
		foreach ( $results as $row ) {
			$inst   = new self();
			$list[] = $inst->map_properties( $row );
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
		$table_name     = ( new self() )->get_table_name();
		$cache_key      = Lasso_Cache_Per_Process::determine_cache_key( array( $table_name, $table_id, $lasso_id ) );
		$cache_instance = Lasso_Cache_Per_Process::get_instance();
		$cache_value    = $cache_instance->get_cache( $cache_key );
		if ( $cache_value ) {
			return $cache_value;
		}
		$sql = '
			SELECT * 
			FROM ' . $table_name . ' 
			WHERE table_id = %d 
				AND lasso_id = %d 
			ORDER BY `order` ASC
		';

		$sql     = self::prepare( $sql, $table_id, $lasso_id ); // phpcs:ignore
		$results = self::get_results( $sql ); // phpcs:ignore

		$list = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$list[] = ( new self() )->map_properties( $result );
			}
		}
		if ( ! empty( $list ) ) {
			$cache_instance->set_cache( $cache_key, $list );
		}
		return $list;
	}
}
