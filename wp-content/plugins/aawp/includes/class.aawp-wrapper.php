<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if (!class_exists('AAWP_Wrapper')) {

    class AAWP_Wrapper
    {
        // Function variables
        public $options = array();

        /**
         * Construct the object
         */
        public function __construct() {
            // Options
            $this->options['api'] = get_option( 'aawp_api', array() );
            $this->options['general'] = get_option( 'aawp_general', array() );
            $this->options['output'] = get_option( 'aawp_output', array() );
        }

        public function debug( $arg ) {
            echo '<pre>';
            print_r($arg);
            echo '</pre>';
        }
    }

    $AAWP_Wrapper = new AAWP_Wrapper();
}

// Helper
function aawp_set_api_check_routine() {
    set_transient( 'flowdee_aawp_api_check_routine', true, 60 * 60 * 72 );
}