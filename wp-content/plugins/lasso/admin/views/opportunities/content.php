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

<!-- CONTENT -->
<input id="total-posts" class="d-none" value="0" />
<section class="px-3 py-5">
	<div class="container">
		<!-- HEADER -->
		<?php require 'header.php'; ?>

		<!-- CONTENT THRESHOLD -->
		<!--
		<div class="white-bg rounded shadow p-4 mb-4">
			<div class="row align-items-center">
				<div class="col-lg-2 text-right">
					Content with at least
				</div>
				<div class="col-lg-1">
					<form role="search" method="get">
						<input type="number" min="0" id="content-count" class="form-control text-center" value="3">
					</form>
				</div>
				<div class="col-lg-7 text-left">
					monetized links.
				</div>
				<div class="col-lg-2 text-right">
					<button id="show-content" target="_blank" class="btn learn-btn d-block">
						Show Content
					</button>
				</div>
			</div>
		</div>
		-->

		<!-- KEYWORD OPPORTUNTIES TABLE -->
		<div class="white-bg rounded shadow">
			<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray d-lg-block">
				<div class="row align-items-center">
					<div class="col-lg-2 sortable-col" data-order-by="post_modified" data-order-type="desc" data-order-init="desc">Last Updated</div>
					<div class="col-lg-5 sortable-col" data-order-by="detection_title" data-order-type="">
						Content
						<label data-tooltip="Where we found this keyword. Click name to view its links."><i class="far fa-info-circle light-purple"></i></label>
					</div>
					<div class="col-lg-4 sortable-col" data-order-by="detection_slug" data-order-type="">
						Permalink
						<label data-tooltip="The slug for this piece of content. Click to view it on your site."><i class="far fa-info-circle light-purple"></i></label>
					</div>
					<div class="col-lg-1 text-center sortable-col" data-order-by="count" data-order-type="" data-order-init="desc">
						Links
						<label data-tooltip="Number of links discovered in this piece of content."><i class="far fa-info-circle light-purple"></i></label>
					</div>
				</div>
			</div>

			<div id="report-content"></div>

		</div>     

		<!-- PAGINATION -->
		<div class="pagination row align-items-center no-gutters pb-3 pt-0"></div>

	</div>
</section>

<?php
	$page = $_GET['page'] ?? '';
	$template_variables = array( 'page' => $page );
	echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/assets/js/view-js.php', $template_variables );
?>

<?php Lasso_Config::get_footer(); ?>
