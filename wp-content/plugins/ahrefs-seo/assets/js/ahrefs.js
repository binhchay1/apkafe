/**
 * Ahrefs js handlers
 */

(function($) {
	"use strict";

	$(
		function() {

			/* wizard */
			if ($( '#ahrefs_get' ).length) {
				$( '#ahrefs_get' ).on(
					'click',
					function() {
						$.post(
							ajaxurl,
							{
								action: 'ahrefs_token',
								step: 1,
								_wpnonce: $( '#_wpnonce' ).val(),
								_: Math.random(),
							}
						);
						$( '[name="ahrefs_token"]' ).val( '' );
						$( '.ahrefs-seo-error' ).text( '' );

						$( '.setup-wizard' ).addClass( 'step-1' );
						return true;
					}
				)
			}
			if ($( '#ahrefs_seo_activate' ).length) {
				$( '#ahrefs_seo_activate' ).on(
					'click',
					function() {
						$( this ).attr( 'disabled', 'disabled' );
						$( '.ahrefs-seo-wizard' ).trigger( 'submit' );
						return false;
					}
				)
			}
			if ($( '#ahrefs_seo_submit, #step2_1_submit' ).length) {
				$( '#ahrefs_seo_submit, #step2_1_submit' ).on(
					'click',
					function() {
						if ( ! $( this ).attr( 'disabled' ) ) {
							// set flag and allow to submit a form.
							if ( $( '#analytics_code' ).length && '' === $( '#analytics_code' ).val() ) {
								$( '#analytics_code' ).addClass( 'error' );
								$( '#analytics_code' ).closest( 'form' ).find( '.ahrefs-seo-error' ).text( ahrefs_seo_strings.enter_auth_code ); // 'Please enter your authorization code'.
								return false;
							}
							$( this ).closest( 'form' ).trigger( 'submit' );
						}
						return false;
					}
				)
			}
			if ( $( '#analytics_code' ).length ) {
				$( '#analytics_code' ).on(
					'keyup',
					function() {
						$( this ).closest( 'form' ).find( '.ahrefs-seo-error' ).text( '' );
						$( this ).removeClass( 'error' );
					}
				);
			}

			function step_2_2_set_button_enabled() {
				var enabled = false;
				// both accounts selected.
				if ($( '#analytics_account' ).length && $( '#analytics_account' ).val() && $( '#gsc_account' ).length && $( '#gsc_account' ).val()) {
					enabled = true;
				}
				if ( enabled ) {
					$( '#ahrefs_seo_submit' ).removeAttr( 'disabled' );
				} else {
					$( '#ahrefs_seo_submit' ).attr( 'disabled', 'disabled' );
				}
			}
			if ($( '#analytics_account' ).length) {
				$( '#analytics_account' ).on(
					'change',
					function() {
						// reset incorrect flag.
						$( this ).removeClass( 'incorrect-value' );
						// fill hidden field with account name.
						$( this ).closest( 'form' ).find( '#ua_name' ).val( $( this ).find( 'option:selected' ).text() );
						$( this ).closest( 'form' ).find( '#ua_url' ).val( $( this ).find( 'option:selected' ).data( 'url' ) );
						// Continue button enable/disable.
						step_2_2_set_button_enabled();
					}
				)
			}
			if ($( '#gsc_account' ).length) {
				$( '#gsc_account' ).on(
					'change',
					function() {
						// reset incorrect flag.
						$( this ).removeClass( 'incorrect-value' );
						// Continue button enable/disable.
						step_2_2_set_button_enabled();
					}
				)
			}
			if ($( '.checkbox-main' ).length) {
				$( '.checkbox-main' ).on(
					'change',
					function() {
						if ( $( this ).is( ':checked' ) ) {
							$( this ).closest( '.checkbox-group' ).find( '.subitems input:not(:checked)' ).attr( 'checked', 'checked' ).prop( 'checked',true );
						} else {
							$( this ).closest( '.checkbox-group' ).find( '.subitems input:checked' ).removeAttr( 'checked' ).prop( 'checked',false );
						}
					}
				)
				// parent item became unchecked only if all child items already unchecked.
				$( '.checkbox-group .subitems input[type="checkbox"]' ).on(
					'change',
					function() {
						if ( $( this ).closest( '.checkbox-group' ).find( '.subitems input:checked' ).length ) {
							$( this ).closest( '.checkbox-group' ).find( '.checkbox-main' ).attr( 'checked', 'checked' ).prop( 'checked',true );
						} else if ( 0 === $( this ).closest( '.checkbox-group' ).find( '.subitems input:checked' ).length ) {
							$( this ).closest( '.checkbox-group' ).find( '.checkbox-main:checked' ).removeAttr( 'checked' ).prop( 'checked',false );
						}
					}
				)
			}
			try {
				// a tooltip with ability to use html code an with a delay before close.
				// this allow to click on a link inside it.
				$( document ).tooltip(
					{
						items: ".help-small, .show-tooltip, [title], label.with-long-title, a.badge-keyword",
						position: {
							my: "left bottom",
							at: "center top",
						},
						content: function () {
							var element = $( this );
							if (element.is( "[data-tooltip]" )) {
								return element.data( 'tooltip' );
							}
							if (element.hasClass( "with-long-title" ) || element.hasClass( "badge-keyword" )) {
								var $with_overflow = $( element ).clone().css(
									{
										display: 'inline',
										width: 'auto',
										visibility: 'hidden'
									}
								).appendTo( 'body' );
								var overflow_width = $with_overflow.width();
								$with_overflow.remove();
								if (Math.floor( overflow_width ) > Math.ceil( $( element ).width() )) { // show only for too long items.
									return $( element ).text();
								}
								return null;
							}
							return element.attr( 'title' );
						},
						show: null,
						close: function (event, ui) {
							ui.tooltip.hover(
								function () {
									$( this ).stop( true ).fadeTo( 600, 1 );
								},
								function () {
									$( this ).fadeOut(
										'600',
										function () {
											$( this ).remove();
										}
									)
								}
							);
						},
					}
				)
			} catch (e) {
				console.log( "Tooltip initialization error", e );
			}
			$( document ).on(
				'click',
				'.message-expanded-link',
				function() {
					$( this ).hide();
					$( this ).parent().find( '.message-expanded-text' ).show();
					return false;
				}
			);
			/* settings: audit scope */
			$( document ).on(
				'click',
				'a.show-more-a',
				function() {
					$( this ).closest( '.checkbox-group' ).toggleClass( 'group-expanded' );
					return false;
				}
			);
			/* settings: diagnostics */
			if ($( '#google_config_button' ).length) {
				$( '#google_config_button' ).on(
					'click',
					function() {
						if ( copy_to_clipboard( document.getElementById( 'google_config_input' ) ) ) {
							console.log( 'Copied' );
						}
						return false;
					}
				);
			}
			$( document ).on(
				'click',
				'a.show-collapsed, span.show-collapsed',
				function(e) {
					if ( 'INPUT' !== e.target.tagName ) {
						$( this ).toggleClass( 'active' );
						return false;
					}
				}
			);

			/* Content audit */
			$( document ).on(
				'click',
				'.content-button',
				function() {
					return false;
				}
			);

			/* settings */
			if ($( '#ahrefs_diagnostics_submit' ).length) {
				$( '#ahrefs_diagnostics_submit' ).on(
					'click',
					function() {
						$( this ).closest( 'form' ).trigger( 'submit' );
						return false;
					}
				)
			}
			if ( $( '#ahrefs_seo_screen.setup-screen, #ahrefs_seo_screen.wizard-step-3' ).length ) {
				// form submit button.
				if ( $( 'form.ahrefs-audit' ).length && $( 'form.ahrefs-audit #waiting_value' ).length ) {

					$( 'form.ahrefs-audit' ).validate(
						{
							rules: {
								'waiting_value': 'required',
							},
							errorPlacement: function(error, element) {
								$( element ).closest( 'div' ).find( 'label.error' ).remove();
								$( element ).closest( 'div' ).append( error );
							},
							submitHandler: function(form) {
								form.submit();
							}
						}
					);

					$( 'form.ahrefs-audit .waiting-units' ).on(
						'change',
						function() {
							var max    = 'month' === $( 'form.ahrefs-audit .waiting-units' ).val() ? 12 : 48;
							var $field = $( 'form.ahrefs-audit #waiting_value' );
							$field.attr( 'max', max ).valid();
						}
					)

				}
			}
			if ( $( '.scope-show-more' ).length ) {
				$( '.scope-show-more' ).on(
					'click',
					function() {
						$( this ).hide();
						$( this ).closest( 'li' ).siblings().removeClass( 'hidden' );
						return false;
					}
				);
			}
			if ($( '.scope-block, .scope-block-n' ).length) {
				// show full title if text in label is too long.
				$( '.scope-block li label, .scope-block-n li label' ).each(
					function() {
						$( this ).addClass( 'with-long-title' ).data( 'with-long-title', $( this ).text() );
					}
				)
			}
			// [run new audit] at Audit settings.
			if ( $( '.manual-update-content-link-submit' ).length ) {
				$( '.manual-update-content-link-submit' ).on(
					'click',
					function() {
						$( this ).closest( 'form' ).trigger( 'submit' );
						return false;
					}
				)
			}
			if ( $( '#options_cpt_tip_got_it' ).length ) {
				$( '#options_cpt_tip_got_it' ).on(
					'click',
					function() {
						$.post(
							ajaxurl,
							{
								action: 'ahrefs_seo_options_new_cpt_tip_close',
								_wpnonce: $( '#_wpnonce' ).val(),
								_: Math.random(),
							}
						);
						$( this ).closest( '.ahrefs-content-notice' ).hide();
						$( 'html, body' ).animate( { scrollTop: $( '#cpt' ).offset().top - 60 } );
						return false;
					}
				)
			}
			if ( $( '.how-much-selected' ).length ) {
				var func_update_count = function($item, $subitems) {
					try {
						if ( ! $item.length ) {
							return;
						}
						if ($subitems) {
							var $block = $subitems.closest( '.block-content' );
						} else {
							$subitems  = $item.closest( '.block-content' ).find( '.subitems-n' );
							var $block = $item.closest( '.block-content' );
						}
						var $inputs = $subitems.length ? $subitems.find( 'input[type="checkbox"]' ) : 0;
						var count   = $subitems.length ? $inputs.length : 0;
						var checked = $subitems.find( 'input[type="checkbox"]:checked' ).length;
						var $text   = $block.find( '.how-much-selected' );
						var text    = $text.data( 'text' );
						$text.text( text.replace( '{0}', checked ).replace( '{1}', count ) );
						var $main_checkbox = $block.find( '.checkbox-main:first' );
						if (checked && ! $main_checkbox.is( ':checked' )) {
							$main_checkbox.attr( 'checked', 'checked' ).prop( 'checked', true ); // set main choice.
						} else if ( ! checked && $main_checkbox.is( ':checked' )) {
							$main_checkbox.removeAttr( 'checked', 'checked' ).prop( 'checked', false ); // unset main choice.
						}
					} catch ( e ) {
						console.log( 'Count update error: ', e );
					}
				}
				// set columns number & update 'X of Y' value.
				$( '.how-much-selected' ).each(
					function() {
						var $subitems     = $( this ).closest( '.block-content' ).find( '.subitems-n' );
						var count         = $subitems.find( 'input[type="checkbox"]' ).length;
						var class_columns = ( count > 10 ) ? 'columns-3' : ( count > 6 ? 'columns-2' : 'columns-1' );
						$subitems.addClass( class_columns );
						func_update_count( $subitems.find( 'input[type="checkbox"]:first' ), $subitems );
					}
				);
				$( '.subitems-n input[type="checkbox"]' ).on(
					'change',
					function() {
						func_update_count( $( this ) );
					}
				);
				$( '.scope-block-n input.checkbox-main' ).on(
					'change',
					function() {
						var is_checked = $( this ).is( ':checked' );
						if ( is_checked ) {
							// check all subitems.
							var $items = $( this ).closest( '.block-content' ).find( '.subitems-n input[type="checkbox"]:not(:checked)' );
							if ( $items.length ) {
								$items.each(
									function() {
										$( this ).attr( 'checked', 'checked' ).prop( 'checked',true );
									}
								)
								func_update_count( $items.first() );
							}
						} else {
							// uncheck all subitems.
							var $items = $( this ).closest( '.block-content' ).find( '.subitems-n input[type="checkbox"]:checked' );
							if ( $items.length ) {
								$items.each(
									function() {
										$( this ).removeAttr( 'checked', 'checked' ).prop( 'checked',false );
									}
								)
								func_update_count( $items.first() );
							}
						}

					}
				)
			}
			if ( $( '.scope-block-n' ).length ) {
				// replace many input values from each [data-tax] block with single variable.
				var $form = $( '.scope-block-n:first' ).closest( 'form' );
				function refill_values() {
					$form.find( 'option.form-hidden-js' ).remove();
					// walk on all subitems.
					$form.find( 'ul.subitems-n[data-tax]' ).each(
						function() {
							var $block   = $( this );
							var var_name = $block.data( 'var' );
							var selected = $block.find( 'input:checked' ).map( function() { return $( this ).val(); } ).toArray().join( ' ' );
							var $input   = $( '<input type="hidden" class="form-hidden-js">' ).attr( 'name', var_name ).val( selected );
							$form.append( $input );
						}
					)
					$form.append( $( '<input type="hidden" class="form-hidden-js" name="ah_options_n" value="1">' ) );
				}

				refill_values();
				$form.find( 'ul.subitems-n[data-tax] input' ).on( 'change', refill_values ).each( function() { $( this ).removeAttr( 'name' )} );
				$form.find( '.checkbox-main' ).on( 'change', refill_values );
			}

			/**
			 * Copy content of textarea or input element into clipboard
			 *
			 * @param elem
			 */
			function copy_to_clipboard( elem ) {
				// save current selection and focus.
				var original_selection_start = elem.selectionStart;
				var original_selection_end   = elem.selectionEnd;
				var original_focused_elem    = document.activeElement;
				// select the content.
				elem.focus();
				elem.setSelectionRange( 0, elem.value.length );
				// copy the selection.
				var succeed;
				try {
					succeed = document.execCommand( 'copy' );
				} catch (e) {
					succeed = false;
				}
				// restore original focus.
				if (original_focused_elem && 'function' === typeof original_focused_elem.focus) {
					original_focused_elem.focus();
				}
				// restore prior selection.
				elem.setSelectionRange( original_selection_start, original_selection_end );
				return succeed;
			}
		}
	)

	// Shared between Wizard last step and Content audit.
	window.ahrefs_progress = {
		/**
		 * Update progress position and text
		 *
		 * @param $block_progress Block with both position and progress.
		 * @param new_value int|null Null to initialize with 0% position.
		 * @param skip_animation bool Do not use animation.
		 */
		move_to: function( $block_progress, new_value, skip_animation ) {
			var $bar  = $block_progress.find( '.position' );
			var $text = $block_progress.find( '.progress' );

			if ( null === new_value ) {
				$bar.stop( true );
				$bar.css( 'width', '0%' );
				$text.text( content_strings.audit.progress_initial );
				return;
			}
			new_value      = new_value > 100 ? 100.0 : new_value;
			var progress_s = ( 1.0 * new_value ).toFixed( 1.0 * new_value < 10.0 ? 2 : 1 );
			$bar.stop( true );

			if ( skip_animation ) {
				$bar.css( 'width', '' + progress_s + '%' );
				$text.text( content_strings.audit.progress_percents.replaceAll( '{0}', progress_s ) );
			} else {
				var last_percents = '';
				$bar.animate(
					{
						width: '' + progress_s + '%',
					},
					{
						duration: 15000,
						queue: false,
						easing: 'swing',
						step: function(now, fx) {
							var progress_now = ( 1.0 * now ).toFixed( 1.0 * now < 10.0 ? 2 : 1 );
							if ( last_percents !== progress_now ) {
								last_percents = progress_now;
								$text.text( content_strings.audit.progress_percents.replaceAll( '{0}', progress_now ) );
							}
						},
						complete: function() {
							$text.text( content_strings.audit.progress_percents.replaceAll( '{0}', progress_s ) );
						}
					}
				);
			}
		}
	};

})( jQuery )

