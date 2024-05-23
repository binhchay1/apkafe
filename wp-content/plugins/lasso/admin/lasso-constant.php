<?php
/**
 * Declare constants
 *
 * @package lasso constants
 */

// ? wp-includes/default-constants.php
if ( ! defined( 'SECURE_AUTH_COOKIE' ) ) {
	if ( ! function_exists( 'wp_cookie_constants' ) ) {
		require_once ABSPATH . 'wp-includes/default-constants.php';
	}

	wp_cookie_constants();
}

// ? Define some variables
define( 'LASSO_TEXT_DOMAIN', 'lasso-urls' );

// ? Lasso meta
define( 'LASSO_SETTINGS', 'lasso_settings' );
define( 'LASSO_POST_TYPE', 'lasso-urls' );
define( 'LASSO_CATEGORY', 'lasso-cat' );
define( 'LASSO_LITE_CATEGORY', 'lasso-lite-cat' );

// ? Lasso tables
define( 'LASSO_REVERT_DB', 'lasso_revert' );
define( 'LASSO_URL_DETAILS', 'lasso_url_details' );
define( 'LASSO_URL_ISSUE_DB', 'lasso_url_issues' );
define( 'LASSO_LINK_LOCATION_DB', 'lasso_link_locations' );
define( 'LASSO_AMAZON_PRODUCTS_DB', 'lasso_amazon_products' );
define( 'LASSO_CATEGORY_ORDER_DB', 'lasso_category_order' );
define( 'LASSO_CONTENT_DB', 'lasso_content' );
define( 'LASSO_URL_ISSUE_DEFINITIONS_DB', 'lasso_url_issue_definitions' );
define( 'LASSO_TRACKED_KEYWORDS', 'lasso_tracked_keywords' );
define( 'LASSO_KEYWORD_LOCATIONS', 'lasso_keyword_locations' );
define( 'LASSO_AMAZON_TRACKING_IDS', 'lasso_amazon_tracking_ids' );
define( 'LASSO_FIELDS', 'lasso_fields' );
define( 'LASSO_FIELD_MAPPING', 'lasso_field_mapping' );
define( 'LASSO_AFFILIATE_PROGRAMS', 'lasso_affiliate_programs' );
define( 'LASSO_POST_CONTENT_HISTORY', 'lasso_post_content_history' );
define( 'LASSO_EXTEND_PRODUCTS', 'lasso_extend_products' );

// ? Lasso type
define( 'LASSO_BASIC_LINK_TYPE', 'Basic Link' );
define( 'LASSO_AMAZON_PRODUCT_TYPE', 'Amazon Product' );

define( 'LASSO_CRON_DAILY_CLEAN_LINK_LOCATIONS_HOOK', 'lasso_daily_clean_link_locations_hook' );
define( 'LASSO_CRON_DAILY_DATA_SYNC_CONTENT', 'lasso_daily_data_sync_content' );
define( 'LASSO_CRON_DAILY_DATA_GET_PRETTY_LINK_FINAL_URLS', 'lasso_daily_get_pretty_link_final_urls' );
define( 'LASSO_CRON_DAILY_DATA_SYNC_AFFILIATE_PROGRAMS', 'lasso_daily_data_sync_affiliate_programs' );
define( 'LASSO_CRON_DAILY_DATA_SYNC_PLUGINS', 'lasso_daily_data_sync_plugins' );
define( 'LASSO_CRON_DAILY_DATA_SYNC_LINK_LOCATIONS', 'lasso_daily_data_sync_link_locations' );
define( 'LASSO_CRON_DAILY_DATA_SYNC_LASSO_LINKS', 'lasso_daily_data_sync_lasso_links' );
define( 'LASSO_CRON_DAILY_DATA_SYNC_AUTHORS', 'lasso_daily_data_sync_authors' );
define( 'LASSO_CRON_DAILY_UPDATE_LICENSE_STATUS', 'lasso_update_license_status' );

define( 'LASSO_CRON_HOURLY_HOOK', 'lasso_hourly_event_hook' );

