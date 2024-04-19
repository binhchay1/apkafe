<?php
/**
 * Declare class TradeTracker
 *
 * @package TradeTracker
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * TradeTracker
 * Example URL:
 *  https://tc.tradetracker.net/?c=30368&m=12&a=393162&r=&u=%2Fportugal%2Ffaro%2Ftavira%2Fparque-de-campismo-da-psp-120047%2F
 */
class TradeTracker extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Tradetracker';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'tradetracker';

	/**
	 * Set affiliate id
	 */
	protected function set_affiliate_id() {
		$this->affiliate_id = Lasso_Helper::get_argument_from_url( $this->url, 'a', true, true );
	}

	/**
	 * Set advertiser id
	 */
	protected function set_advertiser_id() {
		$this->advertiser_id = Lasso_Helper::get_argument_from_url( $this->url, 'm', true, true );
	}

	/**
	 * Set deep link
	 */
	protected function set_deep_link() {
		$this->deep_link = null;
	}
}
