<?php
/**
 * Declare view-js.php file
 *
 * @package view-js
 */

use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;
$page = esc_js( $page ); // phpcs:ignore
?>
<script>
	let interval_timeout_init = 10;
	let init_handle = setInterval(function () {
		// Check lasso_helper load already.
		if ( typeof lasso_helper != "undefined" && typeof jQuery != "undefined"  && typeof jQuery.fn.pagination != "undefined" ) {
			// Clearing the interval.
			clearInterval(init_handle);
			// Call the initializing function init_lasso_view
			init_lasso_view();
		}
	}, interval_timeout_init);

	let page               = lasso_helper.get_page_name();
	let pages_sort_default = {
		'content-opportunities': {
			'order_by': 'post_modified',
			'order_type': 'desc',
		},
		'url-links': {
			'order_by': 'post_title',
			'order_type': 'desc',
		},
	};

	<?php if ( 'dashboard' === $page ) { ?>
		var modal_confirm_delete_links = new lasso_helper.lasso_generate_modal();
		modal_confirm_delete_links.init();
	<?php } ?>

	/**
	 * Init init_lasso_view
	 */
	function init_lasso_view () {
		var monetize_id;
		jQuery(document).ready(function() {
			// At the begin, if search parameter available, set this value to search input
			let search_parameter = get_url_parameter( 'search' );

			if ( search_parameter ) {
				jQuery('#search-links input').val( search_parameter );
			}

			// Form submit disabled
			jQuery('form').submit(function() {
				return false;
			});

			// *************************
			//  Pagination
			// *************************
			var container = jQuery('#report-content');
			var pagination = jQuery('.pagination');
			var search_term = "<?php echo esc_js( $_GET['search'] ?? '' ); // phpcs:ignore ?>";
			var tab_filter = "<?php echo esc_js( $_GET['filter'] ?? '' ); // phpcs:ignore ?>";
			var url_page_number = lasso_helper.get_page_from_current_url();
			var limit = 10;

			if ( 'table-details' === lasso_helper.get_page_name() ) {
				limit = 6;
			}

			function full_list_paginate(set_page) {
				lasso_helper.set_pagination_cache(lasso_helper.get_page_name(), set_page);

				var data = {
					items: jQuery('#total-posts').val(),
					displayedPages: 3,
					itemsOnPage: limit,
					cssStyle: 'light-theme',
					prevText: '<i class="far fa-angle-double-left"></i> Previous',
					nextText: 'Next <i class="far fa-angle-double-right"></i>',
					onPageClick: function(pageNumber, event) {
						var sortable = jQuery('.sortable-col.active');
						var orderBy = '';
						var orderType = '';
						if(sortable) {
							orderBy = sortable.attr('data-order-by');
							orderType = sortable.attr('data-order-type');
						}

						lasso_helper.set_pagination_cache(lasso_helper.get_page_name(), pageNumber);
						lasso_helper.remove_page_number_out_of_url();

						if(['asc', 'desc'].includes(orderType) && orderBy !== '') {
							get_data_via_ajax(pageNumber, limit, orderBy, orderType);
						} else {
							get_data_via_ajax(pageNumber, limit);
						}
					}
				};

				if(set_page > 0) {
					data.currentPage = set_page;
				}
				pagination.pagination(data);

				return pagination;
			}
			full_list_paginate();

			// Sorting
			jQuery('.sortable-col').unbind().click(function(){
				var column = jQuery(this);
				var order_by = column.attr('data-order-by');
				var order_type = column.attr('data-order-type');
				var order_type_init = column.attr('data-order-init');

				if(order_type == "") {
					if(order_type_init) {
						order_type = order_type_init;
					} else {
						order_type = "asc";    
					}
				} else if(order_type == "desc") {
					order_type = "asc";
				} else {
					order_type = "desc";
				}

				// Unset all other sorting columns
				column.siblings().attr('data-order-type', '').removeClass('active');
				column.attr('data-order-type', order_type);
				column.addClass('active');

				get_data_via_ajax(lasso_helper.get_page_from_current_url(), limit, order_by, order_type);
			});

			// *************************
			//  Search
			// *************************
			// Action when click search button
			jQuery('#search-icon').unbind().click(function(){
				get_data_via_ajax(1, limit);
			});

			// TYPE TO ADD TAGS TO SEARCH BAR
			jQuery('#search-links input').off('focusout');
			jQuery('#search-links input')
				.on('focusout', function() { 
					if(this) {
						var txt = this.value.replace(/[^a-zA-Z0-9\+\-\.\#\u4e00-\u9fff]/g,' ');
						search_term = txt.trim();
					}   

					// if(!jQuery('input.js-toggle').hasClass('js-popup-opened')) {
					//     this.focus();
					// }
				})
				.on('keyup', function( e ) {
					// WHEN ENTER IS PRESSED, SEARCH
					if(e.which == 13) {
						jQuery(this).focusout();
						clear_notifications();
						lasso_helper.remove_page_number_out_of_url();
						get_data_via_ajax(1, limit);
					}
				});

			// *************************
			//  Fields
			// *************************
			// Switch between "Add Field" Tabs
			jQuery("#create_from_library_tab").on( "click", function() {
				get_data_via_ajax(1, limit);
			});

			// *************************
			//  Dashboard Report Filters
			// *************************
			jQuery('#total-broken-links-a').unbind().click(function(){
				check_filter("broken-links");
				jQuery("#total-out-of-stock-a").removeClass('active');
				jQuery("#total-opportunities-a").removeClass('active');
				get_data_via_ajax(1, limit);
			});

			jQuery('#total-out-of-stock-a').unbind().click(function(){
				jQuery("#total-broken-links-a").removeClass('active');
				check_filter("out-of-stock");
				jQuery("#total-opportunities-a").removeClass('active');
				get_data_via_ajax(1, limit);
			});

			jQuery('#total-opportunities-a').unbind().click(function(){
				jQuery("#total-broken-links-a").removeClass('active');
				jQuery("#total-out-of-stock-a").removeClass('active');
				check_filter("opportunities");         

				get_data_via_ajax(1, limit);
			});

			jQuery('#show-content').unbind().click(function(){
				tab_filter = jQuery('#content-count').val();

				get_data_via_ajax(1, limit);
			});

			function check_filter(filter) {
				filter_container = "#total-" + filter + "-a";
				if(jQuery(filter_container).hasClass('active')) {
					jQuery(filter_container).blur();
					jQuery(filter_container).removeClass('active');
					tab_filter = "<?php echo esc_js( $_GET['filter'] ?? '' ); // phpcs:ignore ?>";

				} else {
					jQuery(filter_container).addClass('active');
					tab_filter = filter;
				}
			}

			// *************************
			//  Main Report Generation 
			// *************************

			// Get full url data via ajax
			function get_data_via_ajax(page, limit, order_by = undefined, order_type = undefined, container_name = '') {
				var link_type = '<?php echo esc_js( $page ); // phpcs:ignore ?>';
				var t0 = performance.now();
				var no_field_ids = [];
				var page_param = get_url_parameter( 'page' );

				// Push search to url parameter
				if ( 'url-details' !== link_type ) {
					update_url_parameter('search', search_term);
				}

				// Apply filter import plugin
				if ( ['import-urls', 'domain-links'].includes( page_param ) ) {
					tab_filter = get_url_parameter( 'filter' );
				}

				if(container_name == 'custom-fields') {
					container = jQuery('#custom-fields');
					tab_filter = 'url-details';
				} else {
					container = jQuery('#report-content');
				}

				// get field ids are added to the table detail
				if ( 'table-details' === lasso_helper.get_page_name() ) {
					let table_el = jQuery('#lasso-table');
					let table_col = table_el.find('#table-column');
					let table_row = table_el.find('.table-row');

					if ( table_col.length > 0 ) { // column
						// heading
						let heading = table_col.find('.group-heading').first();
						let heading_fields = heading.find('.row.url-details-field-box');
						for (let index = 0; index < heading_fields.length; index++) {
							const element = jQuery(heading_fields[index]);
							if ( element.data('field-id') !== undefined ) {
								no_field_ids.push(element.data('field-id'));
							}

						}

						// non heading
						let non_heading = table_col.find('.sortable-row.ui-sortable > li.row-content > ul > li:first-child');
						let non_heading_fields = non_heading.find('.row.url-details-field-box');
						for (let index = 0; index < non_heading_fields.length; index++) {
							const element = jQuery(non_heading_fields[index]);
							if ( element.data('field-id') !== undefined ) {
								no_field_ids.push(element.data('field-id'));
							}
						}
					} else if ( table_row.length > 0 ) { // row
						// heading
						let heading = table_row.find('.group-heading').first();
						let heading_fields = heading.find('.row.url-details-field-box');
						for (let index = 0; index < heading_fields.length; index++) {
							const element = jQuery(heading_fields[index]);
							if ( element.data('field-id') !== undefined ) {
								no_field_ids.push(element.data('field-id'));
							}
						}

						// non heading
						let non_heading = table_row.find('.sortable_column.ui-sortable > ul.ui-sortable-handle > li:first-child');
						let non_heading_fields = non_heading.find('.row.url-details-field-box');
						for (let index = 0; index < non_heading_fields.length; index++) {
							const element = jQuery(non_heading_fields[index]);
							if ( element.data('field-id') !== undefined ) {
								no_field_ids.push(element.data('field-id'));
							}
						}
					}
				}

				jQuery.ajax({
					url: lassoOptionsData.ajax_url,
					type: 'post',
					data: {
						action: 'lasso_report_urls',
						post_id: '<?php echo esc_js( $_GET['post_id'] ?? '' ); // phpcs:ignore ?>',
						link_type: link_type,
						pageNumber: page,
						pageSize: limit,
						order_by: order_by,
						order_type: order_type,
						search: search_term,
						keyword: '<?php echo esc_js( $_GET['keyword'] ?? '' ); // phpcs:ignore ?>',
						filter: tab_filter,
						no_field_ids: no_field_ids
					},
					beforeSend: function() {
						if(tab_filter != 'url-details') {
							// Loading image
							container.html(get_loading_image());
						}

						if( -1 === jQuery.inArray(tab_filter, [ "opportunities", "out-of-stock", "broken-links" ] ) ) {
							tab_filter = '';
						}
						pagination.pagination('disable');
					}
				})
				.done(function(response) {
					var t1 = performance.now();
					// console.log("Query response took " + (t1 - t0) + " milliseconds.");
					if(response.success) {
						var post = response.data.post;
						var responseData = response.data;
						let order_icon = (post.order_type == 'asc') ? ' <i class="far fa-caret-up green"></i> ': ' <i class="far fa-caret-down green"></i> ';
						jQuery('div.row.align-items-center').find('.sortable-col svg, .sortable-col i').remove();
						jQuery('div.row.align-items-center').find('.sortable-col[data-order-by="' + post.order_by + '"]')
							.attr('data-order-type', post.order_type).append(order_icon);

						var html = get_html(responseData.data, post);
						container.html(html);

						jQuery('.subsubsub').find('li.active').find('span').text(responseData.total.total);
						try { Intercom("update", {"link_count": responseData.total.link_count}); } catch (error) {}

						<?php if ( 'dashboard' === $page ) { ?>
							jQuery('#total-posts').val(responseData.total.total);
							jQuery('#link-search-input').attr('placeholder', "Search All " + responseData.total.total + " Links");
							jQuery('#total-opportunities').html('<i class="far fa-lightbulb-on"></i> '+numberWithCommas(responseData.total.opportunities));
							jQuery('#total-broken-links').html('<i class="far fa-unlink"></i> '+numberWithCommas(responseData.total.broken_link_count));
							jQuery('#total-out-of-stock').html('<i class="far fa-box-open"></i> '+numberWithCommas(responseData.total.out_of_stock_count));

							try { Intercom("update", {"display_count": responseData.total.display_count}); } catch (error) {}
							try { Intercom("update", {"opportunities": responseData.total.opportunities}); } catch (error) {}
							try { Intercom("update", {"broken_links": responseData.total.broken_link_count}); } catch (error) {}
							try { Intercom("update", {"out_of_stock_links": responseData.total.out_of_stock_count}); } catch (error) {}

							if(responseData.total.opportunities > 0) {
								jQuery('#total-opportunities-li').removeClass('d-none');
							}
							if(responseData.total.broken_link_count > 0) {
								jQuery('#total-broken-links-li').removeClass('d-none');
							}
							if(responseData.total.out_of_stock_count > 0) {
								jQuery('#total-out-of-stock-li').removeClass('d-none');
							}

							//Process to checkboxes
							var lasso_ids_checkbox = [];
							let heading_default = "Delete the selected links?";
							let desc_default ="If deleted, you won't be able to get them back.";
							let btn_confirm_attr = {'class': 'red-bg', 'label': 'Delete'};
							modal_confirm_delete_links.set_btn_ok(btn_confirm_attr)
								.set_heading(heading_default)
								.set_description(desc_default)
								.on_submit(function () {
									process_delete_links()
								})
								.on_show(function () {
									modal_confirm_delete_links
										.set_heading(heading_default)
										.set_description(desc_default)
								})
								.on_cancel(function () {
									modal_confirm_delete_links
										.set_heading(heading_default)
										.set_description(desc_default)
										.set_btn_ok(btn_confirm_attr);
								});

							jQuery( "#report-content .dashboard-row .checkbox-wrapper .form-check-input").change(url_row_process_checkbox);
							jQuery('.btn-clear-selection').click(clear_selection);
							jQuery('.btn-show-modal-confirm-del-links').click(show_modal_confirm_delete);

							function clear_selection() {
								lasso_ids_checkbox = [];
								process_show_text_selection(lasso_ids_checkbox);
								jQuery( "#report-content .dashboard-row .checkbox-wrapper .form-check-input").prop('checked', false);
								jQuery("#report-content .dashboard-row").removeClass('checked');
							}

							function url_row_process_checkbox() {
								let el = jQuery(this);
								let post_id = el.data('post-id');
								let wrapper = el.closest('.dashboard-row');
								if ( el.prop('checked') ) {
									lasso_ids_checkbox.push(post_id);
									jQuery('.links-selected-wrapper').fadeIn();
									wrapper.addClass('checked');
								} else {
									let index = lasso_ids_checkbox.indexOf(post_id);
									if (index > -1) { // only splice array when item is found
										lasso_ids_checkbox.splice(index, 1); // 2nd parameter means remove one item only
									}
									wrapper.removeClass('checked');
								}
								process_show_text_selection(lasso_ids_checkbox);
							}

							function process_show_text_selection( checkboxes_value ) {
								if ( checkboxes_value.length > 0 ) {
									jQuery('#report-content .dashboard-row').addClass('force-checkbox');
								} else {
									jQuery('#report-content .dashboard-row').removeClass('force-checkbox');
								}

								let total_links = checkboxes_value.length;
								if ( total_links === 1 ) {
									jQuery('#total-links-selected').text(total_links + ' link selected');
								} else if ( total_links > 1 ) {
									jQuery('#total-links-selected').text( total_links + ' links selected');
								} else {
									jQuery('.links-selected-wrapper').fadeOut();
								}
							}

							function show_modal_confirm_delete() {
								modal_confirm_delete_links.show();
							}

							function process_delete_links() {
								jQuery.ajax({
									url: lassoOptionsData.ajax_url,
									type: 'post',
									data: {
										action: 'lasso_bulk_remove_links',
										post_ids: lasso_ids_checkbox
									},
									beforeSend: function() {
										lasso_helper.add_loading_button(jQuery('#'+modal_confirm_delete_links.get_modal_id()).find('.btn-ok'));
									}
								})
									.done(function(response) {
										let data = response.data
										if ( data.error === '' ) {
											clear_selection();
											modal_confirm_delete_links.hide();
											get_data_via_ajax(lasso_helper.get_page_from_current_url(), limit);
										} else {
											modal_confirm_delete_links
												.set_heading("")
												.set_description("<span class='text-danger'>" + data.error + "</span>", false)
												.set_btn_ok(btn_confirm_attr);
										}
									});
							}

						<?php } else { ?>
							jQuery('#total-posts').val(responseData.total.total);
							if(jQuery("#js-report-result").length != 0) {
								jQuery('#js-report-result').html(responseData.total.total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + " Found");
							}

						<?php } ?>

						// display view popup
						<?php if ( 'content-links' === $page ) : ?>
							show_display_preview();
						<?php endif ?>

						// monetize popup
						<?php
						if ( in_array(
							$page,
							array(
								'link-opportunities',
								'url-opportunities',
								'keyword-opportunities',
								'content-links',
								'domain-links',
								'url-links',
							),
							true
						) ) :
							?>
							monetize_button_triggered();
							show_monetize_modal_triggered();
							show_display_preview_link();
						<?php endif ?>

						<?php if ( 'groups' === $page ) : ?>
							try { Intercom("update", {"group_count": responseData.total.total}); } catch (error) {}
						<?php endif ?>

						<?php if ( in_array( $page, array( 'link-opportunities', 'url-opportunities', 'keyword-opportunities' ), true ) ) : ?>
							show_dismiss_modal_triggered();
							click_on_dismiss_btn();
						<?php endif ?>

						<?php if ( 'keyword-opportunities' === $page ) : ?>
							show_keywords_modal_triggered();
							show_keywords_modal_dismiss_report_refresh();
						<?php endif ?>

						// import view popup
						<?php if ( 'import-urls' === $page ) : ?>
							init_import_events();
							render_filter_plugin_select(responseData.total.plugins);
						<?php endif ?>

						<?php if ( in_array( $page, array( Lasso_Setting_Enum::PAGE_URL_DETAILS ), true ) ) : ?>
							add_field_to_product();
						<?php endif ?>

						<?php if ( 'post-content-history' === $page ) : ?>
							click_on_post_content_revert_btn();
						<?php endif ?>

						//get_sub_list(link_type);
					} else {
						container.html('Failed to load data.');
					}
				})
				.fail(function(xhr) {
					container.html('Failed to load data.');
				})
				.always(function(res) {
					var page = res && 'object' === typeof res && 'data' in res ? res.data.page : 1;

					// Don't change pagination if get custom fields of Lasso url
					if(container_name != 'custom-fields') {
						full_list_paginate(page);
					}

					pagination.pagination('enable');
				});
			}

			// Initial get data
			if( jQuery('#custom-fields').length ) {
				get_data_via_ajax(url_page_number, limit, undefined, undefined, 'custom-fields');
			} else if ('<?php echo Lasso_Setting_Enum::PAGE_TABLE_DETAILS; // phpcs:ignore ?>' !== '<?php echo $page; // phpcs:ignore ?>') { // should not auto call request in table details page
				let order_by   = pages_sort_default[page] ? pages_sort_default[page].order_by : undefined;
				let order_type = pages_sort_default[page] ? pages_sort_default[page].order_type : undefined;
				get_data_via_ajax(url_page_number, limit, order_by , order_type, '');
			}

			// Get html for full url data
			function get_html(data, post) {
				var html = '';
				var default_image = '';
				if(data.length > 0) {
					for (let index = 0; index < data.length; index++) {
						const element = data[index];
						var collapse_id = index;
						var icon = (element.count > 0) ? '<i class="fas fa-caret-down green"></i>' : '';
						<?php if ( 'dashboard' === $page ) { ?>
						var categories = (element.categories).join(', ');
						<?php } ?>
						var image_url = (!element.thumbnail) ? default_image : element.thumbnail;
						var image = `<img alt="${ element.post_title }" src="${ image_url }" loading="lazy" class="rounded border" width="48" height="48" />`;

						var display_type = 'text-link';
						if(element.link_report_tooltip == 'a text link') {
							display_type = 'text-link';
						} else if(element.link_report_tooltip == 'a image link') {
							display_type = 'image-link';
						} else if(element.link_report_tooltip == 'a keyword mention') {
							display_type = 'keyword-link';
						}

						if(element.amazon_url) {
							element.link_slug = element.amazon_url;
						}

						var type = `<a class="lasso-list-btn">${ element.type }</a>`;
						var status = `<a class="lasso-list-btn">${ element.status }</a>`;
						var create_link = `<a href="${ element.link_slug }" target="_blank" class="pl-2"><i class="far fa-external-link-alt green"></i></a><a class="trash-lasso-modal pl-2" href="#"><i class="far fa-trash-alt red"></i></a>`;
						var link_count = parseInt(element.count);
						var toggle_checked = element.link_report_color == 'green' ? 'checked' : '';

						<?php if ( 'dashboard' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/url-row.php'; ?>`;

						<?php } elseif ( 'url-links' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/link-display-row.php'; ?>`;

						<?php } elseif ( in_array( $page, array( Lasso_Setting_Enum::PAGE_URL_DETAILS, Lasso_Setting_Enum::PAGE_TABLE_DETAILS ), true ) ) { ?>
						if(post.filter !== undefined && post.filter == 'url-details') {
							html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/field-details-row.php'; ?>`;
						} else {
							html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/field-row.php'; ?>`;
						}

						<?php } elseif ( 'program-opportunities' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/program-opportunity-row.php'; ?>`;

						<?php } elseif ( 'domain-opportunities' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/domain-row.php'; ?>`;

						<?php } elseif ( 'content-opportunities' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/content-opportunity-row.php'; ?>`;

						<?php } elseif ( 'content-links' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/content-link-row.php'; ?>`;

						<?php } elseif ( 'link-opportunities' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/link-opportunity-row.php'; ?>`;

						<?php } elseif ( 'url-opportunities' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/url-opportunity-row.php'; ?>`;

						<?php } elseif ( 'keyword-opportunities' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/keyword-opportunity-row.php'; ?>`;

						<?php } elseif ( 'groups' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/group-row.php'; ?>`;

						<?php } elseif ( 'group-urls' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/group-url-row.php'; ?>`;

						<?php } elseif ( 'domain-links' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/domain-link-row.php'; ?>`;

						<?php } elseif ( 'import-urls' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/import-url.php'; ?>`;

						<?php } elseif ( 'fields' === $page ) { ?>
							html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/field-row-count.php'; ?>`;

						<?php } elseif ( 'field-urls' === $page ) { ?>
							html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/field-url-row.php'; ?>`;

						<?php } elseif ( 'post-content-history' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/post-content-history-row.php'; ?>`;

						<?php } else { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/link-row.php'; ?>`;

						<?php } ?>
					}
				} else {
					jQuery('#js-report-result').html("0 Found");

					<?php if ( 'keyword-opportunities' === $page ) { ?>
						html = `<?php include LASSO_PLUGIN_PATH . '/admin/views/education/keywords-row.php'; ?>`;

					<?php } elseif ( 'dashboard' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/education/dashboard.php'; ?>`;

					<?php } elseif ( 'link-opportunities' === $page || 'url-opportunities' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/education/link-opportunities-row.php'; ?>`;

					<?php } elseif ( 'groups' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/education/groups.php'; ?>`;

					<?php } elseif ( 'url-links' === $page ) { ?>
						html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/education/locations.php'; ?>`;

					<?php } elseif ( 'url-details' === $page ) { ?>
						html += ``;

					<?php } else { ?>
						html = `
							<div class="row align-items-center" id="not-found-wrapper">
								<div class="col text-center p-5 m-5">
									<i class="far fa-skull-cow fa-7x mb-3"></i>
									<h3>Looks like we're coming up empty.</h3>
								</div>
							</div>
						`;
					<?php } ?>
				}

				return html;
			}

			function formatNumber(num) {
				return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
			}

			function numberWithCommas(x) {
				return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			}

			function monetize_button_triggered() {
				var toggle = jQuery('input.js-toggle');
				toggle.unbind('change');
				toggle.change(function(e) {
					var checkbox = jQuery(this);
					var is_checked = this.checked;
					var row = checkbox.closest('.row');
					var link = row.find('a').first().text();
					var is_amazon_link = link.includes('www.amazon.') || link.includes('//amazon.') || link.includes('amzn.') ? true : false;
					var keyword_location_id = checkbox.data('keyword-location-id');
					var monetize_link_id = checkbox.data('link-id');

					jQuery('.js-toggle').removeClass('recent-select');
					checkbox.addClass('recent-select');

					if(is_checked) {
						monetize_id = monetize_link_id;
						if(is_amazon_link) { // amazon link
							monetize_amazon_link(checkbox);
						} else { // normal link
							jQuery('#link-monetize').modal('show');
							checkbox.addClass('js-popup-opened');
						}
					} else { // User add monetize link and then uncheck the keyword checkbox.
						if ( keyword_location_id ) {
							unmonetize_keyword(keyword_location_id, checkbox);
						} else {
							unmonetized(checkbox);
						}
					}
				});
			}

			function unmonetize_keyword(keyword_location_id, checkbox) {
				var row = checkbox.closest('.row');
				var keyword = checkbox.data('old-keyword');
				var post_id = checkbox.data('post-id');


				jQuery.ajax({
					url: lassoOptionsData.ajax_url,
					type: 'post',
					data: {
						action: 'lasso_unmonetized_keyword',
						keyword: keyword,
						post_id: post_id,
						keyword_location_id: keyword_location_id,
					},
					beforeSend: function() {
						clear_notifications();
						// REMOVE TOGGLE AND SHOW LOADER WHILE AMAZON LINK IS MONETIZING
						row.find('.toggle').addClass('d-none');
						row.find('.toggle').closest('div').append('<div class="loader-small"></div>');
					}
				})
				.done(function(res) {
					res = res.data;
					if(res.status) {
						lasso_helper.successScreen("Keyword unmonetized.");
						checkbox.data('link-type', 'keyword');
						row.find('a').first().text(keyword).removeAttr('href');
					} else {
						lasso_helper.errorScreen("Unmonetized keyword failed, please try again.");
						checkbox.prop('checked', true);
					}
				})
				.always(function() {
					row.find('.toggle').removeClass('d-none');
					row.find('.loader-small').remove();
				});
			}

			function monetize_amazon_link(checkbox) {
				var row = checkbox.closest('.row');
				var link = row.find('a').first().text();

				jQuery.ajax({
					url: lassoOptionsData.ajax_url,
					type: 'post',
					data: {
						action: 'monetize_amazon_link',
						link: link,
						post_id: checkbox.data('post-id'),
						link_location_id: checkbox.data('link-id'),
					},
					beforeSend: function() {
						// REMOVE TOGGLE AND SHOW LOADER WHILE AMAZON LINK IS MONETIZING
						row.find('.toggle').addClass('d-none');
						row.find('.toggle').closest('div').append('<div class="loader-small"></div>');
					}
				})
				.done(function(res) {
					res = res.data;

					if(res.status == 1) {
						lasso_segment_tracking('Monetize Amazon Link', {
							lasso_id: res.post_id,
							link: link
						});
						var row = checkbox.closest('div.row');
						var columns = row.children();
						row.find('a').first().text(res.permalink).attr('href', res.permalink);
						flash_background_color(row.parent(), 'green');
					} else {
						checkbox.trigger('click');
					}
				})
				.always(function() {
					row.find('.loader-small').remove();
					row.find('label').removeClass('d-none');
				});
			}

			// show display preview link
			function show_display_preview_link() {
				jQuery('#link-preview').on('show.bs.modal', function(e) {
					var popup = jQuery(e.target);
					var icon = jQuery(e.relatedTarget);
					var wrapper = icon.closest('.text-break.hover-gray');
					var display = wrapper.find('.js-link-preview-html');
					var display_html = display.html();
					var display_type = display.data('type');

					popup.find('.' + display_type).html(display_html).removeClass('d-none');
				}).on('hide.bs.modal', function(e) {
					var popup = jQuery(e.target);
					var icon = jQuery(e.relatedTarget);
					var wrapper = icon.closest('.text-break.hover-gray');
					var display = wrapper.find('.js-link-preview-html');
					var display_type = display.data('type');

					popup.find('.js-preview').addClass('d-none');
				});
			}

			// show display preview
			function show_display_preview() {
				jQuery('#display').on('show.bs.modal', function(e) {
					var popup = jQuery(e.target);
					var icon = jQuery(e.relatedTarget);
					var wrapper = icon.closest('.text-break.hover-gray');
					var lasso_id = wrapper.data('lasso-id');
					var display_preview = wrapper.find('.js-display-preview');
					var display_html = display_preview.html();

					popup.find('.js-preview-display').html(display_html);
				}).on('hide.bs.modal', function(e) {
					var popup = jQuery(e.target);
					var modal_content = popup.find('.modal-content').first();
				});
			}

			// unmonetized link
			function unmonetized(checkbox) {
				var link_location_id = checkbox.data('link-id');
				var link_type = checkbox.data('link-type');
				var post_id = checkbox.data('post-id');
				var keyword = checkbox.data('old-keyword');
				jQuery.ajax({
					url: lassoOptionsData.ajax_url,
					type: 'post',
					data: {
						action: 'lasso_unmonetized_link',
						link_location_id: link_location_id,
						post_id: post_id,
						link_type: link_type,
						keyword: keyword,
					}
				})
				.done(function(res) {
					res = res.data;
					lasso_segment_tracking('Link Unmonetized', {
						lasso_id: post_id
					});
					var original_link_slug = res.original_link_slug;

					var row = checkbox.closest('div.row');
					var columns = row.children();
					columns.eq(0).find('a').first().text(original_link_slug).attr('href', original_link_slug);
					flash_background_color(row.parent(), 'purple');
				});
			}

			// when the popup is show, load data via ajax
			function show_dismiss_modal_triggered() {    
				jQuery('.js-dismiss-opportunity').unbind().click(function() {
					var link_type = jQuery(this).data('link-type');
					var link_id = jQuery(this).data('link-id');

					jQuery('#dismiss-opportunity').modal('show');                
					jQuery('.js-dismiss-opportunity-btn').data('link-type', link_type);
					jQuery('.js-dismiss-opportunity-btn').data('link-id', link_id);
				});
			}

			function show_keywords_modal_dismiss_report_refresh() {
				var keyword_popup = jQuery('#saved-keywords');

				keyword_popup.unbind('hide.bs.modal');
				keyword_popup.on('hide.bs.modal', function (e) {                
					get_data_via_ajax(1, limit);
				});
			}

			// When the user clicks to import an individual link
			function click_on_dismiss_btn() {
				jQuery('.js-dismiss-opportunity-btn').unbind().click(function() {
					var link_type = jQuery(this).data('link-type');
					var link_id = jQuery(this).data('link-id');
					var dismiss_btn = jQuery('.js-dismiss-opportunity[data-link-id="'+link_id+'"]');

					// Dismiss Opportunity
					jQuery.ajax({
						url: lassoOptionsData.ajax_url,
						type: 'post',
						data: {
							action: 'lasso_dismiss_opportunity',
							link_type: link_type,
							link_id: link_id,
						}
					})
					.done(function(res) {
						res = res.data;
						if(res.status) {
							lasso_segment_tracking('Dismiss Opportunity Clicked', {
								link_id: link_id
							});
							var row = dismiss_btn.closest('div.row');
							var columns = row.children();

							jQuery('#dismiss-opportunity').modal('hide');
							flash_background_color(row.parent(), 'purple');
							row.parent().addClass("collapse");
						} else {
							jQuery('#dismiss-opportunity').modal('hide');
							lasso_helper.errorScreen("Dismiss failed.");
						}
					});
				});
			}

			<?php
			if ( in_array( $page, array( 'link-opportunities', 'domain-opportunities', 'keyword-opportunities', 'content-opportunities' ), true )
				&& ! ( isset( $lasso_options['general_disable_tooltip'] ) && 1 === intval( $lasso_options['general_disable_tooltip'] ) ) ) {
				?>
				close_education_box('<?php echo $page; // phpcs:ignore ?>');
			<?php } ?>

			function close_education_box(page) {
				jQuery('.close-' + page).unbind().click(function() {
					jQuery.ajax({
						url: lassoOptionsData.ajax_url,
						type: 'post',
						data: {
							action: 'lasso_hide_education_box',
							education_page: page,
						},
						beforeSend: function() {
							jQuery('.' + page + '-box').addClass('d-none');
						}
					})
					.done(function(res) {
						res = res.data;
						lasso_segment_tracking('Hide Education Box', {
							education_page: page
						});
						console.log(res);
					});
				});
			}

			// All add related actions - lives here because it's on every page
			jQuery('#add_keyword_button').unbind().click(function() {
				add_keyword();
			});

			jQuery('#add_keyword_text').off().on('keyup', function( e ) {
				// WHEN ENTER IS PRESSED, SEARCH
				if(e.which == 13) {
					jQuery(this).focusout();
					add_keyword();
				}
			});

			function add_keyword() {
				clear_notifications();
				let addKeywordTxtFld = jQuery('#add_keyword_text');
				let addKeywordBtnFld = jQuery('#add_keyword_button');
				if(addKeywordTxtFld.val() != "") {
					//Animate Add Keyword
					addKeywordTxtFld.prop('disabled', true);
					addKeywordBtnFld.prop('disabled', true);
					addKeywordBtnFld.html(get_loading_image_small());

					console.log("Waiting...");

					jQuery.ajax({
						url: lassoOptionsData.ajax_url,
						type: 'post',
						data: {
							action: 'lasso_add_keyword',
							keyword: addKeywordTxtFld.val().trim()
						}
					})
					.done(function(res, textStatus, jqXHR) {
						lasso_segment_tracking('Keyword Added', {
							keyword: addKeywordTxtFld.val()
						});
						lasso_helper.successScreen("", "save-success", "keyword");
						// Refresh Report
						get_data_via_ajax(1, limit);                    
					})
					.fail(function(jqXHR, textStatus, errorThrown){
						if (!jqXHR.hasOwnProperty('responseJSON')) {
							return;
						}

						data = jqXHR.responseJSON.data
						if (jqXHR.status == 409) {
							lasso_helper.errorScreen(data);
						} else {
							lasso_helper.errorScreen(data, "save-fail", "keyword");
						}
					})
					.always(function(){
						// Reset UI
						addKeywordTxtFld.val("");
						addKeywordTxtFld.prop('disabled', false);
						addKeywordBtnFld.prop('disabled', false);
						addKeywordBtnFld.html("Add Keyword");  
					});
				} else {
					lasso_helper.warningScreen("You didn't enter a keyword");
				}   
			}

			function add_field_to_product() {
				jQuery('.js-create-field').unbind().click(function() {
					var title = jQuery("#field-title").val();
					var type = jQuery("#field-type-picker").val();
					var description = jQuery("#field-description").val();
					jQuery('.js-create-field').html(get_loading_image_small());
					console.log("Creating...");

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
						res = res.data;
						jQuery("#create_from_library_tab").trigger("click");
						jQuery('.js-create-field').html("Create Field");

						if(res.status) {
							// Show in URL Details
							get_data_via_ajax(1, limit);
							console.log("Created.");
							jQuery("#field-title").val("");
							jQuery("#field-description").val("");
						} else {
							// Alert to failure
							console.log("Failed!");
							jQuery('#create_new_tab').trigger('click');
						}
					});
				});
			}

			// *************************
			//  Import Page
			// *************************
			jQuery(document)
				.on('change', '#filter-plugin', filter_import_plugin_change);

			function filter_import_plugin_change() {
				var selected_value = jQuery(this).val();

				update_url_parameter('filter', selected_value); // Push filter to url parameter
				clear_notifications();
				lasso_helper.remove_page_number_out_of_url();
				get_data_via_ajax(1, limit);
			}

			function render_filter_plugin_select(plugins) {
				var filter_plugin_select = jQuery('#filter-plugin');
				var options              = '<option value="">All Plugins</option>';

				plugins.forEach(function(plugin_source) {
					var selected = plugin_source == get_url_parameter( 'filter' ) ? 'selected' : '';
					options += '<option '+ selected +' value="' + plugin_source + '">' + plugin_source + '</option>';
				});

				filter_plugin_select.html(options);
			}
		});

		/**
		 * Get url parameter by key
		 *
		 * @param key
		 * @returns {string}
		 */
		function get_url_parameter( key ) {
			let url = new URL( window.location.href );
			return url.searchParams.get( key );
		}

		/**
		 * Update url parameter by key and value, delete parameter if value is empty
		 *
		 * @param key
		 * @param value
		 */
		function update_url_parameter( key, value ) {
			let url = new URL( window.location.href );

			if ( value ) {
				url.searchParams.set( key, value );
			} else {
				url.searchParams.delete( key );
			}

			window.history.replaceState( null, null, url );
		}
	}
</script>
