<?php

namespace ahrefs\AhrefsSeo\Third_Party;

use ahrefs\AhrefsSeo\Post_Tax;
use ahrefs\AhrefsSeo\Keywords\Data_Keyword;
/**
 * Import keyword from the source
 *
 * @since 0.8.8
 */
interface Assigned_Keyword {

	/**
	 * Is this source available?
	 *
	 * @return bool
	 */
	public function is_available();
	/**
	 * Get post keyword assigned in current source.
	 *
	 * @param Post_Tax $post_tax Post or term.
	 * @return Data_Keyword|null Assigned keyword.
	 */
	public function get_assigned_keyword( Post_Tax $post_tax );
}