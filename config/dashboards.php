<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Multi-Dashboard Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file manages the multi-dashboard system settings
    | including default configurations, theme options, and security settings.
    |
    */

    'enabled' => env('MULTI_DASHBOARD_ENABLED', true),

    'default_dashboard_type' => env('DEFAULT_DASHBOARD_TYPE', 'admin'),

    'allow_dashboard_creation' => env('ALLOW_DASHBOARD_CREATION', true),

    /*
    |--------------------------------------------------------------------------
    | Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | Default authentication settings for new dashboard types.
    |
    */

    'auth' => [
        'default_methods' => ['email'],
        'available_methods' => ['email', 'sms'],
        'rate_limiting' => [
            'max_attempts' => 5,
            'lockout_duration' => 900, // 15 minutes
        ],
        'otp' => [
            'length' => 6,
            'expiry' => 600, // 10 minutes
        ],
        'jwt' => [
            'ttl' => env('JWT_TTL', 60),
            'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Configuration
    |--------------------------------------------------------------------------
    |
    | Default theme settings and available presets for dashboard customization.
    |
    */

    'theme' => [
        'default' => [
            'primary_color' => '#3b82f6',
            'secondary_color' => '#64748b',
            'sidebar_color' => '#ffffff',
            'text_color' => '#1f2937',
            'background_color' => '#f9fafb',
            'dark_mode' => false,
            'font_family' => 'Inter',
            'border_radius' => '0.5rem',
            'sidebar_width' => '16rem',
        ],
        'presets' => [
            'default' => 'Default Blue',
            'dark' => 'Dark Mode',
            'green' => 'Nature Green',
            'purple' => 'Royal Purple',
            'orange' => 'Sunset Orange',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security settings for the multi-dashboard system.
    |
    */

    'security' => [
        'password_requirements' => [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_symbols' => false,
        ],
        'session' => [
            'timeout' => 7200, // 2 hours
            'concurrent_sessions' => 3,
        ],
        'api' => [
            'rate_limit' => 60, // per minute
            'rate_limit_unauthenticated' => 10, // per minute
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Database connection settings for multi-tenant support.
    |
    */

    'database' => [
        'shared_connection' => true,
        'tenant_connections' => [],
        'migration_path' => 'database/migrations/tenants',
    ],

    /*
    |--------------------------------------------------------------------------
    | File Generation Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for automatic file generation when creating new dashboards.
    |
    */

    'generation' => [
        'create_seeders' => true,
        'create_factories' => true,
        'create_tests' => true,
        'create_api_docs' => true,
        'default_roles' => ['admin', 'manager', 'user'],
        'default_permissions' => [
            'view_dashboard',
            'manage_users',
            'manage_roles',
            'manage_settings',
            'view_reports',
            'export_data',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Activity logging settings for audit trails and monitoring.
    |
    */

    'logging' => [
        'enabled' => true,
        'log_authentication' => true,
        'log_user_actions' => true,
        'log_api_requests' => true,
        'retention_days' => 90,
        'cleanup_schedule' => 'daily',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for email and SMS notifications across dashboard types.
    |
    */

    'notifications' => [
        'email' => [
            'enabled' => true,
            'queue' => true,
        ],
        'sms' => [
            'enabled' => true,
            'queue' => true,
            'provider' => 'aakash_sms',
        ],
    ],

];