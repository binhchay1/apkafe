<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Content_Tips;

/**
 * Class for content audit tips: Last audit was over a month ago.
 *
 * @since 0.8.4
 */
class Tip_Last_Audit extends Tip {

	public const ID          = 'last-audit';
	protected const TEMPLATE = 'last-audit';

	/**
	 * Need to show the tip.
	 * Do not allow to close it by user.
	 *
	 * @return bool
	 */
	public function need_to_show() : bool {
		return $this->data->is_last_audit_expired();
	}

}
