<?php

namespace Library\Domain;

final readonly class User
{
    public string $username;

    public function __construct(public string $id, string $username, private string $passwordHash, public string $role)
    {
        $this->username = self::normalizeUsername($username);
        if ($this->username === '' || strlen($this->username) > 120 || !in_array($role, ['admin', 'librarian'], true)) {
            throw new \InvalidArgumentException('Usuario o rol inválido.');
        }
    }

    public static function normalizeUsername(string $username): string
    {
        return strtolower(trim($username));
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }
}