define( 'LASSO_CRON_MONTHLY_DATA_SYNC_CONTENT', 'lasso_monthly_data_sync_content' );
define( 'LASSO_CRON_MONTHLY_DATA_SYNC_LINK_LOCATIONS', 'lasso_monthly_data_sync_link_locations' );
define( 'LASSO_CRON_MONTHLY_DATA_SYNC_LASSO_LINKS', 'lasso_monthly_data_sync_lasso_links' );
define( 'LASSO_CRON_MONTHLY_DATA_SYNC_AUTHORS', 'lasso_monthly_data_sync_authors' );

define( 'LASSO_CRON_SCAN_ALL_LINKS', 'lasso_scan_all_links' );
define( 'LASSO_CRON_FORCE_SCAN_ALL_LINKS', 'lasso_force_scan_all_links' );
define( 'LASSO_CRON_UPDATE_CATEGORY_FOR_IMPORTED_PRETTY_LINK', 'lasso_update_category_for_imported_pretty_link' );
define( 'LASSO_CRON_SYNC_AMAZON_API', 'lasso_sync_and_encrypt_amazon_api' );
define( 'LASSO_CRON_AUTO_MONETIZE', 'lasso_auto_monetize' );
define( 'LASSO_CRON_REMOVE_DUPLICATE_PROCESSES', 'lasso_remove_duplicate_processes' );

define( 'LASSO_CRON_ADD_AMAZON', 'lasso_cron_add_amazon' );
define( 'LASSO_CRON_UPDATE_AMAZON', 'lasso_cron_update_amazon' );
define( 'LASSO_CRON_CHECK_ISSUE', 'lasso_cron_check_issue' );
define( 'LASSO_CRON_IMPORT_ALL', 'lasso_cron_import_all' );
define( 'LASSO_CRON_REVERT_ALL', 'lasso_cron_revert_all' );
define( 'LASSO_CRON_LINK_UPDATE', 'lasso_cron_link_update' );
define( 'LASSO_CRON_CREATE_LASSO_WEBP_IMAGE', 'lasso_cron_create_lasso_webp_image' );
define( 'LASSO_CRON_CREATE_LASSO_WEBP_IMAGE_TABLE', 'lasso_cron_create_lasso_webp_image_table' );

define( 'LASSO_INTERCOM_APP_ID', 'az01idfr' );
define( 'LASSO_DEFAULT_THUMBNAIL', LASSO_PLUGIN_URL . 'admin/assets/images/lasso-no-thumbnail.webp' );
define( 'LASSO_SEGMENT_ANALYTIC_ID', 'wUdmsOfZW9FZHrg5UCvUE3whYXGQyrpH' );

// ? Lasso button type
define( 'LASSO_PRIMARY_TYPE_BTN', 'primary' );
define( 'LASSO_SECONDARY_TYPE_BTN', 'secondary' );

define( 'LASSO_REVISIONS_TO_KEEP', 8 );

