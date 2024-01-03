<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * デバッグメッセージ表示をするかのチェック
 *
 * 設定がon かつプレビュー時のみ表示する。
 *
 * @param array $settings	設定全体
 * @return string	: 'show' 表示する場合、'off' 表示しない場合
 */
function aaih__debug_msg_show( $settings ) {
	if ( 'on' === $settings['debug_mode_onoff'] && is_preview() ) {
		return 'show';
	}
	else {
		return 'off';
	}
}


/**
 * デバッグメッセージhtml（ラッパー）
 *
 * @param string $msg	表示文言
 * @return string	$msg を <pre> .. </pre>で囲んで返す
 * @param string $type ショートコードの場合のみ 'shortcode' が入る
 */
function aaih__debug_msg_return( $msg , $type = '' ) {
	if ( 'shortcode' === $type ) {
		$class = 'debug-aaih shortcode';
	}
	else{
		$class = 'debug-aaih';
	}
	return '<pre class="'. $class .'">'. $msg . '</pre>';
}


/**
 * デバッグメッセージ表示 ： 一般に使う表示
 *
 * @param string $msg			表示文言
 * @param string $settings	設定全体
 * @param string $type ショートコードの場合のみ 'shortcode' が入る
 * @return string	: 表示設定が 'show' の場合 表示文字列、 そうでない場合　''（空文字）
 */
function aaih__debug_msg( $msg , $settings , $type = '' ) {
	if ( 'show' !== aaih__debug_msg_show( $settings ) ) {
		return '';
	}
	else {
		return aaih__debug_msg_return( $msg , $type );
	}
}


/**
 * デバッグメッセージ：最初のHタグ前Ad
 *
 * @param string 	$settings	設定全体
 * @param int		$ad_show_count	何番目の広告表示か
 * @return string	表示設定が 'show' の場合 表示文字列、 そうでない場合　''（空文字）
 */
function aaih__debug_msg__first_h_tag_ad( $settings , $ad_show_count ) {
	if ( 'show' !== aaih__debug_msg_show( $settings ) ) {
		return '';
	}

	$title			= __( 'Ad before the first H2 tag', AAIH__TEXT_DOMAIN );
	// 挿入広告情報
	$used_ad_nth	= $settings['ad_select__first_h_tag'];
	$ad_unit_select	= $settings[ $used_ad_nth ]['ad_unit_select'];
	$ad_unit_name	= aaih__common_str( $ad_unit_select );
	// 何番目かの表示
	$label_ad		= __( '* Auto Ad Insert', AAIH__TEXT_DOMAIN );
	$st_nd_rd_th	= aaih__change_number_suffix( $ad_show_count );


	// 改行は「"\n"」というようにダブルクォートで括る必要あり。
	//（シングルクォートにするとそのまま文字列として表示されてしまうので注意）

	$debug_msg = $title . ' : ' . $used_ad_nth .' ( '. $ad_unit_name .' )<br />' . "\n";
	$debug_msg .= '<span class="ad-nth-aaih">' . $label_ad . ' : ' . $st_nd_rd_th . '</span>';

	return aaih__debug_msg_return( $debug_msg );
}


/**
 * デバッグメッセージ：記事下Ad（設定が on の場合）
 *
 * @param string 	$settings	設定全体
 * @param int		$ad_show_count	何番目の広告表示か
 * @return string	表示設定が 'show' の場合 表示文字列、 そうでない場合　''（空文字）
 */
