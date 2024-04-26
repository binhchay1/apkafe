<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Content_Tips;

/**
 * Class for duplicated keywords tip at Content audit page.
 *
 * @since 0.8.4
 */
class Tip_Duplicated extends Tip {

	public const ID          = 'duplicated';
	protected const TEMPLATE = 'duplicated';

	/**
	 * Need to show the tip.
	 * Basically when it was not closed by user.
	 *
	 * @return bool
	 */
	public function need_to_show() : bool {
		return parent::need_to_show() && $this->data->has_duplicated_keywords();
	}
}
