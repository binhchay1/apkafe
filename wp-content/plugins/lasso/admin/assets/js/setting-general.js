
function ga_pageview() {
    let pageview = jQuery('input[name="analytics_enable_send_pageview"]');
    let modal_confirm_pageview = new lasso_helper.lasso_generate_modal();
    modal_confirm_pageview
        .init({
            backdrop: true,
        })
        .set_heading('Enable Pageview')
        .set_description('If you want Lasso to report pageviews to Google Analytics, click Enable. If you have Google Analytics installed outside of Lasso, keep this disabled.')
        .set_btn_ok({
            class: 'green-bg',
            label: 'Enable'
        })
        .on_submit(function () {
            modal_confirm_pageview.hide();
        })
        .on_cancel(function () {
            pageview.prop( "checked", false );
        });

    pageview
        .change(function() {
            let enablePageview = jQuery(this);
            var isChecked = enablePageview.is(":checked");
            if(isChecked) {
                modal_confirm_pageview.show();
            }
        });
}
jQuery(document).ready(function () {
    ga_pageview();
});
