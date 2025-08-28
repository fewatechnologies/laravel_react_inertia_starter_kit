import React from 'react';
import AppLayout from '@/Layouts/Test/AppLayout';
import { Head } from '@inertiajs/react';
import { 
    UsersIcon, 
    CheckCircleIcon, 
    ShieldCheckIcon,
    ClockIcon
} from '@heroicons/react/outline';

export default function Dashboard({ auth, stats, userRoles, userPermissions }) {
    return (
        <AppLayout title="Test Dashboard">
            <Head title="Dashboard" />
            
            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-900">
                        Welcome back, {auth.user?.name}!
                    </h1>
                    <p className="mt-1 text-sm text-gray-600">
                        Here's what's happening with your test dashboard today.
                    </p>
                </div>

                {/* User Info Card */}
                <div className="bg-white shadow rounded-lg">
                    <div className="px-4 py-5 sm:p-6">
                        <h3 className="text-lg leading-6 font-medium text-gray-900">
                            Your Account Information
                        </h3>
                        <div className="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Email</dt>
                                <dd className="mt-1 text-sm text-gray-900">{auth.user?.email}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Phone</dt>
                                <dd className="mt-1 text-sm text-gray-900">{auth.user?.phone || 'Not provided'}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Email Verified</dt>
                                <dd className="mt-1 text-sm text-gray-900">
                                    {auth.user?.email_verified_at ? (
                                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <CheckCircleIcon className="w-3 h-3 mr-1" />
                                            Verified
                                        </span>
                                    ) : (
                                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    )}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-gray-500">Phone Verified</dt>
                                <dd className="mt-1 text-sm text-gray-900">
                                    {auth.user?.phone_verified_at ? (
                                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <CheckCircleIcon className="w-3 h-3 mr-1" />
                                            Verified
                                        </span>
                                    ) : (
                                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Not verified
                                        </span>
                                    )}
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="bg-white overflow-hidden shadow rounded-lg">
                        <div className="p-5">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <UsersIcon className="h-6 w-6 text-primary-600" />
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
                                    <CheckCircleIcon className="h-6 w-6 text-green-600" />
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
                                    <ShieldCheckIcon className="h-6 w-6 text-blue-600" />
                                </div>
                                <div className="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt className="text-sm font-medium text-gray-500 truncate">
                                            Verified Users
                                        </dt>
                                        <dd className="text-lg font-medium text-gray-900">
                                            {stats.verified_users || 0}
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
                                    <ClockIcon className="h-6 w-6 text-purple-600" />
                                </div>
                                <div className="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt className="text-sm font-medium text-gray-500 truncate">
                                            Recent Logins
                                        </dt>
                                        <dd className="text-lg font-medium text-gray-900">
                                            {stats.recent_logins || 0}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Roles and Permissions */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div className="bg-white shadow rounded-lg">
                        <div className="px-4 py-5 sm:p-6">
                            <h3 className="text-lg leading-6 font-medium text-gray-900">
                                Your Roles
                            </h3>
                            <div className="mt-5">
                                {userRoles && userRoles.length > 0 ? (
                                    <div className="flex flex-wrap gap-2">
                                        {userRoles.map((role, index) => (
                                            <span
                                                key={index}
                                                className="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800"
                                            >
                                                {role}
                                            </span>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-sm text-gray-500">No roles assigned</p>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="bg-white shadow rounded-lg">
                        <div className="px-4 py-5 sm:p-6">
                            <h3 className="text-lg leading-6 font-medium text-gray-900">
                                Your Permissions
                            </h3>
                            <div className="mt-5">
                                {userPermissions && userPermissions.length > 0 ? (
                                    <div className="flex flex-wrap gap-2">
                                        {userPermissions.map((permission, index) => (
                                            <span
                                                key={index}
                                                className="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"
                                            >
                                                {permission}
                                            </span>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-sm text-gray-500">No permissions assigned</p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Features Card */}
                <div className="bg-primary-50 rounded-lg p-6">
                    <div className="flex">
                        <div className="flex-shrink-0">
                            <ShieldCheckIcon className="h-6 w-6 text-primary-600" />
                        </div>
                        <div className="ml-3">
                            <h3 className="text-sm font-medium text-primary-800">
                                Test Dashboard Features
                            </h3>
                            <div className="mt-2 text-sm text-primary-700">
                                <ul className="list-disc pl-5 space-y-1">
                                    <li>Multi-database support with test database connection</li>
                                    <li>SMS OTP authentication with Aakash SMS integration</li>
                                    <li>Email verification and notifications</li>
                                    <li>Role-based access control and permissions</li>
                                    <li>Activity logging and audit trails</li>
                                    <li>Responsive design with dark/light mode toggle</li>
                                    <li>JWT API authentication for mobile apps</li>
                                    <li>Rate limiting and security measures</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}