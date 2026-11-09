<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add optional translation columns (name_en, name_ja) to categories and accounts
     * for multi-language localized display. Falls back to primary 'name' when null.
     *
     * Translation values are populated by DatabaseSeeder, not here.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_ja')->nullable()->after('name_en');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('name_ja')->nullable()->after('name_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ja']);
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ja']);
        });
    }
};
