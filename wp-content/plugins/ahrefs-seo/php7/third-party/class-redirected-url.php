<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Third_Party;

use ahrefs\AhrefsSeo\Post_Tax;

/**
 * Import redirected URL from the source
 *
 * @since 0.9.2
 */
interface Redirected_Url {

	/**
	 * Get redirected url.
	 *
	 * @since 0.9.1
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return string|null Assigned redirect URL.
	 */
	public function get_redirected_url( Post_Tax $post_tax ) : ?string;
	/**
	 * Is this source available?
	 *
	 * @since 0.9.4
	 *
	 * @return bool
	 */
	public function is_available() : bool;

	/**
	 * Get source ID.
	 *
	 * @since 0.9.4
	 *
	 * @return string
	 */
	public function get_source_id() : string;
}
