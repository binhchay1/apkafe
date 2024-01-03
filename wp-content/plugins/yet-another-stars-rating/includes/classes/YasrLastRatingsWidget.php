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
 * Class YasrLastRatingsWidget
 *
 * This class is used to show:
 *  - "Recent Ratings" widget in dashboard
 *  - "Your Ratings"   widget in dashboard
 *  - [yasr_user_rate_history] shortcode
 *
 */
class YasrLastRatingsWidget {

    private $limit = 8;

    /**
     * This array will contain the permalinks, to avoid to get again and again the same data for the same post id
     */
    public $permalinks   = array();

    /**
     * This array will contain the avatar urls, to avoid to get again and again the same data for the same user id
     */
    public $avatar_urls  = array();

    private $user_widget = false;

    /**
     * Return the log for the admin area, only user that can manage options can see this
     *
     * @return string | void
     */
    public function adminWidget() {
        if (!current_user_can('manage_options')) {
            return;
        }
        global $wpdb;

        //query for admin widget
        $number_of_rows =
            $wpdb->get_var("SELECT COUNT(*) 
                                  FROM $wpdb->posts AS p, " . YASR_LOG_TABLE . " AS l  
                                  WHERE  p.ID = l.post_id"
            );

        $query_results = $wpdb->get_results($this->returnQueryAdmin());

        return($this->returnWidget($number_of_rows, $query_results, 'yasr-admin-log-container'));
    }

