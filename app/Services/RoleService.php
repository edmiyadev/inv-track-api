<?php

namespace App\Services;

use App\Interfaces\RoleServiceInterface;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
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
        return Role::create(['name' => $data['name'], 'guard_name' => 'sanctum']);
    }

    public function updateRole(Role $role, array $data)
    {
        if (!$data) {
            return false;
        }

        return $role->update($data);
    }

    public function deleteRole(Role $role)
    {
        return $role->delete();
    }

    // public function syncPermissions(Role $role, Collection $permissions)
    // {
    //     rerun $role->syncPermissions($permissions);
    // }

    // public function revokePermissions(Permission $permission) {}
}
