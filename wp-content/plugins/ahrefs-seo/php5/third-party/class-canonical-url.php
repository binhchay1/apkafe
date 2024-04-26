<?php

namespace ahrefs\AhrefsSeo\Third_Party;

use ahrefs\AhrefsSeo\Post_Tax;
/**
 * Import canonical URL from the source
 *
 * @since 0.9.1
 */
interface Canonical_Url {

	/**
	 * Get canonical url.
	 *
	 * @since 0.9.1
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return string|null Assigned canonical URL.
	 */
	public function get_canonical_url( Post_Tax $post_tax );
	/**
	 * Is this source available?
	 *
	 * @since 0.9.4
	 *
	 * @return bool
	 */
	public function is_available();
	/**
	 * Get source ID.
	 *
	 * @since 0.9.4
	 *
	 * @return string
	 */
	public function get_source_id();
}