    /**
     * @return string
     */
    public function userWidget() {
        $user_id = get_current_user_id();

        if($user_id === 0) {
            $must_login_text = __('You must login to see this widget.', 'yet-another-stars-rating');

            /**
             *  Hook here to customize the message "You must login to see this widget." when
             *  the shortcode yasr_user_rate_history is used
             */
            $must_login_text = apply_filters('yasr_user_rate_history_must_login_text', $must_login_text);
            return '<p>'.$must_login_text.'</p>';
        }

        //set true to user widget
        $this->user_widget = true;

        global $wpdb;

        $number_of_rows = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) 
                          FROM $wpdb->posts AS p, " . YASR_LOG_TABLE . " AS l  
                          WHERE l.user_id = %d
                              AND p.ID = l.post_id",
                $user_id)
        );

        $query_results = $wpdb->get_results(
            $this->returnQueryUser($user_id)
        );

        return $this->returnWidget($number_of_rows, $query_results, 'yasr-user-log-container');
    }

    /**
     * Return the widget
     *
     * @return string
     */
    private function returnWidget($number_of_rows, $query_results, $container_id) {
        if($number_of_rows > 0) {
            $n_of_pages = ceil($number_of_rows / $this->limit);
        } else {
            $n_of_pages = 1;
        }

        if (!$query_results) {
            return __('No Recent votes yet', 'yet-another-stars-rating');
        }

        if($this -> user_widget === false) {
            $nonce_id = 'yasr-admin-log-nonce-page';
        } else {
            $nonce_id = 'yasr-user-log-nonce-page';
        }

        $nonce = wp_create_nonce('yasr_user_log');

        $html_to_return  = "<div class='yasr-log-container' id='$container_id'>";

        $html_to_return  .= '<input type="hidden"
                           name="yasr_user_log_nonce"
                           value="'.$nonce.'"
                           id="'.$nonce_id.'">';

        $html_to_return .= $this->loopResults($query_results);

        $html_to_return .= $this->pagination($n_of_pages);

        $html_to_return .= '</div>'; //End Yasr Log Container

        return $html_to_return;
    }

    /**
     * Loop the query results and return the html with content
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.4
     *
     * @param $query_results
     *
     * @return string|void
     */
    public function loopResults ($query_results) {
        $i = 0;

        if(!is_array($query_results)) {
            return;
        }

        //avoid undefined
        $rows       = '';
        $ip_span    = '';

        foreach ($query_results as $result) {
            //cast to int
            $result->user_id = (int)$result->user_id;
            $result->post_id = (int)$result->post_id;

            $permalink = $this->returnPermalink($result->post_id);
            $avatar    = $this->returnAvatarUrl($result->user_id);

            $vote  = (int)$result->vote;
            $title = $result->post_title;
            $date  = $result->date;

            if ($this->user_widget !== true) {
                $user = $result->user_nicename;
            } else {
                $user = false;
            }

            //Set value depending if we're on user or admin widget
            if ($this->user_widget !== true) {
                if (YASR_ENABLE_IP === 'yes') {
                    $ip_id        = "yasr-admin-log-ip-$i";
                    $ip_span = '<span class="yasr-log-ip">' . __('Ip address', 'yet-another-stars-rating') . ': 
                                    <span id="'.$ip_id.'" style="color:blue">' . $result->ip . '</span>
                                </span>';
                }
            }

            $rows .= $this->rowContent($avatar, $i, $user, $permalink, $ip_span, $vote, $title, $date);

            $i = $i +1;
        } //End foreach

        return $rows;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  3.3.4
     *
     * @param $avatar_url
     * @param $i
     * @param $user
     * @param $permalink
     * @param $ip_span
     * @param $vote
     * @param $title
     * @param $date
     *
     * @return string
     */
    private function rowContent ($avatar_url, $i, $user, $permalink, $ip_span, $vote, $title, $date) {

        if ($this->user_widget !== true) {
            $yasr_log_vote_text = ' ' . sprintf(
                    __('Vote %s from %s on', 'yet-another-stars-rating'),
                    '<span id="yasr-admin-log-vote-'.$i.'" style="color: blue;">' . $vote . '</span>',
                    '<span id="yasr-admin-log-user-'.$i.'" style="color: blue">' . $user . '</span>'
                );
            $container_id = "yasr-admin-log-div-child-$i";
            $text_id      = "yasr-admin-log-text-$i";
            $title_id     = "yasr-admin-log-post-$i";
            $date_id      = "yasr-admin-log-date-$i";
        }
        else {
            $yasr_log_vote_text = ' ' . sprintf(
                    __('You rated %s on', 'yet-another-stars-rating'),
                    '<span id="yasr-user-log-vote-'.$i.'" style="color: blue;">' . $vote . '</span>'
            );

            $container_id = "yasr-user-log-div-child-$i";
            $text_id      = "yasr-user-log-text-$i";
            $title_id     = "yasr-user-log-post-$i";
            $date_id      = "yasr-user-log-date-$i";
        }

        return "<div class='yasr-log-div-child' id='$container_id'>
                    <div class='yasr-log-image'>
                        <img alt='avatar' src='$avatar_url' class='avatar avatar-32 photo' 
                             loading='lazy' width='32' height='32' id='yasr-admin-log-avatar-".$i."'>
                    </div>
                    <div class='yasr-log-child-head'>
                        <span class='yasr-log-vote' id='$text_id'>
                            $yasr_log_vote_text
                        </span>
                        <span class='yasr-log-post' id='$title_id'>
                            <a href='$permalink'>$title</a>
                        </span>
                    </div>
                    <div class='yasr-log-ip-date'>
                        $ip_span
                        <span class='yasr-log-date' id='$date_id'>
                            $date
                        </span>
                    </div>
              </div>";
    }

    /**
     * This function will print the row with pagination
     */
    private function pagination($n_of_pages) {
        if($this->user_widget === true) {
            $container_id     = 'yasr-user-log-page-navigation-buttons';
            $span_loader_id   = 'yasr-user-log-loader-metabox';
            $span_total_pages = 'yasr-user-log-total-pages';
            $button_class     = 'yasr-user-log-page-num';

        } else {
            $container_id     = 'yasr-admin-log-page-navigation-buttons';
            $span_loader_id   = 'yasr-admin-log-loader-metabox';
            $span_total_pages = 'yasr-admin-log-total-pages';
            $button_class     = 'yasr-admin-log-page-num';
        }

        $html_pagination = "<div class='yasr-log-page-navigation'>";

        $html_pagination .= "<div id='$span_total_pages' 
                                 data-yasr-log-total-pages='$n_of_pages' 
                                 style='display: inline'>";
        $html_pagination .= __('Pages', 'yet-another-stars-rating') . ": ($n_of_pages) &nbsp;&nbsp;&nbsp;";
        $html_pagination .= '</div>';


        $html_pagination .= '<div id="'.$container_id.'" style="display: inline">';

        //current page (always the first) plus one
        $end_for = 2;

        if ($end_for >= $n_of_pages) {
            $end_for = $n_of_pages;
        }

        for ($i = 1; $i <= $end_for; $i++) {
            if ($i === 1) {
                $html_pagination .= "<button class='button-primary' 
                                             value='$i'>$i</button>&nbsp;&nbsp;";
            } else {
                $html_pagination .= "<button class='$button_class' 
                                             value='$i'>$i</button>&nbsp;&nbsp;";
            }
        }

        if ($n_of_pages > 3) {
            $html_pagination .= "...&nbsp;&nbsp;
                                <button class='$button_class'
                                    value='$n_of_pages'>
                                    Last &raquo;</button>
                                    &nbsp;&nbsp;";
        }

        $html_pagination .= '</div>';

        //loader
        $html_pagination .= "<span class='yasr-last-ratings-loader' id='$span_loader_id'>&nbsp;
                                <img alt='loader' src='" . YASR_IMG_DIR . "/loader.gif' >
                            </span>";

        $html_pagination .= '</div>'; //End yasr-log-page-navigation

        return $html_pagination;
    }

    /**
    * Return the ajax response for the user widget
    *
    * @author Dario Curvino <@dudo>
    * @since  3.3.4
    * @return void
    */
    public function returnAjaxResponse($admin_widget = false) {
        if (isset($_POST['pagenum']) && isset($_POST['yasr_user_log_nonce'])) {
            $page_num = (int) $_POST['pagenum'];
            $nonce    = $_POST['yasr_user_log_nonce'];
        }
        else {
            $page_num = 1;
            $nonce    = '';
        }

        $error = "Wrong nonce, can't change page";
        $nonce_response = YasrShortcodesAjax::validNonce($nonce, 'yasr_user_log', $error);

        if($nonce_response !== true) {
            die($nonce_response);
        }

        global $wpdb;

        $this->limit   = 8;

        $offset = ($page_num - 1) * $this->limit;

        if($admin_widget === true) {
            if (!current_user_can('manage_options')) {
                return;
            }
            $query = $this->returnQueryAdmin($offset);

        } else {
            $user_id = get_current_user_id();
            $query   = $this->returnQueryUser($user_id, $offset);
        }

        $log_query = $wpdb->get_results($query, ARRAY_A);

        if ($log_query === null) {
            $array_to_return['status']  = 'error';
            $array_to_return['message'] = 'Error with the query';
        }
        else {
            $array_to_return['status'] = 'success';

            $i = 0;
            //get the permalink and add it to log_query
            foreach ($log_query as $result) {
                if($admin_widget === true) {
                    $log_query[$i]['avatar_url'] = $this->returnAvatarUrl($result['user_id']);
                }

                $log_query[$i]['permalink'] = $this->returnPermalink($result['post_id']);
                $i++;
            }

            $array_to_return['data'] = $log_query;
        }

        wp_send_json($array_to_return);
    }

    /**
     * Return the sanitized query string for user query
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.4
     *
     * @param $user_id
     * @param $offset
     *
     * @return string|null
     */
    public function returnQueryUser($user_id, $offset=0) {
        global $wpdb;

        //Since there is no need to select the l.user_id on ajax, do this only if $offset = 0 (first page)
        $select_user_id = '';
        if($offset === 0) {
            $select_user_id = ', l.user_id';
        }

        return $wpdb->prepare(
            "SELECT p.post_title, l.vote, l.date, l.post_id $select_user_id
                       FROM $wpdb->posts AS p, " . YASR_LOG_TABLE . " AS l 
                    WHERE l.user_id = %d 
                        AND p.ID = l.post_id
                    ORDER BY date 
                    DESC LIMIT %d,  %d",
            $user_id, $offset, $this->limit
        );
    }

    /**
     * Return the recent ratings.
     * If an user is not found in u.ID, return "anonymous"
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.4
     *
     * @param $offset
     *
     * @return string|null
     */
    public function returnQueryAdmin($offset=0) {
        global $wpdb;

        $anonymous_string = esc_html__('anonymous', 'yet-another-stars-rating');

        return $wpdb->prepare(
            "SELECT p.post_title, l.vote, l.date, l.post_id, l.user_id, l.ip,
                           IF(l.user_id = 0, %s, IFNULL(u.user_nicename, %s)) AS user_nicename
                   FROM " .$wpdb->posts." AS p, " . YASR_LOG_TABLE . " AS l 
                   LEFT JOIN " .$wpdb->users. " AS u ON l.user_id = u.ID 
                   WHERE  p.ID = l.post_id
                   ORDER BY date DESC
                   LIMIT %d,  %d",
            $anonymous_string, $anonymous_string, $offset, $this->limit
        );
    }

    /**
     * Return the avatar url
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.4
     *
     * @param $user_id
     *
     * @return false|mixed|string
     */
    public function returnAvatarUrl ($user_id) {
        //get user info only if not already done,
        //so check if $result->user_id already exists in array user_ids
        if(!array_key_exists($user_id, $this->avatar_urls)) {

            //Get avatar from user id
            $avatar = get_avatar_url($user_id, '32');

            //inset $result->user_id; into $user_ids
            $this->avatar_urls[$user_id] = $avatar;

            return $avatar;
        }

        return $this->avatar_urls[$user_id];
    }

    /**
     * Return the permalink of the post
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.4
     *
     * @param $post_id
     *
     * @return false|mixed|string
     */
    public function returnPermalink ($post_id) {
        //cast to int
        $post_id = (int)$post_id;

        //get post permalink only if not already done,
        //so check if $post_id already exists in array permalinks
        if(!array_key_exists($post_id, $this->permalinks)) {

            //Get post link from post id
            $link  = get_permalink($post_id);

            //first, save the link into $this->permalink
            $this->permalinks[$post_id] = $link;

            //return
            return $link;
        }
        //here, means that we've already got the permalink for this post_id, so return it
        return $this->permalinks[$post_id];
    }

}