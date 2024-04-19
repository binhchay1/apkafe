let import_locations_local_store_key = 'import_locations';
let modal_show_import_locations = null;
// Define popup: Bulk import is processing
let modal_import_all_notification = null;
jQuery(document).ready(function () {
    modal_show_import_locations = new lasso_helper.lasso_generate_modal();
    modal_show_import_locations
        .init( {
            hide_btn_cancel: true,
            hide_btn_ok: true,
            use_modal_large: true
        })
        .set_heading( "Import Locations" )
        .set_pagination( 'import-locations-pagination' );

    modal_import_all_notification = new lasso_helper.lasso_generate_modal();
    modal_import_all_notification
        .init( {
            hide_btn_cancel: true
        })
        .set_btn_ok({
            class: 'green-bg'
        })
        .set_heading('')
        .set_description('Lasso is currently bulk importing links. When this is complete, you can import more.')
        .on_submit(function () {
            modal_import_all_notification.hide();
        });
});

function init_import_events() {
    import_toggle_triggered();
    revert_toggle_triggered();
    show_import_modal_triggered();
    show_revert_modal_triggered();
    click_on_import_all_btn();
    click_on_revert_all_btn();
    click_on_import_btn();
    click_on_revert_btn();
    view_location_triggered();
    click_on_bulk_import_btn();
}

function import_toggle_triggered() {
    var toggle = jQuery('.js-toggle-import');
    toggle.on('click', function(e) {
        
        var checkbox = jQuery(this);
        var is_checked = checkbox.addClass('js-popup-opened');
        var row = checkbox.closest('.row');
        
        if(checkbox.hasClass('js-popup-opened')) {
            jQuery('#import-confirm').modal('show');
        }
    });
}


function revert_toggle_triggered() {
    var toggle = jQuery('.js-toggle-revert');
    toggle.on('click', function(e) {
        
        var checkbox = jQuery(this);
        var is_checked = checkbox.addClass('js-popup-opened');
        var row = checkbox.closest('.row');
        
        if(checkbox.hasClass('js-popup-opened')) {
            jQuery('#revert-confirm').modal('show');
        }
    });
}

function view_location_triggered() {
    jQuery('.show-locations').on('click', function(e) {
        let button   = jQuery(e.target);
        let lasso_id = button.data('import-id');
        let modal_el = jQuery('#' + modal_show_import_locations.get_modal_id());
        let table_locations_current_page = lasso_helper.get_pagination_cache(import_locations_local_store_key, lasso_id);

        modal_show_import_locations.set_description(get_loading_image(), false);
        modal_show_import_locations.show();

        get_import_locations( modal_el, lasso_id, table_locations_current_page, false,function (res) {
            modal_show_import_locations.set_description(build_import_location_modal_content(res.datas), false);

            lasso_helper.generate_paging( jQuery('.import-locations-pagination'), res.page, res.count, function (page_number) {
                get_import_locations( modal_el, lasso_id, page_number, true, function (res) {
                    modal_show_import_locations.set_description(build_import_location_modal_content(res.datas), false);
                } );
            }, lasso_helper.link_location_limit);
        });
    });
}

/**
 * Get import locations html
 *
 * @param wrapper_el
 * @param lasso_id
 * @param page_number
 * @param is_loading
 * @param callback
 */
function get_import_locations( wrapper_el, lasso_id, page_number = 1, is_loading = false, callback = null ) {
    var data_post = {
        action: 'lasso_get_import_locations',
        lasso_id: lasso_id,
        page_number: page_number,
        limit: lasso_helper.link_location_limit
    };
    jQuery.ajax({
        url: lassoOptionsData.ajax_url,
        type: 'post',
        data: data_post,
        async: true,
        beforeSend: function( xhr ) {
            if ( is_loading ) {
                wrapper_el.find('.modal-content p').html(get_loading_image());
            }
        }})
        .done(function (res) {
            res = res.data;
            lasso_helper.set_pagination_cache(import_locations_local_store_key, res.page, lasso_id);
            lasso_helper.remove_page_number_out_of_url();

            if ( typeof callback === 'function' && res.status === 1 ) {
                return callback(res);
            }
        });
}

