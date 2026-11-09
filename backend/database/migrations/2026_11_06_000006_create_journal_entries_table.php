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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_category_id')->constrained('account_categories')->cascadeOnDelete();
            $table->enum('entry_type', ['DEBIT', 'CREDIT']); // DEBIT (借方) / CREDIT (貸方)
            $table->decimal('amount', 12, 2);
            $table->decimal('tax_rate', 4, 2)->default(0.10); // 0.10 for 10%, 0.08 for 8%, 0.00 for exempt
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->index(['transaction_id', 'entry_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
