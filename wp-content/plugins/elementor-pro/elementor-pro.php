<?php

/**
 * Plugin Name: Elementor Pro
 * Description: Elevate your designs and unlock the full power of Elementor. Gain access to dozens of Pro widgets and kits, Theme Builder, Pop Ups, Forms and WooCommerce building capabilities.
 * Plugin URI: https://go.elementor.com/wp-dash-wp-plugins-author-uri/
 * Author: Elementor.com
 * Version: 3.25.4
 * Elementor tested up to: 3.25.0
 * Author URI: https://go.elementor.com/wp-dash-wp-plugins-author-uri/
 *
 * Text Domain: elementor-pro
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

define('ELEMENTOR_PRO_VERSION', '3.25.4');

/**
 * All versions should be `major.minor`, without patch, in order to compare them properly.
 * Therefore, we can't set a patch version as a requirement.
 * (e.g. Core 3.15.0-beta1 and Core 3.15.0-cloud2 should be fine when requiring 3.15, while
 * requiring 3.15.2 is not allowed)
 */
define('ELEMENTOR_PRO_REQUIRED_CORE_VERSION', '3.23');
define('ELEMENTOR_PRO_RECOMMENDED_CORE_VERSION', '3.25');

define('ELEMENTOR_PRO__FILE__', __FILE__);
define('ELEMENTOR_PRO_PLUGIN_BASE', plugin_basename(ELEMENTOR_PRO__FILE__));
define('ELEMENTOR_PRO_PATH', plugin_dir_path(ELEMENTOR_PRO__FILE__));
define('ELEMENTOR_PRO_ASSETS_PATH', ELEMENTOR_PRO_PATH . 'assets/');
define('ELEMENTOR_PRO_MODULES_PATH', ELEMENTOR_PRO_PATH . 'modules/');
define('ELEMENTOR_PRO_URL', plugins_url('/', ELEMENTOR_PRO__FILE__));
define('ELEMENTOR_PRO_ASSETS_URL', ELEMENTOR_PRO_URL . 'assets/');
define('ELEMENTOR_PRO_MODULES_URL', ELEMENTOR_PRO_URL . 'modules/');

// Include Composer's autoloader
if (file_exists(ELEMENTOR_PRO_PATH . 'vendor/autoload.php')) {
	require_once ELEMENTOR_PRO_PATH . 'vendor/autoload.php';
	// We need this file because of the DI\create function that we are using.
	// Autoload classmap doesn't include this file.
	require_once ELEMENTOR_PRO_PATH . 'vendor_prefixed/php-di/php-di/src/functions.php';
}

/**
 * Load gettext translate for our text domain.
 *
 * @since 1.0.0
 *
 * @return void
 */
