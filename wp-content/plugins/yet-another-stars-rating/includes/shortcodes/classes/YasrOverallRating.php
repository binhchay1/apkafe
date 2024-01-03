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
 * Class YasrOverallRating
 * Print Yasr Overall Rating
 */
class YasrOverallRating extends YasrShortcode {

    protected $overall_rating;

    /**
     * This is called when shortcode is used
     *
     * @return string
     */
    public function returnShortcode () {
        $overall_attributes    = $this->returnAttributes();

        $shortcode_html  = '<!--Yasr Overall Rating Shortcode-->';

        $shortcode_html .= $this->customTextBefore();

        $shortcode_html .= '<div class="yasr-overall-rating">';
        $shortcode_html .= $overall_attributes['html_stars'];
        $shortcode_html .= '</div>';

        $shortcode_html .= '<!--End Yasr Overall Rating Shortcode-->';

        //Use this filter to customize overall rating
        $shortcode_html = apply_filters('yasr_overall_rating_shortcode', $shortcode_html, $overall_attributes);

        self::defineOvAttributes($overall_attributes['overall_rating']);

        YasrScriptsLoader::loadOVMultiJs();

        return $shortcode_html;
    }

    /**
     * @param int | bool $stars_size
     * @param int | bool $post_id
     * @param string | bool $class
     * @param string | bool $rating
     *
     * @return array
     *     array(
     *         'overall_rating' => $overall_rating,
     *         'post_id'        => $post_id,
     *         'html_stars'     => $html_stars
     *     );
     */
    public function returnAttributes($stars_size=false, $post_id=false, $class=false, $rating=false) {
        if(!is_int($stars_size)) {
            $stars_size = $this->starSize();
        }

        if(!is_int($post_id)) {
            $post_id = $this->post_id;
        }

        $class .= ' yasr-rater-stars';

        //if here $this->overall_rating is still null, check if rating is not false, and if so, put it in $overall rating
        // if rating is false, get from the db
        if($this->overall_rating === null) {
            if($rating !== false) {
                $overall_rating = $rating;
            } else {
                $overall_rating = YasrDB::overallRating($post_id);
            }
        }  else {
            $overall_rating = $this->overall_rating;
        }

        $overall_rating_html_id  = yasr_return_dom_id('yasr-overall-rating-rater-');

        $html_stars = "<div class='$class'
                           id='$overall_rating_html_id'
                           data-rating='$overall_rating'
                           data-rater-starsize='$stars_size'>
                       </div>";

        return array(
            'overall_rating' => $overall_rating,
            'post_id'        => $post_id,
            'html_stars'     => $html_stars
        );

    }

    /**
     * If enabled in the settings, this function will show the custom text
     * before yasr_overall_rating
     *
     * @return void|string
     *
     */
    protected function customTextBefore() {
        //Get overall Rating
        $this->overall_rating  = YasrDB::overallRating();
        $text_before_star      = apply_filters('yasr_cstm_text_before_overall', $this->overall_rating);

        if(!$text_before_star) {
            return;
        }

        return "<div class='yasr-custom-text-before-overall' id='yasr-custom-text-before-overall'>"
                    .wp_kses_post(htmlspecialchars_decode($text_before_star)).
                "</div>";
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.8.8
     *
     * @param $overall_rating
     */
    public static function defineOvAttributes($overall_rating) {
        if(!defined('YASR_OV_ATTRIBUTES')) {
            define('YASR_OV_ATTRIBUTES', json_encode($overall_rating));
        }
    }

}
