<?php
/*
* Plugin Name: Ad Auto Insert H
* Plugin URI:https://tabibitojin.com/ad-auto-insert-h
* Description:Automatically inserts ad codes of Google AdSense before H tags, before the first H tag and at the end of a post or a page. Lazy Load of ads to speed up page display is available. Insertion of AdSense Auto Ads code and Google Analytics code can be set as well.
* Version: 1.4.0
* Author: Jin Koyama
* Author URI:https://tabibitojin.com
* License: GPLv2 or later
* Text Domain: ad-auto-insert-h
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// プラグイン名
define( 'AAIH__PLUGIN_NAME', 'Ad Auto Insert H' );
define( 'AAIH__PLUGIN_NAME_JA', '広告自動追加 H' );

// メニューslug
define( 'AAIH__MENU_SLUG' , 'ad-auto-insert-h' 	);

// プラグインファイルまでの絶対パス
// ～\wp-content\plugins\ad-auto-insert-h\ad-auto-insert-h.php
define( 'AAIH__PLUGIN_FILE_PATH' , __FILE__ );

// プラグインのベースネーム
// ad-auto-insert-h\ad-auto-insert-h.php
define( 'AAIH__PLUGIN_BASENAME' , plugin_basename( __FILE__ ) );

// プラグインの言語フォルダーまでのパス
// ad-auto-insert-h\ad-auto-insert-h.php
define( 'AAIH__PATH_TO_LANGUAGE' , plugin_dir_path( __FILE__ ) . '/languages/' );

// テキストドメイン
define( 'AAIH__TEXT_DOMAIN' , AAIH__MENU_SLUG );
// プラグインの説明 : ヘッダの description に同じ
define( 'AAIH__PLUGIN_DESCRIPTION',
__( 'Automatically inserts ad codes of Google AdSense before H tags, before the first H tag and at the end of a post or a page. Lazy Load of ads to speed up page display is available. Insertion of AdSense Auto Ads code and Google Analytics code can be set as well.' , AAIH__TEXT_DOMAIN ) );
//グーグルアドセンスの広告コードをHタグ前、最初のHタグ前、記事の下に自動で挿入するプラグイン。
// 遅延読み込み（Lazy Load）やアドセンス自動広告、グーグルアナリティクスのコード挿入もできる。

/* -----------------------------------
 * ファイル読み込み
 * ------------------------------------ */
include_once( AAIH__MENU_SLUG . '-defines.php'			); // 定義ファイル
include_once( AAIH__MENU_SLUG . '-ad-format.php'		); // アドセンスの広告、アナリティクスコードの生成、表示、取得
include_once( AAIH__MENU_SLUG . '-common-functions.php' ); // 共通で使う関数たち
include_once( AAIH__MENU_SLUG . '-plugin-functions.php' ); // プラグインの有効化、無効化などの関数たち
include_once( AAIH__MENU_SLUG . '-do-functions.php' 	); // 広告コード挿入に関する関数たち
include_once( AAIH__MENU_SLUG . '-sanitize.php' 		); // サニタイズ
include_once( AAIH__MENU_SLUG . '-settings.php' 		); // 設定画面 ; メインファイル
include_once( AAIH__MENU_SLUG . '-settings-sc.php' 		); // 設定画面 : セクションを追加
include_once( AAIH__MENU_SLUG . '-settings-fld.php' 	); // 設定画面 : フィールドを追加
include_once( AAIH__MENU_SLUG . '-settings-fld-cb.php' 	); // 設定画面 : フィールドのコールバック関数
include_once( AAIH__MENU_SLUG . '-do.php' 				); // 記事に広告を入れるメインファイル
include_once( AAIH__MENU_SLUG . '-auto-lazy.php' 		); // アドセンスの自動広告挿入, 遅延読込み, アナリティクスのコード挿入
include_once( AAIH__MENU_SLUG . '-shortcode.php' 		); // ショートコード
include_once( AAIH__MENU_SLUG . '-custom-field.php' 	); // 投稿や固定ページ編集画面下に表示するカスタムフィールド
include_once( AAIH__MENU_SLUG . '-debug.php' 			); // デバッグ表示
include_once( AAIH__MENU_SLUG . '-reset.php' 			); // 設定値リセット

?>