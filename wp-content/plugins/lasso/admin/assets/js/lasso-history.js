function click_on_post_content_revert_btn() {
	jQuery(".post-content-revert").on("click", function() {
			let history_id         = jQuery(this).data('history-id');
			let post_name          = jQuery(this).data('post-name');
			let lasso_update_popup = jQuery('#url-save');
			lasso_helper.setProgressZero();
			lasso_helper.scrollTop();
			lasso_update_popup.find('p').text("Saving your changes.");

			jQuery.ajax({
				url: lassoOptionsData.ajax_url,
				type: 'post',
				data: {
					action: 'lasso_revert_post_content',
					history_id: history_id
				},
				beforeSend: function (xhr) {
					// collapse current error + success notifications
					jQuery(".alert.red-bg.collapse").collapse('hide');
					jQuery(".alert.green-bg.collapse").collapse('hide');

					lasso_update_popup.modal('show');
					lasso_helper.set_progress_bar( 98, 20 );
				}
			})
			.done(function (res) {
				res = res.data;

				if (res.status) {
					lasso_helper.successScreen('Successfully reverted content for "' + post_name + '".');
				} else {
					lasso_helper.errorScreen(res.msg);
				}
			})
			.fail(function (xhr, status, error) {
				if(xhr.lasso_error) {
					error = xhr.lasso_error;
				}
				lasso_helper.errorScreen(error);
			})
			.always(function(){
				lasso_helper.set_progress_bar_complete();
				setTimeout(function() {
					// Hide update popup by setTimeout to make sure this run after lasso_update_popup.modal('show')
					lasso_update_popup.modal('hide');
				}, 1000);
			});
	});
}