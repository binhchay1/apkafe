<?php
/**
 * Import
 *
 * @package Import
 */

use Lasso\Classes\Config as Lasso_Config;
use Lasso\Classes\Helper as Lasso_Helper;

use Lasso\Pages\Install\Ajax as Lasso_Install_Ajax;

// phpcs:ignore
require LASSO_PLUGIN_PATH . '/admin/views/header-new.php';

// ? Validate to lock screen until scan all posts first time  was finished. Conditions: Is the first activate + Does not scan all posts successful
$is_scanning_all_posts_first_time = get_option( Lasso_Install_Ajax::ACTIVATE_FIRST_TIME_KEY ) && ! get_option( Lasso_Process_Link_Database::SCAN_ALL_POSTS_SUCCESSFUL_KEY );
$hide_imports                     = $is_scanning_all_posts_first_time ? 'style="display:none !important;"' : '';
?>

<!-- IMPORT/EXPORT SETTINGS -->
<input id="total-posts" class="d-none" value="0" /> 
<section class="px-3 py-5">
	<div class="container">
		<!-- HEADER -->
		<?php if( ! $is_scanning_all_posts_first_time ) { ?>
			<?php require 'header.php'; ?>
		<?php } ?>


		<!-- LINKS TO IMPORT -->
		<div class="white-bg rounded shadow">            
			<?php if( $is_scanning_all_posts_first_time ) { ?>
			<!-- IMPORT/EXPORT -->
			<div class="row mb-5">
				<div class="col-lg">
					<div class="white-bg rounded shadow p-4 mb-lg-0 mb-5">
						<div class="text-center p-5">
							<h2>Check back soon.</h2>
							<p>Lasso is indexing your links. When this is complete, you can import links from other plugins.</p>
							<div class="ls-loader"></div>
						</div>
					</div>             
				</div>  
			</div>
			<?php } ?>

			<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray d-lg-block" <?php echo $hide_imports; ?>>
				<div class="row align-items-center">
					<div class="col-4">Link Title</div>
					<div class="col">Import Target</div>
					<div class="col-1">Source</div>
					<div class="col-1 text-center"></div>
					<div class="col-1 text-center"></div>
				</div>
			</div>

			<div id="report-content" <?php echo $hide_imports; ?>></div>

		</div>

		<!-- PAGINATION -->
		<div class="pagination row align-items-center no-gutters pb-3 pt-0" <?php echo $hide_imports; ?>></div>

	</div>
</section>

<!-- MODALS -->
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/import-all-confirm.php'; ?>
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/revert-all-confirm.php'; ?>
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/import-confirm.php'; ?>
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/revert-confirm.php'; ?>
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/url-save.php'; ?>

<?php
	$page = $_GET['page'] ?? '';
	$template_variables = array( 'page' => $page );
	echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/assets/js/view-js.php', $template_variables );
?>

<?php Lasso_Config::get_footer(); ?>
