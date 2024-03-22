<?php
/**
 * Class AAWP_API
 */

// Load dependencies
require_once AAWP_PLUGIN_DIR . 'includes/libraries/amazon-paapi5/autoload.php';

use Flowdee\AmazonPAAPI5WP;

class AAWP_API {

    /**
     * Amazon API instance
     *
     * @var AmazonPAAPI5WP\AmazonAPI
     */
    protected $Amazon_API;

    /**
     * Verification status
     *
     * @var bool
     */
    protected $verified = false;

    /**
     * Error
     *
     * @var array
     */
    protected $error = array();

    /**
     * Plugin options
     *
     * @var array
     */
    protected $options = array();

    /**
     * Credentials: Country (Store)
     *
     * @var string
     */
    protected $api_country;

    /**
     * Credentials: API Key
     *
     * @var string
     */
    protected $api_key;

    /**
     * Credentials: API secret
     *
     * @var string
     */
    protected $api_secret;

    /**
     * Credentials: API tracking id
     *
     * @var string
     */
    protected $api_tracking_id;

    /**
     * Debug switch (default set to false)
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * AAWP_API constructor.
     */
    public function __construct() {

        // Maybe enable debug mode
        if ( aawp_is_debug() )
            $this->debug = true;

        // Setup plugin options
        $this->options = aawp_get_options();

        // Setup credentials
        $this->setup_credentials();

        // Setup Amazon API instance
        $this->setup_amazon_api();

        // Setup Amazon API configuration
        $this->setup_amazon_api_configuration();
    }

    /**
     * Setup Amazon API instance
     */
    public function setup_amazon_api() {

        // Initialize new Amazon API class
        $this->Amazon_API = new AmazonPAAPI5WP\AmazonAPI();

        // Maybe enable debug mode
        if ( $this->debug )
            $this->Amazon_API->setDebugMode();
    }

    /**
     * Setup Amazon API configuration
     */
    public function setup_amazon_api_configuration() {
        $this->Amazon_API->setConfiguration( $this->api_key, $this->api_secret, $this->api_tracking_id, $this->api_country );
    }

    /**
     * Set up default credentials (from plugin options)
     */
    public function setup_credentials() {

        // Country / store
        $this->api_country = ( ! empty( $this->options['api']['country'] ) ) ? $this->options['api']['country'] : '';

        // Key
        $this->api_key = ( ! empty( $this->options['api']['key'] ) ) ? $this->options['api']['key'] : '';

        // Secret
        $this->api_secret = ( ! empty( $this->options['api']['secret'] ) ) ? $this->options['api']['secret'] : '';

        // Tracking ID
        $this->api_tracking_id = ( ! empty( $this->options['api']['associate_tag'] ) ) ? $this->options['api']['associate_tag'] : '';
    }

    /**
     * Set credentials
     *
     * (...and maybe verify them)
     *
     * @param $country
     * @param $api_key
     * @param $api_secret
     * @param $api_tracking_id
     * @param bool $verify
     */
    public function set_credentials( $country, $api_key, $api_secret, $api_tracking_id, $verify = true ) {

        // Country / store
        $this->api_country = $country;

        // Key
        $this->api_key = $api_key;

        // Secret
        $this->api_secret = $api_secret;

        // Tracking ID
        $this->api_tracking_id = $api_tracking_id;

        // Update Amazon API configuration
        $this->setup_amazon_api_configuration();
    }

