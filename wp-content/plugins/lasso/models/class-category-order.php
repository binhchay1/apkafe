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
class Category_Order extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_category_order';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'item_id',
		'parent_slug',
		'term_order',
	);

	/**
	 * Primary key of the table
	 *
	 * @var string
	 */
	protected $primary_key = 'item_id';

	/**
	 * Create table
	 */
	public function create_table() {
		$columns_sql = '
			item_id bigint(10) NOT NULL,
			parent_slug varchar(150) NOT NULL,
			term_order int(10) NOT NULL,
			PRIMARY KEY  (item_id, parent_slug)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name(), Model::get_wp_table_name( 'terms' ) );
	}

	/**
	 * Update DB structure and data for v229
	 */
	public function update_for_v229() {
		// ? drop and re-add primary key
		$query = '
			ALTER TABLE ' . $this->get_table_name() . ' 
				DROP PRIMARY KEY, 
				ADD PRIMARY KEY (item_id, parent_slug)
		';
		self::query( $query );
	}

	/**
	 * Update DB structure and data for v240
	 */
	public function update_for_v240() {
		$this->update_for_v229();
	}
}
