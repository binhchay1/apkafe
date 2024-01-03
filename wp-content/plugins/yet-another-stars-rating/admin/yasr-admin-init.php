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

if(!is_admin()) {
    return;
}

//this defines must be triggered after the active theme's functions.php file is loaded
add_action('init', function (){
    $text = __('This feature is available only in the pro version', 'yet-another-stars-rating');
    $lock_image =
        apply_filters('yasr_feature_locked',
            '<span class="dashicons dashicons-lock" title="'.esc_attr($text).'"></span>',
            10,
            1);
    define ('YASR_LOCKED_FEATURE', yasr_kses($lock_image));


    $html_disabled_attribute = apply_filters('yasr_feature_locked_html_attribute', 'disabled', 10, 1);
    //whitelist to only allow empty string.
    if($html_disabled_attribute !== '') {
        $html_disabled_attribute = 'disabled';
    }
    define ('YASR_LOCKED_FEATURE_HTML_ATTRIBUTE', $html_disabled_attribute);

    $url = 'https://yetanotherstarsrating.com/?utm_source=wp-plugin&utm_medium=edit_category&utm_campaign=yasr_editor_category#yasr-pro';
    $upgrade_text = sprintf(
        esc_html__('Upgrade to %s to unlock this feature', 'yet-another-stars-rating'),
        sprintf(
            '<a href="%s">%s</a>',
            esc_url($url),
            'YASR PRO'
        )
    );
    $upgrade_text = apply_filters('yasr_feature_locked_text', $upgrade_text, 10, 1);

    define ('YASR_LOCKED_TEXT', yasr_kses($upgrade_text));
});

/**
 * Callback function for the spl_autoload_register above.
 *
 * @param $class
 */
function yasr_autoload_admin_classes($class) {
    /**
     * If the class being requested does not start with 'Yasr' prefix,
     * it's not in Yasr Project
     */
    if (0 !== strpos($class, 'Yasr')) {
        return;
    }
    $file_name =  YASR_ABSOLUTE_PATH_ADMIN . '/classes/' . $class . '.php';

    // check if file exists, just to be sure
    if (file_exists($file_name)) {
        require($file_name);
    }

    $file_name_settings = YASR_ABSOLUTE_PATH_ADMIN . '/settings/classes/' . $class . '.php';

    // check if file exists, just to be sure
    if (file_exists($file_name_settings)) {
        require($file_name_settings);
    }

    $file_name_editor = YASR_ABSOLUTE_PATH_ADMIN . '/editor/' . $class . '.php';

    // check if file exists, just to be sure
    if (file_exists($file_name_editor)) {
        require($file_name_editor);
    }

}

//AutoLoad Yasr Shortcode Classes, only when a object is created
spl_autoload_register('yasr_autoload_admin_classes');

$yasr_admin = new YasrAdmin();
$yasr_admin->init();

$yasr_settings = new YasrSettings();
$yasr_settings->init();

$yasr_stats_export = new YasrStatsExport();
$yasr_stats_export->init();

$yasr_editor  = new YasrEditorHooks();
$yasr_editor->init();