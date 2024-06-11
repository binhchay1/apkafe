<?php
/**
 * Schema Pro Admin Init
 *
 * @package Schema Pro
 */

if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_REQUEST['wp_schema_pro_admin_page_nonce'] ), 'wp_schema_pro_admin_page' ) ) {
	return;
}
$setting_url            = self::get_page_url( 'settings' );
$wpsp_advanced_settings = self::get_page_url( 'wpsp-advanced-settings' );
$current_section        = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'general';
?>
<div id="wp-schema-pro-setting-links">
	<a href="<?php echo esc_url( $setting_url ); ?>" <?php echo ( 'general' === $current_section ) ? 'class="active"' : ''; ?> ><?php esc_html_e( 'General', 'wp-schema-pro' ); ?></a> |
	<a href="<?php echo esc_url( $setting_url . '&section=social-profiles' ); ?>" <?php echo ( 'social-profiles' === $current_section ) ? 'class="active"' : ''; ?>><?php esc_html_e( 'Social Profiles', 'wp-schema-pro' ); ?></a> |
	<a href="<?php echo esc_url( $setting_url . '&section=corporate-contact' ); ?>" <?php echo ( 'corporate-contact' === $current_section ) ? 'class="active"' : ''; ?>><?php esc_html_e( 'Contact Information', 'wp-schema-pro' ); ?></a> |
	<a href="<?php echo esc_url( $setting_url . '&section=global-schemas' ); ?>" <?php echo ( 'global-schemas' === $current_section ) ? 'class="active"' : ''; ?>><?php esc_html_e( 'Other Schemas', 'wp-schema-pro' ); ?></a>
