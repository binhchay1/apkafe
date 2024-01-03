<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'enqueue_block_editor_assets', 'aaih__editor_styles' );
/**
 * カスタムフィールド用のCSSを追加
 *
 * 投稿や固定ページの編集画面に表示するカスタムフィールド用のCSS。
 * wp_enqueue_style() でまずハンドルを登録し、
 * （編集画面なので）enqueue_block_editor_assets フックを使う。
 *
 * @return void
 *
 * 補足 : 以下を使っても 編集画面には cssは読みこまれない
 *
 * add_editor_style( array|string $stylesheet = 'editor-style.css' )
 * https://developer.wordpress.org/reference/functions/add_editor_style/
 *
 * apply_filters( 'mce_css', string $stylesheets )
 * https://developer.wordpress.org/reference/hooks/mce_css/
 */
function aaih__editor_styles() {
	wp_enqueue_style( 'aaih__editor_css' , plugins_url( 'css/' . AAIH__MENU_SLUG . '-editor.css' , __FILE__ ) );
}


add_action( 'add_meta_boxes', 'aaih__add_meta_box_id' );
/**
 * カスタムフィールドを追加
 *
 * 投稿と固定ページの編集画面に専用カスタムフィールドを追加する。
 * 設定が有効となるポストタイプ（ post / page ）の場合に表示する
 * アクションフック　add_meta_boxes を使用。
 *
 * @return void
 *
 * 関連
 * add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args )
 * 	$id				：	（必須）編集画面セクションの HTML ID
 * 	$title			：	（必須）編集画面セクションのタイトル、画面上に表示される
 * 	$callback		：	編集画面セクションに HTML 出力する関数。
 *						2つの引数が指定できる（第一引数： 現在編集している $post object 、第二引数：full $metabox item (an array)）
 * 	$screen			：	（文字列） （オプション） どの編集画面に表示するか
 * 						（'post','page','dashboard','link','attachment','custom_post_type','comment' ）初期値： null
 *
 *	以下不使用
 * 	$context		：不使用）（文字列） （オプション） 編集画面セクションが表示される部分 ( 'normal' , 'advanced' または (2.7 以降) 'side' )　初期値： 'advanced'
 * 	$priority		：不使用）（文字列） （オプション） ボックスが表示される優先度 ( 'high' , 'core' , 'default' または 'low' )　初期値： 'default'
 * 	$callback_args	：不使用）（array） （オプション） コールバックに渡す引数。$post object と その他何でも。初期値： null
 */
function aaih__add_meta_box_id() {
	if ( 'stop' ===  aaih__chk_login_user() ) {
		return;	// 利用制限 ON 時、ログインの状態で、ユーザーが管理者以外の場合
	};

	// 対象がpostかpageかをまずチェック
	$post_type	= aaih__get_item( 'post_type' );

	switch ( $post_type ) {
		case 'post':
			$screens = array( 'post' );
			break;
		case 'page':
			$screens = array( 'page' );
			break;
		case 'both':
			$screens = array( 'post', 'page' );
			break;
		default:
			$screens = array( 'post', 'page' );
	}

	foreach ( $screens as $screen ) {
		$id_html	= 'meta-box-aaih';
		$title 		= aaih__meta_box__get_title();
	// 記事個別の設定
		$callback_func	= 'aaih__meta_box_callback';

		add_meta_box( $id_html, $title, $callback_func, $screen );
	}
}

/**
 * メタボックス : タイトルの生成と取得
 *
 * 日本語とその他言語で表示を分ける
 *
 * @return string タイトル文字列（html）
 */
function aaih__meta_box__get_title() {
	$language 	= aaih__get_item( 'language' );
	$title 		= aaih__get_plugin_name( $language );

	return $title . ' ' . '<span class="hosoku"> <-- ' . __( 'Article individual settings', AAIH__TEXT_DOMAIN ) . ' --></span>';
}


/**
 * メタボックスの表示
 *
 * add_meta_boxesフックで呼び出している add_meta_box のコールバック関数
 * 編集画面セクションに HTML 出力する関数。
 *
 * @param WP_Post $post　現在編集している $post オブジェクト（が引数として設定できる）
 * @return void
 *
 * 補足
 * nonceフィールドを使用しセキュリティ確保する必要あり
 */
