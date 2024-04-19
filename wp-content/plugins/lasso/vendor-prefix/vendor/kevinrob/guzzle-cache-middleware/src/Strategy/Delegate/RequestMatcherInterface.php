<?php

namespace LassoVendor\Kevinrob\GuzzleCache\Strategy\Delegate;

use LassoVendor\Psr\Http\Message\RequestInterface;
interface RequestMatcherInterface
{
    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function matches(RequestInterface $request);
}
