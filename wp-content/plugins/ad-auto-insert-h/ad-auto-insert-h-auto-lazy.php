<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_head', 'aaih__adsense_auto_ads__insert' );
/**
 * アドセンスの自動広告 コード挿入
 *
 * HTMLヘッダにアドセンスコード（自動広告用のコード）を追加する。
 * - 設定がon かつ pub_id 入力がある場合に挿入する。
 * - 固定ページや投稿で除外がONの場合は何もしない。
 *
 * @return void	: 条件外では抜けるだけ
 *
 * 参照link	：https://developer.wordpress.org/reference/hooks/wp_head/
 */
function aaih__adsense_auto_ads__insert() {
	if ( 'stop' ===  aaih__chk_login_user() ) {
		return;	// 利用制限 ON 時、ログインの状態で、ユーザーが管理者以外の場合
	};

	$settings	= aaih__get_item();	// 設定値全体を取得

	$adsense_auto_ads__onoff 		= $settings['adsense_auto_ads_onoff'];		// 設定ON/OFFを取得
	$adsense_auto_ads__post_off 	= $settings['adsense_auto_ads__post_off']; 	// 除外する場合には on
	$adsense_auto_ads__page_off 	= $settings['adsense_auto_ads__page_off']; 	// 除外する場合には on

	// パブリッシャーIDがない場合
	if( '' === $settings['pub_id'] ) {
		return;
	}

	// 設定がONの場合
	if ( 'on' === $adsense_auto_ads__onoff ) {
		// 固定ページの場合で、除外が on の場合には何もしないで抜ける
		if ( is_page() && 'on' === $adsense_auto_ads__page_off ) {
			return;
		}
		// 投稿の場合で、除外が on の場合には何もしないで抜ける
		if ( is_single() && 'on' === $adsense_auto_ads__post_off ) {
			return;
		}

		// アドセンスコードの挿入
		aaih__adsense_code( 'insert' , $settings );
	}
}

add_action( 'wp_head', 'aaih__analytics_code_header_insert' );
/**
 * アナリティクスのコードのHTMLヘッダ挿入
 *
 * HTMLヘッダにグーグルアナリティクスのコードを追加する。
 * - 設定がon かつ 対象のIDのコード入力がある場合 に挿入する。
 *
 * @return void	: 条件外では抜けるだけ
 *
 * 参照link	：https://developer.wordpress.org/reference/hooks/wp_head/
 */
function aaih__analytics_code_header_insert() {
	$settings	= aaih__get_item();	// 設定値全体を取得

	$analytics_header_insert_code_onoff = $settings['analytics_header_insert_code_onoff'];	// 設定ON/OFFを取得
	$analytics_id = aaih__get_analytics_id ( $settings );	// アナリティクスのID取得

	// コード入力がない場合
	if( AAIH__ANALYTICS_ID_NOTHING_ALL_STR === $analytics_id ) {
		return;
	}
	// 設定がONの場合
	if ( 'on' === $analytics_header_insert_code_onoff ) {
		// コードをヘッダへ挿入
		aaih__analytics_code( $analytics_id , 'insert' );
	}
}


/**
 * LazyLoadの実行判定
 *
 * 以下の全ての条件が満たされる場合に LazyLoad の実行
 * - １) pub_id の入力ありの場合（つまりアドセンスコードの挿入OKの場合）
 * - ２) lazyload_onoff が on の場合
 * - ３) PC表示除外が on の場合は desktop 以外 の場合
 * - ４) 表示広告あり（yes）の場合
 *
 * @param array $settings 設定値全体
 * @return string 'yes' : 条件に当てはまる場合 / 'no' : 条件に当てはまらない場合
 */
function aaih__lazyload_execute_check( $settings ) {
	// 1) pub_id の入力ありの場合（つまりアドセンスコードの挿入OKの場合）
	if ( '' === $settings['pub_id'] ) {
		return 'no';
	}
	// 2) lazyload_onoff が on の場合
	$lazyload_onoff		= $settings['adsense_lazy_load_onoff'];
	if ( 'on' !== $lazyload_onoff ) {
		return 'no';
	}

	// 3) PC表示除外が on の場合は desktop 以外 の場合
	$no_pc_onoff = $settings['adsense_lazy_load_no_pc_onoff'];

	/* PC除外が on かつ desktop の場合（mobile や tablet でない場合） no を返す */
	if ( 'on' === $no_pc_onoff && ! wp_is_mobile() ) {
		return 'no';
	}

	// 4) 表示広告あり（yes）の場合
	$has_ad = aaih__has_ad( $settings ); // 表示する広告があるか情報取得
	if ( 'no' === $has_ad ) {
		return 'no';
	}

	// すべての条件が OK
	return 'yes';
}


