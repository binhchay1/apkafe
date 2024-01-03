<?php

if (!defined('ABSPATH')) {
    exit('You\'re not allowed to see this page');
} // Exit if accessed directly

//AutoLoad Yasr Response, only when a object is created
spl_autoload_register('yasr_autoload_rest_response');

/**
 * Callback function for the spl_autoload_register above.
 *
 * @param $class
 */
function yasr_autoload_rest_response($class) {
    /**
     * If the class being requested does not start with 'Yasr' prefix,
     * it's not in Yasr Project
     */
    if (0 !== strpos($class, 'Yasr')) {
        return;
    }
    $file_name =  YASR_ABSOLUTE_PATH_INCLUDES . '/rest/classes/' . $class . '.php';

    // check if file exists, just to be sure
    if (file_exists($file_name)) {
        require($file_name);
    }
}

//get all post meta and return it in the meta[] response
$yasr_post_meta       = new YasrPostMeta();

//add a custom response
$yasr_custom_fields   = new YasrCustomFields();

//register new route
$yasr_custom_endpoint = new YasrCustomEndpoint();

$yasr_post_meta->restApiInit();
$yasr_custom_fields->restApiInit();
$yasr_custom_endpoint->restApiInit();

//Filter existing CPT to work with YASR
/****
 * Since version 2.4.5 this is comment out,
 * to avoid security problems tha may occur
 */
//YasrCustomPostTypes::addFilterExistingCpt();