<?php

namespace Library\Application\Ports\Outbound;

use Library\Domain\User;

interface TokenIssuer
{
    public function issue(User $user): string;
}
