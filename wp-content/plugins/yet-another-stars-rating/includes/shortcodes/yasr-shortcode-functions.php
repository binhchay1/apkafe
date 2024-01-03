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
 * Callback function for the spl_autoload_register above.
 *
 * @param $class
 */
function yasr_autoload_shortcodes($class) {
    /**
     * If the class being requested does not start with 'Yasr' prefix,
     * it's not in Yasr Project
     */
    if (0 !== strpos($class, 'Yasr')) {
        return;
    }
    $file_name =  YASR_ABSOLUTE_PATH_INCLUDES . '/shortcodes/classes/' . $class . '.php';

    // check if file exists, just to be sure
    if (file_exists($file_name)) {
        require($file_name);
    }
}

//AutoLoad YASR Shortcode Classes, only when a object is created
spl_autoload_register('yasr_autoload_shortcodes');

/**
 **
 * ###What is?
 * `[yasr_overall_rating]` shortcode is read only and is used by the reviewer.
 * It comes in three sizes: "Small", "Medium", and "Large".
 * The text displayed before or after the rating can be customized in the settings.
 * The shortcode can be manually placed or automatically inserted using the auto insert feature
 * ### How to use it?
 * To insert the rating in this widget, there are two ways:
 *    - If you're using the Classic editor, simply give a rating in the YASR metabox that appears at the top right of the
 *        screen while you're writing a new post or page.
 *    - If you're using the new Gutenberg editor, click on the "+" icon to add a block, search for YASR, and select
 *        YASR: Overall Rating. A new panel will appear to the right, where you can add your rating.
 *
 * @return string|void|null
 */
add_shortcode('yasr_overall_rating', 'shortcode_overall_rating_callback');

/**
 * @author Dario Curvino <@dudo>
 *
 * @param $atts
 * @param $content
 * @param $shortcode_tag
 *
 * @return string|void
 */
function shortcode_overall_rating_callback ($atts, $content=false, $shortcode_tag=false) {
    if (YASR_SHOW_OVERALL_IN_LOOP === 'disabled' && !is_singular() && is_main_query()) {
        return;
    }
    return (new YasrOverallRating($atts, $shortcode_tag))->returnShortcode();
} //end function


/**
 *
 * ### What is?
 * With `[yasr_visitor_votes]` visitors can rate a post or page.
 * With it, you can:
 * - Choose to allow anonymous or logged in only users.
 * - Logged-in users can update their vote anytime.
 * - Size can be “Small”, “Medium” or “Large”.
 * - Customize the text shown before or after.
 * - Hover on the chart bar icon to see the stats.
 * ### How to use it?
 * To insert the rating in this widget, there are two ways:
 * - You can paste the shortcode [yasr_visitor_votes] where you need to show the widget, or you can use the auto insert
 * feature as explained in [this tutorial](https://yetanotherstarsrating.com/tutorials/).
 * - If you're using the new Gutenberg editor, click on the "+" icon to add a block, search for YASR and select YASR: Visitor Votes.
 *
 */
 add_shortcode('yasr_visitor_votes', 'shortcode_visitor_votes_callback');

 /**
 *
 * @param      $atts
 * @param bool $content
 * @param bool $shortcode_tag
 *
 * @return string|void|null
 */
function shortcode_visitor_votes_callback($atts, $content=false, $shortcode_tag=false) {
    if (YASR_SHOW_VISITOR_VOTES_IN_LOOP === 'disabled' && !is_singular() && is_main_query()) {
        return;
    }

    return (new YasrVisitorVotes($atts, $shortcode_tag))->returnShortcode();
} //End function shortcode_visitor_votes_callback


/**
 * `[yasr_multiset]` allows you to insert a rating for each aspect of your review (up to nine rows).
 *
 * The setid is a number that identifies the multiset.
 *
 * This shortcode return author multi set
 */
add_shortcode ('yasr_multiset',  'yasr_multiset_callback');

/**
 * @param      $atts
 * @param bool $content
 * @param bool $shortcode_tag
 *
 * @return bool|string
 */
function yasr_multiset_callback($atts, $content, $shortcode_tag) {
    return (new YasrMultiSet($atts, $shortcode_tag))->printMultiset();
}

/**
 * Yasr Visitor Multiset
 */
add_shortcode ('yasr_visitor_multiset', 'yasr_visitor_multiset_callback');

/**
 * @param      $atts
 * @param bool $content
 * @param bool $shortcode_tag
 *
 * @return string
 */
function yasr_visitor_multiset_callback($atts, $content, $shortcode_tag) {
    return (new YasrVisitorMultiSet($atts, $shortcode_tag))->printVisitorMultiSet();
}

