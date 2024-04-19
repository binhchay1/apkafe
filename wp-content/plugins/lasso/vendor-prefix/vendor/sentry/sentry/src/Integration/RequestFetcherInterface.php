<?php

declare (strict_types=1);
namespace LassoVendor\Sentry\Integration;

use LassoVendor\Psr\Http\Message\ServerRequestInterface;
/**
 * Allows customizing the request information that is attached to the logged event.
 * An implementation of this interface can be passed to RequestIntegration.
 */
interface RequestFetcherInterface
{
    /**
     * Returns the PSR-7 request object that will be attached to the logged event.
     */
    public function fetchRequest() : ?ServerRequestInterface;
}
