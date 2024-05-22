<?php
namespace Ht_Easy_Ga4\Admin\Tabs;

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

class Events_Tracking {
    use \Ht_Easy_Ga4\Helper_Trait;

    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct(){
        
    }

    public function render(){
        include_once HT_EASY_GA4_PATH . '/admin/views/html-events-tracking.php';
    }
}

Events_Tracking::instance(); // Initiate the class