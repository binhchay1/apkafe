<?php
/**
 * URL links
 *
 * @package Lasso URL links
 */

use Lasso\Pages\Settings_General\Ajax as Setting_General_Ajax;
use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;
use Lasso\Classes\Launch_Darkly as Lasso_Launch_Darkly;
use Lasso\Classes\Config as Lasso_Config;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;
use Lasso\Classes\Enum as Lasso_Enum;

require LASSO_PLUGIN_PATH . '/admin/views/header-new.php';
?>

<?php
	$lasso_ajax_setting = new Setting_General_Ajax();

	$license_active = Lasso_License::get_license_status();
	$license_status = $license_active 
		? '<strong id="is_license_active" class="green">active</strong>'
		: '<strong id="is_license_active" class="red">not active</strong>';

	$stats                 = $lasso_ajax_setting->lasso_get_stats_new();
	$count_all_pages_posts = Lasso_Helper::count_all_pages_posts();

	$performance_event_tracking = $lasso_options['performance_event_tracking'] ? 'checked="true"' : '';
	
	$enable_google_tracking_checked = $lasso_options['analytics_enable_click_tracking'] ? 'checked="true"' : '';
	$enable_google_tracking_disabled = empty( $enable_google_tracking_checked ) ? 'disabled' : '';

	$ip_anonymization_checked  = $lasso_options['analytics_enable_ip_anonymization'] ? 'checked="true"' : '';
	$ip_anonymization_disabled = empty( $enable_google_tracking_checked ) ? 'disabled' : '';

	$auto_monetize_affiliates_checked  = $lasso_options['auto_monetize_affiliates'] ? 'checked="true"' : '';

	$send_pageview_checked  = $lasso_options['analytics_enable_send_pageview'] ? 'checked="true"' : '';
	$send_pageview_disabled = empty( $enable_google_tracking_checked ) ? 'disabled' : '';

	$disbale_notification = $lasso_options['general_disable_notification'];
	$disbale_notification = $disbale_notification ? 'checked="true"' : '';

	$disable_tooltip = $lasso_options['general_disable_tooltip'];
	$disable_tooltip = $disable_tooltip ? 'checked="true"' : '';

	$disable_amazon_notifications = $lasso_options['general_disable_amazon_notifications'];
	$disable_amazon_notifications = $disable_amazon_notifications ? 'checked="true"' : '';

	$lasso_permission    = $lasso_options['lasso_permission'] ?? 'Administrator';
	$permissions_options = array( 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber' );

	$open_new_tab         = $lasso_options['open_new_tab'] ? 'checked="true"' : '';
	$enable_nofollow      = $lasso_options['enable_nofollow'] ? 'checked="true"' : '';
	$enable_webp          = $lasso_options[ Lasso_Enum::OPTION_ENABLE_WEBP ] ? 'checked="true"' : '';
	$enable_sponsored     = $lasso_options['enable_sponsored'] ? 'checked="true"' : '';
	$show_disclosure      = $lasso_options['show_disclosure'] ? 'checked="true"' : '';
	$check_duplicate_link = $lasso_options['check_duplicate_link'] ? 'checked="true"' : '';

	$select_permission = '<select name="lasso_permission" class="form-control">';
	foreach ( $permissions_options as $permission ) {
		$selected = $permission === $lasso_permission ? 'selected' : '';
		$select_permission .= '<option value="' . $permission . '" ' . $selected . ' >' . $permission . '</option>';
	}
	$select_permission .= '</select>';

	$cpu_threshold = $lasso_options['cpu_threshold'];
	$rewrite_slug  = $lasso_options['rewrite_slug'];
	$keep_original_url = $lasso_options['keep_original_url'];
	$keep_original_url = implode( '\n', $keep_original_url );
	$keep_original_url = str_replace('\\n', '&#10;', htmlspecialchars($keep_original_url, ENT_QUOTES, 'UTF-8'));


	// ? CPT
	$all_cpt = get_post_types(array(), 'objects');
	$selected_cpt = $lasso_options['cpt_support'] ?? array();
	$select_cpt = '<select class="form-control" id="lasso-cpt-support" name="cpt_support" data-placeholder="Select post types" multiple>';
	$cpt_list = (array) array_merge( Lasso_Helper::$post_types_are_excluded, Lasso_Helper::$post_types_are_allowed_scanning );
	foreach ( $all_cpt as $cpt ) {
		if (in_array($cpt->name, $cpt_list, true) ) continue;
		$selected = is_array($selected_cpt) && in_array($cpt->name, $selected_cpt, true) ? 'selected' : '';
		$select_cpt .= '<option value="' . $cpt->name . '" ' . $selected . ' >' . $cpt->label . '</option>';
	}
	$select_cpt .= '</select>';
	
	// ? custom fields ACF
	$all_acf = Lasso_Helper::get_all_acf_fields();
	$selected_custom_fields = $lasso_options['custom_fields_support'] ?? array();
	$select_custom_fields = '<select class="form-control" id="lasso-custom-fields" name="custom_fields_support" data-placeholder="Select fields" multiple>';
	foreach ( $all_acf as $acf_name => $acf_label ) {
		$selected = is_array($selected_cpt) && in_array($acf_name, $selected_custom_fields, true) ? 'selected' : '';
		$select_custom_fields .= '<option value="' . $acf_name . '" ' . $selected . ' >' . $acf_label . '</option>';
	}
	$select_custom_fields .= '</select>';

	$segment_analytics       = $lasso_options[Lasso_Setting_Enum::SEGMENT_ANALYTICS];
    $restrict_prefix         = $lasso_options[Lasso_Setting_Enum::RESTRICT_PREFIX];
    $processes_execute_limit = $lasso_options[Lasso_Setting_Enum::PROCESSES_EXECUTE_LIMIT];
    $fontawesome_js_svg      = $lasso_options['fontawesome_js_svg'];

    $is_startup_plan        = Lasso_License::is_startup_plan();
?>

<!-- GENERAL SETTINGS -->
<section class="px-3 py-5">
	<div class="container">
		<!-- HEADER -->
		<?php require 'header.php'; ?>  

		<form class="lasso-admin-settings-form" autocomplete="off">
			<!-- SETTINGS -->
			<div class="row mb-5">
				<div class="col-lg">
					<div class="white-bg rounded shadow p-4 mb-lg-0 mb-5">
						<input type="hidden" name="count_all_pages_posts" value="<?php echo $count_all_pages_posts; ?>" />

						<!-- LICENSE KEY -->
						<section class="mb-5">
							<h3>License Key</h3>
							<p>Your license key is <?php echo $license_status; ?>. Access your <a class="purple underline" 
								href="https://app.getlasso.co/login" target="_blank">Lasso account</a> to manage installs.</p>
							<div class="form-group form-row">
								<div class="col-md pr-0">
									<input name="license_serial" class="form-control form-control-append mb-lg-0 mb-3 lasso-admin-input" 
										type="text" value="<?php echo esc_html( $lasso_options['license_serial'] ); ?>" />
								</div>
								<div class="col-md-3 p-0 mr-1">
									<button id="reactivate" class="btn btn-append w-100">Activate</button>
								</div>
							</div>
						</section>

						<!-- GOOGLE ANALYTICS -->
						<section class="mb-5">
							<h3>Google Analytics</h3>
							<p>Send click tracking data to Google Analytics. <a data-tooltip="Enter a Tracking ID (UA-) or a Product ID (G-). Or, enter them both and separate them with a comma. You can find them in Google Analytics > Admin > Property Settings."><i class="far fa-info-circle light-purple"></i></a></p>

							<div class="form-group">
								<div class="form-row align-items-center mb-3">
									<div class="col mb-lg-0 mb-3">
										<input name="analytics_google_tracking_id" type="text" class="form-control" placeholder="Tracking ID or Property ID" value="<?php echo esc_html( $lasso_options['analytics_google_tracking_id'] ); ?>">
									</div>
								</div>
								<div class="form-group align-items-center">
									<div class="" id="ga-tracking-toggle">
										<label class="toggle m-0 mb-3 mr-1">
											<input type="checkbox" name="analytics_enable_click_tracking" <?php echo esc_html( $enable_google_tracking_checked ); ?> <?php echo esc_html( $enable_google_tracking_disabled ); ?>>
											<span class="slider"></span>
										</label>
										<label class="m-0" data-tooltip="Enable to begin tracking Lasso Link clicks in Google Analytics.">Click Tracking <i class="far fa-info-circle light-purple"></i></label>
									</div>
									<div class="" id="ga-pageview">
										<label class="toggle m-0 mb-3 mr-1">
											<input type="checkbox" name="analytics_enable_send_pageview" <?php echo esc_html( $send_pageview_checked ); ?> <?php echo esc_html( $send_pageview_disabled ); ?>>
											<span class="slider"></span>
										</label>
										<label class="m-0" data-tooltip="If you have Google Analytics installed outside of Lasso, keep this disabled.">Pageview <i class="far fa-info-circle light-purple"></i></label>
									</div>
									<div class="" id="ga-ip-anonymization-toggle">
										<label class="toggle m-0 mb-3 mr-1">
											<input type="checkbox" name="analytics_enable_ip_anonymization" <?php echo esc_html( $ip_anonymization_checked ); ?> <?php echo esc_html( $ip_anonymization_disabled ); ?>>
											<span class="slider"></span>
										</label>
										<label class="m-0" data-tooltip="When enabled, we won't report visitors' IP addresses for GDPR compliance.">IP Anonymization <i class="far fa-info-circle light-purple"></i></label>
									</div>
									<div class="" id="auto-monetize-affiliates-toggle">
										<label class="toggle m-0 mr-1">
											<input type="checkbox" name="auto_monetize_affiliates" <?php echo esc_html( $auto_monetize_affiliates_checked ); ?>>
											<span class="slider"></span>
										</label>
										<label class="m-0" data-tooltip="Add all detected affiliate links to your dashboard as individual products.">Auto-Detect Affiliate Links <i class="far fa-info-circle light-purple"></i></label>
									</div>
								</div>
							</div>
						</section>

						<?php if ( ! $is_startup_plan ) { ?>
						<!-- Force Performance JS always turn on client using start up plan -->
						<!-- PERFORMANCE -->
						<section class="mb-5">
							<h3>Advanced Click Tracking</h3>
							<p>Send click tracking data to <a href="https://app.getlasso.co/performance/" target="_blank">Performance</a> for easy-to-understand analytics.</p>

							<div class="form-group">
								<label class="toggle m-0 mb-3 mr-1">
									<!-- click and page view tracking -->
									<input type="checkbox" name="performance_event_tracking" <?php echo esc_html( $performance_event_tracking ); ?>>
									<span class="slider"></span>
								</label>
								<label class="m-0" data-tooltip="When enabled, Lasso can help you understand what pages and links drive conversions on your site.">Page and Link-Level Tracking <i class="far fa-info-circle light-purple"></i></label>
							</div>
						</section>
						<?php } ?>

						<!-- LASSO URL: REWRITE SLUG -->
						<section class="mb-5">
							<h3>Cloaked Link Prefix</h3>
							<p>Add subdirectory to cloaked links. Most leave this empty. <a data-tooltip="For no prefix leave this field empty."><i class="far fa-info-circle light-purple"></i></a>
							<br/><i>Example: https://domain.com/<strong>recommends</strong>/link-name/</i></p>
							<div class="form-group">
								<div class="input-group">
									<input class="form-control form-control" type="text" name="rewrite_slug" id="rewrite_slug" value="<?php echo esc_html( $rewrite_slug ); ?>" aria-label="">
								</div>
							</div>
						</section>

						<!-- LASSO URL: KEEP ORIGINAL URL -->
						<?php if ( isset( $_GET['support'] ) ): ?>
						<section class="mb-5">
							<h3>Keep Original URL</h3>
							<p>We don't convert the affiliate URLs to destination URLs</p>
							<p>Example: <strong>domain.com</strong>, not <strong>https://domain.com/</strong></p>
							<div class="form-group">
								<div class="input-group">
									<textarea class="form-control form-control" name="keep_original_url" id="keep_original_url" rows="5"><?php echo $keep_original_url; ?></textarea>
								</div>
							</div>
						</section>
						<?php endif; ?>

						<!-- PERMISSIONS -->
						<section class="mb-5">
							<h3>Permissions</h3>
							<p>Select the minimum user role that can access Lasso.</p>
							<?php echo $select_permission; ?>
						</section>

						<!-- UNINSTALL LASSO -->
						<?php $bg_remove_attr = new Lasso_Process_Remove_Attribute() ?>
						<?php if ( ! Lasso_Process::are_all_processes_disabled() || $bg_remove_attr->is_process_running() || isset( $_GET['support'] ) ): ?>
						<section>
							<h3>Uninstall Lasso</h3>
							<p>Click this button to remove all data attributes from all your links.</p>
							<p class="form-group">
								<?php if( ! Lasso_Process::are_all_processes_disabled() || isset( $_GET['support'] ) ): ?>
								<button id="remove_lasso_attributes" class="btn red-bg">Remove Data Attributes</button>
								<?php endif; ?>
							</p>
							<div class="row lasso-remove-attributes">
							</div>
						</section>
						<?php endif; ?>

						<?php if ( isset( $_GET['support'] ) ): ?>
						<!-- CRON INTERVAL -->
						<section class="mt-5">
							<h3>Cron</h3>
							<p>Time interval (hours)</p>
							<p class="form-group">
								<input class="form-control form-control" type="number" name="cron_time_interval" id="cron_time_interval"
									min="0" max="3"
									value="<?php echo esc_html( $lasso_options['cron_time_interval'] ); ?>">
							</p>
						</section>

						<section class="mt-5">
							<h3>Lasso Cronjob Manually</h3>
							<p>Maximum Lasso cronjob at a time. <a data-tooltip="This feature runs only when the system's cronjob does not work."><i class="far fa-info-circle light-purple"></i></a></p>
							<p class="form-group">
								<input class="form-control form-control" type="number" name="manually_background_process_limit" id="manually_background_process_limit"
									min="1" max="3"
									value="<?php echo $lasso_options['manually_background_process_limit']; ?>">
							</p>
						</section>

						<!-- Optional Setting -->
						<section class="mt-5">
							<h3>Optional Settings</h3>
							<div class="form-group">
								<?php 
									echo Lasso_Html_Helper::render_toggle_option(
										Lasso_Setting_Enum::SEGMENT_ANALYTICS,
										'Segment Analytics',
										null,
										$segment_analytics,
										array(
											'id' => Lasso_Setting_Enum::SEGMENT_ANALYTICS
										)
									); 
								?>
							</div>

							<div class="form-group">
							<?php 
								echo Lasso_Html_Helper::render_toggle_option(
									Lasso_Setting_Enum::RESTRICT_PREFIX,
									'Restrict Prefix',
									null,
									$restrict_prefix,
									array(
										'id' => Lasso_Setting_Enum::RESTRICT_PREFIX
									)
								); 
							?>
							</div>

							<div class="form-group">
								<?php
								echo Lasso_Html_Helper::render_toggle_option(
									Lasso_Setting_Enum::PROCESSES_EXECUTE_LIMIT,
									'Processes Execute Limit',
									null,
									$processes_execute_limit,
									array(
										'id' => Lasso_Setting_Enum::PROCESSES_EXECUTE_LIMIT
									)
								);
								?>
							</div>

							<div class="form-group">
								<?php
								echo Lasso_Html_Helper::render_toggle_option(
									'fontawesome_js_svg',
									'Font Awesome JS Render SVG',
									null,
									$fontawesome_js_svg,
									array(
										'id' => 'fontawesome_js_svg'
									)
								);
								?>
							</div>
						</section>
						<?php endif; ?>
					</div>
				</div>

				<div class="col-lg">
					<div class="white-bg rounded shadow p-4">  
						<!-- LINK DATABASE -->
						<section class="mb-4">
							<h3>Link Index</h3>
							<p>Lasso tracks every link on your site.</p>
					
							<div class="row lasso-stats">
								<?php echo $stats; ?>
							</div>
					
							<?php if ( Lasso_Process::are_all_processes_disabled() ): ?>
							<div class="form-group">
								<button id="lasso-rescan" class="btn">Start Link Indexing</button>
							</div>
							<?php endif; ?>
					
							<?php if ( isset( $_GET['support'] ) ) { ?>
								<button class="btn" data-target="#confirm-build-db" data-toggle="modal">Rebuild Database</button>
							<?php } ?>
						</section>

						<!-- Custom post type support -->
						<section class="mb-5">
							<h3>Custom Link Detection</h3>
							<p>Scan for links and shortcodes in custom locations. <a data-tooltip="Additional locations where Lasso will discovering links."><i class="far fa-info-circle light-purple"></i></a></p>
							<div class="form-group">
							<label>Custom Post Types:</label>
								<?php echo $select_cpt; ?>
							</div>
							<?php if ( class_exists( 'ACF' ) ): ?>
							<div class="form-group">
								<label>Custom Fields (ACF):</label>
								<?php echo $select_custom_fields; ?>
							</div>
							<?php endif; ?>
						</section>

						<!-- CPU THRESHOLD -->
						<section class="mb-5">
							<h3>Performance</h3>
							<p>Set the maximum CPU level that Lasso will run at. <a data-tooltip="Lasso will wait until your CPU drops below this level to continue with its updates."><i class="far fa-info-circle light-purple"></i></a></p>
							<div class="form-group">
								<div class="input-group">
									<input class="form-control form-control-append" type="text" name="cpu_threshold" id="cpu-threshold" value="<?php echo esc_html( $cpu_threshold ); ?>" aria-label="">
									<div class="input-group-append">
										<span class="input-group-text">%</span>
									</div>
								</div>
							</div>
						</section>

						<!-- NEW LINK DEFAULT OPTIONS -->
						<section class="mb-5">
							<h3>Link Defaults</h3>
							<p>Set the default attributes for new links.</p>
							<div class="form-group">
								<label class="toggle m-0 mr-1">
									<input type="checkbox" name="open_new_tab" <?php echo esc_html( $open_new_tab ); ?> >
									<span class="slider"></span>
								</label>
								<label class="m-0" data-tooltip="When enabled, users who click this link will have it loaded in a new tab.">New Window / Tab <i class="far fa-info-circle light-purple"></i></label>
							</div>
					
							<div class="form-group">
								<label class="toggle m-0 mr-1">
									<input type="checkbox" name="enable_nofollow" <?php echo esc_html( $enable_nofollow ); ?> >
									<span class="slider"></span>
								</label>
								<label class="m-0" data-tooltip="When enabled, this link will be set to nofollow. This indicates to Google that it's an affiliate link.">NoFollow / NoIndex <i class="far fa-info-circle light-purple"></i></label>
							</div>
					
							<div class="form-group">
								<label class="toggle m-0 mr-1">
									<input type="checkbox" name="enable_sponsored" <?php echo esc_html( $enable_sponsored ); ?> >
									<span class="slider"></span>
								</label>
								<label class="m-0" data-tooltip="When enabled, this link will be set to sponsored.">Sponsored <i class="far fa-info-circle light-purple"></i></label>
							</div>

							<div class="form-group">
								<label class="toggle m-0 mr-1">
									<input type="checkbox" name="show_disclosure" <?php echo esc_html( $show_disclosure ); ?> >
									<span class="slider"></span>
								</label>
								<label class="m-0" data-tooltip="When enabled, this link will show the disclosure.">Show Disclosure <i class="far fa-info-circle light-purple"></i></label>
							</div>
							
							<div class="form-group">
								<label class="toggle m-0 mr-1">
									<input id="check_duplicate_link" type="checkbox" name="check_duplicate_link" <?php echo esc_html( $check_duplicate_link ); ?> >
									<span class="slider"></span>
								</label>
								<label class="m-0" data-tooltip="Allows you to have links from different networks to the same product.">Allow duplicate destinations <i class="far fa-info-circle light-purple"></i></label>
							</div>
						</section>

						<!-- NOTIFICATIONS -->
						<section class="mb-5">
							<h3>Notifications</h3>
							<p>Toggle which notifications you want enabled.</p>
					
							<div class="form-group">
								<label class="toggle m-0 mr-1">
									<input type="checkbox" name="general_disable_amazon_notifications" <?php echo esc_html( $disable_amazon_notifications ); ?> >
									<span class="slider"></span>
								</label>
								<label class="m-0">Disable Configure Amazon Notification</label>
							</div>
					
							<div class="form-group">
								<label class="toggle m-0 mr-1">
									<input type="checkbox" name="general_disable_tooltip" <?php echo esc_html( $disable_tooltip ); ?> >
									<span class="slider"></span>
								</label>
								<label class="m-0">Disable Help Tooltips</label>
							</div>
					
							<div class="form-group mb-1">
								<label class="toggle m-0 mr-1">
									<input type="checkbox" name="general_disable_notification" <?php echo esc_html( $disbale_notification ); ?> >
									<span class="slider"></span>
								</label>
								<label class="m-0">Disable Import Notifications</label>
							</div>
						</section>

						<!-- WEBP IMAGES -->
						<section class="mb-5">
							<h3>Images</h3>
							<p>Convert images to WebP format when enabled.</p>
							<div class="form-group">
								<label class="toggle m-0 mr-1">
									<input type="checkbox" name="enable_webp" <?php echo esc_html( $enable_webp ); ?> >
									<span class="slider"></span>
								</label>
								<label class="m-0">Webp Images</label>
							</div>
						</section>

						<!-- HISTORY SETTING -->
						<?php if( Lasso_Launch_Darkly::enable_audit_log() ): ?>
						<section>
							<h3>Audit</h3>
							<p>Toggle this if you want to track changes.</p>
							<div class="form-group">
								<?php $checked = Lasso_Helper::cast_to_boolean( $lasso_options[Lasso_Setting_Enum::ENABLE_HISTORY] ); ?>
								<?php echo Lasso_Html_Helper::render_toggle_option( Lasso_Setting_Enum::ENABLE_HISTORY, 'Post Content History', null, $checked ) ?>
							</div>
						</section>
						<?php endif; ?>
					</div>
				</div>

			</div>       

			<!-- SAVE CHANGES -->
			<div class="row align-items-center">
				<div class="col-lg text-lg-right text-center">
					<button class="btn save-change-tab" disabled>Save Changes</button>
				</div>
			</div>  
		</form>

	</div>
</section>

<!-- DATABASE REBUILD CONFIRM MODAL -->
<div class="modal fade" id="confirm-build-db" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content text-center shadow p-5 rounded">
			<h2 class="font-weight-bold">Ready to rebuild?</h2>
			<p>By rebuilding your link index, you'll start with a clean slate.</p>
			<div>
				<button type="button" class="btn red-bg mx-1" data-dismiss="modal">
					Cancel
				</button>
				<button id="confirm-db-rebuild" type="button" class="btn mx-1">
					Confirm
				</button>
			</div>
		</div>
	</div>
</div>


<script>
	function removeLassoAttributes() {
		jQuery('#remove-attributes-confirm').modal('hide');
		// Prepare data
		var data = {
			action: 'lasso_remove_lasso_attributes',
		};

		jQuery.post(ajaxurl, data, function (response) {
			lasso_helper.clearLoadingScreen();
			response = response.data;

			if(response.result) {
				lasso_helper.successScreen('Working... Please be patient.');
			} else {
				lasso_helper.errorScreen('The processes are running.');
			}

			return;
		})
		.fail(function (error) {
			lasso_helper.errorScreen(error);
		});
	}

	var globalOptionName = '';
	var globalOptionValue = '';

	jQuery(document).ready(function() {
		function confirmOverrideDisplay(optionName) {
			let input = jQuery('input[name="' + optionName + '"]');
			let isInputEnabled = input.prop('checked');
			let optionLabel = input.closest('div').find('label').eq(1).text().trim();

			globalOptionName = optionName;
			globalOptionValue = isInputEnabled;

			jQuery('.override-display-label').text(optionLabel);
			jQuery('.override-display-set').text(isInputEnabled ? 'enable' : 'disable');
			jQuery('#override-display').modal('show');
		}

		jQuery('input[name="open_new_tab"], input[name="enable_nofollow"], input[name="enable_sponsored"], input[name="show_disclosure"]').change(function() {
			let optionName = jQuery(this).attr('name');
			confirmOverrideDisplay(optionName);
		});

		jQuery("#lasso-cpt-support, #lasso-custom-fields").select2({
			width: '100%',
			allowClear: true,
			tags: true,
		});

		jQuery("#confirm-db-rebuild").on("click", function() {
			jQuery('#confirm-build-db').modal('hide');
			ajax_trigger_cron();
		})

		function ajax_trigger_cron() {
			// The loading screen
			lasso_helper.loadingScreen('', true);

			// Prepare data
			var data = {
				action: 'lasso_trigger_cron',
			};

			// var start_time = new Date().getTime();
			jQuery.post(ajaxurl, data, function (response) {
				lasso_helper.clearLoadingScreen();
				response = response.data;

				if(response.result) {
					lasso_helper.successScreen('Working... Please be patient.');
				} else {
					lasso_helper.errorScreen('The processes are running.');
				}

				return;
			})
			.fail(function (error) {
				lasso_helper.errorScreen(error);
			});
		}

		// update the stats in General tab in Settings page (Database Status)
		lasso_helper.rebuild_database_background();

		// convert duration to time
		var stats = jQuery('.lasso-stats');
		var duration = stats.find('.eta').text();
		duration = parseFloat(duration);
		var time = convert_duration_to_time(duration * 1000);
		stats.find('.eta').text(time);

		jQuery(".lasso-admin-settings-form").submit(function(e) {
			e.preventDefault();
		});

		jQuery('#remove_lasso_attributes').click(function(e) {
			e.preventDefault();
			jQuery('#remove-attributes-confirm').modal('show');
		});

		jQuery('#lasso-rescan').click(function() {
			// Prepare data
			var data = {
				action: 'lasso_rescan_lasso_attributes',
			};

			jQuery.post(ajaxurl, data, function (response) {
				lasso_helper.clearLoadingScreen();
				response = response.data;

				if(response.result) {
					lasso_helper.successScreen('Working... Please be patient.');
				} else {
					lasso_helper.errorScreen('The processes are running.');
				}

				return;
			})
			.fail(function (error) {
				lasso_helper.errorScreen(error);
			});
		});

		// Rewrite slug validate
		jQuery('#rewrite_slug').unbind().keyup(function() {
			var el = jQuery(this);
			el.val(el.val().trim().toLowerCase().replace(/[\W_]+/g,"-"));
		});
	});

</script>

<!-- UNSAVED CHANGES MODAL -->
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/unsaved-changes.php'; ?>
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/remove-attributes-confirm.php'; ?>
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/override-display.php'; ?>

<?php
	Lasso_Helper::enqueue_script( 'lasso-helper', 'lasso-helper.js', array( 'jquery' ) );
	Lasso_Config::get_footer();
?>
