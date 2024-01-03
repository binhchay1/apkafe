<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * 各フィールドのコールバック関数
 */

/* -----------------------------------
 * 基本の設定
 * ----------------------------------- */
/**
 * 広告選択
 *
 * 先頭のHタグ前Ad / Hタグ前Ad / 記事下Ad の enable チェックボックス と
 * 各々に対して Adセレクトボックスを表示する
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__ad_insert_onoff__ad_select( $args ) {
	$settings			= $args['settings'];

	$item				= array(
		'first_h_tag' 	=> __( 'Ad before the first H tag' , AAIH__TEXT_DOMAIN ),
		'h_tag' 		=> __( 'Ad before H tag', AAIH__TEXT_DOMAIN ),
		'after_content' => __( 'Ad end of article', AAIH__TEXT_DOMAIN ),
	);
	$class			= array(
		'first_h_tag' 	=>	'first-h-tag',
		'h_tag' 		=>	'h-tag',
		'after_content' => 'after-content',
	);
	$supplement	= array(
		'first_h_tag' 	=> __( 'Always insert an ad code before the first H tag.' , AAIH__TEXT_DOMAIN ) , 	//先頭のHタグの前に必ず広告コードを入れる。
		'h_tag' 		=> __( 'Insert an ad code before H tags in the article.' , AAIH__TEXT_DOMAIN ), 	//記事内のHタグの前に広告コードを入れる。
		'after_content' => __( 'Always insert an ad code at the end of the article.' , AAIH__TEXT_DOMAIN ), // 記事の下に必ず広告コードを入れる。
	);
	?>
	<table class="options narrow ad-insert-onoff">
		<?php
		foreach( $item as $item_name => $item_label ) {
			$ad_select_item			= 'ad_select__' . $item_name;
			$selectbox__attr_name	= AAIH__SETTINGS . '[' . $ad_select_item . ']';	// セレクトボックスの name 属性
			$selected_ad_nth		= $settings[ $ad_select_item ];

			$onoff_item_name		= $item_name . '_ad_onoff';
			$onoff					= $settings[ $onoff_item_name ];
			?>
			<tr class="ad-select-onoff <?php echo esc_attr( $class[ $item_name ] . ' ' . esc_attr( $onoff )); ?>">
				<th><?php echo esc_attr( $item_label );?></th>
				<td class="onoff"><?php aaih__ad_insert__onoff( $item_name , $settings ); ?></td>
				<td class="ad"><?php aaih__ad_selectbox( $selectbox__attr_name , $selected_ad_nth , $settings , $item_name ); ?></td>
			</tr>
			<tr>
				<th></th>
				<td colspan="2" class="supplement"><p class="supplement"><?php echo esc_attr( $supplement[ $item_name ] ); ?></p></td>
			</tr>
			<?php
		}
		?>
	</table>
<?php
}

/**
 * 広告選択のon/off チェックボックス
 *
 * 先頭のHタグ前Ad / Hタグ前Ad / 記事下Ad の enable チェックボックス 表示共通化
 *
 * @param string $item_name	first_h_tag / h_tag / after_content
 * @param array $settings	設定値全体の配列
 * @return void
 */
function aaih__ad_insert__onoff( $item_name , $settings ){
	$ad_onoff_item	= $item_name . '_ad_onoff';
	$ad_onoff_value	= $settings[ $ad_onoff_item ];
	?>
	<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $ad_onoff_item ); ?>]" value="off">
	<label>
		<input type='checkbox' class="ad-select-onoff-checkbox" name='<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $ad_onoff_item ); ?>]' <?php checked( $ad_onoff_value, 'on' ); ?> value='on'>
		<?php _e( 'Enable', AAIH__TEXT_DOMAIN ); // 有効にする ?>
	</label>
	<?php
}


/**
 * 広告選択のセレクトボックスの表示
 *
 * - 広告選択、広告置き換え対応 の Adxx 選択セレクトボックスの表示。
 * - セレクトボックスの name 属性について、
 * 広告選択（1次元配列）、広告置き換え対応（2次元配列）と異なることから、name属性自体もパラメーターとして渡す
 *
 * @param string $selectbox__attr_name	セレクトボックスの name属性 の文字列
 * @param string $selected_ad_nth	選択されている Adxx
 * @param string $settings	設定値全体の配列
 * @param string $item_name 広告選択の場合、ad_select__xxxx, 広告置き換え対応の場合 ad_replace が入る
 * @return void
 */
function aaih__ad_selectbox( $selectbox__attr_name , $selected_ad_nth , $settings , $item_name ='' ) {
	// メモ欄の表示の on/off （ off 時では メモは表示）
	$memo_onoff = $settings['memo_input_onoff'];

	// セレクトボックス親要素のクラス : 選択されている広告が有効かどうか
	$class = ( 'OK' === aaih__adsense_unit_code__validation ( $selected_ad_nth , $settings ) ) ? 'enable' : 'disable';
	?>
	<select class="ad-select <?php echo esc_attr( $class ); ?>" name="<?php echo esc_attr( $selectbox__attr_name ); ?>">
		<?php
		// 表示される入力フォームの数だけ Adxx を表示
		for( $i = 0 ; $i < AAIH__AD_CODE_HOW_MANY ; $i ++ ) {
			$ad_nth			= 'Ad' . ( $i + 1 ); // Ad1 ～ Adxx
			$ad_unit		= $settings[ $ad_nth ]['ad_unit_select'];	// Adxx の 広告ユニット
			$ad_unit_name	= aaih__common_str( $ad_unit );			// Adxx の 広告ユニット名
			$ad_code_name	= $settings[ $ad_nth ]['name'];				// Adxx の 名前（メモ入力）

			// Adxx のデータ検証（pub_id, ad_unit_id が設定されていて有効なコードかどうか）
			if ( 'OK' === aaih__adsense_unit_code__validation ( $ad_nth , $settings ) ) {
				$enable = 'enable';
			}
			else{
				$enable = 'disable';
			}
		?>
			<option
				value="<?php echo esc_attr( $ad_nth );?>"
				class="<?php echo esc_attr( $enable );?>"
				<?php echo $selected_ad_nth === $ad_nth ? ' selected ' : ''; ?>>
				<?php
					echo esc_attr( $ad_nth );

					// Adxx のデータ検証（pub_id, ad_unit_id が設定されていて有効なコードかどうか）
					if ( 'enable' === $enable ) {
						echo ' ( ' . esc_attr( $ad_unit_name ) . ' ) ';
					}
				?>
				<?php echo ( 'on' === $memo_onoff  && $ad_code_name ? ' '. esc_attr( $ad_code_name ) : '' ); ?>
			</option>
		<?php
		}
		?>
	</select>

	<?php // 広告選択 , 広告置き換え対応 の場合分け
		switch ( $item_name ) {
			case 'first_h_tag':
			case 'h_tag':
			case 'after_content':
				$onoff = $settings[ $item_name . '_ad_onoff' ];
				break;
			case 'ad_replace':
				$onoff = $settings[ $item_name . '_onoff' ];
				break;
			default:
				aaih__popup_alert( 'aaih__ad_selectbox : no case : ' . $item_name );
		}

		// off の場合は passive（非表示）
		if( 'off' === $onoff ){
			$passive_active = 'passive';
		}
		// on の場合、広告が有効な場合（enableの場合）passive（非表示）
		else if ( 'on' === $onoff && 'enable' === $class ) {
			$passive_active = 'passive';
		}
		// on の場合、広告が無効な場合（disableの場合）active （表示）
		else{
			$passive_active = 'active';
		}
		?>
			<!-- div class="check-wrap<?php echo ( 'active' === $passive_active ? ' blink' : '' );?>" -->
			<div class="check-wrap blink">
				<span class="check <?php echo esc_attr( $passive_active ); ?>"></span>
			</div>
		<?php
}


/* -----------------------------------
 * 対象記事の種別
 * ----------------------------------- */
/**
 * コールバック）対象記事の種別（投稿／固定ページ／両方）
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__post_type( $args ) {
	$settings	= $args['settings'];
	$post_type	= $args['settings']['post_type'];
	$supplement	= __( 'Select target post type to insert ad codes : post / page / both.', AAIH__TEXT_DOMAIN ); 
	//広告を挿入する対象の記事の種類（投稿／固定ページ／両方）を選択する。
	?>
	<label><input type="radio" name="<?php echo AAIH__SETTINGS; ?>[post_type]" value="post" <?php checked( $post_type, 'post' ); ?> ><?php _e( 'Post', AAIH__TEXT_DOMAIN ); ?></label>
	<label><input type="radio" name="<?php echo AAIH__SETTINGS; ?>[post_type]" value="page" <?php checked( $post_type, 'page' ); ?> ><?php _e( 'Page', AAIH__TEXT_DOMAIN ); ?></label>
	<label><input type="radio" name="<?php echo AAIH__SETTINGS; ?>[post_type]" value="both" <?php checked( $post_type, 'both' ); ?> ><?php _e( 'Post & Page', AAIH__TEXT_DOMAIN ); ?></label>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
<?php
}

/* -----------------------------------
 * 広告表示の設定
 * ----------------------------------- */
