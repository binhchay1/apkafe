<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Settings
 */
if ( !class_exists( 'AAWP_Bestseller_Settings' ) ) {

    class AAWP_Bestseller_Settings extends AAWP_Functions {

        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // Call parent constructor first
            parent::__construct();

            // Setup identifier
            $this->func_id = 'bestseller';
            $this->func_name = __('Bestseller', 'aawp');
            $this->func_listener = 'bestseller';

            // Standard variables
            $this->template_default = 'horizontal';
            $this->template = ( empty ( $this->options['functions']['bestseller_template'] ) || ( $this->options['functions']['bestseller_template'] == 'custom' && empty($this->options['functions']['bestseller_template_custom']) ) ) ? $this->template_default : $this->options['functions']['bestseller_template'];
            $this->template_custom = ( !empty ( $this->options['functions']['bestseller_template_custom'] ) ) ? $this->options['functions']['bestseller_template_custom'] : null;
            $this->style = ( !empty ( $this->options['functions']['bestseller_style'] ) ) ? $this->options['functions']['bestseller_style'] : 'standard';

            // Hooks
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
                'aawp_bestseller_section',
                false,
                false,
                'aawp_functions'
            );

            add_settings_field(
                'aawp_bestseller',
                __( 'Bestseller', 'aawp'),
                array( &$this, 'settings_bestseller_render' ),
                'aawp_functions',
                'aawp_bestseller_section',
                array('label_for' => 'aawp_bestseller_template')
            );
        }

        /**
         * Settings callbacks
         */
        public function settings_bestseller_render() {

            $templates = array(
                $this->template_default => __('Standard', 'aawp')
            );

            $styles = array(
                '0' => __('Standard', 'aawp'),
            );

            $default_items = ( isset ( $this->options['functions']['bestseller_default_items'] ) ) ? $this->options['functions']['bestseller_default_items'] : '10';
            ?>

            <!-- Template -->
            <h4 class="first"><?php _e('Templates', 'aawp'); ?></h4>
            <?php $this->do_settings_render_template( $templates, $this->func_id ); ?>

            <!-- Styles -->
            <h4><?php _e('Styles', 'aawp'); ?></h4>
            <?php $this->do_settings_render_style( $styles, $this->func_id ); ?>

            <!-- Default items -->
            <h4><?php _e('Default items', 'aawp'); ?></h4>
            <p>
                <input type="number" id="aawp_bestseller_default_items" name="aawp_functions[bestseller_default_items]" value="<?php echo $default_items; ?>" />
                <?php $this->do_admin_note('shortcode'); ?>
            </p>

            <!-- Ribbon text -->
            <h4><?php _e( 'Label', 'aawp' ); ?></h4>
            <?php $this->do_settings_render_ribbon_text( $this->func_id, aawp_get_bestseller_default_ribbon_text() ); ?>

            <!-- Notices -->
            <h4><?php _e( 'Notices', 'aawp' ); ?></h4>
            <?php aawp_admin_settings_functions_notices_render( $this->func_id ); ?>

            <?php
            do_action( 'aawp_settings_functions_bestseller_render' );
        }

        /*
         * Hooks & Actions
         */
        public function hooks() {
            add_filter('aawp_widget_types', array(&$this, 'add_widget_types'));

            // Settings functions
            add_filter( 'aawp_admin_menu_show_lists', '__return_true' );
            add_filter( $this->settings_functions_filter, array( &$this, 'add_settings_functions_filter' ) );

            add_action( 'aawp_settings_functions_register', array( &$this, 'add_settings' ), 30 );
        }

        /*
         * Add type to widget
         */
        function add_widget_types($types) {

            $types[] = array(
                'id' => $this->func_listener . '_list',
                'name' => $this->func_name
            );

            return $types;
        }
    }

    if ( is_admin() ) {
        new AAWP_Bestseller_Settings();
    }
}

/*
 * Functions
 */
