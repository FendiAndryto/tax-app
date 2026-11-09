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
        Schema::create('auto_categorization_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('keyword', 255);
            $table->enum('match_type', ['contains', 'exact', 'starts_with'])->default('contains');
            $table->enum('action_status', ['business', 'private'])->default('business');
            $table->foreignId('category_id')->constrained('account_categories')->cascadeOnDelete();
            $table->decimal('business_ratio', 5, 2)->default(100.00);
            $table->integer('priority')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_categorization_rules');
    }
};
