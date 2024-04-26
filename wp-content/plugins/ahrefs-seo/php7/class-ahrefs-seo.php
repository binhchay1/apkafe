<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;
use ahrefs\AhrefsSeo\Features\Duplicated_Keywords;
use Error;
use Exception;
use Throwable;

/**
 * Main class of Ahrefs Seo plugin.
 */
class Ahrefs_Seo {

	/** Menu slug for plugin. */
	const SLUG = 'ahrefs';
	/** Menu slug for Content Audit submenu. */
	const SLUG_CONTENT = 'ahrefs';
	/** Menu slug for Settings submenu. */
	const SLUG_SETTINGS = 'ahrefs-settings';
	/** Capability for plugin pages and features. */
	const CAP_ROLE_ADMIN  = 'edit_dashboard'; // Must be an Administrator.
	const CAP_ROLE_EDITOR = 'edit_others_posts'; // Must be at least an Editor.
	const CAP_ROLE_AUTHOR = 'publish_posts'; // Must be at least an Author.

	const CAP_WIZARD_VIEW            = self::CAP_ROLE_ADMIN; // View & submit Wizard steps.
	const CAP_SETTINGS_MENU          = self::CAP_ROLE_EDITOR; // View "Settings" menu item.
	const CAP_CONTENT_AUDIT_VIEW     = self::CAP_ROLE_AUTHOR; // View "Content Audit page".
	const CAP_CONTENT_AUDIT_RUN      = self::CAP_ROLE_EDITOR; // Run new audit or Resume paused audit.
	const CAP_SETTINGS_AUDIT_VIEW    = self::CAP_ROLE_EDITOR; // View "Audit settings" page.
	const CAP_SETTINGS_AUDIT_SAVE    = self::CAP_ROLE_EDITOR; // Can save "Scope of audit" options.
	const CAP_SETTINGS_SCHEDULE_VIEW = self::CAP_ROLE_EDITOR; // View "Audit schedule" page.
	const CAP_SETTINGS_SCHEDULE_SAVE = self::CAP_ROLE_ADMIN; // Can save "Audit schedule" options.
	const CAP_SETTINGS_ACCOUNTS_VIEW = self::CAP_ROLE_ADMIN; // View Ahrefs and Google account pages.
	const CAP_SETTINGS_ACCOUNTS_SAVE = self::CAP_ROLE_ADMIN; // Can change or disconnect Ahrefs and Google accounts.
	const CAP_SETTINGS_DATA_SAVE     = self::CAP_ROLE_ADMIN; // Change settings at My audit data page.
	const CAP_EXPORT_CSV             = self::CAP_ROLE_EDITOR; // Can use "Export to CSV" at Content audit page.
	const CAP_EXPORT_ZIP             = self::CAP_ROLE_EDITOR; // Can export ZIP at "My latest audit data" section of My audit data page.
	const CAP_EXPORT_GOOGLE_CONFIG   = self::CAP_SETTINGS_ACCOUNTS_SAVE; // Can export config at "Share my Google configuration" section of My audit data page.

	const ACTION_DOMAIN_CHANGED = 'ahrefs_seo_domain_changed';
	const ACTION_TOKEN_CHANGED  = 'ahrefs_seo_token_changed';
	/** Version of database, used for running update when current version changed. */
	const OPTION_TABLE_VERSION = 'ahrefs-seo-db-version';
	/** Version of Content Audit rules, used for running update when current version changed. */
	const OPTION_CONTENT_RULES_VERSION = 'ahrefs-seo-content-rules-version';
	const OPTION_LAST_HASH             = 'ahrefs-seo-last-hash';

	/** Table name, without prefix. */
	const TABLE_CONTENT   = 'ahrefs_seo_content';
	const TABLE_SNAPSHOTS = 'ahrefs_seo_snapshots';

	/**
	 * Ahrefs key is submitted and correct.
	 */
	const OPTION_IS_INITIALIZED = 'ahrefs-seo-is-initialized1';
	/**
	 * Analytics code is submitted and correct.
	 */
	const OPTION_IS_INITIALIZED_ANALYTICS = 'ahrefs-seo-is-initialized2';
	/**
	 * Analysis started: user is seeing step 3.2 with progress bar instead of steps 1-2.
	 */
	const OPTION_IS_INITIALIZED_IN_PROGRESS = 'ahrefs-seo-is-initialized21';
	/**
	 * Load or do not load wizard screens.
	 * Turned on after initial backlinks transfer and content analysis completed.
	 */
	const OPTION_IS_INITIALIZED_FIRST_TIME = 'ahrefs-seo-is-initialized3';
	/**
	 * Load or do not load wizard screen ajax stuff.
	 * Turned off after any normal page opened first time.
	 * (It turned on a bit later than previous option, because we want that already opened wizard page receive updates using ajax).
	 */
	const OPTION_IS_INITIALIZED_WIZARD_COMPLETED = 'ahrefs-seo-is-initialized4';

