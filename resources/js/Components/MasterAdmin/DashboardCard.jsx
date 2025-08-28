import { Link } from "@inertiajs/react";
import {
    UserGroupIcon,
    GlobeAltIcon,
    CircleStackIcon,
    PauseIcon,
    EllipsisVerticalIcon,
} from "@heroicons/react/24/outline";
import { useState } from "react";

export default function DashboardCard({ dashboard }) {
    const [showMenu, setShowMenu] = useState(false);

    const getStatusColor = (isActive) => {
        return isActive
            ? "text-green-600 bg-green-100"
            : "text-gray-500 bg-gray-100";
    };

    const getDatabaseStrategyIcon = (strategy) => {
        return strategy === "separate" ? CircleStackIcon : GlobeAltIcon;
    };

    return (
        <div className="bg-white rounded-lg shadow border border-gray-200 hover:shadow-md transition-shadow duration-200">
            <div className="p-6">
                <div className="flex items-start justify-between">
                    <div className="flex-1">
                        <h3 className="text-lg font-medium text-gray-900 mb-1">
                            {dashboard.name}
                        </h3>
                        <p className="text-sm text-gray-500 mb-3 line-clamp-2">
                            {dashboard.description || "No description provided"}
                        </p>
                    </div>

                    <div className="relative">
                        <button
                            onClick={() => setShowMenu(!showMenu)}
                            className="text-gray-400 hover:text-gray-600"
                        >
                            <EllipsisVerticalIcon className="h-5 w-5" />
                        </button>

                        {showMenu && (
                            <div className="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-10">
                                <div className="py-1">
                                    <Link
                                        href={`/${dashboard.type}`}
                                        className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                        target="_blank"
                                    >
                                        Visit Dashboard
                                    </Link>
                                    <button className="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        Edit Settings
                                    </button>
                                    <button className="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                <div className="flex items-center space-x-4 text-sm text-gray-500">
                    <div className="flex items-center">
                        <UserGroupIcon className="h-4 w-4 mr-1" />
                        {dashboard.users_count} users
                    </div>

                    <div className="flex items-center">
                        {getDatabaseStrategyIcon(dashboard.database_strategy)({
                            className: "h-4 w-4 mr-1",
                        })}
                        {dashboard.database_strategy}
                    </div>
                </div>

                <div className="mt-4 flex items-center justify-between">
                    <div
                        className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(
                            dashboard.is_active
                        )}`}
                    >
                        {dashboard.is_active ? (
                            <>
                                <PlayIcon className="h-3 w-3 mr-1" />
                                Active
                            </>
                        ) : (
                            <>
                                <PauseIcon className="h-3 w-3 mr-1" />
                                Inactive
                            </>
                        )}
                    </div>

                    <span className="text-xs text-gray-400">
                        Created{" "}
                        {new Date(dashboard.created_at).toLocaleDateString()}
                    </span>
                </div>
            </div>
        </div>
    );
}
