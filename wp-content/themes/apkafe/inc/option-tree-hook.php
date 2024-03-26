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
    )
  );

  $custom_settings = apply_filters('option_tree_settings_args', $custom_settings);
  if ($saved_settings !== $custom_settings) {
    update_option('option_tree_settings', $custom_settings);
  }
}
