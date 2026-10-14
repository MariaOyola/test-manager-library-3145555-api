<?php

namespace Library\Adapters\Outbound\Security;

use Library\Application\Ports\Outbound\Clock;

final class SystemClock implements Clock
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
