<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Workers;

use ahrefs\AhrefsSeo\Ahrefs_Seo;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Abstract_Api;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Compatibility;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Errors;
use ahrefs\AhrefsSeo\Ahrefs_Seo_Exception;
use ahrefs\AhrefsSeo\Content_Audit;
use ahrefs\AhrefsSeo\Post_Tax;
use Error;
use Exception;
use InvalidArgumentException;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use ahrefs\AhrefsSeo_Vendor\Google_Service_Exception;
use Throwable;

/**
 * Abstract Worker class.
 * Used for loading details for posts during content audit.
 *
 * @since 0.7.3
 */
abstract class Worker {

	public const API_NAME                = '';
	protected const OPTION_PREFIX_WAIT   = 'ahrefs-seo-wwait';
	protected const OPTION_PREFIX_LOCK   = 'ahrefs-seo-wlock';
	protected const MAX_UPDATE_TIME_AJAX = 15;
	protected const MAX_UPDATE_TIME_CRON = 15;

	/**
	 * @var string One of 'traffic, 'backlinks', 'keywords', 'position', 'isnoindex'.
	 */
	protected const WHAT_TO_UPDATE = '';

	/**
	 * @var int Load up to (number) items in same request.
	 */
	protected $items_at_once = 5;

	/**
	 * Minimal delay after update_posts() failed. Real delay calculated in Worker::on_rate_error() method.
	 *
	 * @var float
	 */
	protected $pause_after_fail = 30.0;

	/** @var float Delay after successful request to API */
	protected $pause_after_success = 1.5;

	/**
	 * @var Content_Audit Content audit instance.
	 */
	protected $content_audit;

	/**
	 * @var int|null
	 */
	protected $snapshot_id;

	/**
	 * @var null|Ahrefs_Seo_Abstract_Api
	 */
	protected $api = null;

	/**
	 * @var bool|null
	 */
	protected $has_pending_items = null;
	/**
	 * @var bool
	 */
	protected $is_cron = false;
	/**
	 * @var float
	 */
	protected $time_start = 0.0;
	/**
	 * @var bool
	 */
	protected $has_rate_error = false;

	/**
	 * Constructor
	 *
	 * @param Content_Audit|null                 $content_audit Content audit instance.
	 * @param Ahrefs_Seo_Abstract_Api|null|mixed $api What API is assigned to current worker.
	 * @param bool                               $is_cron Executed using cron.
	 */
	public function __construct( ?Content_Audit $content_audit = null, $api = null, bool $is_cron = false ) {
		$this->content_audit = $content_audit ?? new Content_Audit();
		$this->snapshot_id   = $this->content_audit->get_snapshot_id();
		$this->is_cron       = $is_cron;
		if ( ! is_null( $api ) && ( $api instanceof Ahrefs_Seo_Abstract_Api ) ) {
			$this->api = $api;
		}
		$this->time_start = microtime( true );
		if ( is_null( $this->snapshot_id ) ) { // nothing to update if snapshot is missing.
			$this->has_pending_items = false;
		}
	}

