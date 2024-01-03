<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 「メニュー情報」の関数名を指定
 */

add_action( 'admin_menu', 'aaih__setting_main' );
/**
 * メニュー表示のコールバック
 *
 * - 翻訳の追加
 * - add_options_page() でワードプレスのオプション（設定）にプラグインメニューを追加
 * - admin_print_styles-xxxx フックで add_options_page() をコールバックして css, js の キューに追加を行う
 * @return void
 */
function aaih__setting_main() {
	// 翻訳追加
	// 初期は ワードプレスのlocale情報、設定保存後は設定値により切り替え
	aaih__add_translation();

	// 設定画面メイン
	$language 	= aaih__get_item( 'language' );
	$title		= aaih__get_plugin_name( $language );

	// $page_hook を取得して、そのページだけに反映させたい css, js を キューに追加
	$page_hook = add_options_page(
		$title,					// ① ページタイトル
		$title,					// ② メニュー名
		'manage_options',		// ③ メニューが表示されるユーザー権限
		AAIH__MENU_SLUG,		// ④ メニューのスラッグ
		'aaih__setting_func'	// ⑤ 「設定画面」表示の関数名
	);
	add_action( 'admin_print_styles-'.$page_hook , 'aaih__admin_enqueue' );
}


add_action( 'admin_init', 'aaih__admin_register_myfiles' );
/**
 * CSS, js の登録
 *
 * admin_init　フックで、設定画面用の CSS と js の ハンドルをまず登録
 */
function aaih__admin_register_myfiles() {
	wp_register_style( 	'aaih__settings_css'	, plugins_url( 'css/' . AAIH__MENU_SLUG . '-settings.css' , __FILE__ ) );
	wp_register_script( 'aaih__settings_js'		, plugins_url( 'js/' . AAIH__MENU_SLUG . '-settings.js' , __FILE__ ) );
}


/**
 * 設定画面ロード時にファイルをキューに追加
 *
 * aaih__admin_register_myfiles()で登録したハンドルの CSS, js をキューに追加
 *
 * @return void
 */
function aaih__admin_enqueue() {
	wp_enqueue_style( 'aaih__settings_css' );
	wp_enqueue_script( 'aaih__settings_js' );
}


/**
 * プラグイン設定画面の表示メイン
 *
 * @return void
 */
function aaih__setting_func() {

	// タイトル下の説明 : プラグインヘッダの文言に同じ
	$description = __( AAIH__PLUGIN_DESCRIPTION , AAIH__TEXT_DOMAIN );

	// タブのラベル
	$label_tab1 = __( 'General settings',	AAIH__TEXT_DOMAIN );	// 一般設定
	$label_tab2 = __( 'Advanced settings',	AAIH__TEXT_DOMAIN );	// 高度な設定
	$label_tab3 = __( 'Options',			AAIH__TEXT_DOMAIN );	// グーグルアドセンスのオプション設定
	$label_tab4 = __( 'Language / Others',	AAIH__TEXT_DOMAIN );	// 言語、その他

	// タブ表示／非表示のclass取得 : 表示：active , 非表示：passive
	$tab_class = aaih__get_tab_class();
	// 表示言語 : H1 タイトルの表記分け, js に渡し用
	$language = aaih__get_item( 'language' );

	// 設定画面の具体的内容を表示
?>
	<div id="aaih-settings-wrap">
		<?php
			// javascript 有効化のチェック（念のため）
			aaih__javascript_enable_check ();
		?>
		<div class="settings">
			<h1><?php aaih__get_plugin_name( $language , 'page_title' ); ?></h1>
			<div class="description">
				<p><?php echo esc_attr( $description ); ?></p>
			</div>
			<div class="tab-menu">
				<div class="tab tab1 <?php echo esc_attr( $tab_class[0] ); ?>"><?php echo esc_attr( $label_tab1 ); // 一般設定 		?></div>
				<div class="tab tab2 <?php echo esc_attr( $tab_class[1] ); ?>"><?php echo esc_attr( $label_tab2 ); // 高度な設定 	?></div>
				<div class="tab tab3 <?php echo esc_attr( $tab_class[2] ); ?>"><?php echo esc_attr( $label_tab3 ); // オプション 	?></div>
				<div class="tab tab4 <?php echo esc_attr( $tab_class[3] ); ?>"><?php echo esc_attr( $label_tab4 ); // 言語、その他 	?></div>
			</div>

			<form method="post" action="options.php">

				<?php settings_fields( AAIH__FIELD_GROUP ); ?>
				<!-- タブ１（一般設定） -->
				<div class="tab-content content1 <?php echo esc_attr( $tab_class[0] ); ?>">
				<?php do_settings_sections( AAIH__MENU_SLUG . AAIH__SUB_GENERAL_SETTINGS ); ?>
				</div>
				<!-- タブ2 （高度な設定）-->
				<div class="tab-content content2 <?php echo esc_attr( $tab_class[1] ); ?>">
				<?php do_settings_sections( AAIH__MENU_SLUG . AAIH__SUB_ADVANCED_SETTINGS ); ?>
				</div>
				<!-- タブ3 （オプション）-->
				<div class="tab-content content3 <?php echo esc_attr( $tab_class[2] ); ?>">
				<?php do_settings_sections( AAIH__MENU_SLUG . AAIH__SUB_OPTIONS ); ?>
				</div>
				<!-- タブ4 （言語、その他）-->
				<div class="tab-content content4 <?php echo esc_attr( $tab_class[3] ); ?>">
				<?php do_settings_sections( AAIH__MENU_SLUG . AAIH__SUB_LANGUAGES_OTHERS );?> 
				</div>

				<?php
				// 「変更を保存」ボタンの表示
				aaih__show_submit_button();
				?>
			</form>
			<?php
			// 設定値リセットのボタン表示
			aaih__show_reset_button( $tab_class );
			?>
		</div><!--// settings-->
	</div><!--// aaih-settings-wrap-->
<?php
	//リセットの場合の実際のリセットと結果表示
	aaih__reset_check();
}

