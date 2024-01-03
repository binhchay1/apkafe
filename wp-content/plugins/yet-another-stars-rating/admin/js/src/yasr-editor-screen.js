import {copyToClipboard} from "./yasr-admin-functions";

import {yasrMultiCriteriaEditPage} from "./yasrMultiCriteriaUtils";

// executes this when the DOM is ready
document.addEventListener('DOMContentLoaded', function(event) {
    const metaboxBelow   = document.getElementById('yasr_metabox_below_editor');

    //always show snippet or multi set, if metabox is rendered
    if(metaboxBelow !== null) {
        yasrPrintMetaBoxBelowEditor();
    }

    //check if is gutenberg editor
    let yasrIsGutenbergEditor = document.body.classList.contains('block-editor-page');

    if(yasrIsGutenbergEditor !== true) {
        const metaboxOverall = document.getElementById('yasr_metabox_overall_rating');

        if(metaboxOverall !== null) {
            //show overall rating in the metabox
            yasrPrintMetaBoxOverall();
        }

        //run shortcode creator
        yasrShortcodeCreator();
    }

}); //end document ready

/**
 * Print the stars for top right metabox
 *
 * @return void;
 */
function yasrPrintMetaBoxOverall() {
    //Convert string to number
    let overallRating = parseFloat(document.getElementById('yasr-overall-rating-value').value);
    const copyOverall = document.getElementById('yasr-editor-copy-overall');

    if(copyOverall !== null) {
        copyOverall.onclick = function (event) {
            let el = document.getElementById(event.target.id);
            copyToClipboard(el.textContent.trim());
        }
    }

    yasrSetRaterValue (
        32,
        'yasr-rater-overall',
        false,
        0.1,
        false,
        overallRating,
        function (rating, done) {
            rating = rating.toFixed(1);
            rating = parseFloat(rating);

            //update hidden field
            document.getElementById('yasr-overall-rating-value').value = rating;

            this.setRating(rating);

            let yasrOverallString = 'You\'ve rated';

            document.getElementById('yasr_overall_text').textContent = yasrOverallString + ' ' + rating;

            done();
        }
    );

}

/**
 * Print metabox below editor
 * At the page load, show Schema.org option
 */
function yasrPrintMetaBoxBelowEditor () {
    const selectSchema = document.getElementById('yasr-metabox-below-editor-select-schema');

    selectSchema.addEventListener('change',
        function() {
            let selectedItemtype = this.value;
            yasrSwitchItemTypeDiv(selectedItemtype);
        }
    );

    // When click on main tab hide multi set content
    jQuery('#yasr-metabox-below-editor-structured-data-tab').on("click", function (e) {

        //prevent click on link jump to the top
        e.preventDefault();

        jQuery('.yasr-nav-tab').removeClass('nav-tab-active');
        jQuery('#yasr-metabox-below-editor-structured-data-tab').addClass('nav-tab-active');

        jQuery('.yasr-metabox-below-editor-content').hide();
        jQuery('#yasr-metabox-below-editor-structured-data').show();

    });

    //When click on multi set tab hide snippet content
    jQuery('#yasr-metabox-below-editor-multiset-tab').on("click", function (e) {

        //prevent click on link jump to the top
        e.preventDefault();

        jQuery('.yasr-nav-tab').removeClass('nav-tab-active');
        jQuery('#yasr-metabox-below-editor-multiset-tab').addClass('nav-tab-active');

        jQuery('.yasr-metabox-below-editor-content').hide();
        jQuery('#yasr-metabox-below-editor-multiset').show();

    });

    let selectedItemtype = document.getElementById('yasr-metabox-below-editor-select-schema').value;

    if(document.getElementById('yasr-editor-multiset-container') !== null) {
        yasrMultiCriteriaEditPage();
    }

    yasrSwitchItemTypeDiv(selectedItemtype);
}

function yasrSwitchItemTypeDiv (itemType) {
    if(itemType === 'Product') {
        //show main div
        document.getElementById('yasr-metabox-info-snippet-container').style.display = '';
        //show product
        document.getElementById('yasr-metabox-info-snippet-container-product').style.display = '';

        //hide all child divs
        document.getElementById('yasr-metabox-info-snippet-container-localbusiness').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-recipe').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-software').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-book').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-movie').style.display = 'none';

    }
    else if(itemType === 'LocalBusiness') {
        //show main div
        document.getElementById('yasr-metabox-info-snippet-container').style.display = '';
        //show localbusiness
        document.getElementById('yasr-metabox-info-snippet-container-localbusiness').style.display = '';
        //hide all child
        document.getElementById('yasr-metabox-info-snippet-container-product').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-recipe').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-software').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-book').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-movie').style.display = 'none';

    }
    else if(itemType === 'Recipe') {
        //show main div
        document.getElementById('yasr-metabox-info-snippet-container').style.display = '';
        //show recipe
        document.getElementById('yasr-metabox-info-snippet-container-recipe').style.display = '';
        //hide all child
        document.getElementById('yasr-metabox-info-snippet-container-localbusiness').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-product').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-software').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-book').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-movie').style.display = 'none';
    }
    else if(itemType === 'SoftwareApplication') {
        //show main div
        document.getElementById('yasr-metabox-info-snippet-container').style.display = '';
        //show Software Application
        document.getElementById('yasr-metabox-info-snippet-container-software').style.display = '';

        //hide all childs
        document.getElementById('yasr-metabox-info-snippet-container-recipe').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-localbusiness').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-product').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-book').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-movie').style.display = 'none';
    }
    else if(itemType === 'Book') {
        //show main div
        document.getElementById('yasr-metabox-info-snippet-container').style.display = '';
        //show Book
        document.getElementById('yasr-metabox-info-snippet-container-book').style.display = '';

        //hide all childs
        document.getElementById('yasr-metabox-info-snippet-container-recipe').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-localbusiness').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-product').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-software').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-movie').style.display = 'none';

    }

    else if(itemType === 'Movie') {
        //show main div
        document.getElementById('yasr-metabox-info-snippet-container').style.display = '';
        //show Book
        document.getElementById('yasr-metabox-info-snippet-container-movie').style.display = '';

        //hide all childs
        document.getElementById('yasr-metabox-info-snippet-container-recipe').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-localbusiness').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-product').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-software').style.display = 'none';
        document.getElementById('yasr-metabox-info-snippet-container-book').style.display = 'none';

    }

    else {
        document.getElementById('yasr-metabox-info-snippet-container').style.display = 'none';
    }
}


