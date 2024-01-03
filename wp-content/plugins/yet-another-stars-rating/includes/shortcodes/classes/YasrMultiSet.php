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
 * Class YasrMultiSet
 */
class YasrMultiSet extends YasrShortcode {

    public $set_id;         //1
    public $show_average;   //null
    public $star_readonly;

    public function __construct($atts, $shortcode_name) {
        parent::__construct($atts, $shortcode_name);

        //load css only when YasrMultiset or a Ranking is used (YasrRankings extend this)
        YasrScriptsLoader::loadTableCss();

        $atts = shortcode_atts(
            array(
                'setid'        => YasrDB::returnFirstSetId(),
                'show_average' => null
            ),
            $atts,
            $shortcode_name
        );

        $this->set_id        = (int)$atts['setid'];
        $this->show_average  = sanitize_text_field($atts['show_average']);

    }

    /**
     * @return string
     */
    public function printMultiset ($post_id = null, $set_id = null) {
        if($post_id === null) {
            $post_id = $this->post_id;
        }
        if($set_id === null) {
            $set_id = $this->set_id;
        }

        $this->shortcode_html = '<!-- Yasr Multi Set Shortcode-->';

        $multiset_content = YasrDB::returnMultisetContent($post_id, $set_id);

        if ($multiset_content === false) {
            return $this->returnErrorData('<!-- Yasr Multi Set Shortcode-->');
        }

        $this->shortcode_html  .= '<table class="yasr_table_multi_set_shortcode">';
        $this->star_readonly   = 'true';

        $this->shortcode_html .= $this->returnMultisetRows($multiset_content);

        $this->shortcode_html .= $this->returnAverageRowIfEnabled(false, $multiset_content);

        $this->shortcode_html .= '</table>';
        $this->shortcode_html .= '<!--End Yasr Multi Set Shortcode-->';

        YasrScriptsLoader::loadOVMultiJs();

        return $this->shortcode_html;
    }


    /**
     * Return Error if no data is found
     *
     * @author Dario Curvino <@dudo>
     * @since  2.9.8
     * @return string
     */
    protected function returnErrorData($shortcode_html) {
        $string = esc_html__('No Set Found with this ID', 'yet-another-stars-rating');
        return $shortcode_html . $string;
    }

    /**
     * Loop the multiset content and return the table rows
     *
     * @param $multiset_content
     *
     * @return string
     *
     */
    protected function returnMultisetRows($multiset_content) {
        $shortcode_html  = '';

        $span_container_number_of_votes = '';

        foreach ($multiset_content as $set_content) {
            if (isset($set_content['number_of_votes'])) {
                $span_container_number_of_votes = $this->returnContainerNOfVotes($set_content['number_of_votes']);
            }

            $average_rating  = round($set_content['average_rating'], 1);

            $html_stars      = $this->returnStarsDiv($set_content['id'], $average_rating, $this->star_readonly);

            $shortcode_html .= $this->returnTableRow($set_content['name'], $html_stars, $span_container_number_of_votes);
        } //End foreach

        return $shortcode_html;

    }

    /**
     * return the div of the stars
     *
     * @author Dario Curvino <@dudo>
     * @since  2.9.8
     *
     * @param $field_id
     * @param $average_rating
     * @param $readonly
     *
     * @return string
     */
    public function returnStarsDiv ($field_id, $average_rating, $readonly) {
        $unique_id_identifier = yasr_return_dom_id('yasr-multiset-');

        return  "<div class='yasr-multiset-visitors-rater'
                    id='$unique_id_identifier' 
                    data-rater-postid='$this->post_id'
                    data-rater-setid='$this->set_id'
                    data-rater-set-field-id='$field_id' 
                    data-rating='$average_rating'
                    data-rater-readonly='$readonly'>
                </div>";
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 2.9.8
     * @param $number
     *
     * @return string
     */
    private function returnContainerNOfVotes($number) {
        return '<span class="yasr-visitor-multiset-vote-count">'
            . (int)$number .
            '</span>';
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 2.9.8
     * @param $field_name
     * @param $div_stars
     * @param $span_container_number_of_votes
     *
     * @return string
     */
    private function returnTableRow($field_name, $div_stars, $span_container_number_of_votes) {
        return '<tr>
                    <td>
                         <span class="yasr-multi-set-name-field">' . $field_name . '</span>
                    </td>
                    <td>'
                        . $div_stars . $span_container_number_of_votes .
                   '</td>
              </tr>';
    }

    /**
     * Check if the average is enabled, get the data and return it
     *
     * @author Dario Curvino <@dudo>
     * @since  2.9.8
     *
     * @param $visitor_multiset
     * @param $multiset_content
     *
     * @return false|string
     */
    protected function returnAverageRowIfEnabled($visitor_multiset, $multiset_content) {
        //If average row should be showed
        if ($this->showAverageMultiset() === true) {
            //get the average of the multiset
            $multiset_average = YasrDB::returnMultiSetAverage(
                $this->post_id, $this->set_id, $visitor_multiset, $multiset_content
            );

            //return it
            return $this->returnAverageRowMultiSet($multiset_average);
        }

        return false;
    }

    /**
     * This function return the html code of the average multiset
     *
     * @since 2.1.0
     *
     * @param $multiset_average
     *
     * @return string
     */
    protected function returnAverageRowMultiSet($multiset_average) {
        $average_txt = esc_html__('Average', 'yet-another-stars-rating');
        //Show average row
        $unique_id_identifier = yasr_return_dom_id('yasr-multiset-');

        return "<tr>
                    <td colspan='2' class='yasr-multiset-average'>
                        <div class='yasr-multiset-average'>
                            <span class='yasr-multiset-average-text'>$average_txt</span>
                            <div class='yasr-rater-stars' id='$unique_id_identifier'
                            data-rating='$multiset_average' data-rater-readonly='true'
                            data-rater-starsize='24'></div>
                        </div>
                    </td>
                </tr>";
    }

    /**
     * Return true or false if average should be displayed
     *
     * @return bool
     */
    protected function showAverageMultiset() {
        if ( ( $this->show_average === '' && YASR_MULTI_SHOW_AVERAGE !== 'no' ) ||
             ( $this->show_average !== '' && $this->show_average !== 'no' ) ) {
            return true;
        }
        return false;
    }

}