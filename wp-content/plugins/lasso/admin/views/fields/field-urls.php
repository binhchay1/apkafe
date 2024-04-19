<?php
/**
 * Group url
 *
 * @package Group url
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
					<div class="col-lg-1 text-center">Image</div>
					<div class="col-lg">Link Name</div>
					<div class="col-lg">Permalink</div>
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
