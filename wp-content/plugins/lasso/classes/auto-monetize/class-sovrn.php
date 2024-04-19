<?php
/**
 * Declare class Sovrn
 *
 * @package Sovrn
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Sovrn
 * Example URL:
 *  https://redirect.viglink.com/?key=fd0acc5b9b058400dc93b568ff7ec00e&u=https%3A%2F%2Fwww.ray-ban.com%2Fusa%2Fsunglasses%2FRB2140%2520UNISEX%2520093-original%2520wayfarer%2520classic-black%2F805289126607%3Fcid%3DPM-FGS_300419-PLA-Smart%2BShopping-All-Products-June2019-805289126607%26gclid%3DCj0KCQjwy8f6BRC7ARIsAPIXOjiveieG6O_W-r54lSIOu9YFuawBjWzyqv8dKgVzJ0ZxAiNabsX8MHsaAh0UEALw_wcB%26gclsrc%3Daw.ds&type=CE&opt=false
 *  https://sovrn.co/ax9x0hh
 */
class Sovrn extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Sovrn';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'sovrn';

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
		$this->deep_link = $this->get_deep_link( 'u' );
	}
}
