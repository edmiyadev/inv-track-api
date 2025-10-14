<?php

namespace Database\Seeders;

use App\Enums\ActionEnum;
use App\Enums\ModelPermissionEnum;
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

        $actions = ActionEnum::values();
        $models = ModelPermissionEnum::values();
        $permissions = [];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                $permissions[] = "{$model}.{$action}";
            }
        }

        foreach ($permissions as $permission) {
            Permission::createOrFirst(['name' => $permission, 'guard_name' => 'sanctum']);
        }

        Role::createOrFirst(['name' => 'Super Admin', 'guard_name' => 'sanctum']);
        $roleAdmin = Role::createOrFirst(['name' => 'Admin', 'guard_name' => 'sanctum']);

        $roleAdmin->givePermissionTo([
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
        ]);
    }
}