/**
 * コールバック）記事内広告の上限の数
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__ad_show_num( $args ) {
	$settings		= $args['settings'];
	$ad_show_num	= $args['settings']['ad_show_num'];
	$supplement		= __( 'Set the upper limit of number of ads in an article.', AAIH__TEXT_DOMAIN );
?>
	<select name="<?php echo AAIH__SETTINGS; ?>[ad_show_num]">
	<?php
		for( $i = 0 ; $i <= AAIH__AD_SHOW_NUM_MAX ; $i ++ ) {
			if ( 0 === $i ) {
				$label	= __( 'Unlimited', AAIH__TEXT_DOMAIN ); //制限しない
			}
			else {
				$label = $i;
			}
		?>
			<option value="<?php echo esc_attr( $i );?>"<?php echo $ad_show_num === $i ? ' selected ' : ''; ?>><?php echo esc_attr( $label ); ?></option>
		<?php
		}
		?>
	</select>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
<?php
}

/**
 * コールバック）対象Hタグ
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__target_h_tag( $args ) {
	$target_h_tag	= $args['settings']['target_h_tag'];
	$supplement		= __( 'Select target H tag to insert an ad code.', AAIH__TEXT_DOMAIN );
						// 広告を挿入する対象のHタグを選択する。
	?>
	<label><input type="radio" name="<?php echo AAIH__SETTINGS; ?>[target_h_tag]" value="H_tag_all" <?php checked( $target_h_tag, 'H_tag_all' ); ?> ><?php _e( 'All H tags (H2-H6)', AAIH__TEXT_DOMAIN ); ?></label>
	<label><input type="radio" name="<?php echo AAIH__SETTINGS; ?>[target_h_tag]" value="H2_only" <?php checked( $target_h_tag, 'H2_only' ); ?> ><?php _e( 'H2 only', AAIH__TEXT_DOMAIN ); ?></label>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
<?php
}

/**
 * コールバック）広告の間隔
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__ad_space( $args ) {
	// 広告の間隔
	$ad_space			= $args['settings']['ad_space'];

	// 文字数の単位：全角 or 半角
	$char_width_unit	= $args['settings']['character_width_unit'];

	// 全角換算、半角換算の表示文字列
	$str_full_width 	= __( 'characters', AAIH__TEXT_DOMAIN ) . __( ' ( in full-width character )', AAIH__TEXT_DOMAIN );
	$str_hald_width 	= __( 'characters', AAIH__TEXT_DOMAIN ) . __( ' ( in half-width character )', AAIH__TEXT_DOMAIN );
	$unit_str			= ( 'full' === $char_width_unit ) ? $str_full_width : $str_hald_width ;

	// 補足説明用の文言
	$supplement1		= __( 'Specify the space between ads to be inserted by the number of characters.', AAIH__TEXT_DOMAIN );
							//挿入する広告の間隔を文字数で指定する。
	$supplement_half	= __( 'The number of half-width characters ( normal alphanumeric characters ). If you want to convert to 1000 full-width characters such as Japanese, specify double, , 2000.' , AAIH__TEXT_DOMAIN );
							//半角英数の文字数。全角1000文字とする場合には倍の2000を指定する。
	$supplement_full	= __( 'The number of full-width characters such as Japanese If you want to convert to 2000 half-width characters ( normal alphanumeric characters), specify half, 1000.', AAIH__TEXT_DOMAIN );
							//全角日本語の文字数。半角2000文字とする場合には2分の1の1000を指定する。
	$supplement2		= ( 'full' === $char_width_unit ) ? $supplement_full : $supplement_half ;
	?>
	<label>
		<input
			type='number'
			name='<?php echo AAIH__SETTINGS; ?>[ad_space]'
			value="<?php echo absint( $ad_space ); ?>"
			min="<?php echo AAIH__AD_SPACE_MIN; ?>"
			max="<?php echo AAIH__AD_SPACE_MAX; ?>"
			step="<?php echo AAIH__AD_SPACE_STEP; ?>"> <?php echo esc_attr( $unit_str );?>
	</label>
	<p class="supplement"><?php echo esc_attr( $supplement1 ) . '<br />' . esc_attr( $supplement2 ); ?></p>
<?php
}

/* -----------------------------------
 * 広告コード設定
 * ----------------------------------- */

 /**
 * パブリッシャーIDの入力
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__pub_id( $args ) {
	$settings		= $args['settings'];
	$pub_id 		= $settings['pub_id'];
	$class			= 'id pub-id';
	?>
	<table class="options narrow pub-id-input">
		<tr>
			<th><?php _e( 'Publisher ID' , AAIH__TEXT_DOMAIN );?></th>
			<td><?php aaih__pub_id_input( $class , 'pub_id' , $pub_id ); ?></td>
		</tr>
	</table>

	<?php
	// jsに渡す用
	?>
	<input type="hidden" id="ad-id-no-value" value="<?php echo AAIH__AD_ID_NO_VALUE?>">
	<?php
}

/**
 * パブリッシャーIDの入力表示
 *
 * - 一般設定の広告コードで入力を行う。
 * - 自動広告の設定でもパブリッシャーIDの入力があった方が便利なため、この部分だけ関数化している
 *
 * @param string $class	広告コードの設定 と AdSense自動広告とで分けるため 異なる class を設定
 * @param string $item_name
 * @param int $item_value
 * @param string $supplement
 * @return void
 */
function aaih__pub_id_input( $class , $item_name , $item_value , $supplement ='' ) {
	$supplement		= __( 'Input AdSense Publisher ID (" pub-xxxxx " part of " ca-pub-xxxxx " ).', AAIH__TEXT_DOMAIN );	// パブリッシャーID は「ca-pub-xxxxx」の「pub-xxxxxx」の部分。
	?>
	<label>pub-
		<input
			type='text'
			class='<?php echo esc_attr( $class ); ?>'
			name='<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $item_name ); ?>]'
			<?php
				if( '' === $item_value ) {
				?>
					value=""
				<?php
				}else {
				?>
					value="<?php echo esc_attr( $item_value ); ?>"
				<?php
				}
			?>
			maxlength="<?php echo AAIH__PUB_ID_MAXLENGTH; ?>"
		>
	</label>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
	<?php
}

/**
 * 広告コードの選択タブ表示
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__ad_select_tab( $args ) {
	$settings	= $args['settings'];

	// 広告コード入力のactiveタブ番号を取得
	$tab_num = $settings['ad_code_tab_num'];
	$tab_num = aaih__get_tab_num( $tab_num );

	// 実際のタブ表示：設定できる広告コードの数だけ表示
	?>
	<div class="ad-code-tab margin-top3">
		<?php
		for( $i = 0; $i < AAIH__AD_CODE_HOW_MANY ; $i ++ ) {
			$nth	= $i + 1;
			$ad_nth = 'Ad' . $nth;

			/*
			$class	= 'tab' .' '. strtolower( $ad_nth );
			*/
		// ユニーククラス を設定（コード設定箇所と区別するため）
			$class	= 'tab';
			// 設定する class を取得（コード設定部と共通）
			$class = aaih__get_class__ad_tab_ad_code( $i , $class , $settings );

			// タブを表示
			?>
			<div class="<?php echo esc_attr( $class ); ?>"><span class="ad-tab"><?php echo esc_attr( $ad_nth ); ?></span></div>
			<?php
		}
		?>
	</div>

	<?php
	// 広告コード入力のタブナンバーを読み込みセットする（画面には表示しない）
	// 変更を保存時のみ設定値を読み込み反映。
	// $value は、タブがクリックされると jQueryで値を変更している（その値を保存し読み込む）
	?>
	<input id="ad-code-tab-num" type="hidden" name="<?php echo AAIH__SETTINGS; ?>[ad_code_tab_num]" value="<?php echo esc_attr( $tab_num );?>">
	<?php
}

/**
 * 各Ad code 設定画面（Ad1, Ad2, ... ）
 *
 * @param array $args	: string $class , int $nth（タブ番号 / 広告コードの番号 ）, array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__ad_code( $args ) {
	$settings	= $args['settings'];	// 設定値全体
	$ad_nth		= $args['ad_nth'];		// Ad1, Ad2, ...

	// 表示用の設定値の取得
	$label_input_onoff	 			= $settings['label_input_onoff'];				// 広告ラベルの入力欄を表示するか
	$memo_input_onoff	 			= $settings['memo_input_onoff'];				// メモ欄を表示するか

	// その他オプション
	$title__options		= __( 'Options', AAIH__TEXT_DOMAIN );	// オプション

	?>
	<div class="ad-code <?php echo esc_attr( strtolower( $ad_nth ) );?>">
		<?php
		/* -----------------------------------
		 *
		 * 先頭：使用している設定表示
		 *
		 * ----------------------------------- */
		echo '<span class="supplement">';
		aaih__check_which_ad_settings_used ( $ad_nth, $settings );
		echo '</span>';

		/* -----------------------------------
		 *
		 * 広告ユニット・広告ユニットID
		 *
		 * ----------------------------------- */
		$title		= __( 'Select ad unit', AAIH__TEXT_DOMAIN );	// 広告ユニットの選択

		// 広告ユニット（ディスプレイ広告選択時に 広告の形状選択を表示するために使用）
		$ad_unit_select	= $settings[ $ad_nth ]['ad_unit_select'];	// 'in_article' 記事内広告 , 'display' ディスプレイ広告 , 'multiplex' Multiplex 広告

		?>
		<div class="table-title first"><?php echo esc_attr( $title ); ?></div>
		<table class="options narrow ad-unit">
			<tr class="select-all">
				<th><?php _e( 'Ad unit' , AAIH__TEXT_DOMAIN ); ?></td>
				<td><?php aaih__ad_unit_select( $ad_nth, $settings ); // 広告ユニット選択 ?></td>
				<td><?php aaih__ad_unit_id_input_all( $ad_nth, $settings ); // 広告ユニットID 入力 ?></td>
			</tr>
			<tr>
				<th></th>
				<td colspan="2" class="supplement"><?php aaih__ad_unit_explain( $ad_nth , $settings ); // 広告ユニットガイド ?></td>
			</tr>
		</table>

		<?php
		/* -----------------------------------
		 *
		 * 広告ユニット : 広告の形状
		 *
		 * ----------------------------------- */
		?>
		<table class="options narrow ad-data-ad-format <?php echo 'display' === $ad_unit_select ? 'active' : 'passive';?>">
			<tr>
				<th><?php _e( 'Ad shape' , AAIH__TEXT_DOMAIN ); ?></td>
				<td><?php aaih__ad_data_ad_format_select( $ad_nth, $settings ); // 広告形状の選択 ?></td>
			</tr>
		</table>

		<?php
		/* -----------------------------------
		 *
		 * 広告コード表示
		 *
		 * ----------------------------------- */
		?>
		<table class="options narrow code-insert">
			<tr class="caption top">
				<td><p class="supplement"><?php echo esc_attr( aaih__common_str( 'code_up_supplement' ) ); ?></p></td>
			</tr>
			<tr class="code">
				<td class="show-code"><?php aaih__adsense_unit_code__show_all( $ad_nth , $settings ); // 広告コードの実際の表示 ?></td>
			</tr>
			<?php
				if( '' === $settings['pub_id'] ) { ?>
				<tr class="caption bottom">
					<td>
						<p class="supplement"><?php echo esc_attr( aaih__common_str( 'code_down_supplement' , 2 ) ); ?></p>
					</td>
				</tr>
				<?php
				}
			?>
		</table>

		<?php
		/* -----------------------------------
		 *
		 * オプション
		 *
		 * ----------------------------------- */
		?>
		<div class="table-title"><?php echo esc_attr( $title__options ); ?></div>
		<table class="options narrow other-options">
			<tr class="ad-updown-margin">
				<th class="label"><?php _e( 'Margins' , AAIH__TEXT_DOMAIN ); ?></td>
				<td class="item"><?php aaih__ad_code_input_margin( $ad_nth, $settings ); // 広告上下のマージン入力 ?></td>
			</tr>

			<tr class="centering">
				<th class="label"><?php _e( 'Centering' , AAIH__TEXT_DOMAIN ); ?></td>
				<td class="item"><?php aaih__ad_code_centering_on_off( $ad_nth , $settings ); // 広告のセンタリング ?>
				</td>
			</tr>
			<tr class="ad-label <?php echo 'off' === $label_input_onoff ? 'passive' : 'active'; ?>">
				<th class="label"><?php _e( 'Ad label' , AAIH__TEXT_DOMAIN ); // 広告ラベルと広告との間のスペース入力 ?></td>
				<td class="item"><?php aaih__ad_code_label_input( $ad_nth , $settings ); ?></td>
			</tr>
		</table>

		<?php
		/* -----------------------------------
		 *
		 * その他
		 *
		 * ----------------------------------- */
		$title 				= __( 'Others' , AAIH__TEXT_DOMAIN ); 	// その他
		?>
		<div class="table-title"><?php echo esc_attr( $title ); ?></div>
		<table class="options others">
			<tr class="ad-name <?php echo 'off' === $memo_input_onoff ? 'passive' : 'active'; ?>">
				<th class="label"><?php _e( 'Memo (Ad name)' , AAIH__TEXT_DOMAIN ); // メモ（広告名）入力 ?></td>
				<td class="item"><?php aaih__ad_code_memo_input( $ad_nth , $settings ); ?></td>
			</tr>
			<tr class="ad-shortcode">
				<th class="label"><?php _e( 'Shortcode' , AAIH__TEXT_DOMAIN ); // ショートコード表示 ?></td>
				<td class="item"><?php aaih__ad_code_shortcode_display( $ad_nth ); ?></td>
			</tr>
		</table>
	</div>
<?php
}

/**
 * どの設定に使われているコードか表示
 *
 * Ad1, Ad2, xxxx に対して、
 * 先頭のHタグ前Ad / Hタグ前Ad / 記事下Ad の いずれか使用されていたら
 * その設定を表示（視覚的に分かりやすくするため）
 *
 * - 設定 が ON
 * - 且つ、その設定で指定されていること 
 *
 * @param string $ad_nth	Ad1, Ad2, xxxx
 * @param array $settings	設定値全体の配列
 *
 * @return void
 */
