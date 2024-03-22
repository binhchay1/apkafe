<?php
/**
 * Plugin Name: Schema Pro
 * Plugin URI: https://wpschema.com
 * Author: Brainstorm Force
 * Author URI: https://www.brainstormforce.com
 * Description: Schema Pro is the go-to plugin to adding Schema Markup on your website with ease. Enables you to display rich snippets on search engines and improve your overall page SEO.
 * Version: 2.7.13
 * Text Domain: wp-schema-pro
 * License: GPL2
 *
 * @package Schema Pro
 */

/**
 * If this file is called directly, abort.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// Refresh bundled products on activate.
register_activation_hook( __FILE__, 'on_bsf_aiosrs_pro_activate' );
register_deactivation_hook( __FILE__, 'on_bsf_aiosrs_pro_deactivate' );

/**
 * Bsf aiosrs pro activate
 *
 * @since 1.1.0
 * @return void
 */
function on_bsf_aiosrs_pro_activate() {
	update_site_option( 'bsf_force_check_extensions', true );
	set_transient( 'wp-schema-pro-activated', true );
	BSF_AIOSRS_Pro_Helper::bsf_schema_pro_set_default_options();
}

/**
 * Bsf aiosrs pro deactivate
 *
 * @since 1.3.0
 * @return void
 */
function on_bsf_aiosrs_pro_deactivate() {
	delete_option( 'sp_hide_label' );

	if ( is_network_admin() ) {
		$branding = get_site_option( 'wp-schema-pro-branding-settings' );
	} else {
		$branding = get_option( 'wp-schema-pro-branding-settings' );
	}

	if ( isset( $branding['sp_hide_label'] ) && false !== $branding['sp_hide_label'] ) {

		$branding['sp_hide_label'] = 'disabled';

		if ( is_network_admin() ) {

			update_site_option( 'wp-schema-pro-branding-settings', $branding );

		} else {
			update_option( 'wp-schema-pro-branding-settings', $branding );
		}
	}
}
/**
 * Set constants.
 */
define( 'BSF_AIOSRS_PRO_FILE', __FILE__ );
define( 'BSF_AIOSRS_PRO_BASE', plugin_basename( BSF_AIOSRS_PRO_FILE ) );
define( 'BSF_AIOSRS_PRO_DIR', plugin_dir_path( BSF_AIOSRS_PRO_FILE ) );
define( 'BSF_AIOSRS_PRO_URI', plugins_url( '/', BSF_AIOSRS_PRO_FILE ) );
define( 'BSF_AIOSRS_PRO_VER', '2.7.13' );
define( 'BSF_AIOSRS_PRO_CACHE_KEY', 'wp_schema_pro_optimized_structured_data' );
define( 'BSF_AIOSRS_PRO_WEBSITE_URL', 'https://wpschema.com/' );

/**
 * Initial file.
 */
require_once BSF_AIOSRS_PRO_DIR . 'classes/class-bsf-aiosrs-pro-helper.php';
require_once BSF_AIOSRS_PRO_DIR . 'classes/class-bsf-aiosrs-pro.php';


if ( is_admin() ) {
	// Load Astra Notices library.
	require_once BSF_AIOSRS_PRO_DIR . '/lib/astra-notices/class-astra-notices.php';
}

/**
 * BSF analytics.
 */
/**
 * BSF analytics.
 */
if ( ! class_exists( 'BSF_Analytics_Loader' ) ) {
	require_once BSF_AIOSRS_PRO_DIR . 'admin/bsf-analytics/class-bsf-analytics-loader.php';
}

$bsf_analytics = BSF_Analytics_Loader::get_instance();

$bsf_analytics->set_entity(
	array(
		'bsf' => array(
			'product_name'    => 'Schema Pro',
			'path'            => BSF_AIOSRS_PRO_DIR . 'admin/bsf-analytics',
			'author'          => 'Brainstorm Force',
			'time_to_display' => '+24 hours',
		),
	)
);

/**
 * Brainstorm Updater.
 */
require_once BSF_AIOSRS_PRO_DIR . 'classes/class-brainstorm-update-aiosrs-pro.php';
require_once BSF_AIOSRS_PRO_DIR . 'wpsp-blocks/classes/class-bsf-sp-loader.php';
require_once BSF_AIOSRS_PRO_DIR . 'wpsp-blocks/classes/class-bsf-sp-admin.php';

/**
 * Schema Pro for Gutenberg admin notice for minimum PHP version.
 *
 * Warning when the site doesn't have the minimum required PHP version.
 *
 * @since 2.1.1
 *
 * @return void
 */
function wpsp_fail_php_version() {
	/* translators: %s: PHP version */
	$message      = sprintf( esc_html__( 'Schema Pro blocks requires PHP version %s+, plugin is currently NOT RUNNING.', 'wp-schema-pro' ), '5.6' );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
	echo wp_kses_post( $html_message );
}


/**
 * Schema Pro for Gutenberg admin notice for minimum WordPress version.
 *
 * Warning when the site doesn't have the minimum required WordPress version.
 *
 * @since 1.8.1
 *
 * @return void
 */
function wpsp_fail_wp_version() {
	/* translators: %s: WordPress version */
	$message      = sprintf( esc_html__( 'Schema Pro blocks requires WordPress version %s+. Because you are using an earlier version, the plugin is currently NOT RUNNING.', 'wp-schema-pro' ), '4.7' );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
	echo wp_kses_post( $html_message );
}

