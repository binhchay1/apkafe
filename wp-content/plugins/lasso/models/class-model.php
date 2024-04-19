<?php
/**
 * Models
 *
 * @package Models
 */

namespace Lasso\Models;

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Activator as Lasso_Activator;
use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Model
 *
 * HOW TO USE?
 * 1. Create a new class object (child class): $model = new Model_Child_Class();
 * 2. Insert:
 *      a. Set data for columns: $model->set_[column_name]($value);
 *      b. Insert data: $model->insert();
 * 3. Update:
 *      a. Update a model object after using get_one($id):
 *          i. Just update what columns you want: $model->set_[column_name]($value);
 *          ii. Update data: $model->update();
 *      b. Update a non-model object:
 *          i. Just update what columns you want: $model->set_[column_name]($value);
 *          ii. Update data: $model->update($id);
 *  4. Delete:
 *      a. Delete a model object after using get_one($id): $model->delete();
 *      b. Delete a non-model object: $model->delete($id);
 *
 * COMMON FUNCTIONS
 * 1. Get table name: $model->get_table_name();
 * 2. Get a record: $model->get_one($id);
 * 3. Get all records: $model->get_all($limit, $page);
 * 4. Get prefix of the table in WP: Model::get_prefix();
 * 5. Get table name of WP (with prefix): Model::get_wp_table_name('posts');
 * 6. Create the table: $model->create_table();
 * 7. Add default data: $model->add_default_data();
 *
 * WPDB CLASS: https://developer.wordpress.org/reference/classes/wpdb/
 * 1. Get a record: Model::get_row($sql, $output, $enable_cache);
 * 2. Get multiple records: Model::get_results($sql, $output, $enable_cache);
 * 3. Get a var: Model::get_var($sql, $enable_cache);
 * 4. Get a column: Model::get_col($sql, $enable_cache);
 * 5. Run a general query: Model::query($sql);
 * 5. Replace a row (update or create if it doesn't exist): Model::replace($table, $data, $format);
 */
abstract class Model {
	const LASSO_RECREATE_TABLES_LIMIT_TIMES = 3;

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns;

	/**
	 * Primary key of the table
	 *
	 * @var string
	 */
	protected $primary_key;

	/**
	 * Data from DB
	 *
	 * @var object $data
	 */
	protected $data;

	/**
	 * Default
	 *
	 * @var mixed $default_data
	 */
	protected $default_data;

	/**
	 * Data from DB
	 *
	 * @var bool $is_db_loaded
	 */
	protected $is_db_loaded = false;

	/**
	 * Table charset
	 *
	 * @var array $table_charset
	 */
	protected $table_charset = array();

	/**
	 * Table collation
	 *
	 * @var array $table_collation
	 */
	protected $table_collation = array();

	/**
	 * Table collation default
	 *
	 * @var array $table_collation_default
	 */
	protected $table_collation_default = array();

	/**
	 * Column meta
	 *
	 * @var array $col_meta
	 */
	protected $col_meta = array();

	/**
	 * Create table
	 */
	abstract public function create_table();

	/**
	 * Use to check object is mapped properties
	 *
	 * @var bool $is_map_properties
	 */
	private $is_map_properties = false;

	/**
	 * Model constructor.
	 *
	 * @param object $object An object.
	 */
	public function __construct( $object = null ) {
		if ( ! is_null( $object ) && ! $this->is_map_properties ) {
			$this->map_properties( $object );
		}
	}

	/**
	 * Get wpdb
	 *
	 * @return wpdb WP wpdb class.
	 */
	public static function get_wpdb() {
		global $wpdb;

		return $wpdb;
	}

	/**
	 * Get WP prefix
	 *
	 * @return string WP prefix of table.
	 */
	public static function get_db_name() {
		return self::get_wpdb()->dbname;
	}

	/**
	 * Get WP prefix
	 *
	 * @return string WP prefix of table.
	 */
	public static function get_prefix() {
		return self::get_wpdb()->prefix;
	}

	/**
	 * Get Lasso table name
	 *
	 * @return string Get current table name in WP.
	 */
	public function get_table_name() {
		return self::get_prefix() . $this->table;
	}

	/**
	 * Get WP table name
	 *
	 * @param string $table Table name in DB.
	 *
	 * @return string Table name in WP.
	 */
	public static function get_wp_table_name( $table ) {
		return self::get_prefix() . $table;
	}

	/**
	 * Get all columns of the table
	 */
	public function get_all_columns() {
		return $this->columns;
	}

	/**
	 * Get default data
	 */
	public function get_default_data() {
		return $this->default_data;
	}

	/**
	 * Get a row in DB
	 *
	 * @param int   $id             Id in DB. Default to null.
	 * @param array $select_columns Custom selecting the columns. Default to empty array.
	 *
	 * @return array|object
	 */
	public function get_one( $id = null, $select_columns = array() ) {
		$method = 'get_' . $this->primary_key;
		$id     = $id ? $id : $this->$method();

		return $this->get_one_by_col( $this->primary_key, $id, $select_columns );
	}

	/**
	 * Get all rows in DB
	 *
	 * @param int   $limit          Number of rows are returned, 0 is no limit. Default to 10.
	 * @param int   $page           Page number. Default to 1.
	 * @param array $select_columns Custom selecting the columns. Default to empty array.
	 *
	 * @return array|object
	 */
	public function get_all( $limit = 10, $page = 1, $select_columns = array() ) {
		$select = $this->build_select_columns( $select_columns );

		$sql = '
			SELECT ' . $select . '
			FROM ' . $this->get_table_name() . '
		';

		if ( $limit > 0 ) {
			$index = ( $page - 1 ) * $limit;

			$sql .= ' LIMIT %d OFFSET %d';
			$sql  = self::prepare( $sql, $limit, $index );
		}

		return $this->get_results( $sql );
	}

	/**
	 * Insert a row in DB
	 */
	public function insert() {
		$result = self::get_wpdb()->insert(
			$this->get_table_name(),
			$this->data
		);
		if ( $result ) {
			if ( in_array( 'id', $this->columns, true ) ) {
				$this->map_property( 'id', self::get_wpdb()->insert_id );
			}
		}
		return $result;
	}

	/**
	 * Update a row in DB
	 *
	 * @param int|string $id Id in the table. Default to null.
	 */
	public function update( $id = null ) {
		$method = 'get_' . $this->primary_key;
		$id     = $id ? $id : $this->$method();

		if ( ! $id ) {
			return false;
		}

		return $this->update_by_col( $this->primary_key, $id );
	}

	/**
	 * Delete a row in DB
	 *
	 * @param int|string $id Id in the table. Default to null.
	 */
	public function delete( $id = null ) {
		$method = 'get_' . $this->primary_key;
		$id     = $id ? $id : $this->$method();

		if ( ! $id ) {
			return false;
		}

		return $this->delete_by_col( $this->primary_key, $id );
	}

	/**
	 * Bulk upsert (insert/update) records into a table using WPDB.  All rows must contain the same keys.
	 * Returns number of affected (inserted) rows.
	 *
	 * @param array $rows Table data.
	 */
	public function bulk_upsert( $rows ) {
		$table = $this->get_table_name();

		$ids = array();
		if ( 0 === count( $rows ) ) {
			return array( false, $ids );
		}

		// ? Extract column list from first row of data
		$columns = array_keys( Lasso_Helper::convert_stdclass_to_array( $rows[0] ) );
		asort( $columns );
		$column_list = '`' . implode( '`, `', $columns ) . '`';

		// ? Start building SQL, initialise data and placeholder arrays
		// ? $sql = "INSERT INTO `$table` ($column_list) VALUES\n";
		$sql          = "REPLACE INTO `$table` ($column_list) VALUES\n"; // ? upsert in mysql
		$placeholders = array();
		$data         = array();

		// ? Build placeholders for each row, and add values to data array
		foreach ( $rows as $row ) {
			$row = Lasso_Helper::convert_stdclass_to_array( $row );
			ksort( $row );
			$row_placeholders = array();
			$ids[]            = intval( $row['id'] );

			foreach ( $row as $value ) {
				$data[]             = $value;
				$row_placeholders[] = is_numeric( $value ) ? '%d' : '%s';
			}

			$placeholders[] = '(' . implode( ', ', $row_placeholders ) . ')';
		}

		// ? Stitch all rows together
		$sql    .= implode( ",\n", $placeholders );
		$prepare = self::prepare( $sql, $data ); // phpcs:ignore

		// ? Run the query. Returns number of affected rows.
		return array( self::query( $prepare ), $ids ); // phpcs:ignore
	}

	/**
	 * Check whether a column exists or not
	 *
	 * @param string $table   Table name.
	 * @param array  $columns Array of columns.
	 */
	public static function column_exists( $table, $columns ) {
		if ( ! is_array( $columns ) || empty( $columns ) ) {
			return false;
		}

		$expected_count = count( $columns );
		$columns        = "'" . implode( "', '", $columns ) . "'";

		// @codingStandardsIgnoreStart
		$sql = "
			SELECT count(COLUMN_NAME) as total
			FROM information_schema.COLUMNS
			WHERE TABLE_SCHEMA = %s
				AND TABLE_NAME = %s
				AND COLUMN_NAME IN ($columns)
		";
		$prepare = self::prepare( $sql, self::get_db_name(), $table );
		// @codingStandardsIgnoreEnd
		$result = self::get_var( $prepare );
		$result = intval( $result );

		return $result === $expected_count;
	}

	/**
	 * Check whether columns exist or not
	 *
	 * @param array $columns Column name.
	 */
	public function are_columns_created( $columns ) {
		return self::column_exists( $this->get_table_name(), $columns );
	}

	/**
	 * Check whether a table exists or not
	 *
	 * @param string $table Table name.
	 */
	public static function table_exists( $table ) {
		// @codingStandardsIgnoreStart
		$sql = '
			SELECT count(*)
			FROM information_schema.TABLES
			WHERE TABLE_SCHEMA = DATABASE()
				AND TABLE_NAME = %s
		';
		$prepare = self::prepare( $sql, $table );
		// @codingStandardsIgnoreEnd
		$result = self::get_var( $prepare );

		return intval( $result ) === 1;
	}

	/**
	 * Check whether a table exists or not
	 */
	public function is_table_created() {
		return self::table_exists( $this->get_table_name() );
	}

	/**
	 * Drop index in the table
	 *
	 * @param string $index_name Index name.
	 *
	 * @return bool
	 */
	public function is_index_created( $index_name ) {
		$sql     = '
			SHOW INDEX 
			FROM ' . $this->get_table_name() . ' 
			WHERE KEY_NAME = %s
		';
		$prepare = self::prepare( $sql, $index_name );
		$index   = self::query( $prepare );

		return $index > 0;
	}

	/**
	 * Drop columns in the table
	 *
	 * @param array $columns Columns list.
	 */
	public function drop_columns( $columns ) {
		if ( ! is_array( $columns ) || empty( $columns ) ) {
			return false;
		}

		foreach ( $columns as $column ) {
			if ( $this->are_columns_created( array( $column ) ) ) {
				$query = '
					ALTER TABLE ' . $this->get_table_name() . ' 
					DROP COLUMN `' . $column . '`
				';
				self::query( $query );
			}
		}
	}

	/**
	 * Drop Index
	 *
	 * @param string $index_name Index name.
	 */
	public function drop_index( $index_name ) {
		$index_exists = $this->is_index_created( $index_name );

		if ( ! $index_exists ) {
			return false;
		}

		$sql = '
			ALTER TABLE ' . $this->get_table_name() . ' 
			DROP INDEX `' . $index_name . '`
		';

		return self::query( $sql );
	}

	/**
	 * Drop table
	 */
	public function drop_table() {
		$sql = 'DROP TABLE IF EXISTS ' . $this->get_table_name();

		return self::query( $sql );
	}

	/**
	 * Format keyword for searching
	 *
	 * @param string $keyword Keyword.
	 */
	public static function esc_like( $keyword ) {
		return self::get_wpdb()->esc_like( $keyword );
	}

	/**
	 * Get property name by method
	 *
	 * @param string $method Method.
	 */
	private function get_property_name( $method ) {
		return substr_replace( $method, '', 0, 4 );
	}

	/**
	 * Call a method in this class
	 *
	 * @param string $method Method name.
	 * @param array  $args   Arguments.
	 */
	public function __call( $method, $args ) {
		$prefix = substr_replace( $method, '', 4 );

		switch ( $prefix ) {
			case 'get_':
				return $this->$method;

			case 'set_':
				$this->$method = $args[0] ?? null;
				break;
		}

		return null;
	}

	/**
	 * Set value for a property
	 *
	 * @param string $name  Function name (set_property_name).
	 * @param mix    $value Property value.
	 */
	public function __set( $name, $value ) {
		$method   = strtolower( $name );
		$property = $this->get_property_name( $method );

		if ( ! $property ) {
			$property = $method;
		}

		if ( ! in_array( $property, $this->columns, true ) ) {
			return;
		}

		// ? see if there exists a extra setter method: setName()
		if ( ! method_exists( $this, $method ) ) {
			// ? if there is no setter, receive all public/protected vars and set the correct one if found
			$this->data[ $property ] = $value;
		} else {
			$this->$method( $value ); // ? call the setter with the value
		}
	}

	/**
	 * Get value for a property
	 *
	 * @param string $name Function name (get_property_name).
	 *
	 * @return mixed Property value.
	 */
	public function __get( $name ) {
		$method   = strtolower( $name );
		$property = $this->get_property_name( $method );

		if ( ! $property ) {
			$property = $method;
		}

		if ( ! in_array( $property, $this->columns, true ) ) {
			return;
		}

		// ? see if there is an extra getter method: get_name()
		if ( ! method_exists( $this, $method ) ) {
			// ? if there is no getter, receive all public/protected vars and return the correct one if found
			return $this->data[ $property ] ?? null;
		} else {
			return $this->$method(); // ? call the getter
		}

		return null;
	}

	/**
	 * Get a row in DB by a column
	 *
	 * @param string $column         Column name in DB.
	 * @param string $value          Value in DB.
	 * @param array  $select_columns Custom selected the columns. Default to empty array.
	 *
	 * @return array|object
	 */
	public function get_one_by_col( $column, $value, $select_columns = array() ) {
		$result = null;

		if ( ! $column || ! $value ) {
			return $result;
		}

		$select  = $this->build_select_columns( $select_columns );
		$sql     = '
			SELECT ' . $select . '
			FROM ' . $this->get_table_name() . '
			WHERE `' . $column . '` = %s
		';
		$prepare = self::prepare( $sql, $value ); // phpcs:ignore

		$result = self::get_row( $prepare );
		$this->map_properties( $result );
		$this->is_db_loaded = true;

		return $this;
	}

	/**
	 * Get one by many columns and values condition
	 *
	 * @param string $column_values  Column-values in array.
	 * @param string $select_columns Custom selected the columns. Default to empty array.
	 * @return $this|null
	 */
	public function get_one_by_cols( $column_values, $select_columns = array() ) {
		$result = null;

		if ( ! $column_values || ! is_array( $column_values ) ) {
			return $result;
		}
		$condition = '';

		foreach ( $column_values as $column => $value ) {
			$condition .= " AND $column=%s ";
			$condition  = self::prepare( $condition, $value );
		}

		$select = $this->build_select_columns( $select_columns );
		$sql    = "
			SELECT $select
			FROM {$this->get_table_name()}
			WHERE
				1=1 
				$condition
		";

		$result = self::get_row( $sql );
		$this->map_properties( $result );
		$this->is_db_loaded = true;

		return $this;
	}

	/**
	 * Update a row by a column
	 *
	 * @param string $column Column name in DB.
	 * @param string $value  Value in DB.
	 */
	public function update_by_col( $column, $value ) {
		$result = self::get_wpdb()->update(
			$this->get_table_name(),
			$this->data,
			array( $column => $value )
		);

		return $result;
	}

	/**
	 * Delete rows by a column
	 *
	 * @param string $column Column name in DB.
	 * @param string $value  Value in DB.
	 */
	public function delete_by_col( $column, $value ) {
		$result = self::get_wpdb()->delete(
			$this->get_table_name(),
			array( $column => $value )
		);

		return $result;
	}

	/**
	 * Prepare data
	 *
	 * @param string $sql     SQL query.
	 * @param mixed  ...$args Further variables to substitute into the query's placeholders if being called with individual arguments.
	 */
	public static function prepare( $sql, ...$args ) {
		return self::get_wpdb()->prepare( $sql, ...$args );
	}

	/**
	 * Get row
	 * Get row from cache if existed
	 *
	 * @param string  $sql          Sql query.
	 * @param string  $output       Type of results.
	 * @param boolean $enable_cache Enable cache.
	 *
	 * @return array|object|null|void Database query result in format specified by $output or null on failure.
	 */
	public static function get_row( $sql, $output = 'OBJECT', $enable_cache = false ) {
		$result       = false;
		$cache_string = md5( trim( (string) $sql ) . $output . __FUNCTION__ );

		if ( $enable_cache ) {
			$result = Lasso_Cache_Per_Process::get_instance()->get_cache( $cache_string );
		}

		if ( false === $result ) {
			$wpdb   = self::get_wpdb();
            $result = $wpdb->get_row( $sql, $output ); // phpcs:ignore
			self::log_error( $wpdb->last_error );

			if ( $enable_cache ) {
				Lasso_Cache_Per_Process::get_instance()->set_cache( $cache_string, $result );
			}
		}

		return $result;
	}

	/**
	 * Map data into object
	 *
	 * @param object $row A record from DB.
	 */
	protected function map_properties( $row ) {
		if ( ! $row || $this->is_map_properties ) {
			return;
		}

		$columns = $this->columns;
		foreach ( $columns as $column ) {
			$method = 'set_' . $column;
			$this->$method( $row->$column ?? null );
		}
		$this->is_map_properties = true;
		return $this;
	}

	/**
	 * Set value for column
	 *
	 * @param string $column Column name.
	 * @param string $value Value.
	 */
	protected function map_property( $column, $value ) {
		$method = 'set_' . $column;
		$this->$method( $value ?? null );
		return $this;
	}

	/**
	 * Get results
	 * Get results from cache if existed
	 *
	 * @param string  $sql          Sql query.
	 * @param string  $output       Type of results.
	 * @param boolean $enable_cache Enable cache.
	 *
	 * @return array|object|null    Database query results.
	 */
	public static function get_results( $sql, $output = 'OBJECT', $enable_cache = false ) {
		$results      = false;
		$cache_string = md5( trim( (string) $sql ) . $output . __FUNCTION__ );

		if ( $enable_cache ) {
			$results = Lasso_Cache_Per_Process::get_instance()->get_cache( $cache_string );
		}

		if ( false === $results ) {
			$wpdb    = self::get_wpdb();
            $results = $wpdb->get_results( $sql, $output ); // phpcs:ignore
			self::log_error( $wpdb->last_error );

			if ( $enable_cache ) {
				Lasso_Cache_Per_Process::get_instance()->set_cache( $cache_string, $results );
			}
		}

		return $results;
	}

	/**
	 * The replace method replaces a row in a table if it exists
	 * or inserts a new row in a table if the row did not already exist.
	 *
	 * @param string       $table       Table name.
	 * @param array        $data        Data to insert (in column => value pairs).
	 *             Both $data columns and $data values should be "raw" (neither should be SQL escaped).
	 *             Sending a null value will cause the column to be set to NULL.
	 * @param array|string $data_format An array of formats to be mapped to each of the value in $data.
	 *      If string, that format will be used for all of the values in $data. A format is one of '%d', '%f', '%s' (integer, float, string).
	 *      If omitted, all values in $data will be treated as strings unless otherwise specified in wpdb::$field_types.
	 */
	public static function replace( $table, $data, $data_format ) {
		$wpdb   = self::get_wpdb();
		$result = $wpdb->replace( $table, $data, $data_format ); // phpcs:ignore
		self::log_error( $wpdb->last_error );

		return $result;
	}

	/**
	 * Run query
	 *
	 * @param string $sql Sql query.
	 *
	 * @return int|bool Boolean true for CREATE, ALTER, TRUNCATE and DROP queries.
	 * Number of rows affected/selected for all other queries. Boolean false on error.
	 */
	public static function query( $sql ) {
		$wpdb    = self::get_wpdb();
		$results = $wpdb->query( $sql ); // phpcs:ignore
		self::log_error( $wpdb->last_error );

		return $results;
	}

	/**
	 * Get var
	 * Get var from cache if existed
	 *
	 * @param string  $sql          Sql query.
	 * @param boolean $enable_cache Enable cache.
	 *
	 * @return mixed Database query result (as string), or null on failure.
	 */
	public static function get_var( $sql, $enable_cache = false ) {
		$results      = false;
		$cache_string = md5( trim( (string) $sql ) . __FUNCTION__ );

		if ( $enable_cache ) {
			$results = Lasso_Cache_Per_Process::get_instance()->get_cache( $cache_string );
		}

		if ( false === $results ) {
			$wpdb    = self::get_wpdb();
            $results = $wpdb->get_var( $sql ); // phpcs:ignore
			self::log_error( $wpdb->last_error );

			if ( $enable_cache ) {
				Lasso_Cache_Per_Process::get_instance()->set_cache( $cache_string, $results );
			}
		}

		return $results;
	}

	/**
	 * Get col of the rows
	 * Get col from cache if existed
	 *
	 * @param string  $sql          Sql query.
	 * @param boolean $enable_cache Is use cache.
	 */
	public static function get_col( $sql, $enable_cache = false ) {
		$results      = false;
		$cache_string = md5( trim( (string) $sql ) . __FUNCTION__ );

		if ( $enable_cache ) {
			$results = Lasso_Cache_Per_Process::get_instance()->get_cache( $cache_string );
		}

		if ( false === $results ) {
			global $wpdb;

            $results = $wpdb->get_col( $sql ); // phpcs:ignore
			self::log_error( $wpdb->last_error );

			if ( $enable_cache ) {
				Lasso_Cache_Per_Process::get_instance()->set_cache( $cache_string, $results );
			}
		}

		return $results;
	}

	/**
	 * Count items by a sql query
	 *
	 * @param string $sql Sql query.
	 */
	public static function get_count( $sql ) {
		$count_sql = '
			SELECT COUNT(*) AS `count` 
			FROM (' . $sql . ') AS `tbl_count`
		';

		$result = self::get_var( $count_sql );
		$result = intval( $result );

		return $result;
	}

	/**
	 * Print error log message to log file
	 *
	 * @param string $error Error message.
	 */
	private static function log_error( $error ) {
		$log_name = 'sql_errors';
		if ( ! empty( $error ) ) {
			if ( Lasso_Helper::is_lasso_tables_does_not_exist_error( $error ) // ? Only recreate Lasso's tables when error relative to Lasso's table.
				|| strpos( $error, 'Illegal mix of collations' ) !== false
				|| strpos( $error, 'Unknown column' ) !== false
			) {
				if ( ! self::should_recreate_tables() ) {
					Lasso_Helper::write_log( 'Stop repeat call Lasso_Activator::create_lasso_table()', $log_name, false, true );
					return;
				}

				Lasso_Activator::create_lasso_table();
			}

			// ? Add force write log for lasso_debug, to see what happen when Lasso call query.
			Lasso_Helper::write_log( $error, $log_name, false, true );
			trigger_error( $error, E_USER_NOTICE ); // phpcs:ignore
		}
	}

	/**
	 * Check if number of time to call Lasso_Activator::create_lasso_table() > limit time. So we decide that should call this method or not
	 *
	 * @return bool
	 */
	private static function should_recreate_tables() {
		$lasso_recreate_table_time = Lasso_Helper::get_option( Lasso_Setting_Enum::RECREATE_TABLE_TIME, 0 );
		$lasso_recreate_table_time = intval( $lasso_recreate_table_time );

		// ? Increase number of times we call Lasso_Activator::create_lasso_table().
		++$lasso_recreate_table_time;
		if ( $lasso_recreate_table_time > self::LASSO_RECREATE_TABLES_LIMIT_TIMES ) {
			$now      = time();
			$next_run = Lasso_Helper::get_option( Lasso_Setting_Enum::NEXT_TIME_RECREATE_TABLE, 0 );
			$next_run = intval( $next_run );
			if ( 0 === $next_run ) {
				Lasso_Helper::update_option( Lasso_Setting_Enum::NEXT_TIME_RECREATE_TABLE, strtotime( '+1 hour' ) );
			}

			if ( $next_run && $now > $next_run ) {
				Lasso_Helper::update_option( Lasso_Setting_Enum::RECREATE_TABLE_TIME, 1 );
				Lasso_Helper::update_option( Lasso_Setting_Enum::NEXT_TIME_RECREATE_TABLE, 0 );
				return true;
			}

			return false;
		}
		Lasso_Helper::update_option( Lasso_Setting_Enum::RECREATE_TABLE_TIME, $lasso_recreate_table_time );
		return true;
	}

	/**
	 * Get charset collate
	 */
	protected function get_charset_collate() {
		return self::get_wpdb()->get_charset_collate();
	}

	/**
	 * Create table
	 *
	 * @param string $sql   SQL query.
	 * @param string $table Table name.
	 * @param string $reference_charset_table Reference table to get charset.
	 */
	protected function modify_table( $sql, $table, $reference_charset_table = null ) {
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$result = dbDelta( $sql );
		self::log_error( self::get_wpdb()->last_error );

		$reference_charset_table  = $reference_charset_table ? $reference_charset_table : self::get_wp_table_name( 'posts' );
		$reference_charset_status = $this->get_table_charset( $reference_charset_table, true );
		$current_table_status     = $this->get_table_charset( $table, false );

		if ( ! $reference_charset_status[1] !== $current_table_status[1] ) {
			$result = $this->update_table_collation( $table, $reference_charset_status[0], $reference_charset_status[1] );
		} else {
			$result = true;
		}

		$result = $result[ $table ] ?? 'Table already exists.';
		if ( 'Table already exists.' === $result ) {
			$check         = self::get_row( "CHECK TABLE $table" );
			$check_msg_txt = $check->Msg_text ?? ''; // phpcs:ignore
			if ( 'OK' === $check_msg_txt ) {
				$result = 'The table is okay, it does not need to be repaired.';
			} else {
				self::log_error( $check_msg_txt );
			}
		}

		return array( $table, $result );
	}

	/**
	 * Get table charset
	 *
	 * @param string $table    Table name.
	 * @param bool   $is_posts Is posts table or not. Default to false.
	 */
	private function get_table_charset( $table, $is_posts = false ) {
		// @codeCoverageIgnoreStart
		$wpdb = self::get_wpdb();

		$tablekey = strtolower( $table );
		$charset  = apply_filters( 'pre_get_table_charset', null, $table );
		if ( null !== $charset ) {
			return $charset;
		}

		if ( isset( $this->table_charset[ $tablekey ] ) ) {
			return array(
				$this->table_charset[ $tablekey ],
				$this->table_collation[ $tablekey ],
				$this->table_collation_default,
				$this->get_charset_collate(),
			);
		}

		$charsets_collections = array();
		$columns              = array();

		$table_parts = explode( '.', $table );
		$table       = '`' . implode( '`.`', $table_parts ) . '`';
		$results     = self::get_results( "SHOW FULL COLUMNS FROM $table" );
		if ( ! $results ) {
			return 'wpdb_get_table_charset_failure';
		}

		foreach ( $results as $column ) {
			$columns[ strtolower( $column->Field ) ] = $column; // phpcs:ignore
		}

		$this->col_meta[ $tablekey ] = $columns;

		foreach ( $columns as $column ) {
			if ( ! empty( $column->Collation ) ) { // phpcs:ignore
				$this->table_collation[ $tablekey ] = $column->Collation; // phpcs:ignore

				if ( $is_posts ) {
					$this->table_collation_default = $column->Collation; // phpcs:ignore
				}

				list( $charset ) = explode( '_', $column->Collation ); // phpcs:ignore

				// If the current connection can't support utf8mb4 characters, let's only send 3-byte utf8 characters.
				if ( 'utf8mb4' === $charset && ! $wpdb->has_cap( 'utf8mb4' ) ) {
					$charset = 'utf8';
				}

				$charsets_collections[ strtolower( $charset ) ] = $column->Collation; // phpcs:ignore
			} else {
				$this->table_collation[ $tablekey ] = $this->table_collation_default;
			}

			list( $type ) = explode( '(', $column->Type ); // phpcs:ignore

			// A binary/blob means the whole query gets treated like this.
			if ( in_array( strtoupper( $type ), array( 'BINARY', 'VARBINARY', 'TINYBLOB', 'MEDIUMBLOB', 'BLOB', 'LONGBLOB' ), true ) ) {
				$this->table_charset[ $tablekey ] = 'binary';
				return 'binary';
			}
		}

		// utf8mb3 is an alias for utf8.
		if ( isset( $charsets_collections['utf8mb3'] ) ) {
			$charsets_collections['utf8']       = str_replace( 'utf8mb3', 'utf8', $charsets_collections['utf8mb3'] );
			$this->table_collation[ $tablekey ] = $charsets_collections['utf8'];
			unset( $charsets_collections['utf8mb3'] );
		}

		// Check if we have more than one charset in play.
		$count = count( $charsets_collections );
		if ( 1 === $count ) {
			$charset = key( $charsets_collections );
		} elseif ( 0 === $count ) {
			// No charsets, assume this table can store whatever.
			$charset = false;
		} else {
			// More than one charset. Remove latin1 if present and recalculate.
			unset( $charsets_collections['latin1'] );
			$count = count( $charsets_collections );
			if ( 1 === $count ) {
				// Only one charset (besides latin1).
				$charset = key( $charsets_collections );

				// ? Update suitable collation for this charset
				$this->table_collation[ $tablekey ] = $charsets_collections[ $charset ];
			} elseif ( 2 === $count && isset( $charsets_collections['utf8'], $charsets_collections['utf8mb4'] ) ) {
				// Two charsets, but they're utf8 and utf8mb4, use utf8.
				$charset = 'utf8';

				// ? Update suitable collation for this charset
				$this->table_collation[ $tablekey ] = $charsets_collections['utf8'];
			} else {
				// Two mixed character sets. ascii.
				$charset = 'ascii';

				// ? Update suitable collation for this charset
				$this->table_collation[ $tablekey ] = 'ascii_general_ci';
			}
		}

		$this->table_charset[ $tablekey ] = $charset;

		return array(
			$this->table_charset[ $tablekey ],
			$this->table_collation[ $tablekey ],
			$this->table_collation_default,
			$this->get_charset_collate(),
		);
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Update table collation
	 *
	 * @param string $table     Table name.
	 * @param string $character Table character. Default to utf8mb4.
	 * @param string $collate   Table collation. Default to utf8mb4_unicode_520_ci.
	 */
	private function update_table_collation( $table, $character = 'utf8mb4', $collate = 'utf8mb4_unicode_520_ci' ) {
		if ( '' === $character || '' === $collate ) {
			return false;
		}

		$sql = '
			ALTER TABLE ' . $table . '
			CONVERT TO CHARACTER SET ' . $character . '
			COLLATE ' . $collate . ';
		';

		return self::query( $sql );
	}

	/**
	 * Build select columns query.
	 *
	 * @param array $select_columns Custom selecting columns.
	 * @return string
	 */
	private function build_select_columns( $select_columns ) {
		if ( empty( $select_columns ) || ! is_array( $select_columns ) ) {
			return '*';
		}

		$valid_columns = array_intersect( $select_columns, $this->columns );
		if ( empty( $valid_columns ) ) {
			$select = '*';
		} else {
			$select = implode( ',', $valid_columns );
		}

		return $select;
	}

	/**
	 * Determine meta data type
	 *
	 * @return string
	 */
	private function determine_meta_type() {
		$type   = get_called_class();
		$pieces = explode( '\\', $type );
		$type   = $pieces[ count( $pieces ) - 1 ];
		return $type;
	}

	/**
	 * Insert meta data
	 *
	 * @param integer $object_id  Object ID.
	 * @param string  $meta_key   Meta key.
	 * @param string  $meta_value Meta value.
	 */
	public function insert_meta_by_id( $object_id, $meta_key, $meta_value ) {
		$sql = '
			INSERT INTO ' . ( new MetaData() )->get_table_name() . '(`object_id`, `type`, `meta_key`, meta_value) 
				VALUES(%d, %s, %s, %s)
				ON DUPLICATE KEY UPDATE 
					`object_id`  = VALUES(`object_id`),
					`type`       = VALUES(`type`), 
					`meta_key`   = VALUES(`meta_key`),
					`meta_value` = VALUES(`meta_value`) 
		';
		$sql = self::prepare( $sql, $object_id, $this->determine_meta_type(), $meta_key, $meta_value );
		self::query( $sql );
	}

	/**
	 * Get total records of table.
	 *
	 * @param bool $enable_cache Is Enable query cache per process.
	 * @return int
	 */
	public function total_count( $enable_cache = false ) {
		$sql = '
			SELECT COUNT(*) as total
			FROM ' . $this->get_table_name() . '
		';

		$result = self::get_var( $sql, $enable_cache );

		return empty( $result ) ? 0 : intval( $result );
	}

	/**
	 * Add an unique key to table.
	 *
	 * @param string $column_name Column name.
	 */
	public function add_unique_key_index( $column_name ) {

		if ( $this->is_index_created( 'unique_' . $column_name ) ) {
			return false;
		}

		$sql = '
			ALTER TABLE ' . $this->get_table_name() . '
			ADD UNIQUE KEY unique_' . $column_name . ' (' . $column_name . ');
		';

		$result = self::get_var( $sql );
		return intval( $result ) === 1;
	}

	/**
	 * Get table column length.
	 *
	 * @param string $table  Table name.
	 * @param string $column Column name.
	 * @return false|mixed
	 */
	public static function get_column_length( $table, $column ) {
		$sql = '
			SELECT CHARACTER_MAXIMUM_LENGTH
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE
				table_schema = %s
				AND table_name = %s
				AND column_name = %s
		';
		$sql = self::prepare( $sql, self::get_db_name(), self::get_wp_table_name( $table ), $column );
		return self::get_var( $sql );
	}
}
