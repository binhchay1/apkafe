jQuery( function($) {

	/* -----------------------------------
	 * タブメニュー表示切り替え（クリックによる）
	 *
	 * どのタブがクリックされたかを見て以下の class を追加
	 * クリックされたタブ			：active
	 * そのタブに属するコンテンツ	：active
	 * そのタブに属さないコンテンツ	：passive（display:none）
	 * ----------------------------------- */
	$( '.tab-menu .tab' ) . click( function() {
		const index = $( '.tab-menu .tab' ) . index( this );		//どのタブがクリックされたか取得
		console.log( 'tab-menu click : ' + index );

		//タブメニュー
		// 一旦すべてのタブから active , passive を削除
		$( '.tab-menu .tab' ) . removeClass( 'active passive' );
		// クリックされたタブのみ active を設定
		$( this) . addClass( 'active' );

		//タブコンテンツ：表示コンテンツ・非表示コンテンツ
		$( '.settings .tab-content' ) . removeClass( 'active passive' ) . addClass( 'passive' );
		$( '.settings .tab-content' ) . eq( index ) . removeClass( 'passive' ) . addClass( 'active' );

		// リセットボタン : 「言語、その他」のタブ時のみ表示
		$( '#reset' ) . removeClass( 'active passive' );
		if ( 3 === index ) {
			$( '#reset' ) . addClass( 'active' );
		}
		else {
			$( '#reset' ) . addClass( 'passive' );
		}

		// タブ番号をセット
		$( '#tab_menu_num' ) . attr( 'value' , index );
	});


	/**
	 * input データ検証
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
 	 * ※）この ID入力の検証 は php 側のデータ検証とあわせる必要あり
	 *
	 * @param string データ検証対象の文字列
	 * @returns string データ検証後の文字列
	 */
	 function inputValidation( value , type ) {
		/*
		 * \s	: 空白文字（半角スペース、\t、\n、\r、\f）すべて
		 * +	: 1文字以上
		 * g	: 最初だけでなくすべてが対象
		*/

		// 共通
		if( '' === value ){
			return value;
		}
		// スペース：削除
		value = value . replace( /\s+/g , '' );

		// 全角英数, 全角ハイフン：半角へ変換
		value = value . replace(/[ａ-ｚＡ-Ｚ０-９－]/g , function( s ) {
			return String .fromCharCode( s . charCodeAt( 0 ) - 0xFEE0 );
		});

		if ( 'analyticsID' === type ) {
			// Analytics ID は、英数数字 と ハイフン 以外削除
			value = value . replace( /[^a-zA-Z0-9\-]/g , '' ) . toUpperCase();
			// 小文字は大文字へ
			value = value . toUpperCase();
		}
		else {	// パブリッシャーID , 広告ユニット ID
			// 数字以外は削除
			value = value . replace( /[^0-9]/g , '' );
			//value = Math . abs( value ); // 数値の絶対値 は行わない（先頭 0 が削除されてしまうため ）
		}

		return value;
	}


	/**
	 * 値が変更されたた箇所を強調表示
	 *
	 * @param string element : 対象の要素
	 * @param string type : pubId , adUnit , AdUnitId
	 * @param string uniqueName : pubId, ad1InArticleAdUnitId, ad1DisplayAdUnitId, ...
	 */

	// uniqueName　と タイマーID の配列
	let timerArray 	= {};

	function showChange( element , type , uniqueName ) {
		// 現在の色情報を取得
		const BACK_GROUND_COLOUR	= $( '#aaih-settings-wrap .show-code' ) . css( 'background-color' );
		const COLOUR__PUB_ID		= $( '#aaih-settings-wrap table td.show-code .id' ) . css( 'color' );
		const COLOUR__AD_UNIT_ID	= $( '#aaih-settings-wrap table td.show-code .data' ) . css( 'color' );

		// 要素に対してエフェクトを付ける（背景色、文字色）
		$( element ) . css( 'transition' , '.5s' ) ;
		$( element ) . css( 'background' , '#ccc' ) ;
		$( element ) . css( 'color' , '#333' ) ;

		// 以下でタイマーセットして、時間になったら元に戻す
		// まず以前のタイマーがあれば一度cancel
		if ( uniqueName in timerArray ) {
			id = timerArray[ uniqueName ];
			clearTimeout( id );
			console.log( 'Clear Timer : ' + uniqueName + ' Id ' + id );
		}

		// タイマーセット : 時間が来たら 元の文字色、背景色に戻す
		id = setTimeout( function() {
			$( element ) . css( 'transition' ,'1s' ) ;
			$( element ) . css( 'background' , BACK_GROUND_COLOUR ) ;

			switch ( type ) {
				case 'pubId':
				case 'analyticsId':
					$( element ) . css( 'color', COLOUR__PUB_ID ) ;
					break;
				case 'adUnitType':
				case 'adUnitId':
					$( element ) . css( 'color', COLOUR__AD_UNIT_ID ) ;
					break;
				default:
					alert( 'showChange - noType');
			}
		}, 1500);
		console.log( 'Set Timer : ' + uniqueName + ' Id ' + id );

		// セットしたタイマーを配列に追加 : この配列でタイマーを覚えてcancel時に使う
		timerArray[ uniqueName ] = id;
	}

	/* -----------------------------------
	 * 広告選択 / 広告置き換え対応
	 * ----------------------------------- */
	// 広告選択
	const elementAdSelectOnOffTr		= 'tr.ad-select-onoff';
	const elementAdSelectOnOffCheckBox	= 'input.ad-select-onoff-checkbox';
	// 広告置き換え
	const elementAdReplacementOnOffTr			= 'tr.ad-replace';
	const elementAdReplacementOnOffCheckBox		= '.ad-replace-onoff-checkbox';

	// 共通
	const elementAdSelectBox			= 'select.ad-select';
	const elementAdSelectWarningParent	= 'div.check-wrap';
	const elementAdSelectWarning		= 'div.check-wrap .check';

	/**
	 * セレクトボックスの選択された広告に対する warning 表示切替
	 *
	 * - 表示する場合： passive があれば削除して（ソース的にわかるようにするだけのために）active 追加し fadeIn
	 * - 非表示にする場合： active を削除して fadeOut
	 *
	 * @param string type 'show' 表示する 'hide' 隠す
	 * @param int index 何番目の要素か
	 */
	function ad_select_check ( type , index ) {

		switch ( type ) {
			case 'show':
				// 選択された広告が無効の場合（pub-id か unit-id の入力がない場合 （disable の場合）
				if ( $( elementAdSelectBox ) . eq ( index) . hasClass( 'disable' ) ) {
					$( elementAdSelectWarning ) . eq ( index ) . removeClass('passive') . addClass( 'active' ) . hide() . fadeIn();
				}
				console.log( 'show warning ' + index );
				break;

			case 'hide':
				$( elementAdSelectWarning ) . eq ( index ) . removeClass( 'active' ) . fadeOut( 1000 );
				console.log( 'hide warning ' + index );
				break;

			default:
				alert( 'ad_select_warning : no case : ' + type );
		}
	}

	/*
	 * 広告選択 on / off 切り替え
	 *
	 * on / off が切り替わったら、class の on / off も切り替え
	 * チェックされたら true, チェックを外されたら false が返る
	 */
	$( elementAdSelectOnOffCheckBox ) . each( function( index , element ) {
		$( element ) . click( function() {
			// チェック状態の取得
			let onoff = $( this ) . prop( "checked" );
			console.log( 'onoff ' + index + ' : ' + onoff );

			// チェック状態の class を追加
			let onoffClass = ( true === onoff ? 'on' : 'off' );
			$( elementAdSelectOnOffTr ) . eq( index ) . removeClass( 'on off' ) . addClass( onoffClass );

			// Warning 表示
			// 設定 on にした時
			if ( true === onoff ) {
				ad_select_check ( 'show' , index );
			}
			// 設定 off にした時
			else {
				ad_select_check ( 'hide' , index );
			}
		});
	});

	/*
	 * 広告の置き換え対応 on / off 切り替え
	 *
	 * on / off が切り替わったら、class の on / off も切り替え
	 * チェックされたら true, チェックを外されたら false が返る
	 */

	$( elementAdReplacementOnOffCheckBox ) . click( function() {
		// チェック状態の取得
		let onoff = $( this ) . prop( "checked" );
		console.log( 'onoff : ' + onoff );

		// チェック状態の class を追加
		let onoffClass = ( true === onoff ? 'on' : 'off' );
		$( elementAdReplacementOnOffTr ) . removeClass( 'on off' ) . addClass( onoffClass );

		// replace 出来る個数の取得
		let maxNum = $( 'input.shortcode-replace-max-num' ) . val();

		// Warning 表示
		// 設定 on にした時 : 広告設定が3つあるので 3 を足す
		if ( true === onoff ) {
			for ( i = 0 ; i < maxNum ; i ++ ){
				ad_select_check ( 'show' , i + 3 );
			}
		}
		// 設定 off にした時
		else {
			for ( i = 0 ; i < maxNum ; i ++ ){
				ad_select_check ( 'hide' , i + 3 );
			}
		}
	});

	/*
	 * 広告選択 : 広告 Ad1, Ad2, ... 切り替え
	 *
	 * 広告選択が変わったら、セレクトボックス　と 次の要素 .check の class の enable / disable を切り替え
	 */
	$( elementAdSelectBox ) . each( function( index , element ) {
		$( element ) . change( function() {
			// 選択された Adxx を取得
			let selectAdNth = $( element ) . val();
			console.log( index + ' selectAdNth : ' + selectAdNth );

			// 選択された Ad の class ( enable / disable ) を取得
			// let elementOption = 'tr.ad-select-onoff select.ad-select option[value="' + selectAdNth + '"]';
			let elementOption = elementAdSelectBox + ' option[value="' + selectAdNth + '"]';
			let selectAdNthOptionClass = $( elementOption ) . attr( "class" );
			console.log( 'selectAdOptionClass : ' + selectAdNthOptionClass );

			// セレクトボックスの class 設定
			$( element ) . removeClass( 'enable disable') . addClass( selectAdNthOptionClass );

			// on/off 取得 : 広告選択と広告置き換えとで分ける
			let onoff;

			// 広告選択の場合
			if ( index < 3 ) {
				onoff = $( elementAdSelectOnOffCheckBox ) . eq( index ) . prop( "checked" );
				console.log( 'onoff adSelect' + index + ' : ' + onoff );
			}
			//　広告置き換え対応の場合
			else{
				onoff = $( elementAdReplacementOnOffCheckBox ) . prop( "checked" );
				console.log( 'onoff adReplacement: ' + onoff );
			}

			// Warning 表示
			// 設定 on 時
			if ( true === onoff ){
				if( $( element ) . hasClass( 'disable' ) ) {
					ad_select_check ( 'show' , index );
				}
				else {
					ad_select_check ( 'hide' , index );
				}
			}
			// 設定 off 時：何もしない
		});
	});


	/* -----------------------------------
	 * 広告コード入力のタブ切り替え（クリックによる）
	 *
	 * どのタブがクリックされたかを見て以下の class を追加
	 * クリックされたタブ	：active
	 * その他のタブ			：passive
	 * ----------------------------------- */

	$( '.ad-code-tab .tab' ) . click( function() {
		// 何番目かを取得
		var index = $( '.ad-code-tab .tab' ) . index( this );
		console.log( 'ad-code-tab : ' + index );

		// タブとタブに対するコンテンツ 全てを一旦 passive へ
		$( '.ad-code-tab .tab, .ad-code-all' ) . removeClass( 'active passive' ) . addClass( 'passive' );
		// タブ　active 設定
		$( this ) . removeClass( 'passive' ) . addClass( 'active' );
		// コンテンツ active 設定
		$( '.ad-code-all' ) .eq( index ) . removeClass( 'passive' ) . addClass( 'active' );

		// タブ番号をセット
		$( '#ad-code-tab-num' ) .attr( 'value', index );
	});


	/* -----------------------------------
	 * パブリッシャーID の入力 と コード表示への反映
	 * ----------------------------------- */
	$( 'input.pub-id' ) .on( 'input' , function() {
		// 入力が空文字の場合の文字列取得
		const AD_ID_NO_VALUE	= $( 'input#ad-id-no-value' ).val();

		// pubId 入力を取得
		let pubId = $( this ) . val();
		console.log( 'pubId 入力: ' + pubId );

		// データ検証
		pubId = inputValidation( pubId , 'pubId' );
		// 検証済のデータを入力に反映 : アドセンス自動広告側の入力にも反映
		$( 'input.pub-id' ) .each( function( index, element ) {
			$( element ) .val( pubId ) ;
		});
		console.log( 'pubId 検証後: ' + pubId );

		// pubId をコード内全ての pub id に反映 : アドセンス自動広告側の入力にも反映
		if ( '' === pubId) {
			pubId = AD_ID_NO_VALUE;
		}
		$( '.show-code .id-num' ) .each( function( index, element ) {
			$( element ) .text( pubId ) ;
		});

		/* -----------------------------------
		* コード内の表示効果付け
		----------------------------------- */
		// 現在のタブ番号を取得 : これにより pub id の入力について 広告コードの設定内か オプションページ内かを判別
		let tabNum = Number( $( '#tab_menu_num' ) . val() );
		console.log( 'tab-num : ' + tabNum);

		let element		= '';	// 効果付け対象
		let uniqueName 	= '';	// タイマーに対して唯一の名前を付ける

		switch ( tabNum ) {
			case 0:	// 一般設定 タブ
				// 広告コードの設定内で効果付け
				// 現在表示している AdNum の取得
				let adNum = Number( $( 'input#ad-code-tab-num' ) . val() ) + 1;
				let adNth = 'ad' + adNum;

				// 現在表示している AdNum の 広告ユニットを取得
				element = '.ad-code.' + adNth + ' input.ad-unit-select:checked';
				let adUnit = $( element ) . val();

				// 現在表示している Adxx の広告ユニット内のみで 表示効果を行う
				$( '.ad-code-all.active .show-code-all.active .id-num' ) .each( function( index, element ) {
					uniqueName 	= adNth + '-' + adUnit + '-pubId' + index;	// Adxx + AdUnit + 'PubId' + num
					showChange( element , 'pubId' , uniqueName );
				});
				break;

			case 2: // オプション タブ
				element 	= '.adsense-auto-ad .adsense-code .id-num';
				uniqueName 	= 'adsenseAutoAd-pubId';
				showChange( element , 'pubId' , uniqueName );
				break;

			default:
				// 特に何もなしで OK
		}
	});

	/**
	 * 広告ユニットID の コードへの反映表示
	 *
	 * @param string parentElement 親要素 '.ad-code-all.ad' + adNum
	 * @param string adNum	: 1, 2, 3, 4, 5
	 * @param string adUnit : 広告ユニット
	 * @param string effect	: 省略可。'no'で視覚的効果なし
	 *
	 * @return void
	 */
	function adUnitIdDisplayInCode( parentElement , adNum , adUnit , effect ) {
		// 広告ユニットIDの取得
		let adUnitIdElement = parentElement + ' input.' + adUnit + '-id';
		let adUnitId		= $( adUnitIdElement ) . val();

		// 入力が無い場合の文字列取得
		const AD_ID_NO_VALUE	= $( '#ad-id-no-value' ) . val();

		// データ検証
		adUnitId = inputValidation( adUnitId , 'adUnitId' );
		$( adUnitIdElement ) . val( adUnitId );	// 検証したデータを反映

		// 空文字は xxxxx を表示（コード内表示用）
		if ( ''=== adUnitId) {
			adUnitId = AD_ID_NO_VALUE;
		}

		// 広告ユニットIDを対象コードへ反映 .show-code-all.display
		adUnitIdElement_inCode = parentElement + ' .show-code-all.' + adUnit + ' .data-ad-slot-num';
		$( adUnitIdElement_inCode ) . text( adUnitId ) ;

		if ( 'no' !== effect ) {
			let uniqueName = 'ad'+ adNum + adUnit + 'adUnitId';
			showChange( adUnitIdElement_inCode , 'adUnitId' , uniqueName );
		}
	}


	/* -----------------------------------
	 * 広告ユニットの選択切り替え
	 *
	 * 広告ユニットの選択切り替え で以下を行う。
	 * 1) 選択された広告ユニットに対して、広告ユニットID の 入力切替
	 * 2) 広告の形状（ ad-data-ad-format ）の表示非表示
	 * 3) コードの表示切替： in-article / display / multiplex
	 * 4) 広告ユニットID を コード表示へ反映 ad_unit_id__in_article
	 * 5) 広告ユニット説明の文言変更
	 *
	 * ----------------------------------- */
	$( '.ad-code-all' ) . each( function( index ) {

		let adNum 			= index + 1;	// 1, 2, ...
		let parentElement 	= '.ad-code-all.ad' + adNum;
		let select			= parentElement + ' select.ad-unit-select';

		// 広告ユニット選択の値が変わった場合
		$( select ) . change( function() {
			let adUnit = $( this ) . val();	// 広告ユニット選択の値取得 in_article / display / multiplex
			if ( 'in_article' === adUnit ) {
				adUnit = 'in-article';
			}
			console.log( 'adNum ' + adNum + ' changed to ' + adUnit );

			/*
			 * -----------------------------------
			 * 1) 選択された 広告ユニットに対して、広告ユニットID の 入力切替
			 * -----------------------------------
			 */

			let inputAdUnitID 	= parentElement + ' .ad-unit-id-item';
			let activateElement	= parentElement + ' .ad-unit-id-item.' + adUnit;

			console.log( 'activateElement:' + activateElement );

			$( inputAdUnitID ) . removeClass( 'passive active' ) . addClass( 'passive' );
			$( activateElement ) . removeClass( 'passive' ) . addClass( 'active' ) . hide() . fadeIn();


			/*
			 * -----------------------------------
			 * 2) 広告の形状（ ad-data-ad-format ）の表示非表示
			 * -----------------------------------
			 * 広告ユニットで ディスプレイ広告が選択されたときのみ表示する。
			 *
			 * - ディスプレイ広告が選択されたとき	：class に active を追加
			 * - それ以外：class の active を削除し、代わりに passive を追加
			 */

			let dataAdFormat = parentElement + ' table.ad-data-ad-format';
			$( dataAdFormat ) .removeClass( 'active passive' );
			if ( 'display' === adUnit ) {
				$( dataAdFormat ) .addClass( 'active' );
				$( dataAdFormat ) .hide() .fadeIn();
			}
			else {
				$( dataAdFormat ) .addClass( 'passive' );
			}


			/*
			 * -----------------------------------
			 * 3) コードの表示切替： in-article / display / multiplex
			 * -----------------------------------
			 * - 広告ユニットの選択により、対応した広告コードの class に active を追加。
			 * - それ以外：class の active を削除し、代わりに passive を追加
			 */

			let codeAll = parentElement + ' div.show-code-all';

			// 広告の形状：表示／表示 : 一旦すべて passive を設定
			$( codeAll ) .removeClass( 'active passive' ) . addClass( 'passive' );

			// 選択された広告ユニットのみ active を設定
			let codeAll__unit	= codeAll + '.' + adUnit;
			$( codeAll__unit ) .removeClass( 'passive' ) . addClass( 'active' );
			$( codeAll__unit ) .hide() .fadeIn();

			console.log( 'adNum ' + adNum + ' code display: changed to ' + adUnit );


			/*
			 * -----------------------------------
			 * 4) 広告ユニットID を コード表示へ反映 ( data-ad-slot-num )
			 * -----------------------------------
			 * 広告ユニットの選択により、対応した広告コードの id を コード表示へ反映
			*/
			adUnitIdDisplayInCode( parentElement , adNum , adUnit , 'no' ); // エフェクトは付けず

			/*
			 * -----------------------------------
			 * 5) 広告ユニット説明の文言変更
			 * -----------------------------------
			 * 広告ユニットの選択により、対応した説明表示に切り替え
			*/
			let adUnitExplanation = parentElement + ' .explain .ex';
			$( adUnitExplanation ) . removeClass( 'active passive' ) . addClass( 'passive' );

			adUnitExplanation = adUnitExplanation + '.' + adUnit;
			console.log( 'adUnitExplanation:' + adUnitExplanation );
			$( adUnitExplanation ) . removeClass( 'passive' ) . addClass( 'active' );
			$( adUnitExplanation ) . hide() . fadeIn();
		});
	});

	/* -----------------------------------
	 * 広告ユニットID 入力 を、コード表示に反映 ( data-ad-slot-num )
	 * ----------------------------------- */
	$( '.ad-code-all' ) . each( function( index ) {

		let adNum 			= index + 1;	// 1, 2, ...
		let parentElement	= '.ad-code-all.ad' + adNum;

		//広告ユニットIDの値が変わった場合
		$( 'input.ad-unit-id' ) . each( function( index ) {
			const adUnitArray = new Array( 'in-article' , 'display' , 'multiplex' );

			let adUnit 			= adUnitArray[ index ];
			let adUnitIdElement	= parentElement + ' input.' + adUnit + '-id';

			$( adUnitIdElement ) .on( 'input' , function() {
				adUnitIdDisplayInCode( parentElement , adNum , adUnit );
			});
		});
	});


	/* -----------------------------------
	 * 広告の形状 を、コード表示に反映 ( data-ad-format )
	 * ----------------------------------- */
	$( '.ad-code-all select.ad-data-ad-format' ) .each( function(index, element) {

		// 広告の形状の値が変わった場合
		$( element ) .change( function() {
			let adNum = index + 1;	// 1, 2, ...
			let adUnitType = $( element ) .val();	// 広告形状の取得 auto / rectangle / vertical / horizontal

			// コード表示に反映
			dataAdSlotNum = '.ad-code-all' + '.ad' + adNum + ' .data-ad-format-type';
			$( dataAdSlotNum ) .text( adUnitType ) ;

			let uniqueName = 'ad' + adNum + 'adUnitType';
			showChange( dataAdSlotNum , 'adUnitType' , uniqueName );
		});

	});

	/**
	 * グーグルアナリテイクス ID 表示
	 * ID入力をコード表示に反映
	 */
	$( 'input.analytics-id' ) . on( 'input' , function() {
		// ID 入力がない場合の文字列
		const ANALYTICS_ID_NOTHING_ALL	= $( '#analytics-id-nothing-all' ) . val();	// UA-xxxxx-xx/G-xxxxx

		// ID 入力を取得
		let analyticsID = $( this) . val();
		console.log( 'analyticsID 検証前: ' + analyticsID);
		// ID 入力　データ検証
		analyticsID 	= inputValidation( analyticsID , 'analyticsID' );
		// 検証結果を反映
		$( this) . val( analyticsID );
		console.log( 'analyticsID 検証後: ' + analyticsID);

		// アナリティクス ID
		let analyticsId = '' === analyticsID ? ANALYTICS_ID_NOTHING_ALL : analyticsID;

		// 表示コードにID反映
		let element = '.analytics.options .show-code .id';
		$( element ) .text( analyticsId );
		showChange( element , 'analyticsId' , 'analyticsId' );
	});

	/* -----------------------------------
	* コピーボタン
	*
	* ショートコードを表示する横のコピーボタン。
	* ショートコードをコピーしやすいよう、ボタンが押されたら自動でコピーを行い
	* 視覚的に明確になるよう、コピーされた旨の表示を行う
	*
	* ----------------------------------- */

	$( '.copybtn' ) . each( function( index , element ) {
		let adNum = index + 1;

		let parentElement 		= '.ad-code.ad' + adNum;
		let copyBtnElement		= parentElement + ' .copybtn';
		let shortCodeElement 	= parentElement + ' pre.shortcode';

		$( copyBtnElement ) .on( 'click' , function() {
			let shortCode = $( shortCodeElement ) . text();
			// アラートメッセージ表示として、クラスを .copymsg と、ボタンのvalueと同じ値をセット
			let strName = $( element ) .attr( 'title' );

			console.log( 'shortCode:' + shortCode );

			valSlideDown	= 250;
			valDelay		= 1250;
			valSlideUp		= 250;

			// 	document . execCommand( 'copy' ) は 非推奨になったようなので Clipboard API を使う
			navigator . clipboard . writeText( shortCode )
			.then(
				success	=> $('.copymsg.success.' + strName ) . slideDown( valSlideDown ) . delay( valDelay ) . slideUp( valSlideUp ),
				error	=> $('.copymsg.error.' + strName ) . slideDown( valSlideDown ) . delay( valDelay ) . slideUp( valSlideUp ),
			);
		});
	});
});

/**
 * 確認ダイアログの返り値によりフォーム送信
*/
function resetAllDataChk() {

	// 確認ダイアログ表示
	let element = document . getElementById( 'reset-all-settings-msg' );
	let msg1 = element . value;
	let msg2 = element . title;
	let msg3 = element . placeholder;

	var flag = confirm( msg1 + '\n' + msg2 );

	if ( true === flag ) {
		var flag = confirm( msg3 );
	}else {
		flag = false;
	}
	return flag;
}
