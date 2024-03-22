<?php
/**
 * Admin Functions
 *
 * @package     AAWP\Includes\Admin
 * @since       3.4
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Display formatted entry status
 *
 * @param $status
 */
function aawp_admin_display_post_type_entry_status( $status ) {

    if ( empty( $status ) )
        return;

    if ( 'active' === $status ) {
        $label = __( 'Active', 'aawp' );
    } elseif ( 'inactive' === $status ) {
        $label = __( 'Inactive', 'aawp' );
    } elseif ( 'not-found' === $status ) {
        $label = __( 'Not found', 'aawp' );
    } elseif ( 'not-available' === $status ) {
        $label = __( 'Not available via API', 'aawp' );
    } else {
        $label = $status;
    }

    ?>
    <span class="aawp-pp-status aawp-pp-status--<?php echo $status; ?>"><?php echo $label; ?></span>
    <?php
}

/**
 * Display formatted list type
 *
 * @param $type
 * @param bool $echo
 *
 * @return string|void
 */
function aawp_admin_display_list_type( $type, $echo = true ) {

    if ( empty( $type ) )
        return;

    if ( 'bestseller' === $type ) {
        $label = __( 'Bestseller', 'aawp' );
    } elseif ( 'new_releases' === $type ) {
        $label = __( 'New Releases', 'aawp' );
    } else {
        $label = $type;
    }

    if ( $echo ) {
        echo $label;
    } else {
        return $label;
    }
}

/**
 * Render input field html
 *
 * @param $value
 * @param string $status
 * @param bool $focus
 */
function aawp_admin_pp_input_field_html( $value, $status = 'readonly', $focus = true ) {
    ?>
    <input class="widefat" type="text" <?php if( ! empty( $status ) ) echo $status; ?> <?php if ( $focus ) echo 'onclick="this.focus(); this.select()"'; ?> value="<?php echo ( ! empty( $value ) ) ? $value : '-'; ?>" />
    <?php
}

/**
 * Render no products info setting
 *
 * @param $prefix
 */
function aawp_admin_settings_functions_notices_render( $prefix ) {

    $options = aawp_get_options();

    $text = ( ! empty( $options['functions'][$prefix . '_no_products_found_text'] ) ) ? $options['functions'][$prefix . '_no_products_found_text'] : __( 'No products found.' , 'aawp' );
    $hide = ( isset ( $options['functions'][$prefix . '_no_products_found_hide_public'] ) && $options['functions'][$prefix . '_no_products_found_hide_public'] == '1' ) ? 1 : 0;

    ?>
    <!-- No products found -->
    <p>
        <?php _e( 'Displaying a message in case no products were found.', 'aawp' ); ?>
    </p>
    <p>
        <input type="text" id="aawp_<?php echo $prefix; ?>_no_products_found_text" name="aawp_functions[<?php echo $prefix; ?>_no_products_found_text]" value="<?php echo esc_html( $text ); ?>" />

        <input type="checkbox" id="aawp_<?php echo $prefix; ?>_no_products_found_hide_public" name="aawp_functions[<?php echo $prefix; ?>_no_products_found_hide_public]" value="1" <?php echo( $hide == 1 ? 'checked' : ''); ?>><label for="aawp_<?php echo $prefix; ?>_no_products_found_hide_public"><?php _e( 'Hide notice for public visitors', 'aawp' ); ?></label>
    </p>
    <p>
        <small><?php _e( 'Additionally the notice will be highlighted for <strong>admins only</strong>.', 'aawp' ); ?></small>
    </p>

    <?php
}

function aawp_admin_the_renew_post_button( $post_id, $post_type, $reload = false ) {

    if ( empty( $post_type ) )
        return;

    if ( 'aawp_product' === $post_type ) {
        $label = __( 'Renew product', 'aawp' );
    } elseif ( 'aawp_list' === $post_type ) {
        $label = __( 'Renew list', 'aawp' );
    }

    if ( empty( $label ) )
        return;

    ?>
    <span class="aawp-admin-renew-post-action">
        <input type="button" class="button aawp-admin-button-cta"
               value="<?php echo $label; ?>"
               data-aawp-admin-renew-post="<?php echo $post_id; ?>"
               data-aawp-admin-renew-post-success-reload="<?php echo ( $reload ) ? '1' : '0'; ?>" />
        <span class="aawp-admin-renew-post-action__spinner">
            <span class="aawp-spinner">
                <span class="aawp-spinner__bounce-1"></span><span class="aawp-spinner__bounce-2"></span>
            </span>
        </span>
    </span>

    <?php
}

function aawp_admin_the_renew_post_last_update( $post_id, $post_type ) {

    if ( empty( $post_type ) )
        return;

    if ( 'aawp_product' === $post_type ) {
        $last_update = aawp_get_product_last_update( $post_id );
        //$outdated = aawp_is_product_data_outdated( $post_id ); // TODO Deprecated
    } elseif ( 'aawp_list' === $post_type ) {
        $last_update = aawp_get_list_last_update( $post_id );
        //$outdated = aawp_is_list_data_outdated( $post_id ); // TODO Deprecated
    }

    if ( empty( $last_update ) )
        $last_update = '-';
    ?>
    <span id="aawp-admin-renew-post-last-update-<?php echo $post_id; ?>" class="aawp-admin-renew-post-last-update">
        <span><?php echo $last_update; ?></span>
        <?php if ( ! empty( $outdated ) ) { // TODO: Remove on live ?>
            <span class="aawp-admin-renew-post-last-update__outdated"><?php _e( 'Outdated' , 'aawp' ); ?></span>
        <?php } ?>
    </span>
    <?php
}

function aawp_admin_display_placeholders_note( $placeholders = array() ) {

    $string = '';

    foreach ( $placeholders as $placeholder ) {

        if ( ! empty( $string ) )
            $string .= ', ';

        $string .= '%' . strtoupper( $placeholder ) . '%';
    }

    ?>
    <small><?php printf( __( 'The following placeholder(s) can be used: <strong>%1$s</strong>', 'aawp' ), $string ); ?></small>
    <?php
}

function aawp_admin_is_plugin_page() {

    global $current_screen;

    return ( isset( $current_screen->parent_base ) && strpos( $current_screen->parent_base, 'aawp') !== false ) ? true : false;
}

/**
 * Get admin settings page url (maybe incl. a tab)
 *
 * @param string $tab
 *
 * @return string
 */
function aawp_admin_get_settings_page_url( $tab = '' ) {

    $url = 'admin.php?page=aawp-settings';

    if ( ! empty( $tab ) )
        $url = add_query_arg( 'tab', $tab, $url );

    $url = admin_url( $url );

    return $url;
}