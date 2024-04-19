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

<!-- KEYWORDS -->
<input id="total-posts" class="d-none" value="0" />
<section class="px-3 py-5">
	<div class="container">
		<!-- HEADER -->
		<?php require 'header.php'; ?>

		<!-- KEYWORD ADD -->
		<div class="white-bg rounded shadow p-4 mb-4">
			<div class="row align-items-center">
				<div class="col-lg">
					<form role="search" method="get" autocomplete="off">
						<input type="text" id="add_keyword_text" class="form-control" placeholder="Enter a keyword or phrase">
					</form>
				</div>

				<div class="col-lg-2 text-center">
					<a id="add_keyword_button" class="btn d-block">
						<i class="far fa-plus-circle"></i> Add Keyword
					</a>
				</div>

				<div class="col-lg-2 text-center">
					<a class="btn learn-btn d-block" data-toggle="modal" data-target="#saved-keywords">
						Saved Keywords
					</a>
				</div>
			</div>
		</div>

		<!-- KEYWORD OPPORTUNITIES TABLE -->
		<div class="white-bg rounded shadow">
			<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray d-lg-block">
				<div class="row align-items-center">
					<div class="col-lg-4 sortable-col" data-order-by="anchor_text" data-order-type="">
						Keyword with Context
						<label data-tooltip="A keyword we discovered in your content. Click link to view context."><i class="far fa-info-circle light-purple"></i></label>
					</div>
					<div class="col-lg-7 sortable-col" data-order-by="post_title" data-order-type="">
						Content
						<label data-tooltip="Where we found this keyword. Click link to edit."><i class="far fa-info-circle light-purple"></i></label>
					</div>
					<div class="col-lg-1 text-center">Monetize</div>
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
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/saved-keywords.php'; ?>
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/dismiss-opportunity.php'; ?>

<?php
	$page = $_GET['page'] ?? '';
	$template_variables = array( 'page' => $page );
	echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/assets/js/view-js.php', $template_variables );
?>

<?php Lasso_Config::get_footer(); ?>
