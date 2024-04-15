<?php
/**
 * The admin-specific of the plugin.
 *
 * @link https://shapedplugin.com
 * @since 2.0.0
 *
 * @package Easy_Accordion_Free
 * @subpackage Easy_Accordion_Free/admin
 */

/**
 * The class for the admin-specific functionality of the plugin.
 */
class Easy_Accordion_Free_Admin {

	/**
	 * Instance
	 *
	 * @since 2.1.10
	 *
	 * @access private
	 * @static
	 *
	 * @var Easy_Accordion_Free_Admin The single instance of the class.
	 */
	private static $instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since 2.0.0
	 * @static
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register the stylesheets for the admin area of the plugin.
	 *
	 * @since  2.0.0
	 * @return void
	 */
	public function enqueue_admin_styles() {
		$current_screen        = get_current_screen();
		$the_current_post_type = $current_screen->post_type;
		if ( 'sp_easy_accordion' === $the_current_post_type ) {
			wp_enqueue_style( 'sp-ea-fontello-icons' );
			wp_enqueue_style( 'sp-ea-style' );

			wp_enqueue_script( 'sp-ea-accordion-js' );
		}
		wp_enqueue_style( 'sp-ea-style-admin' );
	}

	/**
	 * Change Accordion updated messages.
	 *
	 * @param string $messages The Update messages.
	 * @return statement
	 */
	public function eap_updated_messages( $messages ) {
		global $post, $post_ID;
		$messages['sp_easy_accordion'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => sprintf( __( 'Accordion updated.', 'easy-accordion-free' ) ),
			2  => '',
			3  => '',
			4  => __( ' updated.', 'easy-accordion-free' ),
			5  => isset( $_GET['revision'] ) ? sprintf( wp_kses_post( 'Accordion restored to revision from %s', 'easy-accordion-free' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => sprintf( __( 'Accordion published.', 'easy-accordion-free' ) ),
			7  => __( 'Accordion saved.', 'easy-accordion-free' ),
			8  => sprintf( __( 'Accordion submitted.', 'easy-accordion-free' ) ),
			9  => sprintf( wp_kses_post( 'Accordion scheduled for: <strong>%1$s</strong>.', 'easy-accordion-free' ), date_i18n( __( 'M j, Y @ G:i', 'easy-accordion-free' ), strtotime( $post->post_date ) ) ),
			10 => sprintf( __( 'Accordion draft updated.', 'easy-accordion-free' ) ),
		);
		return $messages;
	}

	/**
	 * Add accordion admin columns.
	 *
	 * @return statement
	 */
	public function filter_accordion_admin_column() {
		$admin_columns['cb']        = '<input type="checkbox" />';
		$admin_columns['title']     = __( 'Accordion Group Title', 'easy-accordion-free' );
		$admin_columns['shortcode'] = __( 'Shortcode', 'easy-accordion-free' );
		$admin_columns['date']      = __( 'Date', 'easy-accordion-free' );

		return $admin_columns;
	}

	/**
	 * Display admin columns for the accordions.
	 *
	 * @param mix    $column The columns.
	 * @param string $post_id The post ID.
	 * @return void
	 */
	public function display_accordion_admin_fields( $column, $post_id ) {
		$upload_data    = get_post_meta( $post_id, 'sp_eap_upload_options', true );
		$accordion_type = isset( $upload_data['eap_accordion_type'] ) ? $upload_data['eap_accordion_type'] : '';
		switch ( $column ) {
			case 'shortcode':
				echo '<div class="sp_eap-after-copy-text"><i class="fa fa-check-circle"></i>  Shortcode  Copied to Clipboard! </div><input style="width: 270px; padding: 6px; cursor:pointer;"  type="text" onClick="this.select();" readonly="readonly" value="[sp_easyaccordion id=&quot;' . esc_attr( $post_id ) . '&quot;]"/>';
				break;
			case 'accordion_type':
				echo esc_html( ucwords( str_replace( '-', ' ', $accordion_type ) ) );
		} // end switch.
	}

	/**
	 * Bottom review notice.
	 *
	 * @param string $text The review notice.
	 * @return string
	 */
	public function sp_eap_review_text( $text ) {
		$screen = get_current_screen();
		if ( 'sp_easy_accordion' === $screen->post_type ) {
			$url  = 'https://wordpress.org/support/plugin/easy-accordion-free/reviews/?filter=5';
			$text = sprintf( wp_kses_post( 'Enjoying <strong>Easy Accordion?</strong> Please rate us <span class="spea-footer-text-star">★★★★★</span> <a href="%s" target="_blank">WordPress.org.</a> Your positive feedback will help us grow more. Thank you! 😊', 'easy-accordion-free' ), $url );
		}
		return $text;
	}
	/**
	 * Bottom version notice.
	 *
	 * @param string $text Version notice.
	 * @return string
	 */
	public function sp_eap_version_text( $text ) {
		$screen = get_current_screen();
		if ( 'sp_easy_accordion' === $screen->post_type ) {
			$text = 'Easy Accordion ' . SP_EA_VERSION;
		}
		return $text;
	}
	/**
	 *  Add plugin row meta link
	 *
	 * @param [array] $plugin_meta Add plugin row meta link.
	 * @param [url]   $file plugin row meta link.
	 * @return array
	 */
	public function after_easy_accodion_row_meta( $plugin_meta, $file ) {
		if ( SP_EA_BASENAME === $file ) {
			$plugin_meta[] = '<a href="https://easyaccordion.io/easy-accordion-free-demo/" target="_blank">' . __( 'Live Demo', 'easy-accordion-free' ) . '</a>';
		}
		return $plugin_meta;
	}
	/**
	 * Redirect after activation.
	 *
	 * @param string $file Path to the plugin file, relative to the plugin.
	 * @return void
	 */
	public function sp_ea_redirect_after_activation( $file ) {
		if ( SP_EA_BASENAME === $file && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			exit( esc_url( wp_safe_redirect( admin_url( 'edit.php?post_type=sp_easy_accordion&page=eap_help' ) ) ) );
		}
	}

	/**
	 * Add plugin action menu
	 *
	 * @param array  $links The action link.
	 * @param string $file The file.
	 *
	 * @return array
	 */
	public function add_plugin_action_links( $links, $file ) {

		if ( SP_EA_BASENAME === $file ) {
			$new_links =
				sprintf( '<a href="%s">%s</a>', admin_url( 'post-new.php?post_type=sp_easy_accordion' ), __( 'Add Accordion', 'easy-accordion-free' ) );
			array_unshift( $links, $new_links );

			$links['go_pro'] = sprintf( '<a target="_blank" href="%1$s" style="color: #35b747; font-weight: 700;">Go Pro!</a>', 'https://easyaccordion.io/pricing/?ref=1' );
		}

		return $links;
	}
}
