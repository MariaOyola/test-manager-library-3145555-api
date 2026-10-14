<?php

namespace Library\Application;

final readonly class AuthenticatedIdentity
{
    public function __construct(public string $userId, public string $role) {}
}
