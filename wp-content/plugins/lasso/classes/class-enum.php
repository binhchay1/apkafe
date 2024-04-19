<?php
/**
 * Declare class Enum
 *
 * @package Enum
 */

namespace Lasso\Classes;

/**
 * Enum
 */
class Enum {
	const LASSO_REVIEW_URL = 'https://www.trustpilot.com/review/getlasso.co';
	const SETTING_PREFIX   = 'lasso_';

	const OPTION_REVIEW_ALLOW                    = 'review_allow_notification';
	const OPTION_REVIEW_SNOOZE                   = 'review_snooze';
	const OPTION_REVIEW_LINK_COUNT               = 'review_link_count';
	const OPTION_FIRST_INSTALL_DATE              = 'lasso_first_install_date';
	const OPTION_ENABLE_WEBP                     = 'enable_webp';
	const OPTION_ENABLE_SCAN_NOTICE_AFTER_IMPORT = 'enable_scan_notice_after_import';

	const LASSO_WEBP_THUMBNAIL  = 'lasso_webp_thumbnail';
	const LASSO_INSTALL_COUNT   = 'lasso_install_count';
	const LASSO_LL_ATTR         = 'lasso-ll-do-not-delete';
	const LASSO_IS_STARTUP_PLAN = 'lasso_is_startup_plan';
}
