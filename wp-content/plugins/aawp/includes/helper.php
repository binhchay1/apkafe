<?php
/**
 * Helper
 *
 * @package     AAWP\Helper
 * @since       3.2.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Format datetime
 */
function aawp_datetime( $timestamp ) {

    if ( ! is_numeric( $timestamp ) )
        return null;

    $date_format = get_option( 'date_format', 'm/d/Y' );
    $time_format = get_option( 'time_format', 'g:i:s A' );

    return get_date_from_gmt( date( 'Y-m-d H:i:s', $timestamp ), $date_format . ' - ' . $time_format );
}

/*
 * Format date
 */
function aawp_date( $timestamp, $lang = false ) {

    if ( ! is_numeric( $timestamp ) )
        $timestamp = strtotime( $timestamp );

    //aawp_debug_log( '$lang >> ' . $lang . ' - aawp_is_lang_de() >> ' . aawp_is_lang_de() );

    $format = ( 'de' === $lang || ( ! $lang && aawp_is_lang_de() ) ) ? 'd.m.Y' : 'm/d/Y';

    $date = date( $format, $timestamp );

    return $date;
}

/*
 * Flag icon
 */
function aawp_the_icon_flag( $country ) {

    $country = str_replace( array( 'co.', 'com.', 'com' ), array( '', '', 'us' ), $country);

    echo '<span class="aawp-icon-flag aawp-icon-flag--' . $country . '"></span>';
}

function aawp_truncate_string( $string, $limit = 200, $pad = '...' ) {

    if ( strlen( $string ) > $limit )
        $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $limit + 1)) . $pad;

    return $string;
}

/*
 * Get function name
 */
function aawp_cleanup_function_name( $file, $path ) {
    return str_replace( array( $path, 'class.', '.php' ), '', $file);
}

/**
 * Highlight colors
 *
 * @return string
 */
function aawp_get_default_highlight_bg_color() {
    return '#256aaf';
}

function aawp_get_default_highlight_color() {
    return '#fff';
}

/**
 * Output data to a log for debugging reasons
 **/
function aawp_add_log( $string ) {

    $support_options = aawp_get_options( 'support' );

    $debug_log = ( isset ( $support_options['debug_log'] ) && $support_options['debug_log'] == '1' ) ? true : false;

    if ( $debug_log ) {

        $datetime = get_date_from_gmt( date( 'Y-m-d H:i:s', time() ), 'd.m.Y H:i:s' );
        $string = $datetime . " >>> " . $string . "\n";
        //$string = date( 'd.m.Y H:i:s' ) . " >>> " . $string . "\n";

        $log = aawp_get_log();
        $log .= $string;
        aawp_update_log( $log );
    }
}

function aawp_get_log() {
    return get_option( 'aawp_log', '' );
}

function aawp_update_log( $log ) {
    update_option( 'aawp_log', $log );
}

function aawp_delete_log() {
    delete_option( 'aawp_log' );
}

/*
 * Check lang
 */
function aawp_is_lang_de() {
    return ( strpos( get_bloginfo('language') ,'de-') !== false ) ? true : false;
}

/*
 * Check user rights
 */
function aawp_is_user_admin() {
    return ( current_user_can('manage_options' ) ) ? true : false;
}

function aawp_is_user_editor() {
    return ( current_user_can('edit_pages' ) ) ? true : false;
}

/*
 * The assets
 */
function aawp_the_assets() {
    echo apply_filters( 'aawp_assets_url', AAWP_PLUGIN_URL . 'assets/' );
}

function aawp_get_assets_url() {
    return apply_filters( 'aawp_assets_url', AAWP_PLUGIN_URL . 'assets/' );
}

function aawp_get_public_url() {
	return apply_filters( 'aawp_public_url', AAWP_PLUGIN_URL . 'public/' );
}

function aawp_get_db_datetime() {
    return date( 'Y-m-d H:i:s' );
}

/**
 * Lightens/darkens a given colour (hex format), returning the altered colour in hex format.7
 * @param str $hex Colour as hexadecimal (with or without hash);
 * @percent float $percent Decimal ( 0.2 = lighten by 20%(), -0.4 = darken by 40%() )
 * @return str Lightened/Darkend colour as hexadecimal (with hash);
 */
function aawp_color_luminance( $hex, $percent ) {

    // validate hex string

    $hex = preg_replace( '/[^0-9a-f]/i', '', $hex );
    $new_hex = '#';

    if ( strlen( $hex ) < 6 ) {
        $hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
    }

    // convert to decimal and change luminosity
    for ($i = 0; $i < 3; $i++) {
        $dec = hexdec( substr( $hex, $i*2, 2 ) );
        $dec = min( max( 0, $dec + $dec * $percent ), 255 );
        $new_hex .= str_pad( dechex( $dec ) , 2, 0, STR_PAD_LEFT );
    }

    return $new_hex;
}

/**
 * Convert hexdec color string to rgb(a) string
 *
 * @param $color
 * @param bool $opacity
 *
 * @return string
 */
