<?php

/*

Copyright 2014 Dario Curvino (email : d.curvino@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>
*/

if (!defined('ABSPATH')) {
    exit('You\'re not allowed to see this page');
} // Exit if accessed directly

/****** Translating YASR ******/
add_action('init', 'yasr_translate');

function yasr_translate() {
    load_plugin_textdomain('yet-another-stars-rating', false, YASR_LANG_DIR);
}

/**
 * Create a select menu to choose the rich snippet itemtype
 *
 * @param bool|string $html_id the id of the select name
 * @param bool|string $name
 * @param bool|string $default_option
 * @param bool|int    $term_id
 * @param bool        $disabled
 */

function yasr_select_itemtype($html_id=false, $name=false, $default_option=false, $term_id=false, $disabled=false) {
    if($html_id === false) {
        $html_id = 'yasr-choose-reviews-types-list';
    }

    if($name === false) {
        $name = 'yasr-review-type';
    }

    $itemtypes_array = YasrRichSnippetsItemTypes::returnItemTypes();
    sort($itemtypes_array);

    if($default_option === false) {
        $review_type_choosen = YasrDB::getItemType($term_id);
    } else {
        $review_type_choosen = $default_option;
    }

    $disabled_attribute = '';

    if($disabled === true) {
        $disabled_attribute = 'disabled';
    }
    ?>

    <label for="<?php echo esc_attr($html_id) ?>"></label>
    <select name="<?php echo esc_attr($name) ?>" id="<?php echo esc_attr($html_id) ?>">
        <?php
        foreach ($itemtypes_array as $itemType) {
            $itemType = trim($itemType);
            if ($itemType === $review_type_choosen) {
                echo '<option value="'.esc_attr($itemType).'" selected >
                          '.esc_html($itemType).'
                      </option>';
            } else {
                echo '<option value="'.esc_attr($itemType).'" '.esc_attr($disabled_attribute).'>
                        '.esc_html($itemType).'
                      </option>';
            }
        }
        ?>
    </select>

    <?php
} //End function yasr_select_itemtype()

/*** Function to set cookie
 * @since 0.8.3
 *
 * @param $cookiename //can come from a filter
 * @param $data_to_save
 */
function yasr_setcookie($cookiename, $data_to_save) {

    if (!$data_to_save || !$cookiename || !is_string($cookiename)) {
        exit('Error setting yasr cookie');
    }

    //sanitize the cookie name
    $cookiename = wp_strip_all_tags($cookiename);
    $domain = COOKIE_DOMAIN;

    //this is for multisite support
    if(defined('DOMAIN_CURRENT_SITE')) {
        $domain = DOMAIN_CURRENT_SITE;
    }

    $existing_data = array(); //avoid undefined index

    if (isset($_COOKIE[$cookiename])) {
        //setcookie add \ , so I need to stripslahes
        $existing_data = stripslashes($_COOKIE[$cookiename]);

        //By default, json_decode return an object, TRUE to return an array
        $existing_data = json_decode($existing_data, true);
    }

    //whetever exists or not, push into at the end of array
    $existing_data[] = $data_to_save;

    $encoded_data = json_encode($existing_data);
    $expire = time() + 31536000;

    if (PHP_VERSION_ID < 70300) {
        setcookie($cookiename, $encoded_data, $expire, COOKIEPATH . '; samesite=' . 'Lax', $domain, false);
        return;
    }
    setcookie($cookiename, $encoded_data, [
        'expires'  => $expire,
        'path'     => COOKIEPATH,
        'domain'   => $domain,
        'samesite' => 'Lax',
        'secure'   => false,
        'httponly' => false,
    ]);

}

/** Function to get ip, since version 0.8.8
 *
 **/

/**
 * Get the user Ip
 *
 * This code can be found on http://codex.wordpress.org/Plugin_API/Filter_Reference/pre_comment_user_ip
 *
 * @author Dario Curvino <@dudo>
 *
 * @since  0.8.8
 * @return array|mixed|string|string[]|null
 */
function yasr_get_ip() {
    $ip = null;
    $ip = apply_filters('yasr_filter_ip', $ip);

    if (isset($ip)) {
        return $ip;
    }
    $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];

    if (!empty($_SERVER['X_FORWARDED_FOR'])) {
        $X_FORWARDED_FOR = explode(',', $_SERVER['X_FORWARDED_FOR']);
        if (!empty($X_FORWARDED_FOR)) {
            $REMOTE_ADDR = trim($X_FORWARDED_FOR[0]);
        }
    }
    /*
    * Some php environments will use the $_SERVER['HTTP_X_FORWARDED_FOR']
    * variable to capture visitor address information.
    */
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $HTTP_X_FORWARDED_FOR = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        if (!empty($HTTP_X_FORWARDED_FOR)) {
            $REMOTE_ADDR = trim($HTTP_X_FORWARDED_FOR[0]);
        }
    }
    return preg_replace('/[^0-9a-f:., ]/i', '', $REMOTE_ADDR);
}

/**
 * Return ip
 *
 * @author Dario Curvino <@dudo>
 *
 * @since  3.3.7
 * @return array|mixed|string|string[]|null
 */
function yasr_ip_to_save () {

    // since 3.4.7 we always return the ip to prevent voting fraud
    return yasr_get_ip();

    /*
    if (YASR_ENABLE_IP === 'yes') {
        return yasr_get_ip();
    }

    return ('X.X.X.X');
    */

}