/**
 * Build import location content.
 *
 * @param datas
 * @returns {string}
 */
function build_import_location_modal_content( datas ) {
    let html = `
        <div class="table-locations-wrapper">
            <div class="pt-4 pb-1 font-weight-bold dark-gray d-lg-block table-location-heading">
                <div class="row row align-items-center">
                    <div class="col-lg-11">
                        <div class="d-inline">Content <label data-tooltip="This is the post or page this table was found in."><i class="far fa-info-circle light-purple"></i></label>
                        </div>
                    </div>
                    <div class="col-lg-1 text-center">Type</div>
                </div>
            </div>
    `;

    for(let i in datas) {
        let data = datas[i];
        let size_class = 'fa-link' === data.icon_class ? '' : 'fa-lg';
        html += `
            <div class="table-location-row ">
                <div class="row row align-items-center py-2 hover-gray">
                    <div class="col-md-11">
                        <div class="font-weight-bold">
                            <a href="${data.edit_post}" target="_blank" title="${data.post_title}" class="black hover-purple-text">${data.post_title}</a>
                            <br/>
                            <a href="${data.post_link}" class="dark-gray hover-purple-text small" target="_blank">
                                ${data.post_link}
                            </a>
                        </div>
                    </div>

                    <!-- LINK TYPE -->
                    <div class="col-lg-1 text-center">
                        <span class="green green-tooltip" data-tooltip="This is ${data.type_description}.">
                            <a data-toggle="modal" class="black hover-purple-text">
                                <i class="fa-lg far ${data.icon_class} ${size_class}"></i>
                            </a>
                        </span>
                    </div>
                </div>
            </div>
        `;
    }

	html += `<div class="clearfix mt-3"></div>
        </div>
    `;

    return html;
}

// Handle the toggle/modal
function show_import_modal_triggered() {
    var import_popup = jQuery('#import-confirm');

    // load lasso posts when the popup is opened
    import_popup.unbind('show.bs.modal');
    import_popup.unbind('hide.bs.modal');
    import_popup.on('hide.bs.modal', function (e) {
        var content_tbl = jQuery('#report-content');
        var toggle = content_tbl.find('.js-popup-opened').first();
        toggle.removeClass('js-popup-opened');
        import_popup.find('.js-import-button').text("Import").prop('disabled', false);
        setTimeout(function () {
            import_popup.find('.js-import-cancel').show();
        }, 500)
    });
}

function show_revert_modal_triggered() {
    var revert_popup = jQuery('#revert-confirm');
    
    // load lasso posts when the popup is opened
    revert_popup.unbind('show.bs.modal');
    revert_popup.unbind('hide.bs.modal');
    revert_popup.on('hide.bs.modal', function (e) {
        var content_tbl = jQuery('#report-content');
        var toggle = content_tbl.find('.js-popup-opened').first();
        toggle.removeClass('js-popup-opened');
        revert_popup.find('.js-revert-button').text("Revert").prop('disabled', false);
        setTimeout(function () {
            revert_popup.find('.js-revert-cancel').show();
        }, 500)

    });
}

// For dramatic effect
function flash_background_color(target, color) {
    if(color == 'green') {
        target.append('<div class="monetized-animation"></div>');
        jQuery(".unmonetized-animation").remove();
        target.find('i').removeClass('gray').addClass('green');
    } else if (color == 'purple') {
        target.append('<div class="unmonetized-animation"></div>');
        jQuery(".monetized-animation").remove();
        target.find('i').removeClass('green').addClass('gray');
    } else {
        target.append('<div class="unmonetized-animation"></div>');
        jQuery(".monetized-animation").remove();
        target.find('i').removeClass('green').addClass('gray');
    }
}

