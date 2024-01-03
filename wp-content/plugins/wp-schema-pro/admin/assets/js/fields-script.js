(function($){

	$(document).ready(function($) {
		
		$(document).on('change input', '.bsf-rating-field', function() {

			var star_wrap = $(this).next('.aiosrs-star-rating-wrap'),
				value     = $(this).val(),
				filled    = ( value > 5 ) ? 5 : ( ( value < 0 ) ? 0 : parseInt(value) ),
				half      = ( value == filled || value > 5 || value < 0 ) ? 0 : 1,
				empty     = 5 - ( filled + half );

			star_wrap.find('span').each(function(index, el) {
				$(this).removeClass('dashicons-star-filled dashicons-star-half dashicons-star-empty');
				if( index < filled ) {
					$(this).addClass('dashicons-star-filled')
				} else if( index == filled && half == 1 ) {
					$(this).addClass('dashicons-star-half')
				} else {
					$(this).addClass('dashicons-star-empty')
				}
			});
		});

		$(document).on( 'click', '.aiosrs-star-rating-wrap:not(.disabled) > .aiosrs-star-rating', function( e ) {
			e.preventDefault();
			var index         = $(this).data('index');
				star_wrap     = $(this).parent();

			star_wrap.prev('.bsf-rating-field').val( index );

			star_wrap.find('.aiosrs-star-rating').each(function(i, el) {
				$(this).removeClass('dashicons-star-filled dashicons-star-half dashicons-star-empty');
				if( i < index ) {
					$(this).addClass('dashicons-star-filled')
				} else {
					$(this).addClass('dashicons-star-empty')
				}
			});
		});

		$(document).on( 'change', '#aiosrs-pro-custom-fields .aiosrs-pro-custom-field-checkbox input[type="checkbox"]', function( e ) {
			e.preventDefault();
			
			var siblings = $(this).closest('tr.row').siblings('tr.row');
			if( $(this).prop('checked') ) {
				siblings.show();
			} else {
				siblings.hide();
			}
		});

		$('#aiosrs-pro-custom-fields .aiosrs-pro-custom-field-checkbox input[type="checkbox"]').trigger('change');

		$(document).on( 'click', '.aiosrs-reset-rating', function( e ) {
			e.preventDefault();

			if( confirm( AIOSRS_Rating.reset_rating_msg ) ) {
				var parent = $(this).closest('.aiosrs-pro-custom-field-rating');
				
				var schema_id = $(this).data('schema-id');
				$(this).addClass('reset-disabled');
				parent.find('.spinner').addClass('is-active');

				jQuery.ajax({
					url: ajaxurl,
					type: 'post',
					dataType: 'json',
					data: {
						action: 'aiosrs_reset_post_rating',
						post_id: AIOSRS_Rating.post_id,
						schema_id: schema_id,
						nonce: AIOSRS_Rating.reset_rating_nonce
					}
				}).success(function( response ) {
					if( 'undefined' != typeof response['success'] && response['success'] == true ) {
						var avg_rating   = response['rating-avg'],
							review_count = response['review-count'];

						parent.find('.aiosrs-rating').text(avg_rating);
						parent.find('.aiosrs-rating-count').text(review_count);

						parent.find('.aiosrs-star-rating-wrap > .aiosrs-star-rating')
							.removeClass('dashicons-star-filled dashicons-star-half dashicons-star-empty')
							.addClass('dashicons-star-empty');

					} else {
						$(this).removeClass('reset-disabled');
					}
					parent.find('.spinner').removeClass('is-active');
				});
			}
		});

		$(document).on('change', '.multi-select-wrap select', function() {

			var multiselect_wrap = $(this).closest('.multi-select-wrap'),
				select_wrap      = multiselect_wrap.find('select'),
				input_field      = multiselect_wrap.find('input[type="hidden"]'),
				value            = select_wrap.val();

			if( 'undefined' != typeof value && null != value && value.length > 0 ) {
				input_field.val( value.join(',') );
			} else {
				input_field.val('');
			}
		});
		
		$(document).on('change input', '.time-duration-wrap input', function() {

			var duration_wrap = $(this).closest('.aiosrs-pro-custom-field-time-duration'),
				input_field   = duration_wrap.find('.time-duration-field'),
				day           = duration_wrap.find('.time-duration-day').val(),
				hour          = duration_wrap.find('.time-duration-hour').val(),
				min           = duration_wrap.find('.time-duration-min').val(),
				sec           = duration_wrap.find('.time-duration-sec').val(),
				value         = '';

			if ( '' != day || '' != hour || '' != min || '' != sec ) {

				value = 'P';
				if( '' != day ) {
					value += day+'D';
				}
				
				if( '' != hour || '' != min || '' != sec ) {
					value += 'T';
					if( '' != hour ) {
						value += hour+'H';
					}
					if( '' != min ) {
						value += min+'M';
					}
					if( '' != sec ) {
						value += sec+'S';
					}
				}
			}
			input_field.val( value );
		});

		// Verticle Tabs
		$(document).on('click', '.aiosrs-pro-meta-fields-tab', function(e) {
			e.preventDefault();

			var id = $(this).data('tab-id');
			$(this).siblings('.aiosrs-pro-meta-fields-tab').removeClass('active');
			$(this).addClass('active');

			$('#aiosrs-pro-custom-fields').find('.aiosrs-pro-meta-fields-wrap').removeClass('open');
			$('#aiosrs-pro-custom-fields').find('.'+id).addClass('open');
		});

		// Call Tooltip
		$('.bsf-aiosrs-schema-heading-help').tooltip({
			content: function() {
				return $(this).prop('title');
			},
			tooltipClass: 'bsf-aiosrs-schema-ui-tooltip',
			position: {
				my: 'center top',
				at: 'center bottom+10',
			},
			hide: {
				duration: 200,
			},
			show: {
				duration: 200,
			},
		});

		var file_frame;
		window.inputWrapper = '';

		$( document.body ).on('click', '.image-field-wrap .aiosrs-image-select', function(e) {

			e.preventDefault();

			window.inputWrapper = $(this).closest('.bsf-aiosrs-schema-custom-text-wrap, .aiosrs-pro-custom-field-image');

			// Create the media frame.
			file_frame = wp.media( {
				button: {
					text: 'Select Image',
					close: false
				},
				states: [
					new wp.media.controller.Library({
						title: 'Select Custom Image',
						library: wp.media.query({ type: 'image' }),
						multiple: false,
					})
				]
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {

				var attachment = file_frame.state().get( 'selection' ).first().toJSON();
				
				var image = window.inputWrapper.find('.image-field-wrap img');
				if ( image.length == 0 ) {
					window.inputWrapper.find('.image-field-wrap').append('<a href="#" class="aiosrs-image-select img"><img src="'+ attachment.url +'" /></a>');
				} else {
					image.attr( 'src', attachment.url );
				}
				window.inputWrapper.find( '.image-field-wrap' ).addClass( 'bsf-custom-image-selected' );
				window.inputWrapper.find( '.single-image-field' ).val( attachment.id );

				file_frame.close();
			});

			file_frame.open();
		});


		$(document).on( 'click', '.aiosrs-image-remove', function( e ) {

			e.preventDefault();
			var parent = $(this).closest( '.bsf-aiosrs-schema-custom-text-wrap, .aiosrs-pro-custom-field-image' );
			parent.find( '.image-field-wrap' ).removeClass( 'bsf-custom-image-selected' );
			parent.find( '.single-image-field' ).val('');
			parent.find( '.image-field-wrap img' ).removeAttr( 'src' );
		});

		var file_frame;
		window.inputWrapper = '';
	});

})(jQuery);