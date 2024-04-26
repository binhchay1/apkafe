<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Third_Party;

use ahrefs\AhrefsSeo\Ahrefs_Seo;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Exception;
use ahrefs\AhrefsSeo\Post_Tax;
use ahrefs\AhrefsSeo\Keywords\Data_Keyword;
use Error;
use Exception;
use WP_Term;

/**
 * Class for getting details from other SEO plugins.
 *
 * @since 0.8.8
 */
class Source_Rankmath extends Source implements Assigned_Keyword, Canonical_Url {

	/**
	 * Fill internal variables during initialization
	 *
	 * @return void
	 */
	protected function fill_vars() : void {
		$this->source_id    = Sources::SOURCE_RANKMATH;
		$this->is_available = class_exists( '\\RankMath' );
		if ( $this->is_available ) {
			$this->version = defined( 'RANK_MATH_VERSION' ) ? RANK_MATH_VERSION : 'unknown';
		}
	}

	/**
	 * Try to get noindex value from Yoast SEO plugin, version 14+
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return bool|null True - noindex, false - index, null - unknown
	 */
	public function is_noindex( Post_Tax $post_tax ) : ?bool {
		$result = null;
		try {
			if ( $this->is_available ) {
				if ( class_exists( '\\RankMath\\Helper' )
				&& method_exists( '\\RankMath\\Helper', 'is_post_indexable' ) && method_exists( '\\RankMath\\Helper', 'is_term_indexable' ) ) { // @phpstan-ignore-line -- Plugin may be active or not exists.
					if ( $post_tax->is_post() ) {
						$result = ! \RankMath\Helper::is_post_indexable( $post_tax->get_post_id() );
					} else {
						$term = get_term_by( 'id', $post_tax->get_post_id(), $post_tax->get_taxonomy() );
						if ( $term instanceof WP_Term ) {
							$result = ! \RankMath\Helper::is_term_indexable( $term );
						}
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
	 * Get post keyword assigned in RankMath plugin.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return Data_Keyword|null Assigned keyword.
	 */
	public function get_assigned_keyword( Post_Tax $post_tax ) : ?Data_Keyword {
		$result = null;
		if ( $this->is_available ) {
			$meta = $post_tax->is_post() ? get_post_meta( $post_tax->get_post_id(), 'rank_math_focus_keyword', true ) : get_term_meta( $post_tax->get_post_id(), 'rank_math_focus_keyword', true );
			if ( is_string( $meta ) && '' !== $meta ) {
				if ( false !== strpos( $meta, ',' ) ) {
					$meta = substr( $meta, 0, strpos( $meta, ',' ) ); // get first focus keyphrase from list.
				}
				$result = new Data_Keyword( $meta, $this->source_id );
			}
		}
		return $result;
	}

	/**
	 * Get canonical url from RankMath plugin.
	 * From posts only.
	 *
	 * @since 0.9.1
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return string|null Canonical URL.
	 */
	public function get_canonical_url( Post_Tax $post_tax ) : ?string {
		$result = null;
		if ( $this->is_available ) {
			if ( class_exists( '\\RankMath\\Helper' )
			&& method_exists( '\\RankMath\\Helper', 'get_post_meta' ) && method_exists( '\\RankMath\\Helper', 'get_term_meta' ) ) { // @phpstan-ignore-line -- Plugin may be active or not exists.
				if ( $post_tax->is_post() ) {
					$result = \RankMath\Helper::get_post_meta( 'canonical_url', $post_tax->get_post_id() );
				} else {
					$term = get_term_by( 'id', $post_tax->get_post_id(), $post_tax->get_taxonomy() );
					if ( $term instanceof WP_Term ) {
						$result = \RankMath\Helper::get_term_meta( 'canonical_url', $term, $term->taxonomy );
					}
				}
			} else {
				$result = $post_tax->is_post() ? get_post_meta( $post_tax->get_post_id(), 'rank_math_canonical_url', true ) : get_term_meta( $post_tax->get_post_id(), 'rank_math_canonical_url', true );
			}
		}
		Ahrefs_Seo::breadcrumbs( sprintf( '%s(%s)[url:%s]: %s', __METHOD__, (string) $post_tax, $post_tax->get_url( true ), (string) wp_json_encode( $result ) ) );
		return ( is_string( $result ) && '' !== $result ) ? $result : null;
	}
}
