<?php

namespace Library\Application\Ports\Outbound;

interface Clock
{
    public function now(): \DateTimeImmutable;
}
