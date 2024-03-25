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
			  'class'       => '',
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
add_action( 'admin_init', 'ia_product_meta_boxes' );
if ( ! function_exists( 'ia_product_meta_boxes' ) ){
	function ia_product_meta_boxes() {
		$theme_uri = get_template_directory_uri();
		//app
		$meta_box_app = array(
		'id'        => 'product_app',
		'title'     => 'App settings',
		'desc'      => '',
		'pages'     => array( 'product','app_portfolio' ),
		'context'   => 'normal',
		'priority'  => 'high',
		'fields'    => array(
			array(
			  'id'          => 'app-icon',
			  'label'       => __('Icon','leafcolor'),
			  'desc'        => __('Upload App Icon','leafcolor'),
			  'std'         => '',
			  'type'        => 'upload',
			  'class'       => '',
			  'choices'     => array()
			),
			array(
			  'id'          => 'app-banner',
			  'label'       => __('Header Banner','leafcolor'),
			  'desc'        => __('Upload App Banner Image','leafcolor'),
			  'std'         => '',
			  'type'        => 'upload',
			  'class'       => '',
			  'choices'     => array()
			),
			array(
			  'id'          => 'banner-darkness',
			  'label'       => __('Header Banner Darkness (Optional)','leafcolor'),
			  'desc'        => __('Adjust Banner Darkness level % (To make title readable)','leafcolor'),
			  'std'         => '0',
			  'type'        => 'numeric-slider',
			  'class'       => '',
			  'min_max_step'=> '0,100,5',
			  'choices'     => array()
			),
			//devide
			array(
				'label'       => __( 'Device for displaying Screenshots', 'leafcolor' ),
				'id'          => 'devide',
				'type'        => 'select',
				'desc'        => '',
				'std'         => '',
				'choices'     => array(
					array(
						'value'       => 'def_themeoption',
						'label'       => __( 'Default theme options', 'leafcolor' ),
						'src'         => ''
					),
					array(
						'value'       => 'def',
						'label'       => __( 'Default Gallery', 'leafcolor' ),
						'src'         => ''
					),
					array(
						'value'       => 'iphone6',
						'label'       => __( 'iPhone 6', 'leafcolor' ),
						'src'         => ''
					),
					array(
						'value'       => 'iphone6plus',
						'label'       => __( 'iPhone 6 Plus', 'leafcolor' ),
						'src'         => ''
					),
					array(
						'value'       => 'iphone5s',
						'label'       => __( 'iPhone 5S', 'leafcolor' ),
						'src'         => ''
					),
					array(
						'value'       => 'iphone5c',
						'label'       => __( 'iPhone 5C', 'leafcolor' ),
						'src'         => ''
					),
					array(
						'value'       => 'iphone4s',
						'label'       => __( 'iPhone 4S', 'leafcolor' ),
						'src'         => ''
					),
					array(
						'value'       => 'nexus5',
						'label'       => __( 'Nexus 5', 'leafcolor' ),
						'src'         => ''
					),
					array(
						'value'       => 'lumia920',
						'label'       => __( 'Lumia 920', 'leafcolor' ),
						'src'         => ''
					),
					array(
						'value'       => 'galaxys5',
						'label'       => __( 'Galaxy S5', 'leafcolor' ),
						'src'         => ''
					),
					array(
						'value'       => 'htcone',
						'label'       => __( 'HTC One', 'leafcolor' ),
						'src'         => ''
					),
					array(
						'value'       => 'ipadmini',
						'label'       => __( 'iPad Mini', 'leafcolor' ),
						'src'         => ''
					),
					array(
						'value'       => 'macbookair',
						'label'       => __( 'Macbook Air', 'leafcolor' ),
						'src'         => ''
					),
					array(
						'value'       => 'macbookpro',
						'label'       => __( 'Macbook Pro', 'leafcolor' ),
						'src'         => ''
					),
					array(
						'value'       => 'applewatch',
						'label'       => __( 'Apple Watch', 'leafcolor' ),
						'src'         => ''
					),
				),
			),//end devide
			//color
			array(
				'id'          => 'devide_color_iphone6',
				'label'       => __( 'Device color', 'leafcolor' ),
				'desc'        => __( 'Choose device\'s color style', 'leafcolor' ),
				'std'         => 'silver',
				'type'        => 'radio-image',
				'condition'   => 'devide:is(iphone6)',
				'choices'     => array(
				  array(
					'value'       => 'silver',
					'label'       => __( 'Silver', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/white.png'
				  ),
				  array(
					'value'       => 'black',
					'label'       => __( 'Black', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/black.png'
				  ),
				  array(
					'value'       => 'gold',
					'label'       => __( 'Gold', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/gold.png'
				  )
				)
			),//end features 6 color
			//color
			array(
				'id'          => 'devide_color_iphone6plus',
				'label'       => __( 'Device color', 'leafcolor' ),
				'desc'        => __( 'Choose device\'s color style', 'leafcolor' ),
				'std'         => 'silver',
				'type'        => 'radio-image',
				'condition'   => 'devide:is(iphone6plus)',
				'choices'     => array(
				  array(
					'value'       => 'silver',
					'label'       => __( 'Silver', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/white.png'
				  ),
				  array(
					'value'       => 'black',
					'label'       => __( 'Black', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/black.png'
				  ),
				  array(
					'value'       => 'gold',
					'label'       => __( 'Gold', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/gold.png'
				  )
				)
			),//end features 6 color
			array(
				'id'          => 'devide_color_iphone5s',
				'label'       => __( 'Device color', 'leafcolor' ),
				'desc'        => __( 'Choose device\'s color style', 'leafcolor' ),
				'std'         => 'silver',
				'type'        => 'radio-image',
				'condition'   => 'devide:is(iphone5s)',
				'choices'     => array(
				  array(
					'value'       => 'silver',
					'label'       => __( 'Silver', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/white.png'
				  ),
				  array(
					'value'       => 'black',
					'label'       => __( 'Black', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/black.png'
				  ),
				  array(
					'value'       => 'gold',
					'label'       => __( 'Gold', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/gold.png'
				  )
				)
			),//end features 5s color
			array(
				'id'          => 'devide_color_iphone5c',
				'label'       => __( 'Device color', 'leafcolor' ),
				'desc'        => __( 'Choose device\'s color style', 'leafcolor' ),
				'std'         => 'green',
				'type'        => 'radio-image',
				'condition'   => 'devide:is(iphone5c)',
				'choices'     => array(
				  array(
					'value'       => 'green',
					'label'       => __( 'Green', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/green.png'
				  ),
				  array(
					'value'       => 'white',
					'label'       => __( 'White', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/white.png'
				  ),
				  array(
					'value'       => 'red',
					'label'       => __( 'Red', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/red.png'
				  ),
				  array(
					'value'       => 'yellow',
					'label'       => __( 'Yellow', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/yellow.png'
				  ),
				  array(
					'value'       => 'blue',
					'label'       => __( 'Blue', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/blue.png'
				  )
				)
			),//end features 5c color
			array(
				'id'          => 'devide_color_lumia920',
				'label'       => __( 'Device color', 'leafcolor' ),
				'desc'        => __( 'Choose device\'s color style', 'leafcolor' ),
				'std'         => 'yellow',
				'type'        => 'radio-image',
				'condition'   => 'devide:is(lumia920)',
				'choices'     => array(
				  array(
					'value'       => 'black',
					'label'       => __( 'Black', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/black.png'
				  ),
				  array(
					'value'       => 'white',
					'label'       => __( 'White', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/white.png'
				  ),
				  array(
					'value'       => 'yellow',
					'label'       => __( 'Yellow', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/yellow.png'
				  ),
				  array(
					'value'       => 'red',
					'label'       => __( 'Red', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/red.png'
				  ),
				  array(
					'value'       => 'blue',
					'label'       => __( 'Blue', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/blue.png'
				  )
				)
			),//end features lumia color
			array(
				'id'          => 'devide_color_ipadmini',
				'label'       => __( 'Device color', 'leafcolor' ),
				'desc'        => __( 'Choose device\'s color style', 'leafcolor' ),
				'std'         => 'silver',
				'type'        => 'radio-image',
				'condition'   => 'devide:is(ipadmini)',
				'choices'     => array(
				  array(
					'value'       => 'silver',
					'label'       => __( 'Silver', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/white.png'
				  ),
				  array(
					'value'       => 'black',
					'label'       => __( 'Black', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/black.png'
				  )
				)
			),//end features ipadmini color
			array(
				'id'          => 'devide_color_iphone4s',
				'label'       => __( 'Device color', 'leafcolor' ),
				'desc'        => __( 'Choose device\'s color style', 'leafcolor' ),
				'std'         => 'silver',
				'type'        => 'radio-image',
				'condition'   => 'devide:is(iphone4s)',
				'choices'     => array(
				  array(
					'value'       => 'silver',
					'label'       => __( 'Silver', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/white.png'
				  ),
				  array(
					'value'       => 'black',
					'label'       => __( 'Black', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/black.png'
				  )
				)
			),//end features 4s color
			array(
				'id'          => 'devide_color_galaxys5',
				'label'       => __( 'Device color', 'leafcolor' ),
				'desc'        => __( 'Choose device\'s color style', 'leafcolor' ),
				'std'         => 'white',
				'type'        => 'radio-image',
				'condition'   => 'devide:is(galaxys5)',
				'choices'     => array(
				  array(
					'value'       => 'white',
					'label'       => __( 'White', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/white.png'
				  ),
				  array(
					'value'       => 'black',
					'label'       => __( 'Black', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/black.png'
				  )
				)
			),//end features s5 color
			array(
				'id'          => 'orientation',
				'label'       => __( 'Device Screen Orientation', 'leafcolor' ),
				'desc'        => __( 'Not affect on some devices', 'leafcolor' ),
				'std'         => '',
				'type'        => 'radio-image',
				'condition'   => 'devide:not(def_themeoption),devide:not(def)',
				'operator'    => 'and',
				'choices'     => array(
				  array(
					'value'       => 0,
					'label'       => __( 'Portrait', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/orientation-1.png'
				  ),
				  array(
					'value'       => 1,
					'label'       => __( 'Landscape', 'leafcolor' ),
					'src'         => $theme_uri.'/images/options/orientation-2.png'
				  )
				)
			),//end orientation
			//appstore
			array(
			  'id'          => 'store-link-apple',
			  'label'       => __('<i class="fa fa-apple"></i> iOS Appstore URL','leafcolor'),
			  'desc'        => __('Enter Appstore URL if available','leafcolor'),
			  'std'         => '',
			  'type'        => 'text',
			  'class'       => '',
			  'choices'     => array()
			),
			array(
			  'id'          => 'store-link-google',
			  'label'       => __('<i class="fa fa-google"></i> Android Google Play Store URL','leafcolor'),
			  'desc'        => __('Enter Play Store URL if available','leafcolor'),
			  'std'         => '',
			  'type'        => 'text',
			  'class'       => '',
			  'choices'     => array()
			),
			array(
			  'id'          => 'store-link-windows',
			  'label'       => __('<i class="fa fa-windows"></i> Windows Phone Store URL','leafcolor'),
			  'desc'        => __('Enter Windows Phone Store URL if available','leafcolor'),
			  'std'         => '',
			  'type'        => 'text',
			  'class'       => '',
			  'choices'     => array()
			),
			array(
			  'id'          => 'app-port-file',
			  'label'       => __('Installation File','leafcolor'),
			  'desc'        => __('Upload or enter your app download link','leafcolor'),
			  'std'         => '',
			  'type'        => 'upload',
			  'class'       => '',
			  'choices'     => array()
			),
			array(
				'label'       => 'Custom Store Links',
				'id'          => 'app-custom-link',
				'type'        => 'list-item',
				'desc'        => __( 'Add Custom Store Links', 'leafcolor' ),
				'settings'    => array(
					array(
						'label'       => __( 'Icon', 'leafcolor' ),
						'id'          => 'icon',
						'type'        => 'text',
						'desc'        => __( 'Enter Font Awesome icon class (Ex: fa-apple)', 'leafcolor' ),
						'std'         => '',
					),
					array(
						'label'       => __( 'Download text', 'leafcolor' ),
						'id'          => 'download_text',
						'type'        => 'text',
						'desc'        => __( 'Ex: Download from', 'leafcolor' ),
						'std'         => 'Download from',
					),
					array(
						'label'       => __( 'URL', 'leafcolor' ),
						'id'          => 'url',
						'type'        => 'text',
						'desc'        => '',
					),
				),
			),//end custom link
			array(
			  'id'          => 'port-author-name',
			  'label'       => __('Author','leafcolor'),
			  'desc'        => __('App\'s Author name','leafcolor'),
			  'std'         => '',
			  'type'        => 'text',
			  'class'       => '',
			  'choices'     => array()
			),
			array(
			  'id'          => 'port-release',
			  'label'       => __('Release','leafcolor'),
			  'desc'        => __('Release Date','leafcolor'),
			  'std'         => '',
			  'type'        => 'date-picker',
			  'class'       => '',
			  'choices'     => array()
			),
			array(
			  'id'          => 'port-version',
			  'label'       => __('Version','leafcolor'),
			  'desc'        => __('Current App\'s Version','leafcolor'),
			  'std'         => '',
			  'type'        => 'text',
			  'class'       => '',
			  'choices'     => array()
			),
			array(
			  'id'          => 'port-requirement',
			  'label'       => __('Requirement','leafcolor'),
			  'desc'        => __('App\'s Requirement','leafcolor'),
			  'std'         => '',
			  'type'        => 'text',
			  'class'       => '',
			  'choices'     => array()
			),
			array(
				'label'       => 'Custom Meta',
				'id'          => 'app-custom-meta',
				'type'        => 'list-item',
				'desc'        => __( 'Add Custom Meta', 'leafcolor' ),
				'settings'    => array(
					array(
						'label'       => __( 'Icon', 'leafcolor' ),
						'id'          => 'icon',
						'type'        => 'text',
						'desc'        => __( 'Enter Font Awesome icon class (Ex: fa-check-square-o)', 'leafcolor' ),
						'std'         => '',
					),
					array(
						'label'       => __( 'Value', 'leafcolor' ),
						'id'          => 'value',
						'type'        => 'text',
						'desc'        => __( 'Enter value of meta', 'leafcolor' ),
						'std'         => '',
					),
				),
			),//end custom link
			array(
			  'id'          => 'custom-screenshot',
			  'label'       => __('Custom Screenshot Images','leafcolor'),
			  'desc'        => __('Enter custom app screenshots url, each image url per line','leafcolor'),
			  'std'         => '',
			  'type'        => 'textarea',
			  'class'       => '',
			  'choices'     => array()
			),			
		 )
		);
	  //layout
	  $theme_uri = get_template_directory_uri();
	  $meta_box2 = array(
		'id'        => 'product_layout',
		'title'     => 'Layout settings',
		'desc'      => '',
		'pages'     => array( 'product'),
		'context'   => 'normal',
		'priority'  => 'high',
		'fields'    => array(
			array(
			  'id'          => 'product-sidebar',
			  'label'       => __('Sidebar','leafcolor'),
			  'desc'        => __('Select "Default" to use settings in Theme Options','leafcolor'),
			  'std'         => '',
			  'type'        => 'radio-image',
			  'class'       => '',
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
			  'id'          => 'product-contpadding',
			  'label'       => __('Content Padding','leafcolor'),
			  'desc'        => __('Enable default top and bottom padding for content (30px)','leafcolor'),
			  'std'         => 'on',
			  'type'        => 'on-off',
			  'class'       => '',
			  'choices'     => array()
			),
			array(
			  'id'          => 'disable-woo',
			  'label'       => __('Disable Woocommerce Layout','leafcolor'),
			  'desc'        => __('Enable only if you want to build your own product content','leafcolor'),
			  'std'         => 'off',
			  'type'        => 'on-off',
			  'class'       => '',
			  'choices'     => array()
			),
			array(
			  'id'          => 'product-mode',
			  'label'       => __('Product style','leafcolor'),
			  'desc'        => __('Select "Default" to use settings in Theme Options','leafcolor'),
			  'std'         => '',
			  'type'        => 'select',
			  'class'       => '',
			  'choices'     => array(
				 array(
					'value'       => '',
					'label'       => 'Default',
					'src'         => ''
				  ),
				  array(
					'value'       => '1',
					'label'       => 'Woocommerce Product',
					'src'         => ''
				  ),
				  array(
					'value'       => 'on',
					'label'       => 'Listing App',
					'src'         => ''
				  ),
			   )
			),
		 )
		);
	  $meta_box4 = array(
		'id'        => 'port_app_screenshots',
		'title'     => 'App screenshots',
		'desc'      => '',
		'pages'     => array('app_portfolio'  ),
		'context'   => 'normal',
		'priority'  => 'high',
		'fields'    => array(
				  array(
					  'label'       => __( 'Screen Image', 'leafcolor' ),
					  'id'          => 'app_screen_image',
					  'type'        => 'gallery',
					  'desc'        => '',
					  'std'         => '',
					  'rows'        => '',
					  'post_type'   => '',
					  'taxonomy'    => '',
					  'choices'     => array(),
				  ),//end screen image
					
		 )
		);

	  $meta_box3 = array(
		'id'        => 'port_layout',
		'title'     => 'Layout settings',
		'desc'      => '',
		'pages'     => array('app_portfolio'  ),
		'context'   => 'normal',
		'priority'  => 'high',
		'fields'    => array(
			array(
			  'id'          => 'port_sidebar',
			  'label'       => __('Sidebar','leafcolor'),
			  'desc'        => __('Select "Default" to use settings in Theme Options','leafcolor'),
			  'std'         => '',
			  'type'        => 'radio-image',
			  'class'       => '',
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
			  'id'          => 'port-ctpadding',
			  'label'       => __('Content Padding','leafcolor'),
			  'desc'        => __('Enable default top and bottom padding for content (30px)','leafcolor'),
			  'std'         => 'on',
			  'type'        => 'on-off',
			  'class'       => '',
			  'choices'     => array()
			),
		 )
		);
		$meta_auto_fetch = array(
			'id'        => 'standard',
			'title'     => 'Auto Fetch Data from itunes store',
			'desc'      => '',
			'pages'     => array('product'  ),
			'context'   => 'side',
			'priority'  => 'high',
			'fields'    => array(
				array(
				  'id'          => 'fetch_data_itunes',
				  'label'       => __('<strong>Enable Auto Fetch</strong>','leafcolor'),
				  'desc' => __('Turn-off here if you do not want to auto-fetch data after save/edit','leafcolor'),
				  'std'         => 'on',
				  'type'        => 'on-off',
				  'class'       => '',
				  'choices'     => array()
				),
			)
		);
	  
	  if (function_exists('ot_register_meta_box')) {
		  ot_register_meta_box( $meta_box_app );
		  ot_register_meta_box( $meta_box2 );
		  ot_register_meta_box( $meta_box4 );
		  ot_register_meta_box( $meta_box3 );
		  ot_register_meta_box( $meta_auto_fetch );
	  }
	}
}
?>