/*
 * メモ）タブUIとdo_settings_sections　について
 *
 * do_settings_sections は SUBスラッグを使ってグループ分け。
 * これによりタブ切り替えで表示、非表示を行っている。
 *
 * do_settings_sectionsをグループ分けせず1つとした場合、
 * タブ切り替えは jQuery で行っているため、
 * 保存ボタン押下で、全てを読みこんでから選択しているタブとその内容を表示することから
 * （そうしないと jQueryの内容が反映できないため）
 * まず最初のタブとその内容が一瞬アクティブ表示され、その後選択しているタブとその内容が表示される、といった
 * タイムラグがあり、見た目的にどうもいまいち、ということから SUBスラグを使用。
 *
 * タブUIについては一般的には、タブをリンクでおいて $_GET でどのタブがクリックされたかを取得し、
 * その値に対応した、settings_fields、do_settings_sections を表示する、
 * ということがされているようだ。
 *
 * https://code.tutsplus.com/tutorials/the-wordpress-settings-api-part-5-tabbed-navigation-for-settings--wp-24971
 *
 */

/* ------------------------------------------------------------------------
設定値とサニタイズ関数の登録
------------------------------------------------------------------------ */
add_action( 'admin_init' , 'aaih__setting_values_init' );
/**
 * register_setting
 *
 * 設定値名とサニタイズ関数の登録。
 * サニタイズは変更を保存ボタンを押されたとき通過する。
 * （データ呼び出し時にも同じサニタイズを使用する）
 *
 * データがない場合の初期値は aaih__get_item の中でセット。
 *
 * @return void
 */
function aaih__setting_values_init() {
	register_setting(
		AAIH__FIELD_GROUP,		// ① グループ名（settings_fieldsで設定されたもの）
		AAIH__SETTINGS,			// ② 設定値名：全ての設定値を含む配列
		array(
			'sanitize_callback' => 'aaih__item_sanitize', 	// ③ サニタイズ関数
			//'default' => $init_array						// ④ 初期値（配列）
		)
	);
}

/**
 * 初期値の取得
 *
 * 以下2つのケースで呼び出す
 * - 1) まだ1度も設定値の保存が行われてない場合（DB上に設定値がない場合）
 * - 2) バージョンアップ時（設定値項目の整合を取るため）
 *
 * @return array $default デフォルトの全てのキーと値の配列
 */
function aaih__get_default_values() {

	$default = AAIH__SETTING_DEFAULT__BASIC;

	/*
	 * ワードプレスの言語環境チェック
	 * - 表示言語を ワードプレスの言語環境に合わせる
	 * - 日本語環境以外は 文字数カウントの単位を半角（half）にセット
	 * - 日本語環境以外は 広告間隔の文字数を2倍にセット（英語を想定）
	 */
	$language	= get_bloginfo( 'language' );

	// 表示言語を ワードプレスの言語環境に合わせる
	$default['language'] = $language;

	// 日本語環境以外は 文字数カウントの単位を半角（half）にセット
	// 日本語環境以外は 広告間隔の文字数を2倍にセット（英語を想定）
	if ( 'ja' !== $language ) {
		$default['character_width_unit'] = 'half';
		$default['ad_space'] = AAIH__AD_SPACE_INIT * 2;
	}
	else {
		$default['character_width_unit'] = 'full';
		$default['ad_space'] = AAIH__AD_SPACE_INIT;
	}

	// Ad の 中身をセット
	for( $i = 0 ; $i < AAIH__AD_CODE_HOW_MANY ; $i++ ) {
		$ad_nth		= 'Ad' . ( $i + 1 );
		$ad_array	= array( $ad_nth => AAIH__SETTINGS_DEFAULT__AD['Ad'] );
		$default 	= array_merge( $default , $ad_array );
	}

	// // shortcode_replace_num の 中身をセット
	for( $i = 1 ; $i <= AAIH__SHORTCODE_REPLACE_MAX_NUM ; $i ++ ) {
		$shortcode_replace_nth		= 'shortcode_replace' . $i;
		$shortcode_replace_array	= array( $shortcode_replace_nth => AAIH__SETTINGS_DEFAULT__SHORTCODE_REPLACE['shortcode_replace'] );
		$default = array_merge( $default , $shortcode_replace_array );
	}
	return $default;
}
?>