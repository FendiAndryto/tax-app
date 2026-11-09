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
        Schema::create('user_business_ratios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('expense_type', ['RENT', 'ELECTRICITY', 'INTERNET', 'VEHICLE']);
            $table->decimal('monthly_estimated_cost', 10, 2)->default(0);
            $table->decimal('business_percentage', 5, 2)->default(0); // 0.00 to 100.00 %
            $table->foreignId('target_account_id')->nullable()->constrained('account_categories')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'expense_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_business_ratios');
    }
};
