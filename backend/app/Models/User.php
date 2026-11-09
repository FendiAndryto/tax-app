<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'business_mode',
        't_number',
        'tax_rounding_rule',
        'subscription_status',
        'access_mode',
        'purge_scheduled_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'purge_scheduled_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'created_by');
    }

    public function userBusinessRatios(): HasMany
    {
        return $this->hasMany(UserBusinessRatio::class);
    }

    public function autoCategorizationRules(): HasMany
    {
        return $this->hasMany(AutoCategorizationRule::class);
    }

    public function clientDirectories(): HasMany
    {
        return $this->hasMany(ClientDirectory::class);
    }

    public function erpaDocuments(): HasMany
    {
        return $this->hasMany(ErpaDocument::class);
    }

    public function posSyncLogs(): HasMany
    {
        return $this->hasMany(PosSyncLog::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function fixedAssets(): HasMany
    {
        return $this->hasMany(FixedAsset::class);
    }

    public function taxFilingSummaries(): HasMany
    {
        return $this->hasMany(TaxFilingSummary::class);
    }
}
