<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * フィールドの追加
 *
 */

add_action( 'admin_init' , 'aaih__settings_fields_init' );
/**
 * フィールドの追加
 *
 * セクションに表示する各フィールドを実際に追加
 *
 * @return void
 */
function aaih__settings_fields_init() {

	// 全ての変数をゲット
	$settings	= aaih__get_item();

	/* ------------------------------------
	 * 以下、各設定のフィールド
	 *
	 * 表示するページやセクションを変えたい場合、
	 * コピペで簡単に移動できるよう、page slug, section id は先頭で指定し、
	 * add_settings_field() ではその変数を指定。
	 *
	 * コールバック関数は検索しやすいようにあえて文字列で指定。
	 *
	 * ★ 補足：$class について
	 * Settings API で $class を配列の中で渡すと、その class が tr に自動的にセットされる。
	 *（ということで、td 内で使うと勘違いもしやすそうなので、td内では基本は使わない）
	 * (td 内での class 指定は個別に行うのを基本とする)
	 * ------------------------------------ */

	/* ------------------------------------------------------------------------
	 * page slug	タブ：一般設定
	 * ------------------------------------------------------------------------ */
	$page_slug	= AAIH__PAGE_SLUG['general'];

	/* ------------------------------------
	 * section id	基本の設定
	 * ------------------------------------ */
	$section_id	= AAIH__SCID_BASIC;

	//フィールド：Hタグ前Ad（on/off）と広告選択
	// ------------------------------------
	$item_name		= 'ad_insert_onoff__ad_select';

	$field_id		= 'field_id_' . $item_name;
	$field_title	= '';	// display none で 非表示
	$field_callbak	= 'aaih__' . 'fc__ad_insert_onoff__ad_select';
	$class			= 'hide ad-insert-onoff';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);

	/* ------------------------------------
	 * section id	対象記事
	 * ------------------------------------ */
	$section_id	= AAIH__SCID_POST_PAGE;

	//フィールド：記事タイプ（投稿／固定ページ／両方）
	// ------------------------------------
	$item_name		= 'post_type';

	$field_id		= 'field_id_' . $item_name;
	$field_title	= __( 'Post type', AAIH__TEXT_DOMAIN );	// 投稿タイプ
	$field_callbak	= 'aaih__' . 'fc__post_type';
	$class			= 'post-type';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);


	/* ------------------------------------
	 * section id	広告表示の設定
	 * ------------------------------------ */
	$section_id	= AAIH__SCID_AD_DISPLAY;

	// 記事内広告数設定
	// ------------------------------------
	$item_name		= 'ad_show_num';

	$field_id		= 'field_id_' . $item_name;
	$field_title	= __( 'Max number of ads' , AAIH__TEXT_DOMAIN ); // 記事内の最大広告数
	$field_callbak	= 'aaih__' . 'fc__ad_show_num';
	$class			= 'ad-show-num';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);

	//フィールド：対象Hタグ
	// ------------------------------------
	$item_name		= 'target_h_tag';

	$field_id		= 'field_id_' . $item_name;
	$field_title 	= __( 'Target H tag' , AAIH__TEXT_DOMAIN );
	$field_callbak	= 'aaih__' . 'fc__target_h_tag';
	$class			= 'target-h-tag';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);


	//フィールド：広告の間隔
	// ------------------------------------
	$item_name		= 'ad_space';

	$field_id		= 'field_id_' . $item_name;
	$field_title	= __( 'Space between ads' , AAIH__TEXT_DOMAIN );
	$field_callbak	= 'aaih__' . 'fc__ad_space';
	$class			= 'ad-space';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);

	/* ------------------------------------
	 * section id	広告コードの設定
	 * ------------------------------------ */
	$section_id		= AAIH__SCID_AD_CODE;

	//フィールド：グーグルアドセンス パブリッシャーID
	// ------------------------------------
	$item_name		= 'pub_id';

	$field_id		= 'field_id_' . $item_name;
	$field_title	= __( 'Publisher ID' , AAIH__TEXT_DOMAIN );	// パブリッシャーID
	$field_callbak	= 'aaih__' . 'fc__pub_id';
	$class			= 'hide pub-id';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);

	//フィールド：広告コード選択（Ad1タブ～Adxxタブ）
	// ------------------------------------
	// タブを表示するだけなので field_titleは不要（空文字）
	$item_name		= 'ad_select_tab';

	$field_id		= 'field_id_' . $item_name;
	$field_title	= '';
	$field_callbak	= 'aaih__' . 'fc__ad_select_tab';
	$class			= 'hide ad-select-tab';		// th は隠すので hide を追加

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);


	// 広告コード設定フォーム表示
	// ------------------------------------
	$item_name		= 'ad_code_all';

	// 設定値（固定の初期値）の数だけ設定フォームを表示
	for( $i = 0 ; $i < AAIH__AD_CODE_HOW_MANY ; $i ++ ) {
		$nth	= $i + 1;
		$ad_nth = 'Ad' . $nth;

		$field_id		= 'field_id_' . $item_name . $ad_nth;
		$field_title	= '';	// display none で 非表示
		$field_callbak	= 'aaih__' . 'fc__ad_code';

		// 設定する class を取得

		// ユニーククラス を設定（タブ表示と区別するため）
		// 加えて、th は隠すので hide も追加。
		$class	= 'hide' . ' ad-code-all';
		// 設定する class を取得（タブと共通）
		$class = aaih__get_class__ad_tab_ad_code( $i , $class , $settings );

		// 以上から add_settings_field
		add_settings_field(
			$field_id,		// ① id
			$field_title,	// ② フィールドのタイトル（設定項目のラベル）
			$field_callbak,	// ③ input要素などを表示するコールバック関数
			$page_slug,		// ④ 設定画面のスラッグ
			$section_id,	// ⑤ 表示するセクションのID
			array( 'class' => $class , 'ad_nth' => $ad_nth , 'settings' => $settings ),
		);
	}


	/* ------------------------------------------------------------------------
	 * page slug	タブ：高度な設定
	 * ------------------------------------------------------------------------ */
	$page_slug	= AAIH__PAGE_SLUG['advanced'];

	/* ------------------------------------
	 * section id	文字数カウント関連
	 * ------------------------------------ */
	$section_id	= AAIH__SCID_CHAR_COUNT;

	//フィールド：文字数カウントの単位
	// ------------------------------------
	$item_name		= 'character_width_unit';

	$field_id		= 'field_id_' . $item_name;
	$field_title 	= __( 'Unit (counting chars)' , AAIH__TEXT_DOMAIN );
	$field_callbak	= 'aaih__' . 'fc__character_width_unit';
	$class			= 'character-width-unit';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);

	/* ------------------------------------
	 * section id	広告コード設定関連
	 * ------------------------------------ */
	$section_id		= AAIH__SCID_AD_SETTINGS_ADVANCED;

	//上下の余白を個別設定
	// ------------------------------------
	$item_name		= 'updown_margin_separate_onoff';

	$field_id		= 'field_id_' . $item_name;
	$field_title	= __( 'Margin (top and bottom)' , AAIH__TEXT_DOMAIN ); //上下の余白
	$field_callbak	= 'aaih__' . 'fc__updown_margin_separate_onoff';
	$class			= 'updown-margin-separate-onoff';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);

	//フィールド：スペースの単位
	// ------------------------------------
	$item_name		= 'space_unit';

	$field_id		= 'field_id_' . $item_name;
	$field_title 	= __( 'Unit of space' , AAIH__TEXT_DOMAIN ); // スペースの単位
	$field_callbak	= 'aaih__' . 'fc__space_unit';
	$class			= 'space-unit';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);

	//フィールド：広告ラベル入力の表示
	// ------------------------------------
	$item_name		= 'label_input_onoff';

	$field_id		= 'field_id_' . $item_name;
	$field_title 	= __( 'Use Ad label' , AAIH__TEXT_DOMAIN ); // 広告ラベルの使用
	$field_callbak	= 'aaih__' . 'fc__label_input_onoff';
	$class			= 'label-input-onoff';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);

	//フィールド：メモ（広告名）入力の表示
	// ------------------------------------
	$item_name		= 'memo_input_onoff';

	$field_id		= 'field_id_' . $item_name;
	$field_title 	= __( 'Use Ad memo' , AAIH__TEXT_DOMAIN ); //メモの使用
	$field_callbak	= 'aaih__' . 'fc__memo_input_onoff';
	$class			= 'memo-input-onoff';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);


	/* ------------------------------------
	 * section id	広告置き換え対応
	 * ------------------------------------ */
	$section_id		= AAIH__SCID_AD_REPLACE;

	//フィールド：広告の置き換え
	// ------------------------------------
	$item_name		= 'ad_replace';

	$field_id		= 'field_id_' . $item_name;
	$field_title	= __( 'Ad replacement' , AAIH__TEXT_DOMAIN );
	$field_callbak	= 'aaih__' . 'fc__ad_replace';
	$class			= 'ad-replace';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);


	/* ------------------------------------------------------------------------
	 * page slug	タブ：オプション設定
	 * ------------------------------------------------------------------------ */
	$page_slug	= AAIH__PAGE_SLUG['options'];

	/* ------------------------------------
	 * section id	遅延読込み（Lazy load）対応
	 * ------------------------------------ */
	$section_id		= AAIH__SCID_ADSENSE_LAZY_LOAD;

	//フィールド：アドセンスの遅延読込み
	// ------------------------------------
	$item_name		= 'adsense_lazy_load_onoff';

	$field_id		= 'field_id_' . $item_name;
	$field_title	= __( 'AdSense Lazy Load' , AAIH__TEXT_DOMAIN ); // アドセンス自動広告
	$field_callbak	= 'aaih__' . 'fc__adsense_lazy_load';
	$class			= 'hide adsense-lazy-load-onoff';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);


	/* ------------------------------------
	 * section id	アドセンス自動広告
	 * ------------------------------------ */
	$section_id		= AAIH__SCID_ADSENSE_AUTO_ADS;

	//フィールド：グーグルアドセンスの自動広告
	// ------------------------------------
	$item_name		= 'adsense_auto_ads';

	$field_id		= 'field_id_' . $item_name;
	$field_title	= __( 'AdSense Auto Ads' , AAIH__TEXT_DOMAIN ); // アドセンス自動広告
	$field_callbak	= 'aaih__' . 'fc__adsense_auto_ads';
	$class			= 'hide adsense-auto-ads';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);


	/* ------------------------------------
	 * section id	グーグルアナリティクス : コード ヘッダ挿入
	 * ------------------------------------ */
	$section_id		= AAIH__SCID_ANALYTICS_HEADER_INSERT_CODE;

	//フィールド：アナリィテイクス : ヘッダーコード挿入
	// ------------------------------------
	$item_name		= 'analytics_header_insert_code';

	$field_id		= 'field_id_' . $item_name;
	$field_title	= __( 'Google Analytics' , AAIH__TEXT_DOMAIN ); // グーグルアナリティクス
	$field_callbak	= 'aaih__' . 'fc__analytics_header_insert_code';
	$class			= 'hide header-insert-code';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);


	/* ------------------------------------------------------------------------
	 * page slug	タブ：言語、その他
	 * ------------------------------------------------------------------------ */
	$page_slug	= AAIH__PAGE_SLUG['others'];

	/* ------------------------------------
	 * section id	言語設定
	 * ------------------------------------ */
	$section_id		= AAIH__SCID_LANGUAGE;

	//フィールド：言語
	// ------------------------------------
	$item_name		= 'language';

	$field_id		= 'field_id_' . $item_name;
	$field_title	= __( 'Language' , AAIH__TEXT_DOMAIN ); // 表示言語
	$field_callbak	= 'aaih__' . 'fc__language';
	$class			= 'language';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);

	/* ------------------------------------
	 * section id	利用制限
	 * ------------------------------------ */
	$section_id		= AAIH__SCID_ACCESS_CONTROL;

	//フィールド：利用制限
	// ------------------------------------
	$item_name		= 'access_control_onoff';

	$field_id		= 'field_id_' . $item_name;
	$field_title	= __( 'Access control', AAIH__TEXT_DOMAIN ); // 利用制限
	$field_callbak	= 'aaih__' . 'fc__access_control_onoff';
	$class			= 'hide access-control-onoff';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);

	/* ------------------------------------
	 * section id	デバッグモード
	 * ------------------------------------ */
	$section_id		= AAIH__SCID_DEBUG_MODE;

	//フィールド：デバッグモード
	// ------------------------------------
	$item_name		= 'debug_mode_onoff';

	$field_id		= 'field_id_' . $item_name;
	$field_title	= __( 'Debug mode', AAIH__TEXT_DOMAIN ); // デバッグモード
	$field_callbak	= 'aaih__' . 'fc__debug_mode_onoff';
	$class			= 'hide debug-mode-onoff';

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);

	//タブ番号
	// ------------------------------------
	// hidden で表示するため、どこにおいてもOK
	$item_name		= 'tab_menu_num';

	$field_id		= 'field_id_' . $item_name;
	$field_title	= '';
	$field_callbak	= 'aaih__' . 'fc__tab_menu_num';
	$class			= 'tab-menu-num';
	$item_value		= $settings[ $item_name ];

	add_settings_field(
		$field_id,		// ① id
		$field_title,	// ② フィールドのタイトル（設定項目のラベル）
		$field_callbak,	// ③ input要素などを表示するコールバック関数
		$page_slug,		// ④ 設定画面のスラッグ
		$section_id,	// ⑤ 表示するセクションのID
		array( 'class' => $class , 'settings' => $settings ),
	);
}
?>