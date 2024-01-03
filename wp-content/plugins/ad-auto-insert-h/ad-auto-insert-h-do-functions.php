<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 無限ループチェック
 *
 * - 何かしらの理由からループ回数が指定する上限に達したら、'too_many' を返す。
 * - 上限はあり得ない数値（1000）を関数内で設定。
 * - $loop_num は 0 を最初に指定して参照渡し。
 *
 * @param int & $loop_num	繰り返しのカウント
 * @return string 上限に達してない場合：'still_ok'、上限以上の場合：'too_many'
 */
function aaih__infinite_loop_chk( & $loop_num ) {
	$loom_num_max	= 1000; // ありえない数値を設定
	$loop_num ++;

	if ( $loom_num_max < $loop_num ) {
		$msg	= __( 'Process was canceled halfway because of too many repeated checks.', AAIH__TEXT_DOMAIN );
					// 繰り返しチェックが多すぎたため、プロセスが途中でキャンセルされました。
		aaih__popup_alert( $msg );
		return 'too_many';
	}
	return 'still_ok';
}

/**
 * post id の取得
 *
 * - global $post は 安易に使わないようにしておく
 * - より安心感のある global $wp_query を使用
 *
 * @return int $post_id;
 */
function aaih__get_post_id() {
	global $wp_query;
	$post_id = $wp_query -> get_queried_object_id();

	return $post_id;
}

/**
 * 広告置き換え設定の確認
 *
 * 指定のショートコードの文字列が入力設定されている場合、
 * - そのショートコードが記事の中に１つでもあれば、最初のHタグ前Ad, Hタグ前Ad, 記事下Ad の設定 は 広告置き換え設定のオプションを反映する。
 * - 選択されている Adxx のショートコードで置き換える。
 * - 置き換え回数を $settings['total_replace_times'] にセットする
 *
 * @param string & $the_content	（参照渡し）表示コンテンツ
 * @param array & $settings		（参照渡し）設定全体
 * @return void
 *
 * 補足
 * total_replace_times：デバッグ表示用に取得。$total_replace_timesを使うのはデバッグ表示時のみ
 */
function aaih__replace_shortcode_with_ad( & $the_content , & $settings ) {
	// //置き換え対象のショートコードがあるかチェック
	$exist_check = aaih__replace_shortcode__exist( $the_content , $settings );

	// 置き換え処理を行う場合
	if( 'yes' === $exist_check ) {
		$total_replace_times 	= 0;

		// 実際に置き換え
		for( $i = 1 ; $i <= AAIH__SHORTCODE_REPLACE_MAX_NUM ; $i ++ ) {
			$shortcode_replace	= 'shortcode_replace' . $i;

			// 置き換え対象のショートコード, 置き換えるAdxxをゲット
			$replace_code	= $settings[ $shortcode_replace ]['replace_code']; 		//置き換え対象のショートコード
			$replace_ad_nth	= $settings[ $shortcode_replace ]['replace_ad_select'];	// 置き換えるAd num（Adxx）

			// 置き換える実際のショートコード
			$key 				= 'ad';
			$replace_to			= aaih__shortcode_create( $key , $replace_ad_nth );

			// 実際に置き換え（置き換え対象のショートコード入力がある場合）
			if ( '' !== $replace_code ) {
				//str_replace("検索を行う文字列","置き換えを行う文字列","対象の文字列","str_replace処理の回数");
				$the_content 			= str_replace( $replace_code , $replace_to , $the_content , $replace_times );
				$total_replace_times 	= $total_replace_times + $replace_times;
			}
		}
		// 広告の置き換えのオプション設定を適用
		$settings['first_h_tag_ad_onoff']		= $settings['ad_replace___first_h_tag_ad__off'] === 'on' ? 'off' : 'on';
		$settings['h_tag_ad_onoff']				= $settings['ad_replace___h_tag_ad__off'] === 'on' ? 'off' : 'on';
		$settings['after_content_ad_onoff']		= $settings['ad_replace___after_content_ad__off'] === 'on' ? 'off' : 'on';

	}else{
		// 置き換え処理を行わない場合
		$total_replace_times = 'no_execution';
	}

	$settings['total_replace_times'] = $total_replace_times;
}

