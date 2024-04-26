<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Content_Tips;

/**
 * Class for content audit tip "Some articles are no longer “well-performing”"
 *
 * @since 0.8.4
 */
class Tip_Dropped_Article extends Tip {

	public const ID          = 'dropped';
	protected const TEMPLATE = 'drops-from-well-performing';

	/**
	 * Need to show the tip.
	 * Has dropped (no longer well-performing) posts and was not closed by user.
	 *
	 * @return bool
	 */
	public function need_to_show() : bool {
		return parent::need_to_show() && $this->data->has_dropped_articles();
	}

}
