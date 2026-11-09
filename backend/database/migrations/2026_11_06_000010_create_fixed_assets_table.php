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
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('asset_name', 255);
            $table->date('purchase_date');
            $table->date('in_service_date');
            $table->decimal('acquisition_cost', 12, 2);
            $table->enum('depreciation_method', ['straight_line', 'declining_balance', 'small_sum', 'lump_sum'])->default('straight_line');
            $table->unsignedSmallInteger('useful_life_years')->default(4);
            $table->decimal('business_ratio', 5, 2)->default(100.00); // 0.00 to 100.00 %
            $table->decimal('accumulated_depreciation', 12, 2)->default(0);
            $table->decimal('book_value', 12, 2);
            $table->boolean('is_retired')->default(false);
            $table->date('retirement_date')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'purchase_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
