<?php

class Boomdevs_Toc_Widgets extends WP_Widget {

    public function __construct() {
        $widget_ops = array(
            'classname'   => 'boomdevs_toc_shortcode_widget',
            'description' => __( 'Shortcode or HTML or Plain Text.', 'boomdevs-toc' ),
        );
        parent::__construct( 'boomdevs-toc-shortcode-widget', __( 'TOP Table Of Contents', 'boomdevs-toc' ), $widget_ops);
    }

	/**
	 * Shortcode insert in widgets
	 */
	public function widget( $args, $instance ) {

		$title = esc_html(! empty( $instance['title'] ) ? $instance['title'] : '');

		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget'];

			if ( ! empty( $title ) ) {

				echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
			}
			if(is_singular()){
				echo do_shortcode( '[boomdevs_toc]' );
			}

		echo $args['after_widget'];
	}

	/**
	 * widget input form function
	 */
	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title' => '',
			)
		);
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'boomdevs-toc' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<?php
	}

	/**
	 * widget update function
	 */
	public function update( $new_instance, $old_instance ) {
		$new_instance = wp_parse_args(
			$new_instance,
			array(
				'title'  		=> '',
			)
		);

		$instance                  = $old_instance;
		$instance['title']         = sanitize_text_field( $new_instance['title'] );
		return $instance;
	}

	/**
	 * Register the widget
	 */
    public function boomdevs_toc_widget() {
        register_widget( 'Boomdevs_Toc_widgets' );
    }
}
