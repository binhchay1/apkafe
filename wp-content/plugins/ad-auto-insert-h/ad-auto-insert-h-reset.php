<?php
/**
 * 設定リセットと結果表示
 * - 設定値のリセット動作
 * - リセット動作に対して結果の表示
 *
 * 設定値のリセット動作は、aaih__show_reset_button() で表示されるリセットボタンの form で js でまず実行の判断。
 * その後、POST で送られる nonce をチェックし実際のリセット動作に移る
 *
 * @return void
 */
function aaih__reset_check() {
	// 設定値のリセット
	aaih__reset_all_settings();
	// リセット動作の結果表示
	aaih__show_reset_result();
}

/**
 * 設定値のリセット
 *
 * - $_POST, nonce をチェックし、設定値をすべてリセット（削除）する。
 * - リセット結果はワードプレスのキャッシュに格納
 * - リセット動作後は、改めてプラグインの設定画面をロードし直す（リダイレクトにより実現）
 * - nonce が無ければ何もせずに戻る
 *
 * @return void
 */
function aaih__reset_all_settings() {
	/*
	* nonce がセットされているかまず確認。
	* セットされてなければそこで終わり。
	*/
	if ( ! isset( $_POST[ AAIH__NONCE_NAME__RESET_SETTINGS ] ) ) {	// AAIH__NONCE_NAME__RESET_SETTINGS
		return;
	}

	// nonceが正しいかどうか検証
	if ( ! wp_verify_nonce( $_POST[ AAIH__NONCE_NAME__RESET_SETTINGS ] , AAIH__NONCE_ACTION__RESET_SETTINGS ) ) {
		// 正しくなければ期限切れメッセージ表示して終わり
		wp_nonce_ays( AAIH__NONCE_ACTION__RESET_SETTINGS );
	}


	// リセット動作： AAIH__SETTINGS の削除
	if ( isset( $_POST[ AAIH__POST_NAME__RESET_SETTINGS ] ) ) {
		$button_name = aaih__common_str( 'reset_button_name' );

		if ( $_POST[ AAIH__POST_NAME__RESET_SETTINGS ] === $button_name ) {
			// DB上のプラグイン設定値の削除
			$result = delete_option( AAIH__SETTINGS );

			// 削除結果をキャッシュに格納しておく
			if ( true === $result ) {
				$result	= 'reset_OK';
			}else {
				$result	= 'reset_NG';
			}
			aaih__cache__set_reset_settings_result( $result );
			//aaih__popup_alert( $msg );
		}
		// リセット動作をしたので改めて設定画面を表示し直し
		aaih__redirect_to_plugin();
	}
}

/**
 * 設定値リセットの結果表示
 *
 * キャッシュを確認し、リセット操作をしている場合には
 * javascript の pop up で成功／失敗の結果を表示する。
 *
 * - リセット成功時：全ての設定値をリセットしました。
 * - リセット失敗時：何かの理由で設定値がリセットできませんでした。
 *
 * 結果表示後はキャッシュを削除しておく。
 *
 * @return void
 */
function aaih__show_reset_result() {
	// リセット操作をしているか、キャッシュデータを取得
	$reset_result	= aaih__cache__get_reset_settings_result();

	// リセット用キャッシュデータが正しくない場合は単に戻る
	if ( 'reset_OK' !== $reset_result && 'reset_NG' !== $reset_result ) {
		return;
	}

	// リセットに成功した場合
	if ( 'reset_OK' === $reset_result ) {
		$msg	= __( 'All settings have been reset.', AAIH__TEXT_DOMAIN );	// 全ての設定値をリセットしました。
	}else {
		// リセットに失敗した場合
		$msg	= __( 'The setting value could not be reset for some reason.', AAIH__TEXT_DOMAIN ) . '\n' . __( 'Please try again.', AAIH__TEXT_DOMAIN );
		// 何かの理由で設定値がリセットできませんでした。
		// もう一度お試しください。
	}
	aaih__popup_alert( $msg );

	// キャッシュの削除
	aaih__cache__delete_reset_settings_result();
}


/**
 * 設定リセット情報をキャッシュ
 *
 * @return void
 *
 * 関連
 * set_transient( string $transient, mixed $value, int $expiration )
 * $expiration:(int) (Optional) Time until expiration in seconds. Default 0 (no expiration).
 */
function aaih__cache__set_reset_settings_result( $result ) {
	$expiration	= AAIH__CACHE_RESET_EXPIRATION_TIME;
	set_transient( AAIH__RESET_SETTINGS_CACHE_NAME , $result , $expiration );
}

/**
 * 設定リセット情報のキャッシュを削除
 *
 * @return void
 */
function aaih__cache__delete_reset_settings_result() {
	delete_transient( AAIH__RESET_SETTINGS_CACHE_NAME );
}

/**
 * 設定リセット情報のキャッシュの値を取得
 *
 * @return mixed	false | string
 *
 * - false : the transient does not exist, does not have a value, or has expired.
 * get_transient( string $transient ):
 * - string: reset_OK or reset_NG
 */
function aaih__cache__get_reset_settings_result() {
	$result	= get_transient( AAIH__RESET_SETTINGS_CACHE_NAME );
	return $result;
}
?>