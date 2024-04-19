<?php
/**
 * Declare class ShareASale
 *
 * @package ShareASale
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Auto_Monetize\Auto_Monetize;
use Lasso\Classes\Helper as Lasso_Helper;

/**
 * ShareASale
 * Example URL:
 * https://shareasale.com/m-pr.cfm?merchantid=4355&amp;userid=2396132&amp;productid=1223289293&amp;afftrack=best-hunting-boots
 */
class ShareASale extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'ShareASale';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'shareasale';

	/**
	 * Set affiliate id
	 */
	protected function set_affiliate_id() {
		$u = Lasso_Helper::get_argument_from_url( $this->url, 'u', true, true );
		if ( ! $u ) {
			$u = Lasso_Helper::get_argument_from_url( $this->url, 'userid', true, true );
		}

		$this->affiliate_id = $u;
	}

	/**
	 * Set advertiser id
	 */
	protected function set_advertiser_id() {
		$m = Lasso_Helper::get_argument_from_url( $this->url, 'm', true, true );
		if ( ! $m ) {
			$m = Lasso_Helper::get_argument_from_url( $this->url, 'merchantid', true, true );
		}

		$this->advertiser_id = $m;
	}

	/**
	 * Set deep link
	 */
	protected function set_deep_link() {
		$this->deep_link = $this->get_deep_link( 'urllink' );
	}
}
