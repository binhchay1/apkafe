<?php
add_action('admin_init', 'custom_theme_options');
function custom_theme_options()
{
  $saved_settings = get_option('option_tree_settings', array());
  $theme_uri = get_template_directory_uri();
  $custom_settings = array(
    'contextual_help' => array(
      'sidebar'       => ''
    ),
    'sections'        => array(
      array(
        'id'          => 'general',
        'title'       => '<i class="fa fa-cogs"><!-- --></i>General'
      ),
      array(
        'id'          => 'color',
        'title'       => '<i class="fa fa-magic"><!-- --></i>Colors & Background'
      ),
      array(
        'id'          => 'fonts',
        'title'       => '<i class="fa fa-font"><!-- --></i>Fonts'
      ),
      array(
        'id'          => 'nav',
        'title'       => '<i class="fa fa-bars"><!-- --></i>Main Navigation'
      ),
      array(
        'id'          => 'single_post',
        'title'       => '<i class="fa fa-file-text-o"><!-- --></i>Single Post'
      ),
      array(
        'id'          => 'single_page',
        'title'       => '<i class="fa fa-file"><!-- --></i>Single Page'
      ),
      array(
        'id'          => 'archive',
        'title'       => '<i class="fa fa-pencil-square"><!-- --></i>Archives'
      ),
      array(
        'id'          => '404',
        'title'       => '<i class="fa fa-exclamation-triangle"><!-- --></i>404'
      ),
      array(
        'id'          => 'portfolio',
        'title'       => '<i class="fa fa-suitcase "><!-- --></i>App Portfolio'
      ),
      array(
        'id'          => 'social_account',
        'title'       => '<i class="fa fa-twitter-square"><!-- --></i>Social Accounts'
      ),
      array(
        'id'          => 'social_share',
        'title'       => '<i class="fa fa-share-square"><!-- --></i>Social Sharing'
      ),
    ),
    'settings'        => array(
      array(
        'id'          => 'app-theme-style',
        'label'       => 'Theme Style',
        'desc'        => '',
        'std'         => '1',
        'type'        => 'select',
        'section'     => 'general',
        'rows'        => '',
        'choices'     => array(
          array(
            'value'       => '1',
            'label'       => 'Mordern',
            'src'         => ''
          ),
          array(
            'value'       => '2',
            'label'       => 'Classic',
            'src'         => ''
          )
        ),
      ),
      array(
        'id'          => 'fixed_footer',
        'label'       => 'Enable Fixed Footer Effect',
        'desc'        => 'Please disable if your footer contains too much content',
        'std'         => 'on',
        'type'        => 'on-off',
        'section'     => 'general',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'copyright',
        'label'       => 'Copyright Text',
        'desc'        => 'Appear in footer',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'general',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'right_to_left',
        'label'       => 'RTL mode',
        'desc'        => '',
        'std'         => '',
        'type'        => 'checkbox',
        'section'     => 'general',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
        'choices'     => array(
          array(
            'value'       => '1',
            'label'       => 'Enable RTL',
            'src'         => ''
          )
        ),
      ),
      array(
        'id'          => 'custom_css',
        'label'       => 'Custom CSS',
        'desc'        => 'Enter custom CSS. Ex: <i>.class{ font-size: 13px; }</i>',
        'std'         => '',
        'type'        => 'textarea-simple',
        'section'     => 'general',
        'rows'        => '5',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'google_analytics_code',
        'label'       => 'Custom Code',
        'desc'        => 'Enter custom code or JS code here. For example, enter Google Analytics',
        'std'         => '',
        'type'        => 'textarea-simple',
        'section'     => 'general',
        'rows'        => '5',
        'post_type'   => '',
        'taxonomy'    => '',
        'class'       => ''
      ),
      array(
        'id'          => 'favicon',
        'label'       => 'Favicon',
        'desc'        => 'Upload favicon (.ico)',
        'std'         => '',
        'type'        => 'upload',
        'section'     => 'general',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'class'       => ''
      ),
      array(
        'id'          => 'logo_image',
        'label'       => 'Logo Image',
        'desc'        => 'Upload your logo image',
        'std'         => '',
        'type'        => 'upload',
        'section'     => 'general',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'class'       => ''
      ),
      array(
        'id'          => 'retina_logo',
        'label'       => 'Retina Logo (optional)',
        'desc'        => 'Retina logo should be two time bigger than the custom logo. Retina Logo is optional, use this setting if you want to strictly support retina devices.',
        'std'         => '',
        'type'        => 'upload',
        'section'     => 'general',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'class'       => ''
      ),
      array(
        'id'          => 'login_logo',
        'label'       => 'Login Logo Image',
        'desc'        => 'Upload your Admin Login logo image',
        'std'         => '',
        'type'        => 'upload',
        'section'     => 'general',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'class'       => ''
      ),
      array(
        'id'          => 'off_gototop',
        'label'       => 'Scroll Top button',
        'desc'        => 'Enable Scroll Top button',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'general',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'pre-loading',
        'label'       => 'Pre-loading Effect',
        'desc'        => 'Enable Pre-loading Effect',
        'std'         => '2',
        'type'        => 'select',
        'section'     => 'general',
        'rows'        => '',
        'choices'     => array(
          array(
            'value'       => '-1',
            'label'       => 'Disable',
            'src'         => ''
          ),
          array(
            'value'       => '1',
            'label'       => 'Enable',
            'src'         => ''
          ),
          array(
            'value'       => '2',
            'label'       => 'Enable for Homepage Only',
            'src'         => ''
          )
        ),
      ),
      array(
        'id'          => 'loading_bg',
        'label'       => 'Pre-Loading Background Color',
        'desc'        => 'Default is Black',
        'std'         => '',
        'type'        => 'colorpicker',
        'section'     => 'general',
      ),
      array(
        'id'          => 'loading_spin_color',
        'label'       => 'Pre-Loading Spinners Color',
        'desc'        => 'Default is White',
        'std'         => '',
        'type'        => 'colorpicker',
        'section'     => 'general',
      ),
      //color
      array(
        'id'          => 'main_color_1',
        'label'       => 'Main color',
        'desc'        => 'Choose Main color (Default is #39ba93)',
        'std'         => '',
        'type'        => 'colorpicker',
        'section'     => 'color',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'footer_bg',
        'label'       => 'Footer Background Color',
        'desc'        => 'Choose Footer background color (Default is Main color)',
        'std'         => '',
        'type'        => 'colorpicker',
        'section'     => 'color',
      ),
      array(
        'id'          => 'heading_bg',
        'label'       => 'Page Heading Background',
        'desc'        => 'Choose Page Heading background (Default is Main color)',
        'std'         => '',
        'type'        => 'background',
        'section'     => 'color',
      ),
      //font
      array(
        'id'          => 'main_font',
        'label'       => 'Main Font Family',
        'desc'        => 'Enter font-family name here. <a href="http://www.google.com/fonts/" target="_blank">Google Fonts</a> are supported. For example, if you choose "Source Code Pro" Google Font with font-weight 400,500,600, enter <i>Source Code Pro:400,500,600</i>',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'fonts',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'heading_font',
        'label'       => 'Heading Font Family',
        'desc'        => 'Enter font-family name here. <a href="http://www.google.com/fonts/" target="_blank">Google Fonts</a> are supported. For example, if you choose "Source Code Pro" Google Font with font-weight 400,500,600, enter <i>Source Code Pro:400,500,600</i> (Only few heading texts are affected)',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'fonts',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'main_size',
        'label'       => 'Main Font Size',
        'desc'        => 'Select base font size (px)',
        'std'         => '13',
        'type'        => 'numeric-slider',
        'section'     => 'fonts',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '10,18,1',
        'class'       => ''
      ),
      array(
        'id'          => 'custom_font_1',
        'label'       => 'Upload Custom Font 1',
        'desc'        => 'Upload your own font and enter name "custom-font-1" in "Main Font Family" or "Heading Font Family" setting above',
        'std'         => '',
        'type'        => 'upload',
        'section'     => 'fonts',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'custom_font_2',
        'label'       => 'Upload Custom Font 2',
        'desc'        => 'Upload your own font and enter name "custom-font-2" in "Main Font Family" or "Heading Font Family" setting above',
        'std'         => '',
        'type'        => 'upload',
        'section'     => 'fonts',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      //nav
      array(
        'id'          => 'nav_style',
        'label'       => 'Main Navigation Style',
        'desc'        => 'Choose Main Navigation Style',
        'std'         => '',
        'type'        => 'select',
        'section'     => 'nav',
        'min_max_step' => '',
        'choices'     => array(
          array(
            'value'       => '0',
            'label'       => 'Default',
          ),
          array(
            'value'       => '1',
            'label'       => 'Off Canvas',
          ),
        ),
      ),
      array(
        'id'          => 'nav_schema',
        'label'       => 'Main Navigation Schema',
        'desc'        => 'Choose Main Navigation color schema',
        'std'         => '',
        'type'        => 'select',
        'section'     => 'nav',
        'min_max_step' => '',
        'choices'     => array(
          array(
            'value'       => '0',
            'label'       => 'Dark',
          ),
          array(
            'value'       => '1',
            'label'       => 'Light',
          ),
        ),
      ),
      array(
        'id'          => 'nav_bg',
        'label'       => 'Main Navigation Background Color',
        'desc'        => 'Choose Main Navigation background color',
        'std'         => '',
        'type'        => 'colorpicker',
        'section'     => 'nav',
      ),
      array(
        'id'          => 'nav_opacity',
        'label'       => 'Main Navigation Background Opacity',
        'desc'        => 'Choose Main Navigation background opacity (%)',
        'std'         => '100',
        'type'        => 'numeric-slider',
        'section'     => 'nav',
        'min_max_step' => '0,100,5',
      ),
      array(
        'id'          => 'nav_sticky',
        'label'       => 'Sticky Navigation',
        'desc'        => 'Choose to Enable Sticky Navigation',
        'std'         => 'on',
        'type'        => 'on-off',
        'section'     => 'nav',
      ),
      array(
        'id'          => 'sticky_logo_image',
        'label'       => 'Sticky Logo',
        'desc'        => 'Upload your logo image',
        'std'         => '',
        'type'        => 'upload',
        'section'     => 'nav',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'class'       => ''
      ),
      array(
        'id'          => 'enable_search',
        'label'       => 'Enable Search',
        'desc'        => 'Enable or disable default search button on Navigation',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'nav',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'nav_des',
        'label'       => 'Enable Menu Item Description',
        'desc'        => 'Choose to display Menu Items Description',
        'std'         => 'on',
        'type'        => 'on-off',
        'section'     => 'nav',
      ),
      //single post
      array(
        'id'          => 'post_layout',
        'label'       => 'Sidebar Layout',
        'desc'        => 'Select Sidebar Layout (Right, Left or Fullwidth)',
        'std'         => '',
        'type'        => 'radio-image',
        'section'     => 'single_post',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
        'choices'     => array(
          array(
            'value'       => 'right',
            'label'       => 'Sidebar Right',
            'src'         => $theme_uri . '/images/options/layout-right.png'
          ),
          array(
            'value'       => 'left',
            'label'       => 'Sidebar Left',
            'src'         => $theme_uri . '/images/options/layout-left.png'
          ),
          array(
            'value'       => 'full',
            'label'       => 'Hidden',
            'src'         => $theme_uri . '/images/options/layout-full.png'
          ),
        ),
      ),
      array(
        'id'          => 'enable_author',
        'label'       => 'Author',
        'desc'        => 'Enable Author info',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'single_post',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'enable_author_info',
        'label'       => 'About Author',
        'desc'        => 'Enable About Author info',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'single_post',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'single_published_date',
        'label'       => 'Published Date',
        'desc'        => 'Enable Published Date info',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'single_post',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'single_categories',
        'label'       => 'Categories',
        'desc'        => 'Enable Categories info',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'single_post',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'single_tags',
        'label'       => 'Tags',
        'desc'        => 'Enable Categories info',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'single_post',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'single_cm_count',
        'label'       => 'Comment Count',
        'desc'        => 'Enable Comment Count Info',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'single_post',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'single_navi',
        'label'       => 'Post Navigation',
        'desc'        => 'Enable Post Navigation',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'single_post',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),

      array(
        'id'          => 'page_layout',
        'label'       => 'Sidebar Layout',
        'desc'        => 'Select Sidebar Layout (Right, Left or Fullwidth)',
        'std'         => '',
        'type'        => 'radio-image',
        'section'     => 'single_page',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
        'choices'     => array(
          array(
            'value'       => 'right',
            'label'       => 'Sidebar Right',
            'src'         => $theme_uri . '/images/options/layout-right.png'
          ),
          array(
            'value'       => 'left',
            'label'       => 'Sidebar Left',
            'src'         => $theme_uri . '/images/options/layout-left.png'
          ),
          array(
            'value'       => 'full',
            'label'       => 'Hidden',
            'src'         => $theme_uri . '/images/options/layout-full.png'
          ),
        ),
      ),
      array(
        'id'          => 'archive_sidebar',
        'label'       => 'Sidebar Layout',
        'desc'        => 'Select Sidebar position for Archive pages',
        'std'         => '',
        'type'        => 'radio-image',
        'section'     => 'archive',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
        'choices'     => array(
          array(
            'value'       => 'right',
            'label'       => 'Sidebar Right',
            'src'         => $theme_uri . '/images/options/layout-right.png'
          ),
          array(
            'value'       => 'left',
            'label'       => 'Sidebar Left',
            'src'         => $theme_uri . '/images/options/layout-left.png'
          ),
          array(
            'value'       => 'full',
            'label'       => 'Hidden',
            'src'         => $theme_uri . '/images/options/layout-full.png'
          ),
        ),
      ),
      array(
        'id'          => 'listing_style',
        'label'       => 'Archive Post Listing Style',
        'desc'        => 'Select Post Listing Style for Archive pages (Quick Ajax need to be installed)',
        'std'         => '',
        'type'        => 'select',
        'section'     => 'archive',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
        'choices'     => array(
          array(
            'value'       => '0',
            'label'       => 'Default',
          ),
          array(
            'value'       => 'ajax',
            'label'       => 'Quick Ajax',
          )
        ),
      ),
      array(
        'id'          => 'page404_title',
        'label'       => 'Page Title',
        'desc'        => '',
        'std'         => '',
        'type'        => 'text',
        'section'     => '404',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'page404_content',
        'label'       => 'Page Content',
        'desc'        => '',
        'std'         => '',
        'type'        => 'textarea',
        'section'     => '404',
        'rows'        => '8',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'page404_search',
        'label'       => 'Search Form',
        'desc'        => 'Enable Search Form in 404 page',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => '404',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),

      array(
        'id'          => 'portfolio_layout',
        'label'       => 'App Portfolio Page Layout',
        'desc'        => 'Select default layout of single portfolio pages',
        'std'         => '',
        'type'        => 'radio-image',
        'section'     => 'portfolio',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
        'choices'     => array(
          array(
            'value'       => 'right',
            'label'       => 'Sidebar Right',
            'src'         => $theme_uri . '/images/options/layout-right.png'
          ),
          array(
            'value'       => 'left',
            'label'       => 'Sidebar Left',
            'src'         => $theme_uri . '/images/options/layout-left.png'
          ),
          array(
            'value'       => 'full',
            'label'       => 'Hidden',
            'src'         => $theme_uri . '/images/options/layout-full.png'
          ),
        ),
      ),
      array(
        'id'          => 'portfolio_slug',
        'label'       => 'App Portfolio Slug',
        'desc'        => 'Change portfolio slug. Remember to save the permalink settings again in Settings > Permalinks',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'portfolio',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'port_enable_author',
        'label'       => 'Author',
        'desc'        => 'Enable Author info',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'portfolio',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'port_author_info',
        'label'       => 'About Author',
        'desc'        => 'Enable About Author info',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'portfolio',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'port_published_date',
        'label'       => 'Published Date',
        'desc'        => 'Enable Published Date info',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'portfolio',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'port_categories',
        'label'       => 'Categories',
        'desc'        => 'Enable Categories info',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'portfolio',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'port_single_tags',
        'label'       => 'Tags',
        'desc'        => 'Enable Categories info',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'portfolio',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'port_cm_count',
        'label'       => 'Comment Count',
        'desc'        => 'Enable Comment Count Info',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'portfolio',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),






      array(
        'id'          => 'acc_facebook',
        'label'       => 'Facebook',
        'desc'        => 'Enter full link to your account (including http://)',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'social_account',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'acc_twitter',
        'label'       => 'Twitter',
        'desc'        => 'Enter full link to your account (including http://)',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'social_account',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'acc_linkedin',
        'label'       => 'LinkedIn',
        'desc'        => 'Enter full link to your account (including http://)',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'social_account',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'acc_tumblr',
        'label'       => 'Tumblr',
        'desc'        => 'Enter full link to your account (including http://)',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'social_account',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'acc_google-plus',
        'label'       => 'Google Plus',
        'desc'        => 'Enter full link to your account (including http://)',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'social_account',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'acc_pinterest',
        'label'       => 'Pinterest',
        'desc'        => 'Enter full link to your account (including http://)',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'social_account',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'acc_youtube',
        'label'       => 'Youtube',
        'desc'        => 'Enter full link to your account (including http://)',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'social_account',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'acc_flickr',
        'label'       => 'Flickr',
        'desc'        => 'Enter full link to your account (including http://)',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'social_account',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'label'       => 'Custom Social Account',
        'id'          => 'custom_acc',
        'type'        => 'list-item',
        'class'       => '',
        'section'     => 'social_account',
        'desc'        => 'Add Social Account',
        'choices'     => array(),
        'settings'    => array(
          array(
            'label'       => 'Icon Font Awesome',
            'id'          => 'icon',
            'type'        => 'text',
            'desc'        => 'Enter Font Awesome class (Ex: fa-facebook)',
            'std'         => '',
            'rows'        => '',
            'post_type'   => '',
            'taxonomy'    => ''
          ),
          array(
            'label'       => 'URL',
            'id'          => 'link',
            'type'        => 'text',
            'desc'        => 'Enter full link to your account (including http://)',
            'std'         => '',
            'rows'        => '',
            'post_type'   => '',
            'taxonomy'    => ''
          ),
        )
      ),
      array(
        'id'          => 'social_link_open',
        'label'       => 'Open Social link in new tab?',
        'desc'        => '',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'social_account',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'share_facebook',
        'label'       => 'Facebook Share',
        'desc'        => 'Enable Facebook Share button',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'social_share',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'share_twitter',
        'label'       => 'Twitter Share',
        'desc'        => 'Enable Twitter Tweet button',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'social_share',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'share_linkedin',
        'label'       => 'LinkedIn Share',
        'desc'        => 'Enable LinkedIn Share button',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'social_share',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'share_tumblr',
        'label'       => 'Tumblr Share',
        'desc'        => 'Enable Tumblr Share button',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'social_share',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'share_google_plus',
        'label'       => 'Google+ Share',
        'desc'        => 'Enable Google+ Share button',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'social_share',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'share_pinterest',
        'label'       => 'Pinterest Share',
        'desc'        => 'Enable Pinterest Pin button',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'social_share',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'share_email',
        'label'       => 'Email Share',
        'desc'        => 'Enable Email button',
        'std'         => '',
        'type'        => 'on-off',
        'section'     => 'social_share',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),

      array(
        'id'          => 'user_submit_status',
        'label'       => 'Post status for submitted app',
        'desc'        => '',
        'std'         => '',
        'type'        => 'select',
        'section'     => 'user_submit',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
        'choices'     => array(
          array(
            'value'       => 'pending',
            'label'       => 'Pending',
            'src'         => ''
          ),
          array(
            'value'       => 'publish',
            'label'       => 'Publish',
            'src'         => ''
          )
        ),
      ),
      array(
        'id'          => 'user_submit_cat_exclude',
        'label'       => 'Exclude Category from Category checkbox',
        'desc'        => 'Enter category ID that you dont want to be display in category checkbox (ex: 1,68,86)',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'user_submit',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
        'choices'     => array(),
      ),
      array(
        'id'          => 'user_submit_fetch',
        'label'       => 'Enable Auto Fetch Data for user\'s submited app',
        'desc'        => 'Auto fill title, description, image ... for Google Play, Itunes app url',
        'std'         => '',
        'type'        => 'select',
        'section'     => 'user_submit',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
        'choices'     => array(
          array(
            'value'       => 0,
            'label'       => 'Disable',
            'src'         => ''
          ),
          array(
            'value'       => 1,
            'label'       => 'Enable',
            'src'         => ''
          )
        ),
      ),

    )
  );

  $custom_settings = apply_filters('option_tree_settings_args', $custom_settings);
  if ($saved_settings !== $custom_settings) {
    update_option('option_tree_settings', $custom_settings);
  }
}
