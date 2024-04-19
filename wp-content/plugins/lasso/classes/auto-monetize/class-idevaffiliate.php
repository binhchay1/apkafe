<?php
/**
 * Declare class IDevaffiliate
 *
 * @package IDevaffiliate
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * IDevaffiliate
 * Example URL:
 *  https://affiliates.viral-launch.com/idevaffiliate.php?id=2241
 *  https://www.idevaffiliate.com/33076/159.html
 * Document: https://help.idevaffiliate.com/videos_section/affiliate-links/
 */
class IDevaffiliate extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'IDevAffiliate';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'idevaffiliate';

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
