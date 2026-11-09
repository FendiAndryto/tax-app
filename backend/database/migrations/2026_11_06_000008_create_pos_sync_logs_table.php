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
        Schema::create('pos_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('pos_transaction_id', 100)->index();
            $table->string('pay_period', 7); // e.g. 2026-09
            $table->unsignedSmallInteger('attendance_days'); // Article 204: 5,000 JPY/day allowance
            $table->decimal('gross_revenue', 12, 2);
            $table->decimal('withholding_tax', 12, 2); // (Gross - (5,000 * Days)) * 10.21%
            $table->decimal('net_payment', 12, 2);
            $table->json('deductions_json')->nullable(); // Hair-make, locker, late penalty, etc.
            $table->json('raw_payload')->nullable();
            $table->enum('status', ['synced', 'failed', 'reconciled'])->default('synced');
            $table->timestamps();

            $table->unique(['user_id', 'pos_transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_sync_logs');
    }
};
