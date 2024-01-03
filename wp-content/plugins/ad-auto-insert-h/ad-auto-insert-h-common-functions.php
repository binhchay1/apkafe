<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 変数の値を取得
 *
 * 設定値全体を取得、または個別に取得する。
 * サニタイズ（aaih__item_sanitize()）、データ検証（aaih__item_validation()）して返す。
 *
 * - 全体を取得：キー指定なし
 * - 個別に取得：キーを指定
 *
 * @param string $key	：個別に取得する場合に指定
 * $param string $state	: プラグインバージョンアップ時のみ使用 'version_up'
 * （サニタイズ、データ検証をせずにひとまず返し、呼び出し側でサニタイズとデータ検証を行う
 *
 * @return array | string | int | float ：設定項目と値の配列、または設定個別の値を返す
 *
 */
function aaih__get_item( $key = '' , $state = '' ) {

	// まだ値がない場合はデフォルト
	if ( false === get_option( AAIH__SETTINGS )	) {
		$settings = aaih__get_default_values();
	}
	else{
		$settings = get_option( AAIH__SETTINGS );
		$settings = aaih__item_sanitize( $settings , $state ); // $state : version_up の場合に 'version_up' が入る。その他は空文字
	}

	// $key 指定あり
	if ( '' !== $key ) {
		if ( isset( $settings[ $key ] ) ) {
			$settings 	= array( $key => $settings[ $key ] );
			$settings 	= aaih__item_validation( $settings );

			return $settings[ $key ];

		}else {
			aaih__popup_alert( 'aaih__get_item - key: ' . $key );
		}

	}
	else {
	// $key 指定なし
		if ( 'version_up' !== $state ) {
			$settings 	= aaih__item_validation( $settings );
		}
		return $settings;
	}
}

/**
 * プラグインタイトル表示（H1）
 *
 * - 日本語とその他言語で表示を分ける（日本語だけ特別に日本語訳のタイトルも表示）
 * - ページタイトルは echo（プラグイン設定画面、カスタムフィールドの表示でも使う）
 * - 設定に使う場合は 文字列を返す
 *
 * @param string $language
 * @param string $type	ページタイトル時は 'page_title'
 * @return void | string タイトル文字列
 */
function aaih__get_plugin_name( $language , $type ='' ) {
	if ( 'page_title' === $type ) {
		if ( 'ja' === $language ) {
			echo AAIH__PLUGIN_NAME . '<span class="supplement">（ ' . AAIH__PLUGIN_NAME_JA . ' ）</span>';
		}
		else {
			echo AAIH__PLUGIN_NAME;
		}
	}
	else {
		if ( 'ja' === $language ) {
			return AAIH__PLUGIN_NAME . '（ ' . AAIH__PLUGIN_NAME_JA . ' ）';
		}
		else {
			return AAIH__PLUGIN_NAME;
		}
	}
}

/**
 * タブ表示／非表示のclass取得
 *
 * @return string $tab_class　active / passive
 */
function aaih__get_tab_class() {
	// 一旦すべて passive に設定
	$tab_class			= array( 'passive' , 'passive' , 'passive' , 'passive' );
	// どのタブを表示するか取得
	$current_tab_num	= aaih__get_current_tab_num( 'tab_menu_num' );

	// 表示するタブのみ active に設定
	// 補足：変更を保存時は、その時点のタブを active にする
	$tab_class[ $current_tab_num ] = 'active';

	return $tab_class;
}


/**
 * 現在のタブナンバーを取得
 *
 * @return int $current_tab_num 現在のタブナンバー 0, 1, 2, 3
 */
function aaih__get_current_tab_num() {
	// 変更を保存ボタン押下で保存された タブナンバーを取得
	$tab_menu_num		= aaih__get_item( 'tab_menu_num' );
	// 現在表示するタブナンバーを取得
	$current_tab_num	= aaih__get_tab_num( $tab_menu_num );

	return $current_tab_num;
}


