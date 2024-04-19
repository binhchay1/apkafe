<?php
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting;
use Lasso\Classes\Setting_Enum;

$lasso_options = Setting::lasso_get_settings();

$amazon_default_tracking_country = !$lasso_options['amazon_default_tracking_country'] ?? '1';
$countries_dd = Lasso_Helper::get_countries_dd( $amazon_default_tracking_country, 'us' );
$amazon_tracking_id   = $lasso_options['amazon_tracking_id'] ?? '';
$amazon_access_key_id = $lasso_options['amazon_access_key_id'] ?? '';
$amazon_secret_key    = $lasso_options['amazon_secret_key'] ?? '';
$is_valid_tracking_id = empty( $amazon_tracking_id ) ? true : Lasso_Amazon_Api::validate_tracking_id( $amazon_tracking_id );

$tracking_id_class = $is_valid_tracking_id ? '' : ' invalid-field';
$tracking_id_invalid_class = $is_valid_tracking_id ? ' d-none' : '';
?>

<div id="imports" class="tab-item d-none" data-step="imports">
	<div class="progressbar_container">
		<?php 
            $params = array( 'active_step' => 6 );
            echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/onboarding/steps.php', $params ); 
        ?>
	</div>

	<div class="onboarding_header text-center mb-4">
		<h1 class="font-weight-bold d-inline-block align-middle">Imports</h1>
		&nbsp;<a href="https://support.getlasso.co/en/articles/4005802-how-to-import-links-from-another-plugin" target="_blank" class="btn btn-sm learn-btn">
			<i class="far fa-info-circle"></i> Learn
		</a>
	</div>

	<div class="row align-items-center">
		<!-- TITLE -->
		<div class="col-lg mb-4 text-lg-left text-center">
			<button id="btn-bulk-import" class="btn btn-sm">
				Bulk Import
			</button>
		</div>
	
		<div class="col-lg text-center large mb-4">
			<select name="filter_plugin" id="filter-plugin" class="form-control">
				<option value="">All Plugins</option>
			</select>
		</div>
	
		<!-- IMPORT SEARCH -->
		<div class="col-lg-4 mb-4">
			<form role="search" method="get" id="links-filter" autocomplete="off">
				<div id="search-links">
					<input type="search" id="link-search-input" name="link-search-input" class="form-control" placeholder="Search URLs to Import">
				</div>
			</form>
		</div>
	
	</div>

	<!-- LINKS TO IMPORT -->
	<div class="white-bg rounded shadow">            
		<div class="px-4 pt-4 pb-2 font-weight-bold dark-gray d-lg-block">
			<div class="row align-items-center">
				<div class="col-4">Link Title</div>
				<div class="col">Import Target</div>
				<div class="col-1">Plugin</div>
				<div class="col-1 text-center"></div>
				<div class="col-1 text-center"></div>
			</div>
		</div>

		<div id="report-content"></div>

	</div>

	<!-- PAGINATION -->
	<div class="pagination row align-items-center no-gutters pb-3 pt-0"></div>
	
	<!-- SAVE CHANGES -->
	<div class="row align-items-center">
		<div class="col-lg text-lg-right text-center">
			<button class="btn next-step" >Continue &rarr;</button>
		</div>
	</div>

	<!-- MODALS -->
	<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/import-all-confirm.php'; ?>
	<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/revert-all-confirm.php'; ?>
	<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/import-confirm.php'; ?>
	<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/revert-confirm.php'; ?>
	<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/url-save.php'; ?>

	<?php
		$template_variables = array( 'page' => Setting_Enum::PAGE_IMPORT );
		echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/assets/js/view-js.php', $template_variables );
	?>

</div>