function aaih__debug_msg__after_content_ad__on( $settings , $ad_show_count ) {
	if ( 'show' !== aaih__debug_msg_show( $settings ) ) {
		return '';
	}
	if ( 'on' === $settings['debug_mode_summary_disable']) {

	}

	$title			= __( 'Ad end of article', AAIH__TEXT_DOMAIN );
	// 挿入広告情報
	$used_ad_nth	= $settings['ad_select__after_content'];
	$ad_unit_select	= $settings[ $used_ad_nth ]['ad_unit_select'];
	$ad_unit_name	= aaih__common_str( $ad_unit_select );
	// 何番目かの表示
	$label_ad		= __( '* Auto Ad Insert', AAIH__TEXT_DOMAIN );
	$st_nd_rd_th	= aaih__change_number_suffix( $ad_show_count );

	// 改行は「"\n"」というようにダブルクォートで括る必要あり。
	//（シングルクォートにするとそのまま文字列として表示されてしまうので注意）

	$debug_msg = $title . ' : ' . $used_ad_nth .' ( '. $ad_unit_name .' )<br />' . "\n";
	$debug_msg .= '<span class="ad-nth-aaih">' . $label_ad . ' : ' . $st_nd_rd_th . '</span>';

	return aaih__debug_msg_return( $debug_msg );
}


/**
 * デバッグメッセージ：記事下Ad（設定が off の場合）
 *
 * @param string 	$settings	設定全体
 * @return string	表示設定が 'show' の場合 表示文字列、 そうでない場合　''（空文字）
 */
function aaih__debug_msg__after_content_ad__off( $settings ) {
	if ( 'show' !== aaih__debug_msg_show( $settings ) ) {
		return '';
	}

	$debug_msg	= '* ' . __( '[ Ad end of article ] OFF', AAIH__TEXT_DOMAIN );

	return aaih__debug_msg_return( $debug_msg );
}


/**
 * デバッグメッセージ：Hタグ前Ad
 *
 * @param string	$ok_ng				広告が見出し前に入れられるかの判定文字列
 * @param string 	$settings		設定全体
 * @param string 	$num_of_part1_all	見出し前の文字数
 * @param string 	$num_of_part2_all	見出し後の文字数
 * @param int		$ad_show_count		何番目の広告表示か
 * @return string 	表示文字列
 *
 * $ok_ng
 * - 'ok_ok'			見出し前：ok、見出し後：ok、両方設定文字数以上			：●広告入れる
 * - 'ok_ng__no_ad'		見出し前：ok、見出し後：ng、記事下広告ON				：×広告は入れない
 * - 'ok_ng__add_ad'	見出し前：ok、見出し後：ng、記事下広告OFF				：●広告入れる
 * - 'ng_ok'			見出し前：ng、見出し後：ok	見出し前が設定文字数未満	：×告は入れない
 * - 'ng_ng'			見出し前：ng、見出し後：ng、両方設定文字数未満			：●広告は入れない
 */
