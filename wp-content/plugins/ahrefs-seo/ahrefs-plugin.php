<?php
/**
Plugin Name: Ahrefs SEO
Plugin URI: https://ahrefs.com/wordpress-seo-plugin
Description: Automate content audits and grow organic traffic to your WordPress website with Ahrefs SEO plugin.
Author: Ahrefs
Author URI: https://ahrefs.com/
Version: 0.10.2
Requires at least: 5.0
Requires PHP: 5.6
Text Domain: ahrefs-seo
Domain Path: /languages
 */

namespace ahrefs\AhrefsSeo;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AHREFS_SEO_VERSION', '0.10.2' );
define( 'AHREFS_SEO_RELEASE', 'production' );

// check minimal php version.
if ( version_compare( PHP_VERSION, '5.6' ) >= 0 ) {
	if ( ! defined( 'AHREFS_SEO_BASENAME' ) ) {
		define( 'AHREFS_SEO_BASENAME', plugin_basename( __FILE__ ) );
	}
	require_once __DIR__ . '/autoload.php';

	register_activation_hook(
		__FILE__,
		function() {
			Ahrefs_Seo::plugin_activate();
		}
	);
	register_deactivation_hook(
		__FILE__,
		function() {
			Ahrefs_Seo::plugin_deactivate();
		}
	);
	add_action(
		'init',
		function() {
			Ahrefs_Seo::get();
		},
		12
	); // load later.
} else {
	// deactivate with a notice.
	add_action(
		'admin_notices',
		function() {
			?>
		<div class="notice ahrefs-seo-notice notice-error">
			<p>
			<?php
			/* translators: %s: php version string */
			printf( esc_html__( 'Ahrefs SEO requires PHP version %s or above. Please update PHP to run this plugin.', 'ahrefs-seo' ), '5.6' );
			?>
			&nbsp;<a href="https://wordpress.org/support/update-php/" target="_blank"><?php esc_html_e( 'Read more', 'ahrefs-seo' ); ?></a></p>
		</div>
			<?php
		}
	);
	add_action(
		'admin_init',
		function() {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			if ( isset( $_GET['activate'] ) ) { // phpcs:ignore
				unset( $_GET['activate'] ); // phpcs:ignore -- do not show "Plugin activated" notice.
			}
		}
	);
}
