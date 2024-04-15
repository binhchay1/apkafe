<?php
/**
 * The review admin notice.
 *
 * @since        2.0.4
 * @version      2.0.4
 *
 * @package    Easy_Accordion_Free
 * @subpackage Easy_Accordion_Free/admin/views/notices
 * @author     ShapedPlugin<support@shapedplugin.com>
 */

/**
 * Admin review notice class.
 */
class Easy_Accordion_Free_Review {

	/**
	 * Display admin notice.
	 *
	 * @return void
	 */
	public function display_admin_notice() {
		// Show only to Admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Variable default value.
		$review = get_option( 'sp_eafree_review_notice_dismiss' );
		$time   = time();
		$load   = false;

		if ( ! $review ) {
			$review = array(
				'time'      => $time,
				'dismissed' => false,
			);
			add_option( 'sp_eafree_review_notice_dismiss', $review );
		} else {
			// Check if it has been dismissed or not.
			if ( ( isset( $review['dismissed'] ) && ! $review['dismissed'] ) && ( isset( $review['time'] ) && ( ( $review['time'] + ( DAY_IN_SECONDS * 3 ) ) <= $time ) ) ) {
				$load = true;
			}
		}

		// If we cannot load, return early.
		if ( ! $load ) {
			return;
		}
		?>
		<div id="sp-eafree-review-notice" class="sp-eafree-review-notice">
			<div class="sp-eafree-plugin-icon">
				<img src="<?php echo esc_url( SP_EA_URL . 'admin/css/images/eap-256.svg' ); ?>" alt="Easy Accordion">
			</div>
			<div class="sp-eafree-notice-text">
				<h3>Enjoying <strong>Easy Accordion</strong>?</h3>
				<p>We hope you had a wonderful experience using <strong>Easy Accordion</strong>. Please take a moment to leave a review on <a href="https://wordpress.org/support/plugin/easy-accordion-free/reviews/?filter=5#new-post" target="_blank"><strong>WordPress.org</strong></a>.
				Your positive review will help us improve. Thank you! 😊</p>

				<p class="sp-eafree-review-actions">
					<a href="https://wordpress.org/support/plugin/easy-accordion-free/reviews/?filter=5#new-post" target="_blank" class="button button-primary notice-dismissed rate-easy-accordion">Ok, you deserve ★★★★★</a>
					<a href="#" class="notice-dismissed remind-me-later"><span class="dashicons dashicons-clock"></span>Nope, maybe later
</a>
					<a href="#" class="notice-dismissed never-show-again"><span class="dashicons dashicons-dismiss"></span>Never show again</a>
				</p>
			</div>
		</div>

		<script type='text/javascript'>

			jQuery(document).ready( function($) {
				$(document).on('click', '#sp-eafree-review-notice.sp-eafree-review-notice .notice-dismissed', function( event ) {
					if ( $(this).hasClass('rate-easy-accordion') ) {
						var notice_dismissed_value = "1";
					}
					if ( $(this).hasClass('remind-me-later') ) {
						var notice_dismissed_value =  "2";
						event.preventDefault();
					}
					if ( $(this).hasClass('never-show-again') ) {
						var notice_dismissed_value =  "3";
						event.preventDefault();
					}

					$.post( ajaxurl, {
						action: 'sp-eafree-never-show-review-notice',
						notice_dismissed_data : notice_dismissed_value,
						nonce: '<?php echo esc_attr( wp_create_nonce( 'sp_eafree_review_notice' ) ); ?>'
					});

					$('#sp-eafree-review-notice.sp-eafree-review-notice').hide();
				});
			});

		</script>
		<?php
	}

	/**
	 * Dismiss review notice
	 *
	 * @since  2.0.4
	 *
	 * @return void
	 **/
	public function dismiss_review_notice() {
		$post_data = wp_unslash( $_POST );
		$review    = get_option( 'sp_eafree_review_notice_dismiss' );

		if ( ! isset( $post_data['nonce'] ) || ! wp_verify_nonce( sanitize_key( $post_data['nonce'] ), 'sp_eafree_review_notice' ) ) {
			return;
		}

		if ( ! $review ) {
			$review = array();
		}
		switch ( isset( $post_data['notice_dismissed_data'] ) ? $post_data['notice_dismissed_data'] : '' ) {
			case '1':
				$review['time']      = time();
				$review['dismissed'] = true;
				break;
			case '2':
				$review['time']      = time();
				$review['dismissed'] = false;
				break;
			case '3':
				$review['time']      = time();
				$review['dismissed'] = true;
				break;
		}
		update_option( 'sp_eafree_review_notice_dismiss', $review );
		die;
	}
}