function aaih__debug_msg__before_h_tag_ad( $ok_ng , $settings , $num_of_part1_all, $num_of_part2_all , $ad_show_count ) {
	if ( 'show' !== aaih__debug_msg_show( $settings ) ) {
		return '';
	}
	$title			= __( 'Ad before H2 tag', AAIH__TEXT_DOMAIN );
	// 使用されているAdxx
	$used_ad_nth	= $settings['ad_select__h_tag'];
	$ad_unit_select	= $settings[ $used_ad_nth ]['ad_unit_select'];
	$ad_unit_name	= aaih__common_str( $ad_unit_select );

	//広告の間隔（文字数）
	//part1_all, part2_allともこの文字数以上であれば広告挿入
	$ad_space			= $settings['ad_space'];

	$before_ad_chars	= __( '[ Before AD ] Chars', 			AAIH__TEXT_DOMAIN );	// 【広告前】文字数
	$after_ad_chars		= __( '[ After AD ] Chars' , 			AAIH__TEXT_DOMAIN );	// 【広告後】文字数
	$sum_chars			= __( '[ Sum ]' , 						AAIH__TEXT_DOMAIN );	// 【合計】
	$space_setting		= __( '[ Space between ads setting ]' , AAIH__TEXT_DOMAIN );	// 【広告間隔の文字数設定】
	$number_of_chars	= __( 'characters' , 					AAIH__TEXT_DOMAIN ); 	// 文字

	$str_end_supplement = __( '(finished)', AAIH__TEXT_DOMAIN ); // （終わりだよ）

	// 何番目かの表示
	$label_ad		= __( '* Auto Ad Insert', AAIH__TEXT_DOMAIN );
	$st_nd_rd_th	= aaih__change_number_suffix( $ad_show_count );

	// デバッグメッセージ
	$debug_msg = $title . ' : ' . $used_ad_nth .' ( '. $ad_unit_name .' )<br />' . "\n";
	$debug_msg .=
		'* ' .
		$before_ad_chars 	. ' : ' . $num_of_part1_all . ' / ' .
		 $after_ad_chars 	. ' : ' . $num_of_part2_all . ' / ' .
		 $sum_chars 			. ' : ' . ( $num_of_part1_all + $num_of_part2_all ) . ' ' . $number_of_chars . '<br />' .
		 '* ' . $space_setting . ' ' . $ad_space . ' ' . $number_of_chars .'<br />';

	switch ( $ok_ng ) {
		case 'ok_ok':	// 広告入れる
			$debug_msg	.= aaih__show_before_after_ok_ng( 'OK' , 'OK' , '' ) . '<br />' . "\n";
			$debug_msg	.= '<span class="ad-nth-aaih">' . $label_ad . ' : ' . $st_nd_rd_th . '</span>';
			break;

		case 'ok_ng__no_ad':
			// ★記事下広告ON：だから広告入れない
			$debug_msg	.= aaih__show_before_after_ok_ng( 'OK' , 'NG' , $str_end_supplement ) . '<br />' . "\n";
			$debug_msg	.= __( 'No Ad inserted because [ Ad end of article ] ON.', AAIH__TEXT_DOMAIN );
			break;

		case 'ok_ng__add_ad':
			// ★記事下広告OFF：だから広告入れる
			$debug_msg	.= aaih__show_before_after_ok_ng( 'OK' , 'NG' , $str_end_supplement ) . '<br />' .
			'* ' . __( 'Ad inserted because [ Ad end of article ] OFF.', AAIH__TEXT_DOMAIN ) . '<br />' . "\n";
			$debug_msg	.= '<span class="ad-nth-aaih">' . $label_ad . ' : ' . $st_nd_rd_th . '</span>';
			break;

		case 'ng_ok':
			// デバッグ表示なし
			break;

		case 'ng_ng':
			$debug_msg	.= aaih__show_before_after_ok_ng( 'NG' , 'NG' , $str_end_supplement );
			break;

		default:
			aaih__popup_alert( 'aaih__debug_msg__before_h_tag_ad: no case' );
	}
	return aaih__debug_msg_return( $debug_msg );
}


/**
 * デバッグメッセージ表示：先頭のサマリー表示
 *
 * プレビュー時、先頭に表示する情報表示
 * 関連する設定値をすべてリストで表示する
 *
 * @param string 	$settings	設定全体
 * @param string 	$no_target		対象の記事（投稿タイプ）でない場合のみ、'not_target' がセットされ簡略表示を行う
 * @return string 	表示文字列
 */
