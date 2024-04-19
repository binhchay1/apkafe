<?php

namespace LassoVendor\Http\Factory\Guzzle;

use LassoVendor\GuzzleHttp\Psr7\Request;
use LassoVendor\Psr\Http\Message\RequestFactoryInterface;
use LassoVendor\Psr\Http\Message\RequestInterface;
class RequestFactory implements RequestFactoryInterface
{
    public function createRequest(string $method, $uri) : RequestInterface
    {
        return new Request($method, $uri);
    }
}