	/**
	 * Run update of current snapshot.
	 * Main method.
	 *
	 * @return bool Has something to update in next run.
	 */
	public function execute() : bool {
		$result = null;
		if ( false === $this->has_pending_tasks() ) {
			return false; // nothing to update.
		}
		if ( $this->should_finish() ) {
			return false; // nothing to update in next run.
		}
		if ( $this->on_pause_now() ) {
			$this->has_pending_items = true;
			return false; // nothing to update soon (at next execution).
		}

		$items = $this->get_next_posts_wrapper();
		if ( is_null( $items ) ) { // some item is pending, but blocked now.
			$this->has_pending_items = true;
			return false; // no need to run again in this round.
		} elseif ( empty( $items ) ) { // nothing to update more, everything is finished.
			$this->has_pending_items = false;
			return false;
		} else {
			$this->has_pending_items = true;
		}
		try {
			Ahrefs_Seo::breadcrumbs( sprintf( '%s::update_posts: %s', get_called_class(), (string) wp_json_encode( Post_Tax::id( $items ) ) ) );
			try {
				if ( $this->api instanceof Ahrefs_Seo_Abstract_Api ) {
					$this->api->set_worker( $this ); // report problems to current worker.
				}
				$result = $this->update_posts( $items );
			} finally {
				if ( $this->api instanceof Ahrefs_Seo_Abstract_Api ) {
					$this->api->set_worker( null );  // do not report problems to current worker.
				}
			}
		} catch ( Error $e ) {
			Ahrefs_Seo_Compatibility::on_type_error( $e, __METHOD__, __FILE__ );
		} catch ( Exception $e ) {
			Ahrefs_Seo_Errors::save_message( 'content audit', $e->getMessage() );
			Ahrefs_Seo::notify( $e, sprintf( 'Unexpected exception on %s::%s', get_called_class(), __FUNCTION__ ) );
		} finally {
			$this->unlock_posts( $items ); // unlock items, locked in get_next_posts_wrapper() call.
		}
		if ( ! $result ) { // false mean rate error, so need to have a rest for some time...
			$this->set_pause( max( $this->pause_after_fail, $this->pause_after_success ) );
		} else {
			$this->set_pause( $this->pause_after_success );
		}

		return true;
	}

	/**
	 * Can not run now because of time restriction from API side
	 *
	 * @return bool True if on pause now.
	 */
	public function on_pause_now() : bool {
		$wait_until = $this->get_pause_value();
		return ! empty( $wait_until ) && ( $wait_until > time() );
	}

	/**
	 * Should finish, must not start new requests
	 *
	 * @return bool
	 */
	public function should_finish() : bool {
		$max_allowed_time = $this->is_cron ? $this::MAX_UPDATE_TIME_CRON : $this::MAX_UPDATE_TIME_AJAX;
		return microtime( true ) - $this->time_start >= $max_allowed_time;
	}

	/**
	 * Get unique option name for waiting time.
	 * Instance class name used.
	 *
	 * @return string
	 */
	protected function get_waiting_option_name() : string {
		return $this::OPTION_PREFIX_WAIT . $this::API_NAME;
	}

	/**
	 * Get unique option name for post lock.
	 * Instance class name, snapshot ID and post ID used.
	 *
	 * @param Post_Tax|null $post_tax What post is locked, null if the worker is starting.
	 * @return string
	 */
	protected function get_lock_name( ?Post_Tax $post_tax ) : string {
		$class = str_replace( 'worker_', '', strtolower( str_replace( __NAMESPACE__ . '\\', '', get_class( $this ) ) ) );
		return is_null( $post_tax ) ? sprintf( '%s%s', $this::OPTION_PREFIX_LOCK, $class ) : sprintf( '%s%s-%d-%d%s', $this::OPTION_PREFIX_LOCK, $class, intval( $post_tax->get_snapshot_id() ), $post_tax->get_post_id(), $post_tax->get_taxonomy() );
	}

	/**
	 * Save waiting time from API, to option.
	 * Do not overwrite current value if new time is smaller from existing.
	 *
	 * @param float $wait_seconds Waiting time, in seconds.
	 * @return void
	 */
	protected function set_pause( float $wait_seconds ) : void {
		$old_wait_until = $this->get_pause_value();
		$wait_until     = microtime( true ) + $wait_seconds;
		if ( $wait_until > $old_wait_until ) {
			update_option( $this->get_waiting_option_name(), $wait_until, false ); // do not use autoloading, otherwise wp_cache_delete does not work.
		}
	}

	/**
	 * Reset waiting time
	 *
	 * @since 0.8.4
	 *
	 * @return void
	 */
	public function reset_pause() : void {
		delete_option( $this->get_waiting_option_name() );
	}

	/**
	 * Get pause time.
	 *
	 * @param bool $cached_value True: cached value; False: force read from DB instead of cache.
	 * @return float Timestamp in future or at the past, 0 if was not set.
	 */
	private function get_pause_value( bool $cached_value = false ) : float {
		if ( defined( 'AHREFS_SEO_IGNORE_DELAY' ) && AHREFS_SEO_IGNORE_DELAY ) {
			return 0;
		}
		if ( ! $cached_value ) {
			wp_cache_delete( $this->get_waiting_option_name(), 'options' ); // force read from DB instead of cache.
		}
		return floatval( get_option( $this->get_waiting_option_name(), 0 ) );
	}

