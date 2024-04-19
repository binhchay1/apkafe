<?php
/**
 * URL links
 *
 * @package Lasso URL links
 */

use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Config as Lasso_Config;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;

// phpcs:ignore
require LASSO_PLUGIN_PATH . '/admin/views/header-new.php';
?>

<?php
	$disclosure_text_default = "We earn a commission if you make a purchase, at no additional cost to you.";
	$disclosure_text         = $lasso_options['disclosure_text'] ?? $disclosure_text_default;
	$theme_name              = $lasso_options['theme_name'] ?? 'Cactus';
	$custom_css              = $lasso_options['custom_css'] ?? '';
	$main_color              = $lasso_options['display_color_main'] ?? 'black';
	$title_color             = $lasso_options['display_color_title'] ?? $main_color;
	$bg_color          		 = $lasso_options['display_color_background'] ?? 'white';
	$button_color      		 = $lasso_options['display_color_button'] ?? '#22BAA0';
	$secondary_button_color  = $lasso_options['display_color_secondary_button'] ?? $button_color;
	$button_text_color       = $lasso_options['display_color_button_text'] ?? 'white';
	$pros_color              = $lasso_options['display_color_pros'] ?? '#22BAA0';
	$cons_color              = $lasso_options['display_color_cons'] ?? '#E06470';
	
	$primary_button_text   = '' === $lasso_options['primary_button_text'] ? 'Buy Now' : $lasso_options['primary_button_text'];
	$secondary_button_text = '' === $lasso_options['secondary_button_text'] ? 'Our Review' : $lasso_options['secondary_button_text'];
	
	$show_price              = $lasso_options['show_price'] ? 'checked' : '';
	$show_disclosure_grid    = $lasso_options['show_disclosure_grid'] ? 'checked' : '';
	$keep_site_stripe_ui     = $lasso_options['keep_site_stripe_ui'] ? 'checked' : '';
	$show_disclosure         = $lasso_options['show_disclosure'] ? 'checked' : '';
	$link_from_display_title = $lasso_options['link_from_display_title'] ? 'checked' : '';
	
	$theme_options = array( 'Cactus', 'Cutter', 'Flow', 'Geek', 'Lab', 'Llama', 'Money', 'Splash' );

	$select_theme = '<select name="theme_name" class="form-control">';
	foreach ( $theme_options as $theme ) {
		$selected_theme_option = '';
		if ( $theme === $theme_name ) {
			$selected_theme_option = 'selected';
		}
		$select_theme .= '<option value="' . $theme . '" ' . $selected_theme_option . ' >' . $theme . '</option>';
	}
	$select_theme .= '</select>';

	$display_type        = Lasso_Setting::get_display_type();
	$select_display_type = Lasso_Html_Helper::render_select_option( "display_type", $display_type, Lasso_Setting::DISPLAY_TYPE_SINGLE );

	$width_options        = Lasso_Setting::get_width_options();
	$select_width_options = Lasso_Html_Helper::render_select_option( "width", $width_options, ( int ) Lasso_Setting::W_800 );

	$number_of_column = array(
		1 => 1,
		2 => 2,
		3 => 3
	);
	$select_number_of_column = Lasso_Html_Helper::render_select_option( "number_of_column", $number_of_column, 3 );

	$enable_brag_mode = $lasso_options['enable_brag_mode'] ?? false;
	$enable_brag_mode = $enable_brag_mode ? 'checked="true"' : '';

	$lasso_affiliate_url = $lasso_options['lasso_affiliate_URL'] ?? '';

	$settings = $lasso_options;
	$type     = '';

?>

<div id="custom_css_html">
	<style>
		<?php echo $custom_css; ?>
	</style>
</div>

