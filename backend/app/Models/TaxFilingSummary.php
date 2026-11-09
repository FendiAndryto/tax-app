<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxFilingSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fiscal_year',
        'gross_sales',
        'total_expenses',
        'net_profit',
        'blue_return_deduction',
        'basic_personal_deduction',
        'other_deductions',
        'taxable_income',
        'estimated_income_tax',
        'estimated_resident_tax',
        'questionnaire_answers',
        'form_b_snapshot',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'gross_sales' => 'decimal:2',
            'total_expenses' => 'decimal:2',
            'net_profit' => 'decimal:2',
            'blue_return_deduction' => 'decimal:2',
            'basic_personal_deduction' => 'decimal:2',
            'other_deductions' => 'decimal:2',
            'taxable_income' => 'decimal:2',
            'estimated_income_tax' => 'decimal:2',
            'estimated_resident_tax' => 'decimal:2',
            'questionnaire_answers' => 'array',
            'form_b_snapshot' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Recompute tax liability according to Japanese personal income and resident tax rules
     */
    public function recompute(): void
    {
        $this->net_profit = $this->gross_sales - $this->total_expenses;
        $this->taxable_income = max(
            0,
            $this->net_profit - $this->blue_return_deduction - $this->basic_personal_deduction - $this->other_deductions
        );
        $this->estimated_income_tax = floor($this->taxable_income * 0.05); // 5% minimum progressive bracket
        $this->estimated_resident_tax = floor($this->taxable_income * 0.10); // 10% standard flat
    }
}
