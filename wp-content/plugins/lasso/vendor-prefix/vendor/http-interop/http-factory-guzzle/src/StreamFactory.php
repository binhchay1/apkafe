<?php

namespace LassoVendor\Http\Factory\Guzzle;

use LassoVendor\GuzzleHttp\Psr7\Stream;
use LassoVendor\GuzzleHttp\Psr7\Utils;
use LassoVendor\Psr\Http\Message\StreamFactoryInterface;
use LassoVendor\Psr\Http\Message\StreamInterface;
use function function_exists;
use function LassoVendor\GuzzleHttp\Psr7\stream_for;
use function LassoVendor\GuzzleHttp\Psr7\try_fopen;
class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = '') : StreamInterface
    {
        if (function_exists('LassoVendor\\GuzzleHttp\\Psr7\\stream_for')) {
            // fallback for guzzlehttp/psr7<1.7.0
            return stream_for($content);
        }
        return Utils::streamFor($content);
    }
    public function createStreamFromFile(string $file, string $mode = 'r') : StreamInterface
    {
        if (function_exists('LassoVendor\\GuzzleHttp\\Psr7\\try_fopen')) {
            // fallback for guzzlehttp/psr7<1.7.0
            $resource = try_fopen($file, $mode);
        } else {
            $resource = Utils::tryFopen($file, $mode);
        }
        return $this->createStreamFromResource($resource);
    }
    public function createStreamFromResource($resource) : StreamInterface
    {
        return new Stream($resource);
    }
}
