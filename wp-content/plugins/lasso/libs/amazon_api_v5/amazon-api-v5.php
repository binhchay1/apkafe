<?php

// product advertising API v5.0
// https://webservices.amazon.com/paapi5/documentation/without-sdk.html

class LassoAwsV5 {
    private $accessKeyID = null;
    private $secretAccessKey = null;
    private $path = '/paapi5/searchitems';
    private $regionName = 'us-east-1';
    private $serviceName = 'ProductAdvertisingAPI';
    private $httpMethodName = 'POST';
    private $queryParametes = [];
    private $awsHeaders = [];
    private $payload = "";

    private $HMACAlgorithm = "AWS4-HMAC-SHA256";
    private $aws4Request = "aws4_request";
    private $strSignedHeader = null;
    private $xAmzDate = null;
    private $currentDate = null;

    private $host = 'webservices.amazon.com';

    public function __construct($accessKeyID, $secretAccessKey) {
        $this->accessKeyID = $accessKeyID;
        $this->secretAccessKey = $secretAccessKey;
        $this->xAmzDate = $this->getTimeStamp ();
        $this->currentDate = $this->getDate ();
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function setServiceName($serviceName) {
        $this->serviceName = $serviceName;
    }

    public function setRegionName($regionName) {
        $this->regionName = $regionName;
    }

    public function setPayload($payload) {
        $this->payload = $payload;
    }

    public function setRequestMethod($method) {
        $this->httpMethodName = $method;
    }

    public function setHost($host) {
        $this->host = $host;
    }

    public function getHost() {
        return $this->host;
    }

    public function addHeader($headerName, $headerValue) {
        $this->awsHeaders [$headerName] = $headerValue;
    }

    private function prepareCanonicalRequest() {
        $canonicalURL = "";
        $canonicalURL .= $this->httpMethodName . "\n";
        $canonicalURL .= $this->path . "\n" . "\n";
        $signedHeaders = '';
        foreach ( $this->awsHeaders as $key => $value ) {
            $signedHeaders .= $key . ";";
            $canonicalURL .= $key . ":" . $value . "\n";
        }
        $canonicalURL .= "\n";
        $this->strSignedHeader = substr ( $signedHeaders, 0, - 1 );
        $canonicalURL .= $this->strSignedHeader . "\n";
        $canonicalURL .= $this->generateHex ( $this->payload );
        return $canonicalURL;
    }

    private function prepareStringToSign($canonicalURL) {
        $stringToSign = '';
        $stringToSign .= $this->HMACAlgorithm . "\n";
        $stringToSign .= $this->xAmzDate . "\n";
        $stringToSign .= $this->currentDate . "/" . $this->regionName . "/" . $this->serviceName . "/" . $this->aws4Request . "\n";
        $stringToSign .= $this->generateHex ( $canonicalURL );
        return $stringToSign;
    }

    private function calculateSignature($stringToSign) {
        $signatureKey = $this->getSignatureKey ( $this->secretAccessKey, $this->currentDate, $this->regionName, $this->serviceName );
        $signature = hash_hmac ( "sha256", $stringToSign, $signatureKey, true );
        $strHexSignature = strtolower ( bin2hex ( $signature ) );
        return $strHexSignature;
    }

    public function getHeaders($headerString = false) {
        $this->awsHeaders ['x-amz-date'] = $this->xAmzDate;
        $this->awsHeaders ['host'] = $this->getHost();
        $this->awsHeaders ['content-type'] = 'application/json; charset=utf-8';
        $this->awsHeaders ['content-encoding'] = 'amz-1.0';

        ksort ( $this->awsHeaders );

        $canonicalURL = $this->prepareCanonicalRequest ();
        $stringToSign = $this->prepareStringToSign ( $canonicalURL );
        $signature = $this->calculateSignature ( $stringToSign );
        if ($signature) {
            $this->awsHeaders ['Authorization'] = $this->buildAuthorizationString ( $signature );

            // headers string for curl
            if($headerString) {
                $headerString = [];
                $headers = $this->awsHeaders;
                foreach ( $headers as $key => $value ) {
                    $headerString[] = $key . ': ' . $value;
                }
                return $headerString;
            }

            return $this->awsHeaders;
        }
    }

    private function buildAuthorizationString($strSignature) {
        return $this->HMACAlgorithm . " " . "Credential=" . $this->accessKeyID . "/" . $this->getDate () . "/" . $this->regionName . "/" . $this->serviceName . "/" . $this->aws4Request . "," . "SignedHeaders=" . $this->strSignedHeader . "," . "Signature=" . $strSignature;
    }

    private function generateHex($data) {
        return strtolower ( bin2hex ( hash ( "sha256", $data, true ) ) );
    }

    private function getSignatureKey($key, $date, $regionName, $serviceName) {
        $kSecret = "AWS4" . $key;
        $kDate = hash_hmac ( "sha256", $date, $kSecret, true );
        $kRegion = hash_hmac ( "sha256", $regionName, $kDate, true );
        $kService = hash_hmac ( "sha256", $serviceName, $kRegion, true );
        $kSigning = hash_hmac ( "sha256", $this->aws4Request, $kService, true );

        return $kSigning;
    }

    private function getTimeStamp() {
        return gmdate ( "Ymd\THis\Z" );
    }

    private function getDate() {
        return gmdate ( "Ymd" );
    }
}
