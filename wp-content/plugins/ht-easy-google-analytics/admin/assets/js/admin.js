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

		$("form.htga4")
		.on("input change", function (e) {
			$(this).find("#submit").removeAttr("disabled");
		})
		.on("submit", function (e) {
			e.preventDefault();

			// Disable the submit button with attribute "disabled" to prevent multiple clicks
			$(this).find("#submit").attr("disabled", "disabled");
		
			const formData = new FormData(this);
			const $_this = $(this);
		
			const formValues = {};
			for (let [name, value] of formData.entries()) {
			  const ignoreInputs = [
				"action",
				"option_page",
				"submit",
				"_wp_http_referer",
				"_wpnonce",
			  ];
		
			  if (!ignoreInputs.includes(name)) {
				if (name.endsWith("[]")) {
				  name = name.replace("[]", ""); // remove [] from the name
		
				  formValues[name] = (formValues[name] || []).concat(value);
				} else {
				  formValues[name] = value;
				}
			  }
			}

			const $notification = $(
				'<div class="htga4-ajax-notification">Saved!</div>'
			  );
			  $("body").append($notification);

			$.ajax({
				url: htga4_params.ajax_url,
				type: "POST",
				data: {
				  action: "htga4_save_options",
				  formValues: formValues,
				  nonce: htga4_params.nonce,
				},
		  
				beforeSend: function () {
				  $_this.find("#submit").css("pointer-events", "none").val("Saving...");
				},
		  
				success: function (response) {
				  $notification.addClass("open");
				},
		  
				complete: function (response) {
				  $_this
					.find("#submit")
					.css("pointer-events", "initial")
					.val("Save Changes");
		  
				  setTimeout(function () {
					$notification.removeClass("open");
				  }, 3000);
				},
		  
				error: function (errorThrown) {
				  console.log(errorThrown);
				},
			});
		});

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
		$('tr.htga4_no_pro th, .htga4-checkbox-switch, .htga4_no_pro').on('click', function(e){
			openAdvPopup();
		});

		function openAdvPopup(){
			$('.htga4_pro_adv_popup').addClass('open');
		}

		const $select_account 	= $('.htga4-select-account'),
			$select_property 	= $('.htga4-select-property'),
			$select_measurement = $('.htga4-select-measurement-id');

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
							// Loop through the object and prepare the dropdown options
							$select_measurement.html('<option value="">Select measurement ID</option>');
							for (let [key, value] of Object.entries(response.data)) {
								$select_measurement.append(`<option value="${value.measurement_id}" data-stream_id="${key}">${value.display_name} &#60;${value.measurement_id}&#62;</option>`);
							}
						}

						$select_measurement.removeAttr('disabled', '');

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