<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoCategorizationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'keyword',
        'match_type',
        'action_status',
        'category_id',
        'business_ratio',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'business_ratio' => 'decimal:2',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AccountCategory::class, 'category_id');
    }
}
