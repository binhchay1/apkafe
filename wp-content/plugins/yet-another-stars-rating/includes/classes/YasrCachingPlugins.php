<?php

/*

Copyright 2020 Dario Curvino (email : d.curvino@gmail.com)

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
 * Check if caching plugin is active
 *
 * @author Dario Curvino <@dudo>
 * @since 2.7.7
 * Class YasrFindCachingPlugins
 */
class YasrCachingPlugins {

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     * @return false|string
     */
    public function cachingPluginFound () {
        $methods = get_class_methods($this);

        foreach($methods as $method) {
            if((substr( $method, 0, 4 ) === "find") && $this->{$method}()) {
                return str_replace('find', '', $method);
            }
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     * @return bool
     */
    public function findWpRocket() {
        if (is_plugin_active('wp-rocket/wp-rocket.php')) {
            return true;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     * @return bool
     */
    public function findCacheEnabler() {
        if (is_plugin_active('cache-enabler/cache-enabler.php')) {
            return true;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     * @return bool
     */
    public function findLitespeed() {
        if (is_plugin_active('litespeed-cache/litespeed-cache.php')) {
            return true;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     * @return bool
     */
    public function findW3TotalCache() {
        if (is_plugin_active('w3-total-cache/w3-total-cache.php')) {
            return true;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     * @return bool
     */
    public function findWpFastestCache() {
        if (is_plugin_active('wp-fastest-cache/wpFastestCache.php')) {
            return true;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     * @return bool
     */
    public function findWpSuperCache() {
        if (is_plugin_active('wp-super-cache/wp-cache.php')) {
            return true;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     * @return bool
     */
    public function findWpOptimize() {
        if (is_plugin_active('wp-optimize/wp-optimize.php')) {
            return true;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     * @return bool
     */
    public function findBreeze() {
        if (is_plugin_active('breeze/breeze.php')) {
            return true;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     * @return bool
     */
    public function findCometCache() {
        if (is_plugin_active('comet-cache/comet-cache.php')) {
            return true;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     * @return bool
     */
    public function findHummingbird() {
        if (is_plugin_active('hummingbird-performance/wp-hummingbird.php')) {
            return true;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     * @return bool
     */
    public function findPantheon() {
        if (is_plugin_active('pantheon-advanced-page-cache/pantheon-advanced-page-cache.php')) {
            return true;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     * @return bool
     */
    public function findPerformanceScoreBooster() {
        if (is_plugin_active('wp-performance-score-booster/wp-performance-score-booster.php')) {
            return true;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.4
     *
     */
    public function cachingPluginSupport() {
        //Autooptimize
        add_filter('autoptimize_filter_js_dontmove', static function($excluded_files) {
            if (is_array($excluded_files)) {
                $excluded_files[] = 'wp-includes/js/dist/';
            }
            return $excluded_files;
        });

        //wp rocket
        add_filter('rocket_exclude_defer_js', static function($excluded_files) {
            if (is_array($excluded_files)) {
                $excluded_files[] = 'wp-includes/js/dist/';
            }
            return $excluded_files;
        });

        //Delete caches for supported plugins on visitor vote
        //Can't use is_singular() here because always return false
        add_action('yasr_action_on_visitor_vote',          array($this, 'deleteCaches'));
        add_action('yasr_action_on_visitor_multiset_vote', array($this, 'deleteCaches'));
    }


    /**
     * @author Dario Curvino <@dudo>
     * @since  refactored in 2.7.4
     *
     * @param $array_action_visitor_vote
     */
    public function deleteCaches($array_action_visitor_vote) {
        if (isset($array_action_visitor_vote['post_id'])) {
            $post_id = $array_action_visitor_vote['post_id'];
        }
        else {
            return;
        }

        if (isset($array_action_visitor_vote['is_singular'])) {
            $is_singular = $array_action_visitor_vote['is_singular'];
        }
        else {
            return;
        }

        //Adds support for wp super cache
        if (function_exists('wp_cache_post_change')) {
            wp_cache_post_change($post_id);
        }

        //Adds support for wp rocket, thanks to GeekPress
        //https://wordpress.org/support/topic/compatibility-with-wp-rocket-2
        if (function_exists('rocket_clean_post')) {
            rocket_clean_post($post_id);
        }

        //Adds support for LiteSpeed Cache plugin
        if (method_exists('\LiteSpeed\Purge', 'purge_post')) {
            (new LiteSpeed\Purge)->purge_post($post_id);
        }

        //Adds support for Wp Fastest Cache
        if ($is_singular === 'true') {
            if (isset($GLOBALS['wp_fastest_cache'])
                && method_exists(
                    $GLOBALS['wp_fastest_cache'], 'singleDeleteCache'
                )
            ) {
                $GLOBALS['wp_fastest_cache']->singleDeleteCache(false, $post_id);
            }
        }
        else {
            if (isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache')) {
                $GLOBALS['wp_fastest_cache']->deleteCache();
            }
        }

        //cache enabler support
        if (defined('CACHE_ENABLER_VERSION')) {
            if (class_exists('Cache_Enabler')) {
                //since Cache Enabler version 1.8.0
                if(method_exists('Cache_Enabler', 'clear_page_cache_by_post')){
                    Cache_Enabler::clear_page_cache_by_post($post_id);
                }
                //deprecated since Cache Enabler version 1.8.0
                else if(method_exists('Cache_Enabler', 'clear_page_cache_by_post_id')){
                    Cache_Enabler::clear_page_cache_by_post_id($post_id);
                }
            }
        }

    }

}