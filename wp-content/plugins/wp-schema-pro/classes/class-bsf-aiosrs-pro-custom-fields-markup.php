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

			add_action( 'load-post.php', array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
			add_action( 'admin_head', array( $this, 'meta_boxes_style' ) );
			add_shortcode( 'aiosrs_pro_custom_field', array( $this, 'shortcode_callback' ) );

			add_filter( 'wp_schema_pro_field_edit_script_localize', array( $this, 'script_localize_vars' ) );
			add_action( 'wp_ajax_aiosrs_reset_post_rating', array( $this, 'aiosrs_reset_post_rating_callback' ) );
		}


		/**
		 * Filter localize variableds.
		 *
		 * @param  array $vars Localize varible list.
		 * @return array
		 */
		public function script_localize_vars( $vars ) {

			$vars['reset_rating_msg']   = __( 'Do you really want to reset current post rating.', 'wp-schema-pro' );
			$vars['post_id']            = get_the_ID();
			$vars['reset_rating_nonce'] = wp_create_nonce( 'schema-pro-reset-rating' );
			return $vars;
		}

		/**
		 * Rest star rating.
		 *
		 * @return bool
		 */
		public function aiosrs_reset_post_rating_callback() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			wp_verify_nonce( $_POST['nonce'], 'schema-pro-reset-rating' );

			$response = array(
				'success' => false,
			);
			if ( isset( $_POST['post_id'] ) ) {
				$post_id   = $_POST['post_id'];
				$schema_id = $_POST['schema_id'];
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

			$output = '';

			$post_id = empty( $args['post_id'] ) ? get_the_ID() : (int) $args['post_id'];
			$output  = get_metadata( $args['post_type'], $post_id, $args['field_key'], true );

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
			if ( 'aiosrs-schema' === $current_post_type ) {
				return;
			}

			add_action( 'add_meta_boxes', array( $this, 'setup_meta_box' ) );
			add_action( 'save_post', array( $this, 'save_meta_box' ) );
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

						if ( $schema_field_value ) {

							if ( 'create-field' === $schema_value || 'accept-user-rating' === $schema_value ) {

								// Skip review count in case of Accept user rating.
								if ( 'review-count' === $schema_key && 'accept-user-rating' === $schema_meta['rating'] ) {
									continue;
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
								);
							} elseif ( 'repeater' === $schema_field_value['type'] ) {
								foreach ( $schema_value as $repeater_index => $repeater_data ) {
									if ( ! empty( $repeater_data ) ) {
										$repeater_count = count( $schema_value );
										foreach ( $repeater_data as $repeater_key => $repeater_value ) {

											if ( isset( $schema_field_value['fields'][ $repeater_key ] ) ) {
												$schema_repeater_field_value = $schema_field_value['fields'][ $repeater_key ];
												if ( 'create-field' === $repeater_value || 'accept-user-rating' === $repeater_value ) {
													$repeater_field_label  = $schema_repeater_field_value['label'];
													$repeater_field_label .= ( $repeater_count > 1 ) ? ' - ' . ( $repeater_index + 1 ) : '';
													$custom_fields[]       = array(
														'default'          => isset( $schema_meta[ $repeater_key . '-custom-meta-default' ] ) ? $schema_meta[ $schema_key . '-custom-meta-default' ] : '',
														'name'             => $schema_type . '-' . $post_id . '-' . $schema_key . '-' . $repeater_index . '-' . $repeater_key,
														'type'             => $schema_repeater_field_value['type'],
														'label'            => $repeater_field_label,
														'required'         => isset( $schema_repeater_field_value['required'] ) ? $schema_repeater_field_value['required'] : false,
														'dropdown-content' => isset( $schema_repeater_field_value['dropdown-type'] ) ? $schema_repeater_field_value['dropdown-type'] : '',
														'min' => isset( $schema_repeater_field_value['attrs']['min'] ) ? $schema_repeater_field_value['attrs']['min'] : '',
														'step' => isset( $schema_repeater_field_value['attrs']['step'] ) ? $schema_repeater_field_value['attrs']['step'] : '',
														'user-rating'      => 'accept-user-rating' === $schema_value,
														'description'      => isset( $schema_repeater_field_value['description'] ) ? $schema_repeater_field_value['description'] : '',
													);
												}
											}
										}
									}
								}
							}
						}
					}

					if ( ! empty( $custom_fields ) ) {

						$schema_enabled = self::enable_schema_post_option();
						if ( $schema_enabled ) {
							array_unshift(
								$custom_fields,
								array(
									'default'          => 'disabled',
									'name'             => $schema_type . '-' . $post_id . '-enabled-schema',
									'type'             => 'checkbox',
									'label'            => __( 'Enable Schema Markup', 'wp-schema-pro' ),
									'required'         => false,
									'dropdown-content' => '',
									'description'      => '',
									'min'              => '',
									'step'             => '',
								)
							);
						}

						self::$meta_boxes[ $post_id ] = array(
							'ID'          => $post_id,
							'post_title'  => get_the_title( $post_id ),
							'schema_type' => $schema_type,
							'fields'      => $custom_fields,
						);

						self::$meta_options = array_merge( self::$meta_options, $custom_fields );
					}
				}
			}
		}

		/**
		 *  Setup Metabox
		 */
		public function setup_meta_box() {

			$this->init_static_fields();
			if ( ! empty( self::$meta_boxes ) ) {
				$title = __( 'Schema Pro', 'wp-schema-pro' );
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
			$stored    = get_post_meta( $post->ID );
			$first_tab = true;

			do_action( 'aiosrs_pro_custom_fields_markup_before', $post, $stored );
			if ( count( self::$meta_boxes ) > 1 ) { ?>
				<div class="aiosrs-pro-meta-fields-tabs-wrapper">
					<?php
					foreach ( self::$meta_boxes as $key => $meta_box ) {

						$id    = 'aiosrs_pro_custom_meta_box_' . $key;
						$title = ! empty( $meta_box['post_title'] ) ? $meta_box['post_title'] : '&nbsp;';
						?>
						<div class="aiosrs-pro-meta-fields-tab <?php echo $first_tab ? 'active' : ''; ?>" data-tab-id="aiosrs-pro-meta-fields-wrapper-<?php echo esc_attr( $id ); ?>" >
							<label><?php echo esc_html( $title ); ?></label>
						</div>
						<?php
						$first_tab = false;
					}
				// @codingStandardsIgnoreStart
				?>
				</div><?php } // PHP
			?><div class="aiosrs-pro-meta-fields-wrapper">
			<?php // @codingStandardsIgnoreEnd ?>
				<?php $first_tab = true; ?>
				<?php
				foreach ( self::$meta_boxes as $key => $meta_box ) {

					$id           = 'aiosrs_pro_custom_meta_box_' . $key;
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
							<?php foreach ( $meta_options as $key => $option ) { ?>
							<tr class="row">
								<th>
									<?php echo isset( $option['label'] ) ? esc_html( $option['label'] ) : ''; ?>
									<?php if ( isset( $option['required'] ) && true === $option['required'] ) { ?>
										<span class="required">*</span>
									<?php } ?>
									<?php if ( isset( $option['description'] ) && ! empty( $option['description'] ) ) { ?>
										<i class="bsf-aiosrs-schema-heading-help dashicons dashicons-editor-help" title="<?php echo esc_html( $option['description'] ); ?>"></i>
									<?php } ?>
								</th>
								<td>
									<?php self::get_field_markup( $option, $meta_box['ID'], $meta_box['schema_type'] ); ?>
								</td>
							</tr>
						<?php } ?>
						</table>
					</div>
					<?php
					$first_tab = false;
				}
				?>
			</div>
			<?php
			do_action( 'aiosrs_pro_custom_fields_markup_after', $post, $stored );
		}

		/**
		 * Get Field Markup.
		 *
		 * @param  array  $option      Option array.
		 * @param  int    $schema_id   Schema Id.
		 * @param  string $schema_type Schema Type.
		 * @return void
		 */
		public static function get_field_markup( $option, $schema_id, $schema_type ) {
			$option_default = isset( $option['default'] ) ? $option['default'] : '';
			?>
			<div class="aiosrs-pro-custom-field aiosrs-pro-custom-field-<?php echo esc_attr( $option['type'] ); ?>">
			<?php
			switch ( $option['type'] ) {
				case 'text':
				case 'tel':
				case 'time':
				case 'date':
				case 'datetime-local':
					?>
					<input type="<?php echo esc_attr( $option['type'] ); ?>" name="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $option_default ); ?>">
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
						<a href="#" class="aiosrs-reset-rating <?php echo ( 0 === $review_counts ) ? 'reset-disabled' : ''; ?>" data-schema-id="<?php echo esc_attr( $schema_id ); ?>"><?php esc_html_e( 'Reset', 'wp-schema-pro' ); ?></a>
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
						<input type="number" class="bsf-rating-field" step="0.1" min="0" max="5" name="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $option_default ); ?>">
						<?php
						self::get_star_rating_markup( $option_default );
					}
					break;

				case 'time-duration':
					?>
					<input type="hidden" class="time-duration-field" name="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $option_default ); ?>">
					<div class="time-duration-wrap">
						<?php
						if ( ! empty( $option_default ) ) {
							$interval = new DateInterval( $option_default );
						}

							$duration_day  = isset( $interval ) ? str_pad( $interval->format( '%d' ), 2, '0', STR_PAD_LEFT ) : '';
							$duration_hour = isset( $interval ) ? str_pad( $interval->format( '%h' ), 2, '0', STR_PAD_LEFT ) : '';
							$duration_min  = isset( $interval ) ? str_pad( $interval->format( '%i' ), 2, '0', STR_PAD_LEFT ) : '';
							$duration_sec  = isset( $interval ) ? str_pad( $interval->format( '%s' ), 2, '0', STR_PAD_LEFT ) : '';
						?>
						<input type="number" class="time-duration-day" placeholder="DAYS" min="0" value="<?php echo esc_attr( $duration_day ); ?>"><!--
						--><input type="number" class="time-duration-hour" placeholder="HOUR" min="0" max="23" value="<?php echo esc_attr( $duration_hour ); ?>"><!--
						--><input type="number" class="time-duration-min" placeholder="MIN" min="0" max="59" value="<?php echo esc_attr( $duration_min ); ?>"><!--
						--><input type="number" class="time-duration-sec" placeholder="SEC" min="0" max="59" value="<?php echo esc_attr( $duration_sec ); ?>">
					</div>
					<?php
					break;

				case 'image':
					?>
					<input type="hidden" class="single-image-field" name="<?php echo esc_attr( $option['name'] ); ?>" value="<?php echo esc_attr( $option_default ); ?>">
					<?php
					if ( ! empty( $option_default ) ) {
						$image_url = wp_get_attachment_url( $option_default );
					}
					?>
					<div class="image-field-wrap <?php echo ( ! empty( $image_url ) ) ? 'bsf-custom-image-selected' : ''; ?>"">
						<a href="#" class="aiosrs-image-select"><?php esc_html_e( 'Select Image', 'wp-schema-pro' ); ?></a>
						<a href="#" class="aiosrs-image-remove dashicons dashicons-no-alt wp-ui-text-highlight"></a>
						<?php if ( isset( $image_url ) && ! empty( $image_url ) ) : ?>
							<a href="#" class="aiosrs-image-select img"><img src="<?php echo esc_url( $image_url ); ?>" /></a>
						<?php endif; ?>
					</div>
					<?php
					break;

				case 'textarea':
					?>
					<div class="aiosrs-pro-custom-field aiosrs-pro-custom-field-<?php echo esc_attr( $option['type'] ); ?>">
						<textarea name="<?php echo esc_attr( $option['name'] ); ?>"><?php echo esc_attr( $option_default ); ?></textarea>
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
										<option value="<?php echo esc_attr( $key ); ?>" <?php in_array( $key, $option_default, true ) ? selected( 1 ) : ''; ?>><?php echo esc_attr( $value ); ?></option>
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
					?>
					<select name="<?php echo esc_attr( $option['name'] ); ?>">
						<?php
						if ( isset( $option['dropdown-content'] ) && ! empty( $option['dropdown-content'] ) ) {
							$option_list = BSF_AIOSRS_Pro_Schema::get_dropdown_options( $option['dropdown-content'] );
							$option_list = array_filter( $option_list );
							if ( ! empty( $option_list ) ) {
								foreach ( $option_list as $key => $value ) {
									?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $option_default, $key ); ?>><?php echo esc_attr( $value ); ?></option>
									<?php
								}
							}
						}
						?>
					</select>
					<?php
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

			$is_valid_nonce = ( isset( $_POST['aiosrs-pro-custom-meta'] ) && wp_verify_nonce( $_POST['aiosrs-pro-custom-meta'], basename( __FILE__ ) ) ) ? true : false;

			// Exits script depending on save status.
			if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
				return;
			}

			/**
			 * Get meta options
			 */
			$this->init_static_fields( $post_id );
			$post_meta = self::$meta_options;

			foreach ( $post_meta as $key => $data ) {

				// Sanitize values.
				$sanitize_filter = ( isset( $data['type'] ) ) ? $data['type'] : 'text';

				switch ( $sanitize_filter ) {

					case 'FILTER_SANITIZE_STRING':
						$meta_value = filter_input( INPUT_POST, $data['name'], FILTER_SANITIZE_STRING );
						break;

					case 'FILTER_SANITIZE_URL':
						$meta_value = filter_input( INPUT_POST, $data['name'], FILTER_SANITIZE_URL );
						break;

					case 'FILTER_SANITIZE_NUMBER_INT':
						$meta_value = filter_input( INPUT_POST, $data['name'], FILTER_SANITIZE_NUMBER_INT );
						break;

					default:
						$meta_value = filter_input( INPUT_POST, $data['name'], FILTER_DEFAULT );
						break;
				}
				update_post_meta( $post_id, $data['name'], $meta_value );
			}

			// Deleteing the cached structured data.
			delete_post_meta( $post_id, 'wp_schema_pro_optimized_structured_data' );
		}
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
BSF_AIOSRS_Pro_Custom_Fields_Markup::get_instance();