	/**
	 * Has pending tasks to execute in current thread.
	 * Initially return null, then value filled from execution.
	 *
	 * @return bool|null Null if no cached value exists.
	 */
	public function has_pending_tasks() : ?bool {
		return $this->has_pending_items;
	}

	/**
	 * Lock post, so another process will not update it.
	 *
	 * @param Post_Tax[] $post_taxes What to lock.
	 * @return void
	 */
	private function lock_posts( array $post_taxes ) : void {
		array_walk(
			$post_taxes,
			function( Post_Tax $post_tax ) : void {
				set_transient( $this->get_lock_name( $post_tax ), true, 5 * MINUTE_IN_SECONDS );
			}
		);
	}

	/**
	 * Unlock post
	 *
	 * @param Post_Tax[] $post_taxes What to unlock.
	 * @return void
	 */
	private function unlock_posts( array $post_taxes ) : void {
		array_walk(
			$post_taxes,
			function( Post_Tax $post_tax ) : void {
				delete_transient( $this->get_lock_name( $post_tax ) );
			}
		);
	}

	/**
	 * Is post locked by another process
	 *
	 * @param Post_Tax $post_tax What to check.
	 * @return bool
	 */
	private function is_locked_post( Post_Tax $post_tax ) : bool {
		return (bool) get_transient( $this->get_lock_name( $post_tax ) );
	}

	/**
	 * Get next items for loading details for.
	 * Use transients, internally call get_next_posts().
	 * Posts are locked.
	 *
	 * @return null|Post_Tax[] List of post ID, null if we can not return items, because something is blocked.
	 */
	protected function get_next_posts_wrapper() : ?array {
		$transient = $this->get_lock_name( null );
		$result    = null;
		$filled    = false;
		$n         = 0;
		do {
			if ( $n++ > 0 ) {
				Ahrefs_Seo::usleep( rand( 10000, 100000 ) );
			}
			if ( ! get_transient( $transient ) ) {
				set_transient( $transient, 1, MINUTE_IN_SECONDS );
				try {
					$result = $this->get_next_posts();
					if ( ! empty( $result ) ) {
						$this->lock_posts( $result );
					}
					$filled = true;
				} finally {
					delete_transient( $transient );
				}
			}
		} while ( $n < 3 && ! $filled );

		return $result;
	}

	/**
	 * Get next items for loading details for.
	 *
	 * @return null|Post_Tax[] List of post ID, null if we can not return items, because something is blocked.
	 */
	protected function get_next_posts() : ?array {
		$data = $this->content_audit->get_unprocessed_item_from_new( $this::WHAT_TO_UPDATE, max( 10, 2 * $this->items_at_once ) );
		if ( ! empty( $data ) && is_array( $data ) ) {
			// need to filter using already locked posts.
			foreach ( $data as $key => $post_tax ) {
				if ( $this->is_locked_post( $post_tax ) ) {
					unset( $data[ $key ] );
				}
			}
			if ( count( $data ) > $this->items_at_once ) {
				// leave only items_at_once items.
				$indexes = array_rand( array_values( $data ), $this->items_at_once );
				if ( ! is_array( $indexes ) ) {
					$indexes = [ $indexes ];
				}
				foreach ( $data as $key => $item ) {
					if ( ! in_array( $key, $indexes, true ) ) {
						unset( $data[ $key ] );
					}
				}
			}
			/** @var Post_Tax[] $data */
			$data = array_values( $data );
			if ( ! count( $data ) ) { // all found items were removed as already locked.
				return null;
			}
		}

		return $data;
	}

	/**
	 * Run update for items in list
	 *
	 * @param Post_Tax[] $post_taxes Post ID list.
	 * @return bool False if rate limit error received and need to do pause.
	 */
	abstract protected function update_posts( array $post_taxes ) : bool;

	/**
	 * Update details which current worker can.
	 *
	 * @param Post_Tax[] $post_taxes Post ID list.
	 *
	 * @return void
	 */
	abstract public function update_posts_info( array $post_taxes ) : void;

