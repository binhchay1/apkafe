<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Content_Tips;

/**
 * Class for suggested keywords next time tip at Content audit page.
 *
 * @since 0.8.4
 */
class Tip_Suggested_Subsequent extends Tip {

	public const ID          = 'suggested_subsequent';
	protected const TEMPLATE = 'suggested-subsequent';

	/**
	 * Need to show the tip.
	 * Basically when it was not closed by user.
	 *
	 * @return bool
	 */
	public function need_to_show() : bool {
		return parent::need_to_show() && $this->data->has_suggested_keywords() && $this->data->is_not_first_tip();
	}
}
