<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

global $AAWP_Fields;

/*
 * Settings
 */
if ( !class_exists( 'AAWP_Fields_Settings' ) ) {

    class AAWP_Fields_Settings extends AAWP_Functions {

        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // Call parent constructor first
            parent::__construct();

            $this->func_id = 'fields';

            // Hooks
            $this->hooks();
        }

        /**
         * Add settings functions
         */
        public function add_settings_functions_filter( $functions ) {

            $functions[] = 'fields';

            return $functions;
        }

        /*
         * Hooks
         */
        public function hooks() {

            // Settings functions
            add_filter( $this->settings_functions_filter, array( &$this, 'add_settings_functions_filter' ) );
            add_action( 'aawp_settings_functions_register', array( &$this, 'add_settings' ), 50 );
        }

        /**
         * Settings: Register section and fields
         */
        public function add_settings() {

            add_settings_section(
                'aawp_fields_section',
                false,
                false,
                'aawp_functions'
            );

            add_settings_field(
                'aawp_fields',
                __( 'Data Fields', 'aawp'),
                array( &$this, 'settings_fields_render' ),
                'aawp_functions',
                'aawp_fields_section',
                array('label_for' => 'aawp_fields_template')
            );
        }

        /**
         * Settings callbacks
         */
        public function settings_fields_render() {

            ?>
            <!-- Notices -->
            <h4 class="first"><?php _e( 'Notices', 'aawp' ); ?></h4>
            <?php aawp_admin_settings_functions_notices_render( $this->func_id ); ?>

            <?php
            do_action( 'aawp_settings_functions_fields_render' );
        }
    }

    if ( is_admin() ) {
        new AAWP_Fields_Settings();
    }
}

/*
 * Functions
 */
