<?php if (!defined('ABSPATH')) die;
/*
Plugin Name: Config for only Apkafe
Description: All config SEO for Apkafe
Author: binhchay
Version: 1.0
License: GPLv2 or later
*/

define('BINHCHAY_ADMIN_VERSION', '1.0.0');
define('BINHCHAY_ADMIN_DIR', 'binhchay');

require plugin_dir_path(__FILE__) . 'admin-form.php';
function run_ct_wp_admin_form()
{
	$plugin = new Apkafe_Admin_Form();
	$plugin->init();
}
run_ct_wp_admin_form();

add_action('template_redirect', function () {

	if ((defined('DOING_CRON') && DOING_CRON) || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) || (defined('DOING_AJAX') && DOING_AJAX)) return;

	if (is_admin()) return;

	global $wp_query;
	if ($wp_query->is_404 === false) {

		$paths = explode('/', $_SERVER['REQUEST_URI']);
		foreach ($paths as $path) {
			if ($path == '404') {
				if (end($paths) == '') {
					status_header(200);
					$wp_query->is_404  = false;
					return;
				}
			}

			if ($path == 'ja') {
				$redirect = 'ja';
			}
	
			if ($path == 'ultimate-guide-to-unleashing-natures-fury-master-the-minecraft-weather-mod') {
				$status_redirect = true;
			}
		}
		
		if($redirect == 'ja' && $status_redirect == true) {
			wp_redirect('/minecraft-weather-mod/');

			return;
		}
		
		return;
	} else {
		$paths = explode('/', $_SERVER['REQUEST_URI']);
		foreach ($paths as $path) {
			if ($path == '404') {
				if (end($paths) == '') {
					status_header(200);
					$wp_query->is_404  = false;
					return;
				}
			}
		}
	}
}, PHP_INT_MAX);

add_action('init', function ($search) {
	add_rewrite_rule('search/?$', 'index.php?s=' . $search, 'top');
	add_rewrite_rule('search/ja/?$', 'index.php?s=' . $search, 'top');
	add_rewrite_rule('search/th/?$', 'index.php?s=' . $search, 'top');
});

add_filter('get_search_form', function ($form) {
	$form = '<form role="search" method="post" id="searchform" class="searchform" action="' . home_url('/search/') . '" >
	  <div class="custom-form"><label class="screen-reader-text" for="s">' . __('Search:') . '</label>
	  <input type="text" value="' . get_search_query() . '" name="s" id="s" />
	  <input type="submit" id="searchsubmit" value="' . esc_attr__('Search') . '" />
	</div>
	</form>';

	return $form;
}, 40);

$path = explode('/', $_SERVER['REQUEST_URI']);
if (in_array('page', $path)) {
	add_filter('wpseo_canonical', function () {
		$url = "https://" . $_SERVER['HTTP_HOST'] . '/' . $_SERVER['REQUEST_URI'];
		$link = '';
		$statusExp = false;

		$explode = explode('/', $url);
		foreach ($explode as $check) {
			if ($check == 'page') {
				$link = $link . '/';
				break;
			}

			if ($link == '') {
				$link = $check;
			} else {
				if ($check == null) {
					if ($statusExp == false) {
						$link = $link . $check;
						$statusExp = true;
					} else {
						continue;
					}
				}
				$link = $link . '/' . $check;
			}
		}

		return $link;
	});
}

add_action('wp_head', function () {
	$paths = explode('/', $_SERVER['REQUEST_URI']);
	if (is_front_page()) {
		$urlFontPage = 'https://apkafe.com/';
		if (in_array('th', $paths)) {
			$urlFontPage = 'https://apkafe.com/th/';
		}

		if (in_array('ja', $paths)) {
			$urlFontPage = 'https://apkafe.com/ja/';
		}

		echo '<link rel="alternate" href="' . $urlFontPage . '" hreflang="x-default" />';
	}

	if (in_array('product-category', $paths)) {
		$host = 'https://apkafe.com';
		$urlProductCategory = $host . $_SERVER['REQUEST_URI'];
		echo '<link rel="alternate" href="' . $urlProductCategory . '" hreflang="x-default" />';
	}

	if (is_admin() || is_user_logged_in()) {
		$style = '<style type="text/css">
		#main-nav {
			margin-top: 30px !important;
		}
		</style>';

		echo $style;
	}
}, PHP_INT_MAX);
