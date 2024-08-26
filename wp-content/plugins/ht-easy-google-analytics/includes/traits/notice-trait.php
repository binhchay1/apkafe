<?php
namespace Ht_Easy_Ga4;

trait Notice_Trait {

	/**
	 * The function `render_login_notice` is used to display different login notices based on the provided
	 * notice name.
	 *
	 * @param string $notice_name It can have two possible values: manually_set_tracking_id, insufficient_permission.
	 *
	 * @return void
	 */
	public function render_login_notice( $notice_name = '' ) {
		$icon             = '<svg viewBox="64 64 896 896" focusable="false" data-icon="info-circle" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm32 664c0 4.4-3.6 8-8 8h-48c-4.4 0-8-3.6-8-8V456c0-4.4 3.6-8 8-8h48c4.4 0 8 3.6 8 8v272zm-32-344a48.01 48.01 0 010-96 48.01 48.01 0 010 96z"></path></svg>';
		$message          = '';
		$classes          = 'htga4-desc-info';
		$login_url        = $this->get_auth_url();
		$login_again_text = __( 'Sign in Again', 'ht-easy-ga4' );

		if ( $notice_name === 'manually_set_tracking_id' ) {

			$message = __( 'To access analytical reports within your WordPress dashboard, you need to connect / authenticate with your Google Analytics account. <br>If you don\'t need to access the reports within the dashboard, <strong>manually insert your GA4 tracking ID below.</strong>', 'ht-easy-ga4' );

			printf(
				'<div class="%1$s"><span>%2$s</span> <span>%3$s</span></div>',
				esc_attr( $classes ),
				wp_kses_post( $icon ),
				wp_kses_post( $message )
			);

		} elseif ( $notice_name === 'insufficient_permission' ) {

			$message  = __( 'Our system has detected that the access permissions you granted earlier was <b>insufficient</b>. <br>Please Sign in again and make sure that you have granted access for <strong>\'See and download your Google Analytics data\'</strong> on the Google Authentication screen to display analytical reports.', 'ht-easy-ga4' );
			$classes .= ' htga4-warning';

			printf(
				'<div class="%1$s"><span>%2$s</span> <span>%3$s</span> <a href="%4$s" target="_blank">%5$s</a></div>',
				esc_attr( $classes ),
				wp_kses_post( $icon ),
				wp_kses_post( $message ),
				esc_url( $login_url ),
				esc_html( $login_again_text ),
			);

		}
	}
}
