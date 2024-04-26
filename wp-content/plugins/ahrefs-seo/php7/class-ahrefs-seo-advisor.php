<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo;

use WP_Post;

/**
 * Class for recommendations about relevant pages and internal links.
 */
class Ahrefs_Seo_Advisor {

	/**
	 * @var Ahrefs_Seo_Advisor
	 */
	private static $instance = null;

	/**
	 * @var Ahrefs_Seo_Keywords
	 */
	private $data_keywords;

	/**
	 * Return the instance
	 *
	 * @return Ahrefs_Seo_Advisor
	 */
	public static function get() : Ahrefs_Seo_Advisor {
		if ( null === self::$instance ) {
			self::$instance = new self( Ahrefs_Seo_Keywords::get() );
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @param Ahrefs_Seo_Keywords $keywords Keywords instance.
	 */
	public function __construct( Ahrefs_Seo_Keywords $keywords ) {
		$this->data_keywords = $keywords;
	}

	/**
	 * Get well performing relevant pages.
	 * Use "Well performing" folder.
	 *
	 * @param Post_Tax $post_tax Source post or term.
	 * @param int      $limit Max results limit.
	 * @return Post_Tax[]|null List of posts or terms.
	 */
	public function find_relevant_top_performing_pages( Post_Tax $post_tax, int $limit = 20 ) : ?array {
		return $this->find_posts_with_same_keys( $post_tax, [ Ahrefs_Seo_Data_Content::ACTION4_DO_NOTHING ], $limit );
	}

	/**
	 * Get low performing relevant pages.
	 * Use "Underperforming" and "Non-performing" folders.
	 *
	 * @param Post_Tax $post_tax Source post or term.
	 * @param int      $limit Max results limit.
	 *
	 * @return Post_Tax[]|null List of posts or terms.
	 */
	public function find_relevant_under_performing_pages( Post_Tax $post_tax, int $limit = 20 ) : ?array {
		return $this->find_posts_with_same_keys(
			$post_tax,
			[
				Ahrefs_Seo_Data_Content::ACTION4_UPDATE_YELLOW,
				Ahrefs_Seo_Data_Content::ACTION4_MERGE,
				Ahrefs_Seo_Data_Content::ACTION4_EXCLUDE,
				Ahrefs_Seo_Data_Content::ACTION4_UPDATE_ORANGE,
				Ahrefs_Seo_Data_Content::ACTION4_REWRITE,
			],
			$limit,
			false
		);
	}

	/**
	 * Find active pages with the same keyword.
	 *
	 * @param Post_Tax $post_tax     Search posts with same keyword as this.
	 * @param int      $limit Max results limit.
	 * @return Post_Tax[]|null List of posts or terms or empty result.
	 */
	public function find_active_pages_with_same_keyword( Post_Tax $post_tax, int $limit = 1000 ) : ?array {
		return $this->find_posts_with_same_keys(
			$post_tax,
			[
				Ahrefs_Seo_Data_Content::ACTION4_DO_NOTHING,
				Ahrefs_Seo_Data_Content::ACTION4_UPDATE_YELLOW,
				Ahrefs_Seo_Data_Content::ACTION4_MERGE,
				Ahrefs_Seo_Data_Content::ACTION4_EXCLUDE,
				Ahrefs_Seo_Data_Content::ACTION4_REWRITE,
				Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_FINAL, // also active status on last step of each audit.
			],
			$limit,
			false
		);
	}

	/**
	 * Has active pages (not from "Excluded" or "Not analyzed") with same keyword?
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return bool
	 */
	public function has_active_pages_with_same_keywords( Post_Tax $post_tax ) : bool {
		$data = $this->find_active_pages_with_same_keyword( $post_tax, 1 );
		return ! empty( $data );
	}

	/**
	 * Find posts with same keys.
	 * Do not include inactive items.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param string[] $actions_filter List of Ahrefs_Seo_Data_Content::ACTION_xxx constants.
	 * @param int      $limit Max links count.
	 * @param bool     $traffic_order_desc Order results by: True: Traffic desc, False: Traffic asc.
	 *
	 * @return Post_Tax[]|null List of posts or terms.
	 */
	private function find_posts_with_same_keys( Post_Tax $post_tax, array $actions_filter = [], int $limit = 100, bool $traffic_order_desc = true ) : ?array {
		global $wpdb;
		// post keywords.
		$key = $this->data_keywords->post_keyword_get( $post_tax );
		if ( empty( $key ) ) {
			return null;
		}
		Ahrefs_Seo::breadcrumbs( sprintf( '%s (%s) (%s)', __METHOD__, (string) $post_tax, $key ) );

		$placeholders_actions = '';
		if ( ! empty( $actions_filter ) ) {
			$placeholders_actions = ' AND action IN ( ' . implode( ',', array_fill( 0, count( $actions_filter ), '%s' ) ) . ' )';
		}
		$sql = $wpdb->prepare( "SELECT post_id, taxonomy, keyword, action, organic_month as traffic, snapshot_id FROM {$wpdb->ahrefs_content} WHERE snapshot_id = %d AND (post_id <> %d OR taxonomy <> %s) $placeholders_actions AND inactive = 0 AND keyword = %s LIMIT %d", array_merge( [ $post_tax->get_snapshot_id(), $post_tax->get_post_id(), $post_tax->get_taxonomy() ], $actions_filter, [ $key, $limit ] ) ); // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$items = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( empty( $items ) ) {
			return null;
		}

		$data = $items;
		usort(
			$data,
			function( $a, $b ) use ( $traffic_order_desc ) {
				// then order by social monthly traffic.
				return $traffic_order_desc ? ( intval( $b['traffic'] ) <=> intval( $a['traffic'] ) ) : ( intval( $a['traffic'] ) <=> intval( $b['traffic'] ) );
			}
		);

		return array_slice(
			array_map(
				function( $row ) {
					return Post_Tax::create_from_array( $row );
				},
				$data
			),
			0,
			$limit
		);
	}

	/**
	 * Find posts and menus which contain desired url.
	 * Limits:
	 *  - search using full url in post's (post, page, any CPT) content with published status;
	 *  - search using full url in terms content;
	 *  - search using full url or just a slug in menus.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @param int      $limit Max links count.
	 * @return array<array{title:string, url:string|null, url_edit:string|null}>
	 */
	public function find_internal_links( Post_Tax $post_tax, int $limit = 1000 ) : array {
		global $wpdb;
		$result = [];
		$url    = $post_tax->get_url();
		$slug   = wp_parse_url( $url, PHP_URL_PATH );
		$search = '%' . $wpdb->esc_like( $url ) . '%';

		// search in posts, pages and other CPT.
		$pages = $wpdb->get_results( $wpdb->prepare( "SELECT ID as id, post_title as title, post_type FROM {$wpdb->posts} WHERE post_content LIKE %s AND post_status = 'publish' LIMIT %d", $search, $limit ), ARRAY_A );
		if ( $pages ) {
			array_walk(
				$pages,
				function( $item, $key ) use ( &$result ) {
					if ( is_post_type_viewable( $item['post_type'] ) ) {
						$pt        = new Post_Tax( intval( $item['id'] ), '' );
						$link_edit = $pt->user_can_edit() ? $pt->get_url_edit() : null;
						$link      = $pt->get_url();
						if ( strlen( $link ) ) {
							$result[] = [
								'title'    => strlen( $item['title'] ) ? $item['title'] : "{$item['post_type']} #{$item['id']}",
								'url'      => $link,
								'url_edit' => $link_edit,
							];
						}
					}
				}
			);
			unset( $pages );
		}

		// search in terms.
		$terms = $wpdb->get_results( $wpdb->prepare( "SELECT tt.term_id, tt.taxonomy, t.name FROM {$wpdb->term_taxonomy} tt JOIN {$wpdb->terms} t ON tt.term_id = t.term_id WHERE tt.description LIKE %s LIMIT %d", $search, $limit ), ARRAY_A );
		if ( $terms ) {
			array_walk(
				$terms,
				function( $item, $key ) use ( &$result ) {
					if ( is_taxonomy_viewable( $item['taxonomy'] ) ) {
						$pt        = new Post_Tax( intval( $item['term_id'] ), $item['taxonomy'] );
						$link_edit = $pt->user_can_edit() ? $pt->get_url_edit() : null;
						$link      = $pt->get_url();
						if ( strlen( $link ) ) {
							$result[] = [
								'title'    => strlen( $item['name'] ) ? $item['name'] : "Term #{$item['term_id']} of {$item['taxonomy']}",
								'url'      => $link,
								'url_edit' => $link_edit,
							];
						}
					}
				}
			);
			unset( $terms );
		}

		// search in menus.
		$menus = wp_get_nav_menus();
		if ( $menus ) {
			foreach ( $menus as $menu ) {
				$menu_items = wp_get_nav_menu_items( $menu->term_id );
				$count      = 0;
				foreach ( (array) $menu_items as $menu_item ) {
					/** @psalm-suppress UndefinedMagicPropertyFetch */
					if ( $menu_item instanceof WP_Post && property_exists( $menu_item, 'url' ) && ( $url === $menu_item->url || false !== $slug && $slug === $menu_item->url ) ) { // @phpstan-ignore-line -- This is not exactly WP_Post, but instance filled with 'url' and some other properties.
						$count++;
					}
				}
				if ( $count > 0 ) {
					$result[] = [
						/* translators: %d: number of found link in menu */
						'title'    => $menu->name . ' ' . sprintf( _n( '(menu with %d link)', '(menu with %d links)', $count, 'ahrefs-seo' ), $count ),
						'url'      => null,
						'url_edit' => add_query_arg( 'menu', $menu->term_id, admin_url( 'nav-menus.php' ) ),
					];
				}
			}
		}
		return $result;
	}

	/**
	 * Item has assigned keyword.
	 *
	 * @since 0.8.0
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return bool
	 */
	public function has_keyword( Post_Tax $post_tax ) : bool {
		$keyword = Post_Tax_With_Keywords::create_from( $post_tax )->load_from_db()->get_keyword_current();
		return ! is_null( $keyword ) && ( '' !== $keyword );
	}

	/**
	 * Fill details for current and other possible keywords.
	 *
	 * @param Post_Tax    $post_tax In parameter.
	 * @param string|null $keyword_current Fill with current keyword or empty string.
	 * @param array       $keyword_info Fill with current keyword's data or empty array.
	 * @param array       $items Fill with other possible keyword's data.
	 *
	 * @return void
	 * @since 0.9.8
	 */
	public function fill_current_and_possible_keywords_data( Post_Tax $post_tax, ?string &$keyword_current, array &$keyword_info, array &$items ) : void {
		$keyword_current = Post_Tax_With_Keywords::create_from( $post_tax )->load_from_db()->get_keyword_current();
		$keyword_info    = []; // what to show in first table.
		$items           = []; // what to show in second table.
		if ( $this->has_keyword( $post_tax ) ) {
			$kw_data = Ahrefs_Seo_Keywords::get()->get_other_keywords( $post_tax, false );
			if ( ! is_null( $kw_data ) ) {
				// search info for the current keyword.
				$keywords     = $kw_data->get_keywords();
				$keyword_info = [
					'query'  => $keyword_current,
					'impr'   => null,
					'clicks' => null,
					'pos'    => null,
				];
				if ( is_array( $keywords ) && isset( $keywords['result'] ) && is_array( $keywords['result'] ) ) {
					foreach ( $keywords['result'] as $key => $row ) {
						if ( $keyword_current === $row['query'] ) {
							$keyword_info = $row;
							unset( $keywords['result'][ $key ] );
							break;
						}
					}
					// what to show at the second table.
					$items = array_filter(
						$keywords['result'],
						function( $row ) {
							return ( $row['pos'] > 3.5 ) && ( $row['pos'] < 20.5 ); // filter pos 4-20 only.
						}
					);
				}
				unset( $kw_data );
			}
		}
	}

}
