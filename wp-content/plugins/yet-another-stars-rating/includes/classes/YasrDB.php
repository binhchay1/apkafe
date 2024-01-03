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
 * This class is used to connect to the DB
 *
 * Class YasrDB
 */
class YasrDB {

    /**
     * @var null | int
     */
    private static $set_id                      = null;

    /**
     * @var null | array
     * null in class declaration
     * later updated as array
     * Here I will save data from yasr_visitor_votes, with post_id as index
     * e.g. $visitor_votes_data['XXX'] = array of data
     * @see visitorVotes
     */
    private static $visitor_votes_data          = null;

    /**
     * @var null | array
     * null in class declaration
     * later updated as array
     * Here I will save the rating if a logged-in user has rated, with post_id as index
     * e.g. $current_user_rating_data['XXX'] = (int)rating
     * @see vvCurrentUserRating
     */
    private static $current_user_rating_data    = null;

    /**
     * @var null|int
     * null in class declaration
     * later updated as array
     * Here I will save the first multiset id
     * @see returnFirstSetId
     */
    private static $first_set_id                = null;

    /**
     * @var null|array
     * null in class declaration
     * later updated as array
     * Here I will save data from multisetFieldsAndID
     * @see multisetFieldsAndID
     */
    private static $multiset_fields_and_id_data = null;



    /**
     * Returns overall rating for single post or page
     *
     * @param $post_id void|bool|int
     *
     * @return mixed|null
     */
    public static function overallRating ($post_id=false) {
        //if values it's not passed get the post id, since version 1.6.9 this is just for yasr_add_schema function
        //and for a further check
        if (!is_int($post_id)) {
            $post_id = get_the_ID();
        }

        $overall_rating = get_post_meta($post_id, 'yasr_overall_rating', true);

        $overall_rating = yasr_validate_rating($overall_rating, 0);

        return $overall_rating;
    }

    /**
     * Returns ALL Overall Ratings
     *
     * @author Dario Curvino <@dudo>
     * @since  2.5.2
     * @return array|object|\stdClass[]|null
     */
    public static function allOverallRatings($limit=25, $offset=0) {
        global $wpdb;

        $query = "SELECT pm.post_id,
                         pm.meta_value   as vote,
                         pm.meta_id      as id,
                         p.post_author   as user_id,
                         p.post_date     as date
                  FROM $wpdb->postmeta   as pm,
                       $wpdb->posts      as p
                  WHERE pm.meta_key = 'yasr_overall_rating'
                      AND p.ID = pm.post_id
                      AND pm.meta_value > 0
                  ORDER BY p.post_date DESC
                  LIMIT %d
                  OFFSET %d";

        return $wpdb->get_results(
            $wpdb->prepare($query, $limit, $offset),
        ARRAY_A);

    }

    /**
     * Return the number of rows in postmeta where metakey = yasr_overall_rating
     *
     * @author Dario Curvino <@dudo>
     *
     * @since  3.3.3
     * @return int
     */
    public static function ovNumberOfRows() {
        global $wpdb;
        return (int)$wpdb->get_var(
            'SELECT COUNT(*)
                   FROM ' . $wpdb->postmeta .'
                   WHERE meta_key = "yasr_overall_rating"
                       AND meta_value > 0'
        );
    }

