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
		<div id="post-body" class="columns-3">
			<div id="post-body-content">
			<?php
			if ( is_multisite() || is_network_admin() ) {
				$settings = get_site_option( 'wp-schema-pro-branding-settings' );
			} else {
				$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
			}
			?>
			<!-- White Label -->
						<div class="postbox wp-schema-pro-branding-settings" >
							<h2 class="hndle">
								<span><?php esc_html_e( 'White label Settings', 'wp-schema-pro' ); ?></span>
							</h2>
							<div class="inside">
								<p><?php esc_html_e( 'White label Branding is the ability to rename and present a product or a plugin as your own. This helps you hide the actual identity of the plugins used and lets you use your brand name instead.', 'wp-schema-pro' ); ?></p>
								<form method="post" action="options.php">
									<?php settings_fields( 'wp-schema-pro-branding-group' ); ?>
									<?php do_settings_sections( 'wp-schema-pro-branding-group' ); ?>
									<table class="form-table schema-branding">
										<tr>
											<th><?php esc_html_e( 'Plugin Name', 'wp-schema-pro' ); ?></th>
											<td><input type="text" name="wp-schema-pro-branding-settings[sp_plugin_name]" placeholder="Schema Pro" value="<?php echo esc_attr( $settings['sp_plugin_name'] ); ?>" class="regular-text sp_plugin_name" /></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'Plugin Short Name', 'wp-schema-pro' ); ?></th>
											<td><input type="text" name="wp-schema-pro-branding-settings[sp_plugin_sname]" placeholder="Schema Pro" value="<?php echo esc_attr( $settings['sp_plugin_sname'] ); ?>" class="regular-text sp_plugin_sname" /></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'Plugin Description', 'wp-schema-pro' ); ?></th>
											<td><input type="text" name="wp-schema-pro-branding-settings[sp_plugin_desc]" placeholder="Integrate Schema.org JSON-LD code in your website and improve SEO." value="<?php echo esc_attr( $settings['sp_plugin_desc'] ); ?>" class="regular-text sp_plugin_desc" /></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'Author / Agency Name', 'wp-schema-pro' ); ?></th>
											<td><input type="text" name="wp-schema-pro-branding-settings[sp_plugin_author_name]" placeholder="Brainstorm Force" value="<?php echo esc_attr( $settings['sp_plugin_author_name'] ); ?>" class="regular-text sp_plugin_author_name" /></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'Author / Agency URL', 'wp-schema-pro' ); ?></th>
											<td><input type="text" name="wp-schema-pro-branding-settings[sp_plugin_author_url]" placeholder="http://www.brainstormforce.com" value="<?php echo esc_attr( $settings['sp_plugin_author_url'] ); ?>" class="regular-text sp_plugin_author_url" /></td>
										</tr>
										<tr>
											<th class="tooltip-with-image-wrapper">
												<?php esc_html_e( 'Hide White Label Settings', 'wp-schema-pro' ); ?>
												<?php
													$message  = __( 'You\'re about to enable the white label. This will remove the white label settings.', 'wp-schema-pro' );
													$message .= '<br><br>' . __( 'If you want to access while label settings in future, simply deactivate the plugin and activate it again.', 'wp-schema-pro' );
													BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
												?>
											</th>
											<td>
												<label>
													<input type="hidden" name="wp-schema-pro-branding-settings[sp_hide_label]" value="disabled" />
													<input type="checkbox" name="wp-schema-pro-branding-settings[sp_hide_label]" <?php checked( '1', $settings['sp_hide_label'] ); ?> value="1" /> <?php esc_html_e( 'Hide White Label Settings', 'wp-schema-pro' ); ?>
												</label>
											</td>
										</tr>
										<tr>
											<th colspan="2">
												<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wp-schema-pro' ); ?>" />
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
		</div>
	</div>
</div>
