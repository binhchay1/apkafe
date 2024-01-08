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
 * This function adds ajax action needed for shortcodes
 *
 * @author Dario Curvino <@dudo>
 * @since 2.7.7
 * Class YasrShortcodesAjax
 */
class YasrShortcodesAjax {
    public function init() {
        if (YASR_ENABLE_AJAX === 'yes') {
            //load vv with ajax
            add_action('wp_ajax_yasr_load_vv',        array('YasrShortcodesAjax', 'returnArrayVisitorVotes'));
            add_action('wp_ajax_nopriv_yasr_load_vv', array('YasrShortcodesAjax', 'returnArrayVisitorVotes'));

            add_action('wp_ajax_yasr_load_rankings',        array('YasrShortcodesAjax', 'rankingData'));
            add_action('wp_ajax_nopriv_yasr_load_rankings', array('YasrShortcodesAjax', 'rankingData'));
        }

        //VV save rating
        add_action('wp_ajax_yasr_send_visitor_rating',        array($this, 'saveVV'));
        add_action('wp_ajax_nopriv_yasr_send_visitor_rating', array($this, 'saveVV'));

        //die if post status is non publish
        add_action('yasr_action_on_visitor_vote',             array($this, 'dieIfPrivatePost'));
        add_action('yasr_action_on_visitor_multiset_vote',    array($this, 'dieIfPrivatePost'));

        //MV save rating
        add_action('wp_ajax_yasr_visitor_multiset_field_vote',        array($this, 'saveMV'));
        add_action('wp_ajax_nopriv_yasr_visitor_multiset_field_vote', array($this, 'saveMV'));

        $yasr_log_widget = new YasrLastRatingsWidget();
        //yasr_user_rate_history action to change page
        add_action('wp_ajax_yasr-user_change_log_page',      array($yasr_log_widget, 'returnAjaxResponse'));

        //VV load stats
        if(YASR_VISITORS_STATS === 'yes') {
            add_action('wp_ajax_yasr_stats_visitors_votes',        array($this, 'returnVVStats'));
            add_action('wp_ajax_nopriv_yasr_stats_visitors_votes', array($this, 'returnVVStats'));
        }

    }

    /**
     * Save or update rating for yasr_visitor_votes
     *
     * @author Dario Curvino <@dudo>
     * @since  refactor in 2.7.7
     */
    public function saveVV() {
        $this->dieIfNotAjax();

        $this->vvDieIfNotValidData();

        $post_id     = (int) $_POST['post_id'];
        $is_singular = $_POST['is_singular'];

        $this->vvDieIfNonceInvalid();

        $this->actionOnVV($post_id, $is_singular);

        $this->vvDieIfNotAllowed();

        $rating = yasr_validate_rating((int) $_POST['rating']);

        if (is_user_logged_in()) {
            $result_insert_log = $this->saveVVLoggedIn($post_id, get_current_user_id(), $rating);

        } //if user is not logged in insert
        else {
            $result_insert_log = $this->saveVVAnonymous($post_id, $rating);
        }

        if ($result_insert_log !== false) {
            echo $this->vvReturnResponse($post_id, $rating, $result_insert_log);
        } else {
            echo $this->returnErrorResponse(__('Error in Ajax Call, rating can\'t be saved',
                'yet-another-stars-rating'));
        }

        die(); // this is required to return a proper result

    }

    /**
     * Echo an error and die if rating or post id are missing in $_POST
     *
     * @author Dario Curvino <@dudo>
     *
     * @since  3.4.4
     * @return void
     */
    private function vvDieIfNotValidData() {
        if (!isset($_POST['rating']) || !isset($_POST['post_id'])) {
            echo $this->returnErrorResponse(__('Error in Ajax Call, missing required param.', 'yet-another-stars-rating'));
            die();
        }
    }