/**
 * 広告置き換え対象のショートコードがあるかチェック
 *
 * @param string $the_content
 * @param array $settings 設定全体
 * @return string １つでもあれば 'yes', なければ 'no'
 */
function aaih__replace_shortcode__exist( $the_content , $settings ) {
	$onoff__ad_replace		= $settings['ad_replace_onoff'];

	// 設定が on かつ １つでも置き換え対象のショートコードの入力がある場合
	if ( 'on' === $onoff__ad_replace ) {
		for( $i = 1 ; $i <= AAIH__SHORTCODE_REPLACE_MAX_NUM ; $i ++ ) {
			// 置き換え対象のショートコードをゲット
			$shortcode_replace	= 'shortcode_replace' . $i;
			$replace_code	= $settings[ $shortcode_replace ]['replace_code']; 		//置き換え対象のショートコード

			if ( '' !== $replace_code ) {
				// 記事中に何回出現されるかチェック
				$how_many_times = substr_count( $the_content, $replace_code );
				// １つでもあれば抜ける
				if ( 1 <= $how_many_times ) {
					return 'yes';
				}
			}
		}
	}

	return	'no';
}


/**
 * 広告自動挿入のメインループ終了時の返すコンテンツチェック
 *
 * 広告自動挿入のメインループ終了時、「返すコンテンツ」に part1_allが付加されている場合
 * （ 'part1_all__added' === $content_return_chk の場合：広告が挿入された場合）
 * $part1_allは付加しないで返す
 *
 * @param string $content_return_chk	'part1_all__added', 'part1_all__add_skipped'
 * @param string $part1_all				見出し前のコンテンツ全体
 * @param string $part2_all				見出し含め見出し以降のコンテンツ全体
 * @return string	$part1_all . $part2_all
 */
function aaih__main_loop_end_chk( $content_return_chk , $part1_all , $part2_all ) {
	if ( 'part1_all__added' === $content_return_chk ) {
		$part1_all = '';
	}
	return $part1_all . $part2_all;
}

/**
 * 広告自動挿入の初期設定
 *
 * 広告自動挿入における設定値全体と表示コンテンツに対して、各種設定を反映する
 *
 * - カスタムフィールドの設定を反映
 * - ショートコードがあれば「H2タグ前Ad」はOFFへ
 * - 広告置き換えがあれば、コンテンツに反映し、「H2タグ前Ad」OFFへ
 *
 * デバッグ表示では、翻訳ファイルをロードする。
 *
 * @param array &$settings : $settings（設定値全体） の参照
 * @param string &$the_content : $the_content（表示コンテンツ） の参照
 * @return string	'selected_post_type' | 'not_selected_post_type'
 *
 * 返り値：post type チェック結果を返す
 */
function aaih__ad_insert__init( & $settings, & $the_content) {

	// 日本語翻訳表示対応
	if ( 'show' === aaih__debug_msg_show( $settings ) ) {
		aaih__add_translation();
	}

	/*
	 * post type チェック
	 *
	 * 広告挿入対象の post type でなければデバッグ表示用（summary表示）を付けて終わり
	 * デバッグ表示は、デバッグ表示の条件に合わない場合には空文字
	 */
	if ( 'selected_post_type' !== aaih__post_type_check( $settings['post_type'] ) ) {
		$debug_msg = aaih__debug_msg__summary( $settings , 'not_target' );
		$the_content = $debug_msg . $the_content;

		return 'not_selected_post_type';
	}

	// 設定の強さ： カスタムフィールド > 広告置き換え設定 > 一般設定
	// 広告置き換え設定の確認 :
	aaih__replace_shortcode_with_ad( $the_content , $settings );

	// カスタムフィールド（記事個別の設定）を反映
	$settings	= aaih__meta_data_check( $settings );

	return 'selected_post_type';
}


/**
 * 表示する広告数の上限チェック
 *
 * @param int $ad_show_count				広告が表示されるたびに１づつカウントアップ（広告を入れた 【後】 の数値）
 * @param int $ad_show_num					広告の上限数（設定値）
 * @param string $onoff__after_content_ad	記事下広告 on/off 設定
 * @return string	上限に達していない場合 'ok-insert-ad', 上限に達している場合 'ng-insert-ad'
 */
