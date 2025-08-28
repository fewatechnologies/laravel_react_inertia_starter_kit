<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample dashboard types for testing
        \App\Models\DashboardType::create([
            'type' => 'admin',
            'name' => 'Master Admin',
            'description' => 'Master administration dashboard for system management',
            'theme_config' => [
                'primary_color' => '#3b82f6',
                'secondary_color' => '#64748b',
                'sidebar_color' => '#ffffff',
                'text_color' => '#1f2937',
                'background_color' => '#f9fafb',
                'dark_mode' => false,
            ],
            'auth_methods' => ['email'],
            'settings' => [],
            'is_active' => true,
        ]);

        \App\Models\DashboardType::create([
            'type' => 'doctor',
            'name' => 'Doctor Dashboard',
            'description' => 'Dashboard for medical doctors',
            'theme_config' => [
                'primary_color' => '#10b981',
                'secondary_color' => '#6b7280',
                'sidebar_color' => '#ffffff',
                'text_color' => '#1f2937',
                'background_color' => '#f0fdf4',
                'dark_mode' => false,
            ],
            'auth_methods' => ['email', 'sms'],
            'settings' => [],
            'is_active' => true,
        ]);

        \App\Models\DashboardType::create([
            'type' => 'nurse',
            'name' => 'Nurse Dashboard',
            'description' => 'Dashboard for nursing staff',
            'theme_config' => [
                'primary_color' => '#8b5cf6',
                'secondary_color' => '#64748b',
                'sidebar_color' => '#ffffff',
                'text_color' => '#1f2937',
                'background_color' => '#faf5ff',
                'dark_mode' => false,
            ],
            'auth_methods' => ['email'],
            'settings' => [],
            'is_active' => true,
        ]);
    }
}
