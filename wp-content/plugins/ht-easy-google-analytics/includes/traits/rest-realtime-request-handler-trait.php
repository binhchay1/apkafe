<?php
namespace Ht_Easy_Ga4;

trait Rest_Realtime_Request_Handler_Trait {
    use Helper_Trait;

    public function make_realtime_request( $request_body ){
        $response_json = '';

        if( !$this->has_proper_request_data() ){
            $response_json = wp_json_encode(array('error' => array(
                'message' => 'Invalid request data',
                'code'    => 400,
            )));

            return $response_json;
        }

        $property    = $this->get_option('property');
        $request_url  = 'https://analyticsdata.googleapis.com/v1beta/properties/' . $property . ':runRealtimeReport';
        $access_token = $this->get_access_token();

        $response_raw = wp_remote_post( $request_url, array(
            'timeout'     => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode( $request_body ),
            'sslverify' => false,
        ) );

        if ( is_wp_error( $response_raw ) ) { // Can't resolve host
            // Make the error response like Google Analytics API.
            $response_json = array('error' => array(
                'message' => $response_raw->get_error_message(),
                'code'    => $response_raw->get_error_code(),
            ));
        } else {
            $response_code = wp_remote_retrieve_response_code( $response_raw );
            $response_message = wp_remote_retrieve_response_message( $response_raw );

            if( $response_code == 200 ){
                $response_json = wp_remote_retrieve_body( $response_raw );
            } else {
                $response_json = wp_json_encode(array('error' => array(
                    'message' => $response_message,
                    'code'    => $response_code,
                )));
            }
        }

        return $response_json;
    }

    /**
     * Request active users from Google Analytics.
     *
     * @return string JSON response
     */
    public function request_active_users(){
        $request_body = [
            'metrics' => [
                'name' => 'activeUsers'
            ],
        ];

        $response_json = $this->make_realtime_request($request_body);

        return $response_json;
    }

    /**
     * Request page views per minute from Google Analytics.
     *
     * @return string JSON response
     */
    public function request_page_views_per_minute(){
        $request_body = [
            'metrics' => [
                'name' => 'screenPageViews'
            ],
            'dimensions' => [
                'name' => 'minutesAgo',
            ]
        ];

        $response_json = $this->make_realtime_request($request_body);
        return $response_json;
    }

    /**
     * Request top pages from Google Analytics.
     *
     * @return string JSON response
     */
    public function request_top_pages(){
        $request_body = [
            'dimensions' => [
                'name' => 'unifiedScreenName'
            ],
            'metrics' => [
                'name' => 'screenPageViews'
            ]
        ];
        
        $response_json = $this->make_realtime_request($request_body);
        return $response_json;
    }

    /**
     * Request top events from Google Analytics.
     *
     * @return string JSON response
     */
    public function request_top_events(){
        $request_body = [
            'dimensions' => [
                'name' => 'eventName'
            ],
            'metrics' => [
                'name' => 'eventCount'
            ]  
        ];

        $response_json = $this->make_realtime_request($request_body);
        return $response_json;
    }

    /**
     * Request top countries from Google Analytics.
     *
     * @return string JSON response
     */
    public function request_top_countries(){
        $request_body = [
            'dimensions' => [
                'name' => 'country'
            ],
            'metrics' => [
                'name' => 'activeUsers'
            ]
        ];

        $response_json = $this->make_realtime_request($request_body);
        return $response_json;
    }

    /**
     * Request device types from Google Analytics.
     *
     * @return string JSON response
     */
    public function request_device_types(){
        $request_body = [
            'dimensions' => [
                'name' => 'deviceCategory'
            ],
            'metrics' => [
                'name' => 'activeUsers'
            ]
        ];

        $response_json = $this->make_realtime_request($request_body);
        return $response_json;
    }
}