<?php
/**
 * Schema Pro Admin Init
 *
 * @package Schema Pro
 */

$setting_url = self::get_page_url( 'branding-settings' );
?>

<div class="wrap bsf-aiosrs-pro clear">
	<div id="poststuff">
		<div id="post-body" class="columns-2">
			<div id="post-body-content">
				<?php
				if ( is_multisite() || is_network_admin() ) {
					$settings = get_site_option( 'wp-schema-pro-branding-settings' );
				} else {
					$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
				}
				$sp_names       = isset( $settings['sp_plugin_name'] ) ? $settings['sp_plugin_name'] : '';
				$sp_snames      = isset( $settings['sp_plugin_sname'] ) ? $settings['sp_plugin_sname'] : '';
				$sp_desc        = isset( $settings['sp_plugin_desc'] ) ? $settings['sp_plugin_desc'] : '';
				$sp_author_name = isset( $settings['sp_plugin_author_name'] ) ? $settings['sp_plugin_author_name'] : '';
				$sp_author_url  = isset( $settings['sp_plugin_author_url'] ) ? $settings['sp_plugin_author_url'] : '';
				$sp_hide_label  = isset( $settings['sp_hide_label'] ) ? $settings['sp_hide_label'] : 'disabled';
				?>
				<!-- White Label -->
				<div class="postbox wp-schema-pro-branding-settings" >
					<h2 class="hndle">
						<span><?php esc_html_e( 'Configure White Label', 'wp-schema-pro' ); ?></span>
					</h2>
					<div class="inside">
						<p>
						<?php
						esc_html_e( 'White Label lets you change the identity (name, description, etc.) of this plugin on the WordPress Dashboard. You can rename the plugin and present it as your own. This is mostly used by agencies and developers who are building websites for clients. ', 'wp-schema-pro' );
							echo sprintf(
								wp_kses_post( '<a href="https://wpschema.com/docs/schema-pro-white-label/" target="_blank">Learn more</a>', 'wp-schema-pro' )
							);
							?>
						</p>
						<form method="post" action="options.php">
							<?php settings_fields( 'wp-schema-pro-branding-group' ); ?>
							<?php do_settings_sections( 'wp-schema-pro-branding-group' ); ?>
							<table class="form-table schema-branding">
								<tr>
									<th><?php esc_html_e( 'Plugin Name', 'wp-schema-pro' ); ?></th>
									<td><input type="text" name="wp-schema-pro-branding-settings[sp_plugin_name]" placeholder="Schema Pro" value="<?php echo esc_attr( $sp_names ); ?>" class="regular-text sp_plugin_name" /></td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'Plugin Short Name', 'wp-schema-pro' ); ?></th>
									<td><input type="text" name="wp-schema-pro-branding-settings[sp_plugin_sname]" placeholder="Schema Pro" value="<?php echo esc_attr( $sp_snames ); ?>" class="regular-text sp_plugin_sname" /></td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'Plugin Description', 'wp-schema-pro' ); ?></th>
									<td><input type="text" name="wp-schema-pro-branding-settings[sp_plugin_desc]" placeholder="Integrate Schema.org JSON-LD code in your website." value="<?php echo esc_attr( $sp_desc ); ?>" class="regular-text sp_plugin_desc" /></td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'Author / Agency Name', 'wp-schema-pro' ); ?></th>
									<td><input type="text" name="wp-schema-pro-branding-settings[sp_plugin_author_name]" placeholder="Brainstorm Force" value="<?php echo esc_attr( $sp_author_name ); ?>" class="regular-text sp_plugin_author_name" /></td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'Author / Agency URL', 'wp-schema-pro' ); ?></th>
									<td><input type="text" name="wp-schema-pro-branding-settings[sp_plugin_author_url]" placeholder="https://www.brainstormforce.com" value="<?php echo esc_attr( $sp_author_url ); ?>" class="regular-text sp_plugin_author_url" /></td>
								</tr>
								<tr>
									<th scope="row" class="tooltip-with-image-wrapper">
										<?php esc_html_e( 'Hide White Label Settings', 'wp-schema-pro' ); ?>
										<?php
											$message  = __( 'Checking this box will enable the White Label features of this plugin and will remove the white label settings.', 'wp-schema-pro' );
											$message .= '<br><br>' . __( 'If you want to access it in the future, you would have to deactivate and reactivate the plugin.', 'wp-schema-pro' );
											BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
										?>
									</th>
									<td>
										<label>
											<input type="hidden" name="wp-schema-pro-branding-settings[sp_hide_label]" value="disabled" />
											<input type="checkbox" name="wp-schema-pro-branding-settings[sp_hide_label]" <?php checked( '1', $sp_hide_label ); ?> value="1" /> <?php esc_html_e( 'Hide White Label Settings', 'wp-schema-pro' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row" colspan="2">
										<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wp-schema-pro' ); ?>" />
									</th>
								</tr>
							</table>
							<?php if ( is_multisite() ) : ?>
								<p class="install-help"><strong><?php esc_html_e( 'Note:', 'wp-schema-pro' ); ?></strong>  <?php esc_html_e( 'Whitelabel settings are applied to all the sites in the Network.', 'wp-schema-pro' ); ?></p>
							<?php endif; ?>
							<?php wp_nonce_field( 'white-label', 'wp-schema-pro-white-label-nonce' ); ?>
						</form>
					</div>
				</div>
			</div>
			<div class="postbox-container" id="postbox-container-1">
				<div id="side-sortables" style="min-height: 0px;">
					<div class="postbox">
						<h2 class="hndle"><span><?php esc_html_e( 'Setup Wizard', 'wp-schema-pro' ); ?></span></h2>
						<div class="inside">
							<div>
								<?php
								$sp_name = isset( $settings['sp_plugin_name'] ) ? $settings['sp_plugin_name'] : '';
								if ( is_multisite() ) {
									$settings = get_site_option( 'wp-schema-pro-branding-settings' );
								} else {
									$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
								}
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
							esc_html_e( 'Having issues with your schema? Try regenerating the code on all your posts/pages. ', 'wp-schema-pro' );
								echo sprintf(
									wp_kses_post( '<a href="https://wpschema.com/docs/regenerate-schema/" target="_blank">Learn More</a>', 'wp-schema-pro' )
								);
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
