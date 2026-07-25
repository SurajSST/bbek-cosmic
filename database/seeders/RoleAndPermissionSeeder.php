<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions by module
        $permissionsByModule = [
            'dashboard' => [
                'dashboard.view',
            ],
            'users' => [
                'users.view',
                'users.create',
                'users.edit',
                'users.delete',
            ],
            'roles' => [
                'roles.view',
                'roles.create',
                'roles.edit',
                'roles.delete',
            ],
            'permissions' => [
                'permissions.view',
                'permissions.create',
                'permissions.edit',
                'permissions.delete',
            ],
            'sales-orders' => [
                'sales-orders.view',
                'sales-orders.create',
                'sales-orders.edit',
                'sales-orders.delete',
            ],
        ];

        // Create or fetch permissions
        $allPermissions = [];
        foreach ($permissionsByModule as $module => $permissions) {
            foreach ($permissions as $permissionName) {
                $permission = Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web']
                );
                $allPermissions[] = $permission;
            }
        }

        // Create or fetch Super Admin role
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web']
        );

        // Sync all permissions to Super Admin role
        $superAdminRole->syncPermissions(Permission::all());

        // Create initial administrator user
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $adminPassword = env('ADMIN_PASSWORD', 'Password123!');
        $adminName = env('ADMIN_NAME', 'System Administrator');

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Assign Super Admin role to administrator
        if (!$admin->hasRole('Super Admin')) {
            $admin->assignRole($superAdminRole);
        }
    }
}
