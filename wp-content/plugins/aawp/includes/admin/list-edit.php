<?php
/**
 * List edit page
 *
 * @package     AAWP\Admin
 * @since       3.4.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Remove default meta boxes
 */
add_action( 'admin_menu' , function() {

    // Slug
    remove_meta_box( 'slugdiv' , 'aawp_list' , 'normal' );

});


/**
 * Register meta boxes
 */
add_action('add_meta_boxes', function() {

    add_meta_box(
        'aawp-list-metabox',  // $id
        __( 'Amazon List', 'aawp' ),                     // $title
        'aawp_list_metabox_render',  // $callback
        'aawp_list',      // $page
        'normal',                  // $context
        'high'                     // $priority
    );

});

/**
 * Product Metabox
 */
function aawp_list_metabox_render() {

    global $post;

    // Use nonce for verification to secure data sending
    //wp_nonce_field( basename( __FILE__ ), 'wpse_our_nonce' );

    $list_id = $post->ID;

    $options = aawp_get_options();

    ?>
    <input id="aawp-post-id" type="hidden" value="<?php echo $post->ID; ?>">

    <div class="aawp aawp-pp-metabox">

        <div class="aawp-pp-content__data">
            <h4><?php _e( 'Status', 'aawp' ); ?></h4>
            <?php $list_status = aawp_get_list_status( $list_id ); ?>
            <p><?php aawp_admin_display_post_type_entry_status( $list_status ); ?></p>
        </div>

        <div class="aawp-pp-content__data">
            <h4><?php _e( 'Store', 'aawp' ); ?></h4>
            <?php $list_store = aawp_get_list_store( $list_id ); ?>
            <p><?php aawp_the_icon_flag( $list_store ); ?>&nbsp;Amazon.<?php echo $list_store; ?>
        </div>

        <div class="aawp-pp-content__data">
            <h4><?php _e( 'Type', 'aawp' ); ?></h4>
            <?php $list_type = aawp_get_list_type( $list_id ); ?>
            <?php $list_types = aawp_get_list_types(); ?>
            <?php if ( is_array( $list_types ) ) { ?>
                <select class="widefat" disabled="disabled">
                    <?php foreach ( $list_types as $list_type_key => $list_type_label ) { ?>
                        <option value="<?php echo $list_type_key; ?>" <?php selected( $list_type, $list_type_key ); ?>><?php echo $list_type_label; ?></option>
                    <?php } ?>
                </select>
            <?php } ?>
        </div>

        <div class="aawp-pp-content__data">
            <h4><?php _e( 'Browse node id or keyword(s)', 'aawp' ); ?></h4>
            <?php $list_keys = aawp_get_list_keys( $list_id ); ?>
            <input class="widefat" type="text" readonly="readonly" value="<?php echo ( ! empty( $list_keys ) ) ? $list_keys : '-'; ?>" />
        </div>

        <div class="aawp-pp-content__data">
            <h4><?php _e( 'Amount of items', 'aawp' ); ?></h4>
            <?php $list_max = aawp_get_list_max( $list_id ); ?>
            <input class="widefat" type="number" readonly="readonly" value="<?php echo ( ! empty( $list_max ) ) ? $list_max : '0'; ?>" />
        </div>

        <div class="aawp-pp-content__data">
            <h4><?php _e( 'Products', 'aawp' ); ?></h4>
            <?php $list_items = aawp_get_list_items( $list_id ); ?>
            <?php if ( ! empty( $list_items ) && is_array( $list_items ) ) { ?>
                <ol>
                    <?php foreach( $list_items as $list_item_asin ) { ?>
                        <?php $list_item_product_id = aawp_get_product_by_asin( $list_item_asin ); ?>
                        <li>
                            <?php echo $list_item_asin; ?>
                            <?php if ( ! empty( $list_item_product_id ) ) { ?>
                                <small>&rarr; <a href="<?php echo get_edit_post_link( $list_item_product_id ); ?>"><?php _e( 'Edit', 'aawp' ); ?></a></small>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ol>
            <?php } else { ?>
                <p>-</p>
            <?php } ?>
        </div>

        <div class="aawp-pp-content__data">
            <h4><?php _e( 'Last update', 'aawp' ); ?></h4>
            <?php aawp_admin_the_renew_post_last_update( $list_id, $type = 'aawp_list' ); ?>
        </div>

        <?php aawp_admin_the_renew_post_button( $list_id, $type = 'aawp_list', $reload = true ); ?>

        <?php aawp_debug_pp_post_meta(); ?>

    </div>

    <?php
}

/**
 * Removing admin elements on custom post type page(s)
 */
add_action( 'admin_head', function() {

    global $pagenow, $typenow;

    if ( empty( $typenow ) && ! empty( $_GET['post'] ) ) {
        $post = get_post($_GET['post']);
        $typenow = $post->post_type;
    }

    if ( is_admin() && ( $pagenow =='post-new.php' || $pagenow =='post.php' || $pagenow =='edit.php' ) && $typenow == 'aawp_list' ) {
        ?>
        <style type="text/css">
            a.page-title-action {
                display: none;
            }
            #post-body-content {
                display: none;
            }
        </style>
        <?php
    }

});

add_action( 'admin_footer', function() {
    ?>
    <script type="text/javascript">
        /*
        jQuery('input#title').prop('disabled', true);
        */
    </script>
    <?php
});