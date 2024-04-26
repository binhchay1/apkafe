<?php

namespace Bugsnag\DateTime;

use DateTimeImmutable;
final class Clock implements \Bugsnag\DateTime\ClockInterface
{
    /**
     * @return DateTimeImmutable
     */
    public function now()
    {
        return new \DateTimeImmutable();
    }
}
