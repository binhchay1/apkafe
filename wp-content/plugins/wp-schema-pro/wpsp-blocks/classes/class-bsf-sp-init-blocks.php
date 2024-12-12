<?php
/**
 * Schema Pro Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   2.2.0
 * @package Schema Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * BSF_SP_Init_Blocks.
 *
 * @package Schema Pro
 */
class BSF_SP_Init_Blocks {

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
	 * Constructor
	 */
	public function __construct() {
		// Hook: Frontend assets.
		add_action( 'enqueue_block_assets', array( $this, 'block_assets' ) );

		// Hook: Editor assets.
		add_action( 'enqueue_block_editor_assets', array( $this, 'editor_assets' ), 15 );

		add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );
	}

	/**
	 * Gutenberg block category for Schema Pro.
	 *
	 * @param array $categories Block categories.
	 * @since 2.2.0
	 */
	public function register_block_category( $categories ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'wpsp',
					'title' => __( 'Schema Pro', 'wp-schema-pro' ),
				),
			)
		);
	}

	/**
	 * Enqueue Gutenberg block assets for both frontend + backend.
	 *
	 * @since 2.2.0
	 */
	public function block_assets() {
		$post = get_post();

		/**
		 * Filters the post to build stylesheet for.
		 *
		 * @param \WP_Post $post The global post.
		 */
		$post = apply_filters( 'wpsp_post_for_stylesheet', $post );

		if ( false === has_blocks( $post ) ) {
			return;
		}

		if ( false === BSF_SP_Helper::$wpsp_flag ) {
			return;
		}

		wp_enqueue_style(
			'wpsp-block-css', // Handle.
			BSF_AIOSRS_PRO_URI . 'dist/style-blocks.css', // Block style CSS.
			array(),
			BSF_AIOSRS_PRO_VER
		);
		$blocks       = BSF_SP_Config::get_block_attributes();
		$block_assets = BSF_SP_Config::get_block_assets();

		foreach ( $blocks as $slug => $value ) {
			$js_assets  = ( isset( $blocks[ $slug ]['js_assets'] ) ) ? $blocks[ $slug ]['js_assets'] : array();
			$css_assets = ( isset( $blocks[ $slug ]['css_assets'] ) ) ? $blocks[ $slug ]['css_assets'] : array();

			foreach ( $js_assets as $asset_handle => $val ) {
				// Scripts.
				wp_register_script(
					$val, // Handle.
					$block_assets[ $val ]['src'],
					$block_assets[ $val ]['dep'],
					BSF_AIOSRS_PRO_VER,
					true
				);

				$skip_editor = isset( $block_assets[ $val ]['skipEditor'] ) ? $block_assets[ $val ]['skipEditor'] : false;

				if ( is_admin() && false === $skip_editor ) {
					wp_enqueue_script( $val );
				}
			}

			foreach ( $css_assets as $asset_handle => $val ) {
				// Styles.
				wp_register_style(
					$val, // Handle.
					$block_assets[ $val ]['src'],
					$block_assets[ $val ]['dep'],
					BSF_AIOSRS_PRO_VER
				);

				if ( is_admin() ) {
					wp_enqueue_style( $val );
				}
			}
		}
	} // End function editor_assets().

	/**
	 * Enqueue Gutenberg block assets for backend editor.
	 *
	 * @since 2.2.0
	 */
	public function editor_assets() {
		$wpsp_ajax_nonce = wp_create_nonce( 'wpsp_ajax_nonce' );
		$script_dep_path = BSF_AIOSRS_PRO_DIR . 'dist/blocks.asset.php';
		$script_info     = file_exists( $script_dep_path )
			? include $script_dep_path
			: array(
				'dependencies' => array(),
				'version'      => BSF_AIOSRS_PRO_VER,
			);
		$script_dep      = array_merge( $script_info['dependencies'], array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor', 'wp-api-fetch' ) );

		// Scripts.
		wp_enqueue_script(
			'wpsp-block-editor-js', // Handle.
			BSF_AIOSRS_PRO_URI . 'dist/blocks.js',
			$script_dep, // Dependencies, defined above.
			$script_info['version'], // BSF_AIOSRS_PRO_VER.
			true // Enqueue the script in the footer.
		);

		// Common Editor style.
		wp_enqueue_style(
			'wpsp-block-common-editor-css', // Handle.
			BSF_AIOSRS_PRO_URI . 'wpsp-blocks/assets/css/blocks.commoneditorstyle.build.css', // Block editor CSS.
			array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
			BSF_AIOSRS_PRO_VER
		);

		$blocks       = array();
		$saved_blocks = BSF_SP_Admin_Helper::get_admin_settings_option( '_wpsp_blocks' );

		if ( is_array( $saved_blocks ) ) {
			foreach ( $saved_blocks as $slug => $data ) {
				$_slug         = 'wpsp/' . $slug;
				$current_block = BSF_SP_Config::$block_attributes[ $_slug ];

				if ( isset( $current_block['is_child'] ) && $current_block['is_child'] ) {
					continue;
				}

				if ( isset( $current_block['is_active'] ) && ! $current_block['is_active'] ) {
					continue;
				}

				if ( isset( $saved_blocks[ $slug ] ) && ( 'disabled' === $saved_blocks[ $slug ] ) ) {
					array_push( $blocks, $_slug );
				}
			}
		}
		wp_localize_script(
			'wpsp-block-editor-js',
			'wpsp_blocks_info',
			array(
				'blocks'            => BSF_SP_Config::get_block_attributes(),
				'category'          => 'wpsp',
				'ajax_url'          => admin_url( 'admin-ajax.php' ),
				'tablet_breakpoint' => WPSP_TABLET_BREAKPOINT,
				'mobile_breakpoint' => WPSP_MOBILE_BREAKPOINT,
				'wpsp_ajax_nonce'   => $wpsp_ajax_nonce,
				'wpsp_home_url'     => home_url(),
				'wpsp_base_url'     => BSF_AIOSRS_PRO_URI,
				'wpsp_icons'        => $this->get_svg_icons(),
			)
		);
	} // End function editor_assets().

	/**
	 * Get the SVG icons.
	 */
	private function get_svg_icons() {
		$file_url = BSF_AIOSRS_PRO_URI . 'wpsp-config/controls/WPSPIcon.json';

		$response = wp_remote_get( $file_url );

		if ( is_wp_error( $response ) ) {
			return array(); // Return an empty array if there's an error.
		}

		$body  = wp_remote_retrieve_body( $response );
		$icons = json_decode( $body, true );

		return is_array( $icons ) ? $icons : array();
	}
}

/**
 *  Prepare if class 'BSF_SP_Init_Blocks' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
BSF_SP_Init_Blocks::get_instance();
