<?php
/**
 * A Trait to help with managing the plugin admin functions
 */
namespace HtEasyGa4;

use Exception;
use WP_Error;

trait Helper_Trait {
	/**
    * File path of the Core plugin
    * @return string
    */
	public function get_pro_plugin_file(){
        return 'ht-easy-google-analytics-pro/ht-easy-google-analytics-pro.php';
    }

    /**
    * File path of the WooCommerce plugin
    * @return string
    */
	public function get_woocommerce_file(){
        return 'woocommerce/woocommerce.php';
    }

    /**
	 * This function checks if the WooCommerce plugin is active in WordPress.
	 *
	 * @return bool
	 */
	public function is_pro_plugin_active(){
		if( is_plugin_active( $this->get_pro_plugin_file() ) ){
			return true;
		}

		return false;
	}

	/**
	 * This function checks if the WooCommerce plugin is active in WordPress.
	 *
	 * @return bool
	 */
	public function is_woocommerce_active(){
		if( is_plugin_active( $this->get_woocommerce_file() ) ){
			return true;
		}

		return false;
	}

    /**
     * Returns the measurement ID for Google Analytics 4, either from the plugin's
     * options or from a separate option for the GA4 ID.
     * 
     * @return string the measurement ID. If the measurement ID is set in the plugin options, it will return
     * that value. Otherwise, it will return the value of the `ht_easy_ga4_id` option.
     */
    public function get_measurement_id(){
        if( !empty($this->get_option('measurement_id')) ){
            return $this->get_option('measurement_id');
        }

        $ht_easy_ga4_id = get_option('ht_easy_ga4_id') ? get_option('ht_easy_ga4_id') : '';
        return $ht_easy_ga4_id;
    }

    public function get_data($query_str){
        $get_data = wp_unslash($_GET);

        if( !empty($get_data[$query_str]) ){
            return $get_data[$query_str];
        }

        return '';
    }

    /**
     * > It sends a POST request to our server endpoint with the user's email address, and if the
     * request is successful, it returns the new access token
     * 
     * @param email The email address of the user to get an access token.
     * 
     * @return string
     */
    function generate_access_token( $email ){
        $request_url = \Ht_Easy_Ga4::$htga4_rest_base_url . 'v1/get-access-token';

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
     * This function returns the access token stored in the 'htga4_access_token' option.
     * 
     * @return string the value of the 'htga4_access_token' option.
     */
    public function get_access_token(){
        return get_transient('htga4_access_token');
    }

    /**
     * @param data_name The parameter "data_name" is a string that represents the name of the data that
     * needs to be retrieved from the "htga4_api_data" option.
     * 
     * @return string|array value of the specified key from the 'htga4_api_data' option array. If the specified
     * key is not found or the 'htga4_api_data' option array is empty, an empty string will be
     * returned.
     */
    public function get_api_data( $data_name ){
        $api_data = get_option('htga4_api_data', array(
            'userinfo' => array(),
            'accounts' => array(),
            'properties' => array(),
            'reports' => array(),
            'data_stream' => array(),
            'data_streams' => array()
        ));

        return !empty($api_data[$data_name]) ? $api_data[$data_name] : '';
    }

    /**
     * This function updates the API data stored in the WordPress options table with the provided data
     * for a specific data name.
     * 
     * @param data_name a string representing the name of the data being updated in the API data array.
     * @param data  is the data that needs to be updated in the API. It could be an array, object,
     * or any other data type.
     * 
     * @return void
     */
    public function update_api_data( $data_name, $data ){
        $api_data = (array) get_option('htga4_api_data', array(
            'userinfo' => array(),
            'accounts' => array(),
            'properties' => array(),
            'reports' => array(),
            'data_stream' => array(),
            'data_streams' => array()
        ));

        if( empty($api_data[$data_name]) ){
            $api_data[$data_name] = $data;
        }

        update_option('htga4_api_data', $api_data);
    }

    /**
     * Request userinfo
     * 
     * @return array|WP_Error either the response data as an array if it exists and does not contain an error, or a
     * WP_Error object if there is an error or the response data is null.
     */
    public function request_userinfo(){
        $access_token = $this->get_access_token();
        $request_url = 'https://www.googleapis.com/oauth2/v3/userinfo';

        $request_args = array(
            'timeout'     => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ),
            'sslverify' => false,
        );
        
        $response = wp_remote_get( $request_url, $request_args );
        $body               = wp_remote_retrieve_body( $response );
        $response_data      = json_decode( $body, true );

        // response has some data
        if( $response_data ){

            if( !empty($response_data['error']) ){
                delete_transient('htga4_access_token');
                
                return new WP_Error($response['response']['code'], $response_data['error_description']);
            }

            return $response_data;

        } else { // $response_data = null
            return new WP_Error($response['response']['code'], __('Not found', 'ht-easy-ga4'));
        }
    }

