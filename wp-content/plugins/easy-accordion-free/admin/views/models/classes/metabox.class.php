<?php
/**
 * Framework metabox.class file.
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.0
 *
 * @package    easy-accordion-free
 * @subpackage easy-accordion-free/framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

if ( ! class_exists( 'SP_EAP_Metabox' ) ) {
	/**
	 *
	 * Metabox Class
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	class SP_EAP_Metabox extends SP_EAP_Abstract {


		/**
		 * $unique variable
		 *
		 * @var string
		 */
		public $unique = '';
		/**
		 * $abstract variable
		 *
		 * @var string
		 */
		public $abstract = 'metabox';
		/**
		 * $pre_fields variable
		 *
		 * @var string
		 */
		public $pre_fields = array();
		/**
		 * $sections variable
		 *
		 * @var array
		 */
		public $sections = array();
		/**
		 * $post_type variable
		 *
		 * @var string
		 */
		public $post_type = array();
		/**
		 * Post_formats
		 *
		 * @var array
		 */
		public $post_formats = array();
		/**
		 * Page_templates
		 *
		 * @var array
		 */
		public $page_templates = array();
		/**
		 * $args variable
		 *
		 * @var string
		 */
		public $args = array(
			'title'              => '',
			'post_type'          => 'post',
			'data_type'          => 'serialize',
			'context'            => 'advanced',
			'priority'           => 'default',
			'exclude_post_types' => array(),
			'page_templates'     => '',
			'post_formats'       => '',
			'show_restore'       => false,
			'enqueue_webfont'    => true,
			'async_webfont'      => false,
			'output_css'         => true,
			'theme'              => 'dark',
			'class'              => '',
			'defaults'           => array(),
		);

		/**
		 * Constructor of the class.
		 *
		 * @param string $key key.
		 * @param array  $params params.
		 */
		public function __construct( $key, $params = array() ) {

			$this->unique         = $key;
			$this->args           = apply_filters( "eapro_{$this->unique}_args", wp_parse_args( $params['args'], $this->args ), $this );
			$this->sections       = apply_filters( "eapro_{$this->unique}_sections", $params['sections'], $this );
			$this->post_type      = ( is_array( $this->args['post_type'] ) ) ? $this->args['post_type'] : array_filter( (array) $this->args['post_type'] );
			$this->post_formats   = ( is_array( $this->args['post_formats'] ) ) ? $this->args['post_formats'] : array_filter( (array) $this->args['post_formats'] );
			$this->page_templates = ( is_array( $this->args['page_templates'] ) ) ? $this->args['page_templates'] : array_filter( (array) $this->args['page_templates'] );
			$this->pre_fields     = $this->pre_fields( $this->sections );

			add_action( 'add_meta_boxes', array( &$this, 'add_meta_box' ) );
			add_action( 'save_post', array( &$this, 'save_meta_box' ) );
			add_action( 'edit_attachment', array( &$this, 'save_meta_box' ) );

			if ( ! empty( $this->page_templates ) || ! empty( $this->post_formats ) || ! empty( $this->args['class'] ) ) {
				foreach ( $this->post_type as $post_type ) {
						add_filter( 'postbox_classes_' . $post_type . '_' . $this->unique, array( &$this, 'add_metabox_classes' ) );
				}
			}

			/**
			 * Wp enqeueu for typography and output css.
			 */
			parent::__construct();

		}

		/**
		 * Instance.
		 *
		 * @param string $key key.
		 * @param array  $params params.
		 */
		public static function instance( $key, $params = array() ) {

			return new self( $key, $params );
		}

		/**
		 * Pre fields.
		 *
		 * @param array $sections sections.
		 */
		public function pre_fields( $sections ) {

			$result = array();

			foreach ( $sections as $key => $section ) {
				if ( ! empty( $section['fields'] ) ) {
					foreach ( $section['fields'] as $field ) {
						$result[] = $field;
					}
				}
			}

			return $result;
		}

		/**
		 * Add metabox classes.
		 *
		 * @param array $classes classes.
		 */
		public function add_metabox_classes( $classes ) {

			global $post;

			if ( ! empty( $this->post_formats ) ) {

				$saved_post_format = ( is_object( $post ) ) ? get_post_format( $post ) : false;
				$saved_post_format = ( ! empty( $saved_post_format ) ) ? $saved_post_format : 'default';

				$classes[] = 'eapro-post-formats';

				// Sanitize post format for standard to default.
				$key = array_search( 'standard', $this->post_formats, true );
				if ( ( $key ) !== false ) {
					$this->post_formats[ $key ] = 'default';
				}

				foreach ( $this->post_formats as $format ) {
					$classes[] = 'eapro-post-format-' . $format;
				}

				if ( ! in_array( $saved_post_format, $this->post_formats, true ) ) {
					$classes[] = 'eapro-hide';
				} else {
					$classes[] = 'eapro-show';
				}
			}

			if ( ! empty( $this->page_templates ) ) {

					$saved_template = ( is_object( $post ) && ! empty( $post->page_template ) ) ? $post->page_template : 'default';

				$classes[] = 'eapro-page-templates';

				foreach ( $this->page_templates as $template ) {
					$classes[] = 'eapro-page-' . preg_replace( '/[^a-zA-Z0-9]+/', '-', strtolower( $template ) );
				}

				if ( ! in_array( $saved_template, $this->page_templates, true ) ) {
					$classes[] = 'eapro-hide';
				} else {
					$classes[] = 'eapro-show';
				}
			}

			if ( ! empty( $this->args['class'] ) ) {
				$classes[] = $this->args['class'];
			}

			return $classes;

		}

		/**
		 * Add metabox.
		 *
		 * @param array $post_type Add meta box post type.
		 */
		public function add_meta_box( $post_type ) {

			if ( ! in_array( $post_type, $this->args['exclude_post_types'], true ) ) {
				add_meta_box( $this->unique, wp_kses_post( $this->args['title'] ), array( &$this, 'add_meta_box_content' ), $this->post_type, $this->args['context'], $this->args['priority'], $this->args );
			}

		}

		/**
		 * Get default value.
		 *
		 * @param array $field Default field.
		 */
		public function get_default( $field ) {

			$default = ( isset( $field['default'] ) ) ? $field['default'] : '';
			$default = ( isset( $this->args['defaults'][ $field['id'] ] ) ) ? $this->args['defaults'][ $field['id'] ] : $default;

			return $default;

		}

		/**
		 * Get meta value.
		 *
		 * @param array $field Meta value field.
		 */
		public function get_meta_value( $field ) {

			global $post;

			$value = null;

			if ( is_object( $post ) && ! empty( $field['id'] ) ) {

				if ( 'serialize' !== $this->args['data_type'] ) {
					$meta  = get_post_meta( $post->ID, $field['id'] );
					$value = ( isset( $meta[0] ) ) ? $meta[0] : null;
				} else {
					$meta  = get_post_meta( $post->ID, $this->unique, true );
					$value = ( isset( $meta[ $field['id'] ] ) ) ? $meta[ $field['id'] ] : null;
				}
			}

			$default = ( isset( $field['id'] ) ) ? $this->get_default( $field ) : '';
			$value   = ( isset( $value ) ) ? $value : $default;

			return $value;

		}

		/**
		 * Add metabox content.
		 *
		 * @param object $post post.
		 * @param string $callback callback function.
		 */
		public function add_meta_box_content( $post, $callback ) {

			global $post;

			$has_nav  = ( count( $this->sections ) > 1 && 'side' !== $this->args['context'] ) ? true : false;
			$show_all = ( ! $has_nav ) ? ' eapro-show-all' : '';
			$errors   = ( is_object( $post ) ) ? get_post_meta( $post->ID, '_eapro_errors_' . $this->unique, true ) : array();
			$errors   = ( ! empty( $errors ) ) ? $errors : array();
			$theme    = ( $this->args['theme'] ) ? ' eapro-theme-' . $this->args['theme'] : '';

			if ( is_object( $post ) && ! empty( $errors ) ) {
				delete_post_meta( $post->ID, '_eapro_errors_' . $this->unique );
			}

			wp_nonce_field( 'eapro_metabox_nonce', 'eapro_metabox_nonce' . $this->unique );

			echo '<div class="eapro eapro-metabox' . esc_attr( $theme ) . '">';

			echo '<div class="eapro-wrapper' . esc_attr( $show_all ) . '">';

			if ( $has_nav ) {

				echo '<div class="eapro-nav eapro-nav-metabox" data-unique="' . esc_attr( $this->unique ) . '">';

				echo '<ul>';
				$tab_key = 1;
				foreach ( $this->sections as $section ) {
					$tab_error = ( ! empty( $errors['sections'][ $tab_key ] ) ) ? '<i class="eapro-label-error eapro-error">!</i>' : '';
					$tab_icon  = ( ! empty( $section['icon'] ) ) ? '<i class="eapro-tab-icon ' . esc_attr( $section['icon'] ) . '"></i>' : '';

					echo '<li><a href="#" data-section="' . esc_attr( $this->unique . '_' . $tab_key ) . '">' . wp_kses_post( $tab_icon . $section['title'] . $tab_error ) . '</a></li>';

					$tab_key++;
				}
				echo '</ul>';

				echo '</div>';

			}

			echo '<div class="eapro-content">';

			echo '<div class="eapro-sections">';

			$count = 1;

			foreach ( $this->sections as $section ) {

				$onload = ( ! $has_nav ) ? ' eapro-onload' : '';

				echo '<div id="eapro-section-' . esc_attr( $this->unique . '_' . $count ) . '" class="eapro-section' . esc_attr( $onload ) . '">';

				$section_icon  = ( ! empty( $section['icon'] ) ) ? '<i class="eapro-section-icon ' . esc_attr( $section['icon'] ) . '"></i>' : '';
				$section_title = ( ! empty( $section['title'] ) ) ? $section['title'] : '';

				echo ( $section_title || $section_icon ) ? '<div class="eapro-section-title"><h3>' . wp_kses_post( $section_icon . $section_title ) . '</h3></div>' : '';

				if ( ! empty( $section['fields'] ) ) {

					foreach ( $section['fields'] as $field ) {

						if ( ! empty( $field['id'] ) && ! empty( $errors['fields'][ $field['id'] ] ) ) {
							$field['_error'] = $errors['fields'][ $field['id'] ];
						}

						if ( ! empty( $field['id'] ) ) {
							$field['default'] = $this->get_default( $field );
						}

						SP_EAP::field( $field, $this->get_meta_value( $field ), $this->unique, 'metabox' );

					}
				} else {

					echo '<div class="eapro-no-option eapro-text-muted">' . esc_html__( 'No option provided by developer.', 'easy-accordion-free' ) . '</div>';

				}

				echo '</div>';

				$count++;

			}

			echo '</div>';

			echo '<a class="btn btn-success" id="sp__eap-show-preview" data-id="' . esc_attr( $post->ID ) . '"href=""> <i class="fa fa-eye" aria-hidden="true"></i> Show Preview</a>';

			echo '<div class="clear"></div>';

			if ( ! empty( $this->args['show_restore'] ) ) {

				echo '<div class="eapro-restore-wrapper">';
				echo '<label>';
				echo '<input type="checkbox" name="' . esc_attr( $this->unique ) . '[_restore]" />';
				echo '<span class="button eapro-button-restore">' . esc_html__( 'Restore', 'easy-accordion-free' ) . '</span>';
				echo '<span class="button eapro-button-cancel">' . sprintf( '<small>( %s )</small> %s', esc_html__( 'update post for restore ', 'easy-accordion-free' ), esc_html__( 'Cancel', 'easy-accordion-free' ) ) . '</span>';
				echo '</label>';
				echo '</div>';

			}

			echo '</div>';

			echo ( $has_nav ) ? '<div class="eapro-nav-background"></div>' : '';

			echo '<div class="clear"></div>';

			echo '</div>';

			echo '</div>';

		}

		/**
		 * Save metabox.
		 *
		 * @param int $post_id post_id.
		 */
		public function save_meta_box( $post_id ) {

			$count    = 1;
			$data     = array();
			$errors   = array();
			$noncekey = 'eapro_metabox_nonce' . $this->unique;
			$nonce    = ( ! empty( $_POST[ $noncekey ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ $noncekey ] ) ) : '';

			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! wp_verify_nonce( $nonce, 'eapro_metabox_nonce' ) ) {
				return $post_id;
			}

			// XSS ok.
			// No worries, This "POST" requests is sanitizing in the below foreach.
			$request = ( ! empty( $_POST[ $this->unique ] ) ) ? $_POST[ $this->unique ] : array(); // phpcs:ignore

			if ( ! empty( $request ) ) {

				foreach ( $this->sections as $section ) {

					if ( ! empty( $section['fields'] ) ) {

						foreach ( $section['fields'] as $field ) {

							if ( ! empty( $field['id'] ) && ! isset( $field['only_pro'] ) ) {

								$field_id    = sanitize_key( $field['id'] );
								$field_value = isset( $request[ $field_id ] ) ? $request[ $field_id ] : '';
								// Sanitize "post" request of field.
								if ( ! empty( $field['sanitize'] ) ) {
									$data[ $field_id ] = call_user_func( $field['sanitize'], $field_value );
								} else {
									if ( $field_value && is_array( $field_value ) ) {
										$data[ $field_id ] = wp_kses_post_deep( $field_value );
									} else {
										$data[ $field_id ] = $field_value ? wp_kses_post( $field_value ) : $field_value;
									}
								}

								// Validate "post" request of field.
								if ( isset( $field['validate'] ) && function_exists( $field['validate'] ) ) {

									$has_validated = call_user_func( $field['validate'], $field_value );

									if ( ! empty( $has_validated ) ) {

										$errors['sections'][ $count ]  = true;
										$errors['fields'][ $field_id ] = $has_validated;
										$data[ $field_id ]             = $this->get_meta_value( $field );

									}
								}
							}
						}
					}
					$count++;
				}
			}

			$data = apply_filters( "eapro_{$this->unique}_save", $data, $post_id, $this );

			do_action( "eapro_{$this->unique}_save_before", $data, $post_id, $this );

			if ( empty( $data ) || ! empty( $request['_restore'] ) ) {

				if ( 'serialize' !== $this->args['data_type'] ) {
					foreach ( $data as $key => $value ) {
						delete_post_meta( $post_id, $key );
					}
				} else {
					delete_post_meta( $post_id, $this->unique );
				}
			} else {

				if ( 'serialize' !== $this->args['data_type'] ) {
					foreach ( $data as $key => $value ) {
						update_post_meta( $post_id, $key, $value );
					}
				} else {
					update_post_meta( $post_id, $this->unique, $data );
				}

				if ( ! empty( $errors ) ) {
					update_post_meta( $post_id, '_eapro_errors_' . $this->unique, $errors );
				}
			}

			do_action( "eapro_{$this->unique}_saved", $data, $post_id, $this );

			do_action( "eapro_{$this->unique}_save_after", $data, $post_id, $this );
		}
	}
}