	/**
	 * Callback for on rate error
	 *
	 * @param Throwable                   $e Error source.
	 * @param array<int|string|null>|null $page_slugs_list Pages list where error happened.
	 * @return void
	 */
	public function on_rate_error( Throwable $e, ?array $page_slugs_list = [] ) : void {
		$wait_minutes = null;
		Ahrefs_Seo::breadcrumbs( sprintf( '%s::%s(%s)(%s)', get_called_class(), __FUNCTION__, (string) wp_json_encode( $page_slugs_list ), (string) $e ) );
		if ( $e instanceof InvalidArgumentException || $e instanceof GuzzleConnectException || $e instanceof Ahrefs_Seo_Exception ) {
			$wait_minutes = 1;
		} elseif ( $e instanceof Google_Service_Exception ) {
			$wait_minutes = 1; // common pause length.
			$error        = $e->getErrors()[0] ?? null;
			if ( is_array( $error ) && isset( $error['reason'] ) ) {
				if ( in_array( $error['reason'], [ 'userRateLimitExceeded', 'rateLimitExceeded', 'quotaExceeded' ], true ) ) {
					$up_limit     = strpos( $error['message'], 'for at least an hour' ) || strpos( $error['message'], 'in under an hour' ) || strpos( $error['message'], 'the daily request limit' ) ? 60 : 10; // todo: show additional message.
					$wait_minutes = rand( $up_limit * 45, $up_limit * 75 ) / 60; // use 100-125% of max timeout.
				} elseif ( 'internalError' === $error['reason'] ) {
					$wait_minutes = 15;
				} elseif ( 'dailyLimitExceeded' === $error['reason'] ) {
					$wait_minutes = 12 * 60; // todo: show additional message.
				} elseif ( 'badRequest' === $error['reason'] ) {
					$wait_minutes = 10;
				} else {
					$wait_minutes = 2;
				}
			} else {
				if ( false !== stripos( $e->getMessage(), 'The server encountered a temporary error' ) ) {
					$wait_minutes = 2.5;
				} elseif ( false !== stripos( $e->getMessage(), 'Error 404' ) ) {
					$wait_minutes = 3;
				}
			}
		}
		if ( ! is_null( $wait_minutes ) ) {
			$this->set_pause( $wait_minutes * MINUTE_IN_SECONDS );
		}
		do_action( 'ahrefs_seo_rate_error', $this::WHAT_TO_UPDATE, $wait_minutes, $e, $page_slugs_list );
	}

	/**
	 * Return time before pause ends.
	 *
	 * @return float|null Pause (seconds). 0 if no delay was set. noll if nothing to update.
	 */
	public function get_waiting_seconds() : ?float {
		if ( ! $this->has_pending_items ) {
			return null; // nothing to update, may ignore any delays.
		}
		$wait   = $this->get_pause_value( true );
		$result = $wait - microtime( true );
		return max( $result, 0 );
	}

	/**
	 * Get waiting time for each API.
	 *
	 * @since 0.8.2
	 *
	 * @return float[]
	 */
	protected static function get_waiting_times() : array {
		$result = [];
		foreach ( [ 'ahrefs', 'ga', 'gsc', 'noindex' ] as $api_name ) {
			$value               = floatval( get_option( self::OPTION_PREFIX_WAIT . $api_name, 0 ) ) - microtime( true );
			$result[ $api_name ] = max( $value, 0 );
		}
		return $result;
	}

	/**
	 * Get max waiting time for all APIs.
	 *
	 * @since 0.7.5
	 *
	 * @return float Number of seconds for waiting last thread.
	 */
	public static function get_max_waiting_time() : float {
		return (float) max( self::get_waiting_times() );
	}

	/**
	 * Get min waiting time for all APIs.
	 *
	 * @since 0.8.2
	 *
	 * @return float Number of seconds for waiting last thread.
	 */
	public static function get_min_waiting_time() : float {
		if ( defined( 'AHREFS_SEO_IGNORE_DELAY' ) && AHREFS_SEO_IGNORE_DELAY ) {
			return 0;
		}
		return (float) max( self::get_waiting_times() );
	}

}
