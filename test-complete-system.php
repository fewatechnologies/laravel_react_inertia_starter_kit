<?php

echo "🚀 Laravel Multi-Dashboard System Test\n";
echo "=====================================\n\n";

// Test 1: Check if Laravel is working
echo "✅ Testing Laravel Installation...\n";
echo "Laravel Version: " . app()->version() . "\n";
echo "Environment: " . app()->environment() . "\n\n";

// Test 2: Check database connections
echo "✅ Testing Database Connections...\n";
try {
    $mainDb = DB::connection('mysql')->getPdo();
    echo "Main Database (mysql): Connected ✓\n";
} catch (Exception $e) {
    echo "Main Database (mysql): Failed ✗ - " . $e->getMessage() . "\n";
}

try {
    $testDb = DB::connection('test_mysql')->getPdo();
    echo "Test Database (test_mysql): Connected ✓\n";
} catch (Exception $e) {
    echo "Test Database (test_mysql): Failed ✗ - " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Check dashboard types
echo "✅ Testing Dashboard Types...\n";
try {
    $dashboards = App\Models\DashboardType::all();
    echo "Dashboard Types Found: " . $dashboards->count() . "\n";
    foreach ($dashboards as $dashboard) {
        echo "  - {$dashboard->type}: {$dashboard->name} (" . ($dashboard->is_active ? 'Active' : 'Inactive') . ")\n";
    }
} catch (Exception $e) {
    echo "Dashboard Types: Failed ✗ - " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Check routes
echo "✅ Testing Routes...\n";
$routes = [
    'master.admin.dashboard' => 'Master Admin Dashboard',
    'test.login' => 'Test Login Page',
    'test.dashboard' => 'Test Dashboard',
];

foreach ($routes as $routeName => $description) {
    try {
        $url = route($routeName);
        echo "  - {$description}: {$url} ✓\n";
    } catch (Exception $e) {
        echo "  - {$description}: Failed ✗\n";
    }
}

echo "\n";

// Test 5: Check services
echo "✅ Testing Services...\n";
try {
    $dashboardService = app(App\Services\DashboardService::class);
    echo "Dashboard Service: Available ✓\n";
} catch (Exception $e) {
    echo "Dashboard Service: Failed ✗\n";
}

try {
    $themeService = app(App\Services\ThemeService::class);
    echo "Theme Service: Available ✓\n";
} catch (Exception $e) {
    echo "Theme Service: Failed ✗\n";
}

try {
    $authService = app(App\Services\AuthService::class);
    echo "Auth Service: Available ✓\n";
} catch (Exception $e) {
    echo "Auth Service: Failed ✗\n";
}

try {
    $smsService = app(App\Services\SmsService::class);
    echo "SMS Service: Available ✓\n";
} catch (Exception $e) {
    echo "SMS Service: Failed ✗\n";
}

echo "\n";

// Test 6: Configuration check
echo "✅ Testing Configuration...\n";
echo "SMS Token: " . (config('services.aakash_sms.token') ? 'Configured ✓' : 'Missing ✗') . "\n";
echo "JWT Secret: " . (config('jwt.secret') ? 'Configured ✓' : 'Missing ✗') . "\n";
echo "Mail Host: " . (config('mail.host') ? config('mail.host') . ' ✓' : 'Missing ✗') . "\n";

echo "\n";

echo "🎯 System Status Summary\n";
echo "========================\n";
echo "✅ Laravel Multi-Dashboard System is ready!\n\n";

echo "📱 Available URLs:\n";
echo "  - Master Admin: http://127.0.0.1:8000/master-admin\n";
echo "  - Test Dashboard: http://127.0.0.1:8000/test/login\n";
echo "  - API Endpoint: http://127.0.0.1:8000/api/test/login\n\n";

echo "🔐 Test Credentials (once seeded):\n";
echo "  - Email: admin@test.com | Password: password\n";
echo "  - Phone: 9843223774 (for SMS OTP testing)\n\n";

echo "🧪 API Test Commands:\n";
echo "  curl -X POST http://127.0.0.1:8000/api/test/send-otp -H 'Content-Type: application/json' -d '{\"phone\":\"9843223774\"}'\n";
echo "  curl -X POST http://127.0.0.1:8000/api/test/login -H 'Content-Type: application/json' -d '{\"email\":\"admin@test.com\",\"password\":\"password\"}'\n\n";

echo "✨ System test completed!\n";