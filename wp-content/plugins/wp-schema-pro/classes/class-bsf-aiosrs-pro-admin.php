<?php
/**
 * Schema Pro Admin Init
 *
 * @package Schema Pro
 */

if ( ! class_exists( 'BSF_AIOSRS_Pro_Admin' ) ) {

	/**
	 * BSF_AIOSRS_Pro_Admin initial setup
	 *
	 * @since 1.0.0
	 */
	class BSF_AIOSRS_Pro_Admin {

		/**
		 * Class instance.
		 *
		 * @access private
		 * @var $instance Class instance.
		 */
		private static $instance;

		/**
		 * View all actions
		 *
		 * @since 1.0
		 * @var array $view_actions
		 */
		public static $view_actions = array();

		/**
		 * Pages
		 *
		 * @since 1.1.0
		 * @var array $pages
		 */
		public static $pages = array();

		/**
		 * Menu page title
		 *
		 * @since 1.0
		 * @var array $menu_page_title
		 */
		public static $menu_page_title = 'Schema Pro';

		/**
		 * Plugin slug
		 *
		 * @since 1.0
		 * @var array $plugin_slug
		 */
		public static $plugin_slug = 'aiosrs_pro_admin_menu_page';

		/**
		 * Default Menu position
		 *
		 * @since 1.0
		 * @var array $default_menu_position
		 */
		public static $default_menu_position = 'options-general.php';

		/**
		 * White label Branding array
		 *
		 * @since 1.3.0
		 * @var array $branding
		 */
		public static $branding = array();

		/**
		 * Parent Page Slug
		 *
		 * @since 1.0
		 * @var array $parent_page_slug
		 */
		public static $parent_page_slug = 'aiosrs-schema';

		/**
		 * Current Slug
		 *
		 * @since 1.0
		 * @var array $current_slug
		 */
		public static $current_slug = 'aiosrs-schema';

		/**
		 * Settings option.
		 *
		 * @since 1.0
		 * @var array $setting_option
		 */
		public static $setting_option = array();

		/**
		 * Custom Fields.
		 *
		 * @since 1.0
		 * @var array $meta_options
		 */
		public static $meta_options = '';

		/**
		 * Is Top Level Page.
		 *
		 * @since 1.0
		 * @var array $is_top_level_page
		 */
		public static $is_top_level_page = true;

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
		 * Constructor function.
		 */
		public function __construct() {

			$setting_options = BSF_AIOSRS_Pro_Helper::$settings['aiosrs-pro-settings'];
			if ( is_multisite() ) {
				self::$branding = get_site_option( 'wp-schema-pro-branding-settings' );
			} else {
				self::$branding = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
			}
			$settings = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-global-schemas'];
			if ( isset( $setting_options['menu-position'] ) && $setting_options['menu-position'] ) {
				self::$default_menu_position = $setting_options['menu-position'];
				self::$is_top_level_page     = in_array( $setting_options['menu-position'], array( 'top', 'middle', 'bottom' ), true );
			}

			add_action( 'init', array( $this, 'init' ) );

			add_action( 'admin_bar_menu', array( $this, 'admin_bar' ), 100 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );

			add_action( 'admin_head', array( $this, 'license_form' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_head', array( $this, 'menu_highlight' ) );

			if ( is_admin() ) {
				add_action( 'wp_redirect', array( $this, 'redirect_menu_position' ), 10, 2 );
				add_action( 'init', array( $this, 'init_admin_settings' ) );

				add_filter( 'wp_schema_pro_menu_options', array( $this, 'setting_menu_options' ) );
				add_action( 'aiosrs_menu_settings_action', array( $this, 'setting_page' ) );

				if ( '' === self::$branding['sp_hide_label'] || 'disabled' === self::$branding['sp_hide_label'] && false === ( defined( 'WP_SP_WL' ) && WP_SP_WL ) ) {
					add_filter( 'wp_schema_pro_menu_options', array( $this, 'branding_settings_options' ) );
					add_action( 'aiosrs_menu_branding_settings_action', array( $this, 'load_branding_setting_page' ) );
					update_option( 'sp_hide_label', true );
				}
				if ( '1' === $settings['breadcrumb'] ) {
					add_filter( 'wp_schema_pro_menu_options', array( $this, 'breadcrumb_settings_options' ) );
					add_action( 'aiosrs_menu_breadcrumb_settings_action', array( $this, 'load_breadcrumb_setting_page' ) );
				}
			}

			add_action( 'plugin_action_links_' . BSF_AIOSRS_PRO_BASE, array( $this, 'action_links' ) );

			/* Schema Redirect to Setup Wizard */
			add_action( 'admin_init', __CLASS__ . '::admin_redirects' );
			add_action( 'admin_init', __CLASS__ . '::white_label_admin_redirects' );

			/* Schema Setup Wizard */
			add_action( 'init', __CLASS__ . '::schema_wizard' );
			add_action( 'init', array( $this, 'save_settings' ) );
		}

		/**
		 * Initialize
		 *
		 * @since 1.1.0
		 * @return void
		 */
		public function init() {

			$all_pages = get_pages();
			if ( ! empty( $all_pages ) && is_array( $all_pages ) ) {
				foreach ( $all_pages as $page ) {
					self::$pages[ $page->ID ] = $page->post_title;
				}
			}
		}

		/**
		 * Save All admin settings here
		 */
		public function save_settings() {

			// Only admins can save settings.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( isset( $_POST['wp-schema-pro-white-label-nonce'] ) && wp_verify_nonce( $_POST['wp-schema-pro-white-label-nonce'], 'white-label' ) ) {

				$branding_options = BSF_AIOSRS_Pro_Helper::$settings['wp-schema-pro-branding-settings'];
				$input_settings   = array();
				$new_settings     = array();

				if ( isset( $_POST['wp-schema-pro-branding-settings'] ) ) {

					$input_settings = $_POST['wp-schema-pro-branding-settings'];

					// Loop through the input and sanitize each of the values.
					foreach ( $input_settings as $key => $val ) {

						if ( is_array( $val ) ) {
							foreach ( $val as $k => $v ) {
								$new_settings[ $key ][ $k ] = ( isset( $val[ $k ] ) ) ? sanitize_text_field( $v ) : '';
							}
						} else {
							$new_settings[ $key ] = ( isset( $input_settings[ $key ] ) ) ? sanitize_text_field( $val ) : '';
						}
					}

					$new_settings = wp_parse_args( $new_settings, $branding_options );
				}

				// Update the site-wide option since we're in the network admin.
				if ( is_multisite() ) {
					update_site_option( 'wp-schema-pro-branding-settings', $new_settings );
					update_site_option( 'hide_label', 'true' );
				} else {
					update_option( 'wp-schema-pro-branding-settings', $new_settings );
					update_option( 'hide_label', 'true' );
				}
			}
		}

		/**
		 * Redirect to astra page.
		 */
		public static function admin_redirects() {
			if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
				return;
			}
			global $pagenow;
			if ( 'post-new.php' === $pagenow && isset( $_GET['post_type'] ) && 'aiosrs-schema' === $_GET['post_type'] ) {

				wp_safe_redirect( admin_url( 'index.php?page=aiosrs-pro-setup' ) );
				exit;
			}
		}

		/**
		 * Redirect to main schema page when white label is activated.
		 */
		public static function white_label_admin_redirects() {
			if ( is_multisite() ) {
				if ( 'true' === get_site_option( 'hide_label' ) ) {
						global $pagenow;
					if ( 'options-general.php' === $pagenow ) {
						wp_safe_redirect( admin_url( 'options-general.php?page=' . self::$plugin_slug . '&action=aiosrs-schema' ) );
						delete_site_option( 'hide_label' );
					}
				}
			} else {
				if ( 'true' === get_option( 'hide_label' ) ) {
						global $pagenow;
					if ( 'options-general.php' === $pagenow ) {
						wp_safe_redirect( admin_url( 'options-general.php?page=' . self::$plugin_slug . '&action=aiosrs-schema' ) );
						delete_option( 'hide_label' );
					}
				}
			}
		}

		/**
		 * Include schema wizard
		 */
		public static function schema_wizard() {
			if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
				return;
			}
			// Setup/welcome.
			if ( isset( $_GET['page'] ) && 'aiosrs-pro-setup' === $_GET['page'] ) {
				include_once BSF_AIOSRS_PRO_DIR . 'classes/class-bsf-aiosrs-pro-schema-wizard.php';
			}
			if ( isset( $_GET['page'] ) && 'aiosrs-pro-setup-wizard' === $_GET['page'] ) {
				include_once BSF_AIOSRS_PRO_DIR . 'classes/class-bsf-aiosrs-pro-setup-wizard.php';
			}
		}

		/**
		 * Keep the Schema Pro menu open when editing the advanced headers.
		 * Highlights the wanted admin (sub-) menu items for the CPT.
		 *
		 * @since  1.0.0
		 */
		public function menu_highlight() {
			if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
				return;
			}
			global $parent_file, $submenu_file, $post_type;

			$screen = get_current_screen();

			$parent_page     = self::$default_menu_position;
			$setting_options = BSF_AIOSRS_Pro_Helper::$settings['aiosrs-pro-settings'];
			$menu_position   = isset( $setting_options['menu-position'] ) ? $setting_options['menu-position'] : $parent_page;

			$is_top_level_page = in_array( $menu_position, array( 'top', 'middle', 'bottom' ), true );

			if ( $is_top_level_page && isset( $_GET['page'] ) && self::$plugin_slug === $_GET['page'] ) {
				$parent_file = self::$plugin_slug; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				if ( isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ) {
					$submenu_file = esc_url(  // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						add_query_arg(
							array(
								'action' => $_GET['action'],
								'page'   =>
								self::$plugin_slug,
							),
							admin_url()
						)
					);
				} else {
					$submenu_file = self::$plugin_slug; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				}
			}
		}

		/**
		 * Register AIPRS Pro settings
		 *
		 * @return void
		 */
		public function register_settings() {

			register_setting( 'wp-schema-pro-general-settings-group', 'wp-schema-pro-general-settings' );
			register_setting( 'wp-schema-pro-social-profiles-group', 'wp-schema-pro-social-profiles' );
			register_setting( 'wp-schema-pro-global-schemas-group', 'wp-schema-pro-global-schemas' );
			register_setting( 'wp-schema-pro-breadcrumb-setting-group', 'wp-schema-pro-breadcrumb-setting' );
			register_setting( 'aiosrs-pro-settings-group', 'aiosrs-pro-settings' );
			register_setting( 'wp-schema-pro-branding-group', 'wp-schema-pro-branding-settings' );
			register_setting( 'wp-schema-pro-corporate-contact-group', 'wp-schema-pro-corporate-contact' );
		}

		/**
		 * Redirect to menu position.
		 *
		 * @param string $location URL.
		 * @param string $status URL status.
		 * @return string
		 */
		public function redirect_menu_position( $location, $status ) {

			$value         = get_option( 'aiosrs-pro-settings' );
			$current_parts = wp_parse_url( $location );
			if ( isset( $current_parts['query'] ) ) {
				parse_str( $current_parts['query'], $current_query );
			}

			if ( isset( $current_query['page'] ) && isset( $current_query['action'] ) && self::$plugin_slug === $current_query['page'] && 'settings' === $current_query['action'] ) {

				// Menu position.
				$menu_position     = isset( $value['menu-position'] ) ? $value['menu-position'] : self::$default_menu_position;
				$is_top_level_page = in_array( $menu_position, array( 'top', 'middle', 'bottom' ), true );

				// If menu is at top level.
				if ( $is_top_level_page ) {
					$url = admin_url( 'admin.php?page=' . self::$plugin_slug . '&action=settings' );
				} else {
					if ( strpos( $menu_position, '?' ) !== false ) {
						$query_var = '&page=' . self::$plugin_slug . '&action=settings';
					} else {
						$query_var = '?page=' . self::$plugin_slug . '&action=settings';
					}
					$url = admin_url( $menu_position . $query_var );
				}

				$new_parts = wp_parse_url( $url );
				parse_str( $new_parts['query'], $new_query );

				if ( $new_parts['path'] !== $current_parts['path'] || ( isset( $new_query['post_type'] ) && ! isset( $current_query['post_type'] ) ) || ( ! isset( $new_query['post_type'] ) && isset( $current_query['post_type'] ) ) || isset( $new_query['post_type'] ) !== isset( $current_query['post_type'] ) ) {
					$location = $url;
				}
			}

			return $location;
		}

		/**
		 * Get Admin Menu Positions.
		 *
		 * @since 1.1.0
		 * @return array
		 */
		public static function get_admin_menu_positions() {

			// Get list of current General entries.
			$entries = array();
			foreach ( $GLOBALS['menu'] as $entry ) {
				if ( false !== strpos( $entry[2], '.php' ) ) {
					$entries[ $entry[2] ] = $entry[0];
				}
			}

			// Remove <span> elements with notification bubbles (e.g. update or comment count).
			if ( isset( $entries['plugins.php'] ) ) {
				$entries['plugins.php'] = preg_replace( '/ <span.*span>/', '', $entries['plugins.php'] );
			}
			if ( isset( $entries['edit-comments.php'] ) ) {
				$entries['edit-comments.php'] = preg_replace( '/ <span.*span>/', '', $entries['edit-comments.php'] );
			}

			$entries['top']    = __( 'Top-Level (top)', 'wp-schema-pro' );
			$entries['middle'] = __( 'Top-Level (middle)', 'wp-schema-pro' );
			$entries['bottom'] = __( 'Top-Level (bottom)', 'wp-schema-pro' );

			return $entries;
		}

		/**
		 * Get Settings.
		 *
		 * @param  string $setting Option name.
		 * @return array
		 */


		/**
		 * Show action links on the plugin screen.
		 *
		 * @param   mixed $links Plugin Action links.
		 * @return  array
		 */
		public function action_links( $links ) {

			$admin_url    = self::get_page_url( self::$parent_page_slug );
			$action_links = array(
				'settings' => '<a href="' . esc_url( $admin_url ) . '" aria-label="' . esc_attr__( 'View Schema Pro settings', 'wp-schema-pro' ) . '">' . esc_html__( 'Settings', 'wp-schema-pro' ) . '</a>',
			);

			return array_merge( $action_links, $links );
		}

		/**
		 * Admin settings init
		 */
		public function init_admin_settings() {

			self::$menu_page_title = apply_filters( 'wp_schema_pro_menu_page_title', __( 'Schema Pro', 'wp-schema-pro' ) );

			add_action( 'admin_menu', array( $this, 'aiosrs_admin_menu_register' ) );
			add_action( 'admin_menu', array( $this, 'add_admin_menu_rename' ), 9999 );
		}

		/**
		 * Tooltip
		 *
		 * @since 1.1.0
		 * @param  string $message Tooltips message.
		 * @param  string $title Tooltips title.
		 * @return void
		 */
		public static function get_tooltip( $message = '', $title = '' ) {
			?>
			<div class="wp-schema-pro-tooltip-wrapper">
				<a class="dashicons dashicons-editor-help wp-schema-pro-tooltip-icon"></a>
				<div class="wp-schema-pro-tooltip-description">
					<?php if ( ! empty( $title ) ) { ?>
						<h2  class="wp-schema-pro-tooltip-heading"><?php echo esc_html( $title ); ?></h2>
					<?php } ?>
					<div class="wp-schema-pro-tooltip-content">
						<?php echo wp_kses_post( $message ); ?>
					</div>
					<span class="dashicons dashicons-arrow-down"></span>
				</div>
			</div>
			<?php
		}


		/**
		 * Registers a new settings page under Settings.
		 */
		public function aiosrs_admin_menu_register() {

			$parent_page       = self::$default_menu_position;
			$is_top_level_page = self::$is_top_level_page;

			self::$current_slug = str_replace( '-', '_', self::$parent_page_slug );

			if ( is_array( self::$branding ) && array_key_exists( 'sp_plugin_sname', self::$branding ) && ! empty( self::$branding['sp_plugin_sname'] ) ) {
					self::$menu_page_title = self::$branding['sp_plugin_sname'];
			}

			if ( $is_top_level_page ) {

				switch ( $parent_page ) {
					case 'top':
						$position = 3; // position of Dashboard + 1.
						break;
					case 'bottom':
						$position = ( ++$GLOBALS['_wp_last_utility_menu'] );
						break;
					case 'middle':
					default:
						$position = ( ++$GLOBALS['_wp_last_object_menu'] );
						break;
				}

				$main_page = add_menu_page(
					self::$menu_page_title,
					self::$menu_page_title,
					'manage_options',
					self::$plugin_slug,
					__CLASS__ . '::menu_callback',
					'dashicons-admin-site',
					$position
				);

			} else {
				add_submenu_page(
					$parent_page,
					self::$menu_page_title,
					self::$menu_page_title,
					'manage_options',
					self::$plugin_slug,
					__CLASS__ . '::menu_callback'
				);

			}
			$_REQUEST['wp_schema_pro_admin_page_nonce'] = wp_create_nonce( 'wp_schema_pro_admin_page' );
		}

		/**
		 * Function Name: add_admin_menu_rename.
		 * Function Description: add admin menu rename.
		 */
		public function add_admin_menu_rename() {
			if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
				return;
			}
			global $menu, $submenu;
			if ( isset( $submenu['aiosrs_pro_admin_menu_page'][0][0] ) ) {
				$submenu['aiosrs_pro_admin_menu_page'][0][0] = __( 'Schemas', 'wp-schema-pro' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}
		}

		/**
		 * Menu callback
		 *
		 * @since 1.0
		 */
		public static function menu_callback() {
			if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
				return;
			}
			$current_slug = isset( $_GET['action'] ) ? esc_attr( $_GET['action'] ) : self::$current_slug;

			$active_tab   = str_replace( '_', '-', $current_slug );
			$current_slug = str_replace( '-', '_', $current_slug );

			?>
			<div class="aiosrs-menu-page-wrapper">
				<?php self::init_nav_menu( $active_tab ); ?>
				<?php do_action( 'aiosrs_menu_' . esc_attr( $current_slug ) . '_action' ); ?>
			</div>
			<?php
		}

		/**
		 * View actions
		 */
		public static function get_view_actions() {

			if ( empty( self::$view_actions ) ) {

				$actions            = array();
				self::$view_actions = apply_filters( 'wp_schema_pro_menu_options', $actions );
			}

			return self::$view_actions;
		}

		/**
		 * Add extension option in menu page
		 *
		 * @param  array $actions Array of actions.
		 * @return array            Return the actions.
		 */
		public function setting_menu_options( $actions ) {

			$actions['settings'] = array(
				'label' => esc_html__( 'Configuration', 'wp-schema-pro' ),
				'show'  => ! is_network_admin(),
			);
			return $actions;
		}

		/**
		 * Add White Label option in menu page
		 *
		 * @param  array $actions Array of actions.
		 * @return array            Return the actions.
		 */
		public function branding_settings_options( $actions ) {

			$actions['branding-settings'] = array(
				'label' => esc_html__( 'White Label', 'wp-schema-pro' ),
				'show'  => ! is_network_admin(),
			);
			return $actions;
		}
		/**
		 * Add extension option in menu page
		 *
		 * @param  array $actions Array of actions.
		 * @return array            Return the actions.
		 */
		public function breadcrumb_settings_options( $actions ) {

			$actions['breadcrumb-settings'] = array(
				'label' => esc_html__( 'Breadcrumbs', 'wp-schema-pro' ),
				'show'  => ! is_network_admin(),
			);
			return $actions;
		}

		/**
		 * Init Nav Menu
		 *
		 * @param mixed $action Action name.
		 * @since 1.0
		 */
		public static function init_nav_menu( $action = '' ) {

			if ( '' !== $action ) {
				?>
				<div id="aiosrs-menu-page">
					<?php self::render( $action ); ?>
				</div>
				<?php
			}
		}

		/**
		 * Prints HTML content for tabs
		 *
		 * @param mixed $action Action name.
		 * @since 1.0
		 */
		public static function render( $action ) {
			if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
				return;
			}

			?>
			<div class="wrap">
				<h2> <?php echo esc_html( self::$menu_page_title ); ?>
				<span class="schema-version"><?php echo esc_html( 'Ver ' . BSF_AIOSRS_PRO_VER ); ?></span></h2>
			</div>
			<div class="nav-tab-wrapper">
				<?php
				$view_actions = self::get_view_actions();

				foreach ( $view_actions as $slug => $data ) {

					if ( ! $data['show'] ) {
						continue;
					}

					$url = self::get_page_url( $slug, $data );

					$active = ( $slug === $action ) ? 'nav-tab-active' : '';
					?>
						<a class='nav-tab <?php echo esc_attr( $active ); ?>' href='<?php echo esc_url( $url ); ?>'> <?php echo esc_html( $data['label'] ); ?> </a>
				<?php } ?>
			</div><!-- .nav-tab-wrapper -->

			<?php
			// Settings update message.
			if ( isset( $_REQUEST['message'] ) && ( 'saved' === $_REQUEST['message'] || 'saved_ext' === $_REQUEST['message'] ) ) {
				?>
					<span id="message" class="notice notice-success is-dismissive"><p> <?php esc_html_e( 'Settings saved successfully.', 'wp-schema-pro' ); ?> </p></span>
				<?php
			}

		}

		/**
		 * Get and return page URL
		 *
		 * @param string $menu_slug Menu name.
		 * @param mixed  $menu Menu data.
		 * @since 1.0
		 * @return  string page url
		 */
		public static function get_page_url( $menu_slug, $menu = false ) {

			$parent_page     = self::$default_menu_position;
			$setting_options = BSF_AIOSRS_Pro_Helper::$settings['aiosrs-pro-settings'];

			$menu_position = isset( $setting_options['menu-position'] ) ? $setting_options['menu-position'] : $parent_page;

			$chk_is_top_level_page = in_array( $menu_position, array( 'top', 'middle', 'bottom' ), true );

			if ( $chk_is_top_level_page ) {

				if ( $menu_slug === self::$parent_page_slug ) {
					$url = admin_url( 'admin.php?page=' . self::$plugin_slug );
				} else {
					$url = admin_url( 'admin.php?page=' . self::$plugin_slug . '&action=' . $menu_slug );
				}

				if ( false !== $menu && isset( $menu['link'] ) && false !== $menu['link'] ) {
					$url = $menu['link'];
				}
			} else {

				$parent_page = self::$default_menu_position;

				if ( strpos( $parent_page, '?' ) !== false ) {
					$query_var = '&page=' . self::$plugin_slug;
				} else {
					$query_var = '?page=' . self::$plugin_slug;
				}
				$parent_page_url = admin_url( $parent_page . $query_var );

				$url = $parent_page_url . '&action=' . $menu_slug;

			}
			return $url;
		}

		/**
		 * Load Scripts
		 *
		 * @since 1.0.0
		 *
		 * @param  string $hook Current Hook.
		 * @return void
		 */
		public function load_scripts( $hook = '' ) {

			if ( 'plugins.php' === $hook ) {
				wp_enqueue_style( 'aiosrs-pro-license-form', BSF_AIOSRS_PRO_URI . 'admin/assets/css/license-form-popup.css', array(), BSF_AIOSRS_PRO_VER, 'all' );
				wp_enqueue_script( 'aiosrs-pro-license-form', BSF_AIOSRS_PRO_URI . 'admin/assets/js/license-form-popup.js', array( 'jquery' ), BSF_AIOSRS_PRO_VER, true );
			}

		}

		/**
		 * License Form
		 *
		 * @since 1.0.0
		 *
		 * @return null If invalid screen ID.
		 */
		public function license_form() {
			if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
				return;
			}

			if ( ! isset( get_current_screen()->id ) ) {
				return;
			}

			if ( 'plugins' !== get_current_screen()->id ) {
				return;
			}

			require_once BSF_AIOSRS_PRO_DIR . 'template/license-form.php';

			if ( isset( $_GET['aiosrs-pro-license-form'] ) ) {
				?>
				<script type="text/javascript">
					jQuery( document ).ready( function() {
						setTimeout(function() {

							// Show Popup.
							jQuery('#aiosrs-pro-license-form').show();
							jQuery('body').addClass('aiosrs-pro-license-form-open');
						}, 800);
					});
				</script>
				<?php
			}

		}

		/**
		 * Enqueue required scripts
		 *
		 * @return void
		 */
		public function enqueue_script() {

			global $pagenow;
			global $post;

			$screen = get_current_screen();
			if ( isset( $_REQUEST['wp_schema_pro_admin_page_nonce'] ) && ! wp_verify_nonce( $_REQUEST['wp_schema_pro_admin_page_nonce'], 'wp_schema_pro_admin_page' ) ) {
				return;
			}

			if ( 'post-new.php' === $pagenow || 'post.php' === $pagenow ) {
				wp_enqueue_script( 'aiosrs-pro-field-edit-script', BSF_AIOSRS_PRO_URI . 'admin/assets/js/fields-script.js', array( 'jquery', 'jquery-ui-tooltip' ), BSF_AIOSRS_PRO_VER, true );
				wp_enqueue_style( 'aiosrs-pro-field-edit-style', BSF_AIOSRS_PRO_URI . 'admin/assets/css/fields-style.css', BSF_AIOSRS_PRO_VER, 'false' );
				wp_localize_script( 'aiosrs-pro-field-edit-script', 'AIOSRS_Rating', apply_filters( 'wp_schema_pro_field_edit_script_localize', array(), BSF_AIOSRS_PRO_VER, 'false' ) );

				if ( 'aiosrs-schema' === $screen->post_type ) {
					wp_enqueue_media();
					wp_enqueue_script( 'aiosrs-pro-admin-edit-script', BSF_AIOSRS_PRO_URI . 'admin/assets/js/script.js', array( 'jquery', 'jquery-ui-tooltip' ), BSF_AIOSRS_PRO_VER, true );
					wp_enqueue_style( 'aiosrs-pro-admin-edit-style', BSF_AIOSRS_PRO_URI . 'admin/assets/css/style.css', BSF_AIOSRS_PRO_VER, 'false' );
					wp_localize_script(
						'aiosrs-pro-admin-edit-script',
						'AIOSRS_Rating',
						apply_filters(
							'wp_schema_pro_field_admin_script_localize',
							array(
								'security'        => wp_create_nonce( 'schema_nonce' ),
								'specified_field' => wp_create_nonce( 'spec_schema' ),
							)
						)
					);
				}
			}

			if ( isset( $_GET['page'] ) && 'aiosrs_pro_admin_menu_page' === $_GET['page'] ) {
				wp_enqueue_style( 'aiosrs-pro-admin-style', BSF_AIOSRS_PRO_URI . 'admin/assets/css/settings-style.css', BSF_AIOSRS_PRO_VER, 'false' );
				wp_enqueue_media();
				wp_enqueue_script( 'aiosrs-pro-settings-script', BSF_AIOSRS_PRO_URI . 'admin/assets/js/settings-script.js', array( 'jquery' ), BSF_AIOSRS_PRO_VER, null, true );
			}
		}


		/**
		 * Admin bar menu
		 *
		 * @return void
		 */
		public function admin_bar() {

			$settings = BSF_AIOSRS_Pro_Helper::$settings['aiosrs-pro-settings'];
			if ( '1' === $settings['quick-test'] ) {

				global $wp_admin_bar;
				$http        = ( ! empty( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) ? 'https' : 'http';
				$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

				if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
					return;
				}

				if ( ! is_admin() ) {
					$wp_admin_bar->add_menu(
						array(
							'id'    => 'aiosrs',
							'title' => 'Test Schema',
							'href'  => 'https://search.google.com/structured-data/testing-tool#url=' . $actual_link,
							'meta'  => array(
								'target' => '_blank',
								'rel'    => 'noopener',
							),
						)
					);
				}
			}
		}

		/**
		 * Setting Page markup.
		 *
		 * @return void
		 */
		public function setting_page() {
			require_once BSF_AIOSRS_PRO_DIR . 'template/settings.php';
		}

		/**
		 * White Label setting Page markup.
		 *
		 * @return void
		 */
		public function load_branding_setting_page() {
			require_once BSF_AIOSRS_PRO_DIR . 'template/branding-settings.php';
		}
		/**
		 * Breadcrumb Setting Page markup.
		 *
		 * @return void
		 */
		public function load_breadcrumb_setting_page() {
			require_once BSF_AIOSRS_PRO_DIR . 'template/breadcrumb-settings.php';
		}
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
BSF_AIOSRS_Pro_Admin::get_instance();
