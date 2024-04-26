<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Content_Tips\Events;
use ahrefs\AhrefsSeo\Features\Duplicated_Keywords;
use ahrefs\AhrefsSeo\Options\Settings_Scope;
use ahrefs\AhrefsSeo\Third_Party\Sources;
use ahrefs\AhrefsSeo\Workers\Worker_Backlinks;
use stdClass;
use WP_Post;
use WP_Term;

/**
 * Base class for content, implement options get and save.
 *
 * Call update_processed_items() when content options changed for re-analyze existing items.
 */
class Ahrefs_Seo_Data_Content extends Ahrefs_Seo_Content {
	/*
	Statuses are converted to actions at the function status_to_action_clause().
	Actions are converted to statuses at the function data_get_count_by_status().
	Please update both if their mapping changed.
	*/
	const STATUS_ERROR = 'error';

	// same as tabs at Content audit table.
	const STATUS4_ALL_ANALYZED     = '';
	const STATUS4_WELL_PERFORMING  = 'well-performing';
	const STATUS4_UNDER_PERFORMING = 'under-performing';
	const STATUS4_NON_PERFORMING   = 'non-performing';
	const STATUS4_EXCLUDED         = 'excluded';
	const STATUS4_DROPPED          = 'dropped';

	/*
	* Possible 'action' field values: new v4 suggestions.
	*/
	const ACTION4_ADDED_SINCE_LAST       = 'added_since_last'; // item added after the snapshot created.
	const ACTION4_NOINDEX_PAGE           = 'noindex';
	const ACTION4_NONCANONICAL           = 'noncanonical'; // canonical url exists and url of page/category is different from canonical url.
	const ACTION4_REDIRECTED             = 'redirected'; // article's url is redirected to other url.
	const ACTION4_MANUALLY_EXCLUDED      = 'manually_excluded'; // manually excluded by user.
	const ACTION4_OUT_OF_SCOPE           = 'out_of_scope'; // item was out of scope, when snapshot created.
	const ACTION4_NEWLY_PUBLISHED        = 'newly_published';
	const ACTION4_ERROR_ANALYZING        = 'error_analyzing';
	const ACTION4_DO_NOTHING             = 'do_nothing';
	const ACTION4_UPDATE_YELLOW          = 'update_yellow';
	const ACTION4_MERGE                  = 'merge';
	const ACTION4_EXCLUDE                = 'exclude';
	const ACTION4_UPDATE_ORANGE          = 'update_orange'; // obsolete since 0.9.1.
	const ACTION4_REWRITE                = 'rewrite'; // replacement to update_orange since 0.9.1.
	const ACTION4_ANALYZING              = 'analyzing'; // item is analyzing, later this status will be updated with one of permanent statuses.
	const ACTION4_ANALYZING_INITIAL      = 'analyzing_initial'; // status to use just when new snapshot created.
	const ACTION4_OUT_OF_SCOPE_INITIAL   = 'out_of_scope_initial'; // status to use just when new snapshot created.
	const ACTION4_ANALYZING_FINAL        = 'analyzing_final'; // status to use when detect all inactive items.
	const ACTION4_OUT_OF_SCOPE_ANALYZING = 'out_of_scope_analyzing'; // status to use when detect all inactive items.

	// is_duplicated column values.
	public const KEYWORD_UNKNOWN        = 0;
	public const KEYWORD_NOT_DUPLICATED = 1;
	public const KEYWORD_DUPLICATED     = 2;

	const POSITION_MAX = 1000000;

	private const OPTION_LAST_AUDIT_TIME = 'ahrefs-seo-content-audit-last-time';
	private const TRANSIENT_ADDED_SINCE  = 'ahrefs_seo_snapshot_added_since';

	public const CAT_FILTER_DIVIDER = '|';
	/** @var Ahrefs_Seo_Data_Content */
	private static $instance = null;

	/** @var int|null */
	private static $snapshot_id = null;

