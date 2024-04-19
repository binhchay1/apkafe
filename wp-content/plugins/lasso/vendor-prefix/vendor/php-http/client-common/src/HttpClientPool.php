<?php

declare (strict_types=1);
namespace LassoVendor\Http\Client\Common;

use LassoVendor\Http\Client\Common\HttpClientPool\HttpClientPoolItem;
use LassoVendor\Http\Client\HttpAsyncClient;
use LassoVendor\Http\Client\HttpClient;
use LassoVendor\Psr\Http\Client\ClientInterface;
/**
 * A http client pool allows to send requests on a pool of different http client using a specific strategy (least used,
 * round robin, ...).
 */
interface HttpClientPool extends HttpAsyncClient, HttpClient
{
    /**
     * Add a client to the pool.
     *
     * @param ClientInterface|HttpAsyncClient|HttpClientPoolItem $client
     */
    public function addHttpClient($client) : void;
}
