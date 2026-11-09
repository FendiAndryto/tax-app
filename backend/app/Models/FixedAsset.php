<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset_name',
        'purchase_date',
        'in_service_date',
        'acquisition_cost',
        'depreciation_method',
        'useful_life_years',
        'business_ratio',
        'accumulated_depreciation',
        'book_value',
        'is_retired',
        'retirement_date',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'in_service_date' => 'date',
            'acquisition_cost' => 'decimal:2',
            'useful_life_years' => 'integer',
            'business_ratio' => 'decimal:2',
            'accumulated_depreciation' => 'decimal:2',
            'book_value' => 'decimal:2',
            'is_retired' => 'boolean',
            'retirement_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
