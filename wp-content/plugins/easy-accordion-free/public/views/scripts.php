<?php
/**
 * The Front Scripts class to manage all public-facing scripts of the plugin.
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.0
 *
 * @package    easy-accordion-free
 * @subpackage easy-accordion-free/public
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  // if direct access

/**
 * Scripts and styles
 */
class SP_EA_Front_Scripts {

	/**
	 * This class Instance.
	 *
	 * @var null
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * This class Instance function.
	 *
	 * @return SP_EA_Front_Scripts
	 * @since 1.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'register_all_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ) );
	}

	/**
	 * Plugin Scripts and Styles
	 */
	public function front_scripts() {
		// Get the existing shortcode ids.
		$get_page_data      = self::get_page_data();
		$found_generator_id = $get_page_data['generator_id'];
		// CSS Files.
		if ( $found_generator_id ) {
			wp_enqueue_style( 'sp-ea-fontello-icons' );
			wp_enqueue_style( 'sp-ea-style' );
			// Load dynamic style for the existing shordcodes.
			$ea_dynamic_css = self::load_dynamic_style( $found_generator_id );
			wp_add_inline_style( 'sp-ea-style', $ea_dynamic_css['dynamic_css'] );
		}
	}

	/**
	 * Register the all scripts for the public-facing side of the site.
	 *
	 * @since    2.0
	 */
	public function register_all_scripts() {
		$settings = get_option( 'sp_eap_settings' );
		$prefix   = defined( 'WP_DEBUG' ) && WP_DEBUG ? '' : '.min';

		// CSS Files.
		wp_register_style( 'sp-ea-fontello-icons', esc_url( SP_EA_URL . 'public/assets/css/fontello.css' ), array(), SP_EA_VERSION );
		wp_register_style( 'sp-ea-style', esc_url( SP_EA_URL . 'public/assets/css/ea-style.css' ), array(), SP_EA_VERSION );
		// Admin style of the plugin.
		wp_register_style( 'sp-ea-style-admin', esc_url( SP_EA_URL . 'admin/css/easy-accordion-free-admin.min.css' ), array(), SP_EA_VERSION, 'all' );

		// JS Files.
		wp_register_script( 'sp-ea-accordion-js', esc_url( SP_EA_URL . 'public/assets/js/collapse' . $prefix . '.js' ), array( 'jquery' ), SP_EA_VERSION, false );
		wp_register_script( 'sp-ea-accordion-config', esc_url( SP_EA_URL . 'public/assets/js/script.js' ), array( 'jquery', 'sp-ea-accordion-js' ), SP_EA_VERSION, true );

		$ea_custom_js = isset( $settings['custom_js'] ) ? trim( html_entity_decode( $settings['custom_js'] ) ) : '';
		if ( ! empty( $ea_custom_js ) ) {
			wp_add_inline_script( 'sp-ea-accordion-config', $ea_custom_js );
		}
	}

	/**
	 * Gets the existing shortcode-id, page-id and option-key from the current page.
	 *
	 * @return array
	 */
	public static function get_page_data() {
		$current_page_id    = get_queried_object_id();
		$option_key         = 'easy_accordion_page_id' . $current_page_id;
		$found_generator_id = get_option( $option_key );
		if ( is_multisite() ) {
			$option_key         = 'easy_accordion_page_id' . get_current_blog_id() . $current_page_id;
			$found_generator_id = get_site_option( $option_key );
		}
		$get_page_data = array(
			'page_id'      => $current_page_id,
			'generator_id' => $found_generator_id,
			'option_key'   => $option_key,
		);
		return $get_page_data;
	}
	/**
	 * Load dynamic style of the existing shortcode id.
	 *
	 * @param  mixed $found_generator_id to push id option for getting how many shortcode in the page.
	 * @param  mixed $shortcode_data to push all options.
	 * @return array dynamic style use in the specific shortcode.
	 */
	public static function load_dynamic_style( $found_generator_id, $shortcode_data = '' ) {
		$settings       = get_option( 'sp_eap_settings' );
		$ea_dynamic_css = '';
		// If multiple shortcode found in the page.
		if ( is_array( $found_generator_id ) ) {
			foreach ( $found_generator_id  as $accordion_id ) {
				if ( $accordion_id && is_numeric( $accordion_id ) && get_post_status( $accordion_id ) !== 'trash' ) {
					$shortcode_data = get_post_meta( $accordion_id, 'sp_eap_shortcode_options', true );
					include SP_EA_PATH . 'public/dynamic-style.php';
				}
			}
		} else {
			// If single shortcode found in the page.
			$accordion_id = $found_generator_id;
			include SP_EA_PATH . 'public/dynamic-style.php';
		}
		// Custom css merge with dynamic style.
		$custom_css = isset( $settings['ea_custom_css'] ) ? trim( html_entity_decode( $settings['ea_custom_css'] ) ) : '';
		if ( ! empty( $custom_css ) ) {
				$ea_dynamic_css .= $custom_css;
		}
		// Focus style to improve accessibility.
		$focus_style = isset( $settings['eap_focus_style'] ) ? $settings['eap_focus_style'] : false;
		if ( $focus_style ) {
			$ea_dynamic_css .= '.sp-easy-accordion .ea-header a:focus,
			.sp-horizontal-accordion .ea-header a:focus{
				box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
			}';
		}
		// Google font enqueue dequeue check.
		$dynamic_style = array(
			'dynamic_css' => $ea_dynamic_css,
		);
		return $dynamic_style;
	}

	/**
	 * If the option does not exist, it will be created.
	 *
	 * It will be serialized before it is inserted into the database.
	 *
	 * @param  string $post_id existing shortcode id.
	 * @param  array  $get_page_data get current page-id, shortcode-id and option-key from the page.
	 * @return void
	 */
	public static function easy_accordion_update_options( $post_id, $get_page_data ) {
		$found_generator_id = $get_page_data['generator_id'];
		$option_key         = $get_page_data['option_key'];
		$current_page_id    = $get_page_data['page_id'];
		if ( $found_generator_id ) {
			$found_generator_id = is_array( $found_generator_id ) ? $found_generator_id : array( $found_generator_id );
			if ( ! in_array( $post_id, $found_generator_id ) || empty( $found_generator_id ) ) {
				// If not found the shortcode id in the page options.
				array_push( $found_generator_id, $post_id );
				if ( is_multisite() ) {
					update_site_option( $option_key, $found_generator_id );
				} else {
					update_option( $option_key, $found_generator_id );
				}
			}
		} else {
			// If option not set in current page add option.
			if ( $current_page_id ) {
				if ( is_multisite() ) {
					add_site_option( $option_key, array( $post_id ) );
				} else {
					add_option( $option_key, array( $post_id ) );
				}
			}
		}
	}
}

new SP_EA_Front_Scripts();
