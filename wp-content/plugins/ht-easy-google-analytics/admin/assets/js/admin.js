;( function ( $ ) {
    'use strict';

	// Remove loader
	$(window).on('load', function () {
		$('#htga4-loading').hide();
	});

	const Htga4Admin = {
		init: function(){
			// Init select2
			$('.htga4 select[multiple]').select2();
		},
	}

    $(document).ready(function(){

		Htga4Admin.init();

		// Open the Documentation & Need Help? links in new tab.
		$('.toplevel_page_ht-easy-ga4-setting-page ul li a').each(function(){
			if( $(this).text() == 'Documentation' || $(this).text() == 'Need Help?' ){
				$(this).attr('target', '_blank');
			}
		});

		// Popup close
		$('.htga4_pro_adv_popup_close').on('click', function(){
			const $this = $(this),
				$popup = $this.closest('.htga4_pro_adv_popup')
			$popup.removeClass('open')
		});

		/* Close on outside click */
		$(document).on('click', function(e){
			if(e.target.classList.contains('htga4_pro_adv_popup')) {
				e.target.classList.remove('open')
			}
		});

		// Open popup
		$('tr.htga4_no_pro th, .htga4-checkbox-switch').on('click', function(e){
			openAdvPopup();
		});

		function openAdvPopup(){
			$('.htga4_pro_adv_popup').addClass('open');
		}

		const $select_account 	= $('.htga4-select-account'),
			$select_property 	= $('.htga4-select-property'),
			$select_measurement = $('.htga4-select-measurement-id');

		let current_url = window.location.href;
		if( current_url.indexOf('ngrok') > -1 && $('.htga4_login .button').length ){
			const button_url = $('.htga4_login .button').attr('href');

			let new_url = updateQueryStringParameter(button_url, 'state', current_url);
			$('.htga4_login .button').attr('href', new_url);
		}

		// Listen to select account
		$select_account.on('change', function(e){
			removeAccessTokenFromURL();
			$('.htga4_data_stream_id').attr('value', '');

			var account = this.value;

			if( account ){
				// Request properties list
				$.ajax({
					url: htga4_params.ajax_url,
					type: 'POST',
					//dataType: 'json',
					data: {
						'action': 'htga4_get_properties',
						'nonce' : htga4_params.nonce,
						'account': account
					},
			
					beforeSend:function(){ 
						$select_property.html('<option value="">Loading . . .</option>');
					},
			
					success:function(response) {
						$select_property.html('<option value="">Select property</option>');
						// Prepare & append dropdown options
						for (let [key, value] of Object.entries(response.data)) {
							$select_property.append(`<option value="${key}">${value} <${key}></option>`);
						}

						$select_property.removeAttr('disabled', '');
					},
			
					complete:function( response ){
					},
			
					error: function(errorThrown){
						console.log(errorThrown);
					}
				});
				
			} else {
				$select_property.html('<option value="">Select account</option>');
				$select_measurement.html('<option value="">Select measurement ID</option>');
				$select_property.attr('disabled', 'disabled');
				$select_measurement.attr('disabled', 'disabled');
			}
		});

		// Listen to select property
		$select_property.on('change', function(e){
			removeAccessTokenFromURL();
			$('.htga4_data_stream_id').attr('value', '');

			var property = this.value;

			if( property ){
				// Request measurement id list
				$.ajax({
					url: htga4_params.ajax_url,
					type: 'POST',
					//dataType: 'json',
					data: {
						'action': 'htga4_get_data_streams',
						'nonce' : htga4_params.nonce,
						'property': property
					},
			
					beforeSend:function(){ 
						$select_measurement.html('<option value="">Loading . . .</option>');
					},
			
					success:function(response) {
						if( !response.success ){
							$select_measurement.html(`<option value="">${response.data.message}</option>`);
						}

						if( response.success ){
							$select_measurement.html('<option value="">Select measurement ID</option>');

							// Prepare & append dropdown options
							for (let [key, value] of Object.entries(response.data)) {
								let data_stream_id = value.name.split('/');
									data_stream_id = data_stream_id[data_stream_id.length - 1];

								let measurement_id = value.webStreamData.measurementId;

								$select_measurement.append(`<option value="${measurement_id}" data-stream_id="${data_stream_id}">${value.displayName} &#60;${measurement_id}&#62;</option>`);
							}

							$select_measurement.removeAttr('disabled', '');

							
						}
					},
			
					complete:function( response ){
					},
			
					error: function(errorThrown){
						console.log(errorThrown);
					}
				});
				
			} else {
				$select_measurement.html('<option value="">Select measurement ID</option>');
				$select_measurement.attr('disabled', 'disabled');
			}
		});

		// Listen to select measurement
		$select_measurement.on('change', function(e){
			var data_stream_id = $('option:selected', this).attr('data-stream_id') ? $('option:selected', this).attr('data-stream_id') : '';
			
			if( $('.htga4_data_stream_id').length ){
				$('.htga4_data_stream_id').attr('value', data_stream_id);
			}
		});

		function removeAccessTokenFromURL(){
			let $field = $('.htga4_general_options input[name="_wp_http_referer"]');
			let wp_http_referer = $field.attr('value');

			// Remove the "token" & "email" query string from the value
			wp_http_referer = wp_http_referer.replace(/&?access_token=[^&]+/, '');
			wp_http_referer = wp_http_referer.replace(/(\?|&)email=[^&]*(&|$)/, '');

			$field.attr('value', wp_http_referer);
		}

		function updateQueryStringParameter(uri, key, value) {
			var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
			var separator = uri.indexOf('?') !== -1 ? "&" : "?";
			if (uri.match(re)) {
			  return uri.replace(re, '$1' + key + "=" + value + '$2');
			}
			else {
			  return uri + separator + key + "=" + value;
			}
		}
		
    }); // document ready

} )( jQuery );