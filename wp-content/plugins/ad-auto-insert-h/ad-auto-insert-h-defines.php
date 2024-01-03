<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* -----------------------------------
* 各種定義
*
* ★★★ defineで配列が使えるのは php 7 以上
* AAIH__MENU_SLUG のみ メインファイルで定義
*
* ----------------------------------- */

/* -----------------------------------
* 全体
* ------------------------------------ */
// 設定値の変数名（配列）
define( 'AAIH__SETTINGS'				, 'aaih_settings' 		);

// 各タブSLUG
define( 'AAIH__SUB_GENERAL_SETTINGS'	, '-general-settings' 	); // 一般設定
define( 'AAIH__SUB_ADVANCED_SETTINGS'	, '-advanced-settings'	); // 高度な設定
define( 'AAIH__SUB_OPTIONS'				, '-adsense-options' 	); // オプション
define( 'AAIH__SUB_LANGUAGES_OTHERS'	, '-language-others' 	); // Language, その他

// 各タブのページSLUG
define( 'AAIH__PAGE_SLUG', array(
		'general'	=> AAIH__MENU_SLUG . AAIH__SUB_GENERAL_SETTINGS,
		'advanced'	=> AAIH__MENU_SLUG . AAIH__SUB_ADVANCED_SETTINGS,
		'options'	=> AAIH__MENU_SLUG . AAIH__SUB_OPTIONS,
		'others' 	=> AAIH__MENU_SLUG . AAIH__SUB_LANGUAGES_OTHERS,
	)
);

// キャッシュ用
define( 'AAIH__PLUGIN_VER_CACHE_NAME' 		, 'aaih__cache__plugin_version' );	// プラグインバージョンのキャッシュ用
define( 'AAIH__RESET_SETTINGS_CACHE_NAME' 	, 'aaih__cache__reset_settings' );	// 設定リセットのキャッシュ用
define( 'AAIH__CACHE_RESET_EXPIRATION_TIME' , 60 * 5 );							// 設定リセットのキャッシュの期限（sec）

// nonce関連
define( 'AAIH__NONCE_NAME__META_BOX' 	, 'aaih__meta_box_nonce' );		// メタフィールド：nonce name
define( 'AAIH__NONCE_ACTION__META_BOX'	, 'aaih__save_meta_box_data' );	// メタフィールド：nonce action

define( 'AAIH__NONCE_NAME__RESET_SETTINGS'		, 'aaih__nonce_name__reset_settings' );		// 設置リセット：nonce name
define( 'AAIH__NONCE_ACTION__RESET_SETTINGS'	, 'aaih__nonce_action__reset_settings' );	// 設置リセット：nonce action
define( 'AAIH__POST_NAME__RESET_SETTINGS'		, 'aaih__post_name__reset_settings' );		// 設置リセット：POST name

/* -----------------------------------
 * フィールドグループ
 * ------------------------------------ */
define( 'AAIH__FIELD_GROUP', 'aaih_field-group' );

/* -----------------------------------
 * セクション
 * ------------------------------------ */
define( 'AAIH__SCID_BASIC', 				'aaih__scid_basic' 					);
define( 'AAIH__SCID_POST_PAGE', 			'aaih__scid_post_page' 				);
define( 'AAIH__SCID_AD_DISPLAY', 			'aaih__scid_ad_display' 			);
define( 'AAIH__SCID_AD_SELECT', 			'aaih__scid_ad_select' 				);
define( 'AAIH__SCID_AD_CODE', 				'aaih__scid_ad_code' 				);
define( 'AAIH__SCID_CHAR_COUNT', 			'aaih__scid_char_count' 			);
define( 'AAIH__SCID_AD_SETTINGS_ADVANCED',	'aaih__scid_ad_settings_advanced' 	);
define( 'AAIH__SCID_ACCESS_CONTROL', 		'aaih__scid_access_control' 		);
define( 'AAIH__SCID_DEBUG_MODE', 			'aaih__scid_debug_mode' 			);
define( 'AAIH__SCID_AD_REPLACE', 			'aaih__scid_ad_replace' 			);
define( 'AAIH__SCID_ADSENSE_AUTO_ADS', 		'aaih__scid_adsense_auto_ads' 		);
define( 'AAIH__SCID_ANALYTICS_HEADER_INSERT_CODE', 	'aaih__scid_analytics_header_insert_code' 	);
define( 'AAIH__SCID_ADSENSE_LAZY_LOAD', 	'aaih__scid_adsense_lazy_load'		);
define( 'AAIH__SCID_LANGUAGE', 				'aaih__scid_language' 				);

