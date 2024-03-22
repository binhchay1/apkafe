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
 * Class GetItemsRequest
 *
 * @category Class
 * @package Flowdee\AmazonPAAPI5
 * @author flowdee
 */
class GetItemsRequest extends BaseRequest {

    /**
     * Request Path
     *
     * @var string
     */
    public $Path = '/paapi5/getitems';

    /**
     * Request Path
     *
     * @var string
     */
    public $Target = 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems';

    /**
     * Item Type
     *
     * @var string
     */
    public $ItemIdType = 'ASIN';

    /**
     * Item IDs
     *
     * @var string
     */
    public $ItemIds;

    /**
     * SearchItemsRequest constructor.
     */
    public function __construct() {

        // Defaults.
        $this->setResources( GetItemsResource::getValues() );
    }

    /**
     * Set Item IDs
     *
     * @param $itemIds
     */
    public function setItemIds( $itemIds ) {

        if ( empty ( $itemIds ) )
            return;

        // Maybe convert string of ASINs into an array.
        if ( is_string( $itemIds ) )
            $itemIds = explode(',', $itemIds );

        $this->ItemIds = $itemIds;
    }
}