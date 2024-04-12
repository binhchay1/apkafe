<?php
/**
 * Plugin Name: Version Control for jQuery
 * Plugin URI: https://github.com/leanderiversen/version-control-for-jquery/
 * Description: Version Control for jQuery is the easiest way to control the version of jQuery used on your website.
 * Version: 3.9
 * Author: Leander Iversen
 * Author URI: https://github.com/leanderiversen/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: version-control-for-jquery
 * Domain Path: /languages
 */

namespace LI\VCFJ;

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {

	// Define the default version of jQuery Core.
	public const DEFAULT_CORE = '3.7.1';

	// Define the default version of jQuery Migrate.
	public const DEFAULT_MIGRATE = '3.4.1';

	// Define the default CDN.
	public const DEFAULT_CDN = 'jquery';

	private static $instance = null;

	public static function initialise() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'require_files' ) );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'version-control-for-jquery', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function require_files(): void {
		require_once plugin_dir_path( __FILE__ ) . 'src/traits/trait-initialise.php';
		require_once plugin_dir_path( __FILE__ ) . 'src/class-helpers.php';
		require_once plugin_dir_path( __FILE__ ) . 'src/class-mappings.php';
		require_once plugin_dir_path( __FILE__ ) . 'src/class-settings.php';
		require_once plugin_dir_path( __FILE__ ) . 'src/class-enqueue.php';
	}

}

Plugin::initialise();
