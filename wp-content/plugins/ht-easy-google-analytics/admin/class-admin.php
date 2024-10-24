<?php
namespace Ht_Easy_Ga4\Admin;

use Ht_Easy_Ga4\Admin\Tabs\General;
use Ht_Easy_Ga4\Admin\Tabs\Events_Tracking;
use Ht_Easy_Ga4\Admin\Tabs\Standard_Reports;
use Ht_Easy_Ga4\Admin\Tabs\Ecommerce_Reports;
use Ht_Easy_Ga4\Admin\Tabs\Realtime_Reports;

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

class Admin {
	use \Ht_Easy_Ga4\Helper_Trait;

	public $active_tab;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_plugin_menus' ) );
		add_action( 'admin_init', array( $this, 'register_setting_sections' ) );

		// Add submenu for upgrade to pro.
		add_action( 'admin_menu', array( $this, 'upgrade_submenu' ), 99999 );
		add_action( 'admin_footer', array( $this, 'enqueue_admin_head_scripts' ), 11 );

		// Active tab.
		$this->active_tab = '';

		$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $current_page === 'ht-easy-ga4-setting-page' ) {
			$this->active_tab = 'general';
		}

		if ( ! empty( $_GET['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->active_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( ! $this->is_pro_plugin_active() ) {
			// Render ecommerce reports tab content.
			add_action( 'admin_footer', array( $this, 'insert_pro_notice_markup' ) );
		}

		add_filter( 'submenu_file', array( $this, 'set_submenu_as_current_menu' ), 10, 2 );
	}

	public function register_plugin_menus() {
		global $submenu;

		add_menu_page(
			__( 'HT Easy GA4', 'ht-easy-ga4' ),
			__( 'HT Easy GA4', 'ht-easy-ga4' ),
			'manage_options',
			'ht-easy-ga4-setting-page',
			array( $this, 'render_plugin_page' ),
			HT_EASY_GA4_URL . 'admin/assets/images/logo.png',
			66
		);

		add_submenu_page(
			'ht-easy-ga4-setting-page',
			__( 'Reports', 'ht-easy-ga4' ),
			__( 'Reports', 'ht-easy-ga4' ),
			'manage_options',
			'ht-easy-ga4-setting-page&tab=standard_reports',
			'__return_null()'
		);

		add_submenu_page(
			'ht-easy-ga4-setting-page',
			__( 'Documentation', 'ht-easy-ga4' ),
			__( 'Documentation', 'ht-easy-ga4' ),
			'manage_options',
			'https://hasthemes.com/docs/ht-easy-ga4/how-to/',
			__return_null()
		);

		add_submenu_page(
			'ht-easy-ga4-setting-page',
			__( 'Need Help?', 'ht-easy-ga4' ),
			__( 'Need Help?', 'ht-easy-ga4' ),
			'manage_options',
			'https://hasthemes.com/contact-us/',
			__return_null()
		);

		if ( isset( $submenu['ht-easy-ga4-setting-page'][0][0] ) ) {
			$submenu['ht-easy-ga4-setting-page'][0][0] = __( 'Settings', 'ht-easy-ga4' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}

	public function register_setting_sections() {
		register_setting( 'ht-easy-ga4-settings-option', 'ht_easy_ga4_id' );
		register_setting( 'ht-easy-ga4-settings-option', 'ht_easy_ga4_options' );
	}

	public function upgrade_submenu() {

		if ( $this->is_pro_plugin_installed() ) { // Already installed pro plugin.
			return;
		}

		add_submenu_page(
			'ht-easy-ga4-setting-page',
			__( 'Upgrade to Pro', 'ht-easy-ga4' ),
			__( 'Upgrade to Pro', 'ht-easy-ga4' ),
			'manage_options',
			'https://hasthemes.com/plugins/google-analytics-plugin-for-wordpress/?utm_source=admin&utm_medium=mainmenu&utm_campaign=free#pricing'
		);
	}

	public function enqueue_admin_head_scripts() {
		printf( '<style>%s</style>', '#adminmenu .toplevel_page_ht-easy-ga4-setting-page a.htga4-upgrade-pro { font-weight: 600; background-color: #ff6e30; color: #ffffff; text-align: center; margin-top: 4px;}' );
		$script = '(function ($) {
            $("#toplevel_page_ht-easy-ga4-setting-page .wp-submenu a").each(function() {
                if($(this)[0].href === "https://hasthemes.com/plugins/google-analytics-plugin-for-wordpress/?utm_source=admin&utm_medium=mainmenu&utm_campaign=free#pricing") {
                    $(this).addClass("htga4-upgrade-pro").attr("target", "_blank");
                }
            })
        })(jQuery);';
		printf( '<script>%s</script>', $script ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function is_ga4_admin_screen() {
		$screen = get_current_screen();

		if ( ! empty( $screen->id ) && $screen->id === 'toplevel_page_ht-easy-ga4-setting-page' ) {
			return true;
		}

		return false;
	}

	public function render_plugin_page() {
		// Define the tabs.
		$tabs = array(
			'general_options'  => __( 'General Options', 'ht-easy-ga4' ),
			'events_tracking'  => __( 'Events Tracking', 'ht-easy-ga4' ),
			'standard_reports' => __( 'Standard Reports', 'ht-easy-ga4' ),
		);

		if ( ! $this->is_pro_plugin_active() ) {
			$tabs['events_tracking']   = __( 'Events Tracking <span class="htga4_pro_badge">Pro</span>', 'ht-easy-ga4' );
			$tabs['ecommerce_reports'] = __( 'E-commerce Reports <span class="htga4_pro_badge">Pro</span>', 'ht-easy-ga4' );
			$tabs['realtime_reports']  = __( 'Realtime Reports <span class="htga4_pro_badge">Pro</span>', 'ht-easy-ga4' );
		}

		$tabs = apply_filters( 'htga4_settings_tabs', $tabs );

		// Get the current tab or set the default.
		$current_tab = $this->get_current_tab();
		?>
			<div class="wrap htga4">
				<div id="htga4-loading">
					<div id="htga4-loading-spinner"></div>
				</div>

				<h2><?php echo esc_html__( 'HT Easy GA4 Option', 'ht-easy-ga4' ); ?></h2>
				<?php $this->save_message(); ?>

				<?php
					// Output the tabs navigation.
					echo '<div class="nav-tab-wrapper">';
				foreach ( $tabs as $tab => $label ) {
					$active = ( $current_tab === $tab ) ? 'nav-tab-active' : '';

					printf(
						'<a class="nav-tab %1$s" href="%2$s&tab=%3$s">%4$s</a>',
						esc_attr( $active ),
						esc_url( admin_url( 'admin.php?page=ht-easy-ga4-setting-page' ) ),
						esc_attr( $tab ),
						wp_kses_post( $label )
					);
				}
					echo '</div>';

					// Output the tab content.
				switch ( $current_tab ) {
					case 'general_options':
						General::instance()->render();
						break;

					case 'events_tracking':
						Events_Tracking::instance()->render();
						break;

					case 'standard_reports':
						Standard_Reports::instance()->render();
						break;

					case 'ecommerce_reports':
						Ecommerce_Reports::instance()->render();
						break;

					case 'realtime_reports':
						Realtime_Reports::instance()->render();
						break;
				}
				?>
			</div>
		<?php
	}

	public function insert_pro_notice_markup() {
		if ( ! $this->is_ga4_admin_screen() ) {
			return;
		}
		?>
		<div class="htga4_pro_adv_popup">
			<div class="htga4_pro_adv_popup_inner">
				<button class="htga4_pro_adv_popup_close">
					<svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M9.08366 1.73916L8.26116 0.916656L5.00033 4.17749L1.73949 0.916656L0.916992 1.73916L4.17783 4.99999L0.916992 8.26082L1.73949 9.08332L5.00033 5.82249L8.26116 9.08332L9.08366 8.26082L5.82283 4.99999L9.08366 1.73916Z" fill="currentColor"></path>
					</svg>
				</button>
				<div class="htga4_pro_adv_popup_icon"><img src="<?php echo esc_url( HT_EASY_GA4_URL . '/admin/assets/images/pro-badge.png' ); ?>" alt="pro"></div>
				<h2 class="htga4_pro_adv_popup_title"><?php echo esc_html__( 'Upgrade to PRO', 'ht-easy-ga4' ); ?></h2>
				<p class="htga4_pro_adv_popup_text"><?php echo esc_html__( 'Our free version is great, but it doesn\'t have all our advanced features. The best way to unlock all of the features in our plugin is by purchasing the pro version.', 'ht-easy-ga4' ); ?></p>
				<a href="https://hasthemes.com/plugins/google-analytics-plugin-for-wordpress?utm_source=wp-org&utm_medium=ht-ga4&utm_campaign=htga4_buy_pro_popup" class="htga4_pro_adv_popup_button" target="_blank"><?php echo esc_html__( 'Buy Now', 'ht-easy-ga4' ); ?></a>
			</div>
		</div>		
		<?php
	}

	public function save_message() {
		if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<div class="updated notice is-dismissible"> 
				<p><strong><?php echo esc_html__( 'Successfully Settings Saved.', 'ht-easy-ga4' ); ?></strong></p>
			</div>
			<?php
		}
	}

	public function get_current_admin_url() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );

		if ( ! $uri ) {
			return '';
		}

		return remove_query_arg( array( '_wpnonce', '_wc_notice_nonce', 'wc_db_update', 'wc_db_update_nonce', 'wc-hide-notice' ), admin_url( $uri ) );
	}

	public function set_submenu_as_current_menu( $submenu_file, $parent_file ) {
		$current_page = ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_tab  = ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ( $current_page === 'ht-easy-ga4-setting-page' ) && $current_tab === 'standard_reports' || $current_tab === 'ecommerce_reports' ) {
			$submenu_file = 'ht-easy-ga4-setting-page&tab=standard_reports';
		}

		return $submenu_file;
	}
}

new Admin();

?>
