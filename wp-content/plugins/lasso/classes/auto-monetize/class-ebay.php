<?php
/**
 * Declare class Ebay
 *
 * @package Ebay
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Ebay
 * Example URL:
 *  https://www.ebay.com/?ff3=4&pub=5575597807&toolid=10001&campid=5338700701&customid=&mpre=https%3A%2F%2Fwww.ebay.com%2Fitm%2FStreacom-ST-DB4-Black-Fanless-Aluminium-Bi-Symmetrical-Chassis%2F332472620735%3Fepid%3D598263204%26hash%3Ditem4d68e996bf%3Ag%3A57AAAOSwEb9aJTcJ&mkevt=1&mkcid=1&mkrid=711-53200-19255-0&ufes_redirect=true
 *  https://www.ebay.de/sch/i.html?_from=R40&_trksid=p2380057.m570.l1313&_nkw=canon+eos+6d&_sacat=0&mkcid=1&mkrid=707-53477-19255-0&siteid=77&campid=5338928990&customid=&toolid=10001&mkevt=1
 *  https://www.ebay.co.uk/sch/i.html?_from=R40&_nkw=yamaha%20r1&_sacat=422&rt=nc&_udhi=6000&mkcid=1&mkrid=710-53481-19255-0&siteid=3&campid=5338844476&customid=R1uk&toolid=10001&mkevt=1
 *  https://ebay.us/DhqEEb
 */
class Ebay extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'eBay Partner Network';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'ebay';

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
		$this->deep_link = $this->get_deep_link( 'mpre' );
	}
}
