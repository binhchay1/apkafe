<?php
declare(strict_types=1);

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Workers\Worker;

/**
 * Class for work APIs together with workers.
 *
 * @since 0.7.3
 */
class Ahrefs_Seo_Abstract_Api {

	/**
	 * Active worker, used to send errors back to caller.
	 *
	 * @var Worker|null
	 */
	protected $active_worker;

	/**
	 * Called on any error from API request received.
	 * Report this error to active worker.
	 *
	 * @since 0.7.3
	 *
	 * @param \Throwable                  $e Exception or Error.
	 * @param array<int|string|null>|null $source_list List of slugs, urls or post id.
	 * @return void
	 */
	public function on_error_received( \Throwable $e, ?array $source_list = null ) : void {
		if ( ! is_null( $this->active_worker ) ) {
			$this->active_worker->on_rate_error( $e, $source_list );
		}
		Ahrefs_Seo::notify( $e );
	}

	/**
	 * Report error to workers.
	 *
	 * @since 0.7.3
	 *
	 * @param Worker|null $worker Worker instance.
	 * @return void
	 */
	public function set_worker( ?Worker $worker ) : void {
		$this->active_worker = $worker;
	}

}
