<?php
/**
 * Schemas - Markup.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Markup' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Markup {


		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $schema_post_result = array();

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
		 *  Constructor
		 */
		public function __construct() {

			$this->init();
		}

		/**
		 * Initalize
		 *
		 * @return void
		 */
		public function init() {
			$settings = BSF_AIOSRS_Pro_Helper::$settings['aiosrs-pro-settings'];

			add_action( 'wp', array( $this, 'disable_astra_theme_schema' ) );
			add_filter( 'the_content', array( $this, 'rating_markup' ) );
			add_shortcode( 'wp_schema_pro_rating_shortcode', array( $this, 'rating_markup' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'wp_ajax_aiosrs_user_rating', array( $this, 'aiosrs_user_rating_callback' ) );
			add_action( 'wp_ajax_nopriv_aiosrs_user_rating', array( $this, 'aiosrs_user_rating_callback' ) );
			add_filter( 'body_class', array( $this, 'wp_schema_body_class' ) );

			if ( isset( $settings['schema-location'] ) ) {

				switch ( $settings['schema-location'] ) {
					case 'head':
						add_action( 'wp_head', array( $this, 'schema_markup' ) );
						add_action( 'wp_head', array( $this, 'global_schemas_markup' ) );
						break;
					case 'footer':
						add_action( 'wp_footer', array( $this, 'schema_markup' ) );
						add_action( 'wp_footer', array( $this, 'global_schemas_markup' ) );
						break;
					default:
						break;
				}
			}
		}

		/**
		 * Adding class to body
		 *
		 * @param  array $classes body classes.
		 * @return array
		 */
		public function wp_schema_body_class( $classes ) {

			$classes[] = 'wp-schema-pro-' . BSF_AIOSRS_PRO_VER;

			return $classes;

		}

		/**
		 * Add Guest user rating
		 *
		 * @return void
		 */
		public function aiosrs_user_rating_callback() {

			check_ajax_referer( 'schema-pro-user-rating', 'nonce' );

			$response = array(
				'success' => false,
			);
			if ( isset( $_POST['post_id'] ) && isset( $_POST['rating'] ) && isset( $_POST['schemaId'] ) ) {
				$post_id    = absint( $_POST['post_id'] );
				$schema_id  = absint( $_POST['schemaId'] );
				$new_rating = absint( $_POST['rating'] );
				if ( $new_rating > 5 ) {
					$new_rating = 5;
				}
				$new_rating = $new_rating <= 0 ? 1 : $new_rating;
				$client_ip  = $this->get_client_ip();

				$all_ratings = get_post_meta( $post_id, 'bsf-schema-pro-reviews-' . $schema_id, true );
				if ( empty( $all_ratings ) ) {
					update_post_meta( $post_id, 'bsf-schema-pro-rating-' . $schema_id, $new_rating );
					update_post_meta( $post_id, 'bsf-schema-pro-review-counts-' . $schema_id, 1 );
					update_post_meta( $post_id, 'bsf-schema-pro-reviews-' . $schema_id, array( $client_ip => $new_rating ) );
					$response['success']    = true;
					$response['rating']     = $new_rating;
					$response['rating-avg'] = sprintf(
						/* translators: 1: rating */
						_x( '%s/5', 'rating out of', 'wp-schema-pro' ),
						esc_html( $new_rating )
					);
					$response['review-count'] = __( '1 Review', 'wp-schema-pro' );
				} else {

					$all_ratings[ $client_ip ] = $new_rating;
					$all_ratings               = array_filter( $all_ratings );
					$review_count              = count( $all_ratings );
					$avg_rating                = round( array_sum( $all_ratings ) / $review_count, 1 );

					update_post_meta( $post_id, 'bsf-schema-pro-rating-' . $schema_id, $avg_rating );
					update_post_meta( $post_id, 'bsf-schema-pro-review-counts-' . $schema_id, $review_count );
					update_post_meta( $post_id, 'bsf-schema-pro-reviews-' . $schema_id, $all_ratings );

					$response['success']    = true;
					$response['rating']     = $avg_rating;
					$response['rating-avg'] = sprintf(
						/* translators: 1: rating */
						_x( '%s/5', 'rating out of', 'wp-schema-pro' ),
						esc_html( $avg_rating )
					);
					$response['review-count'] = sprintf(
						/* translators: 1: number of reviews */
						_n( '(%1s Review)', '(%1s Reviews)', absint( $review_count ), 'wp-schema-pro' ),
						absint( $review_count )
					);
				}
				// Deleting cached structured data.
				delete_post_meta( $post_id, BSF_AIOSRS_PRO_CACHE_KEY );
				do_action( 'wp_schema_pro_after_update_user_rating', $post_id );
			}
			wp_send_json( $response );
		}

		/**
		 * Function to get the client IP address
		 *
		 * @return string
		 */
		public function get_client_ip() {

			if ( getenv( 'HTTP_CLIENT_IP' ) ) {
				$ipaddress = getenv( 'HTTP_CLIENT_IP' );
			} elseif ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
				$ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
			} elseif ( getenv( 'HTTP_X_FORWARDED' ) ) {
				$ipaddress = getenv( 'HTTP_X_FORWARDED' );
			} elseif ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
				$ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
			} elseif ( getenv( 'HTTP_FORWARDED' ) ) {
				$ipaddress = getenv( 'HTTP_FORWARDED' );
			} elseif ( getenv( 'REMOTE_ADDR' ) ) {
				$ipaddress = getenv( 'REMOTE_ADDR' );
			} else {
				$ipaddress = 'UNKNOWN';
			}

			return $ipaddress;
		}

		/**
		 * Script & Styles for frontend.
		 *
		 * @return void
		 */
		public function enqueue_scripts() {

			if ( is_singular() ) {
				$post_id   = get_the_ID();
				$minfy_css = BSF_AIOSRS_Pro_Helper::bsf_schema_pro_is_wp_debug_enable() ? 'css/frontend.css' : 'min-css/frontend.min.css';
				$minfy_js  = BSF_AIOSRS_Pro_Helper::bsf_schema_pro_is_wp_debug_enable() ? 'js/frontend.js' : 'min-js/frontend.min.js';
				wp_enqueue_style( 'dashicons' );
				wp_register_script( 'wp-schema-pro-fontend-script', BSF_AIOSRS_PRO_URI . 'admin/assets/' . $minfy_js, array( 'jquery' ), BSF_AIOSRS_PRO_VER, true );
				wp_register_style( 'wp-schema-pro-fontend-style', BSF_AIOSRS_PRO_URI . 'admin/assets/' . $minfy_css, array(), BSF_AIOSRS_PRO_VER );
				wp_localize_script(
					'wp-schema-pro-fontend-script',
					'AIOSRS_Frontend',
					array(
						'ajaxurl'           => admin_url( 'admin-ajax.php' ),
						'post_id'           => $post_id,
						'user_rating_nonce' => wp_create_nonce( 'schema-pro-user-rating' ),
						'success_msg'       => __( 'Thanks!', 'wp-schema-pro' ),
					)
				);
			}
		}

		/**
		 * Add rating markup in content
		 *
		 * @param  html $content Post content.
		 * @return html
		 */
		public function rating_markup( $content = '' ) {

			if ( ! is_singular() ) {
				return $content;
			}
			$rating_enabled = array();
			$result         = self::get_schema_posts();
			if ( is_array( $result ) && ! empty( $result ) ) {

				$current_post_id = get_the_id();
				foreach ( $result as $post_id => $post_data ) {

					$schema_type = get_post_meta( $post_id, 'bsf-aiosrs-schema-type', true );
					$schema_meta = get_post_meta( $post_id, 'bsf-aiosrs-' . $schema_type, true );

					$schema_enabled            = BSF_AIOSRS_Pro_Custom_Fields_Markup::enable_schema_post_option();
					$schema_enabled_meta_key   = $schema_type . '-' . $post_id . '-enabled-schema';
					$schema_enabled_meta_value = get_post_meta( $current_post_id, $schema_enabled_meta_key, true );
					$schema_enabled_meta_value = ! empty( $schema_enabled_meta_value ) ? $schema_enabled_meta_value : 'disabled';
					$schema_rating_value       = isset( $schema_meta['schema-type'] ) ? $schema_meta['schema-type'] . '-rating' : '';

					if ( empty( $current_post_id ) || empty( $schema_type ) || empty( $schema_meta ) || ( $schema_enabled && 'disabled' === $schema_enabled_meta_value ) ) {
						continue;
					}
					foreach ( $schema_meta as $meta_key => $metavalue ) {
						if ( $meta_key === $schema_rating_value && 'accept-user-rating' === $metavalue ) {
							$rating_enabled[] = $post_id;
						}
					}

					if ( ( isset( $schema_meta['rating'] ) && 'accept-user-rating' === $schema_meta['rating'] ) || ( 'accept-user-rating' === $schema_rating_value ) ) {
						$rating_enabled[] = $post_id;
					}
				}
			}

			$post_id = get_the_ID();
			if ( ! empty( $rating_enabled ) ) {
				ob_start();

				foreach ( $rating_enabled as $index => $schema_id ) {

					if ( 'publish' !== get_post_status( $schema_id ) ) {
						unset( $rating_enabled[ $index ] );
						update_post_meta( $post_id, 'bsf-schema-pro-accept-user-rating', $rating_enabled );
						continue;
					}

					$review_counts = get_post_meta( $post_id, 'bsf-schema-pro-review-counts-' . $schema_id, true );
					$review_counts = ! empty( $review_counts ) ? $review_counts : 0;
					$avg_rating    = get_post_meta( $post_id, 'bsf-schema-pro-rating-' . $schema_id, true );
					$avg_rating    = ! empty( $avg_rating ) ? $avg_rating : 0;

					wp_enqueue_script( 'wp-schema-pro-fontend-script' );
					wp_enqueue_style( 'wp-schema-pro-fontend-style' );
					$avg_rating = apply_filters( 'update_avg_star_ratings_schema_pro_markup', $avg_rating );
					apply_filters( 'add_ratings_schema_pro_markup', $review_counts, $avg_rating );
					?>
					<div class="aiosrs-rating-wrap" data-schema-id="<?php echo esc_attr( $schema_id ); ?>">
						<?php
						BSF_AIOSRS_Pro_Custom_Fields_Markup::get_star_rating_markup( $avg_rating );
						?>
						<div class="aiosrs-rating-summary-wrap">
							<span class="aiosrs-rating">
							<?php
							printf(
								/* translators: 1: rating */
								esc_attr_x( '%s/5', 'rating out of', 'wp-schema-pro' ),
								esc_html( $avg_rating )
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
					</div>
					<?php
				}
				$content .= ob_get_clean();
			}

			return $content;
		}

		/**
		 * Get current page schemas.
		 *
		 * @return array
		 */
		public static function get_schema_posts() {

			if ( is_singular() ) {
				if ( empty( self::$schema_post_result ) ) {

					$option = array(
						'location'  => 'bsf-aiosrs-schema-location',
						'exclusion' => 'bsf-aiosrs-schema-exclusion',
					);

					self::$schema_post_result = BSF_Target_Rule_Fields::get_instance()->get_posts_by_conditions( 'aiosrs-schema', $option );

				}
			}
			return self::$schema_post_result;
		}

		/**
		 * Disable astra theme schemas.
		 *
		 * @return void
		 */
		public function disable_astra_theme_schema() {

			$result = self::get_schema_posts();
			if ( is_array( $result ) && ! empty( $result ) ) {

				// Disable default Astra article schema.
				add_filter( 'astra_article_schema_enabled', '__return_false' );
			}
		}

		/**
		 * Schema Markup in JSON-LD form.
		 *
		 * @return void
		 */
		public function schema_markup() {

			$current_post_id = get_the_id();

			$result = self::get_schema_posts();

			if ( is_array( $result ) && ! empty( $result ) ) {
				$json_ld_markup = get_post_meta( $current_post_id, BSF_AIOSRS_PRO_CACHE_KEY, true );
				if ( $json_ld_markup ) {
					echo $json_ld_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					return;
				}
				foreach ( $result as $post_id => $post_data ) {

					$schema_type = get_post_meta( $post_id, 'bsf-aiosrs-schema-type', true );
					$schema_meta = get_post_meta( $post_id, 'bsf-aiosrs-' . $schema_type, true );

					$schema_enabled            = BSF_AIOSRS_Pro_Custom_Fields_Markup::enable_schema_post_option();
					$schema_enabled_meta_key   = $schema_type . '-' . $post_id . '-enabled-schema';
					$schema_enabled_meta_value = get_post_meta( $current_post_id, $schema_enabled_meta_key, true );
					$schema_enabled_meta_value = ! empty( $schema_enabled_meta_value ) ? $schema_enabled_meta_value : 'disabled';

					if ( empty( $current_post_id ) || empty( $schema_type ) || empty( $schema_meta ) || ( $schema_enabled && 'disabled' === $schema_enabled_meta_value ) ) {
						continue;
					}

					do_action( "wp_schema_before_schema_markup_{$schema_type}", $current_post_id, $schema_type );

					$enabled         = apply_filters( 'wp_schema_pro_schema_enabled', true, $current_post_id, $schema_type );
					$enabled_comment = apply_filters( 'wp_schema_pro_comment_before_markup_enabled', true );
					if ( true === $enabled_comment ) {
						$json_ld_markup .= '<!-- Schema optimized by Schema Pro -->';
					}
					if ( true === $enabled ) {
						if ( 'custom-markup' === $schema_type ) {
							$custom_markup = BSF_AIOSRS_Pro_Schema_Template::get_schema( $current_post_id, $post_id, $schema_type, $schema_meta );
							if ( isset( $custom_markup[ $schema_type ] ) && ! empty( $custom_markup[ $schema_type ] ) ) {
								$custom_markup[ $schema_type ] = trim( $custom_markup[ $schema_type ] );
								$first_schema_character        = substr( $custom_markup[ $schema_type ], 0, 1 );
								$last_schema_character         = substr( $custom_markup[ $schema_type ], -1, 1 );
								if ( '{' === $first_schema_character && '}' === $last_schema_character ) {
									$json_ld_markup .= '<script type="application/ld+json">';
									$json_ld_markup .= $custom_markup[ $schema_type ];
									$json_ld_markup .= '</script>';
								} else {
									$json_ld_markup .= $custom_markup[ $schema_type ];

								}
							}
						} else {
							// @codingStandardsIgnoreStart
							$json_ld_markup .= '<script type="application/ld+json">';
							if ( version_compare( PHP_VERSION, '5.3', '>' ) ) {
							$json_ld_markup .= wp_json_encode( BSF_AIOSRS_Pro_Schema_Template::get_schema( $current_post_id, $post_id, $schema_type, $schema_meta ),JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
							} else {
								$json_ld_markup .= wp_json_encode( BSF_AIOSRS_Pro_Schema_Template::get_schema( $current_post_id, $post_id, $schema_type, $schema_meta ));
							}
							// @codingStandardsIgnoreEnd
							$json_ld_markup .= '</script>';
						}
						if ( true === $enabled_comment ) {
							$json_ld_markup .= '<!-- / Schema optimized by Schema Pro -->';
						}
					}

					do_action( "wp_schema_after_schema_markup_{$schema_type}", $current_post_id, $schema_type );
				}

				echo $json_ld_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$json_ld_markup = addcslashes( $json_ld_markup, '"\\/' );
				update_post_meta( $current_post_id, 'wp_schema_pro_optimized_structured_data', $json_ld_markup );
			}
		}

		/**
		 * Global Schema
		 *
		 * @since 1.1.0
		 * @param  int    $post_id     Post Id.
		 * @param  string $type        Schema type.
		 */
		public static function global_schema_markup( $post_id, $type ) {

			do_action( "wp_schema_before_global_schema_markup_{$type}", $post_id, $type );

			$enabled_global_schema  = apply_filters( 'wp_schema_pro_global_schema_enabled', true, $post_id, $type );
			$enabled_global_comment = apply_filters( 'wp_schema_pro_comment_before_markup_enabled', true );
			if ( true === $enabled_global_schema ) {
				if ( true === $enabled_global_comment ) {
					echo '<!-- ' . esc_html( $type ) . ' Schema optimized by Schema Pro -->';
				}
				// @codingStandardsIgnoreStart
				echo '<script type="application/ld+json">';
				if ( ! version_compare( PHP_VERSION, '5.3', '>' ) ) {
				echo wp_json_encode( BSF_AIOSRS_Pro_Schema_Template::get_global_schema( $post_id, $type ),JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
				} else {
					echo wp_json_encode( BSF_AIOSRS_Pro_Schema_Template::get_global_schema( $post_id, $type ));
				}
				// @codingStandardsIgnoreEnd
				echo '</script>';
				if ( true === $enabled_global_comment ) {
					echo '<!-- / ' . esc_html( $type ) . ' Schema optimized by Schema Pro -->';
				}
			}

			do_action( "wp_schema_after_global_schema_markup_{$type}", $post_id, $type );
		}

		/**
		 * Global Schemas
		 *
		 * @since 1.1.0
		 */
		public function global_schemas_markup() {

			$yoast_enabled        = WP_Schema_Pro_Yoast_Compatibility::get_option( 'wp_schema_pro_yoast_enabled' );
			$yoast_company_person = WP_Schema_Pro_Yoast_Compatibility::get_option( 'company_or_person' );
			$yoast_advanced_meta  = WP_Schema_Pro_Yoast_Compatibility::get_option( 'disableadvanced_meta' );
			$yoast_breadcrumb     = WP_Schema_Pro_Yoast_Compatibility::get_option( 'breadcrumbs-enable' );
			$post_id              = get_the_ID();
			$general_settings     = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-general-settings'];
			$global_settings      = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-global-schemas'];
			if ( 'person' === $general_settings['site-represent'] || 'personblog' === $general_settings['site-represent'] ) {
				$general_settings['site-represent'] = 'person';
			}
			if ( 'organization' === $general_settings['site-represent'] || 'Webshop' === $general_settings['site-represent'] || 'Smallbusiness' === $general_settings['site-represent'] || 'Otherbusiness' === $general_settings['site-represent'] ) {
				$general_settings['site-represent'] = 'organization';
			}
			if ( is_front_page() && ( ! $yoast_company_person || empty( $yoast_company_person ) ) && ! empty( $general_settings['site-represent'] ) ) {

				echo esc_html( self::global_schema_markup( $post_id, $general_settings['site-represent'] ) );
			}
			if ( ! empty( $global_settings['site-navigation-element'] ) ) {
				echo esc_html( self::global_schema_markup( $post_id, 'site-navigation-element' ) );
			}
			if ( ! $yoast_enabled && '1' === $global_settings['sitelink-search-box'] ) {
				echo esc_html( self::global_schema_markup( $post_id, 'sitelink-search-box' ) );
			}
			if ( ! is_front_page() && ! ( $yoast_advanced_meta && $yoast_breadcrumb ) && '1' === $global_settings['breadcrumb'] ) {
				echo esc_html( self::global_schema_markup( $post_id, 'breadcrumb' ) );
			}

			if ( ! is_singular() ) {
				return;
			}
			if ( absint( $global_settings['about-page'] ) === $post_id ) {

				echo esc_html( self::global_schema_markup( $post_id, 'about-page' ) );
			}
			if ( absint( $global_settings['contact-page'] ) === $post_id ) {
				echo esc_html( self::global_schema_markup( $post_id, 'contact-page' ) );
			}
		}

	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
BSF_AIOSRS_Pro_Markup::get_instance();
