<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Third_Party\Sources;
use ahrefs\AhrefsSeo\Third_Party\Result_Canonical;
use ahrefs\AhrefsSeo\Third_Party\Result_Noindex;
use ahrefs\AhrefsSeo\Third_Party\Result_Redirected;
/**
 * Detect:
 * - is page noindex
 * - is page non-canonical
 */
class Ahrefs_Seo_Noindex extends Ahrefs_Seo_Abstract_Api {

	/**
	 * Analyze settings of popular SEO plugins and WordPress blog option.
	 *
	 * @since 0.7.3
	 *
	 * @param array<string,string> $page_id_to_url_list Associative array, page_tax_string => url.
	 * @return array<string,Result_Noindex> Associative array page_tax_string => Result_Noindex.
	 */
	public function is_noindex( array $page_id_to_url_list ) {
		$results = [];
		$sources = Sources::get()->get_noindex_sources();
		foreach ( $page_id_to_url_list as $post_tax_string => $url ) {
			// try to load noindex from SEO plugins.
			$result   = null;
			$post_tax = Post_Tax::create_from_string( $post_tax_string );
			foreach ( $sources as $source ) {
				if ( $source->is_available() ) {
					$noindex = $source->is_noindex( $post_tax );
					if ( ! is_null( $noindex ) ) {
						$result = ( new Result_Noindex( $post_tax, $url, $source->get_source_id() ) )->set_is_noindex( $noindex ? 1 : 0 );
						break;
					}
				}
			}
			$results[ $post_tax_string ] = isset( $result ) ? $result : ( new Result_Noindex( $post_tax, $url, null ) )->set_is_noindex( 0 );
			// indexed by default.
		}
		return $results;
	}
	/**
	 * Analyze settings of popular SEO plugins and WordPress blog option.
	 *
	 * @since 0.9.1
	 *
	 * @param array<string,string> $page_id_to_url_list Associative array, page_tax_string => url.
	 * @return array<string, Result_Canonical> Associative array page_tax_string => Result_Canonical.
	 */
	public function is_noncanonical( array $page_id_to_url_list ) {
		$results = [];
		foreach ( $page_id_to_url_list as $post_tax_string => $url ) {
			$is_noncanonical = false;
			$post_tax        = Post_Tax::create_from_string( $post_tax_string );
			// both urls with applied filters.
			$result_canonical       = $post_tax->get_canonical_data();
			$original_canonical_url = $result_canonical->get_url_filtered();
			$original_url           = $post_tax->get_url( true );
			if ( ! is_null( $original_canonical_url ) ) {
				// compare it with the current url.
				$is_noncanonical = ! $this->is_same_urls( $original_canonical_url, $original_url );
			}
			$results[ $post_tax_string ] = $result_canonical->set_is_noncanonical( $is_noncanonical ? 1 : 0 );
		}
		return $results;
	}
	/**
	 * Analyze settings of popular SEO plugins and WordPress blog option.
	 *
	 * @since 0.9.2
	 *
	 * @param array<string,string> $page_id_to_url_list Associative array, page_tax_string => url.
	 * @return array<string,Result_Redirected> Associative array page_tax_string => Result_Redirected.
	 */
	public function is_redirected( array $page_id_to_url_list ) {
		$results = [];
		foreach ( $page_id_to_url_list as $post_tax_string => $url ) {
			$is_redirected = false;
			$post_tax      = Post_Tax::create_from_string( $post_tax_string );
			// both urls with applied filters.
			$result_redirected       = $post_tax->get_redirected_data();
			$original_redirected_url = $result_redirected->get_url_filtered();
			$original_url            = $post_tax->get_url( true );
			if ( ! is_null( $original_redirected_url ) ) {
				// compare it with the current url.
				$is_redirected = ! $this->is_same_urls( $original_redirected_url, $original_url );
			}
			$results[ $post_tax_string ] = $result_redirected->set_is_redirected( $is_redirected ? 1 : 0 );
		}
		return $results;
	}
	/**
	 * Compare two urls.
	 *
	 * @since 0.9.1
	 *
	 * @param string $url_a First URL.
	 * @param string $url_b Second URL.
	 * @return bool True: the same, false: different.
	 */
	private function is_same_urls( $url_a, $url_b ) {
		return $url_a === $url_b;
	}
}