function aaih__meta_box_callback( $post ) {

	/* ------------------------------------
	 * nonceフィールドを追加して後でチェックする
	 * wp_nonce_field( $action, $name, $referer, $echo )
	 * ------------------------------------
	 * $action	：（文字列）アクションの名前。実行中のコンテキストを与える
	 * $name	：（文字列）nonce の名前。作成される hidden フィールドの name 属性。
	 * $referer	：（真偽値）wp_referer_field() 関数を使って、リファラーを表す hidden フィールドを生成するかどうか。初期値： true
	 * $echo	：（真偽値）hidden フィールドを表示する（true）か値として返す（false）か
	 *
	 * 例）フォーム内でアクションと nonce に名前をつける。1 番目と 2 番目の引数に値を入れて、必要な hidden フィールドを表示
	 * <form method="post">
	 * 		<!-- some inputs here ... -->
	 * 		<?php wp_nonce_field( 'アクションの名前', 'nonce フィールドの名前' ); ?>
	 * </form>
	 * ----------------------------------- */
	wp_nonce_field( AAIH__NONCE_ACTION__META_BOX , AAIH__NONCE_NAME__META_BOX );

	/* ------------------------------------
	 * プラグイン設定値をゲット
	 * カスタムフィールドの値をDBから取得
	* ----------------------------------- */
	$settings		= aaih__get_item();
	$meta_settings	= aaih__get_meta_data( $post->ID );


	/* ------------------------------------
	 * 記事ステータスチェック
	 * （公開 publish 記事はメッセージ表示）
	 * ----------------------------------- */
	aaih__show_meta__publish_warning();

	/* ------------------------------------
	 * カスタムフィールド表示
	 * ----------------------------------- */
	?>
	<div class="settings-wrap">
	<?php
		aaih__show_meta__ad_off( $settings, $meta_settings );
		aaih__show_meta__target_h( $settings, $meta_settings );
		aaih__show_meta__ad_space( $settings, $meta_settings );
		aaih__show_meta__ad_num( $settings, $meta_settings );
		aaih__show_meta__debug_mode( $settings, $meta_settings );
		aaih__show_meta__shortcode( $settings );
	?>
	</div>
<?php
}

/**
 * 公開されている記事の場合のメッセージ表示
 *
 * 公開されている記事の場合、編集画面からのプレビュー表示では
 * カスタムフィールドで値を変えても反映されない。
 * そのためのご注意メッセージ。
 *
 * @return void
 */
function aaih__show_meta__publish_warning() {
	$post_status	= get_post_status();

	// 公開されている記事の場合にはメッセージ表示

	if ( 'publish' === $post_status ) {
		$msg1	= __( 'This article is published. The settings here will be reflected when the article is updated.', AAIH__TEXT_DOMAIN );
		//公開されている記事のため、ここでの設定は記事の更新時に反映されます
		$msg2	= '( ' . __( 'Not reflected in the preview display', AAIH__TEXT_DOMAIN ) . ' )';
		//プレビュー表示には反映されません
		?>
		<div class="warning"><?php echo esc_attr( $msg1 );?><br /><?php echo esc_attr( $msg2 );?></div>
		<?php
	}
}

/**
 * オプション：広告非表示　カスタムフィールドへHTML表示
 *
 * @param array $settings		設定値全体
 * @param array $meta_settings		カスタムフィールド設定値全体
 * @return void
 */
