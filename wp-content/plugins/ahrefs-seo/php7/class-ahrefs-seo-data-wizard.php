<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo;

use Error;
use Exception;

/**
 * Data Wizard class.
 * Provide ajax endpoint for progress tracking.
 */
class Ahrefs_Seo_Data_Wizard {

	/** @var Ahrefs_Seo_Data_Wizard */
	private static $instance = null;

	/**
	 * Return the instance
	 *
	 * @return Ahrefs_Seo_Data_Wizard
	 */
	public static function get() : Ahrefs_Seo_Data_Wizard {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->register_ajax_handlers();
	}

	/**
	 * Register ajax handler
	 */
	public function register_ajax_handlers() : void {
		add_action( 'wp_ajax_ahrefs_progress', [ $this, 'ajax_progress' ] );
	}

	/**
	 * Ajax handler for initial wizard update, output a current progress.
	 * Author+ can view progress.
	 */
	public function ajax_progress() : void {
		Ahrefs_Seo::thread_id( 'wizard' );
		$nonce_action = Ahrefs_Seo_Screen_Wizard::get_nonce_name_static();
		if ( current_user_can( Ahrefs_Seo::CAP_CONTENT_AUDIT_VIEW ) && check_ajax_referer( $nonce_action ) ) { // anyone can see the progress.
			wp_send_json_success( $this->get_progress() );
		}
	}

	/**
	 * Get current Backlinks and Content Audit update progress as array with details
	 *
	 * @param bool $do_not_run_update True - will run update if has pending items, false - just return result.
	 * @return array<string, mixed> Associative array with details.
	 */
	public function get_progress( bool $do_not_run_update = false ) : array {
		$result = [];
		try {
			Ahrefs_Seo::breadcrumbs( sprintf( 'Wizard get_progress() started, used memory %1.1fM', ( intval( memory_get_usage() / 1024 / 1024 * 10 ) / 10 ) ) );
			$progress_analytics     = 0; // 1 is max.
			$error                  = '';
			$error2                 = '';
			$current_time           = null;
			$planned_time           = null;
			$can_not_continue_error = null; // null or string.

			// get progress from content audit progress.
			$content_audit = new Content_Audit();
			if ( $content_audit->require_update() ) {
				try {
					if ( ! $do_not_run_update ) {
						$can_not_continue_error = $content_audit->maybe_can_not_proceed();
						( new Content_Audit_Current() )->maybe_update() || $content_audit->update_table();
					}
				} catch ( Exception $e ) {
					$error2 = $e->getMessage();
				}
				$progress_analytics = ( 100 - $content_audit->content_get_unprocessed_percent() ) / 100;
			} else {
				$progress_analytics = 1;
			}

			$finished = ! $content_audit->require_update(); // everything completed.
			if ( $finished ) {
				Ahrefs_Seo::get()->initialized_set( null, null, null, true ); // wizard update finished.
			}

			$is_paused = Content_Audit::audit_is_paused();
			// progress value in percents.
			$result = [
				'percents'     => floor( $progress_analytics * 10000 ) / 100,
				'finish'       => $finished,
				'_error'       => $error,
				'_error2'      => $error2,
				'planned_time' => $planned_time,
				'current_time' => $current_time,
				'paused'       => $is_paused, // on audit paused: close last step of wizard and show Content audit page.
				'delayed'      => Content_Audit::audit_is_delayed() && ! $is_paused,
			];
			Ahrefs_Seo::breadcrumbs( sprintf( 'Wizard get_progress() finished, used memory %1.1fM, return: %s', ( intval( memory_get_usage() / 1024 / 1024 * 10 ) / 10 ), (string) wp_json_encode( $result ) ) );
		} catch ( Error $e ) {
			Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__, __( 'Get Wizard progress failed', 'ahrefs-seo' ) );
		} catch ( Exception $e ) {
			Ahrefs_Seo::notify( $e, 'Get Wizard progress failed' );
		}
		return $result;
	}

}
