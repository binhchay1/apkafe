<?php
/**
 * General functions
 *
 * @package     AAWP\Functions
 * @since       3.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Global
global $aawp_dependencies;
global $aawp_scripts_loaded;
global $aawp_product;
global $aawp_item_type;

global $aawp_shortcode_atts;

/**
 * Get options
 *
 * @param string $type
 *
 * @return array|mixed
 */
function aawp_get_options( $type = null ) {

    if ( ! empty( $type ) ) {
        return get_option( 'aawp_' . $type, array() );
    } else {
        return array(
            'licensing' => get_option( 'aawp_licensing', array() ),
            'api' => get_option( 'aawp_api', array() ),
            'general' => get_option( 'aawp_general', array() ),
            'output' => get_option( 'aawp_output', array() ),
            'functions' => get_option( 'aawp_functions', array() ),
            'support' => get_option( 'aawp_support', array() )
        );
    }
}

/**
 * Get option
 *
 * @param $key
 * @param $type
 *
 * @return bool|mixed|null
 */
function aawp_get_option( $key, $type ) {

    if ( empty( $key) || empty( $type ) )
        return false;

    $options = aawp_get_options( $type );

    return ( isset( $options[$key] ) ) ? $options[$key] : null;
}

/*
 * Update options
 */
function aawp_update_options( $type, $options ) {
    $result = update_option( 'aawp_' . $type, $options );
    //aawp_debug_log( $result);
}

function aawp_get_amazon_store() {

    $options_api = aawp_get_options( 'api' );

    return ( isset( $options_api['country'] ) ) ? $options_api['country'] : null;
}

/*
 * Get shortcode
 */
function aawp_get_shortcode() {

    $options = aawp_get_options( 'general' );

    $shortcode = ( isset ( $options['shortcode'] ) ) ? $options['shortcode'] : 'amazon';

    return $shortcode;
}

/*
 * Check API connection
 */
function aawp_check_api_connection() {
    $api_options = get_option( 'aawp_api' );

    return ( isset ( $api_options['status'] ) && $api_options['status'] == '1' ) ? true : false;
}

/*
 * Check dependencies
 */
function aawp_check_dependencies() {

    // Check PHP version
    if ( version_compare( phpversion(), '5.3', '<') ) // TODO: Later increase to v5.6 as well
        return false;

    // Check PHP modules
    if ( !function_exists('curl_version') || ! extension_loaded('soap') || ! extension_loaded('mbstring') )
        return false;

    return true;
}

/*
 * Amazon Stores
 */
function aawp_get_amazon_stores() {

    $stores = array(
        'ca'     => __( 'Canada', 'aawp' ),
	    'com.au' => __( 'Australia', 'aawp' ),
        'com.br' => __( 'Brazil', 'aawp' ),
        'cn'     => __( 'China', 'aawp' ),
        'de'     => __( 'Germany', 'aawp' ),
        'fr'     => __( 'France', 'aawp' ),
        'in'     => __( 'India', 'aawp' ),
        'it'     => __( 'Italy', 'aawp' ),
        'co.jp'  => __( 'Japan', 'aawp' ),
        'com.mx' => __( 'Mexico', 'aawp' ),
        'nl'     => __( 'Netherlands', 'aawp' ),
        'pl'     => __( 'Poland', 'aawp' ),
        'sa'     => __( 'Saudi Arabia', 'aawp' ),
        'sg'     => __( 'Singapore', 'aawp' ),
        'es'     => __( 'Spain', 'aawp' ),
        'se'     => __( 'Sweden', 'aawp' ),
        'com.tr' => __( 'Turkey', 'aawp' ),
        'co.uk'  => __( 'UK', 'aawp' ),
        'com'    => __( 'US', 'aawp' ),
        'ae'     => __( 'United Arab Emirates', 'aawp' )
    );

    return $stores;
}

/*
 * Amazon Associates Links
 */
