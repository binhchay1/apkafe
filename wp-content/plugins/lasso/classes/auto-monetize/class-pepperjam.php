<?php
/**
 * Declare class Pepperjam
 *
 * @package Pepperjam
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Pepperjam
 * Example URL:
 *  https://www.pjtra.com/t/TUJGRUdLTUJGTktHTU5CRkdKR0VJ?url=https%3A%2F%2Fwww.mottandbow.com%2Fmerino-wool-v-neck-lucas-charcoal-gray.html
 */
class Pepperjam extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Pepperjam (Ascend)';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'pepperjam';

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
