<?php
/**
 * Declare class Rakuten
 *
 * @package Rakuten
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Auto_Monetize\Auto_Monetize;
use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Rakuten
 * Example URL:
 *  https://click.linksynergy.com/deeplink?id=POXtv4C/dbc&mid=36667&murl=https://www.aquasana.com/under-sink-water-filters/claryum-3-stage-max-flow/brushed-nickel-100236357.html
 */
class Rakuten extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Rakuten';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'rakuten';

	/**
	 * Set affiliate id
	 */
	protected function set_affiliate_id() {
		$this->affiliate_id = Lasso_Helper::get_argument_from_url( $this->url, 'id', true, true );
	}

	/**
	 * Set advertiser id
	 */
	protected function set_advertiser_id() {
		$this->advertiser_id = Lasso_Helper::get_argument_from_url( $this->url, 'mid', true, true );
	}

	/**
	 * Set deep link
	 */
	protected function set_deep_link() {
		$this->deep_link = $this->get_deep_link( 'murl' );
	}
}
