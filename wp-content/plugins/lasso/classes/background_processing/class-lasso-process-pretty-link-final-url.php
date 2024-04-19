<?php
/**
 * Declare class Lasso_Process_Pretty_Link_Final_Url
 * Get the final url from Pretty Link records. This data is used to fix the issue Lasso change "a" link href to final url.
 *
 * @package Lasso_Process_Check_Issue
 */

use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Models\Model;

/**
 * Lasso_Process_Pretty_Link_Final_Url
 */
class Lasso_Process_Pretty_Link_Final_Url extends Lasso_Process {
	const LIMIT       = 500;
	const OPTION_PAGE = 'lasso_pretty_link_final_url_page';
	const OPTION_DATE = 'lasso_pretty_link_final_url_date';

	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_pretty_link_final_url';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'pretty_link_final_url';

	const PREFIX_KEY = 'lasso_pretty_link_final_url';

	/**
	 * Lasso_Process constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'lasso_all_processes', array( $this, 'lasso_all_processes' ), 20, 1 );
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $cpt_id Queue item to iterate over.
	 *
	 * @return mixed
	 */
	public function task( $cpt_id ) {
		if ( ! Lasso_Helper::is_pretty_link_plugin_active() ) {
			return $cpt_id;
		}

		$lasso_db    = new Lasso_DB();
		$pretty_link = $lasso_db->get_pretty_link_by_id( $cpt_id );

		if ( ! $pretty_link || empty( $pretty_link->url ) ) {
			return false;
		}

		$origin_url = $pretty_link->url;

		// ? Only process each origin url one time
		if ( get_option( self::PREFIX_KEY . '_origin_' . md5( $origin_url ) ) ) {
			return false;
		}

		Lasso_Helper::write_log( '== START get final url for id:' . $cpt_id . ' ==', $this->log_name );

		$final_url = Lasso_Helper::get_redirect_final_target( $origin_url );
		$final_url = Lasso_Helper::get_url_without_parameters( $final_url );

		global $prli_blogurl;
		// ? Store Pretty link final url to option table.
		update_option( self::PREFIX_KEY . '_' . md5( $final_url ), $prli_blogurl . PrliUtils::get_permalink_pre_slug_uri() . $pretty_link->slug );
		update_option( self::PREFIX_KEY . '_origin_' . md5( $origin_url ), 1 );

		Lasso_Helper::write_log( '== End get final url for id: ' . $cpt_id . ' ==', $this->log_name );

		return false;
	}

	/**
	 * Prepare data for process
	 */
	public function process() {
		// ? check whether process is age out and make it can work on Lasso UI via ajax requests
		$this->is_process_age_out();

		if ( $this->is_process_running() ) {
			return false;
		}

		$today      = gmdate( 'Y-m-d' );
		$check_date = get_option( self::OPTION_DATE, '' );

		$page = get_option( self::OPTION_PAGE, '1' );
		$page = intval( $page );

		if ( 1 === $page && $today === $check_date ) {
			return false;
		}

		$lasso_db            = new Lasso_DB();
		$sql                 = $lasso_db->get_pretty_link_cpt_ids_query();
		$sql                 = $lasso_db->paginate( $sql, $page, self::LIMIT );
		$pretty_link_cpt_ids = Model::get_col( $sql );
		$total_count         = count( $pretty_link_cpt_ids );

		if ( $total_count <= 0 ) {
			update_option( self::OPTION_PAGE, '1' );
			return false;
		}
		update_option( self::OPTION_PAGE, strval( $page + 1 ) ); // ? increase page
		update_option( self::OPTION_DATE, $today ); // ? set check date is t

		foreach ( $pretty_link_cpt_ids as $cpt_id ) {
			$this->push_to_queue( $cpt_id );
		}

		$this->set_total( $total_count );
		$this->set_log_file_name( $this->log_name );
		$this->task_start_log();
		// ? save queue
		$this->save()->dispatch();

		return true;
	}

	/**
	 * Add this processes to Lasso processes list for running manually
	 *
	 * @param array $lasso_processes Lasso Processes.
	 * @return array
	 */
	public function lasso_all_processes( $lasso_processes ) {
		// ? We only add this process to UI if cron is getting the issue
		if ( Lasso_Helper::is_cron_getting_issues() ) {
			$processes = array(
				'Lasso_Process_Pretty_Link_Final_Url' => 'Get Pretty Link final url',
			);

			return array_merge( $lasso_processes, $processes );
		}

		return $lasso_processes;
	}
}
new Lasso_Process_Pretty_Link_Final_Url();
