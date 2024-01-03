<?php
/**
 * Affiliate Links Extended
 *
 * @package     AAWP\Functions\Components
 * @since       3.2.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Register customizations component
 */
function aawp_settings_register_affiliate_links_extended_component( $functions ) {

    $functions[] = 'affiliate-links-extended';

    return $functions;
}
add_filter( 'aawp_settings_functions', 'aawp_settings_register_affiliate_links_extended_component' );


/*
 * Extending supported shortcode attributes
 */
function aawp_extend_supported_attributes_for_affiliate_links_extended( $supported, $type ) {

    // All
    array_push( $supported, 'link_type', 'keywords' );

    return $supported;
}
add_filter( 'aawp_func_supported_attributes', 'aawp_extend_supported_attributes_for_affiliate_links_extended', 10, 2 );

/*
 * Add to Cart Links: Settings
 */
function aawp_settings_add_to_cart_links() {

    $output_options = aawp_get_options('output');

    $add_to_cart = ( isset ( $output_options['button_cart_links'] ) && $output_options['button_cart_links'] == '1' ) ? 1 : 0;

    $docs_url = ( aawp_is_lang_de() ) ? 'https://partnernet.amazon.de/help/node/topic/G9SMD8TQHFJ7728F' : 'https://affiliate-program.amazon.com/help/node/topic/G9SMD8TQHFJ7728F';
    ?>

    <h4><?php _e('Add to Cart Links (90-days-cookie)', 'aawp'); ?></h4>
    <p>
        <input type="checkbox" id="aawp_button_cart_links" name="aawp_output[button_cart_links]" value="1" <?php echo($add_to_cart == 1 ? 'checked' : ''); ?>>
        <label for="aawp_button_cart_links"><?php _e('Check in order to use "add to cart" links instead of the default links', 'aawp'); ?></label>
    </p>
    <p>
        <small><?php _e('<strong>Note:</strong> By enabling this option please be aware that your visitors will <u>not be redirected to the product page</u>. Instead they will be forwarded to a new page on Amazon in order to confirm the add to cart action. Afterwards the 90-days-cookie will be set.', 'aawp'); ?> <?php printf( wp_kses( __( 'For more information about "add to cart links" please take a look into the <a href="%s" target="_blank">official documentation</a> on Amazon Associates.', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => '_blank' ) ) ), esc_url( $docs_url ) ); ?></small>
    </p>
    <?php

}
add_action( 'aawp_settings_output_button_render', 'aawp_settings_add_to_cart_links', 10 );

/*
 * Update Link Types
 */
function aawp_update_link_types( $type, $origin, $atts ) {

    // Overwriting by shortcode
    $available_link_types = array( 'basic', 'wishlist', 'recommend', 'reviews', 'offers', 'cart' );

    if ( isset( $atts['link_type'] ) && in_array( $atts['link_type'], $available_link_types ) )
        return $atts['link_type'];

    // Overwriting by settings
    $output_options = aawp_get_options('output');

    if ( 'button' === $origin && isset ( $output_options['button_cart_links'] ) && $output_options['button_cart_links'] == '1' ) {
        $type = 'cart';
    }

    return $type;
}
add_filter( 'aawp_func_product_url_type', 'aawp_update_link_types', 10, 3 );

/**
 * Maybe return super url
 *
 * Source: https://marketplace-analytics.de/super-urls-werten-sie-externen-traffic-auf-und-verbessern-sie-gezielt-ihr-ranking
 *
 * @param $url
 * @param $type
 * @param $atts
 * @return string
 */
function aawp_maybe_return_super_url( $url, $type, $atts ) {

    if ( ! empty( $atts['keywords'] ) ) {
        return $url . '&keywords=' . rawurlencode( $atts['keywords'] );
        //return add_query_arg( 'keywords', $atts['keywords'], $url ); // this destroys the url and replaces . with _
    }

    return $url;
}
add_filter( 'aawp_template_product_url', 'aawp_maybe_return_super_url', 10, 3 );

/**
 * Temporary fix for Amazon's changed reviews page url
 *
 * Problem: They still provide the wrong urls via their API
 *
 * https://bitbucket.org/flowdee/aawp/issues/1190/amazon-changed-reviews-direct-link
 *
 * @param $url
 * @param $type
 * @param $atts
 * @return string
 */
function aawp_temp_fix_for_reviews_url( $url, $type, $atts ) {

    if ( 'reviews' === $type && strpos( $url, '/review/product/' ) !== false) {
        $url = str_replace('/review/product/', '/product-reviews/', $url );
    }

    return $url;
}
add_filter( 'aawp_template_product_url', 'aawp_temp_fix_for_reviews_url', 10, 3 );