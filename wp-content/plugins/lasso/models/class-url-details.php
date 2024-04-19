<?php
/**
 * Models
 *
 * @package Models
 */

namespace Lasso\Models;

use Lasso\Classes\Extend_Product;
use Lasso_Amazon_Api;

/**
 * Model
 */
class Url_Details extends Model {

	const META_KEY_URL_WITHOUT_ARGUMENTS = 'url_without_arguments';
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_url_details';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'lasso_id',
		'redirect_url',
		'base_domain',
		'is_opportunity',
		'product_id',

		'product_type',
	);

	/**
	 * Primary key of the table
	 *
	 * @var string
	 */
	protected $primary_key = 'lasso_id';

	/**
	 * Create table
	 */
	public function create_table() {
		$columns_sql = '
			lasso_id bigint UNSIGNED NOT NULL,
			redirect_url longtext NOT NULL,
			base_domain varchar(128) NOT NULL,
			is_opportunity tinyint NOT NULL DEFAULT 1,
			product_id varchar(150),
			product_type varchar(20),
			PRIMARY KEY  (lasso_id),
			KEY  ix_base_domain (base_domain)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Update DB structure and data for v231
	 */
	public function update_for_v231() {
		$query   = '
			INSERT INTO ' . $this->get_table_name() . "
            SELECT
                DISTINCT
				p.ID as lasso_id,
                MAX(CASE WHEN pm.meta_key = 'lasso_redirect_url' THEN pm.meta_value ELSE '' END) as redirect_url,
                MAX(CASE WHEN pm.meta_key = 'affiliate_homepage' THEN pm.meta_value ELSE '' END) as base_domain,
                MAX(CASE WHEN pm.meta_key = 'is_amazon_page' THEN pm.meta_value ELSE 0 END) as is_amazon_page,
				1 as is_opportunity,
				MAX(CASE WHEN pm.meta_key = 'amazon_product_id' THEN pm.meta_value ELSE '' END) as product_id
            FROM
				" . self::get_wp_table_name( 'posts' ) . ' as p
                LEFT JOIN
					' . self::get_wp_table_name( 'postmeta' ) . " as pm
						ON p.ID = pm.post_id
                        AND pm.meta_key IN ('lasso_redirect_url', 'is_amazon_page', 'affiliate_homepage', 'amazon_product_id')
            WHERE
				post_type = %s
				AND p.ID NOT IN (SELECT lasso_id FROM " . $this->get_table_name() . ')
            GROUP BY
				p.ID
		';
		$prepare = self::prepare( $query, LASSO_POST_TYPE );
		self::query( $prepare );
	}

	/**
	 * Update DB structure and data for v263
	 */
	public function update_for_v263() {
		// ? trim column value
		$query = '
			UPDATE ' . $this->get_table_name() . ' 
			SET redirect_url = TRIM(redirect_url)
		';
		self::query( $query );
	}

	/**
	 * Update DB structure and data for v288
	 */
	public function update_for_v288() {
		// ? Remove column "is_amazon_page" from "lasso_url_details" table
		$this->drop_columns( array( 'is_amazon_page' ) );

		// ? update base_domain for amazon links that has empty base_domain and product_type is null
		$query = '
			UPDATE ' . $this->get_table_name() . "
			SET base_domain = REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(redirect_url, '/', 3), '://', -1), '/', 1), '?', 1), 'www.', '')
			WHERE redirect_url LIKE '%amazon.%' AND base_domain = '' AND product_id != '' AND product_type IS NULL;
		";
		self::query( $query );

		// ? Update product_type value for existed Amazon links in "lasso_url_details" table
		$query   = '
			UPDATE ' . $this->get_table_name() . "
			SET product_type = %s
			WHERE (base_domain LIKE '%amazon%' OR base_domain LIKE '%amzn%')
		";
		$prepare = self::prepare( $query, \Lasso_Amazon_Api::PRODUCT_TYPE );
		self::query( $prepare );
	}

	/**
	 * Get lasso url detail object by product id and product_type
	 *
	 * @param string $product_id   Product id.
	 * @param string $product_type Product type. Default is amazon.
	 * @param string $product_url  Product url. Default is empty.
	 */
	public static function get_by_product_id_and_type( $product_id, $product_type = Lasso_Amazon_Api::PRODUCT_TYPE, $product_url = '' ) {
		if ( ! $product_id ) {
			return null;
		}

		if ( $product_url && Lasso_Amazon_Api::PRODUCT_TYPE === $product_type ) {
			$product_id = Lasso_Amazon_Api::get_product_id_country_by_url( $product_url );
		}

		$sql = '
			SELECT lud.*
			FROM ' . self::get_wp_table_name( 'posts' ) . ' as wpp
				LEFT JOIN ' . ( new self() )->get_table_name() . ' as lud
				ON wpp.id = lud.lasso_id
			WHERE wpp.post_type = %s 
				AND lud.product_id = %s 
				AND lud.product_type = %s 
				AND wpp.post_status = "publish"
		';

		$prepare = self::prepare( $sql, LASSO_POST_TYPE, $product_id, $product_type ); // phpcs:ignore
		$result  = self::get_row( $prepare );

		if ( $result ) {
			return ( new self() )->map_properties( $result );
		}

		return null;
	}

	/**
	 * Get instance by url
	 *
	 * @param string $url URL.
	 *
	 * @return array|object
	 */
	public static function get_by_url_without_arguments( $url ) {
		// ? don't check amazon and extend URLs
		if ( ! $url || Lasso_Amazon_Api::is_amazon_url( $url ) || Extend_Product::get_extend_product_type_from_url( $url ) ) {
			return null;
		}

		$sql     = '
			SELECT pm.post_id 
			FROM ' . self::get_wp_table_name( 'postmeta' ) . ' AS pm
			INNER JOIN ' . Model::get_wp_table_name( 'posts' ) . ' AS p
				ON pm.post_id = p.ID
			WHERE pm.meta_key = %s 
				AND pm.meta_value = %s
				AND p.post_type = %s 
			ORDER BY pm.post_id DESC 
			LIMIT 1
		';
		$prepare = self::prepare( $sql, self::META_KEY_URL_WITHOUT_ARGUMENTS, $url, LASSO_POST_TYPE ); // phpcs:ignore
		$result  = self::get_row( $prepare );

		if ( $result ) {
			return ( new self() )->get_one_by_col( 'lasso_id', $result->post_id );
		}
		return null;
	}
}
