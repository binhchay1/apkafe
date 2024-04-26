<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Content_Tips\Events;
use ahrefs\AhrefsSeo\Features\Duplicated_Keywords;
use ahrefs\AhrefsSeo\Keywords\Data_Content_Storage;
use ahrefs\AhrefsSeo\Keywords\Data_Keyword;
use ahrefs\AhrefsSeo\Keywords\Data_Keywords;
use ahrefs\AhrefsSeo\Keywords\Keywords_Search;
use ahrefs\AhrefsSeo\Third_Party\Assigned_Keyword;
use ahrefs\AhrefsSeo\Third_Party\Sources;
use Exception;

/*
We can have 3 types of keywords by source (db ahrefs_seo_content table, keyword_source field):
- 1 or 0 keyword imported from each active SEO plugins (Yoast);
- assigned manually: input field in popup dialog.
- up to 10 proposed by gsc: "gsc" badge in popup dialog;
- up to 5 proposed by tf-idf: "tf-idf" badge;

We run bulk keywords update:
- on initial wizard, at last step;
- on each audit (when new snapshot created);

We update keywords of each single post:
- when keyword is not approved by user;
- when keyword is imported from other plugin (using kw_source field).

Time:
- When keywords received from GSC we do 2-3 requests (case A below) or 0-1 request (case B below);
- We are doing at pauses between requests (Ahrefs_Seo_Analytics::API_MIN_DELAY, counting from previous request started time);
- GSC can have a big delay before API returned data on first request (it seems it cache results and next requests are much faster).

How is GSC working now?
A. Common update, when we load detail during content audit or receive updated keywords suggestions (on keywords popup opened):
When we load keyword suggestions from GSC we make up to 3 queries:
- import existing keywords from SEO plugins (Yoast, RankMath);
- get total clicks, positions and impressions - this required, as we must show values in percents;
- get top 10 results - what we show at the keywords popup. Search current keyword here, if not found then:
- get clicks, positions and impressions exactly for current keyword.

B. Fast update, when user sets a new keyword:
- we try to load position from cached gsc result (from last query)
- if not found, then make single request using filter with current keyword and update position value (do not cache result).
This is working much faster, that common update (with 2 or 3 queries).
But we do not have impressions. clicks and percents values. If "Change" link is clicked again, we will load all the details and update keywords table with fresh results (using case A).


Summary:
We do NOT update keywords using automated results when they already set by user ("is_approved_keyword" is set).
We pull fresh keyword if it imported from other SEO plugin.
GSC or TF-IDF can propose another keywords in different times (when GSC will have new click details or more new posts added for TF-IDF).

Note: we must add any inactive post (not added at the Wizard or created later) post to content table before assign keywords or use cached suggestions.
Note: the "post" word means any post, page, product, custom post type, category or product category page.
*/

/**
 * Wrapper class for keywords features.
 */
class Ahrefs_Seo_Keywords {

	/**
	 * @var array
	 */
	private $all_posts = [];

	/**
	 * @var array
	 */
	private $all_keywords = [];

	/**
	 * @var Ahrefs_Seo_Keywords
	 */
	private static $instance = null;

	/**
	 * @var array<int, string>
	 */
	private $keywords_source_cache = [];

	/** Allow to create new keywords once per seconds. */
	const KEY_MIN_DELAY = 0.05;
	/** @var float Time when last keyword created. */
	private $last_query_time = 0;

	/**
	 * @var Ahrefs_Seo_Data_Content
	 */
	private $data_content;

	/**
	 * @var Ahrefs_Seo_Analytics
	 */
	private $analytics;

