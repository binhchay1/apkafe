<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Content_Tips\Events;
use ahrefs\AhrefsSeo\Messages\Message;
use ahrefs\AhrefsSeo\Options\Countries;
use ahrefs\AhrefsSeo\Options\Option_Taxonomy;
use ahrefs\AhrefsSeo\Options\Settings_Scope;
use WP_Post;
/**
 * What is a current snapshot?
 * - it is the latest snapshot with 'current' status if exists;
 * - otherwise it is any latest snapshot (can be 'old' or 'new');
 * - if no snapshot records found (maybe corrupted DB?): create and initialize new snapshot.
 *
 * Update from previous versions (without snapshots, when no snapshots table exists before):
 * - create new snapshot with ID = 1 and assign this snapshot ID to all existing content items.
 *
 * When new snapshot added:
 * - add all published items from selected post categories and/or pages with initial status ACTION4_ANALYZING_INITIAL;
 * - add the rest of published items with initial out-of-scope status ACTION4_OUT_OF_SCOPE_INITIAL.
 *
 * Then run content audit for newly created snapshot.
 *
 * Finally, when all items updated:
 * - calculate traffic median;
 * - update status of snapshot from 'new' to 'current' (and previous 'current' to 'old').
 *
 * How to determine, do we need to update items or not?
 * - if we have a snapshot with 'new' status - then we need to run update. Otherwise - no update needed.
 */
/**
 * Snapshot class.
 */
class Snapshot {

