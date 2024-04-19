<?php
/**
 * Declare class Setting_Enum
 *
 * @package Setting_Enum
 */

namespace Lasso\Classes;

/**
 * Setting_Enum
 */
abstract class Setting_Enum {
	// ? Lasso pages.
	const PAGE_GROUP_DETAILS = 'group-details';
	const PAGE_GROUP_URLS    = 'group-urls';
	const PAGE_URL_DETAILS   = 'url-details';
	const PAGE_TABLES        = 'tables';
	const PAGE_TABLE_DETAILS = 'table-details';
	const PAGE_ONBOARDING    = 'install';
	const PAGE_IMPORT        = 'import-urls';

	// ? Setting Post content history.
	const ENABLE_HISTORY = 'enable_history';

	const THEME_CACTUS  = 'Cactus';
	const THEME_CUTTER  = 'Cutter';
	const THEME_FLOW    = 'Flow';
	const THEME_GEEK    = 'Geek';
	const THEME_LAB     = 'Lab';
	const THEME_LLAMA   = 'Llama';
	const THEME_MONEY   = 'Money';
	const THEME_SPLASH  = 'Splash';
	const THEME_OPTIONS = array(
		self::THEME_CACTUS,
		self::THEME_CUTTER,
		self::THEME_FLOW,
		self::THEME_GEEK,
		self::THEME_LAB,
		self::THEME_LLAMA,
		self::THEME_MONEY,
		self::THEME_SPLASH,
	);

	// Lasso table style.
	const TABLE_STYLE_ROW     = 'Row';
	const TABLE_STYLE_COLUMN  = 'Column';
	const DISPLAY_TYPE_TABLE  = 'Table';
	const LIMIT_TABLE_ON_PAGE = 10;

	const META_LINK_LOCATION_NAME = 'Link_Locations';

	const PRETTY_LINK_SLUG         = 'pretty-link';
	const THIRSTYLINK_SLUG         = 'thirstylink';
	const SURL_SLUG                = 'surl';
	const EARNIST_SLUG             = 'earnist';
	const AFFILIATE_URL_SLUG       = 'affiliate_url';
	const AAWP_SLUG                = 'aawp';
	const AAWP_TABLE_SLUG          = 'aawp_table';
	const EASYAZON_SLUG            = 'easyazon';
	const EASY_AFFILIATE_LINK_SLUG = 'easy_affiliate_link';
	const AMA_LINKS_PRO_SLUG       = 'amalinkspro';

	const SUPPORT_IMPORT_PLUGINS = array(
		self::PRETTY_LINK_SLUG         => 'Pretty Links',
		self::THIRSTYLINK_SLUG         => 'Thirsty Affiliates',
		self::SURL_SLUG                => 'Lasso Lite / Simple URLs',
		self::EARNIST_SLUG             => 'Earnist',
		self::AFFILIATE_URL_SLUG       => 'Affiliate URLs',
		self::AAWP_SLUG                => 'AAWP',
		self::EASYAZON_SLUG            => 'EasyAzon',
		self::EASY_AFFILIATE_LINK_SLUG => 'Easy Affiliate Links',
		self::AMA_LINKS_PRO_SLUG       => 'AmaLinks Pro',
	);

	// ? Lasso options
	const SEGMENT_ANALYTICS        = 'segment_analytics';
	const RESTRICT_PREFIX          = 'restrict_prefix';
	const PROCESSES_EXECUTE_LIMIT  = 'processes_execute_limit';
	const RECREATE_TABLE_TIME      = 'recreate_table_time';
	const NEXT_TIME_RECREATE_TABLE = 'next_time_recreate_table';

	// ? Hooks.
	const HOOK_ISSUE_DETECTION          = 'lasso_issue_detection';
	const HOOK_FETCH_AMAZON_PRODUCT_API = 'lasso_fetch_amazon_product_api';
	const HOOK_AFTER_SAVED_SETTINGS     = 'lasso_after_saved_settings';

	// ? Limit columns.
	const GALLERY_COLUMNS_DEFAULT = 4;
	const GRID_COLUMNS_DEFAULT    = 2;

	// ? In Lasso URL detail page, we will use affiliate URL instead of final URL.
	const DOMAIN_ALLOW_ORIGINAL_URL_IN_LASSO_DETAIL = array(
		'geni.us',
		'primalvid.io',
		'rstyle.me',
		'howl.me',
		'go.skimresources.com',
		'awin1.com',
		'shareasale.com',
		'refer.link',
		'lvnta.com',
		'link.archeraffiliates.com',
		'lasso.link',
	);

	// ? In Lasso URL detail page, keep URL params.
	const DOMAIN_ALLOW_KEEP_FINAL_URL_PARAMS = array(
		'brooksbrothers.com',
		'mechanicalkeyboards.com',
	);

	// ? Should not get final URL from these domains.
	const DOMAIN_NOT_GET_URL_PARAMS = array(
		'pntra.com',
	);

	// ? URLs get error when redirect.
	const ERROR_URL = array(
		'http://pepperjamnetwork.com/tracking/error.php',
		'https://pepperjamnetwork.com/tracking/error.php',
	);
}
