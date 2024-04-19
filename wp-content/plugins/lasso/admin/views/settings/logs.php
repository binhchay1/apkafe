<?php
/**
 * URL links
 *
 * @package Lasso URL links
 */

use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Config as Lasso_Config;

// phpcs:ignore
require LASSO_PLUGIN_PATH . '/admin/views/header-new.php';

$enable_logs = $lasso_options['enable_logs'] ?? false;
$enable_logs = $enable_logs ? 'checked="true"' : '';
?>

<!-- LOGS SETTINGS -->
<section class="px-3 py-5">
	<div class="container">
		<!-- HEADER -->
		<?php require 'header.php'; ?>  

		<form class="lasso-admin-settings-form">

			<!-- LOGS -->
			<div class="row mb-5">
				<div class="col-lg">
					<div class="white-bg rounded shadow p-4">
						<section class="mb-5">
							<h3>Enable Logs</h3>
							<br>
							<div class="form-group mb-4">
								<label class="toggle m-0 mr-1">
									<input type="checkbox" name="enable_logs" <?php echo $enable_logs; ?>>
									<span class="slider"></span>
								</label>
							</div>
						</section>

						<!-- IMPORT LOG -->
						<section class="mb-5">
							<h3>Import Log</h3>
							<p>All the plugin import connections.</p>
							<div class="form-group mb-4">
								<textarea readonly class="form-control light-gray-bg" rows="6"><?php echo Lasso_Setting::get_logs_content( 'import' ); ?></textarea>
							</div>
						</section>

						<!-- LINK DISCOVERY -->
						<section class="mb-5">
							<h3>Link Discovery</h3>
							<p>Every time Lasso discovers and stores a new link on your site.</p>
							<div class="form-group mb-4">
								<textarea readonly class="form-control light-gray-bg" rows="6"><?php echo Lasso_Setting::get_logs_content( 'link_database' ); ?></textarea>
							</div>
						</section>

						<!-- AMAZON PROCESSING -->
						<section class="mb-5">
							<h3>Amazon Processing</h3>
							<p>Each time Lasso updates your Amazon links.</p>
							<div class="form-group mb-4">
								<textarea readonly class="form-control light-gray-bg" rows="6"><?php echo Lasso_Setting::get_logs_content( 'cron' ); ?></textarea>
							</div>
						</section>

						<!-- AMAZON API ERRORS -->
						<section class="mb-5">
							<h3>Amazon Product API Errors</h3>
							<p>Any errors that occur when trying to hit the Amazon Product API.</p>
							<div class="form-group mb-4">
								<textarea readonly class="form-control light-gray-bg" rows="6"><?php echo Lasso_Setting::get_logs_content( 'amazon_api_error' ); ?></textarea>
							</div>
						</section>

						<!-- LINK ALERTS -->
						<section class="mb-5">
							<h3>Link Alerts</h3>
							<p>Every time Lasso runs a health check against a URL.</p>
							<div class="form-group mb-4">
								<textarea readonly class="form-control light-gray-bg" rows="6"><?php echo Lasso_Setting::get_logs_content( 'url_issue_check' ); ?></textarea>
							</div>
						</section>

						<!-- DUPLICATED SLUG -->
						<section class="mb-5">
							<h3>Lasso Duplicate Slug Changes</h3>
							<p>Any slug conflicts we detect when changing a permalink.</p>
							<div class="form-group mb-4">
								<textarea readonly class="form-control light-gray-bg" rows="6"><?php echo Lasso_Setting::get_logs_content( 'duplicated_slug' ); ?></textarea>
							</div>
						</section>

						<!-- SQL ERRORS -->
						<section class="mb-5">
							<h3>SQL Errors</h3>
							<p>Any database errors that occur.</p>
							<div class="form-group mb-4">
								<textarea readonly class="form-control light-gray-bg" rows="6"><?php echo Lasso_Setting::get_logs_content( 'sql_errors' ); ?></textarea>
							</div>
						</section>

					</div>             
				</div>
			</div>     

			<!-- SAVE CHANGES -->
			<div class="row align-items-center">
				<div class="col-lg text-lg-right text-center">
					<button class="btn save-change-tab">Save Changes</button>
				</div>
			</div>     
		</form>
	</div>
</section>

<?php Lasso_Config::get_footer(); ?>
