<?php
/**
 * Declare class Lasso_Process
 * Scan lasso link in posts/pages
 *
 * @package Lasso_Process
 */

use Lasso\Classes\Cache_Per_Process as Lasso_Cache_Per_Process;
use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Setting as Lasso_Setting;
use Lasso\Classes\Verbiage as Lasso_Verbiage;

use Lasso\Models\Model;

/**
 * Load vendor-prefix for cron requests
 */
require_once LASSO_PLUGIN_PATH . '/vendor-prefix/vendor/autoload.php';

/**
 * $key generated when save() event get fired
 * OR
 * $this->identifier = $this->prefix . '_' . $this->action;
 * $key = $wpdb->esc_like( $this->identifier . '_batch_' ) . '%';
 *
 * Reference URL
 * https://deliciousbrains.com/background-processing-wordpress/
 * https://github.com/A5hleyRich/wp-background-processing
 * https://github.com/A5hleyRich/wp-background-processing-example
 */
abstract class Lasso_Process extends Lasso_WP_Background_Process {
	const EXCEEDED_TIME_LIMIT      = 10; // ? Exceeded time limit in seconds
	const AGE_OUT                  = 6; // ? Age out time after n hours
	const RESTART_ATTEMPTED_LIMIT  = 3;
	const OPTION_RESTART_ATTEMPTED = 'lasso_process_restart_attempted'; // ? Age out time after n hours
	const PROCESS_EXECUTE_LIMIT    = 2;
	const PROCESSES_QUEUE_KEY      = 'lasso_processes_queue';
	const COMPLETE_ACTION_KEY      = 'lasso_process_complete';

	const PROCESS_PRIORITIES = array(
		'Lasso_Process_Build_Link'                        => 50,
		'Lasso_Process_Import_All'                        => 40,
		'Lasso_Process_Revert_All'                        => 40,
		'Lasso_Process_Link_Database'                     => 30,
		'Lasso_Process_Update_Amazon'                     => 20,
		'Lasso_Process_Add_Amazon'                        => 20,
		'Lasso_Process_Scan_Keyword'                      => 10,
		'Lasso_Process_Scan_Link'                         => 10,
		'Lasso_Process_Check_Issue'                       => 10,
		'Lasso_Process_Build_Rewrite_Slug_Links_In_Posts' => 10,
		'Lasso_Process_Create_Webp_Image'                 => 10,
		'Lasso_Process_Create_Webp_Image_Table'           => 10,
	);

	const PROCESSES_DISALLOW_DUPLICATE_IN_QUEUE = array(
		'Lasso_Process_Scan_Link',
		'Lasso_Process_Scan_Keyword',
	);

	/**
	 * Action name
	 *
	 * @var string $action
	 */
	protected $action = 'lasso_background_process';

	/**
	 * Log name
	 *
	 * @var string $log_name
	 */
	protected $log_name = 'lasso_background_process';

	/**
	 * Process name
	 *
	 * @var string $process_name
	 */
	protected $process_name = 'Lasso Background Process';

	/**
	 * Lasso_Process constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_get_list_background_processing', array( $this, 'get_list_background_processing' ) );
		add_filter( $this->identifier . '_default_time_limit', array( $this, 'custom_exceeded_time_limit' ), 10, 1 );
	}

	/**
	 * Custom exceeded time limit.
	 *
	 * @param int $time_limit Exceeded time limit.
	 */
	public function custom_exceeded_time_limit( $time_limit ) {
		return self::EXCEEDED_TIME_LIMIT;
	}

	/**
	 * Set log name
	 *
	 * @param string $log_name Log name.
	 */
	public function set_log_file_name( $log_name ) {
		$this->log_name     = $log_name;
		$this->process_name = ucwords( preg_replace( '/[\-_]/', ' ', $log_name ) );
	}

	/**
	 * Write logs when task starts
	 */
	public function task_start_log() {
		// ? set process start time
		$this->set_process_start_time();

		Lasso_Helper::write_log( '========== ========== ==========', $this->log_name );
		Lasso_Helper::write_log( '= ' . $this->process_name . ' Started =', $this->log_name );
		Lasso_Helper::write_log( '========== ========== ==========', $this->log_name );
	}

