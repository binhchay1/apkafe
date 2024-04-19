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
class Amazon_Products extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_amazon_products';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'amazon_id',
		'default_product_name',
		'latest_price',
		'base_url',
		'monetized_url',

		'default_image',
		'out_of_stock',
		'is_prime',
		'currency',
		'savings_amount',

		'savings_percent',
		'savings_basis',
		'features',
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
			amazon_id varchar(20) NOT NULL,
			default_product_name varchar(2000) NOT NULL,
			latest_price varchar(32) NULL,
			base_url varchar(2000) NOT NULL,
			monetized_url varchar(2000) NULL,
			default_image varchar(10000) NULL,
			out_of_stock TINYINT(1) NULL DEFAULT 0,
			is_prime TINYINT(1) NOT NULL DEFAULT 0,
			currency VARCHAR(5) NOT NULL DEFAULT \'USD\',
			savings_amount VARCHAR(10) NULL,
			savings_percent INT(10) NULL,
			savings_basis VARCHAR(10) NULL,
			features TEXT NULL,
			is_manual TINYINT(1) NOT NULL DEFAULT 0,
			rating VARCHAR(10) NULL DEFAULT 0,
			reviews INT(11) NULL DEFAULT 0,
			last_updated datetime NOT NULL,
			PRIMARY KEY  (amazon_id)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Update DB structure and data for v263
	 */
	public function update_for_v263() {
		// ? trim column value
		$query = '
			UPDATE ' . $this->get_table_name() . ' 
			SET base_url = TRIM(base_url), monetized_url = TRIM(monetized_url)
		';
		self::query( $query );
	}
}