function aaih__show_meta__ad_off( $settings, $meta_settings ) {
	$h_tag_ad_onoff			= $settings['h_tag_ad_onoff'];
	$first_h_tag_ad_onoff	= $settings['first_h_tag_ad_onoff'];
	$after_content_ad_onoff	= $settings['after_content_ad_onoff'];

	$meta_value				= $meta_settings['meta__ad_off'];	// on / off

	$description	= __( 'Hide ads', AAIH__TEXT_DOMAIN ); // 広告を非表示にする
	$supplement1	= __( 'Turn off all automatic insert ad settings.', AAIH__TEXT_DOMAIN );
	$supplement2	= __( '( Shortcode ads will be displayed regardless of this setting. )', AAIH__TEXT_DOMAIN );
	$supplement_setting	= __( '[ Plugin settings ] ', AAIH__TEXT_DOMAIN ) .
		__( 'Ad before the first H tag', AAIH__TEXT_DOMAIN ) . ': ' . $first_h_tag_ad_onoff . ' / ' .
		__( 'Ad before H tag', AAIH__TEXT_DOMAIN ) . ': ' . $h_tag_ad_onoff . ' / ' .
		__( 'Ad end of article', AAIH__TEXT_DOMAIN ) . ': ' . $after_content_ad_onoff;

	// 置き換え対象のショートコードがある場合のメッセージ
	$the_content	= get_the_content() ;

	if ( 'yes' === aaih__replace_shortcode__exist( $the_content , $settings ) ) {
		$supplement_setting2 = __( '* The ad replacement option settings take precedence over the plugin settings because replace shortcode is found.', AAIH__TEXT_DOMAIN ) ;
		// ※）置き換え対象のショートコードがあるため、プラグイン設定は 広告の置き換えオプション設定が優先される。
	}
	else{
		$supplement_setting2 = '';
	}

	/* 自動広告の設定を全てOFFにする
	 * ショートコードで入れている広告は表示する（この設定は影響しない）
	 * 【プラグイン設定値】
	 * 先頭のHタグ前Ad
	 * Hタグ前Ad
	 * 記事下Ad
	*/
	?>
	<input type="hidden" name="meta__ad_off" value="off">
	<label class="meta-ad-off">
		<input type='checkbox' name='meta__ad_off' <?php checked( $meta_value , 'on' ); ?> value='on'><?php echo esc_attr( $description ); ?>
	</label>
	<p class="supplement"><?php echo esc_attr( $supplement1 ); ?><br /><?php echo esc_attr( $supplement2 ); ?></p>
	<p class="supplement settings">
		<span class="first"><?php echo esc_attr( $supplement_setting ); ?></span>
		<span class="second"><?php echo esc_attr( $supplement_setting2 ); ?></span>
	</p>
<?php
}


/**
 * オプション：対象Hタグ　カスタムフィールドへHTML表示
 *
 * @param array $settings	設定値全体
 * @param array $meta_settings		カスタムフィールド設定値全体
 * @return void
 */
function aaih__show_meta__target_h( $settings, $meta_settings ) {
	$target_h_tag	= $settings['target_h_tag'];
	$meta_value		= $meta_settings['meta__target_h_tag'];	// on / off

	$str_h_tag_all	= __( 'All H tags (H2-H6)', AAIH__TEXT_DOMAIN ); // 全てのHタグ（H2-H6）
	$str_h2_only	= __( 'H2 only', AAIH__TEXT_DOMAIN ); // H2タグのみ

	$label				= __( 'Target H tag', AAIH__TEXT_DOMAIN );	// 対象Hタグ
	$supplement			= __( 'Select the H tag to insert ad.', AAIH__TEXT_DOMAIN ); // 広告を挿入する対象のＨタグを選択する
	$supplement_setting	= __( '[ Plugin settings ] ', AAIH__TEXT_DOMAIN ) . ( 'H_tag_all' === $target_h_tag ? $str_h_tag_all : $str_h2_only ) ; // 【プラグイン設定値】
?>
	<hr>
	<span class="item-label"><?php echo esc_attr( $label ); ?> : </span>
	<label class="same-as-setting">
		<input type="radio" name="meta__target_h_tag" value="same" <?php checked( $meta_value , 'same' ); ?>><?php _e( 'Same as plugin settings', AAIH__TEXT_DOMAIN ); // 設定値に同じ ?>
	</label>
	<label class="H_tag_all">
		<input type="radio" name="meta__target_h_tag" value="H_tag_all" <?php checked( $meta_value , 'H_tag_all' ); ?> ><?php _e( 'All H tags (H2-H6)', AAIH__TEXT_DOMAIN ); ?>
	</label>
	<label class="H2_only">
		<input type="radio" name="meta__target_h_tag" value="H2_only" <?php checked( $meta_value , 'H2_only' ); ?> ><?php _e( 'H2 only', AAIH__TEXT_DOMAIN ); ?>
	</label>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
	<p class="supplement settings"><?php echo esc_attr( $supplement_setting ); ?></p>
<?php
}


/**
 * オプション：広告の間隔　カスタムフィールドへHTML表示
 *
 * @param array $settings	設定値全体
 * @param array $meta_settings		カスタムフィールド設定値全体
 * @return void
 */
