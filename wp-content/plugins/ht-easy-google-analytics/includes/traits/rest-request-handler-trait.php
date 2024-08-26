<?php
namespace Ht_Easy_Ga4;

use Exception;
use WP_Error;

trait Rest_Request_Handler_Trait {
	/**
     * > It sends a POST request to our server endpoint with the user's email address, and if the
     * request is successful, it returns the new access token
     * 
     * @param email The email address of the user to get an access token.
     * 
     * @return string
     */
    function generate_access_token( $email ){
        $request_url = Base::$htga4_rest_base_url . 'v1/get-access-token';

        $raw_response = wp_remote_post( $request_url, array(
            'timeout'     => 10,
            'body'        => array(
                'email' => sanitize_email($email),
                'key'   => get_option('htga4_sr_api_key') // The key is used to authenticate the request.
            ),
            'sslverify' => false,
        ));

        // Something wrong happened on the server side.
        $response_code = wp_remote_retrieve_response_code( $raw_response );
        if ( is_wp_error( $raw_response ) || 200 !== $response_code ) {
            return array();
        }

        $response = json_decode( wp_remote_retrieve_body( $raw_response ), true );

        $access_token = '';
        if( !empty($response['success']) && $response['success'] === true ){
            $access_token = $response['access_token'];
            set_transient('htga4_access_token', $access_token, (MINUTE_IN_SECONDS * 58) );
        }

        return $access_token;
    }

    /**
     * Request userinfo
     * 
     * @return string
     */
    public function request_userinfo(){
        $response_json = '';
        $access_token  = $this->get_access_token();

        if( !$access_token ){
            $response_json = wp_json_encode(array('error' => array(
                'message' => __('Access token is missing!', 'ht-easy-ga4'),
                'code'    => 400,
            )));
        }

        // Transient
        $transient_key = 'htga4_userinfo';
        $transient_data = get_transient($transient_key);

        if( $transient_data ){
            return $transient_data;
        }

        $request_url = 'https://www.googleapis.com/oauth2/v3/userinfo';

        $request_args = array(
            'timeout'     => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ),
            'sslverify' => false,
        );
        
        $response_raw = wp_remote_get( $request_url, $request_args );

        if ( is_wp_error( $response_raw ) ) { // Can't resolve host
            // Make the error response like Google Analytics API.
            $response_json = wp_json_encode(array('error' => array(
                'message' => $response_raw->get_error_message(),
                'code'    => $response_raw->get_error_code(),
            )));
        } else {
            $response_code    = wp_remote_retrieve_response_code( $response_raw );
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

        set_transient($transient_key, $response_json, (MINUTE_IN_SECONDS * 58));

        return $response_json;
    }

    public function get_userinfo_data_prepared(){
        $response_json = $this->request_userinfo();
        $response_data = json_decode($response_json, true);

        if( !empty($response_data['error']) ){
            return $response_data;
        }

        $data = array(
            'email' => $response_data['email'],
            'name'  => $response_data['name'],
            'picture' => $response_data['picture'],
        );

        return $data;
    }

	/**
     * @param account The account ID.
     * @param access_token The access token you received from the Google API.
     * 
     * @return string
     */
    public function request_accounts(){
        $response_json = '';
        $access_token = $this->get_access_token();

        if( !$access_token ){
            $response_json = wp_json_encode(array('error' => array(
                'message' => __('Access token is missing!', 'ht-easy-ga4'),
                'code'    => 400,
            )));

            return $response_json;
        }

        // Transient
        $transient_key = 'htga4_accounts_v2'; // Existing transient kay store array of accounts and this one store response json.
        $transient_data = get_transient($transient_key);

        if( $transient_data ){
            return $transient_data;
        }

        $request_url = 'https://analyticsadmin.googleapis.com/v1beta/accounts';
        $request_args = array(
            'timeout'     => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ),
            'sslverify' => false,
        );

        $response_raw = wp_remote_get( $request_url, $request_args );

        if ( is_wp_error( $response_raw ) ) { // Can't resolve host
            // Make the error response like Google Analytics API.
            $response_json = wp_json_encode(array('error' => array(
                'message' => $response_raw->get_error_message(),
                'code'    => $response_raw->get_error_code(),
            )));
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

        set_transient($transient_key, $response_json, (MINUTE_IN_SECONDS * 58));

        return $response_json;
    }

