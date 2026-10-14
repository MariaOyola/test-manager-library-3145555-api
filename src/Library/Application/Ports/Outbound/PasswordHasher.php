<?php

namespace Library\Application\Ports\Outbound;

interface PasswordHasher
{
    public function hash(#[\SensitiveParameter] string $password): string;
    public function verify(#[\SensitiveParameter] string $password, string $hash): bool;
}