	/**
	 * Allow to send error diagnostic reports to Ahrefs.
	 */
	const OPTION_ALLOW_REPORTS = 'ahrefs-seo-allow-reports';
	/**
	 * Current database version. Increased when database tables structure changed.
	 */
	const CURRENT_TABLE_VERSION = '84'; // previous published version is '81'.
	/**
	 * Current Content Audit rules version. Increased when rules changed.
	 */
	const CURRENT_CONTENT_RULES = '5';

	/**
	 * This class instance.
	 *
	 * @var Ahrefs_Seo|null
	 */
	private static $instance = null;

	/**
	 * What is a source of thread: ping, wizard, fast, scheduled.
	 *
	 * @var string|null
	 */
	private static $thread_source = null;

	/**
	 * View class instance.
	 *
	 * @var Ahrefs_Seo_View
	 */
	private $view;

	/**
	 * Token class instance.
	 *
	 * @var Ahrefs_Seo_Token
	 */
	private $token;
	/**
	 * Wizard screen instance.
	 *
	 * @var Ahrefs_Seo_Screen_Wizard
	 */
	private $wizard;
	/**
	 * Content screen instance.
	 *
	 * @var Ahrefs_Seo_Screen_Content
	 */
	private $content;
	/**
	 * Settings screen instance.
	 *
	 * @var Ahrefs_Seo_Screen_Settings
	 * */
	private $settings;

	/**
	 * @var null|\Bugsnag\Client
	 */
	protected static $bugsnag;

	/**
	 * @var float
	 */
	private static $time_start;
	/**
	 * @var float
	 */
	private static $time_limit;
	/**
	 * Fatal error, plugin is not working.
	 *
	 * @var array|null
	 */
	private static $fatal_info = null;

	/**
	 * @var string Last reported error hash.
	 */
	private static $last_hash = '';

	/**
	 * Return the plugin instance
	 *
	 * @return Ahrefs_Seo
	 */
	public static function get() : Ahrefs_Seo {
		if ( ! self::$instance ) {
			self::$time_start = microtime( true );
			self::use_time_limit();

			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		try {
			$this->define_tables();
			$this->init();
		} catch ( Error $e ) {
			Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__, __( 'Plugin initialization failed.', 'ahrefs-seo' ) );
		} catch ( Exception $e ) {
			self::notify( $e, 'Plugin initialization failed.' );
			Ahrefs_Seo_Errors::save_message( 'general', __( 'Plugin initialization failed.', 'ahrefs-seo' ) . ' ' . $e->getMessage(), Message::TYPE_ERROR ); // show error to user if we can't submit it.
		}
	}

	/**
	 * Initialize plugin
	 */
	private function init() : void {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		if ( is_admin() ) {
			add_action( 'init', [ $this, 'load_textdomain' ], 13 ); // first of init actions.
			add_action( 'init', [ $this, 'init_screens' ], 15 );
			add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
			add_filter( 'wp_refresh_nonces', [ $this, 'wp_refresh_nonces' ], 10, 3 );
		}
		add_action( 'init', [ $this, 'init_cron' ], 15 );
		add_action( 'init', [ $this, 'quick_updates_check' ], 14 );

		add_action( 'switch_blog', [ $this, 'switch_blog' ], 9, 0 );

		add_action( self::ACTION_TOKEN_CHANGED, [ $this, 'clear_caches_on_events' ] );
		add_action( self::ACTION_DOMAIN_CHANGED, [ $this, 'clear_caches_on_events' ] );
		// initialize earlier.
		/** @psalm-suppress TypeDoesNotContainType */
		if ( self::allow_reports() && ( ! defined( 'AHREFS_SEO_BUGSNAG_OFF' ) || ! AHREFS_SEO_BUGSNAG_OFF ) ) {
			self::$bugsnag = Ahrefs_Seo_Bugsnag::get()->create_client();
			\Bugsnag\Handler::register( self::$bugsnag );
		}

		if ( $this->initialized_get() ) {
			if ( is_admin() ) {
				add_action( 'admin_init', [ new Custom_Traffic_Column(), 'add_post_columns' ] );
				add_action( 'init', [ Ahrefs_Seo_Screen_With_Table::class, 'add_table_and_post_actions' ], 15 ); // "per page" options.
			}

			// initialize Content Audit hooks.
			Content_Hooks::get();
		}
	}

