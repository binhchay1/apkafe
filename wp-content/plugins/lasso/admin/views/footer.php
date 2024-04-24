<?php
/**
 * Declare class Lasso_Process_Import_All
 *
 * @package Lasso_Process_Import_All
 */

use Lasso\Classes\Enum;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Setting_Enum;
use Lasso\Models\Table_Details;

$page = $_GET['page'] ?? ''; // phpcs:ignore

if ( in_array( $page, array( 'install' ), true ) ) {
	$footer_bg = 'purple-bg';
} else {
	$footer_bg = '';
}

$lasso_db       = new Lasso_DB();
$lasso_settings = new Lasso_Setting();
$lasso_options  = Lasso_Setting::lasso_get_settings();
$report_stats   = $lasso_settings->lasso_get_stats_in_report_page();

// ? import
$plugin_stats = $lasso_settings->check_plugins_for_import_and_deactivate();
extract( $plugin_stats ); // phpcs:ignore
?>

<!-- Sentry -->
<script
	src="https://browser.sentry-cdn.com/7.21.1/bundle.tracing.min.js"
	integrity="sha384-1MTgFwp9zb14K+BOaYA9Y4H/I3s3IUNIQRPYEHgR19NS1rl8d5KCjP9NfM7o1Bnz"
	crossorigin="anonymous"
></script>

<h6 class="text-center pb-4 <?php echo esc_html( $footer_bg ); ?>" style="margin-bottom: 20px;">
	<span class="badge rounded purple-bg white font-weight-normal py-2 px-3">
		<?php print 'Version ' . LASSO_VERSION; // phpcs:ignore ?>
	</span>
</h6>

<!-- NOTIFICATIONS -->
<script>
<?php
// ? show notification for plugin update
if ( isset( get_plugin_updates()[ LASSO_PLUGIN_BASE_NAME ] ) ) {
	$update_link = $lasso_settings->generate_update_link();
	?>
		var html = `<?php include LASSO_PLUGIN_PATH . '/admin/views/notifications/lasso-update-available.php'; ?>`;
		jQuery("#lasso_notifications").append(html);
	<?php
}

// ? Show notification for import plugin
if ( '' !== $plugins_for_import && ! $lasso_options['general_disable_notification'] && Setting_Enum::PAGE_IMPORT !== $page ) { // phpcs:ignore
	?>
		var html = `<?php include LASSO_PLUGIN_PATH . '/admin/views/notifications/import-suggestion.php'; ?>`;
		jQuery("#lasso_notifications").append(html);
		<?php
}

// ? Show notification for import plugin
$lasso_enable_auto_amazon_notification = intval( get_option( 'lasso_enable_auto_amazon_notification', 1 ) );
if ( ! $lasso_options['auto_monetize_amazon'] && 0 !== $lasso_enable_auto_amazon_notification ) { // phpcs:ignore
	?>
		var html = `<?php include LASSO_PLUGIN_PATH . '/admin/views/notifications/auto-amazon.php'; ?>`;
		jQuery("#lasso_notifications").append(html);
		jQuery(document).ready(function() {
			jQuery("#close-auto-amazon").click(function(e) {
				jQuery.ajax({
					url: '<?php echo Lasso_Helper::get_ajax_url(); // phpcs:ignore ?>',
					type: 'post',
					data: {
						action: 'lasso_disable_auto_amazon_notification',
					},
				})
				.done(function (res) {
					jQuery('#auto-amazon').hide(500);
				});
			});
		});
		<?php
}

// Is Import Process running?
if ( $report_stats['import'] >= 0 ) {
	?>
		var html = `<?php include LASSO_PLUGIN_PATH . '/admin/views/notifications/import-running.php'; ?>`;
		jQuery("#lasso_notifications").append(html);
		<?php
}

// Is Link Build running?
if ( $report_stats['build'] >= 0 ) {
	?>
		var html = `<?php include LASSO_PLUGIN_PATH . '/admin/views/notifications/link-build-running.php'; ?>`;
		jQuery("#lasso_notifications").append(html);
		<?php
}

// ? Show notification GA Tracking
if ( get_option( 'lasso_ga_tracking', true ) ) {
	if ( ! $lasso_options['analytics_enable_click_tracking'] ) {
		?>
		if( !jQuery("#amazon-not-configured").length &&
			!jQuery("#amazon-url-detected").length &&
			!jQuery("#lasso-update-available").length &&
			!jQuery("#license-expired").length &&
			!jQuery("#link-build-running").length ) {

			var html = `<?php include LASSO_PLUGIN_PATH . '/admin/views/notifications/ga-tracking-detected.php'; ?>`;
			jQuery("#lasso_notifications").append(html);
		}
		<?php
	}
}

