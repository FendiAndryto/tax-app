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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number', 50)->unique(); // e.g. INV-202610-9012
            $table->string('client_name', 255);
            $table->string('client_t_number', 14)->nullable();
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('subtotal_10_percent', 12, 2)->default(0);
            $table->decimal('tax_10_percent', 12, 2)->default(0);
            $table->decimal('subtotal_8_percent', 12, 2)->default(0);
            $table->decimal('tax_8_percent', 12, 2)->default(0);
            $table->decimal('total_tax', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->enum('rounding_rule', ['FLOOR', 'ROUND', 'CEIL'])->default('FLOOR');
            $table->json('items_json')->nullable();
            $table->json('bank_info_snapshot')->nullable();
            $table->string('pdf_path', 512)->nullable();
            $table->char('erpa_hash', 64)->nullable();
            $table->enum('status', ['draft', 'issued', 'delivered', 'paid', 'canceled'])->default('issued');
            $table->boolean('auto_delivered_to_pos')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'issue_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