	/**
	 * Return the instance
	 *
	 * @param Ahrefs_Seo_Analytics|null $analytics Analytics instance for use in requests to GSC.
	 * @return Ahrefs_Seo_Keywords
	 */
	public static function get( ?Ahrefs_Seo_Analytics $analytics = null ) : Ahrefs_Seo_Keywords {
		if ( null === self::$instance ) {
			self::$instance = new self( Ahrefs_Seo_Data_Content::get() );
		}
		if ( ! is_null( $analytics ) ) {
			self::$instance->analytics = $analytics;
		}
		if ( is_null( self::$instance->analytics ) ) { // @phpstan-ignore-line
			self::$instance->analytics = Ahrefs_Seo_Analytics::get();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @param Ahrefs_Seo_Data_Content $data_content Data Content instance.
	 */
	public function __construct( Ahrefs_Seo_Data_Content $data_content ) {
		$this->data_content = $data_content;
	}

	/**
	 * Get keywords assigned to post
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return string|null Assigned keyword if any.
	 */
	public function post_keyword_get( Post_Tax $post_tax ) : ?string {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT keyword FROM {$wpdb->ahrefs_content} WHERE post_id = %d AND snapshot_id = %d AND taxonomy = %s", $post_tax->get_post_id(), $post_tax->get_snapshot_id(), $post_tax->get_taxonomy() ) );
	}

	/**
	 * Is this post keywords is approved?
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return bool
	 */
	public function post_keywords_is_approved( Post_Tax $post_tax ) : bool {
		global $wpdb;
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT is_approved_keyword FROM {$wpdb->ahrefs_content} WHERE post_id = %d AND snapshot_id = %d AND taxonomy = %s LIMIT 1", $post_tax->get_post_id(), $post_tax->get_snapshot_id(), $post_tax->get_taxonomy() ) );
		return ! empty( $result );
	}

	/**
	 * Reset keywords need update flag.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return void
	 */
	public function post_keywords_set_updated( Post_Tax $post_tax ) : void {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( sprintf( '%s (%s)', __METHOD__, (string) $post_tax ) );
		$sql = $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET keywords_need_update = 0 WHERE post_id = %d AND snapshot_id = %d AND taxonomy = %s", $post_tax->get_post_id(), $post_tax->get_snapshot_id(), $post_tax->get_taxonomy() );
		if ( 0 === $wpdb->query( $sql ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$this->maybe_add_post_and_retry_query( $post_tax, $sql );
		}
	}

	/**
	 * Set post keywords (selected with source and user keyword).
	 * Update position (using cached results from GSC) or set a flag for an update.
	 * Set flag for keywords update if keyword is null.
	 * Will update article recommended action.
	 * Reset last_well_date if keyword replaced with another keyword by user.
	 *
	 * @param Post_Tax          $post_tax Post ID.
	 * @param Data_Keyword|null $data_keyword Post keyword with source info.
	 * @param string|null       $keyword_manual Keywords from manual input field.
	 * @param bool              $reanalyze_everything Update action for current post and other active posts with same keywords (before and after update).
	 * @param bool              $changed_by_user Keyword changed by user action, not in automated way (true: reset last well date).
	 * @return bool
	 */
	public function post_keywords_set( Post_Tax $post_tax, ?Data_Keyword $data_keyword, ?string $keyword_manual = null, bool $reanalyze_everything = true, bool $changed_by_user = false ) : bool {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( sprintf( '%s (%s) (%s) (%s) (%d)', __METHOD__, (string) $post_tax, (string) $data_keyword, $keyword_manual, $reanalyze_everything ) );
		$keyword   = ! is_null( $data_keyword ) ? $data_keyword->get_keyword() : null;
		$source_id = ! is_null( $data_keyword ) ? $data_keyword->get_source_id() : null;

		if ( ! is_null( $keyword ) && strlen( $keyword ) > 191 ) {
			$keyword = function_exists( 'mb_substr' ) ? mb_substr( $keyword, 0, 191 ) : substr( $keyword, 0, 191 );
		}

		if ( $post_tax->exists() && $post_tax->is_tax_or_published() ) { // only for existing and published posts.
			$position                  = null; // position value for newly set keyword.
			$keyword_old               = $this->post_keyword_get( $post_tax );
			$update_same_keyword_posts = $reanalyze_everything && $keyword_old !== $keyword;

			// need to reanalyze posts with same keyword as current (using keyword before update).
			$posts_reanalyze_old = $update_same_keyword_posts ? Ahrefs_Seo_Advisor::get()->find_active_pages_with_same_keyword( $post_tax ) : [];

			if ( ! is_null( $keyword ) ) {
				// try to update position with fresh value, if exists in cached data.
				$position = $this->load_position_from_cache( $post_tax, $keyword );
			}
			$fields = [
				'keyword'              => $keyword,
				'position'             => $position,
				'position_need_update' => is_null( $position ) ? 1 : 0,
				'keywords_need_update' => is_null( $keyword ) ? 1 : 0,
				'kw_source'            => Sources::validate_db_source( $source_id ),
			];
			$format = [ '%s', '%f', '%d', '%d', '%s' ];
			if ( Sources::is_source_imported( $source_id ) ) { // reset 'is approved' for imported keywords.
				$fields['is_approved_keyword'] = false;
				$format[]                      = '%d';
			}
			if ( ! is_null( $keyword_manual ) ) {
				$fields['keyword_manual'] = $keyword_manual;
				$format[]                 = '%s';
			}
			$query_result = $wpdb->update(
				$wpdb->ahrefs_content,
				$fields,
				$post_tax->as_where_array(),
				$format,
				$post_tax->as_where_format()
			);
			if ( 0 === $query_result ) {
				$this->maybe_add_post_and_retry_query( $post_tax, $wpdb->last_query );
			} elseif ( false === $query_result ) {
				$this->post_keywords_set_updated( $post_tax );
				Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Query failed (keywords set) (%s) (%s) (%s) (%s)', (string) wp_json_encode( func_get_args() ), (string) wp_json_encode( $fields ), $wpdb->last_query, $wpdb->last_error ) ) ); // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
			}
			if ( $changed_by_user && ( $keyword_old !== $keyword ) ) { // user changed the keyword: reset last well date.
				Ahrefs_Seo_Db_Helper::set_last_well_date( $post_tax, false );
			}
			// update is_duplicated for old and new keywords.
			if ( ( '' === $keyword ) || is_null( $keyword ) ) {
				( new Duplicated_Keywords() )->set_not_duplicated( $post_tax );
			} else {
				( new Duplicated_Keywords() )->fill_duplicated_for_keyword( (int) $post_tax->get_snapshot_id(), $keyword );
			}
			if ( ! is_null( $keyword_old ) && ( '' !== $keyword_old ) && ( $keyword_old !== $keyword ) ) {
				( new Duplicated_Keywords() )->fill_duplicated_for_keyword( (int) $post_tax->get_snapshot_id(), $keyword_old );
			}

			$content_audit = new Content_Audit_Current( $post_tax->get_snapshot_id() );
			if ( is_null( $position ) && $reanalyze_everything ) {
				// update position immediately using fast update only when reanalyze_everything required. Because this additional API call may be a reason of possible rate error.
				$content_audit->update_post_info_position( [ $post_tax ], true );
			}
			if ( $reanalyze_everything ) {
				// update recommended action.
				$content_audit->reanalyze_post( $post_tax );
			}

			// need to reanalyze posts with same keyword as current (using keyword after update).
			$posts_reanalyze_new = $update_same_keyword_posts && '' !== (string) $keyword ? Ahrefs_Seo_Advisor::get()->find_active_pages_with_same_keyword( $post_tax ) : [];

			$posts_reanalyze = array_merge( $posts_reanalyze_old ?? [], $posts_reanalyze_new ?? [] );

			if ( ! empty( $posts_reanalyze ) ) {
				$posts_reanalyze = array_unique( $posts_reanalyze );
				foreach ( $posts_reanalyze as $post_tax ) {
					// update recommended action.
					$content_audit->reanalyze_post( $post_tax );
				}
			}

			// if this is current snapshot and new snapshot exists: update it with cached suggestions (do not do request to API) and current keyword.
			$new_snapshot_id = ( new Content_Audit() )->get_snapshot_id();
			if ( ! is_null( $new_snapshot_id ) && ( new Snapshot() )->has_current_and_new_snapshots() && $post_tax->get_snapshot_id() !== $new_snapshot_id ) {
				$post_tax_kw = Post_Tax_With_Keywords::create_from( $post_tax )->load_from_db(); // load existing cached suggestions.
				$post_tax_kw->set_snapshot_id( $new_snapshot_id ); // assign new snapshot.
				$this->set_cached_suggestions( $post_tax_kw ); // and save to new snapshot.
				// also update everything in 'new' snapshot.
				$this->post_keywords_set( $post_tax->set_snapshot_id( $new_snapshot_id ), $data_keyword, $keyword_manual, $reanalyze_everything );
			}
			$snapshot_id = $post_tax->get_snapshot_id();
			if ( ! is_null( $snapshot_id ) ) {
				( new Events() )->on_keyword_changed( $snapshot_id );
			}

			return true;
		}
		return false;
	}

	/**
	 * Check, add new inactive post to content table and retry initial sql query.
	 *
	 * Newly added posts or initially inactive posts are missing at the content table.
	 * Must add post before use it, otherwise update keywords or cached suggestions do not work.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param string   $sql SQL query.
	 *
	 * @return void
	 */
	private function maybe_add_post_and_retry_query( Post_Tax $post_tax, string $sql ) : void {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( __METHOD__ . ' ' . (string) $post_tax . " ($sql)" );
		$error = $wpdb->last_error;
		if ( $error ) { // is there any error?
			// maybe this post was not added to content table at all?
			if ( is_null( $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->ahrefs_content} WHERE post_id = %d AND snapshot_id = %d AND taxonomy = %s LIMIT 1", $post_tax->get_post_id(), $post_tax->get_snapshot_id(), $post_tax->get_taxonomy() ) ) ) ) {
				Ahrefs_Seo_Data_Content::get()->add_post_as_added_since_last( $post_tax->get_post_id(), $post_tax->get_taxonomy() );

				// retry query.
				if ( '' !== $sql && 0 === $wpdb->query( $sql ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- we already tried to execute this sql code before, this time we just retry it.
					Ahrefs_Seo::notify( new Exception( sprintf( 'SQL query failed again (%s) (%s) (%s)', $sql, $error, $wpdb->last_error ) ) );
				}
			} else {
				Ahrefs_Seo::notify( new Exception( sprintf( 'SQL query failed (%s) (%s)', $sql, $error ) ) );
			}
		}
	}

	/**
	 * Create keywords for single post/term.
	 * Wrapper with delay around Keywords_Search calls.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param int      $keywords_limit Keywords limit.
	 * @return Data_Content_Storage|null Keywords for the post Data_Content_Storage or null.
	 */
	private function create_single_post_keywords( Post_Tax $post_tax, int $keywords_limit = 10 ) : ?Data_Content_Storage {
		if ( function_exists( 'wp_raise_memory_limit' ) ) {
			wp_raise_memory_limit( 'admin' );
		}
		unset( $this->all_keywords );
		$this->maybe_do_a_pause();
		// run research.
		return ( new Keywords_Search( $post_tax, $keywords_limit ) )->get_all_keywords();
	}

	/**
	 * Do a minimal delay between requests.
	 * Used to prevent 504 errors.
	 */
	private function maybe_do_a_pause() : void {
		$time_since = microtime( true ) - $this->last_query_time;
		if ( $time_since < self::KEY_MIN_DELAY && ! defined( 'AHREFS_SEO_IGNORE_DELAY' ) ) {
			Ahrefs_Seo::usleep( intval( ceil( self::KEY_MIN_DELAY - $time_since ) * 1000000 ) );
		}
		$this->last_query_time = microtime( true );
	}

	/**
	 * Get cached details for post
	 *
	 * @param Post_Tax $post_tax Post or term.
	 *
	 * @return Post_Tax_With_Keywords
	 */
	protected function get_cached_detail_for_post( Post_Tax $post_tax ) : Post_Tax_With_Keywords {
		$result = Post_Tax_With_Keywords::create_from( $post_tax );
		if ( $post_tax->is_tax_or_published() ) { // only for existing and published posts or taxonomy items.
			$result->load_from_db();
		}
		return $result;
	}

	/**
	 * Set cached suggestions
	 *
	 * @param Post_Tax_With_Keywords $post_tax_kw Post_tax_kw instance.
	 * @return void
	 */
	private function set_cached_suggestions( Post_Tax_With_Keywords $post_tax_kw ) : void {
		global $wpdb;
		$keywords          = $post_tax_kw->get_keywords();
		$keywords2         = $post_tax_kw->get_keywords2();
		$keywords_imported = $post_tax_kw->get_keywords_imported();
		$keywords2_low_len = $post_tax_kw->get_keywords2_low_len();
		$keywords_pos      = $post_tax_kw->get_keywords_pos();
		$keyword_source    = Sources::validate_db_source( $post_tax_kw->get_keyword_source() );
		if ( isset( $keywords['error'] ) ) {
			unset( $keywords['error'] ); // it makes no sense to cache errors.
		}
		$sql = $wpdb->prepare(
			"UPDATE {$wpdb->ahrefs_content} SET kw_gsc = %s, kw_idf = %s, kw_imported = %s, kw_pos = %s, kw_low = %d, kw_source = %s, updated=updated WHERE post_id = %d AND snapshot_id = %d AND taxonomy = %s", // do not change 'updated' value.
			(string) wp_json_encode( $keywords ),
			(string) wp_json_encode( $keywords2 ),
			(string) wp_json_encode( $keywords_imported->as_array() ),
			(string) wp_json_encode( $keywords_pos ),
			$keywords2_low_len,
			Sources::validate_db_source( $keyword_source ),
			$post_tax_kw->get_post_id(),
			$post_tax_kw->get_snapshot_id(),
			$post_tax_kw->get_taxonomy()
		);
		if ( 0 === $wpdb->query( $sql ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$this->maybe_add_post_and_retry_query( $post_tax_kw, $sql );
		}
	}

	/**
	 * Get current keyword and keywords data from GSC or TF-IDF or import from SEO plugins.
	 * Update cached values.
	 *
	 * @param Post_Tax[] $post_taxes Post ID list.
	 * @param int|null   $limit Limit.
	 * @param bool       $without_totals Do not make additional query for total values.
	 * @param bool       $skip_gsc_errors Do not return error in result.
	 * @param bool       $load_imported_info Load metrics for imported keywords too.
	 *
	 * @return Post_Tax_With_Keywords[]
	 */
	public function get_full_detail_for_posts( array $post_taxes, ?int $limit = null, bool $without_totals = false, bool $skip_gsc_errors = false, bool $load_imported_info = false ) : array {
		$v    = func_get_args();
		$v[0] = Post_Tax::id( $post_taxes );
		Ahrefs_Seo::breadcrumbs( sprintf( '%s %s', __METHOD__, (string) wp_json_encode( $v ) ) );
		unset( $v );
		$start_date = '';
		$end_date   = '';
		$this->get_time_period_keywords( $start_date, $end_date );

		$results          = [];
		$requests         = [];
		$request_keywords = [];

		foreach ( $post_taxes as $post_tax ) {
			$post_tax_kw = Post_Tax_With_Keywords::create_from( $post_tax )->load_from_db();
			if ( $post_tax->exists() && $post_tax->is_tax_or_published() && ( '' !== $post_tax->get_url() ) ) { // only for existing and published posts or terms with nonempty url.
				$country_code = $post_tax->get_country_code();
				$url          = apply_filters( 'ahrefs_seo_search_traffic_url', $post_tax_kw->get_url() );

				// fill tf-idf keywords.
				$post_tax_kw->set_keywords2( $this->create_single_post_keywords( $post_tax_kw, 10 ) ); // do not apply $limit parameter here.
				// fill imported keywords.
				$this->fill_imported_keywords( $post_tax_kw );
				// fill requests for individual keyword metrics.
				if ( $this->analytics->is_gsc_set() ) {
					if ( ! is_null( $post_tax_kw->get_keyword_current() ) && ( '' !== $post_tax_kw->get_keyword_current() ) ) { // current keyword is not empty.
						$request_keywords[] = new Data_Keyword( $post_tax_kw->get_keyword_current(), null, null, $url, $country_code );
					}
					if ( $load_imported_info ) { // load position of imported keywords too.
						$imported = $post_tax_kw->get_keywords_imported();
						if ( $imported->get_keywords() ) {
							foreach ( $imported->get_keywords() as $item ) {
								$request_keywords[] = $item->set_url( $url );
							}
						}
						unset( $imported, $item );
					}

					$post_tax_string              = (string) $post_tax_kw;
					$requests[ $post_tax_string ] = new Data_Keyword( '', null, null, $url, $country_code ); // will load suggestions from GSC.
				} else {
					$post_tax_kw->set_error_message( __( 'Google Search Console is not connected.', 'ahrefs-seo' ) );
				}

				// save to cache before make any calls to GSC API.
				$this->set_cached_suggestions( $post_tax_kw );
			} else {
				$post_tax_kw->set_error_message( __( 'This page cannot be found. It is possible that you’ve archived the page or changed the page ID. Please reload the page & try again.', 'ahrefs-seo' ) );
			}

			$results[ (string) $post_tax_kw ] = $post_tax_kw; // results without fresh GSC data.
		}
		// make single call for all pending urls.
		if ( ! empty( $requests ) ) {
			$clicks = $this->analytics->get_clicks_and_impressions_by_urls( $requests, $start_date, $end_date, $limit, $without_totals, $request_keywords );
			// update results for each post id.
			foreach ( $requests as $post_tax_string => $_ ) {
				$clicks_of_url = is_array( $clicks ) ? ( $clicks[ $post_tax_string ] ?? null ) : null;
				if ( ! empty( $clicks_of_url ) ) {
					$results[ $post_tax_string ]->set_keywords_pos( $clicks_of_url['kw_pos'] ?? [] );
					unset( $clicks_of_url['kw_pos'] );

					$results[ $post_tax_string ]->set_keywords( $clicks_of_url );
					if ( ! empty( $clicks_of_url['error'] ) ) {
						if ( $clicks_of_url['error'] instanceof Exception ) {
							$clicks_of_url['error'] = $clicks_of_url['error']->getMessage();
						}
						if ( is_string( $clicks_of_url['error'] ) ) {
							$results[ $post_tax_string ]->set_error_message( $clicks_of_url['error'] );
						}
					}
					// todo: save organic traffic in AHREFS_SEO_NO_GA mode.
				} elseif ( ! $skip_gsc_errors ) {
					$error = $this->analytics->get_message();
					if ( $error ) {
						$results[ $post_tax_string ]->set_error_message( $error );
					}
				}
				// set fresh cached values.
				$this->set_cached_suggestions( $results[ $post_tax_string ] );
			}
		}
		return $results;
	}

	/**
	 * Fill keyword row using cached GSC results
	 *
	 * @param array                                                       $keyword_row {
	 *                                                           Keyword row data.
	 *
	 *     @type string $query
	 *     @type float $pos
	 *     @type mixed $clicks
	 *     @type mixed $impr
	 * }
	 * @param array                                                       $cached_results Cached results.
	 * @param array<array{query:string, clicks:int, pos:float, impr:int}> $cached_kw_pos Additional cached results.
	 * @return void
	 */
	private function fill_using_cached_gsc_results( array &$keyword_row, array $cached_results, array $cached_kw_pos ) : void {
		$keyword        = $keyword_row[1];
		$cached_results = $cached_results + $cached_kw_pos;
		if ( ! empty( $cached_results ) && ! is_null( $keyword ) ) {
			foreach ( $cached_results as $item ) {
				if ( isset( $item['query'] ) && 0 === strcasecmp( $keyword, $item['query'] ) ) {
					$keyword_row[3] = $item['pos'] ?? null;
					$keyword_row[4] = $item['clicks'] ?? null;
					$keyword_row[5] = $item['impr'] ?? null;
					break;
				}
			}
		}
	}

	/**
	 * Get all suggestions (current keyword + imported, gsc, tf-idf keywords).
	 * Load and fill metrics for current keyword and imported keywords if not use cached data.
	 * Return the same data, as 'keywords-list' template expected.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param bool     $use_cached_data Use cached data.
	 * @return array<string, mixed>
	 */
	public function get_suggestions( Post_Tax $post_tax, bool $use_cached_data = false ) : array {
		$errors   = null;
		$keywords = [];
		if ( ! $use_cached_data ) {
			$data        = $this->get_full_detail_for_posts( [ $post_tax ], 10, false, false, true );
			$post_tax_kw = $data[ (string) $post_tax ];
		} else {
			$post_tax_kw = $this->get_cached_detail_for_post( $post_tax );
			if ( empty( $post_tax_kw->get_keywords2() ) ) { // always fill TF-IDF keywords, even this is cached results.
				$post_tax_kw->set_keywords2( $this->create_single_post_keywords( $post_tax_kw, 10 ) );
			}
			if ( ! count( $post_tax_kw->get_keywords_imported()->get_keywords() ) ) { // always fill imported keywords, even this is cached results.
				$this->fill_imported_keywords( $post_tax_kw );
			}
		}
		$total_clicks           = 0;
		$total_impr             = 0;
		$keyword_manual         = $post_tax_kw->get_keyword_manual();
		$data_keywords          = $post_tax_kw->get_keywords();
		$data_keywords2         = $post_tax_kw->get_keywords2();
		$data_kw_pos            = $post_tax_kw->get_keywords_pos() ?? [];
		$data_keywords_imported = $post_tax_kw->get_keywords_imported()->get_keywords();
		$keyword                = $post_tax_kw->get_keyword_current() ?? '';
		$is_approved            = $post_tax_kw->get_is_keyword_approved() ?? false;
		$source_id              = $post_tax_kw->get_keyword_source();
		$item_selected          = false;
		$cached_positions       = is_array( $data_keywords ) && ! empty( $data_keywords['result'] ) ? $data_keywords['result'] : [];

		// update current target keyword if it pulled from imported keywords: keyword input field must show actual keyword.
		if ( ( '' !== $keyword ) && count( $data_keywords_imported ) && is_string( $source_id ) && in_array( $source_id, [ Sources::SOURCE_YOASTSEO, Sources::SOURCE_AIOSEO, Sources::SOURCE_RANKMATH ], true ) ) {
			$sources = [];
			foreach ( $data_keywords_imported as $item ) {
				$sources[ $item->get_source_id() ] = $item->get_keyword();
			}
			if ( isset( $sources[ $source_id ] ) && ( 0 !== strcasecmp( $sources[ $source_id ], $keyword ) ) ) {
				$keyword = $sources[ $source_id ];
			}
		}

		if ( count( $data_keywords_imported ) ) {
			foreach ( $data_keywords_imported as $item ) {
				$word  = $item->get_keyword();
				$badge = $item->get_source_id();
				$row   = [ false, $word, $badge, null, '-', '-' ];
				$this->fill_using_cached_gsc_results( $row, $cached_positions, $data_kw_pos );
				$keywords[] = $row;
			}
		}

		if ( ! empty( $data_keywords ) && ! empty( $data_keywords['result'] ) ) {
			$data_keywords['result'] = array_slice( $data_keywords['result'], 0, 10 ); // use only top 10 items, ordered by clicks.
			$total_clicks            = ! empty( $data_keywords['total_clicks'] ) ? $data_keywords['total_clicks'] : 1;
			$total_impr              = ! empty( $data_keywords['total_impr'] ) ? $data_keywords['total_impr'] : 1;
			foreach ( $data_keywords['result'] as $item ) {
				if ( isset( $item['query'] ) && is_string( $item['query'] ) ) {
					$word       = $item['query'];
					$keywords[] = [ false, $word, Sources::SOURCE_GSC, $item['pos'], $item['clicks'], $item['impr'] ];
				}
			}
		}
		if ( ! empty( $data_keywords2 ) ) {
			if ( 0 === $total_clicks ) {
				$total_clicks = 1;
			}
			if ( 0 === $total_impr ) {
				$total_impr = 1;
			}
			$data_keywords2 = array_slice( $data_keywords2, 0, 5 ); // use only top 5 items.
			foreach ( $data_keywords2 as $item ) {
				$word = $item->get_keyword();
				$row  = [ false, $word, Sources::SOURCE_TF_IDF, null, '-', '-' ];
				$this->fill_using_cached_gsc_results( $row, $cached_positions, $data_kw_pos );
				$keywords[] = $row;
			}
		}
		$item_found = ( '' !== $keyword ) && array_sum(
			array_map(
				function( $item ) use ( $keyword ) {
					return ( 0 === strcasecmp( $item[1], $keyword ) ) ? 1 : 0;
				},
				$keywords
			)
		) > 0;

		$badge = $is_approved ? Sources::SOURCE_MANUAL : Sources::SOURCE_EXT_SAVED;
		// do we have currently selected item somewhere? Keyword is set, but we did not find it at GSC or TF-IDF suggestions and it is not a manual keyword too...
		if ( ( '' !== $keyword ) && ! $item_found && ( is_null( $keyword_manual ) || 0 !== strcasecmp( $keyword_manual, $keyword ) ) ) {
			// replace manual keyword by selected keyword.
			$keyword_manual = $keyword;
		}

		if ( '' !== $keyword_manual && ! is_null( $keyword_manual ) ) {
			// do we already have same keyword as keyword_manual in list? Then skip keyword manual.
			if ( 0 === count(
				array_filter(
					$keywords,
					function( $row ) use ( $keyword_manual ) {
						return 0 === strcasecmp( $keyword_manual, $row[1] ); }
				)
			) ) {
					$item = [ false, $keyword_manual ?? '', $badge, null, '-', '-' ];
					$this->fill_using_cached_gsc_results( $item, $cached_positions, $data_kw_pos );
					array_unshift( $keywords, $item );
			}
		}

		// choose selected keyword.
		if ( '' !== $keyword ) {
			// try to select same source.
			if ( ! is_null( $source_id ) ) {
				foreach ( $keywords as $k => $row ) {
					if ( ( $source_id === $row[2] ) && ( 0 === strcasecmp( $row[1], $keyword ) ) ) {
						$keywords[ $k ][0] = true;
						$item_selected     = true;
						break;
					}
				}
			}
			if ( ! $item_selected ) {
				foreach ( $keywords as $k => $row ) {
					if ( 0 === strcasecmp( $row[1], $keyword ) ) {
						$keywords[ $k ][0] = true;
						$item_selected     = true;
						break;
					}
				}
			}
		}
		if ( empty( $keywords ) ) {
			// show errors.
			if ( ! empty( $post_tax_kw->get_error_message() ) ) {
				$errors = $post_tax_kw->get_error_message();
			}
		}

		if ( ! $use_cached_data && ! empty( $post_tax_kw->get_error_message() ) ) {
			$errors = $post_tax_kw->get_error_message();
		}
		return [
			'post_id'      => (string) $post_tax_kw, // post_tax_string.
			'keyword'      => $keyword,
			'keywords'     => $keywords,
			'total_clicks' => $total_clicks,
			'total_impr'   => $total_impr,
			'errors'       => $errors,
			'post_tax'     => $post_tax_kw,
			'imported'     => Sources::is_source_imported( $source_id ),
		];
	}

	/**
	 * Return current keyword and other GSC keywords with positions 4-20 sorted by clicks.
	 *
	 * @since 0.9.8
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param bool     $use_cached_data Use cached data.
	 * @return Post_Tax_With_Keywords|null
	 */
	public function get_other_keywords( Post_Tax $post_tax, bool $use_cached_data = false ) : ?Post_Tax_With_Keywords {
		// todo: cache data.
		$data = $this->get_full_detail_for_posts( [ $post_tax ], 1000, true, false, false );
		return array_shift( $data );
	}

	/**
	 * Get time period
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return void
	 */
	public function get_time_period_keywords( string &$start_date, string &$end_date ) : void {
		$start_date = date( 'Y-m-d', strtotime( '- 3 month' ) );
		$end_date   = date( 'Y-m-d' );
	}

	/**
	 * Load position of keyword.
	 *
	 * @param Post_Tax[] $post_taxes Items to load current keyword position for.
	 * @return Post_Tax_With_Keywords[]
	 */
	public function load_position_value_fast( array $post_taxes ) : array {
		$post_taxes_kw = [];
		foreach ( $post_taxes as $post_tax ) {
			$post_taxes_kw[] = Post_Tax_With_Keywords::create_from( $post_tax )->load_from_db();
		}
		$url_with_keywords = [];
		/** @var Post_Tax_With_Keywords[] $url_to_item */
		$url_to_item = [];
		array_walk(
			$post_taxes_kw,
			function( Post_Tax_With_Keywords &$post_tax_kw ) use ( &$url_with_keywords, &$url_to_item ) {
				$url             = $post_tax_kw->get_url();
				$keyword_current = $post_tax_kw->get_keyword_current();
				if ( ! empty( $keyword_current ) && ! empty( $url ) ) {
					$url_with_keywords[]                                 = new Data_Keyword( $keyword_current, null, null, $url, $post_tax_kw->get_country_code() );
					$url_to_item[ array_key_last( $url_with_keywords ) ] = &$post_tax_kw; // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.array_key_lastFound -- polyfill used for older php versions.
					$post_tax_kw->set_position( -1 ); // set error, will overwrite it with value from API later.
				} else {
					$post_tax_kw->set_position( Ahrefs_Seo_Data_Content::POSITION_MAX );
				}
			}
		);
		if ( $url_with_keywords ) { // have something to load?
			$rows = $this->analytics->get_position_fast( $url_with_keywords );
			if ( $rows ) {
				// one url can have many (current + imported) keyword entries, group them by url before assign.
				$by_url = [];
				foreach ( $rows as $data_keyword ) {
					if ( is_null( $data_keyword->get_error() ) ) {
						$url = $data_keyword->get_url();
						if ( ! isset( $by_url[ $url ] ) ) {
							$by_url[ $url ] = [];
						}
						$by_url[ $url ][] = $data_keyword;
					}
				}
				foreach ( $post_taxes_kw as $post_tax_kw ) {
					$url = $post_tax_kw->get_url();
					if ( isset( $by_url[ $url ] ) ) {
						$post_tax_kw->set_keywords_pos( $by_url[ $url ] );
					}
					// check keyword.
					$_keyword_current = $post_tax_kw->get_keyword_current();
					if ( ! is_null( $_keyword_current ) ) {
						$position = $this->load_position_from_caches_raw( $_keyword_current, $post_tax_kw->get_keywords(), $post_tax_kw->get_keywords_pos() );
						if ( ! is_null( $position ) ) {
							$post_tax_kw->set_position( floatval( $position ) );
						}
					}
				}
			}
		}

		return $post_taxes_kw;
	}

	/**
	 * Return position for URL and current keyword.
	 * Load top 10 results for the page and result for current keyword.
	 *
	 * @param Post_Tax[] $post_taxes Posts or terms list.
	 * @return Post_Tax_With_Keywords[]
	 */
	public function load_position_value( array $post_taxes ) : array {
		$post_taxes_with_kw = $this->get_full_detail_for_posts( $post_taxes, 10, true, true );
		foreach ( $post_taxes_with_kw as &$post_tax_kw ) {
			$current_keyword_lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $post_tax_kw->get_keyword_current() ?? '' ) : strtolower( $post_tax_kw->get_keyword_current() ?? '' );
			$keywords              = $post_tax_kw->get_keywords();
			if ( ! empty( $keywords ) && ! empty( $keywords['result'] ) ) {
				foreach ( $keywords['result'] as $item ) {
					if ( ! empty( $item['query'] ) && ( function_exists( 'mb_strtolower' ) ? mb_strtolower( $item['query'] ) : strtolower( $item['query'] ) ) === $current_keyword_lower ) {
						$post_tax_kw->set_position( floatval( $item['pos'] ) )->set_keywords_pos( [ $item ] );
						break;
					}
				}
			}
		}
		return $post_taxes_with_kw;
	}

	/**
	 * Load position of keyword from GSC cache & GSG current keyword position.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param string   $current_keyword Current keyword.
	 * @return float|null Position or null.
	 */
	public function load_position_from_cache( Post_Tax $post_tax, string $current_keyword ) : ?float {
		$cached = Post_Tax_With_Keywords::create_from( $post_tax )->load_from_db();
		$kw_gsc = $cached->get_keywords();
		$kw_pos = $cached->get_keywords_pos();
		return $this->load_position_from_caches_raw( $current_keyword, $kw_gsc, $kw_pos );
	}

	/**
	 * Load position from raw cache (gsc + imported arrays)
	 *
	 * @since 0.8.8
	 *
	 * @param string                                                           $current_keyword Search positions for this keyword.
	 * @param array|null                                                       $kw_gsc Raw GSC data.
	 * @param array<array{query:string, clicks:int, pos:float, impr:int}>|null $kw_pos Raw imported (kw_pos) data.
	 * @return float|null Null if keyword not found in both caches.
	 */
	protected function load_position_from_caches_raw( string $current_keyword, ?array $kw_gsc, ?array $kw_pos ) : ?float {
		$result  = null;
		$current = new Data_Keyword( $current_keyword );
		if ( is_array( $kw_gsc ) && ! empty( $kw_gsc['result'] ) && is_array( $kw_gsc['result'] ) ) {
			foreach ( $kw_gsc['result'] as $item ) {
				if ( isset( $item['query'] ) && $current->is_same_keyword( $item['query'] ) ) {
					$result = isset( $item['pos'] ) ? floatval( $item['pos'] ) : null;
					break;
				}
			}
		}
		if ( is_null( $result ) && is_array( $kw_pos ) ) {
			foreach ( $kw_pos as $item ) {
				if ( $current->is_same_keyword( $item['query'] ) ) {
					$result = floatval( $item['pos'] );
					break;
				}
			}
		}
		return $result;
	}

	/**
	 * Import keywords for the article
	 *
	 * @since 0.8.8
	 *
	 * @param Post_Tax_With_Keywords $post_tax_kw Article to import for.
	 * @return void
	 */
	protected function fill_imported_keywords( Post_Tax_With_Keywords &$post_tax_kw ) : void {
		$list = $this->get_assigned_keyword( $post_tax_kw );
		$post_tax_kw->set_keywords_imported( new Data_Keywords( $list ) );
	}

	/**
	 * Get assigned keywords (imported from third party plugins) for the article.
	 *
	 * @since 0.8.8
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return Data_Keyword[] List of Data_Keyword items, filled with assigned keywords.
	 */
	protected function get_assigned_keyword( Post_Tax $post_tax ) : array {
		$results = array_map(
			function( Assigned_Keyword $source ) use ( $post_tax ) {
				return $source->get_assigned_keyword( $post_tax );
			},
			Sources::get()->get_keywords_sources()
		);
		return array_filter( $results ); // If no callback is supplied, all empty entries of array will be removed.
	}

	/**
	 * Find suggested keyword using results from GSC, TF-IDF and imported keywords.
	 *
	 * @since 0.8.8
	 *
	 * @param array|null          $keywords Recommended by GSC keywords.
	 * @param array|null          $keywords2 Recommended by TF-IDF keywords.
	 * @param Data_Keyword[]|null $keywords_imported Keywords imported from third-party plugins.
	 * @param string|null         $last_source_id Last source id, used for prioritize selected of import sources over others.
	 * @return Data_Keyword|null
	 */
	public function find_suggested_keyword( ?array $keywords, ?array $keywords2, ?array $keywords_imported, ?string $last_source_id = null ) : ?Data_Keyword {
		$result         = null;
		$last_source_id = Sources::is_source_imported( $last_source_id ) ? $last_source_id : null; // reset non-imported sources.

		// using imported keywords.
		if ( ! is_null( $keywords_imported ) && count( $keywords_imported ) ) {
			if ( ! is_null( $last_source_id ) ) { // prefer same source if exists in newly imported list.
				foreach ( $keywords_imported as $item ) {
					if ( $item->get_source_id() === $last_source_id && '' !== $item->get_keyword() ) {
						$result = $item;
						break;
					}
				}
			}
			if ( is_null( $result ) ) { // then try with any available imported source.
				foreach ( $keywords_imported as $item ) {
					if ( '' !== $item->get_keyword() ) {
						$result = $item;
						break;
					}
				}
			}
		}

		if ( is_null( $result ) && ! empty( $keywords ) && ! empty( $keywords['result'] ) ) {
			$best_results = $keywords['result'];
			// default order: by clicks.
			if ( isset( $best_results[0] ) && isset( $best_results[0]['clicks'] ) ) {
				if ( $best_results[0]['clicks'] > 0 ) { // items with 90-100% clicks of top-1 result, but at least -1 click.
					$min_clicks   = min( $keywords['result'][0]['clicks'] * 0.9, max( $keywords['result'][0]['clicks'] - 1, 1 ) );
					$best_results = array_filter(
						$best_results,
						function( $row ) use ( $min_clicks ) {
							return $row['clicks'] >= $min_clicks;
						}
					);
				}
			}
			// choose best position.
			usort(
				$best_results,
				function( $a, $b ) {
					$pos_a = $a['pos'] ?? 10000;
					$pos_b = $b['pos'] ?? 10000;
					return $pos_a - $pos_b;
				}
			);
			if ( isset( $best_results[0] ) && isset( $best_results[0]['query'] ) ) {
				$result = new Data_Keyword( $best_results[0]['query'], Sources::SOURCE_GSC );
			}
		}
		if ( is_null( $result ) && ! empty( $keywords2 ) ) {
			$keyword = array_map(
				function( $item ) {
					return $item->get_keyword();
				},
				array_slice( $keywords2, 0, 1 ) // top 1 keyword.
			);
			if ( count( $keyword ) ) {
				$result = new Data_Keyword( $keyword[0], Sources::SOURCE_TF_IDF );
			}
		}
		return $result;
	}


}
