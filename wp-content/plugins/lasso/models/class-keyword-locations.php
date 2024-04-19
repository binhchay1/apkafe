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
class Keyword_Locations extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_keyword_locations';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'lasso_id',
		'keyword',
		'detection_id',
		'is_ignored',
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
			id bigint(20) NOT NULL AUTO_INCREMENT,
			lasso_id bigint(20) NOT NULL,
			keyword varchar(100) NOT NULL, 
			detection_id bigint(20) NOT NULL,
			is_ignored int(1) NOT NULL DEFAULT 0, 
			PRIMARY KEY  (id),
			KEY  kl_lasso_id (lasso_id, detection_id)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}
}
