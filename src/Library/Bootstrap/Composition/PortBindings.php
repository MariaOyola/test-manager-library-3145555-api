<?php

namespace Library\Bootstrap\Composition;

use Illuminate\Support\ServiceProvider;
use Library\Application\Ports\Outbound\{Clock, PasswordHasher, UserRepository, TokenIssuer, TokenVerifier};
use Library\Adapters\Outbound\Security\{SystemClock, BcryptPasswordHasher, JwtTokens};
use Library\Adapters\Outbound\Persistence\EloquentUserRepository;

final class PortBindings extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Clock::class, SystemClock::class);
        $this->app->singleton(PasswordHasher::class, BcryptPasswordHasher::class);
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->singleton(JwtTokens::class, fn ($app) => new JwtTokens((string) config('library.jwt_secret'), $app->make(Clock::class)));
        $this->app->alias(JwtTokens::class, TokenIssuer::class);
        $this->app->alias(JwtTokens::class, TokenVerifier::class);
    }
}
