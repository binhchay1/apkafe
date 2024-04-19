<?php
/**
 * Declare class Partnerize
 *
 * @package Partnerize
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Partnerize
 * Example URL:
 * https://adorebeauty.prf.hn/click/camref:1011lqzzd/pubref:beautyspace.com.au%2Fbest-purple-shampoos-australia%2F/destination:https%3A%2F%2Fwww.adorebeauty.com.au%2Fredken%2Fredken-color-extend-blondage-shampoo-high-bright-300ml.html%3F
 */
class Partnerize extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Partnerize';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'partnerize';

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
		$deep_link = explode( '/destination:', $this->url )[1] ?? '';
		$deep_link = $deep_link ? html_entity_decode( $deep_link ) : '';
		$deep_link = $deep_link ? urldecode( $deep_link ) : '';

		if ( $deep_link ) {
			$base_domain  = Lasso_Helper::get_base_domain( $deep_link );
			$this->domain = $base_domain;
		}

		$this->deep_link = $deep_link;
	}
}
