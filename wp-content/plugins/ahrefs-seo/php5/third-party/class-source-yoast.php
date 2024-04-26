<?php

namespace ahrefs\AhrefsSeo\Third_Party;

use ahrefs\AhrefsSeo\Ahrefs_Seo;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Exception;
use ahrefs\AhrefsSeo\Post_Tax;
use ahrefs\AhrefsSeo\Keywords\Data_Keyword;
use Error;
use Exception;
use WP_Post;
use WP_Term;
/**
 * Class for getting details from other SEO plugins.
 *
 * @since 0.8.8
 */
class Source_Yoast extends Source implements Assigned_Keyword, Has_Post_Hooks, Canonical_Url {

	/**
	 * Fill internal variables during initialization
	 *
	 * @return void
	 */
	protected function fill_vars() {
		$this->source_id    = Sources::SOURCE_YOASTSEO;
		$this->is_available = function_exists( '\\YoastSEO' ) && defined( 'WPSEO_VERSION' );
		$this->version      = defined( 'WPSEO_VERSION' ) ? WPSEO_VERSION : null;
	}
	/**
	 * Try to get noindex value from Yoast SEO plugin, version 14+
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return bool|null True - noindex, false - index, null - unknown
	 */
	public function is_noindex( Post_Tax $post_tax ) {
		$result = null;
		try {
			if ( $this->is_available ) {
				if ( version_compare( (string) $this->version, '14.0' ) >= 0 && class_exists( '\\Yoast\\WP\\SEO\\Surfaces\\Meta_Surface' ) ) { // v14.0 and later.
					$robots_array = null;
					/** @var \Yoast\WP\SEO\Surfaces\Meta_Surface $meta */
					$meta   = YoastSEO()->meta; // @phpstan-ignore-line -- Plugin may be active or not exists.
					$values = $post_tax->is_post() ? $meta->for_post( $post_tax->get_post_id() ) : $meta->for_term( $post_tax->get_post_id() );
					if ( $values ) {
						$robots_array = $values->robots;
					}
					if ( ! is_null( $robots_array ) ) {
						$result = in_array( 'noindex', $robots_array, true );
						Ahrefs_Seo::breadcrumbs( sprintf( '%s(%s): %d', __METHOD__, (string) $post_tax, $result ? 1 : 0 ) );
					}
				}
			}
		} catch ( Error $e ) {
			Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s(%s)', __METHOD__, (string) $post_tax ), 0, $e ) );
		} catch ( Exception $e ) {
			Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s(%s)', __METHOD__, (string) $post_tax ), 0, $e ) );
		}
		return $result;
	}
	/**
	 * Get post keyword assigned in Yoast SEO plugin.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return Data_Keyword|null Assigned keyword.
	 */
	public function get_assigned_keyword( Post_Tax $post_tax ) {
		$result = null;
		if ( $this->is_available && class_exists( '\\Yoast\\WP\\SEO\\Repositories\\Indexable_Repository' ) ) {
			try {
				$repository    = \YoastSEO()->classes->get( \Yoast\WP\SEO\Repositories\Indexable_Repository::class );
				$focus_keyword = $repository->query()->select( 'primary_focus_keyword' )->where( 'object_type', $post_tax->is_post() ? 'post' : 'term' )->where( 'object_id', $post_tax->get_post_id() )->limit( 1 )->find_array();
				if ( is_array( $focus_keyword ) && isset( $focus_keyword[0] ) && is_string( $focus_keyword[0]['primary_focus_keyword'] ) ) {
					$result = trim( $focus_keyword[0]['primary_focus_keyword'] );
				}
			} catch ( Error $e ) {
				Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s(%s)', __METHOD__, (string) $post_tax ), 0, $e ) );
			} catch ( Exception $e ) {
				Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s(%s)', __METHOD__, (string) $post_tax ), 0, $e ) );
			}
		}
		return is_null( $result ) ? null : new Data_Keyword( $result, $this->source_id );
	}
	/**
	 * Get canonical url from Yoast SEO plugin.
	 *
	 * @since 0.9.1
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return string|null Canonical URL.
	 */
	public function get_canonical_url( Post_Tax $post_tax ) {
		$result = null;
		if ( $this->is_available && class_exists( '\\Yoast\\WP\\SEO\\Repositories\\Indexable_Repository' ) ) {
			try {
				$repository = \YoastSEO()->classes->get( \Yoast\WP\SEO\Repositories\Indexable_Repository::class );
				$data       = $repository->query()->select( 'canonical' )->where( 'object_type', $post_tax->is_post() ? 'post' : 'term' )->where( 'object_id', $post_tax->get_post_id() )->limit( 1 )->find_array();
				if ( is_array( $data ) && isset( $data[0] ) && is_string( $data[0]['canonical'] ) ) {
					$result = $data[0]['canonical'];
				} elseif ( is_string( $data ) && '' !== $data ) {
					$result = trim( $data );
				}
			} catch ( Error $e ) {
				Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s(%s)', __METHOD__, (string) $post_tax ), 0, $e ) );
			} catch ( Exception $e ) {
				Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s(%s)', __METHOD__, (string) $post_tax ), 0, $e ) );
			}
		}
		Ahrefs_Seo::breadcrumbs( sprintf( '%s(%s)[url:%s]: %s', __METHOD__, (string) $post_tax, $post_tax->get_url( true ), (string) wp_json_encode( $result ) ) );
		return $result;
	}
	/**
	 * Register filters.
	 *
	 * @since 0.9.1
	 *
	 * @return void
	 */
	public function register_post_hooks() {
		add_filter( 'post_link_category', [ $this, 'filter_post_link_category' ], 10, 3 );
	}
	/**
	 * Unregister filters.
	 *
	 * @since 0.9.1
	 *
	 * @return void
	 */
	public function unregister_post_hooks() {
		remove_filter( 'post_link_category', [ $this, 'filter_post_link_category' ], 10 );
	}
	/**
	 * Filters the category that gets used in the %category% permalink token.
	 * Try to load and return the primary category, that was set in Yoast SEO plugin.
	 *
	 * @since 0.9.1
	 *
	 * @param WP_Term      $category  The category to use in the permalink.
	 * @param array        $categories Array of all categories (WP_Term objects) associated with the post.
	 * @param WP_Post|null $post The post in question.
	 *
	 * @return array|null|object|\WP_Error The category we want to use for the post link.
	 */
	public function filter_post_link_category( $category, $categories = [], $post = null ) {
		// Filter function. Do not use parameter types.
		try {
			if ( ! is_null( $post ) ) {
				$post = \get_post( $post );
				if ( ! is_null( $post ) && $post instanceof WP_Post ) {
					$primary_category = null;
					if ( class_exists( '\\WPSEO_Primary_Term' ) ) {
						$primary_term     = new \WPSEO_Primary_Term( 'category', $post->ID );
						$primary_category = $primary_term->get_primary_term();
					}
					if ( ! is_null( $primary_category ) && false !== $primary_category && is_int( $primary_category ) && $primary_category !== $category->term_id ) {
						$value = \get_category( $primary_category );
						if ( ! is_wp_error( $value ) ) {
							$category = $value;
						}
					}
				}
			}
		} catch ( Error $e ) {
			Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s(%s)', __METHOD__, (string) wp_json_encode( func_get_args() ) ), 0, $e ) ); // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.Changed
		} catch ( Exception $e ) {
			Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error in %s(%s)', __METHOD__, (string) wp_json_encode( func_get_args() ) ), 0, $e ) ); // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.Changed
		}
		return $category;
	}
}