var lasso_stop_ajax_timeout = 300000; // ms ~ 5 mins
var get_lasso_background_processing_interval_time = 15000; // ms
var lasso_background_processing_interval          = false;
var lasso_sync_content_id_selector = "#lasso-sync-content";
var lasso_sync_icon_id_selector = "#lasso-sync-icon";
var lasso_sync_number_id_selector = "#lasso-sync-number";
var lasso_sync_content_modal_open_class_selector = ".sync-content-modal-open";
var lasso_sync_content_modal_open_class_str = lasso_sync_content_modal_open_class_selector.replace(".", "");
var background_template_wrapper = '<ul class="list-group"><li class="list-group-header">Automations</li></ul>';
var background_template_item = '<li class="list-group-item"></li>';

// GET LIST BACKGROUND PROCESSING
function send_request_get_background_processing() {
    var wrapper_sync   = jQuery(lasso_sync_icon_id_selector);
    var syncing_number = wrapper_sync.find(lasso_sync_number_id_selector);
    var current_timestamp = Math.floor(Date.now() / 1000); // in seconds

    let lasso_bg_process_data = localStorage.getItem("lasso_bg_process_data");
    try {
        lasso_bg_process_data = JSON.parse(lasso_bg_process_data);
    } catch (error) {
        lasso_bg_process_data = null;
    }

    let lasso_bg_process_time = parseInt(localStorage.getItem("lasso_bg_process_time"));
    let check_local_storage = (current_timestamp - lasso_bg_process_time) < (get_lasso_background_processing_interval_time / 1000);
    // use bg process data from local storage instead of ajax when the another tab sent a ajax request
    if (check_local_storage && lasso_bg_process_data) {
        let data = lasso_bg_process_data;
        render_background_process_stats_content(data, false);

        if (data.running_total > 0) {
            wrapper_sync.find('i').addClass('fa-spin'); // Update icon sync to syncing
            wrapper_sync.find('svg').addClass('fa-spin'); // Update icon sync to syncing
            syncing_number.removeClass('d-none');
        } else {
            wrapper_sync.find('i').removeClass('fa-spin'); // Update icon sync to not un-syncing
            wrapper_sync.find('svg').removeClass('fa-spin'); // Update icon sync to not un-syncing
            syncing_number.addClass('d-none');
        }

        return;
    }

    jQuery.ajax({
        url: lassoOptionsData.ajax_url,
        type: 'get',
        data: {
            action: 'get_list_background_processing',
        }
    })
        .done(function(res) {
            // Apply try catch cause sometime res.data is undefined for some reason, maybe server error for a moment.
            try {
                let data = res.data;

                // store data to local storage for reusing in other tabs
                lasso_helper.set_local_storage("lasso_bg_process_data", JSON.stringify(data));

                // Render background content by js because we don't want ajax response the long content in html, that increase the hosting cost.
                render_background_process_stats_content(data, true);

                if (data.running_total > 0) {
                    wrapper_sync.find('i').addClass('fa-spin'); // Update icon sync to syncing
                    wrapper_sync.find('svg').addClass('fa-spin'); // Update icon sync to syncing
                    syncing_number.removeClass('d-none');
                } else {
                    wrapper_sync.find('i').removeClass('fa-spin'); // Update icon sync to not un-syncing
                    wrapper_sync.find('svg').removeClass('fa-spin'); // Update icon sync to not un-syncing
                    syncing_number.addClass('d-none');
                }
            } catch(err) {
                console.log('ERROR: Send request get background processing: ' + err.message);
                console.log('Response: ', res);
            }
        });

    lasso_helper.set_local_storage("lasso_bg_process_time", current_timestamp);
}

function get_lasso_background_processing() {
    lasso_background_processing_interval = setInterval(function () {
        send_request_get_background_processing();
    }, get_lasso_background_processing_interval_time);

    setTimeout(function() {
        clearInterval( lasso_background_processing_interval );
    }, lasso_stop_ajax_timeout);
}


