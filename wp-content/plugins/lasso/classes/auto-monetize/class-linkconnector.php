<?php
/**
 * Declare class LinkConnector
 *
 * @package LinkConnector
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * LinkConnector
 * Example URL:
 *  https://www.linkconnector.com/ta.php?lcpf=3&lcpt=0&lcpr=0&lc=156562000003002639&lc_pid=1049077-AAA&url=https%3A%2F%2Fwww.globalgolf.com%2Fgolf-clubs%2F1049077-taylormade-p790-2019-iron-set%2F%3Fopt%3Daaa%26utm_campaign%3Daff-lc%26utm_medium%3Daffiliate%26utm_source%3Dlcfeed%26utm_term%3DP790-2019
 */
class LinkConnector extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'LinkConnector';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'linkconnector';

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
