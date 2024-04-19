jQuery(document).ready(function () {
	if ( jQuery('#link-monetize').length > 0 ) {
		var link_monetize = jQuery('#link-monetize');
		jQuery(link_monetize).on('shown.bs.modal', function () {
			search_attributes("", 1, function ( total_links ) {
				add_lasso_to_group_from_search();
				add_lasso_to_group();
				add_lasso_to_group_from_pagination( total_links );
			});
		});
		jQuery(link_monetize).on('hide.bs.modal', function () {
			jQuery(".js-montize-btn").prop("disabled", false);
		});
	}
});

function add_lasso_to_group_from_pagination( total_links ) {
	var link_monetize = jQuery('#link-monetize');
	// pagination
	var popup_pagination = link_monetize.find('.pagination').first();
	popup_pagination.pagination('destroy');
	link_monetize.find('.js-pagination-popup').first().pagination({
		items: total_links,
		itemsOnPage: 6,
		cssStyle: 'light-theme',
		prevText: '<i class="far fa-angle-double-left"></i> Previous',
		nextText: 'Next <i class="far fa-angle-double-right"></i>',
		onPageClick: function(pageNumber, event){
			search_key = link_monetize.find('.js-monetize-search').first().val();
			search_attributes(search_key, pageNumber, function () {
				add_lasso_to_group();
			});
		},
		currentPage: 1
	});
}

function add_lasso_to_group_from_search() {
	var js_monetize_search_el = jQuery('.js-monetize-search');
	jQuery(js_monetize_search_el).unbind('keypress');
	jQuery(js_monetize_search_el).first().keypress(function(e) {
		var monetize_search = jQuery(this);
		var key_code = e.keyCode ? e.keyCode : e.which;
		if(key_code == 13) {
			var search_key = monetize_search.val();
			search_attributes(search_key, 1, function () {
				add_lasso_to_group();
			});
		}
	});
}

function add_lasso_to_group() {
	var js_montize_btn_label = "Add Link";
	var js_montize_btn = jQuery(".js-montize-btn");
	jQuery(js_montize_btn).text(js_montize_btn_label);
	jQuery(js_montize_btn).unbind( "click" );
	var link_monetize_modal = jQuery('#link-monetize');

	jQuery(js_montize_btn).click(function () {
		jQuery(js_montize_btn).prop("disabled", true);

		var js_montize_current_btn = jQuery(this);
		jQuery(js_montize_current_btn).prop("disabled", false);
		var lasso_id = lasso_helper.get_value_by_data_attr(jQuery(js_montize_current_btn), "lasso-id");
		var post_id = lasso_helper.get_value_by_id_selector("post_id");
		var post_data = {
			action: 'lasso_add_lasso_to_group',
			post_id: post_id,
			lasso_id: lasso_id
		};
		jQuery.ajax({
			url: lassoOptionsData.ajax_url,
			type: 'post',
			data: post_data,
			beforeSend: function() {
				var html_loading = '<span style="opacity: 0">' + js_montize_btn_label + '</span><i style="position: absolute; left: 0; right: 0; top: 0; bottom: 0; margin: auto" class="far fa-circle-notch fa-spin"></i>';
				jQuery(js_montize_current_btn).html(html_loading);
			}
		})
			.done(function (res) {
				lasso_helper.remove_element(jQuery("#not-found-wrapper"));
				jQuery(link_monetize_modal).modal('hide');
				jQuery(js_montize_current_btn).html(js_montize_btn_label);
				var data = res.data;
				jQuery( "#report-content" ).append(data.html);
				jQuery('#group-badge').text(data.total_links);
			})
			.fail(function (xhr, status, error) {
				jQuery(link_monetize_modal).modal('hide');
				if (typeof xhr.responseJSON === 'undefined') {
					return;
				}
				lasso_helper.errorScreen(xhr.responseJSON.data);
			});
	});
}
