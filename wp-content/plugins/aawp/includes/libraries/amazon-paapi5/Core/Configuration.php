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
 * Class Configuration
 *
 * @category Class
 * @package Flowdee\AmazonPAAPI5
 * @author flowdee
 */
class Configuration {

    /**
     * Access key
     *
     * @var string
     */
    protected $accessKey;

    /**
     * Secret key
     *
     * @var string
     */
    protected $secretKey;

    /**
     * Partner tag
     *
     * @var string
     */
    protected $partnerTag;

    /**
     * The host
     *
     * @var string
     */
    protected $host;

    /**
     * The region
     *
     * @var string
     */
    protected $region;

    /**
     * The store
     *
     * @var string
     */
    protected $store;

    /**
     * Configuration constructor.
     *
     * @param $accessKey
     * @param $secretKey
     * @param $partnerTag
     * @param $store
     */
    public function __construct( $accessKey, $secretKey, $partnerTag, $store ) {

        // Access key.
        $this->accessKey = $accessKey;

        // Secret key.
        $this->secretKey = $secretKey;

        // Partner tag.
        $this->partnerTag = $partnerTag;

        /*
         * Source: https://webservices.amazon.com/paapi5/documentation/common-request-parameters.html#host-and-region
         */
        switch( $store ) {
            // Australia
            case ( 'com.au' ):
                $host = 'webservices.amazon.com.au';
                $region = 'us-west-2';
                break;
            // Brazil
            case ( 'com.br' ):
                $host = 'webservices.amazon.com.br';
                $region = 'us-east-1';
                break;
            // Canada
            case ( 'ca' ):
                $host = 'webservices.amazon.ca';
                $region = 'us-east-1';
                break;
            // France
            case ( 'fr' ):
                $host = 'webservices.amazon.fr';
                $region = 'eu-west-1';
                break;
            // Germany
            case ( 'de' ):
                $host = 'webservices.amazon.de';
                $region = 'eu-west-1';
                break;
            // India
            case ( 'in' ):
                $host = 'webservices.amazon.in';
                $region = 'eu-west-1';
                break;
            // Italy
            case ( 'it' ):
                $host = 'webservices.amazon.it';
                $region = 'eu-west-1';
                break;
            // Japan
            case ( 'co.jp' ):
                $host = 'webservices.amazon.co.jp';
                $region = 'us-west-2';
                break;
            // Mexico
            case ( 'com.mx' ):
                $host = 'webservices.amazon.com.mx';
                $region = 'us-east-1';
                break;
            // Netherlands
            case ( 'nl' ):
                $host = 'webservices.amazon.nl';
                $region = 'eu-west-1';
                break;
            // Poland
            case ( 'pl' ):
                $host = 'webservices.amazon.pl';
                $region = 'eu-west-1';
                break;
            // Singapore
            case ( 'sg' ):
                $host = 'webservices.amazon.sg';
                $region = 'us-west-2';
                break;
            // Saudi Arabia
            case ( 'sa' ):
                $host = 'webservices.amazon.sa';
                $region = 'eu-west-1';
                break;
            // Spain
            case ( 'es' ):
                $host = 'webservices.amazon.es';
                $region = 'eu-west-1';
                break;
            // Sweden
            case ( 'se' ):
                $host = 'webservices.amazon.se';
                $region = 'eu-west-1';
                break;
            // Turkey
            case ( 'com.tr' ):
                $host = 'webservices.amazon.com.tr';
                $region = 'eu-west-1';
                break;
            // United Arab Emirates
            case ( 'ae' ):
                $host = 'webservices.amazon.ae';
                $region = 'eu-west-1';
                break;
            // United Kingdom
            case ( 'co.uk' ):
                $host = 'webservices.amazon.co.uk';
                $region = 'eu-west-1';
                break;
            // United States (Default)
            default:
                $host = 'webservices.amazon.com';
                $region = 'us-east-1';
                break;
        }

        // Set host.
        $this->host = $host;

        // Set region.
        $this->region = $region;
    }

    /**
     * Get access key.
     *
     * @return string
     */
    public function getAccessKey() {
        return $this->accessKey;
    }

    /**
     * Get secret key.
     *
     * @return string
     */
    public function getSecretKey() {
        return $this->secretKey;
    }

    /**
     * Get partner tag.
     *
     * @return string
     */
    public function getPartnerTag() {
        return $this->partnerTag;
    }

    /**
     * Get host.
     *
     * @return string
     */
    public function getHost() {
        return $this->host;
    }

    /**
     * Get region.
     *
     * @return string
     */
    public function getRegion() {
        return $this->region;
    }
}