<!-- DISPLAY SETTINGS -->
<section class="px-3 py-5">
	<div class="container">
		<!-- HEADER -->
		<?php require 'header.php'; ?>  

		<form class="lasso-admin-settings-form" autocomplete="off">
			<div class="white-bg rounded shadow p-4 mb-4">
				<div class="row">
					<div class="col-lg-12 mb-lg-0 mb-5 h-100">
						<div class="demo-display-header form-group mb-3">
							<div class="form-row">
								<div class="col-lg">
									<label data-tooltip="This defines the style and structure of your display."><strong>Theme</strong> <i class="far fa-info-circle light-purple"></i></label>
									<?php echo $select_theme; ?>
								</div>
								<div class="col-lg">
									<label data-tooltip="This determines how many products are shown and in what configuration."><strong>Display Type</strong> <i class="far fa-info-circle light-purple"></i></label>
									<?php echo $select_display_type; ?>
								</div>
								
								<!-- Hide for now, confusing to customers
								<div class="col-lg" id="number_of_column_wrapper" style="display: none">
									<label data-tooltip="Set the number of columns the demo grid will display."><strong>Columns</strong> <i class="far fa-info-circle light-purple"></i></label>
									<?php echo $select_number_of_column; ?>
								</div>
								-->
								
								<!-- Hide for now, confusing to customers
								<div class="col-lg">
									<label data-tooltip="Preview a specific width to demo what a display would look like."><strong>Width</strong> <i class="far fa-info-circle light-purple"></i></label>
									<?php echo $select_width_options; ?>
								</div>
								<div class="col-lg" id="custom_width_wrapper" style="display: none">
									<div>
										<label data-tooltip="Enter the pixel width number to demo your display."><strong>Customize width</strong> <i class="far fa-info-circle light-purple"></i></label>
										<input id="custom_width" name="custom_width" type="number" class="form-control" autocomplete="off">
									</div>
								</div>
								-->
							</div>
						</div>
					</div>
				</div>
				
				<div class="onboarding_display_container pb-3">
					<div id="demo_display_box"></div>
					<div class="image_loading onboarding d-none"></div>
				</div>
		
				<div class="row">
					<div class="col-lg-6 mb-lg-0 mb-5 h-100">
						<div class="form-group">
							<div class="form-row mb-4">
								<div class="col-lg">
									<label data-tooltip="This is the color of your badge background."><strong>Badge</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" name="display_color_main" value="<?php echo esc_html( $main_color ); ?>" class="form-control color-picker" placeholder="#5E36CA" onkeydown="return ignore_enter(event)" />
								</div>
						
								<div class="col-lg">
									<label data-tooltip="This is the color of the title text of your display."><strong>Title</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" name="display_color_title" value="<?php echo esc_html( $title_color ); ?>" class="form-control color-picker" placeholder="#FFFFFF" onkeydown="return ignore_enter(event)" />
								</div>
							</div>
							
							<div class="form-row mb-4">
								<div class="col-lg">
									<label data-tooltip="This is the color of the inside of your display."><strong>Background</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" name="display_color_background" value="<?php echo esc_html( $bg_color ); ?>" class="form-control color-picker" placeholder="#FFFFFF" onkeydown="return ignore_enter(event)"  />
								</div>
								
								<div class="col-lg">
									<label data-tooltip="This is text color for your badges and buttons."><strong>Button + Badge Text</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" name="display_color_button_text" value="<?php echo esc_html( $button_text_color ); ?>" class="form-control color-picker" placeholder="#FFFFFF" onkeydown="return ignore_enter(event)" />
								</div>
							</div>
							
							<div class="form-row mb-4">
								<div class="col-lg">
									<label data-tooltip="This is the main color of the Pros Field."><strong>Pros</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" name="display_color_pros" value="<?php echo esc_html( $pros_color ); ?>" class="form-control color-picker" placeholder="#FFFFFF" onkeydown="return ignore_enter(event)" />
								</div>
								
								<div class="col-lg">
									<label data-tooltip="This is the main color of the Cons Field."><strong>Cons</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" name="display_color_cons" value="<?php echo esc_html( $cons_color ); ?>" class="form-control color-picker" placeholder="#FFFFFF" onkeydown="return ignore_enter(event)" />
								</div>
							</div>
		
							<div class="form-row">
								<div class="col-lg-6 mb-4">
									<label class="toggle m-0 mr-1">
										<input type="checkbox" id="show_price" name="show_price" <?php echo esc_html( $show_price ); ?> >
										<span class="slider"></span>
									</label>
									<label data-tooltip="Turn this on to show the price in Displays by default. You can override this per display.">Show Price <i class="far fa-info-circle light-purple"></i></label>
								</div>
						
								<div class="col-lg-6 mb-4">
									<label class="toggle m-0 mr-1">
										<input type="checkbox" id="show_disclosure" name="show_disclosure" <?php echo esc_html( $show_disclosure ); ?> >
										<span class="slider"></span>
									</label>
									<label data-tooltip="Turn this on to show the disclosure in Displays by default. You can override this per display.">Disclosure in Single Display <i class="far fa-info-circle light-purple"></i></label>
								</div>

								<div class="col-lg-6">
									<label class="toggle m-0 mr-1">
										<input type="checkbox" id="link_from_display_title" name="link_from_display_title" <?php echo esc_html( $link_from_display_title ); ?> >
										<span class="slider"></span>
									</label>
									<label data-tooltip="When enabled, your Display titles will be your clickable Lasso Link. Toggle this off to have Display titles only appear as text.">
										Link from Display Title <i class="far fa-info-circle light-purple"></i>
									</label>
								</div>

								<div class="col-lg-6 mb-4">
									<label class="toggle m-0 mr-1">
										<input type="checkbox" id="show_disclosure_grid" name="show_disclosure_grid" <?php echo esc_html( $show_disclosure_grid ); ?> >
										<span class="slider"></span>
									</label>
									<label data-tooltip="Turn this on to show the disclosure in Displays by default. You can override this per display.">Disclosure in Grids <i class="far fa-info-circle light-purple"></i></label>
								</div>

								<div class="col-lg-6 mb-4">
									<label class="toggle m-0 mr-1">
										<input type="checkbox" id="keep_site_stripe_ui" name="keep_site_stripe_ui" <?php echo esc_html( $keep_site_stripe_ui ); ?> >
										<span class="slider"></span>
									</label>
									<label data-tooltip="When enabled, turn consecutive Single Displays into a Grid Display.">
										Keep Site Stripe UI <i class="far fa-info-circle light-purple"></i>
									</label>
								</div>
							</div>
						</div>
					</div>
					
					<div class="col-lg-6 mb-lg-0 mb-5 h-100">					
						<div class="form-row mb-4">
							<div class="col-lg">
								<label data-tooltip="If you leave your display button text blank, this is what it will default to."><strong>Primary Button</strong> <i class="far fa-info-circle light-purple"></i></label>
								<input type="text" name="primary_button_text" value="<?php echo esc_html( $primary_button_text ); ?>" class="form-control" placeholder="Buy Now" onkeydown="return ignore_enter(event)" />
							</div>
							
							<div class="col-lg">
								<label data-tooltip="This is the color of your display's main CTA button.">&nbsp;</label>
								<input type="text" name="display_color_button" value="<?php echo esc_html( $button_color ); ?>" class="form-control color-picker" placeholder="#22BAA0" onkeydown="return ignore_enter(event)" />
							</div>
						</div>
						
						<div class="form-row mb-4">
							<div class="col-lg">
								<label data-tooltip="If you set a secondary button for your display and leave it blank, this is what it will default to."><strong>Secondary Button</strong> <i class="far fa-info-circle light-purple"></i></label>
								<input type="text" name="secondary_button_text" value="<?php echo esc_html( $secondary_button_text ); ?>" class="form-control" placeholder="Learn More" onkeydown="return ignore_enter(event)" />
							</div>
					
							<div class="col-lg">
								<label data-tooltip="This is the color of your display's secondary CTA button.">&nbsp;</label>
								<input type="text" name="display_color_secondary_button" value="<?php echo esc_html( $secondary_button_color ); ?>" class="form-control color-picker" placeholder="#22BAA0" onkeydown="return ignore_enter(event)" />
							</div>
						</div>
						
						<div class="form-row mb-1">
							<div class="col-lg">
								<label data-tooltip="This is the default disclosure text used with your displays."><strong>Disclosure</strong> <i class="far fa-info-circle light-purple"></i></label>
								<textarea id="disclosure_text" name="disclosure_text" class="form-control" rows="4"><?php echo esc_html( $disclosure_text ); ?></textarea>
							</div>
							<div class="col-lg">
								<label data-tooltip="Earn money sharing Lasso with our affiliate program."><strong>Brag Mode</strong> <i class="far fa-info-circle light-purple"></i></label>
								<input name="lasso_affiliate_URL" type="text" class="form-control mb-4" placeholder="Your Lasso Affiliate URL" value="<?php echo esc_html( $lasso_affiliate_url ); ?>" onkeydown="return ignore_enter(event)">
								<label class="toggle m-0 mr-1">
									<input type="checkbox" name="enable_brag_mode" <?php echo esc_html( $enable_brag_mode ); ?>>
									<span class="slider"></span>
								</label>
								<label class="m-0">Enable Brag Mode</label>
							</div>
						</div>
						
						<?php if ( ! empty( $custom_css ) || isset( $_GET['css'] ) ) { ?>
						<div class="form-row mt-4">
							<div class="col-lg-12">
								<label data-tooltip="Use this to add CSS to further customize Lasso."><strong>Custom CSS</strong> <i class="far fa-info-circle light-purple"></i></label>
								<textarea id="custom_css" name="custom_css" class="form-control" rows="6" placeholder="Enter CSS code here"><?php echo esc_html( $custom_css ); ?></textarea>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>

			<!-- SAVE CHANGES -->
			<div class="row align-items-center">
				<div class="col-lg text-lg-left text-center">
					<a class="btn black white-bg black-border" id="need-more-customization">Need More Customization?</a>
				</div>
				<div class="col-lg text-lg-right text-center">
					<button class="btn save-change-tab" disabled>Save Changes</button>
				</div>
			</div>   
		</form>
	</div>
</section>

<script>
	jQuery(document).ready(function() {
		jQuery(".lasso-admin-settings-form").submit(function(e){
			e.preventDefault();
		});

		jQuery('#show_disclosure').on('change', function() {
			var disclosure = jQuery('.lasso-disclosure');
			if(jQuery(this).prop('checked') == true) {
				disclosure.css('visibility','visible');
				disclosure.css('display','block');
			} else {
				disclosure.css('visibility', 'hidden');
				disclosure.css('display', 'none');
			}
		});

		jQuery('#show_price').on('change', function() {
			var price = jQuery('.lasso-price');
			if(jQuery(this).prop('checked') == true) {
				price.css('visibility','visible');
				price.css('display','block');
			} else {
				price.css('visibility', 'hidden');
				price.css('display', 'none');
			}
		});

		jQuery('#disclosure_text').on('keyup', function(){
			let text = jQuery(this).val();
			jQuery('.lasso-disclosure').text(text);
		})
		
		jQuery('.color-picker').spectrum({
			type: "component",
			hideAfterPaletteSelect: "true",
			showAlpha: "false",
			allowEmpty: "false"
		});

		jQuery('#need-more-customization').on('click', function () {
			window.Intercom('showNewMessage', 'Hey team, can you help with special customizations to the Lasso Displays? I need...');
		});
	});

	/**
	 * Stop submit by enter
	 *
	 * @param event
	 * @returns {boolean}
	 */
	function ignore_enter( event ) {
		if ( event.keyCode == 13 ) {
			return false;
		}

		return true;
	}
</script>

<?php Lasso_Config::get_footer(); ?>
