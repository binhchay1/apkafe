<?php
/**
 * Declare class Lasso_DB
 *
 * @package Lasso_DB
 */

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Enum;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Launch_Darkly;
use Lasso\Classes\Link_Location as Lasso_Link_Location;
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Setting_Enum;

use Lasso\Models\Amazon_Products;
use Lasso\Models\Amazon_Shortened_Url;
use Lasso\Models\Fields as Lasso_Fields;
use Lasso\Models\Link_Locations;
use Lasso\Models\Model;
use Lasso\Models\Url_Details;
use Lasso\Models\Revert;

/**
 * Lasso_DB
 */
class Lasso_DB {
	const LASSO_RECREATE_TABLES_LIMIT_TIMES = 3;
	const DB_LIMIT_200                      = 200;

	/**
	 * Current domain
	 *
	 * @var array $current_domain Current domain
	 */
	public $current_domain;

	/**
	 * Construction of Lasso_DB
	 */
	public function __construct() {
		$this->current_domain = str_replace( 'https://', '', str_replace( 'http://', '', strtolower( get_site_url() ) ) );
	}

	/**
	 * Get dashboard link count
	 *
	 * @param string $search Search text. Default to empty.
	 */
	public function get_dashboard_link_count( $search = '' ) {
		$join_url_detail = '';
		if ( $search ) {
			$join_url_detail = '
				LEFT JOIN ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . ' AS ud
				ON ud.lasso_id = p.ID
			';
		}

		$sql = '
			SELECT COUNT(p.ID) AS `lasso_count`
			FROM ' . Model::get_wp_table_name( 'posts' ) . ' AS p
				' . $join_url_detail . '
			WHERE
				p.post_type = %s
				AND p.post_status = %s
				' . $search . '
		';
		$sql = Model::prepare( $sql, LASSO_POST_TYPE, 'publish' );

		$row         = Model::get_row( $sql );
		$lasso_count = intval( $row->lasso_count ?? 0 );

		return $lasso_count;
	}

