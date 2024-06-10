<?php
function register_boomdevs_toc_elementor_widget( $widgets_manager ) {

	require_once( __DIR__ . '/boomdevs-toc-elementor-widget.php' );

	$widgets_manager->register( new \Boomdevs_Toc_Elementor_Widget() );

}
add_action( 'elementor/widgets/register', 'register_boomdevs_toc_elementor_widget' );