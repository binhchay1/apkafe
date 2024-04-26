<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Content_Tips\Events;
use ahrefs\AhrefsSeo\Content_Tips\Tips;
use ahrefs\AhrefsSeo\Export\Export_Audit_Data;
use ahrefs\AhrefsSeo\Keywords\Data_Keyword;
use ahrefs\AhrefsSeo\Messages\Message;
use ahrefs\AhrefsSeo\Third_Party\Sources;
use ahrefs\AhrefsSeo\Workers\Worker;
/**
 * Content audit screen class.
 */
class Ahrefs_Seo_Screen_Content extends Ahrefs_Seo_Screen_With_Table {

	const ACTION_NEW_AUDIT_RUN = 'ahrefs_seo_screen_new_audit_run';
	const CSV_TAB_EXPORT       = 'export-csv';
	/**
	 * Register ajax handlers
	 */
	public function register_ajax_handlers() {
		add_action( 'wp_ajax_ahrefs_seo_content_details', [ $this, 'ajax_content_details' ] );
		add_action( 'wp_ajax_ahrefs_seo_content_set_active', [ $this, 'ajax_content_set_active' ] );
		add_action( 'wp_ajax_ahrefs_seo_content_bulk', [ $this, 'ajax_bulk_actions' ] );
		add_action( 'wp_ajax_ahrefs_seo_content_ping', [ $this, 'ajax_content_ping' ] );
		add_action( 'wp_ajax_ahrefs_seo_content_manual_update', [ $this, 'ajax_content_manual_update' ] );
		add_action( 'wp_ajax_ahrefs_content_set_keyword', [ $this, 'ajax_content_set_keyword' ] );
		add_action( 'wp_ajax_ahrefs_seo_content_approve_keyword', [ $this, 'ajax_content_approve_keyword' ] );
		add_action( 'wp_ajax_ahrefs_content_get_keyword_popup', [ $this, 'ajax_content_get_keyword_popup' ] );
		add_action( 'wp_ajax_ahrefs_content_get_fresh_suggestions', [ $this, 'ajax_content_get_fresh_suggestions' ] );
		add_action( 'wp_ajax_ahrefs_seo_content_refdomains_update', [ $this, 'ajax_content_refdomains_update' ] );
		add_action( 'wp_ajax_ahrefs_seo_content_tip_close', [ $this, 'ajax_content_tip_close' ] );
		add_filter( 'heartbeat_received', [ $this, 'ajax_receive_heartbeat' ], 10, 2 );
		parent::register_ajax_handlers();
	}
	/**
	 * Process CSV export request
	 *
	 * @since 0.9.4
	 */
	public function process_post_data() {
		parent::process_post_data();
		if ( isset( $_GET['action'] ) && self::CSV_TAB_EXPORT === $_GET['action'] && check_admin_referer( Export_Audit_Data::ACTION, 'a' ) ) {
			$export = new Export_Audit_Data();
			if ( current_user_can( Ahrefs_Seo::CAP_EXPORT_CSV ) ) {
				$this->initialize_table();
				// the tab for export.
				$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
				// the visible columns list, id => title.
				$columns = $this->get_visible_columns();
				if ( $export->export_data_tab( $tab, $columns ) ) {
					exit;
				}
				$error = ! empty( $export->get_error() ) ? $export->get_error() : '';
			} else {
				$error = __( 'Sorry, you are not allowed to export the content audit data.', 'ahrefs-seo' );
			}
			Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( sprintf( 'Error during CSV export: %s(%s)', __METHOD__, $error ), 0 ) );
		}
	}
	/**
	 * Show a page
	 */
	public function show() {
		if ( current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_VIEW ) ) {
			if ( isset( $_POST['run_new_audit'] ) && current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_RUN ) && check_admin_referer( self::ACTION_NEW_AUDIT_RUN, 'scope_updated_nonce' ) ) {
				$this->new_audit_run( false );
			}
			$content_audit = new Content_Audit();
			// if audit paused, check, maybe some plugin already deactivated and we can run?
			if ( Content_Audit::audit_is_paused() && $content_audit->require_update() ) {
				Ahrefs_Seo_Compatibility::recheck_saved_incompatibility();
				if ( empty( Content_Audit::audit_get_paused_messages() ) ) { // no saved reasons exists.
					Content_Audit::audit_resume();
				}
			}
			$params = [
				'last_audit_stopped' => Content_Audit::audit_get_paused_messages( true ), // Message[] or null type.
				'stop'               => Ahrefs_Seo_Errors::check_stop_status( false ), // Message[] or null type.
				'header_class'       => $this->get_header_classes( [ 'content' ] ),
				'is_first_audit'     => $content_audit->show_first_audit(),
			];
			// manage new 'added since' items.
			( new Ahrefs_Seo_Data_Content() )->update_added_since_items();
			$this->view->show( 'content', __( 'Content Audit', 'ahrefs-seo' ), $params, $this, 'content' );
		}
	}
	/**
	 * Get prefix for ajax requests to tables
	 *
	 * @return string
	 */
	protected function get_ajax_table_prefix() {
		return 'ahrefs_seo_table_content';
	}
	/**
	 * Create new table
	 *
	 * @return Ahrefs_Seo_Table_Content
	 */
	protected function new_table_instance() {
		return new Ahrefs_Seo_Table_Content();
	}
	/**
	 * Print navigation and placeholder for future table
	 *
	 * @return void
	 */
	public function show_table_placeholder() {
		if ( ! is_null( $this->table ) ) {
			?>
			<a id="view-table"></a>
			<div class="wrap content-wrap">
				<form id="content_form" class="table-form wp-clearfix" method="get">
					<div class="tabs-wrap">
						<?php
						$this->table->views();
						$this->view->show_part( 'content/export', [ 'tab' => $this->table->get_current_tab() ] );
						?>
					</div>
					<div class="clear"></div>

					<div class="table-wrap">
						<div id="table_loader" style="display: none;"><div class="loader"></div></div>
						<div id="content_table">
							<!-- place for table -->
						</div>
					</div>
					<?php
					wp_nonce_field( Ahrefs_Seo_Table::ACTION, 'table_nonce' );
					?>
					<input type="hidden" id="current_lang" value="<?php echo esc_attr( ! empty( Helper_Content::get()->get_lang( true ) ) ? Helper_Content::get()->get_lang( true ) : '' ); ?>">
				</form>
			</div>
			<?php
		}
	}
	/**
	 * Ajax handler for content of expanded view with recommended actions
	 *
	 * @return void
	 */
	public function ajax_content_details() {
		Ahrefs_Seo::thread_id( 'suggestions' );
		if ( check_ajax_referer( Ahrefs_Seo_Table::ACTION ) && current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_VIEW ) && isset( $_REQUEST['id'] ) ) {
			$post_tax_string = sanitize_text_field( wp_unslash( $_REQUEST['id'] ) );
			$post_tax        = Post_Tax::create_from_string( $post_tax_string );
			if ( $post_tax->is_tax_or_published() ) {
				$action = ! empty( $post_tax->load_action() ) ? $post_tax->load_action() : Ahrefs_Seo_Data_Content::ACTION4_ADDED_SINCE_LAST;
				if ( in_array( $action, [ Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_INITIAL, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_INITIAL, Ahrefs_Seo_Data_Content::ACTION4_ANALYZING_FINAL, Ahrefs_Seo_Data_Content::ACTION4_OUT_OF_SCOPE_ANALYZING ], true ) ) {
					$action = Ahrefs_Seo_Data_Content::ACTION4_ANALYZING; // substitute correct template for initial actions.
				}
				ob_start();
				$this->view->show_part( 'actions/' . $action, [ 'post_tax' => $post_tax ] );
				$result = ob_get_clean();
			} else {
				$result = __( 'This page cannot be found. It is possible that you’ve archived the page or changed the page ID. Please reload the page & try again.', 'ahrefs-seo' );
			}
			wp_send_json_success( $result );
		}
	}
	/**
	 * Handler for 'Run Content Audit again'.
	 * Created new snapshot if it was not exists.
	 *
	 * @return void
	 */
	public function ajax_content_manual_update() {
		Ahrefs_Seo::thread_id( 'run_manual' );
		if ( check_ajax_referer( Ahrefs_Seo_Table::ACTION ) ) {
			if ( current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_RUN ) ) {
				$this->new_audit_run( true );
			} else {
				wp_send_json_error( [ 'error' => __( 'Sorry, you are not allowed to run content audits.', 'ahrefs-seo' ) ] );
			}
		}
	}
	/**
	 * Try to run new audit
	 *
	 * @since 0.8.0
	 *
	 * @param bool $exit_with_json_answer Output JSON answer and terminate execution.
	 * @return void
	 */
	private function new_audit_run( $exit_with_json_answer ) {
		if ( ! current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_RUN ) ) {
			$message = Message::action_not_allowed( __( 'Sorry, you are not allowed to run content audits.', 'ahrefs-seo' ) );
			if ( $exit_with_json_answer ) {
				wp_send_json_error( [ 'tips' => $message ] );
			} else {
				Ahrefs_Seo_Errors::save_message( 'WordPress', $message->get_text(), Message::TYPE_ERROR_SINGLE );
			}
			return;
		}
		// check compatibility issues.
		$tips = [];
		Ahrefs_Seo_Api::get()->get_subscription_info(); // update Ahrefs account details (update 'is limited account' value).
		if ( ! Ahrefs_Seo_Errors::has_stop_error( true ) ) {
			$snapshots = new Snapshot();
			if ( is_null( $snapshots->get_new_snapshot_id() ) ) {
				$snapshots->create_new_snapshot();
				if ( $exit_with_json_answer ) {
					$fields = [ 'ok' => true ];
					if ( ! empty( $_POST['info'] ) && current_user_can( Ahrefs_Seo::CAP_ROLE_ADMIN ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce already checked before this method called.
						$fields['_'] = Ahrefs_Seo::get_breadcrumbs_data(); // return breadcrumbs details for debugging purposes.
					}
					wp_send_json_success( $fields );
				}
				return;
			}
		} else {
			if ( ! $exit_with_json_answer ) {
				return;
			}
			// fill message with all stop errors.
			ob_start();
			Ahrefs_Seo_Errors::show_stop_errors( Ahrefs_Seo_Errors::check_stop_status( true ) );
			$tips['stop'] = ob_get_clean();
			wp_send_json_error( [ 'tips' => $tips ] );
		}
		if ( ! $exit_with_json_answer ) {
			return;
		}
		ob_start();
		$this->view->show_part( 'content-tips/already-run' );
		$tips['stop'] = ob_get_clean();
		wp_send_json_error( [ 'tips' => $tips ] );
	}
	/**
	 * Bulk actions handler.
	 *
	 * @return void
	 */
	public function ajax_bulk_actions() {
		Ahrefs_Seo::thread_id( 'bulk_actions' );
		if ( check_ajax_referer( Ahrefs_Seo_Table::ACTION ) && current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_VIEW ) && isset( $_REQUEST['doaction'] ) && isset( $_REQUEST['ids'] ) && is_array( $_REQUEST['ids'] ) ) {
			$doaction = sanitize_text_field( wp_unslash( $_REQUEST['doaction'] ) );
			$ids      = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['ids'] ) );
			$this->bulk_actions( $doaction, Post_Tax::create_from_strings( $ids ) );
		}
	}
	/**
	 * Receive ping message with items shown at the current moment and their versions ('ver' field of query).
	 * Run update of all pending items.
	 * Check current version of items and return all updated items (rows) at the 'data.updated' field of json answer.
	 * If 'data.status' is true - should run next ajax request.
	 * If nothing updated, simply return json success.
	 */
	public function ajax_content_ping() {
		Ahrefs_Seo::breadcrumbs( __METHOD__ );
		Ahrefs_Seo::thread_id( 'ping' );
		if ( check_ajax_referer( Ahrefs_Seo_Table::ACTION ) && current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_VIEW ) && isset( $_REQUEST['items'] ) && ( is_array( $_REQUEST['items'] ) || 'false' === $_REQUEST['items'] ) ) {
			$fields            = [];
			$fields['timeout'] = 2 + ( intval( ini_get( 'max_execution_time' ) ) ?: 120 ); // set this value before update_table call (it can modify default value).
			$stop = isset( $_POST['stop'] ) ? sanitize_text_field( wp_unslash( $_POST['stop'] ) ) : ''; // what tip is displayed in stop block.
			$content               = Ahrefs_Seo_Data_Content::get();
			$content_audit         = new Content_Audit();
			$content_audit_current = new Content_Audit_Current();
			$something_updated     = true; // send new request by default.
			$cancel_audit          = ! empty( $_POST['cancel_audit'] );
			if ( $cancel_audit ) { // try to cancel audit.
				if ( current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_RUN ) ) {
					Content_Audit::audit_cancel();
				}
			} elseif ( ! empty( $_POST['unpause_audit'] ) ) { // try to resume audit.
				if ( current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_RUN ) ) {
					Content_Audit::audit_resume();
				}
			}
			if ( ! $cancel_audit && ! Content_Audit::audit_is_paused() ) { // if not paused or cancelled.
				// 1. Do update if we have any pending items.
				// return value is true if we updated something and can run next request from JS.
				$something_updated = $content_audit_current->maybe_update();
				$waiting_time      = $content_audit_current->get_waiting_time(); // from current snapshot.
				if ( ! $something_updated ) {
					$something_updated = $content_audit->update_table();
					$waiting_time_new  = $content_audit->get_waiting_time(); // from new snapshot.
					if ( ! is_null( $waiting_time_new ) && ( is_null( $waiting_time ) || $waiting_time_new > $waiting_time ) ) {
						$waiting_time = $waiting_time_new;
					}
				} elseif ( is_null( $waiting_time ) ) {
					$waiting_time = $content_audit->get_waiting_time(); // from new snapshot.
				}
				$fields['paused'] = Content_Audit::audit_is_paused(); // update after audit executed.
			} else {
				$waiting_time     = 2 * MINUTE_IN_SECONDS;
				$fields['paused'] = true;
				if ( $cancel_audit ) {
					$fields['reload'] = 1;
				}
			}
			if ( is_null( $waiting_time ) ) {
				$waiting_time = max( [ 10, Worker::get_min_waiting_time() ] );
			}
			$fields['delayed']      = $fields['paused'] ? false : Content_Audit::audit_is_delayed(); // do not show "audit delayed" message when audit is paused.
			$fields['new-request']  = $something_updated || ( new Content_Audit_Current() )->require_update() || $content_audit->require_update();
			$fields['audit']        = Ahrefs_Seo_Data_Content::get()->get_statistics();
			$fields['waiting_time'] = round( $waiting_time, 1 ); // All active workers are paused during this amount of time.
			$fields['tips'] = $this->prepare_tips( $stop );
			// 2. Return updated items.
			$post_tax_strings_with_ver = []; // array with [ post_tax_string => version_string ] items.
			if ( is_array( $_REQUEST['items'] ) ) {
				foreach ( $_REQUEST['items'] as $key => $value ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- we are sanitizing both key and value one line below as string and integers.
					$post_tax_strings_with_ver[ sanitize_text_field( wp_unslash( $key ) ) ] = intval( $value ); // key is post_tax_string, value is (int)version.
				}
			}
			$updated_ids = count( $post_tax_strings_with_ver ) ? $content->get_updated_items( $post_tax_strings_with_ver ) : [];
			// is the current snapshot different from displayed one?
			if ( $this->is_current_snapshot_different( $post_tax_strings_with_ver ) ) {
				$fields['reload'] = 1;
			}
			unset( $post_tax_strings_with_ver );
			$charts = Ahrefs_Seo_Charts::maybe_return_charts();
			if ( ! empty( $_POST['info'] ) && current_user_can( Ahrefs_Seo::CAP_ROLE_ADMIN ) ) {
				$fields['_'] = Ahrefs_Seo::get_breadcrumbs_data(); // return breadcrumbs details for debugging purposes.
			}
			if ( count( $updated_ids ) || ! empty( $charts ) ) {
				// this will return ajax response and terminate.
				if ( ! empty( $charts ) ) {
					$fields['charts'] = $charts;
				}
				$this->initialize_table()->ajax_response_updated( $updated_ids, $fields );
			}
			// Nothing to update: send default ajax response with success and maybe messages.
			wp_send_json_success( $fields );
		}
	}
	/**
	 * Is the current snapshot different from snapshot in parameters?
	 *
	 * @since 0.8.0
	 *
	 * @param array<string,int> $updated_ids Updated post tax strings list with version info: array [ post_tax_string => version_string ].
	 * @return bool
	 */
	private function is_current_snapshot_different( array $updated_ids ) {
		if ( count( $updated_ids ) ) {
			$post_tax_string = array_key_first( $updated_ids ); // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.array_key_firstFound -- symfony/polyfill-php73 define the array_key_first function.
			if ( $post_tax_string ) {
				$post_tax = Post_Tax::create_from_string( "{$post_tax_string}" );
				return $post_tax->get_snapshot_id() !== ( new Snapshot() )->get_current_snapshot_id();
			}
		}
		return false;
	}
	/**
	 * Start / stop post analyzing handler.
	 *
	 * @return void
	 */
	public function ajax_content_set_active() {
		Ahrefs_Seo::thread_id( 'set_active' );
		if ( check_ajax_referer( Ahrefs_Seo_Table::ACTION ) && current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_VIEW ) && isset( $_REQUEST['id'] ) && ( isset( $_REQUEST['active'] ) || isset( $_REQUEST['recheck'] ) ) ) {
			$success  = true;
			$result   = __( 'Done.', 'ahrefs-seo' );
			$errors   = [];
			$post_tax = Post_Tax::create_from_string( sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) );
			if ( $post_tax->is_tax_or_published() ) {
				if ( $post_tax->user_can_manage() ) {
					$result  = __( 'Will be soon.', 'ahrefs-seo' );
					$content = Ahrefs_Seo_Data_Content::get();
					if ( isset( $_REQUEST['recheck'] ) && '' !== $_REQUEST['recheck'] ) {
						if ( 0 === count( $content->posts_recheck( [ $post_tax ] ) ) ) {
							$result = __( 'Post status is rechecked.', 'ahrefs-seo' );
						} else {
							$errors[] = __( 'Post is not eligible to status recheck.', 'ahrefs-seo' );
							$success  = false;
						}
					} elseif ( isset( $_REQUEST['active'] ) && '' !== $_REQUEST['active'] ) {
						if ( ! empty( $_REQUEST['active'] ) ) {
							if ( 0 === count( $content->posts_include( [ $post_tax ] ) ) ) {
								$result = __( 'Post is included in the audit.', 'ahrefs-seo' );
							} else {
								$errors[] = __( 'This post is already included in the audit.', 'ahrefs-seo' );
								$success  = false;
							}
						} else {
							if ( 0 === count( $content->posts_exclude( [ $post_tax ] ) ) ) {
								$result = __( 'Post is excluded from the audit.', 'ahrefs-seo' );
							} else {
								$errors[] = __( 'This post is already excluded from the audit.', 'ahrefs-seo' );
								$success  = false;
							}
						}
					}
				} else {
					$errors[] = __( 'Sorry, you do not have sufficient permissions to update the page.', 'ahrefs-seo' );
					$success  = false;
				}
			} else {
				$errors[] = __( 'This page cannot be found. It is possible that you’ve archived the page or changed the page ID. Please reload the page & try again.', 'ahrefs-seo' );
				$success  = false;
			}
			$fields            = [];
			$fields['message'] = $result;
			if ( $errors ) {
				$success            = false;
				$fields['messages'] = array_unique( $errors );
			}
			if ( $success ) {
				wp_send_json_success( $fields );
			} else {
				wp_send_json_error( $fields );
			}
		}
	}
	/**
	 * Handler for 'Select keyword' buttons and manual keyword field.
	 * Save keyword for post.
	 *
	 * @return void
	 */
	public function ajax_content_set_keyword() {
		Ahrefs_Seo::thread_id( 'set_keyword' );
		if ( check_ajax_referer( Ahrefs_Seo_Table::ACTION ) && current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_VIEW ) && isset( $_POST['post'] ) && isset( $_POST['keyword'] ) ) {
			$keyword        = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
			$keyword_manual = isset( $_POST['keyword_manual'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword_manual'] ) ) : null;
			$source_id      = isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : null;
			$not_approved   = ! empty( $_POST['not_approved'] );
			$post_tax       = Post_Tax::create_from_string( sanitize_text_field( wp_unslash( $_POST['post'] ) ) );
			if ( $post_tax->user_can_manage() ) {
				// use current snapshot ID instead of received.
				$post_tax->set_snapshot_id( ( new Content_Audit_Current() )->get_snapshot_id() );
				$result = Ahrefs_Seo_Keywords::get()->post_keywords_set( $post_tax, new Data_Keyword( $keyword, $source_id ), $keyword_manual, true, true, $post_tax->get_country_code() );
				if ( $result ) {
					if ( $not_approved && '' !== $keyword ) { // approve keyword.
						if ( ! Sources::is_source_imported( $source_id ) ) { // imported item can't be approved.
							( new Snapshot() )->analysis_approve_items( [ $post_tax ] );
						}
					} elseif ( '' === $keyword ) { // remove approvement from empty keyword.
						( new Snapshot() )->analysis_approve_items( [ $post_tax ], false );
					}
					wp_send_json_success();
				} else {
					wp_send_json_error( [ 'error' => __( 'This page cannot be found. It is possible that you’ve archived the page or changed the page ID. Please reload the page & try again.', 'ahrefs-seo' ) ] );
				}
			} else {
				wp_send_json_error( [ 'error' => __( 'Sorry, you do not have sufficient permissions to perform this action.', 'ahrefs-seo' ) ] );
			}
		}
	}
	/**
	 * Handler for 'Approve keyword' link.
	 * Approve keyword for post.
	 *
	 * @return void
	 */
	public function ajax_content_approve_keyword() {
		Ahrefs_Seo::thread_id( 'approve_keyword' );
		if ( check_ajax_referer( Ahrefs_Seo_Table::ACTION ) && current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_VIEW ) && isset( $_POST['post'] ) ) {
			$post_tax = Post_Tax::create_from_string( sanitize_text_field( wp_unslash( $_POST['post'] ) ) );
			if ( $post_tax->user_can_manage() ) {
				// approve keyword.
				( new Snapshot() )->analysis_approve_items( [ $post_tax ] );
				wp_send_json_success();
			} else {
				wp_send_json_error( [ 'message' => __( 'Sorry, you do not have sufficient permissions to update the page.', 'ahrefs-seo' ) ] );
			}
		}
	}
	/**
	 * Handler for 'Change keywords' buttons.
	 * Show html content of popup dialog.
	 *
	 * @return void
	 */
	public function ajax_content_get_keyword_popup() {
		Ahrefs_Seo::thread_id( 'keyword_popup' );
		if ( check_ajax_referer( Ahrefs_Seo_Table::ACTION ) && current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_VIEW ) && isset( $_REQUEST['ahpost'] ) ) {
			$post_tax = Post_Tax::create_from_string( sanitize_text_field( wp_unslash( $_REQUEST['ahpost'] ) ) );
			if ( $post_tax->user_can_manage() ) {
				$time = microtime( true );
				if ( ! isset( $GLOBALS['hook_suffix'] ) ) { // patch for notice when called using AJAX in wp-admin\includes\class-wp-screen.php .
					$GLOBALS['hook_suffix'] = ''; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				}
				$this->view->show_part( 'popups/keywords-list', [ 'post_tax' => $post_tax ] );
				Ahrefs_Seo::breadcrumbs( sprintf( 'Keywords for post %s found in %1.3f sec.', (string) $post_tax, microtime( true ) - $time ) );
			} else {
				$this->view->show_part(
					'popups/keywords-list',
					[
						'post_tax' => null,
						'error'    => Message::action_not_allowed(),
					]
				);
			}
			exit;
		}
	}
	/**
	 * Handler for get fresh keywords suggestion, when Keywords popup opened.
	 * Return json content with received from API fresh data.
	 *
	 * @return void
	 */
	public function ajax_content_get_fresh_suggestions() {
		Ahrefs_Seo::thread_id( 'fresh_suggestions' );
		if ( check_ajax_referer( Ahrefs_Seo_Table::ACTION ) && current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_VIEW ) && isset( $_REQUEST['post'] ) ) { // will check access to the post itself later.
			$post_tax = Post_Tax::create_from_string( sanitize_text_field( wp_unslash( $_REQUEST['post'] ) ) );
			if ( $post_tax->user_can_manage() ) {
				$time = microtime( true );
				Ahrefs_Seo::breadcrumbs( __METHOD__ );
				$data = Ahrefs_Seo_Keywords::get()->get_suggestions( $post_tax, false );
				Ahrefs_Seo::breadcrumbs( sprintf( 'Fresh keywords for post %s found in %1.3f sec.', (string) $post_tax, microtime( true ) - $time ) );
				wp_send_json_success( $data );
			} else {
				wp_send_json_success( [ 'errors' => [ __( 'Sorry, you do not have sufficient permissions to load fresh suggestions for this article.', 'ahrefs-seo' ) ] ] );
			}
		}
	}
	/**
	 * Handler for get fresh ref. domains number, when suggestion with ref.domains number opened.
	 * Return json content with received from API fresh data.
	 *
	 * @since 0.9.8
	 *
	 * @return void
	 */
	public function ajax_content_refdomains_update() {
		Ahrefs_Seo::thread_id( 'refdomains update' );
		if ( check_ajax_referer( Ahrefs_Seo_Table::ACTION ) && current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_VIEW ) && isset( $_REQUEST['post'] ) ) { // will check access to the post itself later.
			$post_tax = Post_Tax::create_from_string( sanitize_text_field( wp_unslash( $_REQUEST['post'] ) ) );
			Ahrefs_Seo::breadcrumbs( __METHOD__ );
			// user without credentials got cached value.
			$ref_domains = Ahrefs_Seo_Data_Content::get()->content_get_ref_domains_for_post( $post_tax, ! $post_tax->user_can_manage() );
			if ( ! is_null( $ref_domains ) && $ref_domains >= 0 ) {
				/* translators: %d: value */
				wp_send_json_success( [ 'ref_domains_text' => esc_html( sprintf( _n( '%d ref. domain', '%d ref. domains', $ref_domains, 'ahrefs-seo' ), $ref_domains ) ) ] );
			} else {
				wp_send_json_error( [ 'error' => esc_html__( 'Couldn’t fetch the number of ref. domains', 'ahrefs-seo' ) ] );
			}
		}
	}
	/**
	 * Handler for content audit suggested keywords tip.
	 * Called on close.
	 *
	 * @return void
	 */
	public function ajax_content_tip_close() {
		Ahrefs_Seo::thread_id( 'tip_close' );
		if ( check_ajax_referer( Ahrefs_Seo_Table::ACTION ) && current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_VIEW ) && isset( $_POST['tip_id'] ) ) {
			if ( is_array( $_POST['tip_id'] ) ) {
				$tip_ids = array_map( 'sanitize_text_field', wp_unslash( $_POST['tip_id'] ) );
			} else {
				$tip_ids = [ sanitize_text_field( wp_unslash( $_POST['tip_id'] ) ) ];
			}
			foreach ( $tip_ids as $tip_id ) {
				( new Events() )->on_closed_by_user( $tip_id );
			}
			wp_send_json_success();
		}
	}
	/**
	 * Do a bulk action and terminate execution
	 *
	 * @param string     $doaction Action.
	 * @param Post_Tax[] $post_taxes Post ids array.
	 * @return void Print JSON answer and terminate execution.
	 */
	public function bulk_actions( $doaction, array $post_taxes ) {
		$sendback = [];
		// check if some items are not allowed to modify.
		$results = array_filter(
			$post_taxes,
			function ( Post_Tax $post_tax ) {
				return ! $post_tax->user_can_manage();
			}
		);
		if ( $results ) {
			$results = array_map(
				function ( Post_Tax $post_tax ) {
					return esc_html( $post_tax->get_title( true ) );
				},
				$post_taxes
			);
			/* translators: 1: number of pages, 2: list of pages */
			$sendback['message2'] = sprintf( _n( 'Sorry, you do not have sufficient permissions to update %1$d page %2$s', 'Sorry, you do not have sufficient permissions to update %1$d pages	 %2$s', count( $results ), 'ahrefs-seo' ), count( $results ), '<br>・ ' . implode( '<br>・ ', $results ) );
			$post_taxes           = array_filter(
				$post_taxes,
				function ( Post_Tax $post_tax ) {
					return $post_tax->user_can_manage();
				}
			);
		}
		if ( count( $post_taxes ) ) {
			switch ( $doaction ) {
				case 'start':
					$content = Ahrefs_Seo_Data_Content::get();
					$results = $content->posts_include( $post_taxes );
					if ( $results ) {
						$results = array_map(
							function ( Post_Tax $post_tax ) {
								return $post_tax->get_title( true );
							},
							$results
						);
						/* translators: 1: number of pages, 2: list of pages */
						$sendback['message'] = sprintf( _n( 'This %1$d page is already included in the audit: %2$s', 'These %1$d pages are already included in the audit: %2$s.', count( $results ), 'ahrefs-seo' ), count( $results ), '<br>・ ' . implode( '<br>・ ', $results ) );
					}
					break;
				case 'stop':
					$content = Ahrefs_Seo_Data_Content::get();
					$results = $content->posts_exclude( $post_taxes );
					if ( $results ) {
						$results = array_map(
							function ( Post_Tax $post_tax ) {
								return $post_tax->get_title( true );
							},
							$results
						);
						/* translators: 1: number of pages, 2: list of pages */
						$sendback['message'] = sprintf( _n( 'This %1$d page is already excluded from the audit: %2$s', 'These %1$d page are already excluded from the audit: %2$s.', count( $results ), 'ahrefs-seo' ), count( $results ), '<br>・ ' . implode( '<br>・ ', $results ) );
					}
					break;
				case 'approve':
					( new Snapshot() )->analysis_approve_items( $post_taxes );
					break;
				default:
					wp_send_json_error( __( 'Unknown action.', 'ahrefs-seo' ) );
			}
		}
		wp_send_json_success( $sendback );
	}
	/**
	 * Return status if content audit update run required.
	 *
	 * @param array<string, mixed> $response The Heartbeat response.
	 * @param array<string, mixed> $data The $_POST data sent.
	 * @return array<string, mixed>
	 */
	public function ajax_receive_heartbeat( $response, $data ) {
		Ahrefs_Seo::thread_id( 'heartbeat' );
		// Callback, do not use data types.
		if ( ! empty( $data['ahrefs_seo_content'] ) ) {
			$need_update                    = ( new Content_Audit_Current() )->maybe_update() || ( new Content_Audit() )->require_update();
			$response['ahrefs_seo_content'] = compact( 'need_update' ); // not a string to translate.
		}
		return (array) $response;
	}
	/**
	 * Fill all tips.
	 *
	 * @param string $stop_displayed Tips already displayed in stop block.
	 *
	 * @return array<string,string|array|null> Null if nothing updated, string with html code otherwise.
	 * @since 0.7.5
	 */
	protected function prepare_tips( $stop_displayed = '' ) {
		$result = [];
		// show stop messages at the first: other messages are filtered by compatibility text.
		ob_start();
		// show compatibility message, if audit stopped (paused) because of it.
		$saved               = ! empty( Content_Audit::audit_get_paused_messages( true ) ) ? Content_Audit::audit_get_paused_messages( true ) : [];
		$status              = ! empty( Ahrefs_Seo_Errors::check_stop_status( false ) ) ? Ahrefs_Seo_Errors::check_stop_status( false ) : [];
		$need_to_clean_block = Ahrefs_Seo_Errors::show_stop_errors( array_merge( $saved, $status ), $stop_displayed );
		$result['stop']      = (string) ob_get_clean();
		if ( '' === $result['stop'] && ! $need_to_clean_block ) {
			$result['stop'] = null; // no need to clean already displayed messages.
		}
		// audit-tip.
		ob_start();
		$tips = array_map( [ Message::class, 'create' ], Ahrefs_Seo_Errors::get_saved_messages( null, 'tip' ) );
		array_walk(
			$tips,
			function ( Message $message ) {
				$message->show();
			}
		);
		$result['audit-tip'] = (string) ob_get_clean();
		// errors: api-messages.
		ob_start();
		$this->view->show_part( 'notices/api-messages' );
		$messages_html = (string) ob_get_clean();
		if ( '' !== $messages_html ) {
			$result['api-messages'] = $messages_html;
		}
		// notices: api-delayed.
		$result['api-delayed'] = ''; // clean block by default.
		if ( ( new Content_Audit() )->has_unprocessed_items() ) {
			$notices = array_merge( Ahrefs_Seo_Errors::get_saved_messages( null, 'notice' ), Ahrefs_Seo_Errors::get_saved_messages( null, 'error-single' ) );
			if ( $notices ) {
				ob_start();
				$ids = [];
				foreach ( $notices as $notice ) {
					$message = Message::create( $notice );
					if ( ! in_array( $message->get_id(), $ids, true ) ) { // do not show duplicated messages.
						$message->show();
						$ids[] = $message->get_id();
					}
				}
				unset( $ids );
				$result['api-delayed'] .= (string) ob_get_clean();
			}
		}
		// tips block: what to show.
		$result['content-tips-show'] = [];
		$tips                        = Tips::at_content_screen();
		foreach ( $tips as $tip ) {
			$result['content-tips-show'][ $tip::ID ] = $tip->need_to_show();
		}
		return $result;
	}
	/**
	 * Get a list of visible columns
	 *
	 * @since 0.9.4
	 *
	 * @return array<string, string> Array [ column ID => column title].
	 */
	private function get_visible_columns() {
		$columns = [];
		$table   = $this->initialize_table();
		if ( $table instanceof Ahrefs_Seo_Table_Content ) {
			$columns = $table->get_columns();
			$hidden  = get_hidden_columns( $this->screen_id );
			unset( $columns['cb'] );
			foreach ( $hidden as $id ) {
				unset( $columns[ $id ] );
			}
			foreach ( $columns as $id => $html ) {
				$columns[ $id ] = sanitize_text_field( $html ); // remove any html tags.
			}
		}
		return $columns;
	}
}