function aaih__check_which_ad_settings_used ( $ad_nth , $settings ){
	// 先頭のHタグ前Ad, Hタグ前Ad, 記事下Ad の on/off 設定取得
	$onoff_0	= $settings['first_h_tag_ad_onoff'];
	$onoff_1	= $settings['h_tag_ad_onoff'];
	$onoff_2	= $settings['after_content_ad_onoff'];

	// 先頭のHタグ前Ad, Hタグ前Ad, 記事下Ad の設定Ad Ad 取得
	$ad_0 		= $settings['ad_select__first_h_tag'];
	$ad_1 		= $settings['ad_select__h_tag'];
	$ad_2 		= $settings['ad_select__after_content'];

	// 使用されているかのチェックして class を作る
	$class_0	= $ad_nth === $ad_0 && 'on' === $onoff_0 ? 'used' : 'not-used';
	$class_1	= $ad_nth === $ad_1 && 'on' === $onoff_1 ? 'used' : 'not-used';
	$class_2	= $ad_nth === $ad_2 && 'on' === $onoff_2 ? 'used' : 'not-used';

	// 使用されているときの表示文言
	$used_ad_0	= __( 'Ad the first H tag', 	AAIH__TEXT_DOMAIN ); 	// （最初のHタグ前Ad）
	$used_ad_1	= __( 'Ad H tag', 			AAIH__TEXT_DOMAIN ); 	// （Hタグ前Ad）
	$used_ad_2	= __( 'Ad end of article', 	AAIH__TEXT_DOMAIN ); 	// （記事下Ad）

	// 実際の表示
	?>
	<ul class="used-ad-all">
		<li class="<?php echo esc_attr( $class_0 ); ?>"><?php echo esc_attr( $used_ad_0 ); ?></li>
		<li class="<?php echo esc_attr( $class_1 ); ?>"><?php echo esc_attr( $used_ad_1 ); ?></li>
		<li class="<?php echo esc_attr( $class_2 ); ?>"><?php echo esc_attr( $used_ad_2 ); ?></li>
	</ul>
	<?php
}

/**
 * 広告ユニットの選択
 *
 * @param string $ad_nth	Ad1, Ad2, ....
 * @param array $settings	設定値全体の配列
 * @return void
 */
function aaih__ad_unit_select( $ad_nth , $settings ){
	$ad_unit_select = $settings[ $ad_nth ]['ad_unit_select'];	// 広告ユニット
	$ad_unit		= array(
		'in_article',
		'display',
		'multiplex',
	);
?>
	<select 
		class="ad-unit-select"
		name="<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $ad_nth );?>][ad_unit_select]"
	>
	<?php
		foreach( $ad_unit as $key => $ad_unit_name ) {
		?>
			<option value="<?php echo esc_attr( $ad_unit_name ); ?>"<?php echo $ad_unit_select === $ad_unit_name ? ' selected ' : ''; ?>>
			<?php echo aaih__common_str( $ad_unit_name ); ?></option>
		<?php
		}
		?>
	<?php
}

/**
 * 広告ユニットの選択 と 広告ユニット ID 入力
 *
 * 広告ユニットは3種類対応：in_article / display / multiplex
 * 各々に対して 広告ユニットID の入力がある。
 *
 * @param string $ad_nth	Ad1, Ad2, ...
 * @param array $settings	全ての設定値のキーと値の配列
 * @return void
 */
function aaih__ad_unit_id_input_all( $ad_nth , $settings ) {
	// 設定されている広告ユニット
	$ad_unit_select	= $settings[ $ad_nth ]['ad_unit_select'];

	// 広告ユニット入力 にセットする class
	// 選択されている tr の class に active, それ以外は passive を付ける
	$class = array (
		'in_article'	=> 'passive',
		'display'		=> 'passive',
		'multiplex'		=> 'passive',
	);
	$class[ $ad_unit_select ] = 'active';
?>
	<div class="ad-unit-id-item in-article <?php echo esc_attr( $class['in_article'] ); ?>"><?php aaih__ad_unit_id_input ( 'in_article' , $ad_nth , $settings ); ?></div>
	<div class="ad-unit-id-item display <?php echo esc_attr( $class['display'] ); ?>"><?php aaih__ad_unit_id_input ( 'display' , $ad_nth , $settings ); ?></div>
	<div class="ad-unit-id-item multiplex <?php echo esc_attr( $class['multiplex'] ); ?>"><?php aaih__ad_unit_id_input ( 'multiplex' , $ad_nth , $settings ); ?></div>
<?php
}

/**
 * 広告ユニットの説明表示
 *
 * アドセンスの広告ユニット（ 記事内広告 , ディスプレイ広告 , Multiplex 広告）各々の説明を表示
 *
 * @param string $ad_nth
 * @param array $settings 設定値全体の配列
 * @return void
 */
function aaih__ad_unit_explain( $ad_nth , $settings ){
	// 設定されている広告ユニット
	$ad_unit_select	= $settings[ $ad_nth ]['ad_unit_select'];

	$supplement = __( 'Ad unit ID is the value xxxxx of data-ad-slot=xxxxx in the ad code.', AAIH__TEXT_DOMAIN );	// 広告ユニットIDは、広告コード中の data-ad-slot の値（ data-ad-slot=xxxxx の xxxxx）。

	$ad_explain = array (
		'in_article'	=> __( 'In-article ads : native ads that appear in editorial content, designed to fit neatly inside the user\'s path through your site.'	, AAIH__TEXT_DOMAIN ),		// 記事内広告
		'display'		=> __( 'Display ads : all-rounder, these ads work well anywhere.'	, AAIH__TEXT_DOMAIN ),	// ディスプレイ広告
		'multiplex'		=> __( 'Multiplex ads : Grid-based ad unit that shows content recommendation-style native ads, designed to fit neatly inside the user\'s path through your site.'	, AAIH__TEXT_DOMAIN ),
		// 記事内広告 : 記事コンテンツ内に表示されるネイティブ広告（コンテンツに自然に溶け込むように表示される広告）。
		// ディスプレイ広告 : どこにでも使えるオールラウンドな広告。
		// Multiplex 広告 : 広告ユニット内のグリッドに複数の広告を表示する、ネイティブ広告（コンテンツに自然に溶け込むように表示される広告）フォーマット。
	);
	$explanation_class = array (
		'in_article'	=> ' passive',
		'display'		=> ' passive',
		'multiplex'		=> ' passive',
	);
	$explanation_class[ $ad_unit_select ] = ' active';
	?>
	<div class="explain">
		<p class="ex in-article supplement<?php echo esc_attr( $explanation_class['in_article'] ); ?>"><?php echo esc_attr( $ad_explain[ 'in_article' ] ); ?></p>
		<p class="ex display supplement<?php echo esc_attr( $explanation_class['display'] ); ?>"><?php echo esc_attr( $ad_explain[ 'display' ] ); ?></p>
		<p class="ex multiplex supplement<?php echo esc_attr( $explanation_class['multiplex'] ); ?>"><?php echo esc_attr( $ad_explain[ 'multiplex' ] ); ?></p>
		<p class="ad-unit-id supplement"><?php echo esc_attr( $supplement ); ?></p>
	</div>
	<?php
}

/**
 * 広告ユニット ID 入力 （ data ad slot の値 ）
 *
 * @param string $ad_unit ; in_article / display / multiplex
 * @param string $ad_nth : Ad1, Ad2, ...
 * @param array $settings	全ての設定値のキーと値の配列
 * @return void
 */
function aaih__ad_unit_id_input ( $ad_unit , $ad_nth , $settings ) {

	$class	= array(
		'in_article'	=> 'ad-unit-id ' . 'in-article'	. '-id',
		'display'		=> 'ad-unit-id ' . 'display'	. '-id',
		'multiplex'		=> 'ad-unit-id ' . 'multiplex'	. '-id',
	);
	$item_name	= array(
		'in_article'	=> 'ad_unit_id__in_article',
		'display'		=> 'ad_unit_id__display',
		'multiplex'		=> 'ad_unit_id__multiplex',
	);
	$item_value	= array(
		'in_article'	=> $settings[ $ad_nth ][ $item_name['in_article']	],	// 記事内広告
		'display'		=> $settings[ $ad_nth ][ $item_name['display']		],	// ディスプレイ広告
		'multiplex'		=> $settings[ $ad_nth ][ $item_name['multiplex']	],	// Multiplex 広告
	);

	$label = __( 'Ad unit ID' , AAIH__TEXT_DOMAIN ) ;
	$language = $settings['language'];
?>
	<div class="label <?php echo esc_attr( $language ); ?>">
		<div class="all">
			<span class="ad-unit-id inline-block"><?php echo esc_attr( $label ); ?></span>
			<span class="ad-unit inline-block supplement"><?php echo esc_attr( aaih__common_str( $ad_unit ) ); ?></span>

		</div>
	</div>
	<input
		type="text"
		class="<?php echo esc_attr( $class[ $ad_unit ] ); ?>"
		name="<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $ad_nth );?>][<?php echo esc_attr( $item_name[ $ad_unit ] );?>]"
		value="<?php echo esc_attr( $item_value[ $ad_unit ] );?>"
		maxlength="<?php echo AAIH__DATA_AD_SLOT_MAXLENGTH; ?>"
	>
<?php
}

/**
 * ディスプレイ広告: data_ad_format
 *
 * - ディスプレイ広告のみのオプション。
 * - 広告ユニットが「ディスプレイ広告」の場合のみ表示。（その他は非表示）
 * （表示非表示は、tr の class に対して js でコントロール）

 * @param string $ad_nth	Ad1, Ad2, ...
 * @param array $settings	全ての設定値のキーと値の配列
 * @return void
 */
function aaih__ad_data_ad_format_select( $ad_nth , $settings ) {
	$ad__data_ad_format	= $settings[ $ad_nth ]['ad__data_ad_format'];	// auto（自動調整） / rectangle（長方形） / vertical（縦長） / horizontal（横長）

	$name_str__auto			= __( 'auto', AAIH__TEXT_DOMAIN );
	$name_str__rectangle	= __( 'rectangle', AAIH__TEXT_DOMAIN );
	$name_str__vertical		= __( 'vertical', AAIH__TEXT_DOMAIN );
	$name_str__horizontal	= __( 'horizontal', AAIH__TEXT_DOMAIN );

	$format_array		= array(
		'auto'			=> $name_str__auto,
		'rectangle'		=> $name_str__rectangle,
		'vertical'		=> $name_str__vertical,
		'horizontal'	=> $name_str__horizontal,
	);


	$supplement = __( 'Ad Shape can be selected only for display ads.', AAIH__TEXT_DOMAIN );	// 広告の形状は、ディスプレイ広告のみ選択できます。
	?>
	<select class="ad-data-ad-format" name="<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $ad_nth ); ?>][ad__data_ad_format]">
	<?php
		foreach ( $format_array as $key => $value ) {
		?>
			<option value="<?php echo esc_attr( $key ); ?>"<?php echo $ad__data_ad_format === $key ? ' selected ' : ''; ?>>
			<?php echo esc_attr( $value ); ?></option>
		<?php
		}
	?>
	</select>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
<?php
}

/**
 * 広告ユニット : ad_nth の コード 取得
 *
 * - Lazyload実行時：広告ユニットコード のみを返す。
 * - Lazyloadを実行しない場合：アドセンスコード + 広告ユニットコード を返す
 * - pub_id, ad__unit_id の設定がない場合は 空文字 を返す
 *
 * @param string $ad_nth	Ad1, Ad2, ...
 * @param array $settings	全ての設定値のキーと値の配列
 * @return void | string
 * - 'show'	アドセンスコード + 広告ユニットコード
 * - 'get'	Lazyload実行時：広告ユニットコード / Lazyloadを実行しない場合：アドセンスコード + 広告ユニットコード
 */
