<?php

namespace App\Services;

use App\Interfaces\UserServiceInterface;
use App\Models\User;

class UserService implements UserServiceInterface
{
    public function getAllUsers()
    {
        return User::withoutSuperAdmin()->with('roles')->paginate();
    }

    public function getUserById(int|string $id)
    {
        return User::with('roles')->find($id);
    }

    public function createUser(array $data)
    {
        return User::create($data);
    }

    public function updateUser(User $user, array $data)
    {
        if (! $data) {
            return false;
        }

        return $user->update($data);
    }

    public function deleteUser(User $user)
    {
        return $user->delete();
    }

    public function syncRoles(User $user, array $roles)
    {
        return $user->syncRoles($roles);
    }
}
