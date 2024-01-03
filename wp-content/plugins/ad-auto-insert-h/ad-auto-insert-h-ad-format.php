<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * アドセンスコードの取得 / 表示
 *
 * @param string $action
 * - 'get'		; コードをそのまま取得（記事内挿入時 / LazyLoad で挿入コードとして使う場合）
 * - 'show'		; コードの表示（設定内で表示時 : 全体をエスケープして echo で表示）
 * - 'insert'	: コードの挿入（ヘッダに挿入時：変数のみエスケープして echo で表示）
 * @param array $settings	設定値全体
 *
 * @return string
 * - 'get'		; コードの文字列を返す（ pub_id がない場合は空文字を返す ）
 * - 'show'		; 成功したら 'ok' を返す
 * - 'insert'	: 成功したら 'ok' を返す（ pub_id がない場合は空文字を返す ）
 */
function aaih__adsense_code ( $action , $settings ) {
	// パブリッシャーID xxxxx of pub-xxxxx
	$pub_id = $settings['pub_id'];

	switch ( $action ) {
		case 'get':	// 記事内挿入時 / LazyLoad で挿入コードとして使う場合
			if ( '' === $pub_id ) {
				return '';
			}

			$code	= AAIH__ADSENSE_CODE__BEFORE .
			'pub-' . esc_attr( $pub_id ) .
			AAIH__ADSENSE_CODE__AFTER .
			AAIH__ADSENSE_CODE__END;

			return $code;

		case 'show':	// 設定内で表示時
			echo '<div class="adsense-code">';

			echo esc_html( AAIH__ADSENSE_CODE__BEFORE );
			echo '<span class="id">pub-' .'<span class="id-num">' . ( '' === $pub_id ? AAIH__AD_ID_NO_VALUE : esc_attr( $pub_id ) ) . '</span></span>';
			echo esc_html( AAIH__ADSENSE_CODE__AFTER );
			echo esc_html( AAIH__ADSENSE_CODE__END );

			echo '</div>';
			return 'ok';

		case 'insert':	// ヘッダに挿入時
			if ( '' === $pub_id ) {
				return '';
			}
			else{
				// アドセンスコードの挿入
				echo '<!-- aaih adsense code (auto ads) -->' . "\n";
				echo AAIH__ADSENSE_CODE__BEFORE;
				echo 'pub-' . esc_attr( $pub_id );
				echo AAIH__ADSENSE_CODE__AFTER;
				echo AAIH__ADSENSE_CODE__END . "\n";
				return 'ok';
			}
			break;

		default:
			aaih__popup_alert( 'aaih__adsense_code - no action: ' . $action );
	}
}

/**
 * アドセンス：広告ユニットのコードの取得
 *
 * @param string $ad_nth	Ad1, Ad2, ...
 * @param array $settings	設定値全体の配列
 *
 * @return string
 * - 取得したコードを返す
 * - pub_id ad__unit_id がない場合は 空文字 を返す
 */
function aaih__get_adsense_unit_code ( $ad_nth , $settings ) {
	// 広告ユニットの種類（ display / in_article / multiplex ）
	$ad_unit = $settings[ $ad_nth ]['ad_unit_select'];

	switch ( $ad_unit ) {
		case 'display':
			return aaih__adsense_unit_code__display_ad ( $ad_nth , 'get' , $settings );

		case 'in_article':
			return aaih__adsense_unit_code__in_article_ad ( $ad_nth , 'get' , $settings );

		case 'multiplex':
			return aaih__adsense_unit_code__multiplex_ad ( $ad_nth , 'get' , $settings );

		default:
			aaih__popup_alert( 'aaih__get_adsense_unit_code - no ad_unit: ' . $ad_unit );
	}
}


/**
 * アドセンス：広告ユニット【 記事内広告 】のコードの取得 / 表示
 *
 * @param string $ad_nth	Ad1, Ad2, ...
 * @param string $action
 * - 'get'		; コードをそのまま取得（記事内挿入時）
 * - 'show'		; コードの表示（設定内で表示時 : 全体をエスケープして echo で表示）
 * - ※）'insert' は無し
 * @param array $settings	設定値全体の配列
 *
 * @return string
 * - 'get'		; 取得したコードを返す（記事内挿入時）（ pub_id ad__unit_id がない場合は空文字を返す）
 * - 'show'		; 成功したら 'ok' を返す
 */