function aaih__get_ad_code_of_nth ( $ad_nth , $settings ) {

	// pub_id、ad__unit_id とも入力がある場合 'OK' を返す。
	// どちらか、または両方入力が無い場合は NG
	if( 'NG' === aaih__adsense_unit_code__validation ( $ad_nth , $settings ) ){
		return '';
	}

	// Lazy Load が実行されない場合は、アドセンスコードを含める
	// アドセンスコードには（アドセンスからコピペして来た時と同様に）改行を付けて返す
	if( 'no' === aaih__lazyload_execute_check( $settings ) ) {
		$code = aaih__adsense_code ( 'get' , $settings ) . "\n";
	}
	else {
		$code = '';
	}

	// ユニットコードの取得
	$code .= aaih__get_adsense_unit_code ( $ad_nth , $settings );

	return $code;

}

/**
 * 広告コードの表示
 *
 * - Adxx に対する 記事内広告、ディスプレイ広告、Multiplex 広告のコードをすべて echo する
 * - 選択されている広告ユニットは class に active を指定して、実際に表示
 * - 選択されていない広告ユニットは class に passive を指定して非表示。
 *
 * @param string $ad_nth 	Ad1, Ad2, ...
 * @param [type] $settings	設定値全体の配列
 * @return void
 */
function aaih__adsense_unit_code__show_all ( $ad_nth , $settings ) {
	$ad_unit = $settings[ $ad_nth ]['ad_unit_select'];	// 広告ユニットの種類（ display / in_article / multiplex ）

	$show_code_class	= array(
		'in_article'	=> 'passive' ,
		'display' 		=> 'passive' ,
		'multiplex' 	=> 'passive',
	);
	$show_code_class[ $ad_unit ] = 'active';

	// ディスプレイ広告の表示
	echo '<div class="show-code-all display ' . esc_attr( $show_code_class[ 'display' ] ) . '">';
	aaih__adsense_code ( 'show' , $settings );				// アドセンスコードの表示
	aaih__adsense_unit_code__display_ad ( $ad_nth , 'show' , $settings );
	echo '</div>';

	// 記事内広告の表示
	echo '<div class="show-code-all in-article ' . esc_attr( $show_code_class[ 'in_article' ] ) . '">';
	aaih__adsense_code ( 'show' , $settings );				// アドセンスコードの表示
	aaih__adsense_unit_code__in_article_ad ( $ad_nth , 'show' , $settings );
	echo '</div>';

	// Multiplex 広告の表示
	echo '<div class="show-code-all multiplex ' . esc_attr( $show_code_class[ 'multiplex' ] ) . '">';
	aaih__adsense_code ( 'show' , $settings );				// アドセンスコードの表示
	aaih__adsense_unit_code__multiplex_ad ( $ad_nth , 'show' , $settings );
	echo '</div>';
}

/**
 * 広告上下のマージン入力
 *
 * デフォルトは上下共通。
 * 高度な設定中の設定により、上下別々に設定もできる。
 *
 * 文字数カウントの単位は高度な設定中で em / px を選択できることから、
 * その設定を見て表示切り替えを行う。
 * 選択されてない単位の値は hidden で保持する。
 *
 * @param string $ad_nth	Ad1, Ad2, ...
 * @param array $settings	全ての設定値のキーと値の配列
 * @return void
 */
function aaih__ad_code_input_margin( $ad_nth , $settings ) {
	$updown_margin_separate_onoff	= $settings['updown_margin_separate_onoff'];	// 広告の上下余白を上側、下側個別に設定するか
	$space_unit						= $settings['space_unit']; 						// スペースの単位

	$ad_code_updown_margin_em		= $settings[ $ad_nth ]['updown_margin_em'];			// 上下マージン：広告の上下のマージン（em）
	$ad_code_updown_margin_px		= $settings[ $ad_nth ]['updown_margin_px'];			// 上下マージン：広告の上下のマージン（px）
	$ad_code_updown_margin_down_em	= $settings[ $ad_nth ]['updown_margin_down_em'];	// 上下マージン：広告の下のマージン（em）上下個別に設定する場合
	$ad_code_updown_margin_down_px	= $settings[ $ad_nth ]['updown_margin_down_px'];	// 上下マージン：広告の下のマージン（px）上下個別に設定する場合

	// 上下の余白 em/px 切り替え
	// 上下個別の場合
	if ( 'em' === $space_unit ) {
		$name			= 'updown_margin_em';
		$name_down		= 'updown_margin_down_em';
		$value			= $ad_code_updown_margin_em;
		$value_down		= $ad_code_updown_margin_down_em;
		$step			= AAIH__AD_CODE_UPDOWN__STEP_EM;
		$min			= AAIH__AD_CODE_UPDOWN__MARGIN_EM_MIN;
		$max			= AAIH__AD_CODE_UPDOWN__MARGIN_EM_MAX;

		$unit			= __( 'chars (em)' , AAIH__TEXT_DOMAIN ); // 文字分（em）

		// px の値を保持するために hidden で用意
		$hidden__name		= 'updown_margin_px';
		$hidden__value		= $ad_code_updown_margin_px;
		$hidden__name_down	= 'updown_margin_down_px';
		$hidden__value_down	= $ad_code_updown_margin_down_px;
	}
	else {	//px
		$name			= 'updown_margin_px';
		$name_down		= 'updown_margin_down_px';
		$value			= $ad_code_updown_margin_px;
		$value_down		= $ad_code_updown_margin_down_px;
		$step			= AAIH__AD_CODE_UPDOWN__STEP_PX;
		$min			= AAIH__AD_CODE_UPDOWN__MARGIN_PX_MIN;
		$max			= AAIH__AD_CODE_UPDOWN__MARGIN_PX_MAX;

		$unit			= __( ' dots (px)' , AAIH__TEXT_DOMAIN ); // ドット（px）

		// em の値を保持するために hidden で用意
		$hidden__name		= 'updown_margin_em';
		$hidden__value		= $ad_code_updown_margin_em;
		$hidden__name_down	= 'updown_margin_down_em';
		$hidden__value_down	= $ad_code_updown_margin_down_em;
	}

	// 上下余白を個別に設定する場合
	if ( 'on' === $updown_margin_separate_onoff ) {
		$prefix			= __( 'Top ' , AAIH__TEXT_DOMAIN ) ; // 上側
		$prefix_down 	= __( 'Bottom ' , AAIH__TEXT_DOMAIN ) ; // 下側
		$type_down		= 'number';	// input type
		$unit_down		= $unit;
		$supplement		= '';
	}else {
	// 上下余白：上下共通の場合
		$prefix			= '';	// 上側 の表示なし
		$prefix_down 	= '';	// 下側 の表示なし
		$type_down		= 'hidden';	// 共通の場合: 下側余白は hidden で値を保持
		$unit_down		= '';		// 共通の場合: 下側に対する 単位の表示はなし
		$supplement		= __( 'Top & bottom margins outside of the ad' , AAIH__TEXT_DOMAIN ) ;
		// （広告外側の上下余白（スペース））
	}
?>
	<span class="inline-block">
		<?php echo esc_attr( $prefix ); // 上側 ?>
		<input
			type='number'
			step="<?php echo esc_attr( $step ); // 絶対値 int | float ?>"
			class="margin"
			name='<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $ad_nth );?>][<?php echo esc_attr( $name ); ?>]'
			value="<?php echo esc_attr( $value ); // 絶対値 int | float ?>"
			min="<?php echo esc_attr( $min ); ?>"
			max="<?php echo esc_attr( $max ); ?>"> <?php echo esc_attr( $unit ); ?>
	</span>
	<input
		type='hidden'
		name='<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $ad_nth );?>][<?php echo esc_attr( $hidden__name ); ?>]'
		value="<?php echo esc_attr( $hidden__value ); // em なら px を、px なら em を hidden で保持 int | float ?>">
	<?php echo 'hidden' !== $type_down ? ' / ' : ''; // 上下個別に設定の場合は セパレータ「 / 」を表示 ?>
	<span class="inline-block">
		<span class="prefix_down"><?php echo esc_attr( $prefix_down ); // 下側 ?></span>
		<input
			type='<?php echo esc_attr( $type_down ); // 共通の場合は hidden として input フィールドは表示しない ?>'
			step="<?php echo esc_attr( $step ); // int || float ?>"
			class="margin"
			name='<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $ad_nth );?>][<?php echo esc_attr( $name_down ); ?>]'
			value="<?php echo esc_attr( $value_down ); // int | float ?>"
			min="<?php echo esc_attr( $min ); ?>"
			max="<?php echo esc_attr( $max ); ?>"> <?php echo esc_attr( $unit_down ); ?>
	</span>
	<input
		type='hidden'
		name='<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $ad_nth );?>][<?php echo esc_attr( $hidden__name_down ); ?>]'
		value="<?php echo esc_attr( $hidden__value_down ); // em なら px を、px なら em を hidden で保持 ?>">
		<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
<?php
}

/**
 * 広告のセンタリング on/off
 *
 * @param string $ad_nth	Ad1, Ad2, ...
 * @param array $settings	全ての設定値のキーと値の配列
 * @return void
 */
function aaih__ad_code_centering_on_off( $ad_nth , $settings ) {
	$ad_code_centering	= $settings[ $ad_nth ]['centering']; // センタリング on/off
?>
	<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $ad_nth );?>][centering]" value="off">
	<label>
		<input
			type='checkbox'
			class="centering"
			name='<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $ad_nth );?>][centering]'
			<?php checked( $ad_code_centering, 'on' ); ?>
			value="on"><?php _e( 'Center the ad horizontally.' , AAIH__TEXT_DOMAIN ); // 左右を中央寄せにする。 ?>
	</label>
<?php
}

/**
 * 広告ラベルと広告との間のスペース
 *
 * スペースの単位は高度な設定中で em / px を選択できることから、
 * その設定を見て表示切り替えを行う。
 * 選択されてない単位の値は hidden で保持する。
 *
 * @param string $ad_nth	Ad1, Ad2, ...
 * @param array $settings	全ての設定値のキーと値の配列
 * @return void
 */
