<?php
/**
 * The plugin gutenberg block Initializer.
 *
 * @link       https://shapedplugin.com/
 * @since      2.4.1
 *
 * @package    Easy_Accordion_Free
 * @subpackage Easy_Accordion_Free/Admin
 * @author     ShapedPlugin <support@shapedplugin.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Easy_Accordion_Free_Gutenberg_Block_Init' ) ) {
	/**
	 * Easy_Accordion_Free_Gutenberg_Block_Init class.
	 */
	class Easy_Accordion_Free_Gutenberg_Block_Init {
		/**
		 * Script and style suffix
		 *
		 * @since 2.4.1
		 * @access protected
		 * @var string
		 */
		protected $suffix;
		/**
		 * Custom Gutenberg Block Initializer.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'sp_easy_accordion_free_gutenberg_shortcode_block' ) );
			add_action( 'enqueue_block_editor_assets', array( $this, 'sp_easy_accordion_free_block_editor_assets' ) );
		}

		/**
		 * Register block editor script for backend.
		 */
		public function sp_easy_accordion_free_block_editor_assets() {
			wp_enqueue_script(
				'sp-easy-accordion-free-shortcode-block',
				plugins_url( '/GutenbergBlock/build/index.js', dirname( __FILE__ ) ),
				array( 'jquery' ),
				SP_EA_VERSION,
				true
			);

			/**
			 * Register block editor css file enqueue for backend.
			 */
			wp_enqueue_style( 'sp-ea-fontello-icons' );
			wp_enqueue_style( 'sp-ea-style' );
		}
		/**
		 * Shortcode list.
		 *
		 * @return array
		 */
		public function sp_easy_accordion_free_post_list() {
			$shortcodes = get_posts(
				array(
					'post_type'      => 'sp_easy_accordion',
					'post_status'    => 'publish',
					'posts_per_page' => 9999,
				)
			);

			if ( count( $shortcodes ) < 1 ) {
				return array();
			}

			return array_map(
				function ( $shortcode ) {
						return (object) array(
							'id'    => absint( $shortcode->ID ),
							'title' => esc_html( $shortcode->post_title ),
						);
				},
				$shortcodes
			);
		}

		/**
		 * Register Gutenberg shortcode block.
		 */
		public function sp_easy_accordion_free_gutenberg_shortcode_block() {
			/**
			 * Register block editor js file enqueue for backend.
			 */
			$prefix = defined( 'WP_DEBUG' ) && WP_DEBUG ? '' : '.min';
			wp_register_script( 'sp-ea-gb-accordion-config', esc_url( SP_EA_URL . 'public/assets/js/script.js' ), array( 'jquery', 'sp-ea-accordion-js' ), SP_EA_VERSION, true );

			wp_localize_script(
				'sp-ea-gb-accordion-config',
				'sp_easy_accordion_free',
				array(
					'url'           => SP_EA_URL,
					'loadScript'    => SP_EA_URL . 'public/assets/js/script.js',
					'link'          => admin_url( 'post-new.php?post_type=sp_easy_accordion' ),
					'shortCodeList' => $this->sp_easy_accordion_free_post_list(),
				)
			);

			/**
			 * Register Gutenberg block on server-side.
			 */
			register_block_type(
				'sp-easy-accordion-pro/shortcode',
				array(
					'attributes'      => array(
						'shortcodelist'      => array(
							'type'    => 'object',
							'default' => '',
						),
						'shortcode'          => array(
							'type'    => 'string',
							'default' => '',
						),
						'showInputShortcode' => array(
							'type'    => 'boolean',
							'default' => true,
						),
						'preview'            => array(
							'type'    => 'boolean',
							'default' => false,
						),
						'is_admin'           => array(
							'type'    => 'boolean',
							'default' => is_admin(),
						),
					),
					'example'         => array(
						'attributes' => array(
							'preview' => true,
						),
					),
					// Enqueue blocks.editor.build.js in the editor only.
					'editor_script'   => array(
						'sp-ea-accordion-js',
						'sp-ea-gb-accordion-config',
					),
					// Enqueue blocks.editor.build.css in the editor only.
					'editor_style'    => array(),
					'render_callback' => array( $this, 'sp_easy_accordion_free_render_shortcode' ),
				)
			);
		}

		/**
		 * Render callback.
		 *
		 * @param string $attributes Shortcode.
		 * @return string
		 */
		public function sp_easy_accordion_free_render_shortcode( $attributes ) {

			$class_name = '';
			if ( ! empty( $attributes['className'] ) ) {
				$class_name = 'class="' . esc_attr( $attributes['className'] ) . '"';
			}

			if ( ! $attributes['is_admin'] ) {
				return '<div ' . $class_name . '>' . do_shortcode( '[sp_easyaccordion id="' . sanitize_text_field( $attributes['shortcode'] ) . '"]' ) . '</div>';
			}
			$edit_accordion_link = get_edit_post_link( sanitize_text_field( $attributes['shortcode'] ) );
			return '<div id="' . uniqid() . '" ' . $class_name . ' ><a href="' . $edit_accordion_link . '" target="_blank" class="sp-easyaccordion-block-edit-button">Edit Accordion</a>' . do_shortcode( '[sp_easyaccordion id="' . sanitize_text_field( $attributes['shortcode'] ) . '"]' ) . '</div>';
		}
	}
}
