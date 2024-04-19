<?php
/**
 * Declare class Daisycon
 *
 * @package Daisycon
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Daisycon
 * Example URL: https://jf79.net/c/?si=16954&li=1730508&wi=376825&ws=wct2211230206glm95&dl=products%2Fgravity-blanket-verzwaringsdeken
 * Document: https://faq-publisher.daisycon.com/hc/en-us/articles/204787042-How-is-a-Daisycon-affiliate-link-structured-
 */
class Daisycon extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Daisycon';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'daisycon';

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
