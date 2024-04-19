<?php
/**
 * Declare class Keyword
 *
 * @package Keyword
 */

namespace Lasso\Classes;

use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Models\Model;

use Lasso_DB;
use Lasso_Cron;
use Lasso_Affiliate_Link;

use simple_html_dom;

/**
 * Keyword
 */
class Keyword {
	/**
	 * Delete untracked keyword
	 *
	 * @param int   $lasso_id     Lasso post id.
	 * @param array $new_keywords Array of new keywords. Default to empty array.
	 */
	/**
	// public static function remove_keywords( $lasso_id = 0, $new_keywords = array() ) {
	//  $keywords         = self::get_keywords( $lasso_id );
	//  $deleted_keywords = array_diff( $keywords, $new_keywords );
	//  if ( ! empty( $deleted_keywords ) ) {
	//      self::delete_untrack_keywords( $deleted_keywords );
	//  }
	// }
	 */

	/**
	 * Delete untrack keywords
	 *
	 * @param array $keywords Array of keywords.
	 */
	public static function delete_untrack_keywords( $keywords ) {
		$lasso_cron = new Lasso_Cron();

		$keywords = array_map(
			function( $value ) {
				$value = addslashes( addslashes( $value ) );
				return $value;
			},
			$keywords
		);

		$keyword_in        = "'" . implode( "', '", $keywords ) . "'";
		$keyword_locations = Model::get_results(
			"
            SELECT 
                keyword, detection_id, GROUP_CONCAT(id SEPARATOR '|') ids 
            FROM 
                " . Model::get_wp_table_name( LASSO_KEYWORD_LOCATIONS ) . "
            WHERE keyword IN ($keyword_in) 
            GROUP BY detection_id, keyword
        "
		);

		foreach ( $keyword_locations as $location ) {
			$post = Model::get_row(
				'
                SELECT post_content 
                FROM ' . Model::get_wp_table_name( 'posts' ) . " 
                WHERE ID = '$location->detection_id'
            "
			);

			if ( ! empty( $post ) ) {
				$post_content = preg_replace( '/<keyword\s*data-keyword-id="(' . $location->ids . ')">(.*?)<\/keyword>/i', '$2', $post->post_content );
				$lasso_cron->update_post_content( $location->detection_id, $post_content );
			}
		}

		$delete1 = Model::query(
			'
            DELETE FROM ' . Model::get_wp_table_name( LASSO_TRACKED_KEYWORDS ) . " 
            WHERE keyword IN ($keyword_in)
        "
		);
		$delete2 = Model::query(
			'
            DELETE FROM ' . Model::get_wp_table_name( LASSO_KEYWORD_LOCATIONS ) . " 
            WHERE keyword IN ($keyword_in)
        "
		);
		$delete2 = 0 === $delete2 ? true : $delete2;

		return $delete1 && $delete2;
	}

	/**
	 * Add new keywords
	 *
	 * @param int   $lasso_id     Id of a lasso post.
	 * @param array $new_keywords List of keyword. Default to empty array.
	 * @return int 1: success, 0: error, 2: already exists
	 */
	public static function add_keywords( $lasso_id = 0, $new_keywords = array() ) {
		// ? add new keywords
		$keywords = self::get_keywords( $lasso_id );
		if ( ! empty( array_udiff( $new_keywords, $keywords, 'strcasecmp' ) ) ) {
			$new_keywords = self::check_and_save_keywords( $lasso_id, $new_keywords );
			if ( ! empty( $new_keywords ) ) {
				self::scan_post_page_for_keyword( $new_keywords );
				return 1;
			}
			return 0; // saving failed.
		} else {
			return 2;
		}
	}

