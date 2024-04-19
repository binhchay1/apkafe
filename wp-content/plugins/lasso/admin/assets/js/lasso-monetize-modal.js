// when the popup is show, load data via ajax
function show_monetize_modal_triggered() {
    var monetize_popup = jQuery('#link-monetize');

    // load lasso posts when the popup is opened
    monetize_popup.unbind('show.bs.modal');
    monetize_popup.on('show.bs.modal', function (e) {
        var popup = jQuery(e);
        var search = popup.find('.js-monetize-search').first().val();

        search_attributes(search, 1);
    });

    monetize_popup.unbind('hide.bs.modal');
    monetize_popup.on('hide.bs.modal', function (e) {
        var content_tbl = jQuery('#report-content');
        var toggle = content_tbl.find('.js-toggle.js-popup-opened').first();
        var monetize_status = toggle.data('monetize-status');

        if (monetize_status != "checked") {
            toggle.prop('checked', false);
        }

        toggle.removeClass('js-popup-opened');
        toggle.closest('label').removeClass('d-none');
        toggle.closest('div').find('.loader-small').remove();
    });
}

function show_keywords_modal_triggered() {
    var keyword_popup = jQuery('#saved-keywords');

    // load lasso posts when the popup is opened
    keyword_popup.unbind('show.bs.modal');
    keyword_popup.on('show.bs.modal', function (e) {
        var popup = jQuery(e);
        var search = popup.find('.js-keywords-search').first().val();

        search_keywords(search, 1);
    });
}

function flash_background_color(target, color) {
    if(color == 'green') {
        target.append('<div class="monetized-animation"></div>');
        jQuery(".unmonetized-animation").remove();
        target.find('.fa-link, .fa-key').removeClass('gray').addClass('green');
    } else if (color == 'purple') {
        target.append('<div class="unmonetized-animation"></div>');
        jQuery(".monetized-animation").remove();
        target.find('.fa-link, .fa-key').removeClass('green').addClass('gray');
    } else {
        target.append('<div class="unmonetized-animation"></div>');
        jQuery(".monetized-animation").remove();
        target.find('.fa-link, .fa-key').removeClass('green').addClass('gray');
    }
}

// when clicking on Monetize button in the popup,
// monetize a link and hide the popup and reload the data
function click_on_monetize_btn() {
    jQuery('.js-montize-btn').unbind().click(function() {
        var monetize_btn = jQuery(this);
        var content_tbl = jQuery('#report-content');
        var toggle = content_tbl.find('.js-toggle.js-popup-opened').first();
        var link_type = toggle.data('link-type');
        link_type = link_type ? link_type : 'Normal';
        console.log(link_type);

        toggle.removeClass('js-popup-opened');
        jQuery('#link-monetize').modal('hide');

        var post_id = toggle.data('post-id');
        var lasso_id = link_type == 'keyword' ? toggle.data('keyword-location-id') : toggle.data('link-id');
        var row = toggle.closest('.row');
        var new_url = monetize_btn.closest('.row').find('.js-url').first().text();

        // monetize link
        jQuery.ajax({
            url: lassoOptionsData.ajax_url,
            type: 'post',
            data: {
                action: 'lasso_popup_monetize_link',
                post_id: post_id,
                lasso_id: lasso_id,
                new_url: new_url,
                link_type: link_type,
                post_id_count: post_id,
                keyword: toggle.data('old-keyword'),
            },
            beforeSend: function() {
                // REMOVE TOGGLE AND SHOW LOADER WHILE AMAZON LINK IS MONETIZING
                row.find('.toggle').addClass('d-none');
                row.find('.toggle').closest('div').append('<div class="loader-small"></div>');
            }
        })
            .done(function(res) {
                res = res.data;
                if(res.status) {
                    // reload report page after monetized link
                    // var e = jQuery.Event( "keyup", { which: 13 } );
                    // jQuery('#link-search-input').trigger(e);

                    // domain links report
                    var new_url = res.post.new_url;
                    var row = toggle.closest('div.row');
                    var columns = row.children();
                    columns.eq(0).find('a').first().text(new_url).attr('href', new_url);
                    flash_background_color(row.parent(), 'green');
                    toggle.closest('label').removeClass('d-none');
                    toggle.closest('div').find('.loader-small').remove();

                    if(link_type == 'keyword') {
                        toggle.data('link-type', 'link');
                        toggle.data('link-id', res.location_id);
                    }
                } else {
                    toggle.prop('checked', false);
                }
            });
    });
}

function click_on_keyword_delete_btn() {
    jQuery('.js-keyword-btn').unbind().click(function() {
        var keyword_popup = jQuery('#saved-keywords');
        var search = keyword_popup.find('.js-keywords-search').first().val();

        var keyword_delete_btn = jQuery(this);
        var keyword = keyword_delete_btn.data('keyword');

        jQuery.ajax({
            url: lassoOptionsData.ajax_url,
            type: 'post',
            data: {
                action: 'lasso_delete_keyword',
                keyword: keyword
            },
            beforeSend: function() {
                keyword_delete_btn.html(get_loading_image_small());
                clear_notifications();
            }
        })
            .done(function(res) {
                res = res.data;
                if(res.status) {
                    lasso_helper.successScreen("Keyword deleted.");
                    keyword_popup.modal('hide');
                } else {
                    lasso_helper.errorScreen("Delete keyword failed.");
                    keyword_delete_btn.html("Delete");
                }
            });
    });
}

