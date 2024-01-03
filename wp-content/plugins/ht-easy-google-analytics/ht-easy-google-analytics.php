<?php
/**
 * Plugin Name: HT Easy GA4
 * Description: Start tracking your website usage data by using Google Analytics 4.
 * Author: 		HasThemes
 * Author URI: 	https://hasthemes.com/
 * Version: 	1.0.8
 * License:  	GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ht-easy-ga4
 * Domain Path: /languages
*/

if( ! defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly
define( 'HT_EASY_GA4_ROOT', __FILE__ );
define( 'HT_EASY_GA4_URL', plugins_url( '/', HT_EASY_GA4_ROOT ) );
define( 'HT_EASY_GA4_PATH', plugin_dir_path( HT_EASY_GA4_ROOT ) );
define( 'HT_EASY_GA4_BASE', plugin_basename( HT_EASY_GA4_ROOT ) );

function ht_easy_ga4_get_id(){
	$ht_easy_ga4_id = get_option('ht_easy_ga4_id') ? get_option('ht_easy_ga4_id') : '';
	return $ht_easy_ga4_id;
}

// Required File
require_once ( HT_EASY_GA4_PATH .'includes/class.ht-easy-ga4.php' );
