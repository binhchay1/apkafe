<?php
/**
 * Product edit page
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
    remove_meta_box( 'slugdiv' , 'aawp_product' , 'normal' );

});


/**
 * Register meta boxes
 */
add_action('add_meta_boxes', function() {


    add_meta_box(
        'aawp-product-metabox',  // $id
        __( 'Amazon Product', 'aawp' ),                     // $title
        'aawp_product_metabox_render',  // $callback
        'aawp_product',      // $page
        'normal',                  // $context
        'high'                     // $priority
    );

});

/**
 * Product Metabox
 */
function aawp_product_metabox_render() {

    global $post;

    // Use nonce for verification to secure data sending
    //wp_nonce_field( basename( __FILE__ ), 'wpse_our_nonce' );

    $product_id = $post->ID;

    $options = aawp_get_options();

    $country_tags = aawp_get_amazon_stores();
    $country = ( isset ( $options['api']['country'] ) ) ? $options['api']['country'] : false;

    $show_country_selector = ( aawp_show_debug() ) ? true : false;

    if ( ! $country )
        return;
    ?>
    <input id="aawp-post-id" type="hidden" value="<?php echo $post->ID; ?>">

    <div class="aawp aawp-pp-metabox">

        <h4><?php _e( 'Showing data for the following store', 'aawp' ); ?></h4>
        <div id="aawp-pp-countries" class="aawp-pp-countries">
            <ul class="aawp-pp-countries__list aawp-pp-countries__list--current">
                <?php foreach ( $country_tags as $tag => $label ) { ?>
                    <?php if ( ! $show_country_selector && $tag != $country ) continue; ?>
                    <?php $tag_attr = str_replace('.', '-', $tag); ?>
                    <li class="aawp-pp-countries__list-item aawp-pp-countries__list-item--<?php echo $tag_attr; ?><?php if ( $country === $tag ) echo ' active'; ?>" data-aawp-pp-switch-country="true">
                        <?php aawp_the_icon_flag( $tag ); ?> <span class="aawp-pp-countries__item-label">Amazon <?php echo $label; ?></span>
                        <?php if ( $show_country_selector ) { ?>
                            <span class="dashicons dashicons-menu aawp-pp-countries__action"></span>
                        <?php } ?>
                    </li>
                <?php } ?>
            </ul>
            <?php if ( $show_country_selector ) { ?>
                <ul class="aawp-pp-countries__list aawp-pp-countries__list--select">
                    <?php foreach ( $country_tags as $tag => $label ) { ?>
                        <?php $tag_attr = str_replace('.', '-', $tag); ?>
                        <li class="aawp-pp-countries__list-item aawp-pp-countries__list-item--<?php echo $tag_attr; ?><?php if ( $country === $tag ) echo ' active'; ?>" data-aawp-pp-select-country="<?php echo $tag_attr; ?>">
                            <?php aawp_the_icon_flag( $tag ); ?> <span class="aawp-pp-countries__item-label">Amazon <?php echo $label; ?></span>
                        </li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>

        <?php foreach ( $country_tags as $tag => $label ) { ?>
            <?php if ( ! $show_country_selector && $tag != $country ) continue; ?>
            <?php $tag_attr = str_replace('.', '-', $tag); ?>
            <div class="aawp-pp-content aawp-pp-content--<?php echo $tag_attr; ?><?php if ( $country === $tag ) echo ' active'; ?>">

                <div class="aawp-pp-content__data">
                    <h4><?php _e( 'Status', 'aawp' ); ?></h4>
                    <?php $product_status = aawp_get_product_status( $product_id, $tag ); ?>
                    <p><?php aawp_admin_display_post_type_entry_status( $product_status ); ?></p>
                </div>

                <div class="aawp-pp-content__data">
                    <h4><?php _e( 'ASIN', 'aawp' ); ?></h4>
                    <?php $product_asin = aawp_get_product_asin( $product_id ); ?>
                    <?php aawp_admin_pp_input_field_html( $product_asin ); ?>
                </div>

                <div class="aawp-pp-content__data">
                    <h4><?php _e( 'EAN', 'aawp' ); ?></h4>
                    <?php $product_ean = aawp_get_product_ean( $product_id, $tag ); ?>
                    <?php aawp_admin_pp_input_field_html( $product_ean ); ?>
                </div>

                <div class="aawp-pp-content__data">
                    <h4><?php _e( 'ISBN', 'aawp' ); ?></h4>
                    <?php $product_isbn = aawp_get_product_isbn( $product_id, $tag ); ?>
                    <?php aawp_admin_pp_input_field_html( $product_isbn ); ?>
                </div>

                <div class="aawp-pp-content__data">
                    <h4><?php _e( 'URLs', 'aawp' ); ?></h4>
                    <?php $product_url_types = aawp_get_amazon_product_url_types(); ?>
                    <?php foreach ( $product_url_types as $url_type => $url_type_label ) { ?>
                        <h5><?php echo $url_type_label; ?></h5>
                        <p>
                            <?php $product_url = aawp_get_product_url( $product_id, $url_type, $tag ); ?>
                            <?php aawp_admin_pp_input_field_html( $product_url ); ?>
                        </p>
                    <?php } ?>
                </div>

                <div class="aawp-pp-content__data">
                    <h4><?php _e( 'Title', 'aawp' ); ?></h4>
                    <?php $product_title = aawp_get_product_title( $product_id, $tag ); ?>
                    <?php aawp_admin_pp_input_field_html( $product_title ); ?>
                </div>

                <div class="aawp-pp-content__data">
                    <h4><?php _e( 'Images', 'aawp' ); ?></h4>
                    <?php $product_default_image = aawp_get_product_default_image( $product_id, $tag ); ?>
                    <div class="aawp-pp-images">
                        <?php $product_images = aawp_get_product_images( $product_id, $tag ); ?>
                        <!-- Images available -->
                        <?php if ( ! empty( $product_images ) && is_array( $product_images ) && sizeof( $product_images ) > 0 ) { ?>
                            <?php foreach ( $product_images as $i => $product_image ) { ?>
                                <?php aawp_admin_pp_product_image_action_html( $product_image, $i, $product_default_image, $tag ); ?>
                            <?php } ?>
                        <!-- No image available -->
                        <?php } else { ?>
                            <?php _e( 'No thumbnail available.', 'aawp' ); ?>
                        <?php } ?>
                    </div>
                </div>

                <div class="aawp-pp-content__data">
                    <h4><?php _e( 'Description', 'aawp' ); ?></h4>
                    <?php $product_description = aawp_get_product_description( $product_id, $tag ); ?>
                    <div class="aawp-admin-html-preview"><?php echo ( ! empty( $product_description ) ) ? $product_description : '-'; ?></div>
                </div>

                <?php /*
                <div class="aawp-pp-content__data">
                    <h4><?php _e( 'Editorial Review', 'aawp' ); ?></h4>
                    <?php $product_editorial_review = aawp_get_product_editorial_review( $product_id, $tag ); ?>
                    <div class="aawp-admin-html-preview"><?php echo ( ! empty( $product_editorial_review ) ) ? $product_editorial_review : '-'; ?></div>
                </div>
                */ ?>

                <?php $product_rating_timestamp = aawp_get_product_rating_timestamp( $product_id, $tag ); ?>

                <div class="aawp-pp-content__data">
                    <h4><?php _e( 'Reviews', 'aawp' ); ?></h4>
                    <?php $product_reviews = aawp_get_product_reviews( $product_id, $tag ); ?>
                    <?php aawp_admin_pp_input_field_html( $product_reviews ); ?>

                    <?php if ( ! empty( $product_rating_timestamp ) ) { ?>
                        <p><small><?php printf( esc_html__( 'Last update: %s', 'aawp' ), aawp_datetime( $product_rating_timestamp ) ); ?></small></p>
                    <?php } ?>
                </div>

                <div class="aawp-pp-content__data">
                    <h4><?php _e( 'Rating', 'aawp' ); ?></h4>
                    <?php $product_rating = aawp_get_product_rating( $product_id, $tag ); ?>
                    <?php aawp_admin_pp_input_field_html( $product_rating ); ?>

                    <?php if ( ! empty( $product_rating_timestamp ) ) { ?>
                        <p><small><?php printf( esc_html__( 'Last update: %s', 'aawp' ), aawp_datetime( $product_rating_timestamp ) ); ?></small></p>
                    <?php } ?>
                </div>

                <div class="aawp-pp-content__data">
                    <h4><?php _e( 'Salesrank', 'aawp' ); ?></h4>
                    <?php $product_salesrank = aawp_get_product_salesrank( $product_id, $tag ); ?>
                    <?php aawp_admin_pp_input_field_html( $product_salesrank ); ?>
                </div>

                <?php if ( aawp_show_debug() ) { ?>
                <div class="aawp-pp-content__data">
                    <h4><?php _e( 'Attributes', 'aawp' ); ?></h4>
                    <?php $product_attributes = aawp_get_product_attributes( $product_id, $tag ); ?>
                    <?php if ( ! empty( $product_attributes ) && is_array( $product_attributes ) && sizeof( $product_attributes ) > 1 ) { ?>
                        <p>
                            <a href="#" data-aawp-pp-content-toggle-hidden="aawp-pp-content-product-attributes-<?php echo $tag_attr; ?>"><?php _e( 'Show attributes', 'aawp' ); ?></a>
                        </p>
                        <table id="aawp-pp-content-product-attributes-<?php echo $tag_attr; ?>" class="widefat striped aawp-pp-content__hidden">
                            <tbody>
                                <?php foreach ( $product_attributes as $attribute_key => $attribute_values ) { ?>
                                    <tr>
                                        <th><strong><?php echo $attribute_key; ?></strong></th>
                                        <td>
                                            <?php if ( is_array( $attribute_values ) ) { ?>
                                                <ul>
                                                    <?php foreach ( $attribute_values as $attribute_value ) { ?>
                                                        <?php if ( is_string( $attribute_value ) ) { ?>
                                                            <li><?php echo $attribute_value; ?></li>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </ul>
                                            <?php } else { ?>
                                                <?php echo $attribute_values; ?>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </div>
                <?php } ?>

            </div>
        <?php } ?>

        <div class="aawp-pp-content__data">
            <h4><?php _e( 'Last update', 'aawp' ); ?></h4>
            <?php aawp_admin_the_renew_post_last_update( $product_id, $type = 'aawp_product' ); ?>
        </div>

        <?php aawp_admin_the_renew_post_button( $product_id, $type = 'aawp_product', $reload = true ); ?>

        <?php aawp_debug_pp_post_meta(); ?>

    </div>

    <?php
}

function aawp_admin_pp_product_image_action_html( $image, $i, $default_image, $store ) {

    $classes = 'aawp-pp-image';

    if ( is_numeric( $default_image ) && $i === intval( $default_image ) )
        $classes .= ' aawp-pp-image--selected';

    ?>
    <span class="<?php echo $classes; ?>" data-aawp-pp-select-image="<?php echo $i; ?>" data-aawp-pp-store="<?php echo $store; ?>">
        <span class="aawp-pp-image__thumb"><img src="<?php echo $image['small']; ?>" /></span>
        <span class="aawp-pp-image__selected"><span class="dashicons dashicons-yes"></span></span>
    </span>
    <?php
}

//now we are saving the data
function wpse_save_meta_fields( $post_id ) {

    // verify nonce
    if (!isset($_POST['wpse_our_nonce']) || !wp_verify_nonce($_POST['wpse_our_nonce'], basename(__FILE__)))
        return 'nonce not verified';

    // check autosave
    if ( wp_is_post_autosave( $post_id ) )
        return 'autosave';

    //check post revision
    if ( wp_is_post_revision( $post_id ) )
        return 'revision';

    // check permissions
    if ( 'project' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) )
            return 'cannot edit page';
    } elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
        return 'cannot edit post';
    }

    //so our basic checking is done, now we can grab what we've passed from our newly created form
    $wpse_value = $_POST['wpse_value'];

    //simply we have to save the data now
    global $wpdb;

    $table = $wpdb->base_prefix . 'project_bids_mitglied';

    $wpdb->insert(
        $table,
        array(
            'col_post_id' => $post_id, //as we are having it by default with this function
            'col_value'   => intval( $wpse_value ) //assuming we are passing numerical value
        ),
        array(
            '%d', //%s - string, %d - integer, %f - float
            '%d', //%s - string, %d - integer, %f - float
        )
    );

}
//add_action( 'save_post', 'wpse_save_meta_fields' );
//add_action( 'new_to_publish', 'wpse_save_meta_fields' );

/**
 * Removing admin elements on custom post type page(s)
 */
add_action( 'admin_head', function() {

    global $pagenow, $typenow;

    if ( empty( $typenow ) && ! empty( $_GET['post'] ) ) {
        $post = get_post($_GET['post']);
        $typenow = $post->post_type;
    }

    if ( is_admin() && ( $pagenow =='post-new.php' || $pagenow =='post.php' || $pagenow =='edit.php' ) && $typenow == 'aawp_product' ) {
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