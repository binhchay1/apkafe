<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * サニタイズ（無害化）
 *
 * - 渡された配列の $key, $value に対してサニタイズを行う。
 * - バージョンアップ時のみ エラーポップアップは出さない
 * （ 配列の整合性は サニタイズの後で行うため、この時点ではエラーポップは出さない ）
 *
 * @param array	$args			設定の配列
 * @param array	$state			バージョンアップ時 version_up の文字列が入る。その他は空文字
 * @return array サニタイズした値を $value にセットして返す
 *
 */
function aaih__item_sanitize( $args , $state = '') {
	foreach ( $args as $key => $value ) {
		switch ( $key ) {
			case 'tab_menu_num':	// メインのタブ番号（ 取りえる値：0, 1, 2, 3 ）
			case 'ad_code_tab_num':
			case 'h_tag_ad_onoff':
			case 'first_h_tag_ad_onoff':
			case 'after_content_ad_onoff':
			case 'updown_margin_separate_onoff':
			case 'label_input_onoff':
			case 'memo_input_onoff':
			case 'ad_replace_onoff':
			case 'ad_replace___h_tag_ad__off':
			case 'ad_replace___first_h_tag_ad__off':
			case 'ad_replace___after_content_ad__off':
			case 'adsense_auto_ads_onoff':
			case 'analytics_header_insert_code_onoff':
			case 'adsense_lazy_load_onoff':
			case 'adsense_auto_ads__post_off':
			case 'adsense_auto_ads__page_off':
			case 'adsense_lazy_load_no_pc_onoff':
			case 'post_type':
			case 'target_h_tag': 				// 広告挿入対象のHタグ
			case 'ad_select__first_h_tag': 		// Adナンバー文字列 Adxx
			case 'ad_select__h_tag': 			// Adナンバー文字列 Adxx
			case 'ad_select__after_content': 	// Adナンバー文字列 Adxx
			case 'space_unit':
			case 'character_width_unit':
			case 'language':
			case 'ad_show_num':
			case 'ad_space':
			case 'pub_id':						// アドセンス : パブリッシャーID
			case 'pub_id__auto_ad':				// アドセンス : パブリッシャーID 自動広告
			case 'analytics_id':				// グーグルアナリティクス　Tracking ID : 000000 of UA-000000-2 , Measurement ID : G-XXXXXXX / Tracking ID : UA-000000-2
			case 'adsense_lazy_load_second':	// LazyLoadするまでの秒数（スクロールや画面タッチなど操作がなにもされない場合）
			case 'debug_mode_onoff':
			case 'access_control_onoff':		// 利用制限：管理者のみ有効にするかどうか
			case 'debug_mode_summary_disable':
			case 'meta__ad_off':				// メタ設定
			case 'meta__ad_show_num':			// メタ設定
			case 'meta__target_h_tag':			// メタ設定
			case 'meta__ad_space_change':		// メタ設定
			case 'meta__ad_space':				// メタ設定
			case 'meta__debug_mode_onoff':			// メタ設定
				$value = sanitize_text_field( $value );
				$args[ $key ] 	= $value;
				break;

			// Ad, shortcode_replace
			default:
				$prefix_name = aaih__get_only_prefix( $key );
				// Ad, shortcode_replace の中身をチェック
				$args2	= $value;

				switch ( $prefix_name ) {
					case 'shortcode_replace':
						foreach ( $args2 as $key2 => $value2 ) {
							switch ( $key2 ) {
								case 'replace_code' :
								case 'replace_ad_select':
									$value2 	= sanitize_text_field( $value2 );
									$args[ $key ][ $key2 ] = $value2;
									break;

								default:
									$alert_msg	= 'aaih__item_sanitize: no case2: '.$key2;
									aaih__popup_alert( $alert_msg , $state );
							}
						}
						break;

					case 'Ad':
						foreach ( $args2 as $key2 => $value2 ) {
							switch ( $key2 ) {
								case 'ad_unit_select':			// グーグルアドセンス 広告ユニットの選択
								case 'ad_unit_id__in_article':	// グーグルアドセンス 広告ユニット ID ( xxxxx of data ad slot = xxxxx）
								case 'ad_unit_id__display':		// グーグルアドセンス 広告ユニット ID ( xxxxx of data ad slot = xxxxx）
								case 'ad_unit_id__multiplex':	// グーグルアドセンス 広告ユニット ID ( xxxxx of data ad slot = xxxxx）
								case 'ad__data_ad_format':		// グーグルアドセンス ディスプレイ広告のオプション auto（自動調整） / rectangle（長方形） / vertical（縦長） / horizontal（横長）
								case 'label':
								case 'name': // memo
								case 'centering':
								case 'label_space_em':
								case 'label_space_px':
								case 'updown_margin_em':
								case 'updown_margin_down_em':
								case 'updown_margin_px':
								case 'updown_margin_down_px':
									$value2	= sanitize_text_field( $value2 );
									$args[ $key ][ $key2 ] = $value2;
									break;

								default:
									$alert_msg	= 'aaih__item_sanitize: no case3: '.$key2;
									aaih__popup_alert( $alert_msg , $state );
							}
						}
						break;

					default:
						$alert_msg	= 'aaih__item_sanitize: no case1: '.$key;
						aaih__popup_alert( $alert_msg , $state );
				} // default switch
		} // switch
	} // foreach
	return $args;
}

