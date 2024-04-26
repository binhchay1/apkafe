<?php

namespace ahrefs\AhrefsSeo;

use Error;
use Exception;
/**
 * Abstract class for cron updates.
 */
abstract class Cron_Any {

	const OPTION_RECURRENCE_SECONDS  = 'ahrefs-seo-cron-any-recurrence';
	const DEFAULT_RECURRENCE_SECONDS = 180; // once per 3 minutes.
	/**
	 * @var int
	 */
	protected $recurrence_fast = 180; // in seconds.
	/**
	 * @var int
	 */
	protected $recurrence_slow = 300; // in seconds.
	/**
	 * Predefined name of event name.
	 * Must be filled in child classes.
	 *
	 * @var string
	 */
	protected $event_name = '';
	/**
	 * Predefined name of transient.
	 * Must be filled in child classes.
	 *
	 * @var string
	 */
	protected $transient_name = '';
	/**
	 * Constructor.
	 *
	 * @param bool $add_handlers When to add schedule interval and handlers.
	 */
	public function __construct( $add_handlers = false ) {
		$this->recurrence_fast = $this->get_recurrence_time(); // initialize from configuration.
		$this->recurrence_slow = 2 * $this->recurrence_fast;
		if ( $add_handlers ) {
			add_filter( 'cron_schedules', [ $this, 'cron_schedules_add_interval' ] ); // phpcs:ignore WordPress.VIP.CronInterval.ChangeDetected,WordPress.WP.CronInterval.ChangeDetected
			add_action( $this->event_name, [ $this, 'run_task' ] );
		}
	}
	/**
	 * Add custom schedule interval for Internal links updates.
	 *
	 * @param array<string, array> $schedules Existing schedules.
	 * @return array<string, array> Schedules with our intervals.
	 */
	public function cron_schedules_add_interval( $schedules ) {
		// Note: callback, do not use parameter types.
		if ( ! is_array( $schedules ) ) {
			$schedules = [];
		}
		if ( ! isset( $schedules['ahrefs_fast'] ) ) {
			$schedules['ahrefs_fast'] = [
				'interval' => $this->recurrence_fast,
				'display'  => __( 'Ahrefs SEO', 'ahrefs-seo' ) . ': ' . __( 'fast update', 'ahrefs-seo' ),
			];
		}
		if ( ! isset( $schedules['ahrefs_slow'] ) ) {
			$schedules['ahrefs_slow'] = [
				'interval' => $this->recurrence_slow,
				'display'  => __( 'Ahrefs SEO', 'ahrefs-seo' ) . ': ' . __( 'slow update', 'ahrefs-seo' ),
			];
		}
		return $schedules;
	}
	/**
	 * Start links fast or slow updates or change scheduled recurrence/next time.
	 *
	 * @param bool $fast_updates True: run fast updates, False: run slow updates.
	 * @param bool $is_recurrence_updated Is new recurrence value different from previous? Need to recreate existing task. No need to start new task.
	 * @return void
	 */
	public function start_tasks( $fast_updates = true, $is_recurrence_updated = false ) {
		Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ . (string) wp_json_encode( func_get_args() ) );
		$recurrence = $fast_updates ? 'ahrefs_fast' : 'ahrefs_slow';
		$next_time  = wp_next_scheduled( $this->event_name );
		if ( ! $next_time ) {
			if ( ! $is_recurrence_updated ) {
				// start next task, nearest call in 15 seconds.
				$desired_time = time() + 15;
				if ( false === wp_schedule_event( $desired_time, $recurrence, $this->event_name ) ) {
					Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( 'Cron schedule failed ' . (string) wp_json_encode( [ $desired_time, $recurrence, $this->event_name, time() ] ) ) );
				}
			}
		} else {
			$existing = wp_get_schedule( $this->event_name );
			// update event if recurrence is different from existing or scheduled call is after longest wait time for fast update.
			$desired_time = time() + $this->recurrence_fast;
			if ( $is_recurrence_updated || $existing !== $recurrence || $next_time > $desired_time ) {
				wp_clear_scheduled_hook( $this->event_name );
				if ( false === wp_schedule_event( $desired_time, $recurrence, $this->event_name ) ) {
					Ahrefs_Seo::notify( new Ahrefs_Seo_Exception( 'Cron schedule failed ' . (string) wp_json_encode( [ $desired_time, $recurrence, $this->event_name, time() ] ) ) );
				}
			}
		}
	}
	/**
	 * Stop tasks, remove scheduled event
	 *
	 * @return void
	 */
	public function stop_tasks() {
		Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ );
		if ( wp_next_scheduled( $this->event_name ) ) {
			wp_clear_scheduled_hook( $this->event_name );
		}
	}
	/**
	 * Apply time limits
	 *
	 * @since 0.7.3
	 *
	 * @return void
	 */
	protected function apply_time_limits() {
		Ahrefs_Seo::set_time_limit( 300 ); // call it before set transient, because it can update transient time.
	}
	/**
	 * Run the task
	 *
	 * @return void
	 */
	public function run_task() {
		try {
			$this->apply_time_limits();
			Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ . sprintf( ' Transient time: %d', Ahrefs_Seo::transient_time() ) );
			if ( ! $this->is_busy() ) {
				set_transient( $this->transient_name, true, Ahrefs_Seo::transient_time() );
				Ahrefs_Seo::ignore_user_abort( true );
				// run until finished or time limit reached.
				while ( $executed = $this->execute() ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
					if ( Ahrefs_Seo::should_finish( null, 33 ) ) { // allow 2/3 of all time to update internal links.
						Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ . ' exit earlier.' );
						break;
					}
				}
				if ( ! $executed ) {
					// Nothing to update now, but there may be tasks, blocked by something.
					if ( $this->has_slow_tasks() ) {
						$this->start_tasks( false ); // switch to slow update mode.
					} else {
						$this->stop_tasks(); // all finished: stop cron task.
					}
				}
				delete_transient( $this->transient_name );
			}
			Ahrefs_Seo::breadcrumbs( get_called_class() . '::' . __FUNCTION__ . ' exit.' );
		} catch ( Error $e ) {
			Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__, __( 'Run task failed', 'ahrefs-seo' ) );
		} catch ( Exception $e ) {
			Ahrefs_Seo::notify( $e, 'Run task failed' );
		}
	}
	/**
	 * Is task already running?
	 *
	 * @since 0.9.2
	 *
	 * @return bool
	 */
	protected function is_busy() {
		return ! empty( get_transient( $this->transient_name ) );
	}
	/**
	 * Set recurrence time
	 *
	 * @since 0.9.2
	 *
	 * @param int $seconds Time between subsequent requests, in seconds.
	 * @return void
	 */
	public function set_recurrence_time( $seconds ) {
		if ( $seconds < 30 ) {
			$seconds = self::DEFAULT_RECURRENCE_SECONDS;
		}
		update_option( self::OPTION_RECURRENCE_SECONDS, $seconds );
		$this->recurrence_fast = $seconds;
		$this->recurrence_slow = 2 * $this->recurrence_fast;
		$old_value             = $this->get_recurrence_time();
		// does the new value different from existing?
		if ( $old_value !== $seconds ) {
			$this->start_tasks( true, true ); // update existing tasks.
		}
	}
	/**
	 * Get recurrence time
	 *
	 * @since 0.9.2
	 *
	 * @return int Time between subsequent requests, in seconds.
	 */
	public function get_recurrence_time() {
		$result = (int) get_option( self::OPTION_RECURRENCE_SECONDS, self::DEFAULT_RECURRENCE_SECONDS );
		return $result >= 30 ? $result : self::DEFAULT_RECURRENCE_SECONDS;
	}
	/**
	 * Execute an update.
	 *
	 * @return bool True if task finished, false if nothing to run.
	 */
	public abstract function execute();
	/**
	 * Has more tasks, but need to switch to slow mode.
	 *
	 * @return bool True if we have pending tasks.
	 */
	public abstract function has_slow_tasks();
}