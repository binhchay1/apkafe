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
            if ( callback !== null && typeof callback === "function" ) {
                return callback(total_links);
            }
        });
}

function search_attributes_group(search_key, page, callback = null) {
    var monetize_popup = jQuery('#group-monetize');

    jQuery.ajax({
            url: lassoOptionsData.ajax_url,
            type: 'post',
            data: {
                action: 'lasso_search_attributes_group',
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
            popup.find('.js-monetize-search').attr('placeholder', `Search ${total_links} Groups`);

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
            popup.find('.js-pagination-popup').first().pagination({
                items: total_links,
                itemsOnPage: 6,
                cssStyle: 'light-theme',
                prevText: '<i class="far fa-angle-double-left"></i> Previous',
                nextText: 'Next <i class="far fa-angle-double-right"></i>',
                onPageClick: function(pageNumber, event){
                    search_key = popup.find('.js-monetize-search').first().val();
                    search_attributes_group(search_key, pageNumber);
                },
                currentPage: page
            });
            if ( callback !== null && typeof callback === "function" ) {
                return callback(total_links);
            }
        });
}


jQuery(document).ready(function() {
    // search lasso posts in the popup
    var monetize_popup = jQuery('#link-monetize');
    monetize_popup.find('.js-monetize-search').first().keypress(function(e) {
        var monetize_search = jQuery(this);
        var key_code = e.keyCode ? e.keyCode : e.which;
        if(key_code == 13) {
            var search_key = monetize_search.val();
            search_attributes(search_key, 1);
            
            return false;
        }
    });

    var monetize_popup_group = jQuery('#group-monetize');
    // search lasso group in the popup
    monetize_popup_group.find('.js-monetize-search').first().keypress(function(e) {
        var monetize_search = jQuery(this);
        var key_code = e.keyCode ? e.keyCode : e.which;
        if(key_code == 13) {
            var search_key = monetize_search.val();
            search_attributes_group(search_key, 1);
            
            return false;
        }
    });
});
