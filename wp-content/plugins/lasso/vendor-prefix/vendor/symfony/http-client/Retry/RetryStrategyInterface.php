<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace LassoVendor\Symfony\Component\HttpClient\Retry;

use LassoVendor\Symfony\Component\HttpClient\Response\AsyncContext;
use LassoVendor\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
/**
 * @author Jérémy Derussé <jeremy@derusse.com>
 * @author Nicolas Grekas <p@tchwork.com>
 */
interface RetryStrategyInterface
{
    /**
     * Returns whether the request should be retried.
     *
     * @param ?string $responseContent Null is passed when the body did not arrive yet
     *
     * @return bool|null Returns null to signal that the body is required to take a decision
     */
    public function shouldRetry(AsyncContext $context, ?string $responseContent, ?TransportExceptionInterface $exception) : ?bool;
    /**
     * Returns the time to wait in milliseconds.
     */
    public function getDelay(AsyncContext $context, ?string $responseContent, ?TransportExceptionInterface $exception) : int;
}