function click_on_import_all_btn() {
    jQuery('.js-import-all-button').unbind().click(function() {
        var lasso_update_popup = jQuery('#url-save');
        var progress_bar = lasso_update_popup.find(".progress-bar");
        lasso_update_popup.find('p').text("Importing and updating your links. For large bulk imports this may take some time with no progress bar movement.");
        
        jQuery('#import-all-confirm').modal('hide');

        lasso_helper.setProgressZero();
        let filter_plugin = jQuery('#filter-plugin').val();
        // Import ALL URL
        jQuery.ajax({
            url: lassoOptionsData.ajax_url,
            type: 'post',
            data: {
                action: 'lasso_import_all_links',
                filter_plugin: filter_plugin
            },
            beforeSend: function (xhr) {
                lasso_update_popup.modal('show');

                setTimeout(function() {
                    lasso_helper.setProgress(10);
                }, 200);
            }
        })
        .done(function(res) {
            res = res.data;
            if(!res.status) {
                lasso_helper.errorScreen("Import failed.");
            }
        })
        .fail(function (xhr, status, error) {
            if(xhr.lasso_error) {
                error = xhr.lasso_error;
            }
            lasso_helper.errorScreen(error);
        })
        .always(function() {
            progress_bar.css({ width: '100%' });
            setTimeout(function() {
                lasso_update_popup.modal('hide');
            }, 1000);
            
            // For each every row and flip toggle/animate
            jQuery(".js-toggle-import").each(function(index, element) {
                flip_toogle_animate('import', element);
            });
        });
    });
}

function click_on_revert_all_btn() {
    jQuery('.js-revert-all-button').unbind().click(function() {
        var lasso_update_popup = jQuery('#url-save');
        var progress_bar = lasso_update_popup.find(".progress-bar");
        lasso_update_popup.find('p').text("Reverting and updating your links. For large bulk reverts this may take some time with no progress bar movement.");
        
        jQuery('#revert-all-confirm').modal('hide');

        lasso_helper.setProgressZero();
        let filter_plugin = jQuery('#filter-plugin').val();

        // Revert ALL URL
        jQuery.ajax({
            url: lassoOptionsData.ajax_url,
            type: 'post',
            data: {
                action: 'lasso_revert_all_links',
                filter_plugin: filter_plugin
            },
            beforeSend: function (xhr) {
                lasso_update_popup.modal('show');

                setTimeout(function() {
                    lasso_helper.setProgress(10);
                }, 200);
            }
        })
        .done(function(res) {
            res = res.data;
            if(!res.status) {
                lasso_helper.errorScreen("Revert failed.");
            }
        })
        .fail(function (xhr, status, error) {
            if(xhr.lasso_error) {
                error = xhr.lasso_error;
            }
            lasso_helper.errorScreen(error);
        })
        .always(function() {
            progress_bar.css({ width: '100%' });
            setTimeout(function() {
                lasso_update_popup.modal('hide');
            }, 1000);
            
            // For each every row and flip toggle/animate
            jQuery(".js-toggle-revert").each(function(index, element) {
                flip_toogle_animate('revert', element);
            });
        });
    });
}

// When the user clicks to import an individual link
function click_on_import_btn() {
    jQuery('.js-import-button').unbind().click(function() {
        var import_btn = jQuery(this);
        jQuery('#import-confirm').find('.js-import-cancel').hide();
        lasso_helper.add_loading_button( import_btn );
        import_btn.prop('disabled', true);
        var content_tbl = jQuery('#report-content');
        var toggle = content_tbl.find('.js-toggle-import.js-popup-opened').first();

        var import_id = toggle.data('import-id');
        var import_permalink = toggle.data('import-permalink');
        var import_source = toggle.data('import-source');
        var post_type = toggle.data('post-type');
        var post_title = toggle.data('post-title');

        // Import URL
        jQuery.ajax({
            url: lassoOptionsData.ajax_url,
            type: 'post',
            data: {
                action: 'lasso_import_single_link',
                import_id: import_id,
                import_permalink: import_permalink,
                post_type: post_type,
                post_title: post_title,
            },
            beforeSend: function() {
                // REMOVE TOGGLE AND SHOW LOADER WHILE AMAZON LINK IS MONETIZING
                toggle.hide();
                toggle.parent().append('<div class="loader-small"></div>');
            }
        })
        .done(function(res) {
            jQuery('#import-confirm').modal('hide');
            res = res.data;
            if(res.status) {
                update_status_for_same_plugin_import_id(import_source, import_id, 'import');
            } else {
                toggle.prop('checked', false);
                jQuery('#import-confirm').modal('hide');
                lasso_helper.errorScreen("Import failed.");
            }
        })
        .always(function() {
            toggle.parent().find('.loader-small').remove();
        });
    });
}

