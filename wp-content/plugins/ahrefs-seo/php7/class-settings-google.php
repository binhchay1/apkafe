<?php
declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Disconnect_Reason\Disconnect_Reason_Google;
use ahrefs\AhrefsSeo\Messages\Message;
use ahrefs\AhrefsSeo\Options\Advanced;
use ahrefs\AhrefsSeo\Options\Temporary_Code;

/**
 * Google account settings.
 */
class Settings_Google extends Settings_Any {

	/**
	 * Updated message.
	 *
	 * @var string
	 */
	protected $updated = '';
	/**
	 * Error message, if any
	 *
	 * @var string
	 */
	protected $error = '';

	/**
	 * Load options from request.
	 * Save proxy code if exists.
	 *
	 * @param Ahrefs_Seo_Screen $screen Screen instance.
	 *
	 * @return string|null Error message if any.
	 */
	public function apply_options( Ahrefs_Seo_Screen $screen ) : ?string {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- nonce already checked before this function call.
		$called_from_wizard = $screen instanceof Ahrefs_Seo_Screen_Wizard;

		$analytics      = Ahrefs_Seo_Analytics::get();
		$analytics_step = isset( $_REQUEST['analytics_step'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['analytics_step'] ) ) : '';
		if ( $analytics->get_data_tokens()->is_token_set() ) {
			// reset step.
			if ( ! $analytics->is_analytics_enabled() || ! $analytics->is_gsc_enabled() ) {
				$analytics_step = '1';
			} else {
				$analytics_step = '2';
			}
		}
		if ( isset( $_GET['action'] ) && ( 'google_oauth' === $_GET['action'] ) ) {
			if ( isset( $_GET['state'] ) ) {
				if ( ( new Temporary_Code( 'oauth2' ) )->verify_code( sanitize_text_field( wp_unslash( $_GET['state'] ) ) ) ) { // check the state parameter.
					// set code.
					$analytics_code = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
					$client_id      = isset( $_GET['client_id'] ) ? sanitize_text_field( wp_unslash( $_GET['client_id'] ) ) : null;
					if ( ! is_null( $client_id ) ) {
						$analytics->set_client_id( $client_id );
					}

					if ( '' !== $analytics_code ) {
						if ( ! $analytics->check_token( $analytics_code, $client_id ) ) {
							$this->error = $analytics->get_message(); // get error from current actions only.
							if ( '' === $this->error || strpos( $this->error, 'invalid_grant' ) ) {
								// replace empty or default message "Error fetching OAuth2 access token, message: 'invalid_grant: Malformed auth code.'".
								$this->error = __( 'Unable to authenticate.', 'ahrefs-seo' );
							}
						}
					}
				} else {
					$this->error = __( 'Authorization session expired', 'ahrefs-seo' );
				}
			}
		} elseif ( '2' === $analytics_step ) {

			// part 2: set ua_id.
			$analytics_ua_id        = isset( $_REQUEST['ua_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ua_id'] ) ) : '';
			$analytics_ua_name      = isset( $_REQUEST['ua_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ua_name'] ) ) : '';
			$analytics_ua_url       = isset( $_REQUEST['ua_url'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ua_url'] ?? '' ) ) : '';
			$analytics_gsc_site     = isset( $_REQUEST['gsc_site'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['gsc_site'] ?? '' ) ) : '';
			$adv_gsc_uses_uppercase = ! empty( $_REQUEST['gsc_uses_uppercase'] );
			$adv_ga_not_urlencoded  = ! empty( $_REQUEST['ga_not_urlencoded'] );
			$adv_ga_uses_full_url   = ! empty( $_REQUEST['ga_uses_full_url'] );
			$updated                = $analytics_ua_id !== $analytics->get_data_tokens()->get_ua_id();
			$updated_gsc            = $analytics_gsc_site !== $analytics->get_data_tokens()->get_gsc_site();
			( new Advanced() )->set_adv_options( $adv_gsc_uses_uppercase, $adv_ga_not_urlencoded, $adv_ga_uses_full_url );
			if ( '' !== $analytics_ua_id && '' !== $analytics_ua_name && '' !== $analytics_gsc_site ) {
				$analytics->set_ua( $analytics_ua_id, $analytics_ua_name, $analytics_ua_url, $analytics_gsc_site );
				Ahrefs_Seo::get()->initialized_set( null, true );
			}
			if ( '' !== $analytics_ua_id && '' !== $analytics_ua_name || '' !== $analytics_gsc_site ) {
				$analytics->set_ua( $analytics_ua_id, $analytics_ua_name, $analytics_ua_url, $analytics_gsc_site );
				Ahrefs_Seo::get()->initialized_set( null, true );
				if ( $called_from_wizard && $screen instanceof Ahrefs_Seo_Screen_Wizard ) {
					$screen->set_step_and_reload( 3 );
				}
			}
			if ( ! $called_from_wizard ) {
				if ( $updated_gsc && $analytics->is_gsc_set() ) {
					// reset keywords and positions if snapshot with 'new' status exists.
					( new Snapshot() )->reset_keywords_and_position_for_new_snapshot();
				}
				// reanalyze everything if new UA ID value set.
				if ( $updated ) {
					( new Snapshot() )->reset_ga_for_new_snapshot();
				}
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended -- nonce already checked before this function call.
		return $this->error ?: null;
	}

	/**
	 * Show options block.
	 *
	 * @param Ahrefs_Seo_Screen $screen Screen instance.
	 * @param Ahrefs_Seo_View   $view View instance.
	 * @param Message|null      $error Message with already happened error if any.
	 *
	 * @return void
	 */
	public function show_options( Ahrefs_Seo_Screen $screen, Ahrefs_Seo_View $view, ?Message $error = null ) : void {
		$analytics = Ahrefs_Seo_Analytics::get();
		$advanced  = new Advanced();
		$token_set = $analytics->get_data_tokens()->is_token_set();
		$vars      = $screen->get_template_vars();
		$is_wizard = ! ( $screen instanceof Ahrefs_Seo_Screen_Settings );
		if ( $is_wizard ) {
			$this->error = isset( $_REQUEST['error'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['error'] ) ) : $this->error;
		}
		// preselect accounts in the Wizard or just after new token set.
		$preselect = $screen instanceof Ahrefs_Seo_Screen_Wizard || ( $token_set && ( ! $analytics->is_ua_set() && ! $analytics->is_gsc_set() ) );
		// last line may disconnect an account, so get value again.
		$token_set                  = $analytics->get_data_tokens()->is_token_set();
		$vars['updated']            = $this->updated;
		$vars['is_wizard']          = $is_wizard;
		$vars['error']              = ( empty( $this->error ) && ! is_null( $error ) ) ? $error : $this->error;
		$vars['token_set']          = $token_set;
		$vars['no_ga']              = ! ( $token_set && $analytics->is_analytics_enabled() );
		$vars['no_gsc']             = ! ( $token_set && $analytics->is_gsc_enabled() );
		$vars['ga_has_account']     = $token_set && $analytics->is_analytics_has_accounts();
		$vars['gsc_uses_uppercase'] = $advanced->get_adv_gsc_uses_uppercase();
		$vars['ga_not_urlencoded']  = $advanced->get_adv_ga_not_urlencoded();
		$vars['ga_uses_full_url']   = $advanced->get_adv_ga_uses_full_url();
		$vars['preselect_accounts'] = $preselect;
		$template                   = $is_wizard ? 'wizard' : 'settings';
		$vars['button_title']       = $is_wizard ? __( 'Continue', 'ahrefs-seo' ) : __( 'Save', 'ahrefs-seo' );
		$vars['disconnect_link']    = $is_wizard ? 'wizard' : 'settings';

		$view->show( 'settings-google', __( 'Connect Google Analytics & Search Console', 'ahrefs-seo' ), $vars, $screen, $template );
	}

	/**
	 * Revoke access to Google account if state parameter is correct and tokens was set.
	 * Clean all other Google errors and set "revoked" message.
	 *
	 * @param Ahrefs_Seo_Screen $screen Current screen.
	 * @param string|null       $state_code State code if any.
	 *
	 * @return string|null Error message to show.
	 * @since 0.9.11
	 */
	public function revoke_access( Ahrefs_Seo_Screen $screen, ?string $state_code ) : ?string {
		if ( is_null( $state_code ) || ! ( new Temporary_Code( 'oauth2' ) )->verify_code( $state_code ) ) { // check the state parameter.
			return __( 'Authorization session expired', 'ahrefs-seo' );
		}
		if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
			$analytics = Ahrefs_Seo_Analytics::get();
			if ( $analytics->get_data_tokens()->is_token_set() ) {
				$analytics->disconnect();
				$analytics->set_message( '' );
				// We set the reason directly, but do not return error as result. So we do not show it twice.
				( new Disconnect_Reason_Google() )->save_reason( __( 'Access to your Google account was revoked.', 'ahrefs-seo' ) );
			}
		}
		return null;
	}
}
