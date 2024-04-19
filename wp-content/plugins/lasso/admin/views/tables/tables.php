<?php
/**
 * Table
 *
 * @package Tables
 */

use Lasso\Classes\Config as Lasso_Config;
use Lasso\Classes\Helper as Lasso_Helper;

// phpcs:ignore
require LASSO_PLUGIN_PATH . '/admin/views/header-new.php';

?>
<section class="px-3 py-5">
	<div class="container">
		<!-- HEADER -->
		<?php require 'header.php'; ?>

		<div class="white-bg rounded shadow">
			<!-- TABLE HEADER -->
			<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray d-lg-block">
				<div class="row align-items-center">
					<div class="col-lg">Name</div>
					<div class="col-lg-4 pl-5">Products <label data-tooltip="These are the Lasso Links used in the table."><i class="far fa-info-circle light-purple"></i></label></div>
					<div class="col-lg-2 pl-5">Theme</div>
					<div class="col-lg-2 px-4 pl-5">Style <label data-tooltip="Is your product data displayed across rows or in columns."><i class="far fa-info-circle light-purple"></i></label></div>
					<div class="col-lg-1 text-center"><label data-tooltip="Where this table is used on your site.">Locations</label></div>
					<div class="col-lg-1 text-center"><label data-tooltip="Make a copy of this table.">Clone</label></div>
				</div>
			</div>
			<div class="onboarding_display_container pb-3">
				<div id="demo_display_box"></div>
				<div class="image_loading onboarding d-none"></div>
			</div>

			<!-- TABLE LIST -->
			<div id="tables" class="table-list">
			</div>

		</div>
		<!-- PAGINATION -->
		<div class="pagination row align-items-center no-gutters pb-3 pt-0"></div>
	</div>
</section>
<input id="page-name" type="hidden" value="<?php echo Lasso_Helper::get_page_name() ?>">
<?php Lasso_Config::get_footer(); ?>
