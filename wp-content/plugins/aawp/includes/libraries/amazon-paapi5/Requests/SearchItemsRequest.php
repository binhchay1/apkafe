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
 * Class SearchItemsRequest
 *
 * @category Class
 * @package Flowdee\AmazonPAAPI5
 * @author flowdee
 */
class SearchItemsRequest extends BaseRequest {

    /**
     * Request Path
     *
     * @var string
     */
    public $Path = '/paapi5/searchitems';

    /**
     * Request Path
     *
     * @var string
     */
    public $Target = 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems';

    /**
     * Search Index
     *
     * @var string
     */
    public $SearchIndex = 'All';

    /**
     * Keywords
     *
     * @var string
     */
    public $Keywords;

    /**
     * Item Count
     *
     * @var int
     */
    public $ItemCount = 10;

    /**
     * Item Page
     *
     * @var int
     */
    public $ItemPage = 1;

    /**
     * Browse Node ID
     *
     * @var string
     */
    public $BrowseNodeId;

    /**
     * Sort by
     *
     * @var string
     */
    public $SortBy;

    /**
     * SearchItemsRequest constructor.
     */
    public function __construct() {

        // Defaults.
        $this->setResources( SearchItemsResource::getValues() );
    }

    /**
     * Set search index
     *
     * @param $searchIndex
     */
    public function setSearchIndex( $searchIndex ) {
        $this->SearchIndex = $searchIndex;
    }

    /**
     * Set keywords
     *
     * @param $keywords
     */
    public function setKeywords( $keywords ) {
        $this->Keywords = $keywords;
    }

    /**
     * Set item count
     *
     * @param $itemCount
     */
    public function setItemCount( $itemCount ) {

        if ( empty ( $itemCount ) || ! is_numeric( $itemCount ) )
            return;

        $itemCount = absint( $itemCount );

        // Min: 1.
        if ( $itemCount < 1 )
            $itemCount = 1;

        // Max: 10.
        if ( $itemCount > 10 )
            $itemCount = 10;

        $this->ItemCount = $itemCount;
    }

    /**
     * Set item page
     *
     * @param $itemPage
     */
    public function setItemPage( $itemPage ) {

        if ( empty ( $itemPage ) || ! is_numeric( $itemPage ) )
            return;

        $itemPage = absint( $itemPage );

        // Min: 1.
        if ( $itemPage < 1 )
            $itemPage = 1;

        // Max: 10.
        if ( $itemPage > 10 )
            $itemPage = 10;

        $this->ItemPage = $itemPage;
    }

    /**
     * Get "sort by" pairings
     *
     * @return array
     */
    private function getSortByPairings() {

        return array(
            'reviews' => 'AvgCustomerReviews',
            'featured' => 'Featured',
            'release' => 'NewestArrivals',
            'priceDesc' => 'Price:HighToLow',
            'priceAsc' => 'Price:LowToHigh',
            'relevance' => 'Relevance'
        );
    }

    /**
     * Set sort by
     *
     * @param $sortBy
     */
    public function setSortBy( $sortBy ) {

        if ( empty ( $sortBy ) )
            return;

        $pairings = $this->getSortByPairings();

        if ( isset ( $pairings[$sortBy] ) )
            $this->SortBy = $pairings[$sortBy];
    }

    /**
     * Set browse node id
     *
     * @param $browseNodeId
     */
    public function setBrowseNodeId( $browseNodeId ) {

        if ( empty ( $browseNodeId ) || ! is_numeric( $browseNodeId ) )
            return;

        $this->BrowseNodeId = (string) $browseNodeId;
    }
}