/* eslint-env jquery */
(function ($) {
	const { __ } = wp.i18n;
	const temp = {
		person: __('Website Owner Name'),
		organization: __('Organization Name', 'wp-schema-pro'),
		Webshop: __('Webshop Name', 'wp-schema-pro'),
		personblog: __('Website Owner Name', 'wp-schema-pro'),
		Smallbusiness: __('Blog Website Name', 'wp-schema-pro'),
		Otherbusiness: __('Business Name', 'wp-schema-pro'),
	};

	/**
	 * AIOSRS Frontend
	 *
	 * @class WPSchemaProSettings
	 * @since 1.0
	 */
	const WPSchemaProSettings = {
		init() {
			const self = this;
			this.customFieldDependecy();
			this.customImageSelect();
			this.initRepeater();
			this.toolTips();
			this.regenerateSchema();

			$('select.wp-select2').each(function (index, el) {
				self.init_target_rule_select2(el);
			});
		},
		regenerateSchema() {
			$('#wpsp-regenerate-schema').click(function () {
				$(this).next('span.spinner').addClass('is-active');

				jQuery
					.ajax({
						url: ajaxurl,
						type: 'post',
						dataType: 'json',
						data: {
							action: 'regenerate_schema',
							nonce: $(this).data('nonce'),
						},
					})
					.success(function () {
						$('#wpsp-regenerate-schema')
							.next('span.spinner')
							.removeClass('is-active');

						$('#wpsp-regenerate-notice')
							.show()
							.delay(2000)
							.fadeOut();
					});
			});
		},
		toolTips() {
			$(document).on(
				'click',
				'.wp-schema-pro-tooltip-icon',
				function (e) {
					e.preventDefault();
					$('.wp-schema-pro-tooltip-wrapper').removeClass('activate');
					$(this).parent().addClass('activate');
				}
			);

			$(document).on('click', function (e) {
				if (
					!$(e.target).hasClass(
						'wp-schema-pro-tooltip-description'
					) &&
					!$(e.target).hasClass('wp-schema-pro-tooltip-icon') &&
					$(e.target).closest('.wp-schema-pro-tooltip-description')
						.length === 0
				) {
					$('.wp-schema-pro-tooltip-wrapper').removeClass('activate');
				}
			});
		},

		customImageSelect() {
			let fileFrame;
			window.inputWrapper = '';

			$(document.body).on(
				'click',
				'.image-field-wrap .aiosrs-image-select',
				function (e) {
					e.preventDefault();

					window.inputWrapper = $(this).closest('td');

					// Create the media frame.
					fileFrame = wp.media({
						button: {
							text: 'Select Image',
							close: false,
						},
						states: [
							new wp.media.controller.Library({
								title: 'Select Custom Image',
								library: wp.media.query({ type: 'image' }),
								multiple: false,
							}),
						],
					});

					// When an image is selected, run a callback.
					fileFrame.on('select', function () {
						const attachment = fileFrame
							.state()
							.get('selection')
							.first()
							.toJSON();

						const image = window.inputWrapper.find(
							'.image-field-wrap img'
						);
						if (image.length === 0) {
							window.inputWrapper
								.find('.image-field-wrap')
								.append(
									'<a href="#" class="aiosrs-image-select img"><img src="' +
									attachment.url +
									'" /></a>'
								);
						} else {
							image.attr('src', attachment.url);
						}
						window.inputWrapper
							.find('.image-field-wrap')
							.addClass('bsf-custom-image-selected');
						window.inputWrapper
							.find('.single-image-field')
							.val(attachment.id);

						fileFrame.close();
					});

					fileFrame.open();
				}
			);

			$(document).on('click', '.aiosrs-image-remove', function (e) {
				e.preventDefault();
				const parent = $(this).closest('td');
				parent
					.find('.image-field-wrap')
					.removeClass('bsf-custom-image-selected');
				parent.find('.single-image-field').val('');
				parent.find('.image-field-wrap img').removeAttr('src');
			});
			window.inputWrapper = '';
		},

		customFieldDependecy() {
			jQuery(document).on(
				'change',
				'#post-body-content .wp-schema-pro-custom-option-select, .aiosrs-pro-setup-wizard-content.general-setting-content-wrap .wp-schema-pro-custom-option-select',
				function () {
					const customWrap = jQuery(this).next(
						'.custom-field-wrapper'
					);

					customWrap.css('display', 'none');
					if ('custom' === jQuery(this).val()) {
						customWrap.css('display', '');
					}
				}
			);

			jQuery(document).on(
				'change',
				'select[name="wp-schema-pro-general-settings[site-represent]"]',
				function () {
					const wrapper = jQuery(this).closest('table'),
						logoWrap = wrapper.find(
							'.wp-schema-pro-site-logo-wrap'
						),
						companyNameWrap = wrapper.find(
							'.wp-schema-pro-site-name-wrap'
						),
						personNameWrap = wrapper.find(
							'.wp-schema-pro-person-name-wrap'
						);

					companyNameWrap.css('display', 'none');
					personNameWrap.css('display', 'none');
					if ('' !== jQuery(this).val()) {
						if (
							'organization' === jQuery(this).val() ||
							'Webshop' === jQuery(this).val() ||
							'Smallbusiness' === jQuery(this).val() ||
							'Otherbusiness' === jQuery(this).val()
						) {
							logoWrap.css('display', '');
							companyNameWrap.css('display', '');
						} else {
							personNameWrap.css('display', '');
							logoWrap.css('display', '');
						}
					}
				}
			);
			jQuery(document).on(
				'change',
				'select[name="wp-schema-pro-general-settings[site-represent]"]',
				function () {
					const organizationType = jQuery(this).val();
					if ('' !== jQuery(this).val()) {
						if (organizationType in temp) {
							$('.wpsp-organization-label').text(
								temp[organizationType]
							);
						}
					}
				}
			);
			jQuery(document).on(
				'change',
				'select[name="wp-schema-pro-corporate-contact[contact-type]"]',
				function () {
					const wrapper = jQuery(this).closest('table'),
						contactPointWrap = wrapper.find(
							'.wp-schema-pro-other-wrap'
						);
					contactPointWrap.css('display', 'none');
					if ('' !== jQuery(this).val()) {
						if ('other' === jQuery(this).val()) {
							contactPointWrap.css('display', '');
						}
					}
				}
			);
			$('#add-row').on('click', function () {
				const row = $('.empty-row.screen-reader-text').clone(true);
				row.removeClass('empty-row screen-reader-text');
				row.insertBefore('#repeatable-fieldset-one >tr:last');
				return false;
			});

			$('.remove-row').on('click', function () {
				$(this).parents('tr').remove();
				return false;
			});
		},

		initRepeater() {
			$(document).on(
				'click',
				'.bsf-repeater-add-new-btn',
				function (event) {
					event.preventDefault();

					const selector = $(this),
						parentWrap = selector.closest(
							'.bsf-aiosrs-schema-type-wrap'
						),
						totalCount = parentWrap.find(
							'.aiosrs-pro-repeater-table-wrap'
						).length,
						template = parentWrap
							.find('.aiosrs-pro-repeater-table-wrap')
							.first()
							.clone();

					template.find('input, textarea, select').each(function () {
						$(this).val('');

						const fieldName =
							'undefined' !== typeof $(this).attr('name')
								? $(this)
									.attr('name')
									.replace(
										'[0]',
										'[' + totalCount + ']'
									)
								: '',
							fieldClass =
								'undefined' !== typeof $(this).attr('class')
									? $(this)
										.attr('class')
										.replace(
											'-0-',
											'-' + totalCount + '-'
										)
									: '',
							fieldId =
								'undefined' !== typeof $(this).attr('id')
									? $(this)
										.attr('id')
										.replace(
											'-0-',
											'-' + totalCount + '-'
										)
									: '';

						$(this).attr('name', fieldName);
						$(this).attr('class', fieldClass);
						$(this).attr('id', fieldId);
					});

					template.insertBefore(selector);
				}
			);

			$(document).on('click', '.bsf-repeater-close', function (event) {
				event.preventDefault();

				const selector = $(this),
					parentWrap = selector.closest(
						'.bsf-aiosrs-schema-type-wrap'
					),
					repeaterCount = parentWrap.find(
						'> .aiosrs-pro-repeater-table-wrap'
					).length;

				if (repeaterCount > 1) {
					selector
						.closest('.aiosrs-pro-repeater-table-wrap')
						.remove();
				}
			});
		},
		init_target_rule_select2(selector) {
			$(selector).select2({
				placeholder: 'Search Fields...',

				ajax: {
					url: ajaxurl,
					dataType: 'json',
					method: 'post',
					delay: 250,
					data(params) {
						return {
							nonce_ajax: AIOSRS_search.search_field,
							q: params.term, // search term
							page: params.page,
							action: 'bsf_get_specific_pages',
						};
					},
					processResults(data) {
						return {
							results: data,
						};
					},
					cache: true,
				},
				minimumInputLength: 2,
			});
		},
	};
	const loadDefaultValues = function () {
		const field = jQuery(
			'select[name="wp-schema-pro-general-settings[site-represent]"]'
		),
			wrapper = field.closest('table'),
			logoWrap = wrapper.find('.wp-schema-pro-site-logo-wrap'),
			companyNameWrap = wrapper.find('.wp-schema-pro-site-name-wrap'),
			personNameWrap = wrapper.find('.wp-schema-pro-person-name-wrap');

		companyNameWrap.css('display', 'none');
		personNameWrap.css('display', 'none');
		if ('' !== field.val()) {
			if (
				'organization' === field.val() ||
				'Webshop' === field.val() ||
				'Smallbusiness' === field.val() ||
				'Otherbusiness' === field.val()
			) {
				logoWrap.css('display', '');
				companyNameWrap.css('display', '');
			} else {
				personNameWrap.css('display', '');
				logoWrap.css('display', '');
			}
		}
	};
	const loadDefaultOrganizationLabel = function () {
		const field = jQuery(
			'select[name="wp-schema-pro-general-settings[site-represent]"]'
		),
			organizationType = field.val();
		if ('' !== field) {
			if (organizationType in temp) {
				$('.wpsp-organization-label').text(temp[organizationType]);
			}
		}
	};

	$(document).ready(function () {
		$('.wp-select2').select2();
		$('.wpsp-setup-configuration-settings').select2();
		loadDefaultValues();
		loadDefaultOrganizationLabel();
		$('#add-row').on('click', function () {
			const row = $('.empty-row.screen-reader-text').clone(true);
			row.removeClass('empty-row screen-reader-text');
			row.insertBefore('#repeatable-fieldset-one tbody>tr:last');
			return false;
		});

		$('.remove-row').on('click', function () {
			$(this).parents('tr').remove();
			return false;
		});
	});

	/* Initializes the AIOSRS Frontend. */
	$(function () {
		WPSchemaProSettings.init();
	});
})(jQuery);