function aawp_get_amazon_associates_links() {

    $associate_links = array(
	    'com.au' => 'https://affiliate-program.amazon.com.au/',
        'de' => 'https://partnernet.amazon.de/',
        'com' => 'https://affiliate-program.amazon.com/',
        'co.uk' => 'https://affiliate-program.amazon.co.uk/',
        'ca' => 'https://associates.amazon.ca/',
        'fr' => 'https://partenaires.amazon.fr/',
        'co.jp' => 'https://affiliate.amazon.co.jp/',
        'it' => 'https://programma-affiliazione.amazon.it/',
        'cn' => 'https://associates.amazon.cn/',
        'es' => 'https://afiliados.amazon.es/',
        'in' => 'https://affiliate-program.amazon.in/',
        'com.tr' => 'https://gelirortakligi.amazon.com.tr/',
        'com.br' => 'https://associados.amazon.com.br/',
        'com.mx' => 'https://afiliados.amazon.com.mx/gp/associates/join/landing/main.html',
        'ae' => 'https://affiliate-program.amazon.ae/',
        'sg' => 'https://affiliate-program.amazon.sg/',
        'nl' => 'https://partnernet.amazon.nl/',
        'pl' => 'https://affiliate-program.amazon.pl/',
        'sa' => 'https://affiliate-program.amazon.sa/',
        'se' => 'https://affiliate-program.amazon.se/',
    );

    // Australia not yet added to associates program

    return $associate_links;
}

/*
 * Amazon product url types
 */
function aawp_get_amazon_product_url_types() {

    $stores = array(
        'basic' => __('Detail Page', 'aawp'),
        'cart' => __('Cart', 'aawp'),
        'wishlist' => __('Wishlist', 'aawp'),
        'recommend' => __('Recommend to', 'aawp'),
        'reviews' => __('Reviews', 'aawp'),
        'offers' => __('Offers', 'aawp')
    );

    return $stores;
}

/*
 * Amazon Associates links
 */
function aawp_get_amazon_associates_link( $country = null ) {

    // Source: http://docs.aws.amazon.com/AWSECommerceService/latest/DG/becomingAssociate.html
    $associates_links = aawp_get_amazon_associates_links();

    $api_options = get_option( 'aawp_api', array() );

    return ( isset( $api_options['country'] ) && isset( $associates_links[$api_options['country']] ) ) ? $associates_links[$api_options['country']] : $associates_links['com'];
}

/*
 * Amazon Product Advertising API links
 */
function aawp_get_amazon_product_advertising_api_link( $country = null ) {

    // Source: http://docs.aws.amazon.com/AWSECommerceService/latest/DG/becomingDev.html

    $product_advertising_api_links = array(
	    'com.au' => 'https://affiliate-program.amazon.com.au/',
        'de' => 'https://partnernet.amazon.de/gp/flex/advertising/api/sign-in.html',
        'com' => 'https://affiliate-program.amazon.com/gp/flex/advertising/api/sign-in.html',
        'co.uk' => 'https://affiliate-program.amazon.co.uk/gp/flex/advertising/api/sign-in.html',
        'ca' => 'https://associates.amazon.ca/gp/flex/advertising/api/sign-in.html',
        'fr' => 'https://partenaires.amazon.fr/gp/flex/advertising/api/sign-in.html',
        'co.jp' => 'https://affiliate-program.amazon.com/gp/flex/advertising/api/sign-in-jp.html',
        'it' => 'https://programma-affiliazione.amazon.it/gp/advertising/api/detail/main.html',
        'com.mx' => 'https://afiliados.amazon.com.mx/gp/advertising/api/detail/main.html',
        'cn' => 'https://associates.amazon.cn/gp/advertising/api/detail/main.html',
        'es' => 'https://afiliados.amazon.es/gp/flex/advertising/api/sign-in.html',
        'in' => 'http://affiliate-program.amazon.in/gp/associates/apply/main.html',
        'com.br' => 'http://associados.amazon.com.br/gp/associates/apply/main.html'
    );

    $api_options = get_option( 'aawp_api', array() );

    return ( isset( $api_options['country'] ) && isset( $product_advertising_api_links[$api_options['country']] ) ) ? $product_advertising_api_links[$api_options['country']] : $product_advertising_api_links['com'];
}

/**
 * Assets embed
 *
 * @since       3.0.0
 * @return      string
 */
function aawp_asset_embed( $file, $target ) {

    $response = wp_remote_get( $file );

    if ( ! is_array( $response ) || ! isset( $response['body'] ) )
        return '';

    $content = $response['body'];

    $targetUrl = $target . 'public/assets/';

    $rewriteUrl = function ($matches) use ($targetUrl) {
        $url = $matches['url'];
        // First check also matches protocol-relative urls like //example.com
        if ((isset($url[0])  && '/' === $url[0]) || false !== strpos($url, '://') || 0 === strpos($url, 'data:')) {
            return $matches[0];
        }
        return str_replace($url, $targetUrl . '/' . $url, $matches[0]);
    };

    $content = preg_replace_callback('/url\((["\']?)(?<url>.*?)(\\1)\)/', $rewriteUrl, $content);
    $content = preg_replace_callback('/@import (?!url\()(\'|"|)(?<url>[^\'"\)\n\r]*)\1;?/', $rewriteUrl, $content);
    // Handle 'src' values (used in e.g. calls to AlphaImageLoader, which is a proprietary IE filter)
    $content = preg_replace_callback('/\bsrc\s*=\s*(["\']?)(?<url>.*?)(\\1)/i', $rewriteUrl, $content);

    return $content;
}

