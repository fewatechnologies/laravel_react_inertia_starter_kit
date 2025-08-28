import React from 'react';
import MasterAdminLayout from '@/Layouts/MasterAdminLayout';
import { Head, Link } from '@inertiajs/react';
import { 
    PlusIcon, 
    ViewGridIcon, 
    UsersIcon, 
    CogIcon,
    CheckCircleIcon,
    XCircleIcon,
    EyeIcon
} from '@heroicons/react/outline';

export default function Dashboard({ dashboards, stats, themePresets }) {
    return (
        <MasterAdminLayout title="Master Admin Dashboard">
            <Head title="Master Admin Dashboard" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="md:flex md:items-center md:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-900">
                            Master Admin Dashboard
                        </h1>
                        <p className="mt-1 text-sm text-gray-600">
                            Manage all dashboard types and system configuration
                        </p>
                    </div>
                    <div className="mt-4 md:mt-0">
                        <Link
                            href={route('master.admin.create')}
                            className="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        >
                            <PlusIcon className="h-4 w-4 mr-2" />
                            Create New Dashboard
                        </Link>
                    </div>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="bg-white overflow-hidden shadow rounded-lg">
                        <div className="p-5">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <ViewGridIcon className="h-6 w-6 text-primary-600" />
                                </div>
                                <div className="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt className="text-sm font-medium text-gray-500 truncate">
                                            Total Dashboards
                                        </dt>
                                        <dd className="text-lg font-medium text-gray-900">
                                            {stats.total_dashboards}
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
                                            Active Dashboards
                                        </dt>
                                        <dd className="text-lg font-medium text-gray-900">
                                            {stats.active_dashboards}
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
                                    <UsersIcon className="h-6 w-6 text-blue-600" />
                                </div>
                                <div className="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt className="text-sm font-medium text-gray-500 truncate">
                                            Total Users
                                        </dt>
                                        <dd className="text-lg font-medium text-gray-900">
                                            {stats.total_users}
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
                                    <CogIcon className="h-6 w-6 text-purple-600" />
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

                {/* Dashboard Types Table */}
                <div className="bg-white shadow rounded-lg">
                    <div className="px-4 py-5 sm:p-6">
                        <div className="sm:flex sm:items-center">
                            <div className="sm:flex-auto">
                                <h3 className="text-lg font-medium text-gray-900">
                                    Dashboard Types
                                </h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    Manage all dashboard types in your system
                                </p>
                            </div>
                            <div className="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                                <Link
                                    href={route('master.admin.create')}
                                    className="inline-flex items-center justify-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                >
                                    Add Dashboard Type
                                </Link>
                            </div>
                        </div>
                        
                        <div className="mt-6 flow-root">
                            <div className="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div className="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                    {dashboards.length > 0 ? (
                                        <table className="min-w-full divide-y divide-gray-300">
                                            <thead>
                                                <tr>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Dashboard Type
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Authentication
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Status
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Users
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Created
                                                    </th>
                                                    <th className="relative px-6 py-3">
                                                        <span className="sr-only">Actions</span>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="bg-white divide-y divide-gray-200">
                                                {dashboards.map((dashboard) => (
                                                    <tr key={dashboard.id} className="hover:bg-gray-50">
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <div>
                                                                <div className="text-sm font-medium text-gray-900">
                                                                    {dashboard.name}
                                                                </div>
                                                                <div className="text-sm text-gray-500">
                                                                    /{dashboard.type}
                                                                </div>
                                                                {dashboard.description && (
                                                                    <div className="text-xs text-gray-400 mt-1">
                                                                        {dashboard.description}
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <div className="flex space-x-1">
                                                                {dashboard.auth_methods.map((method) => (
                                                                    <span
                                                                        key={method}
                                                                        className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${
                                                                            method === 'email' 
                                                                                ? 'bg-blue-100 text-blue-800'
                                                                                : 'bg-green-100 text-green-800'
                                                                        }`}
                                                                    >
                                                                        {method.toUpperCase()}
                                                                    </span>
                                                                ))}
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                                                dashboard.is_active 
                                                                    ? 'bg-green-100 text-green-800'
                                                                    : 'bg-red-100 text-red-800'
                                                            }`}>
                                                                {dashboard.is_active ? (
                                                                    <>
                                                                        <CheckCircleIcon className="w-3 h-3 mr-1" />
                                                                        Active
                                                                    </>
                                                                ) : (
                                                                    <>
                                                                        <XCircleIcon className="w-3 h-3 mr-1" />
                                                                        Inactive
                                                                    </>
                                                                )}
                                                            </span>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            {dashboard.users_count || 0}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            {new Date(dashboard.created_at).toLocaleDateString()}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                            <div className="flex items-center space-x-2">
                                                                <a
                                                                    href={`/${dashboard.type}/login`}
                                                                    target="_blank"
                                                                    rel="noopener noreferrer"
                                                                    className="text-primary-600 hover:text-primary-900"
                                                                >
                                                                    <EyeIcon className="h-4 w-4" />
                                                                </a>
                                                                <Link
                                                                    href={route('master.admin.edit', dashboard)}
                                                                    className="text-indigo-600 hover:text-indigo-900"
                                                                >
                                                                    Edit
                                                                </Link>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    ) : (
                                        <div className="text-center py-12">
                                            <ViewGridIcon className="mx-auto h-12 w-12 text-gray-400" />
                                            <h3 className="mt-2 text-sm font-medium text-gray-900">
                                                No dashboard types
                                            </h3>
                                            <p className="mt-1 text-sm text-gray-500">
                                                Get started by creating your first dashboard type.
                                            </p>
                                            <div className="mt-6">
                                                <Link
                                                    href={route('master.admin.create')}
                                                    className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                                >
                                                    <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
                                                    Create Dashboard Type
                                                </Link>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Quick Start Guide */}
                <div className="bg-primary-50 rounded-lg p-6">
                    <div className="flex">
                        <div className="flex-shrink-0">
                            <ViewGridIcon className="h-6 w-6 text-primary-600" />
                        </div>
                        <div className="ml-3">
                            <h3 className="text-sm font-medium text-primary-800">
                                Quick Start Guide
                            </h3>
                            <div className="mt-2 text-sm text-primary-700">
                                <ol className="list-decimal pl-5 space-y-1">
                                    <li>Create your first dashboard type using the "Create New Dashboard" button</li>
                                    <li>Configure authentication methods (Email, SMS, or both)</li>
                                    <li>Choose a theme preset or customize colors</li>
                                    <li>Define roles and permissions for your dashboard users</li>
                                    <li>Access your new dashboard at /{'{dashboard-type}'}/login</li>
                                </ol>
                            </div>
                            <div className="mt-4">
                                <Link
                                    href={route('master.admin.create')}
                                    className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                >
                                    Get Started
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </MasterAdminLayout>
    );
}