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

class YasrNoStarsRankings extends YasrShortcode {

    /**
     * @param $atts
     *
     * @return string
     */
    public function returnTopReviewers($atts) {

        $this->shortcode_html = '<!-- Yasr '.$this->shortcode_name.' Shortcode-->';

        $sql_atts = apply_filters('yasr_tr_rankings_atts', false, $atts);
        $query_result = YasrDB::rankingTopReviewers($sql_atts);

        if ($query_result) {
            $this->shortcode_html = $this->returnTableContent($query_result, $sql_atts);
        } else {
            $this->shortcode_html .= __('Problem while retrieving the top 5 most active reviewers. Did you publish any review?',
                'yet-another-stars-rating');
        }

        $this->shortcode_html .= '<!-- Yasr '.$this->shortcode_name.' Shortcode-->';

        YasrScriptsLoader::loadRankingsJs();

        return $this->shortcode_html;

    }

    /**
     * @param $atts
     *
     * @return string
     */
    public function returnTopUsers($atts) {

        $this->shortcode_html = '<!-- Yasr '.$this->shortcode_name.' Shortcode-->';

        $sql_atts = apply_filters('yasr_tu_rankings_atts', false, $atts);
        $query_result = YasrDB::rankingTopUsers($sql_atts);

        if ($query_result) {
            $this->shortcode_html = $this->returnTableContent($query_result, $sql_atts);
        } else {
            $this->shortcode_html = __('Problem while retrieving the top 10 active users chart. Are you sure you have votes to show?',
                'yet-another-stars-rating');
        }

        $this->shortcode_html .= '<!-- Yasr '.$this->shortcode_name.' Shortcode-->';

        return $this->shortcode_html;

    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.6.2
     * @return string
     */
    private function returnTableHeader() {
        if($this->shortcode_name === 'yasr_most_active_users' || 'yasr_top_ten_active_users') {
            $first_header  = __('User', 'yet-another-stars-rating');
            $second_header = __('Number of votes', 'yet-another-stars-rating');
        } else {
            $first_header  = __('Author', 'yet-another-stars-rating');
            $second_header = __('Reviews', 'yet-another-stars-rating');
        }

        return '<table class="yasr-rankings">
                                <thead>
                                    <tr class="yasr-rankings-td-colored">
                                        <th>' . $first_header . '</th>
                                        <th>' . $second_header . '</th>
                                    </tr>
                                </thead>';
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 2.6.3
     * @param object $query_result //the query ruslt to loop
     *
     * @return string
     */
    private function returnTableContent($query_result, $sql_atts){
        $shortcode_html = $this->returnTableHeader();
        $i = 0;

        foreach ($query_result as $result) {
            $user_id = (int)$result->user;

            $user_link = '#';
            //If user is 0 means is anonumous
            if($user_id === 0) {
                $username = __('Anonymous', 'yet-another-stars-rating');
            } else {
                $user_data = get_userdata($result->user);
                if ($user_data) {
                    $user_link = get_author_posts_url($result->user);
                    $username  = apply_filters('yasr_tu_rankings_display', $user_data->user_login, $user_data);
                }
                //If user_id is not 0, but user_data is false, means that account doesn't exists anymore
                else {
                    $username = __('Account deleted', 'yet-another-stars-rating');
                }
            }

            if ($i % 2 === 0) {
                $tr_class = 'yasr-rankings-td-white';
            } else {
                $tr_class = 'yasr-rankings-td-colored';
            }

            $shortcode_html .= "<tr class='$tr_class'>
                                    <td><a href='$user_link'>$username</a></td>
                                    <td>$result->total_count</td>
                                </tr>";

            $i++;

        }

        $shortcode_html .= '</table>';

        return $shortcode_html;
    }
}