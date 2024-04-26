<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;
/**
 * Save and return errors from GA, GSC, Ahrefs, WordPress.
 *
 * There are 3 types of errors:
 * A. Permanent errors, display as tip, content audit is paused;
 *   1. Compatibility: checked before audit start: show it immediately;
 *   2. Compatibility error caught during content audit run;
 *   3. Ahrefs or Google account not connected;
 *   4. Ahrefs account limited;
 *   5. Not suitable account (gsc account has incorrect domain).
 *   Show: on page loaded, on each ping.
 *   Disappear: when [audit paused] clicked.
 *
 * B. Temporary errors, display as notice:
 *   Current worker paused on these error for some time.
 *   If we have worker, with pause longer from 1 minute - this mean temporary error.
 *   1. ga, gsc returned rate error;
 *   2. ga, gsc, ahrefs returned 5xx error;
 *   3. ga, gsc, ahrefs returned connection error;
 *   4. page of current site returned connection or 5xx error.
 *   Show: when content audit page loaded or with each ping.
 *   Disappear: when pause is lower than 1 minute: on page reload or on each ping.
 *
 * C: Unexpected errors, Unexpected compatibility errors (when source not detected): display as error with "please contact Ahrefs".
 *   Show: when content audit page loaded or with each ping.
 *   Disappear: when page reloaded, when user closed message.
 */
class Ahrefs_Seo_Errors {

