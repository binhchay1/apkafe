/* eslint-env jquery */
(function ($) {
	/**
	 * AIOSRS Frontend
	 *
	 * @class WPSchemaProFrontend
	 * @since 1.0
	 */
	const WPSchemaProFrontend = {
		/**
		 * Initializes a AIOSRS Frontend.
		 *
		 * @since 1.0
		 * @function init
		 */
		container: '',

		init() {
			const self = this;
			jQuery(document).on(
				'click',
				'.aiosrs-rating-wrap .aiosrs-star-rating',
				function (e) {
					e.preventDefault();

					self.star_rating(this);
				}
			);

			jQuery(document).on(
				'mouseover',
				'.aiosrs-rating-wrap .aiosrs-star-rating',
				function (e) {
					e.preventDefault();
					self.hover_star_rating(this);
				}
			);

			jQuery(document).on(
				'mouseout',
				'.aiosrs-rating-wrap .aiosrs-star-rating-wrap',
				function (e) {
					e.preventDefault();
					if (!$(this).hasClass('disabled')) {
						const index = $(this)
							.parent()
							.find('.aiosrs-rating')
							.text();
						self.update_stars($(this), index);
					}
				}
			);
		},

		hover_star_rating(field) {
			const self = this,
				parent = $(field).closest('.aiosrs-star-rating-wrap'),
				index = $(field).data('index');

			if (!parent.hasClass('disabled')) {
				self.update_stars(parent, index);
			}
		},

		update_stars(wrap, rating) {
			let filled = rating > 5 ? 5 : parseInt(rating);

			if (rating > 5) {
				filled = 5;
			} else if (rating < 0) {
				filled = 0;
			} else {
				filled = parseInt(rating);
			}
			const half = rating === filled || rating > 5 || rating < 0 ? 0 : 1;
			wrap.find('span').each(function (index) {
				$(this).removeClass(
					'dashicons-star-filled dashicons-star-half dashicons-star-empty'
				);
				if (index < filled) {
					$(this).addClass('dashicons-star-filled');
				} else if (index === filled && half === 1) {
					$(this).addClass('dashicons-star-empty');
				} else {
					$(this).addClass('dashicons-star-empty');
				}
			});
		},

		star_rating(field) {
			const self = this,
				schemaId = $(field)
					.closest('.aiosrs-rating-wrap')
					.data('schema-id'),
				parent = $(field).closest('.aiosrs-star-rating-wrap'),
				index = $(field).data('index');

			if (!parent.hasClass('disabled')) {
				self.update_stars(parent, index);
				parent.addClass('disabled');

				$.ajax({
					url: AIOSRS_Frontend.ajaxurl,
					type: 'POST',
					data: {
						action: 'aiosrs_user_rating',
						rating: index,
						schemaId,
						post_id: AIOSRS_Frontend.post_id,
						nonce: AIOSRS_Frontend.user_rating_nonce,
					},
				}).success(function (response) {
					if (response.success === true) {
						const summaryWrap = parent.next(
							'.aiosrs-rating-summary-wrap'
						),
							rating = response.rating,
							avgRating = response['rating-avg'],
							reviewCount = response['review-count'];

						summaryWrap.find('.aiosrs-rating').text(avgRating);
						summaryWrap
							.find('.aiosrs-rating-count')
							.text(reviewCount);
						if (parent.next('.success-msg').length === 0) {
							parent.after(
								'<span class="success-msg">' +
								AIOSRS_Frontend.success_msg +
								'</span>'
							);
						}
						setTimeout(function () {
							parent.parent().find('.success-msg').remove();
							parent.removeClass('disabled');
						}, 5000);
						self.update_stars(parent, rating);
					}
				});
			}
		},
	};

	/* Initializes the AIOSRS Frontend. */
	$(function () {
		WPSchemaProFrontend.init();
	});
})(jQuery);