	/**
	 * Write logs when task completed
	 */
	public function task_end_log() {
		Lasso_Helper::write_log( '========== ========== ==========', $this->log_name );
		Lasso_Helper::write_log( '= ' . $this->process_name . ' Completed =', $this->log_name );
		Lasso_Helper::write_log( '========== ========== ==========', $this->log_name );
	}

	/**
	 * Set total idexes of process
	 *
	 * @param int $total_count Total.
	 */
	public function set_total( $total_count ) {
		$key = $this->get_key();

		if ( false === get_option( $key . '_total_count' ) ) {
			update_option( $key . '_total_count', $total_count, false );
		}
	}

	/**
	 * Get total idexes of process
	 */
	public function get_total() {
		$key = $this->get_key();
		return (int) get_option( $key . '_total_count' );
	}

	/**
	 * Get key of process
	 */
	public function get_key() {
		return $this->identifier;
	}

	/**
	 * Set start time of process
	 */
	public function set_process_start_time() {
		// ? SHOULD CONSIDER LATER
		// ? Only set "start time" for the first process of each background process type. The "start time" will auto delete when the process completed.
		/**
		// if ( ! $this->get_process_start_time() ) {
		// $key = $this->get_key();
		// update_option( $key . '_start_time', microtime( true ), false );
		// }
		*/

		$key = $this->get_key();
		update_option( $key . '_start_time', microtime( true ), false );
	}

	/**
	 * Get start time of process
	 */
	public function get_process_start_time() {
		$key   = $this->get_key() . '_start_time';
		$cache = Lasso_Cache_Per_Process::get_instance()->get_cache( $key );

		if ( false !== $cache ) {
			return $cache;
		}

		$start_time = get_option( $key, 0 );
		Lasso_Cache_Per_Process::get_instance()->set_cache( $key, $start_time );

		return $start_time;
	}

	/**
	 * Set running time of process
	 */
	public function set_processing_runtime() {
		$key  = $this->get_key();
		$diff = microtime( true ) - $this->get_process_start_time();
		$diff = round( $diff, 2 );

		update_option( $key . '_process_running_time', $diff, false );
	}

	/**
	 * Get running time of process
	 */
	public function get_processing_runtime() {
		$key = $this->get_key();
		return get_option( $key . '_process_running_time', 0 );
	}

	/**
	 * Get ETA
	 */
	public function get_eta() {
		$total               = $this->get_total();
		$completed           = $this->get_total_completed();
		$completed           = 0 !== $completed ? $completed : 1;
		$unit_execution_time = $this->get_processing_runtime() / $completed;
		$eta                 = $unit_execution_time * ( $total - $completed );

		return $eta;
	}

	/**
	 * Check whether process is running or not
	 *
	 * @param bool $default Use default or custom logic. Default to false.
	 */
	public function is_process_running( $default = false ) {
		if ( $default ) {
			return parent::is_process_running();
		}

		$next_schedule = $this->next_schedule();

		return parent::is_process_running() && $this->get_total_remaining() > 0 && $next_schedule;
	}

	/**
	 * Remove duplicated processes
	 */
	public function remove_duplicated_processes() {
		$query = '
			DELETE 
			FROM ' . Model::get_wp_table_name( 'options' ) . ' 
			WHERE option_id not in (
				SELECT option_id
				FROM (
					SELECT min(option_id) AS option_id 
					FROM ' . Model::get_wp_table_name( 'options' ) . " 
					WHERE option_name LIKE '%" . $this->action . "%batch%'
				) AS wpo
			) AND option_name LIKE '%" . $this->action . "%batch%'
		";

		Model::query( $query );
	}

	/**
	 * Remove: remove a process
	 */
	public function remove_process() {
		$sql = '
			DELETE FROM ' . Model::get_wp_table_name( 'options' ) . "
			WHERE option_name LIKE '%" . $this->action . "%'
				AND option_name NOT LIKE '%" . $this->action . "_start_time%'
		";

		Model::query( $sql );
	}

