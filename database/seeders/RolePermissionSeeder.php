<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define Permissions
        $permissions = [
            'view-dashboard',
            'view-clients',
            'manage-clients',
            'view-projects',
            'manage-projects',
            'view-kanban',
            'view-time-tracker',
            'view-plugins',
            'manage-plugins',
            'manage-rbac',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define Roles and Assign Permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $managerRole->syncPermissions([
            'view-dashboard',
            'view-clients',
            'view-projects',
            'view-kanban',
            'view-time-tracker',
            'view-plugins',
        ]);

        $clientRole = Role::firstOrCreate(['name' => 'client']);
        $clientRole->syncPermissions([
            'view-dashboard',
            'view-projects',
            'view-kanban',
            'view-time-tracker',
        ]);

        // Assign Roles to existing Users based on their 'role' column
        $users = User::all();
        foreach ($users as $user) {
            if ($user->role === 'admin') {
                $user->assignRole($adminRole);
            } elseif ($user->role === 'client') {
                $user->assignRole($clientRole);
            } else {
                $user->assignRole($managerRole);
            }
        }
    }
}
