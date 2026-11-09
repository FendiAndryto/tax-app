<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'name_ja',
        'account_number',
        'initial_balance',
        'description',
        'is_active',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Calculate current real balance for this account
     */
    public function getCurrentBalanceAttribute(): float
    {
        $cashIn = $this->transactions()->where('type', 'cash_in')->sum('amount');
        $cashOut = $this->transactions()->where('type', 'cash_out')->sum('amount');

        return (float) ($this->initial_balance + $cashIn - $cashOut);
    }
}