/**
 * 保存ボタンの表示
 *
 * 全てのタブの設定が保存されるため、ちょっと分かりやすく文言変更
 *
 * @return void
 */
function aaih__show_submit_button() {
	$button_name	= __( 'Save All Changes'	, AAIH__TEXT_DOMAIN );
	?>
	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr( $button_name ); ?>" />
	<?php
}


/**
 * タブ：取得したタブ番号で良いかチェック
 *
 * 変更を保存時	：そのまま返す（現在のタブナンバー）
 * それ以外		：0（先頭のタブ）を返す（初期の起動時やリロード時）
 *
 * @param int $tab_num	: int タブの番号 0, 1, 2, ....
 * @return int $num		: int タブの番号 0, 1, 2, ....
 */
function aaih__get_tab_num( $tab_num ) {
	// $_GET["settings-updated"] ：「true」の　"文字列" が返ってくる
	if ( isset( $_GET["settings-updated"] ) && 'true' === $_GET["settings-updated"] ) {
		$num = $tab_num;
	}else {
		$num = 0;
	}
	return (int) $num;
}


/**
 * 表示する広告があるか判定
 *
 * 現状, lazyload 実行判定の中で使っているのみ。
 *
 * 表示する広告がある場合は以下
 * - 1) pub-id の入力がある
 * - 2）表示するページに対し、広告挿入の対象投稿タイプ（投稿/固定ページ/両方）があっている
 * - 3-1）Hタグ前Ad, 最初のHタグAd、記事下AdのいずれかがON
 * - 3-2）１つでもon に設定されているAdxxに、data-ad-slot の入力がある
 * - または 3）ショートコードが記事中にある
 * ※）ショートコードは Hタグ前Ad, 最初のHタグAd、記事下Adのon/off設定に左右されず、あれば表示
 *
 * @param array $settings	設定値全体
 * @return string
 * 	- string 'yes' 	: 表示する広告がある場合
 * 	- string 'no'	: 表示する広告がない場合
 */
function aaih__has_ad( $settings ) {
	// 1) pub-id の入力があるか
	if( '' === $settings['pub_id'] ) {
		return 'no';
	}

	// 2）自動の広告挿入の対象投稿タイプがあっている
	// あっている場合	：selected_post_type
	// あってない場合	：not_selected_post_type
	$post_type			= $settings['post_type'];
	$post_type_check	= aaih__post_type_check( $post_type );

	if ( 'selected_post_type' === $post_type_check ) {

		// 3-1）Hタグ前Ad, 最初のHタグAd、記事下AdのいずれかがON
		// 3-2）１つでもon に設定されているAdxxに、data-ad-slot の入力がある

		// 最初のHタグAd
		$onoff			= $settings['first_h_tag_ad_onoff'];
		$ad_nth			= $settings['ad_select__first_h_tag'];
		$ad_unit		= $settings[ $ad_nth ]['ad_unit_select'];
		$ad__unit_id	= $settings[ $ad_nth ][ 'ad_unit_id__' . $ad_unit ];

		// 最初のHタグAdが on かつ 広告コード入力あり
		if ( 'on' === $onoff && '' !== $ad__unit_id ) {
			return 'yes';
		}

		// Hタグ前Ad
		$onoff			= $settings['h_tag_ad_onoff'];
		$ad_nth			= $settings['ad_select__h_tag'];
		$ad_unit		= $settings[ $ad_nth ]['ad_unit_select'];
		$ad__unit_id	= $settings[ $ad_nth ][ 'ad_unit_id__' . $ad_unit ];

		// Hタグ前Adが on かつ 広告コード入力あり
		if ( 'on' === $onoff && '' !== $ad__unit_id ) {
			return 'yes';
		}

		// 記事下Ad
		$onoff			= $settings['after_content_ad_onoff'];
		$ad_nth			= $settings['ad_select__after_content'];
		$ad_unit		= $settings[ $ad_nth ]['ad_unit_select'];
		$ad__unit_id	= $settings[ $ad_nth ][ 'ad_unit_id__' . $ad_unit ];

		// 記事下Adが on かつ 広告コード入力あり
		if ( 'on' === $onoff && '' !== $ad__unit_id ) {
			return 'yes';
		}
	}

	// 3）ショートコードが記事中にある
	// ショートコードのありなし（なければ 'nothing' が返る ）
	$the_content 	= get_the_content();
	$has_shortcode	= aaih__get_shortcode( get_the_content( $the_content ) );

	if ( 'nothing' !== $has_shortcode ) {
		return 'yes';
	}

	// いずれも yes でない場合は no を返す
	return 'no';

}


