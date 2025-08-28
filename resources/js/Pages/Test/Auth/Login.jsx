import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { ShieldCheckIcon, PhoneIcon, MailIcon } from '@heroicons/react/outline';

export default function Login({ dashboardType, authMethods }) {
    const [authMethod, setAuthMethod] = useState('email');
    const [showOtpForm, setShowOtpForm] = useState(false);
    const [otpSent, setOtpSent] = useState(false);
    
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        phone: '',
        otp: '',
        remember: false,
    });

    const submitEmailLogin = (e) => {
        e.preventDefault();
        post(route('test.login'), {
            onFinish: () => reset('password'),
        });
    };

    const sendOtp = async (e) => {
        e.preventDefault();
        
        try {
            const response = await fetch(route('test.send-otp'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ phone: data.phone }),
            });

            const result = await response.json();
            
            if (result.success) {
                setOtpSent(true);
                setShowOtpForm(true);
            } else {
                alert(result.message || 'Failed to send OTP');
            }
        } catch (error) {
            alert('Error sending OTP: ' + error.message);
        }
    };

    const verifyOtp = async (e) => {
        e.preventDefault();
        
        try {
            const response = await fetch(route('test.verify-otp'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ 
                    phone: data.phone, 
                    otp: data.otp 
                }),
            });

            const result = await response.json();
            
            if (result.success) {
                window.location.href = result.redirect || route('test.dashboard');
            } else {
                alert(result.message || 'Invalid OTP');
            }
        } catch (error) {
            alert('Error verifying OTP: ' + error.message);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <Head title="Login" />
            <meta name="csrf-token" content={document.querySelector('meta[name="csrf-token"]')?.content} />
            
            <div className="max-w-md w-full space-y-8">
                <div>
                    <div className="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-primary-100">
                        <ShieldCheckIcon className="h-8 w-8 text-primary-600" />
                    </div>
                    <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        Sign in to Test Dashboard
                    </h2>
                    <p className="mt-2 text-center text-sm text-gray-600">
                        Multi-database authentication system
                    </p>
                </div>

                {/* Auth Method Toggle */}
                {authMethods.includes('sms') && authMethods.includes('email') && (
                    <div className="flex rounded-md shadow-sm" role="group">
                        <button
                            type="button"
                            onClick={() => {
                                setAuthMethod('email');
                                setShowOtpForm(false);
                                setOtpSent(false);
                            }}
                            className={`px-4 py-2 text-sm font-medium rounded-l-lg border ${
                                authMethod === 'email'
                                    ? 'bg-primary-600 text-white border-primary-600'
                                    : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
                            }`}
                        >
                            <MailIcon className="w-4 h-4 inline mr-2" />
                            Email
                        </button>
                        <button
                            type="button"
                            onClick={() => {
                                setAuthMethod('sms');
                                setShowOtpForm(false);
                                setOtpSent(false);
                            }}
                            className={`px-4 py-2 text-sm font-medium rounded-r-lg border ${
                                authMethod === 'sms'
                                    ? 'bg-primary-600 text-white border-primary-600'
                                    : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
                            }`}
                        >
                            <PhoneIcon className="w-4 h-4 inline mr-2" />
                            SMS
                        </button>
                    </div>
                )}

                {/* Email Login Form */}
                {authMethod === 'email' && (
                    <form className="mt-8 space-y-6" onSubmit={submitEmailLogin}>
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
                                <label htmlFor="password" className="sr-only">
                                    Password
                                </label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    autoComplete="current-password"
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
                                {processing ? 'Signing in...' : 'Sign in with Email'}
                            </button>
                        </div>
                    </form>
                )}

                {/* SMS Login Form */}
                {authMethod === 'sms' && !showOtpForm && (
                    <form className="mt-8 space-y-6" onSubmit={sendOtp}>
                        <div>
                            <label htmlFor="phone" className="sr-only">
                                Phone number
                            </label>
                            <input
                                id="phone"
                                name="phone"
                                type="tel"
                                required
                                className="relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                placeholder="Phone number (e.g., 9843223774)"
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}
                            />
                        </div>

                        <div>
                            <button
                                type="submit"
                                className="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                            >
                                Send OTP
                            </button>
                        </div>
                    </form>
                )}

                {/* OTP Verification Form */}
                {showOtpForm && (
                    <form className="mt-8 space-y-6" onSubmit={verifyOtp}>
                        <div className="text-center">
                            <p className="text-sm text-gray-600">
                                OTP sent to {data.phone}
                            </p>
                        </div>
                        
                        <div>
                            <label htmlFor="otp" className="sr-only">
                                Enter OTP
                            </label>
                            <input
                                id="otp"
                                name="otp"
                                type="text"
                                maxLength="6"
                                required
                                className="relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm text-center text-lg tracking-widest"
                                placeholder="Enter 6-digit OTP"
                                value={data.otp}
                                onChange={(e) => setData('otp', e.target.value)}
                            />
                        </div>

                        <div className="flex space-x-3">
                            <button
                                type="button"
                                onClick={() => {
                                    setShowOtpForm(false);
                                    setOtpSent(false);
                                    setData('otp', '');
                                }}
                                className="flex-1 py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                            >
                                Back
                            </button>
                            <button
                                type="submit"
                                className="flex-1 py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                            >
                                Verify OTP
                            </button>
                        </div>
                    </form>
                )}

                <div className="text-center">
                    <Link
                        href={route('test.register')}
                        className="font-medium text-primary-600 hover:text-primary-500"
                    >
                        Don't have an account? Sign up
                    </Link>
                </div>

                {/* Test Credentials */}
                <div className="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                    <h4 className="text-sm font-medium text-yellow-800">Test Credentials:</h4>
                    <p className="mt-1 text-sm text-yellow-700">
                        Email: test@example.com | Password: password<br/>
                        Phone: 9843223774 (for SMS testing)
                    </p>
                </div>
            </div>
        </div>
    );
}