    /**
     * Validate the nonce
     *
     * @author Dario Curvino <@dudo>
     *
     * @since  3.4.4
     * @return void
     */
    private function vvDieIfNonceInvalid () {
        if(isset($_POST['nonce_visitor'])) {
            $nonce_visitor = $_POST['nonce_visitor'];
        } else {
            $nonce_visitor = false;
        }

        $nonce_response = self::validNonce($nonce_visitor, 'yasr_nonce_vv');
        if($nonce_response !== true) {
            die($nonce_response);
        }
    }


    /**
     * Create an array and add an action to perform on vv
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.4.4
     *
     * @param $post_id
     * @param $is_singular
     *
     * @return void
     */
    private function actionOnVV($post_id, $is_singular) {
        $array_action_visitor_vote = array('post_id' => $post_id, 'is_singular' => $is_singular);

        /**
         * Hook here to add an action on visitor votes (e.g. empty cache)
         * @param array $array_action_visitor_vote An array containing post_id and is_singular
         */
        do_action('yasr_action_on_visitor_vote', $array_action_visitor_vote);
    }

    /**
     * @author Dario Curvino <@dudo>
     *
     * Die if user not allowed to rate
     *
     * @since 3.4.4
     * @return void
     */
    private function vvDieIfNotAllowed() {
        if(YASR_ALLOWED_USER === 'logged_only' && !is_user_logged_in()) {
            echo ($this->returnErrorResponse(__('Only logged in user can rate.', 'yet-another-stars-rating')));
            die();
        }
    }


