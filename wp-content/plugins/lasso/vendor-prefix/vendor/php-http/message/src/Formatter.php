<?php

namespace LassoVendor\Http\Message;

use LassoVendor\Psr\Http\Message\RequestInterface;
use LassoVendor\Psr\Http\Message\ResponseInterface;
/**
 * Formats a request and/or a response as a string.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * The formatResponseForRequest method will be added to this interface in the next major version, replacing the formatRequest method.
 * Meanwhile, callers SHOULD check the formatter for the existence of formatResponseForRequest and call that if available.
 *
 * @method string formatResponseForRequest(ResponseInterface $response, RequestInterface $request) Formats a response in context of its request.
 */
interface Formatter
{
    /**
     * Formats a request.
     *
     * @return string
     */
    public function formatRequest(RequestInterface $request);
    /**
     * @deprecated since 1.13, use formatResponseForRequest() instead
     *
     * Formats a response.
     *
     * @return string
     */
    public function formatResponse(ResponseInterface $response);
}
