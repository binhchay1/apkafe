<?php
if ( ! defined( 'ABSPATH' ) ) exit;

register_activation_hook( AAIH__PLUGIN_FILE_PATH , 'aaih__my_plugin_activate' );
/**
 * プラグイン有効化：プラグインの値の登録とアンインストール時の関数の登録
 *
 * - プラグイン有効化時にデータベース に プラグインの値を登録する。
 * - この値のありなしで、有効化時に自動で設定画面へ遷移するかどうか判別するのに使用する。
 * - またここで プラグインのアンインストール時に呼び出される関数もあわせて登録しておく。
 *
 * @return void
 *
 */
function aaih__my_plugin_activate() {

	// データベースへプラグインの値を登録
	add_option( 'aaih__activated_plugin', AAIH__MENU_SLUG );

	// プラグインのアンインストール時に呼び出される関数を登録しておく
	register_uninstall_hook( AAIH__PLUGIN_FILE_PATH , 'aaih__plugin_uninstall' );
}


add_action( 'admin_init', 'aaih__redirect_to_plugin_setting' );
/**
 * プラグイン有効化：自動で設定画面にリダイレクト
 *
 * - プラグインの値があれば削除して設定画面へ自動で遷移する。
 * - 初期の有効後直後に1度だけ実行。
 * - 既にデータベース上にプラグインの設定値がある場合（ユーザーが一度でデータの保存をした場合）には自動遷移させない
 * （既に一度で捜査したことがある場合、無効化⇒有効化でまた設定画面へ自動遷移するのは、操作上、何か面倒な気がするので）
 *
 * admin_initフックを使う
 *
 * @return void
 */
function aaih__redirect_to_plugin_setting() {

	if ( is_admin() && AAIH__MENU_SLUG === get_option( 'aaih__activated_plugin' ) ) {
		// 有効化直後に1度だけ実行のため、aaih__activated_plugin はまず削除しておく
		delete_option( 'aaih__activated_plugin' );

		/*
		* データベース上に設定値（ AAIH__SETTINGS ）が保存されている場合
		* （過去にこのプラグインを使用していたケース）
		* 既に設定画面で一度は設定している、ということで自動で設定画面には遷移させない
		*/
		if ( get_option( AAIH__SETTINGS ) ) {
			return;
		}
		// インストール後の有効化で、設定画面へ自動で遷移
		aaih__redirect_to_plugin();
	}
}


add_filter( 'plugin_action_links_' . AAIH__PLUGIN_BASENAME , 'aaih__add_action_links' );
/**
 * プラグイン一覧に「設定」メニュー追加
 *
 * plugin_action_links_xxx フィルターフックを使う
 *
 * @param string[] $actions	 	プラグインアクションリンクの配列。（有効化、無効化、削除、といったプラグインのメニューのリスト）
 * @return string[] $actions	「設定」メニュー追加後のプラグインアクションリンクの配列。
 *
 * 関連
 * apply_filters( "plugin_action_links_{$plugin_file}", string[] $actions, string $plugin_file, array $plugin_data, string $context )
 * https://developer.wordpress.org/reference/hooks/plugin_action_links_plugin_file/
 */
function aaih__add_action_links( $actions ) {
	$menu_name			= __( 'Settings', AAIH__TEXT_DOMAIN );
	$menu_settings_url	= '<a href="options-general.php?page=' . AAIH__MENU_SLUG . '">' . $menu_name . '</a>';

	//配列の先頭へ追加
	array_unshift( $actions , $menu_settings_url );

	return $actions;
}

/* -----------------------------------
 * プラグインの削除（アンインストール）時の処理
 * -----------------------------------
 * 関数は aaih__my_plugin_activate にて登録済
 *
 * DB上のデータの削除
 * キャッシュ（バージョン情報）の削除
 *
 * ----------------------------------- */
/**
 * プラグインの削除（アンインストール）時の処理
 *
 * - 関数は aaih__my_plugin_activate にて登録済。
 * - DB上のデータの削除、キャッシュ（バージョン情報）の削除を行う。
 *
 * @return void
 */
function aaih__plugin_uninstall() {
	// DB上のデータの削除
	delete_option( AAIH__SETTINGS );

	// キャッシュ（バージョン情報）の削除
	aaih__delete_cache__plugin_version();
}


/* ----------------------------------------------------------------------
 * プラグインバージョン関連
 * ---------------------------------------------------------------------- */

