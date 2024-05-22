<?php
namespace Ht_Easy_Ga4\Admin\Tabs;

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

class Ecommerce_Reports {
    use \Ht_Easy_Ga4\Helper_Trait;
	use \Ht_Easy_Ga4\Rest_Ecommerce_Request_Handler_Trait;

    public $ecommerce_reports;

    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    function __construct(){
		if( $this->get_current_tab() != 'ecommerce_reports' || !$this->has_proper_request_data()){
			return;
		}
    }

    public function render(){
        include_once HT_EASY_GA4_PATH . '/admin/views/html-ecommerce-reports.php';
    }
}

Ecommerce_Reports::instance(); // Initiate the plugin