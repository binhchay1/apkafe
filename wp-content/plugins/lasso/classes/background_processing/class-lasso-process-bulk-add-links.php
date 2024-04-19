<?php
/**
 * Declare class Lasso_Process_Bulk_Add_Links
 *
 * @package Lasso_Process_Bulk_Add_Links
 */

/**
 * Lasso_Process_Bulk_Add_Links
 */
class Lasso_Process_Bulk_Add_Links extends Lasso_Process {
	const LASSO_BULK_TOTAL_LINKS_KEY = 'lasso_bulk_total_links';
	const LASSO_BULK_ADD_LINK_KEY    = 'lasso_bulk_add_link';

	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_bulk_add_links';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'buld_add_links';

	/**
	 * Task
	 *
	 * @param string $link URL.
	 *
	 * @return bool|mixed
	 */
	public function task( $link ) {
		if ( empty( $link ) ) {
			return false;
		}
		$lasso_affiliate_link = new Lasso_Affiliate_Link();
		$lasso_affiliate_link->lasso_add_a_new_link( $link );
		$item_no = intval( get_option( self::LASSO_BULK_ADD_LINK_KEY, '0' ) );
		$item_no++;
		update_option( self::LASSO_BULK_ADD_LINK_KEY, $item_no );

		return false;
	}


	/**
	 * Process
	 *
	 * @param array $links URLs array.
	 *
	 * @return bool
	 */
	public function process( $links = array() ) {
		// ? check whether process is age out and make it can work on Lasso UI via ajax requests
		$this->is_process_age_out();

		if ( $this->is_process_running() || 0 >= count( $links ) ) {
			return false;
		}

		update_option( self::LASSO_BULK_ADD_LINK_KEY, 0 );
		update_option( self::LASSO_BULK_TOTAL_LINKS_KEY, count( $links ) ); // ? increase page

		foreach ( $links as $link ) {
			$this->push_to_queue( $link );
		}

		$this->set_total( count( $links ) );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();
		// ? save queue
		$this->save()->dispatch();

		return true;
	}

	/**
	 * Get progress by percent
	 *
	 * @return int
	 */
	public static function get_progress() {
		$total        = intval( get_option( self::LASSO_BULK_TOTAL_LINKS_KEY, '0' ) );
		$current_link = intval( get_option( self::LASSO_BULK_ADD_LINK_KEY, '0' ) );
		if ( 0 === $total ) {
			return 5;
		}
		return round( ( $current_link / $total ) * 100 );
	}
}
new Lasso_Process_Bulk_Add_Links();
