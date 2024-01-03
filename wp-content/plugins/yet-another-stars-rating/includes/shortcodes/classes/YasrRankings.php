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
 * Extends YasrMultiSet instead of YasrShortcode because it also works with this->set_id
 *
 * @author Dario Curvino <@dudo>
 * Class YasrRankings
 */
class YasrRankings extends YasrMultiSet {
    protected $query_highest_rated_overall;
    public    $query_result_most_rated_visitor;
    public    $query_result_highest_rated_visitor;
    protected $ajax_nonce_rankings;

    public function __construct($atts, $shortcode_name) {
        parent::__construct($atts, $shortcode_name);

        $this->ajax_nonce_rankings = wp_create_nonce("yasr_nonce_rankings");
    }

    /**
     * Returns the shortcode for yasr_ov_ranking
     *
     * @param $atts
     *
     * @return string
     */
    public function returnHighestRatedOverall ($atts) {
        $this->shortcode_html = '<!-- Yasr Highest Rated Shortcode-->';

        //hook in this filter to use shortcode atts
        //$this->shorcode_name is the default value
        $sql_atts = apply_filters('yasr_ov_rankings_atts', $this->shortcode_name, $atts);

        $this->query_highest_rated_overall = YasrDB::rankingOverall($sql_atts);

        $this->returnSingleTableRanking($this->urlencodeAtts($sql_atts));
        $this->shortcode_html .= '<!--End Yasr Top 10 highest Rated Shortcode-->';

        YasrScriptsLoader::loadRankingsJs();

        return $this->shortcode_html;
    }

    /**
     * Returns the shortcode for all rankings that get data from visitor votes
     *
     * @param        $atts
     * @param string $source
     *
     * @return string
     */
    public function vvReturnMostHighestRated ($atts, $source='visitor_votes') {
        $this->shortcode_html = '<!-- Yasr Most Or Highest Rated Shortcode -->';

        /**
         * Hook here to use shortcode atts.
         * If not used, will work with no support for atts
         *
         * @param string       $this->shortcode_name  Name of shortcode caller
         * @param string|array $atts  Shortcode atts
         */
        $sql_atts = apply_filters('yasr_vv_rankings_atts', $this->shortcode_name, $atts);

        $this->query_result_most_rated_visitor    = YasrDB::rankingVV($sql_atts, 'most');
        $this->query_result_highest_rated_visitor = YasrDB::rankingVV($sql_atts, 'highest');

        $this->returnDoubleTableRanking($this->urlencodeAtts($sql_atts), $source);
        $this->shortcode_html .= '<!--End Yasr TMost Or Highest Rated Shortcode -->';

        YasrScriptsLoader::loadRankingsJs();

        return $this->shortcode_html;
    }

    /**
     * Return the shortcode yasr_multi_set_ranking
     *
     * @author Dario Curvino <@dudo>
     * @since  2.7.2
     *
     * @param $atts
     *
     * @return string
     */
    public function returnMulti ($atts) {
        $this->shortcode_html = '<!-- Yasr Ranking by Multiset -->';

        //hook in this filter to use shortcode atts
        //$this->shorcode_name is the default value
        $sql_atts = apply_filters('yasr_multi_set_ranking_atts', $this->shortcode_name, $atts);

        $this->query_highest_rated_overall = YasrDB::rankingMulti($this->set_id, $sql_atts);

        $this->returnSingleTableRanking($this->urlencodeAtts($sql_atts), 'author_multi');
        $this->shortcode_html .= '<!-- Yasr Ranking by Multiset -->';

        YasrScriptsLoader::loadRankingsJs();

        return $this->shortcode_html;
    }

    /**
     * Return from shortcode yasr_visitor_multi_set_ranking
     *
     * @author Dario Curvino <@dudo>
     * @since  2.7.2
     *
     * @param $atts
     *
     * @return string
     */
    public function returnMultiVisitor($atts) {
        $this->shortcode_html = '<!-- Yasr Ranking by Visitor Multiset -->';

        /**
         * Hook here to use shortcode atts.
         * If not used, shortcode will works only with setId param
         *
         * @param string       $this->shortcode_name  Name of shortcode caller
         * @param string|array $atts  Shortcode atts
         */
        $sql_atts = apply_filters('yasr_visitor_multi_set_ranking_atts', $this->shortcode_name, $atts);

        $this->query_result_most_rated_visitor    = YasrDB::rankingMultiVV($this->set_id, 'most', $sql_atts);
        $this->query_result_highest_rated_visitor = YasrDB::rankingMultiVV($this->set_id, 'highest', $sql_atts);

        //this means no filter has run, I've to create an array with the setid
        //that will be later urlencoded
        if($sql_atts === $this->shortcode_name) {
            $sql_atts = array('setid' => $this->set_id);
        }

        $this->returnDoubleTableRanking($this->urlencodeAtts($sql_atts), 'visitor_multi');
        $this->shortcode_html .= '<!--End Yasr Ranking by Visitor Multiset -->';

        YasrScriptsLoader::loadRankingsJs();

        return $this->shortcode_html;
    }

