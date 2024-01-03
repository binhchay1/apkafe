<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_filter( 'the_content' , 'aaih__ad_insert' , 10 );
/**
 * 広告挿入メイン
 *
 * - 記事に広告を挿入する
 * - part1_all（見出しより前） と part2_all（見出し以下） に分け、それぞれが設定した広告の間隔（$ad_space）以上であれば広告を挿入する
 *
 * @param string $the_content
 * @return string 広告挿入後の $the_content（デバッグ情報含む）
 */
function aaih__ad_insert( $the_content ) {
	if( ! is_single() && ! is_page() ) {	// exclude editor
		return $the_content;
	}
	if ( 'stop' ===  aaih__chk_login_user() ) {
		return $the_content;	// 利用制限 ON 時、ログインの状態で、ユーザーが管理者以外の場合
	};

	// 全ての設定値の取得
	$settings 	= aaih__get_item();

	/* -----------------------------------
	 * 広告挿入をそもそも行うかのチェック
	 *
	 * １）対象の投稿タイプでない場合は終わり
	 * ２）自動挿入の3つの設定が全て off なら終わり
	 * ----------------------------------- */

	// 環境準備 : 広告自動挿入における設定値全体と表示コンテンツに対して、各種設定を反映する。（ $settings, $the_content は参照渡し）
	// 対象の投稿タイプではない場合 / 編集モードの場合 には 単に $the_content を返して終わり
	if ( 'selected_post_type' !== aaih__ad_insert__init( $settings, $the_content) ) {
		return $the_content;
	}

	// デバッグ表示用（ 記事先頭に表示するsummary表示 ）
	// デバッグ表示の条件を満たさない場合には空文字
	$debug_msg = aaih__debug_msg__summary( $settings );

	/*
	 * 広告自動挿入に必要な3つの設定の取得と確認
	 *
	 * 最初のHタグ前Ad、Hタグ前Ad、記事下Adの3つの設定を取得
	 * この3つの設定全てがOFFの場合、デバッグ情報を付けて終わり
	 */
	$onoff__first_h_tag_ad		= $settings['first_h_tag_ad_onoff'];				// 最初のHタグ前に必ずAd追加の設定 on/off
	$onoff__h_tag_ad			= $settings['h_tag_ad_onoff'];						// Hタグ前にAd追加の設定 on/off
	$onoff__after_content_ad	= $settings['after_content_ad_onoff'];				// 記事下にAd追加の設定 on/off

	// 自動挿入設定が全てOFFの場合には、デバッグ表示を付けて終わり
	if ( 'off' === $onoff__first_h_tag_ad && 'off' === $onoff__h_tag_ad && 'off' === $onoff__after_content_ad ) {
		return $debug_msg . $the_content;
	}

	/* -----------------------------------
	 * 広告挿入に必要な設定を取得
	 * ----------------------------------- */
	$ad_show_num				= $settings['ad_show_num'];							// 広告数の上限 : 0：unlimited
	$ad_space					= $settings['ad_space'];							// 広告の間隔（文字数）: part1_all, part2_allともこの文字数以上であれば広告挿入

	// 見出しより前、見出し以下 の情報 格納用
	$part1	= array(
		'htag_first'	=> '',				// part1 の先頭の見出しタグ
		'content'		=> $the_content,	// part1 の見出し内コンテンツ
		'all'			=> $the_content,	// part1全体 : htag_first + content
	);

	$part2	= array(
		'htag_first'	=> '',				// part2 の先頭の見出しタグ
		'content'		=> '',				// part2 の見出し内コンテンツ
		'all'			=> '',				// part2全体 : htag_first + content
	);

	// 広告表示用 HTML の取得
	$ad_html__each	= array(
		'ad__first_h_tag'	=> aaih__get_ad_html( $settings , '' , 'first' ),		// 広告HTML : 最初のHタグ前用
		'ad__h_tag'			=> aaih__get_ad_html( $settings , '' , 'in-article' ),	// 広告HTML : Hタグ前用
		'ad__after_content'	=> aaih__get_ad_html( $settings , '' , 'bottom' ),		// 広告HTML : 記事下用
	);

	// 最終的に返すコンテンツ : $the_content に広告（とデバック情報）を挿入したもの
	$content_return	='';


	/* -----------------------------------
	 * 開始準備
	 * ----------------------------------- */
	// 最終的に返すコンテンツ : 最初はデバッグ情報のみをセット
	$content_return	= $debug_msg;

	/*
	 * content_return に part1_all が既に追加されているかのチェック用変数を用意
	 *
	 * 後OK,前NGでは part1_all が 追加されず 次のHタグを探し処理が続行されるため、
	 * 次のHタグが無い場合、終了処理に行く。
	 * その場合には content_return に part1_allの付け忘れが出るため、そのチェック用。
	 *
	 * part1_all__added / （初期） part1_all__add_skipped
	 */
	$content_return_chk	= 'part1_all__add_skipped';

	// 記事内に表示する広告数をカウント : 広告を入れた【 後 】の数値
	$ad_show_count	= 0;	// ショートコードによる広告表示はカウント対象外


	/* -----------------------------------
	 * 広告挿入　開始！
	 * ----------------------------------- */
	$loop_num	= 0;	// 無限ループ回避用

	do {
		// 無限ループチェック（念のための処理）
		if ( 'too_many' === aaih__infinite_loop_chk( $loop_num ) ) {
			return $the_content;	// 無限ループでは元のコンテンツをそのまま返す
		}

		/* -----------------------------------
		 * ループ継続チェック
		 * ----------------------------------- */
		 // 「先頭のHタグ」後のコンテンツ（ $part1['content'] ）内に Hタグがあるかチェック
		$h_tag_first 		= aaih__get_h_tag_first( $part1['content'] , $settings );
		// 表示する広告数がすでに上限に達しているかチェック
		$ad_show_num_check 	= aaih__ad_show_num_check( $ad_show_count , $ad_show_num , $onoff__after_content_ad );

		// 「先頭のHタグ」後にHタグがない、または　表示する広告数が上限に達している場合は終わり
		if ( 'nothing' === $h_tag_first || 'ng-insert-ad' === $ad_show_num_check) {
			// すでに part1_all が付加されているかチェック
			$content_return = $content_return . aaih__main_loop_end_chk( $content_return_chk , $part1['all'] , $part2['all'] );
			break;
		}


		/* -----------------------------------
		 * part1_content内を最初のHタグ、そのHタグ前後に分解し、新にpart1, part2を取得
		 * ----------------------------------- */
		// ここで得られる part1_all, part2_allの文字数により、広告挿入を判別

		$check_content	= $part1['content'];

		//$part1'htag_first']：ループ内で取得済
		$part1['content']		= aaih__get_content_before_h( $check_content , $h_tag_first );
		$part1['all']			= $part1['htag_first'] . $part1['content'];

		$part2['htag_first']	= $h_tag_first;
		$part2['content']		= aaih__get_content_after_h( $check_content , $h_tag_first );
		$part2['all']			= $part2['htag_first'] . $part2['content'];


		/* -----------------------------------
		 * 「最初のHタグ前に必ずAd追加」の設定がONの場合
		 * ----------------------------------- */
		// この場合は文字数比較せず、必ず広告を入れる
		if ( 'on' === $onoff__first_h_tag_ad ) {

			// 一度でも通過したら off に変更しておく（繰り返しはしないように1度だけに制限）
			$onoff__first_h_tag_ad = 'off';

			// ★広告を入れる：広告表示カウントをプラス
			$ad_show_count ++;
			// 入れる広告コード
			$ad_html = $ad_html__each['ad__first_h_tag'];

			//デバッグ表示用（summary表示）
			$debug_msg = aaih__debug_msg__first_h_tag_ad( $settings , $ad_show_count );

			//【返すコンテンツ】＝【返すコンテンツ】＋【part1_all】＋【広告HTML】
			$content_return		= $content_return . $part1['all'] . $ad_html . $debug_msg;
			$content_return_chk = 'part1_all__added';

			// 次のチェック用にデータセット
			$part1['htag_first']	= $part2['htag_first'];
			$part1['content']		= $part2['content'];

			// 残りの処理をスキップ。次のループを実行。
			continue;
		}

		/* -----------------------------------
		 * 「Hタグ前にAd追加」の設定がONの場合
		 * ----------------------------------- */
		if ( 'on' === $onoff__h_tag_ad ) {
			//Hタグ前後の文字数
			$num_of_part1_all	= aaih__get_num_of_chars( $part1['all'] , $settings );
			$num_of_part2_all	= aaih__get_num_of_chars( $part2['all'] , $settings );


			/* ----------------------------------------------------------------------
			 * 広告入れ判別 : 4つの場合分け
			 *
			 * その１）　広告【前】文字数OK、広告【後】文字数OK : ★広告を入れる
			 * その２）　広告【前】文字数NG、広告【後】文字数OK ; 広告入れない
			 * その３）　広告【前】文字数OK、広告【後】文字数NG : 記事下Ad off: ★広告を入れる / 記事下Ad on : 広告入れない
			 * その４）　広告【前】文字数NG、広告【後】文字数NG : 広告入れない
			 * ---------------------------------------------------------------------- */

			/* -----------------------------------
			 * その１）【前】OK、【後】OK
			 * ----------------------------------- */
			if ( $num_of_part1_all >= $ad_space && $num_of_part2_all >= $ad_space ) {

				//★広告を入れる
				$ad_show_count ++;
				$ad_html = $ad_html__each['ad__h_tag'];

				//デバッグ表示用
				$debug_msg = aaih__debug_msg__before_h_tag_ad( 'ok_ok' , $settings , $num_of_part1_all, $num_of_part2_all , $ad_show_count );

				//【返すコンテンツ】＝【返すコンテンツ】＋【part1_all】＋【広告HTML】
				$content_return		= $content_return . $part1['all'] . $ad_html . $debug_msg;
				$content_return_chk = 'part1_all__added';

				// 次のチェック用にデータセット
				$part1['htag_first'] 	= $part2['htag_first'];
				$part1['content']		= $part2['content'];

				continue;
			}
			/* -----------------------------------
			 * その２）　【前】NG、【後】OK
			 * ----------------------------------- */
			elseif ( $num_of_part1_all < $ad_space && $num_of_part2_all >= $ad_space ) {

				//★広告入れない
				// デバッグ表示なし（含めたいけど、デバッグ表示用の文字列も文字数にカウントされることになるため）

				//【返すコンテンツ】には何もしない
				$content_return_chk = 'part1_all__add_skipped';

				// 次のチェック用にデータセット
				$part1['htag_first'] 	= $part1['htag_first'] . $part1['content'] . $part2['htag_first'];
				$part1['content']		= $part2['content'];

				continue;
			}
			/* -----------------------------------
			 * その３）　【前】OK、【後】NG
			 * ----------------------------------- */
			// 記事下広告 on/off により場合分け
			elseif ( $num_of_part1_all >= $ad_space && $num_of_part2_all < $ad_space ) {

				if ( 'on' === $onoff__after_content_ad ) {
					// ★記事下広告ON：だから広告入れない
					$ad_html = '';

					//デバッグ表示用
					$debug_msg = aaih__debug_msg__before_h_tag_ad( 'ok_ng__no_ad' , $settings , $num_of_part1_all, $num_of_part2_all , $ad_show_count );
				}
				else {
					// ★記事下広告OFF：だから広告入れる
					$ad_show_count ++;
					$ad_html = $ad_html__each['ad__h_tag'];

					//デバッグ表示用
					$debug_msg = aaih__debug_msg__before_h_tag_ad( 'ok_ng__add_ad' , $settings , $num_of_part1_all, $num_of_part2_all , $ad_show_count );
				}

				//【返すコンテンツ】＝ 【返すコンテンツ】 ＋ 【part1_all】＋【広告HTML】 ＋ 【part2_all】
				$content_return	= $content_return . $part1['all'] . $ad_html . $debug_msg . $part2['all'];
				break;
			}
			/* -----------------------------------
			 * その４）　【前】NG、【後】NG
			 * ----------------------------------- */
			elseif ( $num_of_part1_all < $ad_space && $num_of_part2_all < $ad_space ) {
				//★広告入れない
				$ad_html = '';

				//デバッグ表示用
				$debug_msg = aaih__debug_msg__before_h_tag_ad( 'ng_ng' , $settings , $num_of_part1_all, $num_of_part2_all , $ad_show_count );

				//【返すコンテンツ】＝ 【返すコンテンツ】 ＋ 【part1_all】＋【広告HTML】 ＋ 【part2_all】
				$content_return	= $content_return . $part1['all'] . $ad_html . $debug_msg . $part2['all'];
				break;
			}
			// エラー：　その他のケース（ありえないけど念のため）
			else {
				$msg = __( 'Error: Ad insert before H tag', AAIH__TEXT_DOMAIN ); // Hタグ前にAd追加判別でエラー
				$msg = aaih__debug_msg( $msg , $settings);

				// ありえないケースが起きたら、デバッグメッセージを先頭につけて元のコンテンツをそのまま返す
				return $msg . $the_content;
			}

		}
		else {
			/* -----------------------------------
			 * 「Hタグ前にAd追加」の設定がOFFの場合
			 * ----------------------------------- */
			// whileループを抜ける
			// part1_all を新たに part1_all, part2_allに分解した直後になるため
			// content_return_chk を part1_all__add_skipped にセット

			$content_return_chk	= 'part1_all__add_skipped';
			$content_return 	= $content_return . aaih__main_loop_end_chk( $content_return_chk , $part1['all'] , $part2['all'] );
			break;
		}

	} while ( true ) ;


	/* -----------------------------------
	 * 記事下にAd追加設定がONの場合
	 * ----------------------------------- */

	if ( 'on' === $onoff__after_content_ad ) {
		//★広告を入れる
		$ad_show_count ++;
		$ad_html	= $ad_html__each['ad__after_content'];
		//デバッグ表示用
		$debug_msg = aaih__debug_msg__after_content_ad__on( $settings , $ad_show_count );
	}
	else {
		//★広告を入ない
		$ad_html = '';
		//デバッグ表示用
		$debug_msg = aaih__debug_msg__after_content_ad__off( $settings );
	}

	//【返すコンテンツ】＝【返すコンテンツ】＋【広告HTML】
	$content_return = $content_return . $ad_html . $debug_msg;
	return $content_return;
}
?>