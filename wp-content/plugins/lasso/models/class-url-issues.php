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
class Url_Issues extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_url_issues';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'detection_date',
		'issue_type',
		'issue_slug',
		'issue_resolved',

		'is_ignored',
		'issue_resolved_dt',
		'total_404_request',
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
			id bigint(10) NOT NULL COMMENT \'lasso_id\',
			detection_date datetime NULL,
			issue_type varchar(50) NOT NULL,
			issue_slug varchar(500) NOT NULL,
			issue_resolved int(1) NOT NULL DEFAULT 0,
			is_ignored int(1) NOT NULL DEFAULT 0,
			issue_resolved_dt datetime NULL,
			total_404_request int(1) NOT NULL DEFAULT 0,
			PRIMARY KEY  (id)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Update DB structure and data for v198
	 */
	public function update_for_v198() {
		// ? update the old value from string to integer
		$query = '
            UPDATE ' . $this->get_table_name() . "
			SET issue_resolved = 0
			WHERE length(issue_resolved) > 1 and issue_resolved = 'false';
        ";
		self::query( $query );

		// ? update the old value from string to integer
		$query = '
            UPDATE ' . $this->get_table_name() . "
			SET issue_resolved = 1
			WHERE length(issue_resolved) > 1 and issue_resolved = 'true';
        ";
		self::query( $query );

		// ? delete old data (string: Bad Link,...)
		$query = '
            DELETE FROM ' . $this->get_table_name() . ' 
            WHERE LENGTH(issue_type) > 3;
        ';
		self::query( $query );
	}

	/**
	 * Update DB structure and data for v228
	 */
	public function update_for_v228() {
		// ? change datetime column to not null
		$query = '
			ALTER TABLE ' . $this->get_table_name() . ' 
				CHANGE `detection_date` `detection_date` DATETIME NOT NULL,
				CHANGE `issue_resolved_dt` `issue_resolved_dt` DATETIME NULL
		';
		self::query( $query );
	}
}
