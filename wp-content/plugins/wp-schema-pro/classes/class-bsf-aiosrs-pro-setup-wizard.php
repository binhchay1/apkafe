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
	

		public $steps = array();
		public $step;

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
			if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( sanitize_text_field( $_REQUEST['wp_schema_pro_admin_page_nonce'] ), 'wp_schema_pro_admin_page' ) ) {
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
					'name'    => __( 'Done', 'wp-schema-pro' ),
					'view'    => array( $this, 'success' ),
					'handler' => '',
				),
			);

			$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

			wp_enqueue_style( 'aiosrs-pro-setup-wizard', BSF_AIOSRS_PRO_URI . 'admin/assets/' . BSF_AIOSRS_Pro_Admin::$minfy_css . 'setup-wizard.' . BSF_AIOSRS_Pro_Admin::$minfy_css_ext, array( 'dashicons', 'install' ), BSF_AIOSRS_PRO_VER );
			wp_enqueue_style( 'aiosrs-pro-admin-edit-style', BSF_AIOSRS_PRO_URI . 'admin/assets/' . BSF_AIOSRS_Pro_Admin::$minfy_css . 'style.' . BSF_AIOSRS_Pro_Admin::$minfy_css_ext, BSF_AIOSRS_PRO_VER, 'false' );
			wp_enqueue_style( 'aiosrs-pro-admin-settings-style', BSF_AIOSRS_PRO_URI . 'admin/assets/' . BSF_AIOSRS_Pro_Admin::$minfy_css . 'settings-style.' . BSF_AIOSRS_Pro_Admin::$minfy_css_ext, BSF_AIOSRS_PRO_VER, 'false' );
			wp_register_script( 'aiosrs-pro-settings-script', BSF_AIOSRS_PRO_URI . 'admin/assets/' . BSF_AIOSRS_Pro_Admin::$minfy_js . 'settings-script.' . BSF_AIOSRS_Pro_Admin::$minfy_js_ext, array( 'jquery', 'bsf-target-rule-select2', 'wp-i18n' ), BSF_AIOSRS_PRO_VER, true );
			wp_enqueue_media();
			wp_enqueue_script( 'media' );
			wp_enqueue_style( 'bsf-target-rule-select2', BSF_AIOSRS_PRO_URI . 'classes/lib/target-rule/select2.css', '', BSF_AIOSRS_PRO_VER, false );
			wp_register_script( 'bsf-target-rule-select2', BSF_AIOSRS_PRO_URI . 'classes/lib/target-rule/select2.js', array( 'jquery', 'backbone', 'wp-util' ), BSF_AIOSRS_PRO_VER, true );
			wp_localize_script(
				'aiosrs-pro-settings-script',
				'AIOSRS_search',
				apply_filters(
					'aiosrs_pro_settings_script_localize',
					array(
						'search_field' => wp_create_nonce( 'spec_schema' ),
					)
				)
			);
			wp_register_script( 'aiosrs-pro-setup-wizard', BSF_AIOSRS_PRO_URI . 'admin/assets/' . BSF_AIOSRS_Pro_Admin::$minfy_js . 'setup-wizard.' . BSF_AIOSRS_Pro_Admin::$minfy_js_ext, array( 'jquery' ), BSF_AIOSRS_PRO_VER, true );
			if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['_wpnonce'] ), 'aiosrs-pro-setup-wizard' ) && ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
				call_user_func( $this->steps[ $this->step ]['handler'] );
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
		public function setup_wizard_header() {             ?>
			<!DOCTYPE html>
			<html lang="en">

			<head>
				<meta name="viewport" content="width=device-width" />
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title><?php esc_html_e( 'Schema Setup', 'wp-schema-pro' ); ?></title>
				<script type="text/javascript">
					addLoadEvent = function(func) {
						if (typeof jQuery != "undefined") jQuery(document).ready(func);
						else if (typeof wpOnload != 'function') {
							wpOnload = func;
						} else {
							var oldonload = wpOnload;
							wpOnload = function() {
								oldonload();
								func();
							}
						}
					};
					var ajaxurl = '<?php echo esc_url( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
				</script>
				<?php wp_print_scripts( array( 'aiosrs-pro-admin-edit-script', 'aiosrs-pro-settings-script', 'aiosrs-pro-setup-wizard' ) ); ?>
				<?php do_action( 'admin_print_styles' ); ?>
				<?php do_action( 'admin_print_scripts' ); ?>
			</head>

			<body class="aiosrs-pro-setup-wizard wp-core-ui">
				<div id="aiosrs-pro-logo">
					<?php
					$brand_adv = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
					if ( '' !== $brand_adv['sp_plugin_name'] ) {
						?>
						<h2 class="wpsp-setup-pro-title"><?php echo esc_html( $brand_adv['sp_plugin_name'] ); ?></h2>
					<?php } else { ?>
						<a href="https://wpschema.com/" target="_blank"><img src="<?php echo esc_url( BSF_AIOSRS_PRO_URI . '/admin/assets/images/schema-pro.png' ); ?>" alt="<?php esc_attr_e( 'Schema Pro', 'wp-schema-pro' ); ?>"></a>
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
					<a href="<?php echo esc_url( $admin_url ); ?>" class="wizard-close-link"><?php esc_html_e( 'Exit Setup Wizard', 'wp-schema-pro' ); ?></a>
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
					$classes = '';
					if ( $step_key === $this->step ) {
						$classes = 'active';
					} elseif ( array_search( $this->step, array_keys( $this->steps ), true ) > array_search( $step_key, array_keys( $this->steps ), true ) ) {
						$classes = 'done';
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
			$sp_name = isset( $branding_msg['sp_plugin_name'] ) ? $branding_msg['sp_plugin_name'] : '';
			if ( '' !== $sp_name ) {
				/* translators: %s: search term */
				$brand_msg = sprintf( __( 'Welcome to %s setup wizard !', 'wp-schema-pro' ), $sp_name );
				/* translators: %s: search term */
				$brand_steup = sprintf( __( '%s adds Google-recommended schema markups across your site and specific pages. This improves your SEO and allows search engines to display rich snippets for your website.', 'wp-schema-pro' ), $sp_name );
				?>
				<h1><?php echo esc_html( $brand_msg ); ?></h1>
				<p class="success"><?php echo esc_html( $brand_steup ); ?></p>
				<?php
			} else {
				?>
				<h1><?php esc_html_e( 'Welcome To Schema Pro Setup Wizard!', 'wp-schema-pro' ); ?></h1>
				<p class="success">
				<?php esc_html_e( 'Schema Pro adds Google-recommended schema markups across your site and specific pages. This improves your SEO and allows search engines to display rich snippets for your website.', 'wp-schema-pro' ); ?>
				</p>
				<?php
			}
			?>
			<p class="success">
				<?php esc_html_e( 'This wizard is the first step to making Google understand your site better. Just fill in the required basic information. It’s easy!', 'wp-schema-pro' ); ?>
			</p>
			<?php if ( '' === $sp_name ) { ?>
				<p class="success">
					<?php echo sprintf( wp_kses_post( '<a href="https://www.youtube.com/watch?v=xOiMA0am9QY" target="_blank">Watch a detailed video on the setup wizard tutorial.</a>', 'wp-schema-pro' ) ); ?>
				</p>
			<?php } ?>
			<p class="aiosrs-pro-setup-wizard-actions step">
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next"><?php esc_html_e( 'Start', 'wp-schema-pro' ); ?> &raquo;</a>
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

			<h1><?php esc_html_e( 'General', 'wp-schema-pro' ); ?></h1>
			<p class="success"><?php esc_html_e( 'To help Google understand what your website is about, select the most suitable type for your website below, and fill in the required basic information.', 'wp-schema-pro' ); ?></p>
			<form method="post">
				<table class="form-table">
					<tr>
						<th id=''><?php esc_html_e( 'This Website Represents ', 'wp-schema-pro' ); ?></th>
						<td>
							<select name="wp-schema-pro-general-settings[site-represent]">
								<option <?php selected( $settings['site-represent'], '' ); ?> value=""> <?php esc_html_e( '--None--', 'wp-schema-pro' ); ?></option>
								<option <?php selected( $settings['site-represent'], 'person' ); ?> value="person"> <?php esc_html_e( 'Personal Website', 'wp-schema-pro' ); ?></option>
								<option <?php selected( $settings['site-represent'], 'Otherbusiness' ); ?> value="Otherbusiness"> <?php esc_html_e( 'Business Website', 'wp-schema-pro' ); ?></option>
								<option <?php selected( $settings['site-represent'], 'organization' ); ?> value="organization"> <?php esc_html_e( 'Organization', 'wp-schema-pro' ); ?></option>
								<option <?php selected( $settings['site-represent'], 'personblog' ); ?> value="personblog"> <?php esc_html_e( 'Personal Blog', 'wp-schema-pro' ); ?></option>
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
						<th id=''><?php esc_html_e( 'Website Logo', 'wp-schema-pro' ); ?></th>
						<td>
							<select style='display:none' name="wp-schema-pro-general-settings[site-logo]" class="wp-schema-pro-custom-option-select">
								<option <?php selected( $settings['site-logo'], 'custom' ); ?> value="custom"><?php esc_html_e( 'Add Custom Logo', 'wp-schema-pro' ); ?></option>
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
	<a href=" #" class="aiosrs-image-select button"><span class="dashicons dashicons-format-image"></span><?php esc_html_e( 'Select Image', 'wp-schema-pro' ); ?></a>
									<a href="#" class="aiosrs-image-remove dashicons dashicons-no-alt wp-ui-text-highlight"></a>
									<?php if ( isset( $image_url ) && ! empty( $image_url ) ) : ?>
										<a href="#" class="aiosrs-image-select img"><img src="<?php echo esc_url( $image_url ); ?>" /></a>
									<?php endif; ?>
								</div>
							</div>
							<p class='aiosrs-pro-field-description'><?php esc_html_e( 'Recommended minimum logo size 112 x 112 pixels.', 'wp-schema-pro' ); ?></p>
							<p class='aiosrs-pro-field-description'><?php esc_html_e( 'The image must be in .jpg, .png, .gif, .svg, or .webp format.', 'wp-schema-pro' ); ?></p>
						</td>
					</tr>
					<tr class="wp-schema-pro-person-name-wrap" <?php echo ( 'person' !== $settings['site-represent'] ) ? 'style="display: none;"' : ''; ?>>
						<th class='wpsp-organization-label'><?php esc_html_e( 'Website Owner Name', 'wp-schema-pro' ); ?></th>
						<td>
							<input type="text" name="wp-schema-pro-general-settings[person-name]" value="<?php echo esc_attr( $settings['person-name'] ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
						</td>
					</tr>
					<tr class="wp-schema-pro-site-name-wrap" <?php echo ( 'organization' !== $settings['site-represent'] ) ? 'style="display: none;"' : ''; ?>>
						<th class='wpsp-organization-label'><?php esc_html_e( 'Organization Name', 'wp-schema-pro' ); ?></th>
						<td>
							<input type="text" name="wp-schema-pro-general-settings[site-name]" value="<?php echo esc_attr( $settings['site-name'] ); ?>" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
						</td>
					</tr>
					<tr class="wp-schema-pro-site-name-wrap " <?php echo ( 'organization' !== $settings['site-represent'] ) ? 'style="display: none;"' : ''; ?>>
						<th>
							<?php
							esc_html_e(
								'Organization Type',
								'wp-schema-pro'
							);
							?>
						</th>
						<td>
							<?php
							$option_list = BSF_AIOSRS_Pro_Schema::get_dropdown_options( 'Organization-type' );
							?>
							<select class="wpsp-setup-configuration-settings" name="wp-schema-pro-general-settings[organization]">
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
				</table>
				<p class="aiosrs-pro-setup-wizard-actions step">
					<?php wp_nonce_field( 'aiosrs-pro-setup-wizard' ); ?>
					<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Next', 'wp-schema-pro' ); ?> &raquo;" name="save_step" />
					<a href="<?php echo esc_url( $this->get_prev_step_link() ); ?>" class="button-primary button button-large button-prev">&laquo; <?php esc_html_e( 'Previous', 'wp-schema-pro' ); ?></a>
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
				update_option( 'wp-schema-pro-general-settings', array_map( 'sanitize_text_field', $_POST['wp-schema-pro-general-settings'] ) );
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
			?>
			<h1><?php esc_html_e( 'Social Profiles', 'wp-schema-pro' ); ?></h1>
			<p class="success"><?php esc_html_e( 'Please enter all your possible social media profiles. These links can appear in the knowledge panel of the search results for your website.', 'wp-schema-pro' ); ?></p>
			<form method="post">
				<table class="form-table">
					<tr>
						<th id=''><?php esc_html_e( 'Facebook', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[facebook]" value="<?php echo esc_attr( $settings['facebook'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<tr>
						<th id=''><?php esc_html_e( 'Instagram', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[instagram]" value="<?php echo esc_attr( $settings['instagram'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<tr>
						<th id=''><?php esc_html_e( 'YouTube', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[youtube]" value="<?php echo esc_attr( $settings['youtube'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<tr>
						<th id=''><?php esc_html_e( 'Twitter', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[twitter]" value="<?php echo esc_attr( $settings['twitter'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<tr>
						<th id=''><?php esc_html_e( 'Pinterest', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[pinterest]" value="<?php echo esc_attr( $settings['pinterest'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<tr>
						<th id=''><?php esc_html_e( 'LinkedIn', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[linkedin]" value="<?php echo esc_attr( $settings['linkedin'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<tr>
						<th id=''><?php esc_html_e( 'SoundCloud', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[soundcloud]" value="<?php echo esc_attr( $settings['soundcloud'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<tr>
						<th id=''><?php esc_html_e( 'Tumblr', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[tumblr]" value="<?php echo esc_attr( $settings['tumblr'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<tr>
						<th id=''><?php esc_html_e( 'Wikipedia', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[wikipedia]" value="<?php echo esc_attr( $settings['wikipedia'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<tr>
						<th id=''><?php esc_html_e( 'MySpace', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[myspace]" value="<?php echo esc_attr( $settings['myspace'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<tr style="display:none">
						<th id=''><?php esc_html_e( 'Google+', 'wp-schema-pro' ); ?></th>
						<td><input type="url" name="wp-schema-pro-social-profiles[google-plus]" value="<?php echo esc_attr( $settings['google-plus'] ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /></td>
					</tr>
					<?php
					if ( isset( $settings ) && ! empty( $settings ) && is_array( $settings['other'] ) ) {
						foreach ( $settings['other'] as $sub_social_profiles => $value ) {
							if ( isset( $value ) && ! empty( $value ) ) {
								?>
							<tr style="display:none">
								<th id='' class="wpsp-other-th"><?php esc_html_e( 'Other', 'wp-schema-pro' ); ?></th>
								<td><input type="url" class="wpsp-other" name="wp-schema-pro-social-profiles[other][<?php echo esc_attr( $sub_social_profiles ); ?>]" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /><span class="wpsp-field-close remove-row dashicons dashicons-dismiss "><a href="#" class=""></a></span></td>
							</tr>
									<?php
							}
						}
					}
					?>
					<tr style="display:none" class="empty-row screen-reader-text"> <!-- empty hidden one for jQuery -->
						<th id='' class="wpsp-other-th"><?php esc_html_e( 'Other', 'wp-schema-pro' ); ?></th>
						<td><input type="url" class="wpsp-other" name="wp-schema-pro-social-profiles[other][]" value="" placeholder="<?php echo esc_attr( 'Enter URL' ); ?>" /><span class="wpsp-field-close remove-row dashicons dashicons-dismiss "><a href="#" class="remove-row"></a></span></td>
					</tr>
				</table>
				<p class="aiosrs-pro-setup-wizard-actions step">
					<?php wp_nonce_field( 'aiosrs-pro-setup-wizard' ); ?>
					<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Next', 'wp-schema-pro' ); ?> &raquo;" name="save_step" />
					<a href="<?php echo esc_url( $this->get_prev_step_link() ); ?>" class="button-primary button button-large button-prev">&laquo; <?php esc_html_e( 'Previous', 'wp-schema-pro' ); ?></a>
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
				update_option( 'wp-schema-pro-social-profiles', array_map( 'sanitize_text_field', $_POST['wp-schema-pro-social-profiles'] ) );
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
			<p class="success"><?php esc_html_e( 'Fill in additional information about your website to make sure that search engines fully understand what it’s about. This will help improve your SEO further.', 'wp-schema-pro' ); ?></p>
			<form method="post">
				<table class="form-table">
					<tr>
						<th id=''>
							<?php esc_html_e( 'Select About Page', 'wp-schema-pro' ); ?>
							<?php
							$message = __( 'Select your about page from the dropdown list. This will add AboutPage schema on the selected page.', 'wp-schema-pro' );
							BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
							?>
						</th>
						<td>
							<select class="wp-select2 wpsp-setup-configuration-settings" name="wp-schema-pro-global-schemas[about-page]">
								<option value=""><?php esc_html_e( '--None--', 'wp-schema-pro' ); ?></option>
								<?php foreach ( BSF_AIOSRS_Pro_Admin::$pages as $page_id => $page_title ) { ?>
									<option <?php selected( $page_id, $settings['about-page'] ); ?> value="<?php echo esc_attr( $page_id ); ?>"><?php echo esc_html( $page_title ); ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th id=''>
							<?php esc_html_e( 'Select Contact Page', 'wp-schema-pro' ); ?>
							<?php
							$message = __( 'Select your contact page from the dropdown list. This will add ContactPage schema on the selected page.', 'wp-schema-pro' );
							BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
							?>
						</th>
						<td>
							<select class="wp-select2 wpsp-setup-configuration-settings" name="wp-schema-pro-global-schemas[contact-page]">
								<option value=""><?php esc_html_e( '--None--', 'wp-schema-pro' ); ?></option>
								<?php foreach ( BSF_AIOSRS_Pro_Admin::$pages as $page_id => $page_title ) { ?>
									<option <?php selected( $page_id, $settings['contact-page'] ); ?> value="<?php echo esc_attr( $page_id ); ?>"><?php echo esc_html( $page_title ); ?></option>
								<?php } ?>
							</select>
						</td>
					</tr>
					<tr>
						<th id='' class="tooltip-with-image-wrapper">
							<?php esc_html_e( 'Select Menu for SiteLinks Schema', 'wp-schema-pro' ); ?>
							<?php
							$message = '<img class="tooltip-image" src="' . esc_url( BSF_AIOSRS_PRO_URI . '/admin/assets/images/sitelinks.jpg' ) . '" />';
							BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
							?>
						</th>
						<td>
							<?php $nav_menus = wp_get_nav_menus(); ?>
							<select name="wp-schema-pro-global-schemas[site-navigation-element]">
								<option <?php selected( '', $settings['site-navigation-element'] ); ?> value=""><?php esc_html_e( '--None--', 'wp-schema-pro' ); ?></option>
								<?php foreach ( $nav_menus as $menu ) { ?>
									<option <?php selected( $menu->term_id, $settings['site-navigation-element'] ); ?> value="<?php echo esc_attr( $menu->term_id ); ?>"><?php echo esc_html( $menu->name ); ?></option>
								<?php } ?>
							</select>
							<p class="success aiosrs-pro-field-description"><?php esc_html_e( 'This helps Google understand the most important pages on your website and can generate Rich Snippets.', 'wp-schema-pro' ); ?></p>
						</td>
					</tr>
					<tr>
						<th id='' class="tooltip-with-image-wrapper">
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
							<p class="success aiosrs-pro-field-description"><?php esc_html_e( 'If enabled, Google can display a search box on your search website’s search results.', 'wp-schema-pro' ); ?></p>
						</td>
					</tr>
					<tr>
						<th id='' class="tooltip-with-image-wrapper">
							<?php esc_html_e( 'Enable Breadcrumbs', 'wp-schema-pro' ); ?>
							<?php
							$message = '<img class="tooltip-image" src="' . esc_url( BSF_AIOSRS_PRO_URI . '/admin/assets/images/breadcrumbs.jpg' ) . '" />';
							BSF_AIOSRS_Pro_Admin::get_tooltip( $message );
							?>
						</th>
						<td>
							<label>
								<input type="hidden" name="wp-schema-pro-global-schemas[breadcrumb]" value="disabled" />
								<input type="checkbox" name="wp-schema-pro-global-schemas[breadcrumb]" <?php checked( '1', $settings['breadcrumb'] ); ?> value="1" /> <?php esc_html_e( 'Yes', 'wp-schema-pro' ); ?>
							</label>
							<p class="success aiosrs-pro-field-description"><?php esc_html_e( 'If enabled, Google can add breadcrumbs to your website’s and pages search results.', 'wp-schema-pro' ); ?></p>
						</td>
					</tr>
				</table>
				<p class="aiosrs-pro-setup-wizard-actions step">
					<?php wp_nonce_field( 'aiosrs-pro-setup-wizard' ); ?>
					<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Next', 'wp-schema-pro' ); ?> &raquo;" name="save_step" />
					<a href="<?php echo esc_url( $this->get_prev_step_link() ); ?>" class="button-primary button button-large button-prev">&laquo; <?php esc_html_e( 'Previous', 'wp-schema-pro' ); ?></a>
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
				update_option( 'wp-schema-pro-global-schemas', array_map( 'sanitize_text_field', $_POST['wp-schema-pro-global-schemas'] ) );
				$new_data = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-breadcrumb-setting'];
				if ( isset( $new_data['enable_bread'] ) ) {
					$old_data                 = isset( $_POST['wp-schema-pro-global-schemas']['breadcrumb'] ) ? sanitize_text_field( $_POST['wp-schema-pro-global-schemas']['breadcrumb'] ) : '';
					$new_data['enable_bread'] = $old_data;
					update_option( 'wp-schema-pro-breadcrumb-setting', $new_data );
				}
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
			<h1><?php esc_html_e( 'Well done!', 'wp-schema-pro' ); ?></h1>

			<div class="aiosrs-pro-setup-wizard-next-steps">
				<div class="aiosrs-pro-setup-wizard-next-steps-last">
					<p class="success">
						<?php esc_html_e( 'You\'ve successfully added schema to your website! You’re on your way to get rich snippets for your pages.', 'wp-schema-pro' ); ?>
					</p>
					<p class="success">
						<?php esc_html_e( 'The information you provided is globally applied to the website. You can always go back, change, and add more to this later from your dashboard.', 'wp-schema-pro' ); ?>
					</p>
					<p class="success">
						<?php esc_html_e( 'From the dashboard, you can also explore other advanced features like breadcrumbs and white label services.', 'wp-schema-pro' ); ?>
					</p>
					<p class="success">
						<span class="wpsp-text-strong"><?php esc_html_e( 'Wondering what’s next?', 'wp-schema-pro' ); ?></span>
						<?php esc_html_e( ' The next step would be to create and set schema markups for your individual pages.', 'wp-schema-pro' ); ?>
					</p>
					<p class="success">
						<?php esc_html_e( 'Give it a try now!', 'wp-schema-pro' ); ?>
					</p>

					<table class="form-table">
						<tr>
							<td>
								<a href="<?php echo esc_url( admin_url( 'index.php?page=aiosrs-pro-setup' ) ); ?>" type="button" class="button button-primary button-hero"><?php esc_html_e( 'Create First Schema', 'wp-schema-pro' ); ?></a>
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
