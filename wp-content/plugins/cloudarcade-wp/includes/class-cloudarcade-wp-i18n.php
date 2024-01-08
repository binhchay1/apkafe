<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://cloudarcade.net
 * @since      1.0.0
 *
 * @package    Cloudarcade_Wp
 * @subpackage Cloudarcade_Wp/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Cloudarcade_Wp
 * @subpackage Cloudarcade_Wp/includes
 * @author     CloudArcade <hello@redfoc.com>
 */
class Cloudarcade_Wp_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'cloudarcade-wp',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
