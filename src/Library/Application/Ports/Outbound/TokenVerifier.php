<?php

namespace Library\Application\Ports\Outbound;

use Library\Application\AuthenticatedIdentity;

interface TokenVerifier
{
    public function verify(string $token): AuthenticatedIdentity;
}
