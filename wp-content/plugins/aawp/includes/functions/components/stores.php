<?php
/**
 * Stores
 *
 * @package     AAWP\Functions\Components
 * @since       3.2.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Register cronjobs component
 */
function aawp_settings_register_stores_component( $functions ) {

    $functions[] = 'stores';

    return $functions;
}
add_filter( 'aawp_settings_functions', 'aawp_settings_register_stores_component' );

/*
 * Register multiple stores settings
 */
function aawp_add_api_settings_multiple_stores() {

    add_settings_field(
        'aawp_api_multiple_stores',
        __( 'Multiple Stores', 'aawp' ),
        'aawp_settings_api_multiple_stores_render',
        'aawp_api',
        'aawp_api_section',
        array('label_for' => 'aawp_api_multiple_stores')
    );
}
add_action( 'aawp_settings_api_register', 'aawp_add_api_settings_multiple_stores' );

/*
 * Render multiple stores settings
 */
function aawp_settings_api_multiple_stores_render() {

    $options_api = aawp_get_options( 'api' );

    $multiple_stores = ( isset ( $options_api['multiple_stores'] ) && $options_api['multiple_stores'] == '1' ) ? 1 : 0;
    ?>
    <p style="margin-top: <?php echo ( $multiple_stores ) ? '2px' : '4px'; ?>">
        <input type="checkbox" id="aawp_api_multiple_stores" name="aawp_api[multiple_stores]" value="1" <?php echo($multiple_stores == 1 ? 'checked' : ''); ?> />
        <label for="aawp_api_multiple_stores"><?php _e('Check in order to setup multiple tracking ids for further functionality', 'aawp'); ?></label>
    </p>

    <div id="aawp-settings-stores-tracking-ids" style="display: <?php echo ( $multiple_stores ) ? 'block' : 'none'; ?>;">

        <?php
        $stores = aawp_get_amazon_stores();
        $associates_links = aawp_get_amazon_associates_links();
        ?>

        <table class="widefat aawp-settings-table">
            <thead>
                <tr>
                    <th><?php _e('Store', 'aawp'); ?></th>
                    <th><?php _e('Tracking ID', 'aawp'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $stores as $key => $label ) { ?>
                <?php $associate_tag = ( isset ( $options_api['associate_tag_' . $key] ) ) ? $options_api['associate_tag_' . $key] : ''; ?>
                <tr>
                    <td>
                        <label for="aawp_api_associate_tag_<?php echo $key; ?>"><?php aawp_the_icon_flag( $key ); ?> Amazon <?php echo $label; ?> <small><code><?php echo $key; ?></code></small></label>
                        <?php if ( empty( $associate_tag ) ) { ?>
                            <br />
                            <small><a href="<?php echo $associates_links[$key]; ?>" target="_blank" rel="nofollow"><?php _e('Get local Tracking ID', 'aawp'); ?></a></small>
                        <?php } ?>
                    </td>
                    <td>
                        <input type="text" id="aawp_api_associate_tag_<?php echo $key; ?>" class="regular-text" name="aawp_api[associate_tag_<?php echo $key; ?>]" value="<?php echo $associate_tag; ?>" />
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

    </div>

    <?php do_action('aawp_settings_after_stores_table' ); ?>

    <?php
}

/**
 * Update product url tracking id via shortcode
 *
 * @param $tracking_id
 * @param $product_id
 * @param $store
 *
 * @return mixed
 */
function aawp_atts_product_url_tracking_id( $tracking_id, $product_id, $store ) {

    global $aawp_shortcode_atts;

    if ( ! empty( $aawp_shortcode_atts['tracking_id'] ) )
        return $aawp_shortcode_atts['tracking_id'];

    return $tracking_id;
}
//add_filter( 'aawp_product_url_tracking_id', 'aawp_atts_product_url_tracking_id', 20, 3 ); // TODO: Deprecated; let's see if we need it again

/**
 * Update product url store via shortcode
 *
 * @param $store
 * @param $product_id
 *
 * @return mixed
 */
function aawp_atts_product_url_store( $store, $product_id ) {

    global $aawp_shortcode_atts;

    if ( ! empty( $aawp_shortcode_atts['store'] ) ) {

        $options_api = aawp_get_options( 'api' );

        if ( ! empty( $options_api['associate_tag_' . $aawp_shortcode_atts['store']] ) )
            return $aawp_shortcode_atts['store'];
    }

    return $store;
}
//add_filter( 'aawp_product_url_store', 'aawp_atts_product_url_store', 10, 2 ); // TODO: Deprecated; let's see if we need it again
// TODO: add_filter( 'aawp_product_store', 'aawp_atts_product_store', 10, 3 );

/*
 * Update store by shortcode for prime urls
 */
function aawp_update_store_amazon_prime_country( $country, $atts ) {

    if ( isset( $atts['store'] ) ) {
        $options_api = aawp_get_options( 'api' );

        if ( ! empty( $options_api['associate_tag_' . $atts['store']] ) )
            $country = $atts['store'];
    }

    return $country;
}
add_filter( 'aawp_amazon_prime_country', 'aawp_update_store_amazon_prime_country', 10, 2 );

function aawp_update_store_amazon_prime_tracking_id( $tracking_id, $atts ) {

    if ( isset( $atts['store'] ) ) {
        $options_api = aawp_get_options( 'api' );

        if ( ! empty( $options_api['associate_tag_' . $atts['store']] ) )
            $tracking_id = $options_api['associate_tag_' . $atts['store']];
    }

    return $tracking_id;
}
add_filter( 'aawp_amazon_prime_tracking_id', 'aawp_update_store_amazon_prime_tracking_id', 10, 2 );

/*
 * Extend supported shortcode attributes
 */
function aawp_add_stores_shortcode_attributes( $supported, $type ) {

    array_push( $supported, 'tracking_id', 'store' );

    return $supported;
}
add_filter( 'aawp_func_supported_attributes', 'aawp_add_stores_shortcode_attributes', 10, 2 );