    public function get_accounts_data_prepared(){
        $response_json = $this->request_accounts();
        $response_data = json_decode($response_json, true);

        if( !empty($response_data['error']) ){
            return $response_data;
        }

        $accounts = array();
        if( !empty($response_data['accounts']) ){
            foreach( $response_data['accounts'] as $account ){
                $account_id    = substr( $account['name'], 9 );
                $accounts[$account_id] = $account['displayName'];
            }
        }

        return $accounts;
    }

    /**
     * Returns child Properties under the specified parent Account.
     * https://developers.google.com/analytics/devguides/config/admin/v1/rest/v1alpha/properties/list
     * 
     * @param account The account ID.
     * 
     * @return string 
     */
    public function request_properties( $account ){
        $response_json = '';
        $access_token = $this->get_access_token();

        if( !$access_token || !$account ){
            $response_json = wp_json_encode(array('error' => array(
                'message' => __('The request does not have proper data!', 'ht-easy-ga4'),
                'code'    => 400,
            )));

            return $response_json;
        }

        // Transient
        $transient_key = 'htga4_properties_v2';
        $transient_data = get_transient($transient_key);

        if( $transient_data && !wp_doing_ajax() ){
            return $transient_data;
        }

        $request_url = "https://analyticsadmin.googleapis.com/v1alpha/properties?filter=parent:accounts/{$account}";
        $request_args = array(
            'timeout'     => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ),
            'sslverify' => false,
        );

        $response_raw = wp_remote_get( $request_url, $request_args );

        if ( is_wp_error( $response_raw ) ) { // Can't resolve host
            // Make the error response like Google Analytics API.
            $response_json = wp_json_encode(array('error' => array(
                'message' => $response_raw->get_error_message(),
                'code'    => $response_raw->get_error_code(),
            )));
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

        set_transient($transient_key, $response_json, (MINUTE_IN_SECONDS * 58));

        return $response_json;
    }

    public function get_properties_data_prepared( $account = '' ){
        $account = $account ? $account : $this->get_option('account');

        $response_json = $this->request_properties( $account );
        $response_data = json_decode($response_json, true);

        if( !empty($response_data['error']) ){
            return $response_data;
        }

        $properties = array();
        if( !empty($response_data['properties']) ){
            foreach( $response_data['properties'] as $property ){
                $property_id = substr( $property['name'], 11 );
                $properties[$property_id] = $property['displayName'];
            }
        }

        return $properties;
    }

