<?php
/**
 * Declare class GetYourGuide
 *
 * @package GetYourGuide
 */

namespace Lasso\Classes\Auto_Monetize;

/**
 * GetYourGuide
 * Example URL:
 *  https://www.getyourguide.com/?partner_id=X25EVVL&utm_medium=online_publisher&placement=content-middle&cmp=travel-resources&deeplink_id=98b34dbb-e4fb-5a3b-ac3d-4329cab3d59a
 */
class GetYourGuide extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'GetYourGuide';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'get_your_guide';

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
