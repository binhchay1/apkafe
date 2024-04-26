<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;
use ahrefs\AhrefsSeo\Messages\Message_Access;
use ahrefs\AhrefsSeo\Messages\Message_Error;
use ahrefs\AhrefsSeo\Messages\Message_Error_Single;
use ahrefs\AhrefsSeo\Messages\Message_Notice;
use ahrefs\AhrefsSeo\Messages\Message_Tip;
use ahrefs\AhrefsSeo\Messages\Message_Tip_Incompatible;
use Error;
use Exception;

/**
 * Compatibility with other plugins class.
 *
 * Predict errors: quick compatibility check:
 * - run before content audit start,
 * - on each Wizard page,
 * - on Ahrefs, on Google accounts pages.
 * Catch compatibility errors:
 * - during content audit;
 * - on API calls.
 *
 * On compatibility error the Content audit is paused.
 * We save reason of error (plugins or theme) and check, are these plugins active or not.
 * If at least one is inactive: try to unpause Content audit.
 *
 * @since 0.7.4
 */
class Ahrefs_Seo_Compatibility {

	/**
	 * @var string|null Full message with last error found or empty string.
	 */
	private static $last_message = null;
	/**
	 * @var string[]|null
	 */
	private static $plugins_list;
	/**
	 * @var string[]|null
	 */
	private static $themes_list;
	/**
	 * @var string[]|null
	 */
	private static $files_list;
	/**
	 * @var string[]
	 */
	private static $plugins_slugs = [];
	/**
	 * @var string[]
	 */
	private static $theme_slugs = [];
	/** @var string[] */
	private static $displayed_messages = [];
	/** @var string One of Message::TYPE_* */
	private static $type = 'tip-compatibility';

	/**
	 * Check required classes and libraries.
	 * Will stop Content audit on incompatibility.
	 * DO NOT save any 'compatibility' message.
	 *
	 * @return bool True - no issues found.
	 */
	public static function quick_compatibility_check() : bool {
		self::$plugins_slugs = [];
		self::$theme_slugs   = [];
		self::$last_message  = '';
		return true;
	}

	/**
	 * Handle Error, search reason in plugins or themes, submit report.
	 * Save 'compatibility tip' or 'general tip' message.
	 *
	 * @param Error       $e Caught PHP Error.
	 * @param string      $current_method Method where error was caught.
	 * @param string      $current_file File where error was caught.
	 * @param null|string $type Will show it to user if it is not theme or plugin issue, will submit it with report. Translated string.
	 * @return string User friendly error reason.
	 */
	public static function on_type_error( Error $e, string $current_method, string $current_file, ?string $type = null ) : string {
		self::$plugins_slugs = [];
		self::$theme_slugs   = [];
		$result              = __( 'Unexpected error', 'ahrefs-seo' );
		self::$type          = Message::TYPE_ERROR;
		try {
			$file                = $e->getFile();
			$file_relative       = str_replace( wp_normalize_path( ABSPATH ), '', wp_normalize_path( $file ) ); // path inside WordPress root dir.
			$line                = $e->getLine();
			$message             = $e->getMessage();
			$error_type          = $type ?? get_class( $e );
			self::$plugins_slugs = [];
			self::$theme_slugs   = [];
			self::$plugins_list  = [];
			self::$themes_list   = [];
			self::$files_list    = [];
			$common_reason       = sprintf( '"%s" %s in file %s [%d]: %s', $error_type, ( false === stripos( $error_type, 'error' ) ? __( 'error', 'ahrefs-seo' ) : '' ), $file_relative, $line, $message );
			$trace               = $e->getTrace();
			array_unshift(
				$trace,
				[
					'file'     => $file,
					'function' => '',
				]
			); // add original error position too.

			$files = self::analyze_stack( $trace, $current_method, $current_file );
			foreach ( $files as $_file ) {
				self::search_source( $_file, self::$plugins_list, self::$themes_list, self::$files_list );
			}
			$result = self::return_error_message( self::$plugins_list, self::$themes_list, self::$files_list, $common_reason );
			if ( is_null( $result ) ) {
				/* translators: 1: error type, 2: file path */
				$result = sprintf( __( 'Unexpected "%1$s" error in file %2$s', 'ahrefs-seo' ) . ' [%d]: %s', $error_type, $file_relative, $line, $message ); // default message.
				Ahrefs_Seo::notify( $e, $error_type ); // source error only.
			} else {
				Ahrefs_Seo::notify( new Ahrefs_Seo_Compatibility_Exception( $result . ' ' . $common_reason, 0, $e ), $error_type ); // some incompatibility found in other plugin or theme. Report with source reason.
			}
		} catch ( Exception $ee ) {
			Ahrefs_Seo::notify( $ee, 'error at handler' );
			Ahrefs_Seo::notify( $e, 'initial error' );
		}
		self::$last_message = $result;
		$reason             = self::get_current_incompatibility();
		Content_Audit::audit_stop( $reason ? [ $reason ] : [] );
		return $result ?? __( 'Unexpected error', 'ahrefs-seo' );
	}

