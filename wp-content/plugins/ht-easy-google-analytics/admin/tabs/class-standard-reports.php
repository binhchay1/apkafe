<?php
namespace Ht_Easy_Ga4\Admin\Tabs;

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

class Standard_Reports {
    use \Ht_Easy_Ga4\Helper_Trait;
	use \Ht_Easy_Ga4\Rest_Request_Handler_Trait;

    public $analytics_data_permission = null;
    public $accounts_result = array();
    public $reports;

    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    function __construct(){
		if( $this->get_current_tab() != 'standard_reports'){
			return;
		}

        // Analytics data permission.
		if( $this->get_access_token() ){
			$accounts_result = $this->request_accounts();

			if( $accounts_result ){
				if( !empty($accounts_result['error']) && !empty($accounts_result['error']['code']) && $accounts_result['error']['code'] == 403 ){
					$this->accounts_result = $accounts_result;
					$this->analytics_data_permission = false;
				} else {
					$this->accounts_result = $accounts_result;
					$this->analytics_data_permission = true;
				}
			}
		}
    }

    public function render(){
        include_once HT_EASY_GA4_PATH . '/admin/views/html-standard-reports.php';
    }
}

Standard_Reports::instance(); // Initiate the plugin