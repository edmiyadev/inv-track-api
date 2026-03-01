<?php

namespace App\Interfaces;

use Spatie\Permission\Models\Role;

interface RoleServiceInterface
{
    public function getAllRoles();

    public function getRoleById(int|string $id);

    public function createRole(array $data);

    public function updateRole(Role $role, array $data);

    public function deleteRole(Role $role);
}