function aaih__debug_msg__summary( $settings , $no_target = '' ) {
	// 設定がon かつプレビュー時のみ表示する
	if ( 'show' !== aaih__debug_msg_show( $settings ) ) {
		return '';
	}
	// 先頭のサマリー表示が無効になっていれば空文字を返す
	if ( 'on' === $settings['debug_mode_summary_disable'] ) {
		return '';
	}

	// 条件によって非表示にする場合のスタイル定義
	$style__display_none	= 'style="display:none";';

	// ------------------------------------
	// ショートコードの置き換え
	// ------------------------------------
	// ショートコード置き換え回数
	if ( '' === $no_target ) {	// 対象の記事の場合
		$ad_replace_times 	= $settings['total_replace_times'];
	}
	else { // 対象の記事ではない場合
		$ad_replace_times = 'no_execution';
	}

	// 置き換えを実行してなければ（値が 'no_execution' の場合）非表示
	$ad_replace_times_style 	= ( 'no_execution' === $ad_replace_times ? $style__display_none : '' ) ;

	// ------------------------------------
	// 対象の記事（投稿タイプ）
	// ------------------------------------
	$target_post_type 			= $settings['post_type'];

	switch ( $target_post_type ) {
		case 'post':
			$target_post_type = __( 'Post', AAIH__TEXT_DOMAIN );
			break;
		case 'page':
			$target_post_type = __( 'Page', AAIH__TEXT_DOMAIN );
			break;
		case 'both':
			$target_post_type = __( 'Post & Page', AAIH__TEXT_DOMAIN );
			break;
		default:
			aaih__popup_alert( 'no case: aaih__debug_msg__summary' );
	}

	// ------------------------------------
	// 対象Hタグ
	// ------------------------------------
	$target_h_tag 				= $settings['target_h_tag'];
	$target_h_tag 				= 'H_tag_all' === $target_h_tag ? __( 'All H tags (H2-H6)', AAIH__TEXT_DOMAIN ) : __( 'H2 only', AAIH__TEXT_DOMAIN ) ;

	// ------------------------------------
	//Ad付加の設定 on/off
	// ------------------------------------
	$onoff__first_h_tag_ad		= $settings['first_h_tag_ad_onoff'];	// 最初のHタグ前に必ずAd追加の設定 on/off
	$onoff__h_tag_ad			= $settings['h_tag_ad_onoff'];			// Hタグ前にAd追加の設定 on/off
	$onoff__after_content_ad	= $settings['after_content_ad_onoff'];	// 記事下にAd追加の設定 on/off

	// ------------------------------------
	//広告の間隔（文字数）
	// ------------------------------------
	$ad_space					= $settings['ad_space'];

	// ------------------------------------
	// 文字数カウントの単位
	// ------------------------------------
	$character_width_unit		= $settings['character_width_unit'];
	$full_half					= 'full' === $character_width_unit ? __( 'full-width', AAIH__TEXT_DOMAIN ) : __( 'half-width' , AAIH__TEXT_DOMAIN ); 

	// ------------------------------------
	//記事内の最大広告数
	// ------------------------------------
	$ad_show_num				= $settings['ad_show_num'];
	$limit						= ( 0 === $ad_show_num ? __( 'Unlimited', AAIH__TEXT_DOMAIN ) : $ad_show_num );

	// ------------------------------------
	// アドセンス自動広告
	// ------------------------------------
	$adsense_auto_ads_onoff		= $settings['adsense_auto_ads_onoff'];

	if ( 'on' === $adsense_auto_ads_onoff ) {
		// 投稿で表示するか
		$adsense_auto_ads__post_off	= $settings['adsense_auto_ads__post_off'];
		// 固定ページで表示するか
		$adsense_auto_ads__page_off	= $settings['adsense_auto_ads__page_off'];

		// 非表示になっていればメッセージ表示
		if ( 'on' === $adsense_auto_ads__post_off || 'on' === $adsense_auto_ads__page_off) {
			$adsense_auto_ads__post_off	= 'on' === $adsense_auto_ads__post_off ? ' ' . __( 'Posts are ineligible', AAIH__TEXT_DOMAIN ) . ' ' : '';
			$adsense_auto_ads__page_off	= 'on' === $adsense_auto_ads__page_off ? ' ' . __( 'Pages are ineligible', AAIH__TEXT_DOMAIN ) . ' ' : '';

			$adsense_auto_ads_target	= '(' . $adsense_auto_ads__post_off . $adsense_auto_ads__page_off . ')';
		}
		else {
			// 投稿、固定ページとも on でなければ何も表示しない
			$adsense_auto_ads_target = '';
		}
	}
	else {
		$adsense_auto_ads_target = '';
	}

	// ------------------------------------
	// 遅延表示（LazyLoad）
	// ------------------------------------
	/*
	* 表示パターンは以下５つ
	*	1) off	(設定off）
	*	2) on	(自動読込み xx秒, PC除外)
	*	3) on	(自動読込み xx秒)
	*	4) on	(自動読込みなし、PC除外)
	*	5) on	(自動読込みなし、PC除外なし)
	*/
	$adsense_lazy_load_onoff		= $settings['adsense_lazy_load_onoff'];

	if ( 'off' === $adsense_lazy_load_onoff ) {
		$lazy_load_details = '';	// 1) off	(設定off）
	}else {
		// 自動読み込む秒数指定: 0 は「使用しない」
		$adsense_lazy_load_second	= $settings['adsense_lazy_load_second'];
		// PC除外
		$no_pc_onoff 				= $settings['adsense_lazy_load_no_pc_onoff'];

		// 表示文字列
		$str_auto_load		= __( 'Auto load', AAIH__TEXT_DOMAIN );	// 自動読込み
		$str_second_seconds	= _n( 'second' , 'seconds' , $adsense_lazy_load_second , AAIH__TEXT_DOMAIN );
		$str_no_pc			= __( 'Exclude desktop', AAIH__TEXT_DOMAIN );	// PC表示は対象外

		// 自動読込み あり
		if ( 0 !== $adsense_lazy_load_second ) {
			// PC除外 on	: 2) on	(自動読込み xx秒, PC除外)
			if ( 'on' === $no_pc_onoff ) {
				$lazy_load_details = '( ' . $str_auto_load . ' ' . $adsense_lazy_load_second . ' ' . $str_second_seconds . ' / ' . $str_no_pc . ' )';
			}else {
			// PC除外 off	: 3) on	(自動読込み xx秒)
				$lazy_load_details = '( ' . $str_auto_load . ' ' . $adsense_lazy_load_second . ' ' . $str_second_seconds . ' )';
			}
		}else { 	// 自動読込み なし
			// PC除外 on	: 4) on	(PC除外)
			if ( 'on' === $no_pc_onoff ) {
				$lazy_load_details = '( ' . $str_no_pc . ' )';
			}else {
			// PC除外 off	// 5) on	(自動読込みなし、PC除外なし)
				$lazy_load_details = '';
			}
		}
	}

	// ------------------------------------
	// デバック情報表示：タイトルと各ラベル
	// ------------------------------------
	$title							= __( 'Settings info', 	AAIH__TEXT_DOMAIN ) ; // 設定値情報

	$label_Ad_replacement			= __( 'Ad replacement', 				AAIH__TEXT_DOMAIN ); // ショートコードの置き換え
	$label_target_post_type			= __( 'Target Post type', 				AAIH__TEXT_DOMAIN ); // 対象の記事
	$label_target_h_tag				= __( 'Target H tag', 					AAIH__TEXT_DOMAIN ); // 対象Hタグ
	$label_first_h_tag_ad			= __( 'Ad before the first H2 tag', 	AAIH__TEXT_DOMAIN ); // 最初のHタグ前に必ずAd追加の設定 on/off
	$label_onoff__h_tag_ad			= __( 'Ad before H2 tag', 				AAIH__TEXT_DOMAIN ); // Hタグ前にAd追加の設定 on/off
	$label_onoff__after_content_ad	= __( 'Ad end of article', 				AAIH__TEXT_DOMAIN ); // 記事下にAd追加の設定 on/off
	$label_ad_space					= __( 'Space between ads', 				AAIH__TEXT_DOMAIN ); // 広告の間隔（文字数）
	$moji							= __( 'characters', 					AAIH__TEXT_DOMAIN ); // 文字数の「～文字」表示
	$label__unit_counting_chars		= __( 'Unit (counting chars)', 			AAIH__TEXT_DOMAIN ); // 文字数カウントの単位
	$label_max_number_of_ads 		= __( 'Max number of ads', 				AAIH__TEXT_DOMAIN ); // 記事内の最大広告数
	$label_adsense_auto_ads			= __( 'AdSense Auto Ads', 				AAIH__TEXT_DOMAIN ); // アドセンス自動広告
	$label_lazy_load				= __( 'Lazy Load', 						AAIH__TEXT_DOMAIN ); // 遅延表示（Lazy Load）

	if ( '' === $no_target ) {	// 対象の記事の場合

	// 公式ディレクトリ登録ではヒアドキュメントの使用は禁止されている
	// 改行について）
	// 改行は「"\n"」というようにダブルクォートで括る必要あり。
	//（シングルクォートにするとそのまま文字列として表示されてしまうので注意）

		$debug_msg = '<div>' . $title . '</div>' . "\n";
		$debug_msg .= '<table id="debug-aaih"><tbody>' . "\n";
		$debug_msg .= '	<tr ' . $ad_replace_times_style . '>' . "\n";
		$debug_msg .= '		<td>' . $label_Ad_replacement . '</td>' . "\n";
		$debug_msg .= '		<td>' . $ad_replace_times . '</td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '	<tr>' . "\n";
		$debug_msg .= '		<td>' . $label_target_post_type . '</td>' . "\n";
		$debug_msg .= '		<td>' . $target_post_type . '</td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '	<tr>' . "\n";
		$debug_msg .= '		<td>' . $label_target_h_tag . '</td>' . "\n";
		$debug_msg .= '		<td>' . $target_h_tag . '</td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '	<tr>' . "\n";
		$debug_msg .= '		<td>' . $label_first_h_tag_ad . '</td>' . "\n";
		$debug_msg .= '		<td>' . $onoff__first_h_tag_ad . '</td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '	<tr>' . "\n";
		$debug_msg .= '		<td>' . $label_onoff__h_tag_ad . '</td>' . "\n";
		$debug_msg .= '		<td>' . $onoff__h_tag_ad . '</td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '	<tr>' . "\n";
		$debug_msg .= '		<td>' . $label_onoff__after_content_ad . '</td>' . "\n";
		$debug_msg .= '		<td>' . $onoff__after_content_ad . '</td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '	<tr>' . "\n";
		$debug_msg .= '		<td>' . $label_ad_space . '</td>' . "\n";
		$debug_msg .= '		<td>' . $ad_space . ' ' . $moji . '</td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '	<tr>' . "\n";
		$debug_msg .= '		<td>' . $label__unit_counting_chars . '</td>' . "\n";
		$debug_msg .= '		<td>' . $full_half . '</td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '	<tr>' . "\n";
		$debug_msg .= '		<td>' . $label_max_number_of_ads . '</td>' . "\n";
		$debug_msg .= '		<td>' . $limit . '</td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '	<tr class="separator">' . "\n";
		$debug_msg .= '		<td colspan="2"><hr></td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '	<tr>' . "\n";
		$debug_msg .= '		<td>' . $label_adsense_auto_ads . '</td>' . "\n";
		$debug_msg .= '		<td>' . $adsense_auto_ads_onoff . ' ' . $adsense_auto_ads_target . '</td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '	<tr>' . "\n";
		$debug_msg .= '		<td>' . $label_lazy_load . '</td>' . "\n";
		$debug_msg .= '		<td>' . $adsense_lazy_load_onoff . ' ' . $lazy_load_details . '</td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '</tbody></table>';

	}
	else { // 対象の記事ではない場合
		$msg	= __( 'Auto ad insert is ineligible', AAIH__TEXT_DOMAIN );	// 広告の自動挿入は対象外

		$debug_msg = '<div>' . $title . '</div>' . "\n";
		$debug_msg .= '<table id="debug-aaih" class="debug-aaih"><tbody>' . "\n";
		$debug_msg .= '	<tr>' . "\n";
		$debug_msg .= '		<td colspan="2">' . $msg . '</td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '	<tr>' . "\n";
		$debug_msg .= '		<td>' . $label_target_post_type . '</td>' . "\n";
		$debug_msg .= '		<td>' . $target_post_type . '</td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '	<tr class="separator">' . "\n";
		$debug_msg .= '		<td colspan="2"><hr></td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '	<tr>' . "\n";
		$debug_msg .= '		<td>' . $label_adsense_auto_ads . '</td>' . "\n";
		$debug_msg .= '		<td>' . $adsense_auto_ads_onoff . ' ' . $adsense_auto_ads_target . '</td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '	<tr>' . "\n";
		$debug_msg .= '		<td>' . $label_lazy_load . '</td>' . "\n";
		$debug_msg .= '		<td>' . $adsense_lazy_load_onoff . ' ' . $lazy_load_details . '</td>' . "\n";
		$debug_msg .= '	</tr>' . "\n";
		$debug_msg .= '</tbody></table>';

	}

	return aaih__debug_msg_return( $debug_msg );
}
?>