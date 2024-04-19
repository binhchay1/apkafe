<?php
/**
 * Declare class MaxBounty
 *
 * @package MaxBounty
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * MaxBounty
 * Example URL:
 *  https://afflat3b2.com/lnk.asp?o=8067&c=918277&a=255261&k=1BE3299662D6B88C9BD8740BBB9814A6&l=6779&s1=smarts&s2=ppc
 */
class MaxBounty extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'MaxbBunty';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'maxbounty';

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
