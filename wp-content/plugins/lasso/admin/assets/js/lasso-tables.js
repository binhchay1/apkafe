var lasso_table_id_selector = "#lasso-table";
var flag_add_field = false;
var border_radius_weight = 3;
var preview_mode = 1;
var modal_confirm_remove_product = null;
var table_prpduct_trigged_load = false;
var table_group_trigged_load = false;
var table_page = lasso_helper.get_url_parameter('page');
var modal_field_delete = null;
var modal_confirm_clone_table = null;
var modal_confirm_delete_table = null;
let enter_key_code = 13;
var modal_show_table_locations = null;
var table_page_state = null;
var table_locations_local_store_key = 'table_locations';

// We don't use the hash and search parameter when go to table details page
if ( 'table-details' === table_page) {
	setTimeout(function () {
		lasso_helper.remove_hash_from_current_url();
		lasso_helper.update_url_parameter('search', null);
	}, 1000);
}

jQuery(document).ready(function () {
	modal_field_delete = jQuery("#field-delete");
	var modal_field_delete_content = modal_field_delete.find('.modal-content');
	modal_field_delete_content.removeClass("p-5");
	modal_field_delete_content.addClass("pb-5");
	modal_field_delete_content.addClass("pt-5");
	modal_field_delete_content.addClass("pl-5");
	modal_field_delete_content.addClass("pr-5");
	modal_field_delete_content.find('p').text('No data is deleted and the Field can always be added back.');

	jQuery('#table-action-btns').removeClass('d-none'); // After jquery loaded, we show Add product and add row/column
	modal_confirm_remove_product = new lasso_helper.lasso_generate_modal();
	modal_confirm_remove_product
		.init()
		.on_submit(function () {
			modal_confirm_remove_product.hide();
			remove_product( modal_confirm_remove_product.btn_ok_el );
		});

	modal_confirm_clone_table = new lasso_helper.lasso_generate_modal();
	modal_confirm_clone_table
		.init()
		.on_submit(function () {
			modal_confirm_clone_table.hide();
			var table_id = jQuery('#'+modal_confirm_clone_table.get_modal_id()).find('.btn-ok').attr('data-table-id');
			clone_table(table_id);
		});

	modal_confirm_delete_table = new lasso_helper.lasso_generate_modal();
	modal_confirm_delete_table
		.init()
		.on_submit(function () {
			delete_table(jQuery('#table_id').val());
		})
		.on_show( function () {
			jQuery('#' + modal_confirm_delete_table.get_modal_id()).find('.btn-ok').html("OK");
		});

	modal_show_table_locations = new lasso_helper.lasso_generate_modal();
	modal_show_table_locations
		.init( {
			hide_btn_cancel: true,
			hide_btn_ok: true,
			use_modal_large: true
		})
		.set_heading( "Table Locations" )
		.set_pagination( 'table-locations-pagination' );

	if (lasso_modal.link_monetize !== undefined) {
		// show link popup
		jQuery(lasso_modal.link_monetize).on('shown.bs.modal', function () {
			if ( ! table_prpduct_trigged_load ) {
				table_prpduct_trigged_load = true;
				search_attributes("", 1, function () {
					add_item_to_table();
				});
			}
		});

		// show group popup
		jQuery(lasso_modal.group_monetize).on('shown.bs.modal', function () {
			if ( ! table_group_trigged_load ) {
				table_group_trigged_load = true;
				search_attributes_group("", 1, function () {
					add_group_to_table();
				});
			}
		});

		jQuery(lasso_modal.link_monetize).on('hide.bs.modal', function () {
			lasso_helper.remove_hash_from_current_url();
			lasso_helper.update_url_parameter('search', null);
		});

		jQuery(lasso_modal.field_create).on('show.bs.modal', function () {
			flag_add_field = false;
			jQuery("#create_from_library_tab").trigger("click");
		});

		jQuery(lasso_modal.field_create).on('hide.bs.modal', function () {
			// Should not touch to product popup search input
			// lasso_helper.reset_value(jQuery('.js-monetize-search'));
			var is_btn_large_add_field = jQuery(lasso_modal.field_create).attr("btn-large-add-field");
			if ( is_btn_large_add_field === 'true' && ! flag_add_field ) {
				add_empty_field();
			}
			jQuery(lasso_modal.field_create).attr("btn-large-add-field", false);
			lasso_helper.remove_hash_from_current_url();
			lasso_helper.update_url_parameter('search', null);
		});

		jQuery("#field-delete").on('hide.bs.modal', function () {
			jQuery("#lasso_id").val("");
			jQuery("#field_id").val("");
			jQuery("#field_group_id").val("");
		});


		jQuery("[name='table_style']").change(function () {
			if ( jQuery(this).val() === "Row" ) {
				jQuery("#btn-add-col-field").text("Add Column");
				jQuery('#is-show-headers-toggle-row').removeClass('d-none');
			} else {
				jQuery("#btn-add-col-field").text("Add Row");
				jQuery('#is-show-headers-toggle-row').addClass('d-none');
			}

			if ( preview_mode === 0 ) {
				add_or_update_table( function () {
					preview_table();
				});
			} else {
				add_or_update_table( function () {
					load_table( null );
				});
			}
		});

		jQuery(lasso_modal.field_description).on('hidden.bs.modal', function () {
			let btn_description_save_selector = '#btn-description-save';
			jQuery(jQuery.find('.ql-toolbar.ql-snow')).remove();
			jQuery(jQuery.find('#field-description-editor-modal')).empty().removeClass('ql-container ql-snow');
			jQuery(btn_description_save_selector).find('span').css('opacity', 1);
			jQuery(btn_description_save_selector).find('svg').addClass('d-none');
			jQuery('.saved-item').removeClass('d-none');
			jQuery('.saving-item').addClass('d-none');
		});
	}

	// Get list tables if current page is Table Dashboard
	if ( jQuery("#page-name").length > 0 && jQuery("#page-name").val() === "tables" ) {
		let page_number = lasso_helper.get_page_from_current_url();
		let search_key = lasso_helper.get_url_parameter( 'search' );
		if ( search_key ) {
			jQuery('#tables-search-input').val(search_key);
		}

		get_search_list_table(page_number);
	}

	if ( lasso_helper.get_page_name() === 'table-details' ) {
		var table_id = lasso_helper.get_value_by_name("table_id");
		if ( table_id === undefined || table_id === '' ) {
			load_table(null, false, 'load_table');
		} else {
			load_table(null);
		}
		jQuery("#table_name").focus();
	}

	// Column headers toggle change event
	jQuery(document).on('change', '#is-show-headers-toggle, #is-show-field-name', function () {
		add_or_update_table(function () {
			if ( preview_mode === 0 ) {
				preview_table()
			} else {
				load_table( null );
			}
		});
	});

	jQuery(document)
		.on('click', '#create-new-table', open_create_table_modal)
		.on('click', '.btn-add-field', click_btn_add_field)
		.on("click", ".js-add-field-to-product", run_add_field_to_product)
		.on("click", ".js-remove-field", click_btn_remove_field)
		.on("input", "input.form-control.field_value.star_value.float-left", format_rating_star_value)
		.on("click", "#btn-add-col-field", click_btn_add_row_column)
		.on("change", ".field_value", change_field_value)
		.on("click", 'input[type="number"]', change_input_number)
		.on("focusout", "#table_name", focusout_table_name)
		.on("keyup", "#table_name", change_table_name)
		.on("change", "#theme_name", change_table_theme)
		.on("click", ".btn-delete-product", click_btn_delete_product)
		.on("click", ".btn-delete-field", run_delete_field)
		.on("click", ".btn-preview-table", click_btn_preview_edit_table)
		.on("click", ".js-create-field", run_create_field)
		.on("click", ".js-montize-btn", click_monetize_btn)
		.on("click", ".btn-clone-table", click_btn_clone_table)
		.on("keyup", "#tables-search-input", search_tables_list)
		.on("click", "#btn-delete-table", click_to_show_delete_table_modal)
		.on('click', '.url-details-field-box .img-container', select_product_image)
		.on('click', '.js-edit-button-field', click_btn_edit_button_info)
		.on("focusout", ".badge_text", update_badge_text)
		.on('click', '.btn-show-table-locations', click_to_show_modal_table_locations);

	jQuery('#table-edit-button-field-form').on('submit', submit_edit_field_button);
});

jQuery(document).on('click', '.btn-edit-description', function () {
	if (lasso_modal.field_description !== undefined) {
		lasso_modal.field_description.modal('show');
		handle_description_editor(this);
	}
})

jQuery(document).on('click', '#btn-description-save', function () {
	let lasso_id = jQuery(this).data('lasso-id');
	let field_id = jQuery(this).data('field-id');
	let field_value = jQuery('#field-description-editor-modal').find('.ql-editor').html();
	let field_group_id = jQuery(this).data('field-group-id');
	let table_id = jQuery(this).data('table-id');

	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: {
			action: 'lasso_get_table_comparison_view',
			sub_action: 'update_field',
			lasso_id: lasso_id,
			field_id: field_id,
			field_value: field_value,
			field_group_id: field_group_id,
			table_id: table_id
		},
		context: this,
		beforeSend: function() {
			jQuery(this).find('span').css('opacity', 0);
			jQuery(this).find('svg').removeClass('d-none');
			show_saving_sign();
		}
	})
		.done(function (res) {
			lasso_modal.field_description.modal('hide');
			let current_description_content_selector = 'li[data-id="id-' + lasso_id + '-' + field_id + '"]';
			jQuery(current_description_content_selector).find('.description-content').html(field_value);


		})
		.fail(function (xhr, status, error) {
			lasso_helper.display_ajax_error(xhr);
			lasso_modal.field_description.modal('hide');
		})
		.always(function() {
		});
})

/**
 * Open "Create table" modal
 */
function open_create_table_modal() {
	jQuery('#table-create').modal("show");
}

/**
 * Click to button add field
 */
function click_btn_add_field() {
	let this_el = jQuery(this);
	let modal_field = jQuery(lasso_modal.field_create);
	modal_field.attr("btn-large-add-field", false);
	modal_field.modal("show");

	let lasso_id = lasso_helper.get_value_by_data_attr(this_el, 'lasso-id');
	let field_group_id = lasso_helper.get_value_by_data_attr(this_el, 'field-group-id');

	jQuery('#lasso_id').val(lasso_id);
	jQuery('#field_group_id').val(field_group_id);
	jQuery('#order').val(jQuery(this_el).data('order'));
}

/**
 * Run process: add field to product
 */
