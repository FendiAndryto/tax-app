<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'client_name',
        'client_t_number',
        'issue_date',
        'due_date',
        'subtotal_10_percent',
        'tax_10_percent',
        'subtotal_8_percent',
        'tax_8_percent',
        'total_tax',
        'grand_total',
        'rounding_rule',
        'items_json',
        'bank_info_snapshot',
        'pdf_path',
        'erpa_hash',
        'status',
        'auto_delivered_to_pos',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal_10_percent' => 'decimal:2',
            'tax_10_percent' => 'decimal:2',
            'subtotal_8_percent' => 'decimal:2',
            'tax_8_percent' => 'decimal:2',
            'total_tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'items_json' => 'array',
            'bank_info_snapshot' => 'array',
            'auto_delivered_to_pos' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Round consumption tax based on Japanese Qualified Invoice Rules (Single-pass rounding)
     */
    public static function applyRounding(float $taxAmount, string $rule = 'FLOOR'): float
    {
        return match ($rule) {
            'CEIL' => ceil($taxAmount),
            'ROUND' => round($taxAmount),
            default => floor($taxAmount),
        };
    }
}
