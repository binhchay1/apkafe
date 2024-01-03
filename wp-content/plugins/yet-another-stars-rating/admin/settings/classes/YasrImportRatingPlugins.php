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

class YasrImportRatingPlugins {

    //save here get_option yasr_plugin_imported
    public $plugin_imported;

    //The plugin nicename
    public $plugin_name;

    public function __construct() {
        if($this->plugin_imported === NULL) {
            $this->plugin_imported = get_option('yasr_plugin_imported');
        }
    }

    /**
     * Set the plugin name
     *
     * @author Dario Curvino <@dudo>
     * @since 3.1.6
     * @param $plugin_name
     */
    public function setPluginName($plugin_name) {
        $this->plugin_name = $plugin_name;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  3.1.6
     * @return mixed
     */
    public function getPluginName() {
        return $this->plugin_name;
    }

    /**
     * Add ajax action for plugin import
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.6
     */
    public function addAjaxActions () {
        add_action( 'wp_ajax_yasr_import_wppr', array($this, 'wpprAjaxCallback') );

        add_action( 'wp_ajax_yasr_import_kksr', array($this, 'kksrAjaxCallback') );

        add_action( 'wp_ajax_yasr_import_ratemypost', array($this, 'ratemypostAjaxCallback') );

        add_action( 'wp_ajax_yasr_import_mr', array($this, 'mrAjaxCallback') );
    }

    /**
     * Return true if wp post ratings is installed
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     * @return bool
     */
    public function searchWPPR() {
        //only check for active plugin, since import from table will be not used
        if (is_plugin_active('wp-postratings/wp-postratings.php')) {
            return true;
        }
        return false;
    }

    /**
     * Return true if KK star ratings is installed
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     * @return bool
     */
    public function searchKKSR() {
        //only check for active plugin, since import from table will be not used
        if (is_plugin_active('kk-star-ratings/index.php')) {
            return true;
        }
        return false;
    }

    /**
     * Return true if rate my post is installed
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     * @return bool
     */
    public function searchRMP() {
        if (is_plugin_active('rate-my-post/rate-my-post.php')) {
            return true;
        }
        global $wpdb;

        $rmp_table = $wpdb->prefix . 'rmp_analytics';

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE '%s'", $rmp_table)) === $rmp_table) {
            return true;
        }
        return false;
    }

    /**
     * Return true if multi rating is installed
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     * @return bool
     */
    public function searchMR() {
        //only check for active plugin, since import from table will be not used
        if (is_plugin_active('multi-rating/multi-rating.php')) {
            return true;
        }
        return false;
    }

    /**
     * If a supported rating plugin is found, define YASR_RATING_PLUGIN_FOUND with an array of plugins founds, or false otherwise
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.7
     * @return void
     */
    public function supportedPluginFound () {
        $plugin_to_import = array();

        $wppr_found = $this->searchWPPR();
        $rmp_found  = $this->searchRMP();
        $kksr_found = $this->searchKKSR();
        $mr_found   = $this->searchMR();

        if($wppr_found === true) {
            $plugin_to_import[] = 'wppr';
        }
        if($rmp_found === true) {
            $plugin_to_import[] = 'rmp';
        }
        if($kksr_found === true) {
            $plugin_to_import[] = 'kksr';
        }
        if($mr_found === true) {
            $plugin_to_import[] = 'mr';
        }

        if(!defined('YASR_RATING_PLUGIN_FOUND')) {
            if(!empty($plugin_to_import)) {
                define('YASR_RATING_PLUGIN_FOUND', json_encode($plugin_to_import));
            } else {
                define('YASR_RATING_PLUGIN_FOUND', false);
            }
        }
    }

    /**
     * Returns the number of necessary INSERT query for Wp post rating
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     * @return int|mixed
     */
    public function wpprQueryNumber() {
        $number_of_query_transient = get_transient('yasr_wppr_import_query_number');

        if ($number_of_query_transient !== false) {
            return $number_of_query_transient;
        }

        $logs = $this->returnWPPRData();

        //set counter to 0
        $i = 0;

        if (empty($logs)) {
            return 0;
        }

        //count insert queries
        foreach ($logs as $column) {
            for ($j = 1; $j <= $column->ratings_users; $j++) {
                $i++;
            }
        }

        set_transient('yasr_wppr_import_query_number', $i, DAY_IN_SECONDS);

        return $i;

    }

