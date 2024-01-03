<?php
/**
 * Plugins
 *
 * @since       3.9.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Plugins row action links
 *
 * @param array $links already defined action links
 * @param string $file plugin file path and name being processed
 * @return array $links
 */
function aawp_action_links( $links, $file ) {

	if ( $file != 'aawp/aawp.php' )
		return $links;

    $settings_link = '<a href="' . AAWP_ADMIN_SETTINGS_URL . '">' . esc_html__( 'Settings', 'aawp' ) . '</a>';

	array_unshift( $links, $settings_link );

    return $links;
}
add_filter( 'plugin_action_links', 'aawp_action_links', 10, 2 );

/**
 * Plugin row meta links
 *
 * @param array $input already defined meta links
 * @param string $file plugin file path and name being processed
 * @return array $input
 */
function aawp_row_meta( $input, $file ) {

    if ( $file != 'aawp/aawp.php' )
        return $input;

    $docs_link = esc_url( add_query_arg( array(
            'utm_source'   => 'plugins-page',
            'utm_medium'   => 'plugin-row',
            'utm_campaign' => 'AAWP',
        ), AAWP_DOCS_URL )
    );

    $links = array(
        '<a href="' . $docs_link . '">' . esc_html__( 'Documentation', 'aawp' ) . '</a>',
    );

    $input = array_merge( $input, $links );

    return $input;
}
add_filter( 'plugin_row_meta', 'aawp_row_meta', 10, 2 );