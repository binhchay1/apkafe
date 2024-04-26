<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo;

use Exception;

/**
 * Class for work with Content Audit Schedule option.
 * Start / stop cron job using Ahrefs_Seo_Cron::get()->scheduled_audit_task_update() call when options saved.
 */
class Content_Schedule {

	private const OPTION_SCHEDULE_ENABLED    = 'ahrefs-seo-content-schedule-enabled';
	private const OPTION_SCHEDULE_RECURRENCE = 'ahrefs-seo-content-schedule-recurrence';

	private const DEFAULT_FREQUENCY = 'ahrefs_monthly';

	/**
	 * Is scheduled audit enabled
	 *
	 * @return bool
	 */
	public function is_enabled() : bool {
		return (bool) get_option( self::OPTION_SCHEDULE_ENABLED, false );
	}

	/**
	 * Return scheduled audit options as array.
	 *
	 * @return array<string, int|string> Associative array {
	 *     Array of information about the current options
	 *
	 *     @type string $frequency    One of 'ahrefs_daily', 'ahrefs_weekly', 'ahrefs_monthly'.
	 *     @type int    $day_of_week  Day of week: 0-6, 0 = Sunday is default.
	 *     @type int    $day_of_month  Day of month: 1-31.
	 *     @type int    $hour Hour, 0-23.
	 *     @type string $timezone Timezone string, default is site's timezone or UTC.
	 * }
	 */
	public function get_options() : array {
		$data = (array) get_option( self::OPTION_SCHEDULE_RECURRENCE, [] );

		$data['frequency']   = in_array( $data['frequency'] ?? '', [ 'ahrefs_daily', 'ahrefs_weekly', 'ahrefs_monthly' ], true ) ? $data['frequency'] : self::DEFAULT_FREQUENCY;
		$data['day_of_week'] = in_array( $data['day_of_week'] ?? -1, [ 0, 1, 2, 3, 4, 5, 6 ], true ) ? $data['day_of_week'] : 0; // Sunday is default.
		$d                   = intval( $data['day_of_month'] ?? -1 );
		if ( $d < 1 || $d > 31 ) {
			$data['day_of_month'] = 1; // default is 1st of month.
		}
		$t = intval( $data['hour'] ?? -1 );
		if ( $t < 0 || $t > 23 ) {
			$data['hour'] = 0; // default time is "00:00" (12:00 AM).
		}
		$tz = $data['timezone'] ?? '';

		if ( ! $this->is_valid_timezone_or_utc( $tz ) ) {
			$data['timezone'] = get_option( 'timezone_string', '' ) ?: 'UTC';
		}

		return $data;
	}

	/**
	 * Set options of scheduled audit. Add or remove cron job.
	 *
	 * @param bool   $enabled     Is scheduled updates enabled.
	 * @param string $frequency    One of 'daily', 'weekly', 'monthly'.
	 * @param int    $day_of_week  Day of week: 0-6, 0 = Sunday is default.
	 * @param int    $day_of_month  Day of month: 1-31.
	 * @param int    $hour Hour, 0-23.
	 * @param string $timezone Timezone string, default is site's timezone or UTC.
	 * @return void
	 */
	private function set_options( bool $enabled, string $frequency, int $day_of_week, int $day_of_month, int $hour, string $timezone ) : void { // phpcs:ignore:Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- false positive.
		update_option( self::OPTION_SCHEDULE_ENABLED, $enabled );
		update_option( self::OPTION_SCHEDULE_RECURRENCE, compact( 'frequency', 'day_of_week', 'day_of_month', 'hour', 'timezone' ) );
		Ahrefs_Seo_Cron::get()->scheduled_audit_task_update();
	}

