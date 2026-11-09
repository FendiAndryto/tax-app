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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('business_mode', ['MODE_FREELANCE', 'MODE_NIGHTWORK'])->default('MODE_FREELANCE');
            $table->string('t_number', 14)->nullable(); // e.g., T1234567890123
            $table->enum('tax_rounding_rule', ['FLOOR', 'ROUND', 'CEIL'])->default('FLOOR');
            $table->enum('subscription_status', ['TRIAL', 'ACTIVE', 'CANCELED', 'DELETED'])->default('TRIAL');
            $table->enum('access_mode', ['FULL', 'READ_ONLY_ARCHIVE'])->default('FULL');
            $table->timestamp('purge_scheduled_at')->nullable(); // 30-day quarantine before permanent AWS/DB purge
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // deleted_at
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
