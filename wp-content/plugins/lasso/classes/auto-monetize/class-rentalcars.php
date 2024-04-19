<?php
/**
 * Declare class Rentalcars
 *
 * @package Rentalcars
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Rentalcars
 * Example URL:
 *  https://www.rentalcars.com/?adcamp=4717307aa0914da38fd752e88-328084&adplat=affiliate&affiliateCode=gotravel602
 */
class Rentalcars extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'RentalCarsCom';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'rentalcarscom';

	/**
	 * Set affiliate id
	 */
	protected function set_affiliate_id() {
		$this->affiliate_id = null;
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
