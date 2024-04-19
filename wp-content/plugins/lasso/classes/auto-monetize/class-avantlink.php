<?php
/**
 * Declare class AvantLink
 *
 * @package AvantLink
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * AvantLink
 * Example URL:
 * https://www.avantlink.com/click.php?tt=cl&mi=22053&pw=306293&url=https%3A%2F%2Fwww.escapecampervans.com%2F
 * https://www.avantlink.com/click.php?tt=cl&mi=20297&pw=306293&url=https%3A%2F%2Flunolife.com%2F
 * https://www.avantlink.com/click.php?tt=cl&merchant_id=e0a3fde0-21fd-48e6-8c93-38e5f2b0ecd0&website_id=d983e9ed-2828-47ba-9fd1-8d0ff175982c&url=https://www.farmtofeet.com/pages/mountains-to-sea-trail-collection&ctc=aogdarntoughvsfarmtofeet
 */
class AvantLink extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'AvantLink';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'avantlink';

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
		$mi = Lasso_Helper::get_argument_from_url( $this->url, 'mi', true, true );
		if ( ! $mi ) {
			$mi = Lasso_Helper::get_argument_from_url( $this->url, 'merchant_id', true, true );
		}

		$this->advertiser_id = $mi;
	}

	/**
	 * Set deep link
	 */
	protected function set_deep_link() {
		$this->deep_link = $this->get_deep_link( 'url' );
	}
}
