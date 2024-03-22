<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://boomdevs.com/
 * @since      1.0.0
 *
 * @package    Boomdevs_Toc
 * @subpackage Boomdevs_Toc/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Boomdevs_Toc
 * @subpackage Boomdevs_Toc/includes
 * @author     BoomDevs <admin@boomdevs.com>
 */
class Boomdevs_Toc_i18n {

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain(
            'boomdevs-toc',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );

    }

}
