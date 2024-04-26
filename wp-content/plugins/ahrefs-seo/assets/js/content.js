/**
 * Content audit table page handlers.
 */

if ( jQuery( '#content_table, .first-audit-wrap' ).length ) {
	// content tips.
	( function($) {
		"use strict";
		/* Click on caption with expand icon */
		$( document ).on(
			'click',
			'.caption-toggle',
			function(e) {
				$( this ).closest( '.subitem' ).toggleClass( 'expanded' );
				return false;
			}
		)

		window.content_tips = {
			state : {},
			/**
			 * Get last state for a block using ID.
			 *
			 * @param id
			 */
			get_last_state : function( id ) {
				return content_tips.state[id] || false;
			},
			/**
			 * Set last state for a block using ID.
			 *
			 * @param id
			 * @param state
			 */
			set_last_state : function( id, state ) {
				content_tips.state[id] = state;
			},
			/**
			 * Render multi block with visible single blocks.
			 *
			 * @param (jQuery) $block
			 * @param bool visibility
			 * @return bool Was something updated.
			 */
			render : function( $block, visibility ) {
				var result     = false;
				var last_state = content_tips.get_last_state( $block.attr( 'id' ) );
				if ( content_tips.to_visible( visibility ) !== content_tips.to_visible( last_state ) ) {  // use only visible items and ignore different order of fields.
					result           = true;
					var $multi_block = $block.find( '.tip-multi .subitems' ); // block tip-multi content.
					// what was expanded in tip-multi?
					var expanded_list = [];
					$multi_block.find( '.subitem.expanded' ).each(
						function() {
							expanded_list.push( $( this ).data( 'id' ) );
						}
					)
					$multi_block.empty();

					// create new content.
					var visible_count  = 0;
					var what_to_search = $block.find( '.tip-single .content-tip-wrap' ).length ? '.tip-single .content-tip-wrap' : '.tip-single .ahrefs-content-tip'; // use wrapper class if it exists.
					$block.find( what_to_search ).each(
						function() {
							var id = $( this ).data( 'id' );
							if ( visibility[id] || false ) {
								visible_count++;
								$( this ).show();
								// add content to tip-multi block.
								var id       = $( this ).data( 'id' );
								var $header  = $( this ).find( '.caption' );
								var $text    = $( this ).find( '.text' );
								var $buttons = $( this ).find( '.buttons' );

								var $subitem = $( '<div class="subitem"></div>' );
								$subitem.data( 'id', id );
								if ( ( -1 !== expanded_list.indexOf( id ) ) || ! expanded_list.length ) { // something was expanded before or expand first item by default.
									$subitem.addClass( 'expanded' );
									expanded_list.push( id );
								}
								$subitem.append( $( '<a href="#" class="caption caption-toggle"></a>' ).text( $header.text() ) );
								$subitem.append( $text.clone() );
								if ( $buttons.length ) {
									$subitem.append( $buttons.clone() );
								}
								$multi_block.append( $subitem );
							} else {
								$( this ).hide();
							}
						}
					);
					if ( visible_count > 1 ) {
						$block.addClass( 'multi' );
					} else {
						$block.removeClass( 'multi' );
					}
				}
				content_tips.set_last_state( $block.attr( 'id' ), visibility );
				return result;
			},
			/**
			 * Get id of visible blocks.
			 *
			 * @param $block
			 * @return string[]
			 */
			get_visible : function( $block ) {
				var result = [];
				var state  = content_tips.get_last_state( $block.attr( 'id' ) );
				for ( var id in state ) {
					if ( state[id] ) {
						result.push( id );
					}
				}
				return result;
			},
			/**
			 * Get visible pairs as key:visibility and return only visible keys.
			 *
			 * @param obj
			 * @returns Array Visible keys, as ordered array.
			 */
			to_visible: function( obj ) {
				var result = [];
				if ( 'object' === typeof obj ) {
					var keys = Object.keys( obj );
					var obj2 = {};
					keys     = keys.sort();
					for ( var key in keys ) {
						if (obj[keys[key]]) {
							result.push( keys[key] );
						}
					}
					return result.toString();
				}
				return JSON.stringify( obj );
			},
			/**
			 * Render content of stop block using existing messages.
			 *
			 * @return bool Was the multi block updated.
			 */
			render_block_stop: function() {
				var $stop_block_items = $( '.ahrefs_messages_block[data-type="stop"] .tip-single > div' );
				var ids               = {};
				$stop_block_items.map( function() { ids[ $( this ).data( 'id' ) ] = true; } ); // all existing blocks are active.
				var result = content_tips.render( $( '#content_stop_errors' ), ids );
				$( '#content_stop_errors' ).show(); // show after first render only.
				return result;
			},
			clean_stop_block: function() {
				$( '.ahrefs_messages_block[data-type="stop"] .tip-single' ).empty();
				content_tips.render_block_stop();
			},
			update_tips_block: function() {
				$( '.tip-new-audit-message' ).remove();
				var ids             = content_tips.get_last_state( 'content_tips_block' );
				ids[ 'last-audit' ] = false;
				content_tips.render( $( '#content_tips_block' ), ids );
			}
		};
	})( jQuery );

	// content audit screen.
	( function($) {
		"use strict";

		$( document ).on(
			'click',
			'a.submit-include',
			function(e) {
				e.preventDefault();
				var post_id = $( this ).data( 'id' );
				content.ajax_set_page_active_or_recheck( post_id, 1 );
				$( this ).closest( 'span' ).hide();
				return false;
			}
		)
		$( document ).on(
			'click',
			'a.submit-exclude',
			function(e) {
				e.preventDefault();
				var post_id = $( this ).data( 'id' );
				content.ajax_set_page_active_or_recheck( post_id, 0 );
				$( this ).closest( 'span' ).hide();
				return false;
			}
		)
		$( document ).on(
			'click',
			'a.submit-recheck',
			function(e) {
				e.preventDefault();
				var post_id = $( this ).data( 'id' );
				content.ajax_recheck_page( post_id );
				$( this ).closest( 'span' ).hide();
				return false;
			}
		)
		$( document ).on(
			'click',
			'a.approve-keywords',
			function(e) {
				e.preventDefault();
				var post_id = $( this ).data( 'post' );
				content.ajax_approve_keyword( post_id, $( this ).closest( 'span' ) );
				$( this ).closest( 'span' ).hide();
				return false;
			}
		)
		$( document ).on(
			'click',
			'a.button-keyword-change',
			function(e) {
				e.preventDefault();
				var post_id     = $( this ).closest( 'tr' ).data( 'id' );
				var $row_action = content.$table.find( 'a.change-keywords[data-post="' + post_id + '"]' );
				if ( $row_action.length ) {
					$row_action.trigger( 'click' );
				}

				return false;
			}
		)
		$( document ).on(
			'click',
			'a.button-keyword-approve',
			function(e) {
				e.preventDefault();
				if ( ! $( this ).hasClass( 'disabled' ) ) {
					var post_id = $( this ).closest( 'tr' ).data( 'id' );
					content.ajax_approve_keyword( post_id, $( this ).closest( 'tr' ).prev().prev().find( 'a.approve-keywords' ) );
				}
				return false;
			}
		)
		$( document ).on(
			'click',
			'a.badge-keyword',
			function(e) {
				e.preventDefault();
				return false;
			}
		)
		// open keyword from data-keyword attribute or from current table cell.
		$( document ).on(
			'click',
			'a.ahrefs-open-keyword, a.ahrefs-open-all-keywords',
			function(e) {
				e.preventDefault();
				var keyword   = $( this ).data( 'keyword' );
				var base_link = 'https://app.ahrefs.com/keywords-explorer/google/' + content.keyword_country_code + '/overview?keyword=';
				if (keyword) {
					window.open( base_link + encodeURIComponent( keyword ), '_blank' );
				} else {
					var $td = $( this ).closest( 'td' ).find( '.content-post-keyword' ).clone();
					$td.find( 'a' ).remove();
					var keywords = $td.text().replace( /\n/g, "," ).split( "," ).map( function(e) {return escape_html( e.trim() ) } ).filter( function(e) { return '' !== e } );

					if (keywords) {
						keywords.map(
							function( kw ) {
								window.open( base_link + encodeURIComponent( kw ), '_blank' );
							}
						);
					}
				}
			}
		)
		// [Use as anchor text...] button on expanded view.
		$( document ).on(
			'click',
			'a.new-anchors-submit-button',
			function(e) {
				e.preventDefault();
				var action = $( this ).closest( '.more-page-content' ).find( '.form-url' ).val();
				var $form  = $( '<form/>' ).attr( 'method','post' ).attr( 'action',action );
				$form.append( $( this ).closest( '.more-page-content' ).find( 'input' ).clone() );
				$( 'body' ).append( $form );
				$form.trigger( 'submit' );
			}
		)
		// close message.
		$( document ).on(
			'click',
			'button.close-current-message',
			function(e) {
				e.preventDefault();
				if ( $( this ).closest( '#wordpress_api_error' ).length) {
					$( '#wordpress_api_error' ).hide(); // just hide is enough.
				} else {
					$( this ).closest( '.ahrefs-content-tip, .notice' ).remove(); // remove the message.
				}
				return false;
			}
		)
		$( document ).on(
			'click',
			'button.notice-dismiss',
			function(e) {
				if ( $( this ).closest( '#audit_delayed_google' ).length) {
					$( '#audit_delayed_google' ).hide().append( $( this ).closest( '.notice' ).clone() ); // hide block and recreate notice.
					$( this ).closest( '.notice' ).remove();
					return false;
				}
			}
		)
		// mobile table mode: hide any already opened details on "Show more details" icon clicked.
		$( document ).on(
			'click',
			'button.toggle-row',
			function(e) {
				content.hide_more_info_items();
			}
		)
		window.content_svg_clicked = function(e) {
			var tab = $( e ).data( 'tab' ) || '';
			console.log( tab );
			if ( '' !== tab ) {
				var $item = $( '.tab-content-item[data-tab="' + tab + '"]' );
				if ( $item.length ) {
					document.location = $item.attr( 'href' );
				}
			}
			return false;
		};

		$( document ).on(
			'click',
			'p.chart-legend-item',
			function(e) {
				content_svg_clicked( e.target );
				return false;
			}
		)
		var entityMap = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#39;',
			'/': '&#x2F;',
			'`': '&#x60;',
			'=': '&#x3D;'
		};

		var escape_html = function(string) {
			return String( string ).replace(
				/[&<>"'`=\/]/g,
				function (s) {
					return entityMap[s];
				}
			);
		}
		// Close content tip.
		$( document ).on(
			'click',
			'.suggested-tip-close-button, #keywords_tip_got_it', // 'x' button on top-right corner or [Got it] button.
			function() {
				var is_multi = $( this ).closest( '.tip-multi' ).length > 0;
				var tip_ids  = is_multi ? $( this ).closest( '.tip-multi' ).find( '.subitems .subitem' ).map( function() { return $( this ).data( 'id' ); } ).toArray() : $( this ).closest( '.ahrefs-content-tip' ).data( 'id' );
				$.post(
					ajaxurl,
					{
						action: 'ahrefs_seo_content_tip_close',
						_wpnonce: $( '#table_nonce' ).val(),
						_: Math.random(),
						tip_id: tip_ids,
					}
				);
				if ( $( this ).closest( '.content-tip-wrap' ).length ) { // single tip with wrapper.
					$( this ).closest( '.content-tip-wrap' ).hide();
				} else if ( is_multi ) { // multi tip.
					$( this ).closest( '.tip-multi' ).find( '.tip-single .content-tip-wrap' ).hide(); // hide all single messages.
					content_tips.render( $( this ).closest( '.tip-multi' ).parent(), [] ); // update multi block with no visible items.
				} else { // single tip.
					$( this ).closest( '.ahrefs-content-tip' ).hide();
				}
				return false;
			}
		)
		$( document ).on(
			'click',
			'.content_tip_show_suggested',
			function() {
				return content.switch_to_keywords_type( '', 0 );
			}
		)
		$( document ).on(
			'click',
			'.content_tip_show_duplicated',
			function() {
				return content.switch_to_keywords_type( '', 3 );
			}
		)
		$( document ).on(
			'click',
			'.content_tip_show_dropped',
			function() {
				return content.switch_to_keywords_type( 'dropped', '' );
			}
		)
		// Resets the bulk actions when the search button is clicked.
		$( document ).on(
			'mousedown',
			'.search-box input[type="search"], .search-box input[type="submit"]',
			function() {
				$( 'select[name^="action"]' ).val( '-1' );
			}
		);
		// Submit search when Enter pressed at search input.
		$( document ).on(
			'keypress',
			'.search-box input[type="search"]',
			function (e) {
				if ( 13 === e.which ) {
					$( '.search-box input[type="submit"]' ).trigger( 'click' );
					return false;
				}
			}
		);
		// Submit search when Enter pressed at keywords popup.
		$( document ).on(
			'keypress',
			'input.keyword-input',
			function (e) {
				if ( 13 === e.which ) {
					$( this ).closest( '.ahrefs-seo-modal-keywords' ).find( '#ahrefs_seo_keyword_submit' ).trigger( 'click' );
					return false;
				}
			}
		);
		// Select first item in table with the same value.
		$( document ).on(
			'keyup input',
			'input.keyword-input',
			function (e) {
				content.keyword_set_source( content_strings.source_id.manual );
				content.keyword_set_selected_in_table( $( this ).val() );
			}
		);
		// Download keywords results at expanded view table as csv file.
		$( document ).on(
			'click',
			'a.positions-export-button',
			function (e) {
				e.preventDefault();
				var $target  = $( this ).closest( '.more-content' ).find( '#csv_data' );
				var text     = '\ufeff' + $target.text();
				var filename = $target.data( 'name' );
				var element  = document.createElement( 'a' );
				element.setAttribute( 'href', 'data:text/plain;charset=utf-8,' + encodeURIComponent( text ) );
				element.setAttribute( 'download', filename );

				element.style.display = 'none';
				document.body.appendChild( element );

				element.click();

				document.body.removeChild( element );
				return false;
			}
		);
		// Positions table: show 10 more clicked.
		$( document ).on(
			'click',
			'a.positions-show-more',
			function() {
				if ( content.positions_table ) {
					var len = content.positions_table.page.len();
					content.positions_table.page.len( 10 + len ).draw();
					content.positions_table_update_footer( false ); // check if more items exists or hide 'Show 10 more' link.
				}
				return false;
			}
		);
		$( document ).on(
			'click',
			'a.positions-check-serp',
			function() {
				var keyword   = $( this ).data( 'keyword' );
				var base_link = 'https://www.google.com/search?q=';
				if (keyword) {
					window.open( base_link + encodeURIComponent( keyword ), '_blank' );
				}
				return false;
			}
		);

		window.content = {
			$table   : $( '#content_table' ),
			$form    : $( '#content_table' ).closest( 'form' ),
			lang     : $( '#current_lang' ).val(),
			keyword_data_table: null, // DataTable instance with keywords.
			keyword_data_set: [], // Source data set for keywords table.
			ping_timer: null, // ping timer used with setInterval/clearInterval.
			ping_next: null, // ping timer used with setTimeout/clearTimeout.
			ping_running: false, // ping timer used with setInterval/clearInterval.
			ping_interval: 120, // ping interval, seconds.
			items_to_flash: [], // array of $items to flash after table will be updated.
			no_rows_message: content_strings.no_keywords_info, // No keywords info available.
			last_progress_percents : null, // progress position in percents.
			nonce_refresh_count: 0, // number of refresh calls without success.
			default_params : function( need_update_tabs ) {
				var params = {};
				content.$table.find( 'input.table-query' ).each(
					function() {
						params[ $( this ).data( 'name' ) || '' ] = $( this ).val() || '';
					}
				);
				if ( 'undefined' !== typeof( need_update_tabs ) && need_update_tabs ) {
					params['update_tabs'] = 1;
				}
				params['ahrefs_lang'] = content.lang;
				return params;
			},
			// show notice: text is string or array, html is string ot empty, id is string.
			show_notice : function( text, html, id ) {
				if ( 'string' !== typeof id || '' === id) {
					id = 'info';
				}
				var $item = $( '#' + id );
				if ( $item.length ) {
					$item.remove();
				}
				var $block = $( '<div class="notice notice-info is-dismissible"></div>' );
				$block.attr( 'id', id );
				if ( 'string' === typeof( text ) && '' !== text ) {
					var $p = $( '<p></p>' );
					$p.text( text );
					if ( html ) {
						$p.append( '&nbsp;' + html );
					}
					$block.append( $p );
				} else {
					for ( var k in text ) {
						if ( '' !== text[k] ) {
							$block.append( $( '<p></p>' ).append( text[k] ) );
						}
					}
					if ( html ) {
						if ( text ) {
							$block.append( '&nbsp;' );
						}
						$block.append( html );
					}
				}
				$( '.ahrefs_messages_block[data-type="api-messages"]' ).prepend( $block );
				$( document ).trigger( 'wp-updates-notice-added' );
				$item = $( '#' + id );

				setTimeout( function() { $item.addClass( 'item-flash' ); }, 100 );
				$( 'html, body' ).animate( { scrollTop: $item.offset().top - 60 } );
			},
			show_notices : function( messages ) {
				content.show_notice( messages, 'info' );
			},
			show_notice_with_reload : function() {
				var $block = $( '#wordpress_api_error' );
				if ( ! $block.is( ':visible' )) {
					$block.show();
				}
			},
			hide_notice_with_reload : function() { // called on successful ping requests.
				var $block = $( '#wordpress_api_error' );
				if ( $block.is( ':visible' )) {
					$block.hide();
				}
			},
			show_notice_oops : function( text ) {
				if ( 'undefined' === typeof text || '' === text ) {
					content.show_notice( content_strings.notice_oops, '', 'notice_oops' ); // 'Oops, there was an error. Please try again.'
				} else {
					content.show_notice( text, '', 'notice_oops' );
				}
			},
			/** Create html from array with errors */
			prepare_error_message : function( messages ) {
				var result = '';
				for ( var title in messages ) {
					var $title   = $( '<span class="message-expanded-title"></span>' ).text( title );
					var $content = $( '<span class="message-expanded-text"></span>' ).text( messages[title] );
					var $div     = $( '<div class="message-expanded-wrap"></div>' ).append( $title ).append( '<a href="#" class="message-expanded-link">' + content_strings.show_details + '</a>' ).append( $content ); // '(show details)'.
					result      += $div.get( 0 ).outerHTML;
				}
				return result;
			},
			search_string_get : function() {
				return content.$form.find( 'input[name="last_search"]' ).val() || '';
			},
			search_string_set : function( value ) {
				content.$form.find( 'input[name="last_search"]' ).val( value );
			},
			tab_string_get : function() {
				return content.$form.find( 'input[name="tab"]' ).val();
			},
			tab_string_set : function( value ) {
				content.$form.find( 'input[name="tab"]' ).val( value );
			},
			date_string_get : function() {
				return content.$form.find( 'input[name="m"]' ).val();
			},
			date_string_set : function( value ) {
				content.$form.find( 'input[name="m"]' ).val( value );
			},
			cat_string_get : function() {
				return content.$form.find( 'input[name="cat"]' ).val();
			},
			cat_string_set : function( value ) {
				content.$form.find( 'input[name="cat"]' ).val( value );
			},
			author_string_set : function( value ) {
				content.$form.find( 'input[name="author"]' ).val( value );
			},
			keywords_string_set : function( value ) {
				content.$form.find( 'input[name="keywords"]' ).val( value );
			},
			reason_string_set : function( value ) {
				content.$form.find( 'input[name="reason"]' ).val( value );
			},
			order_string_set : function( value ) {
				content.$form.find( 'input[name="order"]' ).val( value );
			},
			orderby_string_set : function( value ) {
				content.$form.find( 'input[name="orderby"]' ).val( value );
			},
			page_string_set : function( value ) {
				content.$form.find( 'input[name="page"]' ).val( value );
			},
			loader_show : function() {
				content.$form.find( '#table_loader' ).show();
			},
			loader_hide : function() {
				content.$form.find( '#table_loader' ).hide();
			},
			items_to_flash_add : function( $item_to_flash ) {
				content.items_to_flash.push( $item_to_flash );
			},
			items_maybe_flash : function() {
				if ( content.items_to_flash.length ) {
					var $items             = content.items_to_flash;
					content.items_to_flash = [];
					$items.forEach( function($item) { $item.removeClass( 'item-flash' ).addClass( 'long' ); } );
					setTimeout( function() { $items.forEach( function($item) { $item.addClass( 'item-flash' ); } ); }, 100 );
				}
			},

			update_active_filters: function() {
				content.$form.find( '#filter-by-date, #cat, #author, #keywords, #reason' ).find( 'option:selected' ).addClass( 'current' ).siblings( 'option' ).removeClass( 'current' );
			},
			maybe_update_audit_header: function( data ) {
				var in_progress = $( '#content_audit_status' ).hasClass( 'in-progress' );
				if ( data['in_progress'] && ! in_progress ) {
					$( '#content_audit_status, #content_audit_cancel' ).addClass( 'in-progress' );
					ahrefs_progress.move_to( $( '#content_audit_status .audit-progressbar' ), null );
					content.last_progress_percents = 0;
				} else if ( ! data['in_progress'] && in_progress ) {
					$( '#content_audit_status, #content_audit_cancel' ).removeClass( 'in-progress' );
					ahrefs_progress.move_to( $( '#content_audit_status .audit-progressbar' ), null );
					content.last_progress_percents = 0;
					content.ping();
				}
				if ( data['in_progress'] ) {
					if ( null === content.last_progress_percents ) {
						content.last_progress_percents = 1.0 * ( $( '#content_audit_status .audit-progressbar .position' ).data( 'position' ) || 0 );
					}
					if ( content.last_progress_percents < data['percents'] ) {
						ahrefs_progress.move_to( $( '#content_audit_status .audit-progressbar' ), data['percents'] );
						content.last_progress_percents = data['percents'];
					}
				}
				var $hint    = $( '.ahrefs-header .header-hint' );
				var new_last = data['last_time'].map( function(i) {return '<span>' + i + '</span>';} ).join( ' ' );
				if ( $hint.html() !== new_last ) {
					$hint.html( new_last );
				}
			},
			load_content_details: function( post_id, $tr, version ) {
				$.ajax(
					{
						url: ajaxurl,
						dataType: 'json',
						method: 'post',
						data: {
							_wpnonce: content.$form.find( '#table_nonce' ).val(),
							action: 'ahrefs_seo_content_details',
							id: post_id,
							ver: version,
						},
						success: function (response) {
							// must hide loader: replace td content.
							if ( response && response['data'] ) {
								$tr.find( 'td' ).html( '<div class="more-content">' + response['data'] + '</div>' );
								content.init();
							} else {
								$tr.find( 'td' ).html( 'No details available.' );
							}
						},
						error: function (jqXHR, exception) {
							console.log( jqXHR, exception );
							$tr.find( 'td' ).html( content_strings.notice_oops ); // 'Oops, there was an error while loading the details. Please try again.'.
						}
					}
				);
			},
			/**
			 * Set post is active or inactive or recheck the post.
			 *
			 * @param int post_id
			 * @param bool|null is_active 0, 1 or null is not applicable.
			 * @param bool|null recheck true or null is not applicable.
			 */
			ajax_set_page_active_or_recheck: function( post_id, is_active, recheck ) {
				content.loader_show();
				window.setTimeout(
					function() {
						$.ajax(
							{
								url: ajaxurl,
								dataType: 'json',
								method: 'post',
								data: $.extend(
									content.default_params(),
									{
										_wpnonce: content.$form.find( '#table_nonce' ).val(),
										action: 'ahrefs_seo_content_set_active',
										id: post_id,
										active: is_active, // set post is active or inactive.
										recheck: recheck, // recheck noindex/non-canonical/redirected page.
										_ : Math.random(), // to omit possibly cache.
									}
								),

							success: function (response) {
								if ( response.success ) {
									if ( ! recheck ) {
										// update current view.
										content.update( content.default_params( true ) );
									} else {
										content.hide_more_info_items();
										content.loader_hide();
										content.ping();
									}
								} else {
									var message = '';
									if ( response.data && response.data.message ) {
										message = response.data.message;
									}
									if ( response.data && response.data.messages ) {
										content.show_notice( response.data.messages, '' );
									} else {
										content.show_notice_oops( message );
									}
									// update current view.
									content.update( content.default_params( true ) );
								}
								if ( is_active ) {
									content.ping();
								}
							},
								error: function (jqXHR, exception) {
									console.log( jqXHR, exception );
									content.show_notice_oops();
									content.loader_hide();
									content.maybe_refresh_nonce( jqXHR.status );
								}
							}
						);
					},
					1
				);
			},
			// recheck noindex/non-canonical/redirected page status.
			ajax_recheck_page: function( post_id ) {
				content.ajax_set_page_active_or_recheck( post_id, null, true );
			},
			/**
			 * Approve post keyword.
			 *
			 * @param int post_id
			 */
			ajax_approve_keyword: function( post_id, $approve_link ) {
				content.loader_show();
				// disable "Approve" button inside the opened suggestions (if opened and exists).
				var $button = $( 'tr.more-info-tr[data-id="' + post_id + '"] a.button-keyword-approve' );
				if ( $button.length ) {
					$button.addClass( 'disabled' );
				}
				// send request.
				window.setTimeout(
					function() {
						$.ajax(
							{
								url: ajaxurl,
								dataType: 'json',
								method: 'post',
								data: $.extend(
									content.default_params(),
									{
										_wpnonce: content.$form.find( '#table_nonce' ).val(),
										action: 'ahrefs_seo_content_approve_keyword',
										post: post_id,
										_ : Math.random(), // to omit possibly cache.
									}
								),

							success: function (response) {
								if ( response.success ) {
									// update current view, but do not remove approved item from suggested only view.
									content.ping();
									content.loader_hide();
								} else {
									var message = content_strings.action_failed; // 'Action failed. Please try again or reload a page.'.
									if ( response.data && response.data.message ) {
										message = response.data.message;
									}
									if ( response.data && response.data.messages ) {
										content.show_notice( '', content.prepare_error_message( response.data.messages ) );
									} else {
										content.show_notice( message, '' );
									}
									$approve_link.show();
									if ( $button.length ) {
										$button.removeClass( 'disabled' );
									}
									// update current view.
									content.update( content.default_params( true ) );
								}
							},
								error: function (jqXHR, exception) {
									console.log( jqXHR, exception );
									content.show_notice_oops();
									content.loader_hide();
									$approve_link.show();
									if ( $button.length ) {
										$button.removeClass( 'disabled' );
									}
									content.maybe_refresh_nonce( jqXHR.status );
								}
							}
						);
					},
					1
				);
			},
			ajax_bulk: function( action, ids ) {
				content.loader_show();
				window.setTimeout(
					function() {
						$.ajax(
							{
								url: ajaxurl,
								dataType: 'json',
								method: 'post',
								data: $.extend(
									content.default_params(),
									{
										_wpnonce: content.$form.find( '#table_nonce' ).val(),
										action: 'ahrefs_seo_content_bulk',
										doaction: action,
										ids: ids,
										_ : Math.random(), // to omit possibly cache.
									}
								),
							success: function (response) {
								if ( response.success ) {
									// update current view.
									content.update( content.default_params( true ) );
									if ( response.data['message'] || response.data['message2'] ) {
										content.show_notice( [ response.data['message2'] || '', response.data['message'] || '' ], '' );
									}
									if ( response.data['new-request'] ) {
										// run ping again immediately and update timeout.
										content.set_ping_interval( response.data.timeout || 30, 5 );
									}
									if ( response.data['audit'] ) {
										content.maybe_update_audit_header( response.data['audit'] );
									}
									if ( 'start' === action ) {
										content.ping();
									}
								} else {
									content.show_notice_oops();
									// update current view.
									content.update( content.default_params( true ) );
								}
							},
								error: function (jqXHR, exception) {
									console.log( jqXHR, exception );
									content.show_notice_oops();
									content.loader_hide();
									content.update( content.default_params( true ) );
									content.maybe_refresh_nonce( jqXHR.status );
								}
							}
						);
					},
					1
				);
			},
			hide_more_info_items: function() {
				content.$table.find( '.more-info-tr' ).remove();
				// remove active class from expanded button.
				content.$table.find( '.expanded' ).removeClass( 'expanded' );
			},
			add_more_info_item: function( $tr ) {
				content.hide_more_info_items();
				var details = '<div class="row-loader"><div class="loader"></div></div>';
				var post_id = $tr.find( '.check-column input' ).data( 'id' );
				var version = $tr.find( '.check-column input' ).data( 'ver' );

				$tr.after( '<tr class="hidden more-info-tr"></tr><tr class="inline-edit-row more-info-tr more-info-tr-active" data-id="' + post_id + '"><td colspan="' + $( 'th:visible, td:visible', '.widefat:first thead' ).length + '" class="colspanchange">' + details + '</td></tr>' );
				$tr.addClass( 'expanded' );
				var $new_tr = content.$table.find( 'tr.more-info-tr-active' );
				if ( $tr.hasClass( 'uiroles-can-not-manage' ) ) { // duplicate class to new table row.
					$new_tr.addClass( 'uiroles-can-not-manage' );
				}
				// load details.
				content.load_content_details( post_id, $new_tr, version );
			},
			// update tabs content if new html has different items count.
			maybe_update_tabs_content: function( new_html ) {
				var $tabs         = content.$form.find( '.subsubsub' );
				var $new_content  = $( new_html );
				var count_current = $tabs.find( '.count' ).map( function() {return(jQuery( this ).text())} ).toArray().join( '' );
				var count_updated = $new_content.find( '.count' ).map( function() {return($( this ).text())} ).toArray().join( '' );

				if ( count_current !== count_updated ) {
					$tabs.html( $new_content.html() );
					$tabs.removeClass( 'item-flash' );
					setTimeout( function() { $tabs.addClass( 'item-flash' ); }, 100 );
				}
			},
			// update charts content.
			maybe_update_charts_content: function( charts ) {
				if (charts.left && charts.left.length) {
					$( '#charts_block_left' ).html( charts.left ).removeClass( 'item-flash' );
					setTimeout( function() { jQuery( '#charts_block_left' ).addClass( 'item-flash' ); }, 100 );
				}
				if (charts.right && charts.right.length) {
					$( '#charts_block_right' ).html( charts.right );
					$( '#charts_block_right' ).closest( '.chart-wrap' ).removeClass( 'item-flash' );
					setTimeout( function() { jQuery( '#charts_block_right' ).closest( '.chart-wrap' ).addClass( 'item-flash' ); }, 100 );
				}
				if (charts.right_legend && charts.right_legend.length) {
					$( '#charts_block_right_legend' ).html( charts.right_legend );
				}
			},
			// show block with error or add error to existing block.
			show_messages_html: function( messages_html ) {
				var $messages = $( messages_html );
				var $items    = $messages.find( '.ahrefs-message' );
				$items.each(
					function() {
						var $item          = $( this );
						var id             = $item.attr( 'id' );
						var $existing_item = $( '#' + id );
						if ( $existing_item.length ) {
							if ( $existing_item.data( 'count' ) ) { // just increase count number.
								var count = 0 + $item.data( 'count' ) + $existing_item.data( 'count' );
								$existing_item.data( 'count', count );
								var $count_block = $existing_item.find( '.ahrefs-messages-count' );
								$count_block.removeClass( 'hidden' ).text( count );
							}
						} else {
							if ( ! $( '#ahrefs_api_messages' ).length ) {
								$( '.ahrefs_messages_block[data-type="api-messages"]' ).append( '<div class="notice notice-error is-dismissible" id="ahrefs_api_messages"><div id="ahrefs-messages"></div></div>' );
								$( document ).trigger( 'wp-updates-notice-added' );
							}

							var $wrapper = $( '#ahrefs-messages:first .message-expanded-text' );
							if ( ! $wrapper.length ) { // append wrapper.
								var $blocks = $( '<div/>' ).append( $messages.find( '#ahrefs-messages' ).clone() );
								$blocks.find( '.ahrefs-message' ).remove();
								$( '#ahrefs-messages:first' ).empty().append( $blocks );
								$wrapper = $( '#ahrefs-messages:first .message-expanded-text' );
							}
							$wrapper.append( $item );
						}

					}
				)
			},
			show_tips : function( tips ) {
				if ( 'undefined' !== typeof tips['stop'] && null !== tips['stop'] ) { // update a whole block.
					var $stop_block        = $( '.ahrefs_messages_block[data-type="stop"]' );
					var $stop_block_single = $stop_block.find( '.tip-single' ); // part of block, where single messages placed.
					if ($stop_block_single.html() != tips['stop'] ) { // do not show same message, that already displayed.
						var html_new    = $( '<div/>' ).html( tips['stop'] ).text();
						var $temp_block = $stop_block_single.clone();
						$temp_block.find( '.notice-dismiss' ).remove();
						var html_curr = $temp_block.text();
						if ( html_new.trim() !== html_curr.trim() ) {
							$stop_block_single.html( tips['stop'] );
							if ( content_tips.render_block_stop() ) {
								if ( '' !== tips['stop'] ) { // do not scroll screen if stop block is empty.
									$( 'html, body' ).animate( { scrollTop: $stop_block.offset().top - 60 } );
								}
							}
						}
					}
				}
				if ( 'undefined' !== typeof tips['api-messages'] && null !== tips['api-messages'] && '' !== tips['api-messages'] ) { // add new messages.
					content.show_messages_html( tips['api-messages'] );
				}
				if ( 'undefined' !== typeof tips['api-delayed'] && null !== tips['api-delayed'] ) { // add new messages.
					$( '.ahrefs_messages_block[data-type="api-delayed"]' ).append( tips['api-delayed'] );
					$( document ).trigger( 'wp-updates-notice-added' );
				}
				if ( 'undefined' !== typeof tips['audit-tip'] && null !== tips['audit-tip'] ) { // add new tips.
					$( '.ahrefs_messages_block[data-type="audit-tip"]' ).append( tips['audit-tip'] );
					$( document ).trigger( 'wp-updates-notice-added' );
				}
				if ( 'undefined' !== typeof tips['content-tips-show'] && null !== tips['content-tips-show'] ) { // add new tips.
					content.render_tips_block( $( '#content_tips_block' ), tips['content-tips-show'] );
				}
			},

			/**
			 * Update keyword and source input with selected keyword.
			 *
			 * @param rows array with rows info.
			 */
			keyword_popup_update_current: function( rows ) {
				var current_keyword = '';
				var current_source  = '';
				for ( var k in rows ) {
					if ( rows[k][0]) {
						current_keyword = rows[k][1];
						current_source  = rows[k][2];
						break;
					}
				}
				$( '.keyword-choice-wrap .keyword-input' ).val( current_keyword );
				content.keyword_set_source( current_source );
				setTimeout( function() {$( '.keyword-choice-wrap .keyword-input' ).focus();}, 150 );
			},
			/**
			 * Remove 'saved' item from rows.
			 *
			 * @param rows array with rows info.
			 */
			keyword_popup_clean_saved: function( rows ) {
				for ( var k in rows ) {
					if ( content_strings.source_id.saved === rows[k][2] ) { // do not show saved items inside a table.
						rows.splice( k, 1 );
						break;
					}
				}
			},
			/**
			 * Initialize data table
			 */
			keyword_popup_update_table : function() {
				if ( $( '#keyword_results' ).length && ! content.keyword_data_table ) {
					content.keyword_popup_update_current( content.keyword_data_set );
					content.keyword_popup_clean_saved( content.keyword_data_set );

					content.keyword_data_table = $( '#keyword_results' ).DataTable(
						{
							data: content.keyword_data_set,
							columns: [
								{ title: '<span></span>', orderSequence: [], render: function( data, type, row, meta ) {
									return '<span class="checked"></span>';
								}   },
								{ title: '<span>' + content_strings.popup.keyword + '</span>', orderSequence: [ 'asc', 'desc' ], render: function( data, type, row, meta ) {
									if ( 'display' === type ) {
										return data;
									}
									return data;
								}, },
								{ title: '<span>' + content_strings.popup.source + '</span>', orderSequence: [ 'asc', 'desc' ], render: function( data, type, row, meta ) {
									if ( 'display' === type ) {
										var badge = data;
										if ( content_strings.source_id.manual === row[2] ) {
											badge = 'user input';
										}
										return '<span class="badge-keyword-source badge-keyword-source-' + data + '">' + escape_html( badge ) + '</span>';
									}
									if ( 'sort' === type ) {
										if ( content_strings.source_id.gsc === data ) {
											return 3;
										}
										if ( content_strings.source_id.tf_idf === data ) {
											return 4;
										}
										if ( content_strings.source_id.manual === data ) {
											return 2;
										}
										return data;
									}
									return data;
								}, },
								{ title: $( '<span class="show_tooltip">' + content_strings.popup.position + '</span>' ).attr( 'title', content_strings.popup.hint_position )[0].outerHTML, orderSequence: [ 'asc' ], render: function( data, type, row, meta ) {
									if ( 'display' === type ) {
										if ('' === data || null === data) {
											return '<span class="position">—</span>';
										}
										return '<span class="position">' + data.toFixed( 1 ) + '</span>';
									}
									if ( 'sort' === type ) {
										if ('' === data || null === data) {
											return 1000000.0; // show items without positions at the end.
										}
										return data;
									}
									return data;
								}, },
								{ title: $( '<span class="show_tooltip">' + content_strings.popup.clicks + '</span>' ).attr( 'title', content_strings.popup.hint_clicks )[0].outerHTML, orderSequence: [ 'desc' ], render: function( data, type, row, meta ) {
									if ( 'display' === type ) {
										if ('-' === data) {
											return '<span class="keyword-no-info">—</span>';
										}
										return '' + data + ' <span class="keyword-percents">' + Math.round( 100 * data / content.keyword_data_total_clicks ) + '%</span>'
									}
									return data;
								}, },
								{ title: $( '<span class="show_tooltip">' + content_strings.popup.impressions + '</span>' ).attr( 'title', content_strings.popup.hint_impressions )[0].outerHTML, orderSequence: [ 'desc' ], render: function( data, type, row, meta ) {
									if ( 'display' === type ) {
										if ('-' === data) {
											return '<span class="keyword-no-info">—</span>';
										}
										return '' + data + ' <span class="keyword-percents">' + Math.round( 100 * data / content.keyword_data_total_impr ) + '%</span>'
									}
									return data;
								}, },
								{ title: '<span>' + content_strings.popup.country + '</span>', orderSequence: [], render: function( data, type, row, meta ) {
									var code = content.keyword_country_code3;
									if ( content_strings.source_id.gsc !== row[2] && ( '' === row[3] || null === row[3] ) ) {
										code = '';
									} else if ( '' === code ) {
										code = content_strings.all_countries;
									}
									if ( '' === code ) {
										return '<span class="keyword-no-info">—</span>';
									}
									return '<span class="keyword-country">' + code + '</span>';
								}, },
								{ title: "", orderSequence: [], render: function( data, type, row, meta ) {
									var keyword = row[1];
									return jQuery( '<a href="#" class="ahrefs-open-keyword"><span>' + content_strings.link_explore_keyword + '</span><span class="link-open"></span></a>' ).attr( 'data-keyword', keyword ).get( 0 ).outerHTML; // 'Explore in Ahrefs'.
								} },
							],
							rowCallback: function (row, data) {
								if ( data[0] && ! $( '#keyword_results tr.selected' ).length ) {
									$( row ).addClass( 'selected' );
								}
								$( row ).attr( 'data-source', data[2] );
							},
							'order': [],
							'pageLength' : 10,
							'paging':   false,
							'info':     false,
							'searching': false,
							language : {
								emptyTable: content.no_rows_message,
							}
						}
					);
					// add-remove actions.
					$( '#keyword_results' ).on(
						'click',
						'tbody td',
						function() {
							var $tr     = $( this ).closest( 'tr' );
							var $input  = $( '.keyword-choice-wrap .keyword-input' );
							var $source = $( '.keyword-choice-wrap .source-input' );
							if ( ! $tr.hasClass( '.selected' ) ) {
								$tr.siblings().removeClass( 'selected' );
								$tr.addClass( 'selected' );
								$input.val( $tr.find( 'td:nth(1)' ).text() );
								content.keyword_set_source( $tr.attr( 'data-source' ) );
							}
						}
					);
					$( '#ahrefs_seo_keyword_submit' ).on(
						'click',
						function() {
							var post_id        = $( this ).closest( '.ahrefs-seo-modal-keywords' ).data( 'id' );
							var keyword_manual = $( '#keyword_results .badge-keyword-source-manual' ).closest( 'tr' ).find( 'td:nth(1)' ).text(); // previous user input.
							var keyword        = $( '.keyword-choice-wrap .keyword-input' ).val(); // target keyword.
							var source         = $( '.keyword-choice-wrap .source-input' ).val(); // target keyword.
							content.keyword_set_post_keyword( post_id, keyword, source, keyword_manual, content.keyword_data_not_approved || 0 );
							return false;
						}
					);
					$( '#ahrefs_seo_keyword_cancel' ).on(
						'click',
						function() {
							$( '#TB_closeWindowButton' ).trigger( 'click' );
							return false;
						}
					);

				}
				$( '#TB_window' ).on( 'tb_unload', content.keyword_popup_delete_table );
				content.keyword_popup_set_height();
			},
			keyword_popup_set_height : function() {
				var screen_height = jQuery( '#TB_overlay' ).height();
				var $content      = jQuery( '#TB_ajaxContent' );
				var $buttons      = $content.find( '.keywords-buttons' );
				var $body         = $content.find( '.keywords-wrap-body' );
				var max_height    = screen_height - $content.position().top - 7 - $( '#TB_title' ).outerHeight();
				$content.addClass( 'keywords-popup' ).css( 'max-height', '' + max_height + 'px' ).css( 'height', 'auto' );
				$body.css( 'max-height', '' + ( max_height - $buttons.outerHeight() - 16 ) + 'px' ).css( 'height', 'auto' );
				// update width.
				var $window = $( '#TB_window' );
				$window.css( 'padding-right', 0 ).css( 'padding-left', 0 );
				$content.css( 'width', '' + ( 30 + $window.outerWidth() ) + 'px' );
			},
			keyword_popup_delete_table : function() {
				if ( content.keyword_data_table ) {
					content.keyword_data_table.destroy();
					content.keyword_data_table = null;
				};
			},
			keyword_popup_show: function( post_id, title ) {
				// window width.
				var width = Math.round( ( document.body.clientWidth ) * 0.9 ) - 30;
				if ( width > 1024 ) {
					width = 1024;
				}
				var height = Math.round( ( document.body.clientHeight ) ) - 50;
				if ( height > 1024 ) {
					height = 1024;
				}

				var url = ajaxurl + '?action=ahrefs_content_get_keyword_popup&ahpost=' + encodeURIComponent( post_id ) + '&_wpnonce=' + content.$form.find( '#table_nonce' ).val() + '&width=' + width + '&height=' + height;
				tb_show( content_strings.title_select_target_keyword, url ); // 'Select target keyword'.
			},
			keyword_show_error : function( text ) {
				if ( 'undefined' === typeof text || '' === text ) {
					text = [ content_strings.notice_oops_while_saving ]; // 'Oops, there was an error while saving the keyword. Please try again.'.
				} else if ( 'string' === typeof text ) {
					text = [ text ];
				}
				// keyword_popup_error_place.
				if ( "visible" === jQuery( "#TB_window" ).css( "visibility" ) && $( '#TB_ajaxContent' ).length && $( '.ahrefs-seo-modal-keywords' ).length ) { // show in current keywords popup.
					var $item = $( '.ahrefs-seo-modal-keywords' ).find( '.keyword-save-error' );
					var $div  = $( '<div class="notice notice-error is-dismissible"></div>' );
					for ( var k in text ) {
						$div.append( $( '<p/>' ).text( text[k] ) );
					}
					$item.append( $div );
					$( document ).trigger( 'wp-updates-notice-added' );
					$( '#TB_ajaxContent' ).animate( { scrollTop: 0 } );
				} else { // show in main window.
					content.show_notice( text, '' )
				}
			},
			keyword_hide_error : function() {
				$( '.ahrefs-seo-modal-keywords' ).find( '.keyword-save-error' ).empty();
			},
			keyword_show_loader : function() {
				$( '#loader_suggested_keywords' ).show();
			},
			keyword_hide_loader : function() {
				$( '#loader_suggested_keywords' ).hide();
			},
			/**
			 * Set selected item in table using value from keyword input field.
			 * Update source field with found row's source.
			 *
			 * @param value String with current keyword.
			 * @param source String with current source id.
			 */
			keyword_set_selected_in_table : function( value, source ) {
				var $found_tr       = null;
				var support_locale  = 'function' === typeof String.prototype.toLocaleLowerCase;
				var value_lowercase = support_locale ? value.toLocaleLowerCase().trim() : value.toLowerCase().trim();
				$( '#keyword_results tbody tr > td:nth-child(2)' ).each( // select using same source.
					function() {
						var current_lowercase = support_locale ? $( this ).text().toLocaleLowerCase().trim() : $( this ).text().toLowerCase().trim();
						if ( ! $found_tr && ( $( this ).closest( 'tr' ).attr( 'data-source' ) === source ) && ( value_lowercase === current_lowercase ) ) {
							$found_tr = $( this ).parent(); // tr.
						}
					}
				);
				if ( ! $found_tr ) {
					$( '#keyword_results tbody tr > td:nth-child(2)' ).each( // select ignoring source.
						function() {
							var current_lowercase = support_locale ? $( this ).text().toLocaleLowerCase().trim() : $( this ).text().toLowerCase().trim();
							if ( ! $found_tr && ( value_lowercase === current_lowercase ) ) {
								$found_tr = $( this ).parent(); // tr.
							}
						}
					);
				}
				if ( $found_tr && ! $found_tr.hasClass( 'selected' ) ) {
					$found_tr.siblings().removeClass( 'selected' );
					$found_tr.addClass( 'selected' );
				} else if ( ! $found_tr ) {
					$( '#keyword_results tbody tr.selected' ).removeClass( 'selected' );
				}
				if ( $found_tr ) {
					content.keyword_set_source( $found_tr.attr( 'data-source' ) );
				}
			},
			/**
			 * Set source for target keyword
			 *
			 * @param source
			 */
			keyword_set_source: function( source ) {
				$( '.keyword-choice-wrap .source-input' ).val( source );
			},
			/**
			 * Save selected keyword and user keyword input.
			 *
			 * @param post_id
			 * @param keyword
			 * @param source
			 * @param not_approved
			 * @param keyword_manual
			 */
			keyword_set_post_keyword: function( post_id, keyword, source, keyword_manual, not_approved ) {
				var $button = $( '#ahrefs_seo_keyword_submit' );
				if ( $button.hasClass( 'disabled' ) ) {
					return;
				}
				$button.addClass( 'disabled' ); // disable a button and show loader inside button.
				content.keyword_hide_error();
				$.ajax(
					{
						url: ajaxurl,
						method: 'post',
						async: true,
						data: {
							action: 'ahrefs_content_set_keyword',
							_wpnonce: content.$form.find( '#table_nonce' ).val(),
							referer: $( 'input[name="_wp_http_referer"]' ).val(),
							post: post_id,
							keyword: keyword,
							source: source,
							keyword_manual: keyword_manual,
							not_approved: not_approved,
						},
						success: function( response ) {
							$button.removeClass( 'disabled' );

							if ( response['success'] ) { // update keyword in the content audit table.
								$( '#TB_closeWindowButton' ).trigger( 'click' );
								content.$table.find( '.check-column input[data-id="' + post_id + '"]' ).closest( 'tr' ).find( '.content-post-keyword' ).text( keyword ).closest( 'tr' ).find( '.column-position' ).text( '' );
								content.ping();
							} else {
								if ( response && response['data'] && response['data']['error'] ) {
									content.keyword_show_error( response['data']['error'] );
								} else {
									content.keyword_show_error();
								}
								console.log( response );
							}
						},
						error: function( jqXHR, exception ) {
							console.log( jqXHR, exception );
							content.keyword_show_error();
							$button.removeClass( 'disabled' );
							content.maybe_refresh_nonce( jqXHR.status );
						}
					}
				);
			},
			// run keywords suggestions update.
			keyword_popup_update_suggestions: function( post_id ) {
				$.ajax(
					{
						url: ajaxurl,
						method: 'post',
						async: true,
						data: {
							action: 'ahrefs_content_get_fresh_suggestions',
							_wpnonce: content.$form.find( '#table_nonce' ).val(),
							referer: $( 'input[name="_wp_http_referer"]' ).val(),
							post: post_id,
						},
						success: function( response ) {
							$( '#loader_suggested_keywords' ).hide();
							if ( $( '#ahrefs_seo_modal_keywords' ).length && $( '#ahrefs_seo_modal_keywords' ).data( 'id' ) == response['data']['post_id'] ) { // if a table displayed and this is the table for same post as we received.
								// keywords.
								if ( response['success'] && response['data'] && response['data']['post_id'] ) {
									if ( response['data']['imported'] ) { // update target keyword input with fresh imported keyword.
										var rows = response['data']['keywords'] || [];
										content.keyword_popup_update_current( rows );
									}
									// remove 'saved' item, but do not set current keyword again.
									content.keyword_popup_clean_saved( response['data']['keywords'] );

									var different_rows = [];
									// compare with existing...
									for ( var k in response['data']['keywords'] ) {
										if ( null === content.keyword_data_set || 'undefined' === typeof( content.keyword_data_set[k] ) || content.keyword_data_set[k].slice( 1 ).toString() != response['data']['keywords'][k].slice( 1 ).toString() ) {
											different_rows.push( k );
										}
									}
									if ( null === content.keyword_data_set || content.keyword_data_set.length !== response['data']['keywords'].length || different_rows.length ) {

										content.keyword_data_total_clicks = response['data']['total_clicks'];
										content.keyword_data_total_impr   = response['data']['total_impr'];
										content.keyword_data_table.clear();
										content.keyword_data_table.rows.add( response['data']['keywords'] || [] ); // update keyword table with fresh suggestions.
										content.keyword_data_table.draw();
										content.keyword_data_set = response['data']['keywords'];
										// blink on updated rows.
										for (var k in different_rows) {
											$( '#keyword_results tbody tr:nth(' + different_rows[k] + ') td' ).addClass( 'item-flash' );
										}
										setTimeout( function() { $( '#keyword_results tbody td.item-flash' ).removeClass( 'item-flash' ); }, 5000 );
										content.keyword_set_selected_in_table( $( '.keyword-choice-wrap .keyword-input' ).val(), $( '.keyword-choice-wrap .source-input' ).val() );
									}
								}
								// error message.
								if ( response['success'] && response['data'] && response['data']['errors'] ) {
									content.keyword_show_error( response['data']['errors'] );
									$( document ).trigger( 'wp-updates-notice-added' );
								}
							} else {
								// error message.
								if ( response['success'] && response['data'] && response['data']['errors'] ) {
									content.keyword_show_error( response['data']['errors'] );
									$( document ).trigger( 'wp-updates-notice-added' );
								}
								console.log( response );
							}
						},
						error: function( jqXHR, exception ) {
							$( '#loader_suggested_keywords' ).hide();
							console.log( jqXHR, exception );
							content.show_notice_with_reload();
							content.maybe_refresh_nonce( jqXHR.status );
						}
					}
				);

			},

			/**
			 * Initialize positions table
			 */
			positions_table_init : function( table_id, table_data, is_additinal_table ) {
				if ( is_additinal_table && content.positions_table ) {
					content.positions_table.destroy();
					content.positions_table = null;
				}
				var table_var = null;
				if ( $( table_id ).length ) {

					var keywords_column_title = is_additinal_table ? content_strings.positions.keyword_additional : content_strings.positions.keyword_target;
					table_var                 = $( table_id ).DataTable(
						{
							destroy: true,
							columns: [
								{ title: '<span>' + keywords_column_title + '</span>', orderSequence: ( is_additinal_table ? [ 'asc', 'desc' ] : [] ), render : function( data, type, row, meta ) {
									if ( 'display' === type ) {
										return data;
									}
									return data;
								}, },
								{ title: $( '<span class="show_tooltip">' + content_strings.positions.position + '</span>' ).attr( 'title', content_strings.positions.hint_position )[0].outerHTML, orderSequence: ( is_additinal_table ? [ 'asc' ] : [] ), render : function( data, type, row, meta ) {
									if ( 'display' === type ) {
										if ('' === data || null === data) {
											return '<span class="position">—</span>';
										}
										if ( 'string' === typeof( data ) ) {
											data = parseFloat( data );
										}
										return '<span class="position">' + data.toFixed( 1 ) + '</span>';
									}
									if ( 'sort' === type ) {
										if ('' === data || null === data) {
											return 1000000.0; // show items without positions at the end.
										}
										return data;
									}
									return data;
								}, },
								{ title: $( '<span class="show_tooltip">' + content_strings.positions.clicks + '</span>' ).attr( 'title', content_strings.positions.hint_clicks )[0].outerHTML, orderSequence: ( is_additinal_table ? [ 'desc' ] : [] ), render : function( data, type, row, meta ) {
									if ( 'display' === type ) {
										if ('-' === data || '' === data) {
											return '<span class="keyword-no-info">—</span>';
										}
										return '' + data;
									}
									return data;
								}, },
								{ title: $( '<span class="show_tooltip">' + content_strings.positions.impressions + '</span>' ).attr( 'title', content_strings.positions.hint_impressions )[0].outerHTML, orderSequence: ( is_additinal_table ? [ 'desc' ] : [] ), render : function( data, type, row, meta ) {
									if ( 'display' === type ) {
										if ('-' === data || '' === data) {
											return '<span class="keyword-no-info">—</span>';
										}
										return '' + data;
									}
									return data;
								}, },
								{ title: "", orderSequence: [], render: function( data, type, row, meta ) {
									var keyword = row[0];
									return jQuery( '<a href="#" class="positions-check-serp"><span>' + content_strings.positions.link_check_serp + '</span><span class="link-open"></span></a>' ).attr( 'data-keyword', keyword ).get( 0 ).outerHTML; // 'Check SERP'.
								} },
							],
							fixedHeader: {
								header: false,
								footer: true
							},
							'footerCallback': function ( row, data, start, end, display ) {
								if ( is_additinal_table ) {
									var api = this.api();
									// Update table footer.
									$( api.column( 0 ).footer() ).html(
										'<a href="#" class="positions-show-more"></a>' // without text, will update later.
									);
									$( api.column( 4 ).footer() ).html(
										'<a href="#" class="positions-export-button with-icon download-button">' + content_strings.positions.export_button + '</a>'
									);
									content.positions_table_update_footer( false, api );
								}
							},
							rowCallback: function (row, data) {
								$( row ).attr( 'data-kw', data[0] );
							},
							'order': [],
							'pageLength' : 10,
							'paging':   true,
							'info':     false,
							'searching': false,
							language : {
								emptyTable: content_strings.positions.no_rows_message,
							}
						}
					);
					if ( is_additinal_table ) {
						content.positions_table = table_var;
						$( table_id ).find( 'thead th:not(.sorting_disabled)' ).on(
							'click',
							function() {
								content.positions_table_update_footer( true ); // reset to initial 10.
							}
						)
					}
				}
				return table_var;
			},

			positions_table_update_footer: function( reset_rows_to_10, $table ) {
				if ( ! $table ) {
					$table = content.positions_table;
				}
				console.log( $table );
				if ( $table ) { // not defined during table creation.
					if ( reset_rows_to_10 ) {
						$table.page.len( 10 ).draw();
					}
					var len   = $table.page.len();
					var count = $table.data().rows().count();
					var rest  = count - len;
					$( '.positions-show-more' ).text( content_strings.positions.show_more_link.replace( '%d', rest > 10 ? 10 : rest ) );
					if ( len < count ) {
						$( '.positions-show-more' ).show();
					} else {
						$( '.positions-show-more' ).hide();
					}
				}
			},
			// load fresh refdomains count data.
			refdomains_refresh: function( item_id, post_id ) {
				console.log( 'refdomains_refresh', item_id, post_id );
				// note: loader image placed inside item_id, so it will be replaced with a new data.
				$.ajax(
					{
						url: ajaxurl,
						dataType: 'json',
						method: 'post',
						data: {
							_wpnonce: content.$form.find( '#table_nonce' ).val(),
							action: 'ahrefs_seo_content_refdomains_update',
							post: post_id,
						},
						success: function (response) {
							if ( response && response['success'] ) {
								$( item_id ).text( response['data']['ref_domains_text'] );
							} else {
								$( item_id ).text( content_strings.notice_no_ref_domains_data ).addClass( 'error' );
							}
						},
						error: function (jqXHR, exception) {
							console.log( jqXHR, exception );
							$( item_id ).text( content_strings.notice_no_ref_domains_data ).addClass( 'error' );
						}
					}
				);

			},

			// expand first suggestion at the report, if exists.
			maybe_expand_first_suggestion : function() {
				if ( ! content.$form.find( '#please_expand_suggestion' ).length ) {
					return;
				}
				var $first_item = content.$table.find( '#the-list tr:first' );
				if ( $first_item.length && ! $first_item.hasClass( 'expanded' ) ) { // need to show expanded view.

					if ( ! $first_item.hasClass( 'is-expanded' ) && $first_item.find( 'button.toggle-row' ).is( ":visible" ) ) { // mobile mode of the table?
						$first_item.find( 'button.toggle-row:first' ).trigger( 'click' ); // show hidden fields.
					}
					$first_item.find( 'a.content-more-button' ).trigger( 'click' );
				}
			},
			// replace placeholder by table content.
			display: function() {
				if ( ! $( '#content_table' ).length) {
					return;
				}
				content.loader_show();
				// use parameters from current url at first table load time.
				var query = window.location.search.substring( 1 );
				var s     = null;
				try {
					s = decodeURIComponent( ( '' + content.__query( query, 's' ).replace( /\+/g, '%20' ) ) ) || null;
				} catch ( e ) {
				}
				var cat = null;
				try {
					cat = decodeURIComponent( ( '' + content.__query( query, 'cat' ).replace( /\+/g, '%20' ) ) ) || null;
				} catch ( e ) {
				}

				var data = $.extend(
					content.default_params(),
					{
						tab: content.__query( query, 'tab' ) || '',
						paged: content.__query( query, 'paged' ) || '1',
						order: content.__query( query, 'order' ) || '',
						orderby: content.__query( query, 'orderby' ) || '',
						keywords: content.__query( query, 'keywords' ) || '',
						s: s,
						cat: cat,
						author: content.__query( query, 'author' ) || null,
						reason: content.__query( query, 'reason' ) || null,
					}
				);
				$.ajax(
					{
						url: ajaxurl,
						method: 'post',
						dataType: 'json',
						data: $.extend(
							data,
							{
								_wpnonce: content.$form.find( '#table_nonce' ).val(),
								action: 'ahrefs_seo_table_content_init',
								screen_id: window.pagenow || null,
							}
						),
					success: function (response) {
						if ( response && response['data'] && response['data']['display'] ) {
							content.$table.html( response.data.display );
							content.$table.find( '.tablenav.top .tablenav-pages' ).removeClass( 'one-page' ).removeClass( 'no-pages' );
							$( "tbody" ).on(
								"click",
								".toggle-row",
								function(e) {
									e.preventDefault();
									$( this ).closest( "tr" ).toggleClass( "is-expanded" )
								}
							);
							content.init();
							content.update_active_filters();
							// start ping immediately it there are unprocessed items.
							if ( $( '#has_unprocessed_items' ).length && $( '#has_unprocessed_items' ).val() ) {
								content.set_ping_interval( 30, 6 );
							}
							content.maybe_expand_first_suggestion();
						} else {
							content.show_notice_with_reload();
						}
						content.update_browser_history( true );
						content.loader_hide();
					},
						error: function (jqXHR, exception) {
							console.log( jqXHR, exception );
							content.loader_hide();
							content.show_notice_with_reload();
							content.maybe_refresh_nonce( jqXHR.status );
						}
					}
				);
			},
			init: function () {
				var timer;
				var delay = 500;
				// form items.
				content.$form.find( '#search-submit' ).off( 'click' ).on(
					'click',
					function (e) {
						e.preventDefault();
						content.search_string_set( content.$form.find( 'input[name="s"]' ).val() );
						content.page_string_set( 1 ); // reset to first page.
						content.update( content.default_params() );
					}
				);
				content.$form.find( '#doaction, #doaction2' ).off( 'click' ).on(
					'click',
					function (e) {
						e.preventDefault();
						var action = $( this ).closest( 'div' ).find( 'select' ).val();
						var ids    = content.$table.find( 'tbody .check-column input:checked' ).map(
							function() {
								return $( this ).val();
							}
						)
						if ( ids.length ) {
							content.ajax_bulk( action, ids.toArray() );
						}
					}
				);
				content.$form.find( '#group-filter-submit' ).off( 'click' ).on(
					'click',
					function (e) {
						e.preventDefault();

						var date     = content.$form.find( '#filter-by-date' ).val();
						var cat      = content.$form.find( '#cat' ).val();
						var author   = content.$form.find( '#author' ).val();
						var keywords = content.$form.find( '#keywords' ).val() || '';
						var reason   = content.$form.find( '#reason' ).val() || '';
						content.date_string_set( date );
						content.cat_string_set( cat );
						content.author_string_set( author );
						content.keywords_string_set( keywords );
						content.reason_string_set( reason );
						content.page_string_set( 1 ); // reset to first page.

						content.update_active_filters();

						content.update( content.default_params() );
					}
				);
				content.$form.on(
					'submit',
					function(e) {
						e.preventDefault();
					}
				);

				content.$table.find( '.tablenav-pages a, .manage-column.sortable a, .manage-column.sorted a' ).off( 'click' ).on(
					'click',
					function (e) {
						e.preventDefault();
						var query = this.search.substring( 1 );
						content.order_string_set( content.__query( query, 'order' ) || '' );
						content.orderby_string_set( content.__query( query, 'orderby' ) || '' );
						content.page_string_set( content.__query( query, 'paged' ) || '' );
						var data = $.extend(
							content.default_params(),
							{
								paged: content.__query( query, 'paged' ) || null,
								s: content.search_string_get(),
							}
						);
						content.update( data );
					}
				);
				content.$table.find( 'a.ahrefs-cat-link' ).off( 'click' ).on(
					'click',
					function (e) {
						e.preventDefault();
						var query  = this.search.substring( 1 );
						var cat_id = content.__query( query, 'cat' );
						try {
							cat_id = decodeURIComponent( cat_id ) || null;
						} catch ( e ) {
						}
						if ( cat_id ) {
							content.$table.find( '#cat' ).val( cat_id ).trigger( 'change' );
							content.$table.find( '#group-filter-submit' ).trigger( 'click' );
						}
						return false;
					}
				);
				// stop or start analyze page.
				content.$table.find( 'a.action-stop, a.action-start' ).off( 'click' ).on(
					'click',
					function (e) {
						e.preventDefault();
						var post_id   = $( this ).closest( 'tr' ).data( 'id' );
						var is_active = $( this ).hasClass( 'action-start' );
						content.ajax_set_page_active_or_recheck( post_id, is_active ? 1 : 0 );
						return false;
					}
				);
				// recheck page status page.
				content.$table.find( 'a.action-recheck' ).off( 'click' ).on(
					'click',
					function (e) {
						e.preventDefault();
						var post_id = $( this ).closest( 'tr' ).data( 'id' );
						content.ajax_recheck_page( post_id );
						return false;
					}
				);
				content.$table.find( 'input[name=paged]' ).off( 'keyup' ).on(
					'keyup',
					function (e) {
						if (13 == e.which) {
							e.preventDefault();
						}
						var data = content.default_params();
						window.clearTimeout( timer );
						timer = window.setTimeout(
							function () {
								content.update( data );
							},
							delay
						);
					}
				);
				content.$table.find( 'a.content-more-button' ).off( 'click' ).on(
					'click',
					function (e) {
						var $tr = $( this ).closest( 'tr' );
						if ( $tr.hasClass( 'expanded' ) ) {
							content.hide_more_info_items();
						} else {
							content.add_more_info_item( $tr );
						}
						return false;
					}
				);
				$( '#analysis_setting_button' ).off( 'click' ).on(
					'click',
					function (e) {
						var href             = $( this ).attr( 'href' ) + '&return=' + encodeURIComponent( window.location.href );
						window.location.href = href;
						return false;
					}
				);
				content.$table.find( '.change-keywords' ).off( 'click' ).on(
					'click',
					function (e) {
						var post_id = $( this ).data( 'post' );
						var title   = $( this ).closest( 'tr' ).find( 'td:nth(0) a:first' ).text();
						content.keyword_popup_show( post_id, title );
						return false;
					}
				);
				content.$table.find( '.keywords-hidden-count' ).off( 'click' ).on(
					'click',
					function (e) {
						$( this ).hide();
						$( this ).closest( 'td' ).find( '.keywords-hidden-content' ).show();
						return false;
					}
				);
			},
			// Send query once per 2 minutes and update current items if any.
			ping: function ( unpause_audit, cancel_audit, callback ) { // unpause_audit=true : try to turn audit on from pause.
				content.ping_running = true;
				content.set_ping_interval(); // update next scheduled ping.
				var data = {};
				content.$table.find( '.check-column > input[data-id]' ).map(
					function() {
						return { id : $( this ).val(), ver: $( this ).attr( 'data-ver' ) };
					}
				).toArray().forEach(
					function( item ) {
						data[item['id']] = item['ver'];
					}
				);
				if ( jQuery.isEmptyObject( data ) ) {
					data = false;
				}
				// add 'stop' block items.
				var stopped_items = [];
				$( '.ahrefs_messages_block[data-type="stop"] > div' ).each(
					function() {
						stopped_items.push( $( this ).data( 'id' ) || '' );
					}
				);

				// use setTimeout(): give UI a chance to redraw.
				window.setTimeout(
					function() {
						$.ajax(
							{
								url: ajaxurl,
								dataType: 'json',
								method: 'post',
								data: {
									_wpnonce: $( '#table_nonce' ).val(),
									action: 'ahrefs_seo_content_ping',
									items: data, // items or false.
									tab: content.tab_string_get(),
									chart_score : $( '.score-number' ).data( 'score' ).trim(),
									chart_pie : $( '.counter' ).map( function() {return $( this ).text().trim(); } ).toArray().join( '-' ),
									stop: stopped_items.join( ' ' ), // string with already displayed messages from stop block.
									unpause_audit: unpause_audit || null, // try to turn audit on from pause.
									cancel_audit: cancel_audit || null, // cancel audit.
									ahrefs_lang: content.lang,
								},

								success: function (response) {
									try {
										content.ping_running = false;
										if ( response && response['data'] ) {
											// update tabs if received.
											if (response.data.tabs && response.data.tabs.length) {
												content.maybe_update_tabs_content( response.data.tabs );
											}
											if (response.data.charts) {
												content.maybe_update_charts_content( response.data.charts );
											}
											if (response.data.tips) {
												content.show_tips( response.data.tips );
											}
											if ( response.data['audit'] ) {
												content.maybe_update_audit_header( response.data['audit'] );
											}
											var in_progress = response.data.audit && response.data.audit.in_progress;
											if ( 'undefined' !== typeof response.data['paused'] ) {
												if ( response.data['paused'] ) {
													$( '#content_audit_status' ).addClass( 'paused' );
												} else {
													$( '#content_audit_status' ).removeClass( 'paused' );
												}
											}
											if ( 'undefined' !== typeof response.data['delayed'] ) {
												if ( response.data['delayed'] && in_progress ) {
													$( '#audit_delayed_google' ).show();
												} else {
													$( '#audit_delayed_google' ).hide();
												}
											}
											if (cancel_audit) {
												// $('#content_audit_cancel').removeClass('in-progress paused');
												// reload page.
												document.location.reload( true );
												return;
											}
											if (response.data.updated && response.data.updated.length) {
												var $tr_expanded = [];
												// all content are in one html field, parse it with jQuery.
												var $div = $( '<div/>' ).html( response.data.updated );
												// hide hidden columns.
												var hidden_classes = content.$table.find( 'thead tr .manage-column.hidden' ).map( function() { return $( this ).attr( 'class' ).match( /(column-(\w)+)/ )[1] || ''} ).toArray();
												$div.find( 'td' ).removeClass( 'hidden' );
												if ( hidden_classes ) {
													for (var k in hidden_classes) {
														$div.find( 'td.' + hidden_classes[k] ).addClass( 'hidden' );
													}
												}
												$div.find( 'tr' ).each(
													function() {
														var id = $( this ).find( '.check-column > input[data-id]' ).data( 'id' ) || '';
														if ( '' !== id ) {
															// search corresponding table row and fill it with new value.
															var $tr = content.$table.find( 'tr > .check-column > input[data-id="' + id + '"]' ).closest( 'tr' );
															if ( $tr.length ) {
																var old_status = $tr.find( '.column-action .status-action > .arrow-text' ).text() + $tr.find( '.column-keyword' ).text();

																$tr.html( $( this ).html() ); // update content or row.
																var new_status = $tr.find( '.column-action .status-action > .arrow-text' ).text() + $tr.find( '.column-keyword' ).text();

																$tr.addClass( 'item-updated' ).removeClass( 'item-flash' );
																setTimeout( function() { $tr.addClass( 'item-flash' ); }, 100 );
															}
															if ( $tr.hasClass( 'expanded' ) && ( old_status !== new_status ) ) { // reload suggestions if status or keyword changed.
																$tr_expanded = $tr;
															}
														}
													}
												);
												content.init();
												// close and expand previously opened row again, with updated details.
												if ( $tr_expanded.length ) {
													$tr_expanded.find( '.content-more-button' ).trigger( 'click' ).trigger( 'click' );
												}
											}
											if ( response.data['new-request'] ) {
												// run ping again immediately and update timeout.
												if ( 'undefined' === typeof response.data.waiting_time ) {
													response.data.waiting_time = 15;
												}
												content.set_ping_interval( response.data.timeout || 30, response.data.waiting_time );
											}
											if ( response.data['reload'] ) {
												content.update( content.default_params( true ) );
											}
										}
										if ( 'function' === typeof callback ) {
											callback();
										}
										content.hide_notice_with_reload();
									} catch ( e ) {
										// do not show any error at the frontend, because this update is working at the background.
										console.log( e );
									}
								},
								error: function (jqXHR, exception) {
									// do not show any error at the frontend, because this update is working at the background.
									console.log( jqXHR, exception );
									content.ping_running = false;
									if ( 'function' === typeof callback ) {
										callback();
									}
									content.show_notice_with_reload();
									content.maybe_refresh_nonce( jqXHR.status );
								}
							}
						);
					},
					1
				);
			},
			/**
			 * Set interval for ping, run immediately.
			 *
			 * @param int timeout Interval, seconds.
			 * @param int run_after Run first request immediately in seconds.
			 */
			set_ping_interval: function( timeout, run_after ) {
				if ( 'undefined' === typeof( timeout ) ) {
					timeout = content.ping_interval; // use same interval as before.
				}
				if ( timeout < 30 ) {
					timeout = 30; // do not send requests too often.
				}
				if ( timeout > 120 ) {
					timeout = 120; // at least once per 2 minutes.
				}
				if ( 'undefined' !== typeof content.ping_timer ) {
					clearInterval( content.ping_timer );
				}
				content.ping_timer    = window.setInterval( content.ping, timeout * 1000 );
				content.ping_interval = Math.ceil( timeout + 250 * Math.random() ); // in seconds.
				if ( 'undefined' !== typeof run_after && ( run_after || 0 === run_after ) && ! content.ping_running ) {
					// prepare next call using timeout.
					if ( 'number' !== typeof run_after || run_after < 5.5 ) {
						run_after = 5.5;
					} else if ( run_after > timeout ) {
						run_after = timeout - 1;
					}
					if ( 'undefined' !== typeof content.ping_next ) {
						clearTimeout( content.ping_next );
					}
					content.ping_next = setTimeout( content.ping, Math.ceil( run_after * 1000 + 250 * Math.random() ) ); // run with predefined delay sec delay.
				}
			},
			// Send query and update table parts with updated versions of rows, headers, nav.
			update: function ( data ) {
				content.loader_show();
				window.setTimeout(
					function() {
						$.ajax(
							{
								url: ajaxurl,
								dataType: 'json',
								method: 'get', // otherwise order by table header click will not work.
								data: $.extend(
									{
										_wpnonce: content.$form.find( '#table_nonce' ).val(),
										action: 'ahrefs_seo_table_content_update',
										chart_score : $( '.score-number' ).data( 'score' ).trim(),
										chart_pie: $( '.counter' ).map( function() {return $( this ).text().trim(); } ).toArray().join( '-' ),
										screen_id: window.pagenow || null,
									},
									data
								),
							success: function (response) {
								if ( response && response['data'] ) {
									if (response.data.tabs && response.data.tabs.length) {
										content.maybe_update_tabs_content( response.data.tabs );
									}
									if (response.data.charts) {
										content.maybe_update_charts_content( response.data.charts );
									}
									if (response.data.rows.length) {
										try {
											content.$table.find( '#the-list' ).html( response.data.rows );
										} catch (e) {
											console.log( e );
										}
									}
									if (response.data.column_headers.length) {
										content.$table.find( 'thead tr, tfoot tr' ).html( response.data.column_headers );
									}
									if (response.data.pagination.bottom.length) {
										content.$table.find( '.tablenav.bottom .tablenav-pages' ).html( $( response.data.pagination.bottom ).html() );
									}
									// add/remove  "one-page", "no-pages" classes from .tablenav-pages on update.
									if ( 0 == response.data.total_pages ) {
										content.$table.find( '.tablenav.bottom .tablenav-pages' ).removeClass( 'one-page' ).addClass( 'no-pages' );
									} else if ( 1 == response.data.total_pages ) {
										content.$table.find( '.tablenav.bottom .tablenav-pages' ).addClass( 'one-page' ).removeClass( 'no-pages' );
									} else {
										content.$table.find( '.tablenav.bottom .tablenav-pages' ).removeClass( 'one-page' ).removeClass( 'no-pages' );
									}
									content.init();
									content.update_browser_history();
								} else {

								}
								content.items_maybe_flash()
								content.loader_hide();
							},
								error: function (jqXHR, exception) {
									console.log( jqXHR, exception );
									content.show_notice_with_reload();
									content.loader_hide();
									content.maybe_refresh_nonce( jqXHR.status );
								}
							}
						);
					},
					1
				);
			},
			/**
			 * Filter the URL Query to extract variables
			 *
			 * @see http://css-tricks.com/snippets/javascript/get-url-variables/
			 *
			 * @param    string    query The URL query part containing the variables
			 * @param    string    variable Name of the variable we want to get
			 *
			 * @return   string|boolean The variable value if available, false else.
			 */
			__query: function (query, variable) {
				var vars = query.split( "&" );
				var len  = vars.length;
				for (var i = 0; i < len; i++) {
					var pair = vars[i].split( "=" );
					if (pair[0] == variable) {
						return pair[1];
					}
				}
				return false;
			},
			init_manual_update: function () {
				// [Start new audit] button clicked.
				$( document ).on(
					'click',
					'.manual-update-content-link',
					function() {
						if ( $( this ).hasClass( 'disallowed' ) || $( this ).hasClass( 'disabled' ) ) {
							return false;
						}
						$.ajax(
							{
								url: ajaxurl,
								method: 'post',
								dataType: 'json',
								async: true,
								data:
								{
									_wpnonce: $( '#table_nonce' ).val(),
									action: 'ahrefs_seo_content_manual_update',
								},
								success: function (response) {
									if ( response && response['success'] ) {
										// ping: there are unprocessed items.
										content.set_ping_interval( 60, false );
										// reload page.
										document.location.reload( true );
									} else {
										if (response.data && response.data.tips) {
											content.show_tips( response.data.tips );
										} else if (response.data && response.data.error) {
											content.show_notice( response.data['error'], '' );
										} else {
											content.show_notice_with_reload();
										}
										content.ping();
									}
									if ( $( '#last_content_audit_tip' ).length ) {
										$( '#last_content_audit_tip' ).hide();
									}
								},
								error: function (jqXHR, exception) {
									console.log( jqXHR, exception );
									content.show_notice_with_reload();
									content.maybe_refresh_nonce( jqXHR.status );
								}
							}
						);
						return false;
					}
				);
				// [Audit paused] button clicked.
				$( '.paused-audit-button' ).on(
					'click',
					function() {
						if ( ! $( this ).hasClass( 'disallowed' ) && ! $( this ).hasClass( 'active' ) ) {
							$( '.paused-audit-button' ).addClass( 'active' );
							content_tips.clean_stop_block();
							$( '.tip-new-audit-message' ).remove();
							content_tips.update_tips_block();
							content.ping(
								true,
								false,
								function() {
									$( '.paused-audit-button' ).removeClass( 'active' );
								}
							);
						}
						return false;
					}
				);
				// [Cancel audit] button clicked.
				$( '#analysis_cancel_button' ).on(
					'click',
					function() {
						if ( ! $( this ).hasClass( 'disallowed' ) && ! $( this ).hasClass( 'disabled' ) ) {
							$( '#analysis_cancel_button' ).addClass( 'disabled' );
							content_tips.clean_stop_block();
							$( '.tip-new-audit-message' ).remove();
							content_tips.update_tips_block();
							content.ping(
								false,
								true,
								function() {
									setTimeout(
										function() {
										// make available in 10 sec.
											$( '#analysis_cancel_button' ).removeClass( 'disabled' );
										},
										10000
									);
								}
							);
						}
						return false;
					}
				);
			},
			heartbeat_register: function() {
				$( document ).on(
					'heartbeat-send',
					function ( event, data ) {
						data.ahrefs_seo_content = true;
					}
				);
				$( document ).on(
					'heartbeat-tick',
					function ( event, data ) {
						if ( data.ahrefs_seo_content ) {
							if ( data.ahrefs_seo_content.need_update ) {
								content.set_ping_interval( 60, 5 );
							}
						}
						// refresh table nonce value.
						if ( data.nonces_expired && data['ahrefs-nonce'] ) {
							var table_nonce = data['ahrefs-nonce']['table_nonce'];
							if (table_nonce) {
								$( '#table_nonce' ).val( table_nonce );
								content.nonce_refresh_count = 0;
								content.ping();
							}
						}
					}
				);
			},
			maybe_refresh_nonce : function(status_code) {
				if (403 == status_code) {
					content.nonce_refresh_count++;
					if (content.nonce_refresh_count < 5) {
						content.run_heartbeat_request();
					}
				}
			},
			run_heartbeat_request : function() {
				try {
					wp.heartbeat.connectNow();
				} catch (e) {
					console.log( e );
				}
			},
			set_keyword_error_handler : function() {
				jQuery( document ).ajaxError(
					function(event, request, settings) {
						if ( settings && settings.url && ( settings.url.indexOf( 'action=ahrefs_content_get_keyword_popup' ) >= 0 ) ) {
							console.log( event, request, settings );
							$( '#TB_ajaxContent' ).append( '<div class="notice notice-error is-dismissible"><p>' + content_strings.notice_oops_while_loading + '</p></div>' ).css( 'height','120px' ); // 'Oops, there was an error while loading the keywords list. Please try again.'.
						}
					}
				);
			},
			render_tips_block : function( $block, visibility ) {
				window.content_tips.render( $block, visibility );
			},
			update_browser_history : function( replace_current ) {
				try {
					var query = window.location.search.substring( 1 );
					var hash  = window.location.hash;
					var page  = content.__query( query, 'page' );
					var a     = content.default_params();
					// remove unwanted parameters.
					delete a['ahrefs_lang'];
					delete a['paged'];
					// remove parameters with default values (filters was filled with zeroes, links with empty strings).
					if ( '' === a.tab ) {
						delete a['tab'];
					}
					if ( 'dropped' !== a.tab && 'desc' === a.order && 'created' === a.orderby || 'dropped' === a.tab && 'desc' === a.order && 'last_well_date' === a.orderby ) {
						delete a['order'];
						delete a['orderby'];
					}
					if ( '' === a.s ) {
						delete a['s'];
					}
					if ( '0' === a.m || '' === a.m ) {
						delete a['m'];
					}
					if ( '0' === a.author || '' === a.author) {
						delete a['author'];
					}
					if ( '000' === a.cat || '' === a.cat ) {
						delete a['cat'];
					}
					if ( '' === a.keywords ) {
						delete a['keywords'];
					}
					if ( '' === a.reason ) {
						delete a['reason'];
					}
					// fill new url.
					var new_params = new URLSearchParams( a ).toString();
					var new_url    = document.location.href.replace( document.location.search, '?page=' + page + '&' + new_params );
					if ( '' !== hash ) {
						new_url = new_url.replace( hash, '', new_url );
					}
					if ( new_url.lastIndexOf( '&' ) === new_url.length - 1 ) {
						new_url = new_url.slice( 0, -1 );
					}
					// fill new title using current tab and order.
					var $active_tab = $( '.subsubsub .tab-content-item.current:first' ).clone();
					$active_tab.find( '.count, .help-small' ).remove();
					var active_tab_title = $active_tab.text();
					var order_title      = '';
					if ( '' !== a['orderby'] ) {
						var colname = 'created' !== a['orderby'] ? a['orderby'] : 'date'; // column class.
						var $col    = $( 'thead th.column-' + colname );
						if ( $col.length ) {
							if ( 'asc' === a['order'] ) {
								order_title = ' ↑ ';
							} else {
								order_title = ' ↓ ';
							}
							order_title += $col.find( 'a > span:first' ).text();
						}
					}
					var search_title = '';
					if ( a['s'] ) {
						search_title = ' (' + a['s'] + ') ';
					}
					var title = content_strings2.title_template ? content_strings2.title_template.replace( '####', content_strings2.title + ': ' + active_tab_title + search_title + order_title ) : content_strings.content_audit + ': ' + active_tab_title;

					document.title = title;
					if ( replace_current ) {
						window.history.replaceState( {}, title, new_url );
					} else if ( new_url !== document.location.href ) {
						window.history.pushState( {}, title, new_url );
					}
				} catch ( e ) {
					console.log( e );
				}
			},
			/**
			 * Clean filters and maybe set keywords filter if destination tab is a current tab.
			 *
			 * @param string destination_tab
			 * @param string|int keywords_value Set keywords filter value if not empty string.
			 *
			 * @returns boolean False if if destination tab is a current tab, True otherwise.
			 */
			switch_to_keywords_type: function( destination_tab, keywords_value ) {
				if ( destination_tab === $( 'input[name="tab"]' ).val() && $( '#keywords' ).length ) {
					// reset bulk action.
					$( 'select[name^="action"]' ).val( '-1' );
					// reset all filters.
					$( '#keywords, #search_to_url-search-input' ).val( '' );
					$( '#cat' ).val( '000' );
					$( '#filter-by-date, #author' ).val( '0' );
					content.search_string_set( '' );
					// run update with required keyword filter value.
					if ( '' !== keywords_value ) {
						window.content.items_to_flash_add( $( '#keywords' ) );
						$( '#keywords' ).val( keywords_value ).trigger( 'update' );
					}
					$( '#group-filter-submit' ).trigger( 'click' );
					return false;
				}
				return true;
			}
		}
		content.display();
		content.heartbeat_register();
		content.init_manual_update();
		content.set_keyword_error_handler();
		content_tips.render_block_stop();
		if ('object' === typeof content_tips_data) {
			content.render_tips_block( $( '#content_tips_block' ), content_tips_data.tips );
		}
		// check for updates once per 2 minutes.
		content.set_ping_interval( 120 );
	})( jQuery );
}
