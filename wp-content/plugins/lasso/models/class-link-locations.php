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
class Link_Locations extends Model {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table = 'lasso_link_locations';

	/**
	 * Columns of the table
	 *
	 * @var array
	 */
	protected $columns = array(
		'id',
		'detection_date',
		'link_type',
		'display_type',
		'anchor_text',

		'post_id',
		'link_slug',
		'link_slug_domain',
		'detection_id',
		'detection_slug',

		'tracking_id',
		'product_id',
		'no_follow',
		'new_window',
		'is_ignored',

		'original_link_slug',
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
		$columns_sql = "
			id bigint(20) NOT NULL AUTO_INCREMENT,
			detection_date datetime NOT NULL,
			link_type varchar(50) NOT NULL,
			display_type varchar(50) NOT NULL,
			anchor_text varchar(1500) NULL,
			post_id int(10) NULL COMMENT 'post_id of link_slug',
			link_slug varchar(1500) NOT NULL,
			link_slug_domain varchar(150) NULL,
			detection_id bigint UNSIGNED NULL COMMENT 'post_id of detection_slug',
			detection_slug varchar(1000) NOT NULL,
			tracking_id varchar(150) NULL,
			product_id varchar(150) NULL,
			no_follow varchar(50) NOT NULL,
			new_window varchar(50) NOT NULL,
			is_ignored int(1) NOT NULL DEFAULT 0,
			original_link_slug varchar(1500) NOT NULL,
			PRIMARY KEY  (id),
			KEY  ix_link_type (link_type, link_slug_domain),
			KEY  ix_link_location_product_id (product_id)
		";
		$sql         = '
			CREATE TABLE ' . $this->get_table_name() . ' (
				' . $columns_sql . '
			) ' . $this->get_charset_collate();

		return $this->modify_table( $sql, $this->get_table_name() );
	}

	/**
	 * Get url links query
	 *
	 * @param int $lasso_id Post id. Default to empty.
	 */
	public static function get_url_links_query( $lasso_id = '' ) {
		if ( is_array( $lasso_id ) ) {
			$lasso_id = self::prepare( 'AND a.id IN ( %d)', implode( ',', $lasso_id ) );
		} else {
			$lasso_id = $lasso_id ? self::prepare( 'AND a.id = %d', $lasso_id ) : '';
		}

		$posts_table_name = self::get_wp_table_name( 'posts' );

		$sql = "
			SELECT
				DISTINCT
				a.id,
				a.post_type,
				c.id AS `link_id`,
				c.`detection_id` AS `post_id`,
				c.`detection_id`,
				c.`detection_slug`,
				c.`link_slug`,
				c.`anchor_text`,
				c.`link_type`,
				d.post_title,
				d.`post_modified`,
				c.link_slug AS link_slug_original,
				c.link_slug AS display_box,
				c.display_type,
				c.post_id AS lasso_id,
				SUBSTR(
					SUBSTR(c.`anchor_text`, LOCATE('src=\"', c.`anchor_text`) + 5), 
					1, 
					LOCATE('\"', SUBSTR(c.`anchor_text`, LOCATE('src=\"', c.`anchor_text`) + 5)) - 1
				) AS img_src
			FROM " . $posts_table_name . ' AS a
			INNER JOIN ' . ( new self() )->get_table_name() . ' AS c
				ON a.id = c.post_id
			LEFT JOIN ' . $posts_table_name . " AS d
				ON c.detection_id = d.id
			WHERE
				a.post_type = '" . LASSO_POST_TYPE . "'  
				" . $lasso_id . "
				AND c.detection_slug <> ''
				AND c.detection_slug NOT LIKE '%__trashed%'
		";

		return $sql;
	}

	/**
	 * Return locations count filter by lasso id.
	 *
	 * @param int $lasso_id Lasso ID.
	 * @return int|mixed
	 */
	public static function total_locations_by_lasso_id( $lasso_id ) {
		$sql = self::get_url_links_query( $lasso_id );

		return self::get_count( $sql );
	}

	/**
	 * Update DB structure and data for v278
	 */
	public function update_for_v278() {
		// ? drop columns
		$this->drop_columns( array( 'is_monetized', 'is_updated', 'monetized_at' ) );

		// ? make anchor_text NULLable
		$query = '
			ALTER TABLE ' . $this->get_table_name() . ' 
				MODIFY anchor_text varchar(1000) NULL
		';
		self::query( $query );

		// ? update link_slug_location to NULL if column is empty
		$query = '
			UPDATE ' . $this->get_table_name() . " 
			SET link_slug_domain = NULL
			WHERE link_slug_domain = ''
		";
		self::query( $query );

		// ? update product_id to NULL if column is empty
		$query = '
			UPDATE ' . $this->get_table_name() . " 
			SET product_id = NULL
			WHERE product_id = ''
		";
		self::query( $query );

		// ? update tracking_id to NULL if column is empty
		$query = '
			UPDATE ' . $this->get_table_name() . " 
			SET tracking_id = NULL
			WHERE tracking_id = ''
		";
		self::query( $query );

		// ? update anchor_text to 'DISPLAY BOX' if column is empty (a shortcode)
		$query = '
			UPDATE ' . $this->get_table_name() . " 
			SET anchor_text = NULL
			WHERE anchor_text = '' 
				OR anchor_text = 'DISPLAY BOX'
		";
		self::query( $query );
	}

	/**
	 * Update DB structure and data for v282
	 */
	public function update_for_v282() {
		// ? drop columns
		$this->drop_columns( array( 'detection_count' ) );
	}

	/**
	 * Get displays type count.
	 *
	 * @param int    $post_id       Post ID.
	 * @param string $link_type     Link type.
	 * @param array  $displays_type Displays type.
	 *
	 * @return int
	 */
	public static function get_displays_type_count( $post_id, $link_type, $displays_type ) {
		$table_name = ( new self() )->get_table_name();

		$sql = '
			SELECT
				SUM(ll.total) AS total_displays_count
			FROM (
				SELECT
					link_type,
					display_type,
					COUNT(*) AS total
				FROM
					`' . $table_name . '`
				WHERE 
					detection_id = %d
					AND link_type = %s
					AND display_type IN (' . implode( ', ', array_fill( 0, count( $displays_type ), '%s' ) ) . ')
				GROUP BY
					link_type,
					display_type
			) AS ll
		';

		$sql    = call_user_func_array(
			array( 'self', 'prepare' ),
			array_merge( array( $sql, $post_id, $link_type ), $displays_type )
		);
		$result = self::get_row( $sql );

		return intval( $result->total_displays_count ?? 0 );
	}

	/**
	 * Get post id by site stripe.
	 *
	 * @param string $amazon_id Amazon ID.
	 */
	public static function get_post_id_by_site_stripe( $amazon_id ) {
		$table_name = ( new self() )->get_table_name();

		$sql     = '
			SELECT DISTINCT detection_id
			FROM ' . $table_name . '
			WHERE product_id = %s
				AND link_type = %s
		';
		$prepare = self::prepare( $sql, $amazon_id, 'SiteStripe' );

		return self::get_col( $prepare );
	}
}