// Wizard last step.
if ( jQuery( '#progressbar' ).length ) {
	( function($) {
		"use strict";

		window.progress = {
			$progress: $( '#progressbar' ),
			$submit: $( '#ahrefs_seo_submit' ),
			$step: $( '.steps .group-3' ),
			nonce: $( '#_wpnonce' ).val(),
			referer: $( 'input[name="_wp_http_referer"]' ).val(),
			timer: null,
			last_percents: 0,
			last_request: null,
			wait_time: 120000, // 2 minutes.
			/**
			 * Update progress bar position.
			 * Can increase progress only.
			 *
			 * @param int percents
			 */
			set_progress : function( percents ) {
				if ( percents >= progress.last_percents ) {
					ahrefs_progress.move_to( progress.$progress, percents );
					progress.last_percents = percents;
				}
			},
			/**
			 * Initialize, run updates.
			 */
			init: function() {
				progress.timer = window.setInterval( progress.update, 5000 );
				progress.update();
			},
			/**
			 * Make request to server and possibly update of progress or finish it.
			 */
			update: function() {
				var now = new Date().getTime();
				if ( null === progress.last_request || progress.last_request + progress.wait_time > now ) {
					progress.last_request = now;
					$.ajax(
						{
							url: ajaxurl,
							method: 'post',
							data: {
								_wpnonce: progress.nonce,
								_wp_http_referer: progress.referer,
								action: 'ahrefs_progress',
								_: Math.random(),
							},
							success: function( response ) {
								if ( response['success'] ) {
									if ( response['data'] ) {
										if ( response['data']['percents'] ) {
											progress.set_progress( response['data']['percents'] );
										}
										if ( response['data']['finish'] ) {
											progress.finish();
										}
										if ( response['data']['paused'] ) {
											progress.$submit.trigger( 'click' );
										}
									}
								} else {
									console.log( response );
								}
								progress.last_request = null;
							},
							error: function( jqXHR, exception ) {
								console.log( jqXHR, exception );
								progress.last_request = null;
							}
						}
					);
				}
			},
			/**
			 * Set update process is finished.
			 */
			finish: function() {
				// Stop updates.
				window.clearTimeout( progress.timer );
				progress.timer = null;
				// Show green 100% progress bar.
				progress.set_progress( 100 );
				progress.$progress.addClass( 'completed' );
				// Step title completed.
				progress.$step.addClass( 'finished' );
			}
		}
		// Initialize.
		progress.init();
	} )( jQuery );
}

