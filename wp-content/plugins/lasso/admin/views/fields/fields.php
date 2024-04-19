<?php
/**
 * Fields
 *
 * @package Groups
 */

use Lasso\Classes\Config as Lasso_Config;
use Lasso\Classes\Helper as Lasso_Helper;

// phpcs:ignore
require LASSO_PLUGIN_PATH . '/admin/views/header-new.php'; 
?>

<!-- GROUPS -->
<input id="total-posts" class="d-none" value="0" />
<section class="px-3 py-5">
	<div class="container min-height">

	<!-- TITLE BAR -->
		<div class="row align-items-center">

		<!-- TITLE -->
			<div class="col-lg text-lg-left text-center mb-4">
				<h1 class="m-0 mr-2 d-inline-block align-middle">Fields</h1>
				<a href="https://support.getlasso.co/en/articles/5150630-how-to-use-fields" target="_blank" class="btn btn-sm learn-btn">
					<i class="far fa-info-circle"></i> Learn
				</a>
				<a href="edit.php?post_type=lasso-urls&page=field-details" class="btn ml-1 btn-sm">
					<i class="far fa-plus-circle"></i> Add New Field
				</a>
			</div>

			<!-- SEARCH -->
			<div class="col-lg-4 mb-4">
				<form role="search" method="get" id="links-filter" autocomplete="off">
					<div id="search-links">
						<input type="search" id="link-search-input" name="link-search-input" class="form-control" placeholder="Search Fields">
					</div>
				</form>
			</div>
		</div>

		<!-- TABLE -->
			<div class="white-bg rounded shadow">
				
				<!-- TABLE HEADER -->
				<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray d-lg-block">
					<div class="row align-items-center">
						<div class="col-lg">Name</div>
						<div class="col-lg-1 text-center">Type</div>
						<div class="col-lg-1 text-center">Usage</div>
					</div>
				</div>   
	
				<!-- FIELD ROW -->
				<div id="report-content" class="pb-4 px-4"></div>
	
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