/**
 * Adコード設定 のタブ と コード設定表示 のクラス取得
 *
 * 取得するクラス
 * - ユニーククラス：tab （タブ部）/ ad-code-all（Adコード設定部）+ hide（tr を非表示に）
 * - Ad ナンバー
 * - active（表示する） / passive（非表示）
 * - used-ad （設定で使用中）/ no-used （設定では不使用）
 *
 * @param int $num 0, 1, 2, ....
 * @param string $unique_class ユニーククラス（ tab / ad-code-all hide）
 * @param array $settings 設定値全体の配列
 * @return string class を返す
 */
function aaih__get_class__ad_tab_ad_code( $num , $unique_class , $settings ){
	// 対象の Adxx
	$ad_nth = 'Ad' . ( $num + 1 );	// Ad1, Ad2, ...

	// 先頭のHタグ前Ad, Hタグ前Ad, 記事下Ad, の どの設定でどのAdxxが使われているか取得
	$setting_ad_nth_0 = $settings['ad_select__first_h_tag'];
	$setting_ad_nth_1 = $settings['ad_select__h_tag'];
	$setting_ad_nth_2 = $settings['ad_select__after_content'];

	// 設定の on/off を取得
	$setting_0_onoff = $settings['first_h_tag_ad_onoff'];
	$setting_1_onoff = $settings['h_tag_ad_onoff'];
	$setting_2_onoff = $settings['after_content_ad_onoff'];

	// 表示するタブ（ activeタブ番号 : 0 ～ )　を取得
	$tab_num = $settings['ad_code_tab_num'];
	$tab_num = aaih__get_tab_num( $tab_num );

	// 個別のクラスをまず設定
	$class = $unique_class . ' ' . strtolower( $ad_nth );

	// 設定（先頭のHタグ前Ad, Hタグ前Ad, 記事下Ad）で使用中の場合には class に used-ad を付加
	if ( $ad_nth === $setting_ad_nth_0 && 'on' === $setting_0_onoff ||
		$ad_nth === $setting_ad_nth_1 && 'on' === $setting_1_onoff ||
		$ad_nth === $setting_ad_nth_2 && 'on' === $setting_2_onoff ) {
		$class = $class . ' used-ad';
	}
	else {
		$class = $class.' no-used';
	}

	// パブリッシャーID、広告ユニットID とも入力がある場合には、enable 、
	// そうでなければ disable を追加
	$validation_chk = aaih__adsense_unit_code__validation( $ad_nth , $settings );
	If ( 'OK' === $validation_chk ) {
		$class = $class . ' enable';
	} else {
		$class = $class . ' disable';
	}

	// 表示するタブ番号と同じであれば class に active 追加（して表示する）
	if ( $tab_num === $num ) {
		$class = $class . ' active';
	}
	else{
		$class = $class . ' passive';
	}

	return $class;
}

/**
 * 挿入する広告コードのHTML生成
 *
 * 広告自動挿入の対象外の post type でも取得できるよう（ショートコードで利用できるよう）
 * 対象投稿タイプは意識しない
 *
 * @param array $settings	: 設定値全体（各種設定値を取得するため）
 * @param string $ad_number_th	: Ad1, Ad2 ...　$unique_style を指定する場合には空文字を指定する
 * @param string $unique_style	: どの設定による広告コードかの識別。指定しない場合には $ad_number_th を指定する
 *
 * - $unique_style : first（最初のHタグ前）, bottom（記事下）, in-article（Hタグ前）, shortcode（ショートコード）が入る
 *
 * @return string $ad_code_html_all	：広告コード表示用のHTMLコード
 */