	/**
	 * Check and save keywords
	 *
	 * @param int   $post_id  Post id.
	 * @param array $keywords Array of keywords. Default to empty array.
	 */
	public static function check_and_save_keywords( $post_id = 0, $keywords = array() ) {
		global $wpdb;

		$lasso_db = new Lasso_DB();

		$new_keywords            = array();
		$already_tracked_keyword = array();

		foreach ( $keywords as $keyword ) {
			$keyword         = addslashes( $keyword );
			$where_condition = " WHERE keyword LIKE '% $keyword %' ";
			$db_keyword      = Model::get_results( ' SELECT * FROM  ' . Model::get_wp_table_name( LASSO_TRACKED_KEYWORDS ) . " $where_condition " );

			if ( empty( $db_keyword ) ) {
				// phpcs:ignore
				$wpdb->insert(
					Model::get_wp_table_name( LASSO_TRACKED_KEYWORDS ),
					array(
						'lasso_id' => $post_id,
						'keyword'  => $keyword,
					)
				);

				$lastid                  = $wpdb->insert_id;
				$new_keywords[ $lastid ] = $keyword;
			} else {
				array_push( $already_tracked_keyword, $keyword );
			}
		}

		if ( ! empty( $already_tracked_keyword ) ) {
			Lasso_Helper::write_log( 'Already tracked keywords:' . implode( ', ', $already_tracked_keyword ), 'keyword_tracking' );
		}

		return $new_keywords;
	}

