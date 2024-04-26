<?php

namespace Bugsnag\Shutdown;

use Bugsnag\Client;
/**
 * Class PhpShutdownStrategy.
 *
 * Use the built-in PHP shutdown function
 */
class PhpShutdownStrategy implements \Bugsnag\Shutdown\ShutdownStrategyInterface
{
    /**
     * @param Client $client
     *
     * @return void
     */
    public function registerShutdownStrategy(\Bugsnag\Client $client)
    {
        \register_shutdown_function([$client, 'flush']);
    }
}
