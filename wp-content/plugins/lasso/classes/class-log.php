<?php
/**
 * Declare class Log
 *
 * @package Log*/

namespace Lasso\Classes;

/**
 * Lasso_Log
 */
class Log {
	const ERROR_LOG = 'general_errors';

	/**
	 * Log file
	 *
	 * @var string $log_file
	 */
	private $log_file;

	/**
	 * File path
	 *
	 * @var string $fp
	 */
	private $fp;

	/**
	 * Set log file (path and name)
	 *
	 * @param string $path File path.
	 */
	public function lfile( $path ) {
		$this->log_file = $path;
	}

	/**
	 * Write message to the log file
	 *
	 * @param string $message Message.
	 */
	public function lwrite( $message ) {
		// ? if file pointer doesn't exist, then open log file
		if ( ! is_resource( $this->fp ) ) {
			$this->lopen();
		}
		// ? define script name
		$script_name = pathinfo( $_SERVER['PHP_SELF'], PATHINFO_FILENAME ); // phpcs:ignore
		// ? define current time and suppress E_WARNING if using the system TZ settings
		// ? (don't forget to set the INI setting date.timezone)
		$time = @date( '[d/M/Y:H:i:s]' ); // phpcs:ignore
		// ? write current time, script name and message to the log file
		@fwrite( $this->fp, "$time ($script_name) $message" . PHP_EOL ); // phpcs:ignore
	}

	/**
	 * Close log file (it's always a good idea to close a file when you're done with it)
	 */
	public function lclose() {
		@fclose( $this->fp ); // phpcs:ignore
	}

	/**
	 * Open log file (private method)
	 */
	private function lopen() {
		$current_date     = gmdate( 'Y_m_d' );
		$log_file_default = LASSO_PLUGIN_PATH . '/logs/' . $current_date . '_default_log.txt';

		// ? define log file from lfile method or use previously set default
		$lfile = $this->log_file ? $this->log_file : $log_file_default;

		// ? open log file for writing only and place file pointer at the end of the file
		// ? (if the file does not exist, try to create it)
		$this->fp = @fopen( $lfile, 'a' ); // phpcs:ignore
	}
}