/**
 * プラグインバージョンの取得
 *
 * @return string $plugin_data['Version']	プラグインのバージョン文字列（例 0.9.1）
 *
 * 関連：
 * get_plugin_data（string $plugin_file、 bool $markup = true、 bool $translate = true ）
 *
 * $plugin_file（文字列） （必須） メインプラグインファイルへの絶対パス。
 * https://developer.wordpress.org/reference/functions/get_plugin_data/
 *
 * 補足：wp-admin/includes/plugin.php を読みこまないと動作しない場合があるようだ
 *
 */
function aaih__get_plugin_version() {
	if ( ! function_exists( 'get_plugin_data' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	$plugin_data = get_plugin_data( AAIH__PLUGIN_FILE_PATH );
	return $plugin_data['Version'];
}


/**
 * プラグインバージョンのバージョンをキャッシュ
 *
 * キャッすする期限（$expiration）は設定しない。（ずっと保持する）
 * @return void
 */
function aaih__cache_plugin_version() {
	$plugin_version	= aaih__get_plugin_version();
	set_transient( AAIH__PLUGIN_VER_CACHE_NAME , $plugin_version );
}

/**
 * プラグインバージョンのキャッシュを削除
 *
 * @return void
 */
function aaih__delete_cache__plugin_version() {
	delete_transient( AAIH__PLUGIN_VER_CACHE_NAME );
}


/**
 * プラグインバージョン違いをチェック（アップデートチェック）
 *
 * @return string
 * - キャッシュがない場合	: 現バージョンをキャッシュにセットし、'no_version' を返す
 * - バージョンが同じ		: 'same_version' を返す
 * - バージョンが異なる	: 現バージョンをキャッシュにセットし、'different_version' を返す
 */
function aaih__check_plugin_update() {
	$this_version	= aaih__get_plugin_version();
	$cache_version	= get_transient( AAIH__PLUGIN_VER_CACHE_NAME );

	// キャッシュがない
	if ( false === $cache_version ) {
		// バージョンをキャッシュにセット
		aaih__cache_plugin_version();
		return 'no_version';

	}elseif ( $this_version === $cache_version ) {
	// バージョンが同じ
		return 'same_version';
	}
	else {
	// バージョンが異なる
	// 現バージョンをキャッシュにセット
		aaih__cache_plugin_version();
		return 'different_version';
	}
}


add_action( 'plugins_loaded' , 'aaih__update_check_settings' );
/**
 * バージョンアップされた時の対応
 *
 * - plugins_loadedフックでコールして、設定値の整合性をチェックする。
 * - バージョンが異なる場合、設定値のdefault に対して過不足があれば整理して データのアップデートを行う。
 *
 * @return void
 *
 * 補足
 * upgrader_process_complete フックというのがあり、
 * これを使うと現在のプラグインが更新されている場合は、何かを実行する、というのができるようだ。
 * でも upgrader_process_complete は チェックができないので使わない。
 *
 * 参考：upgrader_process_complete
 * https://developer.wordpress.org/reference/hooks/upgrader_process_complete/
 */
function aaih__update_check_settings() {
	/* -----------------------------------
	 * プラグインバージョンチェック
	 * ----------------------------------- */
	// ワードプレスのキャッシュに保存するプラグインのバージョン違いにより
	// アップデートされたかどうかをチェック

	$check_plugin_update = aaih__check_plugin_update();

	// バージョンが同じ（ same_version ）なら何もしない
	// バージョン情報がまだない（ no_version ）場合も何もしない
	if ( 'same_version' === $check_plugin_update || 'no_version' === $check_plugin_update ) {
		return;
	}

	// バージョンが異なる場合（アップデートされた場合）には以下対応
	/* -----------------------------------
	 * 設定値がない場合（念のための処理）
	 * ----------------------------------- */
	if ( false === get_option( AAIH__SETTINGS ) ) {
		// 設定値がなければ特に何もしないでOK
		return;

		// 設定値がない場合は、
		// aaih__get_item でデフォルト値にセットしているからOK
	}

	/* ----------------------------------------------------------------------
	 * 設定値がある場合
	 * --------------------------------------------------------------------- */
	// ①：$defaultに比べて、不足しているキーがあれば追加（アップデート対応）
	// ②：$defaultに比べて、余分なキーがあれば削除（アップデート対応）

	$settings 			= aaih__get_item( '', 'version_up');	// 設定値の取得
	$default_array 		= aaih__get_default_values();			// default の取得

	/* -----------------------------------
	 * ①：$default_array に比べて、不足しているキーがあれば追加
	 * ----------------------------------- */
	// DB に保存されている設定のキー数をチェック
	$array_num_before__first_dimension		= count( $settings );	// 1次元チェック
	$array_num_before__ad					= count( $settings['Ad1'] );				// 最初の1つをチェック
	$array_num_before__shortcode_replace	= count( $settings['shortcode_replace1'] );	// 最初の1つをチェック

	$settings 	= array_replace_recursive( $default_array , $settings );
	// array_merge_recursive( $default_array , $args )
	// $default_array に $args をマージ
	// 同じキーがある場合には $args の値で上書き
	// https://www.php.net/manual/ja/function.array-merge-recursive.php

	// デフォルトをマージした後のキー数をチェック
	$array_num_after__first_dimension		= count( $settings );	// 1次元チェック
	$array_num_after__ad					= count( $settings['Ad1'] );				// 最初の1つをチェック
	$array_num_after__shortcode_replace		= count( $settings['shortcode_replace1'] );	// 最初の1つをチェック

	// 上で調べた各々の数について、数が増えていれば yes, そうでなければ no
	$add_change__first_dimension	= ( $array_num_after__first_dimension > $array_num_before__first_dimension ? 'yes': 'no' );
	$add_change__ad					= ( $array_num_after__ad > $array_num_before__ad ? 'yes': 'no' );
	$add_change__shortcode_replace	= ( $array_num_after__shortcode_replace > $array_num_before__shortcode_replace ? 'yes': 'no' );

	if( 'yes' === $add_change__first_dimension || 'yes' === $add_change__ad || 'yes' === $add_change__shortcode_replace ){
		$add_change = 'yes';
	}else{
		$add_change = 'no';
	}

	/* -----------------------------------
	 * ②：$default_array に比べて、余分なキーがあれば削除
	 * ----------------------------------- */

	// 1次元対応：$arg ⇒ $arg1
	$result1		= aaih__delete_keys_no_in_default( $settings , $default_array );

	$settings1		= $result1['args'];
	$delete_change1	= $result1['change'];

	// 二次元対応
	// 上でチェックした settings1 に対して配列のキーをチェック : Ad1, Ad2, ... , shortcode_replace1, shortcode_replace2
	// settings1 中の settings2 を settings1 へ反映
	$delete_change2		= 'no';	// 最初は no をセットしておく
	foreach ( $settings1 as $key => $value ) {
		// キーの値が配列の場合
		if ( is_array( $value ) ) {
			// Ad か shortcode_replace で場合分け
			$prefix_name = aaih__get_only_prefix( $key );
			// キーに対する値（配列）
			$settings2	= $value;

			switch ( $prefix_name ) {
				case 'Ad':
					$default_array2 = AAIH__SETTINGS_DEFAULT__AD['Ad'];
					break;
				case 'shortcode_replace':
					$default_array2 = AAIH__SETTINGS_DEFAULT__SHORTCODE_REPLACE['shortcode_replace'];
					break;

				default:
					$msg = 'aaih__update_check_settings: no case: ' . $key;
					aaih__popup_alert( $msg );
			}

			$result2		= aaih__delete_keys_no_in_default( $settings2 , $default_array2 );
			$settings2		= $result2['args'];
			$delete_change	= $result2['change'];

			// 削除した結果を元の値に反映
			// Ad1, Ad2, .... と複数回チェックするので、1回でも yes になった場合は $delete_change2 に yes をセット。
			if ( 'yes' === $delete_change ) {
				$delete_change2		= 'yes';
				$settings1[ $key ] 	= $settings2;
			}
		}
	}

	// 何かしら元の値に変更が入ったらデータ検証してアップデート
	// アップデートするのは $settings1
	if ( 'yes' === $add_change || 'yes' === $delete_change1 || 'yes' === $delete_change2 ) {
		$settings1 	= aaih__item_validation( $settings1 );	// データ検証
		update_option( AAIH__SETTINGS , $settings1 );
	}
}


/**
 * $default_array にはないキーを削除
 *
 * - ① $args にしかないキーを取得
 * - ② 取得したキーを $args から削除
 *
 * @param array $args			設定の配列
 * @param array $default_array	デフォルトの設定の配列
 * @return array ( 'args' => $args, 'change' => $change ) 余分なキーを削除した配列と変更したかどうかを配列にして返す
 * string $change 'yes' or 'no'
 */
function aaih__delete_keys_no_in_default( $args , $default_array ) {
	// $args にしかないキーを取得
	$diff_array = array_diff_key( $args , $default_array );

	$change = 'no';
	if ( ! empty( $diff_array ) ) {
		// 取得した差分のキーを削除
		foreach( array_keys( $diff_array ) as $key ) {
			unset( $args[ $key ] );
		}
		$change = 'yes';
	}

	// 余分なキーを削除した配列を返す
	return array ( 'args' => $args, 'change' => $change );
}
?>