<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Third_Party\Result;
use ahrefs\AhrefsSeo\Third_Party\Sources;
/**
 * Helper class for some DB requests.
 */
class Ahrefs_Seo_Db_Helper {

	/**
	 * Check and return updated items from given list.
	 * Note: snapshot_id passed using parameter.
	 *
	 * @param array<string,int> $post_tax_strings_with_ver Associative array ( (string)post_tax_string => (int)ver ).
	 * @param int               $snapshot_id Snapshot ID.
	 * @return string[] Array of post_tax_string with updates (ver value changed).
	 */
	public static function get_updated_post_tax_strings( array $post_tax_strings_with_ver, $snapshot_id ) {
		global $wpdb;
		$ids                   = [];
		$updated_ids           = [];
		$post_id_with_taxonomy = [];
		foreach ( $post_tax_strings_with_ver as $post_tax_string => $ver ) {
			$post_tax                                  = Post_Tax::create_from_string( "{$post_tax_string}" );
			$index                                     = $post_tax->get_post_id() . '|' . $post_tax->get_taxonomy(); // index is "post_id|taxonomy".
			$post_id_with_taxonomy[ $post_tax_string ] = $index;
			$ids[]                                     = $index;
		}
		$placeholder    = array_fill( 0, count( $ids ), '%s' );
		$ids[]          = $snapshot_id;
		$sql            = $wpdb->prepare( "SELECT concat(post_id,'|',taxonomy) as 'post_and_taxonomy', UNIX_TIMESTAMP(updated) as 'ver' FROM {$wpdb->ahrefs_content} WHERE concat(post_id,'|',taxonomy) IN ( " . implode( ', ', $placeholder ) . ') AND snapshot_id = %d', $ids ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$data           = $wpdb->get_results( $sql, ARRAY_N ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$found_versions = [];
		foreach ( (array) $data as $row ) {
			$found_versions[ $row[0] ] = intval( $row[1] );
			// index is "post_id|taxonomy".
		}
		// Search ids with updated ver.
		foreach ( $post_tax_strings_with_ver as $post_tax_string => $ver ) {
			if ( ! isset( $found_versions[ $post_id_with_taxonomy[ $post_tax_string ] ] ) || $found_versions[ $post_id_with_taxonomy[ $post_tax_string ] ] !== $ver ) { // if not found or has different ver.
				$updated_ids[] = $post_tax_string;
			}
		}
		return $updated_ids;
	}
	/**
	 * @param int    $snapshot_id Snapshot ID.
	 * @param string $search_string Search string for titles.
	 * @return array<\stdClass> results as OBJECT list.
	 */
	public static function content_data_get_clear_months( $snapshot_id, $search_string = '' ) {
		global $wpdb;
		$additional_where = [ 'AND ( p.post_type IN (' . Ahrefs_Seo_Data_Content::get_allowed_post_types_for_where() . ') )' ];
		if ( '' !== $search_string ) {
			$search             = '%' . $wpdb->esc_like( $search_string ) . '%';
			$additional_where[] = $wpdb->prepare( ' AND c.title LIKE %s ', $search );
		}
		$additional_where = implode( ' ', $additional_where );
		return $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT YEAR( date_updated ) AS year, MONTH( date_updated ) AS month FROM {$wpdb->ahrefs_content} as c, {$wpdb->posts} as p WHERE snapshot_id = %d AND taxonomy = '' AND c.post_id = p.ID {$additional_where} ORDER BY date_updated DESC", $snapshot_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
	/**
	 * Set last well date to current date or reset it to null.
	 *
	 * @since 0.8.4
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param bool     $set_to_current_date True: set to date, false: reset.
	 * @return void
	 */
	public static function set_last_well_date( Post_Tax $post_tax, $set_to_current_date = true ) {
		global $wpdb;
		$wpdb->update( $wpdb->ahrefs_content, [ 'last_well_date' => $set_to_current_date ? date( 'Y-m-d' ) : null ], $post_tax->as_where_array(), [ '%s' ], $post_tax->as_where_format() );
	}
	/**
	 * Load additional data for canonical, noindex, redirected from history or try to recreate it.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param string   $field One of 'canonical_data', 'noindex_data', 'redirected_data'.
	 * @return array<string,mixed> Title => value pairs.
	 */
	public static function load_additional_data_from_history( Post_Tax $post_tax, $field ) {
		global $wpdb;
		$data = null;
		switch ( $field ) {
            // phpcs:disable WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			case 'canonical_data':
				$data = $wpdb->get_row( $wpdb->prepare( "SELECT canonical_data from {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND post_id = %d AND taxonomy = %s", $post_tax->as_where_array() ), ARRAY_A );
				break;
			case 'noindex_data':
				$data = $wpdb->get_row( $wpdb->prepare( "SELECT noindex_data from {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND post_id = %d AND taxonomy = %s", $post_tax->as_where_array() ), ARRAY_A );
				break;
			case 'redirected_data':
				$data = $wpdb->get_row( $wpdb->prepare( "SELECT redirected_data from {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND post_id = %d AND taxonomy = %s", $post_tax->as_where_array() ), ARRAY_A );
				break;
		}
		$saved = null;
		if ( empty( $data ) || is_array( $data ) && is_null( $data[ $field ] ) ) {
			// try to fetch current values.
			$instance = Result::create( $post_tax, $field );
			if ( 1 === $instance->check() ) { // if result of test is the same, we may return it just now.
				$saved = $instance->as_array();
			}
			unset( $instance );
		} elseif ( is_array( $data ) && ! is_null( $data[ $field ] ) ) {
			$saved = json_decode( $data[ $field ], true );
		}
		if ( is_array( $saved ) ) {
			$result = [];
			foreach ( [ 'url', 'c_url', 'r_url', 'source_id' ] as $source_id ) {
				foreach ( $saved as $key => $value ) {
					if ( $source_id === $key ) {
						switch ( $key ) {
							case 'url':
								$result[ __( 'URL', 'ahrefs-seo' ) ] = $value;
								break;
							case 'c_url':
								$result[ __( 'Canonical URL', 'ahrefs-seo' ) ] = $value;
								break;
							case 'r_url':
								$result[ __( 'Redirected to URL', 'ahrefs-seo' ) ] = $value;
								break;
							case 'source_id':
								$result[ __( 'Source', 'ahrefs-seo' ) ] = Sources::get_title( $value );
								break;
						}
					}
				}
			}
			return $result;
		}
		return null;
	}
}