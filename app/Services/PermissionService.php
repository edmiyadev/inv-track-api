<?php

namespace App\Services;

use App\Interfaces\PermissionServiceInterface;
use Spatie\Permission\Models\Permission;

class PermissionService implements PermissionServiceInterface
{
    public function getAllPermissions()
    {
        return Permission::all();
    }
}
