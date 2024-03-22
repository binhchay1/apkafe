<?php
/**
 * Product Class
 *
 * This class is for interacting with a single product
 *
 * @package     AAWP
 * @since       3.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AAWP_Product Class
 *
 * @since 2.1
 */
class AAWP_Product {

    /**
     * Holding our product data
     *
     * @var
     */
    private $data;

    /**
     * Get things started
     *
     * @param $data
     */
    public function __construct( $data ) {

        // Setup product data
        $this->data = $data;
    }

    /**
     * Get type
     *
     * @return string/null
     */
    public function get_type() {
        return ( ! empty( $this->data['type'] ) ) ? $this->data['type'] : null;
    }

    /**
     * Get asin
     *
     * @return string/null
     */
    public function get_asin() {
        return ( ! empty( $this->data['asin'] ) ) ? $this->data['asin'] : null;
    }

    /**
     * Get ean
     *
     * @return string/null
     */
    public function get_ean() {
        return ( ! empty( $this->data['ean'] ) ) ? $this->data['ean'] : null;
    }

    /**
     * Get isbn
     *
     * @return string/null
     */
    public function get_isbn() {
        return ( ! empty( $this->data['isbn'] ) ) ? $this->data['isbn'] : null;
    }

    /**
     * Get title
     *
     * @return string/null
     */
    public function get_title() {
        return ( ! empty( $this->data['title'] ) ) ? trim( $this->data['title'] ) : null;
    }

    /**
     * Get url
     *
     * @param string $type
     *
     * @return string/null
     */
    public function get_url( $type = 'basic' ) {

        // Cart
        if ( 'cart' === $type ) {
            $url = 'https://www.amazon.' . AAWP_STORE . '/gp/aws/cart/add.html?ASIN.1=' . $this->get_asin() . '&Quantity.1=1&tag=' . AAWP_PLACEHOLDER_TRACKING_ID;

        // Reviews
        } elseif ( 'reviews' === $type ) {
            $url = 'https://www.amazon.' . AAWP_STORE . '/product-reviews/' . $this->get_asin() . '?tag=' . AAWP_PLACEHOLDER_TRACKING_ID;

        // Default (basic)
        } else {
            $url = ( ! empty( $this->data['url'] ) ) ? $this->data['url'] : null;
        }

        /* Temporarily disabled due to latest Amazon changes
        $affiliate_links_type = aawp_get_affiliate_links_type();

        if ( 'shorted' === $affiliate_links_type ) {
            $url = aawp_generate_shortened_affiliate_link( $this->get_asin() );
        } else {
            $url = ( isset( $this->data['urls'][$type] ) ) ? $this->data['urls'][$type] : null;
        }
        */

        return $url;
    }

    /**
     * Get image ids
     *
     * @return array
     */
    private function get_image_ids() {
        return ( isset( $this->data['image_ids'] ) && is_array( $this->data['image_ids'] ) ) ? $this->data['image_ids'] : array();
    }

    /**
     * Get image
     *
     * @param int $number
     * @param string $size
     *
     * @return null|string
     */
    public function get_image( $number = 0, $size = 'medium' ) {

        $image_ids = $this->get_image_ids();

        $images_count = $this->get_image_count();

        if ( $number > $images_count )
            $number = $images_count;

        if ( $number < 1 )
            $number = 1;

        $number--; // User input starts with 1, our array index starts with 0

        $image_id = ( isset( $image_ids[$number] ) ) ? $image_ids[$number] : null;

        // Maybe fallback to first image, if desired image is not available
        if ( empty( $image_id ) && isset( $image_ids[0] ) )
            $image_id = $image_ids[0];

        return ( ! empty( $image_id ) ) ? $this->get_image_url( $image_id, $size ) : null;
    }

	/**
	 * Get image url
	 *
	 * @param $image_id
	 * @param $size
	 *
	 * @return null|string
	 */
    private function get_image_url( $image_id, $size ) {

		if ( aawp_is_product_local_images_activated() ) {
			$image_url = aawp_build_product_local_image_url( $image_id, $size );
		} else {
			$image_url = aawp_build_product_image_url( $image_id, $size );
		}

		return ( ! empty( $image_url ) ) ? $image_url : null;
    }

