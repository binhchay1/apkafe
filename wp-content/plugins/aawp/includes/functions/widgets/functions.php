<?php
/**
 * Widget Functions
 *
 * @package     AAWP\Functions\Widgets
 * @since       3.2.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Build and execute widget shortcode
 */
function aawp_widget_do_shortcode( $listener, $values, $template, $atts = array() ) {

    if ( ! empty ( $listener ) && ! empty( $values ) && ! empty( $template ) ) {

        // Build Shortcode
        $shortcode = '[aawp';
        $shortcode .= ' ' . $listener . '="' . $values . '"';
        $shortcode .= ' template="' . $template . '"';
        $shortcode .= ' origin="widget"';

        if ( is_array( $atts ) && sizeof( $atts ) > 0 ) {

            foreach ( $atts as $key => $value ) {
                $shortcode .= ' ' . $key . '="' . $value . '"';
            }
        }

        //var_dump($template);

        $shortcode .= '/]';

        // Execute Shortcode
        echo do_shortcode( $shortcode );
    }
}

/*
 * Styles
 */
function aawp_get_widget_styles( $type = null ) {

    $styles = array(
        '0' => __('Standard', 'aawp'),
    );

    $styles = apply_filters( 'aawp_widget_styles', $styles, $type );

    return $styles;
}

/*
 * Templates
 */
function aawp_get_widget_templates( $type = null ) {

    $templates = array(
        array( 'slug' => 0, 'name' => __( 'Please select...', 'aawp' ) ),
        array( 'slug' => 'widget-vertical', 'name' => __( 'Standard', 'aawp' ) ),
        array( 'slug' => 'widget-small', 'name' => __( 'Small', 'aawp' ) ),
    );

    $templates = apply_filters( 'aawp_widget_templates', $templates, $type );

    return $templates;
}

function aawp_get_default_widget_template() {
    return 'widget-vertical';
}

/**
 * Cleanup output when using a text widgets in combination with our shortcodes
 */
add_filter('widget_text', function( $text ) {

    if (strpos( $text, 'aawp') !== false) {
        $text = str_replace( array( "\r", "\n", "<p></p>" ), '', $text );
    }

    return $text;
}, 10, 1);