<?php
/**
 * Customizations
 *
 * @package     AAWP\Functions\Components
 * @since       3.2.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Register customizations component
 */
function aawp_settings_register_customizations_component( $functions ) {

    $functions[] = 'customizations';

    return $functions;
}
add_filter( 'aawp_settings_functions', 'aawp_settings_register_customizations_component' );

/*
 * Global: Add custom body class for styling issues
 */
function aawp_body_classes( $classes ) {
    $classes[] = 'aawp-custom';

    return $classes;
}
add_filter( 'body_class','aawp_body_classes' );

/*
 * Extend supported shortcode attributes
 */
function aawp_add_customizations_shortcode_attributes( $supported, $type ) {

    array_push( $supported, 'style', 'star_rating_style' );

    return $supported;
}
add_filter( 'aawp_func_supported_attributes', 'aawp_add_customizations_shortcode_attributes', 10, 2 );

/*
 * Template Styles
 */
function aawp_settings_template_styles( $styles, $type ) {

    $styles['light'] = __('Light', 'aawp');
    $styles['dark'] = __('Dark', 'aawp');
    $styles['wayl'] = __('Special: WAYL', 'aawp');

    return $styles;
}
add_filter( 'aawp_func_styles', 'aawp_settings_template_styles', 10, 2 );

/*
 * Widget Template Styles
 */
function aawp_widget_template_styles( $styles, $type ) {

    $styles['light'] = __('Light', 'aawp');
    $styles['dark'] = __('Dark', 'aawp');
    $styles['wayl'] = __('Special: WAYL', 'aawp');

    return $styles;
}
add_filter( 'aawp_widget_styles', 'aawp_widget_template_styles', 10, 2 );

/*
 * Star Rating: Styles
 */
function aawp_star_rating_styles( $classes, $atts ) {

    $output_options = aawp_get_options('output');

    if ( isset( $atts['star_rating_style'] ) ) {
        $style = esc_html( $atts['star_rating_style'] );
    } elseif ( isset ( $output_options['star_rating_style'] ) ) {
        $style = esc_html( $output_options['star_rating_style'] );
    } else {
        $style = null;
    }

    if ( ! empty( $style ) ) {
        $classes .= ' aawp-star-rating--' . $style;
    }

    return $classes;
}
add_filter( 'aawp_star_rating_classes', 'aawp_star_rating_styles', 10, 2 );

/*
 * Star Rating: Settings - Styles
 */
function aawp_settings_star_rating_styles() {

    $output_options = aawp_get_options('output');

    $style = ( isset ( $output_options['star_rating_style'] ) ) ? $output_options['star_rating_style'] : 'v1';

    ?>
    <!-- Star Rating: Styles -->
    <?php
    $styles = array(
        'v1' => __('Standard', 'aawp'),
        'v2' => __('Style 2', 'aawp'),
        'v3' => __('Style 3', 'aawp'),
        'v4' => __('Style 4', 'aawp'),
        'v5' => __('Style 5', 'aawp'),
        'v6' => __('Style 6', 'aawp'),
        'v7' => __('Style 7', 'aawp'),
        'wayl' => __('Special: WAYL', 'aawp'),
        'custom' => __( 'Custom style', 'aawp' )
    );
    ?>
    &nbsp;
    <label for="aawp_star_rating_style"><?php _e('Style:', 'aawp'); ?></label>
    <select id="aawp_star_rating_style" name="aawp_output[star_rating_style]" data-aawp-star-rating-update-preview="true">
        <?php foreach ( $styles as $key => $label ) { ?>
            <option value="<?php echo $key; ?>" <?php selected( $style, $key ); ?>><?php echo $label; ?></option>
        <?php } ?>
    </select>

    <?php
}
add_action( 'aawp_settings_output_star_rating_render', 'aawp_settings_star_rating_styles' );

/*
 * Star Rating: Settings - Notes
 */
function aawp_settings_star_rating_notes() {

    $output_options = aawp_get_options('output');

    $style = ( isset ( $output_options['star_rating_style'] ) ) ? $output_options['star_rating_style'] : 'v1';
    $style_docs_url = ( aawp_is_lang_de() ) ? 'https://aawp.de/docs/article/sterne-bewertungen-eigene-grafiken/' : 'https://getaawp.com/docs/article/star-ratings-replace-icons/';

    ?>
    <p class="wayl"<?php if ( 'wayl' === $style ) echo ' style="display: block;"'; ?>>
        <?php printf( wp_kses( __( 'The <strong>WAYL</strong> style is based on the <a href="%s" target="_blank">Wurst App Your Life</a>.', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => '_blank', 'rel' => 'nofollow' ) ) ), esc_url( 'https://wayl-app.de/' ) ); ?>
    </p>

    <p>
        <small><?php printf( wp_kses( __( 'In case you want to replace the star rating icons, please take a look into the <a href="%s" target="_blank">documentation</a>.', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => '_blank' ) ) ), esc_url( $style_docs_url ) ); ?></small>
    </p>
    <?php
}
add_action( 'aawp_settings_output_star_rating_notes_render', 'aawp_settings_star_rating_notes' );

/*
 * Star Rating: Settings - Preview Classes
 */
function aawp_settings_star_rating_preview_style_classes( $classes ) {

    $output_options = aawp_get_options('output');

    $style = ( isset ( $output_options['star_rating_style'] ) ) ? $output_options['star_rating_style'] : 'v1';

    $classes .= ' aawp-star-rating--' . $style;

    return $classes;
}
add_filter( 'aawp_settings_star_rating_preview_classes', 'aawp_settings_star_rating_preview_style_classes' );