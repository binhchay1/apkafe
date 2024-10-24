<?php
	$initial_settings = array(
		'enable_ecommerce_events' => '',
		'view_item_event'         => '',
		'view_item_list_event'    => '',
		'add_to_cart_event'       => '',
		'begin_checkout_event'    => '',
		'purchase_event'          => '',
		'vimeo_video_event'       => '',
		'self_hosted_video_event' => '',
		'self_hosted_audio_event' => '',
	);

	// User saved data.
	if ( ! empty( get_option( 'ht_easy_ga4_options' ) ) ) {
		$settings = wp_parse_args( get_option( 'ht_easy_ga4_options' ), $initial_settings );
	} else {
		$settings = $initial_settings;
	}

	$is_pro_plugin_actice = $this->is_pro_plugin_active();

	$pro_status_class = '';
	if ( ! $is_pro_plugin_actice ) {
		$pro_status_class = 'htga4_no_pro';
	}
	?>

<form method="post" class="htga4" action="options.php">
	<?php settings_fields( 'ht-easy-ga4-settings-option' ); ?>

	<?php do_action( 'htga4_events_tracking_tab_content_before', $this ); ?>

	<div class="htga4 htga4-events-tracking-tab-content-area">
		<div class="htga4-tab-content-left <?php echo esc_attr( $pro_status_class ); ?>">

			<h2 class="htga4-section-heading"><?php echo esc_html__( 'E-Commerce Events', 'ht-easy-ga4' ); ?></h2>
			<div class="htga4-enable-ecommerce-events">
				<span><?php echo esc_html__( 'Enable E-commerce Events', 'ht-easy-ga4' ); ?></span>
				<div class="htga4-checkbox-switch">
					<input name="enable_ecommerce_events" type="hidden" id="" value="0" />
					<input name="enable_ecommerce_events" type="checkbox" id="htga4_enable_ecommerce_events" <?php checked( 'on', $settings['enable_ecommerce_events'] ); ?> />
					<label for="enable_ecommerce_events">
						<span class="htga4-checkbox-switch-label on"><?php echo esc_html__( 'on', 'ht-easy-ga4' ); ?></span>
						<span class="htga4-checkbox-switch-label off"><?php echo esc_html__( 'off', 'ht-easy-ga4' ); ?></span>
						<span class="htga4-checkbox-switch-indicator"></span>
					</label>
				</div>
			</div>

			<div class="htga4-grid-box-wrapper">

				<div class="htga4-grid-box">
					<div class="htga4-grid-box-left">
						<div class="htga4-grid-box-label">
							<?php echo esc_html__( 'View Product', 'ht-easy-ga4' ); ?>
						</div>
						<span class="htga4-show-info">
							<i class="dashicons dashicons-editor-help"></i>
							<span class="htga4-show-info-content">
							<?php
							printf(
								/* translators: %s: View Item event */
								__( 'Fire the <b>%s</b> event when a visitor views a content (e.g. when a visitor visits a product details page).', 'ht-easy-ga4' ), // phpcs:ignore
								esc_html__( 'View Item', 'ht-easy-ga4' )
							);
							?>
							</span>
						</span>
					</div>

					<div class="htga4-grid-box-right">
						<a href="https://htga4.hasthemes.com/docs/how-to-configure-the-plugin/" target="_blank">
							<span class="htga4-show-info">
								<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>
								<span class="htga4-show-info-content"><?php echo esc_html__( 'Documentation', 'ht-easy-ga4' ); ?></span>
							</span>
						</a>

						<div class="htga4-checkbox-switch">
							<?php if ( $settings['enable_ecommerce_events'] && $is_pro_plugin_actice ) : ?>
							<input name="view_item_event" type="hidden" id="" value="0" />
							<input name="view_item_event" type="checkbox" id="view_item_event" <?php checked( 'on', $settings['view_item_event'] ); ?> />							<?php endif; ?>

							<label for="view_item_event">
								<span class="htga4-checkbox-switch-label on"><?php echo esc_html__( 'on', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-label off"><?php echo esc_html__( 'off', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-indicator"></span>
							</label>
						</div>
					</div>
				</div><!-- .htga4-grid-box -->

				<div class="htga4-grid-box">
					<div class="htga4-grid-box-left">
						<div class="htga4-grid-box-label">
							<?php echo esc_html__( 'View Category', 'ht-easy-ga4' ); ?>
						</div>
						<span class="htga4-show-info">
							<i class="dashicons dashicons-editor-help"></i>
							<span class="htga4-show-info-content">
								<?php
								printf(
									/* translators: %s: View Item List event */
									__( 'Fire the <b>%s</b> event when a visitor views a category or archive page.', 'ht-easy-ga4' ), // phpcs:ignore
									esc_html__( 'View Item List', 'ht-easy-ga4' )
								);
								?>
							</span>
						</span>
					</div>

					<div class="htga4-grid-box-right">
						<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>

						<div class="htga4-checkbox-switch">
							<?php if ( $settings['enable_ecommerce_events'] && $is_pro_plugin_actice ) : ?>
							<input name="view_item_list_event" type="hidden" id="" value="0" />
							<input name="view_item_list_event" type="checkbox" id="htga4_view_item_list_event" <?php checked( 'on', $settings['view_item_list_event'] ); ?> />
							<?php endif ?>
							<label for="view_item_list_event">
								<span class="htga4-checkbox-switch-label on"><?php echo esc_html__( 'on', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-label off"><?php echo esc_html__( 'off', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-indicator"></span>
							</label>
						</div>
					</div>
				</div><!-- .htga4-grid-box -->

				<div class="htga4-grid-box">
					<div class="htga4-grid-box-left">
						<div class="htga4-grid-box-label">
							<?php echo esc_html__( 'Add to Cart', 'ht-easy-ga4' ); ?>
						</div>
						<span class="htga4-show-info">
							<i class="dashicons dashicons-editor-help"></i>
							<span class="htga4-show-info-content">
								<?php
								printf(
									/* translators: %s: Add to Cart event */
									__( 'Fire the <b>%s</b> event when a visitor adds a product to their cart.', 'ht-easy-ga4' ), // phpcs:ignore
									esc_html__( 'Add To Cart', 'ht-easy-ga4' )
								);
								?>
							</span>
						</span>
					</div>
					<div class="htga4-grid-box-right">
						<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>

						<div class="htga4-checkbox-switch">
							<?php if ( $settings['enable_ecommerce_events'] && $is_pro_plugin_actice ) : ?>
							<input name="add_to_cart_event" type="hidden" id="" value="0" />
							<input name="add_to_cart_event" type="checkbox" id="htga4_add_to_cart_event" <?php checked( 'on', $settings['add_to_cart_event'] ); ?> />
							<?php endif; ?>
							<label for="add_to_cart_event">
								<span class="htga4-checkbox-switch-label on"><?php echo esc_html__( 'on', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-label off"><?php echo esc_html__( 'off', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-indicator"></span>
							</label>
						</div>
					</div>
				</div><!-- .htga4-grid-box -->

				<div class="htga4-grid-box">
					<div class="htga4-grid-box-left">
						<div class="htga4-grid-box-label">
							<?php echo esc_html__( 'Initiate Checkout', 'ht-easy-ga4' ); ?>
						</div>
						<span class="htga4-show-info">
							<i class="dashicons dashicons-editor-help"></i>
							<span class="htga4-show-info-content">
							<?php
								printf(
									/* translators: %s: Initiate Checkout event */
									__( 'Fire the <b>%s</b> event when a user starts checkout.', 'ht-easy-ga4' ), // phpcs:ignore
									esc_html__( 'Initiate Checkout', 'ht-easy-ga4' )
								);
								?>
							</span>
						</span>
					</div>

					<div class="htga4-grid-box-right">
						<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>

						<div class="htga4-checkbox-switch">
							<?php if ( $settings['enable_ecommerce_events'] && $is_pro_plugin_actice ) : ?>
							<input name="begin_checkout_event" type="hidden" id="" value="0" />
							<input name="begin_checkout_event" type="checkbox" id="htga4_begin_checkout_event" <?php checked( 'on', $settings['begin_checkout_event'] ); ?> />
							<?php endif; ?>
							<label for="begin_checkout_event">
								<span class="htga4-checkbox-switch-label on"><?php echo esc_html__( 'on', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-label off"><?php echo esc_html__( 'off', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-indicator"></span>
							</label>
						</div>
					</div>
				</div><!-- .htga4-grid-box -->

				<div class="htga4-grid-box">
					<div class="htga4-grid-box-left">
						<div class="htga4-grid-box-label">
							<?php echo esc_html__( 'Purchase', 'ht-easy-ga4' ); ?>
						</div>
						<span class="htga4-show-info">
							<i class="dashicons dashicons-editor-help"></i>
							<span class="htga4-show-info-content"><span>
								<?php
								printf(
									/* translators: %s: Purchase event */
									__( 'Fire the <b>%s</b> event on the thank you page after checkout. Fires once per order.', 'ht-easy-ga4' ), // phpcs:ignore
									esc_html__( 'Purchase', 'ht-easy-ga4' )
								);
								?>
							</span>
						</span>
					</div>

					<div class="htga4-grid-box-right">
						<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>

						<div class="htga4-checkbox-switch">
							<?php if ( $settings['enable_ecommerce_events'] && $is_pro_plugin_actice ) : ?>
							<input name="purchase_event" type="hidden" id="" value="0" />
							<input name="purchase_event" type="checkbox" id="htga4_purchase_event" <?php checked( 'on', $settings['purchase_event'] ); ?> />		<?php endif; ?>
							<label for="purchase_event">
								<span class="htga4-checkbox-switch-label on"><?php echo esc_html__( 'on', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-label off"><?php echo esc_html__( 'off', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-indicator"></span>
							</label>
						</div>
					</div>
				</div><!-- .htga4-grid-box -->	
			</div>

			<h2 class="htga4-section-heading"><?php echo esc_html__( 'Video Events', 'ht-easy-ga4' ); ?></h2>
			<div class="htga4-grid-box-wrapper">

				<div class="htga4-grid-box">
					<div class="htga4-grid-box-left">
						<div class="htga4-grid-box-label">
							<?php echo esc_html__( 'Track Vimeo Videos', 'ht-easy-ga4' ); ?>
						</div>
						<span class="htga4-show-info">
							<i class="dashicons dashicons-editor-help"></i>
							<span class="htga4-show-info-content">
								<?php
								printf(
									/* translators: %s: Vimeo Video event */
									__( 'Track engagement for Vimeo videos. This helps you gain insights into how your audience interacts with your %s.', 'ht-easy-ga4' ), // phpcs:ignore
									esc_html__( 'Vimeo Videos', 'ht-easy-ga4' )
								);
								?>
							</span>
						</span>
					</div>

					<div class="htga4-grid-box-right">
						<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>

						<div class="htga4-checkbox-switch">
							<?php if ( $is_pro_plugin_actice ) : ?>
							<input name="vimeo_video_event" type="hidden" id="" value="0" />
							<input name="vimeo_video_event" type="checkbox" id="htga4_vimeo_video_event" <?php checked( 'on', $settings['vimeo_video_event'] ); ?> />
							<?php endif; ?>
							<label for="vimeo_video_event">
								<span class="htga4-checkbox-switch-label on"><?php echo esc_html__( 'on', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-label off"><?php echo esc_html__( 'off', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-indicator"></span>
							</label>
						</div>
					</div>
				</div><!-- .htga4-grid-box -->

				<div class="htga4-grid-box">
					<div class="htga4-grid-box-left">
						<div class="htga4-grid-box-label">
							<?php echo esc_html__( 'Track Self Hosted Videos', 'ht-easy-ga4' ); ?>
						</div>
						<span class="htga4-show-info">
							<i class="dashicons dashicons-editor-help"></i>
							<span class="htga4-show-info-content">
								<?php
								printf(
									/* translators: %s: Self Hosted Video event */
									__( 'Track engagement for Self Hosted videos. This helps you gain insights into how your audience interacts with your %s.', 'ht-easy-ga4' ), // phpcs:ignore
									esc_html__( 'Self Hosted Videos', 'ht-easy-ga4' )
								);
								?>
							</span>
						</span>
					</div>
					<div class="htga4-grid-box-right">
						<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>

						<div class="htga4-checkbox-switch">
							<?php if ( $is_pro_plugin_actice ) : ?>
							<input name="self_hosted_video_event" type="hidden" id="" value="0" />
							<input name="self_hosted_video_event" type="checkbox" id="htga4_self_hosted_video_event" <?php checked( 'on', $settings['self_hosted_video_event'] ); ?> />
							<?php endif; ?>
							<label for="self_hosted_video_event">
								<span class="htga4-checkbox-switch-label on"><?php echo esc_html__( 'on', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-label off"><?php echo esc_html__( 'off', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-indicator"></span>
							</label>
						</div>
					</div>
				</div><!-- .htga4-grid-box -->	
			</div>

			<h2 class="htga4-section-heading"><?php echo esc_html__( 'Audio Event', 'ht-easy-ga4' ); ?></h2>
			<div class="htga4-grid-box-wrapper">

				<div class="htga4-grid-box">
					<div class="htga4-grid-box-left">
						<div class="htga4-grid-box-label">
							<?php echo esc_html__( 'Track Self Hosted Audios', 'ht-easy-ga4' ); ?>
						</div>
						<span class="htga4-show-info">
							<i class="dashicons dashicons-editor-help"></i>
							<span class="htga4-show-info-content">
								<?php
								printf(
									/* translators: %s: Self Hosted Audio event */
									__( 'Track engagement for Self Hosted Audios. This helps you gain insights into how your audience interacts with your %s.', 'ht-easy-ga4' ), // phpcs:ignore
									esc_html__( 'Self Hosted Audios', 'ht-easy-ga4' )
								);
								?>
							</span>
						</span>
					</div>

					<div class="htga4-grid-box-right">
						<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>

						<div class="htga4-checkbox-switch">
							<?php if ( $is_pro_plugin_actice ) : ?>
							<input name="self_hosted_audio_event" type="hidden" id="" value="0" />
							<input name="self_hosted_audio_event" type="checkbox" id="htga4_self_hosted_audio_event" <?php checked( 'on', $settings['self_hosted_audio_event'] ); ?> />
							<?php endif; ?>	
							<label for="self_hosted_audio_event">
								<span class="htga4-checkbox-switch-label on"><?php echo esc_html__( 'on', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-label off"><?php echo esc_html__( 'off', 'ht-easy-ga4' ); ?></span>
								<span class="htga4-checkbox-switch-indicator"></span>
							</label>
						</div>
					</div>
				</div><!-- .htga4-grid-box -->	
			</div>

		</div>
		<div class="htga4-tab-content-right"></div>
	</div>
	<?php do_action( 'htga4_events_tracking_tab_content_after', $this ); ?>

	<?php
	if ( $this->is_pro_plugin_active() ) :
		?>
		<button type="submit" id="submit" class="button button-primary"><?php echo esc_html__( 'Save Changes', 'ht-easy-ga4' ); ?></button>
	<?php endif; ?>
</form>
