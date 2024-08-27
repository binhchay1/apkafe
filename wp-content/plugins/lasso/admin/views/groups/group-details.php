<?php
/**
 * Group details
 *
 * @package Group details
 */

use Lasso\Classes\Config as Lasso_Config;

// phpcs:ignore
require LASSO_PLUGIN_PATH . '/admin/views/header-new.php';
$urls            = 0;
$post_id         = 0;
$hide_delete_btn = false;

if ( isset( $_GET['urls'] ) ) {
	$urls = $_GET['urls'];
}

if ( isset( $_GET['post_id'] ) ) {
	$post_id         = $_GET['post_id'];
	$hide_delete_btn = true;
}
?>

<section class="py-5">
	<div class="container">

	<!-- TITLE & NAVIATION -->
		<?php require 'header.php'; ?>

		<form id="group-edit" autocomplete="off">
			<input type="hidden" id="post_id" name="" value="<?php echo $post_id; ?>">
			<input type="hidden" id="url_count" name="" value="<?php echo $urls; ?>">

			<!-- EDIT DETAILS -->
			<div class="white-bg rounded shadow p-5 mb-5">
				<div class="row">
					<div class="col-lg-6">
						<div class="row">
							<div class="col-lg">
								<input type="hidden" id="grp_id" name="grp_id" value="<?php echo $post_id; ?>">

								<!-- NAME -->
								<div class="form-group mb-4">
									<label><strong>Name</strong></label>
									<input type="text" class="form-control" id="grp_name" name="grp_name" value="<?php echo $term_name; ?>" placeholder="Group Name Goes Here">
								</div>
							</div>

							<!-- SLUG -->
							<?php if ( 0 !== $post_id ) { ?>
								<div class="col-lg-5">
									<div class="form-group mb-4">
										<label><strong>Slug</strong></label>
										<input type="text" class="form-control" id="grp_name" name="grp_name" value="<?php echo $slug; ?>" readonly>
									</div>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-6">
						<!-- DESCRIPTION -->
						<div class="form-group">
							<label><strong>Description</strong></label>
							<textarea type="text" class="form-control" id="grp_desc" name="grp_desc" rows="3"><?php echo $term_description; ?></textarea>
						</div>
					</div>
				</div>
			</div>

			<!-- SAVE & DETELE -->
			<div class="row align-items-center">
				<!-- SAVE CHANGES -->
				<div class="col-lg order-lg-2 text-lg-right text-center mb-4">
					<button class="btn" data-toggle="modal" id="group_save">Save Changes</button>
				</div>

				<!-- DELETE URL -->
				<?php if ( $hide_delete_btn ) { ?>
					<div class="col-lg text-lg-left text-center mb-4">
						<a id="group_delete_pop" href="#" class="red hover-red-text" data-toggle="modal"><i class="far fa-trash-alt"></i> Delete This Group</a>
					</div>
				<?php } ?>
			</div>
		</form>
	</div>
</section>

<!-- MODALS -->
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/group-delete.php'; ?>  

<div class="modal fade" id="url-save" data-backdrop="static" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content p-5 shadow text-center">
			<h3>Updating Group</h3>
			<p>Saving your changes now.</p>
			<div class="progress">
				<div class="progress-bar progress-bar-striped progress-bar-animated green-bg" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 75%"></div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="group_not_delete" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content text-center shadow p-5 rounded">
			<h2>Hold Up</h2>
			<p>You can't delete a Group if it has associated Links. Remove all Links using this Group first.</p>
			<div>
				<button type="button" class="btn" data-dismiss="modal">
					Ok
				</button>
			</div>
		</div>
	</div>
</div>

<?php Lasso_Config::get_footer(); ?>