    /**
     * @param account The account ID.
     * @param access_token The access token you received from the Google API.
     * 
     * @return array|string
     */
    public function request_accounts(){
        $accounts_result_transient = get_transient('htga4_accounts');

        if( !empty($accounts_result_transient['accounts']) ){ 
            return get_transient('htga4_accounts');
        }

        if( !empty($accounts_result_transient['error']) && !empty($accounts_result_transient['error']['code']) && $accounts_result_transient['error']['code'] != 403 ){ 
            return get_transient('htga4_accounts');
        }

        $access_token = $this->get_access_token();
        $request_url = 'https://analyticsadmin.googleapis.com/v1beta/accounts';

        $request_args = array(
            'timeout'     => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ),
            'sslverify' => false,
        );
        
        $raw_response   = wp_remote_get( $request_url, $request_args );

        // Something wrong happened on the translate server side.
        $response_code = wp_remote_retrieve_response_code( $raw_response );
        if ( is_wp_error( $raw_response ) ) {
            $return_data = array(
                'error' => array(
                    'message' => $raw_response->get_error_message()
                )
            );

            set_transient('htga4_accounts', $return_data, (MINUTE_IN_SECONDS * 58));
            return $return_data;
        }

        $response = json_decode( wp_remote_retrieve_body( $raw_response ), true );