/*
 * Replace product url store
 */
function aawp_replace_url_store( $url, $old, $new ) {

    $url = str_replace($old, $new, $url);

    return $url;
}

/**
 * Replace url tracking id
 *
 * @param $url
 * @param $tracking_id
 *
 * @return mixed
 */
function aawp_replace_url_tracking_id( $url, $tracking_id ) {

    $pattern     = '/tag=[^&]+(.*)/';
    $replacement = "tag=$tracking_id$1";

    $url = preg_replace($pattern, $replacement, $url);

    return $url;
}

/**
 * Replace url tracking id placeholder
 *
 * @param $url
 * @param $tracking_id
 * @param bool $insert
 * @return mixed|null
 */
function aawp_replace_url_tracking_id_placeholder( $url, $tracking_id = '', $insert = true ) {

    if ( empty( $url ) )
        return null;

    if ( empty( $tracking_id ) ) {
        $tracking_id = aawp_get_default_tracking_id();

        if ( empty( $tracking_id ) )
            return $url;
    }

    if ( $insert ) {
        $url = str_replace( $tracking_id, AAWP_PLACEHOLDER_TRACKING_ID, $url );
    } else {
        $url = str_replace( AAWP_PLACEHOLDER_TRACKING_ID, $tracking_id, $url );
    }

    return $url;
}

/*
 * Get document article
 */
function aawp_get_page_url( $slug ) {

    $lang_de = aawp_is_lang_de();

    $url = '#';

    switch ( $slug ) {
        case 'affiliates':
            $url = ( $lang_de ) ? 'https://aawp.de/affiliates/' : 'https://getaawp.com/affiliates/';
            break;
        case 'api_key_checker':
            $url = ( $lang_de ) ? 'https://aawp.de/amazon-api-key-checker/' : 'https://getaawp.com/amazon-api-key-checker/';
            break;
        case 'docs:api_keys':
            $url = ( $lang_de ) ? 'https://aawp.de/docs/article/amazon-product-advertising-api-zugangsdaten/' : 'https://getaawp.com/docs/article/amazon-product-advertising-api-credentials/';
            break;
        case 'docs:api_issues':
            $url = ( $lang_de ) ? 'https://aawp.de/docs/article/amazon-api-nicht-verbunden-haeufige-ursachen/' : 'https://getaawp.com/docs/article/amazon-api-not-connected-frequent-causes/';
            break;
        case 'docs:api_requestthrottled':
            $url = ( $lang_de ) ? 'https://aawp.de/docs/article/amazon-api-requestthrottled-ursache-fehlerbehebungen/' : 'https://getaawp.com/docs/article/amazon-api-requestthrottled-how-to-fix/';
            break;
        case 'docs:amazon_apiv5':
            $url = ( $lang_de ) ? 'https://aawp.de/docs/article/amazon-product-advertising-api-v5/' : 'https://getaawp.com/docs/article/amazon-product-advertising-api-v5/';
            break;
        case 'docs:license_server_issues':
            $url = ( $lang_de ) ? 'https://aawp.de/docs/article/lizenzserver-probleme-fehlerbehebungen/' : 'https://getaawp.com/docs/article/license-server-problems-fixes/';
            break;
        case 'docs:license_upgrades':
            $url = ( $lang_de ) ? 'https://aawp.de/docs/article/lizenz-hochstufen/' : 'https://getaawp.com/docs/article/license-upgrades/';
            break;
        case 'docs:license_renewals':
            $url = ( $lang_de ) ? 'https://aawp.de/docs/article/lizenz-verlaengern/' : 'https://getaawp.com/docs/article/license-renewals/';
            break;
        case 'docs:php_soap':
            $url = ( $lang_de ) ? 'https://aawp.de/docs/article/php-soap-erweiterung/' : 'https://getaawp.com/docs/article/php-soap-extension/';
            break;
        case 'docs:box':
            $url = ( $lang_de ) ? 'https://aawp.de/docs/article/produktboxen/' : 'https://getaawp.com/docs/article/product-boxes/';
            break;
        case 'docs:bestseller':
            $url = ( $lang_de ) ? 'https://aawp.de/docs/article/bestseller-listen/' : 'https://getaawp.com/docs/article/bestseller-lists/';
            break;
        case 'docs:shortcodes':
            $url = ( $lang_de ) ? 'https://aawp.de/docs/article/shortcodes/' : 'https://getaawp.com/docs/article/shortcodes/';
            break;
        case 'docs:browse_nodes':
            $url = ( $lang_de ) ? 'https://aawp.de/docs/article/browse-nodes-verwenden/' : 'https://getaawp.com/docs/article/browse-nodes/';
            break;
        case 'docs:database_garbage_collection':
            $url = ( $lang_de ) ? 'https://aawp.de/docs/article/datenbank-garbage-collection/' : 'https://getaawp.com/docs/article/database-garbage-collection/';
            break;
    }

    return $url;
}