define(
	'SETTINGS_DEFAULT',
	serialize( // phpcs:ignore
		array(
			'amazon_access_key_id'                  => '',
			'amazon_secret_key'                     => '',
			'amazon_tracking_id'                    => '',
			'amazon_tracking_id_whitelist'          => array(),
			'amazon_multiple_tracking_id'           => true,
			'amazon_update_pricing_hourly'          => true,
			'amazon_default_tracking_country'       => 'us',
			'amazon_add_tracking_id_to_attribution' => true,
			'enable_amazon_prime'                   => true,
			'auto_monetize_amazon'                  => false,
			'general_disable_amazon_notifications'  => false,
			'show_amazon_discount_pricing'          => false,
			'cron_time_interval'                    => 0, // ? hours

			'performance_event_tracking'            => true,
			'analytics_enable_click_tracking'       => false,
			'analytics_google_tracking_id'          => '',
			'analytics_enable_ip_anonymization'     => false,
			'analytics_enable_send_pageview'        => false,

			'general_disable_notification'          => false,
			'general_disable_tooltip'               => false,
			'general_bad_link_check'                => false,
			'cron_prioritize_check'                 => 100,
			'io_import_data_from'                   => '',
			'io_import_options'                     => '',
			'io_import_options_revert'              => false,
			'custom_css_default'                    => '',
			'segment_analytics'                     => true,
			'processes_execute_limit'               => false,
			'fontawesome_js_svg'                    => false,
			'keep_original_url'                     => array(),
			'auto_monetize_affiliates'              => true,

			// ? url detail
			'enable_nofollow'                       => true,
			'open_new_tab'                          => true,
			'enable_nofollow2'                      => true,
			'open_new_tab2'                         => true,
			'enable_sponsored'                      => true,
			'check_duplicate_link'                  => true,

			'open_new_tab3'                  => true,
			'enable_nofollow3'               => true,
			'open_new_tab4'                  => true,
			'enable_nofollow4'               => true,
			'open_new_tab_google'            => true,
			'enable_nofollow_google'         => true,
			'open_new_tab_apple'             => true,
			'enable_nofollow_apple'                  => true,

			'show_price'                            => true,
			'show_disclosure'                       => true,
			'show_disclosure_grid'                  => false,
			'keep_site_stripe_ui'                   => false,

			// ? Display tab
			'theme_name'                            => 'Cactus',
			'display_color_main'                    => 'black',
			'display_color_title'                   => 'black',
			'display_color_background'              => 'white',
			'display_color_button'                  => '#22BAA0',
			'display_color_secondary_button'        => '#22BAA0',
			'display_color_button_text'             => 'white',
			'display_color_pros'                    => '#22BAA0',
			'display_color_cons'                    => '#E06470',
			'default_thumbnail'                     => LASSO_DEFAULT_THUMBNAIL,
			'primary_button_text'                   => 'Buy Now',
			'secondary_button_text'                 => 'Our Review',
			'lasso_affiliate_URL'                   => 'https://getlasso.co/',
			'badge_text'                            => '',
			'disclosure_text'                       => 'We earn a commission if you make a purchase, at no additional cost to you.',
			'enable_brag_mode'                      => false,

			// ? license
			'license_serial'                        => '',
			'site_id'                               => '',
			'lasso_permission'                      => 'Administrator',

			// ? Performance
			'cpu_threshold'                         => 80,
			'enable_logs'                           => false,

			// ? Rewrite Lasso url slug
			'rewrite_slug'                          => '',
			'restrict_prefix'                       => false,

			// ? Post content history
			'enable_history'                        => true,

			'cpt_support'                           => array( 'post', 'page', 'wp_block' ),
			'custom_fields_support'                 => array(),
			'manually_background_process_limit'     => 2, // ? number background processes at a time
			'enable_webp'                           => true,
			'link_from_display_title'               => true,
		)
	)
);

