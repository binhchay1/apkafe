<?php
/**
 * Plugin Name: HT Easy GA4
 * Description: Start tracking your website usage data by using Google Analytics 4.
 * Author:      HasThemes
 * Author URI:  https://hasthemes.com/
 * Version:     1.2.6
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ht-easy-ga4
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}
define( 'HT_EASY_GA4_VERSION', '1.2.6' );
define( 'HT_EASY_GA4_ROOT', __FILE__ );
define( 'HT_EASY_GA4_URL', plugins_url( '/', HT_EASY_GA4_ROOT ) );
define( 'HT_EASY_GA4_PATH', plugin_dir_path( HT_EASY_GA4_ROOT ) );
define( 'HT_EASY_GA4_BASE', plugin_basename( HT_EASY_GA4_ROOT ) );

// Required File.
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! function_exists( 'get_current_screen' ) ) {
	include_once ABSPATH . 'wp-admin/includes/screen.php';
}

require_once HT_EASY_GA4_PATH . 'includes/traits/helper-trait.php';
require_once HT_EASY_GA4_PATH . 'includes/traits/notice-trait.php';
require_once HT_EASY_GA4_PATH . 'includes/traits/rest-request-handler-trait.php';
require_once HT_EASY_GA4_PATH . 'includes/traits/rest-ecommerce-request-handler-trait.php';
require_once HT_EASY_GA4_PATH . 'includes/traits/rest-realtime-request-handler-trait.php';
require_once HT_EASY_GA4_PATH . 'includes/class-base.php';

/**
 * Returns the main instance.
 *
 * @return Ht_Easy_Ga4
 */
function htga4() {
	return \Ht_Easy_Ga4\Base::instance();
}

htga4();
