<?php
/**
 * Installation
 *
 * @package Installation
 */

use Lasso\Classes\Config as Lasso_Config;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting as Lasso_Setting;

$lasso_options = Lasso_Setting::lasso_get_settings();
	
$user_email = get_option( 'admin_email' ); // phpcs:ignore
$user_email = get_option( 'lasso_license_email', $user_email ); // phpcs:ignore

$should_show_import_step = Lasso_Helper::should_show_import_page();
?>

<script>
    // Lasso Segment initial
    !function(){var analytics=window.analytics=window.analytics||[];if(!analytics.initialize)if(analytics.invoked)window.console&&console.error&&console.error("Segment snippet included twice.");else{analytics.invoked=!0;analytics.methods=["trackSubmit","trackClick","trackLink","trackForm","pageview","identify","reset","group","track","ready","alias","debug","page","once","off","on","addSourceMiddleware","addIntegrationMiddleware","setAnonymousId","addDestinationMiddleware"];analytics.factory=function(e){return function(){var t=Array.prototype.slice.call(arguments);t.unshift(e);analytics.push(t);return analytics}};for(var e=0;e<analytics.methods.length;e++){var key=analytics.methods[e];analytics[key]=analytics.factory(key)}analytics.load=function(key,e){var t=document.createElement("script");t.type="text/javascript";t.async=!0;t.src="https://cdn.segment.com/analytics.js/v1/" + key + "/analytics.min.js";var n=document.getElementsByTagName("script")[0];n.parentNode.insertBefore(t,n);analytics._loadOptions=e};analytics._writeKey=lassoOptionsData.segment_analytic_id;analytics.SNIPPET_VERSION="4.13.2";
        analytics.load(lassoOptionsData.segment_analytic_id);
        analytics.page(document.title, {
            user_email: '<?php echo $user_email ?>'
        });
    }}();
</script>

<input id="total-posts" class="d-none" value="0" />
<section class="purple-bg pt-3 pb-5 min-vh-100">
	<div id="onboarding_container" class="container container-sm">

	<!-- LOGO -->
		<div class="pb-5">
			<div class="logo-large mx-auto">
				<img src="<?php echo LASSO_PLUGIN_URL; ?>admin/assets/images/lasso-logo.svg">
			</div>
		</div>

		<div class="mt-5 mx-auto white-bg shadow rounded p-5">

			<!-- ACTIVATE LICENSE KEY - "container container-sm py-5" -->
			<div id="activate" class="tab-item text-center">
				<h1 class="font-weight-bold">Let's Get Started</h1>
				<p>First, enter your license key. You can find it on your <a href="https://app.getlasso.co/account" class="purple underline" target="_blank">Lasso Account Page</a>.</p>

				<div class="form-group mb-4">
					<div class="collapse orange" id="activate-error"><label>This license key doesn't work. Double check and try again.</label></div>
					<input type="text" name="license_serial" class="form-control" id="license" value="<?php echo $lasso_options['license_serial']; ?>" placeholder="Enter your license key">
				</div>

				<button id="activate-license" class="btn green-bg white badge-pill px-3 shadow font-weight-bold hover-green hover-down">
					Activate Lasso
				</button>
			</div>
			
			<!-- CHOOSE A DEFAULT STYLE - "container" -->
			<?php echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/onboarding/theme.php' ); ?>
			
			<!-- CUSTOMIZE DISPLAY - "container" -->
			<?php echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/onboarding/display.php' ); ?>

			<!-- AMAZON -->
			<?php echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/onboarding/amazon.php' ); ?>

			<!-- ANALYTICS -->
			<?php echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/onboarding/analytics.php' ); ?>

			<!-- IMPORTS -->
			<?php 
				if ( $should_show_import_step ) {
					echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/onboarding/imports.php' ); 
				}
			?>
			
			<!-- DONE -->
			<?php echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/onboarding/done.php' ); ?>
		</div>

	</div>
</section>
<script>
	jQuery(document).ready(function() {
		jQuery("form").submit(function(e) {
			e.preventDefault();
		});
	});
</script>

<!-- LICENSE ACTIVATION MODAL -->
<?php echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/modals/license-activation.php' ); ?>
<?php Lasso_Config::get_footer(); ?>
