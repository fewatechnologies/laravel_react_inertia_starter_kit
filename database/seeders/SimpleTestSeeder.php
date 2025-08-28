<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SimpleTestSeeder extends Seeder
{
    public function run(): void
    {
        // Create test users directly in test database
        $users = [
            [
                'name' => 'Test Administrator',
                'email' => 'admin@test.com',
                'phone' => '9843223774',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_active' => true,
            ],
            [
                'name' => 'Test Manager',
                'email' => 'manager@test.com',
                'phone' => '9843223775',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ],
            [
                'name' => 'Test User',
                'email' => 'user@test.com',
                'phone' => '9843223776',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            \App\Models\Test\User::firstOrCreate(
                ['email' => $userData['email']], 
                $userData
            );
        }

        // Ensure test dashboard type exists in main database
        \App\Models\DashboardType::updateOrCreate(
            ['type' => 'test'],
            [
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
            ]
        );

        echo "✅ Simple test seeder completed!\n";
        echo "📧 Test Credentials:\n";
        echo "   - Admin: admin@test.com / password\n";
        echo "   - Manager: manager@test.com / password\n";
        echo "   - User: user@test.com / password\n";
        echo "📱 SMS Test Phone: 9843223774\n";
        echo "🌐 Login URL: http://127.0.0.1:8000/test/login\n";
    }
}