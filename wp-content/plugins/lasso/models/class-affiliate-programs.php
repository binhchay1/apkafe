<?php
/**
 * Models
 *
 * @package Models
 */

namespace Lasso\Models;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Model
 */
class Affiliate_Programs extends Model {
	const OPTION_INDEX = 'lasso_sync_affiliate_programs_index';

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_affiliate_programs';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'post_title',
		'permalink',
		'niche',
		'type',

		'commission_rate',
		'lasso_partner_rate',
		'lasso_partner_badge',
		'signup_page',
		'cookie_duration',

		'description',
		'rating',
		'additional_links',
		'primary_domain',
		'post_modified',

		'created_at',
		'image_url',
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
			id bigint UNSIGNED NOT NULL,
			post_title varchar(500),
			permalink varchar(500),
			niche varchar(200),
			type varchar(200),
			commission_rate varchar(200),
			lasso_partner_rate varchar(200),
			lasso_partner_badge varchar(200),
			signup_page varchar(800),
			cookie_duration varchar(200),
			description varchar(1000),
			rating varchar(10),
			additional_links varchar(800),
			primary_domain varchar(200),
			post_modified datetime,
			created_at datetime,
			image_url varchar(200),
			PRIMARY KEY  (id)
		';
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Get row affiliate programs by domain.
	 *
	 * @param string $url url.
	 *
	 * @return object
	 */
	public static function get_row_by_domain( $url ) {
		$domain                        = Lasso_Helper::get_base_domain( $url );
		$table_name_affiliate_programs = ( new self() )->get_table_name();

		$sql = '
			SELECT *
			FROM ' . $table_name_affiliate_programs . '
			WHERE `primary_domain` = %s
		';
		$sql = self::prepare( $sql, $domain );

		return self::get_row( $sql );
	}

	/**
	 * Delete old IDs
	 *
	 * @param array $data List of ids that need to be deleted.
	 */
	public static function delete_old_items( $data ) {
		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}

		$min_index = $data[0];
		$max_index = end( $data );

		$table_name_affiliate_programs = ( new self() )->get_table_name();
		$where_not_in                  = implode( ',', $data );

		$sql     = '
			DELETE FROM ' . $table_name_affiliate_programs . '
			WHERE id >= %d
				AND id <= %d
				AND id NOT IN (' . $where_not_in . ')
		';
		$prepare = self::prepare( $sql, $min_index, $max_index );

		$lasso_sync_affiliate_programs_index = 10 > count( $data ) ? 1 : $max_index;
		update_option( self::OPTION_INDEX, $lasso_sync_affiliate_programs_index );

		return self::query( $prepare );
	}
}
