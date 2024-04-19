<?php
/**
 * Post content histories
 *
 * @package Post content histories
 */

use Lasso\Classes\Config as Lasso_Config;
use Lasso\Classes\Helper as Lasso_Helper;

// phpcs:ignore
require LASSO_PLUGIN_PATH . '/admin/views/header-new.php'; 
?>

<!-- POST CONTENT HISTORY -->
<input id="total-posts" class="d-none" value="0" />
<section class="px-3 py-5" id="post-content-history-list">
	<div class="container min-height">

	<!-- TITLE BAR -->
		<div class="row align-items-center">

		<!-- TITLE -->
			<div class="col-lg text-lg-left text-center mb-4">
				<h1 class="m-0 mr-2 d-inline-block align-middle">Post Content History</h1>
			</div>

			<!-- SEARCH -->
			<div class="col-lg-4 mb-4">
				<form role="search" method="get" id="links-filter" autocomplete="off">
					<div id="search-links">
						<input type="search" id="link-search-input" name="link-search-input" class="form-control" placeholder="Search History">
					</div>
				</form>
			</div>
		</div>

		<!-- TABLE -->
		<div class="white-bg rounded shadow">

		<!-- TABLE HEADER -->
			<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray d-lg-block">
				<div class="row align-items-center">
					<div class="col-lg-1 text-center sortable-col" data-order-by="p.ID" data-order-type="" data-order-init="asc">Post ID</div>
					<div class="col-lg-5 sortable-col" data-order-by="p.post_title" data-order-type="" data-order-init="asc">Post Name</div>
					<div class="col-lg-2 sortable-col" data-order-by="p.post_type" data-order-type="" data-order-init="asc">Post Type</div>
					<div class="col-lg-2 text-center sortable-col active" data-order-by="h.updated_date" data-order-type="desc" data-order-init="asc">Modified date</div>
					<div class="col-lg-2 text-center">Action</div>
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

<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/url-save.php'; ?>
<?php Lasso_Config::get_footer(); ?>