if ( jQuery( '#schedule_content_audits' ).length ) {
	( function($) {
		"use strict";

		$( '#schedule_frequency' ).on(
			'change',
			function() {
				var value = $( this ).val();
				if ( 'ahrefs_daily' === value  ) {
					$( '#schedule_day_wrap' ).hide();
					$( '.schedule_every' ).hide();
					$( '#schedule_each' ).show();
				} else if ( 'ahrefs_weekly' == value ) {
					$( '#schedule_day_of_week' ).show();
					$( '#schedule_day_of_month' ).hide();
					$( '#schedule_day_wrap' ).show();
					$( '.schedule_every' ).hide();
					$( '#schedule_each' ).show();
				} else if ( 'ahrefs_monthly' == value ) {
					$( '#schedule_day_of_week' ).hide();
					$( '#schedule_day_of_month' ).show();
					$( '#schedule_day_wrap' ).show();
					$( '.schedule_every' ).show();
					$( '#schedule_each' ).hide();
				}
			}
		).trigger( 'change' );
	})( jQuery );
};

var ahrefs_settings = (function($) {
	"use strict";
	var received_results       = 0;
	var on_recommended_updated = function() {
		if ( 2 === ++received_results ) {
			$( '.ahrefs-analytics' ).removeClass( 'autodetect' );
			if ( '' === $( '#analytics_account' ).val() ) {
				$( '#analytics_account' ).addClass( 'incorrect-value' );
				$( '.ahrefs-analytics' ).addClass( 'autodetect-no-account' );
			}
			if ( '' === $( '#gsc_account' ).val() ) {
				$( '#gsc_account' ).addClass( 'incorrect-value' );
				$( '.ahrefs-analytics' ).addClass( 'autodetect-no-account' );
			}
			received_results = 0;
		};
	}
	var load_recommended_ga    = function() {
		$.ajax(
			{
				url: ajaxurl,
				method: 'post',
				async: true,
				data: {
					_wpnonce: $( '#_wpnonce' ).val(),
					referer: $( 'input[name="_wp_http_referer"]' ).val(),
					action: 'ahrefs_seo_options_ga_detect',
				},
				success: function( response ) {
					var updated = false;
					if ( response['success'] ) {
						if ( response['data'] ) {
							if ( response['data']['ga'] ) {
								$( '#analytics_account' ).val( response['data']['ga'] ).trigger( 'change' );
								updated = true;
							}
						}
					} else {
						console.log( response );
					}
					if ( ! updated ) {
						$( '#analytics_account' ).val( '' ).trigger( 'change' );
					}
					on_recommended_updated();
				},
				error: function( jqXHR, exception ) {
					$( '#analytics_account' ).val( '' ).trigger( 'change' );
					on_recommended_updated();
					console.log( jqXHR, exception );
				}
			}
		)
	}
	var load_recommended_gsc   = function() {
		$.ajax(
			{
				url: ajaxurl,
				method: 'post',
				async: true,
				data: {
					_wpnonce: $( '#_wpnonce' ).val(),
					referer: $( 'input[name="_wp_http_referer"]' ).val(),
					action: 'ahrefs_seo_options_gsc_detect',
				},
				success: function( response ) {
					var updated = false;
					if ( response['success'] ) {
						if ( response['data'] ) {
							if ( response['data']['gsc'] ) {
								$( '#gsc_account' ).val( response['data']['gsc'] ).trigger( 'change' );
								updated = true;
							}
						}
					} else {
						console.log( response );
					}
					if ( ! updated ) {
						$( '#gsc_account' ).val( '' ).trigger( 'change' );
					}
					on_recommended_updated();
				},
				error: function( jqXHR, exception ) {
					$( '#gsc_account' ).val( '' ).trigger( 'change' );
					on_recommended_updated();
					console.log( jqXHR, exception );
				}
			}
		)
	}

	var autodetect = function() {
		$( '#loader_ga' ).show();
		$( this ).hide();
		$( '.ahrefs-analytics' ).addClass( 'autodetect' );
		load_recommended_ga();
		load_recommended_gsc();
		return false;
	};
	return { autodetect: autodetect, };
})( jQuery );

