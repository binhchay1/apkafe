<?php
/**
 * Declare class PartnerStack
 *
 * @package PartnerStack
 */

namespace Lasso\Classes\Auto_Monetize;

/**
 * PartnerStack
 * Example URL:
 *  https://link.technologyadvice.com/r/monday.com-project-management-tr-monday-review
 */
class PartnerStack extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'PartnerStack';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'partnerstack';

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
