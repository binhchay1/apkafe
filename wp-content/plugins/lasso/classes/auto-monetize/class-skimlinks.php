<?php
/**
 * Declare class Skimlinks
 *
 * @package Skimlinks
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Auto_Monetize\Auto_Monetize;
use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Skimlinks
 * Example URL:
 *  https://go.skimresources.com/?id=116152X1597589&xs=1&url=https%3A%2F%2Fwww.instacart.com%2Fproducts%2F55125-baby-s-only-toddler-formula-dairy-12-7-oz&xcust=best-hypoallergenic-baby-formula_amcid-YMlQONOYgbFpMY7e7YiUI
 *  http://go.effortlessgent.com/?id=47795X1194469&isjs=1&jv=15.3.0-stackpath&sref=https%3A%2F%2Feffortlessgent.com%2Ftypes-of-boots%2F&url=https%3A%2F%2Fwww.amazon.com%2Fdp%2FB07NJKCXRR%3Ftag%3Dbarroncuadroc-20&xs=1&xtz=480&xuuid=a0d508998c631a16170806905c571601&cci=5887fe2a720b344200ff9250551bb0de
 */
class Skimlinks extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Skimlinks';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'skimlinks';

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
		$this->advertiser_id = null;
	}

	/**
	 * Set deep link
	 */
	protected function set_deep_link() {
		$this->deep_link = $this->get_deep_link( 'url' );
	}
}
