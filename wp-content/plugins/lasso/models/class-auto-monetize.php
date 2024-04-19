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
class Auto_Monetize extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_auto_monetize';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'domain',
		'deep_link',
		'integration',
		'affiliate_id',
		'advertiser_id',
		'url',
		'lasso_id',
		'url_encrypt',
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
			id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
			domain varchar(100),
			deep_link varchar(1024),
			integration varchar(20),
			affiliate_id varchar(64),
			advertiser_id varchar(64),
			url varchar(2048),
			lasso_id bigint,
			url_encrypt varchar(32),
			PRIMARY KEY  (id)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Get all except the row has lasso id.
	 *
	 * @param int $limit Limit the results.
	 */
	public static function get_all_except_lasso( $limit = 10 ) {
		$sql     = '
			SELECT url
			FROM ' . ( new self() )->get_table_name() . '
			WHERE lasso_id IS NULL 
				OR lasso_id = ""
				OR lasso_id = 0
				OR lasso_id NOT IN (
					SELECT ID
					FROM ' . Model::get_wp_table_name( 'posts' ) . '
					WHERE post_type = %s
				)
			LIMIT %d
		';
		$prepare = self::prepare( $sql, LASSO_POST_TYPE, $limit );

		return self::get_col( $prepare );
	}

	/**
	 * Add unique index unique_url_encrypt to table
	 *
	 * @return bool
	 */
	public function add_url_encrypt_index() {
		return $this->add_unique_key_index( 'url_encrypt' );
	}

	/**
	 * Populate data to url_encrypt field from url field
	 */
	public function populate_url_encrypt_data() {
		$sql = '
			UPDATE ' . $this->get_table_name() . '
			SET url_encrypt = MD5(url)
		';

		self::query( $sql );
	}

}
