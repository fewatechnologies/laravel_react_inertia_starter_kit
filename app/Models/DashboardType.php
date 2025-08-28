<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DashboardType extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'type',
        'name',
        'description',
        'theme_config',
        'auth_methods',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'theme_config' => 'array',
        'auth_methods' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'name', 'is_active', 'theme_config', 'auth_methods'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the route prefix for this dashboard type
     */
    public function getRoutePrefix(): string
    {
        return $this->type;
    }

    /**
     * Get the guard name for this dashboard type
     */
    public function getGuardName(): string
    {
        return $this->type;
    }

    /**
     * Get the API guard name for this dashboard type
     */
    public function getApiGuardName(): string
    {
        return "api-{$this->type}";
    }

    /**
     * Check if SMS authentication is enabled
     */
    public function hasSmsAuth(): bool
    {
        return in_array('sms', $this->auth_methods ?? []);
    }

    /**
     * Check if email authentication is enabled
     */
    public function hasEmailAuth(): bool
    {
        return in_array('email', $this->auth_methods ?? []);
    }

    /**
     * Get theme configuration with defaults
     */
    public function getThemeConfigAttribute($value)
    {
        $defaults = [
            'primary_color' => '#3b82f6',
            'secondary_color' => '#64748b',
            'sidebar_color' => '#ffffff',
            'text_color' => '#1f2937',
            'background_color' => '#f9fafb',
            'dark_mode' => false,
            'logo_url' => null,
            'favicon_url' => null,
        ];

        $config = json_decode($value, true) ?? [];
        return array_merge($defaults, $config);
    }

    /**
     * Set theme configuration
     */
    public function setThemeConfigAttribute($value)
    {
        $this->attributes['theme_config'] = json_encode($value);
    }

    /**
     * Get users count for this dashboard type
     * This is a placeholder method since users are in separate databases
     */
    public function users()
    {
        // Since users are in separate databases per dashboard type,
        // we'll return a dummy relation that always returns 0 count
        return $this->hasMany(User::class, 'dashboard_type_id')->where('id', 0);
    }

    /**
     * Get actual users count for this dashboard type
     */
    public function getUsersCountAttribute(): int
    {
        try {
            $userModelClass = "App\\Models\\{$this->getModelNamespace()}\\User";
            
            if (class_exists($userModelClass)) {
                return $userModelClass::count();
            }
            
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get model namespace for this dashboard type
     */
    public function getModelNamespace(): string
    {
        return ucfirst($this->type);
    }
}