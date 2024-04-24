<?php

/**
 * Modal
 *
 * @package Modal
 */
/** @var bool $is_from_editor */
?>

<?php if (!isset($is_from_editor)) : ?>
	<!-- URL ADD -->
	<div class="modal fade" id="url-add" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content shadow p-5 rounded text-center">

				<form action="edit.php" method="get" id="add_new_form" autocomplete="off">
					<!-- TARGET URL -->
					<h2>Add A New Link</h2>
					<p>Enter the affiliate link you would like to track.</p>
					<div class="form-group mb-1">
						<input type="text" name="url" id="add-new-url-box" class="form-control" placeholder="https://www.example.com/affiliate-id">
						<input type="hidden" name="post_type" value="lasso-urls">
						<input type="hidden" name="page" value="url-details">
						<div id="multi-links-preview-wrapper" class="d-none text-left"></div>
						<p class="js-error text-danger my-3"></p>
					</div>
					<div class="text-right mb-3 d-none" id="edit-multi-links-wrapper">
						<span id="edit-multi-links"><i class="far fa-pencil green"></i></span>
					</div>

					<div class="text-right mb-3 d-none" id="max-link-wrapper">
						<small>Remaining links: <span id="max-links">10</span></small>
					</div>
					<div class="progress progress-bar mb-3 d-none" id="progress-bar-multi-links">
						<div id="progress-loading" style="height: 100%" class="progress-bar progress-bar-striped progress-bar-animated progress-bar-purple" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
						</div>
					</div>
					<div class="text-center mb-3"">
					<button class=" btn">
						<i class="far fa-plus-circle"></i> Add Link
						</button>
						<a href="#" class="btn btn-reload d-none">
							<i class="far fa-sync"></i> Reload
						</a>
					</div>
					<small id="multi-links-hint">
						Enter each new link on a separate line (Shift + Enter)
					</small>
				</form>

			</div>
		</div>
	</div>
<?php else : ?>
	<!-- Post Editor -->
	<!-- URL ADD -->
	<div class="lasso-modal fade url-add" id="url-add" tabindex="-1" role="dialog" data-is-from-editor="1">
		<div class="modal-dialog modal-sm" role="document">
			<div class="modal-content shadow p-5 rounded text-center">

				<form action="edit.php" method="get" id="add_new_form" onsubmit="return false;">
					<!-- TARGET URL -->
					<h2>Add A New Link</h2>
					<p>Enter the destination URL your Lasso link will redirect to.</p>
					<div class="form-group mb-4">
						<input type="text" name="url" id="add-new-url-box" class="form-control" placeholder="https://www.example.com/affiliate-id">
						<input type="hidden" name="post_type" value="lasso-urls">
						<input type="hidden" name="page" value="url-details">
						<p class="js-error text-danger my-3"></p>
					</div>
					<div class="text-center">
						<span class="btn btn-lasso-add-link" data-disabled="0">
							<i class="far fa-plus-circle"></i> Add Link
						</span>
					</div>
				</form>

			</div>
		</div>
	</div>
<?php endif; ?>

