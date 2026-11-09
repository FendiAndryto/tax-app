<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBusinessRatio extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'expense_type',
        'monthly_estimated_cost',
        'business_percentage',
        'target_account_id',
    ];

    protected function casts(): array
    {
        return [
            'monthly_estimated_cost' => 'decimal:2',
            'business_percentage' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function targetAccount(): BelongsTo
    {
        return $this->belongsTo(AccountCategory::class, 'target_account_id');
    }
}
