<?php
/**
 * Option Tree integration ===========
 */
 /**
 * Optional: set 'ot_show_pages' filter to false.
 * This will hide the settings & documentation pages.
 */
add_filter( 'ot_show_pages', '__return_true' );

/**
 * Optional: set 'ot_show_new_layout' filter to false.
 * This will hide the "New Layout" section on the Theme Options page.
 */
add_filter( 'ot_show_new_layout', '__return_false' );

/**
 * Required: set 'ot_theme_mode' filter to true.
 */
add_filter( 'ot_theme_mode', '__return_true' );

/**
 * Required: include OptionTree Framework.
 */
load_template( trailingslashit( get_template_directory() ) . '/inc/option-tree/ot-loader.php' );
/** 
 * End Option Tree integration ===============================
 * To get options, use this code
 * $test_input = ot_get_option( 'test_input', 'default value');
 * $test_array = ot_get_option( 'test_array', array('value 1','value 2')); or 
 * $test_array = ot_get_option( 'test_array', array());
 */ 
 
if(!class_exists('Mobile_Detect')){
	require_once locate_template('/inc/starter/mobile-detect.php');
}
$detect = new Mobile_Detect;
global $_device_, $_device_name_, $__check_retina;
$_device_ = $detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'mobile') : 'pc';
$_device_name_ = $detect->mobileGrade();
$__check_retina = $detect->isRetina();
//Menu Walker
require_once locate_template('/inc/starter/leaf-menu-walker.php');

//Metadata boxes
require_once locate_template('/inc/meta/meta-boxes.php');


add_action( 'tgmpa_register', 'ia_acplugins' );
function ia_acplugins($plugins) {
	
	global $__required_plugins;
	
    /**
     * Array of configuration settings. Amend each line as needed.
     * If you want the default strings to be available under your own theme domain,
     * leave the strings uncommented.
     * Some of the strings are added into a sprintf, so see the comments at the
     * end of each line for what each argument will be.
     */
    $config = array(
        'domain'            => 'leafcolor',           // Text domain - likely want to be the same as your theme.
        'default_path'      => '',                           // Default absolute path to pre-packaged plugins
        'parent_slug'   	=> 'themes.php',         // Default parent URL slug
        'menu'              => 'install-required-plugins',   // Menu slug
        'has_notices'       => true,                         // Show admin notices or not
        'is_automatic'      => false,            // Automatically activate plugins after installation or not
        'message'           => '',               // Message to output right before the plugins table
        'strings'           => array(
            'page_title'                                => __( 'Install Required &amp; Recommended Plugins', 'leafcolor' ),
            'menu_title'                                => __( 'Install Plugins', 'leafcolor' ),
            'installing'                                => __( 'Installing Plugin: %s', 'leafcolor' ), // %1$s = plugin name
            'oops'                                      => __( 'Something went wrong with the plugin API.', 'leafcolor' ),
            'notice_can_install_required'               => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.', 'leafcolor' ), // %1$s = plugin name(s)
            'notice_can_install_recommended'            => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.', 'leafcolor' ), // %1$s = plugin name(s)
            'notice_cannot_install'                     => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'leafcolor' ), // %1$s = plugin name(s)
            'notice_can_activate_required'              => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'leafcolor' ), // %1$s = plugin name(s)
            'notice_can_activate_recommended'           => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'leafcolor' ), // %1$s = plugin name(s)
            'notice_cannot_activate'                    => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'leafcolor' ), // %1$s = plugin name(s)
            'notice_ask_to_update'                      => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'leafcolor' ), // %1$s = plugin name(s)
            'notice_cannot_update'                      => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'leafcolor' ), // %1$s = plugin name(s)
            'install_link'                              => _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'leafcolor' ),
            'activate_link'                             => _n_noop( 'Activate installed plugin', 'Activate installed plugins', 'leafcolor' ),
            'return'                                    => __( 'Return to Required Plugins Installer', 'leafcolor' ),
            'plugin_activated'                          => __( 'Plugin activated successfully.', 'leafcolor' ),
            'complete'                                  => __( 'All plugins installed and activated successfully. %s', 'leafcolor' ) // %1$s = dashboard link
        )
    );
 
    tgmpa( $__required_plugins, $config);
}