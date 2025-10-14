<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

interface RoleServiceInterface
{
    public function getAllRoles();
    public function getRoleById(int|string $id);
    public function createRole(array $data);
    public function updateRole(Role $role, array $data);
    public function deleteRole(Role $role);
    // public function syncPermissions(Role $role, Collection $permissions);
    // public function revokePermissions(Permission $permission);
}