if ( !class_exists( 'AAWP_Bestseller_Functions' ) ) {

    class AAWP_Bestseller_Functions extends AAWP_Functions
    {

        public function __construct() {

            parent::__construct();

            $this->func_id = 'bestseller';
            $this->func_listener = 'bestseller';
            $this->func_attr = $this->setup_func_attr( $this->func_id );

            // Hooks
            add_filter( 'aawp_template_container_class_extensions', array( &$this, 'bestseller_template_container_class_extensions' ), 10, 3 );
            add_filter( 'aawp_func_ribbon_types', array( &$this, 'bestseller_ribbon_type' ), 10, 2 );
            add_action( 'aawp_func_ribbon', array( &$this, 'get_bestseller_ribbon' ), 10, 2 );
            add_filter( 'aawp_show_product_numbering', array( &$this, 'bestseller_numbering' ), 10, 3 );
            add_filter( 'aawp_product_numbering_label', array( &$this, 'bestseller_numbering_label' ), 10, 3 );
            add_action( 'aawp_shortcode_handler', array( &$this, 'shortcode' ), 10, 2 );
        }

        function shortcode( $atts, $content ) {

            if ( empty( $atts[$this->func_listener] ) )
                return false;

            $this->display( strip_tags( trim( $atts[$this->func_listener] ) ), $atts );
        }

        function display( $keywords, $atts = array()) {

            if ( is_numeric( $keywords ) ) {
                $this->display_shortcode_browse_node_id_notice_after_apiv5();
                return;
            }

            // Setup vars
            $this->setup_shortcode_vars($this->intersect_atts($atts, $this->func_attr));

            // Max items (incl. from older versions Fallbacks)
            if ( !empty($atts['max']) ) {
                $max = $atts['max'];
            } elseif ( !empty($atts['count']) ) {
                $max = $atts['count'];
            } elseif ( !empty($atts['items']) ) {
                $max = $atts['items'];
            } elseif ( isset ( $this->options['functions']['bestseller_default_items'] ) ) {
                $max = intval( $this->options['functions']['bestseller_default_items'] );
            } else {
                $max = $this->items_max;
            }

            $max = ( intval($max) == 1 ) ? apply_filters( 'aawp_bestseller_items_max', $this->items_max, $max ) : $max;
            //$browse_node_search = ( isset( $atts['browsenode'] ) && $atts['browsenode'] == 'none' ) ? 0 : 1; // TODO: Deprecated
            $browse_node_search = '';

            if ( empty( $this->atts['keywords'] ) && ! is_numeric( $keywords ) ) {
                $this->atts['keywords'] = $keywords;
            }

            //echo 'bestseller >> display() >> $max: ' . $max . '<br>';

            // Store output maximum
            $this->update_atts( array( 'items' => $max, 'browse_node_search' => $browse_node_search ) );

            // Use cache or fetch items from API
            $items = $this->get_items(
                $keywords,
                'bestseller',
                true,
                array( 'items' => $max, 'browse_node_search' => $browse_node_search )
            );

            // Allowing items manipulation for extended functionality
            $items = $this->prepare_items( $items, $this->func_id, $this->atts );

            // Setup template handler and render output
            $template_handler = new AAWP_Template_Handler();
            $template_handler->set_atts( $this->atts );
            $template_handler->set_template_variables();
            $template_handler->set_type( $this->func_id );
            $template_handler->set_items( $items );
            $template_handler->set_item_index( $this->item_index );
            $template_handler->set_request_keys( $keywords );
            $template_handler->set_timestamp_list( $this->timestamp_list );
            $template_handler->render();
        }

        /*
         * Hooks
         */
        public function bestseller_template_container_class_extensions( $class_extensions, $type, $atts ) {

            if ( $type == 'bestseller' && ! in_array( 'bestseller', $class_extensions ) )
                $class_extensions[] = 'bestseller';

            if ( $type == 'bestseller' && ! in_array( 'ribbon', $class_extensions ) )
                $class_extensions[] = 'ribbon';

            return $class_extensions;
        }

        public function bestseller_ribbon_type( $types, $item_type ) {

            if ( 'bestseller' === $item_type )
                $types[] = 'bestseller';

            return $types;
        }

        public function get_bestseller_ribbon( $ribbon, $type ) {

            if ( 'bestseller' === $type ) {

                $ribbon_text = $this->get_ribbon_text( aawp_get_bestseller_default_ribbon_text() );

                if ( empty( $ribbon_text ) )
                    return $ribbon;

                $ribbon = array(
                    'type' => 'bestseller',
                    'class' => 'aawp-product__ribbon aawp-product__ribbon--bestseller',
                    'text' => $ribbon_text
                );
            }

            return $ribbon;
        }

        /*
         * Table template numbering
         */
        public function bestseller_numbering( $numbering, $item_type, $atts ) {

            if ( 'bestseller' === $item_type ) {
                return true;
            }

            return $numbering;
        }

        public function bestseller_numbering_label( $label, $item_type, $atts ) {

            if ( 'bestseller' === $item_type ) {
	            return $this->get_ribbon_text( aawp_get_bestseller_default_ribbon_text() );
            }

            return $label;
        }

        /*
         * Deprecated
         */
        public function get_bestseller_text() {
            return __('Bestseller No.', 'aawp');
        }

        public function get_bestseller_position() {
            return $this->item_index;
        }
    }

    new AAWP_Bestseller_Functions();

    /**
     * Register bestseller list type
     *
     * @param $types
     *
     * @return array
     */
    function aawp_register_bestseller_list_type( $types ) {

        $types['bestseller'] = __( 'Bestseller', 'aawp' );

        return $types;
    }
    add_filter( 'aawp_list_types', 'aawp_register_bestseller_list_type', 10 );

    /**
     * Returns bestseller default ribbon text
     *
     * @return string
     */
    function aawp_get_bestseller_default_ribbon_text() {
        return __( 'Bestseller No. %NUMBER%', 'aawp' );
    }
}