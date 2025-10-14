<?php

namespace App\Services;

use App\Interfaces\RoleServiceInterface;
use Spatie\Permission\Models\Role;

class RoleService implements RoleServiceInterface
{
    public function getAllRoles()
    {
        return Role::with(['permissions', 'users'])->get();
    }

    public function getRoleById(int|string $id)
    {
        return Role::find($id) ?? false;
    }

    public function createRole(array $data)
    {
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'sanctum']);
        $role->syncPermissions($data['permissions']);

        return $role;
    }

    public function updateRole(Role $role, array $data)
    {
        if (!$data) {
            return false;
        }

        $role->syncPermissions($data['permissions']);

        return $role->update($data);
    }

    public function deleteRole(Role $role)
    {
        return $role->delete();
    }
}