<script>
	jQuery(document).ready(function() {
		// All add related actions - lives here because it's on every page
		var search_input = jQuery('#link-search-input');
		var add_popup = jQuery('#url-add');
		var save_form = jQuery('#add_new_form');
		var form_link_box = jQuery('#add-new-url-box');
		var save_link_btn = save_form.find('button');
		var btn_lasso_add_link = jQuery('.btn-lasso-add-link');
		var btn_lasso_add_link_clone = jQuery(btn_lasso_add_link).html();
		var save_link_btn_html = save_link_btn.html();
		var js_error = save_form.find('.js-error');
		var edit_page = '<?php echo site_url(); ?>/wp-admin/edit.php?post_type=<?php echo LASSO_POST_TYPE; ?>&page=url-details';
		var dashboard_page = '<?php echo site_url(); ?>/wp-admin/edit.php?post_type=<?php echo LASSO_POST_TYPE; ?>&page=dashboard';
		var is_from_editor = jQuery(add_popup).data('is-from-editor');
		is_from_editor = is_from_editor !== undefined && is_from_editor === 1;
		var is_dashboard = !is_from_editor;
		var bulk_add_links_is_running = '<?php echo (new Lasso_Process_Bulk_Add_Links())->is_process_running() ?>';
		var heartbeat_interval = null;
		var textarea = jQuery("<textarea class='form-control' wrap='off' id='add-new-url-box' rows='7'></textarea>");
		var edit_multi_links_wrapper = jQuery('#edit-multi-links-wrapper');
		var multi_links_preview_wrapper = jQuery('#multi-links-preview-wrapper');
		var max_link_wrapper = jQuery('#max-link-wrapper');
		var progress_bar_multi_links = jQuery('#progress-bar-multi-links');
		var multi_links_hint = jQuery('#multi-links-hint');
		var btn_reload = jQuery('.btn-reload');
		var is_focus_in_textarea = false;
		var max_links_textarea = 10;
		var shift_enter_toggle = 0;
		var multi_links = [];
		var quill;
		var toolbarOptions = [
			[
				'bold',
				'italic',
				'underline',
				'strike'
			],

			[
				'link',
				{
					'list': 'bullet'
				}
			],

			[{
					'color': []
				},
				{
					'background': []
				}
			],
			['clean'],
		];
		var quill_options = {
			theme: 'snow',
			placeholder: 'Enter a description',
			modules: {
				toolbar: toolbarOptions,
				clipboard: {
					matchVisual: false
				}
			}
		};

		var loading_by_font_awesome = typeof get_loading_by_font_awesome == 'undefined' ? '<i class="far fa-circle-notch fa-spin"></i>' : get_loading_by_font_awesome(); // Re-defined for case get_loading_by_font_awesome is not defined
		var url_quick_detail_modal = jQuery("#url-quick-detail");

		var current_page = lasso_helper.get_page_name();
		var allow_pages = ['content-links', 'keyword-opportunities', 'domain-links']; // monetize modals
		var no_redirect = allow_pages.includes(current_page);
		var go_to_detail_modal = is_from_editor || no_redirect;


		function save_lasso_url() {
			js_error.addClass('d-none');
			save_link_btn.data('continue', 0);
			let link = '';
			if (form_add_link_is_text_area()) {
				link = multi_links;
			} else {
				link = form_link_box.val();
			}

			if (link == '' || link.length === 0) {
				js_error.text('URL is incorrect.');
				js_error.removeClass('d-none');
				return;
			}

			jQuery.ajax({
					url: lassoOptionsData.ajax_url,
					type: 'post',
					data: {
						action: 'lasso_add_a_new_link',
						link: link,
					},
					beforeSend: function() {
						save_link_btn.prop('disabled', true);
						form_link_box.prop('disabled', true);
						save_link_btn.html(get_loading_image_small());
						if (btn_lasso_add_link !== undefined) {
							btn_lasso_add_link.data('disabled', 1);
							btn_lasso_add_link.html(loading_by_font_awesome);
						}
						if (form_add_link_is_text_area()) {
							jQuery('#add-new-url-box').prop('disabled', true);
							multi_links_preview_wrapper.addClass('hide-scroll');
							edit_multi_links_wrapper.lassoHide();
							progress_bar_multi_links.lassoShow();
							lasso_helper.setProgress(3, progress_bar_multi_links);
							heartbeat_interval = setInterval(function() {
								jQuery.ajax({
									url: lassoOptionsData.ajax_url,
									type: 'post',
									data: {
										action: 'lasso_heartbeat',
										type: 'bulk_add_links'
									},
									beforeSend: function() {}
								}).done(function(res) {
									let data = res.data;
									if (data.percent === 0) {
										data.percent = 5;
									}
									lasso_helper.setProgress(data.percent, progress_bar_multi_links);
									if (data.percent >= 100) {
										multi_links_preview_wrapper.removeClass('hide-scroll');
										clearInterval(heartbeat_interval);
										window.location.href = dashboard_page;
									}
								});
							}, 3000);
						}
					}
				})
				.done(function(res) {
					if (!form_add_link_is_text_area()) {
						if (res.success) {
							// Track URL Creation Event
							lasso_segment_tracking('Lasso Link Created', {
								link: form_link_box.val()
							});

							if (go_to_detail_modal) {
								load_url_quick_detail(res.data.post['lasso_id']);

							} else {
								add_popup.modal('hide');
								save_link_btn.data('continue', 0);

								if (no_redirect) { //Textbox
									save_link_btn.text('Add Link');
									form_link_box.val('');
								} else if (res.data['is_amazon']) {
									window.location.href = dashboard_page;
								} else {
									let post_id = res.data['post_id'] ? res.data['post_id'] : res.data.post['lasso_id'];
									let lasso_edit_url = edit_page + '&post_id=' + post_id;
									if (res.data['is_duplicate'] !== undefined && res.data['is_duplicate'] === true) {
										lasso_edit_url += '&is_duplicate=true';
									}
									window.location.href = lasso_edit_url;
								}
							}

						} else {
							js_error.text(res.data);
							js_error.removeClass('d-none');

							save_link_btn.text('Continue Anyway');
							save_link_btn.data('continue', 1);

							if (is_from_editor) {
								btn_lasso_add_link.data('disabled', 0);
								btn_lasso_add_link.html(btn_lasso_add_link_clone);
							}

						}
					}
				})
				.error(function(xhr, status, error) {
					js_error.text(error);
					js_error.removeClass('d-none');
				})
				.always(function() {
					if (!form_add_link_is_text_area()) {
						save_link_btn.prop('disabled', false);
						form_link_box.prop('disabled', false);
						save_link_btn.text('Add Link');
					}
				});
		}

		function load_url_quick_detail(lasso_id) {
			jQuery.ajax({
					url: lassoOptionsData.ajax_url,
					type: 'post',
					data: {
						action: 'lasso_get_link_quick_detail',
						lasso_id: lasso_id,
					},
					beforeSend: function() {}
				})
				.done(function(res) {
					// ? Reset the form add link to default behavior
					form_link_box.val("");
					btn_lasso_add_link.data('disabled', 0);
					btn_lasso_add_link.html(btn_lasso_add_link_clone);
					add_popup.modal('hide');

					if (res.success) {
						var data = res.data;
						jQuery('.url-quick-detail-wrapper').html(data.html);
						if (allow_pages.includes(current_page)) {
							let child = jQuery('.url-quick-detail-wrapper').find('.btn-lasso-save-link').children();
							jQuery('.url-quick-detail-wrapper').find('.btn-lasso-save-link').html(child).append(' Save');
						}

						// INITIALIZE QUILL
						quill = new Quill('#description', quill_options);

						// Fix error when bold format is link
						// load_url_quick_detail
						quill.on('editor-change', function(eventName, ...args) {
							if ('selection-change' === eventName) {
								quill.update();
							}
						});

						// RECREATE HOVER EFFECT ON DESCRIPTION BOX
						jQuery('.ql-editor').focus(
							function() {
								jQuery(this).parent('div').attr('style', 'border-color: var(--lasso-light-purple) !important');
							}).blur(
							function() {
								jQuery(this).parent('div').removeAttr('style');
							});

						window.quill = quill;

						// ? url_quick_detail_modal initial when Choose a Display Type is loaded
						url_quick_detail_modal.modal("show");
						jQuery(url_quick_detail_modal).fix_backdrop_elementor();
					}
				})

		}

		save_link_btn.unbind("click").click(function() {
			if (save_link_btn.data('continue') == 1) {
				window.location.href = edit_page + '&url=' + form_link_box.val();
			}
			if (!event.shiftKey) {
				save_lasso_url();
			}
		});
		save_link_btn.on('mousedown', function() {
			if (is_dashboard) {
				var tick = 0;
				var interval = setInterval(function() {
					if (is_focus_in_textarea) {
						is_focus_in_textarea = false;
						clearInterval(interval);
						save_lasso_url();
					} else {
						tick++;
						if (tick === 20) {
							clearInterval(interval);
						}
					}
				}, 50);
			}
		});

		form_link_box.off('change').on('change', function(e) {
			js_error.addClass('d-none');
			save_link_btn.html(save_link_btn_html);
			save_link_btn.data('continue', 0);
		});

		form_link_box.off('paste').on('paste', function(e) {
			js_error.addClass('d-none');
			save_link_btn.html(save_link_btn_html);
			save_link_btn.data('continue', 0);
		});

		form_link_box.off('keypress').on('keypress', function(e) {
			js_error.addClass('d-none');
			save_link_btn.data('continue', 0);

			// WHEN ENTER IS PRESSED, SEARCH
			if (event.which == 13 && !event.shiftKey) {
				jQuery(this).focusout();
				save_lasso_url();
				return false;
			} else {
				save_link_btn.html(save_link_btn_html);
			}
		});

		if (is_from_editor || allow_pages.includes(current_page)) {
			jQuery(btn_lasso_add_link).unbind('click').click(function() {
				if (btn_lasso_add_link.data('disabled') === 0) {
					save_lasso_url();
				}
			});

			jQuery(document).on('keyup', '.affiliate_name', function() {
				jQuery(".product-name").text(jQuery(this).val());
			}).on('click', '.btn-lasso-save-link', function() {
				var btn_lasso_save_link = jQuery(this);
				var btn_lasso_save_link_clone = btn_lasso_save_link.html();
				jQuery.ajax({
						url: lassoOptionsData.ajax_url,
						type: 'post',
						data: {
							action: 'lasso_save_link_quick_detail',
							lasso_id: jQuery("#lasso_id").val(),
							affiliate_name: jQuery("#affiliate_name").val(),
							badge_text: jQuery("#badge_text").val(),
							buy_btn_text: jQuery("#buy_btn_text").val(),
							description: jQuery('#description').find('.ql-editor').html(),
							thumbnail_image_url: jQuery("#thumbnail_image_url").val()
						},
						beforeSend: function() {
							jQuery('.js-error').addClass("d-none");
							btn_lasso_save_link.prop('disabled', true);
							btn_lasso_save_link.html(loading_by_font_awesome);
						}
					})
					.done(function(res) {
						var response = res.data;
						if (response.success) {
							if (allow_pages.includes(current_page)) {
								let toggle_el = jQuery('.js-toggle.recent-select');
								let monetize_id = toggle_el.data('link-id');
								let monetize_row = jQuery('input.js-toggle[data-link-id="' + monetize_id + '"]');
								monetize_row.prop("checked", true).trigger('change');
								url_quick_detail_modal.modal("hide");
								btn_lasso_save_link.prop('disabled', false);
								return;
							}

							if (tab === "single") {
								add_short_code_single_main(btn_lasso_save_link);
							} else if (tab === "button") {
								add_short_code_button_main(btn_lasso_save_link);
							} else if (tab === "image") {
								add_short_code_image_main(btn_lasso_save_link);
							}
							url_quick_detail_modal.modal("hide");
						} else {
							jQuery('.js-error').removeClass("d-none");
							jQuery('.js-error').html(response.msg);
							btn_lasso_save_link.html(btn_lasso_save_link_clone);
							btn_lasso_save_link.prop('disabled', false);
						}
					});
			});
		}
		jQuery(document).on('keydown', function() {
				if (is_dashboard) {
					let keyCode = event.which || event.keyCode;
					if (keyCode === 13 && event.shiftKey) {
						event.preventDefault();
						// Don't generate a new line
						if (shift_enter_toggle === 0 && form_link_box.length === 1) {
							form_link_box.replaceWith(textarea);
							textarea.focus();
							textarea.lassoShow();
							if (parseInt(bulk_add_links_is_running) === 1) {
								btn_reload.lassoShow();
								save_link_btn.lassoHide();
								max_link_wrapper.lassoHide();
								textarea.removeAttr('wrap');
								textarea.prop('disabled', true);
								textarea.attr('rows', 2);
								textarea.attr('placeholder', "Your bulk add links still in running process. Please try again after a few minutes.");
							} else {
								max_link_wrapper.lassoShow();
							}
							//Copy value to textarea
							if (multi_links.length > 0) {
								textarea.val(multi_links.join("\n"));
							} else {
								textarea.val(form_link_box.val());
							}

							shift_enter_toggle = 1;
							multi_links_hint.lassoHide();
						} else {
							do_behavior_for_text_box();
						}
					}
				}
			})
			.on('keyup', '#add-new-url-box', function() {
				if (form_add_link_is_text_area()) {
					let textarea_values = jQuery(this).val();
					textarea_values = textarea_values.split(/\n/);
					let total_links = 0;
					for (let i = 0; i < textarea_values.length; i++) {
						let link = textarea_values[i].trim();
						if (link !== '') {
							total_links++;
						}
					}
					multi_links = textarea_values;
					multi_links.filter(function(item) {
						return item.trim() !== ''
					});
					if (multi_links.length > 10) {
						multi_links = multi_links.splice(0, 10);
					}

					let total = max_links_textarea - total_links;
					if (total <= 0) {
						total = 0;
					}
					jQuery("#max-links").text(total);
				}

			})
			.on('click', '.btn-reload', function() {
				location.reload();
			})
			.on('click', '#edit-multi-links', function() {
				if (form_add_link_is_text_area() && jQuery('#add-new-url-box').val() !== '') {
					textarea.lassoShow();
					max_link_wrapper.lassoShow();
					multi_links_preview_wrapper.lassoHide();
					edit_multi_links_wrapper.lassoHide();
					textarea.focus();
				}
			})
			.on('focusout', '#add-new-url-box', function() {
				if (form_add_link_is_text_area() && jQuery('#add-new-url-box').val() !== '') {
					edit_multi_links_wrapper.lassoShow();
					max_link_wrapper.lassoHide();
					textarea.lassoHide();
					let html = [];
					html.push('<ol>');
					for (let i = 0; i < multi_links.length; i++) {
						if (multi_links[i].trim() !== '') {
							let item_html = '<li><span>' + multi_links[i] + '<span></li>';
							html.push(item_html);
						}
					}
					html.push('</ol>');
					html = html.join('\n');
					multi_links_preview_wrapper.html(html);
					multi_links_preview_wrapper.lassoShow();
					is_focus_in_textarea = true;
					setTimeout(function() {
						is_focus_in_textarea = false;
					}, 1000)
				}
			});

		function form_add_link_is_text_area() {
			return is_dashboard && jQuery('#add-new-url-box').is("textarea") === true
		}

		function do_behavior_for_text_box() {
			if (form_add_link_is_text_area()) {
				btn_reload.lassoHide();
				save_link_btn.lassoShow();
				if (multi_links.length > 0) {
					form_link_box.val(multi_links[0]);
				}
				textarea.replaceWith(form_link_box);
				form_link_box.focus();
				shift_enter_toggle = 0;
				multi_links_hint.lassoShow();
				max_link_wrapper.lassoHide();
				multi_links_preview_wrapper.lassoHide();
				edit_multi_links_wrapper.lassoHide();
			}
		}
	});
</script>