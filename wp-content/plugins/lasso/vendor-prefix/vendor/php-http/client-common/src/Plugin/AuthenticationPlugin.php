<?php

declare (strict_types=1);
namespace LassoVendor\Http\Client\Common\Plugin;

use LassoVendor\Http\Client\Common\Plugin;
use LassoVendor\Http\Message\Authentication;
use LassoVendor\Http\Promise\Promise;
use LassoVendor\Psr\Http\Message\RequestInterface;
/**
 * Send an authenticated request.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
final class AuthenticationPlugin implements Plugin
{
    /**
     * @var Authentication An authentication system
     */
    private $authentication;
    public function __construct(Authentication $authentication)
    {
        $this->authentication = $authentication;
    }
    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first) : Promise
    {
        $request = $this->authentication->authenticate($request);
        return $next($request);
    }
}