function run_add_field_to_product() {
	let modal_field = jQuery(lasso_modal.field_create);
	flag_add_field = true;
	var lasso_table = jQuery(lasso_table_id_selector);
	var js_add_field_el = jQuery(this);
	var field_id = jQuery(js_add_field_el).attr("data-field-id");
	var target = modal_field.attr("data-target");
	var lasso_id = lasso_helper.get_value_by_id_selector("lasso_id");
	var table_style = lasso_helper.get_value_by_name("table_style");
	var table_id = lasso_helper.get_value_by_name("table_id");
	var field_group_id = lasso_helper.get_value_by_name("field_group_id");
	var order = lasso_helper.get_value_by_name("order");
	var action = 'add_field_to_product';

	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: {
			action: 'lasso_get_table_comparison_view',
			sub_action: action,
			target: target,
			field_id: field_id,
			lasso_id: lasso_id,
			table_id: table_id,
			table_style: table_style,
			field_group_id: field_group_id,
			order: order
		},
		beforeSend: function() {
			jQuery(lasso_modal.field_create).modal("hide");
			display_table_detail_loading(true);
			show_saving_sign();
		}
	})
		.done(function (res) {
			load_table(null);
		})
		.fail(function (xhr, status, error) {
			lasso_helper.display_ajax_error(xhr);
			jQuery(lasso_modal.field_create).modal("hide");
			jQuery(lasso_table).show();
		})
		.always(function() {
			jQuery(lasso_modal.field_create).attr("should-add-field-to-product-title-cell", 'false');
			show_saved_sign();
			display_table_detail_loading(false);
		});
}

/**
 * Click on button remove field
 */
function click_btn_remove_field() {
	var js_remove_field_el = jQuery(this);
	var field_id = lasso_helper.get_value_by_data_attr(js_remove_field_el, 'field-id');
	var lasso_id = lasso_helper.get_value_by_data_attr(js_remove_field_el, 'lasso-id');
	var field_name = lasso_helper.get_value_by_data_attr(js_remove_field_el, 'field-name');
	var field_group_id = lasso_helper.get_value_by_data_attr(jQuery(this).closest("li"), 'field-group-id');
	jQuery("#field-delete").find("#js-field-remove-button").text('Remove');
	jQuery("#field-delete").find("h2").text('Remove "' + field_name + '"?');
	jQuery("#lasso_id").val(lasso_id);
	jQuery("#field_id").val(field_id);
	jQuery("#field_group_id").val(field_group_id);
}

/**
 * Format rating start value
 */
function format_rating_star_value() {
	var stars = jQuery(this).val();
	stars = parseFloat(stars).toFixed(1);
	if (stars.substring(stars.length-1) != ".") {
		if(stars > 5) {
			stars = 5.0;
		}
		jQuery(this).val(stars);
		jQuery(this).closest('div').children(".lasso-stars").css("--rating", stars);
	}
}

/**
 * Click to button Add Column/Row
 */
function click_btn_add_row_column() {
	add_empty_field( jQuery(this) );
}

/**
 * Change field value
 */
function change_field_value() {
	var current_element = jQuery(this);
	setTimeout(function () {
		update_field( current_element );
	}, 500);
}

/**
 * Change input number value
 */
function change_input_number() {
	update_field( jQuery(this) );
}

/**
 * Focus out table name input, update the newest value
 */
function focusout_table_name() {
	add_or_update_table();
}

/**
 * Change table name heading text
 */
function change_table_name() {
	jQuery(".table-title").text(jQuery(this).val());
}

/**
 * Run process: Change table theme
 */
function change_table_theme() {
	add_or_update_table(
		function () {
			if ( preview_mode === 0 ) {
				preview_table()
			} else {
				load_table( null );
			}
		}
	);
}

/**
 * Click on button delete product, open the confirm modal
 */
function click_btn_delete_product() {
	var lasso_id = lasso_helper.get_value_by_data_attr(jQuery(this), 'lasso-id');
	var name = lasso_helper.get_value_by_data_attr(jQuery(this), 'name');
	modal_confirm_remove_product.set_heading( 'Remove "' + name + '"?' );
	modal_confirm_remove_product.set_lasso_id( lasso_id );
	modal_confirm_remove_product.show();
}

/**
 * Run process: Delete field
 */
function run_delete_field() {
	remove_fields( jQuery(this) );
}

/**
 * Switch between Edit/Preview mode
 */
function click_btn_preview_edit_table() {
	let this_tbl = jQuery(this);
	let table_style = lasso_helper.get_value_by_name("table_style");
	let btn_add_product = jQuery("#btn-add-product");
	let btn_add_col = jQuery("#btn-add-col-field");
	let btn_add_group = jQuery('#btn-add-group');
	let tbl_info = jQuery('.table-info');
	if ( preview_mode ) {
		preview_table();
		this_tbl.text("Edit Table");
		preview_mode = 0;
		btn_add_col.hide();
		btn_add_product.hide();
		btn_add_group.hide();

		if ( 'Column' == table_style ) {
			tbl_info.text('Vertical style limit display ' + lassoTableData.vertical_display_item_limit + ' products only');
		}
	} else {
		load_table( null, true, 'add_product', {'edit_table_mode': true} );
		this_tbl.text("Preview Table");
		preview_mode = 1;
		btn_add_col.show();
		btn_add_product.show();
		btn_add_group.show();
		tbl_info.text('');
		jQuery("#btn-add-col-field").show();
		jQuery("#btn-add-product").show();
	}
}

/**
 * Run process: Create field
 */
function run_create_field() {
	var title = jQuery("#field-title").val();
	var type = jQuery("#field-type-picker").val();
	var description = jQuery("#field-description").val();
	jQuery('.js-create-field').html(get_loading_image_small());

	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: {
			action: 'lasso_create_new_field',
			title: title,
			type: type,
			description: description
		}
	})
		.done(function(res) {
			if (typeof res.data === 'undefined') {
				return;
			}

			res = res.data;
			var search_keyword = jQuery("#field-from-library").find("input");
			jQuery(search_keyword).val(title);
			var pseudo_enter = jQuery.Event( "keyup", { which: 13 } );
			jQuery(search_keyword).trigger(pseudo_enter);
			jQuery("#create_from_library_tab").trigger("click");
			jQuery('.js-create-field').html("Create Field");

			if(res.status) {
				jQuery("#field-title").val("");
				jQuery("#field-description").val("");
			} else {
				// Alert to failure
				jQuery('#create_new_tab').trigger('click');
			}
		});
}

/**
 * Add product or add group
 */
function click_monetize_btn() {
	var modal = jQuery(this).closest('.modal');
	var modal_id = modal.attr('id');
	var lasso_id = lasso_helper.get_value_by_data_attr(jQuery(this), "lasso-id");

	modal.modal('hide');
	if ( 'group-monetize' === modal_id ) {
		load_table( lasso_id, true, 'add_group' );
	} else {
		load_table( lasso_id );
	}
}

/**
 * Open the confirm modal for clone table
 */
function click_btn_clone_table() {
	var message = 'Do you want to clone ' + jQuery(this).data('name');
	var modal_id = modal_confirm_clone_table.get_modal_id();
	jQuery('#'+modal_id).find('.btn-ok').attr('data-table-id', jQuery(this).data('table-id') );
	modal_confirm_clone_table.set_heading( message );
	modal_confirm_clone_table.set_description('');
	modal_confirm_clone_table.show();
}

/**
 * Change button text for Add product processing
 */
function add_item_to_table() {
	var js_montize_btn = jQuery(lasso_modal.link_monetize.find(".js-montize-btn"));
	jQuery(js_montize_btn).text("Add Product");
}

/**
 * Change button text for Add group processing
 */
function add_group_to_table() {
	var js_montize_btn = jQuery(lasso_modal.group_monetize.find(".js-montize-btn"));
	jQuery(js_montize_btn).text("Add Group");
}

/**
 * load table's content
 *
 * @param lasso_id
 * @param is_loading
 * @param sub_action
 * @param optional_data
 */
function load_table( lasso_id, is_loading = true, sub_action = 'add_product', optional_data = {} ) {
	var theme_name = lasso_helper.get_value_by_name("theme_name");
	var lasso_table = jQuery(lasso_table_id_selector);
	var table_style = lasso_helper.get_value_by_name("table_style");
	var table_id = lasso_helper.get_value_by_name("table_id");
	var content_tbl = jQuery('#report-content');
	var toggle = content_tbl.find('.js-toggle.js-popup-opened').first();
	toggle.removeClass('js-popup-opened');
	jQuery('#link-monetize').modal('hide');
	let edit_table_mode = optional_data.hasOwnProperty('edit_table_mode') && optional_data.edit_table_mode === true;

	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: {
			action: 'lasso_get_table_comparison_view',
			sub_action: sub_action,
			table_id: table_id,
			lasso_id: lasso_id,
			theme: theme_name,
			table_style: table_style,
			default_theme: 'cactus'
		},
		beforeSend: function() {
			if ( is_loading ) {
				if ( edit_table_mode ) {
					jQuery('.image_loading').html(get_loading_image());
					jQuery('.image_loading').removeClass('d-none');
					jQuery(lasso_table).hide();
				} else {
					display_table_detail_loading(true);
				}

			}
			if ( lasso_id ) {
				show_saving_sign();
			}
		}
	})
		.done(function (res) {
			show_table( res );
		})
		.fail(function (xhr, status, error) {
			lasso_helper.display_ajax_error(xhr);
			jQuery(lasso_table).show();
		})
		.always(function() {
			jQuery('.image_loading').addClass('d-none');
			display_table_detail_loading(false);
			if ( lasso_id ) {
				show_saved_sign();
			}
		});
}

/**
 * Trigger drag field
 * @param table_style
 */
function trigger_draggable( table_style ) {
	if ( "Row" === table_style ) {
		process_drag_drop_field_horizontal_style();
	}
	else {
		process_drag_drop_field_vertical_style();
	}
}

/**
 * Get table mapping data
 *
 * @returns {Array}
 */
function get_table_mapping_data() {
	var items = [];
	jQuery(".lasso-item-wrapper").each(function (i, el) {
		var lasso_id = jQuery(el).find(".lasso-title").attr("data-lasso-id");
		var fields = [];
		jQuery(el).find(".url-details-field-box").each(function (i2, el2) {
			var value = {};
			value.field_visible = jQuery(el2).find('.field_visible').val();
			value.field_value = jQuery(el2).find('.field_value').val();
			fields.push(
				{
					field_id: jQuery(el2).attr("data-field-id"),
					lasso_id: jQuery(el2).attr("data-lasso-id"),
					field_data: value
				}
			);
		});
		items.push({
			lasso_id: lasso_id,
			fields: fields
		});
	});
	return items;
}

/**
 * Update field process
 *
 * @param el
 */
