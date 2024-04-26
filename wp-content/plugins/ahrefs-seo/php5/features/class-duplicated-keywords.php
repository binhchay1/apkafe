<?php

namespace ahrefs\AhrefsSeo\Features;

use ahrefs\AhrefsSeo\Ahrefs_Seo;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Data_Content;
use ahrefs\AhrefsSeo\Post_Tax;
/**
 * All about duplicated keywords
 *
 * @since 0.8.5
 */
class Duplicated_Keywords {

	/**
	 * Find all duplicated keywords once content audit is finished.
	 * Fill "is_duplicated" column.
	 *
	 * @param int $snapshot_id Snapshot ID.
	 * @return void
	 */
	public function fill_duplicated_for_snapshot( $snapshot_id ) {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( __METHOD__ . (string) wp_json_encode( func_get_args() ) );
		$from       = ", {$wpdb->ahrefs_content} c2 ";
		$where      = $wpdb->prepare( 'AND c2.snapshot_id = c.snapshot_id AND c.keyword <> "" AND c2.keyword = c.keyword AND ( c2.post_id <> c.post_id OR c2.taxonomy <> c.taxonomy ) AND c.action IN ( %s, %s, %s, %s ,%s ) AND c2.action IN ( %s, %s, %s, %s, %s, %s, %s )', Ahrefs_Seo_Data_Content::ACTION4_DO_NOTHING, Ahrefs_Seo_Data_Content::ACTION4_EXCLUDE, Ahrefs_Seo_Data_Content::ACTION4_MERGE, Ahrefs_Seo_Data_Content::ACTION4_UPDATE_ORANGE, Ahrefs_Seo_Data_Content::ACTION4_REWRITE, Ahrefs_Seo_Data_Content::ACTION4_UPDATE_YELLOW, Ahrefs_Seo_Data_Content::ACTION4_DO_NOTHING, Ahrefs_Seo_Data_Content::ACTION4_EXCLUDE, Ahrefs_Seo_Data_Content::ACTION4_MERGE, Ahrefs_Seo_Data_Content::ACTION4_UPDATE_ORANGE, Ahrefs_Seo_Data_Content::ACTION4_REWRITE, Ahrefs_Seo_Data_Content::ACTION4_UPDATE_YELLOW );
		$duplicated = (array) $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT c.post_id as post_id, c.taxonomy as taxonomy FROM {$wpdb->ahrefs_content} c {$from} WHERE ( c.snapshot_id = %d ) {$where}", $snapshot_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- from and where vars are safe for use.
		// fill duplicated keywords.
		if ( count( $duplicated ) ) {
			$parts = [];
			foreach ( $duplicated as $row ) {
				$parts[] = $wpdb->prepare( '(post_id = %d AND taxonomy = %s)', $row['post_id'], $row['taxonomy'] );
			}
			$where = implode( ' OR ', $parts );
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET is_duplicated = %d WHERE snapshot_id = %d AND ( {$where} )", Ahrefs_Seo_Data_Content::KEYWORD_DUPLICATED, $snapshot_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $where consists of prepared strings.
		}
		// fill the rest with not duplicated.
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET is_duplicated = %d WHERE snapshot_id = %d AND is_duplicated = %d", Ahrefs_Seo_Data_Content::KEYWORD_NOT_DUPLICATED, $snapshot_id, Ahrefs_Seo_Data_Content::KEYWORD_UNKNOWN ) );
	}
	/**
	 * Fill is_duplicated for all articles having this keyword for snapshot
	 *
	 * @param int    $snapshot_id Snapshot ID.
	 * @param string $keyword Current keyword.
	 * @return void
	 */
	public function fill_duplicated_for_keyword( $snapshot_id, $keyword ) {
		global $wpdb;
		Ahrefs_Seo::breadcrumbs( __METHOD__ . (string) wp_json_encode( func_get_args() ) );
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT c.post_id as post_id, c.taxonomy as taxonomy, c.snapshot_id as snapshot_id, c.action as action FROM {$wpdb->ahrefs_content} c WHERE ( c.snapshot_id = %d ) AND ( c.keyword = %s )", $snapshot_id, $keyword ), ARRAY_A );
		if ( is_array( $results ) && count( $results ) ) { // any article with this keyword?
			// only active articles (from "All analyzed" tab) counted as duplicated.
			$active_results = array_filter(
				$results,
				function ( $row ) {
					return in_array( $row['action'], [ Ahrefs_Seo_Data_Content::ACTION4_DO_NOTHING, Ahrefs_Seo_Data_Content::ACTION4_EXCLUDE, Ahrefs_Seo_Data_Content::ACTION4_MERGE, Ahrefs_Seo_Data_Content::ACTION4_UPDATE_ORANGE, Ahrefs_Seo_Data_Content::ACTION4_REWRITE, Ahrefs_Seo_Data_Content::ACTION4_UPDATE_YELLOW ], true );
				}
			);
			$parts          = [];
			foreach ( $results as $row ) {
				$parts[] = $wpdb->prepare( 'post_id = %d AND taxonomy = %s', $row['post_id'], $row['taxonomy'] );
			}
			$where = implode( ' OR ', $parts );
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ahrefs_content} SET is_duplicated = %d WHERE snapshot_id = %d AND ( {$where} )", count( $active_results ) > 1 ? Ahrefs_Seo_Data_Content::KEYWORD_DUPLICATED : Ahrefs_Seo_Data_Content::KEYWORD_NOT_DUPLICATED, $snapshot_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $where consists of prepared strings.
		}
	}
	/**
	 * Update is_duplicated for article
	 *
	 * @param Post_Tax    $post_tax Article.
	 * @param string|null $keyword Current keyword of article.
	 * @param bool        $is_inactive Is the status of article from inactive list (Excluded tab).
	 * @return void
	 */
	public function update_is_duplicated( Post_Tax $post_tax, $keyword = null, $is_inactive ) {
		Ahrefs_Seo::breadcrumbs( __METHOD__ . (string) wp_json_encode( [ (string) $post_tax, $keyword, $is_inactive ] ) );
		if ( $is_inactive ) {
			$this->reset_for_post( $post_tax );
		} elseif ( is_null( $keyword ) || '' === $keyword ) {
			// no keyword set.
			$this->set_not_duplicated( $post_tax );
		} else {
			$snapshot_id = $post_tax->get_snapshot_id();
			if ( ! is_null( $snapshot_id ) ) {
				$this->fill_duplicated_for_keyword( $snapshot_id, $keyword );
			}
		}
	}
	/**
	 * Reset article's is_duplicated to initial state (unknown).
	 *
	 * @param Post_Tax $post_tax Article.
	 * @return void
	 */
	public function reset_for_post( Post_Tax $post_tax ) {
		$this->set( $post_tax, Ahrefs_Seo_Data_Content::KEYWORD_UNKNOWN );
	}
	/**
	 * Set article is not duplicated
	 *
	 * @param Post_Tax $post_tax Article.
	 * @return void
	 */
	public function set_not_duplicated( Post_Tax $post_tax ) {
		$this->set( $post_tax, Ahrefs_Seo_Data_Content::KEYWORD_NOT_DUPLICATED );
	}
	/**
	 * Set is_duplicated value for article
	 *
	 * @param Post_Tax $post_tax Article to be updated.
	 * @param int      $value New value of is_duplicated.
	 * @return void
	 */
	private function set( Post_Tax $post_tax, $value ) {
		global $wpdb;
		$wpdb->update( $wpdb->ahrefs_content, [ 'is_duplicated' => $value ], $post_tax->as_where_array(), [ '%d' ], $post_tax->as_where_format() );
	}
	/**
	 * Return part of filter for WHERE
	 *
	 * @return string
	 */
	public function filter_where() {
		global $wpdb;
		return (string) $wpdb->prepare( 'AND c.keyword <> "" AND c.is_duplicated = %d', Ahrefs_Seo_Data_Content::KEYWORD_DUPLICATED );
	}
}