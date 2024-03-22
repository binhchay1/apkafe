<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'AAWP_New_Releases_Settings' ) ) {

    class AAWP_New_Releases_Settings extends AAWP_Functions {

        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // Call parent constructor first
            parent::__construct();

            // Setup identifier
            $this->func_id = 'new_releases';
            $this->func_listener = 'new';
            $this->func_name = __('New Releases', 'aawp');

            // Standard variables
            $this->template_default = 'horizontal';
            $this->template = ( empty ( $this->options['functions']['new_releases_template'] ) || ( $this->options['functions']['new_releases_template'] == 'custom' && empty($this->options['functions']['new_releases_template_custom']) ) ) ? $this->template_default : $this->options['functions']['new_releases_template'];
            $this->template_custom = ( !empty ( $this->options['functions']['new_releases_template_custom'] ) ) ? $this->options['functions']['new_releases_template_custom'] : null;
            $this->style = ( !empty ( $this->options['functions']['new_releases_style'] ) ) ? $this->options['functions']['new_releases_style'] : 'standard';

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
                'aawp_new_releases_section',
                false,
                false,
                'aawp_functions'
            );

            add_settings_field(
                'aawp_new_releases',
                __('New Releases', 'aawp'),
                array( &$this, 'settings_new_releases_render' ),
                'aawp_functions',
                'aawp_new_releases_section',
                array('label_for' => 'aawp_new_releases_template')
            );
        }

        /**
         * Settings callbacks
         */
        public function settings_new_releases_render() {

            $templates = array(
                $this->template_default => __('Standard', 'aawp')
            );

            $styles = array(
                '0' => __('Standard', 'aawp'),
            );

            $default_items = ( isset ( $this->options['functions']['new_releases_default_items'] ) ) ? $this->options['functions']['new_releases_default_items'] : '10';
            ?>

            <!-- Template -->
            <h4 class="first"><?php _e('Templates', 'aawp'); ?></h4>
            <?php $this->do_settings_render_template( $templates, $this->func_id ); ?>

            <!-- Styles -->
            <h4><?php _e('Styles', 'aawp'); ?></h4>
            <?php $this->do_settings_render_style( $styles, $this->func_id ); ?>

            <!-- Styles -->
            <h4><?php _e('Default items', 'aawp'); ?></h4>
            <p>
                <input type="number" id="aawp_new_releases_default_items" name="aawp_functions[new_releases_default_items]" value="<?php echo $default_items; ?>" />
                <?php $this->do_admin_note('shortcode'); ?>
            </p>

            <!-- Ribbon text -->
            <h4><?php _e( 'Label', 'aawp' ); ?></h4>
            <?php $this->do_settings_render_ribbon_text( $this->func_id, aawp_get_new_releases_default_ribbon_text() ); ?>

            <!-- Notices -->
            <h4><?php _e( 'Notices', 'aawp' ); ?></h4>
            <?php aawp_admin_settings_functions_notices_render( $this->func_id ); ?>

            <?php
            do_action( 'aawp_settings_functions_new_releases_render' );
        }

        /*
         * Shortcode
         */
        public function shortcode( $atts, $content ) {

            // Exit if box is not set
            if ( empty($atts[$this->func_listener]) )
                return false;

            $AAWP_New_Releases = new AAWP_New_Releases_Functions();
            $AAWP_New_Releases->display( strip_tags( trim( $atts[$this->func_listener] ) ), $atts);
        }

        /*
         * Hooks & Actions
         */
        public function hooks() {
            add_filter('aawp_widget_types', array(&$this, 'add_widget_types'));
            add_filter('aawp_widget_template', array(&$this, 'update_widget_template'), 10, 2 );

            // Settings functions
            add_filter( 'aawp_admin_menu_show_lists', '__return_true' );
            add_filter( $this->settings_functions_filter, array( &$this, 'add_settings_functions_filter' ) );

            add_action( 'aawp_settings_functions_register', array( &$this, 'add_settings' ), 40 );
        }

        function add_widget_types($types) {

            $types[] = array(
                'id' => $this->func_listener . '_list',
                'name' => $this->func_name
            );

            return $types;
        }

        function update_widget_template($template, $type) {

            if ( $type == 'new_list' ) {
                return 'new_releases_widget';
            }

            return $template;
        }
    }

    if ( is_admin() ) {
        new AAWP_New_Releases_Settings();
    }
}

