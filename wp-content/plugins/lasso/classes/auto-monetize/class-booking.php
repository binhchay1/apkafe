<?php
/**
 * Declare class Booking
 *
 * @package Booking
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Booking
 * Example URL:
 *  https://www.booking.com/index.html?aid=1931042
 */
class Booking extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Booking';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'booking';

	/**
	 * Set affiliate id
	 */
	protected function set_affiliate_id() {
		$this->affiliate_id = Lasso_Helper::get_argument_from_url( $this->url, 'aid', true, true );
	}

	/**
	 * Set advertiser id
	 */
	protected function set_advertiser_id() {
		$this->advertiser_id = null;
	}

	/**
	 * Set deep link
	 */
	protected function set_deep_link() {
		$this->deep_link = null;
	}
}