/* -----------------------------------
 * グーグルアドセンス 広告コードフォーマット
 * ------------------------------------ */
define( 'AAIH__AD_ID_NO_VALUE', 	'xxxxxxxxxx' );	// ID入力がない場合の表示

// アドセンスコード（自動広告コード）（ js でも扱えるように 最後の </script>タグは 閉じタグとして認識されないように分解しておく）
define( 'AAIH__ADSENSE_CODE__BEFORE' ,	'<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-' );	// 前半
define( 'AAIH__ADSENSE_CODE__AFTER' ,	'"
     crossorigin="anonymous"><' );	// 後半 （アドセンスのコードには改行、スペースが入っている（多分 アドセンスのページ上で綺麗に見れるよう style とか data-ad-client などと揃えている）。視覚的に同じになるように同様に入れておく）
define( 'AAIH__ADSENSE_CODE__END' ,	'/script>' );	// 最後

// 広告ユニット : ディスプレイ広告
define( 'AAIH__ADSENSE_DISPLAY_AD_CODE__BEFORE_ID' , '<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-' );	//pub-xxxx
define( 'AAIH__ADSENSE_DISPLAY_AD_CODE__BEFORE_DATA_AD_SLOT' , '"
     ' );		//　data-ad-slot
define( 'AAIH__ADSENSE_DISPLAY_AD_CODE__BEFORE_DATA_FORMAT' , '
     ' );	//　data-ad-format
define( 'AAIH__ADSENSE_DISPLAY_AD_CODE__END' , '
     data-full-width-responsive="true"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script>' );

// 広告ユニット : 記事内広告
define( 'AAIH__ADSENSE_IN_ARTICLE_AD_CODE__BEFORE_ID' , '<ins class="adsbygoogle"
     style="display:block; text-align:center;"
     data-ad-layout="in-article"
     data-ad-format="fluid"
     data-ad-client="ca-' ); //pub-xxxx
define( 'AAIH__ADSENSE_IN_ARTICLE_AD_CODE__BEFORE_DATA_AD_SLOT' , '"
     ' );		// data-ad-slot
define( 'AAIH__ADSENSE_IN_ARTICLE_AD_CODE__END' , '></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script>' );

// 広告ユニット : マルチミックス広告
define( 'AAIH__ADSENSE_MULTIPLEX_AD_CODE__BEFORE_ID' , '<ins class="adsbygoogle"
     style="display:block"
     data-ad-format="autorelaxed"
     data-ad-client="ca-' ); 	// pub-xxxxx
define( 'AAIH__ADSENSE_MULTIPLEX_AD_CODE__BEFORE_DATA_AD_SLOT' , '"
     ' );			//　data-ad-slot
define( 'AAIH__ADSENSE_MULTIPLEX_AD_CODE__END' , '></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script>' );


/* -----------------------------------
 * グーグルアナリティクス：トラッキングコードフォーマット
 * ------------------------------------ */
define( 'AAIH__ANALYTICS_CODE__URL_BEFORE_ID',	'<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=' );

define( 'AAIH__ANALYTICS_CODE__URL_AFTER_ID',	'"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag(');

define( 'AAIH__ANALYTICS_CODE__FUNCTION_BEFORE_ID', "'js', new Date());

  gtag('config', '");

define( 'AAIH__ANALYTICS_CODE__FUNCTION_AFTER_ID', "');
</script>" );


/* -----------------------------------
 * 設定値の初期
 * ------------------------------------ */
define( 'AAIH__AD_SHOW_NUM_MIN', 	0 		);	// 記事内広告の上限 min
define( 'AAIH__AD_SHOW_NUM_MAX', 	10 		);	// 記事内広告の上限 max
define( 'AAIH__AD_SHOW_NUM_INIT', 	0 		);	// 記事内広告の上限 初期（0 で無制限）

define( 'AAIH__META__AD_SHOW_NUM_MIN', 	-1		);	// カスタムフィールド : -1 で設定値に同じ

define( 'AAIH__AD_SPACE_INIT', 		1000 	);	// 広告の間隔（文字数指定）init
define( 'AAIH__AD_SPACE_MIN', 		100		);	// 広告の間隔（文字数指定）min
define( 'AAIH__AD_SPACE_MAX', 		5000 	);	// 広告の間隔（文字数指定）max
define( 'AAIH__AD_SPACE_STEP',		10		);	// 広告の間隔（文字数指定）ステップ

define( 'AAIH__AD_CODE_HOW_MANY', 	6	 	);	// ★★★ 設定できる広告コードの数


// 広告コード設定

define( 'AAIH__PUB_ID_MAXLENGTH',			50 );	// ★ グーグルアドセンス パブリッシャーID：最大文字数 (変な範囲でコピペされた時を考えて多めに取っておく)
define( 'AAIH__DATA_AD_SLOT_MAXLENGTH',		50 );	// ★ グーグルアドセンス data-ad-slot：最大文字数 (変な範囲でコピペされた時を考えて多めに取っておく)
define( 'AAIH__AD_CODE_LABEL_MAXLENGTH',	30 );	// ★ 広告のラベル：最大文字数
define( 'AAIH__AD_CODE_NAME_MAXLENGTH',		30 );	// ★ 広告のメモ（広告名）：最大文字数

define( 'AAIH__AD_CODE_UPDOWN__STEP_EM',			0.25	);	// 広告の上下のマージン：em 数値入力のステップ
define( 'AAIH__AD_CODE_UPDOWN__MARGIN_EM_INIT', 	2 		);	// 広告の上下のマージン（em）初期値
define( 'AAIH__AD_CODE_UPDOWN__MARGIN_EM_MIN', 		0 		);	// 広告の上下のマージン min（em）
define( 'AAIH__AD_CODE_UPDOWN__MARGIN_EM_MAX', 		20 		);	// 広告の上下のマージン max（em）

define( 'AAIH__AD_CODE_UPDOWN__STEP_PX',			1		);	// 広告の上下のマージン：px 数値入力のステップ
define( 'AAIH__AD_CODE_UPDOWN__MARGIN_PX_INIT', 	32 		);	// 広告の上下のマージン（px）
define( 'AAIH__AD_CODE_UPDOWN__MARGIN_PX_MIN', 		0	 	);	// 広告の上下のマージン min（px）
define( 'AAIH__AD_CODE_UPDOWN__MARGIN_PX_MAX', 		350 	);	// 広告の上下のマージン max（px）// cocoon 使用で、目視で em の max と同等の縦幅を上限に設定


define( 'AAIH__AD_CODE_LABEL__STEP_EM',			0.25	);	// 広告ラベルと広告の間：em 数値入力のステップ
define( 'AAIH__AD_CODE_LABEL__SPACE_EM_INIT', 	1 		);	// 広告ラベルと広告の間のマージン（em）初期値
define( 'AAIH__AD_CODE_LABEL__SPACE_EM_MIN', 	0 		);	// 広告ラベルと広告の間のマージン min（em）
define( 'AAIH__AD_CODE_LABEL__SPACE_EM_MAX', 	5		);	// 広告ラベルと広告の間のマージン max（em）

define( 'AAIH__AD_CODE_LABEL__STEP_PX',			1		);	// 広告ラベルと広告の間：px 数値入力のステップ
define( 'AAIH__AD_CODE_LABEL__SPACE_PX_INIT', 	16 		);	// 広告ラベルと広告の間のマージン（px）初期値
define( 'AAIH__AD_CODE_LABEL__SPACE_PX_MIN', 	0	 	);	// 広告ラベルと広告の間のマージン min（px）
define( 'AAIH__AD_CODE_LABEL__SPACE_PX_MAX', 	100 		);	// 広告ラベルと広告の間のマージン max（px）// cocoon 使用で、目視で em の max と同等の縦幅を上限に設定

define( 'AAIH__SHORTCODE_REPLACE_MAX_NUM',		3		);		// ★★★ ショートコードを置き換えられる数
define( 'AAIH__SHORTCODE_REPLACE_MAXLENGTH',	50		);		// ★ 置き換え対象のショートコード最大文字数

define( 'AAIH__ADSENSE_AUTO_AD_SCRIPT_MAXLENGTH',	500 	);	// ★ アドセンス自動広告コード：最大文字数（拡張とか考えて多めにしておく）

// アナリティクス
define( 'AAIH__ANALYTICS_ID_MAXLENGTH',			30	);					// ★ アナリティクス : Tracking ID 00000 of UA-000000-2 , Measurement ID	XXXXXXX of G-XXXXXXX の最大文字数
define( 'AAIH__ANALYTICS_ID_NOTHING_ALL_STR' , 'UA-xxxxx-xx/G-xxxxx' );	// アナリティクス : ID入力が無い場合の表示文字列

// 遅延読込み
define( 'AAIH__AD_LAZY_LOAD_AUTO_INIT',	3 	);	// 遅延読み込み（Lazy Load）の自動読み込み時間（秒）：初期値
define( 'AAIH__AD_LAZY_LOAD_AUTO_MIN', 	0 	);	// 遅延読み込み（Lazy Load）の自動読み込み時間（秒）：min
define( 'AAIH__AD_LAZY_LOAD_AUTO_MAX', 	10 	);	// 遅延読み込み（Lazy Load）の自動読み込み時間（秒）：max

/* -----------------------------------
 * 初期値
 * ------------------------------------ */
define( 'AAIH__SETTING_DEFAULT__BASIC', array(
	'tab_menu_num'							=> 0,			// int: タブメニュー番号（全体：最上段）0 ～ 4
	'ad_code_tab_num'						=> 0,			// int: タブメニュー番号（広告コード入力）0 ～ AAIH__AD_CODE_HOW_MANY - 1
	'h_tag_ad_onoff'						=> 'off',		// H2タグ前Adの有効無効
	'first_h_tag_ad_onoff'					=> 'off',		// 最初のH2タグ前Adの有効無効
	'after_content_ad_onoff'				=> 'off',		// 記事下Adの有効無効
	'post_type'								=> 'post',		// 対象記事の種別（投稿／固定ページ／両方）
	'ad_show_num'							=> 0,			// int: 記事内広告の上限（ 0 は無制限）
	'target_h_tag'							=> 'H_tag_all',	// 対象とするHタグ
	'pub_id'								=> '',			// グーグルアドセンス：パブリッシャーID
	'pub_id__auto_ad'						=> '',			// グーグルアドセンス：パブリッシャーID　自動広告
	'ad_space'								=> AAIH__AD_SPACE_INIT,	// int: 広告の間隔（文字数指定）
	'ad_select__first_h_tag'				=> 'Ad1',		// 広告選択）先頭Hタグ前Adの選択
	'ad_select__h_tag'						=> 'Ad1',		// 広告選択）Hタグ前Adの選択
	'ad_select__after_content'				=> 'Ad1',		// 広告選択）記事下Adの選択
	'character_width_unit'					=> 'full',		// 文字数カウントの単位（全角：full / 半角：half）
	'updown_margin_separate_onoff'			=> 'off',		// 広告の上下余白を上側、下側個別に設定する
	'space_unit'							=> 'em',		// スペースの単位
	'label_input_onoff'						=> 'off',		// 広告ラベル入力欄の表示
	'memo_input_onoff'						=> 'off',		// メモ入力欄の表示
	'ad_replace_onoff'						=> 'off',		// ショートコードの置きかえ
	'ad_replace___h_tag_ad__off'			=> 'off',		// ショートコードの置きかえ : H2タグ前Adの無効
	'ad_replace___first_h_tag_ad__off'		=> 'off',		// ショートコードの置きかえ : 最初のH2タグ前Adの無効
	'ad_replace___after_content_ad__off'	=> 'off',		// ショートコードの置きかえ : 記事下Adの無効
	'adsense_lazy_load_onoff'				=> 'off',		// グーグルアドセンス: 遅延読み込み 有効無効
	'adsense_lazy_load_no_pc_onoff'			=> 'off',		// グーグルアドセンスの遅延読み込み PCでは行わない設定
	'adsense_lazy_load_second'				=> 3,			// int: グーグルアドセンス:	遅延読み込み 自動で読み込むまでの秒数指定
	'adsense_auto_ads_onoff'				=> 'off',		// グーグルアドセンス: 自動広告 有効無効
	'adsense_auto_ads__post_off'			=> 'off',		// グーグルアドセンス: 自動広告 投稿を無効
	'adsense_auto_ads__page_off'			=> 'off',		// グーグルアドセンス: 自動広告 固定ページを無効
	'analytics_header_insert_code_onoff'	=> 'off',		// その他ヘッダ挿入コード 有効無効
	'analytics_id'							=> '',			// グーグルアナリティクス：Tracking ID : 000000 of UA-000000-2 or Measurement ID : XXXXXXX of G-XXXXXXX
	'language'								=> 'en',		// 表示言語 （仮に決めてるだけ：実際には 初期値の取得時 ワードプレスの locale 値 をセットする）
	'access_control_onoff'					=> 'off',		// 利用制限：ユーザーログイン時、管理者のみ機能させるかどうかの設定（編集者や投稿者などは機能しない）
	'debug_mode_onoff'						=> 'off',		// debug mode
	'debug_mode_summary_disable'			=> 'off',		// debug mode 設定サマリー表示を無効にする
	)
);


define( 'AAIH__SETTINGS_DEFAULT__SHORTCODE_REPLACE', array(
	'shortcode_replace'	=> array(
		'replace_code'		=> '', 		// 置き換える対象のショートコード
		'replace_ad_select'	=> 'Ad1',	// 置き換えるAdxx
		),
	)
);

define( 'AAIH__SETTINGS_DEFAULT__AD', array(
	'Ad'	=> array(
		'ad_unit_select'			=> 'in_article',	// 記事内広告 : display / in_article / multiplex
		'ad_unit_id__in_article'	=> '',		// グーグルアドセンス 広告ユニット ID （ xxxxx of data ad slot=xxxxx）
		'ad_unit_id__display'		=> '',		// グーグルアドセンス 広告ユニット ID （ xxxxx of data ad slot=xxxxx）
		'ad_unit_id__multiplex'		=> '',		// グーグルアドセンス 広告ユニット ID （ xxxxx of data ad slot=xxxxx）
		'ad__data_ad_format'		=> 'auto',	// グーグルアドセンス ディスプレイ広告のオプション auto（自動調整） / rectangle（長方形） / vertical（縦長） / horizontal（横長）
		'updown_margin_em'			=> AAIH__AD_CODE_UPDOWN__MARGIN_EM_INIT,	// float 上下のマージン em
		'updown_margin_down_em'		=> AAIH__AD_CODE_UPDOWN__MARGIN_EM_INIT,	// float 下のマージン em
		'updown_margin_px'			=> AAIH__AD_CODE_UPDOWN__MARGIN_PX_INIT,	// float 上下のマージン px （emにあわせてfloatにしておく）
		'updown_margin_down_px'		=> AAIH__AD_CODE_UPDOWN__MARGIN_PX_INIT,	// float 下のマージン px （emにあわせてfloatにしておく）
		'centering'					=>'off',	// センタリング on/off
		'label'						=> '', 		// 広告ラベル
		'label_space_em'			=> AAIH__AD_CODE_LABEL__SPACE_EM_INIT, 	// float 広告ラベルと広告の間のスペース em
		'label_space_px'			=> AAIH__AD_CODE_LABEL__SPACE_PX_INIT, 	// float 広告ラベルと広告の間のスペース px
		'name'						=>'',		// 広告名などのメモ
		),
	)
);

/* -----------------------------------
 * ショートコード[aaih xxxx]
 * ------------------------------------ */
define( 'AAIH__SHORTCODE_NAME'		, 'aaih' );		// ショートコードの名称

/* -----------------------------------
 * メタ（カスタムフィールド）
 * ------------------------------------ */
// same: 設定値に同じ
define( 'AAIH__META_DEFAULT', array(
		'meta__ad_off' 				=> 'off',					// 広告の表示を全てoff（3つの設定を強制OFF） on / off
		'meta__target_h_tag'		=> 'same',					// 対象とするHタグ	same / H_tag_all / H2_only
		'meta__ad_space_change'		=> 'same',					// 広告の間隔を変更するかのon/off	same / change
		'meta__ad_space'			=> AAIH__AD_SPACE_INIT,		// absint: 広告の間隔（文字数指定）
		'meta__debug_mode_onoff'	=> 'same',					// デバッグモード same / on / off
		'meta__ad_show_num'			=> -1,						// int: 記事内広告の上限（ 0：無制限）
	)
);

/* -----------------------------------
 * 共通文言
 * ------------------------------------ */
/**
 * 共通文字列を返す common strings
 *
 * @param string $str
 * @param string $plural	単数、複数があり、複数の場合には 2 以上をセット
 * @return string 共通で使う文字列を返す
 */
function aaih__common_str( $str , $number = 1 ){

	switch ( $str ) {
		// コード表示
		case 'code_up_supplement';
			$str	= __( 'According to the setting, the following code is automatically inserted.', AAIH__TEXT_DOMAIN );
			// 設定に従い、以下のコードをヘッダに自動で挿入します。
			break;
		case 'code_down_supplement';
			$str	= _n( 'Code insertion works only when the ID is set.' , 'Code insertion works only when the IDs are set.' , $number , AAIH__TEXT_DOMAIN );
			// コードの挿入は、IDが入力されている場合にのみ動作します。
			break;

		// 広告名称
		case 'in_article':
		case 'display':
		case 'multiplex':
			$ad_unit_name = array(
				'in_article'	=> __( 'In-article'		, AAIH__TEXT_DOMAIN ),	// 記事内広告
				'display'		=> __( 'Display'		, AAIH__TEXT_DOMAIN ),	// ディスプレイ広告
				'multiplex'		=> __( 'Multiplex ads'	, AAIH__TEXT_DOMAIN ),	// Multiplex 広告
			);
			$str	= $ad_unit_name[ $str ];
			break;

		// リセットボタン
			case 'reset_button_name':
				$str = __( 'Reset All Settings'	, AAIH__TEXT_DOMAIN );
			break;

		default:
			aaih__popup_alert( 'aaih__cstr no case : ' . $str );
	}

	return $str;
}
?>