jQuery(document).ready(function() {
    send_request_get_background_processing(); // Call to get the first update
    get_lasso_background_processing(); // Call interval to get newest background processing
    check_lasso_crons(); // check and trigger lasso cron if default WP cron doesn't work on hosting/server

    var position = jQuery(lasso_sync_icon_id_selector).offset();
    var width = jQuery(lasso_sync_content_id_selector).width() / 2;
    jQuery(lasso_sync_content_id_selector).css("left", ( position.left - width + 5 ) + "px");
    jQuery(lasso_sync_content_id_selector).css("top", ( position.top + 18 ) + "px");
    jQuery(lasso_sync_icon_id_selector).unbind().click(function () {
        if ( jQuery('body').hasClass(lasso_sync_content_modal_open_class_str) ) {
            close_sync_content_modal();
        }
        else {
            open_sync_content_modal();
        }
    });

}).on("click", "body", function ( el ) {
    if( jQuery(el.target).closest(lasso_sync_icon_id_selector).length === 0 && jQuery(el.target.closest(lasso_sync_content_id_selector)).length === 0 ) {
        close_sync_content_modal();
    }
});


function open_sync_content_modal() {
    if ( jQuery('body').hasClass(lasso_sync_content_modal_open_class_str) === false ) {
        jQuery(lasso_sync_content_id_selector).addClass("animation");
        jQuery(lasso_sync_content_id_selector).css('opacity', 1);
        jQuery(lasso_sync_content_id_selector).css('z-index', 10);
        jQuery('body').addClass(lasso_sync_content_modal_open_class_str);
    }
}

function close_sync_content_modal() {
    if ( jQuery('body').hasClass(lasso_sync_content_modal_open_class_str) ) {
        jQuery(lasso_sync_content_id_selector).removeClass("animation");
        jQuery(lasso_sync_content_id_selector).css('opacity', 0);
        jQuery('body').removeClass(lasso_sync_content_modal_open_class_str);
    }
}

function render_background_process_stats_content( data, enable_cron_manually ) {
    if ( 'undefined' === typeof data ) {
        return;
    }

    var background_wrapper = jQuery(background_template_wrapper);
    var syncing_number = jQuery(lasso_sync_icon_id_selector).find(lasso_sync_number_id_selector);

    if ( data ) {
        syncing_number.html(data.running_total);
    }

    if ( data.hasOwnProperty('items') && data['items'].length ) {
        data['items'].forEach(function (item) {
            var background_item = jQuery(background_template_item);
            background_item = background_item.html('<div class="lasso-list-group-item-heading">' + item['name'] + ' (' + item['completed'] + '/' + item['total'] + ')</div>');
            background_wrapper.append(background_item);

            if ( item['trigger_manually'] && enable_cron_manually ) {
                run_cron_manually(item.class);
            }
        });
    } else {
        var background_item = jQuery(background_template_item);
        background_item = background_item.html('<div class="lasso-list-group-item-heading">Silence is a source of great strength - Lao Tzu</div>');
        background_wrapper.append(background_item);
    }

    jQuery(lasso_sync_content_id_selector).html(background_wrapper);
}

function run_cron_manually(class_name) {
    jQuery.ajax({
        url: lassoOptionsData.ajax_url,
        type: 'post',
        data: {
            action: 'lasso_cron_handle_manually',
            class_name: class_name,
        }
    })
    .done(function(res) {
        console.log(res);
    })
    .error(function(xhr) {
        console.log(xhr);
    });
}

function check_lasso_crons( ) {
    let ajax_timeout = 30000; // ms

    let check_lasso_crons_interval = setInterval(function () {
        let current_timestamp = Math.floor(Date.now() / 1000); // in seconds
        let lasso_bg_check_crons_time = parseInt(localStorage.getItem("lasso_bg_check_crons_time"));
        let check_local_storage = (current_timestamp - lasso_bg_check_crons_time) < (get_lasso_background_processing_interval_time / 1000);
        // use bg process data from local storage instead of ajax when the another tab sent a ajax request
        if (check_local_storage) {
            return;
        }

        jQuery.ajax({
            url: lassoOptionsData.ajax_url,
            type: 'get',
            data: {
                action: 'lasso_check_crons',
            },
            timeout: ajax_timeout,
        }).done(function(res) {
            if ( res.data && res.data.status ) {
                // Clear interval when check Lasso cron status is working well.
                clearInterval( check_lasso_crons_interval );
            }
        });

        setTimeout(function() {
            clearInterval( check_lasso_crons_interval );
        }, lasso_stop_ajax_timeout);

        // store data to local storage for reusing in other tabs
        lasso_helper.set_local_storage("lasso_bg_check_crons_time", current_timestamp);
    }, get_lasso_background_processing_interval_time);
}
