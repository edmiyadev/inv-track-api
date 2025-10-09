<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'suppliers.view',
            'suppliers.viewAny',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'sanctum']);
        }

        Role::create(['name' => 'Super Admin', 'guard_name' => 'sanctum']);
        $roleAdmin = Role::create(['name' => 'Admin', 'guard_name' => 'sanctum']);

        $roleAdmin->givePermissionTo([
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
        ]);
    }
}
