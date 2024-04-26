<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo;

/**
 * Class for work with DB, implement some methods for content data.
 */
class Content_Db {

	/**
	 * Return organic traffic details if exists for current snapshot.
	 * Return:
	 * organic_total - total amount of organic traffic from post created/modified time till analyzed time.
	 * organic_month - amount of organic traffic per month.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $taxonomy Taxonomy.
	 * @return array<string, string|null>|null Associative array [ organic_total => .., organic_month => ... ] or null if no detail exists.
	 */
	public function content_get_organic_traffic_data_for_post( int $post_id, string $taxonomy = '' ) : ?array {
		global $wpdb;
		$snapshot_id = Ahrefs_Seo_Data_Content::get()->snapshot_context_get();
		$result      = $wpdb->get_row( $wpdb->prepare( "SELECT organic as organic_total, organic_month as organic_month FROM {$wpdb->ahrefs_content} WHERE post_id = %d AND snapshot_id = %d AND taxonomy = %s", $post_id, $snapshot_id, $taxonomy ), ARRAY_A );
		return empty( $result ) ? null : $result;
	}

	/**
	 * Return all authors, ordered by display name.
	 * Cache results for 30 minutes.
	 *
	 * @return array<int, array<string, string>>
	 */
	public function get_all_authors() : array {
		global $wpdb;
		$result = get_transient( 'ahrefs-seo-all-authors-list' );
		if ( is_array( $result ) ) {
			return $result;
		}

		$results = (array) $wpdb->get_results( "SELECT DISTINCT p.post_author as id, u.display_name as name FROM {$wpdb->posts} p JOIN {$wpdb->users} u ON p.post_author = u.ID WHERE p.post_status = 'publish' AND ( p.post_type IN (" . Ahrefs_Seo_Data_Content::get_allowed_post_types_for_where() . ') ) LIMIT 1000', ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- get_allowed_post_types_for_where() has prepared string.
		usort(
			$results,
			function( $a, $b ) {
				return strcasecmp( $a['name'], $b['name'] );
			}
		);
		set_transient( 'ahrefs-seo-all-authors-list', $result, 30 * HOUR_IN_SECONDS );
		return $results;
	}

	/**
	 * Reset fields for update of keywords and positions for all items of snapshot.
	 * Note: we do not overwrite approved keywords.
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return void
	 */
	public function reset_gsc_info( int $snapshot_id ) : void {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( __METHOD__ );
		$wpdb->update(
			$wpdb->ahrefs_content,
			[
				'keywords_need_update' => 1,
				'position_need_update' => 1,
				'kw_gsc'               => null,
				'kw_idf'               => null,
				'position'             => null,
			],
			[
				'snapshot_id' => $snapshot_id,
			],
			[ '%d', '%d', '%s', '%s', '%s' ],
			[ '%d' ]
		);
		// Note: active items are items: not initial statuses, not out-of-scope.
		// update active items with all statuses to "analyzing".
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET action = %s WHERE snapshot_id = %d AND action != %s AND action != %s AND action != %s", Ahrefs_Seo_Data_Content::ACTION4_ANALYZING, $snapshot_id, Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_INITIAL, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL ) );
	}

	/**
	 * Reset traffic details and status to analyzing.
	 * Called after GA account updated, if new snapshot exists.
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return void
	 */
	public function reset_ga_info( int $snapshot_id ) : void {
		global $wpdb;
		// update all traffic and backlinks values to initial NULL.
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET total = NULL, organic = NULL, total_month = NULL, organic_month = NULL, error_traffic = NULL WHERE snapshot_id = %d", $snapshot_id ) );

		// Note: active items are items: not initial statuses, not out-of-scope.
		// update active items with all statuses to "analyzing".
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET action = %s WHERE snapshot_id = %d AND action != %s AND action != %s AND action != %s", Ahrefs_Seo_Data_Content::ACTION4_ANALYZING, $snapshot_id, Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_INITIAL, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL ) );
	}

	/**
	 * Reset backlinks details and status to analyzing.
	 * Called after new Ahrefs token set, if new snapshot exists.
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return void
	 */
	public function reset_backlinks_info( int $snapshot_id ) : void {
		global $wpdb;
		// update all traffic and backlinks values to initial NULL.
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET backlinks = NULL, error_backlinks = NULL WHERE snapshot_id = %d", $snapshot_id ) );

		// Note: active items are items: not initial statuses, not out-of-scope.
		// update active items with all statuses to "analyzing".
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET action = %s WHERE snapshot_id = %d AND action != %s AND action != %s AND action != %s AND action != %s", Ahrefs_Seo_Data_Content::ACTION4_ANALYZING, $snapshot_id, Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_INITIAL, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_ANALYZING ) );
	}

}
