<?php
/**
 * Schema Pro Blocks Loader.
 *
 * @package Schema Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BSF_SP_Loader' ) ) {

	/**
	 * Class BSF_Schema_Pro_Loader.
	 */
	final class BSF_SP_Loader {

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

			$this->wpsp_define_constants();

			$this->wpsp_load_plugin();
		}

		/**
		 * Defines all constants
		 *
		 * @since 2.2.0
		 */
		public function wpsp_define_constants() {
			define( 'SP_SLUG', 'wpsp' );
			define( 'WPSP_TABLET_BREAKPOINT', '976' );
			define( 'WPSP_MOBILE_BREAKPOINT', '767' );
		}

		/**
		 * Loads plugin files.
		 *
		 * @since 2.2.0
		 *
		 * @return void
		 */
		public function wpsp_load_plugin() {

			require_once BSF_AIOSRS_PRO_DIR . 'wpsp-blocks/classes/class-bsf-sp-init-blocks.php';
			require_once BSF_AIOSRS_PRO_DIR . 'wpsp-blocks/classes/class-bsf-sp-helper.php';
			require_once BSF_AIOSRS_PRO_DIR . 'wpsp-blocks/classes/class-bsf-sp-admin-helper.php';
		}

		/**
		 *  Prepare if class 'BSF_SP_Loader' exist.
		 *  Kicking this off by calling 'get_instance()' method
		 */

	}
	BSF_SP_Loader::get_instance();
}

