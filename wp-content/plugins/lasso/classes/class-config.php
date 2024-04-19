<?php
/**
 * Declare class Config
 *
 * @package Config
 */

namespace Lasso\Classes;

/**
 * Config
 */
class Config {
	/**
	 * Print header html
	 */
	public static function get_header() {
		$header_path = LASSO_PLUGIN_PATH . '/admin/views/header.php';
		include_once $header_path;
	}

	/**
	 * Print footer html
	 */
	public static function get_footer() {
		$footer_path = LASSO_PLUGIN_PATH . '/admin/views/footer.php';
		include_once $footer_path;
	}
}
