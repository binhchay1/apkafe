<?php
/**
 * Schema Pro - Schema Wizard
 *
 * @package Schema Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Wizard' ) ) :

	/**
	 * BSF_AIOSRS_Pro_Schema_Wizard class.
	 */
	class BSF_AIOSRS_Pro_Schema_Wizard {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			if ( apply_filters( 'wp_schema_pro_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {
				add_action( 'admin_menu', array( $this, 'admin_menus' ) );
				add_action( 'admin_init', array( $this, 'setup_wizard' ) );
			}
		}

		/**
		 * Add admin menus/screens.
		 */
		public function admin_menus() {
			add_dashboard_page( '', '', 'manage_options', 'aiosrs-pro-setup', '' );
		}

		/**
		 * Show the setup wizard.
		 */
		public function setup_wizard() {
			if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
				return;
			}
			if ( empty( $_GET['page'] ) || 'aiosrs-pro-setup' !== $_GET['page'] ) {
				return;
			}
			$this->steps = array(
				'basic-config' => array(
					'name'    => __( 'Choose Schema Type', 'wp-schema-pro' ),
					'view'    => array( $this, 'choose_schema_type' ),
					'handler' => array( $this, 'choose_schema_type_save' ),
				),
				'enable-on'    => array(
					'name'    => __( 'Target Pages', 'wp-schema-pro' ),
					'view'    => array( $this, 'implement_on_callback' ),
					'handler' => array( $this, 'implement_on_callback_save' ),
				),
				'setup-ready'  => array(
					'name'    => __( 'Ready!', 'wp-schema-pro' ),
					'view'    => array( $this, 'schema_ready' ),
					'handler' => '',
				),
			);

			$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

			wp_enqueue_style( 'aiosrs-pro-setup', BSF_AIOSRS_PRO_URI . 'admin/assets/css/setup-wizard.css', array( 'dashicons', 'install' ), BSF_AIOSRS_PRO_VER );
			wp_enqueue_style( 'bsf-target-rule-select2', BSF_AIOSRS_PRO_URI . 'classes/lib/target-rule/select2.css', '', BSF_AIOSRS_PRO_VER, false );
			wp_enqueue_style( 'bsf-target-rule', BSF_AIOSRS_PRO_URI . 'classes/lib/target-rule/target-rule.css', '', BSF_AIOSRS_PRO_VER, false );
			wp_enqueue_style( 'aiosrs-pro-admin-edit-style', BSF_AIOSRS_PRO_URI . 'admin/assets/css/style.css', BSF_AIOSRS_PRO_VER, 'false' );

			wp_register_script( 'bsf-target-rule-select2', BSF_AIOSRS_PRO_URI . 'classes/lib/target-rule/select2.js', array( 'jquery', 'backbone', 'wp-util' ), BSF_AIOSRS_PRO_VER, true );
			wp_register_script( 'bsf-target-rule', BSF_AIOSRS_PRO_URI . 'classes/lib/target-rule/target-rule.js', array( 'jquery', 'bsf-target-rule-select2' ), BSF_AIOSRS_PRO_VER, true );
			wp_register_script( 'bsf-user-role', BSF_AIOSRS_PRO_URI . 'classes/lib/target-rule/user-role.js', array( 'jquery' ), BSF_AIOSRS_PRO_VER, true );

			wp_enqueue_media();
			wp_register_script( 'aiosrs-pro-admin-edit-script', BSF_AIOSRS_PRO_URI . 'admin/assets/js/script.js', array( 'jquery', 'jquery-ui-tooltip' ), BSF_AIOSRS_PRO_VER, true );
			wp_register_script( 'aiosrs-pro-setup', BSF_AIOSRS_PRO_URI . 'admin/assets/js/setup-wizard.js', array( 'jquery' ), BSF_AIOSRS_PRO_VER, true );
			wp_localize_script(
				'bsf-target-rule',
				'Targetrule',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'security' => wp_create_nonce( 'schema_nonce' ),
				)
			);

			if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'aiosrs-pro-setup' ) ) {
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
				<?php wp_print_scripts( array( 'bsf-target-rule-select2', 'bsf-target-rule', 'bsf-user-role', 'aiosrs-pro-admin-edit-script', 'aiosrs-pro-setup' ) ); ?>
				<?php do_action( 'admin_print_styles' ); ?>
				<?php do_action( 'admin_head' ); ?>
			</head>
			<body class="aiosrs-pro-setup wp-core-ui">
				<div id="aiosrs-pro-logo">
					<?php
						$brand_adv = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
					?>
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

			$admin_url = BSF_AIOSRS_Pro_Admin::get_page_url( BSF_AIOSRS_Pro_Admin::$parent_page_slug );
			?>
				<div class="close-button-wrapper">
					<a href="<?php echo esc_url( $admin_url ); ?>" class="wizard-close-link" ><?php esc_html_e( 'Exit Setup Wizard', 'wp-schema-pro' ); ?></a>
				</div>
				</body>
			</html>
			<?php
		}

		/**
		 * Output the steps.
		 */
		public function setup_wizard_steps() {

			$ouput_steps = $this->steps;
			?>
			<ol class="aiosrs-pro-setup-steps">
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
					?>
					<li class="<?php echo esc_attr( $classes ); ?>">
						<span><?php echo esc_html( $step['name'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>
			<?php
		}

		/**
		 * Output the content for the current step.
		 */
		public function setup_wizard_content() {
			echo '<div class="aiosrs-pro-setup-content">';
			call_user_func( $this->steps[ $this->step ]['view'] );
			echo '</div>';
		}

		/**
		 * Introduction step.
		 */
		public function choose_schema_type() {
			?>
			<h1><?php esc_html_e( 'Select the Schema Type You Need to Add:', 'wp-schema-pro' ); ?></h1>
			<form method="post">
				<input type="hidden" id="bsf-aiosrs-schema-title" name="bsf-aiosrs-schema-title" class="bsf-aiosrs-schema-title" >
				<input type="hidden" id="bsf-aiosrs-schema-type" name="bsf-aiosrs-schema-type" class="bsf-aiosrs-schema-type" >
				<table class="form-table aiosrs-pro-basic-config">
					<tr>
						<td><!-- Comment
							<?php foreach ( BSF_AIOSRS_Pro_Schema::$schema_meta_fields as $key => $schema_field ) { ?>
								--><span class="aiosrs-pro-schema-temp-wrap" data-schema-type="<?php echo esc_attr( $schema_field['key'] ); ?>" data-schema-title="<?php echo isset( $schema_field['label'] ) ? esc_attr( $schema_field['label'] ) : ''; ?>" >
									<i class="<?php echo isset( $schema_field['icon'] ) ? esc_attr( $schema_field['icon'] ) : 'dashicons dashicons-media-default'; ?>"></i>
									<?php echo isset( $schema_field['label'] ) ? esc_attr( $schema_field['label'] ) : ''; ?>
								</span><!-- Comment
							<?php } ?>
						--></td>
					</tr>
				</table>

				<p class="aiosrs-pro-setup-actions step">
					<input type="submit" class="uct-activate button-primary button button-large button-next" disabled="true" value="<?php esc_html_e( 'Next', 'wp-schema-pro' ); ?>" name="save_step" />
					<?php wp_nonce_field( 'aiosrs-pro-setup' ); ?>
				</p>
			</form>
			<?php
		}

		/**
		 * Save Locale Settings.
		 */
		public function choose_schema_type_save() {
			check_admin_referer( 'aiosrs-pro-setup' );

			// Update site title & tagline.
			$redirect_url = $this->get_next_step_link();
			$title        = isset( $_POST['bsf-aiosrs-schema-title'] ) ? sanitize_text_field( $_POST['bsf-aiosrs-schema-title'] ) : 0;
			$type         = isset( $_POST['bsf-aiosrs-schema-type'] ) ? sanitize_text_field( $_POST['bsf-aiosrs-schema-type'] ) : 0;

			$default_fields = array();
			if ( isset( BSF_AIOSRS_Pro_Schema::$schema_meta_fields[ 'bsf-aiosrs-' . $type ]['subkeys'] ) ) {
				$default_data = BSF_AIOSRS_Pro_Schema::$schema_meta_fields[ 'bsf-aiosrs-' . $type ]['subkeys'];
				foreach ( $default_data as $key => $value ) {
					if ( 'repeater' === $value['type'] ) {
						foreach ( $value['fields'] as $subkey => $subvalue ) {
							if ( isset( $subvalue['default'] ) && 'none' !== $subvalue['default'] ) {
								$default_fields[ $key ][0][ $subkey ] = $subvalue['default'];
							} else {
								$default_fields[ $key ][0][ $subkey ] = 'create-field';
							}
						}
					} else {
						if ( isset( $value['default'] ) && 'none' !== $value['default'] ) {
							$default_fields[ $key ] = $value['default'];
						} else {
							$default_fields[ $key ] = 'create-field';
						}
					}
				}
			}

			$postarr = array(
				'post_type'   => 'aiosrs-schema',
				'post_title'  => $title,
				'post_status' => 'publish',
				'meta_input'  => array(
					'bsf-aiosrs-schema-type' => $type,
					'bsf-aiosrs-' . $type    => $default_fields,
				),
			);
			$post_id = wp_insert_post( $postarr );

			if ( ! is_wp_error( $post_id ) ) {
				$redirect_url = add_query_arg( 'schema-id', $post_id, $redirect_url );
			}

			wp_safe_redirect( esc_url_raw( $redirect_url ) );
			exit;
		}

		/**
		 * Locale settings
		 */
		public function implement_on_callback() {
			if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
				return;
			}
			$schema_id    = 0;
			$title        = '';
			$redirect_url = $this->get_next_step_link();

			if ( isset( $_GET['schema-id'] ) && ! empty( $_GET['schema-id'] ) ) {
				$schema_id    = intval( $_GET['schema-id'] );
				$redirect_url = add_query_arg( 'schema-id', $schema_id, $redirect_url );
				$title        = get_the_title( $schema_id );
			}

			$meta_values = array(
				'include-locations' => array(
					'rule' => array( 'basic-singulars' ),
				),
				'exclude-locations' => array(),
			);
			?>

			<h1>
			<?php
			printf(
				/* translators: 1 schema title */
				wp_kses_post( 'Where <i>%s</i> schema should be integrated?', 'wp-schema-pro' ),
				esc_html( $title )
			);
			?>
				</h1>
			<form method="post">
				<input type="hidden" name="schema-id" value="<?php echo esc_attr( $schema_id ); ?>">
				<table class="bsf-aiosrs-schema-table widefat">
					<tr class="bsf-aiosrs-schema-row">
						<td class="bsf-aiosrs-schema-row-heading">
							<label><?php esc_html_e( 'Enable On', 'wp-schema-pro' ); ?></label>
							<i class="bsf-aiosrs-schema-heading-help dashicons dashicons-editor-help" title="<?php echo esc_attr__( 'Add locations for where this Schema should appear.', 'wp-schema-pro' ); ?>"></i>
						</td>
						<td class="bsf-aiosrs-schema-row-content">
						<?php
							BSF_Target_Rule_Fields::target_rule_settings_field(
								'bsf-aiosrs-schema-location',
								array(
									'title'          => __( 'Display Rules', 'wp-schema-pro' ),
									'value'          => '[{"type":"basic-global","specific":null}]',
									'tags'           => 'site,enable,target,pages',
									'rule_type'      => 'display',
									'add_rule_label' => __( 'Add And Rule', 'wp-schema-pro' ),
								),
								$meta_values['include-locations']
							);
						?>
						</td>
					</tr>
					<tr class="bsf-aiosrs-schema-row <?php echo empty( $meta_values['exclude-locations'] ) ? 'bsf-hidden' : ''; ?>">
						<td class="bsf-aiosrs-schema-row-heading">
							<label><?php esc_html_e( 'Exclude From', 'wp-schema-pro' ); ?></label>
							<i class="bsf-aiosrs-schema-heading-help dashicons dashicons-editor-help" title="<?php echo esc_attr__( 'This Schema will not appear at these locations.', 'wp-schema-pro' ); ?>"></i>
						</td>
						<td class="bsf-aiosrs-schema-row-content">
						<?php
							BSF_Target_Rule_Fields::target_rule_settings_field(
								'bsf-aiosrs-schema-exclusion',
								array(
									'title'          => __( 'Exclude On', 'wp-schema-pro' ),
									'value'          => '[]',
									'tags'           => 'site,enable,target,pages',
									'add_rule_label' => __( 'Add Or Rule', 'wp-schema-pro' ),
									'rule_type'      => 'exclude',
								),
								$meta_values['exclude-locations']
							);
						?>
						</td>
					</tr>
				</table>
				<p class="aiosrs-pro-setup-actions step">
					<input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Next', 'wp-schema-pro' ); ?>" name="save_step" />
					<?php wp_nonce_field( 'aiosrs-pro-setup' ); ?>
				</p>
			</form>
			<?php
		}

		/**
		 * Save Locale Settings.
		 */
		public function implement_on_callback_save() {
			check_admin_referer( 'aiosrs-pro-setup' );

			$schema_id    = isset( $_POST['schema-id'] ) ? sanitize_text_field( $_POST['schema-id'] ) : 0;
			$enabled_on   = BSF_Target_Rule_Fields::get_format_rule_value( $_POST, 'bsf-aiosrs-schema-location' );
			$exclude_from = BSF_Target_Rule_Fields::get_format_rule_value( $_POST, 'bsf-aiosrs-schema-exclusion' );
			$redirect_url = $this->get_next_step_link();
			if ( $schema_id ) {

				$redirect_url = add_query_arg( 'schema-id', $schema_id, $redirect_url );
				update_post_meta( $schema_id, 'bsf-aiosrs-schema-location', $enabled_on );
				update_post_meta( $schema_id, 'bsf-aiosrs-schema-exclusion', $exclude_from );
			}

			wp_safe_redirect( esc_url_raw( $redirect_url ) );
			exit;
		}

		/**
		 * Get Location rules of schema for Custom meta box.
		 *
		 * @param  array $enabled_on   Enabled on rules.
		 * @param  array $exclude_from Exlcude on rules.
		 * @return array
		 */
		public static function get_display_rules_for_meta_box( $enabled_on, $exclude_from ) {
			$locations        = array();
			$enabled_location = array();
			$exclude_location = array();

			$args       = array(
				'public'   => true,
				'_builtin' => true,
			);
			$post_types = get_post_types( $args );
			unset( $post_types['attachment'] );

			$args['_builtin'] = false;
			$custom_post_type = get_post_types( $args );
			$post_types       = array_merge( $post_types, $custom_post_type );

			if ( ! empty( $enabled_on ) && isset( $enabled_on['rule'] ) ) {
				$enabled_location = $enabled_on['rule'];
			}
			if ( ! empty( $exclude_from ) && isset( $exclude_from['rule'] ) ) {
				$exclude_location = $exclude_from['rule'];
			}

			if ( in_array( 'specifics', $enabled_location, true ) || ( in_array( 'basic-singulars', $enabled_location, true ) && ! in_array( 'basic-singulars', $exclude_location, true ) ) ) {
				foreach ( $post_types as $post_type ) {
					$locations[ $post_type ] = 1;
				}
			} else {
				foreach ( $post_types as $post_type ) {
					$key = $post_type . '|all';
					if ( in_array( $key, $enabled_location, true ) && ! in_array( $key, $exclude_location, true ) ) {
						$locations[ $post_type ] = 1;
					}
				}
			}
			return $locations;
		}

		/**
		 * Final step.
		 */
		public function schema_ready() {
			if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
				return;
			}

			$schema_id = 0;
			$title     = '';

			if ( isset( $_GET['schema-id'] ) && ! empty( $_GET['schema-id'] ) ) {
				$schema_id   = intval( $_GET['schema-id'] );
				$schema_type = get_post_meta( $schema_id, 'bsf-aiosrs-schema-type', true );
				$title       = get_the_title( $schema_id );
			}

			?>
			<h1><?php esc_html_e( 'Your Schema is Ready!', 'wp-schema-pro' ); ?></h1>

			<div class="aiosrs-pro-setup-next-steps">
				<div class="aiosrs-pro-setup-next-steps-last">

					<p class="success">
						<?php
						printf(
							/* translators: 1 schema title */
							wp_kses_post( 'Congratulations! The <i>%s</i> schema has been added and enabled on selected pages.', 'wp-schema-pro' ),
							esc_html( $title )
						);
						?>
					</p>
					<p class="success">
						<b><?php esc_html_e( 'Here is what you do after setup is complete:', 'wp-schema-pro' ); ?></b><br>
						<?php esc_html_e( 'Step 1: Check the Schema you just created.', 'wp-schema-pro' ); ?><br>
						<?php if ( 'article' === $schema_type || 'course' === $schema_type ) { ?>
							<?php esc_html_e( 'Step 2: Test if Schema is integrated correctly.', 'wp-schema-pro' ); ?>
						<?php } else { ?>
							<?php esc_html_e( 'Step 2: Add necessary Schema information on individual pages and posts.', 'wp-schema-pro' ); ?><br>
							<?php esc_html_e( 'Step 3: Test if Schema is integrated correctly.', 'wp-schema-pro' ); ?>
						<?php } ?>
					</p>

					<hr />

					<table class="form-table aiosrs-pro-schema-ready">
						<tr>
							<td scope="row" >
							<a href="<?php echo ( $schema_id ) ? esc_attr( get_edit_post_link( $schema_id ) ) : '#'; ?>" type="button" class="button button-primary button-hero" ><?php esc_html_e( 'Complete Setup', 'wp-schema-pro' ); ?></a>
							</td>
						</tr>
					</table>

				</div>
			</div>
			<?php
		}
	}

	new BSF_AIOSRS_Pro_Schema_Wizard();

endif;