    /**
     * Get images
     *
     * @return array
     */
    public function get_images() {

        $images = array(); // TODO

        return $images;
    }

    /**
     * Get image count
     *
     * @return int
     */
    public function get_image_count() {

        $image_ids = $this->get_image_ids();

        return ( is_array( $image_ids ) ) ? sizeof( $image_ids ) : 0;
    }

    /**
     * Get description
     *
     * @param array $args
     *
     * @return array|null|string
     */
    public function get_description( $args = array() ) {

        $description = aawp_generate_product_description( $this->data, $args, true );

        return $description;
    }

    /**
     * Get attributes
     *
     * @return null/array
     */
    public function get_attributes() {
        return ( ! empty( $this->data['attributes'] ) ) ? $this->data['attributes'] : null;
    }

    /**
     * Get teaser
     *
     * @return array|null
     */
    public function get_teaser() {

        $teaser = aawp_generate_product_teaser( $this->data );

        return ( ! empty( $teaser ) ) ? $teaser : null;
    }

    /**
     * Check whether product is discounted or not
     */
    public function is_discounted() {

        $saving = $this->get_price_savings();

        return ( ! empty( $saving ) ) ? true : false;
    }

    /**
     * Get price saving (amount)
     *
     * @param bool $formatted
     *
     * @return int|mixed|null
     */
    public function get_price_savings( $formatted = false ) {

        $savings = ( ! empty( $this->data['savings'] ) && is_numeric( $this->data['savings'] ) ) ? floatval( $this->data['savings'] ) : 0;

        return ( ! empty( $savings ) && $formatted ) ? aawp_format_price_currency( $savings ) : $savings;
    }

    /**
     * Get price savings (percentage)
     *
     * @param bool $formatted
     *
     * @return float|int|string
     */
    public function get_price_savings_percentage( $formatted = false ) {

        $savings_percentage = ( ! empty( $this->data['savings_percentage'] ) && is_numeric( $this->data['savings_percentage'] ) ) ? floatval( $this->data['savings_percentage'] ) : 0;

        return ( ! empty( $savings_percentage ) && $formatted ) ? $savings_percentage . '%' : $savings_percentage;
    }

    /**
     * Get price savings basis (amount)
     *
     * @param bool $formatted
     *
     * @return int|mixed|null
     */
    public function get_price_savings_basis( $formatted = false ) {

        $savings_basis = ( ! empty( $this->data['savings_basis'] ) && is_numeric( $this->data['savings_basis'] ) ) ? floatval( $this->data['savings_basis'] ) : 0;

        return ( ! empty( $savings_basis ) && $formatted ) ? aawp_format_price_currency( $savings_basis ) : $savings_basis;
    }

