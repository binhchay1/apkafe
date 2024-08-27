<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://boomdevs.com/
 * @since             1.0.0
 * @package           Boomdevs_Toc
 *
 * @wordpress-plugin
 * Plugin Name:       TOP Table Of Contents
 * Plugin URI:        https://boomdevs.com/boomdevs-toc
 * Description:       Easily creates an SEO-friendly table of contents for your blog posts and pages. Offers both Auto and Manual Insert with highly customization options.
 * Version:           1.3.21
 * Author:            BoomDevs
 * Author URI:        https://boomdevs.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       boomdevs-toc
 * Domain Path:       /languages
 */

// If this file is called directly, abort.

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Plugin basic information.
 */
define( 'BOOMDEVS_TOC_VERSION', '1.3.21' );
define( 'BOOMDEVS_TOC_PATH', plugin_dir_path( __FILE__ ) );
define( 'BOOMDEVS_TOC_URL', plugin_dir_url( __FILE__ ) );
define( 'BOOMDEVS_TOC_NAME', 'boomdevs-toc' );
define( 'BOOMDEVS_FULL_NAME', 'TOP Table Of Contents' );
define( 'BOOMDEVS_BASE_NAME', plugin_basename( __FILE__ ) );


/**
 * Require Composer autoload
 */
require __DIR__ . '/vendor/autoload.php';


/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_top_table_of_contents() {

    if ( ! class_exists( 'Appsero\Client' ) ) {
        require_once __DIR__ . '/appsero/src/Client.php';
    }

    $client = new Appsero\Client( 'ffa59dee-5128-4d2c-8c87-be2e83ffefa7', 'TOP Table Of Contents', __FILE__ );

    // Active insights
    $client->insights()->init();
}

appsero_init_tracker_top_table_of_contents();

//RankMath SEO Content Readability
if ( class_exists( 'RankMath' ) ) {
    add_filter( 'rank_math/researches/toc_plugins', function( $toc_plugins ) {
        $toc_plugins['top-table-of-contents/boomdevs-toc.php'] = 'TOP Table Of Contents';
        return $toc_plugins;
    } );
}

/**
 * Intregated Gutenberg block
 *
 */
require_once BOOMDEVS_TOC_PATH . 'includes/block-editor/block.php';

/**
 * Intregated Elementor widgets
 */
require_once BOOMDEVS_TOC_PATH . 'addons/boomdevs-toc-elementor-widgets.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-boomdevs-toc-activator.php
 */
function activate_boomdevs_toc() {
    require_once BOOMDEVS_TOC_PATH . 'includes/class-boomdevs-toc-activator.php';
    Boomdevs_Toc_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-boomdevs-toc-deactivator.php
 */
function deactivate_boomdevs_toc() {
    require_once BOOMDEVS_TOC_PATH . 'includes/class-boomdevs-toc-deactivator.php';
    Boomdevs_Toc_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_boomdevs_toc' );
register_deactivation_hook( __FILE__, 'deactivate_boomdevs_toc' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require BOOMDEVS_TOC_PATH . 'includes/class-boomdevs-toc.php';

do_action( 'boomdevs_toc/loaded' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_boomdevs_toc() {
    $plugin = new Boomdevs_Toc();
    $plugin->run();
}

add_action( 'plugins_loaded', 'run_boomdevs_toc', 2 );