	/**
	 * Check whether the process is age out
	 */
	public function is_process_age_out() {
		$start_time        = $this->get_process_start_time();
		$diff              = microtime( true ) - $start_time;
		$failed_times      = self::RESTART_ATTEMPTED_LIMIT;
		$option_name       = self::OPTION_RESTART_ATTEMPTED;
		$current_attempted = intval( get_option( $option_name, 0 ) );
		$hour_in_seconds   = self::AGE_OUT * HOUR_IN_SECONDS;

		if ( $current_attempted >= $failed_times && ! $this->is_queue_empty() ) {
			return true;
		}

		if ( $start_time > 0 && $diff > $hour_in_seconds ) { // ? the process is age out
			// ? restart_attempted reach the limit, remove the process
			$this->remove_process();

			// ? increase restart_attempted
			update_option( $option_name, ++$current_attempted );
		}

		return false;
	}

	/**
	 * Get next schedule
	 */
	public function next_schedule() {
		return wp_next_scheduled( $this->cron_hook_identifier );
	}

	/**
	 * Get total remaining items
	 */
	public function get_total_remaining() {
		global $wpdb;

		$table        = Model::get_wp_table_name( 'options' );
		$column       = 'option_name';
		$key_column   = 'option_id';
		$value_column = 'option_value';

		if ( is_multisite() ) {
			$table        = $wpdb->sitemeta;
			$column       = 'meta_key';
			$key_column   = 'meta_id';
			$value_column = 'meta_value';
		}

		$key = Model::esc_like( $this->identifier . '_batch_' ) . '%';

		// @codingStandardsIgnoreStart
		$sql = Model::prepare(
			"
                        SELECT {$value_column}
                        FROM {$table}
                        WHERE {$column} LIKE %s
                        ORDER BY {$key_column} ASC
                        LIMIT 1
                    ",
			$key
		);
		// @codingStandardsIgnoreEnd

		$queue              = Model::get_var( $sql );   // ? We want to track SQL errors in Sentry
		$count              = 0;
		$unserialized_queue = maybe_unserialize( $queue );
		if ( is_array( $unserialized_queue ) ) {
			$count = count( $unserialized_queue );
		}

		return $count;
	}

	/**
	 * Get total items are completed
	 */
	public function get_total_completed() {
		$get_total      = $this->get_total();
		$total_remainig = $this->get_total_remaining();
		if ( $total_remainig && $total_remainig <= $get_total ) {
			return $get_total - $total_remainig;
		}

		return $get_total;
	}

	/**
	 * Do something when the process is completed
	 */
	public function set_completed() {
		$key = $this->get_key();
		delete_option( $key . '_total_count' );
		delete_option( $key . '_start_time' );
		delete_option( $key . '_process_running_time' );
	}

	/**
	 * Check whether the process is empty or not
	 */
	public function is_queue_empty() { // phpcs:ignore
		return parent::is_queue_empty();
	}

	/**
	 * Check whether the process is completed or not
	 */
	public function is_completed() {
		return $this->is_queue_empty();
	}

	/**
	 * Do something when an index completes
	 */
	public function complete() {
		parent::complete();
		$this->set_completed();
		$this->task_end_log();
		do_action( self::COMPLETE_ACTION_KEY, $this->action );
		$this->handle_lasso_processes_queue();
		$this->increment_low_priority_lasso_processes_queue();
	}

	/**
	 * Check whether CPU exceeded or not
	 */
	public function cpu_exceeded() {
		$max_cpu_allow = Lasso_Setting::lasso_get_setting( 'cpu_threshold', 80 ); // ? percent
		$cpu_load      = Lasso_Helper::get_cpu_load();

		return $cpu_load >= $max_cpu_allow;
	}

	/**
	 * Lock process
	 *
	 * Lock the process so that multiple instances can't run simultaneously.
	 * Override if applicable, but the duration should be greater than that
	 * defined in the time_exceeded() method.
	 */
	protected function lock_process() {
		$this->start_time = time(); // Set start time of current process.

		$lock_duration = ( property_exists( $this, 'queue_lock_time' ) ) ? $this->queue_lock_time : 30; // ?  1/2 minute
		$lock_duration = apply_filters( $this->identifier . '_queue_lock_time', $lock_duration );

		set_site_transient( $this->identifier . '_process_lock', microtime(), $lock_duration );
	}

