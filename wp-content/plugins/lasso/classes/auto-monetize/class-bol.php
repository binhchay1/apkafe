<?php
/**
 * Declare class Bol
 *
 * @package Bol
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Bol
 * Example URL:
 *  http://partner.bol.com/click/click?p=1&t=url&s=1017126&url=https://www.bol.com/nl/nl/p/ayolite-lichttherapiebril-ervaar-de-beste-vorm-van-lichttherapie-gebruiksvriendelijk-alternatief-daglichtlamp/9300000017204153/?bltgh=kOaaNV9zl80cMJWSFSdLeg.2_5.6.ProductTitle&subid=wct2211250523urb4d
 */
class Bol extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Bol';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'bol';

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
		$this->deep_link = $this->get_deep_link( 'url' );
	}
}
