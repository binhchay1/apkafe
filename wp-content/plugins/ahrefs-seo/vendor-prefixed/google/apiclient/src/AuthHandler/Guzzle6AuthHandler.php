<?php

namespace ahrefs\AhrefsSeo_Vendor\Google\AuthHandler;

use ahrefs\AhrefsSeo_Vendor\Google\Auth\CredentialsLoader;
use ahrefs\AhrefsSeo_Vendor\Google\Auth\FetchAuthTokenCache;
use ahrefs\AhrefsSeo_Vendor\Google\Auth\HttpHandler\HttpHandlerFactory;
use ahrefs\AhrefsSeo_Vendor\Google\Auth\Middleware\AuthTokenMiddleware;
use ahrefs\AhrefsSeo_Vendor\Google\Auth\Middleware\ScopedAccessTokenMiddleware;
use ahrefs\AhrefsSeo_Vendor\Google\Auth\Middleware\SimpleMiddleware;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Client;
use ahrefs\AhrefsSeo_Vendor\GuzzleHttp\ClientInterface;
use ahrefs\AhrefsSeo_Vendor\Psr\Cache\CacheItemPoolInterface;
/**
 * This supports Guzzle 6
 */
class Guzzle6AuthHandler
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
        $middleware = new \ahrefs\AhrefsSeo_Vendor\Google\Auth\Middleware\AuthTokenMiddleware($credentials, $authHttpHandler, $tokenCallback);
        $config = $http->getConfig();
        $config['handler']->remove('google_auth');
        $config['handler']->push($middleware, 'google_auth');
        $config['auth'] = 'google_auth';
        $http = new \ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Client($config);
        return $http;
    }
    public function attachToken(\ahrefs\AhrefsSeo_Vendor\GuzzleHttp\ClientInterface $http, array $token, array $scopes)
    {
        $tokenFunc = function ($scopes) use($token) {
            return $token['access_token'];
        };
        $middleware = new \ahrefs\AhrefsSeo_Vendor\Google\Auth\Middleware\ScopedAccessTokenMiddleware($tokenFunc, $scopes, $this->cacheConfig, $this->cache);
        $config = $http->getConfig();
        $config['handler']->remove('google_auth');
        $config['handler']->push($middleware, 'google_auth');
        $config['auth'] = 'scoped';
        $http = new \ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Client($config);
        return $http;
    }
    public function attachKey(\ahrefs\AhrefsSeo_Vendor\GuzzleHttp\ClientInterface $http, $key)
    {
        $middleware = new \ahrefs\AhrefsSeo_Vendor\Google\Auth\Middleware\SimpleMiddleware(['key' => $key]);
        $config = $http->getConfig();
        $config['handler']->remove('google_auth');
        $config['handler']->push($middleware, 'google_auth');
        $config['auth'] = 'simple';
        $http = new \ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Client($config);
        return $http;
    }
    private function createAuthHttp(\ahrefs\AhrefsSeo_Vendor\GuzzleHttp\ClientInterface $http)
    {
        return new \ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Client(['http_errors' => \true] + $http->getConfig());
    }
}
