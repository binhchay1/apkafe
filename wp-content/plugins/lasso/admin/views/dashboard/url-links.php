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

<input id="total-posts" class="d-none" value="0" />
<section class="py-5">
	<div class="container min-height">

		<!-- TITLE & NAVIATION -->
		<?php require 'header.php'; ?>            

		<!-- LINKS -->
		<div class="white-bg rounded shadow mb-5">

			<!-- TABLE HEADER -->
			<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray d-lg-block">
				<div class="row align-items-center">
					<div class="col-lg-7 sortable-col" data-order-by="post_title" data-order-type="">
						Content
						<label data-tooltip="This is the post or page this link was found in."><i class="far fa-info-circle light-purple"></i></label>
					</div>
					<div class="col-lg-4">
						Link with Context
						<label data-tooltip="Click to see a snippit of this link as it appears on your site."><i class="far fa-info-circle light-purple"></i></label>
					</div>
					<div class="col-lg-1 text-center">
						Type
					</div>
				</div>
			</div>

			<div id="report-content"></div>

		</div>

		<!-- PAGINATION -->
		<div class="pagination row align-items-center no-gutters py-3"></div>

	</div>
</section>

<!-- PREVIEW -->
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/link-preview.php'; ?>

<?php
	$page = $_GET['page'] ?? '';
	$template_variables = array( 'page' => $page );
	echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/assets/js/view-js.php', $template_variables );
?>

<?php Lasso_Config::get_footer(); ?>