function aaih__ad_show_num_check( $ad_show_count , $ad_show_num , $onoff__after_content_ad ) {
	// 表示する広告数の上限 $ad_show_num : 無制限(0)の場合
	if ( 0 === $ad_show_num ) {
		return 'ok-insert-ad';
	}
	// 表示する広告数の上限 $ad_show_num : 無制限ではない場合（1以上）
	elseif ( 0 < $ad_show_num ) {
		// 記事下広告が on の場合 : 記事下は優先的に広告入れるとして、$ad_show_numから 1 を引く
		if ( 'on' === $onoff__after_content_ad ) {
			$ad_show_num = $ad_show_num -1;
		}
	}
	else {
		// その他はエラー表示
		aaih__popup_alert( 'aaih__ad_show_num_check : $ad_show_num ' . $ad_show_num );
	}

	// $ad_show_countが すでに上限に達していれば ng を返す（広告なし）
	if ( $ad_show_count >= $ad_show_num ) {
		return 'ng-insert-ad';
	}
	else {
		return 'ok-insert-ad';
	}
}


/**
 * 最初のH取得
 *
 * 指定されたコンテンツ内の最初のHタグを取得する
 *
 * @param string $content_after_h	見出し後のコンテンツ
 * @param array 	$settings		設定値全体
 * @return string	見つからなかった場合 'nothing' , 見つかった場合はその見出しの文字列 $h_tag_first
 */
function aaih__get_h_tag_first( $content_after_h , $settings ) {
	// 対象Hタグの設定取得
	$target_h_tag		= $settings['target_h_tag'];

	// 全てのHタグ（H2-H6）
	if ( 'H_tag_all' === $target_h_tag ) {
		$str_h_tag	= '/<h[1-6].+<\/h[1-6]>/i';
	}
	// H2のみ対象
	else {
		$str_h_tag	= '/<h2.+?<\/h2>/i';
	}
	// preg_match：マッチしたら1を返す
	$result		= preg_match( $str_h_tag , $content_after_h, $matches );

	//見出しなしの場合
	if ( 1 !== $result ) {
		return 'nothing';
	}
	else {
		//見出しありの場合:　最初のhを取り出し
		$h_tag_first = $matches[ 0 ];
		return $h_tag_first;
	}
}


/**
 * Hタグ前コンテンツ取得
 *
 * 指定されたコンテンツ内の最初のHタグを取得する
 *
 * @param string $content		コンテンツ
 * @param string $h_tag_first	最初のHタグ文字列
 * @return string $content_before_h	Hタグ前の文字列
 */
function aaih__get_content_before_h( $content, $h_tag_first ) {

	/* -----------------------------------
	 * strstr(string $haystack, string $needle, bool $before_needle = false): string|false
	 *
	 * $haystack の中で needle が最初に現れる場所を含めてそこから文字列の終わりまでを返す
	 * false	: $needleが見つからなかった場合 false を返す
	 * before_needle：true にすると、strstr() の戻り値は、haystack の中で最初に needle があらわれる箇所より前の部分となる (needle は含めない)
	 *
	 * https://www.php.net/manual/ja/function.strstr.php
	 * ----------------------------------- */

	// 最初のHタグより前のコンテンツを切り出し
	$content_before_h = strstr ( $content, $h_tag_first, true );

	/* -----------------------------------
	 * 事前に $h_tag_first があることは調べているので false が返ることはないけど念のためのチェック
	 *
	 * 導入部がなく、いきなり見出しで始まる記事の場合、$content_before_h は空文字になる
	 * 緩やかな比較（ == ）を行うと false と見分けが付かなくなるので必ず厳密な判定（ === ）を行う
	 * ----------------------------------- */
	if ( false === $content_before_h ) {
		aaih__popup_alert( 'error: aaih__get_content_before_h return false' );
	}

	return $content_before_h;
}


/**
 * H後コンテンツ取得
 *
 * 最初のHタグより後のコンテンツを切り出す
 *
 * @param string $content		コンテンツ
 * @param string $h_tag_first	最初のHタグ文字列
 * @return string $content_after_h	最初のHタグより後のコンテンツ文字列
 */
