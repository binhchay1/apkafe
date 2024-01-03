<?php
/**
 * Brainstorm_Update_AIOSRS_Pro initial setup
 *
 * @package Schema Pro
 * @since 1.0.0
 */

if ( ! class_exists( 'Brainstorm_Update_AIOSRS_Pro' ) ) :

	/**
	 * Brainstorm Update
	 */
	class Brainstorm_Update_AIOSRS_Pro {

		/**
		 * Instance
		 *
		 * @var object Class object.
		 * @access private
		 */
		private static $instance;

		/**
		 * Initiator
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

			self::version_check();

			add_action( 'init', array( $this, 'load' ), 999 );
			add_filter( 'bsf_get_license_message_wp-schema-pro', array( $this, 'license_message_aiosrs_pro' ), 10, 2 );
			add_filter( 'bsf_skip_braisntorm_menu', array( $this, 'skip_menu' ) );
			add_filter( 'bsf_skip_author_registration', array( $this, 'skip_menu' ) );
			add_filter( 'bsf_remove_wp-schema-pro_from_registration_listing', '__return_true' );
			add_filter( 'agency_updater_productname_wp-schema-pro', array( $this, 'product_name' ) );

			// Add popup license form on plugin list page.
			add_action( 'plugin_action_links_' . BSF_AIOSRS_PRO_BASE, array( $this, 'bsf_aiosrs_pro_license_form_and_links' ) );
			add_action( 'network_admin_plugin_action_links_' . BSF_AIOSRS_PRO_BASE, array( $this, 'bsf_aiosrs_pro_license_form_and_links' ) );

			add_filter( 'bsf_registration_page_url_wp-schema-pro', array( $this, 'bsf_aiosrs_pro_bsf_registration_page_url' ) );
			add_filter( 'bsf_product_activation_notice_wp-schema-pro', __class__ . '::update_aiosrs_license_notice', 10, 3 );
		}

		/**
		 * Updated the license notice when white label is activated.
		 *
		 * @param   string $message for license notice.
		 * @param   string $url for activate button.
		 * @param   string $product_name replace the name with white label.
		 * @return string message.
		 */
		public static function update_aiosrs_license_notice( $message, $url, $product_name ) {

			$branding = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];

			if ( isset( $branding['sp_plugin_name'] ) && '' !== $branding['sp_plugin_name'] ) {
				$product_name = $branding['sp_plugin_name'];
			}
			/* translators: %s: search term */
			$message = sprintf( __( 'Please <a href= %1$s class="bsf-core-license-form-btn" plugin-slug="wp-schema-pro"> activate </a>your copy of the<i> %2$s </i>to get update notifications, access to support features & other resources!', 'wp-schema-pro' ), $url, $product_name );

			return $message;
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @param   mixed $links Plugin Action links.
		 * @return  array        Filtered plugin action links.
		 */
		public function bsf_aiosrs_pro_license_form_and_links( $links = array() ) {
			if ( function_exists( 'get_bsf_inline_license_form' ) ) {

				$args = array(
					'product_id'         => 'wp-schema-pro',
					'popup_license_form' => true,
				);

				return get_bsf_inline_license_form( $links, $args, 'edd' );
			}

			return $links;
		}

		/**
		 * Bsf registration page
		 *
		 * @since 1.1.0
		 */
		public function bsf_aiosrs_pro_bsf_registration_page_url() {
			if ( is_multisite() ) {
				return network_admin_url( 'plugins.php?bsf-inline-license-form=wp-schema-pro' );
			} else {
				return admin_url( 'plugins.php?bsf-inline-license-form=wp-schema-pro' );
			}
		}

		/**
		 * Product Name.
		 *
		 * @param  string $name  Product Name.
		 * @return string product name.
		 */
		public function product_name( $name ) {

			$branding = get_option( 'wp-schema-pro-branding-settings' );
			if ( isset( $branding['sp_plugin_name'] ) && '' !== $branding['sp_plugin_name'] ) {
				$name = $branding['sp_plugin_name'];
			}
			return $name;
		}

		/**
		 * Skip Menu.
		 *
		 * @param array $products products.
		 * @return array $products updated products.
		 */
		public function skip_menu( $products ) {
			$products[] = 'wp-schema-pro';

			return $products;
		}

		/**
		 * Update brainstorm product version and product path.
		 *
		 * @return void
		 */
		public static function version_check() {

			$bsf_core_version_file = realpath( BSF_AIOSRS_PRO_DIR . '/admin/bsf-core/version.yml' );

			// Is file 'version.yml' exist?
			if ( is_file( $bsf_core_version_file ) ) {
				global $bsf_core_version, $bsf_core_path;
				$bsf_core_dir = realpath( BSF_AIOSRS_PRO_DIR . '/admin/bsf-core/' );
				$version      = file_get_contents( realpath( plugin_dir_path( BSF_AIOSRS_PRO_FILE ) . '/admin/bsf-core/version.yml' ) );

				// Compare versions.
				if ( version_compare( $version, $bsf_core_version, '>' ) ) {
					$bsf_core_version = $version;
					$bsf_core_path    = $bsf_core_dir;
				}
			}
		}

		/**
		 * Add Message for license.
		 *
		 * @param  string $content       get the link content.
		 * @param  string $purchase_url  purchase_url.
		 * @return string                output message.
		 */
		public function license_message_aiosrs_pro( $content, $purchase_url ) {
			$purchase_url = apply_filters( 'uael_licence_url', $purchase_url );

			$message = "<p><a target='_blank' href='" . esc_url( $purchase_url ) . "'>" . esc_html__( 'Get the license >>', 'wp-schema-pro' ) . '</a></p>';

			$branding = get_option( 'wp-schema-pro-branding-settings' );

			if ( isset( $branding['sp_plugin_name'] ) && '' !== $branding['sp_plugin_name'] ) {
				$message = '';
			}
			return $message;
		}

		/**
		 * Load the brainstorm updater.
		 *
		 * @return void
		 */
		public function load() {
			global $bsf_core_version, $bsf_core_path;
			if ( is_file( realpath( $bsf_core_path . '/index.php' ) ) ) {
				include_once realpath( $bsf_core_path . '/index.php' );
			}
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Brainstorm_Update_AIOSRS_Pro::get_instance();

endif;
