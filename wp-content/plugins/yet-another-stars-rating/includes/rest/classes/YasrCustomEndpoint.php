<?php

if (!defined('ABSPATH')) {
    exit('You\'re not allowed to see this page');
} // Exit if accessed directly

class YasrCustomEndpoint extends WP_REST_Controller {

    protected $namespace;
    protected $version;

    /**
     * hook into rest_api_init
     */
    public function restApiInit() {
        add_action('rest_api_init',  array($this, 'customEndpoint'));
    }


    /**
     * Load all register_rest_route
     * Since version 2.5.6 this->version must not have the ending slash
     * https://wordpress.org/support/topic/my-dashboard-is-error/#post-13748117
     */
    public function customEndpoint () {
        $this->namespace = 'yet-another-stars-rating/';
        $this->version   = 'v1';

        $this->multisetEndpoint();
        $this->rankingEndpoint();
    }

    /**
     * This function will register base yasr-multiset
     *
     * @author Dario Curvino <@dudo>
     * @since 2.5.2
     */
    public function multisetEndpoint () {
        /*
         * Param for this route must be
         * YOURSITE/wp-json/yet-another-stars-rating/v1/yasr-multiset/?set_id&post_id=<ID>
         *     OPTIONAL &visitor=1 OPTIONAL comment_id
         */

        $base = 'yasr-multiset';

        $args = array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => array($this, 'multiSet'),
            'args' => array(
                'set_id' => array(
                    'required' => true,
                    'sanitize_callback' => array($this, 'sanitizeInput')
                ),
                'post_id' => array(
                    'required' => false,
                    'sanitize_callback' => array($this, 'sanitizeInput')
                ),
                'visitor' => array(
                    'default'  => 0,
                    'required' => false,
                    'sanitize_callback' => array($this, 'sanitizeInput')
                ),
                'comment_id' => array (
                    'required' => false,
                    'sanitize_callback' => array($this, 'sanitizeInput')
                )
            ),
            'permission_callback' => static function () {
                return true;
            }
        );