function aaih__get_ad_html( $settings , $ad_number_th = '' , $unique_style = '' ) {

	// 選択されている広告コードを取得（ Ad1, Ad2 ...）
	switch ( $unique_style ) {
		case 'first':
			$ad_number_th = $settings['ad_select__first_h_tag'];
			break;
		case 'in-article':
			$ad_number_th = $settings['ad_select__h_tag'];
			break;
		case 'bottom':
			$ad_number_th = $settings['ad_select__after_content'];
			break;

		case 'shortcode':
		case '':
			// $ad_number_th をそのまま使う
			break;
		default:
			aaih__popup_alert( 'no case - aaih__get_ad_html: unique_style : ' . $unique_style );
	}

	// $ad_number_th の 実際のコードを取得
	// 遅延表示（LazyLoad）を行う場合は、アドセンスコードは含まれない
	$ad_code	= aaih__get_ad_code_of_nth ( $ad_number_th , $settings );

	// -----------------------------------
	// 広告表示に必要な各設定値読み込み
	// -----------------------------------

	$space_unit						= $settings['space_unit'];						// 空けるスペースの単位　em or px
	$updown_margin_separate_onoff	= $settings['updown_margin_separate_onoff']; 	// 上下のマージンを個別に設定するか

	// $ad_number_th: Ad1～Adxx
	$updown_margin_em		= $settings[ $ad_number_th ]['updown_margin_em'];		//上下のマージン em
	$updown_margin_px		= $settings[ $ad_number_th ]['updown_margin_px'];		//上下のマージン px
	$updown_margin_down_em	= $settings[ $ad_number_th ]['updown_margin_down_em'];	//下のマージン em
	$updown_margin_down_px	= $settings[ $ad_number_th ]['updown_margin_down_px'];	//下のマージン px

	$label					= $settings[ $ad_number_th ]['label'];					//広告ラベル
	$label_space_em			= $settings[ $ad_number_th ]['label_space_em'];			//広告と広告ラベルの間のスペース em
	$label_space_px			= $settings[ $ad_number_th ]['label_space_px'];			//広告と広告ラベルの間のスペース px
	$centering				= $settings[ $ad_number_th ]['centering'];				//センタリング

	$updown_margin 			= 'em' === $space_unit ? $updown_margin_em : $updown_margin_px;
	$updown_margin_down 	= 'em' === $space_unit ? $updown_margin_down_em : $updown_margin_down_px;
	$label_space 			= 'em' === $space_unit ? $label_space_em : $label_space_px;

	// -----------------------------------
	// ラッパー
	// -----------------------------------
	// クラス
	// $unique_style：識別するために first（最初のHタグ前）, bottom（記事下）, in-article（Hタグ前）, shortcode（ショートコード）が入る
	$class = 'aaih ' . $unique_style;

	// スタイル
	if ( 'on' !== $updown_margin_separate_onoff ) {
		// 上下マージンを個別にしてない場合
		$updown_margin_down = $updown_margin;
	}
	$style_wrap	= 'margin-top:'. $updown_margin . $space_unit . ';' . ' margin-bottom:' . $updown_margin_down . $space_unit . '; line-height:1;';

	// デバッグ表示の場合は クラスを追加
	if ( 'show' === aaih__debug_msg_show( $settings ) ) {
		$class .= ' debug';
	}

	// -----------------------------------
	// 広告ラベル
	// -----------------------------------
	//スタイル
	$style_label 	= 'margin-bottom:' . $label_space . $space_unit . ';' ;
	if ( 'on' === $centering ) {
		$style_label 	= $style_label.' text-align:center;';
	}
	$style_label = 'style="' . $style_label . '"';

	// HTML
	if ( '' !== $label ) {
		$label	= '<div class="ad-label" '. $style_label . '>' . $label . '</div>';
	}

	// -----------------------------------
	// 広告
	// -----------------------------------
	//スタイル
	$style_ad		= '';
	if ( 'on' === $centering) {
		$style_ad 		= 'style="text-align:center;"';
	}

	// 広告コードが設定されてなければデバッグ用の文字列表示を返す
	// 実際の記事表示では表示されない（プレビューでのデバッグ表示専用）
	if ( '' === $ad_code ) {
		$msg		= $ad_number_th . __( ' is used but no Publisher ID or Ad unit ID input.' , AAIH__TEXT_DOMAIN ) . '<br />' . __( 'Nothing will be displayed here.' , AAIH__TEXT_DOMAIN );
		// が使用されてますが、広告コードの入力がありません。<br />そのため実際にはここに何も表示されません。';
		$ad_code	= aaih__debug_msg( $msg , $settings );

		$class .= ' no-ad';
	}

	// -----------------------------------
	// 広告表示用のHTML生成
	// -----------------------------------

	// 公式ディレクトリ登録ではヒアドキュメントの使用は禁止されている
	// 改行は「"\n"」というようにダブルクォートで括る必要あり。

	$ad_code_html_all = '<!-- aaih ad code -->' . "\n";
	$ad_code_html_all .= '<div class="' . $class . '" style="' . $style_wrap . '">'. "\n";
	$ad_code_html_all .= $label . "\n";
	$ad_code_html_all .= '	<div class="ad-code" ' . $style_ad . '>' . $ad_code . '</div>' . "\n";
	$ad_code_html_all .= '</div>' . "\n";
	$ad_code_html_all .= '<!-- //aaih ad code -->' . "\n";

	return $ad_code_html_all;
}


