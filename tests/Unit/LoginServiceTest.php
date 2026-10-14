<?php

namespace Tests\Unit;

use Library\Application\Errors\InvalidCredentials;
use Library\Application\Ports\Outbound\{UserRepository, PasswordHasher, TokenIssuer};
use Library\Application\Services\LoginService;
use Library\Domain\User;
use PHPUnit\Framework\TestCase;

final class LoginServiceTest extends TestCase
{
    public function test_login_works_with_ports_without_framework_or_database(): void
    {
        $user = new User('11111111-1111-4111-8111-111111111111', ' Ana ', 'fake-hash', 'librarian');
        $users = $this->createMock(UserRepository::class);
        $users->expects($this->once())->method('findByUsername')->with('ana')->willReturn($user);
        $hasher = $this->createMock(PasswordHasher::class);
        $hasher->expects($this->once())->method('verify')->with('password', 'fake-hash')->willReturn(true);
        $tokens = $this->createMock(TokenIssuer::class);
        $tokens->expects($this->once())->method('issue')->with($user)->willReturn('fake-token');
        $this->assertSame(['access_token' => 'fake-token', 'token_type' => 'Bearer', 'expires_in' => 28800, 'role' => 'librarian'], (new LoginService($users, $hasher, $tokens))->execute(' ANA ', 'password'));
    }

    public function test_unknown_user_still_checks_password_and_never_issues_token(): void
    {
        $users = $this->createMock(UserRepository::class);
        $users->method('findByUsername')->willReturn(null);
        $hasher = $this->createMock(PasswordHasher::class);
        $hasher->expects($this->once())->method('verify')->with('password', '')->willReturn(false);
        $tokens = $this->createMock(TokenIssuer::class);
        $tokens->expects($this->never())->method('issue');
        $this->expectException(InvalidCredentials::class);
        (new LoginService($users, $hasher, $tokens))->execute('unknown', 'password');
    }
}
