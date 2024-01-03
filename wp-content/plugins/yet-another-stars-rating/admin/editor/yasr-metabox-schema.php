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

require YASR_ABSOLUTE_PATH_ADMIN . '/editor/YasrMetaboxSchemaFields.php';

global $post;

//is post review
wp_nonce_field('yasr_nonce_is_post_review_action', 'yasr_nonce_is_post_review');

//itemType select
wp_nonce_field('yasr_nonce_review_type_action', 'yasr_nonce_review_type');

//get all additional post meta
$saved_data = get_post_meta($post->ID, 'yasr_schema_additional_fields', true);

//if empty, create a new array
if(empty($saved_data)) {
    $saved_data = array();
}

$array_item_type_info = YasrRichSnippetsItemTypes::returnAdditionalFields();

//foreach every supported element, create a nonce field
//and set single element to false if not present in db to avoid undefined
foreach ($array_item_type_info as $item_type) {
    $nonce_action = $item_type . '_nonce_action';
    $nonce_name   = $item_type . '_nonce_name';
    wp_nonce_field($nonce_action, $nonce_name);

    //avoid undefined
    if(!isset($saved_data[$item_type])) {
        $saved_data[$item_type] = false;
    }
}

$itemType_obj = new YasrMetaboxSchemaFields($saved_data);

?>

<div>

    <div id="yasr-schema-metabox-post-is-review">
        <div class="rich-snippet-title">
            <?php esc_html_e('Rich snippet options', 'yet-another-stars-rating'); ?>
        </div>
        <?php esc_html_e('Is this a review?', 'yet-another-stars-rating'); ?>
        <br />
        <div class="yasr-onoffswitch-big" id="yasr-switcher-post-is-review">
            <input type="checkbox"
                   name="yasr_is_post_review"
                   class="yasr-onoffswitch-checkbox"
                   value='yes'
                   id="yasr-post-is-review-switch"
                   <?php if ($post->yasr_post_is_review === 'yes') {echo " checked='checked' ";} ?>
            >
            <label class="yasr-onoffswitch-label" for="yasr-post-is-review-switch">
                <span class="yasr-onoffswitch-inner"></span>
                <span class="yasr-onoffswitch-switch"></span>
            </label>
        </div>

        <div style="margin-top: 10px;">
            <label for="yasr-schema-title" style="display:block;"><?php esc_html_e('ItemType Name') ?></label>
                <input
                       type="text"
                       id="yasr-schema-title"
                       name="yasr_schema_title"
                       placeholder="<?php echo esc_attr(get_the_title()) ?>"
                       value="<?php echo esc_attr($saved_data['yasr_schema_title']) ?>"
                >
        </div>
        <div class="yasr-element-row-container-description">
            <?php esc_html_e('Optional. If empty, post title will be used instead.', 'yet-another-stars-rating') ?>
        </div>

    </div>

    <p>
    <div class="yasr-choose-reviews-types">
        <?php esc_html_e('Select ItemType', 'yet-another-stars-rating'); ?>
        <br />
        <?php yasr_select_itemtype('yasr-metabox-below-editor-select-schema'); ?>
    </div>

    <div class="yasr-metabox-info-snippet-container" id="yasr-metabox-info-snippet-container" style="display: none;">
        <?php
            //Product
            $itemType_obj->product();

            //localbuisness
            $itemType_obj->localBuinsess();

            //recipe
            $itemType_obj->recipe();

            //softweareApplication
            $itemType_obj->softwareApplication();

            //Book
            $itemType_obj->book();

            //Movie
            $itemType_obj->movie();
        ?>

    </div>

    <p>

</div>