<?php
/**
 * Amazon Product Advertising API v5
 *
 * The use of this library is strictly prohibited without explicit permission.
 *
 * Copyright 2020 flowdee. All Rights Reserved.
 *
 * Twitter: https://twitter.com/flowdee
 * GitHub: https://github.com/flowdee
 */
namespace Flowdee\AmazonPAAPI5WP;

/**
 * Class Item
 *
 * @category Class
 * @package Flowdee\AmazonPAAPI5
 * @author flowdee
 */
class Item {

    /**
     * Data
     *
     * @var array
     */
    public $data;

    /**
     * Helper data.
     */
    private $dataItemInfo;
    private $dataContentInfo;
    private $dataItemOffers;

    /**
     * Item constructor.
     *
     * @param $data
     */
    public function __construct( $data ) {

        // Set data.
        $this->data = $data;

        // Set helper data.
        $this->dataItemInfo = ( isset ( $data['ItemInfo'] ) ) ? $data['ItemInfo'] : array();
        $this->dataContentInfo = ( isset ( $data['ItemInfo']['ContentInfo'] ) ) ? $data['ItemInfo']['ContentInfo'] : array();
        $this->dataItemOffers = ( isset ( $data['Offers'] ) ) ? $data['Offers'] : array();
    }

    /**
     * Get ASIN
     *
     * @return mixed|null
     */
    public function getASIN() {
        return ( ! empty( $this->data['ASIN'] ) ) ? $this->data['ASIN'] : null;
    }

    /**
     * Get Parent ASIN
     *
     * @return mixed|null
     */
    public function getParentASIN() {
        return ( ! empty( $this->data['ParentASIN'] ) ) ? $this->data['ParentASIN'] : null;
    }

    /**
     * Get EAN
     *
     * @return mixed|null
     */
    public function getEAN() {
        return ( ! empty ( $externalIds = $this->getExternalIds() ) && isset ( $externalIds['ean'] ) ) ? $externalIds['ean'] : null;
    }

    /**
     * Get ISBN
     *
     * @return mixed|null
     */
    public function getISBN() {
        return ( ! empty ( $externalIds = $this->getExternalIds() ) && isset ( $externalIds['isbn'] ) ) ? $externalIds['isbn'] : null;
    }

    /**
     * Get UPC
     *
     * @return mixed|null
     */
    public function getUPC() {
        return ( ! empty ( $externalIds = $this->getExternalIds() ) && isset ( $externalIds['upc'] ) ) ? $externalIds['upc'] : null;
    }

    /**
     * Get URL
     *
     * @return mixed|null
     */
    public function getURL() {
        return ( ! empty( $this->data['DetailPageURL'] ) ) ? $this->data['DetailPageURL'] : null;
    }

    /**
     * Get title
     *
     * @return mixed|null
     */
    public function getTitle() {
        return ( ! empty ( $this->dataItemInfo['Title']['DisplayValue'] ) ) ? $this->dataItemInfo['Title']['DisplayValue'] : null;
    }

    /**
     * Get browse nodes
     *
     * @return array
     */
    public function getBrowseNodes() {
        return null; // TODO
    }

    /**
     * Get website sales rank
     *
     * @return array
     */
    public function getWebsiteSalesRank() {

        return array(
            'contextFreeName' => ( ! empty ( $this->data['BrowseNodeInfo']['WebsiteSalesRank']['ContextFreeName'] ) ) ? $this->data['BrowseNodeInfo']['WebsiteSalesRank']['ContextFreeName'] : null,
            'displayName' => ( ! empty ( $this->data['BrowseNodeInfo']['WebsiteSalesRank']['DisplayName'] ) ) ? $this->data['BrowseNodeInfo']['WebsiteSalesRank']['DisplayName'] : null,
            'salesRank' => ( ! empty ( $this->data['BrowseNodeInfo']['WebsiteSalesRank']['SalesRank'] ) ) ? $this->data['BrowseNodeInfo']['WebsiteSalesRank']['SalesRank'] : null
        );
    }

    /**
     * Get sales rank
     *
     * @return mixed|null
     */
    public function getSalesRank() {
        return ( ! empty ( $websiteSalesRank = $this->getWebsiteSalesRank() ) && isset ( $websiteSalesRank['salesRank'] ) ) ? $websiteSalesRank['salesRank'] : null;
    }

