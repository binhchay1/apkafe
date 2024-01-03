
(function($){

	/**
	 * AIOSRS Frontend
	 *
	 * @class WP_Schema_Pro_Frontend
	 * @since 1.0
	 */
	WP_Schema_Pro_Frontend = {
		
		/**
		 * Initializes a AIOSRS Frontend.
		 *
		 * @since 1.0
		 * @method init
		 */
		container: '',


		init: function() {

			var self = this;
			jQuery(document).on( 'click', '.aiosrs-rating-wrap .aiosrs-star-rating', function(e) {
				e.preventDefault();

				self.star_rating(this);
			});

			jQuery(document).on( 'mouseover', '.aiosrs-rating-wrap .aiosrs-star-rating', function(e) {
				e.preventDefault();
				self.hover_star_rating(this);
			});

			jQuery(document).on( 'mouseout', '.aiosrs-rating-wrap .aiosrs-star-rating-wrap', function(e) {
				e.preventDefault();
				if ( ! $(this).hasClass('disabled') ) {
					index = $(this).parent().find('.aiosrs-rating').text();
					self.update_stars( $(this), index );
				}
			});
		},

		hover_star_rating: function( field ) {
			var self   = this,
				parent = $(field).closest('.aiosrs-star-rating-wrap'),
				index  = $(field).data('index');

			if ( ! parent.hasClass('disabled') ) {
				self.update_stars( parent, index );
			}
		},

		update_stars: function( wrap, rating ) {

			var filled = ( rating > 5 ) ? 5 : ( ( rating < 0 ) ? 0 : parseInt(rating) ),
				half   = ( rating == filled || rating > 5 || rating < 0 ) ? 0 : 1;

			wrap.find('span').each(function(index, el) {
				$(this).removeClass('dashicons-star-filled dashicons-star-half dashicons-star-empty');
				if( index < filled ) {
					$(this).addClass('dashicons-star-filled');
				} else if( index == filled && half == 1 ) {
					$(this).addClass('dashicons-star-half');
				} else {
					$(this).addClass('dashicons-star-empty');
				}
			});
		},

		star_rating: function( field ) {
			var self      = this,
				schema_id = $(field).closest('.aiosrs-rating-wrap').data( 'schema-id' ),
				parent    = $(field).closest('.aiosrs-star-rating-wrap'),
				index     = $(field).data('index');

			if ( ! parent.hasClass('disabled') ) {

				self.update_stars( parent, index );
				parent.addClass('disabled');

				$.ajax({
					url: AIOSRS_Frontend.ajaxurl,
					type: 'POST',
					data: {
						action: 'aiosrs_user_rating',
						rating: index,
						schema_id: schema_id,
						post_id: AIOSRS_Frontend.post_id,
						nonce: AIOSRS_Frontend.user_rating_nonce
					}
				}).success(function( response ) {
					if( response['success'] == true ) {
						var summary_wrap = parent.next('.aiosrs-rating-summary-wrap'),
							rating       = response['rating'],
							avg_rating   = response['rating-avg'],
							review_count = response['review-count'];

						summary_wrap.find('.aiosrs-rating').text(avg_rating);
						summary_wrap.find('.aiosrs-rating-count').text(review_count);
						if( parent.next('.success-msg').length == 0 ) {
							parent.after('<span class="success-msg">'+ AIOSRS_Frontend.success_msg +'</span>');
						}
						setTimeout(function(){
							parent.parent().find('.success-msg').remove();
							parent.removeClass('disabled');
						}, 5000);
						self.update_stars( parent, rating );
					}
				});
			}
		}
	}

	/* Initializes the AIOSRS Frontend. */
	$(function(){

		WP_Schema_Pro_Frontend.init();
	});

})(jQuery);