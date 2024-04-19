<?php
/**
 * Plugin Name: Lasso
 * Plugin URI: https://getlasso.co

 * Description: Lasso lets you add, manage, and beautifully display affiliate links from any network, including Amazon Associates and more.

 * Author: Lasso
 * Author URI: https://getlasso.co

 * Version: 324

 * Text Domain: lasso-urls
 * Domain Path: /languages

 * License: GNU General Public License v2.0 (or later)
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 *
 * @package Lasso
 */

use Lasso\Classes\Deactivator as Lasso_Deactivator;
use Lasso\Classes\Activator as Lasso_Activator;

// ? ==============================================================================================
// ? WE SHOULD UPDATE THE VERSION NUMBER HERE AS WELL WHEN RELEASING A NEW VERSION
define( 'LASSO_VERSION', '324' );
// ? ==============================================================================================





// ? If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// ? Lasso contants definition
define( 'LASSO_PLUGIN_MAIN_FILE', __FILE__ );
define( 'LASSO_PLUGIN_PATH', __DIR__ );
define( 'LASSO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LASSO_PLUGIN_BASE_NAME', plugin_basename( __FILE__ ) );
require_once LASSO_PLUGIN_PATH . '/admin/lasso-constant.php';
require_once LASSO_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'autoload.php';

// ? Show warning message about PHP compatibility
/**
// $get = wp_unslash( $_GET ); // phpcs:ignore
// if ( (float) PHP_VERSION < 7.2 && isset( $get['post_type'] ) && LASSO_POST_TYPE === $get['post_type'] ) {
// require_once LASSO_PLUGIN_PATH . '/admin/views/settings/php-compatibility.php';
// wp_die();
// }
*/

// ? Polyfill declaration. Make lasso to be compatible with PHP8.
require_once LASSO_PLUGIN_PATH . '/libs/lasso/lasso-polyfill.php';

// ? Sentry declaration
require_once LASSO_PLUGIN_PATH . '/libs/lasso/lasso-sentry.php';

// ? Plugin activate
/**
 * Do something when activate Lasso
 */
function activate_lasso_urls() {
	$lasso_activator = new Lasso_Activator();
	$lasso_activator->init();
}

// ? Plugin Deactivate
/**
 * Do something when deactivate Lasso
 */
function deactivate_lasso_urls() {
	$lasso_deactivator = new Lasso_Deactivator();
	$lasso_deactivator->init();
}

register_activation_hook( __FILE__, 'activate_lasso_urls' );
register_deactivation_hook( __FILE__, 'deactivate_lasso_urls' );

// ? Add custom post type: Lasso URLs and load other classes
require_once LASSO_PLUGIN_PATH . '/classes/class-lasso-init.php';
new Lasso_Init();

// ? Process for other plugin
require_once LASSO_PLUGIN_PATH . '/libs/lasso/process-other-plugin.php';

// ? Make this plugin loaded after the other plugins
add_action( 'activated_plugin', 'lasso_load_final' );
/**
 * Check and change the order of plugins.
 */
function lasso_load_final() {
	$path    = LASSO_PLUGIN_BASE_NAME;
	$plugins = get_option( 'active_plugins' );
	if ( $plugins ) {
		$key = array_search( $path, $plugins, true );
		if ( false !== $key ) {
			array_splice( $plugins, $key, 1 );
			array_push( $plugins, $path );
			update_option( 'active_plugins', $plugins );
		}
	}
}

// ? check update
$lasso_update_checker = Lasso_Puc_v4_Factory::buildUpdateChecker(
	Lasso_License::get_plugin_update_url(),
	LASSO_PLUGIN_MAIN_FILE, // ? Full path to the main plugin file or functions.php.
	'affiliate-plugin',
	24 // phpcs:ignore hour(s)
);

do_action( 'lasso_loaded' );
