<?php
/**
 * URL links
 *
 * @package Lasso URL links
 */

use Lasso\Classes\Config as Lasso_Config;
use Lasso\Classes\Helper as Lasso_Helper;

// phpcs:ignore
require LASSO_PLUGIN_PATH . '/admin/views/header-new.php';
?>

<?php
	$lasso_db            = new Lasso_DB();
	$amazon_tracking_ids = $lasso_db->get_amazon_tracking_ids();
	$countries           = array_column( $amazon_tracking_ids, 'country_name', 'id' );

	$amazon_access_key_id            = $lasso_options['amazon_access_key_id'];
	$amazon_secret_key               = $lasso_options['amazon_secret_key'];
	$amazon_tracking_id              = $lasso_options['amazon_tracking_id'];
	$is_valid_tracking_id            = empty( $amazon_tracking_id ) ? true : Lasso_Amazon_Api::validate_tracking_id( $amazon_tracking_id );
	$amazon_default_tracking_country = ! empty( $lasso_options['amazon_default_tracking_country'] )
		? $lasso_options['amazon_default_tracking_country'] : '1';

	$auto_monetize_amazon = $lasso_options['auto_monetize_amazon'] ?? false;
	$enable_amazon_prime  = $lasso_options['enable_amazon_prime'] ?? true;
	$show_amazon_discount_pricing = $lasso_options['show_amazon_discount_pricing'] ?? false;
	$amazon_multiple_tracking_id  = $lasso_options['amazon_multiple_tracking_id'] ?? true;
	$amazon_update_pricing_hourly = $lasso_options['amazon_update_pricing_hourly'] ?? true;

	$amazon_add_tracking_id_to_attribution = $lasso_options['amazon_add_tracking_id_to_attribution'] ?? true;
	$amazon_add_tracking_id_to_attribution_checked = $amazon_add_tracking_id_to_attribution ? 'checked' : '';

	// ? Amazon tracking id whitelist
	$amazon_tracking_id_whitelist = $lasso_options['amazon_tracking_id_whitelist'] ?? array();
	$amazon_tracking_id_whitelist = is_array( $amazon_tracking_id_whitelist ) ? $amazon_tracking_id_whitelist : explode( ' ', $amazon_tracking_id_whitelist );
	$select_tracking_id = '<select class="form-control" id="lasso-tracking-id-whitelist" name="amazon_tracking_id_whitelist" 
		data-placeholder="Select Tracking IDs" multiple ' . ( $amazon_multiple_tracking_id ? '' : 'disabled' ) . '>';
	foreach ( $amazon_tracking_id_whitelist as $tracking_id ) {
		$tracking_id = esc_html( $tracking_id );
		$selected = 'selected';
		$select_tracking_id .= '<option value="' . $tracking_id . '" ' . $selected . ' >' . $tracking_id . '</option>';
	}
	$select_tracking_id .= '</select>';

	$countries_dd          = Lasso_Helper::get_countries_dd( $countries, $amazon_default_tracking_country );
	$amazon_affiliate_html = Lasso_Helper::get_tracking_id_fields( $amazon_tracking_ids, $amazon_tracking_id );
?>