    /**
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.9
     *
     * @param $post_id
     * @param $current_user_id
     * @param $rating
     *
     * @return false|string
     */
    private function saveVVLoggedIn($post_id, $current_user_id, $rating) {
        //try to update first, if fails the do the insert
        $update = YasrDB::vvUpdateRating($post_id, $current_user_id, $rating);

        //do not use identical operator here
        if($update) {
            return 'updated';
        }

        //insert the new row
        $insert = YasrDB::vvSaveRating($post_id, $current_user_id, $rating);

        if($insert) {
            return 'inserted';
        }

        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.9
     *
     * @param $post_id
     * @param $rating
     *
     * @return false|string
     */
    private function saveVVAnonymous($post_id, $rating) {
        $this->dieIfIpBlocked($post_id);

        $result_insert_log = YasrDB::vvSaveRating($post_id, 0, $rating);

        if($result_insert_log) {
            return 'inserted';
        }

        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     *
     * @param $post_id
     * @param $rating
     * @param $result_update_log
     *
     * @return string
     */
    public function vvReturnResponse($post_id, $rating, $result_update_log) {
        $row_exists = YasrDB::visitorVotes($post_id);

        $number_of_votes = $row_exists['number_of_votes'];
        $medium_rating   = $row_exists['average'];

        //customize visitor_votes cookie name
        $cookiename = apply_filters('yasr_vv_cookie', 'yasr_visitor_vote_cookie');

        $data_to_save = array(
            'post_id' => $post_id,
            'rating'  => $rating
        );

        yasr_setcookie($cookiename, $data_to_save);

        $rating_saved_text = '';

        //Default text when rating is saved
        if ($result_update_log === 'updated') {
            $rating_saved_text = apply_filters('yasr_vv_updated_text', $rating_saved_text);
        }
        else {
            $rating_saved_text = apply_filters('yasr_vv_saved_text', $rating_saved_text);
        }

        return json_encode(array(
            'status'            => 'success',
            'number_of_votes'   => $number_of_votes,
            'average_rating'    => $medium_rating,
            'text'              => wp_kses_post($rating_saved_text)
        ));
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 3.0.4
     * @param string|bool $error_text
     *
     * @return string
     */
    public function returnErrorResponse ($error_text) {
        if(!$error_text) {
            $error_text = esc_html__('Error in Ajax Call, rating can\'t be saved.', 'yet-another-stars-rating');
        }

        return json_encode(array(
            'status' => 'error',
            'text'   => wp_kses_post($error_text)
        ));
    }

    /**
     * Return response for Ajax and Rest API
     *
     * @author Dario Curvino <@dudo>
     * @since  moved in YasrShortcodeAjax since 2.7.7
     * @return array
     */
    public static function returnArrayVisitorVotes() {
        $post_id = false;
        if (isset($_GET['post_id'])) {
            $post_id = (int)$_GET['post_id'];
        }

        //default values
        $array_to_return = array(
            'stars_attributes' => array(
                'read_only'   => true,
                'span_bottom' => false
            )
        );

        $cookie_value  = YasrVisitorVotes::checkCookie($post_id);
        $stars_enabled = YasrShortcode::starsEnalbed($cookie_value);

        //if user is enabled to rate, readonly must be false
        if ($stars_enabled === 'true_logged' || $stars_enabled === 'true_not_logged') {
            $array_to_return['stars_attributes']['read_only'] = false;
        }

        $array_to_return['stars_attributes']['span_bottom'] = YasrVisitorVotes::showTextBelowStars($cookie_value, $post_id);

        $array_visitor_votes = YasrDB::visitorVotes($post_id);

        $array_to_return['number_of_votes'] = $array_visitor_votes['number_of_votes'];
        $array_to_return['sum_votes']       = $array_visitor_votes['sum_votes'];

        //this means is an ajax call
        if (wp_doing_ajax() === true) {
            $array_to_echo['yasr_visitor_votes'] = $array_to_return;
            echo json_encode($array_to_echo);
            die();
        }

        //return rest response
        return $array_to_return;
    }

    /**
     * @author Dario Curvino <@dudo>
     *
     * @since 3.4.4
     *
     * @param $array_action_visitor_vote
     *
     * @return void
     */
    public function dieIfPrivatePost($array_action_visitor_vote) {
        $post_id = $array_action_visitor_vote['post_id'];
        if(!is_user_logged_in() || !current_user_can(YASR_USER_CAPABILITY_EDIT_POST)) {
            $status = get_post_status($post_id);

            if ($status !== 'publish') {
                echo $this->returnErrorResponse(__("This post doesn't exists or is private", 'yet-another-stars-rating'));
                die();
            }
        }
    }


    /**
     * Save or update rating for yasr_visitor_multiset
     *
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     */
    public function saveMV() {
        $this->dieIfNotAjax();

        if (isset($_POST['post_id']) && isset($_POST['rating']) && isset($_POST['set_id'])) {
            $post_id  = (int) $_POST['post_id'];
            $rating   = $_POST['rating'];
            $set_id   = (int) $_POST['set_id'];
            $nonce    = $_POST['nonce'];

            $rating_without_backslash = str_replace('\\', '', $rating);
            $rating_array_decoded     = json_decode($rating_without_backslash, true);

            if (!is_array($rating_array_decoded)) {
                die($this->returnErrorResponse(__('Error with rating', 'yet-another-stars-rating')));
            }
        } else {
            die($this->returnErrorResponse(__('Missing required param', 'yet-another-stars-rating')));
        }

        $nonce_response = self::validNonce($nonce, 'yasr_nonce_insert_visitor_rating_multiset');
        if($nonce_response !== true) {
            die ($nonce_response);
        }

        $current_user_id = get_current_user_id();

        $array_action_visitor_multiset_vote = array('post_id' => $post_id);

        do_action('yasr_action_on_visitor_multiset_vote', $array_action_visitor_multiset_vote);

        //clean array, so if a user rate same field twice, take only the last rating
        $cleaned_array = yasr_unique_multidim_array($rating_array_decoded, 'field');

        //this is a counter: if at the end of the foreach it still 0, means that an user rated in a set
        //and then submit another one
        $counter_matched_fields = 0;

        foreach ($cleaned_array as $rating_values) {
            $rating_postid = (int)$rating_values['postid'];
            $rating_setid  = (int)$rating_values['setid'];

            //check if the set id in the array is the same of the clicked
            if ($rating_postid === $post_id && $rating_setid === $set_id) {
                //increase the counter
                $counter_matched_fields = $counter_matched_fields + 1;

                $id_field = (int)$rating_values['field'];
                $rating   = $rating_values['rating'];

                //if the user is logged
                if(is_user_logged_in()) {
                    $this->saveMVLoggedIn($id_field, $set_id, $post_id, $rating, $current_user_id);
                }
                //else try to insert vote
                else {
                    $this->saveMVAnonymous($id_field, $set_id, $post_id, $rating, $current_user_id);
                }
            } //End if $rating_values['postid'] == $post_id

        } //End foreach ($rating as $rating_values)

        if ($counter_matched_fields === 0) {
            die($this->returnErrorResponse(
                esc_html__('Error, most probably you submitted the wrong set', 'yet-another-stars-rating'))
            );
        }

        //echo response
        die($this->mvReturnResponse($post_id, $set_id));

    } //End callback function

    /**
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.9
     *
     * @param $id_field
     * @param $set_id
     * @param $post_id
     * @param $rating
     * @param $current_user_id
     *
     * @return void
     */
    private function saveMVLoggedIn ($id_field, $set_id, $post_id, $rating, $current_user_id) {
        //first try to update the vote
        $update_query_success = YasrDB::mvUpdateRating($id_field, $set_id, $post_id, $rating, $current_user_id);

        //use ! instead of === FALSE
        if (!$update_query_success) {
            //insert as new rating
            $insert_query_success = YasrDB::mvSaveRating($id_field, $set_id, $post_id, $rating, $current_user_id);
            //if rating is not saved, it is an error
            if (!$insert_query_success) {
                die($this->returnErrorResponse(esc_html__("Error in Ajax Call, rating can't be saved.", 'yet-another-stars-rating')));
            }
        }
    }

    /**
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.9
     *
     * @param $id_field
     * @param $set_id
     * @param $post_id
     * @param $rating
     * @param $current_user_id
     *
     * @return void
     */
    private function saveMVAnonymous($id_field, $set_id, $post_id, $rating, $current_user_id) {
        $this->dieIfIpBlocked($post_id, $set_id);

        $replace_query_success = YasrDB::mvSaveRating($id_field, $set_id, $post_id, $rating, $current_user_id);
        //if rating is not saved, it is an error
        if (!$replace_query_success) {
            die($this->returnErrorResponse(esc_html__("Error in Ajax Call, rating can't be saved.", 'yet-another-stars-rating')));
        }
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.2
     *
     * @param $post_id
     * @param $set_id
     *
     * @return string
     */
    public function mvReturnResponse ($post_id, $set_id) {
        $cookiename = apply_filters('yasr_mv_cookie', 'yasr_multi_visitor_cookie');

        $data_to_save = array(
            'post_id' => $post_id,
            'set_id'  => $set_id
        );

        yasr_setcookie($cookiename, $data_to_save);

        $rating_saved_text = apply_filters('yasr_mv_saved_text', __('Rating Saved', 'yet-another-stars-rating'));

        return json_encode(array(
            'status'    => 'success',
            'text'      => wp_kses_post($rating_saved_text)
        ));
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     */
    public function returnVVStats() {
        $this->dieIfNotAjax();

        if (isset($_POST['post_id']) && $_POST['post_id'] !== '') {
            $post_id = (int)$_POST['post_id'];
        }
        else {
            echo json_encode(array(
                'status'    => 'error',
                'text'      => 'Missing Post ID'
            ));
            die();
        }

        $votes_array    = YasrDB::visitorVotes($post_id);
        $average_rating = $votes_array['average'];

        $missing_vote  = null; //avoid undefined variable

        global $wpdb;

        //create an empty array
        $existing_votes = array();

        $stats = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ROUND(vote, 0) as vote,
                       COUNT(vote)    as n_of_votes
                FROM " . YASR_LOG_TABLE . "
                    WHERE post_id=%d
                    AND   vote > 0
                    AND   vote <= 5
                GROUP BY vote
                ORDER BY vote DESC
                ", $post_id
            ), ARRAY_A
        );

        $total_votes = 0; //Avoid undefined variable if stats exists. Necessary if $stats not exists

        //if query return 0 write an empty array $existing_votes
        if ($stats) {
            //Write a new array with only existing votes, and count all the number of votes
            foreach ($stats as $votes_array) {
                $existing_votes[] = $votes_array['vote'];//Create an array with only existing votes
                $total_votes      = $total_votes + $votes_array['n_of_votes'];
            }
        }

        for ($i = 1; $i <= 5; $i++) {
            //If query return 0 write a new $stats array with index
            if (!$stats) {
                $stats[$i]               = array();
                $stats[$i]['vote']       = $i;
                $stats[$i]['n_of_votes'] = 0;
            }
            else {
                //If in the new array there are some vote missing create a new array
                /** @noinspection TypeUnsafeArraySearchInspection */
                if (!in_array($i, $existing_votes)) {
                    $missing_vote[$i]               = array();
                    $missing_vote[$i]['vote']       = $i;
                    $missing_vote[$i]['n_of_votes'] = 0;
                }
            }
        }

        //If missing_vote exists merge it
        if ($missing_vote) {
            $stats = array_merge($stats, $missing_vote);
        }

        arsort($stats); //sort it by $votes[n_of_votes]

        if ($total_votes === 0) {
            $increase_bar_value = 0;
        }
        else {
            $increase_bar_value = 100 / $total_votes; //Find how much all the bars should increase per vote
        }

        $i = 5;

        $array_to_return = array(
            'status'        => 'success',
            'medium_rating' => $average_rating
        );

        foreach ($stats as $logged_votes) {
            //cast int
            $logged_votes['n_of_votes'] = (int)$logged_votes['n_of_votes'];

            $value_progressbar = $increase_bar_value * $logged_votes['n_of_votes']; //value of the single bar
            $value_progressbar = round($value_progressbar, 2) . '%'; //use only 2 decimal

            $array_to_return[$i]['progressbar'] = $value_progressbar;
            $array_to_return[$i]['n_of_votes']  = $logged_votes['n_of_votes'];
            $array_to_return[$i]['vote']        = $logged_votes['vote'];

            $i--;

            //if there is a 0 rating in the database (only possible if manually added) break foreach
            if ($i < 1) {
                break;
            }
        } //End foreach

        echo json_encode($array_to_return);

        die();
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since        2.8.0
     * @param        $nonce
     * @param        $action_name
     * @param string $error
     * @return string|bool;
     */
    public static function validNonce($nonce, $action_name, $error=false) {
        if (is_user_logged_in() && !wp_verify_nonce($nonce, $action_name)){
            if (!$error) {
                $error = __('Wrong nonce. Rating can\'t be updated', 'yet-another-stars-rating');
            } else {
                $error = sanitize_text_field($error);
            }
            $error_nonce = array(
                'status' => 'error',
                'text'   => $error
            );

            return json_encode($error_nonce);
        }
        return true;
    }


    /**
     * This function returns ranking data for both rest and ajax requests
     *
     * @author Dario Curvino <@dudo>
     * @since 2.7.9
     * @param bool|string $source
     * @param bool|array  $request
     *
     * @return array|false|false[]
     */
    public static function rankingData($source=false, $request=false) {
        $is_ajax       = wp_doing_ajax();
        $nonce_visitor = false;

        if (isset($_GET['action']) && isset($_GET['source']) && isset($_GET['nonce_rankings'])) {
            $request       = $_GET;
            $source        = (string)$_GET['source'];
            $nonce_visitor = $_GET['nonce_rankings'];
        }

        if($is_ajax === true) {
            $error          = esc_html__('Can\'t show rankings, wrong nonce.', 'yet-another-stars-rating');
            $nonce_response = self::validNonce($nonce_visitor, 'yasr_nonce_rankings', $error);
            if ($nonce_response !== true) {
                die ($nonce_response);
            }
        }

        $data_to_return = array(
            'source' => $source
        );

        //hook here to add more params
        $sql_params = apply_filters('yasr_filter_ranking_request', false, $request);

        if($source === 'overall_rating') {
            $overall_data = YasrDB::rankingOverall($sql_params);
            if($overall_data === false){
                $data_to_return = false;
            }
            else {
                $data_to_return['data_overall'] = YasrRankings::rankingData($overall_data);
            }
        }

        if($source === 'visitor_votes') {
            //outside 'most', only 'highest' is allowed
            $ranking                = ($request['show'] === 'highest') ? $request['show'] : 'most';
            $data_to_return['show'] = $ranking;

            $vv_data = YasrDB::rankingVV($sql_params, $ranking);
            if ($vv_data === false) {
                $data_to_return = false;
            }
            else {
                $data_to_return['data_vv'] = YasrRankings::rankingData($vv_data);
            }
        }

        if($source === 'author_multi') {
            $am_data = YasrDB::rankingMulti($request['setid'], $sql_params);
            if($am_data === false){
                $data_to_return = false;
            }
            else {
                $data_to_return['data_mv'] = YasrRankings::rankingData($am_data);
            }
        }

        if($source === 'visitor_multi') {
            //outside 'most', only 'highest' is allowed
            $ranking                = ($request['show'] === 'highest') ? $request['show'] : 'most';
            $data_to_return['show'] = $ranking;

            $vm_data = YasrDB::rankingMultiVV($request['setid'], $ranking, $sql_params);
            if($vm_data === false){
                $data_to_return = false;
            }
            else {
                $data_to_return['data_vv'] = YasrRankings::rankingData($vm_data);
            }
        }

        //Use this hook to works with more $sources
        $data_to_return = apply_filters('yasr_add_sources_ranking_request', $data_to_return, $source, $request, $sql_params);

        //if this is coming from an ajax request
        if($is_ajax === true) {
            wp_send_json($data_to_return);
        }

        return $data_to_return;

    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  3.0.5
     */
    private function dieIfNotAjax() {
        if(wp_doing_ajax() === false) {
            die(esc_html__('Not in Ajax Contest', 'yet-anothter-stars-rating'));
        }
    }

    /**
     * This function checks if the post has been rated within the time frame between the starting date and current time.
     * If the post has been rated within that timeframe, it returns an error message
     * indicating that the user cannot rate the post again.
     *
     * @author Dario Curvino <@dudo>
     * @since 3.3.9
     *
     * @param $post_id
     *
     * @return void
     */
    public function dieIfIpBlocked($post_id, $set_id = false) {
        $time_now = date('Y-m-d H:i:s');

        //create di strtotime string, in seconds
        $strtotime_string = '-' . YASR_SECONDS_BETWEEN_RATINGS . ' seconds';
        $starting_date    = date('Y-m-d H:i:s', strtotime($strtotime_string));

        if(is_numeric($set_id)) {
            $blocked = YasrDB::mvBetweenDates($post_id, $set_id, $starting_date, $time_now);
            //this text is escaped later
            $error_text = __("You can't rate again", 'yet-another-stars-rating');
        } else {
            $blocked = YasrDB::vvBetweenDates($post_id, $starting_date, $time_now);
            $error_text = __("You can't rate again for this post", 'yet-another-stars-rating');
        }

        if ($blocked === true) {
            echo $this->returnErrorResponse(
                $error_text
            );
            die();
        }
    }

}
