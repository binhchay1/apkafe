<?php
/**
 * URL links
 *
 * @package Lasso URL links
 */

use Lasso\Classes\Config as Lasso_Config;

// phpcs:ignore
require LASSO_PLUGIN_PATH . '/admin/views/header-new.php';
?>

<?php
$db_script = new Lasso_DB_Script();
$lasso_db  = new Lasso_DB();

$creation_scripts = array(
	'create_amazon_product_table',
	'create_amazon_tracking_table',
	'create_category_order_table',
	'create_content_table',
	'create_link_locations_table',
	'create_url_details_table',
	'create_url_issue_table',
	'create_url_issue_def_table',
	'create_revert_table',
	'create_tracked_keyword_table',
	'create_keyword_locations_table',
);
?>

<!-- LOGS SETTINGS -->
<section class="px-3 py-5">
	<div class="container">
		<!-- HEADER -->
		<?php require 'header.php'; ?>  

		<div class="row mb-5">
			<div class="col-lg">
				<div class="white-bg rounded shadow">

					<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray d-lg-block">
						<div class="row align-items-center">
							<div class="col-lg-6">Table Name</div>
							<div class="col">Output</div>
						</div>
					</div>

					<?php
					foreach ( $creation_scripts as $script ) {
						$results    = $db_script->{$script}();
						$table_name = $results[0];
						$output     = $results[1];

						// @codingStandardsIgnoreStart
						echo '
							<div class="p-4 hover-gray">
								<div class="row align-items-center">
									<div class="col-6"><strong>' . $table_name . '</strong></div>
									<div class="col">' . $output . '</div>
								</div>
							</div>
						';
						// @codingStandardsIgnoreEnd
					}
					?>

				</div>             
			</div>
		</div>        

	</div>
</section>

<?php Lasso_Config::get_footer(); ?>
