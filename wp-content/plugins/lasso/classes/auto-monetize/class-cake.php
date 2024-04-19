<?php
/**
 * Declare class Cake
 *
 * @package Cake
 */

namespace Lasso\Classes\Auto_Monetize;

/**
 * Cake
 * Example URL:
 *  https://ck.lendingtree.com/?a=606&c=3682
 */
class Cake extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'CAKE';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'cake';

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