function update_field( el ) {
	var element = jQuery(el);
	var cell_el = jQuery(element).closest('.url-details-field-box');
	var lasso_id = lasso_helper.get_value_by_data_attr( cell_el, 'lasso-id' );
	var field_id = lasso_helper.get_value_by_data_attr( cell_el, 'field-id' );
	var table_style = lasso_helper.get_value_by_name('table_style');
	var field_group_id = lasso_helper.get_value_by_data_attr(jQuery(element).closest('.sortable-column-fields'), 'field-group-id');
	if ( table_style === "Column" ) {
		field_group_id = lasso_helper.get_value_by_data_attr(jQuery(element).closest('.sortable-row-fields'), 'field-group-id');
	}
	var table_id = lasso_helper.get_value_by_name("table_id");
	var field_value = jQuery(element).val();
	field_value = lasso_helper.remove_empty_line_from_string(field_value);

	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: {
			action: 'lasso_get_table_comparison_view',
			sub_action: 'update_field',
			lasso_id: lasso_id,
			field_id: field_id,
			field_value: field_value,
			field_group_id: field_group_id,
			table_id: table_id
		},
		beforeSend: function() {
			show_saving_sign();
		}
	})
	.always(function() {
		// Show saved sign
		jQuery('.saved-item').removeClass('d-none');
		jQuery('.saving-item').addClass('d-none');
	});
}

/**
 * Add or update table
 *
 * @param callback
 * @returns {boolean}
 */
function add_or_update_table( callback = null ) {
	// Save table
	var title         = lasso_helper.get_value_by_id_selector("table_name");
	var theme         = lasso_helper.get_value_by_name("theme_name");
	var table_mapping = get_table_mapping_data();
	var table_style = lasso_helper.get_value_by_name("table_style");
	var table_id = lasso_helper.get_value_by_name("table_id");
	let show_headers_horizontal = jQuery("#is-show-headers-toggle").is(":checked") ? 1 : 0;
	let show_field_name = jQuery("#is-show-field-name").is(":checked") ? 1 : 0;

	if ( title.trim() === "" ) {
		return false;
	}

	var data_post = {
		action: 'lasso_get_table_comparison_view',
		sub_action: 'add_or_update_table',
		table_id: table_id,
		title: title,
		style: table_style,
		theme: theme,
		table_mapping: table_mapping,
		show_headers_horizontal: show_headers_horizontal,
		show_field_name: show_field_name
	};

	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: data_post,
		beforeSend: function( xhr ) {
			show_saving_sign();
		}
	})
		.done(function (res) {
			if (typeof res.data === 'undefined') {
				return;
			}

			res = res.data;
			if ( res.table_id > 0 && ( jQuery("#table_id").val() === "" || jQuery("#table_id").val() === 0 ) ) {
				jQuery("#table_id").val(res.table_id);
				jQuery("#shortcode").val('[lasso type="table" id="'+res.table_id+'"]');
				jQuery(".shortcode-wrapper").removeClass("d-none");

				var params = [];
				params.push({
					id: res.table_id
				});
				lasso_helper.push_data_to_url( params );
			}

			if ( callback !== null && typeof callback === "function" ) {
				return callback();
			}
		})
		.fail(function (xhr, status, error) {
			lasso_helper.display_ajax_error(xhr);

		})
		.always(function() {
			show_saved_sign();
		});
}

/**
 * Trigger scroll
 */
function trigger_scroll() {
	var table_style = lasso_helper.get_value_by_name("table_style");
	var lasso_table_wrapper_class_selector = '.lasso-table-wrapper';
	var last_item_location = jQuery(lasso_table_wrapper_class_selector).width();
	if ( table_style === "Column" ) {
		jQuery('html, body').animate({
			scrollTop: jQuery(lasso_table_wrapper_class_selector).height()
		}, 200, function () {
			// Done
		});

	} else {
		jQuery('.lasso-table-wrapper').animate({
			scrollLeft: last_item_location + 200
		}, 200, function () {
			// Done
		});
	}
}

/**
 * Remove product
 *
 * @param el
 */
function remove_product( el ) {
	var table_id = lasso_helper.get_value_by_name("table_id");
	var lasso_id = lasso_helper.get_value_by_data_attr(jQuery(el), 'lasso-id');
	var data_post = {
		action: 'lasso_get_table_comparison_view',
		sub_action: 'remove_product',
		table_id: table_id,
		lasso_id: lasso_id
	};
	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: data_post,
		beforeSend: function( xhr ) {
			display_table_detail_loading(true);
		}
	})
		.done(function (res) {
			load_table(null, false);
		})
		.fail(function (xhr, status, error) {
			lasso_helper.display_ajax_error(xhr);
		})
		.always(function() {
			display_table_detail_loading(false);
		});
}

/**
 * Remove fields
 *
 * @param el
 */
function remove_fields( el ) {
	var lasso_table = jQuery(lasso_table_id_selector);
	var table_style = lasso_helper.get_value_by_name("table_style");
	var table_id = lasso_helper.get_value_by_name("table_id");
	var field_id = lasso_helper.get_value_by_data_attr(jQuery(el), "field-id");
	var field_group_id = lasso_helper.get_value_by_data_attr(jQuery(el), "field-group-id");
	var data_post = {
		action: 'lasso_get_table_comparison_view',
		sub_action: 'remove_fields',
		table_id: table_id,
		field_id: field_id,
		field_group_id: field_group_id,
		table_style: table_style
	};

	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: data_post,
		beforeSend: function( xhr ) {
		}
	})
		.done(function (res) {
			if (typeof res.data === 'undefined') {
				return;
			}

			res = res.data;
			jQuery(lasso_table).html(res.html);
			calculate_height();
			trigger_draggable( table_style );
		})
		.fail(function (xhr, status, error) {
			lasso_helper.display_ajax_error(xhr);
		})
		.always(function() {
		});
}

/**
 * Calculate height
 */
function calculate_height() {
	var table_wrapper = jQuery('.lasso-table-wrapper');
	var table_content_wrapper = jQuery('.table-content-wrapper');
	var max_width_of_field = 0;
	var total_column_or_row = 0;
	var heights = [];
	var table_style = lasso_helper.get_value_by_name("table_style");
	var width = '250px';
	if ( "Row" === table_style ) {
		total_column_or_row = jQuery(".sortable_column ul.column").length;
		if ( total_column_or_row > 0 ) {
			// Calculate column with by automatically
			total_column_or_row = total_column_or_row + 1;
			max_width_of_field = jQuery(table_content_wrapper).width() / total_column_or_row;
			if ( total_column_or_row <= 5 ) {
				width = max_width_of_field + 'px';
				table_wrapper.removeClass('scroll-bar');
			} else {
				table_wrapper.addClass('scroll-bar');
			}
			jQuery(".sortable_column ul.column").css("width", width);

			jQuery(".table-row-heading-sortable").css("width", width);
			jQuery(".table-row-heading-fields > li").css("width", width);
		}

		jQuery(".sortable_column ul.column > li").css("height", "auto");
		jQuery(".table-row-heading-sortable > li").css("height", "auto");
		jQuery(".table-row-heading-fields > li").css("height", "auto");
		jQuery(".sortable_column ul.column > li").each(function (index, el) {
			heights.push(jQuery(el).height());
		});

		jQuery(".table-row-heading-sortable > li").each(function (index, el) {
			heights.push(jQuery(el).height());
		});

		var max_height = Math.max(...heights);
		var auto_height = max_height + 56;
		jQuery(".sortable_column ul.column > li").css("height", auto_height + "px");
		jQuery(".table-row-heading-sortable > li").css("height", auto_height + "px");
		jQuery(".table-row-heading-fields > li").css("height", auto_height + "px");

		var width = jQuery(".table-row-heading-sortable").width() - border_radius_weight;
		jQuery(".table-row-heading-fields").css('left', width + "px");

		//Auto calculate height for the large button add a Field
		let height_li_in_empty_group = 0;
		let btn_add_field_height = 0;
		let empty_group_sortable_column = jQuery('.empty-group > li:nth-child(1) .sortable-column-fields');
		if( jQuery(empty_group_sortable_column).find(' > li ').length > 1 ) {
			jQuery(empty_group_sortable_column).find(' > li ').each(function (i, el) {
				jQuery(el).removeClass('h-100');
				if ( ! jQuery(el).hasClass('btn-add-field') ) {
					height_li_in_empty_group += jQuery(el).height() + 15;
				}
			});
			// 25 + 25 + 3 = (margin-top + margin-bottom + border-weight )
			btn_add_field_height = auto_height - height_li_in_empty_group - ( 25 + 25 + 3 );
		}
		if ( btn_add_field_height <= 0 ) {
			jQuery('.empty-group .btn-add-field-big').css('height', "100%");
			jQuery('.empty-group .btn-add-field-big').css('margin-bottom', '0');
			jQuery('.empty-group .btn-add-field-big .box-add-field').css('height', '100%');
		} else {
			jQuery('.empty-group .btn-add-field-big').css('height', btn_add_field_height + "px");
			jQuery('.empty-group .btn-add-field-big').css('margin-bottom', '15px');
			jQuery('.empty-group .btn-add-field-big .box-add-field').css('height', '100%');
		}

	} else {
		total_column_or_row = jQuery(".table-column-heading-sortable >li").length;
		if ( total_column_or_row > 1 ) {
			// Calculate column with by automatically
			max_width_of_field = jQuery(table_content_wrapper).width() / total_column_or_row;
			if ( total_column_or_row <= 5 ) {
				width = max_width_of_field + 'px';
				table_wrapper.removeClass('scroll-bar');
			} else {
				table_wrapper.addClass('scroll-bar');
			}
			jQuery(".table-column-heading-sortable > li").css("width", width);
			jQuery(".sortable-row >li >ul >li ").css("width", width);
			jQuery(".table-column-heading-fields > li").css("width", width);
		} else if ( total_column_or_row === 1 ) {
			jQuery(".sortable-row >li ").css("width", width);
			jQuery(".table-column-heading-fields >li ").css("width", width);
			jQuery(".table-column-heading-sortable > li").css("width", width);
		}

		jQuery(".table-column-heading-sortable > li").css("height", "auto");
		jQuery(".table-column-heading-sortable > li").each(function (index, el) {
			heights.push(jQuery(el).height());
		});
		var max_height = Math.max(...heights);
		// Fix case when only one row, missing border bottom
		if ( jQuery(".lasso-table-wrapper #table-column > ul.sortable-row > li.row-content ").length == 0) {
			max_height += 15;
		}

		jQuery(".table-column-heading-sortable > li").css("height", max_height + "px");

		// Vertical
		jQuery(".table-column-heading-fields").css("top", jQuery(".table-column-heading-sortable >li:first-child").height() +"px");

		var html_stacks = [];
		jQuery(".sortable-row >li >ul >li").each(function (i, el) {
			if ( jQuery(el).find("ul>li").length > 0 ) {
				html_stacks.push(jQuery(el).find("ul>li"));
			}
		});
		jQuery(".sortable-row >li >ul >li").each(function (i, el) {
			if ( jQuery(el).find("ul>li").length === 0 ) {
				jQuery(el).css("height", "120px");
			}
		});
		for( var i = 0 ; i < html_stacks.length ; i++ ) {
			jQuery(html_stacks[i]).closest(".row-content").find('>ul>li').css("height", "auto");
		}

	}
}

/**
 * Sort products
 *
 * @param el
 */
