<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Third_Party\Sources;
use ahrefs\AhrefsSeo\Third_Party\Result_Canonical;
use ahrefs\AhrefsSeo\Third_Party\Result_Redirected;
use ahrefs\AhrefsSeo\Options\Countries;
use stdClass;
use WP_Error;
use WP_Post;
use WP_Taxonomy;
use WP_Term;
/**
 * Helper for work with content
 *
 * @since 0.8.2
 */
class Helper_Content {

	const LANG_PREFIX = 'lng:';
	/**
	 * Get class instance
	 *
	 * @return Helper_Content
	 */
	public static function get() {
		return new self();
	}
	/**
	 * Is WPML plugin active
	 *
	 * @since 0.8.4
	 *
	 * @return bool
	 */
	protected static function is_wpml_active() {
		static $result = null;
		if ( is_null( $result ) ) {
			$result = defined( 'ICL_SITEPRESS_VERSION' ) && isset( $GLOBALS['sitepress'] ) && class_exists( '\\SitePress' ) && $GLOBALS['sitepress'] instanceof \SitePress;
		}
		return $result;
	}
	/**
	 * Get info about used languages
	 *
	 * @since 0.8.4
	 *
	 * @return array Array {
	 *
	 *   @type string $lang Blog language.
	 *   @type array|null $wpml Sitepress plugin short info.
	 * }
	 */
	public static function get_info() {
		static $result = null;
		if ( is_null( $result ) ) {
			$result = [ 'lang' => get_bloginfo( 'language' ) ];
			if ( self::is_wpml_active() ) {
				$result['wpml'] = [
					'version' => defined( 'ICL_SITEPRESS_VERSION' ) ? ICL_SITEPRESS_VERSION : '(unknown)',
					'lang'    => defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '(unknown)',
				];
			}
		}
		return $result;
	}
	/**
	 * Redirect wrapper
	 *
	 * @since 0.9.2
	 *
	 * @param string $location Redirect location.
	 * @return void
	 */
	public static function wp_redirect( $location ) {
		add_filter( 'allowed_redirect_hosts', [ self::class, 'redirect_filter' ] );
		wp_safe_redirect( $location );
		remove_filter( 'allowed_redirect_hosts', [ self::class, 'redirect_filter' ] );
	}
	/**
	 * Callback. Allow domain of backend for redirect.
	 *
	 * @since 0.9.2
	 *
	 * @param string[] $hosts An array of allowed host names.
	 * @return string[]
	 */
	public static function redirect_filter( $hosts ) {
		// Callback, do not use parameter types.
		$domain_home = wp_parse_url( get_home_url(), PHP_URL_HOST ); // frontend domain, allowed by wp_safe_redirect.
		$domain_site = wp_parse_url( get_site_url(), PHP_URL_HOST ); // backend domain (if different) is not allowed by wp_safe_redirect.
		if ( $domain_home !== $domain_site ) {
			$hosts[] = $domain_site;
		}
		return $hosts;
	}
	/**
	 * Get post permalink
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public function get_post_permalink( $post_id ) {
		try {
			Sources::register_post_hooks();
			$result = get_permalink( $post_id ) ?: '';
		} finally {
			Sources::unregister_post_hooks();
		}
		return $result;
	}
	/**
	 * Get edit link for post
	 *
	 * @since 0.8.4
	 *
	 * @param int $post_id Post ID.
	 * @return string|null
	 */
	public function get_edit_post_link( $post_id ) {
		$result = get_edit_post_link( $post_id );
		return is_string( $result ) ? $result : null;
	}
	/**
	 * Get edit link for term
	 *
	 * @since 0.8.4
	 *
	 * @param int    $term_id Term ID.
	 * @param string $taxonomy Taxonomy.
	 * @return string|null
	 */
	public function get_edit_term_link( $term_id, $taxonomy = 'category' ) {
		$result = get_edit_term_link( $term_id, $taxonomy );
		return is_string( $result ) ? $result : null;
	}
	/**
	 * Get term permalink
	 *
	 * @param int    $term_id  Term ID.
	 * @param string $taxonomy Taxonomy.
	 * @return string
	 */
	public function get_term_link( $term_id, $taxonomy = 'category' ) {
		$result = get_term_link( $term_id, $taxonomy );
		return is_string( $result ) ? $result : '';
	}
	/**
	 * Get canonical url and source
	 *
	 * @since 0.9.1
	 *
	 * @param Post_Tax $post_tax Post tax item.
	 * @return Result_Canonical
	 */
	public function get_canonical_data( Post_Tax $post_tax ) {
		$sources = Sources::get()->get_canonical_url_sources();
		if ( count( $sources ) ) {
			foreach ( $sources as $source ) {
				$result = $source->get_canonical_url( $post_tax );
				if ( is_string( $result ) && '' !== $result ) {
					return new Result_Canonical( $post_tax, $result, $source->get_source_id() );
				}
			}
		}
		return new Result_Canonical( $post_tax, null, null );
	}
	/**
	 * Get redirected url
	 *
	 * @since 0.9.2
	 *
	 * @param Post_Tax $post_tax Post tax item.
	 * @return Result_Redirected
	 */
	public function get_redirected_data( Post_Tax $post_tax ) {
		$sources = Sources::get()->get_redirected_url_sources();
		if ( count( $sources ) ) {
			foreach ( $sources as $source ) {
				$result = $source->get_redirected_url( $post_tax );
				if ( is_string( $result ) && '' !== $result ) {
					return new Result_Redirected( $post_tax, $result, $source->get_source_id() );
				}
			}
		}
		return new Result_Redirected( $post_tax, null, null );
	}
	/**
	 * Get post categories or product categories list
	 *
	 * @param string   $taxonomy Taxonomy.
	 * @param string   $prefix Prefix for title with post or category info.
	 * @param int|null $limit Max results number.
	 *
	 * @return array<WP_Term|stdClass>
	 * @see get_terms()
	 */
	public function get_categories( $taxonomy, $prefix, $limit = null ) {
		$args = [
			'fields'     => 'ids',
			'hide_empty' => false,
			'taxonomy'   => $taxonomy,
		];
		if ( ! is_null( $limit ) ) {
			$args['number'] = $limit;
		}
		/** @var WP_Term[]|WP_Error $result -- we use 'fields:ids' */
		$result = get_terms( $args );
		return is_array( $result ) ? $result : [];
	}
	/**
	 * Get terms
	 *
	 * @since 0.8.4
	 *
	 * @see get_terms()
	 *
	 * @param array $args Arguments list for get_terms() call.
	 * @return WP_Term[]|int[]|array<int,string>
	 */
	public function get_terms( array $args ) {
		$result = get_terms( $args );
		return is_array( $result ) ? $result : [];
	}
	/**
	 * Get currently selected language.
	 *
	 * @param bool $raw_value If false - return empty string instead of 'All', as used for SQL.
	 * @return string|null
	 */
	public function get_lang( $raw_value = false ) {
		return null;
	}
	/**
	 * Show taxonomy terms checklist
	 *
	 * @param string         $taxonomy  Taxonomy.
	 * @param int[]|string[] $selected_cats Selected categories or terms list.
	 * @return void
	 */
	public function terms_checklists( $taxonomy, array $selected_cats ) {
		wp_terms_checklist(
			0,
			[
				'taxonomy'             => $taxonomy,
				'descendants_and_self' => 0,
				'selected_cats'        => $selected_cats,
				'checked_ontop'        => false,
			]
		);
	}
	/**
	 * Get all existing taxonomy terms
	 *
	 * @param string $taxonomy Taxonomy.
	 * @return int[] List of term id.
	 */
	public function get_all_term_ids( $taxonomy ) {
		$args = [
			'taxonomy'   => $taxonomy,
			'fields'     => 'ids',
			'hide_empty' => false,
		];
		/** @var int[]|WP_Error $result */
		$result = get_terms( $args );
		return is_array( $result ) ? $result : [];
	}
	/**
	 * Get posts list
	 *
	 * @see get_posts()
	 *
	 * @param array $args Arguments list for get_posts() call.
	 * @return WP_Post[]|int[]
	 */
	public function get_posts( array $args ) {
		$result = get_posts( $args );
		return is_wp_error( $result ) ? [] : $result;
	}
	/**
	 * Get post
	 *
	 * @since 0.8.4
	 *
	 * @param int $post_id Post ID.
	 * @return WP_Post|null
	 */
	public function get_post( $post_id ) {
		$result = get_post( $post_id );
		return is_object( $result ) && $result instanceof WP_Post ? $result : null;
	}
	/**
	 * Get term field
	 *
	 * @param string $field Term field name.
	 * @param int    $term_id Term ID.
	 * @param string $taxonomy Term taxonomy.
	 *
	 * @return string|int|null|WP_Error value of term's field or \WP_error if taxonomy not exists.
	 * @since 0.8.4
	 */
	public function get_term_field( $field, $term_id, $taxonomy ) {
		// do not use return type here -- can return different types.
		return get_term_field( $field, $term_id, $taxonomy );
	}
	/**
	 * Get terms of post
	 *
	 * @since 0.8.4
	 *
	 * @param int    $post_id Post ID.
	 * @param string $taxonomy Taxonomy of terms.
	 * @return WP_Term[]
	 */
	public function get_the_terms( $post_id, $taxonomy = 'category' ) {
		$result = get_the_terms( $post_id, $taxonomy );
		return is_array( $result ) ? $result : [];
	}
	/**
	 * Displays a categories drop-down for filtering on the Posts list table.
	 *
	 * @param string $current_post_type Currently selected post type (from Categories filter) or empty string.
	 * @param string $current_category Current category id (from Categories filter) or empty string.
	 * @param string $cat_value_raw Current raw value of category box.
	 * @param bool   $is_lang_used Some language code used.
	 * @return void
	 */
	public function categories_dropdown( $current_post_type = '', $current_category = '', $cat_value_raw = '', $is_lang_used = false ) {
		$tax              = get_taxonomy( 'category' );
		$d                = Ahrefs_Seo_Data_Content::CAT_FILTER_DIVIDER; // short name for divider.
		$dropdown_options = [
			'taxonomy'        => 'category',
			'show_option_all' => $tax instanceof WP_Taxonomy && property_exists( $tax->labels, 'all_items' ) ? (string) $tax->labels->all_items : '',
			'hide_empty'      => 0,
			'hierarchical'    => 1,
			'show_count'      => 0,
			'orderby'         => 'name',
		];
		unset( $tax );
		if ( in_array( $current_post_type, [ 'post', 'page' ], true ) && ! empty( $current_category ) ) {
			$dropdown_options['selected'] = $current_category;
		}
		?>
		<label class="screen-reader-text" for="cat">
		<?php
		__( 'Filter by category', 'ahrefs-seo' );
		?>
		</label>
		<select name="cat" id="cat" class="postform">
			<?php
		/** @var array<WP_Term|stdClass> $items */
			$items         = [];
			$item          = new stdClass();
			$item->term_id = '000';
			$item->name    = __( 'All Categories', 'ahrefs-seo' );
			$item->parent  = '0';
			$items[]       = $item;
		// Posts.
			$item          = new stdClass();
			$item->term_id = "cat{$d}0";
			$item->name    = __( 'Posts', 'ahrefs-seo' );
			$item->parent  = '0';
			$items[]       = $item;
			$categories    = get_terms( $dropdown_options );
			if ( is_array( $categories ) ) {
				/** @var WP_Term[] $categories */
				foreach ( $categories as $cat ) {
					$item          = new stdClass();
					$item->term_id = "cat{$d}" . $cat->term_id;
					$item->name    = $cat->name;
					$item->count   = $cat->count;
					$item->parent  = "cat{$d}" . $cat->parent;
					$items[]       = $item;
				}
			}
		// Pages.
			$content       = new Ahrefs_Seo_Content_Settings();
			$item          = new stdClass();
			$item->term_id = "page{$d}0";
			$item->name    = __( 'Pages', 'ahrefs-seo' );
			$item->parent  = '0';
			$items[]       = $item;
		// Products.
			if ( $content->products_exists() ) {
				$item          = new stdClass();
				$item->term_id = "product{$d}0";
				$item->name    = __( 'Products', 'ahrefs-seo' );
				$item->parent  = '0';
				$items[]       = $item;
				unset( $dropdown_options['selected'] );
				if ( 'product' === $current_post_type && ! empty( $current_category ) ) {
					$dropdown_options['selected'] = $current_category;
				}
				$dropdown_options['taxonomy'] = 'product_cat';
				$categories                   = get_terms( $dropdown_options );
				if ( is_array( $categories ) ) {
					/** @var WP_Term[] $categories */
					foreach ( $categories as $cat ) {
						$item          = new stdClass();
						$item->term_id = "product{$d}" . $cat->term_id;
						$item->name    = $cat->name;
						$item->count   = $cat->count;
						$item->parent  = "product{$d}" . $cat->parent;
						$items[]       = $item;
					}
				}
			}
		// All existing public custom post types.
			$all_cpt = ( new Ahrefs_Seo_Content_Settings() )->get_custom_post_types();
			foreach ( $all_cpt as $post_type => $title ) {
				$item          = new stdClass();
				$item->term_id = "{$post_type}{$d}0";
				$item->name    = $title;
				$item->parent  = '0';
				$items[]       = $item;
			}
		// All Category Pages (all items from taxonomies).
			$item          = new stdClass();
			$item->term_id = "tax:{$d}0";
			$item->name    = __( 'All Category Pages', 'ahrefs-seo' );
			$item->parent  = '0';
			$items[]       = $item;
			if ( $content->products_exists() ) { // show subitems only when it makes sense.
				// Category (all items from taxonomy Category).
				$item          = new stdClass();
				$item->term_id = "tax:category{$d}0";
				$item->name    = __( 'Post Category Pages', 'ahrefs-seo' );
				$item->parent  = "tax:{$d}0";
				$items[]       = $item;
				// Products (all items from taxonomy Product Category).
				$item          = new stdClass();
				$item->term_id = "tax:product_cat{$d}0";
				$item->name    = __( 'Product Category Pages', 'ahrefs-seo' );
				$item->parent  = "tax:{$d}0";
				$items[]       = $item;
				// Tags (all items from taxonomy Post Tags).
				$item          = new stdClass();
				$item->term_id = "tax:post_tag{$d}0";
				$item->name    = __( 'Tag Pages', 'ahrefs-seo' );
				$item->parent  = "tax:{$d}0";
				$items[]       = $item;
			}
			$r = [
				'depth'       => 0,
				'orderby'     => 'id',
				'order'       => 'ASC',
				'show_count'  => 0,
				'selected'    => $cat_value_raw,
				'name'        => 'cat',
				'id'          => '',
				'class'       => 'postform',
				'tab_index'   => 0,
				'value_field' => 'term_id',
			];
			echo walk_category_dropdown_tree( $items, 0, $r ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
			?>
		</select>
		<?php
	}
	/**
	 * Add language parameter to link
	 *
	 * @since 0.8.4
	 *
	 * @param string      $url Internal url.
	 * @param string|null $lang Language code or null for current language.
	 * @return string
	 */
	public function update_link( $url, $lang = null ) {
		return $url;
	}
	/**
	 * Get language name by code.
	 *
	 * @since 0.8.4
	 *
	 * @param string $lang Language code.
	 * @return string
	 */
	public function get_language_by_code( $lang ) {
		return $lang;
	}
	/**
	 * Get language code for post or term.
	 *
	 * @since 0.8.4
	 *
	 * @param Post_Tax $post_tax Post Tax item.
	 * @return string|null
	 */
	public function get_lang_code( Post_Tax $post_tax ) {
		return null;
	}
	/**
	 * Get url of flag image
	 *
	 * @since 0.8.4
	 *
	 * @param string $code Language code.
	 * @return string Empty string if not found.
	 */
	public function get_flag_url( $code ) {
		return '';
	}
	/**
	 * Return all existing (language code => name) pairs.
	 *
	 * @since 0.8.4
	 *
	 * @return array<string, string>
	 */
	public function get_all_languages() {
		return [];
	}
	/**
	 * Apply filters to sql "where".
	 *
	 * @since 0.8.4
	 *
	 * @param string[] $additional_where Where strings.
	 * @param array    $filters Array with filters.
	 */
	public function where_filters( array &$additional_where, array $filters ) {
	}
	/**
	 * Apply filters to sql "from" part.
	 *
	 * @since 0.8.4
	 *
	 * @param string[] $additional_where Where strings.
	 * @param array    $filters Array with filters.
	 */
	public function from_filters( array &$additional_where, array $filters ) {
	}
	/**
	 * Get country code for the item
	 *
	 * @since 0.9.6
	 *
	 * @param Post_Tax $post_tax Post or taxonomy.
	 * @return string
	 */
	public function get_country_code( Post_Tax $post_tax ) {
		$snapshot_id = $post_tax->get_snapshot_id();
		if ( empty( $snapshot_id ) ) {
			return ( new Countries() )->get_country();
		}
		return ( new Snapshot() )->get_country_code( $snapshot_id );
	}
}