/****** End Yasr Metabox Multple Rating  ******/

/****** Yasr Ajax Page ******/
// When click on chart hide tab-main and show tab-charts

function yasrShortcodeCreator() {

    jQuery('#yasr-shortcode-creator').on("click", function () {
        tb_show( 'Ranking Creator', '#TB_inline?width=770&height=700&inlineId=yasr-tinypopup-form' );
        jQuery("#TB_window").css({width: '800px'});
    });

    jQuery('#yasr-builder-copy-shortcode').on("click", function () {
        // close
        tb_remove();
    });

    let nMultiSet = false

    if(document.getElementById('yasr-editor-multiset-container') !== null) {
        nMultiSet = true;
    }

    const linkDoc = document.getElementById('yasr-tinypopup-link-doc');

    // When click on main tab hide tab-main and show tab-charts
    jQuery('#yasr-link-tab-main').on("click", function () {
        jQuery('.yasr-nav-tab').removeClass('nav-tab-active');
        jQuery('#yasr-link-tab-main').addClass('nav-tab-active');

        jQuery('.yasr-content-tab-tinymce').hide();
        jQuery('#yasr-content-tab-main').show();

        linkDoc.setAttribute('href', 'https://yetanotherstarsrating.com/yasr-shortcodes?utm_source=wp-plugin&utm_medium=tinymce-popup&utm_campaign=yasr_editor_screen');
    });

    jQuery('#yasr-link-tab-charts').on("click", function () {

        jQuery('.yasr-nav-tab').removeClass('nav-tab-active');
        jQuery('#yasr-link-tab-charts').addClass('nav-tab-active');

        jQuery('.yasr-content-tab-tinymce').hide();
        jQuery('#yasr-content-tab-charts').show();

        linkDoc.setAttribute('href', 'https://yetanotherstarsrating.com/yasr-shortcodes/?utm_source=wp-plugin&utm_medium=tinymce-popup&utm_campaign=yasr_editor_screen#yasr-rankings-shortcodes');
    });

    // Add shortcode for overall rating
    jQuery('#yasr-overall').on("click", function () {
        jQuery('#yasr-overall-choose-size').toggle('slow');
    });

    //Add shortcode for visitors rating
    jQuery('#yasr-visitor-votes').on("click", function () {
        jQuery('#yasr-visitor-choose-size').toggle('slow');
    });

    jQuery('.yasr-tinymce-shortcode-buttons').on("click", function () {
        let shortcode = this.getAttribute('data-shortcode');

        if (tinyMCE.activeEditor == null) {
            //this is for tinymce used in text mode
            jQuery("#content").append(shortcode);
        } else {
            // inserts the shortcode into the active editor
            tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
        }

        // close
        tb_remove();
        //jQuery('#yasr-tinypopup-form').dialog('close');

    });

    if (nMultiSet === true) {
        //Add shortcode for multiple set
        jQuery('#yasr-insert-multiset-select').on("click", function () {
            let setType     = jQuery("input:radio[name=yasr_tinymce_pick_set]:checked").val();
            let visitorSet  = jQuery("#yasr-allow-vote-multiset").is(':checked');
            let showAverage = jQuery("#yasr-hide-average-multiset").is(':checked');
            let shortcode;

            if (!visitorSet) {
                shortcode = '[yasr_visitor_multiset setid=';
            } else {
                shortcode = '[yasr_multiset setid=';
            }

            shortcode += setType;

            if (showAverage) {
                shortcode += ' show_average=\'no\'';
            }

            shortcode += ']';

            // inserts the shortcode into the active editor
            if (tinyMCE.activeEditor == null) {
                //this is for tinymce used in text mode
                jQuery("#content").append(shortcode);
            } else {
                // inserts the shortcode into the active editor
                tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
            }

            // close
            tb_remove();
        });

    } //End if

} //End function

/****** End YAsr Ajax page ******/