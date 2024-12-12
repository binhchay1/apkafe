<?php
/**
 * Schema Pro Admin Init
 *
 * @package Schema Pro
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Custom_Fields_Markup' ) ) {

	/**
	 * BSF_AIOSRS_Pro_Custom_Fields_Markup initial setup
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Custom_Fields_Markup {

		/**
		 * Class instance.
		 *
		 * @access private
		 * @var $instance Class instance.
		 */
		private static $instance;

		/**
		 * Meta Boxes.
		 *
		 * @since 1.0
		 * @var array $meta_boxes
		 */
		public static $meta_boxes = array();

		/**
		 * Custom Fields.
		 *
		 * @since 1.0
		 * @var array $meta_options
		 */
		public static $meta_options = array();

		/**
		 * Custom user role.
		 *
		 * @since 2.1.2
		 * @var array $allowed_user_roles
		 */
		public static $allowed_user_roles = array();

		/**
		 * Mapping Fields.
		 *
		 * @since 1.0
		 * @var array $mapping
		 */
		public $mapping = array();
		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor function.
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'aiosrs_custom_allowed_user_role' ) );
				add_action( 'load-post.php', array( $this, 'init_metabox' ) );
				add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
			add_action( 'admin_head', array( $this, 'meta_boxes_style' ) );
			add_shortcode( 'aiosrs_pro_custom_field', array( $this, 'shortcode_callback' ) );
			add_action( 'wp_ajax_aiosrs_reset_post_rating', array( $this, 'aiosrs_reset_post_rating_callback' ) );
			$this->mapping = array(
				'custom-field'       => 'custom-field',
				'fixed-text'         => 'custom-field',
				'accept-user-rating' => 'custom-field',
				'create-field'       => 'custom-field',
				'custom-text'        => 'custom-field',
				'specific-field'     => 'specific-field',
			);
		}

		/**
		 *  Init Metabox user rile dependancies.
		 */
		public function aiosrs_custom_allowed_user_role() {
			$allowed_user = apply_filters(
				'wp_schema_pro_role',
				array( 'administrator' )
			);
			update_option( 'custom_user_role', $allowed_user );
		}

		/**
		 * Rest star rating.
		 */
		public function aiosrs_reset_post_rating_callback() {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error();
			}

			check_ajax_referer( 'schema-pro-reset-rating', 'nonce' );

			$response = array(
				'success' => false,
			);
			if ( isset( $_POST['post_id'] ) ) {
				$post_id   = sanitize_text_field( $_POST['post_id'] );
				$schema_id = isset( $_POST['schema_id'] ) ? sanitize_text_field( $_POST['schema_id'] ) : '';
				delete_post_meta( $post_id, 'bsf-schema-pro-reviews-' . $schema_id );
				delete_post_meta( $post_id, 'bsf-schema-pro-review-counts-' . $schema_id );
				delete_post_meta( $post_id, 'bsf-schema-pro-rating-' . $schema_id );

				$response['success']      = true;
				$response['rating-avg']   = _x( '0/5', 'rating out of', 'wp-schema-pro' );
				$response['review-count'] = __( '(0 Reviews)', 'wp-schema-pro' );
			}
			wp_send_json( $response );
		}

		/**
		 * Custom Fields Shortcode.
		 *
		 * @param array $atts Shortcode attributes.
		 * @return html
		 */
		public function shortcode_callback( $atts ) {

			$args = shortcode_atts(
				array(
					'post_id'   => '',
					'post_type' => 'post',
					'field_key' => '',
					'default'   => '',
				),
				$atts
			);

			$post_id = empty( $args['post_id'] ) ? get_the_ID() : (int) $args['post_id'];
			$post    = get_post( $post_id );
			
			if ( ! current_user_can( 'edit_post', $post_id ) || ( ! current_user_can( 'read_private_posts' ) && ( 'private' === $post->post_status ) ) ) {
				return ''; // Insufficient permissions or invalid post.
			}

			$allowed_post_types = array( 'post', 'page', 'comment', 'term' );
			if ( ! in_array( $args['post_type'], $allowed_post_types ) ) {
				return 'Invalid post type';
			}
			$output = get_metadata( $args['post_type'], $post_id, $args['field_key'], true );

			if ( empty( $output ) || is_array( $output ) ) {
				$output = $args['default'];
			}

			return $output;
		}

		/**
		 * Meta Boxes Style
		 *
		 * @return void
		 */
		public function meta_boxes_style() {

			if ( ! empty( self::$meta_boxes ) ) {
				$ids = array();
				foreach ( self::$meta_boxes as $key => $meta_box ) {
					$ids[] = '#aiosrs_pro_custom_meta_box_' . $key . ' .inside';
				}

				echo '<style id="aiosrs-pro-custom-meta-boxes-style"> ';
				echo esc_html( implode( ', ', $ids ) . '{ margin-top: 0; padding: 0; }' );
				echo '</style>';
			}
		}

		/**
		 *  Init Metabox
		 */
		public function init_metabox() {

			$screen            = get_current_screen();
			$current_post_type = $screen->post_type;
			if ( 'aiosrs-schema' === $current_post_type || 'acf-field-group' === $current_post_type ) {
				return;
			}
			$allowed_user_roles = array();
			$user               = wp_get_current_user();
			if ( is_array( $allowed_user_roles ) ) {
				$allowed_user_roles = get_option( 'custom_user_role' );
			}
			if ( array_intersect( $allowed_user_roles, (array) $user->roles ) ) {
				add_action( 'add_meta_boxes', array( $this, 'setup_meta_box' ) );
				add_action( 'save_post', array( $this, 'save_meta_box' ) );
			}
		}

		/**
		 * Initializing static variable.
		 *
		 * @param int|boolean $current_post_id Post Id.
		 * @return void
		 */
		public function init_static_fields( $current_post_id = false ) {

			$option = array(
				'location'  => 'bsf-aiosrs-schema-location',
				'exclusion' => 'bsf-aiosrs-schema-exclusion',
			);

			$schema_post_result = BSF_Target_Rule_Fields::get_instance()->get_posts_by_conditions( 'aiosrs-schema', $option, $current_post_id );
			if ( is_array( $schema_post_result ) && ! empty( $schema_post_result ) ) {
				$current_post_id = get_the_id();
				foreach ( $schema_post_result as $post_id => $post_data ) {

					$schema_type = get_post_meta( $post_id, 'bsf-aiosrs-schema-type', true );
					$schema_meta = get_post_meta( $post_id, 'bsf-aiosrs-' . $schema_type, true );

					if ( empty( $current_post_id ) || empty( $schema_type ) || empty( $schema_meta ) ) {
						continue;
					}

					$schema_meta_fields = BSF_AIOSRS_Pro_Schema::$schema_meta_fields[ 'bsf-aiosrs-' . $schema_type ]['subkeys'];
					$review_schema_type = BSF_AIOSRS_Pro_Schema::$schema_meta_fields['bsf-aiosrs-review']['subkeys']['schema-type']['choices'];
					$item_schema_type   = isset( $schema_meta['schema-type'] ) ? $schema_meta['schema-type'] : '';
					foreach ( $review_schema_type as $review_type_key => $review_type ) {

						if ( ! ( empty( $item_schema_type ) ) && ( $item_schema_type === $review_type_key ) ) {
							$temp = BSF_AIOSRS_Pro_Schema::$schema_item_types[ $item_schema_type ];
							if ( isset( $temp['subkeys'] ) ) {
								$schema_meta_item_fields = $temp['subkeys'];
							}
						}
					}
					$custom_fields = array();
					foreach ( $schema_meta as $schema_key => $schema_value ) {

						if ( isset( $schema_meta_fields[ $schema_key ] ) ) {
							$schema_field_value = $schema_meta_fields[ $schema_key ];
						} else {
							if ( isset( $schema_meta['schema-type'] ) ) {

								$item_schema_key = str_replace( $schema_meta['schema-type'] . '-', '', $schema_key );
							}
							$item_schema_key    = isset( $item_schema_key ) ? $item_schema_key : '';
							$schema_field_value = isset( $schema_meta_item_fields[ $item_schema_key ] ) ? $schema_meta_item_fields[ $item_schema_key ] : null;
						}
						if ( 'applicant-location' === $schema_key ) {
							$schema_field_value = array(
								'label'       => esc_html__( 'Applicant Location', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'none',
								'required'    => false,
								'description' => esc_html__( 'The geographic location(s) in which employees may be located to be eligible for the Remote job.', 'wp-schema-pro' ),
							);
						}
						$repeater_values = array();
						if ( $schema_field_value ) {

							if ( 'repeater' === $schema_field_value['type'] ) {

								$repeater_values = get_post_meta( $current_post_id, $schema_type . '-' . $post_id . '-' . $schema_key, true );
								// Added backward applicant location field dependancy.
								if ( 'remote-location' === $schema_key ) {
									$applicant_location_string = get_post_meta( $current_post_id, 'job-posting-' . $post_id . '-applicant-location', true );
									$dep_count                 = get_option( 'wp_backward_field' . $current_post_id . '' . $post_id );
									$dep_count                 = ! empty( $dep_count ) ? $dep_count : '';
									if ( $applicant_location_string !== $dep_count && '' === $dep_count ) {

										$deprecated_application_location = array(
											array(
												'applicant-location' => ! empty( $applicant_location_string ) ? $applicant_location_string : '',
												'applicant-location-fieldtype' => 'custom-field',
												'applicant-location-connected' => 'none',
												'applicant-location-custom' => ! empty( $applicant_location_string ) ? $applicant_location_string : '',
												'applicant-location-specific' => 'none',
											),
										);
										if ( isset( $deprecated_application_location ) && ! empty( $deprecated_application_location ) ) {
											if ( ! empty( $repeater_values ) ) {
												$repeater_values = array_merge( $deprecated_application_location, $repeater_values );
												update_option( 'wp_backward_field' . $current_post_id . '' . $post_id, $applicant_location_string );
											} else {
												$repeater_values = $deprecated_application_location;
												update_option( 'wp_backward_field' . $current_post_id . '' . $post_id, $applicant_location_string );
											}
										}
										update_post_meta( $current_post_id, $schema_type . '-' . $post_id . '-' . $schema_key, $repeater_values );
									}
								}

								if ( ! is_array( $repeater_values ) || empty( $repeater_values ) ) {

									$repeater_values = $schema_meta[ $schema_key ];
								}

								$repeter_fields = $schema_meta_fields[ $schema_key ]['fields'];

								$tmp_fields = array();

								foreach ( $repeater_values as $index => $repeater_value ) {

									foreach ( $schema_field_value['fields'] as $field_key => $field ) {

										$field_val = isset( $schema_meta[ $schema_key ][ $index ][ $field_key ] ) ? $schema_meta[ $schema_key ][ $index ][ $field_key ] : '';

										if ( 'create-field' === $field_val ) {
											$selected_field = 'custom-field';
											$selected_value = '';
										} elseif ( isset( $schema_meta[ $schema_key ][ $index ][ $field_key . '-' . $field_val ] ) ) {
											$selected_field = isset( $this->mapping[ $field_val ] ) ? $this->mapping[ $field_val ] : $field_val;
											$selected_value = $schema_meta[ $schema_key ][ $index ][ $field_key . '-' . $field_val ];
										} elseif ( isset( $this->mapping[ $field_val ] ) ) {
											$selected_field = $this->mapping[ $field_val ];
											$selected_value = '';
										} else {
											$selected_field = 'global-field';
											$selected_value = $field_val;
										}

										$tmp_fields[ $index ][] = array(
											'default'     => isset( $repeater_value[ $field_key ] ) ? $repeater_value[ $field_key ] : '',
											'name'        => $schema_type . '-' . $post_id . '-' . $schema_key . '[' . $index . '][' . $field_key . ']',
											'fieldtype'   => $schema_type . '-' . $post_id . '-' . $schema_key . '[' . $index . '][' . $field_key . '-fieldtype]',
											'type'        => $field['type'],
											'label'       => $field['label'],
											'required'    => isset( $field['required'] ) ? $field['required'] : false,
											'min'         => isset( $repeter_fields[ $field_key ]['attrs']['min'] ) ? $repeter_fields[ $field_key ]['attrs']['min'] : '',
											'step'        => isset( $repeter_fields[ $field_key ]['attrs']['step'] ) ? $repeter_fields[ $field_key ]['attrs']['step'] : '',
											'description' => isset( $repeter_fields[ $field_key ]['description'] ) ? $repeter_fields[ $field_key ]['description'] : '',
											'dropdown-content' => isset( $repeter_fields[ $field_key ]['dropdown-type'] ) ? $repeter_fields[ $field_key ]['dropdown-type'] : '',
											'global_fieldtype' => $selected_field,
											'global_default' => $selected_value,
											'class'       => isset( $field['class'] ) ? $field['class'] : '',
											'subkey_data' => $field,

										);
									}
								}

								$custom_fields[] = array(
									'default'          => isset( $schema_meta[ $schema_key . '-custom-meta-default' ] ) ? $schema_meta[ $schema_key . '-custom-meta-default' ] : '',
									'name'             => $schema_type . '-' . $post_id . '-' . $schema_key,
									'type'             => $schema_field_value['type'],
									'label'            => $schema_field_value['label'],
									'min'              => isset( $schema_field_value['attrs']['min'] ) ? $schema_field_value['attrs']['min'] : '',
									'step'             => isset( $schema_field_value['attrs']['step'] ) ? $schema_field_value['attrs']['step'] : '',
									'required'         => isset( $schema_field_value['required'] ) ? $schema_field_value['required'] : false,
									'dropdown-content' => isset( $schema_field_value['dropdown-type'] ) ? $schema_field_value['dropdown-type'] : '',
									'user-rating'      => 'accept-user-rating' === $schema_value,
									'description'      => isset( $schema_field_value['description'] ) ? $schema_field_value['description'] : '',
									'fields'           => $tmp_fields,
									'global_fieldtype' => '',
									'global_default'   => '',
								);
							} elseif ( 'repeater-target' === $schema_field_value['type'] ) {

								$repeater_values = get_post_meta( $current_post_id, $schema_type . '-' . $post_id . '-' . $schema_key, true );

								if ( ! is_array( $repeater_values ) || empty( $repeater_values ) ) {

									$repeater_values = array( array_fill_keys( array_keys( $schema_field_value['fields'] ), '' ) );
								}

								$tmp_fields = array();

								foreach ( $repeater_values as $key => $repeater_value ) {

									foreach ( $schema_field_value['fields'] as $field_key => $field ) {

										$tmp_fields[ $key ][] = array(
											'default'     => $repeater_value[ $field_key ],
											'name'        => $schema_type . '-' . $post_id . '-' . $schema_key . '[' . $key . '][' . $field_key . ']',
											'type'        => $field['type'],
											'label'       => $field['label'],
											'required'    => isset( $field['required'] ) ? $field['required'] : false,
											'description' => isset( $field['description'] ) ? $field['description'] : '',

										);

									}
								}

								$custom_fields[] = array(
									'default'          => isset( $schema_meta[ $schema_key . '-custom-meta-default' ] ) ? $schema_meta[ $schema_key . '-custom-meta-default' ] : '',
									'name'             => $schema_type . '-' . $post_id . '-' . $schema_key,
									'type'             => $schema_field_value['type'],
									'label'            => $schema_field_value['label'],
									'min'              => isset( $schema_field_value['attrs']['min'] ) ? $schema_field_value['attrs']['min'] : '',
									'step'             => isset( $schema_field_value['attrs']['step'] ) ? $schema_field_value['attrs']['step'] : '',
									'required'         => isset( $schema_field_value['required'] ) ? $schema_field_value['required'] : false,
									'dropdown-content' => isset( $schema_field_value['dropdown-type'] ) ? $schema_field_value['dropdown-type'] : '',
									'user-rating'      => 'accept-user-rating' === $schema_value,
									'description'      => isset( $schema_field_value['description'] ) ? $schema_field_value['description'] : '',
									'fields'           => $tmp_fields,
									'global_fieldtype' => '',
									'global_default'   => '',
								);
							} else {
								if ( ! isset( $schema_meta['bsf-aiosrs-software-application-rating'] ) ) {
									$schema_meta['bsf-aiosrs-software-application-rating'] = '';
								}
								if ( ! isset( $schema_meta['bsf-aiosrs-product-rating'] ) ) {
									$schema_meta['bsf-aiosrs-product-rating'] = '';
								}
								// Skip review count in case of Accept user rating.
								if ( ( 'bsf-aiosrs-product-review-count' === $schema_key || 'bsf-aiosrs-software-application-review-count' === $schema_key || 'review-count' === $schema_key ) && ( 'accept-user-rating' === $schema_meta['rating'] || 'accept-user-rating' === $schema_meta['bsf-aiosrs-software-application-rating'] || 'accept-user-rating' === $schema_meta['bsf-aiosrs-product-rating'] ) ) {
									continue;
								}

								if ( 'create-field' === $schema_meta[ $schema_key ] ) {
									$selected_field = 'custom-field';
									$selected_value = '';
								} elseif ( isset( $schema_meta[ $schema_key . '-' . $schema_meta[ $schema_key ] ] ) ) {
									$selected_field = isset( $this->mapping[ $schema_meta[ $schema_key ] ] ) ? $this->mapping[ $schema_meta[ $schema_key ] ] : $schema_meta[ $schema_key ];
									$selected_value = $schema_meta[ $schema_key . '-' . $schema_meta[ $schema_key ] ];
								} elseif ( isset( $this->mapping[ $schema_meta[ $schema_key ] ] ) ) {
									$selected_field = $this->mapping[ $schema_meta[ $schema_key ] ];
									$selected_value = '';
								} else {
									$selected_field = 'global-field';
									$selected_value = $schema_meta[ $schema_key ];
								}

								$custom_fields[] = array(
									'default'          => isset( $schema_meta[ $schema_key . '-custom-meta-default' ] ) ? $schema_meta[ $schema_key . '-custom-meta-default' ] : '',
									'name'             => $schema_type . '-' . $post_id . '-' . $schema_key,
									'type'             => $schema_field_value['type'],
									'label'            => $schema_field_value['label'],
									'min'              => isset( $schema_field_value['attrs']['min'] ) ? $schema_field_value['attrs']['min'] : '',
									'step'             => isset( $schema_field_value['attrs']['step'] ) ? $schema_field_value['attrs']['step'] : '',
									'required'         => isset( $schema_field_value['required'] ) ? $schema_field_value['required'] : false,
									'dropdown-content' => isset( $schema_field_value['dropdown-type'] ) ? $schema_field_value['dropdown-type'] : '',
									'user-rating'      => 'accept-user-rating' === $schema_value,
									'description'      => isset( $schema_field_value['description'] ) ? $schema_field_value['description'] : '',
									'subkey'           => $schema_key,
									'subkey_data'      => $schema_field_value,
									'global_fieldtype' => $selected_field,
									'global_default'   => $selected_value,
								);

							}
						}
					}

					if ( ! empty( $custom_fields ) ) {

						$schema_enabled = self::enable_schema_post_option();
						if ( $schema_enabled ) {
							array_unshift(
								$custom_fields,
								array(
									'default' => 'disabled',
									'name'    => $schema_type . '-' . $post_id . '-enabled-schema',

								)
							);
						}

						self::$meta_boxes[ $post_id ] = array(
							'ID'          => $post_id,
							'post_title'  => get_the_title( $post_id ),
							'schema_type' => $schema_type,
							'fields'      => $custom_fields,
						);

						self::$meta_options[] = $custom_fields;
					}
				}

				self::$meta_options = array_reduce(
					self::$meta_options,
					function ( $carry, $item ) {
						if ( is_array( $item ) ) {
							return array_merge( $carry, $item );
						}
						return $carry;
					},
					array()
				);

			}
		}

		/**
		 *  Setup Metabox
		 */
		public function setup_meta_box() {

			$brand_settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
			$this->init_static_fields();
			if ( ! empty( self::$meta_boxes ) ) {
				if ( '' !== $brand_settings['sp_plugin_name'] ) {
					$title = __( $brand_settings['sp_plugin_name'], 'wp-schema-pro' ); //phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				} else {
					$title = __( 'Schema Pro', 'wp-schema-pro' );
				}
				if ( count( self::$meta_boxes ) === 1 ) {
					$key    = key( self::$meta_boxes );
					$title .= ' - ' . self::$meta_boxes[ $key ]['post_title'];
				}
				$current_post_type = get_post_type();
				add_meta_box( 'aiosrs-pro-custom-fields', $title, array( $this, 'custom_field_markup' ), $current_post_type );
			}
		}

		/**
		 * Function to enable option.
		 *
		 * @since 1.1.1
		 * @return boolean
		 */
		public static function enable_schema_post_option() {

			return apply_filters( 'wp_schema_pro_default_markup', false );
		}

		/**
		 * Custom Fields meta Markup.
		 *
		 * @param  object $post Post Object.
		 * @return void
		 */
		public function custom_field_markup( $post ) {

			wp_nonce_field( basename( __FILE__ ), 'aiosrs-pro-custom-meta' );
			$stored         = get_post_meta( $post->ID );
			$tmp_post       = get_post( $post->ID, ARRAY_A );
			$stored['post'] = $tmp_post;
			$first_tab      = true;

			$schema_enabled = self::enable_schema_post_option();

			do_action( 'aiosrs_pro_custom_fields_markup_before', $post, $stored );
			if ( count( self::$meta_boxes ) > 1 ) { ?>
				<div class="aiosrs-pro-meta-fields-tabs-wrapper">
					<?php
					foreach ( self::$meta_boxes as $key => $meta_box ) {

						$id                    = 'aiosrs_pro_custom_meta_box_' . $key;
						$title                 = ! empty( $meta_box['post_title'] ) ? $meta_box['post_title'] : '&nbsp;';
						$enable_schema_type    = $meta_box['schema_type'];
						$enable_schema_id      = $meta_box['ID'];
						$enable_schema_markup  = $enable_schema_type . '-' . $enable_schema_id . '-enabled-schema';
						$option_default_schema = get_post_meta( $post->ID, $enable_schema_markup, true );

						?>
						<div class="aiosrs-pro-meta-fields-tab <?php echo $first_tab ? 'active' : ''; ?>" data-tab-id="aiosrs-pro-meta-fields-wrapper-<?php echo esc_attr( $id ); ?>" >
							<label><?php echo esc_html( $title ); ?></label>
							<?php if ( $schema_enabled ) : ?>
							<div class="wpsp-enable-schema-markup">
								<div class="wpsp-enable-schema-markup__field">
									<span title= "Enable Schema Markup will help you to enable or disable schema markup from current page/post." class="wpsp-enable-schema-toggle bsf-aiosrs-schema-heading-help">
										<input type="hidden" value="disabled" name="<?php echo esc_attr( $enable_schema_markup ); ?>" class="wpsp-enable-schema-toggle__input-hidden">
										<input type="checkbox" <?php echo isset( $option_default_schema ) && '1' === $option_default_schema ? 'checked' : ''; ?> value="1" class="wpsp-enable-schema-toggle__input">
										<span class="wpsp-enable-schema-toggle__track"></span>
										<span class="wpsp-enable-schema-toggle__thumb"></span>
									</span>
								</div>
							</div>
							<?php endif; ?>
						</div>
						<?php
						$first_tab = false;
					}
				// @codingStandardsIgnoreStart
				?>
				</div><?php } // PHP
			?><div class="aiosrs-pro-meta-fields-wrapper">

			<div id="wpsp-reset-dialog-confirmation"></div>

			<?php // @codingStandardsIgnoreEnd ?>
				<?php $first_tab = true; ?>
				<?php
				foreach ( self::$meta_boxes as $key_id => $meta_box ) {

					$id           = 'aiosrs_pro_custom_meta_box_' . $key_id;
					$fields       = ! empty( $meta_box['fields'] ) ? $meta_box['fields'] : array();
					$meta_options = $fields;

					foreach ( $meta_options as $key => $value ) {
						if ( isset( $stored[ $value['name'] ][0] ) ) {
							$meta_options[ $key ]['default'] = $stored[ $value['name'] ][0];
						}
					}

					?>
					<div class="aiosrs-pro-meta-fields-wrap aiosrs-pro-meta-fields-wrapper-<?php echo esc_attr( $id ); ?> <?php echo $first_tab ? 'open' : ''; ?>">
						<table class="form-table">
							<?php
							$status           = 0;
							$field_cont       = count( $meta_options ) - 1;
							$is_repeater_type = '';
							foreach ( $meta_options as $key => $option ) {

								// Hide the Enable Schema markup label when we use the filter.
								if ( ! isset( $option['label'] ) ) {
									continue;
								}

								$dep_class = isset( $option['subkey_data']['class'] ) ? $option['subkey_data']['class'] : '';
								?>
								<?php if ( ( 0 === $status % 2 ) || $is_repeater_type ) : ?>
							<tr class="row">
							<?php endif; ?>
								<?php
								$fieldtype      = $option['name'] . '-fieldtype';
								$selected_field = isset( $stored[ $fieldtype ][0] ) ? $stored[ $fieldtype ][0] : '';
								$original_name  = $option['name'];
								if ( ! $selected_field ) {
									$selected_field = isset( $option['global_fieldtype'] ) ? $option['global_fieldtype'] : '';
									$default        = ( isset( $option['default'] ) && ! empty( $option['default'] ) ) ? $option['default'] : $option['global_default'];
								} else {
									$default = isset( $stored[ $option['name'] ][0] ) ? $stored[ $option['name'] ][0] : '';
									if ( 'none' === $default || empty( $default ) ) {
											$default        = $option['global_default'];
											$selected_field = $option['global_fieldtype'];
									}
								}

								$required_class = '';
								if ( isset( $option['required'] ) && $option['required'] ) {
									if ( empty( $default ) ) {
										$required_class = 'wpsp-required-error-field';
									} elseif ( 'global-field' === $selected_field ) {
										$gbl_data = BSF_AIOSRS_Pro_Schema_Template::get_post_data( $tmp_post, $default, true, false );
										if ( empty( $gbl_data ) || 'Auto Draft' === $gbl_data || 'none' === $gbl_data ) {
												$required_class = 'wpsp-required-error-field';
										}
									}
								}
								if ( isset( $option['subkey_data']['choices'][ $default ] ) && 'Select Item Type' === $option['subkey_data']['choices'][ $default ] ) {
									continue;
								}
								?>
								<td class="wpsp-field-label <?php echo esc_attr( $dep_class ); ?> <?php echo esc_attr( $required_class ); ?>">
									<?php echo isset( $option['label'] ) ? esc_html( $option['label'] ) : ''; ?>
									<?php if ( isset( $option['required'] ) && true === $option['required'] ) { ?>
										<span class="required">*</span>
									<?php } ?>
									<?php if ( isset( $option['description'] ) && ! empty( $option['description'] ) ) { ?>
										<i class="bsf-aiosrs-schema-heading-help dashicons dashicons-editor-help" title="<?php echo esc_attr( $option['description'] ); ?>"></i>
									<?php } ?>
								</td>
								<?php if ( 'Review Item Type' === $option['label'] ) { ?>

									<td class="bsf-aiosrs-schema-row-content">
									<?php $review_type = $option['subkey_data']['choices'][ $default ]; ?>
										<?php if ( 'Select Item Type' !== $review_type ) { ?>
										<p>
											<?php
											echo esc_html( $review_type );
										}
										?>
										</p>
									</td>
										<?php
										continue;
								}
									$is_repeater_type = ( 'repeater' === $option['type'] || 'repeater-target' === $option['type'] );
								?>

								<?php
								$set_col_span     = false;
								$is_prev_repeater = isset( $meta_options[ $status - 1 ]['type'] ) ? $meta_options[ $status - 1 ]['type'] : '';
								$is_next_repeater = isset( $meta_options[ $status + 1 ]['type'] ) ? $meta_options[ $status + 1 ]['type'] : '';

								if ( ( 'repeater' === $is_prev_repeater || 'repeater-target' === $is_prev_repeater ) && ( 1 === $status % 2 ) ) {
									$set_col_span = true;
								}
								if ( ( 'repeater' === $is_next_repeater || 'repeater-target' === $is_next_repeater ) && ( 0 === $status % 2 ) ) {
									$set_col_span = true;
								}
								?>

								<td <?php echo ( $is_repeater_type || $set_col_span ) ? 'colspan="3"' : ''; ?> class="bsf-aiosrs-schema-row-content <?php echo esc_attr( $dep_class ); ?>">
									<?php if ( $is_repeater_type ) : ?>
										<?php self::get_field_markup( $option, $meta_box['ID'], $meta_box['schema_type'], $stored ); ?>
									<?php else : ?>
									<div class="wpsp-local-fields" style="<?php echo ( $set_col_span ) ? 'width: 38.5%' : ''; ?> ">
										<input class="wpsp-default-hidden-value" type="hidden" name="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $default ); ?>">
										<input class="wpsp-default-hidden-fieldtype" type="hidden" name="<?php echo esc_attr( $fieldtype ); ?>" value="<?php echo esc_attr( $selected_field ); ?>">

										<?php
										$option['attr'] = 'disabled';
										$option['name'] = $original_name . '-connected';

										?>


										<div data-type='global-field'
											class='wpsp-parent-field wpsp-connect-field wpsp-connected-group <?php echo 'global-field' === $selected_field ? '' : 'bsf-hidden'; ?>'>
											<div title=""  class="bsf-aiosrs-schema-heading-help wpsp-connected-field">
												<span class="dashicons dashicons-admin-links wpsp-connected-icon"></span>
											</div>
											<?php BSF_AIOSRS_Pro_Schema::render_meta_box_dropdown( $option, $default, true ); ?>
											<span class="wpsp-field-close dashicons dashicons-dismiss"></span>

										</div>
										<div data-type='custom-field' class='wpsp-parent-field wpsp-custom-field wpsp-custom-field-<?php echo esc_html( $option['type'] ); ?> <?php echo 'custom-field' === $selected_field ? '' : 'bsf-hidden'; ?>'>
										<?php
											$option['name']    = $original_name . '-custom';
											$option['default'] = $default;
											self::get_field_markup( $option, $meta_box['ID'], $meta_box['schema_type'] );
										?>
											<span class="wpsp-custom-field-connect dashicons dashicons-admin-tools"></span>
										</div>
										<?php $option['name'] = $original_name . '-specific'; ?>
										<div data-type="specific-field" class="wpsp-parent-field wpsp-specific-field <?php echo 'specific-field' === $selected_field ? '' : 'bsf-hidden'; ?>">
											<select id="<?php echo esc_attr( $option['name'] ); ?>" name="<?php echo esc_attr( $option['name'] ); ?>"
													class="bsf-aiosrs-schema-select2 bsf-aiosrs-schema-specific-field wpsp-specific-field" >
												<?php if ( $default ) { ?>
													<option value="<?php echo esc_attr( $default ); ?>" selected="selected" ><?php echo esc_html( preg_replace( '/^_/', '', esc_html( str_replace( '_', ' ', $default ) ) ) ); ?></option>
												<?php } ?>
											</select>
											<span class="wpsp-specific-field-connect dashicons dashicons-admin-tools"></span>
										</div>

									</div>
									<?php endif; ?>
								</td>

								<?php
								if ( $is_next_repeater && $set_col_span ) {
									$is_repeater_type = true;
								}

								?>




								<?php if ( ( 1 === $status % 2 ) || ( $is_repeater_type ) || ( $field_cont === $status ) ) : ?>
							</tr>
							<?php endif; ?>
								<?php
								$status++;
							}
							?>
						</table>
					</div>
					<?php
					$first_tab = false;
				}
				?>
			</div>
			<br/>
			<?php
			do_action( 'aiosrs_pro_custom_fields_markup_after', $post, $stored );
		}

		/**
		 * Get Field Markup.
		 *
		 * @param  array  $option      Option array.
		 * @param  int    $schema_id   Schema Id.
		 * @param  string $schema_type Schema Type.
		 * @param  array  $stored saved data.
		 * @return void
		 */
		public static function get_field_markup( $option, $schema_id = '', $schema_type = '', $stored = array() ) {
			$option_default = isset( $option['default'] ) ? $option['default'] : '';
			?>
			<div class="aiosrs-pro-custom-field aiosrs-pro-custom-field-<?php echo esc_attr( $option['type'] ); ?>">
			<?php
			switch ( $option['type'] ) {
				case 'text':
				case 'tel':
				case 'time':
					?>
					<input class="  " type="<?php echo esc_attr( $option['type'] ); ?>" name="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $option_default ); ?>">
					<?php
					break;
				case 'date':
					?>
				<input class="wpsp-date-field  wpsp-<?php echo esc_attr( $option['type'] ); ?>-<?php echo esc_attr( strtolower( str_replace( ' ', '-', $option['label'] ) ) ); ?>" readonly type="text" name="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $option_default ); ?>">
					<?php
					break;
				case 'datetime-local':
					?>
				<input class="wpsp-datetime-local-field wpsp-<?php echo esc_attr( $option['type'] ); ?>-<?php echo esc_attr( strtolower( str_replace( ' ', '-', $option['label'] ) ) ); ?>" readonly type="text" name="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $option_default ); ?>">
					<?php
					break;
				case 'number':
					?>
					<input type="<?php echo esc_attr( $option['type'] ); ?>" name="<?php echo esc_attr( $option['name'] ); ?>" min="<?php echo esc_attr( $option['min'] ); ?>" step="<?php echo esc_attr( $option['step'] ); ?>" value="<?php echo esc_attr( $option_default ); ?>">
					<?php
					break;
				case 'checkbox':
					?>
					<input type="hidden" name="<?php echo esc_attr( $option['name'] ); ?>" value="disabled">
					<input type="checkbox" <?php checked( $option_default, '1' ); ?> name="<?php echo esc_attr( $option['name'] ); ?>" value="1">
					<?php
					break;
				case 'rating':
					$post_id = get_the_ID();
					if ( isset( $option['user-rating'] ) && $option['user-rating'] ) {
						$review_counts    = get_post_meta( $post_id, 'bsf-schema-pro-review-counts-' . $schema_id, true );
						$review_counts    = ! empty( $review_counts ) ? $review_counts : 0;
						$aggrigate_rating = get_post_meta( $post_id, 'bsf-schema-pro-rating-' . $schema_id, true );
						$aggrigate_rating = ! empty( $aggrigate_rating ) ? $aggrigate_rating : 0;

						self::get_star_rating_markup( $aggrigate_rating, true );
						?>
						<?php
						$nonce = wp_create_nonce( 'schema-pro-reset-rating' );
						?>
						<a href="#" class="aiosrs-reset-rating <?php echo ( 0 === $review_counts ) ? 'reset-disabled' : ''; ?>" data-post-id ="<?php echo esc_attr( $post_id ); ?>" data-nonce ="<?php echo esc_attr( $nonce ); ?>" data-schema-id="<?php echo esc_attr( $schema_id ); ?>"><?php esc_html_e( 'Reset', 'wp-schema-pro' ); ?></a>
						<span class="spinner"></span>
						<div class="aiosrs-rating-summary-wrap">
							<span class="aiosrs-rating">
							<?php
							printf(
								/* translators: 1: rating */
								esc_html( _x( '%s/5', 'rating out of', 'wp-schema-pro' ) ),
								esc_html( $aggrigate_rating )
							);
							?>
							</span>
							<span class="aiosrs-rating-count">
							<?php
							printf(
								/* translators: 1: number of reviews */
								esc_html( _n( '(%1s Review)', '(%1s Reviews)', absint( $review_counts ), 'wp-schema-pro' ) ),
								absint( $review_counts )
							);
							?>
							</span>
						</div>
						<?php
					} else {
						?>
						<input type="number" class="bsf-rating-field" style="width: 35%" step="0.1" min="1" max="5" name="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $option_default ); ?>">
						<?php
						self::get_star_rating_markup( $option_default );
					}
					break;
				case 'time-duration':
					?>
					<input type="hidden" class="time-duration-field" name="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $option_default ); ?>">
					<div class="time-duration-wrap">
						<input type="text" readonly
							class="wpsp-time-duration-field"
							value="<?php echo esc_attr( BSF_AIOSRS_Pro_Schema::get_time_duration( $option_default ) ); ?>">
					</div>
					<?php
					break;
				case 'image':
					?>
					<input type="hidden" class="single-image-field" name="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $option_default ); ?>">
					<?php
					if ( ! empty( $option_default ) ) {
						$image_url = wp_get_attachment_url( $option_default );

						if ( ! $image_url && wp_http_validate_url( $option_default ) ) {
							$image_url = $option_default;
						}
					}
					?>
					<div class="image-field-wrap <?php echo ( ! empty( $image_url ) ) ? 'bsf-custom-image-selected' : ''; ?>">
						<a href="#" class="aiosrs-image-select"><?php esc_html_e( 'Select Image', 'wp-schema-pro' ); ?></a>
						<a href="#" class="aiosrs-image-remove dashicons dashicons-no-alt wp-ui-text-highlight"></a>
						<?php if ( isset( $image_url ) && ! empty( $image_url ) ) : ?>
							<a href="#" class="aiosrs-image-select img" ><img src="<?php echo esc_url( $image_url ); ?>" alt ="" /></a>
						<?php endif; ?>
					</div>
					<?php
					break;
				case 'textarea':
					$textarea_row_size = '';
					if ( 'custom-markup' === $schema_type ) {
						$textarea_row_size = 10;
						?>
						<input type="hidden" id ="custom-schema-schema-field" class="custom-schema-schema-field" name="custom-schema-schema-field" value="<?php echo esc_attr( $schema_id ); ?>">
					<?php } ?>
					<textarea name="<?php echo esc_attr( $option['name'] ); ?> " rows="<?php echo esc_attr( $textarea_row_size ); ?>" placeholder = "<?php echo ( 'custom-markup' === $schema_type ) ? esc_html_e( 'Add your snippet here...', 'wp-schema-pro' ) : ''; ?>"><?php echo esc_html( $option_default ); ?></textarea>
					<?php
					break;
				case 'multi-select':
					?>
					<div class="multi-select-wrap" >
						<input type="hidden" name="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $option_default ); ?>" >
						<select multiple="true">
							<?php
							if ( isset( $option['dropdown-content'] ) && ! empty( $option['dropdown-content'] ) ) {
								$option_default = explode( ',', $option_default );
								$option_list    = BSF_AIOSRS_Pro_Schema::get_dropdown_options( $option['dropdown-content'] );
								$option_list    = array_filter( $option_list );

								if ( ! empty( $option_list ) ) {
									foreach ( $option_list as $key => $value ) {
										?>
										<option value="<?php echo esc_attr( $key ); ?>"
												<?php in_array( $value, $option_default, true ) ? selected( 1 ) : ''; ?>
												<?php in_array( $key, $option_default, true ) ? selected( 1 ) : ''; ?>>
											<?php echo esc_attr( $value ); ?>
										</option>
										<?php
									}
								}
								?>
							<?php } ?>
						</select>
					</div>
					<?php
					break;
				case 'dropdown':
					$dropdown_type = isset( $option['dropdown-content'] ) ? $option['dropdown-content'] : '';

					?>
					<select class="wpsp-dropdown-<?php echo esc_attr( $dropdown_type ); ?>" name="<?php echo esc_attr( $option['name'] ); ?>">
						<?php
						if ( $dropdown_type ) {
							$option_list = BSF_AIOSRS_Pro_Schema::get_dropdown_options( $dropdown_type );
							$option_list = array_filter( $option_list );
							if ( ! empty( $option_list ) ) {
								foreach ( $option_list as $key => $value ) {
									?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $option_default, $key ); ?>><?php echo esc_html( $value ); ?></option>
									<?php
								}
							}
						}
						?>
					</select>
					<?php
					break;
				case 'repeater-target':
					$add_button_tooltip = __( 'By clicking on this, it will allow you to fill advanced data.', 'wp-schema-pro' );
					?>
					<div class="bsf-aiosrs-schema-type-wrap">
						<span title="<?php echo esc_attr( $add_button_tooltip ); ?>" class="bsf-aiosrs-schema-heading-help wpsp-show-repeater-target-field dashicons dashicons-plus-alt"></span>
						<span class="bsf-aiosrs-schema-heading-help wpsp-hide-repeater-target-field bsf-hidden dashicons dashicons-dismiss"></span>
						<?php foreach ( $option['fields'] as $fields ) : ?>
							<div class="aiosrs-pro-repeater-table-wrap">
								<a href="#" class="bsf-repeater-close dashicons dashicons-no-alt"></a>
								<table class="aiosrs-pro-repeater-table">
									<tbody>
									<?php foreach ( $fields as $field ) : ?>
										<?php $required_class = ''; ?>
										<tr class="bsf-aiosrs-schema-row bsf-aiosrs-schema-row-text-type ">
											<td class="bsf-aiosrs-schema-row-heading">
												<label class="<?php echo esc_attr( $required_class ); ?>">
													<?php echo esc_attr( $field['label'] ); ?>
													<?php if ( isset( $field['required'] ) && $field['required'] ) { ?>
													<span class="required">*</span>
													<?php } ?>
												</label>
											</td>
											<td class="bsf-aiosrs-schema-row-content">
												<div class="bsf-aiosrs-schema-type-wrap">
													<div class="bsf-aiosrs-schema-custom-text-wrap bsf-hidden-field">
														<?php self::get_field_markup( $field, $schema_id, $schema_type ); ?>
													</div>
												</div>
											</td>
										</tr>
									<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						<?php endforeach; ?>
						<button type="button" class="bsf-repeater-add-new-btn button">+ Add</button>
					</div>
					<?php
					break;
				case 'repeater':
					?>
					<div class="bsf-aiosrs-schema-type-wrap">
						<span title="By clicking on this, it will allow you to fill advanced data." class="bsf-aiosrs-schema-heading-help wpsp-show-repeater-field dashicons dashicons-plus-alt"></span>
						<span class="bsf-aiosrs-schema-heading-help wpsp-hide-repeater-field bsf-hidden dashicons dashicons-dismiss"></span>
						<?php foreach ( $option['fields'] as $index => $fields ) : ?>
							<div class="aiosrs-pro-repeater-table-wrap">
								<a href="#" class="bsf-repeater-close dashicons dashicons-no-alt"></a>
								<table class="aiosrs-pro-repeater-table">
									<tbody>
									<?php
									$status     = 0;
									$field_cont = count( $fields ) - 1;
									?>
									<?php foreach ( $fields as $field ) : ?>


										<?php
										$dep_class = isset( $field['class'] ) ? $field['class'] : '';

										$tmp_post       = $stored['post'];
										$fieldtype      = $field['fieldtype'];
										$field_key      = explode( '[', $fieldtype );
										$fieldtype_name = str_replace( ']', '', $field_key[ count( $field_key ) - 1 ] );
										$field_name     = str_replace( '-fieldtype', '', $fieldtype_name );
										$field_key      = reset( $field_key );

										$repeater_data = '';
										if ( isset( $stored[ $field_key ] ) && is_array( $stored[ $field_key ] ) ) {
											$repeater_data = maybe_unserialize( reset( $stored[ $field_key ] ) );
											$repeater_data = isset( $repeater_data[ $index ] ) ? $repeater_data[ $index ] : '';
										}

										$selected_field = isset( $repeater_data[ $fieldtype_name ] ) ? $repeater_data[ $fieldtype_name ] : '';

										if ( ! $selected_field ) {
											$selected_field = $field['global_fieldtype'];
										}

										if ( isset( $repeater_data[ $field_name ] ) ) {
											$default = $repeater_data[ $field_name ];
											if ( empty( $default ) || 'none' === $default ) {
												$default        = $field['global_default'];
												$selected_field = $field['global_fieldtype'];
											}
										} elseif ( isset( $field['global_default'] ) && ! empty( $field['global_default'] ) ) {
											$default = $field['global_default'];
										} else {
											// Backward compatibility.
											$default = isset( $stored[ $field_key . '-' . $index . '-' . $field_name ][0] ) ? $stored[ $field_key . '-' . $index . '-' . $field_name ][0] : '';
										}

										$required_class = '';
										if ( isset( $field['required'] ) && $field['required'] ) {
											if ( empty( $default ) ) {
												$required_class = 'wpsp-required-error-field';
											} elseif ( 'global-field' === $selected_field ) {
												$gbl_data = BSF_AIOSRS_Pro_Schema_Template::get_post_data( $tmp_post, $default );
												if ( empty( $gbl_data ) || 'Auto Draft' === $gbl_data || 'none' === $gbl_data ) {
													$required_class = 'wpsp-required-error-field';
												}
											}
										}

										?>

										<?php if ( 0 === $status % 2 ) : ?>
										<tr class="bsf-aiosrs-schema-row bsf-aiosrs-schema-row-text-type ">
										<?php endif; ?>
											<td class="bsf-aiosrs-schema-row-heading <?php echo esc_attr( $dep_class ); ?>">
												<label class="<?php echo esc_attr( $required_class ); ?>">
													<?php echo esc_attr( $field['label'] ); ?>

													<?php if ( isset( $field['required'] ) && $field['required'] ) { ?>
														<span class="required">*</span>
													<?php } ?>

												</label>
											</td>
											<td class="bsf-aiosrs-schema-row-content <?php echo esc_attr( $dep_class ); ?>">
												<div class="bsf-aiosrs-schema-type-wrap">
													<div class="bsf-aiosrs-schema-custom-text-wrap bsf-hidden-field">

														<div class="wpsp-local-fields">

															<input class="wpsp-default-hidden-value" type="hidden" name="<?php echo esc_attr( $field['name'] ); ?>" value="<?php echo esc_attr( $default ); ?>">
															<input class="wpsp-default-hidden-fieldtype" type="hidden" name="<?php echo esc_attr( $field['fieldtype'] ); ?>" value="<?php echo esc_attr( $selected_field ); ?>">

															<?php
															$field['attr'] = 'disabled';
															$field['name'] = $field_key . '[' . $index . '][' . $field_name . '-connected]';
															?>

															<div data-type='global-field'
																class='wpsp-parent-field wpsp-connect-field wpsp-connected-group <?php echo 'global-field' === $selected_field ? '' : 'bsf-hidden'; ?>'>
																<div title="" class="bsf-aiosrs-schema-heading-help wpsp-connected-field">
																	<span class="dashicons dashicons-admin-links wpsp-connected-icon"></span>
																</div>
																<?php BSF_AIOSRS_Pro_Schema::render_meta_box_dropdown( $field, $default, true ); ?>
																<span class="wpsp-field-close dashicons dashicons-dismiss"></span>


															</div>

															<div data-type='custom-field' class='wpsp-parent-field wpsp-custom-field <?php echo 'custom-field' === $selected_field ? '' : 'bsf-hidden'; ?>'>
																<?php
																$field['name']    = $field_key . '[' . $index . '][' . $field_name . '-custom]';
																$field['default'] = $default;
																self::get_field_markup( $field, $schema_id, $schema_type )
																?>
																<span class="wpsp-custom-field-connect dashicons dashicons-admin-tools"></span>
															</div>

															<?php $option['name'] = $field_key . '[' . $index . '][' . $field_name . '-specific]'; ?>
															<div data-type="specific-field" class="wpsp-parent-field wpsp-specific-field <?php echo 'specific-field' === $selected_field ? '' : 'bsf-hidden'; ?>">
																<select id="<?php echo esc_attr( $field['name'] ); ?>" name="<?php echo esc_attr( $option['name'] ); ?>"
																		class="bsf-aiosrs-schema-select2 bsf-aiosrs-schema-specific-field wpsp-specific-field" >
																	<?php if ( $default ) { ?>
																		<option value="<?php echo esc_attr( $default ); ?>" selected="selected" ><?php echo esc_html( preg_replace( '/^_/', '', esc_html( str_replace( '_', ' ', $default ) ) ) ); ?></option>
																	<?php } ?>
																</select>
																<span class="wpsp-specific-field-connect dashicons dashicons-admin-tools"></span>
															</div>

														</div>


													</div>
												</div>
											</td>

										<?php if ( ( 1 === $status % 2 ) || ( $field_cont === $status ) ) : ?>
										</tr>
										<?php endif; ?>
										<?php $status++; ?>
									<?php endforeach; ?>
									</tbody>
								</table>
							</div>

						<?php endforeach; ?>
						<button type="button" class="bsf-repeater-add-new-btn button">+ Add</button>
					</div>
					<?php
					break;
				default:
					break;
			}
			?>
			</div>
			<?php
		}

		/**
		 * Get star ratings.
		 *
		 * @param  integer $rating   Rarting.
		 * @param  boolean $disabled Rating should be disable or not.
		 * @return void
		 */
		public static function get_star_rating_markup( $rating = 0, $disabled = false ) {
			?>
			<div class="aiosrs-star-rating-wrap <?php echo $disabled ? 'disabled' : ''; ?>">
			<?php
			if ( ! is_numeric( $rating ) ) {
				$rating = 0;
			}
			$rating     = ( is_null( $rating ) || empty( $rating ) ) ? 0 : $rating;
			$rating     = ( $rating > 5 ) ? 5 : $rating;
			$rating     = ( $rating < 0 ) ? 0 : $rating;
			$star_index = 1;
			$icon       = 'dashicons-star-filled';
			while ( $star_index <= 5 ) {
				if ( $star_index > $rating ) {
					$is_half = $star_index - $rating;
					$icon    = ( is_float( $is_half ) && $is_half < 1 ) ? 'dashicons-star-half' : 'dashicons-star-empty';
				}
				?>
				<span class="aiosrs-star-rating dashicons <?php echo esc_attr( $icon ); ?>" data-index="<?php echo esc_attr( $star_index++ ); ?>"></span>
				<?php
			}
			?>
			</div>
			<?php
		}

		/**
		 * Filter Post repeater array.
		 *
		 * @param array $array post array.
		 * @return array
		 */
		public function filter_post_array( array &$array ) {

			array_walk_recursive(
				$array,
				function ( &$value ) {

					$value = filter_var( trim( $value ), FILTER_DEFAULT ); // phpcs:ignore WordPressVIPMinimum.Security.PHPFilterFunctions.RestrictedFilter
				}
			);

			return array_values( $array );
		}

		/**
		 * Metabox Save
		 *
		 * @param  number $post_id Post ID.
		 *
		 * @return void
		 */
		public function save_meta_box( $post_id ) {

			// Checks save status.
			$is_autosave = wp_is_post_autosave( $post_id );
			$is_revision = wp_is_post_revision( $post_id );

			$is_valid_nonce = ( isset( $_POST['aiosrs-pro-custom-meta'] ) && wp_verify_nonce( sanitize_text_field( $_POST['aiosrs-pro-custom-meta'] ), basename( __FILE__ ) ) );

			// Exits script depending on save status.
			if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
				return;
			}

			/**
			 * Get meta options
			 */
			$this->init_static_fields( $post_id );
			$post_meta  = self::$meta_options;
			$meta_value = null;
			foreach ( $post_meta as $key => $data ) {
				if ( is_numeric( $key ) ) {
					// Sanitize values.
					$sanitize_filter = ( isset( $data['type'] ) ) ? $data['type'] : 'text';

					switch ( $sanitize_filter ) {

						case 'FILTER_SANITIZE_STRING':
							if ( isset( $data['name'] ) ) {
								$meta_value = filter_input( INPUT_POST, $data['name'], FILTER_DEFAULT ); // phpcs:ignore WordPressVIPMinimum.Security.PHPFilterFunctions.RestrictedFilter
							}
							break;

						case 'FILTER_SANITIZE_URL':
							if ( isset( $data['name'] ) ) {
								$meta_value = filter_input( INPUT_POST, $data['name'], FILTER_SANITIZE_URL );
							}
							break;

						case 'FILTER_SANITIZE_NUMBER_INT':
							if ( isset( $data['name'] ) ) {
								$meta_value = filter_input( INPUT_POST, $data['name'], FILTER_SANITIZE_NUMBER_INT );
							}
							break;

						case 'repeater-target':
						case 'repeater':
							if ( isset( $data['name'] ) ) {
								$meta_value = isset( $_POST[ $data['name'] ] ) ? $_POST[ $data['name'] ] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
							}

							break;

						default:
							if ( isset( $data['name'] ) ) {
								$meta_value = filter_input( INPUT_POST, $data['name'], FILTER_DEFAULT ); // phpcs:ignore WordPressVIPMinimum.Security.PHPFilterFunctions.RestrictedFilter
							}
							break;
					}

					if ( isset( $data['name'] ) ) {
						update_post_meta( $post_id, $data['name'] . '-fieldtype', filter_input( INPUT_POST, $data['name'] . '-fieldtype', FILTER_DEFAULT ) ); // phpcs:ignore WordPressVIPMinimum.Security.PHPFilterFunctions.RestrictedFilter
						update_post_meta( $post_id, $data['name'], $meta_value );
					}
				}
			}

			// Deleteing the cached structured data.
			delete_post_meta( $post_id, BSF_AIOSRS_PRO_CACHE_KEY );
		}
	}
}



/**
 * Kicking this off by calling 'get_instance()' method
 */
BSF_AIOSRS_Pro_Custom_Fields_Markup::get_instance();
