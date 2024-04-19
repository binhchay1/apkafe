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
class Amazon_Shortened_Url extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_amazon_shortened_url';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'shortened_url',
		'final_url',
		'lasso_id',
	);

	/**
	 * Primary key of the table
	 *
	 * @var string
	 */
	protected $primary_key = 'shortened_url';

	/**
	 * Create table
	 */
	public function create_table() {
		$columns_sql = '
			shortened_url varchar(200) NOT NULL,
			final_url varchar(200) NOT NULL,
			lasso_id int(10) NOT NULL,
			PRIMARY KEY  (shortened_url)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Insert or update when getting duplicate key
	 *
	 * @param string $shortened_url Shortened URL.
	 * @param string $final_url     Final URL.
	 * @param int    $lasso_id      Lasso post ID.
	 */
	public static function upsert( $shortened_url, $final_url, $lasso_id ) {
		$sql     = '
			INSERT INTO ' . ( new self() )->get_table_name() . ' (shortened_url, final_url, lasso_id)
			VALUES (%s, %s, %d)
			ON DUPLICATE KEY UPDATE
				final_url = %s
		';
		$prepare = self::prepare( $sql, $shortened_url, $final_url, $lasso_id, $final_url );

		return self::query( $prepare );
	}
}
