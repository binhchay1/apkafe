<?php
/**
 * Schema Pro Admin Init
 *
 * @package Schema Pro
 */

$setting_url = self::get_page_url( 'breadcrumb-settings' );
?>

<div class="wrap bsf-aiosrs-pro clear">
	<div id="poststuff">
		<div id="post-body" class="columns-2">
			<div id="post-body-content">
			<?php
				$settings       = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-breadcrumb-setting'];
				$bread_settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-global-schemas'];
			?>
			<!-- General Settings -->
				<div class="postbox wp-schema-pro-breadcrumb-setting" >
					<h2 class="hndle">
						<span><?php esc_html_e( 'Configure Breadcrumbs', 'wp-schema-pro' ); ?></span>
					</h2>
					<div class="inside">
						<p>
							<?php
							$brand_bread = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
							if ( ( '1' === $brand_bread['sp_hide_label'] ) || true === ( defined( 'WP_SP_WL' ) && WP_SP_WL ) ) {
								esc_html_e(
									'Breadcrumb schema helps Google to understand the page\'s position in the site hierarchy.',
									'wp-schema-pro'
								);
							} else {
								esc_html_e(
									'Breadcrumb schema helps Google to understand the page\'s position in the site hierarchy. ',
									'wp-schema-pro'
								);
								echo sprintf(
									wp_kses_post( '<a href="https://wpschema.com/docs/how-to-implement-breadcrumbs-with-schema-pro/" target="_blank">Learn more</a>', 'wp-schema-pro' )
								);
							}
							?>
						</p>
						<form method="post" action="options.php">
							<?php settings_fields( 'wp-schema-pro-breadcrumb-setting-group' ); ?>
							<?php do_settings_sections( 'wp-schema-pro-breadcrumb-setting-group' ); ?>
							<?php
							if ( isset( $bread_settings['breadcrumb'] ) ) {
								$old_data                     = $settings['enable_bread'];
								$bread_settings['breadcrumb'] = $old_data;
								update_option( 'wp-schema-pro-global-schemas', $bread_settings );
							}
							?>
							<table class="form-table">
								<tr>
									<th class="tooltip-with-image-wrapper" scope="row">
										<?php esc_html_e( 'Enable Breadcrumbs', 'wp-schema-pro' ); ?>
										<?php
											$message = '<img class="tooltip-image" src="' . esc_url( BSF_AIOSRS_PRO_URI . '/admin/assets/images/breadcrumbs.jpg' ) . '" />';
											BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
										?>
									</th>
									<td>
										<label>
											<input type="hidden" name="wp-schema-pro-breadcrumb-setting[enable_bread]" value="disabled" />
											<input type="checkbox" name="wp-schema-pro-breadcrumb-setting[enable_bread]" <?php checked( '1', $settings ['enable_bread'] ); ?> value="1" /> <?php esc_html_e( 'Yes', 'wp-schema-pro' ); ?>
										</label>
										<p style="font-style: italic;"><?php esc_html_e( 'If enabled, Google can add breadcrumbs to your website’s and pages search results. ', 'wp-schema-pro' ); ?></p>
									</td>
								</tr>
								<?php
								if ( '1' === $settings['enable_bread'] ) {
									$post_types = get_post_types( array( 'public' => true ), 'objects' );
									if ( array() !== $post_types && is_array( $post_types ) ) {
										foreach ( $post_types as $pt ) {
											?>
									<tr class="wp-schema-pro-breadcrumb-setting-options">
											<?php
											$taxonomies = get_object_taxonomies( $pt->name, 'objects' );
											if ( array() !== $taxonomies && is_array( $taxonomies ) ) {
												$values = array( 1 => __( 'None', 'wp-schema-pro' ) );
												foreach ( $taxonomies as $taxo ) {
													if ( ! $taxo->public ) {
														continue;
													}

													$values[ $taxo->name ] = $taxo->labels->singular_name;
												}
												$label    = $pt->labels->name . ' (<code>' . $pt->name . '</code>)';
												$tax_name = str_replace( ' ', '_', strtolower( $pt->name ) );

												echo '<th>' . wp_kses_post( $label ) . '</th>';
												?>
											<td>
												<select name="<?php echo 'wp-schema-pro-breadcrumb-setting[' . esc_attr( $tax_name ) . ']'; ?>" class="wp-schema-pro-custom-option-select">
																<?php
																foreach ( $values as $key => $value ) {
																	?>
													<option value="<?php print wp_kses_post( $key ); ?>"
																	<?php
																	if ( isset( $settings[ $tax_name ] ) && $key === $settings[ $tax_name ] ) {
																		?>
													selected <?php } ?> ><?php print wp_kses_post( $value ); ?>
													</option>
																	<?php
																}
																?>
													</select>
												<p><?php esc_html_e( 'Select this option to add an extra middle level in a breadcrumb trail. For example  - Domain name > Selected Option > Post Name.', 'wp-schema-pro' ); ?> </p>
											</td>
												<?php
												unset( $values, $taxo );
											}
												unset( $taxonomies );
										}
										?>
										</tr>
										<?php
										unset( $pt );
									}
								}

								?>

							<tr>
							<th colspan="2" scope="row">
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
								</p>
							<?php } ?>
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
								esc_html_e(
									'Having issues with your schema? Try regenerating the code on all your posts/pages.',
									'wp-schema-pro'
								);
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
