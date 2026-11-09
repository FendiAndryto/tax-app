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
        Schema::create('erpa_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('document_type', ['RECEIPT', 'INVOICE']);
            $table->date('issue_date');
            $table->decimal('amount', 12, 2);
            $table->string('partner_name', 255);
            $table->string('storage_path', 512);
            $table->char('file_sha256', 64); // SHA-256 cryptographic hash generated at the exact upload second
            $table->date('statutory_retention_until'); // Fiscal year close + 7 statutory years
            $table->timestamps();

            // Legal 3-key composite search index for ERPA (electronic bookkeeping retention law) audits
            $table->index(['user_id', 'issue_date', 'amount', 'partner_name'], 'idx_erpa_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erpa_documents');
    }
};
