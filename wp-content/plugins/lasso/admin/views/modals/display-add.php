<?php

/**
 * Modal
 *
 * @package Modal
 */

use Lasso\Classes\Launch_Darkly;
?>

<!-- MONETIZE -->
<div id="lasso-display-add" class="lasso-modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content p-0">

			<!-- CHOOSE A DISPLAY TYPE -->
			<div id="lasso-display-type" class="text-center p-5">
				<h2 class="mb-4">Choose a Display Type</h2>
				<div class="row align-items-center">
					<div class="col-3">
						<a id="lasso-single" class="lasso-display-type hover-gray" data-tab="single" data-tab-container="lasso-urls">
							<i class="far fa-pager fa-7x"></i>
							<h3 class="mb-0">Single</h3>
						</a>
					</div>
					<div class="col-3">
						<a id="lasso-grid" class="lasso-display-type hover-gray" data-tab="grid" data-tab-container="lasso-groups">
							<i class="far fa-border-all fa-7x"></i>
							<h3 class="mb-0">Grid</h3>
						</a>
					</div>
					<div class="col-3">
						<a id="lasso-list" class="lasso-display-type hover-gray" data-tab="list" data-tab-container="lasso-groups">
							<i class="far fa-list fa-7x"></i>
							<h3 class="mb-0">List</h3>
						</a>
					</div>
					<div class="col-3">
						<a id="lasso-table" class="lasso-display-type hover-gray" data-tab="table" data-tab-container="lasso-tables">
							<i class="far fa-columns fa-7x"></i>
							<h3 class="mb-0">Table</h3>
						</a>
					</div>
				</div>
				<div class="row align-items-center">
					<div class="col-3">
						<a id="lasso-button" class="lasso-display-type hover-gray" data-tab="button" data-tab-container="lasso-urls">
							<i class="far fa-rectangle-wide fa-7x"></i>
							<h3 class="mb-0">Button</h3>
						</a>
					</div>
					<div class="col-3">
						<a id="lasso-image" class="lasso-display-type hover-gray" data-tab="image" data-tab-container="lasso-urls">
							<i class="far fa-image fa-7x"></i>
							<h3 class="mb-0">Image</h3>
						</a>
					</div>
					<div class="col-3">
						<a id="lasso-gallery" class="lasso-display-type hover-gray" data-tab="gallery" data-tab-container="lasso-groups">
							<i class="far fa-images fa-7x"></i>
							<h3 class="mb-0">Gallery</h3>
						</a>
					</div>

					<div class="col-3">
						<a id="lasso-remind" class="lasso-display-type hover-gray" data-tab="remind" data-tab-container="lasso-urls">
							<i class="far fa-sticky-note fa-7x"></i>
							<h3 class="mb-0">Remind</h3>
						</a>
					</div>

					<!-- Do not show AI block for Lasso Lean -->
					<?php if (!Launch_Darkly::enable_lasso_lean()) : ?>
						<div class="col-3">
							<a id="lasso-ai" class="lasso-display-type hover-gray" data-tab="aitext" data-tab-container="lasso-aitext">
								<i class="fas fa-microchip fa-7x"></i>
								<h3 class="mb-0">AI Text</h3>
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- CHOOSE A URL -->
			<div id="lasso-urls" class="tab-container d-none">
				<div class="row align-items-center px-5 pt-5 pb-4">
					<div class="col-lg-5">
						<h2>Choose a Link</h2>
					</div>
					<div class="col-lg-3">
						<button class="lasso-display-add-btn btn-create-link">
							Create a Link
						</button>
					</div>
					<div class="col-lg-4 search-keys">
						<input id="search-key-single" type="text" class="form-control" placeholder="Search URLs">
						<input id="search-key-remind" type="text" class="form-control" placeholder="Search URLs">
						<input id="search-key-button" type="text" class="form-control" placeholder="Search URLs">
						<input id="search-key-image" type="text" class="form-control" placeholder="Search URLs">
					</div>
				</div>

				<!-- SINGLE URL -->
				<div class="link_wrapper">
					<div id="all_links" class="text-break lasso-items">
						<div class="py-5">
							<div class="ls-loader"></div>
						</div>
					</div>
				</div>

			</div>

			<!-- CHOOSE A GROUP -->
			<div id="lasso-groups" class="tab-container d-none">
				<div class="row align-items-center px-5 pt-5 pb-4">
					<div class="col-lg">
						<h2>Choose a Group</h2>
					</div>
					<div class="col-lg search-keys">
						<input id="search-key-grid" type="text" class="form-control" placeholder="Search groups">
						<input id="search-key-list" type="text" class="form-control" placeholder="Search groups">
						<input id="search-key-gallery" type="text" class="form-control" placeholder="Search groups">
					</div>
				</div>

				<!-- SINGLE GROUP -->
				<div id="all_groups" class="text-break py-4 lasso-items">
					<div class="py-5">
						<div class="ls-loader"></div>
					</div>
				</div>

			</div>

			<!-- CHOOSE A TABLE -->
			<div id="lasso-tables" class="tab-container d-none">
				<div class="row align-items-center px-5 pt-5 pb-4">
					<div class="col-lg">
						<h2>Choose a Table</h2>
					</div>
					<div class="col-lg search-keys">
						<input id="search-key-table" type="text" class="form-control" placeholder="Search tables">
					</div>
				</div>

				<!-- SINGLE TABLE -->
				<div id="all_tables" class="text-break py-4 lasso-items">
					<div class="py-5">
						<div class="ls-loader"></div>
					</div>
				</div>

			</div>

			<!-- AI TEXT Prompt -->
			<div id="lasso-aitext" class="tab-container d-none lasso-ai-wrapper">
				<div class="row align-items-center ">
					<div class="col">
						<div class="lasso-response p-4 d-none">
							<div class="lasso-quill-loading"></div>
							<div class="lasso-quill-wrapper">
								<div class="lasso-quill-content"></div>
								<div class="text-center pt-2">
									<button class="lasso-display-add-btn btn-add-lasso-ai-text" onclick="add_short_code_ai_text_main(this)">
										<i class="far fa-plus-circle" aria-hidden="true"></i> Add
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row align-items-center px-5 pt-5 pb-4">
					<div class="col-lg-3">
						<img src="/wp-content/plugins/lasso/admin/assets/images/lasso-logo.svg">
					</div>
					<div class="col-lg-9 text-right">
						<a href="https://support.getlasso.co/" target="_blank">Example Prompts</a>
					</div>
				</div>
				<div class="row align-items-center px-5 pt-2 pb-4">
					<div class="col-lg">
						<div class="form-group">
							<label for="prompt">Prompt:</label>
							<textarea id="prompt" class="form-control" rows="2" maxlength="1000" required></textarea>
						</div>
						<div class="form-group text-right pt-3 pb-4">
							<button class="lasso-display-add-btn btn-generate-text" onclick="get_response_from_prompt()">
								Generate
							</button>
						</div>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- SCRIPTS FOR DEMO ONLY -->
