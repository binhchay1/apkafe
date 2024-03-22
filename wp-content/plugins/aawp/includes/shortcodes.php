<?php
/**
 * Shortcodes
 *
 * @since       3.4.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

$aawp_shortcode = aawp_get_shortcode();

/**
 * Show disclaimer
 *
 * @param $atts
 *
 * @return null|string
 */
function aawp_shortcode_show_disclaimer( $atts ) {

    $options = aawp_get_options();

    $disclaimer_text = ( !empty ( $options['general']['disclaimer_text'] ) ) ? $options['general']['disclaimer_text'] : null;

    if ( ! $disclaimer_text )
        return null;

    // Last update
    if ( strpos( $disclaimer_text ,'%last_update%') !== false ) {

        $last_update = aawp_get_cache_last_update();

        if ( ! empty ( $last_update ) ) {
            $last_update = aawp_format_last_update( $last_update );

            if ( $last_update ) {
                $disclaimer_text = aawp_replace_last_update_placeholder( $disclaimer_text, $last_update );
            }
        }
    }

    // Output
    return '<p class="aawp-disclaimer">' . stripslashes($disclaimer_text) . '</p>';
}
add_shortcode( $aawp_shortcode . '_disclaimer', 'aawp_shortcode_show_disclaimer' );

/**
 * Show global last update formatted
 *
 * @param $atts
 *
 * @return string
 */
function aawp_shortcode_show_last_update( $atts ) {

    $last_update = aawp_get_cache_last_update();
    $last_update = aawp_format_last_update( $last_update );

    return $last_update;
}
add_shortcode( AAWP_SHORTCODE . '_last_update', 'aawp_shortcode_show_last_update' );