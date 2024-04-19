<?php
/**
 * Declare class FlexOffers
 *
 * @package FlexOffers
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;

/**
 * FlexOffers
 * Example URL: https://track.flexlinkspro.com/g.ashx?foid=24.157354.1454254&trid=1168081.157354&foc=16&fot=9999&fos=5&url=https%3A%2F%2Fwww.creditsesame.com%2Ffree-credit-score%2F
 * Let's break down the query parameters in the given URL:
	1. **`foid=24.157354.1454254`**: This parameter likely represents a unique identifier for a particular offer or product. It could be a combination of values that help identify the specific offer within the FlexOffers network.

	2. **`trid=1168081.157354`**: This parameter might stand for "transaction ID" and is often used to uniquely identify a particular click or transaction. It helps track the interaction from the click to the potential conversion.

	3. **`foc=16`**: This parameter could represent the "category" or "offer category." It may indicate the type or category of the product or service being promoted.

	4. **`fot=9999`**: The purpose of this parameter is not immediately clear without more context. It could be an internal identifier or code associated with tracking or reporting.

	5. **`fos=5`**: Similar to the previous parameter, the specific meaning of "fos" is not clear without additional context. It could be related to tracking, reporting, or categorization.

	6. **`url=https%3A%2F%2Fwww.creditsesame.com%2Ffree-credit-score%2F`**: This parameter is not for tracking purposes but is the actual destination URL that the user will be redirected to when clicking the affiliate link. The URL is URL-encoded, and when decoded, it leads to "https://www.creditsesame.com/free-credit-score/."

	In summary, the query parameters in this URL are likely used for tracking and identifying specific details related to the affiliate marketing campaign, such as the offer, transaction, category, and potentially other internal codes within the FlexOffers network. The destination URL is also included as part of the parameters. The exact meanings may vary based on the internal conventions of the affiliate marketing platform.
 */
class FlexOffers extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'FlexOffers';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'flexoffers';

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
