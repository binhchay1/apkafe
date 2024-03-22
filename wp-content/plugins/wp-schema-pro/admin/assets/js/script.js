/* eslint-env jquery */
(function ($) {
	/**
	 * AIOSRS Schema
	 *
	 * @class AIOSRSSchema
	 * @since 1.0
	 */
	const AIOSRSSchema = {
		/**
		 * Initializes a AIOSRS Schema.
		 *
		 * @since 1.0
		 * @function init
		 */
		container: '',

		init() {
			const self = this;

			self.container = $(
				'#aiosrs-schema-settings, #aiosrs-pro-custom-fields'
			);

			// Init backgrounds.
			$(document).ready(function () {
				$('.select2-class').select2();
				const selectOption = ['Site Meta', 'Post Meta (Basic Fields)'];
				const customOptionGroup = $('#bsf-aiosrs-schema-type').val();
				if ('custom-markup' === customOptionGroup) {
					for (let i = 0; i < selectOption.length; i++) {
						$(
							'#bsf-aiosrs-custom-markup-custom-markup optgroup[label="' +
							selectOption[i] +
							'"]'
						).remove();
					}
				}
				const customMarkupSchemId = $(
					'#custom-schema-schema-field'
				).val();
				if (customMarkupSchemId) {
					for (let i = 0; i < selectOption.length; i++) {
						$(
							'#custom-markup-' +
							customMarkupSchemId +
							'-custom-markup-connected optgroup[label="' +
							selectOption[i] +
							'"]'
						).remove();
					}
				}
			});

			self.container.on(
				'change',
				'select.bsf-aiosrs-schema-meta-field',
				function () {
					const selfFun = $(this),
						parent = selfFun.parent(),
						value = selfFun.val();

					const textwrapperCustom = parent.find(
						'.bsf-aiosrs-schema-custom-text-wrap'
					);
					if ('custom-text' === value) {
						textwrapperCustom.removeClass('bsf-hidden-field');
					} else if (
						!textwrapperCustom.hasClass('bsf-hidden-field')
					) {
						textwrapperCustom.addClass('bsf-hidden-field');
					}

					const textWrapperFixed = parent.find(
						'.bsf-aiosrs-schema-fixed-text-wrap'
					);
					if ('fixed-text' === value) {
						textWrapperFixed.removeClass('bsf-hidden-field');
					} else if (!textWrapperFixed.hasClass('bsf-hidden-field')) {
						textWrapperFixed.addClass('bsf-hidden-field');
					}

					const specificMetaWrapper = parent.find(
						'.bsf-aiosrs-schema-specific-field-wrap'
					);
					if ('specific-field' === value) {
						specificMetaWrapper.removeClass('bsf-hidden-field');
					} else if (
						!specificMetaWrapper.hasClass('bsf-hidden-field')
					) {
						specificMetaWrapper.addClass('bsf-hidden-field');
					}
				}
			);

			self.container.on(
				'change',
				'.bsf-aiosrs-schema-row-rating-type select.bsf-aiosrs-schema-meta-field',
				function (e) {
					e.preventDefault();

					$(this)
						.closest('.bsf-aiosrs-schema-table')
						.find('.bsf-aiosrs-schema-row')
						.css('display', '');
					if ('accept-user-rating' === $(this).val()) {
						const reviewCountWrap = $(this)
							.closest('.bsf-aiosrs-schema-row')
							.next('.bsf-aiosrs-schema-row'),
							name = reviewCountWrap
								.find('.bsf-aiosrs-schema-meta-field')
								.attr('name');

						const selectedSchemaType = jQuery(
							'.bsf-aiosrs-review-schema-type'
						).val();
						if (selectedSchemaType) {
							const prepareName =
								'bsf-aiosrs-review[' +
								selectedSchemaType +
								'-review-count]';

							if (name.indexOf(prepareName) >= 0) {
								reviewCountWrap.hide();
							}
						}

						if (name.indexOf('[review-count]') >= 0) {
							reviewCountWrap.hide();
						}
					}
				}
			);
			self.container
				.find('select.bsf-aiosrs-schema-meta-field')
				.trigger('change');

			$('select.bsf-aiosrs-schema-select2').each(function (index, el) {
				self.init_target_rule_select2(el);
			});

			self.container.on(
				'click',
				'.bsf-repeater-add-new-btn',
				function (event) {
					event.preventDefault();
					self.add_new_repeater($(this));
					self.prepare_event_schmea_fields();
				}
			);

			self.container.on('click', '.bsf-repeater-close', function (event) {
				event.preventDefault();
				self.add_remove_repeater($(this));
			});

			self.schemaTypeDependency();
			self.bindTooltip();
			if (!$('body').hasClass('post-type-aiosrs-schema')) {
				self.field_validation();
			}
		},
		field_validation() {
			$(
				'.wpsp-custom-field-connect, .wpsp-field-close, .bsf-aiosrs-schema-meta-field, image-field-wrap, .aiosrs-pro-custom-field, .wpsp-custom-field-connect'
			).on('click focus change', function () {
				$('.bsf-aiosrs-schema-type-wrap').each(function (
					index,
					repeater
				) {
					let fieldValue = $(repeater)
						.find('.wpsp-default-hidden-value')
						.val();
					const requiredPath = $(repeater)
						.parents('.bsf-aiosrs-schema-row-content')
						.prev();
					if (undefined !== fieldValue) {
						fieldValue = fieldValue.trim();
						if (fieldValue) {
							if ($('body').hasClass('block-editor-page')) {
								if (
									!$(repeater).find(
										'.wpsp-required-error-field'
									).length
								) {
									let metaField;
									switch (fieldValue) {
										case 'post_title':
											metaField = $(
												'.editor-post-title__input'
											).val();
											break;
										case 'post_content':
											metaField =
												$(
													'p.block-editor-rich-text__editable'
												).text().length > 1
													? $(
														'p.block-editor-rich-text__editable'
													).text()
													: '';
											break;
										case 'post_excerpt':
											metaField = $(
												'.components-textarea-control__input'
											).val();
											break;
										case 'featured_img':
											if (
												'Set featured image' ===
												$(
													'.editor-post-featured-image__toggle'
												).text()
											) {
												metaField = '';
											} else {
												metaField = $(
													'.components-responsive-wrapper__content'
												).attr('src');
											}
											break;
										default:
											requiredPath.removeClass(
												'wpsp-required-error-field'
											);
											requiredPath
												.find('label')
												.removeClass(
													'wpsp-required-error-field'
												);
									}

									if (undefined !== metaField) {
										if ('' !== metaField) {
											requiredPath.removeClass(
												'wpsp-required-error-field'
											);
											requiredPath
												.find('label')
												.removeClass(
													'wpsp-required-error-field'
												);
										} else if (
											requiredPath.find('.required')
												.length
										) {
											requiredPath
												.find('label')
												.addClass(
													'wpsp-required-error-field'
												);
										}
									}
								} else {
									requiredPath.removeClass(
										'wpsp-required-error-field'
									);
									requiredPath
										.find('label')
										.removeClass(
											'wpsp-required-error-field'
										);
								}
							} else {
								requiredPath.removeClass(
									'wpsp-required-error-field'
								);
								requiredPath
									.find('label')
									.removeClass('wpsp-required-error-field');
							}
						} else if (requiredPath.find('.required').length) {
							requiredPath
								.find('label')
								.addClass('wpsp-required-error-field');
						}
					}
				});
			});
		},
		hide_review_count() {
			$(this)
				.closest('.bsf-aiosrs-schema-table')
				.find('.bsf-aiosrs-schema-row')
				.css('display', '');
			if ('accept-user-rating' === $(this).val()) {
				const reviewCountWrap = $(this)
					.closest('.bsf-aiosrs-schema-row')
					.next('.bsf-aiosrs-schema-row'),
					name = reviewCountWrap
						.find('.bsf-aiosrs-schema-meta-field')
						.attr('name');

				const selectedSchemaType = jQuery(
					'.bsf-aiosrs-review-schema-type'
				).val();
				if (selectedSchemaType) {
					const prepareName =
						'bsf-aiosrs-review[' +
						selectedSchemaType +
						'-review-count]';

					if (name.indexOf(prepareName) >= 0) {
						reviewCountWrap.hide();
					}
				}

				if (name.indexOf('[review-count]') >= 0) {
					reviewCountWrap.hide();
				}
			}
		},

		add_new_repeater(selector) {
			const self = this,
				parentWrap = selector.closest('.bsf-aiosrs-schema-type-wrap'),
				totalCount = parentWrap.find('.aiosrs-pro-repeater-table-wrap')
					.length,
				template = parentWrap
					.find('.aiosrs-pro-repeater-table-wrap')
					.first()
					.clone();

			template
				.find(
					'.bsf-aiosrs-schema-custom-text-wrap, .bsf-aiosrs-schema-specific-field-wrap'
				)
				.each(function () {
					if (!$(this).hasClass('bsf-hidden-field')) {
						$(this).addClass('bsf-hidden-field');
					}
				});

			template
				.find('select.bsf-aiosrs-schema-meta-field')
				.each(function () {
					$(this).val('none');

					const fieldName =
						'undefined' !== typeof $(this).attr('name')
							? $(this)
								.attr('name')
								.replace('[0]', '[' + totalCount + ']')
							: '',
						fieldClass =
							'undefined' !== typeof $(this).attr('class')
								? $(this)
									.attr('class')
									.replace('-0-', '-' + totalCount + '-')
								: '',
						fieldId =
							'undefined' !== typeof $(this).attr('id')
								? $(this)
									.attr('id')
									.replace('-0-', '-' + totalCount + '-')
								: '';

					$(this).attr('name', fieldName);
					$(this).attr('class', fieldClass);
					$(this).attr('id', fieldId);
				});
			template
				.find(
					'input, textarea, select:not(.bsf-aiosrs-schema-meta-field)'
				)
				.each(function () {
					$(this).val('');

					const fieldName =
						'undefined' !== typeof $(this).attr('name')
							? $(this)
								.attr('name')
								.replace('[0]', '[' + totalCount + ']')
							: '',
						fieldClass =
							'undefined' !== typeof $(this).attr('class')
								? $(this)
									.attr('class')
									.replace('-0-', '-' + totalCount + '-')
								: '',
						fieldId =
							'undefined' !== typeof $(this).attr('id')
								? $(this)
									.attr('id')
									.replace('-0-', '-' + totalCount + '-')
								: '';

					$(this).attr('name', fieldName);
					$(this).attr('class', fieldClass);
					$(this).attr('id', fieldId);
				});

			template.find('span.select2-container').each(function () {
				$(this).remove();
			});

			template.insertBefore(selector);
			template
				.find('select.bsf-aiosrs-schema-select2')
				.each(function (index, el) {
					self.init_target_rule_select2(el);
				});

			AIOSRSSchema.init_date_time_fields();
		},

		add_remove_repeater(selector) {
			const parentWrap = selector.closest('.bsf-aiosrs-schema-type-wrap'),
				repeaterCount = parentWrap.find(
					'> .aiosrs-pro-repeater-table-wrap'
				).length;

			if (repeaterCount > 1) {
				selector.closest('.aiosrs-pro-repeater-table-wrap').remove();

				if ('aiosrs-pro-custom-fields' === this.container.attr('id')) {
					// Reset index to avoid duplicate names.
					parentWrap
						.find('> .aiosrs-pro-repeater-table-wrap')
						.each(function (wrapIndex, repeaterWap) {
							$(repeaterWap).each(function (
								elementIndex,
								element
							) {
								$(element)
									.find(
										'input, textarea, select:not(.bsf-aiosrs-schema-meta-field)'
									)
									.each(function (elIndex, el) {
										const fieldName =
											'undefined' !==
												typeof $(el).attr('name')
												? $(el)
													.attr('name')
													.replace(
														/\[\d+]/,
														'[' +
														wrapIndex +
														']'
													)
												: '';
										$(el).attr('name', fieldName);
									});
							});
						});
				}
			}
		},

		bindTooltip() {
			// Call Tooltip
			$('.bsf-aiosrs-schema-heading-help').tooltip({
				content() {
					return $(this).prop('title');
				},
				tooltipClass: 'bsf-aiosrs-schema-ui-tooltip',
				position: {
					my: 'center top',
					at: 'center bottom+10',
				},
				hide: {
					duration: 200,
				},
				show: {
					duration: 200,
				},
			});
		},

		schemaTypeDependency() {
			const container = this.container;
			this.container.on(
				'change',
				'select[name="bsf-aiosrs-schema-type"]',
				function () {
					container
						.find('.bsf-aiosrs-schema-meta-wrap')
						.css('display', 'none');
					const schemaType = $(this).val();
					if (
						'undefined' !== typeof schemaType &&
						'' !== schemaType
					) {
						container
							.find('#bsf-' + schemaType + '-schema-meta-wrap')
							.css('display', '');
					}
				}
			);
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
							nonce_ajax: AIOSRS_Rating.specified_field,
							q: params.term, // search term
							page: params.page,
							action: 'bsf_get_specific_meta_fields',
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

		get_review_item_type_html(itemType) {
			jQuery
				.post({
					url: ajaxurl,
					data: {
						action: 'fetch_item_type_html',
						itemType,
						nonce: AIOSRS_Rating.security,
						post_id: jQuery('#post_ID').val(),
					},
				})
				.done(function (response) {
					$('.bsf-review-item-type-field').remove();
					$(response).insertAfter(
						jQuery('#bsf-aiosrs-review-schema-type')
							.parent()
							.parent()
							.closest('tr')
					);
					$('select.bsf-aiosrs-schema-select2').each(function (
						index,
						el
					) {
						AIOSRSSchema.init_target_rule_select2(el);
					});

					const itemSpecificType =
						'.bsf-aiosrs-review-' + itemType + '-rating';
					$(itemSpecificType).each(function () {
						$(this)
							.closest('.bsf-aiosrs-schema-table')
							.find('.bsf-aiosrs-schema-row')
							.css('display', '');
						if ('accept-user-rating' === $(this).val()) {
							const reviewCountWrap = $(this)
								.closest('.bsf-aiosrs-schema-row')
								.next('.bsf-aiosrs-schema-row'),
								name = reviewCountWrap
									.find('.bsf-aiosrs-schema-meta-field')
									.attr('name');

							const selectedSchemaType = jQuery(
								'.bsf-aiosrs-review-schema-type'
							).val();
							if (selectedSchemaType) {
								const prepareName =
									'bsf-aiosrs-review[' +
									selectedSchemaType +
									'-review-count]';

								if (name.indexOf(prepareName) >= 0) {
									reviewCountWrap.hide();
								}
							}

							if (name.indexOf('[review-count]') >= 0) {
								reviewCountWrap.hide();
							}
						}
					});

					AIOSRSSchema.init_date_time_fields();
					AIOSRSSchema.prepare_event_schmea_fields();
				})
				.fail(function () { });
		},

		prepare_event_schmea_fields() {
			$(
				'.wpsp-dropdown-event-status, .wpsp-dropdown-bsf-aiosrs-event-event-status'
			).change(function () {
				const parent = $(this).parents(
					'.bsf-aiosrs-schema-meta-wrap, .aiosrs-pro-meta-fields-wrap'
				);

				parent
					.find(
						'td.wpsp-event-status-rescheduled, td.bsf-aiosrs-review-bsf-aiosrs-event-previous-date'
					)
					.hide();
				if (!this.value) {
					this.value = 'EventScheduled';
				}

				if ('EventRescheduled' === this.value) {
					parent
						.find(
							'td.wpsp-event-status-rescheduled, td.bsf-aiosrs-review-bsf-aiosrs-event-previous-date'
						)
						.show();
				}

				const eventStatus = $(
					'.wpsp-dropdown-event-attendance-mode, .wpsp-dropdown-bsf-aiosrs-event-event-attendance-mode'
				).val();

				if (
					'EventMovedOnline' === this.value ||
					'OfflineEventAttendanceMode' !== eventStatus
				) {
					parent.find('td.wpsp-event-status-offline').hide();
					parent.find('td.wpsp-event-status-online').show();
					parent.find('td.wpsp-online-event-timezone').show();
					parent
						.find(
							'.wpsp-dropdown-event-attendance-mode, .wpsp-dropdown-bsf-aiosrs-event-event-attendance-mode'
						)
						.val('OnlineEventAttendanceMode');
				} else {
					parent.find('td.wpsp-event-status-offline').show();
					parent.find('td.wpsp-event-status-online').hide();
					parent.find('td.wpsp-online-event-timezone').hide();
				}
			});
			$(
				'.wpsp-dropdown-event-attendance-mode, .wpsp-dropdown-bsf-aiosrs-event-event-attendance-mode'
			).change(function () {
				const parent = $(this).parents(
					'.bsf-aiosrs-schema-meta-wrap, .aiosrs-pro-meta-fields-wrap'
				);
				parent.find('td.wpsp-event-status-rescheduled').hide();
				const eventStatus = $(
					'.wpsp-dropdown-event-status, .wpsp-dropdown-bsf-aiosrs-event-event-status'
				).val();

				if ('EventMovedOnline' !== eventStatus) {
					parent.find('td.wpsp-event-status-offline').show();
					parent.find('td.wpsp-event-status-online').hide();
					parent.find('td.wpsp-online-event-timezone').hide();
				}

				if ('OfflineEventAttendanceMode' !== this.value) {
					parent.find('td.wpsp-event-status-offline').hide();
					parent.find('td.wpsp-event-status-online').show();
					parent.find('td.wpsp-online-event-timezone').show();
				}

				if ('MixedEventAttendanceMode' === this.value) {
					parent.find('td.wpsp-event-status-offline').show();
					parent.find('td.wpsp-event-status-online').show();
					parent.find('td.wpsp-online-event-timezone').show();
				}
			});

			$(
				'.wpsp-dropdown-event-attendance-mode, .wpsp-dropdown-bsf-aiosrs-event-event-attendance-mode'
			).trigger('change');
		},

		init_date_time_fields() {
			$(
				'.wpsp-datetime-local-field, .wpsp-date-field, .wpsp-time-duration-field'
			).each(function () {
				$(this).removeClass('hasDatepicker');
			});

			const startDateSelectors =
				'.wpsp-date-published-date, .wpsp-datetime-local-event-start-date, .wpsp-date-start-date, .wpsp-datetime-local-start-date';
			const endDateSelectors =
				'.wpsp-date-modified-date, .wpsp-datetime-local-event-end-date, .wpsp-date-end-date, .wpsp-datetime-local-end-date';

			$(document).on('focus', '.wpsp-time-duration-field', function () {
				$(this).timepicker({
					timeFormat: 'HH:mm:ss',
					hourMin: 0,
					hourMax: 99,
					oneLine: true,
					currentText: 'Clear',
					onSelect() {
						updateTimeFormat(this);
					},
				});
			});

			$(document).on(
				'focus',
				'.wpsp-datetime-local-field, .wpsp-date-field',
				function () {
					$(this).datetimepicker({
						dateFormat: 'yy-mm-dd',
						timeFormat: 'hh:mm TT',
						changeMonth: true,
						changeYear: true,
						showOn: 'focus',
						showButtonPanel: true,
						closeText: 'Done',
						currentText: 'Clear',
						yearRange: '-100:+10', // last hundred year
						onClose(dateText, inst) {
							const thisEle = '#' + inst.id;
							if (jQuery(thisEle).is(startDateSelectors)) {
								$(endDateSelectors).datetimepicker(
									'option',
									'minDate',
									new Date(dateText)
								);
							} else if (jQuery(thisEle).is(endDateSelectors)) {
								$(startDateSelectors).datetimepicker(
									'option',
									'maxDate',
									new Date(dateText)
								);
							}
							jQuery(thisEle)
								.parents('.wpsp-local-fields')
								.find('.wpsp-default-hidden-value')
								.val(dateText);
						},
					});
				}
			);

			$.datepicker._gotoToday = function (id) {
				$(id).datepicker('setDate', '').datepicker('hide').blur();
			};

			function updateTimeFormat(thisEle) {
				const durationWrap = $(thisEle).closest(
					'.aiosrs-pro-custom-field-time-duration'
				);
				const inputField = durationWrap.find('.time-duration-field');
				let value = $(thisEle).val();
				value = value.replace(/:/, 'H');
				value = value.replace(/:/, 'M');
				value = 'PT' + value + 'S';
				inputField.val(value);

				// Post/pages related support.
				const parent = $(thisEle).parents('.wpsp-local-fields');
				parent.find('.wpsp-default-hidden-value').val(value);
			}
		},
	};

	/* Initializes the AIOSRS Schema. */
	$(function () {
		AIOSRSSchema.init();

		if (!$('body').hasClass('aiosrs-pro-setup')) {
			AIOSRSSchema.init_date_time_fields();
		}
	});

	$(document).ready(function () {
		let parent = $('.aiosrs-pro-meta-fields-wrap');
		parent.each(function (index, value) {
			let labelMarkup = $(value).find('.wpsp-field-label');
			let label = labelMarkup.text();
			if ('Image License' === label.trim()) {
				labelMarkup.attr('style', 'width:6%');
			}
		});
		$('#bsf-aiosrs-review-schema-type').change(function () {
			const itemVal = $(this).val().trim();
			if (!itemVal) {
				$('.bsf-review-item-type-field').remove();
				return;
			}
			AIOSRSSchema.get_review_item_type_html(itemVal);
		});
		$('#bsf-aiosrs-review-schema-type').change();

		AIOSRSSchema.prepare_event_schmea_fields();
	});
})(jQuery);
