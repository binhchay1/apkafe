<?php
namespace Ht_Easy_Ga4\Admin;

class Ajax_Actions{
    use \Ht_Easy_Ga4\Helper_Trait;
    use \Ht_Easy_Ga4\Rest_Request_Handler_Trait;

    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }    

    function __construct(){
        add_action( 'wp_ajax_htga4_get_properties', array($this, 'get_properties_cb') );
        add_action( 'wp_ajax_htga4_get_data_streams', array($this, 'get_data_streams_cb') );

        add_action( 'wp_ajax_htga4_save_options', array($this, 'save_options') );
	}

    public function get_properties_cb(){
        // Verify nonce
        check_ajax_referer( 'htga4_nonce', 'nonce' );

        $post_data      = wp_unslash($_POST);
        $account        = sanitize_text_field($post_data['account']);
        $properties     = htga4()->get_properties_data_prepared($account);

        if ( !empty($properties['error']) ) {
            wp_send_json_error(array(
                'message' => $properties['error']['message']
            ));
        } elseif($properties) {
            wp_send_json_success($properties);
        } else {
            wp_send_json_error(array(
                'message' => esc_html__( 'No Property Found!', 'ht-easy-ga4' )
            ));
        }
    }

    public function get_data_streams_cb(){
        // Verify nonce
        check_ajax_referer( 'htga4_nonce', 'nonce' );

        $post_data      = wp_unslash($_POST);
        $property       = sanitize_text_field($post_data['property']);

        $data_streams = htga4()->get_data_streams_data_prepared($property);

        if( !empty($data_streams['error']) ){
            wp_send_json_error(array(
                'message' => $data_streams['error']['message']
            ));
        } elseif($data_streams) {
            wp_send_json_success($data_streams);
        } else {
            wp_send_json_error(array(
                'message' => esc_html__( 'No Data Stream Found!', 'ht-easy-ga4' )
            ));
        }
    }

    // ajax request receiver function save_options
    public function save_options(){
        $post_data = wp_unslash($_POST);
        
        // Verify nonce
        check_ajax_referer( 'htga4_nonce', 'nonce' );

        $options_to_update = $this->htga4_clean($post_data['formValues']);
        
        $final_options = wp_parse_args( $options_to_update, get_option('ht_easy_ga4_options') );
        update_option('ht_easy_ga4_options', $final_options);

        wp_send_json_success();
    }
}

Ajax_Actions::instance();