	/**
	 * Handle
	 *
	 * Pass each queue item to the task handler, while remaining
	 * within server memory and time limit constraints.
	 */
	protected function handle() {
		$this->lock_process();

		do {
			$batch = $this->get_batch();

			$batch_data = $batch->data ?? array();
			foreach ( $batch_data as $key => $value ) {
				sleep( 1 ); // ? sleep 1s
				$task = $this->cpu_exceeded() ? $value : $this->task( $value );

				if ( false !== $task ) {
					$batch->data[ $key ] = $task;
				} else {
					unset( $batch->data[ $key ] );
				}

				if ( $this->time_exceeded() || $this->memory_exceeded() ) {
					// ? Batch limits reached.
					break;
				}
			}

			// ? Update or delete current batch.
			$batch_key = $batch->key ?? '';
			if ( ! empty( $batch->data ) ) {
				$this->update( $batch_key, $batch->data );
			} else {
				$this->delete( $batch_key );
			}
		} while ( ! $this->time_exceeded() && ! $this->memory_exceeded() && ! $this->is_queue_empty() );

		$this->unlock_process();

		// ? Start next batch or complete process.
		if ( ! $this->is_queue_empty() ) {
			$this->dispatch();
		} else {
			$this->complete();
		}

		wp_die();
	}

	/**
	 * Handle manually
	 *
	 * Pass each queue item to the task handler, while remaining
	 * within server memory and time limit constraints.
	 */
	public function handle_manually() {
		$this->handle();
	}

	/**
	 * Get batch
	 *
	 * @return stdClass Return the first batch from the queue
	 */
	public function get_batch() {
		global $wpdb;

		$table        = Model::get_wp_table_name( 'options' );
		$column       = 'option_name';
		$key_column   = 'option_id';
		$value_column = 'option_value';

		if ( is_multisite() ) {
			$table        = $wpdb->sitemeta;
			$column       = 'meta_key';
			$key_column   = 'meta_id';
			$value_column = 'meta_value';
		}

		$key = Model::esc_like( $this->identifier . '_batch_' ) . '%';

		// @codingStandardsIgnoreStart
		$query = Model::get_row(
			Model::prepare(
				"
				SELECT *
				FROM {$table}
				WHERE {$column} LIKE %s
				ORDER BY {$key_column} ASC
				LIMIT 1
			",
				$key
			)
		);
		// @codingStandardsIgnoreEnd

		if ( is_null( $query ) ) {
			return array();
		}

		$batch       = new stdClass();
		$batch->key  = $query->$column;
		$batch->data = maybe_unserialize( $query->$value_column );

		return $batch;
	}

	/**
	 * Disable all processes exclude remove attribute process
	 */
	public static function are_all_processes_disabled() {
		$disable = get_option( 'lasso_disable_processes', 0 );
		$disable = boolval( intval( $disable ) );

		return $disable;
	}

	/**
	 * Remove all processes (WP schedule)
	 */
	public function remove_all_processes() {
		$process_classes = Lasso_Verbiage::PROCESS_DESCRIPTION;
		foreach ( $process_classes as $process_class => $process_name ) {
			if ( 'Lasso_Process_Remove_Attribute' === $process_class ) {
				continue;
			}

			/** @var Lasso_Process $process_class_obj */ // phpcs:ignore
			$process_class_obj = new $process_class();
			$process_class_obj->clear_scheduled_event();
			$process_class_obj->set_completed();
			wp_clear_scheduled_hook( $process_class_obj->cron_hook_identifier );
		}

		$lasso_db = new Lasso_DB();
		$lasso_db->remove_all_lasso_processes();
	}

