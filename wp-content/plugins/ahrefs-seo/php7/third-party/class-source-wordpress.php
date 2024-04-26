<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Third_Party;

use ahrefs\AhrefsSeo\Ahrefs_Seo;
use ahrefs\AhrefsSeo\Post_Tax;

/**
 * Class for getting details from other SEO plugins.
 *
 * @since 0.8.8
 */
class Source_WordPress extends Source implements Canonical_Url {

	/**
	 * Fill internal variables during initialization
	 *
	 * @return void
	 */
	protected function fill_vars() : void {
		$this->source_id    = Sources::SOURCE_EXT_WORDPRESS;
		$this->is_available = true;
		$this->version      = get_bloginfo( 'version' );
	}

	/**
	 * Try to get noindex value from WordPress option "Search engine visibility"
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return bool|null True - noindex, false - index, null - unknown
	 */
	public function is_noindex( Post_Tax $post_tax ) : ?bool {
		// is blog marked as not being public?
		return '0' === (string) get_option( 'blog_public' );
	}


	/**
	 * Get canonical url for post.
	 *
	 * @since 0.9.1
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return string|null Canonical URL.
	 */
	public function get_canonical_url( Post_Tax $post_tax ) : ?string {
		$result = $post_tax->is_post() ? wp_get_canonical_url( $post_tax->get_post_id() ) : null;
		if ( $result === $post_tax->get_url( true ) ) {
			return null;
		}
		Ahrefs_Seo::breadcrumbs( sprintf( '%s(%s)[url:%s]: %s', __METHOD__, (string) $post_tax, $post_tax->get_url( true ), (string) wp_json_encode( $result ) ) );
		return is_string( $result ) ? $result : null;
	}

}
