<?php

namespace Library\Application\Services;

use Library\Application\Ports\Outbound\{UserRepository, PasswordHasher};
use Library\Domain\User;

final readonly class ProvisionAdminService
{
    public function __construct(private UserRepository $users, private PasswordHasher $hasher) {}

    public function execute(string $id, string $username, #[\SensitiveParameter] string $password): void
    {
        $username = User::normalizeUsername($username);
        if ($username === '' || $password === '') {
            throw new \InvalidArgumentException('Configura ADMIN_USERNAME y ADMIN_PASSWORD.');
        }
        $existing = $this->users->findByUsername($username);
        if ($existing !== null) {
            if ($existing->role !== 'admin') {
                throw new \RuntimeException('El usuario inicial ya existe con otro rol.');
            }
            return;
        }
        $this->users->addIfAbsent(new User($id, $username, $this->hasher->hash($password), 'admin'));
        if ($this->users->findByUsername($username)?->role !== 'admin') {
            throw new \RuntimeException('No se pudo provisionar el administrador.');
        }
    }
}