function click_on_revert_btn() {
    jQuery('.js-revert-button').unbind().click(function() {
        var revert_btn = jQuery(this);
        jQuery('#revert-confirm').find('.js-revert-cancel').hide();
        lasso_helper.add_loading_button(jQuery(this));
        revert_btn.prop('disabled', true);
        var content_tbl = jQuery('#report-content');
        var toggle = content_tbl.find('.js-toggle-revert.js-popup-opened').first();

        var import_id = toggle.data('import-id');
        var import_source = toggle.data('import-source');
        var post_type = toggle.data('post-type');

        // Import URL
        jQuery.ajax({
            url: lassoOptionsData.ajax_url,
            type: 'post',
            data: {
                action: 'lasso_revert_single_link',
                import_id: import_id,
                import_source: import_source,
                post_type: post_type,
            }
        })
        .done(function(res) {
            res = res.data;
            if(res.status) {
                jQuery('#revert-confirm').modal('hide');
                update_status_for_same_plugin_import_id(import_source, import_id, 'revert');
            } else {
                toggle.prop('checked', true);
                jQuery('#revert-confirm').modal('hide');
                lasso_helper.errorScreen("Revert failed.");
            }
        });
    });
}


/**
 * Change icon status afer import/revert. We apply for the same plugin's import ids too.
 *
 * @param import_source
 * @param import_id
 * @param type
 */
function update_status_for_same_plugin_import_id(import_source, import_id, type) {
    let selector_class = 'revert' === type ? 'js-toggle-revert' : 'js-toggle-import';
    let btns = jQuery(`.${selector_class}[data-import-source="${import_source}"][data-import-id="${import_id}"]`);
    btns.each(function(index, element) {
        flip_toogle_animate(type, element);
    });
}

/**
 * Flip toggle/animate
 * @param type
 */
function flip_toogle_animate(type, element) {
    let selector_class = 'revert' === type ? 'js-toggle-revert' : 'js-toggle-import';
    let toggle = jQuery(element);
    let row = toggle.closest('div.row');
    if ( toggle.hasClass( selector_class ) ) {
        let checked_value = 'revert' === type ? false : true;
        let flash_background_color_value = 'revert' === type ? 'purple' : 'green';
        toggle.prop('checked', checked_value);
        toggle.hide();
        toggle.next('.fa-check-circle').removeClass('d-none');
        flash_background_color(row.parent(), flash_background_color_value);
    }
}

/**
 * Check the bulk import status:
 * Is processing: Show notification pop-up
 * Is Free: Show confirmation pop-up
 */
function click_on_bulk_import_btn() {
    jQuery('#btn-bulk-import').unbind().on('click', function () {
        let bulk_import_btn = jQuery(this);
        jQuery.ajax({
            url: lassoOptionsData.ajax_url,
            type: 'get',
            data: {
                action: 'lasso_is_import_all_processing',
            },
            beforeSend: function() {
                lasso_helper.add_loading_button(bulk_import_btn, 'Checking');
            }
        })
        .done(function(res) {
            res = res.data;
            if(res.is_processing) {
                modal_import_all_notification.show();
            } else {
                jQuery('#import-all-confirm').modal('show');
            }
            bulk_import_btn.html('Bulk Import');
        });
    });
}