function sort_products( el ) {

	setTimeout(
		function () {
			var data = [];

			jQuery(el).find("> li").each(function (index, el) {
				var lasso_id = lasso_helper.get_value_by_data_attr( el, 'lasso-id' );
				var order = lasso_helper.get_value_by_data_attr( el, 'order' );
				data.push({
					lasso_id: lasso_id,
					order: order
				});
			});

			var table_id = lasso_helper.get_value_by_name("table_id");
			var data_post = {
				action: 'lasso_get_table_comparison_view',
				sub_action: 'sort_products',
				table_id: table_id,
				data: data
			};
			jQuery.ajax({
				url: lassoOptionsData.ajax_url,
				type: 'post',
				data: data_post,
				beforeSend: function( xhr ) {
					show_saving_sign();
					display_table_detail_loading();
				}
			})
				.done(function (res) {
					load_table(null, false);
				})
				.fail(function (xhr, status, error) {
					lasso_helper.display_ajax_error(xhr);
					display_table_detail_loading(false);
				})
				.always(function() {
					show_saved_sign();
				});
		},
		200
	);
}

/**
 * Sort field order inside group
 *
 * @param el
 * @param field_group_id
 * @param callback
 */
function sort_field_inside_group( el, field_group_id = null, callback = null ) {
	var data = [];
	jQuery(el).find("> li").each(function (index, el) {
		var lasso_id = lasso_helper.get_value_by_data_attr( el, 'lasso-id' );
		var order = lasso_helper.get_value_by_data_attr( el, 'order' );
		var field_id = lasso_helper.get_value_by_data_attr( el, 'field-id' );
		if ( field_group_id === null ) {
			field_group_id = lasso_helper.get_value_by_data_attr( el, 'field-group-id' );
		}
		// Should check undefine for in case sort field with "the large btn add a Field"
		if ( field_id !== undefined ) {
			data.push({
				lasso_id: lasso_id,
				order: order,
				field_id: field_id,
				field_group_id: field_group_id
			});
		}
	});

	var table_id = lasso_helper.get_value_by_name("table_id");
	var data_post = {
		action: 'lasso_get_table_comparison_view',
		sub_action: 'sort_field_inside_group',
		table_id: table_id,
		data: data
	};
	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: data_post,
		beforeSend: function( xhr ) {
			show_saving_sign();
			display_table_detail_loading(true);
		}
	})
		.done(function (res) {
			load_table(null);
			if ( callback !== null && typeof callback === "function" ) {
				return callback();
			}
		})
		.fail(function (xhr, status, error) {
			lasso_helper.display_ajax_error(xhr);
			jQuery('html, body').animate({
				scrollTop: 0
			}, 200, function () {
				// Done
			});
			load_table(null, false);
		})
		.always(function() {
			show_saved_sign();
			calculate_height();
		});
}

/**
 * Add empty field
 *
 * @param el
 */
function add_empty_field( el ) {
	var btn_current = jQuery(el);
	var lasso_table = jQuery(lasso_table_id_selector);
	var lasso_id = lasso_helper.get_value_by_name('lasso_id');
	var table_style = lasso_helper.get_value_by_name("table_style");
	var table_id = lasso_helper.get_value_by_name("table_id");
	var btn_add_field_column_label = table_style === 'Row' ? 'Add Column' : 'Add Row';

	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: {
			action: 'lasso_get_table_comparison_view',
			sub_action: 'add_col_field',
			table_id: table_id,
			lasso_id: lasso_id,
			table_style: table_style
		},
		beforeSend: function() {
			lasso_helper.add_loading_button(btn_current, btn_add_field_column_label);
			show_saving_sign();
		}
	})
	.done(function (res) {
		if (typeof res.data === 'undefined') {
			return;
		}

		lasso_helper.add_loading_button(btn_current, btn_add_field_column_label, false );
		res = res.data;
		jQuery(lasso_table).html(res.html);
		jQuery(lasso_table).show();
		calculate_height();

		trigger_draggable(table_style);
		trigger_scroll();

		// Fix incorrect display border bottom on class only-one-group
		if ( table_style !== 'Row' ) {
			let number_of_header_row = jQuery(".lasso-table-wrapper #table-column > ul.heading").length;
			let number_of_content_row = jQuery(".lasso-table-wrapper #table-column > ul.sortable-row > li.row-content").length;
			let total_vertical_row = number_of_header_row + number_of_content_row;

			if ( total_vertical_row > 1 ) {
				jQuery('#table-column > ul.heading').removeClass('only-one-group');
			}
		}
	})
	.fail(function (xhr, status, error) {
		lasso_helper.add_loading_button(btn_current, 'Add Field', false );
		lasso_helper.display_ajax_error(xhr);
	})
	.always(function() {
		display_table_detail_loading(false);
		show_saved_sign();
	});
}

/**
 * Drag field to other column row
 *
 * @param from_el
 * @param to_el
 * @param from_field_el
 * @param to_field_el
 */
function drag_field_to_other_column_row( from_el, to_el, from_field_el, to_field_el ) {
	var field_id = lasso_helper.get_value_by_data_attr(jQuery(to_field_el), 'field-id');
	var table_id = lasso_helper.get_value_by_name("table_id");
	var table_style = lasso_helper.get_value_by_name("table_style");
	var from_lasso_id = lasso_helper.get_value_by_data_attr(jQuery(from_el).closest('ul'), 'lasso-id');
	var to_lasso_id = lasso_helper.get_value_by_data_attr(to_el, 'lasso-id');
	var from_group_id = lasso_helper.get_value_by_data_attr(from_el, 'field-group-id');
	var to_group_id = lasso_helper.get_value_by_data_attr(to_el, 'field-group-id');

	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: {
			action: 'lasso_get_table_comparison_view',
			sub_action: 'update_field_to_other_group',
			table_id: table_id,
			from_lasso_id: from_lasso_id,
			to_lasso_id: to_lasso_id,
			field_id: field_id,
			from_group_id: from_group_id,
			to_group_id: to_group_id
		},
		beforeSend: function() {
			show_saving_sign();
			display_table_detail_loading(true);
		}
	})
		.done(function (res) {
			let class_selector = table_style === "Row" ? '.sortable-column-fields' : '.sortable-row-fields';
			sort_field_inside_group( jQuery(to_field_el).closest(class_selector), to_group_id );
		})
		.fail(function (xhr, status, error) {
			lasso_helper.display_ajax_error(xhr);
			jQuery('html, body').animate({
				scrollTop: 0
			}, 200, function () {
				// Done
			});
			load_table( null, false );

		})
		.always(function() {
			show_saved_sign();
		});
}

/**
 * Preview table process
 */
function preview_table() {
	jQuery('#shortcode').trigger('click'); // click another place to save changes before previewing table

	let image_loading = jQuery('.image_loading');
	let lasso_table = jQuery(lasso_table_id_selector);
	let table_id = lasso_helper.get_value_by_name("table_id");

	image_loading.html(get_loading_image());
	image_loading.removeClass('d-none');
	jQuery(lasso_table).hide();

	setTimeout(() => {
		jQuery.ajax({
			url: lassoOptionsData.ajax_url,
			type: 'post',
			data: {
				action: 'lasso_get_table_comparison_view',
				sub_action: 'preview_table',
				table_id: table_id
			}
		})
		.done(function (res) {
			if (typeof res.data === 'undefined') {
				return;
			}

			res = res.data;
			jQuery(lasso_table).html(res.html);
			jQuery(lasso_table).show();
		})
		.fail(function (xhr, status, error) {
			lasso_helper.display_ajax_error(xhr);
			jQuery(lasso_table).show();
		})
		.always(function() {
			image_loading.addClass('d-none');
		});
	}, 1000);
}

/**
 * Delete field
 *
 * @param el
 */
function deleteField( el ) {
	lasso_helper.add_loading_button(el);
	var lasso_table = jQuery(lasso_table_id_selector);
	var table_id = lasso_helper.get_value_by_name('table_id');
	var field_id = lasso_helper.get_value_by_name('field_id');
	var field_group_id = lasso_helper.get_value_by_name('field_group_id');
	var lasso_id = lasso_helper.get_value_by_name('lasso_id');
	var table_style = lasso_helper.get_value_by_name("table_style");
	var data_post = {
		action: 'lasso_get_table_comparison_view',
		table_id: table_id,
		lasso_id: lasso_id,
		field_id: field_id,
		field_group_id: field_group_id,
		sub_action: "remove_field"
	};
	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: data_post,
		beforeSend: function( xhr ) {
			show_saving_sign();
		}
	})
		.done(function (res) {
			if (typeof res.data === 'undefined') {
				return;
			}

			res = res.data;
			jQuery(lasso_table).html(res.html);
			calculate_height();
			trigger_draggable( table_style );

			jQuery("#field-delete").modal("hide");
		})
		.fail(function (xhr, status, error) {
			if(xhr.lasso_error) {
				error = xhr.lasso_error;
			}
			lasso_helper.errorScreen(error);
		})
		.always(function() {
			show_saved_sign();
		});
}

/**
 * Show table then calculate height and apply trigger draggable
 *
 * @param response
 */
function show_table( response ) {
	var lasso_table = jQuery(lasso_table_id_selector);
	res = response.data;
	jQuery(lasso_table).html(res && 'html' in res ? res.html : '');
	jQuery(lasso_table).show();
	display_table_detail_loading(false);
	calculate_height();
	trigger_draggable(lasso_helper.get_value_by_name("table_style"));
	jQuery('#js-field-remove-button').click(function() {
		deleteField(this);
	});
}

/**
 * Sort field group
 *
 * @param el
 */
function sort_field_group( el ) {
	var table_id = lasso_helper.get_value_by_name("table_id");
	var table_style = lasso_helper.get_value_by_name("table_style");
	setTimeout(
		function () {
			var data = [];
			if ( table_style === "Row" ) {
				jQuery(el).find("> ul").each(function (index, el) {
					var field_group_id = lasso_helper.get_value_by_data_attr(jQuery(el).find('> li:first-child'), 'field-group-id');
					let ul_group = jQuery(el).find('> li:first-child').find('.sortable-column-fields');
					if (ul_group.length >= 1 ) {
						field_group_id = lasso_helper.get_value_by_data_attr(ul_group, 'field-group-id');
					}
					var order = lasso_helper.get_value_by_data_attr( el, 'order' );
					data.push({
						order: order,
						field_group_id: field_group_id
					});
				});
			} else {
				jQuery(el).find("> li").each(function (index, el) {
					var field_group_id = lasso_helper.get_value_by_data_attr(jQuery(el).find('ul > li:first-child'), 'field-group-id');
					if ( field_group_id === undefined ) {
						let ul_group = jQuery(el).find('ul > li:first-child').find('.sortable-row-fields');
						if (ul_group.length >= 1 ) {
							field_group_id = lasso_helper.get_value_by_data_attr(ul_group, 'field-group-id');
						}
					}
					var order = lasso_helper.get_value_by_data_attr( el, 'order' );

					data.push({
						order: order,
						field_group_id: field_group_id
					});
				});
			}

			var data_post = {
				action: 'lasso_get_table_comparison_view',
				sub_action: 'sort_field_group',
				table_id: table_id,
				data: data
			};
			jQuery.ajax({
				url: lassoOptionsData.ajax_url,
				type: 'post',
				data: data_post,
				beforeSend: function( xhr ) {
					show_saving_sign();
					display_table_detail_loading(true);
				}
			})
				.done(function (res) {
					// show_table( res );
				})
				.fail(function (xhr, status, error) {
					lasso_helper.display_ajax_error(xhr);
					jQuery('html, body').animate({
						scrollTop: 0
					}, 200, function () {
						// Done
					});
				})
				.always(function() {
					show_saved_sign();
					display_table_detail_loading(false);
				});
		},
		200
	);

}

