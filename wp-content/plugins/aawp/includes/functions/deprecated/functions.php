<?php
/**
 * Deprecated functions
 *
 * @package     AAWP\Functions\Deprecated
 * @since       3.2.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Register cronjobs component
 */
function aawp_settings_register_deprecated_functions_component( $functions ) {

    $functions[] = 'deprecated';

    return $functions;
}
add_filter( 'aawp_settings_functions', 'aawp_settings_register_deprecated_functions_component' );

/*
 * Deprecated since 3.2.0
 */
function aawp_fields_get_value($id, $value, $args = array()) {
    return aawp_get_field_value( $id, $value, $args );
}