	/**
	 * Scan post/page contains keywords
	 *
	 * @param array $keywords Array of keywords. Default to empty array.
	 */
	public static function scan_post_page_for_keyword( $keywords = array() ) {
		global $wpdb;

		$lasso_db      = new Lasso_DB();
		$mysql_version = $wpdb->db_version();

		$query = '
            SELECT id, lasso_id, keyword 
            FROM ' . Model::get_wp_table_name( LASSO_TRACKED_KEYWORDS ) . ' 
        ';

		if ( ! empty( $keywords ) ) {
			$ids    = implode( ', ', array_keys( $keywords ) );
			$query .= ' WHERE id IN (' . $ids . ')';
		}
		$keywords         = Model::get_results( $query );
		$cpt_support      = Lasso_Helper::get_cpt_support();
		$where_post_types = '"' . implode( '", "', $cpt_support ) . '"';

		foreach ( $keywords as $keyword ) {
			$keywrd = addslashes( $keyword->keyword );

			if ( intval( $mysql_version ) >= 8 ) {
				// ? REGEXP in mysql 8
				$query = '
					SELECT 
						DISTINCT p.id
					FROM  
						' . Model::get_wp_table_name( 'posts' ) . ' as p
					LEFT JOIN 
						' . Model::get_wp_table_name( LASSO_KEYWORD_LOCATIONS ) . " as kl
							ON p.ID = kl.detection_id
					WHERE p.post_content REGEXP '\\\\b" . $keywrd . "\\\\b'
						AND p.post_status IN ('publish', 'draft')
						AND p.post_type IN (" . $where_post_types . ')
				';
			} else {
				// ? REGEXP in mysql 5
				$query = '
					SELECT 
						DISTINCT p.id
					FROM  
						' . Model::get_wp_table_name( 'posts' ) . ' as p
					LEFT JOIN 
						' . Model::get_wp_table_name( LASSO_KEYWORD_LOCATIONS ) . " as kl
							ON p.ID = kl.detection_id
					WHERE p.post_content REGEXP '[[:<:]]" . $keywrd . "\[[:>:]]'
						AND p.post_status IN ('publish', 'draft')
						AND p.post_type IN (" . $where_post_types . ')
				';
			}

			$posts = Model::get_results( $query );

			foreach ( $posts as $post ) {
				self::scan_keywords_in_post_page( $post->id );
			}
		}
	}

	/**
	 * Scan keywords in a post/page
	 *
	 * @param int $post_id Post id.
	 */
	public static function scan_keywords_in_post_page( $post_id ) {
		if ( ( 0 === $post_id || '' === $post_id ) && get_post( $post_id ) ) {
			return false;
		}

		global $wpdb;

		$lasso_cron = new Lasso_Cron();

		$query           = '
            SELECT id, lasso_id, LOWER(keyword) as keyword
            FROM ' . Model::get_wp_table_name( LASSO_TRACKED_KEYWORDS ) . ' 
        ';
		$keywords_result = Model::get_results( $query );
		$keywords        = array_column( $keywords_result, 'keyword' );
		$lasso_ids       = array_column( $keywords_result, 'lasso_id' );
		rsort( $keywords );
		$regex_keywords = implode( '|', $keywords );
		if ( empty( $regex_keywords ) ) {
			return;
		}

		$post_content = Model::get_var(
			Model::prepare(
				'SELECT post_content FROM ' . Model::get_wp_table_name( 'posts' ) . ' where ID = %d', // phpcs:ignore
				$post_id
			)
		);

		// ? Update post content table wp_postmeta support Thrive plugin
		$post_content = Lasso_Helper::get_thrive_plugin_post_content( $post_id, $post_content );

		if ( empty( $post_content ) ) {
			return;
		}

		// ? Ignore change content for some blocks
		// ? Ignore block pattern ex: /kadence\/|editor-blocks\//
		$ignore_blocks_content = array();
		$blocks                = parse_blocks( $post_content );

		// ? Replace ignore block content by random key, so we would not change block content by add keyword process
		foreach ( $blocks as $block ) {
			if ( ! Lasso_Cron::is_block_supported( $block['blockName'] ) ) {
				$ignore_key                           = 'ignore_block_content_' . Lasso_Helper::generate_random_string();
				$post_content                         = str_replace( $block['innerHTML'], $ignore_key, $post_content );
				$ignore_blocks_content[ $ignore_key ] = $block['innerHTML'];
			}
		}

		$ignore_tags          = 'a|keyword|script';
		$keyword_exists_regex = "~(\b$regex_keywords\b)(?![^<]*>|[^<>]*<\/(" . $ignore_tags . "|$regex_keywords))(?![^\[]*\])~i";
		preg_match_all( $keyword_exists_regex, $post_content, $key_matches, PREG_PATTERN_ORDER );
		if ( empty( $key_matches[0] ) ) {
			return;
		}

		foreach ( $key_matches[0] as $key => $matched_keyword ) {
			if ( empty( $matched_keyword ) ) {
				continue;
			}
			$keyword_lower = strtolower( $matched_keyword );
			$lasso_id      = $lasso_ids[ array_search( $keyword_lower, $keywords, true ) ];
			$new_content   = '';
			$strict_regex  = "~(\b$matched_keyword\b)(?![^<]*>|[^<>]*<\/(" . $ignore_tags . "|$matched_keyword))(?![^\[]*\])~s";
			$tmp           = preg_split( $strict_regex, $post_content );
			foreach ( $tmp as $key => $paragraph ) {
				// ? before valid keyword must be a space character
				if ( $paragraph && ' ' !== substr( $paragraph, -1 ) ) {
					$new_content .= $key + 2 <= count( $tmp ) ? $paragraph . $matched_keyword : $paragraph;
					continue;
				}

				// phpcs:ignore
				$wpdb->insert(
					Model::get_wp_table_name( LASSO_KEYWORD_LOCATIONS ),
					array(
						'lasso_id'     => "$lasso_id",
						'keyword'      => "$matched_keyword",
						'detection_id' => "$post_id",
					)
				);
				$location_id = $wpdb->insert_id;
				if ( ! empty( $location_id ) ) {
					$keyword_tag  = '<keyword data-keyword-id="' . $location_id . '">' . $matched_keyword . '</keyword>';
					$new_content .= $key + 2 <= count( $tmp ) ? $paragraph . $keyword_tag : $paragraph;
				}
			}
			$post_content = $new_content;
		}

		// ? Bring ignore block content back
		if ( count( $ignore_blocks_content ) ) {
			foreach ( $ignore_blocks_content as $ignore_block_key => $ignore_block_content ) {
				$post_content = str_replace( $ignore_block_key, $ignore_block_content, $post_content );
			}
		}

		// ? update post content
		$lasso_cron->update_post_content( $post_id, $post_content );
		self::remove_old_keywords_in_db( $post_id, $post_content );
	}

	/**
	 * Remove old keywords in DB
	 *
	 * @param int    $post_id      Post id.
	 * @param string $post_content Post content.
	 */
	public static function remove_old_keywords_in_db( $post_id, $post_content ) {
		global $wpdb;

		$lasso_db = new Lasso_DB();

		$html = new simple_html_dom();
		$html->load( $post_content, true, false );

		$keyword_ids = array();
		foreach ( $html->find( 'keyword[data-keyword-id]' ) as $tag ) {
			array_push( $keyword_ids, $tag->attr['data-keyword-id'] );
		};

		$where_in = "'" . implode( "','", array_unique( $keyword_ids ) ) . "'";
		$where_in = '' !== $where_in ? "and id not in ($where_in)" : '';

		$prepare = $wpdb->prepare(
			'DELETE FROM ' . Model::get_wp_table_name( LASSO_KEYWORD_LOCATIONS ) . " WHERE detection_id = %d $where_in", // phpcs:ignore
			$post_id
		);

		return Model::query( $prepare );
	}

	/**
	 * Check whether keywords are used or not
	 *
	 * @param int   $lasso_id Id of lasso post.
	 * @param array $keywords List of keywords.
	 */
	public static function check_keywords_have_been_used( $lasso_id, $keywords ) {
		if ( ! is_array( $keywords ) ) {
			return false;
		}
		// ? check keywords whether they exist in other Lasso post
		if ( is_array( $keywords ) && 0 === count( $keywords ) ) {
			return false;
		}

		if ( $lasso_id && $lasso_id > 0 ) {
			$keywords_not_in   = self::get_keywords( $lasso_id, true );
			$existing_keywords = array_intersect( $keywords, $keywords_not_in );
		} else {
			$lasso_keywords    = self::get_keywords();
			$existing_keywords = array_intersect( $keywords, $lasso_keywords );
		}

		if ( is_countable( $existing_keywords ) && count( $existing_keywords ) > 0 ) {
			$first_existing_keyword        = array_values( $existing_keywords )[0];
			$keyword_exists_in_lasso       = self::get_lasso_id_by_keyword( $first_existing_keyword );
			$keyword_exists_in_lasso_title = get_the_title( $keyword_exists_in_lasso->lasso_id );

			wp_send_json_error( "Keyword <strong>$first_existing_keyword</strong> already exists in $keyword_exists_in_lasso_title" );
		}
	}

	/**
	 * Get keywords of a Lasso post or all keywords
	 *
	 * @param int     $lasso_id     Lasso post id.
	 * @param boolean $not_equal    Include/exclude lasso post id. Default to false.
	 * @param boolean $is_use_cache Is use cache.
	 */
	public static function get_keywords( $lasso_id = 0, $not_equal = false, $is_use_cache = false ) {
		$where_condition = '';
		if ( 0 !== $lasso_id ) {
			$equal           = $not_equal ? '<>' : '=';
			$where_condition = " WHERE lasso_id $equal '$lasso_id'";
		}

		$query = '
            SELECT keyword 
            FROM ' . Model::get_wp_table_name( LASSO_TRACKED_KEYWORDS ) . " 
            $where_condition
            GROUP BY keyword
        ";

		// ? Enable cache on frontend to prevent duplicate queries issue
		if ( ! is_admin() ) {
			$is_use_cache = true;
		}

		return Model::get_col( $query, $is_use_cache );
	}

	/**
	 * Get lasso id by keyword
	 *
	 * @param string $keyword Keyword.
	 */
	public static function get_lasso_id_by_keyword( $keyword ) {
		// @codingStandardsIgnoreStart
		$prepare = Model::prepare(
			'
			SELECT lasso_id 
			FROM ' . Model::get_wp_table_name( LASSO_TRACKED_KEYWORDS ) . ' 
			WHERE keyword = %s
		',
			$keyword
		);
		// @codingStandardsIgnoreEnd

		return Model::get_row( $prepare );
	}

	/**
	 * Replace a link by <keyword> tag
	 *
	 * @param int $keyword_location_id Id in keyword_locations table.
	 * @param int $post_id             Post id.
	 */
	public static function replace_link_by_keyword( $keyword_location_id, $post_id ) {
		$lasso_cron = new Lasso_Cron();

		$regex            = '#<a\s*data-keyword-location-id="' . $keyword_location_id . '"[^>]*>(.*?)<\/a>#i';
		$keyword_with_tag = '<keyword data-keyword-id="' . $keyword_location_id . '">$1</keyword>';
		$post             = get_post( $post_id );
		$post_content     = $post->post_content;
		$post_content     = preg_replace( $regex, $keyword_with_tag, $post_content );
		$lasso_cron->update_post_content( $post_id, $post_content );
		$post->post_content = $post_content;

		$lasso_cron->check_all_posts_pages( array( $post ) );

		return true;
	}

	/**
	 * Remove <keyword> tag by link
	 *
	 * @param int    $keyword_location_id Id in keyword_locations table.
	 * @param int    $post_id             Post id.
	 * @param string $link                Link.
	 */
	public static function replace_keyword_tag_by_link( $keyword_location_id, $post_id, $link ) {
		$lasso_db = new Lasso_DB();

		$lasso_cron = new Lasso_Cron();
		$lasso_id   = Lasso_Affiliate_Link::get_lasso_post_id_by_url( $link );
		$rel        = Lasso_Affiliate_Link::enable_nofollow_noindex( $lasso_id ) ? 'rel="nofollow"' : '';
		$target     = Lasso_Affiliate_Link::open_new_tab( $lasso_id ) ? 'target="_blank"' : '';

		$keyword_with_tag = '#<keyword\s*data-keyword-id="' . $keyword_location_id . '">(.*?)<\/keyword>#i';
		$a_tag            = '<a data-keyword-location-id="' . $keyword_location_id . '" href="' . $link . '"' . " $rel $target " . '>$1</a>';
		$post             = get_post( $post_id );
		$post_content     = $post->post_content;
		// ? Fix issues of Thrive Architect plugin
		$post_content = Lasso_Helper::get_thrive_plugin_post_content( $post_id, $post_content );
		$post_content = preg_replace( $keyword_with_tag, $a_tag, $post_content );
		$lasso_cron->update_post_content( $post_id, $post_content );
		$lasso_db->delete_keyword_location( $keyword_location_id );
		$post->post_content = $post_content;

		$lasso_cron->check_all_posts_pages( array( $post ) );

		$post_content = Model::get_var(
			Model::prepare(
				'select post_content from ' . Model::get_wp_table_name( 'posts' ) . ' where ID = %d', // phpcs:ignore
				$post_id
			)
		);
		$regex        = '/<a * data-keyword-location-id="' . $keyword_location_id . '" .*? (data-lasso-id)="([0-9]*)"/mi';
		$match        = array();
		preg_match( $regex, $post_content, $match );

		return $match[2] ?? true;
	}

	/**
	 * Process to unmonetize a keyword.
	 *
	 * @param string $keyword             Keyword string.
	 * @param int    $post_id             Post id.
	 * @param int    $keyword_location_id Keyword location id.
	 * @return bool
	 */
	public function unmonetized_keyword( $keyword, $post_id, $keyword_location_id ) {
		try {
			$post_content = get_post_field( 'post_content', $post_id );
			if ( ! empty( $post_content ) ) {
				$old_post_content = $post_content;
				// ? Convert monetized link to keyword.
				$lasso_cron     = new Lasso_Cron();
				$keyword_format = '<keyword data-keyword-id="' . $keyword_location_id . '">' . $keyword . '</keyword>';
				$post_content   = preg_replace( '/<a\s*data-keyword-location-id="' . $keyword_location_id . '"\s*(.*?)>' . $keyword . '<\/a>/i', $keyword_format, $post_content );
				if ( is_null( $post_content ) || empty( $post_content ) ) {
					$post_content = $old_post_content;
				} else {
					$old_post_content = $post_content;
				}

				if ( $post_content ) {
					$lasso_cron->update_post_content( $post_id, $post_content );

					// ? Add keyword to keyword location table again.
					Model::query(
						'
						INSERT IGNORE INTO ' . Model::get_wp_table_name( LASSO_KEYWORD_LOCATIONS ) . " (id, lasso_id, keyword, detection_id)
						VALUES ($keyword_location_id, 0, '$keyword', $post_id);
					"
					);
				}
			}
			return true;
		} catch ( \Exception $e ) {
			return false;
		}

		return false;
	}

	/**
	 * Delete keyword location by id.
	 *
	 * @param int $id Keyword location id.
	 * @return bool
	 */
	public static function delete_keyword_location( $id ) {
		$sql = 'DELETE FROM ' . Model::get_wp_table_name( LASSO_KEYWORD_LOCATIONS ) . ' WHERE id=%s';
		$sql = Model::prepare( $sql, $id ); // phpcs:ignore
		Model::query( $sql );

		return true;
	}
}
