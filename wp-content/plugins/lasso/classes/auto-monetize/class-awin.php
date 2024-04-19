<?php
/**
 * Declare class Awin
 *
 * @package Awin
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Awin
 * Example URL:
 * https://www.awin1.com/cread.php?awinmid=11018&awinaffid=470525&ued=https%3A%2F%2Fwww.viator.com%2Fen-GB%2Ftours%2FBarcelona%2FTarragona-and-Sitges-tour%2Fd562-9866P25&clickref=day-trips-from-barcelo_amcid-2gxz7eFzxggJoYZpqY2l7
 */
class Awin extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Awin';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'awin';

	/**
	 * Set affiliate id
	 */
	protected function set_affiliate_id() {
		$this->affiliate_id = Lasso_Helper::get_argument_from_url( $this->url, 'awinaffid', true, true );
	}

	/**
	 * Set advertiser id
	 */
	protected function set_advertiser_id() {
		$this->advertiser_id = Lasso_Helper::get_argument_from_url( $this->url, 'awinmid', true, true );
	}

	/**
	 * Set deep link
	 */
	protected function set_deep_link() {
		$this->deep_link = $this->get_deep_link( 'ued' );
	}
}