/**
 * post type check
 *
 * 広告表示の対象の post type かチェックする。
 *
 * @param string $post_type	：'post' , 'page' , 'both'
 *
 * @return string
 * 広告表示する post type の場合		：'selected_post_type'
 * 広告表示する post type でない場合	：'not_selected_post_type'
 */
function aaih__post_type_check( $post_type ) {
	switch ( $post_type ) {
		case 'post':
			if ( ! is_single() ) {
				return 'not_selected_post_type';
			}
			break;
		case 'page':
			if ( ! is_page() ) {
				return 'not_selected_post_type';
			}
			break;
		case 'both':
			if ( ! is_single() && ! is_page() ) {
				return 'not_selected_post_type';
			}
			break;
		default:
			$alert_msg	= 'aaih__post_type_check: 該当しないケース: '.$post_type;
			aaih__popup_alert( $alert_msg );
	}
	return 'selected_post_type';
}


/**
 * Adxx , shortcode_replacexx の Prefix のみゲット
 *
 * Adxx, shortcode_replacexx の prefix (Ad, shortcode_replace) を取得する。
 *
 * @param string $prefix_with_num	：'Ad1' , 'Ad2' ... , 'shortcode_replace1', 'shortcode_replace2' ....
 * @return string $only_prefix		：'Ad', 'shortcode_replace'
 *
 * 関連	：
 * preg_replace( $pattern, $replacement, $subject, $limit = -1, &$count = null)
 * $subject に対して、pattern で検索を行い replacement に置換
 */
function aaih__get_only_prefix( $prefix_with_num ) {

	$only_prefix = preg_replace( '/[0-9]/' , '' , $prefix_with_num );

	return $only_prefix;
}