	const OPTION_ERRORS_LIST = 'ahrefs-seo-errors-list-v2';
	/**
	 * @var array<array<string,string|string[]>>
	 */
	private static $errors = [];
	/**
	 * @var Ahrefs_Seo_Api|null
	 */
	private static $api = null;
	/**
	 * @var Ahrefs_Seo_Analytics|null
	 */
	private static $analytics = null;
	/**
	 * Get messages from current thread only
	 *
	 * @param string|null $source Source.
	 * @param string|null $type Type.
	 * @return array<array<string,string|string[]>>
	 */
	public static function get_current_messages( $source = null, $type = null ) {
		$result = [];
		// filter error message for compatibility.
		if ( ! empty( self::$errors ) ) {
			foreach ( self::$errors as $key => $values ) {
				if ( is_string( $values['message'] ) && Ahrefs_Seo_Compatibility::filter_messages( $values['message'] ) ) {
					unset( self::$errors[ $key ] );
				}
			}
		}
		if ( is_null( $source ) && is_null( $type ) ) {
			$result       = self::$errors;
			self::$errors = [];
		} elseif ( count( self::$errors ) ) { // filter by source and/or type.
			foreach ( self::$errors as $key => $value ) {
				if ( ( is_null( $source ) || ( isset( $value['source'] ) ? $value['source'] : '' ) === $source ) && ( is_null( $type ) || ( isset( $value['type'] ) ? $value['type'] : '' ) === $type ) ) {
					$result[] = $value;
					unset( self::$errors[ $key ] );
				}
			}
		}
		return $result;
	}
	/**
	 * Get all saved messages and clear list
	 *
	 * @param string|null $source Filter by source if not null.
	 * @param string|null $type   Filter by type if not null.
	 * @return array<array<string,string|string[]>> Saved messages with requested source and type.
	 */
	public static function get_saved_messages( $source = null, $type = null ) {
		$result = [];
		$values = get_option( self::OPTION_ERRORS_LIST, [] );
		if ( ! is_array( $values ) ) {
			$values = [];
		}
		if ( $values ) {
			$initial_count = count( $values );
			foreach ( $values as $key => $value ) { // do not show message or notice with the same content as compatibility tip already displayed.
				if ( Ahrefs_Seo_Compatibility::filter_messages( $value['message'] ) ) {
					unset( self::$errors[ $key ] );
					unset( $values[ $key ] );
				}
			}
			if ( is_null( $source ) && is_null( $type ) ) { // reset all saved errors.
				update_option( self::OPTION_ERRORS_LIST, [], false ); // no need to always autoload it.
				self::$errors = [];
				return $values;
			} else { // filter by source and/or type.
				foreach ( $values as $key => $value ) {
					if ( ( is_null( $source ) || ( isset( $value['source'] ) ? $value['source'] : '' ) === $source ) && ( is_null( $type ) || ( isset( $value['type'] ) ? $value['type'] : '' ) === $type ) ) {
						$result[] = $value;
						unset( $values[ $key ] );
						unset( self::$errors[ $key ] );
					}
				}
				if ( count( $values ) !== $initial_count ) {
					update_option( self::OPTION_ERRORS_LIST, $values, false ); // no need to always autoload it.
				}
			}
		}
		return $result;
	}
	/**
	 * Clean all messages or messages by source.
	 *
	 * @since 0.7.5
	 *
	 * @param string|null $source Filter by source if not null.
	 * @return void
	 */
	public static function clean_messages( $source = null ) {
		if ( is_null( $source ) ) { // reset all saved errors.
			update_option( self::OPTION_ERRORS_LIST, [], false ); // no need to always autoload it.
			self::$errors = [];
		} else { // filter by source.
			$values = get_option( self::OPTION_ERRORS_LIST, [] );
			if ( ! is_array( $values ) ) {
				$values = [];
			}
			if ( $values ) {
				$initial_count = count( $values );
				foreach ( $values as $key => $value ) {
					if ( $source === $value['source'] ) {
						unset( $values[ $key ] );
						unset( self::$errors[ $key ] );
					}
				}
				if ( count( $values ) !== $initial_count ) {
					update_option( self::OPTION_ERRORS_LIST, $values, false ); // no need to always autoload it.
				}
			}
		}
	}
	/**
	 * Save message
	 *
	 * @param string      $source What API is source of message.
	 *          from API: 'ahrefs','google','noindex'.
	 *          from compatibility: 'compatibility','general'.
	 *          from DB: 'database'.
	 *          post not found: 'WordPress'.
	 *          general error: 'content audit'.
	 * @param string|null $message Message, null if no need to show it.
	 * @param string|null $type One of Message::TYPE_*: 'error', 'notice', 'tip'.
	 * @return void
	 */
	public static function save_message( $source, $message = null, $type = null ) {
		if ( ! is_null( $message ) ) {
			$error  = [
				'source'  => $source,
				'message' => $message,
				'type'    => isset( $type ) ? $type : Message::TYPE_ERROR,
				'title'   => self::get_title_for_source( $source ),
			];
			$result = get_option( self::OPTION_ERRORS_LIST, [] );
			if ( ! is_array( $result ) ) {
				$result = [];
			}
			$index = empty( $result ) ? 0 : (int) max( array_keys( $result ) ) + 1;
			// same key used for both arrays, so we can clean same events in both of them.
			self::$errors[ $index ] = $error;
			$result[ $index ]       = $error;
			update_option( self::OPTION_ERRORS_LIST, $result, false ); // no need to always autoload it.
		}
	}
	/**
	 * Has some "stop" error. Accounts are not connected, not set or compatibility check failed.
	 *
	 * @param bool $check_compatibility Check compatibility.
	 *
	 * @return bool False if no stop errors found.
	 * @since 0.7.5
	 */
	public static function has_stop_error( $check_compatibility = false ) {
		return ! empty( self::check_stop_status( $check_compatibility ) );
	}
	/**
	 * Check stop errors status
	 *
	 * @param bool $check_compatibility Run compatibility test too.
	 *
	 * @return Message[]|null Array with error messages or null.
	 * @since 0.7.5
	 */
	public static function check_stop_status( $check_compatibility = false ) {
		$messages = [];
		$last     = Ahrefs_Seo_Compatibility::get_current_incompatibility();
		if ( $last ) {
			$messages[] = $last;
		}
		$ahrefs            = isset( self::$api ) ? self::$api : Ahrefs_Seo_Api::get();
		$analytics         = isset( self::$analytics ) ? self::$analytics : Ahrefs_Seo_Analytics::get();
		$no_ahrefs_account = $ahrefs->is_disconnected();
		$no_google_account = ! $analytics->get_data_tokens()->is_token_set() || ! $analytics->is_ua_set() || ! $analytics->is_gsc_set();
		if ( $no_ahrefs_account || $no_google_account ) {
			$messages[] = Message::account_disconnected( $no_ahrefs_account, $no_google_account );
		}
		if ( ! $ahrefs->is_disconnected() && $ahrefs->is_limited_account( true ) ) { // ahrefs_limited.
			$messages[] = Message::ahrefs_limited();
		}
		if ( false === $analytics->is_gsc_account_correct() || false === $analytics->is_ga_account_correct() ) { // not_suitable_account.
			$messages[] = Message::not_suitable_account();
		}
		if ( $check_compatibility ) {
			if ( ! Ahrefs_Seo_Compatibility::quick_compatibility_check() ) {
				$message = Ahrefs_Seo_Compatibility::get_current_incompatibility();
				if ( ! is_null( $message ) ) {
					$messages[] = $message;
				}
			}
		}
		if ( count( $messages ) > 0 ) {
			Content_Audit::audit_stop( $messages );
		}
		return count( $messages ) ? $messages : null;
	}
	/**
	 * Show stop errors.
	 * Filter already displayed messages. Skip duplicated messages.
	 *
	 * @since 0.7.5
	 *
	 * @param Message[]|null $messages Messages from result of check_stop_status().
	 * @param string         $already_displayed Id of already displayed stop errors, space is separator.
	 * @return bool Need to clean some of already displayed messages.
	 */
	public static function show_stop_errors( array $messages = null, $already_displayed = '' ) {
		$old_set = explode( ' ', $already_displayed );
		if ( ! empty( $messages ) ) { // has new messages.
			$new_set = array_unique(
				array_map(
					function ( Message $message ) {
						return $message->get_id();
					},
					$messages
				)
			);
			if ( count( $old_set ) !== count( $new_set ) || count( array_diff( $new_set, $old_set ) ) ) { // something changes in messages set.
				$ids = [];
				foreach ( $messages as $message ) {
					if ( ! in_array( $message->get_id(), $ids, true ) ) {
						$message->show();
						$ids[] = $message->get_id();
					}
				}
				return true;
			} else {
				return false; // no need to update content.
			}
		}
		return 0 !== count( $old_set ); // no new messages and no old messages mean no need to clean.
	}
	/**
	 * Get title for source
	 *
	 * @since 0.7.5
	 *
	 * @param string $source Source.
	 * @return string
	 */
	public static function get_title_for_source( $source ) {
		switch ( strtolower( $source ) ) {
			case 'ahrefs':
				return __( 'Ahrefs API', 'ahrefs-seo' );
			case 'google':
				return __( 'GSC/GA API', 'ahrefs-seo' );
			case 'compatibility':
				return __( 'Compatibility', 'ahrefs-seo' );
			case 'general':
				return __( 'General', 'ahrefs-seo' );
			case 'noindex':
			case 'wordpress': // phpcs:ignore WordPress.WP.CapitalPDangit.Misspelled,WordPress.WP.CapitalPDangit.MisspelledInText
				return __( 'WordPress', 'ahrefs-seo' );
			case 'content audit':
				return __( 'Content Audit', 'ahrefs-seo' );
		}
		return ucfirst( $source );
	}
	/**
	 * Set API helper method
	 *
	 * @since 0.7.5
	 *
	 * @param Ahrefs_Seo_Api|null       $api API instance.
	 * @param Ahrefs_Seo_Analytics|null $analytics Analytics instance.
	 * @return void
	 */
	public static function set_api( Ahrefs_Seo_Api $api = null, Ahrefs_Seo_Analytics $analytics = null ) {
		if ( $api ) {
			self::$api = $api;
		}
		if ( $analytics ) {
			self::$analytics = $analytics;
		}
	}
	/**
	 * Group unique error messages and return them.
	 *
	 * @param array<array<string,string|string[]>> $messages Error messages list.
	 *
	 * @return array<string,array>
	 * @since 0.10.1
	 */
	public static function unique_errors( array $messages ) {
		$unique = [];
		foreach ( $messages as $item ) {
			if ( is_string( $item['message'] ) ) {
				$key = md5( $item['message'] ); // unique messages, any source.
				if ( ! isset( $unique[ $key ] ) || ! isset( $unique[ $key ]['count'] ) ) {
					$unique[ $key ]          = $item;
					$unique[ $key ]['count'] = 1;
				} else {
					$unique[ $key ]['count']++;
				}
			}
		}
		return $unique;
	}
}