	/**
	 * Set that message displayed
	 *
	 * @param string $message Message text.
	 * @return void
	 */
	public static function set_message_displayed( string $message = '' ) : void {
		self::$displayed_messages[] = $message;
	}

	/**
	 * Clean message if it already displayed
	 *
	 * @param string $message New error message, in-out parameter.
	 * @return bool True if message filtered (already displayed).
	 */
	public static function filter_messages( string &$message ) : bool {
		if ( count( self::$displayed_messages ) ) {
			foreach ( self::$displayed_messages as $full_text ) {
				if ( false !== stripos( $full_text, $message ) ) {
					$message = '';
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Return path of files, called before the Error was caught.
	 *
	 * @param array  $trace Trace data.
	 * @param string $method Method.
	 * @param string $file File.
	 * @return string[] List of directories from trace, where error happened.
	 */
	private static function analyze_stack( array $trace, string $method, string $file ) : array {
		$result = [];
		$file   = wp_normalize_path( $file );
		foreach ( $trace as $item ) {
			if ( isset( $item['file'] ) ) {
				$path = wp_normalize_path( $item['file'] );
				if ( isset( $item['class'] ) &&
					( $file === $path && $method === $item['class'] . '::' . $item['function'] // place where Error caught.
						|| false !== strpos( $path, 'class-wp-hook.php' ) && 'ajax_content_ping' === $item['function'] && 'ahrefs\\AhrefsSeo\\Ahrefs_Seo_Screen_Content' === $item['class'] // ping request.
						|| false !== strpos( $path, 'class-wp-hook.php' ) && 'ajax_progress' === $item['function'] && 'ahrefs\\AhrefsSeo\\Ahrefs_Seo_Data_Wizard' === $item['class'] // wizard progress.
						|| false !== strpos( $path, 'class-cron-any.php' ) && 'run_task' === $item['function'] && 'ahrefs\\AhrefsSeo\\Cron_Any' === $item['class'] // cron fast or cron scheduled audit.
						|| false !== strpos( $path, 'class-ahrefs-seo-view.php' ) && 'show' === $item['function'] && 'ahrefs\\AhrefsSeo\\Ahrefs_Seo_View' === $item['class'] // show dashboard.
					)
					) {
						break;
				} else {
					$result[] = $path;
				}
			}
		}
		return array_values( array_unique( $result ) );
	}

	/**
	 * Search is file a part of another plugin or theme. Fill lists with result.
	 *
	 * @param string   $file File path and name.
	 * @param string[] $plugins_list Plugins list.
	 * @param string[] $themes_list Themes list.
	 * @param string[] $files_list Files list.
	 * @return void
	 */
	protected static function search_source( string $file, array &$plugins_list, array &$themes_list, array &$files_list ) : void {
		static $themes_path = null;
		if ( is_null( $themes_path ) ) {
			$themes_path = dirname( wp_normalize_path( get_template_directory() ) );
		}
		if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
			$files_list[] = $file;
			return;
		}
		$plugin_base = plugin_basename( $file );
		if ( strlen( $plugin_base ) < strlen( $file ) ) { // conflict inside plugins dir.
			$path = explode( '/', $plugin_base );
			if ( basename( AHREFS_SEO_DIR ) === $path[0] ) {
				return; // exclude self.
			}

			$all_plugins   = get_option( 'active_plugins' ) ?: [];
			$plugins_found = [];
			if ( ! is_array( $all_plugins ) ) {
				$all_plugins = [];
			}

			if ( 1 === count( $path ) ) { // plugin at the root, without individual folder.
				$plugin_name   = $path[0];
				$plugins_found = array_filter(
					$all_plugins,
					function( $value ) use ( $plugin_name ) {
						return $value === $plugin_name;
					}
				);
			} else {
				$plugin_dir    = $path[0];
				$plugins_found = array_filter(
					$all_plugins,
					function( $value ) use ( $plugin_dir ) {
						$path = explode( '/', $value );
						return $path[0] === $plugin_dir;
					}
				);
			}
			if ( count( $plugins_found ) ) {
				if ( ! function_exists( 'get_plugin_data' ) ) { // already defined, if called after admin_init.
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				foreach ( $plugins_found as $plugin_slug ) {
					self::$plugins_slugs[] = $plugin_slug;
				}

				array_walk(
					$plugins_found,
					function( $plugin_slug ) use ( &$plugins_list ) {
						$fields = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_slug );
						/* Translators: %s: version string */
						$plugins_list[] = ( $fields['Name'] ?? $plugin_slug ) . ( ! empty( $fields['Version'] ) ? ' ' . sprintf( __( 'version %s', 'ahrefs-seo' ), $fields['Version'] ) : '' );
					}
				);
				return;
			}
		} else {
			$file_path_normalized = wp_normalize_path( $file );
			if ( 0 === strpos( $file_path_normalized, $themes_path ) ) {
				$file_path_normalized = substr( $file_path_normalized, strlen( $themes_path ) + 1 );
				$path                 = explode( '/', $file_path_normalized );
				$theme_found          = wp_get_theme( $path[0] );
				if ( $theme_found->exists() ) {
					self::$theme_slugs[] = $path[0];

					$version       = is_string( $theme_found->get( 'Version' ) ) ? $theme_found->get( 'Version' ) : '';
					$author        = is_string( $theme_found->get( 'Author' ) ) ? $theme_found->get( 'Author' ) : '';
					$name          = is_string( $theme_found->get( 'Name' ) ) ? $theme_found->get( 'Name' ) : "{$path[0]}";
					$themes_list[] = sprintf(
						'"%s"%s%s',
						"$name",
						/* Translators: %s: version string */
						( $version ? ' ' . sprintf( __( 'version %s', 'ahrefs-seo' ), "$version" ) : '' ),
						/* Translators: %s: author name */
						( $author ? ' ' . sprintf( __( 'by %s', 'ahrefs-seo' ), $author ) : '' )
					);
					return;
				}
			}
		}
		$files_list[] = $file;
	}

	/**
	 * Find error reason in other plugin or theme or file.
	 *
	 * @param string[] $plugins_list Plugins list.
	 * @param string[] $themes_list Themes list.
	 * @param string[] $files_list Files list.
	 * @param string   $common_reason Common reason.
	 * @return string|null
	 */
	protected static function return_error_message( $plugins_list, $themes_list, $files_list, $common_reason ) : ?string {
		$result     = null;
		self::$type = Message::TYPE_TIP_COMPATIBILITY;
		if ( count( $plugins_list ) ) {
			$plugins_list = array_unique( $plugins_list );
			$result       = sprintf(
				/* translators: 1: plugin name or comma separated list of plugin names, 2: expanded to 'Ahrefs SEO' */
				_n(
					'We’ve scanned your WordPress environment and discovered %1$s which is conflicting with the %2$s plugin. Please pause the plugin by deactivating it before running content audit again.',
					'We’ve scanned your WordPress environment and discovered %1$s which are conflicting with the %2$s plugin. Please pause these plugins by deactivating them before running content audit again.',
					count( $plugins_list ),
					'ahrefs-seo'
				),
				implode( ', ', $plugins_list ),
				__( 'Ahrefs SEO', 'ahrefs-seo' )
			);
		} elseif ( count( $themes_list ) ) {
			$themes_list = array_unique( $themes_list );
			/* translators: 1: theme name, 2: expanded to 'Ahrefs SEO' */
			$result = sprintf( __( 'We’ve scanned your WordPress environment and discovered %1$s, which is incompatible with the %2$s plugin. Please switch the theme before running content audit again.', 'ahrefs-seo' ), implode( ', ', $themes_list ), /* translators: Plugin name */  __( 'Ahrefs SEO', 'ahrefs-seo' ) );
		} elseif ( count( $files_list ) ) {
			$files_list = array_map(
				function( $value ) {
					return str_replace( ABSPATH, '', $value ); // remove part of path before WordPress root dir.
				},
				$files_list
			);

			$result = sprintf(
				/* translators: 1: file name or comma separated list of file names, 2: expanded to 'Ahrefs SEO' */
				_n(
					'File %1$s has code incompatible with %2$s plugin.',
					'One of files %1$s have code incompatible with %2$s plugin.',
					count( $files_list ),
					'ahrefs-seo'
				),
				implode( ',', $files_list ),
				__( 'Ahrefs SEO', 'ahrefs-seo' )
			);
		} elseif ( $common_reason ) {
			self::$type = Message::TYPE_ERROR;
			$result     = sprintf( '%s', $common_reason );
		}
		return $result;
	}

	/**
	 * Recheck saved incompatibility error.
	 *
	 * @since 0.7.5
	 *
	 * @return bool
	 */
	public static function recheck_saved_incompatibility() : bool {
		$messages = Content_Audit::audit_get_paused_messages();
		if ( ! is_null( $messages ) ) {
			foreach ( $messages as $message ) {
				if ( $message instanceof Message_Tip_Incompatible ) { // maybe compatibility issue was resolved?
					$plugins = $message->get_plugins();
					$themes  = $message->get_themes();

					// check if one of plugins or themes is inactive.
					if ( ( count( $plugins ) + count( $themes ) ) && ( count( $plugins ) + count( $themes ) > self::count_active_plugins( $plugins ) + self::count_active_themes( $themes ) ) ) {
						self::quick_compatibility_check();
						Content_Audit::audit_resume();
						break;
					}
				}
			}
		}
		return empty( self::$last_message );
	}

	/**
	 * Get last compatibility instance if exists
	 *
	 * @since 0.7.5
	 *
	 * @return Message|null
	 */
	public static function get_current_incompatibility() : ?Message {
		return self::get_incompatible_message( self::$plugins_slugs, self::$theme_slugs, self::$last_message ?? '' );
	}

	/**
	 * Get current incompatibility message instance for parameters set
	 *
	 * @param string[] $plugins Plugins list.
	 * @param string[] $themes Themes list.
	 * @param string   $message Message text.
	 * @return Message_Error|Message_Error_Single|Message_Notice|Message_Tip|Message_Tip_Incompatible|Message_Access
	 */
	protected static function get_incompatible_message( array $plugins, array $themes, string $message ) : ?Message {
		if ( count( $plugins ) + count( $themes ) > 0 || '' !== $message ) {
			$title   = __( 'Incompatibility Found', 'ahrefs-seo' );
			$buttons = [];
			if ( count( $plugins ) ) {
				$title     = __( 'Incompatible Plugins Found', 'ahrefs-seo' );
				$buttons[] = 'plugins';
			} elseif ( count( $themes ) ) {
				$title     = __( 'Incompatible Theme Found', 'ahrefs-seo' );
				$buttons[] = 'themes';
			}
			$fields = [
				'type'    => self::$type,
				'title'   => $title,
				'message' => $message,
				'buttons' => $buttons,
				'plugins' => $plugins,
				'themes'  => $themes,
			];
			return Message::create( $fields );
		}

		return null;
	}

	/**
	 * Count number of active plugins from given list
	 *
	 * @since 0.7.5
	 *
	 * @param string[] $plugins Plugin slugs, same format as option "active_plugins" used.
	 * @return int
	 */
	protected static function count_active_plugins( array $plugins ) : int {
		$result      = 0;
		$all_plugins = get_option( 'active_plugins' ) ?: [];
		foreach ( $plugins as $plugin_slug ) {
			if ( in_array( $plugin_slug, $all_plugins, true ) ) {
				$result++;
			}
		}
		return $result;
	}

	/**
	 * Count number of active themes from given list
	 *
	 * @since 0.7.5
	 *
	 * @param string[] $themes Theme slugs.
	 * @return int
	 */
	protected static function count_active_themes( array $themes ) : int {
		$result = 0;
		if ( count( $themes ) ) {
			foreach ( $themes as $theme_slug ) {
				$theme_found = wp_get_theme( $theme_slug );
				if ( $theme_found->exists() && $theme_found->get_stylesheet() === wp_get_theme()->get_stylesheet() ) {
					$result++;
				}
			}
		}
		return $result;
	}

}