add_action( 'wp_enqueue_scripts', 'aaih__add_css' );
/**
 * CSS追加：プレビューのデバッグ情報表示用
 *
 * プレビュー時におけるデバッグ情報表示用のCSSを追加する。
 * アクションフック wp_enqueue_scripts を使用。
 * wp_enqueue_style()で ハンドルをまず登録し、wp_enqueue_scriptsでフックする。
 *
 * @return void
 *
 * 関連		：
 * wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
 * https://developer.wordpress.org/reference/hooks/wp_enqueue_scripts/
 * 適切なタイミングでファイルを生成されたページにリンクする
 * スクリプトのリンクは、wp_register_script() 関数ですでに登録済みのハンドル、またはこの関数に必要なパラメータを与えることで行える。
 *
 * wp_enqueue_style()
 * https://developer.wordpress.org/reference/functions/wp_enqueue_style/
 *
 * plugins_url( $path, $plugin )
 * pluginsディレクトリーの絶対 URL を取得（末尾のスラッシュなし）
 * - $path		: pluginsディレクトリーからの相対パス
 * - $plugin	: __FILE__ を渡せば、$path を現在の PHP スクリプトファイルの親ディレクトリーからの相対パスとして扱える
 * https://developer.wordpress.org/reference/functions/plugins_url/
 */
function aaih__add_css() {

	$debug_mode_onoff = aaih__get_item( 'debug_mode_onoff' );

	// プレビューのデバッグ情報表示用
	if ( is_preview() && 'on' === $debug_mode_onoff ) {
		wp_enqueue_style( 'aaih__preview_css' , plugins_url( 'css/' . AAIH__MENU_SLUG . '-preview.css' , __FILE__ ) );
	}
}


/**
 * 国際化対応：翻訳ファイルのロード
 *
 * 設定値（表示言語の設定）に基づいて翻訳を適用する。
 * - 初期値はワードプレスの locale に基づく。
 * - 一回でも設定が保存されたら設定値に基づく。
 *
 * 設定値 'en'以外	: 対象の翻訳ファイルをロード。
 * 設定値 'en'	: 翻訳ファイルはロードしない。（つまり英語表示）
 *
 * @return void
 *
 * 関連: load_textdomain()
 * 国際化用ファイル（MOファイル）をロードする
 * ロードできた場合：true, 出来なかった場合：false
 * https://developer.wordpress.org/reference/functions/load_textdomain/
 */
function aaih__add_translation() {
	$language	= aaih__get_item( 'language' );

	// 英語以外は翻訳ファイルの読み込み
	if ( 'en' !== $language ) {

		$mofile_path	= AAIH__PATH_TO_LANGUAGE . AAIH__TEXT_DOMAIN . '-' . $language . '.mo';
		$result			= load_textdomain( AAIH__TEXT_DOMAIN , $mofile_path );

		if ( false === $result ) {
			// 翻訳ファイルがない場合、表示言語は英語になるだけ
		}
	}
}

/**
 * JavaScriptが動作する環境かチェック
 *
 * @return void
 */
function aaih__javascript_enable_check () {
?>
	<noscript>
	<div class="js-error warning">
		<?php
			_e( 'It seems like JavaScript is not enabled. Please check your browser settings.', AAIH__TEXT_DOMAIN );
			// JavaScript が有効になってないようです。ブラウザの設定をご確認ください。
		?>
	</div>
	</noscript>
<?php
}

/**
 * アラートポップアップ
 *
 * エラーなどの場合、それと分かるテキスト文言を表示する。
 * 基本はデバッグ用に用意しているもの。
 *
 * @param string $alert_msg	ポップアップ表示用の文字列
 * @param string $case ポップアップ表示をするしないの場合分けが必要なケースでセット（サニタイズで使用）
 * @return void
 */
function aaih__popup_alert( $alert_msg , $state = '') {
	if ( 'version_up' === $state ) {
		return;
	}
	echo '<script type="text/javascript">alert("' . esc_attr( $alert_msg ) . '");</script>';
}

/**
 * 入力文字数チェック
 *
 * 最大文字数内に丸めて返す
 *
 * @param string $value			: 対象の文字列
 * @param int $max_length		: 文字列の最大文字数
 * @return string $value		: 最大文字数を超えたところは削除して丸められた文字列
 */
function aaih__length_chk( $value , $max_length ) {
	$value	= mb_substr( $value , 0 , $max_length , 'UTF-8' );
	return $value;
}


