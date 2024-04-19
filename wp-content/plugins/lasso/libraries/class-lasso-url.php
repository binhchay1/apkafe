<?php
/**
 * Declare Class Lasso_URL
 *
 * @package Lasso\Library
 */

namespace Lasso\Libraries;

use Lasso_Affiliate_Link;

use Lasso\Classes\Helper as Lasso_Helper;
use Lasso\Classes\Html_Helper as Lasso_Html_Helper;
use Lasso\Classes\Setting as Lasso_Setting;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Lasso_URL
 *
 * @package Lasso\Libraries
 */
class Lasso_URL {
	/**
	 * Lasso id
	 *
	 * @var $lasso_id
	 */
	public $lasso_id;

	/**
	 * Name
	 *
	 * @var $name
	 */
	public $name;

	/**
	 * Primary button text
	 *
	 * @var $primary_button_text
	 */
	public $primary_button_text;

	/**
	 * Primary link
	 *
	 * @var $primary_link
	 */
	public $primary_link;

	/**
	 * Secondary button text
	 *
	 * @var $secondary_button_text
	 */
	public $secondary_button_text;

	/**
	 * Secondary url
	 *
	 * @var $secondary_url
	 */
	public $secondary_url;

	/**
	 * Image src
	 *
	 * @var $image_src
	 */
	public $image_src;

	/**
	 * Target url
	 *
	 * @var $target_url
	 */
	public $target_url;

	/**
	 * Price
	 *
	 * @var $price
	 */
	public $price;

	/**
	 * Html attributes
	 *
	 * @var $html_attribute
	 */
	public $html_attribute;

	/**
	 * Public link
	 *
	 * @var $public_link
	 */
	public $public_link;

	/**
	 * Amazon product
	 *
	 * @var $amazon
	 */
	public $amazon;

	/**
	 * Description html text
	 *
	 * @var $description
	 */
	public $description;

	/**
	 * Badge text
	 *
	 * @var $badge_text
	 */
	public $badge_text;

	/**
	 * Link from display title
	 *
	 * @var $link_from_display_title
	 */
	public $link_from_display_title;

	/**
	 * Lasso_URL constructor.
	 *
	 * @param int|object $lasso_url Lasso URL object or Lasso post id.
	 */
	public function __construct( $lasso_url ) {
		$lasso_url = is_object( $lasso_url ) ? $lasso_url : Lasso_Affiliate_Link::get_lasso_url( $lasso_url );

		$this->lasso_id              = $lasso_url->lasso_id;
		$this->name                  = $lasso_url->name;
		$this->primary_button_text   = $lasso_url->display->primary_button_text;
		$this->primary_link          = $lasso_url->target_url;
		$this->secondary_button_text = $lasso_url->display->secondary_button_text;
		$this->secondary_url         = $lasso_url->display->secondary_url;
		$this->image_src             = $lasso_url->image_src;
		$this->target_url            = $lasso_url->target_url;
		$this->price                 = $lasso_url->price;
		$this->html_attribute        = $lasso_url->html_attribute;
		$this->public_link           = $lasso_url->public_link;
		$this->amazon                = $lasso_url->amazon;
		$this->description           = $lasso_url->description;
		$this->badge_text            = $lasso_url->display->badge_text;

		if ( $this->amazon->last_updated ?? '' ) {
			$this->amazon->last_updated_format = $lasso_url->display->last_updated;
		}
		$this->link_from_display_title = Lasso_Setting::lasso_get_setting( 'link_from_display_title', true );

	}

	/**
	 * Get link detail
	 *
	 * @return string
	 */
	public function get_link_detail() {
		return 'edit.php?post_type=lasso-urls&page=url-details&post_id=' . $this->lasso_id;
	}

	/**
	 * Get Lasso_URL
	 *
	 * @param int $lasso_id Lasso ID.
	 *
	 * @return Lasso_URL
	 */
	public static function get_by_lasso_id( $lasso_id ) {
		return new self( $lasso_id );
	}

	/**
	 * Get price.
	 *
	 * @return string
	 */
	public function get_price() {
		return ! empty( $this->price ) ? $this->price : 'N/A';
	}

	/**
	 * Get description.
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get attributes
	 *
	 * @param null|string $url URL.
	 *
	 * @return array
	 */
	private function get_attrs( $url = null ) {
		$attrs = array(
			'target'                   => $this->html_attribute->target,
			'href'                     => $this->public_link,
			'data-lasso-box-trackable' => 'true',
			'data-lasso-id'            => $this->lasso_id,
			'data-lasso-name'          => $this->name,
			'title'                    => $this->name,
		);
		return $attrs;
	}

	/**
	 * Render attributes.
	 *
	 * @param string $url         Custom Url.
	 * @param string $custom_name Custom name.
	 * @return string
	 */
	public function render_attributes( $url = null, $custom_name = null ) {
		$attrs = $this->get_attrs();

		if ( ! empty( $url ) ) {
			$attrs['href'] = $url;
		}

		if ( empty( trim( $attrs['data-lasso-name'] ) ) ) {
			$attrs['data-lasso-name'] = ucfirst( Lasso_Helper::get_name_of_domain( $attrs['href'] ) );
		}

		if ( ! empty( $custom_name ) ) {
			$attrs['data-lasso-name'] = $custom_name;
		}

		$attrs_str  = Lasso_Html_Helper::generate_attrs( $attrs );
		$attrs_str .= $this->html_attribute->rel;

		return $attrs_str;
	}

	/**
	 * Render attributes for secondary button
	 *
	 * @param string $url         Custom URL.
	 * @param string $custom_name Custome Name.
	 * @return string
	 */
	public function render_attributes_second( $url = null, $custom_name = null ) {
		$attrs                      = $this->get_attrs();
		$attrs['data-lasso-button'] = 2;
		$attrs['href']              = $this->secondary_url;
		$attrs['target']            = $this->html_attribute->target2;

		if ( ! empty( $url ) ) {
			$attrs['href'] = $url;
		}

		if ( empty( trim( $attrs['data-lasso-name'] ) ) ) {
			$attrs['data-lasso-name'] = ucfirst( Lasso_Helper::get_name_of_domain( $attrs['href'] ) );
		}

		if ( ! empty( $custom_name ) ) {
			$attrs['data-lasso-name'] = $custom_name;
		}

		if ( empty( trim( $this->secondary_button_text ) ) ) {
			unset( $attrs['target'], $attrs['href'] );
		}
		$attrs_str  = Lasso_Html_Helper::generate_attrs( $attrs );
		$attrs_str .= $this->html_attribute->rel2;

		return $attrs_str;
	}

}
