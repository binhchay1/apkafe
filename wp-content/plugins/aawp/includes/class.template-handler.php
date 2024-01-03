<?php
/**
 * Template Handler
 *
 * @package     AAWP
 * @since       3.4.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'AAWP_Template_Handler') ) {

    class AAWP_Template_Handler extends AAWP_Template_Functions {

        private $inline = false;
        private $is_widget;
        private $timestamp_template = 0;

        /**
         * Constructor
         */
        public function __construct() {

            // Call parent constructor first
            parent::__construct();
        }

        public function set_inline() {
            $this->inline = true;
        }

        public function render() {

            // Items available
            if ( is_array( $this->items ) && sizeof( $this->items ) > 0 ) {

                $this->add_action_before_template();

                $this->render_template_advanced();

                $this->add_action_after_template();

            } else {

                // Error message
                if ( is_string( $this->items ) ) {
                    $this->display_notice_response_error();

                // No products found
                } else {
                    $this->display_notice_products_not_found();
                }
            }
        }

        public function render_inline( $string ) {

            // Items available
            if ( is_array( $this->items ) && sizeof( $this->items ) == 1 ) {

                if ( ! empty( $string ) )
                    echo $string;

            } else {

                // Error message
                if ( is_string( $this->items ) ) {
                    $this->display_notice_response_error();

                    // No products found
                } else {
                    $this->display_notice_products_not_found();
                }
            }
        }

        /**
         * Add action before template render
         */
        private function add_action_before_template() {
            do_action( 'aawp_before_template' );
            do_action( 'aawp_' . $this->type . '_before_template' );
        }

        /**
         * Add action after template render
         */
        private function add_action_after_template() {

            if ( $this->timestamp_template ) {
                $timestamp = $this->timestamp_template;
            } else {
                $timestamp = ( 'bestseller' === $this->type || 'new_releases' === $this->type ) ? $this->timestamp_list : $this->timestamp_product;
            }

            $args = array(
                'item' => $this->item,
                'timestamp' => $timestamp,
                'is_widget' => $this->is_widget
            );

            do_action( 'aawp_after_template', $args );
            do_action( 'aawp_' . $this->type . '_after_template', $args );
        }

        /**
         * Render template main function
         */
        private function render_template_advanced() {

            // Defaults
            $layout_template = null;
            $product_template = null;
            $shortcode_template = null;
            $template = null;
            $template_origin = ( isset ( $this->atts['origin'] ) && 'widget' === $this->atts['origin'] ) ? 'widget' : 'content';
            $this->is_widget = ( 'widget' === $template_origin ) ? true : false;

            // Template set manually?
            if ( ! empty( $this->atts['template'] ) ) {
                $template = sanitize_text_field( $this->atts['template'] );
                $shortcode_template = $template;
                // Custom Template set by settings
            } elseif ( ! $this->is_widget && ! empty( $this->options['functions'][$this->type . '_template_custom'] ) && 'custom' === $this->options['functions'][ $this->type . '_template' ] ) {
                $template = $this->options['functions'][ $this->type . '_template_custom' ];
            } elseif ( ! $this->is_widget && ! empty( $this->options['functions'][$this->type . '_template'] ) ) {
                $template = $this->options['functions'][ $this->type . '_template' ];
            }

            // Set product template
            $product_template_validation = false;

            if ( ! empty ( $template ) ) {

                // Check if template is exists
                if ( file_exists( trailingslashit( AAWP_PLUGIN_DIR . '/templates/products/' ) . $template . '.php' ) ) {
                    $product_template_validation = true;
                }

                $product_template_validation = apply_filters( 'aawp_product_template_validation', $product_template_validation, $template, $this->atts );

                if ( $product_template_validation ) {
                    $product_template = $template;
                }
            }

            // Check if template is layout
            $layout_template_validation = false;

            if ( ! empty( $template ) && ! $product_template_validation ) {

                // Check if template is layout
                if ( file_exists( trailingslashit( AAWP_PLUGIN_DIR . '/templates/' ) . $template . '.php' ) ) {
                    $layout_template_validation = true;
                }

                $layout_template_validation = apply_filters( 'aawp_layout_template_validation', $layout_template_validation, $template, $this->atts );
            }

            // Set layout template
            if ( $layout_template_validation ) {
                $layout_template = $template;
            }

            // Cleanup deprecated templates
            if ( ! empty( $template ) && ! $layout_template_validation )
                $layout_template = $this->replace_deprecated_layout_template( $layout_template, $template, $template_origin );

            // Allow layout template manipulation
            $layout_template = apply_filters( 'aawp_layout_template', $layout_template, $layout_template_validation, $this->atts );

            //if ( aawp_is_amp_endpoint() )
              //  $layout_template = 'amp';

            // Template Fallbacks
            if ( empty ( $layout_template ) ) {
                $layout_template = ( $this->is_widget ) ? 'widget' : 'loop';
            }

            // Store templates
            $this->layout_template = $layout_template;
            $this->product_template = $product_template;
            $this->shortcode_template = $shortcode_template;

            /*
            echo '<br>shortcode origin: '; var_dump( $this->atts['origin'] ); echo '<br>';
            echo '$template: '; var_dump( $template ); echo '<br>';
            echo '$shortcode_template: '; var_dump( $shortcode_template ); echo '<br>';
            echo '$product_template_validation: '; var_dump( $product_template_validation ); echo '<br>';
            echo '$this->product_template: '; var_dump( $this->product_template ); echo '<br>';
            echo '$layout_template_validation: '; var_dump( $layout_template_validation ); echo '<br>';
            echo '$this->layout_template: '; var_dump( $this->layout_template ); echo '<br>';
            */

            // Load template
            $this->get_template_part( $layout_template, $load = true );
        }

        /**
         * Simple direct template rendering
         *
         * @param $template
         */
        function render_template( $template ) {

            // Allow template manipulation
            //$template = apply_filters( 'aawp_template', $template, $this->atts ); // TODO: Enable when template is stable

            $this->add_action_before_template();

            // Load template
            $this->get_template_part( $template, $load = true );


            $this->add_action_after_template();
        }

        /**
         * Replace deprecated layout template
         *
         * @param $layout_template
         * @param $template
         * @param $template_origin
         *
         * @return string
         */
        private function replace_deprecated_layout_template( $layout_template, $template, $template_origin ) {

            $deprecated_templates = array(
                'bestseller', 'bestseller_table', 'bestseller_widget',
                'box', 'box_table', 'box_widget',
                'new_releases', 'new_releases_widget'
            );

            if ( in_array( $template, $deprecated_templates ) ) {

                if ( strpos($template, '_table') !== false ) {
                    $layout_template = 'table';

                } elseif ( strpos($template, '_widget') !== false || 'widget' === $template_origin ) {
                    $layout_template = 'widget';

                } else {
                    $layout_template = 'loop';
                }
            }

            return $layout_template;
        }

        /**
         * Display response error notice for admins only
         */
        private function display_notice_response_error() {

            if ( aawp_is_user_editor() ) {
                echo ( $this->inline ) ? '<span style="color: red;">' : '<p style="color: red;">';
                echo $this->request_keys . ' >>> ' . $this->items . '<br />';
                echo '<small>(' . __('Only admins can see this error message.', 'aawp' ) . ')</small>';
                echo ( $this->inline ) ? '</span>' : '</p>';
            }
        }

        /**
         * Display "products not found" notice
         */
        private function display_notice_products_not_found() {

            $not_found_text = ( ! empty( $this->options['functions'][$this->type . '_no_products_found_text'] ) ) ? $this->options['functions'][$this->type . '_no_products_found_text'] : null;
            $not_found_hide_public = ( isset ( $this->options['functions'][$this->type . '_no_products_found_hide_public'] ) && $this->options['functions'][$this->type . '_no_products_found_hide_public'] == '1' ) ? true : false;

            if ( ! empty( $not_found_text ) ) {

                if ( $not_found_hide_public && ! aawp_is_user_editor() )
                    return;

                $style = ( aawp_is_user_editor() ) ? ' style="color: red;"' : '';

                echo ( $this->inline ) ? '<span' . $style . ' >' : '<p' . $style . ' >';
                echo $not_found_text;
                echo ( $this->inline ) ? '</span>' : '</p>';
            }
        }

        /**
         * Set template timestamp
         *
         * @param $timestamp
         */
        public function set_timestamp_template( $timestamp ) {
            $this->timestamp_template = $timestamp;
        }
    }
}