    /**
     * Get by line information
     *
     * @return array
     */
    public function getByLineInfo() {

        $byLineInfo = array(
            'brand' => ( ! empty ( $this->dataItemInfo['ByLineInfo']['Brand']['DisplayValue'] ) ) ? $this->dataItemInfo['ByLineInfo']['Brand']['DisplayValue'] : null,
            'manufacturer' => ( ! empty ( $this->dataItemInfo['ByLineInfo']['Manufacturer']['DisplayValue'] ) ) ? $this->dataItemInfo['ByLineInfo']['Manufacturer']['DisplayValue'] : null,
            'contributors' => null
        );

        // Contributors.
        if ( ! empty ( $this->dataItemInfo['ByLineInfo']['Contributors'] ) && is_array( $this->dataItemInfo['ByLineInfo']['Contributors'] ) ) {

            $contributors = array();

            foreach ( $this->dataItemInfo['ByLineInfo']['Contributors'] as $contributor ) {

                $contributors[] = array(
                    'name' => ( ! empty ( $contributor['Name'] ) ) ? $contributor['Name'] : null,
                    'role' => ( ! empty ( $contributor['Role'] ) ) ? $contributor['Role'] : null,
                    'roleType' => ( ! empty ( $contributor['RoleType'] ) ) ? $contributor['RoleType'] : null
                );
            }

            $byLineInfo['contributors'] = $contributors;
        }

        return $byLineInfo;
    }

    /**
     * Get classifications
     *
     * @return array
     */
    public function getClassifications() {

        return array(
            'binding' => ( ! empty ( $this->dataItemInfo['Classifications']['Binding']['DisplayValue'] ) ) ? $this->dataItemInfo['Classifications']['Binding']['DisplayValue'] : null,
            'productGroup' => ( ! empty ( $this->dataItemInfo['Classifications']['ProductGroup']['DisplayValue'] ) ) ? $this->dataItemInfo['Classifications']['ProductGroup']['DisplayValue'] : null,
        );
    }

    /**
     * Get binding
     *
     * @return mixed|null
     */
    public function getBinding() {
        return ( ! empty ( $classifications = $this->getClassifications() ) && isset ( $classifications['binding'] ) ) ? $classifications['binding'] : null;
    }

    /**
     * Get product group
     *
     * @return mixed|null
     */
    public function getProductGroup() {
        return ( ! empty ( $classifications = $this->getClassifications() ) && isset ( $classifications['productGroup'] ) ) ? $classifications['productGroup'] : null;
    }

    /**
     * Get content information
     *
     * @return array
     */
    public function getContentInfo() {

        $contentInfo = array(
            'edition' => ( ! empty ( $this->dataContentInfo['Edition']['DisplayValue'] ) ) ? $this->dataContentInfo['Edition']['DisplayValue'] : null,
            'languages' => null,
            'pagesCount' => ( ! empty ( $this->dataContentInfo['PagesCount']['DisplayValue'] ) ) ? $this->dataContentInfo['PagesCount']['DisplayValue'] : null,
            'publicationDate' => ( ! empty ( $this->dataContentInfo['PublicationDate']['DisplayValue'] ) ) ? $this->dataContentInfo['PublicationDate']['DisplayValue'] : null
        );

        // Languages.
        if ( ! empty ( $this->dataContentInfo['Languages']['DisplayValues'] ) && is_array( $this->dataContentInfo['Languages']['DisplayValues'] ) ) {

            $languages = array();

            foreach ( $this->dataContentInfo['Languages']['DisplayValues'] as $language ) {

                $languages[] = array(
                    'value' => ( ! empty ( $language['DisplayValue'] ) ) ? $language['DisplayValue'] : null,
                    'type' => ( ! empty ( $language['Type'] ) ) ? $language['Type'] : null
                );
            }

            $contentInfo['languages'] = $languages;
        }

        return $contentInfo;
    }

    /**
     * Get content rating
     *
     * @return array
     */
    public function getContentRating() {

        return array(
            'audienceRating' => ( ! empty ( $this->dataItemInfo['ContentRating']['AudienceRating']['DisplayValue'] ) ) ? $this->dataItemInfo['ContentRating']['AudienceRating']['DisplayValue'] : null,
        );
    }

