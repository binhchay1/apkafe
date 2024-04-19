<?php
/**
 * Post content history detail
 *
 * @package Lasso Post content history detail
 */

use Lasso\Classes\Config as Lasso_Config;
use Lasso\Classes\Helper as Lasso_Helper;

// phpcs:ignore
require LASSO_PLUGIN_PATH . '/admin/views/header-new.php';

$history_id           = isset( $_GET['id'] ) && $_GET['id'] > 0 ? $_GET['id'] : 0;
$lasso_db             = new Lasso_DB();
$post_content_history = $lasso_db->get_post_content_history_detail( intval( $history_id ) );

if ( ! $post_content_history ) {
	wp_redirect( 'edit.php?post_type=' . LASSO_POST_TYPE . '&page=dashboard' ); // phpcs:ignore
	exit;
}

$post_content_history = Lasso_Helper::format_post_content_history_data( $post_content_history );
$old_value            = json_encode($post_content_history->old_value);
$new_value            = json_encode($post_content_history->new_value);
?>

<section class="py-5" id="content-post-history-detail">
	<div class="container-fluid">
		<div class="p-lr-20">
			<!-- TITLE -->
			<div class="row align-items-center mb-3">
				<div class="col-lg text-lg-left text-center">
					<h1 class="m-0 mr-2 d-inline-block align-middle">View Post Content History</h1>
				</div>
			</div>
		</div>
	</div>

	<div class="container-fluid">
		<div class="p-lr-20">
			<div class="white-bg rounded shadow p-4 mb-4">
				<p>
					<strong>Post Name:</strong> <?php echo $post_content_history->post_title; ?>
					&nbsp;
					<a href="<?php echo $post_content_history->edit_url; ?>" class="light-purple hover-purple-text small cursor-pointer" target="_blank">
						<i class="far fa-filter"></i>
					</a>
					<a href="<?php echo $post_content_history->view_url; ?>" class="light-purple hover-purple-text small cursor-pointer" target="_blank">
						<i class="far fa-external-link-alt"></i>
					</a>
				</p>
				<p><strong>Post ID:</strong> <?php echo $post_content_history->history_id; ?></p>
				<p><strong>Post Type:</strong> <?php echo $post_content_history->post_type; ?></p>
				<p><strong>Modified Date:</strong> <?php echo str_replace('<br>', ' ', $post_content_history->updated_date); ?></p>

				<div id="diff-wrapper">
					<div id="diff-caption">Content changed</div>
					<div id="out-put"></div>
				</div>
			</div>


			<div class="row align-items-center">
				<!-- SAVE CHANGES -->
				<div class="col-lg text-lg-right text-center">
					<button class="btn red-bg post-content-revert d-none"
							data-history-id="<?php echo $post_content_history->history_id; ?>"
							data-post-name="<?php echo $post_content_history->post_title; ?>">
						Revert
					</button>
				</div>
			</div>
		</div>
	</div>


</section>

<script>
	var dmp = new diff_match_patch();

	function launch_diff() {
		var old_value  = <?php echo $old_value ?>;
		var new_value  = <?php echo $new_value ?>;
		var diff_format = dmp.diff_main(old_value, new_value);

		dmp.diff_cleanupSemantic(diff_format);
		var ds = dmp.diff_prettyHtml(diff_format);
		ds = ds.replace(/&para;/g, ''); // Remove break line UI
		document.getElementById('out-put').innerHTML = ds;
	}

	launch_diff();

	jQuery(document).ready(function () {
		jQuery(".post-content-revert").removeClass('d-none');
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
	});
</script>

<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/url-save.php'; ?>
<?php Lasso_Config::get_footer(); ?>
