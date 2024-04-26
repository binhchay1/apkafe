<?php

namespace ahrefs\AhrefsSeo\Content_Tips;

/**
 * Class for expand first suggestion at the "All analyzed" tab at Content audit page.
 * Do not show the content tip, but handle the logic for expand action.
 *
 * @since 0.8.5
 */
class Tip_Expand_Suggestion extends Tip {

	const ID       = 'expand_suggestion';
	const TEMPLATE = '';
	/**
	 * Display tip content
	 *
	 * @param bool $show_hidden Show block as hidden.
	 * @return void
	 */
	public function show( $show_hidden = false ) {
		// nothing to print.
	}
}