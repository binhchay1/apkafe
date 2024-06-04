<?php

/**
 * URL details
 *
 * @package Lasso URL details
 */

use Lasso\Classes\Config as Lasso_Config;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Post as Lasso_Post;
use Lasso\Classes\Setting as Lasso_Setting;

require LASSO_PLUGIN_PATH . '/admin/views/header-new.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
<section class="py-5">
	<div class="container">

		<!-- TITLE & NAVIATION -->
		<?php require 'header.php'; ?>

		<?php
		if ($post_id > 0 && get_post_type($post_id) !== LASSO_POST_TYPE) {
			wp_redirect('edit.php?post_type=' . LASSO_POST_TYPE . '&page=dashboard'); // phpcs:ignore
			exit;
		}

		$lasso_cron = new Lasso_Cron();
		$new_url    = $_GET['url'] ?? '';

		$new_url    = '' !== $new_url ? $new_url : '';
		$flip_url   = '';
		$shortcode_html = '';
		$target_url = $lasso_url->target_url;
		$current_url = $lasso_url->target_url;
		if ($new_url) {
			$link_type      = $lasso_cron->get_link_type(0, $new_url);
			$shortcode_html = do_shortcode('[lasso id="0" title="' . $url_details_h1 . '" description="" badge="" ga="false"]');
		} else {
			$flip_url = $_GET['flip_url'] ?? '';
			if ($flip_url) {
				$target_url = $flip_url;
			}
			$link_type      = $lasso_url->link_type;
			$shortcode_html = do_shortcode('[lasso id="' . $lasso_url->lasso_id . '" ga="false"]');
		}

		$default_theme    = Lasso_Setting::lasso_get_setting('theme_name');
		$default_selected = '' === $lasso_url->display->theme ? 'selected' : '';
		$theme_options    = array('Cactus', 'Cutter', 'Flow', 'Geek', 'Lab', 'Llama', 'Money', 'Splash');
		$select_theme     = '<select id="theme_name" name="theme_name" class="form-control">';
		$select_theme    .= '<option value="" selected>Default (' . $default_theme . ')</option>';

		foreach ($theme_options as $theme) {
			$selected_theme_option = '';
			if ($theme === $lasso_url->display->theme) {
				$selected_theme_option = 'selected';
			}
			$select_theme .= '<option value="' . $theme . '" ' . $selected_theme_option . ' >' . $theme . '</option>';
		}

		$select_theme .= '</select>';

		$is_amazon_link    = Lasso_Amazon_Api::is_amazon_url($lasso_url->target_url);
		$amazon_product_id = $is_amazon_link ? Lasso_Amazon_Api::get_product_id_by_url($lasso_url->target_url) : '';
		$extend_product_id = $lasso_url->extend_product->product_id;
		$lasso_post        = Lasso_Post::create_instance($lasso_url->lasso_id, $lasso_url);
		$description       = $lasso_post->is_show_description() ? $lasso_url->description : '';

		?>
		<form id="url-details" autocomplete="off">
			<div class="row mb-5">
				<div class="col-lg-5 mb-lg-0 mb-5 h-100">
					<div class="white-bg rounded shadow p-4">

						<div class="d-none">
							<input id="is-saving" class="" type="hidden" value="0">
							<input id="total-links" class="" type="hidden" value="">
							<input id="lasso-id" class="" type="hidden" value="<?php echo $lasso_url->lasso_id; ?>">
							<input name="uri" class="lasso-admin-input" type="text" value="<?php echo esc_html($lasso_url->name); ?>">
							<input name="guid" class="lasso-admin-input hidden" type="text" value="<?php echo $lasso_url->guid; ?>">
							<input id="current-url" class="" type="hidden" value="<?php echo esc_html($lasso_url->target_url); ?>">
							<input id="flip-url" class="" type="hidden" value="<?php echo esc_html($flip_url); ?>">
						</div>

						<!-- NAME -->
						<div class="form-group mb-4">
							<label data-tooltip="This title will only be shown in displays"><strong>Name</strong> <i class="far fa-info-circle light-purple"></i></label>
							<input id="affiliate_name" name="affiliate_name" type="text" class="form-control" value="<?php echo str_replace('"', '&quot;', esc_html($lasso_url->name)); ?>" placeholder="URL Name Goes Here">
						</div>

						<!-- PERMALINK -->
						<div class="form-group mb-4 permalink-wrapper <?php echo Lasso_Amazon_Api::is_amazon_url($lasso_url->target_url) ? 'd-none' : '' ?>">
							<label data-tooltip="This slug will be used to cloak the the original target URL"><strong>Permalink</strong> <i class="far fa-info-circle light-purple"></i></label>
							<input id="permalink" name="permalink" type="text" class="form-control" value="<?php echo esc_html($lasso_url->slug); ?>" placeholder="affiliate-name">
						</div>

						<!-- PRIMARY TARGET URL -->
						<div class="row">
							<div class="col-lg-8">
								<div class="form-group mb-4">
									<label data-tooltip="The actual URL you want people to go to when they click a link"><strong>Primary Destination URL</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input id="affiliate_url" name="affiliate_url" type="text" data-original-value="<?php echo esc_html($current_url) ?>" class="form-control" value="<?php echo esc_html($target_url); ?>" placeholder="https://www.example.com/affiliate-id">
								</div>
							</div>

							<div class="col-lg-4">
								<div class="form-group mb-4">
									<label data-tooltip="This text will appear in the primary button"><strong>Button Text</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="buy_btn_text" value="<?php echo esc_html($lasso_url->display->primary_button_text); ?>" placeholder="<?php echo esc_html($lasso_url->display->primary_button_text_default); ?>">
								</div>
							</div>

							<div class="col-lg mb-4">
								<label class="toggle m-0 mr-1">
									<input id="url-open-link" name="open_new_tab" type="checkbox" <?php echo esc_html($lasso_url->url_detail_checkbox->open_new_tab); ?>>
									<span class="slider"></span>
								</label>
								<label data-tooltip="When enabled, users who click this link will have it loaded in a new tab.">New Window / Tab <i class="far fa-info-circle light-purple"></i></label>
							</div>

							<div class="col-lg text-right">
								<label class="toggle m-0 mr-1">
									<input name="enable_nofollow" id="url-en-nofollow" type="checkbox" <?php echo esc_html($lasso_url->url_detail_checkbox->enable_nofollow); ?>>
									<span class="slider"></span>
								</label>
								<label data-tooltip="When enabled, this link will be set to nofollow. This indicates to Google that it's an affiliate link.">NoFollow / NoIndex <i class="far fa-info-circle light-purple"></i></label>
							</div>
						</div>

						<!-- SECONDARY TARGET URL -->
						<div class="row">
							<div class="col-lg-8">
								<div class="form-group mb-4">
									<label data-tooltip="A secondary URL you want people to go to when they click an optional second button in displays">
										<strong>Secondary Destination URL</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="second_btn_url" value="<?php echo esc_html($lasso_url->display->secondary_url); ?>" placeholder="https://www.example.com/affiliate-id2">
								</div>
							</div>

							<div class="col-lg-4">
								<div class="form-group mb-4">
									<label data-tooltip="This text will appear in the optional secondary button"><strong>Button Text</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="second_btn_text" value="<?php echo esc_html($lasso_url->display->secondary_button_text); ?>" placeholder="<?php echo esc_html($lasso_url->display->secondary_button_text_default); ?>">
								</div>
							</div>

							<div class="col-lg-6 mb-4">
								<label class="toggle m-0 mr-1">
									<input id="url-open-link2" name="open_new_tab2" type="checkbox" <?php echo esc_html($lasso_url->url_detail_checkbox->open_new_tab2); ?>>
									<span class="slider"></span>
								</label>
								<label data-tooltip="When enabled, users who click this link will have it loaded in a new tab.">New Window / Tab <i class="far fa-info-circle light-purple"></i></label>
							</div>

							<div class="col-lg-6 text-right">
								<label class="toggle m-0 mr-1">
									<input name="enable_nofollow2" id="url-en-nofollow2" type="checkbox" <?php echo esc_html($lasso_url->url_detail_checkbox->enable_nofollow2); ?>>
									<span class="slider"></span>
								</label>
								<label data-tooltip="When enabled, this link will be set to nofollow. This indicates to Google that it's an affiliate link.">NoFollow / NoIndex <i class="far fa-info-circle light-purple"></i></label>
							</div>
						</div>

						<div class="row">
							<div class="col-lg-8">
								<div class="form-group mb-4">
									<label data-tooltip="A app store URL you want people to go to when they click an optional second button in displays">
										<strong>App Store URL</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="apple_btn_url" value="<?php echo esc_html($lasso_url->apple_btn_url); ?>" placeholder="https://apps.apple.com/us/app/netflix/id363590051?platform=iphone">
								</div>
							</div>

							<div class="col-lg-4">
								<div class="form-group mb-4">
									<label data-tooltip="This text will appear in the optional secondary button"><strong>Button Text</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="apple_btn_text" value="<?php echo esc_html($lasso_url->display->apple_btn_text); ?>" placeholder="<?php echo esc_html($lasso_url->display->secondary_button_text_default); ?>">
								</div>
							</div>

							<div class="col-lg-6 mb-4">
								<label class="toggle m-0 mr-1">
									<input id="url-open-link-apple" name="open_new_tab_apple" type="checkbox" <?php echo esc_html($lasso_url->url_detail_checkbox->open_new_tab_apple); ?>>
									<span class="slider"></span>
								</label>
								<label data-tooltip="When enabled, users who click this link will have it loaded in a new tab.">New Window / Tab <i class="far fa-info-circle light-purple"></i></label>
							</div>

							<div class="col-lg-6 text-right">
								<label class="toggle m-0 mr-1">
									<input name="enable_nofollow_apple" id="url-en-nofollow-apple" type="checkbox" <?php echo esc_html($lasso_url->url_detail_checkbox->enable_nofollow_apple); ?>>
									<span class="slider"></span>
								</label>
								<label data-tooltip="When enabled, this link will be set to nofollow. This indicates to Google that it's an affiliate link.">NoFollow / NoIndex <i class="far fa-info-circle light-purple"></i></label>
							</div>
						</div>

						<div class="row">
							<div class="col-lg-8">
								<div class="form-group mb-4">
									<label data-tooltip="A google app URL you want people to go to when they click an optional second button in displays">
										<strong>Google App URL</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="google_btn_url" value="<?php echo esc_html($lasso_url->google_btn_url); ?>" placeholder="https://play.google.com/store/apps/details?id=net.kairosoft.android.piratedx">
								</div>
							</div>

							<div class="col-lg-4">
								<div class="form-group mb-4">
									<label data-tooltip="This text will appear in the optional secondary button"><strong>Button Text</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="google_btn_text" value="<?php echo esc_html($lasso_url->display->google_btn_text); ?>" placeholder="<?php echo esc_html($lasso_url->display->secondary_button_text_default); ?>">
								</div>
							</div>

							<div class="col-lg-6 mb-4">
								<label class="toggle m-0 mr-1">
									<input id="url-open-link-google" name="open_new_tab_google" type="checkbox" <?php echo esc_html($lasso_url->url_detail_checkbox->open_new_tab_google); ?>>
									<span class="slider"></span>
								</label>
								<label data-tooltip="When enabled, users who click this link will have it loaded in a new tab.">New Window / Tab <i class="far fa-info-circle light-purple"></i></label>
							</div>

							<div class="col-lg-6 text-right">
								<label class="toggle m-0 mr-1">
									<input name="enable_nofollow_google" id="url-en-nofollow-google" type="checkbox" <?php echo esc_html($lasso_url->url_detail_checkbox->enable_nofollow_google); ?>>
									<span class="slider"></span>
								</label>
								<label data-tooltip="When enabled, this link will be set to nofollow. This indicates to Google that it's an affiliate link.">NoFollow / NoIndex <i class="far fa-info-circle light-purple"></i></label>
							</div>
						</div>

						<div class="row">
							<div class="col-lg-8">
								<div class="form-group mb-4">
									<label data-tooltip="A third URL you want people to go to when they click an optional second button in displays">
										<strong>Third Destination URL</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="third_btn_url" value="<?php echo esc_html($lasso_url->display->third_url); ?>" placeholder="https://www.example.com/affiliate-id2">
								</div>
							</div>

							<div class="col-lg-4">
								<div class="form-group mb-4">
									<label data-tooltip="This text will appear in the optional secondary button"><strong>Button Text</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="third_btn_text" value="<?php echo esc_html($lasso_url->display->third_btn_text); ?>" placeholder="<?php echo esc_html($lasso_url->display->secondary_button_text_default); ?>">
								</div>
							</div>

							<div class="col-lg-6 mb-4">
								<label class="toggle m-0 mr-1">
									<input id="url-open-link3" name="open_new_tab3" type="checkbox" <?php echo esc_html($lasso_url->url_detail_checkbox->open_new_tab3); ?>>
									<span class="slider"></span>
								</label>
								<label data-tooltip="When enabled, users who click this link will have it loaded in a new tab.">New Window / Tab <i class="far fa-info-circle light-purple"></i></label>
							</div>

							<div class="col-lg-6 text-right">
								<label class="toggle m-0 mr-1">
									<input name="enable_nofollow3" id="url-en-nofollow3" type="checkbox" <?php echo esc_html($lasso_url->url_detail_checkbox->enable_nofollow3); ?>>
									<span class="slider"></span>
								</label>
								<label data-tooltip="When enabled, this link will be set to nofollow. This indicates to Google that it's an affiliate link.">NoFollow / NoIndex <i class="far fa-info-circle light-purple"></i></label>
							</div>
						</div>

						<div class="row">
							<div class="col-lg-8">
								<div class="form-group mb-4">
									<label data-tooltip="A fourth URL you want people to go to when they click an optional second button in displays">
										<strong>Fourth Destination URL</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="fourth_btn_url" value="<?php echo esc_html($lasso_url->display->fourth_url); ?>" placeholder="https://www.example.com/affiliate-id2">
								</div>
							</div>

							<div class="col-lg-4">
								<div class="form-group mb-4">
									<label data-tooltip="This text will appear in the optional secondary button"><strong>Button Text</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="fourth_btn_text" value="<?php echo esc_html($lasso_url->display->fourth_btn_text); ?>" placeholder="<?php echo esc_html($lasso_url->display->secondary_button_text_default); ?>">
								</div>
							</div>

							<div class="col-lg-6 mb-4">
								<label class="toggle m-0 mr-1">
									<input id="url-open-link4" name="open_new_tab4" type="checkbox" <?php echo esc_html($lasso_url->url_detail_checkbox->open_new_tab4); ?>>
									<span class="slider"></span>
								</label>
								<label data-tooltip="When enabled, users who click this link will have it loaded in a new tab.">New Window / Tab <i class="far fa-info-circle light-purple"></i></label>
							</div>

							<div class="col-lg-6 text-right">
								<label class="toggle m-0 mr-1">
									<input name="enable_nofollow4" id="url-en-nofollow4" type="checkbox" <?php echo esc_html($lasso_url->url_detail_checkbox->enable_nofollow4); ?>>
									<span class="slider"></span>
								</label>
								<label data-tooltip="When enabled, this link will be set to nofollow. This indicates to Google that it's an affiliate link.">NoFollow / NoIndex <i class="far fa-info-circle light-purple"></i></label>
							</div>
						</div>

						<div class="row">
							<div class="col-lg-6">
								<div class="form-group mb-4">
									<label data-tooltip="Price of app in store">
										<strong>Price</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="price_app" value="<?php echo $lasso_url->price ?>" placeholder="Price app">
								</div>
							</div>

							<div class="col-lg-6">
								<div class="form-group mb-4">
									<label data-tooltip="Developer of app in store">
										<strong>Developer</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="developer_app" value="<?php echo $lasso_url->developer ?>" placeholder="Developer app">
								</div>
							</div>

							<div class="col-lg-6">
								<div class="form-group mb-4">
									<label data-tooltip="Rating of app in store">
										<strong>Rating</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="rating_app" value="<?php echo $lasso_url->rating ?>" placeholder="Rating app">
								</div>
							</div>

							<div class="col-lg-6">
								<div class="form-group mb-4">
									<label data-tooltip="Category of app in store">
										<strong>Categories</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="categories_app" value="<?php echo $lasso_url->categories ?>" placeholder="Categories app">
								</div>
							</div>

							<div class="col-lg-6">
								<div class="form-group mb-4">
									<label data-tooltip="Size off app in store">
										<strong>Size</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="size_app" value="<?php echo $lasso_url->size ?>" placeholder="Size app">
								</div>
							</div>

							<div class="col-lg-6">
								<div class="form-group mb-4">
									<label data-tooltip="Version of app in store">
										<strong>Version</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="version_app" value="<?php echo $lasso_url->version ?>" placeholder="Version app">
								</div>
							</div>

							<div class="col-lg-6">
								<div class="form-group mb-4">
									<label data-tooltip="Update date of app in post">
										<strong>Updated on</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="updated_on_app" value="<?php echo $lasso_url->updated_on ?>" placeholder="Updated on">
								</div>
							</div>

							<div class="col-lg-12">
								<div class="form-group mb-4">
									<label data-tooltip="Screen shot of app in store">
										<strong>Screen shots</strong> <i class="far fa-info-circle light-purple"></i></label>
									<?php if (isset($lasso_url->screen_shots)) { ?>
										<?php if (!is_array($lasso_url->screen_shots)) { ?>
											<?php $array_screenshot = json_decode($lasso_url->screen_shots, true); ?>
											<div class="owl-carousel owl-theme">
												<?php foreach ($array_screenshot as $shot) { ?>
													<img class="item" src="<?php echo $shot ?>" width="106" height="66"/>
												<?php } ?>
											</div>
										<?php } else { ?>
											<div class="owl-carousel owl-theme">
												<?php $array_screenshot = explode(PHP_EOL, $lasso_url->screen_shots); ?>
												<?php foreach ($array_screenshot as $shot) { ?>
													<img class="item" src="<?php echo $shot ?>" width="100" height="100"/>
												<?php } ?>
											</div>
										<?php } ?>
									<?php } ?>
								</div>
							</div>
						</div>

						<!-- GROUPS -->
						<div class="form-group mb-4">
							<label data-tooltip="Group this URL for better organization and to use in group displays"><strong>Groups</strong> <i class="far fa-info-circle light-purple"></i></label>
							<select class="form-control" name="categories[]" id="basic-categories" data-placeholder="Select a group" multiple>
								<?php echo $category_options; ?>
							</select>
						</div>

						<!-- CUSTOM FIELDS -->
						<div class="form-group mb-1">
							<div class="add-custom-fields">
								<div id="custom-fields" class="ui-sortable">
								</div>
								<button class="btn" type="button" data-toggle="modal" data-target="#field-create">Add Custom Field</button>
								<a href="https://support.getlasso.co/en/articles/5150630-how-to-use-fields" target="_blank" class="btn ml-3 learn-btn">
									<i class="far fa-info-circle"></i> Learn
								</a>
							</div>
						</div>
					</div>
				</div>

				<!-- EDIT MORE DETAILS -->
				<div class="col-lg-7">
					<div class="white-bg rounded shadow p-4">
						<div class="image_loading onboarding d-none"></div>
						<div id="demo_display_box">
							<?php
							if ('' !== $shortcode_html) {
								echo $shortcode_html;
							}
							?>
						</div>

						<!-- IMAGE PREVIEW -->
						<div class="image_container position-relative d-none image_editor_wrapper" id="image_editor">
							<a href="#" id="lasso-thumbnail">
								<div class="image_wrapper d-block">
									<img id="render_thumbnail" src="<?php echo esc_html($lasso_url->image_src); ?>" loading="lazy" class="render_thumbnail img-fluid url_image">
									<div class="image_loading d-none"></div>
									<div class="image_hover">
										<div class="image_update"><i class="far fa-camera-alt"></i> Update Image</div>
									</div>
								</div>
							</a>
						</div>

						<input type="hidden" id="thumbnail_id" name="thumbnail_id" value="<?php echo $lasso_url->thumbnail_id; ?>" />

						<!-- THEME & IMAGE REFRESH -->
						<div class="row">
							<div class="col-md">
								<div class="form-group mb-4">
									<label data-tooltip="Choose the default display theme for this link."><strong>Display Theme</strong> <i class="far fa-info-circle light-purple"></i></label>
									<?php echo $select_theme; ?>
								</div>
							</div>


							<div class="col-md thumbnail-wrapper <?php echo LASSO_AMAZON_PRODUCT_TYPE === $link_type && !$new_url ? "" : "d-none" ?>">
								<div class="input-group">
									<label data-tooltip="Click the button below to grab an updated image from Amazon."><strong>Amazon Image</strong> <i class="far fa-info-circle light-purple"></i></label>

									<div class="row">
										<div class="col pr-0">
											<input type="text" id="thumbnail_image_url" class="form-control form-control-append" value="<?php echo esc_html($lasso_url->image_src); ?>" readonly>
										</div>

										<div class="col-2 p-0">
											<a href="#" id="lasso-render-image" class="btn btn-append refresh-image">Refresh</a>
										</div>
									</div>
								</div>
							</div>

						</div>

						<!-- PRICE & BADGE TEXT -->
						<div class="row">
							<div class="col-lg">
								<div class="form-group mb-4">
									<label data-tooltip="This text will appear as a badge in displays for this URL."><strong>Badge Text</strong> <i class="far fa-info-circle light-purple"></i></label>
									<input type="text" class="form-control" id="badge_text" value="<?php echo esc_html($lasso_url->display->badge_text); ?>" placeholder="Our Pick">
								</div>
							</div>

							<div class="col-lg">
								<div class="form-group">
									<label data-tooltip="This price can be any text or amount that'll only be shown in displays. Prices automatically update every 24 hours with integrations like Amazon.">
										<strong>Price</strong> <i class="far fa-info-circle light-purple"></i>
									</label>

									<div class="float-right">
										<label data-tooltip="Turn this on to show the price in this display."><i class="far fa-info-circle light-purple"></i></label>
										<label class="toggle">
											<input id="show_pricing" type="checkbox" <?php echo esc_html($lasso_url->url_detail_checkbox->show_price); ?>>
											<span class="slider"></span>
										</label>
									</div>

									<input name="price" type="text" class="form-control" value="<?php echo esc_html($lasso_url->price); ?>" placeholder="$99.99" id="price" <?php echo $amazon_product_id ? 'readonly' : '' ?>>

									<?php if ($amazon_product_id) :
									?>
										<em class="small"><span class="dark-gray">Amazon prices updated via </span> <a href="edit.php?post_type=lasso-urls&page=settings-amazon" class="purple underline">Amazon API settings</a></em>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<!-- DESCRIPTION -->
						<div class="form-group mb-4">
							<label data-tooltip="Share more details about the product or service you're linking to. A short paragraph of 1-4 sentences typically performs best."><strong>Description</strong> <i class="far fa-info-circle light-purple"></i></label>
							<div class="form-control" id="description">
								<?php echo $description; ?>
							</div>
						</div>

						<!-- DISCLOSURE -->
						<div class="form-group mb-4">
							<label data-tooltip="This disclosure will only be shown in displays"><strong>Disclosure</strong> <i class="far fa-info-circle light-purple"></i></label>
							<textarea class="form-control" id="disclosure" rows="2" placeholder="<?php echo esc_html($lasso_url->display->disclosure_text_default); ?>"><?php echo esc_html($lasso_url->display->disclosure_text); ?></textarea>
						</div>

						<!-- DISPLAY TOGGLES -->
						<div class="form-group mb-3">
							<div class="form-row">
								<div class="col-lg-6">
									<label class="toggle m-0 mr-1">
										<input id="enable_sponsored" type="checkbox" <?php echo $lasso_url->url_detail_checkbox->enable_sponsored; ?>>
										<span class="slider"></span>
									</label>
									<label data-tooltip="When enabled, this link will be set to sponsored.">Sponsored <i class="far fa-info-circle light-purple"></i></label>

								</div>
								<div class="col-lg-6">
									<label class="toggle m-0 mr-1">

										<input id="show_disclosure" type="checkbox" <?php echo $lasso_url->url_detail_checkbox->show_disclosure; ?>>
										<span class="slider"></span>
									</label>
									<label data-tooltip="Turn this on to show the disclosure in Displays for this Lasso Link.">Show Disclosure <i class="far fa-info-circle light-purple"></i></label>
								</div>
							</div>
						</div>

						<!-- LINK ACTION TOGGLES -->
						<div class="form-group mb-1">
							<div class="form-row">
								<div class="col-lg">
									<label class="toggle m-0 mr-1">
										<input id="is_opportunity" name="is_opportunity" type="checkbox" <?php echo $lasso_url->url_detail_checkbox->is_opportunity; ?>>
										<span class="slider"></span>
									</label>
									<label class="mb-1" data-tooltip="When disabled, Link Opportunities will not show for this Lasso Link.">Detect Opportunities <i class="far fa-info-circle light-purple"></i></label>
								</div>

								<div class="col-lg link-cloaking-wrapper <?php echo Lasso_Amazon_Api::is_amazon_url($lasso_url->target_url) ? 'd-none' : ''; ?>">
									<label class="toggle m-0 mr-1">
										<input name="link_cloaking" id="url-en-link-cloaking" type="checkbox" <?php echo $lasso_url->url_detail_checkbox->link_cloaking; ?>>
										<span class="slider"></span>
									</label>
									<label data-tooltip="The destination of this URL will be redirected from a pretty url on your domain. All non-Amazon Affiliate links should have this enabled.">Link Cloaking <i class="far fa-info-circle light-purple"></i></label>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
		</form>

		<div class="row align-items-center">
			<div class="col-lg order-lg-2 text-lg-right text-center mb-4">
				<a id="learn-more-link-details" href="https://support.getlasso.co/en/articles/5847370-how-to-use-the-link-details-page" target="_blank" class="btn black white-bg black-border mr-3">Learn About This Page</a>
				<button id="btn-save-url" class="btn">Save Changes</button>
			</div>

			<div class="col-lg text-lg-left text-center mb-4">
				<a href="#" id="btn-confirm-delete" class="red hover-red-text"><i class="far fa-trash-alt"></i> Delete This Link</a>
			</div>
		</div>

	</div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script>
	jQuery('.owl-carousel').owlCarousel({
		loop: true,
		margin: 10,
		nav: true,
		responsive: {
			0: {
				items: 1
			},
			600: {
				items: 3
			},
			1000: {
				items: 5
			}
		}
	})

	jQuery(document).ready(function() {
		var initial_value = get_all_values();
		var amazon_product_id = '<?php echo esc_js($amazon_product_id ?? ''); ?>';
		var lasso_id = '<?php echo esc_js($lasso_url->lasso_id); ?>';
		var defaultTheme = '<?php echo esc_js($default_theme); ?>';
		var rel2 = '<?php echo esc_js($lasso_url->html_attribute->rel2); ?>';
		var amazonIsPrime = '<?php echo esc_js($lasso_url->amazon->is_prime); ?>';
		var target2 = '<?php echo esc_js($lasso_url->html_attribute->target2); ?>';
		var currentTheme = '<?php echo esc_js($lasso_url->display->theme); ?>';
		var show_discount_pricing = <?php echo esc_js($lasso_url->amazon->show_discount_pricing ? 'true' : 'false'); ?>;
		var discount_pricing_html = '<?php echo esc_js($lasso_url->amazon->discount_pricing_html); ?>';
		if (currentTheme === '') {
			currentTheme = defaultTheme;
		}
		var layoutBoxNumber = getLayoutBoxNumber(currentTheme);
		var loading_percent = 15;
		const flip_url = jQuery("#flip-url").val();
		const current_url = jQuery("#current-url").val();

		var toolbarOptions = [
			[
				'bold',
				'italic',
				'underline',
				'strike'
			],
			[
				'link',
				{
					'list': 'bullet'
				}
			],
			[{
				'color': []
			}, {
				'background': []
			}],
			['clean'],
		];

		var quill_options = {
			theme: 'snow',
			placeholder: 'Enter a description',
			modules: {
				toolbar: toolbarOptions,
				clipboard: {
					matchVisual: false
				}
			},
		};

		var quill = new Quill('#description', quill_options);

		quill.root.innerHTML = `<?php echo $description; ?>`;
		quill.on('editor-change', function(eventName, ...args) {
			if ('selection-change' === eventName) {
				quill.update();
			}
		});

		jQuery('.ql-editor').focus(
			function() {
				jQuery(this).parent('div').attr('style', 'border-color: var(--light-purple) !important');
			}).blur(
			function() {
				jQuery(this).parent('div').removeAttr('style');
			});

		jQuery('.image_loading').html(get_loading_image());
		jQuery('.lasso-image').html(jQuery('#image_editor').html());

		var progessPercentage = 0;
		var progressInterval = 100;

		<?php if ($lasso_url->issue->broken) { ?>
			lasso_helper.errorScreen("Your Target URL is broken.");
		<?php } ?>

		<?php if ($lasso_url->issue->out_of_stock) { ?>
			lasso_helper.warningScreen("This product may be out of stock.");
		<?php } ?>

		jQuery("#basic-keywords").select2({
			width: '100%',
			allowClear: true,
			tags: true,
		});

		jQuery("#basic-categories").select2({
			width: '100%',
			allowClear: true,
			tags: true,
		});

		jQuery(".lasso-image").removeAttr("href");
		jQuery(".lasso-image").removeAttr("target");
		jQuery('#theme_name').on('change', function() {
			refresh_display({
				is_use_loading: true
			});
		});

		function getLayoutBoxNumber(themeName) {
			if (['Cutter', 'Flow', 'Geek', 'Splash'].indexOf(themeName) > -1) {
				return 2;
			} else if (['Lab', 'Llama'].indexOf(themeName) > -1) {
				return 3;
			}
			return 6;
		}

		/**
		 * optional_data = {
		 *     is_use_loading: bool,
		 *     modal: jquery Modal object
		 * }
		 *
		 * */
		function refresh_display(optional_data = {}) {
			var is_use_loading = true;
			var modal = null;
			if (optional_data.is_use_loading !== '') {
				is_use_loading = optional_data.is_use_loading;
			}

			if (optional_data.modal !== '') {
				modal = optional_data.modal;
			}

			jQuery.ajax({
					url: lassoOptionsData.ajax_url,
					type: 'post',
					data: get_payload_to_save_url(),
					beforeSend: function(xhr) {
						if (is_use_loading) {
							jQuery('#demo_display_box').addClass('d-none');
							jQuery('.image_loading').removeClass('d-none');
						}
					}
				})
				.done(function(res) {
					if (res.success) {
						if (current_url !== flip_url) {
							lasso_helper.successScreen("Flipped to sponsored Amazon products");
						}
						jQuery.ajax({
								url: lassoOptionsData.ajax_url,
								type: 'post',
								data: {
									action: 'lasso_get_display_html_in_url_details',
									lasso_id: lasso_id,
									theme: jQuery("#theme_name").val().toLowerCase(),
									default_theme: '<?php echo strtolower($default_theme); ?>',
								},
								beforeSend: function() {
									if (is_use_loading) {
										jQuery('.image_loading').removeClass('d-none');
									}
								}
							})
							.done(function(res) {
								if (modal) {
									modal.hide();
								}
								res = res.data;
								var additional_info = res.additional_info;
								update_ui(additional_info);

								jQuery("#demo_display_box").html(res.html);
								jQuery(".lasso-image").removeAttr("href");
								jQuery(".lasso-image").removeAttr("target");
								var image_editor_wrapper = jQuery('.image_editor_wrapper');
								image_editor_wrapper.find('.render_thumbnail').attr('src', jQuery('#thumbnail_image_url').val());
								jQuery('.lasso-image').html(jQuery('#image_editor').html());


								// apply unsaved dynamic changes after theme change
								let themeName = jQuery('#theme_name').val();
								if (themeName === '') {
									themeName = defaultTheme;
								}
								layoutBoxNumber = getLayoutBoxNumber(themeName);

								jQuery('#buy_btn_text').trigger('keyup');
								jQuery('#badge_text').trigger('keyup');
								updateSecondaryButton();
								updatePriceSection();
								updateDisclosureSection();
							})
							.fail(function(xhr, status, error) {
								console.log("fail");
								if (xhr.lasso_error) {
									error = xhr.lasso_error;
								}
								lasso_helper.errorScreen(error);
							})
							.always(function() {
								jQuery('.image_loading').addClass('d-none');
								jQuery('#demo_display_box').removeClass('d-none');
								jQuery('#lasso-thumbnail').click(function() {
									set_thumbnail();
								});
							});
					}
				})
				.fail(function(xhr, status, error) {
					if (xhr.lasso_error) {
						error = xhr.lasso_error;
					}
					lasso_helper.errorScreen(error);
				})
				.always(function() {});
		}

		jQuery("#affiliate_name").off("keyup").on("keyup", function(event) {
			jQuery("#lasso-url-heading").text(jQuery(event.target).val());
			jQuery(".lasso-title").text(jQuery(event.target).val());
		});

		jQuery(function() {
			jQuery(document).on("change", "input.field_visible", function() {
				prepUpdate();
			});

			jQuery(document).on("focusout", "input.form-control.field_value", function() {
				prepUpdate();
			});

			jQuery(document).on("focusout", "input.form-control.field_value.star_value.float-left", function() {
				prepUpdate();
			});

			jQuery(document).on("focusout", "textarea.form-control.field_value", function() {
				prepUpdate();
			});

			jQuery(document).on("input", "input.form-control.field_value.star_value.float-left", function() {
				var stars = jQuery(this).val();
				if (stars == 0 || isNaN(stars)) {
					stars = 1;
				}
				if (stars >= 5) {
					stars = 5;
				}
				stars = parseFloat(stars).toFixed(1);
				jQuery(this).val(stars);
				jQuery(this).closest('div').children(".lasso-stars").css("--rating", stars);
			});

			// ? Add field to product
			jQuery(document).on("click", ".js-add-field-to-product", function() {
				let row = jQuery(this);
				let field_id = row.data('field-id');
				let post_id = row.data('product-id');
				let $report_content = jQuery('#report-content');
				let $field_from_library_loading = jQuery('#field-from-library-loading');
				console.log("Adding...");

				jQuery.ajax({
						url: '<?php echo Lasso_Helper::get_ajax_url(); ?>',
						type: 'post',
						data: {
							action: 'lasso_add_field_to_page',
							field_id: field_id,
							post_id: post_id
						},
						beforeSend: function(xhr) {
							$report_content.hide();
							$field_from_library_loading.html(get_loading_image());
							jQuery('#demo_display_box').addClass('d-none');
							jQuery('.image_loading').removeClass('d-none');
						}
					})
					.done(function(res) {
						res = res.data;
						jQuery('#field-create').modal('hide');
						get_custom_fields_details();
						if (res.status) {
							// Show in URL Details
							console.log("Added.");
							if ('function' === typeof refresh_display) {
								refresh_display();
							}
						} else {
							// Alert to failure
							console.log("Failed!");
						}
					})
					.always(function() {
						$report_content.show();
						$field_from_library_loading.html('');
						jQuery('#demo_display_box').removeClass('d-none');
						jQuery('.image_loading').addClass('d-none');
					});
			});

			function get_custom_fields_details(page = 1, limit = 10) {
				let link_type = 'url-details';
				let tab_filter = 'url-details';
				let container = jQuery('#custom-fields');

				jQuery.ajax({
						url: '<?php echo Lasso_Helper::get_ajax_url(); ?>',
						type: 'post',
						data: {
							action: 'lasso_report_urls',
							post_id: '<?php echo $_GET['post_id'] ?? ''; ?>',
							link_type: link_type,
							pageNumber: page,
							pageSize: limit,
							filter: tab_filter,
						},
						beforeSend: function() {

						}
					})
					.done(function(response) {
						let html = '';

						if (response.success) {
							let post = response.data.post;
							let responseData = response.data;
							// let html = get_html(responseData.data, post);
							let data = responseData.data;

							if (data.length > 0) {
								for (let index = 0; index < data.length; index++) {
									const element = data[index];
									html += `<?php include LASSO_PLUGIN_PATH . '/admin/views/rows/field-details-row.php'; ?>`;
								}
							} else {
								html = `
									<div class="row align-items-center" id="not-found-wrapper">
										<div class="col text-center p-5 m-5">
											<i class="far fa-skull-cow fa-7x mb-3"></i>
											<h3>Looks like we're coming up empty.</h3>
										</div>
									</div>
								`;
							}
						} else {
							html = 'Failed to load data.';
						}

						container.html(html);
					}).fail(function(xhr) {
						container.html('Failed to load data.');
					});
			}

			jQuery(document).on("click", ".js-remove-field", function() {
				var remove_button = jQuery("#js-field-remove-button");
				var field = jQuery(this);
				remove_button.data('field-id', field.data('field-id'));
				remove_button.data('lasso-id', field.data('lasso-id'));
				jQuery("#js-field-name").html(field.data('field-name'));
			});

			jQuery("#custom-fields").sortable({
				cursor: "move",
				//cancel: "div.static",
				stop: function() {
					jQuery("#custom-fields div.static").each(function() {
						var desiredLocation = jQuery(this).attr("id").replace("static-", "");
						var currentLocation = jQuery(this).index();
						while (currentLocation < desiredLocation) {
							jQuery(this).next().insertBefore(this);
							currentLocation++;
						}
						while (currentLocation > desiredLocation) {
							jQuery(this).prev().insertAfter(this);
							currentLocation--;
						}
					});
					prepUpdate();
				}
			});

			function prepUpdate() {
				var selectedData = new Array();
				jQuery('#custom-fields>div').each(function() {
					let field_id = jQuery(this).attr("data-field-id");
					if (!lasso_helper.is_empty(field_id)) {
						var field_value = jQuery("#field_" + field_id).val();
						field_value = lasso_helper.remove_empty_line_from_string(field_value);
						selectedData.push([
							jQuery(this).attr("data-field-id"),
							jQuery(this).attr("data-lasso-id"),
							field_value,
							jQuery("#fieldvisible_" + jQuery(this).attr("data-field-id")).prop("checked"),
							jQuery("#show_field_name_" + jQuery(this).attr("data-field-id")).prop("checked")
						]);
					}
				});
				console.log(selectedData);
				updateFields(selectedData);
			}

			function updateFields(data, set) {
				jQuery.ajax({
						url: lassoOptionsData.ajax_url,
						type: 'post',
						data: {
							action: 'lasso_save_field_positions',
							data: data
						},
						beforeSend: function() {
							jQuery('#demo_display_box').addClass('d-none');
							jQuery('.image_loading').removeClass('d-none');
						}
					})
					.done(function(res) {
						res = res.data;
						console.log(res);
						refresh_display();
					});
			}

			jQuery(document).on("focusout", "#affiliate_url", function() {
				var original_value = jQuery(this).data('original-value');
				original_value = original_value ? original_value.toString().trim() : '';
				var new_value = jQuery(this).val().trim();
				if (new_value && original_value !== new_value) {
					jQuery(this).data('original-value', new_value);
					var modal_waiting = new lasso_helper.lasso_generate_modal_dynamic();
					modal_waiting.init_simple_modal();
					modal_waiting.set_main_content("Fetching new data");
					modal_waiting.show();

					loading_percent = 15;
					var loading_interval = setInterval(function() {
						if (loading_percent > 100) {
							clearInterval(loading_interval)
						} else {
							lasso_helper.setProgress(loading_percent, '#' + modal_waiting.get_modal_id());
						}
						loading_percent++;
					}, 200);
					refresh_display({
						is_use_loading: false,
						modal: modal_waiting
					});
				}

			});

			if (flip_url !== '' && flip_url !== current_url) {
				jQuery("#affiliate_url").trigger("focusout");
			}
		});

		quill.on('text-change', function(delta, oldDelta, source) {
			let description_value = quill.root.innerHTML;
			description_value = '<p><br></p>' == description_value ? '' : description_value;
			add_description_block();
			jQuery(".lasso-description").html(description_value);
			// quill.root.innerHTML = description_value; // ? fix emoji error
		});

		jQuery("#buy_btn_text").off("keyup").on("keyup", function(event) {
			var button1_text = jQuery(event.target).val();
			if (button1_text.trim() === '') {
				jQuery(".lasso-button-1").text(jQuery(event.target).attr('placeholder'));
			} else {
				jQuery(".lasso-button-1").text(button1_text);
			}
		});

		jQuery("#second_btn_text").off("keyup").on("keyup", updateSecondaryButton);
		jQuery("#second_btn_url").off("keyup").on("keyup", updateSecondaryButton);

		jQuery("#badge_text").off("keyup").on("keyup", function(event) {
			var badge_text = jQuery(event.target).val();

			if (badge_text == "") {
				jQuery(".lasso-badge").remove();
			} else {
				if (jQuery(".lasso-badge").length == 0) {
					jQuery(".lasso-display").prepend("<div class='lasso-badge'>" + badge_text + "</div>");
				} else {
					jQuery(".lasso-badge").text(badge_text);
				}
			}
		});

		jQuery("#price").off("keyup").on("keyup", updatePriceSection);
		jQuery("#disclosure").off("keyup").on("keyup", updateDisclosureSection);
		jQuery("#show_disclosure").off("change").on("change", updateDisclosureSection);
		jQuery("#show_pricing").off("change").on("change", updatePriceSection);

		jQuery('#lasso-render-image').click(function() {
			render_image();
		});

		function render_image() {
			let urlRegex = /^((([A-Za-z]{3,9}:(?:\/\/)?)(?:[\-;:&=\+\$,\w]+@)?[A-Za-z0-9\.\-]+|(?:www\.|[\-;:&=\+\$,\w]+@)[A-Za-z0-9\.\-]+)((?:\/[\+~%\/\.\w\-_]*)?\??(?:[\-\+=&;%@\.\w_]*)#?(?:[\.\!\/\\\w]*))?).(jpeg|jpg|gif|png)/i;
			let image_link = jQuery("#thumbnail_image_url").val();
			image_link = image_link.split('?')[0];
			if (image_link.trim() == "") {
				// set_thumbnail();
			} else if (urlRegex.test(image_link) || amazon_product_id != '') {
				jQuery.ajax({
						url: lassoOptionsData.ajax_url,
						type: 'post',
						data: {
							action: 'upload_thumbnail',
							lasso_id: lasso_id,
							product_url: jQuery('#affiliate_url').val(),
							product_id: amazon_product_id,
							image_url: image_link,
							is_product_url: amazon_product_id != '',
						},
						beforeSend: function() {
							jQuery('.image_loading').removeClass('d-none');
							jQuery('#demo_display_box').addClass('d-none');
						}
					})
					.done(function(res) {
						res = res.data;
						console.log(res);
						jQuery("#render_thumbnail").attr('src', res.thumbnail);
						jQuery("#thumbnail_id").val(res.thumbnail_id);
						jQuery("#thumbnail_image_url").val(res.thumbnail);

						var lasso_name = jQuery('#affiliate_name');
						if (lasso_name.val().trim() == '' || lasso_name.val().trim() == 'Add a Link Title') {
							lasso_name.val(res.product_name);
							jQuery('.lasso-title').text(res.product_name);
							jQuery('#lasso-url-heading').text(res.product_name);
						}

						if (res.amazon_product) {
							jQuery('#price').val(res.amazon_product.price);
							jQuery('div.lasso-price > div.lasso-price-value').val(res.amazon_product.price);

							if (res.amazon_product.url != '') {
								jQuery('#affiliate_url').val(res.amazon_product.url);
								jQuery('.js-permalink').text(res.amazon_product.url);
								jQuery('.js-permalink').attr('href', res.amazon_product.url);
								jQuery('.lasso-title').attr('href', res.amazon_product.url);
								jQuery('.lasso-button-1').attr('href', res.amazon_product.url);

								if (res.amazon_product.hasOwnProperty('show_discount_pricing')) {
									show_discount_pricing = res.amazon_product.show_discount_pricing;
									discount_pricing_html = res.amazon_product.discount_pricing_html;
								}
							}

							updatePriceSection();
						}
					})
					.fail(function(xhr, status, error) {
						if (xhr.lasso_error) {
							error = xhr.lasso_error;
						}
						lasso_helper.errorScreen(error);
					})
					.always(function() {
						jQuery('.image_loading').addClass('d-none');
						jQuery('#demo_display_box').removeClass('d-none');
					});

			} else {
				lasso_helper.errorScreen("Invalid image url.");
			}

			return;
		}

		function setProgressComplete() {
			progessPercentage = 100;
			jQuery("#url-save").find(".progress-bar").css({
				width: progessPercentage + '%'
			});
		}

		function progress() {
			if (progessPercentage <= 100) {
				progessPercentage += 25;
				jQuery("#url-save").find(".progress-bar").css({
					width: progessPercentage + '%'
				});
			} else {
				clearProgressInterval();
			}
		}

		function clearProgressInterval() {
			clearInterval(progressInterval);
		}

		function change_progress_message() {
			let default_message = 'Just saving your URL changes';
			let keyword_only = 'Just checking all the affected keywords on your site to make sure everything\'s updated.';
			let toggle_only = 'Saving your changes.';
			let both_keywords_and_toggle = 'Just checking all the affected links and keywords on your site to make sure everything\'s updated';
			let slug_only = '';
			let message = '';

			let old_keywords = '<?php echo rawurlencode($old_keywords); ?>';
			let new_keywords = jQuery("#basic-keywords").val() ? jQuery("#basic-keywords").val().toString() : '';

			let is_keyword_changed = (old_keywords != new_keywords);

			let old_nofollow = '<?php echo $old_enable_nofollow ?? ''; ?>';
			let old_new_tab = '<?php echo $old_open_new_tab ?? ''; ?>';
			let new_nofollow = jQuery("#url-en-nofollow").prop("checked") ? 'true' : 'false';
			let new_open_tab = jQuery("#url-open-link").prop("checked") ? 'true' : 'false';

			let is_toggle_changed = ((old_nofollow != new_nofollow) || (old_new_tab != new_open_tab));

			if (is_keyword_changed && is_toggle_changed) {
				message = both_keywords_and_toggle;
			} else if (is_toggle_changed) {
				message = toggle_only;
			} else if (is_keyword_changed) {
				message = keyword_only;
			} else {
				message = default_message;
			}

			jQuery('#url-save').find('p').text(message);
		}

		function get_all_values() {
			let all_values = {};
			let all_fields = jQuery(":input.form-control:not(:checkbox):not(:hidden):not(:button):not([type=search]):not([id=add-new-url-box])");
			all_fields.each(function(index, el) {
				all_values[jQuery(el).attr('id')] = jQuery(el).val();
			});
			let all_switches = jQuery("input.form-control:checkbox");
			all_switches.each(function(index, el) {
				all_values[jQuery(el).attr('id')] = jQuery(el).prop("checked") ? 1 : 0;
			});
			return all_values;
		}

		function check_changes() {
			let is_changed = false;
			let new_values = get_all_values();
			Object.keys(new_values).forEach(function(key) {
				if (Array.isArray(new_values[key]) && Array.isArray(initial_value[key])) {
					let diff = new_values[key]
						.filter(x => !initial_value[key].includes(x))
						.concat(initial_value[key].filter(x => !new_values[key].includes(x)));
					if (diff.length > 0) {
						is_changed = true;
					}
				} else if (new_values[key] != initial_value[key]) {
					is_changed = true;
				}
			});
			return is_changed;
		}

		function updatePriceSection(event) {
			let price = jQuery("#price").val();
			let price_txt = jQuery(".lasso-price");
			let lasso_date = jQuery(".lasso-date");

			if (!price) {
				price = ""; // convert undefined/null value to empty string
			}

			if (jQuery('#show_pricing').is(":checked")) {
				var discount_price_value = show_discount_pricing ? discount_pricing_html : '';
				if (price === "" && amazonIsPrime !== "1") {
					price_txt.remove();
				} else if (price_txt.length > 0) {
					// price_txt.text(price);
					jQuery('.lasso-price .discount-price').html(discount_price_value);
					jQuery('.lasso-price .latest-price').html(price);
					lasso_date.removeClass('d-none');
				} else {
					var priceHtml = "<div class='lasso-price'>";
					if (price !== '') {

						priceHtml += "<div class = 'lasso-price-value'><span class='discount-price'>" + discount_price_value + "</span><span class='latest-price'>" + price + "</span></div>";
					}
					if (amazonIsPrime === '1') {
						priceHtml += "<i class = 'lasso-amazon-prime'></i>";
					}
					priceHtml += "</div>";
					lasso_date.removeClass('d-none');
					add_description_block();
					jQuery(priceHtml).insertBefore(jQuery(".lasso-description").prev());
				}
			} else {
				price_txt.remove();
				lasso_date.addClass('d-none');
			}
		}

		function updateSecondaryButton(event) {
			var secondaryUrl = jQuery("#second_btn_url").val();
			var button2Text = jQuery("#second_btn_text").val();
			var lassoName = jQuery("[name='uri']").val();

			if ('' === button2Text.trim()) {
				button2Text = jQuery("#second_btn_text").attr('placeholder');
			}

			if ('' === secondaryUrl) {
				jQuery(".lasso-button-2").remove();
			} else {
				if (jQuery(".lasso-button-2").length == 0) {
					button2Html = "<a class='lasso-button-2'";
					if ('' !== button2Text) {
						button2Html += " target='" + target2 + "'";
						button2Html += " href='" + secondaryUrl + "'";
					}
					button2Html += rel2;
					button2Html += " data-lasso-box-trackable='true'";
					button2Html += " data-lasso-name='" + lassoName + "'";
					button2Html += " data-lasso-id='' data-lasso-button='2'>";
					button2Html += button2Text;
					button2Html += "</a>";

					if (layoutBoxNumber == '6') {
						jQuery(".lasso-box-4").append(button2Html);
					} else if (layoutBoxNumber == '3' || layoutBoxNumber == '2') {
						jQuery(".lasso-button-1").after(button2Html);
					}
				} else {
					jQuery(".lasso-button-2").text(button2Text);
					jQuery(".lasso-button-2").attr("href", secondaryUrl);
				}
			}
		}

		function updateDisclosureSection(event) {
			var disclosureTxt = jQuery("#disclosure").val();
			var lassoDisclosure = jQuery(".lasso-disclosure");
			var showDisclosure = jQuery('#show_disclosure');

			if ('' === disclosureTxt.trim()) {
				disclosureTxt = jQuery("#disclosure").attr('placeholder');
			}

			lassoDisclosure.text(disclosureTxt);
			if (!showDisclosure.is(":checked")) {
				lassoDisclosure.addClass("d-none");
			} else {
				lassoDisclosure.removeClass("d-none");
			}
		}

		function update_ui(data) {
			let price_input = jQuery('input[name="price"]');
			let permalink_wrapper = jQuery('.permalink-wrapper');
			let thumbnail_wrapper = jQuery('.thumbnail-wrapper');
			let link_cloaking_wrapper = jQuery('.link-cloaking-wrapper');
			let js_permalink = jQuery('.js-permalink');

			if (data.is_amazon_product === true) {
				thumbnail_wrapper.removeClass('d-none');
				permalink_wrapper.addClass('d-none');
				price_input.attr('readonly', true);
				link_cloaking_wrapper.addClass('d-none');
			} else {
				thumbnail_wrapper.addClass('d-none');
				permalink_wrapper.removeClass('d-none');
				price_input.attr('readonly', false);
				link_cloaking_wrapper.removeClass('d-none');
			}

			if (data.is_amazon_page === true) {
				permalink_wrapper.addClass('d-none');
			}

			price_input.val(data.price);
			js_permalink.text(data.public_link);
			js_permalink.attr('href', data.public_link);

			jQuery("#lasso-url-heading").text(data.name);
			jQuery("#affiliate_name").val(data.name);
			jQuery('#thumbnail_image_url').val(data.image_src);
		}

		function add_description_block() {
			if (!jQuery(".lasso-description").length) {
				let lasso_description_html = '<div class="lasso-description"></div>';
				jQuery(lasso_description_html).insertAfter(jQuery("#demo_display_box div.clear"));
			}
		}

		function get_payload_to_save_url() {
			var post_id = '<?php echo $lasso_url->lasso_id; ?>';
			var action = 'lasso_save_lasso_url';
			var affiliate_name = jQuery("#affiliate_name").val();
			var affiliate_url = jQuery("#affiliate_url").val();
			var price = jQuery("#price").val();
			var thumbnail_id = jQuery("#thumbnail_id").val();
			var permalink = jQuery('#permalink').val();
			var disclosure = jQuery("#disclosure").val();
			var apple_btn_url = jQuery("#apple_btn_url").val();
			var google_btn_url = jQuery("#google_btn_url").val();
			var third_btn_url = jQuery("#third_btn_url").val();
			var fourth_btn_url = jQuery("#fourth_btn_url").val();
			var price_app = jQuery("#price_app").val();
			var developer_app = jQuery("#developer_app").val();
			var rating_app = jQuery("#rating_app").val();
			var categories_app = jQuery("#categories_app").val();
			var size_app = jQuery("#size_app").val();
			var version_app = jQuery("#version_app").val();
			var updated_on_app = jQuery("#updated_on_app").val();
			var affiliate_desc = quill.root.innerHTML;
			affiliate_desc = affiliate_desc == '<p><br></p>' ? '' : affiliate_desc;

			var settings = {
				// fields
				post_name: jQuery("[name='uri']").val(),
				affiliate_name: affiliate_name,
				affiliate_url: affiliate_url,
				affiliate_desc: affiliate_desc,
				thumbnail: jQuery("#render_thumbnail").attr('src'),
				permalink: jQuery("#permalink").val(),
				price: price,
				apple_btn_url: apple_btn_url,
				google_btn_url: google_btn_url,
				third_btn_url: third_btn_url,
				fourth_btn_url: fourth_btn_url,
				price_app: price_app,
				developer: developer_app,
				rating: rating_app,
				categories_app: categories_app,
				size: size_app,
				version: version_app,
				updated_on: updated_on_app,

				enable_nofollow: jQuery("#url-en-nofollow").prop("checked") ? 1 : 0,
				open_new_tab: jQuery("#url-open-link").prop("checked") ? 1 : 0,
				enable_nofollow2: jQuery("#url-en-nofollow2").prop("checked") ? 1 : 0,
				open_new_tab2: jQuery("#url-open-link2").prop("checked") ? 1 : 0,

				enable_nofollow3: jQuery("#url-en-nofollow3").prop("checked") ? 1 : 0,
				open_new_tab3: jQuery("#url-open-link3").prop("checked") ? 1 : 0,

				enable_nofollow4: jQuery("#url-en-nofollow4").prop("checked") ? 1 : 0,
				open_new_tab4: jQuery("#url-open-link4").prop("checked") ? 1 : 0,

				enable_nofollow_google: jQuery("#url-en-nofollow-google").prop("checked") ? 1 : 0,
				open_new_tab_google: jQuery("#url-open-link-google").prop("checked") ? 1 : 0,

				enable_nofollow_apple: jQuery("#url-en-nofollow-apple").prop("checked") ? 1 : 0,
				open_new_tab_apple: jQuery("#url-open-link-apple").prop("checked") ? 1 : 0,

				link_cloaking: jQuery("#url-en-link-cloaking").prop("checked") ? 1 : 0,
				is_opportunity: jQuery("#is_opportunity").prop("checked") ? 1 : 0,
				enable_sponsored: jQuery("#enable_sponsored").prop("checked") ? 1 : 0,
				// dropdowns
				categories: jQuery("#basic-categories").val(),
				keywords: jQuery("#basic-keywords").val(),

				theme_name: jQuery("#theme_name").val(),
				disclosure_text: disclosure ? disclosure.trim() : '',
				show_price: jQuery("#show_pricing").prop("checked") ? 1 : 0,
				show_disclosure: jQuery("#show_disclosure").prop("checked") ? 1 : 0,

				badge_text: jQuery("#badge_text").val(),
				buy_btn_text: jQuery("#buy_btn_text").val(),
				second_btn_url: jQuery("#second_btn_url").val(),
				second_btn_text: jQuery("#second_btn_text").val(),

				third_btn_url: jQuery("#third_btn_url").val(),
				third_btn_text: jQuery("#third_btn_text").val(),

				fourth_btn_url: jQuery("#fourth_btn_url").val(),
				fourth_btn_text: jQuery("#fourth_btn_text").val(),

				google_btn_text: jQuery("#google_btn_text").val(),
				apple_btn_text: jQuery("#apple_btn_text").val(),
			};

			if (amazon_product_id != '') {
				settings.amazon_product_id = amazon_product_id;
				settings.amazon_url = '<?php echo $lasso_url->target_url; ?>';
				settings.amazon_price = price;
				settings.amazon_image_url = jQuery("#thumbnail_image_url").val();
				settings.amazon_product_name = affiliate_name;
				settings.amazon_description = affiliate_desc;
			}

			return {
				action: action,
				post_id: lasso_id,
				settings: settings,
				thumbnail_id: thumbnail_id,
				permalink: permalink,
				samplepermalinknonce: jQuery('#samplepermalinknonce').val()
			};
		}

		// save url detail
		jQuery('#btn-save-url').unbind().click(function() {
			change_progress_message();
			lasso_helper.setProgressZero();
			lasso_helper.scrollTop();

			var is_update = '<?php echo $is_update; ?>';
			var amazon_aff = '<?php echo $amazon_aff; ?>';
			var lasso_update_popup = jQuery('#url-save');
			var affiliate_url = jQuery("#affiliate_url").val();
			var permalink = jQuery('#permalink').val();

			var regex = /(?:[/dp/|/gp/product/|/ASIN/]|$)([A-Z0-9]{10})/;
			var m = affiliate_url.match(regex);
			amazon_product_id = m !== null ? m[1] : '';

			var ajax_url = lassoOptionsData.ajax_url;

			jQuery.ajax({
					url: ajax_url,
					type: 'post',
					data: get_payload_to_save_url(),
					beforeSend: function(xhr) {
						jQuery('#is-saving').val('1');

						// collapse current error + success notifications
						jQuery(".alert.red-bg.collapse").collapse('hide');
						jQuery(".alert.green-bg.collapse").collapse('hide');

						// Collapse Current Success Notification
						jQuery(".alert.green-bg.collapse").collapse('hide');

						lasso_update_popup.modal('show');

						lasso_helper.set_progress_bar(98, 20);
					}
				})
				.done(function(res) {
					if (res.success) {
						lasso_segment_tracking('Lasso Link Saved', {
							lasso_id: lasso_id,
							link: permalink
						});
						var interval = setInterval(function() {
							if (!lasso_update_popup.hasClass('show')) {
								clearInterval(interval);
								if (res.data.error != '' && 'post' in res.data) {
									lasso_helper.warningScreen(res.data.error);
								} else if (res.data.error != '') {
									lasso_helper.errorScreen("", "save-fail", "link");
								} else {
									lasso_helper.successScreen("", "save-success", "link");
								}
							}
						}, 10);

						var post = res.data.post;
						var product_image = post.image_src;
						jQuery('#render_thumbnail').attr('src', product_image);

						// Redirect to edit page if add new
						if (is_update == 0) {
							var timeout = res.data.error != '' ? 3000 : 0;
							setTimeout(function() {
								window.location.replace(post.edit_link);
							}, timeout);
						}

						// brief flash Opportunities count
						var oppotunities = jQuery('.js-sub-nav').find('li').eq(2).find('span');
						var opportunity_count = oppotunities.text();
						if ('count' in res.data) {
							oppotunities.text(res.data.count.opportunities);
						}

						// update info
						var image_src = post.image_src;
						var post_name = decodeEntities(post.name);
						jQuery('a.lasso-title').attr('href', post.public_link);
						jQuery('a.lasso-title').text(post_name);
						jQuery('#lasso-url-heading').text(post_name);
						jQuery('#affiliate_name').val(post_name);
						jQuery('#affiliate_url').val(post.target_url);
						jQuery('#permalink').val(post.slug);
						jQuery('a.lasso-image').attr('href', post.public_link);
						jQuery('#render_thumbnail').attr('src', image_src);
						jQuery('#thumbnail_image_url').attr('value', image_src);
						jQuery('#thumbnail_image_url').val(image_src);
						jQuery('.js-permalink').attr('href', post.public_link);
						jQuery('.js-permalink').text(post.public_link);
						jQuery('a.lasso-button-1').attr('href', post.public_link);
						jQuery('a.lasso-button-2').attr('href', post.display.secondary_url);

						jQuery('#price').val(post.price);
						jQuery('#second_btn_url').val(post.display.secondary_url);
						jQuery('#third_btn_url').val(post.display.third_url);
						jQuery('#fourth_btn_url').val(post.display.fourth_url);

						show_discount_pricing = post.amazon.show_discount_pricing;
						discount_pricing_html = post.amazon.discount_pricing_html;
						updatePriceSection();

						if (res.data.warning != '') {
							lasso_helper.warningScreen(res.data.warning);
						}
					} else {
						lasso_helper.errorScreen(res.data);
					}
				})
				.fail(function(xhr, status, error) {
					lasso_helper.errorScreen(error);
				})
				.always(function() {
					lasso_helper.set_progress_bar_complete();
					setTimeout(function() {
						// Hide update popup by setTimeout to make sure this run after lasso_update_popup.modal('show')
						lasso_update_popup.modal('hide');
					}, 1000);

					unsaved = false;
					jQuery('#is-saving').val('0');
					// unset initial_value
					initial_value = get_all_values();
				});
		});

		function decodeEntities(encodedString) {
			var textArea = document.createElement('textarea');
			textArea.innerHTML = encodedString;
			return textArea.value;
		}

		jQuery('#lasso-thumbnail').click(function() {
			set_thumbnail();
		});

		function set_thumbnail() {
			if (lasso_helper.is_empty(wp) || !wp.hasOwnProperty('media') || typeof wp.media !== 'function') {
				console.warn('Lasso cannot load WP media JS');
			}

			var custom_uploader = wp.media({
				title: 'Select an Image',
				multiple: false,
				library: {
					type: 'image'
				},
				button: {
					text: 'Select Image'
				}
				// frame: 'post'
			});

			if (custom_uploader) {
				// When a file is selected, grab the URL
				custom_uploader.on('select', function() {
					var attachment = custom_uploader.state().get('selection').first().toJSON();
					jQuery("#render_thumbnail").attr('src', attachment.url);
					var image_editor = jQuery("#image_editor");
					jQuery(image_editor).find("#render_thumbnail").attr("src", attachment.url);
					jQuery("#thumbnail_id").val(attachment.id);
					jQuery("#thumbnail_image_url").val("");
				});

				custom_uploader.open();
			}
		}

		jQuery('#lasso-delete-url').click(function() {
			deleteUrl();
		});

		function deleteUrl() {
			jQuery.ajax({
					url: lassoOptionsData.ajax_url,
					type: 'post',
					data: {
						action: 'lasso_delete_post',
						post_id: lasso_id
					},
					beforeSend: function(xhr) {
						if (lasso_id == '') {
							xhr.lasso_error = 'Unsaved url can not be deleted.';
							return false;
						}
					}
				})
				.done(function(res) {
					res = res.data;
					if (res.data == 1) {
						lasso_segment_tracking('Delete Lasso Link', {
							lasso_id: lasso_id
						});
						window.location.href = "/wp-admin/edit.php?post_type=lasso-urls&page=dashboard";
					} else {
						lasso_helper.errorScreen(res.error);
					}
				})
				.fail(function(xhr, status, error) {
					if (xhr.lasso_error) {
						error = xhr.lasso_error;
					}
					lasso_helper.errorScreen(error);
				})
				.always(function() {
					jQuery('#url-delete').modal('hide');
				});
		}

		jQuery('#js-field-remove-button').click(function() {
			deleteField(this);
		});

		function deleteField(field) {
			var field_id = jQuery(field).data('field-id');
			jQuery.ajax({
					url: lassoOptionsData.ajax_url,
					type: 'post',
					data: {
						action: 'lasso_remove_field_from_page',
						post_id: lasso_id,
						field_id: field_id
					},
					beforeSend: function(xhr) {
						if (lasso_id == '') {
							xhr.lasso_error = 'Unsaved url can not be deleted.';
							return false;
						}
						jQuery(field).html(get_loading_image_small_red());
						jQuery('#demo_display_box').addClass('d-none');
						jQuery('.image_loading').removeClass('d-none');
					}
				})
				.done(function(res) {
					res = res.data;
					jQuery('#field-delete').modal('hide');
					jQuery(field).html("Remove");
					jQuery(".url-details-field-box[data-field-id='" + field_id + "']").remove();
					refresh_display();
				})
				.fail(function(xhr, status, error) {
					if (xhr.lasso_error) {
						error = xhr.lasso_error;
					}
					lasso_helper.errorScreen(error);
				})
				.always(function() {
					jQuery('#url-delete').modal('hide');
				});
		}

		// Delete Lasso URL
		jQuery('#btn-confirm-delete').unbind().click(function() {
			// Get newest location count
			jQuery.ajax({
					url: lassoOptionsData.ajax_url,
					type: 'post',
					data: {
						action: 'get_lasso_url_location_count',
						lasso_post_id: lasso_id
					},
				})
				.done(function(res) {
					res = res.data;

					if (res.status == 1) {
						if (parseInt(res.location_count) > 0) {
							jQuery('#url-delete-reject').modal('show');
						} else {
							jQuery('#url-delete').modal('show');
						}
					} else {
						lasso_helper.errorScreen(res.error);
					}
				});
		});

		jQuery('#permalink').unbind().keyup(function() {
			var el = jQuery(this);
			var permalink = jQuery('.js-permalink');
			var post_name = el.val().trim().replace(/[\W_]+/g, "-");
			var site_url = '<?php echo site_url(); ?>';

			el.val(post_name);
			permalink.text(site_url + '/' + post_name + '/');
			// permalink.attr('href', site_url + '/' + post_name + '/'); // Commented out so the link works even before a save.
		});

		// COPY SHORTCODE
		jQuery('#copy-shortcode').click(function() {
			copy_shortcode();
		});

		function copy_shortcode() {
			// ANIMATE CLICK
			jQuery('#copy-shortcode').addClass('animate-bounce-in').delay(500).queue(function() {
				jQuery(this).removeClass('animate-bounce-in').dequeue();
			});

			jQuery('#copy-shortcode').attr('data-tooltip', 'Copied!');

			var copyText = document.getElementById("shortcode");

			copyText.select();
			copyText.setSelectionRange(0, 99999); /*For mobile devices*/

			document.execCommand("copy");
		}

		// Show warning message
		let is_duplicate_url = lasso_helper.get_url_parameter('is_duplicate');
		if (is_duplicate_url === 'true') {
			lasso_helper.warningScreen(`This product already exists. Please update the Primary URL or add a new link.`);
		}

		/*
		// RATING SYSTEM
		jQuery(".rating input:radio").attr("checked", false);
		jQuery('.rating input').click(function() {
			jQuery(".rating span").removeClass('checked');
			jQuery(this).parent().addClass('checked');
		});
		jQuery('input:radio').change(function() {
			var userRating = this.value;
			alert(userRating);
		}); 
		*/

		<?php
		// ? Prompt to set your Tracking ID.
		if ('' === $lasso_options['amazon_tracking_id'] && LASSO_AMAZON_PRODUCT_TYPE === $link_type) {
			$notification_url   = $new_url ? $new_url : $lasso_url->target_url;
			$template_variables = array('notification_url' => $notification_url);
		?>
			var html = `<?php echo Lasso_Helper::include_with_variables(LASSO_PLUGIN_PATH . '/admin/views/notifications/amazon-url-detected.php', $template_variables); ?>`;
			jQuery("#lasso_notifications").append(html);

			jQuery('#btn-tracking-id-save').unbind().click(function() {
				var amazon_tracking_id = jQuery('#btn-tracking-id-save').data('tracking-id');
				jQuery.ajax({
						url: lassoOptionsData.ajax_url,
						type: 'post',
						data: {
							action: 'lasso_save_amazon_tracking_id',
							amazon_tracking_id: amazon_tracking_id
						},
						beforeSend: function(xhr) {
							jQuery('#btn-tracking-id-save').html(get_loading_image_small());
						}
					})
					.done(function(res) {
						jQuery('#btn-tracking-id-save').html("Yes");
						jQuery('#amazon-url-detected').collapse('hide');
						lasso_helper.successScreen("Tracking ID Saved!");
					})
					.fail(function(xhr, status, error) {
						lasso_helper.errorScreen("Failed to save Tracking ID");
					});
			});
		<?php
		}
		?>
	});
</script>

<!-- CUSTOM FIELDS MODAL -->
<?php include(LASSO_PLUGIN_PATH . '/admin/views/modals/field-create.php'); ?>
<?php include(LASSO_PLUGIN_PATH . '/admin/views/modals/url-field-delete.php'); ?>

<!-- URL SAVE & DELETE MODALs -->
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/url-save.php'; ?>
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/url-delete.php'; ?>
<?php require LASSO_PLUGIN_PATH . '/admin/views/modals/url-delete-reject.php'; ?>

<?php Lasso_Config::get_footer(); ?>