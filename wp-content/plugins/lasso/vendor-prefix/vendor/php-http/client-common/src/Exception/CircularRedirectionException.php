<?php

declare (strict_types=1);
namespace LassoVendor\Http\Client\Common\Exception;

use LassoVendor\Http\Client\Exception\HttpException;
/**
 * Thrown when circular redirection is detected.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
final class CircularRedirectionException extends HttpException
{
}