function aawp_color_hex2rgba($color, $opacity = false) {

    $default = 'rgb(0,0,0)';

    //Return default if no color provided
    if(empty($color))
        return $default;

    //Sanitize $color if "#" is provided
    if ($color[0] == '#' ) {
        $color = substr( $color, 1 );
    }

    //Check if color has 6 or 3 characters and get values
    if (strlen($color) == 6) {
        $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
    } elseif ( strlen( $color ) == 3 ) {
        $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
    } else {
        return $default;
    }

    //Convert hexadec to rgb
    $rgb =  array_map('hexdec', $hex);

    //Check if opacity is set(rgba or rgb)
    if($opacity){
        if(abs($opacity) > 1)
            $opacity = 1.0;
        $output = 'rgba('.implode(",",$rgb).','.$opacity.')';
    } else {
        $output = 'rgb('.implode(",",$rgb).')';
    }

    //Return rgb(a) color string
    return $output;
}

/**
 * Get currency symbol from currency code
 *
 * @param $code
 *
 * @return mixed|string
 */
function aawp_get_currency_symbol( $code ) {

    // SRC: https://gist.github.com/Gibbs/3920259

    $currency_symbols = array(
        'AED' => '&#1583;.&#1573;', // ?
        'AFN' => '&#65;&#102;',
        'ALL' => '&#76;&#101;&#107;',
        'AMD' => '',
        'ANG' => '&#402;',
        'AOA' => '&#75;&#122;', // ?
        'ARS' => '&#36;',
        'AUD' => '&#36;',
        'AWG' => '&#402;',
        'AZN' => '&#1084;&#1072;&#1085;',
        'BAM' => '&#75;&#77;',
        'BBD' => '&#36;',
        'BDT' => '&#2547;', // ?
        'BGN' => '&#1083;&#1074;',
        'BHD' => '.&#1583;.&#1576;', // ?
        'BIF' => '&#70;&#66;&#117;', // ?
        'BMD' => '&#36;',
        'BND' => '&#36;',
        'BOB' => '&#36;&#98;',
        'BRL' => '&#82;&#36;',
        'BSD' => '&#36;',
        'BTN' => '&#78;&#117;&#46;', // ?
        'BWP' => '&#80;',
        'BYR' => '&#112;&#46;',
        'BZD' => '&#66;&#90;&#36;',
        'CAD' => '&#36;',
        'CDF' => '&#70;&#67;',
        'CHF' => '&#67;&#72;&#70;',
        'CLF' => '', // ?
        'CLP' => '&#36;',
        'CNY' => '&#165;',
        'COP' => '&#36;',
        'CRC' => '&#8353;',
        'CUP' => '&#8396;',
        'CVE' => '&#36;', // ?
        'CZK' => '&#75;&#269;',
        'DJF' => '&#70;&#100;&#106;', // ?
        'DKK' => '&#107;&#114;',
        'DOP' => '&#82;&#68;&#36;',
        'DZD' => '&#1583;&#1580;', // ?
        'EGP' => '&#163;',
        'ETB' => '&#66;&#114;',
        'EUR' => '&#8364;',
        'FJD' => '&#36;',
        'FKP' => '&#163;',
        'GBP' => '&#163;',
        'GEL' => '&#4314;', // ?
        'GHS' => '&#162;',
        'GIP' => '&#163;',
        'GMD' => '&#68;', // ?
        'GNF' => '&#70;&#71;', // ?
        'GTQ' => '&#81;',
        'GYD' => '&#36;',
        'HKD' => '&#36;',
        'HNL' => '&#76;',
        'HRK' => '&#107;&#110;',
        'HTG' => '&#71;', // ?
        'HUF' => '&#70;&#116;',
        'IDR' => '&#82;&#112;',
        'ILS' => '&#8362;',
        'INR' => '&#8377;',
        'IQD' => '&#1593;.&#1583;', // ?
        'IRR' => '&#65020;',
        'ISK' => '&#107;&#114;',
        'JEP' => '&#163;',
        'JMD' => '&#74;&#36;',
        'JOD' => '&#74;&#68;', // ?
        'JPY' => '&#165;',
        'KES' => '&#75;&#83;&#104;', // ?
        'KGS' => '&#1083;&#1074;',
        'KHR' => '&#6107;',
        'KMF' => '&#67;&#70;', // ?
        'KPW' => '&#8361;',
        'KRW' => '&#8361;',
        'KWD' => '&#1583;.&#1603;', // ?
        'KYD' => '&#36;',
        'KZT' => '&#1083;&#1074;',
        'LAK' => '&#8365;',
        'LBP' => '&#163;',
        'LKR' => '&#8360;',
        'LRD' => '&#36;',
        'LSL' => '&#76;', // ?
        'LTL' => '&#76;&#116;',
        'LVL' => '&#76;&#115;',
        'LYD' => '&#1604;.&#1583;', // ?
        'MAD' => '&#1583;.&#1605;.', //?
        'MDL' => '&#76;',
        'MGA' => '&#65;&#114;', // ?
        'MKD' => '&#1076;&#1077;&#1085;',
        'MMK' => '&#75;',
        'MNT' => '&#8366;',
        'MOP' => '&#77;&#79;&#80;&#36;', // ?
        'MRO' => '&#85;&#77;', // ?
        'MUR' => '&#8360;', // ?
        'MVR' => '.&#1923;', // ?
        'MWK' => '&#77;&#75;',
        'MXN' => '&#36;',
        'MYR' => '&#82;&#77;',
        'MZN' => '&#77;&#84;',
        'NAD' => '&#36;',
        'NGN' => '&#8358;',
        'NIO' => '&#67;&#36;',
        'NOK' => '&#107;&#114;',
        'NPR' => '&#8360;',
        'NZD' => '&#36;',
        'OMR' => '&#65020;',
        'PAB' => '&#66;&#47;&#46;',
        'PEN' => '&#83;&#47;&#46;',
        'PGK' => '&#75;', // ?
        'PHP' => '&#8369;',
        'PKR' => '&#8360;',
        'PLN' => '&#122;&#322;',
        'PYG' => '&#71;&#115;',
        'QAR' => '&#65020;',
        'RON' => '&#108;&#101;&#105;',
        'RSD' => '&#1044;&#1080;&#1085;&#46;',
        'RUB' => '&#1088;&#1091;&#1073;',
        'RWF' => '&#1585;.&#1587;',
        'SAR' => '&#65020;',
        'SBD' => '&#36;',
        'SCR' => '&#8360;',
        'SDG' => '&#163;', // ?
        'SEK' => '&#107;&#114;',
        'SGD' => '&#36;',
        'SHP' => '&#163;',
        'SLL' => '&#76;&#101;', // ?
        'SOS' => '&#83;',
        'SRD' => '&#36;',
        'STD' => '&#68;&#98;', // ?
        'SVC' => '&#36;',
        'SYP' => '&#163;',
        'SZL' => '&#76;', // ?
        'THB' => '&#3647;',
        'TJS' => '&#84;&#74;&#83;', // ? TJS (guess)
        'TMT' => '&#109;',
        'TND' => '&#1583;.&#1578;',
        'TOP' => '&#84;&#36;',
        'TRY' => '&#8356;', // New Turkey Lira (old symbol used)
        'TTD' => '&#36;',
        'TWD' => '&#78;&#84;&#36;',
        'TZS' => '',
        'UAH' => '&#8372;',
        'UGX' => '&#85;&#83;&#104;',
        'USD' => '&#36;',
        'UYU' => '&#36;&#85;',
        'UZS' => '&#1083;&#1074;',
        'VEF' => '&#66;&#115;',
        'VND' => '&#8363;',
        'VUV' => '&#86;&#84;',
        'WST' => '&#87;&#83;&#36;',
        'XAF' => '&#70;&#67;&#70;&#65;',
        'XCD' => '&#36;',
        'XDR' => '',
        'XOF' => '',
        'XPF' => '&#70;',
        'YER' => '&#65020;',
        'ZAR' => '&#82;',
        'ZMK' => '&#90;&#75;', // ?
        'ZWL' => '&#90;&#36;',
    );

    return ( ! empty ( $currency_symbols[$code] ) ) ? $currency_symbols[$code] : '';
}