/**
 * データ検証
 *
 * 渡された配列の $key, $value に対してデータ検証を行う
 *
 * @param array	$args			設定の配列
 * @return array 検証された値を $value にセットして返す
 */
function aaih__item_validation( $args ) {

	foreach ( $args as $key => $value ) {

		switch ( $key ) {
			case 'tab_menu_num':	// メインのタブ番号（ 取りえる値：0, 1, 2, 3 ）
				// タブの数を何か定数で指定しているわけではないので実際の数（4）で最大を比較
				if ( 0 > $value || 3 < $value ) {
					$value = AAIH__SETTING_DEFAULT__BASIC[ $key ];
				}
				$args[ $key ] = (int) $value;
				break;

			case 'ad_code_tab_num':
				$max	= AAIH__AD_CODE_HOW_MANY -1;	// AAIH__AD_CODE_HOW_MANY : 広告コードが設定できる数
				if ( 0 > $value || $max < $value ) {
					$value = AAIH__SETTING_DEFAULT__BASIC[ $key ];
				}
				$args[ $key ] = (int) $value;
				break;

			case 'debug_mode_onoff':
			case 'debug_mode_summary_disable':
			case 'h_tag_ad_onoff':
			case 'first_h_tag_ad_onoff':
			case 'after_content_ad_onoff':
			case 'updown_margin_separate_onoff':
			case 'label_input_onoff':
			case 'memo_input_onoff':
			case 'ad_replace_onoff':
			case 'ad_replace___h_tag_ad__off':
			case 'ad_replace___first_h_tag_ad__off':
			case 'ad_replace___after_content_ad__off':
			case 'adsense_auto_ads_onoff':
			case 'analytics_header_insert_code_onoff':
			case 'adsense_lazy_load_onoff':
			case 'adsense_auto_ads__post_off':
			case 'adsense_auto_ads__page_off':
			case 'adsense_lazy_load_no_pc_onoff':
			case 'access_control_onoff':
				if ( 'on' !== $value && 'off' !== $value ) {
					$value = AAIH__SETTING_DEFAULT__BASIC[ $key ];
				}
				$args[ $key ] = $value;
				break;

			case 'post_type':
				if ( 'post' !== $value && 'page' != $value && 'both' !== $value ) {
					$value = AAIH__SETTING_DEFAULT__BASIC[ $key ];
				}
				$args[ $key ] = $value;
				break;

			case 'target_h_tag': //　広告挿入対象のHタグ
				if ( 'H_tag_all' !== $value && 'H2_only' !== $value ) {
					$value = AAIH__SETTING_DEFAULT__BASIC[ $key ];
				}
				$args[ $key ] = $value;
				break;

			case 'meta__ad_off':
				if ( 'on' !== $value && 'off' !== $value ) {
					$value = AAIH__META_DEFAULT[ $key ];
				}
				$args[ $key ] = $value;
				break;

			case 'meta__target_h_tag':	//　広告挿入対象のHタグ（メタ設定）
				if ( 'same' !== $value && 'H_tag_all' !== $value && 'H2_only' !== $value ) {
					$value = AAIH__META_DEFAULT[ $key ];
				}
				$args[ $key ] = $value;
				break;

			case 'meta__ad_space_change':
				if ( 'same' !== $value && 'change' !== $value ) {
					$value = AAIH__META_DEFAULT[ $key ];
				}
				$args[ $key ] = $value;
				break;

			case 'meta__debug_mode_onoff':
				if ( 'same' !== $value && 'on' !== $value && 'off' !== $value ) {
					$value = AAIH__META_DEFAULT[ $key ];
				}
				$args[ $key ] = $value;
				break;

			case 'ad_select__first_h_tag': 		//Adナンバー文字列 Adxx
			case 'ad_select__h_tag': 			//Adナンバー文字列 Adxx
			case 'ad_select__after_content': 	//Adナンバー文字列 Adxx
				// 設定範囲外の数値かチェック（範囲外であれば 初期値 をセット）
				if ( 'NG' === aaih__chk_multiple_variable_name( $value , 'Ad' ) ) {
					$value = AAIH__SETTING_DEFAULT__BASIC[ $key ];
				}
				$args[ $key ] = $value;
				break;

			case 'space_unit':
				if ( 'em' !== $value && 'px' !== $value ) {
					$value = AAIH__SETTING_DEFAULT__BASIC[ $key ];
				}
				$args[ $key ] = $value;
				break;

			case 'character_width_unit':
				if ( 'full' !== $value && 'half' !== $value ) {
					$value = AAIH__SETTING_DEFAULT__BASIC[ $key ];
				}
				$args[ $key ] = $value;
				break;

			case 'language':
				if ( '' === $value ) {
					$value = AAIH__SETTING_DEFAULT__BASIC[ $key ];
				}
				$args[ $key ] = $value;
				break;

			case 'ad_show_num':
				$init	= AAIH__SETTING_DEFAULT__BASIC[ $key ];
				$min	= AAIH__AD_SHOW_NUM_MIN; // 0 : 制限しない
				$max	= AAIH__AD_SHOW_NUM_MAX;

				$value 			= (int) aaih__check_numeric_min_max( $value, $init , $min , $max );
				$args[ $key ] 	= $value;
				break;

			case 'meta__ad_show_num':
				$init 	= AAIH__META_DEFAULT[ $key ];
				$min	= AAIH__META__AD_SHOW_NUM_MIN; // -1 ; 設定値に同じ
				$max	= AAIH__AD_SHOW_NUM_MAX;

				$value 			= (int) aaih__check_numeric_min_max( $value, $init , $min , $max );
				$args[ $key ] 	= $value;
				break;

			case 'ad_space':
			case 'meta__ad_space':
				// 全角数字は半角へ（ブラウザ側で type="number" が機能しない場合の誤入力の念のため）
				$value = mb_convert_kana( $value, "n");

				$init	= AAIH__AD_SPACE_INIT;
				$min	= AAIH__AD_SPACE_MIN;
				$max	= AAIH__AD_SPACE_MAX;

				$value 			= (int) aaih__check_numeric_min_max( $value, $init , $min , $max );
				$args[ $key ] 	= $value;
				break;

			case 'pub_id__auto_ad':		// アドセンス : パブリッシャーID
				$value			= $args[ 'pub_id' ] ;	// pub_id 優先で値を常に同じにする
				$args[ $key ] 	= $value;	// 以下 空文字で break するため、一旦値を入れておく
				// break せずに以下に落ちる
			case 'pub_id':				// アドセンス : パブリッシャーID
				if( '' === $value ){
					break;
				}
				// 前後の空白など削除 , 全角は半角 , 数字以外は削除
				$value	= aaih__id_input_validation( $value , $key );

				// 数値の場合は文字数をチェック（最大値とか最小値ではない）
				$max_length	= AAIH__PUB_ID_MAXLENGTH;
				$value		= aaih__length_chk( $value , $max_length );
				$args[ $key ] 	= $value;
				break;

			case'analytics_id':
				if( '' === $value ){
					break;
				}
				// 前後の空白など削除 , 全角は半角 , 英数ハイフン以外は削除
				$value		= aaih__id_input_validation( $value , $key );

				$max_length	= AAIH__ANALYTICS_ID_MAXLENGTH;
				$value		= aaih__length_chk( $value , $max_length );
				$args[ $key ] 	= $value;
				break;

			case 'adsense_lazy_load_second':	// LazyLoadするまでの秒数（スクロールや画面タッチなど操作がなにもされない場合）
				$init	= AAIH__AD_LAZY_LOAD_AUTO_INIT;
				$min	= AAIH__AD_LAZY_LOAD_AUTO_MIN;
				$max	= AAIH__AD_LAZY_LOAD_AUTO_MAX;

				$args[ $key ] = (int) aaih__check_numeric_min_max( $value, $init , $min , $max );
				break;

			// Ad, shortcode_replace
			default:
				$prefix_name = aaih__get_only_prefix( $key );
				// Ad, shortcode_replace の中身をチェック
				$args2	= $value;

				switch ( $prefix_name ) {
					case 'shortcode_replace':
						foreach ( $args2 as $key2 => $value2 ) {
							switch ( $key2 ) {
								case 'replace_code' :
									// 前後の余計な空白（とか変なコード）削除
									// https://qiita.com/fallout/items/a13cebb07015d421fde3
									$value2 = preg_replace( '/\A[\p{Cc}\p{Cf}\p{Z}]++|[\p{Cc}\p{Cf}\p{Z}]++\z/u' , '' , $value2 );

									// 文字数チェック
									$max_length = AAIH__SHORTCODE_REPLACE_MAXLENGTH;
									$value2 	= aaih__length_chk( $value2 , $max_length );

									$args[ $key ][ $key2 ] = $value2;
									break;

								case 'replace_ad_select':
									// 設定範囲外の数値かチェック（範囲外であれば 初期値（Ad1） をセット）
									if ( 'NG' === aaih__chk_multiple_variable_name( $value2 , 'Ad' ) ) {
										$value2 = AAIH__SETTINGS_DEFAULT__SHORTCODE_REPLACE[ $key2 ];
									}

									$args[ $key ][ $key2 ] = $value2;
									break;

								default:
									$alert_msg	= 'aaih__item_validation: no case2: '.$key2;
									aaih__popup_alert( $alert_msg );
							}
						}
						break;

					case 'Ad':
						foreach ( $args2 as $key2 => $value2 ) {
							switch ( $key2 ) {
								case 'ad_unit_select':	// 広告ユニットの選択
									if( 'display' !== $value2 && 'in_article' !== $value2 && 'multiplex' !== $value2 ){
										$args[ $key ][ $key2 ] = AAIH__SETTINGS_DEFAULT__AD[ $prefix_name ][ $key2 ];
									}
									break;

								case 'ad_unit_id__in_article':	// 広告ユニット ID ( xxxxx of data ad slot=xxxxx )
								case 'ad_unit_id__display':		// 広告ユニット ID ( xxxxx of data ad slot=xxxxx )
								case 'ad_unit_id__multiplex':	// 広告ユニット ID ( xxxxx of data ad slot=xxxxx )
									if( '' === $value2 ){
										break;
									}

									// 前後の空白など削除 , 全角は半角 , 数字以外は削除
									$value2	= aaih__id_input_validation( $value2 , $key2 );

									// 文字数をチェック
									$max_length	= AAIH__DATA_AD_SLOT_MAXLENGTH;
									$value2		= aaih__length_chk( $value2 , $max_length );

									$args[ $key ][ $key2 ] = $value2;
									break;

								case 'ad__data_ad_format':	// グーグルアドセンス ディスプレイ広告のオプション auto（自動調整） / rectangle（長方形） / vertical（縦長） / horizontal（横長）
									if ( 'auto' !== $value2 && 'rectangle' !== $value2 && 'vertical' !== $value2 && 'horizontal' !== $value2 ) {
										$args[ $key ][ $key2 ] = AAIH__SETTINGS_DEFAULT__AD[ $prefix_name ][ $key2 ];
									}
									break;

								case 'label':
									// 文字数チェック
									$max_length = AAIH__AD_CODE_LABEL_MAXLENGTH;
									$value2 	= aaih__length_chk( $value2 , $max_length );

									$args[ $key ][ $key2 ] = $value2;
									break;

								case 'name': // memo
									// 文字数チェック
									$max_length = AAIH__AD_CODE_NAME_MAXLENGTH;
									$value2 	= aaih__length_chk( $value2 , $max_length );

									$args[ $key ][ $key2 ] = $value2;
									break;

								case 'centering':
									if ( 'on' !== $value2 && 'off' !== $value2 ) {
										$value2 = AAIH__SETTINGS_DEFAULT__AD[ $prefix_name ][ $key2 ];
									}
									$args[ $key ][ $key2 ] = $value2;
									break;

								case 'label_space_em':
									// 前後の余計な空白は削除、全角数字は半角へ（ブラウザ側で type="number" が機能しない場合の誤入力の念のため）
									$value2 = aaih__spaces_tabs_of_head_and_end__delete( $value2 );
									$value2 = mb_convert_kana( $value2, "n");

									$init	= AAIH__SETTINGS_DEFAULT__AD[ $prefix_name ][ $key2 ];
									$min	= AAIH__AD_CODE_LABEL__SPACE_EM_MIN;
									$max	= AAIH__AD_CODE_LABEL__SPACE_EM_MAX;

									$args[ $key ][ $key2 ] = (float) aaih__check_numeric_min_max( $value2, $init , $min , $max );
									break;

								case 'label_space_px':
									// 前後の余計な空白は削除、全角数字は半角へ（ブラウザ側で type="number" が機能しない場合の誤入力の念のため）
									$value2 = aaih__spaces_tabs_of_head_and_end__delete( $value2 );
									$value2 = mb_convert_kana( $value2, "n");

									$init	= AAIH__SETTINGS_DEFAULT__AD[ $prefix_name ][ $key2 ];
									$min	= AAIH__AD_CODE_LABEL__SPACE_PX_MIN;
									$max	= AAIH__AD_CODE_LABEL__SPACE_PX_MAX;

									$args[ $key ][ $key2 ] = (int) aaih__check_numeric_min_max( $value2, $init , $min , $max );
									break;

								case 'updown_margin_em':
								case 'updown_margin_down_em':
									// 1.25 など float 型
									// 全角英数字は半角へ（ブラウザ側で type="number" が機能しない場合の誤入力の念のため）
									// 前後の余計な空白は削除、全角数字は半角へ（ブラウザ側で type="number" が機能しない場合の誤入力の念のため）
									$value2 = aaih__spaces_tabs_of_head_and_end__delete( $value2 );
									$value2 = mb_convert_kana( $value2, "a");	// em では、小数点が全角の場合も考慮して オプションは a 指定

									switch ( $key2 ) {
										case 'updown_margin_em':
											$init	= AAIH__SETTINGS_DEFAULT__AD[ $prefix_name ][ $key2 ];
											break;
										case 'updown_margin_down_em':
											$init	= AAIH__SETTINGS_DEFAULT__AD[ $prefix_name ][ $key2 ];
											break;
										default:
											// 特に何もしない
									}
									$min	= AAIH__AD_CODE_UPDOWN__MARGIN_EM_MIN;
									$max	= AAIH__AD_CODE_UPDOWN__MARGIN_EM_MAX;

									$args[ $key ][ $key2 ] = (float) aaih__check_numeric_min_max( $value2, $init , $min , $max );
									break;

								case 'updown_margin_px':
								case 'updown_margin_down_px':
									// 前後の余計な空白は削除、全角数字は半角へ（ブラウザ側で type="number" が機能しない場合の誤入力の念のため）
									$value2 = aaih__spaces_tabs_of_head_and_end__delete( $value2 );
									$value2 = mb_convert_kana( $value2, "n");

									switch ( $key2 ) {
										case 'updown_margin_px':
											$init	= AAIH__SETTINGS_DEFAULT__AD[ $prefix_name ][ $key2 ];
											break;
										case 'updown_margin_down_px':
											$init	= AAIH__SETTINGS_DEFAULT__AD[ $prefix_name ][ $key2 ];
											break;
										default:
											// 特に何もしない
									}
									$min	= AAIH__AD_CODE_UPDOWN__MARGIN_PX_MIN;
									$max	= AAIH__AD_CODE_UPDOWN__MARGIN_PX_MAX;

									$args[ $key ][ $key2 ] = (int) aaih__check_numeric_min_max( $value2, $init , $min , $max );
									break;

								default:
									$alert_msg	= 'aaih__item_validation: no case3: ' . $key2;
									aaih__popup_alert( $alert_msg );
							}
						}
						break;

					default:
						$alert_msg	= 'aaih__item_validation: no case1: '.$key;
						aaih__popup_alert( $alert_msg );
				} // default switch
		} // switch
	} // foreach
	return $args;
}