(function($) {
	"use strict";

	$(
		function() {
			if ( '#reconnect' === document.location.hash ) {
				var $disconnect_link = $( '#ahrefs_seo_screen .account-actions .disconnect-button' );
				if ( $disconnect_link.length && $disconnect_link.is( ':visible' ) ) {
					$disconnect_link.addClass( 'item-flash' );
					setTimeout(
						function () {
							$disconnect_link.removeClass( 'item-flash' );
						},
						5000
					);
				}
			}
		}
	);
})( jQuery );

// phpcs:disable Squiz.Commenting.InlineComment.InvalidEndChar
/*! https://mths.be/base64 v0.1.0 by @mathias | MIT license */(function(root) {

	// Detect free variables `exports`.
	var freeExports = typeof exports == 'object' && exports;

	// Detect free variable `module`.
	var freeModule = typeof module == 'object' && module &&
		module.exports == freeExports && module;

	// Detect free variable `global`, from Node.js or Browserified code, and use
	// it as `root`.
	var freeGlobal = typeof global == 'object' && global;
	if (freeGlobal.global === freeGlobal || freeGlobal.window === freeGlobal) {
		root = freeGlobal;
	}

	/*--------------------------------------------------------------------------*/

	var InvalidCharacterError            = function(message) {
		this.message = message;
	};
	InvalidCharacterError.prototype      = new Error();
	InvalidCharacterError.prototype.name = 'InvalidCharacterError';

	var error = function(message) {
		// Note: the error messages used throughout this file match those used by
		// the native `atob`/`btoa` implementation in Chromium.
		throw new InvalidCharacterError( message );
	};

	var TABLE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
	// http://whatwg.org/html/common-microsyntaxes.html#space-character
	var REGEX_SPACE_CHARACTERS = /[\t\n\f\r ]/g;

	// `decode` is designed to be fully compatible with `atob` as described in the
	// HTML Standard. http://whatwg.org/html/webappapis.html#dom-windowbase64-atob
	// The optimized base64-decoding algorithm used is based on @atk’s excellent
	// implementation. https://gist.github.com/atk/1020396
	var decode = function(input) {
		input      = String( input )
			.replace( REGEX_SPACE_CHARACTERS, '' );
		var length = input.length;
		if (length % 4 == 0) {
			input  = input.replace( /==?$/, '' );
			length = input.length;
		}
		if (
			length % 4 == 1 ||
			// http://whatwg.org/C#alphanumeric-ascii-characters
			/ [ ^ +a - zA - Z0 - 9 / ] / .test( input )
		) {
			error(
				'Invalid character: the string to be decoded is not correctly encoded.'
			);
		}
		var bitCounter = 0;
		var bitStorage;
		var buffer;
		var output   = '';
		var position = -1;
		while (++position < length) {
			buffer     = TABLE.indexOf( input.charAt( position ) );
			bitStorage = bitCounter % 4 ? bitStorage * 64 + buffer : buffer;
			// Unless this is the first of a group of 4 characters…
			if (bitCounter++ % 4) {
				// …convert the first 8 bits to a single ASCII character.
				output += String.fromCharCode(
					0xFF & bitStorage >> (-2 * bitCounter & 6)
				);
			}
		}
		return output;
	};

	// `encode` is designed to be fully compatible with `btoa` as described in the
	// HTML Standard: http://whatwg.org/html/webappapis.html#dom-windowbase64-btoa
	var encode = function(input) {
		input = String( input );
		if (/[^\0-\xFF]/.test( input )) {
			// Note: no need to special-case astral symbols here, as surrogates are
			// matched, and the input is supposed to only contain ASCII anyway.
			error(
				'The string to be encoded contains characters outside of the ' +
				'Latin1 range.'
			);
		}
		var padding  = input.length % 3;
		var output   = '';
		var position = -1;
		var a;
		var b;
		var c;
		var buffer;
		// Make sure any padding is handled outside of the loop.
		var length = input.length - padding;

		while (++position < length) {
			// Read three bytes, i.e. 24 bits.
			a      = input.charCodeAt( position ) << 16;
			b      = input.charCodeAt( ++position ) << 8;
			c      = input.charCodeAt( ++position );
			buffer = a + b + c;
			// Turn the 24 bits into four chunks of 6 bits each, and append the
			// matching character for each of them to the output.
			output += (
				TABLE.charAt( buffer >> 18 & 0x3F ) +
				TABLE.charAt( buffer >> 12 & 0x3F ) +
				TABLE.charAt( buffer >> 6 & 0x3F ) +
				TABLE.charAt( buffer & 0x3F )
			);
		}

		if (padding == 2) {
			a       = input.charCodeAt( position ) << 8;
			b       = input.charCodeAt( ++position );
			buffer  = a + b;
			output += (
				TABLE.charAt( buffer >> 10 ) +
				TABLE.charAt( (buffer >> 4) & 0x3F ) +
				TABLE.charAt( (buffer << 2) & 0x3F ) +
				'='
			);
		} else if (padding == 1) {
			buffer  = input.charCodeAt( position );
			output += (
				TABLE.charAt( buffer >> 2 ) +
				TABLE.charAt( (buffer << 4) & 0x3F ) +
				'=='
			);
		}

		return output;
	};

	var base64 = {
		'encode': encode,
		'decode': decode,
		'version': '0.1.0'
	};

	// Some AMD build optimizers, like r.js, check for specific condition patterns
	// like the following:
	if (
		typeof define == 'function' &&
		typeof define.amd == 'object' &&
		define.amd
	) {
		define(
			function() {
				return base64;
			}
		);
	} else if (freeExports && ! freeExports.nodeType) {
		if (freeModule) { // in Node.js or RingoJS v0.8.0+
			freeModule.exports = base64;
		} else { // in Narwhal or RingoJS v0.7.0-
			for (var key in base64) {
				base64.hasOwnProperty( key ) && (freeExports[key] = base64[key]);
			}
		}
	} else { // in Rhino or a web browser
		root.base64 = base64;
	}

}(this));
