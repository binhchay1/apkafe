<?php

declare(strict_types=1);

namespace ahrefs\AhrefsSeo\Workers;

use ahrefs\AhrefsSeo\Ahrefs_Seo_Analytics;
use Throwable;

/**
 * Worker_GSC class.
 * Share same rate functions for everything, that query GSC.
 *
 * @since 0.7.3
 */
abstract class Worker_GSC extends Worker {

	public const API_NAME = 'gsc';

	/** @var float Delay after successful request to API */
	protected $pause_after_success = 10;

	/**
	 * Can not run now because of time restriction from API side
	 *
	 * @return bool True if on pause now.
	 */
	public function on_pause_now() : bool {
		$result = parent::on_pause_now();
		if ( ! $result && ( $this->api instanceof Ahrefs_Seo_Analytics ) ) {
			if ( $this->api->is_gsc_paused() ) {
				$result = true; // if API in unavailable.
			}
		}
		return $result;
	}

	/**
	 * Callback for on rate error
	 *
	 * @param Throwable                   $e Error source.
	 * @param array<int|string|null>|null $page_slugs_list List of page slugs, where this error happened.
	 * @return void
	 */
	public function on_rate_error( Throwable $e, ?array $page_slugs_list = [] ) : void {
		parent::on_rate_error( $e, $page_slugs_list );
		if ( $this->api instanceof Ahrefs_Seo_Analytics ) {
			$this->api->set_gsc_paused( true );
		}
	}
}
