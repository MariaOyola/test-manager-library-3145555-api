<?php

namespace Library\Application\Ports\Outbound;

use Library\Domain\User;

interface UserRepository
{
    public function findByUsername(string $username): ?User;
    public function findById(string $id): ?User;
    public function addIfAbsent(User $user): void;
}
