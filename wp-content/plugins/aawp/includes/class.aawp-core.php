<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'AAWP_Core' ) ) {

    class AAWP_Core extends AAWP_Wrapper {

        /**
         * Shortcodes used status
         *
         * @var bool
         */
        var $shortcodes_used = false;

        /**
         * Cleanup shortcode output status
         *
         * @var
         */
        var $cleanup_shortcode_output;

        /**
         * AAWP_Core constructor.
         */
        public function __construct()
        {
            // Call parent constructor first
            parent::__construct();

            // Check environment dependencies
            if ( ! aawp_check_dependencies() )
                return;

            if ( ! aawp_is_license_valid() )
                return;

            // Check API status
            //if ( ! aawp_check_api_connection() )
              //  return;

            global $aawp_dependencies;

            $aawp_dependencies = true;

            // Setup core functionality
            $this->setup_shortcodes();

            add_filter( 'the_content', array( $this, 'the_content' ), 20 );
        }

        /**
         * Setup shortcodes
         */
        public function setup_shortcodes() {

            if ( AAWP_SHORTCODE === 'disabled' ) {
                add_shortcode( 'aawp', array( $this, 'render_disabled_shortcode' ) );

                if ( ! shortcode_exists( 'amazon' ) )
                    add_shortcode( 'amazon', array( $this, 'render_disabled_shortcode' ) );

            } else {

                add_shortcode( 'aawp', array( $this, 'render_shortcode' ) );

                if ( AAWP_SHORTCODE === 'amazon' )
                    add_shortcode( 'amazon', array( $this, 'render_shortcode' ) );
            }
        }

        /**
         * Render shortcode
         *
         * @param $atts
         * @param null $content
         * @return false|string
         */
        public function render_shortcode($atts, $content = null) {

            $this->shortcodes_used = true;

            // Shortcode action
            ob_start();

            do_action( 'aawp_shortcode_before_handler', $atts, $content );

            do_action( 'aawp_shortcode_handler', $atts, $content );

            do_action( 'aawp_shortcode_after_handler', $atts, $content );

            $str = ob_get_clean();

            // Return
            return $str;
        }

        /**
         * Render disabled shortcode
         *
         * @param $atts
         * @param null $content
         * @return string
         */
        public function render_disabled_shortcode( $atts, $content = null ) {
            return '';
        }

        /**
         * The content
         *
         * @param $content
         * @return mixed|void
         */
        public function the_content( $content ) {

            // Default
            $aawp_content = false;

            // Check shortcodes
            if ( $this->shortcodes_used )
                $aawp_content = true;

            //$aawp_content = ( has_shortcode( $content, AAWP_SHORTCODE) ) ? true : false;
            $aawp_content = apply_filters( 'aawp_content', $aawp_content );

            if ( $aawp_content ) {

                // Hook for functions
                $content = apply_filters( 'aawp_the_content', $content );
            }

            return $content;
        }
    }

    new AAWP_Core();
}