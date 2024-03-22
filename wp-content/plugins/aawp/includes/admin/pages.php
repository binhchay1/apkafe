<?php
/**
 * Admin Menu Pages
 *
 * @package     AAWP\CacheHandler
 * @since       2.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

global $aawp_menu_slug;

/**
 * Setup menus
 *
 * Source: http://stackoverflow.com/a/23002306
 */
add_action( 'admin_menu', function() {

    global $aawp_menu_slug;

    $aawp_menu_slug = 'aawp-settings';

    $show_notification = apply_filters( 'aawp_admin_menu_show_notification', false );

    if ( ! aawp_is_license_valid() )
        $show_notification = true;

    $notification_html = ( true === $show_notification ) ? ' <span class="update-plugins count-1"><span class="update-count">!</span></span>' : '';

    $menu_cap = apply_filters( 'aawp_admin_menu_cap', 'edit_pages' );

    add_menu_page(
        __( 'AAWP', 'aawp' ),
        __( 'AAWP', 'aawp' ) . $notification_html,
        $menu_cap,
        $aawp_menu_slug,
        'aawp_admin_render_settings_page',
        AAWP_PLUGIN_URL . '/assets/img/icon-menu.png',
        30
    );

    $menu_settings_title = ( aawp_is_license_valid() ) ? __( 'Settings', 'aawp' ) : '<span style="color: red;">' . __( 'Settings', 'aawp' ) . '</span>';

    add_submenu_page(
        $aawp_menu_slug,
        __( 'AAWP - Settings', 'aawp' ),
        $menu_settings_title,
        $menu_cap,
        'aawp-settings',
        'aawp_admin_render_settings_page'
    );

    /*
    add_submenu_page(
        $aawp_menu_slug,
        __( 'AAWP - Products', 'aawp' ),
        __( 'Products', 'aawp' ),
        $menu_cap,
        'edit.php?post_type=aawp_product'
    );

    $show_lists = apply_filters( 'aawp_admin_menu_show_lists', false );

    if ( $show_lists ) {

        add_submenu_page(
            $aawp_menu_slug,
            __( 'AAWP - Lists', 'aawp' ),
            __( 'Lists', 'aawp' ),
            $menu_cap,
            'edit.php?post_type=aawp_list'
        );
    }
    */

    /**
     * Dynamically add more menu items
     */
    do_action( 'aawp_admin_menu', $aawp_menu_slug );

}, 11);

/**
 * Correct active submenu items for custom post types
 *
 * Source: http://stackoverflow.com/a/23002306
 */
add_filter('parent_file', function( $parent_file ) {

    global $submenu_file, $current_screen;

    if ( $current_screen->post_type == 'aawp_product' ) {
        $submenu_file = 'edit.php?post_type=aawp_product';
        $parent_file = 'aawp-settings';
    }

    if ( $current_screen->post_type == 'aawp_list' ) {
        $submenu_file = 'edit.php?post_type=aawp_list';
        $parent_file = 'aawp-settings';
    }

    return $parent_file;
});

/**
 * Render settings page function
 */
function aawp_admin_render_settings_page() {
    do_action( 'aawp_admin_render_settings_page' );
}