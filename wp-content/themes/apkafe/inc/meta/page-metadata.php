<?php

/**
 * Initialize the meta boxes. 
 */
add_action( 'admin_init', 'ia_page_meta_boxes' );
if ( ! function_exists( 'ia_page_meta_boxes' ) ){
	function ia_page_meta_boxes() {
		$theme_uri = get_template_directory_uri();
		//layout
		$page_meta_box_layout = array(
		'id'        => 'page_layout',
		'title'     => 'Layout settings',
		'pages'     => array( 'page' ),
		'context'   => 'normal',
		'priority'  => 'high',
		'fields'    => array(
			array(
			  'id'          => 'sidebar_layout',
			  'label'       => __('Sidebar','leafcolor'),
			  'desc'        => __('Select "Default" to use Theme Options or Front page fullwidth','leafcolor'),
			  'std'         => '',
			  'type'        => 'radio-image',
			  'choices'     => array(
				  array(
					'value'       => '',
					'label'       => 'Default',
					'src'         => $theme_uri.'/images/options/layout-default.png'
				  ),
				  array(
					'value'       => 'right',
					'label'       => 'Sidebar Right',
					'src'         => $theme_uri.'/images/options/layout-right.png'
				  ),
				  array(
					'value'       => 'left',
					'label'       => 'Sidebar Left',
					'src'         => $theme_uri.'/images/options/layout-left.png'
				  ),
				  array(
					'value'       => 'full',
					'label'       => 'Hidden',
					'src'         => $theme_uri.'/images/options/layout-full.png'
				  ),
			   )
			),
            array(
              'id'          => 'header_content',
              'label'       => __('Header Content','leafcolor'),
              'desc'        => __('Enter header content or shortcodes','leafcolor'),
              'std'         => '',
              'type'        => 'textarea',
            ),
			array(
			  'id'          => 'content_padding',
			  'label'       => __('Content Padding','leafcolor'),
			  'desc'        => __('Enable default top and bottom padding for content (30px)','leafcolor'),
			  'std'         => 'on',
			  'type'        => 'on-off',
			),
			array(
		        'id'          => 'heading_bg',
		        'label'       => 'Page Heading Background',
		        'desc'        => 'Choose Page Heading background (Default is Main color)',
		        'std'         => '',
		        'type'        => 'background',
		    ),
		 )
		);
		ot_register_meta_box( $page_meta_box_layout );
    }
}
