import { useState } from "react";
import { Head, Link, router } from "@inertiajs/react";
import {
    Bars3Icon,
    XMarkIcon,
    HomeIcon,
    CogIcon,
    ArrowRightOnRectangleIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from "@heroicons/react/24/outline";
import {
    HomeIcon as HomeIconSolid,
    CogIcon as CogIconSolid,
} from "@heroicons/react/24/solid";

const navigation = [
    {
        name: "Dashboard",
        href: "master-admin.dashboard",
        icon: HomeIcon,
        iconActive: HomeIconSolid,
    },
    {
        name: "Settings",
        href: "master-admin.settings",
        icon: CogIcon,
        iconActive: CogIconSolid,
    },
];

export default function MasterAdminLayout({ children }) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [sidebarCollapsed, setSidebarCollapsed] = useState(false);

    const currentRoute = route().current();

    const logout = () => {
        router.post(route("master-admin.logout"));
    };

    return (
        <>
            <Head>
                <title>Master Admin</title>
            </Head>

            <div className="min-h-screen bg-gray-50 flex">
                {/* Mobile sidebar */}
                <div
                    className={`fixed inset-0 z-40 lg:hidden ${
                        sidebarOpen ? "" : "pointer-events-none"
                    }`}
                >
                    <div
                        className={`fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity ${
                            sidebarOpen ? "opacity-100" : "opacity-0"
                        }`}
                        onClick={() => setSidebarOpen(false)}
                    />

                    <div
                        className={`fixed inset-y-0 left-0 flex flex-col w-64 bg-white shadow-xl transform transition-transform ${
                            sidebarOpen ? "translate-x-0" : "-translate-x-full"
                        }`}
                    >
                        <div className="flex items-center justify-between h-16 px-4 bg-primary-600">
                            <span className="text-white font-bold text-lg">
                                Master Admin
                            </span>
                            <button
                                onClick={() => setSidebarOpen(false)}
                                className="text-white"
                            >
                                <XMarkIcon className="h-6 w-6" />
                            </button>
                        </div>

                        <nav className="flex-1 px-4 py-4 space-y-2">
                            {navigation.map((item) => {
                                const isActive = currentRoute === item.href;
                                const Icon = isActive
                                    ? item.iconActive
                                    : item.icon;

                                return (
                                    <Link
                                        key={item.name}
                                        href={route(item.href)}
                                        className={`flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors ${
                                            isActive
                                                ? "bg-primary-100 text-primary-700"
                                                : "text-gray-700 hover:bg-gray-50"
                                        }`}
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
                <div
                    className={`hidden lg:flex lg:flex-col ${
                        sidebarCollapsed ? "w-16" : "w-64"
                    } bg-white shadow-sm transition-all duration-300`}
                >
                    <div className="flex items-center justify-between h-16 px-4 bg-primary-600">
                        {!sidebarCollapsed && (
                            <span className="text-white font-bold text-lg">
                                Master Admin
                            </span>
                        )}
                        <button
                            onClick={() =>
                                setSidebarCollapsed(!sidebarCollapsed)
                            }
                            className="text-white p-1 rounded hover:bg-primary-700"
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
                                    className={`flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors ${
                                        isActive
                                            ? "bg-primary-100 text-primary-700"
                                            : "text-gray-700 hover:bg-gray-50"
                                    }`}
                                    title={sidebarCollapsed ? item.name : ""}
                                >
                                    <Icon
                                        className={`h-5 w-5 ${
                                            sidebarCollapsed ? "" : "mr-3"
                                        }`}
                                    />
                                    {!sidebarCollapsed && item.name}
                                </Link>
                            );
                        })}
                    </nav>

                    <div className="px-4 py-4 border-t border-gray-200">
                        <button
                            onClick={logout}
                            className={`flex items-center w-full px-4 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-50 transition-colors`}
                            title={sidebarCollapsed ? "Logout" : ""}
                        >
                            <ArrowRightOnRectangleIcon
                                className={`h-5 w-5 ${
                                    sidebarCollapsed ? "" : "mr-3"
                                }`}
                            />
                            {!sidebarCollapsed && "Logout"}
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
                                Master Admin
                            </span>
                            <div className="w-6" /> {/* Spacer */}
                        </div>
                    </div>

                    {/* Page content */}
                    <main className="flex-1 overflow-auto p-6">{children}</main>
                </div>
            </div>
        </>
    );
}
