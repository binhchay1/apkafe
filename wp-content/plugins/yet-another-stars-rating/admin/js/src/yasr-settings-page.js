/****** Yasr Settings Page ******/

import {
    addMultisetCriteria,
    editFormAddElement,
    selectMultiset,
} from "./yasrMultiCriteriaUtils";

import {
    getActiveTab
} from "./yasr-admin-functions";

//get active Tab
const activeTab = getActiveTab();

//-------------------General Settings Code---------------------
if (activeTab === 'general_settings') {
    const autoInsertEnabled = document.getElementById('yasr_auto_insert_switch');
    const starsTitleEnabled = document.getElementById('yasr-general-options-stars-title-switch');
    const sortPostsDisabled = document.getElementById('yasr_general_options[sort_posts_by]-no');
    const sortPostsRadio    = document.querySelectorAll('input[type=radio][name="yasr_general_options[sort_posts_by]"]');

    if (autoInsertEnabled.checked === false) {
        jQuery('.yasr-auto-insert-options-class').prop('disabled', true);
    }

    if(starsTitleEnabled.checked === false) {
        jQuery('.yasr-stars-title-options-class').prop('disabled', true);
    }

    if(sortPostsDisabled.checked === true) {
        jQuery('#yasr-sort-posts-list-archives :input').prop('disabled', true);
    }

    //First Div, for auto insert
    autoInsertEnabled.addEventListener('change', function() {
        if (this.checked) {
            jQuery('.yasr-auto-insert-options-class').prop('disabled', false);
        } else {
            jQuery('.yasr-auto-insert-options-class').prop('disabled', true);
        }
    });

    //Second Div, for stars title
    starsTitleEnabled.addEventListener('change', function() {
        if (this.checked) {
            jQuery('.yasr-stars-title-options-class').prop('disabled', false);
        } else {
            jQuery('.yasr-stars-title-options-class').prop('disabled', true);
        }
    });

    //attach an eventlistener to the radio buttons (sortPostsRadio)
    Array.prototype.forEach.call(sortPostsRadio, function(radio) {
        radio.addEventListener('change', function (event) {
            //if "no is select, disable checkboxes, or enable otherwise
            if(this.value === 'no') {
                jQuery('#yasr-sort-posts-list-archives :input').prop('disabled', true);
            } else {
                jQuery('#yasr-sort-posts-list-archives :input').prop('disabled', false);
            }
        });
    });


    document.getElementById('yasr-settings-custom-texts').addEventListener('click', function() {
        document.getElementById('yasr-settings-custom-text-before-overall').value = 'Our Score';
        document.getElementById('yasr-settings-custom-text-before-visitor').value = 'Click to rate this post!';
        document.getElementById('yasr-settings-custom-text-after-visitor').value  = '[Total: %total_count% Average: %average%]';
        document.getElementById('yasr-settings-custom-text-rating-saved').value   = 'Rating saved!';
        document.getElementById('yasr-settings-custom-text-rating-updated').value = 'Rating updated!';
        document.getElementById('yasr-settings-custom-text-must-sign-in').value   = 'You must sign in to vote';
        document.getElementById('yasr-settings-custom-text-already-rated').value  = 'You have already voted for this article with %rating%';
    });

} //End if general settings

if (activeTab === 'style_options') {
    wp.codeEditor.initialize(
        document.getElementById('yasr_style_options_textarea'),
        yasr_cm_settings
    );

    jQuery('#yasr-color-scheme-preview-link').on('click', function () {
        jQuery('#yasr-color-scheme-preview').toggle('slow');
        return false; // prevent default click action from happening!
    });

    wp.hooks.doAction('yasrStyleOptions');
}

//--------------Multi Sets Page ------------------
if (activeTab === 'manage_multi') {
    //Manage the "Add new Criteria" button
    addMultisetCriteria ();

    editFormAddElement();

    selectMultiset();
} //end if active_tab=='manage_multi'

if (activeTab === 'migration_tools') {
    jQuery('#yasr-import-rmp-submit').on('click', function () {

        //show loader on click
        document.getElementById('yasr-import-rmp-answer').innerHTML = yasrWindowVar.loaderHtml;

        var nonce = document.getElementById('yasr-import-rmp-nonce').value;

        var data = {
            action: 'yasr_import_ratemypost',
            nonce: nonce
        };

        jQuery.post(ajaxurl, data, function (response) {
            response = JSON.parse(response);
            document.getElementById('yasr-import-rmp-answer').innerHTML = response;
        });

    });

    jQuery('#yasr-import-wppr-submit').on('click', function () {

        //show loader on click
        document.getElementById('yasr-import-wppr-answer').innerHTML = yasrWindowVar.loaderHtml;

        var nonce = document.getElementById('yasr-import-wppr-nonce').value;

        var data = {
            action: 'yasr_import_wppr',
            nonce: nonce
        };

        jQuery.post(ajaxurl, data, function (response) {
            response = JSON.parse(response);
            document.getElementById('yasr-import-wppr-answer').innerHTML = response;
        });

    });

    jQuery('#yasr-import-kksr-submit').on('click', function () {

        //show loader on click
        document.getElementById('yasr-import-kksr-answer').innerHTML = yasrWindowVar.loaderHtml;

        var nonce = document.getElementById('yasr-import-kksr-nonce').value;

        var data = {
            action: 'yasr_import_kksr',
            nonce: nonce
        };

        jQuery.post(ajaxurl, data, function (response) {
            response = JSON.parse(response);
            document.getElementById('yasr-import-kksr-answer').innerHTML = response;
        });

    });

    //import multi rating
    jQuery('#yasr-import-mr-submit').on('click', function () {

        //show loader on click
        document.getElementById('yasr-import-mr-answer').innerHTML = yasrWindowVar.loaderHtml;

        var nonce = document.getElementById('yasr-import-mr-nonce').value;

        var data = {
            action: 'yasr_import_mr',
            nonce: nonce
        };

        jQuery.post(ajaxurl, data, function (response) {
            response = JSON.parse(response);
            document.getElementById('yasr-import-mr-answer').innerHTML = response;
        });

    });

    wp.hooks.doAction('yasr_migration_page_bottom');
}

if (activeTab === 'rankings') {
    wp.hooks.doAction('yasr_ranking_page_top');
}

/****** End Yasr Settings Page ******/