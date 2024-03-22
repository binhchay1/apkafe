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
 * Class BaseRequest
 *
 * @category Class
 * @package Flowdee\AmazonPAAPI5
 * @author flowdee
 */
class BaseRequest {

    /**
     * Request Path
     *
     * @var string
     */
    public $Path;

    /**
     * Request Path
     *
     * @var string
     */
    public $Target;

    /**
     * Partner type
     *
     * @var string
     */
    public $PartnerType = 'Associates';

    /**
     * Partner tag
     *
     * @var string
     */
    public $PartnerTag;

    /**
     * Resources
     *
     * @var string
     */
    public $Resources = array();

    /**
     * Condition
     *
     * @var string
     */
    public $Condition = 'Any';

    /**
     * Offer Count
     *
     * @var int
     */
    public $OfferCount = 1;

    /**
     * Merchant
     *
     * @var string
     */
    public $Merchant = 'All';

    /**
     * Get Request Path
     *
     * @return string
     */
    public function getPath() {
        return $this->Path;
    }

    /**
     * Get Request Target
     *
     * @return string
     */
    public function getTarget() {
        return $this->Target;
    }

    /**
     * Set partner tag
     *
     * @param $partnerTag
     */
    public function setPartnerTag( $partnerTag ) {
        $this->PartnerTag = $partnerTag;
    }

    /**
     * Set resources
     *
     * @param array $resources
     */
    public function setResources( $resources ) {
        $this->Resources = $resources;
    }

    /**
     * Get "condition" pairings
     *
     * @return array
     */
    private function getConditionPairings() {

        return array(
            'any' => 'Any',
            'new' => 'New',
            'used' => 'Used',
            'collectible' => 'Collectible',
            'refurbished' => 'Refurbished'
        );
    }

    /**
     * Set condition
     *
     * @param $condition
     */
    public function setCondition( $condition ) {

        if ( empty ( $condition ) )
            return;

        $pairings = $this->getConditionPairings();

        if ( isset ( $pairings[$condition] ) )
            $this->Condition = $pairings[$condition];
    }

    /**
     * Set offer count
     *
     * @param int $offerCount
     */
    public function setOfferCount( $offerCount ) {

        if ( ! empty ( $offerCount ) )
            $this->OfferCount = $offerCount;
    }

    /**
     * Set merchant
     *
     * @param int $merchant
     */
    public function setMerchant( $merchant ) {

        if ( ! empty ( $merchant ) )
            $this->Merchant = $merchant;
    }
}