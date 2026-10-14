<?php

namespace Library\Adapters\Outbound\Security;

use Firebase\JWT\{JWT, Key};
use Library\Application\AuthenticatedIdentity;
use Library\Application\Errors\InvalidToken;
use Library\Application\Ports\Outbound\{Clock, TokenIssuer, TokenVerifier};
use Library\Domain\User;

final readonly class JwtTokens implements TokenIssuer, TokenVerifier
{
    public function __construct(#[\SensitiveParameter] private string $secret, private Clock $clock)
    {
        if (strlen($secret) < 32) {
            throw new \RuntimeException('JWT_SECRET debe contener al menos 32 caracteres.');
        }
    }

    public function issue(User $user): string
    {
        $now = $this->clock->now()->getTimestamp();
        return JWT::encode(['sub' => $user->id, 'role' => $user->role, 'iat' => $now, 'nbf' => $now, 'exp' => $now + 28800], $this->secret, 'HS256');
    }

    public function verify(string $token): AuthenticatedIdentity
    {
        try {
            $claims = JWT::decode($token, new Key($this->secret, 'HS256'));
            $now = $this->clock->now()->getTimestamp();
            if (!isset($claims->sub, $claims->role, $claims->iat, $claims->nbf, $claims->exp)
                || !is_string($claims->sub)
                || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $claims->sub)
                || !in_array($claims->role, ['admin', 'librarian'], true)
                || !is_int($claims->iat) || !is_int($claims->nbf) || !is_int($claims->exp)
                || $claims->exp - $claims->iat !== 28800
                || $claims->nbf > $now || $claims->iat > $now || $claims->exp <= $now) {
                throw new InvalidToken();
            }
            return new AuthenticatedIdentity($claims->sub, $claims->role);
        } catch (\Exception) {
            throw new InvalidToken();
        }
    }
}
