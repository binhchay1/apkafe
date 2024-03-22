<?php
/**
 * New Releases Widget
 *
 * @package     AAWP\Functions\Widgets
 * @since       3.2.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'AAWP_Widget_New_Releases' ) ) {

    /**
     * Adds AAWP_Widget widget.
     */
    class AAWP_Widget_New_Releases extends WP_Widget {

        /**
         * Register widget with WordPress.
         */
        function __construct() {
            parent::__construct(
                'aawp_widget_new_releases', // Base ID
                'AAWP - ' . __( 'Amazon New Releases List', 'aawp' ), // Name
                array( 'description' => __( 'Display an automated new releases list.', 'aawp' ), ) // Args
            );
        }

        /**
         * Front-end display of widget.
         *
         * @see WP_Widget::widget()
         *
         * @param array $args     Widget arguments.
         * @param array $instance Saved values from database.
         */
        public function widget( $args, $instance ) {

            // Widget Output
            $keys = ( ! empty( $instance['keys'] ) ) ? $instance['keys'] : null;
            $items = ( ! empty( $instance['items'] ) ) ? $instance['items'] : '3';
            $style = ( ! empty( $instance['style'] ) ) ? $instance['style'] : null;
            $template = ( ! empty( $instance['template'] ) ) ? $instance['template'] : null;
            $template_custom = ( ! empty( $instance['template_custom'] ) ) ? $instance['template_custom'] : null;

            if ( ! empty( $keys ) && ! empty( $template ) ) {

                echo $args['before_widget'];

                if ( ! empty( $instance['title'] ) ) {
                    echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
                }

                // Collect attributes
                $atts = array();

                $atts['items'] = $items;

                // Style
                if ( ! empty( $style ) )
                    $atts['style'] = $style;

                // Set Widget Template
                $widget_template = ( ! empty ( $template_custom ) ) ? $template_custom : $template;
                $widget_template = apply_filters( 'aawp_widget_bestseller_template', $widget_template, $template, $template_custom );

                // Execute
                aawp_widget_do_shortcode( 'new', $keys, $widget_template, $atts );

                echo $args['after_widget'];
            }
        }

        /**
         * Back-end widget form.
         *
         * @see WP_Widget::form()
         *
         * @param array $instance Previously saved values from database.
         */
        public function form( $instance ) {
            $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Title', 'aawp' );
            $keys = ! empty( $instance['keys'] ) ? $instance['keys'] : '';
            $items = ! empty( $instance['items'] ) ? $instance['items'] : '3';
            $style = ( ! empty( $instance['style'] ) ) ? $instance['style'] : '0';
            $template = ! empty( $instance['template'] ) ? $instance['template'] : aawp_get_default_widget_template();
            $template_custom = ! empty( $instance['template_custom'] ) ? $instance['template_custom'] : '';

            $styles = aawp_get_widget_styles( $type = 'new_releases' );
            $templates = aawp_get_widget_templates( $type = 'new_releases' );

            ?>

            <span class="aawp-widget-logo"></span>

            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'aawp' ); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'keys' ); ?>"><?php _e( 'Search term:', 'aawp' ); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'keys' ); ?>" name="<?php echo $this->get_field_name( 'keys' ); ?>" type="text" value="<?php echo esc_attr( $keys ); ?>">
                <br />
                <small><?php _e( 'Enter a search term such as "smartphone"', 'aawp' ); ?></small>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'items' ); ?>"><?php _e( 'Amount of items:', 'aawp' ); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'items' ); ?>" name="<?php echo $this->get_field_name( 'items' ); ?>" type="number" value="<?php echo esc_attr( $items ); ?>">
                <br />
                <small><?php _e( 'The maximum amount of items for this type is 10.', 'aawp' ); ?></small>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php _e( 'Style:', 'aawp' ); ?></label>
                <select id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>" class="widefat">
                    <?php foreach ( $styles as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $style, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php _e( 'Template:', 'aawp' ); ?></label>
                <select id="<?php echo $this->get_field_id( 'template' ); ?>" name="<?php echo $this->get_field_name( 'template' ); ?>" class="widefat">
                    <?php foreach ( $templates as $templateItem ) { ?>
                        <option value="<?php echo $templateItem['slug']; ?>" <?php selected( $template, $templateItem['slug'] ); ?>><?php echo $templateItem['name']; ?></option>
                    <?php } ?>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'template_custom' ); ?>"><?php _e( 'Custom Template:', 'aawp' ); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'template_custom' ); ?>" name="<?php echo $this->get_field_name( 'template_custom' ); ?>" type="text" value="<?php echo esc_attr( $template_custom ); ?>">
                <br />
                <small><?php _e( 'Please enter the custom template name without the file extension: e.g. <em>my_widget</em>', 'aawp' ); ?></small>
            </p>

            <strong><?php _e('Need help?', 'aawp'); ?></strong>
            <ul class="aawp-widget-docs-list">
                <li><a href="<?php echo esc_url( aawp_get_page_url('docs:new_releases') ); ?>" target="_blank" rel="nofollow"><?php _e('Documentation', 'aawp'); ?></a></li>
                <li><a href="<?php echo esc_url( aawp_get_page_url('docs:browse_nodes') ); ?>" target="_blank" rel="nofollow"><?php _e('Browse Nodes', 'aawp'); ?></a></li>
                <li><a href="<?php echo esc_url( aawp_get_page_url('docs:shortcodes') ); ?>" target="_blank" rel="nofollow"><?php _e('All available shortcode adjustments', 'aawp'); ?></a></li>
                <li><a href="<?php echo esc_url( aawp_get_page_url('docs:templating') ); ?>" target="_blank" rel="nofollow"><?php _e('PHP-Templating', 'aawp'); ?></a></li>
            </ul>

            <?php
        }

        /**
         * Sanitize widget form values as they are saved.
         *
         * @see WP_Widget::update()
         *
         * @param array $new_instance Values just sent to be saved.
         * @param array $old_instance Previously saved values from database.
         *
         * @return array Updated safe values to be saved.
         */
        public function update( $new_instance, $old_instance ) {
            $instance = array();
            $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
            $instance['style'] = ( ! empty( $new_instance['style'] ) ) ? strip_tags( $new_instance['style'] ) : '0';
            $instance['template'] = ( ! empty( $new_instance['template'] ) ) ? strip_tags( $new_instance['template'] ) : aawp_get_default_widget_template();

            // IDs
            $instance['keys'] = ( ! empty( $new_instance['keys'] ) ) ? strip_tags( $new_instance['keys'] ) : '';

            if ( ! empty( $instance['keys'] ) ) {
                $instance['keys'] = trim( $instance['keys'] );
            }

            // Items
            $instance['items'] = ( ! empty( $new_instance['items'] ) ) ? strip_tags( $new_instance['items'] ) : '3';

            if ( ! is_numeric( $new_instance['items'] ) ) {
                $instance['items'] = '3';

            } else {

                if ( intval( $new_instance['items'] ) < 1 ) {
                    $new_instance['items'] = '1';

                } elseif ( intval( $new_instance['items'] ) > 10 )
                    $new_instance['items'] = '10';
            }

            // Template custom
            $instance['template_custom'] = ( ! empty( $new_instance['template_custom'] ) ) ? strip_tags( $new_instance['template_custom'] ) : '';

            if ( ! empty( $instance['template_custom'] ) && strpos( $instance['template_custom'], '.php') !== false ) {
                $instance['template_custom'] = str_replace( '.php', '', $instance['template_custom'] );
            }

            // Finally update
            return $instance;
        }

    } // class AAWP_Widget_New_Releases

    /*
     * Register Widget
     */
    function aawp_register_new_releases_widget() {

        if ( ! class_exists( 'AAWP_New_Releases_Functions' ) )
            return;

        register_widget( 'AAWP_Widget_New_Releases' );
    }

    add_action( 'widgets_init', 'aawp_register_new_releases_widget' );
}