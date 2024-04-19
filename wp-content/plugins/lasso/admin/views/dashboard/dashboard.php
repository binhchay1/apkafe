<?php
/**
 * Dashboard
 *
 * @package Dashboard
 */

use Lasso\Classes\Config as Lasso_Config;
use Lasso\Classes\Helper as Lasso_Helper;

// phpcs:ignore
require LASSO_PLUGIN_PATH . '/admin/views/header-new.php'; 

$dashboard_link_count = $lasso_db->get_dashboard_link_count(); // phpcs:ignore
?>

<!-- DASHBOARD -->
<input id="total-posts" class="d-none" value="0" />
<section class="px-3 py-5">
	<div class="container min-height">

	<!-- TITLE BAR -->
		<div class="row align-items-center">

		<!-- TITLE -->
			<div class="col-lg-4 text-lg-left text-center mb-4">
				<h1 class="m-0 mr-2 d-inline-block align-middle">Dashboard</h1>
				<a href="https://support.getlasso.co/en/articles/3943735-how-to-use-the-affiliate-dashboard" target="_blank" class="btn btn-sm learn-btn">
					<i class="far fa-info-circle"></i> Learn
				</a>
			</div>

			<!-- FILTERS -->
			<div class="col-lg text-center large mb-4">
				<ul class="nav justify-content-center font-weight-bold">
					<li class="nav-item mx-3 red-tooltip d-none" id="total-broken-links-li" data-tooltip="See broken URLs">
						<a class="nav-link red hover-underline px-0" href="#" id="total-broken-links-a"><span id="total-broken-links"></span></a>
					</li>
					<li class="nav-item mx-3 orange-tooltip d-none" id="total-out-of-stock-li" data-tooltip="See out-of-stock products">
						<a class="nav-link orange hover-underline px-0" href="#" id="total-out-of-stock-a"><span id="total-out-of-stock"></span></a>
					</li>
					<li class="nav-item mx-3 green-tooltip d-none" id="total-opportunities-li" data-tooltip="See opportunities">
						<a class="nav-link green hover-underline px-0" href="#" id="total-opportunities-a"><!-- add class "active" --><span id="total-opportunities"></span></a>
					</li>
				</ul>
			</div>

			<!-- SEARCH -->
			<div class="col-lg-4 mb-4">
				<form role="search" method="get" id="links-filter" autocomplete="off">
					<div id="search-links">
						<input type="search" id="link-search-input" name="link-search-input" class="form-control" placeholder="Search All <?php echo $dashboard_link_count; ?> Links">
					</div>
				</form>
			</div>
		</div>
		<div class="row align-items-left mb-2 links-selected-wrapper" style="display: none">
			<div class="col">
				<span><strong id="total-links-selected"></strong></span>
				<button class="btn btn-sm white-bg small-pill btn-clear-selection black">Clear selection</button>
				<button class="btn btn-sm red-bg small-pill btn-show-modal-confirm-del-links">Delete</button>
			</div>
		</div>
		<!-- TABLE -->
		<div class="white-bg rounded shadow">

		<!-- TABLE HEADER -->
			<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray d-lg-block">
				<div class="row align-items-center">
					<div class="col-lg-1 text-center">Image</div>
					<div class="col-lg-6">Link Name</div>
					<div class="col-lg-3">Groups</div>
					<div class="col-lg-1 text-center">Alert</div>
					<div class="col-lg-1 text-center sortable-col"  data-order-by="count" data-order-type="" data-order-init="desc">Locations</div>
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

<?php
Lasso_Config::get_footer();
