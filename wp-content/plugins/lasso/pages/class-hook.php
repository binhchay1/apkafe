<?php
/**
 * Lasso Url detail - Hook.
 *
 * @package Pages
 */

namespace Lasso\Pages;

use Lasso\Classes\Encrypt;
use Lasso\Classes\Enum;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Extend_Product as Lasso_Extend_Product;
use Lasso\Classes\Launch_Darkly as Lasso_Launch_Darkly;
use Lasso\Classes\Log as Lasso_Log;
use Lasso\Classes\Post_Type as Lasso_Post_Type;
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Setting_Enum as Lasso_Setting_Enum;
use Lasso\Classes\Table_Detail as Lasso_Table_Detail;

use Lasso\Libraries\Field\Lasso_Object_Field;

use Lasso\Models\Fields as Model_Field;
use Lasso\Models\Field_Mapping;

use Lasso_Affiliate_Link;
use Lasso_Amazon_Api;
use Lasso_Cron;
use Lasso_License;
use Lasso_DB;

/**
 * Lasso Url detail - Hook.
 */
class Hook {
	/**
	 * Current template
	 *
	 * @var string $current_template
	 */
	public $current_template = '';

	/**
	 * Declare "Lasso register hook events" to WordPress.
	 */
	public function register_hooks() {
		$lasso_setting   = new Lasso_Setting();
		$lasso_post_type = new Lasso_Post_Type();

		add_action( 'init', array( $this, 'lasso_custom_post_status' ) );
		add_action( 'admin_init', array( $this, 'redirect_admin_pages' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ), 2 );

		add_action( 'wp_head', array( $this, 'lasso_schema_markup_output' ) );
		add_action( 'wp_head', array( $this, 'lasso_custom_css' ) ); // ? frontend
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts_frontend' ) ); // ? frontend

		add_action( 'admin_head', array( $this, 'lasso_custom_css' ) ); // ? admin
		add_action( 'admin_enqueue_scripts', array( $this, 'add_styles' ) ); // ? admin
		add_action( 'admin_print_styles', array( $this, 'remove_non_lasso_styles' ) ); // ? admin
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) ); // ? admin
		add_action( 'admin_print_scripts', array( $this, 'remove_non_lasso_scripts' ) ); // ? admin

		add_filter( 'wp_revisions_to_keep', array( $this, 'lasso_revisions_to_keep' ), 10, 2 );

		// ? Move hook register from Lasso_Config
		add_filter( 'admin_footer_text', array( $this, 'admin_footer' ), 11, 1 );
		add_filter( 'update_footer', '__return_empty_string', 11 );

		// ? re-order submenu pages
		add_filter( 'custom_menu_order', array( $lasso_post_type, 'lasso_order_submenu' ) );
		add_filter( 'admin_title', array( $lasso_post_type, 'change_dashboard_title' ) );

		// ? Elementor
		add_action( 'elementor/init', array( $this, 'elementor_init' ) );

		add_action( Lasso_Setting_Enum::HOOK_FETCH_AMAZON_PRODUCT_API, array( $this, 'fetch_amazon_product_api' ) );

		// ? Track Lasso version realtime
		add_filter( 'body_class', array( $this, 'lasso_body_classes' ) );

		add_filter( 'auto_update_plugin', array( $this, 'disable_auto_update_specific_plugin' ), 10, 2 );

		// ? FIX: CONFLICT WITH OTHER PLUGINS
		// ? remove js files from other plugins in Lasso pages
		if ( $lasso_setting->is_lasso_page() || $lasso_setting->is_lasso_configured_page() || $lasso_setting->is_lasso_uninstall_page() ) {
			// @codeCoverageIgnoreStart
			// ? plugin: SEO Booster
			if ( class_exists( 'Seobooster2' ) ) {
				remove_action( 'admin_print_footer_scripts', array( 'Seobooster2', 'admin_print_footer_scripts' ) );
			}

			// ? plugin: Extended Post Status
			// ? Error line description: wp.i18n.setLocaleData({'Publish': ...
			if ( class_exists( 'Extended_Post_Status_Admin' ) ) {
				Lasso_Helper::remove_action( 'admin_print_footer_scripts', array( 'Extended_Post_Status_Admin', 'change_publish_button_gutenberg' ) );
			}

			// ? plugin: ShortPixel Adaptive Images
			if ( class_exists( 'ShortPixel\AI\Notice' ) && class_exists( 'ShortPixelAI' ) ) {
				$spai  = \ShortPixelAI::_();
				$spain = \ShortPixel\AI\Notice::_( $spai );
				remove_action( 'admin_footer', array( $spain, 'enqueueAdminScripts' ) );
				remove_action( 'admin_bar_menu', array( $spai, 'toolbar_styles' ), 999 );
			}

			// ? plugin: WZone - WooCommerce Amazon Affiliates (WooZone)
			if ( class_exists( 'WooZone' ) ) {
				global $WooZone; // phpcs:ignore
				remove_action( 'init', array( $WooZone, 'initThePlugin' ), 5 ); // phpcs:ignore
			}

			// ? plugin: Client Portal
			$lasso_page = $_GET['page'] ?? ''; // phpcs:ignore
			if ( class_exists( 'CCGClientPortal' ) && $lasso_page === $lasso_setting->dashboard_page ) {
				global $zohopwp; // phpcs:ignore
				remove_action( 'admin_menu', array( $zohopwp, 'ccgclientportal_admin_menu' ) ); // phpcs:ignore
			}

			if ( class_exists( 'wpe_admin_pointers' ) ) {
				global $wpe_admin_pointers; // phpcs:ignore
				remove_action( 'admin_enqueue_scripts', array( $wpe_admin_pointers, 'custom_admin_pointers_header' ) ); // phpcs:ignore
			}

			// ? plugin: tagDiv Composer
			if ( function_exists( 'td_change_backbone_js_hook' ) ) {
				remove_action( 'print_media_templates', 'td_change_backbone_js_hook' );
			}

			// ? plugin: ZipList Recipe Plugin
			if ( function_exists( 'amd_zlrecipe_js_vars' ) ) {
				remove_action( 'admin_head', 'amd_zlrecipe_js_vars' );
			}

			// ? plugin: Pretty Links Pro
			if ( class_exists( 'PrliUpdateController' ) ) {
				Lasso_Helper::remove_action( 'admin_notices', array( 'PrliUpdateController', 'activation_warning' ) );
			}

			// ? plugin: AdSense Integration WP QUADS
			if ( Lasso_Helper::is_adsense_integration_wp_quads_plugin_active() ) {
				add_action( 'init', array( $this, 'adsense_integration_wp_quads' ), 100 );
			}

			// ? plugin: WP RSS Aggregator - Feed to Post
			if ( Lasso_Helper::is_wp_rss_feed_to_post_plugin_active() ) {
				add_action( 'init', array( $this, 'wprss_admin_footer_after' ), 100 );
			}

			// ? plugin: Sitemap Generator
			if ( Lasso_Helper::is_sitemap_generator_plugin_actived() ) {
				remove_action( 'admin_head', 'ga_header' );
			}

			// ? plugin: OptimizePress
			if ( function_exists( 'op_include_files' ) ) {
				add_action( 'init', array( $this, 'op_include_files' ), 100 );
			}

			// ? plugin: Ocean Accelerator
			if ( function_exists( 'my_custom_css_p' ) ) {
				remove_action( 'admin_head', 'my_custom_css_p' );
			}

			// ? plugin: footnotes
			if ( class_exists( 'Footnotes_WYSIWYG' ) ) {
				remove_action( 'admin_print_footer_scripts', array( 'Footnotes_WYSIWYG', 'new_plain_text_editor_button' ) );
			}

			// ? fix js error in theme
			add_action( 'after_setup_theme', array( $this, 'fix_js_errors' ), 100 );
			// @codeCoverageIgnoreEnd

			if ( Lasso_Helper::is_earnist_plugin_loaded() ) {
				Lasso_Helper::remove_action( 'admin_print_footer_scripts', array( 'EarnistProductPicker', 'register_tinymce_quicktags' ) );
			}
			if ( Lasso_Helper::is_shortcode_start_rating_plugin_loaded() ) {
				global $ShortcodeStarRating; // phpcs:ignore
				remove_action( 'admin_print_footer_scripts', array( $ShortcodeStarRating, 'appthemes_add_quicktags' ) ); // phpcs:ignore
			}
			if ( Lasso_Helper::is_plugin_easy_table_of_contents_activated() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'remove_easy_table_of_content_action_admin_print_footer_scripts' ), 20 ); // ? admin
			}

			remove_all_actions( 'admin_footer' );
			if ( ! $lasso_setting->is_lasso_configured_page() ) {
				add_action( 'admin_footer', array( $this, 'lasso_print_media_templates' ) );
			}
			add_action( 'admin_footer', array( $this, 'lasso_organize_menu' ), 100 );

			remove_all_filters( 'script_loader_src' );

			// ? The wp_loaded action hook, which fires after WordPress has finished loading but before any headers are sent. This gives your code a chance to run after other plugins have been loaded.
			add_action( 'wp_loaded', array( $this, 'init_wp_loaded_for_lasso_page' ) );
		}

		// ? fix conflict with plugin Affiliate URL Automation
		if ( class_exists( 'AffiliateURLs' ) ) {
			global $AffiliateURLs; // phpcs:ignore
			remove_filter( 'the_content', array( &$AffiliateURLs, 'the_content' ), 12 ); // phpcs:ignore
		}

		// ? remove js files from other plugins in WP post/page pages
		if ( $lasso_setting->is_wordpress_post() ) {
			// @codeCoverageIgnoreStart
			if ( class_exists( 'WooZone' ) ) {
				global $WooZone; // phpcs:ignore
				remove_action( 'init', array( $WooZone, 'initThePlugin' ), 5 ); // phpcs:ignore
			}
			// @codeCoverageIgnoreEnd
		}

		// @codeCoverageIgnoreStart
		if ( Lasso_Helper::is_gravity_perks_plugin_active() ) {
			remove_action( 'admin_print_footer_scripts', array( 'GWPerks', 'welcome_pointer_script' ), 10 ); // phpcs:ignore
		}
		// @codeCoverageIgnoreEnd

		add_action( 'wp_footer', array( $this, 'lasso_event_tracking' ) );
		add_action( 'pmxi_saved_post', array( $this, 'pmxi_saved_post' ), 10, 3 );
	}

	/**
	 * Registering custom post status
	 */
	public function lasso_custom_post_status() {
		register_post_status(
			'lasso_delete',
			array(
				'label'                     => _x( 'Lasso Delete', 'post' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Lasso Delete <span class="count">(%s)</span>', 'Lasso Delete <span class="count">(%s)</span>' ), // phpcs:ignore
			)
		);
	}

	/**
	 * Redirect to admin pages

	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function redirect_admin_pages() {
		$get     = wp_unslash( $_GET ); // phpcs:ignore
		$request = wp_unslash( $_REQUEST ); // phpcs:ignore

		$lasso_setting = new Lasso_Setting();

		$in_lasso_admin = isset( $get['post_type'] ) && LASSO_POST_TYPE === $get['post_type'];
		if ( $in_lasso_admin || $lasso_setting->is_lasso_configured_page() ) {
			$page = $request['page'] ?? false;

			if ( ! $page ) {
				wp_redirect( 'edit.php?post_type=' . LASSO_POST_TYPE . '&page=dashboard' ); // phpcs:ignore
				exit;
			}

			if ( $lasso_setting->is_lasso_configured_page() ) {
				// ? Always check the license before we reload the install page
				list($license_status, $error_code, $error_message) = Lasso_License::check_license( Lasso_Setting::lasso_get_setting( 'license_serial', '' ) );

				// ? License is active
				if ( $license_status ) {
					wp_redirect( 'edit.php?post_type=' . LASSO_POST_TYPE . '&page=dashboard' ); // phpcs:ignore
					exit;
				}
			} else {
				// ? If not the install page, go from db cache
				$license_status = Lasso_License::get_license_status();
			}

			// ? License not active, send user to the install page
			if ( ! $license_status && ! $lasso_setting->is_lasso_configured_page() && ! $lasso_setting->is_lasso_uninstall_page() ) {
				wp_redirect( 'edit.php?post_type=' . LASSO_POST_TYPE . '&page=install' ); // phpcs:ignore
				exit;
			}
		}
	}

	/**
	 * Enable revisions
	 *
	 * @param int    $num  Number of revisions.
	 * @param object $post Post object.
	 */
	public function lasso_revisions_to_keep( $num, $post ) {
		$post_type = get_post_type( $post );
		// ? only keep revisions for supported custom post types
		if ( in_array( $post_type, Lasso_Helper::get_cpt_support(), true ) ) {
			return LASSO_REVISIONS_TO_KEEP;
		}

		return $num;
	}

	/**
	 * Add setting page in WP admin
	 */
	public function add_settings_page() {
		$this->redirect_admin_pages();

		$lasso_setting = new Lasso_Setting();

		$pages           = array();
		$configure_lasso = (object) array(
			'title'    => 'Configure',
			'slug'     => $lasso_setting->install_page,
			'template' => '/onboarding/index.php',
		);

		$uninstall_lasso = (object) array(
			'title'    => 'Uninstall',
			'slug'     => $lasso_setting->uninstall_page,
			'template' => '/settings/uninstall.php',
		);

		$license_status = Lasso_License::get_license_status();
		if ( ( $lasso_setting->is_lasso_configured_page() || $lasso_setting->is_lasso_page() || $lasso_setting->is_lasso_uninstall_page() )
			&& ! $license_status
		) {

			$license = Lasso_License::get_license();
			list($license_status, $error_code, $error_message) = Lasso_License::check_license( $license );
		}

		// ? only show this menu for deactivated installs
		if ( ! $license_status ) {
			$pages = array( $configure_lasso, $uninstall_lasso );
		} else {
			$pages = array(
				$configure_lasso,

				// ? Dashboard + Lasso URLs
				(object) array(
					'title'    => 'Dashboard',
					'slug'     => $lasso_setting->dashboard_page,
					'template' => '/dashboard/dashboard.php',
				),
				(object) array(
					'title'    => 'Add New URL',
					'slug'     => $lasso_setting->url_details_page,
					'template' => '/dashboard/url-details.php',
				),
				(object) array(
					'title'    => 'Links',
					'slug'     => $lasso_setting->url_links_page,
					'template' => '/dashboard/url-links.php',
				),
				(object) array(
					'title'    => 'Opportunities',
					'slug'     => $lasso_setting->url_opportunities_page,
					'template' => '/dashboard/url-opportunities.php',
				),

				// ? Groups
				(object) array(
					'title'    => 'Groups',
					'slug'     => $lasso_setting->groups_page,
					'template' => '/groups/groups.php',
				),
				(object) array(
					'title'    => 'Group Details',
					'slug'     => $lasso_setting->group_details_page,
					'template' => '/groups/group-details.php',
				),
				(object) array(
					'title'    => 'Group URLs',
					'slug'     => $lasso_setting->group_urls_page,
					'template' => '/groups/group-urls.php',
				),

				// ? Fields
				(object) array(
					'title'    => 'Fields',
					'slug'     => $lasso_setting->fields_page,
					'template' => '/fields/fields.php',
				),
				(object) array(
					'title'    => 'Field Details',
					'slug'     => $lasso_setting->field_details_page,
					'template' => '/fields/field-details.php',
				),
				(object) array(
					'title'    => 'Field URLs',
					'slug'     => $lasso_setting->field_urls_page,
					'template' => '/fields/field-urls.php',
				),

				// ? Opportunities
				(object) array(
					'title'    => 'Domain Opportunities',
					'slug'     => $lasso_setting->domain_opportunities,
					'template' => '/opportunities/domain.php',
				),
				(object) array(
					'title'    => 'Content Links',
					'slug'     => $lasso_setting->content_links_page,
					'template' => '/opportunities/content-links.php',
				),
				(object) array(
					'title'    => 'Domain Links',
					'slug'     => $lasso_setting->domain_links_page,
					'template' => '/opportunities/domain-links.php',
				),
				(object) array(
					'title'    => 'Link Opportunities',
					'slug'     => $lasso_setting->link_opportunities,
					'template' => '/opportunities/links.php',
				),
				(object) array(
					'title'    => 'Keyword Opportunities',
					'slug'     => $lasso_setting->keyword_opportunities,
					'template' => '/opportunities/keywords.php',
				),
				(object) array(
					'title'    => 'Program Opportunities',
					'slug'     => $lasso_setting->program_opportunities,
					'template' => '/opportunities/programs.php',
				),
				(object) array(
					'title'    => 'Content Opportunities',
					'slug'     => $lasso_setting->content_opportunities,
					'template' => '/opportunities/content.php',
				),

				// ? Settings
				(object) array(
					'title'    => 'Settings',
					'slug'     => $lasso_setting->settings_general_page,
					'template' => '/settings/general.php',
				),
				(object) array(
					'title'    => 'Settings - Display',
					'slug'     => $lasso_setting->settings_display_page,
					'template' => '/settings/display.php',
				),
				(object) array(
					'title'    => 'Settings - Amazon',
					'slug'     => $lasso_setting->settings_amazon_page,
					'template' => '/settings/amazon.php',
				),
				(object) array(
					'title'    => 'Settings - Import',
					'slug'     => $lasso_setting->import_page,
					'template' => '/import/import.php',
				),
				(object) array(
					'title'    => 'Settings - Logs',
					'slug'     => $lasso_setting->settings_logs_page,
					'template' => '/settings/logs.php',
				),
				(object) array(
					'title'    => 'Settings - DB Status',
					'slug'     => $lasso_setting->settings_db_status_page,
					'template' => '/settings/db-status.php',
				),
			);

			if ( Lasso_Launch_Darkly::enable_audit_log() ) {
				$post_content_history_pages = array(
					(object) array(
						'title'    => 'History - Post Content',
						'slug'     => $lasso_setting->post_content_history_page,
						'template' => '/history/post_content.php',
					),
					(object) array(
						'title'    => 'History Detail - Post Content',
						'slug'     => $lasso_setting->post_content_history_detail_page,
						'template' => '/history/post_content_detail.php',
					),
				);
				$pages                      = array_merge( $pages, $post_content_history_pages );
			}

			$comparision_table_pages = array(
				(object) array(
					'title'    => 'Tables',
					'slug'     => $lasso_setting->tables_page,
					'template' => '/tables/tables.php',
				),
				(object) array(
					'title'    => 'Table Details',
					'slug'     => $lasso_setting->table_details_page,
					'template' => '/tables/table-details.php',
				),
			);
			$pages                   = array_merge( $pages, $comparision_table_pages );
		}

		$this->build_lasso_pages( $pages );
	}

	/**
	 * Build Lasso pages
	 *
	 * @param array $pages List of pages.
	 */
	public function build_lasso_pages( $pages ) {
		$get      = wp_unslash( $_GET ); // phpcs:ignore
		$get_page = $get['page'] ?? '';

		$lasso_setting = new Lasso_Setting();

		$lasso_permission = Lasso_Setting::lasso_get_setting( 'lasso_permission' );

		// ? Only check permissions if not Admin
		if ( ! current_user_can( 'manage_options' ) ) {
			$lasso_role = 'read';
			if ( 'Administrator' === $lasso_permission ) {
				$lasso_role = 'manage_access';
			} elseif ( 'Editor' === $lasso_permission ) {
				$lasso_role = 'edit_others_posts';
			} elseif ( 'Author' === $lasso_permission ) {
				$lasso_role = 'publish_posts';
			} elseif ( 'Subscriber' === $lasso_permission ) {
				$lasso_role = 'edit_posts';
			} elseif ( 'Contributor' === $lasso_permission ) {
				$lasso_role = 'read';
			}
			$lasso_setting->lasso_permission_level = $lasso_role;
		}

		if ( ! current_user_can( $lasso_setting->lasso_permission_level ) ) {
			remove_menu_page( 'edit.php?post_type=' . LASSO_POST_TYPE );
			return;
		}

		foreach ( $pages as $page ) {
			if ( $get_page === $page->slug ) {
				$this->current_template = $page->template;
			}

			add_submenu_page(
				'edit.php?post_type=' . LASSO_POST_TYPE,
				$page->title,
				$page->title,
				$lasso_setting->lasso_permission_level,
				$page->slug,
				array( $this, 'render_html' )
			);
		}
	}

	/**
	 * Render html for pages
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function render_html() {
		include_once LASSO_PLUGIN_PATH . '/admin/views' . $this->current_template;
	}

	/**
	 * Prints the templates used in the media manager.
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function lasso_print_media_templates() {
		wp_print_media_templates();
	}

	/**
	 * Move "New Affiliate Link" to the second position
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function lasso_organize_menu() {
		$lasso_setting = new Lasso_Setting();
		$setting_page  = $lasso_setting->is_lasso_setting_page();
		?>
		<script>
			jQuery(document).ready(function(){
				var lasso_menu = jQuery('#menu-posts-lasso-urls');
				if(lasso_menu.is('.wp-has-current-submenu, .wp-menu-open')){
					var submenu = lasso_menu.find('ul.wp-submenu').find('li');
					if(submenu != undefined) { 
						if(!submenu.hasClass('current') || submenu.hasClass('current').length == 0) {
							submenu.eq(1).addClass('current');
						}

						// add class `current` for Settings menu
						if('<?php echo (int) $setting_page; ?>' == '1') {
							submenu.removeClass('current');
							lasso_menu.find('ul.wp-submenu').find('li:contains("Settings")').addClass('current');
						}
					}
				}
			});
		</script>
		<?php
	}

	/**
	 * DISPLAYS CSS FOR FRONTEND OF SITE
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function add_scripts_frontend() {
		Lasso_Helper::enqueue_style( 'lasso-live', 'lasso-live.min.css' );
		Lasso_Helper::enqueue_style( 'lasso-table-frontend', 'lasso-table-frontend.min.css' );
	}

	/**
	 * DISPLAYS CUSTOM CSS FOR FRONTEND OF SITE
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function lasso_custom_css() {
		$settings = Lasso_Setting::lasso_get_settings();

		// @codingStandardsIgnoreStart
		echo '<style type="text/css">
			:root{
				--lasso-main: ' . $settings['display_color_main'] . ' !important;
				--lasso-title: ' . $settings['display_color_title'] . ' !important;
				--lasso-button: ' . $settings['display_color_button'] . ' !important;
				--lasso-secondary-button: ' . $settings['display_color_secondary_button'] . ' !important;
				--lasso-button-text: ' . $settings['display_color_button_text'] . ' !important;
				--lasso-background: ' . $settings['display_color_background'] . ' !important;
				--lasso-pros: ' . $settings['display_color_pros'] . ' !important;
				--lasso-cons: ' . $settings['display_color_cons'] . ' !important;
			}
			
			' . $settings['custom_css_default'] . '
		</style>';

		// fix fontawesome js render svg (from other plugins) instead of using css
		$fontawesome_js_svg = $settings['fontawesome_js_svg'];
		echo '
			<script type="text/javascript">
				// Notice how this gets configured before we load Font Awesome
				let lassoFontAwesomeJS = "' . $fontawesome_js_svg . '" == 1
				console.log("lassoFontAwesomeJS", lassoFontAwesomeJS)
				window.FontAwesomeConfig = { autoReplaceSvg: lassoFontAwesomeJS }
			</script>
		';
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Load css files
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function add_styles() {
		$setting = new Lasso_Setting();

		$page = Lasso_Helper::get_page_name();
		// @codingStandardsIgnoreStart
		Lasso_Helper::enqueue_style( 'lasso-live', 'lasso-live.min.css' );

		// ? Everywhere in the Lasso post-type (add/edit/reports/settings/wizard)
		if ( $setting->is_lasso_page() || $setting->is_lasso_configured_page() ) {
			Lasso_Helper::enqueue_style( 'bootstrap-css', 'bootstrap.min.css' );
			Lasso_Helper::enqueue_style( 'bootstrap-select-css', 'bootstrap-select.min.css' );
			Lasso_Helper::enqueue_style( 'simple-panigation-css', 'simplePagination.css' );
			Lasso_Helper::enqueue_style( 'select2-css', 'select2.min.css' );
			wp_enqueue_style( 'wp-color-picker' );
		}

		// ? LOAD LASSO DISPLAY MODAL CSS ON POST AND PAGE EDIT ONLY
		if ( $setting->is_wordpress_post() || $setting->is_custom_post() ) {
			Lasso_Helper::enqueue_style( 'bootstrap-grid-css', 'bootstrap-grid.min.css' );
			Lasso_Helper::enqueue_style( 'lasso-display-modal', 'lasso-display-modal.css' );
			Lasso_Helper::enqueue_style( 'simple-pagination', 'simplePagination.css' );
			Lasso_Helper::enqueue_style( 'lasso-quill', 'quill.snow.css' );
			Lasso_Helper::enqueue_style( 'lasso-table-frontend', 'lasso-table-frontend.min.css' );
		}

		if ( $setting->is_lasso_dashboard_page() || $setting->is_lasso_configured_page() ) {
			Lasso_Helper::enqueue_style( 'lasso-dashboard', 'lasso-dashboard.css' );
			Lasso_Helper::enqueue_style( 'lasso-dashboard-grid', 'lasso-dashboard-grid.css' );
			Lasso_Helper::enqueue_style( 'lasso-live', 'lasso-live.min.css' );
			Lasso_Helper::enqueue_style( 'lasso-quill', 'quill.snow.css' );
		}
		
		if ( $setting->is_lasso_configured_page() || $setting->is_lasso_setting_page() ) {
			Lasso_Helper::enqueue_style( 'spectrum', 'spectrum.min.css' );
		}

		if ( $setting->is_lasso_uninstall_page() ) {
			Lasso_Helper::enqueue_style( 'lasso-dashboard', 'lasso-dashboard.css' );
		}

		if ( $setting->is_lasso_page() && in_array( $page, array( Lasso_Setting_Enum::PAGE_TABLES, Lasso_Setting_Enum::PAGE_TABLE_DETAILS ), true )  ) {
			Lasso_Helper::enqueue_style( 'lasso-tables', 'lasso-tables.css' );
			Lasso_Helper::enqueue_style( 'lasso-table-frontend', 'lasso-table-frontend.min.css' );
		}

		if ( $setting->is_lasso_opportunities_content_page() || $setting->is_lasso_opportunities_keyword_page() ) {
			Lasso_Helper::enqueue_style( 'lasso-display-modal', 'lasso-display-modal.css' );
		}

		if ( $setting->is_wordpress_post() && Lasso_Helper::is_classic_editor()) {
			Lasso_Helper::enqueue_style( 'lasso-display-classic-editor', 'lasso-display-classic-editor.css' );
		}
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Remove non Lasso js files in the Lasso pages
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function remove_non_lasso_styles() {
		global $wp_styles;

		$setting = new Lasso_Setting();

		if ( $setting->is_lasso_page() ) {
			$css_allowed_in_lasso = (array) Lasso_Setting::$css_allowed_in_lasso;
			$css_files            = $wp_styles->queue;
			foreach ( $css_files as $css ) {
				if ( ! in_array( $css, $css_allowed_in_lasso, true ) ) {
					wp_dequeue_style( $css );
				}
			}
		}
	}

	/**
	 * Remove non Lasso js files in the Lasso pages
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function remove_non_lasso_scripts() {
		global $wp_scripts;

		$setting = new Lasso_Setting();

		if ( $setting->is_lasso_page() || $setting->is_lasso_configured_page() ) {
			$js_allowed_in_lasso = (array) Lasso_Setting::$js_allowed_in_lasso;
			$js_files            = $wp_scripts->queue;
			foreach ( $js_files as $js ) {
				if ( ! in_array( $js, $js_allowed_in_lasso, true ) ) {
					wp_dequeue_script( $js );
				}
			}
		}

		if ( $setting->is_custom_post() || $setting->is_wordpress_post() ) {
			$js_allowed_in_editor = array(
				'jquery-modal',
			);
			$js_files             = $wp_scripts->queue;
			foreach ( $js_files as $js ) {
				if ( in_array( $js, $js_allowed_in_editor, true ) ) {
					wp_dequeue_script( $js );
				}
			}
		}
	}

	/**
	 * Load js files
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function add_scripts() {
		$get       = wp_unslash( $_GET ); // phpcs:ignore
		$post_type = $get['post_type'] ?? false;
		$page      = $get['page'] ?? false;

		$setting = new Lasso_Setting();

		$data_passed_to_js = array(
			'registerNonce'              => wp_create_nonce( 'lasso_registration' ),
			'optionsNonce'               => wp_create_nonce( 'lasso_settings_save' ),
			'ajax_url'                   => admin_url( 'admin-ajax.php' ),
			'site_url'                   => site_url(),

			'lasso_settings_general_url' => Lasso_Setting::get_lasso_page_url( $setting->settings_general_page ),

			'loading_image'              => LASSO_PLUGIN_URL . '/admin/assets/images/lasso-icon.svg',
			'plugin_url'                 => LASSO_PLUGIN_URL,
			'customizing_display'        => wp_json_encode( LASSO_LINK_CUSTOMIZE_DISPLAY ),
			'schema_display'             => wp_json_encode( LASSO_LINK_SCHEMA_DISPLAY ),
			'segment_analytic_id'        => LASSO_SEGMENT_ANALYTIC_ID,

			'display_type_single'        => Lasso_Setting::DISPLAY_TYPE_SINGLE,
			'display_type_grid'          => Lasso_Setting::DISPLAY_TYPE_GRID,
			'display_type_list'          => Lasso_Setting::DISPLAY_TYPE_LIST,

			'app_id'                     => LASSO_INTERCOM_APP_ID,

			'amazon_tracking_id_regex'   => Lasso_Amazon_Api::TRACKING_ID_REGEX,
			'icon_brag'                  => LASSO_PLUGIN_URL . 'admin/assets/images/lasso-icon-brag.png',
			'is_wc_plugin_activate'      => Lasso_Helper::is_woody_code_plugin_loaded() ? 1 : 0,
		);

		if ( LASSO_POST_TYPE === $post_type ) {
			wp_dequeue_script( 'up_admin_script' ); // ? fix js conflict with plugin: Download plugin
		}

		// ? plugin: ReactPress
		if ( class_exists( 'ReactPress\User\User' ) && class_exists( 'ReactPress\Includes\Core' ) ) {
			Lasso_Helper::enqueue_script( 'lasso-js', 'lasso.js', array( 'jquery' ) );
		}

		if ( $setting->is_lasso_page() ) {
			wp_enqueue_script( 'jquery-migrate' ); // ? fix jQuery(...).live is not a function
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'wp-color-picker' );

			Lasso_Helper::enqueue_script( 'popper-js', 'popper.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'bootstrap-js', 'bootstrap.min.js', array( 'jquery', 'popper-js' ) );
			Lasso_Helper::enqueue_script( 'lasso-helper', 'lasso-helper.js', array( 'jquery', 'bootstrap-js' ) );
			wp_localize_script( 'lasso-helper', 'lassoOptionsData', $data_passed_to_js );

			if ( ! $setting->is_lasso_configured_page() && ! $setting->is_lasso_uninstall_page() ) {
				Lasso_Helper::enqueue_script( 'lasso-page', 'lasso-page.js', array( 'jquery', 'lasso-helper' ), true );
			}
		}

		// @codingStandardsIgnoreStart
		// ? LOAD LASSO DISPLAY MODAL JS ON WordPress POSTS AND PAGES ONLY
		if ( $setting->is_wordpress_post() || $setting->is_custom_post() ) {
			wp_enqueue_script( 'jquery' );
			wp_dequeue_script( 'bootstrap' );

			Lasso_Helper::enqueue_script( 'popper-js', 'popper.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'bootstrap-js', 'bootstrap.min.js', array( 'jquery', 'popper-js' ) );
			Lasso_Helper::enqueue_script( 'bootstrap-select-js', 'bootstrap-select.min.js', array( 'jquery', 'bootstrap-js' ) );
			Lasso_Helper::enqueue_script( 'pagination-js', 'jquery.simplePagination.js', array( 'jquery' ) );

			Lasso_Helper::enqueue_script( 'lasso-icons', 'fontawesome.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'lasso-icons-regular', 'regular.min.js', array( 'jquery' ) );
		}

		if ( $setting->is_lasso_dashboard_page() || $setting->is_lasso_configured_page() ) {
			Lasso_Helper::enqueue_script( 'lasso-icons', 'fontawesome.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'lasso-icons-regular', 'regular.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'lasso-icons-brands', 'brands.min.js', array( 'jquery' ) );
		}

		// ? Add js for configure lasso
		if ( $setting->is_lasso_configured_page() ||  $setting->is_lasso_setting_page() ) {
			Lasso_Helper::enqueue_script( 'spectrum-js', 'spectrum.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'moment-js', 'moment.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'select2-js', 'select2.full.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'pagination-js', 'jquery.simplePagination.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'popper-js', 'popper.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'bootstrap-js', 'bootstrap.min.js', array( 'jquery', 'popper-js' ) );
			Lasso_Helper::enqueue_script( 'bootstrap-select-js', 'bootstrap-select.min.js', array( 'jquery', 'bootstrap-js' ) );
			Lasso_Helper::enqueue_script( 'lasso-helper', 'lasso-helper.js', array( 'jquery', 'bootstrap-js' ) );
			Lasso_Helper::enqueue_script( LASSO_POST_TYPE . '-js', 'settings.js', array( 'jquery', 'lasso-helper', 'bootstrap-js' ) );
			Lasso_Helper::enqueue_script( 'lasso-onboarding', 'onboarding.js', array( 'jquery', LASSO_POST_TYPE . '-js' ) );
			
			wp_localize_script( LASSO_POST_TYPE . '-js', 'lassoOptionsData', $data_passed_to_js );
		}

		if ( $setting->is_general_setting_page() ) {
			Lasso_Helper::enqueue_script( 'setting-general', 'setting-general.js', array( 'jquery', 'lasso-helper' ) );
		}

		if ( $setting->is_wordpress_post() || $setting->is_lasso_page() || $setting->is_custom_post() ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-effects-core' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-tooltip' );
			Lasso_Helper::enqueue_script( 'lasso-quill', 'quill.min.js' );
			wp_enqueue_media();
			Lasso_Helper::enqueue_script( 'moment-js', 'moment.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'select2-js', 'select2.full.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( LASSO_POST_TYPE . '-jq-auto-complete-js', 'jquery-autocomplete.js' );

			Lasso_Helper::enqueue_script( 'popper-js', 'popper.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'bootstrap-js', 'bootstrap.min.js', array( 'jquery', 'popper-js' ) );
			Lasso_Helper::enqueue_script( 'bootstrap-select-js', 'bootstrap-select.min.js', array( 'jquery', 'bootstrap-js' ) );
			Lasso_Helper::enqueue_script( 'pagination-js', 'jquery.simplePagination.js', array( 'jquery' ) );

			Lasso_Helper::enqueue_script( 'lasso-helper', 'lasso-helper.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( LASSO_POST_TYPE . '-js', 'settings.js', array( 'jquery', 'lasso-helper' ) );
			wp_localize_script( LASSO_POST_TYPE . '-js', 'lassoOptionsData', $data_passed_to_js );
		}

		if ($setting->is_wordpress_post() ) {
			// ? Get current post
			$post                          = get_post();
			$lasso_url_details_schema_data = self::get_lasso_urls_schema_data( $post );
			// ? Pass variable Lasso URLs details schema data to js in edit post page
			wp_localize_script( LASSO_POST_TYPE . '-js', 'lassoUrlDetailsSchemaData', $lasso_url_details_schema_data );
		}

		// ? settings import page
		if ( $setting->is_lasso_import_page() || $setting->is_lasso_configured_page() ) {
			Lasso_Helper::enqueue_script( 'lasso-import', 'settings-import.js', array( 'jquery' ) );
		}

		if ( $setting->is_edit_lasso_post_page() ) {
			wp_enqueue_script( 'quicktags' );

			Lasso_Helper::enqueue_script( 'popper-js', 'popper.min.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'edit-lasso', 'edit-lasso.js', array( 'jquery', 'popper-js', 'bootstrap-js', 'bootstrap-select-js' ) );
		}

		if ( $setting->is_add_lasso_post_page() ) {
			Lasso_Helper::enqueue_script( 'custom-slug', 'custom-slug.js', array( 'jquery' ) );
			wp_localize_script(
				'custom-slug',
				'lassocustomSlugData',
				array(
					'site_url' => site_url(),
				)
			);
		}

		if ( $setting->is_lasso_page() ) {
			if ( in_array( $page, array( 'link-opportunities', 'url-opportunities', 'keyword-opportunities', 'content-links', 'domain-links', 'url-links', Lasso_Setting_Enum::PAGE_GROUP_URLS ), true ) ) {
				Lasso_Helper::enqueue_script( 'lasso-popup-monetize', 'lasso-monetize-modal.js', array() );
			}

			if ( Lasso_Setting_Enum::PAGE_GROUP_URLS === $page ) {
				Lasso_Helper::enqueue_script( 'group-urls', 'group-urls.js' );
			}

			if ( in_array( $page, array( Lasso_Setting_Enum::PAGE_TABLES, Lasso_Setting_Enum::PAGE_TABLE_DETAILS ), true ) ) {
				Lasso_Helper::enqueue_script( 'lasso-tables', 'lasso-tables.js', array( 'jquery' ) );
			}

			if ( Lasso_Setting_Enum::PAGE_TABLE_DETAILS === $page ) {
				Lasso_Helper::enqueue_script( 'lasso-table-product-link', 'table-product-link.js', array( 'jquery' ) );
				wp_localize_script(
					'lasso-table-product-link',
					'lassoTableData',
					array(
						'image_field_id'         => Lasso_Object_Field::IMAGE_FIELD_ID,
						'vertical_product_limit' => Lasso_Table_Detail::VERTICAL_DISPLAY_ITEM_LIMIT,
					)
				);
			}
		}

		if ( $setting->is_wordpress_post() && Lasso_Setting::lasso_get_setting( Lasso_Setting_Enum::SEGMENT_ANALYTICS, true ) ) {
			Lasso_Helper::enqueue_script( 'lasso-post-edit-segment-analytic', 'lasso-post-edit-segment-analytic.js' );
		}

		if ( $setting->is_lasso_post_content_history_pages() ) {
			Lasso_Helper::enqueue_script( 'lasso-history-js', 'lasso-history.js', array( 'jquery' ) );
		}

		if ( $setting->is_lasso_post_content_history_detail_page() ) {
			wp_enqueue_script( 'google-diff_match_patch-js', LASSO_PLUGIN_URL . 'libs/google-diff_match_patch/javascript/diff_match_patch.js' );
		}

		if ( $setting->is_lasso_opportunities_content_page() || $setting->is_lasso_opportunities_keyword_page() ) {
			Lasso_Helper::enqueue_script( 'lasso-helper', 'lasso-helper.js', array( 'jquery' ) );
			Lasso_Helper::enqueue_script( 'lasso-display-modal', 'lasso-display-modal.js' );
		}
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Register lasso widget
	 *
	 * @param object $widgets_manager  Widget manager.
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function register_widget_lasso_shortcode( $widgets_manager ) {
		require_once LASSO_PLUGIN_PATH . '/libs/elementor/widgets/lasso-shortcode.php';
		$widgets_manager->register( new \Widget_Lasso_Shortcode() );

	}

	/**
	 * Elementor load css
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function elementor_editor_styles() {
		Lasso_Helper::enqueue_style( 'lasso-elementor', 'lasso-elementor.css' );
	}

	/**
	 * Remove action "admin_print_footer_scripts" of "Easy Table of Contents" plugin
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function remove_easy_table_of_content_action_admin_print_footer_scripts() {
		Lasso_Helper::remove_action( 'admin_print_footer_scripts', array( 'eztoc_pointers', 'admin_print_footer_scripts' ) );
	}

	/**
	 * Elementor Init Hook
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function elementor_init() {
		if ( Lasso_Helper::is_wp_elementor_plugin_actived() ) {
			if ( class_exists( 'Elementor\Widget_Base' ) && class_exists( 'Elementor\Controls_Manager' ) ) {
				add_action( 'elementor/widgets/register', array( $this, 'register_widget_lasso_shortcode' ) );
				add_action( 'elementor/editor/before_enqueue_styles', array( $this, 'elementor_editor_styles' ) );
			}

			// ? Move the scan post into the  "elementor/document/after_save" action
			add_action( 'elementor/document/after_save', array( $this, 'after_elementor_document_save' ), 10, 1 );
		}
	}

	/**
	 * OptimizePress Init Hook
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function op_include_files() {
		if ( class_exists( 'OptimizePress_Admin_Init' ) ) {
			Lasso_Helper::remove_action( 'admin_enqueue_scripts', array( 'OptimizePress_Admin_Init', 'print_scripts' ) );
		}
	}

	/**
	 * Move the scan post into the  "elementor/document/after_save" action
	 *
	 * @param object $document Elementor document.
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function after_elementor_document_save( $document ) {
		$post       = $document->get_post();
		$lasso_cron = new Lasso_Cron();
		$lasso_cron->check_all_posts_pages( array( get_post( $post->ID ) ) );
	}

	/**
	 * When Amazon API fetches a product
	 *
	 * @param array $product Amazon product.
	 */
	public function fetch_amazon_product_api( $product ) {
		if ( ! is_array( $product ) ) {
			return;
		}

		$url  = isset( $product['url'] ) ? Lasso_Amazon_Api::get_amazon_product_url( $product['url'], false ) : ''; // ? Remove "tag" parameter from url;
		$data = array(
			'url'             => $url,
			'name'            => $product['title'] ?? '',
			'image'           => $product['image'] ?? '',
			'price'           => $product['price'] ?? '',
			'quantity'        => $product['quantity'] ?? '',
			'features'        => $product['features'] ?? array(),
			'categories'      => $product['categories'] ?? array(),
			'savings_amount'  => $product['savings_amount'] ?? '',
			'savings_percent' => $product['savings_percent'] ?? '',
			'savings_basis'   => $product['savings_basis'] ?? '',
		);

		// ? Do not send the empty price or empty quantity item
		if ( empty( $data['price'] ) || 0 === intval( $data['quantity'] ) ) {
			return;
		}

		$headers = Lasso_Helper::get_lasso_headers();
		$url     = LASSO_LINK . '/link/amazon';
		$body    = Encrypt::encrypt_aes( $data );
		$res     = Lasso_Helper::send_request( 'put', $url, $body, $headers );

		return $res;
	}

	/**
	 * Update the text in the footer
	 *
	 * @param string $text Text.
	 *
	 * @codeCoverageIgnore Coverage ignore.
	 */
	public function admin_footer( $text ) {
		global $current_screen;

		$lasso_setting = new Lasso_Setting();

		if ( ! empty( $current_screen->id ) && $lasso_setting->is_lasso_page() ) {
			$url  = Enum::LASSO_REVIEW_URL;
			$text = sprintf(
				wp_kses(
					'Enjoying %1$s? Please rate <a href="%2$s" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%3$s" target="_blank" rel="noopener">Trustpilot</a> to help us spread the word. Thanks from the Lasso team!',
					array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
							'rel'    => array(),
						),
					)
				),
				'<strong>Lasso</strong>',
				$url,
				$url
			);
		}

		return $text;
	}

	/**
	 * Track Lasso version realtime
	 *
	 * @param array $classes classes.
	 */
	public function lasso_body_classes( $classes ) {
		$classes[] = 'lasso-v' . LASSO_VERSION;

		return $classes;
	}

	/**
	 * Schema output JSON-LD for custom attribute shortcode
	 *
	 * @return void
	 */
	public function lasso_schema_markup_output() {
		// ? Get current post
		$post = get_post();
		if ( is_null( $post ) ) {
			return;
		}

		$content = $post->post_content;
		preg_match_all(
			'/' . get_shortcode_regex( array( 'lasso' ) ) . '/s',
			$content,
			$matches,
			PREG_SET_ORDER
		);

		$is_render_json_ld               = false;
		$is_render_json_ld_schema_review = false;
		$is_render_json_ld_pros_cons     = false;
		$image                           = '';
		$schema_pros_list                = array();
		$schema_cons_list                = array();
		$schema_price_currency           = 'USD';
		$schema_review_author            = get_the_author_meta( 'display_name', $post->post_author );

		foreach ( $matches as $match ) {
			if ( strpos( $match[3], 'u0022' ) !== false ) {
				continue;
			}

			$atts    = shortcode_parse_atts( $match[3] );
			$post_id = $atts['id'] ?? '';

			if ( ! $post_id ) {
				continue;
			}

			$schema_review    = $atts['schema_review'] ?? false;
			$schema_review    = 'enable' === $schema_review;
			$schema_pros_cons = $atts['schema_pros_cons'] ?? false;
			$schema_pros_cons = 'enable' === $schema_pros_cons;

			if ( $schema_review || $schema_pros_cons ) {
				$is_render_json_ld = true;
			} else {
				continue;
			}

			$lasso_url = Lasso_Affiliate_Link::get_lasso_url( $post_id );

			if ( ! $lasso_url->lasso_id ) {
				continue;
			}

			$schema_price          = $atts['schema_price'] ?? '';
			$schema_price_currency = $atts['schema_price_currency'] ?? $lasso_url->currency;

			// ? Get fields
			$lasso_db    = new Lasso_DB();
			$fields      = $lasso_db->get_fields_by_lasso_id( $post_id );
			$schema_pros = Lasso_Helper::get_root_pros_cons_value( $fields, Model_Field::PROS_FIELD_ID );
			$schema_cons = Lasso_Helper::get_root_pros_cons_value( $fields, Model_Field::CONS_FIELD_ID );
			$image       = $lasso_url->image_src;

			if ( $schema_review ) {
				$is_render_json_ld_schema_review = true;
			}

			if ( $schema_pros_cons ) {
				$is_render_json_ld_pros_cons = true;

				$schema_pros_values = explode( '|||', $schema_pros );
				foreach ( $schema_pros_values as $pros ) {
					if ( ! empty( $pros ) ) {
						$schema_pros_list[] = $pros;
					}
				}

				$schema_cons_values = explode( '|||', $schema_cons );
				foreach ( $schema_cons_values as $cons ) {
					if ( ! empty( $cons ) ) {
						$schema_cons_list[] = $cons;
					}
				}
			}

			// ? Only render one Lasso's shortcode.
			break;
		}

		if ( ! $is_render_json_ld || ! isset( $lasso_url ) ) {
			return;
		}

		$schema = array(
			'@context' => 'http://schema.org/',
			'@type'    => 'Product',
			'name'     => $lasso_url->name,
			'image'    => array(
				'@context' => 'https://schema.org',
				'@type'    => 'ImageObject',
				'url'      => $image,
			),
		);

		$review = array(
			'@context' => 'https://schema.org',
			'@type'    => 'Review',
			'author'   => array(
				'@type' => 'Person',
				'name'  => $schema_review_author,
			),
		);

		if ( $is_render_json_ld_schema_review ) {
			if ( '' !== $schema_price ) {
				$lasso_url->price = $schema_price;
			}

			$price        = (float) str_replace( '$', '', $lasso_url->price );
			$price        = number_format( $price, 2, '.', '' );
			$rating_value = $lasso_url->fields->primary_rating ? $lasso_url->fields->primary_rating->field_value : false;
			$rating       = $rating_value ? Lasso_Helper::show_decimal_field_rate( $rating_value ) : 0;

			if ( (int) $rating > 0 ) {
				$review['reviewRating'] = array(
					'@context'    => 'https://schema.org',
					'@type'       => 'Rating',
					'ratingValue' => $rating,
				);
			}

			$offers = array(
				'@type'         => 'Offer',
				'price'         => $price,
				'priceCurrency' => $schema_price_currency,
				'availability'  => 'https://schema.org/InStock',
			);

			$schema['offers'] = $offers;
			$schema['review'] = $review;
		}

		if ( $is_render_json_ld_pros_cons ) {
			$pros_item_list_element = Lasso_Helper::get_pros_cons_items( $schema_pros_list );
			$cons_item_list_element = Lasso_Helper::get_pros_cons_items( $schema_cons_list );

			if ( ! empty( $pros_item_list_element ) ) {
				$review['positiveNotes'] = array(
					'@context'        => 'https://schema.org',
					'@type'           => 'ItemList',
					'numberOfItems'   => count( $pros_item_list_element ),
					'itemListElement' => $pros_item_list_element,
				);
			}

			if ( ! empty( $cons_item_list_element ) ) {
				$review['negativeNotes'] = array(
					'@context'        => 'https://schema.org',
					'@type'           => 'ItemList',
					'numberOfItems'   => count( $cons_item_list_element ),
					'itemListElement' => $cons_item_list_element,
				);
			}

			$schema['review'] = $review;
		}

		// ? Render JSON LD output once enable option Review Schema or Schema Pros Cons
		echo PHP_EOL;
		echo '<script type="application/ld+json" class="lasso-schema-markup-output">';
		echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES );
		echo '</script>';
		echo PHP_EOL;
	}

	/**
	 * Prepare data Lasso urls details schema in post
	 *
	 * @param object $post WP Post.
	 *
	 * @return array
	 */
	public static function get_lasso_urls_schema_data( $post ) {
		$results = array(
			'lasso_id_using_schema'  => null,
			'lasso_urls_schema_data' => array(),
			'post_author'            => 'admin',
			'root_fields_id'         => array(
				'primary_rating' => Model_Field::RATING_FIELD_ID,
				'pros'           => Model_Field::PROS_FIELD_ID,
				'cons'           => Model_Field::CONS_FIELD_ID,
			),
		);

		if ( ! $post ) {
			return $results;
		}

		try {
			$results['post_author'] = get_the_author_meta( 'display_name', $post->post_author );
			$content                = $post->post_content;
			$lasso_id_using_schema  = null;

			preg_match_all(
				'/' . get_shortcode_regex( array( 'lasso' ) ) . '/s',
				$content,
				$matches,
				PREG_SET_ORDER
			);

			foreach ( $matches as $match ) {
				if ( strpos( $match[3], 'u0022' ) !== false ) {
					continue;
				}

				$atts             = shortcode_parse_atts( $match[3] );
				$post_id          = $atts['id'] ?? '';
				$schema_review    = $atts['schema_review'] ?? false;
				$schema_review    = 'enable' === $schema_review;
				$schema_pros_cons = $atts['schema_pros_cons'] ?? false;
				$schema_pros_cons = 'enable' === $schema_pros_cons;

				if ( '' === $post_id ) {
					continue;
				}

				if ( $schema_review || $schema_pros_cons && ! $lasso_id_using_schema ) {
					$lasso_id_using_schema = $post_id;
				}

				$schema                                        = Lasso_Helper::get_schema_info_by_lasso_atts( $atts );
				$results['lasso_urls_schema_data'][ $post_id ] = $schema;
			}

			$results['lasso_id_using_schema'] = $lasso_id_using_schema;
			$results['root_fields_id']        = array(
				'primary_rating' => Model_Field::RATING_FIELD_ID,
				'pros'           => Model_Field::PROS_FIELD_ID,
				'cons'           => Model_Field::CONS_FIELD_ID,
			);
		} catch ( \Exception $e ) {
			Lasso_Helper::write_log( 'Error: ' . $e->getMessage(), Lasso_Log::ERROR_LOG, false, true );
		}

		return $results;
	}

	/**
	 * Fix: Can't find variable: quads
	 */
	public function adsense_integration_wp_quads() {
		remove_action( 'admin_print_footer_scripts', 'wp_quads_quick_tag', 100 );
	}

	/**
	 * Fix: Cannot read properties of undefined (reading 'f2p')
	 */
	public function wprss_admin_footer_after() {
		Lasso_Helper::remove_action( 'wprss_admin_footer_after', 'admin_footer' );
	}

	/**
	 * Print JS script for Lasso event tracking.
	 */
	public function lasso_event_tracking() {
		$process_lasso_event_tracking = apply_filters( 'lasso_event_tracking', true );

		if ( ! $process_lasso_event_tracking ) {
			return;
		}

		$lasso_options       = Lasso_Setting::lasso_get_settings();
		$tracking_ids        = Lasso_Setting::get_ga_tracking_ids();
		$is_ip_anonymization = Lasso_Helper::cast_to_boolean( $lasso_options['analytics_enable_ip_anonymization'] );
		$send_pageview       = Lasso_Helper::cast_to_boolean( $lasso_options['analytics_enable_send_pageview'] );
		$is_startup_plan     = Lasso_License::is_startup_plan();
		$lsid                = Lasso_Helper::build_lsid();

		$current_date    = gmdate( 'Ymd' );
		$js_version      = LASSO_VERSION . '.' . $current_date;
		$performance_url = 'https://js.getlasso.co/lasso-performance.min.js?ver=' . $js_version;

		// @codeCoverageIgnoreStart
		if ( $lasso_options['analytics_enable_click_tracking'] && ! empty( $tracking_ids ) ) :
			?>
			<!-- Google tag (gtag.js) -->
			<script type="text/javascript" src="https://www.googletagmanager.com/gtag/js?id=<?php echo $tracking_ids[0]; // phpcs:ignore ?>" defer></script>
			<script type="text/javascript" defer>
				window.dataLayer = window.dataLayer || [];
				function gtag(){dataLayer.push(arguments);}
				gtag('js', new Date());

				// ? multiple tracking ids
				<?php foreach ( $tracking_ids as $tracking_id ) : ?>
					gtag('config', '<?php echo $tracking_id; // phpcs:ignore ?>', {
						'send_page_view': Boolean(<?php echo $send_pageview; // phpcs:ignore ?>),
						'anonymize_ip': Boolean(<?php echo $is_ip_anonymization; // phpcs:ignore ?>)
					});
				<?php endforeach; ?>
			</script>

			<!-- Lasso tracking events - Performance -->
			<script type="text/javascript" src="<?php echo $performance_url; // phpcs:ignore ?>" defer></script>
			<script type="text/javascript" defer>
				document.addEventListener("DOMContentLoaded", function() {
					let lasso_event = setInterval(() => {
						if (typeof LassoEvents !== 'undefined') {
							clearInterval(lasso_event);
							LassoEvents.init({
								'lssid': '<?php echo Lasso_License::get_site_id(); // phpcs:ignore ?>',
								'lsid': '<?php echo $lsid; // phpcs:ignore ?>',
								'pid': '<?php echo get_the_ID(); // phpcs:ignore ?>',
								'ipa': '<?php echo $is_ip_anonymization; // phpcs:ignore ?>',
								'performance': '<?php echo $lasso_options['performance_event_tracking'] || $is_startup_plan ? 1 : 0; // phpcs:ignore ?>',
							});
						}
					}, 200);
				});
			</script>
			<?php
		elseif ( $lasso_options['performance_event_tracking'] || $is_startup_plan ) :
			?>
			<!-- Lasso tracking events - Performance -->
			<script type="text/javascript" src="<?php echo $performance_url; // phpcs:ignore ?>" defer></script>
			<script type="text/javascript" defer>
				document.addEventListener("DOMContentLoaded", function() {
					let lasso_event = setInterval(() => {
						if (typeof LassoEvents !== 'undefined') {
							clearInterval(lasso_event);
							LassoEvents.init({
								'lssid': '<?php echo Lasso_License::get_site_id(); // phpcs:ignore ?>',
								'lsid': '<?php echo $lsid; // phpcs:ignore ?>',
								'pid': '<?php echo get_the_ID(); // phpcs:ignore ?>',
								'ipa': '<?php echo $is_ip_anonymization; // phpcs:ignore ?>',
								'performance': '1',
							});
						}
					}, 200);
				});
			</script>
			<?php
		endif;
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Update data in url_detail table.
	 * https://www.wpallimport.com/documentation/action-reference/#pmxi_saved_post
	 *
	 * @param int              $post_id   Post id.
	 * @param SimpleXMLElement $xml_node  The libxml resource of the current XML element.
	 * @param bool             $is_update Returns 0 for new item 1 for updated item.
	 */
	public function pmxi_saved_post( $post_id, $xml_node, $is_update ) {
		$lasso_db = new Lasso_DB();

		// ? Convert SimpleXml object to array for easier use.
		$record = json_decode( json_encode( ( array ) $xml_node ), 1 ); // phpcs:ignore

		$lasso_custom_redirect   = $record['lasso_custom_redirect'] ?? get_post_meta( $post_id, 'lasso_custom_redirect', true );
		$url_detail_redirect_url = $lasso_custom_redirect;
		$is_opportunity          = 0;
		$affiliate_homepage      = Lasso_Helper::get_base_domain( $url_detail_redirect_url );

		$amazon_product_id   = Lasso_Amazon_Api::get_product_id_by_url( $url_detail_redirect_url );
		$extend_product_url  = Lasso_Extend_Product::url_to_get_product_id( $url_detail_redirect_url, $url_detail_redirect_url );
		$extend_product_type = Lasso_Extend_Product::get_extend_product_type_from_url( $extend_product_url );
		$extend_product_id   = Lasso_Extend_Product::get_extend_product_id_by_url( $extend_product_url );

		// ? Update Lasso URL Details
		$product_id_col   = $amazon_product_id
			? $amazon_product_id
			: ( $extend_product_id ? $extend_product_id : '' );
		$product_type_col = Lasso_Amazon_Api::is_amazon_url( $url_detail_redirect_url )
			? Lasso_Amazon_Api::PRODUCT_TYPE
			: ( $extend_product_type ? $extend_product_type : '' );

		$lasso_db->update_url_details( $post_id, $url_detail_redirect_url, $affiliate_homepage, $is_opportunity, $product_id_col, $product_type_col );

		// ? Add Fields
		$allow_field_types = array(
			Model_Field::FIELD_TYPE_TEXT,
			Model_Field::FIELD_TYPE_TEXT_AREA,
			Model_Field::FIELD_TYPE_NUMBER,
			Model_Field::FIELD_TYPE_RATING,
			Model_Field::FIELD_TYPE_BULLETED_LIST,
			Model_Field::FIELD_TYPE_NUMBERED_LIST,
		);
		$fields            = $record['lasso_fields'] ?? null;

		if ( $fields ) {
			$tmp_fields = explode( '||', $fields );
			foreach ( $tmp_fields as $field ) {
				$field       = explode( '>>', $field );
				$field_type  = trim( $field[0] ?? null );
				$field_name  = trim( $field[1] ?? null );
				$field_value = trim( $field[2] ?? null );
				if ( $field_type && in_array( $field_type, $allow_field_types, true ) && $field_name ) {
					// ? check field exists or not, if not, create a new field
					$field = new Model_Field();
					$f     = $field->get_one_by_cols(
						array(
							'field_name' => $field_name,
							'field_type' => $field_type,
						)
					);
					if ( ! $f->get_id() ) {
						$f->set_field_name( $field_name );
						$f->set_field_type( $field_type );
						$f->insert();
					}

					// ? insert field value
					if ( $f->get_id() && $field_value ) {
						$fm = new Field_Mapping();
						$fm->set_lasso_id( $post_id );
						$fm->set_field_id( $f->get_id() );
						$fm->set_field_value( $field_value );
						$fm->insert_on_duplicate_update_field_value();
					}
				}
			}
		}
	}

	/**
	 * Disable automatic updates for a specific plugin.
	 *
	 * @param boolean $update Current auto-update status.
	 * @param object  $item   Plugin update object.
	 * @return boolean        Updated auto-update status.
	 */
	public function disable_auto_update_specific_plugin( $update, $item ) {
		$item_plugin = $item->plugin ?? '';
		// ? Replace 'plugin-folder/plugin-file.php' with the folder and file name of Lasso plugin
		if ( LASSO_PLUGIN_BASE_NAME === $item_plugin && Lasso_Launch_Darkly::enable_lasso_lean() ) {
			return false;
		}
		return $update;
	}

	/**
	 * Init after WP Loaded for Lasso Pages.
	 *
	 * @return void
	 */
	public function init_wp_loaded_for_lasso_page() {
		// ? Remove action "admin_footer" of "Gravity Forms Stripe Add-On" plugin to avoid conflict with Lasso pages
		if ( class_exists( 'GFStripe' ) ) {
			Lasso_Helper::remove_action( 'admin_footer', array( 'GFStripe', 'remove_cardholder_name_merge_tag' ) );
		}
	}

	/**
	 * Fix JS errors
	 */
	public function fix_js_errors() {
		// ? theme: The7
		Lasso_Helper::remove_action( 'admin_print_footer_scripts', array( 'Dt_Mega_menu', 'javascript_magick' ), 99 );
	}
}