	/**
	 * Set options from request.
	 * Add/update/remove cron job.
	 * Note: nonce checked before the call.
	 *
	 * @return void
	 */
	public function set_options_from_request() : void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce already checked before.
		$enabled      = ! empty( $_POST['schedule_enabled'] );
		$frequency    = isset( $_POST['schedule_frequency'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_frequency'] ) ) : '';
		$day_of_week  = isset( $_POST['schedule_day_of_week'] ) ? intval( $_POST['schedule_day_of_week'] ) : -1;
		$day_of_month = isset( $_POST['schedule_day_of_month'] ) ? intval( $_POST['schedule_day_of_month'] ) : -1;
		$hour         = isset( $_POST['schedule_hour'] ) ? intval( $_POST['schedule_hour'] ) : -1;
		$timezone     = isset( $_POST['schedule_timezone'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_timezone'] ) ) : '';
		$this->set_options( $enabled, $frequency, $day_of_week, $day_of_month, $hour, $timezone );
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Filter timezone option values.
	 * Exclude UTC+xxx/UTC-xxx values, when no corresponding timezone exists.
	 *
	 * @param string $value Options of select, like: <option value="xxx">...</option>.
	 * @return string The filtered list of option. Exclude UTC+-xxx values, if no corresponding zone exists.
	 */
	public function filter_timezone_values( string $value ) : string {
		if ( preg_match_all( '!<option.*?value="(.*?)".*?</option>!', $value, $m, PREG_SET_ORDER ) ) {
			foreach ( $m as $mm ) {
				if ( ! $this->is_valid_timezone_or_utc( $mm[1] ) ) {
					$value = str_replace( $mm[0], '', $value );
				}
			}
		}
		return $value;
	}

	/**
	 * Is a value the valid timezone or UTC value?
	 *
	 * @param string $value Value to test.
	 * @return bool
	 */
	private function is_valid_timezone_or_utc( string $value ) : bool {
		static $allowed = null;
		if ( is_null( $allowed ) ) {
			$allowed = timezone_identifiers_list();
		}
		return in_array( $value, $allowed, true ) || '' !== $this->timezone_from_utc( $value );
	}

	/**
	 * Find corresponding timezone for UTC+-xxx value.
	 *
	 * @param string $timezone Timezone string.
	 * @return string Value of timezone or empty string.
	 */
	private function timezone_from_utc( string $timezone ) : string {
		if ( preg_match( '/^UTC[+-]/', $timezone ) ) {
			$offset = intval( floatval( preg_replace( '/UTC\+?/', '', $timezone ) ) * HOUR_IN_SECONDS );

			// Get timezone name from seconds.
			$timezone_new = timezone_name_from_abbr( '', $offset, 0 );
			if ( false === $timezone_new ) {
				$timezone_new = timezone_name_from_abbr( '', $offset, 1 );
			}
			if ( false !== $timezone_new ) {
				return $timezone_new;
			}
		}
		return '';
	}

	/**
	 * Return UTC time from string with date & time, like "monday this week 12:00:00" using timezone.
	 * Note, the timestamp in UTC may point to another day of week or date.
	 *
	 * @param string $utc_date_string String with date and time info, like "monday this week 12:00:00", "2020-10-28 00:00:00".
	 * @param string $timezone Valid timezone, like "Asia/Singapore" or "Europe/London".
	 * @return int Timestamp or 0.
	 */
	private function time_from_str( string $utc_date_string, string $timezone ) : int {
		try {
			$date = date_create( $utc_date_string, timezone_open( $timezone ) ?: null );
			return false !== $date ? (int) $date->format( 'U' ) : 0;
		} catch ( Exception $e ) {
			Ahrefs_Seo::notify( $e );
		}
		return 0;
	}

	/**
	 * Calculate and return next run time using options.
	 *
	 * @return array<string, mixed> Associative array with details {
	 *
	 *     @type int    $time  Timestamp of next rune time.
	 *     @type string $frequency    One of 'ahrefs_daily', 'ahrefs_weekly', 'ahrefs_monthly'.
	 *     @type int    $interval Interval of future schedules, in seconds.
	 *     @type bool   $reschedule  Need to reschedule event on next run. Used when monthly frequency set.
	 * }
	 */
	public function next_run_time() : array {
		$options      = $this->get_options();
		$frequency    = (string) $options['frequency'];
		$day_of_week  = (int) $options['day_of_week'];
		$day_of_month = (int) $options['day_of_month'];
		$hour         = (int) $options['hour'];
		$timezone     = (string) $options['timezone'];

		$reschedule   = false;
		$time_now     = time();
		$timezone_new = $this->timezone_from_utc( $timezone );
		if ( '' === $timezone_new ) {
			$timezone_new = $timezone;
		}
		$time_next = 0;
		$interval  = 0;
		switch ( $frequency ) {
			case 'ahrefs_monthly':
				$reschedule = true;
				$interval   = MONTH_IN_SECONDS;
				$max_day    = (int) date( 't', $this->time_from_str( 'this month', $timezone_new ) ); // Number of days in the current month.
				$year_month = date( 'Y-m', $this->time_from_str( 'this month', $timezone_new ) );
				$used_day   = min( [ $max_day, $day_of_month ] ); // if the month has fewer days, then day_of_month, use last day of month.
				$time_next  = $this->time_from_str( sprintf( '%s-%02d %02d:00:00', $year_month, $used_day, $hour ), $timezone_new );
				if ( $time_next < $time_now ) { // use next month.
					$max_day    = (int) date( 't', $this->time_from_str( 'next month', $timezone_new ) );
					$year_month = date( 'Y-m', $this->time_from_str( 'next month', $timezone_new ) );
					$used_day   = min( [ $max_day, $day_of_month ] );
					$time_next  = $this->time_from_str( sprintf( '%s-%02d %02d:00:00', $year_month, $used_day, $hour ), $timezone_new );
				}
				break;
			case 'ahrefs_weekly':
				$interval  = WEEK_IN_SECONDS;
				$values    = [ // do not translate, internal use only.
					0 => 'Sunday',
					1 => 'Monday',
					2 => 'Tuesday',
					3 => 'Wednesday',
					4 => 'Thursday',
					5 => 'Friday',
					6 => 'Saturday',
				];
				$day       = $values[ $day_of_week ];
				$time_next = $this->time_from_str( sprintf( '%s this week %02d:00:00', $day, $hour ), $timezone_new );
				if ( $time_next < $time_now ) {
					$time_next += $interval; // next week.
				}
				break;
			case 'ahrefs_daily':
			default:
				$interval  = DAY_IN_SECONDS;
				$time_next = $this->time_from_str( sprintf( 'today %02d:00:00', $hour ), $timezone_new );
				if ( $time_next < $time_now ) {
					$time_next += $interval;
				}
		}

		return [
			'time'       => $time_next, // time of next run.
			'frequency'  => $frequency,
			'interval'   => $interval, // interval of future schedules.
			'reschedule' => $reschedule, // need to reschedule on each run.
		];
	}
}
