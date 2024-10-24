<?php
namespace Ht_Easy_Ga4;

use Exception;
use WP_Error;

trait Rest_Ecommerce_Request_Handler_Trait {
    use Rest_Request_Handler_Trait;
    
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
                        'name' => 'addToCarts'
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
     * Request Standard reports
     * 
     * @return array
     */
    public function report_batch_request_ecommerce(){
        $response_json = array(
            'batch1' => '',
            'batch2' => '',
        );

        if( !$this->has_proper_request_data() ){
            $response_json['batch1'] = $response_json['batch2'] = wp_json_encode(array('error' => array(
                'message' => __('The request does not have proper data!', 'ht-easy-ga4'),
                'code'    => 400,
            )));

            return $response_json;
        }

        $date_range = isset($_GET['date_range']) ? sanitize_text_field($_GET['date_range']) : 'last_30_days';

        if( $date_range == 'last_30_days' ){
            // Check transient data
            $transient_key = 'htga4_ecommerce_reports_data_' . htga4()->get_unique_transient_suffix();
            $transient_data = get_transient($transient_key);

            if( $transient_data ){
                return $transient_data;
            }
        }

        $access_token = $this->get_access_token();
        $property = $this->get_option('property');

        $request_url  = sprintf('https://analyticsdata.googleapis.com/v1beta/properties/%s:batchRunReports', $property);

        $ga4_requests = $this->get_ga4_requests_ecommerce();

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
                    $ga4_requests['transactions'],
                    $ga4_requests['average_purchase_revenue'],
                    $ga4_requests['purchase_revenue'],
                    $ga4_requests['items_viewed'],
                    $ga4_requests['items_added_to_cart'],
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

        // Batch reuqest allow 5 requests per batch
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
                    $ga4_requests['items_checked_out'],
                    $ga4_requests['items_purchased'],
                    $ga4_requests['top_products'],
                    $ga4_requests['top_brands'],
                    $ga4_requests['top_referrers'],
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
            $response_code2 = wp_remote_retrieve_response_code( $response2_raw );
            $response_message2 = wp_remote_retrieve_response_message( $response2_raw );

            if( $response_code == 200 ){
                $response_json['batch2'] = wp_remote_retrieve_body( $response2_raw );
            } else {
                $response_json['batch2'] = wp_json_encode(array('error' => array(
                    'message' => $response_message2,
                    'code'    => $response_code2,
                )));
            }
        }

        if( $date_range == 'last_30_days'){
            // Set transient data
            set_transient($transient_key, $response_json, (MINUTE_IN_SECONDS * 60));
        }
       

        return $response_json;
    }

    /**
     * Reports data
     *
     * @return array
     */
    public function get_all_reports_data_ecommerce_prepared(){
        $reports_data = [];

        $response_batch = $this->report_batch_request_ecommerce();
        $batch_1_data = json_decode($response_batch['batch1'], true);
        $batch_2_data = json_decode($response_batch['batch2'], true);

        if( !empty($batch_1_data['error']) || !empty($batch_2_data['error']) ){
            $reports['error'] = array(
                'message' => !empty($batch_1_data['error']['message']) ? $batch_1_data['error']['message'] : $batch_2_data['error']['message'],
                'code'    => !empty($batch_1_data['error']['code']) ? $batch_1_data['error']['code'] : $batch_2_data['error']['code'],
            );

            return $reports;
        }

        foreach( $batch_1_data['reports'] as $response_index => $response_data  ){
            if ($response_index == 0) {
                $reports_data['transactions'] = $this->prepare_session_data($response_data);
            } elseif ($response_index == 1) {
                $reports_data['average_purchase_revenue'] = $this->prepare_session_data($response_data);
            } elseif ($response_index == 2) {
                $reports_data['purchase_revenue'] = $this->prepare_session_data($response_data);
            } elseif ($response_index == 3) {
                $reports_data['items_viewed'] = $this->prepare_items_viewed_data($response_data);
            } elseif ($response_index == 4) {
                $reports_data['items_added_to_cart'] = $this->prepare_items_viewed_data($response_data);
            }
        }

        // Loop through each request
        foreach( $batch_2_data['reports'] as $response_index => $response_data  ){
            if ($response_index == 0) {
                $reports_data['items_checked_out'] = $this->prepare_items_viewed_data($response_data);
            } elseif ($response_index == 1) {
                $reports_data['items_purchased'] = $this->prepare_items_viewed_data($response_data);
            } elseif ($response_index == 2) {
                $reports_data['top_products'] = $this->prepare_top_products($response_data);
            } elseif ($response_index == 3) {
                $reports_data['top_brands'] = $this->prepare_top_products($response_data);
            } elseif ($response_index == 4) {
                $reports_data['top_referrers'] = $this->prepare_top_products($response_data);
            }
        }

        return $reports_data;
    }

    public function prepare_top_products( $response_data ){
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

    public function prepare_items_viewed_data( $response_data ){
        if( !empty($response_data['error']) ){
            throw new Exception( esc_html($response_data['error']['message']), esc_html($response_data['error']['code']) );
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
}