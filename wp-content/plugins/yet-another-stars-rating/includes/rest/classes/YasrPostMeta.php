<?php

if (!defined('ABSPATH')) {
    exit('You\'re not allowed to see this page');
} // Exit if accessed directly

class YasrPostMeta {
    /**
     * Load in init
     */
    public function restApiInit() {
        add_action('rest_api_init', array($this, 'registerPostMeta'));
    }

    /*
     * Get Yasr Post Meta Values and insert in the rest response
     * YOURSITE.COM/wp-json/wp/v2/posts?_field=meta
     * or
     * YOURSITE.COM/wp-json/wp/v2/posts/<POSTID>?_field=meta
     *
     */
    public function registerPostMeta () {
        //'post' here works also for CPT
        register_meta(
            'post',
            'yasr_overall_rating',
            array(
                'show_in_rest' => true,
                'single'       => true,
                'type'         => 'number',
                'auth_callback' => static function() {
                    return current_user_can('edit_posts');
                }
            )
        );

        register_meta(
            'post',
            'yasr_post_is_review',
            array(
                'show_in_rest' => true,
                'single'       => true,
                'type'         => 'string',
                'auth_callback' => static function() {
                    return current_user_can('edit_posts');
                }
            )
        );

        register_meta(
            'post',
            'yasr_auto_insert_disabled',
            array(
                'show_in_rest' => true,
                'single'       => true,
                'type'         => 'string',
                'auth_callback' => static function() {
                    return current_user_can('edit_posts');
                }
            )
        );

        register_meta(
            'post',
            'yasr_review_type',
            array(
                'show_in_rest' => true,
                'single'       => true,
                'type'         => 'string',
                'auth_callback' => function() {
                    return current_user_can('edit_posts');
                }
            )
        );
    }
}