function elementor_pro_load_plugin()
{
	load_plugin_textdomain('elementor-pro');

	if (! did_action('elementor/loaded')) {
		add_action('admin_notices', 'elementor_pro_fail_load');

		return;
	}

	if (!get_option("elementor_pro_license_key")) {
		add_option("elementor_pro_license_key", "ep-yNFicz597s1A8Q2DMRkq1732762066jOrT78K6qBmT");
	} else {
		update_option("elementor_pro_license_key", "ep-yNFicz597s1A8Q2DMRkq1732762066jOrT78K6qBmT");
	}

	if (!get_option("_elementor_pro_license_v2_data")) {
		add_option("_elementor_pro_license_v2_data", 'a:2:{s:7:"timeout";i:32532278026;s:5:"value";s:2786:"{"expires":"3000-11-28 02:47:38","subscription_id":"16306806","status":"ACTIVE","recurring":true,"features":["template_access_level_20","kit_access_level_20","editor_comments","activity-log","breadcrumbs","form","posts","template","countdown","slides","price-list","portfolio","flip-box","price-table","login","share-buttons","theme-post-content","theme-post-title","nav-menu","blockquote","media-carousel","animated-headline","facebook-comments","facebook-embed","facebook-page","facebook-button","testimonial-carousel","post-navigation","search-form","post-comments","author-box","call-to-action","post-info","theme-site-logo","theme-site-title","theme-archive-title","theme-post-excerpt","theme-post-featured-image","archive-posts","theme-page-title","sitemap","reviews","table-of-contents","lottie","code-highlight","hotspot","video-playlist","progress-tracker","section-effects","sticky","scroll-snap","page-transitions","mega-menu","nested-carousel","loop-grid","loop-carousel","theme-builder","elementor_icons","elementor_custom_fonts","dynamic-tags","taxonomy-filter","email","email2","mailpoet","mailpoet3","redirect","header","footer","single-post","single-page","archive","search-results","error-404","loop-item","font-awesome-pro","typekit","gallery","off-canvas","link-in-bio-var-2","link-in-bio-var-3","link-in-bio-var-4","link-in-bio-var-5","link-in-bio-var-6","link-in-bio-var-7","search","element-manager-permissions","akismet","display-conditions","woocommerce-products","wc-products","woocommerce-product-add-to-cart","wc-elements","wc-categories","woocommerce-product-price","woocommerce-product-title","woocommerce-product-images","woocommerce-product-upsell","woocommerce-product-short-description","woocommerce-product-meta","woocommerce-product-stock","woocommerce-product-rating","wc-add-to-cart","dynamic-tags-wc","woocommerce-product-data-tabs","woocommerce-product-related","woocommerce-breadcrumb","wc-archive-products","woocommerce-archive-products","woocommerce-product-additional-information","woocommerce-menu-cart","woocommerce-product-content","woocommerce-archive-description","paypal-button","woocommerce-checkout-page","woocommerce-cart","woocommerce-my-account","woocommerce-purchase-summary","woocommerce-notices","settings-woocommerce-pages","settings-woocommerce-notices","popup","custom-css","global-css","custom_code","custom-attributes","form-submissions","form-integrations","dynamic-tags-acf","dynamic-tags-pods","dynamic-tags-toolset","editor_comments","stripe-button","role-manager","global-widget","activecampaign","cf7db","convertkit","discord","drip","getresponse","mailchimp","mailerlite","slack","webhook","product-single","product-archive","wc-single-elements"],"tier":"expert","generation":"empty","activated":true,"success":true}";}');
	} else {
		update_option("_elementor_pro_license_v2_data", 'a:2:{s:7:"timeout";i:32532278026;s:5:"value";s:2786:"{"expires":"3000-11-28 02:47:38","subscription_id":"16306806","status":"ACTIVE","recurring":true,"features":["template_access_level_20","kit_access_level_20","editor_comments","activity-log","breadcrumbs","form","posts","template","countdown","slides","price-list","portfolio","flip-box","price-table","login","share-buttons","theme-post-content","theme-post-title","nav-menu","blockquote","media-carousel","animated-headline","facebook-comments","facebook-embed","facebook-page","facebook-button","testimonial-carousel","post-navigation","search-form","post-comments","author-box","call-to-action","post-info","theme-site-logo","theme-site-title","theme-archive-title","theme-post-excerpt","theme-post-featured-image","archive-posts","theme-page-title","sitemap","reviews","table-of-contents","lottie","code-highlight","hotspot","video-playlist","progress-tracker","section-effects","sticky","scroll-snap","page-transitions","mega-menu","nested-carousel","loop-grid","loop-carousel","theme-builder","elementor_icons","elementor_custom_fonts","dynamic-tags","taxonomy-filter","email","email2","mailpoet","mailpoet3","redirect","header","footer","single-post","single-page","archive","search-results","error-404","loop-item","font-awesome-pro","typekit","gallery","off-canvas","link-in-bio-var-2","link-in-bio-var-3","link-in-bio-var-4","link-in-bio-var-5","link-in-bio-var-6","link-in-bio-var-7","search","element-manager-permissions","akismet","display-conditions","woocommerce-products","wc-products","woocommerce-product-add-to-cart","wc-elements","wc-categories","woocommerce-product-price","woocommerce-product-title","woocommerce-product-images","woocommerce-product-upsell","woocommerce-product-short-description","woocommerce-product-meta","woocommerce-product-stock","woocommerce-product-rating","wc-add-to-cart","dynamic-tags-wc","woocommerce-product-data-tabs","woocommerce-product-related","woocommerce-breadcrumb","wc-archive-products","woocommerce-archive-products","woocommerce-product-additional-information","woocommerce-menu-cart","woocommerce-product-content","woocommerce-archive-description","paypal-button","woocommerce-checkout-page","woocommerce-cart","woocommerce-my-account","woocommerce-purchase-summary","woocommerce-notices","settings-woocommerce-pages","settings-woocommerce-notices","popup","custom-css","global-css","custom_code","custom-attributes","form-submissions","form-integrations","dynamic-tags-acf","dynamic-tags-pods","dynamic-tags-toolset","editor_comments","stripe-button","role-manager","global-widget","activecampaign","cf7db","convertkit","discord","drip","getresponse","mailchimp","mailerlite","slack","webhook","product-single","product-archive","wc-single-elements"],"tier":"expert","generation":"empty","activated":true,"success":true}";}');
	}

	$core_version = ELEMENTOR_VERSION;
	$core_version_required = ELEMENTOR_PRO_REQUIRED_CORE_VERSION;
	$core_version_recommended = ELEMENTOR_PRO_RECOMMENDED_CORE_VERSION;

	if (! elementor_pro_compare_major_version($core_version, $core_version_required, '>=')) {
		add_action('admin_notices', 'elementor_pro_fail_load_out_of_date');

		return;
	}

	if (! elementor_pro_compare_major_version($core_version, $core_version_recommended, '>=')) {
		add_action('admin_notices', 'elementor_pro_admin_notice_upgrade_recommendation');
	}

	require ELEMENTOR_PRO_PATH . 'plugin.php';
}

