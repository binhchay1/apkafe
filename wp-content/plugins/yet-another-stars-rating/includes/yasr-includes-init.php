<?php
/*

Copyright 2014 Dario Curvino (email : d.curvino@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>
*/

if (!defined('ABSPATH')) {
    exit('You\'re not allowed to see this page');
} // Exit if accessed directly

//The defines are inside a file, instead of a class, for better support PHPStorm auto-completion
//https://youtrack.jetbrains.com/issue/WI-11390/Make-define-Constants-from-inside-methods-available-for-completion-everywhere.

//e.g. http://localhost/plugin_development/wp-content/plugins/yet-another-stars-rating/includes/js/
define('YASR_JS_DIR_INCLUDES', plugins_url() . '/' . YASR_RELATIVE_PATH_INCLUDES . '/js/');
//CSS directory absolute URL
define('YASR_CSS_DIR_INCLUDES', plugins_url() . '/' . YASR_RELATIVE_PATH_INCLUDES . '/css/');

global $wpdb;
//defining tables names
define('YASR_LOG_TABLE',              $wpdb->prefix . 'yasr_log');
define('YASR_LOG_MULTI_SET',          $wpdb->prefix . 'yasr_log_multi_set');
define('YASR_MULTI_SET_NAME_TABLE',   $wpdb->prefix . 'yasr_multi_set');
define('YASR_MULTI_SET_FIELDS_TABLE', $wpdb->prefix . 'yasr_multi_set_fields');

require YASR_ABSOLUTE_PATH . '/vendor/gamajo/template-loader/class-gamajo-template-loader.php';

require YASR_ABSOLUTE_PATH_INCLUDES . '/yasr-includes-functions.php';
require YASR_ABSOLUTE_PATH_INCLUDES . '/yasr-widgets.php';
require YASR_ABSOLUTE_PATH_INCLUDES . '/shortcodes/yasr-shortcode-functions.php';


/**
 * Callback function for the spl_autoload_register above.
 *
 * @param $class
 */
function yasr_autoload_includes_classes($class) {
    /**
     * If the class being requested does not start with 'Yasr' prefix,
     * it's not in Yasr Project
     */
    if (0 !== strpos($class, 'Yasr')) {
        return;
    }
    $file_name =  YASR_ABSOLUTE_PATH_INCLUDES . '/classes/' . $class . '.php';

    // check if file exists, just to be sure
    if (file_exists($file_name)) {
        require($file_name);
    }
}

//AutoLoad Yasr Classes, only when a object is created
spl_autoload_register('yasr_autoload_includes_classes');

//do defines
require YASR_ABSOLUTE_PATH_INCLUDES . '/yasr-includes-defines.php';

//run includes filters
$yasr_includes_filter = new YasrIncludesFilters();
$yasr_includes_filter->filterCustomTexts();

//Load window.var used by YASR
$yasr_load_script = new YasrScriptsLoader();
$yasr_load_script->loadRequiredScripts();

//support for caching plugins
$yasr_caching_plugin_support = new YasrCachingPlugins();
$yasr_caching_plugin_support->cachingPluginSupport();

//Init Ajax
$yasr_init_ajax = new YasrShortcodesAjax();
$yasr_init_ajax->init();

//Load rest API
require YASR_ABSOLUTE_PATH_INCLUDES . '/rest/yasr-rest.php';