<script>
	var url_quick_detail_modal = jQuery("#url-quick-detail");
	var tab = 'single';
	var block_editor = document.body.classList.contains('block-editor-page');
	var elementor_editor = document.body.classList.contains('elementor-page');
	var quill_lasso_ai_editor = null;

	function add_short_code_single_main(obj) {
		let link_slug = jQuery(obj).data('link-slug');
		let post_id = jQuery(obj).data('post-id');
		let shortcode = '[lasso ref="' + link_slug + '" id="' + post_id + '"]';

		try {
			if (block_editor) {
				add_short_code_single_block(shortcode);
			} else if (elementor_editor) {
				add_short_code_elementor(shortcode);
			} else {
				tinymce.activeEditor.execCommand('mceInsertContent', 0, shortcode);
				after_insert_shortcode_to_post_content(shortcode);
			}
		} catch (error) {
			console.error(error);
		}
	}

	function add_short_code_button_main(obj) {
		let link_slug = jQuery(obj).data('link-slug');
		let post_id = jQuery(obj).data('post-id');
		let shortcode = '[lasso type="button" ref="' + link_slug + '" id="' + post_id + '"]';

		try {
			if (block_editor) {
				add_short_code_button_block(shortcode);
			} else if (elementor_editor) {
				add_short_code_elementor(shortcode);
			} else {
				tinymce.activeEditor.execCommand('mceInsertContent', 0, shortcode);
				after_insert_shortcode_to_post_content(shortcode);
			}
		} catch (error) {
			console.error(error);
		}
	}

	function add_short_code_image_main(obj) {
		let link_slug = jQuery(obj).data('link-slug');
		let post_id = jQuery(obj).data('post-id');
		let shortcode = '[lasso type="image" ref="' + link_slug + '" id="' + post_id + '"]';

		try {
			if (block_editor) {
				add_short_code_image_block(shortcode);
			} else if (elementor_editor) {
				add_short_code_elementor(shortcode);
			} else {
				tinymce.activeEditor.execCommand('mceInsertContent', 0, shortcode);
				after_insert_shortcode_to_post_content(shortcode);
			}
		} catch (error) {
			console.error(error);
		}
	}

	function add_short_code_grid_main(obj) {
		let category_slug = jQuery(obj).data('slug');
		let shortcode = '[lasso type="grid" category="' + category_slug + '"]';

		try {
			if (block_editor) {
				add_short_code_grid_block(shortcode);
			} else if (elementor_editor) {
				add_short_code_elementor(shortcode);
			} else {
				tinymce.activeEditor.execCommand('mceInsertContent', 0, shortcode);
				after_insert_shortcode_to_post_content(shortcode);
			}
		} catch (error) {
			console.error(error);
		}
	}

	function add_short_code_list_main(obj) {
		let category_slug = jQuery(obj).data('slug');
		let shortcode = '[lasso type="list" category="' + category_slug + '"]';

		try {
			if (block_editor) {
				add_short_code_list_block(shortcode);
			} else if (elementor_editor) {
				add_short_code_elementor(shortcode);
			} else {
				tinymce.activeEditor.execCommand('mceInsertContent', 0, shortcode);
				after_insert_shortcode_to_post_content(shortcode);
			}
		} catch (error) {
			console.error(error);
		}
	}

	function add_short_code_gallery_main(obj) {
		let category_slug = jQuery(obj).data('slug');
		let shortcode = '[lasso type="gallery" category="' + category_slug + '"]';

		try {
			if (block_editor) {
				add_short_code_gallery_block(shortcode);
			} else if (elementor_editor) {
				add_short_code_elementor(shortcode);
			} else {
				tinymce.activeEditor.execCommand('mceInsertContent', 0, shortcode);
				after_insert_shortcode_to_post_content(shortcode);
			}
		} catch (error) {
			console.error(error);
		}
	}

	function add_short_code_remind_main(obj) {
		let link_slug = jQuery(obj).data('link-slug');
		let post_id = jQuery(obj).data('post-id');
		let shortcode = '[lasso type="remind" ref="' + link_slug + '" id="' + post_id + '"]';

		try {
			if (block_editor) {
				add_short_code_single_block(shortcode);
			} else if (elementor_editor) {
				add_short_code_elementor(shortcode);
			} else {
				tinymce.activeEditor.execCommand('mceInsertContent', 0, shortcode);
				after_insert_shortcode_to_post_content(shortcode);
			}
		} catch (error) {
			console.error(error);
		}
	}

	function add_short_code_table_main(obj) {
		let table_id = jQuery(obj).data('table-id');
		let shortcode = '[lasso type="table" id="' + table_id + '"]';

		try {
			if (block_editor) {
				add_short_code_table_block(shortcode);
			} else if (elementor_editor) {
				add_short_code_elementor(shortcode);
			} else {
				tinymce.activeEditor.execCommand('mceInsertContent', 0, shortcode);
				after_insert_shortcode_to_post_content(shortcode);
			}
		} catch (error) {
			console.error(error);
		}
	}

	function add_short_code_ai_text_main() {
		let prompt = jQuery('#lasso-aitext #prompt').val().trim();
		let btn_add = jQuery(this);
		// Need to submit to store the rich text editor content
		jQuery.ajax({
			url: ajax_url,
			type: 'post',
			data: {
				action: 'lasso_get_response_from_prompt',
				prompt: prompt,
				is_from_modal: true,
				is_add_shortcode: true,
				result: quill_lasso_ai_editor.root.innerHTML
			},
			beforeSend: function() {
				btn_add.html('<div class="ls-loader"></div>');
			}
		}).done(function(res) {
			btn_add.html('<i class="far fa-plus-circle" aria-hidden="true"></i>Add')
			let shortcode = '[lasso prompt="' + prompt + '"]';
			try {
				if (block_editor) {
					add_short_code_aitext_block(shortcode);
				} else if (elementor_editor) {
					add_short_code_elementor(shortcode);
				} else {
					tinymce.activeEditor.execCommand('mceInsertContent', 0, shortcode);
					after_insert_shortcode_to_post_content(shortcode);
				}
			} catch (error) {
				console.error(error);
			}
		})
	}

	function get_response_from_prompt(is_click_edit_display_btn = false) {
		let prompt = jQuery('#lasso-aitext #prompt').val()
		if (prompt.trim() === '') {
			return false
		}

		let btn_generate_text = jQuery('#lasso-aitext .btn-generate-text');
		let lasso_response_el = jQuery('#lasso-aitext .lasso-response');
		let quill_content = '';
		let is_from_modal = true;
		if (is_click_edit_display_btn === false && quill_lasso_ai_editor) {
			quill_content = quill_lasso_ai_editor.root.innerHTML;
		} else {
			is_from_modal = false;
		}
		jQuery.ajax({
				url: ajax_url,
				type: 'post',
				data: {
					action: 'lasso_get_response_from_prompt',
					prompt: prompt,
					is_from_modal: is_from_modal,
					result: quill_content
				},
				beforeSend: function() {
					lasso_response_el.removeClass('d-none').find('.lasso-quill-loading').html('<div class="py-5"><div class="ls-loader"></div></div>')
					lasso_response_el.find('.lasso-quill-wrapper').addClass('d-none')
				}
			})
			.done(function(res) {
				res = res.data;
				lasso_response_el.removeClass('d-none');
				lasso_response_el.find('.lasso-quill-wrapper').removeClass('d-none')
				// INITIALIZE QUILL
				let toolbarOptions = [
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
				let quill_options = {
					theme: 'snow',
					modules: {
						toolbar: toolbarOptions,
						clipboard: {
							matchVisual: false
						}
					}
				};
				if (quill_lasso_ai_editor) {
					jQuery('#lasso-aitext .ql-toolbar').remove();
					quill_lasso_ai_editor = null;
				}
				quill_lasso_ai_editor = new Quill('.lasso-quill-content', quill_options);
				quill_lasso_ai_editor.root.innerHTML = res.response;

				quill_lasso_ai_editor.on('editor-change', function(eventName, ...args) {
					if ('selection-change' === eventName) {
						quill_lasso_ai_editor.update();
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

				btn_generate_text.text('Refresh');
				lasso_response_el.find('.lasso-quill-loading').html('');
			});
	}

	jQuery(function() {
		var limit = 5;
		var currentPage = {};

		jQuery(".lasso-display-type").off('click').on('click', function() {
			tab = jQuery(this).data('tab');
			show_tab(jQuery(this).data('tab-container'));
		});

		jQuery(".search-keys input").off('keyup').on('keyup', function(e) {
			// WHEN ENTER IS PRESSED, SEARCH
			if (event.which == 13) {
				if (Array('single', 'image', 'button', 'remind').indexOf(tab) != -1) {
					single_search_attributes(true);
				} else if (Array('list', 'grid', 'gallery').indexOf(tab) != -1) {
					grid_search_attributes(true);
				} else if (Array('table').indexOf(tab) != -1) {
					table_search_attributes(true);
				}
			}
		});

		// reset pop-up on close
		jQuery('#lasso-display-add').on('hidden.bs.modal', function() {
			jQuery("#lasso-display-type").removeClass("d-none");
			jQuery("#lasso-display-add .tab-container").addClass("d-none");
			jQuery("#lasso-display-add .tab-container .lasso-items").html('');

			jQuery("#lasso-display-add .btn-generate-text").text("Generate");
			jQuery("#lasso-display-add #prompt").val('');
			jQuery("#lasso-display-add .lasso-response").addClass("d-none");
		});

		function single_search_attributes(entering_search = false) {
			
			let search_key = jQuery('.search-keys input#search-key-' + tab).val();
			let current_page = get_current_page(entering_search);
			
			if(tab == 'remind') {
				tab_for_search = 'single'
			} else {
				tab_for_search = tab;
			}

			console.log(tab, tab_for_search, current_page, entering_search)

			lasso_segment_tracking('Get Lasso urls of "' + tab_for_search + '" type', {
				tab: tab_for_search,
				search_key: search_key,
				current_page: current_page
			});

			jQuery.ajax({
					url: ajax_url,
					type: 'post',
					data: {
						action: 'lasso_search_attributes',
						search_key: search_key,
						limit: limit,
						page: current_page,
					},
					beforeSend: function() {
						show_loading();
					}
				})
				.done(function(res) {
					res = res.data;
					let attributes_count = parseInt(res.count);
					currentPage[tab] = res.page;
					link_html(res.data);
					paginator(attributes_count, search_key);
				});
		}

		function link_html(data) {
			let html = '';
			data.forEach(function(item, index) {
				if (tab == 'single') {
					html += `<div class="row align-items-center py-4 px-5 no-gutters hover-gray">
								<div class="col-lg-1 text-center">
									<div class="lasso-url-image">
										<img width="50" height="50" loading="lazy" class="lasso-url-image" src="${ item.thumbnail }">
									</div>
								</div>
								<div class="col-lg-8">
									<strong class="lasso-url-title">${ item.name }</strong>
									<small class="lasso-url-permalink">${ item.permalink }</small> 
								</div>
								<div class="col-lg-3 text-right">
									<button class="lasso-display-add-btn" onclick="add_short_code_single_main(this);" 
									data-link-slug="${ item.slug }" data-post-id="${ item.post_id }">
										<i class="far fa-plus-circle"></i> Add
									</button> 
								</div>
							</div>
							`;
				} else if (tab == 'image') {
					html += `<div class="row align-items-center py-4 px-5 no-gutters hover-gray">
								<div class="col-lg-1 text-center">
									<div class="lasso-url-image">
										<img width="50" height="50" loading="lazy" class="lasso-url-image" src="${ item.thumbnail }">
									</div>
								</div>
								<div class="col-lg-8">
									<strong class="lasso-url-title">${ item.name }</strong>
									<small class="lasso-url-permalink">${ item.permalink }</small> 
								</div>
								<div class="col-lg-3 text-right">
									<button class="lasso-display-add-btn" onclick="add_short_code_image_main(this);" 
									data-link-slug="${ item.slug }" data-post-id="${ item.post_id }">
										<i class="far fa-plus-circle"></i> Add
									</button> 
								</div>
							</div>
							`;
				} else if (tab == 'button') {
					html += `<div class="row align-items-center py-4 px-5 no-gutters hover-gray">
								<div class="col-lg-1 text-center">
									<div class="lasso-url-image">
										<img width="50" height="50" loading="lazy" class="lasso-url-image" src="${ item.thumbnail }">
									</div>
								</div>
								<div class="col-lg-8">
									<strong class="lasso-url-title">${ item.name }</strong>
									<small class="lasso-url-permalink">${ item.permalink }</small> 
								</div>
								<div class="col-lg-3 text-right">
									<button class="lasso-display-add-btn" onclick="add_short_code_button_main(this);" 
									data-link-slug="${ item.slug }" data-post-id="${ item.post_id }">
										<i class="far fa-plus-circle"></i> Add
									</button> 
								</div>
							</div>
							`;
				} else if (tab == 'grid') {
					html += `<div class="row align-items-center py-4 px-5 no-gutters hover-gray"> 
								<div class="col-lg"> 
									<strong class="lasso-url-title">${ item.post_title }</strong> 
								</div> 
								<div class="col-lg-3 text-right"> 
									<button class="lasso-display-add-btn" onclick="add_short_code_grid_main(this);" 
									data-slug="${ item.slug }">
										<i class="far fa-plus-circle"></i> Add
									</button>
								</div> 
							</div> 
							`;
				} else if (tab == 'list') {
					html += `<div class="row align-items-center py-4 px-5 no-gutters hover-gray"> 
								<div class="col-lg"> 
									<strong class="lasso-url-title">${ item.post_title }</strong> 
								</div> 
								<div class="col-lg-3 text-right"> 
									<button class="lasso-display-add-btn" onclick="add_short_code_list_main(this);" 
									data-slug="${ item.slug }">
										<i class="far fa-plus-circle"></i> Add
									</button>
								</div> 
							</div> 
							`;
				} else if (tab == 'gallery') {
					html += `<div class="row align-items-center py-4 px-5 no-gutters hover-gray"> 
								<div class="col-lg"> 
									<strong class="lasso-url-title">${ item.post_title }</strong> 
								</div> 
								<div class="col-lg-3 text-right"> 
									<button class="lasso-display-add-btn" onclick="add_short_code_gallery_main(this);" 
									data-slug="${ item.slug }">
										<i class="far fa-plus-circle"></i> Add
									</button>
								</div> 
							</div> 
							`;
				} else if (tab == 'table') {
					html += `<div class="row align-items-center py-4 px-5 no-gutters hover-gray">
								<div class="col-lg">
									<strong class="lasso-url-title">${ item.title }</strong>
								</div>
								<div class="col-lg-3 text-right">
									<button class="lasso-display-add-btn" onclick="add_short_code_table_main(this);"
									data-table-id="${ item.id }">
										<i class="far fa-plus-circle"></i> Add
									</button>
								</div>
							</div>
							`;
				} else if (tab == 'remind') {
					html += `<div class="row align-items-center py-4 px-5 no-gutters hover-gray">
								<div class="col-lg-1 text-center">
									<div class="lasso-url-image">
										<img width="50" height="50" loading="lazy" class="lasso-url-image" src="${ item.thumbnail }">
									</div>
								</div>
								<div class="col-lg-8">
									<strong class="lasso-url-title">${ item.name }</strong>
									<small class="lasso-url-permalink">${ item.permalink }</small> 
								</div>
								<div class="col-lg-3 text-right">
									<button class="lasso-display-add-btn" onclick="add_short_code_remind_main(this);" 
									data-link-slug="${ item.slug }" data-post-id="${ item.post_id }">
										<i class="far fa-plus-circle"></i> Add
									</button> 
								</div>
							</div>
							`;
				}
			});

			html += '<div id="pagination-container" class="pagination"></div>';

			if (Array('single', 'image', 'button', 'remind').indexOf(tab) != -1) {
				jQuery("#all_links").html(html);
			} else if (Array('list', 'grid', 'gallery').indexOf(tab) != -1) {
				jQuery("#all_groups").html(html);
			} else if (Array('table').indexOf(tab) != -1) {
				jQuery("#all_tables").html(html);
			}

		}

		function paginator(attributes_count) {
			var paginator = jQuery('#pagination-container').pagination({
				items: attributes_count,
				itemsOnPage: limit,
				currentPage: currentPage[tab],
				cssStyle: 'light-theme',
				onPageClick: function(pageNumber, event) {
					currentPage[tab] = pageNumber;
					lasso_helper.remove_page_number_out_of_url();
					if (Array('single', 'image', 'button', 'remind').indexOf(tab) != -1) {
						single_search_attributes();
					} else if (Array('grid', 'list', 'gallery').indexOf(tab) != -1) {
						grid_search_attributes();
					} else if (Array('table').indexOf(tab) != -1) {
						table_search_attributes();
					}
				}
			});
		}

		function show_loading() {
			let html = '<div class="py-5"><div class="ls-loader"></div></div>';
			if (Array('single', 'image', 'button', 'remind').indexOf(tab) != -1) {
				jQuery("#all_links").html(html);
			} else if (Array('grid', 'list', 'gallery').indexOf(tab) != -1) {
				jQuery("#all_groups").html(html);
			} else if (Array('table').indexOf(tab) != -1) {
				jQuery("#all_tables").html(html);
			}
		}

		// GRID
		function grid_search_attributes(entering_search = false) {
			let search_key = jQuery('.search-keys input#search-key-' + tab).val();
			let current_page = get_current_page(entering_search);

			lasso_segment_tracking('Get Lasso urls of "' + tab + '" type', {
				tab: tab,
				search_key: search_key,
				current_page: current_page
			});

			jQuery.ajax({
					url: ajax_url,
					type: 'post',
					data: {
						action: 'lasso_get_groups',
						search_key: search_key,
						limit: limit,
						page: current_page,
					},
					beforeSend: function() {
						show_loading();
					}
				})
				.done(function(res) {
					res = res.data;
					let attributes_count = parseInt(res.count);
					currentPage[tab] = res.page;
					link_html(res.data);
					paginator(attributes_count);
				})
		}

		// TABLE COMPARISON
		function table_search_attributes(entering_search = false) {
			let search_key = jQuery('.search-keys input#search-key-' + tab).val();
			let current_page = get_current_page(entering_search);

			lasso_segment_tracking('Get Lasso urls of "' + tab + '" type', {
				tab: tab,
				search_key: search_key,
				current_page: current_page
			});

			jQuery.ajax({
					url: ajax_url,
					type: 'post',
					data: {
						action: 'lasso_get_tables',
						search_key: search_key,
						limit: limit,
						page: current_page,
					},
					beforeSend: function() {
						show_loading();
					}
				})
				.done(function(res) {
					res = res.data;
					let attributes_count = parseInt(res.count);
					currentPage[tab] = res.page;
					link_html(res.data);
					paginator(attributes_count);
				})
		}

		// AI Text
		function lasso_ai_text_attributes() {
			let current_page = get_current_page(false);
			lasso_segment_tracking('Get Lasso AI Text of "' + tab + '" type', {
				tab: tab,
				current_page: current_page
			});
			add_short_code_ai_text_main();
		}

		// Get tab's current page
		function get_current_page(entering_search = false) {
			if (!(tab in currentPage) || entering_search) {
				currentPage[tab] = 1;
			}

			return currentPage[tab];
		}

		/**
		 * Show selected tab.
		 *
		 * @param tab_container
		 */
		function show_tab(tab_container) {
			let tab_container_el = jQuery('#' + tab_container);
			jQuery('#lasso-display-type').addClass('d-none');
			tab_container_el.removeClass('d-none');
			tab_container_el.find('.search-keys input').addClass('d-none');
			tab_container_el.find('.search-keys input#search-key-' + tab).removeClass('d-none');

			if (Array('single', 'image', 'button', 'remind').indexOf(tab) != -1) {
				single_search_attributes();
			} else if (Array('list', 'grid', 'gallery').indexOf(tab) != -1) {
				grid_search_attributes();
			} else if (Array('table').indexOf(tab) != -1) {
				table_search_attributes();
			}
		}

		jQuery(document).on('click', '.btn-close-save-quick-link', function() {
			let allow_pages = lasso_helper.allow_monetize_pages;
			let current_page = lasso_helper.get_page_name();

			url_quick_detail_modal.modal("hide");
			jQuery(this).fix_backdrop_elementor();
			if (allow_pages.includes(current_page)) {
				let monetize_row = jQuery('input.js-toggle[data-link-id="' + monetize_id + '"]');
				monetize_row.prop("checked", true).trigger('change');
				return;
			}

			jQuery("#lasso-display-type").addClass("d-none");
			jQuery("#lasso-urls").removeClass("d-none");
			jQuery("#lasso-display-add").modal("show");
			single_search_attributes();
		}).on('click', '.btn-create-link', function() {
			jQuery("#lasso-display-add").modal("hide");
			jQuery("#url-add").modal("show");
			jQuery(this).fix_backdrop_elementor();
		});
	});
</script>