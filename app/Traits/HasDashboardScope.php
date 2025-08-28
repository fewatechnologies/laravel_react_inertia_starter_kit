<?php

namespace App\Traits;

trait HasDashboardScope
{
    /**
     * Scope for dashboard type (shared database only)
     */
    public function scopeDashboardType($query, $type)
    {
        if ($this->getConnectionName() === 'mysql') {
            // Only apply scope for shared database
            return $query->where('dashboard_type', $type);
        }

        return $query;
    }

    /**
     * Boot trait
     */
    protected static function bootHasDashboardScope()
    {
        static::creating(function ($model) {
            if ($model->getConnectionName() === 'mysql' && !$model->dashboard_type) {
                $model->dashboard_type = static::getDefaultDashboardType();
            }
        });
    }

    /**
     * Get default dashboard type (override in models)
     */
    protected static function getDefaultDashboardType()
    {
        return 'default';
    }
}
