<?php
/**
 * Box Widget
 *
 * @package     AAWP\Functions\Widgets
 * @since       3.2.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'AAWP_Widget_Box' ) ) {

    /**
     * Adds AAWP_Widget widget.
     */
    class AAWP_Widget_Box extends WP_Widget {

        /**
         * Register widget with WordPress.
         */
        function __construct() {
            parent::__construct(
                'aawp_widget_box', // Base ID
                'AAWP - ' . __( 'Amazon Single/Multiple Boxes', 'aawp' ), // Name
                array( 'description' => __( 'Display single or multiple product boxes', 'aawp' ), ) // Args
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
            $ids = ( ! empty( $instance['ids'] ) ) ? $instance['ids'] : null;
            $style = ( ! empty( $instance['style'] ) ) ? $instance['style'] : null;
            $template = ( ! empty( $instance['template'] ) ) ? $instance['template'] : null;
            $template_custom = ( ! empty( $instance['template_custom'] ) ) ? $instance['template_custom'] : null;

            if ( ! empty( $ids ) && ! empty( $template ) ) {

                echo $args['before_widget'];

                if ( ! empty( $instance['title'] ) ) {
                    echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
                }

                // Collect attributes
                $atts = array();

                // Style
                if ( ! empty( $style ) )
                    $atts['style'] = $style;

                // Set Widget Template
                $widget_template = ( ! empty ( $template_custom ) ) ? $template_custom : $template;
                $widget_template = apply_filters( 'aawp_widget_box_template', $widget_template, $template, $template_custom );

                // Execute
                aawp_widget_do_shortcode( 'box', $ids, $widget_template, $atts );

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
            $ids = ! empty( $instance['ids'] ) ? $instance['ids'] : '';
            $style = ( ! empty( $instance['style'] ) ) ? $instance['style'] : '0';
            $template = ! empty( $instance['template'] ) ? $instance['template'] : aawp_get_default_widget_template();
            $template_custom = ! empty( $instance['template_custom'] ) ? $instance['template_custom'] : '';

            $styles = aawp_get_widget_styles( $type = 'box' );
            $templates = aawp_get_widget_templates( $type = 'box' );

            ?>

            <span class="aawp-widget-logo"></span>

            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'aawp' ); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'ids' ); ?>"><?php _e( 'ASIN/ISBN:', 'aawp' ); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'ids' ); ?>" name="<?php echo $this->get_field_name( 'ids' ); ?>" type="text" value="<?php echo esc_attr( $ids ); ?>" placeholder="<?php _e( 'e.g. B00NGOD2OI or 3551559007', 'aawp' ); ?>">
                <br />
                <small><?php _e( 'In order to show more than one box, simply enter multiple ASIN/ISBN and separate them with comma: e.g. <em>B00NGOD2OI,3551559007</em>', 'aawp' ); ?></small>
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
                <li><a href="<?php echo esc_url( aawp_get_page_url('docs:box') ); ?>" target="_blank" rel="nofollow"><?php _e('Documentation', 'aawp'); ?></a></li>
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
            $instance['ids'] = ( ! empty( $new_instance['ids'] ) ) ? strip_tags( $new_instance['ids'] ) : '';

            if ( ! empty( $instance['ids'] ) ) {
                $instance['ids'] = str_replace( array( ' ', ';' ), array( '', ',' ), $instance['ids'] );
                $instance['ids'] = rtrim( $instance['ids'], ',' );
            }

            // Template custom
            $instance['template_custom'] = ( ! empty( $new_instance['template_custom'] ) ) ? strip_tags( $new_instance['template_custom'] ) : '';

            if ( ! empty( $instance['template_custom'] ) && strpos( $instance['template_custom'], '.php') !== false ) {
                $instance['template_custom'] = str_replace( '.php', '', $instance['template_custom'] );
            }

            // Finally update
            return $instance;
        }

    } // class AAWP_Widget_Box

    /*
     * Register Widget
     */
    function aawp_register_box_widget() {

        if ( ! class_exists( 'AAWP_Box_Functions' ) )
            return;

        register_widget( 'AAWP_Widget_Box' );
    }

    add_action( 'widgets_init', 'aawp_register_box_widget' );
}