<?php

namespace LassoVendor\Kevinrob\GuzzleCache\Strategy;

use LassoVendor\Kevinrob\GuzzleCache\CacheEntry;
use LassoVendor\Psr\Http\Message\RequestInterface;
use LassoVendor\Psr\Http\Message\ResponseInterface;
class NullCacheStrategy implements CacheStrategyInterface
{
    /**
     * @inheritDoc
     */
    public function fetch(RequestInterface $request)
    {
        return null;
    }
    /**
     * @inheritDoc
     */
    public function cache(RequestInterface $request, ResponseInterface $response)
    {
        return \true;
    }
    /**
     * @inheritDoc
     */
    public function update(RequestInterface $request, ResponseInterface $response)
    {
        return \true;
    }
    /**
     * {@inheritdoc}
     */
    public function delete(RequestInterface $request)
    {
        return \true;
    }
}