function aaih__show_meta__ad_space( $settings, $meta_settings ) {
	$ad_space			= $settings['ad_space'];
	$char_width_unit	= $settings['character_width_unit'];	// full / half
	$meta_value			= $meta_settings['meta__ad_space_change'];	// on / off

	$min = AAIH__AD_SPACE_MIN;
	$max = AAIH__AD_SPACE_MAX;

	$str_moji			= ' ' . __( 'characters', 				AAIH__TEXT_DOMAIN ); // 文字
	$str_kansan_zenkaku	= __( ' ( in full-width character )', 	AAIH__TEXT_DOMAIN ); // （全角換算）
	$str_kansan_hankaku	= __( ' ( in half-width character )', 	AAIH__TEXT_DOMAIN ); // （半角換算）
	$str_max			= __( 'max', 							AAIH__TEXT_DOMAIN ); // 最大
	$str_min			= __( 'min', 							AAIH__TEXT_DOMAIN ); // 最小
	$str_unit			= ( 'full' === $char_width_unit ) ? $str_moji . $str_kansan_zenkaku : $str_moji . $str_kansan_hankaku;

	$label				= __( 'Space between ads', AAIH__TEXT_DOMAIN );				// 広告の間隔
	$supplement1		= __( 'Set the space between ads to insert by number of characters. ', AAIH__TEXT_DOMAIN ); //挿入する広告の間隔を文字数で指定する。
	$supplement2		= $str_min . ' ' . $min . ' ' . $str_moji . ' / ' . $str_max . ' ' . $max . ' ' . $str_moji;
	$supplement_setting	= __( '[ Plugin settings ] ', AAIH__TEXT_DOMAIN ) . $ad_space . $str_unit;
	?>

	<hr>
	<span class="item-label"><?php echo esc_attr( $label ); ?> : </span>
	<label class="same-as-setting">
		<input type="radio" name="meta__ad_space_change" value="same" <?php checked( $meta_value , 'same' ); ?> ><?php _e( 'Same as plugin settings' , AAIH__TEXT_DOMAIN ); ?>
	</label>
	<label>
		<input type="radio" name="meta__ad_space_change" value="change" <?php checked( $meta_value , 'change' ); ?> ><?php _e( 'Change', AAIH__TEXT_DOMAIN ); // 変更する?>
	</label>
	<label>
		<input
			type='number'
			name='meta__ad_space'
			class="ad-space"
			value="<?php echo absint( $meta_settings['meta__ad_space'] ); ?>"
			min="<?php echo absint( $min ); ?>"
			max="<?php echo absint( $max ); ?>"
			step="<?php echo AAIH__AD_SPACE_STEP; ?>"> <?php echo esc_attr( $str_unit );?>
	</label>
	<p class="supplement"><?php echo esc_attr( $supplement1 ) . '( ' . esc_attr( $supplement2 ) .' )' ?></p>
	<p class="supplement settings"><?php echo esc_attr( $supplement_setting ); ?></p>
<?php
}


/**
 * オプション：記事内広告の上限の数　カスタムフィールドへHTML表示
 *
 * @param array $settings	設定値全体
 * @param array $meta_settings		カスタムフィールド設定値全体
 * @return void
 */
function aaih__show_meta__ad_num( $settings, $meta_settings ) {
	$ad_show_num	= $settings['ad_show_num'];
	$meta_value		= $meta_settings['meta__ad_show_num'];

	$label				= __( 'Max number of ads in an article', AAIH__TEXT_DOMAIN );
	$supplement			= __( 'Set the upper limit of number of ads in an article.', AAIH__TEXT_DOMAIN ); //記事内に表示する広告数の上限を設定する
	$supplement_setting	= __( '[ Plugin settings ] ', AAIH__TEXT_DOMAIN ) . ( 0 === $ad_show_num ? __( 'Unlimited', AAIH__TEXT_DOMAIN ) : $ad_show_num );
?>
	<hr>
	<span class="item-label"><?php echo esc_attr( $label ); ?></span>
	<select name="meta__ad_show_num" class="ad-show-num">

		<?php
		// スタートは -1（設置値に同じ）
		for( $i = -1 ; $i <= AAIH__AD_SHOW_NUM_MAX ; $i ++ ) {
			switch ( $i ) {
				case -1:
					$label	= __( 'Same as plugin settings', AAIH__TEXT_DOMAIN );	// 設定値に同じ
					break;
				case 0:
					$label	= __( 'Unlimited', AAIH__TEXT_DOMAIN );	// 制限しない
					break;
				default:
					$label = $i;
			}
		?>
			<option value="<?php echo (int) $i;?>"<?php echo ( $i === $meta_value ? ' selected ' : '' ); ?>>
			<?php echo esc_attr( $label ); ?>
			</option>
		<?php
		}
		?>
	</select>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
	<p class="supplement settings"><?php echo esc_attr( $supplement_setting ); ?></p>
<?php
}