/**
 * Strip Emojis
 *
 * From text string or array items
 *
 * @param string $text
 * @return string
 */
function aawp_strip_emojis( $text ) {

    if ( empty( $text ) || ! is_string( $text ) )
        return $text;

    //aawp_debug( $text, 'aawp_strip_emojis() >> BEFORE' );

    // Source: https://stackoverflow.com/a/20208095/3379704
    $pattern = '/[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0077}\x{E006C}\x{E0073}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0073}\x{E0063}\x{E0074}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0065}\x{E006E}\x{E0067}\x{E007F})|[\x{1F3F4}](?:\x{200D}\x{2620}\x{FE0F})|[\x{1F3F3}](?:\x{FE0F}\x{200D}\x{1F308})|[\x{0023}\x{002A}\x{0030}\x{0031}\x{0032}\x{0033}\x{0034}\x{0035}\x{0036}\x{0037}\x{0038}\x{0039}](?:\x{FE0F}\x{20E3})|[\x{1F415}](?:\x{200D}\x{1F9BA})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F468})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F468})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9BD})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9AF})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2640}\x{FE0F})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2642}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2695}\x{FE0F})|[\x{1F471}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F9CF}\x{1F647}\x{1F926}\x{1F937}\x{1F46E}\x{1F482}\x{1F477}\x{1F473}\x{1F9B8}\x{1F9B9}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F486}\x{1F487}\x{1F6B6}\x{1F9CD}\x{1F9CE}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}\x{1F9D8}](?:\x{200D}\x{2640}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B0})|[\x{1F471}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F9CF}\x{1F647}\x{1F926}\x{1F937}\x{1F46E}\x{1F482}\x{1F477}\x{1F473}\x{1F9B8}\x{1F9B9}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F486}\x{1F487}\x{1F6B6}\x{1F9CD}\x{1F9CE}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}\x{1F9D8}](?:\x{200D}\x{2642}\x{FE0F})|[\x{1F441}](?:\x{FE0F}\x{200D}\x{1F5E8}\x{FE0F})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FA}](?:\x{1F1FF})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1FA}](?:\x{1F1FE})|[\x{1F1E6}\x{1F1E8}\x{1F1F2}\x{1F1F8}](?:\x{1F1FD})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F9}\x{1F1FF}](?:\x{1F1FC})|[\x{1F1E7}\x{1F1E8}\x{1F1F1}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1FB})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1FB}](?:\x{1F1FA})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FE}](?:\x{1F1F9})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FA}\x{1F1FC}](?:\x{1F1F8})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F7})|[\x{1F1E6}\x{1F1E7}\x{1F1EC}\x{1F1EE}\x{1F1F2}](?:\x{1F1F6})|[\x{1F1E8}\x{1F1EC}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}](?:\x{1F1F5})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EE}\x{1F1EF}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1F8}\x{1F1F9}](?:\x{1F1F4})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1F3})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F4}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FF}](?:\x{1F1F2})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F1})|[\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FD}](?:\x{1F1F0})|[\x{1F1E7}\x{1F1E9}\x{1F1EB}\x{1F1F8}\x{1F1F9}](?:\x{1F1EF})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EB}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F3}\x{1F1F8}\x{1F1FB}](?:\x{1F1EE})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1ED})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1EC})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F9}\x{1F1FC}](?:\x{1F1EB})|[\x{1F1E6}\x{1F1E7}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FB}\x{1F1FE}](?:\x{1F1EA})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1E9})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FB}](?:\x{1F1E8})|[\x{1F1E7}\x{1F1EC}\x{1F1F1}\x{1F1F8}](?:\x{1F1E7})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F6}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}\x{1F1FF}](?:\x{1F1E6})|[\x{00A9}\x{00AE}\x{203C}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21A9}-\x{21AA}\x{231A}-\x{231B}\x{2328}\x{23CF}\x{23E9}-\x{23F3}\x{23F8}-\x{23FA}\x{24C2}\x{25AA}-\x{25AB}\x{25B6}\x{25C0}\x{25FB}-\x{25FE}\x{2600}-\x{2604}\x{260E}\x{2611}\x{2614}-\x{2615}\x{2618}\x{261D}\x{2620}\x{2622}-\x{2623}\x{2626}\x{262A}\x{262E}-\x{262F}\x{2638}-\x{263A}\x{2640}\x{2642}\x{2648}-\x{2653}\x{265F}-\x{2660}\x{2663}\x{2665}-\x{2666}\x{2668}\x{267B}\x{267E}-\x{267F}\x{2692}-\x{2697}\x{2699}\x{269B}-\x{269C}\x{26A0}-\x{26A1}\x{26AA}-\x{26AB}\x{26B0}-\x{26B1}\x{26BD}-\x{26BE}\x{26C4}-\x{26C5}\x{26C8}\x{26CE}-\x{26CF}\x{26D1}\x{26D3}-\x{26D4}\x{26E9}-\x{26EA}\x{26F0}-\x{26F5}\x{26F7}-\x{26FA}\x{26FD}\x{2702}\x{2705}\x{2708}-\x{270D}\x{270F}\x{2712}\x{2714}\x{2716}\x{271D}\x{2721}\x{2728}\x{2733}-\x{2734}\x{2744}\x{2747}\x{274C}\x{274E}\x{2753}-\x{2755}\x{2757}\x{2763}-\x{2764}\x{2795}-\x{2797}\x{27A1}\x{27B0}\x{27BF}\x{2934}-\x{2935}\x{2B05}-\x{2B07}\x{2B1B}-\x{2B1C}\x{2B50}\x{2B55}\x{3030}\x{303D}\x{3297}\x{3299}\x{1F004}\x{1F0CF}\x{1F170}-\x{1F171}\x{1F17E}-\x{1F17F}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F201}-\x{1F202}\x{1F21A}\x{1F22F}\x{1F232}-\x{1F23A}\x{1F250}-\x{1F251}\x{1F300}-\x{1F321}\x{1F324}-\x{1F393}\x{1F396}-\x{1F397}\x{1F399}-\x{1F39B}\x{1F39E}-\x{1F3F0}\x{1F3F3}-\x{1F3F5}\x{1F3F7}-\x{1F3FA}\x{1F400}-\x{1F4FD}\x{1F4FF}-\x{1F53D}\x{1F549}-\x{1F54E}\x{1F550}-\x{1F567}\x{1F56F}-\x{1F570}\x{1F573}-\x{1F57A}\x{1F587}\x{1F58A}-\x{1F58D}\x{1F590}\x{1F595}-\x{1F596}\x{1F5A4}-\x{1F5A5}\x{1F5A8}\x{1F5B1}-\x{1F5B2}\x{1F5BC}\x{1F5C2}-\x{1F5C4}\x{1F5D1}-\x{1F5D3}\x{1F5DC}-\x{1F5DE}\x{1F5E1}\x{1F5E3}\x{1F5E8}\x{1F5EF}\x{1F5F3}\x{1F5FA}-\x{1F64F}\x{1F680}-\x{1F6C5}\x{1F6CB}-\x{1F6D2}\x{1F6D5}\x{1F6E0}-\x{1F6E5}\x{1F6E9}\x{1F6EB}-\x{1F6EC}\x{1F6F0}\x{1F6F3}-\x{1F6FA}\x{1F7E0}-\x{1F7EB}\x{1F90D}-\x{1F93A}\x{1F93C}-\x{1F945}\x{1F947}-\x{1F971}\x{1F973}-\x{1F976}\x{1F97A}-\x{1F9A2}\x{1F9A5}-\x{1F9AA}\x{1F9AE}-\x{1F9CA}\x{1F9CD}-\x{1F9FF}\x{1FA70}-\x{1FA73}\x{1FA78}-\x{1FA7A}\x{1FA80}-\x{1FA82}\x{1FA90}-\x{1FA95}]/u';

    // Remove emojis.
    $text = preg_replace( $pattern, '', $text );

    // Maybe strip whitespaces ad the beginning and end of the string.
    $text = trim( $text );

    // Maybe fix appeared double spaces.
    $text = str_replace( '  ', ' ', $text );

    //aawp_debug( $text, 'aawp_strip_emojis() >> AFTER' );

    return $text;
}

