<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tax_filing_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('fiscal_year'); // e.g. 2026
            $table->decimal('gross_sales', 14, 2)->default(0);
            $table->decimal('total_expenses', 14, 2)->default(0);
            $table->decimal('net_profit', 14, 2)->default(0);
            $table->decimal('blue_return_deduction', 10, 2)->default(650000.00); // 650,000 JPY Blue Return deduction
            $table->decimal('basic_personal_deduction', 10, 2)->default(480000.00); // 480,000 JPY basic deduction
            $table->decimal('other_deductions', 12, 2)->default(0); // Spouse, medical, social insurance, etc.
            $table->decimal('taxable_income', 14, 2)->default(0);
            $table->decimal('estimated_income_tax', 12, 2)->default(0); // 5% minimum progressive bracket
            $table->decimal('estimated_resident_tax', 12, 2)->default(0); // 10% standard flat
            $table->json('questionnaire_answers')->nullable(); // Answers from Screen D-07 Wizard
            $table->json('form_b_snapshot')->nullable();
            $table->enum('status', ['draft', 'calculated', 'finalized', 'submitted'])->default('draft');
            $table->timestamps();

            $table->unique(['user_id', 'fiscal_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_filing_summaries');
    }
};
