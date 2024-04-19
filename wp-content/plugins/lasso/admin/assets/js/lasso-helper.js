var lasso_modal = {};
jQuery(document).ready(function () {
		if (jQuery("#link-monetize").length > 0) {
			lasso_modal.link_monetize = jQuery("#link-monetize");
			lasso_modal.group_monetize = jQuery("#group-monetize");
			lasso_modal.field_create = jQuery("#field-create");
			lasso_modal.field_description = jQuery("#field-description-editor");
		}
	}
);

var lasso_helper = {
	pagination_includes_post_id: ['url-links', 'url-opportunities', 'content-links', 'field-urls', 'url-details'],
	allow_monetize_pages: ['content-links', 'keyword-opportunities', 'domain-links'],
	link_location_limit: 6,
	reset_value: function ( element ) {
		jQuery(element).val("");
	},
	get_value_by_name: function ( element_name ) {
		return jQuery("[name='"+element_name+"']").val();
	},
	get_value_by_data_attr: function ( element_name, attr_name ) {
		return jQuery(element_name).data(attr_name);
	},
	get_value_by_id_selector: function ( id ) {
		return jQuery('#' + id).val();
	},
	get_checked_value: function ( element_name ) {
		return jQuery('[name="' + element_name + '"]').prop('checked');
	},
	remove_element: function ( el ) {
		if ( jQuery(el) !== undefined && jQuery(el).length > 0 ) {
			jQuery(el).remove();
		}
	},
	set_progress_bar( maximum, speed, progress_bar_element = '#url-save' ) {
		var progress = 0;
		window.process_bar_interval = setInterval( function () {
			if ( progress >= maximum ) {
				clearInterval(window.process_bar_interval);
			}
			progress++;
			lasso_helper.setProgress( progress, progress_bar_element );
		}, speed );
	},
	set_progress_bar_complete( progress_bar_element = '#url-save' ) {
		lasso_helper.setProgress( 100, progress_bar_element );
		clearInterval(window.process_bar_interval);
	},
	scrollTop: function() {
		document.body.scrollTop = 0;
		document.documentElement.scrollTop = 0;
	},
	setProgressZero: function ( progress_bar_element = '#url-save' ) {
		jQuery(progress_bar_element).find(".progress-bar").css({width: '0%'});
	},
	setProgress: function (progessPercentage, progress_bar_element = '#url-save' ) {
		jQuery(progress_bar_element).find(".progress-bar").css({width: progessPercentage + '%'});
	},
	add_loading_button: function ( el, label = '', is_loading = true ) {
		var html = label;
		if ( is_loading ) {
			html = '<span style="opacity: 1">' + label + '</span>&nbsp;<i class="far fa-circle-notch fa-spin"></i>';
		}
		jQuery(el).html(html);
	},

	/**
	 *
	 * @param params is array object
	 */
	push_data_to_url: function ( params ) {
		var query_string = '';
		for ( var i = 0; i < params.length; i ++ ) {
			Object.entries(params[i]).forEach(([key, value]) => {
				query_string += `&${key}=${value}`;
			});
		}
		var current_url = window.location.href + query_string;
		window.history.pushState({ path: current_url }, '', current_url);
	},

	debug_log: function ( value, caption = null ) {
		console.log("========BEGIN======");
		var output = value;
		if ( caption != null ) {
			console.log(caption);
		}
		console.log(output);
		console.log("========END======");
	},

	lasso_generate_modal: function () {
		var template = null;
		var modal_id = null;
		var is_rendered = false;
		var modal_object = null;
		var btn_ok = null;
		var btn_cancel = null;
		var on_show_callback = null;
		var on_hide_callback = null;
		var on_submit_callback = null;
		var on_cancel_callback = null;
		var heading = null;
		var description = null;
		var pagination_container = null;
		var _generate_id = function () {
			return "lasso-modal-" + Math.random().toString(16).slice(2);
		};
		var _replace_text = function( text, key, value ) {
			key = '{{' + key + '}}';
			let re = new RegExp( key, "g" );
			return text.replace( re, value );
		};
		var _render_template = function ( optional_data = {} ) {
			let modal_size = '';
			if ( optional_data.hasOwnProperty('use_modal_large') && optional_data.use_modal_large === true ) {
				modal_size = 'modal-lg';
			}
			let backdrop = '';
			if ( optional_data.hasOwnProperty('backdrop') && optional_data.backdrop === true ) {
				backdrop = 'data-backdrop="static"';
			}
			let template_temp = [
				'<div class="modal fade modal_confirm" id="{{modal_id}}" tabindex="-1" role="dialog" ' + backdrop + '>',
					'<div class="modal-dialog ' + modal_size + '" role="document">',
						'<div class="modal-content text-center shadow p-5 rounded">',
							'<h2>Remove \"This Product\"</h2>',
							'<p>If removed, you won\'t be able to get its back.</p>',
							'<div class="pagination-container"></div>',
							'<div>' +
								'<button type="button" class="btn cancel-btn mx-1" data-dismiss="modal">Cancel</button>',
								'<button type="button" class="btn red-bg mx-1 btn-ok" data-lasso-id="">OK</button>',
							'</div>',
						'</div>',
					'</div>' +
				'</div>'
			];
			template = _replace_text( template_temp.join( "\n"), "modal_id", modal_id );
		};
		var _inject_to_template = function ( optional_data = {} ) {
			if ( !is_rendered ) {
				jQuery("#wpbody-content").append(template);
				modal_object = jQuery("#"+modal_id);
				heading = modal_object.find('h2');
				btn_ok = modal_object.find('.btn-ok');
				btn_cancel = modal_object.find('.cancel-btn');
				description = modal_object.find('p');
				pagination_container = modal_object.find('.pagination-container');
				is_rendered = true;
				if ( optional_data.hasOwnProperty('hide_btn_cancel') && optional_data.hide_btn_cancel === true ) {
					jQuery(btn_cancel).addClass('d-none');
				}
				if ( optional_data.hasOwnProperty('hide_btn_ok') && optional_data.hide_btn_ok === true ) {
					jQuery(btn_ok).addClass('d-none');
				}
			}
		}
		this.set_heading = function ( heading_text ) {
			heading.text( heading_text );
			return this;
		}
		this.set_description = function ( msg, is_text = true ) {
			if ( is_text ) {
				description.text( msg );
			}
			else {
				description.html( msg );
			}

			return this;
		}
		this.init = function ( optional_data = {}) {
			modal_id = _generate_id();
			_render_template( optional_data );
			_inject_to_template( optional_data );
			return this;
		}
		this.set_lasso_id = function ( lasso_id ) {
			btn_ok.data('lasso-id', lasso_id);
		}
		this.show = function () {
			modal_object.on('shown.bs.modal', on_show_callback);
			modal_object.on('hide.bs.modal', on_hide_callback);
			btn_ok.unbind().on('click', on_submit_callback);
			btn_cancel.unbind().on('click', on_cancel_callback);
			modal_object.modal("show");

			this.set_btn_ok_el( btn_ok );
			return this;
		}
		this.hide = function () {
			modal_object.modal("hide");
		}
		this.on_show = function ( callback ) {
			if ( typeof callback === "function" ) {
				on_show_callback = callback;
			}
			return this;
		}
		this.on_submit = function ( callback ) {
			if ( typeof callback === "function" ) {
				on_submit_callback = callback;
			}
			return this;
		}
		this.on_cancel = function ( callback ) {
			if ( typeof callback === "function" ) {
				on_cancel_callback = callback;
			}
			return this;
		}
		this.on_hide = function ( callback ) {
			if ( typeof callback === "function" ) {
				on_hide_callback = callback;
			}
			return this;
		}
		this.set_btn_ok_el = function ( el ) {
			this.btn_ok_el = el;
		}
		this.set_btn_ok = function ( optional_data ) {
			if ( optional_data.hasOwnProperty('label') && optional_data.label !== '' ) {
				btn_ok.removeClass('red-bg');
				btn_ok.text(optional_data.label);
			}

			if ( optional_data.hasOwnProperty('class') && optional_data.class !== '' ) {
				if ( optional_data.class !== 'red-bg' ) {
					btn_ok.removeClass('red-bg');
				}
				btn_ok.addClass(optional_data.class);
			}

			return this;
		}
		this.get_modal_id = function () {
			return modal_id;
		}
		this.set_pagination = function ( pagination_class = '' ) {
			pagination_container.html(`<div class="pagination row align-items-center no-gutters ${pagination_class}"></div>`);
			return this;
		}
	},

	/**
	 * Get page number from current url and localStorage
	 * Example: http://affiliate.local/wp-admin/edit.php?post_type=lasso-urls&page=dashboard&search=iphone#page-2
	 * Result: 2
	 *
	 * @param key
	 * @returns {int}
	 */
	get_page_from_current_url() {
		let current_page = 1;
		let result = window.location.href.match(/.*#page-(\d*)/i);

		if (result && result.length) {
			current_page = result[1];
		} else {
			let lasso_current_page = this.get_pagination_cache( this.get_page_name() );

			// set current page as the page from local storage
			if ( lasso_current_page > 0 ) {
				current_page = lasso_current_page;
			}
		}

		return current_page;
	},

	/**
	 * Remove #page-xx out of current url
	 *
	 * @param set_timeout Use setTimeout to make sure apply successful after click pagination button.
	 */
	remove_page_number_out_of_url(set_timeout = 100) {
		setTimeout( function () {
			let url_without_page_hash = window.location.href.replace(/#page-\d+/g, '');
			window.history.replaceState( null, null, new URL( url_without_page_hash ) );
		}, set_timeout );
	},

	/**
	 * Remove hash from current url
	 */
	remove_hash_from_current_url() {
		history.pushState("", document.title, window.location.pathname + window.location.search);
	},
	/**
	 * Get url parameter by key
	 *
	 * @param key
	 * @returns {string}
	 */
	get_url_parameter( key ) {
		let url = new URL( window.location.href );
		return url.searchParams.get( key );
	},

	/**
	 * Update url parameter by key and value, delete parameter if value is empty
	 *
	 * @param key
	 * @param value
	 */
	update_url_parameter( key, value ) {
		let url = new URL( window.location.href );

		if ( value ) {
			url.searchParams.set( key, value );
		} else {
			url.searchParams.delete( key );
		}

		window.history.replaceState( null, null, url );
	},
	/**
	 * Get page name
	 *
	 * @returns {string}
	 */
	get_page_name() {
		let url = new URL(location.href);
		let searchParams = new URLSearchParams(url.search);
		return searchParams.get('page');
	},
	lasso_generate_modal_dynamic: function () {
		var template = null;
		var modal_id = null;
		var is_rendered = false;
		var modal_object = null;
		var on_show_callback = null;
		var on_hide_callback = null;
		var _generate_id = function () {
			return "lasso-modal"+Date.now();
		};
		var _replace_text = function( text, key, value ) {
			key = '{{' + key + '}}';
			let re = new RegExp( key, "g" );
			return text.replace( re, value );
		};
		var _render_template = function ( type ) {
			let template_temp = null;
			if ( type === 'simple' ) {
				template_temp = [
					'<div class="modal fade" id="{{modal_id}}" data-backdrop="static" tabindex="-1" role="dialog" style="display: block; padding-right: 17px;">',
						'<div class="modal-dialog" role="document">',
							'<div class="modal-content p-5 shadow text-center">',
								'<h2>{{main_content}}</h2>',
								'<div class="progress mt-3 mb-3">',
									'<div class="progress-bar progress-bar-striped progress-bar-animated green-bg" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>',
								'</div>',
							'</div>',
						'</div>',
					'</div>',
				];
			}
			template = _replace_text( template_temp.join( "\n"), "modal_id", modal_id );
		};
		var _inject_to_wp_template = function () {
			if ( !is_rendered ) {
				jQuery("#wpbody-content").append(template);
				modal_object = jQuery("#"+modal_id);
				is_rendered = true;
			}
		}
		this.init = function ( type ) {
			modal_id = _generate_id();
			_render_template( type );
			return this;
		}
		this.init_simple_modal = function () {
			this.init('simple');
			_inject_to_wp_template();
		}
		this.show = function () {
			modal_object.on('shown.bs.modal', on_show_callback);
			modal_object.on('hide.bs.modal', on_hide_callback);
			modal_object.modal("show");
			return this;
		}
		this.hide = function () {
			modal_object.modal("hide");
		}
		this.set_main_content = function ( content ) {
			modal_object.find('h2').text(content);
		}
		this.get_modal_id = function () {
			return modal_id;
		}
	},
	set_local_storage: function(key, value) {
		try {
			if ( key === undefined || ! key || value === undefined ) {
				return;
			}
			localStorage.setItem(key, value);
		} catch (error) {
			console.error('Local storage error.');
		}
	},
	/**
	 * Remove empty line from string.
	 *
	 * @param value
	 * @returns {string}
	 */
	remove_empty_line_from_string: function(value) {
		if ( typeof value !== 'string' ) {
			return '';
		}

		var lines = value.split('\n');
		var valid_lines = [];

		lines.forEach(function(line) {
			line = line.trim();
			if ( line ) {
				valid_lines.push(line);
			}
		});

		return valid_lines.join('\n');
	},

	generate_paging: function ( paging_el, set_page, total_items, click_page_number_callback, items_on_page = 10 ) {
		let pagination_helper = jQuery(paging_el);
		let data_helper = {
			items: total_items,
			displayedPages: 3,
			itemsOnPage: items_on_page,
			cssStyle: 'light-theme',
			prevText: '<i class="far fa-angle-double-left"></i> Previous',
			nextText: 'Next <i class="far fa-angle-double-right"></i>',
			onPageClick: function(page_number) {
				if ( typeof click_page_number_callback === 'function' ) {
					return click_page_number_callback(page_number)
				}
			}
		};

		if(set_page > 0) {
			data_helper.currentPage = set_page;
		}
		pagination_helper.pagination(data_helper);
	},
	remove_local_store( target_key, is_like = true ) {
		let store_keys = Object.keys(localStorage);
		if ( is_like === false ) {
			localStorage.removeItem(target_key);
		} else {
			for( let i = 0 ; i < store_keys.length; i++ ) {
				let local_key = store_keys[i];
				if (  local_key.indexOf(target_key) >= 0 ) {
					localStorage.removeItem(local_key);
				}
			}
		}
	},
	build_pagination_cache_key( key, suffix = '' ) {
		if ( ! suffix && this.pagination_includes_post_id.includes(key) ) {
			suffix = this.get_url_parameter('post_id');
		}

		let cache_key = key + ( suffix ? '_' + suffix : '' );
		cache_key = cache_key.replace(/-/g, '_');

		return cache_key;
	},
	set_pagination_cache( key, page_number, suffix = '' ) {
		if ( ! key || ! page_number) {
			return;
		}

		let cache_key = this.build_pagination_cache_key( key, suffix );
		let pagination_cache = localStorage.getItem('lasso_pagination');
		pagination_cache = pagination_cache ? JSON.parse( pagination_cache ) : {};
		pagination_cache[cache_key] = page_number;
		pagination_cache = JSON.stringify(pagination_cache);

		this.set_local_storage( 'lasso_pagination', pagination_cache );
	},
	get_pagination_cache( key, suffix = '' ) {
		if ( ! key ) {
			return 1;
		}

		let cache_key = this.build_pagination_cache_key( key, suffix );
		let pagination_cache = localStorage.getItem('lasso_pagination');
		pagination_cache = pagination_cache ? JSON.parse( pagination_cache ) : {};

		if ( cache_key in pagination_cache ) {
			return parseInt(pagination_cache[cache_key]);
		}

		return 1;
	},
	do_notification(data, callback) {
		let alert_id = '_' + Math.random().toString(36).substr(2, 9);
		let alert_bg = data.color + '-bg';
		let ajax_data = {
			'action': 'lasso_get_notification_template',
			'template': data.template_name,
			'message': data.message,
			'name': data.object_name,
			'alert_id': alert_id,
			'alert_bg': alert_bg,
		};

		jQuery.ajax({
			url: lassoOptionsData.ajax_url,
			type:'get',
			data : ajax_data,
		})
		.done(function(response) {
			if (typeof response.data != 'undefined') {
				response = response.data;
				jQuery("#lasso_notifications").append(response.html);
				jQuery('#' + alert_id).collapse('show');

				if ( typeof callback === "function" ) {
					callback(alert_id);
				}
			}
		})
	},
	successScreen(message = "", template_name = "default-template", object_name = "changes") {
		var data = {
			message: message,
			template_name: template_name,
			object_name: object_name,
			color: 'green'
		};
	
		this.do_notification(data);
	},
	/**
	 *
	 * @param xhr
	 */
	display_ajax_error( xhr ) {
		let msg = "An unexpected error has occurred please try again later.";
		if ( xhr.hasOwnProperty('responseJSON') && typeof xhr.responseJSON.data === 'string' ) {
			msg = xhr.responseJSON.data;
		}
		this.errorScreen(msg);
	},
	errorScreen(message = "", template_name = "default-template", object_name = "changes") {
		var data = {
			message: message,
			template_name: template_name,
			object_name: object_name,
			color: 'red'
		};
	
		this.do_notification(data);
	},
	warningScreen(message = "", template_name = "default-template", object_name = "changes") {
		var data = {
			message: message,
			template_name: template_name,
			object_name: object_name,
			color: 'orange'
		};
	
		this.do_notification(data);
	},
	loadingScreen(message, image_loading = false) {
		if (!jQuery('body').find('.lasso-loading-bg').length > 0) {
			jQuery('body').append('<div class="lasso-loading-bg"><div class="lasso-loading-message"></div></div>');
		} else {
			jQuery('body').find('.lasso-loading-bg')
				.removeClass('lasso-loading-bg-image')
				.html('<div class="lasso-loading-message"></div>');
		}
	
		if(image_loading) {
			jQuery('body').find('.lasso-loading-bg')
				.addClass('lasso-loading-bg-image')
				.html(`
					<div class="lasso-loading-message-image">
						<img src="${ lassoOptionsData.plugin_url }/admin/assets/images/lasso-icon.svg" alt="Lasso" class="img-fluid" width="100">
					</div>
				`);
		}
	
		this.popup_align_center();
	},
	clearLoadingScreen(msg) {
		var message = '';
	
		if (msg) {
			message = ': ' + msg || message;
		}
	
		jQuery('body').find('.lasso-loading-bg').addClass('lasso-loading-bg-image').html(`
			<div class="lasso-loading-message-image">
				<img src="${ lassoOptionsData.plugin_url }/admin/assets/images/lasso-icon.svg" alt="Lasso" class="img-fluid" width="100">
			</div>
		`);
	
		jQuery('.lasso-loading-bg').delay(1000).fadeOut(1000);
		jQuery('.lasso-loading-bg').remove();
	},
	popup_align_center() {
		// align center
		var window_width = jQuery(window).width();
		var window_height = jQuery(window).height();
		var block_width = jQuery('.lasso-loading-message').outerWidth();
		var block_height = jQuery('.lasso-loading-message').outerHeight();
		var margin_left = (window_width - block_width) / 2;
		var margin_top = (window_height - block_height) / 2;
	
		jQuery('.lasso-loading-message').css({
			'margin-left': margin_left,
			'margin-top': margin_top,
			'left': 0,
			'top': 0
		});
	},
	rebuild_database_background() {
		let interval_time = 5000;
		let helper = this;
	
		setInterval(function () {
			var data = {
				'action': 'lasso_get_stats',
				'support': helper.get_url_parameter('support'),
			};
			jQuery.post(ajaxurl, data, function (response) {
				if(!response.hasOwnProperty('success') || (response.hasOwnProperty('success') && !response.success)) {
					return;
				}
				var res = response.data;
	
				var stats = jQuery('.lasso-stats');
				stats.html(res.stats);
				var duration = stats.find('.eta').text();
				duration = parseFloat(duration);
				var time = convert_duration_to_time(duration * 1000);
				stats.find('.eta').text(time);
	
				// remove attribute
				var remove_attr = jQuery('.lasso-remove-attributes');
				remove_attr.html(res.remove_attribute);
	
				var duration = remove_attr.find('.eta').text();
				duration = parseFloat(duration);
				
				var time = convert_duration_to_time(duration * 1000);
				remove_attr.find('.eta').text(time);
			});
		}, interval_time);
	},

	is_empty(input) {
		return typeof (input) === 'undefined';
	}
};

// Write custom function below extend from Jquery
jQuery.fn.extend({
	lassoHide: function() {
		if (!this.hasClass('d-none')) {
			return this.addClass('d-none');
		}

	},
	lassoShow: function() {
		if (this.hasClass('d-none')) {
			return this.removeClass('d-none');
		}
	},
	fix_backdrop_elementor: function () {
		if ( window.elementorFrontend !== 'undefined' && window.elementorFrontend
			&& elementorFrontend !== 'undefined' && elementorFrontend.isEditMode() ) {
			let body = jQuery(this).closest('body');
			let widget_container = jQuery(this).closest('.elementor-widget-lasso_shortcode');
			let back_drop = body.find('.modal-backdrop');
			jQuery(widget_container).append(back_drop);
		}
	}
});
