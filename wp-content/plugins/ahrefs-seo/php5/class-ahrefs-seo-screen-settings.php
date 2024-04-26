<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Export\Export_Audit_Data;
use ahrefs\AhrefsSeo\Messages\Message;
/**
 * Settings screen class.
 */
class Ahrefs_Seo_Screen_Settings extends Ahrefs_Seo_Screen {

	const TAB_CONTENT     = 'content';
	const TAB_SCHEDULE    = 'schedule';
	const TAB_ANALYTICS   = 'analytics';
	const TAB_ACCOUNT     = 'account';
	const TAB_DATA        = 'my-audit-data';
	const CSV_DATA_EXPORT = 'export-data';
	/**
	 * Tabs of the Setting page.
	 *
	 * @var array<string,string> self::TAB_* const as keys, title as value.
	 */
	private $tabs = [];
	/**
	 * Updated message.
	 *
	 * @var string
	 */
	protected $updated = '';
	/**
	 * Error message, if any
	 *
	 * @var string|Message
	 */
	protected $error = '';
	/**
	 * Current options
	 *
	 * @var Settings_Any|null
	 */
	protected $settings = null;
	/**
	 * Constructor
	 *
	 * @param Ahrefs_Seo_View $view View instance.
	 */
	public function __construct( Ahrefs_Seo_View $view ) {
		parent::__construct( $view );
		$this->tabs  = [
			self::TAB_CONTENT   => __( 'Audit settings', 'ahrefs-seo' ),
			self::TAB_SCHEDULE  => __( 'Audit schedule', 'ahrefs-seo' ),
			self::TAB_ANALYTICS => __( 'Google accounts', 'ahrefs-seo' ),
			self::TAB_ACCOUNT   => __( 'Ahrefs account', 'ahrefs-seo' ),
			self::TAB_DATA      => __( 'My audit data', 'ahrefs-seo' ),
		];
		$this->error = isset( $_GET['error'] ) ? sanitize_text_field( wp_unslash( $_GET['error'] ) ) : '';
	}
	/**
	 * Process get and post requests.
	 */
	public function process_post_data() {
		parent::process_post_data();
		if ( isset( $_GET['disconnect-analytics'] ) || isset( $_GET['disconnect-ahrefs'] ) ) {
			if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
				Ahrefs_Seo_Analytics::maybe_disconnect( $this );
				Ahrefs_Seo_Api::maybe_disconnect( $this );
			} else {
				$this->error = Message::action_not_allowed();
			}
		}
		if ( isset( $_GET['action'] ) && self::CSV_DATA_EXPORT === $_GET['action'] && check_admin_referer( Export_Audit_Data::ACTION, 'a' ) ) {
			if ( current_user_can( Ahrefs_Seo::CAP_EXPORT_ZIP ) ) {
				$export = new Export_Audit_Data();
				if ( $export->export_data_zip() ) {
					exit;
				}
				$this->error = ! empty( $export->get_error() ) ? $export->get_error() : '';
			} else {
				$this->error = Message::action_not_allowed( __( 'Sorry, you are not allowed to export the content audit data.', 'ahrefs-seo' ) );
			}
		}
		// set options request.
		if ( ! empty( $_POST ) && check_admin_referer( $this->get_nonce_name() ) && current_user_can( Ahrefs_Seo::CAP_SETTINGS_MENU ) ) {
			switch ( $this->get_current_tab() ) {
				case self::TAB_CONTENT:
					if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_AUDIT_SAVE ) ) {
						$content       = new Ahrefs_Seo_Content_Settings();
						$this->updated = $content->set_options_from_request() ? __( 'Updated.', 'ahrefs-seo' ) : '';
						$content->approve_existing_cpt();
					} else {
						$this->error = Message::action_not_allowed();
					}
					break;
				case self::TAB_SCHEDULE:
					if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_SCHEDULE_SAVE ) ) {
						( new Content_Schedule() )->set_options_from_request();
						$delay = isset( $_POST['audit_cron_delay'] ) ? intval( $_POST['audit_cron_delay'] ) : 0;
						if ( $delay ) {
							( new Cron_Content_Fast() )->set_recurrence_time( $delay );
						}
						$this->updated = __( 'Updated.', 'ahrefs-seo' );
					} else {
						$this->error = Message::action_not_allowed();
					}
					break;
				case self::TAB_ANALYTICS:
					if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
						$this->settings = new Settings_Google();
						$this->settings->apply_options( $this );
					} else {
						$this->error = Message::action_not_allowed();
					}
					break;
				case self::TAB_ACCOUNT:
					if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
						if ( isset( $_POST['ahrefs_step'] ) ) {
							if ( empty( $_POST['ahrefs_code'] ) ) {
								$this->error = __( 'Please enter your authorization code', 'ahrefs-seo' );
							} else {
								$code         = sanitize_text_field( wp_unslash( $_POST['ahrefs_code'] ) );
								$ahrefs_token = Ahrefs_Seo_Token::get();
								$ahrefs_token->token_save( $code );
								$updated = $ahrefs_token->query_api_is_token_valid();
								if ( $updated ) {
									// reanalyze everything if new Ahrefs token value set.
									( new Snapshot() )->reset_backlinks_for_new_snapshot();
								} else {
									$this->error = $ahrefs_token->get_error() ?: __( 'The code is invalid', 'ahrefs-seo' );
								}
							}
						}
					} else {
						$this->error = Message::action_not_allowed();
					}
					break;
				case self::TAB_DATA:
					if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_DATA_SAVE ) ) {
						Ahrefs_Seo::allow_reports_set( ! empty( $_POST['allow_reports'] ) );
						if ( isset( $_POST['remove_data_present'] ) ) {
							( new Ahrefs_Seo_Uninstall() )->set_option_save_data( empty( $_POST['remove_data'] ) );
						}
						$this->updated = __( 'Updated.', 'ahrefs-seo' );
					} else {
						$this->error = Message::action_not_allowed();
					}
					break;
			}
			if ( isset( $_REQUEST['return'] ) && ( ! isset( $_REQUEST['ahrefs_step'] ) || isset( $_REQUEST['ahrefs_step'] ) && 1 !== (int) $_REQUEST['ahrefs_step'] ) && ( ! isset( $_REQUEST['analytics_step'] ) || isset( $_REQUEST['analytics_step'] ) && 1 !== (int) $_REQUEST['analytics_step'] ) ) {
				// return back to initial page, if it is not a step 1 of Google or Ahrefs account settings.
				Helper_Content::wp_redirect( add_query_arg( 'updated', 'true', sanitize_text_field( wp_unslash( $_REQUEST['return'] ) ) ) );
				die;
			}
		}
	}
	/**
	 * Register AJAX handlers for Settings screen.
	 * Must be overwritten.
	 */
	public function register_ajax_handlers() {
		add_action( 'wp_ajax_ahrefs_seo_options_ga_detect', [ $this, 'ajax_options_ga_detect' ] );
		add_action( 'wp_ajax_ahrefs_seo_options_gsc_detect', [ $this, 'ajax_options_gsc_detect' ] );
		add_action( 'wp_ajax_ahrefs_seo_options_new_cpt_tip_close', [ $this, 'ajax_options_new_cpt_tip_close' ] );
	}
	/**
	 * Autodetect of ga account.
	 * Event on button click.
	 */
	public function ajax_options_ga_detect() {
		Ahrefs_Seo::thread_id( 'ga_detect' );
		if ( check_ajax_referer( $this->get_nonce_name() ) && current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
			$result = Ahrefs_Seo_Analytics::get()->find_recommended_ga_id();
			wp_send_json_success( [ 'ga' => $result ] );
		}
	}
	/**
	 * Autodetect of gsc account.
	 * Event on button click.
	 */
	public function ajax_options_gsc_detect() {
		Ahrefs_Seo::thread_id( 'gsc_detect' );
		if ( check_ajax_referer( $this->get_nonce_name() ) && current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_SAVE ) ) {
			$result = Ahrefs_Seo_Analytics::get()->find_recommended_gsc_id();
			wp_send_json_success( [ 'gsc' => $result ] );
		}
	}
	/**
	 * Do not show more 'new custom post types detected' tip.
	 * Event on button click.
	 *
	 * @since 0.8.0
	 */
	public function ajax_options_new_cpt_tip_close() {
		Ahrefs_Seo::thread_id( 'cpt_tip_close' );
		if ( check_ajax_referer( $this->get_nonce_name() ) && current_user_can( Ahrefs_Seo::CAP_SETTINGS_AUDIT_SAVE ) ) {
			( new Ahrefs_Seo_Content_Settings() )->approve_existing_cpt();
			wp_send_json_success();
		}
	}
	/**
	 * Show content of Settings screen.
	 */
	public function show() {
		$this->view->show_part( 'loader/settings-begin' );
		$active_tab = $this->get_current_tab();
		switch ( $active_tab ) {
			case self::TAB_CONTENT:
				if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_AUDIT_VIEW ) ) {
					$settings              = new Ahrefs_Seo_Content_Settings();
					$vars                  = $this->get_template_vars();
					$vars['button_title']  = __( 'Save', 'ahrefs-seo' );
					$vars['updated_scope'] = $settings->is_scope_updated();
					$vars['new_cpt']       = $settings->has_new_cpt_for_tip();
					if ( $vars['updated_scope'] ) {
						$vars['updated'] = false;
					}
					$this->view->show( 'settings-content', __( 'Audit settings', 'ahrefs-seo' ), $vars, $this, 'settings' );
				} else {
					$this->view->show( 'settings-not-allowed', __( 'Audit settings', 'ahrefs-seo' ), $this->get_template_vars(), $this, 'settings' );
				}
				break;
			case self::TAB_SCHEDULE:
				if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_SCHEDULE_VIEW ) ) {
					$vars                 = $this->get_template_vars();
					$vars['button_title'] = __( 'Save', 'ahrefs-seo' );
					$this->view->show( 'settings-schedule', __( 'Audit schedule', 'ahrefs-seo' ), $vars, $this, 'settings' );
				} else {
					$this->view->show( 'settings-not-allowed', __( 'Audit schedule', 'ahrefs-seo' ), $this->get_template_vars(), $this, 'settings' );
				}
				break;
			case self::TAB_ANALYTICS:
				if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_VIEW ) ) {
					Content_Audit::audit_clean_scheduled_message();
					if ( is_null( $this->settings ) ) {
						$this->settings = new Settings_Google();
					}
					if ( is_string( $this->error ) && strlen( $this->error ) ) {
						$this->error = Message::google_generic_error( $this->error );
					}
					$this->settings->show_options( $this, $this->view, $this->error instanceof Message ? $this->error : null );
				} else {
					$this->view->show( 'settings-not-allowed', __( 'Connect Google Analytics & Search Console', 'ahrefs-seo' ), $this->get_template_vars(), $this, 'settings' );
				}
				break;
			case self::TAB_ACCOUNT:
				if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_ACCOUNTS_VIEW ) ) {
					Content_Audit::audit_clean_scheduled_message();
					$vars                    = $this->get_template_vars();
					$vars['disconnect_link'] = 'settings';
					$vars['header_class']    = $this->get_header_classes( [ 'settings', 'settings-ahrefs-account' ] );
					$this->view->show( 'settings-account', __( 'Ahrefs account', 'ahrefs-seo' ), $vars, $this, 'settings' );
				} else {
					$this->view->show( 'settings-not-allowed', __( 'Ahrefs account', 'ahrefs-seo' ), $this->get_template_vars(), $this, 'settings' );
				}
				break;
			case self::TAB_DATA:
				if ( current_user_can( Ahrefs_Seo::CAP_SETTINGS_DATA_SAVE ) || current_user_can( Ahrefs_Seo::CAP_EXPORT_GOOGLE_CONFIG ) || current_user_can( Ahrefs_Seo::CAP_EXPORT_ZIP ) ) {
					$vars = $this->get_template_vars();
					$this->view->show( 'settings-data', __( 'My audit data', 'ahrefs-seo' ), $vars, $this, 'settings' );
				} else {
					$this->view->show( 'settings-not-allowed', __( 'My audit data', 'ahrefs-seo' ), $this->get_template_vars(), $this, 'settings' );
				}
				break;
		}
		$this->view->show_part( 'loader/settings-end' );
	}
	/**
	 * Get template variables for view call
	 *
	 * @return array<string, mixed>
	 */
	public function get_template_vars() {
		return [
			'header_class' => $this->get_header_classes( [ 'settings' ] ),
			'active_tab'   => $this->get_current_tab(),
			'tabs'         => $this->tabs,
			'updated'      => $this->updated,
			'error'        => $this->error,
		];
	}
	/**
	 * Get current tab using parameter from request
	 *
	 * @global $_REQUEST
	 * @return string
	 */
	private function get_current_tab() {
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification,WordPress.VIP.SuperGlobalInputUsage.AccessDetected -- load parameters from opened page.
		if ( ! isset( $this->tabs[ $active_tab ] ) ) {
			$active_tab = self::TAB_CONTENT;
		}
		return $active_tab;
	}
}