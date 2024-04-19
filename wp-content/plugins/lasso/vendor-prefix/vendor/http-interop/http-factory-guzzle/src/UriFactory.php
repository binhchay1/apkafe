<?php

namespace LassoVendor\Http\Factory\Guzzle;

use LassoVendor\GuzzleHttp\Psr7\Uri;
use LassoVendor\Psr\Http\Message\UriFactoryInterface;
use LassoVendor\Psr\Http\Message\UriInterface;
class UriFactory implements UriFactoryInterface
{
    public function createUri(string $uri = '') : UriInterface
    {
        return new Uri($uri);
    }
}
