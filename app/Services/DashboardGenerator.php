<?php

namespace App\Services;

use App\Models\DashboardType;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class DashboardGenerator
{
    protected $type;
    protected $studlyType;
    protected $config;

    public function __construct()
    {
        //
    }

    /**
     * Generate complete dashboard system
     */
    public function generate(array $config): DashboardType
    {
        $this->type = $config['type'];
        $this->studlyType = Str::studly($this->type);
        $this->config = $config;

        // Create dashboard type record
        $dashboardType = $this->createDashboardTypeRecord();

        // Setup database strategy
        $this->setupDatabaseStrategy();

        // Generate all files
        $this->generateAllFiles();

        // Run migrations
        $this->runMigrations();

        // Create default data
        $this->seedDefaultData();

        return $dashboardType;
    }

    /**
     * Create dashboard type record
     */
    protected function createDashboardTypeRecord(): DashboardType
    {
        return DashboardType::create([
            'type' => $this->type,
            'name' => $this->config['name'],
            'description' => $this->config['description'],
            'database_strategy' => $this->config['database_strategy'],
            'database_config' => $this->config['database_config'],
            'auth_methods' => $this->config['auth_methods'],
            'theme_config' => $this->config['theme_config'],
            'settings' => $this->config['settings'],
            'has_landing_page' => $this->config['has_landing_page'],
            'is_active' => true,
        ]);
    }

    /**
     * Setup database strategy
     */
    protected function setupDatabaseStrategy(): void
    {
        if ($this->config['database_strategy'] === 'separate') {
            $this->setupSeparateDatabase();
        } else {
            $this->setupSharedDatabase();
        }
    }

    /**
     * Setup separate database connection
     */
    protected function setupSeparateDatabase(): void
    {
        $dbConfig = $this->config['database_config'];

        // Create database configuration file
        $configPath = config_path("database_connections/{$this->type}.php");

        if (!is_dir(dirname($configPath))) {
            mkdir(dirname($configPath), 0755, true);
        }

        $configContent = "<?php\n\nreturn " . var_export([
            'connection_name' => $dbConfig['connection_name'],
            'config' => [
                'driver' => 'mysql',
                'host' => $dbConfig['host'],
                'port' => $dbConfig['port'] ?? 3306,
                'database' => $dbConfig['database'],
                'username' => $dbConfig['username'],
                'password' => $dbConfig['password'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]
        ], true) . ";\n";

        file_put_contents($configPath, $configContent);

        // Update main database config
        $this->updateMainDatabaseConfig();
    }

    /**
     * Setup shared database with prefix
     */
    protected function setupSharedDatabase(): void
    {
        // No additional setup needed for shared database
        // Prefix will be handled in model generation
    }

    /**
     * Generate all necessary files
     */
    protected function generateAllFiles(): void
    {
        $this->createDirectories();
        $this->generateModels();
        $this->generateControllers();
        $this->generateMigrations();
        $this->generateRoutes();
        $this->generateReactComponents();
        $this->generateMiddleware();

        if ($this->config['has_landing_page']) {
            $this->generateLandingPage();
        }
    }

    /**
     * Create necessary directories
     */
    protected function createDirectories(): void
    {
        $directories = [
            app_path("Models/{$this->studlyType}"),
            app_path("Http/Controllers/{$this->studlyType}"),
            app_path("Http/Controllers/Api/{$this->studlyType}"),
            app_path("Http/Middleware/{$this->studlyType}"),
            resource_path("js/Pages/{$this->studlyType}"),
            resource_path("js/Pages/{$this->studlyType}/Auth"),
            resource_path("js/Components/{$this->studlyType}"),
            resource_path("js/Layouts/{$this->studlyType}"),
            database_path("seeders/{$this->studlyType}"),
        ];

        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
        }
    }

    /**
     * Generate models based on database strategy and user system
     */
    protected function generateModels(): void
    {
        // User model
        $this->generateUserModel();

        // Role and permission models only if role-based system
        if ($this->config['user_system'] === 'roles') {
            $this->generateRoleModel();
            $this->generatePermissionModel();
        }
    }

    /**
     * Generate user model
     */
    protected function generateUserModel(): void
    {
        $connection = $this->config['database_strategy'] === 'separate'
            ? $this->config['database_config']['connection_name']
            : 'mysql';

        $tableName = $this->config['database_strategy'] === 'shared'
            ? $this->config['database_config']['prefix'] . 'users'
            : 'users';

        $modelContent = $this->getUserModelTemplate($connection, $tableName);

        File::put(
            app_path("Models/{$this->studlyType}/User.php"),
            $modelContent
        );
    }

    /**
     * Get user model template
     */
    protected function getUserModelTemplate(string $connection, string $tableName): string
    {
        $authMethods = implode("', '", $this->config['auth_methods']);
        $hasRoles = $this->config['user_system'] === 'roles';

        $rolesImports = $hasRoles ? "use Spatie\\Permission\\Traits\\HasRoles;\nuse Spatie\\Permission\\Models\\Role;\nuse Spatie\\Permission\\Models\\Permission;" : '';
        $rolesTraits = $hasRoles ? ', HasRoles' : '';
        $guardName = $hasRoles ? "\n    protected \$guard_name = '{$this->type}';" : '';

        return <<<PHP
<?php

namespace App\\Models\\{$this->studlyType};

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Foundation\\Auth\\User as Authenticatable;
use Illuminate\\Notifications\\Notifiable;
use Laravel\\Sanctum\\HasApiTokens;
use Spatie\\Activitylog\\Traits\\LogsActivity;
use Spatie\\Activitylog\\LogOptions;
{$rolesImports}

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity{$rolesTraits};

    protected \$connection = '{$connection}';
    protected \$table = '{$tableName}';{$guardName}

    protected \$fillable = [
        'name',
        'email',
        'phone',
        'password',
        'dashboard_type',
        'is_active',
        'email_verified_at',
        'phone_verified_at',
        'profile_data',
        'last_login_at',
    ];

    protected \$hidden = [
        'password',
        'remember_token',
    ];

    protected \$casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'profile_data' => 'array',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Scope for dashboard type (shared database only)
     */
    public function scopeDashboardType(\$query, \$type = '{$this->type}')
    {
        if ('{$this->config['database_strategy']}' === 'shared') {
            return \$query->where('dashboard_type', \$type);
        }
        return \$query;
    }

    /**
     * Get validation rules
     */
    public static function getValidationRules(\$id = null): array
    {
        \$table = (new static)->getTable();
        \$uniqueEmail = "unique:{\$table},email" . (\$id ? ",{\$id}" : "");
        \$uniquePhone = "unique:{\$table},phone" . (\$id ? ",{\$id}" : "");
        
        if ('{$this->config['database_strategy']}' === 'shared') {
            \$uniqueEmail .= ",id,dashboard_type,{$this->type}";
            \$uniquePhone .= ",id,dashboard_type,{$this->type}";
        }

        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', \$uniqueEmail],
            'phone' => ['nullable', 'string', \$uniquePhone],
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * Check if SMS auth is enabled
     */
    public function canUseSmsAuth(): bool
    {
        return in_array('sms', ['{$authMethods}']);
    }

    /**
     * Check if email auth is enabled  
     */
    public function canUseEmailAuth(): bool
    {
        return in_array('email', ['{$authMethods}']);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        \$this->update(['last_login_at' => now()]);
    }
}
PHP;
    }

    /**
     * Generate controllers
     */
    protected function generateControllers(): void
    {
        $this->generateAuthController();
        $this->generateDashboardController();
        $this->generateApiAuthController();

        if ($this->config['user_system'] === 'roles') {
            $this->generateUserController();
        }
    }

    /**
     * Generate auth controller
     */
    protected function generateAuthController(): void
    {
        $authMethods = $this->config['auth_methods'];
        $hasSms = in_array('sms', $authMethods);
        $hasEmail = in_array('email', $authMethods);

        $smsImports = $hasSms ? "use App\\Services\\AakashSmsService;" : '';
        $smsMethod = $hasSms ? $this->getSmsAuthMethods() : '';

        $controllerContent = <<<PHP
<?php

namespace App\\Http\\Controllers\\{$this->studlyType};

use App\\Http\\Controllers\\Controller;
use App\\Models\\{$this->studlyType}\\User;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Auth;
use Illuminate\\Support\\Facades\\Hash;
use Illuminate\\Validation\\ValidationException;
use Inertia\\Inertia;
{$smsImports}

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('{$this->studlyType}/Auth/Login', [
            'dashboardType' => '{$this->type}',
            'authMethods' => ['{$this->implodeAuthMethods()}'],
            'hasLandingPage' => '{$this->getHasLandingPageString()}',
        ]);
    }

    public function login(Request \$request)
    {
        \$request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        \$query = User::where('email', \$request->email);
        
        // Add dashboard type scope for shared database
        if ('{$this->config['database_strategy']}' === 'shared') {
            \$query->dashboardType('{$this->type}');
        }

        \$user = \$query->first();

        if (!\$user || !Hash::check(\$request->password, \$user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!\$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is deactivated.'],
            ]);
        }

        Auth::guard('{$this->type}')->login(\$user, \$request->boolean('remember'));
        \$user->updateLastLogin();

        \$request->session()->regenerate();

        return redirect()->intended(route('{$this->type}.dashboard'));
    }

    public function logout(Request \$request)
    {
        Auth::guard('{$this->type}')->logout();
        \$request->session()->invalidate();
        \$request->session()->regenerateToken();

        return redirect()->route('{$this->type}.login');
    }

    {$smsMethod}
}
PHP;

        File::put(
            app_path("Http/Controllers/{$this->studlyType}/AuthController.php"),
            $controllerContent
        );
    }

    /**
     * Get SMS auth methods
     */
    protected function getSmsAuthMethods(): string
    {
        return <<<PHP
    
    /**
     * Send SMS OTP
     */
    public function sendSmsOtp(Request \$request)
    {
        \$request->validate([
            'phone' => 'required|string',
        ]);

        \$query = User::where('phone', \$request->phone);
        
        if ('{$this->config['database_strategy']}' === 'shared') {
            \$query->dashboardType('{$this->type}');
        }

        \$user = \$query->first();

        if (!\$user) {
            throw ValidationException::withMessages([
                'phone' => ['No account found with this phone number.'],
            ]);
        }

        if (!\$user->is_active) {
            throw ValidationException::withMessages([
                'phone' => ['Your account is deactivated.'],
            ]);
        }

        // Generate OTP
        \$otp = random_int(100000, 999999);
        
        // Store OTP in session (in production, use cache with expiry)
        session(['sms_otp_' . \$user->id => \$otp, 'sms_otp_user_id' => \$user->id]);

        // Send SMS
        try {
            \$smsService = app(AakashSmsService::class);
            \$smsService->sendOtp(\$user->phone, \$otp);
            
            return response()->json(['message' => 'OTP sent successfully']);
        } catch (\\Exception \$e) {
            return response()->json(['message' => 'Failed to send OTP'], 500);
        }
    }

    /**
     * Verify SMS OTP and login
     */
    public function verifySmsOtp(Request \$request)
    {
        \$request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string',
        ]);

        \$userId = session('sms_otp_user_id');
        \$storedOtp = session('sms_otp_' . \$userId);

        if (!\$userId || !\$storedOtp || \$storedOtp !== \$request->otp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP code.'],
            ]);
        }

        \$user = User::find(\$userId);

        if (!\$user || \$user->phone !== \$request->phone) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP code.'],
            ]);
        }

        // Clear OTP from session
        session()->forget(['sms_otp_' . \$userId, 'sms_otp_user_id']);

        // Login user
        Auth::guard('{$this->type}')->login(\$user, true);
        \$user->updateLastLogin();

        \$request->session()->regenerate();

        return redirect()->intended(route('{$this->type}.dashboard'));
    }
