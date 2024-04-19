<?php

declare (strict_types=1);
namespace LassoVendor\Http\Client\Common;

use LassoVendor\Psr\Http\Message\RequestInterface;
use LassoVendor\Psr\Http\Message\ResponseInterface;
/**
 * A client that helps you migrate from php-http/httplug 1.x to 2.x. This
 * will also help you to support PHP5 at the same time you support 2.x.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
trait VersionBridgeClient
{
    protected abstract function doSendRequest(RequestInterface $request);
    public function sendRequest(RequestInterface $request) : ResponseInterface
    {
        return $this->doSendRequest($request);
    }
}