/**
 * Strip text formatting
 *
 * @param $text
 * @return string|string[]|null
 */
function aawp_strip_text_formatting( $text ) {

    if ( empty( $text ) || ! is_string( $text ) )
        return $text;

    /*echo '<h3>aawp_strip_text_formatting() >> $text >> BEFORE:</h3>';
    var_dump( $text );*/

    // Remove HTML.
    $text = strip_tags( $text );

    // Remove Emojis.
    $text = aawp_strip_emojis( $text );

    // Remove unicode characters.
    $text = preg_replace( '/[^\x20-\x7E]/', '', $text );

    // Maybe strip whitespaces ad the beginning and end of the string.
    $text = trim( $text );

    /*echo '<h3>aawp_strip_text_formatting() >> $text >> AFTER:</h3>';
    var_dump( $text );*/

    return $text;
}

/**
 * Format bytes
 *
 * @param $bytes
 * @param int $precision
 * @return string
 */
function aawp_format_bytes( $bytes, $precision = 2 ) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    // Uncomment one of the following alternatives
    // $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Wrapper function for wp_die(). This function adds filters for wp_die() which
 * kills execution of the script using wp_die(). This allows us to then to work
 * with functions using edd_die() in the unit tests.
 *
 * @author Sunny Ratilal
 * @since 3.4
 * @return void
 */