    /**
     * Returns the number of necessary INSERT query for KK star ratings
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     * @return int|mixed
     */
    public function kksrQueryNumber() {
        $number_of_query_transient = get_transient('yasr_kksr_import_query_number');

        if ($number_of_query_transient !== false) {
            return $number_of_query_transient;
        }

        $logs = $this->returnKKSRData();

        //set counter to 0
        $i = 0;

        if (empty($logs)) {
            return 0;
        }

        //count insert queries
        foreach ($logs as $column) {
            for ($j = 1; $j <= $column->ratings_users; $j++) {
                $i++;
            }
        }

        set_transient('yasr_kksr_import_query_number', $i, DAY_IN_SECONDS);

        return $i;

    }

    /**
     * Returns the number of necessary INSERT query for rate my post
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     * @return int|mixed
     */
    public function rmpQueryNumber() {
        global $wpdb;

        $number_of_query_transient = get_transient('yasr_rmp_import_query_number');

        if ($number_of_query_transient !== false) {
            return $number_of_query_transient;
        }

        $logs = $this->returnRMPData();

        if (empty($logs)) {
            return 0;
        }

        set_transient('yasr_rmp_import_query_number', $wpdb->num_rows, DAY_IN_SECONDS);

        return $wpdb->num_rows;

    }

    /**
     * Returns the number of necessary INSERT query for multi rating
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     * @return int|mixed
     */
    public function mrQueryNumber() {
        $number_of_query_transient = get_transient('yasr_mr_import_query_number');

        if ($number_of_query_transient !== false) {
            return $number_of_query_transient;
        }

        $logs = $this->returnMRData();

        //set counter to 0
        $i = 0;

        if (empty($logs)) {
            return 0;
        }

        //count insert queries
        foreach ($logs as $column) {
            for ($j = 1; $j <= $column->ratings_users; $j++) {
                $i++;
            }
        }
        set_transient('yasr_mr_import_query_number', $i, DAY_IN_SECONDS);

        return $i;

    }

    /**
     * Get WpPostRating Data
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     * @return array|int|object|\stdClass[]
     */
    public function returnWPPRData() {
        global $wpdb;

        $logs = $wpdb->get_results(
            "SELECT pm.post_id, 
                        MAX(CASE WHEN pm.meta_key = 'ratings_average' THEN pm.meta_value END) as ratings_average,
                        MAX(CASE WHEN pm.meta_key = 'ratings_users' THEN pm.meta_value END) as ratings_users
                   FROM $wpdb->postmeta as pm,
                         $wpdb->posts as p
                   WHERE pm.meta_key IN ('ratings_average', 'ratings_users')
                       AND pm.meta_value <> 0
                       AND pm.post_id = p.ID
                   GROUP BY pm.post_id
                   ORDER BY pm.post_id"
        );

        if (empty($logs)) {
            return 0;
        }

        return $logs;
    }

    /**
     * Get KK star rating data
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     * @return array|int|object|\stdClass[]
     */
    public function returnKKSRData() {
        global $wpdb;

        $logs = $wpdb->get_results(
            "SELECT pm.post_id, 
                        MAX(CASE WHEN pm.meta_key = '_kksr_avg' THEN pm.meta_value END) as ratings_average,
                        MAX(CASE WHEN pm.meta_key = '_kksr_casts' THEN pm.meta_value END) as ratings_users
                    FROM $wpdb->postmeta as pm,
                         $wpdb->posts as p
                    WHERE pm.meta_key IN ('_kksr_avg', '_kksr_casts')
                        AND pm.meta_value <> 0
                        AND pm.post_id = p.ID
                    GROUP BY pm.post_id
                    ORDER BY pm.post_id"
        );

        if (empty($logs)) {
            return 0;
        }

        return $logs;
    }

    /**
     * Get rate my post data
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     * @return array|int|object|\stdClass[]
     */
    public function returnRMPData() {
        global $wpdb;

        $rmp_table = $wpdb->prefix . 'rmp_analytics';

        //get logs
        $logs = $wpdb->get_results(
            "SELECT rmp.post AS post_id,
                       rmp.value as vote, 
                       rmp.time AS date,
                       p.ID
                    FROM $rmp_table AS rmp, 
                        $wpdb->posts AS p
                    WHERE rmp.post = p.id"
        );

        if (empty($logs)) {
            return 0;
        }

        return $logs;
    }

