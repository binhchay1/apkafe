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

/**
 * @since 2.3.3
 *
 * Class YasrCustomPostTypes
 */
class YasrCustomPostTypes {

    /**
     ** @since 2.3.3
     *
     * @depreacted 2.4.5
     * This function is deprecated since version 2.4.5, the caller (in yasr-rest.php) is commented out
     */
    public static function addFilterExistingCpt() {
        add_filter('register_post_type_args', 'YasrCustomPostTypes::enableRestAllCPT', 10, 2);
    }

    /**
     * @param $args
     * @param $post_type
     *
     * @return mixed
     * @since 2.3.3
     *
     * @depreacted 2.4.5
     * @deprecated the add_filter addFilterExistingCpt is just above
     *
     * Filter all post types and enables rest response and rest base, only if:
     * there isn't (or set to false) show_in_rest arg
     * there isn't (or set to false) _builtin arg
     *
     * lastly, add a rest base, only if not set (or set to false)
     */
    public static function enableRestAllCPT($args, $post_type) {

        if (
            (!isset($args['show_in_rest']) || ($args['show_in_rest'] === false))
            &&
            (!isset($args['_builtin']) || $args['_builtin'] === false)
        ) {
            $args['show_in_rest'] = true;

            //this if should be useless, just to be safe
            if(!isset($args['rest_base']) || $args['rest_base'] === false) {
                $args['rest_base'] = $post_type;
            }
        }
        return $args;
    }

    /**
     * Return all user registered post types.
     * Must be used on init or after
     *
     * @return bool|string[]|WP_Post_Type[]
     */
    public static function getCustomPostTypes() {
        $args = array(
            'public'   => true,
            '_builtin' => false
        );

        $output   = 'names'; // names or objects, note names is the default
        $operator = 'and'; // 'and' or 'or'

        //if not found, returns an empty array
        $post_types = get_post_types( $args, $output, $operator );

        if ($post_types) {
            return ($post_types);
        }
        return false;
    }

    /**
     * Return all post types (even post, page, etc.)
     * Must be used on init or after
     *
     * @return string[]|WP_Post_Type[]
     */
    public static function returnAllPostTypes() {
        $args = array(
            'public' => true,
        );

        $output   = 'names';
        $operator = 'and';

        return get_post_types($args, $output, $operator);
    }

    /**
     * Check if the current post/page is CPT
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.5
     * @return bool
     */
    public static function isCpt() {
        $custom_post_types = self::getCustomPostTypes();
        //If is a post type return content and stars
        if (is_singular($custom_post_types)) {
            return true;
        } //else return just content
        return false;
    }

    /**
     * Return rest_base if exists
     * or post_type otherwise
     *
     * @param integer | bool $post_id
     *
     * @return string
     */
    public static function returnBaseUrl($post_id=false) {
        if($post_id === false || !is_int($post_id)) {
            $post_id = get_the_ID();
        }
        $post_type = get_post_type($post_id);

        $post_type_object = get_post_type_object($post_type);

        if($post_type_object !== null && is_object($post_type_object) && $post_type_object->rest_base !== false) {
            return $post_type_object->rest_base;
        }

        return $post_type;
    }

    public static function enableCustomFields ($cpt) {
        add_post_type_support($cpt, 'custom-fields');
    }
}