function aawp_die( $message = '', $title = '', $status = 400 ) {
    add_filter( 'wp_die_ajax_handler', '_aawp_die_handler', 10, 3 );
    add_filter( 'wp_die_handler', '_aawp_die_handler', 10, 3 );
    wp_die( $message, $title, array( 'response' => $status ));
}

/*
 * Debug display
 */
function aawp_debug_display( $args, $title = null ) {

    if ( ! defined( 'AAWP_DEBUG_DISPLAY' ) || AAWP_DEBUG_DISPLAY != true )
        return;

    if ( ! empty ( $title ) )
        echo '<h3>' . $title . '</h3>';

    echo '<pre>';
    print_r( $args );
    echo '</pre>';
}

/**
 * Check if AAWP debugging is enabled
 *
 * @return bool
 */
function aawp_is_debug() {
    return ( defined( 'AAWP_DEBUG' ) && AAWP_DEBUG === true ) ? true : false;
}

/*
 * Debug
 */
function aawp_show_debug() {
    return ( ! defined( 'AAWP_DEBUG' ) || AAWP_DEBUG != true ) ? false : true;
}

function aawp_debug_pp_post_meta() {

    if ( ! aawp_show_debug() )
        return;

    global $post;
    ?>
    <p>
        <input type="button" class="button secondary" data-aawp-pp-debug-info-toggle="true"
               value="<?php _e( 'Show debug information', 'aawp' ); ?>" />
    </p>
    <div id="aawp-pp-debug-info" class="aawp-pp-debug-info">
        <?php $meta = get_post_meta( $post->ID ); echo '<pre>'; print_r( $meta ); echo '</pre>'; ?>
    </div>
    <?php
}

function aawp_debug( $args, $title = false ) {

    if ( defined( 'AAWP_DEBUG' ) && AAWP_DEBUG === true ) {

        if ($title) {
            echo '<h3>' . $title . '</h3>';
        }

        if ($args) {
            echo '<pre>';
            print_r($args);
            echo '</pre>';
        }
    }
}

function aawp_debug_log( $message ) {

    if ( defined( 'AAWP_DEBUG' ) && AAWP_DEBUG === true ) {
        if (is_array( $message ) || is_object( $message ) ) {
            error_log( print_r( $message, true ) );
        } else {
            error_log( $message );
        }
    }
}