    /**
     * get multi rating data
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     * @return array|int|object|\stdClass[]
     */
    public function returnMRData() {
        global $wpdb;

        $logs = $wpdb->get_results(
            "SELECT pm.post_id, 
                        MAX(CASE WHEN pm.meta_key = 'mr_rating_results_star_rating' THEN pm.meta_value END) as ratings_average,
                        MAX(CASE WHEN pm.meta_key = 'mr_rating_results_count_entries' THEN pm.meta_value END) as ratings_users
                    FROM $wpdb->postmeta as pm,
                         $wpdb->posts as p
                    WHERE pm.meta_key IN ('mr_rating_results_star_rating', 'mr_rating_results_count_entries')
                        AND pm.meta_value <> 0
                        AND pm.post_id = p.ID
                    GROUP BY pm.post_id 
                    ORDER BY pm.post_id"
        );

        if (empty($logs)) {
            return 0;
        }

        return $logs;
    }

    /**
     * Ajax callback for import data from WordPress post Ratings
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     */
    public function wpprAjaxCallback() {
        if($_POST['nonce']) {
            $nonce = $_POST['nonce'];
        } else {
            exit();
        }

        if (!wp_verify_nonce( $nonce, 'yasr-import-wppr-action' ) ) {
            die('Error while checking nonce');
        }

        if (!current_user_can( 'manage_options' ) ) {
            die(esc_html__( 'You do not have sufficient permissions to access this page.', 'yet-another-stars-rating' ));
        }

        global $wpdb;

        //get logs
        //With Wp Post Rating I need to import postmeta.
        //It has his own table too, but can be disabled in the settings.
        //The only way to be sure is get the postmeta

        $logs = $this->returnWPPRData();

        if(empty($logs)) {
            echo json_encode(esc_html__('No WP Post Rating data found'));
        } else {
            $result = false;

            /****** Insert logs ******/
            foreach ($logs as $column) {

                if($column->ratings_average > 5) {
                    $column->ratings_average = 5;
                }

                for ($i=1; $i<=$column->ratings_users; $i++) {

                    //check if rating_average is not null.
                    //I found out that sometimes Wp Post Rating can save value with null data (sigh!!)
                    if ($column->ratings_average !== null) {

                        $result = $wpdb->replace(
                            YASR_LOG_TABLE,
                            array(
                                'post_id'      => $column->post_id,
                                'user_id'      => 0, //not stored on wp post rating
                                'vote'         => $column->ratings_average,
                                'date'         => 'wppostrating', //not stored on wp post rating
                                'ip'           => 'wppostrating'//not stored on wp post rating
                            ),
                            array('%d', '%d', '%f', '%s', '%s')
                        );
                    }
                }
            }

            if ($result) {
                $this->savePluginImported('wppr');

                $string_to_return = esc_html__('Woot! All data have been imported!', 'yet-another-stars-rating');
                echo json_encode($string_to_return);
            }

        }
        die();
    }

    /**
     * Ajax callback for import data from KK Star Ratings
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     */
    public function kksrAjaxCallback() {
        if($_POST['nonce']) {
            $nonce = $_POST['nonce'];
        } else {
            exit();
        }

        if (!wp_verify_nonce( $nonce, 'yasr-import-kksr-action' ) ) {
            die('Error while checking nonce');
        }

        if (!current_user_can( 'manage_options' ) ) {
            die(esc_html__( 'You do not have sufficient permissions to access this page.', 'yet-another-stars-rating' ));
        }

        global $wpdb;

        //get logs
        //With KK star rating I need to import postmeta.
        $logs= $this->returnKKSRData();

        if(empty($logs)) {
            echo json_encode(esc_html__('No KK Star Ratings data found'));
        } else {
            $result = false;

            /****** Insert logs ******/
            foreach ($logs as $column) {
                if($column->ratings_average > 5) {
                    $column->ratings_average = 5;
                }

                for ($i=1; $i<=$column->ratings_users; $i++) {
                    $result = $wpdb->replace(
                        YASR_LOG_TABLE,
                        array(
                            'post_id'      => $column->post_id,
                            'user_id'      => 0, //not stored on KK star rating
                            'vote'         => $column->ratings_average,
                            'date'         => 'kkstarratings', //not stored KK star rating
                            'ip'           => 'kkstarratings'//not stored KK star rating
                        ),
                        array('%d', '%d', '%f', '%s', '%s')
                    );
                }
            }

            if ($result) {
                $this->savePluginImported('kksr');

                $string_to_return = esc_html__('Woot! All data have been imported!', 'yet-another-stars-rating');
                echo json_encode($string_to_return);
            }

        }
        die();
    }

