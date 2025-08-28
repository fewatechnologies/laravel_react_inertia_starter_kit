import { Head } from "@inertiajs/react";
import {
    CubeIcon,
    UserGroupIcon,
    CircleStackIcon,
    PlusIcon,
    PlayIcon,
    PauseIcon,
} from "@heroicons/react/24/outline";
import MasterAdminLayout from "@/Layouts/MasterAdminLayout";
import DashboardCard from "@/Components/MasterAdmin/DashboardCard";
import StatsCard from "@/Components/MasterAdmin/StatsCard";

export default function Dashboard({ dashboards, stats }) {
    return (
        <MasterAdminLayout>
            <Head title="Master Admin Dashboard" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">
                            Dashboard Overview
                        </h1>
                        <p className="text-gray-600">
                            Manage your multi-dashboard system
                        </p>
                    </div>
                    <button className="btn-primary flex items-center">
                        <PlusIcon className="h-4 w-4 mr-2" />
                        Create Dashboard
                    </button>
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <StatsCard
                        title="Total Dashboards"
                        value={stats.total_dashboards}
                        icon={CubeIcon}
                        color="primary"
                    />
                    <StatsCard
                        title="Active Dashboards"
                        value={stats.active_dashboards}
                        icon={PlayIcon}
                        color="green"
                    />
                    <StatsCard
                        title="Total Users"
                        value={stats.total_users}
                        icon={UserGroupIcon}
                        color="blue"
                    />
                    <StatsCard
                        title="Database Strategies"
                        value={`${stats.shared_db_dashboards}S / ${stats.separate_db_dashboards}D`}
                        icon={CircleStackIcon}
                        color="purple"
                        subtitle="Shared / Separate"
                    />
                </div>

                {/* Dashboards Grid */}
                <div>
                    <h2 className="text-lg font-medium text-gray-900 mb-4">
                        Your Dashboards
                    </h2>
                    {dashboards.length === 0 ? (
                        <div className="text-center py-12 bg-white rounded-lg border border-gray-200">
                            <CubeIcon className="mx-auto h-12 w-12 text-gray-400" />
                            <h3 className="mt-4 text-lg font-medium text-gray-900">
                                No dashboards yet
                            </h3>
                            <p className="mt-2 text-gray-500">
                                Get started by creating your first dashboard
                            </p>
                            <button className="mt-4 btn-primary">
                                <PlusIcon className="h-4 w-4 mr-2" />
                                Create Your First Dashboard
                            </button>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {dashboards.map((dashboard) => (
                                <DashboardCard
                                    key={dashboard.id}
                                    dashboard={dashboard}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </MasterAdminLayout>
    );
}