/**
 * Check if content uses our shortcodes
 */
function aawp_content_has_shortcode() {

    global $post;

    if( ( is_a( $post, 'WP_Post' ) && ( has_shortcode( $post->post_content, 'amazon') || has_shortcode( $post->post_content, 'aawp') ) ) ) {
        return true;
    }

    return false;
}

/**
 * Check scheduled events
 */
function aawp_check_scheduled_events() {

    if ( ! wp_next_scheduled ( 'aawp_wp_scheduled_events' ) )
        wp_schedule_event( time(), 'aawp_continuously', 'aawp_wp_scheduled_events' );

    if ( ! wp_next_scheduled ( 'aawp_wp_scheduled_hourly_events' ) )
        wp_schedule_event( time(), 'hourly', 'aawp_wp_scheduled_hourly_events' );

    if ( ! wp_next_scheduled ( 'aawp_wp_scheduled_daily_events' ) )
        wp_schedule_event( time(), 'daily', 'aawp_wp_scheduled_daily_events' );

    if ( ! wp_next_scheduled ( 'aawp_wp_scheduled_weekly_events' ) )
        wp_schedule_event( time(), 'aawp_weekly', 'aawp_wp_scheduled_weekly_events' );
}

/**
 * Remove scheduled events
 */
function aawp_remove_scheduled_events() {
    wp_clear_scheduled_hook('aawp_wp_scheduled_events');
    wp_clear_scheduled_hook('aawp_wp_scheduled_hourly_events');
    wp_clear_scheduled_hook('aawp_wp_scheduled_daily_events');
    wp_clear_scheduled_hook('aawp_wp_scheduled_weekly_events');
}

/**
 * Check if timestamp is outdated
 */
function aawp_is_timestamp_outdated( $timestamp ) {

    $cache_duration = aawp_get_cache_duration();

    $expiry = absint( time() - ( $cache_duration * 60 ) );

    return ( intval ( $timestamp ) < $expiry ) ? true : false;
}

/**
 * Format last update timestamp
 *
 * @param $timestamp
 *
 * @return string
 */
function aawp_format_last_update( $timestamp ) {

    $options = aawp_get_options();

    // Check date format
    $last_update_format = ( isset ( $options['general']['last_update_format'] ) ) ? $options['general']['last_update_format'] : 'date';

    // Language checks
    $lang_de = ( aawp_is_lang_de() ) ? true : false;
    $format_overwrite = apply_filters( 'aawp_func_last_update_format', '' );

    if ( empty( $format_overwrite ) ) {
        // Build format depending on locale
        if ( $lang_de ) {
            $format = 'j.m.Y';

            if ( $last_update_format === 'date_time' ) {
                $format .= ' \u\m H:i';
                $last_update_adding = ' Uhr';
            }
        } else {
            $format = 'Y-m-d';

            if ( $last_update_format === 'date_time' ) {
                $format .= ' \a\t H:i';
            }
        }
    } else {
        $format = $format_overwrite;
    }

    // Build date time
    if ( is_numeric( $timestamp ) ) {
        $last_update = get_date_from_gmt( date( 'Y-m-d H:i:s', $timestamp ), $format );
    } else {
        $last_update = get_date_from_gmt( date( 'Y-m-d H:i:s', strtotime( $timestamp ) ), $format );
    }

    // Addings
    if ( empty( $format_overwrite ) && !empty( $last_update_adding ) )
        $last_update .= $last_update_adding;

    // Finish
    return $last_update;
}

/**
 * Replace last_update placeholder
 *
 * @param $string
 * @param $last_update
 *
 * @return mixed
 */
function aawp_replace_last_update_placeholder( $string, $last_update ) {
    return str_replace('%last_update%', $last_update, $string );
}

/**
 * Credits link
 *
 * @return string
 */
