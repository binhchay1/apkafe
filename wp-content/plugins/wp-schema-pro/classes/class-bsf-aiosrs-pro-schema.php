<?php
/**
 * Schemas - Loader.
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Schema' ) ) {

	/**
	 * AIOSRS Schemas Initialization
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Schema {


		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Member Variable
		 *
		 * @var $wp_schema_actions
		 */
		public static $wp_schema_action = 'aiosrs-schema';

		/**
		 * Member Variable
		 *
		 * @var $meta_option
		 */
		public static $meta_option = array();

		/**
		 * Member Variable
		 *
		 * @var $post_metadata
		 */
		public static $post_metadata = array();

		/**
		 * Member Variable
		 *
		 * @var $schema_meta_fields
		 */
		public static $schema_meta_fields = array();

		/**
		 * Member Variable
		 *
		 * @var $schema_item_types
		 */
		public static $schema_item_types = array();

		/**
		 * Member Variable
		 *
		 * @var $schema_meta_keys
		 */
		public static $schema_meta_keys = array();

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

			add_action( 'init', array( $this, 'init_schema_fields' ) );
			add_action( 'init', array( $this, 'schema_post_type' ) );
			add_action( 'admin_head', array( $this, 'menu_highlight' ) );
			add_action( 'admin_head', array( $this, 'schema_location_rule_notice' ) );
			add_filter( 'admin_notices', array( $this, 'back_to_schema' ), 100 );
			add_action( 'admin_init', array( $this, 'redirect_custom_post_type' ) );

			add_filter( 'postbox_classes_aiosrs-schema-aiosrs-schema-settings', array( $this, 'add_class_to_metabox' ) );

			add_action( 'load-post.php', array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );

			add_action( 'wp_ajax_bsf_get_specific_meta_fields', array( $this, 'bsf_get_specific_meta_fields' ) );

			add_action( 'wp_ajax_fetch_item_type_html', array( $this, 'get_review_item_type_html' ) );

			add_filter( 'post_updated_messages', array( $this, 'custom_post_type_post_update_messages' ) );

			add_filter( 'wp_schema_pro_post_metadata', array( $this, 'acf_compatibility' ) );

			if ( is_admin() ) {
				add_filter( 'wp_schema_pro_menu_options', array( $this, 'schema_menu_options' ), 9, 1 );
				add_action( 'aiosrs_menu_aiosrs_schema_action', array( $this, 'setting_page' ) );

				add_action( 'manage_aiosrs-schema_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
				add_filter( 'manage_aiosrs-schema_posts_columns', array( $this, 'column_headings' ) );
				add_action( 'save_post_' . self::$wp_schema_action, array( $this, 'bsf_delete_cached_json_ld' ) );
				add_action( 'delete_post_' . self::$wp_schema_action, array( $this, 'bsf_delete_cached_json_ld' ) );
			}

			add_filter( 'wp_schema_pro_meta_options', array( $this, 'rating_options' ), 10, 3 );
			add_filter( 'wp_schema_pro_mapping_option_string_custom-text', array( $this, 'custom_text_string' ), 10, 2 );
		}

		/**
		 *  Delete the store cache of HTML Markup.
		 *
		 *  @param int $post_id Post id.
		 */
		public function bsf_delete_cached_json_ld( $post_id ) {

			delete_post_meta( $post_id, 'wp_schema_pro_optimized_structured_data' );
		}

		/**
		 *  Fetch the HTML item type for Review.
		 */
		public function get_review_item_type_html() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			check_ajax_referer( 'schema_nonce', 'nonce' );

			$item_type        = filter_input( INPUT_POST, 'item_type', FILTER_SANITIZE_STRING );
			$post_id          = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );
			$item_type_fields = self::$schema_item_types[ $item_type ]['subkeys'];

			foreach ( $item_type_fields as  $key => $item_type_field ) {

				$review_meta_data = get_post_meta( $post_id, 'bsf-aiosrs-review', true );
				$item_key         = $item_type . '-' . $key;
				$schemas_meta     = array( 'bsf-aiosrs-review' => $review_meta_data );

				self::get_meta_markup(
					array(
						'name'            => 'bsf-aiosrs-review',
						'subkey'          => $item_key,
						'subkey_data'     => $item_type_field,
						'item_type_class' => 'bsf-review-item-type-field',
					),
					$schemas_meta
				);
			}

		}


		/**
		 * Filter String for fixed text.
		 *
		 * @param  string $label Option Label.
		 * @param  string $type  Option field type.
		 * @return string
		 */
		public function custom_text_string( $label, $type ) {

			switch ( $type ) {
				case 'dropdown':
					$label = __( 'Fixed Option', 'wp-schema-pro' );
					break;

				case 'multi-select':
					$label = __( 'Fixed Options', 'wp-schema-pro' );
					break;

				case 'number':
					$label = __( 'Fixed Number', 'wp-schema-pro' );
					break;

				case 'date':
					$label = __( 'Fixed Date', 'wp-schema-pro' );
					break;

				case 'time':
					$label = __( 'Fixed Time', 'wp-schema-pro' );
					break;

				case 'datetime-local':
					$label = __( 'Fixed Date & Time', 'wp-schema-pro' );
					break;

				case 'rating':
					$label = __( 'Fixed Rating', 'wp-schema-pro' );
					break;
			}
			return $label;
		}

		/**
		 * Filter option array.
		 *
		 * @param  array  $mapping_options Schema field mapping option array.
		 * @param  string $name            Schema field name.
		 * @param  string $subkey          Schema field subkey.
		 * @return array
		 */
		public function rating_options( $mapping_options, $name, $subkey ) {

			$names     = array( 'bsf-aiosrs-book', 'bsf-aiosrs-local-business', 'bsf-aiosrs-product', 'bsf-aiosrs-recipe', 'bsf-aiosrs-software-application', 'bsf-aiosrs-course', 'bsf-aiosrs-review' );
			$item_key_ = substr( $subkey, -6 );
			if ( in_array( $name, $names, true ) && ( 'rating' === $subkey || 'rating' === $item_key_ ) && isset( $mapping_options[2]['meta-list'] ) ) {
				$mapping_options[2]['meta-list']['accept-user-rating'] = __( 'Accept User Rating', 'wp-schema-pro' );
			}
			return $mapping_options;
		}

		/**
		 * Adds or removes list table column headings.
		 *
		 * @param array $columns Array of columns.
		 * @return array
		 */
		public static function column_headings( $columns ) {

			unset( $columns['date'] );

			$columns['aiosrs_schema_type']          = __( 'Type', 'wp-schema-pro' );
			$columns['aiosrs_schema_display_rules'] = __( 'Target Location', 'wp-schema-pro' );
			$columns['date']                        = __( 'Date', 'wp-schema-pro' );

			return $columns;
		}

		/**
		 * Adds the custom list table column content.
		 *
		 * @since 1.0
		 * @param array $column Name of column.
		 * @param int   $post_id Post id.
		 * @return void
		 */
		public function column_content( $column, $post_id ) {

			if ( 'aiosrs_schema_type' === $column ) {

				$meta_key = get_post_meta( $post_id, 'bsf-aiosrs-schema-type', true );
				echo isset( self::$schema_meta_fields[ 'bsf-aiosrs-' . $meta_key ]['label'] ) ? esc_html( self::$schema_meta_fields[ 'bsf-aiosrs-' . $meta_key ]['label'] ) : '';
			} elseif ( 'aiosrs_schema_display_rules' === $column ) {

				$locations = get_post_meta( $post_id, 'bsf-aiosrs-schema-location', true );
				if ( ! empty( $locations ) ) {
					echo '<div class="bsf-aiosrs-schema-location-wrap" style="margin-bottom: 5px;">';
					echo '<strong>' . esc_html__( 'Enable On: ', 'wp-schema-pro' ) . '</strong>';
					$this->column_display_location_rules( $locations );
					echo '</div>';
				}

				$locations = get_post_meta( $post_id, 'bsf-aiosrs-schema-exclusion', true );
				if ( ! empty( $locations ) ) {
					echo '<div class="bsf-aiosrs-schema-exclusion-wrap" style="margin-bottom: 5px;">';
					echo '<strong>' . esc_html__( 'Exclude From: ', 'wp-schema-pro' ) . '</strong>';
					$this->column_display_location_rules( $locations );
					echo '</div>';
				}
			}
		}

		/**
		 * Get Markup of Location rules for Display rule column.
		 *
		 * @param array $locations Array of locations.
		 * @return void
		 */
		public function column_display_location_rules( $locations ) {

			$location_label = array();
			$index          = array_search( 'specifics', $locations['rule'], true );
			if ( false !== $index && ! empty( $index ) ) {
				unset( $locations['rule'][ $index ] );
			}

			if ( isset( $locations['rule'] ) && is_array( $locations['rule'] ) ) {
				foreach ( $locations['rule'] as $location ) {
					$location_label[] = BSF_Target_Rule_Fields::get_location_by_key( $location );
				}
			}
			if ( isset( $locations['specific'] ) && is_array( $locations['specific'] ) ) {
				foreach ( $locations['specific'] as $location ) {
					$location_label[] = BSF_Target_Rule_Fields::get_location_by_key( $location );
				}
			}

			echo esc_html( join( ', ', $location_label ) );
		}

		/**
		 * Ajax handeler to return the posts based on the search query.
		 * When searching for the post/pages only titles are searched for.
		 *
		 * @since  1.0.0
		 */
		public function bsf_get_specific_meta_fields() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			check_ajax_referer( 'spec_schema', 'nonce_ajax' );

			$search_string = isset( $_POST['q'] ) ? sanitize_text_field( $_POST['q'] ) : '';
			$data          = array();
			$result        = array();

			global $wpdb;
			// WPCS: unprepared SQL OK.
			$aiosrs_meta_array = $wpdb->get_results( "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE '%{$search_string}%'", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$schema_post_meta_fields = array_merge( self::$schema_meta_keys, array( 'bsf-aiosrs-schema-type', 'bsf-aiosrs-schema-location', 'bsf-aiosrs-schema-exclusion' ) );
			if ( isset( $aiosrs_meta_array ) && ! empty( $aiosrs_meta_array ) ) {
				foreach ( $aiosrs_meta_array as $value ) {
					if ( ! in_array( $value['meta_key'], $schema_post_meta_fields, true ) ) {
						$data[] = array(
							'id'   => $value['meta_key'],
							'text' => preg_replace( '/^_/', '', esc_html( str_replace( '_', ' ', $value['meta_key'] ) ) ),
						);
					}
				}
			}

			if ( is_array( $data ) && ! empty( $data ) ) {
				$result[] = array(
					'children' => $data,
				);
			}

			wp_send_json( $result );
		}

		/**
		 * Initialize Schema Meta fields.
		 *
		 * @return void
		 */
		public function init_schema_fields() {
			$doc_link  = '';
			$brand_adv = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
			if ( ( '1' === $brand_adv['sp_hide_label'] ) || true === ( defined( 'WP_SP_WL' ) && WP_SP_WL ) ) {
				$doc_link = 'whit-label';
			}

			self::$schema_meta_fields = apply_filters(
				'wp_schema_pro_schema_meta_fields',
				array(
					'bsf-aiosrs-article'              => array(
						'key'            => 'article',
						'icon'           => 'dashicons dashicons-media-default',
						'label'          => __( 'Article', 'wp-schema-pro' ),
						'guideline-link' => empty( $doc_link ) ? 'https://wpschema.com/docs/add-schema-markup-for-articles-on-the-website/' : 'https://developers.google.com/search/docs/data-types/articles',
						'path'           => BSF_AIOSRS_PRO_DIR . 'classes/schema/',
						'subkeys'        => array(
							'schema-type'      => array(
								'label'    => esc_html__( 'Article Type', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'article',
								'choices'  => array(
									'Article'          => esc_html__( 'Article (General)', 'wp-schema-pro' ),
									'AdvertiserContentArticle' => esc_html__( 'Advertiser Content Article', 'wp-schema-pro' ),
									'BlogPosting'      => esc_html__( 'BlogPosting', 'wp-schema-pro' ),
									'NewsArticle'      => esc_html__( 'NewsArticle', 'wp-schema-pro' ),
									'Report'           => esc_html__( 'Report', 'wp-schema-pro' ),
									'SatiricalArticle' => esc_html__( 'Satirical Article', 'wp-schema-pro' ),
									'ScholarlyArticle' => esc_html__( 'Scholarly Article', 'wp-schema-pro' ),
									'TechArticle'      => esc_html__( 'Tech Article', 'wp-schema-pro' ),
								),
								'required' => true,
							),
							'author'           => array(
								'label'    => esc_html__( 'Article\'s Author Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'author_name',
								'required' => true,
							),
							'image'            => array(
								'label'    => esc_html__( 'Article Image', 'wp-schema-pro' ),
								'type'     => 'image',
								'default'  => 'featured_img',
								'required' => true,
							),
							'description'      => array(
								'label'   => esc_html__( 'Article Short Description', 'wp-schema-pro' ),
								'type'    => 'textarea',
								'default' => 'post_excerpt',
							),
							'main-entity'      => array(
								'label'   => esc_html__( 'Article URL ( Main Entity Of Page)', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'post_permalink',
							),
							'name'             => array(
								'label'    => esc_html__( 'Article Headline', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_title',
								'required' => true,
							),
							'published-date'   => array(
								'label'    => esc_html__( 'Article Published Date', 'wp-schema-pro' ),
								'type'     => 'date',
								'default'  => 'post_date',
								'required' => true,
							),
							'modified-date'    => array(
								'label'    => esc_html__( 'Article Modified Date', 'wp-schema-pro' ),
								'type'     => 'date',
								'default'  => 'post_modified',
								'required' => true,
							),
							'orgnization-name' => array(
								'label'    => esc_html__( 'Publisher Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'blogname',
								'required' => true,
							),
							'site-logo'        => array(
								'label'    => esc_html__( 'Publisher Logo', 'wp-schema-pro' ),
								'type'     => 'image',
								'default'  => 'site_logo',
								'required' => true,
							),
						),
					),
					'bsf-aiosrs-book'                 => array(
						'key'            => 'book',
						'icon'           => 'dashicons dashicons-book',
						'label'          => __( 'Book', 'wp-schema-pro' ),
						'guideline-link' => empty( $doc_link ) ? 'https://wpschema.com/docs/schema-markup-for-a-book-page/' : 'https://developers.google.com/search/docs/data-types/books',
						'path'           => BSF_AIOSRS_PRO_DIR . 'classes/schema/',
						'subkeys'        => array(
							'name'         => array(
								'label'    => esc_html__( 'Title Of The Book', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_title',
								'required' => true,
							),
							'image'        => array(
								'label'   => esc_html__( 'Image Of The Book', 'wp-schema-pro' ),
								'type'    => 'image',
								'default' => 'featured_img',
							),
							'author'       => array(
								'label'    => esc_html__( 'Author Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'url'          => array(
								'label'    => esc_html__( 'URL Of The Page About The Book', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_permalink',
								'required' => true,
							),
							'work-example' => array(
								'label'    => esc_html__( 'Work Example (The Editions Of Book)', 'wp-schema-pro' ),
								'type'     => 'repeater',
								'required' => true,
								'fields'   => array(
									'serial-number'   => array(
										'label'       => esc_html__( 'ISBN', 'wp-schema-pro' ),
										'type'        => 'number',
										'default'     => 'none',
										'required'    => true,
										'description' => esc_html__( 'The International Standard Book Number (ISBN) is a unique numeric commercial book identifier. ISBN having 10 or 13 digit number.', 'wp-schema-pro' ),
									),
									'book-format'     => array(
										'label'         => esc_html__( 'Book Format', 'wp-schema-pro' ),
										'type'          => 'dropdown',
										'default'       => 'none',
										'dropdown-type' => 'book-format',
										'required'      => true,
										'description'   => esc_html__( 'The format of the book using one or more of the [ EBook, Hardcover, Paperback, AudioBook ] values', 'wp-schema-pro' ),

									),
									'book-edition'    => array(
										'label'   => esc_html__( 'Book Edition', 'wp-schema-pro' ),
										'type'    => 'text',
										'default' => 'none',
									),

									'url-template'    => array(
										'label'       => esc_html__( 'EntryPoint URL Template', 'wp-schema-pro' ),
										'type'        => 'text',
										'default'     => 'none',
										'required'    => true,
										'description' => esc_html__( 'Link(s) to content. The EntryPoint serves as the mechanism for you to provide the deep links to your books.', 'wp-schema-pro' ),
									),
									'action-platform' => array(
										'label'         => esc_html__( 'EntryPoint Action Platform', 'wp-schema-pro' ),
										'type'          => 'multi-select',
										'default'       => 'none',
										'dropdown-type' => 'action-platform',
										'required'      => true,
										'description'   => esc_html__( 'The platform(s) on which the link works', 'wp-schema-pro' ),
									),
									'price'           => array(
										'label'    => esc_html__( 'Offer Price', 'wp-schema-pro' ),
										'type'     => 'number',
										'default'  => 'none',
										'required' => true,
										'attrs'    => array(
											'min'  => '0',
											'step' => 'any',
										),
									),
									'currency'        => array(
										'label'         => esc_html__( 'Offer Price Currency', 'wp-schema-pro' ),
										'type'          => 'dropdown',
										'default'       => 'none',
										'required'      => true,
										'dropdown-type' => 'currency',
									),
									'country'         => array(
										'label'         => esc_html__( 'Offer Eligible Region', 'wp-schema-pro' ),
										'type'          => 'multi-select',
										'default'       => 'none',
										'dropdown-type' => 'country',
									),
									'avail'           => array(
										'label'         => esc_html__( 'Offer Availability Status', 'wp-schema-pro' ),
										'type'          => 'dropdown',
										'default'       => 'none',
										'dropdown-type' => 'availability',
									),
								),
							),
							'same-as'      => array(
								'label'       => esc_html__( 'Same As', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'none',
								'description' => esc_html__( 'A reference page that unambiguously indicates the item\'s identity; for example, the URL of the item\'s Wikipedia page, Freebase page, or official website.', 'wp-schema-pro' ),
							),
						),
					),
					'bsf-aiosrs-course'               => array(
						'key'            => 'course',
						'icon'           => 'dashicons dashicons-media-default',
						'label'          => __( 'Course', 'wp-schema-pro' ),
						'guideline-link' => empty( $doc_link ) ? 'https://wpschema.com/docs/add-a-schema-markup-for-a-course-page/' : 'https://developers.google.com/search/docs/data-types/courses',
						'path'           => BSF_AIOSRS_PRO_DIR . 'classes/schema/',
						'subkeys'        => array(
							'name'             => array(
								'label'    => esc_html__( 'Title Of The Course.', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_title',
								'required' => true,
							),
							'description'      => array(
								'label'       => esc_html__( 'Course Description', 'wp-schema-pro' ),
								'type'        => 'textarea',
								'default'     => 'post_content',
								'description' => esc_html__( 'A description of the course. Display limit of 60 characters.', 'wp-schema-pro' ),
								'required'    => true,
							),
							'course-code'      => array(
								'label'       => esc_html__( 'Course Code', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'none',
								'description' => esc_html__( 'The identifier for the Course used by the course provider (e.g. CS101 or 6.001).', 'wp-schema-pro' ),
							),
							'orgnization-name' => array(
								'label'       => esc_html__( 'Course Provider', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'blogname',
								'description' => esc_html__( 'The organization that publishes the source content of the course. For example, UC Berkeley.', 'wp-schema-pro' ),
							),
							'course-instance'  => array(
								'label'  => esc_html__( 'Course Instance', 'wp-schema-pro' ),
								'type'   => 'repeater',
								'fields' => array(
									'name'             => array(
										'label'    => esc_html__( 'Instance Name', 'wp-schema-pro' ),
										'type'     => 'text',
										'default'  => 'none',
										'required' => true,
									),
									'description'      => array(
										'label'    => esc_html__( 'Instance Description', 'wp-schema-pro' ),
										'type'     => 'textarea',
										'default'  => 'none',
										'required' => true,
									),
									'course-mode'      => array(
										'label'       => esc_html__( 'Course Mode', 'wp-schema-pro' ),
										'type'        => 'text',
										'default'     => 'none',
										'description' => esc_html__( 'The medium or means of delivery of the course instance or the mode of study, either as a text label (e.g. "online", "onsite" or "blended"; "synchronous" or "asynchronous"; "full-time" or "part-time") or as a URL reference to a term from a controlled vocabulary (e.g. https://ceds.ed.gov/element/001311#Asynchronous )', 'wp-schema-pro' ),
									),
									'image'            => array(
										'label'   => esc_html__( 'Image', 'wp-schema-pro' ),
										'type'    => 'image',
										'default' => 'none',
									),
									'start-date'       => array(
										'label'    => esc_html__( 'Start Date', 'wp-schema-pro' ),
										'type'     => 'date',
										'default'  => 'none',
										'required' => true,
									),
									'end-date'         => array(
										'label'   => esc_html__( 'End Date', 'wp-schema-pro' ),
										'type'    => 'date',
										'default' => 'none',
									),
									'location-name'    => array(
										'label'   => esc_html__( 'Location Name', 'wp-schema-pro' ),
										'type'    => 'text',
										'default' => 'none',
									),
									'location-address' => array(
										'label'    => esc_html__( 'Location Address', 'wp-schema-pro' ),
										'type'     => 'text',
										'default'  => 'none',
										'required' => true,
									),
									'price'            => array(
										'label'   => esc_html__( 'Price', 'wp-schema-pro' ),
										'type'    => 'number',
										'default' => 'none',
										'attrs'   => array(
											'min'  => '0',
											'step' => 'any',
										),
									),
									'currency'         => array(
										'label'         => esc_html__( 'Currency', 'wp-schema-pro' ),
										'type'          => 'dropdown',
										'default'       => 'none',
										'dropdown-type' => 'currency',
									),
									'valid-from'       => array(
										'label'   => esc_html__( 'Valid From', 'wp-schema-pro' ),
										'type'    => 'date',
										'default' => 'none',
									),
									'url'              => array(
										'label'   => esc_html__( 'Offer URL', 'wp-schema-pro' ),
										'type'    => 'text',
										'default' => 'none',
									),
									'avail'            => array(
										'label'         => esc_html__( 'Availability', 'wp-schema-pro' ),
										'type'          => 'dropdown',
										'default'       => 'none',
										'dropdown-type' => 'availability',
									),
									'performer'        => array(
										'label'   => esc_html__( 'Performer', 'wp-schema-pro' ),
										'type'    => 'text',
										'default' => 'none',
									),
								),
							),
							'same-as'          => array(
								'label'       => esc_html__( 'Same As', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'none',
								'description' => esc_html__( 'A reference page that unambiguously indicates the item\'s identity; for example, the URL of the item\'s Wikipedia page, Freebase page, or official website.', 'wp-schema-pro' ),
							),
							'rating'           => array(
								'label'   => esc_html__( 'Rating', 'wp-schema-pro' ),
								'type'    => 'rating',
								'default' => 'none',
							),
							'review-count'     => array(
								'label'   => esc_html__( 'Review Count', 'wp-schema-pro' ),
								'type'    => 'number',
								'default' => 'none',
							),
						),
					),
					'bsf-aiosrs-event'                => array(
						'key'            => 'event',
						'icon'           => 'dashicons dashicons-tickets-alt',
						'label'          => __( 'Event', 'wp-schema-pro' ),
						'guideline-link' => empty( $doc_link ) ? 'https://wpschema.com/docs/add-schema-markup-on-event-page/' : 'https://developers.google.com/search/docs/data-types/events',
						'path'           => BSF_AIOSRS_PRO_DIR . 'classes/schema/',
						'subkeys'        => array(
							'schema-type'           => array(
								'label'   => esc_html__( 'Event Type', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'Event',
								'choices' => array(
									'Event'            => esc_html__( 'Event (General)', 'wp-schema-pro' ),
									'BusinessEvent'    => esc_html__( 'Business Event', 'wp-schema-pro' ),
									'ChildrensEvent'   => esc_html__( 'Childrens Event', 'wp-schema-pro' ),
									'ComedyEvent'      => esc_html__( 'Comedy Event', 'wp-schema-pro' ),
									'CourseInstance'   => esc_html__( 'Course Instance', 'wp-schema-pro' ),
									'DanceEvent'       => esc_html__( 'Dance Event', 'wp-schema-pro' ),
									'DeliveryEvent'    => esc_html__( 'Delivery Event', 'wp-schema-pro' ),
									'EducationEvent'   => esc_html__( 'Education Event', 'wp-schema-pro' ),
									'EventSeries'      => esc_html__( 'EventSeries', 'wp-schema-pro' ),
									'ExhibitionEvent'  => esc_html__( 'Exhibition Event', 'wp-schema-pro' ),
									'Festival'         => esc_html__( 'Festival', 'wp-schema-pro' ),
									'FoodEvent'        => esc_html__( 'Food Event', 'wp-schema-pro' ),
									'LiteraryEvent'    => esc_html__( 'Literary Event', 'wp-schema-pro' ),
									'MusicEvent'       => esc_html__( 'Music Event', 'wp-schema-pro' ),
									'PublicationEvent' => esc_html__( 'Publication Event', 'wp-schema-pro' ),
									'SaleEvent'        => esc_html__( 'Sale Event', 'wp-schema-pro' ),
									'ScreeningEvent'   => esc_html__( 'Screening Event', 'wp-schema-pro' ),
									'SocialEvent'      => esc_html__( 'Social Event', 'wp-schema-pro' ),
									'SportsEvent'      => esc_html__( 'Sports Event', 'wp-schema-pro' ),
									'TheaterEvent'     => esc_html__( 'Theater Event', 'wp-schema-pro' ),
									'VisualArtsEvent'  => esc_html__( 'Visual Arts Event', 'wp-schema-pro' ),

								),
							),
							'name'                  => array(
								'label'    => esc_html__( 'Event Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_title',
								'required' => true,
							),
							'description'           => array(
								'label'   => esc_html__( 'Event Description', 'wp-schema-pro' ),
								'type'    => 'textarea',
								'default' => 'post_content',
							),
							'image'                 => array(
								'label'   => esc_html__( 'Event Image/Logo', 'wp-schema-pro' ),
								'type'    => 'image',
								'default' => 'featured_img',
							),
							'event-status'          => array(
								'label'         => esc_html__( 'Event Status', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'custom-text',
								'dropdown-type' => 'event-status',
								'required'      => false,
								'description'   => esc_html__( 'The status of the event. If you don\'t use this field, Google understands the eventStatus to be EventScheduled. ', 'wp-schema-pro' ),

							),
							'event-attendance-mode' => array(
								'label'         => esc_html__( 'Event Attendance Mode', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'custom-text',
								'dropdown-type' => 'event-attendance-mode',
								'required'      => false,
								'description'   => esc_html__( 'The location of the event. There are different requirements depending on if the event is happening online or at a physical location.', 'wp-schema-pro' ),

							),
							'start-date'            => array(
								'label'    => esc_html__( 'Event Start Date', 'wp-schema-pro' ),
								'type'     => 'datetime-local',
								'default'  => 'none',
								'required' => true,
							),
							'end-date'              => array(
								'label'   => esc_html__( 'Event End Date', 'wp-schema-pro' ),
								'type'    => 'datetime-local',
								'default' => 'none',
							),
							'previous-date'         => array(
								'label'   => esc_html__( 'Event Previous Start Date', 'wp-schema-pro' ),
								'type'    => 'datetime-local',
								'default' => 'custom-text',
							),
							'online-location'       => array(
								'label'   => esc_html__( 'Online Event URL', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'post_permalink',

							),
							'location'              => array(
								'label'       => esc_html__( 'Event Location Name', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'none',
								'description' => esc_html__( 'The detailed name of the place or venue where the event is being held. This property is only recommended for events that take place at a physical location.', 'wp-schema-pro' ),
							),
							'location-street'       => array(
								'label'    => esc_html__( 'Street Address', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'location-locality'     => array(
								'label'    => esc_html__( 'Locality', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'location-postal'       => array(
								'label'    => esc_html__( 'Postal Code', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'location-region'       => array(
								'label'    => esc_html__( 'Region', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'location-country'      => array(
								'label'         => esc_html__( 'Country', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'none',
								'required'      => true,
								'dropdown-type' => 'country',
							),

							'avail'                 => array(
								'label'         => esc_html__( 'Offer Availability', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'none',
								'dropdown-type' => 'availability',
							),
							'price'                 => array(
								'label'   => esc_html__( 'Offer Price', 'wp-schema-pro' ),
								'type'    => 'number',
								'default' => 'none',
								'attrs'   => array(
									'min'  => '0',
									'step' => 'any',
								),
							),
							'currency'              => array(
								'label'         => esc_html__( 'Offer 	Currency', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'none',
								'dropdown-type' => 'currency',
							),
							'valid-from'            => array(
								'label'   => esc_html__( 'Offer Valid From', 'wp-schema-pro' ),
								'type'    => 'date',
								'default' => 'none',
							),
							'ticket-buy-url'        => array(
								'label'   => esc_html__( 'Offer Online Ticket URL', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'performer'             => array(
								'label'   => esc_html__( 'Performer', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
						),
					),
					'bsf-aiosrs-job-posting'          => array(
						'key'            => 'job-posting',
						'icon'           => 'dashicons dashicons-businessman',
						'label'          => __( 'Job Posting', 'wp-schema-pro' ),
						'guideline-link' => empty( $doc_link ) ? 'https://wpschema.com/docs/add-a-schema-markup-for-a-job-posting-page/' : 'https://developers.google.com/search/docs/data-types/job-postings',
						'path'           => BSF_AIOSRS_PRO_DIR . 'classes/schema/',
						'subkeys'        => array(
							'title'                   => array(
								'label'    => esc_html__( 'Job Title', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'orgnization-name'        => array(
								'label'    => esc_html__( 'Hiring Organization', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'blogname',
								'required' => true,
							),
							'same-as'                 => array(
								'label'       => esc_html__( 'Hiring Organization URL', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'none',
								'required'    => true,
								'description' => esc_html__( 'A reference page that unambiguously indicates the item\'s identity; for example, the URL of the item\'s Wikipedia page, Freebase page, or official website.', 'wp-schema-pro' ),
							),
							'organization-logo'       => array(
								'label'   => esc_html__( 'Hiring Organization Logo', 'wp-schema-pro' ),
								'type'    => 'image',
								'default' => 'create-field',
							),
							'industry'                => array(
								'label'   => esc_html__( 'Industry', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'job-type'                => array(
								'label'         => esc_html__( 'Employment Type', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'none',
								'dropdown-type' => 'employment',
							),
							'description'             => array(
								'label'    => esc_html__( 'Job Description', 'wp-schema-pro' ),
								'type'     => 'textarea',
								'default'  => 'post_content',
								'required' => true,
							),
							'start-date'              => array(
								'label'    => esc_html__( 'Date Posted', 'wp-schema-pro' ),
								'type'     => 'date',
								'default'  => 'post_date',
								'required' => true,
							),
							'expiry-date'             => array(
								'label'   => esc_html__( 'Valid Through', 'wp-schema-pro' ),
								'type'    => 'date',
								'default' => 'none',
							),
							'education-requirements'  => array(
								'label'   => esc_html__( 'Education Requirements', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'experience-requirements' => array(
								'label'   => esc_html__( 'Experience Requirements', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'qualifications'          => array(
								'label'   => esc_html__( 'Qualifications', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'responsibilities'        => array(
								'label'   => esc_html__( 'Responsibilities', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'skills'                  => array(
								'label'   => esc_html__( 'Skills', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'work-hours'              => array(
								'label'   => esc_html__( 'Work Hours', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'location-street'         => array(
								'label'    => esc_html__( 'Street Address', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'location-locality'       => array(
								'label'    => esc_html__( 'Locality', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'location-postal'         => array(
								'label'    => esc_html__( 'Postal Code', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'location-region'         => array(
								'label'    => esc_html__( 'Region', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'location-country'        => array(
								'label'         => esc_html__( 'Country', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'none',
								'required'      => true,
								'dropdown-type' => 'country',
							),
							'salary'                  => array(
								'label'   => esc_html__( 'Base Salary', 'wp-schema-pro' ),
								'type'    => 'number',
								'default' => 'none',
							),
							'salary-min-value'        => array(
								'label'   => esc_html__( 'Min Salary', 'wp-schema-pro' ),
								'type'    => 'number',
								'default' => 'create-field',
							),
							'salary-max-value'        => array(
								'label'   => esc_html__( 'Max Salary', 'wp-schema-pro' ),
								'type'    => 'number',
								'default' => 'create-field',
							),
							'salary-currency'         => array(
								'label'         => esc_html__( 'Salary In Currency', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'none',
								'dropdown-type' => 'currency',
							),
							'salary-unit'             => array(
								'label'         => esc_html__( 'Salary Per Unit', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'none',
								'dropdown-type' => 'time-unit',
							),
						),
					),
					'bsf-aiosrs-local-business'       => array(
						'key'            => 'local-business',
						'icon'           => 'dashicons dashicons-admin-site',
						'label'          => __( 'Local Business', 'wp-schema-pro' ),
						'guideline-link' => empty( $doc_link ) ? 'https://wpschema.com/docs/add-schema-markup-for-a-local-business-page/' : 'https://developers.google.com/search/docs/data-types/local-businesses',
						'path'           => BSF_AIOSRS_PRO_DIR . 'classes/schema/',
						'subkeys'        => array(
							'name'                => array(
								'label'    => esc_html__( 'Business Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'blogname',
								'required' => true,
							),
							'schema-type'         => array(
								'label'   => esc_html__( 'Local Business Type', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'LocalBusiness',
								'choices' => array(
									'LocalBusiness'        => esc_html__( 'General', 'wp-schema-pro' ),
									'AnimalShelter'        => esc_html__( 'Animal Shelter', 'wp-schema-pro' ),
									'AutomotiveBusiness'   => esc_html__( 'Automotive', 'wp-schema-pro' ),
									'ChildCare'            => esc_html__( 'Child Care', 'wp-schema-pro' ),
									'Dentist'              => esc_html__( 'Dentist', 'wp-schema-pro' ),
									'DryCleaningOrLaundry' => esc_html__( 'Dry Cleaning Or Laundry', 'wp-schema-pro' ),
									'EmergencyService'     => esc_html__( 'Emergency Service', 'wp-schema-pro' ),
									'EmploymentAgency'     => esc_html__( 'Employment Agency', 'wp-schema-pro' ),
									'EntertainmentBusiness' => esc_html__( 'Entertainment', 'wp-schema-pro' ),
									'FinancialService'     => esc_html__( 'Financial Service', 'wp-schema-pro' ),
									'FoodEstablishment'    => esc_html__( 'Food Establishment', 'wp-schema-pro' ),
									'GovernmentOffice'     => esc_html__( 'Government Office', 'wp-schema-pro' ),
									'HealthAndBeautyBusiness' => esc_html__( 'Health And Beauty', 'wp-schema-pro' ),
									'HomeAndConstructionBusiness' => esc_html__( 'Home And Construction', 'wp-schema-pro' ),
									'InternetCafe'         => esc_html__( 'Internet Cafe', 'wp-schema-pro' ),
									'LegalService'         => esc_html__( 'Legal Service', 'wp-schema-pro' ),
									'Library'              => esc_html__( 'Library', 'wp-schema-pro' ),
									'Locksmith'            => esc_html__( 'Locksmith', 'wp-schema-pro' ),
									'LodgingBusiness'      => esc_html__( 'Lodging', 'wp-schema-pro' ),
									'MedicalBusiness'      => esc_html__( 'Medical Business', 'wp-schema-pro' ),
									'ProfessionalService'  => esc_html__( 'Professional Service', 'wp-schema-pro' ),
									'RadioStation'         => esc_html__( 'Radio Station', 'wp-schema-pro' ),
									'RealEstateAgent'      => esc_html__( 'Real Estate Agent', 'wp-schema-pro' ),
									'RecyclingCenter'      => esc_html__( 'Recycling Center', 'wp-schema-pro' ),
									'SelfStorage'          => esc_html__( 'Self Storage', 'wp-schema-pro' ),
									'ShoppingCenter'       => esc_html__( 'Shopping Center', 'wp-schema-pro' ),
									'SportsActivityLocation' => esc_html__( 'Sports Activity Location', 'wp-schema-pro' ),
									'Store'                => esc_html__( 'Store', 'wp-schema-pro' ),
									'TelevisionStation'    => esc_html__( 'Television Station', 'wp-schema-pro' ),
									'TouristInformationCenter' => esc_html__( 'Tourist Information Center', 'wp-schema-pro' ),
									'TravelAgency'         => esc_html__( 'Travel Agency', 'wp-schema-pro' ),
								),
							),
							'image'               => array(
								'label'    => esc_html__( 'Business Image', 'wp-schema-pro' ),
								'type'     => 'image',
								'default'  => 'none',
								'required' => true,
							),
							'telephone'           => array(
								'label'   => esc_html__( 'Telephone', 'wp-schema-pro' ),
								'type'    => 'tel',
								'default' => 'none',
							),
							'price-range'         => array(
								'label'    => esc_html__( 'Price Range', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'url'                 => array(
								'label'   => esc_html__( 'URL', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'site_url',
							),
							'location-street'     => array(
								'label'    => esc_html__( 'Street Address', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'location-locality'   => array(
								'label'    => esc_html__( 'Locality', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'location-postal'     => array(
								'label'    => esc_html__( 'Postal Code', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'location-region'     => array(
								'label'    => esc_html__( 'Region', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'location-country'    => array(
								'label'         => esc_html__( 'Country', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'none',
								'dropdown-type' => 'country',
							),
							'hours-specification' => array(
								'label'  => esc_html__( 'Hours Specification', 'wp-schema-pro' ),
								'type'   => 'repeater',
								'fields' => array(
									'days'   => array(
										'label'         => esc_html__( 'Day Of Week', 'wp-schema-pro' ),
										'type'          => 'multi-select',
										'default'       => 'none',
										'required'      => true,
										'dropdown-type' => 'days',
									),
									'opens'  => array(
										'label'    => esc_html__( 'Opens', 'wp-schema-pro' ),
										'type'     => 'time',
										'default'  => 'none',
										'required' => true,
									),
									'closes' => array(
										'label'    => esc_html__( 'Closes', 'wp-schema-pro' ),
										'type'     => 'time',
										'default'  => 'none',
										'required' => true,
									),
								),
							),
							'rating'              => array(
								'label'   => esc_html__( 'Rating', 'wp-schema-pro' ),
								'type'    => 'rating',
								'default' => 'accept-user-rating',
							),
							'review-count'        => array(
								'label'   => esc_html__( 'Review Count', 'wp-schema-pro' ),
								'type'    => 'number',
								'default' => 'none',
							),
						),
					),
					'bsf-aiosrs-review'               => array(
						'key'            => 'review',
						'icon'           => 'dashicons dashicons-admin-comments',
						'label'          => __( 'Review', 'wp-schema-pro' ),
						'guideline-link' => empty( $doc_link ) ? 'https://wpschema.com/docs/add-a-schema-markup-for-a-review-page/' : 'https://developers.google.com/search/docs/data-types/reviews',
						'path'           => BSF_AIOSRS_PRO_DIR . 'classes/schema/',
						'subkeys'        => array(
							'schema-type'    => array(
								'label'    => esc_html__( 'Review Item Type', 'wp-schema-pro' ),
								'type'     => 'text',
								'required' => true,
								'choices'  => array(
									''                   => esc_html__( 'Select Item Type', 'wp-schema-pro' ),
									'bsf-aiosrs-product' => esc_html__( 'Product', 'wp-schema-pro' ),
									'bsf-aiosrs-book'    => esc_html__( 'Book', 'wp-schema-pro' ),
									'bsf-aiosrs-course'  => esc_html__( 'Course', 'wp-schema-pro' ),
									'bsf-aiosrs-event'   => esc_html__( 'Event', 'wp-schema-pro' ),
									'bsf-aiosrs-local-business' => esc_html__( 'Local business', 'wp-schema-pro' ),
									'bsf-aiosrs-recipe'  => esc_html__( 'Recipe', 'wp-schema-pro' ),
									'bsf-aiosrs-software-application' => esc_html__( 'Software Application', 'wp-schema-pro' ),
									'bsf-aiosrs-movie'   => esc_html__( 'Movie', 'wp-schema-pro' ),
									'bsf-aiosrs-organization' => esc_html__( 'Organization', 'wp-schema-pro' ),
								),
							),
							'item'           => array(
								'label'    => esc_html__( 'Review Item', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'item-image'     => array(
								'label'   => esc_html__( 'Review Item Image', 'wp-schema-pro' ),
								'type'    => 'image',
								'default' => 'none',
							),
							'description'    => array(
								'label'    => esc_html__( 'Review Description', 'wp-schema-pro' ),
								'type'     => 'textarea',
								'default'  => 'post_content',
								'required' => true,
							),
							'review-body'    => array(
								'label'    => esc_html__( 'Review Body', 'wp-schema-pro' ),
								'type'     => 'textarea',
								'default'  => 'post_content',
								'required' => true,
							),
							'date'           => array(
								'label'    => esc_html__( 'Review Date', 'wp-schema-pro' ),
								'type'     => 'date',
								'default'  => 'post_date',
								'required' => true,
							),
							'rating'         => array(
								'label'   => esc_html__( 'Review Rating', 'wp-schema-pro' ),
								'type'    => 'rating',
								'default' => 'none',
							),
							'reviewer-type'  => array(
								'label'   => esc_html__( 'Reviewer Type', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'Person',
								'choices' => array(
									'Person'       => esc_html__( 'Person', 'wp-schema-pro' ),
									'Organization' => esc_html__( 'Organization', 'wp-schema-pro' ),
								),
							),
							'reviewer-name'  => array(
								'label'   => esc_html__( 'Reviewer Name', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'author_name',
							),
							'publisher-name' => array(
								'label'   => esc_html__( 'Publisher Name', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'author_name',
							),
							'url'            => array(
								'label'   => esc_html__( 'URL', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'post_permalink',
							),
						),
					),
					'bsf-aiosrs-person'               => array(
						'key'            => 'person',
						'icon'           => 'dashicons dashicons-admin-users',
						'label'          => __( 'Person', 'wp-schema-pro' ),
						'guideline-link' => empty( $doc_link ) ? 'https://wpschema.com/docs/add-schema-markup-for-a-person/' : 'https://schema.org/Person',
						'path'           => BSF_AIOSRS_PRO_DIR . 'classes/schema/',
						'subkeys'        => array(

							'name'         => array(
								'label'    => esc_html__( 'Person Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'gender'       => array(
								'label'         => esc_html__( 'Person Gender', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'dropdown-type' => 'gender-select',
								'default'       => 'none',
							),
							'dob'          => array(
								'label'   => esc_html__( 'Person DOB', 'wp-schema-pro' ),
								'type'    => 'date',
								'default' => 'none',
							),
							'member'       => array(
								'label'   => esc_html__( 'Member of', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'email'        => array(
								'label'    => esc_html__( 'Person Email', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => false,
							),
							'telephone'    => array(
								'label'   => esc_html__( 'Telephone', 'wp-schema-pro' ),
								'type'    => 'tel',
								'default' => 'none',
							),
							'image'        => array(
								'label'   => esc_html__( 'Photograph', 'wp-schema-pro' ),
								'type'    => 'image',
								'default' => 'none',
							),
							'job-title'    => array(
								'label'   => esc_html__( 'Job Title', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'homepage-url' => array(
								'label'   => esc_html__( 'Homepage URL', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'nationality'  => array(
								'label'    => esc_html__( 'Nationality', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => false,
							),
							'street'       => array(
								'label'    => esc_html__( 'Street Address', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => false,
							),
							'locality'     => array(
								'label'    => esc_html__( 'Locality', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => false,
							),
							'postal'       => array(
								'label'    => esc_html__( 'Postal Code', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => false,
							),
							'region'       => array(
								'label'    => esc_html__( 'Region', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => false,
							),
							'country'      => array(
								'label'         => esc_html__( 'Country', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'none',
								'dropdown-type' => 'country',
							),
							'add-url'      => array(
								'label'  => esc_html__( 'Same As', 'wp-schema-pro' ),
								'type'   => 'repeater',
								'fields' => array(
									'same-as' => array(
										'label'    => esc_html__( 'URL', 'wp-schema-pro' ),
										'type'     => 'text',
										'default'  => 'none',
										'required' => false,
									),
								),
							),
						),
					),
					'bsf-aiosrs-product'              => array(
						'key'            => 'product',
						'icon'           => 'dashicons dashicons-cart',
						'label'          => __( 'Product', 'wp-schema-pro' ),
						'guideline-link' => empty( $doc_link ) ? 'https://wpschema.com/docs/add-a-schema-markup-for-a-product-page/' : 'https://developers.google.com/search/docs/data-types/products',
						'path'           => BSF_AIOSRS_PRO_DIR . 'classes/schema/',
						'subkeys'        => array(
							'name'              => array(
								'label'    => esc_html__( 'Product Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_title',
								'required' => true,
							),
							'brand-name'        => array(
								'label'   => esc_html__( 'Product Brand', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'image'             => array(
								'label'    => esc_html__( 'Product Image', 'wp-schema-pro' ),
								'type'     => 'image',
								'default'  => 'featured_img',
								'required' => true,
							),
							'url'               => array(
								'label'   => esc_html__( 'Product URL', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'post_permalink',
							),
							'description'       => array(
								'label'   => esc_html__( 'Product Description', 'wp-schema-pro' ),
								'type'    => 'textarea',
								'default' => 'post_content',
							),
							'sku'               => array(
								'label'       => esc_html__( 'Product SKU', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'description' => esc_html__( 'The Stock Keeping Unit (SKU) is a unique numerical identifying number that refers to a specific stock item in a retailers inventory or product catalog.', 'wp-schema-pro' ),
							),
							'mpn'               => array(
								'label'       => esc_html__( 'Product MPN', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'description' => esc_html__( 'The Manufacturer Part Number (MPN) of the product, or the product to which the offer refers. e.g. "925872"', 'wp-schema-pro' ),
							),
							'avail'             => array(
								'label'         => esc_html__( 'Product Availability', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'none',
								'dropdown-type' => 'availability',
							),
							'price-valid-until' => array(
								'label'       => esc_html__( 'Price Valid Until', 'wp-schema-pro' ),
								'type'        => 'datetime-local',
								'default'     => 'create-field',
								'description' => esc_html__( 'The date after which the price will no longer be available. e.g. "31/12/2021 09:00 AM"', 'wp-schema-pro' ),
							),
							'price'             => array(
								'label'   => esc_html__( 'Product Price', 'wp-schema-pro' ),
								'type'    => 'number',
								'default' => 'none',
								'attrs'   => array(
									'min'  => '0',
									'step' => '0.01',
								),
							),
							'currency'          => array(
								'label'         => esc_html__( 'Currency', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'none',
								'dropdown-type' => 'currency',
							),
							'rating'            => array(
								'label'   => esc_html__( 'Rating', 'wp-schema-pro' ),
								'type'    => 'rating',
								'default' => 'accept-user-rating',
							),
							'review-count'      => array(
								'label'   => esc_html__( 'Review Count', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
						),
					),
					'bsf-aiosrs-recipe'               => array(
						'key'            => 'recipe',
						'icon'           => 'dashicons dashicons-carrot',
						'label'          => __( 'Recipe', 'wp-schema-pro' ),
						'guideline-link' => empty( $doc_link ) ? 'https://wpschema.com/docs/add-schema-markup-for-a-recipe-page/' : 'https://developers.google.com/search/docs/data-types/recipes',
						'path'           => BSF_AIOSRS_PRO_DIR . 'classes/schema/',
						'subkeys'        => array(
							'name'                => array(
								'label'    => esc_html__( 'Recipe Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'image'               => array(
								'label'    => esc_html__( 'Recipe Photo', 'wp-schema-pro' ),
								'type'     => 'image',
								'default'  => 'featured_img',
								'required' => true,
							),
							'description'         => array(
								'label'   => esc_html__( 'Recipe Description', 'wp-schema-pro' ),
								'type'    => 'textarea',
								'default' => 'post_content',
							),
							'reviewer-type'       => array(
								'label'   => esc_html__( 'Reviewer Type', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'Person',
								'choices' => array(
									'Person'       => esc_html__( 'Person', 'wp-schema-pro' ),
									'Organization' => esc_html__( 'Organization', 'wp-schema-pro' ),
								),
							),
							'author'              => array(
								'label'   => esc_html__( 'Author Name', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'author_name',
							),
							'preperation-time'    => array(
								'label'   => esc_html__( 'Preparation Time', 'wp-schema-pro' ),
								'type'    => 'time-duration',
								'default' => 'none',
							),
							'cook-time'           => array(
								'label'   => esc_html__( 'Cook Time', 'wp-schema-pro' ),
								'type'    => 'time-duration',
								'default' => 'none',
							),
							'recipe-keywords'     => array(
								'label'       => esc_html__( 'Keywords', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'description' => esc_html__( 'e.g. "winter apple pie", "nutmeg crust"', 'wp-schema-pro' ),
							),
							'recipe-category'     => array(
								'label'       => esc_html__( 'Recipe Category', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'description' => esc_html__( 'e.g. "dinner", "entree", or "dessert"', 'wp-schema-pro' ),
							),
							'recipe-cuisine'      => array(
								'label'       => esc_html__( 'Recipe Cuisine', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'description' => esc_html__( 'e.g. "French", "Indian", or "American"', 'wp-schema-pro' ),
							),
							'nutrition'           => array(
								'label'       => esc_html__( 'Recipe Calories', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'none',
								'description' => esc_html__( 'The number of calories in the recipe.', 'wp-schema-pro' ),
							),
							'ingredients'         => array(
								'label'       => esc_html__( 'Recipe Ingredients', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'none',
								'description' => esc_html__( 'Ingredient used in the recipe. Separate multiple ingredients with comma(,).', 'wp-schema-pro' ),
							),
							'recipe-instructions' => array(
								'label'  => esc_html__( 'Recipe Instructions', 'wp-schema-pro' ),
								'type'   => 'repeater',
								'fields' => array(
									'steps' => array(
										'label'   => esc_html__( 'Instructions Step', 'wp-schema-pro' ),
										'type'    => 'text',
										'default' => 'create-field',
									),
								),
							),
							'recipe-video'        => array(
								'label'         => esc_html__( 'Recipe Video', 'wp-schema-pro' ),
								'type'          => 'repeater',
								'is_recommnded' => true,
								'fields'        => array(
									'video-name'  => array(
										'label'    => esc_html__( 'Video Name', 'wp-schema-pro' ),
										'type'     => 'text',
										'default'  => 'create-field',
										'required' => true,
									),
									'video-desc'  => array(
										'label'    => esc_html__( 'Video Description', 'wp-schema-pro' ),
										'type'     => 'textarea',
										'default'  => 'create-field',
										'required' => true,
									),
									'video-image' => array(
										'label'    => esc_html__( 'Thumbnail Url', 'wp-schema-pro' ),
										'type'     => 'image',
										'default'  => 'create-field',
										'required' => true,
									),
									'recipe-video-content-url' => array(
										'label'   => esc_html__( 'Content URL', 'wp-schema-pro' ),
										'type'    => 'text',
										'default' => 'create-field',
									),
									'recipe-video-embed-url' => array(
										'label'   => esc_html__( 'Embed URL', 'wp-schema-pro' ),
										'type'    => 'text',
										'default' => 'create-field',
									),
									'recipe-video-duration' => array(
										'label'   => esc_html__( 'Duration', 'wp-schema-pro' ),
										'type'    => 'time-duration',
										'default' => 'create-field',
									),
									'recipe-video-upload-date' => array(
										'label'    => esc_html__( 'Upload Date', 'wp-schema-pro' ),
										'type'     => 'date',
										'default'  => 'post_date',
										'required' => true,
									),
									'recipe-video-expires-date' => array(
										'label'   => esc_html__( 'Expires On', 'wp-schema-pro' ),
										'type'    => 'date',
										'default' => 'create-field',
									),
									'recipe-video-interaction-count' => array(
										'label'   => esc_html__( 'Interaction Count', 'wp-schema-pro' ),
										'type'    => 'number',
										'default' => 'create-field',
									),
								),
							),
							'rating'              => array(
								'label'   => esc_html__( 'Rating', 'wp-schema-pro' ),
								'type'    => 'rating',
								'default' => 'accept-user-rating',
							),
							'review-count'        => array(
								'label'   => esc_html__( 'Review Count', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
						),
					),
					'bsf-aiosrs-service'              => array(
						'key'            => 'service',
						'icon'           => 'dashicons dashicons-admin-generic',
						'label'          => __( 'Service', 'wp-schema-pro' ),
						'guideline-link' => empty( $doc_link ) ? 'https://wpschema.com/docs/add-schema-markup-to-service-page/' : 'https://schema.org/Service',
						'path'           => BSF_AIOSRS_PRO_DIR . 'classes/schema/',
						'subkeys'        => array(
							'name'              => array(
								'label'    => esc_html__( 'Service Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_title',
								'required' => true,
							),
							'type'              => array(
								'label'    => esc_html__( 'Service Type', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'none',
								'required' => true,
							),
							'area'              => array(
								'label'       => esc_html__( 'Service Area', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'none',
								'description' => esc_html__( 'The geographic area where a service or offered item is provided.', 'wp-schema-pro' ),
							),
							'provider'          => array(
								'label'    => esc_html__( 'Service Provider Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'blogname',
								'required' => true,
							),
							'location-image'    => array(
								'label'    => esc_html__( 'Service Location Image', 'wp-schema-pro' ),
								'type'     => 'image',
								'default'  => 'none',
								'required' => true,
							),
							'description'       => array(
								'label'   => esc_html__( 'Service Description', 'wp-schema-pro' ),
								'type'    => 'textarea',
								'default' => 'post_content',
							),
							'image'             => array(
								'label'   => esc_html__( 'Service Image', 'wp-schema-pro' ),
								'type'    => 'image',
								'default' => 'featured_img',
							),
							'location-locality' => array(
								'label'   => esc_html__( 'Locality', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'location-region'   => array(
								'label'   => esc_html__( 'Region', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'location-street'   => array(
								'label'   => esc_html__( 'Street Address', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'telephone'         => array(
								'label'   => esc_html__( 'Telephone', 'wp-schema-pro' ),
								'type'    => 'tel',
								'default' => 'none',
							),
							'price-range'       => array(
								'label'   => esc_html__( 'Price Range', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
						),
					),
					'bsf-aiosrs-software-application' => array(
						'key'            => 'software-application',
						'icon'           => 'dashicons dashicons-dashboard',
						'label'          => __( 'Software Application', 'wp-schema-pro' ),
						'guideline-link' => empty( $doc_link ) ? 'https://wpschema.com/docs/add-schema-markup-for-software-application-page/' : 'https://developers.google.com/search/docs/data-types/software-apps',
						'path'           => BSF_AIOSRS_PRO_DIR . 'classes/schema/',
						'subkeys'        => array(
							'name'             => array(
								'label'    => esc_html__( 'Application Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_title',
								'required' => true,
							),
							'category'         => array(
								'label'         => esc_html__( 'Application Type', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'none',
								'required'      => true,
								'dropdown-type' => 'software-category',
							),
							'operating-system' => array(
								'label'       => esc_html__( 'Operating System', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'none',
								'required'    => true,
								'description' => esc_html__( 'Software for the operating system, for example, "Windows 7", "OSX 10.6", "Android 1.6"', 'wp-schema-pro' ),
							),
							'price'            => array(
								'label'    => esc_html__( 'Price', 'wp-schema-pro' ),
								'type'     => 'number',
								'default'  => 'none',
								'required' => true,
								'attrs'    => array(
									'min'  => '0',
									'step' => 'any',
								),
							),
							'currency'         => array(
								'label'         => esc_html__( 'Currency', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'none',
								'required'      => true,
								'dropdown-type' => 'currency',
							),
							'image'            => array(
								'label'   => esc_html__( 'Application Image', 'wp-schema-pro' ),
								'type'    => 'image',
								'default' => 'featured_img',
							),
							'rating'           => array(
								'label'   => esc_html__( 'Rating', 'wp-schema-pro' ),
								'type'    => 'rating',
								'default' => 'accept-user-rating',
							),
							'review-count'     => array(
								'label'   => esc_html__( 'Review Count', 'wp-schema-pro' ),
								'type'    => 'number',
								'default' => 'none',
							),
						),
					),
					'bsf-aiosrs-video-object'         => array(
						'key'            => 'video-object',
						'icon'           => 'dashicons dashicons-video-alt3',
						'label'          => __( 'Video Object', 'wp-schema-pro' ),

						'guideline-link' => empty( $doc_link ) ? 'https://wpschema.com/docs/how-to-add-a-schema-markup-for-a-video-object/' : 'https://developers.google.com/search/docs/data-types/videos',
						'path'           => BSF_AIOSRS_PRO_DIR . 'classes/schema/',
						'subkeys'        => array(
							'name'              => array(
								'label'    => esc_html__( 'Video Title', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_title',
								'required' => true,
							),
							'description'       => array(
								'label'    => esc_html__( 'Video Description', 'wp-schema-pro' ),
								'type'     => 'textarea',
								'default'  => 'post_content',
								'required' => true,
							),
							'image'             => array(
								'label'    => esc_html__( 'Video Thumbnail', 'wp-schema-pro' ),
								'type'     => 'image',
								'default'  => 'featured_img',
								'required' => true,
							),
							'upload-date'       => array(
								'label'    => esc_html__( 'Video Upload Date', 'wp-schema-pro' ),
								'type'     => 'date',
								'default'  => 'post_date',
								'required' => true,
							),
							'orgnization-name'  => array(
								'label'   => esc_html__( 'Publisher Name', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'blogname',
							),
							'site-logo'         => array(
								'label'   => esc_html__( 'Publisher Logo', 'wp-schema-pro' ),
								'type'    => 'image',
								'default' => 'site_logo',
							),
							'content-url'       => array(
								'label'   => esc_html__( 'Content URL', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'embed-url'         => array(
								'label'   => esc_html__( 'Embed URL', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
							'duration'          => array(
								'label'   => esc_html__( 'Video Duration', 'wp-schema-pro' ),
								'type'    => 'time-duration',
								'default' => 'none',
							),
							'expires-date'      => array(
								'label'   => esc_html__( 'Video Expires On', 'wp-schema-pro' ),
								'type'    => 'date',
								'default' => 'none',
							),
							'interaction-count' => array(
								'label'   => esc_html__( 'Video Interaction Count', 'wp-schema-pro' ),
								'type'    => 'number',
								'default' => 'none',
							),
						),
					),
				)
			);

			self::$schema_item_types = apply_filters(
				'wp_schema_pro_schema_item_type_recommended',
				array(
					'bsf-aiosrs-book'                 => array(
						'subkeys' => array(
							'name'          => array(
								'label'    => esc_html__( 'Book Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_title',
								'required' => true,
							),
							'serial-number' => array(

								'label'       => esc_html__( 'Book ISBN', 'wp-schema-pro' ),
								'type'        => 'number',
								'default'     => 'create-field',
								'required'    => true,
								'description' => esc_html__( 'The International Standard Book Number (ISBN) is a unique numeric commercial book identifier. ISBN having 10 or 13 digit number.', 'wp-schema-pro' ),
							),
							'author'        => array(
								'label'    => esc_html__( 'Book Author Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'same-As'       => array(
								'label'       => esc_html__( 'Same As', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'required'    => true,
								'description' => esc_html__( 'A reference page that unambiguously indicates the item\'s identity; for example, the URL of the item\'s Wikipedia page, Freebase page, or official website.', 'wp-schema-pro' ),
							),
						),
					),
					'bsf-aiosrs-course'               => array(
						'subkeys' => array(
							'name'             => array(
								'label'    => esc_html__( 'Course Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_title',
								'required' => true,
							),
							'description'      => array(
								'label'   => esc_html__( 'Course Description', 'wp-schema-pro' ),
								'type'    => 'textarea',
								'default' => 'post_content',
							),
							'orgnization-name' => array(
								'label'   => esc_html__( 'Course Organization Name', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'blogname',
							),
						),
					),
					'bsf-aiosrs-event'                => array(
						'subkeys' => array(
							'name'              => array(
								'label'    => esc_html__( 'Event Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_title',
								'required' => true,
							),
							'image'             => array(
								'label'   => esc_html__( 'Event Image', 'wp-schema-pro' ),
								'type'    => 'image',
								'default' => 'featured_img',
							),

							'start-date'        => array(
								'label'    => esc_html__( 'Event Start Date', 'wp-schema-pro' ),
								'type'     => 'datetime-local',
								'default'  => 'create-field',
								'required' => true,
							),
							'end-date'          => array(
								'label'   => esc_html__( 'Event End Date', 'wp-schema-pro' ),
								'type'    => 'datetime-local',
								'default' => 'create-field',
							),
							'location'          => array(
								'label'   => esc_html__( 'Event Location Name', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'create-field',
							),
							'location-street'   => array(
								'label'    => esc_html__( 'Event Street Address', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'location-locality' => array(
								'label'    => esc_html__( 'Event Locality', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'location-postal'   => array(
								'label'    => esc_html__( 'Event Postal Code', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'location-region'   => array(
								'label'    => esc_html__( 'Event Region', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'location-country'  => array(
								'label'         => esc_html__( 'Event Country', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'create-field',
								'required'      => true,
								'dropdown-type' => 'country',
							),
							'avail'             => array(
								'label'         => esc_html__( 'Event Offer Availability', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'create-field',
								'dropdown-type' => 'availability',
							),
							'price'             => array(
								'label'   => esc_html__( 'Event Offer Price', 'wp-schema-pro' ),
								'type'    => 'number',
								'default' => 'create-field',
								'attrs'   => array(
									'min'  => '0',
									'step' => 'any',
								),
							),
							'currency'          => array(
								'label'         => esc_html__( 'Event Currency', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'create-field',
								'dropdown-type' => 'currency',
							),
							'valid-from'        => array(
								'label'   => esc_html__( 'Event Offer Valid From', 'wp-schema-pro' ),
								'type'    => 'date',
								'default' => 'create-field',
							),
							'ticket-buy-url'    => array(
								'label'   => esc_html__( 'Event Ticket Link', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'create-field',
							),
							'performer'         => array(
								'label'   => esc_html__( 'Event Performer', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'create-field',
							),
							'description'       => array(
								'label'   => esc_html__( 'Event Description', 'wp-schema-pro' ),
								'type'    => 'textarea',
								'default' => 'post_content',
							),
						),

					),
					'bsf-aiosrs-local-business'       => array(
						'subkeys' => array(
							'name'              => array(
								'label'    => esc_html__( 'Local Business Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'blogname',
								'required' => true,
							),
							'image'             => array(
								'label'    => esc_html__( 'Local Business Image', 'wp-schema-pro' ),
								'type'     => 'image',
								'default'  => 'create-field',
								'required' => true,
							),
							'telephone'         => array(
								'label'   => esc_html__( 'Local Business Telephone', 'wp-schema-pro' ),
								'type'    => 'tel',
								'default' => 'create-field',
							),
							'location-street'   => array(
								'label'    => esc_html__( 'Local Business Street Address', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'location-locality' => array(
								'label'    => esc_html__( 'Local Business Locality', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'location-postal'   => array(
								'label'    => esc_html__( 'Local Business Postal Code', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'location-region'   => array(
								'label'    => esc_html__( 'Local Business Region', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'location-country'  => array(
								'label'         => esc_html__( 'Local Business Country', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'create-field',
								'dropdown-type' => 'country',
							),
							'price-range'       => array(
								'label'    => esc_html__( 'Local Business Price Range', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
						),
					),
					'bsf-aiosrs-product'              => array(
						'subkeys' => array(
							'name'              => array(
								'label'    => esc_html__( 'Product Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_title',
								'required' => true,
							),
							'brand-name'        => array(
								'label'    => esc_html__( 'Product Brand Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'image'             => array(
								'label'    => esc_html__( ' Product Image', 'wp-schema-pro' ),
								'type'     => 'image',
								'default'  => 'featured_img',
								'required' => true,
							),
							'url'               => array(
								'label'    => esc_html__( ' Product URL', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_permalink',
								'required' => true,
							),
							'description'       => array(
								'label'    => esc_html__( 'Product Description', 'wp-schema-pro' ),
								'type'     => 'textarea',
								'default'  => 'post_content',
								'required' => true,
							),
							'sku'               => array(
								'label'       => esc_html__( 'Product SKU', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'description' => esc_html__( 'The Stock Keeping Unit (SKU), a merchant-specific identifier for a product or service, or the product e.g. "0446310786"', 'wp-schema-pro' ),
								'required'    => true,
							),
							'mpn'               => array(
								'label'       => esc_html__( 'Product MPN', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'description' => esc_html__( 'The Manufacturer Part Number (MPN) of the product, or the product to which the offer refers. e.g. "925872"', 'wp-schema-pro' ),
								'required'    => true,
							),
							'avail'             => array(
								'label'         => esc_html__( 'Product Availability', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'create-field',
								'dropdown-type' => 'availability',
								'required'      => true,
							),
							'price-valid-until' => array(
								'label'    => esc_html__( 'Product Price Valid Until', 'wp-schema-pro' ),
								'type'     => 'datetime-local',
								'default'  => 'create-field',
								'required' => true,
							),
							'price'             => array(
								'label'    => esc_html__( 'Product Price', 'wp-schema-pro' ),
								'type'     => 'number',
								'default'  => 'create-field',
								'required' => true,
								'attrs'    => array(
									'min'  => '0',
									'step' => '0.01',
								),
							),
							'currency'          => array(
								'label'         => esc_html__( 'Product Currency', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'create-field',
								'required'      => true,
								'dropdown-type' => 'currency',
							),
							'rating'            => array(
								'label'   => esc_html__( 'Product Rating', 'wp-schema-pro' ),
								'type'    => 'rating',
								'default' => 'accept-user-rating',
							),
							'review-count'      => array(
								'label'   => esc_html__( 'Product Review Count', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'none',
							),
						),
					),
					'bsf-aiosrs-recipe'               => array(
						'subkeys' => array(
							'name'                      => array(
								'label'    => esc_html__( 'Recipe Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'image'                     => array(
								'label'    => esc_html__( 'Recipe Photo', 'wp-schema-pro' ),
								'type'     => 'image',
								'default'  => 'featured_img',
								'required' => true,
							),
							'description'               => array(
								'label'   => esc_html__( 'Recipe Description', 'wp-schema-pro' ),
								'type'    => 'textarea',
								'default' => 'post_content',
							),
							'author'                    => array(
								'label'   => esc_html__( 'Recipe Author Name', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'author_name',
							),
							'preperation-time'          => array(
								'label'   => esc_html__( 'Recipe Preparation Time', 'wp-schema-pro' ),
								'type'    => 'time-duration',
								'default' => 'create-field',
							),
							'cook-time'                 => array(
								'label'   => esc_html__( 'Recipe Cook Time', 'wp-schema-pro' ),
								'type'    => 'time-duration',
								'default' => 'create-field',
							),
							'recipe-keywords'           => array(
								'label'       => esc_html__( 'Recipe Keywords', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'description' => esc_html__( 'e.g. "winter apple pie", "nutmeg crust"', 'wp-schema-pro' ),
							),
							'recipe-category'           => array(
								'label'       => esc_html__( 'Recipe Category', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'description' => esc_html__( 'e.g. "dinner", "entree", or "dessert"', 'wp-schema-pro' ),
							),
							'recipe-cuisine'            => array(
								'label'       => esc_html__( 'Recipe Cuisine', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'description' => esc_html__( 'e.g. "French", "Indian", or "American"', 'wp-schema-pro' ),
							),
							'nutrition'                 => array(
								'label'       => esc_html__( 'Recipe Calories', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'description' => esc_html__( 'The number of calories in the recipe.', 'wp-schema-pro' ),
							),
							'ingredients'               => array(
								'label'       => esc_html__( 'Recipe Ingredients', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'description' => esc_html__( 'Ingredient used in the recipe. Separate multiple ingredients with comma(,).', 'wp-schema-pro' ),
							),
							'recipe-instructions'       => array(
								'label'       => esc_html__( 'Recipe Instructions Step', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'description' => esc_html__( 'Recipe Instructions Steps used in the recipe. Separate multiple Instructions Steps with comma(,).', 'wp-schema-pro' ),
							),
							'video-name'                => array(
								'label'    => esc_html__( 'Recipe Video Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'video-desc'                => array(
								'label'    => esc_html__( 'Recipe Video Description', 'wp-schema-pro' ),
								'type'     => 'textarea',
								'default'  => 'create-field',
								'required' => true,
							),
							'video-image'               => array(
								'label'    => esc_html__( 'Recipe Video Thumbnail Url', 'wp-schema-pro' ),
								'type'     => 'image',
								'default'  => 'create-field',
								'required' => true,
							),
							'recipe-video-content-url'  => array(
								'label'   => esc_html__( 'Recipe Video Content URL', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'create-field',
							),
							'recipe-video-embed-url'    => array(
								'label'   => esc_html__( 'Recipe Video Embed URL', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'create-field',
							),
							'recipe-video-duration'     => array(
								'label'   => esc_html__( 'Recipe Video  Duration', 'wp-schema-pro' ),
								'type'    => 'time-duration',
								'default' => 'create-field',
							),
							'recipe-video-upload-date'  => array(
								'label'    => esc_html__( 'Recipe Video Upload Date', 'wp-schema-pro' ),
								'type'     => 'date',
								'default'  => 'post_date',
								'required' => true,
							),
							'recipe-video-expires-date' => array(
								'label'   => esc_html__( 'Recipe Video Expires On', 'wp-schema-pro' ),
								'type'    => 'date',
								'default' => 'create-field',
							),
							'recipe-video-interaction-count' => array(
								'label'   => esc_html__( 'Recipe Video Interaction Count', 'wp-schema-pro' ),
								'type'    => 'number',
								'default' => 'create-field',
							),
							'rating'                    => array(
								'label'   => esc_html__( 'Recipe Video Rating', 'wp-schema-pro' ),
								'type'    => 'rating',
								'default' => 'accept-user-rating',
							),
							'review-count'              => array(
								'label'   => esc_html__( 'Recipe Video Review Count', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'number',
							),
						),
					),
					'bsf-aiosrs-software-application' => array(
						'subkeys' => array(
							'name'             => array(
								'label'    => esc_html__( 'Software Application Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_title',
								'required' => true,
							),
							'operating-system' => array(
								'label'       => esc_html__( 'Software Application Operating System', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'required'    => true,
								'description' => esc_html__( 'Software for the operating system, for example, "Windows 7", "OSX 10.6", "Android 1.6"', 'wp-schema-pro' ),
							),
							'category'         => array(
								'label'         => esc_html__( 'Software Application Category', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'create-field',
								'required'      => true,
								'dropdown-type' => 'software-category',
							),
							'rating'           => array(
								'label'   => esc_html__( 'Software Application Rating', 'wp-schema-pro' ),
								'type'    => 'rating',
								'default' => 'accept-user-rating',
							),
							'review-count'     => array(
								'label'   => esc_html__( 'Software Application Review Count', 'wp-schema-pro' ),
								'type'    => 'number',
								'default' => 'number',
							),
							'price'            => array(
								'label'    => esc_html__( 'Software Application Price', 'wp-schema-pro' ),
								'type'     => 'number',
								'default'  => 'create-field',
								'required' => true,
								'attrs'    => array(
									'min'  => '0',
									'step' => 'any',
								),
							),
							'currency'         => array(
								'label'         => esc_html__( 'Software Application Currency', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'create-field',
								'required'      => true,
								'dropdown-type' => 'currency',
							),
						),
					),
					'bsf-aiosrs-movie'                => array(
						'subkeys' => array(
							'name'          => array(
								'label'    => esc_html__( 'Movie Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'post_title',
								'required' => true,
							),
							'same-As'       => array(
								'label'       => esc_html__( 'Movie SameAs', 'wp-schema-pro' ),
								'type'        => 'text',
								'default'     => 'create-field',
								'required'    => true,
								'description' => esc_html__( 'A reference page that unambiguously indicates the item\'s identity; for example, the URL of the item\'s Wikipedia page, Freebase page, or official website.', 'wp-schema-pro' ),
							),
							'image'         => array(
								'label'    => esc_html__( 'Movie Image', 'wp-schema-pro' ),
								'type'     => 'image',
								'default'  => 'featured_img',
								'required' => true,
							),
							'dateCreated'   => array(
								'label'   => esc_html__( 'Movie Date', 'wp-schema-pro' ),
								'type'    => 'datetime-local',
								'default' => 'create-field',
							),
							'director-name' => array(
								'label'   => esc_html__( 'Movie Director Name', 'wp-schema-pro' ),
								'type'    => 'text',
								'default' => 'create-field',
							),
						),
					),
					'bsf-aiosrs-organization'         => array(
						'subkeys' => array(
							'name'              => array(
								'label'    => esc_html__( 'Organization Name', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'blogname',
								'required' => true,
							),
							'location-street'   => array(
								'label'    => esc_html__( 'Organization Street Address', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'location-locality' => array(
								'label'    => esc_html__( 'Organization Locality', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'location-postal'   => array(
								'label'    => esc_html__( 'Organization Postal Code', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'location-region'   => array(
								'label'    => esc_html__( 'Organization Region', 'wp-schema-pro' ),
								'type'     => 'text',
								'default'  => 'create-field',
								'required' => true,
							),
							'location-country'  => array(
								'label'         => esc_html__( 'Organization Country', 'wp-schema-pro' ),
								'type'          => 'dropdown',
								'default'       => 'create-field',
								'dropdown-type' => 'country',
							),
						),
					),
				)
			);
		}

		/**
		 * Get Meta list for Schema's meta field options.
		 *
		 * @param string $type Field type.
		 * @return array
		 */
		public static function get_meta_list( $type ) {

			if ( empty( self::$post_metadata ) ) {

				self::$post_metadata = apply_filters(
					'wp_schema_pro_post_metadata',
					array(
						'text'  => array(
							array(
								'label'     => __( 'Site Meta', 'wp-schema-pro' ),
								'meta-list' => array(
									'blogname'        => __( 'Site Title', 'wp-schema-pro' ),
									'blogdescription' => __( 'Tagline', 'wp-schema-pro' ),
									'site_url'        => __( 'Site URL', 'wp-schema-pro' ),
								),
							),
							array(
								'label'     => __( 'Post Meta (Basic Fields)', 'wp-schema-pro' ),
								'meta-list' => array(
									'post_title'        => __( 'Title', 'wp-schema-pro' ),
									'post_content'      => __( 'Content', 'wp-schema-pro' ),
									'post_excerpt'      => __( 'Excerpt', 'wp-schema-pro' ),
									'post_permalink'    => __( 'Permalink', 'wp-schema-pro' ),
									'author_name'       => __( 'Author name', 'wp-schema-pro' ),
									'author_first_name' => __( 'Author first name', 'wp-schema-pro' ),
									'author_last_name'  => __( 'Author last name', 'wp-schema-pro' ),
									'post_date'         => __( 'Publish Date', 'wp-schema-pro' ),
									'post_modified'     => __( 'Last Modify Date', 'wp-schema-pro' ),
								),
							),
							array(
								'label'     => __( 'Add Custom Info', 'wp-schema-pro' ),
								'meta-list' => array(
									'custom-text'  => __( 'Fixed Text', 'wp-schema-pro' ),
									'create-field' => __( 'New Custom Field', 'wp-schema-pro' ),
								),
							),
							array(
								'label'     => __( 'All Other Custom Fields', 'wp-schema-pro' ),
								'meta-list' => array(
									'specific-field' => __( 'Select Other Custom Fields Here', 'wp-schema-pro' ),
								),
							),
						),
						'image' => array(
							array(
								'label'     => __( 'Site Meta', 'wp-schema-pro' ),
								'meta-list' => array(
									'site_logo' => __( 'Logo', 'wp-schema-pro' ),
								),
							),
							array(
								'label'     => __( 'Post Meta (Basic Fields)', 'wp-schema-pro' ),
								'meta-list' => array(
									'featured_img' => __( 'Featured image', 'wp-schema-pro' ),
									'author_image' => __( 'Author image', 'wp-schema-pro' ),
								),
							),
							array(
								'label'     => __( 'Add Custom Info', 'wp-schema-pro' ),
								'meta-list' => array(
									'custom-text'  => __( 'Fixed Image', 'wp-schema-pro' ),
									'fixed-text'   => __( 'Image URL', 'wp-schema-pro' ),
									'create-field' => __( 'New Custom Field', 'wp-schema-pro' ),
								),
							),
							array(
								'label'     => __( 'All Other Custom Fields', 'wp-schema-pro' ),
								'meta-list' => array(
									'specific-field' => __( 'Select Other Custom Fields Here', 'wp-schema-pro' ),
								),
							),
						),
					)
				);
			}

			return self::$post_metadata[ $type ];
		}

		/**
		 * Advanced Custom Fields compatibility.
		 *
		 * @param  array $fields Meta fields array.
		 * @return array
		 */
		public function acf_compatibility( $fields ) {

			if ( function_exists( 'acf' ) && class_exists( 'acf' ) ) {

				$post_type = 'acf';
				if ( ( defined( 'ACF_PRO' ) && ACF_PRO ) || ( defined( 'ACF' ) && ACF ) ) {
					$post_type = 'acf-field-group';
				}
				$text_acf_field  = array();
				$image_acf_field = array();
				$args            = array(
					'post_type'      => $post_type,
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				);

				$the_query = new WP_Query( $args );
				if ( $the_query->have_posts() ) :
					while ( $the_query->have_posts() ) :
						$the_query->the_post();

						$post_id = get_the_id();

						// Get ACF fields.
						// Ignore the PHPCS warning about hook names.
						// @codingStandardsIgnoreStart
						$acf_fields = apply_filters( 'acf/field_group/get_fields', array(), $post_id ); // WPCS: XSS OK.
						// @codingStandardsIgnoreEnd

						if ( 'acf-field-group' === $post_type ) {
							$acf_fields = acf_get_fields( $post_id );
						}

						if ( is_array( $acf_fields ) && ! empty( $acf_fields ) ) {
							foreach ( $acf_fields as $key => $value ) {

								if ( 'image' === $value['type'] ) {
									$image_acf_field[ $value['name'] ] = $value['label'];
								} else {
									$text_acf_field[ $value['name'] ] = $value['label'];
								}
							}
						}
					endwhile;
				endif;
				wp_reset_postdata();

				if ( ! empty( $text_acf_field ) ) {
					$fields['text'][] = array(
						'label'     => __( 'Advanced Custom Fields', 'wp-schema-pro' ),
						'meta-list' => $text_acf_field,
					);
				}

				if ( ! empty( $image_acf_field ) ) {
					$fields['image'][] = array(
						'label'     => __( 'Advanced Custom Fields', 'wp-schema-pro' ),
						'meta-list' => $image_acf_field,
					);
				}
			}

			return $fields;
		}

		/**
		 * Init Metabox
		 */
		public function init_metabox() {
			add_action( 'add_meta_boxes', array( $this, 'setup_meta_box' ) );
			add_action( 'save_post', array( $this, 'save_meta_box' ) );

			/**
			 * Set metabox options
			 *
			 * @see http://php.net/manual/en/filter.filters.sanitize.php
			 */
			self::$meta_option = apply_filters(
				'wp_schema_pro_schema_meta_box_options',
				array(
					'bsf-aiosrs-schema-type'      => array(
						'default'  => 'article',
						'sanitize' => 'FILTER_DEFAULT',
					),
					'bsf-aiosrs-schema-location'  => array(
						'default'  => array(
							'rule' => array(
								'basic-singulars',
							),
						),
						'sanitize' => 'FILTER_DEFAULT',
					),
					'bsf-aiosrs-schema-exclusion' => array(
						'default'  => array(),
						'sanitize' => 'FILTER_DEFAULT',
					),
				)
			);

			$schema_meta_keys = array();
			foreach ( self::$schema_meta_fields as $key => $value ) {
				self::$schema_meta_keys[]  = $key;
				self::$meta_option[ $key ] = array(
					'default'  => array(),
					'sanitize' => 'FILTER_DEFAULT',
				);
			}
		}

		/**
		 *  Setup Metabox
		 */
		public function setup_meta_box() {

			// Get all posts.
			$post_types = get_post_types();

			if ( 'aiosrs-schema' === get_post_type() ) {
				// Enable for all posts.
				foreach ( $post_types as $type ) {

					if ( 'attachment' !== $type ) {
						add_meta_box(
							'aiosrs-schema-settings',                // Id.
							__( 'Schema Settings', 'wp-schema-pro' ), // Title.
							array( $this, 'markup_meta_box' ),      // Callback.
							$type,                                  // Post_type.
							'normal',                               // Context.
							'high'                                  // Priority.
						);
					}
				}
			}
		}

		/**
		 * Keep the Schema Pro menu open when editing the advanced headers.
		 * Highlights the wanted admin (sub-) menu items for the CPT.
		 *
		 * @since  1.0.0
		 */
		public function menu_highlight() {
			global $parent_file, $submenu_file, $post_type;
			if ( 'aiosrs-schema' === $post_type ) {

				$parent_page     = BSF_AIOSRS_Pro_Admin::$default_menu_position;
				$setting_options = BSF_AIOSRS_Pro_Helper::$settings['aiosrs-pro-settings'];
				$menu_position   = isset( $setting_options['menu-position'] ) ? $setting_options['menu-position'] : $parent_page;

				$is_top_level_page = in_array( $menu_position, array( 'top', 'middle', 'bottom' ), true );

				if ( $is_top_level_page ) {
					$parent_file  = 'aiosrs_pro_admin_menu_page'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$submenu_file = 'aiosrs_pro_admin_menu_page'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				} else {
					$parent_file  = BSF_AIOSRS_Pro_Admin::$default_menu_position; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					$submenu_file = 'aiosrs_pro_admin_menu_page'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				}
			}
		}

		/**
		 * Schema location rule notice.
		 *
		 * @since  1.0.0
		 */
		public function schema_location_rule_notice() {
			global $post, $post_type;
			if ( 'aiosrs-schema' === $post_type ) {

				$type = get_post_meta( $post->ID, 'bsf-aiosrs-schema-type', true );
				if ( ! empty( $type ) ) {
					$meta_values = get_post_meta( $post->ID, 'bsf-aiosrs-' . $type, true );

					$required_fields  = array();
					$schema_meta      = self::$schema_meta_fields[ 'bsf-aiosrs-' . $type ]['subkeys'];
					$schema_meta_keys = array_keys( $schema_meta );

					if ( is_array( $schema_meta_keys ) && is_array( $meta_values ) && ! empty( $meta_values ) ) {
						foreach ( $schema_meta_keys as $meta_key ) {
							if ( ( isset( $schema_meta[ $meta_key ]['required'] ) && true === $schema_meta[ $meta_key ]['required'] ) && ( ! isset( $meta_values[ $meta_key ] ) || 'none' === $meta_values[ $meta_key ] ) ) {
								$required_fields[] = $schema_meta[ $meta_key ]['label'];
							}
						}
					}

					if ( ! empty( $required_fields ) ) {
						add_action(
							'admin_notices',
							function() use ( $required_fields ) {

								$required_fields = '<strong>' . implode( ', ', $required_fields ) . '</strong>';

								/* translators: %s post title. */
								$notice = sprintf( __( 'Schema required mapping of %s meta fields.', 'wp-schema-pro' ), $required_fields );

								echo '<div class="error">';
								echo '<p>' . wp_kses_post( $notice ) . '</p>';
								echo '</div>';

							}
						);
					}
				}
			}
		}

		/**
		 * Get metabox options
		 */
		public static function get_meta_option() {
			return self::$meta_option;
		}

		/**
		 * Metabox Markup
		 *
		 * @param  object $post Post object.
		 * @return void
		 */
		public function markup_meta_box( $post ) {

			wp_nonce_field( basename( __FILE__ ), 'aiosrs-schema' );
			$stored = get_post_meta( $post->ID );

			$schema_meta_keys = array_merge( self::$schema_meta_keys, array( 'bsf-aiosrs-schema-location', 'bsf-aiosrs-schema-exclusion' ) );

			// Set stored and override defaults.
			foreach ( $stored as $key => $value ) {
				if ( in_array( $key, $schema_meta_keys, true ) ) {
					self::$meta_option[ $key ]['default'] = ( isset( $stored[ $key ][0] ) ) ? maybe_unserialize( $stored[ $key ][0] ) : '';
				} else {
					self::$meta_option[ $key ]['default'] = ( isset( $stored[ $key ][0] ) ) ? $stored[ $key ][0] : '';
				}
			}

			// Get defaults.
			$meta = self::get_meta_option();

			/**
			 * Get options
			 */
			$schema_type       = ( isset( $meta['bsf-aiosrs-schema-type']['default'] ) ) ? $meta['bsf-aiosrs-schema-type']['default'] : '';
			$display_locations = ( isset( $meta['bsf-aiosrs-schema-location']['default'] ) ) ? $meta['bsf-aiosrs-schema-location']['default'] : '';
			$exclude_locations = ( isset( $meta['bsf-aiosrs-schema-exclusion']['default'] ) ) ? $meta['bsf-aiosrs-schema-exclusion']['default'] : '';

			$schemas_meta = array(
				'schema_type'       => $schema_type,
				'include-locations' => $display_locations,
				'exclude-locations' => $exclude_locations,
			);

			foreach ( self::$schema_meta_keys as $key ) {
				$schemas_meta[ $key ] = ( isset( $meta[ $key ]['default'] ) ) ? $meta[ $key ]['default'] : array();
			}

			do_action( 'aiosrs_schema_settings_markup_before', $meta );
			$this->render( $schemas_meta );
			do_action( 'aiosrs_schema_settings_markup_after', $meta );
		}

		/**
		 * Page Header Tabs
		 *
		 * @param  array $meta_values Post meta.
		 */
		public function render( $meta_values ) {
			?>
			<table class="bsf-aiosrs-schema-table widefat">
				<tr class="bsf-aiosrs-schema-row">
					<td class="bsf-aiosrs-schema-row-heading">
						<label><?php esc_html_e( 'Schema Type', 'wp-schema-pro' ); ?></label>
						<?php if ( ! isset( $meta_values['schema_type'] ) || empty( $meta_values['schema_type'] ) || ! isset( self::$schema_meta_fields[ 'bsf-aiosrs-' . $meta_values['schema_type'] ] ) ) { ?>
							<i class="bsf-aiosrs-schema-heading-help dashicons dashicons-editor-help" title="<?php echo esc_attr__( 'Select schema type.', 'wp-schema-pro' ); ?>"></i>
						<?php } ?>
					</td>
					<td class="bsf-aiosrs-schema-row-content">
						<div class="bsf-aiosrs-schema-type-wrap">
							<?php
							if ( isset( $meta_values['schema_type'] ) && ! empty( $meta_values['schema_type'] ) && isset( self::$schema_meta_fields[ 'bsf-aiosrs-' . $meta_values['schema_type'] ] ) ) {
								$meta_key = $meta_values['schema_type'];
								echo esc_html( self::$schema_meta_fields[ 'bsf-aiosrs-' . $meta_key ]['label'] );
								?>
								<input type="hidden" id="bsf-aiosrs-schema-type" name="bsf-aiosrs-schema-type" class="bsf-aiosrs-schema-type" value="<?php echo esc_attr( $meta_key ); ?>" >
							<?php } else { ?>
								<select id="bsf-aiosrs-schema-type" name="bsf-aiosrs-schema-type" class="bsf-aiosrs-schema-type" >
									<?php foreach ( self::$schema_meta_fields as $key => $schema_field ) { ?>
										<option <?php selected( $schema_field['key'], $meta_values['schema_type'] ); ?> value="<?php echo esc_attr( $schema_field['key'] ); ?>" ><?php echo esc_html( $schema_field['label'] ); ?></option>
									<?php } ?>
								</select>
							<?php } ?>
						</div>
					</td>
				</tr>
			</table>
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
			<?php foreach ( self::$schema_meta_fields as $key => $value ) { ?>
				<table id="bsf-<?php echo esc_attr( $value['key'] ); ?>-schema-meta-wrap" class="bsf-aiosrs-schema-table bsf-aiosrs-schema-meta-wrap widefat" <?php echo ( $value['key'] !== $meta_values['schema_type'] ) ? 'style="display: none;"' : ''; ?> >
					<?php if ( isset( $value['guideline-link'] ) && ! empty( $value['guideline-link'] ) ) { ?>
						<tr class="bsf-aiosrs-schema-row">
							<td class="bsf-aiosrs-schema-row-heading">
								<label><?php esc_html_e( 'Guidelines', 'wp-schema-pro' ); ?></label>
							</td>
							<td class="bsf-aiosrs-schema-row-content">
								<a href="<?php echo esc_url( $value['guideline-link'] ); ?>" class="bsf-aiosrs-guideline-link" target="_blank" rel="noopener noreferrer">
									<?php
									printf(
										/* translators: %s Schema type */
										esc_html__( 'Read Guidelines for %s Schema', 'wp-schema-pro' ),
										esc_attr( $value['label'] )
									);
									?>
									<i class="dashicons dashicons-external"></i>
								</a>
							</td>
						</tr>
					<?php } ?>
					<?php
					foreach ( $value['subkeys'] as $subkey => $subkey_data ) {
						self::get_meta_markup(
							array(
								'name'        => $key,
								'subkey'      => $subkey,
								'subkey_data' => $subkey_data,
							),
							$meta_values
						);
					}
					?>
				</table>
			<?php } ?>

			<?php
		}

		/**
		 * Get Meta field markup
		 *
		 * @param  array $option_meta Meta fields.
		 * @param  array $meta_values Meta Values array.
		 * @return void
		 */
		public static function get_meta_markup( $option_meta = array(), $meta_values ) {
			if ( ! empty( $option_meta ) ) {

				$name   = $option_meta['name'];
				$subkey = $option_meta['subkey'];

				$is_item_type_render = isset( $option_meta['item_type_class'] ) ? $option_meta['item_type_class'] : '';

				if ( isset( $option_meta['index'] ) ) {
					$index                = $option_meta['index'];
					$name_subkey          = $option_meta['name_subkey'];
					$option_value         = ( isset( $meta_values[ $name ][ $name_subkey ][ $index ][ $subkey ] ) ) ? $meta_values[ $name ][ $name_subkey ][ $index ][ $subkey ] : $option_meta['subkey_data']['default'];
					$custom_text_value    = ( isset( $meta_values[ $name ][ $name_subkey ][ $index ][ $subkey . '-custom-text' ] ) && self::is_not_empty( $meta_values[ $name ][ $name_subkey ][ $index ][ $subkey . '-custom-text' ] ) ) ? $meta_values[ $name ][ $name_subkey ][ $index ][ $subkey . '-custom-text' ] : '';
					$fixed_text_value     = ( isset( $meta_values[ $name ][ $name_subkey ][ $index ][ $subkey . '-fixed-text' ] ) && ! empty( $meta_values[ $name ][ $name_subkey ][ $index ][ $subkey . '-fixed-text' ] ) ) ? $meta_values[ $name ][ $name_subkey ][ $index ][ $subkey . '-fixed-text' ] : '';
					$specific_field_value = ( isset( $meta_values[ $name ][ $name_subkey ][ $index ][ $subkey . '-specific-field' ] ) && ! empty( $meta_values[ $name ][ $name_subkey ][ $index ][ $subkey . '-specific-field' ] ) ) ? $meta_values[ $name ][ $name_subkey ][ $index ][ $subkey . '-specific-field' ] : '';

					$name = $name . '[' . $name_subkey . '][' . $index . ']';
				} else {
					$option_value         = ( isset( $meta_values[ $name ][ $subkey ] ) ) ? $meta_values[ $name ][ $subkey ] : ( isset( $option_meta['subkey_data']['default'] ) ? $option_meta['subkey_data']['default'] : '' );
					$custom_text_value    = ( isset( $meta_values[ $name ][ $subkey . '-custom-text' ] ) && self::is_not_empty( $meta_values[ $name ][ $subkey . '-custom-text' ] ) ) ? $meta_values[ $name ][ $subkey . '-custom-text' ] : '';
					$fixed_text_value     = ( isset( $meta_values[ $name ][ $subkey . '-fixed-text' ] ) && ! empty( $meta_values[ $name ][ $subkey . '-fixed-text' ] ) ) ? $meta_values[ $name ][ $subkey . '-fixed-text' ] : '';
					$specific_field_value = ( isset( $meta_values[ $name ][ $subkey . '-specific-field' ] ) && ! empty( $meta_values[ $name ][ $subkey . '-specific-field' ] ) ) ? $meta_values[ $name ][ $subkey . '-specific-field' ] : '';
				}

				$required = ( isset( $option_meta['subkey_data']['required'] ) && true === $option_meta['subkey_data']['required'] ) ? true : false;

				$option_type   = isset( $option_meta['subkey_data']['type'] ) ? $option_meta['subkey_data']['type'] : 'text';
				$get_meta_type = ( 'image' === $option_type ) ? 'image' : 'text';

				$replace_name = str_replace( array( '][', '-[', ']-', ']', '[' ), '-', $name . '-' );
				$option_name  = $name . '[' . $subkey . ']';
				$option_id    = $replace_name . $subkey;
				$option_class = $replace_name . $subkey;

				$fixed_text_name  = $name . '[' . $subkey . '-fixed-text]';
				$fixed_text_id    = $replace_name . $subkey . '-custom-text';
				$fixed_text_class = $replace_name . $subkey . '-custom-text';

				$custom_meta_attrs = array(
					'name'          => $name . '[' . $subkey . '-custom-text]',
					'id'            => $replace_name . $subkey . '-custom-text',
					'class'         => $replace_name . $subkey . '-custom-text',
					'dropdown-type' => isset( $option_meta['subkey_data']['dropdown-type'] ) ? $option_meta['subkey_data']['dropdown-type'] : '',
				);

				$specific_field_name = $name . '[' . $subkey . '-specific-field]';

				$attrs = '';
				if ( isset( $option_meta['subkey_data']['attrs'] ) && ! empty( $option_meta['subkey_data']['attrs'] ) ) {
					foreach ( $option_meta['subkey_data']['attrs'] as $key => $value ) {
						$attrs .= $key . '="' . esc_attr( $value ) . '" ';
					}
				}
				?>
				<tr class="bsf-aiosrs-schema-row bsf-aiosrs-schema-row-<?php echo esc_attr( $option_type ); ?>-type <?php echo esc_html( $is_item_type_render ); ?>">
					<td class="bsf-aiosrs-schema-row-heading">
						<label>
							<?php echo esc_html( $option_meta['subkey_data']['label'] ); ?>
							<?php if ( $required ) { ?>
								<span class="required">*</span>
							<?php } ?>
						</label>
						<?php if ( isset( $option_meta['subkey_data']['description'] ) && ! empty( $option_meta['subkey_data']['description'] ) ) { ?>
							<i class="bsf-aiosrs-schema-heading-help dashicons dashicons-editor-help" title="<?php echo esc_attr( $option_meta['subkey_data']['description'] ); ?>"></i>
						<?php } ?>
					</td>
					<td class="bsf-aiosrs-schema-row-content">
						<div class="bsf-aiosrs-schema-type-wrap">
							<?php if ( 'repeater' === $option_meta['subkey_data']['type'] ) : ?>
								<?php
								if ( is_array( $option_value ) && count( $option_value ) > 0 ) {
									foreach ( $option_value as $index => $option_subkey_value ) {
										?>
										<div class="aiosrs-pro-repeater-table-wrap">
											<a href="#" class="bsf-repeater-close dashicons dashicons-no-alt"></a>
											<table class="aiosrs-pro-repeater-table" style="border-collapse: separate; border-spacing: 0 1em;">
											<?php
											foreach ( $option_meta['subkey_data']['fields'] as $repeater_subkey => $repeater_subkey_data ) {
												self::get_meta_markup(
													array(
														'name'        => $name,
														'name_subkey' => $subkey,
														'index'       => $index,
														'subkey'      => $repeater_subkey,
														'subkey_data' => $repeater_subkey_data,
													),
													$meta_values
												);
											}
											?>
											</table>
										</div>
										<?php
									}
								} else {
									?>
									<div class="aiosrs-pro-repeater-table-wrap">
										<a href="#" class="bsf-repeater-close dashicons dashicons-no-alt"></a>
										<table class="aiosrs-pro-repeater-table">
										<?php
										foreach ( $option_meta['subkey_data']['fields'] as $repeater_subkey => $repeater_subkey_data ) {
											self::get_meta_markup(
												array(
													'name' => $name,
													'name_subkey' => $subkey,
													'index' => 0,
													'subkey' => $repeater_subkey,
													'subkey_data' => $repeater_subkey_data,
												),
												$meta_values
											);
										}
										?>
										</table>
									</div>
									<?php } ?>
								<button type="button" class="bsf-repeater-add-new-btn button">+ Add</button>
							<?php else : ?>
								<select id="<?php echo esc_attr( $option_id ); ?>" name="<?php echo esc_attr( $option_name ); ?>" class="<?php echo esc_attr( $option_class ); ?> bsf-aiosrs-schema-meta-field" >

									<?php if ( isset( $option_meta['subkey_data']['choices'] ) && is_array( $option_meta['subkey_data']['choices'] ) && ! empty( $option_meta['subkey_data']['choices'] ) ) : ?>
										<?php foreach ( $option_meta['subkey_data']['choices'] as $key => $value ) : ?>
											<option <?php selected( $key, $option_value ); ?> value="<?php echo esc_attr( $key ); ?>" ><?php echo esc_html( $value ); ?></option>
										<?php endforeach; ?>
									<?php else : ?>
										<option value='none'><?php printf( '&mdash; %s &mdash;', esc_html__( 'None', 'wp-schema-pro' ) ); ?></option>

										<?php $post_metadata = apply_filters( 'wp_schema_pro_meta_options', self::get_meta_list( $get_meta_type ), $name, $subkey ); ?>
										<?php if ( is_array( $post_metadata ) && ! empty( $post_metadata ) ) : ?>
											<?php foreach ( $post_metadata as $post_meta ) : ?>
												<optgroup label="<?php echo esc_attr( $post_meta['label'] ); ?>">
													<?php if ( is_array( $post_meta['meta-list'] ) && ! empty( $post_meta['meta-list'] ) ) : ?>
														<?php foreach ( $post_meta['meta-list'] as $key => $value ) : ?>
															<?php $value = apply_filters( 'wp_schema_pro_mapping_option_string_' . $key, $value, $option_type ); ?>
															<option <?php selected( $key, $option_value ); ?> value="<?php echo esc_attr( $key ); ?>" ><?php echo esc_html( $value ); ?></option>
														<?php endforeach; ?>
													<?php endif; ?>
												</optgroup>
											<?php endforeach; ?>
										<?php endif; ?>
									<?php endif; ?>
								</select>
								<div class="bsf-aiosrs-schema-specific-field-wrap <?php echo ( 'specific-field' !== $option_value ) ? 'bsf-hidden-field' : ''; ?>">
									<select id="<?php echo esc_attr( $specific_field_name ); ?>" name="<?php echo esc_attr( $specific_field_name ); ?>" class="bsf-aiosrs-schema-select2 bsf-aiosrs-schema-specific-field" >
										<?php if ( $specific_field_value ) { ?>
											<option value="<?php echo esc_attr( $specific_field_value ); ?>" selected="selected" ><?php echo esc_html( preg_replace( '/^_/', '', esc_html( str_replace( '_', ' ', $specific_field_value ) ) ) ); ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="bsf-aiosrs-schema-custom-text-wrap <?php echo ( 'custom-text' !== $option_value && 'fixed-text' !== $option_value ) ? 'bsf-hidden-field' : ''; ?>" >
									<?php self::get_custom_field_default( $option_type, $custom_text_value, $custom_meta_attrs, $attrs ); ?>
								</div>
								<?php if ( 'image' === $option_type ) { ?>
								<div class="bsf-aiosrs-schema-fixed-text-wrap <?php echo ( 'fixed-text' !== $option_value ) ? 'bsf-hidden-field' : ''; ?>" >
									<input type="text" id="<?php echo esc_attr( $fixed_text_id ); ?>" name="<?php echo esc_attr( $fixed_text_name ); ?>" class="<?php echo esc_attr( $fixed_text_class ); ?>" value="<?php echo esc_attr( $fixed_text_value ); ?>" <?php echo esc_attr( $attrs ); ?> >
								</div>
								<?php } ?>
							<?php endif; ?>
						</div>
					</td>
				</tr>
				<?php
			}
		}

		/**
		 * Get Custom field default value.
		 *
		 * @param string $type Custom Field type.
		 * @param string $default_value Custom Field value.
		 * @param array  $attrs Field attrubutes.
		 * @param array  $field_attrs Field attrubutes in string.
		 * @return void
		 */
		public static function get_custom_field_default( $type = 'text', $default_value = '', $attrs = array(), $field_attrs = '' ) {

			switch ( $type ) {
				case 'text':
				case 'number':
				case 'tel':
				case 'date':
				case 'time':
				case 'datetime-local':
					?>
					<input type="<?php echo esc_attr( $type ); ?>" id="<?php echo isset( $attrs['id'] ) ? esc_attr( $attrs['id'] ) : ''; ?>" class="<?php echo isset( $attrs['class'] ) ? esc_attr( $attrs['class'] ) : ''; ?>" name="<?php echo isset( $attrs['name'] ) ? esc_attr( $attrs['name'] ) : ''; ?>" value="<?php echo isset( $default_value ) ? esc_attr( $default_value ) : ''; ?>" <?php echo esc_attr( $field_attrs ); ?> >
					<?php
					break;

				case 'textarea':
					?>
					<textarea id="<?php echo isset( $attrs['id'] ) ? esc_attr( $attrs['id'] ) : ''; ?>" class="bsf-textarea-field <?php echo isset( $attrs['class'] ) ? esc_attr( $attrs['class'] ) : ''; ?>" name="<?php echo isset( $attrs['name'] ) ? esc_attr( $attrs['name'] ) : ''; ?>" <?php echo esc_attr( $field_attrs ); ?> ><?php echo isset( $default_value ) ? esc_attr( $default_value ) : ''; ?></textarea>
					<?php
					break;

				case 'rating':
					?>
					<div class="aiosrs-pro-custom-field-rating">
						<input type="number" id="<?php echo isset( $attrs['id'] ) ? esc_attr( $attrs['id'] ) : ''; ?>" class="bsf-rating-field <?php echo isset( $attrs['class'] ) ? esc_attr( $attrs['class'] ) : ''; ?>" name="<?php echo isset( $attrs['name'] ) ? esc_attr( $attrs['name'] ) : ''; ?>" value="<?php echo isset( $default_value ) ? esc_attr( $default_value ) : ''; ?>" min="0" max="5" step="0.1" <?php echo esc_attr( $field_attrs ); ?> >
						<div class="aiosrs-star-rating-wrap" ><!-- comment
							<?php
								$filled = 0;
								$half   = 0;

							$star_index = 1;
							if ( isset( $default_value ) && is_numeric( $default_value ) ) {
								$filled = ( $default_value > 5 ) ? 5 : ( $default_value < 0 ? 0 : (int) $default_value );
								$half   = $default_value === $filled || $default_value > 5 || $default_value < 0 ? 0 : 1;

								for ( $i = 0; $i < $filled; $i++ ) {
									?>
										--><span class="aiosrs-star-rating dashicons dashicons-star-filled" data-index="<?php echo esc_attr( $star_index ); ?>"></span><!-- comment
									<?php
									$star_index++;
								}

								if ( $half ) {
									?>
										--><span class="aiosrs-star-rating dashicons dashicons-star-half" data-index="<?php echo esc_attr( $star_index ); ?>"></span><!-- comment
									<?php
									$star_index++;
								}
							}

								$empty = 5 - ( $filled + $half );
							for ( $i = 0; $i < $empty; $i++ ) {
								?>
									--><span class="aiosrs-star-rating dashicons dashicons-star-empty" data-index="<?php echo esc_attr( $star_index ); ?>"></span><!-- comment
								<?php
								$star_index++;
							}
							?>
						--></div>
					</div>
					<?php
					break;

				case 'image':
					if ( ! empty( $default_value ) ) {
						$image_url = wp_get_attachment_url( $default_value );
					}
					?>
					<div class="aiosrs-pro-custom-field-single-image">
						<input type="hidden" id="<?php echo isset( $attrs['id'] ) ? esc_attr( $attrs['id'] ) : ''; ?>" class="single-image-field <?php echo isset( $attrs['class'] ) ? esc_attr( $attrs['class'] ) : ''; ?>" name="<?php echo isset( $attrs['name'] ) ? esc_attr( $attrs['name'] ) : ''; ?>" value="<?php echo isset( $default_value ) ? esc_attr( $default_value ) : ''; ?>" <?php echo esc_attr( $field_attrs ); ?> >
						<div class="image-field-wrap <?php echo ( isset( $image_url ) && ! empty( $image_url ) ) ? 'bsf-custom-image-selected' : ''; ?>">
							<a href="#" class="aiosrs-image-select button"><span class="dashicons dashicons-format-image"></span><?php esc_html_e( 'Select Image', 'wp-schema-pro' ); ?></a>
							<a href="#" class="aiosrs-image-remove dashicons dashicons-no-alt wp-ui-text-highlight"></a>
							<?php if ( isset( $image_url ) && ! empty( $image_url ) ) : ?>
								<a href="#" class="aiosrs-image-select img"><img src="<?php echo esc_url( $image_url ); ?>" /></a>
							<?php endif; ?>
						</div>
					</div>
					<?php
					break;

				case 'multi-select':
					$selected_options = array();
					$option_list      = self::get_dropdown_options( $attrs['dropdown-type'] );
					$option_list      = array_filter( $option_list );
					if ( ! empty( $default_value ) ) {
						$selected_options = explode( ',', $default_value );
					}
					?>
					<div class="multi-select-wrap">
						<input type="hidden" id="<?php echo isset( $attrs['id'] ) ? esc_attr( $attrs['id'] ) : ''; ?>" class="<?php echo isset( $attrs['class'] ) ? esc_attr( $attrs['class'] ) : ''; ?>" name="<?php echo isset( $attrs['name'] ) ? esc_attr( $attrs['name'] ) : ''; ?>" value="<?php echo isset( $default_value ) ? esc_attr( $default_value ) : ''; ?>" <?php echo esc_attr( $field_attrs ); ?> >
						<select multiple="true" >
							<?php
							if ( ! empty( $option_list ) ) {
								foreach ( $option_list as $key => $value ) {
									$value = explode( ':', trim( $value ) );
									$key   = $value[0];
									$text  = isset( $value[1] ) ? $value[1] : $value[0];
									?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php in_array( $key, $selected_options, true ) ? selected( 1 ) : ''; ?>><?php echo esc_attr( $text ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
					<?php
					break;

				case 'dropdown':
					$option_list = self::get_dropdown_options( $attrs['dropdown-type'] );
					$option_list = array_filter( $option_list );
					?>
					<select id="<?php echo isset( $attrs['id'] ) ? esc_attr( $attrs['id'] ) : ''; ?>" class="<?php echo isset( $attrs['class'] ) ? esc_attr( $attrs['class'] ) : ''; ?>" name="<?php echo isset( $attrs['name'] ) ? esc_attr( $attrs['name'] ) : ''; ?>" >
						<?php
						if ( ! empty( $option_list ) ) {
							foreach ( $option_list as $key => $value ) {
								?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $default_value, $key ); ?>><?php echo esc_attr( $value ); ?></option>
								<?php
							}
						}
						?>
					</select>
					<?php
					break;

				case 'time-duration':
					?>
					<div class="aiosrs-pro-custom-field-time-duration">
						<input type="hidden" id="<?php echo isset( $attrs['id'] ) ? esc_attr( $attrs['id'] ) : ''; ?>" class="time-duration-field <?php echo isset( $attrs['class'] ) ? esc_attr( $attrs['class'] ) : ''; ?>" name="<?php echo isset( $attrs['name'] ) ? esc_attr( $attrs['name'] ) : ''; ?>" value="<?php echo isset( $default_value ) ? esc_attr( $default_value ) : ''; ?>" <?php echo esc_attr( $field_attrs ); ?> >
						<div class="time-duration-wrap">
							<?php
							if ( ! empty( $default_value ) ) {
								$interval = new DateInterval( $default_value );
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
					</div>
					<?php
					break;

				default:
					?>
					<input type="text" id="<?php echo isset( $attrs['id'] ) ? esc_attr( $attrs['id'] ) : ''; ?>" class="<?php echo isset( $attrs['class'] ) ? esc_attr( $attrs['class'] ) : ''; ?>" name="<?php echo isset( $attrs['name'] ) ? esc_attr( $attrs['name'] ) : ''; ?>" value="<?php echo isset( $default_value ) ? esc_attr( $default_value ) : ''; ?>" <?php echo esc_attr( $field_attrs ); ?> >
					<?php
					break;
			}
		}

		/**
		 * Get Dropdown options.
		 *
		 * @param  string $name Field Name.
		 * @return array
		 */
		public static function get_dropdown_options( $name = '' ) {

			switch ( $name ) {
				case 'availability':
					$return = apply_filters(
						'wp_schema_pro_availability_options',
						array(
							'Discontinued'        => __( 'Discontinued', 'wp-schema-pro' ),
							'InStock'             => __( 'InStock', 'wp-schema-pro' ),
							'InStoreOnly'         => __( 'InStoreOnly', 'wp-schema-pro' ),
							'LimitedAvailability' => __( 'LimitedAvailability', 'wp-schema-pro' ),
							'OnlineOnly'          => __( 'OnlineOnly', 'wp-schema-pro' ),
							'OutOfStock'          => __( 'OutOfStock', 'wp-schema-pro' ),
							'PreOrder'            => __( 'PreOrder', 'wp-schema-pro' ),
							'PreSale'             => __( 'PreSale', 'wp-schema-pro' ),
							'SoldOut'             => __( 'SoldOut', 'wp-schema-pro' ),
						)
					);
					break;

				case 'book-format':
					$return = apply_filters(
						'wp_schema_pro_book_format_options',
						array(
							'EBook'     => __( 'EBook', 'wp-schema-pro' ),
							'Hardcover' => __( 'Hardcover', 'wp-schema-pro' ),
							'Paperback' => __( 'Paperback', 'wp-schema-pro' ),
							'AudioBook' => __( 'AudioBook', 'wp-schema-pro' ),
						)
					);
					break;
				case 'event-status':
					$return = apply_filters(
						'wp_schema_pro_event_status_options',
						array(
							'EventScheduled'   => __( 'Scheduled', 'wp-schema-pro' ),
							'EventRescheduled' => __( 'Rescheduled', 'wp-schema-pro' ),
							'EventPostponed'   => __( 'Postponed', 'wp-schema-pro' ),
							'EventMovedOnline' => __( 'Moved Online', 'wp-schema-pro' ),
							'EventCancelled'   => __( 'Cancelled', 'wp-schema-pro' ),
						)
					);
					break;
				case 'event-attendance-mode':
					$return = apply_filters(
						'wp_schema_pro_event_attendance_mode_options',
						array(
							'OfflineEventAttendanceMode' => __( 'Physical Location', 'wp-schema-pro' ),
							'OnlineEventAttendanceMode'  => __( 'Online Event', 'wp-schema-pro' ),
							'MixedEventAttendanceMode'   => __( 'Mix Of Online & Physical Locations', 'wp-schema-pro' ),

						)
					);
					break;
				case 'action-platform':
					$return = apply_filters(
						'wp_schema_pro_action_platform_options',
						array(
							'DesktopWebPlatform' => __( 'DesktopWebPlatform', 'wp-schema-pro' ),
							'MobileWebPlatform'  => __( 'MobileWebPlatform', 'wp-schema-pro' ),
							'AndroidPlatform'    => __( 'AndroidPlatform', 'wp-schema-pro' ),
							'IOSPlatform'        => __( 'IOSPlatform', 'wp-schema-pro' ),
						)
					);
					break;
				case 'days':
					$return = apply_filters(
						'wp_schema_pro_days_options',
						array(
							'Monday'    => __( 'Monday', 'wp-schema-pro' ),
							'Tuesday'   => __( 'Tuesday', 'wp-schema-pro' ),
							'Wednesday' => __( 'Wednesday', 'wp-schema-pro' ),
							'Thursday'  => __( 'Thursday', 'wp-schema-pro' ),
							'Friday'    => __( 'Friday', 'wp-schema-pro' ),
							'Saturday'  => __( 'Saturday', 'wp-schema-pro' ),
							'Sunday'    => __( 'Sunday', 'wp-schema-pro' ),
						)
					);
					break;
				case 'country':
					$return = apply_filters(
						'wp_schema_pro_country_options',
						array(
							'AF' => __( 'Afghanistan', 'wp-schema-pro' ),
							'AX' => __( 'Åland Islands', 'wp-schema-pro' ),
							'AL' => __( 'Albania', 'wp-schema-pro' ),
							'DZ' => __( 'Algeria', 'wp-schema-pro' ),
							'AS' => __( 'American Samoa', 'wp-schema-pro' ),
							'AD' => __( 'Andorra', 'wp-schema-pro' ),
							'AO' => __( 'Angola', 'wp-schema-pro' ),
							'AI' => __( 'Anguilla', 'wp-schema-pro' ),
							'AQ' => __( 'Antarctica', 'wp-schema-pro' ),
							'AG' => __( 'Antigua and Barbuda', 'wp-schema-pro' ),
							'AR' => __( 'Argentina', 'wp-schema-pro' ),
							'AM' => __( 'Armenia', 'wp-schema-pro' ),
							'AW' => __( 'Aruba', 'wp-schema-pro' ),
							'AU' => __( 'Australia', 'wp-schema-pro' ),
							'AT' => __( 'Austria', 'wp-schema-pro' ),
							'AZ' => __( 'Azerbaijan', 'wp-schema-pro' ),
							'BH' => __( 'Bahrain', 'wp-schema-pro' ),
							'BS' => __( 'Bahamas', 'wp-schema-pro' ),
							'BD' => __( 'Bangladesh', 'wp-schema-pro' ),
							'BB' => __( 'Barbados', 'wp-schema-pro' ),
							'BY' => __( 'Belarus', 'wp-schema-pro' ),
							'BE' => __( 'Belgium', 'wp-schema-pro' ),
							'BZ' => __( 'Belize', 'wp-schema-pro' ),
							'BJ' => __( 'Benin', 'wp-schema-pro' ),
							'BM' => __( 'Bermuda', 'wp-schema-pro' ),
							'BT' => __( 'Bhutan', 'wp-schema-pro' ),
							'BQ' => __( 'Bonaire, Sint Eustatius and Saba', 'wp-schema-pro' ),
							'BA' => __( 'Bosnia and Herzegovina', 'wp-schema-pro' ),
							'BW' => __( 'Botswana', 'wp-schema-pro' ),
							'BV' => __( 'Bouvet Island', 'wp-schema-pro' ),
							'BR' => __( 'Brazil', 'wp-schema-pro' ),
							'IO' => __( 'British Indian Ocean Territory', 'wp-schema-pro' ),
							'BN' => __( 'Brunei Darussalam', 'wp-schema-pro' ),
							'BG' => __( 'Bulgaria', 'wp-schema-pro' ),
							'BF' => __( 'Burkina Faso', 'wp-schema-pro' ),
							'BI' => __( 'Burundi', 'wp-schema-pro' ),
							'KH' => __( 'Cambodia', 'wp-schema-pro' ),
							'CM' => __( 'Cameroon', 'wp-schema-pro' ),
							'CA' => __( 'Canada', 'wp-schema-pro' ),
							'CV' => __( 'Cape Verde', 'wp-schema-pro' ),
							'KY' => __( 'Cayman Islands', 'wp-schema-pro' ),
							'CF' => __( 'Central African Republic', 'wp-schema-pro' ),
							'TD' => __( 'Chad', 'wp-schema-pro' ),
							'CL' => __( 'Chile', 'wp-schema-pro' ),
							'CN' => __( 'China', 'wp-schema-pro' ),
							'CX' => __( 'Christmas Island', 'wp-schema-pro' ),
							'CC' => __( 'Cocos (Keeling) Islands', 'wp-schema-pro' ),
							'CO' => __( 'Colombia', 'wp-schema-pro' ),
							'KM' => __( 'Comoros', 'wp-schema-pro' ),
							'CG' => __( 'Congo', 'wp-schema-pro' ),
							'CD' => __( 'Congo, the Democratic Republic of the', 'wp-schema-pro' ),
							'CK' => __( 'Cook Islands', 'wp-schema-pro' ),
							'CR' => __( 'Costa Rica', 'wp-schema-pro' ),
							'CI' => __( 'Ivory Coast', 'wp-schema-pro' ),
							'HR' => __( 'Croatia', 'wp-schema-pro' ),
							'CU' => __( 'Cuba', 'wp-schema-pro' ),
							'CW' => __( 'Curaçao', 'wp-schema-pro' ),
							'CY' => __( 'Cyprus', 'wp-schema-pro' ),
							'CZ' => __( 'Czech Republic', 'wp-schema-pro' ),
							'DK' => __( 'Denmark', 'wp-schema-pro' ),
							'DJ' => __( 'Djibouti', 'wp-schema-pro' ),
							'DM' => __( 'Dominica', 'wp-schema-pro' ),
							'DO' => __( 'Dominican Republic', 'wp-schema-pro' ),
							'EC' => __( 'Ecuador', 'wp-schema-pro' ),
							'EG' => __( 'Egypt', 'wp-schema-pro' ),
							'SV' => __( 'El Salvador', 'wp-schema-pro' ),
							'GQ' => __( 'Equatorial Guinea', 'wp-schema-pro' ),
							'ER' => __( 'Eritrea', 'wp-schema-pro' ),
							'EE' => __( 'Estonia', 'wp-schema-pro' ),
							'ET' => __( 'Ethiopia', 'wp-schema-pro' ),
							'FK' => __( 'Falkland Islands (Malvinas)', 'wp-schema-pro' ),
							'FO' => __( 'Faroe Islands', 'wp-schema-pro' ),
							'FM' => __( 'Federated States of Micronesia', 'wp-schema-pro' ),
							'FJ' => __( 'Fiji', 'wp-schema-pro' ),
							'FI' => __( 'Finland', 'wp-schema-pro' ),
							'FR' => __( 'France', 'wp-schema-pro' ),
							'GF' => __( 'French Guiana', 'wp-schema-pro' ),
							'PF' => __( 'French Polynesia', 'wp-schema-pro' ),
							'TF' => __( 'French Southern Territories', 'wp-schema-pro' ),
							'GA' => __( 'Gabon', 'wp-schema-pro' ),
							'GM' => __( 'Gambia', 'wp-schema-pro' ),
							'GE' => __( 'Georgia', 'wp-schema-pro' ),
							'DE' => __( 'Germany', 'wp-schema-pro' ),
							'GH' => __( 'Ghana', 'wp-schema-pro' ),
							'GI' => __( 'Gibraltar', 'wp-schema-pro' ),
							'GR' => __( 'Greece', 'wp-schema-pro' ),
							'GL' => __( 'Greenland', 'wp-schema-pro' ),
							'GD' => __( 'Grenada', 'wp-schema-pro' ),
							'GP' => __( 'Guadeloupe', 'wp-schema-pro' ),
							'GU' => __( 'Guam', 'wp-schema-pro' ),
							'GT' => __( 'Guatemala', 'wp-schema-pro' ),
							'GG' => __( 'Guernsey', 'wp-schema-pro' ),
							'GN' => __( 'Guinea', 'wp-schema-pro' ),
							'GW' => __( 'Guinea-Bissau', 'wp-schema-pro' ),
							'GY' => __( 'Guyana', 'wp-schema-pro' ),
							'HT' => __( 'Haiti', 'wp-schema-pro' ),
							'HM' => __( 'Heard Island and McDonald Islands', 'wp-schema-pro' ),
							'VA' => __( 'Holy See (Vatican City State)', 'wp-schema-pro' ),
							'HN' => __( 'Honduras', 'wp-schema-pro' ),
							'HK' => __( 'Hong Kong', 'wp-schema-pro' ),
							'HU' => __( 'Hungary', 'wp-schema-pro' ),
							'IS' => __( 'Iceland', 'wp-schema-pro' ),
							'IN' => __( 'India', 'wp-schema-pro' ),
							'ID' => __( 'Indonesia', 'wp-schema-pro' ),
							'IR' => __( 'Iran, Islamic Republic of', 'wp-schema-pro' ),
							'IQ' => __( 'Iraq', 'wp-schema-pro' ),
							'IE' => __( 'Ireland', 'wp-schema-pro' ),
							'IM' => __( 'Isle of Man', 'wp-schema-pro' ),
							'IL' => __( 'Israel', 'wp-schema-pro' ),
							'IT' => __( 'Italy', 'wp-schema-pro' ),
							'JM' => __( 'Jamaica', 'wp-schema-pro' ),
							'JP' => __( 'Japan', 'wp-schema-pro' ),
							'JE' => __( 'Jersey', 'wp-schema-pro' ),
							'JO' => __( 'Jordan', 'wp-schema-pro' ),
							'KZ' => __( 'Kazakhstan', 'wp-schema-pro' ),
							'KE' => __( 'Kenya', 'wp-schema-pro' ),
							'KI' => __( 'Kiribati', 'wp-schema-pro' ),
							'KP' => __( 'Korea, Democratic People\'s Republic of', 'wp-schema-pro' ),
							'KR' => __( 'Korea, Republic of', 'wp-schema-pro' ),
							'KW' => __( 'Kuwait', 'wp-schema-pro' ),
							'KG' => __( 'Kyrgyzstan', 'wp-schema-pro' ),
							'LA' => __( 'Laos', 'wp-schema-pro' ),
							'LV' => __( 'Latvia', 'wp-schema-pro' ),
							'LB' => __( 'Lebanon', 'wp-schema-pro' ),
							'LS' => __( 'Lesotho', 'wp-schema-pro' ),
							'LR' => __( 'Liberia', 'wp-schema-pro' ),
							'LY' => __( 'Libya', 'wp-schema-pro' ),
							'LI' => __( 'Liechtenstein', 'wp-schema-pro' ),
							'LT' => __( 'Lithuania', 'wp-schema-pro' ),
							'LU' => __( 'Luxembourg', 'wp-schema-pro' ),
							'MO' => __( 'Macao', 'wp-schema-pro' ),
							'MG' => __( 'Madagascar', 'wp-schema-pro' ),
							'MW' => __( 'Malawi', 'wp-schema-pro' ),
							'MY' => __( 'Malaysia', 'wp-schema-pro' ),
							'MV' => __( 'Maldives', 'wp-schema-pro' ),
							'ML' => __( 'Mali', 'wp-schema-pro' ),
							'MT' => __( 'Malta', 'wp-schema-pro' ),
							'MH' => __( 'Marshall Islands', 'wp-schema-pro' ),
							'MQ' => __( 'Martinique', 'wp-schema-pro' ),
							'MR' => __( 'Mauritania', 'wp-schema-pro' ),
							'MU' => __( 'Mauritius', 'wp-schema-pro' ),
							'YT' => __( 'Mayotte', 'wp-schema-pro' ),
							'MX' => __( 'Mexico', 'wp-schema-pro' ),
							'MC' => __( 'Monaco', 'wp-schema-pro' ),
							'MN' => __( 'Mongolia', 'wp-schema-pro' ),
							'ME' => __( 'Montenegro', 'wp-schema-pro' ),
							'MS' => __( 'Montserrat', 'wp-schema-pro' ),
							'MA' => __( 'Morocco', 'wp-schema-pro' ),
							'MZ' => __( 'Mozambique', 'wp-schema-pro' ),
							'MM' => __( 'Myanmar', 'wp-schema-pro' ),
							'NA' => __( 'Namibia', 'wp-schema-pro' ),
							'NR' => __( 'Nauru', 'wp-schema-pro' ),
							'NP' => __( 'Nepal', 'wp-schema-pro' ),
							'NL' => __( 'Netherlands', 'wp-schema-pro' ),
							'NC' => __( 'New Caledonia', 'wp-schema-pro' ),
							'NZ' => __( 'New Zealand', 'wp-schema-pro' ),
							'NI' => __( 'Nicaragua', 'wp-schema-pro' ),
							'NE' => __( 'Niger', 'wp-schema-pro' ),
							'NG' => __( 'Nigeria', 'wp-schema-pro' ),
							'NU' => __( 'Niue', 'wp-schema-pro' ),
							'NF' => __( 'Norfolk Island', 'wp-schema-pro' ),
							'MP' => __( 'Northern Mariana Islands', 'wp-schema-pro' ),
							'NO' => __( 'Norway', 'wp-schema-pro' ),
							'OM' => __( 'Oman', 'wp-schema-pro' ),
							'PK' => __( 'Pakistan', 'wp-schema-pro' ),
							'PW' => __( 'Palau', 'wp-schema-pro' ),
							'PS' => __( 'Palestine, State of', 'wp-schema-pro' ),
							'PA' => __( 'Panama', 'wp-schema-pro' ),
							'PG' => __( 'Papua New Guinea', 'wp-schema-pro' ),
							'PY' => __( 'Paraguay', 'wp-schema-pro' ),
							'PE' => __( 'Peru', 'wp-schema-pro' ),
							'PH' => __( 'Philippines', 'wp-schema-pro' ),
							'PN' => __( 'Pitcairn', 'wp-schema-pro' ),
							'BO' => __( 'Plurinational State of Bolivia', 'wp-schema-pro' ),
							'PL' => __( 'Poland', 'wp-schema-pro' ),
							'PT' => __( 'Portugal', 'wp-schema-pro' ),
							'PR' => __( 'Puerto Rico', 'wp-schema-pro' ),
							'QA' => __( 'Qatar', 'wp-schema-pro' ),
							'RE' => __( 'Réunion', 'wp-schema-pro' ),
							'MK' => __( 'Republic of Macedonia', 'wp-schema-pro' ),
							'MD' => __( 'Republic of Moldova', 'wp-schema-pro' ),
							'RO' => __( 'Romania', 'wp-schema-pro' ),
							'RU' => __( 'Russian Federation', 'wp-schema-pro' ),
							'RW' => __( 'Rwanda', 'wp-schema-pro' ),
							'BL' => __( 'Saint Barthélemy', 'wp-schema-pro' ),
							'SH' => __( 'Saint Helena, Ascension and Tristan da Cunha', 'wp-schema-pro' ),
							'KN' => __( 'Saint Kitts and Nevis', 'wp-schema-pro' ),
							'LC' => __( 'Saint Lucia', 'wp-schema-pro' ),
							'MF' => __( 'Saint Martin (French part', 'wp-schema-pro' ),
							'PM' => __( 'Saint Pierre and Miquelon', 'wp-schema-pro' ),
							'VC' => __( 'Saint Vincent and the Grenadines', 'wp-schema-pro' ),
							'WS' => __( 'Samoa', 'wp-schema-pro' ),
							'SM' => __( 'San Marino', 'wp-schema-pro' ),
							'ST' => __( 'Sao Tome and Principe', 'wp-schema-pro' ),
							'SA' => __( 'Saudi Arabia', 'wp-schema-pro' ),
							'SN' => __( 'Senegal', 'wp-schema-pro' ),
							'RS' => __( 'Serbia', 'wp-schema-pro' ),
							'SC' => __( 'Seychelles', 'wp-schema-pro' ),
							'SL' => __( 'Sierra Leone', 'wp-schema-pro' ),
							'SG' => __( 'Singapore', 'wp-schema-pro' ),
							'SX' => __( 'Sint Maarten (Dutch part)', 'wp-schema-pro' ),
							'SK' => __( 'Slovakia', 'wp-schema-pro' ),
							'SI' => __( 'Slovenia', 'wp-schema-pro' ),
							'SB' => __( 'Solomon Islands', 'wp-schema-pro' ),
							'SO' => __( 'Somalia', 'wp-schema-pro' ),
							'ZA' => __( 'South Africa', 'wp-schema-pro' ),
							'GS' => __( 'South Georgia and the South Sandwich Islands', 'wp-schema-pro' ),
							'SS' => __( 'South Sudan', 'wp-schema-pro' ),
							'ES' => __( 'Spain', 'wp-schema-pro' ),
							'LK' => __( 'Sri Lanka', 'wp-schema-pro' ),
							'SD' => __( 'Sudan', 'wp-schema-pro' ),
							'SR' => __( 'Suriname', 'wp-schema-pro' ),
							'SJ' => __( 'Svalbard and Jan Mayen', 'wp-schema-pro' ),
							'SZ' => __( 'Swaziland', 'wp-schema-pro' ),
							'SE' => __( 'Sweden', 'wp-schema-pro' ),
							'CH' => __( 'Switzerland', 'wp-schema-pro' ),
							'SY' => __( 'Syrian Arab Republic', 'wp-schema-pro' ),
							'TW' => __( 'Taiwan, Province of China', 'wp-schema-pro' ),
							'TJ' => __( 'Tajikistan', 'wp-schema-pro' ),
							'TZ' => __( 'Tanzania, United Republic of', 'wp-schema-pro' ),
							'TH' => __( 'Thailand', 'wp-schema-pro' ),
							'TL' => __( 'Timor-Leste', 'wp-schema-pro' ),
							'TG' => __( 'Togo', 'wp-schema-pro' ),
							'TK' => __( 'Tokelau', 'wp-schema-pro' ),
							'TO' => __( 'Tonga', 'wp-schema-pro' ),
							'TT' => __( 'Trinidad and Tobago', 'wp-schema-pro' ),
							'TN' => __( 'Tunisia', 'wp-schema-pro' ),
							'TR' => __( 'Turkey', 'wp-schema-pro' ),
							'TM' => __( 'Turkmenistan', 'wp-schema-pro' ),
							'TC' => __( 'Turks and Caicos Islands', 'wp-schema-pro' ),
							'TV' => __( 'Tuvalu', 'wp-schema-pro' ),
							'UG' => __( 'Uganda', 'wp-schema-pro' ),
							'UA' => __( 'Ukraine', 'wp-schema-pro' ),
							'AE' => __( 'United Arab Emirates', 'wp-schema-pro' ),
							'GB' => __( 'United Kingdom', 'wp-schema-pro' ),
							'US' => __( 'United States', 'wp-schema-pro' ),
							'UM' => __( 'United States Minor Outlying Islands', 'wp-schema-pro' ),
							'UY' => __( 'Uruguay', 'wp-schema-pro' ),
							'UZ' => __( 'Uzbekistan', 'wp-schema-pro' ),
							'VU' => __( 'Vanuatu', 'wp-schema-pro' ),
							'VE' => __( 'Venezuela, Bolivarian Republic of', 'wp-schema-pro' ),
							'VN' => __( 'Viet Nam', 'wp-schema-pro' ),
							'VG' => __( 'Virgin Islands, British', 'wp-schema-pro' ),
							'VI' => __( 'Virgin Islands, U.S', 'wp-schema-pro' ),
							'WF' => __( 'Wallis and Futuna', 'wp-schema-pro' ),
							'EH' => __( 'Western Sahara', 'wp-schema-pro' ),
							'YE' => __( 'Yemen', 'wp-schema-pro' ),
							'ZM' => __( 'Zambia', 'wp-schema-pro' ),
							'ZW' => __( 'Zimbabwe', 'wp-schema-pro' ),
						)
					);
					break;
				case 'employment':
					$return = apply_filters(
						'wp_schema_pro_employment_options',
						array(
							'FULL_TIME'  => __( 'FULL_TIME', 'wp-schema-pro' ),
							'PART_TIME'  => __( 'PART_TIME', 'wp-schema-pro' ),
							'CONTRACTOR' => __( 'CONTRACTOR', 'wp-schema-pro' ),
							'TEMPORARY'  => __( 'TEMPORARY', 'wp-schema-pro' ),
							'INTERN'     => __( 'INTERN', 'wp-schema-pro' ),
							'VOLUNTEER'  => __( 'VOLUNTEER', 'wp-schema-pro' ),
							'PER_DIEM'   => __( 'PER_DIEM', 'wp-schema-pro' ),
							'OTHER'      => __( 'OTHER', 'wp-schema-pro' ),
						)
					);
					break;
				case 'currency':
					$return = apply_filters(
						'wp_schema_pro_currency_options',
						array(
							'AFA' => __( 'Afghan Afghani', 'wp-schema-pro' ),
							'ALL' => __( 'Albanian Lek', 'wp-schema-pro' ),
							'DZD' => __( 'Algerian Dinar', 'wp-schema-pro' ),
							'AOA' => __( 'Angolan Kwanza', 'wp-schema-pro' ),
							'ARS' => __( 'Argentine Peso', 'wp-schema-pro' ),
							'AMD' => __( 'Armenian Dram', 'wp-schema-pro' ),
							'AWG' => __( 'Aruban Florin', 'wp-schema-pro' ),
							'AUD' => __( 'Australian Dollar', 'wp-schema-pro' ),
							'AZN' => __( 'Azerbaijani Manat', 'wp-schema-pro' ),
							'BSD' => __( 'Bahamian Dollar', 'wp-schema-pro' ),
							'BHD' => __( 'Bahraini Dinar', 'wp-schema-pro' ),
							'BDT' => __( 'Bangladeshi Taka', 'wp-schema-pro' ),
							'BBD' => __( 'Barbadian Dollar', 'wp-schema-pro' ),
							'BYR' => __( 'Belarusian Ruble', 'wp-schema-pro' ),
							'BEF' => __( 'Belgian Franc', 'wp-schema-pro' ),
							'BZD' => __( 'Belize Dollar', 'wp-schema-pro' ),
							'BMD' => __( 'Bermudan Dollar', 'wp-schema-pro' ),
							'BTN' => __( 'Bhutanese Ngultrum', 'wp-schema-pro' ),
							'BTC' => __( 'Bitcoin', 'wp-schema-pro' ),
							'BOB' => __( 'Bolivian Boliviano', 'wp-schema-pro' ),
							'BAM' => __( 'Bosnia-Herzegovina Convertible Mark', 'wp-schema-pro' ),
							'BWP' => __( 'Botswanan Pula', 'wp-schema-pro' ),
							'BRL' => __( 'Brazilian Real', 'wp-schema-pro' ),
							'GBP' => __( 'British Pound', 'wp-schema-pro' ),
							'BND' => __( 'Brunei Dollar', 'wp-schema-pro' ),
							'BGN' => __( 'Bulgarian Lev', 'wp-schema-pro' ),
							'BIF' => __( 'Burundian Franc', 'wp-schema-pro' ),
							'KHR' => __( 'Cambodian Riel', 'wp-schema-pro' ),
							'CAD' => __( 'Canadian Dollar', 'wp-schema-pro' ),
							'CVE' => __( 'Cape Verdean Escudo', 'wp-schema-pro' ),
							'KYD' => __( 'Cayman Islands Dollar', 'wp-schema-pro' ),
							'XAF' => __( 'Central African CFA Franc', 'wp-schema-pro' ),
							'XPF' => __( 'CFP Franc', 'wp-schema-pro' ),
							'CLP' => __( 'Chilean Peso', 'wp-schema-pro' ),
							'CNY' => __( 'Chinese Yuan', 'wp-schema-pro' ),
							'COP' => __( 'Colombian Peso', 'wp-schema-pro' ),
							'KMF' => __( 'Comorian Franc', 'wp-schema-pro' ),
							'CDF' => __( 'Congolese Franc', 'wp-schema-pro' ),
							'CRC' => __( 'Costa Rican Colón', 'wp-schema-pro' ),
							'HRK' => __( 'Croatian Kuna', 'wp-schema-pro' ),
							'CUC' => __( 'Cuban Convertible Peso', 'wp-schema-pro' ),
							'CZK' => __( 'Czech Koruna', 'wp-schema-pro' ),
							'DKK' => __( 'Danish Krone', 'wp-schema-pro' ),
							'DJF' => __( 'Djiboutian Franc', 'wp-schema-pro' ),
							'DOP' => __( 'Dominican Peso', 'wp-schema-pro' ),
							'XCD' => __( 'East Caribbean Dollar', 'wp-schema-pro' ),
							'EGP' => __( 'Egyptian Pound', 'wp-schema-pro' ),
							'ERN' => __( 'Eritrean Nakfa', 'wp-schema-pro' ),
							'EEK' => __( 'Estonian Kroon', 'wp-schema-pro' ),
							'ETB' => __( 'Ethiopian Birr', 'wp-schema-pro' ),
							'EUR' => __( 'Euro', 'wp-schema-pro' ),
							'FKP' => __( 'Falkland Islands Pound', 'wp-schema-pro' ),
							'FJD' => __( 'Fijian Dollar', 'wp-schema-pro' ),
							'GMD' => __( 'Gambian Dalasi', 'wp-schema-pro' ),
							'GEL' => __( 'Georgian Lari', 'wp-schema-pro' ),
							'DEM' => __( 'German Mark', 'wp-schema-pro' ),
							'GHS' => __( 'Ghanaian Cedi', 'wp-schema-pro' ),
							'GIP' => __( 'Gibraltar Pound', 'wp-schema-pro' ),
							'GRD' => __( 'Greek Drachma', 'wp-schema-pro' ),
							'GTQ' => __( 'Guatemalan Quetzal', 'wp-schema-pro' ),
							'GNF' => __( 'Guinean Franc', 'wp-schema-pro' ),
							'GYD' => __( 'Guyanaese Dollar', 'wp-schema-pro' ),
							'HTG' => __( 'Haitian Gourde', 'wp-schema-pro' ),
							'HNL' => __( 'Honduran Lempira', 'wp-schema-pro' ),
							'HKD' => __( 'Hong Kong Dollar', 'wp-schema-pro' ),
							'HUF' => __( 'Hungarian Forint', 'wp-schema-pro' ),
							'ISK' => __( 'Icelandic Króna', 'wp-schema-pro' ),
							'INR' => __( 'Indian Rupee', 'wp-schema-pro' ),
							'IDR' => __( 'Indonesian Rupiah', 'wp-schema-pro' ),
							'IRR' => __( 'Iranian Rial', 'wp-schema-pro' ),
							'IQD' => __( 'Iraqi Dinar', 'wp-schema-pro' ),
							'ILS' => __( 'Israeli New Shekel', 'wp-schema-pro' ),
							'ITL' => __( 'Italian Lira', 'wp-schema-pro' ),
							'JMD' => __( 'Jamaican Dollar', 'wp-schema-pro' ),
							'JPY' => __( 'Japanese Yen', 'wp-schema-pro' ),
							'JOD' => __( 'Jordanian Dinar', 'wp-schema-pro' ),
							'KZT' => __( 'Kazakhstani Tenge', 'wp-schema-pro' ),
							'KES' => __( 'Kenyan Shilling', 'wp-schema-pro' ),
							'KWD' => __( 'Kuwaiti Dinar', 'wp-schema-pro' ),
							'KGS' => __( 'Kyrgystani Som', 'wp-schema-pro' ),
							'LAK' => __( 'Laotian Kip', 'wp-schema-pro' ),
							'LVL' => __( 'Latvian Lats', 'wp-schema-pro' ),
							'LBP' => __( 'Lebanese Pound', 'wp-schema-pro' ),
							'LSL' => __( 'Lesotho Loti', 'wp-schema-pro' ),
							'LRD' => __( 'Liberian Dollar', 'wp-schema-pro' ),
							'LYD' => __( 'Libyan Dinar', 'wp-schema-pro' ),
							'LTL' => __( 'Lithuanian Litas', 'wp-schema-pro' ),
							'MOP' => __( 'Macanese Pataca', 'wp-schema-pro' ),
							'MKD' => __( 'Macedonian Denar', 'wp-schema-pro' ),
							'MGA' => __( 'Malagasy Ariary', 'wp-schema-pro' ),
							'MWK' => __( 'Malawian Kwacha', 'wp-schema-pro' ),
							'MYR' => __( 'Malaysian Ringgit', 'wp-schema-pro' ),
							'MVR' => __( 'Maldivian Rufiyaa', 'wp-schema-pro' ),
							'MRO' => __( 'Mauritanian Ouguiya', 'wp-schema-pro' ),
							'MUR' => __( 'Mauritian Rupee', 'wp-schema-pro' ),
							'MXN' => __( 'Mexican Peso', 'wp-schema-pro' ),
							'MDL' => __( 'Moldovan Leu', 'wp-schema-pro' ),
							'MNT' => __( 'Mongolian Tugrik', 'wp-schema-pro' ),
							'MAD' => __( 'Moroccan Dirham', 'wp-schema-pro' ),
							'MZM' => __( 'Mozambican Metical', 'wp-schema-pro' ),
							'MMK' => __( 'Myanmar Kyat', 'wp-schema-pro' ),
							'NAD' => __( 'Namibian Dollar', 'wp-schema-pro' ),
							'NPR' => __( 'Nepalese Rupee', 'wp-schema-pro' ),
							'ANG' => __( 'Netherlands Antillean Guilder', 'wp-schema-pro' ),
							'TWD' => __( 'New Taiwan Dollar', 'wp-schema-pro' ),
							'NZD' => __( 'New Zealand Dollar', 'wp-schema-pro' ),
							'NIO' => __( 'Nicaraguan Córdoba', 'wp-schema-pro' ),
							'NGN' => __( 'Nigerian Naira', 'wp-schema-pro' ),
							'KPW' => __( 'North Korean Won', 'wp-schema-pro' ),
							'NOK' => __( 'Norwegian Krone', 'wp-schema-pro' ),
							'OMR' => __( 'Omani Rial', 'wp-schema-pro' ),
							'PKR' => __( 'Pakistani Rupee', 'wp-schema-pro' ),
							'PAB' => __( 'Panamanian Balboa', 'wp-schema-pro' ),
							'PGK' => __( 'Papua New Guinean Kina', 'wp-schema-pro' ),
							'PYG' => __( 'Paraguayan Guarani', 'wp-schema-pro' ),
							'PEN' => __( 'Peruvian Sol', 'wp-schema-pro' ),
							'PHP' => __( 'Philippine Peso', 'wp-schema-pro' ),
							'PLN' => __( 'Polish Zloty', 'wp-schema-pro' ),
							'QAR' => __( 'Qatari Rial', 'wp-schema-pro' ),
							'RON' => __( 'Romanian Leu', 'wp-schema-pro' ),
							'RUB' => __( 'Russian Ruble', 'wp-schema-pro' ),
							'RWF' => __( 'Rwandan Franc', 'wp-schema-pro' ),
							'SVC' => __( 'Salvadoran Colón', 'wp-schema-pro' ),
							'WST' => __( 'Samoan Tala', 'wp-schema-pro' ),
							'SAR' => __( 'Saudi Riyal', 'wp-schema-pro' ),
							'RSD' => __( 'Serbian Dinar', 'wp-schema-pro' ),
							'SCR' => __( 'Seychellois Rupee', 'wp-schema-pro' ),
							'SLL' => __( 'Sierra Leonean Leone', 'wp-schema-pro' ),
							'SGD' => __( 'Singapore Dollar', 'wp-schema-pro' ),
							'SKK' => __( 'Slovak Koruna', 'wp-schema-pro' ),
							'SBD' => __( 'Solomon Islands Dollar', 'wp-schema-pro' ),
							'SOS' => __( 'Somali Shilling', 'wp-schema-pro' ),
							'ZAR' => __( 'South African Rand', 'wp-schema-pro' ),
							'KRW' => __( 'South Korean Won', 'wp-schema-pro' ),
							'XDR' => __( 'Special Drawing Rights', 'wp-schema-pro' ),
							'LKR' => __( 'Sri Lankan Rupee', 'wp-schema-pro' ),
							'SHP' => __( 'St. Helena Pound', 'wp-schema-pro' ),
							'SDG' => __( 'Sudanese Pound', 'wp-schema-pro' ),
							'SRD' => __( 'Surinamese Dollar', 'wp-schema-pro' ),
							'SZL' => __( 'Swazi Lilangeni', 'wp-schema-pro' ),
							'SEK' => __( 'Swedish Krona', 'wp-schema-pro' ),
							'CHF' => __( 'Swiss Franc', 'wp-schema-pro' ),
							'SYP' => __( 'Syrian Pound', 'wp-schema-pro' ),
							'STD' => __( 'São Tomé & Príncipe Dobra', 'wp-schema-pro' ),
							'TJS' => __( 'Tajikistani Somoni', 'wp-schema-pro' ),
							'TZS' => __( 'Tanzanian Shilling', 'wp-schema-pro' ),
							'THB' => __( 'Thai Baht', 'wp-schema-pro' ),
							'TOP' => __( 'Tongan Pa\'anga', 'wp-schema-pro' ),
							'TTD' => __( 'Trinidad & Tobago Dollar', 'wp-schema-pro' ),
							'TND' => __( 'Tunisian Dinar', 'wp-schema-pro' ),
							'TRY' => __( 'Turkish Lira', 'wp-schema-pro' ),
							'TMT' => __( 'Turkmenistani Manat', 'wp-schema-pro' ),
							'UGX' => __( 'Ugandan Shilling', 'wp-schema-pro' ),
							'UAH' => __( 'Ukrainian Hryvnia', 'wp-schema-pro' ),
							'AED' => __( 'United Arab Emirates Dirham', 'wp-schema-pro' ),
							'UYU' => __( 'Uruguayan Peso', 'wp-schema-pro' ),
							'USD' => __( 'US Dollar', 'wp-schema-pro' ),
							'UZS' => __( 'Uzbekistani Som', 'wp-schema-pro' ),
							'VUV' => __( 'Vanuatu Vatu', 'wp-schema-pro' ),
							'VEF' => __( 'Venezuelan Bolívar', 'wp-schema-pro' ),
							'VND' => __( 'Vietnamese Dong', 'wp-schema-pro' ),
							'XOF' => __( 'West African CFA Franc', 'wp-schema-pro' ),
							'YER' => __( 'Yemeni Rial', 'wp-schema-pro' ),
							'ZMK' => __( 'Zambian Kwacha', 'wp-schema-pro' ),
						)
					);
					break;
				case 'software-category':
					$return = apply_filters(
						'wp_schema_pro_software_category_options',
						array(
							'BusinessApplication '        => __( 'Business App', 'wp-schema-pro' ),
							'GameApplication'             => __( 'Game App', 'wp-schema-pro' ),
							'MultimediaApplication'       => __( 'Multimedia App', 'wp-schema-pro' ),
							'MobileApplication'           => __( 'Mobile App', 'wp-schema-pro' ),
							'WebApplication'              => __( 'Web App', 'wp-schema-pro' ),
							'SocialNetworkingApplication' => __( 'Social Networking App', 'wp-schema-pro' ),
							'TravelApplication'           => __( 'Travel App', 'wp-schema-pro' ),
							'ShoppingApplication'         => __( 'Shopping App', 'wp-schema-pro' ),
							'SportsApplication'           => __( 'Sports App', 'wp-schema-pro' ),
							'LifestyleApplication'        => __( 'Lifestyle App', 'wp-schema-pro' ),
							'DesignApplication '          => __( 'Design App', 'wp-schema-pro' ),
							'DeveloperApplication'        => __( 'Developer App', 'wp-schema-pro' ),
							'DriverApplication'           => __( 'Driver App', 'wp-schema-pro' ),
							'EducationalApplication'      => __( 'Educational App', 'wp-schema-pro' ),
							'HealthApplication'           => __( 'Health App', 'wp-schema-pro' ),
							'FinanceApplication '         => __( 'Finance App', 'wp-schema-pro' ),
							'SecurityApplication'         => __( 'Security App', 'wp-schema-pro' ),
							'BrowserApplication'          => __( 'Browser App', 'wp-schema-pro' ),
							'CommunicationApplication'    => __( 'Communication App', 'wp-schema-pro' ),
							'DesktopEnhancementApplication' => __( 'Desktop Enhancement App', 'wp-schema-pro' ),
							'EntertainmentApplication '   => __( 'Business App', 'wp-schema-pro' ),
							'HomeApplication'             => __( 'Home App', 'wp-schema-pro' ),
							'UtilitiesApplication'        => __( 'Utilities App', 'wp-schema-pro' ),
							'ReferenceApplication'        => __( 'Reference App', 'wp-schema-pro' ),
						)
					);
					break;
				case 'time-unit':
					$return = apply_filters(
						'wp_schema_pro_time_unit_options',
						array(
							'HOUR'  => 'HOUR',
							'WEEK'  => 'WEEK',
							'MONTH' => 'MONTH',
							'YEAR'  => 'YEAR',
						)
					);
					break;
				case 'gender-select':
					$return = apply_filters(
						'wp_schema_pro_gender_options',
						array(
							'Male'   => 'Male',
							'Female' => 'Female',
							'Other'  => 'Other',
						)
					);
					break;
				default:
					$return = apply_filters( 'wp_schema_pro_dropdown_options', array() );
					break;
			}
			return $return;
		}

		/**
		 * Function to filter only Blank value.
		 *
		 * @since 1.1.3
		 * @param  mixed $var Variable.
		 * @return boolean
		 */
		public static function is_not_empty( $var ) {

			return ! empty( $var ) || '0' === $var;
		}

		/**
		 * Metabox Save
		 *
		 * @param  number $post_id Post ID.
		 * @return void
		 */
		public function save_meta_box( $post_id ) {

			// Checks save status.
			$is_autosave = wp_is_post_autosave( $post_id );
			$is_revision = wp_is_post_revision( $post_id );

			$is_valid_nonce = ( isset( $_POST['aiosrs-schema'] ) && wp_verify_nonce( $_POST['aiosrs-schema'], basename( __FILE__ ) ) ) ? true : false;

			// Exits script depending on save status.
			if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
				return;
			}

			/**
			 * Get meta options
			 */
			$post_meta = self::get_meta_option();
			foreach ( $post_meta as $key => $data ) {
				if ( in_array( $key, self::$schema_meta_keys, true ) ) {

					$_POST[ $key ] = array_filter( $_POST[ $key ], __CLASS__ . '::is_not_empty' );

					$meta_value = array();
					foreach ( $_POST[ $key ] as $meta_key => $value ) {
						$subkey_type = isset( self::$schema_meta_fields[ $key ]['subkeys'][ $meta_key ]['type'] ) ? self::$schema_meta_fields[ $key ]['subkeys'][ $meta_key ]['type'] : 'text';
						if ( 'repeater' === $subkey_type && is_array( $value ) ) {
							$i = 0;
							foreach ( $value as $repeater_value ) {
								$meta_value[ $meta_key ][ $i ] = array_map( 'esc_attr', $repeater_value );
								$i++;
							}
						} else {
							$meta_value[ $meta_key ] = esc_attr( $value );
						}
					}
				} elseif ( in_array( $key, array( 'bsf-aiosrs-schema-location', 'bsf-aiosrs-schema-exclusion' ), true ) ) {
					$meta_value = BSF_Target_Rule_Fields::get_format_rule_value( $_POST, $key );
				} else {
					// Sanitize values.
					$sanitize_filter = ( isset( $data['sanitize'] ) ) ? $data['sanitize'] : 'FILTER_DEFAULT';

					switch ( $sanitize_filter ) {

						case 'FILTER_SANITIZE_STRING':
							$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_STRING );
							break;

						case 'FILTER_SANITIZE_URL':
							$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_URL );
							break;

						case 'FILTER_SANITIZE_NUMBER_INT':
							$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_NUMBER_INT );
							break;

						default:
							$meta_value = filter_input( INPUT_POST, $key, FILTER_DEFAULT );
							break;
					}
				}

				// Store values.
				if ( $meta_value ) {
					update_post_meta( $post_id, $key, $meta_value );
				} else {
					delete_post_meta( $post_id, $key );
				}
			}
		}

		/**
		 * Add Custom Class to setting meta box
		 *
		 * @param array $classes Array of meta box classes.
		 * @return array $classes updated body classes.
		 */
		public function add_class_to_metabox( $classes ) {
			$classes[] = 'aiosrs-schema-meta-box-wrap';
			return $classes;
		}



		/**
		 * Redirect to aiosrs admin page.
		 *
		 * @return void
		 */
		public function redirect_custom_post_type() {
			if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
				return;
			}
			global $pagenow;
			/* Check current admin page. */

			if ( 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && 'aiosrs-schema' === $_GET['post_type'] ) {
				$url = BSF_AIOSRS_Pro_Admin::get_page_url( self::$wp_schema_action );
				wp_safe_redirect( $url, 301 );
				exit;
			}

		}

		/**
		 * Back to Schemas link
		 */
		public function back_to_schema() {
			global $post_type;
			if ( 'aiosrs-schema' !== $post_type ) {
				return;
			}
			$url = BSF_AIOSRS_Pro_Admin::get_page_url( self::$wp_schema_action );
			?>
			<div class="wrap">
				<h2>
					<a href= "<?php echo esc_url( $url ); ?>" class="page-title-action">
						<?php echo esc_html__( 'All Schemas', 'wp-schema-pro' ); ?>
					</a>
				</h2>
			</div>
			<?php
		}

		/**
		 * Add extension option in menu page
		 *
		 * @param  array $actions Array of actions.
		 * @return array            Return the actions.
		 */
		public function schema_menu_options( $actions ) {

			$actions[ self::$wp_schema_action ] = array(
				'label' => esc_html__( 'Schemas', 'wp-schema-pro' ),
				'show'  => ! is_network_admin(),
			);
			return $actions;
		}

		/**
		 * Render Schemas Setting page
		 */
		public function setting_page() {
			$list_table_instance = new BSF_Custom_Post_List_Table( 'aiosrs-schema' );
			$list_table_instance->render_markup();
		}

		/**
		 * Create AIOSRS Schemas custom post type
		 */
		public function schema_post_type() {
			$labels = array(
				'name'          => esc_html_x( 'Schemas', 'aiosrs-schemas general name', 'wp-schema-pro' ),
				'singular_name' => esc_html_x( 'Schema', 'aiosrs-schemas singular name', 'wp-schema-pro' ),
				'search_items'  => esc_html__( 'Search Schema', 'wp-schema-pro' ),
				'all_items'     => esc_html__( 'All Schemas', 'wp-schema-pro' ),
				'edit_item'     => esc_html__( 'Edit Schema', 'wp-schema-pro' ),
				'view_item'     => esc_html__( 'View Schema', 'wp-schema-pro' ),
				'add_new'       => esc_html__( 'Add New', 'wp-schema-pro' ),
				'update_item'   => esc_html__( 'Update Schema', 'wp-schema-pro' ),
				'add_new_item'  => esc_html__( 'Add New', 'wp-schema-pro' ),
				'new_item_name' => esc_html__( 'New Schema Name', 'wp-schema-pro' ),
			);
			$args   = array(
				'labels'       => $labels,
				'show_in_menu' => false,
				'public'       => false,
				'show_ui'      => true,
				'query_var'    => true,
				'can_export'   => true,
				'supports'     => apply_filters( 'wp_schema_pro_schema_supports', array( 'title' ) ),
			);

			register_post_type( 'aiosrs-schema', apply_filters( 'wp_schema_pro_schema_post_type_args', $args ) );
		}

		/**
		 * Add Update messages for any custom post type
		 *
		 * @param array $messages Array of default messages.
		 */
		public function custom_post_type_post_update_messages( $messages ) {
			if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
				return;
			}
			$custom_post_type = get_post_type( get_the_ID() );

			if ( 'aiosrs-schema' === $custom_post_type ) {

				$obj                           = get_post_type_object( $custom_post_type );
				$singular_name                 = $obj->labels->singular_name;
				$messages[ $custom_post_type ] = array(
					0  => '', // Unused. Messages start at index 1.
					/* translators: %s: singular custom post type name */
					1  => sprintf( __( '%s updated.', 'wp-schema-pro' ), $singular_name ),
					/* translators: %s: singular custom post type name */
					2  => sprintf( __( 'Custom %s updated.', 'wp-schema-pro' ), $singular_name ),
					/* translators: %s: singular custom post type name */
					3  => sprintf( __( 'Custom %s deleted.', 'wp-schema-pro' ), $singular_name ),
					/* translators: %s: singular custom post type name */
					4  => sprintf( __( '%s updated.', 'wp-schema-pro' ), $singular_name ),
					/* translators: %1$s: singular custom post type name ,%2$s: date and time of the revision */
					5  => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s', 'wp-schema-pro' ), $singular_name, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
					/* translators: %s: singular custom post type name */
					6  => sprintf( __( '%s published.', 'wp-schema-pro' ), $singular_name ),
					/* translators: %s: singular custom post type name */
					7  => sprintf( __( '%s saved.', 'wp-schema-pro' ), $singular_name ),
					/* translators: %s: singular custom post type name */
					8  => sprintf( __( '%s submitted.', 'wp-schema-pro' ), $singular_name ),
					/* translators: %s: singular custom post type name */
					9  => sprintf( __( '%s scheduled for.', 'wp-schema-pro' ), $singular_name ),
					/* translators: %s: singular custom post type name */
					10 => sprintf( __( '%s draft updated.', 'wp-schema-pro' ), $singular_name ),
				);
			}

			return $messages;
		}

	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
BSF_AIOSRS_Pro_Schema::get_instance();
