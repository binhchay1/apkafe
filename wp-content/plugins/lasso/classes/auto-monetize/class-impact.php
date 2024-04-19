<?php
/**
 * Declare class Impact
 *
 * @package Impact
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Auto_Monetize\Auto_Monetize;
use Lasso\Classes\Helper as Lasso_Helper;

/**
 * Impact
 * Example URL:
 * https://goto.walmart.com/c/3194906/1499545/9383?sourceid=imp_000011112222333344&amp;u=https%3A%2F%2Fwww.walmart.com%2Fcp%2Fsports-and-outdoors%2F4125%3Fpovid%3DHardlinesLHN_DSK_sports_sports_outdoors&amp;veh=aff
 * https://famous-smoke.7eer.net/c/1723571/1452984/974
 */
class Impact extends Auto_Monetize {
	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration = 'Impact';

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug = 'impact';

	/**
	 * Regex for checking link
	 *
	 * Examples:
	 * https://revolut.ngih.net/c/2500389/1471667/9626
	 * https://99designs.qvig.net/Daooq
	 * https://acorns.sjv.io/jjOqM
	 * https://betterment.evyy.net/BeK60
	 * https://crowdstreet.4cl7.net/a1BOaj
	 * https://creditkarma.myi4.net/GjAKzE
	 * https://buildium.ustnul.net/15a9qg
	 *
	 * @var string $regex
	 */
	private $regex = '~[\d\w]+\.\w+(\/(\w{4,12}))|(\/c\/(\d+)\/(\d+)\/(\d+))~';


	/**
	 * Set affiliate id
	 */
	protected function set_affiliate_id() {
		preg_match( $this->regex, $this->url, $matches );

		$affiliate_id = $matches[2] ?? '';

		// ? Url https://revolut.ngih.net/c/2500389/1471667/9626 => affiliate_id = 2500389
		$affiliate_id = $matches[4] ?? $affiliate_id;

		$this->affiliate_id = $affiliate_id;
	}

	/**
	 * Set advertiser id
	 */
	protected function set_advertiser_id() {
		preg_match( $this->regex, $this->url, $matches );

		// ? Url https://revolut.ngih.net/c/2500389/1471667/9626 => advertiser_id = 9626
		$this->advertiser_id = $matches[6] ?? '';
	}

	/**
	 * Set deep link
	 */
	protected function set_deep_link() {
		$this->deep_link = $this->get_deep_link( 'u' );
	}
}
