var affiliateLasso = affiliateLasso || {};
var ajax_url = lassoOptionsData.ajax_url;
var interval_time = 5000;
var initialFieldsValue = {};
var progessPercentage = 0;
var progressInterval = '';

function format_number_with_zero_before(num) {
	return num = (num >= 10) ? num : '0' + num;
}

// require moment.js
function convert_duration_to_time(miliseconds) {
	var duration = moment.duration(miliseconds);
	var s = duration.seconds();
	var m = duration.minutes();
	var h = duration.hours();
	var d = duration.days();

	s = format_number_with_zero_before(s);
	m = format_number_with_zero_before(m);
	h = format_number_with_zero_before(h);
	d = format_number_with_zero_before(d);
	d = (d == 0) ? '' : d + ' day(s) ';
	var time = h + ':' + m + ':' + s;
	var result = d + time;

	return result;
}

// request_time: miliseconds
function calculate_remaining_time(request_time, limit, total, completed) {
	request_time = parseInt(request_time);
	limit = parseInt(limit);
	total = parseInt(total);
	completed = parseInt(completed);

	var remaining_item = total - completed;
	var request_time_per_item = request_time / limit;
	var remaining_time = request_time_per_item * remaining_item;

	return convert_duration_to_time(remaining_time);
}

// get loading html
function get_loading_image() {
	return '<div class="py-5"><div class="ls-loader"></div></div>';
}
function get_loading_image_small() {
	return '<div class="loader-small"></div>';
}
function get_loading_image_small_red() {
	return '<div class="loader-small-red"></div>';
}
function get_loading_by_font_awesome() {
	return '<i class="far fa-circle-notch fa-spin"></i>';
}

function get_brag_icon() {
	return '<a class="lasso-brag d-none" href="#" target="_blank" rel="nofollow noindex"> ' +
		'<img src="' + lassoOptionsData.icon_brag +'" loading="lazy" alt="Lasso Brag" width="30" height="30"> ' +
		'</a>';
}

function lasso_loading_full() {
	jQuery('body').append('<div id="lasso-loading-full">' + get_loading_image() + '</div>');
	jQuery('#lasso-loading-full').css({
		'position': 'fixed',
		'width': '100%',
		'height': '100%',
		'background': 'rgba(255, 255, 255, 0.34)',
		'top': 0,
		'z-index': 9999,
		'display': 'flex',
	});
	jQuery('#lasso-loading-full').find('.spinner-dues').css({
		'align-self': 'center',
	});
	jQuery('#lasso-loading-full').hide();
}

function clear_notifications() {
    jQuery(".alert.red-bg.collapse").collapse('hide');
    jQuery(".alert.orange-bg.collapse").collapse('hide');
    jQuery(".alert.green-bg.collapse").collapse('hide');
}

