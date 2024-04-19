<?php
/**
 * Declare class Auto_Monetize
 *
 * @package Auto_Monetize
 */

namespace Lasso\Classes\Auto_Monetize;

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Models\Auto_Monetize as Model_Auto_Monetize;
use Lasso\Classes\Affiliates as Lasso_Affiliates;

/**
 * Auto_Monetize
 */
abstract class Auto_Monetize {
	/**
	 * Affiliate id
	 *
	 * @var string $affiliate_id
	 */
	protected $affiliate_id;

	/**
	 * Advertiser id
	 *
	 * @var string $advertiser_id
	 */
	protected $advertiser_id;

	/**
	 * Domain
	 *
	 * @var string $domain
	 */
	protected $domain;

	/**
	 * Integration name
	 *
	 * @var string $integration
	 */
	protected $integration;

	/**
	 * Integration slug
	 *
	 * @var string $integration_slug
	 */
	protected $integration_slug;

	/**
	 * URL
	 *
	 * @var string $url
	 */
	protected $url;

	/**
	 * Deep link
	 *
	 * @var string $deep_link
	 */
	protected $deep_link;

	const AFFILIATES_CLASSES = array(
		'Agoda',
		'AvantLink',
		'Awin',
		'Bol',
		'Booking',
		'Cake',
		'CJ',
		'ClickBank',
		'Daisycon',
		'Ebay',
		'FinanceAds',
		'FlexOffers',
		'GetYourGuide',
		'GoAffPro',
		'IDevaffiliate',
		'Impact',
		'LinkConnector',
		'MaxBounty',
		'Partnerize',
		'PartnerStack',
		'Pepperjam',
		'PostAffiliatePro',
		'Rakuten',
		'Refersion',
		'Rentalcars',
		'ShareASale',
		'Skimlinks',
		'Sovrn',
		'Tradedoubler',
		'TradeTracker',
		'Tune',
		'Webgains',
	);

	/**
	 * Constructor
	 *
	 * @param string $url URL.
	 */
	public function __construct( $url ) {
		$this->url = html_entity_decode( $url );

		$this->set_affiliate_id();
		$this->set_advertiser_id();
		$this->set_deep_link();
	}

	/**
	 * Set affiliate id
	 */
	abstract protected function set_affiliate_id();

	/**
	 * Set advertiser id
	 */
	abstract protected function set_advertiser_id();

	/**
	 * Set deep link
	 */
	abstract protected function set_deep_link();

	/**
	 * Check whether URL is valid
	 */
	public function is_valid_url() {
		$affiliate      = new Lasso_Affiliates();
		$affiliate_slug = $affiliate->is_affiliate_link( $this->url, false, $this->integration_slug );

		return $this->integration_slug === $affiliate_slug;
	}

	/**
	 * Map data and insert into the auto monetize table
	 */
	public function map_data() {
		if ( ! $this->integration || ! $this->url || ! $this->is_valid_url() ) {
			return false;
		}

		try {
			$model       = new Model_Auto_Monetize();
			$url_encrypt = md5( $this->url );

			$model->set_integration( $this->integration );
			$model->set_affiliate_id( $this->affiliate_id );
			$model->set_advertiser_id( $this->advertiser_id );
			$model->set_url( $this->url );
			$model->set_deep_link( $this->deep_link );
			$model->set_domain( $this->domain );
			$model->set_url_encrypt( $url_encrypt );

			$row = $model->get_one_by_col( 'url_encrypt', $url_encrypt );
			if ( ! $row->get_id() ) {
				$model->insert();
			}
		} catch ( \Exception $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Call a method in this class
	 *
	 * @param string $method Method name.
	 * @param array  $args   Arguments.
	 */
	public function __call( $method, $args ) {
		$prefix = substr_replace( $method, '', 4 );

		switch ( $prefix ) {
			case 'get_':
				return $this->$method;

			case 'set_':
				$this->$method = $args[0] ?? null;
				break;
		}

		return null;
	}

	/**
	 * Set value for a property
	 *
	 * @param string $name  Function name (set_property_name).
	 * @param mix    $value Property value.
	 */
	public function __set( $name, $value ) {
		$method   = strtolower( $name );
		$property = $this->get_property_name( $method );

		if ( ! $property ) {
			$property = $method;
		}

		// ? see if there exists a extra setter method: setName()
		if ( ! method_exists( $this, $method ) ) {
			// ? if there is no setter, receive all public/protected vars and set the correct one if found
			$this->$property = $value;
		} else {
			$this->$method( $value ); // ? call the setter with the value
		}
	}

	/**
	 * Get value for a property
	 *
	 * @param string $name Function name (get_property_name).
	 *
	 * @return mixed Property value.
	 */
	public function __get( $name ) {
		$method   = strtolower( $name );
		$property = $this->get_property_name( $method );

		if ( ! $property ) {
			$property = $method;
		}

		// ? see if there is an extra getter method: get_name()
		if ( ! method_exists( $this, $method ) ) {
			// ? if there is no getter, receive all public/protected vars and return the correct one if found
			return $this->$property ?? null;
		} else {
			return $this->$method(); // ? call the getter
		}

		return null;
	}

	/**
	 * Get property name by method
	 *
	 * @param string $method Method.
	 */
	private function get_property_name( $method ) {
		return substr_replace( $method, '', 0, 4 );
	}

	/**
	 * Get deep link from URL parameter.
	 *
	 * @param string $argument Argument.
	 * @return string
	 */
	protected function get_deep_link( $argument ) {
		$deep_link = Lasso_Helper::get_argument_from_url( $this->url, $argument );
		$deep_link = html_entity_decode( $deep_link );

		if ( $deep_link ) {
			$base_domain  = Lasso_Helper::get_base_domain( $deep_link );
			$this->domain = $base_domain;
		}

		return $deep_link ? $deep_link : null;
	}
}
