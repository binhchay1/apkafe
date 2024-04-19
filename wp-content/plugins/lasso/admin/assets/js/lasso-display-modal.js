var tiny_mce_editor;
var lasso_editor_check = 0;
var lasso_model_html = '';
var tinymce_lasso_button_label = 'Add A Lasso Display';

if (typeof tinymce !== 'undefined') {
    tinymce.PluginManager.add('lasso_tc_button', function(editor, url) {
        
        if(lasso_editor_check == 0) {
            tiny_mce_editor = editor;
            lasso_editor_check++;
        }
        
        var url_arr = url.split('/');
        url_arr.pop();
        var asset_url = url_arr.join('/');
        
        editor.addButton('lasso_tc_button', {
            title: tinymce_lasso_button_label,
            image: asset_url + '/images/lasso-icon-tinymce.svg',
            icon: false,
            onclick: function() {
                var popup = jQuery('#lasso-display-add');
                if ( 0 === popup.length ) {
                    jQuery('#wpcontent').append(lasso_model_html);
                }
                popup.modal('show');
            }
        });
    });
}

jQuery(function() {
    function loadPopupContent() {
        let allow_pages = ['content-links', 'keyword-opportunities'];
        let current_page = lasso_helper.get_page_name();

        jQuery.ajax({
            url: lassoOptionsData.ajax_url,
            type: 'get',
            data: {
                action: 'lasso_get_display_html',
            }
        })
        .done(function(res) {
            res = res.data;
            lasso_model_html = res.html;

            jQuery('div[aria-label="' + tinymce_lasso_button_label + '"]').click(function() {
                var popup = jQuery('#lasso-display-add');
                if ( 0 === popup.length ) {
                    jQuery('#wpcontent').append(lasso_model_html);
                    popup.modal('toggle');
                }

                lasso_cactus_icon_click();
            });

            if ( allow_pages.includes(current_page) ) {
                var popup = jQuery('#lasso-display-add');
                if ( 0 === popup.length ) {
                    jQuery('#wpcontent').append(lasso_model_html);
                    popup.modal('toggle');
                }
            }

            // Fix for Divi Supreme Pro Editor
            if ( jQuery('#et_pb_layout').length != 0 && 0 === jQuery('#lasso-display-add').length ) {
                jQuery('#wpcontent').append(lasso_model_html);
            }
        });
    }

    loadPopupContent();

    // Lasso Segment Tracking
    jQuery( document ).on( 'hide.bs.modal', '#lasso-display-add', lasso_display_close_event);
});

/**
 * Handler after inserting shortcode into post content
 * 
 * @param {string} shortcode 
 */
function after_insert_shortcode_to_post_content(shortcode) {
    jQuery('#lasso-display-add').modal('hide');

    lasso_segment_tracking('Display Added', {
        shortcode: shortcode
    });
}

/**
 * Lasso Cactus icon click
 */
function lasso_cactus_icon_click() {
    lasso_segment_tracking('Click Lasso cactus icon');
}


/**
 * Lasso Display block close
 */
function lasso_display_close_event() {
    lasso_segment_tracking('Close "Choose a Display Type" Popup');
}

function reset_bs_transition_by_woody_code_plugin(modal='show') {
    if ( lassoOptionsData.is_wc_plugin_activate ) {
        jQuery.event.special.bsTransitionEnd = {
            bindType: 'transitionend',
            delegateType: 'transitionend'
        }

        if ( modal === 'hide' ) {
            setTimeout(function() {
                bindType = 'webkitTransitionEnd';
                delegateType = 'webkitTransitionEnd';
                jQuery.event.special.bsTransitionEnd = {
                    bindType: bindType,
                    delegateType: delegateType
                }
            }, 3000)
        }
    }
}

jQuery(document).on('show.bs.modal', '#lasso-display-add', function () {
    reset_bs_transition_by_woody_code_plugin();
    jQuery('body').addClass('lasso-display-add-modal-open');
});
jQuery(document).on('hide.bs.modal', '#lasso-display-add', function () {
    reset_bs_transition_by_woody_code_plugin('hide');
    jQuery('body').removeClass('lasso-display-add-modal-open');
});

jQuery(document).on('show.bs.modal', '#url-quick-detail', function () {
    reset_bs_transition_by_woody_code_plugin();
});
jQuery(document).on('show.bs.modal', '#url-add', function () {
    reset_bs_transition_by_woody_code_plugin();
});

jQuery(document).on('hide.bs.modal', '#url-quick-detail', function () {
    reset_bs_transition_by_woody_code_plugin('hide');
});
jQuery(document).on('hide.bs.modal', '#url-add', function () {
    reset_bs_transition_by_woody_code_plugin('hide');
});
