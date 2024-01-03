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
				$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-breadcrumb-setting'];
			?>
			<!-- General Settings -->
				<div class="postbox wp-schema-pro-breadcrumb-setting" >
					<h2 class="hndle">
						<span><?php esc_html_e( 'Breadcrumbs Settings', 'wp-schema-pro' ); ?></span>
					</h2>
					<div class="inside">
						<p>
							<?php
							$brand_bread = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
							if ( ( '1' === $brand_bread['sp_hide_label'] ) || true === ( defined( 'WP_SP_WL' ) && WP_SP_WL ) ) {
								esc_html_e(
									'Breadcrumbs are important for SEO. They help Google to understand the site structure. You can set breadcrumbs with a taxonomy for the following post types.',
									'wp-schema-pro'
								);
							} else {
								esc_html_e(
									'Breadcrumbs are important for SEO. They help Google to understand the site structure. You can set breadcrumbs with a taxonomy for the following post types.',
									'wp-schema-pro'
								);
								echo sprintf(
									wp_kses_post( '<a href="https://wpschema.com/docs/how-to-implement-breadcrumbs-with-schema-pro/"> Know More...</a>', 'wp-schema-pro' )
								);
							}
							?>
						</p>
						<form method="post" action="options.php">
							<?php settings_fields( 'wp-schema-pro-breadcrumb-setting-group' ); ?>
							<?php do_settings_sections( 'wp-schema-pro-breadcrumb-setting-group' ); ?>
							<table class="form-table">
								<?php
									$post_types = get_post_types( array( 'public' => true ), 'objects' );

								if ( array() !== $post_types && is_array( $post_types ) ) {
									foreach ( $post_types as $pt ) {
										?>
								<tr>
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

								?>

							<tr> 
							<th colspan="2">
							<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'wp-schema-pro' ); ?>" />
							</th>
							</tr>
							</table>
						</form>
					</div>
				</div>
			</div>
			<div class="postbox-container" id="postbox-container-1">
				<div id="side-sortables" style="">
					<div class="postbox">
						<h2 class="hndle"><span><?php esc_html_e( 'Setup Wizard', 'wp-schema-pro' ); ?></span></h2>
						<div class="inside">
							<div>
								<?php
								if ( is_multisite() ) {
									$settings = get_site_option( 'wp-schema-pro-branding-settings' );
								} else {
									$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
								}
								if ( '' !== $settings['sp_plugin_name'] ) {
									/* translators: %s: search term */
									$brand_name = sprintf( __( 'Need help configure %s step by step?', 'wp-schema-pro' ), $settings['sp_plugin_name'] );
									?>
										<p><?php echo esc_html( $brand_name ); ?></p>
													<?php
								} else {
									?>
								<p><?php esc_html_e( 'Need help configure Schema Pro step by step?', 'wp-schema-pro' ); ?></p><?php } ?>
								<a href="<?php echo esc_url( admin_url( 'index.php?page=aiosrs-pro-setup-wizard' ) ); ?>" class="button button-large button-primary"><?php esc_html_e( 'Start setup wizard &raquo;', 'wp-schema-pro' ); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
