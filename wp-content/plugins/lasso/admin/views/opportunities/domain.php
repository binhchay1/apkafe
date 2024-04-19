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

<!-- REPORTS -->
<input id="total-posts" class="d-none" value="0" />
<section class="px-3 py-5">
	<div class="container">
		<!-- REPORT HEADER -->
		<?php require 'header.php'; ?>          

		<!-- DOMAIN TABLE -->
		<div class="white-bg rounded shadow">

			<!-- TABLE HEADER -->
			<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray d-lg-block">
				<div class="row align-items-center">
					<div class="col-lg-10 sortable-col" data-order-by="link_slug_domain">Domain</div>
					<div class="col-lg-1"></div>
					<div class="col-lg-1 text-center sortable-col" data-order-by="count" data-order-type="desc" data-order-init="desc">Links</div>
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
