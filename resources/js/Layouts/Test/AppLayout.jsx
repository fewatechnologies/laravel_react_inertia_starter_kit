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
    LogoutIcon,
    UserIcon,
    ShieldCheckIcon
} from '@heroicons/react/outline';

export default function AppLayout({ title, children }) {
    const { auth, dashboardType } = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [darkMode, setDarkMode] = useState(false);

    const navigation = [
        { name: 'Dashboard', href: route('test.dashboard'), icon: HomeIcon, current: true },
        { name: 'Profile', href: route('test.profile'), icon: UserIcon, current: false },
        { name: 'Users', href: '#', icon: UsersIcon, current: false },
        { name: 'Settings', href: '#', icon: CogIcon, current: false },
    ];

    return (
        <div className={`min-h-screen bg-gray-50 ${darkMode ? 'dark' : ''}`}>
            <Head title={title} />
            
            {/* Sidebar */}
            <div className={`fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out ${
                sidebarOpen ? 'translate-x-0' : '-translate-x-full'
            } lg:translate-x-0 lg:static lg:inset-0`}>
                <div className="flex items-center justify-between h-16 px-6 border-b border-gray-200">
                    <div className="flex items-center">
                        <ShieldCheckIcon className="h-8 w-8 text-primary-600" />
                        <h1 className="ml-2 text-xl font-semibold text-gray-900">
                            Test Dashboard
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
                                className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 ${
                                    item.current
                                        ? 'bg-primary-100 text-primary-700'
                                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                                }`}
                            >
                                <item.icon className="mr-3 h-5 w-5" />
                                {item.name}
                            </Link>
                        ))}
                    </div>
                </nav>

                {/* User info */}
                <div className="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200">
                    <div className="flex items-center">
                        <div className="flex-shrink-0">
                            <div className="h-8 w-8 bg-primary-600 rounded-full flex items-center justify-center">
                                <span className="text-sm font-medium text-white">
                                    {auth?.user?.name?.charAt(0) || 'U'}
                                </span>
                            </div>
                        </div>
                        <div className="ml-3 flex-1 min-w-0">
                            <p className="text-sm font-medium text-gray-900 truncate">
                                {auth?.user?.name || 'User'}
                            </p>
                            <p className="text-xs text-gray-500 truncate">
                                {auth?.user?.email || 'user@example.com'}
                            </p>
                        </div>
                    </div>
                </div>
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
                        <div className="flex flex-1 items-center">
                            <span className="text-sm text-gray-500">
                                Welcome to your test dashboard
                            </span>
                        </div>
                        <div className="flex items-center gap-x-4 lg:gap-x-6">
                            {/* Theme toggle */}
                            <button
                                onClick={() => setDarkMode(!darkMode)}
                                className="p-2 text-gray-400 hover:text-gray-500 rounded-md hover:bg-gray-100"
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
                                    {auth?.user?.name}
                                </span>
                                <Link
                                    href={route('test.logout')}
                                    method="post"
                                    as="button"
                                    className="p-2 text-gray-400 hover:text-gray-500 rounded-md hover:bg-gray-100"
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