function aawp_get_credits_link() {

    $url = ( aawp_is_lang_de() ) ? 'https://aawp.de' : 'https://getaawp.com/';

    $affiliate_id = aawp_get_option( 'affiliate_id', 'general' );

    if ( ! empty ( $affiliate_id ) && is_numeric( $affiliate_id ) )
        $url = add_query_arg( 'ref', $affiliate_id, $url );

    $link = sprintf( wp_kses( __( 'This product was presentation was made with <a href="%s" rel="nofollow" target="_blank" title="Amazon WordPress Plugin">AAWP</a> plugin.', 'aawp' ), array(  'a' => array( 'href' => array(), 'rel' => array(), 'target' => array(), 'title' => array() ) ) ), esc_url( $url ) );

    return $link;
}

/**
 * Checking settings if crawling of ratings is activated
 *
 * @return bool
 */
function aawp_is_crawling_reviews_activated() {

    $options = aawp_get_options();

    $activated = false;

    if ( isset ( $options['output']['star_rating_size'] ) && $options['output']['star_rating_size'] != '0' )
        $activated = true;

    if ( isset ( $options['output']['show_reviews'] ) && $options['output']['show_reviews'] != '0' )
        $activated = true;

    return $activated;
}

/**
 * Get API error message text from code
 *
 * @param $error_code
 *
 * @return string
 */
function aawp_get_api_error_message( $error_code ) {

    /*
    $text = 'Code: "' . $error_message . '" - ';

    if (strpos($error_message, 'MissingClientTokenId') !== false) {
        $text .= __('API key is missing.', 'aawp');

    } elseif (strpos($error_message, 'InvalidClientTokenId') !== false) {
        $text .= __('The provided API key does not exist.', 'aawp');

    } elseif (strpos($error_message, 'SignatureDoesNotMatch') !== false) {
        $text .= __('The provided API secret is not valid for the given API key.', 'aawp');

    } elseif (strpos($error_message, 'AWS.InvalidAccount') !== false) {
        $text .= __('Your API key is not registered for the Amazon Product Advertising API.', 'aawp');

    } elseif (strpos($error_message, 'AWS.InvalidAssociate') !== false) {
        $text .= __('The provided API key is either not registered as an Amazon Associate or for using the Amazon Product Advertising API.', 'aawp');

    } elseif (strpos($error_message, 'RequestThrottled') !== false) {
        $text .= sprintf( wp_kses( __( 'Your Amazon Affiliate Associates account does not yet have access to the Amazon API. <a href="%s" target="_blank">Click here to find out how to solve the problem</a>', 'aawp' ), array(  'a' => array( 'href' => array(), 'target' => array( '_blank' ) ) ) ), esc_url( aawp_get_page_url( 'docs:api_requestthrottled' ) ) );

    } else {
        $text .= __('Something went wrong. Please check your API keys', 'aawp');
    }
    */

    // AccessDenied
    if ( 'AccessDenied' === $error_code ) {
        $text = __( 'The Access Key is not enabled for accessing the Product Advertising API in general or this specific version only. Please migrate your credentials.', 'aawp' );

    // AccessDeniedAwsUsers
    } elseif ( 'AccessDeniedAwsUsers' === $error_code ) {
        $text = __( 'The Access Key is not enabled for accessing the Product Advertising API in general or this specific version only. Please migrate your credentials.', 'aawp' );

    // InvalidAssociate
    } elseif ( 'InvalidAssociate' === $error_code ) {
        $text = __( 'Your access key is not mapped to primary of approved associate store. Please visit associate central.', 'aawp' );

    // IncompleteSignature
    } elseif ( 'IncompleteSignature' === $error_code ) {
        $text = __( 'The request signature did not include all of the required components.', 'aawp' );

    // InvalidPartnerTag
    } elseif ( 'InvalidPartnerTag' === $error_code ) {
        $text = __( 'The partner tag is not mapped to a valid associate store with your access key. Please visit associates central.', 'aawp' );

    // InvalidSignature
    } elseif ( 'InvalidSignature' === $error_code ) {
        $text = __( 'The request has not been correctly signed.', 'aawp' );

    // TooManyRequests
    } elseif ( 'TooManyRequests' === $error_code ) {
        $text = __( 'The request was denied due to request throttling. Please verify the number of requests made per second to the Amazon Product Advertising API.', 'aawp' );

    // RequestExpired
    } elseif ( 'RequestExpired' === $error_code ) {
        $text = __( 'The request is past expiry date or the request date (either with 15 minute padding), or the request date occurs more than 15 minutes in the future.', 'aawp' );

    // InvalidParameterValue
    } elseif ( 'InvalidParameterValue' === $error_code ) {
        $text = __( 'Some of the input parameter relating to request are invalid or missing.', 'aawp' );

    // MissingParameter
    } elseif ( 'MissingParameter' === $error_code ) {
        $text = __( 'Some of the input parameter relating to request are invalid or missing.', 'aawp' );

    // UnknownOperation
    } elseif ( 'UnknownOperation' === $error_code ) {
        $text = __( 'The operation requested is invalid. Please verify that the operation name is typed correctly.', 'aawp' );

    // UnrecognizedClient
    } elseif ( 'UnrecognizedClient' === $error_code ) {
        $text = __( 'The Access Key or security token included in the request is invalid.', 'aawp' );

    // Unknown error
    //} elseif ( ! empty( $error_code ) ) {
      //  $text = sprintf( esc_html__( 'Unknown error: %s', 'aawp' ), $error_code );

    // Undefined error
    } else {
        $text = __( 'Undefined error.', 'aawp' );
    }

    return $text;
}

