<?php
/**
 * Declare class Webgains
 *
 * @package Webgains
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Webgains
 * // phpcs:disable
 * A typical Webgains affiliate link might have a structure similar to the following:
	https://track.webgains.com/click.html?wgcampaignid=12345&wgprogramid=6789&product=1&wglinkid=54321
	Here's a breakdown of some components:

	https://track.webgains.com/click.html: This is the base URL that initiates the click tracking.

	wgcampaignid=12345: The "wgcampaignid" parameter might represent the campaign ID associated with the specific promotion or marketing campaign.

	wgprogramid=6789: The "wgprogramid" parameter could represent the program ID or offer ID associated with the product or service being promoted.

	product=1: The "product" parameter might indicate a specific product ID or SKU being promoted.

	wglinkid=54321: The "wglinkid" parameter could represent additional tracking information, such as a link or click identifier.
 * Example URL:
 * https://track.webgains.com/click.html?wgcampaignid=185377&wgprogramid=8445&clickref=ref=post-hpi&wgtarget=https%3A%2F%2Fhpicheck.com%2Fbike-check
 * // phpcs:enable
 */
class Webgains extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Webgains';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'webgains';

	/**
	 * Set affiliate id
	 */
	protected function set_affiliate_id() {
		$this->affiliate_id = Lasso_Helper::get_argument_from_url( $this->url, 'wglinkid', true, true );
	}

	/**
	 * Set advertiser id
	 */
	protected function set_advertiser_id() {
		$this->advertiser_id = Lasso_Helper::get_argument_from_url( $this->url, 'wgprogramid', true, true );
	}

	/**
	 * Set deep link
	 */
	protected function set_deep_link() {
		$this->deep_link = $this->get_deep_link( 'wgtarget' );
	}
}