<!-- AMAZON SETTINGS -->
<section class="px-3 py-5">
	<div class="container">
		<!-- HEADER -->
		<?php require 'header.php'; ?>  
		<form class="lasso-admin-settings-form" autocomplete="off">
			<!-- AMAZON -->
			<div class="row mb-5">
				<div class="col-lg">

					<div class="white-bg rounded shadow p-4 mb-4">
						<!-- AMAZON TRACKING ID -->
						<section>
							<h3>Amazon Associates Accounts</h3>
							<p>Enter your primary tracking ID and make sure your international accounts are connected with OneLink. It'll automatically send visitors to their local store.</p>

							<?php 
								$tracking_id_class = $is_valid_tracking_id ? '' : ' invalid-field'; 
								$tracking_id_invalid_class = $is_valid_tracking_id ? ' d-none' : '';
							?>
							<div class="form-group mb-4">
								<label><strong>Tracking ID for This Site</strong></label>
								<input type="text" name="amazon_tracking_id" 
									class="form-control<?php echo $tracking_id_class; ?>" 
									value="<?php echo esc_html( $amazon_tracking_id ); ?>"
									placeholder="tracking-20"
								>
								<div id="tracking-id-invalid-msg" class="red<?php echo $tracking_id_invalid_class; ?>">This is an invalid Tracking ID</div>
							</div>

							<?php $update_price_checked = $amazon_update_pricing_hourly ? 'checked' : ''; ?>
							<div class="form-group">
								<label class="toggle m-0 mr-1">
									<input type="checkbox" name="amazon_update_pricing_hourly" <?php echo $update_price_checked; ?>>
									<span class="slider"></span>
								</label>
								<label class="m-0">Update Amazon pricing daily</label>
							</div>
							<div class="form-group">
								<label class="toggle m-0 mr-1 lasso-lite-disabled no-hint">
									<input type="checkbox" name="amazon_add_tracking_id_to_attribution" <?php echo $amazon_add_tracking_id_to_attribution_checked; ?>>
									<span class="slider"></span>
								</label>
							<label class="m-0 lasso-lite-disabled no-hint">Add Tracking ID to Amazon Attribution links</label>
						</div>
						</section>
					</div>

					<!-- AUTO MONETIZE AMAZON -->
					<div class="white-bg rounded shadow p-4">
						<section>
							<h3 class="d-inline-block align-middle mr-2">Auto-Monetize Amazon Links</h3>
							<a href="https://support.getlasso.co/en/articles/5607815-how-to-use-auto-amazon" target="_blank" class="btn btn-sm learn-btn mb-2">
								<i class="far fa-info-circle"></i> Learn
							</a>
							<p>Automatically monetize all current and future Amazon links with your Tracking ID, migrate your SiteStripe displays, and add them to your affiliate dashboard.</p>

							<p>
								<label class="toggle m-0 mr-1">
									<input type="checkbox" name="auto_monetize_amazon" <?php echo $auto_monetize_amazon ? 'checked' : ''; ?>>
									<span class="slider"></span>
								</label>
								<label class="m-0">Enable Amazon Auto-Monetization</label>
							</p>
							<p class="text-danger amazon-error"></p>
						</section>
						<section>
							<p>
								<label class="toggle m-0 mr-1">
									<input type="checkbox" name="amazon_multiple_tracking_id" <?php echo $amazon_multiple_tracking_id ? 'checked' : ''; ?>>
									<span class="slider"></span>
								</label>
								<label class="m-0">Allow Multiple Tracking IDs</label>
							</p>
							<div class="form-group mb-4">
								<label><strong>Tracking ID Whitelist</strong></label>
								<?php echo $select_tracking_id; ?>
							</div>
						</section>
					</div>
				</div> 

				<div class="col-lg">
					<div class="white-bg rounded shadow p-4 mb-lg-0 mb-5">
						<!-- PRODUCT API -->
						<section>
							<h3>Amazon Product API</h3>
							<p>If you want to use the Amazon API for product data, here's how to get your <a href="https://support.getlasso.co/en/articles/3182308-how-to-get-your-amazon-product-api-keys" target="_blank" class="purple underline">API keys from Amazon</a>. <b>Most people can leave this blank</b>.</p>

								<div class="form-group">
								<label data-tooltip="Select your Amazon Associates locale."><strong>Default Tracking ID</strong> <i class="far fa-info-circle light-purple"></i></label>
								<?php echo $countries_dd; ?>
							</div>

							<div class="form-group mb-4">
								<label><strong>Access Key ID</strong></label>
								<input type="text" name="amazon_access_key_id" class="form-control" value="<?php echo esc_html( $amazon_access_key_id ); ?>">
							</div>

							<div class="form-group mb-4">
								<label><strong>Secret Key</strong></label>
								<input type="text" name="amazon_secret_key" class="form-control" value="<?php echo esc_html( $amazon_secret_key ); ?>">
							</div>
						</section>

						<div class="form-group">
							<label class="toggle m-0 mr-1">
								<input type="checkbox" name="enable_amazon_prime" <?php echo $enable_amazon_prime ? 'checked' : ''; ?>>
								<span class="slider"></span>
							</label>
							<label class="m-0">Show Prime Logo In Displays</label>
						</div>

						<div class="form-group">
							<label class="toggle m-0 mr-1">
								<input type="checkbox" name="show_amazon_discount_pricing" <?php echo $show_amazon_discount_pricing ? 'checked' : ''; ?>>
								<span class="slider"></span>
							</label>
							<label class="m-0">Show Discount Pricing</label>
						</div>
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

<!-- MODALS -->
<?php require_once LASSO_PLUGIN_PATH . '/admin/views/modals/auto-monetize-flip.php'; ?>

<script>
	jQuery(document).ready(function() {
		jQuery(".lasso-admin-settings-form").submit(function(e){
			e.preventDefault();
		});

		jQuery("#lasso-tracking-id-whitelist").select2({
			width: '100%',
			allowClear: true,
			tags: true,
		});
	});
</script>

<?php Lasso_Config::get_footer(); ?>
