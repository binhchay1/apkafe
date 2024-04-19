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
class Tracked_Keywords extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_tracked_keywords';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'lasso_id',
		'keyword',
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
			id int(20) NOT NULL AUTO_INCREMENT,
			lasso_id bigint(20) NOT NULL,
			keyword varchar(100) NOT NULL, 
			PRIMARY KEY  (id),
			KEY  tk_lasso_id (lasso_id)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}
}
