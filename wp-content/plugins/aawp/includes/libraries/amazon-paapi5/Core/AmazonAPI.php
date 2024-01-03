<?php
/**
 * Amazon Product Advertising API v5
 *
 * The use of this library is strictly prohibited without explicit permission.
 *
 * Copyright 2020 flowdee. All Rights Reserved.
 *
 * Twitter: https://twitter.com/flowdee
 * GitHub: https://github.com/flowdee
 */
namespace Flowdee\AmazonPAAPI5WP;

/**
 * Class AmazonAPI
 *
 * @category Class
 * @package Flowdee\AmazonPAAPI5
 * @author flowdee
 */
class AmazonAPI {

    /**
     * Debug mode state
     *
     * @var bool
     */
    private $debug = false;

    /**
     * Configuration
     *
     * @var Configuration
     */
    protected $configuration;

    /**
     * Amazon_API constructor.
     */
    public function __construct() {
        // Silence is golden.
    }

    /**
     * Set configuration.
     *
     * @param $access_key
     * @param $secret_key
     * @param $partner_tag
     * @param $store
     */
    public function setConfiguration( $access_key, $secret_key, $partner_tag, $store ) {

        // Set configuration.
        $this->configuration = new Configuration( $access_key, $secret_key, $partner_tag, $store );
    }

    /**
     * Test connection.
     *
     * @param array $args
     * @return bool|mixed
     */
    public function testConnection( $args = array() ) {

        // Defaults.
        $defaults = array(
            'keywords' => 'Harry Potter'
        );

        // Parse args.
        $args = wp_parse_args( $args, $defaults );

        // Set up request.
        $TestRequest = new TestRequest();
        $TestRequest->setKeywords( $args['keywords'] );

        // Execute request.
        $response = $this->request( $TestRequest, false );

        if ( isset ( $response['error'] ) )
            return $response;

        return ( isset ( $response['SearchResult'] ) ) ? true : false;
    }

    /**
     * Get items
     *
     * @param array $args
     * @return bool
     */

    /**
     * Get items
     *
     * @param array $asins
     * @param array $args
     * @return bool|mixed|null
     */
    public function getItems( $asins, $args = array() ) {

        if ( empty( $asins ) )
            return false;

        // Defaults.
        $defaults = array(
            'condition' => '',
            'merchant' => ''
        );

        // Parse args.
        $args = wp_parse_args( $args, $defaults );

        // Set up request.
        $GetItemsRequest = new GetItemsRequest();
        $GetItemsRequest->setItemIds( $asins );
        $GetItemsRequest->setCondition( $args['condition'] );
        $GetItemsRequest->setOfferCount( 1 );
        $GetItemsRequest->setMerchant( $args['merchant'] );

        // Execute request.
        $response = $this->request( $GetItemsRequest );

        if ( isset ( $response['error'] ) )
            return $response;

        return ( isset( $response['ItemsResult'] ) ) ? $this->getResponseItems( $response['ItemsResult'] ) : null;
    }

    /**
     * Search items
     *
     * @param array $args
     * @return bool
     */
    public function searchItems( $args = array() ) {

        if ( empty( $args['keywords'] ) && empty( $args['browseNodeId'] ) )
            return false;

        // Defaults.
        $defaults = array(
            'keywords' => '',
            'items' => 10,
            'searchIndex' => 'All',
            'sortBy' => '',
            'browseNodeId' => '',
            'condition' => '',
            'merchant' => '',
            'page' => 1
        );

        // Parse args.
        $args = wp_parse_args( $args, $defaults );

        // Set up request.
        $SearchItemsRequest = new SearchItemsRequest();
        $SearchItemsRequest->setKeywords( $args['keywords'] );
        $SearchItemsRequest->setItemCount( $args['items'] );
        $SearchItemsRequest->setItemPage( $args['page'] );
        $SearchItemsRequest->setSearchIndex( $args['searchIndex'] );
        $SearchItemsRequest->setSortBy( $args['sortBy'] );
        $SearchItemsRequest->setBrowseNodeId( $args['browseNodeId'] );
        $SearchItemsRequest->setCondition( $args['condition'] );

        // Execute request.
        $response = $this->request( $SearchItemsRequest );

        if ( isset ( $response['error'] ) )
            return $response;

        return ( isset( $response['SearchResult'] ) ) ? $this->getResponseItems( $response['SearchResult'] ) : null;
    }

