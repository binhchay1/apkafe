<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Settings
 */
if ( !class_exists( 'AAWP_Box_Settings' ) ) {

    class AAWP_Box_Settings extends AAWP_Functions {

        /**
         * Construct the plugin object
         */
        public function __construct() {
            // Call parent constructor first
            parent::__construct();

            // Setup identifier
            $this->func_id = 'box';
            $this->func_name = __('Boxes', 'aawp');
            $this->func_listener = 'box';

            // Standard variables
            $this->template_default = 'horizontal';
            $this->template = ( empty ( $this->options['functions']['box_template'] ) || ( $this->options['functions']['box_template'] == 'custom' && empty($this->options['functions']['box_template_custom']) ) ) ? $this->template_default : $this->options['functions']['box_template'];
            $this->template_custom = ( !empty ( $this->options['functions']['box_template_custom'] ) ) ? $this->options['functions']['box_template_custom'] : null;
            $this->style = ( !empty ( $this->options['functions']['box_style'] ) ) ? $this->options['functions']['box_style'] : 'standard';

            // Execute
            $this->hooks();
        }

        /**
         * Add settings functions
         */
        public function add_settings_functions_filter( $functions ) {

            $functions[] = $this->func_id;

            return $functions;
        }

        /**
         * Settings: Register section and fields
         */
        public function add_settings() {

            add_settings_section(
                'aawp_box_section',
                false,
                false,
                'aawp_functions'
            );

            add_settings_field(
                'aawp_box',
                __( 'Boxes', 'aawp'),
                array( &$this, 'settings_box_render' ),
                'aawp_functions',
                'aawp_box_section',
                array('label_for' => 'aawp_box_template')
            );
        }

        /**
         * Settings callbacks
         */
        public function settings_box_render() {

            $templates = array(
                $this->template_default => __('Standard', 'aawp')
            );

            $styles = array(
                '0' => __('Standard', 'aawp'),
            );
            ?>

            <!-- Template -->
            <h4 class="first"><?php _e('Templates', 'aawp'); ?></h4>
            <?php $this->do_settings_render_template( $templates, $this->func_id ); ?>

            <!-- Styles -->
            <h4><?php _e('Styles', 'aawp'); ?></h4>
            <?php $this->do_settings_render_style( $styles, $this->func_id ); ?>

            <!-- Notices -->
            <h4><?php _e( 'Notices', 'aawp' ); ?></h4>
            <?php aawp_admin_settings_functions_notices_render( $this->func_id ); ?>

            <?php
            do_action( 'aawp_settings_functions_box_render' );
        }

        /*
         * Hooks & Actions
         */
        public function hooks() {
            add_filter('aawp_widget_types', array(&$this, 'add_widget_types'));

            // Settings functions
            add_filter( $this->settings_functions_filter, array( &$this, 'add_settings_functions_filter' ) );

            add_action( 'aawp_settings_functions_register', array( &$this, 'add_settings' ), 20 );
        }

        /*
         * Add type to widget
         */
        function add_widget_types($types) {

            $types[] = array(
                'id' => $this->func_listener,
                'name' => $this->func_name
            );

            return $types;
        }
    }

    if ( is_admin() ) {
        new AAWP_Box_Settings();
    }
}

/*
 * Functions
 */
if ( !class_exists( 'AAWP_Box_Functions' ) ) {

    class AAWP_Box_Functions extends AAWP_Functions {

        public $func_id, $func_attr;

        public function __construct() {

            parent::__construct();

            $this->func_id = 'box';
            $this->func_listener = 'box';
            $this->func_attr = $this->setup_func_attr( $this->func_id );

            // Hooks
            add_action( 'aawp_shortcode_handler', array( &$this, 'shortcode' ), 10, 2 );
        }

        function shortcode( $atts, $content ) {

            if ( empty( $atts[$this->func_listener] ) )
                return false;

            $this->display( strip_tags( trim( $atts[$this->func_listener] ) ), $content, $atts );
        }

        function display($ids, $content, $atts = array()) {

            // Setup vars
            $this->setup_shortcode_vars($this->intersect_atts($atts, $this->func_attr), $content);

            // Use cache or fetch items from API
            $items = $this->get_items( $ids, $this->func_id );

            // Allowing items manipulation for extended functionality
            $items = $this->prepare_items( $items, $this->func_id, $this->atts );

            // Setup template handler and render output
            $template_handler = new AAWP_Template_Handler();
            $template_handler->set_atts( $this->atts );
            $template_handler->set_template_variables();
            $template_handler->set_type( $this->func_id );
            $template_handler->set_items( $items );
            //$template_handler->set_item_index( $this->item_index );
            $template_handler->set_request_keys( $ids );
            $template_handler->render();
        }
    }

    new AAWP_Box_Functions();
}