function aaih__ad_code_label_input( $ad_nth , $settings ) {
	$ad_code_label	 				= $settings[ $ad_nth ]['label'];				// 広告ラベル
	$ad_code_label_space_em			= $settings[ $ad_nth ]['label_space_em'];		// 広告ラベル：広告とのスペース（em）
	$ad_code_label_space_px			= $settings[ $ad_nth ]['label_space_px'];		// 広告ラベル：広告とのスペース（ex）
	$space_unit						= $settings['space_unit'];	// スペースの単位 em / px

	// ラベル入力
?>
	<input
		type='text'
		class="ad-label-input"
		name='<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $ad_nth );?>][label]'
		value="<?php echo esc_attr( $ad_code_label ); ?>"
		maxlength="<?php echo AAIH__AD_CODE_LABEL_MAXLENGTH; ?>"
		placeholder="<?php _e( 'e.g. Sponsored Links' , AAIH__TEXT_DOMAIN ); // 例：スポンサーリンク ?>">
	<p class="supplement"><?php _e( 'Enter if you want to display a label above the ad.' , AAIH__TEXT_DOMAIN ); // （広告上にラベルを表示したい場合に入力）?></p>

	<?php
	// スペース入力

	// スペース em/px 切り替え
	if ( 'em' === $space_unit ) {
		$name			= 'label_space_em';
		$value			= $ad_code_label_space_em;
		$step			= AAIH__AD_CODE_LABEL__STEP_EM;
		$min			= AAIH__AD_CODE_LABEL__SPACE_EM_MIN;
		$max			= AAIH__AD_CODE_LABEL__SPACE_EM_MAX;

		$unit			= __( 'chars (em)' , AAIH__TEXT_DOMAIN );

		$hidden__name	= 'label_space_px';
		$hidden__value	= $ad_code_label_space_px;
	}
	else {	//px
		$name			= 'label_space_px';
		$value			= $ad_code_label_space_px;
		$step			= AAIH__AD_CODE_LABEL__STEP_PX;
		$min			= AAIH__AD_CODE_LABEL__SPACE_PX_MIN;
		$max			= AAIH__AD_CODE_LABEL__SPACE_PX_MAX;

		$unit			= __( ' dots (px)' , AAIH__TEXT_DOMAIN );

		$hidden__name	= 'label_space_em';
		$hidden__value	= $ad_code_label_space_em;
	}
	?>
	<div class="space">
			<input
				type='number'
				class="margin"
				name='<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $ad_nth );?>][<?php echo esc_attr( $name ); ?>]'
				value="<?php echo esc_attr( $value ); // int | float ?>"
				min="<?php echo esc_attr( $min ); ?>"
				max="<?php echo esc_attr( $max ); ?>"
				step="<?php echo esc_attr( $step ); // int | float ?>"> <?php echo esc_attr( $unit ); ?>
			<input
				type='hidden'
				name='<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $ad_nth );?>][<?php echo esc_attr( $hidden__name ); ?>]'
				value="<?php echo esc_attr( $hidden__value ); // int | float ?>">
		<p class="supplement"><?php _e( 'Space between ad label and ad.' , AAIH__TEXT_DOMAIN ); // （広告ラベルと広告の間のスペース）?></p>
	</div>
	<?php
}

/**
 * メモ（広告名）入力
 *
 * @param string $ad_nth	Ad1, Ad2, ...
 * @param array $settings	全ての設定値のキーと値の配列
 * @return void
 */
function aaih__ad_code_memo_input( $ad_nth , $settings ) {
	$ad_code_name	= $settings[ $ad_nth ]['name'];	// メモ（広告名）
	$placeholder 	= __( 'e.g. easy-to-distinguish name' , AAIH__TEXT_DOMAIN ); // （区別の付けやすい名前など）
?>
	<input type='text'
			class="name"
			name='<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $ad_nth );?>][name]'
			value="<?php echo esc_attr( $ad_code_name ); ?>"
			maxlength="<?php echo AAIH__AD_CODE_NAME_MAXLENGTH; ?>"
			placeholder="<?php echo esc_attr( $placeholder ); ?>">
	<p class="supplement"><?php _e( 'For internal used only in this settings.' , AAIH__TEXT_DOMAIN ); // （設定内のみで使われる内部管理用）?></p>
<?php
}

/**
 * ショートコード表示
 *
 * 表示している広告コード（Ad1, Ad2, ...）に対するショートコードを表示する
 *
 * @param string $ad_nth	Ad1, Ad2, ...
 * @return void
 */
function aaih__ad_code_shortcode_display( $ad_nth ) {
	$shortcode_key		= 'ad';
	$shortcode_value	= $ad_nth;
	$supplement			= __( 'Used to manually insert an ad code in an article.' , AAIH__TEXT_DOMAIN ); // （記事内に手動で広告を入れる場合に使う）

	$shortcode	= aaih__shortcode_create( $shortcode_key, $shortcode_value );
	$copy_class	= 'shortcode-' . $shortcode_key . '-' . $shortcode_value ;

	$msg_copy			= __( 'Copy' , AAIH__TEXT_DOMAIN);
	$msg_copied			= __( 'copied' , AAIH__TEXT_DOMAIN); // をコピーしました
	$msg_copied_error	= __( 'It seems like copy did not work well.' , AAIH__TEXT_DOMAIN); // をコピーしました

	// 改行は「"\n"」というようにダブルクォートで括る必要あり。
	//（シングルクォートにするとそのまま文字列として表示されてしまうので注意）

	echo '<pre class="shortcode ' . esc_attr( $copy_class ) . '">' . esc_attr( $shortcode ) . '</pre>' . "\n";
	echo '<div class="copybtn" title="' . esc_attr( $copy_class ) . '">' . esc_attr( $msg_copy ) . '</div>' . "\n";
	echo '<p class="supplement">' . esc_attr( $supplement ) . '</p>' . "\n";
	echo '<div class="copymsg success ' . esc_attr( $copy_class ) . '">' . esc_attr( $shortcode ) . ' ' . esc_attr( $msg_copied ) . '</div>' . "\n";
	echo '<div class="copymsg error ' . esc_attr( $copy_class ) . '">' . esc_attr( $msg_copied_error ) . '</div>' . "\n";
}


/* -----------------------------------
 * 文字数カウント関連
 * ----------------------------------- */

/**
 * コールバック）文字数カウントの単位
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__character_width_unit( $args ) {
	$character_width_unit	= $args['settings']['character_width_unit'];
	$supplement1	= __( 'Select the unit to count the number of characters: full-width or half-width.', AAIH__TEXT_DOMAIN );	// カウントする文字数の単位は全角か半角かの選択
	$supplement2	= __( 'Half-width: so-called 1-byte characters ( normal alphanumeric characters )', AAIH__TEXT_DOMAIN );	// 全角：日本語など（いわゆる2バイトコードの文字）
	$supplement3	= __( 'Full-width: so-called 2-byte characters ( such as Japanese )', AAIH__TEXT_DOMAIN );					// 半角：（半角の）英数字（いわゆる1バイトコードの文字）
	?>
	<label><input type="radio" name="<?php echo AAIH__SETTINGS; ?>[character_width_unit]" value="full" <?php checked( $character_width_unit, 'full' ); ?>><?php _e( 'full-width', AAIH__TEXT_DOMAIN ); ?></label>
	<label><input type="radio" name="<?php echo AAIH__SETTINGS; ?>[character_width_unit]" value="half" <?php checked( $character_width_unit, 'half' ); ?>><?php _e( 'half-width', AAIH__TEXT_DOMAIN ); ?></label>
	<p class="supplement"><?php echo esc_attr( $supplement1 ) . '<br /><br />' .esc_attr( $supplement2 ) . '<br />' . esc_attr( $supplement3 ); ?></p>
<?php
}

/* -----------------------------------
 * 広告コード設定関連
 * ----------------------------------- */
/**
 * コールバック）上下の余白を個別設定（on/off）
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__updown_margin_separate_onoff( $args ) {
	$updown_margin_separate_onoff = $args['settings']['updown_margin_separate_onoff'];
	$supplement	= __( 'By default, the top and bottom margins of the ad are set in common.', AAIH__TEXT_DOMAIN ); // 初期状態では、広告の上下余白を共通で設定する
	?>
	<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[updown_margin_separate_onoff]" value="off">
	<label>
		<input type='checkbox' name='<?php echo AAIH__SETTINGS; ?>[updown_margin_separate_onoff]'<?php checked( $updown_margin_separate_onoff, 'on' ); ?> value='on'>
		<?php _e( 'Set the top and bottom margins of the ad individually.', AAIH__TEXT_DOMAIN ); //広告の上下余白を個別に設定する ?>
	</label>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
<?php
}

/**
 * コールバック）スペースの単位
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__space_unit( $args ) {
	$space_unit	= $args['settings']['space_unit'];
	$supplement	= __( 'Select the unit of space in ad code settings.', AAIH__TEXT_DOMAIN ); // 広告コードの設定におけるスペースの単位を選択。
	?>
	<label><input type="radio" name="<?php echo AAIH__SETTINGS; ?>[space_unit]" value="em" <?php checked( $space_unit, 'em' ); ?> >
	<?php _e( 'em (character height)', AAIH__TEXT_DOMAIN ); // em（文字の高さ）?></label>
	<label><input type="radio" name="<?php echo AAIH__SETTINGS; ?>[space_unit]" value="px" <?php checked( $space_unit, 'px' ); ?> >
	<?php _e( 'px (dot)', AAIH__TEXT_DOMAIN ); // px（ドット）?></label>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
<?php
}

/**
 * コールバック）広告ラベルの入力欄の表示
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__label_input_onoff( $args ) {
	$label_input_onoff 	= $args['settings']['label_input_onoff'];
	$supplement			= __( 'Display an input field for labeling ad.', AAIH__TEXT_DOMAIN ); //広告上にラベルを付けるための入力欄を表示する。
	?>
	<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[label_input_onoff]" value="off">
	<label>
		<input type='checkbox' name='<?php echo AAIH__SETTINGS; ?>[label_input_onoff]' <?php checked( $label_input_onoff, 'on' ); ?> value='on'>
		<?php _e( 'Display ad label input field', AAIH__TEXT_DOMAIN ); // 広告ラベルの入力欄を表示する ?>
	</label>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
<?php
}

/**
 * コールバック）メモ入力の表示
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__memo_input_onoff( $args ) {
	$memo_input_onoff 	= $args['settings']['memo_input_onoff'];
	$supplement			= __( 'Display a memo field to name the ad code and make it easy to identify.', AAIH__TEXT_DOMAIN );
							// 設定する広告コードに名称付けして分かりやすくするためのメモ欄を表示する。
	?>
	<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[memo_input_onoff]" value="off">
	<label>
		<input type='checkbox' name='<?php echo AAIH__SETTINGS; ?>[memo_input_onoff]' <?php checked( $memo_input_onoff, 'on' ); ?> value='on'>
		<?php _e( 'Display memo field', AAIH__TEXT_DOMAIN ); // メモ欄を表示する ?>
	</label>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
<?php
}


/* -----------------------------------
 * 広告置き換え対応
 * ----------------------------------- */
/**
 * コールバック）広告置き換え（on/off & 文字列）
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__ad_replace( $args ) {
	$settings			= $args['settings'];	// 設定値全体

	// 広告置き換え on/off checkbox 表示
	aaih__ad_replace__enable_checkbox( $settings );
	// 広告置き換え ショートコード文字列入力と広告指定
	aaih__ad_replace__input_and_ad( $settings );
	// 広告置き換え オプション表示
	aaih__ad_replace__options( $settings );
}


/**
 * 広告置き換え on/off checkbox 表示
 *
 * @param array $settings	全ての設定値のキーと値の配列
 * @return void
 */
function aaih__ad_replace__enable_checkbox( $settings ) {
	$ad_replace_onoff 	= $settings['ad_replace_onoff'];
	?>
	<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[ad_replace_onoff]" value="off">
	<label>
		<input type='checkbox' class='ad-replace-onoff-checkbox' name='<?php echo AAIH__SETTINGS; ?>[ad_replace_onoff]' <?php checked( $ad_replace_onoff, 'on' ); ?> value='on'>
		<?php _e( 'Enable shortcode replacement', AAIH__TEXT_DOMAIN ); // ショートコードの置き換えを有効にする ?>
	</label>
	<?php
}

/**
 * 広告置き換え ショートコード文字列入力と広告指定
 *
 * @param array $settings	全ての設定値のキーと値の配列
 * @return void
 */
