<?php
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'AAWP_Widget' ) ) {

    /**
     * Adds AAWP_Widget widget.
     */
    class AAWP_Widget extends WP_Widget {

        /**
         * Register widget with WordPress.
         */
        function __construct() {
            parent::__construct(
                'aawp_widget', // Base ID
                __( 'AAWP (Old Widget)', 'aawp' ), // Name
                array( 'description' => __( 'Please do not use this widget anymore.', 'aawp' ), ) // Args
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
            $type = ( ! empty( $instance['type'] ) ) ? $instance['type'] : null;
            $key = ( ! empty( $instance['key'] ) ) ? $instance['key'] : null;
            $items = ( ! empty( $instance['items'] ) ) ? $instance['items'] : 3;

            if ( !empty($type) && !empty($key) ) {

                echo $args['before_widget'];

                if ( ! empty( $instance['title'] ) ) {
                    echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
                }

                $shortcode = aawp_get_shortcode();

                // Set type
                if ( strpos( $type,'_list') !== false ) {
                    $attr = " " . str_replace('_list', '', $type) . "='" . $key . "'"; ;
                } else {
                    $attr = " $type='$key'";
                }

                // Set max items
                if ( is_numeric($items) && ( strpos($type,'_list') !== false ) ) {
                    $attr .= " items='$items'";
                }

                // Set Widget Template
                $widget_template = apply_filters( 'aawp_widget_template', str_replace('_list', '', $type) . '_widget', $type );
                $attr .= ' template="' . $widget_template . '"';

                // Execute
                echo do_shortcode('[' . $shortcode . $attr . ']');

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
            $type = ! empty( $instance['type'] ) ? $instance['type'] : '';
            $key = ! empty( $instance['key'] ) ? $instance['key'] : '';
            $items = ! empty( $instance['items'] ) ? $instance['items'] : '3';

            $types = array(
                array('id' => 0, 'name' => __('Please select...', 'aawp'))
            );

            $types = apply_filters( 'aawp_widget_types', $types );
            ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'aawp' ); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e( 'Type:', 'aawp' ); ?></label>
                <select id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" class="widefat">
                    <?php foreach ( $types as $typeItem ) { ?>
                        <option value="<?php echo $typeItem['id']; ?>" <?php selected( $type, $typeItem['id'] ); ?>><?php echo $typeItem['name']; ?></option>
                    <?php } ?>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'key' ); ?>"><?php _e( 'Key:', 'aawp' ); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'key' ); ?>" name="<?php echo $this->get_field_name( 'key' ); ?>" type="text" value="<?php echo esc_attr( $key ); ?>">
            </p>
            <p id="<?php echo $this->get_field_id( 'items' ); ?>-wrapper" <?php if (strpos($type,'_list') === false) echo 'style="display: none;"'; ?>>
                <label for="<?php echo $this->get_field_id( 'items' ); ?>"><?php _e( 'Amount of items:', 'aawp' ); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'items' ); ?>" name="<?php echo $this->get_field_name( 'items' ); ?>" type="number" value="<?php echo esc_attr( $items ); ?>">
            </p>

            <!-- Script -->
            <script type="text/javascript">
                jQuery(document).ready(function ($) {

                    /* Custom template select */
                    $('#<?php echo $this->get_field_id( 'type' ); ?>').change(function() {

                        var option = $(this).find('option:selected').val();
                        var target = $('#<?php echo $this->get_field_id( 'items' ); ?>-wrapper');

                        if (option.indexOf("_list") !== -1) {
                            target.fadeIn();
                        } else {
                            target.fadeOut();
                        }
                    });
                });
            </script>
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
            $instance['type'] = ( ! empty( $new_instance['type'] ) ) ? strip_tags( $new_instance['type'] ) : '';
            $instance['key'] = ( ! empty( $new_instance['key'] ) ) ? strip_tags( $new_instance['key'] ) : '';
            $instance['items'] = ( ! empty( $new_instance['items'] ) ) ? strip_tags( $new_instance['items'] ) : '3';

            return $instance;
        }

    } // class AAWP_Widget

    /*
     * Register Widget
     */
    function aawp_register_widget() {
        register_widget( 'AAWP_Widget' );
    }

    add_action( 'widgets_init', 'aawp_register_widget' );
}