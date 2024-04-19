<?php
/**
 * Declare class CJ
 *
 * @package CJ
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Auto_Monetize\Auto_Monetize;
use Lasso\Classes\Helper as Lasso_Helper;

/**
 * CJ
 * Example URL:
 * https://www.jdoqocy.com/click-100321065-12513140?sid=comfortable-tree-stand&url=https%3A%2F%2Fwww.sportsmansguide.com%2Fproduct%2Findex%2Fsummit-viper-sd-climber-tree-stand%3Fa%3D1333086
 */
class CJ extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Commission Junction';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'cj';

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
