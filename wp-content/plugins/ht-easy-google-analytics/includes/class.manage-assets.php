<?php
namespace Ht_Easy_Ga4;

/**
 * Loading Google Analytics 4 scripts in header.
 */
class Manage_Assets {
    public $version;
    public $active_tab;

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

    public function __construct() {
        // Set time as the version for development mode.
		if( defined('WP_DEBUG') && WP_DEBUG ){
			$this->version = time();
		} else {
			$this->version = HT_EASY_GA4_VERSION;
		}

        // Active tab.
		$this->active_tab = ! empty( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '';

        // Enqueue script.
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ), 9999 );
    }
	/**
    * $hook_suffix Hook Suffix
    * Fires when scripts and styles are enqueued.
	*/
	public function action_admin_enqueue_scripts( $hook_suffix ) {
		if ( $hook_suffix == 'toplevel_page_ht-easy-ga4-setting-page' ) {

			wp_enqueue_style( 'select2', HT_EASY_GA4_URL . 'admin/assets/css/select2.min.css', array(), $this->version );
			wp_enqueue_style( 'daterangepicker', HT_EASY_GA4_URL . 'admin/assets/css/daterangepicker.css', array(), $this->version );
			wp_enqueue_style( 'htga4-admin', HT_EASY_GA4_URL . 'admin/assets/css/admin.css', array(), $this->version );

			wp_enqueue_script( 'select2', HT_EASY_GA4_URL . 'admin/assets/js/select2.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( 'chart', HT_EASY_GA4_URL . 'admin/assets/js/chart.js', array(), $this->version, true );
			wp_enqueue_script( 'moment', HT_EASY_GA4_URL . 'admin/assets/js/moment.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( 'daterangepicker', HT_EASY_GA4_URL . 'admin/assets/js/daterangepicker.js', array( 'moment' ), $this->version, true );
			wp_enqueue_script( 'jquery-interdependencies', HT_EASY_GA4_URL . 'admin/assets/js/jquery-interdependencies.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( 'htga4-admin', HT_EASY_GA4_URL . 'admin/assets/js/admin.js', array( 'jquery' ), $this->version, true );

			if ( $this->active_tab == 'standard_reports' || $this->active_tab == 'ecommerce_reports' ) {
				wp_enqueue_script( 'htga4-chart-active', HT_EASY_GA4_URL . 'admin/assets/js/chart-active.js', array( 'chart', 'daterangepicker' ), $this->version, true );
			}
		}
	}
}

Manage_Assets::instance();