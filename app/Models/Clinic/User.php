<?php

namespace App\Models\Clinic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity;

    protected $connection = 'mysql';
    protected $table = 'clinic_users';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'dashboard_type',
        'is_active',
        'email_verified_at',
        'phone_verified_at',
        'profile_data',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'profile_data' => 'array',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Scope for dashboard type (shared database only)
     */
    public function scopeDashboardType($query, $type = 'clinic')
    {
        if ('shared' === 'shared') {
            return $query->where('dashboard_type', $type);
        }
        return $query;
    }

    /**
     * Get validation rules
     */
    public static function getValidationRules($id = null): array
    {
        $table = (new static)->getTable();
        $uniqueEmail = "unique:{$table},email" . ($id ? ",{$id}" : "");
        $uniquePhone = "unique:{$table},phone" . ($id ? ",{$id}" : "");
        
        if ('shared' === 'shared') {
            $uniqueEmail .= ",id,dashboard_type,clinic";
            $uniquePhone .= ",id,dashboard_type,clinic";
        }

        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', $uniqueEmail],
            'phone' => ['nullable', 'string', $uniquePhone],
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * Check if SMS auth is enabled
     */
    public function canUseSmsAuth(): bool
    {
        return in_array('sms', ['email', 'sms']);
    }

    /**
     * Check if email auth is enabled  
     */
    public function canUseEmailAuth(): bool
    {
        return in_array('email', ['email', 'sms']);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }
}