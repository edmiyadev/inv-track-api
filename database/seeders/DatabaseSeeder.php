<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            SupplierSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        $superAdminUser = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'username' => 'superadmin',
        ]);

        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'username' => 'admin',
        ]);

        $roleSuperAdmin = Role::findByName('Super Admin', 'sanctum');
        $roleAdmin = Role::findByName('Admin', 'sanctum');

        $superAdminUser->assignRole($roleSuperAdmin);
        $adminUser->assignRole($roleAdmin);
    }
}
