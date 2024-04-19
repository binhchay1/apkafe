<?php

namespace LassoVendor\Http\Factory\Guzzle;

use LassoVendor\GuzzleHttp\Psr7\ServerRequest;
use LassoVendor\Psr\Http\Message\ServerRequestFactoryInterface;
use LassoVendor\Psr\Http\Message\ServerRequestInterface;
class ServerRequestFactory implements ServerRequestFactoryInterface
{
    public function createServerRequest(string $method, $uri, array $serverParams = []) : ServerRequestInterface
    {
        if (empty($method)) {
            if (!empty($serverParams['REQUEST_METHOD'])) {
                $method = $serverParams['REQUEST_METHOD'];
            } else {
                throw new \InvalidArgumentException('Cannot determine HTTP method');
            }
        }
        return new ServerRequest($method, $uri, [], null, '1.1', $serverParams);
    }
}