	/**
	 * Get Background Process is running
	 */
	public function get_list_background_processing() {
		$result            = array(
			'running_total' => 0,
			'items'         => array(),
		);
		$restart_attempted = intval( get_option( self::OPTION_RESTART_ATTEMPTED, 0 ) );

		$process_classes                   = Lasso_Verbiage::PROCESS_DESCRIPTION;
		$process_classes                   = apply_filters( 'lasso_all_processes', $process_classes );
		$manually_background_process_limit = Lasso_Setting::lasso_get_setting( 'manually_background_process_limit' );
		$total_background_process_running  = $this->get_total_running_processes();

		foreach ( $process_classes as $process_class => $process_name ) {
			/** @var Lasso_Process $process_class_obj */ // phpcs:ignore
			$process_class_obj     = new $process_class();
			$process_next_schedule = $process_class_obj->next_schedule();

			$process_total_item = $process_class_obj->get_total();
			$process_completed  = $process_class_obj->get_total_completed();
			$process_remaining  = $process_class_obj->get_total_remaining();
			$trigger_manually   = $restart_attempted >= self::RESTART_ATTEMPTED_LIMIT
				&& $total_background_process_running < $manually_background_process_limit
				&& ! $process_class_obj->is_process_running();

			$data = array(
				'name'             => $process_name,
				'class'            => $process_class,
				'completed'        => $process_completed,
				'total'            => $process_total_item,
				'trigger_manually' => $trigger_manually,
			);

			if ( $process_next_schedule ) {
				if ( ! $process_total_item || ! $process_remaining ) {
					continue;
				}

				$result['running_total'] += 1;

				$result['items'][] = $data;
			} elseif ( ! $process_class_obj->is_queue_empty() && $trigger_manually ) {
				$result['items'][] = $data;
			}
		}

		wp_send_json_success( $result );
	}

	/**
	 * Check limit process running at a time.
	 *
	 * @return bool
	 */
	public function is_process_running_reach_the_limit() {
		$processes_execute_limit = Lasso_Setting::lasso_get_setting( 'processes_execute_limit' );
		if ( ! $processes_execute_limit ) {
			return false;
		}

		// ? Get Lasso's process classes
		$process_lock_names = array();
		$process_classes    = array_keys( self::PROCESS_PRIORITIES );

		foreach ( $process_classes as $process_class ) {
			if ( ! class_exists( $process_class ) ) {
				continue;
			}

			/** @var Lasso_Process $process_class_obj */ // phpcs:ignore
			$process_class_obj    = new $process_class();
			$process_lock_names[] = '_site_transient_' . $process_class_obj->get_key() . '_process_lock';
		}

		// ? Count process is running
		$query = '
			SELECT 
				COUNT(*) as `process_count`
			FROM 
				' . Model::get_wp_table_name( 'options' ) . ' 
			WHERE 
				`option_name` IN ("' . implode( '","', $process_lock_names ) . '")
		';

		$result        = Model::get_row( $query );
		$process_count = $result->process_count;

		// ? Check limit process running at a time by const PROCESS_EXECUTE_LIMIT
		if ( $process_count < self::PROCESS_EXECUTE_LIMIT ) {
			return false;
		}

		return true;
	}

	/**
	 * Get total Lasso background processes are running
	 *
	 * @return int
	 */
	public function get_total_running_processes() {
		// ? Count process is running
		$query = '
			SELECT 
				COUNT(*) AS `process_count`
			FROM 
				' . Model::get_wp_table_name( 'options' ) . " 
			WHERE 
				`option_name` LIKE '_site_transient_" . $this->prefix . "_lasso_%_process_lock'
		";

		$data = Model::get_var( $query );

		return intval( $data );
	}

	/**
	 * Push to lasso processes queue in case processes running reach the limit.
	 *
	 * @param string $class_name    Process's class.
	 * @param string $function_name Process's function.
	 * @param array  $args Process's arguments.
	 */
	public function push_to_lasso_processes_queue( $class_name, $function_name, $args ) {
		$current_identify_string = $this->get_process_identify_string( $class_name, $function_name, $args );
		$lasso_processes_queue   = get_option( self::PROCESSES_QUEUE_KEY, array() );

		// ? Check process duplicate in queue list
		if ( self::is_duplicate_process_in_queue( $class_name, $lasso_processes_queue ) ) {
			return;
		}

		if ( ! in_array( $current_identify_string, array_keys( $lasso_processes_queue ) ) ) { // phpcs:ignore
			$lasso_processes_queue[ $current_identify_string ] = array(
				'class'         => $class_name,
				'function_name' => $function_name,
				'args'          => $args,
				'priority'      => self::PROCESS_PRIORITIES[ $class_name ] ?? 10,
				'created_at'    => time(),
			);

			// ? Sort by process priority
			$lasso_processes_queue = $this->sort_priority_list( $lasso_processes_queue );

			// ? Update lasso_processes_queue option
			update_option( self::PROCESSES_QUEUE_KEY, $lasso_processes_queue );
		}
	}

