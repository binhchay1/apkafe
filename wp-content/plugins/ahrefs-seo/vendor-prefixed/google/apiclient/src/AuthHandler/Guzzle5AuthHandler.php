<?php

namespace ahrefs\AhrefsSeo_Vendor\Google\AuthHandler;

use ahrefs\AhrefsSeo_Vendor\Google\Auth\CredentialsLoader;
use ahrefs\AhrefsSeo_Vendor\Google\Auth\FetchAuthTokenCache;
use ahrefs\AhrefsSeo_Vendor\Google\Auth\HttpHandler\HttpHandlerFactory;
use ahrefs\AhrefsSeo_Vendor\Google\Auth\Subscriber\AuthTokenSubscriber;
use ahrefs\AhrefsSeo_Vendor\Google\Auth\Subscriber\ScopedAccessTokenSubscriber;
use ahrefs\AhrefsSeo_Vendor\Google\Auth\Subscriber\SimpleSubscriber;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Client;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\ClientInterface;
use ahrefs\AhrefsSeo_Vendor\Psr\Cache\CacheItemPoolInterface;
/**
 * This supports Guzzle 5
 */
class Guzzle5AuthHandler
{
    protected $cache;
    protected $cacheConfig;
    public function __construct(\ahrefs\AhrefsSeo_Vendor\Psr\Cache\CacheItemPoolInterface $cache = null, array $cacheConfig = [])
    {
        $this->cache = $cache;
        $this->cacheConfig = $cacheConfig;
    }
    public function attachCredentials(\ahrefs\AhrefsSeo_Vendor\GuzzleHttp\ClientInterface $http, \ahrefs\AhrefsSeo_Vendor\Google\Auth\CredentialsLoader $credentials, callable $tokenCallback = null)
    {
        // use the provided cache
        if ($this->cache) {
            $credentials = new \ahrefs\AhrefsSeo_Vendor\Google\Auth\FetchAuthTokenCache($credentials, $this->cacheConfig, $this->cache);
        }
        return $this->attachCredentialsCache($http, $credentials, $tokenCallback);
    }
    public function attachCredentialsCache(\ahrefs\AhrefsSeo_Vendor\GuzzleHttp\ClientInterface $http, \ahrefs\AhrefsSeo_Vendor\Google\Auth\FetchAuthTokenCache $credentials, callable $tokenCallback = null)
    {
        // if we end up needing to make an HTTP request to retrieve credentials, we
        // can use our existing one, but we need to throw exceptions so the error
        // bubbles up.
        $authHttp = $this->createAuthHttp($http);
        $authHttpHandler = \ahrefs\AhrefsSeo_Vendor\Google\Auth\HttpHandler\HttpHandlerFactory::build($authHttp);
        $subscriber = new \ahrefs\AhrefsSeo_Vendor\Google\Auth\Subscriber\AuthTokenSubscriber($credentials, $authHttpHandler, $tokenCallback);
        $http->setDefaultOption('auth', 'google_auth');
        $http->getEmitter()->attach($subscriber);
        return $http;
    }
    public function attachToken(\ahrefs\AhrefsSeo_Vendor\GuzzleHttp\ClientInterface $http, array $token, array $scopes)
    {
        $tokenFunc = function ($scopes) use($token) {
            return $token['access_token'];
        };
        $subscriber = new \ahrefs\AhrefsSeo_Vendor\Google\Auth\Subscriber\ScopedAccessTokenSubscriber($tokenFunc, $scopes, $this->cacheConfig, $this->cache);
        $http->setDefaultOption('auth', 'scoped');
        $http->getEmitter()->attach($subscriber);
        return $http;
    }
    public function attachKey(\ahrefs\AhrefsSeo_Vendor\GuzzleHttp\ClientInterface $http, $key)
    {
        $subscriber = new \ahrefs\AhrefsSeo_Vendor\Google\Auth\Subscriber\SimpleSubscriber(['key' => $key]);
        $http->setDefaultOption('auth', 'simple');
        $http->getEmitter()->attach($subscriber);
        return $http;
    }
    private function createAuthHttp(\ahrefs\AhrefsSeo_Vendor\GuzzleHttp\ClientInterface $http)
    {
        return new \ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Client(['base_url' => $http->getBaseUrl(), 'defaults' => ['exceptions' => \true, 'verify' => $http->getDefaultOption('verify'), 'proxy' => $http->getDefaultOption('proxy')]]);
    }
}