        register_rest_route(
            $this->namespace . $this->version,
            $base,
            $args
        );
    }

    /**
     *
     * Returns Multi Set
     * must be public
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     *
     */
    public function multiSet ($request) {
        /*
         * Get cleaned params
         */
        $set_id     = $request['set_id'];     //this is mandatory, so it is sanitized and is always an int
        $post_id    = $request['post_id'];    //not mandatory, can be null
        $visitor    = $request['visitor'];    //not mandatory, can be null
        $comment_id = $request['comment_id']; //not mandatory, can be null

        //if $visitor === 1 then get data from yasr_visitor_multiset
        if($visitor === 1) {
            return $this->returnVisitorMultiset($post_id, $set_id);
        }

        //comment_id is an int here, if is > than 0 is valid
        if($comment_id !== null && $comment_id > 0) {
            return $this->returnCommentMultiset($set_id, $comment_id);
        }

        //last one return author multiset
        return $this->returnAuthorMultiset($post_id, $set_id);

    }

    /**
     * This function will register base yasr-multiset
     * @author Dario Curvino <@dudo>
     * @since 2.5.2
     */
    public function rankingEndpoint () {
        /*
         * Param for this route must be
         *
         * source can be:
         * overall_rating
         * visitor_votes
         *
         * YOURSITE/wp-json/yet-another-stars-rating/v1/yasr-rankings/?source
         *
         */
        $base = 'yasr-rankings';

        $args = array(
            'source' => array(
                'required' => true,
                'sanitize_callback' => array($this, 'sanitizeInput')
            ),
            'show' => array(
                'required' => false,
                'sanitize_callback' => array($this, 'sanitizeInput')
            ),
        );

        //This hooks will adds params to query
        $args = apply_filters('yasr_rest_rankings_args', $args);

        $options = array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => array($this, 'ranking'),
            'args' => $args,
            'permission_callback' => static function () {
                return true;
            }
        );

        register_rest_route(
            $this->namespace . $this->version,
            $base,
            $options
        );
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 2.5.2
     *
     * @param $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function ranking($request){
        //Get cleaned params
        $source     = $request['source'];

        $data_to_return = YasrShortcodesAjax::rankingData($source, $request);

        if ($data_to_return === false) {
            return new WP_Error(
                'no_data',
                __('No posts found. Try remove some filters', 'yet-another-stars-rating'),
                400
            );
        }

        $response = new WP_REST_Response($data_to_return);
        $response->set_status(200);

        return $response;
    }


    /**
     * Sanitizie input, must be public
     *
     * @param $param
     * @param $request
     * @param $key
     *
     * @return int|void|WP_Error
     */
    public function sanitizeInput ($param, $request, $key) {

        if($key === 'post_id') {
            $post_id = (int)$param;

            //if post_id is null means that is not set in the request.
            //(int) will convert it to 0.
            //When WordPress is installed, the first post has ID = 1
            if ($post_id === 0) {
                return new WP_Error (
                    'wrong_post_id',
                    __('Invalid Post ID', 'yet-another-stars-rating'),
                    400
                );
            }

            //Check if exists a post with this ID
            if (get_post($post_id) === null) {
                return new WP_Error (
                    'wrong_post_id',
                    __('Post ID doesn\'t exists', 'yet-another-stars-rating'),
                    404
                );
            }
            return $post_id;
        }

        if($key === 'set_id') {
            return (int)$param;
        }

        if($key === 'visitor') {
            return (int)$param;
        }

        if($key === 'comment_id') {
            $comment_id = (int)$param;

            //When WordPress is installed, the first comment has ID = 1
            if ($comment_id === 0) {
                return new WP_Error (
                    'wrong_comment_id',
                    esc_html__('Invalid Comment ID', 'yet-another-stars-rating'),
                    400
                );
            }

            //Check if exists a post with this ID
            if (get_comment($comment_id) === null) {
                return new WP_Error (
                    'wrong_comment_id',
                    'This comment doesn\'t exists',
                    404
                );
            }

            return $comment_id;
        }

        if($key === 'source') {
            if( $param === 'overall_rating'
                || $param === 'visitor_votes'
                || $param === 'author_multi'
                || $param === 'visitor_multi' ) {
                return trim($param);
            }
        }

        if($key === 'show') {
            if($param === 'highest') {
                return $param;
            }
            return 'most';
        }

        return apply_filters('yasr_rest_sanitize', $key, $param);

    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  refactored 2.9.8
     * @param  $post_id
     * @param  $set_id
     *
     * @return bool|\WP_Error|\WP_REST_Response
     */
    protected function returnVisitorMultiset($post_id, $set_id) {
        if(!$post_id) {
            return $this->returnErrorPostId();
        }

        $data_to_return['yasr_visitor_multiset'] = YasrDB::returnMultisetContent($post_id, $set_id, true);
        if ($data_to_return['yasr_visitor_multiset'] === false) {
            return $this->returnInvalidMultiset();
        }

        return $this->returnMultisetResponse($data_to_return, $set_id);
    }


    /**
     * @author Dario Curvino <@dudo>
     * @since 2.9.8
     * @param $set_id
     * @param $comment_id
     *
     * @return \WP_Error|\WP_REST_Response
     */
    protected function returnCommentMultiset($set_id, $comment_id) {
        //if comment id is valid, post_id can be ignored at all
        $data_to_return['yasr_comment_multiset'] = YasrDB::returnMultisetContent(false, $set_id, false, $comment_id);
        if ($data_to_return['yasr_comment_multiset'] === false) {
            return $this->returnInvalidMultiset();
        }

        return $this->returnMultisetResponse($data_to_return, $set_id);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 2.9.8
     * @param $post_id
     * @param $set_id
     *
     * @return bool|\WP_Error|\WP_REST_Response
     */
    protected function returnAuthorMultiset($post_id, $set_id) {
        if(!$post_id) {
            return $this->returnErrorPostId();
        }

        $data_to_return['yasr_multiset'] = YasrDB::returnMultisetContent($post_id, $set_id);
        if ($data_to_return['yasr_multiset'] === false) {
            return $this->returnInvalidMultiset();
        }

        return $this->returnMultisetResponse($data_to_return, $set_id);
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since 2.9.8
     */
    protected function returnInvalidMultiset() {
        return new WP_Error(
            'invalid_multiset',
            __('This Multi Set doesn\'t exists', 'yet-another-stars-rating'),
            400
        );
    }

    /**
     * This function is called when post_is is mandatory
     *
     * @author Dario Curvino <@dudo>
     * @since  2.9.8
     *
     * @return \WP_Error
     */
    protected function returnErrorPostId() {
        return new WP_Error(
            'missing_post_id',
            'You must specific post id here',
            400
        );
    }

    /**
     * @author Dario Curvino <@dudo>
     * @since  2.9.8
     *
     * @param $data_to_return
     * @param $set_id
     *
     * @return \WP_REST_Response
     */
    protected function returnMultisetResponse($data_to_return, $set_id) {
        $data = array(
            'set_id' => $set_id
        );

        $response = new WP_REST_Response(array_merge($data, $data_to_return));
        $response->set_status(200);

        return $response;
    }
}