    /**
     * Ajax callback for import data from rate My Post
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     */
    public function ratemypostAjaxCallback() {
        if($_POST['nonce']) {
            $nonce = $_POST['nonce'];
        } else {
            exit();
        }

        if (!wp_verify_nonce($nonce, 'yasr-import-rmp-action')) {
            die('Error while checking nonce');
        }

        if (!current_user_can( 'manage_options' ) ) {
            die(esc_html__( 'You do not have sufficient permissions to access this page.', 'yet-another-stars-rating' ));
        }

        global $wpdb;

        //get logs
        $logs=$this->returnRMPData();

        if(empty($logs)) {
            echo json_encode(esc_html__('No Rate My Post data found'));
        } else {
            $result = false;

            /****** Insert logs ******/
            foreach ($logs as $column) {
                $result = $wpdb->replace(
                    YASR_LOG_TABLE,
                    array(
                        'post_id'      => $column->post_id,
                        'user_id'      => 0, //seems like rate my post store all users like -1, so I cant import the user_id
                        'vote'         => $column->vote,
                        'date'         => $column->date,
                        'ip'           => 'ratemypost'
                    ),
                    array('%d', '%d', '%f', '%s', '%s')
                );
            }

            if ($result) {
                $this->savePluginImported('rmp');

                $string_to_return = esc_html__('Woot! All data have been imported!', 'yet-another-stars-rating');
                echo json_encode($string_to_return);
            }
        }
        die();
    }

    /**
     * Ajax callback for import data from multi rating
     *
     * @author Dario Curvino <@dudo>
     * @since  2.0.0
     */
    public function mrAjaxCallback() {
        if($_POST['nonce']) {
            $nonce = $_POST['nonce'];
        } else {
            exit();
        }

        if (!wp_verify_nonce( $nonce, 'yasr-import-mr-action' ) ) {
            die('Error while checking nonce');
        }

        if (!current_user_can( 'manage_options' ) ) {
            die(esc_html__( 'You do not have sufficient permissions to access this page.', 'yet-another-stars-rating' ));
        }

        global $wpdb;

        //get logs
        //With Multi Rating I need to import postmeta.
        $logs= $this->returnMRData();

        if(empty($logs)) {
            echo json_encode(esc_html__('No Multi Rating data found'));
        } else {
            $result = false;

            /****** Insert logs ******/
            foreach ($logs as $column) {

                if($column->ratings_average > 5) {
                    $column->ratings_average = 5;
                }

                for ($i=1; $i<=$column->ratings_users; $i++) {
                    $result = $wpdb->replace(
                        YASR_LOG_TABLE,
                        array(
                            'post_id'      => $column->post_id,
                            'user_id'      => 0, //not stored on KK star rating
                            'vote'         => $column->ratings_average,
                            'date'         => 'multirating', //not stored KK star rating
                            'ip'           => 'multirating'//not stored KK star rating
                        ),
                        array('%d', '%d', '%f', '%s', '%s')
                    );
                }
            }

            if ($result) {
                $this->savePluginImported('mr');

                $string_to_return = esc_html__('Woot! All data have been imported!', 'yet-another-stars-rating');
                echo json_encode($string_to_return);
            }

        }

        die();
    }

    /**
     * Returns an alert box
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.6
     *
     * @param $number_of_queries
     *
     * @return string
     */
    public function alertBox($number_of_queries) {
        $plugin_name = $this->getPluginName();

        $div  =  '<div class="yasr-alert-box">';
        $div .= sprintf(__(
            'To import %s seems like %s %d %s INSERT queries are necessary. %s
                There is nothing wrong with that, but some hosting provider can have a query limit/hour. %s
                I strongly suggest to contact your hosting and ask about your plan limit',
            'yet-another-stars-rating'
        ), $plugin_name, '<strong>', $number_of_queries, '</strong>', '<br />','<br />');
        $div .= '</div>';

        return $div;
    }