/**
 * Maybe re-Verify stored API credentials
 */
function aawp_maybe_verify_stored_api_credentials() {

    $options_api = get_option( 'aawp_api', array() );

    // Skip if API connection already established
    if ( isset( $options_api['status'] ) && $options_api['status'] == '1' )
        return;

    // Skip if there is no error set
    if ( empty( $options_api['error'] ) )
        return;

    // Only proceed when all credentials were entered
    if ( ! empty( $options_api['country'] ) && ! empty( $options_api['key'] ) && ! empty( $options_api['secret'] ) && ! empty( $options_api['associate_tag'] ) ) {

        // Setup AAWP
        $amazon = new AAWP_API();
        $amazon->set_credentials( $options_api['country'], $options_api['key'], $options_api['secret'], $options_api['associate_tag'] );

        if ( $amazon->is_verified() ) {
            $options_api['status'] = 1;
            $options_api['error'] = '';

            // Update options
            update_option( 'aawp_api', $options_api );
        }
    }
}

/**
 * Format price currency
 *  *
 * @param $price
 *
 * @return null/string
 */
function aawp_format_price_currency( $price ) {

    if ( ! $price || is_null( $price ) )
        return null;

    $options = aawp_get_options();

    $country = ( isset( $options['api']['country'] ) ) ? $options['api']['country'] : false;

    if ( ! $country )
        return $price;

    $number_format = true;

    // Defaults
    $euro_countries = aawp_get_amazon_euro_countries();

    // 'de', 'com', 'co.uk', 'ca', 'fr', 'co.jp', 'it', 'cn', 'es', 'in', 'com.br'

    // Add currency
    $prefix = false;
    $suffix = false;

    // Currency codes: http://www.xe.com/iso4217.php
    if ( in_array( $country, $euro_countries ) ) {
        $currency_format = ( ! empty ( $options['output']['pricing_currency_format'] ) ) ? $options['output']['pricing_currency_format'] : 'EUR';
        $suffix = ' ' . $currency_format;
    } elseif ( 'com' === $country ) {
        $prefix = aawp_get_currency_symbol( 'USD' );
    } elseif ( 'co.uk' === $country ) {
        $prefix = aawp_get_currency_symbol( 'GBP' );
    } elseif ( 'ca' === $country ) {
        $prefix = 'CDN' . aawp_get_currency_symbol( 'CAD' ) . ' ';
    } elseif ( 'co.jp' === $country ) {
        $prefix = aawp_get_currency_symbol( 'JPY' );
    } elseif ( 'cn' === $country ) {
        $prefix = aawp_get_currency_symbol( 'CNY' );
    } elseif ( 'in' === $country ) {
        $prefix = aawp_get_currency_symbol( 'INR' ) . ' ';
    } elseif ( 'com.br' === $country ) {
        $prefix = aawp_get_currency_symbol( 'BRL' ) . ' ';
    } elseif ( 'com.mx' === $country ) {
        $prefix = aawp_get_currency_symbol( 'MXN' ) . ' ';
    } elseif ( 'com.au' === $country ) {
	    $prefix = aawp_get_currency_symbol( 'AUD' ) . ' ';
    } elseif ( 'com.tr' === $country ) {
        $suffix = ' TL'; //aawp_get_currency_symbol( 'TRY' ) . ' ';
    } elseif ( 'ae' === $country ) {
        $suffix = 'AED '; //aawp_get_currency_symbol( 'AED' ) . ' ';
    } elseif ( 'sg' === $country ) {
        $suffix = 'S$ '; //aawp_get_currency_symbol( 'SGD' ) . ' ';
    } elseif ( 'se' === $country ) {
        $suffix = ' kr';
    } elseif ( 'sa' === $country ) {
        $prefix = aawp_get_currency_symbol('SAR') . ' ';
    }

    // Number separators
    $number_dec     = 2;
    $number_sep_th  = ',';
    $number_sep_dec = '.';

    if ( in_array( $country, $euro_countries ) || 'com.br' === $country || 'se' === $country ) {
        $number_sep_th  = '.';
        $number_sep_dec = ',';
    }

    if ( 'fr' == $country ) {
        $number_sep_th = ' ';
    }

    if ( 'co.jp' == $country ) {
        //$number_format = false;
        $number_dec = 0;
    }

    if ( 'in' == $country ) {
        $number_dec = 0;
    }

    // Add separator
    $price = ( $number_format ) ? number_format( $price, $number_dec, $number_sep_dec, $number_sep_th ) : $price; // Previously ( $price / 100 )

    // Add prefix or suffix
    if ( $prefix ) {
        $price = $prefix . $price;
    }

    if ( $suffix ) {
        $price = $price . $suffix;
    }

    return $price;
}

