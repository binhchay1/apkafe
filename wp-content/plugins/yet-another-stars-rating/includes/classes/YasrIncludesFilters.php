<?php

if (!defined('ABSPATH')) {
    exit('You\'re not allowed to see this page');
} // Exit if accessed directly

/**
 * Public filters
 *
 * @since 2.4.3
 *
 * Class YasrPublicFilters
 */
class YasrIncludesFilters {

    /**
     * This filters will hook for show custom texts
     *
     * @author Dario Curvino <@dudo>
     * @since 2.6.6
     *
     */
    public function filterCustomTexts() {
        add_filter('yasr_cstm_text_before_overall', array($this, 'filterTextOverall'));
        add_filter('yasr_cstm_text_before_vv',      array($this, 'filterTextVVBefore'), 10, 3);
        add_filter('yasr_cstm_text_after_vv',       array($this, 'filterTextVVAfter'), 10, 3);
        add_filter('yasr_vv_saved_text',            array($this, 'filterTextRatingSaved'));
        add_filter('yasr_vv_updated_text',          array($this, 'filterTextRatingUpdated'));
        add_filter('yasr_mv_saved_text',            array($this, 'filterTextRatingSaved'));
        add_filter('yasr_cstm_text_already_voted',  array($this, 'filterTextAlreadyVoted'));
        add_filter('yasr_must_sign_in',             array($this, 'filterTextMustSignIn'));
    }

    /**
     * Get text_before_overall from db if exists and return it replacing %overall_rating% pattern with the vote
     *
     * @author Dario Curvino <@dudo>
     * @since 2.6.6
     *
     * @param $overall_rating
     *
     * @return string|string[]
     */
    public function filterTextOverall ($overall_rating) {
        return str_replace('%rating%', $overall_rating, YASR_TEXT_BEFORE_OVERALL);
    }

    /**
     * Get text_before_visitor_rating from db if exists and return it replacing the patterns with the votes
     *
     * @author Dario Curvino <@dudo>
     * @since 2.6.6
     *
     * @param $number_of_votes
     * @param $average_rating
     * @param $unique_id
     *
     * @return string|string[]
     */
    public function filterTextVVBefore ($number_of_votes, $average_rating, $unique_id) {
        //no need to escape, it is done later when string is printed
        return $this->strReplaceInput(YASR_TEXT_BEFORE_VR, $number_of_votes, $average_rating, $unique_id);
    }

    /**
     * Get text_after_visitor_rating from db if exists and return it
     *
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     *
     * @param $number_of_votes
     * @param $average_rating
     * @param $unique_id
     *
     * @return string|string[]
     */
    public function filterTextVVAfter ($number_of_votes, $average_rating, $unique_id) {
        $custom_text  = '<span id="yasr-vv-text-container-'.$unique_id.'" class="yasr-vv-text-container">';
        $custom_text .= YASR_TEXT_AFTER_VR;
        $custom_text .= '</span>';
        return $this->strReplaceInput($custom_text, $number_of_votes, $average_rating, $unique_id);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 2.9.5
     *
     * @return mixed
     */
    public function filterTextRatingSaved() {
        return YASR_TEXT_RATING_SAVED;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 2.9.5
     *
     * @return mixed
     */
    public function filterTextRatingUpdated() {
        return YASR_TEXT_RATING_UPDATED;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 2.6.6
     * @param $rating
     *
     * @return array|string|string[]
     */
    public function filterTextAlreadyVoted ($rating) {
        return str_replace('%rating%', $rating, YASR_TEXT_USER_VOTED);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.6.6
     *
     * @return mixed|string|void
     */
    public function filterTextMustSignIn () {
        return YASR_TEXT_MUST_SIGN_IN;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.5.9
     *
     * @param $subject
     * @param $number_of_votes
     * @param $average_rating
     * @param $unique_id
     *
     * @return string|string[]
     */
    protected function strReplaceInput($subject, $number_of_votes, $average_rating, $unique_id) {
        //This will contain the number of votes
        $number_of_votes_container  = '<span id="yasr-vv-votes-number-container-'. $unique_id .'">';

        //this will contain the average
        $average_rating_container   = '<span id="yasr-vv-average-container-'. $unique_id .'">';

        return str_replace(
            array(
                '%total_count%',
                '%average%'
            ),
            array(
                $number_of_votes_container . $number_of_votes . '</span>',
                $average_rating_container  . $average_rating  . '</span>'
            ),
            $subject
        );
    }

}
