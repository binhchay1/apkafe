<?php
namespace Ht_Easy_Ga4\Admin\Tabs;

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

class Realtime_Reports {
    use \Ht_Easy_Ga4\Helper_Trait;
	use \Ht_Easy_Ga4\Rest_Request_Handler_Trait;
	use \Ht_Easy_Ga4\Rest_Realtime_Request_Handler_Trait;

    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    function __construct(){
		if( $this->get_current_tab() != 'realtime_reports' || !$this->has_proper_request_data()){
			return;
		}
    }

	public function request_all_realtime_reports(){
		$reports = array();

		// Transient
		$transient_key = 'ht_easy_ga4_realtime_reports_' . htga4()->get_unique_transient_suffix();
		$transient_data = get_transient( $transient_key );

		if( $transient_data ){
			return $transient_data;
		}

		$reports['page_views_per_minute']  = $this->prepare_device_types(json_decode($this->request_page_views_per_minute(), true));
		$reports['active_users']  = $this->prepare_active_users(json_decode($this->request_active_users(), true));
		$reports['top_pages']     = $this->prepare_top_pages(json_decode($this->request_top_pages(), true));
		$reports['top_events']    = $this->prepare_top_pages(json_decode($this->request_top_events(), true));
		$reports['top_countries'] = $this->prepare_top_pages(json_decode($this->request_top_countries(), true));
		$reports['device_types']  = $this->prepare_device_types(json_decode($this->request_device_types(), true));

		// Transient
		set_transient( $transient_key, $reports, MINUTE_IN_SECONDS * 30 ); // 30 minutes

		return $reports;
	}

    public function render(){
        include_once HT_EASY_GA4_PATH . '/admin/views/html-realtime-reports.php';
    }
}

Realtime_Reports::instance(); // Initiate the plugin