<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * セクションの追加
 */

add_action( 'admin_init' , 'aaih__section_init' );
/**
 * セクションの実際の追加
 *
 * 設定ページSlugは、AAIH__MENU_SLUG に SUB_xxxx をつけてグループ分け
 *
 * @return void
 */
function aaih__section_init() {
	/* ------------------------------------
	 * 一般設定
	 * ------------------------------------ */
	$page_slug	= AAIH__PAGE_SLUG['general'];

	$section_title	= __( 'Ad select', AAIH__TEXT_DOMAIN ); // 広告選択
	add_settings_section(
		AAIH__SCID_BASIC, 		// ① ID
		$section_title, 		// ② セクションのタイトル
		'aaih__sc_basic', 		// ③ コールバック関数1
		$page_slug				// ④ 設定ページSlug
	);

	$section_title	= __( 'Target post type settings', AAIH__TEXT_DOMAIN ); // 対象記事の設定
	add_settings_section(
		AAIH__SCID_POST_PAGE, 	// ① ID
		$section_title, 		// ② セクションのタイトル
		'aaih__sc_post_page', 	// ③ コールバック関数1
		$page_slug				// ④ 設定ページSlug
	);

	$section_title	= __( 'Ad display settings', AAIH__TEXT_DOMAIN ); // 広告表示の設定
	add_settings_section(
		AAIH__SCID_AD_DISPLAY, 	// ① ID
		$section_title, 		// ② セクションのタイトル
		'aaih__sc_display', 	// ③ コールバック関数1
		$page_slug				// ④ 設定ページSlug
	);

	$section_title	= __( 'Ad code settings', AAIH__TEXT_DOMAIN ); // 広告コードの設定
	add_settings_section(
		AAIH__SCID_AD_CODE, 		// ① ID
		$section_title, 			// ② セクションのタイトル
		'aaih__sc_ad_code', 		// ③ コールバック関数1
		$page_slug 					// ④ 設定ページSlug
	);

	/* ------------------------------------
	 * 高度な設定
	 * ------------------------------------ */
	$page_slug	= AAIH__PAGE_SLUG['advanced'];

	$section_title	= __( 'Character count related', AAIH__TEXT_DOMAIN ); // 文字数カウント関連
	add_settings_section(
		AAIH__SCID_CHAR_COUNT, 	// ① ID
		$section_title, 		// ② セクションのタイトル
		'aaih__sc_char_count', 	// ③ コールバック関数1
		$page_slug				// ④ 設定ページSlug
	);

	$section_title	= __( 'Ad code settings related', AAIH__TEXT_DOMAIN ); // 広告コード設定関連
	add_settings_section(
		AAIH__SCID_AD_SETTINGS_ADVANCED, 	// ① ID
		$section_title, 					// ② セクションのタイトル
		'aaih__sc_ad_settings_advanced', 	// ③ コールバック関数1
		$page_slug							// ④ 設定ページSlug
	);

	$section_title	= __( 'Ad replacement support', AAIH__TEXT_DOMAIN ); // 広告置き換え対応
	add_settings_section(
		AAIH__SCID_AD_REPLACE, 	// ① ID
		$section_title, 		// ② セクションのタイトル
		'aaih__sc_ad_replace', 	// ③ コールバック関数1
		$page_slug				// ④ 設定ページSlug
	);

	/* ------------------------------------
	 * オプション
	 * ------------------------------------ */
	$page_slug	= AAIH__PAGE_SLUG['options'];

	$section_title	= __( 'Google AdSense lazy load support', AAIH__TEXT_DOMAIN ); // 広告表示の高速化対応
	add_settings_section(
		AAIH__SCID_ADSENSE_LAZY_LOAD, 			// ① ID
		$section_title, 						// ② セクションのタイトル
		'aaih__sc_adsense_display_speed_up', 	// ③ コールバック関数1
		$page_slug 								// ④ 設定ページSlug
	);

	$section_title	= __( 'Google AdSense Auto Ads', AAIH__TEXT_DOMAIN ); // アドセンス自動広告対応
	add_settings_section(
		AAIH__SCID_ADSENSE_AUTO_ADS, 		// ① ID
		$section_title, 					// ② セクションのタイトル
		'aaih__sc_adsense_auto_ads', 		// ③ コールバック関数1
		$page_slug 							// ④ 設定ページSlug
	);

	$section_title	= __( 'Google Analytics', AAIH__TEXT_DOMAIN ); // アナリティスクコードのヘッダ挿入用
	add_settings_section(
		AAIH__SCID_ANALYTICS_HEADER_INSERT_CODE, 	// ① ID
		$section_title, 							// ② セクションのタイトル
		'aaih__sc_analytics_header_insert_code', 	// ③ コールバック関数1
		$page_slug 									// ④ 設定ページSlug
	);

	/* ------------------------------------
	 * 言語、その他
	 * ------------------------------------ */
	$page_slug	= AAIH__PAGE_SLUG['others'];

	$section_title	= __( 'Display related', AAIH__TEXT_DOMAIN ); // 表示関連
	add_settings_section(
		AAIH__SCID_LANGUAGE, 	// ① ID
		$section_title, 		// ② セクションのタイトル
		'aaih__sc_language', 	// ③ コールバック関数1
		$page_slug 				// ④ 設定ページSlug
	);

	$section_title	= __( 'Access control', AAIH__TEXT_DOMAIN ); // 利用制限
	add_settings_section(
		AAIH__SCID_ACCESS_CONTROL, 	// ① ID
		$section_title, 		// ② セクションのタイトル
		'aaih__sc_access_control', 	// ③ コールバック関数1
		$page_slug 				// ④ 設定ページSlug
	);

	$section_title	= __( 'Debug related', AAIH__TEXT_DOMAIN ); // デバッグ関連
	add_settings_section(
		AAIH__SCID_DEBUG_MODE, 	// ① ID
		$section_title, 		// ② セクションのタイトル
		'aaih__sc_debug_mode', 	// ③ コールバック関数1
		$page_slug 				// ④ 設定ページSlug
	);
}