    /**
     * Insert option yasr_plugin_imported
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.6
     * @param $plugin
     */
    public function savePluginImported($plugin) {
        //get actual data
        $plugin_imported = $this->plugin_imported;

        //Since php 8.1, it is not possible anymore to automatically convert false into array, so I need to declare it first
        //if plugin_imported === false
        //https://wiki.php.net/rfc/autovivification_false
        if($plugin_imported === false) {
            $plugin_imported = array();
        }
        //Add plugin just imported as a key
        $plugin_imported[$plugin] = array('date' => date('Y-m-d H:i:s'));
        //update option
        update_option('yasr_plugin_imported', $plugin_imported, false);
    }

    /**
     * Print fields to import Wp PostRatings
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.6
     */
    public function importWPPR () {
        $this->setPluginName('WP-PostRatings');

        echo wp_kses_post($this->pluginFoundTitle());

        $number_of_stars = (int)get_option('postratings_max', false);

        if ($number_of_stars && $number_of_stars !== 5) {
            $error  = '<div class="yasr-indented-answer" style="margin-top: 10px;">';
            $error .= sprintf(__('You\' re using a star set different from 5 %s
                                Import can not be done', 'yet-another-stars-rating'), '<br />');
            $error .= '</div>';
            echo wp_kses_post($error);
        } else {
            echo wp_kses_post($this->noteAverageRating());

            $wppr_imported = $this->alreadyImported($this->plugin_imported, 'wppr');

            if($wppr_imported !== false) {
                echo wp_kses_post($wppr_imported);
            } else {
                $number_of_queries_wppr = (int) $this->wpprQueryNumber();

                if ($number_of_queries_wppr > 1000) {
                    echo wp_kses_post(
                        $this->alertBox($number_of_queries_wppr)
                    );
                }
                $this->htmlImportButton('wppr');
            }
        }
    }

    /**
     * Print fields to import KKSR
     * 
     * @author Dario Curvino <@dudo>
     * @since 3.1.6
     */
    public function importKKSR () {
        $this->setPluginName('KK Star Ratings');

        echo wp_kses_post($this->pluginFoundTitle());
        echo wp_kses_post($this->noteAverageRating());
        $kksr_imported = $this->alreadyImported($this->plugin_imported, 'kksr');

        if($kksr_imported !== false) {
            echo wp_kses_post($kksr_imported);
        }
        else {
            $number_of_queries_kksr = (int)$this->kksrQueryNumber();

            if($number_of_queries_kksr > 1000) {
                echo wp_kses_post($this->alertBox($number_of_queries_kksr));
            }
            $this->htmlImportButton('kksr');
        }
    }

    /**
     * Print fields to import Rate My post
     * 
     * @author Dario Curvino <@dudo>
     * @since  3.1.6
     */
    public function importRMP () {
        $this->setPluginName('Rate My Post');

        echo wp_kses_post($this->pluginFoundTitle());
        $rmp_imported = $this->alreadyImported($this->plugin_imported, 'rmp');

        if($rmp_imported !== false) {
            echo wp_kses_post($rmp_imported);
        }
        else {
            $number_of_queries_rmp = (int)$this->rmpQueryNumber();

            if($number_of_queries_rmp > 1000) {
                echo wp_kses_post($this->alertBox($number_of_queries_rmp));
            }
            $this->htmlImportButton('rmp');
        }
    }

    /**
     * Print fields to import Multi Rating
     *
     * @author Dario Curvino <@dudo>
     * @since 3.1.6
     */
    public function importMR() {
        $this->setPluginName('Multi Rating');

        echo wp_kses_post($this->pluginFoundTitle());
        echo wp_kses_post($this->noteAverageRating());
        $mr_imported = $this->alreadyImported($this->plugin_imported, 'mr');

        if($mr_imported !== false) {
            echo wp_kses_post($mr_imported);
        }
        else {
            $number_of_queries_mr = (int) $this->mrQueryNumber();

            if ($number_of_queries_mr > 1000) {
                echo wp_kses_post($this->alertBox($number_of_queries_mr));
            }
            $this->htmlImportButton('mr');
        }
    }
    
    /**
     * Return a span with title of the plugin found
     *
     * @author Dario Curvino <@dudo>
     * @since 3.1.6
     *
     * @return string
     */
    public function pluginFoundTitle() {
        $plugin_name = $this->getPluginName();
        if($plugin_name === '') {
            $class = 'title-noplugin-found';
            $text  = __('No supported plugin has been found' , 'yet-another-stars-rating');
        } else {
            $class = 'title-plugin-found';
            $text  = __('Plugin found:' , 'yet-another-stars-rating');
        }
        return (
                "<div class='$class'>
                    $text $plugin_name
                </div>"
        );
    }

    /**
     * Returns a note to explain ho data is imported if a plugin doesn't have a full log
     *
     * @author Dario Curvino <@dudo>
     * @since  3.1.6
     * @return string
     */
    public function noteAverageRating() {
        $plugin_name = $this->getPluginName();

        $head = sprintf(__(
            '%s Please note: %s depending on the settings, %s may save data in different ways.',
            'yet-another-stars-rating'),
            '<strong>', '</strong>', $plugin_name
        ). '<br />';
        $further_info = '';

        if($plugin_name === 'KK Star Ratings') {
            $head = sprintf(__(
                    '%s Please note: %s KK Star Ratings doesn\'t save information about the single vote.',
                'yet-another-stars-rating'),
                '<strong>', '</strong>'
            ) . '<br />';

            $further_info = '<br />' .  __('If you use a rating scale different than 1 to 5, all ratings will be 
            converted to work with a 5 ratings star scale.');
        }

        $info  = '<div class="yasr-indented-answer">';
        $info .= $head;
        $info .= sprintf(__(
            'The only way to be sure to get ALL data is, for every single post or page, getting the total 
            number of votes, and save the current average as the rating for all votes. %s
            E.g. A post has 130 votes with an average of 4.4: since is impossible to know the single rating,
            YASR will import 130 votes with an average of 4.4. %s
            Because of this, statistics in front end will be disabled for all post or page published before 
            the import.',
            'yet-another-stars-rating'
        ), '<br />', '<br />');
        $info .= $further_info;
        $info .='</div>';

        return $info;
    }

    /**
     * Return an "already imported" message with date
     *
     * @author Dario Curvino <@dudo>
     * @since 3.1.6
     * @param $plugin_imported_option  | value from get_option('yasr_plugin_imported');
     * @param $plugin_key              | plugin key to search
     *
     * @return false|string
     */
    public function alreadyImported($plugin_imported_option, $plugin_key) {
        $plugin_name = $this->getPluginName();

        if (is_array($plugin_imported_option) && array_key_exists($plugin_key, $plugin_imported_option)) {
            return(
                '<div class="yasr-indented-answer" style="margin-top: 10px;">'
                    . sprintf(__('You\'ve already imported %s data on', 'yet-another-stars-rating'), $plugin_name) .
                    '&nbsp;<strong>' . $plugin_imported_option[$plugin_key]['date'] . '</strong>
                </div>'
            );
        }

        return false;
    }

    /**
     * Print the import button
     *
     * @author Dario Curvino <@dudo>
     * @since 3.1.6
     * @param $plugin_key
     */
    public function htmlImportButton($plugin_key) {
        $button_id  = 'yasr-import-'.$plugin_key.'-submit';
        $nonce_name = 'yasr-import-'.$plugin_key.'-action';
        $id_nonce   = 'yasr-import-'.$plugin_key.'-nonce';
        $id_answer  = 'yasr-import-'.$plugin_key.'-answer';
        $nonce      = wp_create_nonce($nonce_name);

        ?>
        <div class="yasr-indented-answer">
            <button class="button-primary" id="<?php echo esc_attr($button_id);?>">
                <?php esc_html_e('Import data', 'yet-another-stars-rating') ?>
            </button>
            <input type="hidden" id="<?php echo esc_attr($id_nonce)?>" value="<?php echo esc_attr($nonce) ?>">
        </div>
        <div id="<?php echo esc_attr($id_answer)?>" class="yasr-indented-answer">
        </div>
        <div class="yasr-space-settings-div">
        </div>
        <?php
    }
}