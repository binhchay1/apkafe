<?php
/**
 * Schema Pro - Setup Wizard
 *
 * @package Schema Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BSF_AIOSRS_Pro_Setup_Wizard' ) ) :

	/**
	 * BSF_AIOSRS_Pro_Setup_Wizard class.
	 */
	class BSF_AIOSRS_Pro_Setup_Wizard {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {

			if ( apply_filters( 'wp_schema_pro_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {

				if ( get_transient( 'wp-schema-pro-activated' ) ) {
					delete_transient( 'wp-schema-pro-activated' );
				}
				add_action( 'admin_menu', array( $this, 'admin_menus' ) );
				add_action( 'admin_init', array( $this, 'setup_wizard' ) );

			}
		}

		/**
		 * Add admin menus/screens.
		 */
		public function admin_menus() {
			add_dashboard_page( '', '', 'manage_options', 'aiosrs-pro-setup-wizard', '' );
		}

		/**
		 * Show the setup wizard.
		 */
		public function setup_wizard() {
			if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
				return;
			}
			if ( empty( $_GET['page'] ) || 'aiosrs-pro-setup-wizard' !== $_GET['page'] ) {
				return;
			}

			$this->steps = array(
				'welcome'         => array(
					'name' => __( 'Welcome', 'wp-schema-pro' ),
					'view' => array( $this, 'welcome' ),
				),
				'general-setting' => array(
					'name'    => __( 'General', 'wp-schema-pro' ),
					'view'    => array( $this, 'general_setting' ),
					'handler' => array( $this, 'general_setting_save' ),
				),
				'social-profiles' => array(
					'name'    => __( 'Social Profiles', 'wp-schema-pro' ),
					'view'    => array( $this, 'social_profiles' ),
					'handler' => array( $this, 'social_profiles_save' ),
				),
				'global-schemas'  => array(
					'name'    => __( 'Other Schemas', 'wp-schema-pro' ),
					'view'    => array( $this, 'global_schemas' ),
					'handler' => array( $this, 'global_schemas_save' ),
				),
				'success'         => array(
					'name'    => __( 'Success', 'wp-schema-pro' ),
					'view'    => array( $this, 'success' ),
					'handler' => '',
				),
			);

			$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

			wp_enqueue_style( 'aiosrs-pro-setup-wizard', BSF_AIOSRS_PRO_URI . 'admin/assets/css/setup-wizard.css', array( 'dashicons', 'install' ), BSF_AIOSRS_PRO_VER );
			wp_enqueue_style( 'aiosrs-pro-admin-edit-style', BSF_AIOSRS_PRO_URI . 'admin/assets/css/style.css', BSF_AIOSRS_PRO_VER, 'false' );
			wp_enqueue_style( 'aiosrs-pro-admin-settings-style', BSF_AIOSRS_PRO_URI . 'admin/assets/css/settings-style.css', BSF_AIOSRS_PRO_VER, 'false' );
			wp_enqueue_media();
			wp_enqueue_script( 'media' );
			wp_register_script( 'aiosrs-pro-settings-script', BSF_AIOSRS_PRO_URI . 'admin/assets/js/settings-script.js', array( 'jquery' ), BSF_AIOSRS_PRO_VER, true );
			wp_register_script( 'aiosrs-pro-setup-wizard', BSF_AIOSRS_PRO_URI . 'admin/assets/js/setup-wizard.js', array( 'jquery' ), BSF_AIOSRS_PRO_VER, true );
			if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'aiosrs-pro-setup-wizard' ) ) {
				if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
					call_user_func( $this->steps[ $this->step ]['handler'] );
				}
			}

			ob_start();
			$this->setup_wizard_header();
			$this->setup_wizard_steps();
			$this->setup_wizard_content();
			$this->setup_wizard_footer();
			exit;
		}

		/**
		 * Get previous step link
		 */
		public function get_prev_step_link() {
			$keys = array_keys( $this->steps );
			return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ), true ) - 1 ] );
		}

		/**
		 * Get next step link
		 */
		public function get_next_step_link() {
			$keys = array_keys( $this->steps );
			return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ), true ) + 1 ] );
		}

		/**
		 * Setup Wizard Header.
		 */
		public function setup_wizard_header() {
			?>
			<!DOCTYPE html>
			<html>
			<head>
				<meta name="viewport" content="width=device-width" />
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title><?php esc_html_e( 'Schema Setup', 'wp-schema-pro' ); ?></title>
				<script type="text/javascript">
					addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
					var ajaxurl = '<?php echo esc_url( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
				</script>
				<?php wp_print_scripts( array( 'aiosrs-pro-admin-edit-script', 'aiosrs-pro-settings-script', 'aiosrs-pro-setup-wizard' ) ); ?>
				<?php do_action( 'admin_print_styles' ); ?>
				<?php do_action( 'admin_print_scripts' ); ?>
				<?php do_action( 'admin_head' ); ?>
			</head>
			<body class="aiosrs-pro-setup-wizard wp-core-ui">
				<div id="aiosrs-pro-logo">
					<?php $brand_adv = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings']; ?>			
					<?php
					if ( ( '1' === $brand_adv['sp_hide_label'] ) || true === ( defined( 'WP_SP_WL' ) && WP_SP_WL ) ) {
						?>
						<img src="<?php echo esc_url( BSF_AIOSRS_PRO_URI . '/admin/assets/images/schema-pro60x60.png' ); ?>" alt="<?php esc_html_e( 'Schema Pro', 'wp-schema-pro' ); ?>" >
					<?php } else { ?>
						<a href="https://wpschema.com/"><img src="<?php echo esc_url( BSF_AIOSRS_PRO_URI . '/admin/assets/images/schema-pro60x60.png' ); ?>" alt="<?php esc_html_e( 'Schema Pro', 'wp-schema-pro' ); ?>" ></a>
				<?php } ?>	
				</div>
			<?php
		}

		/**
		 * Setup Wizard Footer.
		 */
		public function setup_wizard_footer() {

				$admin_url = BSF_AIOSRS_Pro_Admin::get_page_url( 'settings' );
			?>
				<div class="close-button-wrapper">
					<a href="<?php echo esc_url( $admin_url ); ?>" class="wizard-close-link" ><?php esc_html_e( 'Exit Setup Wizard', 'wp-schema-pro' ); ?></a>
				</div>
				</body>
				<?php do_action( 'admin_footer' ); ?>
				<?php do_action( 'admin_print_footer_scripts' ); ?>
			</html>
			<?php
		}

		/**
		 * Output the steps.
		 */
		public function setup_wizard_steps() {

			$ouput_steps = $this->steps;
			?>
			<ol class="aiosrs-pro-setup-wizard-steps">
				<?php
				foreach ( $ouput_steps as $step_key => $step ) :
					$classes   = '';
					$activated = false;
					if ( $step_key === $this->step ) {
						$classes   = 'active';
						$activated = true;
					} elseif ( array_search( $this->step, array_keys( $this->steps ), true ) > array_search( $step_key, array_keys( $this->steps ), true ) ) {
						$classes   = 'done';
						$activated = true;
					}
					$url = add_query_arg( 'step', $step_key )
					?>
					<li class="<?php echo esc_attr( $classes ); ?>">
						<a href="<?php echo esc_url( $url ); ?>"><span><?php echo esc_html( $step['name'] ); ?></span></a>
					</li>
				<?php endforeach; ?>
			</ol>
			<?php
		}

		/**
		 * Output the content for the current step.
		 */
		public function setup_wizard_content() {
			echo '<div class="aiosrs-pro-setup-wizard-content ' . esc_attr( $this->step ) . '-content-wrap">';
			call_user_func( $this->steps[ $this->step ]['view'] );
			echo '</div>';
		}

		/**
		 * Welcome.
		 */
		public function welcome() {
			if ( is_multisite() ) {
				$branding_msg = get_site_option( 'wp-schema-pro-branding-settings' );
			} else {
				$branding_msg = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
			}
			if ( '' !== $branding_msg['sp_plugin_name'] ) {
					/* translators: %s: search term */
					$brand_msg = sprintf( __( 'Thank you for choosing %s!', 'wp-schema-pro' ), $branding_msg['sp_plugin_name'] );
					/* translators: %s: search term */
					$brand_steup = sprintf( __( '%s adds JSON-LD markups across your website and on specific pages that improves SEO.', 'wp-schema-pro' ), $branding_msg['sp_plugin_name'] );
				?>
				<h1><?php echo esc_html( $brand_msg ); ?></h1>
				<p class="success"><?php echo esc_html( $brand_steup ); ?></p>
				<?php
			} else {
				?>
				<h1><?php esc_html_e( 'Thank you for choosing Schema Pro!', 'wp-schema-pro' ); ?></h1>
				<p class="success">
					<?php esc_html_e( 'Schema Pro adds JSON-LD markups across your website and on specific pages that improves SEO.', 'wp-schema-pro' ); ?>
				</p>
				<?php
			}
			?>
			<p class="success no-margin">
				<?php esc_html_e( 'Here is what we\'re going to do in next few steps:', 'wp-schema-pro' ); ?>
			</p>
			<ul>
				<li><?php esc_html_e( 'Implement Schema for entire website. Ex: Breadcrumb, SiteLinks, Social Profiles, etc.', 'wp-schema-pro' ); ?></li>
				<li><?php esc_html_e( 'Setup specific Schema for individual pages. Ex: Services, Products, Reviews, Recipes, etc.', 'wp-schema-pro' ); ?></li>
			</ul>
			<p class="success">
				<?php esc_html_e( 'Now, let\'s proceed with implementing Schema for entire website.', 'wp-schema-pro' ); ?>
			</p>
			<p class="aiosrs-pro-setup-wizard-actions step">
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next" ><?php esc_html_e( 'Start', 'wp-schema-pro' ); ?> &raquo;</a>
				<?php wp_nonce_field( 'aiosrs-pro-setup-wizard' ); ?>
			</p>
			<?php
		}

		/**
		 * General Setting
		 */
		public function general_setting() {

			$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-general-settings'];
			?>

			<h1><?php esc_html_e( 'General Settings', 'wp-schema-pro' ); ?></h1>
			<p class="success"><?php esc_html_e( 'This will be used in Google\'s Knowledge Graph Card. You can mention who your site represents, your company/person details.', 'wp-schema-pro' ); ?></p>
			<form method="post">
				<table class="form-table">
					<tr class="wp-schema-pro-site-logo-wrap">
						<th><?php esc_html_e( 'Company Logo', 'wp-schema-pro' ); ?></th>
						<td>
							<select name="wp-schema-pro-general-settings[site-logo]" class="wp-schema-pro-custom-option-select">
								<option  <?php selected( $settings['site-logo'], 'custom' ); ?> value="custom"><?php esc_html_e( 'Add Custom Logo', 'wp-schema-pro' ); ?></option>
								<option  <?php selected( $settings['site-logo'], 'customizer-logo' ); ?> value="customizer-logo"><?php esc_html_e( 'Use Logo From Customizer', 'wp-schema-pro' ); ?></option>
							</select>
							<div class="custom-field-wrapper site-logo-custom-wrap" <?php echo ( 'custom' !== $settings['site-logo'] ) ? 'style="display: none;"' : ''; ?> >
								<input type="hidden" class="single-image-field" name="wp-schema-pro-general-settings[site-logo-custom]" value="<?php echo esc_attr( $settings['site-logo-custom'] ); ?>" />
								<?php
								if ( ! empty( $settings['site-logo-custom'] ) ) {
									$image_url = wp_get_attachment_url( $settings['site-logo-custom'] );
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
						<th><?php esc_html_e( 'This Website Represent a', 'wp-schema-pro' ); ?></th>
						<td>
							<select name="wp-schema-pro-general-settings[site-represent]">
								<option <?php selected( $settings['site-represent'], '' ); ?> value=""> <?php esc_html_e( '--None--', 'wp-schema-pro' ); ?></option>
								<option <?php selected( $settings['site-represent'], 'organization' ); ?> value="organization"> <?php esc_html_e( 'Company', 'wp-schema-pro' ); ?></option>
								<option <?php selected( $settings['site-represent'], 'person' ); ?> value="person"> <?php esc_html_e( 'Person', 'wp-schema-pro' ); ?></option>
							</select>
						</td>
					</tr>
					<tr class="wp-schema-pro-person-name-wrap" <?php echo ( 'person' !== $settings['site-represent'] ) ? 'style="display: none;"' : ''; ?>>
						<th><?php esc_html_e( 'Person Name', 'wp-schema-pro' ); ?></th>
						<td>
							<input type="text" name="wp-schema-pro-general-settings[person-name]" value="<?php echo esc_attr( $settings['person-name'] ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
						</td>
					</tr>
					<tr class="wp-schema-pro-site-name-wrap" <?php echo ( 'organization' !== $settings['site-represent'] ) ? 'style="display: none;"' : ''; ?>>
						<th><?php esc_html_e( 'Company Name', 'wp-schema-pro' ); ?></th>
						<td>
							<input type="text" name="wp-schema-pro-general-settings[site-name]" value="<?php echo esc_attr( $settings['site-name'] ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
						</td>
					</tr>
					<tr class="wp-schema-pro-site-name-wrap" <?php echo ( 'organization' !== $settings['site-represent'] ) ? 'style="display: none;"' : ''; ?>>
											<th><?php esc_html_e( 'Organization Schema Type', 'wp-schema-pro' ); ?></th>
											<td>
											<select name="wp-schema-pro-general-settings[organization]">
													<option <?php selected( $settings['organization'], 'organization' ); ?> value="organization"> <?php esc_html_e( 'General', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['organization'], 'Corporation' ); ?> value="Corporation"> <?php esc_html_e( 'Corporation', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['organization'], 'Airline' ); ?> value="Airline"> <?php esc_html_e( 'Airline', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['organization'], 'EducationalOrganization' ); ?> value="EducationalOrganization"> <?php esc_html_e( 'EducationalOrganization', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['organization'], 'GovernmentOrganization' ); ?> value="GovernmentOrganization"> <?php esc_html_e( 'GovernmentOrganization', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['organization'], 'MedicalOrganization' ); ?> value="MedicalOrganization"> <?php esc_html_e( 'MedicalOrganization', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['organization'], 'NGO' ); ?> value="NGO"> <?php esc_html_e( 'NGO', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['organization'], 'PerformingGroup' ); ?> value="PerformingGroup"> <?php esc_html_e( 'PerformingGroup', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['organization'], 'SportsOrganization' ); ?> value="SportsOrganization"> <?php esc_html_e( 'SportsOrganization', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['organization'], 'Consortium' ); ?> value="Consortium"> <?php esc_html_e( 'Consortium', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['organization'], 'LibrarySystem' ); ?> value="LibrarySystem"> <?php esc_html_e( 'LibrarySystem', 'wp-schema-pro' ); ?>
													</option>
													<option <?php selected( $settings['organization'], 'NewsMediaOrganization' ); ?> value="NewsMediaOrganization"> <?php esc_html_e( 'NewsMediaOrganization', 'wp-schema-pro' ); ?></option>
													<option <?php selected( $settings['organization'], 'WorkersUnion' ); ?> value="WorkersUnion"> <?php esc_html_e( ' WorkersUnion', 'wp-schema-pro' ); ?>
													</option>
												</select>
											</td>
										</tr>
										<tr> 
				</table>
				<p class="aiosrs-pro-setup-wizard-actions step">
					<?php wp_nonce_field( 'aiosrs-pro-setup-wizard' ); ?>
					<input type="submit" class="button-primary button button-large button-next" value="<?php esc_html_e( 'Next', 'wp-schema-pro' ); ?> &raquo;"  name="save_step" />
					<a href="<?php echo esc_url( $this->get_prev_step_link() ); ?>" class="button-primary button button-large button-prev" >&laquo; <?php esc_html_e( 'Previous', 'wp-schema-pro' ); ?></a>
				</p>
			</form>
			<?php
		}

		/**
		 * General Setting Save.
		 */
		public function general_setting_save() {
			check_admin_referer( 'aiosrs-pro-setup-wizard' );

			if ( isset( $_POST['wp-schema-pro-general-settings'] ) ) {
				update_option( 'wp-schema-pro-general-settings', $_POST['wp-schema-pro-general-settings'] );
			}

			$redirect_url = $this->get_next_step_link();
			wp_safe_redirect( esc_url_raw( $redirect_url ) );
			exit;
		}

		/**
		 * Social Profiles
		 */
		public function social_profiles() {

			$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-social-profiles'];
			if ( is_multisite() ) {
				$branding_msg = get_site_option( 'wp-schema-pro-branding-settings' );
			} else {
				$branding_msg = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
			}
			?>
			<h1><?php esc_html_e( 'Social Profiles', 'wp-schema-pro' ); ?></h1>
			<?php
			if ( '' !== $branding_msg['sp_plugin_name'] ) {
				/* translators: %s: search term */
				$brand_social_name = sprintf( __( 'You can add your social profile links here. This will help %s tell search engines a little more about you and your social presence.', 'wp-schema-pro' ), $branding_msg['sp_plugin_name'] );
				?>
					<p><?php echo esc_html( $brand_social_name ); ?></p>
								<?php
			} else {
				?>
			<p class="success"><?php esc_html_e( 'You can add your social profile links here. This will help Schema Pro tell search engines a little more about you and your social presence.', 'wp-schema-pro' ); ?></p><?php } ?>
			<form method="post">
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Facebook', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[facebook]"  value="<?php echo esc_attr( $settings['facebook'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Twitter', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[twitter]"  value="<?php echo esc_attr( $settings['twitter'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Google+', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[google-plus]"  value="<?php echo esc_attr( $settings['google-plus'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
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
						<th><?php esc_html_e( 'LinkedIn', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[linkedin]"  value="<?php echo esc_attr( $settings['linkedin'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Pinterest', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[pinterest]"  value="<?php echo esc_attr( $settings['pinterest'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'SoundCloud', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[soundcloud]"  value="<?php echo esc_attr( $settings['soundcloud'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Tumblr', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[tumblr]"  value="<?php echo esc_attr( $settings['tumblr'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
				</table>
				<p class="aiosrs-pro-setup-wizard-actions step">
					<?php wp_nonce_field( 'aiosrs-pro-setup-wizard' ); ?>
					<input type="submit" class="button-primary button button-large button-next" value="<?php esc_html_e( 'Next', 'wp-schema-pro' ); ?> &raquo;"  name="save_step" />
					<a href="<?php echo esc_url( $this->get_prev_step_link() ); ?>" class="button-primary button button-large button-prev" >&laquo; <?php esc_html_e( 'Previous', 'wp-schema-pro' ); ?></a>
				</p>
			</form>
			<?php
		}

		/**
		 * Social Profiles Save.
		 */
		public function social_profiles_save() {
			check_admin_referer( 'aiosrs-pro-setup-wizard' );

			if ( isset( $_POST['wp-schema-pro-social-profiles'] ) ) {
				update_option( 'wp-schema-pro-social-profiles', $_POST['wp-schema-pro-social-profiles'] );
			}

			$redirect_url = $this->get_next_step_link();
			wp_safe_redirect( esc_url_raw( $redirect_url ) );
			exit;
		}

		/**
		 * Global Schemas
		 */
		public function global_schemas() {

			$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-global-schemas'];
			?>
			<h1><?php esc_html_e( 'Other Schemas', 'wp-schema-pro' ); ?></h1>
			<p class="success"><?php esc_html_e( 'Apply some other global schemas for your site.', 'wp-schema-pro' ); ?></p>
			<form method="post">
				<table class="form-table">
					<tr>
						<th>
							<?php esc_html_e( 'About Page Schema', 'wp-schema-pro' ); ?>
							<?php
								$message = __( 'Select your about page from the dropdown list.This will add AboutPage schema.', 'wp-schema-pro' );
								BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
							?>
						</th>
						<td>
							<select name="wp-schema-pro-global-schemas[about-page]">
								<option value=""><?php esc_html_e( '--None--', 'wp-schema-pro' ); ?></option>
								<?php foreach ( BSF_AIOSRS_Pro_Admin::$pages as $page_id => $page_title ) { ?>
									<option <?php selected( $page_id, $settings['about-page'] ); ?> value="<?php echo esc_attr( $page_id ); ?>"><?php echo esc_html( $page_title ); ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th>
							<?php esc_html_e( 'Contact Page Schema', 'wp-schema-pro' ); ?>
							<?php
								$message = __( 'Select your contact page from the dropdown list. This will add ContactPage schema.', 'wp-schema-pro' );
								BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
							?>
						</th>
						<td>
							<select name="wp-schema-pro-global-schemas[contact-page]">
								<option value=""><?php esc_html_e( '--None--', 'wp-schema-pro' ); ?></option>
								<?php foreach ( BSF_AIOSRS_Pro_Admin::$pages as $page_id => $page_title ) { ?>
									<option <?php selected( $page_id, $settings['contact-page'] ); ?> value="<?php echo esc_attr( $page_id ); ?>"><?php echo esc_html( $page_title ); ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th class="tooltip-with-image-wrapper">
							<?php esc_html_e( 'Select Menu for SiteLinks Schema', 'wp-schema-pro' ); ?>
							<?php
								$message  = __( 'This helps Google understand the most important pages on your website and can generate Rich Snippet as below.', 'wp-schema-pro' );
								$message .= '<br /><img class="tooltip-image" src="' . esc_url( BSF_AIOSRS_PRO_URI . '/admin/assets/images/sitelinks.jpg' ) . '" />';
								BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
							?>
						</th>
						<td>
							<?php $nav_menus = wp_get_nav_menus(); ?>
							<select name="wp-schema-pro-global-schemas[site-navigation-element]" >
								<option <?php selected( '', $settings['site-navigation-element'] ); ?> value=""><?php esc_html_e( '--None--', 'wp-schema-pro' ); ?></option>
								<?php foreach ( $nav_menus as $menu ) { ?>
									<option <?php selected( $menu->term_id, $settings['site-navigation-element'] ); ?> value="<?php echo esc_attr( $menu->term_id ); ?>"><?php echo esc_html( $menu->name ); ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th class="tooltip-with-image-wrapper">
							<?php esc_html_e( 'Enable Breadcrumb Schema?', 'wp-schema-pro' ); ?>
							<?php
								$message  = __( 'If enabled, Google can Breadcrumb for your website Search results.', 'wp-schema-pro' );
								$message .= '<br /><img class="tooltip-image" src="' . esc_url( BSF_AIOSRS_PRO_URI . '/admin/assets/images/breadcrumbs.jpg' ) . '" />';
								BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
							?>
						</th>
						<td>
							<label>
								<input type="hidden" name="wp-schema-pro-global-schemas[breadcrumb]" value="disabled" />
								<input type="checkbox" name="wp-schema-pro-global-schemas[breadcrumb]" <?php checked( '1', $settings ['breadcrumb'] ); ?> value="1" /> <?php esc_html_e( 'Yes', 'wp-schema-pro' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th class="tooltip-with-image-wrapper">
							<?php esc_html_e( 'Enable Sitelinks Search Box?', 'wp-schema-pro' ); ?>
							<?php
								$message  = __( 'If enabled, Google can display a search box with your Search results.', 'wp-schema-pro' );
								$message .= '<br /><img class="tooltip-image" src="' . esc_url( BSF_AIOSRS_PRO_URI . '/admin/assets/images/sitelink-search.jpg' ) . '" />';
								BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
							?>
						</th>
						<td>
							<label>
								<input type="hidden" name="wp-schema-pro-global-schemas[sitelink-search-box]" value="disabled" />
								<input type="checkbox" name="wp-schema-pro-global-schemas[sitelink-search-box]" <?php checked( '1', $settings['sitelink-search-box'] ); ?> value="1" /> <?php esc_html_e( 'Yes', 'wp-schema-pro' ); ?>
							</label>
						</td>
					</tr>
				</table>
				<p class="aiosrs-pro-setup-wizard-actions step">
					<?php wp_nonce_field( 'aiosrs-pro-setup-wizard' ); ?>
					<input type="submit" class="button-primary button button-large button-next" value="<?php esc_html_e( 'Next', 'wp-schema-pro' ); ?> &raquo;"  name="save_step" />
					<a href="<?php echo esc_url( $this->get_prev_step_link() ); ?>" class="button-primary button button-large button-prev" >&laquo; <?php esc_html_e( 'Previous', 'wp-schema-pro' ); ?></a>
				</p>
			</form>
			<?php
		}

		/**
		 * Global Schemas Save.
		 */
		public function global_schemas_save() {
			check_admin_referer( 'aiosrs-pro-setup-wizard' );

			if ( isset( $_POST['wp-schema-pro-global-schemas'] ) ) {
				update_option( 'wp-schema-pro-global-schemas', $_POST['wp-schema-pro-global-schemas'] );
			}

			$redirect_url = $this->get_next_step_link();
			wp_safe_redirect( esc_url_raw( $redirect_url ) );
			exit;
		}

		/**
		 * Final step.
		 */
		public function success() {

			?>
			<h1><?php esc_html_e( 'Congratulations!', 'wp-schema-pro' ); ?></h1>

			<div class="aiosrs-pro-setup-wizard-next-steps">
				<div class="aiosrs-pro-setup-wizard-next-steps-last">

					<p class="success">
						<?php esc_html_e( 'You\'ve successfully completed the one-time setup before you begin setting schema markups for individual pages.', 'wp-schema-pro' ); ?>
					</p>

					<p class="success">
						<?php
						$brand_adv = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
						if ( ( '1' === $brand_adv['sp_hide_label'] ) || true === ( defined( 'WP_SP_WL' ) && WP_SP_WL ) ) {
							esc_html_e( 'A Knowledge Base Articles', 'wp-schema-pro' );
						} else {
							printf(
								/* translators: 1. anchor opening, 2. anchor closing*/
								esc_html__( 'A Knowledge Base Articles %1$shere%2$s.', 'wp-schema-pro' ),
								'<a href="https://wpschema.com/docs" target="_blank" rel="noopener">',
								'</a>'
							);
						}
						?>
					</p>

					<p class="success">
						<?php
						printf(
							/* translators: 1. anchor opening, 2. anchor closing*/
							esc_html__( 'You can change these settings from %1$shere%2$s.', 'wp-schema-pro' ),
							'<a href="' . esc_url( BSF_AIOSRS_Pro_Admin::get_page_url( 'settings' ) ) . '" >',
							'</a>'
						);
						?>
					</p>

					<hr />

					<table class="form-table">
						<tr>
							<td>
								<a href="<?php echo esc_url( admin_url( 'index.php?page=aiosrs-pro-setup' ) ); ?>" type="button" class="button button-primary button-hero" ><?php esc_html_e( 'Create First Schema', 'wp-schema-pro' ); ?></a>
							</td>
						</tr>
					</table>

				</div>
			</div>
			<?php
		}
	}

	new BSF_AIOSRS_Pro_Setup_Wizard();

endif;

?>
