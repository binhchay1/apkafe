let window_url_detail;
let view_elementor;
let model_elementor;
let el_lasso_shortcode_textarea        = '.elementor-control-lasso_shortcode.elementor-control-type-textarea .elementor-control-tag-area';
let shortcodes_reload                  = [];
let customizing_display                = lassoOptionsData.customizing_display;
let toogle_attributes                  = customizing_display['toogle_attributes'];
let default_lasso_shortcode_attributes = ['ref', 'id', 'link_id', 'type', 'category'];
let modal_display_popup_html           = '';
let allow_pages                        = [];
let current_page                       = 'elementor';

jQuery( function( $ ) {
	function loadPopupContent() {
		$.ajax({
			url: lassoOptionsData.ajax_url,
			type: 'get',
			data: {
				action: 'lasso_get_display_html',
			}
		})
			.done(function(res) {
				res = res.data;
				modal_display_popup_html = res.html;
			});
	}

	loadPopupContent();

	if ( window.elementorFrontend && elementorFrontend.isEditMode() ) {
		// ? Load Lasso shortcode on editable mode
		elementorFrontend.hooks.addAction( 'frontend/element_ready/lasso_shortcode.default', function( $scope ) {
			let id = $scope.data('id');

			setTimeout(function(){
				let shortcode = parent.jQuery(el_lasso_shortcode_textarea).val();
				if ( shortcode ) {
					getLassoShortcodeHtml(id, shortcode, false);
				}
			}, 500);
		} );

		elementor.hooks.addAction( 'panel/open_editor/widget/lasso_shortcode', function( panel, model, view ) {
			let content_tab_el = '.elementor-panel-navigation .elementor-tab-control-content';
			view_elementor     = view;
			model_elementor    = model;

			init_editor_fields( view, model )

			// ? Correctly display fields editor change tab
			parent.jQuery(parent.document).on('click', content_tab_el, function (e){
				init_editor_fields( view, model );
			});

			view.renderOnChange();
		});
	}
});

jQuery(document).ready(function () {
	jQuery(document).on('click', '.elementor-element-edit-mode.elementor-widget-lasso_shortcode .elementor-editor-widget-settings', function(){
		view_elementor.renderOnChange();
	});

	jQuery(document).on('click', '.btn-modal-add-display', function(){
		let body = jQuery(this).closest('body');
		let widget_container = jQuery(this).closest('.elementor-widget-lasso_shortcode');
		if ( ! jQuery(widget_container).hasClass('lasso-modal') ) {
			jQuery(widget_container).append(modal_display_popup_html);
		}
		let lasso_display      = jQuery('#lasso-display-add');
		let lasso_display_type = jQuery('#lasso-display-type');
		lasso_display.modal('toggle');
		let back_drop = body.find('.modal-backdrop');
		jQuery(widget_container).append(back_drop);
		lasso_segment_tracking('Open "Choose a Display Type" Popup');

		// hide other tab, only show the types of shortcode (single, button, image, grid, list, gallery)
		if(lasso_display.hasClass('modal')) {
			lasso_display.removeClass('modal');
			lasso_display.addClass('show');
			lasso_display.find('.close-modal').remove();

			lasso_display.find('.modal-content').children().addClass('d-none');
			lasso_display_type.removeClass('d-none');
		}

		let main_nav = jQuery('div.elementor-widget-creativo_navigation_menu').closest('section.elementor-section');
		lasso_display.on('hidden.bs.modal', function (e) {
			main_nav.show();
		});
		lasso_display.on('shown.bs.modal', function (e) {
			main_nav.hide();
		});

		// hide the popup when clicking out of `lasso-display-add`
		jQuery(document).click(function(e) {
			let el = jQuery(e.target);
			let id = el.attr('id');
			if(id == 'lasso-display-add') {
				lasso_display.modal('hide');
				lasso_display.removeClass('show');
				lasso_display.find('.close-modal').remove();
				lasso_display.closest('body').find('.modal-backdrop').remove();
				lasso_display.css('display', 'none');
				jQuery('.jquery-modal.blocker.current').trigger('click');
			}
		});
	});

	jQuery(document).on('click', '.lasso-update-display', function (){
		if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 ) {
			// ? Update input lasso shortcode on change
			let id        = jQuery(this).closest('[data-element_type]').data('id');
			let shortcode = parent.jQuery(el_lasso_shortcode_textarea).val();

			if ( shortcode ) {
				getLassoShortcodeHtml(id, shortcode, false);
			}
		}
	});

	jQuery(document).on('click', '.lasso-edit-display', function (){
		if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 ) {
			// ? Update input lasso shortcode on change
			let id        = jQuery(this).closest('[data-element_type]').data('id');
			let shortcode = parent.jQuery(el_lasso_shortcode_textarea).val();

			if ( shortcode ) {
				open_url_detail_window( id, shortcode )
			}
		}
	});

	jQuery(document).on('keyup', '.shortcode-input', function (e){
		if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 ) {
			// ? Update input lasso shortcode on change
			parent.jQuery(el_lasso_shortcode_textarea).val(jQuery(this).val());
			model_elementor.attributes.settings.attributes.lasso_shortcode = jQuery(this).val();
		}
	});

	jQuery(document).on('focusout', '.shortcode-input', function (e){
		if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 ) {
			model_elementor.renderRemoteServer();
		}
	});

	jQuery(document).on('click', '.ql-editor', function(){
		if ( window.quill ) {
			window.quill.focus();
		}
	});

});