/**
 * セクションのコールバック（特に使わない）
 *
 * @return void
 */
function aaih__sc_basic() {
	//echo '<p>aaih__sc_basic</p>';
}

/**
 * セクションのコールバック（特に使わない）
 *
 * @return void
 */
function aaih__sc_post_page() {
	//echo '<p>aaih__sc_post_page</p>';
}

/**
 * セクションのコールバック（特に使わない）
 *
 * @return void
 */
function aaih__sc_display() {
	//echo '<p>aaih__sc_display</p>';
}

/**
 * セクションのコールバック（特に使わない）
 *
 * @return void
 */
function aaih__sc_ad_code() {
	//echo '<p>aaih__sc_ad_code</p>';
}

/**
 * セクションのコールバック（特に使わない）
 *
 * @return void
 */
function aaih__sc_char_count() {
	//echo '<p>aaih__sc_char_count</p>';
}

/**
 * セクションのコールバック（特に使わない）
 *
 * @return void
 */
function aaih__sc_ad_settings_advanced() {
	//echo '<p>aaih__sc_ad_settings_advanced</p>';
}

/**
 * セクションのコールバック（特に使わない）
 *
 * @return void
 */
function aaih__sc_ad_replace() {
	//echo '<p>aaih__sc_ad_replace</p>';
}

/**
 * セクションのコールバック（特に使わない）
 *
 * @return void
 */
function aaih__sc_adsense_auto_ads() {
	//echo '<p>aaih__sc_adsense_auto_ads</p>';
}

/**
 * セクションのコールバック（特に使わない）
 *
 * @return void
 */
function aaih__sc_analytics_header_insert_code() {
	//echo '<p>aaih__sc_analytics_header_insert_code</p>';
}

/**
 * セクションのコールバック（特に使わない）
 *
 * @return void
 */
function aaih__sc_adsense_display_speed_up() {
	//echo '<p>aaih__sc_adsense_display_speed_up</p>';
}

/**
 * セクションのコールバック（特に使わない）
 *
 * @return void
 */
function aaih__sc_language() {
	//echo '<p>aaih__sc_language</p>';
}

/**
 * セクションのコールバック（特に使わない）
 *
 * @return void
 */
function aaih__sc_access_control() {
	//echo '<p>aaih__sc_login_user</p>';
}

/**
 * セクションのコールバック（特に使わない）
 *
 * @return void
 */
function aaih__sc_debug_mode() {
	//echo '<p>aaih__sc_debug_mode</p>';
}
?>