	/**
	 * Initialize cron jobs.
	 */
	public function init_cron() : void {
		if ( is_null( $this::$fatal_info ) ) {
			if ( $this->initialized_get() || get_option( self::OPTION_IS_INITIALIZED_IN_PROGRESS ) ) { // allow cron jobs if initialized or after the Wizard update is in progress.
				Ahrefs_Seo_Cron::get();
			}
		}
	}

	/**
	 * Initialize admin part
	 */
	public function admin_init() : void {
		$this->token = Ahrefs_Seo_Token::get();
		if ( ! get_option( self::OPTION_IS_INITIALIZED_WIZARD_COMPLETED ) ) {
			Ahrefs_Seo_Data_Wizard::get(); // ajax handlers for wizard progress.
		}
		add_filter( 'plugin_action_links_' . AHREFS_SEO_BASENAME, [ $this, 'add_action_links' ] );
		add_action( 'after_plugin_row_' . AHREFS_SEO_BASENAME, [ $this, 'after_plugin_row' ], 20, 1 );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 20, 2 );
	}

	/**
	 * Load plugin text domain
	 *
	 * @since 0.8.4
	 * @return void
	 */
	public function load_textdomain() : void {
		load_plugin_textdomain( 'ahrefs-seo', false, dirname( plugin_basename( AHREFS_SEO_DIR . '/ahrefs-plugin.php' ) ) . '/languages' );
	}

	/**
	 * Initialize screens
	 */
	public function init_screens() : void {
		$this->view = new Ahrefs_Seo_View();
		if ( ! is_null( $this::$fatal_info ) ) {
			$this->settings = new Ahrefs_Seo_Screen_Settings( $this->view );
		} elseif ( ! $this->initialized_get() ) {
			$this->wizard = new Ahrefs_Seo_Screen_Wizard( $this->view );
		} else {
			$this->content  = new Ahrefs_Seo_Screen_Content( $this->view );
			$this->settings = new Ahrefs_Seo_Screen_Settings( $this->view );
		}
	}

	// phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch, Squiz.Commenting.EmptyCatchComment.Missing
	/**
	 * Called on plugin activation.
	 * Add cron tasks.
	 */
	public static function plugin_activate() : void {
		try {
			Ahrefs_Seo_Cron::get()->add_tasks();
		} catch ( Exception $e ) {
		} catch ( Error $e ) {
		}
	}

	/**
	 * Called on plugin deactivation.
	 * Remove cron tasks.
	 */
	public static function plugin_deactivate() : void {
		try {
			Ahrefs_Seo_Cron::get()->remove_tasks();
			( new Snapshot() )->clean_cache();
		} catch ( Exception $e ) {
		} catch ( Error $e ) {
		}
	}
	// phpcs:enable Generic.CodeAnalysis.EmptyStatement.DetectedCatch, Squiz.Commenting.EmptyCatchComment.Missing

	/**
	 * Add items to admin menu
	 */
	public function add_admin_menu() : void {
		$icon = 'data:image/svg+xml;base64,' . base64_encode( '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.23 6.104H12.315V8.094L9.651 8.301C5.346 8.623 4 9.761 4 12.956V13.601C4 15.939 5.64 17.349 8.157 17.349C10.149 17.349 11.29 16.879 12.697 15.445H12.93V16.968H15.77V3H5.23V6.104ZM12.315 12.839C11.438 13.715 10.119 14.3 9.036 14.3C7.866 14.3 7.367 13.86 7.396 12.894C7.426 11.548 7.923 11.225 10.178 11.021L12.315 10.816V12.839Z" fill="white"/></svg>' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		if ( ! is_null( $this::$fatal_info ) ) {
			// if already initialized - then same as for Content audit screen, otherwise same as for Wizard view.
			$this->view->add_admin_screen( $this->settings, add_menu_page( __( 'Ahrefs SEO for WordPress', 'ahrefs-seo' ), __( 'Ahrefs SEO', 'ahrefs-seo' ), ( $this->initialized_get() ? self::CAP_CONTENT_AUDIT_VIEW : self::CAP_WIZARD_VIEW ), self::SLUG, [ $this, 'error_menu' ], $icon, 81 ) );
			return;
		}

		if ( ! $this->initialized_get() ) {
			// show wizard.
			$this->view->add_admin_screen( $this->wizard, add_menu_page( __( 'Ahrefs SEO for WordPress', 'ahrefs-seo' ), __( 'Ahrefs SEO', 'ahrefs-seo' ), self::CAP_WIZARD_VIEW, self::SLUG, [ $this, 'wizard_menu' ], $icon, 81 ) );
		} else {
			if ( get_option( self::OPTION_IS_INITIALIZED_FIRST_TIME ) && ! get_option( self::OPTION_IS_INITIALIZED_WIZARD_COMPLETED ) ) {
				$this->initialized_set( null, null, null, null, true );
			}

			// subpages (for all) + settings (editor+admin).
			$this->view->add_admin_screen( $this->content, add_menu_page( __( 'Ahrefs SEO for WordPress', 'ahrefs-seo' ), __( 'Ahrefs SEO', 'ahrefs-seo' ), self::CAP_CONTENT_AUDIT_VIEW, self::SLUG, array( $this, 'content_menu' ), $icon, 81 ) );
			$page = add_submenu_page( self::SLUG, __( 'Content Audit', 'ahrefs-seo' ), __( 'Content Audit', 'ahrefs-seo' ), self::CAP_CONTENT_AUDIT_VIEW, self::SLUG_CONTENT, array( $this, 'content_menu' ) );
			if ( $page ) {
				$this->view->add_admin_screen( $this->content, $page );
			}
			$page = add_submenu_page( self::SLUG, __( 'Ahrefs SEO for WordPress', 'ahrefs-seo' ), __( 'Settings', 'ahrefs-seo' ), self::CAP_SETTINGS_MENU, self::SLUG_SETTINGS, array( $this, 'settings_menu' ) );
			if ( $page ) {
				$this->view->add_admin_screen( $this->settings, $page );
			}
		}
	}

	/**
	 * Handler for Wizard menu item.
	 * It used until Wizard will be finished.
	 */
	public function wizard_menu() : void {
		$this->wizard->show();
	}

	/**
	 * Handler for Settings menu item
	 */
	public function settings_menu() : void {
		$this->settings->show();
	}


	/**
	 * Handler for Content Audit menu item
	 */
	public function content_menu() : void {
		$this->content->show();
	}

	/**
	 * Handler for Error menu item
	 */
	public function error_menu() : void {
		$this->view->show(
			'fatal',
			__( 'Content Audit', 'ahrefs-seo' ),
			array_merge(
				[ 'header_class' => ( $this->settings ? $this->settings->get_header_classes( [ 'content' ] ) : [ 'content' ] ) ],
				self::$fatal_info ?? []
			),
			$this->settings,
			'error'
		);
	}

	/**
	 * Clear vary caches.
	 */
	public function clear_caches_on_events() : void {
		// need to call clear cache for those instances, because they may be not initialized before.
		Ahrefs_Seo_Api::get()->clear_cache();
	}

	/**
	 * Check DB version and run update if current version is different from version value saved at the DB.
	 * Do same for Content Audit rules.
	 */
	public function quick_updates_check() : void {
		try {
			if ( get_option( self::OPTION_TABLE_VERSION ) !== self::CURRENT_TABLE_VERSION ) {
				$previous_version = intval( get_option( self::OPTION_TABLE_VERSION, 0 ) );
				self::breadcrumbs( sprintf( 'Update DB from version %d to %d.', $previous_version, self::CURRENT_TABLE_VERSION ) );
				if ( 0 === $previous_version ) { // not an update from some previous version.
					add_action( 'init', [ new Ahrefs_Seo_Content_Settings(), 'maybe_turn_on_products' ], 9999 ); // custom post type 'product' is not registered at the current moment.
				}
				// reset stop error if requested.
				if ( isset( $_GET['reset_error'] ) && check_admin_referer( 'reset_error', 'reset_error' ) ) {
					Ahrefs_Seo_Db::reset_stop_error();
					Helper_Content::wp_redirect( remove_query_arg( [ 'reset_error', 'r' ] ) );
					die();
				}
				if ( Ahrefs_Seo_Db::create_table( $previous_version ) ) {
					update_option( self::OPTION_TABLE_VERSION, self::CURRENT_TABLE_VERSION );
					update_option( self::OPTION_LAST_HASH, [] ); // reset last reported error if any.
				} else {
					return;
				}
			}
			$prev_rules_ver = get_option( self::OPTION_CONTENT_RULES_VERSION );
			if ( self::CURRENT_CONTENT_RULES !== $prev_rules_ver ) {
				update_option( self::OPTION_CONTENT_RULES_VERSION, self::CURRENT_CONTENT_RULES );
				if ( (int) $prev_rules_ver > 0 ) {
					Ahrefs_Seo_Data_Content::get()->update_options( (int) $prev_rules_ver );
					// run new analysis on rules version update.
					if ( (int) $prev_rules_ver < 4 ) {
						if ( Ahrefs_Seo_Compatibility::quick_compatibility_check() ) {
							( new Snapshot() )->create_new_snapshot();
						}
					}
					if ( (int) $prev_rules_ver < 5 ) { // prev version may show incorrect duplicated keywords.
						( new Duplicated_Keywords() )->fill_duplicated_for_snapshot( ( new Snapshot() )->get_current_snapshot_id() );
					}
				}
			}
		} catch ( Error $e ) {
			Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__, __( 'Quick database update failed', 'ahrefs-seo' ) );
		} catch ( Exception $e ) {
			self::notify( $e, 'Quick Database update failed' );
		}
	}

	/**
	 * Set vary initialized statuses to on or off.
	 * Update status only if not null.
	 *
	 * @param null|bool $is_initialized_ahrefs Is ahrefs initialized (has valid token).
	 * @param null|bool $is_initialized_analytics Is analytics initialized (has valid token AND ua_id profile chosen).
	 * @param null|bool $analysis_in_progress Analysis started: user is seeing step 3.2 with progress bar.
	 * @param null|bool $is_initialized_first_time Load or do not load wizard screens.
	 * Turned off after initial backlinks transfer and content analysis completed.
	 * @param null|bool $is_initialized_wizard_completed Load or do not load wizard screen ajax stuff.
	 * Turned off after any normal page opened first time.
	 * (It turned on a bit longer then previous option, because we want that already opened wizard page receive updates using ajax).
	 * @return void
	 */
	public function initialized_set( ?bool $is_initialized_ahrefs, ?bool $is_initialized_analytics = null, ?bool $analysis_in_progress = null, ?bool $is_initialized_first_time = null, ?bool $is_initialized_wizard_completed = null ) : void {
		self::breadcrumbs( sprintf( 'initialized_set(%s)', (string) wp_json_encode( func_get_args() ) ) );

		if ( ! is_null( $is_initialized_ahrefs ) ) {
			update_option( self::OPTION_IS_INITIALIZED, $is_initialized_ahrefs );
			if ( ! $is_initialized_ahrefs ) {
				// reset current token.
				$this->token->token_save( '' );
			}
		}
		if ( ! is_null( $is_initialized_analytics ) ) {
			if ( (bool) get_option( self::OPTION_IS_INITIALIZED_ANALYTICS ) !== $is_initialized_analytics ) {
				update_option( self::OPTION_IS_INITIALIZED_ANALYTICS, $is_initialized_analytics );
				if ( ! $is_initialized_analytics ) {
					// reset current token if any.
					Ahrefs_Seo_Analytics::get()->disconnect();
				}
			}
		}
		if ( ! is_null( $analysis_in_progress ) ) {
			update_option( self::OPTION_IS_INITIALIZED_IN_PROGRESS, $analysis_in_progress );
		}
		if ( ! is_null( $is_initialized_first_time ) ) {
			update_option( self::OPTION_IS_INITIALIZED_FIRST_TIME, $is_initialized_first_time );
		}
		if ( ! is_null( $is_initialized_wizard_completed ) ) {
			update_option( self::OPTION_IS_INITIALIZED_WIZARD_COMPLETED, $is_initialized_wizard_completed );
		}
	}

	/**
	 * Is Wizard already initialized?
	 * No need to show it again if already did.
	 *
	 * @return bool
	 */
	public function initialized_wizard() : bool {
		$value = get_option( self::OPTION_IS_INITIALIZED_FIRST_TIME );
		return ! empty( $value );
	}

	/**
	 * Is plugin initialized
	 *
	 * @return bool
	 */
	public function initialized_get() : bool {
		static $result = null;
		// function called twice, must return same result.
		if ( is_null( $result ) ) {
			$value2 = get_option( self::OPTION_IS_INITIALIZED_FIRST_TIME );
			$result = ! empty( $value2 );
		}
		return $result;
	}

	/**
	 * Register custom tables within $wpdb object.
	 */
	private function define_tables() : void {
		global $wpdb;

		// List of tables without prefixes [ name for use inside $wpdb => real table name ].
		$tables = array(
			'ahrefs_content'   => self::TABLE_CONTENT,
			'ahrefs_snapshots' => self::TABLE_SNAPSHOTS,
		);

		foreach ( $tables as $name => $table ) {
			$wpdb->$name    = $wpdb->prefix . $table;
			$wpdb->tables[] = $table;
		}
	}

	/**
	 * Callback for switch blog action.
	 *
	 * @since 0.9.8
	 *
	 * @return void
	 */
	public function switch_blog() : void {
		$this->define_tables();
	}

	/**
	 * Should finish code execution?
	 *
	 * @param int|null $seconds_to_end Exit when x seconds left before max allowed limit.
	 * @param int|null $percents_to_end Exit when x percents of max allowed time left.
	 * @return bool
	 */
	public static function should_finish( ?int $seconds_to_end = null, ?int $percents_to_end = null ) : bool {
		if ( ! is_null( $percents_to_end ) ) {
			$seconds_to_end = ( 100 - $percents_to_end ) / 100.0 * self::$time_limit;
			if ( $seconds_to_end > 10 ) {
				$seconds_to_end = 10;
			}
		}
		if ( is_null( $seconds_to_end ) ) {
			$seconds_to_end = self::$time_limit > 30 ? 10 : 5;
		}
		return microtime( true ) - self::$time_start - ( defined( 'AHREFS_SEO_IGNORE_DELAY' ) && AHREFS_SEO_IGNORE_DELAY ? 5 * 60 : 0 ) >= self::$time_limit - $seconds_to_end;
	}

	/**
	 * Get time for transient options
	 *
	 * @return int Time in seconds.
	 */
	public static function transient_time() : int {
		return intval( self::$time_limit );
	}

	/**
	 * Set fatal error or notice reason
	 *
	 * @param string|null $header Header to show instead of default "contact ahrefs...".
	 * @param string      $reason Message to show.
	 * @param bool        $is_error Show ooops... part of screen if error.
	 * @param bool        $has_reset_button Show button for error reset.
	 * @return void
	 */
	public static function set_fatal_error( ?string $header, string $reason, bool $is_error = true, bool $has_reset_button = false ) : void {
		self::breadcrumbs( 'Fatal error: ' . (string) wp_json_encode( func_get_args() ) );
		self::$fatal_info = [
			'header'           => $header,
			'error'            => $reason,
			'is_error'         => $is_error,
			'has_reset_button' => $has_reset_button,
		];
	}

	/**
	 * Define and apply max execution time limit
	 *
	 * @return void
	 */
	private static function use_time_limit() : void {
		self::$time_limit = intval( ini_get( 'max_execution_time' ) );
		$expected_limit   = self::is_doing_real_cron() ? 55 : 15;
		if ( self::$time_limit <= 0 ) {
			self::$time_limit = $expected_limit;
		}
		if ( self::$time_limit > $expected_limit ) {
			self::$time_limit = $expected_limit;
		}
		if ( $expected_limit > self::$time_limit ) {
			self::set_time_limit( $expected_limit );
		}
	}

	/**
	 * A real cron job is running now.
	 *
	 * @return bool
	 */
	public static function is_doing_real_cron() : bool {
		/** @psalm-suppress RedundantCondition */
		return wp_doing_cron() && defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
	}

	/**
	 * Check if set_time_limit allowed and call it.
	 * Update internal time limit value.
	 * Try to avoid possible php warning "set_time_limit() has been disabled for security reasons".
	 *
	 * @param int $seconds Value, in seconds.
	 *
	 * @return bool
	 */
	public static function set_time_limit( int $seconds ) : bool {
		if ( $seconds > self::$time_limit ) { // no need to decrease allowed time.
			if ( function_exists( 'set_time_limit' ) && ! self::function_disabled( 'set_time_limit' ) ) {
				if ( @set_time_limit( $seconds ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- the checking is passed, but function call may produce warning: "set_time_limit(): Cannot set max execution time limit due to system policy" anyway.
					self::use_time_limit();
				}
			}
			return false;
		}
		return true;
	}

	/**
	 * Delay execution in microseconds.
	 *
	 * @since 0.7.3
	 *
	 * @param int $micro_seconds Value, in micro seconds.
	 * @return void
	 */
	public static function usleep( int $micro_seconds ) : void {
		if ( ! defined( 'AHREFS_SEO_IGNORE_DELAY' ) ) {
			if ( $micro_seconds > 0 ) {
				usleep( $micro_seconds );
			}
		}
	}

	/**
	 * Check if ignore_user_abort allowed and call it.
	 * Try to avoid possible php warning "ignore_user_abort() has been disabled for security reasons".
	 *
	 * @param bool $ignore New value.
	 *
	 * @return void
	 */
	public static function ignore_user_abort( bool $ignore ) : void {
		if ( function_exists( 'ignore_user_abort' ) && ! self::function_disabled( 'ignore_user_abort' ) ) {
			ignore_user_abort( $ignore );
		}
	}

	/**
	 * Is function disabled?
	 *
	 * @param string $function_name Function name to check.
	 * @return bool
	 */
	private static function function_disabled( string $function_name ) : bool {
		$disabled = explode( ',', (string) ini_get( 'disable_functions' ) );
		return in_array( $function_name, $disabled, true );
	}

	/**
	 * Set breadcrumbs for current code execution.
	 * No need to translate it.
	 *
	 * @param string $string String to save.
	 * @param bool   $is_error Is it an error.
	 *
	 * @return void
	 */
	public static function breadcrumbs( string $string, bool $is_error = false ) : void {
		if ( ! is_null( self::$bugsnag ) ) {
			self::$bugsnag->leaveBreadcrumb( $string, $is_error ? \Bugsnag\Breadcrumbs\Breadcrumb::ERROR_TYPE : \Bugsnag\Breadcrumbs\Breadcrumb::MANUAL_TYPE );
		} else {
			if ( defined( 'AHREFS_SEO_RELEASE' ) && 'development' === AHREFS_SEO_RELEASE && ! defined( 'AHREFS_SEO_SILENT' ) ) {
				error_log( '** ' . self::thread_id() . ( $is_error ? " Error $string" : " Log $string" ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}
	}

	/**
	 * Get previously collected breadcrumbs data
	 *
	 * @since 0.9.5
	 *
	 * @return array
	 */
	public static function get_breadcrumbs_data() : array {
		if ( ! is_null( self::$bugsnag ) ) {
			$report = \Bugsnag\Report::fromNamedError( self::$bugsnag->getConfig(), 'debug' );
			self::$bugsnag->getPipeline()->execute( $report, function(){} );
			$result = $report->toArray();
			unset( $result['app'], $result['context'], $result['severityReason'], $result['unhandled'], $result['user'] );
			return $result;
		}
		return [ 'disabled' ];
	}

	/**
	 * Notify (if allowed) about exception happened.
	 * No need to translate it.
	 *
	 * @param Throwable   $e Exception to report.
	 * @param null|string $type Type of notification, any string.
	 *
	 * @return void
	 */
	public static function notify( Throwable $e, ?string $type = null ) : void {
		$hash = ( (string) $e );
		if ( $hash === self::$last_hash ) { // do not report same error twice.
			return;
		}
		self::$last_hash = $hash;
		$events          = get_option( self::OPTION_LAST_HASH, [] );
		$count           = 0;
		if ( is_array( $events ) && isset( $events['hash'] ) && $events['hash'] === $hash ) {
			$count = absint( $events['count'] );
			if ( $count > 5 && 0 !== $count % 100 ) { // do not report same error many times.
				return;
			}
		}
		if ( $count >= 5 ) {
			self::breadcrumbs( sprintf( 'No more reports (repeated %d times).', $count ) );
		}
		update_option(
			self::OPTION_LAST_HASH,
			[
				'hash'  => $hash,
				'count' => ++$count,
			]
		);

		if ( ! is_null( self::$bugsnag ) ) {
			self::$bugsnag->notifyException(
				$e,
				function( $report ) use ( $type ) {
					if ( ! is_null( $type ) ) {
						/** @var \Bugsnag\Report $report */
						$report->addMetaData( [ 'type' => [ 'type' => $type ] ] );
					}
					return $report;
				}
			);
		} elseif ( defined( 'AHREFS_SEO_RELEASE' ) && ! defined( 'AHREFS_SEO_SILENT' ) ) {
			error_log( sprintf( '** Exception %s', (string) $e ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}

	/**
	 * Set reports allowed or not.
	 *
	 * @param bool $enable Enable reports.
	 * @return void
	 */
	public static function allow_reports_set( bool $enable = true ) : void {
		update_option( self::OPTION_ALLOW_REPORTS, $enable );
	}

	/**
	 * Is reports allowed?
	 *
	 * @return bool
	 */
	public static function allow_reports() : bool {
		return (bool) get_option( self::OPTION_ALLOW_REPORTS, false );
	}

	/**
	 * Get current domain.
	 *
	 * @return string
	 */
	public static function get_current_domain() : string {
		$domain = apply_filters( 'ahrefs_seo_domain', wp_parse_url( get_home_url(), PHP_URL_HOST ) ?? '' );
		return is_string( $domain ) ? $domain : '';
	}

	/**
	 * Return unique id for current thread.
	 * Set source of current thread execution when called first time.
	 *
	 * @since 0.7.3
	 *
	 * @param string|null $source Thread source.
	 * @return string
	 */
	public static function thread_id( ?string $source = null ) : string {
		static $result = null;
		if ( ! is_null( $source ) && is_null( self::$thread_source ) ) {
			self::$thread_source = $source;
		}
		if ( is_null( $result ) ) {
			$result = self::$time_start . '-' . rand( 1000000, 9999999 ) . ( wp_doing_cron() ? ( self::is_doing_real_cron() ? 'cron' : 'wpcron' ) : 'user' );
		}
		return $result . ( is_null( self::$thread_source ) ? '' : '-' . self::$thread_source ) . self::$time_limit;
	}

	/**
	 * @return Ahrefs_Seo_View
	 */
	public function get_view() : Ahrefs_Seo_View {
		return $this->view;
	}

	/**
	 * Get url to contact Ahrefs support team.
	 *
	 * @since 0.8.4
	 *
	 * @param bool $with_details Link will have body filled with some details.
	 * @return string
	 */
	public static function get_support_url( bool $with_details = false ) : string {
		$result = 'mailto:support@ahrefs.com?subject=' . rawurlencode( 'WordPress plugin' );
		if ( $with_details ) {
			$analytics        = Ahrefs_Seo_Analytics::get();
			$google_connected = $analytics->get_data_tokens()->is_token_set();
			$result          .= '&body=' . rawurlencode(
				"\n" .
				'Site: ' . self::get_current_domain() . "\n" .
				'Ahrefs account: ' . ( Ahrefs_Seo_Api::get()->is_disconnected() ? 'not connected' : ( Ahrefs_Seo_Api::get()->is_free_account( true ) ? ' free token connected' : ' connected' ) ) . "\n" .
				'Google accounts: ' . (
					$google_connected ? 'connected' . "\n" .
					( $analytics->is_analytics_enabled( false ) ? 'GA enabled' . ( $analytics->is_ua_set() ? ' and set' : ' but not set' ) : 'GA not enabled' ) . "\n" .
					( $analytics->is_gsc_enabled( false ) ? 'GSC enabled' . ( $analytics->is_gsc_set() ? ' and set' : ' but not set' ) : 'GSC not enabled' ) . "\n"
					: 'not connected'
				)
			);
		}
		return $result;
	}

	/**
	 * Add action links for the plugins page.
	 *
	 * @since 0.9.7
	 *
	 * @param string[] $actions An array of plugin action links.
	 * @return string[]
	 */
	public function add_action_links( $actions ) : array {
		if ( ! is_array( $actions ) ) {
			$actions = [];
		}
		if ( $this->initialized_get() ) {
			$actions[] = sprintf(
				'<a href="%s">%s</>',
				esc_attr( Links::settings( Ahrefs_Seo_Screen_Settings::TAB_DATA ) . '&from-plugins-list=1' ),
				esc_html__( 'My audit data', 'ahrefs-seo' )
			);
		}
		return $actions;
	}

	/**
	 * Show notice below a plugin row at multisite installation
	 *
	 * @since 0.9.7
	 *
	 * @param string $plugin_file Path to the plugin file relative to the plugin's directory.
	 * @return void
	 */
	public function after_plugin_row( $plugin_file ) {
		if ( is_multisite() && is_network_admin() && is_plugin_active_for_network( $plugin_file ) ) {
			try {
				/** @var \WP_Plugins_List_Table $wp_list_table */
				$wp_list_table = _get_list_table(
					'\WP_Plugins_List_Table',
					[ 'screen' => get_current_screen() ]
				);
				$columns       = $wp_list_table ? $wp_list_table->get_column_count() : 4;
				?>
				<tr class="plugin-update-tr active ahrefs-seo-plugin-notice-row" data-slug="" data-plugin="<?php echo esc_attr( $plugin_file ); ?>"><td colspan="<?php echo esc_attr( (string) $columns ); ?>" class="plugin-update colspanchange" style="position:relative;top:-2px;"><div class="notice inline notice-warning notice-alt"><p>
				<?php
				esc_html_e( 'When you delete the plugin, your settings and audit data are removed from your website’s database. You can disable this in the plugin settings individually for each website if you are temporarily uninstalling the plugin and want your data to be preserved.', 'ahrefs-seo' );
				?>
				</p></div></td></tr>
				<?php
			} catch ( Exception $e ) {
				self::notify( $e, 'multisite plugins list' );
			}
		}
	}

	/**
	 * Add meta links for the plugins page.
	 *
	 * @since 0.9.7
	 *
	 * @param string[] $plugin_meta An array of the plugin's metadata, including the version, author, author URI, and plugin URI.
	 * @param string   $plugin_file Path to the plugin file relative to the plugin's directory.
	 * @return string[]
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( AHREFS_SEO_BASENAME === $plugin_file ) {
			$plugin_meta[] = sprintf(
				'<a href="%s" target="_blank">%s</>',
				esc_attr( 'https://ahrefs.canny.io/wordpress-plugin' ),
				esc_html__( 'Suggest a feature', 'ahrefs-seo' )
			);
		}

		return $plugin_meta;
	}

	/**
	 * Checks nonce expiration on the ahrefs seo screen and refresh if needed.
	 *
	 * @since 0.10.2
	 *
	 * @param array  $response  The Heartbeat response.
	 * @param array  $data      The $_POST data sent.
	 * @param string $screen_id The screen ID.
	 * @return array The Heartbeat response.
	 */
	public function wp_refresh_nonces( $response, $data, $screen_id ) {
		if ( 'toplevel_page_' . self::SLUG_CONTENT === $screen_id && current_user_can( self::CAP_CONTENT_AUDIT_VIEW ) ) {
			$response['ahrefs-nonce'] = [
				'table_nonce' => wp_create_nonce( Ahrefs_Seo_Table::ACTION ),
			];
		}
		return $response;
	}

}