function getLassoShortcodeHtml(id, shortcode, is_new_display) {
	var loading_img = '<div class="py-5"><div class="ls-loader"></div></div>';
	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'get',
		data: {
			action: 'lasso_get_shortcode_content',
			shortcode: shortcode,
		},
		beforeSend: function( xhr ) {
			jQuery('.elementor-element-' + id).find('div.shortcode-html').html(loading_img);
		}
	})
		.done(function(res) {
			res = res.data;
			html = res.html;
			jQuery('.elementor-element-' + id).find('div.shortcode-html').html(html);

			// Tracking if Display Added
			if ( is_new_display ) {
				lasso_segment_tracking('Display Added', {
					shortcode: shortcode
				});
			}
		})
		.always(function() {
			jQuery('.elementor-element-' + id).find('div.py-5').remove();
		});
}

function add_short_code_elementor(shortcode) {
	// ? Set lasso shortcode to input
	parent.jQuery(el_lasso_shortcode_textarea).val(shortcode).trigger('input');

	clean_modal();
	trigger_load_preview();
}

function clean_modal(){
	let lasso_block = jQuery('#lasso-display-add');
	lasso_block.modal('hide');
	lasso_block.removeClass('show');
	jQuery('.jquery-modal.blocker.current').trigger('click');

	jQuery('.modal-backdrop.fade.show').remove();
	jQuery('body').removeClass('modal-open');
	jQuery('.lasso-display-modal-wrapper').html('');
}

function trigger_load_preview(){
	model_elementor.renderRemoteServer();
}

function open_url_detail_window(id, shortcode) {
	if( typeof window_url_detail === 'object' ) {
		window_url_detail.close();  // close windows are opening
	}

	let current_attributes = get_lasso_shortcode_attributes(shortcode);
	let display_type       = current_attributes['type'] ? current_attributes['type'] : 'single';
	let detail_page        = '';
	let post_id            = 0;

	if ( current_attributes.hasOwnProperty('id') ) {
		post_id = current_attributes.id;
		if ( 'table' === display_type ) {
			detail_page = lassoOptionsData.site_url + "/wp-admin/edit.php?post_type=lasso-urls&page=table-details&id=" + post_id;
		} else {
			detail_page = lassoOptionsData.site_url + "/wp-admin/edit.php?post_type=lasso-urls&page=url-details&post_id=" + post_id;
		}
	}

	if ( post_id !== 0 && ! isNaN(post_id) ) {
		shortcodes_reload.push({blockId: id, shortcode: shortcode});
		window_url_detail = window.open(detail_page,'_blank');
		window_url_detail.onload = function(){
			this.onbeforeunload = function(){
				for ( let i = 0; i < shortcodes_reload.length; i++ ) {
					getLassoShortcodeHtml(id, shortcodes_reload[i].shortcode);
				}
				shortcodes_reload = [];
			}
		}
	}
}