	/**
	 * Handle next process in queue.
	 *
	 * @return bool|void
	 */
	public function handle_lasso_processes_queue() {
		if ( $this->is_process_running_reach_the_limit() ) {
			return;
		}

		$lasso_processes_queue = get_option( self::PROCESSES_QUEUE_KEY, array() );
		if ( empty( $lasso_processes_queue ) ) {
			return;
		}

		// ? Get the next process info and remove this one out of lasso processes queue option
		$next_process  = array_shift( $lasso_processes_queue );
		$process_class = $next_process['class'];
		$function_name = $next_process['function_name'];
		$args          = $next_process['args'];

		update_option( self::PROCESSES_QUEUE_KEY, $lasso_processes_queue );

		if ( ! class_exists( $process_class ) ) {
			return;
		}

		/** @var Lasso_Process $process_class_obj */ // phpcs:ignore
		$process_class_obj = new $process_class();
		call_user_func_array( array( $process_class_obj, $function_name ), $args );
		return true;
	}

	/**
	 * Handle increment priority process in queue have low priority when reach to period time.
	 *
	 * @param int $period_hour period_hour.
	 *
	 * @return void
	 */
	public function increment_low_priority_lasso_processes_queue( $period_hour = 24 ) {
		$lasso_processes_queue = get_option( self::PROCESSES_QUEUE_KEY, array() );
		$cur_time              = time();
		$period_time           = 3600 * $period_hour;

		foreach ( $lasso_processes_queue as &$processes ) {
			$processes['created_at'] = $processes['created_at'] ?? $cur_time;
			$time_elapsed            = $cur_time - $processes['created_at'];
			// ? Increment priority when greater than period time
			if ( $time_elapsed >= $period_time ) {
				$processes['priority']  += 10;
				$processes['created_at'] = $cur_time;
			}
		}

		// ? Sort by process priority
		$lasso_processes_queue = $this->sort_priority_list( $lasso_processes_queue );

		// ? Update lasso_processes_queue option
		update_option( self::PROCESSES_QUEUE_KEY, $lasso_processes_queue );
	}

	/**
	 * Get process identify string.
	 *
	 * @param string $process_class process_class.
	 * @param string $function function.
	 * @param array  $args args.
	 *
	 * @return string
	 */
	public function get_process_identify_string( $process_class, $function, $args ) {
		return md5( $process_class . '_' . $function . '_' . serialize( $args ) ); // phpcs:ignore
	}

	/**
	 * Sort by process priority
	 *
	 * @param array $lasso_processes_queue Array of process classes.
	 *
	 * @return array
	 */
	public function sort_priority_list( $lasso_processes_queue ) {
		uasort(
			$lasso_processes_queue,
			function( $a, $b ) {
				return $a['priority'] < $b['priority'];
			}
		);

		return $lasso_processes_queue;
	}

	/**
	 * Check process duplicate in queue list.
	 *
	 * @param string $class_name Process class name.
	 * @param array  $lasso_processes_queue Queue list.
	 *
	 * @return bool
	 */
	public function is_duplicate_process_in_queue( $class_name, $lasso_processes_queue ) {
		if ( ! in_array( $class_name, self::PROCESSES_DISALLOW_DUPLICATE_IN_QUEUE, true ) ) {
			return false;
		}

		foreach ( $lasso_processes_queue as $process ) {
			$process_class = $process['class'] ?? '';
			if ( $process_class === $class_name ) {
				return true;
			}
		}

		return false;
	}
}