function aaih__ad_replace__input_and_ad( $settings ) {
	// 'Replace 表示のための 言語設定取得 '
	$language 	= aaih__get_item( 'language' );

	// 未入力時の表示
	$placeholder = __( '( shortcode )', AAIH__TEXT_DOMAIN ) ;

	?>
	<div class="replace-all">
	<?php
	// ショートコード入力
	for( $i = 1 ; $i <= AAIH__SHORTCODE_REPLACE_MAX_NUM ; $i ++ ) {
		$shortcode_replace_num	= 'shortcode_replace' . $i;

		// 置き換えショートコードの設定取得（ shortcode_replace1, shortcode_replace2, ... ）
		$shortcode_replace		= $settings[ $shortcode_replace_num ];
		$replace_code			= $shortcode_replace['replace_code'];		// 置き換えたいショートコード文字列
		$replace_ad_nth			= $shortcode_replace['replace_ad_select'];	// 置き換えるAd num（Adxx）

		$selectbox__attr_name	= AAIH__SETTINGS . '[' . $shortcode_replace_num . '][' . 'replace_ad_select' . ']';	// セレクトボックスの name 属性
	?>
		<div class="replace <?php echo esc_attr( $language ); ?>">
			<div class="shortcode">
				<span class="head"><?php _e( 'Replace', AAIH__TEXT_DOMAIN ); ?></span>
				<input
					type='text'
					name='<?php echo AAIH__SETTINGS; ?>[<?php echo esc_attr( $shortcode_replace_num ); ?>][replace_code]'
					value="<?php echo esc_attr( $replace_code ); ?>"
					maxlength="<?php echo AAIH__SHORTCODE_REPLACE_MAXLENGTH; ?>"
					placeholder="<?php echo esc_attr( $placeholder ); ?>">
					<?php
						_e( 'with ', AAIH__TEXT_DOMAIN ); // を
						// セレクトボックス表示
						aaih__ad_selectbox( $selectbox__attr_name , $replace_ad_nth , $settings , 'ad_replace' );
					?>
				<span class="inline-block period"><?php _e( '.', AAIH__TEXT_DOMAIN ); // で置き換える ?></span>
			</div>
		</div>
	<?php
	}
	?>
	</div><!-- // replace-all -->
	<?php
	$supplement	=
	__( 'If the shortcode entered here is found in articles, it is replaced with the selected ad code.' , AAIH__TEXT_DOMAIN );	// 記事中にここで入力したショートコードが見つかれば、指定の広告コードで置き換える。
	?>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
	<input type="hidden" class="shortcode-replace-max-num" value="<?php echo AAIH__SHORTCODE_REPLACE_MAX_NUM; // jis に渡す用 ?>">
<?php
}

/**
 * 広告置き換え オプション表示
 *
 * ショートコードの広告置き換えが有効で実際ショートコードの置き換えがある場合、以下の設定を無効にするかのon/off設定を表示する。
 *
 * - 先頭のHタグ前Ad 設定
 * - Hタグ前Ad 設定
 * - 記事下Ad 設定
 *
 * ※）この設定は 一般設定より優先する
 *
 * @param array $settings	全ての設定値のキーと値の配列
 * @return void
 */
function aaih__ad_replace__options( $settings ) {
	$title_main	= __( 'Options', AAIH__TEXT_DOMAIN );	// オプション
	$supplement	= __( 'This settings take precedence over the general settings if there is a shortcode replacement.', AAIH__TEXT_DOMAIN ); // ショートコードの置き換えがある場合、この設定を一般設定より優先する。
?>
	<div class="table-title"><?php echo esc_attr( $title_main ); ?></div>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
	<table class="ad-replace options narrow"><tbody>
		<tr class="option">
			<td>
				<ul>
					<li><?php aaih__ad_replace__options__first_h_tag_ad_onoff( $settings ); ?></li>
					<li><?php aaih__ad_replace__options__h_tag_ad_onoff( $settings ); ?></li>
					<li><?php aaih__ad_replace__options__after_content_ad_onoff( $settings ); ?></li>
				</ul>
			</td>
		</tr>
	</tbody></table>
<?php
}

/**
 * 広告置き換え オプション : 先頭のHタグ前Ad の無効 設定
 *
 * 広告置き換えが有効の場合で、置き換え対象のショートコードがある場合、この設定が優先される。
 *
 * @param array $settings	全ての設定値のキーと値の配列
 * @return void
 */
function aaih__ad_replace__options__first_h_tag_ad_onoff( $settings ) {
	$ad_replace___first_h_tag_ad__off	= $settings['ad_replace___first_h_tag_ad__off'];
?>
	<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[ad_replace___first_h_tag_ad__off]" value="off">
	<label>
		<input type='checkbox' name='<?php echo AAIH__SETTINGS; ?>[ad_replace___first_h_tag_ad__off]' <?php checked( $ad_replace___first_h_tag_ad__off, 'on' ); ?> value='on'>
		<?php _e( 'Disable Ad before the first H tag setting', AAIH__TEXT_DOMAIN ); // 先頭のHタグ前Ad設定を無効にする ?>
	</label>
<?php
}

/**
 * 広告置き換え オプション : Hタグ前Ad の無効 設定
 *
 * 広告置き換えが有効の場合で、置き換え対象のショートコードがある場合、この設定が優先される。
 *
 * @param array $settings	全ての設定値のキーと値の配列
 * @return void
 */
function aaih__ad_replace__options__h_tag_ad_onoff( $settings ) {
	$ad_replace___h_tag_ad__off			= $settings['ad_replace___h_tag_ad__off'];
?>
	<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[ad_replace___h_tag_ad__off]" value="off">
	<label>
		<input type='checkbox' name='<?php echo AAIH__SETTINGS; ?>[ad_replace___h_tag_ad__off]' <?php checked( $ad_replace___h_tag_ad__off, 'on' ); ?> value='on'>
		<?php _e( 'Disable Ad before H tags setting', AAIH__TEXT_DOMAIN ); //Hタグ前Ad設定を無効にする ?>
	</label>
<?php
}

/**
 * 広告置き換え オプション : 記事の下Ad の無効 設定
 *
 * 広告置き換えが有効の場合で、置き換え対象のショートコードがある場合、この設定が優先される。
 *
 * @param array $settings	全ての設定値のキーと値の配列
 * @return void
 */
function aaih__ad_replace__options__after_content_ad_onoff( $settings ) {
	$ad_replace___after_content_ad__off	= $settings['ad_replace___after_content_ad__off'];
	?>
	<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[ad_replace___after_content_ad__off]" value="off">
	<label>
		<input type='checkbox' name='<?php echo AAIH__SETTINGS; ?>[ad_replace___after_content_ad__off]' <?php checked( $ad_replace___after_content_ad__off, 'on' ); ?> value='on'>
		<?php _e( 'Disable Ad end of article setting', AAIH__TEXT_DOMAIN ); // 記事の下Ad設定を無効にする
		?>
	</label>
<?php
}

/* -----------------------------------
 * グーグルアドセンスの遅延読込み（Lazy load）対応
 * ----------------------------------- */
/**
 * コールバック）アドセンスの遅延表示（LazyLoad）
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__adsense_lazy_load( $args ) {
	$settings	= $args['settings'];	// 設定値全体

	// 遅延読込み（lazy load）on/off checkbox 表示
	aaih__lazyload_enable_checkbox( $settings );
	// 遅延読込み（lazy load）オプション表示
	aaih__lazyload_options( $settings );
}

/**
 * 遅延読込み（lazy load）on/off checkbox 表示
 *
 * @param array $settings 設定全体の配列
 * @return void
 */
function aaih__lazyload_enable_checkbox( $settings ) {
	$item_label	= __( 'AdSense Lazy load', AAIH__TEXT_DOMAIN );

	$adsense_lazy_load_onoff 	= $settings['adsense_lazy_load_onoff'];		// 遅延読込み on/off
	$supplement1 =__( 'After an article is displayed, ads will be loaded by user actions such as scrolling and screen touch or after specified number of seconds of Auto load option passed.', AAIH__TEXT_DOMAIN );
	$supplement2 = __( 'Lazy load will not work if the code of Google AdSense Auto Ads is inserted in the header.', AAIH__TEXT_DOMAIN );
	// 記事が表示された後、スクロールや画面タッチなどが行われるか 自動読込みオプションで設定した秒数経過後、広告が読み込まれるようになります。
	// アドセンス自動広告用のコードがヘッダに挿入されている場合には遅延表示にはなりません。
	?>
	<table class="options narrow lazyload onoff"><tbody>
		<tr>
			<th class="label"><?php echo esc_attr( $item_label ); ?></td>
			<td colspan="2">
				<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[adsense_lazy_load_onoff]" value="off">
				<label>
					<input
						type='checkbox'
						name='<?php echo AAIH__SETTINGS; ?>[adsense_lazy_load_onoff]'
						<?php checked( $adsense_lazy_load_onoff, 'on' ); ?>
						value='on'><?php _e( 'Enable', AAIH__TEXT_DOMAIN ); // 有効にする ?>
				</label>
			</td>
		</tr>
		<tr>
			<th></th>
			<td class="supplement">
				<p class="supplement"><?php echo esc_attr( $supplement1 ); ?></p>
				<p class="supplement"> ( <?php echo esc_attr( $supplement2 ); ?> )</p>
			</td>
		</tr>
	</tbody></table>
<?php
}

/**
 * 遅延読込み（lazy load）オプション表示
 *
 * @param array $settings 設定全体の配列
 * @return void
 */
function aaih__lazyload_options( $settings ) {
	$title_pc			= __( 'Desktop', AAIH__TEXT_DOMAIN );	// PC表示を除外する設定
	$title_auto_load	= __( 'Auto load', AAIH__TEXT_DOMAIN );	// 自動で読み込むまでの時間を変更
?>
	<table class="lazyload options advance narrow"><tbody>
		<tr>
			<th></th>
			<td class="label"><?php echo esc_attr( $title_pc ); ?></td>
			<td><?php aaih__lazyload_options_pc( $settings );?></td>
		</tr>
		<tr>
			<th></th>
			<td class="label"><?php echo esc_attr( $title_auto_load ); ?></td>
			<td><?php aaih__lazyload_options_auto_load( $settings ); ?></td>
		</tr>
	</tbody></table>
<?php
}

/**
 * 遅延読込み（lazy load）オプション : PC表示を除外する設定
 *
 * @param array $settings 設定全体の配列
 * @return void
 */
function aaih__lazyload_options_pc( $settings ) {
	$adsense_lazy_load_no_pc_onoff 	= $settings['adsense_lazy_load_no_pc_onoff'];
	?>
	<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[adsense_lazy_load_no_pc_onoff]" value="off">
	<label>
		<input type='checkbox' name='<?php echo AAIH__SETTINGS; ?>[adsense_lazy_load_no_pc_onoff]'<?php checked( $adsense_lazy_load_no_pc_onoff, 'on' ); ?> value='on'>
		<?php _e( 'Disable desktop', AAIH__TEXT_DOMAIN ); // PCは対象外 ?>
	</label>
	<?php
}

/**
 * 遅延読込み（lazy load）オプション : 自動で読み込むまでの時間を変更
 *
 * @param array $settings 設定全体の配列
 * @return void
 */
function aaih__lazyload_options_auto_load( $settings ) {
	// 現在の設定情報を取得
	$adsense_lazy_load_second 	= $settings['adsense_lazy_load_second'];	// 自動読込みまでの秒数指定
	$second_seconds				= _n( 'second' , 'seconds' , $adsense_lazy_load_second , AAIH__TEXT_DOMAIN );
	?>
	<select name="<?php echo AAIH__SETTINGS; ?>[adsense_lazy_load_second]">
	<?php
		for( $i = AAIH__AD_LAZY_LOAD_AUTO_MIN ; $i <= AAIH__AD_LAZY_LOAD_AUTO_MAX ; $i ++ ) {
			if ( 0 === $i ) {
				$label	= __( 'non use', AAIH__TEXT_DOMAIN ); //使用しない
			}
			else {
				$second_seconds	= _n( 'second' , 'seconds' , $i , AAIH__TEXT_DOMAIN );
				$label = $i . ' ' . $second_seconds;
			}
		?>
		<option value="<?php echo absint( $i );?>"<?php echo $adsense_lazy_load_second === $i ? ' selected ' : ''; ?>>
		<?php echo esc_attr( $label ); ?></option>
	<?php
	}
	?>
	</select>
	<?php
}