/**
 * Add field to product
 *
 * @param lasso_id
 * @param field_group_id
 * @param field_id
 * @param is_loading
 */
function add_field_to_product( lasso_id, field_group_id, field_id, is_loading = true ) {
	var lasso_table = jQuery(lasso_table_id_selector);
	var table_style = lasso_helper.get_value_by_name("table_style");
	var table_id = lasso_helper.get_value_by_name("table_id");
	var field_values = [];
	if ( table_style === 'Column' ) {
		jQuery('.row-content .field_value').each(function (i, el) {
			field_values.push({
				'lasso_id': jQuery(el).closest('li').data('lasso-id'),
				'field_id': jQuery(el).closest('li').data('field-id'),
				'field_value': jQuery(el).val()
			});
		})
	} else {
		jQuery('.column .field_value').each(function (i, el) {
			field_values.push({
				'lasso_id': jQuery(el).closest('li').data('lasso-id'),
				'field_id': jQuery(el).closest('li').data('field-id'),
				'field_value': jQuery(el).val()
			});
		})
	}
	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: {
			action: 'lasso_get_table_comparison_view',
			sub_action: 'add_field_to_product',
			field_id: field_id,
			lasso_id: lasso_id,
			table_id: table_id,
			table_style: table_style,
			field_group_id: field_group_id,
			field_values: field_values
		},
		beforeSend: function() {
			if ( is_loading ) {
				jQuery(lasso_modal.field_create).modal("hide");
				display_table_detail_loading(true);
				show_saving_sign();
			}
		}
	})
		.done(function (res) {
			if ( is_loading ) {
				load_table(null, is_loading);
			}
		})
		.fail(function (xhr, status, error) {
			lasso_helper.display_ajax_error(xhr);
			jQuery(lasso_modal.field_create).modal("hide");
			jQuery(lasso_table).show();
		})
		.always(function() {
			display_table_detail_loading(false);
			show_saved_sign();
		});
}

/**
 * Process drag drop field vertical style
 */
function process_drag_drop_field_vertical_style() {
	var from_el = null;
	var to_el = null;
	var from_field_el = null;
	var to_field_el = null;
	var from_id_selector  = null;
	var to_id_selector = null;
	var to_lasso_id = null;
	var from_lasso_id = null;

	jQuery(".sortable-row").sortable({
		cursor: "move",
		start: function( event, ui ) {
			jQuery(this).addClass("dragging");
		},
		stop: function() {
			jQuery(this).removeClass("dragging");
			jQuery('.sortable-row .row-content').each(function (index, element) {
				jQuery(element).data('order', ( index + 1 ) );
			});
			refresh_table_after_sort_group_vertical_style();
			sort_field_group( jQuery(this) );
		}
	});

	jQuery(".table-column-heading-sortable").sortable({
		cursor: "move",
		axis: 'x',
		start: function( event, ui ) {
			jQuery(this).addClass("dragging");
			jQuery(".sortable-row").addClass("visibility-hide");
			jQuery(".sortable-row").removeClass("visibility-visible");

			jQuery(".table-column-heading-fields").addClass("visibility-visible");
			jQuery(".table-column-heading-fields").removeClass("visibility-hide");

		},
		stop: function() {
			jQuery(this).removeClass("dragging");
			jQuery(".sortable-row").removeClass("visibility-hide");
			jQuery(".sortable-row").addClass("visibility-visible");

			jQuery(".table-column-heading-fields").removeClass("visibility-visible");
			jQuery(".table-column-heading-fields").addClass("visibility-hide");

			jQuery('.table-column-heading-sortable > li').each(function (index, element) {
				jQuery(element).data('order', ( index + 1 ) );
			});
			refresh_table_after_sort_product_vertical_style();
			sort_products( jQuery(this) );
		}
	});

	jQuery( ".sortable-row-fields" ).sortable({
		cursor: "move",
		receive: function(ev, ui) {
			if(ui.item.hasClass("btn-add-field"))
				ui.sender.sortable("cancel");
		},
		start: function( event, ui ) {
			from_el = jQuery(ui.item[0]).closest('.sortable-row-fields');
			from_field_el = ui.item[0];
			from_lasso_id = lasso_helper.get_value_by_data_attr(from_field_el, 'lasso-id');
		},
		stop: function( event, ui ) {
			to_field_el = ui.item[0];
			to_el = jQuery(to_field_el).closest('ul');

			var field_id = lasso_helper.get_value_by_data_attr(to_field_el, 'field-id');
			to_lasso_id = lasso_helper.get_value_by_data_attr(to_el, 'lasso-id');
			var index_field = -1;
			jQuery(to_el).find('> li').each(function (index, element) {
				if ( lasso_helper.get_value_by_data_attr(element, 'field-id') === field_id ) {
					index_field = index;
					index_field++;
					return false;
				}
			});

			var css_selector = '.' + jQuery(to_field_el).attr("class");
			var id_selector = lasso_helper.get_value_by_data_attr(jQuery(to_field_el), 'id');
			var field_group_id = lasso_helper.get_value_by_data_attr(to_el, 'field-group-id');
			css_selector = css_selector.split(" ")[0];

			jQuery('.sortable-row-fields ' +css_selector).each(function (index_2, el_2) {
				var current_id_selector = lasso_helper.get_value_by_data_attr(jQuery(el_2), 'id');
				if ( current_id_selector !== id_selector ) {
					var lasso_id = lasso_helper.get_value_by_data_attr(jQuery(el_2),'lasso-id');
					var to_el_class_selector = '.sortable-row-fields-' + field_group_id + '-' + lasso_id;
					let same_from_to_field = lasso_helper.get_value_by_data_attr(from_field_el, 'field-id') === lasso_helper.get_value_by_data_attr(to_field_el, 'field-id');
					if ( index_field === 1 ) {
						if ( jQuery(to_el_class_selector + " > li" ).length === 0 ) {
							jQuery(to_el_class_selector).append( el_2 );
						} else if ( from_lasso_id === to_lasso_id && same_from_to_field ) {
							jQuery(to_el_class_selector + " > li:nth-child(1)" ).before( el_2 );
						}
					} else if ( jQuery(to_el_class_selector + " > li:nth-child(" + index_field + ")" ).length !== 0 ) {
						let from_group_id = lasso_helper.get_value_by_data_attr(from_el, 'field-group-id');
						let to_group_id = lasso_helper.get_value_by_data_attr(to_el, 'field-group-id');
						if ( from_lasso_id === to_lasso_id && same_from_to_field ) {
							jQuery(to_el_class_selector + " > li:nth-child(" + ( index_field ) + ")" ).after( el_2 );
							if ( from_group_id !== to_group_id ) {
								jQuery(to_el_class_selector + " > li:nth-child(" + ( index_field - 1 ) + ")" ).after( el_2 );
							} else {
								jQuery(to_el_class_selector + " > li:nth-child(" + ( index_field ) + ")" ).before( el_2 );
							}
						}
					} else {
						jQuery(to_el_class_selector).append( el_2 );
					}
				}
			});

			jQuery(to_el).find('> li').each(function (index, element) {
				jQuery(element).data('order', ( index + 1 ) );
			});

			calculate_height();
			// Update hidden fields
			jQuery('.sortable-row .row-content >ul >li').each(function (index, el) {
				var ul_el = jQuery(el).find('>ul');
				var group_id = lasso_helper.get_value_by_data_attr(jQuery(ul_el), 'field-group-id');
				var lasso_id = lasso_helper.get_value_by_data_attr(jQuery(ul_el), 'lasso-id');
				var class_selector = `.sortable-row-fields-${group_id}-${lasso_id}`;
				var class_selector_hidden_fields = `.table-column-heading-fields-${group_id}-${lasso_id}`;
				jQuery(class_selector_hidden_fields).html(jQuery(class_selector).html());
			});


			from_id_selector = jQuery(from_el).attr("id");
			to_id_selector = jQuery(to_el).attr("id");

			var current_field_group_id = lasso_helper.get_value_by_data_attr(jQuery(from_el), 'field-group-id');
			var target_field_group_id = lasso_helper.get_value_by_data_attr(jQuery(to_el), 'field-group-id');

			if ( current_field_group_id === target_field_group_id && from_id_selector === to_id_selector ) {
				// Update fields for hidden fields
				var lasso_id = lasso_helper.get_value_by_data_attr(jQuery(to_el),'lasso-id');
				var fields_wrapper_selector = `.table-column-heading-fields-${target_field_group_id}-${lasso_id}`;
				jQuery(fields_wrapper_selector).html(jQuery(to_el).html());

				if ( jQuery(this).find(">li").length === 0 ) {
					sort_field_inside_group( jQuery(to_el).closest("ul") );
				} else {
					sort_field_inside_group( jQuery(to_el) );
				}
			} else {
				// Drag to other row
				// ? Re-index the order
				jQuery(to_el).find('> li').each(function (index, element) {
					jQuery(element).data('order', ( index + 1 ) );
				});
				var fields = jQuery(from_el).find('>li');
				if ( fields.length === 0 ) {
					// ? Remove empty row
					jQuery(from_el).closest('.row-content').remove();
				}
				drag_field_to_other_column_row( from_el, to_el, from_field_el, to_field_el );
			}
		},
		connectWith: ".sortable-row-fields"

	}).disableSelection();
}

/**
 * Process drag drop field horizontal style
 */
