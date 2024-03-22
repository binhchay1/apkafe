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
 * Class GetVariationsRequest
 *
 * @category Class
 * @package Flowdee\AmazonPAAPI5
 * @author flowdee
 */
class GetVariationsRequest extends BaseRequest {

    /**
     * Request Path
     *
     * @var string
     */
    public $Path = '/paapi5/getvariations';

    /**
     * Request Path
     *
     * @var string
     */
    public $Target = 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetVariations';

    /**
     * ASIN
     *
     * @var string
     */
    public $ASIN;

    /**
     * Variation Count
     *
     * @var int
     */
    public $VariationCount = 10;

    /**
     * Variation Page
     *
     * @var int
     */
    public $VariationPage = 1;

    /**
     * SearchItemsRequest constructor.
     */
    public function __construct() {

        // Defaults.
        $this->setResources( SearchItemsResource::getValues() );
    }

    /**
     * Set ASIN
     *
     * @param $asin
     */
    public function setASIN( $asin ) {

        if ( empty ( $asin ) )
            return;

        $this->ASIN = $asin;
    }

    /**
     * Set variation count
     *
     * @param $variationCount
     */
    public function setVariationCount( $variationCount ) {

        if ( empty ( $variationCount ) || ! is_numeric( $variationCount ) )
            return;

        $variationCount = absint( $variationCount );

        // Min: 1.
        if ( $variationCount < 1 )
            $variationCount = 1;

        // Max: 10.
        if ( $variationCount > 10 )
            $variationCount = 10;

        $this->VariationCount = $variationCount;
    }

    /**
     * Set variation page
     *
     * @param $variationPage
     */
    public function setVariationPage( $variationPage ) {

        if ( empty ( $variationPage ) || ! is_numeric( $variationPage ) )
            return;

        $variationPage = absint( $variationPage );

        // Min: 1.
        if ( $variationPage < 1 )
            $variationPage = 1;

        // Max: 10.
        if ( $variationPage > 10 )
            $variationPage = 10;

        $this->VariationPage = $variationPage;
    }
}