    /**
     * Get external ids
     *
     * @return array
     */
    public function getExternalIds() {

        return array(
            'ean' => ( ! empty ( $this->dataItemInfo['ExternalIds']['EANs']['DisplayValues'][0] ) ) ? $this->dataItemInfo['ExternalIds']['EANs']['DisplayValues'][0] : null,
            'isbn' => ( ! empty ( $this->dataItemInfo['ExternalIds']['ISBNs']['DisplayValues'][0] ) ) ? $this->dataItemInfo['ExternalIds']['ISBNs']['DisplayValues'][0] : null,
            'upc' => ( ! empty ( $this->dataItemInfo['ExternalIds']['UPCs']['DisplayValues'][0] ) ) ? $this->dataItemInfo['ExternalIds']['UPCs']['DisplayValues'][0] : null
        );
    }

    /**
     * Get features
     *
     * @return mixed|null
     */
    public function getFeatures() {
        return ( ! empty ( $this->dataItemInfo['Features']['DisplayValues'] ) ) ? $this->dataItemInfo['Features']['DisplayValues'] : null;
    }

    /**
     * Get images
     *
     * @return array
     */
    public function getImages() {

        $images = array(
            'primary' => null,
            'variants' => null
        );

        if ( ! empty ( $this->data['Images'] ) ) {

            if ( ! empty ( $this->data['Images']['Primary'] ) ) {
                $images['primary'] = $this->setupImageData( $this->data['Images']['Primary'] );
            }

            if ( ! empty ( $this->data['Images']['Variants'] ) && is_array( $this->data['Images']['Variants'] ) ) {

                $images['variants'] = array();

                foreach ( $this->data['Images']['Variants'] as $variant ) {
                    $images['variants'][] = $this->setupImageData( $variant );
                }
            }
        }

        return $images;
    }

    /**
     * Get image
     *
     * @param $type
     * @param $size
     * @param string $value
     * @return mixed
     */
    public function getImage( $type, $size, $value = '' ) {

        $images = $this->getImages();

        if ( ! is_array( $images ) || ! isset( $images[$type] ) || ! isset( $images[$type][$size] ) )
            return null;

        $image = $images[$type][$size];

        return ( ! empty( $value ) && isset( $image[$value] ) ) ? $image[$value]: $image;
    }

    /**
     * Setup image data
     *
     * @param array $image
     * @return array|null
     */
    private function setupImageData( $image ) {

        $imageData = array();
        $imageSizes = array( 'Small', 'Medium', 'Large' );

        foreach ( $imageSizes as $imageSize ) {

            if ( ! empty ( $image[$imageSize] ) ) {

                $imageData[ strtolower( $imageSize ) ] = array(
                    'url' => ( ! empty ( $image[$imageSize]['URL'] ) ) ? $image[$imageSize]['URL'] : null,
                    'height' => ( ! empty ( $image[$imageSize]['Height'] ) ) ? $image[$imageSize]['Height'] : null,
                    'width' => ( ! empty ( $image[$imageSize]['Width'] ) ) ? $image[$imageSize]['Width'] : null,
                );
            }
        }

        return ( ! empty( $imageData ) ) ? $imageData : null;
    }

