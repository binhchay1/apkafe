<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ショートコードを生成
 *
 * @param string $key	'ad'
 * @param string $value	'Ad1' , 'Ad2', ...
 * @return string ショートコード文字列　[aaih ad=Ad1]
 *
 * 補足
 * ad 以外にも使用する予定で関数化しているが、結果として 'ad' のみで使用している
 */
function aaih__shortcode_create( $key, $value ) {
	$shortcode	= '[' .AAIH__SHORTCODE_NAME. ' ' . $key . '=' . $value . ']';
	return $shortcode;
}

/**
 * ショートコードタグ用のフックを追加
 *
 * @param string $tag	AAIH__SHORTCODE_NAME	投稿の本文から検索する、ショートコードタグ
 * @param string $func	'aaih__shortcode_ad'	ショートコードが見つかったときに実行するコールバック関数
 *
 * 補足：
 * コールバック関数は何も出力してはいけない。
 * ショートコードを置き換えるために使われるテキストを返すことが必要。
 */
add_shortcode( AAIH__SHORTCODE_NAME, 'aaih__shortcode_ad' );

/**
 * ショートコードが見つかったときに実行するコールバック関数
 *
 * @param string $args	投稿の本文から検索する、ショートコードタグ
 * @return string	広告コード（ + デバッグメッセージ ）
 */
function aaih__shortcode_ad( $args ) {
	static $shortcode__ad_show_count = 0;

	if( ! is_single() && ! is_page() ) {	// exclude editor
		return '';
	}
	if ( 'stop' ===  aaih__chk_login_user() ) {
		return;	// 利用制限 ON 時、ログインの状態で、ユーザーが管理者以外の場合
	};

	// サポートするすべての属性の名前とデフォルト値
	//（最終的に ad だけ残し、削ったものははカスタムフィールドで設定できるようにした）
	$pairs	= array(
		'ad'	=> 'none', // 手動で指定（Ad1～Ad5）
		//'ad'	=> 'none', // 広告表示なし or 手動で指定（Ad1～Ad5）
		//'num'	=> 'none', // 広告表示数の上限
		//'htag'	=> 'none', // hタグの対象
		//'space' => 'none', // 広告の間のスペース
	);

	/* shortcode_atts( $pairs , $atts, $shortcode );
	* $pairs		:（配列） （必須） サポートするすべての属性の名前とデフォルト値
	* $atts			:（配列） （必須） ユーザーがショートコードタグに指定した属性
	* $shortcode	:（文字列） （オプション） shortcode_atts_{$shortcode} フィルターに使われるショートコード名
	* 戻り値		: 結合されフィルターされた属性のリスト
	*/
	$ad_nth_list = shortcode_atts( $pairs , $args );

	if ( 'none' !== $ad_nth_list['ad'] ) {
		// $value: Ad1～Adxx
		// 念のため、設定できる Adナンバーかチェック（Ad100とか指定された場合エラーになるので）
		// 設定範囲外の数値かチェック（範囲外であれば 1 をセット）
		if ( 'NG' === aaih__chk_multiple_variable_name( $ad_nth_list['ad'] , 'Ad' ) ) {
			$used_ad_nth = 'Ad1';
		}
		else {
			$used_ad_nth = $ad_nth_list['ad'];
		}

		// 設定コードの取得
		$settings 	= aaih__get_item();
		// カスタムフィールド（記事個別の設定）を反映（デバッグモードの on / off ）
		$settings	= aaih__meta_data_check( $settings );

		$ad_code 	= aaih__get_ad_html( $settings , $used_ad_nth , 'shortcode' );
	}
	else {
		$ad_code = '';
	}

	// ショートコードの広告表示回数 カウント
	$shortcode__ad_show_count ++;

	// デバッグ表示情報： タイトル , 挿入広告情報
	$title			= __( 'Shortcode', AAIH__TEXT_DOMAIN );
	$ad_unit_select	= $settings[ $used_ad_nth ]['ad_unit_select'];
	$ad_unit_name	= aaih__common_str( $ad_unit_select );

	// 何番目かの表示
	$label_ad		= __( 'Shortcode Ad Insert', AAIH__TEXT_DOMAIN );
	$st_nd_rd_th	= aaih__change_number_suffix( $shortcode__ad_show_count );

	$msg = $title . ' : ' . $used_ad_nth .' ( '. $ad_unit_name .' )<br />' . "\n";
	$msg .= '<span class="shortcode ad-nth-aaih">' . $label_ad . ' : ' . $st_nd_rd_th . '</span>';

	$debug_msg 	= aaih__debug_msg( $msg , $settings , 'shortcode' );

	return $ad_code . $debug_msg;
}


/**
 * 記事中からショートコードをゲット
 *
 * このプラグインのショートコード aaih が存在するかチェック
 *
 * @param string $the_content コンテンツ全体の文字列
 * @return array | string	$matches ショートコードがある場合その配列, 'nothing':ショートコードがない場合
 *
 * 補足：
 * matches[2] に ショートコード名 aaih が入る
 */
function aaih__get_shortcode( $the_content ) {

	// get_shortcode_regex()
	// 投稿に含まれるショートコードを検索するための正規表現を返す
	$pattern 		= get_shortcode_regex();

	$shortcode_name	= AAIH__SHORTCODE_NAME;
	if ( preg_match_all( '/'. $pattern .'/s', $the_content, $matches )
		&& array_key_exists( 2, $matches )
		&& in_array( $shortcode_name , $matches[2] ) ) {
		// shortcode 'aaih' is being used
		return $matches;
	}
	else {
		return 'nothing';
	}
}
/*
* matches[0]=> array(9) {
* 	[0]=> string(15) "[aaih ad=no_ad]"
* 	[1]=> string(13) "[aaih ad=Ad1]"
* 	[2]=> string(13) "[aaih ad=Ad2]"
*	}
* matches[2]=> array(9) {
* 	[0]=> string(4) "aaih"
* 	[1]=> string(4) "aaih"
* 	[2]=> string(4) "aaih"
* }
* matches[3]=> array(9) {
* 	[0]=> string(9) " ad=no_ad"
* 	[1]=> string(7) " ad=Ad1"
* 	[2]=> string(7) " ad=Ad2"
* }
*
* matches[2] に ショートコード名 aaih が入る
* ⇒ matches[2] があり、その中にプラグインのショートコード名が含まれるかをチェックする
* 含まれていれば、チェック結果を返し、含まれてなければ nothing を返す
*/

?>
