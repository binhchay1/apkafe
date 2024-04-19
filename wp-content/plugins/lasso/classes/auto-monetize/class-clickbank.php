<?php
/**
 * Declare class ClickBank
 *
 * @package ClickBank
 */

namespace Lasso\Classes\Auto_Monetize;

/**
 * ClickBank
 * Example URL:
 *  https://90c59boato48vpeazlpn6v9v4n.hop.clickbank.net/
 */
class ClickBank extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'ClickBank';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'clickbank';

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