</div>
<div class="wrap bsf-aiosrs-pro clear">
	<div id="poststuff">
		<div id="post-body" class="columns-2">
			<div id="post-body-content">
				<?php
				switch ( $current_section ) {
					case 'general':
						$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-general-settings'];
						?>
						<!-- General Settings -->
						<div class="postbox wp-schema-pro-general-settings" >
							<h2 class="hndle">
								<span><?php esc_html_e( 'General', 'wp-schema-pro' ); ?></span>
								<?php
								if ( WP_Schema_Pro_Yoast_Compatibility::$activated ) {
									$message  = __( 'Looks like you have Yoast SEO plugin installed. So we\'ve gone ahead and disabled some features which comes with Yoast SEO as well.', 'wp-schema-pro' );
									$message .= '<br><br>' . __( 'If you would still like to enable then,', 'wp-schema-pro' );
									$message .= ' <a href="' . esc_url( $wpsp_advanced_settings . '#aiosrs-pro-settings-yoast-compatibility' ) . '">Click Here</a>';
									BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
								}
								?>
							</h2>
							<div class="inside">
								<p><?php esc_html_e( 'To help Google understand what your website is about, select the most suitable type for your website below, and fill in the required basic information.', 'wp-schema-pro' ); ?></p>
								<form method="post" action="options.php">
									<?php settings_fields( 'wp-schema-pro-general-settings-group' ); ?>
									<?php do_settings_sections( 'wp-schema-pro-general-settings-group' ); ?>
									<table class="form-table">
										<tr>
											<th scope="row">
												<?php esc_html_e( 'This Website Represents', 'wp-schema-pro' ); ?>
											</th>
											<td>
												<select name="wp-schema-pro-general-settings[site-represent]">
													<option <?php selected( $settings['site-represent'], '' ); ?> value=""> <?php esc_html_e( '--None--', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['site-represent'], 'person' ); ?> value="person"> <?php esc_html_e( 'Personal Website', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['site-represent'], 'Otherbusiness' ); ?> value="Otherbusiness"> <?php esc_html_e( 'Business Website', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['site-represent'], 'organization' ); ?> value="organization"> <?php esc_html_e( 'Organization', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['site-represent'], 'personblog' ); ?> value="person"> <?php esc_html_e( 'Personal Blog', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['site-represent'], 'Smallbusiness' ); ?> value="Smallbusiness"> <?php esc_html_e( 'Community Blog/News Website ', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['site-represent'], 'Webshop' ); ?> value="Webshop"> <?php esc_html_e( 'Webshop', 'wp-schema-pro' ); ?></option>
												</select>
											</td>
										</tr>
										<?php
										if ( 'person' === $settings['site-represent'] || 'personblog' === $settings['site-represent'] ) {
											$settings['site-represent'] = 'person';
										}
										if ( 'organization' === $settings['site-represent'] || 'Webshop' === $settings['site-represent'] || 'Smallbusiness' === $settings['site-represent'] || 'Otherbusiness' === $settings['site-represent'] ) {
											$settings['site-represent'] = 'organization';
										}
										?>
										<tr class="wp-schema-pro-site-logo-wrap">
											<th id =""><?php esc_html_e( 'Website Logo', 'wp-schema-pro' ); ?></th>
											<td>
											<select style='display:none' name="wp-schema-pro-general-settings[site-logo]" class="wp-schema-pro-custom-option-select">
													<option  <?php selected( $settings['site-logo'], 'custom' ); ?> value="custom"><?php esc_html_e( 'Add Custom Logo', 'wp-schema-pro' ); ?></option>
												</select>
												<div class="custom-field-wrapper site-logo-custom-wrap">
													<input type="hidden" class="single-image-field" name="wp-schema-pro-general-settings[site-logo-custom]" value="<?php echo esc_attr( $settings['site-logo-custom'] ); ?>" />
													<?php
													if ( ! empty( $settings['site-logo-custom'] ) ) {
														$image_url = wp_get_attachment_url( $settings['site-logo-custom'] );
													} else {
														$logo_id = '';
														if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
																$logo_id = get_theme_mod( 'custom_logo' );
														}
														$image_url = wp_get_attachment_url( $logo_id );
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
												<p style="font-style: italic;"><?php esc_html_e( 'Recommended minimum logo size 112 x 112 pixels.', 'wp-schema-pro' ); ?></p>
												<p style="font-style: italic;"><?php esc_html_e( 'The image must be in .jpg, .png, .gif, .svg, or .webp format.', 'wp-schema-pro' ); ?></p>
											</td>
										</tr>
										<tr class="wp-schema-pro-person-name-wrap" <?php echo ( 'person' !== $settings['site-represent'] ) ? 'style="display: none;"' : ''; ?>>
											<th class = 'wpsp-organization-label' ><?php esc_html_e( 'Website Owner Name', 'wp-schema-pro' ); ?></th>
											<td>
												<input type="text" name="wp-schema-pro-general-settings[person-name]" value="<?php echo esc_attr( $settings['person-name'] ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
											</td>
										</tr>
										<tr class="wp-schema-pro-site-name-wrap" <?php echo ( 'organization' !== $settings['site-represent'] ) ? 'style="display: none;"' : ''; ?>>
											<th class = 'wpsp-organization-label'><?php esc_html_e( 'Organization Name', 'wp-schema-pro' ); ?>
											</th>
											<td>
												<input type="text" name="wp-schema-pro-general-settings[site-name]" value="<?php echo esc_attr( $settings['site-name'] ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
											</td>
										</tr>
										<tr class="wp-schema-pro-site-name-wrap" <?php echo ( 'organization' !== $settings['site-represent'] ) ? 'style="display: none;"' : ''; ?>>
											<th><?php esc_html_e( 'Organization Type', 'wp-schema-pro' ); ?>
											</th>
											<td>
											<?php
											$option_list = BSF_AIOSRS_Pro_Schema::get_dropdown_options( 'Organization-type' );
											?>
											<select class ="wpsp-setup-configuration-settings" name="wp-schema-pro-general-settings[organization]" >
											<?php
											if ( ! empty( $option_list ) ) {
												foreach ( $option_list as $key => $value ) {
													if ( '-- None --' !== $value ) {
														?>
												<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['organization'], $key ); ?>><?php echo esc_html( $value ); ?></option>
														<?php
													}
												}
											}
											?>
										</select>
										<p style="font-style: italic;">
										<?php
										$brand_bread = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
										if ( ( '1' === $brand_bread['sp_hide_label'] ) || true === ( defined( 'WP_SP_WL' ) && WP_SP_WL ) ) {
											esc_html_e( 'Select the type that best describes your website. If you can\'t find one that applies exactly, use the generic "General/Other" type. Further create Local Business schema for "General/Other" type.', 'wp-schema-pro' );
										} else {
											esc_html_e( 'Select the type that best describes your website. If you can\'t find one that applies exactly, use the generic "General/Other" type. Further create Local Business schema for "General/Other" type. ', 'wp-schema-pro' );
											echo sprintf(
												wp_kses_post( '<a href="https://wpschema.com/docs/organization-type-in-setup-wizard/" target="_blank">Learn more</a>', 'wp-schema-pro' )
											);
										}
										?>
										</p>
											</td>
										</tr>
										<tr>
											<th colspan="2">
												<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wp-schema-pro' ); ?>" />
											</th>
										</tr>
									</table>
								</form>
							</div>
						</div>
						<?php
						break;

					case 'social-profiles':
						$settings        = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-social-profiles'];
						$social_settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-social-profiles'];
						?>
						<!-- Social Profiles -->
						<div class="postbox wp-schema-pro-social-profiles" >
							<h2 class="hndle">
								<span><?php esc_html_e( 'Social Profiles', 'wp-schema-pro' ); ?></span>
								<?php
								if ( WP_Schema_Pro_Yoast_Compatibility::$activated ) {
									$message  = __( 'Looks like you have Yoast SEO plugin installed. So we\'ve gone ahead and disabled some features which comes with Yoast SEO as well.', 'wp-schema-pro' );
									$message .= '<br><br>' . __( 'If you would still like to enable then,', 'wp-schema-pro' );
									$message .= ' <a href="' . esc_url( $wpsp_advanced_settings . '#aiosrs-pro-settings-yoast-compatibility' ) . '">Click Here</a>';
									BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
								}
								?>
							</h2>
							<div class="inside">
								<p><?php esc_html_e( 'Please enter all your possible social media profiles. These links can appear in the knowledge panel of the search results for your website.', 'wp-schema-pro' ); ?></p>

								<form method="post" action="options.php">
									<?php settings_fields( 'wp-schema-pro-social-profiles-group' ); ?>
									<?php do_settings_sections( 'wp-schema-pro-social-profiles-group' ); ?>
									<table id="repeatable-fieldset-one" class="form-table">
										<tr>
											<th><?php esc_html_e( 'Facebook', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[facebook]"  value="<?php echo esc_attr( $settings['facebook'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'Instagram', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[instagram]"  value="<?php echo esc_attr( $settings['instagram'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'YouTube', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[youtube]"  value="<?php echo esc_attr( $settings['youtube'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'Twitter', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[twitter]"  value="<?php echo esc_attr( $settings['twitter'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'Pinterest', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[pinterest]"  value="<?php echo esc_attr( $settings['pinterest'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'LinkedIn', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[linkedin]"  value="<?php echo esc_attr( $settings['linkedin'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'SoundCloud', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[soundcloud]"  value="<?php echo esc_attr( $settings['soundcloud'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'Tumblr', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[tumblr]"  value="<?php echo esc_attr( $settings['tumblr'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'Wikipedia', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[wikipedia]"  value="<?php echo esc_attr( $settings['wikipedia'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'MySpace', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[myspace]"  value="<?php echo esc_attr( $settings['myspace'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
										</tr>
										<tr style="display:none">
											<th><?php esc_html_e( 'Google+', 'wp-schema-pro' ); ?></th>
											<td><input type="url" name="wp-schema-pro-social-profiles[google-plus]"  value="<?php echo esc_attr( $settings['google-plus'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
										</tr>
										<?php
										if ( isset( $settings ) && is_array( $settings ) && isset( $settings['other'] ) && ( is_array( $settings['other'] ) || is_object( $settings['other'] ) ) ) {
											foreach ( $settings['other'] as $sub_social_profiles => $value ) {
												if ( isset( $value ) && ! empty( $value ) ) {
													?>
													<tr>
														<th class="wpsp-other-th"><?php esc_html_e( 'Other', 'wp-schema-pro' ); ?></th>
														<td>
															<input type="url" class="wpsp-other" name="wp-schema-pro-social-profiles[other][<?php echo esc_attr( $sub_social_profiles ); ?>]"  value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" />
															<span class="wpsp-field-close remove-row dashicons dashicons-dismiss"><a href="#" class=""></a></span>
														</td>
													</tr>
													<?php
												}
											}
										}
										?>
										<tr  class="empty-row screen-reader-text"> <!-- empty hidden one for jQuery -->
											<th class="wpsp-other-th"><?php esc_html_e( 'Other', 'wp-schema-pro' ); ?></th>
											<td><input type="url" class="wpsp-other" name="wp-schema-pro-social-profiles[other][]"  value="" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /><span class ="wpsp-field-close remove-row dashicons dashicons-dismiss "><a href="#" class="remove-row"></a></span></td>
										</tr>
										</table>
										<p><a id="add-row" class="button" href="#">Add +</a></p>
										<table class="form-table">
										<tr>
											<th colspan="2">
												<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wp-schema-pro' ); ?>" />
											</th>
										</tr>
										</table>
								</form>
							</div>
						</div>
						<?php
						break;

					case 'corporate-contact':
						$contact_settings['contact-type-other'] = '';
						$contact_settings                       = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-corporate-contact'];
						?>
						<!-- Corporate Contact -->
						<div class="postbox wp-schema-pro-corporate-contact" >
							<h2 class="hndle">
								<span><?php esc_html_e( 'Contact Information', 'wp-schema-pro' ); ?></span>
								<?php
								if ( WP_Schema_Pro_Yoast_Compatibility::$activated ) {
									$message  = __( 'Looks like you have Yoast SEO plugin installed. So we\'ve gone ahead and disabled some features which comes with Yoast SEO as well.', 'wp-schema-pro' );
									$message .= '<br><br>' . __( 'If you would still like to enable then,', 'wp-schema-pro' );
									$message .= ' <a href="' . esc_url( $wpsp_advanced_settings . '#aiosrs-pro-settings-yoast-compatibility' ) . '">Click Here</a>';
									BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
								}
								?>
							</h2>
							<div class="inside">
								<p><?php esc_html_e( 'The information provided here will tell search engines about your website\'s contact details. This can improve your appearance in rich snippets and can be displayed in the Knowledge Panel on some searches. ', 'wp-schema-pro' ); ?></p>
								<form method="post" action="options.php">
									<?php settings_fields( 'wp-schema-pro-corporate-contact-group' ); ?>
									<?php do_settings_sections( 'wp-schema-pro-corporate-contact-group' ); ?>
									<table class="form-table" >
										<tr>
											<th><?php esc_html_e( 'Select Contact Type', 'wp-schema-pro' ); ?></th>
											<td><select name="wp-schema-pro-corporate-contact[contact-type]">
													<option <?php selected( $contact_settings['contact-type'], '' ); ?> value=""> <?php esc_html_e( '--None--', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $contact_settings['contact-type'], 'customer support' ); ?> value="customer support"> <?php esc_html_e( 'Customer Support', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $contact_settings['contact-type'], 'technical support' ); ?> value="technical support"> <?php esc_html_e( 'Technical Support', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $contact_settings['contact-type'], 'billing support' ); ?> value="billing support"> <?php esc_html_e( 'Billing Support', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $contact_settings['contact-type'], 'bill payment' ); ?> value="bill payment"> <?php esc_html_e( 'Bill payment', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $contact_settings['contact-type'], 'sales' ); ?> value="sales"> <?php esc_html_e( 'Sales', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $contact_settings['contact-type'], 'reservations' ); ?> value="reservations"> <?php esc_html_e( 'Reservations', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $contact_settings['contact-type'], 'credit card support' ); ?> value="credit card support"> <?php esc_html_e( 'Credit Card Support', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $contact_settings['contact-type'], 'emergency' ); ?> value="emergency"> <?php esc_html_e( 'Emergency', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $contact_settings['contact-type'], 'baggage tracking' ); ?> value="baggage tracking"> <?php esc_html_e( 'Baggage Tracking', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $contact_settings['contact-type'], 'roadside assistance' ); ?> value="roadside assistance"> <?php esc_html_e( 'Roadside Assistance', 'wp-schema-pro' ); ?>
													<option <?php selected( $contact_settings['contact-type'], 'other' ); ?> value="other"> <?php esc_html_e( 'Other', 'wp-schema-pro' ); ?>
													</option>
												</select>
											</td>
										</tr>
										<tr class="wp-schema-pro-other-wrap" <?php echo ( 'other' !== $contact_settings['contact-type'] ) ? 'style="display: none;"' : ''; ?>>
											<th><?php esc_html_e( 'Other Contact Type ', 'wp-schema-pro' ); ?>
											</th>
											<td><input type="text" name="wp-schema-pro-corporate-contact[contact-type-other]"  value="<?php echo esc_attr( $contact_settings['contact-type-other'] ); ?>"  /></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'Contact Page URL', 'wp-schema-pro' ); ?>
											</th>
											<td>
											<?php
											if ( empty( $contact_settings['contact-page-id'] ) && ! empty( $contact_settings['url'] ) ) {
												
												if ( function_exists( 'wpcom_vip_url_to_postid' ) ) {
													$contact_settings['contact-page-id'] = wpcom_vip_url_to_postid( $contact_settings['url'] );
												} else {
													$contact_settings['contact-page-id'] = url_to_postid( $contact_settings['url'] );
												}
											}
											?>
											<select class = ' wp-select2 wpsp-setup-configuration-settings' name="wp-schema-pro-corporate-contact[contact-page-id]">
														<?php
														$post_title = get_the_title( $contact_settings['contact-page-id'] );
														if ( '0' === $contact_settings['contact-page-id'] ) {
															$post_title = '--None--';
														}
														?>
														<option selected="selected"  value="<?php echo esc_attr( $contact_settings['contact-page-id'] ); ?>"><?php echo esc_html( preg_replace( '/^_/', '', esc_html( str_replace( '_', ' ', $post_title ) ) ) ); ?></option>
												</select>
												</td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'Contact Number', 'wp-schema-pro' ); ?>
											</th>
											<td><input type="text" name="wp-schema-pro-corporate-contact[telephone]"  value="<?php echo esc_attr( $contact_settings['telephone'] ); ?>" placeholder="<?php echo esc_attr( 'e.g. +1-800-555-1212' ); ?>" />
											<p style="font-style: italic;"><?php esc_html_e( 'Enter the international version of your contact phone number starting with the “+” sign and country code, e.g., +1 for the US and Canada. Example: +1-800-555-1212. Search your country code here', 'wp-schema-pro' ); ?> <a href="https://countrycode.org/" target="_blank">Learn more</a></p></td>
										</tr>
										<?php $extra_contact_field = apply_filters( 'wp_schema_pro_contactpoint_extra_field_enabled', true ); ?>
										<tr <?php echo ( true === $extra_contact_field ) ? 'style="display: none;"' : ''; ?>>
											<th><?php esc_html_e( 'Area Served', 'wp-schema-pro' ); ?>
											<?php
													$message = __( '	The geographic area where a service or offered item is provided. Supersedes serviceArea. Examples US,ES FR', 'wp-schema-pro' );
													self::get_tooltip( $message );
											?>
											</th>
											<td><input type="text" name="wp-schema-pro-corporate-contact[areaServed]"  value="<?php echo esc_attr( $contact_settings['areaServed'] ); ?>" placeholder="<?php echo esc_attr( 'e.g. IN, US' ); ?>" /></td>
										</tr>
										<tr <?php echo ( true === $extra_contact_field ) ? 'style="display: none;"' : ''; ?> >
											<th><?php esc_html_e( 'Available Language', 'wp-schema-pro' ); ?>
												<?php
													$message = __( 'Details about the language spoken. Languages may be specified by their common English name. If omitted, the language defaults to English.', 'wp-schema-pro' );
													self::get_tooltip( $message );
												?>
											</th>
											<td><input type="text" name="wp-schema-pro-corporate-contact[availableLanguage]"  value="<?php echo esc_attr( $contact_settings['availableLanguage'] ); ?>" placeholder="<?php echo esc_attr( 'e.g. English, French' ); ?>" /></td>
										</tr>
										<tr <?php echo ( true === $extra_contact_field ) ? 'style="display: none;"' : ''; ?> >
											<th><?php esc_html_e( 'Contact Option', 'wp-schema-pro' ); ?>
											<?php
													$message = __( 'An option available on this contact point (e.g. a toll-free number or support for hearing-impaired callers). Hearing Impaired Supported:- Uses devices to support users with hearing impairments.', 'wp-schema-pro' );
													self::get_tooltip( $message );
											?>
											</th>
											<td class="schema-contact-type-option-change schema-contact-type-option ">
												<input type="checkbox" name="wp-schema-pro-corporate-contact[contact-hear]" <?php checked( isset( $contact_settings ['contact-hear'] ) ); ?> value="HearingImpairedSupported" /> <?php esc_html_e( 'Hearing Impaired Supported', 'wp-schema-pro' ); ?></td>
												<td class=" schema-contact-type-option" >
												<input type="checkbox" name="wp-schema-pro-corporate-contact[contact-toll]" <?php checked( isset( $contact_settings ['contact-toll'] ) ); ?> value="TollFree" /> <?php esc_html_e( 'Toll Free', 'wp-schema-pro' ); ?></td>
										</tr>
										<tr class= "schema-contact-type-option" <?php echo ( true === $extra_contact_field ) ? 'style="display: none;"' : ''; ?>>
											<th class="tooltip-with-image-wrapper">
												<?php esc_html_e( 'Enable ContactPoint on schema type?', 'wp-schema-pro' ); ?>
												<?php
													$message = __( 'If enabled, It will add ContactPoint on Local Business and Person schema type.', 'wp-schema-pro' );
													BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
												?>
											</th>
											<td>
												<label>
													<input type="hidden" name="wp-schema-pro-corporate-contact[cp-schema-type]" value="disabled" />
													<input type="checkbox" name="wp-schema-pro-corporate-contact[cp-schema-type]" <?php checked( '1', $contact_settings ['cp-schema-type'] ); ?> value="1" /> <?php esc_html_e( 'Yes', 'wp-schema-pro' ); ?>
												</label>
											</td>
										</tr>
									</tbody>
									</table>
						<table class="form-table .contact-form">

										<tr>
											<th colspan="2">
												<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wp-schema-pro' ); ?>" />
											</th>
										</tr>
									</table>
								</form>
							</div>
						</div>
						<?php
						break;

					case 'global-schemas':
						$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-global-schemas'];
						?>
						<!-- Global Schemas -->
						<div class="postbox wp-schema-pro-global-schemas" >
							<h2 class="hndle">
								<span><?php esc_html_e( 'Other Schema', 'wp-schema-pro' ); ?></span>
								<?php
								if ( WP_Schema_Pro_Yoast_Compatibility::$activated ) {
									$message  = __( 'Looks like you have Yoast SEO plugin installed. So we\'ve gone ahead and disabled some features which comes with Yoast SEO as well.', 'wp-schema-pro' );
									$message .= '<br><br>' . __( 'If you would still like to enable then,', 'wp-schema-pro' );
									$message .= ' <a href="' . esc_url( $wpsp_advanced_settings . '#aiosrs-pro-settings-yoast-compatibility' ) . '">Click Here</a>';
									BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
								}
								?>
							</h2>
							<div class="inside">
								<p><?php esc_html_e( 'Fill in additional information about your website to make sure that search engines fully understand what it’s about. This will help improve your SEO further.', 'wp-schema-pro' ); ?></p>
								<form method="post" action="options.php">
									<?php settings_fields( 'wp-schema-pro-global-schemas-group' ); ?>
									<?php do_settings_sections( 'wp-schema-pro-global-schemas-group' ); ?>
									<table class="form-table">
										<tr>
											<th>
												<?php esc_html_e( 'Select About Page', 'wp-schema-pro' ); ?>
												<?php
													$message = __( 'Select your about page from the dropdown list. This will add AboutPage schema on the selected page.', 'wp-schema-pro' );
													self::get_tooltip( $message );
												?>
											</th>
											<td>
												<select class = 'wp-select2 wpsp-setup-configuration-settings' name="wp-schema-pro-global-schemas[about-page]">
													<option value=""><?php esc_html_e( '--None--', 'wp-schema-pro' ); ?></option>
														<?php
														$post_title = get_the_title( $settings['about-page'] );
														if ( '0' === $settings['about-page'] ) {
															$post_title = '--None--';
														}
														?>
														<option selected="selected"  value="<?php echo esc_attr( $settings['about-page'] ); ?>"><?php echo esc_html( preg_replace( '/^_/', '', esc_html( str_replace( '_', ' ', $post_title ) ) ) ); ?></option>
												</select>
											</td>
										</tr>
										<tr>
											<th>
												<?php esc_html_e( 'Select Contact Page', 'wp-schema-pro' ); ?>
												<?php
													$message = __( 'Select your contact page from the dropdown list. This will add ContactPage schema on the selected page.', 'wp-schema-pro' );
													self::get_tooltip( $message );
												?>
											</th>
											<td>
												<select class = 'wp-select2 wpsp-setup-configuration-settings' name="wp-schema-pro-global-schemas[contact-page]">
														<?php
														$post_title = get_the_title( $settings['contact-page'] );
														if ( '0' === $settings['contact-page'] ) {
															$post_title = '--None--';
														}
														?>
														<option selected="selected"  value="<?php echo esc_attr( $settings['contact-page'] ); ?>"><?php echo esc_html( preg_replace( '/^_/', '', esc_html( str_replace( '_', ' ', $post_title ) ) ) ); ?></option>
												</select>
											</td>
										</tr>
										<tr>
											<th class="tooltip-with-image-wrapper">
												<?php esc_html_e( 'Select Menu for SiteLinks Schema', 'wp-schema-pro' ); ?>
												<?php
													$message = '<img class="tooltip-image" src="' . esc_url( BSF_AIOSRS_PRO_URI . '/admin/assets/images/sitelinks.jpg' ) . '" />';
													BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
												?>
											</th>
											<td>
												<?php $nav_menus = wp_get_nav_menus(); ?>
												<select name="wp-schema-pro-global-schemas[site-navigation-element]" >
													<option <?php selected( '', $settings['site-navigation-element'] ); ?> value=""><?php esc_html_e( '--None--', 'wp-schema-pro' ); ?></option>
													<?php foreach ( $nav_menus as $nav_menu ) { ?>
														<option <?php selected( $nav_menu->term_id, $settings['site-navigation-element'] ); ?> value="<?php echo esc_attr( $nav_menu->term_id ); ?>"><?php echo esc_html( $nav_menu->name ); ?></option>
													<?php } ?>
												</select>
												<p style="font-style: italic;"><?php esc_html_e( 'This helps Google understand the most important pages on your website and can generate Rich Snippets. ', 'wp-schema-pro' ); ?></p>
											</td>
										</tr>
										<tr>
											<th class="tooltip-with-image-wrapper" id ="" >
												<?php esc_html_e( 'Enable SiteLinks Search Box', 'wp-schema-pro' ); ?>
												<?php
													$message = '<img class="tooltip-image" src="' . esc_url( BSF_AIOSRS_PRO_URI . '/admin/assets/images/sitelink-search.jpg' ) . '" />';
													BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
												?>
											</th>
											<td>
												<label>
													<input type="hidden" name="wp-schema-pro-global-schemas[sitelink-search-box]" value="disabled" />
													<input type="checkbox" name="wp-schema-pro-global-schemas[sitelink-search-box]" <?php checked( '1', $settings['sitelink-search-box'] ); ?> value="1" /> <?php esc_html_e( 'Yes', 'wp-schema-pro' ); ?>
												</label>
												<p style="font-style: italic;"><?php esc_html_e( 'If enabled, Google can display a search box with your Search results. ', 'wp-schema-pro' ); ?></p>
											</td>
										</tr>
										<tr style="display: none;">
											<th class="tooltip-with-image-wrapper" id ="">
												<?php esc_html_e( 'Enable Breadcrumbs', 'wp-schema-pro' ); ?>
												<?php
													$message  = __( 'If enabled, Google can add breadcrumbs to your website’s and pages search results.', 'wp-schema-pro' );
													$message .= '<br /><img class="tooltip-image" src="' . esc_url( BSF_AIOSRS_PRO_URI . '/admin/assets/images/breadcrumbs.jpg' ) . '" />';
													BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
												?>
											</th>
											<td>
												<label>
													<input type="hidden" name="wp-schema-pro-global-schemas[breadcrumb]" value="disabled" />
													<input type="checkbox" name="wp-schema-pro-global-schemas[breadcrumb]" <?php checked( '1', $settings ['breadcrumb'] ); ?> value="1" /> <?php esc_html_e( 'Yes', 'wp-schema-pro' ); ?>
												</label>
												<p style="font-style: italic;"><?php esc_html_e( 'If enabled, Google can add breadcrumbs to your website’s and pages search results. ', 'wp-schema-pro' ); ?></p>
											</td>
										</tr>
										<tr>
											<th colspan="2" id ="">
												<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wp-schema-pro' ); ?>" />
											</th>
										</tr>
									</table>
								</form>
							</div>
						</div>
						<?php
						break;
					default:
						break;

				}
				?>
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
								esc_html_e( 'Having issues with your schema? Try regenerating the code on all your posts/pages.', 'wp-schema-pro' );
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
