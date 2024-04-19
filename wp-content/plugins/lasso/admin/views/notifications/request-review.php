<?php 
use Lasso\Classes\Enum;
use Lasso\Classes\Helper as Lasso_Helper;
?>

<div class="ls-review col-12">
	<div class="icon">
		<img src="<?php echo LASSO_PLUGIN_URL; ?>/admin/assets/images/lasso-256x256.png">
	</div>
	<div class="review">
		Hi there! We noticed you've been using Lasso for a while now. How do you like it so far?
		<p>
			<span><a href="#" class="review-request">It's awesome!</a></span>
			<span><a href="#" class="review-support">I'm having some trouble</a></span>
			<span><a href="#" class="review-snooze">I'm testing things out</a></span>
		</p>
	</div>
    <div class="dismiss float-right align-middle p-1"><a href="#"><i class="far fa-times-circle mt-2"></i></a></div>
</div>

<script>
    jQuery(document).ready(function() {
        jQuery(document)
            .on('click', '.ls-review .review-request', click_review_request)
            .on('click', '.ls-review .review-support', click_review_support)
            .on('click', '.ls-review .review-snooze', click_review_snooze)
            .on('click', '.ls-review .dismiss', click_review_dismiss)
        ;

        function click_review_request() {
            let a_link = `<a href="<?php echo Enum::LASSO_REVIEW_URL; ?>" target="_blank">Trustpilot</a>`;
            let new_text = `That's what we like to hear. Please take a moment to leave a 5-star review on ${a_link}. Thanks for your support!`;
            jQuery('.ls-review div.review').html(new_text);
        }

        function click_review_support() {
            let a_link = `<a href="#" class="show-intercom">Lasso's Support Team</a>`;
            let new_text = `We're sorry to hear that. Please contact ${a_link}, and we'll help however we can!`;
            jQuery('.ls-review div.review').html(new_text);

            jQuery('.show-intercom').click(function() {
                jQuery('#support-launcher').trigger('click');
                if ( typeof Intercom === "function" ) {
                    Intercom('show');
                } else {
                    jQuery('.fake-intercom-bubble-chat-app-launcher-icon').trigger('click');
                }
            });
        }

        function click_review_snooze() {
            jQuery.ajax({
                url: '<?php echo Lasso_Helper::get_ajax_url(); ?>',
                type: 'post',
                data: {
                    action: 'lasso_review_snooze',
                },
            })
            .done(function (res) {
                jQuery('.ls-review').hide(500);
            });
        }

        function click_review_dismiss() {
            jQuery.ajax({
                url: '<?php echo Lasso_Helper::get_ajax_url(); ?>',
                type: 'post',
                data: {
                    action: 'lasso_disable_review',
                },
            })
            .done(function (res) {
                jQuery('.ls-review').hide(500);
            });
        }
    });
</script>