/**
 * ID入力の検証
 *
 * パブリッシャーID, 広告ユニット ID, アナリティクス ID に対して以下入力値の制限する
 *
 * 共通
 * - 空白		: 削除
 * - 全角		: 半角へ変換
 *
 * パブリッシャーID, 広告ユニット ID
 * - 数字以外	: 削除
 *
 * アナリティクスID
 * - 英数数字 と ハイフン 以外	: 削除
 * - 小文字は大文字へ
 *
 * ※）この ID入力の検証 は js 側のデータ検証とあわせる必要あり
 *
 * 対象
 * - パブリッシャーID : pub_id
 * - 広告ユニット ID : ad_unit_id__in_article , ad_unit_id__display , ad_unit_id__multiplex
 * - アナリティクス ID : analytics_id
 *
 * @param string $id	idの値
 * @param string $type	パブリッシャーID / 広告ユニット ID / アナリティクス ID
 * @return string $code 検証隅のコード
 */
function aaih__id_input_validation( $id , $type ) {
	// 共通

	if( '' === $id ){
		return $id;
	}

	// 空白（とか変なコード）削除
	$id = preg_replace( '/[\p{Cc}\p{Cf}\p{Z}]++|[\p{Cc}\p{Cf}\p{Z}]++/u' , '' , $id );
	// 全角：半角へ変換
	$id = mb_convert_kana( $id, "a");

	// Analytics ID は、英数数字 と ハイフン 以外削除
	if ( 'analytics_id' === $type ) {
		$pattern	= '/[^a-zA-Z0-9\-]/u';	// u : Unicode
		$id = preg_replace( $pattern , '' , $id );
		// 小文字は大文字へ
		$id = strtoupper( $id ) ;
	}
	else{	// パブリッシャーID , 広告ユニット ID
		// 数字以外は削除
		$pattern	= '/[^0-9]/u';	// u : Unicode
		$id = preg_replace( $pattern , '' , $id );
		// $id = absint( $id );	// 負ではない整数に変換（念のため)してたけど、先頭が 0 の場合 0 が削除されるのでコメントアウト
	}

	return $id;
}

/**
 * 前後の空白やタブを削除
 *
 * - 文字列前後の、余計な空白（半角、全角含む）やコントロール文字など を削除する
 *
 * @param string $code
 * @return string $code 前後の余計な空白などが削除された文字列
 */
function aaih__spaces_tabs_of_head_and_end__delete( $code ) {
	// 前後の余計な空白（とか変なコード）削除
	// https://qiita.com/fallout/items/a13cebb07015d421fde3
	$code = preg_replace( '/\A[\p{Cc}\p{Cf}\p{Z}]++|[\p{Cc}\p{Cf}\p{Z}]++\z/u' , '' , $code );
	return $code;
}
?>