(function (window, $, undefined) {
	'use strict';

	// Extract all input fields in the form
	function fetchAllOptions() {
		// Create an object
		var values = {};

		// Loop through all the inputs
		jQuery('form.lasso-admin-settings-form input, form.lasso-admin-settings-form select, form.lasso-admin-settings-form textarea').each(function () {
			var $field = jQuery(this);

			var name = $field.attr('name');
			
			var value;

			if ('checkbox' === $field.attr('type')) {
				value = $field.prop('checked');
			} else {
				value = $field.val();
			}

			values[name] = value;
		});

		// Create the objects
		values.newOrderOfIcons = {};

		// Loop through each active network
		jQuery('.lasso-active i').each(function () {
			var network = jQuery(this).data('network');
			values.newOrderOfIcons[network] = network;
		});

		return values;
	}

	// A function to show/hide conditionals
	function conditionalFields() {
		// Loop through all the fields that have dependancies
		jQuery('div[dep]').each(function () {
			// Fetch the conditional values
			var conDep = jQuery(this).attr('dep');

			var conDepVal = jQuery.parseJSON(jQuery(this).attr('dep_val'));
			var value;

			// Fetch the value of checkboxes or other input types
			if (jQuery('[name="' + conDep + '"]').attr('type') == 'checkbox') {
				value = jQuery('[name="' + conDep + '"]').prop('checked');
			} else {
				value = jQuery('[name="' + conDep + '"]').val();
			}

			// Show or hide based on the conditional values (and the dependancy must be visible in case it is dependant)
			if (jQuery.inArray(value, conDepVal) !== -1 && jQuery('[name="' + conDep + '"]').closest('.lasso-grid').is(':visible')) {
				jQuery(this).show();
			} else {
				jQuery(this).hide();
			}
		});

		if (lassop_check_val('floatStyleSource') == false && (lassop_select_val('sideDColorSet') == 'customColor' || lassop_select_val('sideDColorSet') == 'ccOutlines' || lassop_select_val('sideIColorSet') == 'customColor' || lassop_select_val('sideIColorSet') == 'ccOutlines' || lassop_select_val('sideOColorSet') == 'customColor' || lassop_select_val('sideOColorSet') == 'ccOutlines')) {
			jQuery('.sideCustomColor_wrapper').slideDown();
		} else {
			jQuery('.sideCustomColor_wrapper').slideUp();
		}
	}

	function lassop_select_val(name) {
		return jQuery('select[name="' + name + '"]').val();
	}

	function lassop_check_val(name) {
		return jQuery('[name="' + name + '"]').prop('checked');
	}

	// Header Menu
	function headerMenuInit() {
		var width = jQuery('.lasso-top-menu').parent().width();

		jQuery('.lasso-top-menu').css({
			width: width
		});

		// jQuery('.lasso-admin-wrapper').css('padding-top', '75px');
	}

	// Tab Navigation
	function tabNavInit() {
		jQuery('.lasso-tab-selector').on('click', function (event) {
			event.preventDefault ? event.preventDefault() : (event.returnValue = false);

			jQuery('html, body').animate({
				scrollTop: 0
			}, 0);

			var tab = jQuery(this).attr('data-link');

			jQuery('.lasso-admin-tab').hide();

			jQuery('#' + tab).show();

			jQuery('.lasso-header-menu li').removeClass('lasso-active-tab');

			jQuery(this).parents('li').addClass('lasso-active-tab');

			if ('lassop_styles' === tab) {
				affiliateLasso.activateHoverStates();
			}

			conditionalFields();

		});
	}

	// Checkboxes
	function checkboxesInit() {
		jQuery('.lasso-checkbox-toggle').off().on('click', function () {
			var status = jQuery(this).attr('status');

			var elem = jQuery(this).attr('field');

			if ('on' === status) {
				jQuery(this).attr('status', 'off');

				jQuery(elem).prop('checked', false);
			} else {
				jQuery(this).attr('status', 'on');

				jQuery(elem).prop('checked', true);
			}

			saveColorToggle();

			conditionalFields();
		});
	}

	function populateOptions() {
		jQuery('form.lasso-admin-settings-form input, form.lasso-admin-settings-form select').on('change', function () {
			conditionalFields();

			affiliateLasso.newOptions = fetchAllOptions();

			saveColorToggle();
		});

		affiliateLasso.defaultOptions = fetchAllOptions();
	}

	// A Function to change the color of the save button
	function saveColorToggle() {
		affiliateLasso.newOptions = fetchAllOptions();

		if (JSON.stringify(affiliateLasso.newOptions) !== JSON.stringify(affiliateLasso.defaultOptions)) {
			jQuery('.save-change-tab').removeClass('lasso-navy-button').addClass('lasso-red-button').trigger('classChange');
		} else {
			jQuery('.save-change-tab').removeClass('lasso-red-button').addClass('lasso-navy-button').trigger('classChange');
		}
	}

	// A Function send the array of setting to ajax.php
	function handleSettingSave(event, show_save = true) {
		// Block the default action
		event.preventDefault ? event.preventDefault() : (event.returnValue = false);
		// show progress bar
		lasso_helper.setProgressZero('.modal');
		lasso_helper.scrollTop();

		if(show_save) {
			jQuery("#url-save").modal('show');
		}

		getInitialValues();

		// Fetch all the settings
		var settings = fetchAllOptions();

		if ( 'keep_original_url' in settings && settings.keep_original_url.length > 0 ) {
			settings.keep_original_url = settings.keep_original_url.split('\n');
		}

		var tab = jQuery('.nav-link.purple.hover-underline.active').text().toLowerCase();

		// Prepare date
		var data = {
			action: 'lasso_store_settings',
			security: lassoOptionsData.optionsNonce,
			settings: settings,
			tab: tab,
			enable_amazon_prime: jQuery('input[name="enable_amazon_prime"]').val(),
			amazon_access_key_id: jQuery('input[name="amazon_access_key_id"]').val(),
			amazon_secret_key: jQuery('input[name="amazon_secret_key"]').val(),
			amazon_tracking_id: jQuery('input[name="amazon_tracking_id"]').val()
		};

		// Track send setting data
		lasso_segment_tracking('Lasso - Setting Data', {
			data: data
		});

		lasso_helper.setProgress(20, '.modal');
		
		// Send the POST request
		jQuery.post(ajaxurl, data, function (response) {
			if (response.success) {
				// refresh the page after save settings successful but not onboarding
				if(show_save) {
					location.reload();
				}
			} else {
				setTimeout(function() {
					console.error(response);
					lasso_helper.errorScreen('Error!');
				}, 1500);
			}				
		}).fail(function (jqXHR, status, error) {
			setTimeout(function() {
				lasso_helper.errorScreen("", "save-fail", "settings");
			}, 1500);
		})
		.always(function () {
			// hide progress bar
			hideProgressBar("#url-save");
		});
	}

	// increate current width of element by 25%
	function increase_width_25_percent() {
		var element = jQuery('.increase-width-25-percent');
		var current_width = element.width();
		var new_width = current_width * 1.25;
		element.width(new_width);
	}

	function update_action(data) {
		var start_time = new Date().getTime();
		jQuery.post(ajaxurl, data, function (response) {
				// Check insert post
				response = response.data;

				if (response.status == 1) {
					if (response.stop == 0) {
						var request_time = new Date().getTime() - start_time;
						if(data.request_time) {
							request_time = data.request_time
						}
						// Prepare new data for the next request
						data.page_amazon = response.page_amazon;
						data.page_basic = response.page_basic;
						data.page_all = response.page_all;

						data.amazon_product_count = response.amazon_product_count;
						data.basic_url_count = response.basic_url_count;
						data.all_count = response.all_count;

						data.amazon_product_total = response.amazon_product_total;
						data.basic_url_total = response.basic_url_total;
						data.all_total = response.all_total;

						data.current_date = response.current_date;

						// Progress to show in the message
						var message = 'Working... Please be patient. ';

						var amazon_product_total = parseInt(data.amazon_product_total);
						var basic_url_total = parseInt(data.basic_url_total);
						var all_total = parseInt(data.all_total);

						var page_amazon = parseInt(data.page_amazon);
						var page_basic = parseInt(data.page_basic);
						var page_all = parseInt(data.page_all);

						// Limit per request in backend
						var limit = 3;
						var amazon_limit = limit;
						var basic_limit = limit;
						var all_limit = limit;

						var remaining_amazon = amazon_product_total - (page_amazon * amazon_limit) + amazon_limit;
						var remaining_basic = basic_url_total - (page_basic * basic_limit) + basic_limit;
						var remaining_all = all_total - (page_all * all_limit) + all_limit;

						// amazon
						var remaining_time_amz = '';
						if(remaining_amazon > 0) {
							remaining_time_amz = calculate_remaining_time(request_time, limit, amazon_product_total, (page_amazon - 1) * limit);
							remaining_time_amz = `(ETA: ${remaining_time_amz})`;
						} else {
							remaining_amazon = 'Done';
						}

						// basic url
						var remaining_time_basic = '';
						if (remaining_amazon == 'Done') {
							remaining_basic = (remaining_basic > 0) ? remaining_basic : 'Done';
							remaining_time_basic = calculate_remaining_time(request_time, limit, basic_url_total, (page_basic - 1) * limit);
							remaining_time_basic = `(ETA: ${remaining_time_basic})`;
						} else {
							remaining_basic = 'Preparing';
						}

						// all posts/pages
						var remaining_time_all = '';
						if (remaining_basic == 'Done') {
							remaining_all = (remaining_all > 0) ? remaining_all : 'Done';
							remaining_time_basic = '';
							remaining_time_all = calculate_remaining_time(request_time, limit, all_total, (page_all - 1) * limit);
							remaining_time_all = `(ETA: ${remaining_time_all})`;
						} else {
							remaining_all = 'Preparing';
						}

						message += `
							<br><span class="d-block mt-2 mb-2">Remaining: </span>
							<p class="mb-1">Amazon products: ${ remaining_amazon } ${ remaining_time_amz }</p>
							<p class="mb-1">Basic urls: ${ remaining_basic } ${ remaining_time_basic }</p>
							<p class="mb-1">All posts/pages: ${ remaining_all } ${ remaining_time_all }</p>
						`;

						jQuery('.lasso-loading-message').width(300);
						lasso_helper.loadingScreen(message);
						data.request_time = request_time
						update_action(data);
					} else {
						lasso_helper.clearLoadingScreen();
					}
				} else {
					lasso_helper.errorScreen('Error');
				}
			})
			.fail(function (jqXHR, status, error) {
				lasso_helper.errorScreen(error);
			});
	}

	// Check license-If success move to Amazon tab.
	function active_license_key(){
		jQuery('#activate-license').click(function(event){
			event.preventDefault ? event.preventDefault() : (event.returnValue = false);
			// The loading screen
			showProgressBar("#license-activation-modal");
			var license = jQuery('[name = license_serial]').val();
			var settings = fetchAllOptions();
			let current_page = lasso_helper.get_page_name();
			jQuery.ajax({
				url:ajax_url,
				type:'post',
				data : {
					action: 'lasso_activate_license',
					security: lassoOptionsData.optionsNonce,
					license: license,
					settings: settings,
					onboarding: current_page === 'install',
				},
				beforeSend:function(xhr){
					if(jQuery('[name = license_serial]').val() == ""){
						jQuery("#activate-error label").html('Please enter your license key.');
						jQuery("#activate-error").collapse();
						jQuery("#license").addClass("red-border");
						xhr.abort();

						// Track user enter license key Event
						lasso_segment_tracking('Lasso User Enters License Key', {
							license: license
						});
					} else {
						jQuery("#license-activation-modal").modal();
					}
				}
			})
			.done(function(response) {
				response = response.data;

				if (typeof response === 'undefined') {
					return;
				}

				response.status = true;

				if(response.status) {
					jQuery("#license-activation-progress").attr('aria-valuenow', 100).css('width', '100%');
					setTimeout(function () {
						jQuery("#license-activation-modal").modal('hide');
						jQuery("#onboarding_container").removeClass("container-sm");
						jQuery('#onboarding_container .tab-item').addClass('d-none');
						jQuery('#onboarding_container div[data-step="theme"]').removeClass('d-none');
					}, 800);

					// Track license key validate successful Event
					lasso_segment_tracking('Lasso License Key Validated', {
						license: license
					});

					// This track event should call after track "key validate successful" event
					setTimeout(function () {
						// Track Step choose Default Display Box Theme
						lasso_segment_tracking('Lasso Step: Choose Default Display Box Theme');
					}, 1000);
				} else {
					jQuery("#license-activation-modal").modal('hide');
					jQuery("#activate-error label").html(response.error_message);
					jQuery("#activate-error").collapse();
					jQuery("#license").addClass("red-border");

					if (typeof APP_ID !== 'undefined' && APP_ID !== 'string' && typeof lassoOptionsData !== 'undefined' ) {
						APP_ID = lassoOptionsData.app_id;
					}

					if (Intercom && intercomParams && response.hash != '') {
						intercomParams['user_hash'] = response.hash;
						window.intercomSettings = intercomParams;
						(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/' + APP_ID;var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
					}

					// Track license key validate fail Event
					lasso_segment_tracking('Lasso License Key Unvalidated', {
						license: license
					});
				}
			})
			.fail(function(xhr, msg) {
			})
			.always(function () {
				hideProgressBar("#license-activation-modal");
			});
        });
	}	

	function reactivate_license () {
		jQuery('#reactivate').click(function() {
			var license = jQuery('input[name="license_serial"]').val();
			showProgressBar("#license-activation-modal");
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'lasso_reactivate_license',
                    license: license
                }
            })
            .done(function(response) {
				response = response.data;
				if(typeof response == 'undefined') {
					return;
				}

                jQuery('.alert').collapse();
                if(response.status) {
					lasso_helper.successScreen('Your license is active.');
					jQuery('#is_license_active').html('active').removeClass('red').addClass('green');
                } else {
                    lasso_helper.errorScreen(response.error_message);
                    jQuery('#is_license_active').html('not active').removeClass('green').addClass('red');
                }
            }).fail(function(error, xhr, message) {
                lasso_helper.errorScreen('Failed.');
			})
			.always(function(){
				hideProgressBar("#license-activation-modal");
			});
        });
	}
	
	function onboarding_set_customizations() {
		var box_style = `
			--lasso-main: `+jQuery("[name='display_color_main']").val()+'!important;'+`;
			--lasso-title: `+jQuery("[name='display_color_title']").val()+'!important;'+`;
			--lasso-background: `+jQuery("[name='display_color_background']").val()+'!important;'+`;
			--lasso-button: `+jQuery("[name='display_color_button']").val()+'!important;'+`;
			--lasso-secondary-button: `+jQuery("[name='display_color_secondary_button']").val()+'!important;'+`;
			--lasso-button-text: `+jQuery("[name='display_color_button_text']").val()+'!important;'+`;
			--lasso-pros: `+jQuery("[name='display_color_pros']").val()+'!important;'+`;
			--lasso-cons: `+jQuery("[name='display_color_cons']").val()+'!important;'+`;
		`;
		jQuery(":root").attr('style', box_style);
		
		jQuery(".lasso-button-1").html(jQuery("[name='primary_button_text']").val());
		jQuery(".lasso-button-2").html(jQuery("[name='secondary_button_text']").val());
		jQuery(".lasso-disclosure").html(jQuery("[name='disclosure_text']").val());
		
		var disclosure_txt = jQuery(".lasso-disclosure");
		if(jQuery('#show_disclosure').is(":checked")) {
			disclosure_txt.removeClass("d-none");
		} else {
			disclosure_txt.addClass("d-none");
		}

		if(jQuery('#show_disclosure').is(":checked")) {
			disclosure_txt.removeClass("d-none");
		} else {
			disclosure_txt.addClass("d-none");
		}

		if(jQuery('#link_from_display_title').is(":checked")) {
			// Replace the <a> tag with an <h3> tag
			$("h3.lasso-title").replaceWith(function() {
				return $("<a>", {
					class: "lasso-title",
					text: $(this).text()
				});
			});
		} else {
			// Replace the <a> tag with an <h3> tag
			$("a.lasso-title").replaceWith(function() {
				return $("<h3>", {
					class: "lasso-title",
					text: $(this).text()
				});
			});
		}

		
		var price_txt = jQuery(".lasso-price");
		var price_date = jQuery(".lasso-date");
		if(jQuery('#show_price').is(":checked")) {
			price_txt.removeClass("d-none");
			price_date.removeClass("d-none");
		} else {
			price_txt.addClass("d-none");
			price_date.addClass("d-none");
		}

		var display_type_el = jQuery("[name='display_type']");
		if ( display_type_el.length ) {
			var display_type = jQuery(display_type_el).val().trim();
		} else {
			var display_type = 'Single';
		}

		// Show/hide brag icon base on current checkbox status
		if ( "Grid" === display_type ) {
			var wrapper_brag_icon = jQuery(".lasso-grid-wrap");
		} else if ( "List" === display_type ) {
			var wrapper_brag_icon = jQuery(".lasso-list-ol");
		} else {
			var wrapper_brag_icon = jQuery(".lasso-display");
		}

		if ( ['Grid', 'List'].includes(display_type) && ! wrapper_brag_icon.find("> .lasso-brag").length ) {
			wrapper_brag_icon.append(get_brag_icon());
		} else if ( 'Single' == display_type && ! wrapper_brag_icon.find(".lasso-brag").length ) {
			wrapper_brag_icon.append(get_brag_icon());
		}

		var brag_icon = jQuery(".lasso-brag");
		if(jQuery("[name='enable_brag_mode']").is(":checked")) {
			brag_icon.removeClass("d-none");
		} else {
			brag_icon.addClass("d-none");
		}
	}
	
	function onboarding_load_display_html(display_theme = 'cactus') {
		var display_type_el = jQuery("[name='display_type']");
		var number_of_column_el = jQuery("[name='number_of_column']");
		var width_el = jQuery("[name='width']");
		var display_type = lassoOptionsData.display_type_single;
		if ( display_type_el.length > 0 ) {
			display_type = jQuery(display_type_el).val().trim();
		}
		refresh_display(display_type, display_theme);
		jQuery(display_type_el).unbind().change(function() {
			if ( lassoOptionsData.display_type_grid === jQuery(this).val() ) {
				jQuery("#number_of_column_wrapper").show();
			} else {
				if ( lassoOptionsData.display_type_single === jQuery(this).val() ) {
					jQuery(width_el).val(800);
				}
				jQuery("#number_of_column_wrapper").hide();
			}
			refresh_display(jQuery(this).val(), display_theme);
		});
		jQuery(number_of_column_el).unbind().change(function() {
			refresh_display(jQuery(display_type_el).val(), display_theme);
		});
		jQuery(width_el).unbind().change(function() {
			jQuery("#custom_width").val("");
			var width = parseInt(jQuery(this).val());
			if ( Number.isInteger(width) ) {
				jQuery("#custom_width_wrapper").hide();
				if ( width <= 500 ) {
					//show Max one column
					jQuery(number_of_column_el).val(1);
					jQuery("[name=\"number_of_column\"] option[value='2']").attr('disabled','disabled');
					jQuery("[name=\"number_of_column\"] option[value='3']").attr('disabled','disabled');
				}
				else {
					jQuery("[name=\"number_of_column\"] option[value='1']").removeAttr('disabled','disabled');
					jQuery("[name=\"number_of_column\"] option[value='2']").removeAttr('disabled','disabled');
					jQuery("[name=\"number_of_column\"] option[value='3']").removeAttr('disabled','disabled');
				}
			}
			else {
				jQuery("#custom_width_wrapper").show();
			}
			refresh_display(jQuery(display_type_el).val(), display_theme);
		});

		jQuery("[name='custom_width']").on('keyup', function(e) {
			if( e.which === 13 ) {
				var width = jQuery("[name='custom_width']").val();
				if ( width <= 599 ) {
					//show Max one column
					jQuery(number_of_column_el).val(1);
					jQuery("[name=\"number_of_column\"] option[value='2']").attr('disabled','disabled');
					jQuery("[name=\"number_of_column\"] option[value='3']").attr('disabled','disabled');
				}
				else if ( width > 599 && width <= 650 ) {
					jQuery(number_of_column_el).val(2);
					jQuery("[name=\"number_of_column\"] option[value='2']").removeAttr('disabled','disabled');
					jQuery("[name=\"number_of_column\"] option[value='3']").attr('disabled','disabled');
				}
				else {
					jQuery(number_of_column_el).val(3);
					jQuery("[name=\"number_of_column\"] option[value='2']").removeAttr('disabled');
					jQuery("[name=\"number_of_column\"] option[value='3']").removeAttr('disabled');
				}

				refresh_display(jQuery(display_type_el).val(), display_theme);
			}
		});
	}
	
	function onboarding_handler() {
		var display_theme = '';
		
		jQuery('.choose_display_onboard').unbind().click(function() {
			document.body.scrollTop = 0;
			document.documentElement.scrollTop = 0;
			
			jQuery(".choose_display_onboard").removeClass("selected");

			jQuery(this).addClass("selected");
			display_theme = jQuery(this).attr('class').split(' ')[1];
			display_theme = display_theme.charAt(0).toUpperCase() + display_theme.slice(1) // Capitalize
			
			// Get display HTML here and show after loading circle
			onboarding_load_display_html(display_theme);
			jQuery("#theme_name").val(display_theme);

			// Track Select Display Theme
			lasso_segment_tracking('Lasso Select Display: ' + display_theme);
			
			// Progress to the next tab
			jQuery('#onboarding_container .tab-item').addClass('d-none');
			jQuery('#onboarding_container div[data-step="customize"]').removeClass('d-none');

			// Track Step Customize Display
			lasso_segment_tracking('Lasso Step: Customize Display');
			
			// Setup color pickers
			jQuery('.color-picker').spectrum({
				type: "component",
				hideAfterPaletteSelect: "true",
				showAlpha: "false",
				allowEmpty: "false"
			});
		});
		
		// onboarding
		jQuery('#onboarding_container li[data-step="theme"]').unbind().click(function() {
			// Track Step choose Default Display Box Theme
			lasso_segment_tracking('Lasso Step: Choose Default Display Box Theme');

			document.body.scrollTop = 0;
			document.documentElement.scrollTop = 0;
			
			if( display_theme != '' ) {
				jQuery(".cactus").removeClass("selected");
				jQuery(".cutter").removeClass("selected");
				jQuery(".flow").removeClass("selected");
				jQuery(".geek").removeClass("selected");
				jQuery(".lab").removeClass("selected");
				jQuery(".llama").removeClass("selected");
				jQuery(".money").removeClass("selected");
				jQuery(".splash").removeClass("selected");
				
				jQuery(".".concat(display_theme)).addClass("selected");
			}
		});
		
		// onboarding
		jQuery('#onboarding_container .btn-save-display').unbind().click(function() {
			// Track Step Customize Display
			lasso_segment_tracking('Lasso Step: Customize Display');

			document.body.scrollTop = 0;
			document.documentElement.scrollTop = 0;
			
			if( display_theme == '' ) {
				display_theme = 'cactus';
			}
			// Get display HTML here and show after loading circle
			onboarding_load_display_html(display_theme);
			jQuery("#theme_name").val(display_theme);

			// Setup color pickers
			jQuery('.color-picker').spectrum({
				type: "component",
				hideAfterPaletteSelect: "true",
				showAlpha: "false",
				allowEmpty: "false"
			});
		});
		
		// onboarding
		jQuery('#onboarding_container .btn-done').unbind().click(function(event) {
			// Track Step Completed
			lasso_segment_tracking('Lasso Step: Completed | Save Setting');

			document.body.scrollTop = 0;
			document.documentElement.scrollTop = 0;
			
			if( display_theme == '' ) {
				display_theme = 'cactus';
			}

			// AJAX SAVE ALL SETTINGS
			handleSettingSave({}, false);
		});

		// onboarding
		jQuery('#onboarding_container .btn-save-amazon').unbind().click(function(event) {
			// Track Step Completed
			lasso_segment_tracking('Lasso Step: Amazon');

			document.body.scrollTop = 0;
			document.documentElement.scrollTop = 0;
			
			if( display_theme == '' ) {
				display_theme = 'cactus';
			}

			// AJAX SAVE ALL SETTINGS
			handleSettingSave({}, false);
		});

		// onboarding
		jQuery('#onboarding_container .btn-save-analytics').unbind().click(function(event) {
			// Track Step Completed
			lasso_segment_tracking('Lasso Step: Google Analytics');

			document.body.scrollTop = 0;
			document.documentElement.scrollTop = 0;
			
			if( display_theme == '' ) {
				display_theme = 'cactus';
			}

			// AJAX SAVE ALL SETTINGS
			handleSettingSave({}, false);
		});
		
		jQuery("[name='display_color_main']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='display_color_title']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='display_color_background']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='display_color_button']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='display_color_secondary_button']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='display_color_pros']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='display_color_cons']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='display_color_button_text']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='primary_button_text']").unbind().keyup(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='secondary_button_text']").unbind().keyup(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='disclosure_text']").unbind().keyup(function() {
			onboarding_set_customizations();
		});
		jQuery("#show_disclosure").off("change").on("change", function(event) {
			onboarding_set_customizations();
		});
		jQuery("#show_price").off("change").on("change", function(event) {
			onboarding_set_customizations();
		});

		jQuery("#link_from_display_title").off("change").on("change", function(event) {
			onboarding_set_customizations();
		});
	}
	
	function customize_displays_handler() {
		onboarding_load_display_html(jQuery("[name='theme_name']").val());

		jQuery("[name='theme_name']").unbind().change(function() {
			onboarding_load_display_html(jQuery("[name='theme_name']").val());
		});
		jQuery("[name='display_color_main']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='display_color_title']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='display_color_background']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='display_color_button']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='display_color_secondary_button']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='display_color_pros']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='display_color_cons']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='display_color_button_text']").unbind().change(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='primary_button_text']").unbind().keyup(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='secondary_button_text']").unbind().keyup(function() {
			onboarding_set_customizations();
		});
		jQuery("[name='disclosure_text']").unbind().keyup(function() {
			onboarding_set_customizations();
		});
		jQuery("#show_disclosure").off("change").on("change", function(event) {
			onboarding_set_customizations();
		});
		jQuery("#show_price").off("change").on("change", function(event) {
			onboarding_set_customizations();
		});
		jQuery("[name='enable_brag_mode']").off("change").on("change", function(event) {
			onboarding_set_customizations();
		});
	}

	function groupSave(){
		jQuery('#group_save').click(function(event) {
			event.preventDefault ? event.preventDefault() : (event.returnValue = false);

    		// Collapse Current Success Notification
            jQuery(".alert.green-bg.collapse").collapse('hide');

			var action = 'lasso_store_category';
			var ajax_url = lassoOptionsData.ajax_url;
			jQuery("#url-save").find("h3").text("Updating Group");
			showProgressBar("#url-save");
			jQuery.ajax({
				url: ajax_url,
				type: 'post',
				data: {
					action: action,
					cat_id: jQuery('#grp_id').val(),
					cat_name: jQuery('#grp_name').val(),
					cat_desc: jQuery('#grp_desc').val(),
				},
				beforeSend: function (xhr) {
					if (jQuery('#grp_name').val() == '') {
						xhr.lasso_error = 'Name is required.';
						return false;
					}
				}
			})
			.done(function (res) {
				res = res.data;
				if (res.cat_id > 0) {
					window.location.replace(res.link);
					lasso_helper.successScreen("", "save-success", "group");
				} else {
					lasso_helper.errorScreen("", "save-fail", "group");
				}
			})
			.fail(function (xhr, status, error) {
				if(xhr.lasso_error) {
					error = xhr.lasso_error;
				}
				lasso_helper.errorScreen(error);
			})
			.always(function(){
				hideProgressBar("#url-save");
			});
		});
	}

	function deleteGroup(){
		jQuery("#group_delete_pop").on("click", function(){
            let url_count = jQuery("#url_count").val();
            if(url_count > 0){
                jQuery("#group_not_delete").modal("show");
            } else {
                jQuery("#group-delete").modal("show");
            }
        });

        jQuery("#group_delete").on("click", function(){
			let post_id = jQuery("#post_id").val();
			jQuery("#url-save").find("h3").text("Deleting Group");
			showProgressBar("#url-save");
			jQuery("#group-delete").modal("hide");
            jQuery.ajax({
                url: lassoOptionsData.ajax_url,
                type: 'post',
                data: {
                    action: 'lasso_delete_category',
                    post_id: post_id
                }
            })
            .done(function (res) {
				res = res.data;
				lasso_helper.successScreen("Successfully deleted the Group.");
				setTimeout(function(){
					window.location.replace(res.redirect_link);
				}, 1200)
			})
			.fail(function (xhr, status, error) {
				if(xhr.lasso_error) {
					error = xhr.lasso_error;
				}
				lasso_helper.errorScreen(error);
			})
			.always(function(){
				hideProgressBar("#url-save");
			})
        });
	}

	function fieldSave(){
		jQuery('#field_save').click(function(event) {
			event.preventDefault ? event.preventDefault() : (event.returnValue = false);
			
			// Collapse Current Success Notification
			jQuery(".alert.green-bg.collapse").collapse('hide');
			
			// TODO: Fix Field Save - reuse existing
			var action = 'lasso_store_field';
			var ajax_url = lassoOptionsData.ajax_url;
			jQuery("#url-save").find("h3").text("Updating Field");
			showProgressBar("#url-save");
			jQuery.ajax({
				url: ajax_url,
				type: 'post',
				data: {
					action: action,
					field_id: jQuery('#field_id').val(),
					field_title: jQuery('#field-title').val(),
					field_type: jQuery('#field-type-picker').val(),
					field_description: jQuery('#field-description').val(),
				},
				beforeSend: function (xhr) {
					if (jQuery('#field-title').val() == '') {
						xhr.lasso_error = 'Title is required.';
						return false;
					}
				}
			})
			.done(function (res) {
				res = res.data;
				if (true === res.status) {
					if (true === res.new_field) {
						window.location.replace(res.link);	
					} else {
						lasso_helper.successScreen("", "save-success", "field");
					}
				} else {
					lasso_helper.errorScreen("", "save-fail", "field");
				}
			})
			.fail(function (xhr, status, error) {
				if(xhr.lasso_error) {
					error = xhr.lasso_error;
				}
				lasso_helper.errorScreen(error);
			})
			.always(function(){
				hideProgressBar("#url-save");
			});
		});
	}

	function deleteField(){
		jQuery("#field_delete_pop").on("click", function(){
			let url_count = jQuery("#url_count").val();
			if(url_count > 0){
				jQuery("#field_not_delete").modal("show");
			} else {
				jQuery("#field-delete").modal("show");
			}
		});
	
		jQuery("#field_delete").on("click", function(){
			let post_id = jQuery("#post_id").val();
			jQuery("#url-save").find("h3").text("Deleting Field");
			showProgressBar("#url-save");
			jQuery("#field-delete").modal("hide");
			jQuery.ajax({
				url: lassoOptionsData.ajax_url,
				type: 'post',
				data: {
					action: 'lasso_delete_field',
					post_id: post_id
				}
			})
			.done(function (res) {
				res = res.data;
				lasso_helper.successScreen("Successfully deleted the Field.");
				setTimeout(function(){
					window.location.replace(res.redirect_link);
				}, 1200)
			})
			.fail(function (xhr, status, error) {
				if(xhr.lasso_error) {
					error = xhr.lasso_error;
				}
				lasso_helper.errorScreen(error);
			})
			.always(function(){
				hideProgressBar("#url-save");
			})
		});
	}

	function getInitialValues() {
		var values = {};

		// Loop through all the inputs
		jQuery('form.lasso-admin-settings-form input, form.lasso-admin-settings-form select, form.lasso-admin-settings-form textarea').each(function () {
			var $field = jQuery(this);
			var name = $field.attr('name');
			var value;
			if(name != "license_serial"){
				
			}
			if ('checkbox' === $field.attr('type')) {
				value = $field.prop('checked');
			} else {
				value = $field.val();
			}
			
			values[name] = value;
			
		});
		delete values["license_serial"];

		initialFieldsValue = values;
	}

	function showProgressBar(selector){
		lasso_helper.setProgressZero('.modal');
		lasso_helper.scrollTop();
		
		jQuery(selector).modal('show');
		progressInterval = setInterval(function(){
			progress();
		}, 1000)
	}

	function hideProgressBar(selector){
		setProgressComplete();
		setTimeout(function(){
			jQuery(selector).modal('hide');
		}, 1000)
	}

    function setProgressComplete() {
		progessPercentage = 100;
		clearProgessInterval();
        jQuery(".modal").find(".progress-bar").css({width: progessPercentage + '%'});
    }

    function progress() {
        if(progessPercentage <=100) {
            progessPercentage += 25;
            jQuery(".modal").find(".progress-bar").css({width: progessPercentage + '%'});
        } else {
            clearProgessInterval();
        }
    }

    function clearProgessInterval() {
        clearInterval(progressInterval);
	}

	function unloadPage() { 
		var settings = fetchAllOptions();
		for (let key in settings) {
			if(initialFieldsValue.hasOwnProperty(key)){
				if(settings[key] != initialFieldsValue[key]){
					return "";
				}
			}
		}
	}

	function validate_tracking_id_when_enable_auto_monetize() {
		let autoMonetize = jQuery('input[name="auto_monetize_amazon"]');
		let amzTrackingIdWhitelist = jQuery('input[name="amazon_multiple_tracking_id"]');
		let isChecked = autoMonetize.is(":checked");
		let trackingId = jQuery('input[name="amazon_tracking_id"]').val() || '';
		let amazonError = jQuery('.amazon-error');

		trackingId = trackingId.trim();
		if(isChecked) {
			if(trackingId === '') {
				let errorMessage = 'Tracking ID must be set to use Auto-Amazon.';
				amazonError.text(errorMessage);
				autoMonetize.prop('checked', false);
			} else {
				if (! amzTrackingIdWhitelist.is(":checked") ) {
					amzTrackingIdWhitelist.attr('data-old-checked', false);
					amzTrackingIdWhitelist.trigger('click');
				} else {
					amzTrackingIdWhitelist.attr('data-old-checked', true);
				}
				amazonError.text('');
			}
		}

		if(trackingId !== '') {
			amazonError.text('');
			autoMonetize.prop('disabled', false);
		}
	}

	/**
	 * Validate tracking id format if having the value
	 *
	 * @returns {boolean}
	 */
	function validate_tracking_id_format() {
		let is_valid = true;
		let trackingIdInput = jQuery('input[name="amazon_tracking_id"]');
		let trackingId = trackingIdInput.val() || '';
		let trackingIdInvalidMsg = jQuery('#tracking-id-invalid-msg');

		if ( trackingId !== '' ) {
			let re = new RegExp(lassoOptionsData.amazon_tracking_id_regex, "i");
			is_valid = trackingId.match(re);
		}

		if ( is_valid ) {
			trackingIdInput.removeClass('invalid-field');
			trackingIdInvalidMsg.addClass('d-none');
		} else {
			trackingIdInput.addClass('invalid-field');
			trackingIdInvalidMsg.removeClass('d-none');
			jQuery('html, body').animate({
				scrollTop: jQuery('input[name="amazon_tracking_id"]').offset().top - 80
			}, 100);
		}

		return is_valid;
	}

	function auto_monetize_amazon_links() {
		var autoMonetize = jQuery('input[name="auto_monetize_amazon"]');

		autoMonetize
			.change(validate_tracking_id_when_enable_auto_monetize)
			.change(function() {
				let autoMonetize = jQuery(this);
				var isChecked = autoMonetize.is(":checked");
				if(isChecked) {
					jQuery('#amazon-auto-monetize').modal('show');
				}
			});

		jQuery('input[name="amazon_tracking_id"]')
			.change(validate_tracking_id_when_enable_auto_monetize)
			.change(validate_tracking_id_format);
	}

	function allow_multiple_tracking_ids() {
		var amzTrackingIdWhitelist = jQuery('input[name="amazon_multiple_tracking_id"]');
		var trackingIds = jQuery('select[name="amazon_tracking_id_whitelist"]');

		amzTrackingIdWhitelist.change(function() {
			let amzTrackingIdWhitelist = jQuery(this);
			var isChecked = amzTrackingIdWhitelist.is(":checked");

			trackingIds.prop('disabled', !isChecked);
		});
	}
	
	// Don't protect user from losing unsaved data when they leave settings
	// window.onbeforeunload = unloadPage;



	function handleSettingSaveEvent() {
		jQuery('.save-change-tab').off().on('click', function (event) {
			if ( 'settings-amazon' === lasso_helper.get_page_name() ) {
				// ? Check tracking id formating before saving setting
				if ( validate_tracking_id_format() ) {
					handleSettingSave(event);
				}
				validate_tracking_id_when_enable_auto_monetize();
			} else {
				handleSettingSave(event);
			}
		});
	}

	function refresh_display( display_type = lassoOptionsData.display_type_single, display_theme = 'cactus') {
		var demo_display_box_el = jQuery("#demo_display_box");
		var width = jQuery("[name='width']").val();
		var custom_width = jQuery("[name='custom_width']").val();
		var number_of_column = jQuery("[name='number_of_column']").val();
		var items_dummy_data = [];
		var lasso_id_demo = -1;
		items_dummy_data.push(
			{
				lasso_id: lasso_id_demo,
				theme: display_theme,
				default_theme: 'cactus',
				title: 'Essentialism: The Disciplined Pursuit of Less',
				image_url: 'admin/assets/images/displays/essentialism.jpg',
				price: '$15.99',
				prime: 'true',
				description: "The Way of the Essentialist isn't about getting more done in less time. It's not about getting less done. It's about getting only the right things done. It's about the pursuit of the right thing, in the right way, at the right time.",
				badge: 'Our Pick',
				basis_price: '$18.99',
			}
		);
		if ( display_type !== lassoOptionsData.display_type_single ) {
			items_dummy_data.push(
				{
					lasso_id: lasso_id_demo,
					theme: display_theme,
					default_theme: 'cactus',
					title: 'Atomic Habits: A Proven Way to Build Good Habits',
					image_url: 'admin/assets/images/displays/atomichabits.jpg',
					price: '$10.99',
					prime: 'true',
					description: "No matter your goals, Atomic Habits offers a proven framework for improving every day. If you’re having trouble changing your habits, the problem isn’t you - it’s your system. This book will transform the way you think about progress and give you the tools to do it.",
					badge: 'hide',
					basis_price: '$12.99',
				},
				{
					lasso_id: lasso_id_demo,
					theme: display_theme,
					default_theme: 'cactus',
					title: 'Effortless: Make It Easier to Do What Matters Most',
					image_url: 'admin/assets/images/displays/effortless.jpg',
					price: '$14.99',
					prime: 'true',
					description: "If you’ve ever felt like you’re running faster but not moving closer to your goals, this book’s for you. It offers actionable advice for making the most vital activities the easiest ones, so you can get the results you want without burnout.",
					badge: 'hide',
					basis_price: '$16.99',
				}
			);
		}

		jQuery.ajax({
			url: lassoOptionsData.ajax_url,
			type: 'post',
			data: {
				action: 'lasso_get_display_html_in_url_details',
				display_type: display_type,
				width: width,
				number_of_column: number_of_column,
				custom_width: custom_width,
				items: items_dummy_data,
			},
			beforeSend: function() {
				jQuery('.image_loading').html(get_loading_image());
				jQuery(demo_display_box_el).html("");
				jQuery('.image_loading').removeClass('d-none');
			}
			})
			.done(function (res) {
				res = res.data;
				jQuery(demo_display_box_el).html(res.html);
				jQuery(".lasso-title").removeAttr('href');
				jQuery(".lasso-image").removeAttr('href');
				jQuery(".lasso-button-1").removeAttr('href');
				jQuery(".lasso-button-2").removeAttr('href');
				jQuery(".lasso-button-2").removeClass('d-none');
				onboarding_set_customizations();
				switch ( display_type ) {
					case lassoOptionsData.display_type_single:
						if ( jQuery(demo_display_box_el).find(".single-view-wrap").length == 0 ) {
							jQuery( ".lasso-container" ).wrapAll( "<div class='single-view-wrap' />");
						}
						break;
					case lassoOptionsData.display_type_grid:
						if ( jQuery(demo_display_box_el).find(".grid-view-wrap").length == 0 ) {
							jQuery( ".lasso-grid-wrap" ).wrapAll( "<div class='grid-view-wrap' />");
						}
						break;
					case lassoOptionsData.display_type_list:
						if ( jQuery(demo_display_box_el).find(".list-view-wrap").length == 0 ) {
							jQuery( ".lasso-list-ol" ).wrapAll( "<div class='list-view-wrap' />");
						}
						break;
				}
			})
			.fail(function (xhr, status, error) {
				console.log("fail");
				if(xhr.lasso_error) {
					error = xhr.lasso_error;
				}
				lasso_helper.errorScreen(error);
			})
			.always(function() {
				jQuery('.image_loading').addClass('d-none');
			});
	}

	function handle_google_analytics() {
		jQuery("#ga-tracking-toggle").on("click",function() {
			let enableGATrackingToggle = jQuery('input[name=analytics_enable_click_tracking]');
			let gaTrackingId = jQuery('input[name=analytics_google_tracking_id]');

			if(enableGATrackingToggle.is(':disabled')){
				gaTrackingId.addClass('required-input');
				let notifiGAWarning = jQuery("#ga-warning");

				if(notifiGAWarning.length === 0) {
					lasso_helper.warningScreen('Please Enter GA Tracking ID Before Enable Click Tracking', 'ga-warning');
				} else {
					if (!notifiGAWarning.hasClass("show")) {
						notifiGAWarning.click();
					}
				}
			}
		});

		jQuery('input[name=analytics_enable_click_tracking]').on("click",function() {
			let enableIPAnonymization = jQuery('input[name=analytics_enable_ip_anonymization]');
			let pageview = jQuery('input[name=analytics_enable_send_pageview]');

			if(this.checked) {
				enableIPAnonymization.attr("disabled", false);
				pageview.attr("disabled", false);
			} else {
				enableIPAnonymization.prop("checked", false).attr("disabled", true);
				pageview.prop("checked", false).attr("disabled", true);
			}
		});

		jQuery('input[name=analytics_google_tracking_id]').on("keyup mouseout",function(e) {
			e.preventDefault();

			if(jQuery(this).hasClass('required-input')) {
				jQuery(this).removeClass('required-input');
			}

			let gaTrackingId = jQuery(this).val().trim();
			let status = !gaTrackingId ? false : true;
			let enableGATrackingToggle = jQuery('input[name=analytics_enable_click_tracking]');
			let enableIPAnonymization = jQuery('input[name=analytics_enable_ip_anonymization]');
			let pageview = jQuery('input[name=analytics_enable_send_pageview]');

			if(status === true) {
				enableGATrackingToggle.attr("disabled", !status);
			} else {
				enableGATrackingToggle.prop("checked", status).attr("disabled", !status);
				enableIPAnonymization.prop("checked", status).attr("disabled", !status);
				pageview.prop("checked", status).attr("disabled", !status);
            }

			return false;
		});

	}

	jQuery(document).ready(function () {
		const urlParams = new URL(window.location.href);
		const page = urlParams.searchParams.get('page');
		// console.log('Page:', page);

		jQuery('button.save-change-tab').prop( 'disabled', false ); // ? Enable the Save button after jQuery loaded
		getInitialValues();
		active_license_key();
		handleSettingSaveEvent();

		populateOptions();
		headerMenuInit();
		tabNavInit();
		checkboxesInit();
		conditionalFields();
		
		if(page == 'settings-display' || page == 'install') {
			// Onboarding
			onboarding_handler();
			// Needed for Display Settings``
			customize_displays_handler();
		}

		increase_width_25_percent();

		lasso_loading_full();

		reactivate_license();
		groupSave();
		deleteGroup();
		fieldSave();
		deleteField();

		// amazon setting page
		auto_monetize_amazon_links();
		allow_multiple_tracking_ids();

		handle_google_analytics();
	});
})(this, jQuery);

/* SEGMENT ANALYTIC */
function lasso_segment_tracking( tracking_title, tracking_data = {} ) {
	try {
		if ( typeof analytics !== 'undefined') {
			analytics.track( tracking_title, tracking_data );
		}
	} catch (e) {
		console.log( e );
	}
}

/* dismiss ga tracking */
function dismiss_ga_tracking() {
	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'get',
		data: {
			action: 'lasso_dismiss_ga_tracking',
		}
	})
}
