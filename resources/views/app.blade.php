<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'Laravel Multi-Dashboard') }}</title>
        
        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- Meta Tags -->
        <meta name="description" content="Laravel Multi-Dashboard Starter Kit - Dynamic dashboard generation system">
        <meta name="keywords" content="laravel, react, multi-dashboard, starter-kit, inertia">
        <meta name="author" content="Laravel Multi-Dashboard">
        
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <!-- Theme Configuration -->
        @if(isset($dashboardType) && $dashboardType)
            @php
                $themeService = app(\App\Services\ThemeService::class);
                $themeConfig = $themeService->getThemeConfig($dashboardType);
            @endphp
            <style>
                {!! $themeService->generateCssVariables($themeConfig) !!}
            </style>
        @endif
        
        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>