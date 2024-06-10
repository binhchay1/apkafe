<?php
/**
 * Schema Pro Admin.
 *
 * @package Schema Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BSF_SP_Admin' ) ) {

	/**
	 * Class BSF_SP_Admin.
	 */
	final class BSF_SP_Admin {

		/**
		 * Calls on initialization
		 *
		 * @since 0.0.1
		 */
		public static function init() {

			if ( ! is_admin() ) {
				return;
			}

			self::wpsp_initialize_ajax();

			add_action( 'wpsp_render_admin_content', __CLASS__ . '::render_content' );
			// Activation hook.
			add_action( 'admin_init', __CLASS__ . '::wpsp_activation_redirect' );
		}

		/**
		 * Activation Reset
		 */
		public static function wpsp_activation_redirect() {
			$do_redirect = apply_filters( 'wpsp_enable_redirect_activation', get_option( '__wpsp_do_redirect' ) );
			if ( $do_redirect ) {
				update_option( '__wpsp_do_redirect', false );
				if ( ! is_multisite() ) {
					wp_safe_redirect( esc_url( admin_url( 'options-general.php?page=' . SP_SLUG ) ) );
					exit();
				}
			}
		}

		/**
		 * Initialize Ajax
		 */
		public static function wpsp_initialize_ajax() {

			// if ( ! current_user_can( 'manage_options' ) ) {
			// return;
			// }
			// Ajax requests.
			add_action( 'wp_ajax_wpsp_activate_widget', __CLASS__ . '::wpsp_activate_widget' );
			add_action( 'wp_ajax_wpsp_deactivate_widget', __CLASS__ . '::wpsp_deactivate_widget' );

			add_action( 'wp_ajax_wpsp_bulk_activate_widgets', __CLASS__ . '::wpsp_bulk_activate_widgets' );
			add_action( 'wp_ajax_wpsp_bulk_deactivate_widgets', __CLASS__ . '::wpsp_bulk_deactivate_widgets' );
		}

		/**
		 * Activate module
		 */
		public static function wpsp_activate_widget() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}
			check_ajax_referer( 'wpsp-block-nonce', 'nonce' );

			$block_id            = isset( $_POST['block_id'] ) ? sanitize_text_field( $_POST['block_id'] ) : '';
			$blocks              = BSF_SP_Admin_Helper::get_admin_settings_option( '_wpsp_blocks', array() );
			$blocks[ $block_id ] = $block_id;
			$blocks              = array_map( 'esc_attr', $blocks );

			// Update blocks.
			BSF_SP_Admin_Helper::update_admin_settings_option( '_wpsp_blocks', $blocks );
			BSF_SP_Admin_Helper::create_specific_stylesheet();

			wp_send_json_success();
		}

		/**
		 * Deactivate module
		 */
		public static function wpsp_deactivate_widget() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}
			check_ajax_referer( 'wpsp-block-nonce', 'nonce' );

			$block_id            = isset( $_POST['block_id'] ) ? sanitize_text_field( $_POST['block_id'] ) : '';
			$blocks              = BSF_SP_Admin_Helper::get_admin_settings_option( '_wpsp_blocks', array() );
			$blocks[ $block_id ] = 'disabled';
			$blocks              = array_map( 'esc_attr', $blocks );

			// Update blocks.
			BSF_SP_Admin_Helper::update_admin_settings_option( '_wpsp_blocks', $blocks );
			BSF_SP_Admin_Helper::create_specific_stylesheet();

			wp_send_json_success();
		}

		/**
		 * Activate all module
		 */
		public static function wpsp_bulk_activate_widgets() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}
			check_ajax_referer( 'wpsp-block-nonce', 'nonce' );

			// Get all widgets.
			$all_blocks = BSF_SP_Helper::$block_list;
			$new_blocks = array();

			// Set all extension to enabled.
			foreach ( $all_blocks as $slug => $value ) {
				$_slug                = str_replace( 'wpsp/', '', $slug );
				$new_blocks[ $_slug ] = $_slug;
			}

			// Escape attrs.
			$new_blocks = array_map( 'esc_attr', $new_blocks );

			// Update new_extensions.
			BSF_SP_Admin_Helper::update_admin_settings_option( '_wpsp_blocks', $new_blocks );
			BSF_SP_Admin_Helper::create_specific_stylesheet();

			wp_send_json_success();
		}

		/**
		 * Deactivate all module
		 */
		public static function wpsp_bulk_deactivate_widgets() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}
			check_ajax_referer( 'wpsp-block-nonce', 'nonce' );

			// Get all extensions.
			$old_blocks = BSF_SP_Helper::$block_list;
			$new_blocks = array();

			// Set all extension to enabled.
			foreach ( $old_blocks as $slug => $value ) {
				$_slug                = str_replace( 'wpsp/', '', $slug );
				$new_blocks[ $_slug ] = 'disabled';
			}

			// Escape attrs.
			$new_blocks = array_map( 'esc_attr', $new_blocks );

			// Update new_extensions.
			BSF_SP_Admin_Helper::update_admin_settings_option( '_wpsp_blocks', $new_blocks );
			BSF_SP_Admin_Helper::create_specific_stylesheet();

			wp_send_json_success();
		}
	}

	BSF_SP_Admin::init();
}