/**
 * オプション：デバッグモードのon/off　カスタムフィールドへHTML表示
 *
 * @param array $settings	設定値全体
 * @param array $meta_settings		カスタムフィールド設定値全体
 * @return void
 */
function aaih__show_meta__debug_mode( $settings, $meta_settings ) {
	$debug_mode_onoff	= $settings['debug_mode_onoff'];
	$meta_value			= $meta_settings['meta__debug_mode_onoff'];	// on / off

	$str_on		= 'on ( ' . __( 'Enable', AAIH__TEXT_DOMAIN ) . ' )'; // on ( 有効 )
	$str_off	= 'off ( ' . __( 'Disable', AAIH__TEXT_DOMAIN ) . ' )'; // off ( 無効 )

	$label				= __( 'Debug mode', AAIH__TEXT_DOMAIN );	// デバッグモード
	$supplement			= __( 'Debug mode on / off.', AAIH__TEXT_DOMAIN ); // デバッグモードの on / off
	$supplement_setting	= __( '[ Plugin settings ] ', AAIH__TEXT_DOMAIN ) . ( 'on' === $debug_mode_onoff ? $str_on : $str_off ) ; // 【プラグイン設定値】
?>
	<hr>
	<span class="item-label"><?php echo esc_attr( $label ); ?> : </span>
	<label class="same-as-setting">
		<input type="radio" name="meta__debug_mode_onoff" value="same" <?php checked( $meta_value , 'same' ); ?>><?php _e( 'Same as plugin settings', AAIH__TEXT_DOMAIN ); // 設定値に同じ ?>
	</label>
	<label class="on">
		<input type="radio" name="meta__debug_mode_onoff" value="on" <?php checked( $meta_value , 'on' ); ?> ><?php _e( 'Enable', AAIH__TEXT_DOMAIN ); ?>
	</label>
	<label class="off">
		<input type="radio" name="meta__debug_mode_onoff" value="off" <?php checked( $meta_value , 'off' ); ?> ><?php _e( 'Disable', AAIH__TEXT_DOMAIN ); ?>
	</label>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
	<p class="supplement settings"><?php echo esc_attr( $supplement_setting ); ?></p>
<?php
}


/**
 * オプション：使用できるショートコードの表示　カスタムフィールドへHTML表示
 *
 * @param array $settings	設定値全体
 * @return void
 *
 * 補足：使用できるショートコードは Ad1～Adxxの内、コードの入力があるもの
 */
