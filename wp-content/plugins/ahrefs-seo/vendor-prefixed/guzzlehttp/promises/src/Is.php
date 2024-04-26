<?php

namespace ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Promise;

final class Is
{
    /**
     * Returns true if a promise is pending.
     *
     * @return bool
     */
    public static function pending(\ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Promise\PromiseInterface::PENDING;
    }
    /**
     * Returns true if a promise is fulfilled or rejected.
     *
     * @return bool
     */
    public static function settled(\ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() !== \ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Promise\PromiseInterface::PENDING;
    }
    /**
     * Returns true if a promise is fulfilled.
     *
     * @return bool
     */
    public static function fulfilled(\ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Promise\PromiseInterface::FULFILLED;
    }
    /**
     * Returns true if a promise is rejected.
     *
     * @return bool
     */
    public static function rejected(\ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \ahrefs\AhrefsSeo_Vendor\GuzzleHttp\Promise\PromiseInterface::REJECTED;
    }
}
