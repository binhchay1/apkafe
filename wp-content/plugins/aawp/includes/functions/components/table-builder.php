<?php
/**
 * Table Builder
 *
 * @package     AAWP\Functions\Components
 * @since       3.5.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

define( 'AAWP_TABLE_ROWS_MAX', 20 ); // higher than 20 lead to missing products -.-
define( 'AAWP_TABLE_PRODUCTS_MAX', 6 );

global $aawp_table;
global $aawp_table_id;
global $aawp_tables;

/**
 * Register post type
 */
add_action( 'init', function() {

    $labels = array(
        'name'                  => _x( 'Tables', 'Post Type General Name', 'aawp' ),
        'singular_name'         => _x( 'Table', 'Post Type Singular Name', 'aawp' ),
        'menu_name'             => __( 'Tables', 'aawp' ),
        'name_admin_bar'        => __( 'Table', 'aawp' ),
        'archives'              => __( 'Table Archives', 'aawp' ),
        'attributes'            => __( 'Table Attributes', 'aawp' ),
        'parent_item_colon'     => __( 'Parent Table:', 'aawp' ),
        'all_items'             => __( 'All Tables', 'aawp' ),
        'add_new_item'          => __( 'Add New Table', 'aawp' ),
        'add_new'               => __( 'Add New', 'aawp' ),
        'new_item'              => __( 'New Table', 'aawp' ),
        'edit_item'             => __( 'Edit Table', 'aawp' ),
        'update_item'           => __( 'Update Table', 'aawp' ),
        'view_item'             => __( 'View Table', 'aawp' ),
        'view_items'            => __( 'View Tables', 'aawp' ),
        'search_items'          => __( 'Search Table', 'aawp' ),
        'not_found'             => __( 'Not found', 'aawp' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'aawp' ),
        'featured_image'        => __( 'Featured Image', 'aawp' ),
        'set_featured_image'    => __( 'Set featured image', 'aawp' ),
        'remove_featured_image' => __( 'Remove featured image', 'aawp' ),
        'use_featured_image'    => __( 'Use as featured image', 'aawp' ),
        'insert_into_item'      => __( 'Insert into table', 'aawp' ),
        'uploaded_to_this_item' => __( 'Uploaded to this table', 'aawp' )
    );
    $args = array(
        'label'                 => __( 'Table', 'aawp' ),
        'description'           => __( 'Table Post Type', 'aawp' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor' ),
        'hierarchical'          => false,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => false,
        'menu_position'         => 25,
        'show_in_admin_bar'     => false,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'rewrite'               => false,
        'capability_type'       => 'page',
        /*
        'capabilities' => array(
            'create_posts' => 'do_not_allow',
        ),
        */
        'show_in_rest'          => false,
    );
    register_post_type( 'aawp_table', $args );

}, 0 );

/**
 * Register admin menu
 */
add_action( 'aawp_admin_menu', function( $aawp_menu_slug ) {

    if ( ! aawp_is_license_valid() )
        return;

    add_submenu_page(
        $aawp_menu_slug,
        __( 'AAWP - Tables', 'aawp' ),
        __( 'Tables', 'aawp' ),
        'edit_pages' ,
        'edit.php?post_type=aawp_table'
    );

}, 20 );

/**
 * Correct active submenu item
 *
 * Source: http://stackoverflow.com/a/23002306
 */
add_filter('parent_file', function( $parent_file ) {

    global $submenu_file, $current_screen;

    if ( $current_screen->post_type == 'aawp_table' ) {
        $submenu_file = 'edit.php?post_type=aawp_table';
        $parent_file = 'aawp-settings';
    }

    return $parent_file;
});

/**
 * Add post columns
 */
add_filter('manage_aawp_table_posts_columns', function( $defaults) {

    $defaults['aawp_table_shortcode'] = __( 'Shortcode', 'aawp' );

    return $defaults;

}, 10);

/**
 * Add post columns content
 */
add_action('manage_aawp_table_posts_custom_column', function( $column_name, $post_id ) {

    if ( $column_name == 'aawp_table_shortcode' ) {

        $shortcode = aawp_get_shortcode();
        ?>
        <input type='text' onClick="this.select();" value='[<?php echo $shortcode; ?> table="<?php echo $post_id; ?>"]'readonly='readonly' />
        <?php
    }

}, 10, 2);

/**
 * Removing admin elements on custom post type page(s)
 */
add_action( 'admin_head', function() {

    global $pagenow, $typenow;

    if ( empty( $typenow ) && ! empty( $_GET['post'] ) ) {
        $post = get_post($_GET['post']);
        $typenow = $post->post_type;
    }

    if ( is_admin() && ( $pagenow =='post-new.php' || $pagenow =='post.php' || $pagenow =='edit.php' ) && $typenow == 'aawp_table' ) {
        ?>
        <style type="text/css">
            #postdivrich {
                display: none;
            }
        </style>
        <?php
    }

});

/**
 * Setup meta boxes
 */
if ( is_admin() ) {
    add_action( 'load-post.php', 'aawp_admin_table_setup_meta_boxes' );
    add_action( 'load-post-new.php', 'aawp_admin_table_setup_meta_boxes' );
}

function aawp_admin_table_setup_meta_boxes() {

    /* Add meta boxes on the 'add_meta_boxes' hook. */
    add_action( 'add_meta_boxes', 'aawp_admin_table_add_meta_boxes' );

    /* Save post meta on the 'save_post' hook. */
    add_action( 'save_post', 'aawp_admin_table_save_meta', 10, 2 );
    //add_action( 'new_to_publish', 'aawp_admin_table_save_meta' );
}

function aawp_admin_table_add_meta_boxes() {

    add_meta_box(
        'aawp-table-config-metabox',
        '<span class="dashicons dashicons-admin-plugins"></span> ' . __( 'Configuration', 'aawp' ),
        'aawp_admin_table_config_meta_box_render',
        'aawp_table',
        'normal',
        'high'
    );

    add_meta_box(
        'aawp-table-products-metabox',
        '<span class="dashicons dashicons-cart"></span> ' . __( 'Products', 'aawp' ),
        'aawp_admin_table_products_meta_box_render',
        'aawp_table',
        'normal',
        'high'
    );

    add_meta_box(
        'aawp-table-shortcode-metabox',
        '<span class="aawp-brand-icon"></span>' . __( 'Shortcode', 'aawp' ),
        'aawp_admin_table_shortcode_meta_box_render',
        'aawp_table',
        'side',
        'high'
    );
}

/**
 * Rendering config meta box
 *
 * @param $post
 */
