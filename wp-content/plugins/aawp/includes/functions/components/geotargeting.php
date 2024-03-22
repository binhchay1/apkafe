<?php
/**
 * Geotargeting
 *
 * @package     AAWP\Functions\Components
 * @since       3.2.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Register component
 */
function aawp_settings_register_geotargeting_component( $functions ) {

    $functions[] = 'geotargeting';

    return $functions;
}
add_filter( 'aawp_settings_functions', 'aawp_settings_register_geotargeting_component' );


function aawp_settings_after_stores_geotargeting_info() {
    ?>
    <p>
        <?php printf( wp_kses( __( 'The geotargeting functionality can be setup on the <a href="%s" target="_blank">functions tab</a>.', 'aawp' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( aawp_admin_get_settings_page_url( 'functions' ) ) ); ?>
    </p>
    <?php
}
add_action( 'aawp_settings_after_stores_table', 'aawp_settings_after_stores_geotargeting_info' );


/*
 * Render settings
 */
function aawp_settings_geotargeting_render() {

    $options_functions = aawp_get_options( 'functions' );

    $geotargeting = ( isset ( $options_functions['geotargeting'] ) && $options_functions['geotargeting'] == '1' ) ? 1 : 0;
    $geotargeting_docs_url = ( aawp_is_lang_de() ) ? 'https://aawp.de/docs/article/geotargeting/' : 'https://getaawp.com/docs/article/geo-targeting/';

	$geotargeting_api_options = array(
        '' => __( 'Default', 'aawp' ),
		'geoip-db' => 'geolocation-db.com',
		//'ipdata' => 'ipdata.co', // Disabled because API key required
        'ipinfo' => 'ipinfo.io',
        'dbip' => 'db-ip.com'
	);

	$geotargeting_api = ( ! empty ( $options_functions['geotargeting_api'] ) ) ? $options_functions['geotargeting_api'] : '';

    $geotargeting_mode_options = array(
        'title' => __('Product search (Recommended)', 'aawp'),
        'asin' => __('Product page', 'aawp')
    );

    $geotargeting_mode = ( ! empty ( $options_functions['geotargeting_mode'] ) ) ? $options_functions['geotargeting_mode'] : 'title';

    ?>
    <!-- Geotargeting -->
    <h4><span class="aawp-icon-globe"></span> <?php _e('Geotargeting', 'aawp'); ?> (<a href="<?php echo ( aawp_is_lang_de() ) ? 'https://aawp.de/docs/article/dsgvo/' : 'https://getaawp.com/docs/article/gdpr/'; ?>" target="_blank" rel="nofollow"><?php _e('GDPR warning', 'aawp'); ?></a>)</h4>
    <p>
        <?php _e('To determine the country of origin, the current IP address of your page visitor will be analyzed through a third party service. The activation and use of the geo-targeting function is voluntary and is the responsibility of the site administrator.', 'aawp' ); ?>
    </p>
    <p>
        <input type="checkbox" id="aawp_geotargeting" name="aawp_functions[geotargeting]" value="1" <?php echo( $geotargeting == 1 ? 'checked' : '' ); ?>>
        <label for="aawp_geotargeting"><?php _e('Check in order to active geotargeting functionality', 'aawp'); ?></label>
    </p>
    <p>
		<?php _e( 'Localization Service', 'aawp' ); ?>
        <select id="aawp_geotargeting_api" name="aawp_functions[geotargeting_api]">
			<?php foreach ( $geotargeting_api_options as $key => $label ) { ?>
                <option value="<?php echo $key; ?>" <?php selected( $geotargeting_api, $key ); ?>><?php echo $label; ?></option>
			<?php } ?>
        </select>
    </p>
    <p>
        <?php _e( 'Link target', 'aawp' ); ?>
        <select id="aawp_geotargeting_mode" name="aawp_functions[geotargeting_mode]">
            <?php foreach ( $geotargeting_mode_options as $key => $label ) { ?>
                <option value="<?php echo $key; ?>" <?php selected( $geotargeting_mode, $key ); ?>><?php echo $label; ?></option>
            <?php } ?>
        </select>
    </p>
    <p>
        <small><?php printf( wp_kses( __( 'Please take a look into the <a href="%s" target="_blank">documentation</a> for more information about the geotargeting feature.', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => '_blank' ) ) ), esc_url( $geotargeting_docs_url ) ); ?></small>
    </p>
    <?php
}
add_action( 'aawp_settings_functions_link_render', 'aawp_settings_geotargeting_render' );

function aawp_geotargeting_maybe_extend_product_container_attributes( $attributes ) {

    $options_functions = aawp_get_options( 'functions' );

    $geotargeting = ( isset ( $options_functions['geotargeting'] ) && $options_functions['geotargeting'] == '1' ) ? 1 : 0;

    if ( $geotargeting )
        $attributes['geotargeting'] = 'true';

    return $attributes;
}
add_filter( 'aawp_product_container_attributes', 'aawp_geotargeting_maybe_extend_product_container_attributes' );

// Deprecated
function aawp_geotargeting_maybe_update_field_container( $result, $request, $container_attributes ) {
    if ( empty ( $result ) )
        return $result;

    $options_functions = aawp_get_options( 'functions' );

    $geotargeting = ( isset ( $options_functions['geotargeting'] ) && $options_functions['geotargeting'] == '1' ) ? 1 : 0;

    if ( $geotargeting && strpos( $result, 'data-aawp-product-title' ) == false) {

        if ( ! in_array( $request, array( 'button_detail' ) ) ) { // 'link', 'thumb', 'star_rating', 'prime', 'premium', 'button', linked values etc.
            //$result = str_replace('<a', '<a ' . $container_attributes, $result );
        }
    }

    return $result;
}
//add_filter( 'aawp_fields_result', 'aawp_geotargeting_maybe_update_field_container', 20, 3 );


/**
 * Enqueue geotargeting script data
 *
 * 1.) Prepare tracking ids
 * 2.) Setup localized stores
 */
function aawp_embed_geotargeting_script_data() {

    if ( aawp_is_user_admin() )
        return;

    $options_api = aawp_get_options( 'api' );
    $stores = aawp_get_amazon_stores();

    if ( empty( $options_api['country'] ) )
        return;

    $default_store = $options_api['country'];
    //$default_store = 'ca';

    $options_functions = aawp_get_options( 'functions' );
    $geotargeting = ( isset ( $options_functions['geotargeting'] ) && $options_functions['geotargeting'] == '1' ) ? 1 : 0;

    if ( ! $geotargeting )
        return;

	$api = ( ! empty ( $options_functions['geotargeting_api'] ) ) ? $options_functions['geotargeting_api'] : '';
    $geotargeting_mode = ( ! empty ( $options_functions['geotargeting_mode'] ) ) ? $options_functions['geotargeting_mode'] : 'title';

    // Settings
    $settings = array( 'store' => $default_store, 'mode' => $geotargeting_mode );

    //aawp_debug_log( __FUNCTION__ . ' >> $settings:' );
    //aawp_debug_log( $settings );

    // Collect tracking IDs
    $tracking_ids = array();

    foreach ( $stores as $key => $label ) {

        if ( ! empty ( $options_api['associate_tag_' . $key] ) && $key != $default_store )
            $tracking_ids[$key] = $options_api['associate_tag_' . $key];
    }

    if ( sizeof( $tracking_ids ) == 0 )
        return;

    //aawp_debug_log( __FUNCTION__ . ' >> $tracking_ids:' );
    //aawp_debug_log( $tracking_ids );

    // Build localized stores
    $localized_stores = array();

    // https://www.proxynova.com/proxy-server-list/
    // https://ipinfo.io/__IP__/json/
    // http://ontheworldmap.com/world-map-2500.jpg

	if ( ! empty( $tracking_ids['com.au'] ) && 'com.au' !== $default_store ) {
		$localized_stores['au'] = 'com.au'; // Australia
        $localized_stores['nz'] = 'com.au'; // New Zealand
	}

    if ( ! empty( $tracking_ids['com.br'] ) && 'com.br' !== $default_store ) {
        $localized_stores['br'] = 'com.br'; // Brazil
    }

    if ( ! empty( $tracking_ids['ca'] ) && 'ca' !== $default_store ) {
        $localized_stores['ca'] = 'ca'; // Canada
    }

    if ( ! empty( $tracking_ids['cn'] ) && 'cn' !== $default_store ) {
        $localized_stores['cn'] = 'cn'; // China
    }

    if ( ! empty( $tracking_ids['de'] ) && 'de' !== $default_store ) {
        $localized_stores['de'] = 'de'; // Germany
        $localized_stores['at'] = 'de'; // Austria
        $localized_stores['ch'] = 'de'; // Switzerland
    }

    if ( ! empty( $tracking_ids['es'] ) && 'es' !== $default_store ) {
        $localized_stores['es'] = 'es'; // Spain
    }

    if ( ! empty( $tracking_ids['fr'] ) && 'fr' !== $default_store ) {
        $localized_stores['fr'] = 'fr'; // France
    }

    if ( ! empty( $tracking_ids['nl'] ) && 'nl' !== $default_store ) {
        $localized_stores['nl'] = 'nl'; // Netherlands
    }

    if ( ! empty( $tracking_ids['in'] ) && 'in' !== $default_store ) {
        $localized_stores['in'] = 'in'; // India
    }

    if ( ! empty( $tracking_ids['it'] ) && 'it' !== $default_store ) {
        $localized_stores['it'] = 'it'; // Italy
    }

    if ( ! empty( $tracking_ids['co.jp'] ) && 'co.jp' !== $default_store ) {
        $localized_stores['jp'] = 'co.jp'; // Japan
    }

    if ( ! empty( $tracking_ids['sa'] ) && 'sa' !== $default_store ) {
        $localized_stores['sa'] = 'sg'; // Saudi Arabia
    }

    if ( ! empty( $tracking_ids['se'] ) && 'se' !== $default_store ) {
        $localized_stores['se'] = 'se'; // Sweden
    }

    if ( ! empty( $tracking_ids['sg'] ) && 'sg' !== $default_store ) {
        $localized_stores['sg'] = 'sg'; // Singapore
    }

    if ( ! empty( $tracking_ids['pl'] ) && 'pl' !== $default_store ) {
        $localized_stores['pl'] = 'pl'; // Poland
    }

    if ( ! empty( $tracking_ids['com.tr'] ) && 'com.tr' !== $default_store ) {
        $localized_stores['tr'] = 'com.tr'; // Turkey
    }

    if ( ! empty( $tracking_ids['com.mx'] ) && 'com.mx' !== $default_store ) {
        $localized_stores['mx'] = 'com.mx'; // Mexico
    }

    if ( ! empty( $tracking_ids['co.uk'] ) && 'co.uk' !== $default_store ) {
        $localized_stores['gb'] = 'co.uk'; // UK
        $localized_stores['ie'] = 'co.uk'; // Ireland
    }

    if ( ! empty( $tracking_ids['com'] ) && 'com' !== $default_store ) {
        $localized_stores['us'] = 'com'; // USA
        $localized_stores['ar'] = 'com'; // Argentina
        $localized_stores['cl'] = 'com'; // Chile
        $localized_stores['pe'] = 'com'; // Peru
        $localized_stores['bo'] = 'com'; // Bolivia
        $localized_stores['py'] = 'com'; // Paraguay
        $localized_stores['co'] = 'com'; // Colombia
        $localized_stores['ve'] = 'com'; // Venezuela
        $localized_stores['ec'] = 'com'; // Ecuador

        // Country fallback
        if ( empty( $tracking_ids['ca'] ) && ! isset( $localized_stores['ca'] ) && 'ca' !== $default_store )
            $localized_stores['ca'] = 'com'; // Canada

        if ( empty( $tracking_ids['com.mx'] ) && ! isset( $localized_stores['mx'] ) && 'com.mx' !== $default_store )
            $localized_stores['mx'] = 'com'; // Mexico

	    if ( empty( $tracking_ids['com.au'] ) && ! isset( $localized_stores['au'] ) && 'com.au' !== $default_store )
		    $localized_stores['au'] = 'com'; // Australia
    }

    //aawp_debug_log( __FUNCTION__ . ' >> $localized_stores:' );
    //aawp_debug_log( $localized_stores );

    ?>
    <script type="text/javascript">
        /* <![CDATA[ */
        var aawp_geotargeting_api = <?php echo json_encode( $api ); ?>;
        var aawp_geotargeting_settings = <?php echo json_encode( $settings ); ?>;
        var aawp_geotargeting_localized_stores = <?php echo json_encode( $localized_stores ); ?>;
        var aawp_geotargeting_tracking_ids = <?php echo json_encode( $tracking_ids ); ?>;
        /* ]]> */
    </script>
    <?php
}
add_action( 'wp_footer', 'aawp_embed_geotargeting_script_data', 100 );

/**
 * WP Rocket Compatibility: Prevent geotargeting inline js from being stripped
 *
 * @param $pattern
 * @return array
 */
function aawp_third_party_wprocket_exclude_inline_js( $pattern ) {

    /**
     * Source: https://github.com/wp-media/wp-rocket-helpers/tree/master/static-files/wp-rocket-static-exclude-inline-js
     */
    $pattern[] = 'var aawp_geotargeting_';

    return $pattern;
}
add_filter( 'rocket_excluded_inline_js_content', 'aawp_third_party_wprocket_exclude_inline_js' );