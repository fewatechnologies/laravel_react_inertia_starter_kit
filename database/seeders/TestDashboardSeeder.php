<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestDashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Set connection for Spatie Permission
        config(['permission.database_connection' => 'test_mysql']);
        config(['permission.table_names.roles' => 'test_roles']);
        config(['permission.table_names.permissions' => 'test_permissions']);
        config(['permission.table_names.model_has_permissions' => 'test_model_has_permissions']);
        config(['permission.table_names.model_has_roles' => 'test_model_has_roles']);
        config(['permission.table_names.role_has_permissions' => 'test_role_has_permissions']);
        
        // Create permissions for test dashboard
        $permissions = [
            'view_dashboard',
            'manage_users',
            'manage_roles',
            'manage_settings',
            'view_reports',
            'export_data',
            'send_sms',
            'view_analytics',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'test',
            ]);
        }

        // Create roles for test dashboard
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'test',
        ]);

        $managerRole = \Spatie\Permission\Models\Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'test',
        ]);

        $userRole = \Spatie\Permission\Models\Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'test',
        ]);

        // Assign all permissions to admin
        $adminRole->givePermissionTo($permissions);

        // Assign specific permissions to manager
        $managerRole->givePermissionTo([
            'view_dashboard',
            'manage_users',
            'view_reports',
            'send_sms',
        ]);

        // Assign basic permissions to user
        $userRole->givePermissionTo([
            'view_dashboard',
            'view_reports',
        ]);

        // Create test users
        $admin = \App\Models\Test\User::create([
            'name' => 'Test Administrator',
            'email' => 'admin@test.com',
            'phone' => '9843223774',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'is_active' => true,
        ]);

        $manager = \App\Models\Test\User::create([
            'name' => 'Test Manager',
            'email' => 'manager@test.com',
            'phone' => '9843223775',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $user = \App\Models\Test\User::create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'phone' => '9843223776',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Assign roles to users
        $admin->assignRole('admin');
        $manager->assignRole('manager');
        $user->assignRole('user');

        // Add test dashboard type to main database
        \App\Models\DashboardType::create([
            'type' => 'test',
            'name' => 'Test Dashboard',
            'description' => 'Multi-database test dashboard with SMS authentication',
            'theme_config' => [
                'primary_color' => '#3b82f6',
                'secondary_color' => '#64748b',
                'sidebar_color' => '#ffffff',
                'text_color' => '#1f2937',
                'background_color' => '#f9fafb',
                'dark_mode' => false,
            ],
            'auth_methods' => ['email', 'sms'],
            'settings' => [
                'database_connection' => 'test_mysql',
                'sms_enabled' => true,
                'registration_enabled' => true,
            ],
            'is_active' => true,
        ]);

        echo "Test dashboard seeded successfully!\n";
        echo "Admin: admin@test.com / password\n";
        echo "Manager: manager@test.com / password\n";
        echo "User: user@test.com / password\n";
        echo "Phone: 9843223774 (for SMS testing)\n";
    }
}
