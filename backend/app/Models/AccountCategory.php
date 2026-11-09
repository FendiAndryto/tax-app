<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_ja',
        'name_en',
        'type',
        'is_nightwork_preset',
    ];

    protected function casts(): array
    {
        return [
            'is_nightwork_preset' => 'boolean',
        ];
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function autoCategorizationRules(): HasMany
    {
        return $this->hasMany(AutoCategorizationRule::class, 'category_id');
    }

    public function userBusinessRatios(): HasMany
    {
        return $this->hasMany(UserBusinessRatio::class, 'target_account_id');
    }
}