add_action( 'wp_footer', 'aaih__adsense_lazy_load_js_insert' );
/**
 * LazyLoad用コードの挿入
 *
 * - アドセンスの遅延表示（lazy load）のために</body>直前にjsコードを挿入。
 * - LazyLoadの実行判定で 'yes' の場合に実行する。
 *
 * @return void	：echo で コード挿入
 *
 * 参照link	: https://developer.wordpress.org/reference/hooks/wp_footer/
 */
function aaih__adsense_lazy_load_js_insert() {

	$settings				= aaih__get_item();	// 設定値全体を取得
	$lazyload_execute_check = aaih__lazyload_execute_check( $settings );

	if ( 'no' === $lazyload_execute_check ) {
		return;
	}

	/* -----------------------------------
	 * jsコード生成に必要な情報を取得
	 * ----------------------------------- */
	$pub_id 					= $settings['pub_id']; // パブリッシャーID は aaih__lazyload_execute_check でチェック済
	$adsense_lazy_load_second	= $settings['adsense_lazy_load_second'];	// グーグルアドセンスの遅延読み込みを自動で読み込むまでの秒数指定
	$adsense_lazy_load_ms		= $adsense_lazy_load_second * 1000;	// ミリ秒に変更

	// 改行やタブの削除
	$delete_breaks_tabs = array( "\r\n", "\r", "\n", "\t" );

	echo '<!-- aaih lazy load script -->
<script>
jQuery(document).ready(function( $ ) {
	// 書き込むスクリプトをセット
	const addScript = \'<!-- aaih lazyload ad script -->';

	// アドセンスコードをセット
	echo str_replace( $delete_breaks_tabs , '' , AAIH__ADSENSE_CODE__BEFORE ) . 'pub-' . esc_attr( $pub_id );
	echo str_replace( $delete_breaks_tabs , '' , AAIH__ADSENSE_CODE__AFTER ) . '\\';
	echo str_replace( $delete_breaks_tabs , '' , AAIH__ADSENSE_CODE__END ) . '\'';

	echo '

	//遅延読込み判別
	let lazyloadCheck 	= "not_yet";

	$( window ) . on( "load" , function() {';

	// 自動読込みの設定がされている場合
	if( 0 < $adsense_lazy_load_ms ){
		echo '
		// 何もイベント発生がない場合：指定秒後に読み込込む（ミリ秒で指定）
		if ( "not_yet" === lazyloadCheck ) {
			lazyloadTimeout = setTimeout( onLazyLoad , '; echo absint( $adsense_lazy_load_ms ) . ' );';
		echo '
		}';
	}

	echo '
		// ページ途中の場合（縦方向のスクロールがある場合）
		if ( $( window ) . scrollTop() ) {
			onLazyLoad();
		}
	}); // function

	// 読み込みタイミング（イベント）
	$( window ) . scroll( onLazyLoad );			// スクロール時
	$( window ) . mousemove( onLazyLoad );		// マウスを動かした時
	$( window ) . mousedown( onLazyLoad );		// マウスキーが押された時
	$( window ) . keydown( onLazyLoad );		// キーボードが何か押された時
	$( window ) . touchstart ( onLazyLoad );	// 画面タッチされた時

	// LazyLoadの処理
	// まだ一度も遅延読み込みしてなければ スクリプトを実際に書き込み
	function onLazyLoad() {
		if ( "not_yet" === lazyloadCheck ) {
			// イベントチェック
			//eventtype = "aaih イベントタイプ: " + event.type;

			// 1度でも lazyload した場合には already に設定
			//（2回以上動作しないようにしておく）
			lazyloadCheck = "already";

			// 余計な動作がないよう、指定したイベントに対する動作もoffしておく
			$( window ) . off( "scroll"		, onLazyLoad );	// スクロール時off
			$( window ) . off( "mousemove"	, onLazyLoad );	// マウスを動かした時
			$( window ) . off( "mousedown"	, onLazyLoad );	// マウスキーが押された時
			$( window ) . off( "keydown"	, onLazyLoad );	// キーボードが何か押された時
			$( window ) . off( "touchstart"	, onLazyLoad );	// 画面タッチされた時

			// bodyタグの最後にスクリプトを追加
			$("body").append( addScript );
			console.log("aaih: bodyタグの最後にスクリプトを追加");';

	if( 0 < $adsense_lazy_load_ms ){
		echo '
			// タイマーもクリア
			clearTimeout( lazyloadTimeout );
			console.log("aaih: clearTimeout");';
	}

	echo '
		} // if
	}// function

});
</script>';
}