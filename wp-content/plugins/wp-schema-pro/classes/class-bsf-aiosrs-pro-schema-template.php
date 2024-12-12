<?php
/**
 * Schemas Template.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema_Template' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema_Template {


		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 *  Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Get post data by tupe.
		 *
		 * @param array  $post post object array.
		 * @param string $schema_key schema key.
		 * @param bool   $single is single.
		 * @param bool   $is_available global post compatibility.
		 * @return array|bool|false|int|mixed|string|void|WP_Error
		 */
		public static function get_post_data( $post, $schema_key, $single = true, $is_available = true ) {
			$value = '';
			switch ( $schema_key ) {
				case 'blogname':
					$value        = get_bloginfo( 'name' );
					$is_available = true;
					break;

				case 'blogdescription':
					$value        = get_bloginfo( 'description' );
					$is_available = true;
					break;

				case 'site_url':
					$value        = get_bloginfo( 'url' );
					$is_available = true;
					break;

				case 'site_logo':
					$general_settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-general-settings'];
					if ( isset( $general_settings['site-logo-custom'] ) && ! empty( $general_settings['site-logo-custom'] ) ) {
						$value = isset( $general_settings['site-logo-custom'] ) ? $general_settings['site-logo-custom'] : '';
					} else {
						if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
								$value = get_theme_mod( 'custom_logo' );
						}
					}
					$is_available = true;
					break;

				case 'featured_img':
				case 'featured_image':
					$sp_default  = '';
					$default_img = BSF_AIOSRS_Pro_Helper::$settings['aiosrs-pro-settings'];
					// default_image.
					if ( $default_img['default_image'] ) {
						$logo_image = wp_get_attachment_image_src( $default_img['default_image'], 'full' );
						$sp_default = self::get_image_schema( $logo_image, 'ImageObject' );
					}
					$feature_img = get_post_thumbnail_id( $post['ID'] );
					if ( ! empty( $feature_img ) ) {
						$value = $feature_img;
					} elseif ( ! empty( $sp_default['url'] ) ) {
						$value = $sp_default['url'];
					} else {
						$value = '';
					}
					$is_available = true;
					break;

				case 'post_title':
					$value        = $post[ $schema_key ];
					$is_available = true;
					break;

				case 'post_excerpt':
				case 'post_content':
					$value        = do_shortcode( $post[ $schema_key ] );
					$is_available = true;
					break;

				case 'post_date':
					$value        = get_the_date( 'Y-m-d\TH:i:s', $post['ID'] );
					$is_available = true;
					break;

				case 'post_modified':
					$value        = get_the_modified_date( 'Y-m-d\TH:i:s', $post['ID'] );
					$is_available = true;
					break;

				case 'post_permalink':
					$value        = get_permalink( $post['ID'] );
					$is_available = true;
					break;

				case 'author_name':
					$author_data  = get_userdata( $post['post_author'] );
					$value        = $author_data->display_name;
					$is_available = true;
					break;

				case 'author_first_name':
					$author_data  = get_userdata( $post['post_author'] );
					$value        = isset( $author_data->first_name ) ? $author_data->first_name : $author_data->display_name;
					$is_available = true;
					break;

				case 'author_last_name':
					$author_data  = get_userdata( $post['post_author'] );
					$value        = isset( $author_data->last_name ) ? $author_data->last_name : $author_data->display_name;
					$is_available = true;
					break;
				case 'author_url':
					$author_data  = get_userdata( $post['post_author'] );
					$author_name  = $author_data->user_nicename;
					$author_url   = get_author_posts_url( $post['ID'] );
					$value        = $author_url . '' . $author_name;
					$is_available = true;
					break;

				case 'author_image':
					$value        = array(
						0 => get_avatar_url( $post['post_author'] ),
						1 => 96,
						2 => 96,
					);
					$is_available = true;
					break;

				default:
					$value = get_post_meta( $post['ID'], $schema_key, $single );
					if ( function_exists( 'acf' ) && class_exists( 'acf' ) ) {
						$acf_value = get_field( $schema_key, $post['ID'] );
						if ( null === $acf_value ) {
							$acf_value = get_field( $schema_key, 'user_' . $post['post_author'] );
						}
						if ( $acf_value ) {
							$value = $acf_value;
						}
					}
					if ( is_array( $value ) ) {
						$selected = array();
						foreach ( $value as $val ) {
							if ( is_array( $val ) && array_key_exists( 'value', $val ) ) {
								$selected[] = $val['value'];
							} else {
								$selected[] = $val;
							}
						}
						if ( ! empty( $selected ) ) {
							$value = implode( ',', $selected );
						} else {
							$value = ''; // or any default value if needed
						}
					}
					break;
			}

			if ( ! $is_available && empty( $value ) ) {

				if ( 'none' === $schema_key ) {
					return '';
				}
			}
			$review_type_keys = array( 'bsf-aiosrs-product', 'bsf-aiosrs-book', 'bsf-aiosrs-course', 'bsf-aiosrs-event', 'bsf-aiosrs-local-business', 'bsf-aiosrs-recipe', 'bsf-aiosrs-software-application', 'bsf-aiosrs-movie', 'bsf-aiosrs-organization' );
			if ( in_array( $schema_key, $review_type_keys, true ) ) {
				$value = substr( $schema_key, 11 );

			}
			return $value;

		}

		/**
		 * Get Meta value by key
		 *
		 * @param  int       $schema_id Schema Id.
		 * @param  int|array $post Post Array.
		 * @param  string    $meta_data Schema Meta data.
		 * @param  string    $key Post meta key.
		 * @param  string    $type field type.
		 * @param  string    $create_field create custom field name.
		 * @param  boolean   $single get_post_meta in array or single.
		 * @return html
		 */
		public static function get_meta_value( $schema_id = 0, $post = 0, $meta_data = array(), $key = '', $type = 'text', $create_field = '', $single = true ) {

			$item_review_rating = isset( $meta_data['schema-type'] ) ? $meta_data['schema-type'] . '-rating' : '';
			$item_review_count  = isset( $meta_data['schema-type'] ) ? $meta_data['schema-type'] . '-review-count' : '';
			$schema_key         = isset( $meta_data[ $key ] ) ? $meta_data[ $key ] : '';
			if ( 'schema-type' === $key || 'reviewer-type' === $key ) {
				return $schema_key;
			} elseif ( 'review-count' === $key && 'accept-user-rating' === $meta_data['rating'] || $item_review_count === $key && 'accept-user-rating' === $meta_data[ $item_review_rating ] ) {
				// Get aggrigate rating count.
				$review_counts = get_post_meta( $post['ID'], 'bsf-schema-pro-review-counts-' . $schema_id, $single );
				return ! empty( $review_counts ) ? $review_counts : 0;
			}
			if ( 'rating' === $key && 'accept-user-rating' === $meta_data['rating'] || $item_review_count === $key && 'accept-user-rating' === $meta_data[ $item_review_rating ] ) {
				$rating = get_post_meta( $post['ID'], 'bsf-schema-pro-rating-' . $schema_id, $single );
				return ! empty( $rating ) ? $rating : 0;
			}

			if ( empty( $post ) || empty( $schema_key ) || 'none' === $schema_key ) {
				$value = '';
			} else {
				switch ( $schema_key ) {

					case 'custom-text':
					case 'fixed-text':
						$value = isset( $meta_data[ $key . '-' . $schema_key ] ) ? $meta_data[ $key . '-' . $schema_key ] : '';
						break;

					case 'specific-field':
						$meta_key = isset( $meta_data[ $key . '-' . $schema_key ] ) ? $meta_data[ $key . '-' . $schema_key ] : '';
						$value    = ! empty( $meta_key ) ? get_post_meta( $post['ID'], $meta_key, $single ) : '';
						break;

					case 'create-field':
						$value = get_post_meta( $post['ID'], $create_field, $single );
						break;
					case 'site_logo':
						$general_settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-general-settings'];
						$logo_id          = get_post_thumbnail_id( $post['ID'] );
						if ( isset( $general_settings['site-logo-custom'] ) && ! empty( $general_settings['site-logo-custom'] ) ) {
							$logo_id = isset( $general_settings['site-logo-custom'] ) ? $general_settings['site-logo-custom'] : '';
						} else {
							if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
									$logo_id = get_theme_mod( 'custom_logo' );
							}
						}
						if ( ! empty( $logo_id ) ) {
							$value = self::get_image_object( $logo_id, $key );
						} else {
							$value = '';
						}

						break;
					case 'accept-user-rating':
						$value = get_post_meta( $post['ID'], 'bsf-schema-pro-rating-' . $schema_id, $single );
						if ( empty( $value ) ) {
							$value = 0;
						}
						break;

					default:
						$value = self::get_post_data( $post, $schema_key, $single );
						break;
				}

				if ( 'image' === $type && ! empty( $value ) ) {
					$value = self::get_image_object( $value, $key );
				} elseif ( 'date' === $type && ! empty( $value ) ) {
					$value = gmdate( 'Y-m-d\TH:i:s', strtotime( $value ) );
				}
			}

			return $value;
		}

		/**
		 * Logo Image Sizes
		 *
		 * @since 1.0.2
		 * @param array $sizes Sizes.
		 *
		 * @return array
		 */
		public static function logo_image_sizes( $sizes ) {

			if ( is_array( $sizes ) ) {

				$sizes['aiosrs-logo-size'] = array(
					'width'  => 600,
					'height' => 60,
					'crop'   => false,
				);
			}

			return $sizes;
		}

		/**
		 * Generate logo image by its width.
		 *
		 * @since 1.0.2
		 * @param int $image_id Image id.
		 */
		public static function generate_logo_by_width( $image_id ) {
			if ( $image_id ) {

				$image = get_post( $image_id );

				if ( $image ) {
					$fullsizepath = get_attached_file( $image->ID );

					if ( false !== $fullsizepath || file_exists( $fullsizepath ) ) {

						require_once ABSPATH . 'wp-admin/includes/image.php';
						$metadata = wp_generate_attachment_metadata( $image->ID, $fullsizepath );

						if ( ! is_wp_error( $metadata ) && ! empty( $metadata ) ) {
							wp_update_attachment_metadata( $image->ID, $metadata );
						}
					}
				}
			}
		}

		/**
		 * Get Field type.
		 *
		 * @param  string $type   schema type.
		 * @param  string $key    schema field key.
		 * @param  string $parent schema parent field key.
		 * @return string
		 */
		public static function get_field_type( $type, $key, $parent = '' ) {
			$schema_fields    = BSF_AIOSRS_Pro_Schema::$schema_meta_fields;
			$review_image_key = substr( $key, -5 );
			if ( empty( $parent ) && isset( $schema_fields[ 'bsf-aiosrs-' . $type ]['subkeys'][ $key ]['type'] ) ) {
				return $schema_fields[ 'bsf-aiosrs-' . $type ]['subkeys'][ $key ]['type'];
			} elseif ( isset( $schema_fields[ 'bsf-aiosrs-' . $type ]['subkeys'][ $parent ]['fields'][ $key ]['type'] ) ) {
				return $schema_fields[ 'bsf-aiosrs-' . $type ]['subkeys'][ $parent ]['fields'][ $key ]['type'];
			} elseif ( 'review' === $type && empty( $parent ) && isset( $review_image_key ) && 'image' === $review_image_key ) {
				return 'image';
			}
			return 'text';
		}

		/**
		 * Strip shortcode from Content.
		 *
		 * @param  string $content   schema Description.
		 * @param  bool   $do_shortcode   Condition.
		 * @return string
		 */
		public static function strip_markup( $content, $do_shortcode = false ) {
			if ( self::maybe_do_shortcode( $do_shortcode ) ) {
				$content = do_shortcode( $content );
			}

			return wp_strip_all_tags( $content );
		}

		/**
		 * Check page builders.
		 *
		 * @param  bool $do_shortcode  Condition.
		 * @return bool
		 */
		public static function maybe_do_shortcode( $do_shortcode ) {
			$status = false;

			if ( class_exists( 'FLBuilderModel' ) || class_exists( 'FusionBuilder' ) || class_exists( 'Vc_Manager' ) || class_exists( 'ET_Builder_Module' ) || true === $do_shortcode ) {
				$status = true;
			}

			return apply_filters( 'schema_pro_maybe_do_shortcode', $status );
		}


		/**
		 * Get Schema Field.
		 *
		 * @param  string $type  schema type.
		 * @param  string $field schema field.
		 * @return string
		 */
		public static function get_schema_field( $type, $field ) {
			$schema_fields = BSF_AIOSRS_Pro_Schema::$schema_meta_fields;
			if ( isset( $schema_fields[ 'bsf-aiosrs-' . $type ][ $field ] ) ) {
				return $schema_fields[ 'bsf-aiosrs-' . $type ][ $field ];
			}
			return '';
		}

		/**
		 * Get Image field Schema markup.
		 *
		 * @param  array  $data_image Image data.
		 * @param  string $type Image type ImageObject|URL|any.
		 * @return array
		 */
		public static function get_image_schema( $data_image, $type = 'any' ) {

			$result = array();
			switch ( $type ) {
				case 'URL':
					if ( is_array( $data_image ) ) {
						if ( isset( $data_image[0] ) && ! empty( $data_image[0] ) ) {
							$result = esc_url( $data_image[0] );
						}
					} else {
						$images = explode( ',', $data_image );
						if ( filter_var( $images[0], FILTER_VALIDATE_URL ) ) {
							$result = esc_url( $images[0] );
						}
					}
					break;

				case 'ImageObject':
					if ( is_array( $data_image ) ) {

						$result['@type'] = 'ImageObject';
						if ( isset( $data_image[0] ) && ! empty( $data_image[0] ) ) {
							$result['url'] = esc_url( $data_image[0] );
						}
						if ( isset( $data_image[1] ) && ! empty( $data_image[1] ) ) {
							$result['width'] = (int) esc_html( $data_image[1] );
						}
						if ( isset( $data_image[2] ) && ! empty( $data_image[2] ) ) {
							$result['height'] = (int) esc_html( $data_image[2] );
						}
					} else {
						$images = explode( ',', $data_image );
						if ( isset( $images[0] ) && '' !== $images[0] ) {
							$image_object = getimagesize( $images[0] );
							if ( $image_object ) {

								$result['@type']               = 'ImageObject';
								$result['url']                 = esc_url( $images[0] );
								list( $width, $height, $type ) = $image_object;
								$result['width']               = (int) esc_html( $width );
								$result['height']              = (int) esc_html( $height );
							}
						}
					}
					break;

				case 'ImageObject2':
					if ( is_array( $data_image ) ) {

						$result['@type'] = 'ImageObject';
						if ( isset( $data_image[0] ) && ! empty( $data_image[0] ) ) {
							$result['url'] = esc_url( $data_image[0] );
						}
					} else {
						$images       = explode( ',', $data_image );
						$image_object = getimagesize( $images[0] );
						if ( $image_object ) {

							$result['@type'] = 'ImageObject';
							$result['url']   = esc_url( $images[0] );
						}
					}
					break;

				default:
					if ( is_array( $data_image ) ) {

						$result['@type'] = 'ImageObject';

						if ( isset( $data_image[0] ) && ! empty( $data_image[0] ) ) {
							$result['url'] = esc_url( $data_image[0] );
						}
						if ( isset( $data_image[1] ) && ! empty( $data_image[1] ) ) {
							$result['width'] = (int) esc_html( $data_image[1] );
						}
						if ( isset( $data_image[2] ) && ! empty( $data_image[2] ) ) {
							$result['height'] = (int) esc_html( $data_image[2] );
						}
					} else {
						$image_urls = array();
						$images     = explode( ',', $data_image );
						foreach ( $images as $image ) {
							if ( filter_var( $image, FILTER_VALIDATE_URL ) ) {
								$image_urls[] = esc_url( $image );
							}
						}
						$result = $image_urls;
					}
					break;
			}

			return $result;
		}

		/**
		 * Get Schema.
		 *
		 * @since 1.1.0
		 * @param  int    $post_id     Post Id.
		 * @param  string $type        Schema type.
		 * @return array
		 */
		public static function get_global_schema( $post_id, $type ) {

			$path = BSF_AIOSRS_PRO_DIR . 'classes/schema/global/class-bsf-aiosrs-pro-schema-global-' . $type . '.php';
			if ( ! file_exists( $path ) ) {
				return array();
			}

			require_once $path;
			$class_name = 'BSF_AIOSRS_Pro_Schema_Global_' . str_replace( '-', '_', ucfirst( $type ) );
			if ( class_exists( $class_name ) ) {
				$schema_instance = new $class_name();
				$post = get_post( $post_id, ARRAY_A );
				if ( null !== $post ) {
					return $schema_instance->render( $post );
				}
			}
		}

		/**
		 * Get Schema.
		 *
		 * @param  int    $post_id     Post Id.
		 * @param  int    $schema_id   Schema Id.
		 * @param  string $type        Schema type.
		 * @param  array  $schema_data Schema Meta Data.
		 * @return array
		 */
		public static function get_schema( $post_id, $schema_id, $type, $schema_data ) {

			$local_meta = get_post_meta( $post_id );
			$data       = array();
			$post       = get_post( $post_id, ARRAY_A );
			foreach ( $schema_data as $key => $value ) {
				$field_type = self::get_field_type( $type, $key );

				if ( 'repeater' === $field_type && is_array( $value ) ) {
					$values                = array();
					$repeater_field_values = isset( $local_meta[ $type . '-' . $schema_id . '-' . $key ][0] ) ? $local_meta[ $type . '-' . $schema_id . '-' . $key ][0] : '';

					$repeater_field_values = maybe_unserialize( $repeater_field_values );
					if ( ! is_array( $repeater_field_values ) || empty( $repeater_field_values ) ) {

						$repeater_field_values = $value;
					}

					foreach ( $repeater_field_values as $index => $repeater_values ) {
						foreach ( $repeater_values as $repeater_key => $repeater_value ) {
							$field_type = self::get_field_type( $type, $repeater_key, $key );

							// Local support.
							$local_data = isset( $local_meta[ $type . '-' . $schema_id . '-' . $key ][0] ) ? $local_meta[ $type . '-' . $schema_id . '-' . $key ][0] : '';

							$local_data = maybe_unserialize( $local_data );
							if ( isset( $local_data[ $index ][ $repeater_key ] ) && isset( $local_data[ $index ][ $repeater_key . '-fieldtype' ] ) ) {
									$this_fieldtype = $local_data[ $index ][ $repeater_key . '-fieldtype' ];
									$this_fieldval  = $local_data[ $index ][ $repeater_key ];
									$this_fieldval  = self::prepare_global_data( $this_fieldtype, $field_type, $this_fieldval, $post );
								if ( ! empty( $this_fieldval ) ) {
									$values[ $index ][ $repeater_key ] = $this_fieldval;
									continue;
								}
							}

							$create_field                      = $type . '-' . $schema_id . '-' . $key . '-' . $index . '-' . $repeater_key;
							$values[ $index ][ $repeater_key ] = self::get_meta_value( $schema_id, $post, $repeater_values, $repeater_key, $field_type, $create_field );
						}
					}
					$data[ $key ] = $values;
				} elseif ( 'repeater-target' === $field_type && is_array( $value ) ) {

					$data[ $key ] = get_post_meta( $post_id, $type . '-' . $schema_id . '-' . $key, true );

				} else {
					$create_field = $type . '-' . $schema_id . '-' . $key;
					// Accept user rating.
					if ( 'accept-user-rating' === $value ) {
						$data[ $key ] = self::get_meta_value( $schema_id, $post, $schema_data, $key, $field_type, $create_field );
						continue;
					}
					// Local support.

					if ( isset( $local_meta[ $create_field ][0] ) && isset( $local_meta[ $create_field . '-fieldtype' ][0] ) ) {
						$this_fieldtype = $local_meta[ $create_field . '-fieldtype' ][0];
						$this_fieldval  = $local_meta[ $create_field ][0];

						$this_fieldval = self::prepare_global_data( $this_fieldtype, $field_type, $this_fieldval, $post );

						if ( ( 'datetime-local' === $field_type || 'date' === $field_type ) && ! empty( $this_fieldval ) ) {
							$this_fieldval = gmdate( DATE_ISO8601, strtotime( $this_fieldval ) );
						}

						if ( ! empty( $this_fieldval ) ) {
							$data[ $key ] = $this_fieldval;
							continue;
						}
					}

					$data[ $key ] = self::get_meta_value( $schema_id, $post, $schema_data, $key, $field_type, $create_field );
				}
			}

			// When schema validation enabled.
			if ( isset( BSF_AIOSRS_Pro_Helper::$settings['aiosrs-pro-settings']['schema-validation'] ) ) {
				$enabled = BSF_AIOSRS_Pro_Helper::$settings['aiosrs-pro-settings']['schema-validation'];
				if ( '1' === $enabled ) {
					$is_validated = true;
					if ( isset( BSF_AIOSRS_Pro_Schema::$schema_meta_fields[ 'bsf-aiosrs-' . $type ]['subkeys'] ) ) {
						$fields = BSF_AIOSRS_Pro_Schema::$schema_meta_fields[ 'bsf-aiosrs-' . $type ]['subkeys'];
						foreach ( $data as $field_name => $field_val ) {
							if ( isset( $fields[ $field_name ]['type'] ) && 'repeater' === $fields[ $field_name ]['type'] ) {
								if ( is_array( $field_val ) ) {
									foreach ( $field_val as $key => $rep_fields ) {
										$field_sub = $fields[ $field_name ]['fields'];
										foreach ( $rep_fields as $rep_field_name => $rep_field_val ) {
											if ( isset( $field_sub[ $rep_field_name ]['required'] ) && empty( $field_sub[ $rep_field_name ]['required'] ) && empty( $rep_field_val ) ) {
													$is_validated = false;
													break;
											}
										}
									}
								}
							} else {
								if ( isset( $fields[ $field_name ]['required'] ) && $fields[ $field_name ]['required'] && empty( $field_val ) ) {
										$is_validated = false;
										break;
								}
							}
						}
					}
					if ( ! $is_validated ) {
						return array();
					}
				}
			}

			$path  = self::get_schema_field( $type, 'path' );
			$path .= 'class-bsf-aiosrs-pro-schema-' . $type . '.php';
			if ( file_exists( $path ) ) {
				require_once $path;

				$class_name = 'BSF_AIOSRS_Pro_Schema_' . str_replace( '-', '_', ucfirst( $type ) );
				if ( class_exists( $class_name ) ) {
					$schema_instance = new $class_name();
					if ( null !== $post ) {
						return $schema_instance->render( $data, $post );
					}
				}
			}
			return array();

		}

		/**
		 * Get Breadcrumb List
		 *
		 * @since 1.1.0
		 * @return array
		 */
		public static function get_breadcrumb_list() {

			global $wp_query;
			$item = array();
			$args = apply_filters(
				'wp_schema_pro_breadcrumb_defaults',
				array(
					'home'           => __( 'Home', 'wp-schema-pro' ),
					'show_blog'      => false,
					'archive-prefix' => __( 'Archives for ', 'wp-schema-pro' ),
					'author-prefix'  => __( 'Archives for ', 'wp-schema-pro' ),
					'search-prefix'  => __( 'You searched for ', 'wp-schema-pro' ),
				)
			);

			/* Link to front page. */
			if ( apply_filters( 'wp_schema_pro_link_to_frontpage', true ) && ! is_front_page() ) {
					$home_url = home_url( '/' );
					$item[]   = array(
						'url'   => $home_url,
						'title' => $args['home'],
					);
			}

			/* Front page. */
			if ( is_home() ) {
				// Blog page.
				$id        = get_option( 'page_for_posts' );
				$home_page = get_page( $wp_query->get_queried_object_id() );
				$item      = array_merge( $item, self::get_parents( $home_page->post_parent ) );
				$item[]    = array(
					'url'   => '',
					'title' => get_the_title( $id ),
				);
			} elseif ( is_singular() ) {
				/* If viewing a singular post. */
				$post             = $wp_query->get_queried_object();
				$post_id          = (int) $wp_query->get_queried_object_id();
				$post_type        = $post->post_type;
				$post_type_object = get_post_type_object( $post_type );
				$post_category    = '';
				$tag_url          = '';
				$tag_name         = '';

				if ( 'post' === $post_type ) {
					$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-breadcrumb-setting'];
					if ( isset( $settings['post'] ) && '1' !== $settings['post'] ) {
						if ( 'post_tag' === $settings['post'] ) {
							/**
							* Get tags name and link
							*/
							$all_tag_info = get_the_tags( $post_id );
							if ( is_array( $all_tag_info ) || is_object( $all_tag_info ) ) {
								foreach ( $all_tag_info as $keys ) {
									$tag_url  = get_tag_link( $keys->term_id );
									$tag_name = $keys->name;
								}
								$item[] = array(
									'url'   => $tag_url,
									'title' => $tag_name,
								);
							}
						} elseif ( 'category' === $settings['post'] ) {
							if ( has_category( $post_category, $post_id ) ) {
								$post_category  = get_the_category();
								$first_category = $post_category[0];
								$cat_link       = get_category_link( $first_category );
								$cat_name       = $first_category->name;

								$item[] = array(
									'url'   => $cat_link,
									'title' => $cat_name,
								);
							}
						} elseif ( 'post_format' === $settings['post'] ) {
							$format = get_post_format( $post_id );
							if ( false !== get_post_format() ) {
								$item[] = array(
									'url'   => get_post_format_link( $format ),
									'title' => $format,
								);
							}
						}
					}
				}

				if ( 'post' === $post_type && $args['show_blog'] ) {
					$item[] = array(
						'url'   => get_permalink( get_option( 'page_for_posts' ) ),
						'title' => get_the_title( get_option( 'page_for_posts' ) ),
					);
				}
				if ( 'page' !== $post_type ) {
					/* If there's an archive page, add it. */
					if ( function_exists( 'get_post_type_archive_link' ) && ! empty( $post_type_object->has_archive ) ) {

						$enabaled = apply_filters( 'wp_schema_pro_link_to_specificpage', true, $post_type, $post_type_object->labels->name );

						if ( true === $enabaled ) {
							$item[] = array(
								'url'   => get_post_type_archive_link( $post_type ),
								'title' => $post_type_object->labels->name,
							);
						}

						$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-breadcrumb-setting'];

						if ( 'product_tag' === $settings['product'] ) {
							$current_tags = get_the_terms( get_the_ID(), 'product_tag' );
							if ( is_array( $current_tags ) || is_object( $current_tags ) ) {
								foreach ( $current_tags as $keys ) {
									$tag_url  = get_tag_link( $keys->term_id );
									$tag_name = $keys->name;

									$item[] = array(
										'url'   => $tag_url,
										'title' => $tag_name,
									);
								}
							}
						} elseif ( 'product_cat' === $settings['product'] ) {

							$terms = get_the_terms( $post->ID, 'product_cat' );
							if ( is_array( $terms ) || is_object( $terms ) ) {
								foreach ( $terms as $term ) {
									$product_cat_id = $term->term_id;
									$prod_cat_link  = esc_url( get_term_link( $product_cat_id, 'product_cat' ) ); // phpcs:ignore
									$prod_cat_name  = $term->name;

									$item[] = array(
										'url'   => $prod_cat_link,
										'title' => $prod_cat_name,
									);
								}
							}
						} elseif ( 'product_shipping_class' === $settings['product'] ) {

							$_product = wc_get_product();
							$terms    = get_the_terms( $post->ID, 'product_shipping_class' );
							if ( is_array( $terms ) || is_object( $terms ) ) {
								foreach ( $terms as $term ) {
									$shipping_class_name = $_product->get_shipping_class();

									$prod_shipping_link = get_term_link( $term->term_id, 'product_shipping_class' );

									$item[] = array(
										'url'   => $prod_shipping_link,
										'title' => $shipping_class_name,
									);
								}
							}
						}
					}
					if ( isset( $args[ "singular_{$post_type}_taxonomy" ] ) && is_taxonomy_hierarchical( $args[ "singular_{$post_type}_taxonomy" ] ) ) {
						$terms = wp_get_object_terms( $post_id, $args[ "singular_{$post_type}_taxonomy" ] );
						if ( isset( $terms[0] ) ) {
							$item = array_merge( $item, self::get_term_parents( $terms[0], $args[ "singular_{$post_type}_taxonomy" ] ) );
						}
					}
				}
				$post_parent = ( isset( $wp_query->post->post_parent ) ) ? $wp_query->post->post_parent : '';
				$parents     = self::get_parents( $post_parent );
				if ( ( is_post_type_hierarchical( $wp_query->post->post_type ) || 'attachment' === $wp_query->post->post_type ) && $parents ) {
					$item = array_merge( $item, $parents );
				}
				$item[] = array(
					'url'   => get_permalink( $post_id ),
					'title' => get_the_title(),
				);
			} elseif ( is_archive() ) {
				$settings    = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-breadcrumb-setting'];
				$http        = ( ! empty( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) ? 'https' : 'http';
				$actual_link = ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) ? $http . '://' . sanitize_text_field( $_SERVER['HTTP_HOST'] ) . esc_url_raw( $_SERVER['REQUEST_URI'] ) : '';

				/* If viewing any type of archive. */
				if ( is_category() || is_tag() || is_tax() ) {
					$term    = $wp_query->get_queried_object();
					$parents = self::get_term_parents( $term->parent, $term->taxonomy );
					if ( ( is_taxonomy_hierarchical( $term->taxonomy ) && $term->parent ) && $parents ) {
						$item = array_merge( $item, $parents );
					}
					if ( 'product' === $settings['product_cat'] ) {
						$archive_page_link = get_post_type_archive_link( $settings['product_cat'] );
						$item[]            = array(
							'url'   => $archive_page_link,
							'title' => 'Products',
						);
					} elseif ( 'product' === $settings['product_tag'] ) {

						$archive_page_link = get_post_type_archive_link( $settings['product_cat'] );

						$item[] = array(
							'url'   => $archive_page_link,
							'title' => 'Products',
						);

					}
					// default code.
					$item[] = array(
						'url'   => $actual_link,
						'title' => $term->name,
					);
				} elseif ( function_exists( 'is_post_type_archive' ) && is_post_type_archive() ) {
					$post_type_object = get_post_type_object( get_query_var( 'post_type' ) );
					$item[]           = array(
						'url'   => $actual_link,
						'title' => $post_type_object->labels->name,
					);
				} elseif ( is_date() ) {
					if ( is_day() ) {
						$item[] = array(
							'url'   => $actual_link,
							'title' => $args['archive-prefix'] . get_the_time( 'F j, Y' ),
						);
					} elseif ( is_month() ) {
						$item[] = array(
							'url'   => $actual_link,
							'title' => $args['archive-prefix'] . single_month_title( ' ', false ),
						);
					} elseif ( is_year() ) {
						$item[] = array(
							'url'   => $actual_link,
							'title' => $args['archive-prefix'] . get_the_time( 'Y' ),
						);
					}
				} elseif ( is_author() ) {
					$item[] = array(
						'url'   => $actual_link,
						'title' => $args['author-prefix'] . get_the_author_meta( 'display_name', $wp_query->post->post_author ),
					);
				}
			} elseif ( is_search() ) {
				/* If viewing search results. */
				$item[] = array(
					'url'   => home_url( '/?s=' . get_search_query() ),
					'title' => $args['search-prefix'] . stripslashes( wp_strip_all_tags( get_search_query() ) ),
				);
			}

			return $item;
		}

		/**
		 * Gets parent pages of any post type.
		 *
		 * @since 1.1.0
		 *
		 * @param int $post_id ID of the post whose parents we want.
		 * @return array
		 */
		public static function get_parents( $post_id = '' ) {
			$parents = array();
			if ( 0 === $post_id ) {
				return $parents;
			}
			while ( $post_id ) {
				$page      = get_page( $post_id );
				$parents[] = array(
					'url'   => get_permalink( $post_id ),
					'title' => get_the_title( $post_id ),
				);

				$post_id = $page->post_parent;
			}
			if ( $parents ) {
				$parents = array_reverse( $parents );
			}

			return $parents;
		}

		/**
		 * Searches for term parents of hierarchical taxonomies.
		 *
		 * @since 1.1.0
		 *
		 * @param int           $parent_id The ID of the first parent.
		 * @param object|string $taxonomy The taxonomy of the term whose parents we want.
		 *
		 * @return array
		 */
		public static function get_term_parents( $parent_id = '', $taxonomy = '' ) {
			$parents = array();
			if ( empty( $parent_id ) || empty( $taxonomy ) ) {
				return $parents;
			}
			while ( $parent_id ) {
				$parent    = get_term( $parent_id, $taxonomy );
				$parents[] = array(
					'url'   => get_term_link( $parent, $taxonomy ),
					'title' => $parent->name,
				);
				$parent_id = $parent->parent;
			}
			if ( $parents ) {
				$parents = array_reverse( $parents );
			}

			return $parents;
		}

		/**
		 * Get Image as object.
		 *
		 * @param string $value image id.
		 * @param string $key image type.
		 * @return array|false
		 */
		public static function get_image_object( $value, $key ) {
			if ( is_numeric( $value ) ) {
				$image_id = $value;
				if ( 'site-logo' === $key && apply_filters( 'wp_schema_pro_exclude_logo_optimize', true ) ) {
					// Add logo image size.
					add_filter( 'intermediate_image_sizes_advanced', 'BSF_AIOSRS_Pro_Schema_Template::logo_image_sizes', 10, 2 );
					$value = wp_get_attachment_image_src( $image_id, 'aiosrs-logo-size' );
					if ( isset( $value[3] ) && 1 !== $value[3] ) {
						self::generate_logo_by_width( $image_id );
						$value = wp_get_attachment_image_src( $image_id, 'aiosrs-logo-size' );
					}
					// Remove logo image size.
					remove_filter( 'intermediate_image_sizes_advanced', 'BSF_AIOSRS_Pro_Schema_Template::logo_image_sizes', 10, 2 );
				} else {
					$value = wp_get_attachment_image_src( $image_id, 'full' );
				}
			}
			return $value;
		}

		/**
		 * Prepare gobal data.
		 *
		 * @param string $this_fieldtype this field type.
		 * @param string $field_type field type.
		 * @param string $this_fieldval this field val.
		 * @param array  $post post array.
		 * @return array|bool|false|int|mixed|string|void|WP_Error
		 */
		public static function prepare_global_data( $this_fieldtype, $field_type, $this_fieldval, $post ) {
			if ( 'custom-field' === $this_fieldtype ) {
				if ( 'image' === $field_type ) {
					$this_fieldval = self::get_image_object( $this_fieldval, $field_type );
				}
			} elseif ( 'global-field' === $this_fieldtype ) {
				$this_fieldval = self::get_post_data( $post, $this_fieldval, true, false );
				if ( 'image' === $field_type ) {
					$this_fieldval = self::get_image_object( $this_fieldval, $field_type );
				}
			} elseif ( 'specific-field' === $this_fieldtype ) {
				$this_fieldval = get_post_meta( $post['ID'], $this_fieldval, true );
			}
			return $this_fieldval;
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
BSF_AIOSRS_Pro_Schema_Template::get_instance();