    /**
     * Return Visitor Votes for a single post or page
     *
     * @param bool|integer $post_id
     *
     * @return array|null
     *    array(
     *        'number_of_votes'  = (int)$user_votes->number_of_votes;
     *        'sum_votes'        = (int)$user_votes->sum_votes;
     *        'average'          = (int)average result
     *    )
     */
    public static function visitorVotes ($post_id=false) {
        //if values it's not passed get the post id, most of the cases and default one
        if (!is_int($post_id)) {
            $post_id = get_the_ID();
        }

        //check if there is already data for this post id, and if so, return it
        if(self::visitorVotesDataExists($post_id)) {
            return self::$visitor_votes_data[$post_id];
        }

        global $wpdb;

        $result = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT SUM(vote) as sum_votes,
                            COUNT(vote)  as number_of_votes
                        FROM " . YASR_LOG_TABLE .
                        "  WHERE post_id=%d
                           AND   vote > 0
                           AND   vote <= 5
                        ",
                $post_id
            )
        );

        $array_to_return = array(
            'number_of_votes' => 0,
            'sum_votes'       => 0,
            'average'         => 0
        );

        foreach ($result as $user_votes) {
            $array_to_return['number_of_votes'] = (int)$user_votes->number_of_votes;
            $array_to_return['sum_votes']       = (int)$user_votes->sum_votes;

            if ($array_to_return['number_of_votes'] > 0) {
                $array_to_return['average']  = ($array_to_return['sum_votes'] / $array_to_return['number_of_votes']);
                $array_to_return['average']  = round($array_to_return['average'], 1);
            }
        }

        self::$visitor_votes_data[$post_id] = $array_to_return;

        return $array_to_return;
    }

    /**
     * This function return true if an IP has already voted for a certain post
     * within a given date range.
     * Only for anonymous users
     *
     * @author Dario Curvino <@dudo>
     * @since  3.3.9
     *
     * @param        $post_id
     * @param        $start_date
     * @param        $end_date
     *
     * @return bool
     */
    public static function vvBetweenDates($post_id, $start_date, $end_date) {
        //if values it's not passed get the post id, most of the cases and default one
        if (!is_int($post_id)) {
            $post_id = get_the_ID();
        }

        global $wpdb;

        $result = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT id FROM ' . YASR_LOG_TABLE . ' 
                WHERE post_id = %d
                AND ip = %s
                AND user_id = 0 /* check for anonymous users */
                AND date BETWEEN %s AND %s',
                $post_id,
                yasr_get_ip(),
                $start_date,
                $end_date
            )
        );

        if(is_numeric($result) && $result > 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns ALL visitor votes in YASR_LOG_TABLE
     * used in stats page
     *
     * @author Dario Curvino <@dudo>
     * @since  2.5.2
     *
     * @param int $limit  | how many rows shows
     * @param int $offset | starting point
     *
     * @return array|object|null
     */
    public static function allVisitorVotes($limit=25, $offset=0) {
        $limit  = (int)$limit;
        $offset = (int)$offset;

        global $wpdb;

        $query = 'SELECT * FROM ' .YASR_LOG_TABLE.  ' ORDER BY date DESC LIMIT %d OFFSET %d';

        return $wpdb->get_results(
            $wpdb->prepare(
                $query, $limit, $offset
        ),ARRAY_A);
    }

    /**
     * Return the number of rows in YASR_LOG_TABLE
     *
     * @author Dario Curvino <@dudo>
     *
     * @since  3.3.3
     * @return int
     */
    public static function vvNumberOfRows () {
        global $wpdb;
        return (int)$wpdb->get_var('SELECT COUNT(*) FROM ' . YASR_LOG_TABLE);
    }

    /**
     * Check if current user has already rated, and if so, return the rating, or false otherwise
     *
     * @param int | bool $post_id
     *
     * @return bool|int
     */
    public static function vvCurrentUserRating($post_id = false) {
        //return 0 for anonymous user
        $user_id = get_current_user_id();
        if($user_id === 0) {
            return false;
        }

        //get current post id if not exists
        if (!is_int($post_id)) {
            $post_id = get_the_ID();
        }

        $post_id = (int)$post_id;

        //check if there is already data for this user, and if so, return it
        if(self::currentUserRatingDataExists($post_id)) {
           return self::$current_user_rating_data[$post_id];
        }

        global $wpdb;
        $rating = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT vote FROM "
                . YASR_LOG_TABLE .
                " WHERE post_id=%d
                    AND user_id=%d
                    LIMIT 1 ",
                $post_id, $user_id
            )
        );

        if ($rating === null) {
            self::$current_user_rating_data = null;
            return false;
        }

        $rating = (int)$rating;

        self::$current_user_rating_data[$post_id] = $rating;

        return $rating;
    }

    /**
     * Save VV rating
     * wpdb prepare not needed here
     * https://wordpress.stackexchange.com/questions/25947/wpdb-insert-do-i-need-to-prepare-against-sql-injection
     *
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     *
     * @param $post_id
     * @param $user_id
     * @param $rating
     * @param $ip_address
     *
     * @return bool|int
     */
    public static function vvSaveRating($post_id, $user_id, $rating, $ip_address=false) {
        if($ip_address === false) {
            $ip_address = yasr_ip_to_save();
        }

        global $wpdb;
        return $wpdb->replace(
            YASR_LOG_TABLE, array(
                'post_id' => $post_id,
                'user_id' => $user_id,
                'vote'    => $rating,
                'date'    => date('Y-m-d H:i:s'),
                'ip'      => $ip_address
            ),
            array('%d', '%d', '%d', '%s', '%s', '%s')
        );
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     *
     * @param $post_id
     * @param $user_id
     * @param $rating
     * @param $ip_address
     *
     * @return bool|int
     */
    public static function vvUpdateRating($post_id, $user_id, $rating, $ip_address=false) {
        if($ip_address === false) {
            $ip_address = yasr_ip_to_save();
        }

        global $wpdb;

        return $wpdb->update(
            YASR_LOG_TABLE, array(
            'post_id' => $post_id,
            'user_id' => $user_id,
            'vote'    => $rating,
            'date'    => date('Y-m-d H:i:s'),
            'ip'      => $ip_address
        ), array(
            'post_id' => $post_id,
            'user_id' => $user_id
        ), array('%d', '%d', '%d', '%s', '%s', '%s'), array('%d', '%d')
        );
    }

    /**
     * Run $wpdb->get_results for overall Rating
     *
     * @author Dario Curvino <@dudo>
     * @since  2.5.2
     *
     * @param bool|array $atts
     *
     * @return array|false|object|void
     */
    public static function rankingOverall($atts) {
        global $wpdb;

        //return custom query_result here
        //must returns rating and post_id
        $query = apply_filters('yasr_rankings_query_ov', $atts);

        //if query_results === $sql_params means that filters doesn't exists
        if ($query === $atts) {
            //default query_results
            $query = "SELECT pm.meta_value AS rating,
                         pm.post_id AS post_id
                  FROM $wpdb->postmeta AS pm,
                       $wpdb->posts AS p
                  WHERE  pm.post_id = p.ID
                      AND p.post_status = 'publish'
                      AND pm.meta_key = 'yasr_overall_rating'
                      AND pm.meta_value > 0
                  ORDER BY pm.meta_value DESC,
                           pm.post_id
                  LIMIT 10";

            $query_results = $wpdb->get_results($query);
        }
        else {
            $query_results = $query;
        }

        if ($query_results) {
            return $query_results;
        }
        return false;
    }

    /**
     * Returns yasr_most_or_highest_rated_posts with no params and 10 rows.
     *
     * @author Dario Curvino <@dudo>
     * @since  2.5.2
     *
     * @param array      $atts
     * @param            $ranking
     *
     * @return array|false|object|void
     */
    public static function rankingVV($atts, $ranking) {
        global $wpdb;

        //hooks here to return a query
        $query = apply_filters('yasr_rankings_query_vv', $atts, $ranking);

        //if no custom query is hooked
        if ($query === $atts) {
            $common_query = "SELECT post_id,
                COUNT(post_id) AS number_of_votes,
                ROUND(SUM(vote) / COUNT(post_id),1) AS rating
            FROM " . YASR_LOG_TABLE . ",
                $wpdb->posts AS p
            WHERE post_id = p.ID
                AND p.post_status = 'publish'
            GROUP BY post_id
                HAVING number_of_votes > 1
            ";

            if ($ranking === 'highest') {
                $order_by = ' ORDER BY rating DESC, number_of_votes DESC';
            }
            else {
                $order_by = ' ORDER BY number_of_votes DESC, rating DESC, post_id DESC';
            }

            $limit = ' LIMIT 10';
            $query = $common_query . $order_by . $limit;

            $query_results = $wpdb->get_results($query);
        }
        else {
            $query_results = $query;
        }

        if ($query_results) {
            return $query_results;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 2.6.3
     *
     * @param $atts
     *
     * @return array|false|object|void
     */
    public static function rankingTopUsers($atts) {
        global $wpdb;

        //return custom query here
        //must returns rating and post_id
        $query = apply_filters('yasr_rankings_query_tu', $atts);

        //if query === $sql_params (both are false) means that filters doesn't exist
        if ($query === $atts) {
            $query = 'SELECT COUNT(user_id) as total_count,
                        user_id as user
                    FROM ' . YASR_LOG_TABLE . ",
                        $wpdb->posts AS p
                    WHERE  post_id = p.ID
                        AND p.post_status = 'publish'
                    GROUP BY user_id
                    ORDER BY ( total_count ) DESC
                    LIMIT 10";

            $query_results = $wpdb->get_results($query);

        }
        else {
            $query_results = $query;
        }

        if ($query_results) {
            return $query_results;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.2
     *
     * @param        $set_id
     * @param  false $sql_atts
     *
     * @return bool|array
    */
    public static function rankingMulti($set_id, $sql_atts = false) {
        global $wpdb;
        if ($set_id === null) {
            $set_id = self::returnFirstSetId();
        }

        $set_id = (int) $set_id;

        //hooks here to return a query
        $query_result = apply_filters('yasr_rankings_multi_query', $sql_atts, $set_id);

        //if hook has run
        if ($query_result !== $sql_atts) {
            //but don't return an array or array is empty, return false
            if (!is_array($query_result) || empty($query_result)) {
                return false;
            }
            //else return hooks result
            return $query_result;
        }

        //if no hook is found in $query_result, return default ranking
        if ($query_result === $sql_atts) {
            //Create an array of post_id that has meta key = yasr_multiset_author_votes
            $array_post_id
                = $wpdb->get_results(
                "SELECT pm.post_id AS id
                FROM $wpdb->postmeta AS pm,
                     $wpdb->posts    AS p
                WHERE  pm.post_id = p.ID
                    AND p.post_status = 'publish'
                    AND pm.meta_key = 'yasr_multiset_author_votes'
                ORDER BY pm.post_id"
            );

            if (!is_array($array_post_id) || empty($array_post_id)) {
                return false;
            }

            //set fields name and ids
            $average_array = self::returnMultiAuthorAverageArray($array_post_id, $set_id);

            //Limit the array to N results
            return array_slice($average_array, 0, 10);
        }

        //should never happen
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 2.6.3
     *
     * @param $atts
     *
     * @return array|false|object|void
    */
    public static function rankingTopReviewers($atts) {
        global $wpdb;

        //return custom query here
        //must returns rating and post_id
        $query = apply_filters('yasr_rankings_query_tr', $atts);

        //if query === $sql_params (both are false) means that filters doesn't exist
        if ($query === $atts) {
            $query = "SELECT COUNT( pm.post_id ) AS total_count,
                          p.post_author    AS user,
                          u.user_login     AS name
                      FROM $wpdb->posts    AS p,
                           $wpdb->postmeta AS pm,
                           $wpdb->users    AS u
                      WHERE pm.post_id = p.ID
                          AND pm.meta_key = 'yasr_overall_rating'
                          AND p.post_status = 'publish'
                          AND p.post_author = u.ID
                      GROUP BY user
                      ORDER BY total_count DESC
                    LIMIT 5";

            $query_results = $wpdb->get_results($query);

        }
        else {
            $query_results = $query;
        }

        if ($query_results) {
            return $query_results;
        }
        return false;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.7.2
     *
     * @param int|bool|string $set_id
     * @param string          $ranking
     * @param false           $sql_atts
     *
     * @return array|false|object
    */
    public static function rankingMultiVV($set_id, $ranking = 'most', $sql_atts = false) {
        global $wpdb;
        //if set_id is not set (e.g. in rest parameter setid is not set)
        if ($set_id === null) {
            $set_id = self::returnFirstSetId();
        }

        $set_id = (int) $set_id;

        //hooks here to return a query
        $query = apply_filters('yasr_rankings_multivv_query', $sql_atts, $ranking, $set_id);

        //if no custom query is hooked
        if ($query === $sql_atts) {
            $query = $wpdb->prepare(
                "SELECT CAST((SUM(l.vote)/COUNT(l.vote)) AS DECIMAL(2,1)) AS rating,
                           COUNT(l.vote) AS number_of_votes,
                           l.post_id
                       FROM " . YASR_LOG_MULTI_SET . " AS l,
                           $wpdb->posts AS p
                       WHERE l.set_type = %d
                           AND p.ID = l.post_id
                           AND p.post_status = 'publish'
                       GROUP BY l.post_id", '%d'
            );
            if ($ranking === 'highest') {
                $query .= ' ORDER BY rating DESC';
            }
            else {
                $query .= ' ORDER BY number_of_votes DESC';
            }
            $query .= ' LIMIT 10';

            $query_results = $wpdb->get_results($query);

        }
        else {
            $query_results = $query;
        }

        if ($query_results) {
            return $query_results;
        }
        return false;
    }

    /**
     * Returns the first set id or false if not exists
     *
     * @author Dario Curvino <@dudo>
     * @since  2.6.8
     * @return false|int
     */
    public static function returnFirstSetId() {
        if(self::$first_set_id !== null) {
            return self::$first_set_id;
        }

        global $wpdb;
        $set_id = false;

        $result = $wpdb->get_results(
            "SELECT set_id
                        FROM " . YASR_MULTI_SET_NAME_TABLE . "
                    ORDER BY set_id
                    LIMIT 1"
        );

        if (is_array($result) && !empty($result[0]) && property_exists($result[0], 'set_id')) {
            $set_id = (int) $result[0]->set_id;
        }

        self::$first_set_id = $set_id;

        return $set_id;
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.2.2
     * @return array|object|\stdClass[]|null
     */
    public static function returnMultiSetNames() {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM " . YASR_MULTI_SET_NAME_TABLE . " ORDER BY set_id");
    }

    /**
     * This function loops an array of post_id that that has yasr_multiset_author_votes as meta key.
     * Returns an ordered array by rating's average for each post id
     *
     * @author Dario Curvino <@dudo>
     * @since  2.7.3
     *
     * @param  $array_post_id
     * @param  $set_id
     *
     * @return array
     */
    public static function returnMultiAuthorAverageArray($array_post_id, $set_id) {
        $average_array = array();

        $i = 0;
        //loop the array
        foreach ($array_post_id as $post_id) {
            $average = self::returnMultiSetAverage($post_id->id, $set_id, false);
            if ($average > 0) {
                $average_array[$i]['post_id'] = $post_id->id;
                $average_array[$i]['rating']  = $average;
            }
            $i++;
        }

        //order the array by average rating
        array_multisort(array_column($average_array, 'rating'), SORT_DESC, SORT_NUMERIC, $average_array);

        return $average_array;
    }

    /**
     * This functions returns an array with all the values to print the multiset
     *
     * array (
     *     array (
     *         'id' => 0,
     *         'name' => 'Field 1',
     *         'average_rating' => 3.5,
     *     ),
     *     array (
     *         'id' => 1,
     *         'name' => 'Field 2',
     *         'average_rating' => 3,
     *     )
     * )
     *
     * @param integer $set_id the set id
     * @param array   $set_fields an array with fields names and id
     * @param integer|bool $post_id the post_id
     *
     * @return bool | array
     */
    public static function returnArrayFieldsRatingsAuthor($set_id, $set_fields, $post_id = false) {
        $array_to_return = array();
        $set_id          = (int) $set_id;

        if (!$set_fields) {
            return false;
        }

        if (!is_int($post_id)) {
            $post_id = get_the_ID();
        }

        //get meta values (field id and rating)
        $set_post_meta_values = get_post_meta($post_id, 'yasr_multiset_author_votes', true);

        //index
        $i = 0;
        //always returns field id and name
        foreach ($set_fields as $fields_ids_and_names) {
            $array_to_return[$i]['id']             = (int) $fields_ids_and_names['id'];
            $array_to_return[$i]['name']           = $fields_ids_and_names['name'];
            $array_to_return[$i]['average_rating'] = 0;

            //if there is post meta
            if ($set_post_meta_values) {
                //first, loop saved fields and ratings
                foreach ($set_post_meta_values as $saved_set_id) {
                    //if the saved set is the same selected
                    if ($saved_set_id['set_id'] === $set_id) {
                        //loop the saved arrays
                        foreach ($saved_set_id['fields_and_ratings'] as $single_value) {
                            //if field id is the same, add the rating
                            if ($array_to_return[$i]['id'] === $single_value->field) {
                                //save the rating
                                $array_to_return[$i]['average_rating'] = $single_value->rating;
                            }
                        }
                    }
                }
            }
            //this is for list the set names
            $i++;
        }
        return $array_to_return;
    }

    /**
     * Returns *ALL* multiset votes in YASR_LOG_MULTI_SET
     * used in stats page
     *
     * @author Dario Curvino <@dudo>
     * @since 2.5.2
     * @return array|object|null
     */
    public static function returnLogMulti($limit, $offset) {
        global $wpdb;

        $query = 'SELECT * FROM ' . YASR_LOG_MULTI_SET . '
                    ORDER BY date DESC, set_type ASC, post_id DESC
                    LIMIT %d
                    OFFSET %d';

        return $wpdb->get_results(
            $wpdb->prepare($query, $limit, $offset),
            ARRAY_A
        );
    }

    /**
     * Return the number of rows in YASR_LOG_MULTI_SET
     *
     * @author Dario Curvino <@dudo>
     *
     * @since  3.3.3
     * @return int
     */
    public static function returnLogMultiNumberOfRows () {
        global $wpdb;
        return (int)$wpdb->get_var('SELECT COUNT(*) FROM ' . YASR_LOG_MULTI_SET);
    }

    /**
     * Get from the db all the values for VisitorMultiSet
     *
     * @param      $post_id
     * @param      $set_id
     * @param bool $visitor_multiset
     * @param int  $comment_id
     *
     * @return array|bool
     */
    public static function returnMultisetContent($post_id, $set_id, $visitor_multiset = false, $comment_id = 0) {
        $set_id     = (int) $set_id;
        $post_id    = (int) $post_id;
        $comment_id = (int) $comment_id;

        if ($post_id === 0 && $comment_id === 0) {
            return false;
        }

        //set fields name and ids
        $set_fields = self::multisetFieldsAndID($set_id);

        if ($set_fields === false) {
            return false;
        }

        if ($visitor_multiset === true || $comment_id > 0) {
            return self::returnArrayFieldsRatingsVisitor($set_id, $set_fields, $comment_id, $post_id);
        }

        //return
        return self::returnArrayFieldsRatingsAuthor($set_id, $set_fields, $post_id);
    }

    /**
     * Return an average of a given multiset_content if provided.
     * Otherwise, it will get the average using the post_id and set_id
     *
     * @param int        $post_id
     * @param int        $set_id
     * @param bool       $visitor_multiset
     * @param bool|array $multiset_content | This is useful to avoid double query
     *
     * @return float|int
     */
    public static function returnMultiSetAverage($post_id, $set_id, $visitor_multiset, $multiset_content = false) {
        if ($multiset_content === false) {
            $post_id = (int) $post_id;
            $set_id  = (int) $set_id;

            if ($visitor_multiset === true) {
                $multiset_content = self::returnMultisetContent($post_id, $set_id, true);
            }
            else {
                $multiset_content = self::returnMultisetContent($post_id, $set_id);
            }
        }

        if (!is_array($multiset_content)) {
            return 0;
        }
        //default values
        $multiset_vote_sum    = 0;
        $multiset_rows_number = 0;

        foreach ($multiset_content as $set_content) {
            $multiset_vote_sum    = $multiset_vote_sum + $set_content['average_rating'];
            $multiset_rows_number = $multiset_rows_number + 1;
        }

        return round($multiset_vote_sum / $multiset_rows_number, 1);
    }

    /**
     * This functions returns an array with all the value to print the multiset
     * array (
     *     array (
     *         'id' => 0,
     *         'name' => 'Field 1',
     *         'average_rating' => 3.5
     *         'number_of_votes' => 3
     *     ),
     *     array (
     *         'id' => 1,
     *         'name' => 'Field 2',
     *         'average_rating' => 3,
     *         'number_of_votes' => 3,
     *     )
     *
     * @param int   $set_id the set id
     * @param array $set_fields an array with fields names and id
     * @param int   $comment_id the comment_id
     * @param int   $post_id the post_id
     *
     * @return bool | array
     */
    public static function returnArrayFieldsRatingsVisitor($set_id, $set_fields, $comment_id, $post_id) {
        $array_to_return = array();

        global $wpdb;

        $set_id     = (int) $set_id;
        $comment_id = (int) $comment_id;
        $post_id    = (int) $post_id;

        if (!$set_fields) {
            return false;
        }

        if ($post_id < 1) {
            $and_post_id = '';
        }
        else {
            $and_post_id = 'AND l.post_id=' . $post_id;
        }

        //get meta values (field id and rating)
        $ratings = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT CAST((SUM(l.vote)/COUNT(l.vote)) AS DECIMAL(2,1)) AS average_rating,
                                COUNT(l.vote) AS number_of_votes,
                                field_id AS field
                            FROM " . YASR_LOG_MULTI_SET . " AS l
                            WHERE l.set_type=%d
                                AND l.comment_id=%d " . esc_sql($and_post_id) . "
                            GROUP BY l.field_id
                            ORDER BY l.field_id", $set_id, $comment_id
            ), ARRAY_A
        );

        //index
        $i = 0;
        //always returns field id and name
        foreach ($set_fields as $fields_ids_and_names) {
            $array_to_return[$i]['id']              = (int) $fields_ids_and_names['id'];
            $array_to_return[$i]['name']            = $fields_ids_and_names['name'];
            $array_to_return[$i]['average_rating']  = 0;
            $array_to_return[$i]['number_of_votes'] = 0;

            //if there are ratings
            if ($ratings) {
                //loop the saved arrays
                foreach ($ratings as $single_value) {
                    //if field id is the same, add the rating
                    if ($array_to_return[$i]['id'] === (int) $single_value['field']) {
                        $array_to_return[$i]['average_rating']  = $single_value['average_rating'];
                        $array_to_return[$i]['number_of_votes'] = (int) $single_value['number_of_votes'];
                    }
                }

            }
            //this is for list the set names
            $i++;
        }

        return $array_to_return;
    }

    /**
     * This function returns a multidimensional array of multiset ID and Fields
     *
     *    array (
     *        array (
     *            'id' => '0',
     *            'name' => 'Field1',
     *        ),
     *        array (
     *            'id' => '1',
     *            'name' => 'Field2',
     *        ),
     *    )
     *
     * @param int $set_id
     *
     * @return array|bool
     */
    public static function multisetFieldsAndID($set_id) {
        $set_id = (int)$set_id;

        if(self::multisetFieldsAndIdDataExists($set_id)) {
            return self::$multiset_fields_and_id_data;
        }

        global $wpdb;

        $result = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT f.field_id AS id,
                        f.field_name AS name
                        FROM " . YASR_MULTI_SET_FIELDS_TABLE . " AS f
                        WHERE f.parent_set_id=%d
                        ORDER BY f.field_id
                        ", $set_id
            ), ARRAY_A
        );

        if (empty($result)) {
            self::$multiset_fields_and_id_data = null;
            return false;
        }

        self::$multiset_fields_and_id_data = $result;
        self::$set_id = $set_id;

        return $result;
    }

    /**
     * Returns the length of a MultiSet
     *
     * @author Dario Curvino <@dudo>
     * @since 2.9.7
     *
     * @param $set_id
     *
     * @return int
     */
    public static function multisetLength($set_id) {
        $set_id = (int)$set_id;

        global $wpdb;

        return (int)$wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*)
                       FROM ' . YASR_MULTI_SET_FIELDS_TABLE .
                       ' WHERE parent_set_id=%d',
            $set_id)
        );
    }

    /**
     * This function return true if an IP has already for a multiset in a certain post
     * within a given date range.
     * Only for anonymous users
     *
     * @author Dario Curvino <@dudo>
     * @since  3.4.3
     *
     * @param        $post_id
     * @param        $set_id
     * @param        $start_date
     * @param        $end_date
     *
     * @return bool
     */
    public static function mvBetweenDates($post_id, $set_id, $start_date, $end_date) {
        //if values it's not passed get the post id, most of the cases and default one
        if (!is_int($post_id)) {
            $post_id = get_the_ID();
        }

        $multiset_len = self::multisetLength($set_id);

        global $wpdb;

        $result = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT id FROM ' . YASR_LOG_MULTI_SET . ' 
                WHERE post_id = %d
                AND set_type  = %d
                AND ip = %s
                AND user_id = 0 /* check for anonymous users */
                AND date BETWEEN %s AND %s
                LIMIT %d',
                $post_id,
                $set_id,
                yasr_get_ip(),
                $start_date,
                $end_date,
                $multiset_len
            ), ARRAY_A
        );

        if(is_array($result)) {
            //count the number of element into the result array, and allow the rating if ratings stored are <
            //then multiset leng
            $array_leng = count($result);
            if ($array_leng < $multiset_len) {
                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * Save rating for multi set visitor
     *
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     *
     * @param $id_field
     * @param $set_id
     * @param $post_id
     * @param $rating
     * @param $user_id
     * @param $ip_address
     *
     * @return bool|int
     */
    public static function mvSaveRating($id_field, $set_id, $post_id, $rating, $user_id, $ip_address=false) {
        global $wpdb;

        if($ip_address === false) {
            $ip_address = yasr_ip_to_save();
        }

        //no need to insert 'comment_id', it is 0 by default
        return
            $wpdb->replace(
                YASR_LOG_MULTI_SET,
                array(
                    'field_id' => $id_field,
                    'set_type' => $set_id,
                    'post_id'  => $post_id,
                    'vote'     => $rating,
                    'user_id'  => $user_id,
                    'date'     => date('Y-m-d H:i:s'),
                    'ip'       => $ip_address
                ),
                array("%d", "%d", "%d", "%d", "%d", "%s", "%s")
            );
    }


    /**
     * Update rating for multi set visitor
     *
     * @author Dario Curvino <@dudo>
     * @since  2.7.7
     *
     * @param $id_field
     * @param $set_id
     * @param $post_id
     * @param $rating
     * @param $user_id
     * @param $ip_address
     *
     * @return bool|int
     */
    public static function mvUpdateRating($id_field, $set_id, $post_id, $rating, $user_id, $ip_address=false) {
        global $wpdb;

        if($ip_address === false) {
            $ip_address = yasr_ip_to_save();
        }

        //no need to insert 'comment_id', it is 0 by default
        return $wpdb->update(
            YASR_LOG_MULTI_SET, array(
            'field_id' => $id_field,
            'set_type' => $set_id,
            'post_id'  => $post_id,
            'vote'     => $rating,
            'user_id'  => $user_id,
            'date'     => date('Y-m-d H:i:s'),
            'ip'       => $ip_address
        ), array(
            'field_id' => $id_field,
            'set_type' => $set_id,
            'post_id'  => $post_id,
            'user_id'  => $user_id
        ), array("%d", "%d", "%d", "%d", "%d", "%s", "%s"), array("%d", "%d", "%d", "%d")
        );
    }


    /**
     * Return the postmeta itemType
     *
     * @param bool|int $term_id
     *
     * @return bool|string
     */
    public static function getItemType($term_id = false) {
        $review_types = YasrRichSnippetsItemTypes::returnItemTypes();

        //if term_id is not an int, use get_post_meta
        if (!is_int($term_id)) {
            $post_id = get_the_ID();

            if (!$post_id) {
                return false;
            }
            $result = get_post_meta($post_id, 'yasr_review_type', true);
        }
        else {
            $result = get_term_meta($term_id, 'yasr_review_type', true);
        }

        if ($result) {
            $snippet_type = trim($result);

            //to keep compatibility with version <2.2.3
            if ($snippet_type === 'Place') {
                $snippet_type = 'LocalBusiness';
            }
            //to keep compatibility with version <2.2.3
            if ($snippet_type === 'Other') {
                $snippet_type = 'BlogPosting';
            }
            if (!in_array($snippet_type, $review_types, true)) {
                $snippet_type = YASR_ITEMTYPE;
            }
        }
        else {
            $snippet_type = YASR_ITEMTYPE;
        }

        //to keep compatibility with version <2.2.3
        if ($snippet_type === 'Place') {
            $snippet_type = 'LocalBusiness';
        }
        //to keep compatibility with version <2.2.3
        if ($snippet_type === 'Other') {
            $snippet_type = 'BlogPosting';
        }

        return $snippet_type;
    }

    /**
     * check if for the current post id, $visitor_votes_data already exists.
     * This is needed to avoid duplicate query
     * (e.g. if shortcode with same post_id is included more than once in the same post)
     * @see visitorVotes
     *
     * @author Dario Curvino <@dudo>
     * @since 3.3.0
     *
     * @param $post_id
     *
     * @return bool
     */
    public static function visitorVotesDataExists($post_id) {
        if(is_array(self::$visitor_votes_data) && array_key_exists($post_id, self::$visitor_votes_data)) {
            return true;
        }
        return false;
    }

    /**
     * check if for the current post id, and current user id, $current_user_rating_data already exists.
     * This is needed to avoid duplicate query
     * (e.g. if shortcode with same post_id is included more than once in the same post)
     * @see vvCurrentUserRating
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.0
     *
     * @param $post_id
     *
     * @return bool
     */
    public static function currentUserRatingDataExists($post_id) {
        if(is_array(self::$current_user_rating_data) && array_key_exists($post_id, self::$current_user_rating_data)) {
            return true;
        }
        return false;
    }

    /**
     * Check if for the current $set_id, multisetFieldsAndID has already run
     * @see multisetFieldsAndID
     *
     * @author Dario Curvino <@dudo>
     *
     * @since 3.3.0
     *
     * @param $set_id
     *
     * @return bool
     */
    public static function multisetFieldsAndIdDataExists($set_id) {
        if (is_array(self::$multiset_fields_and_id_data) && (self::$set_id === $set_id)) {
            return true;
        }
        return false;
    }

    /**
     * This query must be used with hook posts_join_paged
     * Select number of votes and rating from YASR_LOG_TABLE
     *
     * DOESN'T HAVE THE ORDER BY
     * @see returnQueryOrderByPostsVV
     *
     * @author Dario Curvino <@dudo>
     *
     * @since  3.3.0
     * @return string
     */
    public static function returnQuerySelectPostsVV () {
        global $wpdb;
        return "LEFT JOIN
            (
                SELECT post_id, COUNT(post_id) AS number_of_votes, ROUND(SUM(vote) / COUNT(post_id), 1) AS rating
                FROM " . YASR_LOG_TABLE . ", ". $wpdb->posts ." AS p
                WHERE post_id = p.ID
                AND p.post_status = 'publish'
                GROUP BY post_id
                HAVING number_of_votes >= 1
            )  rating ON rating.post_id = ". $wpdb->posts .".ID";
    }

    /**
     * This query must be used with hook posts_orderby and after posts_join_paged
     * Order the previous select values
     *
     * @author Dario Curvino <@dudo>
     * @since 3.3.0
     *
     * @param $order_by
     * @param $order
     *
     * @return string
     * @see returnQuerySelectPostsVV
     */
    public static function returnQueryOrderByPostsVV ($order_by, $order='DESC') {
        $order = strtoupper($order);
        if($order !== 'ASC') {
            $order = 'DESC';
        }

        if($order_by === 'vv_highest') {
            return " rating $order, number_of_votes $order";
        }
        else {
            return " number_of_votes $order, rating $order";
        }
    }


    /**
     * Do a connection with PDO
     *
     * @author Dario Curvino <@dudo>
     *
     * @since  3.3.3
     * @return \PDO|void
     */
    public static function PDOConnect() {
        try {
            $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            wp_die(esc_html__('Error Establishing A Database Connection', 'yet-another-stars-rating'));
        }
    }
}
