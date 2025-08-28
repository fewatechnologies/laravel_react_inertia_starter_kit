const colorVariants = {
    primary: "text-primary-600 bg-primary-100",
    green: "text-green-600 bg-green-100",
    blue: "text-blue-600 bg-blue-100",
    purple: "text-purple-600 bg-purple-100",
    red: "text-red-600 bg-red-100",
    yellow: "text-yellow-600 bg-yellow-100",
};

export default function StatsCard({
    title,
    value,
    icon: Icon,
    color = "primary",
    subtitle = null,
}) {
    return (
        <div className="bg-white rounded-lg shadow border border-gray-200 p-6">
            <div className="flex items-center">
                <div className={`p-3 rounded-lg ${colorVariants[color]}`}>
                    <Icon className="h-6 w-6" />
                </div>

                <div className="ml-4">
                    <p className="text-sm font-medium text-gray-600">{title}</p>
                    <p className="text-2xl font-bold text-gray-900">{value}</p>
                    {subtitle && (
                        <p className="text-xs text-gray-500">{subtitle}</p>
                    )}
                </div>
            </div>
        </div>
    );
}