	const TRANSIENT_CREATE_NEW = 'ahrefs_seo_snapshot_new_create';
	const CACHE_GROUP          = 'ahrefs_seo_snapshot';
	const STATUS_NEW           = 'new';
	const STATUS_CURRENT       = 'current';
	const STATUS_OLD           = 'old';
	const STATUS_CANCELLED     = 'cancelled';
	/**
	 * Snapshot to show in UI, prefer 'current' status.
	 *
	 * @var int|null
	 */
	private $current_snapshot_id = null;
	/**
	 * Get current snapshot id.
	 * Create new snapshot if no snapshots available.
	 *
	 * @return int Snapshot ID.
	 */
	public function get_current_snapshot_id() {
		global $wpdb;
		if ( is_null( $this->current_snapshot_id ) ) {
			// get current snapshot.
			$result = $wpdb->get_var( $wpdb->prepare( "SELECT snapshot_id FROM {$wpdb->ahrefs_snapshots} WHERE snapshot_status = %s ORDER BY snapshot_id DESC LIMIT 1", self::STATUS_CURRENT ) );
			if ( is_null( $result ) ) { // try to get new (pending) or old snapshot.
				$result = $wpdb->get_var( "SELECT snapshot_id FROM {$wpdb->ahrefs_snapshots} ORDER BY snapshot_id DESC LIMIT 1" );
			}
			if ( is_null( $result ) ) { // try to repair last snapshot.
				$result = $this->try_to_repair_last_snapshot();
			}
			if ( is_null( $result ) ) { // create new snapshot.
				$result = $this->create_new_snapshot();
			}
			$this->current_snapshot_id = (int) $result;
		}
		return $this->current_snapshot_id;
	}
	/**
	 * There is no correct snapshot to show.
	 *
	 * @return bool
	 */
	public function current_snapshot_is_cancelled() {
		$info = $this->get_snapshot_info( $this->get_current_snapshot_id() );
		return self::STATUS_CANCELLED === $info['snapshot_status'];
	}
	/**
	 * Try to repair snapshots. Search max snapshot_id from content table and create new snapshot with this ID.
	 *
	 * @return int|null Snapshot ID or null if nothing found.
	 */
	private function try_to_repair_last_snapshot() {
		global $wpdb;
		$id = $wpdb->get_var( "SELECT MAX(snapshot_id) FROM {$wpdb->ahrefs_snapshots}" );
		if ( ! is_null( $id ) && intval( $id ) > 0 ) {
			$id = intval( $id );
			$wpdb->insert(
				$wpdb->ahrefs_snapshots,
				[
					'snapshot_id'     => $id,
					'snapshot_status' => self::STATUS_NEW,
				]
			);
			return ! empty( $wpdb->insert_id ) ? $wpdb->insert_id : null;
		}
		return null;
	}
	/**
	 * Create new snapshot, fill the content table with items.
	 * Note: return existing new snapshot if it exists.
	 *
	 * @param bool $is_scheduled_audit Is scheduled audit, false - manually started.
	 * @return int|null Newly or already created Snapshot ID or null if error.
	 */
	public function create_new_snapshot( $is_scheduled_audit = false ) {
		global $wpdb;
		while ( get_transient( self::TRANSIENT_CREATE_NEW ) ) {
			$snapshot_id = $this->get_new_snapshot_id();
			if ( ! is_null( $snapshot_id ) ) {
				return $snapshot_id;
			}
			Ahrefs_Seo::usleep( 50000 );
		}
		update_option( Ahrefs_Seo::OPTION_LAST_HASH, [] ); // reset last reported error if any.
		$snapshot_id = $this->get_new_snapshot_id();
		Ahrefs_Seo::breadcrumbs( __METHOD__ . ' new snapshot exists? ' . ( is_null( $snapshot_id ) ? 'NULL' : $snapshot_id ) );
		if ( ! get_transient( self::TRANSIENT_CREATE_NEW ) ) { // @phpstan-ignore-line
			set_transient( self::TRANSIENT_CREATE_NEW, true, 10 );
			if ( is_null( $snapshot_id ) ) {
				$wpdb->insert(
					$wpdb->ahrefs_snapshots,
					[
						'snapshot_status' => self::STATUS_NEW,
						'time_start'      => current_time( 'mysql' ),
						'snapshot_type'   => $is_scheduled_audit ? 'scheduled' : 'manual',
						'country'         => ( new Countries() )->get_country(),
					],
					[ '%s', '%s', '%s' ]
				);
				$snapshot_id = $wpdb->insert_id;
				if ( ! empty( $snapshot_id ) ) {
					wp_cache_delete( "median{$snapshot_id}", 'ahrefs_seo_audit' );
					$this->clean_cache();
					// fill the content table with new details.
					$this->fill_content_table( $snapshot_id );
					( new Ahrefs_Seo_Content_Settings() )->reset_scope_updated();
					Ahrefs_Seo_Cron::get()->start_tasks_content();
					// run cron content audit updates.
				} else {
					Ahrefs_Seo_Errors::save_message( 'general', __( 'Can not start new content audit. New snapshot is empty.', 'ahrefs-seo' ), Message::TYPE_ERROR );
					Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( 'New snapshot is empty' ) ); // do not translate.
					// recreate tables.
					Ahrefs_Seo_Db::create_table( (int) Ahrefs_Seo::CURRENT_TABLE_VERSION );
				}
			}
			delete_transient( self::TRANSIENT_CREATE_NEW );
			Content_Audit::audit_clean_pause(); // clean any previous pause and allow content audit run.
		}
		return $snapshot_id;
	}
	/**
	 * Get new snapshot ID, if exists.
	 *
	 * @return null|int 'new' snapshot ID if exists or null.
	 */
	public function get_new_snapshot_id() {
		global $wpdb;
		$result = wp_cache_get( 'new_id', self::CACHE_GROUP );
		if ( ! is_int( $result ) ) {
			$result = $wpdb->get_var( $wpdb->prepare( "SELECT snapshot_id FROM {$wpdb->ahrefs_snapshots} WHERE snapshot_status = %s LIMIT 1", self::STATUS_NEW ) );
			if ( is_null( $result ) && preg_match( "/Table.*?{$wpdb->ahrefs_snapshots}.*?doesn't exist/i", $wpdb->last_error ) ) {
				$last_error = $wpdb->last_error;
				// recreate tables.
				$success = Ahrefs_Seo_Db::create_table( (int) Ahrefs_Seo::CURRENT_TABLE_VERSION );
				Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Recreate tables as snapshots table non exists: %s on [%s] [%s]', $success ? 'success' : 'ERROR', $last_error, $wpdb->last_error ) ), 'Table not exists' );
				$result = $wpdb->get_var( $wpdb->prepare( "SELECT snapshot_id FROM {$wpdb->ahrefs_snapshots} WHERE snapshot_status = %s LIMIT 1", self::STATUS_NEW ) );
			}
			$result = ! is_null( $result ) ? (int) $result : null;
			wp_cache_set( 'new_id', $result, self::CACHE_GROUP, HOUR_IN_SECONDS );
		}
		return $result;
	}
	/**
	 * Fill contents table using initial settings (posts and pages) from wizard.
	 * Add posts using categories and pages as 'analyzing_initial'.
	 * Add other posts and pages as 'out_of_scope_initial'.
	 *
	 * @param int $new_snapshot_id Snapshot ID.
	 * @return void
	 */
	protected function fill_content_table( $new_snapshot_id ) {
		Ahrefs_Seo::breadcrumbs( __METHOD__ . (string) wp_json_encode( func_get_args() ) );
		$content  = Ahrefs_Seo_Data_Content::get();
		$posts_on = Settings_Scope::is_enabled_for_post_type( 'post' );
		$pages_on = Settings_Scope::is_enabled_for_post_type( 'page' );
		// 1. add pages using options.
		$page_ids = $pages_on ? Settings_Scope::get()->get_posts_id_checked( 'page' ) : [];
		if ( count( $page_ids ) ) {
			$params = [
				'post_type'           => 'page',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => true,
			];
			// include pages from $page_ids array only.
			$params['post__in'] = $page_ids;
			$this->add_posts_by_clause( $new_snapshot_id, $params, Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_INITIAL );
			// add other pages as out_of_scope_initial.
			unset( $params['post__in'] );
			$params['post__not_in'] = $page_ids;
			$this->add_posts_by_clause( $new_snapshot_id, $params, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL );
		} else {
			// add all pages as out_of_scope_initial.
			$params = [
				'post_type'           => 'page',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => true,
			];
			$this->add_posts_by_clause( $new_snapshot_id, $params, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL );
		}
		// 2. add posts using options.
		$categories = $posts_on ? Settings_Scope::get()->get_posts_categories_checked( 'post', 'category' ) : []; // array of id categories.
		if ( count( $categories ) ) {
			$params        = [
				'post_type'   => 'post',
				'post_status' => 'publish',
			];
			$params['cat'] = array_map( 'intval', $categories );
			$this->add_posts_by_clause( $new_snapshot_id, $params, Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_INITIAL );
			// add other posts as out_of_scope_initial.
			$params['category__not_in'] = array_map( 'intval', $categories );
			unset( $params['cat'] );
			$this->add_posts_by_clause( $new_snapshot_id, $params, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL );
		} else {
			// add all posts as out_of_scope_initial.
			$params = [
				'post_type'   => 'post',
				'post_status' => 'publish',
			];
			$this->add_posts_by_clause( $new_snapshot_id, $params, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL );
		}
		// 3. Products.
		if ( $content->products_exists() ) {
			$products_on = Settings_Scope::is_enabled_for_post_type( 'product' );
			if ( $products_on ) {
				// array of id product categories.
				$categories = array_map( 'intval', Settings_Scope::get()->get_posts_categories_checked( 'product', 'product_cat' ) );
				$params     = [
					'post_type'   => 'product',
					'post_status' => 'publish',
				];
				if ( ! empty( $categories ) ) {
					$params['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
						array(
					'taxonomy' => 'product_cat',
					'terms'    => $categories,
					'operator' => 'IN',
					),
					);
					$this->add_posts_by_clause( $new_snapshot_id, $params, Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_INITIAL );
				}
				// add other products as out_of_scope_initial.
				if ( ! empty( $categories ) ) {
					$params['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
						array(
					'taxonomy' => 'product_cat',
					'terms'    => $categories,
					'operator' => 'NOT IN',
					),
					);
				}
				$this->add_posts_by_clause( $new_snapshot_id, $params, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL );
			} else {
				// add all products as out_of_scope_initial.
				$params = [
					'post_type'   => 'product',
					'post_status' => 'publish',
				];
				$this->add_posts_by_clause( $new_snapshot_id, $params, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL );
			}
		}
		// 4. CPT.
		$settings                  = new Ahrefs_Seo_Content_Settings();
		$custom_post_types         = $settings->get_custom_post_types();
		$custom_post_types_enabled = $content->get_custom_post_types_enabled();
		$custom_post_types_enabled = array_intersect( $custom_post_types_enabled, array_keys( $custom_post_types ) ); // use enabled and currently active post types.
		foreach ( array_keys( $custom_post_types ) as $post_type ) {
			$params = [
				'post_type'   => $post_type,
				'post_status' => 'publish',
			];
			$this->add_posts_by_clause( $new_snapshot_id, $params, in_array( $post_type, $custom_post_types_enabled, true ) ? Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_INITIAL : Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL );
		}
		// 5. Categories.
		$categories = Settings_Scope::is_enabled_for_taxonomy( 'category' ) ? Settings_Scope::get()->get_posts_categories_checked( 'post', 'category' ) : [];
		$this->add_terms_by_clause( $new_snapshot_id, 'category', $categories );
		// 6. Product Categories.
		if ( $content->products_exists() ) {
			$categories = Settings_Scope::is_enabled_for_taxonomy( 'product_cat' ) ? Settings_Scope::get()->get_posts_categories_checked( 'product', 'product_cat' ) : [];
			$this->add_terms_by_clause( $new_snapshot_id, 'product_cat', $categories );
		}
		// 7. Post tags.
		if ( Option_Taxonomy::exists( 'post_tag' ) ) {
			$categories = Settings_Scope::is_enabled_for_taxonomy( 'post_tag' ) ? Helper_Content::get()->get_all_term_ids( 'post_tag' ) : [];
			$this->add_terms_by_clause( $new_snapshot_id, 'post_tag', $categories );
		}
	}
	/**
	 * Add new posts/pages to Content table using given parameters for get_posts() search call
	 *
	 * @param int                  $snapshot_id Snapshot ID.
	 * @param array<string, mixed> $params Parameters array for get_posts() call.
	 * @param string               $action Initial action.
	 * @return void
	 */
	private function add_posts_by_clause( $snapshot_id, array $params, $action ) {
		$args_str = (string) wp_json_encode( func_get_args() );
		Ahrefs_Seo::breadcrumbs( __METHOD__ . $args_str );
		global $wpdb;
		$results                    = [];
		$paged                      = 1;
		$limit                      = 100;
		$params['orderby']          = 'date'; // add the newest posts at the beginning.
		$params['order']            = 'DESC';
		$params['posts_per_page']   = $limit;
		$params['suppress_filters'] = true;
		$badge                      = substr( isset( $params['post_type'] ) ? $params['post_type'] : ( isset( $params['cat'] ) ? $params['cat'] : '...' ), 0, 20 );
		do {
			$params['paged'] = $paged++;
			/** @var WP_Post[] $data */
			$data = Helper_Content::get()->get_posts( $params );
			if ( ! empty( $data ) ) {
				$query         = "INSERT INTO {$wpdb->ahrefs_content} ( snapshot_id, post_id, action, badge, title, taxonomy, date_updated ) VALUES ";
				$values        = [];
				$place_holders = [];
				foreach ( $data as $post ) {
					if ( ! $post instanceof WP_Post ) {
						Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s(%s) not a WP_Post: %s', __METHOD__, $args_str, wp_json_encode( $post ) ) ) );
					}
					$post_id = (int) $post->ID;
					array_push( $values, $snapshot_id, $post_id, $action, $badge, $post->post_title, '', $post->post_date );
					$place_holders[] = '( %d, %d, %s, %s, %s, %s, %s )';
					$results[]       = $post_id;
				}
				$sql = $query . implode( ', ', $place_holders ) . $wpdb->prepare( ' ON DUPLICATE KEY UPDATE action = %s, total = NULL, organic = NULL, total_month = NULL, organic_month = NULL, backlinks = NULL', $action );
				$wpdb->query( $wpdb->prepare( "{$sql}", $values ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}
		} while ( count( $data ) === $limit ); // phpcs:ignore Squiz.PHP.DisallowSizeFunctionsInLoops.Found -- we increment count of $data.
		Ahrefs_Seo::breadcrumbs( __METHOD__ . ': ' . (string) wp_json_encode( $results ) );
	}
	/**
	 * Add new categories/terms to Content table using given parameters fot get_posts() search call.
	 * Add both, checked (as active) and not checked (as inactive) items.
	 *
	 * @since 0.8.0
	 *
	 * @param int            $snapshot_id Snapshot ID.
	 * @param string         $taxonomy Taxonomy.
	 * @param string[]|int[] $checked Checked terms ID list.
	 * @return void.
	 */
	private function add_terms_by_clause( $snapshot_id, $taxonomy, array $checked ) {
		Ahrefs_Seo::breadcrumbs( __METHOD__ . (string) wp_json_encode( func_get_args() ) );
		global $wpdb;
		$results = [];
		$paged   = 0;
		$limit   = 100;
		$checked = array_map( 'intval', $checked );
		$params  = [
			'taxonomy'   => $taxonomy,
			'fields'     => 'id=>name', // results are [term_id => name] pairs.
			'hide_empty' => false,
			'number'     => $limit,
		];
		$badge   = substr( $taxonomy, 0, 20 );
		if ( 'product_cat' === $badge ) {
			$badge = 'products';
		}
		do {
			$params['offset'] = $limit * $paged++;
			/** @var array<int,string> $data we query for [term id => name ] pairs. */
			$data = Helper_Content::get()->get_terms( $params );
			if ( count( $data ) ) {
				$query         = "INSERT INTO {$wpdb->ahrefs_content} ( snapshot_id, post_id, taxonomy, action, badge, title ) VALUES ";
				$values        = [];
				$place_holders = [];
				foreach ( $data as $term_id => $title ) {
					$action = in_array( (int) $term_id, $checked, true ) ? Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_INITIAL : Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL;
					array_push( $values, $snapshot_id, (int) $term_id, $taxonomy, $action, $badge, $title );
					$place_holders[]           = '( %d, %d, %s, %s, %s, %s )';
					$results[ (int) $term_id ] = $action;
				}
				$sql = $query . implode( ', ', $place_holders ) . $wpdb->prepare( ' ON DUPLICATE KEY UPDATE action = %s, total = NULL, organic = NULL, total_month = NULL, organic_month = NULL, backlinks = NULL', $action );
				$wpdb->query( $wpdb->prepare( "{$sql}", $values ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}
		} while ( is_array( $data ) && count( $data ) === $limit ); // phpcs:ignore Squiz.PHP.DisallowSizeFunctionsInLoops.Found -- we increment count of $data.
		Ahrefs_Seo::breadcrumbs( __METHOD__ . ': ' . (string) wp_json_encode( $results ) );
	}
	/**
	 * Reset keywords and positions if snapshot with 'new' status exists.
	 * Called after GSC account updated.
	 *
	 * @return void
	 */
	public function reset_keywords_and_position_for_new_snapshot() {
		Ahrefs_Seo::breadcrumbs( __METHOD__ . (string) wp_json_encode( func_get_args() ) );
		$new_snapshot_id = $this->get_new_snapshot_id();
		if ( ! is_null( $new_snapshot_id ) ) {
			( new Content_Db() )->reset_gsc_info( $new_snapshot_id );
		}
	}
	/**
	 * Reset GA details if snapshot with 'new' status exists.
	 * Called after GA account updated.
	 *
	 * @return void
	 */
	public function reset_ga_for_new_snapshot() {
		Ahrefs_Seo::breadcrumbs( __METHOD__ . (string) wp_json_encode( func_get_args() ) );
		$new_snapshot_id = $this->get_new_snapshot_id();
		if ( ! is_null( $new_snapshot_id ) ) {
			( new Content_Db() )->reset_ga_info( $new_snapshot_id );
		}
	}
	/**
	 * Reset Ahrefs details if snapshot with 'new' status exists.
	 * Called after Ahrefs token updated.
	 *
	 * @return void
	 */
	public function reset_backlinks_for_new_snapshot() {
		Ahrefs_Seo::breadcrumbs( __METHOD__ . (string) wp_json_encode( func_get_args() ) );
		$new_snapshot_id = $this->get_new_snapshot_id();
		if ( ! is_null( $new_snapshot_id ) ) {
			( new Content_Db() )->reset_backlinks_info( $new_snapshot_id );
		}
	}
	/**
	 * Approve items from current and maybe new (if already exists) snapshots
	 * Ignore snapshot id from post tax.
	 *
	 * @param Post_Tax[] $post_taxes What to approve.
	 * @param bool       $approve True: approve, false: remove approvement.
	 * @return void
	 */
	public function analysis_approve_items( array $post_taxes, $approve = true ) {
		$new_snapshot_id     = $this->get_new_snapshot_id();
		$current_snapshot_id = Ahrefs_Seo_Data_Content::snapshot_context_get();
		$data                = Ahrefs_Seo_Data_Content::get();
		$content_audit       = new Content_Audit();
		foreach ( $post_taxes as $post_tax ) {
			$data->keyword_approve( $post_tax, $approve ); // update current snapshot.
			if ( ! is_null( $new_snapshot_id ) && $new_snapshot_id !== $current_snapshot_id ) {
				// approve the same keyword, as current snapshot has!
				$post_tax->set_snapshot_id( $current_snapshot_id );
				$keyword = ! empty( Ahrefs_Seo_Keywords::get()->post_keyword_get( $post_tax ) ) ? Ahrefs_Seo_Keywords::get()->post_keyword_get( $post_tax ) : '';
				$content_audit->keyword_approve( $post_tax, $keyword, $approve );
			}
		}
	}
	/**
	 * Return traffic median, if is set.
	 * Skip inactive items.
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return float|null Traffic median or null.
	 */
	public function get_traffic_median( $snapshot_id ) {
		global $wpdb;
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT traffic_median FROM {$wpdb->ahrefs_snapshots} WHERE snapshot_id = %d", $snapshot_id ) );
		return ! is_null( $result ) ? floatval( $result ) : null;
	}
	/**
	 * Set traffic median for snapshot
	 *
	 * @param int   $snapshot_id Snapshot ID.
	 * @param float $traffic_median Traffic median value.
	 * @return void
	 */
	public function set_traffic_median( $snapshot_id, $traffic_median ) {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( __METHOD__ . (string) wp_json_encode( func_get_args() ) );
		$wpdb->update( $wpdb->ahrefs_snapshots, [ 'traffic_median' => $traffic_median ], [ 'snapshot_id' => $snapshot_id ], [ '%f' ], [ '%d' ] );
		$this->clean_cache();
	}
	/**
	 * Reset 'require_update'. For new snapshot: any 'current' became 'old', then update 'new' snapshot to 'current'.
	 * Called when content audit is ready.
	 *
	 * @param int  $new_snapshot_id New snapshot ID.
	 * @param bool $is_audit_cancelled Audit was cancelled.
	 *
	 * @return void
	 */
	public function set_finished( $new_snapshot_id, $is_audit_cancelled = false ) {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( __METHOD__ . ': ' . (string) wp_json_encode( $new_snapshot_id ) );
		$snapshot_id = $this->get_new_snapshot_id();
		$type        = $wpdb->get_var( $wpdb->prepare( "SELECT snapshot_type FROM {$wpdb->ahrefs_snapshots} WHERE snapshot_id = %s", $new_snapshot_id ) );
		$type        = in_array( $type, [ 'manual', 'manual_finished' ], true ) ? 'manual_finished' : 'scheduled_finished'; // new snapshot type, based on previous type.
		// if snapshot from parameter is not 'new'.
		if ( $is_audit_cancelled ) {
			// set require_update = 0 for snapshot from parameter.
			$wpdb->update(
				$wpdb->ahrefs_snapshots,
				[
					'require_update'  => 0,
					'snapshot_status' => self::STATUS_CANCELLED,
					'time_end'        => current_time( 'mysql' ),
				],
				[ 'snapshot_id' => $new_snapshot_id ],
				[ '%d', '%s', '%s' ],
				[ '%d' ]
			);
			$this->clean_cache();
			return; // no need for snapshot status updating.
		} // if snapshot from parameter is not 'new'.
		if ( is_null( $snapshot_id ) || $new_snapshot_id !== $snapshot_id ) {
			// set require_update = 0 for snapshot from parameter.
			$wpdb->update(
				$wpdb->ahrefs_snapshots,
				[
					'require_update' => 0,
					'snapshot_type'  => $type,
				],
				[ 'snapshot_id' => $new_snapshot_id ],
				[ '%d', '%s' ],
				[ '%d' ]
			);
			return; // no need for snapshot status updating.
		}
		// update 'new' snapshot, it is definitely not null.
		// any 'current' snapshot became 'old'.
		$wpdb->update( $wpdb->ahrefs_snapshots, [ 'snapshot_status' => self::STATUS_OLD ], [ 'snapshot_status' => self::STATUS_CURRENT ], [ '%s' ], [ '%s' ] );
		$this->current_snapshot_id = null; // reset cached value.
		// this new snapshot became 'current' and not require update.
		$wpdb->update(
			$wpdb->ahrefs_snapshots,
			[
				'snapshot_status' => self::STATUS_CURRENT,
				'time_end'        => current_time( 'mysql' ),
				'require_update'  => 0,
				'snapshot_type'   => $type,
			],
			[ 'snapshot_id' => $snapshot_id ],
			[ '%s', '%s', '%d' ],
			[ '%d' ]
		);
		$this->clean_cache();
		Ahrefs_Seo_Data_Content::get()->set_last_audit_time( time() );
		( new Events() )->on_snapshot_created( $snapshot_id );
	}
	/**
	 * Get snapshot info
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return array<string, string>
	 */
	public function get_snapshot_info( $snapshot_id ) {
		global $wpdb;
		return (array) $wpdb->get_row( $wpdb->prepare( "SELECT time_end, snapshot_status FROM {$wpdb->ahrefs_snapshots} WHERE snapshot_id = %d", $snapshot_id ), ARRAY_A );
	}
	/**
	 * Is update required? Not used for new snapshots.
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return bool
	 */
	public function is_require_update( $snapshot_id ) {
		global $wpdb;
		return (bool) $wpdb->get_var( $wpdb->prepare( "SELECT require_update FROM {$wpdb->ahrefs_snapshots} WHERE snapshot_id = %d", $snapshot_id ) );
	}
	/**
	 * Set that snapshot requires update.
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return bool Success.
	 */
	public function set_require_update( $snapshot_id ) {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( __METHOD__ . ': ' . (string) wp_json_encode( $snapshot_id ) );
		Ahrefs_Seo_Cron::get()->start_tasks_content();
		// run cron content audit updates.
		return false !== $wpdb->update( $wpdb->ahrefs_snapshots, [ 'require_update' => 1 ], [ 'snapshot_id' => $snapshot_id ], [ '%d' ], [ '%s' ] );
	}
	/**
	 * Both snapshots exist: current and new.
	 *
	 * @return bool
	 */
	public function has_current_and_new_snapshots() {
		$current = $this->get_current_snapshot_id();
		$new     = $this->get_new_snapshot_id();
		return ! is_null( $new ) && $new !== $current;
	}
	/**
	 * Is audit scheduled?
	 *
	 * @since 0.7.5
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return bool True - scheduled audit, false - manually started audit or restarted audit of any type.
	 */
	public function is_scheduled_audit( $snapshot_id ) {
		global $wpdb;
		return 'scheduled' === $wpdb->get_var( $wpdb->prepare( "SELECT snapshot_type FROM {$wpdb->ahrefs_snapshots} WHERE snapshot_id = %s", $snapshot_id ) );
	}
	/**
	 * Get start time of audit.
	 *
	 * @since 0.7.5
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return int Timestamp
	 */
	public function get_start_time( $snapshot_id ) {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT UNIX_TIMESTAMP(time_start) FROM {$wpdb->ahrefs_snapshots} WHERE snapshot_id = %s", $snapshot_id ) );
	}
	/**
	 * Update type of snapshot, restarted by user from scheduled.
	 *
	 * @since 0.7.5
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return void
	 */
	public function on_audit_clean_pause( $snapshot_id ) {
		global $wpdb;
		if ( $this->is_scheduled_audit( $snapshot_id ) ) {
			$wpdb->update( $wpdb->ahrefs_snapshots, [ 'snapshot_type' => 'scheduled_restarted' ], [ 'snapshot_id' => $snapshot_id ], [ '%s' ], [ '%d' ] );
		}
	}
	/**
	 * Clean cache for new snapshot ID.
	 *
	 * @since 0.8.6
	 *
	 * @return void
	 */
	public function clean_cache() {
		wp_cache_delete( 'new_id', self::CACHE_GROUP );
		foreach ( [ 1, $this->get_new_snapshot_id(), $this->get_current_snapshot_id() ] as $snapshot_id ) {
			if ( ! is_null( $snapshot_id ) ) {
				wp_cache_delete( "median{$snapshot_id}", 'ahrefs_seo_audit' );
			}
		}
	}
	/**
	 * Get country code of snapshot.
	 *
	 * @since 0.9.6
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return string Country code
	 */
	public function get_country_code( $snapshot_id ) {
		global $wpdb;
		return (string) $wpdb->get_var( $wpdb->prepare( "SELECT country FROM {$wpdb->ahrefs_snapshots} WHERE snapshot_id = %s", $snapshot_id ) );
	}
}