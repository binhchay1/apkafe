<?php
/**
 * Declare class PostAffiliatePro
 *
 * @package PostAffiliatePro
 */

namespace Lasso\Classes\Auto_Monetize;

/**
 * PostAffiliatePro
 * Example URL:
 *  https://www.repfitness.com/benches/flat-benches/rep-fb-5000-competition-flat-bench#5a57794a6600b
 */
class PostAffiliatePro extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Post Affiliate Pro';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'postaffiliatepro';

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
