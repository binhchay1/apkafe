<?php
/**
 * Framework setup.class file.
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.0
 *
 * @package    easy-accordion-free
 * @subpackage easy-accordion-free/framework
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access directly.

if ( ! class_exists( 'SP_EAP' ) ) {
	/**
	 *
	 * Setup Class
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	class SP_EAP {

		/**
		 * Version.
		 *
		 * @var string
		 */
		public static $version = '2.1.5';
		/**
		 * Premium.
		 *
		 * @var string
		 */
		public static $premium = true;
		/**
		 * Dir.
		 *
		 * @var string
		 */
		public static $dir = null;
		/**
		 * Url.
		 *
		 * @var string
		 */
		public static $url = null;
		/**
		 * Init.
		 *
		 * @var array
		 */
		public static $inited = array();
		/**
		 * Field.
		 *
		 * @var array
		 */
		public static $fields = array();
		/**
		 * Args.
		 *
		 * @var array
		 */
		public static $args = array(
			'options'   => array(),
			'metaboxes' => array(),
		);

		/**
		 * Shortcode instances.
		 *
		 * @var array
		 */
		public static $shortcode_instances = array();

		/**
		 * Init
		 *
		 * @return void
		 */
		public static function init() {

			// init action.
			do_action( 'eapro_init' );

			// set constants.
			self::constants();

			// translate.
			self::set_locale();

			// include files.
			self::includes();

			add_action( 'after_setup_theme', array( 'SP_EAP', 'setup' ) );
			add_action( 'init', array( 'SP_EAP', 'setup' ) );
			add_action( 'switch_theme', array( 'SP_EAP', 'setup' ) );
			add_action( 'admin_enqueue_scripts', array( 'SP_EAP', 'add_admin_enqueue_scripts' ), 20 );
			add_action( 'admin_head', array( 'SP_EAP', 'add_admin_head_css' ), 99 );

		}

		/**
		 * Setup
		 *
		 * @return void
		 */
		public static function setup() {

			// setup options.
			$params = array();
			if ( ! empty( self::$args['options'] ) ) {
				foreach ( self::$args['options'] as $key => $value ) {
					if ( ! empty( self::$args['sections'][ $key ] ) && ! isset( self::$inited[ $key ] ) ) {

						$params['args']       = $value;
						$params['sections']   = self::$args['sections'][ $key ];
						self::$inited[ $key ] = true;

						SP_EAP_Options::instance( $key, $params );

						if ( ! empty( $value['show_in_customizer'] ) ) {
							$value['output_css']                     = false;
							$value['enqueue_webfont']                = false;
							self::$args['customize_options'][ $key ] = $value;
							self::$inited[ $key ]                    = null;
						}
					}
				}
			}

			// setup metaboxes.
			$params = array();
			if ( ! empty( self::$args['metaboxes'] ) ) {
				foreach ( self::$args['metaboxes'] as $key => $value ) {
					if ( ! empty( self::$args['sections'][ $key ] ) && ! isset( self::$inited[ $key ] ) ) {

						$params['args']       = $value;
						$params['sections']   = self::$args['sections'][ $key ];
						self::$inited[ $key ] = true;

						SP_EAP_Metabox::instance( $key, $params );

					}
				}
			}

			do_action( 'eapro_loaded' );

		}

		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the Easy_Accordion_Free_I18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    2.0.0
		 * @access   private
		 */
		public static function set_locale() {
			require_once SP_EA_INCLUDES . '/class-easy-accordion-free-i18n.php';
			$plugin_i18n = new Easy_Accordion_Free_I18n();
			$plugin_i18n->load_plugin_textdomain();
		}

		/**
		 * Create options.
		 *
		 * @param string $id The option ID.
		 * @param array  $args The arguments array.
		 * @return void
		 */
		// phpcs:ignore
		public static function createOptions( $id, $args = array() ) {
			self::$args['options'][ $id ] = $args;
		}

		/**
		 * Create metabox options.
		 *
		 * @param string $id The option ID.
		 * @param array  $args The arguments array.
		 * @return void
		 */
		// phpcs:ignore
		public static function createMetabox( $id, $args = array() ) {
			self::$args['metaboxes'][ $id ] = $args;
		}

		/**
		 * Create sections.
		 *
		 * @param string $id The option ID.
		 * @param array  $sections The sections array.
		 * @return void
		 */
		// phpcs:ignore
		public static function createSection( $id, $sections ) {
			self::$args['sections'][ $id ][] = $sections;
			self::set_used_fields( $sections );
		}

		/**
		 * Constants.
		 *
		 * @return void
		 */
		public static function constants() {

			// we need this path-finder code for set URL of framework.
			$dirname        = wp_normalize_path( dirname( dirname( __FILE__ ) ) );
			$theme_dir      = wp_normalize_path( get_parent_theme_file_path() );
			$plugin_dir     = wp_normalize_path( WP_PLUGIN_DIR );
			$located_plugin = ( preg_match( '#' . self::sanitize_dirname( $plugin_dir ) . '#', self::sanitize_dirname( $dirname ) ) ) ? true : false;
			$directory      = ( $located_plugin ) ? $plugin_dir : $theme_dir;
			$directory_uri  = ( $located_plugin ) ? WP_PLUGIN_URL : get_parent_theme_file_uri();
			$foldername     = str_replace( $directory, '', $dirname );
			$protocol_uri   = ( is_ssl() ) ? 'https' : 'http';
			$directory_uri  = set_url_scheme( $directory_uri, $protocol_uri );

			self::$dir = $dirname;
			self::$url = $directory_uri . $foldername;

		}

		/**
		 * Include plugin files.
		 *
		 * @param string  $file Plugin files.
		 * @param boolean $load Load files.
		 *
		 * @return mixed
		 */
		public static function include_plugin_file( $file, $load = true ) {

			$path     = '';
			$file     = ltrim( $file, '/' );
			$override = apply_filters( 'eapro_override', 'eapro-override' );

			if ( file_exists( get_parent_theme_file_path( $override . '/' . $file ) ) ) {
				$path = get_parent_theme_file_path( $override . '/' . $file );
			} elseif ( file_exists( get_theme_file_path( $override . '/' . $file ) ) ) {
				$path = get_theme_file_path( $override . '/' . $file );
			} elseif ( file_exists( self::$dir . '/' . $override . '/' . $file ) ) {
				$path = self::$dir . '/' . $override . '/' . $file;
			} elseif ( file_exists( self::$dir . '/' . $file ) ) {
				$path = self::$dir . '/' . $file;
			}

			if ( ! empty( $path ) && ! empty( $file ) && $load ) {

				global $wp_query;

				if ( is_object( $wp_query ) && function_exists( 'load_template' ) ) {

					load_template( $path, true );

				} else {

					require_once $path;

				}
			} else {

				return self::$dir . '/' . $file;

			}

		}

		/**
		 * Is plugin active.
		 *
		 * @param string $file The plugin file.
		 * @return boolean
		 */
		public static function is_active_plugin( $file = '' ) {
			return in_array( $file, (array) get_option( 'active_plugins', array() ), true );
		}

		/**
		 * Sanitize dirname.
		 *
		 * @param string $dirname The directory name.
		 * @return string
		 */
		public static function sanitize_dirname( $dirname ) {
			return preg_replace( '/[^A-Za-z]/', '', $dirname );
		}

		/**
		 * Set plugin url.
		 *
		 * @param string $file Plugin url.
		 */
		public static function include_plugin_url( $file ) {
			return esc_url( SP_EA_URL . '/admin/views/models' ) . '/' . ltrim( $file, '/' );
		}

		/**
		 * General includes.
		 *
		 * @return void
		 */
		public static function includes() {

			// includes helpers.
			self::include_plugin_file( 'functions/actions.php' );
			self::include_plugin_file( 'functions/helpers.php' );
			self::include_plugin_file( 'functions/sanitize.php' );
			self::include_plugin_file( 'functions/validate.php' );

			// includes classes.
			self::include_plugin_file( 'classes/abstract.class.php' );
			self::include_plugin_file( 'classes/fields.class.php' );
			self::include_plugin_file( 'classes/options.class.php' );
			self::include_plugin_file( 'classes/metabox.class.php' );

		}

		/**
		 * Include field.
		 *
		 * @param string $type File type.
		 * @return void
		 */
		public static function maybe_include_field( $type = '' ) {
			if ( ! class_exists( 'SP_EAP_Field_' . $type ) && class_exists( 'SP_EAP_Fields' ) ) {
				self::include_plugin_file( 'fields/' . $type . '/' . $type . '.php' );
			}
		}

		/**
		 * Get all of fields.
		 *
		 * @param array $sections All the sections used in the plugin.
		 * @return void
		 */
		public static function set_used_fields( $sections ) {

			if ( ! empty( $sections['fields'] ) ) {

				foreach ( $sections['fields'] as $field ) {

					if ( ! empty( $field['fields'] ) ) {
						self::set_used_fields( $field );
					}

					if ( ! empty( $field['tabs'] ) ) {
						self::set_used_fields( array( 'fields' => $field['tabs'] ) );
					}

					if ( ! empty( $field['accordions'] ) ) {
						self::set_used_fields( array( 'fields' => $field['accordions'] ) );
					}

					if ( ! empty( $field['type'] ) ) {
						self::$fields[ $field['type'] ] = $field;
					}
				}
			}

		}

		/**
		 * Enqueue admin and fields styles and scripts.
		 *
		 * @param string $hook Hooks.
		 * @return void
		 */
		public static function add_admin_enqueue_scripts( $hook ) {

			$current_screen        = get_current_screen();
			$the_current_post_type = $current_screen->post_type;
			if ( 'sp_easy_accordion' === $the_current_post_type ) {

				// check for developer mode.
				$min = ( apply_filters( 'eapro_dev_mode', false ) || WP_DEBUG ) ? '' : '.min';

				// admin utilities.
				wp_enqueue_media();

				// wp color picker.
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );

				// font awesome 4 and 5.
				if ( apply_filters( 'eapro_fa4', false ) ) {
					wp_enqueue_style( 'eapro-fa', SP_EA_URL . 'public/assets/css/font-awesome' . $min . '.css', array(), SP_EA_VERSION, 'all' );
				} else {
					wp_enqueue_style( 'eapro-fa5', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.13.0/css/all' . $min . '.css', array(), SP_EA_VERSION, 'all' );
					wp_enqueue_style( 'eapro-fa5-v4-shims', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.13.0/css/v4-shims' . $min . '.css', array(), SP_EA_VERSION, 'all' );
				}

				// framework core styles.
				wp_enqueue_style( 'eapro', self::include_plugin_url( 'assets/css/eapro' . $min . '.css' ), array(), SP_EA_VERSION, 'all' );

				// rtl styles.
				if ( is_rtl() ) {
					wp_enqueue_style( 'eapro-rtl', self::include_plugin_url( 'assets/css/eapro-rtl' . $min . '.css' ), array(), SP_EA_VERSION, 'all' );
				}

				// framework core scripts.
				wp_enqueue_script( 'eapro-plugins', self::include_plugin_url( 'assets/js/eapro-plugins' . $min . '.js' ), array(), SP_EA_VERSION, true );
				wp_enqueue_script( 'eapro', self::include_plugin_url( 'assets/js/eapro' . $min . '.js' ), array( 'eapro-plugins' ), SP_EA_VERSION, true );

				wp_localize_script(
					'eapro',
					'eapro_vars',
					array(
						'pluginsUrl'    => SP_EA_URL,
						'color_palette' => apply_filters( 'eapro_color_palette', array() ),
						'i18n'          => array(
							// global localize.
							'confirm'             => esc_html__( 'Are you sure?', 'easy-accordion-free' ),
							'reset_notification'  => esc_html__( 'Restoring options.', 'easy-accordion-free' ),
							'import_notification' => esc_html__( 'Importing options.', 'easy-accordion-free' ),

							// chosen localize.
							'typing_text'         => esc_html__( 'Please enter %s or more characters', 'easy-accordion-free' ),
							'searching_text'      => esc_html__( 'Searching...', 'easy-accordion-free' ),
							'no_results_text'     => esc_html__( 'No results match', 'easy-accordion-free' ),
						),
					)
				);

				// load admin enqueue scripts and styles.
				$enqueued = array();

				if ( ! empty( self::$fields ) ) {
					foreach ( self::$fields as $field ) {
						if ( ! empty( $field['type'] ) ) {
							$classname = 'SP_EAP_Field_' . $field['type'];
							self::maybe_include_field( $field['type'] );
							if ( class_exists( $classname ) && method_exists( $classname, 'enqueue' ) ) {
								$instance = new $classname( $field );
								if ( method_exists( $classname, 'enqueue' ) ) {
									$instance->enqueue();
								}
								unset( $instance );
							}
						}
					}
				}

				do_action( 'eapro_enqueue' );
			}

		}

		/**
		 * WP 5.2 fallback.
		 * This function has been created as temporary.
		 * It will be remove after stable version of wp 5.3.
		 *
		 * @return void
		 */
		public static function add_admin_head_css() {

			global $wp_version;

			$current_branch = implode( '.', array_slice( preg_split( '/[.-]/', $wp_version ), 0, 2 ) );

			if ( version_compare( $current_branch, '5.3', '<' ) ) {

				echo '<style type="text/css">
          .eapro-field-slider .eapro--unit,
          .eapro-field-border .eapro--label,
          .eapro-field-spacing .eapro--label,
          .eapro-field-dimensions .eapro--label,
          .eapro-field-spinner .ui-button-text-only{
            border-color: #ddd;
          }
          .eapro-warning-primary{
            box-shadow: 0 1px 0 #bd2130 !important;
          }
          .eapro-warning-primary:focus{
            box-shadow: none !important;
          }
        </style>';

			}

		}

		/**
		 * Add a new framework field.
		 *
		 * @param array  $field The fields array.
		 * @param string $value The field value.
		 * @param string $unique The unique string.
		 * @param string $where The position to show the fields.
		 * @param string $parent If the fields has parent.
		 * @return void
		 */
		public static function field( $field = array(), $value = '', $unique = '', $where = '', $parent = '' ) {

			// Check for unallow fields.
			if ( ! empty( $field['_notice'] ) ) {

				$field_type = $field['type'];

				$field            = array();
				$field['content'] = sprintf( esc_html__( 'Ooops! This field type (%s) can not be used here, yet.', 'easy-accordion-free' ), '<strong>' . $field_type . '</strong>' );
				$field['type']    = 'notice';
				$field['style']   = 'danger';

			}

			$depend     = '';
			$hidden     = '';
			$unique     = ( ! empty( $unique ) ) ? $unique : '';
			$class      = ( ! empty( $field['class'] ) ) ? ' ' . esc_attr( $field['class'] ) : '';
			$is_pseudo  = ( ! empty( $field['pseudo'] ) ) ? ' eapro-pseudo-field' : '';
			$field_type = ( ! empty( $field['type'] ) ) ? esc_attr( $field['type'] ) : '';

			if ( ! empty( $field['dependency'] ) ) {

				$dependency      = $field['dependency'];
				$hidden          = ' hidden';
				$data_controller = '';
				$data_condition  = '';
				$data_value      = '';
				$data_global     = '';

				if ( is_array( $dependency[0] ) ) {
					$data_controller = implode( '|', array_column( $dependency, 0 ) );
					$data_condition  = implode( '|', array_column( $dependency, 1 ) );
					$data_value      = implode( '|', array_column( $dependency, 2 ) );
					$data_global     = implode( '|', array_column( $dependency, 3 ) );
				} else {
					$data_controller = ( ! empty( $dependency[0] ) ) ? $dependency[0] : '';
					$data_condition  = ( ! empty( $dependency[1] ) ) ? $dependency[1] : '';
					$data_value      = ( ! empty( $dependency[2] ) ) ? $dependency[2] : '';
					$data_global     = ( ! empty( $dependency[3] ) ) ? $dependency[3] : '';
				}

				$depend .= ' data-controller="' . esc_attr( $data_controller ) . '"';
				$depend .= ' data-condition="' . esc_attr( $data_condition ) . '"';
				$depend .= ' data-value="' . esc_attr( $data_value ) . '"';
				$depend .= ( ! empty( $data_global ) ) ? ' data-depend-global="true"' : '';

			}

			if ( ! empty( $field_type ) ) {

				// These attributes has been sanitized above.
				echo '<div class="eapro-field eapro-field-' . esc_attr( $field_type ) . esc_attr( $is_pseudo ) . esc_attr( $class ) . esc_attr( $hidden ) . '"' . wp_kses_post( $depend ) . '>';

				if ( ! empty( $field['fancy_title'] ) ) {
					echo '<div class="eapro-fancy-title">' . wp_kses_post( $field['fancy_title'] ) . '</div>';
				}

				if ( ! empty( $field['title'] ) ) {
					$title_info = ( ! empty( $field['title_info'] ) ) ? '<span class="eapro-help title-info"><div class="eapro-help-text">' . wp_kses_post( $field['title_info'] ) . '</div><span class="tooltip-icon"><img src="' . self::include_plugin_url( 'assets/images/info.svg' ) . '"></span></span>' : '';
					echo '<div class="eapro-title">';
					echo '<h4>' . wp_kses_post( $field['title'] . $title_info ) . '</h4>';
					echo ( ! empty( $field['subtitle'] ) ) ? '<div class="eapro-text-subtitle">' . wp_kses_post( $field['subtitle'] ) . '</div>' : '';
					echo '</div>';
				}

				echo ( ! empty( $field['title'] ) || ! empty( $field['fancy_title'] ) ) ? '<div class="eapro-fieldset">' : '';

				$value = ( ! isset( $value ) && isset( $field['default'] ) ) ? $field['default'] : $value;
				$value = ( isset( $field['value'] ) ) ? $field['value'] : $value;

				self::maybe_include_field( $field_type );

				$classname = 'SP_EAP_Field_' . $field_type;

				if ( class_exists( $classname ) ) {
					$instance = new $classname( $field, $value, $unique, $where, $parent );
					$instance->render();
				} else {
					echo '<p>' . esc_html__( 'This field class is not available!', 'easy-accordion-free' ) . '</p>';
				}
			} else {
				echo '<p>' . esc_html__( 'This type is not found!', 'easy-accordion-free' ) . '</p>';
			}

			echo ( ! empty( $field['title'] ) || ! empty( $field['fancy_title'] ) ) ? '</div>' : '';
			echo '<div class="clear"></div>';
			echo '</div>';

		}

	}

	SP_EAP::init();
}
