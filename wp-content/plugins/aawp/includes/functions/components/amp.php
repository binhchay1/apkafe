<?php
/**
 * Google AMP
 *
 * @package     AAWP\Functions\Components
 * @since       3.14.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Check if it's an AMP endpoint or not
 */
function aawp_is_amp_endpoint() {

    if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) // AMP for WordPress, MP for WP – Accelerated Mobile Pages
        return true;

    if ( function_exists( 'is_wp_amp' ) && is_wp_amp() ) // WP AMP
        return true;

    if ( function_exists( 'is_better_amp' ) && is_better_amp() ) // Better AMP
        return true;

    if ( function_exists( 'is_penci_amp_endpoint' ) && is_penci_amp_endpoint() ) // tagDiv AMP
        return true;

    return false;
}

/**
 * Print AMP styles
 *
 * @since       3.2.0
 */
function aawp_style_target_url( $target ) {
    return AAWP_PLUGIN_URL;
}
add_filter( 'aawp_amp_style_target_url', 'aawp_style_target_url' );

/**
 * Check and embed AMP styles
 *
 * Supported plugins:
 * https://wordpress.org/plugins/amp/
 * https://wordpress.org/plugins/accelerated-mobile-pages/
 * https://codecanyon.net/item/wp-amp-accelerated-mobile-pages-for-wordpress-and-woocommerce/16278608/
 * https://themeforest.net/item/soledad-multiconcept-blogmagazine-wp-theme/12945398 / https://forum.tagdiv.com/tagdiv-amp-plugin-tutorial/
 *
 * @since       3.0.0
 * @return      void
 */
function aawp_print_amp_styles() {

    $style_target_url = apply_filters( 'aawp_amp_style_target_url', null );
    echo aawp_get_amp_styles( $style_target_url );

    // Custom Setting CSS
    $custom_setting_css = '';
    $custom_setting_css = apply_filters( 'aawp_custom_setting_amp_css', $custom_setting_css );
    $custom_setting_css = aawp_cleanup_css_for_amp( $custom_setting_css );

    if ( ! empty( $custom_setting_css ) )
        echo $custom_setting_css;
}
//add_action( 'amp_post_template_css', 'aawp_print_amp_styles' ); // AMP, Accelerated Mobile Pages
add_action( 'amphtml_template_css', 'aawp_print_amp_styles' ); // WP AMP
add_action( 'penci_amp_post_template_css', 'aawp_print_amp_styles' ); // tagDiv AMP

function aawp_print_amp_styles_for_better_amp() {

    ob_start();
    aawp_print_amp_styles();
    $css = ob_get_clean();

    better_amp_add_inline_style( $css, 'aawp_better_amp_styles' );
}
add_action( 'better-amp/template/enqueue-scripts', 'aawp_print_amp_styles_for_better_amp', 101 ); // Better AMP

/**
 * Get AMP styles
 *
 * out of our amp.css file
 *
 * @param $target
 * @return mixed|null|string
 */
function aawp_get_amp_styles( $target ) {

    if ( empty ( $target ) )
        return null;

    $options_output = get_option( 'aawp_output' );

    // Core styles
    $amp_styles = '';

    if ( ! aawp_is_debug() )
        $amp_styles = get_transient( 'aawp_cached_amp_styles' );

    if ( empty( $amp_styles ) ) {
        $amp_styles = aawp_asset_embed( $target . 'assets/dist/css/amp.css', $target );

        if ( empty( $amp_styles ) )
            $amp_styles = aawp_get_amp_fallback_styles();

        if ( ! empty( $amp_styles ) )
            set_transient( 'aawp_cached_amp_styles', $amp_styles, 60 * 60 * 24 * 7 );
    }

    // Custom styles
    $custom_css_activated = ( isset ( $options_output['custom_css_activated'] ) && $options_output['custom_css_activated'] == '1' ) ? 1 : 0;
    $custom_css = ( !empty ( $options_output['custom_css'] ) ) ? $options_output['custom_css'] : '';

    if ( $custom_css_activated == '1' && $custom_css != '' ) {
        $amp_styles .= stripslashes($custom_css);
    }

    // Remove importants
    $amp_styles = str_replace('!important', '', $amp_styles);

    return $amp_styles;
}

/**
 * Get AMP fallback styles
 *
 * in case we were not able to read our amp.css file
 *
 * @return string
 */
