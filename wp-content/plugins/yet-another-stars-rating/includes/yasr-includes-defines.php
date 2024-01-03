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

//This defines doesn't come from a setting page
//role to save overall_rating and multi set
if(!defined('YASR_USER_CAPABILITY_EDIT_POST')) {
    define('YASR_USER_CAPABILITY_EDIT_POST', 'edit_posts');
}

//This defines doesn't come from a setting page
if(!defined('YASR_SECONDS_BETWEEN_RATINGS')) {
    $time = (int)apply_filters('yasr_seconds_between_ratings', MINUTE_IN_SECONDS);
    define('YASR_SECONDS_BETWEEN_RATINGS', $time);
}

//Since version 3.3.0 the defines are inside a file, instead of a class, for better support PHPStorm auto-completion
//https://youtrack.jetbrains.com/issue/WI-11390/Make-define-Constants-from-inside-methods-available-for-completion-everywhere.
$settings              = new YasrSettingsValues();
$yasr_general_settings = $settings->getGeneralSettings();
$style_options         = $settings->getStyleSettings();
$multi_set_options     = $settings->getMultiSettings();

if(isset($yasr_general_settings['auto_insert_enabled'])) {
    define('YASR_AUTO_INSERT_ENABLED', (int)$yasr_general_settings['auto_insert_enabled']);
} else {
    define('YASR_AUTO_INSERT_ENABLED', 0);
}

if (YASR_AUTO_INSERT_ENABLED === 1) {
    define('YASR_AUTO_INSERT_WHAT',  $yasr_general_settings['auto_insert_what']);
    define('YASR_AUTO_INSERT_WHERE', $yasr_general_settings['auto_insert_where']);
    define('YASR_AUTO_INSERT_ALIGN', $yasr_general_settings['auto_insert_align']);
    define('YASR_AUTO_INSERT_SIZE',  $yasr_general_settings['auto_insert_size']);
    define('YASR_AUTO_INSERT_EXCLUDE_PAGES', $yasr_general_settings['auto_insert_exclude_pages']);
    define('YASR_AUTO_INSERT_CUSTOM_POST_ONLY', $yasr_general_settings['auto_insert_custom_post_only']);
}  else {
    define('YASR_AUTO_INSERT_WHAT', false);
    define('YASR_AUTO_INSERT_WHERE', false);
    define('YASR_AUTO_INSERT_ALIGN', false);
    define('YASR_AUTO_INSERT_SIZE', false);
    define('YASR_AUTO_INSERT_EXCLUDE_PAGES', false);
    define('YASR_AUTO_INSERT_CUSTOM_POST_ONLY', false);
}

define('YASR_STARS_TITLE', $yasr_general_settings['stars_title']);

if (YASR_STARS_TITLE === 'yes') {
    define('YASR_STARS_TITLE_WHAT',          $yasr_general_settings['stars_title_what']);
    define('YASR_STARS_TITLE_EXCLUDE_PAGES', $yasr_general_settings['stars_title_exclude_pages']);
    define('YASR_STARS_TITLE_WHERE',         $yasr_general_settings['stars_title_where']);
} else {
    define('YASR_STARS_TITLE_WHAT', false);
    define('YASR_STARS_TITLE_EXCLUDE_PAGES', false);
    define('YASR_STARS_TITLE_WHERE', false);
}

define('YASR_SORT_POSTS_BY',              $yasr_general_settings['sort_posts_by']);
define('YASR_SORT_POSTS_IN',              json_encode($yasr_general_settings['sort_posts_in']));
define('YASR_SHOW_OVERALL_IN_LOOP',       $yasr_general_settings['show_overall_in_loop']);
define('YASR_SHOW_VISITOR_VOTES_IN_LOOP', $yasr_general_settings['show_visitor_votes_in_loop']);
define('YASR_VISITORS_STATS',             $yasr_general_settings['visitors_stats']);
define('YASR_ALLOWED_USER',               $yasr_general_settings['allowed_user']);

//custom texts
define('YASR_TEXT_BEFORE_OVERALL',        $yasr_general_settings['text_before_overall']);
define('YASR_TEXT_BEFORE_VR',             $yasr_general_settings['text_before_visitor_rating']);
define('YASR_TEXT_AFTER_VR',              $yasr_general_settings['text_after_visitor_rating']);
define('YASR_TEXT_RATING_SAVED',          $yasr_general_settings['custom_text_rating_saved']);
define('YASR_TEXT_RATING_UPDATED',        $yasr_general_settings['custom_text_rating_updated']);
define('YASR_TEXT_USER_VOTED',            $yasr_general_settings['custom_text_user_voted']);
define('YASR_TEXT_MUST_SIGN_IN',          $yasr_general_settings['custom_text_must_sign_in']);
//end custom texts

define('YASR_ENABLE_IP',                  $yasr_general_settings['enable_ip']);
define('YASR_ITEMTYPE',                   $yasr_general_settings['snippet_itemtype']);
define('YASR_PUBLISHER_TYPE',             $yasr_general_settings['publisher']);
define('YASR_PUBLISHER_NAME',             $yasr_general_settings['publisher_name']);

if (isset($yasr_general_settings['publisher_logo'])
    && (yasr_check_valid_url($yasr_general_settings['publisher_logo']) === true)) {
    define('YASR_PUBLISHER_LOGO', $yasr_general_settings['publisher_logo']);
} else {
    define('YASR_PUBLISHER_LOGO', get_site_icon_url());
}

define('YASR_ENABLE_AJAX', $yasr_general_settings['enable_ajax']);


//To better support php version < 7, I can't use an array into define
//Also, I can't use const here, because it only works with primitive values
//https://stackoverflow.com/questions/2447791/php-define-vs-const
define('YASR_STYLE_OPTIONS', json_encode($style_options));

define('YASR_STARS_SET',        $style_options['stars_set_free']);
define('YASR_SCHEME_COLOR',     $style_options['scheme_color_multiset']);
define('YASR_CUSTOM_CSS_RULES', $style_options['textarea']);


/**
 * Defines for MultiSet options
 *
 */
define('YASR_MULTI_SHOW_AVERAGE', $multi_set_options['show_average']);

/**
 * All the other defines
 *
 * @author Dario Curvino <@dudo>
 * @since  3.0.5
 */

//Text for button in settings pages
$save_settings_text = esc_html__('Save All Settings', 'yet-another-stars-rating');
define('YASR_SAVE_All_SETTINGS_TEXT', $save_settings_text);
