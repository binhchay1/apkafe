<?php
/**
 * URL links
 *
 * @package Lasso URL links
 */

use Lasso\Classes\Config as Lasso_Config;
use Lasso\Classes\Helper as Lasso_Helper;

// phpcs:ignore
require LASSO_PLUGIN_PATH . '/admin/views/header-new.php';
?>

<!-- LINKS -->
<input id="total-posts" class="d-none" value="0" />
<section class="px-3 py-5">
	<div class="container">
		<!-- HEADER -->
		<?php require 'header.php'; ?>

		<!-- LINK OPPORTUNTIES TABLE -->
		<div class="white-bg rounded shadow">
			<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray d-lg-block">
				<div class="row align-items-center">
					<div class="col-lg-4 sortable-col" data-order-by="link_slug_original" data-order-type="">
						URL
						<label data-tooltip="This is a URL found on your site that may be able to be monetized."><i class="far fa-info-circle light-purple"></i></label>
					</div>
					<div class="col-lg-4 sortable-col" data-order-by="post_title" data-order-type="">
						Content
						<label data-tooltip="Where we found this URL. Click link to edit."><i class="far fa-info-circle light-purple"></i></label>
					</div>
					<div class="col-lg-2 sortable-col" data-order-by="lasso_suggestion" data-order-type="">
						Suggestion
						<label data-tooltip="The name of a Lasso Link created with the same domain as this URL. Click link to edit."><i class="far fa-info-circle light-purple"></i></label>
					</div>
					<div class="col-lg-1 text-center">
						Type
						<label data-tooltip="Click to see a snippit of this link as it appears on your site."><i class="far fa-info-circle light-purple"></i></label>
					</div>
					<div class="col-lg-1 text-center">
						Monetize
					</div>
				</div>
			</div>

			<div id="report-content"></div>

		</div>     

		<!-- PAGINATION -->
		<div class="pagination row align-items-center no-gutters pb-3 pt-0"></div>

	</div>
</section>

<!-- MODALS -->
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/link-monetize.php'; ?>
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/link-preview.php'; ?>
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/dismiss-opportunity.php'; ?>

<?php
	$page = $_GET['page'] ?? '';
	$template_variables = array( 'page' => $page );
	echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/assets/js/view-js.php', $template_variables );
?>

<?php Lasso_Config::get_footer(); ?>