    /**
     * Get variations
     *
     * @param string $asin
     * @param array $args
     * @return bool|mixed|null
     */
    public function getVariations( $asin, $args = array() ) {

        if ( empty( $asin ) )
            return false;

        // Defaults.
        $defaults = array(
            'items' => 10,
            'page' => 1
        );

        // Parse args.
        $args = wp_parse_args( $args, $defaults );

        // Set up request.
        $GetVariationsRequest = new GetVariationsRequest();
        $GetVariationsRequest->setASIN( $asin );
        $GetVariationsRequest->setVariationCount( $args['items'] );
        $GetVariationsRequest->setVariationPage( $args['page'] );

        // Execute request.
        $response = $this->request( $GetVariationsRequest );

        if ( isset ( $response['error'] ) )
            return $response;

        return ( isset( $response['VariationsResult'] ) ) ? $this->getResponseItems( $response['VariationsResult'] ) : null;
    }


    /**
     * Make POST request.
     *
     * @param TestRequest|GetItemsRequest|SearchItemsRequest|GetVariationsRequest $Request
     * @param bool $return_items
     * @return mixed
     */
    private function request( $Request, $return_items = true ) {

        // Check if configuration was set.
        if ( empty ( $this->configuration ) )
            return false;

        $host = $this->configuration->getHost();
        $path = $Request->getPath();

        if ( empty ( $host ) || empty( $path ) )
            return false;

        // Complete request.
        $Request->setPartnerTag( $this->configuration->getPartnerTag() );

        // Build payload.
        $payload = json_encode( $Request );

        // Get headers.
        $headers = $this->getRequestHeaders( $Request, $payload );

        if ( empty ( $headers ) || ! is_array( $headers ) )
            return false;

        $response = wp_remote_post( 'https://' . $host . $path, array(
                'method' => 'POST',
                'timeout' => 45,
                'headers' => $headers,
                'body' => $payload,
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response->get_error_message();
        }

        $responseBody = wp_remote_retrieve_body( $response );

        $response = json_decode( $responseBody, true );

        //$this->debug( $response );

        if ( $this->isResponseError( $response ) )
            return array( 'error' => $this->getResponseError( $response ) );

        return $response;
    }

    /**
     * Get response items
     *
     * @param $response
     * @return null/array
     */
    private function getResponseItems( $response ) {

        if ( ! isset ( $response['Items'] ) || ! is_array( $response['Items'] ) || sizeof( $response['Items'] ) == 0 )
            return null;

        $items = array();

        foreach ( $response['Items'] as $item ) {
            $items[] = new Item( $item );
        }

        return $items;
    }

    /**
     * Check if response is an error
     *
     * @param $response
     * @return bool
     */
    private function isResponseError( $response ) {
        return ( isset ( $response['Errors'] ) ) ? true : false;
    }

    /**
     * Get error code & message out of response
     *
     * @param $response
     * @return array
     */
    private function getResponseError( $response ) {

        return array(
            'code' => ( isset ( $response['Errors'][0]['Code'] ) ) ? $response['Errors'][0]['Code'] : 'undefined',
            'message' => ( isset ( $response['Errors'][0]['Message'] ) ) ? $response['Errors'][0]['Message'] : '',
        );
    }

    /**
     * Get request headers
     *
     * @param TestRequest $Request
     * @param $payload
     * @return array
     */
    private function getRequestHeaders( $Request, $payload ) {

        $AWS_Signature = new AWS_Signature_V4(
            $this->configuration->getAccessKey(),
            $this->configuration->getSecretKey()
        );

        // Set region.
        $AWS_Signature->setRegionName( $this->configuration->getRegion() );

        // Set service name.
        $AWS_Signature->setServiceName( "ProductAdvertisingAPI" );

        // Set request path.
        $AWS_Signature->setPath( $Request->getPath() );

        // Set payload.
        $AWS_Signature->setPayload ($payload);

        // Set request method.
        $AWS_Signature->setRequestMethod ("POST");

        // Add headers.
        $AWS_Signature->addHeader ('content-encoding', 'amz-1.0');
        $AWS_Signature->addHeader ('content-type', 'application/json; charset=utf-8');
        $AWS_Signature->addHeader ('host', $this->configuration->getHost() );
        $AWS_Signature->addHeader ('x-amz-target', $Request->getTarget() );

        return $AWS_Signature->getHeaders();
    }

    /**
     * Set debug mode
     */
    public function setDebugMode() {
        $this->debug = true;
    }

    /**
     * Debug
     *
     * @param $args
     * @param string $title
     */
    private function debug( $args, $title = '' ) {

        if ( ! $this->debug )
            return;

        if ( ! empty ( $title ) )
            echo '<h3>' . $title . '</h3>';

        echo '<pre>';
        print_r( $args );
        echo '</pre>';
    }

}