/* -----------------------------------
 * グーグルアドセンスの自動広告
 * ----------------------------------- */
/**
 * コールバック）アドセンス自動広告
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__adsense_auto_ads( $args ) {
	$settings	= $args['settings'];	// 設定値全体

	// アドセンス自動広告コードを挿入の on/off 設定
	aaih__adsense_auto_ad_on_off( $settings );
	// アドセンス自動広告コードのコードの表示
	aaih__adsense_auto_ad_code_display( $settings );
	// アドセンス自動広告コードの対象外の設定
	aaih__adsense_auto_ad_post_page( $settings );
}

/**
 * アドセンス自動広告コードを挿入の on/off 設定
 *
 * @param array $settings	: array $settings の配列
 * @return void
 */
function aaih__adsense_auto_ad_on_off( $settings ) {
	$item_label	= __( 'AdSense Auto Ads', AAIH__TEXT_DOMAIN );

	// 自動広告コードを挿入する/しない（ on/off）
	$adsense_auto_ads_onoff	= $settings['adsense_auto_ads_onoff'];

	$supplement1	= __( 'Insert the AdSense code ( Auto ads code ) into the header ( between the &lt;head&gt; and &lt;/head&gt; tags) .', AAIH__TEXT_DOMAIN ) ;
	$supplement2	= __( 'The same code to put in the header of your site to apply for Google AdSense.', AAIH__TEXT_DOMAIN ) ;
	// 以下のコードを ヘッダー（ <head> と </head> の間）に挿入する
	?>
	<table class="options narrow adsense-auto-ad onoff"><tbody>
		<tr>
			<th class="label"><?php echo esc_attr( $item_label ); ?></td>
			<td>
				<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[adsense_auto_ads_onoff]" value="off">
				<label>
					<input
						type='checkbox'
						name='<?php echo AAIH__SETTINGS; ?>[adsense_auto_ads_onoff]'
						<?php checked( $adsense_auto_ads_onoff, 'on' ); ?>
						value='on'><?php _e( 'Enable', AAIH__TEXT_DOMAIN ); // 有効にする ?>
				</label>
			</td>
		</tr>
		<tr>
			<th></th>
			<td class="supplement">
				<p class="supplement"><?php echo esc_attr( $supplement1 ); ?><br /><?php echo esc_attr( $supplement2 ); ?><br /></p>
			</td>
		</tr>
	</tbody></table>
<?php
}

/**
 * アドセンス自動広告コードの対象外の設定
 *
 * アドセンス自動広告コード挿入に対して、post を対象外にする、page を対象外にする設定。
 * 両方対象外にした場合でも、記事の一覧表示などにはコードが挿入される。
 *
 * @param array $settings	: array $settings の配列
 * @return void
 */
function aaih__adsense_auto_ad_post_page( $settings ) {
	$adsense_auto_ads__post_off	= $settings['adsense_auto_ads__post_off'];	// 投稿は対象外にする（ on/off ）
	$adsense_auto_ads__page_off = $settings['adsense_auto_ads__page_off'];	// 固定ページは対象外にする（ on/off ）
?>
	<table class="adsense-auto-ad options narrow"><tbody>
		<tr>
			<th><?php _e( 'Option', AAIH__TEXT_DOMAIN); ?></th>
			<td>
				<label>
					<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[adsense_auto_ads__post_off]" value="off">
					<input type='checkbox' name='<?php echo AAIH__SETTINGS; ?>[adsense_auto_ads__post_off]' <?php checked( $adsense_auto_ads__post_off, 'on' ); ?> value='on'>
					<?php _e( 'Disable posts', AAIH__TEXT_DOMAIN ); // 投稿は対象外にする ?>
				</label>
				<label>
					<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[adsense_auto_ads__page_off]" value="off">
					<input type='checkbox' name='<?php echo AAIH__SETTINGS; ?>[adsense_auto_ads__page_off]' <?php checked( $adsense_auto_ads__page_off, 'on' ); ?> value='on'>
					<?php _e( 'Disable pages', AAIH__TEXT_DOMAIN ); // 固定ページは対象外にする ?>
				</label>
			</td>
		</tr>
	</tbody></table>
<?php
}

/**
 * アドセンス自動広告コードのパブリッシャーID入力とコード表示
 *
 * @param array $settings	: array $settings の配列
 * @return void
 */
function aaih__adsense_auto_ad_code_display( $settings ) {
	$pub_id__auto_ad 	= $settings['pub_id__auto_ad'];
	$class				= 'id pub-id';
	$title_main			= __( 'Code to insert', AAIH__TEXT_DOMAIN );	// 挿入コード
?>
	<div class="table-title"><?php echo esc_attr( $title_main ); ?></div>
	<table class="options adsense-auto-ad code-insert"><tbody>
		<tr>
			<th><?php _e( 'Publisher ID' , AAIH__TEXT_DOMAIN );?></th>
			<td><?php aaih__pub_id_input( $class, 'pub_id__auto_ad' , $pub_id__auto_ad ); ?></td>
		</tr>
		<tr class="caption top">
			<td colspan="2"><p class="supplement"><?php echo esc_attr( aaih__common_str( 'code_up_supplement' ) ); ?></p></td>
		</tr>
		<tr>
			<td colspan="2" class="show-code"><?php aaih__adsense_code( 'show', $settings ); ?></td>
		</tr>
		<?php
			if( '' === $settings['pub_id'] ) { ?>
			<tr class="caption bottom">
				<td colspan="2">
					<p class="supplement"><?php echo esc_attr( aaih__common_str( 'code_down_supplement' ) ); ?></p>
				</td>
			</tr>
			<?php
			}
		?>
	</tbody></table>
<?php
}


/* -----------------------------------
 * アナリィテイクス : コードのヘッダー挿入
 * ----------------------------------- */
/**
 * コールバック）アナリティクス コードの挿入
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__analytics_header_insert_code( $args ) {
	$settings	= $args['settings'];	// 設定値全体

	// ヘッダーコード挿入の on/off 設定
	aaih__analytics_code_insert_on_off( $settings );
	// グーグルアナリティクスの トラッキング ID / 測定 ID 入力 と　コードの表示
	aaih__analytics_code_display( $settings );

	// アナリティクスID の初期表示文字列 : jsに渡す用
	?>
	<input id="analytics-id-nothing-all" type="hidden" value="<?php echo AAIH__ANALYTICS_ID_NOTHING_ALL_STR;?>">
<?php
}

/**
 * アナリティクス：コード挿入のon/off
 *
 * @param array $settings	: array $settings の配列
 * @return void
 */
function aaih__analytics_code_insert_on_off( $settings ) {
	$item_label	= __( 'Analytics code insert', AAIH__TEXT_DOMAIN );

	$analytics_header_insert_code_onoff = $settings['analytics_header_insert_code_onoff'];
	$supplement		= __( 'Insert Google Analytics code into the header ( between the &lt;head&gt; and &lt;/head&gt; tags) .', AAIH__TEXT_DOMAIN ) ;
	// グーグルアナリティクスなどのコードをヘッダー（ <head> と </head> の間）に挿入する
?>
	<table class="options narrow analytics-code-insert onoff"><tbody>
		<tr>
			<th class="label"><?php echo esc_attr( $item_label ); ?></td>
			<td>
				<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[analytics_header_insert_code_onoff]" value="off">
				<label>
					<input
						type='checkbox'
						name='<?php echo AAIH__SETTINGS; ?>[analytics_header_insert_code_onoff]'
						<?php checked( $analytics_header_insert_code_onoff, 'on' ); ?>
						value='on'><?php _e( 'Enable', AAIH__TEXT_DOMAIN ); // 有効にする ?>
				</label>
			</td>
		</tr>
		<tr>
			<th></th>
			<td class="supplement">
				<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
			</td>
		</tr>
	</tbody></table>
<?php
}

/**
 * アナリティクス：ID 入力設定
 *
 * @param array $settings	: array $settings の配列
 * @return void
 */
function aaih__analytics_id_input( $settings ) {
	$analytics_id		= $settings['analytics_id']; 	// Tracking ID : UA-000000-2 or Measurement ID : XXXXXXX of G-XXXXXXX
	$supplement			= __( 'Input Tracking ID ( UA-xxxxx-xx ) or Measurement ID ( G-xxxxxxxx ) .' , AAIH__TEXT_DOMAIN );	// パブリッシャーID は「ca-pub-xxxxx」の「pub-xxxxxx」の部分。
?>
	<label>
		<input
			type='text'
			class='analytics-id'
			name='<?php echo AAIH__SETTINGS; ?>[analytics_id]'
			value='<?php echo '' === $analytics_id ? '' : esc_attr( $analytics_id ); ?>'
			maxlength='<?php echo AAIH__ANALYTICS_ID_MAXLENGTH; ?>'
		/>
	</label>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
<?php
}

/**
 * アナリティクス : ID設定と挿入コードの表示
 *
 * @param array $settings	: array $settings の配列
 * @return void
 */
function aaih__analytics_code_display( $settings ) {
	$title_main				= __( 'Code to insert', AAIH__TEXT_DOMAIN );	// 挿入コード
	$str_analytics_id		= __( 'Analytics ID'	, AAIH__TEXT_DOMAIN );
	$analytics_id_to_show	= aaih__get_analytics_id( $settings );
?>
	<div class="table-title"><?php echo esc_attr( $title_main ); ?></div>
	<table class="analytics options code-insert"><tbody>
		<tr class="tracking-id">
			<th><?php echo esc_attr( $str_analytics_id );?></th>
			<td><?php aaih__analytics_id_input( $settings ); ?></td>
		</tr>
		<tr class="caption top">
			<td colspan="2"><p class="supplement"><?php echo esc_attr( aaih__common_str( 'code_up_supplement' ) ); ?></p></td>
		</tr>
		<tr>
			<td colspan="2" class="show-code"><?php aaih__analytics_code( $analytics_id_to_show , 'show'); ?></td>
		</tr>
		<?php
			if( AAIH__ANALYTICS_ID_NOTHING_ALL_STR === $analytics_id_to_show ) { ?>
			<tr class="caption bottom">
				<td colspan="2">
					<p class="supplement"><?php echo esc_attr( aaih__common_str( 'code_down_supplement' ) ); ?></p>
				</td>
			</tr>
			<?php
			}
		?>
	</tbody></table>
<?php
}

/**
 * アナリティクス : IDの取得
 *
 * - トラッキングID または 計測ID の取得（ Measurement ID : G-XXXXXXX / Tracking ID : UA-000000-2 ）
 * - IDの入力が無い場合は AAIH__ANALYTICS_ID_NOTHING_STR を返す。
 *
 * @param array $settings	: array $settings の配列
 * @return string IDの文字列 : Measurement ID : G-XXXXXXX / Tracking ID : UA-000000-2
 */
function aaih__get_analytics_id ( $settings ) {
	$analytics_id	= $settings['analytics_id']; 	// Tracking ID : 000000 of UA-000000-2 or Measurement ID : XXXXXXX of G-XXXXXXX

	if ( '' === $analytics_id ) {
		$analytics_id	= AAIH__ANALYTICS_ID_NOTHING_ALL_STR;
	}
	return $analytics_id;
}


/* -----------------------------------
 * 表示関連
 * ----------------------------------- */