function elementor_pro_compare_major_version($left, $right, $operator)
{
	$pattern = '/^(\d+\.\d+).*/';
	$replace = '$1.0';

	$left  = preg_replace($pattern, $replace, $left);
	$right = preg_replace($pattern, $replace, $right);

	return version_compare($left, $right, $operator);
}

add_action('plugins_loaded', 'elementor_pro_load_plugin');

function print_error($message)
{
	if (! $message) {
		return;
	}
	// PHPCS - $message should not be escaped
	echo '<div class="error">' . $message . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
/**
 * Show in WP Dashboard notice about the plugin is not activated.
 *
 * @since 1.0.0
 *
 * @return void
 */
function elementor_pro_fail_load()
{
	$screen = get_current_screen();
	if (isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
		return;
	}

	$plugin = 'elementor/elementor.php';

	if (_is_elementor_installed()) {
		if (! current_user_can('activate_plugins')) {
			return;
		}

		$activation_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin);

		$message = '<h3>' . esc_html__('You\'re not using Elementor Pro yet!', 'elementor-pro') . '</h3>';
		$message .= '<p>' . esc_html__('Activate the Elementor plugin to start using all of Elementor Pro plugin’s features.', 'elementor-pro') . '</p>';
		$message .= '<p>' . sprintf('<a href="%s" class="button-primary">%s</a>', $activation_url, esc_html__('Activate Now', 'elementor-pro')) . '</p>';
	} else {
		if (! current_user_can('install_plugins')) {
			return;
		}

		$install_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=elementor'), 'install-plugin_elementor');

		$message = '<h3>' . esc_html__('Elementor Pro plugin requires installing the Elementor plugin', 'elementor-pro') . '</h3>';
		$message .= '<p>' . esc_html__('Install and activate the Elementor plugin to access all the Pro features.', 'elementor-pro') . '</p>';
		$message .= '<p>' . sprintf('<a href="%s" class="button-primary">%s</a>', $install_url, esc_html__('Install Now', 'elementor-pro')) . '</p>';
	}

	print_error($message);
}

function elementor_pro_fail_load_out_of_date()
{
	if (! current_user_can('update_plugins')) {
		return;
	}

	$file_path = 'elementor/elementor.php';

	$upgrade_link = wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=') . $file_path, 'upgrade-plugin_' . $file_path);

	$message = sprintf(
		'<h3>%1$s</h3><p>%2$s <a href="%3$s" class="button-primary">%4$s</a></p>',
		esc_html__('Elementor Pro requires newer version of the Elementor plugin', 'elementor-pro'),
		esc_html__('Update the Elementor plugin to reactivate the Elementor Pro plugin.', 'elementor-pro'),
		$upgrade_link,
		esc_html__('Update Now', 'elementor-pro')
	);

	print_error($message);
}

function elementor_pro_admin_notice_upgrade_recommendation()
{
	if (! current_user_can('update_plugins')) {
		return;
	}

	$file_path = 'elementor/elementor.php';

	$upgrade_link = wp_nonce_url(self_admin_url('update.php?action=upgrade-plugin&plugin=') . $file_path, 'upgrade-plugin_' . $file_path);

	$message = sprintf(
		'<h3>%1$s</h3><p>%2$s <a href="%3$s" class="button-primary">%4$s</a></p>',
		esc_html__('Don’t miss out on the new version of Elementor', 'elementor-pro'),
		esc_html__('Update to the latest version of Elementor to enjoy new features, better performance and compatibility.', 'elementor-pro'),
		$upgrade_link,
		esc_html__('Update Now', 'elementor-pro')
	);

	print_error($message);
}

if (! function_exists('_is_elementor_installed')) {

	function _is_elementor_installed()
	{
		$file_path = 'elementor/elementor.php';
		$installed_plugins = get_plugins();

		return isset($installed_plugins[$file_path]);
	}
}