function aawp_admin_table_config_meta_box_render( $post ) {

    // Use nonce for verification to secure data sending
    wp_nonce_field( basename( __FILE__ ), 'aawp_admin_table_nonce' );

    $table_id = $post->ID;

    // Get data from db
    $table_settings = get_post_meta( $table_id, '_aawp_table_settings', true );
    $rows = get_post_meta( $table_id, '_aawp_table_rows', true );

    //aawp_debug( $table_settings, '$table_settings' );
    //aawp_debug( $rows, '$rows' );
    ?>

    <div class="aawp-table-wrap">

        <h3><?php _e('Customizations', 'aawp' ); ?></h3>
        <p>
            <?php
            printf( wp_kses( __( 'By editing the following customizations, you are going to overwrite the <a href="%s">global settings</a> for this table only.', 'aawp' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( aawp_admin_get_settings_page_url( 'functions' ) . '#aawp_table_template' ) );
            ?>
        </p>
        <table class="form-table">
            <tbody>
            <tr class="row">
                <th><?php _e('Labels', 'aawp' ); ?></th>
                <td>
                    <?php
                    $label_col_options = aawp_admin_table_get_label_col_options();
                    $label_col = ( ! empty ( $table_settings['labels'] ) ) ? $table_settings['labels'] : '';
                    ?>
                    <select id="aawp_table_settings_labels" name="aawp_table_settings[labels]">
                        <option value="" <?php selected( $label_col, '' ); ?>><?php _e('Standard', 'aawp' ); ?></option>
                        <?php foreach ( $label_col_options as $key => $label ) { ?>
                            <option value="<?php echo $key; ?>" <?php selected( $label_col, $key ); ?>><?php echo $label; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
                <?php /*
                <tr class="row">
                    <th><?php _e('Highlight color', 'aawp' ); ?></th>
                    <td><input type="text" class="aawp-input-colorpicker" value="#e9e9e9" /></td>
                </tr>
                */ ?>
            </tbody>
        </table>

        <h3><?php _e('Rows', 'aawp' ); ?></h3>

        <div id="aawp-table-rows" class="aawp-table-rows" data-aawp-table-sortable-rows="true">
            <?php for ( $row_id = 0; $row_id < AAWP_TABLE_ROWS_MAX; $row_id++ ) { ?>
                <?php $row_active = ( isset( $rows[$row_id] ) && aawp_admin_is_table_row_valid( $rows[$row_id] ) ) ? true : false; ?>
                <div id="aawp-table-row-item-<?php echo $row_id; ?>" class="aawp-table-rows__item" style="display: <?php echo ( $row_active ) ? 'block' : 'none'; ?>">
                    <input type="hidden"  />
                    <div class="aawp-table-rows__col aawp-table-rows__col--move">
                        <span class="dashicons dashicons-move"></span>
                    </div>
                    <div class="aawp-table-rows__col aawp-table-rows__col--status">
                        <label class="aawp-control-switch" title="<?php _e('Show/hide row', 'aawp' ); ?>">
                            <input id="aawp-table-row-status-<?php echo $row_id; ?>" type="checkbox"
                                   name="aawp_table_rows[<?php echo $row_id; ?>][status]"
                                   value="1"<?php if ( isset( $rows[$row_id]['status'] ) && '1' == $rows[$row_id]['status'] ) echo ' checked="checked"'; ?>>
                            <span class="aawp-control-switch__slider"></span>
                        </label>
                    </div>
                    <div class="aawp-table-rows__col aawp-table-rows__col--input">
                        <input class="widefat" type="text"
                               name="aawp_table_rows[<?php echo $row_id; ?>][label]"
                               value="<?php echo ( isset( $rows[$row_id]['label'] ) ) ? esc_html( $rows[$row_id]['label'] ) : ''; ?>"
                               data-aawp-table-rows-input="<?php echo $row_id; ?>" placeholder="<?php _e('Enter a label or leave empty...', 'aawp'); ?>" />
                    </div>
                    <div class="aawp-table-rows__col aawp-table-rows__col--type">
                        <?php
                        $row_types = aawp_admin_table_get_row_types();
                        $row_type = ( isset( $rows[$row_id]['type'] ) ) ? $rows[$row_id]['type'] : '';
                        ?>
                        <select class="widefat" name="aawp_table_rows[<?php echo $row_id; ?>][type]" data-aawp-table-row-type="<?php echo $row_id; ?>">
                            <option value=""><?php _e('Please select...', 'aawp' ); ?></option>
                            <?php foreach( $row_types as $type_key => $type_label ) { ?>
                                <option
                                        value="<?php echo $type_key; ?>" <?php selected( $row_type, $type_key ); ?>
                                    <?php if ( in_array( $type_key, aawp_admin_table_get_row_type_drops() ) ) echo ' disabled="disabled"'; ?>
                                ><?php echo $type_label; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="aawp-table-rows__col aawp-table-rows__col--highlight">
                        <label class="aawp-control-icon-switch" title="<?php _e('Highlight row', 'aawp' ); ?>">
                            <input type="checkbox"
                                   name="aawp_table_rows[<?php echo $row_id; ?>][highlight]"
                                   value="1"<?php if ( isset( $rows[$row_id]['highlight'] ) && '1' == $rows[$row_id]['highlight'] ) echo ' checked="checked"'; ?>>
                            <span class="aawp-control-icon-switch__icon"><span class="dashicons dashicons-admin-customizer"></span></span>
                        </label>
                    </div>
                    <div class="aawp-table-rows__col aawp-table-rows__col--link">
                        <label class="aawp-control-icon-switch" title="<?php _e('Link output', 'aawp' ); ?>">
                            <input type="checkbox"
                                   name="aawp_table_rows[<?php echo $row_id; ?>][link]"
                                   value="1"<?php if ( isset( $rows[$row_id]['link'] ) && '1' == $rows[$row_id]['link'] ) echo ' checked="checked"'; ?>>
                            <span class="aawp-control-icon-switch__icon"><span class="dashicons dashicons-admin-links"></span></span>
                        </label>
                    </div>
                    <div class="aawp-table-rows__col aawp-table-rows__col--actions">
                        <span class="aawp-table-rows__action aawp-table-rows__action--delete" data-aawp-table-delete-row="<?php echo $row_id; ?>" title="<?php _e('Remove row', 'aawp' ); ?>"><span class="dashicons dashicons-trash"></span></span>
                    </div>
                </div>

            <?php } ?>
        </div>

        <p id="aawp-table-no-rows" style="display: <?php echo ( empty( $rows ) ) ? 'block' : 'none'; ?>;">
            <?php _e('There are no rows added yet.', 'aawp' ); ?>
        </p>

        <p>
            <span class="button aawp-table-button" data-aawp-table-add-row="true"><span class="dashicons dashicons-plus-alt"></span> <?php _e('Add new row', 'aawp' ); ?></span>
        </p>

    </div>

    <?php
}

/**
 * Rendering products meta box
 *
 * @param $post
 */
function aawp_admin_table_products_meta_box_render( $post ) {

    $table_id = $post->ID;

    $options = aawp_get_options();

    // Reset
    //update_post_meta( $table_id, '_aawp_table_products', '' );
    //update_post_meta( $table_id, '_aawp_table_rows', '' );

    // Gett data from db
    $rows = get_post_meta( $table_id, '_aawp_table_rows', true );
    $products = get_post_meta( $table_id, '_aawp_table_products', true );
    ?>

    <div class="aawp-table-wrap">

        <div id="aawp-table-products" class="aawp-table-products" data-aawp-table-sortable-products="true">
            <?php for ( $product_id = 0; $product_id < AAWP_TABLE_PRODUCTS_MAX; $product_id++ ) { ?>
                <div id="aawp-table-product-<?php echo $product_id; ?>" class="aawp-table-product" style="display: <?php echo ( isset( $products[$product_id] ) ) ? 'block' : 'none'; ?>">
                    <div class="aawp-table-product__header">
                        <!-- Status -->
                        <div class="aawp-table-product__status">
                            <label class="aawp-control-switch" title="<?php _e('Show/hide product', 'aawp' ); ?>">
                                <input id="aawp-table-product-status-<?php echo $product_id; ?>" type="checkbox"
                                       name="aawp_table_products[<?php echo $product_id; ?>][status]"
                                       value="1"<?php if ( isset( $products[$product_id]['status'] ) && '1' == $products[$product_id]['status'] ) echo ' checked="checked"'; ?>>
                                <span class="aawp-control-switch__slider"></span>
                            </label>
                        </div>
                        <!-- ASIN -->
                        <div id="aawp-table-product-asin-<?php echo $product_id; ?>" class="aawp-table-product__asin">
                            <label for="aawp-table-product-asin-field-<?php echo $product_id; ?>" class="aawp-table-product__asin-label"><?php printf( esc_html__( 'Product no. %d', 'aawp' ), ( $product_id + 1 ) ); ?></label>
                            <input type="text" id="aawp-table-product-asin-field-<?php echo $product_id; ?>" class="aawp-table-product__asin-input"
                                   name="aawp_table_products[<?php echo $product_id; ?>][asin]"
                                   value="<?php echo ( isset( $products[$product_id]['asin'] ) ) ? $products[$product_id]['asin'] : ''; ?>"
                                   placeholder="<?php _e('Enter ASIN ...', 'aawp' ); ?>" />
                            <a id="aawp-table-product-asin-search-<?php echo $product_id; ?>" class="aawp-table-product__asin-search" href="#aawp-modal-table-product-search" data-aawp-modal="true" data-aawp-table-search-product="<?php echo $product_id; ?>" title="<?php _e('Click in order to start product search', 'aawp' ); ?>">
                                <span class="dashicons dashicons-search"></span></a>
                        </div>
                        <?php /*
                        <!-- Highlight -->
                        <div id="aawp-table-product-highlight-<?php echo $product_id; ?>" class="aawp-table-product__highlight">
                            <label class="aawp-control-icon-switch" title="<?php _e('Highlight product', 'aawp' ); ?>">
                                <input type="checkbox"
                                       name="aawp_table_products[<?php echo $product_id; ?>][highlight]"
                                       value="1"<?php if ( isset( $products[$product_id]['highlight'] ) && '1' == $products[$product_id]['highlight'] ) echo ' checked="checked"'; ?>>
                                <span class="aawp-control-icon-switch__icon"><span class="dashicons dashicons-admin-customizer"></span></span>
                            </label>
                        </div>
                        */ ?>
                        <!-- Options -->
                        <div id="aawp-table-product-options-<?php echo $product_id; ?>" class="aawp-table-product__options">
                            <a href="#" data-aawp-table-product-footer-toggle="<?php echo $product_id; ?>"><?php _e('Show more options', 'aawp' ); ?></a>
                        </div>
                        <!-- Title -->
                        <?php if ( ! empty( $products[$product_id]['title'] ) ) { ?>
                            <div class="aawp-table-product__title"><?php echo $products[$product_id]['title']; ?></div>
                        <?php } ?>
                        <!-- Delete -->
                        <span class="aawp-table-product__action aawp-table-product__action--delete" data-aawp-table-delete-product="<?php echo $product_id; ?>"><span class="dashicons dashicons-trash"></span></span>
                    </div>
                    <div class="aawp-table-product__body" data-aawp-table-sortable-rows="true">
                        <?php for ( $row_id = 0; $row_id <= AAWP_TABLE_ROWS_MAX; $row_id++ ) { ?>

                            <?php
                            $product_row_types = aawp_admin_table_get_row_types();
                            $product_row_type = ( ! empty( $products[$product_id]['rows'][$row_id]['type'] ) ) ? $products[$product_id]['rows'][$row_id]['type'] : '';

                            // Row type overwritten?
                            if ( ! empty( $product_row_type ) ) {
                                $product_row_type_value = $product_row_type;
                            // Otherwise check default row type
                            } elseif( ! empty( $rows[$row_id]['type'] ) ) {
                                $product_row_type_value = $rows[$row_id]['type'];
                            } else {
                                $product_row_type_value = '';
                            }
                            //$product_row_value = ( ! empty( $products[$product_id]['rows'][$row_id]['value'] ) && ! in_array( $product_row_type, aawp_admin_table_get_row_type_drops() )  ) ? $products[$product_id]['rows'][$row_id]['value'] : '';
                            ?>

                            <div class="aawp-table-product__row" data-aawp-table-product-row="<?php echo $row_id; ?>" style="display: <?php echo ( isset( $rows[$row_id] ) ) ? 'block' : 'none'; ?>">
                                <div class="aawp-table-product__data aawp-table-product__data--move">
                                    <span class="dashicons dashicons-move"></span>
                                </div>
                                <div class="aawp-table-product__data aawp-table-product__data--label" data-aawp-table-product-label-field="<?php echo $row_id; ?>">
                                    <?php echo ( ! empty( $rows[$row_id]['label'] ) ) ? $rows[$row_id]['label'] : ''; ?>
                                </div>
                                <div class="aawp-table-product__data aawp-table-product__data--type">
                                    <select class="widefat" name="aawp_table_products[<?php echo $product_id; ?>][rows][<?php echo $row_id; ?>][type]" data-aawp-table-product-row-type="true">
                                        <option value=""><?php _e('Select in order to overwrite...', 'aawp' ); ?></option>
                                        <?php foreach( $product_row_types as $type_key => $type_label ) { ?>
                                            <option
                                                    value="<?php echo $type_key; ?>" <?php selected( $product_row_type, $type_key ); ?>
                                                    <?php if ( in_array( $type_key, aawp_admin_table_get_row_type_drops() ) ) echo ' disabled="disabled"'; ?>
                                            ><?php echo $type_label; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="aawp-table-product__data aawp-table-product__data--value">
                                    <div class="aawp-table-product__value <?php if ( ! empty( $product_row_type_value ) ) echo ' aawp-table-product__value--' . $product_row_type_value; ?>" data-aawp-table-product-row-value="true">
                                        <?php /*
                                        <!-- Shared values -->
                                        <div class="aawp-table-product-value aawp-table-product-value--linked">
                                            Linked?
                                        </div>
                                        */ ?>
                                        <!-- Bool -->
                                        <div class="aawp-table-product-value aawp-table-product-value--bool">
                                            <?php $product_row_value_bool = ( isset( $products[$product_id]['rows'][$row_id]['values']['bool'] ) && '1' == $products[$product_id]['rows'][$row_id]['values']['bool'] ) ? true : false; ?>
                                            <input id="aawp-table-product-<?php echo $product_id; ?>-row-<?php echo $row_id; ?>-value-bool-yes" class="widefat" type="radio"
                                                   name="aawp_table_products[<?php echo $product_id; ?>][rows][<?php echo $row_id; ?>][values][bool]" value="1" <?php checked( $product_row_value_bool, true ); ?> /><label for="aawp-table-product-<?php echo $product_id; ?>-row-<?php echo $row_id; ?>-value-bool-yes"><?php _e('Yes', 'aawp' ); ?></label>
                                            <input id="aawp-table-product-<?php echo $product_id; ?>-row-<?php echo $row_id; ?>-value-bool-no" class="widefat" type="radio"
                                                   name="aawp_table_products[<?php echo $product_id; ?>][rows][<?php echo $row_id; ?>][values][bool]" value="0" <?php checked( $product_row_value_bool, false ); ?> /><label for="aawp-table-product-<?php echo $product_id; ?>-row-<?php echo $row_id; ?>-value-bool-no"><?php _e('No', 'aawp' ); ?></label>
                                        </div>
                                        <!-- Shortcode -->
                                        <div class="aawp-table-product-value aawp-table-product-value--shortcode">
                                            <?php $product_row_value_shortcode = ( ! empty( $products[$product_id]['rows'][$row_id]['values']['shortcode'] ) ) ? esc_html( $products[$product_id]['rows'][$row_id]['values']['shortcode'] ) : ''; ?>
                                            <input class="widefat" type="text"
                                                   name="aawp_table_products[<?php echo $product_id; ?>][rows][<?php echo $row_id; ?>][values][shortcode]"
                                                   value="<?php echo $product_row_value_shortcode; ?>" />
                                        </div>
                                        <!-- Custom Button -->
                                        <div class="aawp-table-product-value aawp-table-product-value--custom_button">
                                            <?php
                                            $product_row_value_custom_button_text = ( ! empty( $products[$product_id]['rows'][$row_id]['values']['custom_button_text'] ) ) ? esc_html( $products[$product_id]['rows'][$row_id]['values']['custom_button_text'] ) : '';
                                            $product_row_value_custom_button_url = ( ! empty( $products[$product_id]['rows'][$row_id]['values']['custom_button_url'] ) ) ? $products[$product_id]['rows'][$row_id]['values']['custom_button_url'] : '';
                                            $product_row_value_custom_button_blank = ( isset( $products[$product_id]['rows'][$row_id]['values']['custom_button_blank'] ) && '1' == $products[$product_id]['rows'][$row_id]['values']['custom_button_blank'] ) ? true : false;
                                            $product_row_value_custom_button_nofollow = ( isset( $products[$product_id]['rows'][$row_id]['values']['custom_button_nofollow'] ) && '1' == $products[$product_id]['rows'][$row_id]['values']['custom_button_nofollow'] ) ? true : false;
                                            ?>
                                            <div class="aawp-table-product-value-group">
                                                <input class="widefat" type="text"
                                                       name="aawp_table_products[<?php echo $product_id; ?>][rows][<?php echo $row_id; ?>][values][custom_button_text]"
                                                       value="<?php echo $product_row_value_custom_button_text; ?>"
                                                       placeholder="<?php _e('Enter button text...', 'aawp' ); ?>" />
                                            </div>
                                            <div class="aawp-table-product-value-group">
                                                <input class="widefat" type="text"
                                                       name="aawp_table_products[<?php echo $product_id; ?>][rows][<?php echo $row_id; ?>][values][custom_button_url]"
                                                       value="<?php echo $product_row_value_custom_button_url; ?>"
                                                       placeholder="<?php _e('Enter button url...', 'aawp' ); ?>" />
                                            </div>
                                            <div class="aawp-table-product-value-group">
                                                <label>
                                                    <input class="widefat" type="checkbox"
                                                           name="aawp_table_products[<?php echo $product_id; ?>][rows][<?php echo $row_id; ?>][values][custom_button_blank]"
                                                           value="1" <?php if ( $product_row_value_custom_button_blank ) echo 'checked="checked"'; ?> /> <?php _e('Open in new window', 'aawp' ); ?>
                                                </label>
                                            </div>
                                            <div class="aawp-table-product-value-group">
                                                <label>
                                                    <input class="widefat" type="checkbox"
                                                           name="aawp_table_products[<?php echo $product_id; ?>][rows][<?php echo $row_id; ?>][values][custom_button_nofollow]"
                                                           value="1" <?php if ( $product_row_value_custom_button_nofollow ) echo 'checked="checked"'; ?> /> <?php _e('sponsored', 'aawp' ); ?>
                                                </label>
                                            </div>
                                        </div>
                                        <!-- Custom Text -->
                                        <div class="aawp-table-product-value aawp-table-product-value--custom_text">
                                            <?php $product_row_value_custom_text = ( ! empty( $products[$product_id]['rows'][$row_id]['values']['custom_text'] ) ) ? esc_html( $products[$product_id]['rows'][$row_id]['values']['custom_text'] ) : ''; ?>
                                            <input class="widefat" type="text"
                                                   name="aawp_table_products[<?php echo $product_id; ?>][rows][<?php echo $row_id; ?>][values][custom_text]"
                                                   value="<?php echo $product_row_value_custom_text; ?>" />
                                        </div>
                                        <!-- Custom HTML -->
                                        <div class="aawp-table-product-value aawp-table-product-value--custom_html">
                                            <?php $product_row_value_custom_html = ( ! empty( $products[$product_id]['rows'][$row_id]['values']['custom_html'] ) ) ? $products[$product_id]['rows'][$row_id]['values']['custom_html'] : ''; ?>
                                            <textarea class="widefat" name="aawp_table_products[<?php echo $product_id; ?>][rows][<?php echo $row_id; ?>][values][custom_html]"><?php echo esc_html( $product_row_value_custom_html ); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php } ?>
                    </div>
                    <div id="aawp-table-product-footer-<?php echo $product_id; ?>" class="aawp-table-product__footer" style="display: <?php echo ( empty( $products[$product_id]['highlight_color'] ) || ! empty( $products[$product_id]['highlight_text'] ) ) ? 'block' : 'none'; ?>">
                        <!-- Highlight -->
                        <label class="aawp-table-product__highlight-label"><?php _e('Highlight Product:', 'aawp' ); ?></label>
                        <input type="text" class="aawp-input-colorpicker"
                               name="aawp_table_products[<?php echo $product_id; ?>][highlight_color]"
                               value="<?php echo ( isset( $products[$product_id]['highlight_color'] ) ) ? $products[$product_id]['highlight_color'] : ''; ?>"
                               placeholder="<?php _e('Select color...', 'aawp' ); ?>" />
                        <input type="text" class="aawp-table-product__highlight-text"
                               name="aawp_table_products[<?php echo $product_id; ?>][highlight_text]"
                               value="<?php echo ( isset( $products[$product_id]['highlight_text'] ) ) ? $products[$product_id]['highlight_text'] : ''; ?>"
                               placeholder="<?php _e('Maybe enter text ...', 'aawp' ); ?>" />
                    </div>
                </div>
            <?php } ?>
        </div>

        <p id="aawp-table-no-products" style="display: <?php echo ( empty( $products ) ) ? 'block' : 'none'; ?>;">
            <?php _e('There are no products added yet.', 'aawp' ); ?>
        </p>

        <hr />

        <p>
            <strong><?php _e('Add new products', 'aawp' ); ?></strong>
        </p>
        <p id="aawp-table-add-product-actions" class="aawp-table-add-product-actions">
            <span class="aawp-table-add-product-by-asin">
                <input type="text" value="" placeholder="<?php _e('Enter ASIN...', 'aawp' ); ?>" style="width: 125px;" data-aawp-table-add-product-by-asin="true" /><span class="button aawp-table-button" data-aawp-table-add-product-by-asin-submit="true"><?php _e('Add product by ASIN', 'aawp' ); ?></span>
            </span>
            &nbsp;<?php _e('or', 'aawp' ); ?>&nbsp;
            <a class="aawp-table-add-products-search" href="#aawp-modal-table-product-search" data-aawp-modal="true" data-aawp-table-add-products-search="true">
                <span class="button aawp-table-button"><?php _e('Search for product(s)', 'aawp' ); ?></span>
            </a>
        </p>

        <div id="aawp-table-add-product-notices" class="aawp-table-add-product-notices">
            <p id="aawp-table-add-product-notice-asin-length" class="aawp-notice aawp-notice--warning"><?php _e('The ASIN you enter must contain at least 10 digits.', 'aawp'); ?></p>
        </div>

        <?php aawp_admin_the_table_product_search_modal(); ?>
        <?php //aawp_admin_the_modal_link( 'table-product-search', 'Open modal' ); ?>

        <input type="hidden" id="aawp-post-id" value="<?php echo $table_id; ?>">
        <input type="hidden" id="aawp-table-active-product-search" value="">
        <input type="hidden" id="aawp-ajax-search-items-selected" value="" />

    </div>

    <?php
    //aawp_debug( $products, '$products' );

    //aawp_debug( $rows, '$rows' );
}

/**
 * Rendering shortcode meta box
 *
 * @param $post
 */
function aawp_admin_table_shortcode_meta_box_render( $post ) {

    $table_id = $post->ID;

    $shortcode = aawp_get_shortcode();
    ?>
    <input type='text' onClick="this.select();" value='[<?php echo $shortcode; ?> table="<?php echo $table_id; ?>"]'readonly='readonly' />
    <?php
}

/**
 * Saving meta fields
 *
 * @param $post_id
 *
 * @return string
 */
function aawp_admin_table_save_meta( $post_id, $post ) {

    //aawp_debug_log( 'aawp_admin_table_save_meta' );

    /* Verify the nonce before proceeding. */
    if ( ! isset( $_POST['aawp_admin_table_nonce'] ) )
        return $post_id;

    //aawp_debug_log( 'aawp_admin_table_nonce SET' );

    if ( ! wp_verify_nonce( $_POST['aawp_admin_table_nonce'], basename( __FILE__ ) ) )
        return $post_id;

    //aawp_debug_log( 'aawp_admin_table_nonce PASSED' );

    /* Get the post type object. */
    $post_type = get_post_type_object( $post->post_type );

    /* Check if the current user has permission to edit the post. */
    if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) )
        return $post_id;

    // Debug
    //aawp_debug( $_POST, 'Debug: $_POST' );
    //aawp_debug_log( $_POST['aawp_table_rows'] );
    //aawp_debug_log( $_POST['aawp_table_products'] );

    //aawp_debug_log( 'Products submitted: ' . sizeof ( $_POST['aawp_table_products'] ) );

    // Defaults
    $valid_input_row_ids = array();
    $table_settings = array();
    $table_rows = array();
    $table_products = array();
    $table_customizations = array();

    // Handle settings
    if ( isset( $_POST['aawp_table_settings'] ) ) {

        $settings = array(
            'labels' => ( ! empty( $_POST['aawp_table_settings']['labels'] ) ) ? $_POST['aawp_table_settings']['labels'] : ''
        );

        $table_settings = $settings;
    }

    if ( ! empty( $table_settings['labels'] ) )
        $table_customizations[] = str_replace('_', '-', $table_settings['labels'] ) . '-labels';

    // Handling rows
    if ( isset( $_POST['aawp_table_rows'] ) && is_array( $_POST['aawp_table_rows'] ) ) {

        foreach ( $_POST['aawp_table_rows'] as $row_id => $row ) {

            // Kick dummies
            if ( ! is_numeric( $row_id ) || ! aawp_admin_is_table_row_valid( $row ) )
                continue;

            $valid_input_row_ids[] = $row_id;

            // Build row data
            $data = array(
                'status' => ( isset( $row['status'] ) && '1' == $row['status'] ) ? true : false,
                'label' => ( isset( $row['label'] ) ) ? $row['label'] : '',
                'type' => ( isset( $row['type'] ) ) ? esc_html( $row['type'] ) : '',
                'highlight' => ( isset( $row['highlight'] ) && '1' == $row['highlight'] ) ? true : false,
                'link' => ( isset( $row['link'] ) && '1' == $row['link'] ) ? true : false
            );

            $table_rows[] = $data;
        }
    }

    // Handling products
    if ( isset( $_POST['aawp_table_products'] ) && is_array( $_POST['aawp_table_products'] ) ) {

        foreach ( $_POST['aawp_table_products'] as $product_id => $product ) {

            // Kick dummies
            if ( ! is_numeric( $product_id ) )
                continue;

            // Validate inputs
            if( empty( $product['asin'] ) )
                continue;

            $data = array(
                'status' => ( isset( $product['status'] ) && '1' == $product['status'] ) ? true : false,
                'asin' => trim( $product['asin'] ),
                'rows' => array()
            );

            // Build row data
            if ( isset( $product['rows'] ) && is_array( $product['rows'] ) ) {

                foreach ( $product['rows'] as $product_row_id => $product_row ) {

                    if ( ! in_array( $product_row_id, $valid_input_row_ids ) )
                        continue;

                    $row_type = ( isset( $_POST['aawp_table_rows'][$product_row_id]['type'] ) ) ? $_POST['aawp_table_rows'][$product_row_id]['type'] : '';
                    $product_row_type = ( isset( $product_row['type'] ) && ! in_array( $product_row['type'], aawp_admin_table_get_row_type_drops() ) ) ? $product_row['type'] : '';
                    $product_row_values = array();

                    //aawp_debug_log( 'row #' . $product_row_id . ' - $row_type: ' . $row_type . ' >> product asin ' . $product['asin'] . ' $product_row_id: ' . $product_row_id . ' $product_row_type: ' . $product_row_type );

                    // Values
                    if ( 'bool' === $product_row_type || ( ! $product_row_type && 'bool' === $row_type ) ) {
                        $product_row_values['bool'] = ( isset( $product_row['values']['bool'] ) && '1' == $product_row['values']['bool'] ) ? true : false;

                    } elseif ( 'shortcode' === $product_row_type || ( ! $product_row_type && 'shortcode' === $row_type ) ) {
                        $product_row_values['shortcode'] = ( ! empty( $product_row['values']['shortcode'] ) ) ? sanitize_text_field( $product_row['values']['shortcode'] ) : '';

                    } elseif ( 'custom_button' === $product_row_type || ( ! $product_row_type && 'custom_button' === $row_type ) ) {
                        $product_row_values['custom_button_text'] = ( ! empty( $product_row['values']['custom_button_text'] ) ) ? sanitize_text_field( $product_row['values']['custom_button_text'] ) : '';
                        $product_row_values['custom_button_url'] = ( ! empty( $product_row['values']['custom_button_url'] ) ) ? esc_url_raw( $product_row['values']['custom_button_url'] ) : '';
                        $product_row_values['custom_button_blank'] = ( isset( $product_row['values']['custom_button_blank'] ) && '1' == $product_row['values']['custom_button_blank'] ) ? true : false;
                        $product_row_values['custom_button_nofollow'] = ( isset( $product_row['values']['custom_button_nofollow'] ) && '1' == $product_row['values']['custom_button_nofollow'] ) ? true : false;

                    } elseif ( 'custom_text' === $product_row_type || ( ! $product_row_type && 'custom_text' === $row_type ) ) {
                        $product_row_values['custom_text'] = ( ! empty( $product_row['values']['custom_text'] ) ) ? sanitize_text_field( $product_row['values']['custom_text'] ) : '';

                    } elseif ( 'custom_html' === $product_row_type || ( ! $product_row_type && 'custom_html' === $row_type ) ) {
                        $product_row_values['custom_html'] = ( ! empty( $product_row['values']['custom_html'] ) ) ? $product_row['values']['custom_html'] : '';
                    }


                    // Finish
                    $product_row_data = array(
                        'type' => $product_row_type,
                        'values' => $product_row_values
                    );

                    //aawp_debug_log( '$product_row_data' );
                    //aawp_debug_log( $product_row_data );

                    $data['rows'][] = $product_row_data;
                }
            }

            // Options
            $data['highlight'] = false;

            if ( ! empty( $product['highlight_color'] ) ) {
                $data['highlight_color'] = esc_html( $product['highlight_color'] );
                $data['highlight'] = true;
            }

            if ( ! empty( $product['highlight_text'] ) ) {
                $data['highlight_text'] = sanitize_text_field( $product['highlight_text'] );
            }

            if ( $data['highlight'] && ! empty( $product['highlight_color'] ) && ! empty( $product['highlight_text'] ) && ! in_array( 'ribbon', $table_customizations ) )
                $table_customizations[] = 'ribbon';

            // Finally store data
            $table_products[] = $data;
        }
    }

    // Saving meta
    update_post_meta( $post_id, '_aawp_table_settings', $table_settings );
    update_post_meta( $post_id, '_aawp_table_rows', $table_rows );
    update_post_meta( $post_id, '_aawp_table_products', $table_products );
    update_post_meta( $post_id, '_aawp_table_customizations', $table_customizations );
}

/**
 * Validate table row
 *
 * @param $table_row
 *
 * @return bool
 */
function aawp_admin_is_table_row_valid( $table_row ) {

    if ( ! empty( $table_row['status'] ) )
        return true;

    if ( ! empty( $table_row['label'] ) )
        return true;

    if ( ! empty( $table_row['highlight'] ) )
        return true;

    if ( ! empty( $table_row['link'] ) )
        return true;

    return false;
}

/**
 * The product search modal
 */
function aawp_admin_the_table_product_search_modal() {

    aawp_admin_the_modal_header( 'table-product-search', __( 'Product Search', 'aawp' ) ); ?>

    <div class="aawp-modal__form">
        <p>
            <input id="aawp-ajax-search-input" type="text" class="widefat" value="" placeholder="<?php _e('Enter search term...', 'aawp' ); ?>" />
            <br />
            <span class="button aawp-table-button" data-aawp-ajax-search=true" style="margin-top: 10px;">
                <span class="dashicons dashicons-search"></span> <?php _e('Search products', 'aawp' ); ?>
            </span>
        </p>
    </div>

    <div id="aawp-ajax-search-results" class="aawp-ajax-search-results" data-aawp-ajax-search-items-select="9"></div>
    <div id="aawp-ajax-search-meta" class="aawp-ajax-search-meta">
        <span id="aawp-table-product-select" class="button button-primary button-large aawp-table-button aawp-table-product-select" data-aawp-table-product-search-select="true"><?php _e( 'Confirm selection', 'aawp' ); ?></span>
    </div>

    <?php aawp_admin_the_modal_footer();
}

/**
 * Get label col options
 *
 * @return array
 */
function aawp_admin_table_get_label_col_options() {
    return array(
        'show' => __( 'Show', 'aawp' ),
        'hide' => __('Hide', 'aawp'),
        'hide_mobile' => __( 'Hide on mobile devices only', 'aawp' ),
        'hide_desktop' => __( 'Show on mobile devices only', 'aawp' )
    );
}

/**
 * Settings
 */
if ( ! class_exists( 'AAWP_Table_Builder_Settings' ) ) {

    class AAWP_Table_Builder_Settings extends AAWP_Functions {

        /**
         * Construct the plugin object
         */
        public function __construct() {
            // Call parent constructor first
            parent::__construct();

            // Setup identifier
            $this->func_id = 'table';
            $this->func_name = __('Table Builder', 'aawp');
            $this->func_listener = 'table';

            // Execute
            $this->hooks();
        }

        /**
         * Add settings functions
         */
        public function add_settings_functions_filter( $functions ) {

            $functions[] = $this->func_id;

            return $functions;
        }

        /**
         * Settings: Register section and fields
         */
        public function add_settings() {

            add_settings_section(
                'aawp_table_section',
                false,
                false,
                'aawp_functions'
            );

            add_settings_field(
                'aawp_table',
                __( 'Table Builder', 'aawp'),
                array( &$this, 'settings_render' ),
                'aawp_functions',
                'aawp_table_section',
                array('label_for' => 'aawp_table_template')
            );
        }

        /**
         * Settings callbacks
         */
        public function settings_render() {

            $templates = array(
                $this->template_default => __('Standard', 'aawp')
            );

            $styles = array(
                '0' => __('Standard', 'aawp'),
            );

            $template = ( ! empty ( $this->options['functions'][$this->func_id . '_template'] ) ) ? $this->options['functions'][$this->func_id . '_template'] : '';
            $style = ( ! empty ( $this->options['functions'][$this->func_id . '_style'] ) ) ? $this->options['functions'][$this->func_id . '_style'] : '';

            $label_col_options = aawp_admin_table_get_label_col_options();
            $label_col = ( ! empty ( $this->options['functions'][$this->func_id . '_labels'] ) ) ? $this->options['functions'][$this->func_id . '_labels'] : 'show';

            $highlight_bg_color = ( ! empty ( $this->options['functions'][$this->func_id . '_highlight_bg_color'] ) ) ? $this->options['functions'][$this->func_id . '_highlight_bg_color'] : aawp_get_default_highlight_bg_color();
            $highlight_color = ( ! empty ( $this->options['functions'][$this->func_id . '_highlight_color'] ) ) ? $this->options['functions'][$this->func_id . '_highlight_color'] : aawp_get_default_highlight_bg_color();
            ?>

            <!-- Template -->
            <h4 class="first"><?php _e('Default Template', 'aawp'); ?></h4>
            <p>
                <select id="aawp_<?php echo $this->func_id; ?>_template" name="aawp_functions[<?php echo $this->func_id; ?>_template]">
                    <?php foreach ( $templates as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $template, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </p>

            <!-- Labels -->
            <h4><?php _e('Labels', 'aawp'); ?></h4>
            <p>
                <select id="aawp_<?php echo $this->func_id; ?>_labels" name="aawp_functions[<?php echo $this->func_id; ?>_labels]">
                    <?php foreach ( $label_col_options as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $label_col, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </p>

            <!-- Style -->
            <h4><?php _e('Default style', 'aawp'); ?></h4>
            <p>
                <select id="aawp_<?php echo $this->func_id; ?>_style" name="aawp_functions[<?php echo $this->func_id; ?>_style]">
                    <?php foreach ( $styles as $key => $label ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $style, $key ); ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </p>

            <!-- Highlight rows -->
            <h4><?php _e('Highlight rows', 'aawp'); ?></h4>
            <div>
                <div class="aawp-color-picker-inline">
                    <label for="aawp_<?php echo $this->func_id; ?>_highlight_bg_color"><?php _e('Background color', 'aawp' ); ?></label>
                    <input id="aawp_<?php echo $this->func_id; ?>_highlight_bg_color" name="aawp_functions[<?php echo $this->func_id; ?>_highlight_bg_color]" type="text" class="aawp-input-colorpicker" value="<?php echo $highlight_bg_color; ?>" />
                </div>
                <div class="aawp-color-picker-inline">
                    <label for="aawp_<?php echo $this->func_id; ?>_highlight_color"><?php _e('Font color', 'aawp' ); ?></label>
                    <input id="aawp_<?php echo $this->func_id; ?>_highlight_color" name="aawp_functions[<?php echo $this->func_id; ?>_highlight_color]" type="text" class="aawp-input-colorpicker" value="<?php echo $highlight_color; ?>" />
                </div>
            </div>

            <?php
            do_action( 'aawp_settings_functions_table_render' );
        }

        /*
         * Hooks & Actions
         */
        public function hooks() {

            // Settings functions
            add_filter( $this->settings_functions_filter, array( &$this, 'add_settings_functions_filter' ) );

            add_action( 'aawp_settings_functions_register', array( &$this, 'add_settings' ), 60 );
        }
    }

    if ( is_admin() ) {
        new AAWP_Table_Builder_Settings();
    }
}

/**
 * Public functions handler
 */
if ( ! class_exists( 'AAWP_Table_Builder_Functions' ) ) {

    class AAWP_Table_Builder_Functions extends AAWP_Functions {

        public $func_id, $func_attr;

        public function __construct() {

            parent::__construct();

            $this->func_id = 'table';
            $this->func_listener = 'table';
            $this->func_attr = $this->setup_func_attr( $this->func_id );

            // Hooks
            add_action( 'aawp_shortcode_handler', array( &$this, 'shortcode' ), 10, 2 );
        }

        function shortcode( $atts, $content ) {

            if ( empty( $atts[$this->func_listener] ) )
                return false;

            $this->display( $atts[$this->func_listener], $content, $atts );
        }

        function display( $table_id, $content, $atts = array()) {

            if ( ! is_numeric( $table_id ) || 'aawp_table' != get_post_type( $table_id ) ) {
                _e('Invalid table id.' ,'aawp' );
                return;
            }

            if ( 'publish' !== get_post_status( $table_id ) )
                return; // Don't execute when table was not published.

            $table_rows = aawp_get_table_rows( $table_id );
            $table_products = aawp_get_table_products( $table_id );
            $table_customizations = aawp_get_table_customizations( $table_id );
            $table_timestamp = 0;

            if ( ( ! is_array( $table_rows ) || sizeof( $table_rows ) == 0 ) || ( ! is_array( $table_products ) || sizeof( $table_products ) == 0 ) ) {
                _e('Table setup not completed.' ,'aawp' );
                return;
            }

            // Merge with settings customizations
            $table_customizations = aawp_merge_table_settings_customizations( $table_customizations );

            // Loop rows
            $row_labels_exist = false;

            foreach ( $table_rows as $table_row_id => $table_row ) {

                if ( ! empty( $table_row['label'] ) ) {
                    $row_labels_exist = true;
                    break;
                }
            }

            if ( ! $row_labels_exist && ! in_array( 'hide-labels', $table_customizations ) )
                $table_customizations[] = 'hide-labels';

            // Preload products ( Use cache or fetch items from API )
            //aawp_debug( $table_products, '$table_products' );

            $table_product_asins = array();

            foreach ( $table_products as $table_product_id => $table_product ) {

                // Check if ASIN is set
                if ( empty( $table_product['asin'] ) )
                    unset( $table_products[$table_product_id] );

                // Check if products are hidden
                if ( empty( $table_product['status'] ) )
                    unset( $table_products[$table_product_id] );

                $table_product_asins[$table_product_id] = $table_product['asin'];
            }

            //Use cache or fetch items from API
            $items = $this->get_items( $table_product_asins, $this->func_id );

            //aawp_debug( $items, '$items' );

            $table_items = array();

            if ( is_array( $items ) && sizeof( $items ) > 0 ) {

                foreach ( $items as $item ) {

                    if ( empty( $item['asin'] ) )
                        continue;

                    $original_table_product_id = array_search( $item['asin'], $table_product_asins );

                    $table_items[$original_table_product_id] = $item;

                    if ( empty( $table_timestamp ) && ! empty( $item['date_updated'] ) )
                        $table_timestamp = strtotime( $item['date_updated'] );
                }
            }

            $table_products_missing = array_diff_key( $table_products, $table_items );
            //aawp_debug( $table_products_missing, '$table_products_missing' );

            $table_products_final = array_diff_key( $table_products, $table_products_missing );
            //aawp_debug( $table_products_final, '$table_products_final' );

            $table_products = $table_products_final;

            // Still enough products to show the table?
            if ( sizeof( $table_products ) == 0 ) {
                _e('Table could not be displayed.' ,'aawp' );
                return;
            }

            //aawp_debug( $table_products,'$table_products AFTER' );

            // Prepare rendering
            global $aawp_table;
            global $aawp_tables;

            $aawp_table = array(
                'id' => $table_id,
                'rows' => $table_rows,
                'products' => $table_products,
                'items' => $table_items,
                'customizations' => $table_customizations,
                'atts' => $atts
            );

            $aawp_tables[] = $aawp_table;

            // Setup vars
            $this->setup_shortcode_vars( $this->intersect_atts($atts, $this->func_attr), $content );

            // Setup template handler and render output
            $template_handler = new AAWP_Template_Handler();
            $template_handler->set_atts( $this->atts );
            $template_handler->set_type( $this->func_id );
            //$template_handler->set_template_variables( $template_variables );
            $template_handler->set_timestamp_template( $table_timestamp );
            $template_handler->render_template( 'table-builder' );

            //aawp_debug( $aawp_table, '$aawp_table' );
            //aawp_debug( $table_rows, '$table_rows' );
            //aawp_debug( $table_products, '$table_products' );
            //aawp_debug( $table_customizations, '$table_customizations' );
        }
    }

    new AAWP_Table_Builder_Functions();
}

/**
 * Get available row types
 *
 * @return array
 */
function aawp_admin_table_get_row_types() {

    //$first = ( $default ) ? __( 'Please select...', 'aawp' ) : __( 'Select in order to overwrite...', 'aawp' );

    return array(
        '_divider_product_' => __( '----- Product Data -----', 'aawp' ),
        'title'  => __( 'Title', 'aawp' ),
        'thumb'  => __( 'Thumbnail', 'aawp' ),
        'price'  => __( 'Price', 'aawp' ),
        'prime'  => __( 'Prime Status', 'aawp' ),
        'star_rating' => __( 'Star Rating', 'aawp' ),
        'reviews' => __( 'Reviews', 'aawp' ),
        'button' => __( 'Buy Now Button', 'aawp' ),
        '_divider_elements_' => __( '----- Elements -----', 'aawp' ),
        'bool' => __( 'Yes/No', 'aawp' ),
        '_divider_custom_' => '----- Custom Output -----',
        'shortcode' => __( 'Shortcode', 'aawp' ),
        'custom_button' => __( 'Custom Button', 'aawp' ),
        'custom_text' => __( 'Custom Text', 'aawp' ),
        'custom_html' => __( 'Custom HTML', 'aawp' )
    );
}

/**
 * Get product row type drops
 *
 * @return array
 */
function aawp_admin_table_get_row_type_drops() {
    return array( '_divider_product_', '_divider_elements_', '_divider_custom_' );
}

/**
 * Get table rows
 *
 * @param $table_id
 *
 * @return mixed
 */
function aawp_get_table_rows( $table_id ) {

    $rows = get_post_meta( $table_id, '_aawp_table_rows', true );

    return $rows;
}

/**
 * Get table products
 *
 * @param $table_id
 *
 * @return mixed
 */
function aawp_get_table_products( $table_id ) {

    $products = get_post_meta( $table_id, '_aawp_table_products', true );

    return $products;
}

/**
 * Get table customizations
 *
 * @param $table_id
 *
 * @return mixed
 */
function aawp_get_table_customizations( $table_id ) {

    $customizations = get_post_meta( $table_id, '_aawp_table_customizations', true );

    return $customizations;
}

/**
 * Merge table customizations with global settings
 *
 * @param $customizations
 * @return array
 */
function aawp_merge_table_settings_customizations( $customizations ) {

    $options = aawp_get_options( 'functions' );

    //aawp_debug( $customizations, 'aawp_merge_table_settings_customizations >> $customizations' );

    $labels_set = false;

    foreach ( $customizations as $customization ) {

        // Labels
        if ( strpos( $customization, '-labels' ) !== false )
            $labels_set = true;
    }

    // Labels
    if ( ! $labels_set && ! empty( $options['table_labels'] ) && 'show' != $options['table_labels'] ) {
        $customizations[] = str_replace('_', '-', $options['table_labels'] ) . '-labels';
    }

    return $customizations;
}

/**
 * Output table customization classes
 *
 * @param $default_class
 */
function aawp_the_table_customization_classes( $default_class ) {

    global $aawp_table;

    if ( ! isset( $aawp_table['customizations'] ) || ! is_array( $aawp_table['customizations'] ) || sizeof( $aawp_table['customizations'] ) === 0 )
        return;

    foreach ( $aawp_table['customizations'] as $customization ) {
        echo ' ' . $default_class . '--' . esc_html( $customization );
    }
}

/**
 * Output table product data classes
 *
 * @param string $default_class
 * @param $table_row_id
 * @param $table_product_id
 */
function aawp_the_table_product_data_classes( $default_class = '', $table_row_id, $table_product_id ) {

    global $aawp_table;

    $classes = $default_class;

    // Add type
    if ( ! empty( $aawp_table['products'][$table_product_id]['rows'][$table_row_id]['type'] ) ) {
        $type = $aawp_table['products'][$table_product_id]['rows'][$table_row_id]['type'];
    } else {
        $type = ( ! empty( $aawp_table['rows'][$table_row_id]['type'] ) ) ? $aawp_table['rows'][$table_row_id]['type'] : false;
    }

    if ( $type )
        $classes .= ' ' . $default_class . '--type-' . esc_html( $type );

    if ( ! empty( $classes ) )
        echo $classes;
}

/**
 * Check if table product ribbon is visible
 *
 * @param $table_product_id
 *
 * @return bool
 */
function aawp_show_table_product_ribbon( $table_product_id ) {

    global $aawp_table;

    //aawp_debug( $aawp_table['products'][$table_product_id] );

    if ( empty( $aawp_table['products'][$table_product_id]['highlight'] ) )
        return false;

    if ( empty( $aawp_table['products'][$table_product_id]['highlight_text'] ) )
        return false;

    return true;
}

/**
 * Check if table product is highlighted
 *
 * @param $table_product_id
 *
 * @return bool
 */
function aawp_is_table_product_highlighted( $table_product_id ) {

    global $aawp_table;

    if ( ! empty( $aawp_table['products'][$table_product_id]['highlight'] ) && ! empty( $aawp_table['products'][$table_product_id]['highlight_color'] ) ) {
        return true;
    }

    return false;
}

/**
 * Output table product highlight ribbon
 *
 * @param $table_product_id
 * @param null $table_row_id
 */
function aawp_the_table_product_highlight_ribbon( $table_product_id, $table_row_id = null ) {

    global $aawp_table;

    if ( aawp_is_table_product_highlighted( $table_product_id ) && ! empty( $aawp_table['products'][$table_product_id]['highlight_text'] ) ) {

        // Maybe check row
        if ( ! is_null( $table_row_id ) && $table_row_id != 0 )
            return;

        echo '<span class="aawp-tb-ribbon">';
        echo esc_html( $aawp_table['products'][$table_product_id]['highlight_text'] );
        echo '</span>';
    }
}

/**
 * Output table product data type
 *
 * @param $table_row_id
 * @param $table_product_id
 */
function aawp_the_table_product_data_type( $table_row_id, $table_product_id ) {

    global $aawp_table;

    if ( ! empty( $aawp_table['products'][$table_product_id]['rows'][$table_row_id]['type'] ) ) {
        $type = $aawp_table['products'][$table_product_id]['rows'][$table_row_id]['type'];
    } else {
        $type = ( ! empty( $aawp_table['rows'][$table_row_id]['type'] ) ) ? $aawp_table['rows'][$table_row_id]['type'] : '';
    }

    echo $type;
}

/**
 * Display the product data
 *
 * @param $table_row_id
 * @param $table_product_id
 */
function aawp_the_table_product_data( $table_row_id, $table_product_id ) {

    global $aawp_table;

    if ( ! isset( $aawp_table['products'][$table_product_id]['rows'][$table_row_id] ) || empty( $aawp_table['products'][$table_product_id]['asin'] ) )
        return;

    $data = $aawp_table['products'][$table_product_id]['rows'][$table_row_id];

    if ( ! empty( $data['type'] ) ) {
        $type = $data['type'];
    } elseif ( ! empty( $aawp_table['rows'][$table_row_id]['type'] ) ) {
        $type = $aawp_table['rows'][$table_row_id]['type'];
    } else {
        return;
    }

    $options = aawp_get_options();

    $asin = $aawp_table['products'][$table_product_id]['asin'];
    $linked = ( isset( $aawp_table['rows'][$table_row_id]['link'] ) && '1' == $aawp_table['rows'][$table_row_id]['link'] ) ? true : false;

    $field_args = array();

    if ( $linked )
        $field_args['format'] = 'linked';

    // Shortcode attributes
    if ( isset( $aawp_table['atts'] ) ) {

        $table_atts = $aawp_table['atts'];

        if ( ! empty( $table_atts['tracking_id'] ) )
            $field_args['tracking_id'] = $table_atts['tracking_id'];
    }

    $link_text = '';

    $output = '-';

    // Product title
    if ( 'title' === $type ) {

        $title = aawp_get_field_value( $asin, 'title', $field_args );

        if ( ! empty( $title ) )
            $output = $title;

    // Product thumb
    } elseif ( 'thumb' === $type ) {

        $image = aawp_get_field_value( $asin, 'image' );

        if ( ! empty( $image ) ) {

            $title = aawp_get_field_value( $asin, 'title' );

            //$output = '<span class="aawp-tb-thumb" style="background-image: url(' . esc_html( $image ) . ');"><img src="' . aawp_get_assets_url() . 'img/thumb-spacer.png" alt="' . esc_html( $title ) . '" /></span>';
            $output = '<span class="aawp-tb-thumb"><img src="' . esc_html( $image ) . '" alt="' . esc_html( $title ) . '" /></span>';

            if ( $linked )
                $link_text = aawp_get_field_value( $asin, 'title' );
        }

        /*
        $thumb = aawp_get_field_value( $asin, 'thumb' );

        if ( ! empty( $thumb ) )
            $output = $thumb;
        */

    // Product price
    } elseif ( 'price' === $type ) {

        $price = aawp_get_field_value( $asin, 'price', $field_args );

        if ( ! empty( $price ) )
            $output = $price;

    // Product prime status
    } elseif ( 'prime' === $type ) {

        $prime = aawp_get_field_value( $asin, 'prime', $field_args );

        if ( ! empty( $prime ) )
            $output = $prime;

    // Product star rating
    } elseif ( 'star_rating' === $type ) {

        $star_rating = aawp_get_field_value( $asin, 'star_rating', $field_args );

        if ( ! empty( $star_rating ) )
            $output = $star_rating;

    // Product reviews
    } elseif ( 'reviews' === $type ) {

        $reviews = aawp_get_field_value( $asin, 'reviews' );

        if ( ! empty( $reviews ) )
            $output = $reviews;

        if ( $linked )
            $link_text = $output;

    // Product button
    } elseif ( 'button' === $type ) {

        $button = aawp_get_field_value( $asin, 'button', $field_args );

        if ( ! empty( $button ) )
            $output = $button;

    // Elements: Bool
    } elseif ( 'bool' === $type ) {

        $output = ( ! empty( $data['values']['bool'] ) ) ? '<span class="aawp-icon-yes"></span>' : '<span class="aawp-icon-no"></span>';

    // Shortcode
    } elseif ( 'shortcode' === $type ) {

        if ( ! empty( $data['values']['shortcode'] ) ) {
            $output = do_shortcode( $data['values']['shortcode'] );
        }

    // Custom Button
    } elseif ( 'custom_button' === $type ) {

        $custom_button_text = ( ! empty( $data['values']['custom_button_text'] ) ) ? $data['values']['custom_button_text'] : false;
        $custom_button_url = ( ! empty( $data['values']['custom_button_url'] ) ) ? $data['values']['custom_button_url'] : false;

        if ( $custom_button_text && $custom_button_url ) {

            $custom_button_classes = 'aawp-button';

            if ( ! empty( $options['output']['button_detail_style'] ) )
                $custom_button_classes .= ' aawp-button--' . esc_html( $options['output']['button_detail_style'] );

            if ( ! empty( $options['output']['button_detail_style_rounded'] ) )
                $custom_button_classes .= ' rounded';

            if ( ! empty( $options['output']['button_detail_style_shadow'] ) )
                $custom_button_classes .= ' shadow';

            $output = '<a class="' . $custom_button_classes . '"';
            $output .= ' href="' . esc_url( $custom_button_url ) . '"';
            $output .= ' title="' . strip_tags( $custom_button_text ) . '"';

            if ( isset( $data['values']['custom_button_blank'] ) && '1' == $data['values']['custom_button_blank'] )
                $output .= ' target="_blank"';

            if ( isset( $data['values']['custom_button_nofollow'] ) && '1' == $data['values']['custom_button_nofollow'] )
                $output .= ' rel="nofollow noopener sponsored"';

            $output .= '>';
            $output .= $custom_button_text;
            $output .= '</a>';
        }

    // Custom Text
    } elseif ( 'custom_text' === $type ) {

        if ( ! empty( $data['values']['custom_text'] ) ) {
            $output = do_shortcode( $data['values']['custom_text'] );

            if ( $linked )
                $link_text = $data['values']['custom_text'];
        }

    // Custom HTML
    } elseif ( 'custom_html' === $type ) {

        if ( ! empty( $data['values']['custom_html'] ) )
            $output = do_shortcode( $data['values']['custom_html'] );
    }

    // Build custom link
    if ( '-' != $output && ! empty( $link_text ) ) {

        if ( empty( $link_url ) ) {

            if ( isset ( $field_args['format'] ) && 'linked' === $field_args['format'] )
                unset( $field_args['format'] ); // Prevent double linking

            $link_url = aawp_get_field_value( $asin, 'url', $field_args );
        }

        if ( ! empty( $link_url ) ) {

            $attributes = array(); // TODO: Move this into a unique way to handle (class.template-functions.php > "the_product_container())

            $attributes['product-id'] = $asin;
            $attributes['product-title'] = '%title%';

            $attributes = apply_filters( 'aawp_product_container_attributes', $attributes );

            $data_attributes = '';

            if ( sizeof( $attributes ) != 0 ) {

                foreach ( $attributes as $key => $value ) {

                    // Handle placeholders
                    if ( '%title%' === $value )
                        $value = aawp_get_field_value( $asin, 'title' );

                    // Add attribute to output
                    if ( ! empty ( $value ) )
                        $data_attributes .= ' data-aawp-' . $key . '="' . str_replace('"', "'", $value) . '"';
                }
            }

            $output = '<a href="' . esc_url( $link_url ) .'" title="' . esc_html( $link_text ) . '" target="_blank" rel="nofollow noopener sponsored"' . $data_attributes . '>' . $output . '</a>';
        }
    }

    // Wrap output in order to apply custom styles
    $output = '<div class="aawp-tb-product-data-' . esc_html( $type ) . '">' . $output . '</div>';

    // Finally echo output
    echo $output;
}

/**
 * Add table custom setting css
 *
 * @param $custom_setting_css
 *
 * @return string
 */
function aawp_add_table_custom_setting_css( $custom_setting_css ) {

    $options = aawp_get_options();

    $highlight_bg_color = ( ! empty ( $options['functions']['table_highlight_bg_color'] ) ) ? $options['functions']['table_highlight_bg_color'] : aawp_get_default_highlight_bg_color();
    $highlight_color = ( ! empty ( $options['functions']['table_highlight_color'] ) ) ? $options['functions']['table_highlight_color'] : aawp_get_default_highlight_color();

    if ( ! empty( $highlight_bg_color ) )
        $custom_setting_css .= '.aawp .aawp-tb__row--highlight{background-color:' . $highlight_bg_color . ';}';

    if ( ! empty( $highlight_color ) ) {
        $custom_setting_css .= '.aawp .aawp-tb__row--highlight{color:' . $highlight_color . ';}';
        $custom_setting_css .= '.aawp .aawp-tb__row--highlight a{color:' . $highlight_color . ';}';
    }

    return $custom_setting_css;
}
add_filter( 'aawp_custom_setting_css', 'aawp_add_table_custom_setting_css' );
add_filter( 'aawp_custom_setting_amp_css', 'aawp_add_table_custom_setting_css' );

/**
 * Add table custom styles
 *
 * @param $styles
 *
 * @return string
 */
function aawp_the_table_custom_styles( $styles ) {

    global $aawp_tables;

    if ( ! is_array( $aawp_tables ) || sizeof( $aawp_tables ) == 0 )
        return $styles;

    foreach ( $aawp_tables as $table ) {

        if ( ! isset( $table['id'] ) )
            continue;

        $table_id = $table['id'];

        $css_prefix = '#aawp-tb-' . $table_id . ' ';

        // Product customizations
        if ( isset( $table['products'] ) && is_array( $table['products'] ) && sizeof( $table['products'] ) > 0 ) {

            foreach( $table['products'] as $table_product_id => $table_product ) {

                if ( $table_product['highlight'] ) {

                    if ( ! empty( $table_product['highlight_color'] ) ) {

                        $highlight_bg_color = aawp_color_hex2rgba( esc_html( $table_product['highlight_color'] ), 0.1 );
                        $highlight_border_color = esc_html( $table_product['highlight_color'] );
                        $highlight_text = ( ! empty( $table_product['highlight_text'] ) ) ? esc_html( $table_product['highlight_text'] ) : '';

                        // Desktop
                        $styles .= $css_prefix . '.aawp-tb--desktop .aawp-tb__row:first-child .aawp-tb-product-' . $table_product_id . '.aawp-tb__data--highlight { border-top-color: ' . $highlight_border_color . '; }';
                        $styles .= $css_prefix . '.aawp-tb--desktop .aawp-tb__row:last-child .aawp-tb-product-' . $table_product_id . '.aawp-tb__data--highlight { border-bottom-color: ' . $highlight_border_color . '; }';
                        $styles .= $css_prefix . '.aawp-tb--desktop .aawp-tb-product-' . $table_product_id . '.aawp-tb__data--highlight:not(.aawp-tb__data--type-thumb) { background-color: ' . $highlight_bg_color . '; }';
                        $styles .= $css_prefix . '.aawp-tb--desktop .aawp-tb-product-' . $table_product_id . '.aawp-tb__data--highlight { border-right-color: ' . $highlight_border_color . '; }';
                        $styles .= $css_prefix . '.aawp-tb--desktop .aawp-tb-product-' . $table_product_id . '.aawp-tb__data--highlight::after { border-color: ' . $highlight_border_color . '; }';

                        if ( ! empty( $highlight_text ) ) {
                            $styles .= $css_prefix . '.aawp-tb--desktop .aawp-tb-product-' . $table_product_id . '.aawp-tb__data--highlight .aawp-tb-ribbon { background-color: ' . $highlight_border_color . '; }';
                        }

                        // Mobile
                        $styles .= $css_prefix . '.aawp-tb--mobile .aawp-tb-product-' . $table_product_id . '.aawp-tb__product--highlight { border-color: ' . $highlight_border_color . '; }';
                        //$styles .= $css_prefix . '.aawp-tb--mobile .aawp-tb-product-' . $table_product_id . '.aawp-tb__product--highlight .aawp-tb__row { background-color: ' . $highlight_bg_color . '; }';

                        if ( ! empty( $highlight_text ) ) {
                            $styles .= $css_prefix . '.aawp-tb--mobile .aawp-tb-product-' . $table_product_id . '.aawp-tb__product--highlight .aawp-tb-ribbon { background-color: ' . $highlight_border_color . '; }';
                        }
                    }

                }
            }

        }
    }

    return $styles;
}
add_filter( 'aawp_overwrite_styles', 'aawp_the_table_custom_styles' );