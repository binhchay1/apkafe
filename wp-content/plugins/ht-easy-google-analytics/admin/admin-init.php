<?php
namespace HtEasyGa4\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

/**
 * Input HT Easy GA4 Traking ID.
 */
class Ht_Easy_Ga4_Admin_Setting {
	use \HtEasyGa4\Helper_Trait;

	public $analytics_data_permission = null;
	public $accounts_result = array();
	public $reports;
	public $ecommerce_reports;
	public $active_tab;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'upgrade_submenu' ), 99999 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_footer', [ $this, 'enqueue_admin_head_scripts'], 11 );

		// Analytics data permission.
		if( $this->get_access_token() ){
			$accounts_result = $this->request_accounts();

			if( $accounts_result ){
				if( !empty($accounts_result['error']) && !empty($accounts_result['error']['code']) && $accounts_result['error']['code'] == 403 ){
					$this->accounts_result = $accounts_result;
					$this->analytics_data_permission = false;
				} else {
					$this->accounts_result = $accounts_result;
					$this->analytics_data_permission = true;
				}
			}
		}

		// Active tab.
		$this->active_tab = '';
		if( isset($_GET['page']) && sanitize_text_field($_GET['page'] === 'ht-easy-ga4-setting-page') ){
			$this->active_tab = 'general';
		}
		
		if( !empty( $_GET['tab'] ) ){
			$this->active_tab =  sanitize_text_field( $_GET['tab'] );
		}

		if ( 'standard_reports' === $this->active_tab && $this->get_option( 'data_stream_id' ) ) {
			$this->reports = $this->report_batch_request();
		}
		
		if ( 'ecommerce_reports' === $this->active_tab && $this->get_option( 'data_stream_id' ) ) {
			$this->ecommerce_reports = $this->report_batch_request_ecommerce();
		}

		// Enqueue script
		add_action( 'admin_enqueue_scripts', array( $this, 'localize_reports_data' ), 10000 );

		if( !$this->is_pro_plugin_active() ){
			// Render ecommerce reports tab content.
			add_action('htga4_ecommerce_reports', array( $this, 'render_ecommerce_reports_free' ) );
			add_action('admin_footer', array( $this, 'insert_pro_notice_markup' ) );
		}

		add_filter('submenu_file', array( $this, 'set_submenu_as_current_menu' ), 10, 2 );

		// For tose who's the pro version is below 1.0.1
		if( $this->is_pro_plugin_active() && version_compare( HTGA4_PRO_VERSION, '1.0.1', '<' ) ){
			add_filter('pre_update_option_ht_easy_ga4_options', array( $this, 'ecommerce_events_option_update' ), 10, 3 );
		}
	}

	function enqueue_admin_head_scripts() {
		printf( '<style>%s</style>', '#adminmenu .toplevel_page_ht-easy-ga4-setting-page a.htga4-upgrade-pro { font-weight: 600; background-color: #ff6e30; color: #ffffff; text-align: center; margin-top: 4px;}' );
        $script = '(function ($) {
            $("#toplevel_page_ht-easy-ga4-setting-page .wp-submenu a").each(function() {
                if($(this)[0].href === "https://hasthemes.com/plugins/google-analytics-plugin-for-wordpress/?utm_source=admin&utm_medium=mainmenu&utm_campaign=free#pricing") {
                    $(this).addClass("htga4-upgrade-pro").attr("target", "_blank");
                }
            })
        })(jQuery);';
		printf( '<script>%s</script>', $script );
    }

	public function is_ga4_admin_screen(){
		$screen = get_current_screen();

		if( !empty($screen->id) && $screen->id == 'toplevel_page_ht-easy-ga4-setting-page' ){
			return true;
		}

		return false;
    }

	/**
    * $hook_suffix Hook Suffix
    * Fires when scripts and styles are enqueued.
	*/
	public function localize_reports_data( $hook_suffix ) {
		if ( $hook_suffix == 'toplevel_page_ht-easy-ga4-setting-page' ) {
			$localize_vars = array();

			if ( $this->active_tab === 'standard_reports' ) {
				$reports = $this->reports;

				if ( ! empty( $reports['sessions'] ) ) {
					$localize_vars['sessions'] = array(
						'labels'           => array_values( $reports['sessions']['labels'] ),
						'current_dataset'  => $reports['sessions']['current_dataset'],
						'previous_dataset' => $reports['sessions']['previous_dataset'],
						'current_total'    => array_sum( array_values( $reports['sessions']['current_dataset'] ) ),
						'previous_total'   => array_sum( array_values( $reports['sessions']['previous_dataset'] ) ),
					);
				}

				if ( ! empty( $reports['page_views'] ) ) {
					$localize_vars['page_views'] = array(
						'labels'           => array_values( $reports['page_views']['labels'] ),
						'current_dataset'  => $reports['page_views']['current_dataset'],
						'previous_dataset' => $reports['page_views']['previous_dataset'],
						'current_total'    => array_sum( array_values( $reports['page_views']['current_dataset'] ) ),
						'previous_total'   => array_sum( array_values( $reports['page_views']['previous_dataset'] ) ),
					);
				}

				if ( ! empty( $reports['bounce_rate'] ) ) {
					$current_dataset = array_map(
						function( $item ) {
							return number_format( $item * 100, 2 );
						},
						$reports['bounce_rate']['current_dataset']
					);

					$previous_dataset = array_map(
						function( $item ) {
							return number_format( $item * 100, 2 );
						},
						$reports['bounce_rate']['previous_dataset']
					);

					$current_total  = 0;
					$previous_total = 0;

					// Prevent division by zero issue.
					if ( count( $current_dataset ) ) {
						$current_total  = (float) number_format( array_sum( array_values( $current_dataset ) ), 2 ) / count( $current_dataset );
						$previous_total = (float) number_format( array_sum( array_values( $previous_dataset ) ), 2 ) / count( $current_dataset );
					}

					$localize_vars['bounce_rate'] = array(
						'labels'           => array_values( $reports['bounce_rate']['labels'] ),
						'current_dataset'  => $current_dataset,
						'previous_dataset' => $previous_dataset,
						'current_total'    => $current_total,
						'previous_total'   => $previous_total,
					);
				}

				if ( ! empty( $reports['user_types'] ) ) {
					$localize_vars['user_types'] = array(
						'labels' => array_values( $reports['user_types']['labels'] ),
						'values' => array_values( $reports['user_types']['values'] ),
					);
				}

				if ( ! empty( $reports['device_types'] ) ) {
					$localize_vars['device_types'] = array(
						'labels' => array_values( $reports['device_types']['labels'] ),
						'values' => array_values( $reports['device_types']['values'] ),
					);
				}
			} // standard reports

			if ( $this->active_tab === 'ecommerce_reports' ) {
				$reports = $this->ecommerce_reports;

				if ( ! empty( $reports['transactions'] ) ) {
					$localize_vars['transactions'] = array(
						'labels'           => array_values( $reports['transactions']['labels'] ),
						'current_dataset'  => $reports['transactions']['current_dataset'],
						'previous_dataset' => $reports['transactions']['previous_dataset'],
						'current_total'    => array_sum( array_values( $reports['transactions']['current_dataset'] ) ),
						'previous_total'   => array_sum( array_values( $reports['transactions']['previous_dataset'] ) ),
					);
				}

				if ( ! empty( $reports['average_purchase_revenue'] ) ) {
					$localize_vars['average_purchase_revenue'] = array(
						'labels'           => array_values( $reports['average_purchase_revenue']['labels'] ),
						'current_dataset'  => $reports['average_purchase_revenue']['current_dataset'],
						'previous_dataset' => $reports['average_purchase_revenue']['previous_dataset'],
						'current_total'    => array_sum( array_values( $reports['average_purchase_revenue']['current_dataset'] ) ),
						'previous_total'   => array_sum( array_values( $reports['average_purchase_revenue']['previous_dataset'] ) ),
					);
				}

				if ( ! empty( $reports['purchase_revenue'] ) ) {
					$localize_vars['purchase_revenue'] = array(
						'labels'           => array_values( $reports['purchase_revenue']['labels'] ),
						'current_dataset'  => $reports['purchase_revenue']['current_dataset'],
						'previous_dataset' => $reports['purchase_revenue']['previous_dataset'],
						'current_total'    => array_sum( array_values( $reports['purchase_revenue']['current_dataset'] ) ),
						'previous_total'   => array_sum( array_values( $reports['purchase_revenue']['previous_dataset'] ) ),
					);
				}

			} // ecommerce reports

			$localize_vars['nonce']    = wp_create_nonce( 'htga4_nonce' );
			$localize_vars['ajax_url'] = admin_url( 'admin-ajax.php' );

			wp_localize_script( 'htga4-admin', 'htga4_params', $localize_vars );
		}
	}

	public function admin_menu() {
		global $submenu;

		add_menu_page(
			__( 'HT Easy GA4', 'ht-easy-ga4' ),
			__( 'HT Easy GA4', 'ht-easy-ga4' ),
			'manage_options',
			'ht-easy-ga4-setting-page',
			array( $this, 'plugin_page' ),
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

		if( isset($submenu['ht-easy-ga4-setting-page'][0][0]) ){
			$submenu['ht-easy-ga4-setting-page'][0][0] = __( 'Settings', 'ht-easy-ga4' );
		}
	}

	public function upgrade_submenu(){
		add_submenu_page(
            'ht-easy-ga4-setting-page',
            __('Upgrade to Pro', 'ht-easy-ga4'),
            __('Upgrade to Pro', 'ht-easy-ga4'),
            'manage_options', 
            'https://hasthemes.com/plugins/google-analytics-plugin-for-wordpress/?utm_source=admin&utm_medium=mainmenu&utm_campaign=free#pricing'
        );
	}

	public function admin_init(){
		register_setting( 'ht-easy-ga4-settings-option', 'ht_easy_ga4_id' );
		register_setting( 'ht-easy-ga4-settings-option', 'ht_easy_ga4_options' );
	}

	public function get_auth_url() {
		$auth_url = 'https://accounts.google.com/o/oauth2/auth';

		$auth_url = add_query_arg( 'client_id', $this->get_config('client_id'), $auth_url );
		$auth_url = add_query_arg( 'redirect_uri', $this->get_config('redirect_uris'), $auth_url );
		$auth_url = add_query_arg( 'state', admin_url( 'admin.php?page=ht-easy-ga4-setting-page' ), $auth_url );
		$auth_url = add_query_arg( 'scope', 'https://www.googleapis.com/auth/userinfo.email+https://www.googleapis.com/auth/analytics.readonly+https://www.googleapis.com/auth/userinfo.profile', $auth_url );
		$auth_url = add_query_arg( 'access_type', 'offline', $auth_url );
		$auth_url = add_query_arg( 'prompt', 'consent', $auth_url );
		$auth_url = add_query_arg( 'response_type', 'code', $auth_url );

		return $auth_url;
	}

	public function plugin_page() {
		// Define the tabs.
		$tabs = array(
			'general_options'  => __( 'General Options', 'ht-easy-ga4' ),
			'events_tracking'  => __( 'Events Tracking', 'ht-easy-ga4' ),
			'standard_reports' => __( 'Standard Reports', 'ht-easy-ga4' ),
		);

		if( !$this->is_pro_plugin_active() ){
			$tabs['events_tracking']   = __( 'Events Tracking <span class="htga4_pro_badge">Pro</span>', 'ht-easy-ga4' );
			$tabs['ecommerce_reports'] = __( 'E-commerce Reports <span class="htga4_pro_badge">Pro</span>', 'ht-easy-ga4' );
		}

		$tabs = apply_filters( 'htga4_settings_tabs', $tabs);

		if( $this->is_pro_plugin_active() && version_compare( HTGA4_PRO_VERSION, '1.0.1', '<' ) ){
			unset($tabs['events_tracking']);
		}

		// Get the current tab or set the default.
		$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : 'general_options';
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
						$active,
						admin_url( 'admin.php?page=ht-easy-ga4-setting-page' ),
						$tab,
						$label
					);
				}
					echo '</div>';

					// Output the tab content.
				switch ( $current_tab ) {
					case 'general_options':
						echo '<form method="post" class="htga4 htga4_general_options" action="options.php">';
						settings_fields( 'ht-easy-ga4-settings-option' );
						$this->render_general_settings();
						submit_button();
						echo '</form>';
						break;

					case 'events_tracking':
						echo '<form method="post" class="htga4" action="options.php">';
						settings_fields( 'ht-easy-ga4-settings-option' );
						$this->render_events_tracking_tab_content();
						submit_button();
						echo '</form>';
						break;

					case 'standard_reports':
						echo '<form method="post" class="htga4 htga4_standard_reports" action="options.php">';
						settings_fields( 'ht-easy-ga4-settings-option' );
						$this->render_standard_reports();
						echo '</form>';
						break;

					case 'ecommerce_reports':
						echo '<form method="post" class="htga4 htga4_ecommerce_reports" action="options.php">';
						settings_fields( 'ht-easy-ga4-settings-option' );
						do_action('htga4_ecommerce_reports', $this);
						echo '</form>';
						break;
				}
				?>
			</div>
		<?php
	}

	public function render_events_tracking_tab_content() {
		// Defaults for Initial install of the plugin.
		$initial_settings = array(
			'enable_ecommerce_events' => '',
			'view_item_event'         => '',
			'view_item_list_event'    => '',
			'add_to_cart_event'       => '',
			'begin_checkout_event'    => '',
			'purchase_event'          => '',
			'vimeo_video_event'	      => '',
			'self_hosted_video_event' => '',
			'self_hosted_audio_event' => ''
		);

		// User saved data.
		if( !empty(get_option('ht_easy_ga4_options')) ){
			$settings = wp_parse_args( get_option('ht_easy_ga4_options'), $initial_settings );
		} else {
			$settings = $initial_settings;
		}

		$pro_status_class = '';
		if( !$this->is_pro_plugin_active() ){
			$pro_status_class = 'htga4_no_pro';
		}
		
		do_action('htga4_events_tracking_tab_content_before');
		?>
		<div class="htga4 htga4-events-tracking-tab-content-area">
			<div class="htga4-tab-content-left">

				<h2 class="htga4-section-heading"><?php _e('E-Commerce Events', 'ht-easy-ga4') ?></h2>
				<div class="htga4-enable-ecommerce-events <?php echo esc_attr($pro_status_class) ?>">
					<span><?php echo esc_html__( 'Enable E-commerce Events', 'ht-easy-ga4' ); ?></span>
					<div class="htga4-checkbox-switch">
						<input name="ht_easy_ga4_options[enable_ecommerce_events]" type="hidden" id="" value="0" />
						<input name="ht_easy_ga4_options[enable_ecommerce_events]" type="checkbox" id="htga4_enable_ecommerce_events" <?php checked( 'on', $settings[ 'enable_ecommerce_events'] ); ?> />
						<label for="ht_easy_ga4_options[enable_ecommerce_events]">
							<span class="htga4-checkbox-switch-label on"><?php echo __('on', 'ht-easy-ga4') ?></span>
							<span class="htga4-checkbox-switch-label off"><?php echo __('off', 'ht-easy-ga4') ?></span>
							<span class="htga4-checkbox-switch-indicator"></span>
						</label>
					</div>
				</div>

				<div class="htga4-grid-box-wrapper <?php echo esc_attr($pro_status_class) ?>">

					<div class="htga4-grid-box">
                        <div class="htga4-grid-box-left">
                            <div class="htga4-grid-box-label">
                                <?php echo esc_html__( 'View Product', 'ht-easy-ga4' ); ?>
                            </div>
                            <span class="htga4-show-info">
								<i class="dashicons dashicons-editor-help"></i>
								<span class="htga4-show-info-content"><?php echo __( 'Fire the <b>View Content</b> event when a visitor views a content (e.g. when a visitor visits a product details page).', 'ht-easy-ga4' ); ?></span>
                            </span>
                        </div>

						<div class="htga4-grid-box-right">
						    <a href="https://htga4.hasthemes.com/docs/how-to-configure-the-plugin/" target="_blank">
                                <span class="htga4-show-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>
                                    <span class="htga4-show-info-content"><?php echo esc_html__('Documentation', 'ht-easy-ga4') ?></span>
                                </span>
                            </a>

							<div class="htga4-checkbox-switch">
								<?php if($settings[ 'enable_ecommerce_events'] && $this->is_pro_plugin_active()): ?>
								<input name="ht_easy_ga4_options[view_item_event]" type="hidden" id="" value="0" />
								<input name="ht_easy_ga4_options[view_item_event]" type="checkbox" id="view_item_event" <?php checked( 'on', $settings[ 'view_item_event'] ); ?> />							<?php endif; ?>

								<label for="ht_easy_ga4_options[view_item_event]">
									<span class="htga4-checkbox-switch-label on"><?php echo __('on', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-label off"><?php echo __('off', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-indicator"></span>
								</label>
							</div>
						</div>
					</div><!-- .htga4-grid-box -->

					<div class="htga4-grid-box">
						<div class="htga4-grid-box-left">
                            <div class="htga4-grid-box-label">
								<?php echo esc_html__( 'View Category', 'ht-easy-ga4' ); ?>
                            </div>
                            <span class="htga4-show-info">
								<i class="dashicons dashicons-editor-help"></i>
								<span class="htga4-show-info-content"><?php echo __( 'Fire the <b>View Category</b> event when a visitor views a category or archive page.', 'ht-easy-ga4' ); ?></span>
                            </span>
                        </div>

						<div class="htga4-grid-box-right">
						    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>

							<div class="htga4-checkbox-switch">
								<?php if($settings[ 'enable_ecommerce_events'] && $this->is_pro_plugin_active()): ?>
								<input name="ht_easy_ga4_options[view_item_list_event]" type="hidden" id="" value="0" />
								<input name="ht_easy_ga4_options[view_item_list_event]" type="checkbox" id="htga4_view_item_list_event" <?php checked( 'on', $settings[ 'view_item_list_event'] ); ?> />
								<?php endif ?>
								<label for="ht_easy_ga4_options[view_item_list_event]">
									<span class="htga4-checkbox-switch-label on"><?php echo __('on', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-label off"><?php echo __('off', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-indicator"></span>
								</label>
							</div>
						</div>
					</div><!-- .htga4-grid-box -->

					<div class="htga4-grid-box">
                        <div class="htga4-grid-box-left">
                            <div class="htga4-grid-box-label">
                                <?php echo esc_html__( 'Add to Cart', 'ht-easy-ga4' ); ?>
                            </div>
                            <span class="htga4-show-info">
								<i class="dashicons dashicons-editor-help"></i>
								<span class="htga4-show-info-content"><?php echo __( 'Fire the <b>Add To Cart</b> event when a visitor adds a product to their cart.', 'ht-easy-ga4' ); ?></span>
                            </span>
                        </div>
						<div class="htga4-grid-box-right">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>

							<div class="htga4-checkbox-switch">
								<?php if($settings[ 'enable_ecommerce_events'] && $this->is_pro_plugin_active()): ?>
								<input name="ht_easy_ga4_options[add_to_cart_event]" type="hidden" id="" value="0" />
								<input name="ht_easy_ga4_options[add_to_cart_event]" type="checkbox" id="htga4_add_to_cart_event" <?php checked( 'on', $settings[ 'add_to_cart_event'] ); ?> />
								<?php endif; ?>
								<label for="ht_easy_ga4_options[add_to_cart_event]">
									<span class="htga4-checkbox-switch-label on"><?php echo __('on', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-label off"><?php echo __('off', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-indicator"></span>
								</label>
							</div>
						</div>
					</div><!-- .htga4-grid-box -->

					<div class="htga4-grid-box">
                        <div class="htga4-grid-box-left">
                            <div class="htga4-grid-box-label">
                                <?php echo esc_html__( 'Initiate Checkout', 'ht-easy-ga4' ); ?>
                            </div>
                            <span class="htga4-show-info">
								<i class="dashicons dashicons-editor-help"></i>
								<span class="htga4-show-info-content"><?php echo __( 'Fire the <b>Initiate Checkout</b> event when a user starts checkout.', 'ht-easy-ga4' ); ?></span>
                            </span>
                        </div>

						<div class="htga4-grid-box-right">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>

							<div class="htga4-checkbox-switch">
								<?php if($settings[ 'enable_ecommerce_events'] && $this->is_pro_plugin_active()): ?>
								<input name="ht_easy_ga4_options[begin_checkout_event]" type="hidden" id="" value="0" />
								<input name="ht_easy_ga4_options[begin_checkout_event]" type="checkbox" id="htga4_begin_checkout_event" <?php checked( 'on', $settings[ 'begin_checkout_event'] ); ?> />
								<?php endif; ?>
								<label for="ht_easy_ga4_options[begin_checkout_event]">
									<span class="htga4-checkbox-switch-label on"><?php echo __('on', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-label off"><?php echo __('off', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-indicator"></span>
								</label>
							</div>
						</div>
					</div><!-- .htga4-grid-box -->

					<div class="htga4-grid-box">
                        <div class="htga4-grid-box-left">
                            <div class="htga4-grid-box-label">
                                <?php echo esc_html__( 'Purchase', 'ht-easy-ga4' ); ?>
                            </div>
                            <span class="htga4-show-info">
								<i class="dashicons dashicons-editor-help"></i>
								<span class="htga4-show-info-content"><span><?php echo __( 'Fire the <b>Purchase</b> event on the thank you page after checkout. Fires once per order.', 'ht-easy-ga4' ); ?></span></span>
                            </span>
                        </div>

						<div class="htga4-grid-box-right">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>

							<div class="htga4-checkbox-switch">
								<?php if($settings[ 'enable_ecommerce_events'] && $this->is_pro_plugin_active()): ?>
                                <input name="ht_easy_ga4_options[purchase_event]" type="hidden" id="" value="0" />
					            <input name="ht_easy_ga4_options[purchase_event]" type="checkbox" id="htga4_purchase_event" <?php checked( 'on', $settings[ 'purchase_event'] ); ?> />		<?php endif; ?>
								<label for="ht_easy_ga4_options[purchase_event]">
									<span class="htga4-checkbox-switch-label on"><?php echo __('on', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-label off"><?php echo __('off', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-indicator"></span>
								</label>
							</div>
						</div>
					</div><!-- .htga4-grid-box -->
				
				</div>

				<h2 class="htga4-section-heading"><?php _e('Video Events', 'ht-easy-ga4') ?></h2>
				<div class="htga4-grid-box-wrapper <?php echo esc_attr($pro_status_class) ?>">

					<div class="htga4-grid-box">
						<div class="htga4-grid-box-left">
                            <div class="htga4-grid-box-label">
								<?php echo esc_html__( 'Track Vimeo Videos', 'ht-easy-ga4' ); ?>
                            </div>
                            <span class="htga4-show-info">
								<i class="dashicons dashicons-editor-help"></i>
								<span class="htga4-show-info-content"><?php echo __( 'Track engagement for Vimeo videos. This helps you gain insights into how your audience interacts with your Vimeo Videos.', 'ht-easy-ga4' ); ?></span>
                            </span>
                        </div>

						<div class="htga4-grid-box-right">
						    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>

							<div class="htga4-checkbox-switch">
								<?php if($this->is_pro_plugin_active()): ?>
								<input name="ht_easy_ga4_options[vimeo_video_event]" type="hidden" id="" value="0" />
								<input name="ht_easy_ga4_options[vimeo_video_event]" type="checkbox" id="htga4_vimeo_video_event" <?php checked( 'on', $settings[ 'vimeo_video_event'] ); ?> />
								<?php endif; ?>
								<label for="ht_easy_ga4_options[vimeo_video_event]">
									<span class="htga4-checkbox-switch-label on"><?php echo __('on', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-label off"><?php echo __('off', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-indicator"></span>
								</label>
							</div>
						</div>
					</div><!-- .htga4-grid-box -->

					<div class="htga4-grid-box">
                        <div class="htga4-grid-box-left">
                            <div class="htga4-grid-box-label">
                                <?php echo esc_html__( 'Track Self Hosted Videos', 'ht-easy-ga4' ); ?>
                            </div>
                            <span class="htga4-show-info">
								<i class="dashicons dashicons-editor-help"></i>
								<span class="htga4-show-info-content"><?php echo __( 'Track engagement for Self Hosted videos. This helps you gain insights into how your audience interacts with your Self Hosted Videos.', 'ht-easy-ga4' ); ?></span>
                            </span>
                        </div>
						<div class="htga4-grid-box-right">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>

							<div class="htga4-checkbox-switch">
								<?php if($this->is_pro_plugin_active()): ?>
								<input name="ht_easy_ga4_options[self_hosted_video_event]" type="hidden" id="" value="0" />
								<input name="ht_easy_ga4_options[self_hosted_video_event]" type="checkbox" id="htga4_self_hosted_video_event" <?php checked( 'on', $settings[ 'self_hosted_video_event'] ); ?> />
								<?php endif; ?>
								<label for="ht_easy_ga4_options[self_hosted_video_event]">
									<span class="htga4-checkbox-switch-label on"><?php echo __('on', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-label off"><?php echo __('off', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-indicator"></span>
								</label>
							</div>
						</div>
					</div><!-- .htga4-grid-box -->
				
				</div>

				<h2 class="htga4-section-heading"><?php _e('Audio Event', 'ht-easy-ga4') ?></h2>
				<div class="htga4-grid-box-wrapper <?php echo esc_attr($pro_status_class) ?>">

					<div class="htga4-grid-box">
                        <div class="htga4-grid-box-left">
                            <div class="htga4-grid-box-label">
                                <?php echo esc_html__( 'Track Self Hosted Audios', 'ht-easy-ga4' ); ?>
                            </div>
                            <span class="htga4-show-info">
								<i class="dashicons dashicons-editor-help"></i>
								<span class="htga4-show-info-content"><?php echo __( 'Track engagement for Self Hosted Audios. This helps you gain insights into how your audience interacts with your Self Hosted Audios.', 'ht-easy-ga4' ); ?></span>
                            </span>
                        </div>

						<div class="htga4-grid-box-right">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="mdi-file-document-outline" width="25" height="25" viewBox="0 0 24 24" fill="#000000"><path d="M6,2A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2H6M6,4H13V9H18V20H6V4M8,12V14H16V12H8M8,16V18H13V16H8Z"/></svg>

							<div class="htga4-checkbox-switch">
								<?php if($this->is_pro_plugin_active()): ?>
								<input name="ht_easy_ga4_options[self_hosted_audio_event]" type="hidden" id="" value="0" />
								<input name="ht_easy_ga4_options[self_hosted_audio_event]" type="checkbox" id="htga4_self_hosted_audio_event" <?php checked( 'on', $settings[ 'self_hosted_audio_event'] ); ?> />
								<?php endif; ?>
								
								<label for="ht_easy_ga4_options[self_hosted_audio_event]">
									<span class="htga4-checkbox-switch-label on"><?php echo __('on', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-label off"><?php echo __('off', 'ht-easy-ga4') ?></span>
									<span class="htga4-checkbox-switch-indicator"></span>
								</label>
							</div>
						</div>
					</div><!-- .htga4-grid-box -->
				
				</div>

			</div>
			<div class="htga4-tab-content-right"></div>
		</div>
		<?php
		do_action('htga4_events_tracking_tab_content_after');
	}

	public function render_general_settings() {
		$email          = get_option( 'htga4_email' );
		$user_roles     = $this->get_roles_dropdown_options();
		$selected_roles = $this->get_option('exclude_roles', array());
		?>
		<table class="form-table" role="presentation">
			<tbody>

				<tr class="htga4_login">
					<th scope="row" style="width: 20%;">
						<?php echo esc_html__( 'Authentication with Google', 'ht-easy-ga4' ); ?>
					</th>
					<td>
					<?php 
						if ( ! $this->get_access_token() ) {
							printf(
								'<a class="button" href="%1$s" target="_blank">%2$s</a>',
								esc_url( $this->get_auth_url() ),
								esc_html__( 'Sign in with your Google Analytics account', 'ht-easy-ga4' )
							);
		
							echo '<br>';
							$this->render_login_notice( 'manually_set_tracking_id' );
							
						} else {
							printf(
								'<a class="button" href="%1$s">%2$s</a>',
								add_query_arg( 'htga4_logout', 'yes', add_query_arg( 'page', 'ht-easy-ga4-setting-page', get_admin_url( '', 'admin.php' ) ) ),
								sprintf( esc_html__( 'Logout (%s)', 'ht-easy-ga4' ), $email )
							);

							if( $this->analytics_data_permission === false ){
								echo '<br>';
								$this->render_login_notice( 'insufficient_permission' );
							}
						}
					?>
					</td>
				</tr>

				<?php if ( ! $this->get_access_token() ) : ?>
				<tr class="htga4-tracking-id">
					<?php $ht_easy_ga4_id = get_option( 'ht_easy_ga4_id' ) ? get_option( 'ht_easy_ga4_id' ) : ''; ?>
					<th scope="row" style="width: 20%;">
						<?php echo esc_html__( 'GA4 Tracking ID ', 'ht-easy-ga4' ); ?>
						<span><?php echo esc_html__( 'Sample Measurement ID:  G-08F1MTVENK', 'ht-easy-ga4' ); ?></span>
					</th>
					<td>
						<input type="text" id="ht_easy_ga4_id" placeholder="G-XXXXXXXXXX" name="ht_easy_ga4_id" value="<?php echo esc_attr( $ht_easy_ga4_id ); ?>"/>
						<p class="desc"><?php echo esc_html__( 'Manually add the GA4 Tracking / Measurement ID here.', 'ht-easy-ga4' ); ?></p>
					</td>
				</tr>
				<?php endif; ?>

				<?php if ( $this->get_access_token() ) : ?>
				<tr class="htga4-chosse-property">
					<th scope="row" style="width: 20%;">
						<?php echo esc_html__( 'Choose Property: ', 'ht-easy-ga4' ); ?>
						<span><?php echo esc_html__( 'Choose property from your Google Analytics account', 'ht-easy-ga4' ); ?></span>
					</th>
					<td>
						<input type="hidden" class="htga4_data_stream_id" name="ht_easy_ga4_options[data_stream_id]" value='<?php echo esc_attr( $this->get_option( 'data_stream_id' ) ); ?>'>
						<div class="htga4_accounts_wrapper">
							<span>
								<?php
									echo esc_html__( 'Select account', 'ht-easy-ga4' );

									$select_message  = esc_html__( 'Select account', 'ht-easy-ga4' );
								if ( ! empty( $this->accounts_result['error'] ) ) {
									$select_message = $this->accounts_result['error']['message'];
								}
								?>
								<select name="ht_easy_ga4_options[account]" id="" class="htga4-select-account">
									<option value=""><?php echo esc_html( $select_message ); ?></option>
									<?php
									if ( ! empty( $this->accounts_result['accounts'] ) ) {
										$accounts = $this->accounts_result['accounts'];
										foreach ( $accounts as $account ) {
											$account_id = substr( $account['name'], 9 );

											printf(
												'<option value="%1$s" %3$s>%2$s <%1$s></option>',
												$account_id,
												$account['displayName'],
												selected( $this->get_option( 'account' ), $account_id, false )
											);
										}
									}
									?>
								</select>
							</span>

							<span>
								<?php echo esc_html__( 'Select property', 'ht-easy-ga4' ); ?>
								<select name="ht_easy_ga4_options[property]" class="htga4-select-property" <?php echo ! $this->get_option( 'account' ) ? 'disabled' : ''; ?>>
									<option value=""><?php echo esc_html__( 'Select property', 'ht-easy-ga4' ); ?></option>
									<?php
									if ( $this->get_option( 'account' ) ) {
										$properties_result = $this->request_properties( $this->get_option( 'account' ) );

										if ( ! empty( $properties_result['error'] ) ) {
											echo esc_html( $properties_result['error']['message'] );
										} elseif ( is_array( $properties_result['properties'] ) ) {
											foreach ( $properties_result['properties'] as $property ) {
												$property_id = substr( $property['name'], 11 );

												printf(
													'<option value="%1$s" %3$s>%2$s <%1$s></option>',
													$property_id,
													$property['displayName'],
													selected( $this->get_option( 'property' ), $property_id, false )
												);
											}
										}
									}
									?>
								</select>
							</span>
						</div>
					</td>
				</tr><!-- Choose Property -->
				

				<?php if ( $this->active_tab != 'standard_reports' ) : ?>
				<tr>
					<th scope="row" style="width: 20%;">
						<?php echo esc_html__( 'Measurement ID: ', 'ht-easy-ga4' ); ?>
						<span><?php echo esc_html__( 'Select Measurement ID to start tracking & view reports.', 'ht-easy-ga4' ); ?></span>
					</th>
					<td>
						<select name="ht_easy_ga4_options[measurement_id]" class="htga4-select-measurement-id" <?php echo ! $this->get_option( 'property' ) ? 'disabled' : ''; ?>>                     
							<?php
							$select_message = esc_html__( 'Select measurement ID', 'ht-easy-ga4' );
							if ( $this->get_option( 'property' ) ) {
								$measurement_result = $this->request_data_streams( $this->get_option( 'property' ) );

								if ( ! empty( $measurement_result['error'] ) ) {
									$select_message = $measurement_result['error']['message'];
								}
							}
							?>

							<option value=""><?php echo esc_html( $select_message ); ?></option>
							<?php
							if ( $this->get_option( 'property' ) ) {
								$measurement_result = $this->request_data_streams( $this->get_option( 'property' ) );

								if ( ! empty( $measurement_result['dataStreams'] ) ) {
									foreach ( $measurement_result['dataStreams'] as $data_stream ) {
										if ( $data_stream['type'] === 'WEB_DATA_STREAM' ) {
											$data_stream_id = explode( '/', $data_stream['name'] );
											$data_stream_id = end( $data_stream_id );

											$measurement_id = $data_stream['webStreamData']['measurementId'];

											printf(
												'<option data-stream_id="%s" value="%s" %s>%s &#60;%s&#62;</option>',
												$data_stream_id,
												$measurement_id,
												selected( $this->get_option( 'measurement_id' ), $measurement_id, false ),
												$data_stream['displayName'],
												$measurement_id,
											);
										}
									}
								}
							}
							?>
						</select>
					</td>
				</tr> <!-- Measurement id -->
				<?php endif; ?> 

				<?php endif; ?>

				<tr class="htga4-exclude-tracking-for">
					<th scope="row" style="width: 20%;">
						<?php _e('Exclude Tracking For', 'ht-easy-ga4') ?> <span><?php _e('The users of the selected Role(s) will not be tracked', 'ht-easy-ga4') ?></span>
					</th>
					<td>
						<div class="htga4-select2-parent">
							<select id="ht_easy_ga4_options[exclude_roles]" name="ht_easy_ga4_options[exclude_roles][]" multiple data-placeholder="<?php echo __('Select Role', 'ht-easy-ga4') ?>">
							<?php
								foreach ($user_roles as $role_slug => $role_label) {
									$selected = in_array($role_slug, $selected_roles) ? 'selected' : '';
									$disabled = $role_slug === 'administrator' ? '' : 'disabled';
									
									if( $this->is_pro_plugin_active() ){
										echo "<option value='$role_slug' $selected>$role_label</option>";
									} else {
										echo "<option value='$role_slug' $selected $disabled>$role_label</option>";
									}
								}
							?>
							</select>
						</div>

						<?php if( !$this->is_pro_plugin_active() ): ?>
						<p class="desc"><?php _e('Excluding multiple roles together available in the <a href="https://hasthemes.com/plugins/google-analytics-plugin-for-wordpress?utm_source=wp-org&utm_medium=ht-ga4&utm_campaign=htga4_exclude_multiple_roles" target="_blank">Premium</a> version.', 'ht-easy-ga4') ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<?php do_action('htga4_general_options_before_tbody') ?>
			</tbody>
		</table>
		<?php
	}


	/**
	 * The function `render_login_notice` is used to display different login notices based on the provided
	 * notice name.
	 * 
	 * @param notice_name It can have two possible values: manually_set_tracking_id, insufficient_permission
	 * 
	 * @return void
	 */
	public function render_login_notice( $notice_name = '' ){
		$icon       = '<svg viewBox="64 64 896 896" focusable="false" data-icon="info-circle" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm32 664c0 4.4-3.6 8-8 8h-48c-4.4 0-8-3.6-8-8V456c0-4.4 3.6-8 8-8h48c4.4 0 8 3.6 8 8v272zm-32-344a48.01 48.01 0 010-96 48.01 48.01 0 010 96z"></path></svg>';
		$message    = '';
		$classes    = 'htga4-desc-info';
		$login_url  = $this->get_auth_url();
		$login_again_text = __('Sign in Again', 'ht-easy-ga4');

		if( $notice_name === 'manually_set_tracking_id' ){

			$message = __('To access analytical reports within your WordPress dashboard, you need to connect / authenticate with your Google Analytics account. <br>If you don\'t need to access the reports within the dashboard, <strong>manually insert your GA4 tracking ID below.</strong>', 'ht-easy-ga4');

			printf(
				'<div class="%1$s"><span>%2$s</span> <span>%3$s</span></div>',
				$classes,
				$icon,
				$message
			);

		} elseif( $notice_name === 'insufficient_permission' ){

			$message = __('Our system has detected that the access permissions you granted earlier was <b>insufficient</b>. <br>Please Sign in again and make sure that you have granted access for <strong>\'See and download your Google Analytics data\'</strong> on the Google Authentication screen to display analytical reports.', 'ht-easy-ga4');
			$classes .= ' htga4-warning';

			printf(
				'<div class="%1$s"><span>%2$s</span> <span>%3$s</span> <a href="%4$s" target="_blank">%5$s</a></div>',
				$classes,
				$icon,
				$message,
				$login_url,
				$login_again_text,
			);

		}
	}

	public function render_standard_reports() {
		$does_not_have_proper_api_info = ( ! $this->get_option( 'account' ) || ! $this->get_option( 'property' ) || ! $this->get_option( 'data_stream_id' ) );

		// @todo improve the notice.
		if( $this->analytics_data_permission === false  ){
			$this->render_login_notice( 'insufficient_permission');
			return;
		}

		if ( get_option( 'htga4_email' ) && $does_not_have_proper_api_info ) {
			echo '<div class="htga4-notice notice-warning"><p>' . esc_html__( 'Select the Account, Property and Measurement ID to display the reports from "General Options" tab.', 'ht-easy-ga4' ) . '</p></div>';
			return;
		} elseif ( ! get_option( 'htga4_email' ) && $does_not_have_proper_api_info ) {
			echo '<div class="htga4-notice notice-warning"><p>' . esc_html__( 'Sign in with your Google Analytics account to view the reports!', 'ht-easy-ga4' ) . '</p></div>';
			return;
		}

		if ( ! is_array( $this->reports ) ) {
			echo '<div class="htga4-notice notice-warning"><p>this->'. __('reports does not have proper data', 'ht-easy-ga4') .'</p></div>';
			return;
		}

		$last_7_days_url  = add_query_arg( 'date_range', 'last_7_days', $this->get_current_admin_url() );
		$last_15_days_url = add_query_arg( 'date_range', 'last_15_days', $this->get_current_admin_url() );
		$last_30_days_url = add_query_arg( 'date_range', 'last_30_days', $this->get_current_admin_url() );
		?>
		<div class="ht_easy_ga4_reports_head">
			<div class="ht_easy_ga4_reports_user_card">
				<?php
				try {
					$userinfo_request = $this->request_userinfo();

					$data_stream_request = array( 'displayName' => '' );
					if ( $this->get_option( 'property' ) && $this->get_option( 'measurement_id' ) ) {
						$data_stream_request = $this->request_data_stream( $this->get_option( 'property' ), $this->get_option( 'data_stream_id' ) );
					}
					?>
						<div class="ht_easy_ga4_reports_user_thumb">
							<img src="<?php echo esc_url( $userinfo_request['picture'] ); ?>" alt="">
						</div>
						<div class="ht_easy_ga4_reports_user_info">
							<h3><?php echo esc_html( $userinfo_request['name'] ); ?></h3>
							<p><?php echo esc_html( $userinfo_request['email'] ); ?></p>
							<p><?php echo esc_html( $data_stream_request['displayName'] ); ?> &#60;<?php echo esc_html( $this->get_option( 'measurement_id' ) ); ?>&#62;</p>
						</div>
						<?php
				} catch ( \Exception $e ) {
					printf( '<pre>%s: %s</pre>', $e->getCode(), $e->getMessage() );
				}
				?>
			</div>

			<div class="ht_easy_ga4_reports_toolbar">

				<div class="ht_easy_ga4_reports_filter">
					<a href="<?php echo esc_url( $last_7_days_url ); ?>" class="ht_easy_ga4_reports_filter_button <?php echo esc_attr( $this->get_current_class( 'last_7_days' ) ); ?>"><?php echo esc_html__( 'Last 7 days', 'ht-easy-ga4' ); ?></a>
					<a href="<?php echo esc_url( $last_15_days_url ); ?>" class="ht_easy_ga4_reports_filter_button <?php echo esc_attr( $this->get_current_class( 'last_15_days' ) ); ?>"><?php echo esc_html__( 'Last 15 days', 'ht-easy-ga4' ); ?></a>
					<a href="<?php echo esc_url( $last_30_days_url ); ?>" class="ht_easy_ga4_reports_filter_button <?php echo esc_attr( $this->get_current_class( 'last_30_days' ) ); ?>"><?php echo esc_html__( 'Last 30 days', 'ht-easy-ga4' ); ?></a>
					<button type="button" class="ht_easy_ga4_reports_filter_button ht_easy_ga4_reports_filter_custom_range <?php echo esc_attr( $this->get_current_class( 'custom' ) ); ?>"><?php echo esc_html__( 'Custom', 'ht-easy-ga4' ); ?></span>
				</div>

				<label for="ht_easy_ga4_reports_compare_field" class="ht_easy_ga4_reports_compare">
					<span class="ht_easy_ga4_reports_compare_icon">
						<input type="checkbox" id="ht_easy_ga4_reports_compare_field">
						<span class="ht_easy_ga4_reports_compare_field_icon"></span>
					</span>
					<span class="ht_easy_ga4_reports_compare_label"><?php echo esc_html__( 'Compare to previous period', 'ht-easy-ga4' ); ?></span>
				</label>

			</div>
		</div><!-- .ht_easy_ga4_reports_head -->

		<div class="ht_easy_ga4_reports_body">
			<div class="ht_easy_ga4_reoport_card_wrapper ht_easy_ga4_reoport_card_wrapper_column-3">
				<div class="ht_easy_ga4_report_card ht_session">
					<h2 class="ht_easy_ga4_report_card_title">
						<i class="dashicons dashicons-admin-users"></i>
						<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'Sessions', 'ht-easy-ga4' ); ?></span>
					</h2>
					<div class="ht_easy_ga4_report_card_head">
						<h3 class="ht_easy_ga4_report_card_head_count">000</h3>
						<div class="ht_easy_ga4_report_card_head_difference ht_easy_ga4_report_card_head_difference_positive">
							<i class="dashicons"></i>
							<p><span class="ht_growth_count">0%</span> <span><?php echo esc_html__( 'vs. previous period', 'ht-easy-ga4' ); ?></span></p>
						</div>
					</div>
					<div class="ht_easy_ga4_report_card_chart">
						<canvas id="sessions-chart"></canvas>
					</div>
				</div>
				<div class="ht_easy_ga4_report_card ht_pageview">
					<h2 class="ht_easy_ga4_report_card_title">
						<i class="dashicons dashicons-visibility"></i>
						<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'Page View', 'ht-easy-ga4' ); ?></span>
					</h2>
					<div class="ht_easy_ga4_report_card_head">
						<h3 class="ht_easy_ga4_report_card_head_count">000</h3>
						<div class="ht_easy_ga4_report_card_head_difference ht_easy_ga4_report_card_head_difference_negative">
							<i class="dashicons"></i>
							<p><span class="ht_growth_count">0%</span> <span><?php echo esc_html__( 'vs. previous period', 'ht-easy-ga4' ); ?></span></p>
						</div>
					</div>
					<div class="ht_easy_ga4_report_card_chart">
						<canvas id="page-view-chart"></canvas>
					</div>
				</div>
				<div class="ht_easy_ga4_report_card ht_bounce_rate">
					<h2 class="ht_easy_ga4_report_card_title">
						<i class="dashicons dashicons-arrow-down-alt"></i>
						<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'Bounce Rate', 'ht-easy-ga4' ); ?></span>
					</h2>
					<div class="ht_easy_ga4_report_card_head">
						<h3 class="ht_easy_ga4_report_card_head_count">000</h3>
						<div class="ht_easy_ga4_report_card_head_difference ht_easy_ga4_report_card_head_difference_negative">
							<i class="dashicons"></i>
							<p><span class="ht_growth_count">0%</span> <span><?php echo esc_html__( 'vs. previous period', 'ht-easy-ga4' ); ?></span></p>
						</div>
					</div>
					<div class="ht_easy_ga4_report_card_chart">
						<canvas id="page-view-chart2"></canvas>
					</div>
				</div>
			</div>

			<div class="ht_easy_ga4_reoport_card_wrapper ht_easy_ga4_reoport_card_wrapper_column-3">
				<div class="ht_easy_ga4_report_card ht_top_pages">
					<h2 class="ht_easy_ga4_report_card_title">
						<i class="dashicons dashicons-text-page"></i>
						<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'Top Pages', 'ht-easy-ga4' ); ?></span>
					</h2>
					<div class="ht_easy_ga4_report_card_list">
						<ul>
							<li>
								<span><b><?php echo esc_html__( 'Page Path', 'ht-easy-ga4' ); ?></b></span>
								<span><b><?php echo esc_html__( 'Page View', 'ht-easy-ga4' ); ?></b></span>
							</li>

							<?php foreach ( $this->reports['top_pages'] as $item ) : ?>
							<li>
								<span><?php echo esc_html( $item[1] ); ?></span>
								<span><?php echo esc_html( $item[0] ); ?></span>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>

				<div class="ht_easy_ga4_report_card ht_top_referrers">
					<h2 class="ht_easy_ga4_report_card_title">
						<i class="dashicons dashicons-controls-forward"></i>
						<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'Top Referrers', 'ht-easy-ga4' ); ?></span>
					</h2>
					<div class="ht_easy_ga4_report_card_list">
						<ul>
							<li>
								<span><b><?php echo esc_html__( 'Referrer', 'ht-easy-ga4' ); ?></b></span>
								<span><b><?php echo esc_html__( 'Session', 'ht-easy-ga4' ); ?></b></span>
							</li>
							<?php foreach ( $this->reports['top_referrers'] as $item ) : ?>
							<li>
								<span><?php echo esc_html( $item[1] ); ?></span>
								<span><?php echo esc_html( $item[0] ); ?></span>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>

				<div class="ht_easy_ga4_report_card ht_top_countries">
					<h2 class="ht_easy_ga4_report_card_title">
						<i class="dashicons dashicons-controls-forward"></i>
						<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'Top country’s', 'ht-easy-ga4' ); ?></span>
					</h2>
					<div class="ht_easy_ga4_report_card_list">
						<ul>
							<li>
								<span><b><?php echo esc_html__( 'Country', 'ht-easy-ga4' ); ?></b></span>
								<span><b><?php echo esc_html__( 'Session', 'ht-easy-ga4' ); ?></b></span>
							</li>
							<?php foreach ( $this->reports['top_countries'] as $item ) : ?>
							<li>
								<span><?php echo esc_html( $item[1] ); ?></span>
								<span><?php echo esc_html( $item[0] ); ?></span>
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>

			<div class="ht_easy_ga4_reoport_card_wrapper ht_easy_ga4_reoport_card_wrapper_column-3">
				<div class="ht_easy_ga4_report_card ht_user_type">
					<h2 class="ht_easy_ga4_report_card_title">
						<i class="dashicons dashicons-admin-users"></i>
						<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'User Types', 'ht-easy-ga4' ); ?></span>
					</h2>
					<div class="ht_easy_ga4_report_card_chart">
						<canvas id="user-types-chart"></canvas>
					</div>
				</div>

				<div class="ht_easy_ga4_report_card ht_device_type">
					<h2 class="ht_easy_ga4_report_card_title">
						<i class="dashicons dashicons-welcome-view-site"></i>
						<span class="ht_easy_ga4_report_card_title"><?php echo esc_html__( 'Device Types', 'ht-easy-ga4' ); ?></span>
					</h2>
					<div class="ht_easy_ga4_report_card_chart">
						<canvas id="device-types-chart"></canvas>
					</div>
				</div>					
			</div>
		</div><!-- .ht_easy_ga4_reports_body -->
		
		<?php
	}

	public function render_ecommerce_reports_free(){
		?>
		<div class="htga4_no_pro">
			<div class="htga4-notice notice-warning"> 
				<p><?php echo __( 'E-Commerce reports are available in the <a href="https://hasthemes.com/plugins/google-analytics-plugin-for-wordpress?utm_source=wp-org&utm_medium=ht-ga4&utm_campaign=htga4_tab_ecommerce_reports" target="_blank">Pro version</a>. <br> The reports displayed below with a blurred effect are just for demonstration purposes.', 'ht-easy-ga4' ); ?></p>
			</div>
			<img src="<?php echo esc_url(HT_EASY_GA4_URL . '/admin/assets/images/ecommerce-reports.jpeg') ?>" alt="">
		</div>
		<?php
	}

	/**
	 * Calculates and render the percentage growth.
	 * 
	 * @param previous_total
	 * @param current_total
	 * 
	 * @return void
	 */
	public function render_growth( $previous_total = 0, $current_total = 0 ){
		$growth = 0;
		$previous_total = round( $previous_total );
		$current_total 	= round( $current_total );

		if ( $previous_total > 0 ) {
			$growth = ( ( $current_total - $previous_total ) / $previous_total ) * 100;
		}

		$growth = round( $growth );

		$head_class = '';
		$icon_class = '';

		if ( $growth > 0 ) {
			$head_class = 'ht_easy_ga4_report_card_head_difference_positive';
			$icon_class = 'dashicons-arrow-up-alt';
		} elseif ( $growth < 0 ) {
			$head_class = 'ht_easy_ga4_report_card_head_difference_negative';
			$icon_class = 'dashicons-arrow-down-alt';
		}
		?>
		<h3 class="ht_easy_ga4_report_card_head_count"><?php echo esc_html( round($current_total) ) ?></h3>
		<div class="ht_easy_ga4_report_card_head_difference <?php echo esc_attr($head_class) ?>">
			<i class="dashicons <?php echo esc_attr($icon_class) ?>"></i>
			<p><span class="ht_growth_count"><?php echo esc_html($growth) ?>%</span> <span><?php echo esc_html__('vs. previous period', 'htga4-pro') ?></span></p>
		</div>
		<?php
	}

	public function insert_pro_notice_markup(){
		if( !$this->is_ga4_admin_screen() ){
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
				<div class="htga4_pro_adv_popup_icon"><img src="<?php echo esc_url(HT_EASY_GA4_URL . '/admin/assets/images/pro-badge.png') ?>" alt="pro"></div>
				<h2 class="htga4_pro_adv_popup_title"><?php echo esc_html__('BUY PRO', 'ht-easy-ga4') ?></h2>
				<p class="htga4_pro_adv_popup_text"><?php echo esc_html__('Our free version is great, but it doesn\'t have all our advanced features. The best way to unlock all of the features in our plugin is by purchasing the pro version.', 'ht-easy-ga4') ?></p>
				<a href="https://hasthemes.com/plugins/google-analytics-plugin-for-wordpress?utm_source=wp-org&utm_medium=ht-ga4&utm_campaign=htga4_buy_pro_popup" class="htga4_pro_adv_popup_button" target="_blank"><?php echo esc_html__('Buy Now', 'ht-easy-ga4') ?></a>
			</div>
		</div>		
		<?php
	}
  
	public function save_message() {
		if ( isset( $_GET['settings-updated'] ) ) {
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

	public function set_submenu_as_current_menu( $submenu_file, $parent_file ){
		$current_page = !empty($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
		$current_tab  = !empty($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
	
		if( ( $current_page == 'ht-easy-ga4-setting-page') && $current_tab == 'standard_reports' || $current_tab == 'ecommerce_reports' ){
			$submenu_file = 'ht-easy-ga4-setting-page&tab=standard_reports';
		}
		
		return $submenu_file;
	}

	public function ecommerce_events_option_update( $value, $old_value, $option ){
		$request_data = isset($_REQUEST['ht_easy_ga4_options']) ? wp_unslash($_REQUEST['ht_easy_ga4_options']) : array();

		if( !isset($request_data['enable_ecommerce_events']) ){
			$value['enable_ecommerce_events'] = '';
		}

		if( !isset($request_data['view_item_event']) ){
			$value['view_item_event'] = '';
		}

		if( !isset($request_data['view_item_list_event']) ){
			$value['view_item_list_event'] = '';
		}

		if( !isset($request_data['add_to_cart_event']) ){
			$value['add_to_cart_event'] = '';
		}

		if( !isset($request_data['begin_checkout_event']) ){
			$value['begin_checkout_event'] = '';
		}

		if( !isset($request_data['purchase_event']) ){
			$value['purchase_event'] = '';
		}

		return $value;
	}
}

new Ht_Easy_Ga4_Admin_Setting();

?>
