<?php
/**
 * Lasso Post Type - Hook.
 *
 * @package Pages
 */

namespace Lasso\Pages\Post_Type;

use Lasso\Classes\Post_Type as Lasso_Post_Type;
use Lasso\Models\Revert;

/**
 * Lasso Post Type - Hook.
 */
class Hook {
	/**
	 * Declare "Lasso register hook events" to WordPress.
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function register_hooks() {
		$lasso_post_type = new Lasso_Post_Type();

		add_action( 'init', array( $lasso_post_type, 'add_lasso_urls_post_type' ) );
		add_action( 'current_screen', array( $lasso_post_type, 'add_new_redirect_to_affiate_link' ), 10, 1 );

		// ? use Amazon product url instead Lasso url when search/insert a link into a post/page
		add_filter( 'wp_link_query', array( $lasso_post_type, 'use_amazon_url_instead_lasso_url' ), 100 );
		add_filter( 'rest_pre_echo_response', array( $lasso_post_type, 'use_amazon_url_instead_lasso_url_gutenberg' ), 100, 3 );

		// ? add Lasso button into TinyMCE
		add_filter( 'mce_external_plugins', array( $lasso_post_type, 'lasso_add_tinymce_plugin' ) );
		add_filter( 'mce_buttons', array( $lasso_post_type, 'lasso_register_my_tc_button' ) );
		add_filter( 'tiny_mce_before_init', array( $lasso_post_type, 'tinymce_init' ) );

		// ? remove slug
		add_action( 'pre_get_posts', array( $lasso_post_type, 'custom_post_request' ) );
		add_action( 'pre_get_posts', array( $lasso_post_type, 'hide_post_in_author_page' ) );
		add_filter( 'post_type_link', array( $lasso_post_type, 'remove_custom_slug' ), 10, 3 );

		// ? lasso gutenberg block
		add_action( 'enqueue_block_editor_assets', array( $lasso_post_type, 'lasso_gutenberg_block' ) );

		// ? Remove Lasso URLs from Yoast Sitemap
		add_filter( 'wpseo_sitemap_exclude_post_type', array( $this, 'sitemap_exclude_post_type' ), 10, 2 );
		add_filter( 'wpseo_sitemap_exclude_taxonomy', array( $this, 'sitemap_exclude_taxonomy' ), 10, 2 );

		add_filter( 'rank_math/sitemap/exclude_taxonomy', array( $this, 'rankmath_exclude_taxonomy' ), 10, 2 );
		add_filter( 'rank_math/sitemap/exclude_post_type', array( $this, 'rankmath_exclude_post_type' ), 10, 2 );

		add_filter( 'rocket_cpcss_excluded_post_types', array( $this, 'rocket_cpcss_excluded_post_types' ), 10, 1 );
		add_filter( 'rocket_cpcss_excluded_taxonomies', array( $this, 'rocket_cpcss_excluded_taxonomies' ), 10, 1 );

		// ? Remove out imported links from Pretty Link dashboard search results
		add_filter( 'posts_search', array( $this, 'remove_out_imported_link_from_pretty_link_search' ), 5, 1 );
	}

	/**
	 * Remove Lasso URLs from Yoast Sitemap
	 *
	 * @param boolean $false Defaults to false.
	 * @param string  $post_type Post type name.
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function sitemap_exclude_post_type( $false, $post_type ) {
		return LASSO_POST_TYPE === $post_type;
	}

	/**
	 * Remove Lasso URLs from Yoast Sitemap
	 *
	 * @param boolean $false Defaults to false.
	 * @param string  $taxonomy Name of the taxonomy to exclude.
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function sitemap_exclude_taxonomy( $false, $taxonomy ) {
		return LASSO_CATEGORY === $taxonomy;
	}

	/**
	 * Remove Lasso category from RankMath Sitemap
	 *
	 * @param boolean $exclude        Defaults to true.
	 * @param array   $taxonomy_names Array of names for the taxonomies being processed.
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function rankmath_exclude_taxonomy( $exclude, $taxonomy_names ) {
		return LASSO_CATEGORY === $taxonomy_names;
	}

	/**
	 * Remove Lasso URLs from RankMath Sitemap
	 *
	 * @param bool   $exclude Default false.
	 * @param string $type    Post type name.
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function rankmath_exclude_post_type( $exclude, $type ) {
		return LASSO_POST_TYPE === $type;
	}

	/**
	 * Filters the post types excluded from critical CSS generation.
	 *
	 * @since 2.11
	 *
	 * @param array $excluded_post_types An array of post types names.
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function rocket_cpcss_excluded_post_types( $excluded_post_types ) {
		$excluded_post_types[] = LASSO_POST_TYPE;

		return $excluded_post_types;
	}

	/**
	 * Filters the taxonomies excluded from critical CSS generation.
	 *
	 * @since  2.11
	 *
	 * @param array $excluded_taxonomies An array of taxonomies names.
	 *
	 * @return array
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function rocket_cpcss_excluded_taxonomies( $excluded_taxonomies ) {
		$excluded_taxonomies[] = LASSO_CATEGORY;

		return $excluded_taxonomies;
	}

	/**
	 * Remove out imported links from Pretty Link dashboard search results
	 *
	 * @param string $where Where SQL.
	 * @return string
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function remove_out_imported_link_from_pretty_link_search( $where ) {
		global $typenow;

		$pretty_link_post_type = 'pretty-link';
		if ( $pretty_link_post_type === $typenow ) {
			$sql = ' 
				AND ' . Revert::get_wp_table_name( 'posts' ) . '.ID NOT IN (
					SELECT lasso_id 
					FROM ' . ( new Revert() )->get_table_name() . '
					WHERE plugin = %s
				)
			';
			$sql = Revert::prepare( $sql, $pretty_link_post_type );

			$where .= $sql;
		}

		return $where;
	}
}