/**
 * Get Amazon euro countries
 *
 * @return array
 */
function aawp_get_amazon_euro_countries() {
    return array('de', 'fr', 'it', 'es', 'nl');
}

/**
 * Get button html
 *
 * @param array $args
 *
 * @return string
 */
function aawp_get_button_html( $args = array() ) {

    $defaults = array(
        'classes' => 'aawp-button',
        'url' => '',
        'target' => '',
        'title' => '',
        'text' => '',
        'rel' => '',
        'attributes' => ''
    );

    $args = wp_parse_args( $args, $defaults );

    if ( empty( $args['url'] ) || empty( $args['text'] ) )
        return '';

    $html = '<a';

    if ( ! empty( $args['classes'] ) )
        $html .= ' class="' . esc_html( $args['classes'] ) . '"';

    $html .= ' href="' . esc_url( $args['url'] ) . '"';

    if ( ! empty( $args['title'] ) )
        $html .= ' title="' . strip_tags( $args['title'] ) . '"';

    if ( ! empty( $args['target'] ) )
        $html .= ' target="' . strip_tags( $args['target'] ) . '"';

    if ( ! empty( $args['rel'] ) )
        $html .= ' rel="' . strip_tags( $args['rel'] ) . '"';

    if ( ! empty( $args['attributes'] ) )
        $html .= ' ' . strip_tags( ltrim( $args['attributes'] ) );

    $html .= '>';

    $html .= $args['text'];

    $html .= '</a>';

    return $html;
}

/**
 * Get default tracking id
 *
 * @return mixed|null
 */
function aawp_get_default_country() {

    $options_api = aawp_get_options( 'api' );

    return ( ! empty( $options_api['country'] ) ) ? $options_api['country'] : null;
}

/**
 * Get default tracking id
 *
 * @return mixed|null
 */
function aawp_get_default_tracking_id() {

    $options_api = aawp_get_options( 'api' );

    return ( ! empty( $options_api['associate_tag'] ) ) ? $options_api['associate_tag'] : null;
}

/**
 * Get affiliate links type
 *
 * @return mixed|string
 */
function aawp_get_affiliate_links_type() {

    $general_options = aawp_get_options( 'general' );

    return ( ! empty( $general_options['affiliate_links'] ) ) ? $general_options['affiliate_links'] : 'standard';
}

/**
 * Generate shortened affiliate link (based on ASIN)
 *
 * @param $asin
 * @param string $type
 *
 * @return null|string
 */
