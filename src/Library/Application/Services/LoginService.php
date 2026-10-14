<?php

namespace Library\Application\Services;

use Library\Application\Errors\InvalidCredentials;
use Library\Application\Ports\Outbound\{UserRepository, PasswordHasher, TokenIssuer};
use Library\Domain\User;

final readonly class LoginService
{
    public function __construct(private UserRepository $users, private PasswordHasher $hasher, private TokenIssuer $tokens) {}

    public function execute(string $username, #[\SensitiveParameter] string $password): array
    {
        $user = $this->users->findByUsername(User::normalizeUsername($username));
        // Ejecutar la verificación incluso si el usuario no existe.
        $valid = $this->hasher->verify($password, $user?->passwordHash() ?? '');
        if (!$user || !$valid) {
            throw new InvalidCredentials();
        }

        return ['access_token' => $this->tokens->issue($user), 'token_type' => 'Bearer', 'expires_in' => 28800, 'role' => $user->role];
    }
}
