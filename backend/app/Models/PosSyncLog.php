<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pos_transaction_id',
        'pay_period',
        'attendance_days',
        'gross_revenue',
        'withholding_tax',
        'net_payment',
        'deductions_json',
        'raw_payload',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'attendance_days' => 'integer',
            'gross_revenue' => 'decimal:2',
            'withholding_tax' => 'decimal:2',
            'net_payment' => 'decimal:2',
            'deductions_json' => 'array',
            'raw_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Compute Article 204 Withholding Tax according to Japanese Income Tax Act
     * Statutory allowance: ¥5,000 per attendance day. Tax rate: 10.21%.
     */
    public static function computeArticle204Tax(float $grossRevenue, int $attendanceDays): float
    {
        $deductibleBase = 5000 * $attendanceDays;
        if ($grossRevenue > $deductibleBase) {
            return floor(($grossRevenue - $deductibleBase) * 0.1021);
        }

        return 0.0;
    }
}