/**
 * コールバック）表示言語
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */

function aaih__fc__language( $args ) {
	$language_setting 	= $args['settings']['language'];
	$supplement	= __( 'Select language to display in this settings.', AAIH__TEXT_DOMAIN );

	// mo ファイルリストを取得
	$translation_files = aaih__get_translation_files();
	?>
	<select class="language-select" name="<?php echo AAIH__SETTINGS; ?>[language]">
	<?php
		foreach ( $translation_files as $key => $language_code ) {
		?>
			<option value="<?php echo esc_attr( $language_code ); ?>"<?php echo ( $language_setting === $language_code ? ' selected ' : '' ); ?>>
				<?php echo esc_attr( ( $key + 1 ) . ' ) ' . aaih__get_country_language_name( $language_code ) ); ?>
			</option>
		<?php
		}
	?>
	</select>
	<p class="supplement"><?php echo esc_attr( $supplement ); ?></p>
	<?php
}

/**
 * 翻訳ファイル（ .mo ）を取得
 *
 * - language フォルダー内にある .mo ファイルを取得して 配列で返す
 * - .mo ファイルがない場合でも、'en' だけが入っている配列を返す
 *
 * @return array $translation_files
 */
function aaih__get_translation_files() {
	// 英語をまずセット
	$translation_files 	= array( 'en' );
	// .mo ファイルのファイル名取得（配列）
	$lang_array 		= get_available_languages( AAIH__PATH_TO_LANGUAGE );

	foreach ( $lang_array as $key => $file_name ) {
		// ファイル名から Language Code 以外は削除
		$pattern	= '/' . AAIH__MENU_SLUG . '-/';	// /ad-auto-insert-h-/
		$lan 		= preg_replace( $pattern , '' , $file_name );
		// 取得ファイルを配列に追加
		$translation_files[] = $lan;
	}

	return $translation_files;
}


/**
 * 言語コードに対する 国名を返す
 *
 * @param string $lan_code
 * @return string （翻訳された）国名
 */
function aaih__get_country_language_name ( $lan_code ) {

	// 中国語では、zh-CN など大文字が使われる可能性があるため
	// 念のために 大文字を小文字に戻しておく
	$lan_code = strtolower( $lan_code );

	// 主要言語　翻訳用
	// 適当に抜粋（ここにないもの使いたい場合には po ファイルに直接翻訳を入れて翻訳ファイル mo を作る ）

	// 言語コードについては以下参照
	// https://ja.wordpress.org/support/article/installing-wordpress-in-your-language/
	// http://www.gnu.org/savannah-checkouts/gnu/gettext/manual/gettext.html#Language-Codes

	$language_name	= array(
		'ar' 	=>	__( 'Arabic' 		, AAIH__TEXT_DOMAIN ),	// アラビア語
		'en' 	=>	__( 'English' 		, AAIH__TEXT_DOMAIN ),	// 英語
		'de' 	=>	__( 'German'		, AAIH__TEXT_DOMAIN ), 	// ドイツ語
		'es' 	=>	__( 'Spanish'		, AAIH__TEXT_DOMAIN ), 	// スペイン語
		'fr' 	=>	__( 'French' 		, AAIH__TEXT_DOMAIN ), 	// フランス語
		'hi' 	=>	__( 'Hindi'			, AAIH__TEXT_DOMAIN ), 	// ヒンディー語
		'id' 	=>	__( 'Indonesian'	, AAIH__TEXT_DOMAIN ),	// インドネシア語
		'it' 	=>	__( 'Italian' 		, AAIH__TEXT_DOMAIN ), 	// イタリア語
		'ja' 	=>	__( 'Japanese' 		, AAIH__TEXT_DOMAIN ), 	// 日本語
		'ko' 	=>	__( 'Korean' 		, AAIH__TEXT_DOMAIN ), 	// 韓国語
		'nl' 	=>	__( 'Dutch' 		, AAIH__TEXT_DOMAIN ), 	// オランダ語
		'pl' 	=>	__( 'Polish' 		, AAIH__TEXT_DOMAIN ), 	// ポーランド語
		'pt' 	=>	__( 'Portuguese' 	, AAIH__TEXT_DOMAIN ),	// ポルトガル語
		'ru' 	=>	__( 'Russian' 		, AAIH__TEXT_DOMAIN ), 	// ロシア語
		'th' 	=>	__( 'Thai' 			, AAIH__TEXT_DOMAIN ), 	// タイ語
		'tl' 	=>	__( 'Tagalog' 		, AAIH__TEXT_DOMAIN ), 	// タガログ語
		'tr' 	=>	__( 'Turkish' 		, AAIH__TEXT_DOMAIN ), 	// トルコ語
		'vi' 	=>	__( 'Vietnamese' 	, AAIH__TEXT_DOMAIN ), 	// ベトナム語
		'zh' 	=>	__( 'Chinese' 				, AAIH__TEXT_DOMAIN ), 	// 中国語
		'zh-cn' =>	__( 'Chinese (Simplified)' 	, AAIH__TEXT_DOMAIN ), 	// 中国語 (簡体字)
		'zh-tw' =>	__( 'Chinese (Traditional)'	, AAIH__TEXT_DOMAIN ), 	// 中国語 (繁体字)
	);

	// 配列にあれば、（翻訳された）国名を返す
	if( isset ( $language_name[ $lan_code]) ){
		return  $language_name[ $lan_code];
	}
	// 配列になければ、言語コードを翻訳したとして返す
	// （ po ファイルに追加して mo にすれば翻訳表示される ）
	else{
		return 	__( $lan_code , AAIH__TEXT_DOMAIN );
	}
}

/* -----------------------------------
 * 利用制限
 * ----------------------------------- */
/**
 * コールバック）利用制限
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__access_control_onoff( $args ){
	$settings	= $args['settings'];	// 設定値全体

	$item_label	= __( 'Administrator only', AAIH__TEXT_DOMAIN );	//管理者のみ

	$access_control_onoff = $settings['access_control_onoff'];
	$supplement1 = __( 'Access control works only for logging in users. Ad codes are inserted only to users with administrator role.', AAIH__TEXT_DOMAIN );
	$supplement2 = __( 'The plugin does not insert any ad codes to other roles such as editor and author.', AAIH__TEXT_DOMAIN );
	// ワードプレスにログイン時、プラグインが動作するのは管理者のみとする設定。
	// 編集者や投稿者など他の権限のユーザーがログインしている場合、広告の挿入は行われない。
	?>

	<table class="options narrow access-control-onoff"><tbody>
		<tr>
			<th class="label"><?php echo esc_attr( $item_label ); ?></td>
			<td colspan="2">
				<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[access_control_onoff]" value="off">
				<label>
					<input type='checkbox' name='<?php echo AAIH__SETTINGS; ?>[access_control_onoff]' <?php checked( $access_control_onoff, 'on' ); ?> value='on'>
					<?php _e( 'Enable', AAIH__TEXT_DOMAIN ); // 管理者の場合のみ有効にする ?>
				</label>
			</td>
		</tr>
		<tr>
			<th></th>
			<td class="supplement">
				<p class="supplement"><?php echo esc_attr( $supplement1 ); ?><br /><?php echo esc_attr( $supplement2 ); ?></p>
			</td>
		</tr>
	</tbody></table>
 <?php
}

/* -----------------------------------
 * デバッグ関連
 * ----------------------------------- */
/**
 * コールバック）デバッグモード（on/off）
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__debug_mode_onoff( $args ) {
	$settings	= $args['settings'];	// 設定値全体

	// デバッグモード on/off checkbox 表示
	aaih__debug_mode_enable_checkbox( $settings );
	// デバッグモード オプション表示
	aaih__debug_mode_options( $settings );
}

/**
 * デバッグモード on/off checkbox 表示
 *
 * @param array $settings 設定全体の配列
 * @return void
 */
function aaih__debug_mode_enable_checkbox( $settings ){
	$item_label	= __( 'Debug mode', AAIH__TEXT_DOMAIN );

	$debug_mode_onoff = $settings['debug_mode_onoff'];
	$supplement1 = __( 'Display setting values only when an article is previewed. ( For operation confirmation )', AAIH__TEXT_DOMAIN );
	$supplement2 = __( 'The display of actual post or page does not change.', AAIH__TEXT_DOMAIN );
	// プレビュー時でのみ各種設定の数値を表示する。動作確認用の設定。
	// 実際の投稿や固定ページの表示は変化しない。
	?>

	<table class="options narrow debug-mode onoff"><tbody>
		<tr>
			<th class="label"><?php echo esc_attr( $item_label ); ?></td>
			<td colspan="2">
				<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[debug_mode_onoff]" value="off">
				<label>
					<input type='checkbox' name='<?php echo AAIH__SETTINGS; ?>[debug_mode_onoff]' <?php checked( $debug_mode_onoff, 'on' ); ?> value='on'>
					<?php _e( 'Enable Debug mode', AAIH__TEXT_DOMAIN ); // デバッグモードを有効にする ?>
				</label>
			</td>
		</tr>
		<tr>
			<th></th>
			<td class="supplement">
				<p class="supplement"><?php echo esc_attr( $supplement1 ); ?><br /><?php echo esc_attr( $supplement2 ); ?></p>
			</td>
		</tr>
	</tbody></table>
<?php
}


/**
 * デバッグモード オプション表示
 *
 * @param array $settings 設定全体の配列
 * @return void
 */
function aaih__debug_mode_options( $settings ) {
	$title_summary_onoff		= __( 'Summary display' , AAIH__TEXT_DOMAIN );	// 設定サマリー表示
	$supplement = __('Disable the display of the settings overview at the beginning of the article in the preview.' , AAIH__TEXT_DOMAIN );
?>
	<table class="debug-mode options narrow"><tbody>
		<tr>
			<th></th>
			<td class="summary-disable"><?php echo esc_attr( $title_summary_onoff ); ?></td>
			<td><?php aaih__debug_mode_options_summary_disable( $settings );?></td>
		</tr>
		<tr>
			<th></th>
			<td></td>
			<td class="supplement"><p><?php echo esc_attr( $supplement ); ?></p></td>
		</tr>
	</tbody></table>
<?php
}

/**
 * デバッグモード オプション : サマリー表示無効 on /off
 *
 * @param array $settings 設定全体の配列
 * @return void
 */
function aaih__debug_mode_options_summary_disable( $settings ){
	$debug_mode_summary_disable 	= $settings['debug_mode_summary_disable'];
	?>
	<input type="hidden" name="<?php echo AAIH__SETTINGS; ?>[debug_mode_summary_disable]" value="off">
	<label>
		<input type='checkbox' name='<?php echo AAIH__SETTINGS; ?>[debug_mode_summary_disable]'<?php checked( $debug_mode_summary_disable, 'on' ); ?> value='on'>
		<?php _e( 'Disable Setting summary display', AAIH__TEXT_DOMAIN ); ?>
	</label>
	<?php
}

/**
 * コールバック）タブナンバーを読み込みセットする
 *
 * タブナンバーを jsへ渡す用。
 * 画面には表示しない。
 *
 * @param array $args	: string $class , array $settings の配列（$class は基本使わない）
 * @return void
 */
function aaih__fc__tab_menu_num( $args ) {
	// 変更を保存時のみ設定値を読み込み反映
	// $value は、タブがクリックされると jQueryで値を変更している（その値を保存し読み込む）
	$value = $args['settings']['tab_menu_num'];
	$value = aaih__get_tab_num( $value );

	?>
	<input id="tab_menu_num" type="hidden" name="<?php echo AAIH__SETTINGS; ?>[tab_menu_num]" value="<?php echo absint( $value );?>">
<?php
}
?>