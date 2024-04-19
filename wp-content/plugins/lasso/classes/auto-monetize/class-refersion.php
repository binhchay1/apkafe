<?php
/**
 * Declare class Refersion
 *
 * @package Refersion
 */

namespace Lasso\Classes\Auto_Monetize;

/**
 * Refersion
 * Example URL:
 *  https://fluentu.refersion.com/c/4592c
 *  https://freshbrewedtee.refersion.com/l/9f2.10058
 */
class Refersion extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Refersion';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'refersion';

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