	/**
	 * Return the instance
	 *
	 * $return Ahrefs_Seo_Data_Content
	 */
	public static function get() : Ahrefs_Seo_Data_Content {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Set snapshot ID for all requests
	 *
	 * @param int $snapshot_id Snapshot ID.
	 */
	public static function snapshot_context_set( int $snapshot_id ) : void {
		self::$snapshot_id = $snapshot_id;
	}

	/**
	 * Get snapshot ID for all requests.
	 * This is "current" snapshot by default.
	 *
	 * @return int Snapshot ID.
	 */
	public static function snapshot_context_get() : int {
		if ( is_null( self::$snapshot_id ) ) {
			self::$snapshot_id = ( new Snapshot() )->get_current_snapshot_id();
		}
		return self::$snapshot_id;
	}

	/**
	 * Include post to analysis.
	 * Include in analysis can only be applied to items in the ‘Excluded’ folder.
	 *
	 * @param Post_Tax[] $post_taxes Posts or terms list.
	 * @return Post_Tax[] Can not exclude these items.
	 */
	public function posts_include( array $post_taxes ) : array {
		// update currently displayed snapshot (either 'current' or 'new', if no current snapshot exists).
		$result = ( new Content_Audit_Current( $this->snapshot_context_get() ) )->audit_include_posts( $post_taxes );
		if ( ( new Snapshot() )->has_current_and_new_snapshots() ) {
			// also update 'new' snapshot.
			( new Content_Audit() )->audit_include_posts( $post_taxes );
		}
		if ( count( $post_taxes ) ) {
			Settings_Scope::get()->pages_add_to_checked( $post_taxes );
		}
		return $result;
	}

	/**
	 * Exclude post from analysis.
	 * Exclude from analysis can be applied to items from ‘Well performing’, ‘Under performing’ & ‘Non-performing’.
	 * Exclude items from current and new audit.
	 * Ignore snapshot_id of post tax.
	 *
	 * @param Post_Tax[] $post_taxes Posts or terms list.
	 * @return Post_Tax[] Can not exclude these items.
	 */
	public function posts_exclude( array $post_taxes ) : array {
		// update currently displayed snapshot (either 'current' or 'new', if no current snapshot exists).
		$result = ( new Content_Audit_Current( $this->snapshot_context_get() ) )->audit_exclude_posts( $post_taxes );
		if ( ( new Snapshot() )->has_current_and_new_snapshots() ) {
			// also update 'new' snapshot.
			( new Content_Audit() )->audit_exclude_posts( $post_taxes );
		}
		if ( count( $post_taxes ) ) {
			Settings_Scope::get()->pages_remove_from_checked( $post_taxes );
		}
		return $result;
	}

	/**
	 * Recheck post status.
	 * Recheck status can be applied to ‘noindex’, ‘non-canonical’, ‘redirected’ items.
	 * Applied to current and new audit.
	 * Ignore snapshot_id of post tax.
	 *
	 * @since 0.9.7
	 *
	 * @param Post_Tax[] $post_taxes Posts or terms list.
	 * @return Post_Tax[] Can not recheck these items.
	 */
	public function posts_recheck( array $post_taxes ) : array {
		// update currently displayed snapshot (either 'current' or 'new', if no current snapshot exists).
		$result = ( new Content_Audit_Current( $this->snapshot_context_get() ) )->audit_recheck_posts( $post_taxes );
		if ( ( new Snapshot() )->has_current_and_new_snapshots() ) {
			// also update 'new' snapshot.
			( new Content_Audit() )->audit_recheck_posts( $post_taxes );
		}
		return $result;
	}

	/**
	 * Convert statuses to actions for the where part
	 *
	 * @param string $status Status (tab id), one of Ahrefs_Seo_Data_Content::STATUS4_*.
	 * @return string Correctly escaped part of where.
	 */
	private function status_to_action_clause( string $status ) : string {
		global $wpdb;
		switch ( $status ) {
			case self::STATUS4_ALL_ANALYZED:
				return $wpdb->prepare( 'AND ( c.action = %s || c.action = %s  || c.action = %s  || c.action = %s  || c.action = %s  || c.action = %s || c.action = %s || c.action = %s || c.action = %s )', self::ACTION4_DO_NOTHING, self::ACTION4_UPDATE_YELLOW, self::ACTION4_MERGE, self::ACTION4_EXCLUDE, self::ACTION4_UPDATE_ORANGE, self::ACTION4_REWRITE, self::ACTION4_ANALYZING, self::ACTION4_ANALYZING_INITIAL, self::ACTION4_ANALYZING_FINAL );
			case self::STATUS4_DROPPED:
				return $wpdb->prepare( 'AND ( c.action = %s || c.action = %s || c.action = %s || c.action = %s || c.action = %s ) AND ( last_well_date IS NOT NULL )', self::ACTION4_UPDATE_YELLOW, self::ACTION4_MERGE, self::ACTION4_EXCLUDE, self::ACTION4_UPDATE_ORANGE, self::ACTION4_REWRITE );
			case self::STATUS4_WELL_PERFORMING:
				return $wpdb->prepare( 'AND ( c.action = %s )', self::ACTION4_DO_NOTHING );
			case self::STATUS4_UNDER_PERFORMING:
				return $wpdb->prepare( 'AND ( c.action = %s || c.action = %s )', self::ACTION4_UPDATE_YELLOW, self::ACTION4_MERGE );
			case self::STATUS4_NON_PERFORMING:
				return $wpdb->prepare( 'AND ( c.action = %s || c.action = %s || c.action = %s )', self::ACTION4_EXCLUDE, self::ACTION4_UPDATE_ORANGE, self::ACTION4_REWRITE );
			case self::STATUS4_EXCLUDED:
				return $wpdb->prepare( 'AND ( c.action = %s || c.action = %s || c.action = %s || c.action = %s || c.action = %s || c.action = %s || c.action = %s || c.action = %s || c.action = %s || c.action = %s )', self::ACTION4_NOINDEX_PAGE, self::ACTION4_NONCANONICAL, self::ACTION4_REDIRECTED, self::ACTION4_MANUALLY_EXCLUDED, self::ACTION4_OUT_OF_SCOPE, self::ACTION4_OUT_OF_SCOPE_INITIAL, self::ACTION4_OUT_OF_SCOPE_ANALYZING, self::ACTION4_NEWLY_PUBLISHED, self::ACTION4_ADDED_SINCE_LAST, self::ACTION4_ERROR_ANALYZING );
		}
		return '';
	}

	/**
	 * Create string for using in SQL WHERE using filters
	 *
	 * @param array<string, int|string|array|null> $filters May include filters with indexes: 'cat' category id, 'post_type' with values page or post, 'page_id' int value, 's' search string.
	 * @return string Correctly escaped part of where.
	 */
	private static function apply_filters_to_where( array $filters ) : string {
		global $wpdb;
		$additional_where = [];
		$category         = $filters['cat'] ?? '';
		if ( ! empty( $category ) ) {
			if ( 'product' === $filters['post_type'] ) {
				$additional_where[] = $wpdb->prepare(
					"AND r.object_id = p.ID
					AND tt.term_taxonomy_id = r.term_taxonomy_id
					AND tt.taxonomy = 'product_cat'
					AND tt.term_id = t.term_id
					AND t.term_id = %d",
					$category
				);
			} else {
				$additional_where[] = $wpdb->prepare(
					"AND r.object_id = p.ID
					AND tt.term_taxonomy_id = r.term_taxonomy_id
					AND tt.taxonomy = 'category'
					AND tt.term_id = t.term_id
					AND t.term_id = %d",
					$category
				);
			}
		}
		if ( ! empty( $filters['post_type'] ) ) {
			$additional_where[] = $wpdb->prepare(
				'AND p.post_type = %s',
				$filters['post_type']
			);
		}
		if ( isset( $filters['taxonomy'] ) ) { // empty string to any taxonomy allowed.
			if ( '' !== $filters['taxonomy'] ) {
				$additional_where[] = $wpdb->prepare(
					'AND c.taxonomy = %s',
					$filters['taxonomy']
				);
			} else {
				$additional_where[] = $wpdb->prepare(
					'AND c.taxonomy <> %s',
					''
				);
			}
		}
		if ( ! empty( $filters['page_id'] ) ) {
			$additional_where[] = $wpdb->prepare(
				'AND p.ID = %d',
				$filters['page_id']
			);
		}
		if ( ! empty( $filters['author'] ) ) {
			$additional_where[] = $wpdb->prepare(
				'AND p.post_author = %d',
				$filters['author']
			);
		}
		if ( isset( $filters['s'] ) && '' !== $filters['s'] ) {
			$search             = '%' . $wpdb->esc_like( $filters['s'] ) . '%';
			$additional_where[] = $wpdb->prepare(
				' AND c.title LIKE %s ',
				$search
			);
		}
		Helper_Content::get()->where_filters( $additional_where, $filters );
		if ( isset( $filters['keywords'] ) && '' !== $filters['keywords'] ) {
			switch ( intval( $filters['keywords'] ) ) {
				case Ahrefs_Seo_Table_Content::FILTER_KEYWORDS_NO_DETECTED:
					$additional_where[] = 'AND ( c.is_approved_keyword = 0 OR c.is_approved_keyword IS NULL ) AND ( c.keyword = "" OR c.keyword IS NULL )';
					break;
				case Ahrefs_Seo_Table_Content::FILTER_KEYWORDS_APPROVED:
					$additional_where[] = 'AND c.is_approved_keyword = 1';
					break;
				case Ahrefs_Seo_Table_Content::FILTER_KEYWORDS_SUGGESTED:
					$additional_where[] = $wpdb->prepare( 'AND c.is_approved_keyword = 0 AND c.keyword <> "" AND c.kw_source <> %s AND c.kw_source <> %s AND c.kw_source <> %s', Sources::SOURCE_YOASTSEO, Sources::SOURCE_AIOSEO, Sources::SOURCE_RANKMATH );
					break;
				case Ahrefs_Seo_Table_Content::FILTER_KEYWORDS_YOAST:
					$additional_where[] = $wpdb->prepare( 'AND c.kw_source = %s AND c.keyword <> ""', Sources::SOURCE_YOASTSEO );
					break;
				case Ahrefs_Seo_Table_Content::FILTER_KEYWORDS_RANKMATH:
					$additional_where[] = $wpdb->prepare( 'AND c.kw_source = %s AND c.keyword <> ""', Sources::SOURCE_RANKMATH );
					break;
				case Ahrefs_Seo_Table_Content::FILTER_KEYWORDS_AIOSEO:
					$additional_where[] = $wpdb->prepare( 'AND c.kw_source = %s AND c.keyword <> ""', Sources::SOURCE_AIOSEO );
					break;
				case Ahrefs_Seo_Table_Content::FILTER_KEYWORDS_DUPLICATED:
					$additional_where[] = ( new Duplicated_Keywords() )->filter_where();
					break;
			}
		}
		if ( ! empty( $filters['reason'] ) ) {
			$additional_where[] = $wpdb->prepare(
				'AND c.action = %s',
				$filters['reason']
			);
		}
		return implode( ' ', $additional_where );
	}

	/**
	 * Create string for using in SQL FROM using filters
	 *
	 * @param array $filters May include filters with indexes: 'cat' category id, 'post_type' with values page or post, 'page_id' int value, 's' search string.
	 * @return string
	 */
	private static function apply_filters_to_from( array $filters ) : string {
		global $wpdb;
		$additional_from = [];
		$category        = $filters['cat'] ?? '';
		Helper_Content::get()->from_filters( $additional_from, $filters );
		if ( ! empty( $category ) ) {
			$additional_from[] = ", {$wpdb->term_relationships} r,
			{$wpdb->term_taxonomy} tt,
			{$wpdb->terms} t ";
		}
		return implode( ' ', $additional_from );
	}

	/**
	 * Get content table items
	 *
	 * @param string                               $type Status, one of self::STATUS_xxx constants.
	 * @param string                               $date Date with Year and Month as 'YYYYMM'.
	 * @param array<string, int|string|array|null> $filters May include filters with indexes: 'cat' category id, 'post_type' with values page or post, 'page_id' int value, 's' search string.
	 * @param int                                  $start Start from, number.
	 * @param int                                  $per_page Per page count.
	 * @param string                               $orderby Order by, field name.
	 * @param string                               $order Order, asc or desc.
	 *
	 * @return array<stdClass>
	 */
	public function data_get_clear( string $type, string $date, array $filters, int $start = 0, int $per_page = 10, string $orderby = 'post_date', string $order = 'asc' ) : array {
		global $wpdb;
		$snapshot_id        = self::snapshot_context_get();
		$orderby            = sanitize_sql_orderby( "$orderby $order" );
		$additional_from    = [ $this::apply_filters_to_from( $filters ) ];
		$additional_where   = [ $this::apply_filters_to_where( $filters ) ];
		$additional_where[] = "AND ( c.taxonomy = '' AND p.post_status = 'publish' OR c.taxonomy <> '')"; // published posts or any terms.
		$additional_where[] = 'AND ( c.taxonomy <> "" OR p.post_type IN (' . self::get_allowed_post_types_for_where() . ') )';

		if ( ! empty( $date ) ) {
			$additional_where[] = $wpdb->prepare( 'AND ( concat(YEAR( c.date_updated ), RIGHT(CONCAT("0", MONTH( c.date_updated )), 2) ) = %s ) ', $date ); // use date field from ahrefs content table.
		}
		// must be last call for additional where.
		$additional_where[] = $this->status_to_action_clause( $type );

		$additional_from  = implode( ' ', $additional_from );
		$additional_where = implode( ' ', $additional_where );

		// load both posts and terms. Note: there is similar query at data_get_by_ids method, both must be updated and return the same data set.
		$sql    = $wpdb->prepare( "SELECT c.post_id as post_id, c.title as title, p.post_author as author, p.post_type as post_type, date(c.date_updated) as created, c.total_month as 'total', c.organic_month as 'organic', c.backlinks, c.refdomains, c.position, c.keyword, c.kw_low, kw_source, c.is_approved_keyword, c.action, UNIX_TIMESTAMP(c.updated) as 'ver', c.badge, c.taxonomy as taxonomy, c.last_well_date FROM {$wpdb->ahrefs_content} c LEFT JOIN {$wpdb->posts} p ON p.ID = c.post_id AND c.taxonomy = '' $additional_from WHERE c.snapshot_id = %d $additional_where " . ( $orderby ? ' ORDER BY ' . $orderby : '' ) . ' LIMIT %d, %d', $snapshot_id, absint( $start ), absint( $per_page ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->get_results( $sql, OBJECT ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// we have posts without categories details.
		$this->append_categories( $result );

		return $result;
	}

	/**
	 * Return count of items
	 *
	 * @param string                         $type Status, one of self::STATUS_xxx constants.
	 * @param string                         $date Date with Year and Month as 'YYYYMM'.
	 * @param array<string, int|string|null> $filters May include filters with indexes: 'cat' category id, 'post_type' with values page or post, 'page_id' int value, 's' search string.
	 * @return int
	 */
	public function data_get_clear_count( string $type, string $date, array $filters ) : int {
		global $wpdb;
		$snapshot_id        = self::snapshot_context_get();
		$additional_from    = [ $this::apply_filters_to_from( $filters ) ];
		$additional_where   = [ $this::apply_filters_to_where( $filters ) ];
		$additional_where[] = "AND ( p.post_status = 'publish' AND p.post_type IN (" . self::get_allowed_post_types_for_where() . ") OR c.taxonomy <> '' )";

		if ( ! empty( $date ) ) {
			$additional_where[] = $wpdb->prepare( 'AND ( concat(YEAR( c.date_updated ), RIGHT(CONCAT("0", MONTH( c.date_updated )), 2) ) = %s ) ', $date );
		}
		// must be last call for additional where.
		$additional_where[] = $this->status_to_action_clause( $type );

		$additional_from  = implode( ' ', $additional_from );
		$additional_where = implode( ' ', $additional_where );

		$sql = $wpdb->prepare( "SELECT count(*) FROM {$wpdb->ahrefs_content} c LEFT JOIN {$wpdb->posts} p ON p.ID = c.post_id AND c.taxonomy = '' $additional_from WHERE c.snapshot_id = %d $additional_where", $snapshot_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return absint( $wpdb->get_var( $sql ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Append categories details and id to result.
	 *
	 * @param stdClass[] $result Result from get_results at content audit table.
	 * @return void
	 */
	private function append_categories( array &$result ) : void {
		$snapshot_id = self::snapshot_context_get();
		// add categories to found items.
		foreach ( $result as &$item ) {
			$item->id = Post_Tax::get_post_tax_string( (int) $item->post_id, $item->taxonomy, $snapshot_id );

			$item->categories = []; // this index will have array with links to category, but we will handle it and use Categories select dropdown.
			if ( '' === $item->taxonomy ) {
				if ( 'product' === $item->post_type ) {
					$cats   = Helper_Content::get()->get_the_terms( (int) $item->post_id, 'product_cat' );
					$prefix = 'product';
				} else {
					$cats   = Helper_Content::get()->get_the_terms( (int) $item->post_id, 'category' );
					$prefix = 'cat';
				}
				if ( count( $cats ) ) {
					foreach ( $cats as $cat ) {
						if ( is_int( $cat ) ) { // @phpstan-ignore-line -- third party filters can return int instead of \WP_Term.
							$cat = get_term( $cat, 'product' === $item->post_type ? 'product_cat' : 'category' );
							if ( ! ( $cat instanceof WP_Term ) ) {
								continue;
							}
						}
						$item->categories[] = sprintf(
							'<a href="%s" class="ahrefs-cat-link">%s</a>',
							Helper_Content::get()->update_link(
								Links::content_audit( null, $prefix . self::CAT_FILTER_DIVIDER . $cat->term_id )
							),
							$cat->name
						);
					}
				}
			}
		}
	}

	/**
	 * Check and return updated items from given list.
	 *
	 * @param array<string, int> $post_tax_strings_with_ver Associative array ( (string)post_tax_string => (int)ver ).
	 * @return Post_Tax[] Array of post_id with updates (ver value changed).
	 */
	public function get_updated_items( array $post_tax_strings_with_ver ) : array {
		// filter keys and remove trailing part with snapshot_id.

		$snapshot_id = self::snapshot_context_get();
		return array_map(
			function( $post_tax_string ) {
				return Post_Tax::create_from_string( "$post_tax_string" );
			},
			Ahrefs_Seo_Db_Helper::get_updated_post_tax_strings( $post_tax_strings_with_ver, $snapshot_id )
		);
	}

	/**
	 * Get content table items using their post ids.
	 *
	 * @param Post_Tax[] $post_taxes Posts or terms list.
	 *
	 * @return array<stdClass>
	 * @see data_get_clear()
	 */
	public function data_get_by_ids( array $post_taxes ) : array {
		global $wpdb;
		$post_ids  = [];
		$terms_ids = [];
		array_walk(
			$post_taxes,
			function( Post_Tax $post_tax ) use ( &$post_ids, &$terms_ids ) {
				if ( $post_tax->is_post() ) {
					$post_ids[] = $post_tax->get_post_id();
				} else {
					$terms_ids[] = $post_tax->get_post_id();
				}
			}
		);

		$snapshot_id = self::snapshot_context_get();
		$result1     = [];
		$result2     = [];
		// Part 1: posts.
		if ( count( $post_ids ) ) {
			$additional_where   = [];
			$additional_where[] = "AND p.post_status = 'publish'";
			$additional_where[] = 'AND ( p.post_type IN (' . self::get_allowed_post_types_for_where() . ') )';

			$placeholder        = array_fill( 0, count( $post_ids ), '%d' );
			$additional_where[] = $wpdb->prepare( 'AND post_id in ( ' . implode( ', ', $placeholder ) . ')', $post_ids ); // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare,WordPress.DB.PreparedSQL.NotPrepared
			$additional_where   = implode( ' ', $additional_where );

			$sql = $wpdb->prepare( "SELECT c.post_id as post_id, c.title as title, p.post_author as author, p.post_type as post_type, date(date_updated) as created, total_month as 'total', organic_month as 'organic', backlinks, refdomains, position, keyword, kw_low, kw_source, is_approved_keyword, action, UNIX_TIMESTAMP(c.updated) as 'ver', badge, c.taxonomy as taxonomy, c.last_well_date FROM {$wpdb->ahrefs_content} c RIGHT JOIN {$wpdb->posts} p ON p.ID = c.post_id WHERE c.snapshot_id = %d AND c.taxonomy = '' $additional_where LIMIT %d ", $snapshot_id, count( $post_ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$result1 = $wpdb->get_results( $sql, OBJECT ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		if ( count( $terms_ids ) ) {
			$additional_where   = [];
			$placeholder        = array_fill( 0, count( $terms_ids ), '%d' );
			$additional_where[] = $wpdb->prepare( 'AND post_id in ( ' . implode( ', ', $placeholder ) . ')', $terms_ids ); // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare,WordPress.DB.PreparedSQL.NotPrepared
			$additional_where   = implode( ' ', $additional_where );

			$sql     = $wpdb->prepare( "SELECT c.post_id as post_id, c.title as title, '' as author, '' as post_type, date(date_updated) as created, total_month as 'total', organic_month as 'organic', backlinks, refdomains, position, keyword, kw_low, kw_source, is_approved_keyword, action, UNIX_TIMESTAMP(c.updated) as 'ver', badge, c.taxonomy as taxonomy, c.last_well_date FROM {$wpdb->ahrefs_content} c WHERE c.snapshot_id = %d AND c.taxonomy <> '' $additional_where LIMIT %d ", $snapshot_id, count( $terms_ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$result2 = $wpdb->get_results( $sql, OBJECT ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
		$result = array_merge( $result1, $result2 );
		$this->append_categories( $result );
		return $result;
	}

	/**
	 * Return number of items by status, for published posts&pages only
	 *
	 * @return array<string, int> index is status, value is count.
	 */
	public function data_get_count_by_status() : array {
		$data                                 = self::data_get_count_by_action();
		$count_in_queue                       = ( $data[ self::ACTION4_ANALYZING ] ?? 0 ) + ( $data[ self::ACTION4_ANALYZING_INITIAL ] ?? 0 ) + ( $data[ self::ACTION4_ANALYZING_FINAL ] ?? 0 );
		$result                               = [
			self::STATUS4_WELL_PERFORMING  => ( $data[ self::ACTION4_DO_NOTHING ] ?? 0 ),
			self::STATUS4_UNDER_PERFORMING => ( $data[ self::ACTION4_UPDATE_YELLOW ] ?? 0 ) + ( $data[ self::ACTION4_MERGE ] ?? 0 ),
			self::STATUS4_NON_PERFORMING   => ( $data[ self::ACTION4_EXCLUDE ] ?? 0 ) + ( $data[ self::ACTION4_UPDATE_ORANGE ] ?? 0 ) + ( $data[ self::ACTION4_REWRITE ] ?? 0 ),
			self::STATUS4_EXCLUDED         => ( $data[ self::ACTION4_NOINDEX_PAGE ] ?? 0 ) + ( $data[ self::ACTION4_NONCANONICAL ] ?? 0 ) + ( $data[ self::ACTION4_REDIRECTED ] ?? 0 ) + ( $data[ self::ACTION4_MANUALLY_EXCLUDED ] ?? 0 ) + ( $data[ self::ACTION4_OUT_OF_SCOPE ] ?? 0 ) + ( $data[ self::ACTION4_OUT_OF_SCOPE_INITIAL ] ?? 0 ) + ( $data[ self::ACTION4_OUT_OF_SCOPE_ANALYZING ] ?? 0 ) + ( $data[ self::ACTION4_NEWLY_PUBLISHED ] ?? 0 ) + ( $data[ self::ACTION4_ADDED_SINCE_LAST ] ?? 0 ) + ( $data[ self::ACTION4_ERROR_ANALYZING ] ?? 0 ),
			self::STATUS4_DROPPED          => $this->calculate_dropped_count(),
		];
		$result[ self::STATUS4_ALL_ANALYZED ] = $count_in_queue + $result[ self::STATUS4_WELL_PERFORMING ] + $result[ self::STATUS4_UNDER_PERFORMING ] + $result[ self::STATUS4_NON_PERFORMING ];
		return $result;
	}

	/**
	 * Get number of active, but dropped (no longer well-performing) posts
	 *
	 * @since 0.8.4
	 *
	 * @param int|null $snapshot_id Snapshot ID or use default on null.
	 * @return int
	 */
	public function calculate_dropped_count( ?int $snapshot_id = null ) : int {
		global $wpdb;
		return intval( $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM {$wpdb->ahrefs_content} c WHERE ( c.snapshot_id = %d ) " . $this->status_to_action_clause( self::STATUS4_DROPPED ), $snapshot_id ?? self::snapshot_context_get() ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Posts with duplicated keywords exists
	 *
	 * @since 0.8.4
	 *
	 * @param int|null $snapshot_id Snapshot ID or use default on null.
	 * @return bool
	 */
	public function calculate_duplicated_exists( ?int $snapshot_id = null ) : bool {
		global $wpdb;
		$filter = [ 'keywords' => Ahrefs_Seo_Table_Content::FILTER_KEYWORDS_DUPLICATED ];
		$from   = $this->apply_filters_to_from( $filter );
		$where  = $this->apply_filters_to_where( $filter );
		return ! is_null( $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->ahrefs_content} c {$from} WHERE ( c.snapshot_id = %d ) {$where} LIMIT 1", $snapshot_id ?? self::snapshot_context_get() ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- from and where vars are safe for use.
	}

	/**
	 * Posts with suggested keywords exists, All analyzed tab only.
	 *
	 * @since 0.8.4
	 *
	 * @param int|null $snapshot_id Snapshot ID or use default on null.
	 * @return bool
	 */
	public function calculate_suggested_exists( ?int $snapshot_id = null ) : bool {
		global $wpdb;
		$filter = [ 'keywords' => Ahrefs_Seo_Table_Content::FILTER_KEYWORDS_SUGGESTED ];
		$from   = $this->apply_filters_to_from( $filter );
		$where  = $this->apply_filters_to_where( $filter ) . $this->status_to_action_clause( '' ); // tab 'All analyzed'.
		return ! is_null( $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->ahrefs_content} c {$from} WHERE ( c.snapshot_id = %d ) {$where} LIMIT 1", $snapshot_id ?? self::snapshot_context_get() ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- from and where vars are safe for use.
	}

	/**
	 * Return count of items by action for chart
	 *
	 * @return int[] Associative array [chart action => int].
	 */
	public static function get_statuses_for_charts() : array {
		$data = self::get()->data_get_count_by_status();
		return [
			Ahrefs_Seo_Charts::CHART_WELL_PERFORMING => $data[ self::STATUS4_WELL_PERFORMING ],
			Ahrefs_Seo_Charts::CHART_UNDERPERFORMING => $data[ self::STATUS4_UNDER_PERFORMING ],
			Ahrefs_Seo_Charts::CHART_NON_PERFORMING  => $data[ self::STATUS4_NON_PERFORMING ],
			Ahrefs_Seo_Charts::CHART_EXCLUDED        => $data[ self::STATUS4_EXCLUDED ],
		];
	}

	/**
	 * Return count by action for published posts&pages only.
	 * Static method.
	 *
	 * @return array<string, int> Key is action, value is count.
	 */
	public static function data_get_count_by_action() : array {
		global $wpdb;
		$result      = [];
		$snapshot_id = self::snapshot_context_get();
		// count posts & terms in content table, do not count missing in content table posts of allowed types as "added since".
		$data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT c.action, count(c.post_id) as number FROM {$wpdb->ahrefs_content} c WHERE c.snapshot_id = %d GROUP BY c.action",
				$snapshot_id
			),
			ARRAY_A
		);

		if ( is_array( $data ) && count( $data ) ) {
			foreach ( $data as $row ) {
				$current_action                     = $row['action'] ?? self::ACTION4_ADDED_SINCE_LAST; // Items never added (action is null) to content audit will have ACTION4_ADDED_SINCE_LAST.
				$result[ (string) $current_action ] = (int) $row['number'] + ( $result[ (string) $current_action ] ?? 0 );
			}
		}

		return $result;
	}

	/**
	 * Remove all post details from DB (content table only) for current snapshot and new snapshot.
	 * Ignore snapshot_id field of post tax.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return void
	 */
	public function delete_post_details( Post_Tax $post_tax ) : void {
		( new Content_Audit_Current( $this->snapshot_context_get() ) )->audit_delete_post_details( $post_tax );
		if ( ( new Snapshot() )->has_current_and_new_snapshots() ) {
			( new Content_Audit() )->audit_delete_post_details( $post_tax );
		}
	}

	/**
	 * Add post or term to current and new snapshots as ACTION4_ADDED_SINCE_LAST.
	 * Use post ID, not post_tax as parameter.
	 *
	 * @param int    $post_id Post or term ID.
	 * @param string $taxonomy Taxonomy.
	 * @return void
	 */
	public function add_post_as_added_since_last( int $post_id, string $taxonomy = '' ) : void {
		global $wpdb;
		$snapshot_id = $this->snapshot_context_get();// current snapshot.
		$badge       = substr( ( '' === $taxonomy ? ( get_post_type( $post_id ) ?: '...' ) : $taxonomy ), 0, 20 );
		$post_tax    = new Post_Tax( $post_id, $taxonomy, $snapshot_id );
		$title       = $post_tax->get_title();

		$date_updated = null;
		if ( $post_tax->is_post() ) {
			$post = Helper_Content::get()->get_post( $post_tax->get_post_id() );
			if ( $post instanceof WP_Post ) {
				$date_updated = $post->post_date;
			}
		}
		$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->ahrefs_content} ( snapshot_id, post_id, taxonomy, action, badge, title, date_updated ) VALUES ( %d, %d, %s, %s, %s, %s, %s ) ON DUPLICATE KEY UPDATE action = %s", $snapshot_id, $post_id, $taxonomy, self::ACTION4_ADDED_SINCE_LAST, $badge, $title, self::ACTION4_ADDED_SINCE_LAST, $date_updated ) );

		// update new snapshot, if exists.
		$snapshot    = new Snapshot();
		$snapshot_id = $snapshot->has_current_and_new_snapshots() ? $snapshot->get_new_snapshot_id() : null; // new snapshot id.
		if ( ! is_null( $snapshot_id ) ) {
			$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->ahrefs_content} ( snapshot_id, post_id, taxonomy, action, badge, title, date_updated ) VALUES ( %d, %d, %s, %s, %s, %s, %s ) ON DUPLICATE KEY UPDATE action = %s", $snapshot_id, $post_id, $taxonomy, self::ACTION4_ADDED_SINCE_LAST, $badge, $title, self::ACTION4_ADDED_SINCE_LAST, $date_updated ) );
		}
	}

	/**
	 * Update 'added since' items for the new and current snapshots.
	 *
	 * @since 0.8.0
	 *
	 * @return bool Something was added.
	 */
	public function update_added_since_items() : bool {
		global $wpdb;
		$time = get_transient( self::TRANSIENT_ADDED_SINCE );
		if ( empty( $time ) ) {
			set_transient( self::TRANSIENT_ADDED_SINCE, time(), 10 * MINUTE_IN_SECONDS ); // do not update next 10 minutes.
			$data = $wpdb->get_results(
				"(SELECT p.ID as 'post_id', '' as 'taxonomy'
				FROM {$wpdb->ahrefs_content} c RIGHT JOIN {$wpdb->posts} p ON c.post_id = p.ID AND c.taxonomy = ''
				WHERE
				c.snapshot_id IS NULL AND p.post_status = 'publish' AND ( p.post_type IN (" . self::get_allowed_post_types_for_where() . // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				") )
				LIMIT 50)

				UNION ALL

				(SELECT t.term_id as 'post_id', t.taxonomy as 'taxonomy'
				FROM {$wpdb->ahrefs_content} c RIGHT JOIN {$wpdb->term_taxonomy} t ON c.post_id = t.term_id AND c.taxonomy <> ''
				WHERE
				c.snapshot_id IS NULL AND ( t.taxonomy IN ('category'" . ( $this->products_exists() ? ",'product_cat'" : '' ) . ') ) ' . // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				'LIMIT 50)',
				ARRAY_A
			);

			if ( ! empty( $data ) ) {
				foreach ( $data as $row ) {
					$post_tax = new Post_Tax( (int) $row['post_id'], (string) $row['taxonomy'] );
					if ( $post_tax->exists() ) {
						$this->add_post_as_added_since_last( (int) $row['post_id'], (string) $row['taxonomy'] );
					}
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * Update item title for current snapshot and new snapshot.
	 * Ignore snapshot_id field of post tax.
	 *
	 * @since 0.8.0
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param string   $new_title New title.
	 * @return void
	 */
	public function update_post_title( Post_Tax $post_tax, string $new_title ) : void {
		global $wpdb;
		$post_tax->set_snapshot_id( $this->snapshot_context_get() );// current snapshot.
		$wpdb->update( $wpdb->ahrefs_content, [ 'title' => $new_title ], $post_tax->as_where_array(), [ '%s' ], $post_tax->as_where_format() );
		// update new snapshot, if exists.
		$snapshot    = new Snapshot();
		$snapshot_id = $snapshot->has_current_and_new_snapshots() ? $snapshot->get_new_snapshot_id() : null; // new snapshot id.
		if ( ! is_null( $snapshot_id ) ) {
			$post_tax->set_snapshot_id( $snapshot_id );
			$wpdb->update( $wpdb->ahrefs_content, [ 'title' => $new_title ], $post_tax->as_where_array(), [ '%s' ], $post_tax->as_where_format() );
		}
	}

	/**
	 * Convert options from previous versions to current. Delete obsolete options.
	 *
	 * @param int $old_version Old version or rules.
	 * @return void
	 */
	public function update_options( int $old_version ) : void {
		$old_options = [];
		if ( $old_version < 4 ) {
			$waiting_time_months = get_option( 'ahrefs-seo-content-waiting-time', null );
			if ( ! is_null( $waiting_time_months ) ) {
				update_option( self::OPTION_WAITING_UNITS, self::WAITING_UNIT_MONTH );
			}
			$old_options = [
				'ahrefs-seo-content-count-visitors',
				'ahrefs-seo-content-count-organic',
				'ahrefs-seo-content-min-backlinks',
				'ahrefs-seo-content-waiting-time',
			];
		}
		if ( $old_version < 5 ) {
			$waiting_time_weeks = get_option( 'ahrefs-seo-content-waiting-weeks', null );
			if ( ! is_null( $waiting_time_weeks ) ) {
				update_option( self::OPTION_WAITING_UNITS, self::WAITING_UNIT_WEEK );
				update_option( self::OPTION_WAITING_VALUE, (int) $waiting_time_weeks );
			}
			$old_options[] = 'ahrefs-seo-content-waiting-weeks';
		}
		if ( count( $old_options ) ) {
			array_walk(
				$old_options,
				function( string $option ) {
					if ( ! is_null( get_option( $option, null ) ) ) {
						delete_option( $option );
					}
				}
			);
		}
	}

	/**
	 * Return backlinks for post.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return int|null
	 */
	public function content_get_backlinks_for_post( Post_Tax $post_tax ) : ?int {
		global $wpdb;
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT backlinks FROM {$wpdb->ahrefs_content} WHERE post_id = %d AND snapshot_id = %d AND taxonomy = %s", $post_tax->get_post_id(), $post_tax->get_snapshot_id(), $post_tax->get_taxonomy() ) );
		return is_null( $result ) ? null : (int) $result;
	}

	/**
	 * Return ref.domains for post.
	 *
	 * @since 0.9.8
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param bool     $cached_value True: return cached value, False: call API for fresh value.
	 * @return int|null
	 */
	public function content_get_ref_domains_for_post( Post_Tax $post_tax, bool $cached_value = true ) : ?int {
		global $wpdb;
		if ( ! $cached_value ) { // try to load fresh data.
			( new Worker_Backlinks() )->update_posts_info( [ $post_tax ] );
		}
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT refdomains FROM {$wpdb->ahrefs_content} WHERE post_id = %d AND snapshot_id = %d AND taxonomy = %s", $post_tax->get_post_id(), $post_tax->get_snapshot_id(), $post_tax->get_taxonomy() ) );
		return is_null( $result ) ? null : (int) $result;
	}

	/**
	 * Return noindex, noncanonical, redirected statuses for post.
	 *
	 * @since 0.9.7
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return array<string, bool|string>
	 */
	public function content_get_noindexes_for_post( Post_Tax $post_tax ) : array {
		global $wpdb;
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT is_noindex, is_noncanonical, is_redirected FROM {$wpdb->ahrefs_content} WHERE post_id = %d AND snapshot_id = %d AND taxonomy = %s", $post_tax->get_post_id(), $post_tax->get_snapshot_id(), $post_tax->get_taxonomy() ), ARRAY_A );
		return is_null( $result ) ? [
			'is_noindex'      => false,
			'is_noncanonical' => false,
			'is_redirected'   => false,
		] : $result;
	}

	/**
	 * Return string, when last content audit was completed, otherwise 'update in progress' or empty string.
	 *
	 * @return string[] One or more strings.
	 */
	private function get_last_updated_time_string() : array {
		$time = $this->get_last_audit_time();

		if ( ! is_null( $time ) ) {
			return [
				__( 'Last update:', 'ahrefs-seo' ),
				date_i18n(
				/* Translators: This is a date format string (ex: Fri, 23 Jul 2021, 1:45 PM). Leave it untouched or read more: https://wordpress.org/support/article/formatting-date-and-time/ */
					__( 'D, j M Y, g:i A', 'ahrefs-seo' ),
					$time + (int) ( (float) get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS )
				),
			]; // 'Last update: Fri, 23 Jul 2021, 1:45 PM'.
		}
		return [ ( new Content_Audit() )->require_update() ? __( 'Update in progress', 'ahrefs-seo' ) : '' ];
	}

	/**
	 * Get statistics of content audit.
	 *
	 * @return array<string,mixed> Array {
	 *
	 *   @type bool $in_progress Is audit in progress?
	 *   @type float $percents Current progress.
	 *   @type string $last_time Last audit time.
	 * }
	 */
	public function get_statistics() : array {
		$content_audit = new Content_Audit();
		$in_progress   = $content_audit->require_update();
		$percents      = $in_progress ? 100 - $content_audit->content_get_unprocessed_percent() : 100;

		return [
			'in_progress' => $in_progress,
			'percents'    => $percents,
			'last_time'   => $this->get_last_updated_time_string(),
		];
	}

	/**
	 * Approve keyword of post
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param bool     $approve True: approve, false: remove approvement.
	 * @return void
	 */
	public function keyword_approve( Post_Tax $post_tax, bool $approve = true ) : void {
		global $wpdb;
		$snapshot_id = $post_tax->get_snapshot_id();
		if ( ! is_null( $snapshot_id ) ) {
			$wpdb->update(
				$wpdb->ahrefs_content,
				[ 'is_approved_keyword' => $approve ? 1 : 0 ],
				$post_tax->as_where_array(),
				[ '%d' ],
				$post_tax->as_where_format()
			);
			( new Events() )->on_keyword_approved( $snapshot_id );
		}
	}

	/**
	 * Is post keyword approved?
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return bool
	 */
	public function is_keyword_approved( Post_Tax $post_tax ) : bool {
		global $wpdb;
		return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT is_approved_keyword FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND post_id = %d AND taxonomy = %s", $post_tax->get_snapshot_id(), $post_tax->get_post_id(), $post_tax->get_taxonomy() ) );
	}

	/**
	 * Is post keyword imported from other SEO plugin?
	 *
	 * @since 0.8.8
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return bool
	 */
	public function is_keyword_imported( Post_Tax $post_tax ) : bool {
		global $wpdb;
		$source_id = $wpdb->get_var( $wpdb->prepare( "SELECT kw_source FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND post_id = %d AND taxonomy = %s", $post_tax->get_snapshot_id(), $post_tax->get_post_id(), $post_tax->get_taxonomy() ) );
		return Sources::is_source_imported( $source_id );
	}

	/**
	 * Return last audit time
	 *
	 * @return int|null Unix timestamp or null if no audit completed.
	 */
	public function get_last_audit_time() : ?int {
		$result = get_option( self::OPTION_LAST_AUDIT_TIME );
		return is_numeric( $result ) ? (int) $result : null;
	}

	/**
	 * Set last audit time
	 *
	 * @param int $time Unix timestamp.
	 * @return void
	 */
	public function set_last_audit_time( int $time ) : void {
		update_option( self::OPTION_LAST_AUDIT_TIME, $time );
	}

	/**
	 * Currently viewed snapshot is updating now.
	 *
	 * @return bool
	 */
	public function is_updating_now() : bool {
		$snapshot_id = $this->snapshot_context_get();
		$new_id      = ( new Snapshot() )->get_new_snapshot_id();

		return $snapshot_id === $new_id;
	}

	/**
	 * Get allowed for returning post types.
	 * Post, page and all listed in settings CPT.
	 *
	 * @since 0.8.0
	 *
	 * @return string
	 */
	public static function get_allowed_post_types_for_where() : string {
		global $wpdb;
		$result   = array_keys( ( new Ahrefs_Seo_Content_Settings() )->get_custom_post_types( false ) );
		$result[] = 'post';
		$result[] = 'page';
		return $wpdb->prepare( implode( ',', array_fill( 0, count( $result ), '%s' ) ), $result );
	}

	/**
	 * Is it possible to run new content audit?
	 * Both Ahrefs and Google accounts must be connected.
	 *
	 * @since 0.8.4
	 *
	 * @return bool
	 */
	public function can_run_new_audit() : bool {
		$api       = Ahrefs_Seo_Api::get();
		$analytics = Ahrefs_Seo_Analytics::get();
		return ! $api->is_disconnected() && ! $api->is_limited_account( true ) && $analytics->get_data_tokens()->is_token_set() && $analytics->is_ua_set() && $analytics->is_gsc_set();
	}

	/**
	 * Get raw data for export
	 *
	 * @since 0.9.4
	 *
	 * @param string $tab Tab for export.
	 * @param int    $snapshot_id Snapshot ID.
	 * @return array
	 */
	public function get_data_for_export_tab( string $tab, int $snapshot_id ) : array {
		global $wpdb;

		$additional_where = $this->status_to_action_clause( $tab );
		$result           = $wpdb->get_results( $wpdb->prepare( "SELECT c.post_id as post_id, c.title as title, p.post_author as author, p.post_type as post_type, date(c.date_updated) as created, c.total_month as 'total', c.organic_month as 'organic', c.backlinks, c.refdomains, c.position, c.keyword, c.kw_low, kw_source, c.is_approved_keyword, c.action, c.badge, c.taxonomy as taxonomy, c.last_well_date FROM {$wpdb->ahrefs_content} c LEFT JOIN {$wpdb->posts} p ON c.post_id = p.ID AND c.taxonomy = '' WHERE snapshot_id = %d $additional_where ORDER BY post_id, taxonomy", $snapshot_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $additional_where is safe.

		// we have posts without categories details.
		$this->append_categories( $result );

		return $result;
	}
}
