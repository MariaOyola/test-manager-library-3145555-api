<?php

namespace Library\Adapters\Outbound\Persistence;

use Library\Application\Ports\Outbound\UserRepository;
use Library\Domain\User;
use Library\Adapters\Outbound\Persistence\Models\UserModel;

final class EloquentUserRepository implements UserRepository
{
    public function findByUsername(string $username): ?User
    {
        return $this->toDomain(UserModel::query()->where('username', $username)->first());
    }

    public function findById(string $id): ?User
    {
        return $this->toDomain(UserModel::query()->find($id));
    }

    public function addIfAbsent(User $user): void
    {
        UserModel::query()->insertOrIgnore(['id' => $user->id, 'username' => $user->username, 'password_hash' => $user->passwordHash(), 'role' => $user->role]);
    }

    private function toDomain(?UserModel $row): ?User
    {
        return $row ? new User($row->id, $row->username, $row->password_hash, $row->role) : null;
    }
}
