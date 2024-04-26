<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Disconnect_Reason\Disconnect_Reason_Ahrefs;
use ahrefs\AhrefsSeo\Third_Party\Sources;

/**
 * Ahrefs_Seo_View class.
 */
class Ahrefs_Seo_View {

	/** Handle of main JS file. */
	const AHREFS_JS_HANDLE         = 'ahrefs-seo';
	private const QUERY_VAR_LOCALS = '__ahrefs_seo_locals__';

	/**
	 * List of admin screens id
	 *
	 * @var string[]
	 */
	private $admin_screens = [];

	/**
	 * @var Ahrefs_Seo_Screen|Ahrefs_Seo_Screen_With_Table|null
	 */
	private $current_screen = null;

	/**
	 * @var string
	 */
	private $title = '';

	/**
	 * @var string
	 */
	private $title_template = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'current_screen', [ $this, 'current_screen' ] );
		add_filter( 'admin_title', [ $this, 'admin_title' ], 9999, 2 );
	}


	/**
	 * Add a result of add_menu_page, add_submenu_page to list
	 *
	 * @param Ahrefs_Seo_Screen $screen Screen instance.
	 * @param string            $screen_id Screen ID from WordPress (result of add_menu_page or add_submenu_page calls).
	 * @return void
	 */
	public function add_admin_screen( Ahrefs_Seo_Screen $screen, string $screen_id ) : void {
		// class name of a screen instance, without namespaces.
		$items = explode( '\\', get_class( $screen ) );
		$class = array_pop( $items );

		$this->admin_screens[ $class ] = $screen_id;
		$screen->set_screen_id( $screen_id );
	}

	/**
	 * Add scripts to plugin's pages and call custom actions.
	 * Also call action 'ahrefs_seo_process_data_' . $screen->id.
	 */
	public function current_screen() : void {
		if ( is_admin() ) {
			$screen = get_current_screen();

			$this->register_scripts();

			if ( $screen instanceof \WP_Screen && in_array( $screen->id, $this->admin_screens, true ) ) {
				do_action( 'ahrefs_seo_process_data_' . $screen->id );

				$this->add_scripts();
			}
		}
	}

	/**
	 * Is a plugin screen now.
	 *
	 * @param null|string $screen_name Plugin's current screen class name or null - for any plugin's screen.
	 * @return bool
	 */
	public function is_plugin_screen( ?string $screen_name = null ) : bool {
		if ( is_admin() ) {
			$screen = get_current_screen();
			if ( $screen instanceof \WP_Screen ) {
				if ( is_null( $screen_name ) ) { // any of plugin's screens.
					return in_array( $screen->id, $this->admin_screens, true );
				} else { // given screen class is currently viewed.
					return isset( $this->admin_screens[ $screen_name ] ) && ( $screen->id === $this->admin_screens[ $screen_name ] );
				}
			}
		}
		return false;
	}

	/**
	 * Show a template
	 * It removes dependency on knowing the PHP var name that is used
	 * for passing variables to included template via query vars
	 *
	 * E.g.,
	 * ```php
	 * Ahrefs_Seo_View::get()->show( 'a/b', 'title', array(
	 *     'c' => 1,
	 *     'd' => 1,
	 * ), $screen, '' );
	 * // Now `__ahrefs_seo_locals__['c']` and `__ahrefs_seo_locals__['d']`
	 * // will be available in template `a/b.php`
	 * ```
	 *
	 * @see http://wordpress.stackexchange.com/a/176807/31766
	 * @see https://developer.wordpress.org/reference/functions/set_query_var/
	 * @see Ahrefs_Seo_View::get_template_variables()
	 * @see Ahrefs_Seo_View::show_part()
	 *
	 * @param string               $template Template file name, without '.php' extension.
	 * @param string               $title Page title.
	 * @param array<string, mixed> $template_variables Variables to be included into template, associative array [ name => value ].
	 * @param Ahrefs_Seo_Screen    $screen Instance of screen class, that is using view.
	 * @param string               $custom_header Custom header for using instead of standard header.php template.
	 * @return void
	 */
	public function show( string $template, string $title, array $template_variables, Ahrefs_Seo_Screen $screen, string $custom_header = '' ) : void {
		$old_value                   = self::get_template_variables();
		$this->current_screen        = $screen;
		$template_variables['title'] = $title;
		// nonce variable for future checking.
		$template_variables['page_nonce'] = $screen->get_nonce_name();
		set_query_var( self::QUERY_VAR_LOCALS, $template_variables );
		// use passed to template variables.
		$templates_dir = __DIR__ . '/templates/';
		$headers_dir   = __DIR__ . '/templates/headers/';

		// include header, desired page template and footer.
		$header = ( '' !== $custom_header ) ? $custom_header : 'header';

		if ( file_exists( "{$headers_dir}{$header}.php" ) ) {
			require "{$headers_dir}{$header}.php";
		} else {
			require "{$headers_dir}header.php";
		}

		if ( 'settings-account' === $template ) {
			$this->maybe_show_ahrefs_notices();
		}

		if ( file_exists( "{$templates_dir}{$template}.php" ) ) {
			require "{$templates_dir}{$template}.php";
		}
		require "{$headers_dir}footer.php";
		set_query_var( self::QUERY_VAR_LOCALS, $old_value );
	}

	/**
	 * Show template part.
	 * Template parts located at the 'parts' subdir of templates.
	 *
	 * @see Ahrefs_Seo_View::show()
	 * @see Ahrefs_Seo_View::get_template_variables()
	 *
	 * @param string               $template Template file name, without '.php' extension.
	 * @param array<string, mixed> $template_variables Variables to be included into template, associative array [ name => value ].
	 * @return bool
	 */
	public function show_part( string $template, array $template_variables = [] ) : bool {
		$old_value = self::get_template_variables();
		set_query_var( self::QUERY_VAR_LOCALS, $template_variables );
		// use passed to template variables.
		$templates_dir = __DIR__ . '/templates/parts/';
		if ( file_exists( "$templates_dir$template.php" ) ) {
			require "$templates_dir$template.php";
			set_query_var( self::QUERY_VAR_LOCALS, $old_value );
			return true;
		} else {
			Ahrefs_Seo::breadcrumbs( "Template part not found: [$template] $templates_dir$template.php" );
		}
		set_query_var( self::QUERY_VAR_LOCALS, $old_value );
		return false;
	}

	/**
	 * Return current screen if defined.
	 *
	 * @return Ahrefs_Seo_Screen|Ahrefs_Seo_Screen_With_Table|null
	 */
	public function get_ahrefs_screen() : ?Ahrefs_Seo_Screen {
		return $this->current_screen;
	}
	/**
	 * Return template variables without need to know their exact names.
	 *
	 * E.g.,
	 * ```php
	 * <?php
	 * // (continuing from `show` or `show_part` method).
	 * $locals = Ahrefs_Seo_View::get_template_variables();
	 * ?>
	 * <h1>
	 *     The var <code>c</code> is
	 *     <code><?php echo esc_html( $locals['c'] ); ?></code>
	 * </h1>
	 *
	 * @see Ahrefs_Seo_View::show()
	 * @see Ahrefs_Seo_View::show_part()
	 *
	 * @return array<string, mixed>
	 */
	public static function get_template_variables() : array {
		return (array) get_query_var( self::QUERY_VAR_LOCALS, array() );
	}

	/**
	 * Register JS and CSS files.
	 */
	private function register_scripts() : void {
		wp_enqueue_script( 'jquery-validate', AHREFS_SEO_URL . 'assets/js/jquery.validate.min.js', [ 'jquery' ], AHREFS_SEO_VERSION, true );

		wp_register_script( 'datatables', AHREFS_SEO_URL . 'assets/js/datatables.min.js', [], AHREFS_SEO_VERSION, true );
		wp_register_script( 'ahrefs-seo-content', AHREFS_SEO_URL . 'assets/js/content.js', [ 'jquery', 'datatables', 'jquery-ui-tooltip' ], AHREFS_SEO_VERSION, true );
		wp_localize_script(
			'ahrefs-seo-content',
			'content_strings',
			[
				'title_select_target_keyword' => esc_html__( 'Select target keyword', 'ahrefs-seo' ),
				'show_details'                => esc_html__( '(show details)', 'ahrefs-seo' ),
				'link_explore_keyword'        => esc_html__( 'Explore in Ahrefs', 'ahrefs-seo' ),
				'notice_oops'                 => esc_html__( 'Oops, there was an error. Please try again.', 'ahrefs-seo' ),
				'notice_oops_while_saving'    => esc_html__( 'Oops, there was an error while saving the keyword. Please try again.', 'ahrefs-seo' ),
				'notice_oops_while_loading'   => esc_html__( 'Oops, there was an error while loading the keywords list. Please try again.', 'ahrefs-seo' ),
				'action_failed'               => esc_html__( 'Action failed. Please try again or reload a page.', 'ahrefs-seo' ),
				'title'                       => esc_html( $this->title ),
				'title_template'              => esc_html( $this->title_template ),
				'content_audit'               => esc_html__( 'Content Audit', 'ahrefs-seo' ),
				'no_keywords_info'            => esc_html__( 'No keywords info available.', 'ahrefs-seo' ),
				'all_countries'               => esc_html_x( 'All', 'Country name (short of "All countries")', 'ahrefs-seo' ),
				'notice_no_ref_domains_data'  => esc_html__( 'Couldn’t fetch the number of ref. domains', 'ahrefs-seo' ),
				'popup'                       => [ // Select target keyword popup: column titles and hints.
					'keyword'          => esc_html__( 'Target keyword', 'ahrefs-seo' ),
					'source'           => esc_html__( 'Source', 'ahrefs-seo' ),
					'position'         => esc_html__( 'Position', 'ahrefs-seo' ),
					'clicks'           => esc_html__( 'Clicks', 'ahrefs-seo' ),
					'impressions'      => esc_html__( 'Impressions', 'ahrefs-seo' ),
					'country'          => esc_html__( 'Country', 'ahrefs-seo' ),
					'hint_position'    => esc_html__( 'The average position of the page for the last 3 months. This metric comes from your GSC account.', 'ahrefs-seo' ),
					'hint_clicks'      => esc_html__( 'The total number of clicks for the last 3 months. This metric comes from your GSC account.', 'ahrefs-seo' ),
					'hint_impressions' => esc_html__( 'The total number of impressions for the last 3 months. This metric comes from your GSC account.', 'ahrefs-seo' ),
				],
				'positions'                   => [
					'keyword_target'     => esc_html__( 'Target keyword', 'ahrefs-seo' ),
					'keyword_additional' => esc_html__( 'Additional keywords', 'ahrefs-seo' ),
					'position'           => esc_html__( 'Position', 'ahrefs-seo' ),
					'clicks'             => esc_html__( 'Clicks', 'ahrefs-seo' ),
					'impressions'        => esc_html__( 'Impressions', 'ahrefs-seo' ),
					'hint_position'      => esc_html__( 'The average position of the page for the last 3 months. This metric comes from your GSC account.', 'ahrefs-seo' ),
					'hint_clicks'        => esc_html__( 'The total number of clicks for the last 3 months. This metric comes from your GSC account.', 'ahrefs-seo' ),
					'hint_impressions'   => esc_html__( 'The total number of impressions for the last 3 months. This metric comes from your GSC account.', 'ahrefs-seo' ),
					'link_check_serp'    => esc_html__( 'Check SERP', 'ahrefs-seo' ),
					'no_rows_message'    => esc_html__( 'No keywords info available.', 'ahrefs-seo' ),
					'export_button'      => esc_html__( 'Export', 'ahrefs-seo' ),
					/* translators: %d: number of items to show */
					'show_more_link'     => esc_html__( 'Show %d more', 'ahrefs-seo' ),

				],
				'source_id'                   => [ // useful source id constants.
					'gsc'    => Sources::SOURCE_GSC,
					'manual' => Sources::SOURCE_MANUAL,
					'tf_idf' => Sources::SOURCE_TF_IDF,
					'saved'  => Sources::SOURCE_EXT_SAVED,
				],
				'audit'                       => [
					'progress_initial'  => esc_html_x( 'Analyzing...', 'button title', 'ahrefs-seo' ),
					/* Translators: {0}: current progress value, like '12.3'. Note: Button title at the header, must be short */
					'progress_percents' => esc_html_x( 'Analyzing: {0}%', 'button title', 'ahrefs-seo' ),
				],
			]
		);
		wp_register_script( self::AHREFS_JS_HANDLE, AHREFS_SEO_URL . 'assets/js/ahrefs.js', [ 'jquery', 'jquery-validate', 'datatables', 'jquery-ui-tooltip' ], AHREFS_SEO_VERSION, true );
		wp_localize_script(
			self::AHREFS_JS_HANDLE,
			'ahrefs_seo_strings',
			[
				'enter_auth_code' => esc_html__( 'Please enter your authorization code', 'ahrefs-seo' ),
			]
		);

		wp_register_style( 'ahrefs-seo', AHREFS_SEO_URL . 'assets/css/ahrefs.css', [], AHREFS_SEO_VERSION );
		wp_register_style( 'ahrefs-seo-rtl', AHREFS_SEO_URL . 'assets/css/rtl.css', [], AHREFS_SEO_VERSION );
	}

	/**
	 * Add JS and CSS files to plugin's admin screens.
	 */
	private function add_scripts() : void {
		add_thickbox();
		wp_enqueue_script( 'ahrefs-seo-content' );
		wp_enqueue_script( 'ahrefs-seo-backlinks' );
		wp_enqueue_script( 'ahrefs-seo-link-rules' );
		wp_enqueue_script( self::AHREFS_JS_HANDLE );
		wp_enqueue_style( 'ahrefs-seo' );
		if ( is_rtl() ) {
			wp_enqueue_style( 'ahrefs-seo-rtl' );
		}
	}

	/**
	 * Check and show notice if ahrefs account is disconnected or over limit.
	 */
	public function maybe_show_ahrefs_notices() : void {
		$message = ( new Disconnect_Reason_Ahrefs() )->get_reason();
		if ( ! is_null( $message ) ) {
			$message->show();
		} else {
			$api = Ahrefs_Seo_Api::get();
			if ( $api->is_disconnected() ) {
				$this->show_part( 'notices/ahrefs-disconnected' );
			} elseif ( $api->is_limited_account() ) {
				$this->show_part( 'notices/ahrefs-limited' );
			}
		}
	}

	/**
	 * Flush current output
	 *
	 * @since 0.8.4
	 *
	 * @return void
	 */
	public function flush() : void {
		if ( 2 === ob_get_level() ) { // is output blocked by WooCommerce?
			ob_end_flush();
			flush();
			ob_start();
		} else {
			flush();
		}
	}

	/**
	 * Filters the title tag content for an admin page.
	 *
	 * @since 0.8.4
	 *
	 * @param string $admin_title The page title, with extra context added.
	 * @param string $title       The original page title.
	 * @return string
	 */
	public function admin_title( $admin_title, $title ) {
		// Note: callback, do not use parameter types.
		$this->title          = (string) $title;
		$this->title_template = str_replace( $this->title, '####', (string) $admin_title );
		wp_localize_script(
			'ahrefs-seo-content',
			'content_strings2',
			[
				'title'          => esc_html( $this->title ),
				'title_template' => esc_html( $this->title_template ),
			]
		);
		return $admin_title;
	}

	/**
	 * Show link with "learn more" icon and text
	 *
	 * @param string $href Destination URL.
	 * @param string $text Link text.
	 *
	 * @return void
	 * @since 0.10.1
	 */
	public function learn_more_link( string $href, string $text ) : void {
		?>
		<a href="<?php echo esc_attr( $href ); ?>" target="_blank" class="learn-more-link">
			<span class="text"><?php echo esc_html( $text ); ?></span>
			<img src="<?php echo esc_attr( AHREFS_SEO_IMAGES_URL . 'link-open.svg' ); ?>" alt="<?php esc_attr_e( 'Open the link', 'ahrefs-seo' ); ?>" class="icon">
		</a>
		<?php
	}
}
