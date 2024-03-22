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
 * Class TestRequest
 *
 * @category Class
 * @package Flowdee\AmazonPAAPI5
 * @author flowdee
 */
class TestRequest extends BaseRequest {

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
    public $ItemCount = 1;

    /**
     * TestRequest constructor.
     */
    public function __construct() {

        // Defaults.
        $this->setResources( array( 'ItemInfo.Title' ) );
    }

    /**
     * Set keywords
     *
     * @param $keywords
     */
    public function setKeywords( $keywords ) {
        $this->Keywords = $keywords;
    }
}