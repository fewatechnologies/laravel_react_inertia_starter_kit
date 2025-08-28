<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DashboardService;
use Illuminate\Support\Str;

class CreateDashboardCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'dashboard:create 
                          {type : The dashboard type (e.g., doctor, nurse, admin)}
                          {--name= : The display name for the dashboard}
                          {--description= : Description of the dashboard}
                          {--auth=email : Authentication methods (email,sms,both)}
                          {--theme=default : Theme preset (default,dark,green,purple,orange)}
                          {--roles= : Default roles to create (comma-separated)}
                          {--permissions= : Default permissions to create (comma-separated)}
                          {--force : Force creation even if dashboard type exists}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new dashboard type with all necessary files and configurations';

    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        parent::__construct();
        $this->dashboardService = $dashboardService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = strtolower($this->argument('type'));
        
        // Validate dashboard type name
        if (!$this->isValidDashboardType($type)) {
            $this->error('Invalid dashboard type. Use only letters, numbers, and underscores.');
            return 1;
        }

        // Check if dashboard type already exists
        if (!$this->option('force') && $this->dashboardTypeExists($type)) {
            $this->error("Dashboard type '{$type}' already exists. Use --force to overwrite.");
            return 1;
        }

        $this->info("Creating dashboard type: {$type}");
        $this->newLine();

        // Collect dashboard information
        $dashboardData = $this->collectDashboardData($type);

        try {
            // Create the dashboard type
            $this->info('Creating dashboard configuration...');
            $dashboardData['force'] = $this->option('force');
            $dashboardType = $this->dashboardService->createDashboardType($dashboardData);

            $this->info('Generating files and directories...');
            $this->createProgressBar();

            // Run migrations
            $this->info('Running migrations...');
            $this->call('migrate');

            // Create default roles and permissions
            if ($dashboardData['roles'] || $dashboardData['permissions']) {
                $this->info('Creating default roles and permissions...');
                $this->createDefaultRolesAndPermissions($type, $dashboardData);
            }

            // Create seeder
            $this->info('Creating database seeder...');
            $this->createSeeder($type, $dashboardData);

            // Update route files
            $this->info('Updating route configurations...');
            $this->updateRouteFiles($type);

            // Generate API documentation
            $this->info('Generating API documentation...');
            $this->generateApiDocumentation($type);

            $this->newLine();
            $this->info("✅ Dashboard type '{$type}' created successfully!");
            $this->displaySummary($dashboardType, $dashboardData);

        } catch (\Exception $e) {
            $this->error("Failed to create dashboard type: " . $e->getMessage());
            $this->line("Stack trace: " . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    /**
     * Validate dashboard type name
     */
    protected function isValidDashboardType(string $type): bool
    {
        return preg_match('/^[a-z][a-z0-9_]*$/', $type);
    }

    /**
     * Check if dashboard type exists
     */
    protected function dashboardTypeExists(string $type): bool
    {
        return \App\Models\DashboardType::where('type', $type)->exists();
    }

    /**
     * Collect dashboard data from user input
     */
    protected function collectDashboardData(string $type): array
    {
        $name = $this->option('name') ?: $this->ask('Dashboard display name', Str::title(str_replace('_', ' ', $type)));
        $description = $this->option('description') ?: $this->ask('Dashboard description', "Dashboard for {$name} users");
        
        // Authentication methods
        $authOption = $this->option('auth');
        $authMethods = $this->parseAuthMethods($authOption);
        
        if (!$authMethods) {
            $authChoice = $this->choice('Select authentication methods', [
                'email' => 'Email only',
                'sms' => 'SMS only', 
                'both' => 'Both email and SMS'
            ], 'email');
            
            $authMethods = $this->parseAuthMethods($authChoice);
        }

        // Theme configuration
        $themePreset = $this->option('theme') ?: $this->choice('Select theme preset', [
            'default' => 'Default Blue',
            'dark' => 'Dark Mode',
            'green' => 'Nature Green',
            'purple' => 'Royal Purple',
            'orange' => 'Sunset Orange',
        ], 'default');

        // Roles and permissions
        $roles = $this->option('roles') ? explode(',', $this->option('roles')) : $this->askForRoles();
        $permissions = $this->option('permissions') ? explode(',', $this->option('permissions')) : $this->askForPermissions();

        return [
            'type' => $type,
            'name' => $name,
            'description' => $description,
            'auth_methods' => $authMethods,
            'theme_preset' => $themePreset,
            'roles' => array_filter($roles),
            'permissions' => array_filter($permissions),
        ];
    }

    /**
     * Parse authentication methods
     */
    protected function parseAuthMethods(string $auth): array
    {
        switch (strtolower($auth)) {
            case 'email':
                return ['email'];
            case 'sms':
                return ['sms'];
            case 'both':
                return ['email', 'sms'];
            default:
                return ['email'];
        }
    }

    /**
     * Ask for default roles
     */
    protected function askForRoles(): array
    {
        $this->info('Define default roles for this dashboard (press Enter to skip):');
        $roles = [];
        
        $defaultRoles = ['admin', 'manager', 'user'];
        
        if ($this->confirm('Create default roles (admin, manager, user)?', true)) {
            return $defaultRoles;
        }
        
        while (true) {
            $role = $this->ask('Role name (or press Enter to finish)');
            if (empty($role)) {
                break;
            }
            $roles[] = strtolower(trim($role));
        }
        
        return $roles;
    }

    /**
     * Ask for default permissions
     */
    protected function askForPermissions(): array
    {
        $this->info('Define default permissions for this dashboard (press Enter to skip):');
        
        $defaultPermissions = [
            'view_dashboard',
            'manage_users',
            'manage_roles',
            'manage_settings',
            'view_reports',
            'export_data',
        ];
        
        if ($this->confirm('Create default permissions?', true)) {
            return $defaultPermissions;
        }
        
        $permissions = [];
        while (true) {
            $permission = $this->ask('Permission name (or press Enter to finish)');
            if (empty($permission)) {
                break;
            }
            $permissions[] = strtolower(trim($permission));
        }
        
        return $permissions;
    }

    /**
     * Create progress bar for file generation
     */
    protected function createProgressBar(): void
    {
        $steps = [
            'Creating directories...',
            'Generating models...',
            'Creating controllers...',
            'Setting up authentication...',
            'Creating React components...',
            'Generating API endpoints...',
            'Setting up routes...',
            'Creating migrations...',
        ];

        $bar = $this->output->createProgressBar(count($steps));
        $bar->start();

        foreach ($steps as $step) {
            $this->line("  {$step}");
            $bar->advance();
            usleep(200000); // Small delay for visual effect
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Create default roles and permissions
     */
    protected function createDefaultRolesAndPermissions(string $type, array $data): void
    {
        // This would typically be handled by a seeder
        $this->line("  - Roles: " . implode(', ', $data['roles']));
        $this->line("  - Permissions: " . implode(', ', $data['permissions']));
    }

    /**
     * Create database seeder
     */
    protected function createSeeder(string $type, array $data): void
    {
        $studlyType = Str::studly($type);
        $seederName = "{$studlyType}DatabaseSeeder";
        
        $seederContent = $this->generateSeederContent($type, $data);
        file_put_contents(
            database_path("seeders/{$seederName}.php"),
            $seederContent
        );
        
        $this->line("  - Created: database/seeders/{$seederName}.php");
    }

    /**
     * Update route files
     */
    protected function updateRouteFiles(string $type): void
    {
        // Update web.php to include dashboard routes
        $webRoutesPath = base_path('routes/web.php');
        $routeInclude = "require __DIR__ . '/{$type}.php';";
        
        if (!str_contains(file_get_contents($webRoutesPath), $routeInclude)) {
            file_put_contents($webRoutesPath, "\n{$routeInclude}\n", FILE_APPEND);
        }
        
        // Update api.php for API routes
        $apiRoutesPath = base_path('routes/api.php');
        $apiRouteInclude = "require __DIR__ . '/api/{$type}.php';";
        
        if (!str_contains(file_get_contents($apiRoutesPath), $apiRouteInclude)) {
            file_put_contents($apiRoutesPath, "\n{$apiRouteInclude}\n", FILE_APPEND);
        }
        
        $this->line("  - Updated route files");
    }

    /**
     * Generate API documentation
     */
    protected function generateApiDocumentation(string $type): void
    {
        $docContent = $this->generateApiDocumentationContent($type);
        $docPath = base_path("docs/api/{$type}.md");
        
        if (!is_dir(dirname($docPath))) {
            mkdir(dirname($docPath), 0755, true);
        }
        
        file_put_contents($docPath, $docContent);
        $this->line("  - Created: docs/api/{$type}.md");
    }

    /**
     * Display creation summary
     */
    protected function displaySummary($dashboardType, array $data): void
    {
        $this->newLine();
        $this->line('<fg=green>Dashboard Summary:</>');
        $this->line("Type: {$dashboardType->type}");
        $this->line("Name: {$dashboardType->name}");
        $this->line("Authentication: " . implode(', ', $dashboardType->auth_methods));
        $this->line("Theme: {$data['theme_preset']}");
        
        if ($data['roles']) {
            $this->line("Roles: " . implode(', ', $data['roles']));
        }
        
        if ($data['permissions']) {
            $this->line("Permissions: " . implode(', ', $data['permissions']));
        }

        $this->newLine();
        $this->line('<fg=yellow>Next Steps:</>');
        $this->line("1. Run: php artisan db:seed --class={$dashboardType->type}DatabaseSeeder");
        $this->line("2. Visit: /{$dashboardType->type}/login");
        $this->line("3. API Base URL: /api/{$dashboardType->type}");
        $this->line("4. Documentation: docs/api/{$dashboardType->type}.md");
    }

    /**
     * Generate seeder content
     */
    protected function generateSeederContent(string $type, array $data): string
    {
        $studlyType = Str::studly($type);
        $roles = implode("', '", $data['roles']);
        $permissions = implode("', '", $data['permissions']);
        
        return <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\\{$type}\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class {$studlyType}DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        \$permissions = ['{$permissions}'];
        foreach (\$permissions as \$permission) {
            Permission::create([
                'name' => \$permission,
                'guard_name' => '{$type}'
            ]);
        }

        // Create roles
        \$roles = ['{$roles}'];
        foreach (\$roles as \$roleName) {
            \$role = Role::create([
                'name' => \$roleName,
                'guard_name' => '{$type}'
            ]);
            
            // Assign all permissions to admin role
            if (\$roleName === 'admin') {
                \$role->givePermissionTo(\$permissions);
            }
        }

        // Create default admin user
        \$admin = User::create([
            'name' => '{$studlyType} Administrator',
            'email' => 'admin@{$type}.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        \$admin->assignRole('admin');
    }
}
PHP;
    }

    /**
     * Generate API documentation content
     */
    protected function generateApiDocumentationContent(string $type): string
    {
        $studlyType = Str::studly($type);
        
        return <<<MD
# {$studlyType} Dashboard API Documentation

## Authentication

### Login
**POST** `/api/{$type}/login`

```json
{
    "email": "user@example.com",
    "password": "password"
}
```

### Register
**POST** `/api/{$type}/register`

```json
{
    "name": "Full Name",
    "email": "user@example.com",
    "phone": "9843223774",
    "password": "password",
    "password_confirmation": "password"
}
```

### Get User Profile
**GET** `/api/{$type}/me`

Headers:
```
Authorization: Bearer <token>
```

### Logout
**POST** `/api/{$type}/logout`

Headers:
```
Authorization: Bearer <token>
```

## Dashboard Endpoints

### Get Dashboard Stats
**GET** `/api/{$type}/dashboard/stats`

Headers:
```
Authorization: Bearer <token>
```

## User Management

### List Users
**GET** `/api/{$type}/users`

### Create User
**POST** `/api/{$type}/users`

### Update User
**PUT** `/api/{$type}/users/{id}`

### Delete User
**DELETE** `/api/{$type}/users/{id}`

## Role Management

### List Roles
**GET** `/api/{$type}/roles`

### Create Role
**POST** `/api/{$type}/roles`

### Update Role
**PUT** `/api/{$type}/roles/{id}`

### Delete Role
**DELETE** `/api/{$type}/roles/{id}`

## Error Responses

All endpoints return errors in the following format:

```json
{
    "error": true,
    "message": "Error description",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

## Rate Limiting

API endpoints are rate limited to:
- 60 requests per minute for authenticated users
- 10 requests per minute for unauthenticated users

## Authentication Methods

This dashboard supports the following authentication methods:
- Email/Password
- SMS OTP (if enabled)
- JWT tokens for API access
MD;
    }
}