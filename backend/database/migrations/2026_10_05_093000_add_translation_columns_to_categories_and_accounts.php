<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add optional translation columns (name_en, name_ja) to categories and accounts
     * for multi-language localized display. Falls back to primary 'name' when null.
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

        // Seed translations for existing default categories
        $categoryTranslations = [
            'Penjualan Produk' => ['en' => 'Product Sales', 'ja' => '製品販売'],
            'Pendapatan Jasa' => ['en' => 'Service Revenue', 'ja' => 'サービス収入'],
            'Pendapatan Lain-lain' => ['en' => 'Other Revenue', 'ja' => 'その他収入'],
            'Beban Gaji Karyawan' => ['en' => 'Employee Salary', 'ja' => '従業員給与'],
            'Beban Sewa & Utilitas' => ['en' => 'Rent & Utilities', 'ja' => '賃料・光熱費'],
            'Beban Operasional' => ['en' => 'Operational Expense', 'ja' => '営業費用'],
            'Beban Marketing & Iklan' => ['en' => 'Marketing & Advertising', 'ja' => 'マーケティング・広告費'],
            'Bahan Baku & HPP' => ['en' => 'Raw Materials & COGS', 'ja' => '原材料・売上原価'],
        ];

        foreach ($categoryTranslations as $name => $trans) {
            DB::table('categories')
                ->where('name', $name)
                ->update(['name_en' => $trans['en'], 'name_ja' => $trans['ja']]);
        }

        // Seed translations for existing default accounts
        $accountTranslations = [
            'Kas Tunai / Toko' => ['en' => 'Petty Cash / Store', 'ja' => '小口現金 / 店舗'],
            'Bank BCA Operasional' => ['en' => 'BCA Bank (Operations)', 'ja' => 'BCA銀行（運営用）'],
            'Bank Mandiri' => ['en' => 'Mandiri Bank', 'ja' => 'マンディリ銀行'],
        ];

        foreach ($accountTranslations as $name => $trans) {
            DB::table('accounts')
                ->where('name', $name)
                ->update(['name_en' => $trans['en'], 'name_ja' => $trans['ja']]);
        }
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
