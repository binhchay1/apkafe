<?php
/**
 * URL links
 *
 * @package Lasso URL links
 */

use Lasso\Classes\Config as Lasso_Config;
use Lasso\Classes\Helper as Lasso_Helper;

$count_all_pages_posts = Lasso_Helper::count_all_pages_posts();
?>

<!-- GENERAL SETTINGS -->
<section class="px-3 py-5">
	<div class="container">

		<form class="lasso-admin-settings-form" autocomplete="off">
			<!-- SETTINGS -->
			<div class="row mb-5">
				<div class="col-lg">
					<div class="white-bg rounded shadow p-4 mb-lg-0 mb-5">
						<input type="hidden" name="count_all_pages_posts" value="<?php echo $count_all_pages_posts; ?>" />

						<!-- UNINSTALL LASSO -->
						<?php if ( ! Lasso_Process::are_all_processes_disabled() ): ?>
						<section>
							<h3>Uninstall Plugin</h3>
							<p>If you want to uninstall Lasso, we recommend that you remove all data attributes from all your links first. Click the button below to start the process.</p>
							<div class="form-group">
								<button id="remove_lasso_attributes" class="btn red-bg">Remove Lasso Attributes</button>
							</div>
							<div class="row lasso-remove-attributes">
							</div>
						</section>
						<?php else: ?>
						<section>
							<h3>Uninstall Plugin</h3>
							<p>Lasso attributes are removed.</p>
						</section>
						<?php endif; ?>
					</div>
				</div>

			</div>       

		</form>

	</div>
</section>

<script>
	function removeLassoAttributes() {
		jQuery('#remove-attributes-confirm').modal('hide');
		// Prepare data
		var data = {
			action: 'lasso_remove_lasso_attributes',
		};

		jQuery.post(ajaxurl, data, function (response) {
			lasso_helper.clearLoadingScreen();
			response = response.data;

			if(response.result) {
				lasso_helper.successScreen('Working... Please be patient.');
			} else {
				lasso_helper.errorScreen('The processes are running.');
			}

			return;
		})
		.fail(function (error) {
			lasso_helper.errorScreen(error);
		});
	}

	jQuery(document).ready(function() {
		// update the stats in General tab in Settings page (Database Status)
		lasso_helper.rebuild_database_background();

		jQuery('#remove_lasso_attributes').click(function(e) {
			e.preventDefault();
			jQuery('#remove-attributes-confirm').modal('show');
		});
	});
</script>

<!-- UNSAVED CHANGES MODAL -->
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/remove-attributes-confirm.php'; ?>

<?php
	Lasso_Helper::enqueue_script( 'lasso-helper', 'lasso-helper.js', array( 'jquery' ) );
	Lasso_Config::get_footer();
?>