/*function to remove duplicate in an array for a specific key
Taken value: array to search, key
*/
function yasr_unique_multidim_array($array, $key) {
    $temp_array = array();
    $i          = 0;

    //this array will contain only indexes
    $key_array = array();

    foreach ($array as $val) {
        $result_search_array = array_search($val[$key], $key_array);

        $key_array[$i]  = $val[$key];
        $temp_array[$i] = $val;

        //if result is found
        if ($result_search_array !== false) {
            unset($key_array[$result_search_array], $temp_array[$result_search_array]);
        }
        $i ++;
    }
    sort($temp_array);
    return $temp_array;
}

/**
 * Return true if the requested url return 200, string with error otherwise
 *
 * @author Dario Curvino <@dudo>
 * @since refactor in 3.0.8
 * @param $url
 *
 * @return true|string
 */
function yasr_check_valid_url($url) {
    if (wp_http_validate_url($url) === false) {
        return __FUNCTION__ . '(): Given url is not valid';
    }

    $response = wp_remote_get($url);

    if(is_wp_error($response)) {
        return __FUNCTION__ . '(): error in wp_remote_get';
    }

    $status_code = wp_remote_retrieve_response_code($response);
    if($status_code === 200) {
        return true;
    }

    return __FUNCTION__ . '(): wrong status code: ' . $status_code;
}

/**
 * @author Dario Curvino <@dudo>
 * @since 2.9.3
 * @return bool
 */
function yasr_is_catch_infinite_sroll_installed () {
    if (is_plugin_active('catch-infinite-scroll/catch-infinite-scroll.php')) {
        return true;
    }
    return false;
}

/**
 * Wrapper function for wp_kses that adds allowed HTML
 *
 * it adds more fields to wp_kses_allowed_html('post')
 *
 * @author Dario Curvino <@dudo>
 * @since  3.0.6
 * @param $string
 *
 * @return string
 */
function yasr_kses($string) {
    //use this instead of wp_kses_allowed_html('post'), to avoid conflict with plugins that may filter the result of
    //wp_kses_allowed_html('post')
    global $allowedposttags;

    $allowed_html = array (
        'input' => array(
            'type'         => array(),
            'name'         => array(),
            'id'           => array(),
            'class'        => array(),
            'value'        => array(),
            'placeholder'  => array(),
            'autocomplete' => array(),
            'checked'      => array(),
            'data-shortcode' => array()
        ),
        'select' => array(
            'name'         => array(),
            'id'           => array(),
            'autocomplete' => array(),
        ),
        'option'       => array(
            'value'    => array(),
            'selected' => array()
        ),
    );

    //put $allowed_html to the right, so if the input arrays have the same string keys,
    //the later value for that key will overwrite the previous one
    //this will avoid conflict with plugin that filter the result of wp_kses_allowed_html
    $html = array_merge($allowedposttags, $allowed_html);

    return wp_kses($string, $html);
}

/**
 * Wrapper function for getimagesize.
 * If url is invalid or getimagesize doesn't return an array, return an array(0,0)
 *
 * @author Dario Curvino <@dudo>
 * @since  3.1.5
 * @param $url
 *
 * @return array
 */

function yasr_getimagesize($url) {
    //check if url is valid
    if (yasr_check_valid_url($url) === true) {
        $image_size = @getimagesize($url);

        //be sure that getimagesize has returned an array
        if (!is_array($image_size)) {
            $image_size[0] = 0;
            $image_size[1] = 0;
        }

        return $image_size;
    }

    return array(0,0);
}

/**
 * Check if the given url is a SVG image
 * Return true if everything was fine, a string with message error otherwise
 *
 * @author Dario Curvino <@dudo>
 * @since  2.6.8
 *
 * @param $url
 *
 * @return true|string
 */

function yasr_check_svg_image($url) {
    if ($url !== '') {
        $url_response = yasr_check_valid_url($url);
        //check if url is valid
        if ($url_response === true) {
            //if url is valid, check if is a svg image
            $type  = wp_check_filetype(($url));

            if ($type['type'] === 'image/svg+xml') {
                return true;
            }
            return __FUNCTION__ .  '(): Image provided is not svg';
        }
        return $url_response;
    }
    return __FUNCTION__ . '(): Url can\'t be empty';
}

/**
 * This function return a random string to be used in the dom as ID value
 *
 * @author Dario Curvino <@dudo>
 * @since  2.6.8
 *
 * @param string $prefix
 *
 * @return string
 */
function yasr_return_dom_id ($prefix='') {
    //Do not use $more_entropy param to uniqid() function here, since it can return chars not allowed as ID value
    //To increase likelihood of uniqueness, str_shuffle() is enough for the scope of use
    /** @noinspection NonSecureUniqidUsageInspection */
    return esc_html($prefix) . str_shuffle(uniqid());
}

/**
 * Sanitize rating
 *
 * @author Dario Curvino <@dudo>
 *
 * @since 3.4.4
 *
 * @param $rating
 * @param $min_value
 * @param $only_min
 * @param $only_max
 *
 * @return int|mixed
 */
function yasr_validate_rating($rating, $min_value=1, $only_min=false, $only_max=false) {
    if(!$rating) {
        $rating = 0;
    }

    if ($rating < $min_value) {
        $rating = $min_value;
    }
    elseif ($rating > 5) {
        $rating = 5;
    }

    return $rating;
}