function aaih__show_meta__shortcode	( $settings ) {
	$label				= __( 'Available shortcodes', AAIH__TEXT_DOMAIN );	// 利用できるショートコード
	$supplement			= __( 'Display only ad shortcodes with ID input.', AAIH__TEXT_DOMAIN );	// ID入力がある広告コードのショートコードのみ表示
	$supplement_none 	= __( 'No shortcode is available because no ad code is entered yet.', AAIH__TEXT_DOMAIN );	// 広告コードの入寮kがないため利用できるショートコードはありません。

	// 利用できるショートコードの取得
	for( $i = 0 ; $i < AAIH__AD_CODE_HOW_MANY ; $i ++ ) {
		$ad_nth = 'Ad' . ( $i + 1 );
		if( 'OK' === aaih__adsense_unit_code__validation ( $ad_nth , $settings ) ){
			$shortcode_array[ $ad_nth ]	= aaih__shortcode_create( 'ad' , $ad_nth );
		}
	}

	// メタフィールド表示
?>
	<hr>
	<span class="item-label"><?php echo esc_attr( $label ); ?></span>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
	<?php

	/* -----------------------------------
	 * 利用できるショートコード表示
	 * -----------------------------------
	 * empty: var が存在しない、 または空や0の値が設定されている場合にtrueを返す
	 * 空の配列も true を返す
	 * https://www.php.net/manual/ja/function.empty.php
	 * ----------------------------------- */
	if ( empty( $shortcode_array ) ) {
	?>
		<p class="supplement"><?php echo esc_attr( $supplement_none ); ?></p>
	<?php
	}else {
		foreach ( $shortcode_array as $ad_nth => $str_shortcode ) {
			// Adxxに設定されている 広告ユニットを取得
			$ad_unit_select	= $settings[ $ad_nth ]['ad_unit_select'];

			// Adxxに設定されている memo（名前の入力）を取得
			$ad_name	= $settings[ $ad_nth ]['name'];
			if ( '' === $ad_name ) {
				$ad_name = __( '---', AAIH__TEXT_DOMAIN ); // なし表示;
			}

			// ショートコード表示
			?>
			<div class="sc">
				<pre class="shortcode"><?php echo esc_attr( $str_shortcode ); ?></pre>
				<span class="ad-unit"><?php echo esc_attr( aaih__common_str( $ad_unit_select ) ); ?></span>
				<span class="memo"><?php _e( '[ Memo ] ', AAIH__TEXT_DOMAIN ); echo esc_attr( $ad_name ); ?></span>
				<span class="used_settings">
					<?php
					_e( '[ Settings used ] ', AAIH__TEXT_DOMAIN );
					aaih__check_which_ad_settings_used ( $ad_nth , $settings )
					?>
				</span>
			</div>
		<?php

		}
	}
}


add_action( 'save_post', 'aaih__save_meta_box_data' );
/**
 * カスタムフィールドのデータ保存
 *
 * 投稿保存時に、カスタムデータも保存する
 * アクションフック save_post を使用。
 * コールバック関数の引数として、保存する記事の $post_id が受け取れる。
 *
 * @param int $post_id	保存する記事の ID
 * @return void
 *
 * 補足：
 * メタボックスの表示でセットする nonceを確認し、
 * 正しければメタフィールドの各種値を保存する
 */