/**
 * 数値の最大、最小、デフォルトチェック
 *
 * 設定値に 最小値、最大値、デフォルト値がある場合（例：広告の上下のマージン）
 * 値がその範囲内に収まるように調整する。
 *
 * - 数値以外			: デフォルト値をセット
 * - 最大値を超える場合	: 最大値をセット
 * - 最小値を下回る場合	: 最小値をセット
 *
 * @param float $value 	: 対象の数値
 * @param float $init	: デフォルト値
 * @param float $min	: 最小値
 * @param float $max	: 最大値値
 * @return float : 読み出しもとで、int となる場合には int でキャストする
 */
function aaih__check_numeric_min_max( $value, $init , $min , $max ) {
	//数字以外の場合：初期値に戻す
	if ( ! is_numeric( $value ) ) {
		$value = $init;
	}
	//数字の場合
	else {
		if ( $value < $min ) {		//ゼロ以下はとりあえず制限しておく
			$value = $min;
		}else if ( $value > $max ) {	//上限もとりあえず制限しておく
			$value = $max;
		}
	}
	return (float) $value;
}


/**
 * Adxx, shortcode_replacexxx の数チェック
 *
 * 設定値を超えるナンバー（例えば Ad100 の「100」など）になってないか
 * 念のためにチェックする関数
 *
 * @param string $check_name	: Ad1, Ad2, ... , shortcode_replace1, shortcode2, ...
 * @param string $type			: 'Ad', 'shortcode'
 * @return string $chk			: 設定値内であれば 'OK', それ以外であれば 'NG' を返す
 *
 * 補足:
 * Adxx は設定中に選択があるが、shortcode_replace は選択がないので
 * 結果、case 'shortcode' は使わない
 */
function aaih__chk_multiple_variable_name( $check_name , $type ) {
	switch ( $type ) {
		case 'Ad':
			$prefix	= 'Ad';
			$max	= AAIH__AD_CODE_HOW_MANY;
			break;
		case 'shortcode':
			$prefix	= 'shortcode_replace_num';
			$max	= AAIH__SHORTCODE_REPLACE_MAX_NUM;
			break;
		default:
			aaih__popup_alert( 'aaih__chk_multiple_variable_name: no type: ' . $type );
	}

	// チェック用に最初に 'NG'をセット
	$chk	= 'NG';

	// 設定値内であれば 'OK' をセット
	for( $i = 1; $i <= $max ; $i ++) {
		$array_name	= $prefix . $i;
		if ( $array_name === $check_name ) {
			$chk	= 'OK';
			break;
		}
	}

	return $chk;
}

/**
 * リセットボタンの表示
 *
 * - タブ4（言語、その他）のみに表示する。
 * - プラグインロード時は 配列 $tab_class の4つ目を class に指定。
 * - タブクリックで、js から リアルタイムに class を active / passive 切り替え。
 * - 未だ設定値がない場合（初期の場合）には表示しない
 *
 * @param array $tab_class	array( 'passive' , 'passive' , 'passive' , 'passive' );
 * @return void
 */