function process_drag_drop_field_horizontal_style() {
	var from_el = null;
	var to_el = null;
	var from_field_el = null;
	var to_field_el = null;
	var from_id_selector  = null;
	var to_id_selector = null;
	var to_lasso_id = null;
	var from_lasso_id = null;
	jQuery(".sortable_column").sortable({
		cursor: "move",
		axis: 'x',
		start: function( event, ui ) {
			jQuery(this).addClass("dragging");
		},
		stop: function() {
			jQuery(this).removeClass("dragging");
			jQuery('.sortable_column .column').each(function (index, element) {
				jQuery(element).data('order', ( index + 1 ) );
			});
			refresh_table_after_sort_group_horizontal_style();
			sort_field_group( jQuery(this) );
		}
	});

	jQuery( ".table-row-heading-sortable" ).sortable({
		cursor: "move",
		start: function( event, ui ) {
			jQuery(this).addClass("dragging");
			jQuery(".table-row .col_content").addClass("hide");
			jQuery(this).find(".table-row-heading-fields").addClass('show');
		},
		stop: function() {
			jQuery(this).removeClass("dragging");
			jQuery(".table-row .col_content").removeClass("hide");
			jQuery(this).find(".table-row-heading-fields").removeClass('show');
			jQuery('.table-row-heading-sortable > li').each(function (index, element) {
				jQuery(element).data('order', ( index + 1 ) );
			});
			refresh_table_after_sort_product_horizontal_style();
			sort_products( jQuery(this) );
		}
	});

	var current_el = null;
	var target_el = null;
	jQuery( ".table-row-heading-sortable" ).disableSelection();
	jQuery( ".sortable-column-fields" ).sortable({
		handle: function(event, ui) {
		},
		receive: function(ev, ui) {
			if(ui.item.hasClass("btn-add-field"))
				ui.sender.sortable("cancel");
		},
		tolerance: 'intersect',
		start: function( event, ui ) {
			current_el = jQuery(ui.item[0]).closest('.sortable-column-fields');

			from_el = jQuery(ui.item[0]).closest('.sortable-column-fields');
			from_field_el = ui.item[0];
			from_lasso_id = lasso_helper.get_value_by_data_attr(from_field_el, 'lasso-id');
		},
		stop: function( event, ui ) {
			to_field_el = ui.item[0];
			to_el = jQuery(ui.item[0]).closest('.sortable-column-fields');
			var field_id = lasso_helper.get_value_by_data_attr(to_field_el, 'field-id');
			to_lasso_id = lasso_helper.get_value_by_data_attr(to_el, 'lasso-id');
			var index_field = -1;
			jQuery(to_el).find('> li').each(function (index, element) {
				if ( lasso_helper.get_value_by_data_attr(element, 'field-id') === field_id ) {
					index_field = index;
					index_field++;
					return false;
				}
			});

			var css_selector = '.' + jQuery(to_field_el).attr("class");
			var id_selector = lasso_helper.get_value_by_data_attr(jQuery(to_field_el), 'id');
			var field_group_id = lasso_helper.get_value_by_data_attr(to_el, 'field-group-id');

			//Reset order to make sure drag field will correct when client drag from up to down and vice versa
			jQuery('.sortable-column-fields-' + field_group_id).each(function (i, el) {
				jQuery(el).find(' > li ').each(function (i2, el2) {
					jQuery(el2).data('order', (i2+1));
				})
			});

			jQuery('.sortable-column-fields ' +css_selector).each(function (index_2, el_2) {
				// Clone field when drag and drop
				var current_id_selector = lasso_helper.get_value_by_data_attr(jQuery(el_2), 'id');
				if ( current_id_selector !== id_selector ) {
					var lasso_id = lasso_helper.get_value_by_data_attr(jQuery(el_2),'lasso-id');
					var to_el_class_selector = '.sortable-column-fields-' + field_group_id + '-' + lasso_id;
					let same_from_to_field = lasso_helper.get_value_by_data_attr(from_field_el, 'field-id') === lasso_helper.get_value_by_data_attr(to_field_el, 'field-id');
					if ( index_field === 1 ) {
						if ( jQuery(to_el_class_selector + " > li" ).length === 0 && from_lasso_id === to_lasso_id && same_from_to_field ) {
							jQuery(to_el_class_selector).append( el_2 );
						} else if (from_lasso_id === to_lasso_id && same_from_to_field ) {
							jQuery(to_el_class_selector + " > li:nth-child(1)" ).before( el_2 );
						}
					} else if ( jQuery(to_el_class_selector + " > li:nth-child(" + index_field + ")" ).length !== 0 ) {
						if ( from_lasso_id === to_lasso_id && same_from_to_field ) {
							let from_group_id = lasso_helper.get_value_by_data_attr(from_el, 'field-group-id');
							let to_group_id = lasso_helper.get_value_by_data_attr(to_el, 'field-group-id');
							if ( from_group_id === to_group_id ) {
								let el_2_order = parseInt(jQuery(el_2).data('order'));
								let order = parseInt( jQuery(to_el_class_selector + " > li:nth-child(" + ( index_field ) + ")").data('order') );
								if ( order > el_2_order ) {
									// Drag field from up to down
									jQuery(to_el_class_selector + " > li:nth-child(" + ( index_field ) + ")" ).after( el_2 );
								}
								else {
									// Drag field from down to up
									jQuery(to_el_class_selector + " > li:nth-child(" + ( index_field  ) + ")" ).before( el_2 );
								}
							} else {
								jQuery(to_el_class_selector + " > li:nth-child(" + ( index_field - 1 ) + ")" ).after( el_2 );
							}
						}
					} else {
						jQuery(to_el_class_selector).append( el_2 );
					}
				}
			});

			//Update hidden fields
			jQuery(".sortable_column > ul.column > li").each(function (index, el) {
				var ul_el = jQuery(el).find('>ul');
				var group_id = lasso_helper.get_value_by_data_attr(jQuery(ul_el), 'field-group-id');
				var lasso_id = lasso_helper.get_value_by_data_attr(jQuery(ul_el), 'lasso-id');
				var class_selector = `.sortable-column-fields-${group_id}-${lasso_id}`;
				var class_selector_hidden_fields = `.table-row-heading-fields-wrapper-${group_id}-${lasso_id}`;
				jQuery(class_selector_hidden_fields).html(jQuery(class_selector).html());
			});

			calculate_height();

			var current_field_group_id = lasso_helper.get_value_by_data_attr(jQuery(from_el), 'field-group-id');
			var target_field_group_id = lasso_helper.get_value_by_data_attr(jQuery(to_el), 'field-group-id');

			from_id_selector = jQuery(from_el).attr("id");
			to_id_selector = jQuery(to_el).attr("id");

			if ( current_field_group_id === target_field_group_id && from_id_selector === to_id_selector ) {
				// Drag inside column
				//Update fields for hidden fields
				var lasso_id = lasso_helper.get_value_by_data_attr(jQuery(to_el),'lasso-id');
				var fields_wrapper_selector = `.table-row-heading-fields-wrapper-${target_field_group_id}-${lasso_id}`;
				jQuery(fields_wrapper_selector).html(jQuery(to_el).html());

				if ( jQuery(this).find(">li").length === 0 ) {
					sort_field_inside_group( jQuery(to_el).closest("ul") );
				} else {
					sort_field_inside_group( jQuery(to_el) );
				}
			}
			else {
				var wrapper_drag_el = from_el.closest('.column');
				var fields = wrapper_drag_el.find(" > li:first-child .sortable-column-fields > li" );
				if ( fields.length === 0 ) {
					wrapper_drag_el.remove();
				}

				// Drag to other column
				// ? Re-index the order
				jQuery(target_el).find('> li').each(function (index, element) {
					jQuery(element).data('order', ( index + 1 ) );
				});
				drag_field_to_other_column_row( from_el, to_el, from_field_el, to_field_el );
			}

		},
		connectWith: ".sortable-column-fields"

	}).disableSelection();
}

/**
 * Refresh table after sort product vertical style
 */
function refresh_table_after_sort_product_vertical_style() {
	jQuery('.table-column-heading-sortable >li').each(function (index, el) {
		var index_2 = index + 1;
		var lasso_id = lasso_helper.get_value_by_data_attr(jQuery(el), 'lasso-id');
		jQuery('.sortable-row >li').each(function (index_3, el_3) {
			var current_el = jQuery(el_3).find('>ul>li:nth-child('+index_2+')');

			var old_lasso_id = lasso_helper.get_value_by_data_attr(jQuery(current_el), 'lasso-id');
			var group_id = lasso_helper.get_value_by_data_attr(jQuery(current_el), 'field-group-id');
			var old_class_selector = `sortable-row-fields-${group_id}-${old_lasso_id}`;

			var sortable_row_fields = jQuery(current_el).find(".sortable-row-fields");
			jQuery(sortable_row_fields).removeClass(old_class_selector);

			jQuery(current_el).data('lasso-id', lasso_id);
			jQuery(sortable_row_fields).data('lasso-id', lasso_id);

			var sortable_row_fields_li = jQuery(sortable_row_fields).find(" > li");
			var field_id = lasso_helper.get_value_by_data_attr(sortable_row_fields_li, 'field-id');
			jQuery(sortable_row_fields_li).data('lasso-id', lasso_id);
			jQuery(sortable_row_fields_li).data('id', `id-${lasso_id}-${field_id}`);

			var new_class_selector = `sortable-row-fields-${group_id}-${lasso_id}`;
			jQuery(sortable_row_fields).addClass(new_class_selector);
		});
	});
}

/**
 * Refresh table after sort product horizontal style
 */
function refresh_table_after_sort_product_horizontal_style() {
	jQuery(".table-row-heading-sortable > li").each(function (index_1, el_1) {
		var new_lasso_id = lasso_helper.get_value_by_data_attr(jQuery(el_1), 'lasso-id');
		var ntd_child_1 = index_1 + 1;
		jQuery(el_1).find(">ul.table-row-heading-fields > li").each(function (index_2, el_2) {
			var hidden_field_html = jQuery(el_2).find(">ul").html();

			var ntd_child_2 = index_2 + 1;
			var column_li = jQuery(".sortable_column > ul.column:nth-child("+ntd_child_2+") > li:nth-child("+ntd_child_1+")");
			var old_lasso_id = lasso_helper.get_value_by_data_attr(jQuery(column_li), 'lasso-id');
			var field_group_id = lasso_helper.get_value_by_data_attr(jQuery(column_li), 'field-group-id');
			var old_class_1 = `sortable-column-fields-${field_group_id}-${old_lasso_id}`;
			var old_class_2 = `sortable-column-fields-wrapper-${old_lasso_id}`;
			jQuery(column_li).data("lasso-id", new_lasso_id);

			var ul_wrapper_fields = jQuery(column_li).find("> ul");
			jQuery(ul_wrapper_fields).data("lasso-id", new_lasso_id);
			jQuery(ul_wrapper_fields).html(hidden_field_html);

			var new_class_1 = `sortable-column-fields-${field_group_id}-${new_lasso_id}`;
			var new_class_2 = `sortable-column-fields-wrapper-${new_lasso_id}`;
			jQuery(ul_wrapper_fields).removeClass(old_class_1);
			jQuery(ul_wrapper_fields).removeClass(old_class_2);
			jQuery(ul_wrapper_fields).addClass(new_class_1);
			jQuery(ul_wrapper_fields).addClass(new_class_2);
		});
	});

}

/**
 * Fresh table after sort group horizontal style
 */
