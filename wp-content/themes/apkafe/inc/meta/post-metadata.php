<?php
/**
 * Initialize the meta boxes. 
 */
add_action( 'admin_init', 'ia_post_meta_boxes' );
if ( ! function_exists( 'ia_post_meta_boxes' ) ){
	function ia_post_meta_boxes() {
	  $theme_uri = get_template_directory_uri();
	  //layout
	  $meta_box = array(
		'id'        => 'page_layout',
		'title'     => 'Layout settings',
		'desc'      => '',
		'pages'     => array( 'post' ),
		'context'   => 'normal',
		'priority'  => 'high',
		'fields'    => array(
			array(
			  'id'          => 'sidebar_layout',
			  'label'       => __('Sidebar','leafcolor'),
			  'desc'        => __('Select "Default" to use settings in Theme Options','leafcolor'),
			  'std'         => '',
			  'type'        => 'radio-image',
			  'class'       => ''
			),
			array(
			  'id'          => 'content_padding',
			  'label'       => __('Content Padding','leafcolor'),
			  'desc'        => __('Enable default top and bottom padding for content (30px)','leafcolor'),
			  'std'         => 'on',
			  'type'        => 'on-off',
			  'class'       => '',
			  'choices'     => array()
			),
		 )
		);
	  
	  if (function_exists('ot_register_meta_box')) {
		  ot_register_meta_box( $meta_box );
	  }
	}
}

?>