	/**
	 * Get total display count
	 */
	public function get_total_display_count() {
		$displays              = array(
			Lasso_Link_Location::DISPLAY_TYPE_SINGLE,
			Lasso_Link_Location::DISPLAY_TYPE_BUTTON,
			Lasso_Link_Location::DISPLAY_TYPE_IMAGE,
			Lasso_Link_Location::DISPLAY_TYPE_GRID,
			Lasso_Link_Location::DISPLAY_TYPE_LIST,
			Lasso_Link_Location::DISPLAY_TYPE_GALLERY,

			Lasso_Link_Location::DISPLAY_TYPE_FIELD,
			Lasso_Link_Location::DISPLAY_TYPE_TABLE,
		);
		$displays_in_condition = "'" . implode( "', '", $displays ) . "'";

		$sql = '
			SELECT COUNT(id) as `display_count`
			FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . "
			WHERE
				link_type = '" . Lasso_Link_Location::LINK_TYPE_LASSO . "'
				AND display_type IN (" . $displays_in_condition . ');
		';

		$row           = Model::get_row( $sql );
		$display_count = intval( $row->display_count ?? 0 );

		return $display_count;
	}

	/**
	 * Get display counts for Intercom
	 */
	public function get_display_counts_for_intercom() {
		$sql = '
			SELECT
				SUM(display_count) as `single_count`,
				SUM(grid_count) as `grid_count`,
				SUM(list_count) as `list_count`,
				SUM(table_count) as `table_count`
			FROM ' . Model::get_wp_table_name( LASSO_CONTENT_DB ) . ';
		';

		$row = Model::get_row( $sql );

		$result['single_count'] = intval( $row->single_count ?? 0 );
		$result['grid_count']   = intval( $row->grid_count ?? 0 );
		$result['list_count']   = intval( $row->list_count ?? 0 );
		$result['table_count']  = intval( $row->table_count ?? 0 );

		return $result;
	}

	/**
	 * Get lasso link count
	 *
	 * @param int $id Post id.
	 */
	public function get_lasso_link_counts( $id ) {
		$sql     = '
			SELECT
				p.ID AS `post_id`,
				IF(ll.link_count > 0, ll.link_count, 0) AS `links`,
				IF(ll.display_count > 0, ll.display_count, 0) AS `displays`,
				IF(lo.opportunity_count > 0, lo.opportunity_count, 0) +
					IF(koc.opportunity_count > 0, koc.opportunity_count, 0)
				AS `opportunities`
			FROM
					' . Model::get_wp_table_name( 'posts' ) . ' as p
				LEFT JOIN
					' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . ' as d
					ON p.id = d.lasso_id
				LEFT JOIN
					(
						SELECT
							`post_id`,
							SUM(case when link_type = %s then 1 else 0 end) as `display_count`,
							SUM(case when link_type <> %s then 1 else 0 end) as `link_count`
						FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . '
						WHERE `link_type` NOT IN (%s, %s)
						GROUP BY `post_id`
						ORDER BY NULL
					) as ll
					ON p.id = ll.post_id
				LEFT JOIN
					(
						SELECT `link_slug_domain`, COUNT(*) AS `opportunity_count`
						FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . '
						WHERE `link_type` = %s AND is_ignored = 0
						GROUP BY `link_slug_domain`
						ORDER BY NULL
					) as lo
					ON d.base_domain = lo.link_slug_domain
				LEFT JOIN
					(
						SELECT lasso_id, COUNT(*) AS `opportunity_count`
						FROM ' . Model::get_wp_table_name( LASSO_KEYWORD_LOCATIONS ) . '
						WHERE `lasso_id` = %d AND is_ignored = 0
						GROUP BY lasso_id
						ORDER BY NULL
					) as koc
					ON p.id = koc.lasso_id
			WHERE
				p.id = %d
				AND p.post_type = %s
				AND p.post_status = %s
		';
		$prepare = Model::prepare( $sql, 'shortcode', 'shortcode', 'Normal', 'Affiliate Homepage', 'Affiliate Homepage', $id, $id, LASSO_POST_TYPE, 'publish' );

		return $prepare;
	}

	/**
	 * Get dashboard query
	 *
	 * @param string $search Search text.
	 * @param string $where  Where statement. Default to '1=1'.
	 * @param string $filter Filter. Default to empty.
	 */
	public function get_dashboard_query( $search, $where = '1=1', $filter = '' ) {
		if ( 'opportunities' === $filter ) {
			$filter = 'AND IF(lo.opportunity_count > 0, lo.opportunity_count, 0) > 0';
		} elseif ( 'broken-links' === $filter ) {
			$filter = 'AND bl.broken_link_count > 0';
		} elseif ( 'out-of-stock' === $filter ) {
			$filter = 'AND (IF(bl.out_of_stock_count > 0, bl.out_of_stock_count, 0) > 0)';
		}

		$opportunities_sql = '
			SELECT lasso_id, COUNT(*) as `opportunity_count`
			FROM (
				' . $this->get_link_opportunities_query( '', 0, true ) . '
			) as base
			GROUP BY lasso_id
			ORDER BY NULL
		';

		$url_detail_tbl  = ( new Url_Details() )->get_table_name();
		$amz_product_tbl = ( new Amazon_Products() )->get_table_name();

		$sql = '
			SELECT
				p.ID,
				p.ID AS `post_id`,
				IF(ll.count > 0, ll.count, 0) AS `count`,
				IF(lo.opportunity_count > 0, lo.opportunity_count, 0) AS `opportunities`,
				IF(bl.broken_link_count > 0, bl.broken_link_count, 0) AS `broken_link`,
				IF(bl.out_of_stock_count > 0, bl.out_of_stock_count, 0) AS `out_of_stock`,
				p.post_title,
				p.post_modified
			FROM 
					' . Model::get_wp_table_name( 'posts' ) . " AS p
				LEFT JOIN
					(
						SELECT `post_id`, COUNT(`id`) AS 'count'
						FROM " . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . "
						WHERE `link_type` = %s
							AND detection_slug <> ''
							AND detection_slug NOT LIKE '%__trashed%'
						GROUP BY `post_id`
						ORDER BY NULL
					) AS ll
						ON p.id = ll.post_id
				LEFT JOIN
					(
						" . $opportunities_sql . "
					) AS lo
						ON lo.lasso_id = p.ID
				LEFT JOIN
					(
						SELECT
							ui.id,
							SUM(CASE WHEN ui.issue_type != '000' THEN 1 ELSE 0 END) AS broken_link_count,
							SUM(CASE WHEN ui.issue_type = '000' THEN 1 ELSE 0 END) AS out_of_stock_count
						FROM " . Model::get_wp_table_name( LASSO_URL_ISSUE_DB ) . ' AS ui
						WHERE
							issue_resolved = 0
							AND is_ignored = 0
						GROUP BY ui.id
						ORDER BY NULL
					) AS bl
						ON bl.id = p.ID
				LEFT JOIN
					' . $url_detail_tbl . ' AS ud
						ON ud.lasso_id = p.ID

				# check out of stock in lasso amazon table
				LEFT JOIN (
					SELECT p.ID, la.amazon_id, la.out_of_stock
					FROM ' . Model::get_wp_table_name( 'posts' ) . ' AS p
						INNER JOIN ' . $url_detail_tbl . ' AS lud
							ON p.ID = lud.lasso_id
						INNER JOIN ' . $amz_product_tbl . ' AS la
							ON lud.product_id = la.amazon_id
					WHERE 
						lud.product_type = %s
					GROUP BY p.ID, la.amazon_id, la.out_of_stock
				) AS amz_tbl
					ON p.ID = amz_tbl.ID
				
			WHERE
				p.post_type = %s
				AND p.post_status = %s
				' . $search . '
				' . $filter . '
				AND ' . $where . '
		';
		$sql = Model::prepare( $sql, Lasso_Link_Location::LINK_TYPE_LASSO, Lasso_Amazon_Api::PRODUCT_TYPE, LASSO_POST_TYPE, 'publish' );

		return $sql;
	}

	/**
	 * Get monetize modal query
	 *
	 * @param string $search Search text.
	 */
	public function get_monetize_modal_query( $search ) {
		$sql = '
			SELECT
				p.ID,
				p.ID AS `post_id`,
				p.post_title,
				p.post_modified
			FROM ' . Model::get_wp_table_name( 'posts' ) . " as p
			WHERE
				p.post_type = '" . LASSO_POST_TYPE . "'
				AND p.post_status = 'publish'
				" . $search . '
		';

		return $sql;
	}

	/**
	 * Get keyword query
	 *
	 * @param string $search Search text.
	 */
	public function get_keywords_query( $search ) {
		$sql = '
			SELECT id, lasso_id, keyword
			FROM ' . Model::get_wp_table_name( LASSO_TRACKED_KEYWORDS ) . '
			WHERE
				1=1
				' . $search . '
		';

		return $sql;
	}

	/**
	 * Get fields query
	 *
	 * @param string $search Search text.
	 */
	public function get_fields_query( $search ) {
		$sql = '
			SELECT 
				lf.id, 
				lf.field_name, 
				lf.field_type, 
				lf.field_description,
				count(distinct wpp.id) as `count`
			FROM 
					' . Model::get_wp_table_name( LASSO_FIELDS ) . ' as lf
				LEFT JOIN
					' . Model::get_wp_table_name( LASSO_FIELD_MAPPING ) . ' as lfm
						ON lf.id = lfm.field_id
				LEFT JOIN 
					' . Model::get_wp_table_name( 'posts' ) . ' as wpp
						ON wpp.id = lfm.lasso_id
			WHERE
				1=1
				' . $search . '
			GROUP BY
				lf.id, 
				lf.field_name, 
				lf.field_type, 
				lf.field_description
		';

		return $sql;
	}

	/**
	 * Get field query
	 *
	 * @param string $post_id Search text.
	 */
	public function get_field( $post_id ) {
		$where  = $post_id ? Model::prepare( 'AND lf.id = %d', $post_id ) : '';
		$sql    = $this->get_fields_query( $where );
		$fields = Model::get_row( $sql );

		return $fields;
	}

	/**
	 * Get urls in field
	 *
	 * @param string $post_id Where statement. Default to '1=1'.
	 */
	public function get_urls_in_field( $post_id ) {
		$where = $post_id ? Model::prepare( 'f.id = %d', $post_id ) : '1=1';

		$sql = '
			SELECT
				f.id,
				f.field_name as field_name,
				f.field_description as field_description,
				f.field_type,
				p.ID as post_id,
				p.post_title,
				p.post_name
			FROM
					' . Model::get_wp_table_name( LASSO_FIELDS ) . ' as f
				LEFT JOIN
					' . Model::get_wp_table_name( LASSO_FIELD_MAPPING ) . ' as fm
						ON fm.field_id = f.id
				INNER JOIN
					' . Model::get_wp_table_name( 'posts' ) . ' as p
						ON fm.lasso_id = p.ID
			WHERE
				' . $where . "
				AND p.post_type = '" . LASSO_POST_TYPE . "'
				AND p.post_status = 'publish'
		";

		return $sql;
	}

	/**
	 * Get link opportunities query
	 *
	 * @param string $search       Search text. Default to empty.
	 * @param int    $lasso_id     Lasso post id. Default to 0.
	 * @param bool   $is_dashboard Is dashboard page or not. Default to false.
	 * @param array  $post_ids     Filter by post ids. Default to empty array.
	 */
	public function get_link_opportunities_query( $search = '', $lasso_id = 0, $is_dashboard = false, $post_ids = array() ) {
		$amazon_in_condition = $this->get_amazon_domains_in_clause();

		if ( ! $is_dashboard ) {
			$search = ( '' !== $search && '%%' !== $search )
				? "
				AND (p.post_title LIKE '" . $search . "'
				OR a.post_title LIKE '" . $search . "'
				OR c.link_slug LIKE '" . $search . "'
				OR d.base_domain LIKE '" . $search . "')
				" : '';
		} else {
			$search = '' !== $search ? "AND (p.post_title LIKE '" . $search . "' OR p.post_name LIKE '" . $search . "' OR d.redirect_url LIKE '" . $search . "')" : '';
			$search = empty( $post_ids ) ? $search : $search . ' AND p.ID IN(' . implode( ',', $post_ids ) . ')';
		}

		$lasso_id_search = $lasso_id > 0 ? 'AND d.lasso_id=' . $lasso_id : '';

		$sql = '
			SELECT
				-- DISTINCT
				c.id as `link_id`,
				c.`detection_id` as `post_id`,
				c.`detection_id`,
				a.post_title,
				c.`detection_slug`,
				c.`link_slug`,
				c.link_slug_domain,
				c.`anchor_text`,
				c.`link_type`,
				p.post_title as lasso_suggestion,
				c.link_slug as link_slug_original,
				c.display_type,
				d.lasso_id
			FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . ' as c
				INNER JOIN
					' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . " as d
						ON d.base_domain = c.link_slug_domain
						AND d.base_domain <> '" . $this->current_domain . "'
						AND d.base_domain NOT IN (" . $amazon_in_condition . ")
						AND c.link_slug_domain <> '" . $this->current_domain . "'
				INNER JOIN
					" . Model::get_wp_table_name( 'posts' ) . ' as p
						ON p.ID = d.lasso_id
				LEFT JOIN
					' . Model::get_wp_table_name( 'posts' ) . " as a
						ON c.detection_id = a.ID
			WHERE
				p.post_type = '" . LASSO_POST_TYPE . "'  
				AND c.post_id = 0
				AND d.is_opportunity = 1
				AND c.detection_slug <> ''
				AND c.detection_slug NOT LIKE '%__trashed%'
				AND c.is_ignored = 0
				" . $search . '
				' . $lasso_id_search . '
		';

		return $sql;
	}

	/**
	 * Get keyword opportunities query
	 *
	 * @param string $search  Search text. Default to empty.
	 * @param string $keyword Where statement. Default to empty.
	 */
	public function get_keyword_opportunities_query( $search = '', $keyword = '' ) {
		$search = '' !== $search
			? "
				AND (d.post_title LIKE '" . $search . "'
					OR k.keyword LIKE '" . $search . "')
			" : '';

		$lasso_keyword_search = '' !== $keyword ? Model::prepare( 'AND k.keyword = %s', $keyword ) : '';

		$sql = "
			SELECT 
				k.id as keyword_location_id,
				k.id as link_id,
				k.detection_id as post_id,
				k.detection_id,
				null as detection_slug,
				k.keyword as link_slug,
				k.keyword as anchor_text,
				'keyword' as link_type,
				d.post_title,
				d.post_modified,
				k.keyword as link_slug_original,
				null as display_type,
				k.lasso_id
			FROM 
					" . Model::get_wp_table_name( LASSO_KEYWORD_LOCATIONS ) . ' as k
				INNER JOIN 
					' . Model::get_wp_table_name( 'posts' ) . ' as d
						ON d.id = k.detection_id
			WHERE
				k.is_ignored = 0 
				AND d.post_title IS NOT NULL
				' . $search . '
				' . $lasso_keyword_search . '
		';

		// TODO: Add WP Prepare around keyword text to protect from quotes.

		return $sql;
	}

	/**
	 * Get content query
	 *
	 * @param string $search Search text. Default to '1=1'.
	 * @param string $where  Where statement. Default to '1=1'.
	 */
	public function get_content_query( $search = '1=1', $where = '1=1' ) {
		$sql = "
			SELECT
				`ID`,
				`ID` as post_id,
				detection_title,
				detection_id,
				detection_slug,
				REPLACE(REPLACE(REPLACE(detection_slug, '" . $this->current_domain . "', ''), 'http://', ''), 'https://', '') as short_detection_slug,
				monetized,
				`count`,
				DATE_FORMAT(post_modified, '%b %D, %Y') as post_modified_string
			FROM (
				SELECT
					p.`ID`,
					post_title as detection_title,
					detection_id,
					ll.detection_slug,
					SUM(CASE WHEN ll.post_id <> 0 THEN 1 ELSE 0 END) as monetized,
					COUNT(ll.`link_slug`) AS `count`,
					MAX(post_modified) as post_modified
				FROM " . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . ' as ll
					LEFT JOIN ' . Model::get_wp_table_name( 'posts' ) . " as p
					ON ll.detection_id = p.`ID`
				WHERE p.post_status = 'publish'
				GROUP BY
					p.`ID`,
					post_title,
					ll.detection_slug,
					detection_id
				) as base
			WHERE
				" . $where . '
				' . $search . '
		';

		return $sql;
	}

	/**
	 * Get content link query
	 *
	 * @param string $search Search text. Default to '1=1'.
	 * @param string $where  Where statement. Default to '1=1'.
	 */
	public function get_content_link_query( $search = '1=1', $where = '1=1' ) {
		$sql = "
			SELECT
				p.`ID`,
				ll.id as link_location_id,
				p.post_title,
				detection_id,
				ll.detection_slug,
				ll.link_slug as link_slug_original,
				ll.link_slug_domain,
				ll.link_type,
				ll.link_slug as display_box,
				ll.display_type,
				ll.anchor_text,
				ll.post_id as lasso_id,
				SUBSTR(
					SUBSTR(ll.`anchor_text`, LOCATE('src=\"', ll.`anchor_text`) + 5), 
					1, 
					LOCATE('\"', SUBSTR(ll.`anchor_text`, LOCATE('src=\"', ll.`anchor_text`) + 5)) - 1
				) as img_src
			FROM " . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . ' as ll
				LEFT JOIN
					' . Model::get_wp_table_name( 'posts' ) . ' as p
						ON ll.detection_id = p.`ID`
				LEFT JOIN
					' . Model::get_wp_table_name( 'posts' ) . " as a
						ON ll.post_id = a.`ID`
			WHERE
				p.post_status = 'publish'
				AND (
					ll.post_id = 0 OR ll.post_id in (select ID from " . Model::get_wp_table_name( 'posts' ) . ')
				)
				AND ll.display_type NOT IN ("Table", "Grid", "List")
				AND ' . $where . '
				' . $search . '
		';

		return $sql;
	}

	/**
	 * Get opportunities query
	 *
	 * @param string $search Search text.
	 * @param string $where  Where statement. Default to '1=1'.
	 */
	public function get_opportunities_query( $search, $where = '1=1' ) {
		$sql = '
			SELECT
				link_slug_domain,
				link_slug_domain as post_id,
				`count`
			FROM
				(
					SELECT link_slug_domain, count(*) as `count`
					FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . " as lll
					WHERE
						lll.display_type = '" . Lasso_Link_Location::DISPLAY_TYPE_TEXT . "'
						AND lll.post_id = 0
					GROUP BY link_slug_domain
					ORDER BY NULL
				) as base
			WHERE
				link_slug_domain  <> '" . $this->current_domain . "'
				" . $search . '
				AND ' . $where . '
		';

		return $sql;
	}

	/**
	 * Get program opportunities query
	 *
	 * @param string $search Search text.
	 * @param string $where  Where statement. Default to '1=1'.
	 */
	public function get_program_opportunities_query( $search, $where = '1=1' ) {
		$sql = "
			SELECT
				post_title as affiliate_name,
				image_url,
				permalink,
				CASE
					WHEN lasso_partner_rate <> ''
						THEN lasso_partner_rate
					ELSE commission_rate
				END as commission_rate,
				description,
				link_slug_domain,
				signup_page,
				count
			FROM
				( 
					SELECT link_slug_domain, count(*) as count
					FROM  " . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . "
					WHERE link_slug_domain <> ''
					GROUP BY link_slug_domain 
				) as ll
			INNER JOIN
				" . Model::get_wp_table_name( LASSO_AFFILIATE_PROGRAMS ) . " as ap
					ON ll.link_slug_domain = TRIM(LEADING 'www.' FROM TRIM(TRAILING '/' FROM REPLACE( REPLACE( ap.primary_domain, 'https://', ''), 'http://', '' ) ) )
					AND ap.post_title NOT IN ('Amazon Associates', 'Google AdSense')
			WHERE
				" . $where . '
				' . $search . '
				
			';

		return $sql;
	}

	/**
	 * Get domain link query
	 *
	 * @param string $search Search text.
	 * @param string $where  Where statement. Default to '1=1'.
	 */
	public function get_domain_link_query( $search, $where = '1=1' ) {
		$where = $where . ' AND ll.post_id = 0';

		$search = '' !== $search
			? "
				AND (post_title LIKE '" . $search . "'
					OR ll.anchor_text LIKE '" . $search . "')
			" : '';

		$sql = '
			SELECT
				p.`ID`,
				ll.id as link_location_id,
				post_title,
				detection_id,
				ll.detection_slug,
				ll.link_slug as link_slug_original,
				ll.link_slug_domain,
				ll.link_type,
				ll.display_type,
				ll.anchor_text,
				ll.post_id as lasso_id
			FROM
				' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . ' as ll
				LEFT JOIN ' . Model::get_wp_table_name( 'posts' ) . ' as p
				ON ll.detection_id = p.`ID`
			WHERE
				' . $where . '
				' . $search . '
		';

		return $sql;
	}

	/**
	 * Get opportunitues only query
	 *
	 * @param string $search Search text.
	 * @param string $where  Where statement. Default to '1=1'.
	 */
	public function get_opportunities_only_query( $search, $where = '1=1' ) {
		$displays              = array(
			Lasso_Link_Location::DISPLAY_TYPE_TEXT,
			Lasso_Link_Location::DISPLAY_TYPE_IMAGE_ONLY,
		);
		$displays_in_condition = "'" . implode( "', '", $displays ) . "'";

		$sql = '
			SELECT
				link_slug_domain,
				link_slug_domain as post_id,
				`count`
			FROM
				(
					SELECT
						link_slug_domain,
						count(*) as `count`
					FROM
						' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . " as lll
					WHERE
						link_type = '" . Lasso_Link_Location::LINK_TYPE_EXTERNAL . "'
						AND lll.display_type IN (" . $displays_in_condition . ")
					GROUP BY
						link_slug_domain
					ORDER BY
						NULL
				) as base
			WHERE
				link_slug_domain  <> '" . $this->current_domain . "'
				" . $search . '
				AND ' . $where . '
		';

		return $sql;
	}

	/**
	 * Get groups query
	 *
	 * @param string $search Search text.
	 * @param string $where  Where statement. Default to '1=1'.
	 */
	public function get_groups_query( $search, $where = '1=1' ) {
		$sql = "
			SELECT
				t.term_id as `post_id`,
				t.term_id,
				t.name as post_title,
				t.slug,
				CASE
					WHEN LENGTH(tt.description) > 237
						THEN CONCAT(SUBSTRING(tt.description, 1, 237), '...')
					ELSE tt.description
				END as description,
				SUM(
					CASE
						WHEN p.ID IS NOT NULL
							THEN 1
						ELSE 0
					END
				) as `count`
			FROM
					" . Model::get_wp_table_name( 'terms' ) . ' as t
				INNER JOIN
					' . Model::get_wp_table_name( 'term_taxonomy' ) . " as tt
						ON t.term_id = tt.term_id
						AND tt.taxonomy = '" . LASSO_CATEGORY . "'
				LEFT JOIN
					" . Model::get_wp_table_name( 'term_relationships' ) . ' as tr
						ON tt.term_taxonomy_id = tr.term_taxonomy_id
				LEFT JOIN
					' . Model::get_wp_table_name( 'posts' ) . " as p
						ON tr.object_id = p.ID
						AND p.post_type = '" . LASSO_POST_TYPE . "'
						AND p.post_status = 'publish'
			WHERE
				" . $where . '
				' . $search . '
			GROUP BY
				t.term_id,
				t.name,
				t.slug
		';

		return $sql;
	}

	/**
	 * Get urls in group
	 *
	 * @param string $group_slug Slug of group.
	 * @param string $where      Where statement. Default to '1=1'.
	 */
	public function get_urls_in_group( $group_slug = '', $where = '1=1' ) {
		if ( '' !== $group_slug ) {
			$group_slug = Model::prepare( 'AND t.slug = %s', $group_slug ); // phpcs:ignore
		}

		$sql = '
			SELECT
				t.term_id,
				t.name as term_name,
				t.slug as term_slug,
				t.term_group,
				p.ID,
				p.post_title,
				p.post_name
			FROM
					' . Model::get_wp_table_name( 'terms' ) . ' as t
				INNER JOIN
					' . Model::get_wp_table_name( 'term_taxonomy' ) . " as tt
						ON t.term_id = tt.term_id
						AND tt.taxonomy = '" . LASSO_CATEGORY . "'
				LEFT JOIN
					" . Model::get_wp_table_name( 'term_relationships' ) . ' as tr
						ON tt.term_taxonomy_id = tr.term_taxonomy_id
				INNER JOIN
					' . Model::get_wp_table_name( 'posts' ) . ' as p
						ON tr.object_id = p.ID
				LEFT JOIN
					' . Model::get_wp_table_name( LASSO_CATEGORY_ORDER_DB ) . " as o
						ON o.parent_slug = t.slug
						AND p.post_type = '" . LASSO_POST_TYPE . "'
						AND p.ID = o.item_id
						AND p.post_status = 'publish'
			WHERE
				" . $where . '
				' . $group_slug . "
				AND p.post_type = '" . LASSO_POST_TYPE . "'
				AND p.post_status = 'publish'
				AND tt.taxonomy = '" . LASSO_CATEGORY . "'
		";

		return $sql;
	}

	/**
	 * Get revert data of earnist shortcode
	 *
	 * @param int $legacy_id Lasso post id.
	 */
	public function earnist_shortcode_query( $legacy_id ) {
		if ( Model::column_exists( Model::get_wp_table_name( LASSO_REVERT_DB ), array( 'post_data' ) ) ) {
			$sql = '
				SELECT lasso_id
				FROM ' . Model::get_wp_table_name( LASSO_REVERT_DB ) . "
				WHERE
					post_data='" . $legacy_id . "'
					OR lasso_id='" . $legacy_id . "';
			";
		} else {
			$sql = '
				SELECT  lasso_id
				FROM ' . Model::get_wp_table_name( LASSO_REVERT_DB ) . "
				WHERE lasso_id='" . $legacy_id . "';
			";
		}

		return Model::get_results( $sql );
	}

	/**
	 * Get dashboard opportunities count
	 *
	 * @param string $search   Search text.
	 * @param array  $post_ids Filter by post ids. Default to empty array.
	 */
	public function get_dashboard_opportunities_count( $search, $post_ids = array() ) {
		$sql = '
			SELECT lasso_id, COUNT(*) as `opportunity_count`
			FROM (' . $this->get_link_opportunities_query( $search, 0, true, $post_ids ) . ') as base
			GROUP BY lasso_id
			HAVING COUNT(*) > 0
		';

		return Model::get_count( $sql );
	}

	/**
	 * Get url issue list
	 *
	 * @param int     $post_id      Post id. Default to 0.
	 * @param boolean $is_use_cache Is use cache.
	 */
	public function get_url_issue_list( $post_id = '0', $is_use_cache = false ) {
		$sql = '
			SELECT ui.id, issue_type
			FROM ' . Model::get_wp_table_name( LASSO_URL_ISSUE_DB ) . ' as ui
				INNER JOIN ' . Model::get_wp_table_name( 'posts' ) . " as p
				ON ui.id = p.ID AND p.post_type = '" . LASSO_POST_TYPE . "'
			WHERE
				ui.id = '" . $post_id . "'
				AND issue_resolved = 0
				AND is_ignored = 0
		";

		// ? Enable cache on frontend to prevent duplicate queries issue
		if ( ! is_admin() ) {
			$is_use_cache = true;
		}

		$result = Model::get_results( $sql, 'OBJECT', $is_use_cache );
		$result = ( isset( $result[0] ) && $result[0]->id > 0 ) ? $result : 0;

		return $result;
	}

	/**
	 * Get links have issue: broken link
	 *
	 * @param string $search Search text. Default to empty.
	 */
	public function get_url_broken_link_count( $search = '' ) {
		$join_url_detail = '';
		if ( $search ) {
			$join_url_detail = '
				LEFT JOIN ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . ' AS ud
				ON ud.lasso_id = p.ID
			';
		}

		$sql = '
			SELECT ui.id
			FROM ' . Model::get_wp_table_name( LASSO_URL_ISSUE_DB ) . ' AS ui
				INNER JOIN ' . Model::get_wp_table_name( 'posts' ) . " AS p
					ON  ui.id = p.ID AND p.post_type = '" . LASSO_POST_TYPE . "'
				" . $join_url_detail . "
			WHERE
				issue_resolved = 0
				AND ui.issue_type != '000'
				AND is_ignored = 0
				AND p.post_status = 'publish'
				" . $search . '
		';

		return Model::get_count( $sql );
	}

	/**
	 * Get links have issue: out of stock
	 *
	 * @param string $search Search text.
	 */
	public function get_url_out_of_stock_link_count( $search ) {
		$join_url_detail = '';
		if ( $search ) {
			$join_url_detail = '
				LEFT JOIN ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . ' AS ud
				ON ud.lasso_id = p.ID
			';
		}

		$sql = '
			SELECT ui.id
			FROM ' . Model::get_wp_table_name( LASSO_URL_ISSUE_DB ) . ' AS ui
				INNER JOIN ' . Model::get_wp_table_name( 'posts' ) . ' AS p
					ON ui.id = p.ID
				' . $join_url_detail . "
			WHERE
				issue_resolved = 0
				AND issue_type = '000'
				AND is_ignored = 0
				AND p.post_type = '" . LASSO_POST_TYPE . "'
				AND p.post_status = 'publish'
				" . $search;

		return Model::get_count( $sql );
	}

	/**
	 * Get revertable url query
	 *
	 * @param string $filter_plugin Plugin name.
	 */
	public function get_revertable_urls_query( $filter_plugin = null ) {
		$key                          = array_search( $filter_plugin, Setting_Enum::SUPPORT_IMPORT_PLUGINS, true );
		$array_support_import_plugins = array(
			Setting_Enum::PRETTY_LINK_SLUG,
			Setting_Enum::THIRSTYLINK_SLUG,
			Setting_Enum::SURL_SLUG,
			Setting_Enum::EARNIST_SLUG,
			Setting_Enum::AFFILIATE_URL_SLUG,
			Setting_Enum::AAWP_SLUG,
			Setting_Enum::EASYAZON_SLUG,
			Setting_Enum::AMA_LINKS_PRO_SLUG,
			Setting_Enum::EASY_AFFILIATE_LINK_SLUG,
		);

		if ( false === $key ) {
			$where = "`plugin` IN ('" . implode( "', '", $array_support_import_plugins ) . "')";
		} else {
			$where = '`plugin` = %s';
			$where = Model::prepare( $where, $key );
		}

		$sql = '
			SELECT 
				lasso_id AS import_id, 
				`plugin` AS import_source,
				old_uri,
				post_data
			FROM ' . ( new Revert() )->get_table_name() . '
			WHERE ' . $where . '
		';

		return $sql;
	}

	/**
	 * Get importable url query
	 *
	 * @param bool   $include_imported  Include imported or not. Default to true.
	 * @param string $search            Search text. Default to empty.
	 * @param string $group_by          Group by column. Default to empty.
	 * @param string $filter_plugin     Plugin name. Default to null.
	 * @param bool   $limit_each_plugin Limit each plugin. Default to false.
	 * @param bool   $is_count          Is count query. Default to false.
	 */
	public function get_importable_urls_query( $include_imported = true, $search = '', $group_by = '', $filter_plugin = null, $limit_each_plugin = false, $is_count = false ) {
		$amazon_products_tbl = ( new Amazon_Products() )->get_table_name();
		$link_locations_tbl  = ( new Link_Locations() )->get_table_name();
		$revert_tbl          = ( new Revert() )->get_table_name();
		$posts_tbl           = Model::get_wp_table_name( 'posts' );
		$postmeta_tbl        = Model::get_wp_table_name( 'postmeta' );
		$options_tbl         = Model::get_wp_table_name( 'options' );
		$pretty_link_tbl     = Model::get_wp_table_name( 'prli_links' );

		$is_prlipro_installed = $this->is_pretty_links_pro_installed();
		$is_aawp_installed    = $this->is_aawp_installed();
		$group_by             = $group_by ? " GROUP BY BASE.$group_by " : '';
		$limit_each_plugin    = $limit_each_plugin ? 'LIMIT 1' : '';

		$support_plugin             = Setting_Enum::SUPPORT_IMPORT_PLUGINS;
		$support_plugin_flip        = array_flip( $support_plugin );
		$post_status_allow          = "('publish', 'pending', 'draft', 'future', 'private', 'inherit', 'trash')";
		$sql                        = '';
		$no_duplicate_with_imported = '
			AND p.ID NOT IN (
				SELECT lasso_id
				FROM ' . $revert_tbl . '
			)
			AND p.ID NOT IN (
				SELECT post_data
				FROM ' . $revert_tbl . '
				WHERE post_data IS NOT NULL
			)
		';

		// ? SQL pre-processing
		if ( $is_prlipro_installed && $support_plugin[ Setting_Enum::PRETTY_LINK_SLUG ] === $filter_plugin ) {
			$prlipro_post_name = "
				CASE
					WHEN p.post_type = 'pretty-link'
						THEN CONVERT(pl.slug USING utf8)
					ELSE CONVERT(p.post_name USING utf8)
				END AS post_name,
			";
			$prlipro_join      = '
				LEFT JOIN ' . Model::get_wp_table_name( 'prli_links' ) . " AS pl
					ON p.post_type = 'pretty-link'
						AND p.id = pl.link_cpt_id
			";
		} else {
			$prlipro_post_name = 'CONVERT(p.post_name USING utf8) AS post_name,';
			$prlipro_join      = '';
		}

		// ? Start SQL Statement
		// AAWP plugin
		if ( ( $is_aawp_installed && empty( $filter_plugin ) ) || ( $support_plugin[ Setting_Enum::AAWP_SLUG ] === $filter_plugin ) ) {
			// ? aawp products
			$select = $is_count
				? "CONVERT(asin USING utf8) AS id, 'AAWP' AS import_source, CONVERT(title USING utf8) AS post_title"
				: "
					CONVERT(asin USING utf8) AS id,
					'aawp' AS post_type,
					'AAWP' AS import_source,
					CONVERT(asin USING utf8) AS post_name,
					CONVERT(title USING utf8) AS post_title,
					'' AS check_status,
					'' AS check_disabled
				";
			$sql   .= '
				SELECT ' . $select . '
				FROM
						' . Model::get_wp_table_name( 'aawp_products' ) . ' AS ap
					LEFT JOIN
						' . $revert_tbl . ' AS r
						ON CONVERT(ap.asin USING utf8) = CONVERT(r.old_uri USING utf8)
				WHERE
					r.old_uri IS NULL

				UNION
			';

			// ? aawp lists
			$select = $is_count
				? "id, 'AAWP' AS import_source, keywords AS post_title"
				: "
					id,
					'aawp_list' AS post_type,
					'AAWP' AS import_source,
					type AS post_name,
					keywords AS post_title,
					'' AS check_status,
					'' AS check_disabled
				";
			$sql   .= '
				SELECT ' . $select . '
				FROM ' . Model::get_wp_table_name( 'aawp_lists' ) . ' AS ap

				UNION
			';

			// ? aawp tables
			$aawp_table_only_import = $include_imported
				? ''
				: '
				AND ID NOT IN (
					SELECT post_data
					FROM ' . $revert_tbl . '
				)
			';

			$select = $is_count
				? "p.id, 'AAWP' AS import_source, CONVERT(p.post_title USING utf8) AS post_title"
				: "
					p.id,
					p.post_type,
					'AAWP' AS import_source,
					CONVERT(p.post_name USING utf8) AS post_name,
					CONVERT(p.post_title USING utf8) AS post_title,
					'' AS check_status,
					'' AS check_disabled
				";
			$sql    = $sql . '
				SELECT ' . $select . '
				FROM ' . $posts_tbl . " AS p
				WHERE
					post_type = 'aawp_table'
					AND post_status IN ('publish')
					" . $aawp_table_only_import . '

				UNION
			';
		}

		// ? Earnist plugin
		$select = $is_count
			? "p.id, 'Earnist' AS import_source, CONVERT(p.post_title USING utf8) AS post_title"
			: "
				p.id,
				p.post_type,
				'Earnist' AS import_source,
				CONVERT(p.post_name USING utf8) AS post_name,
				CONVERT(p.post_title USING utf8) AS post_title,
				'' AS check_status,
				'' AS check_disabled
			";
		$sql    = $sql . '
			SELECT ' . $select . '
			FROM ' . $posts_tbl . " AS p
			WHERE
				post_type = 'earnist'
				AND p.ID NOT IN (
					SELECT post_id
					FROM " . $postmeta_tbl . "
					WHERE meta_key = 'old_status'
						AND meta_value != ''
				)
				AND post_status IN " . $post_status_allow
				. $no_duplicate_with_imported;

		// ? Pretty Links plugin
		if ( $is_prlipro_installed && ( empty( $filter_plugin ) || $support_plugin[ Setting_Enum::PRETTY_LINK_SLUG ] === $filter_plugin ) ) {
			$select = $is_count
				? "p.id, 'Pretty Links' AS import_source, CONVERT(p.post_title USING utf8) AS post_title"
				: "
					p.id,
					p.post_type,
					'Pretty Links' AS import_source,
					" . $prlipro_post_name . "
					CONVERT(p.post_title USING utf8) AS post_title,
					'' AS check_status,
					'' AS check_disabled
				";
			$sql    = $sql . '
				UNION

				SELECT ' . $select . '
				FROM ' . $posts_tbl . ' AS p
					' . $prlipro_join . "
				WHERE
					post_type = 'pretty-link'
					AND p.ID NOT IN (
						SELECT post_id
						FROM " . $postmeta_tbl . "
						WHERE meta_key = 'old_status'
							AND meta_value != ''
					)
					AND p.ID IN (
						SELECT link_cpt_id
						FROM " . $pretty_link_tbl . '
					)
					AND post_status IN ' . $post_status_allow
					. $no_duplicate_with_imported;
		}

		// ? Lasso Lite plugin
		if (
			Lasso_Helper::is_lasso_lite_plugin_actived()
			&& (
				empty( $filter_plugin )
				|| $support_plugin[ Setting_Enum::SURL_SLUG ] === $filter_plugin
			)
		) {
			$lasso_lite_url_detail_tbl = Model::get_wp_table_name( 'lasso_lite_url_details' );
			$select                    = $is_count
				? "ID, 'Lasso Lite / Simple URLs' AS import_source, post_title"
				: "
					ID,
					post_type,
					'Lasso Lite / Simple URLs' AS import_source,
					post_name,
					post_title,
					CASE
						WHEN ID IN (
							SELECT post_data
							FROM " . $revert_tbl . "
							WHERE plugin = 'surl'
						)
						THEN 'checked'
						ELSE ''
					END AS check_status,
					'' AS check_disabled
				";
			$sql                       = $sql . '
				UNION
	
				(
					SELECT DISTINCT ' . $select . '
					FROM ' . $posts_tbl . " AS p
					WHERE post_type = 'surl' AND ( 
						ID IN (
							SELECT post_id
							FROM " . $postmeta_tbl . " AS pm
							WHERE meta_key = '_surl_redirect'
						) OR ID IN (
							SELECT lasso_id
							FROM " . $lasso_lite_url_detail_tbl . '
						)
					)
					' . $limit_each_plugin . '
				)
			';
		}

		// ? Easyazon plugin
		if ( empty( $filter_plugin ) || $support_plugin[ Setting_Enum::EASYAZON_SLUG ] === $filter_plugin ) {
			$select = $is_count
				? "CONVERT(substring_index( substring_index(option_name, 'easyazon_item_', -1), '_', 1) USING utf8) AS id, 'EasyAzon' AS import_source, CONVERT(substring_index( option_name, '_', 4) USING utf8) AS post_title"
				: "
					CONVERT(substring_index( substring_index(option_name, 'easyazon_item_', -1), '_', 1) USING utf8) AS id,
					'easyazon' AS post_type,
					'EasyAzon' AS import_source,
					'' AS post_name,
					CONVERT(substring_index( option_name, '_', 4) USING utf8) AS post_title,
					CASE
						WHEN CONVERT(substring_index( substring_index(option_name, 'easyazon_item_', -1), '_', 1) USING utf8) IN (
							SELECT CONVERT(old_uri USING utf8) AS old_uri
							FROM " . $revert_tbl . "
							WHERE plugin = 'easyazon'
						)
						THEN 'checked'
						ELSE ''
					END AS check_status,
					'' AS check_disabled
				";
			$sql    = $sql . '
				UNION
	
				SELECT DISTINCT ' . $select . '
				FROM ' . $options_tbl . " AS ap
				WHERE option_name LIKE 'easyazon_item_%'
			";
		}

		// ? AmaLinks Pro plugin
		if ( empty( $filter_plugin ) || $support_plugin[ Setting_Enum::AMA_LINKS_PRO_SLUG ] === $filter_plugin ) {
			$select = $is_count
				? "CONVERT(product_id USING utf8) AS id, 'AmaLinks Pro' AS import_source, CONVERT(anchor_text USING utf8) AS post_title"
				: "
					CONVERT(product_id USING utf8) AS id,
					'amalinkspro' AS post_type,
					'AmaLinks Pro' AS import_source,
					CONVERT(link_slug USING utf8) AS post_name,
					CONVERT(anchor_text USING utf8) AS post_title,
					'' AS check_status,
					'' AS check_disabled
				";
			$sql    = $sql . '
				UNION
	
				SELECT ' . $select . '
				FROM ' . $link_locations_tbl . ' AS ap
				WHERE link_type = "External"
					AND display_type IN ("amalinkspro", "AMALink")
					AND ap.post_id = 0
			';
		}

		// ? Easy Affiliate Link plugin - EAL is having 2 types "HTML code" and "Text Link", we only get "text" value.
		if ( empty( $filter_plugin ) || $support_plugin[ Setting_Enum::EASY_AFFILIATE_LINK_SLUG ] === $filter_plugin ) {
			$select = $is_count
				? "p.ID AS id, 'Easy Affiliate Links' AS import_source, CONVERT(p.post_title USING utf8) AS post_title"
				: "
					p.ID AS id,
					p.post_type,
					'Easy Affiliate Links' AS import_source,
					CONVERT(p.post_name USING utf8) AS post_name,
					CONVERT(p.post_title USING utf8) AS post_title,
					'' AS check_status,
					'' AS check_disabled
				";
			$sql    = $sql . '
				UNION
	
				SELECT ' . $select . '
				FROM ' . $posts_tbl . ' AS p
					INNER JOIN ' . $postmeta_tbl . ' AS pom 
						ON p.ID = pom.post_id
				WHERE p.post_type = %s 
					AND pom.meta_value = %s
					' . $no_duplicate_with_imported . '
			';

			$sql = Model::prepare( $sql, Setting_Enum::EASY_AFFILIATE_LINK_SLUG, 'text' );
		}

		// ? Affiliate URL Automation plugin
		if ( empty( $filter_plugin ) || $support_plugin[ Setting_Enum::AFFILIATE_URL_SLUG ] === $filter_plugin ) {
			$select = $is_count
				? "p.ID AS id, 'Affiliate URLs' AS import_source, CONVERT(p.post_title USING utf8) AS post_title"
				: "
					p.ID AS id,
					p.post_type,
					'Affiliate URLs' AS import_source,
					CONVERT(p.post_name USING utf8) AS post_name,
					CONVERT(p.post_title USING utf8) AS post_title,
					'' AS check_status,
					'' AS check_disabled
				";
			$sql    = $sql . '
				UNION

				SELECT ' . $select . '
				FROM ' . $posts_tbl . ' AS p
					INNER JOIN ' . $postmeta_tbl . ' AS pom 
						ON p.ID = pom.post_id
				WHERE p.post_type = %s
					AND pom.meta_key = %s
					AND pom.meta_value IS NOT NULL
					AND pom.meta_value <> ""
					' . $no_duplicate_with_imported . '
			';

			$sql = Model::prepare( $sql, Setting_Enum::AFFILIATE_URL_SLUG, '_affiliate_url_redirect' );
		}

		// ? Thirsty Affiliates plugin
		if ( empty( $filter_plugin ) || $support_plugin[ Setting_Enum::THIRSTYLINK_SLUG ] === $filter_plugin ) {
			$select = $is_count
				? "p.ID AS id, 'Thirsty Affiliates' AS import_source, CONVERT(p.post_title USING utf8) AS post_title"
				: "
					p.ID AS id,
					p.post_type,
					'Thirsty Affiliates' AS import_source,
					CONVERT(p.post_name USING utf8) AS post_name,
					CONVERT(p.post_title USING utf8) AS post_title,
					'' AS check_status,
					'' AS check_disabled
				";
			$sql    = $sql . '
				UNION
	
				SELECT ' . $select . '
				FROM ' . $posts_tbl . ' AS p
				WHERE p.post_type = %s 
					AND p.ID IN (
						SELECT post_id
						FROM ' . $postmeta_tbl . '
						WHERE meta_key = %s
							AND meta_value IS NOT NULL
							AND meta_value <> ""
					)
					' . $no_duplicate_with_imported . '
			';

			$sql = Model::prepare( $sql, Setting_Enum::THIRSTYLINK_SLUG, '_ta_destination_url' );
		}

		// ? Site Stripe links
		if ( empty( $filter_plugin ) || Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE === $filter_plugin ) {
			$select = $is_count
				? "CONVERT(product_id USING utf8) AS id, %s AS import_source, CONVERT(CONCAT(product_id, ' ', display_Type) USING utf8) AS post_title"
				: "
					CONVERT(product_id USING utf8) AS id,
					%s AS post_type,
					%s AS import_source,
					CONVERT(MAX(link_slug) USING utf8) AS post_name,
					CONVERT(CONCAT(product_id, ' ', display_Type) USING utf8) AS post_title,
					'' AS check_status,
					'' AS check_disabled
				";
			$sql    = $sql . '
				UNION
	
				(
					SELECT ' . $select . '
					FROM ' . $link_locations_tbl . ' AS p
					WHERE link_type = %s
						AND product_id NOT IN (
							SELECT old_uri
							FROM ' . $revert_tbl . '
							WHERE plugin = %s
						)
					GROUP BY
						link_slug_domain,
						product_id,
						tracking_id
					' . $limit_each_plugin . '
				)
			';

			$sql = $is_count
				? Model::prepare(
					$sql,
					Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE,
					Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE,
					Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE
				)
				: Model::prepare(
					$sql,
					Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE,
					Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE,
					Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE,
					Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE
				);
		}

		if ( $include_imported ) {
			if ( in_array(
				$filter_plugin,
				array(
					$support_plugin[ Setting_Enum::PRETTY_LINK_SLUG ],
					$support_plugin[ Setting_Enum::THIRSTYLINK_SLUG ],
					$support_plugin[ Setting_Enum::SURL_SLUG ],
					$support_plugin[ Setting_Enum::EARNIST_SLUG ],
					$support_plugin[ Setting_Enum::AFFILIATE_URL_SLUG ],
					$support_plugin[ Setting_Enum::AAWP_SLUG ],
					$support_plugin[ Setting_Enum::EASY_AFFILIATE_LINK_SLUG ],
				),
				true
			)
			) {
				$r_plugin_where = "r.plugin IN ('%s')";
				$r_plugin_where = sprintf( $r_plugin_where, $support_plugin_flip[ $filter_plugin ] );
			} else {
				$r_plugin_where = "r.plugin IN ('%s', '%s', '%s', '%s', '%s', '%s', '%s')";
				$r_plugin_where = sprintf(
					$r_plugin_where,
					Setting_Enum::PRETTY_LINK_SLUG,
					Setting_Enum::THIRSTYLINK_SLUG,
					Setting_Enum::SURL_SLUG,
					Setting_Enum::EARNIST_SLUG,
					Setting_Enum::AFFILIATE_URL_SLUG,
					Setting_Enum::AAWP_SLUG,
					Setting_Enum::EASY_AFFILIATE_LINK_SLUG
				);
			}
			$select = $is_count
				? "
					p.id,
					CASE
						WHEN r.plugin = 'pretty-link'
							THEN 'Pretty Links'
						WHEN r.plugin = 'thirstylink'
							THEN 'Thirsty Affiliates'
						WHEN r.plugin = 'surl'
							THEN '" . $support_plugin[ Setting_Enum::SURL_SLUG ] . "'
						WHEN r.plugin = 'earnist'
							THEN 'Earnist'
						WHEN r.plugin = 'affiliate_url'
							THEN 'Affiliate URLs'
						WHEN r.plugin = 'aawp'
							THEN 'AAWP'
						WHEN r.plugin = 'easyazon'
							THEN 'EasyAzon'
						WHEN r.plugin = 'amalinks'
							THEN 'Amalinks Pro'
						WHEN r.plugin = 'easy_affiliate_link'
							THEN 'Easy Affiliate Links'
						ELSE 'Unknown'
					END AS import_source,
					CONVERT(p.post_title USING utf8) AS post_title
				"
				: "
					p.id,
					p.post_type,
					CASE
						WHEN r.plugin = 'pretty-link'
							THEN 'Pretty Links'
						WHEN r.plugin = 'thirstylink'
							THEN 'Thirsty Affiliates'
						WHEN r.plugin = 'surl'
							THEN '" . $support_plugin[ Setting_Enum::SURL_SLUG ] . "'
						WHEN r.plugin = 'earnist'
							THEN 'Earnist'
						WHEN r.plugin = 'affiliate_url'
							THEN 'Affiliate URLs'
						WHEN r.plugin = 'aawp'
							THEN 'AAWP'
						WHEN r.plugin = 'easyazon'
							THEN 'EasyAzon'
						WHEN r.plugin = 'amalinks'
							THEN 'Amalinks Pro'
						WHEN r.plugin = 'easy_affiliate_link'
							THEN 'Easy Affiliate Links'
						ELSE 'Unknown'
					END AS import_source,
					CASE
						WHEN r.plugin = 'aawp'
							THEN CONVERT(r.old_uri USING utf8)
						ELSE CONVERT(p.post_name USING utf8)
					END AS post_name,
					CONVERT(p.post_title USING utf8) AS post_title,
					'checked' AS check_status,
					'' AS check_disabled
				";
			$sql    = $sql . '
				UNION

				SELECT ' . $select . '
				FROM ' . $posts_tbl . ' AS p
					INNER JOIN
						' . $revert_tbl . " AS r
						ON p.id = r.lasso_id
						AND r.old_uri NOT LIKE '[amazon table%'
				WHERE
					$r_plugin_where
			";

			// ? AmaLinks Pro plugin - imported
			if ( empty( $filter_plugin ) || $support_plugin[ Setting_Enum::AFFILIATE_URL_SLUG ] === $filter_plugin ) {
				$select = $is_count
					? "CONVERT(r.lasso_id USING utf8) AS id, 
						'AmaLinks Pro' AS import_source,
						CASE
							WHEN CONVERT(la.default_product_name USING utf8) != ''
								THEN CONVERT(la.default_product_name USING utf8)
							WHEN CONVERT(ll.anchor_text USING utf8) != ''
								THEN CONVERT(ll.anchor_text USING utf8)
							WHEN CONVERT(wpp.post_title USING utf8) != ''
								THEN CONVERT(wpp.post_title USING utf8)
							ELSE ''
						END AS post_title"
					: "
						CONVERT(r.lasso_id USING utf8) AS id,
						'amalinkspro' AS post_type,
						'AmaLinks Pro' AS import_source,
						CASE
							WHEN CONVERT(ll.original_link_slug USING utf8) != ''
								THEN CONVERT(ll.original_link_slug USING utf8)
							WHEN CONVERT(la.monetized_url USING utf8) != ''
								THEN CONVERT(la.monetized_url USING utf8)
							WHEN CONVERT(wpp.guid USING utf8) != ''
								THEN CONVERT(wpp.guid USING utf8)
							ELSE ''
						END AS post_name,
						CASE
							WHEN CONVERT(la.default_product_name USING utf8) != ''
								THEN CONVERT(la.default_product_name USING utf8)
							WHEN CONVERT(ll.anchor_text USING utf8) != ''
								THEN CONVERT(ll.anchor_text USING utf8)
							WHEN CONVERT(wpp.post_title USING utf8) != ''
								THEN CONVERT(wpp.post_title USING utf8)
							ELSE ''
						END AS post_title,
						'checked' AS check_status,
						'' AS check_disabled
					";
				$sql    = $sql . '
					UNION
	
					SELECT DISTINCT ' . $select . '
					FROM ' . $revert_tbl . ' AS r
						LEFT JOIN ' . $posts_tbl . ' AS wpp
							ON r.lasso_id = wpp.ID
						LEFT JOIN ' . $link_locations_tbl . ' AS ll
							ON r.lasso_id = ll.post_id AND ll.original_link_slug like "[amalinkspro %"
						LEFT JOIN ' . $amazon_products_tbl . " AS la
							ON r.old_uri = la.amazon_id
					WHERE r.plugin = 'amalinkspro' 
						AND ll.product_id != ''
				";
			}

			// ? Site Stripe links
			if ( empty( $filter_plugin ) || Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE === $filter_plugin ) {
				$select = $is_count
					? "CONVERT(ll.product_id USING utf8) AS id, %s AS import_source, CONVERT(CONCAT(ll.product_id, ' ', ll.display_Type) USING utf8) AS post_title"
					: "
						CONVERT(ll.product_id USING utf8) AS id,
						%s AS post_type,
						%s AS import_source,
						CONVERT(MAX(r.post_data) USING utf8) AS post_name,
						CONVERT(CONCAT(ll.product_id, ' ', ll.display_Type) USING utf8) AS post_title,
						'checked' AS check_status,
						'' AS check_disabled
					";
				$sql    = $sql . '
					UNION
		
					SELECT ' . $select . '
					FROM ' . $revert_tbl . ' AS r
						LEFT JOIN ' . $link_locations_tbl . ' AS ll
							ON ll.product_id = r.old_uri
					WHERE plugin = %s
					GROUP BY
						r.old_uri,
						r.plugin
				';

				$sql = $is_count
					? Model::prepare(
						$sql,
						Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE,
						Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE
					)
					: Model::prepare(
						$sql,
						Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE,
						Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE,
						Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE
					);
			}
		}

		$include_imported_where = $include_imported ? '' : 'AND check_status != "checked"';

		$order_by = $is_count ? '' : 'ORDER BY BASE.check_status, BASE.import_source, BASE.post_title';
		$sql      = '
			SELECT *
			FROM
				(
					' . $sql . '
				) as BASE
			WHERE
				1=1
				' . $include_imported_where . '
				' . $search .
			$group_by . ' ' .
			$order_by . '
		';

		return $sql;
	}

	/**
	 * Get pretty link by id
	 *
	 * @param int $id Id of pretty link.
	 */
	public function get_pretty_link_by_id( $id ) {
		$sql = '
			SELECT *
			FROM ' . Model::get_wp_table_name( 'prli_links' ) . '
			WHERE link_cpt_id = ' . $id . ';
		';

		return Model::get_row( $sql );
	}

	/**
	 * Get list cpt ids from pretty link table
	 *
	 * @return string
	 */
	public function get_pretty_link_cpt_ids_query() {
		$sql = 'SELECT link_cpt_id FROM ' . Model::get_wp_table_name( 'prli_links' );

		return $sql;
	}

	/**
	 * Get posts and links with url.
	 *
	 * @param string $search_url URL.
	 */
	public function get_posts_and_links_with_url( $search_url ) {
		$sql = '
			SELECT DISTINCT detection_id, id
			FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . "
			WHERE link_slug = '" . $search_url . "'
				OR link_slug = '" . rtrim( $search_url, '/' ) . "';
		";

		return $sql;
	}

	/**
	 * Process import
	 *
	 * @param int    $id          Post id.
	 * @param string $slug        Link slug.
	 * @param string $old_uri     Old URI.
	 * @param string $plugin      Plugin name.
	 * @param string $old_post_id Old post id before importing or post data.
	 */
	public function process_import( $id, $slug, $old_uri, $plugin, $old_post_id = '' ) {
		if ( empty( $id ) || empty( $slug ) || is_wp_error( $id ) ) {
			return false;
		}

		clean_post_cache( $id );

		$result1 = true;
		if ( ! in_array( $plugin, array( Setting_Enum::AAWP_SLUG, Setting_Enum::AMA_LINKS_PRO_SLUG, Setting_Enum::EASYAZON_SLUG ), true ) ) {
			// ? Flip post time and potentially the slug
			$update_sql = '
				UPDATE ' . Model::get_wp_table_name( 'posts' ) . '
				SET
					post_name = %s,
					post_type = %s,
					post_modified = NOW(),
					post_modified_gmt = NOW()
				WHERE id = %d
			';
			$update_sql = Model::prepare( $update_sql, $slug, LASSO_POST_TYPE, $id ); // phpcs:ignore

			if ( LASSO_POST_TYPE !== get_post_type( $id ) ) {
				Model::query( $update_sql );
			}

			$result1 = LASSO_POST_TYPE === get_post_type( $id );
		}

		// ? Log what we imported for potential reverts
		$insert_sql = '
			INSERT INTO ' . ( new Revert() )->get_table_name() . ' (lasso_id, post_data, old_uri, plugin, revert_dt)
			VALUES (%d, %s, %s, %s, NOW());
		';
		$lasso_id   = $id;
		$insert_sql = Model::prepare( $insert_sql, $lasso_id, $old_post_id, $old_uri, $plugin );
		$result2    = Model::query( $insert_sql );

		clean_post_cache( $id );

		return $result1 && $result2;
	}

	/**
	 * Process revert
	 *
	 * @param int         $id               Post id.
	 * @param bool        $custom_post_type It is custom post type or not. Default to true.
	 * @param string|bool $import_source    Import plugin source. Default to false.
	 */
	public function process_revert( $id, $custom_post_type = true, $import_source = false ) {
		// ? Get post type from revert table
		if ( empty( $id ) ) {
			return false;
		}

		$plugin       = false;
		$result1      = true;
		$model_revert = new Revert();

		if ( Setting_Enum::SUPPORT_IMPORT_PLUGINS[ Setting_Enum::SURL_SLUG ] === $import_source ) {
			$plugin = Setting_Enum::SURL_SLUG;
		} elseif ( Setting_Enum::SUPPORT_IMPORT_PLUGINS[ Setting_Enum::EASY_AFFILIATE_LINK_SLUG ] === $import_source ) {
			$plugin = Setting_Enum::EASY_AFFILIATE_LINK_SLUG;
		} elseif ( Setting_Enum::SUPPORT_IMPORT_PLUGINS[ Setting_Enum::PRETTY_LINK_SLUG ] === $import_source ) {
			$plugin = Setting_Enum::PRETTY_LINK_SLUG;
		} elseif ( Setting_Enum::SUPPORT_IMPORT_PLUGINS[ Setting_Enum::THIRSTYLINK_SLUG ] === $import_source ) {
			$plugin = Setting_Enum::THIRSTYLINK_SLUG;
		} elseif ( $import_source ) {
			$plugin = $import_source;
		}

		if ( $custom_post_type ) {
			$revert_data = $model_revert->get_revert_data( $id, $plugin );
			$post_type   = get_post_type( $id );

			if ( $revert_data && LASSO_POST_TYPE === $post_type ) {
				// ? Switch back
				$revert_plugin = $revert_data->get_plugin();
				$posts_tbl     = Model::get_wp_table_name( 'posts' );
				if ( 'pretty-link' === $revert_plugin ) {
					$pretty_link_data = $this->get_pretty_link_by_id( $id );
					$update_sql       = '
						UPDATE ' . $posts_tbl . '
						SET
							post_name = %s,
							post_type = %s
						WHERE id = %d
					';
					$update_sql       = Model::prepare( $update_sql, $pretty_link_data->slug, $revert_plugin, $id );
				} else {
					$update_sql = '
						UPDATE ' . $posts_tbl . '
						SET post_type = %s
						WHERE id = %d
					';
					$update_sql = Model::prepare( $update_sql, $revert_plugin, $id );
				}
				$result1 = Model::query( $update_sql );

				do_action( Lasso_Cron::FILTER_FIX_LASSO_IMPORT_REVERT_POST_NAME, $id, $revert_plugin );
			}
		}

		// ? Delete tracking record
		$delete_sql = '
			DELETE FROM ' . $model_revert->get_table_name() . '
			WHERE 
				(lasso_id = %d OR post_data = %d)
				AND old_uri NOT LIKE %s -- Do not delete AAWP table has the same lasso_id with the current import item
		';
		$delete_sql = Model::prepare( $delete_sql, $id, $id, '[amazon table%' );

		if ( Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE === $import_source ) {
			$delete_sql = '
				DELETE FROM ' . $model_revert->get_table_name() . '
				WHERE 
					old_uri = %s
					AND plugin = %s
			';
			$delete_sql = Model::prepare( $delete_sql, $id, Lasso_Link_Location::DISPLAY_TYPE_SITE_STRIPE );
		}

		if ( $plugin ) {
			$delete_sql .= '
				AND plugin = %s
			';
			$delete_sql  = Model::prepare( $delete_sql, $plugin );
		}

		$result2 = Model::query( $delete_sql );

		clean_post_cache( $id );

		return $result1 && $result2;
	}

	/**
	 * Process dismiss opportunity
	 *
	 * @param string $link_type Link type.
	 * @param int    $link_id   Link id. Id in link location table or keyword location table.
	 */
	public function process_dismiss_opportunity( $link_type, $link_id ) {
		if ( 'link' === $link_type ) {
			$sql = '
				UPDATE ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . '
				SET is_ignored = 1
				WHERE id = ' . $link_id . ';
			';
		} else {
			$sql = '
				UPDATE ' . Model::get_wp_table_name( LASSO_KEYWORD_LOCATIONS ) . '
				SET is_ignored = 1
				WHERE id = ' . $link_id . ';
			';
		}

		return Model::query( $sql );
	}

	/**
	 * Check whether pretty links plugin is install or not
	 */
	/**
	// public function is_pretty_links_installed() {
	//  return Model::table_exists( Model::get_wp_table_name( 'prli_links' ) );
	// }
	 */

	/**
	 * Check whether pretty links pro plugin is install or not
	 */
	public function is_pretty_links_pro_installed() {
		return Model::column_exists( Model::get_wp_table_name( 'prli_links' ), array( 'link_cpt_id' ) );
	}

	/**
	 * Check whether aawp plugin is installed or not
	 */
	public function is_aawp_installed() {
		return Model::table_exists( Model::get_wp_table_name( 'aawp_products' ) );
	}

	/**
	 * Get aawp product
	 *
	 * @param string $product_id Amazon product id.
	 */
	public function get_aawp_product( $product_id ) {
		$sql = '
			SELECT *
			FROM ' . Model::get_wp_table_name( 'aawp_products' ) . "
			WHERE asin = '" . $product_id . "'
		";

		$row = Model::get_row( $sql );

		if ( $row ) {
			$lasso_amazon_api = new Lasso_Amazon_Api();

			$row_url = maybe_unserialize( $row->urls ?? $row->url ?? '' );
			$url     = $row_url['basic'] ?? $row_url;
			$url     = is_string( $url ) ? $url : '';

			$row->url = $lasso_amazon_api->get_amazon_product_url( $url, true, false );

			$images    = $row->image_ids;
			$images    = explode( ',', $images );
			$image_id  = $images[0];
			$image_url = 'https://m.media-amazon.com/images/I/' . $image_id . '.jpg';

			$row->image_url = $image_url;
			$row->features  = maybe_unserialize( $row->features ?? array() ); // phpcs:ignore
		}

		return $row;
	}

	/**
	 * Check whether amazon product is imported from AAWP or not
	 *
	 * @param string $amazon_id Amazon product id.
	 * @param string $lasso_id  Lasso post id.
	 */
	public function is_imported_from_aawp( $amazon_id, $lasso_id ) {
		$sql = '
			SELECT *
			FROM ' . Model::get_wp_table_name( LASSO_REVERT_DB ) . "
			WHERE old_uri = '" . $amazon_id . "' 
				AND lasso_id = '" . $lasso_id . "' 
				AND plugin = 'aawp'
		";
		$row = Model::get_row( $sql );

		return $row ? true : false;
	}

	/**
	 * Get aawp lists
	 *
	 * @param string $id Id.
	 */
	public function get_aawp_list( $id ) {
		$sql = '
			SELECT *
			FROM ' . Model::get_wp_table_name( 'aawp_lists' ) . '
			WHERE id = ' . $id . '
		';

		return Model::get_row( $sql );
	}

	/**
	 * Set order by a sql query
	 *
	 * @param string $sql        Sql query.
	 * @param string $order_by   Number of page.
	 * @param string $order_type Number of results. Default to 10.
	 * @param string $sub_order_by The sub order by query. Default to empty.
	 */
	public function set_order( $sql, $order_by, $order_type, $sub_order_by = '' ) {
		if ( '' !== $order_by && '' !== $order_type ) {
			$sql = $sql . ' ORDER BY ' . $order_by . ' ' . $order_type;

			if ( $sub_order_by ) {
				$sql .= $sub_order_by;
			}

			return $sql;
		} else {
			return $sql;
		}
	}

	/**
	 * Paginate items by a sql query
	 *
	 * @param string $sql   Sql query.
	 * @param int    $page  Number of page.
	 * @param int    $limit Number of results. Default to 10.
	 */
	public function paginate( $sql, $page, $limit = 10 ) {
		$start_index = ( $page - 1 ) * $limit;
		return $sql . ' LIMIT ' . $start_index . ', ' . $limit;
	}

	/**
	 * Count total keywords
	 */
	public function saved_keywords_count() {
		$count_sql = 'SELECT count(*) as `count` FROM ' . Model::get_wp_table_name( LASSO_TRACKED_KEYWORDS ) . ';';

		$result = Model::get_results( $count_sql );
		$result = ( isset( $result[0] ) && $result[0]->count > 0 ) ? $result[0]->count : 0;
		$result = intval( $result );

		return $result;
	}

	/**
	 * Count link in link locations table (by post id or slug)
	 *
	 * @param int  $post_id Post id.
	 * @param bool $by_slug Count by slug. Default to false.
	 */
	public function get_link_location_count( $post_id, $by_slug = false ) {
		$where_condition = '';
		if ( $by_slug ) {
			$where_condition = " link_slug = '" . get_permalink( $post_id ) . "' ";
		} else {
			$where_condition = " detection_id = '$post_id' ";
		}

		return Model::get_var( 'SELECT COUNT(*) FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . ' WHERE ' . $where_condition );
	}

	/**
	 * Get link by anchor text
	 *
	 * @param string $link_text Text.
	 */
	public function get_links_by_anchor_text( $link_text ) {
		$link_text = esc_sql( $link_text );

		return Model::get_var( 'select post_id from ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . " where anchor_text like '%$link_text%'" );
	}

	/**
	 * Get amazon tracking id
	 */
	public function get_amazon_tracking_ids() {
		return Model::get_results( 'SELECT * FROM ' . Model::get_wp_table_name( LASSO_AMAZON_TRACKING_IDS ), 'ARRAY_A' );
	}

	/**
	 * Get url detail
	 *
	 * @param int     $lasso_id     Lasso post id.
	 * @param boolean $is_use_cache Is use cache.
	 */
	public function get_url_details( $lasso_id, $is_use_cache = false ) {
		// ? Enable cache on frontend to prevent duplicate queries issue
		if ( ! is_admin() ) {
			$is_use_cache = true;
		}

		return Model::get_row( 'SELECT * FROM ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . ' WHERE lasso_id=' . $lasso_id, 'OBJECT', $is_use_cache );
	}

	/**
	 * Get url detail by url
	 *
	 * @param string $url URL.
	 */
	public function get_url_details_by_url( $url ) {
		$url   = trim( $url, '/' );
		$url_2 = $url . '/';

		$sql     = '
			SELECT lud.* 
			FROM ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . ' AS lud
			LEFT JOIN ' . Model::get_wp_table_name( 'posts' ) . " AS wpp
			ON lud.lasso_id = wpp.ID
			WHERE ( redirect_url LIKE %s OR redirect_url LIKE %s )
				AND wpp.post_type = %s
				AND wpp.ID != ''
		";
		$prepare = Model::prepare( $sql, $url, $url_2, LASSO_POST_TYPE ); // phpcs:ignore

		return Model::get_row( $prepare, 'OBJECT', true );
	}

	/**
	 * Get url detail by product id (Amazon/Extend product)
	 *
	 * @param string $product_id   Product id.
	 * @param string $product_type Product type.
	 * @param bool   $fetch_all    Is fetch all rows. Defautl to false.
	 * @param string $product_url  Product url. Default to empty.
	 */
	public function get_url_details_by_product_id( $product_id, $product_type, $fetch_all = false, $product_url = '' ) {
		if ( ! $product_id ) {
			return $fetch_all ? array() : null;
		}

		if ( Lasso_Amazon_Api::PRODUCT_TYPE === $product_type && $product_url ) {
			$product_id = Lasso_Amazon_Api::get_product_id_country_by_url( $product_url );
		}

		$sql     = '
			SELECT * 
			FROM ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . ' as lud
			LEFT JOIN ' . Model::get_wp_table_name( 'posts' ) . ' as wpp
			ON lud.lasso_id = wpp.ID
			WHERE lud.product_id = %s 
				AND wpp.ID is not null 
				AND wpp.post_status = "publish" 
				AND wpp.post_type = %s 
				AND lud.product_type = %s
			ORDER BY lasso_id desc
		';
		$prepare = Model::prepare( $sql, $product_id, LASSO_POST_TYPE, $product_type ); // phpcs:ignore

		return $fetch_all ? Model::get_results( $prepare ) : Model::get_row( $prepare );
	}

	/**
	 * Get lasso post by uri
	 *
	 * @param string $uri Uri.
	 */
	public function get_lasso_by_uri( $uri ) {
		if ( empty( $uri ) ) {
			return null;
		}

		$explode   = explode( '/', $uri );
		$post_name = end( $explode );

		$rewrite_slug = Lasso_Setting::lasso_get_setting( 'rewrite_slug' );
		if ( ( $rewrite_slug && $rewrite_slug !== $explode[0] ) || ( ! $rewrite_slug && count( $explode ) === 2 ) ) {
			return null;
		}

		$sql     = '
			SELECT * 
			FROM ' . Model::get_wp_table_name( 'posts' ) . ' 
			WHERE post_name = %s 
				AND post_type = %s
		';
		$prepare = Model::prepare( $sql, $post_name, LASSO_POST_TYPE ); // phpcs:ignore

		return Model::get_row( $prepare, 'OBJECT', true );
	}

	/**
	 * Update data in url details table
	 *
	 * @param int    $lasso_id       Lasso post id.
	 * @param string $redirect_url   Redirect url.
	 * @param string $base_domain    Base domain.
	 * @param int    $is_opportunity Is Opportunity.
	 * @param string $product_id     Product id. Default to empty.
	 * @param string $product_type   Product type. Default to empty.
	 */
	public function update_url_details( $lasso_id, $redirect_url, $base_domain, $is_opportunity, $product_id = '', $product_type = '' ) {
		$url_detail_model = new Url_Details();
		$redirect_url     = trim( $redirect_url );
		$redirect_url     = addcslashes( $redirect_url, "'" );

		if ( Lasso_Amazon_Api::is_amazon_url( $redirect_url ) ) {
			$product_id = Lasso_Amazon_Api::get_product_id_country_by_url( $redirect_url );
			$product_id = $product_id ? $product_id : '';
		}

		$sql = '
			INSERT INTO ' . $url_detail_model->get_table_name() . ' (lasso_id, redirect_url, base_domain, is_opportunity, product_id, product_type)
			VALUES (%d, %s, %s, %d, %s, %s) 
			ON DUPLICATE KEY
				UPDATE
					redirect_url = %s,
					base_domain = %s,
					is_opportunity = %d,
					product_id = %s,
					product_type = %s
		';

		$prepare = Url_Details::prepare(
			$sql,
			// ? insert
			$lasso_id,
			$redirect_url,
			$base_domain,
			$is_opportunity,
			$product_id,
			$product_type,
			// ? on duplicate update
			$redirect_url,
			$base_domain,
			$is_opportunity,
			$product_id,
			$product_type
		);

		Url_Details::query( $prepare );

		// ? Unset deprecated cache
		Lasso_Cache_Per_Process::get_instance()->un_set( Lasso_Amazon_Api::OBJECT_KEY . '_' . Lasso_Amazon_Api::FUNCTION_NAME_GET_LASSO_ID_BY_PRODUCT_ID_AND_TYPE . '_' . $product_id . '_' . $product_type );
	}

	/**
	 * Get query link location and issue
	 */
	public function get_query_link_location_and_issue() {
		$query = '
			SELECT p.ID, ul.issue_resolved
			FROM ' . Model::get_wp_table_name( 'posts' ) . ' as p
				LEFT JOIN ' . Model::get_wp_table_name( LASSO_URL_ISSUE_DB ) . ' as ul
				ON p.id = ul.id
			WHERE p.id = %d;
		';

		return $query;
	}

	/**
	 * Get query issue insert
	 */
	public function get_query_issue_insert() {
		$query = '
			INSERT INTO ' . Model::get_wp_table_name( LASSO_URL_ISSUE_DB ) . '
				(
					id,
					detection_date,
					issue_type,
					issue_slug,
					issue_resolved,
					issue_resolved_dt,
					is_ignored
				)
			VALUES
				( %d, %s, %s, %s, %d, %s, %d )
			ON DUPLICATE KEY UPDATE
				issue_resolved = %s,
				issue_resolved_dt = %s,
				issue_slug = %s,
				issue_type = %s
		';

		return $query;
	}

	/**
	 * Check issue queue
	 */
	public function check_issue_queue_query() {
		// ? delete data in lasso url issues that the lasso id is not a lasso post in the posts table
		$query = '
			DELETE FROM ' . Model::get_wp_table_name( LASSO_URL_ISSUE_DB ) . '
			WHERE id NOT IN (
				SELECT ID
				FROM ' . Model::get_wp_table_name( 'posts' ) . '
				WHERE post_type = "' . LASSO_POST_TYPE . '"
			)
		';
		Model::query( $query );

		// ? delete data in lasso url details that the lasso id is not a lasso post in the posts table
		$query = '
			DELETE FROM ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . '
			WHERE lasso_id NOT IN (
				SELECT ID
				FROM ' . Model::get_wp_table_name( 'posts' ) . '
				WHERE post_type IN (%s, %s)
			)
		';
		$query = Model::prepare( $query, LASSO_POST_TYPE, Setting_Enum::SURL_SLUG );
		Model::query( $query );

		// ? Get Lasso Posts to check the status issues
		// ? We should exclude amazon product link if the "Update Amazon pricing daily" setting was enabled
		$amazon_update_pricing        = Lasso_Setting::lasso_get_setting( 'amazon_update_pricing_hourly', true );
		$exclude_amazon_product_query = '';
		if ( $amazon_update_pricing ) {
			$exclude_amazon_product_query = '
				AND p.ID NOT IN (
					SELECT ud.lasso_id
					FROM ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . ' AS ud
					INNER JOIN ' . Model::get_wp_table_name( 'posts' ) . ' AS po
						ON po.ID = ud.lasso_id
					WHERE ud.product_type = %s
						AND ud.product_id <> ""
						AND ud.product_id IS NOT NULL
						AND po.post_type = %s
						AND po.post_status = %s
				)
			';
			$exclude_amazon_product_query = Model::prepare( $exclude_amazon_product_query, 'amazon', LASSO_POST_TYPE, 'publish' );
		}

		$query = '
			SELECT
				DISTINCT
				p.id,
				lui.issue_resolved,
                lui.issue_resolved_dt
			FROM
				' . Model::get_wp_table_name( 'posts' ) . ' AS p
				LEFT JOIN
					' . Model::get_wp_table_name( LASSO_URL_ISSUE_DB ) . " AS lui
					ON p.ID = lui.id
			WHERE
				p.post_type = '" . LASSO_POST_TYPE . "'
				AND p.post_status = 'publish'
				$exclude_amazon_product_query
			ORDER BY
				ISNULL(lui.issue_resolved),
				lui.issue_resolved ASC,
				lui.issue_resolved_dt ASC
		";

		return $query;
	}

	/**
	 * Resolve an issue in url issues table
	 *
	 * @param int $id Id in url issues table.
	 */
	public function resolve_issue( $id ) {
		if ( intval( $id ) <= 0 ) {
			return false;
		}

		$now = gmdate( 'Y-m-d h:i:m' );
		$sql = '
			UPDATE `' . Model::get_wp_table_name( LASSO_URL_ISSUE_DB ) . "`
			SET issue_resolved = 1, issue_resolved_dt = '" . $now . "', total_404_request = 0
			WHERE id = " . $id . ';
		';

		return Model::query( $sql );
	}

	/**
	 * Resolve an issue in url issues table
	 *
	 * @param int $url        Url issues table.
	 * @param int $issue_type Issue type (status code).
	 */
	public function resolve_issue_by_url( $url, $issue_type ) {
		if ( '' === $url || ! $url ) {
			return false;
		}

		$url = explode( '?', $url )[0] ?? $url;

		$now = gmdate( 'Y-m-d h:i:m' );
		$sql = '
			UPDATE `' . Model::get_wp_table_name( LASSO_URL_ISSUE_DB ) . "`
			SET issue_resolved = 1, issue_resolved_dt = '" . $now . "'
			WHERE issue_slug like '%" . $url . "%' and issue_type = '" . $issue_type . "'
		";

		return Model::query( $sql );
	}

	/**
	 * Resolve an issue in url issues table
	 *
	 * @param int $id         Id in url issues table.
	 * @param int $issue_type Issue type (status code).
	 */
	public function resolve_issue_by_type( $id, $issue_type ) {
		if ( intval( $id ) <= 0 ) {
			return false;
		}

		$now     = gmdate( 'Y-m-d h:i:m' );
		$sql     = '
			UPDATE `' . Model::get_wp_table_name( LASSO_URL_ISSUE_DB ) . "`
			SET issue_resolved = 1, issue_resolved_dt = '" . $now . '
			WHERE id = %d and issue_type = %s
		';
		$prepare = Model::prepare( $sql, $id, $issue_type ); // phpcs:ignore

		return Model::query( $prepare );
	}

	/**
	 * Resolve an issue in url issues table
	 *
	 * @param string $product_id   Product id.
	 * @param string $product_type Product type.
	 */
	public function resolve_product_out_of_stock( $product_id, $product_type ) {
		$now                      = gmdate( 'Y-m-d h:i:m' );
		$sql                      = '
			UPDATE ' . Model::get_wp_table_name( LASSO_URL_ISSUE_DB ) . '
			SET issue_resolved = 1, issue_resolved_dt = %s
			WHERE issue_type = %s AND id IN (
				SELECT lasso_id AS id
				FROM ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . '
				WHERE 
					product_id = %s 
					AND product_type = %s
			)
		';
		$prepare = Model::prepare( $sql, $now, '000', $product_id, $product_type ); // phpcs:ignore
		$resolve_issue_url_detail = Model::query( $prepare );

		$sql                  = '
			UPDATE ' . ( new Amazon_Products() )->get_table_name() . '
			SET out_of_stock = 0
			WHERE amazon_id = %s
		';
		$prepare = Model::prepare( $sql, $product_id); // phpcs:ignore
		$resolve_issue_amazon = Model::query( $prepare );

		return $resolve_issue_url_detail && $resolve_issue_amazon;
	}

	/**
	 * Remove all links of a post in link location table
	 *
	 * @param int $post_id Post id.
	 */
	public function remove_all_link_location_data( $post_id ) {
		$sql     = '
			DELETE FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . '
			WHERE detection_id = %d;
		';
		$prepare = Model::prepare( $sql, $post_id ); // phpcs:ignore

		return Model::query( $prepare );
	}

	/**
	 * Get external non-amazon links in link location table
	 */
	public function get_amazon_short_links_from_ll() {
		$amazon_short_link_in_condition = "'" . implode( "', '", Lasso_Amazon_Api::SHORT_LINK_DOMAINS ) . "'";

		$sql = '
			SELECT id, post_id, detection_id, link_slug, original_link_slug
			FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . '
			WHERE link_slug_domain IN (' . $amazon_short_link_in_condition . ')
			ORDER BY id ASC
			LIMIT 50;
		';

		return Model::get_results( $sql );
	}

	/**
	 * Get mysql query to get amazon products need to be update pricing
	 */
	public function get_sql_query_amazon_products_need_to_be_updated() {
		$now             = gmdate( 'Y-m-d H:i:s', time() );
		$posts           = Model::get_wp_table_name( 'posts' );
		$amazon_products = new Amazon_Products();
		$url_details     = new Url_Details();
		$sql             = '
			SELECT 
				ap.*, ud.redirect_url as amazon_url
			FROM ' . $url_details->get_table_name() . ' AS ud
				LEFT JOIN ' . $posts . ' AS p
					ON ud.lasso_id = p.ID
				LEFT JOIN ' . $amazon_products->get_table_name() . ' AS ap
					ON ap.amazon_id = ud.product_id
			WHERE p.post_type = %s
				AND p.post_status = %s
				AND ud.product_type = %s
				AND (ap.last_updated < DATE_SUB(%s, INTERVAL 24 HOUR) OR ap.last_updated IS NULL)
			ORDER BY 
				ap.last_updated ASC
		';
		$prepare         = Model::prepare( $sql, LASSO_POST_TYPE, 'publish', 'amazon', $now );

		return $prepare;
	}

	/**
	 * Get products in wp_lasso_amazon_products table
	 *
	 * @param int $limit Number of results. Default to 100.
	 */
	public function get_amazon_product_in_db( $limit = 100 ) {
		$mysql   = $this->get_sql_query_amazon_products_need_to_be_updated();
		$mysql  .= '
			LIMIT %d
		';
		$prepare = Model::prepare( $mysql, $limit ); // phpcs:ignore

		return Model::get_results( $prepare, ARRAY_A );
	}

	/**
	 * Count total products in wp_lasso_amazon_products table
	 */
	public function count_amazon_product_in_db() {
		$amz_tbl = $this->get_sql_query_amazon_products_need_to_be_updated();
		$mysql   = '
			select count(amz_tbl.amazon_id) as count
			from (' . $amz_tbl . ') as amz_tbl
		';

		$result = Model::get_col( $mysql );

		return intval( $result[0] ?? 0 );
	}

	/**
	 * Get old amazon links
	 */
	public function get_old_amazon_links() {
		$amazon_in_condition = $this->get_amazon_domains_in_clause();

		$query = '
			SELECT lud.*
			FROM ' . Model::get_wp_table_name( 'posts' ) . ' as p
			LEFT JOIN ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . " as lud
				ON p.ID = lud.lasso_id
			WHERE
				p.post_type='" . LASSO_POST_TYPE . "'
				AND lud.base_domain IN (" . $amazon_in_condition . ')
		';

		return Model::get_results( $query );
	}

	/**
	 * Update last_updated of amazon product and set the product is 404 issue in the issue table
	 *
	 * @param string $product_id Amazon product id.
	 * @param int    $error      Error code.
	 */
	public function update_amazon_last_updated( $product_id, $error = -1 ) {
		$now     = gmdate( 'Y-m-d H:i:s', time() );
		$sql     = '
			update ' . Model::get_wp_table_name( LASSO_AMAZON_PRODUCTS_DB ) . '
			set last_updated = %s
			where amazon_id = %s
		';
		$prepare = Model::prepare( $sql, $now, $product_id ); // phpcs:ignore
		Model::query( $prepare );

		$error      = strval( $error );
		$amazon_api = new Lasso_Amazon_Api();
		if ( '404' === $error || '000' === $error ) {
			$product_url = Lasso_Amazon_Api::get_default_product_domain( $product_id );
			$post_data   = $amazon_api->get_lasso_id_from_amazon_url( $product_url );
			$lasso_id    = $post_data['post_id'] ?? false;
			$lasso_url   = $post_data['monetized_url'] ?? '';
			if ( $lasso_id ) {
				$issue_resolved    = 0;
				$is_ignored        = 0;
				$issue_resolved_dt = '';
				// ? issue_resolved null means lasso_url_don't have a entry
				$sql = $this->get_query_issue_insert();
				$sql = Model::prepare( // phpcs:ignore
					$sql, // phpcs:ignore
					$lasso_id,
					$now,
					$error,
					$lasso_url,
					$issue_resolved,
					$issue_resolved_dt,
					$is_ignored,
					// ? update
					$issue_resolved,
					$issue_resolved_dt,
					$lasso_url,
					$error
				);
				Model::query( $sql );
			}
		}

		return true;
	}

	/**
	 * Get all links by lasso post id
	 *
	 * @param int $lasso_id Lasso post id.
	 */
	public function get_links_locations_by_lasso_id( $lasso_id ) {
		$sql = '
			SELECT id, detection_id, link_slug
			FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . "
			WHERE post_id = '" . $lasso_id . "'
		";

		return Model::get_results( $sql );
	}

	/**
	 * Update original link
	 *
	 * @param int    $id                         Id of link in link location table.
	 * @param string $original_link              Original link.
	 * @param bool   $is_update_origin_link_slug Is update column origin_link_slug. Default to false.
	 */
	public function update_amazon_links_ll_query( $id, $original_link, $is_update_origin_link_slug = false ) {
		$link_slug_domain  = Lasso_Helper::get_base_domain( $original_link );
		$additional_update = '';
		$product_id        = Lasso_Amazon_Api::get_product_id_by_url( $original_link );
		$tracking_id       = Lasso_Amazon_Api::get_amazon_tracking_id_by_url( $original_link );

		if ( $product_id ) {
			$additional_update .= ", product_id = '$product_id'";
		}

		if ( $tracking_id ) {
			$additional_update .= ", tracking_id = '$tracking_id'";
		}

		if ( $is_update_origin_link_slug ) {
			$additional_update .= ", original_link_slug = '$original_link'";
		}

		$query = '
			UPDATE ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . '
			SET 
				link_slug = %s,
				link_slug_domain = %s
				' . $additional_update . '
			WHERE
				id = %s;
		';

		$sql = Model::prepare( $query, $original_link, $link_slug_domain, $id ); // phpcs:ignore

		return $sql;
	}

	/**
	 * Get wrong amazon links
	 */
	public function get_wrong_amazon_links() {
		$sql = '
			select *
			from ' . Model::get_wp_table_name( LASSO_AMAZON_PRODUCTS_DB ) . '
			where base_url like "%,%" or monetized_url like "%,%"
				or (base_url like "%amazon.%" and monetized_url like "%amzn.%")
		';

		return Model::get_results( $sql );
	}

	/**
	 * Get amazon product that has empty features and they are imported from AAWP
	 */
	public function get_amazon_empty_features() {
		$is_aawp_installed = $this->is_aawp_installed();
		$features_column   = Model::column_exists( Model::get_wp_table_name( 'aawp_products' ), array( 'features' ) );

		if ( ! $is_aawp_installed || ! $features_column ) {
			return null;
		}

		$sql = '
			SELECT DISTINCT aawp.asin, aawp.features as aawp_features, la.features as lasso_features
			FROM ' . Model::get_wp_table_name( 'aawp_products' ) . ' AS aawp
			LEFT JOIN ' . Model::get_wp_table_name( LASSO_REVERT_DB ) . ' AS lr
			ON CONVERT(lr.old_uri USING utf8) = CONVERT(aawp.asin USING utf8)
			LEFT JOIN ' . Model::get_wp_table_name( LASSO_AMAZON_PRODUCTS_DB ) . " AS la
			ON CONVERT(la.amazon_id USING utf8) = CONVERT(aawp.asin USING utf8)
			WHERE aawp.features != '' AND lr.plugin = 'aawp' AND la.features = '[]'
			LIMIT 100
		";

		return Model::get_results( $sql );
	}

	/**
	 * Get amazon product that has incorrect format of features in the amazon table
	 */
	public function get_amazon_incorrect_format_features() {
		$sql = '
			SELECT *
			FROM ' . Model::get_wp_table_name( LASSO_AMAZON_PRODUCTS_DB ) . " AS la
			WHERE la.features LIKE 'a:%i:0%'
			LIMIT 100
		";

		return Model::get_results( $sql );
	}

	/**
	 * Get amazon products that are not linked to any Lasso post
	 *
	 * @param string $amazon_product_id Amazon product id. Default to empty.
	 */
	public function get_unlinked_amazon_product_lasso_url( $amazon_product_id = '' ) {
		$search = '1=1';
		if ( ! empty( $amazon_product_id ) ) {
			$search = "pm.meta_value = '" . $amazon_product_id . "'";
		}

		$sql = '
			SELECT
				DISTINCT
				pm.post_id,
				product_id,
				detection_id,
				link_location_id,
				link_slug
			FROM
				(
					SELECT
						post_id,
						meta_value
					FROM
						' . Model::get_wp_table_name( 'postmeta' ) . "
					WHERE
						meta_key = 'amazon_product_id'
				) as pm
				INNER JOIN
				(
					SELECT
						post_id,
						product_id,
						detection_id,
						id as link_location_id,
						link_slug
					FROM
						" . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . "
					WHERE
						product_id != ''
						AND post_id = 0
				) as ll
				ON CONVERT(pm.meta_value USING utf8mb4) = CONVERT(ll.product_id USING utf8mb4)
			WHERE
				" . $search . '
			GROUP BY
				post_id,
				product_id;
		';

		return Model::get_results( $sql );
	}

	/**
	 * Update amazon product
	 */
	public function update_amazon_product_lasso_url() {
		$sql = '
			UPDATE ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . ' ull
				JOIN
					(
						SELECT
							DISTINCT pm.post_id,
							product_id,
							target_link
						FROM
							(
								SELECT lud.lasso_id as post_id, lud.product_id as meta_value, lud.redirect_url as target_link
								FROM ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . " as lud
								WHERE lud.product_id <> '' AND lud.product_id IS NOT NULL AND lud.product_type = '" . Lasso_Amazon_Api::PRODUCT_TYPE . "'
							) pm
						INNER JOIN
							(
								SELECT
									post_id,
									product_id
								FROM
									" . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . "
								WHERE
									product_id != '' AND post_id = 0
							) ll
						ON pm.meta_value = ll.product_id
					) as pmll
				ON ull.product_id = pmll.product_id
			SET ull.post_id = pmll.post_id,
				ull.original_link_slug = pmll.target_link,
				ull.link_slug = pmll.target_link
		";

		return Model::query( $sql );
	}

	/**
	 * Get amazon links in link location table, that don't have id
	 */
	public function get_amazon_urls_in_ll_without_ids() {
		$amazon_in_condition = $this->get_amazon_domains_in_clause();

		$sql = '
			SELECT id, link_slug, tracking_id, product_id
			FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . '
			WHERE
				link_slug_domain IN (' . $amazon_in_condition . ")
				AND (tracking_id = '' OR tracking_id IS NULL OR product_id = '' OR product_id IS NULL);
		";

		return Model::get_results( $sql );
	}

	/**
	 * Update link location with amazon id and tracking id
	 *
	 * @param int    $link_id     Id in link location table.
	 * @param string $tracking_id Amazon tracking id.
	 * @param string $product_id  Amazon product id.
	 */
	public function update_ll_with_amazon_ids( $link_id, $tracking_id, $product_id ) {
		$update_sql = '
			UPDATE ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . "
			SET tracking_id = '" . $tracking_id . "', product_id = '" . $product_id . "'
			WHERE id = " . $link_id . ';
		';

		return Model::query( $update_sql );
	}

	/**
	 * Get amazon links that don't have product in DB
	 *
	 * @param int    $limit Limit results. Defaul to 0.
	 * @param string $order_by Order by. Default to asc.
	 */
	public function get_non_lasso_amazon_product( $limit = 0, $order_by = 'asc' ) {
		$acf_query              = '';
		$selected_custom_fields = Lasso_Setting::lasso_get_setting( 'custom_fields_support', array() );
		if ( class_exists( 'ACF' ) && count( $selected_custom_fields ) > 0 ) {
			$like_query = array_map(
				function( $v ) {
					return 'meta_key LIKE "%' . $v . '%"';
				},
				$selected_custom_fields
			);
			$like_query = implode( ' OR ', $like_query );

			// ? exclude amazon url that are added to Lasso
			$all_lasso_amazon_query = '
				SELECT product_id
				FROM ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . ' AS lud
				LEFT JOIN ' . Model::get_wp_table_name( 'posts' ) . ' AS wpp
					ON wpp.ID = lud.lasso_id
				WHERE wpp.ID != "" 
					AND wpp.ID IS NOT NULL 
					AND product_id != ""
			';
			$all_lasso_amazon_ids   = Model::get_col( $all_lasso_amazon_query );
			if ( count( $all_lasso_amazon_ids ) > 0 ) {
				$where_query        = array_map(
					function( $v ) {
						return 'meta_value NOT LIKE "%' . $v . '%"';
					},
					$all_lasso_amazon_ids
				);
				$where_query        = implode( ' AND ', $where_query );
				$not_in_lasso_where = 'AND ( ' . $where_query . ' )';
			} else {
				$not_in_lasso_where = '';
			}

			$acf_query = '
				UNION

				SELECT "" AS product_id, "" AS link_slug_domain, "" AS tracking_id, meta_value AS link_slug
				FROM ' . Model::get_wp_table_name( 'postmeta' ) . ' AS wppm
				WHERE ( ' . $like_query . ' ) 
					AND ( 
						meta_value LIKE "%amazon.%" 
						AND ( meta_value LIKE "%/dp/%" OR meta_value LIKE "%/gp/product/%" OR meta_value LIKE "%/ASIN/%" OR meta_value LIKE "%/gp/video/detail/%" ) 
					)
					OR (
						meta_value LIKE "[amazon%box=%"
						OR meta_value LIKE "[amazon%link=%"
						OR meta_value LIKE "[amazon%fields=%"
						OR meta_value LIKE "[aawp%box=%"
						OR meta_value LIKE "[aawp%link=%"
						OR meta_value LIKE "[aawp%fields=%"
					)
					' . $not_in_lasso_where . '
			';
		}

		$amazon_non_product_sql = '
			UNION

			SELECT lll.product_id, link_slug_domain, tracking_id, lll.link_slug
			FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . ' as lll
			WHERE 
				( lll.product_id = "" OR lll.product_id IS NULL )
				AND lll.post_id = 0
				AND ( lll.link_slug_domain LIKE %s OR lll.link_slug_domain IN (%s, %s) )
				AND lll.detection_id IN (
					SELECT ID
					FROM ' . Model::get_wp_table_name( 'posts' ) . '
					WHERE post_status = %s
				)
				AND lll.link_slug NOT IN (
					SELECT shortened_url
					FROM ' . ( new Amazon_Shortened_Url() )->get_table_name() . '
				)
			GROUP BY lll.product_id, link_slug_domain, tracking_id, lll.link_slug
		';
		$amazon_non_product_sql = Model::prepare( $amazon_non_product_sql, 'amazon.%', 'amzn.com', 'amzn.to', 'publish' );

		$sql = '
			SELECT lll.product_id, link_slug_domain, tracking_id, lll.link_slug
			FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . ' AS lll

			LEFT JOIN ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . " AS lud 
				ON lll.product_id = lud.product_id AND lud.product_type = '" . Lasso_Amazon_Api::PRODUCT_TYPE . "' 
			LEFT JOIN " . Model::get_wp_table_name( 'posts' ) . ' AS wpp
				ON wpp.ID = lud.lasso_id 
			WHERE
				lll.product_id <> ""
				AND lll.product_id IS NOT null
				AND lll.post_id = 0
				AND wpp.ID is null
				AND lll.product_id NOT IN (
					SELECT product_id
					FROM ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . '
					WHERE product_type = "amazon"
						AND product_id <> ""
						AND product_id IS NOT null
				)
				AND lll.product_id NOT LIKE "%,%"
			GROUP BY lll.product_id, link_slug_domain, tracking_id, lll.link_slug

			' . $acf_query . '
			' . $amazon_non_product_sql . '

			ORDER BY product_id ' . $order_by . '
		';

		if ( $limit && intval( $limit ) > 0 ) {
			$sql .= '
				LIMIT ' . $limit . '
			';
		}

		return Model::get_results( $sql );
	}

	/**
	 * Delete keyword location
	 *
	 * @param int $id Id in keyword_location table.
	 */
	public function delete_keyword_location( $id ) {
		$sql = '
			DELETE FROM ' . Model::get_wp_table_name( LASSO_KEYWORD_LOCATIONS ) . '
			WHERE id = ' . $id . ';
		';

		return Model::query( $sql );
	}

	/**
	 * Get all post ids that are in the link_locations table
	 */
	public function get_all_post_ids_in_link_locations() {
		$sql = '
			SELECT DISTINCT `detection_id` 
			FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . ' AS lll
			INNER JOIN ' . Model::get_wp_table_name( 'posts' ) . ' AS p
				ON lll.detection_id = p.ID
			WHERE 1
		';

		return Model::get_col( $sql );
	}

	/**
	 * Get all post ids contain link (<a> tags)
	 *
	 * @param bool  $diff        Get post in link location or not.
	 * @param array $post_status Filter post status. Default to "publish".
	 * @param int   $page        Page. Default to 1.
	 * @param int   $limit       Limit. Default to 10.
	 */
	public function get_all_post_ids_link_db( $diff, $post_status = array( 'publish' ), $page = 0, $limit = 10 ) {
		$diff_sql = '';
		if ( $diff ) {
			$diff_sql = '
				AND ID NOT IN (
					SELECT DISTINCT detection_id 
					FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . '
				)
			';
		}

		$shortcode_list = Lasso_Cron::SHORTCODE_LIST;
		$shortcodes     = array_map(
			function( $v ) {
				return '[' . $v;
			},
			$shortcode_list
		);
		// ? scan post content that contains shortcodes of other plugins
		$shortcodes_allow = 'post_content LIKE \'%' . implode( "%' OR post_content LIKE '%", $shortcodes ) . '%\'';

		if ( Lasso_Helper::is_existing_thrive_plugin() ) {
			$shortcodes_allow_meta = 'meta_value LIKE \'%' . implode( "%' OR meta_value LIKE '%", $shortcodes ) . '%\'';

			$meta_sql = '
				UNION

				SELECT post_id AS ID
				FROM ' . Model::get_wp_table_name( 'postmeta' ) . '
				WHERE (
					meta_key = "page" 
					OR meta_key = "tve_updated_post"
				) AND (
					meta_value LIKE "%<a %" 
					OR meta_value LIKE "%' . Lasso_Cron::SITE_STRIPE_DOMAIN . '%"
					OR meta_value LIKE "%' . Lasso_Cron::SITE_STRIPE_EU_DOMAIN . '%"
					OR meta_value LIKE "%wp:aawp/aawp-block%" OR ' . $shortcodes_allow_meta . '
				)
			';
		} else {
			$meta_sql = '
				UNION

				SELECT post_id AS ID
				FROM ' . Model::get_wp_table_name( 'postmeta' ) . '
				WHERE meta_key = "page" AND (meta_value LIKE "%wp:aawp/aawp-block%")
			';
		}

		$post_types_are_allow = Lasso_Helper::get_cpt_support();
		$post_types_are_allow = '\'' . implode( '\', \'', $post_types_are_allow ) . '\'';
		$post_status          = '\'' . implode( '\', \'', $post_status ) . '\'';
		$sql                  = '
			SELECT 
				DISTINCT ID
			FROM 
				' . Model::get_wp_table_name( 'posts' ) . '
			WHERE
				post_type IN (' . $post_types_are_allow . ')
				AND post_type <> \'revision\'
				AND post_status IN (' . $post_status . ")
				AND (
					post_content LIKE '%<a %' 
					OR post_content LIKE '%" . Lasso_Cron::SITE_STRIPE_DOMAIN . "%' 
					OR post_content LIKE '%" . Lasso_Cron::SITE_STRIPE_EU_DOMAIN . "%' 
					OR post_content LIKE '%wp:aawp/aawp-block%' OR " . $shortcodes_allow . '
				)
				' . $diff_sql . '

			' . $meta_sql . '
		';

		if ( $page > 0 ) {
			$offset = ( $page - 1 ) * $limit;
			$offset = floor( $offset );
			$sql   .= '
				LIMIT ' . $limit . ' OFFSET ' . $offset . '
			';
		}

		return Model::get_col( $sql );
	}

	/**
	 * Get posts contain a lasso post id
	 *
	 * @param int $lasso_id Lasso post id.
	 */
	public function get_post_contains_lasso_id( $lasso_id ) {

		if ( empty( $lasso_id ) ) {
			return array();
		}

		$query = "
			SELECT
				GROUP_CONCAT(id SEPARATOR ',') as link_location_ids, detection_id, post_id as lasso_id
			FROM 
				" . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . "
			WHERE 
				post_id = '" . $lasso_id . "'
			GROUP BY 
				detection_id
		";

		return Model::get_results( $query );
	}

	/**
	 * Check whether an amazon product existed in DB or not
	 *
	 * @param string $product_id Amazon product id.
	 */
	public function check_amazon_product_exist( $product_id ) {
		global $wpdb;

		$query = '
			SELECT `amazon_id`
			FROM ' . Model::get_wp_table_name( LASSO_AMAZON_PRODUCTS_DB ) . '
			WHERE `amazon_id` = %s
		';

		$prepare = $wpdb->prepare( $query, $product_id ); // phpcs:ignore

		return Model::get_row( $prepare, ARRAY_A );
	}

	/**
	 * Get lasso post id by product id and product_type
	 *
	 * @param string $product_id   Product id.
	 * @param string $product_type Product type. Default is amazon.
	 * @param string $amazon_url   Product url. Default is empty.
	 */
	public function get_lasso_id_by_product_id_and_type( $product_id, $product_type = Lasso_Amazon_Api::PRODUCT_TYPE, $amazon_url = '' ) {
		global $wpdb;

		if ( ! $product_id ) {
			return false;
		}

		if ( $amazon_url && Lasso_Amazon_Api::PRODUCT_TYPE === $product_type ) {
			$product_id = Lasso_Amazon_Api::get_product_id_country_by_url( $amazon_url );
		}

		$product_id_without_country = explode( '_', $product_id )[0];

		$sql = '
			SELECT DISTINCT lud.lasso_id as post_id
			FROM ' . Model::get_wp_table_name( 'posts' ) . ' as wpp
				LEFT JOIN ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . ' as lud
				ON wpp.id = lud.lasso_id
			WHERE wpp.post_type = %s 
				AND ( lud.product_id = %s OR lud.product_id = %s )
				AND lud.product_type = %s 
				AND wpp.post_status = "publish"
		';

		$prepare = $wpdb->prepare( $sql, LASSO_POST_TYPE, $product_id, $product_id_without_country, $product_type ); // phpcs:ignore
		$post    = Model::get_row( $prepare );

		if ( $post && get_post( $post->post_id ) ) {
			return $post->post_id;
		}

		return false;
	}

	/**
	 * Get lasso post id by geni url
	 *
	 * @param string $geni_url Amazon product id.
	 */
	public function get_lasso_id_by_geni_url( $geni_url ) {
		if ( ! $geni_url ) {
			return false;
		}

		$sql     = '
			SELECT DISTINCT lud.lasso_id as post_id
			FROM ' . Model::get_wp_table_name( 'posts' ) . ' as wpp
			LEFT JOIN ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . ' as lud
			ON wpp.id = lud.lasso_id
			WHERE wpp.post_type = %s and lud.redirect_url = %s and wpp.post_status = "publish" 
		';
		$prepare = Model::prepare( $sql, LASSO_POST_TYPE, $geni_url ); // phpcs:ignore
		$post    = Model::get_row( $prepare );

		if ( $post && get_post( $post->post_id ) ) {
			return $post->post_id;
		}

		return false;
	}

	/**
	 * Clean link locations
	 * The post is not existing anymore
	 */
	public function clean_link_locations() {
		$post_types_are_allow = Lasso_Helper::get_cpt_support();
		$post_types_are_allow = '\'' . implode( '\', \'', $post_types_are_allow ) . '\'';

		$sql = '
			DELETE FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . '
			WHERE 
				detection_id not in (
					SELECT ID 
					FROM ' . Model::get_wp_table_name( 'posts' ) . '
					WHERE post_type IN (' . $post_types_are_allow . ')
				)
		';

		return Model::query( $sql );
	}

	/**
	 * Get amazon domains in clause (not shortened URLs)
	 *
	 * @param bool $return_array Whether return array or string in SQL condition. Default to false.
	 */
	public function get_amazon_domains_in_clause( $return_array = false ) {
		$amazon_domains = Lasso_Amazon_Api::get_domains();
		$amazon_domains = array_filter(
			$amazon_domains,
			function( $v ) {
				return ! ( 'amzn.com' === $v || 'amzn.to' === $v );
			}
		);

		if ( ! $return_array ) {
			$amazon_domains = "'" . implode( "', '", $amazon_domains ) . "'";
		}

		return $amazon_domains;
	}

	/**
	 * Check whether the process is empty
	 */
	public function check_empty_process() {
		$count_query = '
			SELECT count(option_id) as `total`
			FROM ' . Model::get_wp_table_name( 'options' ) . "
			WHERE option_name like '%lasso_%_batch_%' and option_value like 'a:1:{i:0;i:%'
		";
		$total       = Model::get_row( $count_query );
		$total       = $total->total ?? 0;
		$total       = intval( $total );

		// ? delete empty processes if there are more 10 empty processes
		if ( $total > 10 ) {
			$this->remove_empty_process();
		}
	}

	/**
	 * Remove: empty process
	 */
	public function remove_empty_process() {
		$sql = '
			DELETE FROM ' . Model::get_wp_table_name( 'options' ) . "
			WHERE option_name like '%lasso_%_batch_%' and option_value like 'a:1:{i:0;i:%'
		";
		Model::query( $sql );
	}

	/**
	 * Remove: remove attribute process
	 */
	public function remove_non_remove_attribute_process() {
		$sql = '
			DELETE FROM ' . Model::get_wp_table_name( 'options' ) . "
			WHERE option_name like '%lasso_%_batch_%' and option_name not like '%lasso_remove_attributes_process%'
		";
		Model::query( $sql );
	}

	/**
	 * Remove: all background processes of Lasso
	 */
	public function remove_all_lasso_processes() {
		$sql = '
			DELETE FROM ' . Model::get_wp_table_name( 'options' ) . "
			WHERE option_name like '%lasso_%_batch_%'
		";
		Model::query( $sql );
	}

	/**
	 * Get EasyAzon product
	 *
	 * @param string $asin Amazon product id.
	 */
	public function get_easyazon_product( $asin ) {
		if ( ! $asin ) {
			return null;
		}

		$search  = 'easyazon_item_' . $asin . '%';
		$sql     = '
			select option_value
			from ' . Model::get_wp_table_name( 'options' ) . '
			where option_name like %s
			order by option_id desc
			limit 1
		';
		$prepare = Model::prepare( $sql, $search ); // phpcs:ignore

		return Model::get_row( $prepare );
	}

	/**
	 * Check EasyAzon product id is imported or not
	 *
	 * @param string $asin Amazon product id.
	 */
	public function is_easyazon_product_imported( $asin ) {
		$sql     = '
			select id, lasso_id
			from ' . Model::get_wp_table_name( LASSO_REVERT_DB ) . '
			where old_uri = %s and plugin = \'easyazon\'
		';
		$prepare = Model::prepare( $sql, $asin ); // phpcs:ignore

		return Model::get_row( $prepare );
	}

	/**
	 * Bulk inserts records into a table using WPDB.  All rows must contain the same keys.
	 * Returns number of affected (inserted) rows.
	 *
	 * @param string $table Table name.
	 * @param array  $rows Table data.
	 */
	public function wpdb_bulk_insert( $table, $rows ) {
		$ids = array();
		if ( ! is_array( $rows ) || 0 === count( $rows ) ) {
			return array( false, $ids );
		}

		// ? Extract column list from first row of data
		$columns = array_keys( Lasso_Helper::convert_stdclass_to_array( $rows[0] ) );
		asort( $columns );
		$column_list = '`' . implode( '`, `', $columns ) . '`';

		// ? Start building SQL, initialise data and placeholder arrays
		// ? $sql = "INSERT INTO `$table` ($column_list) VALUES\n";
		$sql          = "REPLACE INTO `$table` ($column_list) VALUES\n"; // ? upsert in mysql
		$placeholders = array();
		$data         = array();

		// ? Build placeholders for each row, and add values to data array
		foreach ( $rows as $row ) {
			$row = Lasso_Helper::convert_stdclass_to_array( $row );
			ksort( $row );
			$row_placeholders = array();
			$ids[]            = intval( $row['id'] );

			foreach ( $row as $value ) {
				$data[]             = $value;
				$row_placeholders[] = is_numeric( $value ) ? '%d' : '%s';
			}

			$placeholders[] = '(' . implode( ', ', $row_placeholders ) . ')';
		}

		// ? Stitch all rows together
		$sql    .= implode( ",\n", $placeholders );
		$prepare = Model::prepare( $sql, $data ); // phpcs:ignore

		// Run the query.  Returns number of affected rows.
		return array( Model::query( $prepare ), $ids ); // phpcs:ignore
	}

	/**
	 * Delete the data are not in the affiliate_programs table (DWH)
	 *
	 * @param array $ids List of id. Default to empty array.
	 */
	public function delete_old_data_affiliate_programs( $ids = array() ) {
		if ( ! is_array( $ids ) || empty( $ids ) ) {
			return false;
		}

		$affiliate_programs_table = Model::get_wp_table_name( LASSO_AFFILIATE_PROGRAMS );
		$query                    = '
			delete from ' . $affiliate_programs_table . '
			where id not in (' . implode( ', ', $ids ) . ')
		';

		return Model::query( $query ); // phpcs:ignore
	}

	/**
	 * Remove a field from a specific product
	 *
	 * @param int $field_id ID of the field selected.
	 * @param int $post_id ID of the product selected.
	 */
	public function remove_field_from_page( $field_id, $post_id ) {
		// ? Insert into field mapping table
		$insert_sql = '
			DELETE FROM ' . Model::get_wp_table_name( LASSO_FIELD_MAPPING ) . ' 
			WHERE field_id = %d 
				AND lasso_id = %d
		';
		$prepare    = Model::prepare( $insert_sql, $field_id, $post_id ); // phpcs:ignore
		$result     = Model::query( $prepare );

		return $result;
	}

	/**
	 * Create a new field
	 *
	 * @param string $title Name of the field.
	 * @param string $type Type of field.
	 * @param string $description Description of field.
	 */
	public function create_new_field( $title, $type, $description ) {
		global $wpdb;

		$data   = array(
			'field_name'        => $title,
			'field_type'        => $type,
			'field_description' => $description,
		);
		$result = $wpdb->insert( Model::get_wp_table_name( LASSO_FIELDS ), $data ); // phpcs:ignore

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Update a field
	 *
	 * @param int    $id ID of the field.
	 * @param string $title Name of the field.
	 * @param string $type Type of field.
	 * @param string $description Description of field.
	 */
	public function update_field( $id, $title, $type, $description ) {
		global $wpdb;

		// ? Update in fields table
		$insert_sql = '
			UPDATE ' . Model::get_wp_table_name( LASSO_FIELDS ) . ' 
			SET field_name=%s, field_type=%s, field_description=%s
			WHERE id=%d;
		';
		$insert_sql = $wpdb->prepare( $insert_sql, $title, $type, $description, $id ); // phpcs:ignore
		$result     = Model::query( $insert_sql );

		return $result;
	}

	/**
	 * Delete a field
	 *
	 * @param int $id ID of the field.
	 */
	public function delete_field( $id ) {
		// ? Delete from fields table
		$sql     = '
			DELETE FROM ' . Model::get_wp_table_name( LASSO_FIELDS ) . ' 
			WHERE id = %d
		';
		$prepare = Model::prepare( $sql, $id );
		$result  = Model::query( $prepare );

		return $result;
	}

	/**
	 * Get all fields by lasso post id
	 *
	 * @param int     $lasso_id     Lasso post id.
	 * @param boolean $is_use_cache Is use cache.
	 */
	public function get_fields_by_lasso_id( $lasso_id, $is_use_cache = false ) {
		$sql = '
			SELECT
				*
			FROM 
					' . Model::get_wp_table_name( LASSO_FIELD_MAPPING ) . ' as fm
				INNER JOIN
					' . Model::get_wp_table_name( LASSO_FIELDS ) . " as ff
						ON fm.field_id = ff.id
			WHERE 
				fm.lasso_id = '" . $lasso_id . "'
				AND fm.field_visible = 1
			ORDER BY
				fm.field_order ASC
		";

		// ? Enable cache on frontend to prevent duplicate queries issue
		if ( ! is_admin() ) {
			$is_use_cache = true;
		}

		return Model::get_results( $sql, 'OBJECT', $is_use_cache );
	}

	/**
	 * Get total 404 request in the url_issues table
	 *
	 * @param array $url URL.
	 */
	public function get_total_404_request_to_bls( $url ) {
		$query = '
			select total_404_request
			from ' . Model::get_wp_table_name( LASSO_URL_ISSUE_DB ) . '
			where issue_slug = %s and issue_type = 404 and issue_resolved = 0
		';

		$query = Model::prepare( $query, $url ); // phpcs:ignore

		return Model::get_row( $query )->total_404_request ?? 0; // phpcs:ignore
	}

	/**
	 * Set total 404 request in the url_issues table
	 *
	 * @param array $url URL.
	 * @param array $total_404_request Total request.
	 */
	public function set_total_404_request_to_bls( $url, $total_404_request ) {
		if ( '' === $url ) {
			return 0;
		}

		$query = '
			update ' . Model::get_wp_table_name( LASSO_URL_ISSUE_DB ) . '
			set total_404_request = ' . $total_404_request . '
			where issue_slug = %s and issue_resolved = 0
		';

		$query = Model::prepare( $query, $url ); // phpcs:ignore

		return Model::query( $query ); // phpcs:ignore
	}

	/**
	 * Update a field for an Amazon product
	 *
	 * @param string $amazon_id Amazon product id.
	 * @param string $field_name  Field name.
	 * @param string $field_value Field value.
	 */
	public function update_amazon_field( $amazon_id, $field_name, $field_value ) {
		$sql     = '
			update ' . Model::get_wp_table_name( LASSO_AMAZON_PRODUCTS_DB ) . '
			set `' . $field_name . '` = %s
			where amazon_id = %s		
		';
		$prepare = Model::prepare( $sql, $field_value, $amazon_id ); // phpcs:ignore

		return Model::query( $prepare ); // phpcs:ignore
	}

	/**
	 * Update Lasso Link location post_id which is zero to a new imported id.
	 *
	 * @param int    $post_id new import lasso_link id.
	 * @param string $old_uri Amazon product_id.
	 */
	public function update_lasso_link_locations_when_importing( $post_id, $old_uri ) {
		$sql = '
			update ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . " 
			set post_id = '" . $post_id . "'
			where product_id = '" . $old_uri . "'";

		Model::query( $sql );
	}

	/**
	 * Get post id that contains shortcode
	 *
	 * @param int    $post_id Post id.
	 * @param string $type Object type.
	 */
	public function get_post_to_replace_shortcode( $post_id, $type = '' ) {
		if ( 'aawp_table' === $type ) {
			$sql = '
				SELECT DISTINCT detection_id
				FROM ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . "
				WHERE display_type = 'AAWP'
					AND (
						original_link_slug LIKE '[amazon %table=%$post_id%]'
						OR original_link_slug LIKE '[aawp %table=%$post_id%]'
					)
				";
		} else {
			$sql = '
				select distinct detection_id
				from ' . Model::get_wp_table_name( LASSO_LINK_LOCATION_DB ) . '
				where post_id = "' . $post_id . '"
			';
		}

		return Model::get_col( $sql );
	}

	/**
	 * Get post content history list
	 *
	 * @param string $search Search text.
	 * @param string $where  Where statement. Default to '1=1'.
	 */
	public function get_post_content_history_query( $search, $where = '1=1' ) {
		$sql = '
			SELECT
				h.id as history_id,
				h.object_id as post_id,
				h.old_value,
				h.new_value,
				h.updated_date,
				p.post_title,
				p.post_type
			FROM
					' . Model::get_wp_table_name( LASSO_POST_CONTENT_HISTORY ) . ' as h
				INNER JOIN
					' . Model::get_wp_table_name( 'posts' ) . ' as p
						ON h.object_id = p.ID
			WHERE
				' . $where . '
				' . $search . '
		';

		return $sql;
	}

	/**
	 * Get post content history by id
	 *
	 * @param string $history_id Post content history id.
	 */
	public function get_post_content_history_detail( $history_id ) {
		$sql = '
			SELECT
				h.id as history_id,
				h.object_id as post_id,
				h.old_value,
				h.new_value,
				h.updated_date,
				p.post_title,
				p.post_type
			FROM
					' . Model::get_wp_table_name( LASSO_POST_CONTENT_HISTORY ) . ' as h
				INNER JOIN
					' . Model::get_wp_table_name( 'posts' ) . ' as p
						ON h.object_id = p.ID
			WHERE h.id = %d';

		$prepare = Model::prepare( $sql, $history_id ); // phpcs:ignore

		return Model::get_row( $prepare );
	}

	/**
	 * Delete post content history over expired days
	 *
	 * @param int $expired_days Expired days. Default to 30.
	 */
	public function delete_expired_post_data_history( $expired_days = 30 ) {
		$sql     = '
			DELETE FROM ' . Model::get_wp_table_name( LASSO_POST_CONTENT_HISTORY ) . ' 
			WHERE updated_date < NOW() - INTERVAL %d DAY
		';
		$prepare = Model::prepare( $sql, $expired_days );

		return Model::query( $prepare );
	}

	/**
	 * Get post ids that have no url detail
	 */
	public function get_empty_details_links() {
		$sql = '
			SELECT wpp.ID, lud.lasso_id
			FROM ' . Model::get_wp_table_name( 'posts' ) . ' as wpp
			LEFT JOIN ' . Model::get_wp_table_name( LASSO_URL_DETAILS ) . " as lud
			ON lud.lasso_id = wpp.ID
			WHERE wpp.post_type = '" . LASSO_POST_TYPE . "' AND lud.lasso_id is null
			LIMIT 100
		";

		return Model::get_results( $sql );
	}

	/**
	 * Get easyazon option by option_name
	 *
	 * @param string $option_name A part of option name.
	 * @return mixed
	 */
	public static function get_easyazon_option( $option_name ) {
		$sql = '
			SELECT option_value
			FROM ' . Model::get_wp_table_name( 'options' ) . '
			WHERE option_name LIKE %s
		';
		$sql = Model::prepare( $sql, $option_name . '%' );

		$result = Model::get_var( $sql );
		$result = maybe_unserialize( $result );

		return $result;
	}

	/**
	 * Get import plugin options to filter
	 *
	 * @param bool $get_count Get result count.
	 * @return array|int|mixed
	 */
	public function get_import_plugins( $get_count = false ) {
		$sql = $this->get_importable_urls_query( true, '', 'import_source' );

		if ( $get_count ) {
			return Model::get_count( $sql );
		}

		$import_results = Model::get_results( $sql );

		$result = array();

		foreach ( $import_results as $import_result ) {
			$result[] = $import_result->import_source;
		}

		return $result;
	}

	/**
	 * Query Lasso Post ID that haven't webp image.
	 *
	 * @return string
	 */
	public function get_lasso_ids_non_webp_query() {
		$sql = '
			SELECT p.ID as id
			FROM ' . Model::get_wp_table_name( 'posts' ) . ' AS p
			WHERE p.post_type = %s
				AND p.ID NOT IN (
					SELECT post_id
					FROM ' . Model::get_wp_table_name( 'postmeta' ) . '
					WHERE meta_key = %s
					GROUP BY post_id
				)
				AND p.ID NOT IN (
					SELECT post_id
					FROM ' . Model::get_wp_table_name( 'postmeta' ) . '
					WHERE meta_key = %s
						AND meta_value IN("", NULL)
					GROUP BY post_id
				)
			ORDER BY p.ID DESC
		';

		$sql = Model::prepare( $sql, LASSO_POST_TYPE, 'lasso_webp_thumbnail', 'lasso_custom_thumbnail' );

		return $sql;
	}

	/**
	 * Query Lasso table field group detail ids that did not a webp image.
	 *
	 * @return string
	 */
	public function get_table_image_field_group_detail_id_non_webp_query() {
		$sql = '
			SELECT id
			FROM ' . Model::get_wp_table_name( 'lasso_table_field_group_detail' ) . "
			WHERE field_id = %d
				AND field_value NOT LIKE '%.webp%'
				AND id NOT IN (
					SELECT object_id FROM " . Model::get_wp_table_name( 'lasso_metadata' ) . " 
						WHERE meta_value LIKE '%.webp%'
						AND `type` = %s 
						AND meta_key = %s
				)
		";
		$sql = Model::prepare( $sql, Lasso_Fields::IMAGE_FIELD_ID, 'Table_Field_Group_Detail', Enum::LASSO_WEBP_THUMBNAIL );

		return $sql;
	}

	/**
	 * Get Lasso Lite amazon product by id
	 *
	 * @param string $amazon_id Amazon product id.
	 * @return array|object|void|null
	 */
	public function get_lasso_lite_product( $amazon_id ) {
		$lite_amazon_product_table = Model::get_wp_table_name( 'lasso_lite_amazon_products' );

		if ( Model::table_exists( $lite_amazon_product_table ) ) {
			$sql = '
				SELECT *
				FROM ' . $lite_amazon_product_table . '
				WHERE amazon_id = %s
			';

			$sql     = Model::prepare( $sql, $amazon_id );
			$product = Model::get_row( $sql );

			return $product;
		}

		return null;
	}

	/**
	 * Get ACF postmetas by post ID and acf keys.
	 *
	 * @param int   $post_id  Post ID.
	 * @param array $acf_keys Array of ACF field keys.
	 * @return array|object|null
	 */
	public static function get_acf_postmetas( $post_id, $acf_keys ) {
		$meta_key_condition = '';
		$acf_keys           = array_unique( $acf_keys );

		// ? Prepare LIKE query. Ex: meta_key LIKE '%field_name1' OR meta_key LIKE '%field_name2'
		foreach ( $acf_keys as $index => $acf_key ) {
			$acf_key      = Model::get_wpdb()->esc_like( $acf_key );
			$or_condition = 0 === $index ? '' : ' OR ';

			$meta_key_condition .= "$or_condition meta_key LIKE %s";
			$meta_key_condition  = Model::prepare( $meta_key_condition, "%$acf_key" ); // phpcs:ignore
		}

		$sql = '
			SELECT meta_id, meta_key, meta_value
			FROM ' . Model::get_wp_table_name( 'postmeta' ) . '
			WHERE post_id = %d
			AND (' . $meta_key_condition . ')';
		$sql = Model::prepare( $sql, $post_id );

		return Model::get_results( $sql );
	}

	/**
	 * Get all Lasso post ids
	 */
	public static function get_lasso_post_ids() {
		$query   = '
			SELECT ID
			FROM ' . Model::get_wp_table_name( 'posts' ) . '
			WHERE post_type = %s
		';
		$prepare = Model::prepare( $query, LASSO_POST_TYPE );

		return Model::get_col( $prepare );
	}

	/**
	 * Get post_id by URL from the postmeta table
	 *
	 * @param string $url URL.
	 */
	public static function get_post_id_by_original_url( $url ) {
		$sql = '
			SELECT post_id
			FROM ' . Model::get_wp_table_name( 'postmeta' ) . '
			WHERE (meta_key = %s OR meta_key = %s)
				AND meta_value = %s
			LIMIT 1
		';
		$sql = Model::prepare( $sql, 'lasso_custom_redirect', 'lasso_final_url', $url );

		return Model::get_var( $sql );
	}

	/**
	 * Get post_id by slug from the post table
	 *
	 * @param string $slug Slug.
	 */
	public static function get_post_id_by_slug( $slug ) {
		$sql = '
			SELECT ID
			FROM ' . Model::get_wp_table_name( 'posts' ) . '
			WHERE 
				post_type = %s
				AND post_name = %s
			LIMIT 1
		';
		$sql = Model::prepare( $sql, LASSO_POST_TYPE, $slug );

		return Model::get_var( $sql );
	}

	/**
	 * Get wp post id by slug.
	 *
	 * @param string $slug         Slug.
	 * @param bool   $enable_cache Enable cache. Default is true.
	 * @return false|mixed
	 */
	public static function get_wp_post_id_by_slug( $slug, $enable_cache = true ) {
		$sql = '
			SELECT ID
			FROM ' . Model::get_wp_table_name( 'posts' ) . "
			WHERE
				post_type IN ('post', 'page')
				AND post_status IN ('publish', 'draft')
				AND post_name = %s
			LIMIT 1
		";
		$sql = Model::prepare( $sql, $slug );

		return Model::get_var( $sql, $enable_cache );
	}
}