function aawp_generate_shortened_affiliate_link( $asin, $type = 'basic' ) {

    if ( empty( $asin ) )
        return null;

    $api_options = aawp_get_options( 'api' );

    if ( empty( $api_options['country'] ) )
        return null;

    $store = $api_options['country'];

    // Cart
    if ( 'cart' === $type ) {
        $url = 'https://www.amazon.' . $store . '/gp/aws/cart/add.html?ASIN.1=' . $asin . '&Quantity.1=1';
        // https://www.amazon.de/gp/aws/cart/add.html?AssociateTag=Associate+Tag&ASIN.1=B01B53NG1K&Quantity.1=1

        // Wishlist
    } elseif ( 'wishlist' === $type  ) {
        $url = 'https://www.amazon.' . $store . '/gp/registry/wishlist/add-item.html?asin.0=' . $asin;

        // Recommend
    } elseif ( 'recommend' === $type  ) {
        $url = 'https://www.amazon.' . $store . '/gp/pdp/taf/' . $asin . '/';

        // Reviews
    } elseif ( 'reviews' === $type  ) {
        $url = 'https://www.amazon.' . $store . '/review/product/' . $asin . '/';

        // Offers
    } elseif ( 'offers' === $type  ) {
        $url = 'https://www.amazon.' . $store . '/gp/offer-listing/' . $asin . '/';

        // Default
    } else {
        $url = ( $store === 'com' ) ? 'https://amzn.com/' . $asin . '/' : 'https://www.amazon.' . $store . '/dp/' . $asin . '/';
    }

    // Add tracking ID placeholder
    //$url = add_query_arg( 'tag', AAWP_PLACEHOLDER_TRACKING_ID, $url ); // Temporarily removed because dot is being replaced with underscore.
    $url .= ( strpos( $url, '?' ) !== false ) ? '&tag=' . AAWP_PLACEHOLDER_TRACKING_ID : '?tag=' . AAWP_PLACEHOLDER_TRACKING_ID;

    return $url;
}

/**
 * Cleanup old "list" and "product" posts
 */
function aawp_cleanup_old_posts() {

    //== Collect products
    $args = array(
        'posts_per_page' => 100,
        'post_type' => 'aawp_product',
        'post_status' => 'any',
        'orderby' => 'date',
        'order' => 'ASC',
        'date_query' => array(
            'column' => 'post_date_gmt',
            'before' => '-7 days'
        )
    );

    $products = get_posts( $args );

    //== Delete products
    if ( is_array( $products ) && sizeof( $products ) > 0 ) {
        foreach ( $products as $post ) {
            if ( isset( $post->ID ) ) {
                wp_delete_post( $post->ID, true );
            }
        }
    }

    //== Collect lists
    $args = array(
        'posts_per_page' => 25,
        'post_type' => 'aawp_list',
        'post_status' => 'any',
        'orderby' => 'date',
        'order' => 'ASC',
        'date_query' => array(
            'column' => 'post_date_gmt',
            'before' => '-7 days'
        )
    );

    $lists = get_posts( $args );

    //== Delete lists
    if ( is_array( $lists ) && sizeof( $lists ) > 0 ) {

        foreach ( $lists as $post ) {
            if ( isset( $post->ID ) ) {
                wp_delete_post( $post->ID, true );
            }
        }
    }
}

/**
 * Delete downloaded images
 */
function aawp_delete_product_images_cache() {

	$dir = aawp_get_product_local_images_path();
	$di = new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS );
	$ri = new RecursiveIteratorIterator( $di, RecursiveIteratorIterator::CHILD_FIRST );

	foreach ( $ri as $file ) {
		$file->isDir() ? rmdir( $file ) : unlink( $file );
	}

	aawp_add_log( '*** DOWNLOADED IMAGES DELETED ***' );

	return true;
}

/**
 * Database garbage collection
 *
 * - Delete lists older than 30 days from database
 * - Delete products older than 30 days from database
 *
 * @since: 3.13
 */
function aawp_execute_database_garbage_collection() {

    if ( ! empty ( aawp_get_option( 'disable_database_garbage_collection', 'general' ) ) )
        return;

     $lists_args = array(
        'number' => 10,
        'orderby' => 'date_created',
        'order' => 'ASC',
        'fields' => 'id, date_created, date_updated'
    );

    $lists = aawp()->lists->get_lists( $lists_args, false );
    //aawp_debug( $lists, '$lists' );

    foreach ( $lists as $list ) {

        if ( ! empty ( $list->id ) && ! empty ( $list->date_created ) && strtotime( $list->date_created ) < strtotime('-30 days') ) {
            //echo 'List ID #' . $list->id . ' will be deleted!<br>';
            $deleted = aawp()->lists->delete( $list->id );
        }
    }

    $products_args = array(
        'number' => 50,
        'orderby' => 'date_created',
        'order' => 'ASC',
        'fields' => 'id, date_created, date_updated'
    );

    $products = aawp()->products->get_products( $products_args, false );
    //aawp_debug( $products, '$products' );

    foreach ( $products as $product ) {

        if ( ! empty ( $product->id ) && ! empty ( $product->date_created ) && strtotime( $product->date_created ) < strtotime('-30 days') ) {
            //echo 'Product ID #' . $product->id . ' will be deleted!<br>';
            $deleted = aawp()->products->delete( $product->id );
        }
    }
}