/**
 * @deprecated since version 2.6.2
 * @todo remove DEC 2023
 */
add_shortcode ('yasr_top_ten_highest_rated', 'yasr_ov_ranking_callback');

/**
 * Yasr Overall Ranking
 *
 * This shortcode print the highest rated posts by overall_rating
 * @since 2.6.2
 */
add_shortcode ('yasr_ov_ranking', 'yasr_ov_ranking_callback');

/**
 * @param $atts
 * @param $content
 * @param $shortcode_tag
 *
 * @return string
 */
function yasr_ov_ranking_callback($atts, $content, $shortcode_tag) {
    return (new YasrRankings(false, $shortcode_tag))->returnHighestRatedOverall($atts);
} //End function


/**
 * Yasr Visitor Votes Ranking
 *
 * This shortcode print the higher / most rated posts with yasr_visitor_votes
 */
add_shortcode ('yasr_most_or_highest_rated_posts', 'yasr_most_or_highest_rated_posts_callback');
/**
 * @param $atts
 * @param $content
 * @param $shortcode_tag
 *
 * @return string
 */
function yasr_most_or_highest_rated_posts_callback($atts, $content, $shortcode_tag) {
    return (new YasrRankings(false, $shortcode_tag))->vvReturnMostHighestRated($atts);
} //End function


/**
 * @deprecated deprecated since version 2.6.2
 * @todo remove DEC 2023
 */
add_shortcode ('yasr_top_5_reviewers', 'yasr_ranking_users_callback');

/**
 * Yasr Top reviewers
 *
 * Shortcode to display most active reviewers
 * @since 2.6.2
 */
add_shortcode ('yasr_top_reviewers', 'yasr_ranking_users_callback');

/**
 * @deprecated since version 2.6.2
 * @todo remove DEC 2023
 */
add_shortcode ('yasr_top_ten_active_users', 'yasr_ranking_users_callback');

/**
 * Yasr Most Active users
 *
 * This shortcode show which users leave more votes on yasr_visitor_votes
 * @since 2.6.2
 */
add_shortcode ('yasr_most_active_users', 'yasr_ranking_users_callback');

/**
 * @author Dario Curvino <@dudo>
 *
 * @param $atts
 * @param $content
 * @param $shortcode_tag
 *
 * @return string
 */
function yasr_ranking_users_callback ($atts, $content, $shortcode_tag) {
    $ranking_users_obj = new YasrNoStarsRankings(false, $shortcode_tag);

    if($shortcode_tag === 'yasr_top_reviewers' || $shortcode_tag === 'yasr_top_5_reviewers') {
        return $ranking_users_obj->returnTopReviewers($atts);
    }

    return $ranking_users_obj->returnTopUsers($atts);
} //End users rankings

/**
 * YASR Multiset Ranking
 */
add_shortcode ('yasr_multi_set_ranking', 'yasr_multi_set_ranking_callback');

function yasr_multi_set_ranking_callback($atts, $content, $shortcode_tag) {
    return (new YasrRankings($atts, $shortcode_tag))->returnMulti($atts);
} //End function

/**
 * Yasr Visitor Multiset Ranking
 */
add_shortcode ('yasr_visitor_multi_set_ranking', 'yasr_visitor_multi_set_ranking_callback');

function yasr_visitor_multi_set_ranking_callback($atts, $content, $shortcode_tag) {
    return (new YasrRankings($atts, $shortcode_tag))->returnMultiVisitor($atts);
} //End function

/**
 * Yasr User Rate History
 *
 * When a user is logged in, print all the rating that user leaved
 */
add_shortcode('yasr_user_rate_history', 'yasr_users_front_widget_callback');
function yasr_users_front_widget_callback() {
    YasrScriptsLoader::loadLogUsersFrontend();
    return (new YasrLastRatingsWidget())->userWidget();
} //End callback function


/**
 * Yasr Display Posts
 *
 * Display your posts according to YASR ratings. This shortcode works only on pages.
 *
 * @since 3.3.0
 */
add_shortcode('yasr_display_posts', 'yasr_display_posts_callback');
/**
 * @author Dario Curvino <@dudo>
 *
 * @since 3.3.0
 *
 * @param $atts
 * @param $content
 * @param $shortcode_tag
 *
 * @return string|void
 */
function yasr_display_posts_callback($atts, $content, $shortcode_tag) {
    if(!is_page()) {
        return;
    }
    return (new YasrDisplayPosts($atts, $shortcode_tag))->returnShortcode();
}

