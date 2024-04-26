<?php

namespace ahrefs\AhrefsSeo\Third_Party;

use ahrefs\AhrefsSeo\Post_Tax;
/**
 * Class for getting details from other SEO plugins.
 *
 * @since 0.9.2
 */
class Source_Redirection extends Source implements Redirected_Url {

	/**
	 * Fill internal variables during initialization
	 *
	 * @return void
	 */
	protected function fill_vars() {
		$this->source_id    = Sources::SOURCE_EXT_REDIRECTION;
		$this->is_available = defined( 'REDIRECTION_VERSION' ) && class_exists( '\\Red_Item' );
		$this->version      = defined( 'REDIRECTION_VERSION' ) ? REDIRECTION_VERSION : null;
	}
	/**
	 * Get redirected url.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return string|null Assigned redirect URL.
	 */
	public function get_redirected_url( Post_Tax $post_tax ) {
		$result = null;
		$url    = $post_tax->get_url();
		$slug   = str_replace( home_url(), '', $url );
		// Get all redirects that match the URL.
		$redirects = \Red_Item::get_for_url( $slug );
		// Redirects will be ordered by position. Run through the list until one fires.
		foreach ( (array) $redirects as $item ) {
			if ( method_exists( $item, 'get_match' ) ) {
				/** @var \Red_Item $item */
				$action = $item->get_match( $slug, rawurlencode( $slug ) );
				if ( $action ) {
					$result = $action->get_target();
					break;
				}
			}
		}
		return is_string( $result ) ? $result : null;
	}
	/**
	 * Try to get noindex value.
	 * Not implemented for this plugin.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return bool|null True - noindex, false - index, null - unknown
	 */
	public function is_noindex( Post_Tax $post_tax ) {
		return null;
	}
}