if ( !class_exists( 'AAWP_Fields_Functions' ) ) {

    class AAWP_Fields_Functions extends AAWP_Functions {

        public function __construct() {

            parent::__construct();

            $this->func_id = 'fields';
            $this->func_attr = $this->setup_func_attr( $this->func_id, array( 'return', 'value', 'image_class', 'raw', 'format' ) );

            // Hooks
            add_action( 'aawp_shortcode_handler', array( &$this, 'shortcode' ), 10, 1 );
            add_action( 'aawp_product_renewed', array( &$this, 'delete_transient' ), 10, 1 );
        }

        private function get_transient_key( $asin ) {
            return 'aawp_fields_' . md5( $asin );
        }

        function delete_transient( $product_id ) {

            $product = aawp_get_product( $product_id );

            if ( empty( $product['asin'] ) )
                return;

            $transient_key = $this->get_transient_key( $product['asin'] );
            delete_transient( $transient_key );
        }

        public function shortcode( $atts ) {

            // Exit if box is not set
            if ( empty( $atts['fields'] ) )
                return false;

            $asin = strip_tags( trim( $atts['fields'] ) );
            $value = ( isset( $atts['value'] ) ) ? strip_tags( trim( $atts['value'] ) ) : '';

            $this->get_value( $asin, $value, $atts, $echo = true );
        }

        public function get_value( $asin, $value, $atts = array(), $echo = false ) {

            $drop_items = false;

            if ( empty( $value ) )
                return __('Value missing.', 'aawp');

            // Prepare vars
            $this->setup_shortcode_vars( $this->intersect_atts( $atts, $this->func_attr ) );

            // Define values which don't need an ID
            $drop_values = array( 'button_detail' );

            if ( $asin == 'none' && in_array( $value, $drop_values ) ) {
                $items = 0;
                $drop_items = true;
            } else {

                // Use cache or fetch items from API
                $cache_key = $this->get_transient_key( $asin );

                $items = get_transient( $cache_key );

                if( $items === false ) {
                    $items = $this->get_items( $asin, $this->func_id );

                    if ( ! empty( $items[$asin] ) )
                        set_transient( $cache_key, $items, 60 * 60 );
                }
            }

            // Allowing items manipulation for extended functionality
            $items = $this->prepare_items( $items, $this->func_id, $this->atts );

            // Setup template handler
            $template_handler = new AAWP_Template_Handler();
            $template_handler->set_atts( $this->atts );
            $template_handler->set_type( $this->func_id );
            $template_handler->set_items( $items );
            $template_handler->set_request_keys( $asin );
            $template_handler->set_inline();

            // Build result
            $result = null;

            if ( ! empty( $items[$asin] ) || $drop_items ) {

                if ( ! $drop_items )
                    $template_handler->setup_item( 0, $items[$asin] );

                // Preparations
                $request = $this->clear_shortcode_value( $value ); // Convert spaces to underscores
                $format = ( isset( $this->atts['format'] ) ) ? explode( ',', $this->atts['format'] ) : array();
                $raw = ( ( isset( $atts['raw'] ) && 'true' == $atts['raw'] ) || in_array( 'raw', $format ) ) ? true : false;
                $linked = ( in_array( 'linked', $format ) ) ? true : false;

                switch ( $request ) {
                    case ( 'asin' ):
                        $result = $asin;
                        break;
                    case ( 'ean' ):
                        $result = $template_handler->get_product_ean();
                        break;
                    case ( 'isbn' ):
                        $result = $template_handler->get_product_isbn();
                        break;
                    case ( 'title' ):
                        $result = $template_handler->get_product_title();
                        break;
                    case ('url'):
                        $result = $template_handler->get_product_url();
                        break;
                    case ('link'):
                        $result = $template_handler->get_product_link();
                        $linked = false;
                        break;
                    case ('image'):
                        $result = $template_handler->get_product_image();
                        break;
                    case ('image_count'):
                        $result = $template_handler->get_product_image_count();
                        break;
                    case ('thumb'):
                        $result = $template_handler->get_product_thumb();
                        $linked = false;
                        break;
                    case ('description'):
                        $result = ( $raw ) ? $template_handler->get_product_description( $html = false ) : $template_handler->get_product_description();
                        $linked = false;
                        break;
                    case ('attributes'):
                        $result = $template_handler->get_product_attributes();
                        break;
                        /*
                    case ('editorial_review'):
                        $result = $template_handler->get_product_editorial_review();
                        $linked = false;
                        break;
                        */
                    case ('rating'):
                        $result = $template_handler->get_product_rating();
                        break;
                    case ('star_rating'):
                        $result = $template_handler->get_product_star_rating( array( 'force' => true ) );
                        $linked = false;
                        break;
                    case ('rating_count'): // Deprecated
                    case ('reviews'):
                        $result = $template_handler->get_product_reviews( ( $raw ) ? false : true );
                        break;
                    case ('price'):
                        $result = ( in_array( 'amount', $format ) ) ? $template_handler->get_product_price( 'display', 'amount' ) : $template_handler->get_product_price();
                        $result = ( empty( $result ) ) ? __('Price not available', 'aawp') : $result;
                        break;
                    case ('list_price'):
                        $result = ( in_array( 'amount', $format ) ) ? $template_handler->get_product_price( 'list', 'amount' ) : $template_handler->get_product_price('list');
                        //$result = ( empty( $result ) ) ? __('List price not available', 'aawp') : $result;
                        $result = ( ! empty( $result ) ) ? $result : null;
                        break;
                    /*
                    case ('used_price'): // Deprecated
                        $result = ( in_array( 'amount', $format ) ) ? $template_handler->get_product_price( 'used', 'amount' ) : $template_handler->get_product_price('used');
                        $result = ( empty( $result ) ) ? __( 'Used price not available', 'aawp' ) : $result;
                        break;
                    */
                    case ('old_price'):
                        $result = ( in_array( 'amount', $format ) ) ? $template_handler->get_product_price( 'old', 'amount' ) : $template_handler->get_product_price( 'old' );
                        $result = ( ! empty( $result ) && $template_handler->product_is_on_sale() ) ? $result : null;
                        break;
                    case ('amount_saved'): // Deprecated
                    case ('price_saving'):
                        $result = ( in_array( 'amount', $format ) ) ? $template_handler->get_product_price_saving( false ) : $template_handler->get_product_price_saving( true );
                        break;
                    case ('percentage_saved'): // Deprecated
                    case ('price_saving_percentage'):
                        $result = ( in_array( 'amount', $format ) ) ? $template_handler->get_product_price_saving_percentage( false ) : $template_handler->get_product_price_saving_percentage( true );
                        break;
                    case ('prime'):
                    case ('premium'):
                        $result = $template_handler->the_product_check_prime_logo( false );
                        $linked = false;
                        break;
                    case ('salesrank'):
                        $result = $template_handler->get_product_salesrank( $format = ( ! $raw ) ? 'formatted' : 'raw' );
                        break;
                    case ('button'):
                        $result = $template_handler->get_button( 'standard', false ); // always return button from tmp function
                        $linked = false;
                        break;
                    case ('button_detail'):
                        $result = $template_handler->get_button( 'detail', false ); // always return button from tmp function
                        $linked = false;
                        break;
                    case ('timestamp'):
                        $result = $template_handler->get_product_timestamp();
                        $linked = false;
                        break;
                    case ('date_updated'):
                        $result = $template_handler->get_product_date_updated();
                        $linked = false;
                        break;
                    case ('last_update'):
                        $result = $template_handler->get_product_last_update();
                        $linked = false;
                        break;
                    default:
                        $result = sprintf( __( 'Value "%1$s" is not supported.', 'aawp' ), $value );
                        $linked = false;
                        break;
                }

                // Maybe wrap into a link
                if ( ! empty( $result ) && $linked ) {
                    $link_class = ( ! empty( $this->atts['link_class'] ) ) ? esc_html( $this->atts['link_class'] ) : 'aawp-field-link';
                    $link_url = $template_handler->get_product_url();
                    $link_title = ( ! empty( $this->atts['link_title'] ) ) ? $template_handler->get_product_link_title() : $result;

                    $result = '<a class="' . $link_class . '" href="' . $link_url . '" title="' . $link_title . '" target="_blank" rel="nofollow noopener sponsored">' . $result . '</a>';
                }

                // Get container attributes for later action
                $container_attributes = $template_handler->the_product_container( false );

                if ( strpos( $result, '<a') !== false && ! in_array( $request, array( 'button_detail' ) ) ) {
                    $result = str_replace('<a', '<a ' . $container_attributes, $result );
                }

                $result = apply_filters( 'aawp_fields_result', $result, $request, $container_attributes );
            }

            //echo 'Echo output? '; var_dump( $echo ); echo '<br>';

            // Finally output
            if ( $echo ) {
                $template_handler->render_inline( $result );
            } else {
                return $result;
            }
        }
    }

    $AAWP_Fields = new AAWP_Fields_Functions();
}

/*
 * Template functions
 */
function aawp_get_field_value( $asin, $value, $args = array() ) {

    global $aawp_dependencies, $AAWP_Fields;

    if ( ! $aawp_dependencies )
        return false;

    // Action
    return $AAWP_Fields->get_value( $asin, $value, $args, $echo = false );
}