add_shortcode('aawp_debug', function() {

    if ( ! defined( 'AAWP_DEBUG' ) || AAWP_DEBUG !== true )
        return null;

    ob_start();

    /*
    $args = array(
        'posts_per_page'   => -1,
        'post_type'        => 'aawp_product',
        'post_status'      => array('publish', 'published')
    );
    $posts_array = get_posts( $args );

    foreach ($posts_array as $post) {
        var_dump($post->ID);
    }
    */

    $AAWP_Functions = new AAWP_Functions();

    $amazon = $AAWP_Functions->get_api_connection( 'de' );

    /*
    foreach ( $items as $item ) {

        echo 'Product ASIN ' . $item['asin'] . ': ';

        $product_id = aawp_get_product_by_asin( $item['asin'] );

        if ( $product_id ) {
            echo 'EXISTS >> ' . $product_id . '<br>';
            aawp_update_product( $product_id, $item );
        } else {
            $product_id = aawp_create_product( $item );
            echo 'CREATED >> ' . $product_id . '<br>';
        }
    }
    */

    /*
    echo '<h3>Get list from API and create db entry</h3>';
    $list_args = array(
        'aawp_list_store' => 'de',
        'aawp_list_keys' => 'anker akku',
        'aawp_list_type' => 'bestseller',
        'aawp_list_max' => 20,
        'crawl_reviews' => 0
    );

    $list_items = $amazon->get_items( $list_args['aawp_list_keys'], $list_args['aawp_list_type'], $AAWP_Functions->get_api_args( array( 'crawl_reviews' => 0 ) ) );
    echo 'Items fetched: ' . sizeof( $list_items ) . '<br>';

    aawp_debug( $list_items );

    if ( is_array( $list_items ) && sizeof( $list_items ) > 0 ) {

        $list_id = aawp_get_list( $list_args );

        if ( $list_id ) {
            echo 'EXISTS >> ' . $list_id . '<br>';
            aawp_update_list( $list_id, $list_items );
        } else {
            $list_id = aawp_create_list( $list_args, $list_items );
            echo 'CREATED >> ' . $list_id . '<br>';
        }
    }
    */


    /*
    echo '<h3>Get list from db</h3>';
    $list_args = array(
        'aawp_list_store' => 'de',
        'aawp_list_keys' => 'persönlichkeitsentwicklung',
        'aawp_list_type' => 'bestseller',
        'aawp_list_max' => 16,
        'crawl_reviews' => 0
    );
    $list_id = aawp_get_list( $list_args );
    var_dump( $list_id );
    */

    /*
    echo '<h3>Get lists from db</h3>';
    $lists = aawp_get_lists();
    var_dump($lists);
    */

    /*
    $list_args = array(
        'list_type' => 'bestseller',
        'list_keys' => 'anker akku',
        'list_items' => 10
    );
    $list = aawp_get_list_from_api( $list_args );

    if ( is_array( $list ) ) {
        aawp_debug( $list );
    } else {
        var_dump( $list );
    }
    */

    // Renew list
    //aawp_renew_list( 1512 );

    // Get products
    /*
    $products_args = array(
        'posts_per_page' => -1,
        //'aawp_product_asin' => 'B00KLVFV34' // Single ASIN
        //'aawp_product_asin' => array( 'B00KLVFV34', 'B01N2JKMRO' ) // Multiple ASINs
        'aawp_is_valid' => true
    );

    $products = aawp_get_products( $products_args );

    echo '<h3>Results: ' . sizeof( $products ) . '</h3>';

    if ( is_array( $products ) ) {
        foreach ( $products as $product ) {
            echo $product['id'] . '<br>';
        }
    } else {
        var_dump($products);
    }
    */

    // Product exists by asin
    //$product_id = aawp_product_exists_by_asin( 'B00UBMO61G' );
    //var_dump($product_id);

    // Get product
    echo '<h3>Get product</h3>';
    //$product = aawp_get_product( 5 );
    $product = aawp_get_product_by_asin( 'B00Z0GAVGW' );
    aawp_debug( $product );

    // Renew product
    //$renewed = aawp_renew_product( 1530 );
    //var_dump($renewed);

    // Get product via API
    /*
    $product_args = array(
        //'product_asin' => 'B01N1FQC6N'
    );
    $product = aawp_get_product_from_api( 'B01N1FQC6N', $product_args );

    if ( is_array( $product ) ) {
        aawp_debug( $product );
    } else {
        var_dump( $product );
    }
    */

    /*
    // Get products via API
    $product_args = array(
        //'product_asin' => 'B01N1FQC6N'
        //'store' => 'com'
    );
    $product_asins = array( 'blub' ); // 'B01N1FQC6N', '386882233X', '3424631078' // Not via API: B001132AR6
    $products = aawp_get_products_from_api( $product_asins, $product_args );

    if ( is_array( $products ) ) {
        aawp_debug( $products );
    } else {
        var_dump( $products );
    }
    */

    // Count
    /*
    echo '<h3>Products count</h3>';
    $count = aawp_get_products_count();
    echo $count . ' products in database<br>';
    */

    $str = ob_get_clean();

    return $str;
});

