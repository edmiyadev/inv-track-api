<?php

namespace Database\Seeders;

use App\Enums\ActionEnum;
use App\Enums\ModelPermissionEnum;
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

        Permission::createOrFirst(['name' => 'users.syncRoles', 'guard_name' => 'sanctum']);

        Role::createOrFirst(['name' => 'Super Admin', 'guard_name' => 'sanctum']);
        $roleAdmin = Role::createOrFirst(['name' => 'Admin', 'guard_name' => 'sanctum']);

        $roleAdmin->givePermissionTo([
            'suppliers.view',
            'suppliers.viewAny',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            'purchases.view',
            'purchases.viewAny',
            'purchases.create',
            'purchases.edit',
            'purchases.delete',
            'inventory_stocks.view',
            'inventory_stocks.viewAny',
            'inventory_stocks.edit',
            'inventory_movements.view',
            'inventory_movements.viewAny',
            'inventory_movements.create',
        ]);
    }
}
