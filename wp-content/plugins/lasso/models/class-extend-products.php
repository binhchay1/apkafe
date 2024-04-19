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
class Extend_Products extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_extend_products';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'product_id',
		'product_type',
		'default_product_name',
		'latest_price',
		'base_url',

		'default_image',
		'out_of_stock',
		'is_manual',
		'last_updated',
	);

	/**
	 * Primary key of the table
	 *
	 * @var string
	 */
	protected $primary_key = 'amazon_id';

	/**
	 * Create table
	 */
	public function create_table() {
		$columns_sql = '
			product_id varchar(50) NOT NULL,
			product_type varchar(20) NOT NULL,
			default_product_name varchar(2000) NOT NULL,
			latest_price varchar(32) NULL,
			base_url varchar(2000) NOT NULL,
			default_image varchar(2000) NULL,
			out_of_stock TINYINT(1) NULL DEFAULT 0,
			is_manual TINYINT(1) NOT NULL DEFAULT 0,
			last_updated datetime NOT NULL,
			PRIMARY KEY  (product_id, product_type)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}
}
