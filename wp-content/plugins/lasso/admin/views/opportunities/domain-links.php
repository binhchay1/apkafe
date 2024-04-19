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

<?php
	$domain = isset( $_GET['search'] ) && $_GET['search'] != 'example.com' ? $_GET['search'] : 'example.com';
?>

<input id="total-posts" class="d-none" value="0" /> 
<section class="py-5">
	<div class="container min-height">
		<!-- REPORT HEADER -->
		<?php require 'header.php'; ?>           

		<!-- DOMAINS -->
		<div class="white-bg rounded shadow mb-5">

		<!-- TABLE HEADER -->
			<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray">
				<div class="row align-items-center">
					<div class="col-lg-3 sortable-col" data-order-by="link_slug" data-order-type="">URL</div>
					<div class="col-lg-5 sortable-col" data-order-by="post_title" data-order-type="">Content</div>
					<div class="col-lg-2 sortable-col">Link</div>
					<div class="col-lg-1 text-center">Type</div>
					<div class="col-lg-1 text-center">Monetize</div>
				</div>
			</div>

			<div id="report-content"></div>

		</div>

		<!-- PAGINATION -->
		<div class="pagination row align-items-center no-gutters pb-3 pt-0"></div>

	</div>
</section>

<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/link-monetize.php'; ?>
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/link-preview.php'; ?>

<?php
	$page = $_GET['page'] ?? '';
	$template_variables = array( 'page' => $page );
	echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/modals/url-quick-detail.php', $template_variables );
	echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/assets/js/view-js.php', $template_variables );
?>

<?php Lasso_Config::get_footer(); ?>
