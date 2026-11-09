<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Assets
            ['code' => '111', 'name_ja' => '普通預金', 'name_en' => 'Cash/Bank', 'type' => 'ASSET', 'is_nightwork_preset' => false],
            ['code' => '112', 'name_ja' => '現金', 'name_en' => 'Cash', 'type' => 'ASSET', 'is_nightwork_preset' => false],
            ['code' => '113', 'name_ja' => '売掛金', 'name_en' => 'Accounts Receivable', 'type' => 'ASSET', 'is_nightwork_preset' => false],
            ['code' => '118', 'name_ja' => '事業主貸', 'name_en' => "Owner's Draw / Prepaid Tax", 'type' => 'ASSET', 'is_nightwork_preset' => false],

            // Liabilities & Equity
            ['code' => '211', 'name_ja' => '買掛金', 'name_en' => 'Accounts Payable', 'type' => 'LIABILITY', 'is_nightwork_preset' => false],
            ['code' => '218', 'name_ja' => '事業主借', 'name_en' => "Owner's Borrowing", 'type' => 'EQUITY', 'is_nightwork_preset' => false],
            ['code' => '311', 'name_ja' => '元入金', 'name_en' => 'Capital', 'type' => 'EQUITY', 'is_nightwork_preset' => false],

            // Revenue
            ['code' => '411', 'name_ja' => '売上高', 'name_en' => 'Sales Revenue', 'type' => 'REVENUE', 'is_nightwork_preset' => false],
            ['code' => '412', 'name_ja' => '雑収入', 'name_en' => 'Miscellaneous Income', 'type' => 'REVENUE', 'is_nightwork_preset' => false],

            // Standard Expenses
            ['code' => '511', 'name_ja' => '消耗品費', 'name_en' => 'Supplies & Tools', 'type' => 'EXPENSE', 'is_nightwork_preset' => false],
            ['code' => '512', 'name_ja' => '地代家賃', 'name_en' => 'Rent Expense', 'type' => 'EXPENSE', 'is_nightwork_preset' => false],
            ['code' => '513', 'name_ja' => '水道光熱費', 'name_en' => 'Utilities & Electricity', 'type' => 'EXPENSE', 'is_nightwork_preset' => false],
            ['code' => '514', 'name_ja' => '通信費', 'name_en' => 'Internet & Phone', 'type' => 'EXPENSE', 'is_nightwork_preset' => false],
            ['code' => '515', 'name_ja' => '旅費交通費', 'name_en' => 'Travel & Transportation', 'type' => 'EXPENSE', 'is_nightwork_preset' => false],
            ['code' => '516', 'name_ja' => '会議費', 'name_en' => 'Meeting Expenses', 'type' => 'EXPENSE', 'is_nightwork_preset' => false],
            ['code' => '517', 'name_ja' => '接待交際費', 'name_en' => 'Entertainment & Dining', 'type' => 'EXPENSE', 'is_nightwork_preset' => false],
            ['code' => '518', 'name_ja' => '広告宣伝費', 'name_en' => 'Advertising & Promotion', 'type' => 'EXPENSE', 'is_nightwork_preset' => false],
            ['code' => '519', 'name_ja' => '減価償却費', 'name_en' => 'Depreciation Expense', 'type' => 'EXPENSE', 'is_nightwork_preset' => false],
            ['code' => '520', 'name_ja' => '支払手数料', 'name_en' => 'Bank & Payment Fees', 'type' => 'EXPENSE', 'is_nightwork_preset' => false],
            ['code' => '528', 'name_ja' => '雑費', 'name_en' => 'Miscellaneous Expense', 'type' => 'EXPENSE', 'is_nightwork_preset' => false],

            // Nightwork / Hospitality Presets
            ['code' => '521', 'name_ja' => '美容衛生費', 'name_en' => 'Styling & Hair-make', 'type' => 'EXPENSE', 'is_nightwork_preset' => true],
            ['code' => '522', 'name_ja' => '衣装代', 'name_en' => 'Costumes & Dress', 'type' => 'EXPENSE', 'is_nightwork_preset' => true],
            ['code' => '523', 'name_ja' => 'ロッカー代', 'name_en' => 'Venue Locker Fee', 'type' => 'EXPENSE', 'is_nightwork_preset' => true],
            ['code' => '529', 'name_ja' => '雑損失', 'name_en' => 'Late Penalty / Misc Loss', 'type' => 'EXPENSE', 'is_nightwork_preset' => true],
        ];

        foreach ($categories as $cat) {
            DB::table('account_categories')->updateOrInsert(
                ['code' => $cat['code']],
                array_merge($cat, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
