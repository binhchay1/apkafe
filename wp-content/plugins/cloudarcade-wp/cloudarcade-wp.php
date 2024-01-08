<?php

/**
 * Plugin Name:       CloudArcade WP
 * Plugin URI:        https://cloudarcade.net
 * Description:       Seamlessly import and sync your CloudArcade CMS's game library with your WordPress site, enriching visitor experience with a diverse range of interactive HTML5 games.
 * Version:           1.0.0
 * Author:            CloudArcade
 * Author URI:        https://cloudarcade.net
 * License:           Proprietary
 * License URI:       https://cloudarcade.net
 * Text Domain:       cloudarcade-wp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CLOUDARCADE_WP_VERSION', '1.0.0' );


/**
 * pugin prefix
 */
define( 'CLOUDARCADE_PREFIX', 'CAWP'  );
/**
 * textdomain
 */
define( 'CA_TEXTDOMAIN', 'cloudarcade-wp'  );

/**
 * used for reedited code as link to root path
 */
define( 'CLOUDARCADE_WP_ROOT', plugin_dir_path( __FILE__ )  );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cloudarcade-wp-activator.php
 */
register_activation_hook( __FILE__, 'activate_cloudarcade_wp' );
function activate_cloudarcade_wp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cloudarcade-wp-activator.php';
	Cloudarcade_Wp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cloudarcade-wp-deactivator.php
 */
register_deactivation_hook( __FILE__, 'deactivate_cloudarcade_wp' );
function deactivate_cloudarcade_wp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cloudarcade-wp-deactivator.php';
	Cloudarcade_Wp_Deactivator::deactivate();
}

/**
 * add settings class to wp
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cloudarcade-wp-settings-api.php';

/**
 * add common functions to wp
 */
if (is_admin()) {
    require plugin_dir_path( __FILE__ ) . 'includes/d-cawp-admin.php';
}
require plugin_dir_path( __FILE__ ) . 'includes/common-functions.php';
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cloudarcade-wp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cloudarcade_wp() {
	$plugin = new Cloudarcade_Wp();
	$plugin->run();
}
run_cloudarcade_wp();


/**
 * run tpl manipulations
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cloudarcade-wp-tpl-manipulations.php';
new Cloudarcade_Wp_Tpl_Manipulations();



/**
 * run content manipulations
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cloudarcade-wp-content-manipulations.php';
new Cloudarcade_Wp_Content_Manipulations();