function search_attributes(search_key, page, callback = null) {
    var monetize_popup = jQuery('#link-monetize');

    jQuery.ajax({
        url: lassoOptionsData.ajax_url,
        type: 'post',
        data: {
            action: 'lasso_search_attributes',
            search_key: search_key,
            limit: 6,
            page: page,
        },
        beforeSend: function() {
            monetize_popup.find('.js-rows').first().html(get_loading_image());
        }
    })
        .done(function(res) {
            res = res.data;
            var total_links = res.count ? res.count : 0;
            var popup = monetize_popup;
            var html = '';
            popup.find('.js-monetize-search').attr('placeholder', `Search ${total_links} URLs`);

            // put data into the popup
            if(res.data.length > 0) {
                var row_template = popup.find('.js-rows-template').first();
                for (let index = 0; index < res.data.length; index++) {
                    const element = res.data[index];
                    var row = row_template.clone();
                    row.find('.js-thumbnail').attr('src', element.thumbnail);
                    row.find('.js-name').html(element.name);
                    row.find('.js-url').text(element.permalink);
                    row.find('.js-montize-btn').attr('data-lasso-id', element.post_id);
                    html += row.html();
                }
                popup.find('.js-rows').first().html(html);
                click_on_monetize_btn();
            } else {
                html = `
                    <div class="row align-items-center">
                        <div class="col text-center p-5 m-5">
                            <i class="far fa-skull-cow fa-7x mb-3"></i>
                            <h3>Looks like we're coming up empty, partner.</h3>
                        </div>
                    </div>
                `;
                popup.find('.js-rows').first().html(html);
            }

            if ( callback !== null && typeof callback === "function" ) {
                return callback(total_links);
            } else {
                // pagination
                var popup_pagination = popup.find('.pagination').first();
                popup_pagination.pagination('destroy');
                popup.find('.js-pagination-popup').first().pagination({
                    items: total_links,
                    itemsOnPage: 6,
                    cssStyle: 'light-theme',
                    prevText: '<i class="far fa-angle-double-left"></i> Previous',
                    nextText: 'Next <i class="far fa-angle-double-right"></i>',
                    onPageClick: function(pageNumber, event){
                        search_key = popup.find('.js-monetize-search').first().val();
                        search_attributes(search_key, pageNumber);
                    },
                    currentPage: page
                });
            }
        });
}

function search_keywords(search_key, page) {
    var keywords_popup = jQuery('#saved-keywords');

    jQuery.ajax({
        url: lassoOptionsData.ajax_url,
        type: 'post',
        data: {
            action: 'lasso_get_keywords',
            search_key: search_key,
            limit: 6,
            page: page,
        },
        beforeSend: function() {
            keywords_popup.find('.js-rows').first().html(get_loading_image());
        }
    })
        .done(function(res) {
            res = res.data;
            var total_links = res.count ? res.count : 0;
            var popup = keywords_popup;
            var html = '';
            popup.find('.js-keywords-search').attr('placeholder', `Search ${total_links} Keywords`);

            // put data into the popup
            if(res.data.length > 0) {
                var row_template = popup.find('.js-rows-template').first();
                for (let index = 0; index < res.data.length; index++) {
                    const element = res.data[index];
                    var row = row_template.clone();
                    row.find('.js-keyword').text(element.keyword);
                    row.find('.js-keyword-btn').attr('data-keyword', element.keyword);
                    html += row.html();
                }
                popup.find('.js-rows').first().html(html);
                click_on_keyword_delete_btn();
            } else {
                html = `
                    <div class="row align-items-center">
                        <div class="col text-center p-5 m-5">
                            <i class="far fa-skull-cow fa-7x mb-3"></i>
                            <h3>Looks like we're coming up empty, partner.</h3>
                        </div>
                    </div>
                `;
                popup.find('.js-rows').first().html(html);
            }

            // pagination
            var popup_pagination = popup.find('.pagination').first();
            popup_pagination.pagination('destroy');
            popup.find('.js-pagination-popup').first().pagination({
                items: total_links,
                itemsOnPage: 6,
                cssStyle: 'light-theme',
                prevText: '<i class="far fa-angle-double-left"></i> Previous',
                nextText: 'Next <i class="far fa-angle-double-right"></i>',
                onPageClick: function(pageNumber, event){
                    search_key = popup.find('.js-keywords-search').first().val();
                    search_keywords(search_key, pageNumber);
                },
                currentPage: page
            });
        });
}


jQuery(document).ready(function() {
    var monetize_popup = jQuery('#link-monetize');
    var keywords_popup = jQuery('#saved-keywords');

    // search lasso posts in the popup
    monetize_popup.find('.js-monetize-search').first().keypress(function(e) {
        var monetize_search = jQuery(this);
        var key_code = e.keyCode ? e.keyCode : e.which;
        if(key_code == 13) {
            var search_key = monetize_search.val();
            search_attributes(search_key, 1);

            return false;
        }
    });

    // search lasso posts in the popup
    keywords_popup.find('.js-keywords-search').first().keypress(function(e) {
        var keywords_search = jQuery(this);
        var key_code = e.keyCode ? e.keyCode : e.which;
        if(key_code == 13) {
            var search_key = keywords_search.val();
            search_keywords(search_key, 1);

            return false;
        }
    });
});