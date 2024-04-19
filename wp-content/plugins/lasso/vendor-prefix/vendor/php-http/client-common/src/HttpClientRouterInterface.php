<?php

declare (strict_types=1);
namespace LassoVendor\Http\Client\Common;

use LassoVendor\Http\Client\HttpAsyncClient;
use LassoVendor\Http\Client\HttpClient;
use LassoVendor\Http\Message\RequestMatcher;
use LassoVendor\Psr\Http\Client\ClientInterface;
/**
 * Route a request to a specific client in the stack based using a RequestMatcher.
 *
 * This is not a HttpClientPool client because it uses a matcher to select the client.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
interface HttpClientRouterInterface extends HttpClient, HttpAsyncClient
{
    /**
     * Add a client to the router.
     *
     * @param ClientInterface|HttpAsyncClient $client
     */
    public function addClient($client, RequestMatcher $requestMatcher) : void;
}
