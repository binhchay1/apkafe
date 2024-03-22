<?php
/**
 * Templates
 *
 * @package     AAWP\Functions\Components
 * @since       3.2.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Register templating component
 */
function aawp_settings_register_templating_component( $functions ) {

    $functions[] = 'templating';

    return $functions;
}
add_filter( 'aawp_settings_functions', 'aawp_settings_register_templating_component' );

/*
 * Add custom template options
 */
function aawp_settings_add_custom_template_options( $templates, $func_id ) {

    if ( 'box' == $func_id || 'bestseller' == $func_id || 'new_releases' == $func_id ) {
        $templates['list'] = __('List', 'aawp');
        $templates['table'] = __('Table', 'aawp');
        $templates['custom'] = __('Custom template', 'aawp');
    }

    return $templates;
}
add_filter( 'aawp_func_templates', 'aawp_settings_add_custom_template_options', 10, 2 );

/*
 * Add custom template render
 */
function aawp_settings_add_custom_template_render( $func_id, $template, $template_custom, $prefix ) {

    ?>
    <div data-aawp-custom-template-wrapper="<?php echo $func_id; ?>"<?php if ( !$template_custom || $template != 'custom' ) echo ' style="display: none;"'; ?>>
        <p>
            <label for="<?php echo $prefix; ?>_template_custom"><?php _e('Template name', 'aawp'); ?></label>:
            <input type="text" id="<?php echo $prefix; ?>_template_custom" name="aawp_functions[<?php echo $prefix; ?>_template_custom]" value="<?php echo $template_custom; ?>" /> <small><?php _e('Do <u>not</u> enter the file extension! Valid example: <strong>my_template</strong>', 'aawp'); ?></small>
        </p>
        <p>
            <small><?php _e('Please take a look into the documentation how to use custom templating.', 'aawp'); ?></small>
        </p>
    </div>

    <?php
}
add_action( 'aawp_func_settings_render_template', 'aawp_settings_add_custom_template_render', 10, 5 );

/*
 * Looking for custom templates when using locate_template
 */
function aawp_template_stack_add_theme_directories( $template_stack, $template_names ) {

    // check child theme first
    $template_stack[] = trailingslashit( get_stylesheet_directory() ) . 'aawp/';
    $template_stack[] = trailingslashit( get_stylesheet_directory() ) . 'aawp/products/';
    $template_stack[] = trailingslashit( get_stylesheet_directory() ) . 'aawp/parts/';

    // check parent theme next
    $template_stack[] = trailingslashit( get_template_directory() ) . 'aawp/';
    $template_stack[] = trailingslashit( get_template_directory() ) . 'aawp/products/';
    $template_stack[] = trailingslashit( get_template_directory() ) . 'aawp/parts/';

    return $template_stack;
}
add_filter( 'aawp_template_stack', 'aawp_template_stack_add_theme_directories', 10, 2 );

/**
 * Adding template relevant shortcode attributes
 */
function aawp_add_template_relevant_shortcode_attributes( $supported, $type ) {

    // Templates
    if ( $type == 'box' || $type == 'bestseller' || $type == 'new_releases' ) {
        array_push( $supported, 'layout', 'grid' );
    }

    return $supported;
}
add_filter( 'aawp_func_supported_attributes', 'aawp_add_template_relevant_shortcode_attributes', 10, 2 );

/*
 * Add Template Wrapper Classes
 */
function aawp_add_template_wrapper_classes( $classes, $layout_template, $atts ) {

    // Grids
    if ( isset( $atts['grid'] ) && is_numeric( $atts['grid'] ) ) {
        $grid_size = ( intval( $atts['grid'] ) <= 6 ) ? $atts['grid'] : '6';
        $classes .= ' aawp-grid--col-' . $grid_size;
    }

    return $classes;
}
add_filter( 'aawp_template_wrapper_classes', 'aawp_add_template_wrapper_classes', 10, 3 );

/*
 * Preselect layout template
 */
function aawp_predefine_layout_template( $template, $layout_template_validation, $atts ) {

    //if ( $layout_template_validation )
      //  return $template;

    // Floating
    if( isset( $atts['float'] ) ) {
        return 'loop';
    }

    // Grids
    if ( isset( $atts['grid'] ) && is_numeric( $atts['grid'] ) ) {
        return 'grid';
    }

    return $template;
}
add_filter( 'aawp_layout_template', 'aawp_predefine_layout_template', 20, 3 );

/*
 * Preselect product template
 */
function aawp_predefine_product_template( $template, $atts ) {

    // Grids
    if( isset( $atts['grid'] ) && is_numeric( $atts['grid'] ) ) {

        if ( empty( $template ) || ! isset( $atts['template'] ) || ( isset( $atts['template'] ) && $template != $atts['template'] ) ) {
            $template = 'vertical';
        }
    }

    // Floating
    if( isset( $atts['float'] ) ) {

        $float_templates = array( 'vertical', 'widget-vertical', 'widget-small' );
        $float_default_template = 'vertical';

        if ( empty( $template ) || ( ! empty( $template ) && ! in_array( $template, $float_templates ) ) )
            $template = $float_default_template;

        if ( ! empty( $atts['template'] ) )
            $template = esc_html( $atts['template'] );
    }

    return $template;
}
add_filter( 'aawp_product_template', 'aawp_predefine_product_template', 10, 2 );

/*
 * Check if selected template is a layout
 */
function aawp_product_template_validation( $product_template_validation, $template, $atts ) {

    // check child theme first
    if ( file_exists( trailingslashit( get_stylesheet_directory() . '/aawp/products/' ) . $template . '.php' ) ) {
        $product_template_validation = true;
    }

    // check parent theme next
    if ( file_exists( trailingslashit( get_template_directory() . '/aawp/products/' ) . $template . '.php' ) ) {
        $product_template_validation = true;
    }

    return $product_template_validation;
}
add_filter( 'aawp_product_template_validation', 'aawp_product_template_validation', 10, 3 );

/*
 * Check if selected template is a layout
 */
function aawp_layout_template_validation( $layout_template_validation, $template, $atts ) {

    // check child theme first
    if ( file_exists( trailingslashit( get_stylesheet_directory() . '/aawp/' ) . $template . '.php' ) ) {
        $layout_template_validation = true;
    }

    // check parent theme next
    if ( file_exists( trailingslashit( get_template_directory() . '/aawp/' ) . $template . '.php' ) ) {
        $layout_template_validation = true;
    }

    return $layout_template_validation;
}
add_filter( 'aawp_layout_template_validation', 'aawp_layout_template_validation', 10, 3 );

/**
 * Floating templates
 */
function aawp_template_floating_wrapper_start() {

    if ( aawp_is_amp_endpoint() )
        return;

    global $aawp_shortcode_atts;

    if ( ! isset( $aawp_shortcode_atts['float'] ) )
        return;

    $classes = 'aawp-floating-wrapper';

    if ( 'left' === $aawp_shortcode_atts['float'] || 'right' === $aawp_shortcode_atts['float'] )
        $classes .= ' aawp-floating-wrapper--' . $aawp_shortcode_atts['float'];

    echo '<div class="' . $classes . '">';

}
add_action( 'aawp_before_template', 'aawp_template_floating_wrapper_start' );

function aawp_template_floating_wrapper_end() {

    if ( aawp_is_amp_endpoint() )
        return;

    global $aawp_shortcode_atts;

    if ( ! isset( $aawp_shortcode_atts['float'] ) )
        return;

    echo '</div>';

}
add_action( 'aawp_after_template', 'aawp_template_floating_wrapper_end' );