    /**
     * Get offers
     *
     * @return array
     */
    public function getOffers() {

        $offers = array(
            'listings' => null,
            'summaries' => null
        );

        //echo '<pre>'; print_r( $this->dataItemOffers ); echo '</pre>';

        // Listings.
        if ( ! empty ( $this->dataItemOffers['Listings'] ) && is_array( $this->dataItemOffers['Listings'] ) && sizeof( $this->dataItemOffers['Listings'] ) > 0 ) {

            $listings = array();

            foreach ( $this->dataItemOffers['Listings'] as $offersListing ) {

                $listing = array(
                    'availability' => array(
                        'maxOrderQuantity' => ( isset ( $offersListing['Availability']['MaxOrderQuantity'] ) ) ? $offersListing['Availability']['MaxOrderQuantity'] : null,
                        'message' => ( isset ( $offersListing['Availability']['Message'] ) ) ? $offersListing['Availability']['Message'] : null,
                        'minOrderQuantity' => ( isset ( $offersListing['Availability']['MinOrderQuantity'] ) ) ? $offersListing['Availability']['MinOrderQuantity'] : null,
                        'type' => ( isset ( $offersListing['Availability']['Type'] ) ) ? $offersListing['Availability']['Type'] : null,
                    ),
                    'condition' => ( isset ( $offersListing['Condition']['Value'] ) ) ? strtolower( $offersListing['Condition']['Value'] ) : null,
                    'deliveryInfo' => array(
                        'isAmazonFulfilled' => ( isset ( $offersListing['DeliveryInfo']['IsAmazonFulfilled'] ) && '1' == $offersListing['DeliveryInfo']['IsAmazonFulfilled'] ) ? 1 : 0,
                        'isFreeShippingEligible' => ( isset ( $offersListing['DeliveryInfo']['IsFreeShippingEligible'] ) && '1' == $offersListing['DeliveryInfo']['IsFreeShippingEligible'] ) ? 1 : 0,
                        'isPrimeEligible' => ( isset ( $offersListing['DeliveryInfo']['IsPrimeEligible'] ) && '1' == $offersListing['DeliveryInfo']['IsPrimeEligible'] ) ? 1 : 0
                    ),
                    'isBuyBoxWinner' => ( isset ( $offersListing['IsBuyBoxWinner'] ) && '1' == $offersListing['IsBuyBoxWinner'] ) ? 1 : 0,
                    'merchantInfo' => array(
                        'id' => ( ! empty ( $offersListing['MerchantInfo']['Id'] ) ) ? $offersListing['MerchantInfo']['Id'] : null,
                        'name' => ( ! empty ( $offersListing['MerchantInfo']['Name'] ) ) ? $offersListing['MerchantInfo']['Name'] : null,
                    ),
                    'price' => null,
                    'programEligibility' => array(
                        'isAmazonFulfilled' => ( isset ( $offersListing['ProgramEligibility']['IsPrimeExclusive'] ) && '1' == $offersListing['ProgramEligibility']['IsPrimeExclusive'] ) ? 1 : 0,
                        'isFreeShippingEligible' => ( isset ( $offersListing['ProgramEligibility']['IsPrimePantry'] ) && '1' == $offersListing['ProgramEligibility']['IsPrimePantry'] ) ? 1 : 0
                    ),
                    'violatesMAP' => null // TODO
                );

                // Price & savings.
                if ( ! empty( $offersListing['Price'] ) ) {

                    // Basic price data.
                    $price = $this->setupPriceData( $offersListing['Price'] );

                    // Savings.
                    $price['savings'] = ( ! empty ( $offersListing['Price']['Savings'] ) ) ? $this->setupPriceData( $offersListing['Price']['Savings'] ) : null;
                    $price['savingsBasis'] = ( ! empty ( $offersListing['SavingBasis'] ) ) ? $this->setupPriceData( $offersListing['SavingBasis'] ) : null;

                    $listing['price'] = $price;
                }

                // Finish.
                $listings[] = $listing;
            }

            $offers['listings'] = $listings;
        }

        // Summaries.
        if ( ! empty ( $this->dataItemOffers['Summaries'] ) && is_array( $this->dataItemOffers['Summaries'] ) && sizeof( $this->dataItemOffers['Summaries'] ) > 0 ) {

            $summaries = array();

            foreach ( $this->dataItemOffers['Summaries'] as $offersSummary ) {

                $summaryCondition = ( ! empty ( $offersSummary['Condition']['Value'] ) ) ? strtolower( $offersSummary['Condition']['Value'] ) : null;

                if ( empty ( $summaryCondition ) || isset ( $summaries[$summaryCondition] ) )
                    continue;

                $summaries[$summaryCondition] = array(
                    'highestPrice' => ( ! empty ( $offersSummary['HighestPrice'] ) ) ? $this->setupPriceData( $offersSummary['HighestPrice'] ) : null,
                    'lowestPrice' => ( ! empty ( $offersSummary['LowestPrice'] ) ) ? $this->setupPriceData( $offersSummary['LowestPrice'] ) : null,
                    'offerCount' => ( ! empty ( $offersSummary['OfferCount'] ) ) ? $offersSummary['OfferCount'] : null
                );
            }

            $offers['summaries'] = $summaries;
        }

        return $offers;
    }

