<?php
/**
 * The admin preview.
 *
 * @link        https://shapedplugin.com/
 * @since      2.1.4
 *
 * @package    Easy_Accordion_Free
 * @subpackage Easy_Accordion_Free/admin
 */

/**
 * The admin preview.
 */
class Easy_Accordion_Free_Preview {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.1.4
	 */
	public function __construct() {
		$this->easy_accordion_preview_action();
	}

	/**
	 * Public Action
	 *
	 * @return void
	 */
	private function easy_accordion_preview_action() {
		// admin Preview.
		add_action( 'wp_ajax_sp_eap_preview_meta_box', array( $this, 'sp_eap_backend_preview' ) );

	}

	/**
	 * Function Backed preview.
	 *
	 * @since 2.2.5
	 */
	public function sp_eap_backend_preview() {
		$nonce = isset( $_POST['ajax_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['ajax_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'eapro_metabox_nonce' ) ) {
			return;
		}

		$setting = array();
		// XSS ok.
		// No worries, This "POST" requests is sanitizing in the below array map.
		$data = ! empty( $_POST['data'] ) ? wp_unslash( $_POST['data'] )  : ''; // phpcs:ignore
		parse_str( $data, $setting );
		// Preset Layouts.
		$post_id            = $setting['post_ID'];
		$upload_data        = $setting['sp_eap_upload_options'];
		$accordion_id       = $post_id;
		$shortcode_data     = $setting['sp_eap_shortcode_options'];
		$main_section_title = $setting['post_title'];

		$ea_dynamic_css = SP_EA_Front_Scripts::load_dynamic_style( $post_id, $shortcode_data );
		echo '<style>' . $ea_dynamic_css['dynamic_css'] . '</style>';

		Easy_Accordion_Free_Shortcode::sp_eap_html_show( $post_id, $upload_data, $shortcode_data, $main_section_title );
		?>
		<script src="<?php echo esc_url( SP_EA_URL . 'public/assets/js/collapse.min.js' ); ?>" ></script>
		<script src="<?php echo esc_url( SP_EA_URL . 'public/assets/js/script.min.js' ); ?>" ></script>
		<?php
		die();
	}

}
new Easy_Accordion_Free_Preview();