define(
	'LASSO_LINK_CUSTOMIZE_DISPLAY',
	array(
		'single'              => array(
			'type'       => 'single',
			'name'       => 'Single Product Displays',
			'attributes' => array(
				array(
					'name' => 'Product title',
					'attr' => 'title',
					'desc' => 'Override the product title.<br/>Hide the product title by <code>hide</code> value.',
				),
				array(
					'name' => 'Title url',
					'attr' => 'title_url',
					'desc' => 'Customize the title link destination. Leave blank for the default.<br/>Example: https://getlasso.co',
				),
				array(
					'name' => 'Title tag',
					'attr' => 'title_type',
					'desc' => 'Set the title tag. Common values are: <i>H1, H2, H3, H4</i>',
				),
				array(
					'name' => 'Product description',
					'attr' => 'description',
					'desc' => 'Override the product description<br/>Hide the product description by <code>hide</code> value.',
				),
				array(
					'name' => 'Badge',
					'attr' => 'badge',
					'desc' => 'Override the display badge.<br/>Hide the badge by <code>hide</code> value.',
				),
				array(
					'name' => 'Brag',
					'attr' => 'brag',
					'desc' => 'Promote Lasso on your display and <a href="https://getlasso.co/affiliate-program/" rel="nofollow noopener noreferrer" target="_blank">earn money</a> by <code>true</code> value.',
				),
				array(
					'name' => 'Show/Hide the price',
					'attr' => 'price',
				),
				array(
					'name' => 'Show/Hide all Fields',
					'attr' => 'field',
				),
				array(
					'name' => 'Show/Hide the Primary Rating',
					'attr' => 'rating',
				),
				array(
					'name' => 'Theme',
					'attr' => 'theme',
					'desc' => 'Override the theme. Supported theme values are: <i>Cactus, Cutter, Lab, Llama, Money, Splash</i>',
				),
				array(
					'name' => 'First button url',
					'attr' => 'primary_url',
					'desc' => 'Override the first button url.<br/>Example: https://getlasso.co',
				),
				array(
					'name' => 'First button text',
					'attr' => 'primary_text',
					'desc' => 'Override the first button text.',
				),
				array(
					'name' => 'Second button url',
					'attr' => 'secondary_url',
					'desc' => 'Override the second button url.',
				),
				array(
					'name' => 'Second button text',
					'attr' => 'secondary_text',
					'desc' => 'Override the second button text.',
				),
				array(
					'name' => 'Image url',
					'attr' => 'image_url',
					'desc' => 'Override the image url.<br/>Example: https://getlasso.co/lasso.png',
				),
				array(
					'name' => 'Disclosure text',
					'attr' => 'disclosure_text',
					'desc' => 'Override the disclosure text.',
				),
				array(
					'name' => 'Anchor id',
					'attr' => 'anchor_id',
					'desc' => 'Override the anchor id.<br/>Example: the-anchor-link-id',
				),
			),
		),
		'button'              => array(
			'type'       => 'button',
			'name'       => 'Button Displays',
			'attributes' => array(
				array(
					'name' => 'Theme',
					'attr' => 'theme',
					'desc' => 'Override the theme. Supported theme values are: <i>Cactus, Cutter, Lab, Llama, Money, Splash</i>',
				),
				array(
					'name' => 'Button type',
					'attr' => 'button_type',
					'desc' => 'Set this value to show either the Secondary(secondary) or Primary(primary) button. This defaults to "primary".',
				),
				array(
					'name' => 'Button url',
					'attr' => 'primary_url',
					'desc' => 'Override the button url.<br/>Example: https://getlasso.co',
				),
				array(
					'name' => 'Button text',
					'attr' => 'primary_text',
					'desc' => 'Override the button text.',
				),
				array(
					'name' => 'Anchor id',
					'attr' => 'anchor_id',
					'desc' => 'Override the anchor id.<br/>Example: the-anchor-link-id',
				),
			),
		),
		'image'               => array(
			'type'       => 'image',
			'name'       => 'Image Displays',
			'attributes' => array(
				array(
					'name' => 'Image url',
					'attr' => 'image_url',
					'desc' => 'Override the image url.<br/>Example: https://getlasso.co/lasso.png',
				),
				array(
					'name' => 'Anchor id',
					'attr' => 'anchor_id',
					'desc' => 'Override the anchor id.<br/>Example: the-anchor-link-id',
				),
			),
		),
		'grid'                => array(
			'type'       => 'grid',
			'name'       => 'Grid Displays',
			'attributes' => array(
				array(
					'name' => 'Columns',
					'attr' => 'columns',
					'desc' => 'Sets the Grid to display three products per row. Ideal for wide layouts. This can also be set to 1 for a single display per-row and the limit can be set is 5.',
				),
				array(
					'name' => 'Compact',
					'attr' => 'compact',
					'desc' => 'This will hide the description and all fields giving you a much more compact display by <code>hide</code> value.',
				),
				array(
					'name' => 'Limit',
					'attr' => 'limit',
					'desc' => 'Show just the top X Lasso Links in a group.',
				),
				array(
					'name' => 'Product title',
					'attr' => 'title',
					'desc' => 'Hide the product title by <code>hide</code> value.',
				),
				array(
					'name' => 'Product description',
					'attr' => 'description',
					'desc' => 'Hide the product description by <code>hide</code> value.',
				),
				array(
					'name' => 'Badge',
					'attr' => 'badge',
					'desc' => 'Hide the badge by <code>hide</code> value.',
				),
				array(
					'name' => 'Brag',
					'attr' => 'brag',
					'desc' => 'Promote Lasso on your display and <a href="https://getlasso.co/affiliate-program/" rel="nofollow noopener noreferrer" target="_blank">earn money</a> by <code>true</code> value.',
				),
				array(
					'name' => 'Show/Hide the price',
					'attr' => 'price',
				),
				array(
					'name' => 'Show/Hide all Fields',
					'attr' => 'field',
				),
				array(
					'name' => 'Show/Hide the Primary Rating',
					'attr' => 'rating',
				),
				array(
					'name' => 'Theme',
					'attr' => 'theme',
					'desc' => 'Override the theme. Supported theme values are: <i>Cactus, Cutter, Lab, Llama, Money, Splash</i>',
				),
				array(
					'name' => 'Title tag',
					'attr' => 'title_type',
					'desc' => 'Set the title tag. Common values are: <i>H1, H2, H3, H4</i>',
				),
				array(
					'name' => 'Anchor id',
					'attr' => 'anchor_id',
					'desc' => 'Override the anchor id.<br/>Example: the-anchor-link-id',
				),
				array(
					'name' => 'Show/Hide the Disclosure',
					'attr' => 'disclosure',
				),
			),
		),
		'list'                => array(
			'type'       => 'list',
			'name'       => 'List Displays',
			'attributes' => array(
				array(
					'name' => 'Bullet',
					'attr' => 'bullets',
					'desc' => 'Override the bullet type. To hide the bullet use <code>hide</code>. All supported values are: <i>decimal, alpha, roman, square, circle, hide</i>',
				),
				array(
					'name' => 'Product description',
					'attr' => 'description',
					'desc' => 'Hide the product description by <code>hide</code> value.',
				),
				array(
					'name' => 'Show/Hide the price',
					'attr' => 'price',
				),
				array(
					'name' => 'Show/Hide all Fields',
					'attr' => 'field',
				),
				array(
					'name' => 'Show/Hide the Primary Rating',
					'attr' => 'rating',
				),
				array(
					'name' => 'Theme',
					'attr' => 'theme',
					'desc' => 'Override the theme. Supported theme values are: <i>Cactus, Cutter, Lab, Llama, Money, Splash</i>',
				),
				array(
					'name' => 'Title tag',
					'attr' => 'title_type',
					'desc' => 'Set the title tag. Common values are: <i>H1, H2, H3, H4</i>',
				),
				array(
					'name' => 'Anchor id',
					'attr' => 'anchor_id',
					'desc' => 'Override the anchor id.<br/>Example: the-anchor-link-id',
				),
			),
		),
		'gallery'             => array(
			'type'       => 'gallery',
			'name'       => 'Gallery Displays',
			'attributes' => array(
				array(
					'name' => 'Columns',
					'attr' => 'columns',
					'desc' => 'Sets the Gallery to display five products per row. Ideal for wide layouts. This can also be set 2, 3, 4, and 5.',
				),
				array(
					'name' => 'Limit',
					'attr' => 'limit',
					'desc' => 'Show just the top X Lasso Links in a group.',
				),
				array(
					'name' => 'Anchor id',
					'attr' => 'anchor_id',
					'desc' => 'Override the anchor id.<br/>Example: the-anchor-link-id',
				),
			),
		),
		'table'               => array(
			'type'       => 'table',
			'name'       => 'Table Comparison Displays',
			'attributes' => array(
				array(
					'name' => 'Anchor id',
					'attr' => 'anchor_id',
					'desc' => 'Override the anchor id.<br/>Example: the-anchor-link-id',
				),
				array(
					'name' => 'data-nosnippet HTML attribute',
					'attr' => 'data_nosnippet',
					'desc' => 'Refer to: <a target="_blank" href="https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag#data-nosnippet-attr">data-nosnippet HTML attribute.</a>',
				),
			),
		),
		'all_attributes'      => array(
			'title',
			'title_url',
			'title_type',
			'description',
			'badge',
			'price',
			'field',
			'rating',
			'theme',
			'primary_url',
			'primary_text',
			'secondary_url',
			'secondary_text',
			'image_url',
			'disclosure_text',
			'button_type',
			'columns',
			'limit',
			'bullets',
			'compact',
			'brag',
			'anchor_id',
		),
		'toogle_attributes'   => array(
			'price',
			'field',
			'rating',
			'data_nosnippet',
			'disclosure',
		),
		'textarea_attributes' => array(
			'description',
		),
		'notice'              => 'For a detailed list of all customization options, visit our <a href="https://support.getlasso.co/en/articles/4575092-shortcode-reference-guide" target="_blank">Shortcode Reference Guide</a>.',
	)
);

