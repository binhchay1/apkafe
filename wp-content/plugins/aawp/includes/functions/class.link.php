<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'AAWP_Link_Settings' ) ) {

    class AAWP_Link_Settings extends AAWP_Functions {

        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // Call parent constructor first
            parent::__construct();

            // Setup identifier
            $this->func_id = 'link';
            $this->func_name = __('Links', 'aawp');
            $this->func_listener = 'link';

            /*
            $this->func_attr = array(
                $this->func_listener, 'title',
            );
            */

            // Standard variables
            $this->link_icon = ( !empty ( $this->options['functions']['link_icon'] ) ) ? $this->options['functions']['link_icon'] : '0';

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

        /*
         * Hooks
         */
        public function hooks() {
            //add_filter( 'the_content', array( &$this, 'add_associate_tag_to_amazon_links' ) );

            // Settings functions
            add_filter( $this->settings_functions_filter, array( &$this, 'add_settings_functions_filter' ) );

            add_action( 'aawp_settings_functions_register', array( &$this, 'add_settings' ), 10 );
        }

        /**
         * Settings: Register section and fields
         */
        public function add_settings() {

            add_settings_section(
                'aawp_link_section',
                false,
                false,
                'aawp_functions'
            );

            add_settings_field(
                'aawp_link',
                __('Links', 'aawp'),
                array(&$this, 'settings_link_render'),
                'aawp_functions',
                'aawp_link_section',
                array('label_for' => 'aawp_link_icon')
            );
        }

        /**
         * Settings callbacks
         */
        public function settings_link_header_render()
        {
            ?>
            <h2 class="aawp-section-title"><?php _e('Links', 'aawp'); ?></h2>
            <?php
        }

        public function settings_link_render() {

            $icons = array(
                '0' => __('Disabled', 'aawp'),
                'amazon' => __('Amazon Icon', 'aawp'),
                'amazon-logo' => __('Amazon Logo', 'aawp'),
                'cart' => __('Cart icon', 'aawp'),
            );
            ?>

            <!-- Link Icon -->
            <h4 class="first"><?php _e('Icon', 'aawp'); ?></h4>
            <p>
                <label for="aawp_link_icon"><?php _e('Display icon after single links', 'aawp'); ?></label>:
                <select id="aawp_link_icon" name="aawp_functions[link_icon]">
                    <?php foreach ( $icons as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $this->link_icon, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
                <?php $this->do_admin_note('shortcode'); ?>
            </p>

            <p>
                <u><?php _e('Example', 'aawp'); ?></u>:
                <a class="aawp-link" href="http://www.amazon.de/gp/product/144934190X/ref=as_li_tl?ie=UTF8&camp=1638&creative=19454&creativeASIN=144934190X&linkCode=as2&tag=aawp-21" title="WordPress: The Missing Manual" target="_blank" rel="nofollow noopener sponsored">WordPress: The Missing Manual</a>
                <a id="aawp_link_icon_preview_container" class="aawp-link-icon-container"<?php if ($this->link_icon == '0') echo ' style="display: none;"'; ?> href="http://www.amazon.de/gp/product/144934190X/ref=as_li_tl?ie=UTF8&camp=1638&creative=19454&creativeASIN=144934190X&linkCode=as2&tag=aawp-21" title="WordPress: The Missing Manual" target="_blank" rel="nofollow noopener sponsored">
                    <span id="aawp_link_icon_preview" class="aawp-link-icon <?php if ($this->link_icon != '0') echo $this->link_icon; ?>"></span>
                </a>
            </p>

            <!-- Notices -->
            <h4><?php _e( 'Notices', 'aawp' ); ?></h4>
            <?php aawp_admin_settings_functions_notices_render( $this->func_id ); ?>

            <?php
            do_action( 'aawp_settings_functions_link_render' );
        }
    }

    if ( is_admin() ) {
        new AAWP_Link_Settings();
    }
}

/*
 * Functions
 */
if ( !class_exists( 'AAWP_Link_Functions' ) ) {

    class AAWP_Link_Functions extends AAWP_Functions {

        public function __construct() {

            parent::__construct();

            $this->func_id = 'link';
            $this->func_listener = 'link';
            $this->func_attr = $this->setup_func_attr( $this->func_id, array( 'link', 'title', 'link_icon', 'link_text', 'link_overwrite' ) );

            // Hooks
            add_action( 'aawp_shortcode_handler', array( &$this, 'shortcode' ), 10, 1 );
            add_action( 'aawp_product_renewed', array( &$this, 'delete_transient' ), 10, 1 );
        }

        private function get_transient_key( $asin ) {
            return 'aawp_link_' . md5( $asin );
        }

        function delete_transient( $product_id ) {

            $product = aawp_get_product( $product_id );

            if ( empty( $product['asin'] ) )
                return;

            $transient_key = $this->get_transient_key( $product['asin'] );
            delete_transient( $transient_key );
        }

        function shortcode( $atts ) {

            if ( empty( $atts[$this->func_listener] ) )
                return false;

            $this->display( strip_tags( trim( $atts[$this->func_listener] ) ), $atts );
        }

        function display($id, $atts = array()) {

            // Setup vars
            $this->setup_shortcode_vars( $this->intersect_atts( $atts, $this->func_attr ) );

            // Use cache or fetch items from API
            $cache_key = $this->get_transient_key( $id );

            $items = get_transient( $cache_key );

            if( $items === false ) {
                $items = $this->get_items( $id, $this->func_id );

                if ( ! empty( $items[$id] ) )
                    set_transient( $cache_key, $items, 60 * 60 );
            }

            // Allowing items manipulation for extended functionality
            $items = $this->prepare_items( $items, $this->func_id, $this->atts );

            // Setup template handler
            $template_handler = new AAWP_Template_Handler();
            $template_handler->set_atts( $this->atts );
            $template_handler->set_type( $this->func_id );
            $template_handler->set_items( $items );
            $template_handler->set_request_keys( $id );

            // Build output
            $output = '';

            if ( ! empty( $items[$id] ) ) {

                $template_handler->setup_item( 0, $items[$id] );

                // Container
                $link_container = $template_handler->the_product_container( $echo = false );

                // URL
                $link_url = $template_handler->get_product_url();

                // Class(es)
                $link_class = ( ! empty( $this->atts['link_class'] ) ) ? esc_html( $this->atts['link_class'] ) : 'aawp-link';

                // Title
                $link_title = $template_handler->get_product_link_title();

                // Maybe update link text
                $link_text = ( ! empty( $this->atts['link_text'] ) ) ? esc_html( $this->atts['link_text'] ) : $template_handler->get_product_title();

                // Icon
                $link_icon = ( isset ( $this->options['functions']['link_icon'] ) ) ? $this->options['functions']['link_icon'] : '0';

                if ( ! empty( $this->atts['link_icon'] ) ) {
                    if ( $this->atts['link_icon'] == 'none' ) {
                        $link_icon = '0';
                    } elseif ( $this->atts['link_icon'] === 'amazon' ) {
                        $link_icon = 'amazon';
                    } elseif ( $this->atts['link_icon'] === 'amazon-logo' ) {
                        $link_icon = 'amazon-logo';
                    } elseif ( $this->atts['link_icon'] === 'cart' ) {
                        $link_icon = 'cart';
                    }
                }

                // Build link
                $output .= '<a'; // Start wrapper
                    $output .= ' class="' . $link_class . '"';
                    $output .= ' href="' . $link_url . '"';
                    $output .= ' title="' . $link_title . '"';
                    $output .= ' target="_blank"';
                    $output .= ' rel="nofollow noopener sponsored"';
                    $output .= ' ' . $link_container;
                $output .= '>';
                $output .= ( 'none' != $link_text ) ? $link_text : ''; // Text
                $output .= '</a>'; // End Wrapper

                // Build link icon
                if ( $link_icon != '0') {

                    $link_icon_class = 'aawp-link-icon-container';

                    if ( 'amazon-logo' === $link_icon )
                        $link_icon_class .= ' aawp-link-icon-container--large';

                    $output .= '&nbsp;'; // Add spacing

                    $output .= '<a'; // Start wrapper
                        $output .= ' class="' . $link_icon_class . '"';
                        $output .= ' href="' . $link_url . '"';
                        $output .= ' title="' . $link_title . '"';
                        $output .= ' target="_blank"';
                        $output .= ' rel="nofollow noopener sponsored"';
                        $output .= ' ' . $link_container;
                    $output .= '>';
                    $output .= '<span class="aawp-link-icon ' . $link_icon . '"></span>'; // Icon
                    $output .= '</a>'; // End Wrapper
                }
            }

            // Finally output
            $template_handler->set_inline();
            $template_handler->render_inline( $output );
        }
    }

    new AAWP_Link_Functions();
}