// Is Link Build running after importing?
if ( '1' === get_option( Enum::OPTION_ENABLE_SCAN_NOTICE_AFTER_IMPORT, 0 ) ) {
	?>
		var html = `<?php include LASSO_PLUGIN_PATH . '/admin/views/notifications/link-build-running-after-importing.php'; ?>`;
		jQuery("#lasso_notifications").append(html);
		<?php
}
?>
</script>

<!-- JS errors detection -->
<script>
	let lasso_path = '<?php echo LASSO_PLUGIN_URL; // phpcs:ignore ?>';
	let post_type = 'post_type=<?php echo LASSO_POST_TYPE; // phpcs:ignore ?>';
	Sentry.init({
		dsn: '<?php echo SENTRY_DNS; // phpcs:ignore ?>',
		release: '<?php echo LASSO_VERSION; // phpcs:ignore ?>',
		ignoreErrors: [
			'ResizeObserver loop limit exceeded', 
			'ResizeObserver loop completed with undelivered notifications', 
			'__ez is not defined',
			'_ezaq is not defined',
			'Can\'t find variable: _ezaq',
			'wpColorPickerL10n is not defined',
			'jQuery(...).wpColorPicker is not a function',
		],
		integrations: [new Sentry.Integrations.BrowserTracing()],
		tracesSampleRate: 1.0,
		beforeSend(event, hint) {
			try {
				let is_lasso_error = false;
				let event_id = event.event_id;
				let frames = event.exception.values[0].stacktrace.frames;
				for(let i = 0; i < frames.length; i++) {
					if(frames[i].filename.includes(lasso_path) || frames[i].filename.includes(post_type)) {
						is_lasso_error = true;
						break;
					}
				}

				if(is_lasso_error) {
					Sentry.showReportDialog({
						eventId: event_id
					});

					return event;
				}
			} catch (error) {
				console.log(error);
			}
		}
	});
</script>

<!-- PHP errors detection -->
<?php
	$sentry_last_event_id = class_exists( '\LassoVendor\Sentry\SentrySdk' )
		? \LassoVendor\Sentry\SentrySdk::getCurrentHub()->getLastEventId() : false;
?>
<?php if ( (float) PHP_VERSION >= 7.2 && 'none' !== SENTRY_LOADED && $sentry_last_event_id ) : ?>
	<script>
		Sentry.init({ 
			dsn: '<?php echo SENTRY_DNS; // phpcs:ignore ?>'
		});
		Sentry.showReportDialog({ 
			eventId: '<?php echo $sentry_last_event_id; // phpcs:ignore ?>' 
		});
	</script>
<?php endif ?>

<?php
$lasso_db      = new Lasso_DB();
$keyword_count = $lasso_db->saved_keywords_count();

// ? check amazon setting and save result into options table if lasso_amazon_valid is empty
if ( '' === get_option( 'lasso_amazon_valid', '' ) ) {
	$lasso_amazon_api = new Lasso_Amazon_Api();
	$lasso_amazon_api->validate_amazon_settings();
}

	$user_email      = get_option( 'admin_email' ); // phpcs:ignore
	$install_count   = get_option( Enum::LASSO_INSTALL_COUNT, 1 ); // phpcs:ignore
	$user            = get_user_by( 'email', $user_email );
	$user_name       = isset( $user->display_name ) ? $user->display_name : get_bloginfo( 'name' );
	$user_hash       = get_option( 'lasso_license_hash', '' );
	$amazon_valid    = get_option( 'lasso_amazon_valid', false ) ? 1 : 0;
	$import_possible = count( Lasso_Setting::get_import_sources() ) > 0 ? 1 : 0;
	$sentry_loaded   = SENTRY_LOADED;
	$user_email      = get_option( 'lasso_license_email', $user_email ); // phpcs:ignore
	$classic_editor  = Lasso_Helper::is_classic_editor() ? 1 : 0;
	$ga_set          = $lasso_options['analytics_enable_click_tracking'] ? 1 : 0;
	$display_counts  = $lasso_db->get_display_counts_for_intercom();
	$single_displays = $display_counts['single_count'];
	$grid_displays   = $display_counts['grid_count'];
	$list_displays   = $display_counts['list_count'];
	$table_displays  = $display_counts['table_count'];
	$tables_created  = ( new Table_Details() )->total_count();
?>