/*
 * Functions
 */
if ( !class_exists( 'AAWP_New_Releases_Functions' ) ) {

    class AAWP_New_Releases_Functions extends AAWP_Functions
    {

        public function __construct() {

            parent::__construct();

            $this->func_id = 'new_releases';
            $this->func_listener = 'new';
            $this->func_attr = $this->setup_func_attr( $this->func_id );

            // Hooks
            add_filter( 'aawp_template_container_class_extensions', array( &$this, 'new_releases_template_container_class_extensions' ), 10, 3 );
            add_filter( 'aawp_func_ribbon_types', array( &$this, 'new_releases_ribbon_type' ), 10, 2 );
            add_action( 'aawp_func_ribbon', array( &$this, 'new_releases_get_ribbon' ), 10, 2 );
            add_action( 'aawp_shortcode_handler', array( &$this, 'shortcode' ), 10, 2 );
        }

        function shortcode( $atts, $content ) {

            if ( empty( $atts[$this->func_listener] ) )
                return false;

            $this->display( $atts[$this->func_listener], $atts );
        }

        function display($keywords, $atts = array()) {

            if ( is_numeric( $keywords ) ) {
                $this->display_shortcode_browse_node_id_notice_after_apiv5();
                return;
            }

            // Setup vars
            $this->setup_shortcode_vars($this->intersect_atts($atts, $this->func_attr));

            // Max items
            if ( !empty($atts['items']) ) {
                $max = $atts['items'];
            } elseif ( isset ( $this->options['functions']['new_releases_default_items'] ) ) {
                $max = intval( $this->options['functions']['new_releases_default_items'] );
            } else {
                $max = $this->items_max;
            }

            $max = ( intval($max) == 1 ) ? apply_filters( 'aawp_new_releases_items_max', $this->items_max, $max ) : $max;
            //$browse_node_search = ( isset( $atts['browsenode'] ) && $atts['browsenode'] == 'none' ) ? 0 : 1;
            $browse_node_search = '';

            // Store output maximum
            $this->update_atts( array( 'items' => $max, 'browse_node_search' => $browse_node_search ) );

            // Use cache or fetch items from API
            $items = $this->get_items(
                $keywords,
                $this->func_id,
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
         * Template functions
         */
        public function new_releases_template_container_class_extensions( $class_extensions, $type, $atts ) {

            if ( $type == 'new_releases' && ! in_array( 'new', $class_extensions ) )
                $class_extensions[] = 'new';

            if ( $type == 'new_releases' && ! in_array( 'ribbon', $class_extensions ) )
                $class_extensions[] = 'ribbon';

            return $class_extensions;
        }

        public function new_releases_ribbon_type( $types, $item_type ) {

            if ( 'new_releases' === $item_type )
                $types[] = 'new';

            return $types;
        }

        public function new_releases_get_ribbon( $ribbon, $type ) {

            if ( 'new' === $type ) {

                $ribbon_text = $this->get_ribbon_text( aawp_get_new_releases_default_ribbon_text() );

                if ( empty( $ribbon_text ) )
                    return $ribbon;

                $ribbon = array(
                    'type' => 'new',
                    'class' => 'aawp-product__ribbon aawp-product__ribbon--new-releases',
                    'text' => $ribbon_text
                );
            }

            return $ribbon;
        }
    }

    new AAWP_New_Releases_Functions();

    /**
     * Register bestseller list type
     *
     * @param $types
     *
     * @return array
     */
    function aawp_register_new_releases_list_type( $types ) {

        $types['new_releases'] = __( 'New Releases', 'aawp' );

        return $types;
    }
    add_filter( 'aawp_list_types', 'aawp_register_new_releases_list_type', 20 );

    /**
     * Returns new releases default ribbon text
     *
     * @return string
     */
    function aawp_get_new_releases_default_ribbon_text() {
        return __( 'New', 'aawp' );
    }
}