    /**
     * Get price
     *
     * @param string $type
     * @param bool $formatted
     *
     * @return mixed|null
     */
    public function get_price( $type = 'display', $formatted = false ) {

        // TODO: Price type
        if ( 'list' === $type ) {
            $price = ( ! empty( $this->data['savings_basis'] ) ) ? $this->data['savings_basis'] : 0;
        } else {
            $price = ( ! empty( $this->data['price'] ) ) ? $this->data['price'] : 0;
        }

        // Prevent formatting issues in case a price was stored with comma instead of dot as decimal char
        if ( ! empty ( $price ) )
            $price = str_replace(',', '.', $price );

        if ( $formatted ) {
            return aawp_format_price_currency( $price );
        }

        /*
        // Defaults
        $is_display_price = false;
        $is_variation = false;

        // Handle different price types
        if ( 'list' === $type ) {
            $price = ( isset( $this->data['list_price'] ) ) ? $this->data['list_price'] : null;
        } elseif ( 'used' === $type ) {
            $price = ( isset( $this->data['used_price'] ) ) ? $this->data['used_price'] : null;
        } elseif ( 'variation' === $type ) {
            $price = ( isset( $this->data['variation_price'] ) ) ? $this->data['variation_price'] : null;
        } elseif ( 'display' === $type && ( isset( $this->data['variation_price'] ) ) && $this->data['variation_price'] > 0 ) {
            $price = $this->data['variation_price'];
            $is_variation = true;
            $is_display_price = true;
        } else {
            $price = ( isset( $this->data['price'] ) ) ? $this->data['price'] : null;
            $is_display_price = true;
        }

        $price = ( is_numeric( $price ) ) ? floatval( $price ) : 0;

        if ( $formatted ) {

            // eBooks
            if ( $is_display_price && 'ABIS_EBOOKS' === $this->get_type() )
                return null;
                //return '<span class="aawp-kindle-edition-info">' . __('Kindle Edition', 'aawp') . '</span>';

            // Variations
            if ( $is_variation )
                return sprintf( esc_html__( 'from %s', 'aawp' ), aawp_format_price_currency( $price ) );

            // Amazon Prime Video
            if ( ! $price && ( 'DOWNLOADABLE_TV_SEASON' === $this->get_type() || 'DOWNLOADABLE_MOVIE' === $this->get_type() ) ) {
                return '<span class="aawp-not-available-info">' . __( 'Available on Amazon Prime Video', 'aawp' ) . '</span>';

            // Amazon Video
            } elseif ( $price && 'DOWNLOADABLE_MOVIE' === $this->get_type() ) {
                return '<span class="aawp-not-available-info">' . __( 'Available on Amazon Video', 'aawp' ) . '</span>';

            // Maybe hide unavailable pricing
            } elseif ( ( ! $price || 'unknown' === $this->get_availability() ) && '1' == aawp_get_option( 'pricing_advertised_price_hide_unavailability', 'output' ) ) {
                return null;

            // Out of stock
            } elseif ( 'unknown' === $this->get_availability() ) {
                return '<span class="aawp-not-available-notice">' . __( 'Currently out of stock', 'aawp' ) . '</span>';

            // Not available
            } elseif ( ! $price && ( isset( $this->data['used_price'] ) ) && $this->data['used_price'] == 0 ) {
                return '<span class="aawp-not-available-notice">' . __( 'Currently not available', 'aawp' ) . '</span>';

            // No new offers available
            } elseif ( ! $price && ( isset( $this->data['used_price'] ) ) && floatval( $this->data['used_price'] ) > 0 ) {
                return '<span class="aawp-not-available-notice">' . __( 'No new offers available', 'aawp' ) . '</span>';

            // Default
            } else {
                return aawp_format_price_currency( $price );
            }
        }
        */

        return $price;
        //return ( ! $price || is_null( $price ) || 0 == $price )
    }

    /**
     * Check whether product is available with prime benefits or not
     *
     * @return bool
     */
    public function is_prime() {
        return ( ! empty( $this->data['is_prime'] ) ) ? true : false;
    }

    /**
     * Get availability
     *
     * @return string/null
     */
    public function get_availability() {
        return ( ! empty( $this->data['availability'] ) ) ? $this->data['availability'] : null;
    }

    /**
     * Get rating
     *
     * @return int
     */
    public function get_rating() {

        $rating = ( ! empty( $this->data['rating'] ) ) ? floatval( $this->data['rating'] ) : 0;

        $rating = number_format( $rating, 1 );

        return ( $rating > 0 ) ? $rating : 0;
    }

    /**
     * Get reviews
     *
     * @return int
     */
    public function get_reviews() {

        $reviews = ( ! empty( $this->data['reviews'] ) ) ? $this->data['reviews'] : 0;

        return ( $reviews > 0 ) ? $reviews : 0;
    }

    /**
     * Get salesrank
     *
     * @return int
     */
    public function get_salesrank() {
        return ( ! empty( $this->data['salesrank'] ) ) ? $this->data['salesrank'] : 0;
    }

    /**
     * Get date created
     *
     * @return string/null
     */
    public function get_date_created() {
        return ( ! empty( $this->data['date_created'] ) ) ? $this->data['date_created'] : null;
    }

    /**
     * @return string/null
     */
    public function get_date_updated() {
        return ( ! empty( $this->data['date_updated'] ) ) ? $this->data['date_updated'] : null;
    }

    /**
     * Get timestamp
     *
     * @return string/null
     */
    public function get_timestamp() {

        $date_updated = $this->get_date_updated();

        return ( ! empty( $date_updated ) ) ? strtotime( $date_updated ) : null;
    }

    /**
     * Get last update (formatted, based on timestamp)
     *
     * @return string
     */
    public function get_last_update() {

        $timestamp = $this->get_timestamp();

        return aawp_format_last_update( $timestamp );
    }
}