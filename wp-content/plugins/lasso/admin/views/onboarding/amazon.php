<?php
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting;

$lasso_options = Setting::lasso_get_settings();

$lasso_db            = new Lasso_DB();
$amazon_tracking_ids = $lasso_db->get_amazon_tracking_ids();
$countries           = array_column( $amazon_tracking_ids, 'country_name', 'id' );
$amazon_default_tracking_country = ! empty( $lasso_options['amazon_default_tracking_country'] )
	? $lasso_options['amazon_default_tracking_country'] : '1';
$countries_dd = Lasso_Helper::get_countries_dd( $countries, $amazon_default_tracking_country );

$amazon_tracking_id   = $lasso_options['amazon_tracking_id'] ?? '';
$amazon_access_key_id = $lasso_options['amazon_access_key_id'] ?? '';
$amazon_secret_key    = $lasso_options['amazon_secret_key'] ?? '';

$is_valid_tracking_id = empty( $amazon_tracking_id ) ? true : Lasso_Amazon_Api::validate_tracking_id( $amazon_tracking_id );

$tracking_id_class = $is_valid_tracking_id ? '' : ' invalid-field';
$tracking_id_invalid_class = $is_valid_tracking_id ? ' d-none' : '';

$amazon_update_pricing_hourly = $lasso_options['amazon_update_pricing_hourly'] ?? true;
$update_price_checked = $amazon_update_pricing_hourly ? 'checked' : '';

$enable_amazon_prime  = $lasso_options['enable_amazon_prime'] ?? true;
$enable_amazon_prime_checked = $enable_amazon_prime ? 'checked' : '';

$show_amazon_discount_pricing = $lasso_options['show_amazon_discount_pricing'] ?? false;
$show_amazon_discount_pricing_checked = $show_amazon_discount_pricing ? 'checked' : '';

$auto_monetize_amazon = $lasso_options['auto_monetize_amazon'] ?? false;
$auto_monetize_amazon_checked = $auto_monetize_amazon ? 'checked' : '';
?>

<div id="amazon" class="tab-item d-none" data-step="amazon">
	<div class="progressbar_container">
		<?php 
            $params = array( 'active_step' => 4 );
            echo Lasso_Helper::include_with_variables( LASSO_PLUGIN_PATH . '/admin/views/onboarding/steps.php', $params ); 
        ?>
	</div>

	<div class="onboarding_header text-center mb-4">
		<h1 class="font-weight-bold d-inline-block align-middle">Amazon Associates</h1>
		&nbsp;<a href="https://support.getlasso.co/en/articles/3182308-how-to-get-your-amazon-product-api-keys" target="_blank" class="btn btn-sm learn-btn">
			<i class="far fa-info-circle"></i> Learn
		</a>
	</div>

	<form class="lasso-admin-settings-form" autocomplete="off" action="">
		<!-- AMAZON -->
		<div class="row mb-5">
			<div class="col-lg">

				<div class="white-bg rounded shadow p-4 mb-4">
					<!-- AMAZON TRACKING ID -->
					<section>
						<h3>Amazon Associates Accounts</h3>
						<p>Enter your primary tracking ID and make sure your international accounts are connected with OneLink. It'll automatically send visitors to their local store.</p>

						<div class="form-group mb-4">
							<label><strong>Tracking ID for This Site</strong></label>
							<input type="text" name="amazon_tracking_id" id="amazon_tracking_id" class="form-control<?php echo $tracking_id_class; ?>" value="<?php echo $amazon_tracking_id ?>" placeholder="tracking-20">
							<div id="tracking-id-invalid-msg" class="red<?php echo $tracking_id_invalid_class; ?>">This is an invalid Tracking ID</div>
						</div>

						<div class="form-group">
							<label class="toggle m-0 mr-1 lasso-lite-disabled no-hint">
								<input type="checkbox" name="amazon_update_pricing_hourly" <?php echo $update_price_checked; ?>>
								<span class="slider"></span>
							</label>
							<label class="m-0 lasso-lite-disabled no-hint">Update Amazon pricing daily</label>
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
						<p>Automatically monetize all current and future Amazon links with your Tracking ID and and added to your affiliate dashboard.</p>

						<p>
							<label class="toggle m-0 mr-1">
								<input type="checkbox" name="auto_monetize_amazon" <?php echo $auto_monetize_amazon_checked; ?>>
								<span class="slider"></span>
							</label>
							<label class="m-0">Enable Amazon Auto-Monetization</label>
						</p>
						<p class="text-danger amazon-error"></p>
					</section>
				</div>
			</div>

			<div class="col-lg">
				<div class="white-bg rounded shadow p-4 mb-lg-0 mb-5">
					<!-- PRODUCT API -->
					<section>
						<h3>Amazon Product API</h3>
						<p>If you want to use the Amazon API for product data, here's how to get your <a href="https://support.getlasso.co/en/articles/3182308-how-to-get-your-amazon-product-api-keys" target="_blank" class="purple underline">API keys from Amazon</a>.</p>

						<div class="form-group">
							<label data-tooltip="Select your Amazon Associates locale."><strong>Default Tracking ID</strong> <i class="far fa-info-circle light-purple"></i></label>
							<?php echo $countries_dd; ?>
						</div>

						<div class="form-group mb-4">
							<label><strong>Access Key ID</strong></label>
							<input type="text" name="amazon_access_key_id" id="amazon_access_key_id" class="form-control" value="<?php echo $amazon_access_key_id ?>" placeholder="Access Key ID">
						</div>

						<div class="form-group mb-4">
							<label><strong>Secret Key</strong></label>
							<input type="text" name="amazon_secret_key" id="amazon_secret_key" class="form-control" value="<?php echo $amazon_secret_key ?>" placeholder="Secret Key">
						</div>
					</section>

					<div class="form-group">
						<label class="toggle m-0 mr-1 lasso-lite-disabled no-hint">
							<input type="checkbox" name="enable_amazon_prime" <?php echo $enable_amazon_prime_checked; ?>>
							<span class="slider"></span>
						</label>
						<label class="m-0 lasso-lite-disabled no-hint">Show Prime Logo In Displays</label>
					</div>

					<div class="form-group">
						<label class="toggle m-0 mr-1 lasso-lite-disabled no-hint">
							<input type="checkbox" name="show_amazon_discount_pricing" <?php echo $show_amazon_discount_pricing_checked; ?>>
							<span class="slider"></span>
						</label>
						<label class="m-0 lasso-lite-disabled no-hint">Show Discount Pricing</label>
					</div>
				</div>
			</div>

		</div>

		<!-- SAVE CHANGES -->
		<div class="row align-items-center">
			<div class="col-lg text-lg-right text-center">
				<button class="btn btn-outline-dark bg-white text-dark next-step">Skip &rarr;</button>
				<button class="btn btn-save-amazon next-step" >Save and Continue &rarr;</button>
			</div>
		</div>
	</form>

	<!-- MODALS -->
	<?php require_once LASSO_PLUGIN_PATH . '/admin/views/modals/auto-monetize-flip.php'; ?>
</div>
