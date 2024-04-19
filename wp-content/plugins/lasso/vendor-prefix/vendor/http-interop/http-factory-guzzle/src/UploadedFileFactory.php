<?php

namespace LassoVendor\Http\Factory\Guzzle;

use LassoVendor\GuzzleHttp\Psr7\UploadedFile;
use LassoVendor\Psr\Http\Message\UploadedFileFactoryInterface;
use LassoVendor\Psr\Http\Message\StreamInterface;
use LassoVendor\Psr\Http\Message\UploadedFileInterface;
class UploadedFileFactory implements UploadedFileFactoryInterface
{
    public function createUploadedFile(StreamInterface $stream, int $size = null, int $error = \UPLOAD_ERR_OK, string $clientFilename = null, string $clientMediaType = null) : UploadedFileInterface
    {
        if ($size === null) {
            $size = $stream->getSize();
        }
        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }
}
