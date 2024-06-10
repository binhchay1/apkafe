<?php
/**
 * Schema Pro Admin Init
 *
 * @package Schema Pro
 */

$blocks      = BSF_SP_Admin_Helper::get_block_options();
$setting_url = self::get_page_url( 'wpsp-advanced-settings' );
$settings    = BSF_AIOSRS_Pro_Helper::$settings['aiosrs-pro-settings'];
	// Get list of current General entries.
	$entries = self::get_admin_menu_positions();

	$select_box = '<select name="aiosrs-pro-settings[menu-position]" >' . "\n";
foreach ( $entries as $entry_page => $entry ) {
	$select_box .= '<option ' . selected( $entry_page, $settings['menu-position'], false ) . ' value="' . $entry_page . '">' . $entry . "</option>\n";
}
	$select_box .= "</select>\n";

$wpsp_advanced_settings = self::get_page_url( 'wpsp-advanced-settings' );
if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_REQUEST['wp_schema_pro_admin_page_nonce'] ), 'wp_schema_pro_admin_page' ) ) {
	return;
}
$current_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'general';
?>
<div class="wrap bsf-aiosrs-pro clear">
	<div id="poststuff">
		<div id="post-body" class="columns-2">
			<div id="post-body-content">
				<!-- Plugin Settings -->
				<div class="postbox wp-schema-pro-advanced-settings" >
					<h2 class="hndle">
						<span><?php esc_html_e( 'Plugin Settings', 'wp-schema-pro' ); ?></span>
					</h2>
					<div class="inside">
						<?php
						$brand_adv = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
						?>
						<p><?php esc_html_e( 'Configure your Schema Pro plugin’s advanced options.', 'wp-schema-pro' ); ?></p>
						<form method="post" action="options.php">
							<?php settings_fields( 'aiosrs-pro-settings-group' ); ?>
							<?php do_settings_sections( 'aiosrs-pro-settings-group' ); ?>
							<table class="form-table">
								<tr>
									<th scope="row">
										<?php esc_html_e( 'Enable “Test Schema” Link in Toolbar', 'wp-schema-pro' ); ?>
										<?php
										if ( ( '1' === $brand_adv['sp_hide_label'] ) || true === ( defined( 'WP_SP_WL' ) && WP_SP_WL ) ) {
											$message = __( 'This allows you to test schema on page/post directly from the toolbar with a click. Enable the option to display the “Test Schema” link in the toolbar.', 'wp-schema-pro' );
										} else {
												$message  = __( 'This allows you to test schema on page/post directly from the toolbar with a click. Enable the option to display the “Test Schema” link in the toolbar.', 'wp-schema-pro' );
												$message .= ' <a href="https://wpschema.com/docs/how-to-test-schema-snippet/" target="_blank" rel="noopener">' . __( 'Learn more.', 'wp-schema-pro' ) . '</a>';
										}

											self::get_tooltip( $message );
										?>
									</th>
									<td>
										<select id="aiosrs-pro-settings-quick-test" name="aiosrs-pro-settings[quick-test]" >
											<option <?php selected( 1, $settings['quick-test'] ); ?> value="1"><?php esc_html_e( 'Yes', 'wp-schema-pro' ); ?></option>
											<option <?php selected( 'disabled', $settings['quick-test'] ); ?> value="disabled"><?php esc_html_e( 'No', 'wp-schema-pro' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<?php
										if ( '' !== $brand_adv['sp_plugin_name'] ) {
											/* translators: %s: search term */
											$brand_name = sprintf( __( 'Display %s Menu Under', 'wp-schema-pro' ), $brand_adv['sp_plugin_name'] );
											?>
											<?php
											echo esc_html( $brand_name );
										} else {
											esc_html_e( 'Display Schema Pro Menu Under', 'wp-schema-pro' );
										}
										?>
										<?php
										if ( ( '1' === $brand_adv['sp_hide_label'] ) || true === ( defined( 'WP_SP_WL' ) && WP_SP_WL ) ) {
											$message = __( 'Decide where you wish to see the Schema Pro menu on your WordPress dashboard.', 'wp-schema-pro' );
										} else {
											$message  = __( 'Decide where you wish to see the Schema Pro menu on your WordPress dashboard.', 'wp-schema-pro' );
											$message .= ' <a href="https://wpschema.com/docs/advanced-settings-schema-pro/?utm_source=wp-dashboard&utm_medium=schema-pro-tooltips#admin-menu" target="_blank" rel="noopener">' . __( 'Learn more.', 'wp-schema-pro' ) . '</a>';
										}
											self::get_tooltip( $message );
										?>
									</th>
									<td><?php echo $select_box; // PHPCS:ignore: WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
								</tr>
								<tr>
									<th scope="row">
										<?php esc_html_e( 'Add Schema Code In', 'wp-schema-pro' ); ?>
										<?php
										if ( ( '1' === $brand_adv['sp_hide_label'] ) || true === ( defined( 'WP_SP_WL' ) && WP_SP_WL ) ) {
											$message = __( 'Select where you wish to add the schema code.', 'wp-schema-pro' );
										} else {
											$message  = __( 'Select where you wish to add the schema code.', 'wp-schema-pro' );
											$message .= ' <a href="https://wpschema.com/docs/advanced-settings-schema-pro/?utm_source=wp-dashboard&utm_medium=schema-pro-tooltips#schema-location" target="_blank" rel="noopener">' . __( 'Learn more.', 'wp-schema-pro' ) . '</a>';
										}
											self::get_tooltip( $message );
										?>
									</th>
									<td>
										<select id="aiosrs-pro-settings-schema-location" name="aiosrs-pro-settings[schema-location]" >
											<option <?php selected( 'head', $settings['schema-location'] ); ?> value="head"><?php esc_html_e( 'Head', 'wp-schema-pro' ); ?></option>
											<option <?php selected( 'footer', $settings['schema-location'] ); ?> value="footer"><?php esc_html_e( 'Footer', 'wp-schema-pro' ); ?></option>
										</select>
									</td>
								</tr>
								<tr>
								<th scope="row">
										<?php esc_html_e( 'Add Default Image', 'wp-schema-pro' ); ?>
										<?php
											$message = __( 'Specify a default image to be a fallback for missing Featured Images.', 'wp-schema-pro' );
											self::get_tooltip( $message );
										?>
									</th>
									<td>
										<div class="custom-field-wrapper site-logo-custom-wrap">
											<input type="hidden" class="single-image-field" name="aiosrs-pro-settings[default_image]" value= "<?php echo esc_attr( $settings['default_image'] ); ?>" />
											<?php
											if ( ! empty( $settings['default_image'] ) ) {
												$image_url = wp_get_attachment_url( $settings['default_image'] );
											}
											?>
											<div class="image-field-wrap <?php echo ( ! empty( $image_url ) ) ? 'bsf-custom-image-selected' : ''; ?>"">
												<a href="#" class="aiosrs-image-select button"><span class="dashicons dashicons-format-image"></span><?php esc_html_e( 'Select Image', 'wp-schema-pro' ); ?></a>
												<a href="#" class="aiosrs-image-remove dashicons dashicons-no-alt wp-ui-text-highlight"></a>
												<?php if ( isset( $image_url ) && ! empty( $image_url ) ) : ?>
													<a href="#" class="aiosrs-image-select img"><img src="<?php echo esc_url( $image_url ); ?>" /></a>
												<?php endif; ?>
											</div>
										</div>
									</td>
								</tr>
								<tr>

						<th class="tooltip-with-image-wrapper">
							<?php esc_html_e( 'Skip Rendering Invalid Schema', 'wp-schema-pro' ); ?>
							<?php
							if ( ( '1' === $brand_adv['sp_hide_label'] ) || true === ( defined( 'WP_SP_WL' ) && WP_SP_WL ) ) {
								$message = __( 'If schema on posts/pages have empty required(*) fields, it may render invalid schema. Enable this option to skip rendering these ‘invalid’ schema.', 'wp-schema-pro' );
							} else {
								$message  = __( 'If schema on posts/pages have empty required(*) fields, it may render invalid schema. Enable this option to skip rendering these ‘invalid’ schema.', 'wp-schema-pro' );
								$message .= ' <a href="https://wpschema.com/docs/skip-rendering-invalid-schema/" target="_blank" rel="noopener">' . __( 'Learn more.', 'wp-schema-pro' ) . '</a>';
							}
							self::get_tooltip( $message );
							?>
						</th>
						<td>
							<label>
								<input type="hidden" name="aiosrs-pro-settings[schema-validation]" value="disabled" />
								<input type="checkbox" name="aiosrs-pro-settings[schema-validation]" <?php checked( '1', $settings['schema-validation'] ); ?> value="1" /> <?php esc_html_e( 'Yes', 'wp-schema-pro' ); ?>
							</label>
						</td>
					</tr>
								<?php
								$original = get_current_blog_id();
								if ( 1 === $original ) {
									?>
										<tr>
											<th class="tooltip-with-image-wrapper">
												<?php esc_html_e( 'Delete Data on Uninstall?', 'wp-schema-pro' ); ?>
												<?php
												if ( ( '1' === $brand_adv['sp_hide_label'] ) || true === ( defined( 'WP_SP_WL' ) && WP_SP_WL ) ) {
													$message = __( 'Check this box if you would like Schema to completely remove all of its data when uninstalling via Plugins > Deactivate > Delete.', 'wp-schema-pro' );
												} else {
													$message  = __( 'Check this box if you would like Schema to completely remove all of its data when uninstalling via Plugins > Deactivate > Delete.', 'wp-schema-pro' );
													$message .= ' <a href="https://wpschema.com/docs/delete-schema-data/" target="_blank" rel="noopener">' . __( 'Learn more.', 'wp-schema-pro' ) . '</a>';
												}
													BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
												?>
											</th>
											<td>
												<label>
													<input type="hidden" name="aiosrs-pro-settings[delete-schema-data]" value="disabled" />
													<input type="checkbox" name="aiosrs-pro-settings[delete-schema-data]" <?php checked( '1', $settings['delete-schema-data'] ); ?> value="1" /> <?php esc_html_e( 'Yes', 'wp-schema-pro' ); ?>
												</label>
											</td>
										</tr><?php } ?>
								<?php if ( WP_Schema_Pro_Yoast_Compatibility::$activated ) { ?>
									<tr class="wp-schema-pro-yoast-compatibilty-wrap">
										<th>
											<?php esc_html_e( 'Disable Duplicate Features that Yoast SEO Offers?', 'wp-schema-pro' ); ?>
											<?php
												$message  = __( 'When disabled, Schema Pro does not output duplicate markup that Yoast SEO Offers.', 'wp-schema-pro' );
												$message .= '<br/><br/>' . __( 'These are the features that will be disabled:', 'wp-schema-pro' ) . '<br/>';
												$message .= '<ol>';
												$message .= '<li>' . __( 'Organization/Person', 'wp-schema-pro' ) . '</li>';
												$message .= '<li>' . __( 'Social Profiles', 'wp-schema-pro' ) . '</li>';
												$message .= '<li>' . __( 'Breadcrumb', 'wp-schema-pro' ) . '</li>';
												$message .= '<li>' . __( 'Sitelink Search Box', 'wp-schema-pro' ) . '</li>';
												$message .= '</ol>';
												self::get_tooltip( $message );
											?>
										</th>
										<td>
											<label>
												<input type="hidden" name="aiosrs-pro-settings[yoast-compatibility]" value="disabled" />
												<input type="checkbox" name="aiosrs-pro-settings[yoast-compatibility]" id="aiosrs-pro-settings-yoast-compatibility" <?php checked( '1', $settings ['yoast-compatibility'] ); ?> value="1" /> <?php esc_html_e( 'Yes', 'wp-schema-pro' ); ?>
											</label>
										</td>
									</tr>
								<?php } else { ?>
									<input type="hidden" name="aiosrs-pro-settings[yoast-compatibility]" value="<?php echo esc_attr( $settings ['yoast-compatibility'] ); ?>" />
								<?php } ?>
								<tr>
									<th colspan="2">
										<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wp-schema-pro' ); ?>" />
									</th>
								</tr>
							</table>
						</form>
					</div>
				</div>
			</div>
			<div class="postbox-container" id="postbox-container-1">
				<?php
				if ( is_multisite() ) {
					$settings = get_site_option( 'wp-schema-pro-branding-settings' );
				} else {
					$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
				}
				$sp_hide_label = isset( $settings['sp_hide_label'] ) ? $settings['sp_hide_label'] : 'disabled';
				?>
				<div id="side-sortables" style="min-height: 0px;">
					<div class="postbox">
						<h2 class="hndle"><span><?php esc_html_e( 'Setup Wizard', 'wp-schema-pro' ); ?></span></h2>
						<div class="inside">
							<div>
								<?php
								$sp_name = isset( $settings['sp_plugin_name'] ) ? $settings['sp_plugin_name'] : '';
								if ( '' !== $sp_name ) {
									/* translators: %s: search term */
									$brand_name = sprintf( __( 'Need help configure %s step by step?', 'wp-schema-pro' ), $sp_name );
									?>
										<p><?php echo esc_html( $brand_name ); ?></p>
													<?php
								} else {
									?>
								<p>
									<?php
									esc_html_e( 'Not sure where to start? Check out our video on ', 'wp-schema-pro' );
									echo sprintf(
										wp_kses_post( '<a href="https://www.youtube.com/watch?v=xOiMA0am9QY" target="_blank">Initial Setup Wizard first.</a>', 'wp-schema-pro' )
									);
									?>
								</p><?php } ?>
								<a href="<?php echo esc_url( admin_url( 'index.php?page=aiosrs-pro-setup-wizard' ) ); ?>" class="button button-large button-primary"><?php esc_html_e( 'Start Setup Wizard', 'wp-schema-pro' ); ?></a>
							</div>
						</div>
					</div>
				</div>
				<div id="side-sortables" style="min-height: 0px;">
					<div class="postbox">
						<h2 class="hndle"><span><?php esc_html_e( 'Regenerate Schema', 'wp-schema-pro' ); ?></span></h2>
						<div class="inside">
							<p>
							<?php
							if ( ( '1' === $sp_hide_label ) || true === ( defined( 'WP_SP_WL' ) && WP_SP_WL ) ) {
								esc_html_e( 'Having issues with your schema? Try regenerating the code on all your posts/pages. ', 'wp-schema-pro' );
							} else {
								esc_html_e( 'Having issues with your schema? Try regenerating the code on all your posts/pages. ', 'wp-schema-pro' );
								echo sprintf(
									wp_kses_post( '<a href="https://wpschema.com/docs/regenerate-schema/" target="_blank">Learn More</a>', 'wp-schema-pro' )
								);
							}
							?>
							</p>
							<div id="wpsp-regenerate-notice" class="notice inline notice-success" style="display: none">
								<p> <?php esc_html_e( 'Schema Regenerated Successfully.', 'wp-schema-pro' ); ?> </p>
							</div>
							<div style="display: inline-block">
								<input
									type="button"
									id="wpsp-regenerate-schema"
									data-nonce="<?php echo esc_attr( wp_create_nonce( 'regenerate_schema' ) ); ?>"
									class="button button-primary"
									value="<?php esc_attr_e( 'Regenerate Now', 'wp-schema-pro' ); ?> ">
								<span class="spinner" ></span>
							</div>
						</div>
					</div>
				</div>
				<?php if ( 'disabled' === $sp_hide_label ) { ?>
					<div id="side-sortables" style="min-height: 0px;">
						<div class="postbox">
							<h2 class="hndle"><span><?php esc_html_e( 'Knowledge Base', 'wp-schema-pro' ); ?></span></h2>
							<div class="inside">
								<p>
								<?php
								esc_html_e( 'Not sure how something works? Take a peek at the knowledge base and learn.', 'wp-schema-pro' );
								?>
								</p>
								<a href="https://wpschema.com/docs/" target="_blank" class="button button-large button-primary"><?php esc_html_e( 'Visit Knowledge Base', 'wp-schema-pro' ); ?></a>
							</div>
						</div>
					</div>
				<?php } ?>
				<?php
				if ( bsf_display_rollback_version_form( 'wp-schema-pro' ) ) {
					?>
					<div id="side-sortables" style="">
						<div class="postbox">
						<h2 class="hndle"><span><?php esc_html_e( 'Rollback Version', 'wp-schema-pro' ); ?></span></h2>
							<div class="inside">
						<?php
							$product_id = 'wp-schema-pro';
							bsf_get_version_rollback_form( $product_id );
						?>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