define(
	'LASSO_LINK_SCHEMA_DISPLAY',
	array(
		'single'            => array(
			'type'       => 'single',
			'name'       => 'Single Product Displays',
			'attributes' => array(
				array(
					'name' => 'Review author',
					'attr' => 'schema_review_author',
					'desc' => 'The post\'s author.',
				),
				array(
					'name' => 'Review schema',
					'attr' => 'schema_review',
					'desc' => 'Enable/Disable the Review Schema, default is disable.',
				),
				array(
					'name' => 'Price currency',
					'attr' => 'schema_price_currency',
					'desc' => 'Price currency default is <code>USD</code>.<br/>Standard ISO 4217 format.<br/>Refer to: <a href="https://en.wikipedia.org/wiki/ISO_4217">https://en.wikipedia.org/wiki/ISO_4217</a>',
				),
				array(
					'name' => 'Price',
					'attr' => 'schema_price',
					'desc' => 'Customize schema price. Default base on Lasso\'s shortcode <code>price</code>.<br/>Example: 199.99',
				),
				array(
					'name' => 'Rating value',
					'attr' => 'schema_rating',
					'desc' => 'Base on Lasso\'s shortcode <code>Primary Rating</code> field.',
				),
				array(
					'name' => 'Pro/Con schema',
					'attr' => 'schema_pros_cons',
					'desc' => 'Enable/Disable the Pros/Cons Schema, default is disable.',
				),
				array(
					'name' => 'Pros',
					'attr' => 'schema_pros',
					'desc' => 'Base on Lasso\'s shortcode <code>Pros</code> field.',
				),
				array(
					'name' => 'Cons',
					'attr' => 'schema_cons',
					'desc' => 'Base on Lasso\'s shortcode <code>Cons</code> field.',
				),
			),
		),
		'all_attributes'    => array(
			'schema_price',
			'schema_rating',
			'schema_pros',
			'schema_cons',
			'schema_price_currency',
			'schema_review_author',
			'schema_review',
			'schema_pros_cons',
		),
		'toogle_attributes' => array(
			'schema_review',
			'schema_pros_cons',
		),
		'notice'            => 'Schema & structured data on this post, refer to <a href="https://developers.google.com/search/docs/appearance/structured-data/product" target="_blank">Product Structured Data Reference Documentation</a>.<br>Notice: Only allow one Lasso shortcode set of Review and Pro/Con schema per post and just supports for Single Displays.',
	)
);

define( 'LASSO_UPDATE_LICENSE_URL', 'https://app.getlasso.co/login' );
define( 'LASSO_LEARN_LINK', 'https://support.getlasso.co' );

if ( ! defined( 'LASSO_LINK' ) ) {
	define( 'LASSO_LINK', 'https://lasso.link' );
}

if ( ! defined( 'SENTRY_DNS' ) ) {
	define( 'SENTRY_DNS', 'https://68e8fcb089ae44259c716b93543afd72@o51581.ingest.sentry.io/262165' );
}

if ( ! defined( 'LAUNCH_DARKLY' ) ) {
	define( 'LAUNCH_DARKLY', 'sdk-86de7efa-bc56-4acc-acf6-1b27833a171c' );
}

define( 'LASSO_BR_CODE', 'lasso-br-code' );