function refresh_table_after_sort_group_horizontal_style() {
	jQuery(".sortable_column > ul.column").each(function (index_1, el_1) {
		var ntd_child_1 = index_1 + 1;
		jQuery(el_1).find("> li").each(function ( index_2, el_2 ) {
			var field_group_id = lasso_helper.get_value_by_data_attr( el_2, 'field-group-id' );
			var lasso_id = lasso_helper.get_value_by_data_attr( el_2, 'lasso-id' );
			var ntd_child_2 = index_2 + 1;
			var ul_wrapper_fields_html = jQuery(el_2).find("> ul").html();
			var li_hidden_field = jQuery(".table-row-heading-sortable >li:nth-child("+ntd_child_2+") .table-row-heading-fields > li:nth-child("+ntd_child_1+")");
			var old_field_group_id = lasso_helper.get_value_by_data_attr(jQuery(li_hidden_field), 'field-group-id');
			var old_class = `table-row-heading-fields-wrapper-${old_field_group_id}-${lasso_id}`;
			jQuery(li_hidden_field).data('field-group-id', field_group_id );

			var new_class = `table-row-heading-fields-wrapper-${field_group_id}-${lasso_id}`;
			var li_hidden_field_ul = jQuery(li_hidden_field).find(">ul");
			jQuery(li_hidden_field_ul).removeClass(old_class);
			jQuery(li_hidden_field_ul).addClass(new_class);
			jQuery(li_hidden_field_ul).html(ul_wrapper_fields_html);

		})
	});
}

/**
 * Fresh table after sort group vertical style
 */
function refresh_table_after_sort_group_vertical_style() {
	jQuery(".sortable-row .row-content").each(function (index_1, el_1) {
		var ntd_child_1 = index_1 + 1;
		jQuery(el_1).find(">ul>li").each(function (index_2, el_2) {
			var ntd_child_2 = index_2 + 1;
			var field_group_id = lasso_helper.get_value_by_data_attr(jQuery(el_2), 'field-group-id');
			var lasso_id = lasso_helper.get_value_by_data_attr(jQuery(el_2), 'lasso-id');
			var ul_wrapper_fields_html = jQuery(el_2).find(">ul.sortable-row-fields").html();

			var li_hidden_field = jQuery(".table-column-heading-sortable >li:nth-child("+ntd_child_2+") > ul.table-column-heading-fields > li:nth-child("+ntd_child_1+")");
			var old_field_group_id = lasso_helper.get_value_by_data_attr(jQuery(li_hidden_field), 'field-group-id');
			var old_class = `table-column-heading-fields-${old_field_group_id}-${lasso_id}`;
			var new_class = `table-column-heading-fields-${field_group_id}-${lasso_id}`;

			var li_hidden_field_ul = jQuery(li_hidden_field).find(">ul");
			jQuery(li_hidden_field_ul).removeClass(old_class);
			jQuery(li_hidden_field_ul).addClass(new_class);
			jQuery(li_hidden_field_ul).html(ul_wrapper_fields_html);
		})
	});
}

/**
 * Apply table pagination
 *
 * @param set_page
 * @returns {*|jQuery|HTMLElement}
 */
function table_paginate(set_page) {
	lasso_helper.set_pagination_cache(lasso_helper.get_page_name(), set_page);
	var pagination = jQuery('.pagination');
	var data = {
		items: parseInt(jQuery('.total-table').text()),
		displayedPages: 3,
		itemsOnPage: 10,
		cssStyle: 'light-theme',
		prevText: '<i class="far fa-angle-double-left"></i> Previous',
		nextText: 'Next <i class="far fa-angle-double-right"></i>',
		onPageClick: function(pageNumber, event) {
			lasso_helper.set_pagination_cache(lasso_helper.get_page_name(), pageNumber);
			lasso_helper.remove_page_number_out_of_url();
			get_search_list_table( pageNumber );

		}
	};

	if(set_page > 0) {
		data.currentPage = set_page;
	}
	pagination.pagination(data);

	return pagination;
}

// COPY SHORTCODE
function copy_shortcode() {
	// ANIMATE CLICK
	jQuery('#copy-shortcode').addClass('animate-bounce-in').delay(500).queue(function(){
		jQuery(this).removeClass('animate-bounce-in').dequeue();
	});

	jQuery('#copy-shortcode').attr('data-tooltip', 'Copied!');

	var copyText = document.getElementById("shortcode");

	copyText.select();
	copyText.setSelectionRange(0, 99999); /*For mobile devices*/

	document.execCommand("copy");
}

// SHOW SAVING SIGN
function show_saving_sign() {
	jQuery('.saved-item').addClass('d-none');
	jQuery('.saving-item').removeClass('d-none');
}

// SHOW SAVED SIGN
function show_saved_sign() {
	jQuery('.saved-item').removeClass('d-none');
	jQuery('.saving-item').addClass('d-none');
}

// Clone a table by table IDclass-lasso-setting.php
function clone_table(table_id) {
	var table_list = jQuery(".table-list");
	var data_post = {
		action: 'lasso_get_table_comparison_view',
		sub_action: 'clone_table',
		table_id: table_id,
	};
	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: data_post,
		beforeSend: function() {
			jQuery('.image_loading').removeClass('d-none');
			table_list.hide();
		}
		})
		.done(function (res) {
			if (typeof res.data === 'undefined') {
				return;
			}

			res = res.data;
			var url_page_hash = window.location.href;
			var page_hash = url_page_hash.match(/#page-\d/g);
			if ( page_hash !== null ) {
				url_page_hash = url_page_hash.replace(/#page-\d+/g, "#page-"+res.current_page);
			} else {
				url_page_hash = url_page_hash + "#page-" + res.current_page
			}
			window.history.replaceState( null, null, new URL( url_page_hash ) );
			get_search_list_table(res.current_page, 10, false);
		})
		.fail(function (xhr, status, error) {
			lasso_helper.display_ajax_error(xhr);
			table_list.show();
		});
}

/**
 * Set data description
 *
 * @returns {Promise<void>}
 */
async function init_description(data) {
	jQuery('#field-description-editor-modal').html(data.content);
	jQuery('#btn-description-save').data({
		'field-id': data.field_id,
		'lasso-id': data.lasso_id,
		'field-group-id': data.field_group_id,
		'table-id': data.table_id
	});
}

/**
 * Init Quill editor WYSIWYG
 *
 * @returns {Promise<void>}
 */
async function init_quill() {

	// // FOR DESCRIPTION RICH EDITOR
	// // ADD OPTIONS FOR EDITOR TOOLBAR
	let toolbar_options = [
		['bold',
			'italic',
			'underline',
			'strike'],

		['link',
			{ 'list': 'bullet' }],

		[{ 'color': [] }, { 'background': [] }],

		['clean'],
	];

	// SET THEME, PLACEHOLDER, AND TOOLBAR OPTIONS
	let description_selector = '#field-description-editor-modal';
	let quill_options		 = {theme: 'snow', placeholder: 'Enter a description', modules: {toolbar: toolbar_options, clipboard: {matchVisual: false}}};


	// INITIALIZE QUILL
	quill = new Quill(description_selector, quill_options);

	// Fix error when bold format is link
	quill.on('editor-change', function(eventName, ...args) {
		if ('selection-change' === eventName) {
			quill.update();
		}
	});

	// RECREATE HOVER EFFECT ON DESCRIPTION BOX
	jQuery('.ql-editor').focus(
		function(){
			jQuery(this).parent('div').attr('style', 'border-color: var(--lasso-light-purple) !important');
		}).blur(
		function(){
			jQuery(this).parent('div').removeAttr('style');
		});

	return quill;
}

/**
 * Handle method when click button edit description
 *
 * @param el
 * @returns {Promise<void>}
 */
async function handle_description_editor(el) {
	let data = await get_data_description(el);
	await init_description(data);
	await init_quill();
}

/**
 * Get data necessary
 *
 * @param el
 * @returns {Promise<{field_id: (*|jQuery), lasso_id: (*|jQuery), field_group_id: (*|jQuery), table_id, content: (*|jQuery)}>}
 */
async function get_data_description(el) {
	return {
		content: jQuery(el).closest('div.url-details-field-box').find('.description-content').html(),
		field_id: jQuery(el).data('field-id'),
		lasso_id: jQuery(el).data('lasso-id'),
		table_id: lasso_helper.get_value_by_name("table_id"),
		field_group_id: jQuery(el).closest('li[data-field-group-id^="group_"]').data('field-group-id'),
	};
}

// Show modal confirm to delete table
function click_to_show_delete_table_modal() {
	let table_id = jQuery('#table_id').val();
	let modal = jQuery('#' + modal_confirm_delete_table.get_modal_id());
	let modal_cancel_btn = modal.find('button[data-dismiss="modal"]');
	let modal_ok_btn = modal.find('.btn-ok');
	let table_name = lasso_helper.get_value_by_data_attr(jQuery(this), 'name');

	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: {
			"action": 'lasso_get_table_location_count',
			"table_id": table_id
		},
	})
		.done(function (res) {
			if (typeof res.data === 'undefined') {
				return;
			}

			res = res.data;
			let location_count = res.location_count;
			// Ready to delete table
			if( location_count == 0 ) {
				let heading = 'Delete "' + table_name + '"';
				modal_ok_btn.text('OK').show();
				modal_cancel_btn.text('Cancel').addClass('cancel-btn');
				modal_confirm_delete_table.set_heading( heading );
				modal_confirm_delete_table.set_description('If deleted, you won\'t be able to get its back.');
				modal_confirm_delete_table.show();
			// Show warning modal when the table is using in posts
			} else {
				modal_cancel_btn.removeClass('cancel-btn'); // Remove white button css to show default green button
				modal_cancel_btn.text('Got It');
				modal_ok_btn.hide();
				modal_confirm_delete_table.set_heading( 'Hold Up' );
				modal_confirm_delete_table.set_description('You can\'t delete a Table if it is in use on your site. Remove this Table from your content first.');
				modal_confirm_delete_table.show();
			}
		})
		.fail(function (xhr, status, error) {
			lasso_helper.display_ajax_error(xhr);
		});

	return false;
}

// Delete a comparison table
function delete_table( table_id ) {
	let btn_ok = jQuery('#' + modal_confirm_delete_table.get_modal_id()).find('.btn-ok');
	var data_post = {
		action: 'lasso_delete_table',
		table_id: table_id,
	};
	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: data_post,
		beforeSend: function() {
			lasso_helper.add_loading_button(btn_ok, '' );
		}
	})
		.done(function (res) {
			if (typeof res.data === 'undefined') {
				return;
			}
			
			res = res.data;
			if( res.status ) {
				modal_confirm_delete_table.hide();
				window.location.href = "/wp-admin/edit.php?post_type=lasso-urls&page=tables";
			} else {
				modal_confirm_delete_table.set_heading( res.msg );
				modal_confirm_delete_table.set_description('');
				setTimeout(function () {
					modal_confirm_delete_table.hide();
				}, 3000);
			}
		})
		.fail(function (xhr, status, error) {
			lasso_helper.display_ajax_error(xhr);
		});
}

