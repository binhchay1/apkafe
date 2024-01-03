(function($){

	/**
	 * AIOSRS Frontend
	 *
	 * @class WP_Schema_Pro_Settings
	 * @since 1.0
	 */
	WP_Schema_Pro_Settings = {
		
		init: function() {
			
			var self = this;
			this.customFieldDependecy();
			this.customImageSelect();
			this.initRepeater();
			this.toolTips();
		},

		toolTips: function() {

			$(document).on('click', '.wp-schema-pro-tooltip-icon', function(e){

				e.preventDefault();
				$('.wp-schema-pro-tooltip-wrapper').removeClass('activate');
				$(this).parent().addClass('activate');
			});

			$(document).on('click', function(e){

				if( ! $(e.target).hasClass('wp-schema-pro-tooltip-description') && ! $(e.target).hasClass('wp-schema-pro-tooltip-icon') && $(e.target).closest('.wp-schema-pro-tooltip-description').length == 0 ) {
					$('.wp-schema-pro-tooltip-wrapper').removeClass('activate');
				}
			});
		},

		customImageSelect: function(){
			
			var file_frame;
			window.inputWrapper = '';

			$( document.body ).on('click', '.image-field-wrap .aiosrs-image-select', function(e) {

				e.preventDefault();

				window.inputWrapper = $(this).closest('td');

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
				var parent = $(this).closest( 'td' );
				parent.find( '.image-field-wrap' ).removeClass( 'bsf-custom-image-selected' );
				parent.find( '.single-image-field' ).val('');
				parent.find( '.image-field-wrap img' ).removeAttr( 'src' );
			});

			var file_frame;
			window.inputWrapper = '';
		},

		customFieldDependecy: function(){

			jQuery(document).on( 'change', '#post-body-content .wp-schema-pro-custom-option-select, .aiosrs-pro-setup-wizard-content.general-setting-content-wrap .wp-schema-pro-custom-option-select', function(){
				var custom_wrap = jQuery(this).next('.custom-field-wrapper');

				custom_wrap.css('display', 'none');
				if( 'custom' == jQuery(this).val() ) {
					custom_wrap.css('display', '');
				}
			});

			jQuery(document).on( 'change', 'select[name="wp-schema-pro-general-settings[site-represent]"]', function(){
				var wrapper   = jQuery(this).closest('table'),
					logo_wrap         = wrapper.find('.wp-schema-pro-site-logo-wrap'),
					company_name_wrap = wrapper.find('.wp-schema-pro-site-name-wrap');
					person_name_wrap  = wrapper.find('.wp-schema-pro-person-name-wrap');

				//logo_wrap.css('display', 'none');
				company_name_wrap.css('display', 'none');
				person_name_wrap.css('display', 'none');
				if( '' != jQuery(this).val() ) {

					if( 'organization' == jQuery(this).val() ) {
						logo_wrap.css('display', '');
						company_name_wrap.css('display', '');
					} else {
						person_name_wrap.css('display', '');
					}
				}
			});
		},

		initRepeater: function() {

			$(document).on( 'click', '.bsf-repeater-add-new-btn', function( event ) {
				event.preventDefault();

				var selector = $(this),
					parent_wrap = selector.closest( '.bsf-aiosrs-schema-type-wrap' ),
					total_count = parent_wrap.find('.aiosrs-pro-repeater-table-wrap').length,
					template    = parent_wrap.find('.aiosrs-pro-repeater-table-wrap').first().clone();

				template.find( 'input, textarea, select' ).each(function(index, el) {
					$(this).val('');

					var field_name  = 'undefined' != typeof $(this).attr('name') ? $(this).attr('name').replace('[0]', '['+ total_count +']') : '',
						field_class = 'undefined' != typeof $(this).attr('class') ? $(this).attr('class').replace('-0-', '-'+ total_count +'-') : '',
						field_id	= 'undefined' != typeof $(this).attr('id') ? $(this).attr('id').replace('-0-', '-'+ total_count +'-') : '';

					$(this).attr( 'name', field_name );
					$(this).attr( 'class', field_class );
					$(this).attr( 'id', field_id );
				});

				template.insertBefore(selector);
			});

			$(document).on( 'click', '.bsf-repeater-close', function( event ) {
				event.preventDefault();

				var selector = $(this),
					parent_wrap = selector.closest( '.bsf-aiosrs-schema-type-wrap' ),
					repeater_count = parent_wrap.find('> .aiosrs-pro-repeater-table-wrap').length;

				if ( repeater_count > 1 ) {
					selector.closest('.aiosrs-pro-repeater-table-wrap').remove();
				}
			});
		},

	}

	/* Initializes the AIOSRS Frontend. */
	$(function(){

		WP_Schema_Pro_Settings.init();
	});
})(jQuery);