    /**
     * @param property
     * 
     * @return string json
     */
    public function request_data_streams( $property ){
        $response_json = '';
        $access_token  = $this->get_access_token();

        if( !$access_token || !$property ){
            $response_json = wp_json_encode(array('error' => array(
                'message' => __('The request does not have proper data!', 'ht-easy-ga4'),
                'code'    => 400,
            )));

            return $response_json;
        }

        // Transient
        $transient_key = 'htga4_data_streams_v2';
        $transient_data = get_transient($transient_key);

        if( $transient_data && !wp_doing_ajax() ){
            return $transient_data;
        }

        $request_url  = "https://analyticsadmin.googleapis.com/v1beta/properties/{$property}/dataStreams";
        $request_args = array(
            'timeout' => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'sslverify' => false,
        );

        $response_raw = wp_remote_get( $request_url, $request_args );

        if ( is_wp_error( $response_raw ) ) { // Can't resolve host
            // Make the error response like Google Analytics API.
            $response_json = wp_json_encode(array('error' => array(
                'message' => $response_raw->get_error_message(),
                'code'    => $response_raw->get_error_code(),
            )));
        } else {
            $response_code    = wp_remote_retrieve_response_code( $response_raw );
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

        set_transient($transient_key, $response_json, (MINUTE_IN_SECONDS * 58));

        return $response_json;
    }

    /**
     * @param property
     * 
     * @return array
     */
    public function get_data_streams_data_prepared( $property = '' ){
        $property = $property ? $property : $this->get_option('property');

        $response_json = $this->request_data_streams( $property );
        $response_data = json_decode($response_json, true);

        if( !empty($response_data['error']) ){
            return $response_data;
        }

        $data_streams = array();
        if( !empty($response_data['dataStreams']) ){
            foreach( $response_data['dataStreams'] as $data_stream ){
                if ( $data_stream['type'] === 'WEB_DATA_STREAM' ) {
                    $data_stream_id = explode( '/', $data_stream['name'] );
                    $data_stream_id = end( $data_stream_id );

                    $measurement_id = $data_stream['webStreamData']['measurementId'];

                    $data_streams[$data_stream_id] = array(
                        'measurement_id' => $measurement_id,
                        'display_name'   => $data_stream['displayName'],
                    );
                }
            }
        }

        return $data_streams;
    }

    /**
     * This function sends a request to the Google Analytics API to retrieve data from a specific data
     * stream for a given property, using an access token for authentication.
     * 
     * @param property
     * @param stream_id
     * 
     * @return string
     */
    public function request_data_stream( $property, $stream_id ){
        $response_json = '';
        $access_token = $this->get_access_token();
 
        if( !$access_token  || !$property || !$stream_id ){
            $response_json = array(
                'error' => array(
                    'message' => __('The request does not have proper data!', 'ht-easy-ga4'),
                    'code'    => 400,
                )
            );

            return $response_json;
        }

        // Trainsient based on $stream_id
        $transient_key = 'htga4_data_stream_' . $stream_id;
        $transient_data = get_transient($transient_key);

        if( $transient_data ){
            return $transient_data;
        }

        $request_url  = "https://analyticsadmin.googleapis.com/v1beta/properties/{$property}/dataStreams/{$stream_id}";
        $request_args = array(
            'timeout'     => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ),
            'sslverify' => false,
        );

        $response_raw = wp_remote_get( $request_url, $request_args );

        if ( is_wp_error( $response_raw ) ) { // Can't resolve host
            // Make the error response like Google Analytics API.
            $response_json = array(
                'error' => array(
                    'message' => $response_raw->get_error_message(),
                    'code'    => $response_raw->get_error_code(),
                )
            );
        } else {
            $response_code = wp_remote_retrieve_response_code( $response_raw );
            $response_message = wp_remote_retrieve_response_message( $response_raw );
  
            if( $response_code == 200 ){
                $response_json = wp_remote_retrieve_body( $response_raw );
            } else {
                $response_json = wp_json_encode(
                    array(
                        'error' => array(
                            'message' => $response_message,
                            'code'    => $response_code,
                        )
                    )
                );
            }
        }

        // Trainsient
        set_transient($transient_key, $response_json, (MINUTE_IN_SECONDS * 58));

        return $response_json;
    }

    public function get_datastream_data_prepared( $property = '', $data_stream_id = ''){
        $property = $property ? $property : $this->get_option('property');
        $data_stream_id = $data_stream_id ? $data_stream_id : $this->get_option('data_stream_id');

        $response_json = $this->request_data_stream( $property, $data_stream_id );
        $response_data = json_decode($response_json, true);

        if( !empty($response_data['error']) ){
            return $response_data;
        }

        $data = array(
            'displayName' => $response_data['displayName']
        );

        return $data;
    }

    /**
     * This function sends batch requests to the Google Analytics API and returns an array of reports.
     * 
     * @return array
     */
    public function report_batch_request(){
        $access_token = $this->get_access_token();
        $property     = $this->get_option('property');

        $response_json = array(
            'batch1' => '',
            'batch2' => '',
        );

        if( !$this->has_proper_request_data() ){
            $response_json['batch1'] = $response_json['batch2'] = wp_json_encode(
                array(
                    'error' => array(
                        'message' => __('The request does not have proper data!', 'ht-easy-ga4'),
                        'code'    => 400,
                    )
                )
            );

            return $response_json;
        }

        $date_range = isset( $_GET['date_range'] ) ? sanitize_text_field($_GET['date_range']) : 'last_30_days'; // PHPCS:ignore

        // Transient
        if( $date_range == 'last_30_days' ){
            $transient_key = 'htga4_standard_reports_data_' . htga4()->get_unique_transient_suffix();
            $transient_data = get_transient($transient_key);
    
            if( $transient_data ){
                return $transient_data;
            }
        }
   

        $request_url  = sprintf('https://analyticsdata.googleapis.com/v1beta/properties/%s:batchRunReports', $property );

        $ga4_requests = $this->get_ga4_requests();

        $response_raw = wp_remote_post($request_url, array(
            'method' => 'POST',
            'timeout' => 20,
            'sslverify' => false,
            'headers' => array(
                'timeout'       => 20,
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body' => wp_json_encode(array(
                'requests' => array(
                    $ga4_requests['session'],
                    $ga4_requests['page_view'],
                    $ga4_requests['bounce_rate'],
                    $ga4_requests['page_path'],
                    $ga4_requests['referrer'],
                )
            ))
        ));

