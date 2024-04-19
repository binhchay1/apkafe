<?php

declare (strict_types=1);
namespace LassoVendor\Sentry\Transport;

use LassoVendor\GuzzleHttp\Promise\FulfilledPromise;
use LassoVendor\GuzzleHttp\Promise\PromiseInterface;
use LassoVendor\Sentry\Event;
use LassoVendor\Sentry\Response;
use LassoVendor\Sentry\ResponseStatus;
/**
 * This transport fakes the sending of events by just ignoring them.
 *
 * @author Stefano Arlandini <sarlandini@alice.it>
 */
final class NullTransport implements TransportInterface
{
    /**
     * {@inheritdoc}
     */
    public function send(Event $event) : PromiseInterface
    {
        return new FulfilledPromise(new Response(ResponseStatus::skipped(), $event));
    }
    /**
     * {@inheritdoc}
     */
    public function close(?int $timeout = null) : PromiseInterface
    {
        return new FulfilledPromise(\true);
    }
}