/**
 *
 * @param e Event keyup
 */
function search_tables_list (e) {
	if (e.which === enter_key_code) {
		let input_search_tables = jQuery(e.target);
		let search_key = input_search_tables.val();
		search_key = search_key ? search_key.replace(/[^a-zA-Z0-9\+\-\_\.\#\u4e00-\u9fff]/g, ' ').trim() : search_key;
		lasso_helper.update_url_parameter('search', search_key);
		input_search_tables.focusout();
		get_search_list_table();
	}
}

/**
 * Open media popup
 */
function select_product_image() {
	if ( ! wp || ! wp.hasOwnProperty('media') || typeof wp.media !== 'function') {
		console.warn('Lasso cannot load WP media JS');
	}

	let custom_uploader = wp.media({
		title: 'Select an Image',
		multiple: false,
		library: { type : 'image' },
		button: { text: 'Select Image' }
	});
	let img_container = jQuery(this);

	if(custom_uploader) {
		// When a file is selected, grab the URL
		custom_uploader.on('select', function() {
			let attachment = custom_uploader.state().get('selection').first().toJSON();
			let field_input = img_container.find('input.field_value');
			img_container.find('img').attr('src', attachment.url);
			field_input.val(attachment.url);
			img_container.find('.image_loading').addClass('d-none');
			update_field(field_input);
		});

		custom_uploader.open();
		img_container.find('.image_loading').removeClass('d-none');
	}
}

/**
 * Get list table
 *
 * @param page_number
 * @param limit
 * @param is_loading
 */
function get_search_list_table( page_number, limit = 10, is_loading = true ) {
	let search_term = lasso_helper.get_url_parameter( 'search' );
	search_term = search_term ? search_term : '';

	let data_post = {
		action: 'lasso_get_search_list_table',
		search_term: search_term,
		page_number: page_number,
		limit: limit
	};

	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: data_post,
		beforeSend: function (xhr) {
			if (is_loading) {
				jQuery('.image_loading').html(get_loading_image());
				jQuery("#tables").html("");
				jQuery('.image_loading').removeClass('d-none');
				jQuery("#tables").hide();
			}
		}
	})
		.done(function (res) {
			if (typeof res.data === 'undefined') {
				return;
			}

			res = res.data;
			jQuery("#tables").html(res.html);
			jQuery("#tables").show();
			jQuery(".total-table").text(res.total_table);
		})
		.fail(function (xhr, status, error) {
			if (xhr.lasso_error) {
				error = xhr.lasso_error;
			}
			lasso_helper.errorScreen(error);
		})
		.always(function (res) {
			if (typeof res.data === 'undefined') {
				return;
			}
			
			res = res.data;
			jQuery('.image_loading').addClass('d-none');
			let page = typeof res !== undefined ? res.page : 1;
			table_paginate(page);
		});
}

/**
 * Open the saving button url modal
 */
function click_btn_edit_button_info() {
	let el = jQuery(this);
	let edit_btn_modal = jQuery('#table-edit-button-field-modal');
	let field_group_detail_id = el.data('field-group-detail-id');
	let field_name = el.data('field-name');
	let field_value = el.data('field-value').replace(/_double_quote_/gm, '"');
	field_value = JSON.parse(field_value);
	let field_id = el.data('field-id');
	if( field_id === 99993 ) {
		edit_btn_modal.find('input[name="table_edit_btn_url"]').prop('disabled', false);
		edit_btn_modal.find('input[name="table_edit_btn_url"]').val(field_value.url);
	} else {
		let primary_public_link = el.data('public-link');
		edit_btn_modal.find('input[name="table_edit_btn_url"]').val(primary_public_link).prop('disabled', true);
	}

	edit_btn_modal.find('#modal-title').text('Edit "'+ field_name +'"');
	edit_btn_modal.find('input[name="table_edit_field_group_detail_id"]').val(field_group_detail_id);
	edit_btn_modal.find('input[name="table_edit_btn_text"]').val(field_value.button_text);
	edit_btn_modal.modal("show");

	return false;
}

/**
 * Submit save button field information
 */
function submit_edit_field_button() {
	let edit_btn_modal = jQuery('#table-edit-button-field-modal');
	let form           = jQuery(this);
	let field_group_detail_id = form.find('input[name="table_edit_field_group_detail_id"]').val();
	let button_text    = form.find('input[name="table_edit_btn_text"]').val();
	let button_url     = form.find('input[name="table_edit_btn_url"]').val();
	let save_btn       = form.find('.save-tb');
	let error_elm      = form.find('#table-edit-button-error');
	let error_msg      = null;

	error_elm.text(''); // Reset error text

	// Validate data
	if ( ! field_group_detail_id ) {
		error_msg = 'Missing the "Field Group Detail Id" information';
	} else if ( ! button_text ) {
		error_msg = 'Missing the "Button Text" information';
	} else if ( ! button_url ) {
		error_msg = 'Missing the "Button URL" information';
	}

	if (error_msg) {
		error_elm.text(error_msg);
		return;
	}

	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'POST',
		data: {
			action: 'lasso_edit_button_field_information',
			field_group_detail_id: field_group_detail_id,
			button_text: button_text,
			button_url: button_url,
		},
		beforeSend: function() {
			save_btn.prop('disabled', true);
			save_btn.html(get_loading_image_small());
		}
	})
	.done(function (res) {
		edit_btn_modal.modal("hide");
		load_table(null, false);
	})
	.fail(function (xhr, status, error) {
		lasso_helper.display_ajax_error(xhr);
	})
	.always(function() {
		save_btn.prop('disabled', false);
		save_btn.text('Save');
	});

	return false;
}

/**
 * Update badge text
 */
function update_badge_text() {
	let lasso_id = lasso_helper.get_value_by_data_attr(jQuery(this), "lasso-id");
	update_product_badge( lasso_id, jQuery(this).val().trim() );
}

/**
 * Update product mapping - badge
 * @param lasso_id
 * @param badge_text
 */
function update_product_badge( lasso_id, badge_text = null ) {
	var table_id = lasso_helper.get_value_by_name("table_id");
	var lasso_table = jQuery(lasso_table_id_selector);
	var data_post = {
		action: 'lasso_get_table_comparison_view',
		sub_action: 'update_product_mapping',
		table_id: table_id,
		lasso_id: lasso_id,
	};

	if ( badge_text !== null ) {
		data_post.badge_text = badge_text;
	}

	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: data_post,
		beforeSend: function() {
			show_saving_sign();
		}})
		.done(function (res) {
		})
		.fail(function (xhr, status, error) {
			lasso_helper.display_ajax_error(xhr);
			jQuery(lasso_table).show();
		})
		.always(function() {
			// jQuery('.image_loading').addClass('d-none');
			show_saved_sign();
		});
}

/**
 * Show table locations
 */
function click_to_show_modal_table_locations() {
	table_page_state = window.location.href;
	let modal_el = jQuery('#' + modal_show_table_locations.get_modal_id());
	let table_id = jQuery(this).data('table-id');
	let table_locations_current_page = lasso_helper.get_pagination_cache(table_locations_local_store_key, table_id);

	modal_show_table_locations.set_description(get_loading_image(), false);
	modal_show_table_locations.show();

	get_table_locations( modal_el, table_id, table_locations_current_page, false,function (res) {
		modal_show_table_locations.set_description(build_table_location_modal_content(res.datas), false);

		lasso_helper.generate_paging( jQuery('.table-locations-pagination'), res.page, res.count, function (page_number) {
			get_table_locations( modal_el, table_id, page_number, true, function (res) {
				modal_show_table_locations.set_description(build_table_location_modal_content(res.datas), false);
			} );
		}, lasso_helper.link_location_limit);
	});
}

/**
 * Get table locations html
 *
 * @param wrapper_el
 * @param table_id
 * @param page_number
 * @param is_loading
 * @param callback
 */
function get_table_locations( wrapper_el, table_id, page_number = 1, is_loading = false, callback = null ) {
	var data_post = {
		action: 'lasso_get_table_locations',
		table_id: table_id,
		page_number: page_number,
		limit: lasso_helper.link_location_limit
	};
	jQuery.ajax({
		url: lassoOptionsData.ajax_url,
		type: 'post',
		data: data_post,
		async: true,
		beforeSend: function( xhr ) {
			if ( is_loading ) {
				wrapper_el.find('.modal-content p').html(get_loading_image());
			}
		}})
		.fail(function (xhr, status, error) {
			lasso_helper.display_ajax_error(xhr);
			modal_show_table_locations.hide();
		})
		.done(function (res) {
			if (typeof res.data === 'undefined') {
				return;
			}

			res = res.data;
			lasso_helper.set_pagination_cache(table_locations_local_store_key, res.page, table_id);
			lasso_helper.remove_page_number_out_of_url();
			jQuery('.btn-show-table-locations[data-table-id="'+ table_id +'"]')
				.data('total-table-locations', res.count)
				.text(res.count);

			if ( typeof callback === 'function' && res.status === 1 ) {
				return callback(res);
			}
		});
}

/**
 * Build table location content.
 *
 * @param datas
 * @returns {string}
 */
function build_table_location_modal_content( datas ) {
	let html = `
		<div class="table-locations-wrapper">
			<div class="pt-4 pb-1 font-weight-bold dark-gray d-lg-block table-location-heading">
				<div class="row row align-items-center">
					<div class="col-lg-9">
						<div class="d-inline">Content <label data-tooltip="This is the post or page this table was found in."><i class="far fa-info-circle light-purple"></i></label>
						</div>
					</div>
					<div class="col-lg-3 text-right">Action</div>
				</div>
			</div>
	`;

	for(let i in datas) {
		let data = datas[i];
		html += `
			<div class="table-location-row ">
				<div class="row row align-items-center py-2 hover-gray">
					<div class="col-md-9">
						<div class="font-weight-bold">
							<a href="${data.edit_post}" target="_blank" title="${data.post_title}" class="black hover-purple-text">${data.post_title}</a>
							<br/>
							<a href="${data.post_link}" class="dark-gray hover-purple-text small" target="_blank">
								${data.post_link}
							</a>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="px-2 text-right">
							<span class="d-inline">
								<a href="${data.post_link}" target="_blank" ><span><i class="far fa-eye"></i></span></a>
							</span>
						</div>
					</div>
				</div>
			</div>
		`;
	}

	html += `<div class="clearfix mt-3"></div>
		</div>
    `;

	return html;
}

/**
 * Display table detail loading
 *
 * @param bool is_show Is show table detail loading. Default to true.
 */
function display_table_detail_loading(is_show = true) {
	if (is_show) {
		jQuery(lasso_table_id_selector).addClass('no-hint');
		jQuery('#image-loading-wrapper').removeClass('d-none');
	} else {
		jQuery('#image-loading-wrapper').addClass('d-none');
		jQuery(lasso_table_id_selector).removeClass('no-hint');
	}
}
