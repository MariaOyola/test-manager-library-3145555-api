<?php

namespace Library\Adapters\Outbound\Security;

use Library\Application\Ports\Outbound\PasswordHasher;

final class BcryptPasswordHasher implements PasswordHasher
{
    private string $dummyHash;

    public function __construct()
    {
        $this->dummyHash = $this->hash(bin2hex(random_bytes(32)));
    }

    public function hash(#[\SensitiveParameter] string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function verify(#[\SensitiveParameter] string $password, string $hash): bool
    {
        if ($hash === '') {
            $hash = $this->dummyHash;
        }
        return password_verify($password, $hash);
    }
}