function aaih__save_meta_box_data( $post_id ) {

	/* ------------------------------------
	 * nonce確認
	 * ------------------------------------ */
	// nonceがセットされているかどうか確認
	if ( ! isset( $_POST[ AAIH__NONCE_NAME__META_BOX ] ) ) {
		return;
	}

	// nonceが正しいかどうか検証
	if ( ! wp_verify_nonce( $_POST[ AAIH__NONCE_NAME__META_BOX ] , AAIH__NONCE_ACTION__META_BOX ) ) {
		return;
	}

	// 自動保存の場合はなにもしない
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	/* ------------------------------------
	 * ユーザー権限の確認
	 * ------------------------------------ */
	if ( isset( $_POST['post_type'] ) && 'page' === $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* ------------------------------------
	 * データの読み取りと保存
	 * ------------------------------------ */
	$meta_settings = AAIH__META_DEFAULT;

	// データの読み取り
	foreach( array_keys( $meta_settings ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			switch ( $key ) {
				case 'meta__ad_off':			// 広告の表示を全てoff（3つの設定を強制OFF）
				case 'meta__target_h_tag':		// 対象とするHタグ
				case 'meta__ad_space_change':	// 広告の間隔を変更するかの on / off
				case 'meta__debug_mode_onoff':	// デバッグモードの on / off
					$meta_settings[ $key ]	= sanitize_text_field( $_POST[ $key ] );
					break;

				case 'meta__ad_space':	// 広告の間隔（文字数指定）
					$meta_settings[ $key ]	= absint( $_POST[ $key ] );
					break;

				case 'meta__ad_show_num': // 記事内広告の上限（ -1: 設定値に同じ 0：無制限）
					$meta_settings[ $key ]	= (int) $_POST[ $key ];
					break;

				default:
					aaih__popup_alert( 'no case - aaih__save_meta_box_data: key : ' . $key );
			}
		}
	}

	// データ検証 : post するデータの検証なので、引数に 'get' は付けない
	$meta_settings	= aaih__item_validation( $meta_settings );

	// データの保存
	foreach ( $meta_settings as $key => $value ) {
		update_post_meta( $post_id, $key , $value );
	}

}


/**
 * メタデータ全取得
 *
 * アクションフック save_post を使用。
 * コールバック関数の引数として、保存する記事の $post_id が受け取れる。
 *
 * @param int $post_id 		：保存する記事の ID
 * @return array $meta_args	：メタフィールド内の設定値全て
 */
function aaih__get_meta_data( $post_id ) {
	/*
	* $meta_names	= array(
	* 	'meta__ad_off' 				=> 'off',	// 広告の表示を全てoff（3つの設定を強制OFF）
	* 	'meta__target_h_tag'		=> 'same',	// 対象とするHタグ
	* 	'meta__ad_space_change'		=> 'same',	// 広告の間隔を変更するかのon/off
	* 	'meta__ad_space'			=> '',		// int: 広告の間隔（文字数指定）
	* 	'meta__ad_show_num'			=> '-1',	// int: 記事内広告の上限（ -1: 設定値に同じ、0：無制限）
	* 	'meta__debug_mode_onoff'	=> 'same',	// int: 記事内広告の上限（ -1: 設定値に同じ、0：無制限）
	* );
	*/

	$meta_settings = AAIH__META_DEFAULT;
	foreach( array_keys( $meta_settings ) as $key ) {
		$meta_value = get_post_meta( $post_id, $key, true );

		// 空文字（一度も保存されてない場合）はデフォルト値
		// 広告の間隔だけは、デフォルトとして設定値をセット
		if ( 'meta__ad_space' === $key && '' === $meta_value ) {
			$meta_settings[ $key ] = aaih__get_item( 'ad_space' );

		}elseif ( '' !== $meta_value ) {
			// 空文字でない場合（一度でも保存された場合）
			$meta_settings[ $key ] = $meta_value;
		}
	}
	//データ検証
	$meta_settings	= aaih__item_validation( $meta_settings );

	return $meta_settings;
}


/**
 * メタデータによる設定変更確認
 *
 * 設定値に対してカスタムフィールド内で設定変更がされたか確認し、
 * 変更されていればその値を優先的に反映する。
 *
 * @param int $post_id 		: 保存する記事の ID
 * @param array $settings	: 設定値全体
 * @return array $settings	: カスタムフィールドの設定値を反映した設定値全体
 */
function aaih__meta_data_check( $settings ) {

	/* 記事に設定されているメタデータをすべて取得
	 * オプション：広告非表示
	 * オプション：対象Hタグ
	 * オプション：広告の間隔
	 * オプション：記事内広告の上限の数
	 */
	// post id をゲット
	$post_id 		= aaih__get_post_id();
	// カスタムフィールド設定値を全て取得
	$meta_settings	= aaih__get_meta_data( $post_id );

	foreach ( $meta_settings as $key => $value ) {
		switch ( $key ) {
			case 'meta__ad_off':	// on / off
				if ( 'on' === $value) {
					// 広告非表示がonの場合には、以下3つの設定は全てoffにする
					// ショートコードには影響しない
					$settings['h_tag_ad_onoff']			= 'off';	// Hタグ前Ad
					$settings['first_h_tag_ad_onoff']	= 'off';	// 最初のHタグ前Ad
					$settings['after_content_ad_onoff']	= 'off';	// 記事下Ad
				}
				break;

			case 'meta__target_h_tag':	// 	same / H_tag_all / H2_only
				if ( 'same' !== $value) {
					$settings['target_h_tag'] 	= $value;	// 対象とするHタグ
				}
				break;

			case 'meta__ad_space_change':	// same / change
				// 特に何もしない（ 以下の 'meta__ad_space' でまとめてチェック）
				break;

			case 'meta__ad_space':
				if ( 'change' === $meta_settings['meta__ad_space_change'] && '' !== $value ) {
					$settings['ad_space'] 		= $value;	// 広告の間隔（文字数指定）
				}
				break;

			case 'meta__ad_show_num':
				if ( -1 !== (int) $value) {		// -1: 設定値に同じ
					// 設定値に同じではない場合は 設定値をカスタムフィールドの値に書き換え
					$settings['ad_show_num'] 	= (int) $value;	// 記事内広告の上限
				}
				break;

			case 'meta__debug_mode_onoff':	// same / on / off
				if ( 'same' !== $value) {
					$settings['debug_mode_onoff'] 	= $value;	// デバッグモードの on/off
				}
				break;

			default:
				$alert_msg	= 'aaih__meta_data_check 無効な値 : key:'. $key . ', value:' . $value ;
				aaih__popup_alert( $alert_msg );
		}
	}
	return $settings;
}
?>