function aaih__adsense_unit_code__in_article_ad ( $ad_nth , $action , $settings ){
	$pub_id			= $settings[ 'pub_id' ];
	$ad__unit_id	= $settings[ $ad_nth ]['ad_unit_id__in_article'];

	switch ( $action ) {
		case 'get':	// 記事内挿入時
			if ( '' === $pub_id || '' === $ad__unit_id ) {
				return '';
			}

			$code	= AAIH__ADSENSE_IN_ARTICLE_AD_CODE__BEFORE_ID .
			'pub-' . esc_attr( $pub_id ) .
			AAIH__ADSENSE_IN_ARTICLE_AD_CODE__BEFORE_DATA_AD_SLOT .
			'data-ad-slot="' . esc_attr( $ad__unit_id ) . '"' .
			AAIH__ADSENSE_IN_ARTICLE_AD_CODE__END;

			return $code;

		case 'show':	// 設定内で表示時
			echo '<div class="in-article-ad">';

			echo esc_html( AAIH__ADSENSE_IN_ARTICLE_AD_CODE__BEFORE_ID );
			echo '<span class="id">pub-<span class="id-num">' . ( '' === $pub_id ? AAIH__AD_ID_NO_VALUE : esc_attr( $pub_id ) ) . '</span></span>';
			echo esc_html( AAIH__ADSENSE_IN_ARTICLE_AD_CODE__BEFORE_DATA_AD_SLOT );
			echo '<span class="data">data-ad-slot="<span class="data-ad-slot-num">' . ( '' === $ad__unit_id ? AAIH__AD_ID_NO_VALUE : esc_attr( $ad__unit_id ) ) . '</span>"</span>';
			echo esc_html( AAIH__ADSENSE_IN_ARTICLE_AD_CODE__END );

			echo '</div>';
			return 'ok';

		default:
			aaih__popup_alert( 'aaih__adsense_unit_code__in_article_ad - no action: ' . $action );
	}
}


/**
 * アドセンス：広告ユニット 【 ディスプレイ広告 】 のコードの取得 / 表示
 *
 * @param string $ad_nth	Ad1, Ad2, ...
 * @param string $action
 * - 'get'		; コードをそのまま取得（記事内挿入時）
 * - 'show'		; コードの表示（設定内で表示時 : 全体をエスケープして echo で表示）
 * - ※）'insert' は無し
 * @param array $settings	設定値全体の配列
 *
 * @return string
 * - 'get'		; 取得したコードを返す（記事内挿入時）（ pub_id ad__unit_id がない場合は空文字を返す）
 * - 'show'		; 成功したら 'ok' を返す
 */
function aaih__adsense_unit_code__display_ad ( $ad_nth , $action , $settings ){
	$pub_id			= $settings[ 'pub_id' ];
	$ad__unit_id	= $settings[ $ad_nth ]['ad_unit_id__display'];
	$data_ad_format	= $settings[ $ad_nth ]['ad__data_ad_format'];	// auto（自動調整） / rectangle（長方形） / vertical（縦長） / horizontal（横長）

	switch ( $action ) {
		case 'get':	// 記事内挿入時
			if ( '' === $pub_id || '' === $ad__unit_id ) {
				return '';
			}

			$code	= AAIH__ADSENSE_DISPLAY_AD_CODE__BEFORE_ID .
			'pub-' . esc_attr( $pub_id ) .
			AAIH__ADSENSE_DISPLAY_AD_CODE__BEFORE_DATA_AD_SLOT .
			'data-ad-slot="' . esc_attr( $ad__unit_id ) . '"' .
			AAIH__ADSENSE_DISPLAY_AD_CODE__BEFORE_DATA_FORMAT .
			'data-ad-format="' . esc_attr( $data_ad_format) . '"' .
			AAIH__ADSENSE_DISPLAY_AD_CODE__END;

			return $code;

		case 'show':	// 設定内で表示時
			echo '<div class="display-ad">';

			echo esc_html( AAIH__ADSENSE_DISPLAY_AD_CODE__BEFORE_ID );
			echo '<span class="id">pub-<span class="id-num">' . ( '' === $pub_id ? AAIH__AD_ID_NO_VALUE : esc_attr( $pub_id ) ) . '</span></span>';
			echo esc_html( AAIH__ADSENSE_DISPLAY_AD_CODE__BEFORE_DATA_AD_SLOT );
			echo '<span class="data">data-ad-slot="<span class="data-ad-slot-num">' . ( '' === $ad__unit_id ? AAIH__AD_ID_NO_VALUE : esc_attr( $ad__unit_id ) ) . '</span>"</span>';
			echo esc_html( AAIH__ADSENSE_DISPLAY_AD_CODE__BEFORE_DATA_FORMAT );
			echo '<span class="data">data-ad-format="<span class="data-ad-format-type">' . esc_attr( $data_ad_format ) . '</span>"</span>';
			echo esc_html( AAIH__ADSENSE_DISPLAY_AD_CODE__END );

			echo '</div>';
			return 'ok';

		default:
			aaih__popup_alert( 'aaih__adsense_unit_code__display_ad - no action: ' . $action );
	}
}


/**
 * アドセンス：広告ユニット 【 Multiplex広告 】 のコードの取得 / 表示
 *
 * @param string $ad_nth	Ad1, Ad2, ...
 * @param string $action
 * - 'get'		; コードをそのまま取得（記事内挿入時）
 * - 'show'		; コードの表示（設定内で表示時 : 全体をエスケープして echo で表示）
 * - ※）'insert' は無し
 * @param array $settings	設定値全体の配列
 *
 * @return string
 * - 'get'		; 取得したコードを返す（記事内挿入時）（ pub_id ad__unit_id がない場合は空文字を返す）
 * - 'show'		; 成功したら 'ok' を返す
 */