function aaih__get_content_after_h( $content , $h_tag_first ) {

	/* -----------------------------------
	 * $needle（$h_tag_first） が見つかった場合 : $h_tag_firstを含んでそれ以降の文字列を返す
	 * $needle（$h_tag_first） が見つからなかった場合 : falseを返す
	 * ----------------------------------- */
	$content_after_h = strstr ( $content , $h_tag_first );

	// 事前に $h_tag_first があることは調べているので false が返ることはないけど念のため
	if ( false === $content_after_h ) {
		aaih__popup_alert( 'error: aaih__get_content_after_h return false' );
	}

	/*
	 * 最初のHタグは削除
	 *
	 * 最初のHタグは削除では preg_replace で空文字に置き換える、としているが
	 * 最初のHタグ文字列 を検索する場合、まず preq_quote() で 正規表現構文の特殊文字の前にバックスラッシュを挿入してエスケープしておく
	 * こうしておかないと、Hタグ内で正規表現で使用する特殊文字が使用されていると warning となり期待する動作にならない
	 */
	// 最初のHタグ内の正規表現特殊文字の前にバックスラッシュを入れてエスケープ
	// 第二引数には デリミタとする文字を指定
	$h_tag_first 	= preg_quote( $h_tag_first , '/' );
	$pattern		= '/' . $h_tag_first . '/';

	//最初のh2を '’に置き換えすることで削除
	$replace = '';
	$content_after_h	= preg_replace( $pattern , $replace, $content_after_h , 1 );

	return $content_after_h;
}


/**
 * 文字数カウント（HTMLタグ除く）
 *
 * 半角を1文字、全角を2文字としてカウント
 * 全角換算の場合は2で割っとく
 *
 * @param string $string	文字数を調べる対象の文字列
 * @param array	$settings	設定値全体
 * @return int 	$num	文字列の文字数
 */
function aaih__get_num_of_chars( $string , $settings ) {
	$character_width_unit	= $settings['character_width_unit'];

	// 全ての NULL バイトと HTML および PHP タグを取り除く
	$string_with_no_html	= strip_tags( $string );
	//半角を1文字、全角を2文字としてカウント
	$num = mb_strwidth( $string_with_no_html );

	// 全角換算の場合は2で割っとく
	$num = ( 'full' === $character_width_unit ) ? $num / 2 : $num;

	return $num;
}


/**
 * 数値を 1st, 2nd, 3rd, 4th に変換
 *
 * 数値1, 2, 3, 4, 5 ... を 1st, 2nd, 3rd, 4th, 5th ... に変換する。
 *
 * @param int	$n	変換したい数字
 * @return string	$n_suffix	変換済みの文字列
 */
function aaih__change_number_suffix( $n ) {
	switch ( $n ) {
		case 1:
			$n_suffix = __( '1st', AAIH__TEXT_DOMAIN );
			break;
		case 2:
			$n_suffix = __( '2nd', AAIH__TEXT_DOMAIN );
			break;
		case 3:
			$n_suffix = __( '3rd', AAIH__TEXT_DOMAIN );
			break;
		default :
			$n_suffix = $n . __( 'th', AAIH__TEXT_DOMAIN ) ;
			break;
	}
	return $n_suffix;
}


/**
 * デバッグモード用：広告【前】文字数、広告【後】文字数のOK、NG 表示
 *
 * @param string	$ok_ng_before	'OK', 'NG'
 * @param string	$ok_ng_after	'OK', 'NG'
 * @param string	$supplement		表示したいコメント文字列（例：（終わりだよ））
 * @return string	表示文字列
 */
function aaih__show_before_after_ok_ng( $ok_ng_before , $ok_ng_after , $supplement ) {
	$str_before_ad 	= __( '[ Before AD ] Chars', AAIH__TEXT_DOMAIN ); // 【広告前】文字数
	$str_after_ad 	= __( '[ After AD ] Chars', AAIH__TEXT_DOMAIN ); // 【広告後】文字数

	return '* ' . $str_before_ad . ' : ' . $ok_ng_before . ' / ' . $str_after_ad . ' : ' . $ok_ng_after . ' ' . $supplement;
}

?>