function get_lasso_shortcode_attributes( shortcode ) {
	var result = {};

	try {
		var raw_attributes = shortcode.replace(/\[lasso/g, '').replace(/\]/g, '').trim();
		var temporary_element = '<div ' + raw_attributes + '></div>';
		temporary_element = jQuery(temporary_element);

		jQuery(temporary_element).each(function() {
			jQuery.each(this.attributes, function() {
				if(this.specified) {
					result[this.name] = this.value;
				}
			});
		});
	} catch (e) {}

	return result;
}

function customize_shortcode( cus_attr_name, cus_attr_value ) {
	let shortcode = parent.jQuery(el_lasso_shortcode_textarea).val();

	if (shortcode && shortcode.match(/\[lasso.*\]/)) {
		var current_attributes = get_lasso_shortcode_attributes( shortcode );

		if (Object.keys( current_attributes ).length !== 0) {
			shortcode = get_new_customize_shortcode( current_attributes, cus_attr_name, cus_attr_value );
		}
	}

	return shortcode;
}

function get_new_customize_shortcode( current_attributes, cus_attr_name, cus_attr_value ) {
	var attribute_content = '';
	var old_customize_attributes = [];

	current_attributes[cus_attr_name] = cus_attr_value; // Add/Update new customize value

	// Build default attributes and newest customize before
	for (const property in current_attributes) {
		if ((default_lasso_shortcode_attributes.indexOf(property) !== -1) || (property === cus_attr_name) ) {
			var value = current_attributes[property];
			if ( toogle_attributes.includes(property) ) { // Toogle attributes
				let attr_value = current_attributes[property] ? 'show' : 'hide';

				// Add "hide" value for toogle attribute, else do nothing
				if ( 'hide' === attr_value ) {
					attribute_content += ' ' + property + '="' + attr_value + '"';
				}
			} else if (value) { // Text box attributes
				attribute_content += ' ' + property + '="' + current_attributes[property] + '"';
			}
		} else {
			old_customize_attributes.push(property);
		}
	}

	// Build old customize attributes later
	old_customize_attributes.forEach(old_cuz_attr => {
		var value = current_attributes[old_cuz_attr];
		if (value) {
			attribute_content += ' ' + old_cuz_attr + '="' + current_attributes[old_cuz_attr] + '"';
		}
	});

	return '[lasso' + attribute_content + ']';
}

function init_editor_fields ( view, model ) {
	let shortcode          = parent.jQuery(el_lasso_shortcode_textarea).val();
	let current_attributes = get_lasso_shortcode_attributes(shortcode);
	let display_type       = current_attributes['type'] ? current_attributes['type'] : 'single';

	jQuery.each( parent.jQuery('#elementor-controls .elementor-control'), function( key, elementor_control ) {
		let element_control_class = parent.jQuery(elementor_control).attr('class');

		if ( element_control_class.search('content_section') >= 0 /*|| element_control_class.search('lasso_shortcode') >= 0*/ ) {
			return;
		}

		if ( element_control_class.search(display_type) < 0 ){
			parent.jQuery(elementor_control).hide();
		} else {
			let input     = parent.jQuery(elementor_control).find('input');
			let attr_name = input.data('setting').replace('_' + display_type, '');

			if ( input.hasClass('elementor-switch-input') ) {
				jQuery(input).change(function(){
					if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 ) {
						let shortcode_customize = customize_shortcode(attr_name, jQuery(this).prop( 'checked' ))

						parent.jQuery(el_lasso_shortcode_textarea).val(shortcode_customize);
						model.attributes.settings.attributes.lasso_shortcode = shortcode_customize;
						view.$el.find('.shortcode-input').val(shortcode_customize);
					}

				});
			} else {
				jQuery(input).keyup(function(){
					if ( parent.jQuery(el_lasso_shortcode_textarea).length > 0 ) {
						let shortcode_customize = customize_shortcode(attr_name, jQuery(this).val())

						parent.jQuery(el_lasso_shortcode_textarea).val(shortcode_customize);
						model.attributes.settings.attributes.lasso_shortcode = shortcode_customize;
						view.$el.find('.shortcode-input').val(shortcode_customize);
					}
				});
			}
		}
	});
}
