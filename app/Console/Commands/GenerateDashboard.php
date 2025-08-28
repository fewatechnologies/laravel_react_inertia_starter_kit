<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DashboardGenerator;
use App\Services\DatabaseManager;
use App\Services\AakashSmsService;
use App\Models\DashboardType;
use Illuminate\Support\Str;

class GenerateDashboard extends Command
{
    protected $signature = 'dashboard:generate {type?}';
    protected $description = 'Generate a complete dashboard system with interactive setup';

    protected DashboardGenerator $generator;
    protected DatabaseManager $dbManager;

    public function __construct(DashboardGenerator $generator, DatabaseManager $dbManager)
    {
        parent::__construct();
        $this->generator = $generator;
        $this->dbManager = $dbManager;
    }

    public function handle()
    {
        $this->info('🚀 Laravel Multi-Dashboard Generator');
        $this->info('=====================================');
        $this->newLine();

        // Get dashboard type
        $type = $this->argument('type') ?: $this->askDashboardType();

        if (!$this->isValidDashboardType($type)) {
            $this->error('Invalid dashboard type. Use only lowercase letters, numbers, and underscores.');
            return 1;
        }

        // Check if exists
        if (DashboardType::where('type', $type)->exists()) {
            if (!$this->confirm("Dashboard '{$type}' already exists. Overwrite?", false)) {
                return 0;
            }
        }

        $this->info("Creating dashboard: {$type}");
        $this->newLine();

        try {
            // Collect all configuration
            $config = $this->collectConfiguration($type);

            // Show summary
            $this->displayConfigSummary($config);

            if (!$this->confirm('Proceed with dashboard creation?', true)) {
                $this->info('Dashboard creation cancelled.');
                return 0;
            }

            // Generate dashboard
            $this->info('🔨 Generating dashboard files...');
            $dashboardType = $this->generator->generate($config);

            // Success message
            $this->displaySuccess($dashboardType, $config);

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Dashboard creation failed: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Ask for dashboard type
     */
    protected function askDashboardType(): string
    {
        return strtolower($this->ask('Dashboard type (e.g., doctor, clinic, shop)'));
    }

    /**
     * Validate dashboard type
     */
    protected function isValidDashboardType(string $type): bool
    {
        return preg_match('/^[a-z][a-z0-9_]*$/', $type);
    }

    /**
     * Collect all configuration through interactive prompts
     */
    protected function collectConfiguration(string $type): array
    {
        $config = ['type' => $type];

        // Basic Info
        $config['name'] = $this->ask('Dashboard name', ucfirst($type) . ' Dashboard');
        $config['description'] = $this->ask('Dashboard description', "Dashboard for {$config['name']}");

        // Landing Page
        $config['has_landing_page'] = $this->confirm('Create landing page?', false);

        // User System
        $config['user_system'] = $this->choice(
            'User management system',
            [
                'single' => 'Single user (no additional users/roles)',
                'roles' => 'Role-based (multiple users with roles & permissions)'
            ],
            'roles'
        );

        // Authentication Methods
        $config['auth_methods'] = $this->collectAuthMethods();

        // Database Strategy
        $config = array_merge($config, $this->collectDatabaseStrategy($type));

        // Theme
        $config['theme_config'] = $this->collectThemeConfig();

        // Additional Settings
        $config['settings'] = [
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
        ];

        return $config;
    }

    /**
     * Collect authentication methods
     */
    protected function collectAuthMethods(): array
    {
        $methods = [];

        if ($this->confirm('Enable email authentication?', true)) {
            $methods[] = 'email';
        }

        if ($this->confirm('Enable SMS authentication (Aakash SMS)?', false)) {
            $methods[] = 'sms';
            $this->verifyAakashSmsConfig();
        }

        if (empty($methods)) {
            $this->warn('At least one authentication method is required. Enabling email.');
            $methods[] = 'email';
        }

        return $methods;
    }

    /**
     * Verify Aakash SMS configuration
     */
    protected function verifyAakashSmsConfig(): void
    {
        $token = config('services.aakash_sms.token');

        if (!$token) {
            $this->warn('Aakash SMS token not configured in .env file.');
            $this->line('Add: AAKASH_SMS_TOKEN=your_token_here');
        } else {
            $this->info('✅ Aakash SMS configured');
        }
    }

    /**
     * Collect database strategy
     */
    protected function collectDatabaseStrategy(string $type): array
    {
        $strategy = $this->choice(
            'Database strategy',
            [
                'shared' => 'Shared database (with table prefixes)',
                'separate' => 'Separate database (isolated data)'
            ],
            'shared'
        );

        $config = ['database_strategy' => $strategy];

        if ($strategy === 'shared') {
            $config['database_config'] = $this->collectSharedDatabaseConfig($type);
        } else {
            $config['database_config'] = $this->collectSeparateDatabaseConfig($type);
        }

        return $config;
    }

    /**
     * Collect shared database configuration
     */
    protected function collectSharedDatabaseConfig(string $type): array
    {
        $prefix = $this->ask('Table prefix', $type . '_');

        // Ensure prefix ends with underscore
        if (!str_ends_with($prefix, '_')) {
            $prefix .= '_';
        }

        return [
            'strategy' => 'shared',
            'prefix' => $prefix,
            'connection' => 'mysql',
        ];
    }

    /**
     * Collect separate database configuration
     */
    protected function collectSeparateDatabaseConfig(string $type): array
    {
        $this->info('Setting up separate database for ' . $type);

        $config = [
            'strategy' => 'separate',
            'connection_name' => $type . '_mysql',
            'host' => $this->ask('Database host', '127.0.0.1'),
            'port' => (int) $this->ask('Database port', '3306'),
            'database' => $this->ask('Database name', 'laravel_' . $type . '_db'),
            'username' => $this->ask('Database username', config('database.connections.mysql.username')),
            'password' => $this->secret('Database password'),
        ];

        // Test connection
        if ($this->confirm('Test database connection?', true)) {
            if ($this->dbManager->testConnection($config)) {
                $this->info('✅ Database connection successful');
            } else {
                $this->error('❌ Database connection failed');
                if (!$this->confirm('Continue anyway?', false)) {
                    throw new \Exception('Database connection required');
                }
            }
        }

        return $config;
    }

    /**
     * Collect theme configuration
     */
    protected function collectThemeConfig(): array
    {
        $preset = $this->choice(
            'Theme preset',
            [
                'blue' => 'Blue (Default)',
                'green' => 'Green (Medical)',
                'purple' => 'Purple (Creative)',
                'orange' => 'Orange (Energy)',
                'red' => 'Red (Alert)',
                'custom' => 'Custom colors'
            ],
            'blue'
        );

        $themes = [
            'blue' => [
                'primary' => '#3b82f6',
                'secondary' => '#64748b',
                'accent' => '#06b6d4',
            ],
            'green' => [
                'primary' => '#10b981',
                'secondary' => '#6b7280',
                'accent' => '#34d399',
            ],
            'purple' => [
                'primary' => '#8b5cf6',
                'secondary' => '#6b7280',
                'accent' => '#a78bfa',
            ],
            'orange' => [
                'primary' => '#f59e0b',
                'secondary' => '#6b7280',
                'accent' => '#fbbf24',
            ],
            'red' => [
                'primary' => '#ef4444',
                'secondary' => '#6b7280',
                'accent' => '#f87171',
            ],
        ];

        if ($preset === 'custom') {
            return [
                'primary' => $this->ask('Primary color (hex)', '#3b82f6'),
                'secondary' => $this->ask('Secondary color (hex)', '#64748b'),
                'accent' => $this->ask('Accent color (hex)', '#06b6d4'),
                'dark_mode' => $this->confirm('Enable dark mode?', false),
            ];
        }

        $config = $themes[$preset];
        $config['dark_mode'] = $this->confirm('Enable dark mode?', false);

        return $config;
    }

    /**
     * Display configuration summary
     */
    protected function displayConfigSummary(array $config): void
    {
        $this->newLine();
        $this->info('📋 Configuration Summary:');
        $this->line('========================');

        $this->table([], [
            ['Dashboard Type', $config['type']],
            ['Name', $config['name']],
            ['Description', $config['description']],
            ['Landing Page', $config['has_landing_page'] ? 'Yes' : 'No'],
            ['User System', $config['user_system'] === 'roles' ? 'Role-based' : 'Single user'],
            ['Auth Methods', implode(', ', $config['auth_methods'])],
            ['Database Strategy', $config['database_strategy']],
            ['Database Config', $this->formatDatabaseConfig($config)],
            ['Theme', $config['theme_config']['primary']],
        ]);
    }

    /**
     * Format database config for display
     */
    protected function formatDatabaseConfig(array $config): string
    {
        if ($config['database_strategy'] === 'shared') {
            return "Shared (prefix: {$config['database_config']['prefix']})";
        }

        return "Separate ({$config['database_config']['database']})";
    }

    /**
     * Display success message
     */
    protected function displaySuccess(DashboardType $dashboardType, array $config): void
    {
        $this->newLine();
        $this->info('🎉 Dashboard created successfully!');
        $this->line('================================');
        $this->newLine();

        $this->info('📱 URLs:');
        if ($config['has_landing_page']) {
            $this->line("  Landing: " . url("/{$dashboardType->type}"));
        }
        $this->line("  Login: " . url("/{$dashboardType->type}/login"));
        $this->line("  Dashboard: " . url("/{$dashboardType->type}/dashboard"));

        $this->newLine();
        $this->info('🔐 Default Admin Credentials:');
        $this->line("  Email: admin@{$dashboardType->type}.com");
        $this->line("  Password: password");

        $this->newLine();
        $this->info('🚀 Next Steps:');
        $this->line("  1. Visit the login page to test authentication");
        $this->line("  2. Customize the dashboard theme and content");
        $this->line("  3. Add additional users if using role-based system");

        if (in_array('sms', $config['auth_methods'])) {
            $this->line("  4. Test SMS authentication with phone: " . config('services.aakash_sms.test_phone', 'N/A'));
        }
    }
}