    /**
     * Get binding
     *
     * @return mixed|null
     */
    public function getAvailability() {
        return ( ! empty ( $offers = $this->getOffers() ) && isset ( $offers['listings'][0]['availability'] ) ) ? $offers['listings'][0]['availability'] : null;
    }

    /**
     * Check whether item is in stock or not.
     *
     * @return bool
     */
    public function isInStock() {
        return ( ! empty ( $availability = $this->getAvailability() ) && isset ( $availability['type'] ) && 'Now' === $availability['type'] ) ? true : false;
    }

    /**
     * Get binding
     *
     * @return mixed|null
     */
    public function getDeliveryInfo() {
        return ( ! empty ( $offers = $this->getOffers() ) && isset ( $offers['listings'][0]['deliveryInfo'] ) ) ? $offers['listings'][0]['deliveryInfo'] : null;
    }

    /**
     * Check whether item is prime or not.
     *
     * @return bool
     */
    public function isPrime() {
        return ( ! empty ( $deliveryInfo = $this->getDeliveryInfo() ) && isset ( $deliveryInfo['isPrimeEligible'] ) && '1' == $deliveryInfo['isPrimeEligible'] ) ? true : false;
    }

    /**
     * Check whether item is prime or not.
     *
     * @return bool
     */
    public function isAmazonFulfilled() {
        return ( ! empty ( $deliveryInfo = $this->getDeliveryInfo() ) && isset ( $deliveryInfo['isAmazonFulfilled'] ) && '1' == $deliveryInfo['isAmazonFulfilled'] ) ? true : false;
    }

    /**
     * Get currency
     *
     * @return string|null
     */
    public function getCurrency() {
        return $this->getPrice( 'currency' );
    }

    /**
     * Get price
     *
     * @param string $value
     * @return mixed|null
     */
    public function getPrice( $value = '' ) {

        $price = ( ! empty ( $offers = $this->getOffers() ) && isset ( $offers['listings'][0]['price'] ) ) ? $offers['listings'][0]['price'] : null;

        if ( empty ( $price ) )
            return null;

        return ( ! empty ( $value ) && isset ( $price[$value] ) ) ? $price[$value] : null;
    }

    /**
     * Get price savings
     *
     * @param string $value
     * @return mixed|null
     */
    public function getPriceSavings( $value = '' ) {

        $savings = $this->getPrice( 'savings' );

        if ( empty ( $savings ) )
            return null;

        return ( ! empty ( $value ) && isset ( $savings[$value] ) ) ? $savings[$value] : null;
    }

    /**
     * Get price savings basis
     *
     * @param string $value
     * @return mixed|null
     */
    public function getPriceSavingsBasis( $value = '' ) {

        $savingsBasis = $this->getPrice( 'savingsBasis' );

        if ( empty ( $savingsBasis ) )
            return null;

        return ( ! empty ( $value ) && isset ( $savingsBasis[$value] ) ) ? $savingsBasis[$value] : null;
    }

    /**
     * Setup price data
     *
     * @param $price
     * @return array
     */
    private function setupPriceData( $price ) {

        $data = array(
            'amount' => ( ! empty( $price['Amount'] ) ) ? $price['Amount'] : null,
            'currency' => ( ! empty( $price['Currency'] ) ) ? $price['Currency'] : null,
            'display' => ( ! empty( $price['DisplayAmount'] ) ) ? $price['DisplayAmount'] : null,
        );

        if ( ! empty ( $price['Percentage'] ) )
            $data['percentage'] = $price['Percentage'];

        return $data;
    }

    /**
     * Get Item data (as array)
     *
     * @return array
     */
    public function getData() {

        return array(
            'asin' => $this->getASIN(),
            'parentASIN' => $this->getParentASIN(),
            'url' => $this->getURL(),
            'title' => $this->getTitle(),
            'byLineInfo' => $this->getByLineInfo(),
            'classifications' => $this->getClassifications(),
            'contentInfo' => $this->getContentInfo(),
            'contentRating' => $this->getContentRating(),
            'externalIds' => $this->getExternalIds(),
            'features' => $this->getFeatures(),
            'images' => $this->getImages(),
            'offers' => $this->getOffers(),
            'websiteSalesRank' => $this->getWebsiteSalesRank()
        );
    }
}