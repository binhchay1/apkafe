<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'AAWP_Functions' ) ) {

    class AAWP_Functions
    {
        // Function variables
        public $settings_functions_filter = 'aawp_settings_functions';
        public $settings_tabs_filter = 'aawp_settings_tabs';
        public $options = array();

        // Identifier
        public $func_order;
        public $func_id;
        public $func_name;
        public $func_listener;
        public $func_attr;

        // Modules
        public $amazon;
        public $items;

        /** @var AAWP_Product $item */
        public $item;
        public $item_index;
        public $item_type;
        public $atts;

        // Attributes
        public $style;
        public $style_custom;
        public $layout;
        public $template;
        public $template_custom;
        public $template_default;

        public $layout_template;
        public $product_template;
        public $shortcode_template;

        public $timestamp_product;
        public $timestamp_list;

        public $request_keys;
        public $template_variables;

        // Standard vars
        public $shortcode;
        public $description_items;

        // Helper
        public $lang_de;
        public $euro_countries;

        /**
         * Construct the plugin object
         */
        public function __construct() {

            // Helper
            $this->lang_de = aawp_is_lang_de();
            $this->euro_countries = aawp_get_amazon_euro_countries();

            // Options
            $this->options['api'] = get_option( 'aawp_api', array() );
            $this->options['general'] = get_option( 'aawp_general', array() );
            $this->options['output'] = get_option( 'aawp_output', array() );
            $this->options['functions'] = get_option( 'aawp_functions', array() );
            $this->options['support'] = get_option( 'aawp_support', array() );

            // Standard vars
            $this->items_max = 10;

            $this->cache_duration = ( isset ( $this->options['general']['cache_duration'] ) ) ? $this->options['general']['cache_duration'] : '720';
            $this->affiliate_links = ( isset ( $this->options['general']['affiliate_links'] ) ) ? $this->options['general']['affiliate_links'] : 'standard';

            $this->title_length_unlimited = ( !empty ( $this->options['output']['title_length'] ) && !isset ( $this->options['output']['title_length_unlimited'] ) ) ? 0 : 1;
            $this->title_length = ( !empty ( $this->options['output']['title_length'] ) ) ? $this->options['output']['title_length'] : 100;
            $this->title_adding = ( isset ( $this->options['output']['title_adding'] ) ) ? $this->options['output']['title_adding'] : '';
            $this->image_link_title_adding = ( isset ( $this->options['output']['image_link_title_adding'] ) ) ? 1 : 0;

            $this->description_items = ( isset ( $this->options['output']['description_items'] ) && $this->options['output']['description_items'] != '' ) ? $this->options['output']['description_items'] : 5;
            $this->description_length_unlimited = ( !empty ( $this->options['output']['description_length'] ) && !isset ( $this->options['output']['description_length_unlimited'] ) ) ? 0 : 1;
            $this->description_length = ( !empty ( $this->options['output']['description_length'] ) ) ? $this->options['output']['description_length'] : 200;
            $this->description_html = ( !isset ( $this->options['output']['description_html'] ) || $this->options['output']['description_html'] == '1' ) ? 1 : 0;
            $this->teaser = ( isset ( $this->options['output']['teaser'] ) && 'hide' === $this->options['output']['teaser'] ) ? 0 : 1;
            $this->teaser_length_unlimited = ( !empty ( $this->options['output']['teaser_length'] ) && !isset ( $this->options['output']['teaser_length_unlimited'] ) ) ? 0 : 1;
            $this->teaser_length = ( !empty ( $this->options['output']['teaser_length'] ) ) ? $this->options['output']['teaser_length'] : 200;
            $this->pricing_advertised_price = ( !empty ( $this->options['output']['pricing_advertised_price'] ) ) ? $this->options['output']['pricing_advertised_price'] : 'standard';
            $this->pricing_advertised_price_hide_unavailability = ( isset ( $this->options['output']['pricing_advertised_price_hide_unavailability'] ) && $this->options['output']['pricing_advertised_price_hide_unavailability'] == '1' ) ? 1 : 0;
            $this->pricing_reduction = ( !empty ( $this->options['output']['pricing_reduction'] ) ) ? $this->options['output']['pricing_reduction'] : 'amount';
            $this->check_prime = ( isset ( $this->options['output']['check_prime'] ) ) ? $this->options['output']['check_prime'] : 'linked';
            $this->star_rating_size = ( isset ( $this->options['output']['star_rating_size'] ) ) ? $this->options['output']['star_rating_size'] : '0';
            $this->star_rating_link = ( isset ( $this->options['output']['star_rating_link'] ) ) ? $this->options['output']['star_rating_link'] : 'reviews';
            $this->show_reviews = ( isset ( $this->options['output']['show_reviews'] ) ) ? $this->options['output']['show_reviews'] : '0';
            $this->button_style = ( isset ( $this->options['output']['button_style'] ) ) ? $this->options['output']['button_style'] : 'amazon';
            $this->button_icon = ( isset ( $this->options['output']['button_icon'] ) ) ? $this->options['output']['button_icon'] : 'black';
            $this->button_text = ( !empty ( $this->options['output']['button_text'] ) ) ? $this->options['output']['button_text'] : '';

            // Prepare API
            $this->api_country = ( !empty( $this->options['api']['country'] ) ) ? $this->options['api']['country'] : null;
            $this->api_key = ( !empty( $this->options['api']['key'] ) ) ? $this->options['api']['key'] : '';
            $this->api_secret_key = ( !empty( $this->options['api']['secret'] ) ) ? $this->options['api']['secret'] : '';
            $this->api_associate_tag = ( !empty( $this->options['api']['associate_tag'] ) ) ? $this->options['api']['associate_tag'] : '';

            // Extras
            $this->country = apply_filters( 'aawp_func_country', $this->api_country );
        }

        public function set_atts( $atts ) {
            $this->atts = $atts;
        }

        public function set_type( $type ) {
            $this->type = $type;
            $this->item_type = $type;
        }

        public function set_items( $items ) {
            $this->items = $items;
        }

        public function set_item ( $item ) {
            $this->item = $item;
        }

        public function set_item_index( $item_index ) {
            $this->item_index = $item_index;
        }

        public function set_timestamp_list( $timestamp ) {
            $this->timestamp_list = $timestamp;
        }

        public function set_request_keys ( $keys ) {
            $this->request_keys = $keys;
        }

        public function set_template_variables( $variables = array() ) {

            $custom_variables = array();

            if ( is_array( $this->atts ) && sizeof( $this->atts ) > 0 ) {
                foreach ( $this->atts as $attr => $value ) {

                    if ( substr( $attr, 0, 4 ) === 'tpl_' ) {

                        // Key
                        $variable_key = substr( $attr, 4 );

                        // Either create an array for multiple values or use the single string
                        if ( strpos( $value, ',' ) !== false ) {
                            $variable_value = explode(',', $value);
                        } else {
                            $variable_value = esc_html( $value );
                        }

                        if ( ! empty( $variable_key ) )
                            $custom_variables[$variable_key] = $variable_value;
                    }
                }
            }

            $variables = array_merge( $variables, $custom_variables );

            $this->template_variables = $variables;
        }

        public function add_template_variables( $variables = array() ) {

            if ( ! is_array( $variables ) )
                return;

            $template_variables = $this->template_variables;

            $template_variables = array_merge( $template_variables, $variables );

            $this->template_variables = $template_variables;
        }

        /*
         * Common functions
         */
        public function get_api_connection( $api_country = null, $api_associate_tag = null ) {

            if ( empty( $api_country ) )
                $api_country = $this->api_country;

            if ( empty( $api_associate_tag ) )
                $api_associate_tag = AAWP_PLACEHOLDER_TRACKING_ID; //$this->api_associate_tag;

            // Setup AAWP
            $amazon = new AAWP_API();
            //$amazon->set_debug_mode();
            $amazon->set_credentials($api_country, $this->api_key, $this->api_secret_key, $api_associate_tag, false);
            $amazon->set_verified(true);

            return $amazon;
        }

        public function get_api_args( $args = array() ) {

            // Reviews
            /* // Deprecated
            if ( ! isset( $args['crawl_reviews'] ) && aawp_is_crawling_reviews_activated() ) {
                $args['crawl_reviews'] = 1;
            }
            */

            // Hooking the arguments
            //$args = apply_filters( 'aawp_api_args', $args, $this->atts );

            //aawp_debug( $args, 'functions->get_api_args');

            return $args;
        }

        public function setup_shortcode_vars($atts, $content = null) {

            if ( ! empty( $content ) ) {
                $atts['description'] = do_shortcode( $content );
            }

            $this->atts = $atts;

            global $aawp_shortcode_atts;

            $aawp_shortcode_atts = $atts;
        }

        public function set_template ( $template ) {
            $this->template = $template;
        }

        /**
         * Get items (single asins or list) by cache or api
         *
         * @param $keys
         * @param $type
         * @param bool $is_list
         * @param array $args
         *
         * @return array|bool/null
         */
        public function get_items( $keys, $type, $is_list = false, $args = array() ) {

            // Decode special chars
            $keys = ( is_string( $keys ) ) ? urldecode( $keys ) : $keys;

            // Defaults
            $items = array();

            // Handle lists
            if ( $is_list ) {

                $args['type'] = $type;

                if ( is_numeric( $keys ) ) {
                    $args['browse_node_id'] = floatval( $keys );
                } else {
                    $args['keywords'] = $this->prepare_list_keywords( $keys );
                }

                $args['items'] = ( isset ( $args['items'] ) && is_numeric( $args['items'] ) ) ? apply_filters( 'aawp_list_query_items', $args['items'], $this->atts ) : 10;

                if ( $args['items'] > 20 ) // TODO: Increasing the max limitation time by time again.
                    $args['items'] = 20;

                // Collect list items which are already stored in database
                $list_item_ids = $this->get_list_items_from_cache( $args );

                if ( ! empty( $list_item_ids ) && is_array( $list_item_ids ) && sizeof( $list_item_ids ) > 0 ) {

                    //aawp_debug( $list_item_ids, '$list_item_ids = $this->get_list_items_from_cache' );

                    /*
                    $list_items_max = intval( $args['query_items'] ); // TODO: Fallback

                    // Maybe remove unneeded items in case we received a larger list from the database
                    if ( sizeof( $list_item_ids ) > $list_items_max )
                        $list_item_ids = array_slice( $list_item_ids, 0, $list_items_max );
                    */

                } else {

                    // Get missing list item ids from API
                    $list_item_ids = $this->get_list_items_from_api( $args );

                    //aawp_debug( $list_item_ids, '$list_item_ids = $this->get_list_items_from_api' );

                    if ( ! is_array( $list_item_ids ) )
                        return $list_item_ids;

                    $this->timestamp_list = time();
                }

                $asins = $list_item_ids;

            // Handle single asins
            } else {
                $asins = ( is_string( $keys ) ) ? explode( ',', str_replace( ' ', '', $keys ) ) : $keys;
            }

            if ( empty ( $asins ) )
                return null;

            //aawp_debug( $asins, '$asins' );

            // Collect items which are already available
            $products_cached = $this->get_products_from_cache( $asins );

            //aawp_debug( $products_cached, '$products_cached' );

            if ( is_array( $products_cached ) && sizeof( $products_cached ) > 0 )
                $items = $products_cached;

            //aawp_debug( $items, '$items after cached' );

            // Collect items which must be fetched from API
            $products_missing = array();

            foreach ( $asins as $asin ) {
                if ( ! isset( $products_cached[$asin] ) )
                    $products_missing[] = $asin;
            }

            //aawp_debug( $products_missing, '$products_missing' );

            // Get missing items from API
            if ( is_array( $products_missing ) && sizeof( $products_missing ) > 0 ) {

                $products_fetched = $this->get_products_from_api( $products_missing );

                //aawp_debug( $products_fetched, '$products_fetched' );

                if ( is_string( $products_fetched ) )
                    return $products_fetched;

                if ( is_array( $products_fetched ) && sizeof( $products_fetched ) > 0 )
                    $items = $items + $products_fetched;

                //aawp_debug( $items, '$items after fetched' );
            }

            // Keep original order
            if ( is_array( $asins ) && sizeof( $asins ) > 1 && is_array( $items ) && sizeof( $items ) > 1 ) {

                $items_reordered = array();

                foreach ( $asins as $asin ) {

                    if ( ! empty( $items[$asin] ) )
                        $items_reordered[] = $items[$asin];
                }

                $items = $items_reordered;
            }

            //aawp_debug( $items, 'final $items' );

            //aawp_debug_log( 'get_items >> size: ' .sizeof( $items ) );

            return $items;
        }

        /**
         * Get list from cache
         *
         * @param array $args
         * @return mixed
         */
        public function get_list_items_from_cache( $args = array() ) {

            //aawp_debug( $args, 'get_list_items_from_cache >> $args' );

            if ( empty( $args['type'] ) )
                return null;

            // Fallback: Convert deprecated args
            if ( ! empty( $args['items'] ) && is_numeric( $args['items'] ) ) {
                $args['items_count'] = $args['items'];
                unset( $args['items'] );
            }

            //aawp_debug( $args, 'get_list_items_from_cache >> $args' );

            /*
             * TODO: Fallbacks?
             *
             * 'type' => $args['type'],
             * 'browse_node_id' => 0,
             * 'keywords' => '',
             * 'browse_node_search' => 1,
             * 'items' => 10
             */

            $default_list_args = array(
                'status' => 'active',
                'list_key' => '', // TODO?
                'type' => '',
                'keywords' => '',
                'browse_node_id' =>  '',
                'browse_node_search' => '',
                'items_count' => 10,
            );

            $list_args = wp_parse_args( $args, $default_list_args );

            //aawp_debug( $list_args, 'get_list_items_from_cache >> $list_args' );

            $list = aawp_get_list_by_args( $list_args );

            //aawp_debug( $list, 'get_list_items_from_cache >>> $list' );

            if ( empty( $list['product_asins'] ) || ! is_array( $list['product_asins'] ) || ! isset( $list['date_updated'] ) )
                return null;

            $this->timestamp_list = strtotime( $list['date_updated'] );

            return $list['product_asins'];
        }

        /**
         * Gert list from API
         *
         * @param array $args
         * @return bool|null
         */
        public function get_list_items_from_api( $args = array() ) {

            if ( ! aawp_check_api_connection() )
                return null;

            if ( empty( $args['type'] ) )
                return null;

            // Fallback: Convert deprecated args
            if ( ! empty( $args['items'] ) && is_numeric( $args['items'] ) ) {
                $args['items_count'] = $args['items'];
                unset( $args['items'] );
            }

            /* TODO: Fallback?
            $default_list_args = array(
                'type' => $args['type'],
                'browse_node_id' => 0,
                'keywords' => '',
                'browse_node_search' => 1,
                'items' => 10
            );
            */

            $default_list_args = array(
                'status' => '',
                'type' => '',
                'keywords' => '',
                'browse_node_id' =>  '',
                'browse_node_search' => '',
                'items_count' => 10,
            );

            $list_args = wp_parse_args( $args, $default_list_args );

            //aawp_debug( $list_args, 'get_list_items_from_api >> $list_args' );

            $list_fetched = aawp()->api->get_list( $this->get_api_args( $list_args ) );

            //aawp_debug( $list_fetched, 'get_list_items_from_api >> aawp()->api->get_list >> result' );

            if ( ! is_array( $list_fetched ) )
                return null;

            $list_args = aawp_attach_product_asins_to_list_args( $list_args, $list_fetched );

            //$list_data['items'] = $list_args['query_items']; // TODO: Deprecated or fallback?

            $list_id = aawp_create_list( $list_args );
            //echo 'Created list #' . $list_id . ' after fetching from API.<br>';

            if ( $list_id ) {

                if ( is_array( $list_fetched ) && sizeof( $list_fetched ) >0 ) {
                    foreach ( $list_fetched as $product ) {
                        $product_id = aawp_create_product( $product );

                        //if ( $product_id )
                          //  echo 'get_list_items_from_api() >> Product ID ' . $product_id . ' created.<br>';
                    }
                }
            }

            return ( ! empty ( $list_id ) && ! empty ( $list_args['product_asins'] ) ) ? $list_args['product_asins'] : null;
        }

        /**
         * Get products from cache
         *
         * @param array $asins
         * @return array|null
         */
        public function get_products_from_cache( $asins = array() ) {

            $args = array(
                'asin' => $asins,
                'number' => sizeof( $asins )
            );

            $products = aawp_get_products( $args );

            if ( ! is_array( $products ) || sizeof( $products ) == 0 )
                return null;

            $products_cached = array();

            foreach ( $products as $product ) {

                if ( ! isset( $product['asin'] ) )
                    continue;

                $products_cached[$product['asin']] = $product;
            }

            // TODO: aawp_add_renew_cache_queue( $product['id'], 'products' );

            // Validate products
            //foreach
            //$items = $this->validate_products( $items );

            return $products_cached;
        }

        /**
         * Get products from API
         *
         * @param array $asins
         * @return array|null
         */
        public function get_products_from_api( $asins = array() ) {

            if ( ! aawp_check_api_connection() )
                return null;

            $products_fetched = aawp()->api->get_products( $asins, $this->get_api_args() );

            //aawp_debug( $products_fetched, '$products_fetched' );

            if ( ! is_array( $products_fetched ) )
                return $products_fetched;

            $products = array();
            $products_created = array();

            foreach ( $products_fetched as $product ) {
                //echo 'Product ASIN ' . $product['asin'] . ' fetched from API.<br>';
                $product_id = aawp_create_product( $product );

                /*
                echo 'aawp_create_product > $product_id: ';
                var_dump( $product_id );
                echo '<br>';
                */

                if ( ! empty( $product_id ) )
                    $products_created[] = $product_id;
            }

            if ( sizeof( $products_created ) > 0 ) {

                $products_from_database = aawp_get_products( array( 'id' => $products_created ) );

                if ( is_array( $products_from_database ) && sizeof( $products_from_database ) > 0 ) {

                    foreach ( $products_from_database as $product ) {

                        if ( ! empty( $product['asin'] ) )
                            $products[$product['asin']] = $product;
                    }
                }
            }

            return $products;
        }

        /**
         * Pare list keywords (sanitizing, placeholders, lowercase)
         *
         * @param $keywords
         * @return mixed|string
         */
        private function prepare_list_keywords( $keywords ) {

            //aawp_debug_log( 'prepare_list_keywords >> before: ' . $keywords );

            // Sanitize input
            $keywords = sanitize_text_field( $keywords );

            // Placeholder: Post/page title
            if ( strpos( $keywords, '%post_title%' ) !== false || strpos( $keywords, '%page_title%' ) !== false ) {
                $post_title = get_the_title();
                $keywords = str_replace( array( '%post_title%', '%page_title%' ), $post_title, $keywords );
            }

            // Placeholder: Post category
            if ( strpos( $keywords, '%post_category%' ) !== false ) {

                $post_categories = get_the_category();

                if ( isset( $post_categories[0]->name ) ) {
                    $keywords = str_replace('%post_category%', $post_categories[0]->name, $keywords );
                }
            }

            // Placeholder: Yoast Focus Keyword
            if ( strpos( $keywords, '%yoast_focus_keyword%' ) !== false ) {
                $yoast_focus_keyword = get_post_meta( get_the_ID(), '_yoast_wpseo_focuskw', true );

                if ( ! empty( $yoast_focus_keyword ) )
                    $keywords = str_replace( '%yoast_focus_keyword%', $yoast_focus_keyword, $keywords );
            }

            // Cleanup keywords
            $keywords = strip_tags( $keywords ); // Strip HTML and PHP tags from a string
            $keywords = preg_replace("/&#?[a-z0-9]+;/i",'', $keywords ); // Remove decoded HTML entities
            $keywords = str_replace( '  ', ' ', $keywords ); // Remove double spaces

            // Lowercase keywords
            $keywords = strtolower( $keywords );

            //aawp_debug_log( 'prepare_list_keywords >> after: ' . $keywords );

            // Finished
            return $keywords;
        }

        /**
         * Prepare items for manipulation
         */
        public function prepare_items( $items, $type, $atts ) {

            $this->item_type = $type;

            if ( ! empty( $items ) ) {
                $this->item_index = apply_filters( 'aawp_items_start_index', 0, $type, $atts );
                //echo 'prepare_items() >> $items size: ' . sizeof( $items ) . '<br>';
                $items = apply_filters( 'aawp_items', $items, $type, $atts );
            }

            //echo 'prepare_items() >> $items size: ' . sizeof( $items ) . '<br>';

            return $items;
        }

        /*
         * Template functions
         */
        public function get_product_id() {
            return $this->item->get_asin();
        }

        public function get_assets_url() {
            return AAWP_PLUGIN_URL . 'assets';
        }

        public function show_star_rating() {

            if ( isset( $this->atts['star_rating'] ) && 'none' === $this->atts['star_rating'] )
                return false;

            return ( $this->star_rating_size == '0' ) ? false : true;
        }

        public function show_product_numbering() {

            if ( isset( $this->atts['numbering'] ) && 'true' == $this->atts['numbering'] )
                return true;

            if ( isset( $this->atts['numbering'] ) && 'none' == $this->atts['numbering'] )
                return false;

            $numbering = false;

            $numbering = apply_filters( 'aawp_show_product_numbering', $numbering, $this->item_type, $this->atts );

            return $numbering;
        }

        public function get_product_numbering() {
            return apply_filters( 'aawp_product_numbering', $this->item_index, $this->item_type, $this->atts );
        }

        public function get_product_numbering_label() { // Right now only being used by table template

            $label = apply_filters( 'aawp_product_numbering_label', __( 'No.', 'aawp' ), $this->item_type, $this->atts );

            $label = $this->get_replace_numbering_label_placeholders( $label );

            return $label;
        }

        public function get_replace_numbering_label_placeholders( $label ) {
	        $label = str_replace( '%NUMBER%', '', $label );
	        $label = trim( $label );

            return $label;
        }

        public function show_prime_logo() {
            return ( 'none' != $this->check_prime ) ? true : false;
        }

        public function get_assets_cart_icon_url($color) {
            return $this->get_assets_url() . '/img/icon-cart-' . $color . '.svg';
        }

        /**
         *  Display notice for currently not supported browse node ids
         */
        public function display_shortcode_browse_node_id_notice_after_apiv5() {

            if ( ! aawp_is_user_editor() )
                return;

            echo '<p style="color: red;">';
            echo sprintf( wp_kses( __( 'Due to recent adjustments of the new Amazon API, right now it is not possible to pass a Browse Node ID. Please use a search term like "Harry Potter" instead (<a href="%s" target="_blank" rel="nofollow">Click here for more details</a>).', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => '_blank', 'rel' => 'nofollow' ) ) ), esc_url( aawp_get_page_url( 'docs:amazon_apiv5' ) ) );
            echo '</p>';
        }

        /**
         * Get button text
         *
         * @param string $type
         *
         * @return string
         */
        public function get_button_text($type = 'standard') {

            $button_text = ( !empty( $this->button_text ) ) ? $this->button_text : __('Buy on Amazon', 'aawp');

            return apply_filters( 'aawp_func_button_text', $button_text, $type );
        }

        /**
         * Get button style
         *
         * @param string $type
         *
         * @return string
         */
        public function get_button_style( $type = 'standard') {

            $classes = $this->button_style;

            return apply_filters( 'aawp_func_button_style', $classes, $type );
        }

        /**
         * Get button icon
         *
         * @return null|string
         */
        public function get_button_icon() {

            if ( $this->button_icon == '0') {
                return null;
            }

            return $this->button_icon;
        }

        public function get_button_icon_url() {
            return $this->get_assets_cart_icon_url($this->button_icon);
        }

        public function get_sale_text() {
            return __('Sale', 'aawp');
        }

        public function get_inline_info() {

            if ( isset ( $this->options['general']['inline_info'] ) && $this->options['general']['inline_info'] != '0' )  {
                return true;
            }

            return false;
        }

        public function get_inline_info_text() {

            $inline_info_text = ( !empty ( $this->options['general']['inline_info_text'] ) ) ? $this->options['general']['inline_info_text'] : null;

            if ( !$inline_info_text )
                return null;

            if ( strpos( $inline_info_text ,'%last_update%') !== false ) {

                $timestamp = $this->item->get_timestamp();

                $last_update = $this->get_formatted_last_update( $timestamp );

                if ( $last_update ) {
                    $inline_info_text = $this->replace_last_update_placeholder($inline_info_text, $last_update);
                }
            }

            return $inline_info_text;
        }

        /**
         * Get ribbon text
         *
         * @param $default
         *
         * @return null|string
         */
        function get_ribbon_text( $default ) {

            // Hide ribbon
            if ( isset( $this->atts['ribbon'] ) && 'none' === $this->atts['ribbon'] )
                return '';

            // Overwrite text via shortcode
            if ( isset( $this->atts['ribbon_text'] ) )
                return esc_html( $this->atts['ribbon_text'] );

            // Get text via settings
            $ribbon_text = ( isset ( $this->options['functions'][$this->func_id . '_ribbon_text'] ) ) ? $this->options['functions'][$this->func_id . '_ribbon_text'] : null;

            // Only use default value when text is null, in order to "hide" it via settings when leaving empty
            if ( null === $ribbon_text )
                $ribbon_text = $default;

            return $ribbon_text;
        }

        /*
         * Numbers
         */
        public function format_number( $number, $use_decimals = true ) {

            $country = $this->api_country;

            $decimals = ( $use_decimals ) ? 2 : 0;
            $decimals_sep = '.';
            $thousands_sep = ',';

            if ( 'de' === $country || 'es' === $country || 'it' === $country ) {
                $decimals_sep = ',';
                $thousands_sep = '.';
            } elseif ( 'fr' === $country ) {
                $decimals_sep = ',';
                $thousands_sep = ' ';
            }

            $number = number_format($number, $decimals, $decimals_sep, $thousands_sep);

            return $number;
        }

        public function get_formatted_last_update( $timestamp ) {
            return aawp_format_last_update( $timestamp );
        }

        private function get_translations() {

            $translations = array(
                'pages' => array('de' => 'Seiten', 'fr' => 'pages', 'es' => 'páginas', 'it' => 'pagine'),
                'Edition no.' => array('de' => 'Auflage Nr.', 'fr' => 'Édition no.', 'es' => 'Edición no.', 'it' => 'Edizione n.'),
                'Release date' => array('de' => 'Ver&ouml;ffentlichungsdatum', 'fr' => 'Date de sortie', 'es' => 'Fecha de lanzamiento', 'it' => 'Data di rilascio'),
                'Running time' => array('de' => 'Laufzeit', 'fr' => 'temps de fonctionnement', 'es' => 'tiempo de ejecución', 'it' => 'tempo di esecuzione'),
            );

            /*
            foreach ($translations as $key => $values) {
                if (isset($values[$country])) {
                    $result[$key] = $values[$country];
                }
            }
            */

            return $translations;
        }

        private function translate( $string ) {

            $translations = $this->get_translations();

            if ( isset( $translations[$string][$this->country] ) && $translations[$string][$this->country] != '' ) {
                return $translations[$string][$this->country];
            }

            return $string;
        }

        /**************************************************
         * Settings renderer
         **************************************************/
        public function do_settings_render_template($templates, $prefix ) {

            $templates = apply_filters( 'aawp_func_templates', $templates, $this->func_id );

            ?>
            <p>
                <select id="aawp_<?php echo $prefix; ?>_template" name="aawp_functions[<?php echo $prefix; ?>_template]" data-aawp-custom-template-selector="<?php echo $this->func_id; ?>">
                    <?php foreach ( $templates as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $this->template, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
                <?php $this->do_admin_note('shortcode'); ?>
            </p>

            <?php do_action( 'aawp_func_settings_render_template', $this->func_id, $this->template, $this->template_custom, $prefix ); ?>

            <?php
        }

        public function do_settings_render_style($styles, $prefix ) {

            $styles = apply_filters( 'aawp_func_styles', $styles, $this->func_id );

            ?>
            <p>
                <select id="aawp_<?php echo $prefix; ?>_style" name="aawp_functions[<?php echo $prefix; ?>_style]">
                    <?php foreach ( $styles as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $this->style, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
                <?php $this->do_admin_note('shortcode'); ?>
            </p>

            <?php
        }

        public function do_settings_render_ribbon_text( $prefix, $default ) {

            $ribbon_text = ( isset ( $this->options['functions'][$prefix . '_ribbon_text'] ) ) ? $this->options['functions'][$prefix . '_ribbon_text'] : $default;

            ?>
            <p>
                <input type="text" id="aawp_<?php echo $prefix; ?>_ribbon_text" name="aawp_functions[<?php echo $prefix; ?>_ribbon_text]" value="<?php echo $ribbon_text; ?>" />
                <?php $this->do_admin_note('shortcode'); ?>
            </p>
            <p>
                <?php aawp_admin_display_placeholders_note( array( 'number' ) ); ?>
            </p>

            <?php do_action( 'aawp_func_settings_render_template', $this->func_id, $this->template, $this->template_custom, $prefix ); ?>

            <?php
        }

        /*
         * Settings tooltips and notes
         */
        public function do_admin_note($type) {

            switch($type) {
                case ('shortcode'):
                    $icon = 'admin-settings'; break;
                // Default
                default:
                    $icon = 'admin-settings'; break;
            }

            echo '<span class="aawp-admin-note-icon dashicons dashicons-' . $icon . '"></span>';;
        }

        public function do_admin_tooltip($text) {

            switch($text) {
                // Check if preset is available
                case ('shortcode_overwrite'):
                    $tooltip = __('This value can be overwritten individually for each shortcode. Please take a look into the documentation.', 'aawp');  break;
                // Default
                default:
                    $tooltip = $text; break;
            }

            ?>
            <span class="dashicons dashicons-info aawp-tooltip pulse" data-aawp-tooltip="true">
                <span class="aawp-tooltip-content">
                    <?php echo $tooltip; ?>
                </span>
            </span>
            <?php
        }

        /*
         * Helper
         */
        function replace_last_update_placeholder( $string, $last_update ) {
            return aawp_replace_last_update_placeholder( $string, $last_update );
        }

        private function sanitize($string) {

            $string = strtolower($string);
            $string = preg_replace('/[^a-z0-9 -]+/', '', $string);
            $string = str_replace(' ', '-', $string);

            return trim($string, '-');
        }

        public function setup_func_attr( $type, $custom = array() ) {

            $supported = array(
                'origin', 'widget'
            );

            // Templating & Styling
            if ( $type == 'box' || $type == 'bestseller' || $type == 'new_releases' )
                array_push( $supported, 'template', 'type', 'button', 'star_rating', 'reviews', 'price', 'class', 'description', 'teaser', 'numbering', 'sale_ribbon', 'sale_ribbon_text' );

            // All shortcodes
            array_push( $supported, 'title_length' );

            // General
            if ( $type == 'box' || $type == 'bestseller' || $type == 'new_releases' || $type == 'fields' )
                array_push( $supported, 'button_text', 'button_class', 'button_icon', 'button_style', 'button_detail', 'button_detail_text', 'button_detail_target', 'button_detail_title', 'button_detail_rel', 'description_items', 'description_length', 'description_text', 'image_size', 'star_rating_size', 'star_rating_link', 'float' );

            // Single outputs
            if ( $type == 'box' || $type == 'fields' )
                array_push( $supported, 'image', 'image_alt', 'image_link', 'image_title', 'link_overwrite', 'link_title', 'title', 'rating' );

            // Lists
            if ( $type == 'bestseller' || $type == 'new_releases' )
                array_push( $supported, 'count', 'items', 'max', 'browsenode', 'ribbon', 'ribbon_text' );

            // Fields
            if ( $type == 'fields' )
                array_push( $supported, 'image_class', 'image_width', 'image_height', 'image_align' );

            // Links & linked fields
            if ( $type == 'link' || $type == 'fields' )
                array_push( $supported, 'link_title', 'link_class' );

            $supported = apply_filters( 'aawp_func_supported_attributes', $supported, $type );

            // Merge supported and custom attributes
            $supported = array_merge ( $supported, $custom );

            // Return
            return $supported;
        }

        public function intersect_atts($given, $supported = array()) {

            if (!is_array($given) || !is_array($supported) || sizeof($supported) == 0)
                return $given;

            foreach ($given as $attr => $value) {

                if ( substr( $attr, 0, 4 ) === 'tpl_' )
                    continue;

                if (!in_array($attr, $supported) || $value == 'false' || $value == 'null') {
                    unset($given[$attr]);
                }
            }

            return $given;
        }

        public function update_atts ( $updates = array() ) {

            if ( sizeof( $updates ) > 0 ) {
                $atts = $this->atts;

                foreach ( $updates as $key => $value ) {
                    $atts[$key] = $value;
                }

                $this->atts = $atts;

                global $aawp_shortcode_atts;

                $aawp_shortcode_atts = $atts;
            }
        }

        public function clear_shortcode_value($value) {
            return str_replace ( ' ' , '_' , $value );
        }

        public function truncate( $string, $limit = 200, $type = false, $pad = '...' ) {

            //$re_add_title_adding = ( ! empty ( $this->title_adding ) && $string === ( $this->item['title'] . $this->title_adding ) ) ? true : false;

            $pad = apply_filters( 'aawp_func_truncate_pad', $pad, $type );

            if ( strlen( $string ) > $limit ) {
                $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $limit + 1)) . $pad;
            }

            //if ( $re_add_title_adding )
                //$string .= $this->title_adding;

            return $string;
        }

        public function display_api_error_message($ids, $string) {
            echo '<span style="color: red">' . $ids . ': ' . $string . '</span>';
        }

        public function is_extended_version() {
            return ( class_exists( 'AAWP_Extended_Functions' ) ) ? true : false;
        }

        public function display_extended_version_info() {
            echo '<em>';
            printf( wp_kses( __( 'This feature is only available in the <a href="%s" target="_blank">extended version</a>.', 'aawp' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( '?page=aawp&tab=info' ) );
            echo '</em>';
        }

        public function debug( $arg ) {

            return;

            echo '<pre>';
            print_r($arg);
            echo '</pre>';
        }

        /**
         * Cleanup HTML attributes
         *
         * Prevent breaking HTML markup (e.g. title/alt attributes)
         *
         * @param $string
         * @return mixed
         */
        public function cleanup_html_attributes( $string ) {

            // Remove html tags completely
            $string = wp_strip_all_tags( $string );

            // Replace double quotes with single quotes
            $string = str_replace( '"', "'", $string );

            // Remove starting/ending tags
            $string = str_replace( array( '>', '<' ), '', $string );

            return $string;
        }
    }

    function aawp_do_admin_note($type) {

        switch($type) {
            case ('shortcode'):
                $icon = 'admin-settings'; break;
            // Default
            default:
                $icon = 'admin-settings'; break;
        }

        echo '<span class="aawp-admin-note-icon dashicons dashicons-' . $icon . '"></span>';
    }
}