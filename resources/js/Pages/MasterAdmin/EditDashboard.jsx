import React, { useState, useEffect } from 'react';
import { Head, useForm } from '@inertiajs/react';
import MasterAdminLayout from '../../Layouts/MasterAdmin/AppLayout';

export default function EditDashboard({ dashboard, errors }) {
    const { data, setData, put, processing } = useForm({
        name: dashboard.name || '',
        type: dashboard.type || '',
        description: dashboard.description || '',
        auth_methods: dashboard.auth_methods || ['email'],
        theme_config: dashboard.theme_config || {
            primary_color: '#3b82f6',
            logo_url: '',
            custom_css: ''
        },
        is_active: dashboard.is_active ?? true,
    });

    const [authMethods, setAuthMethods] = useState(dashboard.auth_methods || ['email']);

    useEffect(() => {
        setData('auth_methods', authMethods);
    }, [authMethods]);

    const handleAuthMethodChange = (method) => {
        const newMethods = authMethods.includes(method)
            ? authMethods.filter(m => m !== method)
            : [...authMethods, method];
        
        setAuthMethods(newMethods);
    };

    function submit(e) {
        e.preventDefault();
        put(`/master-admin/dashboards/${dashboard.id}`);
    }

    return (
        <MasterAdminLayout>
            <Head title={`Edit ${dashboard.name} - Master Admin`} />
            
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="md:flex md:items-center md:justify-between">
                        <div className="min-w-0 flex-1">
                            <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                                Edit Dashboard: {dashboard.name}
                            </h2>
                            <p className="mt-1 text-sm text-gray-600">
                                Update dashboard configuration and settings
                            </p>
                        </div>
                        <div className="mt-4 flex md:mt-0 md:ml-4">
                            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                dashboard.is_active 
                                    ? 'bg-green-100 text-green-800' 
                                    : 'bg-red-100 text-red-800'
                            }`}>
                                {dashboard.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </div>
                    </div>

                    <div className="mt-8">
                        <div className="bg-white shadow rounded-lg">
                            <form onSubmit={submit} className="space-y-6 p-6">
                                <div className="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                    <div className="sm:col-span-4">
                                        <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                            Dashboard Name
                                        </label>
                                        <div className="mt-1">
                                            <input
                                                type="text"
                                                name="name"
                                                id="name"
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="e.g., Student Dashboard"
                                            />
                                            {errors.name && (
                                                <p className="mt-2 text-sm text-red-600">{errors.name}</p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="sm:col-span-2">
                                        <label htmlFor="type" className="block text-sm font-medium text-gray-700">
                                            Dashboard Type
                                        </label>
                                        <div className="mt-1">
                                            <input
                                                type="text"
                                                name="type"
                                                id="type"
                                                value={data.type}
                                                onChange={(e) => setData('type', e.target.value)}
                                                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50"
                                                placeholder="e.g., student"
                                                disabled
                                            />
                                            {errors.type && (
                                                <p className="mt-2 text-sm text-red-600">{errors.type}</p>
                                            )}
                                        </div>
                                        <p className="mt-2 text-sm text-gray-500">
                                            Type cannot be changed after creation
                                        </p>
                                    </div>

                                    <div className="sm:col-span-6">
                                        <label htmlFor="description" className="block text-sm font-medium text-gray-700">
                                            Description
                                        </label>
                                        <div className="mt-1">
                                            <textarea
                                                id="description"
                                                name="description"
                                                rows={3}
                                                value={data.description}
                                                onChange={(e) => setData('description', e.target.value)}
                                                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="Brief description of this dashboard type"
                                            />
                                            {errors.description && (
                                                <p className="mt-2 text-sm text-red-600">{errors.description}</p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="sm:col-span-6">
                                        <fieldset>
                                            <legend className="text-sm font-medium text-gray-700">Authentication Methods</legend>
                                            <div className="mt-2 space-y-2">
                                                <div className="flex items-center">
                                                    <input
                                                        id="auth-email"
                                                        name="auth_methods"
                                                        type="checkbox"
                                                        checked={authMethods.includes('email')}
                                                        onChange={() => handleAuthMethodChange('email')}
                                                        className="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                    />
                                                    <label htmlFor="auth-email" className="ml-2 text-sm text-gray-700">
                                                        Email & Password
                                                    </label>
                                                </div>
                                                <div className="flex items-center">
                                                    <input
                                                        id="auth-sms"
                                                        name="auth_methods"
                                                        type="checkbox"
                                                        checked={authMethods.includes('sms')}
                                                        onChange={() => handleAuthMethodChange('sms')}
                                                        className="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                    />
                                                    <label htmlFor="auth-sms" className="ml-2 text-sm text-gray-700">
                                                        SMS OTP
                                                    </label>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>

                                    <div className="sm:col-span-3">
                                        <label htmlFor="primary_color" className="block text-sm font-medium text-gray-700">
                                            Primary Color
                                        </label>
                                        <div className="mt-1 flex items-center space-x-2">
                                            <input
                                                type="color"
                                                id="primary_color"
                                                value={data.theme_config.primary_color}
                                                onChange={(e) => setData('theme_config', {
                                                    ...data.theme_config,
                                                    primary_color: e.target.value
                                                })}
                                                className="h-10 w-16 rounded border border-gray-300"
                                            />
                                            <input
                                                type="text"
                                                value={data.theme_config.primary_color}
                                                onChange={(e) => setData('theme_config', {
                                                    ...data.theme_config,
                                                    primary_color: e.target.value
                                                })}
                                                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="#3b82f6"
                                            />
                                        </div>
                                    </div>

                                    <div className="sm:col-span-3">
                                        <label htmlFor="logo_url" className="block text-sm font-medium text-gray-700">
                                            Logo URL
                                        </label>
                                        <div className="mt-1">
                                            <input
                                                type="url"
                                                id="logo_url"
                                                value={data.theme_config.logo_url}
                                                onChange={(e) => setData('theme_config', {
                                                    ...data.theme_config,
                                                    logo_url: e.target.value
                                                })}
                                                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="https://example.com/logo.png"
                                            />
                                        </div>
                                    </div>

                                    <div className="sm:col-span-6">
                                        <label htmlFor="custom_css" className="block text-sm font-medium text-gray-700">
                                            Custom CSS
                                        </label>
                                        <div className="mt-1">
                                            <textarea
                                                id="custom_css"
                                                name="custom_css"
                                                rows={4}
                                                value={data.theme_config.custom_css}
                                                onChange={(e) => setData('theme_config', {
                                                    ...data.theme_config,
                                                    custom_css: e.target.value
                                                })}
                                                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono text-sm"
                                                placeholder="/* Custom CSS for this dashboard */"
                                            />
                                        </div>
                                    </div>

                                    <div className="sm:col-span-6">
                                        <div className="flex items-center">
                                            <input
                                                id="is_active"
                                                name="is_active"
                                                type="checkbox"
                                                checked={data.is_active}
                                                onChange={(e) => setData('is_active', e.target.checked)}
                                                className="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            />
                                            <label htmlFor="is_active" className="ml-2 text-sm text-gray-700">
                                                Dashboard is active
                                            </label>
                                        </div>
                                        <p className="mt-2 text-sm text-gray-500">
                                            Inactive dashboards cannot be accessed by users
                                        </p>
                                    </div>
                                </div>

                                <div className="flex justify-between pt-6 border-t border-gray-200">
                                    <div>
                                        <p className="text-sm text-gray-500">
                                            Dashboard ID: {dashboard.id} | Created: {new Date(dashboard.created_at).toLocaleDateString()}
                                        </p>
                                    </div>
                                    <div className="flex space-x-3">
                                        <button
                                            type="button"
                                            className="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            onClick={() => window.history.back()}
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                        >
                                            {processing ? 'Updating...' : 'Update Dashboard'}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </MasterAdminLayout>
    );
}