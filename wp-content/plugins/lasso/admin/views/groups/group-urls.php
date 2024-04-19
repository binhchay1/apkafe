<?php
/**
 * Group url
 *
 * @package Group url
 */

use Lasso\Classes\Config as Lasso_Config;
use Lasso\Classes\Group as Lasso_Group;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;

/** @var int $post_id */

// phpcs:ignore
require LASSO_PLUGIN_PATH . '/admin/views/header-new.php';

$urls = $_GET['urls'] ?? '';
$lasso_group = Lasso_Group::get_by_id( $post_id );
?>

<input id="total-posts" class="d-none" value="0" />
<section class="py-5">
	<div class="container">

	<!-- TITLE & NAVIGATION -->
		<?php require 'header.php'; ?>
		
		<input type="hidden" id="url_count" name="" value="<?php echo $urls; ?>">
		<input type="hidden" id="post_id" name="" value="<?php echo isset( $lasso_group ) ? $lasso_group->get_id() : '' ?>">

		<!-- LINKS -->
		<div class="white-bg rounded shadow mb-5">

		<!-- TABLE HEADER -->
			<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray d-lg-block">
				<div class="row align-items-center">
					<div class="col-lg-1 text-center">Image</div>
					<div class="col-lg">Link Name</div>
					<div class="col-lg">Permalink</div>
				</div>
			</div>

			<div id="report-content"></div>

		</div>

		<!-- PAGINATION -->
		<div class="d-none pagination row align-items-center no-gutters pb-3 pt-0"></div>

		<!-- SAVE & DETELE -->
		<div class="row align-items-center">
			<!-- DELETE URL -->
			<div class="col-lg text-lg-left text-center mb-4">
				<a id="group_delete_pop" href="#" class="red hover-red-text" data-toggle="modal"><i class="far fa-trash-alt"></i> Delete This Group</a>
			</div>
		</div>    

	</div>
</section>

<!-- MODALS -->
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/group-delete.php'; ?>

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

<!-- SORTABLE JAVASCRIPT -->
<script>
	jQuery( function() {
		jQuery( "#report-content" ).sortable({
			cursor: "move",
			stop: function() {
				var selectedData = new Array();
				jQuery('#report-content>div').each(function() {
                    jQuery(this).removeAttr("style");
					selectedData.push(jQuery(this).attr("data-post-id"));
				});
				updateOrder(selectedData, '<?php echo $slug; ?>');
			}
		});
		function updateOrder(data, set) {
			jQuery.ajax({
				url: lassoOptionsData.ajax_url,
				type: 'post',
				data: {
					action: 'lasso_save_category_positions',
					set: set,
					data: data,
					group_id: '<?php echo $_GET['post_id'] ?? ''; ?>',
				}
			});
		}
		jQuery( "#report-content" ).disableSelection();
	} );
</script>
<?php
Lasso_Html_Helper::render_template( LASSO_PLUGIN_PATH . '/admin/views/modals/link-monetize.php', array(
	'modal_title' => 'Add to Group'
), false );
$page = $_GET['page'] ?? '';
$template_variables = array( 'page' => $page );
echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/assets/js/view-js.php', $template_variables );
?>

<?php Lasso_Config::get_footer(); ?>
