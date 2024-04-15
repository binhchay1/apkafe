<?php
/**
 * The file that defines the shortcode plugin class.
 *
 * A class definition that define easy accordion  shortcode of the plugin.
 *
 * @link       https://shapedplugin.com/
 * @since      2.0.0
 *
 * @package   easy-accordion-free
 * @subpackage easy-accordion-free/includes
 */

/**
 * The Shortcode class.
 *
 * This is used to define shortcode, shortcode attributes.
 */
class Easy_Accordion_Free_Shortcode {

	/**
	 * Holds the class object.
	 *
	 * @since 2.0.0
	 * @var object
	 */
	public static $instance;

	/**
	 * Contain the base class object.
	 *
	 * @since 2.0.0
	 * @var object
	 */
	public $base;

	/**
	 * Holds the accordion data.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	public $data;


	/**
	 * Undocumented variable
	 *
	 * @var string $post_id The post id of the accordion shortcode.
	 */
	public $post_id;


	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since 2.0.0
	 * @static
	 * @return Easy_Accordion_Free_Shortcode Shortcode instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Primary class constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_shortcode( 'sp_easyaccordion', array( $this, 'sp_easy_accordion_shortcode' ) );
		add_action( 'save_post', array( $this, 'delete_page_accordion_option_on_save' ) );
	}


	/**
	 * Full html show.
	 *
	 * @param array $post_id Shortcode ID.
	 * @param array $upload_data get all layout options.
	 * @param array $shortcode_data get all meta options.
	 * @param array $main_section_title shows section title.
	 */
	public static function sp_eap_html_show( $post_id, $upload_data, $shortcode_data, $main_section_title ) {
		if ( empty( $upload_data ) ) {
			return;
		}
		$accordion_type  = isset( $upload_data['eap_accordion_type'] ) ? $upload_data['eap_accordion_type'] : '';
		$content_sources = isset( $upload_data['accordion_content_source'] ) ? $upload_data['accordion_content_source'] : '';

		// Shortcode Option.
		$accordion_layout      = isset( $shortcode_data['eap_accordion_layout'] ) ? $shortcode_data['eap_accordion_layout'] : 'vertical';
		$accordion_theme_class = 'sp-ea-one';
		$eap_accordion_uniq_id = isset( $shortcode_data['eap_accordion_uniq_id'] ) ? $shortcode_data['eap_accordion_uniq_id'] : 'sp_easy_accordion-' . time() . '';

		global $accordion_wrapper_class;
		$accordion_wrapper_class = $accordion_theme_class . ' sp-easy-accordion';
		$accordion_item_class    = 'sp-ea-single';
		$eap_schema_markup       = isset( $shortcode_data['eap_schema_markup'] ) ? $shortcode_data['eap_schema_markup'] : false;
		// Accordion settings.
		$eap_preloader             = isset( $shortcode_data['eap_preloader'] ) ? $shortcode_data['eap_preloader'] : false;
		$eap_active_event          = isset( $shortcode_data['eap_accordion_event'] ) ? $shortcode_data['eap_accordion_event'] : '';
		$eap_accordion_mode        = isset( $shortcode_data['eap_accordion_mode'] ) ? $shortcode_data['eap_accordion_mode'] : '';
		$eap_mutliple_collapse     = isset( $shortcode_data['eap_mutliple_collapse'] ) ? $shortcode_data['eap_mutliple_collapse'] : '';
		$eap_accordion_fillspace   = isset( $shortcode_data['eap_accordion_fillspace'] ) ? $shortcode_data['eap_accordion_fillspace'] : '';
		$eap_nofollow_link         = isset( $shortcode_data['eap_nofollow_link'] ) ? $shortcode_data['eap_nofollow_link'] : false;
		$nofollow_link_text        = $eap_nofollow_link ? 'rel=nofollow' : '';
		$eap_scroll_to_active_item = isset( $shortcode_data['eap_scroll_to_active_item'] ) ? $shortcode_data['eap_scroll_to_active_item'] : false;
		$eap_offset_to_scroll      = apply_filters( 'eap_offset_to_scroll', 0 );

		$eap_accordion_fillspace_height = isset( $shortcode_data['eap_accordion_fillspace_height']['all'] ) ? $shortcode_data['eap_accordion_fillspace_height']['all'] : $shortcode_data['eap_accordion_fillspace_height'];
		$acc_section_title              = isset( $shortcode_data['section_title'] ) ? $shortcode_data['section_title'] : '';
		// $acc_section_title_margin_bottom = isset( $shortcode_data['section_title_margin_bottom']['all'] ) ? $shortcode_data['section_title_margin_bottom']['all'] : $shortcode_data['section_title_margin_bottom'];
		$accordion_height   = isset( $shortcode_data['accordion_height'] ) ? $shortcode_data['accordion_height'] : '';
		$eap_animation_time = isset( $shortcode_data['eap_animation_time'] ) ? $shortcode_data['eap_animation_time'] : '';

		$eap_border           = isset( $shortcode_data['eap_border_css'] ) ? $shortcode_data['eap_border_css'] : '';
		$old_eap_border_width = isset( $eap_border['width'] ) ? $eap_border['width'] : '1';
		$eap_border_width     = isset( $eap_border['all'] ) ? $eap_border['all'] : $old_eap_border_width;
		$eap_border_style     = isset( $eap_border['style'] ) ? $eap_border['style'] : '';
		$eap_border_color     = isset( $eap_border['color'] ) ? $eap_border['color'] : '';
		// Section title.
		$section_title_typho       = isset( $shortcode_data['eap_section_title_typography'] ) ? $shortcode_data['eap_section_title_typography'] : '';
		$section_title_typho_color = isset( $section_title_typho['color'] ) ? $section_title_typho['color'] : '#444';
		// Accordion title.
		$eap_title_typho       = isset( $shortcode_data['eap_title_typography'] ) ? $shortcode_data['eap_title_typography'] : '';
		$eap_title_tag         = isset( $shortcode_data['ea_title_heading_tag'] ) ? $shortcode_data['ea_title_heading_tag'] : '3';
		$eap_title_typho_color = isset( $eap_title_typho['color'] ) ? $eap_title_typho['color'] : '#444';
		$eap_title_padding     = isset( $shortcode_data['eap_title_padding'] ) ? $shortcode_data['eap_title_padding'] : '';
		$eap_header_bg         = isset( $shortcode_data['eap_header_bg_color'] ) ? $shortcode_data['eap_header_bg_color'] : '';
		// header icon.
		// Expand / Collapse Icon.
		$eap_icon                 = isset( $shortcode_data['eap_expand_close_icon'] ) ? $shortcode_data['eap_expand_close_icon'] : '';
		$eap_expand_collapse_icon = isset( $shortcode_data['eap_expand_collapse_icon'] ) ? $shortcode_data['eap_expand_collapse_icon'] : '';
		$eap_ex_icon_position     = isset( $shortcode_data['eap_icon_position'] ) ? $shortcode_data['eap_icon_position'] : '';
		$eap_icon_size            = isset( $shortcode_data['eap_icon_size']['all'] ) ? $shortcode_data['eap_icon_size']['all'] : $shortcode_data['eap_icon_size'];
		$eap_icon_color           = isset( $shortcode_data['eap_icon_color_set'] ) ? $shortcode_data['eap_icon_color_set'] : '';
		$eap_collapse_icon        = 'plus';
		$eap_expand_icon          = 'minus';
		// Description.
		$eap_autop              = isset( $shortcode_data['eap_autop'] ) ? $shortcode_data['eap_autop'] : true;
		$eap_content_typo       = isset( $shortcode_data['eap_content_typography'] ) ? $shortcode_data['eap_content_typography'] : '';
		$eap_content_typo_color = isset( $eap_content_typo['color'] ) ? $eap_content_typo['color'] : '#444';
		$eap_description_bg     = isset( $shortcode_data['eap_description_bg_color'] ) ? $shortcode_data['eap_description_bg_color'] : '';

		wp_enqueue_script( 'sp-ea-accordion-js' );
		wp_enqueue_script( 'sp-ea-accordion-config' );
		ob_start();
		include SP_EA_PATH . '/public/views/templates/default-accordion.php';
		$html = ob_get_clean();
		echo apply_filters( 'sp_easy_accordion', $html, $post_id ); // phpcs:ignore
	}

	/**
	 * A shortcode for rendering the accordion.
	 *
	 * @param [string] $attributes Shortcode attributes.
	 * @param [string] $content Shortcode content.
	 * @return array
	 */
	public function sp_easy_accordion_shortcode( $attributes, $content = null ) {
		if ( empty( $attributes['id'] ) || ( get_post_status( $attributes['id'] ) === 'trash' ) || 'sp_easy_accordion' !== get_post_type( $attributes['id'] ) ) {
			return;
		}

		$post_id = esc_attr( intval( $attributes['id'] ) );

		// Content Accordion.
		$upload_data        = get_post_meta( $post_id, 'sp_eap_upload_options', true );
		$shortcode_data     = get_post_meta( $post_id, 'sp_eap_shortcode_options', true );
		$settings           = get_option( 'sp_eap_settings' );
		$main_section_title = get_the_title( $post_id );
		ob_start();
		// Stylesheet loading problem solving here. Shortcode id to push page id option for getting how many shortcode in the page.
		$get_page_data      = SP_EA_Front_Scripts::get_page_data();
		$found_generator_id = $get_page_data['generator_id'];
		if ( ! is_array( $found_generator_id ) || ! $found_generator_id || ! in_array( $post_id, $found_generator_id ) ) {
			// CSS Files.
			wp_enqueue_style( 'sp-ea-fontello-icons' );
			wp_enqueue_style( 'sp-ea-style' );
			$ea_dynamic_css = SP_EA_Front_Scripts::load_dynamic_style( $post_id, $shortcode_data );
			echo '<style>' . wp_strip_all_tags( $ea_dynamic_css['dynamic_css'] ) . '</style>';
		}
		// Update options if the existing shortcode id option not found.
		SP_EA_Front_Scripts::easy_accordion_update_options( $post_id, $get_page_data );
		self::sp_eap_html_show( $post_id, $upload_data, $shortcode_data, $main_section_title );
		return ob_get_clean();
	}
	/**
	 * Delete page shortcode ids array option on save
	 *
	 * @param  int $post_ID current post id.
	 * @return void
	 */
	public function delete_page_accordion_option_on_save( $post_ID ) {
		if ( is_multisite() ) {
			$option_key = 'easy_accordion_page_id' . get_current_blog_id() . $post_ID;
			if ( get_site_option( $option_key ) ) {
				delete_site_option( $option_key );
			}
		} elseif ( get_option( 'easy_accordion_page_id' . $post_ID ) ) {
				delete_option( 'easy_accordion_page_id' . $post_ID );
		}
	}
}
new Easy_Accordion_Free_Shortcode();
