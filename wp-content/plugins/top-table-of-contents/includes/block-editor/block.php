<?php
/**
 * Plugin Name: Top Table of Contents
 */

function register_block() {
    register_block_type( __DIR__, array(
        'render_callback' => 'render_toc_block',
    ) );
}

function render_toc_block() {
    ob_start();
    echo do_shortcode( '[boomdevs_toc]' );
    return ob_get_clean();
}

add_action( 'init', 'register_block' );
