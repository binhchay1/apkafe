<?php

namespace LassoVendor\Http\Discovery\Strategy;

use LassoVendor\Psr\Http\Message\RequestFactoryInterface;
use LassoVendor\Psr\Http\Message\ResponseFactoryInterface;
use LassoVendor\Psr\Http\Message\ServerRequestFactoryInterface;
use LassoVendor\Psr\Http\Message\StreamFactoryInterface;
use LassoVendor\Psr\Http\Message\UploadedFileFactoryInterface;
use LassoVendor\Psr\Http\Message\UriFactoryInterface;
/**
 * @internal
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * Don't miss updating src/Composer/Plugin.php when adding a new supported class.
 */
final class CommonPsr17ClassesStrategy implements DiscoveryStrategy
{
    /**
     * @var array
     */
    private static $classes = [RequestFactoryInterface::class => ['LassoVendor\\Phalcon\\Http\\Message\\RequestFactory', 'LassoVendor\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'LassoVendor\\GuzzleHttp\\Psr7\\HttpFactory', 'LassoVendor\\Http\\Factory\\Diactoros\\RequestFactory', 'LassoVendor\\Http\\Factory\\Guzzle\\RequestFactory', 'LassoVendor\\Http\\Factory\\Slim\\RequestFactory', 'LassoVendor\\Laminas\\Diactoros\\RequestFactory', 'LassoVendor\\Slim\\Psr7\\Factory\\RequestFactory', 'LassoVendor\\HttpSoft\\Message\\RequestFactory'], ResponseFactoryInterface::class => ['LassoVendor\\Phalcon\\Http\\Message\\ResponseFactory', 'LassoVendor\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'LassoVendor\\GuzzleHttp\\Psr7\\HttpFactory', 'LassoVendor\\Http\\Factory\\Diactoros\\ResponseFactory', 'LassoVendor\\Http\\Factory\\Guzzle\\ResponseFactory', 'LassoVendor\\Http\\Factory\\Slim\\ResponseFactory', 'LassoVendor\\Laminas\\Diactoros\\ResponseFactory', 'LassoVendor\\Slim\\Psr7\\Factory\\ResponseFactory', 'LassoVendor\\HttpSoft\\Message\\ResponseFactory'], ServerRequestFactoryInterface::class => ['LassoVendor\\Phalcon\\Http\\Message\\ServerRequestFactory', 'LassoVendor\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'LassoVendor\\GuzzleHttp\\Psr7\\HttpFactory', 'LassoVendor\\Http\\Factory\\Diactoros\\ServerRequestFactory', 'LassoVendor\\Http\\Factory\\Guzzle\\ServerRequestFactory', 'LassoVendor\\Http\\Factory\\Slim\\ServerRequestFactory', 'LassoVendor\\Laminas\\Diactoros\\ServerRequestFactory', 'LassoVendor\\Slim\\Psr7\\Factory\\ServerRequestFactory', 'LassoVendor\\HttpSoft\\Message\\ServerRequestFactory'], StreamFactoryInterface::class => ['LassoVendor\\Phalcon\\Http\\Message\\StreamFactory', 'LassoVendor\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'LassoVendor\\GuzzleHttp\\Psr7\\HttpFactory', 'LassoVendor\\Http\\Factory\\Diactoros\\StreamFactory', 'LassoVendor\\Http\\Factory\\Guzzle\\StreamFactory', 'LassoVendor\\Http\\Factory\\Slim\\StreamFactory', 'LassoVendor\\Laminas\\Diactoros\\StreamFactory', 'LassoVendor\\Slim\\Psr7\\Factory\\StreamFactory', 'LassoVendor\\HttpSoft\\Message\\StreamFactory'], UploadedFileFactoryInterface::class => ['LassoVendor\\Phalcon\\Http\\Message\\UploadedFileFactory', 'LassoVendor\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'LassoVendor\\GuzzleHttp\\Psr7\\HttpFactory', 'LassoVendor\\Http\\Factory\\Diactoros\\UploadedFileFactory', 'LassoVendor\\Http\\Factory\\Guzzle\\UploadedFileFactory', 'LassoVendor\\Http\\Factory\\Slim\\UploadedFileFactory', 'LassoVendor\\Laminas\\Diactoros\\UploadedFileFactory', 'LassoVendor\\Slim\\Psr7\\Factory\\UploadedFileFactory', 'LassoVendor\\HttpSoft\\Message\\UploadedFileFactory'], UriFactoryInterface::class => ['LassoVendor\\Phalcon\\Http\\Message\\UriFactory', 'LassoVendor\\Nyholm\\Psr7\\Factory\\Psr17Factory', 'LassoVendor\\GuzzleHttp\\Psr7\\HttpFactory', 'LassoVendor\\Http\\Factory\\Diactoros\\UriFactory', 'LassoVendor\\Http\\Factory\\Guzzle\\UriFactory', 'LassoVendor\\Http\\Factory\\Slim\\UriFactory', 'LassoVendor\\Laminas\\Diactoros\\UriFactory', 'LassoVendor\\Slim\\Psr7\\Factory\\UriFactory', 'LassoVendor\\HttpSoft\\Message\\UriFactory']];
    public static function getCandidates($type)
    {
        $candidates = [];
        if (isset(self::$classes[$type])) {
            foreach (self::$classes[$type] as $class) {
                $candidates[] = ['class' => $class, 'condition' => [$class]];
            }
        }
        return $candidates;
    }
}