PHP;
    }

    /**
     * Implode auth methods for template
     */
    protected function implodeAuthMethods(): string
    {
        return implode("', '", $this->config['auth_methods']);
    }
    private function getHasLandingPageString()
    {
        return $this->config['has_landing_page'] ? 'true' : 'false';
    }
    /**
     * Generate migrations
     */
    protected function generateMigrations(): void
    {
        $timestamp = date('Y_m_d_His');

        // Users table migration
        $this->generateUsersMigration($timestamp);

        // Roles and permissions migrations (only for role-based systems)
        if ($this->config['user_system'] === 'roles') {
            $this->generateRolesMigration($timestamp + 1);
        }
    }

    /**
     * Generate users migration
     */
    protected function generateUsersMigration(string $timestamp): void
    {
        $migrationContent = $this->getUsersMigrationTemplate();

        File::put(
            database_path("migrations/{$timestamp}_create_{$this->type}_users_table.php"),
            $migrationContent
        );
    }

    /**
     * Get users migration template
     */
    protected function getUsersMigrationTemplate(): string
    {
        $connection = $this->config['database_strategy'] === 'separate'
            ? "'{$this->config['database_config']['connection_name']}'" : 'null';

        $tableName = $this->config['database_strategy'] === 'shared'
            ? $this->config['database_config']['prefix'] . 'users'
            : 'users';

        $dashboardTypeColumn = $this->config['database_strategy'] === 'shared'
            ? "\$table->string('dashboard_type', 50)->default('{$this->type}');" : '';

        $uniqueConstraints = $this->config['database_strategy'] === 'shared'
            ? "// Unique constraints within dashboard type\n            \$table->unique(['email', 'dashboard_type']);\n            \$table->unique(['phone', 'dashboard_type']);"
            : "// Standard unique constraints\n            \$table->unique('email');\n            \$table->unique('phone');";

        return <<<PHP
<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \$connection = {$connection};
        
        Schema::connection(\$connection)->create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->string('email');
            \$table->string('phone')->nullable();
            \$table->timestamp('email_verified_at')->nullable();
            \$table->timestamp('phone_verified_at')->nullable();
            \$table->string('password');
            {$dashboardTypeColumn}
            \$table->boolean('is_active')->default(true);
            \$table->json('profile_data')->nullable();
            \$table->timestamp('last_login_at')->nullable();
            \$table->rememberToken();
            \$table->timestamps();

            {$uniqueConstraints}
            
            // Indexes for performance
            \$table->index(['email', 'is_active']);
            \$table->index(['phone', 'is_active']);
        });
    }

    public function down(): void
    {
        \$connection = {$connection};
        Schema::connection(\$connection)->dropIfExists('{$tableName}');
    }
};
PHP;
    }

    /**
     * Run migrations
     */
    protected function runMigrations(): void
    {
        if ($this->config['database_strategy'] === 'separate') {
            Artisan::call('migrate', [
                '--database' => $this->config['database_config']['connection_name']
            ]);
        } else {
            Artisan::call('migrate');
        }
    }

    /**
     * Seed default data
     */
    protected function seedDefaultData(): void
    {
        $this->createDefaultUser();

        if ($this->config['user_system'] === 'roles') {
            $this->createDefaultRoles();
        }
    }

    /**
     * Create default admin user
     */
    protected function createDefaultUser(): void
    {
        $userClass = "App\\Models\\{$this->studlyType}\\User";

        $userData = [
            'name' => ucfirst($this->type) . ' Admin',
            'email' => "admin@{$this->type}.com",
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ];

        if ($this->config['database_strategy'] === 'shared') {
            $userData['dashboard_type'] = $this->type;
        }

        $userClass::create($userData);
    }

    /**
     * Generate routes
     */
    protected function generateRoutes(): void
    {
        $this->generateWebRoutes();
        $this->generateApiRoutes();
    }

    /**
     * Generate web routes
     */
    protected function generateWebRoutes(): void
    {
        $hasLandingPage = $this->config['has_landing_page'];
        $landingRoute = $hasLandingPage ? "Route::get('/', [LandingController::class, 'index'])->name('landing');" : '';

        $routesContent = <<<PHP
<?php

use App\\Http\\Controllers\\{$this->studlyType}\\AuthController;
use App\\Http\\Controllers\\{$this->studlyType}\\DashboardController;
use Illuminate\\Support\\Facades\\Route;

Route::prefix('{$this->type}')->name('{$this->type}.')->group(function () {
    {$landingRoute}
    
    // Authentication Routes
    Route::middleware('guest:{$this->type}')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login']);
        Route::post('send-sms-otp', [AuthController::class, 'sendSmsOtp'])->name('send-sms-otp');
        Route::post('verify-sms-otp', [AuthController::class, 'verifySmsOtp'])->name('verify-sms-otp');
    });

    // Authenticated Routes
    Route::middleware('auth:{$this->type}')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('profile', [DashboardController::class, 'profile'])->name('profile');
        Route::put('profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
    });
});
PHP;

        File::put(base_path("routes/{$this->type}.php"), $routesContent);

        // Update main web.php to include new routes
        $webRoutesPath = base_path('routes/web.php');
        $webContent = file_get_contents($webRoutesPath);

        $includeStatement = "require __DIR__.'/{$this->type}.php';";

        if (strpos($webContent, $includeStatement) === false) {
            file_put_contents($webRoutesPath, "\n{$includeStatement}\n", FILE_APPEND);
        }
    }

    // Continue with more methods...
    protected function generateDashboardController(): void
    {
        $controllerContent = <<<PHP
<?php

namespace App\\Http\\Controllers\\{$this->studlyType};

use App\\Http\\Controllers\\Controller;
use App\\Models\\{$this->studlyType}\\User;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Auth;
use Illuminate\\Support\\Facades\\Hash;
use Inertia\\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        \$user = Auth::guard('{$this->type}')->user();
        
        \$stats = [
            'total_users' => User::dashboardType('{$this->type}')->count(),
            'active_users' => User::dashboardType('{$this->type}')->where('is_active', true)->count(),
            'recent_logins' => User::dashboardType('{$this->type}')->whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subDays(7))->count(),
        ];

        return Inertia::render('{$this->studlyType}/Dashboard', [
            'user' => \$user,
            'stats' => \$stats,
            'dashboardType' => '{$this->type}',
        ]);
    }

    public function profile()
    {
        \$user = Auth::guard('{$this->type}')->user();

        return Inertia::render('{$this->studlyType}/Profile', [
            'user' => \$user,
        ]);
    }

    public function updateProfile(Request \$request)
    {
        \$user = Auth::guard('{$this->type}')->user();
        
        \$validated = \$request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:' . \$user->getTable() . ',email,' . \$user->id,
            'phone' => 'nullable|string|unique:' . \$user->getTable() . ',phone,' . \$user->id,
            'profile_data' => 'nullable|array',
        ]);

        \$user->update(\$validated);

        return back()->with('success', 'Profile updated successfully.');
    }
}
PHP;

        File::put(
            app_path("Http/Controllers/{$this->studlyType}/DashboardController.php"),
            $controllerContent
        );
    }

    /**
     * Generate API routes
     */
    protected function generateApiRoutes(): void
    {
        $apiRoutesContent = <<<PHP
            <?php

            use App\\Http\\Controllers\\Api\\{$this->studlyType}\\AuthController;
            use Illuminate\\Http\\Middleware\\ThrottleRequests;
            use Illuminate\\Support\\Facades\\Route;

            Route::prefix('api/{$this->type}')->name('api.{$this->type}.')->group(function () {
                // Public API routes
                Route::middleware(['throttle:60,1'])->group(function () {
                    Route::post('login', [AuthController::class, 'login'])->name('login');
                    Route::post('send-otp', [AuthController::class, 'sendOtp'])->name('send-otp');
                    Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp');
                });

                // Protected API routes
                Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
                    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
                    Route::get('user', [AuthController::class, 'user'])->name('user');
                    Route::put('user/profile', [AuthController::class, 'updateProfile'])->name('user.profile');
                });
            });
            PHP;

        File::put(base_path("routes/api/{$this->type}.php"), $apiRoutesContent);

        // Update main api.php
        $apiRoutesPath = base_path('routes/api.php');
        $apiContent = file_get_contents($apiRoutesPath);

        $includeStatement = "require __DIR__.'/api/{$this->type}.php';";

        if (strpos($apiContent, $includeStatement) === false) {
            file_put_contents($apiRoutesPath, "\n{$includeStatement}\n", FILE_APPEND);
        }
    }

    /**
     * Generate React components
     */
    protected function generateReactComponents(): void
    {
        $this->generateAuthComponents();
        $this->generateDashboardComponents();
        $this->generateLayoutComponents();

        if ($this->config['has_landing_page']) {
            $this->generateLandingComponent();
        }
    }
    /**
     * Generate landing page component (if requested)
     */
    protected function generateLandingComponent(): void
    {
        $landingContent = $this->getLandingPageTemplate();
        File::put(
            resource_path("js/Pages/{$this->studlyType}/Landing.jsx"),
            $landingContent
        );
    }

    /**
     * Get landing page template
     */
    protected function getLandingPageTemplate(): string
    {
        $primary = $this->config['theme_config']['primary'];
        $accent = $this->config['theme_config']['accent'] ?? $primary;

        return <<<JSX
            import { Head, Link } from '@inertiajs/react';
            import { 
                ChevronRightIcon,
                ShieldCheckIcon,
                UserGroupIcon,
                ChartBarIcon,
                CogIcon
            } from '@heroicons/react/24/outline';

            export default function Landing() {
                const features = [
                    {
                        name: 'Secure Access',
                        description: 'Enterprise-grade security with multi-factor authentication options.',
                        icon: ShieldCheckIcon,
                    },
                    {
                        name: 'User Management',
                        description: 'Comprehensive user management with role-based access control.',
                        icon: UserGroupIcon,
                    },
                    {
                        name: 'Analytics Dashboard',
                        description: 'Real-time analytics and reporting for data-driven decisions.',
                        icon: ChartBarIcon,
                    },
                    {
                        name: 'Easy Configuration',
                        description: 'Simple and intuitive configuration management interface.',
                        icon: CogIcon,
                    },
                ];

                return (
                    <>
                        <Head title="{\`\${'{$this->type}'.charAt(0).toUpperCase() + '{$this->type}'.slice(1)} Portal\`}" />

                        <div className="min-h-screen bg-white">
                            {/* Navigation */}
                            <nav className="bg-white shadow">
                                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                                    <div className="flex justify-between h-16">
                                        <div className="flex items-center">
                                            <div className="flex-shrink-0 flex items-center">
                                                <div className="h-10 w-10 rounded-lg flex items-center justify-center" style={{ backgroundColor: '{$primary}' }}>
                                                    <span className="text-white font-bold text-lg">
                                                        {\`\${'{$this->type}'.charAt(0).toUpperCase()}\${'{$this->type}'.charAt(1).toUpperCase()}\`}
                                                    </span>
                                                </div>
                                                <span className="ml-2 text-xl font-bold text-gray-900">
                                                    {\`\${'{$this->type}'.charAt(0).toUpperCase() + '{$this->type}'.slice(1)} Portal\`}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-4">
                                            <Link
                                                href={route('{$this->type}.login')}
                                                className="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                                            >
                                                Sign In
                                            </Link>
                                            <Link
                                                href={route('{$this->type}.login')}
                                                className="text-white px-4 py-2 rounded-md text-sm font-medium"
                                                style={{ backgroundColor: '{$primary}' }}
                                            >
                                                Get Started
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </nav>

                            {/* Hero Section */}
                            <div className="relative bg-white overflow-hidden">
                                <div className="max-w-7xl mx-auto">
                                    <div className="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                                        <main className="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                                            <div className="sm:text-center lg:text-left">
                                                <h1 className="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                                                    <span className="block xl:inline">
                                                        {\`\${'{$this->type}'.charAt(0).toUpperCase() + '{$this->type}'.slice(1)}\`}
                                                    </span>{' '}
                                                    <span className="block text-primary-600 xl:inline">Dashboard System</span>
                                                </h1>
                                                <p className="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                                                    A comprehensive management platform designed specifically for {'{$this->type}'} professionals. 
                                                    Streamline your workflow with our intuitive dashboard and powerful features.
                                                </p>
                                                <div className="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                                                    <div className="rounded-md shadow">
                                                        <Link
                                                            href={route('{$this->type}.login')}
                                                            className="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white md:py-4 md:text-lg md:px-10"
                                                            style={{ backgroundColor: '{$primary}' }}
                                                        >
                                                            Get Started
                                                            <ChevronRightIcon className="ml-2 h-5 w-5" />
                                                        </Link>
                                                    </div>
                                                    <div className="mt-3 sm:mt-0 sm:ml-3">
                                                        <button className="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 md:py-4 md:text-lg md:px-10">
                                                            Learn More
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </main>
                                    </div>
                                </div>
                                <div className="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
                                    <div className="h-56 w-full bg-gradient-to-r from-primary-400 to-primary-600 sm:h-72 md:h-96 lg:w-full lg:h-full flex items-center justify-center">
                                        <div className="text-white text-8xl font-bold opacity-20">
                                            {\`\${'{$this->type}'.charAt(0).toUpperCase()}\${'{$this->type}'.charAt(1).toUpperCase()}\`}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Features Section */}
                            <div className="py-12 bg-white">
                                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                                    <div className="lg:text-center">
                                        <h2 className="text-base text-primary-600 font-semibold tracking-wide uppercase">Features</h2>
                                        <p className="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                                            Everything you need to manage your {'{$this->type}'} operations
                                        </p>
                                    </div>

                                    <div className="mt-10">
                                        <div className="space-y-10 md:space-y-0 md:grid md:grid-cols-2 md:gap-x-8 md:gap-y-10">
                                            {features.map((feature) => (
                                                <div key={feature.name} className="relative">
                                                    <div className="absolute flex items-center justify-center h-12 w-12 rounded-md text-white" style={{ backgroundColor: '{$primary}' }}>
                                                        <feature.icon className="h-6 w-6" aria-hidden="true" />
                                                    </div>
                                                    <p className="ml-16 text-lg leading-6 font-medium text-gray-900">{feature.name}</p>
                                                    <p className="mt-2 ml-16 text-base text-gray-500">{feature.description}</p>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* CTA Section */}
                            <div className="bg-primary-50">
                                <div className="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8 lg:flex lg:items-center lg:justify-between">
                                    <h2 className="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                                        <span className="block">Ready to get started?</span>
                                        <span className="block text-primary-600">Access your dashboard today.</span>
                                    </h2>
                                    <div className="mt-8 flex lg:mt-0 lg:flex-shrink-0">
                                        <div className="inline-flex rounded-md shadow">
                                            <Link
                                                href={route('{$this->type}.login')}
                                                className="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white"
                                                style={{ backgroundColor: '{$primary}' }}
                                            >
                                                Sign In Now
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Footer */}
                            <footer className="bg-white border-t border-gray-200">
                                <div className="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                                    <div className="text-center text-sm text-gray-500">
                                        © 2025 {\`\${'{$this->type}'.charAt(0).toUpperCase() + '{$this->type}'.slice(1)} Portal\`}. All rights reserved.
                                    </div>
                                </div>
                            </footer>
                        </div>
                    </>
                );
            }
            JSX;
    }

    /**
     * Generate layout components
     */
    protected function generateLayoutComponents(): void
    {
        $layoutContent = $this->getDashboardLayoutTemplate();
        File::put(
            resource_path("js/Layouts/{$this->studlyType}/DashboardLayout.jsx"),
            $layoutContent
        );
    }
    /**
     * Generate authentication components
     */
    protected function generateAuthComponents(): void
    {
        // Login component
        $loginContent = $this->getLoginComponentTemplate();
        File::put(
            resource_path("js/Pages/{$this->studlyType}/Auth/Login.jsx"),
            $loginContent
        );
    }

    /**
     * Generate dashboard components
     */
    protected function generateDashboardComponents(): void
    {
        // Main dashboard
        $dashboardContent = $this->getDashboardComponentTemplate();
        File::put(
            resource_path("js/Pages/{$this->studlyType}/Dashboard.jsx"),
            $dashboardContent
        );

        // Profile page
        $profileContent = $this->getProfileComponentTemplate();
        File::put(
            resource_path("js/Pages/{$this->studlyType}/Profile.jsx"),
            $profileContent
        );
    }

    /**
     * Generate profile component template
     */
    protected function getProfileComponentTemplate(): string
    {
        return <<<JSX
            import { Head, useForm } from '@inertiajs/react';
            import { UserIcon, EnvelopeIcon, PhoneIcon } from '@heroicons/react/24/outline';
            import DashboardLayout from '@/Layouts/{$this->studlyType}/DashboardLayout';

            export default function Profile({ user }) {
                const { data, setData, put, processing, errors, reset } = useForm({
                    name: user.name || '',
                    email: user.email || '',
                    phone: user.phone || '',
                    profile_data: user.profile_data || {},
                });

                const submit = (e) => {
                    e.preventDefault();
                    put(route('{$this->type}.profile.update'), {
                        onSuccess: () => reset(),
                    });
                };

                return (
                    <DashboardLayout user={user}>
                        <Head title="Profile" />

                        <div className="max-w-2xl mx-auto space-y-6">
                            {/* Header */}
                            <div className="bg-white shadow rounded-lg p-6">
                                <div className="flex items-center">
                                    <div className="flex-shrink-0">
                                        <div className="h-16 w-16 bg-primary-100 rounded-lg flex items-center justify-center">
                                            <UserIcon className="h-8 w-8 text-primary-600" />
                                        </div>
                                    </div>
                                    <div className="ml-4">
                                        <h1 className="text-2xl font-bold text-gray-900">Profile Settings</h1>
                                        <p className="text-gray-600">Manage your account information and preferences</p>
                                    </div>
                                </div>
                            </div>

                            {/* Profile Form */}
                            <div className="bg-white shadow rounded-lg p-6">
                                <form onSubmit={submit} className="space-y-6">
                                    <div>
                                        <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                            Full Name
                                        </label>
                                        <div className="mt-1 relative">
                                            <input
                                                id="name"
                                                name="name"
                                                type="text"
                                                required
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                                className="block w-full px-3 py-2 pl-10 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                                placeholder="Enter your full name"
                                            />
                                            <UserIcon className="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                                        </div>
                                        {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                                    </div>

                                    <div>
                                        <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                                            Email Address
                                        </label>
                                        <div className="mt-1 relative">
                                            <input
                                                id="email"
                                                name="email"
                                                type="email"
                                                required
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                className="block w-full px-3 py-2 pl-10 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                                placeholder="Enter your email address"
                                            />
                                            <EnvelopeIcon className="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                                        </div>
                                        {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
                                    </div>

                                    <div>
                                        <label htmlFor="phone" className="block text-sm font-medium text-gray-700">
                                            Phone Number
                                        </label>
                                        <div className="mt-1 relative">
                                            <input
                                                id="phone"
                                                name="phone"
                                                type="tel"
                                                value={data.phone}
                                                onChange={(e) => setData('phone', e.target.value)}
                                                className="block w-full px-3 py-2 pl-10 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                                placeholder="Enter your phone number"
                                            />
                                            <PhoneIcon className="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                                        </div>
                                        {errors.phone && <p className="mt-1 text-sm text-red-600">{errors.phone}</p>}
                                    </div>

                                    <div className="flex justify-end">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="btn-primary"
                                        >
                                            {processing ? 'Saving...' : 'Save Changes'}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {/* Account Information */}
                            <div className="bg-white shadow rounded-lg p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span className="text-gray-500">Account Created:</span>
                                        <span className="ml-2 text-gray-900">
                                            {new Date(user.created_at).toLocaleDateString()}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="text-gray-500">Last Login:</span>
                                        <span className="ml-2 text-gray-900">
                                            {user.last_login_at 
                                                ? new Date(user.last_login_at).toLocaleDateString()
                                                : 'Never'
                                            }
                                        </span>
                                    </div>
                                    <div>
                                        <span className="text-gray-500">Account Status:</span>
                                        <span className={\`ml-2 \${user.is_active ? 'text-green-600' : 'text-red-600'}\`}>
                                            {user.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="text-gray-500">Email Verified:</span>
                                        <span className={\`ml-2 \${user.email_verified_at ? 'text-green-600' : 'text-yellow-600'}\`}>
                                            {user.email_verified_at ? 'Verified' : 'Pending'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </DashboardLayout>
                );
            }
            JSX;
    }
    /**
     * Get dashboard component template
     */
    protected function getDashboardComponentTemplate(): string
    {
        $hasRoles = $this->config['user_system'] === 'roles';
        $roleSection = $hasRoles ? $this->getRoleBasedDashboardSection() : $this->getSingleUserDashboardSection();

        return <<<JSX
        import { Head } from '@inertiajs/react';
        import { 
            UserGroupIcon, 
            ChartBarIcon, 
            ClockIcon,
            TrendingUpIcon
        } from '@heroicons/react/24/outline';
        import DashboardLayout from '@/Layouts/{$this->studlyType}/DashboardLayout';
        import StatsCard from '@/Components/{$this->studlyType}/StatsCard';

        export default function Dashboard({ user, stats, dashboardType }) {
            return (
                <DashboardLayout user={user}>
                    <Head title="Dashboard" />

                    <div className="space-y-6">
                        {/* Welcome Header */}
                        <div className="bg-white shadow rounded-lg p-6">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className="h-12 w-12 bg-primary-100 rounded-lg flex items-center justify-center">
                                        <span className="text-primary-600 font-bold text-xl">
                                            {\`\${user.name.charAt(0)}\`}
                                        </span>
                                    </div>
                                </div>
                                <div className="ml-4">
                                    <h1 className="text-2xl font-bold text-gray-900">
                                        Welcome back, {user.name}!
                                    </h1>
                                    <p className="text-gray-600">
                                        {\`\${dashboardType.charAt(0).toUpperCase() + dashboardType.slice(1)} Dashboard\`}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Stats Grid */}
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <StatsCard
                                title="Total Users"
                                value={stats.total_users}
                                icon={UserGroupIcon}
                                color="primary"
                                trend="+12%"
                            />
                            <StatsCard
                                title="Active Users"
                                value={stats.active_users}
                                icon={TrendingUpIcon}
                                color="green"
                                trend="+5%"
                            />
                            <StatsCard
                                title="Recent Activity"
                                value={stats.recent_logins}
                                icon={ClockIcon}
                                color="blue"
                                trend="This week"
                            />
                        </div>

                        {/* Quick Actions */}
                        <div className="bg-white shadow rounded-lg p-6">
                            <h2 className="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <button className="p-4 border-2 border-dashed border-gray-200 rounded-lg hover:border-primary-300 hover:bg-primary-50 transition-colors text-left">
                                    <ChartBarIcon className="h-8 w-8 text-gray-400 mb-2" />
                                    <h3 className="font-medium text-gray-900">View Analytics</h3>
                                    <p className="text-sm text-gray-500">Check system performance</p>
                                </button>
                                
                                <button className="p-4 border-2 border-dashed border-gray-200 rounded-lg hover:border-primary-300 hover:bg-primary-50 transition-colors text-left">
                                    <UserGroupIcon className="h-8 w-8 text-gray-400 mb-2" />
                                    <h3 className="font-medium text-gray-900">Manage Users</h3>
                                    <p className="text-sm text-gray-500">Add or edit users</p>
                                </button>
                                
                                <button className="p-4 border-2 border-dashed border-gray-200 rounded-lg hover:border-primary-300 hover:bg-primary-50 transition-colors text-left">
                                    <ClockIcon className="h-8 w-8 text-gray-400 mb-2" />
                                    <h3 className="font-medium text-gray-900">Recent Activity</h3>
                                    <p className="text-sm text-gray-500">View system logs</p>
                                </button>
                            </div>
                        </div>

                        {$roleSection}
                    </div>
                </DashboardLayout>
            );
        }
        JSX;
    }
    /**
     * Get single user dashboard section
     */
    protected function getSingleUserDashboardSection(): string
    {
        return <<<JSX

                {/* Personal Dashboard Section */}
                <div className="bg-white shadow rounded-lg p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">Personal Tools</h2>
                    <div className="text-center py-8">
                        <ChartBarIcon className="mx-auto h-12 w-12 text-gray-400" />
                        <h3 className="mt-4 text-lg font-medium text-gray-900">Your Personal Dashboard</h3>
                        <p className="mt-2 text-gray-500">
                            This is your personal workspace. Add your custom content and tools here.
                        </p>
                    </div>
                </div>
                JSX;
    }

    /**
     * Get login component template
     */
    protected function getLoginComponentTemplate(): string
    {
        $hasSms = in_array('sms', $this->config['auth_methods']);
        $hasEmail = in_array('email', $this->config['auth_methods']);
        // $smsAuthSection = $hasSms ? $this->getSmsAuthSection() : '';

        return <<<JSX
        import { useState } from 'react';
        import { Head, useForm } from '@inertiajs/react';
        import { EyeIcon, EyeSlashIcon, PhoneIcon, EnvelopeIcon } from '@heroicons/react/24/outline';
        import GuestLayout from '@/Layouts/GuestLayout';

        export default function Login({ dashboardType, authMethods, hasLandingPage }) {
            const [showPassword, setShowPassword] = useState(false);
            const [authMethod, setAuthMethod] = useState(authMethods.includes('email') ? 'email' : 'sms');
            const [otpStep, setOtpStep] = useState(false);
            
            const { data, setData, post, processing, errors, reset } = useForm({
                email: '',
                password: '',
                phone: '',
                otp: '',
                remember: false,
            });

            const submitEmailAuth = (e) => {
                e.preventDefault();
                post(route(`\${dashboardType}.login`), {
                    onFinish: () => reset('password'),
                });
            };

            const sendSmsOtp = (e) => {
                e.preventDefault();
                post(route(`\${dashboardType}.send-sms-otp`), {
                    data: { phone: data.phone },
                    onSuccess: () => setOtpStep(true),
                });
            };

            const verifySmsOtp = (e) => {
                e.preventDefault();
                post(route(`\${dashboardType}.verify-sms-otp`));
            };

            return (
                <GuestLayout>
                    <Head title="{\`\${dashboardType.charAt(0).toUpperCase() + dashboardType.slice(1)} Login\`}" />

                    <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
                        <div className="max-w-md w-full space-y-8">
                            {/* Header */}
                            <div className="text-center">
                                <div className="mx-auto h-16 w-16 bg-primary-600 rounded-lg flex items-center justify-center mb-6">
                                    <span className="text-2xl font-bold text-white">
                                        {\`\${dashboardType.charAt(0).toUpperCase()}\${dashboardType.charAt(1).toUpperCase()}\`}
                                    </span>
                                </div>
                                <h2 className="text-3xl font-bold text-gray-900">
                                    {\`\${dashboardType.charAt(0).toUpperCase() + dashboardType.slice(1)} Portal\`}
                                </h2>
                                <p className="mt-2 text-sm text-gray-600">
                                    Sign in to access your dashboard
                                </p>
                            </div>

                            {/* Auth Method Toggle */}
                            {authMethods.length > 1 && !otpStep && (
                                <div className="flex rounded-md shadow-sm" role="group">
                                    {authMethods.includes('email') && (
                                        <button
                                            type="button"
                                            onClick={() => setAuthMethod('email')}
                                            className={\`px-4 py-2 text-sm font-medium border rounded-l-lg \${authMethod === 'email' 
                                                ? 'bg-primary-600 text-white border-primary-600' 
                                                : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'}\`}
                                        >
                                            <EnvelopeIcon className="w-4 h-4 mr-2 inline" />
                                            Email
                                        </button>
                                    )}
                                    {authMethods.includes('sms') && (
                                        <button
                                            type="button"
                                            onClick={() => setAuthMethod('sms')}
                                            className={\`px-4 py-2 text-sm font-medium border \${authMethods.length === 1 ? 'rounded-lg' : 'rounded-r-lg'} \${authMethod === 'sms' 
                                                ? 'bg-primary-600 text-white border-primary-600' 
                                                : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'}\`}
                                        >
                                            <PhoneIcon className="w-4 h-4 mr-2 inline" />
                                            SMS
                                        </button>
                                    )}
                                </div>
                            )}

                            {/* Email Auth Form */}
                            {authMethod === 'email' && !otpStep && (
                                <form className="mt-8 space-y-6" onSubmit={submitEmailAuth}>
                                    <div>
                                        <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                                            Email address
                                        </label>
                                        <input
                                            id="email"
                                            name="email"
                                            type="email"
                                            required
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                            placeholder="Enter your email"
                                        />
                                        {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
                                    </div>

                                    <div>
                                        <label htmlFor="password" className="block text-sm font-medium text-gray-700">
                                            Password
                                        </label>
                                        <div className="mt-1 relative">
                                            <input
                                                id="password"
                                                name="password"
                                                type={showPassword ? 'text' : 'password'}
                                                required
                                                value={data.password}
                                                onChange={(e) => setData('password', e.target.value)}
                                                className="block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                                placeholder="Enter your password"
                                            />
                                            <button
                                                type="button"
                                                className="absolute inset-y-0 right-0 pr-3 flex items-center"
                                                onClick={() => setShowPassword(!showPassword)}
                                            >
                                                {showPassword ? (
                                                    <EyeSlashIcon className="h-5 w-5 text-gray-400" />
                                                ) : (
                                                    <EyeIcon className="h-5 w-5 text-gray-400" />
                                                )}
                                            </button>
                                        </div>
                                        {errors.password && <p className="mt-1 text-sm text-red-600">{errors.password}</p>}
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center">
                                            <input
                                                id="remember"
                                                name="remember"
                                                type="checkbox"
                                                checked={data.remember}
                                                onChange={(e) => setData('remember', e.target.checked)}
                                                className="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                                            />
                                            <label htmlFor="remember" className="ml-2 block text-sm text-gray-900">
                                                Remember me
                                            </label>
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="w-full btn-primary"
                                    >
                                        {processing ? 'Signing in...' : 'Sign in'}
                                    </button>
                                </form>
                            )}

                            {/* SMS Auth Form */}
                            {authMethod === 'sms' && !otpStep && (
                                <form className="mt-8 space-y-6" onSubmit={sendSmsOtp}>
                                    <div>
                                        <label htmlFor="phone" className="block text-sm font-medium text-gray-700">
                                            Phone Number
                                        </label>
                                        <input
                                            id="phone"
                                            name="phone"
                                            type="tel"
                                            required
                                            value={data.phone}
                                            onChange={(e) => setData('phone', e.target.value)}
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                            placeholder="Enter your phone number"
                                        />
                                        {errors.phone && <p className="mt-1 text-sm text-red-600">{errors.phone}</p>}
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="w-full btn-primary"
                                    >
                                        {processing ? 'Sending OTP...' : 'Send OTP'}
                                    </button>
                                </form>
                            )}

                            {/* OTP Verification Form */}
                            {otpStep && (
                                <form className="mt-8 space-y-6" onSubmit={verifySmsOtp}>
                                    <div>
                                        <label htmlFor="otp" className="block text-sm font-medium text-gray-700">
                                            Enter OTP
                                        </label>
                                        <input
                                            id="otp"
                                            name="otp"
                                            type="text"
                                            required
                                            value={data.otp}
                                            onChange={(e) => setData('otp', e.target.value)}
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 text-center text-lg tracking-widest"
                                            placeholder="000000"
                                            maxLength="6"
                                        />
                                        {errors.otp && <p className="mt-1 text-sm text-red-600">{errors.otp}</p>}
                                        <p className="mt-1 text-xs text-gray-500">
                                            OTP sent to {data.phone}
                                        </p>
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="w-full btn-primary"
                                    >
                                        {processing ? 'Verifying...' : 'Verify & Sign In'}
                                    </button>

                                    <button
                                        type="button"
                                        onClick={() => setOtpStep(false)}
                                        className="w-full btn-secondary"
                                    >
                                        Back to Phone Number
                                    </button>
                                </form>
                            )}

                            {/* Landing Page Link */}
                            {hasLandingPage && (
                                <div className="text-center">
                                    <a
                                        href={route(`\${dashboardType}.landing`)}
                                        className="text-sm text-primary-600 hover:text-primary-500"
                                    >
                                        ← Back to Landing Page
                                    </a>
                                </div>
                            )}
                        </div>
                    </div>
                </GuestLayout>
            );
        }
        JSX;
    }
    /**
     * Get role-based dashboard section
     */
    protected function getRoleBasedDashboardSection(): string
    {
        return <<<JSX

                {/* Role-based Management Section */}
                <div className="bg-white shadow rounded-lg p-6">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">Management Tools</h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="border-2 border-dashed border-gray-200 rounded-lg p-4 hover:border-primary-300 hover:bg-primary-50 transition-colors">
                            <UserGroupIcon className="h-8 w-8 text-gray-400 mb-2" />
                            <h3 className="font-medium text-gray-900">User Management</h3>
                            <p className="text-sm text-gray-500">Manage users, roles, and permissions</p>
                        </div>
                        
                        <div className="border-2 border-dashed border-gray-200 rounded-lg p-4 hover:border-primary-300 hover:bg-primary-50 transition-colors">
                            <ChartBarIcon className="h-8 w-8 text-gray-400 mb-2" />
                            <h3 className="font-medium text-gray-900">Reports & Analytics</h3>
                            <p className="text-sm text-gray-500">View detailed system reports</p>
                        </div>
                    </div>
                </div>
            JSX;
    }


    /**
     * Get dashboard layout template
     */
    protected function getDashboardLayoutTemplate(): string
    {
        $primary = $this->config['theme_config']['primary'];

        return <<<JSX
            import { useState } from 'react';
            import { Head, Link, router } from '@inertiajs/react';
            import { 
                Bars3Icon, 
                XMarkIcon,
                HomeIcon,
                UserIcon,
                CogIcon,
                ArrowRightOnRectangleIcon,
                ChevronLeftIcon,
                ChevronRightIcon,
                UserGroupIcon,
                ChartBarIcon
            } from '@heroicons/react/24/outline';
            import {
                HomeIcon as HomeIconSolid,
                UserIcon as UserIconSolid,
                CogIcon as CogIconSolid,
                UserGroupIcon as UserGroupIconSolid,
                ChartBarIcon as ChartBarIconSolid
            } from '@heroicons/react/24/solid';

            const navigation = [
                { 
                    name: 'Dashboard', 
                    href: '{$this->type}.dashboard', 
                    icon: HomeIcon, 
                    iconActive: HomeIconSolid 
                },
                { 
                    name: 'Profile', 
                    href: '{$this->type}.profile', 
                    icon: UserIcon, 
                    iconActive: UserIconSolid 
                },
            ];

            export default function DashboardLayout({ children, user }) {
                const [sidebarOpen, setSidebarOpen] = useState(false);
                const [sidebarCollapsed, setSidebarCollapsed] = useState(false);
                
                const currentRoute = route().current();

                const logout = () => {
                    router.post(route('{$this->type}.logout'));
                };

                return (
                    <>
                        <Head>
                            <title>{\`\${'{$this->type}'.charAt(0).toUpperCase() + '{$this->type}'.slice(1)} Dashboard\`}</title>
                        </Head>

                        <div className="min-h-screen bg-gray-50 flex">
                            {/* Mobile sidebar */}
                            <div className={\`fixed inset-0 z-40 lg:hidden \${sidebarOpen ? '' : 'pointer-events-none'}\`}>
                                <div className={\`fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity \${sidebarOpen ? 'opacity-100' : 'opacity-0'}\`} 
                                    onClick={() => setSidebarOpen(false)} />
                                
                                <div className={\`fixed inset-y-0 left-0 flex flex-col w-64 bg-white shadow-xl transform transition-transform \${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}\`}>
                                    <div className="flex items-center justify-between h-16 px-4" style={{ backgroundColor: '{$primary}' }}>
                                        <span className="text-white font-bold text-lg">
                                            {\`\${'{$this->type}'.charAt(0).toUpperCase() + '{$this->type}'.slice(1)}\`}
                                        </span>
                                        <button onClick={() => setSidebarOpen(false)} className="text-white">
                                            <XMarkIcon className="h-6 w-6" />
                                        </button>
                                    </div>
                                    
                                    <nav className="flex-1 px-4 py-4 space-y-2">
                                        {navigation.map((item) => {
                                            const isActive = currentRoute === item.href;
                                            const Icon = isActive ? item.iconActive : item.icon;
                                            
                                            return (
                                                <Link
                                                    key={item.name}
                                                    href={route(item.href)}
                                                    className={\`flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors \${
                                                        isActive
                                                            ? 'bg-primary-100 text-primary-700'
                                                            : 'text-gray-700 hover:bg-gray-50'
                                                    }\`}
                                                >
                                                    <Icon className="mr-3 h-5 w-5" />
                                                    {item.name}
                                                </Link>
                                            );
                                        })}
                                    </nav>
                                </div>
                            </div>

                            {/* Desktop sidebar */}
                            <div className={\`hidden lg:flex lg:flex-col \${sidebarCollapsed ? 'w-16' : 'w-64'} bg-white shadow-sm transition-all duration-300\`}>
                                <div className="flex items-center justify-between h-16 px-4" style={{ backgroundColor: '{$primary}' }}>
                                    {!sidebarCollapsed && (
                                        <span className="text-white font-bold text-lg">
                                            {\`\${'{$this->type}'.charAt(0).toUpperCase() + '{$this->type}'.slice(1)}\`}
                                        </span>
                                    )}
                                    <button 
                                        onClick={() => setSidebarCollapsed(!sidebarCollapsed)}
                                        className="text-white p-1 rounded hover:bg-white hover:bg-opacity-20"
                                    >
                                        {sidebarCollapsed ? (
                                            <ChevronRightIcon className="h-5 w-5" />
                                        ) : (
                                            <ChevronLeftIcon className="h-5 w-5" />
                                        )}
                                    </button>
                                </div>
                                
                                <nav className="flex-1 px-4 py-4 space-y-2">
                                    {navigation.map((item) => {
                                        const isActive = currentRoute === item.href;
                                        const Icon = isActive ? item.iconActive : item.icon;
                                        
                                        return (
                                            <Link
                                                key={item.name}
                                                href={route(item.href)}
                                                className={\`flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors \${
                                                    isActive
                                                        ? 'bg-primary-100 text-primary-700'
                                                        : 'text-gray-700 hover:bg-gray-50'
                                                }\`}
                                                title={sidebarCollapsed ? item.name : ''}
                                            >
                                                <Icon className={\`h-5 w-5 \${sidebarCollapsed ? '' : 'mr-3'}\`} />
                                                {!sidebarCollapsed && item.name}
                                            </Link>
                                        );
                                    })}
                                </nav>
                                
                                <div className="px-4 py-4 border-t border-gray-200">
                                    <button
                                        onClick={logout}
                                        className={\`flex items-center w-full px-4 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-50 transition-colors\`}
                                        title={sidebarCollapsed ? 'Logout' : ''}
                                    >
                                        <ArrowRightOnRectangleIcon className={\`h-5 w-5 \${sidebarCollapsed ? '' : 'mr-3'}\`} />
                                        {!sidebarCollapsed && 'Logout'}
                                    </button>
                                </div>
                            </div>

                            {/* Main content */}
                            <div className="flex-1 flex flex-col overflow-hidden">
                                {/* Top bar for mobile */}
                                <div className="lg:hidden bg-white shadow-sm">
                                    <div className="flex items-center justify-between h-16 px-4">
                                        <button
                                            onClick={() => setSidebarOpen(true)}
                                            className="text-gray-600"
                                        >
                                            <Bars3Icon className="h-6 w-6" />
                                        </button>
                                        <span className="font-bold text-lg">
                                            {\`\${'{$this->type}'.charAt(0).toUpperCase() + '{$this->type}'.slice(1)}\`}
                                        </span>
                                        <div className="flex items-center space-x-2">
                                            <span className="text-sm text-gray-600">{user.name}</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Page content */}
                                <main className="flex-1 overflow-auto p-6">
                                    {children}
                                </main>
                            </div>
                        </div>
                    </>
                );
            }
JSX;
    }
    protected function generateLandingPage(): void
    {
        // Landing page implementation
    }

    protected function updateMainDatabaseConfig(): void
    {
        // Update main database config
    }

    protected function generateRoleModel(): void
    {
        // Role model implementation
    }

    protected function generatePermissionModel(): void
    {
        // Permission model implementation  
    }

    protected function generateUserController(): void
    {
        // User controller implementation
    }

    /**
     * Generate API auth controller
     */
    protected function generateApiAuthController(): void
    {
        $apiAuthContent = <<<PHP
            <?php

            namespace App\\Http\\Controllers\\Api\\{$this->studlyType};

            use App\\Http\\Controllers\\Controller;
            use App\\Models\\{$this->studlyType}\\User;
            use App\\Services\\AakashSmsService;
            use Illuminate\\Http\\Request;
            use Illuminate\\Support\\Facades\\Hash;
            use Illuminate\\Validation\\ValidationException;

            class AuthController extends Controller
            {
                public function login(Request \$request)
                {
                    \$request->validate([
                        'email' => 'required|email',
                        'password' => 'required|string',
                    ]);

                    \$query = User::where('email', \$request->email);
                    
                    if ('{$this->config['database_strategy']}' === 'shared') {
                        \$query->dashboardType('{$this->type}');
                    }

                    \$user = \$query->first();

                    if (!\$user || !Hash::check(\$request->password, \$user->password)) {
                        throw ValidationException::withMessages([
                            'email' => ['The provided credentials are incorrect.'],
                        ]);
                    }

                    if (!\$user->is_active) {
                        throw ValidationException::withMessages([
                            'email' => ['Your account is deactivated.'],
                        ]);
                    }

                    \$token = \$user->createToken('{$this->type}-token')->plainTextToken;
                    \$user->updateLastLogin();

                    return response()->json([
                        'user' => \$user,
                        'token' => \$token,
                        'dashboard_type' => '{$this->type}',
                    ]);
                }

                public function sendOtp(Request \$request)
                {
                    \$request->validate([
                        'phone' => 'required|string',
                    ]);

                    \$query = User::where('phone', \$request->phone);
                    
                    if ('{$this->config['database_strategy']}' === 'shared') {
                        \$query->dashboardType('{$this->type}');
                    }

                    \$user = \$query->first();

                    if (!\$user) {
                        throw ValidationException::withMessages([
                            'phone' => ['No account found with this phone number.'],
                        ]);
                    }

                    if (!\$user->is_active) {
                        throw ValidationException::withMessages([
                            'phone' => ['Your account is deactivated.'],
                        ]);
                    }

                    \$otp = random_int(100000, 999999);
                    
                    // Store OTP in cache with 5-minute expiry
                    cache()->put("otp_{\$user->id}", \$otp, now()->addMinutes(5));

                    try {
                        \$smsService = app(AakashSmsService::class);
                        \$smsService->sendOtp(\$user->phone, \$otp);
                        
                        return response()->json(['message' => 'OTP sent successfully']);
                    } catch (\\Exception \$e) {
                        return response()->json(['message' => 'Failed to send OTP'], 500);
                    }
                }

                public function verifyOtp(Request \$request)
                {
                    \$request->validate([
                        'phone' => 'required|string',
                        'otp' => 'required|string',
                    ]);

                    \$query = User::where('phone', \$request->phone);
                    
                    if ('{$this->config['database_strategy']}' === 'shared') {
                        \$query->dashboardType('{$this->type}');
                    }

                    \$user = \$query->first();

                    if (!\$user) {
                        throw ValidationException::withMessages([
                            'phone' => ['Invalid phone number.'],
                        ]);
                    }

                    \$storedOtp = cache()->get("otp_{\$user->id}");

                    if (!\$storedOtp || \$storedOtp !== \$request->otp) {
                        throw ValidationException::withMessages([
                            'otp' => ['Invalid or expired OTP.'],
                        ]);
                    }

                    cache()->forget("otp_{\$user->id}");

                    \$token = \$user->createToken('{$this->type}-token')->plainTextToken;
                    \$user->updateLastLogin();

                    return response()->json([
                        'user' => \$user,
                        'token' => \$token,
                        'dashboard_type' => '{$this->type}',
                    ]);
                }

                public function logout(Request \$request)
                {
                    \$request->user()->currentAccessToken()->delete();

                    return response()->json(['message' => 'Logged out successfully']);
                }

                public function user(Request \$request)
                {
                    return response()->json([
                        'user' => \$request->user(),
                        'dashboard_type' => '{$this->type}',
                    ]);
                }

                public function updateProfile(Request \$request)
                {
                    \$user = \$request->user();

                    \$validated = \$request->validate([
                        'name' => 'required|string|max:255',
                        'email' => 'required|email|unique:' . \$user->getTable() . ',email,' . \$user->id,
                        'phone' => 'nullable|string|unique:' . \$user->getTable() . ',phone,' . \$user->id,
                        'profile_data' => 'nullable|array',
                    ]);

                    \$user->update(\$validated);

                    return response()->json([
                        'user' => \$user->fresh(),
                        'message' => 'Profile updated successfully',
                    ]);
                }
            }
            PHP;

        File::put(
            app_path("Http/Controllers/Api/{$this->studlyType}/AuthController.php"),
            $apiAuthContent
        );
    }


    protected function generateRolesMigration(string $timestamp): void
    {
        // Roles migration implementation
    }

    protected function createDefaultRoles(): void
    {
        // Default roles creation
    }

    protected function generateMiddleware(): void
    {
        // Middleware generation
    }
    protected function updateAuthGuards(): void
    {
        $authConfigPath = config_path('auth.php');
        $authContent = file_get_contents($authConfigPath);

        $guardName = $this->type;
        $providerName = $this->type . '_users';
        $modelClass = "App\\Models\\{$this->studlyType}\\User::class";

        // Add guard configuration
        $guardConfig = "'{$guardName}' => [\n            'driver' => 'session',\n            'provider' => '{$providerName}',\n        ],";

        if (strpos($authContent, "'{$guardName}' =>") === false) {
            $authContent = preg_replace(
                "/(    'guards' => \[\s*.*?'web' => \[.*?\],)/s",
                "$1\n\n        {$guardConfig}",
                $authContent
            );
        }

        // Add provider configuration
        $providerConfig = "'{$providerName}' => [\n            'driver' => 'eloquent',\n            'model' => {$modelClass},\n        ],";

        if (strpos($authContent, "'{$providerName}' =>") === false) {
            $authContent = preg_replace(
                "/(    'providers' => \[\s*.*?'users' => \[.*?\],)/s",
                "$1\n\n        {$providerConfig}",
                $authContent
            );
        }

        file_put_contents($authConfigPath, $authContent);
    }
}