    /**
     * Verify given credentials
     *
     * @param $api_key
     * @param $api_secret
     * @param $api_tracking_id
     * @param $api_country
     * @return mixed
     */
    public function verify_credentials( $api_key, $api_secret, $api_tracking_id, $api_country ) {

        if ( ! empty( $api_key ) && ! empty( $api_secret ) && ! empty ( $api_tracking_id ) && ! empty( $api_country ) ) {

            // Initialize new Amazon API class for verification only
            $AmazonAPI = new AmazonPAAPI5WP\AmazonAPI();
            $AmazonAPI->setConfiguration( $api_key, $api_secret, $api_tracking_id, $api_country);

            $response = $AmazonAPI->testConnection();

            if ( ! empty( $response['error'] ) ) {
                return $response['error'];
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * Update verification status
     *
     * @param $verified
     */
    public function set_verified( $verified ) {
        $this->verified = $verified;
    }

    /**
     * Return verification status
     *
     * @return bool
     */
    public function is_verified() {
        return $this->verified;
    }

    /**
     * Set error message
     *
     * @param $e
     * @param bool $pass
     */
    public function set_error_message( $e, $pass = false) {

        // Pass through if error was set manually
        if ( $pass ) {
            $this->error = $e;

        // Handle error object
        } else {
            $error = array();

            $error['code'] = ( isset( $e->faultcode ) ) ? $e->faultcode : null;
            $error['text'] = ( isset( $e->faultstring ) ) ? $e->faultstring : null;

            $this->error = $error;
        }
    }

    /**
     * Get error message
     *
     * @return array
     */
    public function get_error_message() {
        return $this->error;
    }

    /**
     * Get prepared API args
     *
     * @param array $args
     *
     * @return array
     */
    private function get_api_args( $args = array() ) {

        $api_args = array();

        if ( isset( $args['query_items'] ) && is_numeric( $args['query_items'] ) && intval( $args['query_items'] ) != intval( $args['items'] ) ) {
            $api_args['max'] = intval( $args['query_items'] );
        } elseif ( isset( $args['items'] ) ) {
            $api_args['max'] = ( is_numeric( $args['items'] ) ) ? intval( $args['items'] ) : 10;
        }

        if ( isset( $args['search_max'] ) )
            $api_args['max'] = ( is_numeric( $args['search_max'] ) ) ? intval( $args['search_max'] ) : 10;

        if ( isset( $args['browse_node_search'] ) )
            $api_args['browsenode'] = $args['browse_node_search'];

        // Hooking the arguments
        //$args = apply_filters( 'aawp_api_args', $args, $this->atts ); // TODO

        //aawp_debug( $api_args, '$api_args' );
        //aawp_debug_log( $api_args );

        return $api_args;
    }

    /**
     * Get product from API
     *
     * @param array $args
     *
     * @return bool
     */
    public function get_product( $asin, $args = array() ) {

        if ( empty( $asin ) )
            return false;

        $defaults = array(
            //'product_asin' => ''
        );

        // Parse args
        $product_args = wp_parse_args( $args, $defaults );

        //aawp_debug( $product_args, 'get_product' );

        $response = $this->get_items( $asin, $this->get_api_args( $product_args ) );

        //var_dump( $response );
        //echo '<pre>'; print_r( $response ); echo '</pre>';

        if ( ! empty ( $response ) && isset ( $response['error'] )  ) {
            aawp_handle_api_product_error_response( $asin, $response['error'] );
            return null;
        }

        return ( isset( $response[0] ) ) ? $response[0] : $response;
    }

    /**
     * Get products from API
     *
     * @param array $asins
     * @param array $args
     * @return array|null
     */
    public function get_products( $asins = array(), $args = array() ) {

        $defaults = array(
            // Silence
        );

        // Parse args
        $product_args = wp_parse_args( $args, $defaults );

        //aawp_debug( $product_args, 'get_products args' );

        // Updating Chunks
        $chunks = array_chunk( $asins, 10 );

        //aawp_debug( $chunks, 'get_products chunks' );

        $products_fetched = array();

        foreach ( $chunks as $i => $chunk ) {

            //aawp_debug( $chunk );

            // Prepare IDs for API call
            //$id_string = implode(',', $chunk);

            //aawp_debug_display( $chunk, '$chunk chunk' );

            $products = $this->get_items( $chunk, $this->get_api_args( $product_args ) );

            //var_dump( $chunk );
            //echo '<pre>'; print_r( $products ); echo '</pre>';

            if ( ! empty ( $products ) && isset ( $products['error'] )  ) {
                aawp_handle_api_product_error_response( $chunk, $products['error'] );
            }

            /*
             * Error returned, try looping items individually
             */
            if ( is_string( $products ) && sizeof( $chunk ) > 1 ) {

                //aawp_debug_display( $products, 'API returned error for current chunk >> ' . $products . ' >> Trying single loops!' );

                $products_fallback = array();

                foreach ( $chunk as $asin ) {

                    $product = $this->get_items( $asin, $this->get_api_args( $product_args ) );

                    if ( ! empty ( $product ) && isset ( $product['error'] )  ) {
                        aawp_handle_api_product_error_response( $asin, $product['error'] );
                        //echo ' - FAILED: ' . $product . '<br>';
                    } elseif ( is_array( $product ) && isset( $product[0] ) ) {
                        //echo ' - SUCCEED<br>';
                        $products_fallback[] = $product[0];
                    }

                    // Short pause after each api call
                    sleep(1);
                }

                if ( sizeof( $products_fallback ) > 0 )
                    $products = $products_fallback;

            }

            //if ( is_string( $products ) ) {
            //  return $products;

            if ( is_array( $products ) && sizeof( $products ) > 0 ) {
                //echo 'fetched ' . sizeof( $products ) . ' products from API!<br>';
                //echo '$products_fetched before: ' . sizeof( $products_fetched ) . '<br>';
                //$products_fetched = ( sizeof( $products_fetched ) > 0 ) ? $products_fetched + $products : $products;
                $products_fetched = array_merge( $products_fetched, $products );
                //$products_fetched = $products_fetched + $products;
                //echo '$products_fetched after: ' . sizeof( $products_fetched ) . '<br>';
            }

            // Short pause after each api call
            sleep(1);
        }

        //echo 'Result: Products fetched ' . sizeof( $products_fetched ) . '<br>';

        return ( sizeof( $products_fetched ) > 0 ) ? $products_fetched : null;
    }

    /**
     * Get list from API
     *
     * @param array $args
     *
     * @return bool
     */
    public function get_list( $args = array() ) {

        if ( empty( $args['type'] ) || ( empty( $args['keywords'] ) && empty( $args['browse_node_id'] ) ) )
            return null;

        $list_type = $args['type'];
        unset ( $args['type'] );

        // Parse args
        $list_args = array(
            'searchIndex' => ( ! empty ( $args['search_index'] ) ) ? $args['search_index'] : 'All',
            'keywords' => ( ! empty ( $args['keywords'] ) ) ? $args['keywords'] : '',
            'browseNodeId' => ( ! empty ( $args['browse_node_id'] ) && is_numeric( $args['browse_node_id'] ) ) ? $args['browse_node_id'] : '',
            //'browse_node_search' => true,
            'items' => ( ! empty ( $args['items_count'] ) && is_numeric( $args['items_count'] ) ) ? absint( $args['items_count'] ) : 10,
        );

        $list_fetched = null;

        // Set bestseller related args
        if ( 'bestseller' === $list_type ) {
            //$list_args['sortBy'] = 'featured'; // TODO: Temporary disabled due to bad results.

        // Set new releases related args
        } elseif ( 'new_releases' === $list_type ) {
            $list_args['sortBy'] = 'release';
        }

        //aawp_debug( $list_args, 'AAWP_API >> get_list() $args' );

        // Handle paging
        $pages = ceil ( $list_args['items'] / 10 );

        //aawp_debug_log( 'AAWP_API >> get_list() >> Paging >> $list_args[items]: ' . $list_args['items'] . ' - $pages: ' . $pages );

        $list_items = array();

        if ( $pages > 1 ) {

            for( $page = 1; $page <= $pages; $page++ ) {

                // Set page argument.
                $list_args['page'] = $page;

                //aawp_debug_log( 'Fetching Page #' . $page );

                // Fetch items.
                $items_fetched = $this->get_search_items( $list_args );

                if ( ! is_array( $items_fetched ) )
                    break;

                //aawp_debug_log( '$items_fetched: ' . sizeof( $items_fetched ) );

                if ( empty ( $list_items ) ) {
                    $list_items = $items_fetched;

                } else {

                    foreach ( $items_fetched as $item ) {
                        $list_items[] = $item;
                    }
                }
            }


        } else {
            $list_items = $this->get_search_items( $list_args );
        }

        //aawp_debug( $list_items, '$list_fetched' );

        return $list_items;
    }

    /**
     * Get single items
     *
     * @param $keys
     * @param array $args
     * @return mixed
     */
    public function get_items( $keys, $args = array() ) {

        if ( ! $this->Amazon_API )
            return false;

        //aawp_debug( $this->Amazon_API->get_configuration(), 'API Configuration' ); // TODO

        $items = $this->Amazon_API->getItems( $keys, $args );

        return $items;
    }

    /**
     * Get search items
     *
     * @param array $args
     * @return mixed
     */
    public function get_search_items( $args = array() ) {

        if ( ! $this->Amazon_API )
            return false;

        //aawp_debug( $this->Amazon_API->get_configuration(), 'API Configuration' ); // TODO

        $items = $this->Amazon_API->searchItems( $args );

        return $items;
    }
}