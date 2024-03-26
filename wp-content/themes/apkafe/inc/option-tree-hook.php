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
        'id'          => 'menu',
        'title'       => '<i class="fa fa-bars"><!-- --></i>Menu'
      ),
      array(
        'id'          => 'social_share',
        'title'       => '<i class="fa fa-share-square"><!-- --></i>Social Sharing'
      ),
    ),
    'settings'        => array(
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
        'id'          => 'custom_script_header',
        'label'       => 'Custom scripts header',
        'desc'        => 'Enter custom code CSS or JS in header. Ex: <i>.body{ font-size: 13px; }</i>',
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
        'id'          => 'custom_script_footer',
        'label'       => 'Custom script footer',
        'desc'        => 'Enter custom code or JS in footer. For example, enter Google Analytics',
        'std'         => '',
        'type'        => 'textarea-simple',
        'section'     => 'general',
        'rows'        => '5',
        'post_type'   => '',
        'taxonomy'    => '',
        'class'       => ''
      ),
      array(
        'id'          => 'menu_header',
        'label'       => 'Menu header',
        'desc'        => 'Create for list menu items in header',
        'std'         => '',
        'type'        => 'list-item',
        'section'     => 'menu',
        'rows'        => '5',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'menu_footer',
        'label'       => 'Menu footer',
        'desc'        => 'Create for list menu items in footer',
        'std'         => '',
        'type'        => 'list-item',
        'section'     => 'menu',
        'rows'        => '5',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => ''
      ),
      array(
        'id'          => 'share_facebook',
        'label'       => 'Facebook Share',
        'desc'        => 'Paste Facebook share link',
        'std'         => '',
        'type'        => 'text',
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
        'desc'        => 'Paste Twitter share link',
        'std'         => '',
        'type'        => 'text',
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
        'desc'        => 'Paste LinkedIn share link',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'social_share',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
      array(
        'id'          => 'share_youtube',
        'label'       => 'Youtube Share',
        'desc'        => 'Paste Youtube share link',
        'std'         => '',
        'type'        => 'text',
        'section'     => 'social_share',
        'rows'        => '',
        'post_type'   => '',
        'taxonomy'    => '',
        'min_max_step' => '',
        'class'       => '',
      ),
    )
  );

  $custom_settings = apply_filters('option_tree_settings_args', $custom_settings);
  if ($saved_settings !== $custom_settings) {
    update_option('option_tree_settings', $custom_settings);
  }
}