add_shortcode('aawp_debug_apiv5', function() {

    if ( ! defined( 'AAWP_DEBUG' ) || AAWP_DEBUG !== true )
        return null;

    ob_start();

    echo '<h2>API v5!</h2>';

    /*
    $asins = array( 'B07PSKGKGF' );
    $product_args = array();

    $response = aawp()->api->get_items( $asins, 'single', $product_args );

    //var_dump( $response );
    aawp_debug( $response, 'api->get_items $response' );

    echo '<h3>Verify API credentials</h3>';
    $response = aawp()->api->verify_credentials( 'AKIAJQXUMUSO4OORSFPQ', 'x0Hv21OERtON5E80/CUnJ1lE/IEG5VgJFEBtPAXj', 'getaawp04-20', 'com' );
    var_dump( $response );
    */

    /*
    $asins = array( 'B07PSKGKGF' );
    $api_args = array( 'crawl_reviews' => 1 );
    $api_products = aawp()->api->get_products( $asins, $api_args );
    aawp_debug( $api_products, '$api_products' );
    */

    //var_dump( $api_products[0]->asin );

    // Create product in database
    /*
    $products = array();

    if ( ! empty( $api_products ) ) {

        foreach( $api_products as $api_product ) {


            $product_id = aawp_create_product( $api_product );

            if ( ! empty( $product_id ) )
                echo 'Product created in database. ID: ' . $product_id . '<br>';
                //$products[] = aawp_get_product( $product_id );
        }
    }

    aawp_debug( $products, '$products' );
    */

    // Update product in database
    /*
    if ( ! empty( $api_products[0] ) ) {
        $updated = aawp_update_product( 3, $api_products[0] );

        var_dump( $updated );
    }
    */

    // Get product from database
    //$product = aawp_get_product( 3 );
    //aawp_debug( $product, '$product from database' );

    /*
    // Get multiple products from Cache/API
    $asins = array( 'B075RQDJT1', 'B07PSKGKGF' );
    $AAWP_Functions = new AAWP_Functions();

    //$products = $AAWP_Functions->get_products_from_api( $asins );
    //aawp_debug( $products, '$AAWP_Functions->get_products_from_api()' );

    $products = $AAWP_Functions->get_products_from_cache( $asins );
    aawp_debug( $products, '$AAWP_Functions->get_products_from_cache()' );
    */

    /*
    $list_args = array(
        'type' => 'search',
        'keywords' => 'Harry Potter',
        'items_count' => 3
    );

    $response = aawp()->api->get_list( $list_args );

    //var_dump( $response );
    aawp_debug( $response, 'api->get_list $response' );

    if ( ! empty ( $response ) ) {
        $list_args = aawp_attach_product_asins_to_list_args( $list_args, $response );
        $list_id = aawp_create_list( $list_args );
        aawp_debug( $list_id, 'aawp_create_list() >> $list_id' );

        if ( $list_id ) {
            $list = aawp_get_list( $list_id );
            aawp_debug( $list, 'aawp_get_list() >> $list' );
        }
    }
    */

    $AAWP_Functions = new AAWP_Functions();

    $list_args = array(
        'type' => 'search',
        'keywords' => 'Harry Potter',
        'items_count' => 3
    );

    /*
    $list_item_ids = $AAWP_Functions->get_list_items_from_cache( $list_args );
    var_dump( $list_item_ids );
    aawp_debug( $list_item_ids, '$AAWP_Functions->get_list_items_from_cache() >> $list_item_ids' );
    */

    /*
    $list_item_ids = $AAWP_Functions->get_list_items_from_api( $list_args );
    var_dump( $list_item_ids );
    aawp_debug( $list_item_ids, '$AAWP_Functions->get_list_items_from_api() >> $list_item_ids' );
    */

    //$items = $AAWP_Functions->get_items( 'Harry Potter', 'search', true, array( 'items_count' => 3 ) );
    //aawp_debug( $items, '$AAWP_Functions->get_items() >> $items' );

    //echo '<h2>Cache Handler</h2>';
    //aawp_execute_renew_cache( true );

    /*
    $image_urls = array(
        'https://m.media-amazon.com/images/I/41LpeTcgs6L.jpg',
        'https://m.media-amazon.com/images/I/11gfhCwxtoL.jpg',
        'https://m.media-amazon.com/images/I/316ylrXF8-L.jpg',
        'https://m.media-amazon.com/images/I/21rcGVyiYZL.jpg',
        'https://m.media-amazon.com/images/I/21U29oMbTWL.jpg',
        'https://m.media-amazon.com/images/I/31Gn1kSyiDL._SL500_.jpg'
    );
    aawp_debug( $image_urls, 'aawp_get_product_image_ids_from_urls() >> $image_urls' );

    $image_ids = aawp_get_product_image_ids_from_urls( $image_urls );
    aawp_debug( $image_ids, 'aawp_get_product_image_ids_from_urls() >> $image_ids' );
    */

    //$image_url = aawp_build_product_image_url( '11gfhCwxtoL' );
    //var_dump( $image_url );

    /*
    $response = aawp()->api->get_items( array( 'B0011AQLZQ' ), 'single' );

    //var_dump( $response );
    aawp_debug( $response, 'api->get_items $response' );

    if ( isset( $response[0] ) ) {
        $product = aawp_setup_product_data_for_database( $response[0] );
        aawp_debug( $product, 'aawp_setup_product_data_for_database() >> $product' );
    }
    */

    // Crawl reviews
    /*
    $AAWP_Review_Crawler = new AAWP_Review_Crawler();
    $reviews_data = $AAWP_Review_Crawler->get_data( '1538544482' );
    aawp_debug( $reviews_data );

    var_dump( floatval( $reviews_data['rating'] ) );
    var_dump( $reviews_data['reviews'] );
    */

    aawp_execute_renew_rating_cache( true );

    $str = ob_get_clean();

    return $str;
});

