<?php

namespace App\Traits;

trait HasMultiDatabase
{
    /**
     * Get validation rules with proper unique constraints
     */
    public static function getValidationRules($id = null, $dashboardType = null): array
    {
        $table = (new static)->getTable();
        $connection = (new static)->getConnectionName();

        $emailRule = "unique:{$table},email" . ($id ? ",{$id}" : "");
        $phoneRule = "unique:{$table},phone" . ($id ? ",{$id}" : "");

        // Add dashboard type scope for shared database
        if ($connection === 'mysql' && $dashboardType) {
            $emailRule .= ",id,dashboard_type,{$dashboardType}";
            $phoneRule .= ",id,dashboard_type,{$dashboardType}";
        }

        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', $emailRule],
            'phone' => ['nullable', 'string', $phoneRule],
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * Check if email exists for dashboard type
     */
    public static function emailExistsForDashboard(string $email, string $dashboardType): bool
    {
        $query = static::where('email', $email);

        if ((new static)->getConnectionName() === 'mysql') {
            $query->where('dashboard_type', $dashboardType);
        }

        return $query->exists();
    }

    /**
     * Check if phone exists for dashboard type
     */
    public static function phoneExistsForDashboard(string $phone, string $dashboardType): bool
    {
        $query = static::where('phone', $phone);

        if ((new static)->getConnectionName() === 'mysql') {
            $query->where('dashboard_type', $dashboardType);
        }

        return $query->exists();
    }
}