    /**
     * Returns only initial and ending table tag
     * Table content is build with REACT
     *
     * @author Dario Curvino <@dudo>
     * @since 2.5.7
     *
     * @param $sql_params array|string - params that can be used in REST API
     *
     */
    protected function returnSingleTableRanking($sql_params=false, $source='overall_rating') {
        if ($this->query_highest_rated_overall) {

            $table_id = yasr_return_dom_id('yasr_overall_ranking_');
            $array_with_title = htmlspecialchars(
                json_encode(self::rankingData($this->query_highest_rated_overall)),ENT_QUOTES, 'UTF-8'
            );

            $table_attributes = json_encode($sql_params);

            $this->shortcode_html .= "<table
                                          class='yasr-rankings yasr-stars-rankings'
                                          id=$table_id
                                          data-ranking-data='".$array_with_title."'
                                          data-ranking-source='".json_encode($source)."'
                                          data-ranking-params='$table_attributes'
                                          data-ranking-size='".$this->starSize()."'
                                          data-ranking-nonce='".json_encode($this->ajax_nonce_rankings)."'
                                          >";

            $this->shortcode_html .= "</table>";
        }
        else {
            $this->shortcode_html .= '<div style="padding: 20px">';
            $this->shortcode_html .= __('No posts found with these parameters. Try to remove some filters.', 'yet-another-stars-rating');
            $this->shortcode_html .= '</div>';
        }

    }

    /**
     * Create the queries for the rankings
     * Return the full html for the shortcode
     *
     * @param $urlencoded_params array|string - params that can be used in REST API
     */
    public function returnDoubleTableRanking($urlencoded_params, $source) {
        if($this->query_result_most_rated_visitor && $this->query_result_highest_rated_visitor) {

            //This means that an hook has run
            if($urlencoded_params !== $this->shortcode_name) {
                $table_attributes = json_encode($urlencoded_params);
            } else {
                //is is useless here to pass the shortcode name into the data-params
                $table_attributes = json_encode('');
            }

            $array_with_title['most']    = self::rankingData($this->query_result_most_rated_visitor);
            $array_with_title['highest'] = self::rankingData($this->query_result_highest_rated_visitor);

            $table_id = yasr_return_dom_id('yasr_vv_ranking_');

            $this->shortcode_html .= "<table
                                          class='yasr-rankings yasr-stars-rankings'
                                          id='$table_id'
                                          data-ranking-data='".htmlspecialchars(json_encode($array_with_title),ENT_QUOTES, 'UTF-8')."'
                                          data-ranking-source='".json_encode($source)."'
                                          data-ranking-params='$table_attributes'
                                          data-ranking-size='".$this->starSize()."'
                                          data-ranking-nonce='".json_encode($this->ajax_nonce_rankings)."'
                                          >";
            $this->shortcode_html .= '</table>';

        } else {
            $this->shortcode_html .= '<div style="padding: 20px">';
            $this->shortcode_html .= esc_html__('No posts found with these parameters. Try to remove some filters.', 'yet-another-stars-rating');
            $this->shortcode_html .= '</div>';
        }

    }

    /**
     * Returns an array with post titles and links
     *
     * @author Dario Curvino <@dudo>
     * @since 2.5.2
     *
     * @param $query_result array to loop; MUST have:
     * post_id
     * rating
     * (optional) number_of_votes
     *
     * @return array
     */
    public static function rankingData($query_result) {
        $data_array = array();

        $i=0;
        foreach ($query_result as $result) {
            $result = (object) $result;
            $data_array[$i]['post_id']        = (int)$result->post_id;
            $data_array[$i]['rating']         = round($result->rating,1);
            if(isset($result->number_of_votes)) {
                $data_array[$i]['number_of_votes'] = (int)$result->number_of_votes;
            }
            $data_array[$i]['title']          = esc_html(get_post_field( 'post_title', $result->post_id, 'raw' ));
            $data_array[$i]['link']           = get_permalink($result->post_id); //Get permalink from post id
            $i++;
        } //End foreach

        return $data_array;
    }

    /**
     * Urlencode shortcode atts
     *
     * @author Dario Curvino <@dudo>
     * @since  2.7.2
     * @param $atts
     *
     * @return string
     */
    public function urlencodeAtts ($atts) {
        //If shortcode atts is === shortcode name, no filter has run, I don't need to do urlencode
        if($atts === $this->shortcode_name) {
            return '';
        }

        if(is_array($atts)) {
            $urlencoded_atts  = http_build_query($atts);
        } else if (is_string($atts)) {
            $urlencoded_atts = urlencode($atts);
        } else {
            $urlencoded_atts = '';
        }

        return $urlencoded_atts;
    }
}
