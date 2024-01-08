<?php
namespace HtEasyGa4\Admin;

class Ajax_Actions{
    use \HtEasyGa4\Helper_Trait;

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
	}

    public function get_properties_cb(){
        // Verify nonce
        check_ajax_referer( 'htga4_nonce', 'nonce' );

        $post_data      = wp_unslash($_POST);
        $account        = $post_data['account'];
        $properties     = array();

        $result = $this->request_properties( $account );

        if ( !empty($result['error']) ) {
            wp_send_json_error(array(
                'message' => $result['error']['message']
            ));
        } elseif($result) {
            foreach( $result['properties'] as $property ){
                $property_id = substr($property['name'],11);
                $properties[$property_id] = $property['displayName'];
            }

            wp_send_json_success($properties);
        }
            

        wp_send_json_error(array(
            'message' => esc_html__( 'Properties request is failed!', 'ht-easy-ga4' )
        ));
    }

    public function get_data_streams_cb(){
        // Verify nonce
        check_ajax_referer( 'htga4_nonce', 'nonce' );

        $post_data      = wp_unslash($_POST);
        $property       = sanitize_text_field($post_data['property']);

        $result   = $this->request_data_streams($property);
        $data_streams = array();

        if ( !empty($result['error']) ) {
            wp_send_json_error(array(
                'message' => $result['error']['message']
            ));
        } elseif($result) {
            foreach( $result['dataStreams'] as $data_stream ){
                // Return only web streams, there are differnt type of stream types ANDROID_APP_DATA_STREAM, IOS_APP_DATA_STREAM & WEB_DATA_STREAM
                if( $data_stream['type'] === 'WEB_DATA_STREAM' ){
                    $data_streams[] = $data_stream;
                }
            }

            wp_send_json_success($data_streams);
        }


        wp_send_json_error(array(
            'message' => esc_html__( 'Properties request is failed!', 'ht-easy-ga4' )
        ));
    }
}

Ajax_Actions::instance();