        // Has accounts
        if( $response ){
            set_transient('htga4_accounts', $response, (MINUTE_IN_SECONDS * 58));
            return $response;
        } else { // No account
            $return_data = array(
                'error' => array(
                    'message' => __('There is no account!', 'ht-easy-ga4')
                )
            );

            set_transient('htga4_accounts', $return_data, (MINUTE_IN_SECONDS * 58));
            return $return_data;
        }
    }

    public function request_properties( $account ){
        if( get_transient('htga4_properties') && !wp_doing_ajax() ){
            return get_transient('htga4_properties');
        }

        $access_token   = $this->get_access_token();
        $request_url    = "https://analyticsadmin.googleapis.com/v1alpha/properties?filter=parent:accounts/{$account}";

        $request_args = array(
            'timeout'     => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ),
            'sslverify' => false,
        );

        $response   = wp_remote_get( $request_url, $request_args );

        if ( is_wp_error( $response ) ) {
            $return_data = array( 'error' => array(
                'message' => $response->get_error_message()
            ));
            
            set_transient('htga4_properties', $return_data, (MINUTE_IN_SECONDS * 58));
            return $return_data;
        }

        $body              = wp_remote_retrieve_body( $response );
        $response_data     = json_decode( $body, true );

        // if success
        if( $response['http_response']->get_status() === 200){
            set_transient('htga4_properties', $response_data, (MINUTE_IN_SECONDS * 58));
            return $response_data;
        } else {
            if( !empty($response_data['error']) ){
                delete_transient('htga4_access_token');
                
            }
        }

        set_transient('htga4_properties', $response_data, (MINUTE_IN_SECONDS * 58));
        return $response_data;
    }

    /**
     * @param property
     * 
     * @return array|string
     */
    public function request_data_streams( $property ){
        if( get_transient('htga4_data_streams') && !wp_doing_ajax() ){
            return get_transient('htga4_data_streams');
        }

        $access_token   = $this->get_access_token();

        $request_url  = "https://analyticsadmin.googleapis.com/v1beta/properties/{$property}/dataStreams";
        $request_args = array(
            'timeout' => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'sslverify' => false,
        );

        $raw_response   = wp_remote_get( $request_url, $request_args );

        if ( is_wp_error( $raw_response ) ) {
            $return_data = array( 
                'error' => array(
                    'message' => $raw_response->get_error_message()
                )
            );

            set_transient('htga4_data_streams', $return_data, (MINUTE_IN_SECONDS * 58));
            return $return_data;
        }

        $response = json_decode( wp_remote_retrieve_body( $raw_response ), true );

        // Has properties
        if( $response ){
            set_transient('htga4_data_streams', $response, (MINUTE_IN_SECONDS * 58));
            return $response;
        } else { // No property
            $return_data = array(
                'error' => array(
                    'message' => __('There is no data stream!', 'ht-easy-ga4')
                )
            );

            set_transient('htga4_data_streams', $return_data, (MINUTE_IN_SECONDS * 58));
            return $return_data;
        }
    }

    /**
     * This function sends a request to the Google Analytics API to retrieve data from a specific data
     * stream for a given property, using an access token for authentication.
     * 
     * @param property
     * @param stream_id
     * 
     * @return array containing the response data from a GET request to a Google Analytics data
     * stream API endpoint. If the request is successful (HTTP status code 200), the function returns
     * the response data. If the request fails or returns an error, the function returns an array with
     * an error message.
     */
    public function request_data_stream( $property, $stream_id ){
        $access_token   = $this->get_access_token();

        $request_url  = "https://analyticsadmin.googleapis.com/v1beta/properties/{$property}/dataStreams/{$stream_id}";
        $request_args = array(
            'timeout'     => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ),
            'sslverify' => false,
        );

        $response   = wp_remote_get( $request_url, $request_args );

        if ( is_wp_error( $response ) ) {
            return array( 'error' => array(
                'message' => $response->get_error_message()
            ));
        }

        $body       = wp_remote_retrieve_body( $response );
        $response_data     = json_decode( $body, true );

        // if success
        if( $response['http_response']->get_status() === 200){
            return $response_data;
        } else {
            if( !empty($response_data['error']) ){
                delete_transient('htga4_access_token');
                
            }
        }

        return $response_data;
    }

    /**
     * This function sends batch requests to the Google Analytics API and returns an array of reports.
     * 
     * @return array of reports containing data on sessions, page views, bounce rate, top pages, top
     * referrers, top countries, user types, and device types. If there is an error, the function
     * returns an error message.
     */
    public function report_batch_request(){
        $access_token = $this->get_access_token();
        $request_url  = sprintf('https://analyticsdata.googleapis.com/v1beta/properties/%s:batchRunReports', $this->get_option('property'));

        $ga4_requests = $this->get_ga4_requests();

        $reports = array(
            'sessions' => array(),
            'page_views' => array(),
            'bounce_rate' => array(),
            'top_pages' => array(),
            'top_referrers' => array(),
            'top_countries' => array(),
            'user_types' => array(),
            'device_types' => array(),
        );

        $response = wp_remote_post($request_url, array(
            'method' => 'POST',
            'timeout' => 20,
            'sslverify' => false,
            'headers' => array(
                'timeout'       => 20,
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode(array(
                'requests' => array(
                    $ga4_requests['session'],
                    $ga4_requests['page_view'],
                    $ga4_requests['bounce_rate'],
                    $ga4_requests['page_path'],
                    $ga4_requests['referrer'],
                )
            ))
        ));

        $response2 = wp_remote_post($request_url, array(
            'method' => 'POST',
            'timeout' => 20,
            'sslverify' => false,
            'headers' => array(
                'timeout'       => 20,
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode(array(
                'requests' => array(
                    $ga4_requests['countries'],
                    $ga4_requests['user_types'],
                    $ga4_requests['device_types']
                )
            ))
        ));

        // Return error message when is_wp_error
        if ( is_wp_error( $response ) ) {
            return $response->get_error_message();
        }

        $batch_1_data = json_decode($response['body'], true);
        $batch_2_data = json_decode($response2['body'], true);

        if( $response['response']['code'] == 200 ){
            // Loop through each request
            foreach( $batch_1_data['reports'] as $response_index => $response_data  ){
                if ($response_index == 0) {
                    $reports['sessions'] = $this->prepare_session_data($response_data);
                } elseif ($response_index == 1) {
                    $reports['page_views'] = $this->prepare_session_data($response_data);
                } elseif ($response_index == 2) {
                    $reports['bounce_rate'] = $this->prepare_session_data($response_data);
                } elseif ($response_index == 3) {
                    $reports['top_pages'] = $this->prepare_top_pages($response_data);
                } elseif ($response_index == 4) {
                    $reports['top_referrers'] = $this->prepare_top_pages($response_data);
                }
            }
        } elseif( !empty($batch_1_data['error']['message']) ) {
            return $batch_1_data['error']['message'];
        } else {
            return $response['response']['code'] . ' ' . $response['response']['message'];
        }

        if( $response2['response']['code'] == 200 ){
            // Loop through each request
            foreach( $batch_2_data['reports'] as $response_index => $response_data  ){
                if ($response_index == 0) {
                    $reports['top_countries'] = $this->prepare_top_pages($response_data);
                } elseif ($response_index == 1) {
                    $reports['user_types'] = $this->prepare_device_types($response_data);
                } elseif ($response_index == 2) {
                    $reports['device_types'] = $this->prepare_device_types($response_data);
                }
            }
        } elseif( !empty($batch_2_data['error']['message']) ) {
            return $batch_2_data['error']['message'];
        } else {
            return $response2['response']['code'] . ' ' . $response2['response']['message'];
        }

        return $reports;
    }

    public function report_batch_request_ecommerce(){
        $access_token = $this->get_access_token();
        $request_url  = sprintf('https://analyticsdata.googleapis.com/v1beta/properties/%s:batchRunReports', $this->get_option('property'));

        $ga4_requests = $this->get_ga4_requests_ecommerce();

        $reports = array(
            'transactions' => array(),
            'average_purchase_revenue' => array(),
            'purchase_revenue' => array(),
            'items_viewed' => array(),
            'items_added_to_cart' => array(),
            'items_checked_out' => array(),
            'items_purchased' => array(),
            'top_products' => array(),
            'top_brands' => array(),
            'top_referrers' => array(),
        );

        $response = wp_remote_post($request_url, array(
            'method' => 'POST',
            'timeout' => 20,
            'sslverify' => false,
            'headers' => array(
                'timeout'       => 20,
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode(array(
                'requests' => array(
                    $ga4_requests['transactions'],
                    $ga4_requests['average_purchase_revenue'],
                    $ga4_requests['purchase_revenue'],
                    $ga4_requests['items_viewed'],
                    $ga4_requests['items_added_to_cart'],
                )
            ))
        ));

        // Batch reuqest allow 5 requests per batch
        $response2 = wp_remote_post($request_url, array(
            'method' => 'POST',
            'timeout' => 20,
            'sslverify' => false,
            'headers' => array(
                'timeout'       => 20,
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode(array(
                'requests' => array(
                    $ga4_requests['items_checked_out'],
                    $ga4_requests['items_purchased'],
                    $ga4_requests['top_products'],
                    $ga4_requests['top_brands'],
                    $ga4_requests['top_referrers'],
                )
            ))
        ));

        // Return error message when is_wp_error
        if ( is_wp_error( $response ) ) {
            return $response->get_error_message();
        }

        $batch_1_data = json_decode($response['body'], true);
        $batch_2_data = json_decode($response2['body'], true);

        if( $response['response']['code'] == 200 ){
            // Loop through each request
            foreach( $batch_1_data['reports'] as $response_index => $response_data  ){
                if ($response_index == 0) {
                    $reports['transactions'] = $this->prepare_session_data($response_data);
                } elseif ($response_index == 1) {
                    $reports['average_purchase_revenue'] = $this->prepare_session_data($response_data);
                } elseif ($response_index == 2) {
                    $reports['purchase_revenue'] = $this->prepare_session_data($response_data);
                } elseif ($response_index == 3) {
                    $reports['items_viewed'] = $this->prepare_items_viewed_data($response_data);
                } elseif ($response_index == 4) {
                    $reports['items_added_to_cart'] = $this->prepare_items_viewed_data($response_data);
                }
            }
        } elseif( !empty($batch_1_data['error']['message']) ) {
            return $batch_1_data['error']['message'];
        } else {
            return $response['response']['code'] . ' ' . $response['response']['message'];
        }

        if( $response['response']['code'] == 200 ){
            // Loop through each request
            foreach( $batch_2_data['reports'] as $response_index => $response_data  ){
                if ($response_index == 0) {
                    $reports['items_checked_out'] = $this->prepare_items_viewed_data($response_data);
                } elseif ($response_index == 1) {
                    $reports['items_purchased'] = $this->prepare_items_viewed_data($response_data);
                } elseif ($response_index == 2) {
                    $reports['top_products'] = $this->prepare_top_products($response_data);
                } elseif ($response_index == 3) {
                    $reports['top_brands'] = $this->prepare_top_products($response_data);
                } elseif ($response_index == 4) {
                    $reports['top_referrers'] = $this->prepare_top_products($response_data);
                }
            }
        } elseif( !empty($batch_2_data['error']['message']) ) {
            return $batch_2_data['error']['message'];
        } else {
            return $response['response']['code'] . ' ' . $response['response']['message'];
        }

        return $reports;
    }

    /**
     * This function returns an array of Google Analytics API requests for various metrics and
     * dimensions.
     * 
     * @return array of Google Analytics API requests for various metrics and dimensions such as
     * sessions, page views, bounce rate, page path, referrer, countries, user types, and device types.
     */
    public function get_ga4_requests(){
        $get_date_range = !empty($_GET['date_range']) ? sanitize_text_field($_GET['date_range']) : '';
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

    public function get_ga4_requests_ecommerce(){
        $get_date_range = !empty($_GET['date_range']) ? sanitize_text_field($_GET['date_range']) : '';
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
            'transactions' => array(
                'dateRanges' => $date_range_compare,
                'metrics' => array(
                    array(
                        'name' => 'transactions'
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
            'average_purchase_revenue' => array(
                'dateRanges' => $date_range_compare,
                'metrics' => array(
                    array(
                        'name' => 'averagePurchaseRevenue'
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
            'purchase_revenue' => array(
                'dateRanges' => $date_range_compare,
                'metrics' => array(
                    array(
                        'name' => 'purchaseRevenue'
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
            'items_viewed' => array(
                'dateRanges' => $date_range_compare,
                'metrics' => array(
                    array(
                        'name' => 'itemsViewed'
                    ),
                ),
            ),
            'items_added_to_cart' => array(
                'dateRanges' => $date_range_compare,
                'metrics' => array(
                    array(
                        'name' => 'itemsAddedToCart'
                    ),
                )
            ),
            'items_checked_out' => array(
                'dateRanges' => $date_range_compare,
                'metrics' => array(
                    array(
                        'name' => 'itemsCheckedOut'
                    ),
                ),
            ),
            'items_purchased' => array(
                'dateRanges' => $date_range_compare,
                'metrics' => array(
                    array(
                        'name' => 'itemsPurchased'
                    ),
                ),
            ),

            'top_products' => array(
                'dateRanges' => $current_date_range,
                'metrics' => array(
                    array(
                        'name' => 'itemRevenue', // Response will be order by this (first) metric
                    ),
                    array(
                        'name' => 'itemsPurchased',
                    ),
                    array(
                        'name' => 'itemsViewed',
                    ),
                    array(
                        'name' => 'itemsAddedToCart',
                    ),
                    array(
                        'name' => 'cartToViewRate',
                    ),
                    array(
                        'name' => 'purchaseToViewRate',
                    ),
                ),
                'dimensions' => array(
                    array(
                        'name' => 'itemName'
                    ),
                ),
                'limit' => 10,

            ),

            'top_brands' => array(
                'dateRanges' => $current_date_range,
                'metrics' => array(
                    array(
                        'name' => 'itemRevenue', // Response will be order by this (first) metric
                    ),
                    array(
                        'name' => 'itemsViewed',
                    ),
                    array(
                        'name' => 'itemsPurchased',
                    ),
                ),
                'dimensions' => array(
                    array(
                        'name' => 'itemBrand'
                    ),
                ),
                'limit' => 10
            ),

            'top_referrers' => array(
                'dateRanges' => $current_date_range,
                'metrics' => array(
                    array(
                        'name' => 'itemRevenue', // Response will be order by this (first) metric
                    ),
                    array(
                        'name' => 'itemsViewed',
                    ),
                    array(
                        'name' => 'itemsPurchased',
                    ),
                ),
                'dimensions' => array(
                    array(
                        'name' => 'firstUserSource'
                    ),
                ),
                'limit' => 10
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
            throw new Exception( $response_data['error']['message'], $response_data['error']['code'] );
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

        return $dataset;
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
            throw new Exception( $response_data['error']['message'], $response_data['error']['code'] );
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

    public function prepare_top_products( $response_data ){
        if( !empty($response_data['error']) ){
            throw new Exception( $response_data['error']['message'], $response_data['error']['code'] );
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

    public function prepare_items_viewed_data( $response_data ){
        if( !empty($response_data['error']) ){
            throw new Exception( $response_data['error']['message'], $response_data['error']['code'] );
        }

        $dataset = array(
            'previous'  => 0,
            'current'   => 0,
        );

        $rows = !empty($response_data['rows']) ? $response_data['rows'] : array();
        $rows = array_filter($rows, function($item){
            if($item['metricValues'][0]['value'] != 0){
                return $item;
            }
        });

        // Loop through each rows
        foreach( $rows as $key => $row ){
            $state  = $row['dimensionValues'][0]['value'];
            $matric_value = $row['metricValues'][0]['value'];

            if( $state == 'current' ){
                $dataset['current'] = $matric_value;
            } elseif( $state == 'previous' ){
                $dataset['previous'] = $matric_value;
            }
        }

        return $dataset;
    }

    /**
     * The function takes a parameter and returns an array of date ranges based on the parameter value
     * or the value of a custom date range passed through the GET request.
     * 
     * @param string param
     * 
     * @return array with two sub-arrays: 'current' and 'previous'. Each sub-array contains two
     * key-value pairs: 'start_date' and 'end_date'.
     */
    public function get_date_range( $param ) {
        $current_end_date   = date('Y-m-d'); // Today's date
        $get_data       = wp_unslash($_GET);

        if( !empty($get_data['date_range']) && strpos($get_data['date_range'], ',') ){
            $param = 'custom';
        }
        
        switch ( $param ) {
            case 'last_7_days':
                $current_start_date = date('Y-m-d', strtotime('-7 days', strtotime($current_end_date)));
                $current_end_date = 'yesterday';

                $previous_start_date = date('Y-m-d', strtotime('-14 days', strtotime($current_end_date)));
                $previous_end_date = date('Y-m-d', strtotime('-8 days', strtotime($current_end_date)));
                break;

            case 'last_15_days':
                $current_start_date = date('Y-m-d', strtotime('-15 days', strtotime($current_end_date)));
                $current_end_date = 'yesterday';

                $previous_start_date = date('Y-m-d', strtotime('-30 days', strtotime($current_end_date)));
                $previous_end_date = date('Y-m-d', strtotime('-16 days', strtotime($current_end_date)));
                break;

            case 'custom':
                $date_range_arr     = explode(',', $get_data['date_range']);
                $current_start_date = sanitize_text_field($date_range_arr[0]);
                $current_end_date = sanitize_text_field($date_range_arr[1]);

                $d1 = new \DateTime($current_start_date);
                $d2 = new \DateTime($current_end_date);
                $interval = $d1->diff($d2);
                $count = $interval->days + 1;

                $previous_start_date = date('Y-m-d', strtotime("-$count days", strtotime($current_start_date)));
                $previous_end_date = date('Y-m-d', strtotime("-$count days", strtotime($current_end_date)));
                break;
            default:
                // last_30_days
                $current_start_date = date('Y-m-d', strtotime('-30 days', strtotime($current_end_date)));
                $current_end_date = 'yesterday';

                $previous_start_date = date('Y-m-d', strtotime('-60 days', strtotime($current_end_date)));
                $previous_end_date = date('Y-m-d', strtotime('-31 days', strtotime($current_end_date)));
                break;
        }
        
        return array(
            'current' => array(
                'start_date' => $current_start_date,
                'end_date' => $current_end_date
            ),
            'previous' => array(
                'start_date' => $previous_start_date,
                'end_date' => $previous_end_date
            )
        );
    }

    /**
     * Checks if a given date range matches the current date range selected by the
     * user and returns a CSS class name accordingly.
     * 
     * @param date_range
     * 
     * @return string value of 'htga4-current' if the condition is met, otherwise it returns an empty
     * string.
     */
    public function get_current_class( $date_range ){
        $get_date_range = isset($_GET['date_range']) ? sanitize_text_field($_GET['date_range']) : 'last_30_days';
        if( strpos($get_date_range, ',') && $date_range == 'custom' ){
            return 'htga4-current';
        }
        
        return $get_date_range === $date_range ? 'htga4-current' : '';
    }

    /**
     * Returns the current admin URL.
     * 
     * @return string the current admin URL with certain query arguments removed.
     */
    public function get_current_admin_url() {
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );
    
        if ( ! $uri ) {
            return '';
        }
    
        return remove_query_arg( array( '_wpnonce', '_wc_notice_nonce', 'wc_db_update', 'wc_db_update_nonce', 'wc-hide-notice' ), admin_url( $uri ) );
    }

    /**
     * This function retrieves a specific option value from the 'ht_easy_ga4_options' array or returns
     * a default value if the option is not set.
     * 
     * @param option_name The name of the option to retrieve from the options array.
     * @param default The default value to return if the option is not set or does not exist.
     * 
     * @return string|array
     */
    public function get_option( $option_name = '', $default = null ) {
        $options = get_option( 'ht_easy_ga4_options' );
    
        return ( isset( $options[$option_name] ) ) ? $options[$option_name] : $default;
    }

    /**
     * @param  [string] $section
     * @param  [string] $option_key
     * @param  string $new_value
     * 
     * @return [string]
     */
    function update_option( $section, $option_key, $new_value ){
        $options_data = get_option( $section );

        if( isset( $options_data[$option_key] ) ){
            $options_data[$option_key] = $new_value;
        }else{
            $options_data = array( $option_key => $new_value );
        }

        update_option( $section, $options_data );
    }

    public function get_config_file(){
		// if wp environment is test and debug is true then use test config file
		if( defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'development' ){
			return HT_EASY_GA4_PATH .'/includes/config-test.json';
		} else {
			return HT_EASY_GA4_PATH .'/includes/config.json';
		}
	}

    /**
    * @param name The name of the configuration value to retrieve from the config.json.
    * 
    * @return string|array
    */
    public function get_config( $name = '' ){
        $file         =  $this->get_config_file();

        $return_value = '';
        $config_arr   = array();

        if( is_readable($file) ){
            $file_content = file_get_contents( $file );
            $config_arr   = json_decode( $file_content, true );
        }

        if( !empty($name) ){
            $return_value = isset($config_arr['web'][$name]) ? $config_arr['web'][$name] : '';

            if( $name === 'redirect_uris' && is_array($return_value) ){
                $return_value = current($return_value);
            }

            if( $name === 'javascript_origins' && is_array($return_value) ){
                $return_value = current($return_value);
            }
        } else {
            $return_value = $config_arr;
        }

        return $return_value;
    }

    /**
    * Returns user roles with key => value pair.
    * 
    * @return array
    */
	public function get_roles_dropdown_options(){
		global $wp_roles;
		$options = array();

		if ( ! empty( $wp_roles ) ) {
		  if ( ! empty( $wp_roles->roles ) ) {
			foreach ( $wp_roles->roles as $role_key => $role_value ) {
			  $options[$role_key] = $role_value['name'];
			}
		  }
		}

		return $options;
	}
    
    public function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}