function aawp_get_amp_fallback_styles() {
    return '.aawp-clearfix:after{content:".";display:block;clear:both;visibility:hidden;line-height:0;height:0}.aawp-clearfix{display:inline-block}html[xmlns] .aawp-clearfix{display:block}* html .aawp-clearfix{height:1%}.aawp .align-center{text-align:center}@media (min-width:800px){}@media (min-width:768px){}@media (min-width:769px){}.aawp a.aawp-check-prime,a.aawp-check-prime,.aawp a.aawp-check-premium,a.aawp-check-premium{border:none;box-shadow:none;outline:none;text-decoration:none}.aawp a.aawp-check-prime:visited,a.aawp-check-prime:visited,.aawp a.aawp-check-premium:visited,a.aawp-check-premium:visited,.aawp a.aawp-check-prime:hover,a.aawp-check-prime:hover,.aawp a.aawp-check-premium:hover,a.aawp-check-premium:hover,.aawp a.aawp-check-prime:focus,a.aawp-check-prime:focus,.aawp a.aawp-check-premium:focus,a.aawp-check-premium:focus,.aawp a.aawp-check-prime:active,a.aawp-check-prime:active,.aawp a.aawp-check-premium:active,a.aawp-check-premium:active{border:none;box-shadow:none;outline:none;text-decoration:none}.aawp .aawp-check-prime,.aawp-check-prime{display:inline-block;width:55px;height:16px;background-image:url(\'../img/icon-check-prime.png\');vertical-align:middle}@media (-webkit-min-device-pixel-ratio:2),(min-resolution:192dpi){.aawp .aawp-check-prime,.aawp-check-prime{background-image:url(\'../img/icon-check-prime@2x.png\');background-size:55px 16px}}.aawp .aawp-check-prime.aawp-check-prime--jp,.aawp-check-prime.aawp-check-prime--jp{background-image:url(\'../img/icon-check-prime-jp.png\')}@media (-webkit-min-device-pixel-ratio:2),(min-resolution:192dpi){.aawp .aawp-check-prime.aawp-check-prime--jp,.aawp-check-prime.aawp-check-prime--jp{background-image:url(\'../img/icon-check-prime-jp.png\')}}.aawp .aawp-check-premium,.aawp-check-premium{display:inline-block;width:75px;height:16px;background-image:url(\'../img/icon-check-premium.png\');vertical-align:middle}.aawp-product{position:relative;margin:0 0 30px;width:100%}.aawp-product .aawp-product__title{word-wrap:break-word}.aawp-product__ribbon{padding:0 20px;font-weight:400;font-size:12px;line-height:20px;text-transform:uppercase}.aawp-product__ribbon--sale{background:#27ae60;border-bottom-left-radius:2px;color:#fff}.aawp-product__ribbon--bestseller{background:#e47911;border-bottom-right-radius:2px;color:#fff}.aawp-product__ribbon--new{background:#d9534f;border-bottom-right-radius:2px;color:#fff}.aawp-button{box-sizing:border-box;display:inline-block;margin:0;padding:7px 12px 6px 12px;border:1px solid transparent !important;cursor:pointer;font-size:14px;font-weight:400;line-height:19px;text-align:center;text-decoration:none !important;background-color:#fff;border-color:#ccc !important;color:#333 !important}.aawp-button:hover{text-decoration:none !important}.aawp-button:active,.aawp-button:focus{box-shadow:none;text-decoration:none !important;outline:none}.aawp-button:visited{color:#333 !important}.aawp-button:hover,.aawp-button:focus{border:1px solid #ccc !important;color:#333 !important;background-color:#fafafa}.aawp-button--buy{border-color:#9c7e31 #90742d #786025 !important;border-radius:3px;box-shadow:0 1px 0 rgba(255,255,255,0.4) inset;background:#f0c14b;background:linear-gradient(to bottom, #f7dfa5, #f0c14b) repeat scroll 0 0 rgba(0,0,0,0);color:#111 !important}.aawp-button--buy:hover,.aawp-button--buy:active,.aawp-button--buy:focus{background:#eeb933;background:linear-gradient(to bottom, #f5d78e, #eeb933) repeat scroll 0 0 rgba(0,0,0,0);color:#111 !important}.aawp-button--buy.aawp-button--icon{position:relative;padding-left:32px}.aawp-button--buy.aawp-button--icon:before{position:absolute;content:\'\';top:0;right:0;bottom:0;left:0;background-repeat:no-repeat;background-size:14px 14px;background-position:9px center}.aawp-button--buy.aawp-button--icon-amazon-black:before{background-image:url(\'../img/icon-amazon-black.svg\')}.aawp-button--buy.aawp-button--icon-black:before{background-image:url(\'../img/icon-cart-black.svg\')}.aawp-star-rating{position:relative;display:inline-block;background-image:url(\'../img/stars/v1.svg\');background-repeat:repeat-x;background-position:left center;vertical-align:middle;height:16px;width:80px;background-size:16px 16px}.aawp-star-rating>span{position:absolute;top:0;bottom:0;left:0;display:block;background-image:url(\'../img/stars/v1-active.svg\');background-repeat:repeat-x;background-position:left center;vertical-align:middle}.aawp-star-rating>span{height:16px;width:80px;background-size:16px 16px}.aawp-product--amp{position:relative;padding:20px;background:#fff;border:1px solid #ececec;color:inherit;min-width:100%;padding:15px}.aawp-product__ribbon{padding:0 10px}.aawp-product__ribbon--sale{position:absolute;top:-1px;right:-1px}.aawp-product__ribbon--bestseller,.aawp-product__ribbon--new{position:absolute;top:-1px;left:-1px}.aawp-product__thumb{float:left;width:30%}@media (max-width:480px){.aawp-product__thumb{float:none;width:100%}}.aawp-product--ribbon .aawp-product__thumb{margin-top:10px}.aawp-product__content{float:left;padding-left:10px;width:70%}@media (max-width:480px){.aawp-product__content{float:none;padding:0;width:100%}}.aawp-product__image{display:block;width:100%;border:none;box-shadow:none;outline:none;text-decoration:none;background-repeat:no-repeat;background-position:center center;background-size:auto 85%}.aawp-product__image:hover,.aawp-product__image:focus,.aawp-product__image:active{border:none;box-shadow:none;outline:none;text-decoration:none}@media (max-width:480px){.aawp-product__image{margin:0 auto}}.aawp-product__rating{text-align:center}.aawp-product__reviews{display:inline-block;color:#666;font-size:14px;line-height:14px;vertical-align:-10%}.aawp-product__title{display:block;margin-top:10px;font-size:16px;font-weight:bold;line-height:20px}@media (max-width:480px){.aawp-product__title{text-align:center}}.aawp-product__description{margin-top:10px}@media (max-width:600px){.aawp-product__description{display:none}}.aawp-product__pricing{margin:10px 0;text-align:right}@media (max-width:480px){.aawp-product__pricing{text-align:center}}.aawp-product__pricing .aawp-check-prime{margin-bottom:10px}.aawp-product__price{line-height:18px}.aawp-product__price+.aawp-product__price,.aawp-product__price+.aawp-check-prime{margin-left:10px}.aawp-product__price--old{color:#666;font-size:14px;text-decoration:line-through}.aawp-product__price--current{font-size:18px;font-weight:bold}.aawp-product__buttons{text-align:right}@media (max-width:480px){.aawp-product__buttons{text-align:center}}.aawp-product__buttons .aawp-button+.aawp-button{margin-left:10px}.aawp-product__footer{clear:both;margin-top:10px;text-align:right}.aawp-product__info{clear:both;display:block;margin-top:10px;color:#666;font-size:11px;text-align:center}.aawp{box-sizing:border-box}.aawp *,.aawp *:before,.aawp *:after{box-sizing:border-box}.aawp-disclaimer,.aawp-credits{font-size:12px}';
}

/**
 * Overwrite AMP styles via settings
 *
 * @param $styles
 * @return string
 */
function aawp_overwrite_amp_styles_by_settings( $styles ) {

    $output_settings = aawp_get_options( 'output' );

    // Description: Show on mobile devices
    if ( isset( $output_settings['description_show_mobile'] ) && $output_settings['description_show_mobile'] == '1' ) {
        $styles .= '.aawp-product__description { display: block; }';
    }

    return $styles;
}
add_filter( 'aawp_custom_setting_amp_css', 'aawp_overwrite_amp_styles_by_settings' );

/**
 * Cleanup css for AMP usage
 *
 * @param string $css
 *
 * @return mixed|string
 */
function aawp_cleanup_css_for_amp( $css = '' ) {

    $css = stripslashes( $css );

    // Remove importants
    $css = str_replace('!important', '', $css);

    return $css;
}

/*
 * Update affiliate links when displaying AMP site
 */
function aawp_amp_affiliate_links( $affiliate_links ) {

    if ( aawp_is_amp_endpoint() )
        return 'shorted';

    return $affiliate_links;
}
add_filter( 'aawp_func_product_url_affiliate_links', 'aawp_amp_affiliate_links' );