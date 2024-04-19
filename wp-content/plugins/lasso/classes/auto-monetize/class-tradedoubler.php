<?php
/**
 * Declare class Tradedoubler
 *
 * @package Tradedoubler
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;


/**
 * Tradedoubler
 * // phpcs:disable
 * A typical Tradedoubler affiliate link might have a structure similar to the following:
	https://clk.tradedoubler.com/click?p=12345&a=987654&g=54321
	Here's a breakdown of some components:

	https://clk.tradedoubler.com/click: This is the base URL that initiates the click tracking.

	p=12345: The "p" parameter might represent the program ID or offer ID associated with the product or service being promoted.

	a=987654: The "a" parameter could stand for the affiliate ID, identifying the specific affiliate partner.

	g=54321: The "g" parameter might represent additional tracking information, such as a campaign ID or group identifier.
 * Example URL:
 * https://clk.tradedoubler.com/click?p=298901&a=3128742&g=159&url=https%3A%2F%2Fwww.petster.no%2Fhund%2Fhundeleker%2Fbra-for-tennene%2Fkong-orginal-hundeleke-original
 * // phpcs:enable
 */
class Tradedoubler extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Tradedoubler';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'tradedoubler';

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
		$this->advertiser_id = null;
	}

	/**
	 * Set deep link
	 */
	protected function set_deep_link() {
		$this->deep_link = $this->get_deep_link( 'url' );
	}
}
