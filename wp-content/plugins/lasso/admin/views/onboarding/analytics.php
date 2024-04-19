<?php
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting;

$lasso_options = Setting::lasso_get_settings();

$enable_google_tracking_checked = $lasso_options['analytics_enable_click_tracking'] ? 'checked="true"' : '';
$enable_google_tracking_disabled = empty( $enable_google_tracking_checked ) ? 'disabled' : '';

$ip_anonymization_checked  = $lasso_options['analytics_enable_ip_anonymization'] ? 'checked="true"' : '';
$ip_anonymization_disabled = empty( $enable_google_tracking_checked ) ? 'disabled' : '';

$send_pageview_checked  = $lasso_options['analytics_enable_send_pageview'] ? 'checked="true"' : '';
$send_pageview_disabled = empty( $enable_google_tracking_checked ) ? 'disabled' : '';
?>

<div id="analytics" class="tab-item d-none" data-step="analytics">
	<div class="progressbar_container">
		<?php 
            $params = array( 'active_step' => 5 );
            echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/onboarding/steps.php', $params ); 
        ?>
	</div>

	<div class="onboarding_header text-center mb-4">
		<h1 class="font-weight-bold d-inline-block align-middle">Google Analytics</h1>
		&nbsp;<a href="https://support.getlasso.co/en/articles/3182309-how-to-connect-lasso-to-google-analytics-for-click-tracking" target="_blank" class="btn btn-sm learn-btn">
			<i class="far fa-info-circle"></i> Learn
		</a>
	</div>

	<form class="lasso-admin-settings-form" autocomplete="off">
		<!-- GOOGLE ANALYTICS -->
		<section class="mb-5 w-50 mt-0 mb-0 ml-auto mr-auto">
			<div class="form-group">
				<div class="form-row align-items-center mb-3">
					<div class="col mb-lg-0 mb-3">
						<input name="analytics_google_tracking_id" type="text" class="form-control" placeholder="Tracking ID or Property ID" value="<?php echo $lasso_options['analytics_google_tracking_id']; ?>">
					</div>
				</div>
				<div class="form-group align-items-center">
					<div class="" id="ga-tracking-toggle">
						<label class="toggle m-0 mb-3 mr-1">
							<input type="checkbox" name="analytics_enable_click_tracking" <?php echo $enable_google_tracking_checked; ?> <?php echo $enable_google_tracking_disabled; ?>>
							<span class="slider"></span>
						</label>
						<label class="m-0" data-tooltip="Enable to begin tracking Lasso Link clicks in Google Analytics.">Click Tracking <i class="far fa-info-circle light-purple"></i></label>
					</div>
					<div class="" id="ga-tracking-toggle">
						<label class="toggle m-0 mb-3 mr-1">
							<input type="checkbox" name="analytics_enable_send_pageview" <?php echo $send_pageview_checked; ?> <?php echo $send_pageview_disabled; ?>>
							<span class="slider"></span>
						</label>
						<label class="m-0" data-tooltip="If you have Google Analytics installed outside of Lasso, keep this disabled.">Pageview <i class="far fa-info-circle light-purple"></i></label>
					</div>
					<div class="" id="ga-ip-anonymization-toggle">
						<label class="toggle m-0 mr-1">
							<input type="checkbox" name="analytics_enable_ip_anonymization" <?php echo $ip_anonymization_checked; ?> <?php echo $ip_anonymization_disabled; ?>>
							<span class="slider"></span>
						</label>
						<label class="m-0" data-tooltip="When enabled, we won't report visitors' IP addresses for GDPR compliance.">IP Anonymization <i class="far fa-info-circle light-purple"></i></label>
					</div>
				</div>
			</div>
		</section>
	</form>

	<!-- SAVE CHANGES -->
	<div class="row align-items-center">
		<div class="col-lg text-lg-right text-center">
		<button class="btn btn-outline-dark bg-white text-dark next-step">Skip &rarr;</button>
			<button class="btn btn-save-analytics next-step" >Save and Continue &rarr;</button>
		</div>
	</div>
</div>