        if ( is_wp_error( $response_raw ) ) { // Can't resolve host
            // Make the error response like Google Analytics API.
            $response_json['batch1'] = wp_json_encode(array('error' => array(
                'message' => $response_raw->get_error_message(),
                'code'    => $response_raw->get_error_code(),
            )));
        } else {
            $response_code = wp_remote_retrieve_response_code( $response_raw );
            $response_message = wp_remote_retrieve_response_message( $response_raw );
  
            if( $response_code == 200 ){
                $response_json['batch1'] = wp_remote_retrieve_body( $response_raw );
            } else {
                $response_json['batch1'] = wp_json_encode(array('error' => array(
                    'message' => $response_message,
                    'code'    => $response_code,
                )));
            }
            
        }

        $response2_raw = wp_remote_post($request_url, array(
            'method' => 'POST',
            'timeout' => 20,
            'sslverify' => false,
            'headers' => array(
                'timeout'       => 20,
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body' => wp_json_encode(array(
                'requests' => array(
                    $ga4_requests['countries'],
                    $ga4_requests['user_types'],
                    $ga4_requests['device_types']
                )
            ))
        ));

        if ( is_wp_error( $response2_raw ) ) { // Can't resolve host
            // Make the error response like Google Analytics API.
            $response_json['batch2'] = wp_json_encode(array('error' => array(
                'message' => $response2_raw->get_error_message(),
                'code'    => $response2_raw->get_error_code(),
            )));
        } else {
            $response_code = wp_remote_retrieve_response_code( $response2_raw );
            $response_message = wp_remote_retrieve_response_message( $response2_raw );
  
            if( $response_code == 200 ){
                $response_json['batch2'] = wp_remote_retrieve_body( $response2_raw );
            } else {
                $response_json['batch2'] = wp_json_encode(array('error' => array(
                    'message' => $response_message,
                    'code'    => $response_code,
                )));
            }
        }

        if( $date_range == 'last_30_days' ){
            set_transient($transient_key, $response_json, (MINUTE_IN_SECONDS * 60));
        }
        
        return $response_json;
    }

    public function get_all_reports_data_standard_prepared(){
        $reports_data = array();

        $response_batch = $this->report_batch_request();
        $batch_1_data = json_decode($response_batch['batch1'], true);
        $batch_2_data = json_decode($response_batch['batch2'], true);

        if( !empty($batch_1_data['error']) || !empty($batch_2_data['error']) ){
            $reports['error'] = array(
                'message' => !empty($batch_1_data['error']['message']) ? $batch_1_data['error']['message'] : $batch_2_data['error']['message'],
                'code'    => !empty($batch_1_data['error']['code']) ? $batch_1_data['error']['code'] : $batch_2_data['error']['code'],
            );

            return $reports;
        }

        // Loop through each request
        foreach( $batch_1_data['reports'] as $response_index => $response_data  ){
            if ($response_index == 0) {
                $reports_data['sessions'] = $this->prepare_session_data($response_data);
            } elseif ($response_index == 1) {
                $reports_data['page_views'] = $this->prepare_session_data($response_data);
            } elseif ($response_index == 2) {
                $reports_data['bounce_rate'] = $this->prepare_session_data($response_data);
            } elseif ($response_index == 3) {
                $reports_data['top_pages'] = $this->prepare_top_pages($response_data);
            } elseif ($response_index == 4) {
                $reports_data['top_referrers'] = $this->prepare_top_pages($response_data);
            }
        }

        foreach( $batch_2_data['reports'] as $response_index => $response_data  ){
            if ($response_index == 0) {
                $reports_data['top_countries'] = $this->prepare_top_pages($response_data);
            } elseif ($response_index == 1) {
                $reports_data['user_types'] = $this->prepare_device_types($response_data);
            } elseif ($response_index == 2) {
                $reports_data['device_types'] = $this->prepare_device_types($response_data);
            }
        }

        return $reports_data;
    }

    /**
     * This function returns an array of Google Analytics API requests for various metrics and
     * dimensions.
     * 
     * @return array of Google Analytics API requests for various metrics and dimensions such as
     * sessions, page views, bounce rate, page path, referrer, countries, user types, and device types.
     */
    public function get_ga4_requests(){
        $get_date_range = !empty($_GET['date_range']) ? sanitize_text_field($_GET['date_range']) : ''; // PHPCS:ignore
        $date_range     = $this->get_date_range($get_date_range);

        $current_date_range = array(
            'startDate' => $date_range['current']['start_date'],
            'endDate'   => $date_range['current']['end_date'],
            'name'      => 'current'
        );
        
        $previous_date_range = array(
            'startDate' => $date_range['previous']['start_date'],
            'endDate'   => $date_range['previous']['end_date'],
            'name'      => 'previous'
        );

        $date_range_compare = array(
            $current_date_range,
            $previous_date_range
        );

        $requests = array(
            'session' => array(
                'dateRanges' => $date_range_compare,
                'metrics' => array(
                    array(
                        'name' => 'sessions'
                    ),
                ),
                'dimensions' => array(
                    array(
                        'name' => 'date'
                    ),
                ),
                'orderBys' => array(
                    'dimension' => array(
                        'orderType' => 'ALPHANUMERIC',
                        'dimensionName' => 'date'
                    )
                )
            ),
            'page_view' => array(
                'dateRanges' => $date_range_compare,
                'metrics' => array(
                    array(
                        'name' => 'screenPageViews'
                    ),
                ),
                'dimensions' => array(
                    array(
                        'name' => 'date'
                    ),
                ),
                'orderBys' => array(
                    'dimension' => array(
                        'orderType' => 'ALPHANUMERIC',
                        'dimensionName' => 'date'
                    )
                )
            ),
            'bounce_rate' => array(
                'dateRanges' => $date_range_compare,
                'metrics' => array(
                    array(
                        'name' => 'bounceRate'
                    ),
                ),
                'dimensions' => array(
                    array(
                        'name' => 'date'
                    ),
                ),
                'orderBys' => array(
                    'dimension' => array(
                        'orderType' => 'ALPHANUMERIC',
                        'dimensionName' => 'date'
                    )
                )
            ),
            'page_path' => array(
                'dateRanges' => $current_date_range,
                'metrics' => array(
                    array(
                        'name' => 'screenPageViews'
                    ),
                ),
                'dimensions' => array(
                    array(
                        'name' => 'pagePath'
                    ),
                ),
                'limit' => 10
            ),
            'referrer' => array(
                'dateRanges' => $current_date_range,
                'metrics' => array(
                    array(
                        'name' => 'sessions'
                    ),
                ),
                'dimensions' => array(
                    array(
                        'name' => 'firstUserSource'
                    ),
                ),
                'limit' => 10
            ),
            'countries' => array(
                'dateRanges' => $current_date_range,
                'metrics' => array(
                    array(
                        'name' => 'sessions'
                    ),
                ),
                'dimensions' => array(
                    array(
                        'name' => 'country'
                    ),
                ),
                'limit' => 10
            ),
            'user_types' => array(
                'dateRanges' => $current_date_range,
                'metrics' => array(
                    array(
                        'name' => 'activeUsers'
                    ),
                    ),
                'dimensions' => array(
                    array(
                        'name' => 'newVsReturning'
                    ),
                )
            ),
            'device_types' => array(
                'dateRanges' => $current_date_range,
                'metrics' => array(
                    array(
                        'name' => 'activeUsers'
                    ),
                    ),
                'dimensions' => array(
                    array(
                        'name' => 'deviceCategory'
                    ),
                )
            ),
        );

        return $requests;
    }

    /**
     * Prepares session data
     * 
     * @param array response_data The data received as a response from an API call. It is expected to be an
     * array containing information about rows, labels, and metric values.
     * 
     * @return array contains three keys: `labels`, `current_dataset`, and
     * `previous_dataset`.
     */
    public function prepare_session_data( $response_data ){
        if( !empty($response_data['error']) ){
            throw new Exception( esc_html($response_data['error']['message']), esc_html($response_data['error']['code']) );
        }

        $dataset = array(
            'labels'            => array(),
            'current_dataset'   => array(),
            'previous_dataset'  => array(),
        );

        $rows = !empty($response_data['rows']) ? $response_data['rows'] : array();
        $rows = array_filter($rows, function($item){
            if($item['metricValues'][0]['value'] != 0){
                return $item;
            }
        });

        // Loop through each rows
        foreach( $rows as $key => $row ){
            $date   =  $row['dimensionValues'][0]['value'];
            $state  = $row['dimensionValues'][1]['value'];
            $matric_value = $row['metricValues'][0]['value'];

            if( $state == 'current' ){
                $dataset['current_dataset'][$date] = $matric_value;

                // Labels
                $year   = substr($date, 0, 4);
                $month  = substr($date, 4, 2);
                $day    =  substr($date, 6, 2);
                $dateObj = date_create("$year-$month-$date");

                $dataset['labels'][$date] = $dateObj->format('M') . ' ' . $day;
            } elseif( $state == 'previous' ){
                $dataset['previous_dataset'][$date] = $matric_value;
            }
        }

        // Final dataset
        $dataset['labels'] = array_values($dataset['labels']);
        $dataset['current_dataset'] = array_values($dataset['current_dataset']);
        $dataset['previous_dataset'] = array_values($dataset['previous_dataset']);

        $current_total  = 0;
        $previous_total = 0;

        if( !empty($dataset['current_dataset']) ){
            $current_total = $this->calculate_total($dataset['current_dataset']);
        }

        if( !empty($dataset['previous_dataset']) ){
            $previous_total = $this->calculate_total($dataset['previous_dataset']);
        }

        $dataset['current_total'] = $current_total;
        $dataset['previous_total'] = $previous_total;

        return $dataset;
    }

    /**
     * Calculate total
     * 
     * @param array data The data to calculate the total.
     * 
     * @return int
     */
    public function calculate_total( $dataset ){
        $total = 0;

        if( count($dataset) ){
            $total = array_sum( array_values( $dataset ) );
            $total = floatval($total);
        }
        
        return $total;
    }

    /**
     * Prepares device types data by extracting labels and values from a response data
     * array.
     * 
     * @param array response_data It is an array that contains the data retrieved from an API response.
     * 
     * @return array with two keys: 'labels' and 'values'. The 'labels' key contains an array of
     * dimension values from the input data, while the 'values' key contains an array of metric values
     * from the input data.
     */
    public function prepare_device_types( $response_data ){
        $dataset  = array(
            'labels' => array(),
            'values' => array()
        );

        $rows = !empty($response_data['rows']) ? $response_data['rows']: array();

        // Loop through each rows
        foreach( $rows as $row ){
            // metricValues 
            foreach($row['metricValues'] as $key => $value){
                $dataset['values'][] = $value['value'];
            }
            
            // dimensionValues
            foreach($row['dimensionValues'] as $key => $value){
                $dataset['labels'][] = $value['value'];
            }
        }

        // Final data
        return $dataset;
    }

	/**
    * Prepares top pages data by extracting metric and dimension values from a response
    * data array.
    * 
    * @param array response_data The parameter  is an array that contains the response data
    * from an API call. It may contain an error message and code.
    * 
    * @return array of data sets that have been prepared from the response data.
    */
    public function prepare_top_pages( $response_data ){
        if( !empty($response_data['error']) ){
            throw new Exception( esc_html($response_data['error']['message']), esc_html($response_data['error']['code']) );
        }

        $dataset = array();

        $rows = !empty($response_data['rows']) ? $response_data['rows'] : array();

        // Loop through each rows
        foreach( $rows as $key => $row ){
            // metricValues 
            foreach($row['metricValues'] as $mkey => $value){
                $dataset[$key][] = $value['value'];
            }
            
            // dimensionValues
            foreach($row['dimensionValues'] as $dkey => $value){
                $dataset[$key][] = $value['value'];
            }
        }

        return $dataset;
    }

    /**
     * Prepares active users data by extracting metric values from a response
     * data array.
     * 
     * @param array response_data The parameter  is an array that contains the response data
     * from an API call. It may contain an error message and code.
     * 
     * @return int
     */
    public function prepare_active_users( $response_data ){
        if( !empty($response_data['error']) ){
            throw new Exception( esc_html($response_data['error']['message']), esc_html($response_data['error']['code']) );
        }

        $active_usres = 0;

        $rows = !empty($response_data['rows']) ? $response_data['rows'] : array();

        // Loop through each rows
        foreach( $rows as $key => $row ){
            // metricValues 
            foreach($row['metricValues'] as $mkey => $value){
                $active_usres += $value['value'];
            }
        }

        return $active_usres;
    }
}