function aaih__adsense_unit_code__multiplex_ad ( $ad_nth , $action , $settings ){
	$pub_id			= $settings[ 'pub_id' ];
	$ad__unit_id	= $settings[ $ad_nth ]['ad_unit_id__multiplex'];

	switch ( $action ) {
		case 'get':	// 記事内挿入時
			if ( '' === $pub_id || '' === $ad__unit_id ) {
				return '';
			}

			$code	= AAIH__ADSENSE_MULTIPLEX_AD_CODE__BEFORE_ID .
			'pub-' . esc_attr( $pub_id ) .
			AAIH__ADSENSE_MULTIPLEX_AD_CODE__BEFORE_DATA_AD_SLOT .
			'data-ad-slot="' . esc_attr( $ad__unit_id ) . '"' .
			AAIH__ADSENSE_MULTIPLEX_AD_CODE__END;

			return $code;

		case 'show':	// 設定内で表示時
			echo '<div class="multiplex-ad">';

			echo esc_html( AAIH__ADSENSE_MULTIPLEX_AD_CODE__BEFORE_ID );
			echo '<span class="id">pub-<span class="id-num">' . ( '' === $pub_id ? AAIH__AD_ID_NO_VALUE : esc_attr( $pub_id ) ) . '</span></span>';
			echo esc_html( AAIH__ADSENSE_MULTIPLEX_AD_CODE__BEFORE_DATA_AD_SLOT );
			echo '<span class="data">data-ad-slot="<span class="data-ad-slot-num">' . ( '' === $ad__unit_id ? AAIH__AD_ID_NO_VALUE : esc_attr( $ad__unit_id ) ) . '</span>"</span>';
			echo esc_html( AAIH__ADSENSE_MULTIPLEX_AD_CODE__END );

			echo '</div>';
			return 'ok';

		default:
			aaih__popup_alert( 'aaih__adsense_unit_code__in_article_ad - no action: ' . $action );
	}
}


/**
 * アドセンス：ユニット広告のデータ検証
 *
 * - Ad1, Ad2, ... に対し、pub_id、ad__unit_id とも入力がある場合 'OK' を返す。
 * - それ以外は 'NG' を返す
 *
 * @param string $ad_nth : Ad1, Ad2, ...
 * @param array $settings : 設定値全体の配列
 * @return string 'OK' / 'NG'
 */
function aaih__adsense_unit_code__validation ( $ad_nth , $settings ) {
	$pub_id						= $settings[ 'pub_id' ];
	$ad_unit 					= $settings[ $ad_nth ][ 'ad_unit_select' ];
	$ad_unit_id__item_name 		= 'ad_unit_id__' . $ad_unit;

	$ad_unit_id 	= $settings[ $ad_nth ][ $ad_unit_id__item_name ];

	if ( '' === $pub_id || '' === $ad_unit_id ) {
		return 'NG';
	}
	return 'OK';

}


/**
 * アナリティクス : 挿入コードの取得 と 表示
 *
 * @param int | string 	: $analytics_id	アナｒィテイクスID。
 * @param string $action
 * - 'show'		: コードの表示（設定内で表示時 : 全体をエスケープして echo で表示）
 * - 'insert'	: コードの挿入（ヘッダに挿入時：変数のみエスケープして echo で表示）
 *
 * @return string
 * - 'show'		; 成功したら 'ok' を返す
 * - 'insert'	; 成功したら 'ok' , IDが設定されてない場合（UA-xxxx / G-xxxx）には 空文字を返す
 */
function aaih__analytics_code ( $analytics_id , $action ) {

	switch ( $action ) {
		case 'show':	// 設定内で表示時
			echo esc_html( AAIH__ANALYTICS_CODE__URL_BEFORE_ID );
			echo '<span class="id">' . esc_attr( $analytics_id ) . '</span>';
			echo esc_html( AAIH__ANALYTICS_CODE__URL_AFTER_ID );
			echo esc_html( AAIH__ANALYTICS_CODE__FUNCTION_BEFORE_ID );
			echo '<span class="id">' . esc_attr( $analytics_id ) . '</span>';
			echo esc_html( AAIH__ANALYTICS_CODE__FUNCTION_AFTER_ID );

			return 'ok';

		case 'insert':	// ヘッダに挿入時
			if ( AAIH__ANALYTICS_ID_NOTHING_ALL_STR === $analytics_id ) {
				return '';
			}
			else{
				// アナリティクスコードをヘッダに挿入
				echo AAIH__ANALYTICS_CODE__URL_BEFORE_ID;
				echo esc_attr( $analytics_id );
				echo AAIH__ANALYTICS_CODE__URL_AFTER_ID;
				echo AAIH__ANALYTICS_CODE__FUNCTION_BEFORE_ID;
				echo esc_attr( $analytics_id );
				echo AAIH__ANALYTICS_CODE__FUNCTION_AFTER_ID;

				return 'ok';
			}

		default:
			aaih__popup_alert( 'aaih__analytics_code - no action: ' . $action );
	}
}

?>