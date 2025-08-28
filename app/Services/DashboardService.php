<?php

namespace App\Services;

use App\Models\Dashboard;
use App\Models\DashboardType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class DashboardService
{
    /**
     * Get all active dashboard types
     */
    public function getActiveDashboards(): Collection
    {
        return DashboardType::where('is_active', true)->get();
    }

    /**
     * Create a new dashboard type
     */
    public function createDashboardType(array $data): DashboardType
    {
        $type = strtolower($data['type']);
        
        // Handle force creation - update existing or create new
        if (isset($data['force']) && $data['force']) {
            $dashboardType = DashboardType::updateOrCreate(
                ['type' => $type],
                [
                    'name' => $data['name'],
                    'description' => $data['description'] ?? '',
                    'theme_config' => $data['theme_config'] ?? $this->getDefaultThemeConfig(),
                    'auth_methods' => $data['auth_methods'] ?? ['email'],
                    'settings' => $data['settings'] ?? [],
                    'is_active' => true,
                ]
            );
        } else {
            $dashboardType = DashboardType::create([
                'type' => $type,
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'theme_config' => $data['theme_config'] ?? $this->getDefaultThemeConfig(),
                'auth_methods' => $data['auth_methods'] ?? ['email'],
                'settings' => $data['settings'] ?? [],
                'is_active' => true,
            ]);
        }

        // Generate dashboard files
        $this->generateDashboardFiles($dashboardType);

        return $dashboardType;
    }

    /**
     * Generate all necessary files for a dashboard type
     */
    protected function generateDashboardFiles(DashboardType $dashboardType): void
    {
        $type = $dashboardType->type;
        $studlyType = \Illuminate\Support\Str::studly($type);

        // Create directories
        $this->createDirectories($type);

        // Generate models
        $this->generateModel($type, $studlyType);

        // Generate controllers
        $this->generateControllers($type, $studlyType);

        // Generate migrations
        $this->generateMigrations($type, $studlyType);

        // Generate routes
        $this->generateRoutes($type);

        // Generate React components
        $this->generateReactComponents($type, $studlyType);

        // Generate API controllers
        $this->generateApiControllers($type, $studlyType);
    }

    /**
     * Create necessary directories
     */
    protected function createDirectories(string $type): void
    {
        $directories = [
            app_path("Models/{$type}"),
            app_path("Http/Controllers/{$type}"),
            app_path("Http/Controllers/Api/{$type}"),
            resource_path("js/Pages/{$type}"),
            resource_path("js/Pages/{$type}/Auth"),
            resource_path("js/Components/{$type}"),
            resource_path("js/Layouts/{$type}"),
            database_path("seeders"),
            base_path("docs/api"),
        ];

        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
        }
    }

    /**
     * Generate User model for dashboard type
     */
    protected function generateModel(string $type, string $studlyType): void
    {
        $modelContent = $this->getModelTemplate($studlyType, $type);
        File::put(app_path("Models/{$type}/User.php"), $modelContent);

        // Generate other models (Role, Permission, etc.)
        $this->generateRoleModel($type, $studlyType);
        $this->generatePermissionModel($type, $studlyType);
    }

    /**
     * Generate controllers for dashboard type
     */
    protected function generateControllers(string $type, string $studlyType): void
    {
        // Auth controllers
        $this->generateAuthControllers($type, $studlyType);
        
        // Dashboard controller
        $this->generateDashboardController($type, $studlyType);
        
        // User management controller
        $this->generateUserController($type, $studlyType);
    }

    /**
     * Generate migrations for dashboard type
     */
    protected function generateMigrations(string $type, string $studlyType): void
    {
        $timestamp = date('Y_m_d_His');
        
        // Users table migration
        $usersMigration = $this->getUsersMigrationTemplate($type);
        File::put(
            database_path("migrations/{$timestamp}_create_{$type}_users_table.php"),
            $usersMigration
        );

        // Roles table migration
        $rolesMigration = $this->getRolesMigrationTemplate($type);
        File::put(
            database_path("migrations/{$timestamp}_create_{$type}_roles_table.php"),
            $rolesMigration
        );
    }

    /**
     * Generate routes for dashboard type
     */
    protected function generateRoutes(string $type): void
    {
        $routesContent = $this->getRoutesTemplate($type);
        File::put(base_path("routes/{$type}.php"), $routesContent);
    }

    /**
     * Generate React components for dashboard type
     */
    protected function generateReactComponents(string $type, string $studlyType): void
    {
        // Layout component
        $layoutContent = $this->getLayoutTemplate($studlyType, $type);
        File::put(resource_path("js/Layouts/{$type}/AppLayout.jsx"), $layoutContent);

        // Dashboard page
        $dashboardContent = $this->getDashboardPageTemplate($studlyType, $type);
        File::put(resource_path("js/Pages/{$type}/Dashboard.jsx"), $dashboardContent);

        // Auth pages
        $this->generateAuthPages($type, $studlyType);
    }

    /**
     * Generate API controllers
     */
    protected function generateApiControllers(string $type, string $studlyType): void
    {
        $apiControllerContent = $this->getApiControllerTemplate($studlyType, $type);
        File::put(app_path("Http/Controllers/Api/{$type}/AuthController.php"), $apiControllerContent);
    }

    /**
     * Get default theme configuration
     */
    protected function getDefaultThemeConfig(): array
    {
        return [
            'primary_color' => '#3b82f6',
            'secondary_color' => '#64748b',
            'sidebar_color' => '#ffffff',
            'text_color' => '#1f2937',
            'background_color' => '#f9fafb',
            'dark_mode' => false,
            'logo_url' => null,
            'favicon_url' => null,
        ];
    }

    /**
     * Get model template
     */
    protected function getModelTemplate(string $studlyType, string $type): string
    {
        return <<<PHP
<?php

namespace App\Models\\{$type};

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The table associated with the model.
     */
    protected \$table = '{$type}_users';

    /**
     * The attributes that are mass assignable.
     */
    protected \$fillable = [
        'name',
        'email',
        'phone',
        'password',
        'email_verified_at',
        'phone_verified_at',
        'is_active',
        'theme_preferences',
        'language',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected \$hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'theme_preferences' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return \$this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [
            'dashboard_type' => '{$type}',
            'user_type' => 'user',
        ];
    }

    /**
     * Get the guard name for this model
     */
    public function getGuardName(): string
    {
        return '{$type}';
    }

    /**
     * Get activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
PHP;
    }

    /**
     * Generate auth controllers
     */
    protected function generateAuthControllers(string $type, string $studlyType): void
    {
        $authControllerContent = <<<PHP
<?php

namespace App\Http\Controllers\\{$type};

use App\Http\Controllers\Controller;
use App\Models\\{$type}\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    protected \$authService;

    public function __construct(AuthService \$authService)
    {
        \$this->authService = \$authService;
        \$this->middleware('guest:{$type}')->except('logout');
    }

    /**
     * Show the login form
     */
    public function showLogin(): Response
    {
        return Inertia::render('{$studlyType}/Auth/Login', [
            'dashboardType' => '{$type}',
            'authMethods' => config('dashboards.{$type}.auth_methods', ['email']),
        ]);
    }

    /**
     * Handle login request
     */
    public function login(Request \$request)
    {
        \$request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('{$type}')->attempt(\$request->only('email', 'password'), \$request->boolean('remember'))) {
            \$request->session()->regenerate();
            return redirect()->intended(route('{$type}.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout
     */
    public function logout(Request \$request)
    {
        Auth::guard('{$type}')->logout();
        \$request->session()->invalidate();
        \$request->session()->regenerateToken();
        
        return redirect()->route('{$type}.login');
    }

    /**
     * Show registration form
     */
    public function showRegister(): Response
    {
        return Inertia::render('{$studlyType}/Auth/Register', [
            'dashboardType' => '{$type}',
        ]);
    }

    /**
     * Handle registration
     */
    public function register(Request \$request)
    {
        \$request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:{$type}_users',
            'phone' => 'nullable|string|max:20|unique:{$type}_users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        \$user = User::create([
            'name' => \$request->name,
            'email' => \$request->email,
            'phone' => \$request->phone,
            'password' => Hash::make(\$request->password),
        ]);

        Auth::guard('{$type}')->login(\$user);

        return redirect()->route('{$type}.dashboard');
    }
}
PHP;

        File::put(app_path("Http/Controllers/{$type}/AuthController.php"), $authControllerContent);
    }

    /**
     * Generate dashboard controller
     */
    protected function generateDashboardController(string $type, string $studlyType): void
    {
        $dashboardControllerContent = <<<PHP
<?php

namespace App\Http\Controllers\\{$type};

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct()
    {
        \$this->middleware('auth:{$type}');
    }

    /**
     * Show the dashboard
     */
    public function index(): Response
    {
        return Inertia::render('{$studlyType}/Dashboard', [
            'dashboardType' => '{$type}',
            'user' => auth('{$type}')->user(),
            'stats' => \$this->getDashboardStats(),
        ]);
    }

    /**
     * Get dashboard statistics
     */
    protected function getDashboardStats(): array
    {
        return [
            'total_users' => \App\Models\\{$type}\User::count(),
            'active_users' => \App\Models\\{$type}\User::where('is_active', true)->count(),
            'total_roles' => 0, // Will be implemented with role system
            'recent_activities' => [], // Will be implemented with activity log
        ];
    }
}
PHP;

        File::put(app_path("Http/Controllers/{$type}/DashboardController.php"), $dashboardControllerContent);
    }

    // Additional helper methods for generating other components...
    protected function generateRoleModel(string $type, string $studlyType): void
    {
        // Implementation for role model generation
    }

    protected function generatePermissionModel(string $type, string $studlyType): void
    {
        // Implementation for permission model generation
    }

    protected function generateUserController(string $type, string $studlyType): void
    {
        // Implementation for user controller generation
    }

    protected function getUsersMigrationTemplate(string $type): string
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$type}_users', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->string('email')->unique();
            \$table->string('phone')->nullable()->unique();
            \$table->timestamp('email_verified_at')->nullable();
            \$table->timestamp('phone_verified_at')->nullable();
            \$table->string('password');
            \$table->boolean('is_active')->default(true);
            \$table->json('theme_preferences')->nullable();
            \$table->string('language', 10)->default('en');
            \$table->string('timezone', 50)->default('UTC');
            \$table->rememberToken();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$type}_users');
    }
};
PHP;
    }

    protected function getRolesMigrationTemplate(string $type): string
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$type}_roles', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->string('guard_name')->default('{$type}');
            \$table->string('description')->nullable();
            \$table->timestamps();
            
            \$table->unique(['name', 'guard_name']);
        });

        Schema::create('{$type}_permissions', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->string('guard_name')->default('{$type}');
            \$table->string('description')->nullable();
            \$table->timestamps();
            
            \$table->unique(['name', 'guard_name']);
        });

        Schema::create('{$type}_model_has_permissions', function (Blueprint \$table) {
            \$table->unsignedBigInteger('permission_id');
            \$table->string('model_type');
            \$table->unsignedBigInteger('model_id');
            \$table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
            
            \$table->foreign('permission_id')
                ->references('id')
                ->on('{$type}_permissions')
                ->onDelete('cascade');
                
            \$table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
        });

        Schema::create('{$type}_model_has_roles', function (Blueprint \$table) {
            \$table->unsignedBigInteger('role_id');
            \$table->string('model_type');
            \$table->unsignedBigInteger('model_id');
            \$table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
            
            \$table->foreign('role_id')
                ->references('id')
                ->on('{$type}_roles')
                ->onDelete('cascade');
                
            \$table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
        });

        Schema::create('{$type}_role_has_permissions', function (Blueprint \$table) {
            \$table->unsignedBigInteger('permission_id');
            \$table->unsignedBigInteger('role_id');
            
            \$table->foreign('permission_id')
                ->references('id')
                ->on('{$type}_permissions')
                ->onDelete('cascade');
                
            \$table->foreign('role_id')
                ->references('id')
                ->on('{$type}_roles')
                ->onDelete('cascade');
                
            \$table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$type}_role_has_permissions');
        Schema::dropIfExists('{$type}_model_has_roles');
        Schema::dropIfExists('{$type}_model_has_permissions');
        Schema::dropIfExists('{$type}_permissions');
        Schema::dropIfExists('{$type}_roles');
    }
};
PHP;
    }

    protected function getRoutesTemplate(string $type): string
    {
        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\\{$type}\AuthController;
use App\Http\Controllers\\{$type}\DashboardController;

// Authentication Routes
Route::prefix('{$type}')->name('{$type}.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    // Protected Routes
    Route::middleware('auth:{$type}')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
});
PHP;
    }

    protected function getLayoutTemplate(string $studlyType, string $type): string
    {
        return <<<JSX
import React, { useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { 
    HomeIcon, 
    UsersIcon, 
    CogIcon, 
    MenuIcon, 
    XIcon,
    SunIcon,
    MoonIcon,
    LogoutIcon
} from '@heroicons/react/outline';

export default function AppLayout({ title, children }) {
    const { auth, dashboardType } = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [darkMode, setDarkMode] = useState(false);

    const navigation = [
        { name: 'Dashboard', href: route('{$type}.dashboard'), icon: HomeIcon, current: true },
        { name: 'Users', href: '#', icon: UsersIcon, current: false },
        { name: 'Settings', href: '#', icon: CogIcon, current: false },
    ];

    return (
        <div className={\`min-h-screen bg-gray-50 \${darkMode ? 'dark' : ''}\`}>
            <Head title={title} />
            
            {/* Sidebar */}
            <div className={\`fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out \${
                sidebarOpen ? 'translate-x-0' : '-translate-x-full'
            } lg:translate-x-0 lg:static lg:inset-0\`}>
                <div className="flex items-center justify-between h-16 px-6 border-b border-gray-200">
                    <div className="flex items-center">
                        <h1 className="text-xl font-semibold text-gray-900">
                            {ucwords(str_replace('_', ' ', '{$type}'))} Dashboard
                        </h1>
                    </div>
                    <button
                        onClick={() => setSidebarOpen(false)}
                        className="lg:hidden p-1 rounded-md text-gray-400 hover:text-gray-500"
                    >
                        <XIcon className="h-6 w-6" />
                    </button>
                </div>
                
                <nav className="mt-8">
                    <div className="px-3 space-y-1">
                        {navigation.map((item) => (
                            <Link
                                key={item.name}
                                href={item.href}
                                className={\`group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 \${
                                    item.current
                                        ? 'bg-primary-100 text-primary-700'
                                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                                }\`}
                            >
                                <item.icon className="mr-3 h-5 w-5" />
                                {item.name}
                            </Link>
                        ))}
                    </div>
                </nav>
            </div>

            {/* Main content */}
            <div className="lg:pl-64">
                {/* Top bar */}
                <div className="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                    <button
                        type="button"
                        className="-m-2.5 p-2.5 text-gray-700 lg:hidden"
                        onClick={() => setSidebarOpen(true)}
                    >
                        <MenuIcon className="h-6 w-6" />
                    </button>

                    <div className="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                        <div className="flex flex-1"></div>
                        <div className="flex items-center gap-x-4 lg:gap-x-6">
                            {/* Theme toggle */}
                            <button
                                onClick={() => setDarkMode(!darkMode)}
                                className="p-2 text-gray-400 hover:text-gray-500"
                            >
                                {darkMode ? (
                                    <SunIcon className="h-5 w-5" />
                                ) : (
                                    <MoonIcon className="h-5 w-5" />
                                )}
                            </button>

                            {/* User menu */}
                            <div className="flex items-center gap-x-4">
                                <span className="text-sm font-medium text-gray-700">
                                    {auth.user?.name}
                                </span>
                                <Link
                                    href={route('{$type}.logout')}
                                    method="post"
                                    as="button"
                                    className="p-2 text-gray-400 hover:text-gray-500"
                                >
                                    <LogoutIcon className="h-5 w-5" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Page content */}
                <main className="py-6">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        {children}
                    </div>
                </main>
            </div>

            {/* Sidebar overlay for mobile */}
            {sidebarOpen && (
                <div
                    className="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}
        </div>
    );
}
JSX;
    }

    protected function getDashboardPageTemplate(string $studlyType, string $type): string
    {
        return <<<JSX
import React from 'react';
import AppLayout from '@/Layouts/{$type}/AppLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard({ auth, stats }) {
    return (
        <AppLayout title="Dashboard">
            <Head title="Dashboard" />
            
            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-900">
                        Welcome back, {auth.user?.name}!
                    </h1>
                    <p className="mt-1 text-sm text-gray-600">
                        Here's what's happening with your {'{$studlyType}'} dashboard today.
                    </p>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="bg-white overflow-hidden shadow rounded-lg">
                        <div className="p-5">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className="w-8 h-8 bg-primary-500 rounded-md flex items-center justify-center">
                                        <span className="text-white text-sm font-medium">U</span>
                                    </div>
                                </div>
                                <div className="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt className="text-sm font-medium text-gray-500 truncate">
                                            Total Users
                                        </dt>
                                        <dd className="text-lg font-medium text-gray-900">
                                            {stats.total_users || 0}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white overflow-hidden shadow rounded-lg">
                        <div className="p-5">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                        <span className="text-white text-sm font-medium">A</span>
                                    </div>
                                </div>
                                <div className="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt className="text-sm font-medium text-gray-500 truncate">
                                            Active Users
                                        </dt>
                                        <dd className="text-lg font-medium text-gray-900">
                                            {stats.active_users || 0}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white overflow-hidden shadow rounded-lg">
                        <div className="p-5">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                        <span className="text-white text-sm font-medium">R</span>
                                    </div>
                                </div>
                                <div className="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt className="text-sm font-medium text-gray-500 truncate">
                                            Total Roles
                                        </dt>
                                        <dd className="text-lg font-medium text-gray-900">
                                            {stats.total_roles || 0}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white overflow-hidden shadow rounded-lg">
                        <div className="p-5">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                        <span className="text-white text-sm font-medium">L</span>
                                    </div>
                                </div>
                                <div className="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt className="text-sm font-medium text-gray-500 truncate">
                                            Recent Activities
                                        </dt>
                                        <dd className="text-lg font-medium text-gray-900">
                                            {stats.recent_activities?.length || 0}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Welcome card */}
                <div className="bg-white shadow rounded-lg">
                    <div className="px-4 py-5 sm:p-6">
                        <h3 className="text-lg leading-6 font-medium text-gray-900">
                            Getting Started
                        </h3>
                        <div className="mt-2 max-w-xl text-sm text-gray-500">
                            <p>
                                Welcome to your {'{$studlyType}'} dashboard! This is a dynamically generated 
                                dashboard system with full authentication, role management, and theming capabilities.
                            </p>
                        </div>
                        <div className="mt-5">
                            <div className="rounded-md bg-primary-50 p-4">
                                <div className="flex">
                                    <div className="ml-3">
                                        <h3 className="text-sm font-medium text-primary-800">
                                            Dashboard Features:
                                        </h3>
                                        <div className="mt-2 text-sm text-primary-700">
                                            <ul className="list-disc pl-5 space-y-1">
                                                <li>User management with roles and permissions</li>
                                                <li>Dynamic theming and customization</li>
                                                <li>Multi-language support</li>
                                                <li>API endpoints for mobile integration</li>
                                                <li>Activity logging and audit trails</li>
                                                <li>SMS and email authentication</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
JSX;
    }

    protected function generateAuthPages(string $type, string $studlyType): void
    {
        // Generate Login page
        $loginContent = <<<JSX
import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ dashboardType, authMethods }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('{$type}.login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <Head title="Login" />
            
            <div className="max-w-md w-full space-y-8">
                <div>
                    <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        Sign in to your {ucwords(str_replace('_', ' ', '{$type}'))} account
                    </h2>
                </div>
                
                <form className="mt-8 space-y-6" onSubmit={submit}>
                    <div className="space-y-4">
                        <div>
                            <label htmlFor="email" className="sr-only">
                                Email address
                            </label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                autoComplete="email"
                                required
                                className="relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                                placeholder="Email address"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                            />
                            {errors.email && (
                                <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                            )}
                        </div>
                        
                        <div>
                            <label htmlFor="password" className="sr-only">
                                Password
                            </label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autoComplete="current-password"
                                required
                                className="relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
                                placeholder="Password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                            />
                            {errors.password && (
                                <p className="mt-1 text-sm text-red-600">{errors.password}</p>
                            )}
                        </div>
                    </div>

                    <div className="flex items-center justify-between">
                        <div className="flex items-center">
                            <input
                                id="remember"
                                name="remember"
                                type="checkbox"
                                className="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                                checked={data.remember}
                                onChange={(e) => setData('remember', e.target.checked)}
                            />
                            <label htmlFor="remember" className="ml-2 block text-sm text-gray-900">
                                Remember me
                            </label>
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            disabled={processing}
                            className="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                        >
                            {processing ? 'Signing in...' : 'Sign in'}
                        </button>
                    </div>

                    <div className="text-center">
                        <Link
                            href={route('{$type}.register')}
                            className="font-medium text-primary-600 hover:text-primary-500"
                        >
                            Don't have an account? Sign up
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    );
}
JSX;

        File::put(resource_path("js/Pages/{$type}/Auth/Login.jsx"), $loginContent);

        // Generate Register page (similar structure)
        $registerContent = <<<JSX
import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Register({ dashboardType }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('{$type}.register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <Head title="Register" />
            
            <div className="max-w-md w-full space-y-8">
                <div>
                    <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        Create your {ucwords(str_replace('_', ' ', '{$type}'))} account
                    </h2>
                </div>
                
                <form className="mt-8 space-y-6" onSubmit={submit}>
                    <div className="space-y-4">
                        <div>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                required
                                className="relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                placeholder="Full name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                            />
                            {errors.name && (
                                <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                            )}
                        </div>
                        
                        <div>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                required
                                className="relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                placeholder="Email address"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                            />
                            {errors.email && (
                                <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                            )}
                        </div>
                        
                        <div>
                            <input
                                id="phone"
                                name="phone"
                                type="tel"
                                className="relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                placeholder="Phone number (optional)"
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}
                            />
                            {errors.phone && (
                                <p className="mt-1 text-sm text-red-600">{errors.phone}</p>
                            )}
                        </div>
                        
                        <div>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                className="relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                placeholder="Password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                            />
                            {errors.password && (
                                <p className="mt-1 text-sm text-red-600">{errors.password}</p>
                            )}
                        </div>
                        
                        <div>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                className="relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                placeholder="Confirm password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                            />
                            {errors.password_confirmation && (
                                <p className="mt-1 text-sm text-red-600">{errors.password_confirmation}</p>
                            )}
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            disabled={processing}
                            className="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                        >
                            {processing ? 'Creating account...' : 'Create account'}
                        </button>
                    </div>

                    <div className="text-center">
                        <Link
                            href={route('{$type}.login')}
                            className="font-medium text-primary-600 hover:text-primary-500"
                        >
                            Already have an account? Sign in
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    );
}
JSX;

        File::put(resource_path("js/Pages/{$type}/Auth/Register.jsx"), $registerContent);
    }

    protected function getApiControllerTemplate(string $studlyType, string $type): string
    {
        return <<<PHP
<?php

namespace App\Http\Controllers\Api\\{$type};

use App\Http\Controllers\Controller;
use App\Models\\{$type}\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        \$this->middleware('auth:api-{$type}', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     */
    public function login(Request \$request)
    {
        \$credentials = \$request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!\$token = Auth::guard('api-{$type}')->attempt(\$credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return \$this->respondWithToken(\$token);
    }

    /**
     * Register a User.
     */
    public function register(Request \$request)
    {
        \$request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:{$type}_users',
            'phone' => 'nullable|string|max:20|unique:{$type}_users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        \$user = User::create([
            'name' => \$request->name,
            'email' => \$request->email,
            'phone' => \$request->phone,
            'password' => Hash::make(\$request->password),
        ]);

        \$token = Auth::guard('api-{$type}')->login(\$user);

        return \$this->respondWithToken(\$token);
    }

    /**
     * Get the authenticated User.
     */
    public function me()
    {
        return response()->json(Auth::guard('api-{$type}')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout()
    {
        Auth::guard('api-{$type}')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     */
    public function refresh()
    {
        return \$this->respondWithToken(Auth::guard('api-{$type}')->refresh());
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithToken(\$token)
    {
        return response()->json([
            'access_token' => \$token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api-{$type}')->factory()->getTTL() * 60,
            'user' => Auth::guard('api-{$type}')->user()
        ]);
    }
}
PHP;
    }
}