function aaih__show_reset_button( $tab_class ) {
	// まだ設定値がなければ表示せずに戻る
	if ( ! get_option( AAIH__SETTINGS ) ) {
		return;
	}

	// tab3（言語、その他） が表示されているときのみリセットボタンの表示
	$class		= $tab_class[3];

	/*
	* action="" について
	*
	* action="" では別ファイルを指定し呼び出していたが（必要な処理のみを実行するため）、
	* その場合、そのファイルは wordpress 外で動作することになる。
	* nonce チェックなどをする場合 wordpress の関数を使う必要があり、そのファイル内で wp-load.php を改めてロードする、
	* となり、これは公式ディレクトリにプラグインを登録する場合、セキュリティ面などいくつかの理由からNGとのこと（ワードプレスのレビューチームより）。
	* ということで、別ファイル指定はせず、同じファイルに戻ってくるよう（プラグイン内だけの動作で完結するよう）変更した。
	*/
	?>
	<form id="reset" class="<?php echo esc_attr( $class ); ?>" action="" method="post" onsubmit="return resetAllDataChk()">
	<?php
		/* ------------------------------------
		* nonceフィールドを追加して後でチェックする
		* wp_nonce_field( $action, $name, $referer, $echo )
		* ------------------------------------
		* $action	：（文字列）アクションの名前。実行中のコンテキストを与える
		* $name		：（文字列）nonce の名前。作成される hidden フィールドの name 属性。
		* $referer	：（真偽値）wp_referer_field() 関数を使って、リファラーを表す hidden フィールドを生成するかどうか。初期値： true
		* $echo		：（真偽値）hidden フィールドを表示する（true）か値として返す（false）か。初期値：true
		* ----------------------------------- */
		wp_nonce_field( AAIH__NONCE_ACTION__RESET_SETTINGS , AAIH__NONCE_NAME__RESET_SETTINGS );

		// 以下、リセットボタンの表示
		$button_name	= aaih__common_str( 'reset_button_name' );
		$title			= __( 'Reset all settings of this plugin.', AAIH__TEXT_DOMAIN );	// このプラグインの全ての設定をリセットします
	?>
		<input
			id="reset-all-settings"
			type="submit"
			name="<?php echo AAIH__POST_NAME__RESET_SETTINGS; ?>"
			value="<?php echo esc_attr( $button_name ); ?>"
			title="<?php echo esc_attr( $title ); ?>"
		/>
	<?php
		// js に渡し用
		$msg1	= $title;// このプラグインの全ての設定をリセットします。
		$msg2	= __( 'OK ?', AAIH__TEXT_DOMAIN ); // よろしいですか？
		$msg3	= __( 'Are you sure ?', AAIH__TEXT_DOMAIN );	// 本当によろしいですか？
	?>
		<input
			id="reset-all-settings-msg"
			type="hidden"
			name="reset-all-settings-msg"
			value="<?php echo esc_attr( $msg1 ); ?>"
			title="<?php echo esc_attr( $msg2 ); ?>"
			placeholder="<?php echo esc_attr( $msg3); ?>"
		/>
	</form>
<?php
}

/**
 * プラグインURLへリダイレクト
 *
 * - 何か問題がある場合や、有効化直後など、プラグインの設定画面へリダイレクトするために使用する。
 * - リダイレクトでは exit しておくのは忘れずに。
 *
 * @return void
 */
function aaih__redirect_to_plugin() {
	$menu_settings_url	= 'options-general.php?page=' . AAIH__MENU_SLUG;
	$redirect_path		= admin_url() . $menu_settings_url;

	// プラグインの設定画面へ戻る
	exit( wp_redirect( $redirect_path ) );
}

/**
 * ログインユーザーが管理者かチェック
 *
 * ユーザーがログインしている場合で、
 * 利用制限（access_control_onoff）がONになっている場合
 * 管理者以外は高校コードを挿入しない
 * - H前広告
 * - 自動広告（ヘッダ挿入）
 * - ショートコードによる広告
 *
 * （おまけ）編集画面でカスタムフィールドの設定は出さない
 *
 * @param : なし
 * @return string
 * 	- string 'continue' 	: ログインしてない 、または、ログインしていても管理者の場合
 * 	- string 'stop'			: ログインしている && 管理者以外
 */
function aaih__chk_login_user(){

	// 利用制限の設定　OFF なら何もせず戻る
	$access_control_onoff	= aaih__get_item( 'access_control_onoff' );
	if( 'on' !== $access_control_onoff ){
		return 'continue';
	}

	/* ログインしてるか判定*/
	if ( is_user_logged_in() ) {	// ログインしている

		/* ログインユーザーが管理者か判定 */
		if( ! current_user_can( 'administrator' ) ){ // only if administrator
			return 'stop';	// 管理者以外なら終わり
		}
	}

	return 'continue'; // その他は継続（ログインしてない；ログインしている && 管理者の場合）
}
?>