add_shortcode('aawp_debug_widgets', function() {

    if ( ! defined( 'AAWP_DEBUG' ) || AAWP_DEBUG !== true )
        return null;

    ob_start();

    echo '<h2>AAWP Widgets</h2>';

    $widgets = wp_get_sidebars_widgets();
    aawp_debug( $widgets, '$widgets' );

    $widget_option = get_option( 'widget_aawp_widget_box' );
    aawp_debug( $widget_option, '$widget_option' );

    $str = ob_get_clean();

    return $str;
});

add_shortcode('aawp_debug_review_crawler', function() {

    if ( ! defined( 'AAWP_DEBUG' ) || AAWP_DEBUG !== true )
        return null;

    ob_start();

    echo '<h2>AAWP Review Crawler</h2>';

    $ReviewCrawler = new AAWP_Review_Crawler();

    $rating = $ReviewCrawler->get_data( 'B07PHPXHQS' );

    var_dump( $rating );

    $str = ob_get_clean();

    return $str;
});

add_shortcode('aawp_debug_api_items', function() {

    if ( ! defined( 'AAWP_DEBUG' ) || AAWP_DEBUG !== true )
        return null;

    ob_start();

    echo '<h2>Debug API Items</h2>';

    $asin = 'B006BLEZD0';

    /*
    $transient_key = 'aawp_debug_api_items_' . $asin;

    if ( empty ( $data = get_transient( $transient_key ) ) ) {

        $data = aawp()->api->get_product( $asin );

        if ( ! empty ( $data ) && is_object( $data ) )
            set_transient( $transient_key, $data, 99999999999999 );
    }*/

    //$data = aawp()->api->get_product( $asin );
    $data = aawp()->api->get_products( array( 'B07P14RM1R' ) );

    if ( ! empty ( $data[0] ) ) {

        aawp_debug( $data, '$data >> aawp_setup_product_data_for_database() >> BEFORE' );
        $data_prepared = aawp_setup_product_data_for_database( $data[0] );
        aawp_debug( $data_prepared, '$data >> aawp_setup_product_data_for_database() >> AFTER' );

        $product_id = aawp_create_product( $data[0] );
        var_dump( $product_id );


    } else {
        echo 'no data';
    }

    $str = ob_get_clean();

    return $str;
});


add_shortcode('aawp_debug_db_update', function() {

    if ( ! defined( 'AAWP_DEBUG' ) || AAWP_DEBUG !== true )
        return null;

    ob_start();

    echo '<h2>AAWP Alter DB Tables</h2>';

    global $wpdb;

    $db_products_name = $wpdb->prefix . 'aawp_products';
    $db_products_version = get_option( $db_products_name . '_db_version' );
    echo '$db_products_version: ' . $db_products_version . '<br>';

    $db_lists_name = $wpdb->prefix . 'aawp_lists';
    $db_lists_version = get_option( $db_lists_name . '_db_version' );
    echo '$db_lists_version: ' . $db_lists_version . '<br>';

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $db_products_updated = maybe_convert_table_to_utf8mb4( $db_products_name );
    var_dump( $db_products_updated );

    $db_lists_updated = maybe_convert_table_to_utf8mb4( $db_lists_name );
    var_dump( $db_lists_updated );

    $str = ob_get_clean();

    return $str;
});


add_shortcode('aawp_debug_apisdk', function() {

    if ( ! defined( 'AAWP_DEBUG' ) || AAWP_DEBUG !== true )
        return null;

    ob_start();

    echo '<h2>Debug API SDK</h2>';

    $asin = 'B01AWH05GE';

    $transient_key = 'aawp_debug_apisdk_' . $asin;

    if ( empty ( $data = get_transient( $transient_key ) ) ) {

        $data = aawp()->api->get_product( $asin );

        if ( ! empty ( $data ) && is_object( $data ) )
            set_transient( $transient_key, $data, 99999999999999 );
    }

    $data = aawp()->api->get_product( $asin );
    $data = ( ! empty ( $data ) && is_object( $data ) ) ? array( $data ) : $data;
    //$data = aawp()->api->get_products( array( 'B07P14RM1R' ) );

    if ( ! empty ( $data[0] ) ) {

        aawp_debug( $data, '$data >> aawp_setup_product_data_for_database() >> BEFORE' );
        $data_prepared = aawp_setup_product_data_for_database( $data[0] );
        aawp_debug( $data_prepared, '$data >> aawp_setup_product_data_for_database() >> AFTER' );

        $product_id = aawp_create_product( $data[0] );
        var_dump( $product_id );


    } else {
        echo 'no data';
    }

    $str = ob_get_clean();

    return $str;
});

add_shortcode('aawp_debug_garbage_collection', function() {

    if ( ! defined( 'AAWP_DEBUG' ) || AAWP_DEBUG !== true )
        return null;

    ob_start();

    echo '<h2>Garbage Collection</h2>';

    aawp_execute_database_garbage_collection();

    return ob_get_clean();
});