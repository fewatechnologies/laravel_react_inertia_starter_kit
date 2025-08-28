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
        'database_strategy',
        'database_config',
        'auth_methods',
        'theme_config',
        'settings',
        'has_landing_page',
        'is_active',
    ];

    protected $casts = [
        'database_config' => 'array',
        'auth_methods' => 'array',
        'theme_config' => 'array',
        'settings' => 'array',
        'has_landing_page' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'name', 'is_active', 'database_strategy'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getRoutePrefix(): string
    {
        return $this->type;
    }

    public function getDatabaseConnection(): string
    {
        if ($this->database_strategy === 'separate') {
            return $this->database_config['connection_name'] ?? 'mysql';
        }
        
        return 'mysql';
    }

    public function getTablePrefix(): string
    {
        if ($this->database_strategy === 'shared') {
            return $this->database_config['prefix'] ?? $this->type . '_';
        }
        
        return '';
    }

    public function getUsersCount(): int
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

    public function getModelNamespace(): string
    {
        return ucfirst($this->type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStrategy($query, $strategy)
    {
        return $query->where('database_strategy', $strategy);
    }
}