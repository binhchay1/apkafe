<?php
/**
 * Declare class GoAffPro
 *
 * @package GoAffPro
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * GoAffPro
 * Example URL:
 *  https://goaffpro.myshopify.com/?ref=k0iybant0y9
 * Document: https://docs.goaffpro.com/how-tos/view-an-affiliates-profile/customize-an-affiliates-referral-link
 */
class GoAffPro extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'GoAffPro';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'goaffpro';

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
